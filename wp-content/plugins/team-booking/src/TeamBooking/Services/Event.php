<?php

namespace TeamBooking\Services;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Abstracts;

/**
 * Event Service Class
 *
 * @since    2.2.0
 * @author   VonStroheim
 */
class Event extends Appointment
{
    /**
     * Slot max tickets (total) for this service
     *
     * @var int
     */
    protected $slot_max_tickets = 20;

    /**
     * Slot max tickets (per user) for this service
     *
     * @var int
     */
    protected $slot_max_user_tickets = 20;

    public function __construct()
    {
        parent::__construct();
        $this->settings['show_tickets_left'] = TRUE;
        $this->settings['show_tickets_left_threeshold'] = 0;
        $this->settings['location'] = 'inherited';
    }

    /**
     * @param bool $as_label
     *
     * @return string
     */
    public function getClass($as_label = FALSE)
    {
        return $as_label ? __('Event', 'team-booking') : 'event';
    }

    /**
     * Sets the slot max tickets for this service
     *
     * @param int $int
     */
    public function setSlotMaxTickets($int)
    {
        if ((int)$int > 200) $int = 200;
        if ((int)$int < 1) $int = 1;
        $this->slot_max_tickets = (int)$int;
    }

    /**
     * @return int
     */
    public function getSlotMaxTickets()
    {
        return $this->slot_max_tickets;
    }

    /**
     * Sets the slot max user tickets for this service
     *
     * @param int $int
     */
    public function setSlotMaxUserTickets($int)
    {
        if ((int)$int < 1) $int = 1;
        $this->slot_max_user_tickets = (int)$int;
    }

    /**
     * @return int
     */
    public function getSlotMaxUserTickets()
    {
        if ($this->slot_max_user_tickets > $this->slot_max_tickets) {
            return $this->slot_max_tickets;
        } else {
            return $this->slot_max_user_tickets;
        }
    }

    /**
     * The REST API resource of this service
     *
     * @return array
     */
    public function getApiResource()
    {
        $resource = parent::getApiResource();
        $resource['class'] = $this->getClass();
        $resource['maxTotalTicketsPerSlot'] = $this->getSlotMaxTickets();
        $resource['maxUserTicketsPerSlot'] = $this->getSlotMaxUserTickets();

        return $resource;
    }

    /**
     * Whitelist of setting values
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return bool
     */
    protected function validateSettingValues($property, $value)
    {
        if (parent::validateSettingValues($property, $value)) {
            $whitelist = array(
                'show_tickets_left' => array(TRUE, FALSE),
            );
            if (!isset($whitelist[ $property ])) return TRUE;

            return in_array($value, $whitelist[ $property ]);
        }

        return FALSE;

    }


}