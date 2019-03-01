<?php

namespace TeamBooking;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Admin\Notices,
    TeamBooking\Database,
    TeamBooking\Functions,
    TeamBooking\Admin\Overview,
    TeamBooking\FormElements\Factory;
use TeamBooking\EmailTemplates\EmailTemplate;

/**
 * Class Admin
 *
 * @author VonStroheim
 */
class Admin
{
    public function __construct()
    {
        // Admin panel callback registration
        add_action('admin_menu', array(
            $this,
            'addAdminMenu',
        ), 9);
        // Admin panel form callback registration
        add_action('admin_post_tbk_save_service', array(
            $this,
            'saveService',
        ));
        add_action('admin_post_tbk_save_core', array(
            $this,
            'saveCoreSettings',
        ));
        add_action('admin_post_tbk_settings_backup', array(
            $this,
            'settingsBackup',
        ));
        add_action('admin_post_tbk_add_service', array(
            $this,
            'addNewService',
        ));
        add_action('admin_post_tbk_delete_service', array(
            $this,
            'deleteService',
        ));
        add_action('admin_post_tbk_delete_all_reservations', array(
            $this,
            'deleteAllReservations',
        ));
        add_action('admin_post_tbk_delete_selected_services', array(
            $this,
            'deleteSelectedServices',
        ));
        add_action('admin_post_tbk_confirm_pending_reservation', array(
            $this,
            'confirmPendingPaymentReservation',
        ));
        add_action('admin_post_tbk_repair_db', array(
            $this,
            'repairDatabase',
        ));
        add_action('admin_post_tbk_core_from_json', array(
            $this,
            'importGoogleProjectFromJSON',
        ));
        add_action('admin_post_tbk_create_api_token', array(
            $this,
            'createApiToken',
        ));
        add_action('admin_post_tbk_revoke_api_token', array(
            $this,
            'revokeApiToken',
        ));
        add_action('admin_post_tbk_add_custom_field', array(
            $this,
            'addServiceCustomField',
        ));
        add_action('admin_post_tbk_remove_custom_field', array(
            $this,
            'removeServiceCustomField',
        ));
        add_action('admin_post_tbk_move_field', array(
            $this,
            'moveServiceFormField',
        ));
        add_action('admin_post_tbk_clone_service', array(
            $this,
            'cloneService',
        ));
        add_action('admin_post_tbk_clone_service_hint', array(
            $this,
            'cloneServiceSuggestion',
        ));
        add_action('admin_post_tbk_add_email_template', array(
            $this,
            'addEmailTemplate',
        ));
        add_action('admin_post_tbk_add_campaign', array(
            $this,
            'insertPromotionCampaign',
        ));
        add_action('admin_post_tbk_add_coupon', array(
            $this,
            'insertPromotionCoupon',
        ));
        add_action('admin_post_tbk_edit_promotion', array(
            $this,
            'editPromotion',
        ));
        add_action('admin_post_tbk_delete_promotion', array(
            $this,
            'deletePromotion',
        ));
        add_action('admin_post_tbk_toggle_promotion', array(
            $this,
            'changePromotionStatus',
        ));
        add_action('admin_post_tbk_reset_enum_res', array(
            $this,
            'resetEnumerableReservations',
        ));
        add_action('admin_post_tbk_clean_errors', array(
            $this,
            'cleanErrorLogs',
        ));
        add_action('admin_post_tbk_remove_coworker_residual', array(
            $this,
            'removeCoworkerResidualData',
        ));
        add_action('admin_post_tbk_revoke_auth_token', array(
            $this,
            'revokeAuthToken',
        ));
        add_action('admin_post_tbk_approve_reservation', array(
            $this,
            'approveServiceReservation',
        ));
        add_action('admin_post_tbk_add_gcal', array(
            $this,
            'addGoogleCalendar',
        ));
        add_action('admin_post_tbk_remove_gcal', array(
            $this,
            'removeGoogleCalendar',
        ));
        add_action('admin_post_tbk_clean_gcal', array(
            $this,
            'cleanGoogleCalendar',
        ));
        add_action('admin_post_tbk_save_style', array(
            $this,
            'saveFrontendStyle',
        ));
        add_action('admin_post_tbk_save_payments', array(
            $this,
            'savePaymentsSettings',
        ));
        add_action('admin_post_tbk_import_settings', array(
            $this,
            'importSettingsFromFile',
        ));
        add_action('admin_post_tbk_save_coworker_settings', array(
            $this,
            'saveCoworkerSettings',
        ));
        add_action('admin_post_tbk_edit_reservation_data', array(
            $this,
            'editReservationData',
        ));
        add_action('admin_post_tbk_send_email_again', array(
            $this,
            'sendEmailAgain',
        ));
        add_action('admin_post_tbk_sync_all_calendars', array(
            $this,
            'syncAllCalendars',
        ));
        add_action('admin_post_tbk_sync_gcal', array(
            $this,
            'syncCalendar',
        ));
        add_action('admin_post_tbk_save_builtin_field', array(
            $this,
            'saveServiceBuiltinField',
        ));
        add_action('admin_post_tbk_save_custom_field', array(
            $this,
            'saveServiceCustomField',
        ));
        add_action('admin_post_tbk_toggle_service', array(
            $this,
            'toggleServiceActivation',
        ));
        add_action('admin_post_tbk_toggle_gcal_indep', array(
            $this,
            'toggleCalendarIndependency',
        ));
        add_action('admin_post_tbk_delete_reservation', array(
            $this,
            'deleteServiceReservation',
        ));
        add_action('admin_post_tbk_cancel_reservation', array(
            $this,
            'cancelServiceReservation',
        ));
        add_action('admin_post_tbk_revoke_personal_token', array(
            $this,
            'revokePersonalAuthToken',
        ));
        add_action('admin_post_tbk_bulk_csv', array(
            $this,
            'bulkCSV',
        ));
        add_action('admin_post_tbk_bulk_xlsx', array(
            $this,
            'bulkXLSX',
        ));
        add_action('admin_post_tbk_csv_all', array(
            $this,
            'getCSV',
        ));
        add_action('admin_post_tbk_xlsx_all', array(
            $this,
            'getXLSX',
        ));
        add_action('admin_post_tbk_print_res_pdf', array(
            $this,
            'getReservationPDF',
        ));
        add_action('admin_post_tbk_customers_csv', array(
            $this,
            'getCustomersCSV',
        ));
        add_action('admin_post_tbk_customers_xlsx', array(
            $this,
            'getCustomersXLSX',
        ));
        add_action('admin_post_tbk_get_res_details', array(
            $this,
            'getReservationDetails',
        ));
        add_action('admin_post_tbk_send_email_reminder_manually', array(
            $this,
            'sendEmailReminderManually',
        ));
        // Activate output buffer, needed until a better solution to wp_redirect "headers already sent" error will come up
        add_action('init', array(
            $this,
            'activateOutputBuffer',
        ));
        add_action('admin_enqueue_scripts', array(
            $this,
            'addColorPicker',
        ));
        add_action('admin_enqueue_scripts', array(
            $this,
            'enqueueResources',
        ));
        add_action('admin_head', array(
            $this,
            'addTinyMCEButton',
        ));

        // Debug filters
        #add_filter( 'is_protected_meta', '__return_false' );
    }

    public function addAdminMenu()
    {
        $bubble_title = __('new reservations', 'team-booking');
        if (Functions\isAdmin()) {
            // User is an admin coworker
            $new_reservation_ids = $this->getNewReservationIds();
            $bubble_count = count($new_reservation_ids);
            $bubble_span = " <span class='update-plugins tb-bubble count-$bubble_count' title='" . esc_attr($bubble_title) . "'><span class='update-count'>"
                . number_format_i18n($bubble_count)
                . '</span></span>';
            $page_hook = add_menu_page('Team Booking', 'TeamBooking' . $bubble_span, 'manage_options', 'team-booking', array(
                $this,
                'createAdminPage',
            ), TEAMBOOKING_URL . 'images/admin-icon.png');
            add_submenu_page('team-booking', 'Team Booking', __('Reservations', 'team-booking') . $bubble_span, 'manage_options', 'team-booking', array(
                $this,
                'createAdminPage',
            ));
            $page_hook_slots = add_submenu_page('team-booking', 'Team Booking', __('Slots', 'team-booking'), 'manage_options', 'team-booking-slots', array(
                $this,
                'createAdminPage',
            ));
            add_submenu_page('team-booking', 'Team Booking', __('Services', 'team-booking'), 'manage_options', 'team-booking-events', array(
                $this,
                'createAdminPage',
            ));
            add_submenu_page('team-booking', 'Team Booking', __('Coworkers', 'team-booking'), 'manage_options', 'team-booking-coworkers', array(
                $this,
                'createAdminPage',
            ));
            add_submenu_page('team-booking', 'Team Booking', __('Customers', 'team-booking'), 'manage_options', 'team-booking-customers', array(
                $this,
                'createAdminPage',
            ));
            add_submenu_page('team-booking', 'Team Booking', __('Personal', 'team-booking'), 'manage_options', 'team-booking-personal', array(
                $this,
                'createAdminPage',
            ));
            add_submenu_page('team-booking', 'Team Booking', __('Frontend style', 'team-booking'), 'manage_options', 'team-booking-aspect', array(
                $this,
                'createAdminPage',
            ));
            add_submenu_page('team-booking', 'Team Booking', __('Core settings', 'team-booking'), 'manage_options', 'team-booking-general', array(
                $this,
                'createAdminPage',
            ));
            add_submenu_page('team-booking', 'Team Booking', __('Payment Gateways', 'team-booking'), 'manage_options', 'team-booking-payments', array(
                $this,
                'createAdminPage',
            ));
            add_submenu_page('team-booking', 'Team Booking', __('Promotions', 'team-booking'), 'manage_options', 'team-booking-pricing', array(
                $this,
                'createAdminPage',
            ));
            add_action("load-$page_hook", array(
                $this,
                'addScreenOptions',
            ));
            add_action("load-$page_hook_slots", array(
                $this,
                'addScreenOptions',
            ));
        } elseif (current_user_can('tb_can_sync_calendar')) {
            // User is a non-admin coworker
            $new_reservation_ids = $this->getNewReservationIds(FALSE);
            $bubble_count = count($new_reservation_ids);
            $bubble_span = " <span class='update-plugins tb-bubble count-$bubble_count' title='" . esc_attr($bubble_title) . "'><span class='update-count'>"
                . number_format_i18n($bubble_count)
                . '</span></span>';
            $page_hook = add_menu_page('Team Booking', 'TeamBooking' . $bubble_span, 'tb_can_sync_calendar', 'team-booking', array(
                $this,
                'createCoworkerPage',
            ), TEAMBOOKING_URL . 'images/admin-icon.png');
            add_submenu_page('team-booking', 'Team Booking', __('Reservations', 'team-booking') . $bubble_span, 'tb_can_sync_calendar', 'team-booking', array(
                $this,
                'createCoworkerPage',
            ));
            $page_hook_slots = add_submenu_page('team-booking', 'Team Booking', __('Slots', 'team-booking'), 'tb_can_sync_calendar', 'team-booking-slots', array(
                $this,
                'createCoworkerPage',
            ));
            add_submenu_page('team-booking', 'Team Booking', __('Services', 'team-booking'), 'tb_can_sync_calendar', 'team-booking-events', array(
                $this,
                'createCoworkerPage',
            ));
            add_submenu_page('team-booking', 'Team Booking', __('Personal', 'team-booking'), 'tb_can_sync_calendar', 'team-booking-personal', array(
                $this,
                'createCoworkerPage',
            ));
            add_action("load-$page_hook", array(
                $this,
                'addScreenOptions',
            ));
            add_action("load-$page_hook_slots", array(
                $this,
                'addScreenOptions',
            ));
        } else {
            // Anyone else
            return;
        }
    }

    public function addScreenOptions()
    {
        global $plugin_page;
        if ($plugin_page === 'team-booking') {
            $args = array(
                'label'   => isset($_GET['show']) && $_GET['show'] === 'orders' ? esc_html__('Orders per page', 'team-booking') : esc_html__('Reservations per page', 'team-booking'),
                'default' => 20,
                'option'  => isset($_GET['show']) && $_GET['show'] === 'orders' ? 'tbk_orders_per_page' : 'tbk_reservations_per_page'
            );
            add_screen_option('per_page', $args);
        } elseif ($plugin_page === 'team-booking-slots') {
            $args = array(
                'label'   => esc_html__('Slots per page', 'team-booking'),
                'default' => 20,
                'option'  => 'tbk_slots_per_page'
            );
            add_screen_option('per_page', $args);
        }
    }

    /**
     * @param bool|TRUE $is_admin
     *
     * @return array
     */
    private function getNewReservationIds($is_admin = TRUE)
    {
        $transient = get_transient('teambooking_seen_reservations');
        if (!$transient) {
            $transient = array();
        }
        if (!isset($transient[ get_current_user_id() ])) {
            $transient[ get_current_user_id() ] = array();
        }

        if ($is_admin) {
            $reservation_ids = Database\Reservations::getIDs();
        } else {
            $reservation_ids = Database\Reservations::getIDsByCoworker(get_current_user_id());
        }

        return array_diff($reservation_ids, $transient[ get_current_user_id() ]);
    }

    /**
     * Fetch WP Roles
     *
     * @return array List of editable roles
     */
    private function fetchRoles()
    {
        return get_editable_roles();
    }

    /**
     * This method retrieve all the roles with tb_can_sync_calendar capability
     *
     * @return array List of roles allowed to link a calendar
     */
    public function getRolesWithSyncCap()
    {
        $roles = $this->fetchRoles();
        $list = array();
        foreach ($roles as $name => $role) {
            if (isset($role['capabilities']['tb_can_sync_calendar'])) {
                $list[ $name ] = $role['name'];
            }
        }

        return $list;
    }

