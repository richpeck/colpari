<?php

namespace TeamBooking;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Database\eventObject,
    TeamBooking\Functions;
use TeamBooking\Database\Services;

/**
 * Class Slot
 *
 * @author VonStroheim
 */
class Slot
{
    ///////////////////
    // service data  //
    ///////////////////
    private $service_id;
    private $service_name;
    private $service_info;
    private $service_class;
    ///////////////////////
    // Google event data //
    ///////////////////////
    private $start;
    private $end;
    private $event_id;
    private $calendar_id;
    private $container;
    private $multiple_services;
    private $attendees_number = 0;
    private $allday;
    private $event_id_parent;
    private $read_only;
    private $can_be_edited = TRUE;
    private $raw_event_summary = '';
    private $raw_event_description = '';
    ////////////////
    // slot data  //
    ////////////////
    private $coworker_id;
    private $soldout;
    private $location;
    private $from_reservation_id;
    private $timezone;
    private $customers;
    private $price_base = 0;
    private $price_discounted = 0;
    private $price_incremented = 0;
    private $max_tickets;
    private $promotions_applied = array();
    private $bookable_until = 0;

    /**
     * Returns the Google Calendar ID in which
     * the event is.
     *
     * @return string
     */
    public function getCalendarId()
    {
        return $this->calendar_id;
    }

    /**
     * Sets the Google Calendar ID in which
     * the event is.
     *
     * @param string $id
     */
    public function setCalendarId($id)
    {
        $this->calendar_id = $id;
    }

    /**
     * Returns the services array in case of a multiple
     * service container event.
     *
     * @return array
     */
    public function getMultipleServices()
    {
        if (is_array($this->multiple_services)) {
            return $this->multiple_services;
        } else {
            return array();
        }
    }

    /**
     * Sets the services array in case of a multiple
     * service container event.
     *
     * @param array $services
     */
    public function setMultipleServices(array $services)
    {
        $this->multiple_services = $services;
    }

    /**
     * Returns the service name of the slot
     *
     * @param bool $filtered
     *
     * @return string
     */
    public function getServiceName($filtered = FALSE)
    {
        if ($filtered) {
            return \TeamBooking\Actions\service_name_from_slot($this->service_name, $this);
        }

        return $this->service_name;
    }

    /**
     * Sets the service name of the slot
     *
     * @param string $name
     */
    public function setServiceName($name)
    {
        $this->service_name = $name;
    }

    /**
     * Returns the class of the service
     *
     * @return string
     */
    public function getServiceClass()
    {
        return $this->service_class;
    }

    /**
     * Sets the the class of the service
     *
     * @param string $class
     */
    public function setServiceClass($class)
    {
        $this->service_class = $class;
    }

    /**
     * Returns the service description
     *
     * @param bool $filtered
     *
     * @return string
     */
    public function getServiceInfo($filtered = FALSE)
    {
        if ($filtered) {
            return \TeamBooking\Actions\service_description_from_slot($this->service_info, $this);
        }

        return $this->service_info;
    }

    /**
     * Sets the service description
     *
     * @param string $info
     */
    public function setServiceInfo($info)
    {
        $this->service_info = $info;
    }

    /**
     * Returns the service id of the slot
     *
     * @return string
     */
    public function getServiceId()
    {
        return $this->service_id;
    }

    /**
     * Sets the service id of the slot
     *
     * @param string $id
     */
    public function setServiceId($id)
    {
        $this->service_id = $id;
    }

    /**
     * Returns the location of the slot
     *
     * @return string
     */
    public function getLocation()
    {
        return trim($this->location);
    }

    /**
     * Sets the location of the slot
     *
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * Sets the reservation database id, if the slot is created from it
     *
     * @param $id
     */
    public function setFromReservation($id)
    {
        $this->from_reservation_id = $id;
    }

    /**
     * Returns the reservation database id, if the slot was created from it
     *
     * @return int|NULL
     */
    public function getFromReservation()
    {
        return $this->from_reservation_id;
    }

