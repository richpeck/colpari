<?php

defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Functions,
    TeamBooking\Toolkit;

/**
 * @deprecated 2.2.0 No longer used by internal code
 * @see        \TeamBooking\Abstracts\Service()
 *
 * Class TeamBookingType
 */
class TeamBookingType
{
    private $booking_name;
    private $booking_id;
    private $booking_class;
    private $service_color;
    private $service_info;
    private $price;
    private $active;
    private $location_setting;
    private $location_address;
    private $redirect;
    private $redirect_url;
    private $payment_must_be_done;
    private $email_for_notifications;
    private $notify;
    private $confirm;
    private $logged_only;
    private $allow_customer_cancellation;
    private $allow_customer_cancellation_reason;
    private $include_files_as_attachment; // To admin notification address
    private $send_cancellation_email; // To customer
    private $send_cancellation_email_backend; // To admin/coworker

    private $logged_max_reservations;
    private $show_times;
    private $show_soldout;
    private $show_coworker;
    private $show_coworker_url;
    private $show_reservations_left;
    private $attendees;
    private $max_reservations_per_user;

    private $coworker_assignment_rule;
    private $coworker_id_for_direct_assignment;

    private $duration_rule;
    private $default_duration;
    private $send_reminder_email;
    private $approve_rule;
    private $free_until_approval;
    private $allow_customer_cancellation_until; //seconds

    /** @var $cancellation_email TeamBookingServiceEmail */
    private $cancellation_email;

    /** @var $cancellation_email_backend TeamBookingServiceEmail */
    private $cancellation_email_backend;

    /** @var $front_end_email TeamBookingServiceEmail */
    private $front_end_email;

    /** @var $back_end_email TeamBookingServiceEmail */
    private $back_end_email;

    /** @var $reminder_email TeamBookingServiceEmail */
    private $reminder_email;

    /** @var $form_fields TeamBookingFormFields */
    private $form_fields;

    public function getConfirm()
    {
        return $this->confirm;
    }

    public function getNotify()
    {
        return $this->notify;
    }

    public function getSendCancellationEmail()
    {
        return isset($this->send_cancellation_email) ? $this->send_cancellation_email : TRUE;
    }

    public function getSendCancellationEmailBackend()
    {
        return isset($this->send_cancellation_email_backend) ? $this->send_cancellation_email_backend : TRUE;
    }

    public function isActive()
    {
        return isset($this->active) ? $this->active : TRUE;
    }

    public function getIncludeFilesAsAttachment()
    {
        return isset($this->include_files_as_attachment) ? $this->include_files_as_attachment : FALSE;
    }

    public function getName()
    {
        return Toolkit\unfilterInput($this->booking_name);
    }

    public function getId()
    {
        return $this->booking_id;
    }

    public function getLocationSetting()
    {
        if (!$this->location_setting) {
            return ($this->booking_class === 'service') ? 'none' : 'inherited';
        } else {
            return $this->location_setting;
        }
    }

    public function getLocationAddress()
    {
        return isset($this->location_address) ? $this->location_address : '';
    }

    public function getApproveRule()
    {
        return (!$this->approve_rule || $this->getPaymentMustBeDone() !== 'later') ? 'none' : $this->approve_rule;
    }

    public function getPaymentMustBeDone()
    {
        return isset($this->payment_must_be_done) ? $this->payment_must_be_done : 'immediately';
    }

    public function getFreeUntilApproval()
    {
        return isset($this->free_until_approval) ? $this->free_until_approval : FALSE;
    }

    public function getAllowCustomerCancellation()
    {
        return isset($this->allow_customer_cancellation) ? $this->allow_customer_cancellation : FALSE;
    }

    public function getAllowCustomerCancellationReason()
    {
        return isset($this->allow_customer_cancellation_reason) ? $this->allow_customer_cancellation_reason : TRUE;
    }

    public function getAllowCustomerCancellationUntil()
    {
        return isset($this->allow_customer_cancellation_until) ? $this->allow_customer_cancellation_until : (1 * DAY_IN_SECONDS);
    }

    public function getRedirect()
    {
        return isset($this->redirect) ? $this->redirect : FALSE;
    }

    public function getServiceColor()
    {
        return isset($this->service_color) ? $this->service_color : '#e07b53'; // Orange
    }

    public function getDurationRule()
    {
        return isset($this->duration_rule) ? $this->duration_rule : 'coworker';
    }

