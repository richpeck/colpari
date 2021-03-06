<?php
/**
 * @package   Awesome Support: Custom FAQs
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      http://getawesomesupport.com
 * @copyright 2014 Awesome Support
 *
 * @boilerplate-version   0.1.3
 *
 * Plugin Name:       Awesome Support: Custom FAQ
 * Plugin URI:        http://getawesomesupport.com/addons/?utm_source=internal&utm_medium=plugin_meta&utm_campaign=Addon_CUSTOMFAQ
 * Description:       An Awesome Support add-on that allows you to use any custom post type as an Awesome Support FAQ - with full integration into the back-end and front-end ticket screens.
 * Version:           1.0.2
 * Author:            Awesome Support
 * Author URI:        http://getawesomesupport.com/?utm_source=internal&utm_medium=plugin_meta&utm_campaign=Addon_CUSTOMFAQ
 * Text Domain:       as-customfaq
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
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
register_activation_hook( __FILE__, array( 'AS_CUSTOMFAQ_Loader', 'activate' ) );

add_action( 'plugins_loaded', array( 'AS_CUSTOMFAQ_Loader', 'get_instance' ) );
/**
 * Instantiate the addon.
 *
 * This method runs a few checks to make sure that the addon
 * can indeed be used in the current context, after what it
 * registers the addon to the core plugin for it to be loaded
 * when the entire core is ready.
 *
 * @since  1.0.0
 * @return void
 */
class AS_CUSTOMFAQ_Loader {

	/**
	 * ID of the item.
	 *
	 * The item ID must match teh post ID on the e-commerce site.
	 * Using the item ID instead of its name has the huge advantage of
	 * allowing changes in the item name.
	 *
	 * If the ID is not set the class will fall back on the plugin name instead.
	 *
	 * @since 0.1.3
	 * @var int
	 */
	protected $item_id = 840324;

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
	protected $version_required = '3.1.6';

	/**
	 * Required version of PHP.
	 *
	 * Follow WordPress latest requirements and require
	 * PHP version 5.4 at least.
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
	protected $slug = 'customfaq';

	/**
	 * Instance of this loader class.
	 *
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Instance of the addon itself.
	 *
	 * @since  1.0.0
	 * @var    object
	 */
	public $addon = null;

	/**
	 * Possible error message.
	 *
	 * @var null|WP_Error
	 */
	protected $error = null;

