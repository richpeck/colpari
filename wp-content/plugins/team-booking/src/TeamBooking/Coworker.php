<?php

defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Toolkit,
    TeamBooking\Functions,
    TeamBooking\Database;

/**
 * Class TeamBookingCoworker
 *
 * @author VonStroheim
 */
class TeamBookingCoworker
{
    private $coworker_id;
    private $calendar_id;
    private $access_token;
    private $tb_api_token;
    private $custom_event_settings;
    private $services_allowed;
    private $auth_google_account;

    public function __construct($coworker_id = NULL)
    {
        if (NULL === $coworker_id && is_user_logged_in()) {
            /*
             * the class is called without a specified ID,
             * let's load the logged one.
             */
            $this->coworker_id = wp_get_current_user()->data->ID;
        } else {
            $this->coworker_id = $coworker_id;
        }
        $this->tb_api_token = Toolkit\generateToken();
    }

    /**
     * Adds a Google Calendar ID
     *
     * @param string $calendar_id
     */
    public function addCalendarId($calendar_id)
    {
        if (is_array($this->calendar_id)) {
            $this->calendar_id[ $calendar_id ] = array('calendar_id' => $calendar_id, 'sync_token' => NULL, 'independent' => TRUE);
        } else {
            $array = array();
            if (!empty($this->calendar_id)) {
                $array[ $this->calendar_id ] = array('calendar_id' => $this->calendar_id, 'sync_token' => NULL, 'independent' => TRUE);
            }
            $array[ $calendar_id ] = array('calendar_id' => $calendar_id, 'sync_token' => NULL, 'independent' => TRUE);
            $this->calendar_id = $array;
        }
    }

    /**
     * @param string $sync_token
     * @param string $calendar_id
     */
    public function addSyncToken($sync_token, $calendar_id)
    {
        if (isset($this->calendar_id[ $calendar_id ])) {
            $this->calendar_id[ $calendar_id ]['sync_token'] = $sync_token;
        }
    }

    /**
     * @param string $calendar_id
     *
     * @return null|string
     */
    public function getSyncToken($calendar_id)
    {
        if (isset($this->calendar_id[ $calendar_id ])) {
            return isset($this->calendar_id[ $calendar_id ]['sync_token']) ? $this->calendar_id[ $calendar_id ]['sync_token'] : NULL;
        } else {
            return NULL;
        }
    }

    public function cleanSyncTokens()
    {
        foreach ($this->getCalendars() as $cal_id => $calendar) {
            $this->calendar_id[ $cal_id ]['sync_token'] = NULL;
        }
    }

    /**
     * Drops a Google Calendar ID
     *
     * @param string $id
     *
     * @return bool
     */
    public function dropCalendarId($id)
    {
        if (!is_array($this->calendar_id)) return FALSE;
        if (!isset($this->calendar_id[ $id ])) return FALSE;
        unset($this->calendar_id[ $id ]);

        return TRUE;
    }

    /**
     * Returns the Coworker's Google Calendars
     *
     * @return array Google Calendar IDs
     */
    public function getCalendars()
    {
        if (is_array($this->calendar_id)) {
            return $this->calendar_id;
        } elseif (NULL !== $this->calendar_id) {
            // legacy
            return array(
                $this->calendar_id => array(
                    'calendar_id' => $this->calendar_id,
                    'sync_token'  => NULL,
                    'independent' => TRUE
                )
            );
        } else {
            return array();
        }
    }

    /**
     * Returns a particular Coworker's Google Calendar
     *
     * @param $id
     *
     * @return stdClass
     */
    public function getCalendar($id)
    {
        $calendars = $this->getCalendars();

        return (object)$calendars[ $id ];
    }

    /**
     * Drops all the Google Calendars
     */
    public function dropAllCalendarIds()
    {
        $this->calendar_id = array();
    }

