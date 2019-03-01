<?php

namespace TeamBooking\Services;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Abstracts;

/**
 * Appointment Service Class
 *
 * @since    2.2.0
 * @author   VonStroheim
 */
class Appointment extends Abstracts\Service
{

    /**
     * Slot duration for this service
     *
     * @var int
     */
    protected $slot_duration = HOUR_IN_SECONDS;

    /**
     * E-mail reminder (to Customer)
     *
     * @var array
     */
    protected $email_reminder_customer = array();

    public function __construct()
    {
        parent::__construct();
        $this->settings['show_times'] = 'yes';
        $this->settings['show_soldout'] = FALSE;
        $this->settings['treat_discarded_free_slots'] = 'hide';
        $this->settings['show_coworker'] = FALSE;
        $this->settings['show_coworker_url'] = FALSE;
        $this->settings['show_service_name'] = TRUE;
        $this->settings['slot_duration'] = 'coworker';
        $this->settings['cancellation_allowed_until'] = DAY_IN_SECONDS;
        $this->settings['location'] = 'inherited';
        $this->settings['location_visibility'] = 'visible';
        $this->settings['free_until_approval'] = FALSE;
        $this->settings['show_attendees'] = 'no';
        $this->email_reminder_customer = array(
            'subject'     => esc_html__("Don't forget your reservation", 'team-booking'),
            'body'        => esc_html__('We are getting close!', 'team-booking'),
            'send'        => FALSE,
            'from'        => 'admin',
            'days_before' => 1
        );
    }

    /**
     * @param bool $as_label
     *
     * @return string
     */
    public function getClass($as_label = FALSE)
    {
        return $as_label ? __('Appointment', 'team-booking') : 'appointment';
    }

    /**
     * Sets the slot duration for this service
     *
     * @param int $int
     */
    public function setSlotDuration($int)
    {
        $this->slot_duration = (int)$int;
    }

    /**
     * @return int
     */
    public function getSlotDuration()
    {
        return $this->slot_duration;
    }

    /**
     * @param string $param
     * @param string $value
     */
    public function setEmailReminder($param, $value)
    {
        if (isset($this->email_reminder_customer[ $param ])) {
            $this->email_reminder_customer[ $param ] = $value;
        }
    }

    /**
     * @param string $param
     *
     * @return string|bool
     */
    public function getEmailReminder($param)
    {
        if ($param === 'days_before' && !is_numeric($this->email_reminder_customer['days_before'])) {
            return 1;
        }

        return $this->email_reminder_customer[ $param ];
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
        $resource['reminderEmail'] = array(
            'send'    => $this->email_reminder_customer['send'],
            'subject' => $this->email_reminder_customer['subject'],
            'body'    => $this->email_reminder_customer['body']
        );
        $resource['approval'] = array(
            'requireFrom'           => $this->getSettingsFor('approval_rule'),
            'keepFreeUntilApproval' => $this->getSettingsFor('free_until_approval'),
        );

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
                'show_times'                 => array('yes', 'start_time_only', 'no'),
                'show_soldout'               => array(TRUE, FALSE),
                'treat_discarded_free_slots' => array('hide', 'booked'),
                'show_coworker'              => array(TRUE, FALSE),
                'show_coworker_url'          => array(TRUE, FALSE),
                'show_service_name'          => array(TRUE, FALSE),
                'slot_duration'              => array('coworker', 'inherited', 'fixed'),
                'free_until_approval'        => array(TRUE, FALSE),
                'show_attendees'             => array('no', 'name', 'email', 'name_email'),

            );
            if (!isset($whitelist[ $property ])) return TRUE;

            return in_array($value, $whitelist[ $property ]);
        }

        return FALSE;

    }


}