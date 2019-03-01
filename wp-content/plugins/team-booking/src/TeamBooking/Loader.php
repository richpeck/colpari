<?php

namespace TeamBooking;
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class Loader
 *
 * @author VonStroheim
 */
class Loader
{
    public function load()
    {
        // load text domain for translations
        if (!defined('DOING_CRON') || !DOING_CRON) {
            add_action('init', array(
                $this,
                'load_textdomain',
            ));
        }

        // register custom post types
        add_action('init', array(
            'TeamBooking\\Database\\Services',
            'post_type',
        ));
        add_action('init', array(
            'TeamBooking\\Database\\Forms',
            'post_type',
        ));
        add_action('init', array(
            'TeamBooking\\Database\\EmailTemplates',
            'post_type',
        ));

        add_action(
            'admin_post_tb_get_ical',
            array(
                'TeamBooking\\ProcessReservation',
                'getIcalFile',
            )
        );
        add_action(
            'admin_post_nopriv_tb_get_ical',
            array(
                'TeamBooking\\ProcessReservation',
                'getIcalFile',
            )
        );

        // add oAuth handler
        add_action('wp_ajax_teambooking_oauth_callback', 'teambooking_oauth_callback');

        // add IPN listener
        add_action('wp_ajax_teambooking_ipn_listener', 'teambooking_ipn_listener');
        add_action('wp_ajax_nopriv_teambooking_ipn_listener', 'teambooking_ipn_listener');

        // add e-mail reminders handler
        add_action('tb_email_reminder_handler', 'TeamBooking\\Functions\\tbSendEmailReminder');

        add_action('widgets_init', function () {
            register_widget('TeamBooking\\Widgets\\Calendar');
            register_widget('TeamBooking\\Widgets\\Upcoming');
        });

        add_action('init', array(
            $this,
            'register_shortcodes',
        ));
        add_filter('set-screen-option', array(
            $this,
            'setScreenOptions',
        ), 10, 3);

        // Visual Composer elements
        if (defined('WPB_VC_VERSION') && function_exists('vc_map')) {
            require_once dirname(TEAMBOOKING_FILE_PATH) . '/includes/tb_vc_elements.php';
        }

        if (defined('DOING_CRON') && DOING_CRON) {
            add_action('tb-db-cleaning-routine', 'TeamBooking\\Functions\\cleanRoutine');
        }

        if (!defined('DOING_AJAX') || !DOING_AJAX) {

            if (!defined('DOING_CRON') || !DOING_CRON) {
                // load the cart
                add_action('init', array(
                    'TeamBooking\\Cart',
                    'loadCart',
                ));
            }

            if (is_admin()) {
                // activation hook
                register_activation_hook(TEAMBOOKING_FILE_PATH, array(
                    $this,
                    'install',
                ));
                // deactivation hook
                register_deactivation_hook(TEAMBOOKING_FILE_PATH, array(
                    $this,
                    'deactivate',
                ));
                // uninstall hook ($this can't work, TeamBooking_Loader class must be called directly)
                register_uninstall_hook(TEAMBOOKING_FILE_PATH, array(
                    'TeamBooking\\Loader',
                    'uninstall',
                ));

                /*
                 * Check settings integrity
                 */
                if (!Functions\getSettings() instanceof \TeamBookingSettings) {
                    add_action('init', function () {
                        if (!function_exists('deactivate_plugins')) {
                            require_once ABSPATH . 'wp-admin/includes/plugin.php';
                        }
                        deactivate_plugins(plugin_basename(TEAMBOOKING_FILE_PATH), TRUE, FALSE);
                        wp_redirect(admin_url('plugins.php'));
                    });
                } else {
                    /*
                     * TGM Plugin Activation loader
                     */
                    add_action('after_setup_theme', array(
                        $this,
                        'requireTGMPA',
                    ));

                    /*
                     * Update method
                     */
                    if (version_compare(Functions\getSettings()->getVersion(), TEAMBOOKING_VERSION, '<')) {
                        add_action('init', array(
                            'TeamBooking\\Update',
                            'update',
                        ));
                    }
                }

                new Admin();
            } else {
                add_action('wp_enqueue_scripts', array(
                    $this,
                    'tb_frontend_resources_enqueue',
                ));
            }
        } else {
            // add REST API handler
            add_action('wp_ajax_teambooking_rest_api', 'teambooking_rest_api');
            add_action('wp_ajax_nopriv_teambooking_rest_api', 'teambooking_rest_api');

            add_action(
                'wp_ajax_tb_submit_payment',
                array(
                    'TeamBooking\\ProcessReservation',
                    'submitPayment',
                ), 10, 0
            );
            add_action(
                'wp_ajax_nopriv_tb_submit_payment',
                array(
                    'TeamBooking\\ProcessReservation',
                    'submitPayment',
                ), 10, 0
            );
            add_action(
                'wp_ajax_tbajax_action_submit_form',
                array(
                    'TeamBooking\\ProcessReservation',
                    'submitReservation',
                ), 10, 0
            );
            add_action(
                'wp_ajax_nopriv_tbajax_action_submit_form',
                array(
                    'TeamBooking\\ProcessReservation',
                    'submitReservation',
                ), 10, 0
            );
            add_action(
                'wp_ajax_tbajax_action_prepare_form',
                array(
                    'TeamBooking\\ProcessReservation',
                    'prepareReservation',
                )
            );
            add_action(
                'wp_ajax_nopriv_tbajax_action_prepare_form',
                array(
                    'TeamBooking\\ProcessReservation',
                    'prepareReservation',
                )
            );
            add_action(
                'wp_ajax_tb_process_onsite_payment',
                array(
                    'TeamBooking\\ProcessReservation',
                    'processOnsite',
                )
            );
            add_action(
                'wp_ajax_nopriv_tb_process_onsite_payment',
                array(
                    'TeamBooking\\ProcessReservation',
                    'processOnsite',
                )
            );

            // register Ajax Callbacks
            add_action('wp_ajax_tbajax_action_change_month', 'tbajax_action_change_month_callback');
            add_action('wp_ajax_tbajax_action_show_day_schedule', 'tbajax_action_show_day_schedule_callback');
            add_action('wp_ajax_tbajax_action_filter_calendar', 'tbajax_action_filter_calendar_callback');
            add_action('wp_ajax_tbajax_action_filter_upcoming', 'tbajax_action_filter_upcoming_callback');
            add_action('wp_ajax_tbajax_action_upcoming_more', 'tbajax_action_upcoming_more_callback');
            add_action('wp_ajax_tbajax_action_get_reservation_modal', 'tbajax_action_get_reservation_modal_callback');
            add_action('wp_ajax_tbajax_action_put_slot_into_cart', 'tbajax_action_put_slot_into_cart_callback');
            add_action('wp_ajax_tbajax_action_checkout', 'tbajax_action_checkout_callback');
            add_action('wp_ajax_tbajax_action_checkout_cancel', 'tbajax_action_checkout_cancel_callback');
            add_action('wp_ajax_tbajax_action_checkout_confirm', 'tbajax_action_checkout_confirm_callback');
            add_action('wp_ajax_tbajax_action_get_register_modal', 'tbajax_action_get_register_modal_callback');
            add_action('wp_ajax_tbajax_action_save_cookie_consent', 'tbajax_action_save_cookie_consent_callback');
            add_action('wp_ajax_tbajax_action_fast_month_selector', 'tbajax_action_fast_month_selector_callback');
            add_action('wp_ajax_tbajax_action_fast_year_selector', 'tbajax_action_fast_year_selector_callback');
            add_action('wp_ajax_tbajax_action_cancel_reservation', 'tbajax_action_cancel_reservation_callback');
            add_action('wp_ajax_tbajax_action_validate_coupon', 'tbajax_action_validate_coupon_callback');
            add_action('wp_ajax_tbajax_action_validate_coupon_cart', 'tbajax_action_validate_coupon_cart_callback');
            add_action('wp_ajax_tbajax_action_checkout_edit_form', 'tbajax_action_checkout_edit_form_callback');

            add_action('wp_ajax_nopriv_tbajax_action_change_month', 'tbajax_action_change_month_callback');
            add_action('wp_ajax_nopriv_tbajax_action_show_day_schedule', 'tbajax_action_show_day_schedule_callback');
            add_action('wp_ajax_nopriv_tbajax_action_filter_calendar', 'tbajax_action_filter_calendar_callback');
            add_action('wp_ajax_nopriv_tbajax_action_filter_upcoming', 'tbajax_action_filter_upcoming_callback');
            add_action('wp_ajax_nopriv_tbajax_action_upcoming_more', 'tbajax_action_upcoming_more_callback');
            add_action('wp_ajax_nopriv_tbajax_action_get_reservation_modal', 'tbajax_action_get_reservation_modal_callback');
            add_action('wp_ajax_nopriv_tbajax_action_put_slot_into_cart', 'tbajax_action_put_slot_into_cart_callback');
            add_action('wp_ajax_nopriv_tbajax_action_checkout', 'tbajax_action_checkout_callback');
            add_action('wp_ajax_nopriv_tbajax_action_checkout_cancel', 'tbajax_action_checkout_cancel_callback');
            add_action('wp_ajax_nopriv_tbajax_action_checkout_confirm', 'tbajax_action_checkout_confirm_callback');
            add_action('wp_ajax_nopriv_tbajax_action_get_register_modal', 'tbajax_action_get_register_modal_callback');
            add_action('wp_ajax_nopriv_tbajax_action_save_cookie_consent', 'tbajax_action_save_cookie_consent_callback');
            add_action('wp_ajax_nopriv_tbajax_action_fast_month_selector', 'tbajax_action_fast_month_selector_callback');
            add_action('wp_ajax_nopriv_tbajax_action_fast_year_selector', 'tbajax_action_fast_year_selector_callback');
            add_action('wp_ajax_nopriv_tbajax_action_cancel_reservation', 'tbajax_action_cancel_reservation_callback');
            add_action('wp_ajax_nopriv_tbajax_action_validate_coupon', 'tbajax_action_validate_coupon_callback');
            add_action('wp_ajax_nopriv_tbajax_action_validate_coupon_cart', 'tbajax_action_validate_coupon_cart_callback');
            add_action('wp_ajax_nopriv_tbajax_action_checkout_edit_form', 'tbajax_action_checkout_edit_form_callback');
        }
    }