    /**
     * Load WP Color Pickers
     *
     * @param $hook
     */
    public function addColorPicker($hook)
    {
        // let's load only if we're on Team Booking dashboard
        if (strpos($hook, 'page_team-booking') !== FALSE) {
            wp_enqueue_style('wp-color-picker');
        }
    }

    /**
     * @param string $link
     * @param bool   $no_nags
     *
     * @return string
     */
    public static function add_params_to_admin_url($link, $no_nags = TRUE)
    {
        foreach ($_GET as $param => $value) {
            if ($no_nags && 0 === strpos($param, 'nag_')) {
                continue;
            }
            $link = add_query_arg($param, $value, $link);
        }

        return $link;
    }

    /**
     * @param string $hook
     */
    public function enqueueResources($hook)
    {
        // let's load only if we're on Team Booking dashboard
        if (strpos($hook, 'page_team-booking') !== FALSE) {

            //////////////////////
            // jQuery resources //
            //////////////////////
            wp_enqueue_script('tb-admin-script', TEAMBOOKING_URL . 'js/admin.min.js', array(
                'jquery',
                'wp-color-picker',
                'jquery-ui-core',
                'jquery-ui-sortable',
            ), filemtime(TEAMBOOKING_PATH . 'js/admin.min.js'));
            // in javascript, object properties are accessed as ajax_object.some_value
            wp_localize_script('tb-admin-script', 'TB_vars', array(
                'post_url' => static::add_params_to_admin_url(admin_url('admin-post.php')),
                'wpNonce'  => wp_create_nonce('team_booking_options_verify'),
            ));

            //////////////////////
            //   admin CSS      //
            //////////////////////
            wp_enqueue_style('tb-admin-style', TEAMBOOKING_URL . 'css/admin.css', array(), filemtime(TEAMBOOKING_PATH . 'css/admin.css'));

            ////////////////////////
            // semantic UI stuff  //
            ////////////////////////
            $comp_path = 'libs/semantic/components/';
            wp_enqueue_script('semantic-modal-script', TEAMBOOKING_URL . $comp_path . 'modal.js', array('jquery'), filemtime(TEAMBOOKING_PATH . $comp_path . 'modal.js'));
            wp_enqueue_script('semantic-transition-script', TEAMBOOKING_URL . $comp_path . 'transition.js', array('jquery'), filemtime(TEAMBOOKING_PATH . $comp_path . 'transition.js'));
            wp_enqueue_style('semantic-modal-style', TEAMBOOKING_URL . $comp_path . 'modal.css', array(), filemtime(TEAMBOOKING_PATH . $comp_path . 'modal.css'));
            wp_enqueue_style('semantic-transition-style', TEAMBOOKING_URL . $comp_path . 'transition.css', array(), filemtime(TEAMBOOKING_PATH . $comp_path . 'transition.css'));
            wp_enqueue_script('semantic-dimmer-script', TEAMBOOKING_URL . $comp_path . 'dimmer.js', array('jquery'), filemtime(TEAMBOOKING_PATH . $comp_path . 'dimmer.js'));
            wp_enqueue_style('semantic-dimmer-style', TEAMBOOKING_URL . $comp_path . 'dimmer.css', array(), filemtime(TEAMBOOKING_PATH . $comp_path . 'dimmer.css'));
            wp_enqueue_style('semantic-button-style', TEAMBOOKING_URL . $comp_path . 'button.css', array(), filemtime(TEAMBOOKING_PATH . $comp_path . 'button.css'));
            wp_enqueue_style('semantic-icon-style', TEAMBOOKING_URL . $comp_path . 'icon.css', array(), filemtime(TEAMBOOKING_PATH . $comp_path . 'icon.css'));
            wp_enqueue_script('semantic-checkbox-script', TEAMBOOKING_URL . $comp_path . 'checkbox.js', array('jquery'), filemtime(TEAMBOOKING_PATH . $comp_path . 'checkbox.js'));
            wp_enqueue_style('semantic-checkbox-style', TEAMBOOKING_URL . $comp_path . 'checkbox.css', array(), filemtime(TEAMBOOKING_PATH . $comp_path . 'transition.css'));
            wp_enqueue_style('semantic-label-style', TEAMBOOKING_URL . $comp_path . 'label.css', array(), filemtime(TEAMBOOKING_PATH . $comp_path . 'label.css'));

            /////////////////////////
            // jQuery AreYouSure?  //
            /////////////////////////
            wp_enqueue_script('tb-areyousure-js', TEAMBOOKING_URL . 'js/assets/jquery.are-you-sure.js', array('jquery'), filemtime(TEAMBOOKING_PATH . 'js/assets/jquery.are-you-sure.js'));

            /////////////////////////
            // jQuery autoNumeric  //
            /////////////////////////
            wp_enqueue_script('tb-autonumeric-js', TEAMBOOKING_URL . 'js/assets/autoNumeric.js', array('jquery'), filemtime(TEAMBOOKING_PATH . 'js/assets/autoNumeric.js'));

            ///////////////////////
            // jQuery flatpickr  //
            ///////////////////////
            wp_enqueue_script('tb-flatpickr-js', TEAMBOOKING_URL . 'libs/flatpickr/flatpickr.min.js', array('jquery'), filemtime(TEAMBOOKING_PATH . 'libs/flatpickr/flatpickr.min.js'));
            @wp_enqueue_script('tb-flatpickr-loc-js', TEAMBOOKING_URL . 'libs/flatpickr/' . TEAMBOOKING_SHORT_LANG . '.js', array('jquery'), filemtime(TEAMBOOKING_PATH . 'libs/flatpickr/' . TEAMBOOKING_SHORT_LANG . '.js'));
            wp_enqueue_style('tb-flatpickr-style', TEAMBOOKING_URL . 'libs/flatpickr/flatpickr.material_blue.min.css', array(), filemtime(TEAMBOOKING_PATH . 'libs/flatpickr/flatpickr.material_blue.min.css'));
        }
        //////////////////////////////
        //   TinyMCE button CSS     //
        //////////////////////////////
        wp_enqueue_style('tb-tinymcebutton-style', TEAMBOOKING_URL . 'css/tinymce.css', array(), filemtime(TEAMBOOKING_PATH . 'css/tinymce.css'));
    }

    /**
     *  Activates output buffering
     */
    public function activateOutputBuffer()
    {
        ob_start();
    }

    /**
     * This method just intercept $_GET data looking for notices
     */
    public function checkForNotices()
    {
        require_once dirname(TEAMBOOKING_FILE_PATH) . '/includes/tb_admin_notices.php';

        if (isset($_GET['nag_success'])) {
            Notices\generic_success();
        }
        if (isset($_GET['nag_updated'])) {
            Notices\core_saved();
        }
        if (isset($_GET['nag_settings_imported'])) {
            Notices\core_imported();
        }
        if (isset($_GET['nag_version_mismatch'])) {
            Notices\core_version_mismatch();
        }
        if (isset($_GET['nag_not_settings_file'])) {
            Notices\core_wrong_file();
        }
        if (isset($_GET['nag_event_added'])) {
            Notices\service_added();
        }
        if (isset($_GET['nag_event_updated'])) {
            Notices\service_updated();
        }
        if (isset($_GET['nag_event_deleted'])) {
            Notices\service_deleted();
        }
        if (isset($_GET['nag_selected_services_deleted'])) {
            Notices\services_deleted();
        }
        if (isset($_GET['nag_custom_settings_updated'])) {
            Notices\personal_updated();
        }
        if (isset($_GET['nag_reset'])) {
            Notices\personal_deleted();
        }
        if (isset($_GET['nag_partialreset'])) {
            Notices\personal_partial();
        }
        if (isset($_GET['nag_auth_success'])) {
            Notices\personal_success();
        }
        if (isset($_GET['nag_auth_failed'])) {
            Notices\personal_failed();
        }
        if (isset($_GET['nag_auth_no_refresh'])) {
            Notices\personal_no_refresh();
        }
        if (isset($_GET['nag_auth_already_used'])) {
            Notices\personal_already_used();
        }
        if (isset($_GET['nag_duplicated_linked_title'])) {
            Notices\personal_duplicated_event_title($_GET['nag_title_value'], $_GET['nag_service_name']);
        }
        if (isset($_GET['nag_duplicated_booked_title'])) {
            Notices\personal_duplicated_event_booked_title($_GET['nag_title_value'], $_GET['nag_service_name']);
        }
        if (isset($_GET['nag_event_title_not_allowed'])) {
            Notices\personal_event_title_not_allowed();
        }
        if (isset($_GET['nag_reset_failed'])) {
            Notices\personal_revoke_failed();
        }
        if (isset($_GET['nag_reservation_cancelled'])) {
            Notices\overview_reservation_cancelled();
        }
        if (isset($_GET['nag_reservation_approved'])) {
            Notices\overview_reservation_confirmed();
        }
        if (isset($_GET['nag_reservation_confirmed'])) {
            Notices\overview_reservation_confirmed();
        }
        if (isset($_GET['nag_not_revokable'])) {
            Notices\overview_reservation_not_revokable();
        }
        if (isset($_GET['nag_generic_error'])) {
            Notices\overview_generic_error();
        }
        if (isset($_GET['nag_error_logs_cleaned'])) {
            Notices\overview_logs_cleaned();
        }
        if (isset($_GET['nag_token_added'])) {
            Notices\core_token_added();
        }
        if (isset($_GET['nag_token_removed'])) {
            Notices\core_token_removed();
        }
        if (isset($_GET['nag_database_repaired'])) {
            Notices\core_database_repaired();
        }

        $client_id = Functions\getSettings()->getApplicationClientId();
        $client_secret = Functions\getSettings()->getApplicationClientSecret();
        if (empty($client_id) || empty($client_secret)) {
            Notices\not_configured();
        }
        try {
            new \DateTimeZone(get_option('timezone_string'));
        } catch (\Exception $ex) {
            Notices\timezone_approx();
        }

        do_action('tbk_admin_nags', static::add_params_to_admin_url(admin_url('admin.php')));

    }

    /**
     * Builds the admin Teambooking page (Administrators)
     */
    public function createAdminPage()
    {
        global $plugin_page;
        $slug = str_replace('team-booking-', '', $plugin_page, $count);
        ?>
        <div class="wrap">
            <h2 style="padding:0;"></h2><?php
            //Hack for Wordpress alerts that should stay always on top                                                                                                                                                                            
            // Checks for notices
            $this->checkForNotices();
            // Catch the what's new page
            if (isset($_GET['whatsnew'])) {
                Admin\Misc::render()->getWhatsnewPage();
            } else {
                // Defining tabs
                if ($count === 1) {
                    // Select the active tab, if present...
                    Admin\Misc::render()->getTabWrapper($slug);
                    $function_to_call = 'buildAdminTab' . $slug;
                    $this->$function_to_call();
                } else {
                    // ...or provide the default one
                    Admin\Misc::render()->getTabWrapper('overview');
                    $this->buildAdminTaboverview();
                }
            }
            ?>
        </div>
        <?php
    }

    /**
     * Builds the admin Teambooking page (Coworkers)
     */
    public function createCoworkerPage()
    {
        global $plugin_page;
        $slug = str_replace('team-booking-', '', $plugin_page, $count);
        ?>
        <div class="wrap">
            <h2 style="padding:0;"></h2><?php
            //Hack for Wordpress alerts that should stay always on top                                                                                                                                                                              
            // Checks for notices
            $this->checkForNotices();
            // Defining allowed tabs
            $allowed_tabs = array(
                'overview',
                'events',
                'slots',
                'personal'
            );
            if ($count === 1) {
                // Whitelisting tab slug
                if (in_array($slug, $allowed_tabs)) {
                    // Select the active tab, if present...
                    Admin\Misc::render()->getTabWrapperCoworker($slug);
                    $function_to_call = 'buildAdminTab' . $slug;
                    $this->$function_to_call();
                } else {
                    // No authorized/valid tab name, so collapse to default
                    Admin\Misc::render()->getTabWrapperCoworker('overview');
                    $this->buildAdminTaboverview();
                }
            } else {
                // ...or provide the default one
                Admin\Misc::render()->getTabWrapperCoworker('overview');
                $this->buildAdminTaboverview();
            }
            ?>
        </div>
        <?php
    }

    /**
     * Builds the general settings tab
     */
    public function buildAdminTabgeneral()
    {
        // Template call
        $content = new Admin\Core();
        // Set variables
        $content->roles_all = $this->fetchRoles();
        $content->roles_allowed = $this->getRolesWithSyncCap();
        // Render page
        echo $content->getPostBody();
    }

    public function saveService()
    {
        if (!Functions\isAdminOrCoworker()) exit;
        check_admin_referer('team_booking_options_verify');
        if (isset($_POST['team_booking_update_booking_type'])) {
            $this->saveServiceSettings();
        } elseif (isset($_POST['team_booking_save_personal_event_settings'])) {
            $this->saveCoworkerServiceSettings();
        } elseif (isset($_POST['team_booking_update_booking_type_apply_to_all'])) {
            $this->saveServiceSettings(TRUE);
        } elseif (isset($_POST['team_booking_save_personal_event_settings_apply_to_all'])) {
            $this->saveCoworkerServiceSettings(FALSE, TRUE);
        }
    }

    public function buildAdminTabcoworkers()
    {
        $content = new Admin\Coworkers();
        echo $content->getPostBody();
    }

