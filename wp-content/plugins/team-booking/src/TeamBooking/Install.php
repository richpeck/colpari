<?php

defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Functions,
    TeamBooking\Database,
    TeamBooking\Google;

/**
 * Class TeamBooking_Install
 *
 * @author VonStroheim
 */
class TeamBooking_Install
{
    /**
     * The installer method.
     *
     * @return boolean TRUE if all is done
     */
    public function install()
    {
        /*
         * extra safe-guard, the current user must have
         * activate_plugins capability
         */
        if (!current_user_can('activate_plugins')) {
            return TRUE;
        }
        /*
         * checking requirements
         */
        $php_min_version = '5.3.3';
        $php_current_version = phpversion();
        if (version_compare($php_min_version, $php_current_version, '>')) {
            trigger_error(sprintf(
                __('Team Booking requires PHP > 5.3.3, your server has PHP %s, please upgrade it before activate the plugin', 'team-booking')
                , $php_current_version), E_USER_ERROR);
        }
        /*
         * generate options object, if not present
         */
        if (!$this->check_options()) {
            // the Team Booking option record exists but it's not the right object
            return FALSE;
        }
        /*
         * get the "administrator" role object
         */
        $role = get_role('administrator');
        /*
         * add "tb_can_sync_calendar" to this role object
         */
        $role->add_cap('tb_can_sync_calendar');
        /*
         *  Create the tables
         */
        static::createReservationsTable();
        static::createPromotionsTable();
        static::createEventsTable();
        static::createSessionTable();

        /*
         * Set the cleaning cronjob
         */
        if (!wp_get_schedule('tb-db-cleaning-routine')) {
            wp_schedule_event(time(), 'daily', 'tb-db-cleaning-routine');
        }

        /**
         * Set the e-mail reminder handler cronjob
         */
        if (!wp_get_schedule('tb_email_reminder_handler')) {
            wp_schedule_event(time(), 'hourly', 'tb_email_reminder_handler');
        }

        return TRUE;
    }

    /**
     * Deactivation method
     *
     * @return type
     */
    public function deactivate()
    {
        /*
         * extra safe-guard, the current user must have
         * activate_plugins capability
         */
        if (!current_user_can('activate_plugins')) {
            return;
        }

        /*
         * Remove the cronjobs
         */
        wp_clear_scheduled_hook('tb-db-cleaning-routine');
        wp_clear_scheduled_hook('tb_email_reminder_handler');
    }