    /**
     * @param array $calendars
     */
    public function addCalendars(array $calendars)
    {
        $this->calendar_id = array();
        foreach ($calendars as $calendar) {
            if (isset($calendar['calendar_id'])) {
                $this->calendar_id[ $calendar['calendar_id'] ] = array(
                    'calendar_id' => $calendar['calendar_id'],
                    'sync_token'  => $calendar['sync_token'],
                    'independent' => isset($calendar['independent']) ? $calendar['independent'] : TRUE
                );
            }
        }
    }

    /**
     * Retrieve the oAuth access token
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * Sets the oAuth access token
     *
     * @param string $token
     */
    public function setAccessToken($token)
    {
        $this->access_token = $token;
    }

    /**
     * Retrieve the TeamBooking API token
     *
     * @return string
     */
    public function getApiToken()
    {
        return $this->tb_api_token;
    }

    /**
     * Refresh the TeamBooking API token
     *
     * @return string
     */
    public function refreshApiToken()
    {
        $this->tb_api_token = Toolkit\generateToken();

        return $this->tb_api_token;
    }

    /**
     * @param string $email
     */
    public function setAuthAccount($email)
    {
        $this->auth_google_account = $email;
    }

    /**
     * @return string
     */
    public function getAuthAccount()
    {
        return $this->auth_google_account;
    }

    /**
     * Retrieve the custom settings object for a specified service
     *
     * @param string $service_id
     *
     * @return \TeamBookingCustomBTSettings
     */
    public function getCustomEventSettings($service_id)
    {
        // Read & return settings object
        if (isset($this->custom_event_settings[ $service_id ])) {
            return $this->custom_event_settings[ $service_id ];
        } else {
            /*
             * Coworker hasn't customized anything yet for that service,
             * so let's return defaults.
             */
            try {
                $service = Database\Services::get($service_id);
            } catch (Exception $ex) {
                return new TeamBookingCustomBTSettings();
            }
            $return = new TeamBookingCustomBTSettings();
            // This string must not be wrapped in a GetText function
            $return->setAfterBookedTitle('New reservation for' . ' ' . $service->getName());
            $return->setBookedEventColor(0);
            $return->setGetDetailsByEmail(TRUE);
            $return->setLinkedEventTitle($service->getName());
            $return->setMinTime('PT1H');
            $return->setOpenTime(0);
            $service_name = $service->getName(TRUE);
            $return->setNotificationEmailBody(sprintf(__('You have got a new reservation for %s<br>Date and time: [start_datetime]<br>Customer name: [first_name]<br>Customer email: [email]', 'team-booking'), $service_name));
            $return->setNotificationEmailSubject(sprintf(__('New reservation for %s', 'team-booking'), $service_name));
            $return->setReminder(0);
            $return->setParticipate(TRUE);
            $return->setIncludeFilesAsAttachment(FALSE);
            $return->addCustomerAsGuest(TRUE);

            return $return;
        }
    }

    /**
     * Save a custom event settings object for a specified service
     *
     * @param TeamBookingCustomBTSettings $settings
     * @param string                      $service_id
     */
    public function setCustomEventSettings(TeamBookingCustomBTSettings $settings, $service_id)
    {
        $this->custom_event_settings[ $service_id ] = $settings;
    }

