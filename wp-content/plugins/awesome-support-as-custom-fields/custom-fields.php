<?php
/**
 * @package   Awesome Support Custom Fields
 * @author    The Awesome Support Team
 * @license   GPL-2.0+
 * @link      http://awesomesupport.com
 * @copyright 2016 Awesome_Support
 * 
 * @boilerplate-version   0.1.4
 *
 * Plugin Name:       Awesome Support: Custom Fields
 * Plugin URI:        http://getawesomesupport.com/addons/?utm_source=internal&utm_medium=plugin_meta&utm_campaign=awesome_support_custom_fields
 * Description:       A Custom Fields settings page introducing custom fields support for tickets through UI.
 * Version:           1.0.9
 * Author:            The Awesome Support Team
 * Author URI:        http://getawesomesupport.com
 * Text Domain:       ascf
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WPASCF_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'WPASCF_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WPASCF_ROOT', trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) );

/*----------------------------------------------------------------------------*
 * Instantiate the plugin
 *----------------------------------------------------------------------------*/

/**
 * Register the activation hook
 */
register_activation_hook( __FILE__, array( 'WPASCF_Custom_Fields', 'activate' ) );

add_action( 'plugins_loaded', array( 'WPASCF_Custom_Fields', 'get_instance' ) );
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
class WPASCF_Custom_Fields {

	/**
	 * ID of the item.
	 *
	 * The item ID must match the post ID on the e-commerce site.
	 * Using the item ID instead of its name has the huge advantage of
	 * allowing changes in the item name.
	 *
	 * If the ID is not set the class will fall back on the plugin name instead.
	 *
	 * @since 0.1.3
	 * @var int
	 */
	protected $item_id = 767892;

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
	protected $version_required = '4.1.0';

	/**
	 * Required version of PHP.
	 *
	 * Require PHP version 5.6 at least.
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
	protected $slug = 'custom-fields';

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
	 * Possible error message.
	 * 
	 * @var null|WP_Error
	 */
	protected $defaults = array();

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
		define( 'AS_CF_VERSION', '1.0.9' );
		define( 'AS_CF_URL',     trailingslashit( plugin_dir_url( __FILE__ ) ) );
		define( 'AS_CF_PATH',    trailingslashit( plugin_dir_path( __FILE__ ) ) );
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
		
		/**
		 * Set this specific defautls fields
		 */
		$this->defaults = (array)apply_filters( 'as-custom-fields-defaults',  array(
			'order'					=> 0,					  // Field order
			'hide'					=> 'no',				  // Hide field
		) );

		$plugin_name = $this->plugin_data( 'Name' );

		if ( ! $this->is_core_active() ) {
			$this->add_error( sprintf( __( '%s requires Awesome Support to be active. Please activate the core plugin first.', 'wpas-cf' ), $plugin_name ) );
		}

		if ( ! $this->is_php_version_enough() ) {
			$this->add_error( sprintf( __( 'Unfortunately, %s can not run on PHP versions older than %s. Read more information about <a href="%s" target="_blank">how you can update</a>.', 'wpas-cf' ), $plugin_name, $this->php_version_required, esc_url( 'http://www.wpupdatephp.com/update/' ) ) );
		}