    /**
     * Load frontend resources
     */
    public static function tb_frontend_resources_enqueue()
    {
        Functions\registerFrontendResources();

        self::find_shortcodes();
        add_action('the_post', array('TeamBooking\\Loader', 'find_shortcodes'));
    }

    public static function find_shortcodes()
    {
        if (defined('TBK_CALENDAR_SHORTCODE_FOUND') && defined('TBK_RESERV_SHORTCODE_FOUND')) {
            return;
        }

        global $post;
        $post_meta = '';
        $post_content = '';
        if (is_object($post)) {
            $post_meta = get_post_meta($post->ID);
            $post_content = $post->post_content;
        }

        if (is_array($post_meta)) {
            $post_meta = json_encode($post_meta);
            $post_content .= $post_meta;
        }
        $calendar_check = (defined('TBK_WIDGET_SHORTCODE_FOUND')
            || defined('TBK_CALENDAR_SHORTCODE_FOUND')
            || has_shortcode($post_content, 'tb-calendar')
            || has_shortcode($post_content, 'tb-upcoming')
        );
        $reservations_check = (defined('TBK_RESERV_SHORTCODE_FOUND') || has_shortcode($post_content, 'tb-reservations'));
        if ($calendar_check || $reservations_check) {
            if ($calendar_check) {
                if (!defined('TBK_CALENDAR_SHORTCODE_FOUND')) {
                    define('TBK_CALENDAR_SHORTCODE_FOUND', TRUE);
                }
            }
            if ($reservations_check) {
                if (!defined('TBK_RESERV_SHORTCODE_FOUND')) {
                    define('TBK_RESERV_SHORTCODE_FOUND', TRUE);
                }
            }
            \TeamBooking\Functions\enqueueFrontendResources();
        }
    }

