<?php
/**
 * Plugin Name: Awesome Support: Remote Tickets
 * Plugin URI: https://getawesomesupport.com/addons/remote-tickets/
 * Description: Allow users to create tickets outside of this WordPress install
 * Author: Awesome Support
 * Author URI: https://getawesomesupport.com/
 * Version: 1.3.0
 * Text Domain: awesome-support-remote-tickets
 * Domain Path: /languages/
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Instantiate the plugin
 *----------------------------------------------------------------------------*/

/**
 * Register the activation hook
 */
register_activation_hook( __FILE__, array( 'WPAS_Remote_Tickets', 'maybe_activate' ) );

add_action( 'plugins_loaded', 'wpas_remote_tickets' );

/**
 * Awesome Support Remote Tickets main plugin class.
 *
 * @since 1.0.0
 */
class WPAS_Remote_Tickets {

	/**
	 * ID of the item.
	 *
	 * The item ID must match the post ID on the e-commerce site.
	 * Using the item ID instead of its name has the huge advantage of
	 * allowing changes in the item name.
	 *
	 * If the ID is not set the class will fall back on the plugin name instead.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $item_id = 864080;

	/**
	 * Required version of the core.
	 *
	 * The minimum version of the core that's required
	 * to properly run this addon. If the minimum version
	 * requirement isn't met an error message is displayed
	 * and the addon isn't registered.
	 *
	 * @since  1.0.0
	 * @var    string
	 */
	protected $version_required = '4.0.0';

	/**
	 * Required version of PHP.
	 *
	 * Require at least PHP version 5.6.
	 *
	 * @var string
	 */
	protected $php_version_required = '5.6';

	/**
	 * Plugin slug.
	 *
	 * @since  1.0.0
	 * @var    string
	 */
	protected $slug = 'remote-tickets';

	/**
	 * Possible error message.
	 *
	 * @var null|WP_Error
	 */
	protected $error = null;

	/**
	 * Instance of this loader class.
	 *
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * WPAS_Remote_Tickets constructor.
	 */
	public function __construct() {
		$this->declare_constants();
		$this->init();
	}

	/**
	 * Declare plugin constants
	 */
	protected function declare_constants() {
		define( 'AS_RT_VERSION', '1.3.0' );
		define( 'AS_RT_URL',     $this->plugin_url() );
		define( 'AS_RT_PATH',    trailingslashit( $this->plugin_path() ) );
	}

	/**
	 * Initialize the addon.
	 *
	 * This method is the one running the checks and
	 * registering the addon to the core.
	 *
	 * @since  1.0.0
	 * @return boolean Whether or not the addon was registered
	 */
	public function init() {

		$plugin_name = $this->plugin_data( 'Name' );

		if ( ! $this->is_core_active() ) {
			$this->add_error( sprintf( __( '%s requires Awesome Support to be active. Please activate the core plugin first.', 'awesome-support-remote-tickets' ), $plugin_name ) );
		}

		if ( ! function_exists( 'wpas_api' ) ) {
			$this->add_error( sprintf( __( '%s requires Awesome Support API to be active. Please activate the plugin first.', 'awesome-support-remote-tickets' ), $plugin_name ) );
		}

		if ( ! $this->is_php_version_enough() ) {
			$this->add_error( sprintf( __( 'Unfortunately, %s can not run on PHP versions older than %s. Read more information about <a href="%s" target="_blank">how you can update</a>.', 'awesome-support-remote-tickets' ), $plugin_name, $this->php_version_required, esc_url( 'http://www.wpupdatephp.com/update/' ) ) );
		}

		if ( ! $this->is_version_compatible() ) {
			$this->add_error( sprintf( __( '%s requires Awesome Support version %s or greater. Please update the core plugin first.', 'awesome-support-remote-tickets' ), $plugin_name, $this->version_required ) );
		}

		if ( is_a( $this->error, 'WP_Error' ) ) {
			add_action( 'admin_notices', array( $this, 'display_error' ), 10, 0 );
			add_action( 'admin_init',    array( $this, 'deactivate' ),    10, 0 );
			return false;
		}

		/**
		 * Add the addon license field
		 */
		if ( is_admin() ) {
			// Add the license admin notice
			$this->add_license_notice();
			add_filter( 'wpas_addons_licenses', array( $this, 'addon_license' ),       10, 1 );
			add_filter( 'plugin_row_meta',      array( $this, 'license_notice_meta' ), 10, 4 );
		}

		/**
		 * Register the addon
		 */
		if ( function_exists( 'wpas_register_addon' ) ) {
			wpas_register_addon( $this->slug, array( $this, 'load' ) );
		}

		return true;

	}