		if ( ! $this->is_version_compatible() ) {
			$this->add_error( sprintf( __( '%s requires Awesome Support version %s or greater. Please update the core plugin first.', 'wpas-cf' ), $plugin_name, $this->version_required ) );
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
		
		add_action( 'admin_menu',           array( $this, 'register_submenu_items' ),  9, 0 );
		add_filter( 'wpas_plugin_admin_pages', array( $this, 'register_admin_page_with_core' ) );


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
	
	public function register_admin_page_with_core( $actions ) {
		$actions[] = 'wpas-custom-fields';
		return $actions;
	}
	
	/**
	 * Add tickets submenu items.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function register_submenu_items() {
		add_submenu_page( 'edit.php?post_type=ticket', __( 'Custom Fields', 'wpas-cf' ), __( 'Custom Fields', 'wpas-cf' ), 'administrator', 'custom-fields', array( $this, 'custom_fields_settings_page' ) );
	}
	

	/**
	 * Callback for the settings page managing custom fields.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function custom_fields_settings_page() {
		include_once( WPASCF_PATH . 'includes/admin/views/custom-fields-settings.php' );
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
 			WPAS()->admin_notices->add_notice( 'error', "lincense_{$this->slug}", sprintf( __( 'Please <a href="%s">fill-in your product license for Awesome Support: Custom Fields </a> now. If you don\'t, your copy of Awesome Support: Custom Fields <strong>will never be updated</strong>.', 'ascf' ), $link ) );			
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
			$plugin_meta[] = '<strong>' . sprintf( __( 'You must fill-in your product license in order to get future plugin updates. <a href="%s">Click here to do it</a>.', 'wpas-cf' ), $license_page ) . '</strong>';
		}
		
		return $plugin_meta;
	}
	
	/**
	 * Filter on the wrap , to add title in the checkbox wrapper
	 * 
	 * @param string $default
	 * @param array $field
	 * @return string
	 */
	public function add_checkbox_title( $default, $field ) {
		if( ! empty( $field['args'] ) && ! empty( $field['args']['field_type'] ) && 'checkbox' == $field['args']['field_type']){
			$title = '{{field}}';
			if( !empty( $field['args']['title'] ) ){
				$title = '<label>' . $field['args']['title'] . '</label>' . $title;
			}
			$default = str_replace( '{{field}}', $title, $default );
		}
		return $default;
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
		$this->wpas_custom_fields();
		
		$this->ascf_attachment();
		
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		add_filter( 'ascf_field_markup', array( $this, 'get_field_markup' ), 10, 1 );
		add_filter( 'ascf_all_fields_markup', array( $this, 'get_all_fields_markup' ), 10, 0 );

		add_action( 'wp_ajax_ascf_save_fields', array( $this, 'ascf_save_fields' ) );
		add_action( 'wp_ajax_ascf_add_field', array( $this, 'ascf_add_field' ) );
		add_action( 'wp_ajax_ascf_all_fields', array( $this, 'ascf_get_all_fields_markup' ) );

		// Add title of checkbox 
		add_filter( 'wpas_cf_wrapper_markup', array( $this, 'add_checkbox_title' ), 10, 2 );
		
		// Remove upload custom field from admin metabox
		// add_action( 'wpas_cf_wrapper_markup', array( $this, 'remove_cf_upload_from_metabox' ), 10, 2 );
		add_filter( 'wpas_get_custom_fields', array( $this, 'filter_custom_fields' ) );
	}	
	
	/**
	* Remove the Upload button from custom field
	* on the ticket edit page. The uploads has been saved
	* as post attachment
	* 
	* @return void
	*/
	public function remove_cf_upload_from_metabox( $default, $field ) {
		if( 
			! empty( $field['args'] ) 
			&& ! empty( $field['args']['field_type'] ) 
			&& 'upload' == $field['args']['field_type']
			&& is_admin()
		){
			$default = '';// A hack, as wrapper have to have the {{field}} string, that replace with field markup
		}
		return $default;
	}
	
	/**
	 * Filter out before displaying the custom field
	 * 
	 * @param array $fields
	 * @return array
	 */
	public function filter_custom_fields( $fields ) {
		if ( ! empty( $fields ) ) {
			/**
			 * Initial array variable for values
			 * that have either order element is not set or empty
			*/
			$blank_sort = array();
			$positive_sort = array();
			$core_sort = array();
			foreach ( $fields as $name => $field ) {				
				/**
				 * Remove element if set to hide.
				 * We don't need to display it on front-end
				*/
				if( isset( $field['args']['hide'] ) && "yes" === $field['args']['hide'] ){
					unset( $fields[ $name ] );
				}
				
				/**
				 * If order argument is not set( could be core fields )
				 * Remove them and add to core_sort variable
				*/
				if( ! isset( $field['args']['order'] ) ){
					unset( $fields[ $name ] );
					$core_sort[$name] = $field;
				}
				
				/**
				 * If order argument is set but 0
				 * Remove them and add to blank_sort variable
				 * We need to move them out to avoid being sorted
				*/
				if( isset( $field['args']['order'] ) && "0" === $field['args']['order'] ){
					unset( $fields[ $name ] );
					$blank_sort[$name] = $field;
				}
				
				/**
				 * If order argument is set but > 0
				 * Remove them and add to positive_sort variable
				 * We need to move them out, sort, then merge at the bottom
				*/
				if( isset( $field['args']['order'] ) && $field['args']['order'] > 0 ){
					unset( $fields[ $name ] );
					$positive_sort[$name] = $field;
				}
				
				/**
				 * If core argument is set & true
				 * Remove them and add to core_sort variable
				*/
				if( isset( $field['args']['core'] ) && $field['args']['core'] === true ){
					unset( $fields[ $name ] );
					$core_sort[$name] = $field;
				}
				
			}		
		}

		/**
		 * Sort a custom fields array only
		 * if they are not empty.
		*/
		if( ! empty ( $fields ) ){
			uasort( $fields, array( $this, 'sorting' ) );
		}
		if( ! empty ( $positive_sort ) ){
			uasort( $positive_sort, array( $this, 'sorting' ) );
		}
		
		/**
		 * Merge the blank sort before returning values
		 * This will solve the issue if all custom fields 
		 * sort order = 0 being sorted
		*/
		if( ! empty ( $blank_sort ) ){
			$fields = array_merge( $fields, $blank_sort );
		}
		if( ! empty ( $positive_sort ) ){
			$fields = array_merge( $fields, $positive_sort );
		}
		if( ! empty ( $core_sort ) ){
			$fields = array_merge( $fields, $core_sort );
		}
		return $fields;
	}
	
	/**
	 * Sorting helper function
	*/
	public function sorting( $a, $b ){
		if( !isset( $a['args']['order'] ) && !isset( $b['args']['order'] ) ){
			return 0;
		}

		$a_order = isset( $a['args']['order'] )? $a['args']['order']: false;
		$b_order = isset( $b['args']['order'] )? $b['args']['order']: false;
		
		if( $a_order == 0 ) return 1;			
		if( $b_order == 0 ) return -1; 			
		if ( $a_order === $b_order ){
			return 0;				
		}
		return ( $a_order < $b_order ) ? -1 : 1;
	}
	
	/**
	* Include the extended file uploader class
	* 
	* @return void
	*/
	protected function ascf_file_uploader(){
		$file_uploader = plugin_dir_path(__FILE__ ) . "includes/class-ascf-file-uploader.php";		
		if( file_exists( $file_uploader ) ) {			
			include_once( $file_uploader );
		}		
	}
	
	/**
	* If has attachment, do the upload process
	* 
	* @return void
	*/
	public function ascf_attachment() {
		$fields = get_option( 'wpas_custom_fields' );
		if( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				$field_type = $field['field_type'];
				if( $field_type == 'upload' ) {
					$this->do_the_file_upload( $field );
				}
			}	
		}
	}
	
