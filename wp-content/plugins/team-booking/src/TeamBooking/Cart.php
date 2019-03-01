<?php

namespace TeamBooking;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Database\Services;
use TeamBooking\Functions;

/**
 * Class Cart
 *
 * Based on WooCommerce session handler
 *
 * @since    2.5.0
 * @author   VonStroheim
 */
class Cart
{
    private $cookie_name;
    private $session_table;
    private $customer_id;
    private $cookie_expiration;
    private $dirty = FALSE;
    private $has_cookie = FALSE;
    /** @var array */
    private $data;
    /** @var array */
    private $transient = array();

    public function __construct()
    {
        global $wpdb;
        $this->cookie_name = 'wp_teambooking_session_' . COOKIEHASH;
        $this->session_table = $wpdb->prefix . 'teambooking_sessions';
        if ($wpdb->get_var("SHOW TABLES LIKE '$this->session_table'") !== $this->session_table) {
            \TeamBooking_Install::createSessionTable();
        }
        if ($cookie = $this->getCookie()) {
            /** @var array $cookie */
            $this->customer_id = $cookie[0];
            $this->cookie_expiration = $cookie[1];
            $this->has_cookie = TRUE;
            if (time() > ($this->cookie_expiration - HOUR_IN_SECONDS)) {
                $this->cookie_expiration = time() + HOUR_IN_SECONDS * 48;
                $this->updateSessionTimestamp($this->customer_id, $this->cookie_expiration);
            }
            if ((int)$this->customer_id !== get_current_user_id()) {
                $this->destroySession();
            }
        } else {
            $this->cookie_expiration = time() + HOUR_IN_SECONDS * 48;
            $this->customer_id = $this->generateCustomerID();
            $this->setCookie();
        }
        $this->data = array_merge(static::getDefaultData(), array_intersect_key($this->getSessionData($this->customer_id), static::getDefaultData()));

        //cleanup
        if (NULL !== $this->data['cart_time']
            && Functions\getSettings()->getSlotsInCartExpirationTime() > 0
            && time() - $this->data['cart_time'] > Functions\getSettings()->getSlotsInCartExpirationTime()
        ) {
            $this->data['slots'] = array();
            $this->data['cart_time'] = NULL;
            $this->dirty = TRUE;
        }
        $now = Toolkit\getNowInSecondsUTC();
        foreach ($this->data['slots'] as $slotid => $slot) {
            if ($slot['bookable_until'] !== 0 && $now > $slot['bookable_until']) {
                unset($this->data['slots'][ $slotid ]);
                $this->dirty = TRUE;
            }
        }

        add_action('shutdown', array($this, 'saveData'), 20);
        add_action('wp_logout', array($this, 'destroySession'));
    }

    /**
     * @param      $key
     * @param      $value
     * @param bool $transient
     */
    public function set($key, $value, $transient = FALSE)
    {
        if ($value !== $this->get($key, $transient)) {
            if ($transient) {
                $this->transient[ sanitize_key($key) ] = $value;
            } else {
                $this->data[ sanitize_key($key) ] = $value;
                $this->dirty = TRUE;
            }
        }
    }

    /**
     * @param $key
     */
    public function drop($key)
    {
        if (isset($this->data[ $key ])) {
            unset($this->data[ $key ]);
            $this->dirty = TRUE;
        }
    }

    /**
     * @param      $key
     * @param bool $transient
     *
     * @return mixed|null
     */
    public function get($key, $transient = FALSE)
    {
        $key = sanitize_key($key);
        if ($transient) {
            return isset($this->transient[ $key ]) ? $this->transient[ $key ] : NULL;
        }

        return isset($this->data[ $key ]) ? $this->data[ $key ] : NULL;
    }

    /**
     * @return string
     */
    public function generateCustomerID()
    {
        if (is_user_logged_in()) {
            return get_current_user_id();
        }

        return \TeamBooking\Toolkit\generateToken('alnum', 12);
    }

