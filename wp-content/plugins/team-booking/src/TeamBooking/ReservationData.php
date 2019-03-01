<?php

defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Functions,
    TeamBooking\Database,
    TeamBooking\Toolkit;

class TeamBooking_ReservationData
{
    private $coworker_id;
    private $customer_user_id; // WordPress user id, if registered
    private $customer_timezone;
    private $enumerable_for_customer_limit;
    private $google_calendar_event_parent;
    private $google_calendar_event;
    private $google_calendar_id;
    private $hangout_link;
    private $event_html_link;
    private $service_id;
    private $service_name;
    private $service_class;
    private $service_location;
    /* @var $form_fields TeamBooking_ReservationFormField[] */
    private $form_fields;
    private $start; // unix time
    private $end;   // unix time
    private $slot_start; // ATOM time
    private $slot_end;   // ATOM time
    private $slot_id;
    private $tickets;
    private $price; // unitary
    private $price_discounted; // unitary
    private $discount; // array with id,name,value
    private $log_key;
    private $status;
    private $cancellation_reason;
    private $cancellation_who;
    private $confirmation_who;
    private $pending_reason;
    private $email_reminder_sent;
    private $database_id;
    private $payment_gateway;
    private $paid;
    private $payment_details; // array, gateway specific
    private $currency_code;
    private $files; // array
    private $post_id;
    private $post_title;
    private $token;
    private $order_id; // only for cart operations
    private $to_be_paid = FALSE; // only for cart operations
    private $frontend_lang; // WPML compatibility
    public $error_text; // this parameter is not part of persistent reservations (database)

    public function __construct()
    {
        $this->customer_timezone = Toolkit\getTimezone()->getName();
        $this->token = Toolkit\generateToken();
    }

    /**
     * @param int $id
     */
    public function setCoworker($id)
    {
        $this->coworker_id = (int)$id;
    }

    /**
     * @return int
     */
    public function getCoworker()
    {
        return $this->coworker_id;
    }

    /**
     * @param int $id
     */
    public function setCustomerUserId($id)
    {
        $this->customer_user_id = $id;
    }

    /**
     * @return int
     */
    public function getCustomerUserId()
    {
        return NULL !== $this->customer_user_id ? $this->customer_user_id : 0;
    }

    /**
     * @param string $lang
     */
    public function setFrontendLang($lang)
    {
        $this->frontend_lang = $lang;
    }

    /**
     * @param bool $filtered
     *
     * @return mixed
     */
    public function getFrontendLang($filtered = FALSE)
    {
        if ($filtered) {
            return \TeamBooking\Actions\reservation_frontend_language($this->frontend_lang, $this);
        }

        return $this->frontend_lang;
    }

    /**
     * @return string
     */
    public function getCustomerEmail()
    {
        $return = '';
        foreach ($this->form_fields as $field) {
            if ($field->getName() === 'email') {
                $return = $field->getValue();
            }
        }

        return $return;
    }

    /**
     * @return string
     */
    public function getCustomerDisplayName()
    {
        $return = '';
        foreach ($this->form_fields as $field) {
            if ($field->getName() === 'first_name') {
                $return .= $field->getValue();
            }
        }
        foreach ($this->form_fields as $field) {
            if ($field->getName() === 'second_name') {
                $return .= ' ' . $field->getValue();
            }
        }

        return $return;
    }

    /**
     * @return bool|string
     */
    public function getCustomerAddress()
    {
        $return = FALSE;
        foreach ($this->form_fields as $field) {
            if ($field->getName() === \TeamBooking\Actions\customer_address_field_hook('address')) {
                $return = $field->getValue();
            }
        }

        return $return;
    }

    /**
     * @return bool|string
     */
    public function getCustomerPhone()
    {
        $return = FALSE;
        foreach ($this->form_fields as $field) {
            if ($field->getName() === \TeamBooking\Actions\customer_phone_field_hook('phone')) {
                $return = $field->getValue();
            }
        }

        return $return;
    }

    /**
     * @param string $timezone_name
     */
    public function setCustomerTimezone($timezone_name)
    {
        $this->customer_timezone = $timezone_name;
    }

