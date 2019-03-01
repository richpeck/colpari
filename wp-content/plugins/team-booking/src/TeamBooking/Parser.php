<?php

namespace TeamBooking;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Database\eventObject;

/**
 * Class Parser
 *
 * The role of the parser is to parse all the events
 * from Google Calendars and map them to TeamBooking slots.
 *
 * The parser also scans the database for pending or booked reservations.
 *
 * @author VonStroheim
 */
class Parser
{
    private $services_requested;
    private $coworkers_requested;
    private $min_time_requested;
    private $max_time_requested;

    private $timezone;
    private $service_matches;
    /** @var Slot[] */
    private $slots_booked;
    /** @var Slot[] */
    private $slots_free;
    private $slots_containers;
    /** @var Slot[] */
    private $unrelated_events;
    /** @var  \TeamBooking_ReservationData[] */
    private $reservations_in_database;

    /** @var \TeamBookingSettings */
    private static $settings;

    public function __construct($reservations_in_database)
    {
        $this->slots_booked = array();
        $this->slots_containers = array();
        $this->unrelated_events = array();
        $this->reservations_in_database = $reservations_in_database;
        static::$settings = Functions\getSettings();
        $this->timezone = Toolkit\getTimezone();
        $this->coworkers_requested = Functions\getAuthCoworkersIdList();
        $this->services_requested = Functions\getSettings()->getServiceIdList();
        $this->service_matches = $this->getNameMatches();
    }

    /**
     * @param array $services_ids
     */
    public function setRequestedServices(array $services_ids = array())
    {
        if (!empty($services_ids)) {
            $this->services_requested = array_intersect($this->services_requested, $services_ids);
        }
    }

    /**
     * @param array $coworkers_ids
     */
    public function setRequestedCoworkers(array $coworkers_ids = array())
    {
        if (!empty($coworkers_ids)) {
            $this->coworkers_requested = array_intersect($this->coworkers_requested, $coworkers_ids);
        }
    }

    /**
     * @param mixed $time
     */
    public function setRequestedMinTime($time = NULL)
    {
        /**
         * Applying a cut to min time.
         *
         * We're going to do this, to avoid useless fetching of
         * past Google Calendar events (this would happen if the
         * function is called to retrieve current month slots).
         */

        if (\TeamBooking\Actions\parser_min_time_cut(TRUE) &&
            strtotime($time) < current_time('timestamp')) {
            $now = new \DateTime();
            $now->setTimezone($this->timezone);
            $time = $now->format(DATE_ATOM);
        }
        $this->min_time_requested = $time;
    }

    /**
     * @param mixed $time
     */
    public function setRequestedMaxTime($time = NULL)
    {
        $this->max_time_requested = $time;
    }

    /**
     * @param int $limit
     *
     * @return Slot[]
     */
    public function getSlots($limit = 0)
    {
        // Map the slots from the events in database
        $this->mapSlotsFromEvents($limit);
        // Fetch waiting and pending reservations
        $this->fetchWaitingPendingFromDatabase();
        // Split the containers
        $this->splitContainers();
        // Check overlapping events
        $this->dropOverlapping();
        // Post processing of booked slots
        $this->postProcessBookedSlots();
        // Post processing of free slots
        $this->postProcessFreeSlots();

        return array_merge($this->slots_free, $this->slots_booked);

    }