    /**
     * @return array|bool
     */
    public function getCookie()
    {
        if (empty($_COOKIE[ $this->cookie_name ]) || !is_string($_COOKIE[ $this->cookie_name ])) {
            return FALSE;
        }
        $cookie = explode('||', $_COOKIE[ $this->cookie_name ]);
        list($customer_id, $cookie_expiration, $cookie_hash) = $cookie;
        $to_hash = $customer_id . '|' . $cookie_expiration;
        $hash = hash_hmac('md5', $to_hash, wp_hash($to_hash));
        if (empty($cookie_hash) || !Functions\tb_hash_equals($hash, $cookie_hash)) {
            return FALSE;
        }

        return array($customer_id, $cookie_expiration, $cookie_hash);
    }

    public function setCookie()
    {
        $to_hash = $this->customer_id . '|' . $this->cookie_expiration;
        $cookie_hash = hash_hmac('md5', $to_hash, wp_hash($to_hash));
        $cookie_value = $this->customer_id . '||' . $this->cookie_expiration . '||' . $cookie_hash;
        $this->has_cookie = TRUE;
        static::set_cookie($this->cookie_name, $cookie_value, $this->cookie_expiration);
    }

    /**
     * @param string $customer_id
     * @param int    $timestamp
     */
    public function updateSessionTimestamp($customer_id, $timestamp)
    {
        global $wpdb;
        $wpdb->update(
            $this->session_table,
            array('session_expiry' => $timestamp),
            array('session_key' => $customer_id),
            array('%d')
        );
    }

    /**
     * @param string $customer_id
     *
     * @return array|bool
     */
    public function getSessionData($customer_id)
    {
        if (!$this->has_cookie) return static::getDefaultData();
        global $wpdb;
        if (defined('WP_SETUP_CONFIG')) {
            return FALSE;
        }
        if (NULL !== Cache::get('sessions' . $customer_id)) {
            $value = Cache::get('sessions' . $customer_id);
        } else {
            $value = $wpdb->get_var($wpdb->prepare("SELECT session_value FROM $this->session_table WHERE session_key = %s", $customer_id));
            if (NULL === $value) {
                $value = static::getDefaultData();
                $this->dirty = TRUE;
            }
            Cache::add($value, 'sessions' . $customer_id);
        }

        return maybe_unserialize($value);
    }

    /**
     * @return array|bool|mixed|null
     */
    public static function getAllSessionsData()
    {
        if (defined('WP_SETUP_CONFIG')) {
            return FALSE;
        }
        if (NULL !== Cache::get('sessions_all')) {
            return Cache::get('sessions_all');
        }
        global $wpdb;
        $session_table = $wpdb->prefix . 'teambooking_sessions';
        $results = $wpdb->get_results($wpdb->prepare("SELECT session_key, session_value FROM $session_table WHERE session_expiry > %d", time()));
        $return = array();
        foreach ((array)$results as $item) {
            $return[ $item->session_key ] = maybe_unserialize($item->session_value);
        }
        Cache::add($return, 'sessions_all');

        return $return;
    }

    /**
     * @param bool $exclude_current
     *
     * @return Slot[]|bool|mixed|null
     */
    public static function getAllSessionsSlots($exclude_current = FALSE)
    {
        if (defined('WP_SETUP_CONFIG')) {
            return FALSE;
        }
        if (NULL !== Cache::get('sessions_all_slots' . $exclude_current)) {
            return Cache::get('sessions_all_slots' . $exclude_current);
        }
        $sessions = self::getAllSessionsData();
        $cart = $exclude_current ? self::loadCart() : NULL;
        $return = array();
        foreach ($sessions as $key => $session) {
            if (!isset($session['slots'])) continue;
            if (NULL !== $cart && (string)$cart->customer_id === (string)$key) continue;
            foreach ($session['slots'] as $id => $slot) {
                $return[ $id . 'SESSION' . $key ] = \TeamBooking\Toolkit\objDecode($slot['object'], TRUE);
            }
        }
        Cache::add($return, 'sessions_all_slots' . $exclude_current);

        return $return;
    }

