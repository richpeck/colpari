<?php

namespace TeamBooking;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Database\Services,
    TeamBooking\Google;

/**
 * Class Update
 *
 * @since  2.1.0
 * @author VonStroheim
 */
class Update
{
    /**
     * The plugin's Update method
     * Handles changes eventually needed in a not clean-install
     *
     * NOTES:
     *
     * - version < 1.2.5: it repairs the hooks for builtin fields
     *                    due to visibility properties change.
     *                    It resets the show/required settings.
     * - version < 1.3.0: creates the database tables for all
     *                    the reservations records, and transfer
     *                    the logs there.
     * - version < 1.4.0: updates the PayPal gateway by the new
     *                    interface, adds Stripe gateway too.
     * - version < 1.4.3: creates the database table for advanced
     *                    pricing features and sets the cleaning
     *                    cronjob.
     * - version < 1.4.5: sets the e-mail reminder cronjob.
     * - version < 2.0.0: creates the events table
     * - version < 2.1.0: some columns added
     * - version < 2.2.0: creates the custom post types,
     *                    some columns added/removed
     * - version < 2.3.3: status and creator columns added to
     *                    events table
     * - version < 2.5.0: creates the sessions table, creates
     *                    WPML strings if necessary
     */
    private static function doUpdate()
    {
        /* @var $settings \TeamBookingSettings */
        $settings = get_option('team_booking');
        $stored_version = $settings->getVersion();
        if ((!defined('DOING_AJAX') || !DOING_AJAX) && (!defined('DOING_CRON') || !DOING_CRON)) {
            if (version_compare($stored_version, '1.2.5', '<')) {
                self::from1_2_5($settings);
            }
            if (version_compare($stored_version, '1.3.0', '<')) {
                self::from1_3_0($settings);
            }
            if (version_compare($stored_version, '1.4.0', '<')) {
                self::from1_4_0($settings);
            }
            if (version_compare($stored_version, '1.4.3', '<')) {
                self::from1_4_3();
            }
            if (version_compare($stored_version, '1.4.5', '<')) {
                self::from1_4_5();
            }
            if (version_compare($stored_version, '2.0.0', '<')) {
                self::from2_0_0($settings);
            }
            if (version_compare($stored_version, '2.1.0', '<')) {
                self::from2_1_0($settings);
            }
            if (version_compare($stored_version, '2.1.4', '<')) {
                self::from2_1_4();
            }
            if (version_compare($stored_version, '2.2.0', '<')) {
                $settings = self::from2_2_0($settings);
            }
            if (version_compare($stored_version, '2.4.0', '<')) {
                $settings = self::from2_3_3($settings);
            }
            if (version_compare($stored_version, '2.5.0', '<')) {
                $settings = self::from2_5_0($settings);
            }
            $settings->setVersion(TEAMBOOKING_VERSION);
            $settings->save();

            $message = esc_html__('TeamBooking is now updated!', 'team-booking');
            $message_add = ' ' . esc_html__('Since there are major changes under the hood, all the Google Calendars must be re-synced!', 'team-booking');
            $button = '<a class="button button-primary" href="' . admin_url('admin.php?page=team-booking&whatsnew') . '">' . esc_html__("What's new?", 'team-booking') . '</a>';
            Admin\Framework\Notice::getUpdate($message, $button)->render();
        }
    }

    private static function from1_2_5(\TeamBookingSettings $settings)
    {
        $services = $settings->getServices();
        $old_hooks_and_labels = array(
            'first_name'  => __('First name', 'team-booking'),
            'second_name' => __('Last name', 'team-booking'),
            'email'       => __('Email', 'team-booking'),
            'address'     => __('Address', 'team-booking'),
            'phone'       => __('Phone number', 'team-booking'),
            'url'         => __('Website', 'team-booking'),
        );
        $old_hooks = array_keys($old_hooks_and_labels);
        foreach ($services as $service) {
            $fields = $service->getFormFields();
            $i = 0;
            foreach ($fields->getBuiltInFields() as $field) {
                $field->setHook($old_hooks[ $i ]);
                $field->setLabel($old_hooks_and_labels[ $old_hooks[ $i ] ]);
                $i++;
            }
            $service->setFormFields($fields);
            $settings->updateService($service, $service->getId());
        }
    }

