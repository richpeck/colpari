<?php
/**
 * @package   Awesome Support Reports And Statistics
 * @author    Awesome Support <https://www.getawesomesupport.com>
 * @license   GPL-2.0+	
 * @link      https://www.getawesomesupport.com
 * @copyright 2016
 * 
 * @wordpress-plugin
 *
 * Plugin Name:       Awesome Support: Reports And Statistics
 * Plugin URI:        
 * Description:       Create sophisticated decision-making charts and tables from your support data. Start with 6 core reports and build your own from there!
 * Version:           1.2.0
 * Author:            Awesome Support
 * Author URI:        https://www.getawesomesupport.com
 * Text Domain:       reports-and-statistics
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
register_activation_hook( __FILE__, array( 'Reports_And_Statistics', 'activate' ) );

add_action( 'plugins_loaded', array( 'Reports_And_Statistics', 'get_instance' ) );

register_deactivation_hook(__FILE__, array( 'Reports_And_Statistics', 'deactivate' ));
/**
 * Instanciate the addon.
 *
 * This method runs a few checks to make sure that the addon
 * can indeed be used in the current context, after what it
 * registers the addon to the core plugin for it to be loaded
 * when the entire core is ready.
 *
 * @since  0.1.0
 * @return void
 */
class Reports_And_Statistics{

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
	protected $item_id = 853357;

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
	 * Follow WordPress latest requirements and require
	 * PHP version 5.4 at least.
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
	protected $slug = 'reports_and_statistics';

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

	public function __construct() {
		$this->declare_constants();
		$this->init();
		add_action('init', array($this, 'load_my_transl'));
		add_action('init', array($this, 'rns_register_post_type'));
		
	}

	 public function load_my_transl()  {
		 
        load_plugin_textdomain('reports-and-statistics', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
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

	public function declare_constants() {
		define( 'AS_RAS_VERSION', '1.2.0' );
		define( 'AS_RAS_URL',     trailingslashit( plugin_dir_url( __FILE__ ) ) );
		define( 'AS_RAS_PATH',    trailingslashit( plugin_dir_path( __FILE__ ) ) );
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
				sprintf( __( 'You need Awesome Support to activate this addon. Please <a href="%s" target="_blank">install Awesome Support</a> before continuing.', 'wpascr' ), esc_url( 'http://getawesomesupport.com/?utm_source=internal&utm_medium=addon_loader&utm_campaign=Addons' ) )
			);
		}
		
		// Add default value of interval in wordpress option
		add_option( 'rns_chart_interval', '10', '', 'yes' );
		add_option( 'rns_basic_interval', '10', '', 'yes' );
		add_option( 'rns_productivity_interval', '10', '', 'yes' );
		add_option( 'rns_resolution_interval', '10', '', 'yes' );
		add_option( 'rns_delay_interval', '10', '', 'yes' );
		add_option( 'rns_distribution_interval', '10', '', 'yes' );
		add_option( 'rns_trend_interval', '10', '', 'yes' );
		

		$def_cust_fields = "assignee,status,product,department,ticket_priority,ticket_channel";
		
		// save  roles who can save  report
		add_option( 'wpas_reports_statistics_custom_fields' , $def_cust_fields );
		
		$menu_roles = "administrator,wpas_manager,wpas_support_manager"; 
		
		// save  roles who can save  report
		add_option( 'rns_save_roles' , $menu_roles );
		
		// save  roles who can delete  report
		add_option( 'rns_delete_roles' , $menu_roles );
		
		// save roles who can see report menu 
		add_option( 'rns_report_menu_roles' , $menu_roles );
	}
	
	
	/**
	 * Register the report post type.
	 *
	 * @since 1.0.2
	 */
	public function rns_register_post_type() {
	
	
		/* Post type labels */
		$args =  array(
	
					'labels' => array(
	
						'name' => __( 'Saved Report' ),
	
						'singular_name' => __( 'Saved Report' )
	
					),
	
					'public'		 => true,
					'show_in_menu'   => false,
					'has_archive'    => true,
					'rewrite'        => array('slug' => 'rns_report'),
	
				);
		
	
		register_post_type( 'rns_report', $args );
	
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
			$this->add_error( sprintf( __( '%s requires Awesome Support to be active. Please activate the core plugin first.', 'reports-and-statistics' ), $plugin_name ) );
		}

