<?php
/**
 * @package   Awesome Support Rules Engine
 * @author    DevriX for Awesome Support
 * @license   GPL-2.0+
 * @link      http://getawesomesupport.com/addons/
 * @copyright 2017 www.getawesomesupport.com
 *
 *
 * Plugin Name:       Awesome Support: Rules Engine
 * Plugin URI:        http://getawesomesupport.com/addons/
 * Description:       Use this extension to automate common ticket tasks and to integrate with zapier and other third party systems.
 * Version:           2.0.1
 * Author:            Awesome Support
 * Author URI:        http://devrix.com
 * Text Domain:       as-rules-engine
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
use AsRulesEngine\Rules_Engine;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Instantiate the plugin
 *----------------------------------------------------------------------------*/

/**
 * Register the activation hook
 */
register_activation_hook( __FILE__, array( 'AS_RULES_ENGINE', 'activate' ) );

add_action( 'plugins_loaded', array( 'AS_RULES_ENGINE', 'get_instance' ) );

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
class AS_RULES_ENGINE {

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
	protected $item_id = 909784;

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
	protected $version_required = '4.0.6';

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
	protected $slug = 'as-rules-engine';

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
	 * Holds an instance of the rules engine
	 */
	protected $rules_engine;
	
	/**
	 * Holds an instance of the cron process
	 */
	public $rules_cron;
	

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
		define( 'AS_RE_VERSION', '2.0.1' );
		define( 'AS_RE_URL',     trailingslashit( plugin_dir_url( __FILE__ ) ) );
		define( 'AS_RE_PATH',    trailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'AS_RE_DEFAULT_OPERATOR',  'default' );
		define( 'AS_RE_DEFAULT_REGEX',  'regex' );
		define( 'AS_RE_TRIGGER_META_PREFIX',  'trigger_' );
		define( 'AS_RE_CONDITIONS_META_PREFIX',  'condition_' );
		define( 'AS_RE_ACTION_META_PREFIX',  'action_' );
		define( 'AS_RE_RULESET_CPT', 'ruleset' );
		define( 'AS_RE_EMAIL_CPT', 'asre_email' );
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
				sprintf( __( 'You need Awesome Support to activate this addon. Please <a href="%s" target="_blank">install Awesome Support</a> before continuing.', 'as-rules-engine' ), esc_url( 'http://getawesomesupport.com/?utm_source=internal&utm_medium=addon_loader&utm_campaign=Addons' ) )
			);
		}
		flush_rewrite_rules();
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
			$this->add_error( sprintf( __( '%s requires Awesome Support to be active. Please activate the core plugin first.', 'as-rules-engine' ), $plugin_name ) );
		}

		if ( ! $this->is_php_version_enough() ) {
			$this->add_error( sprintf( __( 'Unfortunately, %1$s can not run on PHP versions older than %1$s. Read more information about <a href="%1$s" target="_blank">how you can update</a>.', 'as-rules-engine' ), $plugin_name, $this->php_version_required, esc_url( 'http://www.wpupdatephp.com/update/' ) ) );
		}

		if ( ! $this->is_version_compatible() ) {
			$this->add_error( sprintf( __( '%1$s requires Awesome Support version %1$s or greater. Please update the core plugin first.', 'as-rules-engine' ), $plugin_name, $this->version_required ) );
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
			add_action( 'pre_get_posts',        array( $this, 'show_ruleset_by_user' ) );

		}

		/**
		 * Add the Rules Access parameter
		 */

		if ( is_user_logged_in() ) {
			if( current_user_can( 'administer_awesome_support' ) ){
				add_action( 'admin_menu', array( $this, 'ruleset_settings_menu' ) );
			}

			/**
			* Replace Publish button with Save
			*/
			add_filter( 'gettext', array( $this, 'change_publish_button' ), 10, 2 );
		}

		/**
		 * Register the addon
		 */
		wpas_register_addon( $this->slug, array( $this, 'load' ) );
		add_action( 'plugins_loaded', array( $this, 'as_rules_load_textdomain' ), 21, 0 );

		return true;
	}

	/**
	 * register settings menu to display at bottom
	 */
	public function ruleset_settings_menu() {
		//create a submenu under Settings
		add_submenu_page(
			'edit.php?post_type=' . AS_RE_RULESET_CPT,
			__( 'AS Ruleset Settings','as-rules-engine' ),
			__( 'Settings','as-rules-engine' ),
			'manage_options',
			'as-ruleset-settings',
			array( $this, 'as_ruleset_settings' )
		);
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
	 * @since    1.0.0
	 * @return boolean True if the language file was loaded, false otherwise
	 */
	public function as_rules_load_textdomain() {
		$lang_dir       = trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) . 'languages/';
		$lang_path      = trailingslashit( plugin_dir_path( __FILE__ ) ) . 'languages/';
		$locale         = apply_filters( 'plugin_locale', get_locale(), 'as-rules-engine' );
		$mofile         = "as-rules-engine-$locale.mo";
		$glotpress_file = WP_LANG_DIR . '/plugins/rules-engine/' . $mofile;

		// Look for the GlotPress language pack first of all
		if ( file_exists( $glotpress_file ) ) {
			$language = load_textdomain( 'as-rules-engine', $glotpress_file );
		} elseif ( file_exists( $lang_path . $mofile ) ) {
			$language = load_textdomain( 'as-rules-engine', $lang_path . $mofile );
		} else {
			$language = load_plugin_textdomain( 'as-rules-engine', false, $lang_dir );
		}

		return $language;
	}


	/**
	 * Ceate settings page for rules
	 */
	public function as_ruleset_settings() {

		// check if user role can manage options
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		$wpas_options = maybe_unserialize( get_option( 'wpas_options', array() ) );

		$values = array(
			'' => __( 'Default', 'as-rules-engine' ),
		);

		$user_roles = get_editable_roles();
		if( !empty( $user_roles ) ){
			foreach ( $user_roles as $key => $value ) {
				if( isset( $value['name'] ) && !empty( $value['name'] )){
					$values[ $key ] = __( $value['name'], 'as-rules-engine' );
				}
			}
		}

		// Add filter to extend roles list in user settings.
		$values = apply_filters( 'extend_setting_userroles', $values );
		$headers = array( 'header_trigger','header_condition','header_action' );
		$fields = array(

			'header_trigger' => __( 'Triggers Rules', 'as-rules-engine' ),

			'trigger_new_ticket_role' => __( 'New ticket','as-rules-engine' ),
			'trigger_ticket_reply_received_role' => __( 'Client replied to ticket','as-rules-engine' ),
			'trigger_agent_replied_ticket_role' => __( 'Agent replied to ticket', 'as-rules-engine' ),
			'trigger_status_changed_role' => __( 'Status changed', 'as-rules-engine' ),
			'trigger_ticket_closed_role' => __( 'Ticket closed', 'as-rules-engine' ),
			'trigger_ticket_updated_role' => __( 'Ticket updated', 'as-rules-engine' ),
			'trigger_ticket_trashed_role' => __( 'Ticket trashed', 'as-rules-engine' ),
			'trigger_cron_role' => __( 'Cron', 'as-rules-engine' ),

			'header_condition' => __( 'Conditions Rules', 'as-rules-engine' ),

			'condition_tags_ticket_role' => __( 'Tags on ticket', 'as-rules-engine' ),
			'condition_age_ticket_role' => __( 'Age of ticket(In days)', 'as-rules-engine' ),
			'condition_age_last_customer_reply_role' => __( 'Age from last customer reply(In days)', 'as-rules-engine' ),
			'condition_age_last_agent_reply_role' => __( 'Age from last agent reply(In days)', 'as-rules-engine' ),
			'condition_status_role' => __( 'Status', 'as-rules-engine' ),
			'condition_state_role' => __( 'State', 'as-rules-engine' ),
			'condition_subject_role' => __( 'Subject', 'as-rules-engine' ),
			'condition_contents_role' => __( 'Ticket Contents', 'as-rules-engine' ),
			'condition_client_name_role' => __( 'Client Name', 'as-rules-engine' ),
			'condition_client_email_role' => __( 'Client email address', 'as-rules-engine' ),
			'condition_client_attrs_caps_role' => __( 'Client Attributes and Capabilities', 'as-rules-engine' ),
			'condition_agent_wp_role_role' => __( 'Agent WP Role', 'as-rules-engine' ),
			'condition_agent_name_role' => __( 'Agent Name', 'as-rules-engine' ),
			'condition_agent_attrs_caps_role' => __( 'Agent Attributes and Capabilities', 'as-rules-engine' ),
			'condition_custom_field_role' => __( 'Custom field and custom field contents', 'as-rules-engine' ),
			'condition_custom_field_value_role' => __( 'Custom field contents', 'as-rules-engine' ),
			'condition_reply_contents_role' => __( 'Reply contents', 'as-rules-engine' ),
			'condition_source_role' => __( 'Source', 'as-rules-engine' ),
			'condition_time_role' => __( 'Date', 'as-rules-engine' ),

			'header_action' => __( 'Action Rules', 'as-rules-engine' ),

			'action_change_status_role' => __( 'Change status', 'as-rules-engine' ),
			'action_change_priority_role' => __( 'Change priority', 'as-rules-engine' ),
			'action_change_dept_role' => __( 'Change department', 'as-rules-engine' ),
			'action_change_channel_role' => __( 'Change channel', 'as-rules-engine' ),
			'action_change_agent_role' => __( 'Change agent', 'as-rules-engine' ),
			'action_change_agent2_role' => __( 'Change secondary assignee', 'as-rules-engine' ),
			'action_change_agent3_role' => __( 'Change tertiary assignee', 'as-rules-engine' ),
			'action_change_first_interested_party_email_role' => __( 'Change first interested party email address', 'as-rules-engine' ),
			'action_change_second_interested_party_email_role' => __( 'Change second interested party email address', 'as-rules-engine' ),			
			
			'action_close_ticket_role' => __( 'Close ticket', 'as-rules-engine' ),
			'action_change_state_role' => __( 'State', 'as-rules-engine' ),
			'action_note_ticket_role' => __( 'Add note to ticket', 'as-rules-engine' ),
			'action_reply_ticket_role' => __( 'Add reply to ticket', 'as-rules-engine' ),
			'action_send_email_role' => __( 'Send email', 'as-rules-engine' ),
			'action_trash_ticket_role' => __( 'Move ticket to trash', 'as-rules-engine' ),
			'action_zapier_notification_role' => __( 'Zap URL', 'as-rules-engine' ),
			'action_call_webhook_role' => __( 'Call a webhook/endpoint', 'as-rules-engine' ),
			'action_execute_action_role' => __( 'Execute an HTTP post/get action', 'as-rules-engine' ),
		);

		// check if form is submitted.
		if ( isset( $_POST['submit'] ) ) {
			$wpas_options = $this->update_as_ruleset_settings( $_POST, $wpas_options, $fields, $values );
		}

		$args = array(
			'title' => __( 'AS Ruleset Settings','as-rules-engine' ),
			'options' => $wpas_options,
			'values' => $values,
			'fields' => $fields,
			'headers' => $headers,
		);

		extract( $args );
		require( AS_RE_PATH . 'includes/views/settings.php' );
	}

	/**
	 * Update rules engine setting.
	 * @param  array $post         post data array
	 * @param  array $wpas_options Wpas option
	 * @param  array $fields      Fields Array
	 * @param  array $values    values array
	 *
	 * @return  array $wpas_options Wpas option
	 */
	public function update_as_ruleset_settings( $post, $wpas_options, $fields, $values ) {

		$plugin_name = $this->plugin_data( 'Name' );

		// check wp_nonce_field for form validation.
		if ( ! isset( $post['update_ruleset_settings'] ) || ! wp_verify_nonce( $post['update_ruleset_settings'], 'update_ruleset_settings' ) ) {

			// Send error.
			$this->display_error( __( 'Error Submitting form, please try again.', 'as-rules-engine' ), $plugin_name );

		} else {

		   	// get value keys for options
		   	$values = array_keys( $values );
		   	$arkeys = array();
		   	// process data
		   	foreach ( $post as $key => $val ) {
				// check if post value exist in fields
		   		if ( array_key_exists( $key, $fields ) ) {
					// override new values for certain keys in options
		   			$wpas_options[ $key ] = $val;
		   			$arkeys[] = $val;
		   		}
		   	}

		   	// update options
		   	update_option( 'wpas_options', serialize( $wpas_options ) );
		}

		return $wpas_options;
	}

	/**
	 * Replace publish button text
	 * @param  string $translation translated string
	 * @param  string $text   Button text
	 *
	 * @return  string $text   Button text
	 */
	public function change_publish_button( $translation, $text ) {
		if ( AS_RE_RULESET_CPT == get_post_type() ) {
			if ( $text == 'Publish' ) {
				return 'Save';
			}
		}

		return $translation;
	}

	/**
	 * Preget query to  show rules set by user
	 * @param  object $wp_query post query object
	 *
	 * @return  object $wp_query post query object
	 */
	public function show_ruleset_by_user( $wp_query ) {
	 	global $current_user;
		if ( ! isset( $_GET['post_type'] ) && ! empty( $_GET['post_type'] ) ) {
			$post_type = sanitize_text_field( $_GET['post_type'] );
			if ( AS_RE_RULESET_CPT === $post_type ) {
				if ( 'administrator' !== $current_user->roles[0] && 'administer_awesome_support' !== $current_user->roles[0] && 'wpas_manager' !== $current_user->roles[0] ) {
				    $wp_query->set( 'author', $current_user->ID );
				}
			}
		}
	}

	/**
	 * Function to return trigger list
	 *
	 * @return array Rules engine trigger list
	 */
	protected function triggers_meta_list() {
		$prefix = AS_RE_TRIGGER_META_PREFIX;
		return array(
			$prefix . 'new_ticket' => __( 'New ticket', 'as-rules-engine' ),
			$prefix . 'ticket_reply_received' => __( 'Client replied to ticket', 'as-rules-engine' ),
			$prefix . 'agent_replied_ticket' => __( 'Agent replied to ticket', 'as-rules-engine' ),
			$prefix . 'status_changed' => __( 'Status changed', 'as-rules-engine' ),
			$prefix . 'ticket_closed' => __( 'Ticket closed', 'as-rules-engine' ),
			$prefix . 'ticket_updated' => __( 'Ticket updated', 'as-rules-engine' ),
			$prefix . 'ticket_trashed' => __( 'Ticket trashed', 'as-rules-engine' ),
			$prefix . 'cron' => __( 'Cron', 'as-rules-engine' ),
		);
	}

	/**
	 * Function to return Rules engine conditions list
	 *
	 * @return array Rules engine condition list
	 */
	protected function condition_meta_list() {
		$prefix = AS_RE_CONDITIONS_META_PREFIX;
		return array(
			$prefix . 'tags_ticket' => array(
				'name' => __( 'Tags on ticket', 'as-rules-engine' ),
			),
			$prefix . 'age_ticket' => array(
				'name' => __( 'Age of ticket(In days)', 'as-rules-engine' ),
			),
			$prefix . 'age_last_customer_reply' => array(
				'name' => __( 'Age from last customer reply(In days)', 'as-rules-engine' ),
			),
			$prefix . 'age_last_agent_reply' => array(
				'name' => __( 'Age from last agent reply(In days)', 'as-rules-engine' ),
			),
			$prefix . 'status' => array(
				'name' => __( 'Status', 'as-rules-engine' ),
			),
			$prefix . 'state' => array(
				'name' => __( 'State', 'as-rules-engine' ),
			),
			$prefix . 'subject' => array(
				'name' => __( 'Subject', 'as-rules-engine' ),
			),
			$prefix . 'contents' => array(
				'name' => __( 'Ticket Contents', 'as-rules-engine' ),
			),			
			$prefix . 'client_name' => array(
				'name' => __( 'Client Name', 'as-rules-engine' ),
			),
			$prefix . 'client_email' => array(
				'name' => __( 'Client email address', 'as-rules-engine' ),
			),
			$prefix . 'client_attrs_caps' => array(
				'name' => __( 'Client Attributes and Capabilities', 'as-rules-engine' ),
			),
			$prefix . 'agent_wp_role' => array(
				'name' => __( 'Agent WP Role', 'as-rules-engine' ),
			),
			$prefix . 'agent_name' => array(
				'name' => __( 'Agent Name', 'as-rules-engine' ),
			),
			$prefix . 'agent_attrs_caps' => array(
				'name' => __( 'Agent Attributes and Capabilities', 'as-rules-engine' ),
			),
			$prefix . 'custom_field' => array(
				'name' => __( 'Custom field and custom field contents', 'as-rules-engine' ),
			),
			$prefix . 'custom_field_value' => array(
				'name' => __( 'Custom field contents', 'as-rules-engine' ),
			),
			$prefix . 'reply_contents' => array(
				'name' => __( 'Reply contents', 'as-rules-engine' ),
			),
			$prefix . 'source' => array(
				'name' => __( 'Source', 'as-rules-engine' ),
			),
			$prefix . 'time' => array(
				'name' => __( 'Date', 'as-rules-engine' ),
			),
		);
	}

	/**
	 * Function to return Rules engine actions lists
	 *
	 * @return array Action array
	 */
	protected function action_meta_list() {
		$prefix = AS_RE_ACTION_META_PREFIX;
		return array(
			$prefix . 'change_status' => __( 'Change status', 'as-rules-engine' ),
			$prefix . 'change_agent' => __( 'Change agent', 'as-rules-engine' ),
			$prefix . 'change_agent2' => __( 'Change secondary agent', 'as-rules-engine' ),
			$prefix . 'change_agent3' => __( 'Change tertiary agent', 'as-rules-engine' ),			
			$prefix . 'change_priority' => __( 'Change priority', 'as-rules-engine' ),
			$prefix . 'change_channel' => __( 'Change channel', 'as-rules-engine' ),
			$prefix . 'change_dept' => __( 'Change department', 'as-rules-engine' ),
			$prefix . 'close_ticket' => __( 'Close ticket', 'as-rules-engine' ),
			$prefix . 'note_ticket' => __( 'Add note to ticket', 'as-rules-engine' ),
			$prefix . 'reply_ticket' => __( 'Add reply to ticket', 'as-rules-engine' ),
			$prefix . 'send_email' => __( 'Send email', 'as-rules-engine' ),
			$prefix . 'trash_ticket' => __( 'Move ticket to trash', 'as-rules-engine' ),
			$prefix . 'zapier_notification' => __( 'Zap URL', 'as-rules-engine' ),
			$prefix . 'call_webhook' => __( 'Call a webhook/endpoint', 'as-rules-engine' ),
			$prefix . 'execute_action' => __( 'Execute an HTTP post/get action', 'as-rules-engine' ),
		);
	}

	/**
	 * Rules setting access builder. 
	 */
	public function wpas_rules_access_settings( $def ) {

		global $wp_roles;

		$editable_roles = $wp_roles->roles;
		$roles_options = array();

		foreach ( $editable_roles as $key => $roles ) {
			if ( 'administrator' === $key || 'wpas_user' === $key || 'administer_awesome_support' === $key ) {
				continue;
			}
			$roles_options[ $key ] = $roles['name'];
		}

		$triggers_meta_list = $this->triggers_meta_list();

		$condition_meta_list = $this->condition_meta_list();

		$action_meta_list = $this->action_meta_list();

		$options_rules = array();

		$options_rules[] = array(
			'name'    => __( 'Triggers Rules' , 'as-rap' ),
			'type'    => 'heading',
		);

		foreach ( $triggers_meta_list as $key => $rule_name ) {
			$options_rules[] = array(
				'name'    => __( $rule_name , 'as-rap' ),
				'id'      => $key . '_role',
				'type'    => 'select',
				'desc'    => __( '', 'as-rap' ),
				'default' => 'wpas_manager',
				'multiple' => true,
				'options' => $roles_options,
			);
		}

		$options_rules[] = array(
			'name'    => __( 'Conditions Rules' , 'as-rap' ),
			'type'    => 'heading',
		);

		foreach ( $condition_meta_list as $key => $rule_name ) {
			$options_rules[] = array(
				'name'    => __( $rule_name['name'] , 'as-rap' ),
				'id'      => $key . '_role',
				'type'    => 'select',
				'desc'    => __( '', 'as-rap' ),
				'default' => 'wpas_manager',
				'multiple' => true,
				'options' => $roles_options,
			);
		}

		$options_rules[] = array(
			'name'    => __( 'Action Rules' , 'as-rap' ),
			'type'    => 'heading',
		);

		foreach ( $action_meta_list as $key => $rule_name ) {
			$options_rules[] = array(
				'name'    => __( $rule_name , 'as-rap' ),
				'id'      => $key . '_role',
				'type'    => 'select',
				'desc'    => __( '', 'as-rap' ),
				'default' => 'wpas_manager',
				'multiple' => true,
				'options' => $roles_options,
			);
		}

		$settings = array(
			'access' => array(
				'name'    => __( 'Rules Access Parameter', 'as-rap' ),
				'options' => $options_rules,

			),
		);

		return array_merge( $def, $settings );

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
			return $plugin[ $data ];
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
		if ( class_exists( 'Awesome_Support' ) ) {
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
			deactivate_plugins( basename( __FILE__ ) );
		}
		
		// Remove cron scheduled items
		wp_clear_scheduled_hook( 'awesome_support_rules_engine_cron_action_hook' );
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
			'file'      => __FILE__,
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
		WPAS()->admin_notices->add_notice( 'error', "lincense_{$this->slug}", sprintf( __( 'Please <a href="%s">fill-in your product license</a> now. If you don\'t, your copy of the Awesome Support: Rules Engine <strong>will never be updated</strong>.', 'as-rules-engine' ), $link ) );

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

		$license = wpas_get_option( "license_{$this->slug}", '' );

		if ( ! empty( $license ) ) {
			return $plugin_meta;
		}

		$license_page = add_query_arg( array( 'post_type' => 'ticket', 'page' => 'settings', 'tab' => 'licenses' ), admin_url( 'edit.php' ) );

		if ( plugin_basename( __FILE__ ) === $plugin_file ) {
			$plugin_meta[] = '<strong>' . sprintf( __( 'You must fill-in your product license in order to get future plugin updates. <a href="%s">Click here to do it</a>.', 'as-rules-engine' ), $license_page ) . '</strong>';
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
		require_once( AS_RE_PATH . 'includes/class.rulesengine.php' );
		require_once( AS_RE_PATH . 'includes/class.cron.php' );
		$this->rules_engine = new Rules_Engine();
		
		//initiate the cron process here
		$this->rules_cron = new AsRulesEngine\RE_cron() ;
	}

}