    /**
     * Sets the start time of the slot
     *
     * @param string $time is RFC3339 or y-mm-dd
     */
    public function setStartTime($time)
    {
        $this->start = $time;
    }

    /**
     * Returns the start time of the slot
     *
     * @return string is RFC3339 or y-mm-dd
     */
    public function getStartTime()
    {
        return $this->start;
    }

    /**
     * Sets the end time of the slot
     *
     * @param string $time is RFC3339 or y-mm-dd
     */
    public function setEndTime($time)
    {
        $this->end = $time;
    }

    /**
     * Returns the end time of the slot
     *
     * @return string is RFC3339 or y-mm-dd
     */
    public function getEndTime()
    {
        return $this->end;
    }

    /**
     * Returns the Google event id
     *
     * @return string
     */
    public function getEventId()
    {
        return $this->event_id;
    }

    /**
     * Sets the Google event id
     *
     * @param string $id
     */
    public function setEventId($id)
    {
        $this->event_id = $id;
    }

    /**
     * Returns the Google parent event id, if any
     *
     * @return string
     */
    public function getEventIdParent()
    {
        return isset($this->event_id_parent) ? $this->event_id_parent : FALSE;
    }

    /**
     * Sets the Google parent event id, if any
     *
     * @param string $id
     */
    public function setEventIdParent($id)
    {
        $this->event_id_parent = $id;
    }

    /**
     * @return bool
     */
    public function isReadOnly()
    {
        return (bool)$this->read_only;
    }

    /**
     * @param $bool
     */
    public function setReadOnly($bool)
    {
        $this->read_only = (bool)$bool;
    }

    /**
     * @param string $raw_summary
     */
    public function setEventSummary($raw_summary)
    {
        $this->raw_event_summary = $raw_summary;
    }

    /**
     * @return string
     */
    public function getEventSummary()
    {
        return $this->raw_event_summary;
    }

    /**
     * @param string $raw_description
     */
    public function setEventDescription($raw_description)
    {
        $this->raw_event_description = $raw_description;
    }

    /**
     * @return string
     */
    public function getEventDescription()
    {
        return $this->raw_event_description;
    }

    /**
     * @param $price
     */
    public function setPriceBase($price)
    {
        $this->price_base = (float)$price;
    }

    /**
     * @return float
     */
    public function getPriceBase()
    {
        return (float)$this->price_base;
    }

    /**
     * @param $price
     */
    public function setPriceDiscounted($price)
    {
        $this->price_discounted = (float)$price;
    }

    /**
     * @return float
     */
    public function getPriceDiscounted()
    {
        return (float)$this->price_discounted;
    }

    /**
     * @param $price
     */
    public function setPriceIncremented($price)
    {
        $this->price_incremented = (float)$price;
    }

    /**
     * @return float
     */
    public function getPriceIncremented()
    {
        return (float)$this->price_incremented;
    }

    /**
     * Returns the coworker id
     *
     * @return int
     */
    public function getCoworkerId()
    {
        return $this->coworker_id;
    }

    /**
     * Sets the coworker id
     *
     * @param int $id
     */
    public function setCoworkerId($id)
    {
        $this->coworker_id = $id;
    }

    /**
     * @return int
     */
    public function getMaxTickets()
    {
        return $this->max_tickets === NULL ? 1 : $this->max_tickets;
    }

    /**
     * @param int $int
     */
    public function setMaxTickets($int)
    {
        $this->max_tickets = (int)$int;
    }

    /**
     * Returns the number of attendees for that slot
     *
     * @return int
     */
    public function getAttendeesNumber()
    {
        if ($this->getServiceClass() === 'appointment' && $this->isSoldout()) {
            return 1;
        }

        return $this->attendees_number;
    }

    /**
     * Sets the number of attendees for that slot
     *
     * @param int $number
     */
    public function setAttendeesNumber($number)
    {
        $this->attendees_number = $number;
    }

    /**
     * Sets the slot as all-day or not
     *
     * @param $bool
     */
    public function setAllDay($bool)
    {
        $this->allday = (bool)$bool;
    }