	public function __construct() {
		$this->init();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     3.0.0
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
	 * Return an instance of the addon.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	public function scope() {
		return $this->addon;
	}

	public function declare_constants() {
		define( 'AS_CUSTOMFAQ_VERSION', '1.0.2' );
		define( 'AS_CUSTOMFAQ_URL',     trailingslashit( plugin_dir_url( __FILE__ ) ) );
		define( 'AS_CUSTOMFAQ_PATH',    trailingslashit( plugin_dir_path( __FILE__ ) ) );
	}

	/**
	 * Include all the addon files
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function includes() {
		require( AS_CUSTOMFAQ_PATH . 'includes/functions-search.php' );
		require( AS_CUSTOMFAQ_PATH . 'includes/functions-customfaq.php' );
		require( AS_CUSTOMFAQ_PATH . 'includes/functions-misc.php' );
		require( AS_CUSTOMFAQ_PATH . 'includes/settings.php' );
		require( AS_CUSTOMFAQ_PATH . 'includes/integrations/awesome-support.php' );

		if ( is_admin() ) {
			require( AS_CUSTOMFAQ_PATH . 'includes/functions-quick-reply.php' );
		}
	}

	/**
	 * Activate the plugin.
	 *
	 * The activation method just checks if the main plugin
	 * Awesome Support is installed (active or inactive) on the site.
	 * If not, the addon installation is aborted and an error message is displayed.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function activate() {

		if ( ! class_exists( 'Awesome_Support' ) ) {
			deactivate_plugins( basename( __FILE__ ) );
			wp_die(
				sprintf( __( 'You need Awesome Support to activate this addon. Please <a href="%s" target="_blank">install Awesome Support</a> before continuing.', 'as-customfaq' ), esc_url( 'http://getawesomesupport.com/?utm_source=internal&utm_medium=addon_loader&utm_campaign=Addons' ) )
			);
		}

		// Load activation functions
		require( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/install.php' );

		// Trigger the activation function	
		ascustomfaq_activate();

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
			$this->add_error( sprintf( __( '%s requires Awesome Support to be active. Please activate the core plugin first.', 'as-customfaq' ), $plugin_name ) );
		}

		if ( ! $this->is_php_version_enough() ) {
			$this->add_error( sprintf( __( 'Unfortunately, %s can not run on PHP versions older than %s. Read more information about <a href="%s" target="_blank">how you can update</a>.', 'as-customfaq' ), $plugin_name, $this->php_version_required, esc_url( 'http://www.wpupdatephp.com/update/' ) ) );
		}

		if ( ! $this->is_version_compatible() ) {
			$this->add_error( sprintf( __( '%s requires Awesome Support version %s or greater. Please update the core plugin first.', 'as-customfaq' ), $plugin_name, $this->version_required ) );
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
			add_filter( 'wpas_addons_licenses', array( $this, 'addon_license' ),       10, 1 );
			add_action( 'admin_notices',        array( $this, 'license_notice' ),      10, 0 );
			add_filter( 'plugin_row_meta',      array( $this, 'license_notice_meta' ), 10, 4 );
		}

		// Finish initializing the addon
		$this->declare_constants();
		$this->includes();

		/**
		 * Register the addon
		 */
		wpas_register_addon( $this->slug, array( $this, 'load' ) );

		// Load textdomain
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ), 15 );

		return true;

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

		$plugin = get_plugin_data( __FILE__, false, false );

		if ( array_key_exists( $data, $plugin ) ){
			return $plugin[$data];
		} else {
			return '';
		}

	}

	/**
	 * Check if core is active.
	 *
	 * Checks if the core plugin is listed in the acitve
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

	/**
	 * Deactivate the addon.
	 *
	 * If the requirements aren't met we try to
	 * deactivate the addon completely.
	 *
	 * @return void
	 */
	public function deactivate() {
		if ( function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( basename( __FILE__ ) );
		}
	}

	/**
	 * Add license option.
	 *
	 * @since  1.0.0
	 * @param  array $licenses List of addons licenses
	 * @return array           Updated list of licenses
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
	public function license_notice() {

		/**
		 * We only want to display the notice to the site admin.
		 */
		if ( ! current_user_can( 'administrator' ) ) {
			return;
		}

		/**
		 * If the notice has already been dismissed we don't display it again.
		 */
		if ( wpas_is_notice_dismissed( "license_{$this->slug}" ) ) {
			return;
		}

		$license   = wpas_get_option( "license_{$this->slug}", '' );

		/**
		 * Do not show the notice if the license key has already been entered.
		 */
		if ( ! empty( $license ) ) {
			return;
		}

		/* Prepare the dismiss URL */
		$args              = $_GET;
		$args['notice_id'] = 'license_' . $this->slug;
		$url               = wpas_do_url( '', 'dismiss_notice', $args );
		$license_page      = wpas_get_settings_page_url( 'licenses' ); ?>

		<div class="updated error">
			<p><?php printf( __( 'Please <a href="%s">fill-in your product license</a> now. If you don\'t, your copy of Awesome Support: Custom FAQ <strong>will never be updated</strong>.', 'as-customfaq' ), $license_page ); ?>
				<a href="<?php echo $url; ?>"><small>(<?php _e( 'I do NOT want the updates, dismiss this message', 'as-customfaq' ); ?>)</small></a></p>
		</div>

	<?php }

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

		$license   = wpas_get_option( "license_{$this->slug}", '' );

		if( ! empty( $license ) ) {
			return $plugin_meta;
		}

		$license_page = add_query_arg( array( 'post_type' => 'ticket', 'page' => 'settings', 'tab' => 'licenses' ), admin_url( 'edit.php' ) );

		if ( plugin_basename( __FILE__ ) === $plugin_file ) {
			$plugin_meta[] = '<strong>' . sprintf( __( 'You must fill-in your product license in order to get future plugin updates. <a href="%s">Click here to do it</a>.', 'as-customfaq' ), $license_page ) . '</strong>';
		}

		return $plugin_meta;
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @since 0.1.6
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'as-customfaq', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
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

		// Load the addon here.

	}

}