<?php

namespace TeamBooking\Database;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Functions,
    TeamBooking\Toolkit,
    TeamBooking\RRule\RRule,
    TeamBooking\RRule\RSet,
    TeamBooking\Google;

/**
 * Class Events
 *
 * @author VonStroheim
 */
class Events
{
    private static $relevant_columns = array(
        'coworker_id',
        'calendar_id',
        'event_id',
        'location',
        'summary',
        'status',
        'event_start',
        'event_end',
        'start_timezone',
        'end_timezone',
        'organizer',
        'allday',
        'recurrence',
        'recurring_event_id',
        'creator',
        'description'
    );

    /**
     * @param          $param
     * @param          $value
     * @param int      $min_time
     * @param int|null $max_time
     * @param int      $limit
     *
     * @return array
     */
    private static function getBy($param, $value, $min_time = 0, $max_time = NULL, $limit = 0)
    {
        // TODO: optimize $value to be an array (OR)
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_events';
        $columns = rtrim(implode(', ', static::$relevant_columns), ', ');
        $limit = !$limit ? '' : ' ORDER BY event_start LIMIT ' . $limit;
        if (NULL === $max_time) {
            $prepared_query = $wpdb->prepare("
                SELECT $columns FROM $table_name WHERE $param = %s 
                AND (event_end > %d OR (recurrence IS NOT NULL AND recurrence != 'N;') OR status = 'cancelled')
                " . $limit, array($value, $min_time));
        } else {
            $prepared_query = $wpdb->prepare("
                SELECT $columns FROM $table_name WHERE $param = %s 
                AND (event_end > %d AND event_start < %d) OR (recurrence IS NOT NULL AND recurrence != 'N;' AND event_start < %d) OR status = 'cancelled'
                " . $limit, array($value, $min_time, $max_time, $max_time));
        }
        $results = $wpdb->get_results($prepared_query);
        $return = array();
        $to_be_removed = array();
        foreach ($results as $result) {
            $mapped = Events::map($result, $min_time, $max_time);
            if (is_array($mapped)) {
                // recurring batch
                foreach ($mapped as $event) {
                    if (isset($return[ $result->coworker_id ][ $result->calendar_id ][ $event->id ])) {
                        // a recurring exception is already present, it has priority
                        continue;
                    }
                    $return[ $result->coworker_id ][ $result->calendar_id ][ $event->id ] = $event;
                }
            } else {
                if ($mapped->cancelled) {
                    // cancelled recurring instance
                    $to_be_removed[] = array(
                        'coworker' => $result->coworker_id,
                        'calendar' => $result->calendar_id,
                        'event'    => $result->event_id
                    );
                } else {
                    $return[ $result->coworker_id ][ $result->calendar_id ][ $result->event_id ] = $mapped;
                }
            }
        }

        // removing cancelled recurring instances
        foreach ($to_be_removed as $item) {
            unset($return[ $item['coworker'] ][ $item['calendar'] ][ $item['event'] ]);
        }

        return $return;
    }

    /**
     * @return array
     */
    public static function getAll()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_events';
        $results = $wpdb->get_results("
			SELECT coworker_id, calendar_id, event_id
			FROM $table_name
		");
        $return = array();
        foreach ((array)$results as $result) {
            $return[ $result->coworker_id ][ $result->calendar_id ][ $result->event_id ] = TRUE;
        }

        return $return;
    }

    /**
     * @param string $event_id
     * @param mixed  $min_time
     *
     * @return array
     */
    public static function getByEventId($event_id, $min_time = 0)
    {
        return Events::getBy('event_id', $event_id, $min_time);
    }

    /**
     * @param string $calendar_id
     *
     * @return array
     */
    public static function getByCalendar($calendar_id)
    {
        return Events::getBy('calendar_id', $calendar_id);
    }

    /**
     * @param int  $coworker_id
     * @param int  $min_time
     * @param null $max_time
     * @param int  $limit
     *
     * @return array|mixed
     */
    public static function getByCoworker($coworker_id, $min_time = 0, $max_time = NULL, $limit = 0)
    {
        $events = Events::getBy('coworker_id', $coworker_id, $min_time, $max_time, $limit);

        if (isset($events[ $coworker_id ])) {
            return $events[ $coworker_id ];
        } else {
            return array();
        }
    }

    /**
     * @param Google\Google_Service_Calendar_Event[] $data
     * @param  string                                $calendar_id
     *
     * @return mixed
     */
    public static function insert(array $data, $calendar_id)
    {
        $coworkers = Functions\getAuthCoworkersList();
        $calendars = array();
        foreach ($coworkers as $coworker_id => $coworker_data) {
            foreach ($coworker_data['calendars'] as $calendar) {
                $calendars[ $calendar['calendar_id'] ] = $coworker_id;
            }
        }
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_events';
        $query = "INSERT INTO $table_name (
                coworker_id, calendar_id, event_id, 
                updated, created, color_id, description, 
                hangout_link, html_link, location, 
                recurrence, recurring_event_id, 
                summary, event_start, event_end, 
                organizer, guests, allday, status, creator,
                start_timezone, end_timezone
            ) 
            VALUES ";
        $values = array();
        foreach ($data as $item) {
            if (NULL !== $item->getStart()) {
                if ($item->getStart()->getDateTime()) {
                    $start = $item->getStart()->getDateTime();
                    $end = $item->getEnd()->getDateTime();
                    $allday = 0;
                } else {
                    $start = $item->getStart()->getDate();
                    $end = $item->getEnd()->getDate();
                    $allday = 1;
                }
                $start_timezone = $item->getStart()->getTimeZone();
                $end_timezone = $item->getEnd()->getTimeZone();
            } else {
                if (NULL === $item->getOriginalStartTime()) {
                    $start = 0;
                    $end = 0;
                    $allday = 0;
                    $start_timezone = NULL;
                } elseif ($item->getOriginalStartTime()->getDateTime()) {
                    $start = $item->getOriginalStartTime()->getDateTime();
                    $end = 0;
                    $allday = 0;
                    $start_timezone = $item->getOriginalStartTime()->getTimeZone();
                } else {
                    $start = $item->getOriginalStartTime()->getDate();
                    $end = 0;
                    $allday = 1;
                    $start_timezone = $item->getOriginalStartTime()->getTimeZone();
                }
                $end_timezone = NULL;
            }
            /** @var $creator Google\Google_Service_Calendar_EventCreator */
            $creator = $item->getCreator();
            $values[] = $wpdb->prepare(
                '(%d,%s,%s,%s,%s,%d,%s,%s,%s,%s,%s,%s,%s,%d,%d,%s,%s,%d,%s,%s,%s,%s)',
                $calendars[ $calendar_id ],
                $calendar_id,
                $item->getId(),
                $item->getUpdated(),
                $item->getCreated(),
                $item->getColorId(),
                $item->getDescription(),
                $item->getHangoutLink(),
                $item->getHtmlLink(),
                $item->getLocation(),
                serialize($item->getRecurrence()),
                $item->getRecurringEventId(),
                NULL === $item->getSummary() ? '' : $item->getSummary(),
                strtotime($start),
                strtotime($end),
                NULL !== $item->getOrganizer() ? $item->getOrganizer()->getEmail() : '',
                serialize($item->getAttendees()),
                $allday,
                $item->getStatus(),
                $creator instanceof Google\Google_Service_Calendar_EventCreator ? $creator->getEmail() : '',
                $start_timezone,
                $end_timezone
            );
        }
        $query .= implode(', ', $values);

        return $wpdb->query($query);
    }

    /**
     * @param Google\Google_Service_Calendar_Event[] $data
     * @param  string                                $calendar_id
     *
     * @return int
     */
    public static function update(array $data, $calendar_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_events';
        $i = 0;
        foreach ($data as $item) {
            if (NULL !== $item->getStart()) {
                if ($item->getStart()->getDateTime()) {
                    $start = $item->getStart()->getDateTime();
                    $end = $item->getEnd()->getDateTime();
                    $allday = 0;
                } else {
                    $start = $item->getStart()->getDate();
                    $end = $item->getEnd()->getDate();
                    $allday = 1;
                }
                $start_timezone = $item->getStart()->getTimeZone();
                $end_timezone = $item->getEnd()->getTimeZone();
            } else {
                if (NULL === $item->getOriginalStartTime()) {
                    $start = 0;
                    $end = 0;
                    $allday = 0;
                } elseif ($item->getOriginalStartTime()->getDateTime()) {
                    $start = $item->getOriginalStartTime()->getDateTime();
                    $end = 0;
                    $allday = 0;
                } else {
                    $start = $item->getOriginalStartTime()->getDate();
                    $end = 0;
                    $allday = 1;
                }
                $start_timezone = $item->getOriginalStartTime()->getTimeZone();
                $end_timezone = NULL;
            }
            /** @var $creator Google\Google_Service_Calendar_EventCreator */
            $creator = $item->getCreator();
            $i += $wpdb->update($table_name,
                array(
                    'updated'            => $item->getUpdated(),
                    'color_id'           => $item->getColorId(),
                    'description'        => $item->getDescription(),
                    'hangout_link'       => $item->getHangoutLink(),
                    'html_link'          => $item->getHtmlLink(),
                    'location'           => $item->getLocation(),
                    'recurrence'         => serialize($item->getRecurrence()),
                    'recurring_event_id' => $item->getRecurringEventId(),
                    'summary'            => NULL === $item->getSummary() ? '' : $item->getSummary(),
                    'event_start'        => strtotime($start),
                    'event_end'          => strtotime($end),
                    'start_timezone'     => $start_timezone,
                    'end_timezone'       => $end_timezone,
                    'organizer'          => NULL !== $item->getOrganizer() ? $item->getOrganizer()->getEmail() : '',
                    'guests'             => serialize($item->getAttendees()),
                    'allday'             => $allday,
                    'status'             => $item->getStatus(),
                    'creator'            => $creator instanceof Google\Google_Service_Calendar_EventCreator ? $creator->getEmail() : ''
                ),
                array('event_id' => $item->getId(), 'calendar_id' => $calendar_id)
            );
        }

        return $i;
    }

    /**
     * @param $event_id
     * @param $calendar_id
     *
     * @return mixed
     */
    public static function removeEvent($event_id, $calendar_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_events';

        return $wpdb->delete($table_name, array('event_id' => $event_id, 'calendar_id' => $calendar_id));
    }

    /**
     * @param $event_id
     * @param $calendar_id
     *
     * @return false|int
     */
    public static function removeRecurrenceRelatedEvents($event_id, $calendar_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_events';

        return $wpdb->delete($table_name, array('recurring_event_id' => $event_id, 'calendar_id' => $calendar_id));
    }

    /**
     * @param $calendar_id
     *
     * @return false|int
     */
    public static function removeCalendar($calendar_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_events';

        return $wpdb->delete($table_name, array('calendar_id' => $calendar_id));
    }

    /**
     * @param $coworker_id
     *
     * @return false|int
     */
    public static function removeCoworker($coworker_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_events';

        return $wpdb->delete($table_name, array('coworker_id' => $coworker_id));
    }

    public static function reset()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_events';
        $wpdb->query("TRUNCATE TABLE $table_name");
    }

