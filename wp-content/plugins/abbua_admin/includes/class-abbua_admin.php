<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://www.castorstudio.com
 * @since      1.0.0
 *
 * @package    Abbua_admin
 * @subpackage Abbua_admin/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Abbua_admin
 * @subpackage Abbua_admin/includes
 * @author     Castorstudio <support@castorstudio.com>
 */
class Abbua_admin {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Abbua_admin_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'abbua_admin';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Abbua_admin_Loader. Orchestrates the hooks of the plugin.
	 * - Abbua_admin_i18n. Defines internationalization functionality.
	 * - Abbua_admin_Admin. Defines all hooks for the admin area.
	 * - Abbua_admin_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-abbua_admin-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-abbua_admin-i18n.php';



		/**
		 * El archivo responsable de cargar el framework de Admin y los archivos externos necesarios
		 * para hacer funcionar el plugin.
		 * 
		 * Se agrega aquí para poder tener disponibles las funciones antes de llamar a las acciones
		 * del área de administración y del área pública
		 * 
		 * @date 22/6/2018
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/dependencies.php';



		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-abbua_admin-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-abbua_admin-public.php';

		$this->loader = new Abbua_admin_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Abbua_admin_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Abbua_admin_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Abbua_admin_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles', 999 ); // Priority '999' to load after all stylesheets
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Admin Framework Hooks
		$this->loader->add_action( 'cs_load_option_fields',$plugin_admin,'cs_abbua_load_themes');
		$this->loader->add_action( 'cs_validate_save', $plugin_admin, 'cs_abbua_save_plugin_settings' );
		
		// Menu Settings Admin Page
		$this->loader->add_action('cs_admin_menu', $plugin_admin, 'cs_register_admin_menu_settings');
		
		// Custom Order for Admin Menu
		if (cs_get_settings('sidebar_status')){
			$this->loader->add_action('admin_menu', $plugin_admin, 'cs_adminmenu_rearrange', 999); // Priority '999'
			$this->loader->add_filter('custom_menu_order', $plugin_admin, 'cs_admin_submenu_rearrange', 999); // Priority '999'
		}
		// Admin Init
		$this->loader->add_action('admin_init', $plugin_admin, 'cs_abbua_admin_init');
		$this->loader->add_action('admin_head', $plugin_admin, 'cs_abbua_admin_head');
		$this->loader->add_filter('admin_body_class', $plugin_admin, 'cs_abbua_admin_body_class');
		$this->loader->add_filter('update_right_now_text', $plugin_admin, 'cs_abbua_admin_dashboard_widget_right_now');
		
		// Admin Footer
		$this->loader->add_action('admin_print_footer_scripts', $plugin_admin, 'cs_abbua_admin_getset_settings'); // Function to transfer the admin settings to the js admin framework
		$this->loader->add_filter('admin_footer_text', $plugin_admin, 'cs_abbua_remove_footer_text', 999); // Priority '999'
		$this->loader->add_filter('update_footer', $plugin_admin, 'cs_abbua_remove_footer_version', 999); // Priority '999'

		// AJAX CALL: Menu Save & Menu Reset
		$this->loader->add_action('wp_ajax_abbua_menu_save', $plugin_admin, 'cs_abbua_menu_save_callback');
		$this->loader->add_action('wp_ajax_abbua_menu_reset', $plugin_admin, 'cs_abbua_menu_reset_callback');
		
		// AJAX CALL: Dynamic Themes Stylesheet
		$this->loader->add_action('wp_ajax_abbua_dynamic_themes',$plugin_admin,'cs_abbua_dynamic_themes_callback');
		
		// Login Page
		if (cs_get_settings('login_page_status')){
			$this->loader->add_action('login_enqueue_scripts', $plugin_admin, 'cs_abbua_enqueue_login_style', 999 );
			$this->loader->add_action('login_head', $plugin_admin, 'cs_abbua_login_head');
			$this->loader->add_filter('login_body_class', $plugin_admin, 'cs_abbua_login_class');
			$this->loader->add_filter('gettext', $plugin_admin, 'cs_abbua_login_label_change', 20, 3);
			$this->loader->add_filter('gettext_with_context', $plugin_admin, 'cs_abbua_login_label_change', 20, 3);
			$this->loader->add_filter('login_title', $plugin_admin, 'cs_abbua_login_title');
			$this->loader->add_filter('login_headerurl', $plugin_admin, 'cs_abbua_login_logo_url' );
			$this->loader->add_filter('login_headertitle', $plugin_admin, 'cs_abbua_login_logo_url_title' );
			$this->loader->add_filter('login_message', $plugin_admin, 'cs_abbua_login_message_override' );
			$this->loader->add_filter('login_messages', $plugin_admin, 'cs_abbua_login_messages_override');
			$this->loader->add_filter('login_errors', $plugin_admin, 'cs_abbua_login_errors_override');
		
			// AJAX CALL: Dynamic Themes Stylesheet
			$this->loader->add_action('wp_ajax_nopriv_abbua_dynamic_themes',$plugin_admin,'cs_abbua_dynamic_themes_callback');
		}

		// Filter for plugin action links		
		$this->loader->add_filter( 'plugin_action_links', $plugin_admin, 'cs_abbua_plugin_row_action_links', 10, 2 );
		
		// Filter for plugin meta links
		$this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'cs_abbua_plugin_row_meta_links' , 10, 2 );


		// Logout Redirect and URL Slug Changer
		if (cs_get_settings('login_security_custom_logout_redirect_status')){
			$this->loader->add_filter('logout_redirect', $plugin_admin, 'cs_abbua_logout_redirect', 999, 30);
		}
		if (cs_get_settings('login_security_custom_logout_url_status')){
			$this->loader->add_filter('logout_url', $plugin_admin, 'cs_abbua_logout_url', 10, 2 );
			$this->loader->add_action('wp_loaded', $plugin_admin, 'cs_abbua_logout_action' );
		}

		// Login Redirect and URL Slug Changer
		if (cs_get_settings('login_security_custom_login_redirect_status')){
			$this->loader->add_filter('login_redirect', $plugin_admin, 'cs_abbua_login_redirect', 999, 30);
		}
		if (cs_get_settings('login_security_custom_login_url_status')){
			$this->loader->add_filter('login_url', $plugin_admin, 'cs_abbua_login_url', 10, 3 );
			$this->loader->add_action('wp_loaded', $plugin_admin, 'cs_abbua_login_action' );
			$this->loader->add_action('plugins_loaded', $plugin_admin, 'cs_abbua_plugins_loaded', 1 );
			$this->loader->add_filter('wp_redirect', $plugin_admin, 'cs_abbua_wp_redirect' , 10, 2 );
			// $this->loader->add_action('setup_theme', $plugin_admin, 'setup_theme', 1 ); NO USADO
			
			$this->loader->add_filter('site_url', $plugin_admin, 'cs_abbua_site_url', 10, 4 );
			// $this->loader->add_filter('network_site_url', $plugin_admin, 'cs_abbua_network_site_url', 10, 3 );
			$this->loader->add_filter('site_option_welcome_email', $plugin_admin, 'cs_abbua_login_welcome_email' );
			remove_action('template_redirect', 'wp_redirect_admin_locations', 1000 );

			$this->loader->add_action('template_redirect', $plugin_admin, 'cs_abbua_hide_login_redirect_page_email_notif_woocommerce' );
		}
	}

	
	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Abbua_admin_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Abbua_admin_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
