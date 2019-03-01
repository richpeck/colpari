<?php

namespace TeamBooking\Database;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Functions,
    TeamBooking\Toolkit,
    TeamBooking\Cache;

/**
 * Class Reservations
 *
 * @author VonStroheim
 */
class Reservations
{
    private static $relevant_columns = array(
        'id',
        'coworker_id',
        'customer_id',
        'customer_timezone',
        'enum_for_limit',
        'event_parent_id',
        'event_id',
        'calendar_id',
        'hangout_url',
        'event_url',
        'service_id',
        'service_name',
        'service_class',
        'service_location',
        'form_fields',
        'start',
        'end',
        'slot_start',
        'slot_end',
        'tickets',
        'price',
        'price_discounted',
        'discounts',
        'created_utc',
        'status',
        'canc_reason',
        'canc_who',
        'confirm_who',
        'pending_reason',
        'email_reminder_sent',
        'payment_gateway',
        'currency_code',
        'paid',
        'payment_details',
        'files',
        'post_id',
        'post_title',
        'order_id',
        'wants_payment',
        'frontend_lang',
        'token'
    );

    /**
     * @param bool|string $filter
     * @param int         $per_page
     * @param int         $page_number
     * @param string      $order_by
     * @param string      $order
     *
     * @return \TeamBooking_ReservationData[]
     */
    public static function getAll($filter = FALSE, $per_page = 0, $page_number = 0, $order_by = 'id', $order = 'asc')
    {
        if (NULL !== Cache::get('reservations' . $filter . $per_page . $page_number . $order_by . $order)) {
            $return = Cache::get('reservations' . $filter . $per_page . $page_number . $order_by . $order);
        } else {
            global $wpdb;
            $table_name = $wpdb->prefix . 'teambooking_reservations';
            $columns = rtrim(implode(', ', static::$relevant_columns), ', ');
            $query = "SELECT $columns FROM $table_name";
            if ($filter === 'pending') {
                $query = $wpdb->prepare("SELECT $columns FROM $table_name WHERE status = %s", 'pending');
            }
            if ($filter === 'upcoming') {
                $now = current_time('timestamp', TRUE);
                $query = $wpdb->prepare("SELECT $columns FROM $table_name WHERE start >= %d", $now);
            }
            if ($filter === 'unscheduled') {
                $query = "SELECT $columns FROM $table_name WHERE start IS NULL";
            }
            if ($filter === 'waiting_approval') {
                $query = $wpdb->prepare("SELECT $columns FROM $table_name WHERE status = %s", 'waiting_approval');
            }
            $query .= ' ORDER BY ' . $order_by . ' ' . strtoupper($order);
            if ($per_page !== 0 && $page_number !== 0) {
                $query .= ' LIMIT ' . ($page_number - 1) * $per_page . ', ' . $per_page;
            }
            $results = $wpdb->get_results($query);
            $return = array();
            foreach ($results as $result) {
                $return[ $result->id ] = static::getInstance($result);
            }
            Cache::add($return, 'reservations' . $filter . $per_page . $page_number . $order_by . $order);
        }

        return $return;
    }