    /**
     * @param \stdClass $db_object
     * @param           $min_time
     * @param           $max_time
     *
     * @return eventObject|eventObject[]
     */
    private static function map(\stdClass $db_object, $min_time, $max_time)
    {
        if (empty($db_object->recurrence) || $db_object->recurrence === 'N;' || !empty($db_object->recurring_event_id)) {
            $event = new eventObject();
            $event->allday = (bool)$db_object->allday;
            $event->id = $db_object->event_id;
            $event->summary = $db_object->summary;
            $event->description = $db_object->description;
            $event->location = $db_object->location;
            $event->organizer = $db_object->organizer;
            $event->creator = $db_object->creator;
            $date_obj = \DateTime::createFromFormat('U', $db_object->event_start);
            $event->start = $event->allday ? $date_obj->format('Y-m-d') : $date_obj->format(DATE_ATOM);
            $date_obj = \DateTime::createFromFormat('U', $db_object->event_end);
            $event->end = $event->allday ? $date_obj->format('Y-m-d') : $date_obj->format(DATE_ATOM);
            $event->cancelled = $db_object->status === 'cancelled';
            $event->recurring_exception = !empty($db_object->recurring_event_id);

            return $event;
        } else {
            $return = array();
            $recurrence = unserialize($db_object->recurrence);
            if (!$recurrence) return FALSE;
            $slot_duration = $db_object->event_end - $db_object->event_start;
            if (!$min_time) $min_time = $db_object->event_start;
            $end = NULL !== $max_time ? $max_time : $min_time + 2 * YEAR_IN_SECONDS;
            $tail_format = (bool)$db_object->allday ? 'Ymd' : 'Ymd\THis\Z';
            $dtstart = gmdate($tail_format, $db_object->event_start);
            $start_obj = new \DateTime($dtstart);
            if ((bool)$db_object->allday) {
                $start_obj->setTimezone(new \DateTimeZone('UTC'));
            } elseif (!empty($db_object->start_timezone)) {
                $start_obj->setTimezone(Toolkit\getTimezone($db_object->start_timezone));
            } else {
                $start_obj->setTimezone(Toolkit\getTimezone());
            }
            $r_set = new RSet();
            foreach ($recurrence as $rule) {
                try {
                    $rule_array = RRule::parseRfcString('DTSTART:' . $dtstart . "\n" . $rule);
                    $rule_array['DTSTART'] = $start_obj;
                    $rrule = new RRule($rule_array);
                    $r_set->addRRule($rrule);
                } catch (\Exception $e) {
                    list($property_name, $property_value) = explode(':', $rule);
                    $tmp = explode(';', $property_name);
                    $property_name = $tmp[0];
                    $property_params = array();
                    array_splice($tmp, 0, 1);
                    foreach ($tmp as $pair) {
                        if (strpos($pair, '=') === FALSE) {
                            continue;
                        }
                        list($key, $value) = explode('=', $pair);
                        $property_params[ $key ] = $value;
                    }
                    if ($property_name === 'EXDATE') {
                        $tz = NULL;
                        $dates = explode(',', $property_value);
                        if (isset($property_params['TZID'])) {
                            $tz = new \DateTimeZone($property_params['TZID']);
                        }
                        foreach ($dates as $date) {
                            if (strpos($date, 'T') === FALSE
                                || strpos($date, 'Z') !== FALSE
                            ) {
                                $r_set->addExDate(new \DateTime($date));
                            } else {
                                $r_set->addExDate(new \DateTime($date, $tz));
                            }
                        }
                    }
                }
            }

            /** we need to "clean" the event id from any _R[...] suffix, see:
             * https://stackoverflow.com/questions/42300977/when-recurring-id-is-generated-with-format-previous-event-id-rdate
             */
            $event_id_cleaned = explode('_R', $db_object->event_id);
            $event_id_cleaned = $event_id_cleaned[0];

            foreach ($r_set->getOccurrencesBetween((int)$min_time - 26 * HOUR_IN_SECONDS, $end) as $occurrence) {
                /** @var $occurrence \DateTime */
                $event = new eventObject();
                $event->allday = (bool)$db_object->allday;
                $event->id = $event_id_cleaned . '_' . gmdate($tail_format, $occurrence->getTimestamp());
                $event->summary = $db_object->summary;
                $event->description = $db_object->description;
                $event->location = $db_object->location;
                $event->organizer = $db_object->organizer;
                $event->creator = $db_object->creator;
                $date_obj = \DateTime::createFromFormat('U', $occurrence->getTimestamp());
                $event->start = $event->allday ? $date_obj->format('Y-m-d') : $date_obj->format(DATE_ATOM);
                $date_obj = \DateTime::createFromFormat('U', $occurrence->getTimestamp() + $slot_duration);
                $event->end = $event->allday ? $date_obj->format('Y-m-d') : $date_obj->format(DATE_ATOM);
                $return[] = $event;
            }

            return $return;
        }
    }
}

/**
 * Class eventObject
 *
 * @author VonStroheim
 */
class eventObject
{
    public $id;
    public $summary;
    public $description;
    public $location;
    public $organizer;
    public $creator;
    public $start;
    public $end;
    public $allday;
    public $recurring_exception = FALSE;
    public $cancelled = FALSE;
}