    /**
     * @param int $limit
     *
     * @throws \Exception
     */
    private function mapSlotsFromEvents($limit = 0)
    {
        $min_time = \DateTime::createFromFormat(DATE_ATOM, $this->min_time_requested);
        $max_time = NULL === $this->max_time_requested ? NULL : strtotime($this->max_time_requested);
        /**
         * Let's start the coworker loop
         */
        foreach ($this->coworkers_requested as $coworker_id) {
            $events = Database\Events::getByCoworker($coworker_id, $min_time->format('U'), $max_time, $limit);
            /**
             * Let's start the events loop
             */
            foreach ($events as $calendar_id => $items) {
                foreach ($items as $event_id => $event) {
                    /** @var $event eventObject */
                    $unique_id_prefix = $event->id . $event->organizer;

                    if (isset($this->service_matches[ $coworker_id ][ static::parseSummary($event->summary) ])) {
                        $match = $this->service_matches[ $coworker_id ][ static::parseSummary($event->summary) ];

                        // check service and coworker status
                        if (!Database\Services::get($match['service'])->isActive()
                            || !static::$settings->getCoworkerData($coworker_id)->isServiceAllowed($match['service'])
                            || !static::$settings->getCoworkerData($coworker_id)->getCustomEventSettings($match['service'])->isParticipate()
                        ) {
                            continue;
                        }

                        $slot = Slot::getFromEvent($event, $match['service'], $coworker_id);
                        $custom_time_bounds = $this->getCoworkerTimeBoundaries($coworker_id, $match['service'], $this->min_time_requested);

                        if ($custom_time_bounds->reference === 'start' && $match['status'] !== 'booked') {
                            $time = \DateTime::createFromFormat($slot->isAllDay() ? 'Y-m-d' : DATE_ATOM, $slot->getStartTime());
                        } else {
                            $time = \DateTime::createFromFormat($slot->isAllDay() ? 'Y-m-d' : DATE_ATOM, $slot->getEndTime());
                        }

                        if ($slot->isAllDay()) $time->setTime(0, 0);

                        if ($time->format('U') < strtotime($custom_time_bounds->min_time)) continue;

                        if ($match['status'] !== 'booked') {
                            $slot->setBookableUntil($time->format('U') - $custom_time_bounds->min_interval);
                        }

                        if ($custom_time_bounds->open_time !== 0) {
                            if (strtotime($slot->getStartTime()) > strtotime($custom_time_bounds->open_time)) continue;
                        }

                        if ($match['status'] === 'free') {
                            $this->slots_free[ $unique_id_prefix . $match['service'] ] = $slot;
                        }
                        if ($match['status'] === 'booked') {
                            $slot->setSoldout();
                            $this->slots_booked[ $unique_id_prefix . $match['service'] ] = $slot;
                        }
                    } else {
                        // Is it a container?
                        if (substr(static::parseSummary($event->summary), -10) === ' container') {
                            $services_array = array_map('trim', explode('+', substr(static::parseSummary($event->summary), 0, -10)));

                            foreach ($services_array as $service_name) {
                                if (isset($this->service_matches[ $coworker_id ][ strtolower(trim($service_name)) ])) {
                                    $match = $this->service_matches[ $coworker_id ][ strtolower(trim($service_name)) ];

                                    // check the duration rule compatibility
                                    if (Database\Services::get($match['service'])->getSettingsFor('slot_duration') === 'inherited'
                                        || (
                                            Database\Services::get($match['service'])->getSettingsFor('slot_duration') === 'coworker'
                                            && static::$settings->getCoworkerData($coworker_id)->getCustomEventSettings($match['service'])->getDurationRule() === 'inherited'
                                        )
                                    ) {
                                        continue; //it is "inherited", so skip the container
                                    }

                                    // check service and coworker status
                                    if (!Database\Services::get($match['service'])->isActive()
                                        || !static::$settings->getCoworkerData($coworker_id)->getCustomEventSettings($match['service'])->isParticipate()
                                        || !static::$settings->getCoworkerData($coworker_id)->isServiceAllowed($match['service'])
                                    ) {
                                        continue;
                                    }
                                    $slot = Slot::getContainerFromEvent($event, $match['service'], $coworker_id, $services_array);
                                    if ($match['status'] === 'booked') {
                                        $slot->setSoldout();
                                    }
                                    $time = \DateTime::createFromFormat($slot->isAllDay() ? 'Y-m-d' : DATE_ATOM, $slot->getEndTime());
                                    if ($time >= $min_time) {
                                        $this->slots_containers[ $unique_id_prefix . $match['service'] ] = $slot;
                                    }
                                }
                            }
                        } else {
                            // At this point, we should have left here all unrelated events
                            // TODO: multiple services slots (not containers)
                            // TODO: is stripos() more efficient?

                            // Adding unrelated events boundaries
                            $slot = new Slot();
                            #$slot->setTimezone($event->getStart()->getTimezone());
                            $slot->setAllDay($event->allday);
                            $slot->setEndTime($event->end);
                            $slot->setStartTime($event->start);
                            $slot->setCalendarId($calendar_id);
                            $slot->setCoworkerId($coworker_id);
                            $slot->setEventId($event->id);
                            if (\TeamBooking\Functions\tb_mb_strtolower($event->creator) !== \TeamBooking\Functions\tb_mb_strtolower(static::$settings->getCoworkerData($coworker_id)->getAuthAccount())) {
                                $slot->setReadOnly(TRUE);
                                $slot->canBeEdited(FALSE);
                            }
                            $this->unrelated_events[ $calendar_id . $event->id ] = $slot;
                        }
                    }

                }
            }
        }
    }

