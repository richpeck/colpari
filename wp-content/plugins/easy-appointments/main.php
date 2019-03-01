<?php

/**
 * Plugin Name: Easy Appointments
 * Plugin URI: https://easy-appointments.net/
 * Description: Simple and easy to use management system for Appointments and Bookings
 * Version: 2.3.12
 * Author: Nikola Loncar
 * Author URI: http://nikolaloncar.com
 * Text Domain: easy-appointments
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// path for source files
define('EA_SRC_DIR', dirname(__FILE__) . '/src/');

// path for JS files
define('EA_JS_DIR', dirname(__FILE__) . '/js/');

// url for EA plugin dir
define('EA_PLUGIN_URL', plugins_url(null, __FILE__) . '/');

// Register the autoloader that loads everything except the Google namespace.
if (version_compare(PHP_VERSION, '5.3', '<')) {
    if (!function_exists('ea_autoload')) {
        function ea_autoload($class)
        {
            global $ea_class_location;

            if (empty($ea_class_location)) {
                $ea_class_location = include dirname(__FILE__) . '/vendor/composer/autoload_classmap.php';
            }

            if (is_array($ea_class_location) && array_key_exists($class, $ea_class_location)) {
                require_once $ea_class_location[$class];
            }
        }
    }
    // register autoloader
    spl_autoload_register('ea_autoload');
} else {
    // PHP 5.3.0+ use composer auto loader
    require_once dirname(__FILE__) . '/vendor/autoload.php';
}

/**
 * Entry point
 */
class EasyAppointment
{

    /**
     * DI Container
     * @var tad_DI52_Container
     */
    protected $container;

    function __construct()
    {
        // empty for now
    }

    /**
     * Set all hooks and action callbacks
     */
    public function init()
    {
        $this->init_container();

        // on register hook
        register_activation_hook(__FILE__, array($this, 'install'));

        // registe uninstall hook
        register_uninstall_hook(__FILE__, array('EasyAppointment', 'uninstall'));

        // register deactivation hook
        register_deactivation_hook(__FILE__, array('EasyAppointment', 'remove_scheduled_event'));

        // plugin loaded
        add_action('plugins_loaded', array($this, 'update'));

        // cron
        add_action('easyapp_hourly_event', array($this, 'delete_reservations'));

        // we want to check if it is link from EA mail
        add_action('init', array($this, 'url_delete_reservations'));

        // init action for mails
        /** @var EAMail $mail */

        $mail = $this->container['mail'];
        $mail->init();

        // admin panel
        if (is_admin()) {
            /** @var EAAdminPanel $admin */
            $admin = $this->container['admin_panel'];
            $admin->init();
        } else {
            /** @var EAFrontend $frontend */
            $frontend = $this->container['frontend'];
            $frontend->init();
        }

        // ajax hooks
        /** @var EAAjax $ajax */
        $ajax = $this->container['ajax'];
        $ajax->init();
    }

    /**
     * Init DI Container, set all services as globals
     */
    public function init_container()
    {
        global $wpdb;

        $this->container = new tad_DI52_Container();
        $this->container['wpdb'] = $wpdb;

        $this->container['options'] = function($container) {
            return new EAOptions($container['wpdb']);
        };

        $this->container['table_columns'] = function ($container) {
            return new EATableColumns();
        };

        $this->container['db_models'] = function ($container) {
            return new EADBModels( $container['wpdb'], $container['table_columns'], $container['options']);
        };

        $this->container['datetime'] = function ($container) {
            return new EADateTime();
        };

        $this->container['logic'] = function ($container) {
            return new EALogic($container['wpdb'], $container['db_models'], $container['options']);
        };

        $this->container['install_tools'] = function ($container) {
            return new EAInstallTools( $container['wpdb'], $container['db_models'], $container['options']);
        };

        $this->container['report'] = function ($container) {
            return new EAReport($container['logic'], $container['options']);
        };

        $this->container['admin_panel'] = function ($container) {
            return new EAAdminPanel($container['options'], $container['logic'], $container['db_models'], $container['datetime'] );
        };

        $this->container['frontend'] = function ($container) {
            return new EAFrontend($container['db_models'], $container['options'], $container['datetime']);
        };

        $this->container['ajax'] = function ($container) {
            return new EAAjax($container['db_models'], $container['options'], $container['mail'], $container['logic'], $container['report']);
        };

        $this->container['mail'] = function ($container) {
            return new EAMail($container['wpdb'], $container['db_models'], $container['logic'], $container['options']);
        };

    }

    /**
     * @return tad_DI52_Container
     */
    public function get_container()
    {
        return $this->container;
    }

    /**
     * Installation of DB
     */
    public function install()
    {
        /** @var EAInstallTools $install */
        $install = $this->container['install_tools'];

        // skip update if db version are the same
        if ($install->easy_app_db_version != get_option('easy_app_db_version')) {
            $install->init_db();
            $install->init_data();
        }

        wp_schedule_event(time(), 'hourly', 'easyapp_hourly_event');
    }

    /**
     * Remove tables of Appointments plugin
     */
    public static function uninstall()
    {
        $uninstall = new EAUninstallTools();

        $uninstall->drop_db();
        $uninstall->delete_db_version();
    }

    /**
     * Remove cron action
     */
    public static function remove_scheduled_event()
    {
        wp_clear_scheduled_hook('easyapp_hourly_event');
    }

    public function update()
    {
        // register domain
        $this->register_text_domain();

        // update database
        /** @var EAInstallTools $tools */
        $tools = $this->container['install_tools'];
        $tools->update();
    }

    public function register_text_domain()
    {
        load_plugin_textdomain('easy-appointments', FALSE, basename(dirname(__FILE__)) . '/languages/');
    }

    /**
     * Reserved for cron execution, url for deleting reservations
     */
    public function url_delete_reservations()
    {
        $whitelist = array(
            '127.0.0.1',
            '::1'
        );

        if (!empty($_GET['_ea-action']) && $_GET['_ea-action'] == 'clear_reservations') {

            // only do this when is called from localhost
            if (in_array($_SERVER['REMOTE_ADDR'], $whitelist)) {
                $this->delete_reservations();
                die;
            }
        }
    }

    /**
     * Delete old reservations that are not complete
     */
    public function delete_reservations()
    {
        /** @var EADBModels $models */
        $models = $this->container['db_models'];
        $models->delete_reservations();
    }
}

/**
 * INIT EASY APPOINTMENTS
 */
$ea_app = new EasyAppointment;
$ea_app->init();
/**
 * END
 */