    public function getDefaultDuration()
    {
        return (isset($this->default_duration) && $this->default_duration > 0) ? $this->default_duration : 3600;
    }

    public function isClass($class_name)
    {
        return (strtolower($this->booking_class) === strtolower($class_name));
    }

    public function getLoggedOnly()
    {
        return $this->logged_only;
    }

    public function getShowReservationsLeft()
    {
        return $this->show_reservations_left;
    }

    public function getShowSoldout()
    {
        return $this->show_soldout;
    }

    public function getShowCoworker()
    {
        return $this->show_coworker;
    }

    public function getShowCoworkerUrl()
    {
        return isset($this->show_coworker_url) ? $this->show_coworker_url : FALSE;
    }

    public function getAssignmentRule()
    {
        return ($this->isClass('service') || $this->coworker_assignment_rule === 'direct') ? $this->coworker_assignment_rule : 'none';
    }

    public function getCoworkerForDirectAssignment()
    {
        return $this->coworker_id_for_direct_assignment;
    }

    public function getEmailForNotifications()
    {
        return $this->email_for_notifications;
    }

    public function getAttendees()
    {
        return $this->attendees;
    }

    public function getMaxReservationsPerUser()
    {
        return empty($this->max_reservations_per_user) ? $this->attendees : $this->max_reservations_per_user;
    }

    public function getMaxReservationsLoggedUser()
    {
        return (!$this->logged_max_reservations || !$this->logged_only) ? 0 : $this->logged_max_reservations;
    }

    public function getServiceInfo()
    {
        return Toolkit\unfilterInput($this->service_info);
    }

    /**
     *
     * @return TeamBookingServiceEmail
     */
    public function getFrontendEmail()
    {
        return $this->front_end_email;
    }

    /**
     *
     * @return TeamBookingServiceEmail
     */
    public function getBackendEmail()
    {
        return $this->back_end_email;
    }

    /**
     *
     * @return TeamBookingServiceEmail
     */
    public function getCancellationEmail()
    {
        if (isset($this->cancellation_email)) {
            return $this->cancellation_email;
        } else {
            $return = new TeamBookingServiceEmail();
            $return->setBody(__('Your reservation was cancelled.', 'team-booking'));
            $return->setSubject(__('Reservation cancelled', 'team-booking'));

            return $return;
        }
    }

    /**
     *
     * @return TeamBookingServiceEmail
     */
    public function getCancellationEmailBackend()
    {
        if (isset($this->cancellation_email_backend)) {
            return $this->cancellation_email_backend;
        } else {
            $return = new TeamBookingServiceEmail();
            $return->setBody(__('Your reservation was cancelled.', 'team-booking'));
            $return->setSubject(__('Reservation cancelled', 'team-booking'));

            return $return;
        }
    }

    /**
     *
     * @return TeamBookingServiceEmail
     */
    public function getReminderEmail()
    {
        if (isset($this->reminder_email)) {
            return $this->reminder_email;
        } else {
            $return = new TeamBookingServiceEmail();
            $return->setSubject(esc_html__("Don't forget your reservation", 'team-booking'));
            $default_reminder_body = esc_html__("We're getting close!", 'team-booking') . '<br>'
                . esc_html__('Reservation date and time', 'team-booking') . ': [start_datetime]';
            $return->setBody($default_reminder_body);

            return $return;
        }
    }

    /**
     * @return array
     */
    public function getSendReminderEmail()
    {
        return isset($this->send_reminder_email) ? $this->send_reminder_email : array();
    }

    public function isSendReminderEmail()
    {
        return (count($this->getSendReminderEmail()) < 1) ? FALSE : TRUE;
    }

    public function getReminderTimeframe()
    {
        $reminder = $this->getSendReminderEmail();
        if (isset($reminder['timeframe'])) {
            return $reminder['timeframe'];
        } else {
            return 1;
        }
    }

    /**
     *
     * @return TeamBookingFormFields
     */
    public function getFormFields()
    {
        // legacy
        if (!$this->form_fields->getUrl() instanceof TeamBookingFormTextField) {
            $obj = new TeamBookingFormTextField();
            $obj->setHook('url');
            $obj->setLabel(__('Website', 'team-booking'));
            $this->form_fields->url = $obj;
        }

        return $this->form_fields;
    }

    public function setFormFields($form_fields)
    {
        $this->form_fields = $form_fields;
    }