    /**
     * TODO: This should be REFACTORED! Everything must be done via Google Calendar
     *
     * @throws \Exception
     */
    private function fetchWaitingPendingFromDatabase()
    {
        $min_time = \DateTime::createFromFormat(DATE_ATOM, $this->min_time_requested);

        foreach ($this->reservations_in_database as $id => $reservation) {
            /* @var $reservation \TeamBooking_ReservationData */

            try {
                $service = Database\Services::get($reservation->getServiceId());
            } catch (\Exception $e) {
                continue;
            }

            // Waiting for approval reservations
            if ($reservation->isWaitingApproval()
                && $service->isActive()
                && !$service->getSettingsFor('free_until_approval')
                && (
                    (
                        // Container derived, "first of his name"
                        $reservation->getGoogleCalendarEventParent()
                        && $reservation->getGoogleCalendarEvent() == NULL
                    )
                    ||
                    (
                        // Single slot derived
                        !$reservation->getGoogleCalendarEventParent()
                        && $reservation->getGoogleCalendarEvent()
                    )
                )
            ) {
                $slot = Mappers\reservationDataToSlot($reservation);

                if (static::$settings->getCoworkerData($slot->getCoworkerId())->getCustomEventSettings($slot->getServiceId())->getMinTimeReference() === 'start') {
                    $time = \DateTime::createFromFormat($slot->isAllDay() ? 'Y-m-d' : DATE_ATOM, $slot->getStartTime());
                } else {
                    $time = \DateTime::createFromFormat($slot->isAllDay() ? 'Y-m-d' : DATE_ATOM, $slot->getEndTime());
                }
                if ($time < $min_time) continue;

                if ($service->getClass() === 'event') {
                    if ($reservation->getGoogleCalendarEventParent()) {
                        // In order to be computed
                        $this->slots_free[ $slot->getUniqueId() ] = $slot;
                    }
                } else {
                    $this->slots_booked[ $slot->getUniqueId() ] = $slot;
                    // discard the free one, very important
                    unset($this->slots_free[ $slot->getEventId() . $slot->getCalendarId() . $slot->getServiceId() ]);
                }
            }

            // Pending reservations
            if ($reservation->isPending()
                && !Functions\isReservationTimedOut($reservation)
                && Database\Services::get($reservation->getServiceId())->isActive()
                && (
                    (
                        // Container derived, "first of his name"
                        $reservation->getGoogleCalendarEventParent()
                        && $reservation->getGoogleCalendarEvent() == NULL
                    )
                    ||
                    (
                        // Single slot derived
                        !$reservation->getGoogleCalendarEventParent()
                        && $reservation->getGoogleCalendarEvent()
                    )
                )
            ) {
                $slot = Mappers\reservationDataToSlot($reservation);
                if (Database\Services::get($slot->getServiceId())->getClass() === 'event') {
                    if ($reservation->getGoogleCalendarEventParent()) {
                        // In order to be computed
                        $this->slots_free[ $slot->getUniqueId() ] = $slot;
                    }
                } else {
                    $this->slots_booked[ $slot->getUniqueId() ] = $slot;
                    // discard the free one, very important
                    unset($this->slots_free[ $slot->getEventId() . $slot->getCalendarId() . $slot->getServiceId() ]);
                }
            }
        }
    }

