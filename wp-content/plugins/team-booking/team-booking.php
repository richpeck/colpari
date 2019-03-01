<?php

/**
 * Plugin Name: Team Booking
 * Plugin URI: http://stroheimdesign.com
 * Description: Booking system for reservations and appointments
 * Version: 2.5.5
 * Author: VonStroheim
 * Author URI: http://codecanyon.net/user/vonstroheim
 * Text Domain: team-booking
 * Domain Path: /languages
 */

defined('ABSPATH') or die('No script kiddies please!');

////////////////////////////
// Plugin path constants  //
////////////////////////////
define('TEAMBOOKING_PATH', plugin_dir_path(__FILE__));
define('TEAMBOOKING_URL', plugin_dir_url(__FILE__));
define('TEAMBOOKING_FILE_PATH', __FILE__);

// Set the right include path (temporary)...
$prev_include_path = set_include_path(__DIR__);

////////////////////
// Require files  //
////////////////////
require_once __DIR__ . '/includes/tb_constants.php';
require_once __DIR__ . '/includes/tb_functions.php';
require_once __DIR__ . '/includes/tb_mappers.php';
require_once __DIR__ . '/includes/tb_autoloader.php';
require_once __DIR__ . '/includes/tb_wpml.php';

/////////////////////////////////////
// Admin-Frontend classes selector //
/////////////////////////////////////
if ((!defined('DOING_AJAX') || !DOING_AJAX) && is_admin()) {
    /*
     * ZipArchive class loading for XLSX support
     */
    try {
        include_once dirname(TEAMBOOKING_FILE_PATH) . '/libs/xlsxwriter/xlsxwriter.class.php';
    } catch (Exception $e) {
        // ZipArchive library not supported
    }
} else {
    // Nothing yet
}

//////////////////
// Main loader  //
//////////////////
$plugin_init = new \TeamBooking\Loader();
$plugin_init->load();

// ...restore previous include_path
set_include_path($prev_include_path);