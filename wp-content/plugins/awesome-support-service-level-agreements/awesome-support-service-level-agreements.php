<?php
/**
 * @package   Awesome Support: Service Level Agreements
 * @author    AwesomeSupport
 * @link      http://www.getawesomesupport.com
 *
 * Plugin Name:       Awesome Support: Service Level Agreements
 * Plugin URI:        
 * Description:       A sophisticated add-on to the Awesome Support WordPress Helpdesk Plugin that helps you to easily manage contractual obligations to your customers involving response times to tickets.
 * Version:           1.0.3
 * Author:            Awesome Support
 * Author URI:        http://www.getawesomesupport.com
 * Text Domain:       wpas_sla
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Instantiate the plugin
 *----------------------------------------------------------------------------*/
/**
 * Register the activation hook
 */

register_activation_hook( __FILE__, array( 'WPAS_Service_Level_Agreements', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WPAS_Service_Level_Agreements', 'deactivate' ) ) ;
add_action( 'plugins_loaded', array( 'WPAS_Service_Level_Agreements', 'get_instance' ), 12 );
/**
 * Instantiate the addon.
 *
 * This method runs a few checks to make sure that the addon
 * can indeed be used in the current context, after what it
 * registers the addon to the core plugin for it to be loaded
 * when the entire core is ready.
 *
 * @since  0.1.0
 * @return void
 */
class WPAS_Service_Level_Agreements {
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
	protected $item_id = '1164686';

	/**
	 * Required version of the core.
	 *
	 * The minimum version of the core that's required
	 * to properly run this addon. If the minimum version
	 * requirement isn't met an error message is displayed
	 * and the addon isn't registered.
	 *
	 * @since  0.1.0
	 * @var    string
	 */
	protected $version_required = '4.0.0';

	/**
	 * Required version of PHP.
	 *
	 * @var string
	 */
	protected $php_version_required = '5.6';

	/**
	 * Plugin slug.
	 *
	 * @since  0.1.0
	 * @var    string
	 */
	protected $slug = 'wpas_wpas_sla';

	/**
	 * Instance of this loader class.
	 *
	 * @since    0.1.0
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Instance of the addon itself.
	 *
	 * @since  0.1.0
	 * @var    object
	 */
	public $addon = null;

	/**
	 * Possible error message.
	 * 
	 * @var null|WP_Error
	 */
	protected $error = null;

	/**
	 * WPAS_Chat_Bot constructor
	 */
	public function __construct() {
		$this->declare_constants();
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
	 * @since  0.1.0
	 * @return object
	 */
	public function scope() {
		return $this->addon;
	}

	/**
	 * Declare addon constants
	 *
	 * @since 1.0.0
	 * @return void
	 */
	
	
	public function declare_constants() {
		define( 'WPAS_SLA_VERSION', '1.0.3' );
		define( 'WPAS_SLA_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
		define( 'WPAS_SLA_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'WPAS_SLA_ROOT', trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) );
		define( 'WPAS_SLA_NS', 'wpassla_' );
		
		define( 'SLA_DATE_FORMAT', 'Y-m-d' );
		define( 'SLA_TIME_FORMAT', 'H:i:s' );
	}

	/**
	 * Activate the plugin.
	 *
	 * The activation method just checks if the main plugin
	 * Awesome Support is installed (active or inactive) on the site.
	 * If not, the addon installation is aborted and an error message is displayed.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public static function activate() {
		if ( ! class_exists( 'Awesome_Support' ) ) {
			deactivate_plugins( basename( __FILE__ ) );
			wp_die(
				sprintf( __( 'You need Awesome Support to activate this addon. Please <a href="%s" target="_blank">install Awesome Support</a> before continuing.', 'wpas_sla' ), esc_url( 'https://getawesomesupport.com/?utm_source=internal&utm_medium=addon_loader&utm_campaign=Addons' ) )
			);
		} else {
			
			global $wpdb;

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			$table_name = $wpdb->prefix . "sla_ticket_alerts";
			
			$table = "CREATE TABLE IF NOT EXISTS {$table_name} (
						`id` int(11) AUTO_INCREMENT primary key NOT NULL,
						`sla_id` int(11) NOT NULL,
						`ticket_id` int(11) NOT NULL,
						`alert_id` int(11) NOT NULL,
						`alert_due_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
						`sent` int(11) NOT NULL DEFAULT '0',
						`process_id` varchar(20)
					) ENGINE=InnoDB;";
			dbDelta( $table );
			
			
			$capability_roles = array(
				'ticket_sla_admin' => array(
					'administrator',
					'wpas_support_manager',
					'wpas_manager'
				),
				'ticket_edit_due_date' => array(
					'administrator',
					'wpas_support_manager',
					'wpas_manager'
				)
			);
			
			
			foreach( $capability_roles as $cap => $roles ) {
				
				foreach( $roles as $r ) {
					$role = get_role( $r );
					if (! empty( $role ) ) {
						$role->add_cap( $cap );
					}
				}
			}
			
			$all_users = get_users();
			foreach( $all_users as $user ){
				if( $user->has_cap( 'administer_awesome_support' ) ) {
					$user->add_cap( 'ticket_sla_admin' );
					$user->add_cap( 'ticket_edit_due_date' );
				}

			}
			
		}
	}

	/**
	 * Initialize the addon.
	 *
	 * This method is the one running the checks and
	 * registering the addon to the core.
	 *
	 * @since  0.1.0
	 * @return boolean Whether or not the addon was registered
	 */
	public function init() {

		$plugin_name = $this->plugin_data( 'Name' );

		if ( ! $this->is_core_active() ) {
			$this->add_error( sprintf( __( '%s requires Awesome Support to be active. Please activate the core plugin first.', 'wpas_sla' ), $plugin_name ) );
		}

		if ( ! $this->is_php_version_enough() ) {
			$this->add_error( sprintf( __( 'Unfortunately, %s can not run on PHP versions older than %s. Read more information about <a href="%s" target="_blank">how you can update</a>.', 'wpas_sla' ), $plugin_name, $this->php_version_required, esc_url( 'http://www.wpupdatephp.com/update/' ) ) );
		}

		if ( ! $this->is_version_compatible() ) {
			$this->add_error( sprintf( __( '%s requires Awesome Support version %s or greater. Please update the core plugin first.', 'wpas_sla' ), $plugin_name, $this->version_required ) );
		}

		// Load the plugin translation.
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ), 15 );

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
		wpas_register_addon( $this->slug, array( $this, 'load' ) );

		return true;

	}

	/**
	 * Get the plugin data.
	 *
	 * @since  0.1.0
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
	 * Checks if the core plugin is listed in the active
	 * plugins in the WordPress database.
	 *
	 * @since  0.1.0
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
	 * @since  0.1.0
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
	 * @since  0.1.0
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
	 * @since  0.1.0
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
	 * @since  0.1.0
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
	public static function deactivate() {
		if ( function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( basename( __FILE__ ) );
		}
		
	}

	/**
	 * Add license option.
	 *
	 * @since  0.1.0
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
			'item_name' => $plugin_name,
			'item_id'   => $this->item_id,
			'file'      => __FILE__
		);
			
			
		return $licenses;
	}

	/**
	 * Display notice if user didn't set his license code
	 *
	 * @since 0.1.4
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

		WPAS()->admin_notices->add_notice( 'error', "license_{$this->slug}", sprintf( __( 'Please <a href="%s">fill-in your product license</a> now. If you don\'t, your copy of Awesome Support: Service Level Agreements <strong>will never be updated</strong>.', 'wpas_sla' ), $link ) );

	}

	/**
	 * Add license warning in the plugin meta row
	 *
	 * @since 0.1.0
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

		$license_page = wpas_get_settings_page_url( 'licenses' );

		if ( plugin_basename( __FILE__ ) === $plugin_file ) {
			$plugin_meta[] = '<strong>' . sprintf( __( 'You must fill-in your product license in order to get future plugin updates. <a href="%s">Click here to do it</a>.', 'wpas_sla' ), $license_page ) . '</strong>';
		}
		
		return $plugin_meta;

	}

	/**
	 * Load the addon.
	 *
	 * Include all necessary files and instantiate the addon.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function load() {

		require_once WPAS_SLA_PATH . 'includes/settings.php';
		require_once WPAS_SLA_PATH . 'includes/post-types/sla.php';
		require_once WPAS_SLA_PATH . 'includes/post-types/holiday.php';
		require_once WPAS_SLA_PATH . 'includes/functions.php';
		require_once WPAS_SLA_PATH . 'includes/class_due_date.php';
		
		require_once WPAS_SLA_PATH . 'includes/class_ticket_alert.php';
		
		require_once WPAS_SLA_PATH . 'includes/cron_settings.php';
		require_once WPAS_SLA_PATH . 'includes/class_alert_cron.php';
		
		require_once WPAS_SLA_PATH . 'includes/reports.php';
		
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 99 );
		
	}
	

	/**
	 * Enqueue addon assets
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		if ( is_admin() ) {

			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'wpas-datepicker' );
			wp_enqueue_script( 'wpas-sla-script', WPAS_SLA_URL . 'assets/js/script.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ) );
			wp_enqueue_style( 'wpas-sla-style', WPAS_SLA_URL . 'assets/css/style.css' );
			
		}
	}
	

	/**
	 * Load the plugin text domain for translation.
	 *
	 * With the introduction of plugins language packs in WordPress loading the textdomain is slightly more complex.
	 *
	 * We now have 3 steps:
	 *
	 * 1. Check for the language pack in the WordPress core directory
	 * 2. Check for the translation file in the plugin's language directory
	 * 3. Fallback to loading the textdomain the classic way
	 *
	 * @since   1.0.4
	 * @return boolean True if the language file was loaded, false otherwise
	 */
	public function load_plugin_textdomain() {

		$lang_dir       = WPAS_SLA_ROOT . 'languages/';
		$lang_path      = WPAS_SLA_PATH . 'languages/';
		$locale         = apply_filters( 'plugin_locale', get_locale(), 'wpas_sla' );
		$mofile         = "wpas_sla-$locale.mo";
		$glotpress_file =  WPAS_SLA_PATH . $mofile;

		// Look for the GlotPress language pack first of all
		if ( file_exists( $glotpress_file ) ) {
			$language = load_textdomain( 'wpas_sla', $glotpress_file );
		} elseif ( file_exists( $lang_path . $mofile ) ) {
			$language = load_textdomain( 'wpas_sla', $lang_path . $mofile );
		} else {
			$language = load_plugin_textdomain( 'wpas_sla', false, $lang_dir );
		}

		return $language;

	}

}