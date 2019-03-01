<?php
/**
 * @package   Awesome Support: Guest Tickets
 * @author    awesomesupport <contact@awesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016 Awesomesupport
 *
 * @guest-tickets-version 2.1.1
 *
 * Plugin Name:       Awesome Support: Guest Tickets
 * Description:       Allow users to submit a ticket as guests, without creating an account first.
 * Version:           2.1.1
 * Author:            Awesome Support
 * Author URI:        https://getawesomesupport.com
 * Text Domain:       as-guest-tickets
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Registers the activation hook.
 */
register_activation_hook( __FILE__, array( 'AS_Guest_Tickets_Loader', 'activate' ) );

add_action( 'plugins_loaded', array( 'AS_Guest_Tickets_Loader', 'get_instance' ) );

/**
 * Optionally prevent the new user email with password reset instructions from being sent to end user....
 *
 * This is an override to the WordPress PLUGGABLE function.  It may not work if another pluggable function exists 
 * since only ONE pluggable function can exist at a time!
 */
 if ( !function_exists('wp_new_user_notification') && defined('ASGT_NO_NEW_USER_CONFIRMATION') && true === ASGT_NO_NEW_USER_CONFIRMATION) {

	function wp_new_user_notification( ) {
		// do nothing.
	}
}


/**
 * Guest Tickets add-on loader.
 *
 * @since 1.0.0
 */
class AS_Guest_Tickets_Loader {

	/**
	 * ID of the item.
	 *
	 * The item ID must match teh post ID on the e-commerce site.
	 * Using the item ID instead of its name has the huge advantage of
	 * allowing changes in the item name.
	 *
	 * If the ID is not set the class will fall back on the plugin name instead.
	 *
	 * @access protected
	 * @since  1.0
	 * @var    int
	 */
	protected $item_id = 694611;

	/**
	 * Required version of Awesome Support core.
	 *
	 * The minimum version of the core that's required to properly run this add-on.
	 * If the minimum version requirement isn't met an error message is displayed
	 * and the add-on isn't registered.
	 *
	 * @access protected
	 * @since  1.0
	 * @var    string
	 */
	protected $version_required = '3.3.2';

	/**
	 * Required version of PHP.
	 *
	 * Follow WordPress latest requirements and require PHP version 5.5 at least.
	 *
	 * @access protected
	 * @since  1.0
	 * @var    string
	 */
	protected $php_version_required = '5.5';

	/**
	 * Plugin slug.
	 *
	 * @access protected
	 * @since  1.0
	 * @var    string
	 */
	protected $slug = 'guest-tickets';

	/**
	 * Instance of this loader class.
	 *
	 * @access protected
	 * @since  1.0
	 * @var    AS_Guest_Tickets_Loader
	 */
	protected static $instance = null;

	/**
	 * Instance of the add-on itself.
	 *
	 * @access public
	 * @since  1.0
	 * @var    AS_Guest_Tickets_Loader
	 */
	public $addon = null;

	/**
	 * Potential error message.
	 *
	 * @access protected
	 * @since  1.0
	 * @var    null|WP_Error
	 */
	protected $error = null;

	/**
	 * Sets up the Guest Tickets add-on.
	 *
	 * @access public
	 * @since  1.0
	 */
	public function __construct() {
		$this->define_constants();

		$this->init();
	}