    public function buildAdminTabevents()
    {
        // Case: service details page
        if (isset($_GET['event'])) {
            // Checks if service_id exists, otherwise redirects to main list
            try {
                $service = Database\Services::get($_GET['event']);
            } catch (\Exception $e) {
                exit(wp_redirect(admin_url('admin.php?page=team-booking-events')));
            }
            // Template call
            $content = new Admin\Service($service);
            // Render page
            if (isset($_GET['email'])) {
                echo $content->getPostBodyEmail();
            } elseif (isset($_GET['gcal'])) {
                echo $content->getPostBodyGcalSettings();
            } elseif (isset($_GET['form'])) {
                if (!Functions\isAdmin()) {
                    exit(wp_redirect(admin_url('admin.php?page=team-booking-events')));
                }
                echo $content->getPostBodyReservationForm();
            } else {
                if (!Functions\isAdmin()) {
                    exit(wp_redirect(admin_url('admin.php?page=team-booking-events')));
                }
                echo $content->getPostBody();
            }
        } // Case: services list
        else {
            // Template call
            $content = new Admin\Services();
            // Render content
            echo $content->getPostBody();
        }
    }

    public function buildAdminTabpersonal()
    {
        // Template call
        $content = new Admin\Personal();
        echo $content->getPostBody();
    }

    public function buildAdminTabaspect()
    {
        wp_enqueue_style('semantic-grid-style', TEAMBOOKING_URL . 'libs/semantic/components/grid.css');
        wp_enqueue_style('teambooking_fonts', '//fonts.googleapis.com/css?family=Oswald|Open+Sans:300italic,400,300,700|Josefin+Sans:400,700', array(), '1.0.0');
        // Template call
        $content = new Admin\Style();
        echo $content->getPostBody();
    }

    public function buildAdminTaboverview()
    {
        // Syncing the events
        $process = new Fetch\fromGoogle();
        $process->sync();

        // Retrieving reservations
        $reservation_keys = Database\Reservations::getAllIds();

        // Update the transient for new reservations notification
        $transient = get_transient('teambooking_seen_reservations');
        if (!$transient) {
            $transient = array();
        }
        if (!isset($transient[ get_current_user_id() ])) {
            $transient[ get_current_user_id() ] = array();
        }
        $new_reservation_ids = array_diff($reservation_keys, $transient[ get_current_user_id() ]);
        $transient[ get_current_user_id() ] = $reservation_keys;
        set_transient('teambooking_seen_reservations', $transient);

        // Calling template
        $overview = new Admin\Overview();
        // Setting variables
        $overview->new_reservation_ids = $new_reservation_ids;

        // Render
        echo $overview->getPostBody();
    }

    public function buildAdminTabslots()
    {
        // Calling template
        $slot_view = new Admin\Slots();
        // Render
        echo $slot_view->getPostBody();
    }

    public function buildAdminTabpayments()
    {
        // Template call
        $content = new Admin\Payments();
        echo $content->getPostBody();
    }

    public function buildAdminTabpricing()
    {
        // Template call
        $content = new Admin\Promotion();
        echo $content->getPostBody();
    }

    public function buildAdminTabcustomers()
    {
        // Template call
        $content = new Admin\Customers();
        echo $content->getPostBody();
    }

    public function saveCoreSettings()
    {
        if (!Functions\isAdmin()) wp_die(__('You are not allowed to be on this page.', 'team-booking'));
        check_admin_referer('team_booking_options_verify');

        $roles = $this->fetchRoles();
        global $wp_roles;
        // Clean capability assignment
        foreach ($roles as $name => $role) {
            if ($name === 'administrator') continue;
            $wp_roles->remove_cap($name, 'tb_can_sync_calendar');
        }
        // Clean allowed timezone continents
        foreach (Functions\getSettings()->getContinentsAllowed() as $continent => $value) {
            Functions\getSettings()->setContinentAllowed($continent, FALSE);
        }

        foreach ($_POST as $setting => $value) {
            if ($setting === 'client_id') Functions\getSettings()->setApplicationClientId(trim(filter_input(INPUT_POST, 'client_id', FILTER_SANITIZE_STRING)));
            if ($setting === 'client_secret') Functions\getSettings()->setApplicationClientSecret(trim(filter_input(INPUT_POST, 'client_secret', FILTER_SANITIZE_STRING)));
            if ($setting === 'project_name') Functions\getSettings()->setApplicationProjectName(filter_input(INPUT_POST, 'project_name', FILTER_SANITIZE_STRING));
            if ($setting === 'registration_url') Functions\getSettings()->setRegistrationUrl(filter_input(INPUT_POST, 'registration_url', FILTER_SANITIZE_URL));
            if ($setting === 'login_url') Functions\getSettings()->setLoginUrl(filter_input(INPUT_POST, 'login_url', FILTER_SANITIZE_URL));
            if ($setting === 'database_reservation_timeout') Functions\getSettings()->setDatabaseReservationTimeout(filter_input(INPUT_POST, 'database_reservation_timeout', FILTER_SANITIZE_NUMBER_INT));
            if ($setting === 'autofill') Functions\getSettings()->setAutofillReservationForm(filter_input(INPUT_POST, 'autofill', FILTER_SANITIZE_STRING));
            if ($setting === 'first_month_automatic') Functions\getSettings()->setShowFirstMonthWithFreeSlot(filter_input(INPUT_POST, 'first_month_automatic'));
            if ($setting === 'drop_tables') Functions\getSettings()->setDropTablesOnUninstall(filter_input(INPUT_POST, 'drop_tables'));
            if ($setting === 'allow_cart') Functions\getSettings()->allowCart(filter_input(INPUT_POST, 'allow_cart'));
            if ($setting === 'cookie_policy') Functions\getSettings()->setCookiePolicy(filter_input(INPUT_POST, 'cookie_policy', FILTER_VALIDATE_INT, array('options' => array('min_range' => 0, 'max_range' => 2))));
            if ($setting === 'block_slots_in_cart') Functions\getSettings()->blockSlotsInCart(filter_input(INPUT_POST, 'block_slots_in_cart'));
            if ($setting === 'slots_in_cart_timeout') Functions\getSettings()->setSlotsInCartExpirationTime(filter_input(INPUT_POST, 'slots_in_cart_timeout', FILTER_VALIDATE_INT));
            if ($setting === 'show_ical') Functions\getSettings()->setShowIcal(filter_input(INPUT_POST, 'show_ical'));
            if ($setting === 'allow_slot_commands') Functions\getSettings()->allowSlotCommands(filter_input(INPUT_POST, 'allow_slot_commands'));
            if ($setting === 'batch_email_by_service') Functions\getSettings()->batchEmailByService(filter_input(INPUT_POST, 'batch_email_by_service'));
            if ($setting === 'order_redirect') Functions\getSettings()->setOrderRedirectRule(filter_input(INPUT_POST, 'order_redirect'));
            if ($setting === 'order_redirect_url') {
                $url = filter_input(INPUT_POST, 'order_redirect_url', FILTER_SANITIZE_URL);
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    Functions\getSettings()->setOrderRedirectUrl($url);
                }
            }
            if ($setting === 'gmaps_api_key') Functions\getSettings()->setGmapsApiKey(filter_input(INPUT_POST, 'gmaps_api_key', FILTER_SANITIZE_STRING));
            if ($setting === 'skip_gmaps') Functions\getSettings()->setSkipGmapLibs(filter_input(INPUT_POST, 'skip_gmaps'));
            if ($setting === 'redirect_back_after_login') Functions\getSettings()->setRedirectBackAfterLogin(filter_input(INPUT_POST, 'redirect_back_after_login'));
            if ($setting === 'max_pending_time') Functions\getSettings()->setMaxPendingTime(filter_input(INPUT_POST, 'max_pending_time', FILTER_VALIDATE_INT));
            if ($setting === 'roles_allowed') {
                // set new assignment (if present)
                if (NULL !== filter_input(INPUT_POST, 'roles_allowed', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY)) {
                    foreach (filter_input(INPUT_POST, 'roles_allowed', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) as $name => $role) {
                        $wp_roles->add_cap($name, 'tb_can_sync_calendar');
                    }
                }
            }
            if ($setting === 'continents_allowed') {
                if (NULL !== filter_input(INPUT_POST, 'continents_allowed', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY)) {
                    foreach (filter_input(INPUT_POST, 'continents_allowed', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) as $continent) {
                        Functions\getSettings()->setContinentAllowed($continent, TRUE);
                    }
                }
            }
        }

        Functions\getSettings()->save();