    /**
     * The plugin Install method
     * Creates an instance of the install-class and fires the installation method
     * It is used only during the activation of the plugin
     *
     * @param $networkwide
     *
     * @return bool
     */
    public function install($networkwide)
    {
        if ($networkwide && function_exists('is_multisite') && is_multisite()) {
            global $wpdb;
            $blogs = $wpdb->get_results("
                    SELECT blog_id
                    FROM {$wpdb->blogs}
                    WHERE site_id = '{$wpdb->siteid}'
                    AND spam = '0'
                    AND deleted = '0'
                    AND archived = '0'
                ");
            foreach ($blogs as $blog_id) {
                switch_to_blog($blog_id);
                $install = new \TeamBooking_Install();
                $install->install();
                restore_current_blog();
            }
            $return = TRUE;
        } else {
            $install = new \TeamBooking_Install();
            $return = $install->install();
        }

        return $return;
    }

    /**
     * The plugin Deactivate method
     * Creates an instance of the install-class and fires the deactivation method
     * It is used only during the deactivation of the plugin
     *
     * @param $networkwide
     */
    public function deactivate($networkwide)
    {
        $install = new \TeamBooking_Install();
        if ($networkwide && function_exists('is_multisite') && is_multisite()) {
            global $wpdb;
            $blogs = $wpdb->get_results("
                    SELECT blog_id
                    FROM {$wpdb->blogs}
                    WHERE site_id = '{$wpdb->siteid}'
                    AND spam = '0'
                    AND deleted = '0'
                    AND archived = '0'
                ");
            foreach ($blogs as $blog_id) {
                switch_to_blog($blog_id);
                $install->deactivate();
                restore_current_blog();
            }
        } else {
            $install->deactivate();
        }
    }

    /**
     * The plugin Uninstall method
     * Creates an instance of the install-class and fires the unistallation method
     * It is used only during the uninstallation of the plugin
     *
     * @param $networkwide
     */
    public static function uninstall($networkwide)
    {
        $install = new \TeamBooking_Install();
        if ($networkwide && function_exists('is_multisite') && is_multisite()) {
            global $wpdb;
            $blogs = $wpdb->get_results("
                    SELECT blog_id
                    FROM {$wpdb->blogs}
                    WHERE site_id = '{$wpdb->siteid}'
                    AND spam = '0'
                    AND deleted = '0'
                    AND archived = '0'
                ");
            foreach ($blogs as $blog_id) {
                switch_to_blog($blog_id);
                $install->uninstall();
                restore_current_blog();
            }
        } else {
            $install->uninstall();
        }
    }

    /**
     * Load the text domain on plugin load.
     *
     * Hooked to the plugins_loaded via the load method
     */
    public function load_textdomain()
    {
        $domain = 'team-booking';
        $locale = apply_filters('plugin_locale', get_locale(), $domain);
        load_textdomain($domain, WP_LANG_DIR . '/plugins/' . $domain . '/' . $domain . '-' . $locale . '.mo');
        load_plugin_textdomain($domain, FALSE, plugin_basename(TEAMBOOKING_PATH) . '/languages/');
    }

    public function requireTGMPA()
    {
        $load_tgmpa = TRUE;

        if (isset($GLOBALS['tgmpa']) && is_object($GLOBALS['tgmpa'])) {
            $tgmpa_class = get_class($GLOBALS['tgmpa']);
            if ($tgmpa_class !== 'TGM_Plugin_Activation') {
                $load_tgmpa = FALSE;
            }
        }

        if ($load_tgmpa) {
            include_once dirname(TEAMBOOKING_FILE_PATH) . '/libs/tgm/class-tgm-plugin-activation.php';
            add_action('tgmpa_register', array(
                $this,
                'requiredPlugins',
            ));
        }
    }

    public function requiredPlugins()
    {
        /*
         * Array of plugin arrays. Required keys are name and slug.
         * If the source is NOT from the .org repo, then source is also required.
         */
        $plugins = array(
            array(
                'name'         => 'Envato Market',
                'slug'         => 'envato-market',
                'source'       => 'http://envato.github.io/wp-envato-market/dist/envato-market.zip',
                'required'     => FALSE,
                'external_url' => '',
            ),
        );

        $config = array(
            'id'           => 'tgmpa_tbk',
            'default_path' => '',
            'menu'         => 'tgmpa-install-plugins',
            'parent_slug'  => 'plugins.php',
            'capability'   => 'manage_options',
            'has_notices'  => TRUE,
            'dismissable'  => TRUE,
            'dismiss_msg'  => '',
            'is_automatic' => FALSE,
            'message'      => '',
            'strings'      => array(
                'page_title'                     => __('Install Recommended Plugins', 'team-booking'),
                'menu_title'                     => __('Install Plugins', 'team-booking'),
                'installing'                     => __('Installing Plugin: %s', 'team-booking'),
                'notice_can_install_recommended' => _n_noop(
                    'The following plugin is recommended: %1$s.',
                    'The following plugins are recommended: %1$s.',
                    'team-booking'
                ),
                'notice_ask_to_update'           => _n_noop(
                    'The following plugin needs to be updated to its latest version to ensure maximum compatibility with TeamBooking: %1$s.',
                    'The following plugins need to be updated to their latest version to ensure maximum compatibility with TeamBooking: %1$s.',
                    'team-booking'
                ),
                'notice_ask_to_update_maybe'     => _n_noop(
                    'There is an update available for: %1$s.',
                    'There are updates available for the following plugins: %1$s.',
                    'team-booking'
                ),
            ),
        );

        tgmpa($plugins, $config);
    }

    /**
     * Register Shortcodes handler
     */
    public function register_shortcodes()
    {
        // Main shortcode
        add_shortcode('tb-calendar', array(
            'TeamBooking\\Shortcodes\\Calendar',
            'render',
        ));

        // User reservation list
        add_shortcode('tb-reservations', array(
            'TeamBooking\\Shortcodes\\Reservations',
            'render',
        ));

        // Upcoming events list
        add_shortcode('tb-upcoming', array(
            'TeamBooking\\Shortcodes\\Upcoming',
            'render',
        ));
    }

    /**
     * @param $status
     * @param $option
     * @param $value
     *
     * @return mixed
     */
    public function setScreenOptions($status, $option, $value)
    {
        if ('tbk_reservations_per_page' === $option) return $value;

        return $status;
    }
}