    /**
     * @param bool|string $filter
     *
     * @return mixed
     */
    public static function count($filter = FALSE)
    {
        global $wpdb;
        $query = "SELECT COUNT(*) FROM {$wpdb->prefix}teambooking_reservations";
        if ($filter === 'pending') {
            $query = $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}teambooking_reservations WHERE status = %s", 'pending');
        }
        if ($filter === 'upcoming') {
            $now = current_time('timestamp', TRUE);
            $query = $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}teambooking_reservations WHERE start >= %d", $now);
        }
        if ($filter === 'unscheduled') {
            $query = "SELECT COUNT(*) FROM {$wpdb->prefix}teambooking_reservations WHERE start IS NULL";
        }
        if ($filter === 'waiting_approval') {
            $query = $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}teambooking_reservations WHERE status = %s", 'waiting_approval');
        }
        if ($filter === 'orders') {
            $query = "SELECT COUNT(DISTINCT order_id) FROM {$wpdb->prefix}teambooking_reservations WHERE order_id <> ''";
        }

        return $wpdb->get_var($query);
    }

    /**
     * @param int         $id
     * @param bool|string $filter
     *
     * @return mixed
     */
    public static function countByCoworker($id, $filter = FALSE)
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}teambooking_reservations WHERE coworker_id = %d", $id);
        if ($filter === 'pending') {
            $query = $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}teambooking_reservations WHERE coworker_id = %d AND status = %s", $id, 'pending');
        }
        if ($filter === 'upcoming') {
            $now = current_time('timestamp', TRUE);
            $query = $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}teambooking_reservations WHERE coworker_id = %d AND start >= %d", $id, $now);
        }
        if ($filter === 'unscheduled') {
            $query = $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}teambooking_reservations WHERE coworker_id = %d AND start IS NULL", $id);
        }
        if ($filter === 'waiting_approval') {
            $query = $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}teambooking_reservations WHERE coworker_id = %d AND status = %s", $id, 'waiting_approval');
        }
        if ($filter === 'orders') {
            $query = $wpdb->prepare("SELECT COUNT(DISTINCT order_id) FROM {$wpdb->prefix}teambooking_reservations WHERE coworker_id = %d AND order_id <> ''", $id);
        }

        return $wpdb->get_var($query);
    }

    /**
     * @param bool|string $filter
     *
     * @return array
     */
    public static function getAllIds($filter = FALSE)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $query = "SELECT id FROM $table_name";
        if ($filter === 'pending') {
            $query = $wpdb->prepare("SELECT id FROM $table_name WHERE status = %s", 'pending');
        }

        $results = $wpdb->get_results($query);
        $return = array();

        foreach ($results as $result) {
            $return[] = $result->id;
        }

        return $return;
    }

    /**
     * @param \TeamBooking_ReservationData $data
     *
     * @return mixed
     */
    public static function insert(\TeamBooking_ReservationData $data)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $created = current_time('mysql');
        $wpdb->insert($table_name, array(
            'created'             => $created,
            'service_id'          => $data->getServiceId(),
            'coworker_id'         => $data->getCoworker(),
            'calendar_id'         => $data->getGoogleCalendarId(),
            'event_id'            => $data->getGoogleCalendarEvent(),
            'status'              => $data->getStatus(),
            'token'               => $data->getToken(),
            'start'               => $data->getStart(),
            'end'                 => $data->getEnd(),
            'customer_id'         => $data->getCustomerUserId(),
            'customer_timezone'   => $data->getCustomerTimezone(),
            'enum_for_limit'      => $data->isEnumerableForCustomerLimits(),
            'event_parent_id'     => $data->getGoogleCalendarEventParent(),
            'hangout_url'         => $data->getHangoutLink(),
            'event_url'           => $data->getEventHtmlLink(),
            'service_name'        => $data->getServiceName(),
            'service_class'       => $data->getServiceClass(),
            'service_location'    => $data->getServiceLocation(),
            'tickets'             => $data->getTickets(),
            'price'               => $data->getPrice(),
            'price_discounted'    => $data->getPriceDiscounted(),
            'pending_reason'      => $data->getPendingReason(),
            'canc_reason'         => $data->getCancellationReason(),
            'canc_who'            => $data->getCancellationWho(),
            'confirm_who'         => $data->getConfirmationWho(),
            'email_reminder_sent' => $data->isEmailReminderSent(),
            'paid'                => $data->isPaid(),
            'payment_gateway'     => $data->getPaymentGateway(),
            'currency_code'       => $data->getCurrencyCode(),
            'post_id'             => $data->getPostId(),
            'post_title'          => $data->getPostTitle(),
            'slot_start'          => $data->getSlotStart(),
            'slot_end'            => $data->getSlotEnd(),
            'customer_nicename'   => $data->getCustomerDisplayName(),
            'created_utc'         => $data->getCreationInstant(),
            'order_id'            => $data->getOrderId(),
            'wants_payment'       => $data->isToBePaid(),
            'frontend_lang'       => $data->getFrontendLang(),
            'form_fields'         => static::encode_object($data->getFormFields()),
            'discounts'           => static::encode_object($data->getDiscount()),
            'payment_details'     => static::encode_object($data->getPaymentDetails()),
            'files'               => static::encode_object($data->getFilesReferences())
        ));

        return $wpdb->insert_id;
    }

    /**
     * @param \TeamBooking_ReservationData $data
     *
     * @return mixed
     */
    public static function update(\TeamBooking_ReservationData $data)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $result = $wpdb->update($table_name, array(
            'coworker_id'         => $data->getCoworker(),
            'calendar_id'         => $data->getGoogleCalendarId(),
            'event_id'            => $data->getGoogleCalendarEvent(),
            'service_id'          => $data->getServiceId(),
            'status'              => $data->getStatus(),
            'token'               => $data->getToken(),
            'start'               => $data->getStart(),
            'end'                 => $data->getEnd(),
            'customer_id'         => $data->getCustomerUserId(),
            'customer_timezone'   => $data->getCustomerTimezone(),
            'enum_for_limit'      => $data->isEnumerableForCustomerLimits(),
            'event_parent_id'     => $data->getGoogleCalendarEventParent(),
            'hangout_url'         => $data->getHangoutLink(),
            'event_url'           => $data->getEventHtmlLink(),
            'service_name'        => $data->getServiceName(),
            'service_class'       => $data->getServiceClass(),
            'service_location'    => $data->getServiceLocation(),
            'tickets'             => $data->getTickets(),
            'price'               => $data->getPrice(),
            'price_discounted'    => $data->getPriceDiscounted(),
            'pending_reason'      => $data->getPendingReason(),
            'canc_reason'         => $data->getCancellationReason(),
            'canc_who'            => $data->getCancellationWho(),
            'confirm_who'         => $data->getConfirmationWho(),
            'email_reminder_sent' => $data->isEmailReminderSent(),
            'paid'                => $data->isPaid(),
            'payment_gateway'     => $data->getPaymentGateway(),
            'currency_code'       => $data->getCurrencyCode(),
            'post_id'             => $data->getPostId(),
            'post_title'          => $data->getPostTitle(),
            'slot_start'          => $data->getSlotStart(),
            'slot_end'            => $data->getSlotEnd(),
            'customer_nicename'   => $data->getCustomerDisplayName(),
            'created_utc'         => $data->getCreationInstant(),
            'order_id'            => $data->getOrderId(),
            'wants_payment'       => $data->isToBePaid(),
            'frontend_lang'       => $data->getFrontendLang(),
            'form_fields'         => static::encode_object($data->getFormFields()),
            'discounts'           => static::encode_object($data->getDiscount()),
            'payment_details'     => static::encode_object($data->getPaymentDetails()),
            'files'               => static::encode_object($data->getFilesReferences()),
        ), array('id' => $data->getDatabaseId()));

        return $result;
    }

    /**
     * @param                              $calendar_id
     * @param                              $event_id
     * @param \TeamBooking_ReservationData $data
     *
     * @return mixed
     */
    public static function updateByCalendarEventId($calendar_id, $event_id, \TeamBooking_ReservationData $data)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';

        return $wpdb->update(
            $table_name,
            array(
                'coworker_id'         => $data->getCoworker(),
                'event_id'            => $data->getGoogleCalendarEvent(),
                'calendar_id'         => $data->getGoogleCalendarId(),
                'status'              => $data->getStatus(),
                'service_id'          => $data->getServiceId(),
                'start'               => $data->getStart(),
                'end'                 => $data->getEnd(),
                'token'               => $data->getToken(),
                'customer_id'         => $data->getCustomerUserId(),
                'customer_timezone'   => $data->getCustomerTimezone(),
                'enum_for_limit'      => $data->isEnumerableForCustomerLimits(),
                'event_parent_id'     => $data->getGoogleCalendarEventParent(),
                'hangout_url'         => $data->getHangoutLink(),
                'event_url'           => $data->getEventHtmlLink(),
                'service_name'        => $data->getServiceName(),
                'service_class'       => $data->getServiceClass(),
                'service_location'    => $data->getServiceLocation(),
                'tickets'             => $data->getTickets(),
                'price'               => $data->getPrice(),
                'price_discounted'    => $data->getPriceDiscounted(),
                'pending_reason'      => $data->getPendingReason(),
                'canc_reason'         => $data->getCancellationReason(),
                'canc_who'            => $data->getCancellationWho(),
                'confirm_who'         => $data->getConfirmationWho(),
                'email_reminder_sent' => $data->isEmailReminderSent(),
                'paid'                => $data->isPaid(),
                'payment_gateway'     => $data->getPaymentGateway(),
                'currency_code'       => $data->getCurrencyCode(),
                'post_id'             => $data->getPostId(),
                'post_title'          => $data->getPostTitle(),
                'slot_start'          => $data->getSlotStart(),
                'slot_end'            => $data->getSlotEnd(),
                'customer_nicename'   => $data->getCustomerDisplayName(),
                'created_utc'         => $data->getCreationInstant(),
                'order_id'            => $data->getOrderId(),
                'wants_payment'       => $data->isToBePaid(),
                'frontend_lang'       => $data->getFrontendLang(),
                'form_fields'         => static::encode_object($data->getFormFields()),
                'discounts'           => static::encode_object($data->getDiscount()),
                'payment_details'     => static::encode_object($data->getPaymentDetails()),
                'files'               => static::encode_object($data->getFilesReferences())
            ),
            array(
                'calendar_id' => $calendar_id,
                'event_id'    => $event_id
            )
        );
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public static function delete($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        if (is_array($id)) {
            $how_many = count($id);
            $placeholders = array_fill(0, $how_many, '%d');
            $format = implode(', ', $placeholders);
            $result = $wpdb->get_results($wpdb->prepare("DELETE FROM $table_name WHERE id IN ($format)", $id));
        } else {
            $result = $wpdb->delete($table_name, array('id' => $id));
        }

        return $result;
    }

    /**
     * @return false|int
     */
    public static function deleteAll()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';

        return $wpdb->query("TRUNCATE TABLE $table_name");
    }

    /**
     * @param $id
     *
     * @return bool|\TeamBooking_ReservationData
     */
    public static function getById($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $columns = rtrim(implode(', ', static::$relevant_columns), ', ');
        $result = $wpdb->get_row($wpdb->prepare("SELECT $columns FROM $table_name WHERE id = %d", $id));
        if (!$result) return FALSE;

        return static::getInstance($result);
    }

    /**
     * @param array $ids
     *
     * @return \TeamBooking_ReservationData[]
     */
    public static function getByIds(array $ids)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $columns = rtrim(implode(', ', static::$relevant_columns), ', ');
        $how_many = count($ids);
        $placeholders = array_fill(0, $how_many, '%d');
        $format = implode(', ', $placeholders);
        $results = $wpdb->get_results($wpdb->prepare("SELECT $columns FROM $table_name WHERE id IN ($format)", $ids));
        $return = array();
        foreach ($results as $result) {
            $return[ $result->id ] = static::getInstance($result);
        }

        return $return;
    }

    /**
     * @param $calendar_id
     * @param $event_id
     *
     * @return \TeamBooking_ReservationData[]
     */
    public static function getByCalendarEventId($calendar_id, $event_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $columns = rtrim(implode(', ', static::$relevant_columns), ', ');
        $results = $wpdb->get_results($wpdb->prepare("SELECT $columns FROM $table_name WHERE calendar_id = %s AND event_id = %s", array($calendar_id, $event_id)));

        $return = array();
        foreach ($results as $result) {
            $return[ $result->id ] = static::getInstance($result);
        }

        return $return;
    }

    /**
     * @param int         $id
     * @param bool|string $filter
     * @param int         $per_page
     * @param int         $page_number
     * @param string      $order_by
     * @param string      $order
     *
     * @return \TeamBooking_ReservationData[]
     */
    public static function getByCoworker($id, $filter = FALSE, $per_page = 0, $page_number = 0, $order_by = 'id', $order = 'asc')
    {
        if (NULL !== Cache::get('reservations' . $id . $filter . $per_page . $page_number . $order_by . $order)) {
            $return = Cache::get('reservations' . $id . $filter . $per_page . $page_number . $order_by . $order);
        } else {
            global $wpdb;
            $table_name = $wpdb->prefix . 'teambooking_reservations';
            $columns = rtrim(implode(', ', static::$relevant_columns), ', ');
            $query = $wpdb->prepare("SELECT $columns FROM $table_name WHERE coworker_id = %d", $id);
            if ($filter === 'pending') {
                $query = $wpdb->prepare("SELECT $columns FROM $table_name WHERE coworker_id = %d AND status = %s", $id, 'pending');
            }
            if ($filter === 'upcoming') {
                $now = current_time('timestamp', TRUE);
                $query = $wpdb->prepare("SELECT $columns FROM $table_name WHERE coworker_id = %d AND start >= %d", $id, $now);
            }
            if ($filter === 'unscheduled') {
                $query = $wpdb->prepare("SELECT $columns FROM $table_name WHERE coworker_id = %d AND start IS NULL", $id);
            }
            if ($filter === 'waiting_approval') {
                $query = $wpdb->prepare("SELECT $columns FROM $table_name WHERE coworker_id = %d AND status = %s", $id, 'waiting_approval');
            }
            $query .= ' ORDER BY ' . $order_by . ' ' . strtoupper($order);
            if ($per_page !== 0 && $page_number !== 0) {
                $query .= ' LIMIT ' . ($page_number - 1) * $per_page . ', ' . $per_page;
            }
            $results = $wpdb->get_results($query);
            $return = array();
            foreach ($results as $result) {
                $return[ $result->id ] = static::getInstance($result);
            }
            Cache::add($return, 'reservations' . $id . $filter . $per_page . $page_number . $order_by . $order);
        }

        return $return;
    }

    /**
     * @param string $order_id
     *
     * @return \TeamBooking_ReservationData[]
     */
    public static function getByOrderId($order_id)
    {
        if (NULL !== Cache::get('reservations' . $order_id)) {
            $return = Cache::get('reservations' . $order_id);
        } else {
            $return = array();
            global $wpdb;
            $table_name = $wpdb->prefix . 'teambooking_reservations';
            $columns = rtrim(implode(', ', static::$relevant_columns), ', ');
            $query = $wpdb->prepare("SELECT $columns FROM $table_name WHERE order_id = %s", $order_id);
            $results = $wpdb->get_results($query);
            foreach ($results as $result) {
                $return[ $result->id ] = static::getInstance($result);
            }
            Cache::add($return, 'reservations' . $order_id);
        }

        return $return;
    }

    /**
     * @param array $ids
     *
     * @return array
     */
    public static function getByOrderIds(array $ids)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $columns = rtrim(implode(', ', static::$relevant_columns), ', ');
        $how_many = count($ids);
        $placeholders = array_fill(0, $how_many, '%s');
        $format = implode(', ', $placeholders);
        $results = $wpdb->get_results($wpdb->prepare("SELECT $columns FROM $table_name WHERE order_id IN ($format)", $ids));
        $return = array();
        foreach ($results as $result) {
            $return[ $result->id ] = static::getInstance($result);
        }

        return $return;
    }

    /**
     * @param string $token
     *
     * @return bool|\TeamBooking_ReservationData
     */
    public static function getByToken($token)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $columns = rtrim(implode(', ', static::$relevant_columns), ', ');
        $result = $wpdb->get_row($wpdb->prepare("SELECT $columns FROM $table_name WHERE token = %s", $token));
        $return = FALSE;
        if (NULL !== $result) {
            $return = static::getInstance($result);
        }

        return $return;
    }

    /**
     * @param string $gateway_id
     * @param bool   $payment_details_only
     *
     * @return array[]|\TeamBooking_ReservationData[]
     */
    public static function getByPaymentGateway($gateway_id, $payment_details_only = FALSE)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        if ($payment_details_only) {
            $columns = 'id, payment_details';
        } else {
            $columns = rtrim(implode(', ', static::$relevant_columns), ', ');
        }
        $results = $wpdb->get_results($wpdb->prepare("SELECT $columns FROM $table_name WHERE payment_gateway = %s", $gateway_id));
        $return = array();
        foreach ($results as $result) {
            if ($payment_details_only) {
                $return[ $result->id ] = static::decode_object($result->payment_details);
            } else {
                $return[ $result->id ] = static::getInstance($result);
            }
        }

        return $return;
    }

    /**
     * @param $id
     *
     * @return \TeamBooking_ReservationData[]
     */
    public static function getByService($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $columns = rtrim(implode(', ', static::$relevant_columns), ', ');
        $results = $wpdb->get_results($wpdb->prepare("SELECT $columns FROM $table_name WHERE service_id = %s", $id));
        $return = array();
        foreach ($results as $result) {
            $return[ $result->id ] = static::getInstance($result);
        }

        return $return;
    }

    /**
     * @param array $services
     *
     * @return \TeamBooking_ReservationData[]
     */
    public static function getByServices(array $services)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $columns = rtrim(implode(', ', static::$relevant_columns), ', ');
        $how_many = count($services);
        $placeholders = array_fill(0, $how_many, '%d');
        $format = implode(', ', $placeholders);
        $results = $wpdb->get_results($wpdb->prepare("SELECT $columns FROM $table_name WHERE service_id IN ($format)", $services));
        $return = array();
        foreach ($results as $result) {
            $return[ $result->id ] = static::getInstance($result);
        }

        return $return;
    }

    /**
     * @param $service_id
     * @param $coworker_id
     *
     * @return \TeamBooking_ReservationData[]
     */
    public static function getByServiceAndCoworker($service_id, $coworker_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $columns = rtrim(implode(', ', static::$relevant_columns), ', ');
        $results = $wpdb->get_results($wpdb->prepare("SELECT $columns FROM $table_name WHERE service_id = %s AND coworker_id = %d", array($service_id, $coworker_id)));
        $return = array();
        foreach ($results as $result) {
            $return[ $result->id ] = static::getInstance($result);
        }

        return $return;
    }

    /**
     * @param string $string
     * @param int    $per_page
     * @param int    $page_number
     * @param string $order_by
     * @param string $order
     *
     * @return \TeamBooking_ReservationData[]
     */
    public static function getBySearch($string, $per_page = 0, $page_number = 0, $order_by = 'id', $order = 'asc')
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $columns = rtrim(implode(', ', static::$relevant_columns), ', ');
        $query = $wpdb->prepare(
            "
            SELECT $columns FROM $table_name WHERE service_id LIKE '%%%s%%'
            OR service_name LIKE '%%%s%%'
            OR service_class LIKE '%%%s%%'
            OR service_location LIKE '%%%s%%'
            OR order_id LIKE '%%%s%%'
            OR status LIKE '%%%s%%'
            OR customer_nicename LIKE '%%%s%%'
            ", array($string, $string, $string, $string, $string, $string, $string)
        );
        $query .= ' ORDER BY ' . $order_by . ' ' . strtoupper($order);
        if ($per_page !== 0 && $page_number !== 0) {
            $query .= ' LIMIT ' . ($page_number - 1) * $per_page . ', ' . $per_page;
        }
        $results = $wpdb->get_results($query);
        $return = array();
        foreach ($results as $result) {
            $return[ $result->id ] = static::getInstance($result);
        }

        return $return;
    }

    /**
     * @param int    $id
     * @param string $string
     * @param int    $per_page
     * @param int    $page_number
     * @param string $order_by
     * @param string $order
     *
     * @return \TeamBooking_ReservationData[]
     */
    public static function getByCoworkerSearch($id, $string, $per_page = 0, $page_number = 0, $order_by = 'id', $order = 'asc')
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $columns = rtrim(implode(', ', static::$relevant_columns), ', ');
        $query = $wpdb->prepare(
            "
            SELECT $columns FROM $table_name WHERE coworker_id = %d AND (service_id LIKE '%%%s%%'
            OR service_name LIKE '%%%s%%'
            OR service_class LIKE '%%%s%%'
            OR service_location LIKE '%%%s%%'
            OR order_id LIKE '%%%s%%'
            OR status LIKE '%%%s%%'
            OR customer_nicename LIKE '%%%s%%'
            )", array($id, $string, $string, $string, $string, $string, $string)
        );
        $query .= ' ORDER BY ' . $order_by . ' ' . strtoupper($order);
        if ($per_page !== 0 && $page_number !== 0) {
            $query .= ' LIMIT ' . ($page_number - 1) * $per_page . ', ' . $per_page;
        }
        $results = $wpdb->get_results($query);
        $return = array();
        foreach ($results as $result) {
            $return[ $result->id ] = static::getInstance($result);
        }

        return $return;
    }

    /**
     * @return array
     */
    public static function getIDs()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $results = $wpdb->get_results("SELECT id FROM $table_name");
        $return = array();
        foreach ($results as $result) {
            $return[] = $result->id;
        }

        return $return;
    }

    /**
     * @param $coworker_id
     *
     * @return array
     */
    public static function getIDsByCoworker($coworker_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $results = $wpdb->get_results($wpdb->prepare("SELECT id FROM $table_name WHERE coworker_id = %d", $coworker_id));
        $return = array();
        foreach ($results as $result) {
            $return[] = $result->id;
        }

        return $return;
    }

    /**
     * @param             $min_time
     * @param             $max_time
     * @param bool|string $filter
     *
     * @return \TeamBooking_ReservationData[]
     */
    public static function getByTime($min_time, $max_time, $filter = FALSE)
    {
        global $wpdb;
        if (is_string($min_time)) $min_time = strtotime($min_time);
        if (is_string($max_time)) $max_time = strtotime($max_time);
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $columns = rtrim(implode(', ', static::$relevant_columns), ', ');
        $query = "SELECT $columns FROM $table_name WHERE `end` >= %d";
        $params = array($min_time);
        if ($max_time != NULL) {
            $query .= ' AND start <= %d';
            $params[] = $max_time;
        }
        if ($filter === 'pending') {
            $query .= ' AND status = %s';
            $params[] = 'pending';
        }
        $results = $wpdb->get_results($wpdb->prepare($query, $params));
        $return = array();
        foreach ($results as $result) {
            $return[ $result->id ] = static::getInstance($result);
        }

        return $return;
    }

    /**
     * @return array
     */
    public static function sortByCustomers()
    {
        /** @var  $wp_users \WP_User[] */
        $wp_users = get_users();
        $sorted_reservations = array();
        $reservations = self::getAll();
        foreach ($reservations as $reservation) {
            if ($reservation->isCancelled()) continue;
            if ($reservation->isPending()) continue;
            if (!$reservation->getCustomerUserId()) {
                // Not registered, search email match
                $found = FALSE;
                foreach ($wp_users as $user) {
                    if ($user->user_email === $reservation->getCustomerEmail()) {
                        $sorted_reservations[ $user->ID ]['reservations'][] = $reservation;
                        $sorted_reservations[ $user->ID ]['user'] = $user;
                        $found = TRUE;
                    }
                }
                if (!$found) {
                    $user = new \WP_User();
                    $user->display_name = $reservation->getCustomerDisplayName();
                    $user->user_email = $reservation->getCustomerEmail();
                    $sorted_reservations[ $user->user_email ]['reservations'][] = $reservation;
                    $sorted_reservations[ $user->user_email ]['user'] = $user;
                }
            } else {
                foreach ($wp_users as $user) {
                    if ($user->ID === (int)$reservation->getCustomerUserId()) {
                        $sorted_reservations[ $user->ID ]['reservations'][] = $reservation;
                        $sorted_reservations[ $user->ID ]['user'] = $user;
                    }
                }
            }
        }

        return $sorted_reservations;
    }

    /**
     * @param $obj
     *
     * @return mixed
     */
    public static function decode_object($obj)
    {
        $obj_base = base64_decode($obj, TRUE);
        if (!$obj_base) {
            $obj = unserialize($obj);
        } else {
            $obj = unserialize(gzinflate($obj_base));
        }

        return $obj;
    }

    /**
     * @param $obj
     *
     * @return mixed
     */
    public static function encode_object($obj)
    {
        return base64_encode(gzdeflate(serialize($obj)));
    }

    /**
     * @param $obj
     *
     * @return \TeamBooking_ReservationData
     */
    private static function getInstance($obj)
    {
        $reservation = new \TeamBooking_ReservationData();
        $reservation->setDatabaseId($obj->id);
        $reservation->setCoworker($obj->coworker_id);
        $reservation->setCustomerUserId($obj->customer_id);
        $reservation->setCustomerTimezone($obj->customer_timezone);
        $reservation->setEnumerableForCustomerLimits($obj->enum_for_limit);
        $reservation->setGoogleCalendarEventParent(empty($obj->event_parent_id) ? NULL : $obj->event_parent_id);
        $reservation->setGoogleCalendarEvent(empty($obj->event_id) ? NULL : $obj->event_id);
        $reservation->setGoogleCalendarId($obj->calendar_id);
        $reservation->setHangoutLink($obj->hangout_url);
        $reservation->setEventHtmlLink($obj->event_url);
        $reservation->setServiceId($obj->service_id);
        $reservation->setServiceName($obj->service_name);
        $reservation->setServiceClass($obj->service_class);
        $reservation->setServiceLocation($obj->service_location);
        $reservation->setFormFields(static::decode_object($obj->form_fields));
        $reservation->setStart($obj->start);
        $reservation->setEnd($obj->end);
        $reservation->setSlotStart($obj->slot_start);
        $reservation->setSlotEnd($obj->slot_end);
        $reservation->setTickets($obj->tickets);
        $reservation->setPrice($obj->price);
        $reservation->setPriceDiscounted($obj->price_discounted);
        $reservation->setDiscount(static::decode_object($obj->discounts));
        $reservation->setCreationInstant($obj->created_utc);
        $reservation->setStatus($obj->status);
        $reservation->setCancellationReason($obj->canc_reason);
        $reservation->setCancellationWho($obj->canc_who);
        $reservation->setConfirmationWho($obj->confirm_who);
        $reservation->setPendingReason($obj->pending_reason);
        $reservation->setEmailReminderSent($obj->email_reminder_sent);
        $reservation->setPaymentGateway($obj->payment_gateway);
        $reservation->setCurrencyCode($obj->currency_code);
        $reservation->setPaid($obj->paid);
        $reservation->setPaymentDetails(static::decode_object($obj->payment_details));
        $reservation->setFileReference(static::decode_object($obj->files));
        $reservation->setPostId($obj->post_id);
        $reservation->setPostTitle($obj->post_title);
        $reservation->setToken($obj->token);
        $reservation->setOrderId(empty($obj->order_id) ? NULL : $obj->order_id);
        $reservation->setToBePaid(empty($obj->wants_payment) ? FALSE : $obj->wants_payment);
        $reservation->setFrontendLang($obj->frontend_lang);

        return $reservation;
    }

}