    /**
     * Splits the containers into slots
     */
    private function splitContainers()
    {
        /**
         * During the reservation, the absence of an event ID
         * will be used to tell TeamBooking to create a new event
         * in Google Calendar instead of performing an update
         * (until a first reservation took place, all the slots in
         * a container are just mere abstractions).
         */

        $slots_to_be_added = array();

        foreach ($this->slots_containers as $slot) {

            /** @var $slot Slot */
            $slot->setAllDay(FALSE);
            $service_id = $slot->getServiceId();
            if (!in_array($service_id, $this->services_requested)) continue;
            try {
                $service = Database\Services::get($service_id);
            } catch (\Exception $e) {
                continue;
            }
            $coworker_data = static::$settings->getCoworkerData($slot->getCoworkerId());
            $buffer = $coworker_data->getCustomEventSettings($service_id)->getBufferDuration(); // seconds
            $buffer_rule = $coworker_data->getCustomEventSettings($service_id)->getBufferDurationRule();

            /**
             * We're looking for possibly "contained" slots.
             * This is done using the "booked" array
             * for the "Appointment" class, and the "free"
             * for the "Event" class.
             *
             * Let's set the time limits for this container.
             */
            $start_time_obj = new \DateTime($slot->getStartTime(), $this->timezone);
            $start_time_obj->setTimezone($this->timezone);
            $end_time_obj = new \DateTime($slot->getEndTime(), $this->timezone);
            $end_time_obj->setTimezone($this->timezone);

            // Declare the contained slots array
            $contained_booked_slots = array();

            /* Getting the multiple service names array */
            $multiple_services = $slot->getMultipleServices();

            if (!empty($multiple_services)
                || $service->getClass() === 'appointment'
            ) {
                /**
                 * Let's browse the "booked" array.
                 */
                foreach ($this->slots_booked as $key => $booked_slot) {
                    if ($slot->getCoworkerId() !== $booked_slot->getCoworkerId()) continue;
                    /**
                     * Check for the service id(s)
                     *
                     * A non-empty services array means that the container is multi-service.
                     * So we need to collect all the contained booked slots
                     * of all the services.
                     *
                     * An empty services array means that the container is single-service.
                     * So it's sufficient to check against the service id returned by
                     * getServiceId() method.
                     */
                    if ($slot->getCalendarId() !== $booked_slot->getCalendarId()) {
                        // The calendar ids don't match: skip if at least one of them is independent
                        if ($coworker_data->getCalendar($slot->getCalendarId())->independent === TRUE
                            || $coworker_data->getCalendar($booked_slot->getCalendarId())->independent === TRUE
                        ) {
                            continue;
                        }
                    }
                    if (!empty($multiple_services)) {
                        if (!in_array(strtolower($coworker_data->getCustomEventSettings($booked_slot->getServiceId())->getLinkedEventTitle()), $multiple_services)) {
                            // The booked slot service id is not contained in the array: skip
                            continue;
                        }
                    } else {
                        if ($service_id !== $booked_slot->getServiceId()) {
                            // The service ids don't match, skip
                            continue;
                        }
                    }
                    // Let's set the current booked slot's time limits
                    $start_time_obj_booked = new \DateTime($booked_slot->getStartTime(), $this->timezone);
                    $start_time_obj_booked->setTimezone($this->timezone);
                    $end_time_obj_booked = new \DateTime($booked_slot->getEndTime(), $this->timezone);
                    $end_time_obj_booked->setTimezone($this->timezone);

                    /**
                     * How can we determine if the booked slot is contained
                     * in this container? We're gonna do this by checking two
                     * conditions:
                     *
                     * 1. The start time of the booked slot must be greater than
                     * or equal to the container's start time.
                     *
                     * 2. The end time of the booked slot must be less than
                     * or equal to the container's end time.
                     *
                     * Why aren't we considering the possibility of partially-contained
                     * slots (which meet only one of the conditions)? At this stage,
                     * we're assuming that a contained slot is there because it was
                     * originated by this very container.
                     *
                     * TODO: what if the container was modified, in the meantime?
                     * TODO: What if the reservations are manually adjusted?
                     */
                    if ($start_time_obj_booked->format('U') >= $start_time_obj->format('U')
                        && $end_time_obj_booked->format('U') <= $end_time_obj->format('U')
                    ) {
                        // Conditions are both verified
                        $contained_booked_slots[] = $booked_slot;
                    }
                }
            }

            if (!empty($multiple_services)
                || $service->getClass() === 'event'
            ) {
                /**
                 * Let's browse the $return_free array
                 */
                foreach ((array)$this->slots_free as $potential_booked_slot) {
                    if ($slot->getCoworkerId() !== $potential_booked_slot->getCoworkerId()) continue;
                    // Check for the service id
                    if (empty($multiple_services)) {
                        if ($service_id != $potential_booked_slot->getServiceId()) {
                            // The service id is not the same, skipping
                            continue;
                        }
                    } else {
                        if (!in_array(strtolower($coworker_data->getCustomEventSettings($potential_booked_slot->getServiceId())->getLinkedEventTitle()), $multiple_services)) {
                            continue;
                        }
                    }

                    // Let's set the current booked slot's time limits
                    $start_time_obj_booked = new \DateTime($potential_booked_slot->getStartTime(), $this->timezone);
                    $start_time_obj_booked->setTimezone($this->timezone);
                    $end_time_obj_booked = new \DateTime($potential_booked_slot->getEndTime(), $this->timezone);
                    $end_time_obj_booked->setTimezone($this->timezone);

                    // the potential booked slot is contained in this container?
                    if ($start_time_obj_booked->format('U') >= $start_time_obj->format('U')
                        && $end_time_obj_booked->format('U') <= $end_time_obj->format('U')
                    ) {
                        // Conditions are both verified
                        $contained_booked_slots[] = $potential_booked_slot;
                    }
                }
            }
            /**
             * Now, the $contained_booked_slots array is filled with
             * unordered contained slots relative to this container
             * and this service id. Let's order the slots by time.
             */
            $contained_booked_slots = $this->sortSlotsByTime($contained_booked_slots);

            /**
             * We need to know the duration of the slots inside the
             * containers. Remember, if the duration rule is set to "inherit",
             * the container would never exists in the first place,
             * so the $duration variable should never stay FALSE, actually.
             */
            $duration = FALSE;
            if ($service->getSettingsFor('slot_duration') === 'fixed') {
                $duration = $service->getSlotDuration(); // seconds
            } elseif ($service->getSettingsFor('slot_duration') === 'coworker') {
                if ($coworker_data->getCustomEventSettings($service_id)->getDurationRule() === 'fixed') {
                    $duration = $coworker_data->getCustomEventSettings($service_id)->getFixedDuration(); // seconds
                }
            }

            if (!$duration) {
                /**
                 * This is not a possibility. Let's skip the container
                 * for security pourposes. Below, there is a split loop
                 * commented, for further development in case of dynamic
                 * duration, or customer-defined duration.
                 */
                continue;
                //  $start_time_spool = $slot->getStartTime();
                //  foreach ($contained_booked_slots as $booked_slot) {
                //      if (strtotime($booked_slot->getStartTime()) > strtotime($start_time_spool)) {
                //          $slot_to_add = clone $slot;
                //          $slot_to_add->setEventIdParent($slot->getEventId());
                //          $slot_to_add->setEventId(FALSE);
                //          $slot_to_add->setContainerFalse();
                //          $slot_to_add->setStartTime($start_time_spool);
                //          $slot_to_add->setEndTime($booked_slot->getStartTime());
                //          $slots_to_be_added[] = $slot_to_add;
                //          // Set a new time spool, taking buffer into account
                //          $start_time_spool = date_i18n('c', strtotime($booked_slot->getEndTime()) + $buffer);
                //      } else {
                //          if (strtotime($booked_slot->getEndTime()) > strtotime($start_time_spool)) {
                //              $start_time_spool = $booked_slot->getEndTime();
                //          }
                //      }
                //  }
                //  // residual
                //  if (strtotime($start_time_spool) < strtotime($slot->getEndTime())) {
                //      $slot_to_add = clone $slot;
                //      $slot_to_add->setContainerFalse();
                //      $slot_to_add->setEventIdParent($slot->getEventId());
                //      $slot_to_add->setEventId(FALSE);
                //      $slot_to_add->setStartTime($start_time_spool);
                //      $slots_to_be_added[] = $slot_to_add;
                //  }
            } else {

                /**
                 * Let's start the split loop, based on
                 * duration and buffer values.
                 */
                $start_time_spool = strtotime($start_time_obj->format(DATE_ATOM));
                $end_time_spool = strtotime($end_time_obj->format(DATE_ATOM));
                $time_boundaries = $this->getCoworkerTimeBoundaries($slot->getCoworkerId(), $slot->getServiceId(), $this->min_time_requested);

                foreach ($contained_booked_slots as $booked_slot) {
                    // The buffer duration must be the one relative to the service of the booked slot
                    $buffer_back = empty($multiple_services)
                        ? $buffer
                        : $coworker_data->getCustomEventSettings($booked_slot->getServiceId())->getBufferDuration();
                    if (strtotime($booked_slot->getStartTime()) >= $start_time_spool) {
                        while ((strtotime($booked_slot->getStartTime()) - $start_time_spool) >= ($buffer + $duration)) {
                            $slot_to_add = clone $slot;
                            $slot_to_add->setReadOnly($service->getSettingsFor('bookable') === 'nobody');
                            $slot_to_add->setEventIdParent($slot->getEventId());
                            $slot_to_add->setEventId(FALSE);
                            $slot_to_add->setContainer(FALSE);
                            $slot_to_add->setStartTime(date_i18n('c', $start_time_spool));
                            $slot_to_add->setEndTime(date_i18n('c', $start_time_spool + $duration));
                            $must_skip = FALSE;
                            if ($time_boundaries->reference === 'end') {
                                // The reference for the min_time is the slot's end time
                                $time = strtotime($slot_to_add->getEndTime());
                                if ($time < strtotime($time_boundaries->min_time)) {
                                    // Skip this slot
                                    $must_skip = TRUE;
                                }
                            } else {
                                // The reference for the min_time is the slot's start time
                                $time = strtotime($slot_to_add->getStartTime());
                                if ($time < strtotime($time_boundaries->min_time)) {
                                    // Skip this slot
                                    $must_skip = TRUE;
                                }
                            }
                            $slot_to_add->setBookableUntil($time - $time_boundaries->min_interval);

                            if ($time_boundaries->open_time !== 0) {
                                if (strtotime($slot_to_add->getStartTime()) > strtotime($time_boundaries->open_time)) $must_skip = TRUE;
                            }
                            if (!$must_skip) {
                                $slots_to_be_added[] = $slot_to_add;
                            }
                            // Set a new time spool, taking buffer into account
                            $start_time_spool += $duration;
                            if ($buffer_rule === 'always') {
                                $start_time_spool += $buffer;
                            }
                        }

                        // how to treat discarded slots
                        if ($service_id !== $booked_slot->getServiceId()
                            && $service->getSettingsFor('treat_discarded_free_slots') === 'booked'
                        ) {
                            while ((strtotime($booked_slot->getEndTime()) - $start_time_spool) >= ($buffer + $duration)) {
                                $slot_to_add = clone $slot;
                                $slot_to_add->setReadOnly($service->getSettingsFor('bookable') === 'nobody');
                                $slot_to_add->setEventIdParent($slot->getEventId());
                                $slot_to_add->setEventId(FALSE);
                                $slot_to_add->setContainer(FALSE);
                                $slot_to_add->setStartTime(date_i18n('c', $start_time_spool));
                                $slot_to_add->setEndTime(date_i18n('c', $start_time_spool + $duration));
                                $slot_to_add->setSoldout();
                                $must_skip = FALSE;
                                if ($time_boundaries->reference === 'end') {
                                    // The reference for the min_time is the slot's end time
                                    if (strtotime($slot_to_add->getEndTime()) < strtotime($time_boundaries->min_time)) {
                                        // Skip this slot
                                        $must_skip = TRUE;
                                    }
                                } else {
                                    // The reference for the min_time is the slot's start time
                                    if (strtotime($slot_to_add->getStartTime()) < strtotime($time_boundaries->min_time)) {
                                        // Skip this slot
                                        $must_skip = TRUE;
                                    }
                                }
                                if ($time_boundaries->open_time !== 0) {
                                    if (strtotime($slot_to_add->getStartTime()) > strtotime($time_boundaries->open_time)) $must_skip = TRUE;
                                }
                                if (!$must_skip) {
                                    $slots_to_be_added[] = $slot_to_add;
                                }
                                // Set a new time spool, taking buffer into account
                                $start_time_spool = $start_time_spool + $duration + $buffer;
                            }
                        }

                        // Set a new time spool, taking buffer_back into account
                        $start_time_spool = strtotime($booked_slot->getEndTime()) + $buffer_back;
                    } else {
                        if (strtotime($booked_slot->getEndTime()) > $start_time_spool) {

                            // how to treat discarded slots
                            if ($service_id !== $booked_slot->getServiceId()
                                && $service->getSettingsFor('treat_discarded_free_slots') === 'booked'
                            ) {
                                while ((strtotime($booked_slot->getEndTime()) - $start_time_spool) >= ($buffer + $duration)) {
                                    $slot_to_add = clone $slot;
                                    $slot_to_add->setReadOnly($service->getSettingsFor('bookable') === 'nobody');
                                    $slot_to_add->setEventIdParent($slot->getEventId());
                                    $slot_to_add->setEventId(FALSE);
                                    $slot_to_add->setContainer(FALSE);
                                    $slot_to_add->setStartTime(date_i18n('c', $start_time_spool));
                                    $slot_to_add->setEndTime(date_i18n('c', $start_time_spool + $duration));
                                    $slot_to_add->setSoldout();
                                    $must_skip = FALSE;
                                    if ($time_boundaries->reference === 'end') {
                                        // The reference for the min_time is the slot's end time
                                        if (strtotime($slot_to_add->getEndTime()) < strtotime($time_boundaries->min_time)) {
                                            // Skip this slot
                                            $must_skip = TRUE;
                                        }
                                    } else {
                                        // The reference for the min_time is the slot's start time
                                        if (strtotime($slot_to_add->getStartTime()) < strtotime($time_boundaries->min_time)) {
                                            // Skip this slot
                                            $must_skip = TRUE;
                                        }
                                    }
                                    if ($time_boundaries->open_time !== 0) {
                                        if (strtotime($slot_to_add->getStartTime()) > strtotime($time_boundaries->open_time)) $must_skip = TRUE;
                                    }
                                    if (!$must_skip) {
                                        $slots_to_be_added[] = $slot_to_add;
                                    }
                                    // Set a new time spool, taking buffer into account
                                    $start_time_spool = $start_time_spool + $duration + $buffer;
                                }
                            }

                            $start_time_spool = strtotime($booked_slot->getEndTime()) + $buffer_back;
                        }
                    }
                }
                // residual
                if ($start_time_spool < $end_time_spool) {
                    while (($end_time_spool - $start_time_spool) >= $duration) {
                        $slot_to_add = clone $slot;
                        $slot_to_add->setReadOnly($service->getSettingsFor('bookable') === 'nobody');
                        $slot_to_add->setEventIdParent($slot->getEventId());
                        $slot_to_add->setEventId(FALSE);
                        $slot_to_add->setContainer(FALSE);
                        $slot_to_add->setStartTime(date_i18n('c', $start_time_spool));
                        $slot_to_add->setEndTime(date_i18n('c', $start_time_spool + $duration));
                        $must_skip = FALSE;
                        if ($time_boundaries->reference === 'end') {
                            // The reference for the min_time is the slot's end time
                            $time = strtotime($slot_to_add->getEndTime());
                            if ($time < strtotime($time_boundaries->min_time)) {
                                // Skip this slot
                                $must_skip = TRUE;
                            }
                        } else {
                            // The reference for the min_time is the slot's start time
                            $time = strtotime($slot_to_add->getStartTime());
                            if ($time < strtotime($time_boundaries->min_time)) {
                                // Skip this slot
                                $must_skip = TRUE;
                            }
                        }
                        $slot_to_add->setBookableUntil($time - $time_boundaries->min_interval);

                        if ($time_boundaries->open_time !== 0) {
                            if (strtotime($slot_to_add->getStartTime()) > strtotime($time_boundaries->open_time)) $must_skip = TRUE;
                        }
                        if (!$must_skip) {
                            $slots_to_be_added[] = $slot_to_add;
                        }
                        // Set a new time spool, taking buffer into account
                        $start_time_spool += $duration;
                        if ($buffer_rule === 'always') {
                            $start_time_spool += $buffer;
                        }
                    }
                }
            }
        }

        $this->slots_free = array_merge((array)$this->slots_free, $slots_to_be_added);
    }