    /**
     * Checks if the slot is of All Day type
     *
     * @return boolean
     */
    public function isAllDay()
    {
        return (bool)$this->allday;
    }

    /**
     * @param null|bool $bool
     *
     * @return bool
     */
    public function canBeEdited($bool = NULL)
    {
        if (NULL === $bool) {
            return $this->can_be_edited;
        } else {
            $this->can_be_edited = (bool)$bool;

            return $this->can_be_edited;
        }
    }

    /**
     * Sets the slot as Container-derived or not
     *
     * @param $boolean
     */
    public function setContainer($boolean)
    {
        $this->container = (bool)$boolean;
    }

    /**
     * Checks if the slot is derived from a Container
     *
     * @return boolean
     */
    public function isContainer()
    {
        return (bool)$this->container;
    }

    /**
     * Sets the slot as soldout/booked
     */
    public function setSoldout()
    {
        $this->soldout = TRUE;
    }

    /**
     * Checks if the slot is soldout/booked
     *
     * @return boolean
     */
    public function isSoldout()
    {
        return (bool)$this->soldout;
    }

    /**
     * Sets the timezone of the slot
     *
     * @param string $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * Returns the timezone of the slot
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param int $timestamp
     */
    public function setBookableUntil($timestamp)
    {
        $this->bookable_until = $timestamp < 0 ? 0 : (int)$timestamp;
    }

    /**
     * @return int
     */
    public function getBookableUntil()
    {
        return $this->bookable_until;
    }

    /**
     * @param null|int $timestamp
     *
     * @return bool
     */
    public function isStillBookable($timestamp = NULL)
    {
        if (NULL === $timestamp) {
            $timestamp = \TeamBooking\Toolkit\getNowInSecondsUTC();
        }

        return $this->bookable_until === 0 ? TRUE : $this->bookable_until > $timestamp;
    }

    /**
     * @param array $customers
     */
    public function setCustomers(array $customers)
    {
        $this->customers = $customers;
    }

    /**
     * @param array $customer
     */
    public function addCustomer(array $customer)
    {
        $this->customers[] = $customer;
    }

    /**
     * @return array
     *
     * Arrays have the following elements:
     *  ['email']
     *  ['name']
     *  ['id']
     *  ['address']
     *  ['timezone']
     *  ['tickets']
     *  ['status']
     *  ['reservation_id']
     */
    public function getCustomers()
    {
        return (array)$this->customers;
    }

    /**
     * @return string
     */
    public function getCoworkerDisplayString()
    {
        $coworker = Functions\getSettings()->getCoworkerData($this->getCoworkerId());
        try {
            $service = Database\Services::get($this->getServiceId());
            if (!$service->getSettingsFor('show_coworker')) {
                return '';
            }
            if ($service->getSettingsFor('show_coworker_url')) {
                return '<a href="' . Functions\getSettings()->getCoworkerUrl($this->getCoworkerId()) . '" target="_blank">' . $coworker->getDisplayName() . '</a>';
            }
        } catch (\Exception $e) {
            return '';
        }

        return $coworker->getDisplayName();
    }

