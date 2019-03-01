<?php
/**
 * @package   Awesome Support: Satisfaction Survey
 * @author    Robert W. Kramer III for Awesome Support <support@getawesomesupport.com>
 * @link      http://getawesomesupport.com
 * @copyright 2016 Awesome Support
 *
 * Plugin Name:       Awesome Support: Satisfaction Survey
 * Plugin URI:        http://getawesomesupport.com/addons/satisfaction-survey/
 * Description:       Add satisfaction survey support for closed tickets.
 * Version:           1.0.8
 * Author:            Awesome Support
 * Author URI:        https://www.getawesomesupport.com
 * Text Domain:       wpas-ss
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 *
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/* ----------------------------------------------------------------------------*
 * Shortcuts
 * ---------------------------------------------------------------------------- */

define( 'WPASSS_VERSION', '1.0.8' );
define( 'WPASSS_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'WPASSS_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WPASSS_ROOT', trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) );

/* ----------------------------------------------------------------------------*
 * Instantiate the plugin
 * ---------------------------------------------------------------------------- */

/** @noinspection PhpIncludeInspection */
require_once( WPASSS_PATH . 'class-satisfaction-survey.php' );

/**
 * Load the settings
 */
/** @noinspection PhpIncludeInspection */
require_once( WPASSS_PATH . 'includes/settings.php' );

register_activation_hook( __FILE__, array( 'WPAS_Satisfaction_Survey', 'activate' ) );


/**
 * Check if Awesome Support is active
 * */
//if ( in_array( 'awesome-support/awesome-support.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	add_action( 'plugins_loaded', array( 'WPAS_Satisfaction_Survey', 'get_instance' ), 10, 0 );
//}


if ( is_admin() ) {

	/**
	 * Add link to settings tab
	 */
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array(
		'WPAS_Satisfaction_Survey',
		'settings_page_link',
	) );
}

