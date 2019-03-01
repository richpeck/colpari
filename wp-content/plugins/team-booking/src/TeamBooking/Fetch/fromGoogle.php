<?php

namespace TeamBooking\Fetch;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Functions,
    TeamBooking\Database,
    TeamBooking\Google;

/**
 * Class fromGoogle
 *
 * Collects all the events from Google Calendars and map them to TeamBooking slots
 * The fetcher also scans the database for pending or booked reservations.
 *
 * @author VonStroheim
 */
class fromGoogle
{
    private $coworkers_requested;
    private $sync_tokens = array();
    private $single_events_mode = FALSE;

    /** @var  Google\Google_Client */
    private static $client;

    /** @var  Google\Google_Service_Calendar */
    private static $service;

    /** @var \TeamBookingSettings */
    private static $settings;

    public function __construct()
    {
        static::$settings = Functions\getSettings();
        // Create a new Google Client object
        static::$client = new Google\Google_Client();
        static::$client->addScope('https://www.googleapis.com/auth/calendar');
        static::$client->setApplicationName(static::$settings->getApplicationProjectName());
        static::$client->setClientId(static::$settings->getApplicationClientId());
        static::$client->setClientSecret(static::$settings->getApplicationClientSecret());
        static::$client->setAccessType('offline');
        // Must be set before the auth call
        static::$service = new Google\Google_Service_Calendar(static::$client);
        foreach (Functions\getSettings()->getCoworkersData() as $coworker_data) {
            foreach ($coworker_data->getCalendars() as $data) {
                $this->sync_tokens[ $data['calendar_id'] ] = $data['sync_token'];
            }
        }
        $this->coworkers_requested = Functions\getAuthCoworkersIdList();
    }

    /**
     * @param $calendar_id
     *
     * @return bool|int
     */
    public static function getCoworkerFromCalendar($calendar_id)
    {
        foreach (Functions\getSettings()->getCoworkersData() as $coworker) {
            foreach ($coworker->getCalendars() as $data) {
                if ($data['calendar_id'] == $calendar_id) {
                    return $coworker->getId();
                }
            }
        }

        return FALSE;
    }

    /**
     * Performs a full-sync of the specified Google Calendar
     *
     * @param $calendar_id
     *
     * @return int|mixed
     */
    public static function fullSyncOf($calendar_id)
    {
        Database\Events::removeCalendar($calendar_id);
        // Extending the timeout limit just in case we are fetching a lot of data
        @set_time_limit(120);

        $results = array('sync_token' => NULL);
        $page_token = FALSE;
        $fetcher = new fromGoogle();
        $coworker_id = $fetcher::getCoworkerFromCalendar($calendar_id);
        $helper = array(
            'coworker_id' => $coworker_id,
            'calendar_id' => $calendar_id,
        );
        while (NULL === $results['sync_token']) {
            $list = $fetcher->getSingleRequest($helper, $page_token);
            if (!$list) return TRUE;
            if ($list instanceof Google\Google_Service_Exception) return $list->getCode();
            if ($list instanceof Google\Google_Auth_Exception) return $list->getCode();
            if (isset($list['nextPageToken'])) $page_token = $list['nextPageToken'];
            foreach ($list->getItems() as $item) {
                Database\Events::insert(array($item), $calendar_id);
                $fetcher::reservationSync($item, $calendar_id);
            }
            if (isset($list['nextSyncToken'])) $results['sync_token'] = $list['nextSyncToken'];
        }
        $coworker_data = Functions\getSettings()->getCoworkerData($coworker_id);
        $coworker_data->addSyncToken($results['sync_token'], $calendar_id);
        Functions\getSettings()->updateCoworkerData($coworker_data);
        Functions\getSettings()->save();

        return TRUE;
    }