    /**
     * @return string
     */
    public function getAttendeesList()
    {
        try {
            $service = Database\Services::get($this->getServiceId());
            $customers = $this->getCustomers();
            $return = array();
            switch ($service->getSettingsFor('show_attendees')) {
                case 'name':
                    foreach ($customers as $customer) {
                        $return[ $customer['name'] . $customer['email'] . $customer['id'] ] = '<span class="tbk-attendee-name">' . $customer['name'] . '</span>';
                    }
                    break;
                case 'email':
                    foreach ($customers as $customer) {
                        $return[ $customer['name'] . $customer['email'] . $customer['id'] ] = '<span class="tbk-attendee-email">' . antispambot($customer['email']) . '</span>';
                    }
                    break;
                case 'name_email':
                    foreach ($customers as $customer) {
                        $return[ $customer['name'] . $customer['email'] . $customer['id'] ] = '<span class="tbk-attendee-name">' . $customer['name'] . '</span>'
                            . '<span class="tbk-attendee-email"> (' . antispambot($customer['email']) . ')</span>';
                    }
                    break;
                default:
                    break;
            }

            return '<span class="tbk-attendees-list">' . implode(', ', $return) . '</span>';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @param string $format
     * @param string $start_or_end
     *
     * @return string
     */
    public function getDateFormatted($format, $start_or_end = 'start')
    {
        $start_date_time_object = new \DateTime($this->getStartTime(), new \DateTimeZone($this->getTimezone()));
        if (NULL !== $this->getTimezone()) $start_date_time_object->setTimezone(new \DateTimeZone($this->getTimezone()));
        $end_date_time_object = new \DateTime($this->getEndTime(), new \DateTimeZone($this->getTimezone()));
        if (NULL !== $this->getTimezone()) $end_date_time_object->setTimezone(new \DateTimeZone($this->getTimezone()));
        if ($start_or_end === 'start') {
            return $start_date_time_object->format($format);
        } else {
            return $end_date_time_object->format($format);
        }
    }

    /**
     * @param bool $start_only
     *
     * @return string
     */
    public function getDateString($start_only = TRUE)
    {
        $date_format = get_option('date_format');
        $start_date_time_object = new \DateTime($this->getStartTime());
        if (NULL !== $this->getTimezone()) $start_date_time_object->setTimezone(new \DateTimeZone($this->getTimezone()));
        $end_date_time_object = new \DateTime($this->getEndTime());
        if (NULL !== $this->getTimezone()) $end_date_time_object->setTimezone(new \DateTimeZone($this->getTimezone()));
        if ($this->isAllDay()) {
            return esc_html__('All day', 'team-booking');
        }
        if ($start_only) {
            return $start_date_time_object->format($date_format);
        }

        return $start_date_time_object->format($date_format) . ' - ' . $end_date_time_object->format($date_format);
    }

    /**
     * @param bool $onscreen
     *
     * @return null|string
     */
    public function getTimesString($onscreen = TRUE)
    {
        $time_format = get_option('time_format');
        $start_date_time_object = new \DateTime($this->getStartTime());
        if (NULL !== $this->getTimezone()) $start_date_time_object->setTimezone(new \DateTimeZone($this->getTimezone()));
        $end_date_time_object = new \DateTime($this->getEndTime());
        if (NULL !== $this->getTimezone()) $end_date_time_object->setTimezone(new \DateTimeZone($this->getTimezone()));
        try {
            $service = Database\Services::get($this->getServiceId());
            switch ($service->getSettingsFor('show_times')) {
                case 'yes':
                    if ($this->isAllDay()) {
                        return esc_html__('All day', 'team-booking');
                    }
                    if ($onscreen) {
                        return '<span class="tbk-ev-txt-wrap">' . $start_date_time_object->format($time_format) . '</span>'
                            . ' <span class="tbk-times-arrow">→</span> '
                            . '<span class="tbk-ev-txt-wrap">' . $end_date_time_object->format($time_format) . '</span>';
                    }

                    return $start_date_time_object->format($time_format)
                        . ' - '
                        . $end_date_time_object->format($time_format);
                case 'no' :
                    return NULL;
                case 'start_time_only':
                    if ($this->isAllDay()) {
                        return esc_html__('All day', 'team-booking');
                    }

                    return $start_date_time_object->format($time_format);
                default:
                    return NULL;
            }
        } catch (\Exception $e) {
            return NULL;
        }
    }

    /**
     * @return bool
     */
    public function isPastInTime()
    {
        return Toolkit\getNowInSecondsUTC() > strtotime($this->start);
    }

    /**
     * @param       $promotion_name
     * @param float $discount_val
     *
     * @return bool TRUE if just applied, FALSE if already applied
     */
    public function addPromotionApplied($promotion_name, $discount_val)
    {
        if (isset($this->promotions_applied[ $promotion_name ])) {
            return FALSE;
        }
        $this->promotions_applied[ $promotion_name ] = $discount_val;

        return TRUE;
    }

    /**
     * @param $promotion_name
     *
     * @return bool
     */
    public function isPromotionApplied($promotion_name)
    {
        return isset($this->promotions_applied[ $promotion_name ]);
    }

    /**
     * @param $promotion_name
     *
     * @return int|mixed
     */
    public function getPromotionApplied($promotion_name)
    {
        if (isset($this->promotions_applied[ $promotion_name ])) {
            return $this->promotions_applied[ $promotion_name ];
        }

        return 0;
    }

    /**
     * @return array
     */
    public function getPromotionsApplied()
    {
        return $this->promotions_applied;
    }

    /**
     * @param $promotion_name
     *
     * @return bool TRUE if removed, FALSE if not present
     */
    public function removePromotionApplied($promotion_name)
    {
        if (isset($this->promotions_applied[ $promotion_name ])) {
            unset($this->promotions_applied[ $promotion_name ]);

            return TRUE;
        }

        return FALSE;
    }

    public function removePromotionsApplied()
    {
        $this->promotions_applied = array();
    }

    /**
     * @param eventObject $event
     * @param             $service_id
     * @param             $coworker_id
     * @param array       $services_array
     *
     * @return Slot
     */
    public static function getContainerFromEvent(eventObject $event, $service_id, $coworker_id, array $services_array = array())
    {
        $slot = Slot::getFromEvent($event, $service_id, $coworker_id);
        $slot->setContainer(TRUE);
        if (count($services_array) > 1) {
            $slot->setMultipleServices(array_map('strtolower', $services_array));
        }

        return $slot;
    }

    /**
     * @param eventObject $event
     * @param             $service_id
     * @param             $coworker_id
     *
     * @return Slot
     */
    public static function getSoldoutFromEvent(eventObject $event, $service_id, $coworker_id)
    {
        $slot = Slot::getFromEvent($event, $service_id, $coworker_id);
        $slot->setSoldout();

        return $slot;
    }

    /**
     * @param eventObject $event
     * @param             $service_id
     * @param             $coworker_id
     *
     * @return Slot
     */
    public static function getFromEvent(eventObject $event, $service_id, $coworker_id)
    {
        $slot = new self();
        try {
            $service = Database\Services::get($service_id);
        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                trigger_error("{$service_id} service id cannot be found - invoked by event {$event->id} with summary {$event->summary} related to the coworker {$coworker_id}. Empty slot returning.");
            }

            return $slot;
        }
        // Check for All Day event and set start/end time
        #$slot->setTimezone($event->getStart()->getTimezone());
        $slot->setAllDay($event->allday);
        $slot->setEndTime($event->end);
        $slot->setStartTime($event->start);
        $slot->setEventSummary($event->summary);
        $slot->setEventDescription($event->description);
        $slot->setPriceBase($service->getPrice());
        // Sets the slot's location
        $location_setting = $service->getSettingsFor('location');
        if ($location_setting === 'inherited') {
            $slot->setLocation($event->location);
        } elseif ($location_setting === 'fixed') {
            $slot->setLocation($service->getLocation());
        } else {
            $slot->setLocation(''); //empty
        }
        $slot->setCoworkerId($coworker_id);
        $slot->setServiceId($service_id);
        $slot->setEventId($event->id);
        $slot->setCalendarId($event->organizer);
        $slot->setServiceName($service->getName());
        $slot->setServiceClass($service->getClass());
        $slot->setServiceInfo($service->getDescription());
        if ($service->getClass() === 'event') {
            $slot->setMaxTickets($service->getSlotMaxTickets());
        }
        if (Functions\tb_mb_strtolower($event->creator) !== Functions\tb_mb_strtolower(Functions\getSettings()->getCoworkerData($coworker_id)->getAuthAccount())) {
            $slot->setReadOnly(TRUE);
            $slot->canBeEdited(FALSE);
        }
        if ($service->getSettingsFor('bookable') === 'nobody') {
            $slot->setReadOnly(TRUE);
        }
        $slot->applyCommands();
        $slot->updatePriceDiscounted();

        return $slot;
    }

    /**
     * @param null|string $user_identifier
     *
     * @return int|mixed
     */
    public function getTicketsLeft($user_identifier = NULL)
    {
        try {
            $service = Database\Services::get($this->getServiceId());
        } catch (\Exception $e) {
            return 0;
        }
        $left = ($this->getMaxTickets() - $this->getAttendeesNumber() > 0) ? $this->getMaxTickets() - $this->getAttendeesNumber() : 0;
        if (NULL === $user_identifier || Functions\isAdmin()) {
            return $left;
        }
        if (!$user_identifier) {
            return min($left, $service->getSlotMaxUserTickets());
        }
        $count = 0;
        foreach ($this->getCustomers() as $customer) {
            if (is_user_logged_in()) {
                if ((int)$customer['id'] === $user_identifier) {
                    $count += $customer['tickets'];
                }
            } else {
                if ($customer['email'] === $user_identifier) {
                    $count += $customer['tickets'];
                }
            }
        }

        return min($left, ($service->getSlotMaxUserTickets() - $count) < 0 ? 0 : $service->getSlotMaxUserTickets() - $count);
    }

    /**
     * Updates the discounted price
     *
     * @return bool TRUE if updated, FALSE otherwise
     */
    public function updatePriceDiscounted()
    {
        try {
            $service = Services::get($this->getServiceId());
            $prev = round($this->getPriceDiscounted(), 4);
            $this->setPriceDiscounted(Functions\getDiscountedPrice($service, Functions\strtotime_tb($this->getStartTime()), Functions\strtotime_tb($this->getEndTime()), $this->getPriceBase(), $this));

            return ($prev !== round($this->getPriceDiscounted(), 4));
        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                trigger_error("{$this->getServiceId()} service id cannot be found - invoked by slot {$this->getUniqueId()} during an attempt of updating the discounted price");
            }

            return FALSE;
        }
    }

