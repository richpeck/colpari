<?php
/**
 * @package   Awesome Support Canned Responses
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016 Awesome Support
 *
 * @wordpress-plugin
 * Plugin Name:       Awesome Support: Canned Responses
 * Plugin URI:        http://getawesomesupport.com/addons/canned-responses/
 * Description:       This addon will make replying to a ticket easy as pie. You can manage your canned responses and add them in a ticket reply in one click.
 * Version:           1.4.0
 * Author:            The Awesome Support Team
 * Author URI:        https://getawesomesupport.com
 * Text Domain:       as-canned-responses
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Shortcuts
 *----------------------------------------------------------------------------*/

define( 'WPASCR_VERSION', '1.4.0' );
define( 'WPASCR_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'WPASCR_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WPASCR_ROOT', trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) );

/*----------------------------------------------------------------------------*
 * Instantiate the plugin
 *----------------------------------------------------------------------------*/

require_once( WPASCR_PATH . 'class-canned-response.php' );
register_activation_hook( __FILE__, array( 'WPASCR_Canned_Response', 'activate' ) );

/**
 * Check if Awesome Support is active
 **/
if ( in_array( 'awesome-support/awesome-support.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	add_action( 'plugins_loaded', array( 'WPASCR_Canned_Response', 'get_instance' ), 10, 0 );
}