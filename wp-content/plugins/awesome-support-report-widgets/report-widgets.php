<?php
/**
 * @package   Awesome Support Report Widgets
 * @author    nofearinc <mario@peshev.net>
 * @license   GPL-2.0+
 * @link      http://devrix.com
 * @copyright 2016 Awesome Support
 * 
 * @boilerplate-version   0.1.4
 *
 * Plugin Name:       Awesome Support: Report Widgets
 * Plugin URI:        http://getawesomesupport.com/addons/?utm_source=internal&utm_medium=plugin_meta&utm_campaign=Addons_ReportWidgets
 * Description:       Adds a set of report widgets to the WordPress Admin Dashboard
 * Version:           2.0.4
 * Author:            The Awesome Support Team
 * Author URI:        http://getawesomesupport.com/
 * Text Domain:       wpas-rw
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WPASRW_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'WPASRW_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WPASRW_ROOT', trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) );

/*----------------------------------------------------------------------------*
 * Instantiate the plugin
 *----------------------------------------------------------------------------*/

/**
 * Register the activation hook
 */
register_activation_hook( __FILE__, array( 'WPAS_Report_Widgets', 'activate' ) );

add_action( 'plugins_loaded', array( 'WPAS_Report_Widgets', 'get_instance' ) );
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
class WPAS_Report_Widgets {

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
	protected $item_id = 785323;

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
	protected $slug = 'awesome-support-report-widgets';

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
		define( 'AS_R_VERSION', '2.0.4' );
		define( 'AS_R_URL',     trailingslashit( plugin_dir_url( __FILE__ ) ) );
		define( 'AS_R_PATH',    trailingslashit( plugin_dir_path( __FILE__ ) ) );
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
			$this->add_error( sprintf( __( '%s requires Awesome Support to be active. Please activate the core plugin first.', 'wpas-rw' ), $plugin_name ) );
		}

		if ( ! $this->is_php_version_enough() ) {
			$this->add_error( sprintf( __( 'Unfortunately, %s can not run on PHP versions older than %s. Read more information about <a href="%s" target="_blank">how you can update</a>.', 'wpas-rw' ), $plugin_name, $this->php_version_required, esc_url( 'http://www.wpupdatephp.com/update/' ) ) );
		}

		if ( ! $this->is_version_compatible() ) {
			$this->add_error( sprintf( __( '%s requires Awesome Support version %s or greater. Please update the core plugin first.', 'wpas-rw' ), $plugin_name, $this->version_required ) );
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
		if( function_exists( 'wpas_register_addon' ) ){
			wpas_register_addon( $this->slug, array( $this, 'load' ) );			
		}

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

		if ( array_key_exists( $data, $plugin ) ) {
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
	public function deactivate() {
		if ( function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
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
		$license = '';
		if( function_exists( 'wpas_get_option' ) ){
			$license = wpas_get_option( "license_{$this->slug}", '' );			
		}

		/**
		 * Do not show the notice if the license key has already been entered.
		 */
		if ( ! empty( $license ) ) {
			return;
		}
		if( function_exists( 'wpas_get_settings_page_url' ) ){
			$link = wpas_get_settings_page_url( 'licenses' );
			WPAS()->admin_notices->add_notice( 'error', "lincense_{$this->slug}", sprintf( __( 'Please <a href="%s">fill-in your product license</a> now. If you don\'t, your copy of Awesome Support: Report Widgets <strong>will never be updated</strong>.', 'wpas-rw' ), $link ) );			
		}

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
		$license = '';
		if( function_exists( 'wpas_get_option' ) ){
			$license   = wpas_get_option( "license_{$this->slug}", '' );			
		}

		if( ! empty( $license ) ) {
			return $plugin_meta;
		}

		$license_page = add_query_arg( array( 'post_type' => 'ticket', 'page' => 'settings', 'tab' => 'licenses' ), admin_url( 'edit.php' ) );

		if ( plugin_basename( __FILE__ ) === $plugin_file ) {
			$plugin_meta[] = '<strong>' . sprintf( __( 'You must fill-in your product license in order to get future plugin updates. <a href="%s">Click here to do it</a>.', 'wpas-rw' ), $license_page ) . '</strong>';
		}
		
		return $plugin_meta;
	}

	/**
	 * Load the addon.
	 *
	 * Include all necessary files and instanciate the addon.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function load() {
		require_once( WPASRW_PATH . 'includes/widgets/open-closed-tickets-widget.php');
		require_once( WPASRW_PATH . 'includes/widgets/tickets-report-widget.php');
		require_once( WPASRW_PATH . 'includes/widgets/agent-tickets-report-widget.php');
		require_once( WPASRW_PATH . 'includes/widgets/product-tickets-report-widget.php');
		require_once( WPASRW_PATH . 'includes/widgets/priority-tickets-report-widget.php');
		require_once( WPASRW_PATH . 'includes/widgets/channel-tickets-report-widget.php');
		require_once( WPASRW_PATH . 'includes/widgets/department-tickets-report-widget.php');
		require_once( WPASRW_PATH . 'includes/widgets/custom-statuses-tickets-report-widget.php');
		require_once( WPASRW_PATH . 'includes/widgets/most-recent-tickets-widget.php');
		require_once( WPASRW_PATH . 'includes/functions/general.php');
		//require_once( WPASRW_PATH . 'includes/settings.php');
		require_once( WPASRW_PATH . 'includes/settings/settings-detail-reports.php');
		require_once( WPASRW_PATH . 'includes/settings/settings-summary-reports.php');
		require_once( WPASRW_PATH . 'includes/settings/settings-defaults.php');

		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
		add_action( 'pre_delete_term', array( $this, 'update_deleted_products_option' ), 1, 2 );
		add_action( 'delete_user', array( $this, 'update_deleted_agents_option' ), 1, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_wpas_report_widgets_scripts' ) );
	}

	public function add_wpas_report_widgets_scripts() {
		wp_enqueue_style( 'wpas-report-widgets-css', WPASRW_URL . 'includes/assets/css/styles.css' );
		wp_enqueue_style( 'wpas-font-awesome', WPASRW_URL . 'includes/assets/css/font-awesome.min.css' );
		wp_enqueue_script( 'wpas-canvasjs', WPASRW_URL . 'includes/assets/js/jquery.canvasjs.min.js', array('jquery'), false, true );
	}

	/**
	 * Get all tickets ids related to product
	 *
	 * @param $term_id -> the term/product id to fetch tickets for
	 *
	 * @since  0.1.0
	 * @return void
	 */
	private function get_product_tickets_ids( $term_id ) {
		$args = array(
			'tax_query' => array(
				array(	
					'taxonomy' => 'product',
					'field' => 'id',
					'terms' => $term_id,
				),
			),
			'fields' => 'ids',
		);
		return wpas_get_tickets( 'any', $args );
	}

	/**
	 * Update the option for deleted products
	 *
	 * @param $term
	 * @param $taxonomy
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function update_deleted_products_option( $term, $taxonomy ) {
		if( ! empty( $taxonomy ) && $taxonomy == 'product' && ! empty( $term ) ) {
			$deleted_product_option = get_option( '_deleted_products', array() );
			if( is_array( $deleted_product_option ) ) {
				$term = get_term( $term, $taxonomy );
				$tickets_ids = $this->get_product_tickets_ids( $term->term_id );
				$deleted_product_option[$term->term_id] = array(
					'name' => $term->name,
					'ticket_ids' => $tickets_ids,
				);
				wpas_update_option( '_deleted_products', $deleted_product_option, true );
			}
		}
	}

	/**
	 * Update the option for deleted agents
	 *
	 * @param $term
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function update_deleted_agents_option( $user_id ) {
		if( ! empty( $user_id ) && user_can( $user_id, 'edit_ticket' ) ){
			$deleted_agents = wpas_get_option( '_deleted_agents', array() );
			$userdata = get_userdata( $user_id );
			$deleted_agents[$user_id] = $userdata->user_nicename;
			wpas_update_option( '_deleted_agents', $deleted_agents, true );
		}		
	}
	

	public function add_dashboard_widgets() {
		new Open_Closed_Tickets_Widget();
		
		// Tickets Report Widget
		if ( true === boolval( wpas_get_option( 'asrw_open_tickets' ) ) ) {		
			new Tickets_Report_Widget( 'open', 'open_tickets_report', __( 'Open tickets', 'wpas-rw' ) );
		}
		
		if ( true === boolval( wpas_get_option( 'asrw_closed_tickets' ) ) ) {				
			new Tickets_Report_Widget( 'closed', 'closed_tickets_report', __( 'Closed tickets', 'wpas-rw' ) );	
		}
		
		// Agent Widgets
		if ( true === boolval( wpas_get_option( 'asrw_opened_tickets_by_agent' ) ) ) {
			new Agent_Tickets_Report_Widget( 'open', 'agent_open_tickets_report', __( 'Open tickets by agent', 'wpas-rw' ) );	
		}
		if ( true === boolval( wpas_get_option( 'asrw_closed_tickets_by_agent' ) ) ) {
			new Agent_Tickets_Report_Widget('closed', 'agent_closed_tickets_report', __( 'Closed tickets by agent', 'wpas-rw' ) );	
		}
		if ( true === boolval( wpas_get_option( 'asrw_open_tickets_by_agent_summary_chart' ) ) ) {
			new Agent_Tickets_Report_Widget( 'open', 'agent_open_tickets_chart_report', __( 'Open tickets by agent', 'wpas-rw' ) );	
		}		

		// Product Widgets 
		if ( true === boolval( wpas_get_option( 'asrw_opened_tickets_by_product' ) ) ) {
			new Product_Tickets_Report_Widget( 'open', 'product_open_tickets_report', __( 'Open tickets by product', 'wpas-rw' ) );
		}
		if ( true === boolval( wpas_get_option( 'asrw_closed_tickets_by_product' ) ) ) {
			new Product_Tickets_Report_Widget( 'closed', 'product_closed_tickets_report', __( 'Closed tickets by product', 'wpas-rw' ) );
		}
		if ( true === boolval( wpas_get_option( 'asrw_open_tickets_by_product_summary_chart' ) ) ) {
			new Product_Tickets_Report_Widget( 'open', 'product_open_tickets_chart_report', __( 'Open tickets by product', 'wpas-rw' ) );
		}

		// Custom Statuse Text and Chart Widgets 
		if ( true === boolval( wpas_get_option( 'asrw_open_tickets_by_status_summary' ) ) ) {
			new Custom_Statuses_Tickets_Report_Widget( 'open', 'custom_statuses_open_tickets_report', __( 'Open tickets by status', 'wpas-rw' ) );
		}
		if ( true === boolval( wpas_get_option( 'asrw_open_tickets_by_status_chart' ) ) ) {
			new Custom_Statuses_Tickets_Report_Widget( 'open', 'custom_statuses_open_tickets_chart_report', __( 'Open tickets by status', 'wpas-rw' ) );
		}


		// Most Recent Tickets Widget
		if ( true === boolval( wpas_get_option( 'asrw_most_recent_tickets' ) ) ) {
			new Most_Recent_Tickets_Widget( 'most_recent_tickets', __( 'Most recent tickets', 'wpas-rw' ) );
		}
		
		// Priority Reports
		if ( true === boolval( wpas_get_option( 'asrw_opened_tickets_by_priority' ) ) ) {
			new Priority_Tickets_Report_Widget( 'open', 'priority_open_tickets_report', __( 'Open tickets by priority', 'wpas-rw' ) );	
		}
		if ( true === boolval( wpas_get_option( 'asrw_closed_tickets_by_priority' ) ) ) {
			new Priority_Tickets_Report_Widget( 'closed', 'priority_closed_tickets_report', __( 'Closed tickets by priority', 'wpas-rw' ) );
		}
		
		if ( true === boolval( wpas_get_option( 'asrw_open_tickets_by_priority_summary_chart' ) ) ) {
			new Priority_Tickets_Report_Widget( 'open', 'priority_open_tickets_chart_report', __( 'Open tickets by priority', 'wpas-rw' ) );
		}
		
		// Channel Reports
		if ( true === boolval( wpas_get_option( 'asrw_opened_tickets_by_channel' ) ) ) {
			new Channel_Tickets_Report_Widget( 'open', 'channel_open_tickets_report', __( 'Open tickets by channel', 'wpas-rw' ) );	
		}		
		if ( true === boolval( wpas_get_option( 'asrw_closed_tickets_by_channel' ) ) ) {
			new Channel_Tickets_Report_Widget( 'closed', 'channel_closed_tickets_report', __( 'Closed tickets by channel', 'wpas-rw' ) );
		}
		if ( true === boolval( wpas_get_option( 'asrw_open_tickets_by_channel_summary_chart' ) ) ) {
			new Channel_Tickets_Report_Widget( 'open', 'channel_open_tickets_chart_report', __( 'Open tickets by channel', 'wpas-rw' ) );
		}
		
		// Department Reports
		if ( true === boolval( wpas_get_option( 'asrw_opened_tickets_by_department' ) ) ) {
			new Department_Tickets_Report_Widget( 'open', 'department_open_tickets_report', __( 'Open tickets by department', 'wpas-rw' ) );	
		}		
		if ( true === boolval( wpas_get_option( 'asrw_closed_tickets_by_department' ) ) ) {
			new Department_Tickets_Report_Widget( 'closed', 'department_closed_tickets_report', __( 'Closed tickets by department', 'wpas-rw' ) );
		}
		if ( true === boolval( wpas_get_option( 'asrw_open_tickets_by_dept_summary_chart' ) ) ) {
			new Department_Tickets_Report_Widget( 'open', 'department_open_tickets_chart_report', __( 'Open tickets by department', 'wpas-rw' ) );	
		}
	}
}