    /**
     *
     * @return TeamBookingShowTimes
     */
    public function getShowTimes()
    {
        return $this->show_times;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function save()
    {
        $settings = Functions\getSettings();
        $settings->updateService($this, $this->booking_id);

        return $settings->save();
    }

    public function createPostType()
    {
        if ($this->isClass('appointment')) {
            $new_object = TeamBooking\Services\Factory::createAppointment($this->getId(), $this->getName());
        } elseif ($this->isClass('event')) {
            $new_object = TeamBooking\Services\Factory::createEvent($this->getId(), $this->getName());
            $new_object->setSlotMaxUserTickets($this->getMaxReservationsPerUser());
            $new_object->setSlotMaxTickets($this->getAttendees());
            $new_object->setSettingsFor('show_tickets_left', $this->getShowReservationsLeft());
        } else {
            $new_object = TeamBooking\Services\Factory::createUnscheduled($this->getId(), $this->getName());
            $new_object->setDirectCoworkerId($this->getCoworkerForDirectAssignment());
            $new_object->setMaxReservationsUser($this->getMaxReservationsLoggedUser());
            $new_object->setSettingsFor('assignment_rule', $this->getAssignmentRule());
        }
        if ($this->isClass('event') || $this->isClass('appointment')) {
            $new_object->setEmailReminder('subject', $this->getReminderEmail()->getSubject());
            $new_object->setEmailReminder('body', $this->getReminderEmail()->getBody());
            $new_object->setEmailReminder('send', $this->isSendReminderEmail());
            $new_object->setEmailReminder('days_before', $this->getReminderTimeframe());
            $new_object->setSlotDuration($this->getDefaultDuration());
            $new_object->setSettingsFor('show_times', $this->getShowTimes()->getValue());
            $new_object->setSettingsFor('show_soldout', $this->getShowSoldout());
            $new_object->setSettingsFor('show_coworker', $this->getShowCoworker());
            $new_object->setSettingsFor('show_coworker_url', $this->getShowCoworkerUrl());
            $new_object->setSettingsFor('slot_duration', $this->getDurationRule());
            $new_object->setSettingsFor('cancellation_allowed_until', $this->getAllowCustomerCancellationUntil());
            $new_object->setSettingsFor('approval_rule', $this->getApproveRule());
            $new_object->setSettingsFor('free_until_approval', $this->getFreeUntilApproval());
        }
        $new_object->setColor($this->getServiceColor());
        $new_object->setRedirectUrl($this->redirect_url);
        $new_object->setActive($this->isActive());
        $new_object->setDescription($this->getServiceInfo());
        $new_object->setId($this->getId());
        $new_object->setName($this->getName());
        $new_object->setLocation($this->getLocationAddress());
        $new_object->setPrice($this->getPrice());
        $new_object->setEmailToAdmin('send', $this->getNotify());
        $new_object->setEmailToAdmin('to', $this->getEmailForNotifications());
        $new_object->setEmailToAdmin('subject', $this->getBackendEmail()->getSubject());
        $new_object->setEmailToAdmin('body', $this->getBackendEmail()->getBody());
        $new_object->setEmailToAdmin('attachments', $this->getIncludeFilesAsAttachment());
        $new_object->setEmailToCustomer('send', $this->getConfirm());
        $new_object->setEmailToCustomer('subject', $this->getFrontendEmail()->getSubject());
        $new_object->setEmailToCustomer('body', $this->getFrontendEmail()->getBody());
        $new_object->setEmailCancellationToAdmin('send', $this->getSendCancellationEmailBackend());
        $new_object->setEmailCancellationToAdmin('subject', $this->getCancellationEmailBackend()->getSubject());
        $new_object->setEmailCancellationToAdmin('body', $this->getCancellationEmailBackend()->getBody());
        $new_object->setEmailCancellationToCustomer('send', $this->getSendCancellationEmail());
        $new_object->setEmailCancellationToCustomer('subject', $this->getCancellationEmail()->getSubject());
        $new_object->setEmailCancellationToCustomer('body', $this->getCancellationEmail()->getBody());
        $new_object->setSettingsFor('location', $this->getLocationSetting());
        $new_object->setSettingsFor('redirect', $this->getRedirect());
        $new_object->setSettingsFor('payment', $this->getPaymentMustBeDone());
        $new_object->setSettingsFor('bookable', $this->getLoggedOnly() ? 'logged_only' : 'everyone');
        $new_object->setSettingsFor('customer_cancellation', $this->getAllowCustomerCancellation());
        $new_object->setSettingsFor('cancellation_reason_allowed', $this->getAllowCustomerCancellationReason());
        $form_id = \TeamBooking\Database\Forms::add(array());

        $new_object->setForm($form_id);
        foreach ($this->getFormFields()->getAllFields() as $field) {
            switch (get_class($field)) {
                case 'TeamBookingFormTextField':
                    \TeamBooking\Database\Forms::addElement($form_id, \TeamBooking\FormElements\Factory::getTextField($field->getProperties()));
                    break;
                case 'TeamBookingFormTextarea':
                    \TeamBooking\Database\Forms::addElement($form_id, \TeamBooking\FormElements\Factory::getTextArea($field->getProperties()));
                    break;
                case 'TeamBookingFormSelect':
                    \TeamBooking\Database\Forms::addElement($form_id, \TeamBooking\FormElements\Factory::getSelect($field->getProperties()));
                    break;
                case 'TeamBookingFormRadio':
                    \TeamBooking\Database\Forms::addElement($form_id, \TeamBooking\FormElements\Factory::getRadio($field->getProperties()));
                    break;
                case 'TeamBookingFormFileUpload':
                    \TeamBooking\Database\Forms::addElement($form_id, \TeamBooking\FormElements\Factory::getFileUpload($field->getProperties()));
                    break;
                case 'TeamBookingFormCheckbox':
                    \TeamBooking\Database\Forms::addElement($form_id, \TeamBooking\FormElements\Factory::getCheckbox($field->getProperties()));
                    break;
            }
        }
        \TeamBooking\Database\Services::add($new_object);
    }

}

/**
 * @deprecated 2.2.0 No longer used by internal code
 *
 * Object where are stored the frontend show times options
 */
class TeamBookingShowTimes
{
    private $yes;
    private $no;
    private $start_time_only;