        exit(wp_redirect(add_query_arg('nag_updated', 1, static::add_params_to_admin_url(admin_url('admin.php')))));
    }

    public function saveCoworkerSettings()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        $form_data = $_POST['form_data'];
        parse_str($form_data, $form_data_parsed);
        Functions\getSettings()->updateCoworkerUrl($_POST['coworker_id'], trim($form_data_parsed['coworker_url']));
        $coworker_data = Functions\getSettings()->getCoworkerData($_POST['coworker_id']);
        if (isset($form_data_parsed['coworker_services']) && is_array($form_data_parsed['coworker_services'])) {
            $coworker_data->setAllowedServices($form_data_parsed['coworker_services']);
        } else {
            $coworker_data->setAllowedServices(array());
        }
        Functions\getSettings()->updateCoworkerData($coworker_data);
        Functions\getSettings()->save();
        echo Toolkit\wrapAjaxResponse(add_query_arg('nag_updated', 1, static::add_params_to_admin_url(admin_url('admin.php'))));
        exit;
    }

    public function editReservationData()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        $reservation = Database\Reservations::getById($_POST['reservation_id']);
        $fields = $reservation->getFormFields();
        $new_fields = get_object_vars(json_decode(str_replace("\\", '', $_POST['fields'])));
        foreach ($fields as $key => $field) {
            /** @var $field \TeamBooking_ReservationFormField */
            $field->setValue(Toolkit\filterInput(trim($new_fields[ $field->getName() ])));
        }
        $reservation->setFormFields($fields);
        if (isset($_POST['lang']) && !empty($_POST['lang'])) {
            $reservation->setFrontendLang($_POST['lang']);
        }
        Database\Reservations::update($reservation);
        echo Toolkit\wrapAjaxResponse('ok');
        exit;
    }

    public function bulkCSV()
    {
        if (!Functions\isAdminOrCoworker()) exit;
        check_admin_referer('team_booking_options_verify');
        if (isset($_GET['orders'])) {
            $orders = is_array($_GET['orders']) ? array_values($_GET['orders']) : explode(',', $_POST['orders']);
            if (isset($_POST['filename'])) {
                echo Files\generateCSVOrdersFile($orders, $_POST['filename']);
            } else {
                echo Files\generateCSVFile($orders);
            }
        } else {
            $reservation_ids = is_array($_GET['reservations']) ? array_values($_GET['reservations']) : explode(',', $_POST['reservations']);
            if (isset($_POST['filename'])) {
                echo Files\generateCSVFile(Database\Reservations::getByIds($reservation_ids), $_POST['filename']);
            } else {
                echo Files\generateCSVFile(Database\Reservations::getByIds($reservation_ids));
            }
        }

        exit;
    }

    public function bulkXLSX()
    {
        if (!Functions\isAdminOrCoworker()) exit;
        check_admin_referer('team_booking_options_verify');
        if (isset($_GET['orders'])) {
            $orders = is_array($_GET['orders']) ? array_values($_GET['orders']) : explode(',', $_POST['orders']);
            if (isset($_POST['filename'])) {
                echo Files\generateXLSXOrdersFile($orders, $_POST['filename']);
            } else {
                echo Files\generateXLSXOrdersFile($orders);
            }
        } else {
            $reservation_ids = is_array($_GET['reservations']) ? array_values($_GET['reservations']) : explode(',', $_POST['reservations']);
            if (isset($_POST['filename'])) {
                echo Files\generateXLSXFile(Database\Reservations::getByIds($reservation_ids), $_POST['filename']);
            } else {
                echo Files\generateXLSXFile(Database\Reservations::getByIds($reservation_ids));
            }
        }
        exit;
    }

    public function getCSV()
    {
        if (!Functions\isAdminOrCoworker()) exit;
        check_admin_referer('team_booking_options_verify');
        if (isset($_POST['orders'])) {
            echo Files\generateCSVOrdersFile();
        } else {
            echo Files\generateCSVFile();
        }
        exit;
    }

    public function getXLSX()
    {
        if (!Functions\isAdminOrCoworker()) exit;
        check_admin_referer('team_booking_options_verify');
        if (isset($_POST['orders'])) {
            echo Files\generateXLSXOrdersFile();
        } else {
            echo Files\generateXLSXFile();
        }
        exit;
    }

    public function getReservationPDF()
    {
        if (!Functions\isAdminOrCoworker()) exit;
        check_admin_referer('team_booking_options_verify');
        $reservation = Database\Reservations::getById($_POST['reservation_id']);
        echo Files\generateReservationPDF($reservation);
        exit;
    }

    public function getCustomersCSV()
    {
        if (!Functions\isAdminOrCoworker()) exit;
        check_admin_referer('team_booking_options_verify');
        echo Files\generateCSVClients();
        exit;
    }

    public function getCustomersXLSX()
    {
        if (!Functions\isAdminOrCoworker()) exit;
        check_admin_referer('team_booking_options_verify');
        echo Files\generateXLSXClients();
        exit;
    }

    public function getReservationDetails()
    {
        if (!Functions\isAdminOrCoworker()) exit;
        check_admin_referer('team_booking_options_verify');
        echo Toolkit\wrapAjaxResponse(Overview::getReservationDetailsModal($_POST['reservation_id']));
        exit;
    }

    public function sendEmailReminderManually()
    {
        if (!Functions\isAdminOrCoworker()) exit;
        check_admin_referer('team_booking_options_verify');
        $send = Functions\sendEmailReminderManually($_POST['reservation_id']);
        echo Toolkit\wrapAjaxResponse($send ? 'ok' : 'ko');
        exit;
    }

    public function sendEmailAgain()
    {
        if (!Functions\isAdminOrCoworker()) exit;
        check_admin_referer('team_booking_options_verify');
        $reservation = Database\Reservations::getById($_POST['reservation_id']);
        $type = $_POST['type'];
        if ($reservation instanceof \TeamBooking_ReservationData) {
            $process = new \TeamBooking_Reservation($reservation);
            if ($type === 'confirmation') {
                $process->sendConfirmationEmail(TRUE);
            }
            if ($type === 'cancellation') {
                $process->sendCancellationEmail();
            }
        }
        echo Toolkit\wrapAjaxResponse('ok');
        exit;
    }

    public function syncCalendar()
    {
        if (!Functions\isAdminOrCoworker()) exit;
        check_admin_referer('team_booking_options_verify');
        $result = Fetch\fromGoogle::fullSyncOf($_POST['calendar_id']);
        echo Toolkit\wrapAjaxResponse($result === TRUE ? 'ok' : $result);
        exit;
    }

    public function syncAllCalendars()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        $calendars = Functions\getSettings()->getCoworkerData($_POST['coworker'])->getCalendars();
        foreach ($calendars as $calendar) {
            if (Fetch\fromGoogle::fullSyncOf($calendar['calendar_id']) === 400) {
                $gcal_label = new Admin\Framework\TextLabel(__('This user must re-authorize', 'team-booking'));
                $gcal_label->setColor('red');
                echo Toolkit\wrapAjaxResponse($gcal_label->get());
                exit;
            }
        }
        $gcal_label = new Admin\Framework\TextLabel(__('ready', 'team-booking') . ' (' . count($calendars) . ')');
        $gcal_label->setColor('green');
        $gcal_label->setClass('tbk-calendars-state');
        echo Toolkit\wrapAjaxResponse($gcal_label->get());
        exit;
    }

    public function settingsBackup()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        echo Files\generateSettingsBackup();
        exit;
    }

    public function addNewService()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        $response = '';

        if (empty($_POST['id'])
            || Functions\checkServiceIdExistance($_POST['id'])
        ) {
            $response .= 'id_fail '; // end space mandatory
        }
        if (Functions\checkServiceNameExistance($_POST['name'])) {
            $response .= 'name_fail';
        }

        if (empty($response)) {
            if ($_POST['class'] === 'appointment') $service = Services\Factory::createAppointment($_POST['id'], $_POST['name']);
            if ($_POST['class'] === 'event') $service = Services\Factory::createEvent($_POST['id'], $_POST['name']);
            if ($_POST['class'] === 'unscheduled') $service = Services\Factory::createUnscheduled($_POST['id'], $_POST['name']);
            WPML\register_string_service_translation($service);
            WPML\register_string_service_email_translation($service);
            Database\Services::add($service);

            $response = 'ok';
        }
        echo Toolkit\wrapAjaxResponse(trim($response));
        exit;
    }

    public function importSettingsFromFile()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        $location = static::add_params_to_admin_url(admin_url('admin.php'));
        $settings_json = json_decode(file_get_contents($_FILES['settings_backup_file']['tmp_name']));

        if ($settings_json) {
            $settings_object = Functions\getSettings();
            $settings_object->inject_json(json_encode($settings_json));
            $version_on_file = $settings_object->getVersion();
            if ($version_on_file === TEAMBOOKING_VERSION) {
                // Replace settings
                update_option('team_booking', $settings_object);
                $location = add_query_arg('nag_settings_imported', 1, $location);
            } else {
                // Version mismatch
                $location = add_query_arg('nag_version_mismatch', 1, $location);
            }
        } else {
            // not the right file!
            $location = add_query_arg('nag_not_settings_file', 1, $location);
        }
        exit(wp_redirect($location));
    }

    public function repairDatabase()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        Update::repairDatabase();
        $location = static::add_params_to_admin_url(admin_url('admin.php'));
        echo Toolkit\wrapAjaxResponse(add_query_arg('nag_database_repaired', 1, $location));
        exit;
    }

    public function importGoogleProjectFromJSON()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        if (isset($_FILES['settings_json_file']['tmp_name'])) {
            $settings = json_decode(file_get_contents($_FILES['settings_json_file']['tmp_name']));
            if ($settings instanceof \stdClass) {
                if ($settings->web->client_id
                    && $settings->web->client_secret
                    && $settings->web->redirect_uris
                    && $settings->web->javascript_origins
                ) {
                    // URIs check
                    $redirect_uris = $settings->web->redirect_uris;
                    $uris_found = FALSE;
                    $js_origins = $settings->web->javascript_origins;
                    foreach ($redirect_uris as $redirect_uri) {
                        if ($redirect_uri == admin_url() . 'admin-ajax.php?action=teambooking_oauth_callback') {
                            $uris_found = TRUE;
                            break;
                        }
                    }
                    if ($uris_found) {
                        $uris_found = FALSE;
                        foreach ($js_origins as $js_origin) {
                            if ($js_origin == strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))) . '://' . $_SERVER['HTTP_HOST']) {
                                $uris_found = TRUE;
                                break;
                            }
                        }
                    }
                    if (!$uris_found) {
                        return 'uri mismatch';
                    }
                    Functions\getSettings()->setApplicationClientId($settings->web->client_id);
                    Functions\getSettings()->setApplicationClientSecret($settings->web->client_secret);
                    Functions\getSettings()->save();
                    $response = admin_url('admin.php?page=team-booking-general&nag_settings_imported=1');
                } else {
                    // Invalid file
                    $response = 'invalid file';
                }
            } else {
                // Invalid file
                $response = 'invalid file';
            }
        } else {
            $response = 'no file';
        }
        echo Toolkit\wrapAjaxResponse($response);
        exit;
    }

    /**
     * @param bool $apply_to_all_coworkers
     *
     * @return string
     * @throws \Exception
     */
    private function saveServiceSettings($apply_to_all_coworkers = FALSE)
    {
        try {
            $service = Database\Services::get($_POST['service_id']);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        ////////////////////////////
        // General settings part  //
        ////////////////////////////
        if ($_POST['service_settings'] === 'general' && Functions\isAdmin()) {
            foreach ($_POST['event'] as $setting => $value) {
                if ($setting === 'booking_type_name') $service->setName(filter_var($value, FILTER_SANITIZE_STRING));
                if ($setting === 'max_attendees') $service->setSlotMaxTickets(round(filter_var($value, FILTER_VALIDATE_INT)));
                if ($setting === 'max_tickets_per_reservation') $service->setSlotMaxUserTickets(min($service->getSlotMaxTickets(), round(filter_var($value, FILTER_VALIDATE_INT))));
                if ($setting === 'show_reservations_left') {
                    if ($value === 'under_threeshold') {
                        $service->setSettingsFor('show_tickets_left', TRUE);
                    } else {
                        $service->setSettingsFor('show_tickets_left_threeshold', 0);
                        $service->setSettingsFor('show_tickets_left', filter_var($value, FILTER_VALIDATE_BOOLEAN));
                    }
                }
                if ($setting === 'show_tickets_left_threeshold_value') {
                    $service->setSettingsFor('show_tickets_left_threeshold', filter_var($value, FILTER_VALIDATE_INT));
                }
                if ($setting === 'service_color') $service->setColor(filter_var($value, FILTER_SANITIZE_STRING));
                if ($setting === 'info') $service->setDescription($value); // late filtered
                if ($setting === 'direct_coworker') $service->setDirectCoworkerId(filter_var($value, FILTER_VALIDATE_INT));
                if ($setting === 'payment_must_be_done') $service->setSettingsFor('payment', filter_var($value, FILTER_SANITIZE_STRING));
                if ($setting === 'assignment_rule') $service->setSettingsFor('assignment_rule', filter_var($value, FILTER_SANITIZE_STRING));
                if ($setting === 'allow_reservation') $service->setSettingsFor('bookable', filter_var($value, FILTER_SANITIZE_STRING));
                if ($setting === 'max_reservations_logged_user') $service->setMaxReservationsUser(filter_var($value, FILTER_VALIDATE_INT));
                if ($setting === 'show_times') $service->setSettingsFor('show_times', $value);
                if ($setting === 'show_coworker') $service->setSettingsFor('show_coworker', filter_var($value, FILTER_VALIDATE_BOOLEAN));
                if ($setting === 'show_service_name') $service->setSettingsFor('show_service_name', filter_var($value, FILTER_VALIDATE_BOOLEAN));
                if ($setting === 'show_coworker_url') $service->setSettingsFor('show_coworker_url', filter_var($value, FILTER_VALIDATE_BOOLEAN));
                if ($setting === 'show_soldout') $service->setSettingsFor('show_soldout', filter_var($value, FILTER_VALIDATE_BOOLEAN));
                if ($setting === 'treat_discarded_free_slots') $service->setSettingsFor('treat_discarded_free_slots', filter_var($value, FILTER_SANITIZE_STRING));
                if ($setting === 'service_price') $service->setPrice(round($value, 2));
                if ($setting === 'duration_rule') $service->setSettingsFor('slot_duration', filter_var($value, FILTER_SANITIZE_STRING));
                if ($setting === 'default_duration') $service->setSlotDuration($value['hours'] * 3600 + $value['minutes'] * 60);
                if ($setting === 'location_setting') $service->setSettingsFor('location', filter_var($value, FILTER_SANITIZE_STRING));
                if ($setting === 'location_visibility') $service->setSettingsFor('location_visibility', filter_var($value, FILTER_SANITIZE_STRING));
                if ($setting === 'location_address') $service->setLocation(filter_var($value, FILTER_SANITIZE_STRING));
                if ($setting === 'show_slot_attendees') $service->setSettingsFor('show_attendees', filter_var($value, FILTER_SANITIZE_STRING));
                if ($setting === 'show_map') $service->setSettingsFor('show_map', filter_var($value, FILTER_VALIDATE_BOOLEAN));
                if ($setting === 'redirect') $service->setSettingsFor('redirect', filter_var($value, FILTER_VALIDATE_BOOLEAN));
                if ($setting === 'redirect_url') $service->setRedirectUrl(trim(filter_var($value, FILTER_SANITIZE_URL)));
                if ($setting === 'approve_rule') $service->setSettingsFor('approval_rule', filter_var($value, FILTER_SANITIZE_STRING));
                if ($setting === 'approve_until') $service->setSettingsFor('free_until_approval', filter_var($value, FILTER_VALIDATE_BOOLEAN));
                if ($setting === 'allow_customer_cancellation') $service->setSettingsFor('customer_cancellation', filter_var($value, FILTER_VALIDATE_BOOLEAN));
                if ($setting === 'allow_customer_cancellation_reason') $service->setSettingsFor('cancellation_reason_allowed', filter_var($value, FILTER_VALIDATE_BOOLEAN));
                if ($setting === 'allow_customer_cancellation_timespan') {
                    $service->setSettingsFor('cancellation_allowed_until',
                        (int)$value['minutes'] * MINUTE_IN_SECONDS
                        + (int)$value['hours'] * HOUR_IN_SECONDS
                        + (int)$value['days'] * DAY_IN_SECONDS
                    );
                }
            }
            WPML\register_string_service_translation($service);
        }
        ////////////////////////////
        // Email settings part    //
        ////////////////////////////
        if ($_POST['service_settings'] === 'email') {
            if (Functions\isAdmin()) {
                if ($service->getClass() !== 'unscheduled') {
                    $service->setEmailReminder('send', isset($_POST['event']['email_for_reminder']));
                    if (isset($_POST['event']['email_for_reminder_timeframe'])) {
                        $service->setEmailReminder('days_before', $_POST['event']['email_for_reminder_timeframe']);
                    }
                    $service->setEmailReminder('subject', $_POST['event']['reminder_email']['subject']);
                    $service->setEmailReminder('body', $_POST['event']['reminder_email']['body']);
                    $service->setEmailReminder('from', $_POST['event']['email_to_customer_sender'] === 'coworker' ? 'coworker' : 'admin');
                }
                $service->setEmailToAdmin('to', $_POST['event']['email_for_notifications']);
                $service->setEmailToAdmin('send', isset($_POST['event']['email_to_email']));
                $service->setEmailToAdmin('subject', $_POST['event']['back_end_email']['subject']);
                $service->setEmailToAdmin('body', $_POST['event']['back_end_email']['body']);
                $service->setEmailToCustomer('send', isset($_POST['event']['email_to_customer']));
                $service->setEmailToCustomer('subject', $_POST['event']['front_end_email']['subject']);
                $service->setEmailToCustomer('body', $_POST['event']['front_end_email']['body']);
                $service->setEmailToCustomer('from', $_POST['event']['email_to_customer_sender'] === 'coworker' ? 'coworker' : 'admin');
                if ($_POST['event']['include_files_as_attachment'] == TRUE) {
                    $service->setEmailToAdmin('attachments', TRUE);
                } else {
                    $service->setEmailToAdmin('attachments', FALSE);
                }
                $service->setEmailCancellationToCustomer('send', isset($_POST['event']['send_cancellation_email']));
                $service->setEmailCancellationToCustomer('subject', $_POST['event']['cancellation_email']['subject']);
                $service->setEmailCancellationToCustomer('body', $_POST['event']['cancellation_email']['body']);
                $service->setEmailCancellationToCustomer('from', $_POST['event']['email_to_customer_sender'] === 'coworker' ? 'coworker' : 'admin');
                $service->setEmailCancellationToAdmin('send', isset($_POST['event']['send_cancellation_email_backend']));
                $service->setEmailCancellationToAdmin('subject', $_POST['event']['cancellation_email_backend']['subject']);
                $service->setEmailCancellationToAdmin('body', $_POST['event']['cancellation_email_backend']['body']);
                WPML\register_string_service_email_translation($service);
            }
            $this->saveCoworkerServiceSettings(TRUE, $apply_to_all_coworkers);
        }

        Database\Services::add($service);
        exit(wp_redirect(add_query_arg('nag_event_updated', 1, static::add_params_to_admin_url(admin_url('admin.php')))));
    }

    public function savePaymentsSettings()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        if (isset($_POST['currency_code']) && !empty($_POST['currency_code'])) {
            Functions\getSettings()->setCurrencyCode($_POST['currency_code']);
        }
        if (isset($_POST['gateway_settings'])) {
            foreach ($_POST['gateway_settings'] as $gateway_id => $settings) {
                $object = Functions\getSettings()->getPaymentGatewaySettingObject($gateway_id);
                if ($object instanceof \TeamBooking_PaymentGateways_Settings) {
                    $object->saveBackendSettings($settings, Functions\getSettings()->getCurrencyCode());
                }
            }
        }
        Functions\getSettings()->save();
        exit(wp_redirect(add_query_arg('nag_updated', 1, static::add_params_to_admin_url(admin_url('admin.php')))));
    }

    public function deleteService()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        Database\Services::delete($_POST['booking_id']);
        $coworkers = Functions\getCoworkersIdList();
        foreach ($coworkers as $coworker) {
            Functions\getSettings()->getCoworkerData($coworker)->dropCustomServiceSettings($_POST['booking_id']);
        }
        Functions\getSettings()->save();
        echo Toolkit\wrapAjaxResponse(add_query_arg('nag_event_deleted', 1, static::add_params_to_admin_url(admin_url('admin.php'))));
        exit;
    }

    public function cleanErrorLogs()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        $error_logs = array();
        Functions\getSettings()->setErrorLogs($error_logs);
        Functions\getSettings()->save();
        echo Toolkit\wrapAjaxResponse(add_query_arg('nag_error_logs_cleaned', 1, static::add_params_to_admin_url(admin_url('admin.php'))));
        exit;
    }

    public function deleteServiceReservation()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        // TODO: cancel
        $reservation = Database\Reservations::getById($_POST['reservation_id']);
        $reservation->removeFiles();
        Database\Reservations::delete($_POST['reservation_id']);
        echo Toolkit\wrapAjaxResponse(static::add_params_to_admin_url(admin_url('admin.php')));
        exit;
    }

    public function approveServiceReservation()
    {
        if (!Functions\isAdminOrCoworker()) exit;
        check_admin_referer('team_booking_options_verify');
        $reservation_db_record = Database\Reservations::getById($_POST['reservation_id']);
        $location = static::add_params_to_admin_url(admin_url('admin.php'));

        try {
            $service = Database\Services::get($reservation_db_record->getServiceId());
            if (Functions\isAdmin()
                ||
                ($service->getSettingsFor('approval_rule') === 'coworker'
                    && get_current_user_id() == $reservation_db_record->getCoworker()
                )
            ) {
                $reservation = new \TeamBooking_Reservation($reservation_db_record);
                $updated_record = $reservation->doReservation();
                if ($updated_record instanceof \TeamBooking_ReservationData) {
                    /**
                     * Everything went fine, let's update the database record
                     */
                    $updated_record->setStatusConfirmed();
                    Database\Reservations::update($updated_record);

                    // Send e-mail messages
                    if ($service->getSettingsFor('approval_rule') === 'admin'
                        && Functions\getSettings()->getCoworkerData($updated_record->getCoworker())->getCustomEventSettings($updated_record->getServiceId())->getGetDetailsByEmail()
                    ) {
                        $reservation->sendNotificationEmailToCoworker();
                    }
                    if ($updated_record->getCustomerEmail() && $reservation->getServiceObj()->getEmailToCustomer('send')) {
                        $reservation->sendConfirmationEmail();
                    }
                    $location = add_query_arg('nag_reservation_approved', 1, $location);
                } elseif ($updated_record instanceof \TeamBooking_Error) {
                    /**
                     * Something goes wrong
                     */
                    $message = $updated_record->getMessage();
                    $location = 'ERROR: ' . $message;
                } else {
                    /**
                     * Not an Appointment, nor Event
                     */
                    $location = add_query_arg('nag_not_approvable', 1, $location);
                }
                $return = $location;
            } else {
                $return = add_query_arg('nag_generic_error', 'unauthorized', $location);
            }
        } catch (\Exception $e) {
            $return = add_query_arg('nag_generic_error', urlencode($e->getMessage()), $location);
        }
        echo Toolkit\wrapAjaxResponse($return);
        exit;
    }

    public function confirmPendingPaymentReservation()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        $reservation_db_record = Database\Reservations::getById($_POST['reservation_id']);
        $reservation = new \TeamBooking_Reservation($reservation_db_record);
        $updated_record = $reservation->doReservation();
        if ($updated_record instanceof \TeamBooking_ReservationData) {
            /**
             * Everything went fine, let's update the database record
             */
            $updated_record->setStatusConfirmed();
            if ($_POST['set_as_paid'] === 'true') {
                $updated_record->setPaid(TRUE);
            } else {
                $updated_record->setPaid(FALSE);
            }
            Database\Reservations::update($updated_record);

            // Send e-mail messages
            if ($reservation->getServiceObj()->getEmailToAdmin('send')) {
                $reservation->sendNotificationEmail();
            }
            if (Functions\getSettings()->getCoworkerData($updated_record->getCoworker())->getCustomEventSettings($updated_record->getServiceId())->getGetDetailsByEmail()) {
                $reservation->sendNotificationEmailToCoworker();
            }
            if ($updated_record->getCustomerEmail() && $reservation->getServiceObj()->getEmailToCustomer('send')) {
                $reservation->sendConfirmationEmail();
            }

            $location = admin_url('admin.php?page=team-booking&nag_reservation_confirmed=1');
        } elseif ($updated_record instanceof \TeamBooking_Error) {
            /**
             * Something goes wrong
             */
            $message = $updated_record->getMessage();
            $location = 'ERROR: ' . $message;
        } else {
            /**
             * Not an Appointment, nor Event
             */
            $location = admin_url('admin.php?page=team-booking&not_approvable=1');
        }
        echo Toolkit\wrapAjaxResponse($location);
        exit;
    }

    public function cancelServiceReservation()
    {
        if (!Functions\isAdminOrCoworker()) exit;
        check_admin_referer('team_booking_options_verify');
        $url = static::add_params_to_admin_url(admin_url('admin.php'));
        $reservation_db_record = Database\Reservations::getById($_POST['reservation_id']);
        if (!$reservation_db_record) {
            $url = add_query_arg('nag_generic_error', 1, $url);
            echo Toolkit\wrapAjaxResponse($url);
            exit;
        }
        if (!Functions\isAdmin()) {
            try {
                if ($reservation_db_record->getCoworker() != get_current_user_id()
                    || Database\Services::get($reservation_db_record->getServiceId())->getSettingsFor('approval_rule') === 'admin'
                ) {
                    $url = add_query_arg('nag_not_revokable', 1, $url);
                    echo Toolkit\wrapAjaxResponse($url);
                    exit;
                }
            } catch (\Exception $e) {
                $url = add_query_arg('nag_not_revokable', $e->getMessage(), $url);
                echo Toolkit\wrapAjaxResponse($url);
                exit;
            }
        }
        $reservation = new \TeamBooking_Reservation($reservation_db_record);
        $updated_record = $reservation->cancelReservation($_POST['reservation_id'], get_current_user_id());
        if ($updated_record instanceof \TeamBooking_ReservationData) {
            /**
             * Everything went fine, let's update the database record
             */
            Database\Reservations::update($updated_record);
            $url = add_query_arg('nag_reservation_cancelled', 1, $url);
        } elseif ($updated_record instanceof \TeamBooking_Error) {
            /**
             * Something goes wrong
             */
            if ($updated_record->getCode() == 7) {
                /*
                 * The reservation is already cancelled, let's update the database record
                 */
                $reservation_db_record->setStatusCancelled();
                Database\Reservations::update($reservation_db_record);
            }
            $message = urlencode($updated_record->getMessage());
            $url = add_query_arg('nag_generic_error', $message, $url);
        } else {
            /**
             * Not an Appointment, nor Event
             */
            $url = add_query_arg('nag_not_revokable', 1, $url);
        }
        echo Toolkit\wrapAjaxResponse($url);
        exit;
    }

    public function deleteSelectedServices()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        $services = json_decode(str_replace("\\", '', $_POST['services']));
        $coworkers = Functions\getCoworkersIdList();
        foreach ($services as $service_id) {
            Database\Services::delete($service_id);
            foreach ($coworkers as $coworker) {
                Functions\getSettings()->getCoworkerData($coworker)->dropCustomServiceSettings($service_id);
            }
        }
        Functions\getSettings()->save();
        echo Toolkit\wrapAjaxResponse(add_query_arg('nag_selected_services_deleted', 1, static::add_params_to_admin_url(admin_url('admin.php'))));
        exit;
    }

    public function deleteAllReservations()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        Database\Reservations::deleteAll();
        echo Toolkit\wrapAjaxResponse(static::add_params_to_admin_url(admin_url('admin.php')));
        exit;
    }

    public function addGoogleCalendar()
    {
        if (!Functions\isAdminOrCoworker()) exit;
        check_admin_referer('team_booking_options_verify');
        $coworker = Functions\getSettings()->getCoworkerData(get_current_user_id());
        /**
         * Let's test if the Google Calendar ID belongs to
         * the authorized Google Account
         *
         * Even if the IDs are already picked in the right Google Account,
         * this second step provides a better security level.
         */
        $calendar = new Calendar();
        $test = $calendar->testCalendarID(get_current_user_id(), trim($_POST['calendar_id']));
        if ($test === FALSE) {
            $return = 404;
        } else {
            $coworker->addCalendarId(trim($_POST['calendar_id']));
            Functions\getSettings()->save();
            Fetch\fromGoogle::fullSyncOf($_POST['calendar_id']);

            $return = add_query_arg('nag_custom_settings_updated', 1, static::add_params_to_admin_url(admin_url('admin.php')));
        }
        echo Toolkit\wrapAjaxResponse($return);
        exit;
    }

    public function removeGoogleCalendar()
    {
        if (!Functions\isAdminOrCoworker()) exit;
        check_admin_referer('team_booking_options_verify');
        $coworker = Functions\getSettings()->getCoworkerData(get_current_user_id());
        if ($coworker->dropCalendarId(trim($_POST['calendar_id_key']))) {
            Database\Events::removeCalendar(trim($_POST['calendar_id_key']));
        }
        Functions\getSettings()->save();
        echo Toolkit\wrapAjaxResponse(add_query_arg('nag_custom_settings_updated', 1, static::add_params_to_admin_url(admin_url('admin.php'))));
        exit;
    }

    public function cleanGoogleCalendar()
    {
        if (!Functions\isAdminOrCoworker()) exit;
        check_admin_referer('team_booking_options_verify');
        \TeamBooking\Fetch\fromGoogle::deletePastEventsOf(trim($_POST['calendar_id_key']));
        echo Toolkit\wrapAjaxResponse(add_query_arg('nag_custom_settings_updated', 1, static::add_params_to_admin_url(admin_url('admin.php'))));
        exit;
    }

    public function removeCoworkerResidualData()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        Database\Events::removeCoworker($_POST['coworker_id']);
        Functions\getSettings()->dropCoworkerData($_POST['coworker_id']);
        Functions\getSettings()->save();
        echo Toolkit\wrapAjaxResponse(add_query_arg('nag_updated', 1, static::add_params_to_admin_url(admin_url('admin.php'))));
        exit;
    }

    /**
     * @param bool|int $coworker_id
     *
     * @return string
     */
    private function revokeCoworker($coworker_id = FALSE)
    {
        $location = static::add_params_to_admin_url(admin_url('admin.php'));
        if (!$coworker_id) {
            $coworker = Functions\getSettings()->getCoworkerData(get_current_user_id());
        } else {
            $coworker = Functions\getSettings()->getCoworkerData($coworker_id);
        }
        // Prepare for programmatically revoke token
        if (isset(json_decode($coworker->getAccessToken())->refresh_token)) {
            $token = json_decode($coworker->getAccessToken())->refresh_token;
            // Revoke token
            $calendar_class = new Calendar();
            $revoke_attempt = $calendar_class->revokeToken($token);
            if ($revoke_attempt !== TRUE) {
                if ($revoke_attempt === FALSE) {
                    $location = add_query_arg('nag_partialreset', 1, $location);
                } else {
                    return add_query_arg('nag_reset_failed', urlencode($revoke_attempt), $location);
                }
            } else {
                $location = add_query_arg('nag_reset', 1, $location);
            }
        } else {
            $location = add_query_arg('nag_partialreset', 1, $location);
        }
        // Erase Access Token
        $coworker->setAccessToken('');
        $coworker->setAuthAccount('');
        // Remove Google Calendar ID(s)
        $coworker->dropAllCalendarIds();
        // Remove the events from database
        Database\Events::removeCoworker($coworker_id);
        Functions\getSettings()->save();

        return $location;
    }

    /**
     * @param bool $email
     * @param bool $apply_to_all
     *
     * @return mixed
     * @throws \Exception
     */
    private function saveCoworkerServiceSettings($email = FALSE, $apply_to_all = FALSE)
    {
        $location = static::add_params_to_admin_url(admin_url('admin.php'), TRUE);
        $coworker_data = Functions\getSettings()->getCoworkerData(get_current_user_id());
        $settings = $coworker_data->getCustomEventSettings($_POST['service_id']);
        /////////////////
        // Email part  //
        /////////////////
        if ($email) {
            $settings->setGetDetailsByEmail((int)$_POST['data']['get_details_by_email']);
            $settings->setIncludeFilesAsAttachment((int)$_POST['data']['include_uploaded_files_as_attachment']);
            $settings->setNotificationEmailBody($_POST['data']['email']['email_text']['body']);
            $settings->setNotificationEmailSubject($_POST['data']['email']['email_text']['subject']);
            $location = add_query_arg('nag_event_updated', 1, $location);
            if ($apply_to_all) {
                $coworkers = Functions\getCoworkersIdList();
                foreach ($coworkers as $coworker_id) {
                    if ($coworker_id === get_current_user_id()) continue;
                    $loop_coworker_data = Functions\getSettings()->getCoworkerData($coworker_id);
                    $loop_settings = $loop_coworker_data->getCustomEventSettings($_POST['service_id']);
                    $loop_settings->setGetDetailsByEmail((int)$_POST['data']['get_details_by_email']);
                    $loop_settings->setIncludeFilesAsAttachment((int)$_POST['data']['include_uploaded_files_as_attachment']);
                    $loop_settings->setNotificationEmailBody($_POST['data']['email']['email_text']['body']);
                    $loop_settings->setNotificationEmailSubject($_POST['data']['email']['email_text']['subject']);
                    $loop_coworker_data->setCustomEventSettings($loop_settings, $_POST['service_id']);
                    Functions\getSettings()->updateCoworkerData($loop_coworker_data);
                }
            }
        }
        ///////////////////////////
        // Google Calendar part  //
        ///////////////////////////
        if (!$email) {
            $services_id_list = Functions\getSettings()->getServiceIdList(TRUE);
            $other_linked_event_titles = array();
            $other_booked_event_titles = array();
            foreach ($services_id_list as $id) {
                if ($id !== $_POST['service_id']) {
                    $other_linked_event_titles[ $id ] = strtolower($coworker_data->getCustomEventSettings($id)->getLinkedEventTitle());
                    $other_booked_event_titles[ $id ] = strtolower($coworker_data->getCustomEventSettings($id)->getAfterBookedTitle());
                }
            }
            $linked_event_title_lowercase = strtolower($_POST['data']['linked_event_title']);
            $booked_event_title_lowercase = strtolower($_POST['data']['booked_title']);

            // Checks for duplicate event titles or not allowed strings
            if (in_array($linked_event_title_lowercase, $other_linked_event_titles)) {
                $service_id = array_search($linked_event_title_lowercase, $other_linked_event_titles);
                try {
                    $service = Database\Services::get($service_id);
                    $location = add_query_arg('nag_service_name', $service->getName(), $location);
                } catch (\Exception $e) {
                    $location = add_query_arg('nag_service_name', $service_id, $location);
                }
                $location = add_query_arg('nag_title_value', $_POST['data']['linked_event_title'], $location);
                $location = add_query_arg('nag_duplicated_linked_title', 1, $location);
                exit(wp_redirect($location));
            }
            if (in_array($booked_event_title_lowercase, $other_booked_event_titles)) {
                $service_id = array_search($booked_event_title_lowercase, $other_booked_event_titles);
                try {
                    $service = Database\Services::get($service_id);
                    $location = add_query_arg('nag_service_name', $service->getName(), $location);
                } catch (\Exception $e) {
                    $location = add_query_arg('nag_service_name', $service_id, $location);
                }
                $location = add_query_arg('nag_title_value', $_POST['data']['booked_title'], $location);
                $location = add_query_arg('nag_duplicated_booked_title', 1, $location);
                exit(wp_redirect($location));
            }
            if (strpos($linked_event_title_lowercase, '||') !== FALSE
                || strpos($linked_event_title_lowercase, '>>') !== FALSE) {
                exit(wp_redirect(add_query_arg('nag_event_title_not_allowed', 1, $location)));
            }

            if (isset($_POST['data']['booked_title'])) {
                if (strpos($_POST['data']['booked_title'], '||') !== FALSE
                    || strpos($_POST['data']['booked_title'], '>>') !== FALSE) {
                    exit(wp_redirect(add_query_arg('nag_event_title_not_allowed', 1, $location)));
                }
                $settings->setAfterBookedTitle($_POST['data']['booked_title']);
            }
            $additional_event_title_data = $settings->getAdditionalEventTitleData();
            $additional_event_title_data['customer']['full_name'] = isset($_POST['data']['booked_title_additional_data_customer_name']);
            $additional_event_title_data['customer']['email'] = isset($_POST['data']['booked_title_additional_data_customer_email']);
            $additional_event_title_data['customer']['phone'] = isset($_POST['data']['booked_title_additional_data_customer_phone']);
            $settings->setAdditionalEventTitleData($additional_event_title_data);
            $settings->setBookedEventColor($_POST['data']['booked_color']);
            if (isset($_POST['data']['duration_rule'])) {
                $settings->setDurationRule($_POST['data']['duration_rule']);
            }
            if (isset($_POST['data']['default_duration'])) {
                $fixed_duration = $_POST['data']['default_duration']['hours'] * HOUR_IN_SECONDS + $_POST['data']['default_duration']['minutes'] * MINUTE_IN_SECONDS;
                $settings->setFixedDuration($fixed_duration);
            }
            if (isset($_POST['data']['buffer'])) {
                $buffer_duration = $_POST['data']['buffer']['hours'] * HOUR_IN_SECONDS + $_POST['data']['buffer']['minutes'] * MINUTE_IN_SECONDS;
                $settings->setBufferDuration($buffer_duration);
            }
            if (isset($_POST['data']['buffer_rule'])) {
                $settings->setBufferDurationRule($_POST['data']['buffer_rule']);
            }
            $settings->setLinkedEventTitle($_POST['data']['linked_event_title']);
            $settings->setMinTime($_POST['data']['min_time']);
            $settings->setOpenTime($_POST['data']['open_time']);
            if ($_POST['data']['min_time_reference'] === 'end') {
                $settings->setMinTimeReferenceEnd();
            } else {
                $settings->setMinTimeReferenceStart();
            }
            $settings->setReminder($_POST['data']['reminder']);
            $settings->addCustomerAsGuest($_POST['data']['add_customer_as_guest']);
            $settings->setEventDescriptionContent((int)$_POST['data']['event_description_content']);
            $settings->setDealWithUnrelatedEvents((int)$_POST['data']['deal_with_unrelated_events']);
            $settings->setDealWithBookedOfSameService((int)$_POST['data']['deal_with_booked_same']);
            $settings->setDealWithBookedOfOtherServices((int)$_POST['data']['deal_with_booked_others']);
            $location = add_query_arg('nag_event_updated', 1, $location);

            if ($apply_to_all) {
                $coworkers = Functions\getCoworkersIdList();
                foreach ($coworkers as $coworker_id) {
                    if ($coworker_id === get_current_user_id()) continue;
                    $loop_coworker_data = Functions\getSettings()->getCoworkerData($coworker_id);
                    $loop_settings = $loop_coworker_data->getCustomEventSettings($_POST['service_id']);
                    if (isset($_POST['data']['booked_title'])) {
                        $loop_settings->setAfterBookedTitle($_POST['data']['booked_title']);
                    }
                    $additional_event_title_data = $loop_settings->getAdditionalEventTitleData();
                    $additional_event_title_data['customer']['full_name'] = isset($_POST['data']['booked_title_additional_data_customer_name']);
                    $additional_event_title_data['customer']['email'] = isset($_POST['data']['booked_title_additional_data_customer_email']);
                    $additional_event_title_data['customer']['phone'] = isset($_POST['data']['booked_title_additional_data_customer_phone']);
                    $loop_settings->setAdditionalEventTitleData($additional_event_title_data);
                    $loop_settings->setBookedEventColor($_POST['data']['booked_color']);
                    if (isset($_POST['data']['duration_rule'])) {
                        $loop_settings->setDurationRule($_POST['data']['duration_rule']);
                    }
                    if (isset($fixed_duration)) $loop_settings->setFixedDuration($fixed_duration);
                    if (isset($buffer_duration)) $loop_settings->setBufferDuration($buffer_duration);
                    $loop_settings->setLinkedEventTitle($_POST['data']['linked_event_title']);
                    $loop_settings->setMinTime($_POST['data']['min_time']);
                    $loop_settings->setOpenTime($_POST['data']['open_time']);
                    if ($_POST['data']['min_time_reference'] === 'end') {
                        $loop_settings->setMinTimeReferenceEnd();
                    } else {
                        $loop_settings->setMinTimeReferenceStart();
                    }
                    $loop_settings->setReminder($_POST['data']['reminder']);
                    $loop_settings->addCustomerAsGuest($_POST['data']['add_customer_as_guest']);
                    $loop_settings->setEventDescriptionContent((int)$_POST['data']['event_description_content']);
                    $loop_settings->setDealWithUnrelatedEvents((int)$_POST['data']['deal_with_unrelated_events']);
                    $loop_settings->setDealWithBookedOfSameService((int)$_POST['data']['deal_with_booked_same']);
                    $loop_settings->setDealWithBookedOfOtherServices((int)$_POST['data']['deal_with_booked_others']);
                    $loop_coworker_data->setCustomEventSettings($loop_settings, $_POST['service_id']);
                    Functions\getSettings()->updateCoworkerData($loop_coworker_data);
                }
            }
        }
        $coworker_data->setCustomEventSettings($settings, $_POST['service_id']);
        /**
         * We're using the update method, even if it's an
         * object, because if the settings are created just now,
         * then won't be saved (can't be used by reference)
         */
        Functions\getSettings()->updateCoworkerData($coworker_data);
        Functions\getSettings()->save();
        if ($email) {
            return $location;
        } else {
            exit(wp_redirect($location));
        }
    }

    public function saveFrontendStyle()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        if (empty($_POST['tb-background-color'])) {
            $_POST['tb-background-color'] = '#FFFFFF';
        }
        if (empty($_POST['tb-weekline-color'])) {
            $_POST['tb-weekline-color'] = '#FFFFFF';
        }
        if (empty($_POST['tb-freeslot-color'])) {
            $_POST['tb-freeslot-color'] = '#FFFFFF';
        }
        if (empty($_POST['tb-soldoutslot-color'])) {
            $_POST['tb-freeslot-color'] = '#FFFFFF';
        }
        if ($_POST['tb-group-slots-by'] === 'bytime') {
            Functions\getSettings()->setGroupSlotsByTime();
        }
        if ($_POST['tb-group-slots-by'] === 'bycoworker') {
            Functions\getSettings()->setGroupSlotsByCoworker();
        }
        if ($_POST['tb-group-slots-by'] === 'byservice') {
            Functions\getSettings()->setGroupSlotsByService();
        }
        Functions\getSettings()->setColorBackground($_POST['tb-background-color']);
        Functions\getSettings()->setColorWeekLine($_POST['tb-weekline-color']);
        if (isset($_POST['tb-weekline-pattern'])) {
            Functions\getSettings()->setPatternWeekline($_POST['tb-weekline-pattern']);
        }
        if (isset($_POST['tb-calendar-pattern'])) {
            Functions\getSettings()->setPatternCalendar($_POST['tb-calendar-pattern']);
        }
        if (isset($_POST['tb-border-size'])) {
            Functions\getSettings()->setBorderSize($_POST['tb-border-size']);
        }
        if (isset($_POST['tb-border-radius'])) {
            Functions\getSettings()->setBorderRadius($_POST['tb-border-radius']);
        }
        if (isset($_POST['tb-border-color'])) {
            Functions\getSettings()->setBorderColor($_POST['tb-border-color']);
        }
        if (isset($_POST['tb-css-fix'])) {
            Functions\getSettings()->setFix62dot5($_POST['tb-css-fix']);
        }
        if (isset($_POST['tb-template-vpages'])) {
            Functions\getSettings()->setTemplateVPages(trim($_POST['tb-template-vpages']));
        }
        Functions\getSettings()->setMapStyle($_POST['tb-map-style']);
        Functions\getSettings()->setMapStyleUseDefault($_POST['tb-map-use-default']);
        Functions\getSettings()->setGmapsZoomLevel($_POST['tb-map-zoom']);
        Functions\getSettings()->setColorFreeSlot($_POST['tb-freeslot-color']);
        Functions\getSettings()->setColorSoldoutSlot($_POST['tb-soldoutslot-color']);
        Functions\getSettings()->setPriceTagColor($_POST['tb-price-tag-color']);
        Functions\getSettings()->setSlotStyle($_POST['tb-slots-style']);
        Functions\getSettings()->setNumberedDotsLogic($_POST['tb-numbered-dots-logic']);
        Functions\getSettings()->setNumberedDotsLowerBound($_POST['tb-numbered-dots-lower-bound']);
        Functions\getSettings()->save();

        exit(wp_redirect(add_query_arg('nag_updated', 1, static::add_params_to_admin_url(admin_url('admin.php')))));
    }

    public function createApiToken()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        Functions\getSettings()->addToken(filter_input(INPUT_POST, 'write', FILTER_VALIDATE_BOOLEAN));
        Functions\getSettings()->save();
        echo Toolkit\wrapAjaxResponse(add_query_arg('nag_token_added', 1, static::add_params_to_admin_url(admin_url('admin.php'))));
        exit;
    }

    public function revokeAuthToken()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        echo Toolkit\wrapAjaxResponse($this->revokeCoworker($_POST['coworker_id']));
        exit;
    }

    public function revokePersonalAuthToken()
    {
        if (!Functions\isAdminOrCoworker()) exit;
        check_admin_referer('team_booking_options_verify');
        echo Toolkit\wrapAjaxResponse($this->revokeCoworker());
        exit;
    }

    public function revokeApiToken()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        Functions\getSettings()->revokeToken($_POST['token']);
        Functions\getSettings()->save();
        echo Toolkit\wrapAjaxResponse(add_query_arg('nag_token_removed', 1, static::add_params_to_admin_url(admin_url('admin.php'))));
        exit;
    }

    public function resetEnumerableReservations()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        $reservations = Database\Reservations::getByService($_POST['service_id']);
        foreach ($reservations as $reservation) {
            if ($reservation->isEnumerableForCustomerLimits()
                && $reservation->getCustomerUserId() == $_POST['customer_id']
            ) {
                $reservation->setEnumerableForCustomerLimits(FALSE);
                Database\Reservations::update($reservation);
            }
        }
        echo Toolkit\wrapAjaxResponse('ok');
        exit;
    }

    public function addServiceCustomField()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        try {
            $service = Database\Services::get($_POST['service_id']);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        $service_fields = Database\Forms::get($service->getForm());
        $service_fields_hooks = array_keys($service_fields);
        $return = '';
        switch ($_POST['field']) {
            case 'text':
                $hook = $_POST['service_id'] . '_custom_text';
                $i = 0;
                while (in_array($hook, $service_fields_hooks)) {
                    $i++;
                    $hook .= '_' . $i;
                }
                $field = Factory::createTextField($hook);
                $field->setTitle(__('A new custom text field', 'team-booking'));
                break;
            case 'textarea':
                $hook = $_POST['service_id'] . '_custom_textarea';
                $i = 0;
                while (in_array($hook, $service_fields_hooks)) {
                    $i++;
                    $hook .= '_' . $i;
                }
                $field = Factory::createTextArea($hook);
                $field->setTitle(__('A new custom textarea', 'team-booking'));
                break;
            case 'select':
                $hook = $_POST['service_id'] . '_custom_select';
                $i = 0;
                while (in_array($hook, $service_fields_hooks)) {
                    $i++;
                    $hook .= '_' . $i;
                }
                $field = Factory::createSelect($hook);
                $field->setTitle(__('A new custom select', 'team-booking'));
                break;
            case 'checkbox':
                $hook = $_POST['service_id'] . '_custom_checkbox';
                $i = 0;
                while (in_array($hook, $service_fields_hooks)) {
                    $i++;
                    $hook .= '_' . $i;
                }
                $field = Factory::createCheckbox($hook);
                $field->setTitle(__('A new custom checkbox', 'team-booking'));
                $field->setData('checked', FALSE);
                $field->setData('value', $field->getTitle());
                break;
            case 'radio':
                $hook = $_POST['service_id'] . '_custom_radio';
                $i = 0;
                while (in_array($hook, $service_fields_hooks)) {
                    $i++;
                    $hook .= '_' . $i;
                }
                $field = Factory::createRadio($hook);
                $field->setTitle(__('A new custom radio group', 'team-booking'));
                break;
            case 'file':
                $hook = $_POST['service_id'] . '_custom_file';
                $i = 0;
                while (in_array($hook, $service_fields_hooks)) {
                    $i++;
                    $hook .= '_' . $i;
                }
                $field = Factory::createFileUpload($hook);
                $field->setTitle(__('A new file upload field', 'team-booking'));
                break;
            case 'paragraph':
                $hook = $_POST['service_id'] . '_custom_paragraph';
                $i = 0;
                while (in_array($hook, $service_fields_hooks)) {
                    $i++;
                    $hook .= '_' . $i;
                }
                $field = Factory::createParagraph($hook);
                $field->setTitle(__('A new paragraph', 'team-booking'));
                break;
            default :
                $return = TRUE;
        }

        if ($field instanceof Abstracts\FormElement) {
            Database\Forms::addElement($service->getForm(), $field);
            WPML\register_string_form_translation($field, $service->getId());
            $return = '<li>' . Mappers\adminFormFieldsMapper($field, $service->getId()) . '</li>';
        }
        echo Toolkit\wrapAjaxResponse($return);
        exit;
    }

    public function removeServiceCustomField()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        $return = 'ok';
        try {
            $service = Database\Services::get($_POST['service_id']);
            Database\Forms::removeElement($service->getForm(), $_POST['hook']);
            WPML\remove_string_form_translation($_POST['hook'], $service->getId());
        } catch (\Exception $e) {
            $return = $e->getMessage();
        }
        echo Toolkit\wrapAjaxResponse($return);
        exit;
    }

    public function saveServiceBuiltinField()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        try {
            $service = Database\Services::get($_POST['service_id']);
            $fields = Database\Forms::getBuiltIn($service->getForm());
            $field = $fields[ $_POST['hook'] ];
            // Parse data
            $parsed_data = array();
            parse_str($_POST['inputs'], $parsed_data);
            $parsed_data = $parsed_data['event']['form_fields']['custom'];
            // Set data
            $field->setVisible(isset($parsed_data[ $_POST['hook'] . '_show' ]));
            $field->setRequired(isset($parsed_data[ $_POST['hook'] . '_required' ]));
            if (isset($parsed_data[ $_POST['hook'] . '_regex' ])) {
                $validation = $field->getData('validation');
                $validation['validation_regex']['custom'] = $parsed_data[ $_POST['hook'] . '_regex' ];
                $field->setData('validation', $validation);
            }
            if (isset($parsed_data[ $_POST['hook'] . '_validation' ])) {
                $validation = $field->getData('validation');
                $validation['validate'] = $parsed_data[ $_POST['hook'] . '_validation' ] === 'none' ? FALSE : $parsed_data[ $_POST['hook'] . '_validation' ];
                $field->setData('validation', $validation);
            }
            if ($_POST['hook'] === 'email') {
                $field->setData('value_confirmation', isset($parsed_data[ $_POST['hook'] . '_value_confirmation' ]));
            }
            // Saving
            Database\Forms::addElement($service->getForm(), $field);
            $return = 'ok';
        } catch (\Exception $e) {
            $return = $e->getMessage();
        }
        echo Toolkit\wrapAjaxResponse($return);
        exit;
    }

    public function saveServiceCustomField()
    {
        try {
            $service = Database\Services::get($_POST['service_id']);
            $fields = Database\Forms::get($service->getForm());
            $field = $fields[ $_POST['hook'] ];
            // Parse data
            $parsed_data = array();
            parse_str($_POST['inputs'], $parsed_data);
            $parsed_data = $parsed_data['event']['form_fields']['custom'];
            // Checking Hook availability, if new
            if (array_key_exists($parsed_data[ $_POST['hook'] . '_hook' ], $fields)
                && $_POST['hook'] != $parsed_data[ $_POST['hook'] . '_hook' ]
            ) {
                $return = 'duplicate_hook';
            } else {
                // Sets data
                $field->setHook($parsed_data[ $_POST['hook'] . '_hook' ]);
                $field->setTitle($parsed_data[ $_POST['hook'] . '_label' ]);
                if (isset($parsed_data[ $_POST['hook'] . '_description' ])) {
                    $field->setDescription($parsed_data[ $_POST['hook'] . '_description' ]);
                }
                $field->setVisible(isset($parsed_data[ $_POST['hook'] . '_show' ]));
                $field->setRequired(isset($parsed_data[ $_POST['hook'] . '_required' ]));
                if (isset($parsed_data[ $_POST['hook'] . '_extensions' ])) {
                    $field->setData('file_extensions', trim($parsed_data[ $_POST['hook'] . '_extensions' ]));
                }
                if ($field->getType() === 'checkbox') {
                    $field->setData('checked', isset($parsed_data[ $_POST['hook'] . '_selected' ]));
                }
                if (isset($parsed_data[ $_POST['hook'] . '_default' ])) {
                    if (isset($parsed_data[ $_POST['hook'] . '_default' ]['label'])) {
                        $options = array(
                            0 => array(
                                'text'            => $parsed_data[ $_POST['hook'] . '_default' ]['label'],
                                'price_increment' => (float)$parsed_data[ $_POST['hook'] . '_default' ]['price_increment']
                            )
                        );
                        if (isset($parsed_data[ $_POST['hook'] . '_options' ])) {
                            foreach ($parsed_data[ $_POST['hook'] . '_options' ] as $option) {
                                $options[] = array(
                                    'text'            => $option['label'],
                                    'price_increment' => (float)$option['price_increment']
                                );
                            }
                        }
                        $field->setData('options', $options);
                    } else {
                        $field->setData('value', $parsed_data[ $_POST['hook'] . '_default' ]);
                    }
                }
                if (isset($parsed_data[ $_POST['hook'] . '_regex' ])) {
                    $validation = $field->getData('validation');
                    $escaped_regex = str_replace('\\', '\\\\', $parsed_data[ $_POST['hook'] . '_regex' ]);
                    $validation['validation_regex']['custom'] = $escaped_regex;
                    $field->setData('validation', $validation);
                }
                if (isset($parsed_data[ $_POST['hook'] . '_validation' ])) {
                    $validation = $field->getData('validation');
                    $validation['validate'] = $parsed_data[ $_POST['hook'] . '_validation' ] === 'none' ? FALSE : $parsed_data[ $_POST['hook'] . '_validation' ];
                    $field->setData('validation', $validation);
                }
                if (isset($parsed_data[ $_POST['hook'] . '_prefill' ])) {
                    $field->setData('prefill', trim($parsed_data[ $_POST['hook'] . '_prefill' ]));
                }

                // Saving
                Database\Forms::addElement($service->getForm(), $field);
                WPML\register_string_form_translation($field, $service->getId());

                // Deleting old field if the hook is changed
                if ($_POST['hook'] != $parsed_data[ $_POST['hook'] . '_hook' ]) {
                    Database\Forms::removeElement($service->getForm(), $_POST['hook']);
                    WPML\remove_string_form_translation($_POST['hook'], $service->getId());
                }

                // Calling custom actions
                \TeamBooking\Actions\backend_form_field_save($parsed_data, $service, $_POST['hook']);

                $return = 'ok';
            }
        } catch (\Exception $e) {
            $return = $e->getMessage();
        }
        echo Toolkit\wrapAjaxResponse($return);
        exit;
    }

    public function moveServiceFormField()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        try {
            $service = Database\Services::get($_POST['service_id']);
            Database\Forms::moveElement($service->getForm(), $_POST['hook'], $_POST['where']);
            $return = 'ok';
        } catch (\Exception $e) {
            $return = $e->getMessage();
        }
        echo Toolkit\wrapAjaxResponse($return);
        exit;
    }

    public function toggleServiceActivation()
    {
        if (!Functions\isAdminOrCoworker()) exit;
        check_admin_referer('team_booking_options_verify');
        if (isset($_POST['personal'])) {
            $coworker_data = Functions\getSettings()->getCoworkerData(get_current_user_id());
            $custom_settings = $coworker_data->getCustomEventSettings($_POST['service_id']);
            $custom_settings->setParticipate($_POST['service_action'] === 'activate');
            $coworker_data->setCustomEventSettings($custom_settings, $_POST['service_id']);
            Functions\getSettings()->save();
            $return = 'ok';
        } else {
            try {
                $service = Database\Services::get($_POST['service_id']);
                $service->setActive($_POST['service_action'] === 'activate');
                Database\Services::add($service);
                $return = 'ok';
            } catch (\Exception $e) {
                $return = $e->getMessage();
            }
        }
        echo Toolkit\wrapAjaxResponse($return);
        exit;
    }

    public function toggleCalendarIndependency()
    {
        if (!Functions\isAdminOrCoworker()) exit;
        check_admin_referer('team_booking_options_verify');
        $coworker_data = Functions\getSettings()->getCoworkerData(get_current_user_id());
        $calendars = $coworker_data->getCalendars();
        $calendars[ $_POST['calendar_id'] ]['independent'] = ((bool)$_POST['independent']);
        $coworker_data->addCalendars($calendars);
        Functions\getSettings()->updateCoworkerData($coworker_data);
        Functions\getSettings()->save();
        echo Toolkit\wrapAjaxResponse('ok');
        exit;
    }

    public function cloneService()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        $new_service_id = Toolkit\filterInput($_POST['new_service_id'], TRUE);
        if (empty($new_service_id) || Functions\checkServiceIdExistance($new_service_id)) {
            $return = 'id already used';
        } else {
            $service_id = $_POST['service_id'];
            try {
                $service = Database\Services::get($service_id);
                unset($service->post_id);
                $service->setId($new_service_id);
                $i = 1;
                $service_name = $service->getName();
                while (Functions\checkServiceNameExistance($service_name)) {
                    $i++;
                    $service_name = $service->getName() . '-' . $i;
                }
                $service->setName($service_name);
                // Clone form
                $old_form_elements = Database\Forms::get($service->getForm());
                $new_form_id = Database\Forms::add($old_form_elements);
                // Handling translations
                $custom_fields = Database\Forms::getCustom($new_form_id);
                foreach ($custom_fields as $custom_field) {
                    WPML\register_string_form_translation($custom_field, $service->getId());
                }
                $service->setForm($new_form_id);
                Database\Services::add($service);
                WPML\register_string_service_translation($service);
                WPML\register_string_service_email_translation($service);
                $return = $service_id;
            } catch (\Exception $e) {
                $return = $e->getMessage();
            }
        }
        echo Toolkit\wrapAjaxResponse($return);
        exit;
    }

    public function cloneServiceSuggestion()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        $service_id = $_POST['service_id'];
        try {
            $service = Database\Services::get($service_id);
            $i = 1;
            while (Functions\checkServiceIdExistance($service_id)) {
                $i++;
                $service_id = $service->getId() . '-' . $i;
            }
            $return = $service_id;
        } catch (\Exception $e) {
            $return = $e->getMessage();
        }
        echo Toolkit\wrapAjaxResponse($return);
        exit;
    }

    public function addEmailTemplate()
    {
        if (!Functions\isAdmin()) \TeamBooking\Toolkit\ajaxJsonResponse(array('status' => 'error'));
        check_admin_referer('team_booking_options_verify');
        $name = \TeamBooking\Toolkit\filterInput($_POST['template_name']);
        $description = \TeamBooking\Toolkit\filterInput($_POST['template_description']);
        $content = $_POST['template_content'];
        $template = new EmailTemplate();
        $template->setId($name);
        $template->setName($name);
        $template->setDescription($description);
        $template->setContent($content);
        $template_id = Database\EmailTemplates::add($template);
        if (!$template_id) {
            \TeamBooking\Toolkit\ajaxJsonResponse(array('status' => 'error'));
        } else {
            \TeamBooking\Toolkit\ajaxJsonResponse(array('status' => 'ok'));
        }
    }

    public function insertPromotionCampaign()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        $services_array = $_POST['campaign_services'];
        $name = Toolkit\filterInput($_POST['campaign_name'], TRUE);
        $range = explode('-', $_POST['campaign_validity_date_range']);
        $start_date = \DateTime::createFromFormat('m/d/Y', $range[0]);
        $end_date = \DateTime::createFromFormat('m/d/Y', $range[1]);
        $bound_start_date = \DateTime::createFromFormat('m/d/Y', $_POST['promotion_bound_start']);
        $bound_end_date = \DateTime::createFromFormat('m/d/Y', $_POST['promotion_bound_end']);
        $errors = '';
        if (!$start_date || $start_date->format('m/d/Y') !== $range[0]) {
            $errors .= 'start_date_fail';
        }
        if (!$end_date || $end_date->format('m/d/Y') !== $range[1]) {
            $errors .= 'end_date_fail';
        }
        if (count(Database\Promotions::getByName($name)) > 0) {
            $errors .= 'name_fail';
        }

        if (!empty($errors)) {
            $return = $errors;
        } else {
            $new_campaign = new \TeamBooking_Promotions_Campaign();
            $new_campaign->addServices($services_array);
            $new_campaign->setName($name);
            if (filter_input(INPUT_POST, 'campaign_discount', FILTER_VALIDATE_INT) > 100
                && filter_input(INPUT_POST, 'campaign_discount_type') === 'percentage'
            ) {
                $new_campaign->setDiscount(100);
            } else {
                $new_campaign->setDiscount(filter_input(INPUT_POST, 'campaign_discount', FILTER_VALIDATE_INT));
            }
            $new_campaign->setLimit(filter_input(INPUT_POST, 'promotion_limit', FILTER_VALIDATE_INT, array(
                'options' => array(
                    'min_range' => 0
                )
            )));
            $new_campaign->setDiscountType(filter_input(INPUT_POST, 'campaign_discount_type'));
            $new_campaign->setStartTime($start_date->getTimestamp());
            $new_campaign->setEndTime($end_date->getTimestamp());
            $new_campaign->setStartBound(filter_input(INPUT_POST, 'bound_start_date_active', FILTER_VALIDATE_BOOLEAN) ? $bound_start_date->getTimestamp() : NULL);
            $new_campaign->setEndBound(filter_input(INPUT_POST, 'bound_end_date_active', FILTER_VALIDATE_BOOLEAN) ? $bound_end_date->getTimestamp() : NULL);
            $new_campaign->setStatus(TRUE);
            Database\Promotions::insert($new_campaign);
            $return = 'ok';
        }
        echo Toolkit\wrapAjaxResponse($return);
        exit;
    }

    public function insertPromotionCoupon()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        $services_array = $_POST['coupon_services'];
        if (NULL === $services_array) {
            echo Toolkit\wrapAjaxResponse('ok');
            exit;
        }
        $name = Toolkit\filterInput($_POST['coupon_name'], TRUE);
        $range = explode('-', $_POST['coupon_validity_date_range']);
        $start_date = \DateTime::createFromFormat('m/d/Y', $range[0]);
        $end_date = \DateTime::createFromFormat('m/d/Y', $range[1]);
        $bound_start_date = \DateTime::createFromFormat('m/d/Y', $_POST['promotion_bound_start']);
        $bound_end_date = \DateTime::createFromFormat('m/d/Y', $_POST['promotion_bound_end']);
        $errors = '';
        if (!$start_date || $start_date->format('m/d/Y') !== $range[0]) {
            $errors .= 'start_date_fail';
        }
        if (!$end_date || $end_date->format('m/d/Y') !== $range[1]) {
            $errors .= 'end_date_fail';
        }
        if (count(Database\Promotions::getByName($name)) > 0) {
            $errors .= 'name_fail';
        }

        if (!empty($errors)) {
            $return = $errors;
        } else {
            $new_coupon = new \TeamBooking_Promotions_Coupon();
            $new_coupon->addServices($services_array);
            $new_coupon->setName($name);
            if (filter_input(INPUT_POST, 'coupon_discount', FILTER_VALIDATE_INT) > 100
                && filter_input(INPUT_POST, 'coupon_discount_type') === 'percentage'
            ) {
                $new_coupon->setDiscount(100);
            } else {
                $new_coupon->setDiscount(filter_input(INPUT_POST, 'coupon_discount', FILTER_VALIDATE_INT));
            }
            $new_coupon->setLimit(filter_input(INPUT_POST, 'promotion_limit', FILTER_VALIDATE_INT, array(
                'options' => array(
                    'min_range' => 0
                )
            )));

            $list = array();
            if (Toolkit\filterInput(trim($_POST['coupon_list']), TRUE) !== '') {
                $list = array_map('trim', explode(',', $_POST['coupon_list']));
                foreach ($list as $key => $element) {
                    $list[ $key ] = Toolkit\filterInput($element, TRUE);
                }
            }
            $new_coupon->setList($list);

            $new_coupon->setDiscountType(filter_input(INPUT_POST, 'coupon_discount_type'));
            $new_coupon->setStartTime($start_date->getTimestamp());
            $new_coupon->setEndTime($end_date->getTimestamp());
            $new_coupon->setStartBound(filter_input(INPUT_POST, 'bound_start_date_active', FILTER_VALIDATE_BOOLEAN) ? $bound_start_date->getTimestamp() : NULL);
            $new_coupon->setEndBound(filter_input(INPUT_POST, 'bound_end_date_active', FILTER_VALIDATE_BOOLEAN) ? $bound_end_date->getTimestamp() : NULL);
            $new_coupon->setStatus(TRUE);
            Database\Promotions::insert($new_coupon);
            $return = 'ok';
        }
        echo Toolkit\wrapAjaxResponse($return);
        exit;
    }

    public function changePromotionStatus()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        $promotion = Database\Promotions::getById($_POST['pricing_id']);
        if ($_POST['status'] === 'run') {
            $promotion->setStatus(TRUE);
        } else {
            $promotion->setStatus(FALSE);
        }
        Database\Promotions::update($_POST['pricing_id'], $promotion);
        echo Toolkit\wrapAjaxResponse('ok');
        exit;
    }

    public function deletePromotion()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        Database\Promotions::delete($_POST['pricing_id']);

        echo Toolkit\wrapAjaxResponse('ok');
        exit;
    }

    public function editPromotion()
    {
        if (!Functions\isAdmin()) exit;
        check_admin_referer('team_booking_options_verify');
        $services_array = $_POST['pricing_services'];
        $promotion = Database\Promotions::getById($_POST['pricing_db_id']);
        $name = Toolkit\filterInput($_POST['pricing_name'], TRUE);
        $range = explode('-', $_POST['promotion_validity_date_range']);
        $start_date = \DateTime::createFromFormat('m/d/Y', $range[0]);
        $end_date = \DateTime::createFromFormat('m/d/Y', $range[1]);
        $bound_start_date = \DateTime::createFromFormat('m/d/Y', $_POST['promotion_bound_start']);
        $bound_end_date = \DateTime::createFromFormat('m/d/Y', $_POST['promotion_bound_end']);
        $errors = '';
        if (!$start_date || $start_date->format('m/d/Y') !== $range[0]) {
            $errors .= 'start_date_fail';
        }
        if (!$end_date || $end_date->format('m/d/Y') !== $range[1]) {
            $errors .= 'end_date_fail';
        }
        if ($name !== $promotion->getName()
            && count(Database\Promotions::getByName($name)) > 0
        ) {
            $errors .= 'name_fail';
        }

        if (!empty($errors)) {
            $return = $errors;
        } else {
            $promotion->addServices($services_array);

            if ($promotion->checkClass('coupon')) {
                $list = array();
                if (Toolkit\filterInput(trim($_POST['coupon_list']), TRUE) !== '') {
                    $list = array_map('trim', explode(',', $_POST['coupon_list']));
                    foreach ($list as $key => $element) {
                        $list[ $key ] = Toolkit\filterInput($element, TRUE);
                    }
                }
                $promotion->setList($list);
            }

            $promotion->setName($name);
            if (filter_input(INPUT_POST, 'pricing_discount', FILTER_VALIDATE_INT) > 100
                && filter_input(INPUT_POST, 'pricing_discount_type') === 'percentage'
            ) {
                $promotion->setDiscount(100);
            } else {
                $promotion->setDiscount(filter_input(INPUT_POST, 'pricing_discount', FILTER_VALIDATE_INT));
            }
            $promotion->setDiscountType(filter_input(INPUT_POST, 'pricing_discount_type'));
            $promotion->setLimit(filter_input(INPUT_POST, 'promotion_limit', FILTER_VALIDATE_INT, array(
                'options' => array(
                    'min_range' => 0
                )
            )));
            $promotion->setStartTime($start_date->getTimestamp());
            $promotion->setEndTime($end_date->getTimestamp());
            $promotion->setStartBound(filter_input(INPUT_POST, 'bound_start_date_active', FILTER_VALIDATE_BOOLEAN) ? $bound_start_date->getTimestamp() : NULL);
            $promotion->setEndBound(filter_input(INPUT_POST, 'bound_end_date_active', FILTER_VALIDATE_BOOLEAN) ? $bound_end_date->getTimestamp() : NULL);
            Database\Promotions::update($_POST['pricing_db_id'], $promotion);
            $return = 'ok';
        }
        echo Toolkit\wrapAjaxResponse($return);
        exit;
    }

    public function addTinyMCEButton()
    {
        global $typenow;
        // check user permissions
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
            return;
        }
        // verify the post type
        if (!in_array($typenow, array(
            'post',
            'page',
        ))
        )
            return;
        // check if WYSIWYG is enabled
        if (get_user_option('rich_editing') === 'true') {
            add_filter('mce_external_plugins', array(
                $this,
                'addTinyMCEPlugin',
            ));
            add_filter('mce_buttons', array(
                $this,
                'registerTinyMCEButton',
            ));
        }
        // passing variables
        $services = Functions\getSettings()->getServiceIdList();
        $coworkers = Functions\getSettings()->getCoworkersData();
        $array = array('services' => array(), 'coworkers' => array());
        foreach ($services as $service_id) {
            $array['services'][ $service_id ] = Database\Services::get($service_id)->getName();
        }
        foreach ($coworkers as $coworker_id => $coworker) {
            /* @var $coworker \TeamBookingCoworker */
            $array['coworkers'][ $coworker_id ] = $coworker->getDisplayName();
        }

        ?>
        <!-- TinyMCE TeamBooking Plugin -->
        <script type='text/javascript'>
            var tbk_mce_config = {
                'strings'  : {
                    'title'             : '<?=esc_html__('TeamBooking shortcodes', 'team-booking')?>',
                    'tb_calendar'       : '<?=esc_html__('Add calendar', 'team-booking')?>',
                    'tb_reservations'   : '<?=esc_html__('Add reservations list', 'team-booking')?>',
                    'tb_upcoming'       : '<?=esc_html__('Add upcoming list', 'team-booking')?>',
                    'read_only'         : '<?=esc_html__('Read only', 'team-booking')?>',
                    'no_filter'         : '<?=esc_html__('Hide filter buttons', 'team-booking')?>',
                    'no_timezone'       : '<?=esc_html__('Hide timezone selector', 'team-booking')?>',
                    'logged_only'       : '<?=esc_html__('Show to logged users only', 'team-booking')?>',
                    'no_filters'        : '<?=esc_html__('Hide filter buttons', 'team-booking')?>',
                    'specific_services' : '<?=esc_html__('Specific service(s), leave blank for all', 'team-booking')?>',
                    'specific_coworkers': '<?=esc_html__('Specific coworker(s), leave blank for all', 'team-booking')?>',
                    'show_more'         : '<?=esc_html__('Show more', 'team-booking')?>',
                    'show_descriptions' : '<?=esc_html__('Show service descriptions', 'team-booking')?>',
                    'how_many'          : '<?=esc_html__('How many events to show', 'team-booking')?>',
                    'how_many_tooltip'  : '<?=esc_html__('Please write a number', 'team-booking')?>',
                    'max_events'        : '<?=esc_html__('Max total number of events, leave blank for no limit', 'team-booking')?>',
                    'max_events_tooltip': '<?=esc_html__('Please write a number or leave blank', 'team-booking')?>',
                    'slot_style'        : '<?=esc_html__('Choose the slots display style', 'team-booking')?>',
                    'style_basic'       : '<?=esc_html__('Basic', 'team-booking')?>',
                    'style_elegant'     : '<?=esc_html__('Elegant', 'team-booking')?>',
                    'hide_same_days'    : '<?=esc_html__('Show little calendar only once per day', 'team-booking')?>'
                },
                'coworkers': <?= json_encode($array['coworkers'])?>,
                'services' : <?= json_encode($array['services'])?>
            };
        </script>
        <!-- TinyMCE TeamBooking Plugin -->
        <?php
    }

    /**
     * @param $plugin_array
     *
     * @return mixed
     */
    public function addTinyMCEPlugin($plugin_array)
    {
        $plugin_array['teambooking_tinymce_button'] = TEAMBOOKING_URL . 'js/tinymce_button.js?' . filemtime(TEAMBOOKING_PATH . 'js/tinymce_button.js');

        return $plugin_array;
    }

    /**
     * @param $buttons
     *
     * @return array
     */
    public function registerTinyMCEButton($buttons)
    {
        $buttons[] = 'teambooking_tinymce_button';

        return $buttons;
    }

}