    private function dropOverlapping()
    {
        $booked_to_add = array();
        foreach ($this->slots_free as $key => $slot) {
            $coworker = static::$settings->getCoworkerData($slot->getCoworkerId());
            $coworker_data = $coworker->getCustomEventSettings($slot->getServiceId());
            // dropping unrelated events...
            if ($coworker_data->dealWithUnrelatedEvents()) {
                foreach ($this->unrelated_events as $unr_slot) {
                    if ($slot->getCoworkerId() !== $unr_slot->getCoworkerId()) continue;
                    if ($unr_slot->getCalendarId() !== $slot->getCalendarId()) {
                        // The calendar ids don't match: skip if at least one of them is independent
                        if ($coworker->getCalendar($unr_slot->getCalendarId())->independent === TRUE
                            || $coworker->getCalendar($slot->getCalendarId())->independent === TRUE
                        ) {
                            continue;
                        }
                    }
                    if ($this->checkOverlap($slot, $unr_slot)) {
                        try {
                            $service = Database\Services::get($slot->getServiceId());
                            if ($service->getSettingsFor('treat_discarded_free_slots') === 'booked') {
                                $booked_to_add[ $key ] = $slot;
                            }
                        } catch (\Exception $e) {
                            //nothing
                        }
                        unset($this->slots_free[ $key ]);
                    }
                }
            }
            // dropping booked services...
            if ($coworker_data->dealWithBookedOfOtherServices()
                || $coworker_data->dealWithBookedOfSameService()
            ) {
                foreach ($this->slots_booked as $booked_slot) {
                    if ($slot->getCoworkerId() !== $booked_slot->getCoworkerId()) continue;
                    if ($booked_slot->getCalendarId() !== $slot->getCalendarId()) {
                        // The calendar ids don't match: skip if at least one of them is independent
                        if ($coworker->getCalendar($booked_slot->getCalendarId())->independent === TRUE
                            || $coworker->getCalendar($slot->getCalendarId())->independent === TRUE
                        ) {
                            continue;
                        }
                    }
                    // case 1, the service is the same
                    if (!$coworker_data->dealWithBookedOfSameService()
                        && $booked_slot->getServiceId() === $slot->getServiceId()
                    ) {
                        continue;
                    }
                    // case 2, the service is not the same
                    if (!$coworker_data->dealWithBookedOfOtherServices()
                        && $booked_slot->getServiceId() !== $slot->getServiceId()
                    ) {
                        continue;
                    }
                    if ($this->checkOverlap($slot, $booked_slot)) {
                        try {
                            $service = Database\Services::get($slot->getServiceId());
                            if ($service->getSettingsFor('treat_discarded_free_slots') === 'booked') {
                                $booked_to_add[ $key ] = $slot;
                            }
                        } catch (\Exception $e) {
                            //nothing
                        }
                        unset($this->slots_free[ $key ]);
                    }
                }
            }
        }
        foreach ($booked_to_add as $key => $slot) {
            /** @var $slot Slot */
            $slot->setSoldout();
            $this->slots_booked[ $key ] = $slot;
        }
    }

