<?php
/**
 * @package   Awesome Support: Company Profiles
 * @author    AwesomeSupport
 * @link      http://www.getawesomesupport.com
 *
 * Plugin Name:       Awesome Support: Company Profiles
 * Plugin URI:        
 * Description:       This add-on to the Awesome Support WordPress Helpdesk Plugin enables you to allow multiple users within an organization to view and update each others tickets on the front-end.
 * Version:           1.0.6
 * Author:            Awesome Support Team
 * Author URI:        
 * Text Domain:       wpas_cp
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// Include extension base class
if ( !class_exists( 'WPAS_Extension_Base' ) ) {
	
	$wpas_dir = defined( 'WPAS_ROOT' )  ? WPAS_ROOT : ( defined( 'WPAS_AS_FOLDER' ) ? WPAS_AS_FOLDER : 'awesome-support' );
	$wpas_eb_file = trailingslashit( WP_PLUGIN_DIR . '/' . $wpas_dir ) . 'includes/class-extension-base.php';
	
	if( file_exists( $wpas_eb_file ) ) {
		require_once ( $wpas_eb_file );
	} else {
		add_action( 'admin_notices', function() {
		?>	
		
		<div class="error">
			<p>
				<?php printf( __( 'You need Awesome Support to activate Company Profiles addon. Please <a href="%s" target="_blank">install Awesome Support</a> before continuing.', 'wpas_cp' ), esc_url( 'http://getawesomesupport.com/?utm_source=internal&utm_medium=addon_loader&utm_campaign=Addons' ) ); ?>
			</p>
		</div>
			
		<?php	
			
		});
		
		return;
		
	}
}

/*----------------------------------------------------------------------------*
 * Instantiate the plugin
 *----------------------------------------------------------------------------*/
/**
 * Register the activation hook
 */

register_activation_hook( __FILE__, array( 'WPAS_Company_Profiles', 'activate' ) );

add_action( 'plugins_loaded', array( 'WPAS_Company_Profiles', 'get_instance' ), 12 );
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
class WPAS_Company_Profiles extends WPAS_Extension_Base {
	
	/**
	 * Instance of this loader class.
	 *
	 * @since    0.1.0
	 * @var      object
	 */
	protected static $instance = null;


	public function __construct() {
		
		
		$this->setVersionRequired( '5.1.0' );			// Set required version of core
		$this->setPhpVersionRequired( '5.6' );			// Set required version of php
		$this->setSlug( 'wpas_company_profiles' );		// Set addon slug
		$this->setUid( 'CP' );							// Set short unique id
		$this->setTextDomain( 'wpas_cp' );				// Set text domain for translation
		$this->setVersion( '1.0.6' );					// Set addon version
		$this->setItemId( 1204655 );					// Set addon item id
		
		parent::__construct();
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
				sprintf( __( 'You need Awesome Support to activate this addon. Please <a href="%s" target="_blank">install Awesome Support</a> before continuing.', 'wpas_it' ), esc_url( 'https://getawesomesupport.com/?utm_source=internal&utm_medium=addon_loader&utm_campaign=Addons' ) )
			);
		} else {
			update_option( 'wpas_cp_configured', 'no' );
			
			
			global $wpdb;

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			$su_table_name = $wpdb->prefix . "company_support_users";
			
			$su_table = "CREATE TABLE IF NOT EXISTS {$su_table_name} (
						`id` int(11) AUTO_INCREMENT primary key NOT NULL,
						`profile_id` int(11) NOT NULL,
						`user_id` int(11) NOT NULL,
						`user_type` varchar(40) NOT NULL,
						`divisions` varchar(200) NOT NULL,
						`reporting_group` int(11) NOT NULL,
						`primary` int(11) NOT NULL DEFAULT '0',
						`can_reply_ticket` int(11) NOT NULL DEFAULT '0',
						`can_close_ticket` int(11) NOT NULL DEFAULT '0',
						`can_open_ticket` int(11) NOT NULL DEFAULT '0',
						`can_manage_profile` int(11) NOT NULL DEFAULT '0'
					) ENGINE=InnoDB;";
			dbDelta( $su_table );
			
			
			
			$log_table_name = $wpdb->prefix . "company_logs";
			
			$log_table = "CREATE TABLE IF NOT EXISTS {$log_table_name} (
						`id` int(11) AUTO_INCREMENT primary key NOT NULL,
						`company_id` int(11) NOT NULL,
						`log_type` varchar(100) NOT NULL,
						`date` datetime NOT NULL,
						`content` text NOT NULL,
						`status` varchar(200) NOT NULL
					) ENGINE=InnoDB;";
			
			dbDelta( $log_table );
			
			
			$capability_roles = array(
				'ticket_manage_company_profiles' => array(
					'administrator',
					'wpas_manager',
					'wpas_support_manager',
					'wpas_agent'
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
			
		}
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
		
		require_once WPAS_CP_PATH . 'includes/class_post_meta.php';
		require_once WPAS_CP_PATH . 'includes/class_company_support_user.php';

		require_once WPAS_CP_PATH . 'includes/functions.php';

		require_once WPAS_CP_PATH . 'includes/post-types/company_profile.php';
		
		require_once WPAS_CP_PATH . 'includes/company_support_user.php';
		
		require_once WPAS_CP_PATH . 'includes/pages/add_company_profile.php';
		require_once WPAS_CP_PATH . 'includes/pages/manage_company_profiles.php';
		
		require_once WPAS_CP_PATH . 'includes/taxonomies.php';
		
		require_once WPAS_CP_PATH . 'includes/class_log.php';
		
		WPAS_CP_Support_User::get_instance();
		
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10 );
		
	}

	
	/**
	 * Enqueue addon assets
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		if ( is_admin() ) {
			
			wp_enqueue_script( 'wpas-magnific-script', WPAS_CP_URL . 'assets/js/jquery.magnific-popup.min.js', array( 'jquery' ) );
			
			wp_enqueue_script( 'wpas-cp-script', WPAS_CP_URL . 'assets/js/script.js', array( 'jquery' ) );
			
			wp_enqueue_style( 'wpas-cp-mp-style', WPAS_CP_URL . 'assets/css/magnific-popup.css' );
			wp_enqueue_style( 'wpas-cp-style', WPAS_CP_URL . 'assets/css/style.css' );
			
			$localize_script = array(
				'save_btn_label' => __( 'Save', 'wpas_cp' )
			);

			if( !empty($localize_script) ) {
				wp_localize_script( 'wpas-cp-script', 'wpas_cp', $localize_script );
			}
			
		}
	}

}