		if ( ! $this->is_php_version_enough() ) {
			$this->add_error( sprintf( __( 'Unfortunately, %s can not run on PHP versions older than %s. Read more information about <a href="%s" target="_blank">how you can update</a>.', 'reports-and-statistics' ), $plugin_name, $this->php_version_required, esc_url( 'http://www.wpupdatephp.com/update/' ) ) );
		}

		if ( ! $this->is_version_compatible() ) {
			$this->add_error( sprintf( __( '%s requires Awesome Support version %s or greater. Please update the core plugin first.', 'reports-and-statistics' ), $plugin_name, $this->version_required ) );
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
		$this->includes();
		/**
		 * Register the addon
		 */
		wpas_register_addon( $this->slug, array( $this, 'load' ) );

		return true;

	}
	
	/**
	 * Include all files used sitewide
	 *
	 * @since 3.2.5
	 * @return void
	 */
	private function includes() {
		
		require( AS_RAS_PATH . 'includes/reports-and-statistics-functions.php' );
		require( AS_RAS_PATH . 'settings-reports-and-statistics.php' );
		
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
			$admin_path = str_replace( get_site_url() . '/', ABSPATH, get_admin_url() );
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
	 * @since  0.1.0
	 * @return boolean Whether or not the core is active
	 */
	protected function is_core_active() {
		return ( in_array( 'awesome-support/awesome-support.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) ;
			
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
	
		//delete  roles option for save report  functionality
		delete_option( 'rns_save_roles'  );
		
		//delete  roles option for  delete report  functionality
		delete_option( 'rns_delete_roles'  );
		
		//delete report menu roles permission option
		delete_option( 'rns_report_menu_roles'  );
		
		// delete custom filed options
		delete_option( 'wpas_reports_statistics_custom_fields'  );
		
		
		if ( function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( basename( __FILE__ ) );
		}
		
	}

	/**
	 * Add license option.
	 *
	 * @since  0.1.0
	 * @param  array $licenses List of addons licenses
	 * @return array[] $licenses  Updated list of licenses
	 */
	public function addon_license( $licenses ) {

		$plugin_name = $this->plugin_data( 'Name' );
		$plugin_name = trim( str_replace( 'Awesome Support:', '', $plugin_name ) ); // Remove the Awesome Support prefix from the addon name

		$licenses[] = array(
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
		WPAS()->admin_notices->add_notice( 'error', "license_{$this->slug}", sprintf( __( 'Please <a href="%s">fill-in your product license</a> now. If you don\'t, your copy of Awesome Support: Advanced Reports And Statistics <strong>will never be updated</strong>.', 'wpbp' ), $link ) );

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

		$license_page = add_query_arg( array( 'post_type' => 'ticket', 'page' => 'settings', 'tab' => 'licenses' ), admin_url( 'edit.php' ) );

		if ( plugin_basename( __FILE__ ) === $plugin_file ) {
			$plugin_meta[] = '<strong>' . sprintf( __( 'You must fill-in your product license in order to get future plugin updates. <a href="%s">Click here to do it</a>.', 'wpas' ), $license_page ) . '</strong>';
		}
		
		return $plugin_meta;
	}

	/**
	 * Load the addon.
	 *
	 * Include all necessary files and instanciate the addon.
	 *
	 * @since  1.0
	 * @return void
	 */
	public function load() {

			add_action( 'admin_menu' , array( $this , 'add_report_menu' ) , 9 , 0);


	}
	
	/* Function to add menus*/
	public function add_report_menu() {
		
		$options = array( 'options' => array('default'=> 'dashboard' ) ); 
		$action = filter_input(INPUT_GET, 'action',  FILTER_SANITIZE_SPECIAL_CHARS, $options); 
		
		
		$subs = get_role( 'administrator' );
		
		if ( ! empty( $subs ) ) {
			$subs->add_cap( 'custom_menu_access' );
					
			$report_menu_Roles	= get_option( 'rns_report_menu_roles' , true );
			$report_menu_roles = explode( ",", $report_menu_Roles );	
			
			foreach( $report_menu_roles as $key=>$value ) {
				
				if( $value!='administrator' ) {
					
					$roles = get_role( $value );
					if( ! $roles ) {
						continue;
					}
					$roles->add_cap( 'custom_menu_access' );
				}
				
			}
		}
		
		$handle = add_submenu_page( 'edit.php?post_type=ticket' , __( 'Reports' , 'awesome-support' ), __( 'Reports', 'awesome-support' ) , 'custom_menu_access' , 'wpas-reports' , array( __CLASS__ , $action ) );
		add_action( 'admin_head-'.$handle, array( $this, 'enqueue_styles' ), 10, 0 );
		add_action( 'admin_head-'.$handle, array( $this, 'enqueue_scripts' ), 10, 0 );
		add_action( 'admin_head-'.$handle, array( $this, 'enqueue_scripts' ), 10, 0 );
		$settings_handle = add_submenu_page('edit.php?post_type=ticket' , __( 'Report Settings' , 'awesome-support' ), __( 'Report Settings', 'awesome-support') , 'administrator' , 'reports_settings' , array(__CLASS__, 'reports_settings')  );
		add_action( 'admin_head-'.$settings_handle, array( $this, 'enqueue_styles' ), 10, 0 );
	}
	
	/**
	 * Stylesheets to include on admin profile.php page
	 */
	public function enqueue_styles() {
				
		wp_enqueue_style( 'wp-reporting-style' , plugins_url( 'css/reports-style.css' , __FILE__ ) );
		wp_enqueue_style( 'wp-reporting-dt-style' , plugins_url( 'css/jquery.timepicker.css' , __FILE__ ) );
		wp_register_style( 'reporting-jquery-ui-css', plugins_url( 'css/jquery-ui.css' , __FILE__ ) );
		wp_enqueue_style( 'reporting-jquery-ui-css' );
		wp_enqueue_script( "reporting-jquery-ui-tabs" );
		
	}
	
	/**
	 * Scripts to include on admin profile.php page
	 */
	public function enqueue_scripts() {
		
		wp_enqueue_script( 'wp-reporting-script-utility' , plugins_url( 'js/utils.js' , __FILE__ ) );
		wp_enqueue_script( 'wp-reporting-script-datepickers-ui' , plugins_url( 'js/ui/jquery.ui.datepicker.js' , __FILE__ ) );
		wp_enqueue_script( 'wp-reporting-script-datepickers' , plugins_url( 'js/ui/jquery.timepicker.js' , __FILE__ ) );
		wp_enqueue_script( 'rns-canvasjs-script', plugins_url( 'js/canvasjs.min.js' , __FILE__ ) );
		wp_enqueue_script( 'wp-reporting-script-date-library' , plugins_url( 'js/date.js' , __FILE__ ) );
		
	}
	
	/* Function for dashboard for reports and stats */
	public static function dashboard() {
		
		// Getting all status for tickets.
		$statuses = wpas_get_post_status();
		
		$ticketCountReport = array();
		
		if ( ! empty( $statuses ) ) {
			
			foreach ( $statuses as $status => $label ) {
				
				//Gettig count for tickets as per status.
				$ticketCountReport[$label]['count']		= wpas_get_ticket_count_by_status( $status , array( 'post_status' => array( $status ) ) );
				$ticketCountReport[$label]['status']	= $status;
				$ticketCountReport[$label]['slug']		= $status;
				
			}
			
		}
		
		// Getting count for closed tickets.
		$closedTickets	=	wpas_get_ticket_count_by_status( '' , 'closed' );
		
		//@TODO: Why are these two scripts here instead of in the normal ENQUEUE script functions?
		wp_enqueue_script( 'wp-tooltip-script ' ,plugins_url( 'js/aria-tooltip.js' , __FILE__ ) );
		wp_enqueue_style( 'wp-tooltip-style ' ,plugins_url( 'css/aria-tooltip.css' , __FILE__ ) );
		
		//Including view for the dashboard page.
		include( 'includes/front-page.php' );
		
	}
	
	/** 
	* Function to be call when class's instance gets create to the first loading page for the Report tool 
	*/
	public static function basic_report() {
		
		Reports_And_Statistics::report_commons("basic");
		
	}

	/**
	* @TODO - need function header
	*/
	public static function reports_settings() {
		 			
		$submit 					= filter_input( INPUT_POST, 'submit', FILTER_SANITIZE_SPECIAL_CHARS );
		$wpas_custom_field 			= filter_input( INPUT_POST, 'wpas_custom_field_type', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	
		if( isset( $submit ) ){ // if submit button hit
			
			//if post custom field array is set 
			if( isset( $wpas_custom_field ) && !empty( $wpas_custom_field ) ){
		
				rns_add_or_update_custom_option_from_settings( 'wpas_reports_statistics_custom_fields' , $wpas_custom_field ) ; 
				
			}
		
			
		}
		
		$data = WPAS()->custom_fields->get_custom_fields();
		
		include ( 'includes/reports_settings.php' );
	}
	
	/**
	* @TODO - need function header
	*/		
	public static function reply_report() {
		
		Reports_And_Statistics::report_commons("reply");
		
	}
	
	/**
	* @TODO - need function header
	*/		
	public static function resolution_report() {
		
		Reports_And_Statistics::report_commons("resolution");
	
	}

	/**
	* @TODO - need function header
	*/		
	public static function delay_report() {
		
		Reports_And_Statistics::report_commons("delay");
	
	}

	/**
	* @TODO - need function header
	*/		
	public static function distribution_report() {
		
		Reports_And_Statistics::report_commons("distribution");
	
	}
	
	/**
	* @TODO - need function header
	*/		
	public static function trend_report() {
		include( 'includes/class.charts.php');
		Reports_And_Statistics::report_commons("trend");
	
	}
	
	/**
	* @TODO - need function header
	*/	
	public static function report_commons( $type ) {
		
		global $post;
		$wpdb = rns_get_wpdb();
		
		$filter_interval_val = filter_input( INPUT_GET, 'rns_filter_interval', FILTER_SANITIZE_SPECIAL_CHARS );
		rns_get_interval_value_by_report( $filter_interval_val );
		
		include( 'includes/class.chart.php');
		include( 'includes/chart/'.$type.'.php' );
		
		$agentsList		= rns_get_agents_list();

		// Getting list of Departments for Tickets
		$departments	= get_terms( array(
							   'taxonomy'   => 'department',
							   'hide_empty' => false
							)
						);
		// Getting list of Departments for Tickets
		
		//if product support is enabled
		if(wpas_get_option('support_products')){
			$products	= get_terms( array(
							   'taxonomy'   => 'product',
							   'hide_empty' => false
							)
						);
		}
						
		// Getting all status for tickets.
		$statuses		= wpas_get_post_status();
		
		$stat_custom_fields = explode ("," ,get_option('wpas_reports_statistics_custom_fields',true));
		
		//Get Ticket post type tags list.
		$tags			= get_terms( array( 'taxonomy' => 'ticket-tag' ) );
		
		//Adding view for the report page.
		include( 'includes/reports.php' );
		
		
		wp_enqueue_script( 'wp-reporting-script-common' , plugins_url( 'js/common.js' , __FILE__ ) );
		
	}
	
		/**
		 * Submenu filter function. 
		 * Sort and order submenu positions to match your custom order.
		 *@return  array
		 * 
		 */
		 
		/**
		* @TODO - why is there a header above with no function?
		*/	
		
}