	/**
	 * Load the addon.
	 *
	 * Include all necessary files and instanciate the addon.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function load() {
		require_once( $this->plugin_path() . 'vendor/autoload.php' );

		register_deactivation_hook( $this->plugin_file(), array( 'WPAS_Remote_Tickets', 'deactivate' ) );

		$this->includes();
		$this->actions();
		$this->filters();
	}

	/**
	 * Handle Actions
	 */
	protected function actions() {
		add_action( 'init', array( $this, 'load_text_domain' ) );
	}

	/**
	 * Handle Filters
	 */
	protected function filters() {}

	/**
	 * Include required files
	 *
	 * @since 1.0.0
	 */
	protected function includes() {
		require_once( $this->plugin_path() . 'vendor/webdevstudios/cmb2/init.php' );
		WPAS_Remote_Tickets\Gadgets::get_instance();
		WPAS_Remote_Tickets\Authentication::get_instance();
	}


	/** Actions ******************************************************/

	/**
	 * Load this plugins text domain
	 */
	public function load_text_domain() {

		// Set filter for plugin's languages directory
		$wpas_remote_tickets_lang_dir = dirname( plugin_basename( $this->plugin_file() ) ) . '/languages/';
		$wpas_remote_tickets_lang_dir = apply_filters( 'wpas_remote_tickets_languages_directory', $wpas_remote_tickets_lang_dir );


		// Traditional WordPress plugin locale filter

		$get_locale = get_locale();

		if ( function_exists( 'get_user_locale' ) ) {
			$get_locale = get_user_locale();
		}

		/**
		 * Defines the plugin language locale used in RCP.
		 *
		 * @var string $get_locale The locale to use. Uses get_user_locale()` in WordPress 4.7 or greater,
		 *                  otherwise uses `get_locale()`.
		 */
		$locale = apply_filters( 'plugin_locale', $get_locale, 'awesome-support-remote-tickets' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'awesome-support-remote-tickets', $locale );

		// Setup paths to current locale file
		$mofile_local  = $wpas_remote_tickets_lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/awesome-support-remote-tickets/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/awesome-support-remote-tickets folder
			load_textdomain( 'awesome-support-remote-tickets', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/awesome-support-remote-tickets/languages/ folder
			load_textdomain( 'awesome-support-remote-tickets', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'awesome-support-remote-tickets', false, $wpas_remote_tickets_lang_dir );
		}

	}

	/**
	 * Display error.
	 *
	 * Get all the error messages and display them
	 * in the admin notices.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function display_error() {

		if ( ! is_a( $this->error, 'WP_Error' ) ) {
			return;
		}

		$message = $this->error->get_error_messages(); ?>
		<div class="error">
			<p>
				<?php
				if ( count( $message ) > 1 ) {

					echo '<ul>';

					foreach ( $message as $msg ) {
						echo "<li>$msg</li>";
					}

					echo '</li>';

				} else {
					echo $message[0];
				}
				?>
			</p>
		</div>
	<?php

	}

	/** Filters ******************************************************/

	/** Helper methods ******************************************************/

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Returns __FILE__
	 *
	 * @since 1.0.0
	 * @return string the full path and filename of the plugin file
	 */
	protected function plugin_file() {
		return __FILE__;
	}

	/**
	 * Returns path to plugin directory
	 *
	 * @return string
	 */
	public function plugin_path() {
		return plugin_dir_path( $this->plugin_file() );
	}

	/**
	 * Returns url to plugin directory
	 *
	 * @return string
	 */
	public function plugin_url() {
		return trailingslashit( plugin_dir_url( $this->plugin_file() ) );
	}

	/**
	 * Get the plugin data.
	 *
	 * @since  1.0.0
	 * @param  string $data Plugin data to retrieve
	 * @return string       Data value
	 */
	protected function plugin_data( $data ) {

		if ( ! function_exists( 'get_plugin_data' ) ) {

			$site_url = get_site_url() . '/';

			if ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN && 'http://' === substr( $site_url, 0, 7 ) ) {
				$site_url = str_replace( 'http://', 'https://', $site_url );
			}

			$admin_path = str_replace( $site_url, ABSPATH, get_admin_url() );

			require_once( $admin_path . 'includes/plugin.php' );

		}

		$plugin = get_plugin_data( $this->plugin_file(), false, false );

