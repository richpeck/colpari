<?php

defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Toolkit;

/**
 * Class TeamBookingCustomBTSettings
 *
 * @since  1.0.0
 * @author VonStroheim
 *
 */
class TeamBookingCustomBTSettings
{
    private $linked_event_title;
    private $after_booked_title;
    private $min_time;
    private $min_time_reference;
    private $open_time;
    private $booked_color;
    private $fixed_duration;
    private $duration_rule;
    private $buffer_duration;
    private $buffer_duration_rule;
    private $reminder;
    private $event_description_content;
    private $deal_with_unrelated_events;
    private $deal_with_same_service_booked_slots;
    private $deal_with_other_service_booked_slots;
    private $email;
    private $get_details_by_email;
    private $include_uploaded_files_as_attachment;
    private $participate;
    private $additional_event_title_data;
    private $add_customer_as_guest;

    public function __construct()
    {
        $this->email = array(
            'email_text' => array(
                'subject' => '',
                'body'    => '',
            ),
        );
        $this->additional_event_title_data = array(
            'customer' => array(
                'full_name' => FALSE,
                'email'     => FALSE,
                'phone'     => FALSE,
            ),
        );
    }

    /**
     * @return int
     */
    public function getFixedDuration()
    {
        return (!$this->fixed_duration) ? 3600 : $this->fixed_duration;
    }

    /**
     * @param $seconds
     */
    public function setFixedDuration($seconds)
    {
        $this->fixed_duration = (int)$seconds;
    }

    public function setMinTimeReferenceStart()
    {
        $this->min_time_reference = 'start';
    }

    public function setMinTimeReferenceEnd()
    {
        $this->min_time_reference = 'end';
    }

    /**
     * @return string
     */
    public function getMinTimeReference()
    {
        return (!$this->min_time_reference) ? 'start' : $this->min_time_reference;
    }

    /**
     * @return int
     */
    public function getBufferDuration()
    {
        return (!$this->buffer_duration) ? 0 : $this->buffer_duration;
    }

    /**
     * @param $seconds
     */
    public function setBufferDuration($seconds)
    {
        $this->buffer_duration = (int)$seconds;
    }

    /**
     * @return string
     */
    public function getBufferDurationRule()
    {
        return NULL === $this->buffer_duration_rule ? 'always' : $this->buffer_duration_rule;
    }

    /**
     * @param string $rule
     */
    public function setBufferDurationRule($rule)
    {
        $allowed = array('always', 'after_reservation');
        if (in_array($rule, $allowed, TRUE)) {
            $this->buffer_duration_rule = $rule;
        }
    }

    /**
     * @return string
     */
    public function getDurationRule()
    {
        return (!$this->duration_rule) ? 'inherited' : $this->duration_rule;
    }

    /**
     * @param $rule
     */
    public function setDurationRule($rule)
    {
        $this->duration_rule = $rule;
    }

    /**
     * @return string
     */
    public function getLinkedEventTitle()
    {
        return trim(Toolkit\unfilterInput($this->linked_event_title));
    }

    /**
     * @param $title
     */
    public function setLinkedEventTitle($title)
    {
        $this->linked_event_title = Toolkit\filterInput($title);
    }

    /**
     * @return string
     */
    public function getAfterBookedTitle()
    {
        return trim(Toolkit\unfilterInput($this->after_booked_title));
    }

    /**
     * @param $title
     */
    public function setAfterBookedTitle($title)
    {
        $this->after_booked_title = Toolkit\filterInput($title);
    }

    /**
     * @return int
     */
    public function getEventDescriptionContent()
    {
        /**
         * 0 = leave it blank
         * 1 = name, tickets, email, phone
         * 2 = leave it as is
         */
        return isset($this->event_description_content) ? $this->event_description_content : 2; // default is 2 due to personal data regulations
    }

    /**
     * @param $content
     */
    public function setEventDescriptionContent($content)
    {
        $this->event_description_content = $content;
    }

    /**
     * @return mixed
     */
    public function getMinTime()
    {
        return $this->min_time;
    }

    /**
     * @param $interval
     */
    public function setMinTime($interval)
    {
        $this->min_time = $interval;
    }

    /**
     * @return int
     */
    public function getOpenTime()
    {
        return (!isset($this->open_time)) ? 0 : $this->open_time;
    }

    /**
     * @param $interval
     */
    public function setOpenTime($interval)
    {
        $this->open_time = $interval;
    }

    /**
     * @param $bool
     */
    public function setGetDetailsByEmail($bool)
    {
        $this->get_details_by_email = (bool)$bool;
    }

    /**
     * @return mixed
     */
    public function getGetDetailsByEmail()
    {
        return $this->get_details_by_email;
    }