	/**
	* Process the actual file uploading using
	* ASCF_File_Upload class
	* @return void
	*/
	private function do_the_file_upload( $field ) {
		$this->ascf_file_uploader();
		$field_name = isset( $field['name'] )?  $field['name']: '' ;
		if( !empty( $field_name ) && class_exists( 'ASCF_File_Upload' ) ){
			new ASCF_File_Upload( $field_name );
		}
	}
	
	/**
	*	
	* Get all valid field types
	* @return void
	*/
	private function get_field_types() {
		return array(
			'text',
			'url',
			'email',
			'password',
			'upload',
			'number',
			'select',
			'checkbox',
			'radio',
			'textarea',
			'wysiwyg',
			'taxonomy',
			'date-field'
		);
	}

	/**
	*	
	* Check if the required field value passed through POST is 1
	* @param $field - field to be checked
	* @since  1.0.0
 	* @return void
	*/
	private function sanitize_bool_field( &$field ) {
		$field = ! empty( $field ) && $field == 1;
	}

	/**
	*	
	* get field with all attributes title, field_type, placeholder, etc. and check if the title is valid
	* @param $field - field to be checked
	* @since  1.0.0
 	* @return bool
	*/
	private function is_valid_title( $field = array() ) {
		return is_array( $field ) && array_key_exists( 'title', $field ) && ! empty( $field['title'] );
	}

	/**
	*	
	* check if field capability is set or use create_ticket
	* @param $field - field to be checked
	* @since  1.0.0
 	* @return bool
	*/
	private function sanitize_capability( &$field = array() ) {
		is_array( $field ) && array_key_exists( 'capability', $field ) && ! empty( $field['capability'] ) ? $field['capability'] : $field['capability'] = 'create_ticket';

	}

	/**
	*	
	* get field with all attributes title, placeholder, etc. and check if the field_type is valid
	* @param $field - field to be checked
	* @since  1.0.0
 	* @return bool
	*/
	private function is_valid_field_type( $field = array() ) {
		$response = false;
		$field_types = $this->get_field_types();

		if( is_array( $field ) && array_key_exists('field_type', $field) ){
			$field_type = $field['field_type'];
			$response = ! empty( $field_type ) && in_array( $field_type, $field_types ); 
		}
		return $response;
	}

	/**
	*	
	* get field with all attributes title, field_type, placeholder, etc. and check if the title is valid
	* @param $field - field to be checked
	* @since  1.0.0
 	* @return bool
	*/
	private function is_valid_name( $field = array() ) {
		return is_array( $field ) && array_key_exists( 'name', $field ) && ! empty( $field['name'] ) && preg_match("/^[_a-z0-9]*$/", $field['name'] );
	}