    /**
     * @param array $service_ids
     *
     * @throws Exception
     */
    public function setAllowedServices(array $service_ids)
    {
        $services_array = array();
        $services = Database\Services::get();
        foreach ($services as $id => $service) {
            $services_array[ $id ] = in_array($id, $service_ids);
        }
        $this->services_allowed = $services_array;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getAllowedServices()
    {
        if (isset($this->services_allowed) && is_array($this->services_allowed)) {
            $services = Database\Services::get();
            $return = array();
            foreach ($services as $id => $service) {
                if (isset($this->services_allowed[ $id ])) {
                    if ($this->services_allowed[ $id ]) {
                        $return[] = $id;
                    }
                } else {
                    // new service, allowed by default
                    $return[] = $id;
                }
            }

            return $return;
        } else {
            return Functions\getSettings()->getServiceIdList();
        }
    }

    /**
     * @param $service_id
     *
     * @return bool
     * @throws Exception
     */
    public function isServiceAllowed($service_id)
    {
        return in_array($service_id, $this->getAllowedServices());
    }

    /**
     * Returns the Coworker's ID (WordPress user ID)
     *
     * @return int
     */
    public function getId()
    {
        return $this->coworker_id;
    }

    /**
     * Returns the Coworker's nicename (WordPress user nicename)
     *
     * @return string
     */
    public function getNiceName()
    {
        return get_userdata($this->coworker_id)->data->user_nicename;
    }

    /**
     * Returns the Coworker's email (WordPress user email)
     *
     * @return string
     */
    public function getEmail()
    {
        return get_userdata($this->coworker_id)->data->user_email;
    }

    /**
     * Returns the Coworker's URL (WordPress user URL)
     *
     * @return string
     */
    public function getUrl()
    {
        return get_userdata($this->coworker_id)->data->user_url;
    }

    /**
     * Returns the Coworker's display name (WordPress user display name)
     *
     * @return string
     */
    public function getDisplayName()
    {
        return get_userdata($this->coworker_id)->data->display_name;
    }

    /**
     * Returns the Coworker's roles (WordPress user roles)
     *
     * @return array
     */
    public function getRoles()
    {
        return get_userdata($this->coworker_id)->roles;
    }

    /**
     * Checks if the coworker is an Admin
     *
     * @return bool
     */
    public function isAdministrator()
    {
        return user_can($this->coworker_id, 'manage_options');
    }

    /**
     * Returns the Coworker's first name (WordPress user first name)
     *
     * @return string
     */
    public function getFirstName()
    {
        $tmp = get_user_meta($this->coworker_id); // PHP 5.3 compatibility

        return isset($tmp['first_name']) ? $tmp['first_name'][0] : '';
    }

    /**
     * Returns the Coworker's last name (WordPress user last name)
     *
     * @return string
     */
    public function getLastName()
    {
        $tmp = get_user_meta($this->coworker_id); // PHP 5.3 compatibility

        return isset($tmp['last_name']) ? $tmp['last_name'][0] : '';
    }

    /**
     * Returns the Coworker's full name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * Drops the custom settings for a specified service
     *
     * @param string $service_id
     *
     * @return bool
     */
    public function dropCustomServiceSettings($service_id)
    {
        if (isset($this->custom_event_settings[ $service_id ])) {
            unset($this->custom_event_settings[ $service_id ]);

            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * @return string
     */
    public function get_json()
    {
        $vars = get_object_vars($this);
        foreach ($vars['custom_event_settings'] as $service_id => $custom_event_setting) {
            /* @var $custom_event_setting TeamBookingCustomBTSettings */
            $vars['custom_event_settings'][ $service_id ] = json_decode($custom_event_setting->get_json());
        }

        $encoded = json_encode($vars);
        if ($encoded) {
            return $encoded;
        }

        return '[]';
    }

    /**
     * @param string $json
     */
    public function inject_json($json)
    {
        $array = json_decode($json, TRUE);
        if (!array()) {
            $array = array();
        }
        if (isset($array['calendar_id'])) $this->calendar_id = $array['calendar_id'];
        if (isset($array['access_token'])) $this->setAccessToken($array['access_token']);
        if (isset($array['tb_api_token'])) $this->tb_api_token = $array['tb_api_token'];
        if (isset($array['services_allowed'])) $this->services_allowed = $array['services_allowed'];
        if (isset($array['auth_google_account'])) $this->setAuthAccount($array['auth_google_account']);
        if (isset($array['custom_event_settings'])) {
            $this->custom_event_settings = array();
            foreach ($array['custom_event_settings'] as $service_id => $custom_event_settings) {
                $settings = new TeamBookingCustomBTSettings();
                $settings->inject_json(json_encode($custom_event_settings));
                $this->setCustomEventSettings($settings, $service_id);
            }
        }
    }

}