    /**
     * @return array
     */
    public function getApiResource()
    {
        $return = array(
            'type'               => 'slot',
            'serviceID'          => $this->getServiceId(),
            'serviceName'        => $this->getServiceName(),
            'serviceDescription' => $this->getServiceInfo(),
            'location'           => $this->getLocation(),
            'coworkerID'         => $this->getCoworkerId(),
            'isSoldout'          => $this->isSoldout(),
            'isAllday'           => $this->isAllDay(),
            'tickets'            => $this->getAttendeesNumber(),
            'start'              => $this->getStartTime(),
            'end'                => $this->getEndTime(),
            'gcalEvent'          => $this->getEventId(),
            'gcalParentEvent'    => $this->getEventIdParent(),
            'gcalID'             => $this->getCalendarId()
        );

        return $return;
    }

    /**
     * @return string
     */
    public function getUniqueId()
    {
        if (NULL === $this->event_id_parent) {
            return $this->event_id . $this->calendar_id . $this->service_id . $this->start . $this->end;
        }

        return $this->calendar_id . $this->service_id . $this->event_id_parent . $this->start . $this->end;
    }

    /**
     * Applies the commands to trim the specific slot
     */
    public function applyCommands()
    {
        $to_be_allowed = array(
            'readonly',
            'price'
        );
        $commands = Parser::extractSummarySettings($this->raw_event_summary);
        // Authority check
        if (!Functions\isAdmin($this->coworker_id) && !Functions\getSettings()->allowSlotCommands()) {
            $commands = array_diff($commands, $to_be_allowed);
        }
        foreach ($commands as $command => $value) {
            switch ($command) {
                case 'booked':
                case 'full':
                    $this->setSoldout();
                    break;
                case 'readonly':
                    $this->setReadOnly(TRUE);
                    break;
                case 'price':
                    $this->setPriceBase((float)$value < 0 ? 0 : $value);
                    break;
            }
        }
    }

}