    /**
     * @return string
     */
    public function getCustomerTimezone()
    {
        return NULL !== $this->customer_timezone ? $this->customer_timezone : Toolkit\getTimezone()->getName();
    }

    /**
     * @param $bool
     */
    public function setEnumerableForCustomerLimits($bool)
    {
        $this->enumerable_for_customer_limit = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function isEnumerableForCustomerLimits()
    {
        return NULL !== $this->enumerable_for_customer_limit ? $this->enumerable_for_customer_limit : TRUE;
    }

    /**
     * @param string $id
     */
    public function setGoogleCalendarId($id)
    {
        $this->google_calendar_id = $id;
    }

    /**
     * @return bool|string
     */
    public function getGoogleCalendarId()
    {
        return NULL !== $this->google_calendar_id ? $this->google_calendar_id : FALSE;
    }

    /**
     * @param string $id
     */
    public function setGoogleCalendarEvent($id)
    {
        $this->google_calendar_event = empty($id) ? NULL : $id;
    }

    /**
     * @return null|string
     */
    public function getGoogleCalendarEvent()
    {
        return $this->google_calendar_event;
    }

    /**
     * @param string $id
     */
    public function setGoogleCalendarEventParent($id)
    {
        $this->google_calendar_event_parent = $id;
    }

    /**
     * @return bool|string
     */
    public function getGoogleCalendarEventParent()
    {
        return NULL !== $this->google_calendar_event_parent ? $this->google_calendar_event_parent : FALSE;
    }

    /**
     * @param string $link
     */
    public function setHangoutLink($link)
    {
        $this->hangout_link = $link;
    }

    /**
     * @return mixed
     */
    public function getHangoutLink()
    {
        return $this->hangout_link;
    }

    /**
     * @param string $link
     */
    public function setEventHtmlLink($link)
    {
        $this->event_html_link = $link;
    }

    /**
     * @return mixed
     */
    public function getEventHtmlLink()
    {
        return $this->event_html_link;
    }

    /**
     * @param string $id
     */
    public function setServiceId($id)
    {
        $this->service_id = $id;
    }

    /**
     * @return mixed
     */
    public function getServiceId()
    {
        return $this->service_id;
    }

    /**
     * @param string $class
     */
    public function setServiceClass($class)
    {
        $this->service_class = $class;
    }

    /**
     * @return bool|string
     */
    public function getServiceClass()
    {
        if (!$this->service_class) {
            try {
                $return = Database\Services::get($this->service_id)->getClass();
            } catch (Exception $ex) {
                $return = FALSE;
            }

            return $return;
        } else {
            if ($this->service_class === 'service') return 'unscheduled'; //legacy

            return $this->service_class;
        }
    }

    /**
     * @param string $name
     */
    public function setServiceName($name)
    {
        $this->service_name = $name;
    }

    /**
     * @param bool $filtered
     *
     * @return string
     */
    public function getServiceName($filtered = FALSE)
    {
        if ($filtered) {
            return \TeamBooking\Actions\service_name_from_reservation($this->service_name, $this);
        }

        return $this->service_name;
    }

    /**
     * @param string $location
     */
    public function setServiceLocation($location)
    {
        $this->service_location = $location;
    }

    /**
     * @return mixed
     */
    public function getServiceLocation()
    {
        return $this->service_location;
    }

    /**
     * @param array $fields
     */
    public function setFormFields(array $fields)
    {
        $this->form_fields = $fields;
    }

    /**
     * @return TeamBooking_ReservationFormField[]
     */
    public function getFormFields()
    {
        return $this->form_fields;
    }

    /**
     * @return array
     */
    public function getFieldsArray()
    {
        $return = array();
        foreach ($this->form_fields as $field) {
            $return[ $field->getName() ] = $field->getValue(TRUE);
        }

        return $return;
    }

    /**
     * @param $google_event_time
     */
    public function setSlotStart($google_event_time)
    {
        $this->slot_start = $google_event_time;
    }

    /**
     * @return mixed
     */
    public function getSlotStart()
    {
        return $this->slot_start;
    }

    /**
     * @param $google_event_time
     */
    public function setSlotEnd($google_event_time)
    {
        $this->slot_end = $google_event_time;
    }

    /**
     * @return mixed
     */
    public function getSlotEnd()
    {
        return $this->slot_end;
    }

    /**
     * @param $slot_unique_id
     */
    public function setSlotId($slot_unique_id)
    {
        $this->slot_id = $slot_unique_id;
    }

    /**
     * @return mixed
     */
    public function getSlotId()
    {
        return $this->slot_id;
    }

    /**
     * @param int $time
     */
    public function setStart($time) // Unix
    {
        $this->start = $time;
    }

    /**
     * @return mixed
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param int $time
     */
    public function setEnd($time)
    {
        $this->end = $time;
    }

    /**
     * @return mixed
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param int $number
     */
    public function setTickets($number)
    {
        $this->tickets = $number;
    }

    /**
     * @return int
     */
    public function getTickets()
    {
        return NULL !== $this->tickets ? $this->tickets : 1;
    }

    /**
     * @param $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param $price
     */
    public function setPriceDiscounted($price)
    {
        $this->price_discounted = $price;
    }

    /**
     * @return mixed
     */
    public function getPriceDiscounted()
    {
        return NULL === $this->price_discounted ? FALSE : $this->price_discounted;
    }

    /**
     * @param array $discount
     */
    public function setDiscount(array $discount)
    {
        $this->discount = $discount;
    }

    /**
     * @param array $discount
     */
    public function addDiscount(array $discount)
    {
        $this->discount[] = $discount;
    }

    /**
     * @return array
     */
    public function getDiscount()
    {
        return is_array($this->discount) ? $this->discount : array();
    }

    /**
     * @param $now
     */
    public function setCreationInstant($now)
    {
        $this->log_key = $now;
    }

    /**
     * @return mixed
     */
    public function getCreationInstant()
    {
        return $this->log_key;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function setStatusPending()
    {
        $this->status = 'pending';
    }

    public function setStatusWaitingApproval()
    {
        $this->status = 'waiting_approval';
    }

    public function setStatusConfirmed()
    {
        $this->status = 'confirmed';
    }

    /**
     * This is for Unscheduled Services
     */
    public function setStatusDone()
    {
        $this->status = 'done';
    }

    public function setStatusCancelled()
    {
        $this->status = 'cancelled';
    }

    /**
     * @return bool
     */
    public function isCancelled()
    {
        return ($this->status === 'cancelled');
    }

    /**
     * @return bool
     */
    public function isDone()
    {
        return ($this->status === 'done');
    }

    /**
     * @return bool
     */
    public function isConfirmed()
    {
        return ($this->status === 'confirmed' || NULL === $this->status);
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return ($this->status === 'pending');
    }

    /**
     * @return bool
     */
    public function isWaitingApproval()
    {
        return ($this->status === 'waiting_approval');
    }

    /**
     * Returns wether to consider the reservation as booked (e.g. for tickets count and so on) or not
     *
     * @return bool
     */
    public function isBooked()
    {
        if ($this->isWaitingApproval()) {
            try {
                $service_free_until_approval = Database\Services::get($this->getServiceId())->getSettingsFor('free_until_approval');
            } catch (Exception $e) {
                $service_free_until_approval = FALSE;
            }
        }

        return $this->isConfirmed()
            || ($this->isPending() && !Functions\isReservationTimedOut($this))
            || ($this->isWaitingApproval() && !$service_free_until_approval);
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return (!$this->status) ? 'confirmed' : $this->status;
    }

    /**
     * @param $text
     */
    public function setCancellationReason($text)
    {
        $this->cancellation_reason = $text;
    }

    /**
     * @return string
     */
    public function getCancellationReason()
    {
        return NULL !== $this->cancellation_reason ? $this->cancellation_reason : '';
    }

    /**
     * @param $who
     */
    public function setCancellationWho($who)
    {
        $this->cancellation_who = $who;
    }

    /**
     * @return bool|int
     */
    public function getCancellationWho()
    {
        return NULL !== $this->cancellation_who ? $this->cancellation_who : FALSE;
    }

    /**
     * @param $who
     */
    public function setConfirmationWho($who)
    {
        $this->confirmation_who = $who;
    }

    /**
     * @return bool|int
     */
    public function getConfirmationWho()
    {
        return NULL !== $this->confirmation_who ? $this->confirmation_who : FALSE;
    }

    /**
     * @param $text
     */
    public function setPendingReason($text)
    {
        $this->pending_reason = $text;
    }

    /**
     * @return string
     */
    public function getPendingReason()
    {
        return NULL !== $this->pending_reason ? $this->pending_reason : '';
    }

    /**
     * @return bool
     */
    public function isEmailReminderSent()
    {
        return ($this->email_reminder_sent === TRUE);
    }

    /**
     * @param $bool
     */
    public function setEmailReminderSent($bool)
    {
        $this->email_reminder_sent = (bool)$bool;
    }

    /**
     * @param bool $filtered
     *
     * @return mixed
     */
    public function getDatabaseId($filtered = FALSE)
    {
        if ($filtered) {
            return \TeamBooking\Actions\reservation_id($this);
        }

        return $this->database_id;
    }

    /**
     * @param $id
     */
    public function setDatabaseId($id)
    {
        $this->database_id = (int)$id;
    }

    /**
     * @param $gateway_id
     */
    public function setPaymentGateway($gateway_id)
    {
        $this->payment_gateway = $gateway_id;
    }

    /**
     * @return mixed
     */
    public function getPaymentGateway()
    {
        return $this->payment_gateway;
    }

    /**
     * @param boolean $bool
     */
    public function setPaid($bool)
    {
        $this->paid = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function isPaid()
    {
        return (bool)$this->paid;
    }

    /**
     * @param array $details
     */
    public function setPaymentDetails(array $details)
    {
        $this->payment_details = $details;
    }

    /**
     * @return array
     */
    public function getPaymentDetails()
    {
        return is_array($this->payment_details) ? $this->payment_details : array();
    }

    /**
     * @param $code
     */
    public function setCurrencyCode($code)
    {
        $this->currency_code = $code;
    }

    /**
     * @return mixed
     */
    public function getCurrencyCode()
    {
        return $this->currency_code;
    }

    /**
     * @param array $files
     */
    public function setFileReference(array $files)
    {
        $this->files = $files;
    }

    /**
     * @param string $hook
     * @param array  $returned_handle
     */
    public function addFileReference($hook, array $returned_handle)
    {
        $this->files[ $hook ] = $returned_handle;
    }

    /**
     * @param $hook
     */
    public function removeFileReference($hook)
    {
        if (is_array($this->files) && isset($this->files[ $hook ])) {
            $file = $this->files[ $hook ];
            unlink($file['file']);
            unset($this->files[ $hook ]);
        }
    }

    public function removeFiles()
    {
        if (is_array($this->files)) {
            foreach ($this->files as $file) {
                unlink($file['file']);
            }
            $this->files = array();
        }
    }

    /**
     * @return array
     */
    public function getFilesReferences()
    {
        return is_array($this->files) ? $this->files : array();
    }

    /**
     * @return bool
     */
    public function isFromContainer()
    {
        return ($this->getGoogleCalendarEventParent() !== FALSE);
    }

    /**
     * @param $id
     */
    public function setPostId($id)
    {
        $this->post_id = $id;
    }

    /**
     * @return mixed
     */
    public function getPostId()
    {
        return $this->post_id;
    }

    /**
     * @param $title
     */
    public function setPostTitle($title)
    {
        $this->post_title = $title;
    }

    /**
     * @return mixed
     */
    public function getPostTitle()
    {
        return $this->post_title;
    }

    /**
     * @param $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string|NULL
     */
    public function getOrderId()
    {
        return $this->order_id;
    }

    /**
     * @param string $order_id
     */
    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
    }

    /**
     * @return bool
     */
    public function isToBePaid()
    {
        return $this->isPending() ? TRUE : $this->to_be_paid;
    }

    /**
     * @param bool $bool
     */
    public function setToBePaid($bool)
    {
        $this->to_be_paid = (bool)$bool;
    }

    /**
     * @param string $operation
     * @param bool   $show_confirm
     *
     * @return string
     */
    public function get_customer_action_link($operation, $show_confirm = FALSE)
    {
        $return = admin_url() . "admin-ajax.php?action=teambooking_rest_api&operation={$operation}&id={$this->getDatabaseId()}&checksum={$this->getToken()}";
        if ($show_confirm) {
            $return .= '&show_confirm=1';
        }

        return $return;

    }

    /**
     * @param string $operation
     * @param bool   $show_confirm
     *
     * @return string
     */
    public function get_admin_action_link($operation, $show_confirm = FALSE)
    {
        $return = admin_url() . "admin-ajax.php?action=teambooking_rest_api&operation={$operation}&id={$this->getDatabaseId()}&checksum={$this->getToken()}"
            . '&ctoken=' . Functions\getSettings()->getCoworkerData($this->getCoworker())->getApiToken();
        if ($show_confirm) {
            $return .= '&show_confirm=1';
        }

        return $return;

    }


    /**
     * Returns the hooks array
     *
     * @param bool $customer_timezone
     *
     * @return array
     */
    public function getHooksArray($customer_timezone = FALSE)
    {
        $hooks_values_array = array(
            'reservation_id'    => '#' . $this->getDatabaseId(TRUE),
            'tickets_quantity'  => $this->getTickets(),
            'unit_price'        => Functions\currencyCodeToSymbol($this->getPriceIncremented(), $this->getCurrencyCode()),
            'total_price'       => Functions\currencyCodeToSymbol($this->getPriceIncremented() * $this->getTickets(), $this->getCurrencyCode()),
            'post_id'           => $this->getPostId(),
            'post_title'        => $this->getPostTitle(),
            'service_name'      => $this->getServiceName(TRUE),
            'service_location'  => $this->getServiceLocation(),
            'coworker_name'     => Functions\getSettings()->getCoworkerData($this->getCoworker())->getDisplayName(),
            'coworker_url'      => Functions\getSettings()->getCoworkerUrl($this->getCoworker()),
            'hangout_link'      => $this->getHangoutLink(),
            'gcal_event_link'   => $this->getEventHtmlLink(),
            'reason'            => $this->getCancellationReason(),
            'order_id'          => $this->getOrderId(),
            'cancellation_link' => $this->get_customer_action_link('cancel', TRUE),
            'pay_link'          => $this->get_customer_action_link('pay', TRUE),
            'ics_link'          => $this->get_customer_action_link('ics', TRUE),
            'decline_link'      => $this->get_admin_action_link('decline', TRUE),
            'approve_link'      => $this->get_admin_action_link('approve', TRUE),
        );
        if ($this->getServiceClass() !== 'unscheduled') {
            if ($customer_timezone) {
                $timezone = Toolkit\getTimezone(Functions\parse_timezone_aliases($this->getCustomerTimezone()));
            } else {
                $timezone = Toolkit\getTimezone();
            }
            $hooks_values_array['timezone'] = $this->getCustomerTimezone();
            if ($this->isAllDay()) {
                $hooks_values_array['start_date'] = Functions\dateFormatter(Functions\strtotime_tb($this->getSlotStart()), $this->isAllDay(), $timezone)->date;
                $hooks_values_array['start_time'] = esc_html__('All day', 'team-booking');
            } else {
                $hooks_values_array['start_date'] = Functions\dateFormatter(Functions\strtotime_tb($this->getSlotStart()), $this->isAllDay(), $timezone)->date;
                $hooks_values_array['start_time'] = Functions\dateFormatter(Functions\strtotime_tb($this->getSlotStart()), $this->isAllDay(), $timezone)->time;
            }
            $hooks_values_array['start_datetime'] = $hooks_values_array['start_date'] . ' ' . $hooks_values_array['start_time'];
            if ($this->isAllDay()) {
                $hooks_values_array['end_date'] = Functions\dateFormatter(Functions\strtotime_tb($this->getSlotEnd()), $this->isAllDay(), $timezone)->date;
                $hooks_values_array['end_time'] = esc_html__('All day', 'team-booking');
            } else {
                $hooks_values_array['end_date'] = Functions\dateFormatter(Functions\strtotime_tb($this->getSlotEnd()), $this->isAllDay(), $timezone)->date;
                $hooks_values_array['end_time'] = Functions\dateFormatter(Functions\strtotime_tb($this->getSlotEnd()), $this->isAllDay(), $timezone)->time;
            }
            $hooks_values_array['end_datetime'] = $hooks_values_array['end_date'] . ' ' . $hooks_values_array['end_time'];
        }

        return array_merge($hooks_values_array, $this->getFieldsArray());
    }

    /**
     * @return float
     */
    public function getPriceIncremented()
    {
        $fields = $this->getFormFields();
        $currency = Toolkit\getCurrencies($this->getCurrencyCode());
        $precision = isset($currency['decimal']) && !$currency['decimal'] ? 0 : 2;
        $increment = 0;
        foreach ($fields as $field) {
            $increment += $field->getPriceIncrement();
        }
        if (count($this->getDiscount()) >= 1) {
            return round($this->getPriceDiscounted() + $increment, $precision);
        }

        return round($this->getPrice() + $increment, $precision);
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    public function applyCoupon($code)
    {
        $promotions = Database\Promotions::getByClassAndService('coupon', $this->getServiceId(), TRUE, TRUE);
        foreach ($promotions as $id => $promotion) {
            /** @var $promotion \TeamBooking_Promotions_Coupon */
            if ($promotion->validateCode($code)) {
                if ($promotion->getDiscountType() === 'percentage') {
                    $value = $this->getPriceDiscounted() - ($this->getPrice() - Toolkit\applyPercentage($this->getPrice(), $promotion->getDiscount()));
                } else {
                    $value = $this->getPriceDiscounted() - $promotion->getDiscount();
                }
                if ($value < 0) {
                    $value = 0;
                }
                $this->setPriceDiscounted($value);
                $this->addDiscount(array(
                    'name'   => $promotion->getName(),
                    'value'  => $promotion->getDiscount(),
                    'type'   => $promotion->getDiscountType(),
                    'id'     => $id,
                    'coupon' => $code
                ));

                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * @return string
     */
    public function getChecksum()
    {
        $back_start = $this->start;
        $back_end = $this->end;
        $this->start = NULL;
        $this->end = NULL;
        $return = md5(serialize($this));
        $this->start = $back_start;
        $this->end = $back_end;

        return $return;
    }

    /**
     * @param TeamBooking_ReservationData $reservation
     *
     * @return bool
     */
    public function verifyChecksum(TeamBooking_ReservationData $reservation)
    {
        $back_start = $reservation->getStart();
        $back_end = $reservation->getEnd();
        $reservation->setStart(NULL);
        $reservation->setEnd(NULL);
        $hash_guest = md5(serialize($reservation));
        $reservation->setStart($back_start);
        $reservation->setEnd($back_end);
        if ($hash_guest === $this->getChecksum()) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * @return bool
     */
    public function isAllDay()
    {
        return strlen($this->getSlotStart()) === 10;
    }

    /**
     * @return TeamBooking_ReservationData $this
     */
    public function insert_into_database()
    {
        $this->setDatabaseId(Database\Reservations::insert($this));

        return $this;
    }

    /**
     * @return array
     */
    public function getApiResource()
    {
        $return = array(
            'type'             => 'reservation',
            'id'               => $this->database_id,
            'token'            => $this->token,
            'coworkerID'       => $this->coworker_id,
            'customerUserID'   => $this->customer_user_id,
            'customerTimezone' => $this->customer_timezone,
            'service'          => array(
                'id'       => $this->service_id,
                'location' => $this->service_location
            ),
            'datetime'         => array(
                'start' => $this->start,
                'end'   => $this->end
            ),
            'payment'          => array(
                'amount'   => $this->getPriceIncremented(),
                'isPaid'   => $this->isPaid(),
                'currency' => $this->currency_code,
                'gateway'  => $this->payment_gateway
            ),
            'status'           => $this->getStatus(),
            'tickets'          => $this->tickets,
            'creationDateTime' => $this->log_key,
            'reminderSent'     => $this->isEmailReminderSent(),
            'referer'          => array(
                'postID'    => $this->post_id,
                'postTitle' => $this->post_title

            )
        );
        $fields = array();
        foreach ($this->form_fields as $field) {
            $fields[] = array(
                'type'           => 'form_field',
                'name'           => $field->getName(),
                'value'          => Toolkit\unfilterInput($field->getValue()),
                'priceIncrement' => $field->getPriceIncrement()
            );
        }
        $return['formFields'] = $fields;

        return $return;
    }

    /**
     * @param $resource
     *
     * @return TeamBooking_ReservationData
     */
    public static function parseApiResource($resource)
    {
        $reservation_data = new TeamBooking_ReservationData();
        try {
            $service = Database\Services::get($resource->service->id);
        } catch (Exception $e) {
            $service = FALSE;
        }
        if (isset($resource->customerUserID)) $reservation_data->setCustomerUserId($resource->customerUserID);
        if (isset($resource->customerTimezone)) $reservation_data->setCustomerTimezone($resource->customerTimezone);
        $reservation_data->setServiceId($resource->service->id);
        if (isset($resource->coworkerID)) $reservation_data->setCoworker($resource->coworkerID);
        if (isset($resource->referer->postID)) $reservation_data->setPostId($resource->referer->postID);
        if (isset($resource->referer->postTitle)) $reservation_data->setPostTitle($resource->referer->postTitle);
        if ($service) $reservation_data->setServiceClass($service->getClass());
        if ($service) $reservation_data->setServiceName($service->getName());
        if (isset($resource->service->location)) $reservation_data->setServiceLocation($resource->service->location);
        if (isset($resource->datetime->start)) $reservation_data->setStart($resource->datetime->start);
        if (isset($resource->datetime->end)) $reservation_data->setEnd($resource->datetime->end);

        if ($service) $reservation_data->setPrice($service->getPrice());

        if (isset($resource->payment->isPaid)) $reservation_data->setPaid($resource->payment->isPaid);
        if (isset($resource->payment->currency)) $reservation_data->setCurrencyCode($resource->payment->currency);
        if (isset($resource->payment->gateway)) $reservation_data->setPaymentGateway($resource->payment->gateway);
        if (isset($resource->status)) $reservation_data->setStatus($resource->status);
        if (isset($resource->tickets)) $reservation_data->setTickets($resource->tickets);
        if (isset($resource->lang)) $reservation_data->setFrontendLang($resource->lang);
        $reservation_data->setCreationInstant(Toolkit\getNowInSecondsUTC());
        $reservation_data->setCurrencyCode(Functions\getSettings()->getCurrencyCode());
        if (isset($resource->reminderSent)) $reservation_data->setEmailReminderSent($resource->reminderSent);

        $form_fields = array();
        if (isset($resource->formFields)) {
            $expected_fields = Database\Forms::getActiveHooks($service->getForm());
            foreach ($expected_fields as $name) {
                // $name is the hook
                $form_field = new \TeamBooking_ReservationFormField();
                $form_field->setName($name);
                $found = FALSE;
                foreach ($resource->formFields as $form_field_given) {
                    if ($name === $form_field_given->name) {
                        $form_field->setLabel($service ? Database\Forms::getTitleFromHook($service->getForm(), $form_field_given->name) : $form_field_given->name);
                        $form_field->setValue($form_field_given->value);
                        $form_field->setServiceId($resource->service->id);
                        $form_field->setPriceIncrement($service ? Database\Forms::getPriceIncrementFromOptionValue($service->getForm(), $form_field_given->name, $form_field_given->value) : $form_field_given->priceIncrement);
                        $found = TRUE;
                    }
                }
                if (!$found) {
                    // We are facing unchecked checkboxes here...
                    $form_field->setValue(__('Not selected', 'team-booking'));
                }
                $form_fields[] = $form_field;
            }
        }
        $reservation_data->setFormFields($form_fields);

        if (isset($resource->payment->amount)) {
            if ($resource->payment->amount !== $reservation_data->getPriceIncremented()) {
                $diff = abs($reservation_data->getPriceIncremented() - $resource->payment->amount) / $reservation_data->getTickets();
                if ($reservation_data->getPrice() - $diff >= 0) {
                    $reservation_data->setPriceDiscounted($reservation_data->getPrice() - $diff);
                } else {
                    $reservation_data->setPriceDiscounted(0);
                }
            }
        }

        return $reservation_data;
    }
}