    /**
     * Uninstall method
     *
     * @global type $wp_roles
     * @global type $wpdb
     * @return boolean
     */
    public function uninstall()
    {
        /*
         * extra safe-guard, the current user must have
         * activate_plugins capability
         */
        if (!current_user_can('activate_plugins')) {
            return FALSE;
        }
        /*
         * remove custom capabilities
         */
        $delete_caps = array('tb_can_sync_calendar');
        global $wp_roles;
        foreach ($delete_caps as $cap) {
            foreach ($wp_roles->roles as $role => $value) {
                $wp_roles->remove_cap($role, $cap);
            }
        }
        /*
         * revoke all tokens programmatically
         */
        if (Functions\getSettings()) {
            /*
             * get the coworkers data array
             */
            $coworkers_data = Functions\getSettings()->getCoworkersData();
            if (NULL !== $coworkers_data) {
                /*
                 * looping through coworkers data
                 */
                foreach ($coworkers_data as $coworker_data) {
                    /* @var $coworker_data TeamBookingCoworker */
                    if (isset(json_decode($coworker_data->getAccessToken())->refresh_token)) {
                        /*
                         * a refresh token appears to be set, let's revoke it
                         */
                        $token = json_decode($coworker_data->getAccessToken())->refresh_token;
                        $client = new Google\Google_Client();
                        try {
                            $client->revokeToken($token);
                        } catch (Exception $e) {
                            echo $e->getMessage();
                        }
                    }
                }
            }
        }

        /*
         * remove database tables, if selected
         */
        if (Functions\getSettings()) {
            if (Functions\getSettings()->getDropTablesOnUninstall()) {
                global $wpdb;
                $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}teambooking_reservations");
                $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}teambooking_promotions");
                $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}teambooking_events");
                $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}teambooking_sessions");
                $post_args = array(
                    'post_type' => array('tbk_service', 'tbk_form'),
                    'nopaging'  => TRUE
                );
                $posts = get_posts($post_args);
                foreach ($posts as $post) {
                    $properties = array_keys(get_post_custom($post->ID));
                    foreach ($properties as $property) {
                        delete_post_meta_by_key($property);
                    }
                    wp_delete_post($post->ID, TRUE);
                }
            }
        }

        /*
         * remove plugin settings
         */
        delete_option('team_booking');

        return TRUE;
    }

    /**
     * Creates the settings object
     *
     * @return boolean
     */
    private function check_options()
    {
        /*
         * check if settings record is already present
         */
        if (!get_option('team_booking')) {
            /*
             * not present, so add it
             */
            $option = new TeamBookingSettings();
            add_option('team_booking', $option);

            return TRUE;
        } elseif (!get_option('team_booking') instanceof TeamBookingSettings) {
            /*
             * the settings object is not the right one!
             */
            return FALSE;
        } else {
            /*
             * the right settings object is present
             */
            return TRUE;
        }
    }

    /**
     * Creates the reservation database table
     *
     * @global $wpdb
     * @return string
     */
    public static function createReservationsTable()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_reservations';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name (
                        id int NOT NULL AUTO_INCREMENT,
                        created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                        updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        service_id tinytext NOT NULL,
                        coworker_id int NOT NULL,
                        calendar_id text NOT NULL,
                        event_id text,
                        status text,
                        token text,
                        start int,
                        end int,
                        customer_id int,
                        customer_timezone text,
                        enum_for_limit int,
                        event_parent_id text,
                        hangout_url text,
                        event_url text,
                        service_name text,
                        service_class text,
                        service_location text,
                        tickets int,
                        price decimal(20,2),
                        price_discounted decimal(20,2),
                        pending_reason text,
                        canc_reason text,
                        canc_who text,
                        confirm_who text,
                        email_reminder_sent int,
                        paid int,
                        payment_gateway text,
                        currency_code text,
                        post_id int,
                        post_title text,
                        slot_start text,
                        slot_end text,
                        form_fields text,
                        discounts text,
                        payment_details text,
                        files text,
                        customer_nicename text,
                        created_utc int,
                        order_id text,
                        wants_payment int,
                        frontend_lang text,
                        UNIQUE KEY id (id)
                      ) $charset_collate;";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }

        return $table_name;
    }

    /**
     * Creates the pricing database table
     *
     * @global type $wpdb
     * @return string
     */
    public static function createPromotionsTable()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_promotions';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name (
                        id int NOT NULL AUTO_INCREMENT,
                        created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                        updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        class tinytext NOT NULL,
                        start_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                        end_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                        data_object text not null,
                        UNIQUE KEY id (id)
                      ) $charset_collate;";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }

        return $table_name;
    }

    /**
     * Creates the pricing database table
     *
     * @global type $wpdb
     * @return string
     */
    public static function createEventsTable()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_events';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name (
                        id int NOT NULL AUTO_INCREMENT,
                        coworker_id int NOT NULL,
                        calendar_id text NOT NULL,
                        event_id text NOT NULL,
                        created text NOT NULL,
                        updated text NOT NULL,
                        status text,
                        color_id int,
                        description text,
                        hangout_link text,
                        html_link text,
                        location text,
                        recurrence text,
                        recurring_event_id text,
                        summary text,
                        event_start int,
                        event_end int,
                        start_timezone text,
                        end_timezone text,
                        organizer text,
                        guests text,
                        allday tinyint,
                        creator text,
                        UNIQUE KEY id (id)
                      ) $charset_collate;";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }

        return $table_name;
    }

    /**
     * Creates the sessions table
     *
     * @global $wpdb
     *
     * @return string
     */
    public static function createSessionTable()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_sessions';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name (
                        id int NOT NULL AUTO_INCREMENT,
                        session_key char(32) NOT NULL,
                        session_value longtext,
                        session_expiry int,
                        updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY  (session_key),
                        UNIQUE KEY id (id)
                      ) $charset_collate;";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }

        return $table_name;
    }

}