    /**
     * @param array $slots
     *
     * @return Slot[]
     */
    private function sortSlotsByTime(array $slots)
    {
        uasort($slots, function ($a, $b) {
            return strtotime($a->getStartTime()) > strtotime($b->getStartTime());
        });

        return $slots;
    }

    /**
     * @return array
     */
    private function getNameMatches()
    {
        $matches = array();
        foreach ($this->coworkers_requested as $coworker_id) {
            $coworker_data = static::$settings->getCoworkerData($coworker_id);
            foreach (Functions\getSettings()->getServiceIdList(TRUE) as $service_id) {
                $service_data = $coworker_data->getCustomEventSettings($service_id);
                $matches[ $coworker_id ][ trim(\TeamBooking\Functions\tb_mb_strtolower($service_data->getLinkedEventTitle())) ] = array('service' => $service_id, 'status' => 'free');
                $matches[ $coworker_id ][ trim(\TeamBooking\Functions\tb_mb_strtolower($service_data->getAfterBookedTitle())) ] = array('service' => $service_id, 'status' => 'booked');
            }
        }

        return $matches;
    }

    /**
     * Retrieves custom boundaries getting times for the Google Calendar
     * events, tweaked with the specific coworker's min/open reservation
     * time limits for the specified service.
     *
     * @param int    $coworker_id
     * @param string $service_id
     * @param string $min_get_time
     *
     * @return \stdClass
     */
    protected function getCoworkerTimeBoundaries($coworker_id, $service_id, $min_get_time)
    {
        /**
         * Let's get the min/open time strings for the given
         * coworker id and service id combination.
         *
         * The string is in the DateInterval format, with the
         * exception of *mid appended strings. These strings
         * means that the interval must be cut at midnight.
         *
         */
        $min_time_string = static::$settings->getCoworkerData($coworker_id)->getCustomEventSettings($service_id)->getMinTime();
        $open_time_string = static::$settings->getCoworkerData($coworker_id)->getCustomEventSettings($service_id)->getOpenTime();

        /**
         * Let's get the reference for the min time interval
         * specified by the given coworker for the given
         * service.
         *
         * It could be the START of the slot, or the END.
         */
        $reference = static::$settings->getCoworkerData($coworker_id)->getCustomEventSettings($service_id)->getMinTimeReference();

        /**
         * If the min time interval is a *mid appended one,
         * we should add the prepend interval to the
         * $now object AND set his time to midnight.
         */
        $now = new \DateTime('now', $this->timezone);
        $base = $now->getTimestamp();
        if (!\TeamBooking\Functions\isAdmin() && $coworker_id !== get_current_user_id()) {
            if (substr($min_time_string, -3) === 'mid') {
                // Get rid of 'mid' appendix
                $min_time_string = substr($min_time_string, 0, -3);
                // Add the interval
                $now->add(new \DateInterval($min_time_string));
                // Set the time to midnight
                $now->setTime(0, 0);
            } else {
                // Just add the interval
                $now->add(new \DateInterval($min_time_string));
            }
        }
        $interval_min = $now->getTimestamp() - $base;

        /**
         * Let's return a formatted min time
         */
        if (\TeamBooking\Actions\parser_min_time_cut(TRUE)
            && strtotime($min_get_time) < strtotime($now->format(DATE_ATOM))
        ) {
            /**
             * The min requesting time is less than the tweaked
             * min retrieving time, so we must return the tweaked
             * retrieving time (formatted).
             */
            $min_time = $now->format(DATE_ATOM);
        } else {
            /**
             * The min requesting time is greater than or
             * equal to the tweaked min retrieving time,
             * so we can just return the first.
             */
            $min_time = $min_get_time;
        }

        // repeat the steps for the open time
        if ($open_time_string && !\TeamBooking\Functions\isAdmin() && $coworker_id !== get_current_user_id()) {
            $now = new \DateTime();
            $now->setTimezone($this->timezone);
            if (substr($open_time_string, -3) === 'mid') {
                // Get rid of 'mid' appendix
                $open_time_string = substr($open_time_string, 0, -3);
                // Add the interval
                $now->add(new \DateInterval($open_time_string));
                // Set the time to midnight
                $now->setTime(23, 59);
            } else {
                // Just add the interval
                $now->add(new \DateInterval($open_time_string));
            }
            $open_time = $now->format(DATE_ATOM);
        }


        /**
         * Preparing the object to return.
         *
         * We're using a stdClass for convenience,
         * so we can pass the reference along with it.
         */
        $return = new \stdClass();
        $return->min_time = $min_time;
        $return->min_interval = $interval_min;
        $return->reference = $reference;
        $return->open_time = isset($open_time) ? $open_time : 0;

        return $return;
    }