    public function getValue()
    {
        foreach (get_object_vars($this) as $var => $value) {
            if ($value) {
                return $var;
            }
        }

        return NULL;
    }

}

/**
 * @deprecated 2.2.0 No longer used by internal code
 *
 * Class TeamBookingServiceEmail
 */
class TeamBookingServiceEmail
{
    private $subject;
    private $body;

    public function getSubject()
    {
        return Toolkit\unfilterInput($this->subject);
    }

    public function setSubject($text)
    {
        $this->subject = Toolkit\filterInput($text);
    }

    public function getBody()
    {
        return Toolkit\unfilterInput($this->body);
    }

    public function setBody($text)
    {
        $this->body = Toolkit\filterInput($text);
    }

}

/**
 * @deprecated 2.2.0 No longer used by internal code
 *
 * Class TeamBookingFormFields
 */
class TeamBookingFormFields
{

    //------------------------------------------------------------

    /** @var TeamBookingFormTextField */
    public $first_name;

    /** @var TeamBookingFormTextField */
    public $second_name;

    /** @var TeamBookingFormTextField */
    public $email;

    /** @var TeamBookingFormTextField */
    public $address;

    /** @var TeamBookingFormTextField */
    public $phone;

    /** @var TeamBookingFormTextField */
    public $url;

    /** @var TeamBookingFormTextField[] */
    public $custom_fields = array();

    public function __construct()
    {
        $this->first_name = new TeamBookingFormTextField();
        $this->second_name = new TeamBookingFormTextField();
        $this->email = new TeamBookingFormTextField();
        $this->address = new TeamBookingFormTextField();
        $this->phone = new TeamBookingFormTextField();
        $this->url = new TeamBookingFormTextField();
        $this->first_name->setHook('first_name');
        $this->first_name->setLabel(__('First name', 'team-booking'));
        $this->second_name->setHook('second_name');
        $this->second_name->setLabel(__('Last name', 'team-booking'));
        $this->email->setHook('email');
        $this->email->setLabel(__('Email', 'team-booking'));
        $this->address->setHook('address');
        $this->address->setLabel(__('Address', 'team-booking'));
        $this->phone->setHook('phone');
        $this->phone->setLabel(__('Phone number', 'team-booking'));
        $this->url->setHook('url');
        $this->url->setLabel(__('Website', 'team-booking'));
        // Defaults
        $this->first_name->setOn();
        $this->first_name->setRequiredOn();
        $this->second_name->setOn();
        $this->second_name->setRequiredOn();
        $this->email->setOn();
        $this->email->setRequiredOn();
    }

    //------------------------------------------------------------

    public function getFirstName()
    {
        return $this->first_name;
    }

