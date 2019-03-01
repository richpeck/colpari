<?php
/**
 * @package   Awesome Support Private Notes
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016 Awesome Support
 *
 * @wordpress-plugin
 * Plugin Name:       Awesome Support: Private Notes
 * Plugin URI:        http://getawesomesupport.com/addons/private-notes/
 * Description:       This add-on for Awesome Support enables you to add notes to a ticket that clients won't see. You can use these notes as reminders or to give other agents insights into the ticket issue.
 * Version:           1.3.0
 * Author:            The Awesome Support Team
 * Author URI:        https://getawesomesupport.com
 * Text Domain:       as-private-notes
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

define( 'WPASPR_VERSION', '1.3.0' );
define( 'WPASPR_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'WPASPR_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WPASPR_ROOT', trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) );

/*----------------------------------------------------------------------------*
 * Instantiate the plugin
 *----------------------------------------------------------------------------*/

require_once( WPASPR_PATH . 'class-private-note.php' );
register_activation_hook( __FILE__, array( 'WPAS_Private_Note', 'activate' ) );

/**
 * Check if Awesome Support is active
 **/
if ( in_array( 'awesome-support/awesome-support.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	add_action( 'plugins_loaded', array( 'WPAS_Private_Note', 'get_instance' ), 10, 0 );
}