    /**
     * Checks if two slots overlap
     *
     * @param Slot $main
     * @param Slot $check
     *
     * @return bool|array
     */
    protected function checkOverlap(Slot $main, Slot $check)
    {
        $overlap = FALSE;
        $xs = \TeamBooking\Functions\strtotime_tb($check->getStartTime());
        $xe = \TeamBooking\Functions\strtotime_tb($check->getEndTime());
        $ds = \TeamBooking\Functions\strtotime_tb($main->getStartTime());
        $de = \TeamBooking\Functions\strtotime_tb($main->getEndTime());

        if (!($xe <= $ds || $xs >= $de)) {
            $overlap['length'] = min(abs($xe - $xs), abs($xe - $ds), abs($de - $ds), abs($de - $xs));
            $overlap['start'] = max($xs, $ds);
        }

        return $overlap;
    }

    /**
     * It must be called at the very end of the parsing process
     */
    protected function postProcessBookedSlots()
    {
        foreach ($this->slots_booked as $key => $slot) {
            // clean booked slots
            if (!in_array($slot->getServiceId(), $this->services_requested)) {
                unset($this->slots_booked[ $key ]);
                continue;
            }
            foreach ($this->reservations_in_database as $reservation) {
                if ($reservation->getGoogleCalendarEvent() === $slot->getEventId()) {
                    // add customer's data
                    $slot->addCustomer(array(
                        'email'          => $reservation->getCustomerEmail(),
                        'name'           => $reservation->getCustomerDisplayName(),
                        'id'             => $reservation->getCustomerUserId(),
                        'address'        => $reservation->getCustomerAddress(),
                        'timezone'       => $reservation->getCustomerTimezone(),
                        'tickets'        => $reservation->getTickets(),
                        'status'         => $reservation->getStatus(),
                        'reservation_id' => $reservation->getDatabaseId()
                    ));
                }
            }
        }
    }

