<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Install tools
 *
 * Create whole DB stracture
 */
class EAUninstallTools
{

    /**
     * Delete all database tables of EA
     */
    public function drop_db()
    {
        global $wpdb;

        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ea_fields");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ea_appointments");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ea_connections");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ea_locations");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ea_services");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ea_staff");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ea_options");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ea_meta_fields");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ea_log_errors");
    }

    /**
     * Delete db version value
     */
    public function delete_db_version()
    {
        $option_name = 'easy_app_db_version';

        delete_option($option_name);
    }

    /**
     * Empty all database tables
     */
    public function clear_database()
    {
        global $wpdb;

        $tables = array(
            'ea_fields',
            'ea_appointments',
            'ea_connections',
            'ea_locations',
            'ea_options',
            'ea_services',
            'ea_staff',
        );

        $wpdb->query("SET FOREIGN_KEY_CHECKS=0;");
        $wpdb->query("SET AUTOCOMMIT = 0;");
        $wpdb->query("START TRANSACTION;");

        foreach ($tables as $table) {
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}{$table}");
        }

        $wpdb->query("SET FOREIGN_KEY_CHECKS=1;");
        $wpdb->query("COMMIT;");
    }
}