    public function getSecondName()
    {
        return $this->second_name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getUrl()
    {
        return $this->url;
    }

    //------------------------------------------------------------

    /**
     * @param bool $active_only
     *
     * @return TeamBookingFormTextField[]
     */
    public function getAllFields($active_only = FALSE)
    {
        /** @var $fields TeamBookingFormTextField[] */
        $fields = array_merge($this->getBuiltInFields(), $this->getCustomFields());
        if ($active_only) {
            foreach ($fields as $key => $field) {
                if ($field instanceof TeamBookingFormTextField) {
                    if (!$field->getIsActive()) {
                        unset($fields[ $key ]);
                    }
                } else {
                    unset($fields[ $key ]);
                }
            }
        }

        return $fields;
    }

    /**
     * @return TeamBookingFormTextField[]
     */
    public function getCustomFields()
    {
        return array_filter($this->custom_fields);
    }

    public function addCustomField(TeamBookingFormTextField $field)
    {
        $this->custom_fields[] = $field;
    }

    public function updateCustomField($old_hook, TeamBookingFormTextField $new_field)
    {
        foreach ($this->custom_fields as &$field) {
            if ($field instanceof TeamBookingFormTextField && $field->getHook() == $old_hook) {
                $field = $new_field;
            }
        }
    }

    public function dropCustomField($hook)
    {
        foreach ($this->custom_fields as $key => $field) {
            if ($field instanceof TeamBookingFormTextField && $field->getHook() == $hook) {
                unset($this->custom_fields[ $key ]);
                $this->custom_fields = array_values($this->custom_fields);
                break;
            }
        }
    }

    public function saveCustomFields(array $custom_fields)
    {
        $this->custom_fields = $custom_fields;
    }

    //------------------------------------------------------------

    public function getLabelFromHook($hook)
    {
        // Loop through built-in fields
        foreach ($this->getBuiltInFields() as $field) {
            if ($field->getHook() == $hook) {
                return $field->getLabel();
            }
        }
        // Loop through custom fields
        if (!empty($this->custom_fields)) {
            foreach ($this->custom_fields as $field) {
                if ($field instanceof TeamBookingFormTextField && $field->getHook() == $hook) {
                    return $field->getLabel();
                }
            }
        }

        return FALSE;
    }

    //------------------------------------------------------------

    /**
     * @return TeamBookingFormTextField[]
     */
    public function getBuiltInFields()
    {
        $return = array();
        $return['first_name'] = $this->first_name;
        $return['second_name'] = $this->second_name;
        $return['email'] = $this->email;
        $return['address'] = $this->address;
        $return['phone'] = $this->phone;
        $return['url'] = $this->url;

        return $return;
    }

    //------------------------------------------------------------

    public function isHookDuplicate($hook)
    {
        $hooks = $this->getHookList(TRUE);

        return in_array($hook, $hooks);
    }

    //------------------------------------------------------------

    /**
     * List of active hooks
     *
     * @param bool|FALSE $files_too
     *
     * @return array
     */
    public function getHookList($files_too = FALSE)
    {
        $return = array();
        // Loop through built-in fields
        foreach ($this->getBuiltInFields() as $field) {
            if ($field->getIsActive()) {
                $return[] = $field->getHook();
            }
        }
        // Loop through custom fields
        if (!empty($this->custom_fields)) {
            foreach ($this->custom_fields as $field) {
                if ($field instanceof TeamBookingFormTextField && $field->getIsActive()) {
                    if ($files_too == TRUE || get_class($field) !== 'TeamBookingFormFileUpload') {
                        $return[] = $field->getHook();
                    }
                }
            }
        }

        return $return;
    }

    //------------------------------------------------------------

    public function getPriceIncrementFromOptionValue($hook, $value)
    {
        foreach ($this->getCustomFields() as $field) {
            if ($field->getHook() != $hook) {
                continue;
            }
            if (get_class($field) === 'TeamBookingFormSelect'
                || get_class($field) === 'TeamBookingFormRadio'
            ) {
                /** @var $field TeamBookingFormSelect */
                if ($field->getValue() instanceof TeamBooking_Components_Form_Option && $field->getValue()->getText() == $value) {
                    return $field->getValue()->getPriceIncrement();
                }
                $options = $field->getOptions();
                foreach ($options as $option) {
                    if ($option->getText() == $value) {
                        return $option->getPriceIncrement();
                    }
                }
            }
        }

        return FALSE;
    }
}