    private static function from1_3_0(\TeamBookingSettings $settings)
    {
        global $wpdb;
        $table_name = \TeamBooking_Install::createReservationsTable();
        $logs = $settings->getLogs();
        foreach ($logs as $key => $log) {
            if ($log instanceof \TeamBooking_ReservationData) {
                /* @var $log \TeamBooking_ReservationData */
                $log->setCreationInstant($key);
                $created = current_time('Y-m-d H:i:s');
                $data_object = serialize($log);
                $wpdb->insert($table_name, array(
                    'created'     => $created,
                    'service_id'  => $log->getServiceId(),
                    'coworker_id' => $log->getCoworker(),
                    'data_object' => $data_object,
                ));
                $settings->dropLog($key);
            }
        }
    }

    private static function from1_4_0(\TeamBookingSettings $settings)
    {
        $payment_gateways = $settings->getPaymentGatewaySettingObjects();
        if (empty($payment_gateways)) {
            $settings->addPaymentGatewaySettingObject(new \TeamBooking_PaymentGateways_Stripe_Settings());
            $settings->addPaymentGatewaySettingObject(new \TeamBooking_PaymentGateways_PayPal_Settings());
        }
        $settings->clean();
    }

    private static function from1_4_3()
    {
        \TeamBooking_Install::createPromotionsTable();
        if (!wp_get_schedule('tb-db-cleaning-routine')) {
            wp_schedule_event(time(), 'daily', 'tb-db-cleaning-routine');
        }
    }

    private static function from1_4_5()
    {
        if (!wp_get_schedule('tb_email_reminder_handler')) {
            wp_schedule_event(time(), 'hourly', 'tb_email_reminder_handler');
        }
    }