	/**
	 * Retrieves a Guest Tickets instance.
	 *
	 * @access public
	 * @since  1.0
	 * @static
	 *
	 * @return AS_Guest_Tickets_Loader A single instance of the class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Defines Guest Tickets constants.
	 *
	 * @access public
	 * @since  1.0
	 */
	public function define_constants() {
		define( 'AS_GT_VERSION', '2.1.1' );
		define( 'AS_GT_URL',     trailingslashit( plugin_dir_url( __FILE__ ) ) );
		define( 'AS_GT_PATH',    trailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'AS_GT_ROOT',    trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) );
	}

	/**
	 * Initializes the Guest Tickets add-on.
	 *
	 * This method is the one running the checks and registering the addon to the core.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return bool Whether or not the addon was registered.
	 */
	public function init() {

		$plugin_name = $this->plugin_data( 'Name' );

		if ( ! $this->is_core_active() ) {
			$this->add_error( sprintf( __( '%s requires Awesome Support to be active. Please activate the core plugin first.', 'as-guest-tickets' ), $plugin_name ) );
		}

		if ( ! $this->is_php_version_enough() ) {
			$this->add_error( sprintf( __( 'Unfortunately, %s can not run on PHP versions older than %s. Read more information about <a href="%s" target="_blank">how you can update</a>.', 'as-guest-tickets' ), $plugin_name, $this->php_version_required, esc_url( 'http://www.wpupdatephp.com/update/' ) ) );
		}

		if ( ! $this->is_version_compatible() ) {
			$this->add_error( sprintf( __( '%s requires Awesome Support version %s or greater. Please update the core plugin first.', 'as-guest-tickets' ), $plugin_name, $this->version_required ) );
		}

		if ( ( $this->error instanceof WP_Error ) ) {
			add_action( 'admin_notices', array( $this, 'display_error' ) );
			add_action( 'admin_init',    array( $this, 'deactivate' )    );

			return false;
		}

		// Add the add-on license field.
		if ( is_admin() ) {

			// Add the license admin notice.
			$this->add_license_notice();

			add_filter( 'wpas_addons_licenses', array( $this, 'addon_license' ),       10, 1 );
			add_filter( 'plugin_row_meta',      array( $this, 'license_notice_meta' ), 10, 4 );
		}

		// Load the textdomain for translation.
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ), 15 );

		// Register the add-on.
		wpas_register_addon( $this->slug, array( $this, 'load' ) );

		return true;

	}

	/**
	 * Adds the license option.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param array $licenses List of add-ons licenses.
	 * @return array[] Updated list of licenses
	 */
	public function addon_license( $licenses ) {

		$plugin_name = $this->plugin_data( 'Name' );
		$plugin_name = trim( str_replace( 'Awesome Support:', '', $plugin_name ) ); // Remove the Awesome Support prefix from the addon name

		$licenses[] = array(
			'name'      => $plugin_name,
			'id'        => "license_{$this->slug}",
			'type'      => 'edd-license',
			'default'   => '',
			'server'    => esc_url( 'https://getawesomesupport.com' ),
			'item_name' => $plugin_name,
			'item_id'   => $this->item_id,
			'file'      => __FILE__
		);

		return $licenses;
	}

	/**
	 * Displays a notice if user didn't set his Envato license code.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return void
	 */
	public function add_license_notice() {

		// We only want to display the notice to the site admin.
		if ( ! current_user_can( 'administrator' ) ) {
			return;
		}

		$license = wpas_get_option( "license_{$this->slug}", '' );

		// Do not show the notice if the license key has already been entered.
		if ( ! empty( $license ) ) {
			return;
		}

		$link = wpas_get_settings_page_url( 'licenses' );
		WPAS()->admin_notices->add_notice( 'error', "lincense_{$this->slug}", sprintf( __( 'Please <a href="%s">fill-in your product license</a> now. If you don\'t, your copy of Awesome Support: Guest Tickets <strong>will never be updated</strong>.', 'as-guest-tickets' ), $link ) );

	}

	/**
	 * Adds a license warning in the plugin meta row.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param array  $plugin_meta The current plugin meta row.
	 * @param string $plugin_file The plugin file path.
	 * @return array Updated plugin meta.
	 */
	public function license_notice_meta( $plugin_meta, $plugin_file ) {

		$license = wpas_get_option( "license_{$this->slug}", '' );

		if( ! empty( $license ) ) {
			return $plugin_meta;
		}

		$license_page = add_query_arg( array(
			'post_type' => 'ticket',
			'page'      => 'settings',
			'tab'       => 'licenses'
		), admin_url( 'edit.php' ) );

		if ( plugin_basename( __FILE__ ) === $plugin_file ) {
			$plugin_meta[] = '<strong>' . sprintf( __( 'You must fill-in your product license in order to get future plugin updates. <a href="%s">Click here to do it</a>.', 'as-guest-tickets' ), $license_page ) . '</strong>';
		}

		return $plugin_meta;
	}

	/**
	 * Loads the plugin text domain for translation.
	 *
	 * With the introduction of plugins language packs in WordPress loading the textdomain is slightly more complex.
	 *
	 * We now have 3 steps:
	 *
	 * 1. Check for the language pack in the WordPress core directory
	 * 2. Check for the translation file in the plugin's language directory
	 * 3. Fallback to loading the textdomain the classic way
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @return bool True if the language file was loaded, otherwise false.
	 */
	public function load_plugin_textdomain() {
		$lang_dir       = AS_GT_ROOT . 'languages/';
		$lang_path      = AS_GT_PATH . 'languages/';
		$locale         = apply_filters( 'plugin_locale', get_locale(), 'as-guest-tickets' );
		$mofile         = "guest-tickets-$locale.mo";
		$glotpress_file = WP_LANG_DIR . '/plugins/guest-tickets/' . $mofile;

		// Look for the GlotPress language pack first of all.
		if ( file_exists( $glotpress_file ) ) {
			$language = load_textdomain( 'as-guest-tickets', $glotpress_file );
		} elseif ( file_exists( $lang_path . $mofile ) ) {
			$language = load_textdomain( 'as-guest-tickets', $lang_path . $mofile );
		} else {
			$language = load_plugin_textdomain( 'as-guest-tickets', false, $lang_dir );
		}

		return $language;
	}

	/**
	 * Adds a new error to the WP_Error object.
	 *
	 * The object is created if it doesn't exist yet.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param string $message Error message to add.
	 * @return void
	 */
	public function add_error( $message ) {

		if ( ! is_object( $this->error ) || ! ( $this->error instanceof WP_Error ) ) {
			$this->error = new WP_Error();
		}

		$this->error->add( 'addon_error', $message );

	}

	/**
	 * Retrieves the plugin data.
	 *
	 * @access protected
	 * @since  1.0
	 *
	 * @param string $data Plugin data to retrieve.
	 * @return string Data value.
	 */
	protected function plugin_data( $data ) {

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		$plugin = get_plugin_data( __FILE__, false, false );

		if ( array_key_exists( $data, $plugin ) ){
			return $plugin[ $data ];
		} else {
			return '';
		}

	}

	/**
	 * Checks if Awesome Support core is active.
	 *
	 * Checks if the core plugin is listed in the active plugins in the WordPress database.
	 *
	 * @access protected
	 * @since  1.0
	 *
	 * @return bool Whether or not Awesome Support core is active.
	 */
	protected function is_core_active() {
		/**
		 * Filters the list of active plugins.
		 *
		 * @since 1.0.0
		 *
		 * @param array $active_plugins Active plugins.
		 */
		return in_array( 'awesome-support/awesome-support.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
	}

	/**
	 * Checks if the core version is compatible with this add-on.
	 *
	 * @since  1.0
	 *
	 * @return bool
	 */
	protected function is_version_compatible() {

		/*
		 * Return true if the core is not active so that this message won't show.
		 * We already have the error saying the plugin is disabled, no need to add this one.
		 */
		if ( ! $this->is_core_active() ) {
			return true;
		}

		if ( empty( $this->version_required ) ) {
			return true;
		}

		if ( ! defined( 'WPAS_VERSION' ) ) {
			return false;
		}

		if ( version_compare( WPAS_VERSION, $this->version_required, '<' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if the version of PHP is compatible with this add-on.
	 *
	 * @access protected
	 * @since  1.0
	 *
	 * @return bool Whether the current PHP version is compatible.
	 */
	protected function is_php_version_enough() {

		/**
		 * No version set, we assume everything is fine.
		 */
		if ( empty( $this->php_version_required ) ) {
			return true;
		}

		if ( version_compare( phpversion(), $this->php_version_required, '<' ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Retrieves a Guest Tickets instance.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return AS_Guest_Tickets_Loader Guest Tickets instance.
	 */
	public function scope() {
		return $this->addon;
	}

	/**
	 * Activates the plugin.
	 *
	 * The activation method just checks if the main plugin Awesome Support is installed
	 * (active or inactive) on the site. If not, the addon installation is aborted and
	 * an error message is displayed.
	 *
	 * @since  1.0
	 * @static
	 *
	 * @return void
	 */
	public static function activate() {

		if ( ! class_exists( 'Awesome_Support' ) ) {
			deactivate_plugins( basename( __FILE__ ) );
			wp_die(
				sprintf( __( 'You need Awesome Support to activate this addon. Please <a href="%s" target="_blank">install Awesome Support</a> before continuing.', 'as-guest-tickets' ),
					esc_url( 'http://getawesomesupport.com/?utm_source=internal&utm_medium=addon_loader&utm_campaign=Addons' )
				)
			);

		}
	}

	/**
	 * Loads the add-on.
	 *
	 * Includes all necessary files and instantiates the add-on. Required by wpas_load_addons().
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return void
	 */
	public function load() {
		if ( is_admin() ) {
			require_once( AS_GT_PATH . 'includes/admin/class-guest-settings.php' );

			new AS_Guest_Tickets\Admin\Settings();
		}

		require_once( AS_GT_PATH . 'includes/class-guest-login.php' );

		new AS_Guest_Tickets\Login();
	}
}