	/**
	*	
	* Check if checkbox or radio or select field
	* @param $field - field to be checked
	* @since  1.0.0
 	* @return bool
	*/
	private function is_options_field( $field ){
		$field_type = $field['field_type'];
		return $field_type == 'checkbox' || $field_type == 'radio' || $field_type == 'select';
	}
	
	/**
	*	
	* Remove the empty options
	* @param $field - field to be checked
	* @since  1.0.0
 	* @return bool
	*/
	private function sanitize_options_field( &$field = array() ) {
		if( $this->is_options_field( $field ) && array_key_exists( 'options', $field ) && is_array( $field['options']) ) {
			foreach( $field['options'] as $option_id => $option_value ) {
				if( empty( $option_value ) ) {
					unset( $field['options'][$option_id] );
				}
			}
		}	
	}

	/**
	*
	* Sanitize field 
	* @since  1.0.0
 	* @return array
	*/
	private function sanitize_field( &$field ){
		$field_type =  $field['field_type'];
		$this->sanitize_options_field( $field );
		$this->sanitize_capability( $field );				
		$this->sanitize_bool_field( $field['required'] );
		$this->sanitize_bool_field( $field['log'] );
		$this->sanitize_bool_field( $field['show_column'] );
		$this->sanitize_bool_field( $field['sortable_column'] );
		$this->sanitize_bool_field( $field['filterable'] );
		$this->sanitize_bool_field( $field['select2'] );
		$this->sanitize_bool_field( $field['taxo_std'] );
		$this->sanitize_bool_field( $field['taxo_hierarchical'] );
		$this->sanitize_bool_field( $field['backend_only'] );
		$this->sanitize_bool_field( $field['show_frontend_list'] );
		$this->sanitize_bool_field( $field['show_frontend_detail'] );
		$this->sanitize_bool_field( $field['readonly'] );
	}

	/**
	*
	* Check if field is valid 
	* Return true or false if the field is valid 
	* @since  1.0.0
 	* @return bool
	*/
	private function is_valid_field( $field ) {
		return is_array( $field ) 
				&& $this->is_valid_title( $field ) 
				&& $this->is_valid_field_type( $field ) 
				&& $this->is_valid_name( $field );
	}

	/**
	*
	* Validate the fields 
	* Return empty array if the fields are not valid
	* @since  1.0.0
 	* @return array
	*/
	private function validate_fields( $fields = array() ) {
		if( is_array( $fields ) && ! empty( $fields ) ) {
			$defaults = WPAS_Custom_Field::get_field_defaults();
			$defaults = array_merge( $this->defaults, $defaults );
			foreach ( $fields as &$field ) {
				if( $this->is_valid_field( $field ) ) {
					
					$field = array_merge( $defaults, $field );
					$this->sanitize_field( $field );	
					
				} else {	
					$field = array();
				}
			}

		} else {
			$fields = array();
		}

		return array_filter( $fields );
	}

	/**
	*
	* Return response for successfully saved fields
	* @param $fields - array of fields to be saved in the wpas_custom_fields option
	* @since  1.0.0
 	* @return void
	*/
	private function save_fields_successful_response( $fields = array() ) {
		update_option( 'wpas_custom_fields', $fields );
		$data['message'] = __( 'Fields were saved successfully', 'wpas-cf' );
		wp_send_json_success( $data );	
	}

	/**
	*
	* Return response for unsuccessfully saved fields
	* @since  1.0.0
 	* @return void
	*/
	private function save_fields_unsuccessful_response() {
		$data['message'] = __( 'Fields were not saved successfully', 'wpas-cf' );
		wp_send_json_error( $data );
	}

	/**
	*
	* Saved the fields created by the user
	* If no fields, empty the option value for custom fields
	* @since  1.0.0
 	* @return void
	*/
	public function ascf_save_fields() {
		if( ! empty( $_POST ) && array_key_exists( 'fields', $_POST ) && is_array( $_POST['fields'] ) ) {
		
			$fields = $this->validate_fields( $_POST['fields'] );
			! empty ( $fields ) ? $this->save_fields_successful_response( $fields ) : $this->save_fields_unsuccessful_response();
		
		} else {
			$this->save_fields_successful_response();
		}
	}