    /**
     * It must be called at the very end of the parsing process
     */
    protected function postProcessFreeSlots()
    {
        foreach ($this->slots_free as $key => $slot) {
            if (!in_array($slot->getServiceId(), $this->services_requested)) {
                unset($this->slots_free[ $key ]);
                continue;
            }
        }
    }

    /**
     * Removes the event summary extra data, if present.
     *
     * @param string $raw_summary
     *
     * @return string
     */
    public static function parseSummary($raw_summary)
    {
        $pieces = explode('||', $raw_summary);
        $pieces = explode('>>', $pieces[0]);

        return trim(\TeamBooking\Functions\tb_mb_strtolower($pieces[0]));
    }

    /**
     * Extracts the event summary informative part, if present.
     *
     * @param $raw_summary
     *
     * @return string
     */
    public static function extractSummaryExtraInfo($raw_summary)
    {
        $pieces = explode('||', $raw_summary);
        if (isset($pieces[1])) {
            $sub_pieces = explode('>>', $pieces[1]);

            return trim($sub_pieces[0]);
        } else {
            return '';
        }
    }

    /**
     * Extracts the commands inside the event summary, if present
     *
     * @param string $raw_summary
     * @param bool   $as_string
     *
     * @return array|string
     */
    public static function extractSummarySettings($raw_summary, $as_string = FALSE)
    {
        $pieces = explode('>>', $raw_summary);
        if (isset($pieces[1])) {
            $sub_pieces = explode('||', $pieces[1]);
            $commands = trim($sub_pieces[0]);
            if ($as_string) return $commands;
            $commands = array_map('TeamBooking\\Functions\\tb_mb_strtolower', array_map('trim', explode(',', $commands)));
            $return = array();
            foreach ($commands as $command) {
                $tmp = explode('=', $command);
                $return[ $tmp[0] ] = isset($tmp[1]) ? $tmp[1] : NULL;
            }

            return $return;
        }

        return $as_string ? '' : array();
    }

}