    private static function from2_0_0(\TeamBookingSettings $settings)
    {
        global $wpdb;
        \TeamBooking_Install::createEventsTable();
        foreach ($settings->getCoworkersData() as $coworker_data) {
            $new_array = array();
            if (NULL === $coworker_data->getApiToken()) {
                $coworker_data->refreshApiToken();
            }
            foreach ($coworker_data->getCalendars() as $calendar) {
                if (is_array($calendar)) {
                    $new_array[ $calendar['calendar_id'] ] = array('calendar_id' => $calendar['calendar_id'], 'sync_token' => NULL);
                } else {
                    $new_array[ $calendar ] = array('calendar_id' => $calendar, 'sync_token' => NULL);
                }
            }
            if (!empty($new_array)) {
                $coworker_data->addCalendars($new_array);
                $settings->updateCoworkerData($coworker_data);
            }
        }
        // Add new columns
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $columns = $wpdb->get_col("DESC {$table_name}", 0);
        if (!in_array('calendar_id', $columns)) $wpdb->query("ALTER TABLE $table_name ADD calendar_id text");
        if (!in_array('event_id', $columns)) $wpdb->query("ALTER TABLE $table_name ADD event_id text");
        if (!in_array('status', $columns)) $wpdb->query("ALTER TABLE $table_name ADD status text");
        foreach (Database\Reservations::getAll() as $id => $data) {
            if (!$data->getToken()) {
                $data->setToken(Toolkit\generateToken());
            }
            Database\Reservations::update($data);
        }
        $table_name = $wpdb->prefix . 'teambooking_reservations_pending';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
            $results = $wpdb->get_results("SELECT * FROM $table_name");
            $return = array();
            foreach ($results as $result) {
                $return[ $result->id ] = unserialize($result->data_object);
            }
            foreach ($return as $data) {
                /** @var $data \TeamBooking_ReservationData */
                $data->setStatusPending();
                Database\Reservations::insert($data);
            }
            $wpdb->query("DROP TABLE IF EXISTS $table_name");
        }
    }

    private static function from2_1_0(\TeamBookingSettings $settings)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_events';
        $wpdb->query("TRUNCATE TABLE $table_name");
        $columns = $wpdb->get_col("DESC {$table_name}", 0);
        if (!in_array('organizer', $columns)) $wpdb->query("ALTER TABLE $table_name ADD organizer text");
        if (!in_array('guests', $columns)) $wpdb->query("ALTER TABLE $table_name ADD guests text");
        if (!in_array('allday', $columns)) $wpdb->query("ALTER TABLE $table_name ADD allday tinyint");
        if (in_array('encoded_event', $columns)) $wpdb->query("ALTER TABLE $table_name DROP COLUMN encoded_event");
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $columns = $wpdb->get_col("DESC {$table_name}", 0);
        if (!in_array('calendar_id', $columns)) $wpdb->query("ALTER TABLE $table_name ADD calendar_id text");
        if (!in_array('event_id', $columns)) $wpdb->query("ALTER TABLE $table_name ADD event_id text");
        if (!in_array('status', $columns)) $wpdb->query("ALTER TABLE $table_name ADD status text");
        if (!in_array('token', $columns)) $wpdb->query("ALTER TABLE $table_name ADD token text");
        foreach (Database\Reservations::getAll() as $id => $data) {
            if (!$data->getToken()) {
                $data->setToken(Toolkit\generateToken());
            }
            Database\Reservations::update($data);
        }
        foreach ($settings->getCoworkersData() as $c_data) {
            $c_data->cleanSyncTokens();
        }
    }

    private static function from2_1_4()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $columns = $wpdb->get_col("DESC {$table_name}", 0);
        if (!in_array('start', $columns)) $wpdb->query("ALTER TABLE $table_name ADD start int");
        if (!in_array('end', $columns)) $wpdb->query("ALTER TABLE $table_name ADD end int");
        foreach (Database\Reservations::getAll() as $id => $data) {
            if ($data->getSlotStart() !== NULL) {
                $data->setStart(strtotime($data->getSlotStart()));
                $data->setEnd(strtotime($data->getSlotEnd()));
            }
            Database\Reservations::update($data);
        }
    }

    /**
     * @param \TeamBookingSettings $settings
     *
     * @return \TeamBookingSettings
     */
    private static function from2_2_0(\TeamBookingSettings $settings)
    {
        $services = $settings->getServices();
        foreach ($services as $service) {
            try {
                Services::get($service->getId());
            } catch (\Exception $e) {
                $service->createPostType();
            }
        }
        $all_ok = TRUE;
        foreach ($services as $service) {
            try {
                Services::get($service->getId());
            } catch (\Exception $e) {
                $all_ok = FALSE;
            }
        }
        if ($all_ok) {
            $settings->removeServices();
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $columns = $wpdb->get_col("DESC {$table_name}", 0);
        $reservations = array();
        if (in_array('data_object', $columns)) {
            $query = "SELECT id, data_object FROM $table_name";
            $results = $wpdb->get_results($query);
            foreach ($results as $result) {
                $obj = Database\Reservations::decode_object($result->data_object);
                if (!$obj) continue;
                /** @var $obj \TeamBooking_ReservationData */
                $obj->setDatabaseId($result->id);
                $reservations[ $result->id ] = $obj;
            }
        }
        if (!in_array('customer_id', $columns)) $wpdb->query("ALTER TABLE $table_name ADD customer_id int");
        if (!in_array('customer_timezone', $columns)) $wpdb->query("ALTER TABLE $table_name ADD customer_timezone text");
        if (!in_array('enum_for_limit', $columns)) $wpdb->query("ALTER TABLE $table_name ADD enum_for_limit int");
        if (!in_array('event_parent_id', $columns)) $wpdb->query("ALTER TABLE $table_name ADD event_parent_id text");
        if (!in_array('hangout_url', $columns)) $wpdb->query("ALTER TABLE $table_name ADD hangout_url text");
        if (!in_array('event_url', $columns)) $wpdb->query("ALTER TABLE $table_name ADD event_url text");
        if (!in_array('service_name', $columns)) $wpdb->query("ALTER TABLE $table_name ADD service_name text");
        if (!in_array('service_class', $columns)) $wpdb->query("ALTER TABLE $table_name ADD service_class text");
        if (!in_array('service_location', $columns)) $wpdb->query("ALTER TABLE $table_name ADD service_location text");
        if (!in_array('tickets', $columns)) $wpdb->query("ALTER TABLE $table_name ADD tickets int");
        if (!in_array('price', $columns)) $wpdb->query("ALTER TABLE $table_name ADD price decimal(20,2)");
        if (!in_array('price_discounted', $columns)) $wpdb->query("ALTER TABLE $table_name ADD price_discounted decimal(20,2)");
        if (!in_array('pending_reason', $columns)) $wpdb->query("ALTER TABLE $table_name ADD pending_reason text");
        if (!in_array('canc_reason', $columns)) $wpdb->query("ALTER TABLE $table_name ADD canc_reason text");
        if (!in_array('canc_who', $columns)) $wpdb->query("ALTER TABLE $table_name ADD canc_who text");
        if (!in_array('confirm_who', $columns)) $wpdb->query("ALTER TABLE $table_name ADD confirm_who text");
        if (!in_array('email_reminder_sent', $columns)) $wpdb->query("ALTER TABLE $table_name ADD email_reminder_sent int");
        if (!in_array('paid', $columns)) $wpdb->query("ALTER TABLE $table_name ADD paid int");
        if (!in_array('payment_gateway', $columns)) $wpdb->query("ALTER TABLE $table_name ADD payment_gateway text");
        if (!in_array('currency_code', $columns)) $wpdb->query("ALTER TABLE $table_name ADD currency_code text");
        if (!in_array('post_id', $columns)) $wpdb->query("ALTER TABLE $table_name ADD post_id int");
        if (!in_array('post_title', $columns)) $wpdb->query("ALTER TABLE $table_name ADD post_title text");
        if (!in_array('slot_start', $columns)) $wpdb->query("ALTER TABLE $table_name ADD slot_start text");
        if (!in_array('slot_end', $columns)) $wpdb->query("ALTER TABLE $table_name ADD slot_end text");
        if (!in_array('form_fields', $columns)) $wpdb->query("ALTER TABLE $table_name ADD form_fields text");
        if (!in_array('discounts', $columns)) $wpdb->query("ALTER TABLE $table_name ADD discounts text");
        if (!in_array('payment_details', $columns)) $wpdb->query("ALTER TABLE $table_name ADD payment_details text");
        if (!in_array('files', $columns)) $wpdb->query("ALTER TABLE $table_name ADD files text");
        if (!in_array('customer_nicename', $columns)) $wpdb->query("ALTER TABLE $table_name ADD customer_nicename text");
        if (!in_array('created_utc', $columns)) $wpdb->query("ALTER TABLE $table_name ADD created_utc int");
        foreach ($reservations as $id => $data) {
            if (!$data->getToken()) {
                $data->setToken(Toolkit\generateToken());
            }
            if ($data->getSlotStart() !== NULL) {
                $data->setStart(strtotime($data->getSlotStart()));
                $data->setEnd(strtotime($data->getSlotEnd()));
            }
            Database\Reservations::update($data);
        }
        if (in_array('data_object', $columns)) $wpdb->query("ALTER TABLE $table_name DROP COLUMN data_object");

        return $settings;
    }

    /**
     * @param \TeamBookingSettings $settings
     *
     * @return \TeamBookingSettings
     */
    private static function from2_3_3(\TeamBookingSettings $settings)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_events';
        $wpdb->query("TRUNCATE TABLE $table_name");
        $columns = $wpdb->get_col("DESC {$table_name}", 0);
        if (!in_array('status', $columns)) $wpdb->query("ALTER TABLE $table_name ADD status text");
        if (!in_array('creator', $columns)) $wpdb->query("ALTER TABLE $table_name ADD creator text");
        foreach ($settings->getCoworkersData() as $c_data) {
            foreach ($c_data->getCalendars() as $calendar_id => $calendar_data) {
                Fetch\fromGoogle::fullSyncOf($calendar_id);
            }
        }
        $calendar = new \TeamBooking\Calendar();
        $coworker_id_list = Functions\getAuthCoworkersList();
        foreach ($coworker_id_list as $coworker_id => $coworker_data) {
            $email = $calendar->getTokenEmailAccount($coworker_data['tokens'], $coworker_id);
            if (!($email instanceof Google\Google_Auth_Exception)) {
                $settings->getCoworkerData($coworker_id)->setAuthAccount($email);
            }
        }

        return $settings;
    }

    /**
     * @param \TeamBookingSettings $settings
     *
     * @return \TeamBookingSettings
     */
    private static function from2_5_0(\TeamBookingSettings $settings)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_sessions';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            \TeamBooking_Install::createSessionTable();
        }
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        $columns = $wpdb->get_col("DESC {$table_name}", 0);
        if (!in_array('order_id', $columns)) $wpdb->query("ALTER TABLE $table_name ADD order_id text");
        if (!in_array('wants_payment', $columns)) $wpdb->query("ALTER TABLE $table_name ADD wants_payment int");
        if (!in_array('frontend_lang', $columns)) $wpdb->query("ALTER TABLE $table_name ADD frontend_lang text");
        $table_name = $wpdb->prefix . 'teambooking_events';
        $columns = $wpdb->get_col("DESC {$table_name}", 0);
        if (!in_array('start_timezone', $columns)) $wpdb->query("ALTER TABLE $table_name ADD start_timezone text");
        if (!in_array('end_timezone', $columns)) $wpdb->query("ALTER TABLE $table_name ADD end_timezone text");

        // WPML
        WPML\update();

        $settings->clean();

        return $settings;
    }

    public static function update()
    {
        global $wpdb;
        if (function_exists('is_multisite') && is_multisite()) {
            if (!function_exists('is_plugin_active_for_network')) {
                require_once ABSPATH . '/wp-admin/includes/plugin.php';
            }
            if (is_plugin_active_for_network(plugin_basename(TEAMBOOKING_FILE_PATH))) {
                foreach ($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $blog_id) {
                    switch_to_blog($blog_id);
                    self::doUpdate();
                    restore_current_blog();
                }
            } else {
                self::doUpdate();
            }
        } else {
            self::doUpdate();
        }
    }

    public static function repairDatabase()
    {
        global $wpdb;
        if (!function_exists('get_userdata')) {
            require_once ABSPATH . WPINC . '/pluggable.php';
        }
        if (function_exists('is_multisite') && is_multisite()) {
            if (!function_exists('is_plugin_active_for_network')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            if (is_plugin_active_for_network(plugin_basename(TEAMBOOKING_FILE_PATH))) {
                foreach ($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $blog_id) {
                    switch_to_blog($blog_id);
                    self::doRepair();
                    restore_current_blog();
                }
            } else {
                self::doRepair();
            }
        } else {
            self::doRepair();
        }
    }

    private static function doRepair()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_promotions';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            \TeamBooking_Install::createPromotionsTable();
        }
        $table_name = $wpdb->prefix . 'teambooking_events';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            \TeamBooking_Install::createEventsTable();
        }
        $wpdb->query("TRUNCATE TABLE $table_name");
        $columns = $wpdb->get_col("DESC {$table_name}", 0);
        if (!in_array('description', $columns)) $wpdb->query("ALTER TABLE $table_name ADD description text");
        if (!in_array('hangout_link', $columns)) $wpdb->query("ALTER TABLE $table_name ADD hangout_link text");
        if (!in_array('html_link', $columns)) $wpdb->query("ALTER TABLE $table_name ADD html_link text");
        if (!in_array('location', $columns)) $wpdb->query("ALTER TABLE $table_name ADD location text");
        if (!in_array('recurrence', $columns)) $wpdb->query("ALTER TABLE $table_name ADD recurrence text");
        if (!in_array('recurring_event_id', $columns)) $wpdb->query("ALTER TABLE $table_name ADD recurring_event_id text");
        if (!in_array('summary', $columns)) $wpdb->query("ALTER TABLE $table_name ADD summary text");
        if (!in_array('color_id', $columns)) $wpdb->query("ALTER TABLE $table_name ADD color_id int");
        if (!in_array('event_start', $columns)) $wpdb->query("ALTER TABLE $table_name ADD event_start int");
        if (!in_array('event_end', $columns)) $wpdb->query("ALTER TABLE $table_name ADD event_end int");
        if (!in_array('organizer', $columns)) $wpdb->query("ALTER TABLE $table_name ADD organizer text");
        if (!in_array('guests', $columns)) $wpdb->query("ALTER TABLE $table_name ADD guests text");
        if (!in_array('allday', $columns)) $wpdb->query("ALTER TABLE $table_name ADD allday tinyint");
        if (!in_array('status', $columns)) $wpdb->query("ALTER TABLE $table_name ADD status text");
        if (!in_array('creator', $columns)) $wpdb->query("ALTER TABLE $table_name ADD creator text");
        if (in_array('encoded_event', $columns)) $wpdb->query("ALTER TABLE $table_name DROP COLUMN encoded_event");
        if (!in_array('start_timezone', $columns)) $wpdb->query("ALTER TABLE $table_name ADD start_timezone text");
        if (!in_array('end_timezone', $columns)) $wpdb->query("ALTER TABLE $table_name ADD end_timezone text");
        foreach (\TeamBooking\Functions\getSettings()->getCoworkersData() as $c_data) {
            $c_data->cleanSyncTokens();
        }

        $calendar = new \TeamBooking\Calendar();
        $coworker_id_list = Functions\getAuthCoworkersList();
        foreach ($coworker_id_list as $coworker_id => $coworker_data) {
            if (NULL === $coworker_data['auth_account']) {
                $email = $calendar->getTokenEmailAccount($coworker_data['tokens'], $coworker_id);
                if (!($email instanceof Google\Google_Auth_Exception)) {
                    \TeamBooking\Functions\getSettings()->getCoworkerData($coworker_id)->setAuthAccount($email);
                }
            }
        }

        \TeamBooking\Functions\getSettings()->save();
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            \TeamBooking_Install::createReservationsTable();
        }
        $columns = $wpdb->get_col("DESC {$table_name}", 0);
        if (!in_array('calendar_id', $columns)) $wpdb->query("ALTER TABLE $table_name ADD calendar_id text");
        if (!in_array('event_id', $columns)) $wpdb->query("ALTER TABLE $table_name ADD event_id text");
        if (!in_array('status', $columns)) $wpdb->query("ALTER TABLE $table_name ADD status text");
        if (!in_array('token', $columns)) $wpdb->query("ALTER TABLE $table_name ADD token text");
        if (!in_array('start', $columns)) $wpdb->query("ALTER TABLE $table_name ADD start int");
        if (!in_array('end', $columns)) $wpdb->query("ALTER TABLE $table_name ADD end int");
        if (!in_array('customer_id', $columns)) $wpdb->query("ALTER TABLE $table_name ADD customer_id int");
        if (!in_array('customer_timezone', $columns)) $wpdb->query("ALTER TABLE $table_name ADD customer_timezone text");
        if (!in_array('enum_for_limit', $columns)) $wpdb->query("ALTER TABLE $table_name ADD enum_for_limit int");
        if (!in_array('event_parent_id', $columns)) $wpdb->query("ALTER TABLE $table_name ADD event_parent_id text");
        if (!in_array('hangout_url', $columns)) $wpdb->query("ALTER TABLE $table_name ADD hangout_url text");
        if (!in_array('event_url', $columns)) $wpdb->query("ALTER TABLE $table_name ADD event_url text");
        if (!in_array('service_name', $columns)) $wpdb->query("ALTER TABLE $table_name ADD service_name text");
        if (!in_array('service_class', $columns)) $wpdb->query("ALTER TABLE $table_name ADD service_class text");
        if (!in_array('service_location', $columns)) $wpdb->query("ALTER TABLE $table_name ADD service_location text");
        if (!in_array('tickets', $columns)) $wpdb->query("ALTER TABLE $table_name ADD tickets int");
        if (!in_array('price', $columns)) $wpdb->query("ALTER TABLE $table_name ADD price decimal(20,2)");
        if (!in_array('price_discounted', $columns)) $wpdb->query("ALTER TABLE $table_name ADD price_discounted decimal(20,2)");
        if (!in_array('pending_reason', $columns)) $wpdb->query("ALTER TABLE $table_name ADD pending_reason text");
        if (!in_array('canc_reason', $columns)) $wpdb->query("ALTER TABLE $table_name ADD canc_reason text");
        if (!in_array('canc_who', $columns)) $wpdb->query("ALTER TABLE $table_name ADD canc_who text");
        if (!in_array('confirm_who', $columns)) $wpdb->query("ALTER TABLE $table_name ADD confirm_who text");
        if (!in_array('email_reminder_sent', $columns)) $wpdb->query("ALTER TABLE $table_name ADD email_reminder_sent int");
        if (!in_array('paid', $columns)) $wpdb->query("ALTER TABLE $table_name ADD paid int");
        if (!in_array('payment_gateway', $columns)) $wpdb->query("ALTER TABLE $table_name ADD payment_gateway text");
        if (!in_array('currency_code', $columns)) $wpdb->query("ALTER TABLE $table_name ADD currency_code text");
        if (!in_array('post_id', $columns)) $wpdb->query("ALTER TABLE $table_name ADD post_id int");
        if (!in_array('post_title', $columns)) $wpdb->query("ALTER TABLE $table_name ADD post_title text");
        if (!in_array('slot_start', $columns)) $wpdb->query("ALTER TABLE $table_name ADD slot_start text");
        if (!in_array('slot_end', $columns)) $wpdb->query("ALTER TABLE $table_name ADD slot_end text");
        if (!in_array('form_fields', $columns)) $wpdb->query("ALTER TABLE $table_name ADD form_fields text");
        if (!in_array('discounts', $columns)) $wpdb->query("ALTER TABLE $table_name ADD discounts text");
        if (!in_array('payment_details', $columns)) $wpdb->query("ALTER TABLE $table_name ADD payment_details text");
        if (!in_array('files', $columns)) $wpdb->query("ALTER TABLE $table_name ADD files text");
        if (!in_array('customer_nicename', $columns)) $wpdb->query("ALTER TABLE $table_name ADD customer_nicename text");
        if (!in_array('created_utc', $columns)) $wpdb->query("ALTER TABLE $table_name ADD created_utc int");
        if (!in_array('order_id', $columns)) $wpdb->query("ALTER TABLE $table_name ADD order_id text");
        if (!in_array('wants_payment', $columns)) $wpdb->query("ALTER TABLE $table_name ADD wants_payment int");
        if (!in_array('frontend_lang', $columns)) $wpdb->query("ALTER TABLE $table_name ADD frontend_lang text");

        if (!in_array('data_object', $columns)) {
            $reservations = Database\Reservations::getAll();
        } else {
            $query = "SELECT id, data_object FROM $table_name";
            $results = $wpdb->get_results($query);
            $reservations = array();
            foreach ($results as $result) {
                $obj = Database\Reservations::decode_object($result->data_object);
                if (!$obj) continue;
                /** @var $obj \TeamBooking_ReservationData */
                $obj->setDatabaseId($result->id);
                $reservations[ $result->id ] = $obj;
            }
        }

        foreach (\TeamBooking\Functions\getSettings()->getCoworkersData() as $c_data) {
            foreach ($c_data->getCalendars() as $calendar_id => $calendar_data) {
                Fetch\fromGoogle::fullSyncOf($calendar_id);
            }
        }

        foreach ($reservations as $id => $data) {
            if (!$data->getToken()) {
                $data->setToken(Toolkit\generateToken());
            }
            if ($data->getSlotStart() !== NULL) {
                $data->setStart(strtotime($data->getSlotStart()));
                $data->setEnd(strtotime($data->getSlotEnd()));
            }
            Database\Reservations::update($data);
        }

        if (in_array('data_object', $columns)) $wpdb->query("ALTER TABLE $table_name DROP COLUMN data_object");

        $table_name = $wpdb->prefix . 'teambooking_sessions';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            \TeamBooking_Install::createSessionTable();
        }

        if (!wp_get_schedule('tb-db-cleaning-routine')) {
            wp_schedule_event(time(), 'daily', 'tb-db-cleaning-routine');
        }
        if (!wp_get_schedule('tb_email_reminder_handler')) {
            wp_schedule_event(time(), 'hourly', 'tb_email_reminder_handler');
        }

    }
}