    /**
     * @param      $slot_id
     * @param bool $exclude_current
     *
     * @return Slot[]
     */
    public static function getAllSessionsSlot($slot_id, $exclude_current = FALSE)
    {
        $sessions = self::getAllSessionsData();
        $cart = $exclude_current ? self::loadCart() : NULL;
        $return = array();
        foreach ($sessions as $key => $session) {
            if (!isset($session['slots'])) continue;
            if (NULL !== $cart && $cart->customer_id === $key) continue;
            if (isset($session['slots'][ $slot_id ])) {
                $return[ $slot_id . 'SESSION' . $key ] = \TeamBooking\Toolkit\objDecode($session['slots'][ $slot_id ]['object'], TRUE);
            }
        }

        return $return;
    }

    /**
     * @param bool $exclude_current
     *
     * @return array|bool|mixed|null
     */
    public static function getAllSessionsCoupons($exclude_current = FALSE)
    {
        if (defined('WP_SETUP_CONFIG')) {
            return FALSE;
        }
        if (NULL !== Cache::get('sessions_all_coupons' . $exclude_current)) {
            return Cache::get('sessions_all_coupons' . $exclude_current);
        }
        $sessions = self::getAllSessionsData();
        $return = array();
        foreach ($sessions as $key => $session) {
            if (!isset($session['preferences'])) continue;
            if ($exclude_current) {
                if ((string)self::loadCart()->customer_id === (string)$key) continue;
            }
            if (isset($session['preferences']['coupon_code'])) {
                $return[] = $session['preferences']['coupon_code'];
            }
        }
        Cache::add($return, 'sessions_all_coupons' . $exclude_current);

        return $return;
    }

    public function saveData()
    {
        if ($this->dirty && $this->has_cookie) {
            global $wpdb;
            $wpdb->replace(
                $this->session_table,
                array(
                    'session_key'    => $this->customer_id,
                    'session_value'  => maybe_serialize($this->data),
                    'session_expiry' => $this->cookie_expiration,
                ),
                array(
                    '%s',
                    '%s',
                    '%d',
                )
            );
            Cache::add($this->data, 'sessions' . $this->customer_id);
            $this->dirty = FALSE;
        }
    }

    public function destroySession()
    {
        // Clear cookie
        static::set_cookie($this->cookie_name, '', time() - YEAR_IN_SECONDS);
        global $wpdb;
        $wpdb->delete(
            $this->session_table,
            array('session_key' => $this->customer_id,)
        );
        // Clear data
        $this->data = static::getDefaultData();
        $this->dirty = FALSE;
        $this->customer_id = $this->generateCustomerID();
    }