    /**
     * Delete past events in Google Calendar to keep it clean
     *
     * @param $calendar_id
     *
     * @return array
     */
    public static function deletePastEventsOf($calendar_id)
    {
        $now = current_time('timestamp', TRUE);
        $fetcher = new fromGoogle();
        $coworker_id = $fetcher::getCoworkerFromCalendar($calendar_id);
        $coworker_data = static::$settings->getCoworkerData($coworker_id);
        static::$client->setUseBatch(TRUE);
        static::$client->setAccessToken($coworker_data->getAccessToken());
        $requests = array();
        $events = Database\Events::getByCalendar($calendar_id);
        foreach ($events[ $coworker_id ][ $calendar_id ] as $event_id => $event) {
            /** @var $event Database\eventObject */
            if (strtotime($event->end) < ($now - WEEK_IN_SECONDS)) {
                try {
                    $requests[ $event_id ] = static::$service->events->delete($calendar_id, $event_id);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        $results = array();
        $fetcher->batchCall($requests, $results);
        $fetcher::fullSyncOf($calendar_id);

        return $results;
    }

    public function sync()
    {
        // Extending the timeout limit just in case we are fetching a lot of data
        @set_time_limit(120);

        #$start = microtime(TRUE);

        // To save bandwidth, we use the batch method
        static::$client->setUseBatch(TRUE);
        $batch_requests = array();
        /**
         * The results array structure will be:
         *
         * [coworker_id]
         *      [calendar_id]
         *           [items]      = array of events
         *           [sync_token] = sync token
         */
        $results = array();
        foreach ($this->coworkers_requested as $coworker_id) {
            $this->prepareCoworkerRequests($coworker_id, $batch_requests);
        }
        while (!empty($batch_requests)) {
            $partial_results = array();
            $this->batchCall($batch_requests, $partial_results);
            $batch_requests = array();
            foreach ($partial_results as $id => $result) {
                /** @var $result Google\Google_Service_Calendar_Events */

                /**
                 * Retrieving important information about this results record,
                 * and getting all the slots from it.
                 *
                 * The results record is identified by an ID that is equal to
                 * the marker we've set for the relative request, plus the
                 * "response-" string in front. The marker, was the serialized helper
                 * array, so in this stage we can easily know which Google Calendar
                 * ID is related to this record.
                 */
                $helper = unserialize(gzinflate(base64_decode(substr($id, 9)))); // Get rid of "response-"
                if ($result instanceof Google\Google_Service_Exception) {
                    /** @var $result Google\Google_Service_Exception */
                    if ($result->getCode() == 410) {
                        // Perform a full-sync
                        unset($this->sync_tokens[ $helper['calendar_id'] ]);
                        static::fullSyncOf($helper['calendar_id']);
                    } else {
                        // Error found
                        if (defined('WP_DEBUG') && WP_DEBUG) {
                            $error_log = new \TeamBooking_ErrorLog();
                            $error_log->setCoworkerId($coworker_id);
                            $error_log->setErrorCode($result->getCode());
                            $error_log->setMessage($result->getMessage());
                            trigger_error("Google API error {$result->getCode()} about coworker {$coworker_id}: {$result->getMessage()} - {$error_log->getDescription()}");
                        }
                    }
                } else {

                    if (isset($results[ $helper['coworker_id'] ][ $helper['calendar_id'] ]['items'])) {
                        foreach ($result->getItems() as $item) {
                            $results[ $helper['coworker_id'] ][ $helper['calendar_id'] ]['items'][] = $item;
                        }
                    } else {
                        $results[ $helper['coworker_id'] ][ $helper['calendar_id'] ]['items'] = $result->getItems();
                    }
                    if (isset($result['nextPageToken'])) {
                        // very important, the original request's sync token must be passed too, that's why we stored it in the helper array
                        $request = $this->getSingleRequest($helper, $result->getNextPageToken(), (isset($helper['sync_token']) ? $helper['sync_token'] : FALSE));
                        $batch_requests[ base64_encode(gzdeflate(serialize($helper))) ] = $request;
                    }
                    if (isset($result['nextSyncToken'])) {
                        $results[ $helper['coworker_id'] ][ $helper['calendar_id'] ]['sync_token'] = $result->getNextSyncToken();
                    }
                }
            }

        }

        #echo '<br>Data fetching time (Google): ' . (microtime(TRUE) - $start);
        #$start = microtime(TRUE);

        // Database update
        $this->databaseSync($results);

        #echo '<br>Data fetching time (Database): ' . (microtime(TRUE) - $start);
    }

    /**
     * @param array $helper_array
     * @param bool  $page_token
     * @param bool  $sync_token
     *
     * @return \Exception|Google\Google_Service_Calendar_Events|Google\Google_Service_Exception|bool
     */
    private function getSingleRequest(array $helper_array, $page_token = FALSE, $sync_token = FALSE)
    {
        $coworker_data = static::$settings->getCoworkerData($helper_array['coworker_id']);
        if (NULL === $coworker_data->getAccessToken()) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                trigger_error("Coworker {$coworker_data->getId()} access token is NULL, skipping Google fetching");
            }

            return FALSE;
        }
        static::$client->setAccessToken($coworker_data->getAccessToken());
        // Set the query params
        $event_list_params = array(
            'singleEvents' => $this->single_events_mode,
            'maxResults'   => 250
        );
        if ($page_token) {
            $event_list_params['pageToken'] = $page_token;
        }
        if ($sync_token) {
            $event_list_params['syncToken'] = $sync_token;
        }
        try {
            return static::$service->events->listEvents($helper_array['calendar_id'], $event_list_params);
        } catch (Google\Google_Service_Exception $e) {
            if ($e->getCode() == 404) {
                // Calendar ID not found
                Database\Events::removeCalendar($helper_array['calendar_id']);
                $coworker_data->dropCalendarId($helper_array['calendar_id']);
                static::$settings->save();
            }

            return $e;
        } catch (\Exception $e) {
            return $e;
        }
    }

    /**
     * @param $coworker_id
     * @param $batch_requests
     */
    private function prepareCoworkerRequests($coworker_id, &$batch_requests)
    {
        $helper_array = array(
            'calendar_id' => '',
            'coworker_id' => '',
        );
        $coworker_data = static::$settings->getCoworkerData($coworker_id);
        static::$client->setAccessToken($coworker_data->getAccessToken());
        $helper_array['coworker_id'] = $coworker_id;
        // Set the query params
        $event_list_params = array(
            'singleEvents' => $this->single_events_mode,
            'maxResults'   => 250
        );
        foreach ($coworker_data->getCalendars() as $calendar_data) {
            if (!isset($this->sync_tokens[ $calendar_data['calendar_id'] ])) continue;
            if (isset($this->sync_tokens[ $calendar_data['calendar_id'] ])) {
                $event_list_params['syncToken'] = $this->sync_tokens[ $calendar_data['calendar_id'] ];
                $helper_array['sync_token'] = $this->sync_tokens[ $calendar_data['calendar_id'] ];
            }
            $helper_array['calendar_id'] = $calendar_data['calendar_id'];
            try {
                $request = static::$service->events->listEvents($calendar_data['calendar_id'], $event_list_params);
            } catch (Google\Google_Service_Exception $e) {
                if ($e->getCode() == 404) {
                    // Calendar ID not found
                    Database\Events::removeCalendar($calendar_data['calendar_id']);
                    $coworker_data->dropCalendarId($calendar_data['calendar_id']);
                    static::$settings->save();
                }
                continue;
            } catch (\Exception $e) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    trigger_error("Google connection error code {$e->getCode()}: {$e->getMessage()}");
                }
                continue;
            }
            // Adding the request to the batch requests
            $batch_requests[ base64_encode(gzdeflate(serialize($helper_array))) ] = $request;
        }
    }

    /**
     * @param $calls
     * @param $partial_results
     */
    private function batchCall($calls, &$partial_results)
    {
        $batches = array_chunk($calls, 50, TRUE);
        foreach ($batches as $requests) {
            $batch = new Google\Google_Http_Batch(static::$client);
            foreach ($requests as $helper => $request) {
                $batch->add($request, $helper);
            }
            try {
                foreach ((array)$batch->execute() as $id => $value) {
                    $partial_results[ $id ] = $value;
                }
            } catch (\Exception $e) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    trigger_error("Google connection error code {$e->getCode()}: {$e->getMessage()}");
                }
                continue;
            }
        }
    }

    public function setRequestedCoworkers(array $coworkers_ids = array())
    {
        if (!empty($coworkers_ids)) {
            $this->coworkers_requested = array_intersect($this->coworkers_requested, $coworkers_ids);
        }
    }

    /**
     * Syncs the local database
     *
     * @param $new_data
     */
    private function databaseSync($new_data)
    {
        $settings = Functions\getSettings();
        $db_events = Database\Events::getAll();
        foreach ($new_data as $coworker_id => $calendars_data) {
            $coworker_data = $settings->getCoworkerData($coworker_id);
            $to_be_removed = array();
            foreach ($calendars_data as $calendar_id => $calendar_data) {
                // Merging items
                if (!empty($calendar_data['items'])) {
                    foreach ($calendar_data['items'] as $item) {
                        /** @var $item Google\Google_Service_Calendar_Event */
                        if ($item->getStatus() === 'cancelled' && !$item->getRecurringEventId()) {
                            // Remove this event from database
                            Database\Events::removeEvent($item->getId(), $calendar_id);
                            $to_be_removed[] = array('calendar' => $calendar_id, 'event' => $item->getId());
                            // TODO: remove the reservation, if allowed
                        } else {
                            // Update or insert the event
                            if (isset($db_events[ $coworker_id ][ $calendar_id ][ $item->getId() ])) {
                                Database\Events::update(array($item), $calendar_id);
                            } else {
                                Database\Events::insert(array($item), $calendar_id);
                            }
                            static::reservationSync($item, $calendar_id);
                        }
                    }
                }
                // Upgrade sync token
                if (isset($calendar_data['sync_token'])) {
                    $coworker_data->addSyncToken($calendar_data['sync_token'], $calendar_id);
                }
            }
            foreach ($to_be_removed as $event) {
                Database\Events::removeRecurrenceRelatedEvents($event['event'], $event['calendar']);
            }
            $settings->updateCoworkerData($coworker_data);
        }
        $settings->save();
    }

    /**
     * Syncs a change in the Google Calendar reserved slot with the local reservation database record
     *
     * @param Google\Google_Service_Calendar_Event $event
     * @param string                               $calendar_id
     */
    public static function reservationSync(Google\Google_Service_Calendar_Event $event, $calendar_id)
    {
        if ($event->getStatus() === 'cancelled') return;

        $reservations = Database\Reservations::getByCalendarEventId($calendar_id, $event->getId());

        if (empty($reservations)) return;

        foreach ($reservations as $id => $reservation) {
            if ($reservation instanceof \TeamBooking_ReservationData) {
                // Check for All Day event and set start/end time
                if ($event->getStart()->getDateTime()) {
                    // RFC3339
                    $start = $event->getStart()->getDateTime();
                    $end = $event->getEnd()->getDateTime();
                } else {
                    // The date is in the format "yyyy-mm-dd"
                    $start = $event->getStart()->getDate();
                    $end = $event->getEnd()->getDate();
                }
                $reservation->setSlotStart($start);
                $reservation->setSlotEnd($end);
                $start = new \DateTime($start);
                $reservation->setStart($start->format('U'));
                $end = new \DateTime($end);
                $reservation->setEnd($end->format('U'));
                try {
                    $service = Database\Services::get($reservation->getServiceId());
                    if ($service->getSettingsFor('location') === 'inherited') {
                        $reservation->setServiceLocation($event->getLocation());
                        // TODO: more things ;)
                    }
                } catch (\Exception $e) {
                    // Do nothing
                }
                Database\Reservations::update($reservation);
            }
        }
    }

}