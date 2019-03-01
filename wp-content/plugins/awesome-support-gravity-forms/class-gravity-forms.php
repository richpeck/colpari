<?php

/**
 * Gravity Forms Mapping.
 *
 * @package   Awesome Support: Gravity Forms Field Mapping
 * @author    Robert W. Kramer III for Awesome Support <support@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016. Awesome Support
 *
 */
class WPAS_GF {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;

	/** WPAS GF Mappings Helper */
	public $mappings = null;

	/** WPAS GF/Gravity Forms Mappings */
	public $gf_mappings = null;

	/** Gravity Forms form submission data */
	private $submission_data = array();

	/** WPAS Custom fields */
	private $custom_fields = array();

	/** Gravity Forms form uploads */
	private $attachments = array();


	/** Log Entries */
	protected static $logentries = array();

	/**
	 * @var string
	 */
	protected static $form_id = 'all';

	/**
	 * @var bool
	 */
	private $is_reference_target = false;

	/**
	 * Errors
	 */
	private $errors = null;

	/**
	 * WPAS_GF constructor.
	 */
	public function __construct() {

		global $pagenow, $post_type;

		//if( !wpas_is_plugin_page() && ( isset($pagenow) &&  ! $pagenow === 'edit.php' ) ) {
		//	return;
		//}
		
		// Set up a hook to register a field to flag the users wish to close a ticket (field only applicable on a REPLY TICKET FORM)		
		add_action( 'init', array( $this, 'register_field_close_ticket' ) ); 		
		
		$this->errors = new WP_Error();

		if( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {

			$this->gf_mappings   = get_option( '_wpas_gf_mappings', array() );
			$this->mappings      = new WPAS_GF_MAPPINGS( $this->gf_mappings );
			//$this->custom_fields = WPAS()->custom_fields->get_custom_fields();			
			
			if( is_admin() ) {

				if( ! wpas_is_plugin_page() ) {
					return;
				}

				add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_script' ), 10, 0 );
				add_filter( 'wpas_logs_handles', array( $this, 'register_log_handle' ), 10, 1 );
				add_action( 'tf_pre_save_admin_wpas', array( $this, 'pre_save_admin' ), 10, 3 );

				// Exceptions Notification template on core AS Email settings tab
				add_filter( 'wpas_plugin_settings', array( $this, 'wpas_core_settings_notifications' ), 6, 1 );

			}
			else {

				add_filter( 'upload_dir', array( WPAS_File_Upload::get_instance(), 'set_upload_dir' ) );
				add_filter( 'gform_form_entry_meta', array( $this, 'custom_entry_meta' ), 10, 2 );
				add_action( 'wp_enqueue_scripts', array( $this, 'load_script' ), 10, 0 );

				// Hook Gravity Forms validation filters for mapped fields
				foreach( $this->gf_mappings as $form_id => $value ) {
					add_action( 'gform_after_submission_' . $form_id, array( $this, 'maybe_process_gravity_form' ), 50, 2 );
					add_filter( 'gform_pre_render_' . $form_id, array( $this, 'field_populate' ), 10, 1 );
					//add_filter( 'gform_confirmation_' . $form_id, array( $this, 'form_confirmation' ), 10, 3 );
					//add_filter( 'gform_form_post_get_meta_' . $form_id, array( $this, 'custom_entry_meta' ), 10, 2 );

					// Field validation filters
					$core_fields = array(
						'subject',
						'content',
						'email',
						'ticket_id',
						'product',
						'department',
						'ticket-tag',
						'assignee',
						'ticket_state',
						'status',
						'reference',
					);
					foreach( $this->gf_mappings[ $form_id ] as $key => $mapping_value ) {

						// Don't process options key
						if( $key !== 'options' ) {
							$filter_name = in_array( $key, $core_fields ) ? 'field_validation_' . str_replace( '-', '_', $key ) : 'field_validation_custom_field';
							add_filter( 'gform_field_validation_' . $form_id . '_' . $mapping_value[ 'id' ], array(
								$this,
								$filter_name,
							), 20, 4 );
						}
					}
				}

			}
		}
	}

	/**
	 * Gravity Forms Confirmation
	 *
	 * @param $confirmation
	 * @param $form
	 * @param $entry
	 *
	 * @return string
	 */
	public function form_confirmation( $confirmation, $form, $entry ) {

		foreach( self::$logentries as $entry ) {
			$confirmation .= $entry . '<br/>';
		}

		foreach( $this->errors->get_error_messages() as $code => $error ) {
			$confirmation .= '<div><p>' . '<strong>ERROR</strong>: ' . $code . ' => ' . $error . '<br/>' . '</p></div>';
		}

		return $confirmation;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     3.0.0
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;

	}

	/**
	 * Add a link to the settings page.
	 *
	 * @since  0.1.2
	 *
	 * @param  array $links Plugin links
	 *
	 * @return string[]        Links with the settings
	 */
	public static function settings_page_link( $links ) {
		$link    = add_query_arg( array(
			                          'post_type' => 'ticket',
			                          'page'      => 'wpas-settings',
			                          'tab'       => 'gravity_forms',
		                          ), admin_url( 'edit.php' ) );
		$links[] = "<a href='$link'>" . __( 'Settings', 'wpas-gf' ) . "</a>";

		return $links;

	}

	/**
	 *
	 */
	public function load_admin_script() {

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-widget' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'jquery-ui-selectmenu' );
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_script( 'jquery-ui-button' );
		wp_enqueue_script( 'jquery-ui-dialog' );

		wp_register_script( 'jquery-ui-js', WPAS_GF_URL . 'assets/jquery-ui-1.12.1.custom/jquery-ui.js', null, WPAS_GF_VERSION, 'all' );
		wp_enqueue_script( 'jquery-ui-js' );

		wp_register_style( 'jquery-ui-structure-css', WPAS_GF_URL . 'assets/jquery-ui-1.12.1.custom/jquery-ui.structure.css', null, WPAS_GF_VERSION, 'all' );
		wp_enqueue_style( 'jquery-ui-structure-css' );

		wp_register_style( 'jquery-ui-theme-css', WPAS_GF_URL . 'assets/jquery-ui-1.12.1.custom/jquery-ui.theme.css', null, WPAS_GF_VERSION, 'all' );
		wp_enqueue_style( 'jquery-ui-theme-css' );

		wp_register_style( 'jquery-ui-css', WPAS_GF_URL . 'assets/jquery-ui-1.12.1.custom/jquery-ui.css', null, WPAS_GF_VERSION, 'all' );
		wp_enqueue_style( 'jquery-ui-css' );

		wp_enqueue_style( 'wpas-gravity-forms-css', WPAS_GF_URL . 'assets/css/admin.css', null, WPAS_GF_VERSION, 'all' );

		wp_enqueue_script( 'wpas-gravity-forms-script', WPAS_GF_URL . 'assets/js/gravity-forms.js', array(
			'jquery',
			'heartbeat',
		), WPAS_GF_VERSION, true );

		$this->load_plugin_textdomain();
		$this->textdomain();

	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * With the introduction of plugins language packs in WordPress
	 * loading the textdomain is slightly more complex.
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
	public function load_plugin_textdomain() {

		$lang_dir       = WPAS_GF_ROOT . 'languages/';
		$lang_path      = WPAS_GF_PATH . 'languages/';
		$locale         = apply_filters( 'plugin_locale', get_locale(), 'wpas-gf' );
		$mofile         = "wpas-gf-$locale.mo";
		$glotpress_file = WP_LANG_DIR . '/plugins/awesome-support-gravity-forms/' . $mofile;

		// Look for the GlotPress language pack first of all
		if( file_exists( $glotpress_file ) ) {
			$language = load_textdomain( 'wpas-gf', $glotpress_file );
		}
		elseif( file_exists( $lang_path . $mofile ) ) {
			$language = load_textdomain( 'wpas-gf', $lang_path . $mofile );
		}
		else {
			$language = load_plugin_textdomain( 'wpas-gf', false, $lang_dir );
		}

		return $language;

	}

	/**
	 *
	 */
	public function textdomain() {

		wp_localize_script( 'wpas-gravity-forms-script', 'localized',
		                    array(
			                    'field_mappings'                => esc_html__( 'Field Mappings', 'wpas-gf' ),
			                    'add_new_mapping'               => esc_html__( 'Add New Mapping', 'wpas-gf' ),
			                    'field_hide'                    => esc_html__( 'Hide', 'wpas-gf' ),
			                    'field_force'                   => esc_html__( 'Force', 'wpas-gf' ),
			                    'field_validate'                => esc_html__( 'Validate', 'wpas-gf' ),
			                    'field_require'                 => esc_html__( 'Require', 'wpas-gf' ),
			                    'field_populate'                => esc_html__( 'Populate', 'wpas-gf' ),
			                    'delete'                        => esc_html__( 'Delete this mapping', 'wpas-gf' ),
			                    'select_a_field'                => esc_html__( 'Select a field', 'wpas-gf' ),
			                    'select_a_form'                 => esc_html__( 'Select a form', 'wpas-gf' ),
			                    'all_forms_mapped'              => esc_html__( 'No forms available', 'wpas-gf' ),
			                    'all_gravity_forms_are_mapped'  => esc_html__( 'All Gravity Forms are currently mapped.', 'wpas-gf' ),
			                    'gravity_form'                  => esc_html__( 'Form', 'wpas-gf' ),
			                    'select_the_gravity_form'       => esc_html__( 'Select the Gravity Form.', 'wpas-gf' ),
			                    'subject'                       => esc_html__( 'Subject/Title', 'wpas-gf' ),
			                    'content'                       => esc_html__( 'Content', 'wpas-gf' ),
			                    'email'                         => esc_html__( 'Email', 'wpas-gf' ),
			                    'ticket_id'                     => esc_html__( 'Ticket ID', 'wpas-gf' ),
			                    'department'                    => esc_html__( 'Department', 'wpas-gf' ),
			                    'product'                       => esc_html__( 'Product', 'wpas-gf' ),
			                    'status_options'                => esc_html__( 'Status Options', 'wpas-gf' ),
			                    'status'                        => esc_html__( 'Ticket State', 'wpas-gf' ),
			                    'assignee'                      => esc_html__( 'Assignee', 'wpas-gf' ),
			                    'reference'                     => esc_html__( 'Reference', 'wpas-gf' ),
			                    'custom_fields'                 => esc_html__( 'Custom Fields', 'wpas-gf' ),
			                    'ticket_state'                  => esc_html__( 'Ticket Status', 'wpas-gf' ),
			                    'form_options'                  => esc_html__( 'Form Options', 'wpas-gf' ),
			                    'attach_uploaded_files'         => esc_html__( 'Attach uploaded files', 'wpas-gf' ),
			                    'allow_create_user'             => esc_html__( 'Allow create user', 'wpas-gf' ),
			                    'include_unmapped_fields'       => esc_html__( 'Include unmapped fields in ticket body.', 'wpas-gf' ),
								'new_user_send_wp_email_to_admin' => esc_html__( 'Send standard New User WordPress email to the admin - if a new user is created.', 'wpas-gf' ),
								'new_user_send_wp_email_to_user' => esc_html__( 'Send standard New User WordPress email to the new user - if a new user is created.' , 'wpas-gf' ),
								'hide_field_id_in_ticket_body'	=> esc_html__( 'Hide the field key ID when a field is added to the ticket body', 'wpas-gf' ),
								'hide_blanks'                  	=> esc_html__( 'Hide blank fields when fields are added to the ticket body', 'wpas-gf' ),
			                    'include_wpas_gf_stats'         => esc_html__( 'Include WPAS GF Details', 'wpas-gf'),
			                    'confirm_close_unsaved_changes' => esc_html__( 'You have unsaved changes. Save changes now?', 'wpas-gf' ),
			                    'confirm_delete_this_mapping'   => esc_html__( 'Delete this form mapping?', 'wpas-gf' ),
			                    'tab_menu_common_fields'        => esc_html__( 'Common Fields', 'wpas-gf' ),
			                    'tab_menu_custom_fields'        => esc_html__( 'Custom Fields', 'wpas-gf' ),
			                    'tab_menu_general'              => esc_html__( 'Form Settings', 'wpas-gf' ),
			                    'tab_menu_security'             => esc_html__( 'Security', 'wpas-gf' ),
			                    'tab_menu_advanced'             => esc_html__( 'Advanced', 'wpas-gf' ),
			                    'tabs_forms_menu_status'        => esc_html__( 'Status', 'wpas-gf' ),
			                    'tabs_forms_menu_quickset'      => esc_html__( 'Quick Set', 'wpas-gf' ),
			                    'WPAS_Status'                   => wpas_get_post_status(),
			                    'GF_Forms'                      => $this->getGFForms( true ),
			                    'GF_Mappings'                   => get_option( '_wpas_gf_mappings', array() ),
			                    'attributes'                    => array(
				                    'content'      => array( 'require' ),
				                    'subject'      => array( 'require', 'hide' ),
				                    'email'        => array( 'require', 'hide', 'validate', 'populate' ),
				                    'ticket_id'    => array( 'require', 'hide', 'validate', 'populate' ),
				                    'ticket_state' => array( 'require', 'hide', 'validate', 'populate' ),
				                    'department'   => array( 'require', 'hide', 'validate', 'populate' ),
				                    'product'      => array( 'require', 'hide', 'validate', 'populate' ),
				                    'status'       => array( 'require', 'hide', 'validate', 'populate' ),
				                    'ticket-tag'   => array( 'require', 'hide' ),
				                    'assignee'     => array( 'require', 'hide', 'validate', 'populate' ),
				                    'reference'    => array( 'validate', 'populate' ),
				                    'custom_field' => array( 'require', 'hide', 'populate' ),
			                    ),
			                    'help'                          => array(
				                    'content'      => array(
					                    'field'    => array(
						                    0 => esc_html__( 'This field is always required.', 'wpas-gf' ),
					                    ),
					                    'require'  => array( esc_html__( 'Require', 'wpas-gf' ) ),
					                    'hide'     => array( esc_html__( '' ) ),
					                    'validate' => array( esc_html__( '' ) ),
					                    'populate' => array( esc_html__( '' ) ),
					                    'force'    => array( esc_html__( '' ) ),
				                    ),
				                    'subject'      => array(
					                    'field'    => array(
						                    0 => esc_html__( 'If using form for new ticket:  Mapping is required.', 'wpas-gf' ),
						                    1 => esc_html__( 'If using form for replies to a ticket:  Mapping is not required.', 'wpas-gf' ),
						                    2 => esc_html__( 'If using form for status/field updates:  Mapping is not required.', 'wpas-gf' ),
					                    ),
					                    'require'  => array( esc_html__( 'Check this for New Tickets', 'wpas-gf' ) ),
					                    'hide'     => array( esc_html__( 'Hide this field value from ticket or ticket replies.', 'wpas-gf' ) ),
					                    'validate' => array( esc_html__( '' ) ),
					                    'populate' => array( esc_html__( '' ) ),
					                    'force'    => array( esc_html__( '' ) ),
				                    ),
				                    'ticket_id'    => array(
					                    'field'    => array(
						                    0 => esc_html__( 'If using form for new ticket:  Mapping is not required.', 'wpas-gf' ),
						                    1 => esc_html__( 'If using form for replies to a ticket:  Mapping is required.', 'wpas-gf' ),
						                    2 => esc_html__( 'If using form for status/field updates:  Mapping is required.', 'wpas-gf' ),
					                    ),
					                    'require'  => array( esc_html__( 'Check this for ticket replies and status/field updates.', 'wpas-gf' ) ),
					                    'hide'     => array( esc_html__( 'Hide this field value from ticket or ticket replies.', 'wpas-gf' ) ),
					                    'validate' => array( esc_html__( 'Verify that the ticket ID is valid. If mapping this field, turning on this option is STRONGLY recommended.  Additionally, we strongly recommend that you require an email address and enable Email Validate to validate that the ticket id belongs to user.', 'wpas-gf' ) ),
					                    'populate' => array( esc_html__( 'This field can only be pre-populated if the user is logged in.', 'wpas-gf' ) ),
					                    'force'    => array( esc_html__( '' ) ),
				                    ),
				                    'email'        => array(
					                    'field'    => array(
						                    0 => esc_html__( 'If using form for new ticket:  Mapping is required.', 'wpas-gf' ),
						                    1 => esc_html__( 'If using form for replies to a ticket:  Mapping is not required but STRONGLY recommended.', 'wpas-gf' ),
						                    2 => esc_html__( 'If using form for status/field updates: Mapping is not required.', 'wpas-gf' ),
						                    3 => esc_html__( 'If VALIDATE is enabled: the field will require a valid WP user email.', 'wpas-gf' ),
					                    ),
					                    'require'  => array( esc_html__( 'Check this for New Tickets', 'wpas-gf' ) ),
					                    'hide'     => array( esc_html__( 'Hide this field value from ticket or ticket replies.', 'wpas-gf' ) ),
					                    'validate' => array( esc_html__( 'Recommended when mapping Ticket ID.', 'wpas-gf' ) ),
					                    'populate' => array( esc_html__( 'Populate with email address if user is logged in.', 'wpas-gf' ) ),
					                    'force'    => array( esc_html__( '' ) ),
				                    ),
				                    'ticket_state' => array(
					                    'field'    => array(
						                    0 => esc_html__( 'If using form for New Ticket:  Mapping is not required.', 'wpas-gf' ),
						                    1 => esc_html__( 'If using form for Replies to a ticket:  Mapping is not required.', 'wpas-gf' ),
						                    2 => esc_html__( 'If using form for Status/Field updates: Mapping is not required.', 'wpas-gf' ),
					                    ),
					                    'require'  => array( esc_html__( 'It is recommended that this be checked if this field is mapped.', 'wpas-gf' ) ),
					                    'hide'     => array( esc_html__( 'Hide this field value from ticket or ticket replies.', 'wpas-gf' ) ),
					                    'validate' => array( esc_html__( 'Ensures that a valid AS Ticket State value has been selected.', 'wpas-gf' ) ),
					                    'populate' => array( esc_html__( 'Auto-populate field.', 'wpas-gf' ) ),
					                    'force'    => array( esc_html__( '' ) ),
				                    ),

				                    'department'              => array(
					                    'field'    => array(
						                    0 => esc_html__( 'If using form for New Ticket:  Mapping is not required.', 'wpas-gf' ),
						                    1 => esc_html__( 'If using form for Ticket Replies:  Mapping is not required.', 'wpas-gf' ),
						                    2 => esc_html__( 'If using form for Status/Field updates: Mapping is not required.', 'wpas-gf' ),
					                    ),
					                    'require'  => array( esc_html__( 'Require this field to contain a value.', 'wpas-gf' ) ),
					                    'hide'     => array( esc_html__( 'Hide this field value from ticket or ticket replies.', 'wpas-gf' ) ),
					                    'validate' => array( esc_html__( 'Ensures that a valid AS Department value has been selected.', 'wpas-gf' ) ),
					                    'populate' => array( esc_html__( 'Auto-populate field with valid AS Departments (recommended).', 'wpas-gf' ) ),
					                    'force'    => array( esc_html__( '' ) ),
				                    ),
				                    'product'                 => array(
					                    'field'    => array(
						                    0 => esc_html__( 'If using form for New Ticket:  Mapping is not required.', 'wpas-gf' ),
						                    1 => esc_html__( 'If using form for Ticket Replies:  Mapping is not required.', 'wpas-gf' ),
						                    2 => esc_html__( 'If using form for Status/Field updates: Mapping is not required.', 'wpas-gf' ),
					                    ),
					                    'require'  => array( esc_html__( 'Require this field to contain a value.', 'wpas-gf' ) ),
					                    'hide'     => array( esc_html__( 'Hide this field value from ticket or ticket replies.', 'wpas-gf' ) ),
					                    'validate' => array( esc_html__( 'Ensures that a valid AS Product ID has been selected.', 'wpas-gf' ) ),
					                    'populate' => array( esc_html__( 'Auto-populate field with valid AS Products (recommended).', 'wpas-gf' ) ),
					                    'force'    => array( esc_html__( '' ) ),
				                    ),
				                    'ticket-tag'              => array(
					                    'field'    => array(
						                    0 => esc_html__( 'If using form for New Ticket:  Mapping is not required.', 'wpas-gf' ),
						                    1 => esc_html__( 'If using form for Ticket Replies:  Mapping is not required.', 'wpas-gf' ),
						                    2 => esc_html__( 'If using form for Status/Field updates: Mapping is not required.', 'wpas-gf' ),
					                    ),
					                    'require'  => array( esc_html__( 'Require this field to contain a value.', 'wpas-gf' ) ),
					                    'hide'     => array( esc_html__( 'Hide this field value from ticket or ticket replies.', 'wpas-gf' ) ),
					                    'validate' => array( esc_html__( 'Ensures that a valid AS Ticket Tag has been selected.', 'wpas-gf' ) ),
					                    'populate' => array( esc_html__( 'Populate', 'wpas-gf' ) ),
					                    'force'    => array( esc_html__( '' ) ),
				                    ),
				                    'assignee'                => array(
					                    'field'    => array(
						                    0 => esc_html__( 'If using form for New Ticket:  Mapping is not required.', 'wpas-gf' ),
						                    1 => esc_html__( 'If using form for Ticket Replies:  Mapping is not required.', 'wpas-gf' ),
						                    2 => esc_html__( 'If using form for Status/Field updates: Mapping is not required.', 'wpas-gf' ),
					                    ),
					                    'require'  => array( esc_html__( 'Require this field to contain a value.', 'wpas-gf' ) ),
					                    'hide'     => array( esc_html__( 'Hide this field value from ticket or ticket replies.', 'wpas-gf' ) ),
					                    'validate' => array( esc_html__( 'Ensures that a valid AS Assignee has been selected.', 'wpas-gf' ) ),
					                    'populate' => array( esc_html__( 'Populate field with AS Assignees. (recommended)', 'wpas-gf' ) ),
					                    'force'    => array( esc_html__( '' ) ),
				                    ),
				                    'reference'               => array(
					                    'field'    => array(
						                    0 => esc_html__( '' ),
					                    ),
					                    'require'  => array( esc_html__( 'Require this field to contain a value.', 'wpas-gf' ) ),
					                    'hide'     => array( esc_html__( 'Hide this field value from ticket or ticket replies.', 'wpas-gf' ) ),
					                    'validate' => array( esc_html__( 'Validate that the field value exists.', 'wpas-gf' ) ),
					                    'populate' => array( esc_html__( 'Populate field with data.', 'wpas-gf' ) ),
					                    'force'    => array( esc_html__( '' ) ),
				                    ),
				                    'status'                  => array(
					                    'field'    => array(
						                    0 => esc_html__( 'If using form for New Ticket:  Mapping is not required.', 'wpas-gf' ),
						                    1 => esc_html__( 'If using form for Ticket Replies:  Mapping is not required.', 'wpas-gf' ),
						                    2 => esc_html__( 'If using form for Status/Field updates: Mapping is not required.', 'wpas-gf' ),
					                    ),
					                    'require'  => array( esc_html__( 'It is recommended that this be checked if this field is mapped.', 'wpas-gf' ) ),
					                    'hide'     => array( esc_html__( 'Do not output this field value in ticket or ticket replies.', 'wpas-gf' ) ),
					                    'validate' => array( esc_html__( 'Ensures that a valid AS Status has been selected.', 'wpas-gf' ) ),
					                    'populate' => array( esc_html__( 'Populate this field with AS Status values. (recommended)', 'wpas-gf' ) ),
					                    'force'    => array( esc_html__( '' ) ),
				                    ),
				                    'custom_field'            => array(
					                    'field'    => array(
						                    0 => esc_html__( 'If using form for New Ticket:  Mapping is not required.', 'wpas-gf' ),
						                    1 => esc_html__( 'If using form for Ticket Replies:  Mapping is not required.', 'wpas-gf' ),
						                    2 => esc_html__( 'If using form for Status/Field updates: Mapping is not required.', 'wpas-gf' ),
					                    ),
					                    'require'  => array( esc_html__( 'Require this field to contain a value.', 'wpas-gf' ) ),
					                    'hide'     => array( esc_html__( 'Do not output this field value in ticket or ticket replies.', 'wpas-gf' ) ),
					                    'validate' => array( esc_html__( 'Validate this field.', 'wpas-gf' ) ),
					                    'populate' => array( esc_html__( 'Populate this field.', 'wpas-gf' ) ),
					                    'force'    => array( esc_html__( '' ) ),
				                    ),
				                    'attach_uploaded_files'   => array(
					                    0 => esc_html__( 'Enable to copy form submission uploads to ticket or ticket reply attachments.', 'wpas-gf' ),
				                    ),
				                    'allow_create_user'       => array(
					                    0 => esc_html__( 'When checked, an account will automatically be created for any unrecognized email addresses.', 'wpas-gf' ),
					                    1 => esc_html__( 'It is strongly recommended to enable the Validate attribute on the Email field.', 'wpas-gf' ),
				                    ),
				                    'include_unmapped_fields' => array(
					                    0 => esc_html__( 'When checked, any fields that are not mapped to a core or custom field will have its data added to the body of the ticket/reply.', 'wpas-gf' ),
				                    ),
				                    'hide_blanks' => array(
					                    0 => esc_html__( 'When this is turned on, blank fields will not be added to the ticket body even if the include unmapped fields option is checked and even if the HIDE option on a mapped field is unchecked. WARNING: Zeros are considered blanks/empty so be careful with numeric values.', 'wpas-gf' ),
				                    ),									
				                    'include_wpas_gf_stats'   => array(
					                    0 => esc_html__( 'Adds misc data to the body for the ticket such as the IP address.', 'wpas-gf' ),
				                    ),
				                    'new_user_send_wp_email_to_admin'   => array(
					                    0 => esc_html__( 'Send standard New User WordPress email to admin if new user is created', 'wpas-gf' ),
									),
				                    'new_user_send_wp_email_to_user'   => array(
					                    0 => esc_html__( 'Send standard New User WordPress email to the new user if a new user is created', 'wpas-gf' ),										
				                    ),
				                    'hide_field_id_in_ticket_body'   => array(
					                    0 => esc_html__( 'Hide the field key ID when a field is added to the ticket body', 'wpas-gf' ),
				                    ),									
			                    ),
			                    /**
			                     * Get all the custom fields.
			                     */
			                    'custom_fields'                 => $this->get_user_custom_fields(),

		                    ) );

	}

	/**
	 * Get array of available Gravity Forms
	 *
	 * @return array
	 */
	public static function getGFForms( $includeFields = false, $gfid = array() ) {

		$forms = array();

		if( class_exists( 'GFAPI' ) && method_exists( 'GFAPI', 'get_forms' ) ) {

			$gfapi = new GFAPI();

			$all_gf_forms = $gfapi::get_forms();

			if( empty( $gfid ) ) {
				$forms[ - 1 ] = __( 'Select a form', 'wpas-gf' );
			}

			foreach( $all_gf_forms as $formid => $form ) {

				if( empty( $gfid ) || in_array( $form[ 'id' ], $gfid ) ) {

					if( $form[ 'is_active' ] && ! $form[ 'is_trash' ] ) {

						$search_criteria                 = array();
						$form_id                         = absint( $form[ 'id' ] );
						$start_date                      = date( 'Y-m-d', strtotime( '-300 days' ) );
						$end_date                        = date( 'Y-m-d', time() );
						$search_criteria[ 'start_date' ] = $start_date;
						$search_criteria[ 'end_date' ]   = $end_date;
						$sorting                         = array( 'key' => 'date_created', 'direction' => 'DESC' );

						$entries = GFAPI::get_entries( $form_id, $search_criteria, $sorting );

						$last_entry    = ! empty( $entries ) ? $entries[ 0 ] : null;
						$entries_count = $gfapi::count_entries( $form[ 'id' ], $search_criteria = array() );

						if( $includeFields ) {
							$fields = array();

							foreach( $form[ 'fields' ] as $fieldid => $val ) {
								$fields[ absint( $val[ 'id' ] ) ] = $val[ 'label' ];
							}

							$forms[ absint( $form[ 'id' ] ) ] = array(
								'title'         => $form[ 'title' ],
								'last_entry'    => $last_entry,
								'entries_count' => $entries_count,
								'fields'        => $fields,
								'notifications' => $form[ 'notifications' ],
								'confirmations' => $form[ 'confirmations' ],
							);

						}
						else {
							$forms[ absint( $form[ 'id' ] ) ] = esc_html( $form[ 'title' ] );

						}
					}
				}
			}
		}

		return $forms;

	}

	/**
	 *
	 */
	public function load_script() {

		wp_enqueue_style( 'wpas-gravity-forms-css', WPAS_GF_URL . 'assets/css/public.css', null, WPAS_GF_VERSION, 'all' );

		$this->load_plugin_textdomain();
		$this->textdomain();

	}

	/**
	 * @param $handles
	 *
	 * @return mixed
	 */
	public function register_log_handle( $handles ) {

		array_push( $handles, 'gravity-forms' );

		return $handles;

	}

	/**
	 * @param $message
	 */
	public function log( $message ) {

		if( ! class_exists( 'WPAS_Logger' ) ) {
			return;
		}

		self::$logentries[] = $message;

		$log = new WPAS_Logger( 'wpas-gf-' . self::$form_id );
		$log->add( $message );

	}

	/**
	 * Email Notification Settings
	 *
	 * Creates an email notification template in core AS Settings -> Email.
	 *
	 * @param   $def    array   Core AS default settings
	 *
	 * @return  array   Return default settings with GF Email Template settings merged.
	 */
	public function wpas_core_settings_notifications( $def ) {

		$settings = array(
			'email' => array(
				'options' => array(
					/* New reply from client */
					array(
						'name' => __( 'Gravity Forms Exception', 'awesome-support' ),
						'type' => 'heading',
					),
					array(
						'name'    => __( 'Enable', 'awesome-support' ),
						'id'      => 'enable_gravity_forms_exceptions',
						'type'    => 'checkbox',
						'default' => true,
						'desc'    => __( 'Do you want to activate this e-mail template?', 'awesome-support' ),
					),
					array(
						'name'    => __( 'Subject', 'awesome-support' ),
						'id'      => 'subject_gravity_forms_exceptions',
						'type'    => 'text',
						'default' => __( 'Gravity Forms Exception Notification', 'awesome-support' ),
					),
					array(
						'name'     => __( 'Content', 'awesome-support' ),
						'id'       => 'content_gravity_forms_exceptions',
						'type'     => 'editor',
						'default'  => '<p>Hi <strong><em>{agent_name},</em></strong></p><p>This is a Gravity Forms Exception Notification</p><p>{message}</p>
<hr><p>Regards,<br>{site_name}</p>',
						'settings' => array( 'quicktags' => true, 'textarea_rows' => 7 ),
					),
				),
			),
		);

		return array_merge_recursive( $def, $settings );

	}

	/**
	 * Populate form fields
	 *
	 * @param $form
	 *
	 * @return mixed
	 */
	public function field_populate( $form ) {

		self::$form_id = $form[ 'id' ];
		$form_id       = $form[ 'id' ];

		// Check each field in form for ones we populate
		foreach( $form[ 'fields' ] as &$field ) {

			// Upload Field
			if( in_array( GFFormsModel::get_input_type( $field ), array( 'fileupload', 'post_image' ) ) ) {
				if( $this->get_form_option_value( $this->gf_mappings[ $form_id ], 'attach_uploaded_files' ) ) {
					continue;
				}
			}
			// Departments
			elseif( $field->id == $this->mappings->get_field_setting( $form_id, 'department' ) ) {

				// GF Populate enabled?
				if( $this->mappings->get_field_setting( $form_id, 'department', 'populate' ) ) {

					// Enabled in core AS?
					if( false === wpas_get_option( 'departments', false ) ) {
						$this->log( __FUNCTION__ . ' POPULATE: ' . $field->label . ' Departments not enabled.' );
						continue;
					}

					// Initialize dropdown array
					$items = $this->init_items_array( $field );

					// Get Departments taxonomy terms
					$departments = get_terms( array(
						                          'taxonomy'   => 'department',
						                          'hide_empty' => false,
					                          ) );

					// Populate dropdown array with departments
					foreach( $departments as $department ) {
						$this->log( __FUNCTION__ . ' POPULATE: ' . $field->label . ' ' . $department->term_id . ' -> ' . $department->name );
						$items[] = array( 'text' => $department->name, 'value' => $department->term_id );
					}

					$field->choices = $items;
				}
			}
			// Products
			elseif( $field->id == $this->mappings->get_field_setting( $form_id, 'product' ) ) {

				if( $this->mappings->get_field_setting( $form_id, 'product', 'populate' ) ) {

					// Get Departments taxonomy terms
					$products = get_terms( array(
						                       'taxonomy'   => 'product',
						                       'hide_empty' => false,
					                       ) );

					// Initialize dropdown array
					$items = $this->init_items_array( $field );

					// Populate dropdown array with products
					if( ! empty( $products ) ) {
						foreach( $products as $product ) {
							$this->log( __FUNCTION__ . ' POPULATE: ' . $field->label . ' ' . $product->term_id . ' -> ' . $product->name );
							$items[] = array( 'text' => $product->name, 'value' => $product->term_id );
						}
					}
					else {
						$this->log( __FUNCTION__ . ' POPULATE: ' . $field->label . ' No Products Found.' );
					}

					$field->choices = $items;
				}
			}
			// Ticket Id
			elseif( $field->id == $this->mappings->get_field_setting( $form_id, 'ticket_id' ) ) {

				if( $this->mappings->get_field_setting( $form_id, 'ticket_id', 'populate' ) ) {

					// Initialize dropdown array
					$items = $this->init_items_array( $field );

					// List all tickets

					$user_id = get_current_user_id();

					if( 0 !== $user_id ) {

						$post_status   = 'any';
						$ticket_status = 'open';

						$tickets = wpas_get_user_tickets( $user_id, $ticket_status, $post_status );

						foreach( $tickets as $ticket ) {
							$this->log( __FUNCTION__ . ' POPULATE: ' . $field->label . ' ' . $ticket->ID . ' -> ' . $ticket->post_title );
							$items[] = array(
								'text'  => '#' . $ticket->ID . ' - ' . $ticket->post_title,
								'value' => $ticket->ID,
							);
						}
					}

					$field->choices = $items;
				}
			}
			// Status
			elseif( $field->id == $this->mappings->get_field_setting( $form_id, 'status' ) ) {

				if( $this->mappings->get_field_setting( $form_id, 'status', 'populate' ) ) {

					// Initialize dropdown array
					$items = $this->init_items_array( $field );

					$this->log( __FUNCTION__ . ' POPULATE: ' . $field->label . ' open' . ' -> ' . 'Open' );
					$items[] = array( 'text' => 'Open', 'value' => 'open' );

					$this->log( __FUNCTION__ . ' POPULATE: ' . $field->label . ' closed' . ' -> ' . 'Closed' );
					$items[] = array( 'text' => 'Closed', 'value' => 'closed' );

					$field->choices = $items;
				}
			}
			// State
			elseif( $field->id == $this->mappings->get_field_setting( $form_id, 'ticket_state' ) ) {

				if( $this->mappings->get_field_setting( $form_id, 'ticket_state', 'populate' ) ) {
					$custom_statuses = wpas_get_post_status();

					// Initialize dropdown array
					$items = $this->init_items_array( $field );

					foreach( $custom_statuses as $key => $status ) {
						$this->log( __FUNCTION__ . ' POPULATE: ' . $field->label . ' ' . $key . ' -> ' . $status );
						$items[] = array( 'text' => $status, 'value' => $key );
					}

					$field->choices = $items;
				}
			}
			// Assignee
			elseif( $field->id == $this->mappings->get_field_setting( $form_id, 'assignee' ) ) {

				if( $this->mappings->get_field_setting( $form_id, 'assignee', 'populate' ) ) {

					// Initialize dropdown array
					$items = $this->init_items_array( $field );

					$agents = $this->get_agents();

					if( $agents ) {
						foreach( $agents as $key => $value ) {
							$items[] = array(
								'text'  => $value,
								'value' => $key,
							);
						}

					}
					else {

						$default_id = wpas_get_option( 'assignee_default', 1 );

						if( empty( $default_id ) ) {
							$default_id = 1;
						}

						$this->log( __FUNCTION__ . ' POPULATE: ' . $field->label . ' ' . $default_id . ' -> ' . 'Default Assignee' );
						$items[] = array( 'text' => 'Default Assignee', 'value' => $default_id );
					}

					$field->choices = $items;
				}
			}
			// Email
			elseif( $field->id == $this->mappings->get_field_setting( $form_id, 'email' ) ) {

				if( $this->mappings->get_field_setting( $form_id, 'email', 'populate' ) ) {

					$current_user = wp_get_current_user();

					if( $current_user instanceof WP_User ) {
						$field->defaultValue = $current_user->user_email;
					}
				}
			}
			// Reference
			elseif( $field->id == $this->mappings->get_field_setting( $form_id, 'reference' ) ) {

				if( $this->mappings->get_field_setting( $form_id, 'reference', 'populate' ) ) {
					/* A uniqid, like: 4b3403665fea6 */
					$field->defaultValue = uniqid();

					/* We can also prefix the uniqid, this the same as
					 * doing:
					 *
					 * $uniqid = $prefix . uniqid();
					 * $uniqid = uniqid($prefix);
					 */
					//printf("uniqid('php_'): %s\r\n", uniqid('php_'));

					/* We can also activate the more_entropy parameter, which is
					 * required on some systems, like Cygwin. This makes uniqid()
					 * produce a value like: 4b340550242239.64159797
					 */
					//printf("uniqid('', true): %s\r\n", uniqid('', true));
					//$field->defaultValue = "POPULATED";
				}
			}
			// All others (custom fields)
			else {

				$custom_fields = $this->get_user_custom_fields();

				$field_name = $this->mappings->get_field_name_by_id( $form_id, $field->id );

				if( ! $this->mappings->get_field_setting( $form_id, $field_name, 'populate' ) ) {
					continue;
				}

				if( $custom_fields[ $field_name ][ 'args' ][ 'taxo_std' ] ) {
					//continue;
				}

				switch ( $custom_fields[ $field_name ][ 'args' ][ 'field_type' ] ) {

					case 'taxonomy':

						// Initialize dropdown array
						$items = $this->init_items_array( $field );

						$terms = get_terms( array(
							                    'taxonomy'   => $field_name,
							                    'hide_empty' => false,
						                    ) );

						// Populate with choices
						foreach( $terms as $term ) {
							$this->log( __FUNCTION__ . ' POPULATE: ' . $field->label . ' ' . $term->term_id . ' -> ' . $term->name );
							//$this->log( __FUNCTION__ . ' POPULATE: ' . $field->label . ' ' . $term->term_id . ' -> ' . $term->name );
							$items[] = array( 'text' => $term->name, 'value' => $term->term_id );
						}

						$field->choices = $items;

						break;

					case 'checkbox':
					case 'radio':
					case 'select':
					case 'multiselect':

						$items = $this->init_items_array( $field );

						foreach( $custom_fields[ $field_name ][ 'args' ][ 'options' ] as $label => $value ) {
							$this->log( __FUNCTION__ . ' POPULATE: ' . $field_name . ' ' . $label . ' ' . $value );
							$items[] = array( 'text' => $value, 'value' => $label );
						}

						$field->choices = $items;

						break;

					default:

						continue;
				}

			}

		}

		return $form;

	}

	/**
	 * @param $result
	 * @param $value
	 * @param $form
	 * @param $field
	 *
	 * @return mixed
	 */
	public function field_validation_subject( $result, $value, $form, $field ) {

		return $this->required_and_valid( 'subject', $result, $value, $form, $field, __FUNCTION__ );

	}

	/**
	 * Check if field is required and input is valid
	 *
	 * @param $field_name
	 * @param $result
	 * @param $value
	 * @param $form
	 * @param $field
	 * @param $function
	 *
	 * @return mixed
	 */
	public function required_and_valid( $field_name, $result, $value, $form, $field, $function ) {

		self::$form_id = $form[ 'id' ];
		$form_id       = $form[ 'id' ];

		if( $this->mappings->get_field_setting( $form_id, $field_name, 'require' ) ) {
			if( ! isset( $value ) || empty( $value ) || $value === '-1' ) {
				$result[ 'is_valid' ] = false;
				$result[ 'message' ]  = $field->label . __( ': is required.', 'wpas-gf' );

				$this->log( $function . ' REQUIRE: ' . $field_name . ' not specified.' );

				return $result;
			}

			$this->log( $function . ' REQUIRE: ' . $field_name . ' ' . ( is_array( $value ) ? implode( ",", $value ) : $value ) );

			return $result;
		}

		$this->log( $function . ' ' . $field_name . ' ' . ( is_array( $value ) ? implode( ",", $value ) : $value ) );

		return $result;

	}

	/**
	 * @param $result
	 * @param $value
	 * @param $form
	 * @param $field
	 *
	 * @return mixed
	 */
	public function field_validation_email( $result, $value, $form, $field ) {

		$form_id = $form[ 'id' ];

		$ar_value[ 0 ] = is_array( $value ) ? $value[ 0 ] : $value;

		// Require
		if( $this->mappings->get_field_setting( $form_id, 'email', 'require' ) ) {

			if( array_filter( $ar_value, array( $this, "field_validation_email_callback" ) ) ) {
				$result[ 'is_valid' ] = false;
				$result[ 'message' ]  = __( 'Email is required.', 'wpas-gf' );

				$this->log( __FUNCTION__ . ' REQUIRE: Email not specified.' );

				return $result;
			}

			$this->log( __FUNCTION__ . ' REQUIRE: Email ' . $ar_value[ 0 ] . ' specified.' );

			return $result;
		}

		// Validate
		if( $this->mappings->get_field_setting( $form_id, 'email', 'validate' ) ) {
			$user = get_user_by( 'email', $ar_value[ 0 ] );

			if( $user === false ) {
				$result[ 'is_valid' ] = false;
				$result[ 'message' ]  = __( 'Email validation failed.', 'wpas-gf' );
				$this->log( __FUNCTION__ . ' VALIDATE: Email not found - ' . $ar_value[ 0 ] );

				return $result;
			}

			$this->log( __FUNCTION__ . ' VALIDATE: Email found - ' . $ar_value[ 0 ] );

			return $result;
		}

		$this->log( __FUNCTION__ . ' Email - ' . $ar_value[ 0 ] );

		return $result;

	}

	/**
	 * @param $var
	 *
	 * @return bool
	 */
	public function field_validation_email_callback( $var ) {
		return ! isset( $var ) || empty( $var );
	}

	/**
	 * @param $result
	 * @param $value
	 * @param $form
	 * @param $field
	 *
	 * @return mixed
	 */
	public function field_validation_ticket_id( $result, $value, $form, $field ) {

		$result = $this->required_and_valid( 'ticket_id', $result, $value, $form, $field, __FUNCTION__ );

		if( ! $result[ 'is_valid' ] ) {
			return $result;
		}

		$form_id = $form[ 'id' ];

		// Ticket ID specified. Validate?
		if( $this->mappings->get_field_setting( $form_id, 'ticket_id', 'validate' ) ) {

			$ticket = get_post( $value );

			// Ticket ID is a ticket
			if( 'ticket' !== $ticket->post_type ) {
				$result[ 'is_valid' ] = false;
				$result[ 'message' ]  = __( 'Invalid Ticket ID.', 'wpas-gf' );

				$this->log( __FUNCTION__ . ' VALIDATE: Invalid Ticket ID ' . $ticket->ID . '. Not a Ticket.' );

				return $result;
			}

			$user_id = get_current_user_id();

			if( ! empty( $user_id ) ) {
				$user = get_user_by( 'id', $user_id );
			} // Email address specified exists
			elseif( $this->mappings->get_field_setting( $form_id, 'email', 'validate' ) ) {
				// mapping specifies that the email address be validated
				$user_email = rgpost( 'input_' . $this->gf_mappings[ $form_id ][ 'email' ][ 'id' ] );
				$user       = get_user_by( 'email', $user_email );
			}else {
				// no special processing for the email address - just make sure it belongs to the ticket
				$user_email = rgpost( 'input_' . $this->gf_mappings[ $form_id ][ 'email' ][ 'id' ] );
				if (! empty( $user_email ) ) {
					$user = get_user_by( 'email', $user_email );
				}
			}


			// Check if user exists
			if( empty( $user ) ) {
				$result[ 'is_valid' ] = false;
				$result[ 'message' ]  = __( 'Invalid Ticket ID (Probably because ID does not match the email address provided).', 'wpas-gf');

				$this->log( __FUNCTION__ . ' VALIDATE: Invalid Ticket ID ' . $ticket->ID . ' Unable to find user ' . $user->user_email );

				return $result;
			}

			// Check if ticket belongs to user
			if( $ticket->post_author != $user->ID ) {
				$result[ 'is_valid' ] = false;
				$result[ 'message' ]  = __( 'Invalid Ticket ID - ticket does not belong to the specified user.', 'wpas-gf' );

				$this->log( __FUNCTION__ . ' VALIDATE: Invalid Ticket ID ' . $ticket->ID . ' does not belong to User ID ' . $user->ID );

				return $result;
			}
		}

		$this->log( __FUNCTION__ . ' ' . $value );

		return $result;

	}

	/**
	 * @param $result
	 * @param $value
	 * @param $form
	 * @param $field
	 *
	 * @return mixed
	 */
	public function field_validation_content( $result, $value, $form, $field ) {

		return $this->required_and_valid( 'content', $result, $value, $form, $field, __FUNCTION__ );

	}

	/**
	 * @param $result
	 * @param $value
	 * @param $form
	 * @param $field
	 *
	 * @return mixed
	 */
	public function field_validation_ticket_state( $result, $value, $form, $field ) {

		$result = $this->required_and_valid( 'status', $result, $value, $form, $field, __FUNCTION__ );

		if( ! $result[ 'is_valid' ] ) {
			return $result;
		}

		$form_id = $form[ 'id' ];

		if( $this->mappings->get_field_setting( $form_id, 'status', 'validate' ) ) {

			$custom_statuses = wpas_get_post_status();

			if( ! array_key_exists( $value, $custom_statuses ) ) {
				$result[ 'is_valid' ] = false;
				$result[ 'message' ]  = __( 'Invalid Ticket Status.', 'wpas-gf');
				$this->log( __FUNCTION__ . ' VALIDATE: Invalid Ticket Status ' . $value );
			}
		}

		return $result;

	}

	/**
	 * @param $result
	 * @param $value
	 * @param $form
	 * @param $field
	 *
	 * @return mixed
	 */
	public function field_validation_product( $result, $value, $form, $field ) {

		$result = $this->required_and_valid( 'product', $result, $value, $form, $field, __FUNCTION__ );

		if( ! $result[ 'is_valid' ] ) {
			return $result;
		}

		$form_id = $form[ 'id' ];

		if( $this->mappings->get_field_setting( $form_id, 'product', 'validate' ) ) {

			$products = get_terms( array(
				                       'taxonomy'   => 'product',
				                       'hide_empty' => false,
				                       'include'    => $value,
			                       ) );

			if( empty( $products ) ) {
				$result[ 'is_valid' ] = false;
				$result[ 'message' ]  = __( 'Invalid Product.', 'wpas-gf' );
				$this->log( __FUNCTION__ . ' VALIDATE: Invalid Product ' . $value );

				return $result;
			}

			$this->log( __FUNCTION__ . ' VALIDATE: Product ' . $value );

			return $result;

		}

		$this->log( __FUNCTION__ . ' Product: ' . $value );

		return $result;

	}

	/**
	 * @param $result
	 * @param $value
	 * @param $form
	 * @param $field
	 *
	 * @return mixed
	 */
	public function field_validation_department( $result, $value, $form, $field ) {

		$result = $this->required_and_valid( 'department', $result, $value, $form, $field, __FUNCTION__ );

		if( ! $result[ 'is_valid' ] ) {
			return $result;
		}

		$form_id = $form[ 'id' ];

		$this->errors->add( $form_id, $value );

		if( $this->mappings->get_field_setting( $form_id, 'department', 'validate' ) ) {

			$departments = get_terms( array(
				                          'taxonomy'   => 'department',
				                          'hide_empty' => false,
				                          'include'    => $value,
			                          ) );

			if( empty( $value ) || empty( $departments ) ) {
				$result[ 'is_valid' ] = false;
				$result[ 'message' ]  = __( 'Invalid Department.', 'wpas-gf' );
				$this->log( __FUNCTION__ . ' VALIDATE: Invalid Department ' . $value );

				return $result;
			}

			$this->log( __FUNCTION__ . ' VALIDATE: Department ' . $value );

			return $result;

		}

		$this->log( __FUNCTION__ . ' Department: ' . $value );

		return $result;

	}

	/**
	 * @param $result
	 * @param $value
	 * @param $form
	 * @param $field
	 *
	 * @return mixed
	 */
	public function field_validation_status( $result, $value, $form, $field ) {

		$result = $this->required_and_valid( 'ticket_state', $result, $value, $form, $field, __FUNCTION__ );

		if( ! $result[ 'is_valid' ] ) {
			return $result;
		}

		$form_id = $form[ 'id' ];

		if( $this->mappings->get_field_setting( $form_id, 'ticket_state', 'validate' ) ) {

			$states             = array();
			$states[ 'open' ]   = array( 'text' => 'Open' );
			$states[ 'closed' ] = array( 'text' => 'Closed' );

			if( ! array_key_exists( $value, $states ) ) {
				$result[ 'is_valid' ] = false;
				$result[ 'message' ]  = __( 'Invalid Ticket State.', 'wpas-gf' );
				$this->log( __FUNCTION__ . ' VALIDATE: Invalid Ticket State ' . $value );
			}
		}

		return $result;

	}

	/**
	 * @param $result
	 * @param $value
	 * @param $form
	 * @param $field
	 *
	 * @return mixed
	 */
	public function field_validation_ticket_tag( $result, $value, $form, $field ) {
		return $result;
	}

	/**
	 * @param $result
	 * @param $value
	 * @param $form
	 * @param $field
	 *
	 * @return mixed
	 */
	public function field_validation_assignee( $result, $value, $form, $field ) {

		$result = $this->required_and_valid( 'assignee', $result, $value, $form, $field, __FUNCTION__ );

		if( ! $result[ 'is_valid' ] ) {
			return $result;
		}

		$form_id = $form[ 'id' ];

		if( $this->mappings->get_field_setting( $form_id, 'assignee', 'validate' ) ) {

			$default_id = wpas_get_option( 'assignee_default', 1 );

			if( empty( $default_id ) ) {
				$default_id = 1;
			}

			if( $value !== 1 && $value !== $default_id && ! array_key_exists( $value, $this->get_agents() ) ) {
				$result[ 'is_valid' ] = false;
				$result[ 'message' ]  = __('Invalid Assignee.', 'wpas-gf' );
				$this->log( __FUNCTION__ . ' VALIDATE: Invalid Ticket Status ' . $value );
			}
		}

		return $result;

	}

	/**
	 * @param $result
	 * @param $value
	 * @param $form
	 * @param $field
	 *
	 * @return mixed
	 */
	public function field_validation_reference( $result, $value, $form, $field ) {

		$result = $this->required_and_valid( 'reference', $result, $value, $form, $field, __FUNCTION__ );

		if( ! $result[ 'is_valid' ] ) {
			return $result;
		}

		$form_id = $form[ 'id' ];

		if( $this->mappings->get_field_setting( $form_id, 'reference', 'validate' ) ) {

			$source_form_id  = $this->mappings->get_field_setting( $form_id, 'reference', '', 'source_form' );
			$source_field_id = $this->mappings->get_field_setting( $form_id, 'reference', '', 'source_field' );

			$values                     = array();
			$values[ $source_field_id ] = $value;

			if( ! $this->do_values_exist( $values, $source_form_id ) ) {
				$result[ 'is_valid' ] = false;
				$result[ 'message' ]  = __( 'Invalid Reference value.', 'wpas-gf' );
				$this->log( __FUNCTION__ . ' VALIDATE: Invalid Reference Value ' . $value );
			}
			else {
				$entries = $this->do_values_exist( $values, $source_form_id );

				if( count( $entries ) === 1 ) {
					$this->submission_data[ 'email' ]     = $entries[ 0 ][ 4 ];
					$this->submission_data[ 'ticket_id' ] = $entries[ 0 ][ 5 ];
				}

				//$this->is_reference_target = true;
			}
		}

		return $result;

	}

	/**
	 * @param $result
	 * @param $value
	 * @param $form
	 * @param $field
	 *
	 * @return mixed
	 */
	public function field_validation_custom_field( $result, $value, $form, $field ) {

		$form_id = $form[ 'id' ];

		$field_name = $this->mappings->get_field_name_by_id( $form_id, $field->id );

		$result = $this->required_and_valid( $field_name, $result, $value, $form, $field, __FUNCTION__ );

		return $result;

	}

	/**
	 * @param $values
	 * @param $form_id
	 *
	 * @return array|WP_Error
	 */
	public function do_values_exist( $values, $form_id ) {

		$field_filters = array();

		foreach( $values as $field_id => $value ) {
			$field_filters[] = array(
				'key'   => $field_id,
				'value' => $value,
			);
		}

		$entries = GFAPI::get_entries( $form_id, array( 'status' => 'active', 'field_filters' => $field_filters ) );

		return $entries;
		//return count( $entries ) > 0;
	}

	/**
	 *
	 * @param      $mappings
	 * @param      $option_name
	 *
	 * @return bool
	 */
	public function get_form_option_value( $mappings, $option_name ) {

		$value = false;

		if( isset( $mappings[ 'options' ][ $option_name ] ) && ! empty( $mappings[ 'options' ][ $option_name ] ) ) {
			if( $mappings[ 'options' ][ $option_name ] !== '' ) {
				$value = $mappings[ 'options' ][ $option_name ];
			}
		}

		return $value;
	}

	/**
	 * @param $field
	 *
	 * @return array
	 */
	public function init_items_array( $field ) {

		$items = array();

		if( $field->type === 'select' ) {

			$items[] = array(
				'text'  => __( 'Select a ', 'wpas-gf' ) . $field->label,
				'value' => '-1',
			);
		}

		return $items;

	}

	/**
	 * Get array of available agents
	 *
	 * @return bool|array
	 */
	public function get_agents() {

		$users = shuffle_assoc( wpas_get_users( apply_filters( 'wpas_find_agent_get_users_args', array( 'cap' => 'edit_ticket' ) ) ) );

		$agents = false;

		foreach( $users->members as $user ) {

			$wpas_agent = new WPAS_Member_Agent( $user );

			/**
			 * Make sure the user really is an agent and that he can currently be assigned
			 */
			if( true !== $wpas_agent->is_agent() || false === $wpas_agent->can_be_assigned() ) {
				continue;
			}

			$count = $wpas_agent->open_tickets(); // Total number of open tickets for this agent

			$this->log( __FUNCTION__ . $user->ID . ' -> ' . $user->display_name . '(' . $count . ')' );

			$agents[ $user->ID ] = $user->display_name;
		}

		return $agents;
	}

	/**
	 * Handle saving GF Settings mappings and options
	 *
	 * @param $container
	 * @param $activeTab
	 * @param $options
	 */
	public function pre_save_admin( $container, $activeTab, $options ) {

		// Associative array of mappings
		$gf_mappings = filter_input( INPUT_POST, 'wpas_gf_mapping', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		// Nothing to do if mappings not posted
		if( isset( $gf_mappings ) ) {

			// Add individual form options
			foreach( $gf_mappings as $key => $value ) {

				// Settings per mapping
				$options = array( 'attach_uploaded_files', 'include_unmapped_fields', 'allow_create_user', 'include_wpas_gf_stats' );

				foreach( $options as $option ) {
					$gf_mappings[ $key ][ 'options' ][ $option ] = isset( $gf_mappings[ $key ][ 'options' ][ $option ] )
						? $gf_mappings[ $key ][ 'options' ][ $option ]
						: '0';
				}
			}

			update_option( '_wpas_gf_mappings', $gf_mappings, true );

		}
	}

	/**
	 * Process form submission
	 *
	 * @param $entry
	 * @param $form
	 *
	 * @return mixed
	 */
	public function maybe_process_gravity_form( $entry, $form ) {

		self::$form_id = $form[ 'id' ];
		$this->custom_fields = WPAS()->custom_fields->get_custom_fields();

		$content = '';

		// Build array of mapped fields where data was submitted
		foreach( $this->gf_mappings[ $form[ 'id' ] ] as $key => $mapping ) {
			if( $key !== 'options' && '-1' !== $mapping[ 'id' ] ) {
				$this->submission_data[ $key ] = rgpost( 'input_' . $mapping[ 'id' ] );
			}
		}

		if( $this->is_reference_target ) {
			$entries = $this->do_values_exist( $form[ 'id' ] );

			if( count( $entries ) === 1 ) {
				$this->submission_data[ 'email' ]     = $entries[ 0 ][ 4 ];
				$this->submission_data[ 'ticket_id' ] = $entries[ 0 ][ 5 ];
			}

		}

		// Include form options
		$this->submission_data[ 'options' ] = array(
			'attach_uploaded_files'   			=> $this->get_form_option_value( $this->gf_mappings[ $form[ 'id' ] ], 'attach_uploaded_files' ),
			'allow_create_user'       			=> $this->get_form_option_value( $this->gf_mappings[ $form[ 'id' ] ], 'allow_create_user' ),
			'include_unmapped_fields' 			=> $this->get_form_option_value( $this->gf_mappings[ $form[ 'id' ] ], 'include_unmapped_fields' ),
			'hide_blanks' 						=> $this->get_form_option_value( $this->gf_mappings[ $form[ 'id' ] ], 'hide_blanks' ),
			'include_wpas_gf_stats'   			=> $this->get_form_option_value( $this->gf_mappings[ $form[ 'id' ] ], 'include_wpas_gf_stats' ),
			'new_user_send_wp_email_to_admin' 	=> $this->get_form_option_value( $this->gf_mappings[ $form[ 'id' ] ], 'new_user_send_wp_email_to_admin' ),
			'new_user_send_wp_email_to_user'   	=> $this->get_form_option_value( $this->gf_mappings[ $form[ 'id' ] ], 'new_user_send_wp_email_to_user' ),
			'hide_field_id_in_ticket_body'		=> $this->get_form_option_value( $this->gf_mappings[ $form[ 'id' ] ], 'hide_field_id_in_ticket_body' ),
		);

		// Build ticket/reply content
		if( $this->submission_data[ 'content' ] ) {

			if( $this->submission_data[ 'options' ][ 'include_wpas_gf_stats' ] ) {
				$content .= '<blockquote>';
				$content .= 'Submitted using <strong>(#' . $form[ 'id' ] . ') ' . $form[ 'title' ] . '</strong><br/>';
				$content .= 'Source URL: ' . $entry[ 'source_url' ] . '<br/>';
				$content .= 'Remote IP: ' . $entry[ 'ip' ] . '<br/>';
				$content .= '</blockquote>';
			}

			//$mapped_fields = $this->get_unmapped_fields( $form, $this->gf_mappings[ $form[ 'id' ] ], true );
			foreach( $this->gf_mappings[ $form[ 'id' ] ] as $key => $mapping ) {
				if( $key !== 'options' ) {
					$content .= $this->get_mapped_field_data(
						$this->gf_mappings[ $form[ 'id' ] ],
						$key,
						$this->get_gf_field_label( $mapping[ 'id' ], $form ) );
				}
			}

			/* Include Unmapped Fields */
			if( $this->submission_data[ 'options' ][ 'include_unmapped_fields' ] ) {

				$unmapped_fields = $this->get_unmapped_fields( $form, $this->gf_mappings[ $form[ 'id' ] ] );
				foreach( $unmapped_fields as $key => $value ) {
					
					if ( ! empty( $this->submission_data[ 'options' ][ 'hide_blanks' ] ) && empty( $value ) ) {
						continue;  // field is blank and the hide blanks option is enabled so don't add to the body.
					}
					
					// Add the field  label
					$content .= '<strong>' . $this->get_gf_field_label( $key, $form ) ;
					
					// Optionally include the field id
					if ( ! empty( $this->submission_data[ 'options' ][ 'hide_field_id_in_ticket_body' ] ) ) {	
						$content .=  '</strong><br/>';  // dont' show it
					} else {
						$content .=  ' (#' . $key . ')' . '</strong><br/>' ;
					}
					
					$content .= $value;
					$content .= '<br/><br/>';
				}

			}

			/**
			 * Format the content
			 */
			$this->submission_data[ 'content' ] = wpautop( $content );
		}

		$this->maybe_create_attachments( $entry, $form );

		$gf_ticket     = new WPAS_GF_Ticket( $this->submission_data, $this->get_user_custom_fields(), $this->attachments );
		$ticket_result = $gf_ticket->save_form();

		if( ! $ticket_result[ 'ticket_id' ] ) {
			// Failed creating ticket
			$this->errors->add( $ticket_result[ 'ticket_id' ], 'Error adding ticket or reply.' );
		}

		return $form;

	}

	/**
	 *
	 * @param $entry
	 * @param $form
	 */
	public function maybe_create_attachments( $entry, $form ) {

		// Get Gravity Forms upload file names and paths
		foreach( $form[ 'fields' ] as &$field ) {

			if( ! in_array( GFFormsModel::get_input_type( $field ), array( 'fileupload', 'post_image' ) ) ) {
				continue;
			}

			$uploaded_files = rgar( $entry, $field->id );
			if( empty( $uploaded_files ) ) {
				continue;
			}

			if( $field->multipleFiles ) {
				$uploaded_files = json_decode( $uploaded_files );
			}
			else {
				$uploaded_files = array( $uploaded_files );
			}

			$attachments = array();
			foreach( $uploaded_files as $file ) {

				$pathinfo = pathinfo( GFFormsModel::get_physical_file_path( $file ) );

				$attachments[] = array(
					'filename' => $pathinfo[ 'basename' ],
					'basedir'  => $pathinfo[ 'dirname' ],
				);
			}
			$this->attachments = array_merge( $this->attachments, $attachments );
		}

	}

	/**
	 * @param $entry_meta
	 * @param $form
	 *
	 * @return array
	 */
	public function custom_entry_meta( $entry_meta, $form ) {

		$entry_meta[ 'wpas-gf' ] = array(
			'label'                      => 'Ticket #',
			'is_numeric'                 => true,
			'update_entry_meta_callback' => 'update_entry_meta',
			'is_default_column'          => true,
		);

		return $entry_meta;
	}

	/**
	 * @param $key
	 * @param $lead
	 * @param $form
	 *
	 * @return string
	 */
	public function update_entry_meta( $key, $lead, $form ) {
		//update score
		$value = "5";

		return $value;
	}

	/**
	 * @param $mappings
	 * @param $field_name
	 * @param $label
	 *
	 * @return string
	 */
	public function get_mapped_field_data( $mappings, $field_name, $label ) {

		$html = '';

		if( isset( $this->submission_data[ $field_name ] ) ) {

			if( isset( $mappings[ $field_name ][ 'attributes' ] ) && ! empty( $mappings[ $field_name ][ 'attributes' ][ 'hide' ] ) ) {
				$this->log( __FUNCTION__ . ' HIDE: ' . $field_name . ' -> ' . $this->submission_data[ $field_name ] );
			}
			else {

				if( isset( $this->custom_fields[ $field_name ] ) && 'taxonomy' === $this->custom_fields[ $field_name ][ 'args' ][ 'field_type' ] ) {
					$term = get_term( absint( $this->submission_data[ $field_name ] ) );
					if( is_a( $term, 'WP_Term' ) ) {
						$html .= '<strong>' . $label . '</strong><br/>';
						$html .= $term->name . ' (' . $term->term_id . ')';
						$html .= '<br/><br/>';
					}
				}
				else {
					
					$option_hide_blank = $this->submission_data[ 'options' ][ 'hide_blanks' ];
					
					if ( ! empty( $option_hide_blank ) && empty( $this->submission_data[ $field_name ] ) ) {
						
						// blank field and option to hide blank is enabled so just log things
						$this->log( __FUNCTION__ . ' HIDING BLANKS: ' . $field_name . ' -> ' . $this->submission_data[ $field_name ] );
						
					} else {
						
						$html .= '<strong>' . $label . '</strong><br/>';
						$html .= $this->submission_data[ $field_name ];
						$html .= '<br/><br/>';
						
					}

				}

			}

		}

		return apply_filters( 'gf_mapped_field_data', $html, $mappings, $field_name, $label, $this->submission_data );
	}

	/**
	 * Get Field Label (Gravity Forms)
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public function get_gf_field_label( $id, $form ) {

		foreach( $form[ 'fields' ] as $field ) {
			if( $field->id === absint( $id ) ) {
				return $field->label;
			}
		}

		return '';
	}

	/**
	 * Create array of key/value pairs for unmapped fields
	 *
	 * @return mixed
	 *
	 */
	public function get_unmapped_fields( $form, $mappings, $isMapped = false ) {

		// Create array of key/value pairs for unmapped fields
		$unmapped_fields = array();        // [];

		foreach( $form[ 'fields' ] as $field ) {

			if( in_array( GFFormsModel::get_input_type( $field ), array( 'fileupload', 'post_image' ) ) ) {
				continue;
			}

			$field_id    = $field->id;
			$field_value = rgpost( 'input_' . $field_id );   //isset( $entry[ (string) $field_id ] ) ? $entry[ (string) $field_id ] : '';

			if( $isMapped === $this->in_array_r( $field_id, $mappings ) ) {

				$fields = false;
				$val    = '';

				if( is_array( $field[ 'inputs' ] ) ) {

					// Advanced fields (field array)
					foreach( $field[ 'inputs' ] as $input ) {
						$label                             = $field[ 'type' ] === 'checkbox' ? $field[ 'label' ] : $input[ 'label' ];
						$fields[ (string) $input[ 'id' ] ] = $label;
						$val                               .= ' ' . wp_strip_all_tags( 'input_' . $input[ 'id' ] );   //$entry[ $input[ 'id' ] ] );
					}

				}
				else {

					$fields[ $field[ 'id' ] ] = $field[ 'label' ];
					$val                      .= wp_strip_all_tags( $field_value );
				}

				$unmapped_fields[ $field_id ] = apply_filters( 'gf_unmapped_field', trim( $val ), $field );

				$this->log( __FUNCTION__ . ' ' . $field_id . ' -> ' . $field_value );
			}
		}

		return apply_filters( 'gf_unmapped_field_data', $unmapped_fields );

	}

	/**
	 * @param            $needle
	 * @param            $haystack
	 * @param       bool $strict
	 *
	 * @return      bool
	 */
	public function in_array_r( $needle, $haystack, $strict = false ) {

		foreach( $haystack as $item ) {
			if( isset( $item[ 'id' ] ) ) {
				if( ( $strict ? absint( $item[ 'id' ] ) === $needle : $item[ 'id' ] == $needle ) || ( is_array( $item ) && $this->in_array_r( $needle, $item, $strict ) ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get AS Custom Fields
	 *
	 * Does not return AS core fields.
	 *
	 * @return array
	 */
	public function get_user_custom_fields() {

		$custom_fields = WPAS()->custom_fields->get_custom_fields();

		$return = array();

		foreach( $custom_fields as $key => $custom_field ) {
			if( ! $custom_field[ 'args' ][ 'core' ] && $custom_field[ 'name' ] !== 'department' && $custom_field[ 'name' ] !== 'product' ) {
				$return[ $key ] = $custom_field;
			}
		}

		return $return;
	}

	/**
	* Register a custom field to allow user to map a checkbox/select/radio button to it.
	* If mapped, the ticket will be closed.
	*
	* Note that this duplicates the function provided by mapping to the STATE field.  
	* Either way would allow you to close a ticket now.
	* This is used as a demonstration of how to use custom fields to trigger additional functions inside of the Gravity Forms bridge and Awesome Support.
	*
	* Hook: init
	*
	* @author Awesome Support - Portions contributed by Jamie Madden (https://github.com/digitalchild)
	*/
	public function register_field_close_ticket() { 
	
		$args = array(
			'core'                  => false,
			'field_type'            => 'radio',
			'show_column'           => false,
			'hide_front_end'		=> true,
			'options' 				=> array( '1' => __( 'Close Ticket', 'wpas-gf' ), '0' => __( 'Do Not Close Ticket', 'wpas-gf' )  ),			
			'title'					=> __( 'Close Ticket', 'wpas-gf' ) 
		);

		wpas_add_custom_field( 'gf_close_ticket', $args );
	}
	
}