    /**
     * Set a cookie
     *
     * @param  string  $name   Name of the cookie
     * @param  string  $value  Value of the cookie
     * @param  integer $expire Expiry of the cookie
     */
    public static function set_cookie($name, $value, $expire = 0)
    {
        if (!headers_sent()) {
            setcookie($name, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN);
        } elseif (defined('WP_DEBUG') && WP_DEBUG) {
            headers_sent($file, $line);
            trigger_error("{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE);
        }
    }

    public static function clean_sessions()
    {
        global $wpdb;
        if (!defined('WP_SETUP_CONFIG') && !defined('WP_INSTALLING')) {
            $table = $wpdb->prefix . 'teambooking_sessions';
            $wpdb->query($wpdb->prepare("DELETE FROM $table WHERE session_expiry < %d", time()));
        }
    }

    /**
     * @return mixed|Cart
     */
    public static function loadCart()
    {
        if (NULL !== Cache::get('cart')) {
            $cart = Cache::get('cart');
        } else {
            $cart = new Cart();
            Cache::add($cart, 'cart');
        }

        return $cart;
    }

    /**
     * @return array
     */
    public static function getDefaultData()
    {
        return array(
            'slots'       => array(),
            'forms'       => array(),
            'preferences' => array(),
            'cart_time'   => NULL
        );
    }

    /**
     * @param Slot  $slot
     * @param array $tickets
     *
     * @return Cart
     */
    public static function addSlot(Slot $slot, array $tickets = array('normal' => 1))
    {
        $cart = self::loadCart();
        $slots = $cart->get('slots');
        if (empty($slots)) {
            $cart->set('cart_time', time());
        }
        $slots[ $slot->getUniqueId() ] = array(
            'service_id'     => $slot->getServiceId(),
            'tickets'        => $tickets,
            'start'          => $slot->getStartTime(),
            'end'            => $slot->getEndTime(),
            'bookable_until' => $slot->getBookableUntil(),
            'allday'         => $slot->isAllDay(),
            'object'         => \TeamBooking\Toolkit\objEncode($slot, TRUE)
        );
        $cart->set('slots', $slots);

        return $cart;
    }

    /**
     * @return Slot[]
     */
    public static function getSlots()
    {
        if (NULL === self::getPreference('timezone')) {
            self::updateTimezone(\TeamBooking\Toolkit\getTimezone()->getName());
        }

        $cart = self::loadCart();
        $slots = (array)$cart->get('slots');
        $return = array();
        foreach ($slots as $id => $slot) {
            $return[ $id ] = \TeamBooking\Toolkit\objDecode($slot['object'], TRUE);
        }

        return $return;
    }

    /**
     * @param $unique_id
     *
     * @return Slot|bool
     */
    public static function getSlot($unique_id)
    {
        $cart = self::loadCart();
        $slots = (array)$cart->get('slots');
        if (isset($slots[ $unique_id ])) {
            return \TeamBooking\Toolkit\objDecode($slots[ $unique_id ]['object'], TRUE);
        }

        return FALSE;
    }

    /**
     * @param string $slot_id
     *
     * @return mixed|Cart
     */
    public static function removeSlot($slot_id)
    {
        $cart = self::loadCart();
        $slots = $cart->get('slots');
        unset($slots[ $slot_id ]);
        $cart->set('slots', $slots);
        if (empty($slots)) {
            $cart->set('cart_time', NULL);
        }

        return $cart;
    }

    /**
     * @param Slot $slot
     * @param bool $all_sessions
     * @param bool $exclude_current
     *
     * @return bool
     */
    public static function isSlotIn(Slot $slot, $all_sessions = FALSE, $exclude_current = FALSE)
    {
        $cart = self::loadCart();
        $slots = $all_sessions ? self::getAllSessionsSlots($exclude_current) : $cart->get('slots');

        if ($all_sessions) {
            foreach ($slots as $cart_slot) {
                if ($cart_slot->getUniqueId() === $slot->getUniqueId()) return TRUE;
            }

            return FALSE;
        }

        return isset($slots[ $slot->getUniqueId() ]);
    }

    /**
     * @param       $raw_post_data
     * @param       $service_id
     * @param       $timezone
     * @param array $specific_slot
     * @param array $files
     *
     * @return Cart
     */
    public static function addForm($raw_post_data, $service_id, $timezone, array $specific_slot = array(), array $files = array())
    {
        $cart = self::loadCart();
        $forms = (array)$cart->get('forms');
        $forms[ $service_id ][] = array(
            'raw_data'        => $raw_post_data,
            'timezone'        => $timezone,
            'file_references' => $files,
            'specific_slot'   => $specific_slot
        );
        $cart->set('forms', $forms);

        return $cart;
    }

    /**
     * @return array
     */
    public static function getForms()
    {
        $cart = self::loadCart();

        return (array)$cart->get('forms');
    }

    /**
     * @return array
     */
    public static function getFormFieldsValues()
    {
        $cart = self::loadCart();
        $forms = (array)$cart->get('forms');
        $return = array();
        foreach ($forms as $service_id => $form_group) {
            foreach ($form_group as $form) {
                parse_str($form['raw_data'], $fields);
                foreach ($fields['form_fields'] as $hook => $value) {
                    $return[ $hook ] = $value;
                }
                foreach ($form['file_references'] as $hook => $file_reference) {
                    $return[ $hook ] = array(
                        'path' => $file_reference['file'],
                        'ext'  => pathinfo($file_reference['file'], PATHINFO_EXTENSION)
                    );
                }
            }
        }

        return $return;
    }

    /**
     * @param $hook
     *
     * @return array
     */
    public static function extractFileReference($hook)
    {
        $cart = self::loadCart();
        $forms = (array)$cart->get('forms');
        $return = array();
        foreach ($forms as $service_id => $form_group) {
            foreach ($form_group as $form) {
                if (isset($form['file_references'][ $hook ])) {
                    $return = $form['file_references'][ $hook ];
                }
            }
        }

        return $return;
    }

    /**
     * @param string $service_id
     *
     * @return array
     */
    public static function getFormsByService($service_id)
    {
        $cart = self::loadCart();
        $forms = (array)$cart->get('forms');

        return isset($forms[ $service_id ]) ? $forms[ $service_id ] : array();
    }

    /**
     * @return Cart
     */
    public static function cleanForms()
    {
        $cart = self::loadCart();
        $cart->set('forms', array());

        return $cart;
    }

    /**
     * @return Cart
     */
    public static function cleanSlots()
    {
        $cart = self::loadCart();
        $cart->set('slots', array());

        return $cart;
    }

    /**
     * @param Slot $slot
     * @param int  $tickets
     *
     * @return Cart
     */
    public static function setTickets(Slot $slot, $tickets = 1)
    {
        $cart = self::loadCart();
        $slots = $cart->get('slots');
        if (isset($slots[ $slot->getUniqueId() ])) {
            $slot_p = $slots[ $slot->getUniqueId() ];
            $slot_p['tickets']['normal'] = $tickets;
            $slots[ $slot->getUniqueId() ] = $slot_p;
        }
        $cart->set('slots', $slots);

        return $cart;
    }

    /**
     * @return array
     */
    public static function getTickets()
    {
        $cart = self::loadCart();
        $slots = (array)$cart->get('slots');
        $return = array();
        foreach ($slots as $slot_id => $slot) {
            $return[ $slot_id ] = $slot['tickets'];
        }

        return $return;
    }

    /**
     * @param $tz_identifier
     */
    public static function updateTimezone($tz_identifier)
    {
        $cart = self::loadCart();
        $slots = (array)$cart->get('slots');
        foreach ($slots as $id => $slot) {
            /** @var $slot_obj Slot */
            $slot_obj = \TeamBooking\Toolkit\objDecode($slot['object'], TRUE);
            if ($slot_obj->isAllDay()) {
                continue;
            }
            $slot_obj->setTimezone(Functions\parse_timezone_aliases($tz_identifier));
            $slot['object'] = \TeamBooking\Toolkit\objEncode($slot_obj, TRUE);
            $slots[ $id ] = $slot;
        }
        $cart->set('slots', $slots);
    }

    /**
     * @param $code
     */
    public static function updateCouponCode($code)
    {
        //this is complex
    }

    /**
     * @param      $preference
     * @param      $value
     * @param bool $transient
     */
    public static function setPreference($preference, $value, $transient = FALSE)
    {
        $cart = self::loadCart();
        $preferences = (array)$cart->get('preferences');
        if ($preference !== 'keep_preferences' && !is_user_logged_in()) {
            $keep = isset($preferences['keep_preferences'])
                ? $preferences['keep_preferences']
                : Functions\getSettings()->getCookiePolicy() === 1;
            if (!$keep || !Functions\getSettings()->getCookiePolicy()) {
                unset($preferences['timezone'], $preferences['coupon_code']);
                $cart->set('preferences', $preferences);

                return;
            }
        }

        $preferences[ $preference ] = $value;
        if ($preference === 'timezone') {
            self::updateTimezone($value);
        }
        if ($preference === 'coupon_code') {
            self::updateCouponCode($value);
        }
        $cart->set('preferences', $preferences, $transient);
    }

    /**
     * @param $preference
     */
    public static function cleanPreference($preference)
    {
        $cart = self::loadCart();
        $preferences = (array)$cart->get('preferences');
        unset($preferences[ $preference ]);
        $cart->set('preferences', $preferences);
    }

    /**
     * @param      $preference
     * @param bool $transient
     *
     * @return mixed|null
     */
    public static function getPreference($preference, $transient = FALSE)
    {
        $cart = self::loadCart();
        $preferences = (array)$cart->get('preferences', $transient);

        return isset($preferences[ $preference ]) ? $preferences[ $preference ] : NULL;
    }

    public static function getCartTime($elapsed = FALSE)
    {
        $cart = self::loadCart();
        $return = $cart->get('cart_time');

        return (!$elapsed || $return === NULL) ? $return : (time() - $return);
    }

}