 	/**
	* Enqueue Admin resources
	* @since 1.0.0
	* @return void
	*/
    public function admin_enqueue_scripts() {
        if( is_admin() ) {
        	wp_enqueue_style( 'custom-fields-styles', plugins_url( 'assets/css/styles.css', __FILE__ ) );     
            wp_enqueue_script( 'custom-fields-script', plugins_url( 'assets/js/functions.js', __FILE__ ), array('jquery') );
            $option = array(
            	'option_id' => __( 'Option ID', 'wpas-cf' ),
            	'option_label' => __( 'Option Label', 'wpas-cf' ),
            	'remove_button' => __( 'Remove', 'wpas-cf' ),

        	);
            wp_localize_script( 'custom-fields-script', 'option', $option );
        }
    }

    /**
	* Add textarea custom field
	* @since 1.0.0
	* @return void
	*/
    private function add_textarea_custom_field( $slug, $field ) {
    	if( array_key_exists( 'textarea_options', $field ) && ! empty( $field['textarea_options'] ) ) {
    		$field['textarea_options']['rows'] = (int)$field['textarea_options']['rows'];
    		$field['textarea_options']['cols'] = (int)$field['textarea_options']['cols'];
    	}
		wpas_add_custom_field( $slug, $field );
    }
	
	/**
 	* Add wysiwyg custom field
    * @since 1.0.0
 	* @return void
 	*/
    private function add_wysiwyg_custom_field( $slug, $field ) {
     	if( array_key_exists( 'sanitize', $field ) ) {
     		$field['sanitize'] = 'wp_filter_kses';
     	}
     	wpas_add_custom_field( $slug, $field );
    }

    /**
	* Add upload custom field
	* @since 1.0.0
	* @return void
	*/
    private function add_upload_custom_field( $slug, $field ) {
    	if( array_key_exists( 'multiple', $field ) && $field['multiple'] == "true" ) {
    		$field['multiple'] = true;
    	}
    	wpas_add_custom_field( $slug, $field );
    }

    /**
    * Register all custom fields from wpas_custom_fields option
    * @since 1.0.0
    * @return void
    */
    public function wpas_custom_fields() {

    	if ( function_exists( 'wpas_add_custom_field' ) ) {
    		$fields = get_option( 'wpas_custom_fields' );
    		if( ! empty( $fields ) ) {
	    		foreach ( $fields as $field ) {
	    			$field_type = $field['field_type'];
	    			$slug = array_key_exists( 'name', $field ) ? $field['name'] : '';
	    			if( $field_type == 'textarea'){
	    				$this->add_textarea_custom_field( $slug, $field );
	    			} elseif( $field_type == 'upload' ) {
	    				$this->add_upload_custom_field( $slug, $field );
	    			} elseif( $field_type == 'taxonomy' ) {
	    				wpas_add_custom_taxonomy( $slug, $field );
					} elseif( $field_type == 'wysiwyg' ) {
 	    				$this->add_wysiwyg_custom_field( $slug, $field );
	    			} else {
	    				wpas_add_custom_field( $slug, $field );
	    			}
	    		}	
    		}
    	}
    }

    /**
    * Get view
    * @since 1.0.0
     * @return void
	*/
	private function ascf_get_view( $view_name = '', $field = '' ) {

		$path = plugin_dir_path(__FILE__ ) . "includes/admin/views/{$view_name}.php";
		
		if( file_exists( $path ) ) {
			
			include( $path );
			
		}
		
	}

	/**
    * Get the markup for adding new field
    * @since 1.0.0
    * @return void
 	*/
    public function get_new_field_markup() {
    	ob_start();
    	$this->ascf_get_view( 'new-field' );
    	return ob_get_clean();
    }

    /**
    * Get the markup for adding new field
    * @since 1.0.0
    * @return void
    */
    public function get_field_markup( $field ) {
    	ob_start();
		$this->defaults;
    	$this->ascf_get_view( 'field', $field );
    	return ob_get_clean();
    }

    /**
    * Get the markup for all fields
    * @since 1.0.0
    * @return void
    */
    public function get_all_fields_markup() {
		$fields = get_option( 'wpas_custom_fields' );
		$response = '';

    	if( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
			 	$response .= $this->get_field_markup( $field );
			} 
		}

		return $response;
    }

    /**
    * Send the markup for all fields in json format
    * @since 1.0.0
    * @return void
    */
    public function ascf_get_all_fields_markup() {
    	$fields = $this->get_all_fields_markup();
    	wp_send_json( $fields );
    }

     /**
    * Send the markup for adding field in json format
    * @since 1.0.0
    * @return void
    */
    public function ascf_add_field() {
    	$field = $this->get_new_field_markup();
    	wp_send_json( $field );
    }
}
