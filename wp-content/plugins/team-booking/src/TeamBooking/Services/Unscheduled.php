<?php

namespace TeamBooking\Services;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Abstracts;

/**
 * Unscheduled Service Class
 *
 * @since    2.2.0
 * @author   VonStroheim
 */
class Unscheduled extends Abstracts\Service
{
    /**
     * Max reservations per logged user
     *
     * @var int
     */
    protected $max_reservations_per_user = 0;

    /**
     * Coworker ID for direct assignment
     *
     * @var int
     */
    protected $direct_coworker_id;


    public function __construct()
    {
        parent::__construct();
        $this->settings['show_coworker'] = FALSE;
        $this->settings['show_coworker_url'] = FALSE;
        $this->settings['assignment_rule'] = 'equal';
        $this->settings['location_visibility'] = 'visible';
    }

    /**
     * @param bool $as_label
     *
     * @return string
     */
    public function getClass($as_label = FALSE)
    {
        return $as_label ? __('Unscheduled', 'team-booking') : 'unscheduled';
    }

    /**
     * Sets the max reservations per user
     *
     * @param int $int
     */
    public function setMaxReservationsUser($int)
    {
        if ((int)$int < 0) $int = 0;
        $this->max_reservations_per_user = (int)$int;
    }

    /**
     * @return int
     */
    public function getMaxReservationsUser()
    {
        return $this->max_reservations_per_user;
    }

    /**
     * Sets the Coworker ID for direct assignment
     *
     * @param int $id
     */
    public function setDirectCoworkerId($id)
    {
        $this->direct_coworker_id = (int)$id;
    }

    /**
     * @return int
     */
    public function getDirectCoworkerId()
    {
        return $this->direct_coworker_id;
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
        $resource['maxReservationsPerLoggedUser'] = $this->getMaxReservationsUser();
        $resource['assignment'] = array(
            'rule'             => $this->settings['assignment_rule'],
            'directCoworkerID' => $this->getDirectCoworkerId()
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
                'show_coworker'     => array(TRUE, FALSE),
                'show_coworker_url' => array(TRUE, FALSE),
                'assignment_rule'   => array('equal', 'direct', 'random')
            );
            if (!isset($whitelist[ $property ])) return TRUE;

            return in_array($value, $whitelist[ $property ]);
        }

        return FALSE;
    }

}