    /**
     * @param $bool
     */
    public function setParticipate($bool)
    {
        $this->participate = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function isParticipate()
    {
        return isset($this->participate) ? $this->participate : TRUE;
    }

    /**
     * @param $bool
     */
    public function setIncludeFilesAsAttachment($bool)
    {
        $this->include_uploaded_files_as_attachment = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function getIncludeFilesAsAttachment()
    {
        return isset($this->include_uploaded_files_as_attachment) ? $this->include_uploaded_files_as_attachment : FALSE;
    }

    /**
     * @param $color
     */
    public function setBookedEventColor($color)
    {
        $this->booked_color = (int)$color;
    }

    /**
     * @return mixed
     */
    public function getBookedEventColor()
    {
        return $this->booked_color;
    }

    /**
     * @return mixed
     */
    public function getReminder()
    {
        return $this->reminder;
    }

    /**
     * @param $seconds
     */
    public function setReminder($seconds)
    {
        $this->reminder = (int)$seconds;
    }

    /**
     * @param null|bool $set_value
     *
     * @return bool
     */
    public function addCustomerAsGuest($set_value = NULL)
    {
        if (NULL === $set_value) {
            return (NULL === $this->add_customer_as_guest ? FALSE : (bool)$this->add_customer_as_guest);
        }

        $this->add_customer_as_guest = (bool)$set_value;

        return TRUE;
    }

    /**
     * @return bool
     */
    public function dealWithUnrelatedEvents()
    {
        return isset($this->deal_with_unrelated_events) ? (bool)$this->deal_with_unrelated_events : FALSE;
    }

    /**
     * @param $bool
     */
    public function setDealWithUnrelatedEvents($bool)
    {
        $this->deal_with_unrelated_events = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function dealWithBookedOfSameService()
    {
        return isset($this->deal_with_same_service_booked_slots) ? (bool)$this->deal_with_same_service_booked_slots : FALSE;
    }

    /**
     * @param $bool
     */
    public function setDealWithBookedOfSameService($bool)
    {
        $this->deal_with_same_service_booked_slots = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function dealWithBookedOfOtherServices()
    {
        return isset($this->deal_with_other_service_booked_slots) ? (bool)$this->deal_with_other_service_booked_slots : FALSE;
    }

    /**
     * @param $bool
     */
    public function setDealWithBookedOfOtherServices($bool)
    {
        $this->deal_with_other_service_booked_slots = (bool)$bool;
    }

    /**
     * @param $text
     */
    public function setNotificationEmailSubject($text)
    {
        $this->email['email_text']['subject'] = Toolkit\filterInput($text);
    }

    /**
     * @return string
     */
    public function getNotificationEmailSubject()
    {
        return Toolkit\unfilterInput($this->email['email_text']['subject']);
    }

    /**
     * @param $text
     */
    public function setNotificationEmailBody($text)
    {
        $this->email['email_text']['body'] = Toolkit\filterInput($text);
    }

    /**
     * @return string
     */
    public function getNotificationEmailBody()
    {
        return Toolkit\unfilterInput($this->email['email_text']['body']);
    }

    /**
     * @param array $data
     */
    public function setAdditionalEventTitleData(array $data)
    {
        $this->additional_event_title_data = $data;
    }

    /**
     * @return array
     */
    public function getAdditionalEventTitleData()
    {
        if (is_array($this->additional_event_title_data)) {
            if (!isset($this->additional_event_title_data['customer']['phone'])) {
                $this->additional_event_title_data['customer']['phone'] = FALSE;
            }

            return $this->additional_event_title_data;
        } else {
            return array(
                'customer' => array(
                    'full_name' => FALSE,
                    'email'     => FALSE,
                    'phone'     => FALSE,
                ),
            );
        }
    }

    /**
     * @return string
     */
    public function get_json()
    {
        $encoded = json_encode(get_object_vars($this));
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
        if (isset($array['linked_event_title'])) $this->setLinkedEventTitle($array['linked_event_title']);
        if (isset($array['after_booked_title'])) $this->setAfterBookedTitle($array['after_booked_title']);
        if (isset($array['min_time'])) $this->setMinTime($array['min_time']);
        if (isset($array['min_time_reference'])) $this->min_time_reference = $array['min_time_reference'];
        if (isset($array['open_time'])) $this->setOpenTime($array['open_time']);
        if (isset($array['booked_color'])) $this->setBookedEventColor($array['booked_color']);
        if (isset($array['fixed_duration'])) $this->setFixedDuration($array['fixed_duration']);
        if (isset($array['duration_rule'])) $this->setDurationRule($array['duration_rule']);
        if (isset($array['buffer_duration'])) $this->setBufferDuration($array['buffer_duration']);
        if (isset($array['reminder'])) $this->setReminder($array['reminder']);
        if (isset($array['event_description_content'])) $this->setEventDescriptionContent($array['event_description_content']);
        if (isset($array['deal_with_unrelated_events'])) $this->setDealWithUnrelatedEvents($array['deal_with_unrelated_events']);
        if (isset($array['deal_with_same_service_booked_slots'])) $this->setDealWithBookedOfSameService($array['deal_with_same_service_booked_slots']);
        if (isset($array['deal_with_other_service_booked_slots'])) $this->setDealWithBookedOfOtherServices($array['deal_with_other_service_booked_slots']);
        if (isset($array['get_details_by_email'])) $this->setGetDetailsByEmail($array['get_details_by_email']);
        if (isset($array['email'])) {
            if (isset($array['email']['subject'])) $this->setNotificationEmailSubject($array['email']['subject']);
            if (isset($array['email']['body'])) $this->setNotificationEmailBody($array['email']['body']);
        }
        if (isset($array['include_uploaded_files_as_attachment'])) $this->setIncludeFilesAsAttachment($array['include_uploaded_files_as_attachment']);
        if (isset($array['participate'])) $this->setParticipate($array['participate']);
        if (isset($array['additional_event_title_data'])) $this->setAdditionalEventTitleData($array['additional_event_title_data']);
        if (isset($array['add_customer_as_guest'])) $this->addCustomerAsGuest($array['add_customer_as_guest']);
    }

}