		if ( array_key_exists( $data, $plugin ) ) {
			return $plugin[$data];
		} else {
			return '';
		}

	}

	/**
	 * Check if core is active.
	 *
	 * Checks if the core plugin is listed in the active
	 * plugins in the WordPress database.
	 *
	 * @since  1.0.0
	 * @return boolean Whether or not the core is active
	 */
	protected function is_core_active() {
		if ( in_array( 'awesome-support/awesome-support.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if the core version is compatible with this addon.
	 *
	 * @since  1.0.0
	 * @return boolean
	 */
	protected function is_version_compatible() {

		/**
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
	 * Check if the version of PHP is compatible with this addon.
	 *
	 * @since  1.0.0
	 * @return boolean
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
	 * Add error.
	 *
	 * Add a new error to the WP_Error object
	 * and create the object if it doesn't exist yet.
	 *
	 * @since  1.0.0
	 * @param string $message Error message to add
	 * @return void
	 */
	public function add_error( $message ) {

		if ( ! is_object( $this->error ) || ! is_a( $this->error, 'WP_Error' ) ) {
			$this->error = new WP_Error();
		}

		$this->error->add( 'addon_error', $message );

	}

	/** Lifecycle methods ******************************************************/

	/**
	 * Add license option.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $licenses List of addons licenses
	 *
	 * @return array           Updated list of licenses
	 */
	public function addon_license( $licenses ) {
		$plugin_name = $this->plugin_data( 'Name' );
		$plugin_name = trim( str_replace( 'Awesome Support:', '', $plugin_name ) ); // Remove the Awesome Support prefix from the addon name
		$licenses[]  = array(
			'name'      => $plugin_name,
			'id'        => "license_{$this->slug}",
			'type'      => 'edd-license',
			'default'   => '',
			'server'    => esc_url( 'http://getawesomesupport.com' ),
			'item_name' => $plugin_name,
			'item_id'   => $this->item_id,
			'file'      => __FILE__
		);

		return $licenses;
	}

	/**
	 * Display notice if user didn't set his Envato license code
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_license_notice() {

		/**
		 * We only want to display the notice to the site admin.
		 */
		if ( ! current_user_can( 'administrator' ) ) {
			return;
		}

		$license = wpas_get_option( "license_{$this->slug}", '' );

		/**
		 * Do not show the notice if the license key has already been entered.
		 */
		if ( ! empty( $license ) ) {
			return;
		}

		$link = wpas_get_settings_page_url( 'licenses' );
		WPAS()->admin_notices->add_notice( 'error', "lincense_{$this->slug}", sprintf( __( 'Please <a href="%s">fill-in your product license</a> now. If you don\'t, your copy of Awesome Support: Remote Tickets <strong>will never be updated</strong>.', 'wpbp' ), $link ) );
	}

	/**
	 * Add license warning in the plugin meta row
	 *
	 * @since 1.0.0
	 *
	 * @param array  $plugin_meta The current plugin meta row
	 * @param string $plugin_file The plugin file path
	 *
	 * @return array Updated plugin meta
	 */
	public function license_notice_meta( $plugin_meta, $plugin_file ) {

		$license = wpas_get_option( "license_{$this->slug}", '' );

		if ( ! empty( $license ) ) {
			return $plugin_meta;
		}

		$license_page = add_query_arg( array( 'post_type' => 'ticket', 'page' => 'wpas-settings', 'tab' => 'licenses' ), admin_url( 'edit.php' ) );

		if ( plugin_basename( __FILE__ ) === $plugin_file ) {
			$plugin_meta[] = '<strong>' . sprintf( __( 'You must fill-in your product license in order to get future plugin updates. <a href="%s">Click here to do it</a>.', 'wpas' ), $license_page ) . '</strong>';
		}

		return $plugin_meta;
	}


	/**
	 * Handle plugin activation
	 *
	 * @since 1.0.0
	 */
	public static function maybe_activate() {

		if ( ! class_exists( 'Awesome_Support' ) ) {
			deactivate_plugins( basename( __FILE__ ) );
			wp_die( sprintf( __( 'You need Awesome Support to activate this addon. Please <a href="%s" target="_blank">install Awesome Support</a> before continuing.', 'wpascr' ), esc_url( 'http://getawesomesupport.com/?utm_source=internal&utm_medium=addon_loader&utm_campaign=Addons' ) ) );
		}

		$is_active = get_option( 'wpas_remote_tickets_is_active', false );

		if ( ! $is_active ) {

			update_option( 'wpas_remote_tickets_is_active', true );

			/**
			 * Run when AvaTax is activated.
			 *
			 * @since 1.0.0
			 */
			do_action( 'wpas_remote_tickets_activated' );
		}

	}


	/**
	 * Handle plugin deactivation
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		if ( function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( basename( __FILE__ ) );
		}

		delete_option( 'wpas_remote_tickets_is_active' );

		/**
		 * Run when AvaTax is deactivated
		 *
		 * @since 1.0.0
		 */
		do_action( 'wpas_remote_tickets_deactivated' );
	}


} // end WPAS_Remote_Tickets class


/**
 * Returns the One True Instance of WPAS_Remote_Tickets
 *
 * @since 1.0.0
 * @return object | WPAS_Remote_Tickets
 */
function wpas_remote_tickets() {
	return WPAS_Remote_Tickets::get_instance();
}