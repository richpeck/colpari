<?php
/**
 * Plugin Name:       ABBUA Wordpress Admin Theme
 * Plugin URI:        http://www.castorstudio.com/abbua-admin-wordpress-white-label-admin-theme
 * Description:       ABBUA Admin it's an advanced and carefully crafted white label WordPress admin theme. With ABBUA Admin you can change logos, choose one from tons of color themes, customize everything on the login page and many more features that lets you bring your WordPress admin area to the next level.
 * Version:           1.1.0
 * Author:            Castorstudio
 * Author URI:        http://www.castorstudio.com
 * Text Domain:       abbua_admin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '1.1.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-abbua_admin-activator.php
 */
function activate_abbua_admin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-abbua_admin-activator.php';
	Abbua_admin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-abbua_admin-deactivator.php
 */
function deactivate_abbua_admin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-abbua_admin-deactivator.php';
	Abbua_admin_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_abbua_admin' );
register_deactivation_hook( __FILE__, 'deactivate_abbua_admin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-abbua_admin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_abbua_admin() {

	$plugin = new Abbua_admin();
	$plugin->run();

}
run_abbua_admin();
