<?php

class WPAS_Email {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * New e-mails gathered from the mail server.
	 *
	 * @since  0.1.0
	 * @var    array
	 */
	protected $emails = array();

	public function __construct() {

		add_filter( 'wpas_email_notifications_pre_fetch_subject', array( $this, 'add_ticket_id' ),    10, 2 );
		add_filter( 'wpas_email_notifications_body',              array( $this, 'add_delimiter' ),    10, 2 );
		add_filter( 'wpas_plugin_post_types',                     array( $this, 'add_plugin_page' ),  10, 1 );
		add_filter( 'wpas_attachment_can_attach_files',           array( $this, 'can_attach_files' ), 10, 1);

		//add_filter( 'ases_get_user_id', array( $this, 'testing' ), 10, 3 );

		// Polling mode
		if( '0' === wpas_get_option('email_polling_mode', '0')) {
			// WP Cron (Default)
			//add_filter( 'cron_schedules',                       'custom_cron_schedule' );
			//add_action( 'wpas_es_check_mail',                   'wpas_check_mails' );
			add_action( 'wpas_es_check_mail',                   array( $this, 'wpas_check_mails' ));
			add_filter( 'cron_schedules',                       array( $this, 'custom_cron_schedule' ));

			add_action( 'wp_ajax_wpas_check_mails',             'wpas_check_mail_ajax' );

			if ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) {
				//add_action( 'init',                             'schedule_wp_cron' );
				add_action( 'init',                             array( $this, 'schedule_wp_cron' ));
			}
		}
		elseif( is_admin() ) {
			// jQuery Heartbeat
			add_action( 'wp_ajax_wpas_check_mails',             'wpas_check_mail_ajax' );
		}
		add_action( 'wp_ajax_ases_mailbox_test_connect',        'ases_test_settings_ajax' );


		if ( is_admin() ) {

			add_filter( 'wpas_logs_handles', array( $this, 'register_log_handle' ), 10, 1 );
			add_action( 'init',              array( $this, 'mailboxes_cpt' ),       10, 0 );  		// Register post type to handle multiple mailboxes
			add_action( 'init',              array( $this, 'unknown_post_type' ), 	10, 0 );  		// Register unknown post type
			add_action( 'init',              array( $this, 'inbox_rules_cpt' ),    	10, 0 );  		// Register post type for inbox rules
		
			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
								
				add_action( 'init', 							array( $this,				'turn_off_comments'),				11, 0 );  		// Turn off comments explicitly in the new custom post types - attempt to work around WP core bug
				add_action( 'admin_init',						array( $this,				'mailboxes_cpt_metaboxes'),			10, 0 );  		// Register the metaboxes used inside the mailboxes cpt
				add_action( 'admin_init',						array( $this,				'inbox_rules_cpt_metaboxes'),		10, 0 );		// Register the metaboxes used inside the mailbox rules cpt
				add_action( 'save_post_wpas_mailbox_config',	array( $this,				'mailboxes_cpt_save'),				10, 2 ); 		// Save data collected from the multiple mailboxes cpt.
				add_action( 'save_post_wpas_inbox_rules',		array( $this,				'inbox_rules_cpt_save'),			10, 2 ); 		// Save data collected from the inbox rules cpt
				add_action( 'admin_enqueue_scripts',            array( $this,               'load_script' ),                	10, 0 );
				add_action( 'admin_menu',                       array( $this,               'register_submenu_items' ),     	9, 0 );  		
				add_action( 'admin_menu',                       array( $this,               'unknown_count' ),              	10, 0 );
				add_action( 'add_meta_boxes',                   array( $this,               'metaboxes' ),                  	10, 0 );
				add_action( 'wpas_backend_reply_content_after', array( $this,		    	'backend_reply_content_after'),     10, 1 );
				add_action( 'save_post_wpas_unassigned_mail',   array( 'WPAS_Email_Assign', 'save_hook' ),                  	10, 1 );

				add_filter( 'manage_edit-wpas_mailbox_config_columns', 			array( $this,	'show_columns_on_mailboxes_cpt'),    	10, 1 );	// Configure the list of columns that users see on the mailbox config CPT list screen
				add_action( 'manage_wpas_mailbox_config_posts_custom_column', 	array( $this,	'show_single_column_on_mailboxes_cpt'),	10, 2 );	// Callback to show data in each column on the mailbox config CPT list screen				

				add_filter( 'manage_edit-wpas_inbox_rules_columns', 		array( $this,	'show_columns_on_inbox_rules_cpt'),    	10, 1 );		// Configure the list of columns that users see on the inbox rules CPT list screen
				add_action( 'manage_wpas_inbox_rules_posts_custom_column', 	array( $this,	'show_single_column_on_inbox_rules_cpt'),	10, 2 );	// Callback to show data in each column on the inbox rules CPT list screen								
				
			}

		}

	}
	
	/**
	 * Add special notes to certain replies
	 *
	 * @since  0.4.0
	 *
	 */	
	public function backend_reply_content_after( $reply_id ) {
		
		$email_user_type = get_post_meta($reply_id, 'email_user_type', true);
		$email = get_post_meta($reply_id, 'email_user_email', true);
		
		switch( $email_user_type ) {
			
			case "NOT_FOUND":
				echo '<div class="note">';
				echo sprintf( __( "Note : The above reply was received From e-mail address %s and could not be matched so the reply was assumed to be from the ticket creator." ),  $email);
				echo "</div>";
				break;
			
			case "3rd_party_agent":
				echo '<div class="note">';
				echo sprintf( __( "Note : The above reply was received from an interested 3rd party with email address %s"), $email );
				echo '</div>';
				break;
			
		}
		
	}

	/**
	 * Check for emails
	 *
	 * @since  0.1.0
	 *
	 */	
	public function wpas_check_mails() {

		global $current_user;

		/**
		 * Disable WP Cron polling while we check mail
		 */

		// Get the timestamp for the next event.
		$timestamp = wp_next_scheduled( 'wpas_es_check_mail' );
		wp_unschedule_event( $timestamp, 'wpas_es_check_mail' );

		/**
		 * Set Default Assignee
		 */

		$default_id = wpas_get_option( 'assignee_default', 1 );
		if ( empty( $default_id ) ) {
			$default_id = 1;
		}
		$current_user = wp_set_current_user($default_id);

		/**
		 * Check for mail
		 */
		wpas_check_mails();

		/**
		 * Re-enable WP Cron polling
		 */

		$this->schedule_wp_cron();

		return true;
	}

	/**
	 * Schedule WP Cron polling.
	 */
	public function schedule_wp_cron() {

		/**
		 * Avoid rescheduling cron if it's already scheduled.
		 */
		if ( !wp_next_scheduled( 'wpas_es_check_mail' ) ) { //, $args ) ) {

			/**
			 * Schedule mail server polling.
			 */
			wp_schedule_event(
				time() + wpas_get_option( 'email_interval', 3600 ),
				'wpas_es_email_interval',
				'wpas_es_check_mail' );
		}

	}


	/**
	 * WP Cron custom schedule Every 5 minutes by default. Set on settings tab.
	 *
	 * @param array $schedules An array of non-default cron schedules.
	 * @return array Filtered array of non-default cron schedules.
	 */
	public function custom_cron_schedule( $schedules ) {

		$schedules[ 'wpas_es_email_interval' ] = array(
			'interval' => wpas_get_option( 'email_interval', 300 ),
			'display'  => __( 'WPAS ES Email Interval', 'as-email-support' ),
		);

		return $schedules;
	}


	public function testing($user_id, $user_email, $anotherthis ) {
		wpas_write_log('emails', $user_id);
		//wpas_write_log('emails', $user_email);
		$x = 3;

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

		if ( !class_exists( 'Awesome_Support' ) ) {
			deactivate_plugins( basename( __FILE__ ) );
			wp_die(
				sprintf( __( 'You need Awesome Support to activate this addon. Please <a href="%s" target="_blank">install Awesome Support</a> before continuing.', 'as-email-support' ), esc_url( 'https://getawesomesupport.com' ) )
			);
		}

		/* Register cron task */
		if ( !ases_cron_get_next_event() ) {
			ases_cron_schedule_task();
		}

	}

	/**
	 * Add a link to the settings page.
	 *
	 * @since  0.1.2
	 *
	 * @param  array $links Plugin links
	 *
	 * @return array        Links with the settings
	 */
	public static function settings_page_link( $links ) {

		$link    = wpas_get_settings_page_url( 'email_support' );
		$links[] = "<a href='$link'>" . __( 'Settings', 'wpas' ) . "</a>";

		return $links;

	}

	/**
	 * Register the unassigned post type.
	 *
	 * @since  0.1.0
	 * @param  array  $post_types Registered plugin post types
	 * @return array              Plugin post types
	 */
	public function add_plugin_page( $post_types ) {
		array_push( $post_types, 'wpas_unassigned_mail' );
		return $post_types;
	}

	public function load_script() {
		wp_enqueue_style( 'wpas-email', WPAS_MAIL_URL . 'assets/css/email-support.css', null, WPAS_MAIL_VERSION, 'all' );

		$use_heartbeat = null;
		if( '1' === wpas_get_option('email_polling_mode', '0')) {
			$use_heartbeat = array( 'jquery', 'heartbeat' );
		}
		wp_enqueue_script( 'wpas-email', WPAS_MAIL_URL . 'assets/js/email-support.js', $use_heartbeat, WPAS_MAIL_VERSION, true );

		wp_localize_script( 'wpas-email', 'wpas_mails', array( 'checking_mails' => __( 'Awesome Support is checking your inbox for new e-mails...', 'as-email-support' ), 'testing' => __( 'Testing...', 'as-email-support' ) ) );
	}

	public function register_log_handle( $handles ) {
		array_push( $handles, 'emails' );
		return $handles;
	}

	/**
	 * Add the ticket ID to the e-mails subject.
	 *
	 * We need to add the ticket ID in the e-mail subject line in order to be able
	 * to identify the ticket an e-mail reply is related to.
	 *
	 * @since  3.0.2
	 *
	 * @param  string $subject The e-mail subject
	 *
	 * @return string           The subject with ticket ID template tag added
	 */
	public function add_ticket_id( $subject ) {
		return $subject . ' (#{ticket_id}#)';
	}

	/**
	 * Add a delimiter to the message.
	 *
	 * We need to add a delimiter at the top of the message in order to be able
	 * to easily filter the old replies out of the e-mail reply.
	 *
	 * @since 0.1.0
	 *
	 * @param string $body E-mail body
	 *
	 * @return string The body delimiter
	 */
	public function add_delimiter( $body ) {

		$delimiter = ases_get_message_delimiter( true );

		return $delimiter . $body;
	}

	/**
	 * Register custom post type for unknown / unassigned emails
	 *
	 * This post type is used to store the unknown
	 * e-mails. A manual intervention will be required
	 * to attribute those messages to the appropriate ticket.
	 *
	 * Action Hook: Init
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function unknown_post_type() {

		$labels = array(
			'name'               => _x( 'Unassigned Messages', 'post type general name', 'as-email-support' ),
			'singular_name'      => _x( 'Unassigned Message', 'post type singular name', 'as-email-support' ),
			'menu_name'          => _x( 'Unassigned Messages', 'admin menu', 'as-email-support' ),
			'name_admin_bar'     => _x( 'Unassigned Message', 'add new on admin bar', 'as-email-support' ),
			'add_new'            => _x( 'Add New', 'unassigned message', 'as-email-support' ),
			'add_new_item'       => __( 'Add New Unassigned Message', 'as-email-support' ),
			'new_item'           => __( 'New Unassigned Message', 'as-email-support' ),
			'edit_item'          => __( 'Edit Message', 'as-email-support' ),
			'view_item'          => __( 'View Message', 'as-email-support' ),
			'all_items'          => __( 'All Unassigned Messages', 'as-email-support' ),
			'search_items'       => __( 'Search Messages', 'as-email-support' ),
			'parent_item_colon'  => __( 'Parent Message:', 'as-email-support' ),
			'not_found'          => __( 'No messages found.', 'as-email-support' ),
			'not_found_in_trash' => __( 'No messages found in Trash.', 'as-email-support' )
		);

		/* Post type capabilities */
		$cap = apply_filters( 'wpas_unassigned_mail_type_cap', array(
			'create_posts'           => false,
			'read'                   => 'edit_ticket',
			'read_post'              => 'edit_ticket',
			'read_private_posts'     => 'edit_private_ticket',
			'edit_post'              => 'delete_ticket',
			'edit_posts'             => 'delete_ticket',
			'edit_others_posts'      => 'view_unassigned_tickets',
			'edit_private_posts'     => 'nobody_can',
			'edit_published_posts'   => 'nobody_can',
			'publish_posts'          => 'nobody_can',
			'delete_post'            => 'delete_ticket',
			'delete_posts'           => 'delete_ticket',
			'delete_private_posts'   => 'delete_private_ticket',
			'delete_published_posts' => 'delete_ticket',
			'delete_others_posts'    => 'delete_other_ticket'
		) );

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'show_in_admin_bar'  => false,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'unassigned' ),
			'capabilities'       => $cap,
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'editor' )
		);

		register_post_type( 'wpas_unassigned_mail', $args );
	
	}
	
	/**
	 * Register custom post type for additional mailbox configurations
	 *
	 * This post type is used to store the configurations for mailboxes
	 *
	 * Action Hook: Init
	 *
	 * @since  5.1.0
	 * @return void
	 */
	public function mailboxes_cpt() {

		$labels = array(
			'name'               => _x( 'AS Mailbox', 'post type general name', 'as-email-support' ),
			'singular_name'      => _x( 'AS Mailbox', 'post type singular name', 'as-email-support' ),
			'menu_name'          => _x( 'AS Mailboxes', 'admin menu', 'as-email-support' ),
			'name_admin_bar'     => _x( 'AS Mailbox', 'add new on admin bar', 'as-email-support' ),
			'add_new'            => _x( 'New AS Mailbox Configuration', 'as mailbox', 'as-email-support' ),
			'add_new_item'       => __( 'Add New Mailbox Configuration', 'as-email-support' ),
			'new_item'           => __( 'New Mailbox Configuration', 'as-email-support' ),
			'edit_item'          => __( 'Edit Mailbox Configuration', 'as-email-support' ),
			'view_item'          => __( 'View Mailbox Configuration', 'as-email-support' ),
			'all_items'          => __( 'All Mailboxes', 'as-email-support' ),
			'search_items'       => __( 'Search Mailbox Configurations', 'as-email-support' ),
			'parent_item_colon'  => __( 'Parent Mailbox:', 'as-email-support' ),
			'not_found'          => __( 'No mailbox found.', 'as-email-support' ),
			'not_found_in_trash' => __( 'No mailbox found in Trash.', 'as-email-support' )
		);

		/* Post type capabilities */
		$cap = apply_filters( 'wpas_as_mailbox_cap', array(
			'create_posts'           => true,
			'read'                   => 'edit_ticket',
			'read_post'              => 'edit_ticket',
			'read_private_posts'     => 'edit_private_ticket',
			'edit_post'              => 'delete_ticket',
			'edit_posts'             => 'delete_ticket',
			'edit_others_posts'      => 'administer_awesome_support',
			'edit_private_posts'     => 'administer_awesome_support',
			'edit_published_posts'   => 'administer_awesome_support',
			'publish_posts'          => 'administer_awesome_support',
			'delete_post'            => 'delete_ticket',
			'delete_posts'           => 'delete_ticket',
			'delete_private_posts'   => 'delete_private_ticket',
			'delete_published_posts' => 'delete_ticket',
			'delete_others_posts'    => 'delete_other_ticket'
		) );

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'show_in_admin_bar'  => false,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'asmailbox' ),
			'capabilities'       => $cap,
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array('title' )
		);

		register_post_type( 'wpas_mailbox_config', $args );
	
	}

	/**
	 * Register custom post type for email rules
	 *
	 * This post type is used to store the email rules that govern which
	 * emails should just be dropped based on its contents.
	 * This will help to address things like spam or out of office replies.
	 *
	 * Action Hook: Init
	 *
	 * @since  5.1.0
	 * @return void
	 */
	public function inbox_rules_cpt() {

		$labels = array(
			'name'               => _x( 'Inbox Rule', 'post type general name', 'as-email-support' ),
			'singular_name'      => _x( 'Inbox Rule', 'post type singular name', 'as-email-support' ),
			'menu_name'          => _x( 'Inbox Rules', 'admin menu', 'as-email-support' ),
			'name_admin_bar'     => _x( 'Inbox Rule', 'add new on admin bar', 'as-email-support' ),
			'add_new'            => _x( 'New Inbox Rule', 'as mailbox', 'as-email-support' ),
			'add_new_item'       => __( 'Add New Inbox Rule', 'as-email-support' ),
			'new_item'           => __( 'New Inbox Rule', 'as-email-support' ),
			'edit_item'          => __( 'Edit Inbox Rule', 'as-email-support' ),
			'view_item'          => __( 'View Inbox Rule', 'as-email-support' ),
			'all_items'          => __( 'All Inbox Rules', 'as-email-support' ),
			'search_items'       => __( 'Search Inbox Rules', 'as-email-support' ),
			'parent_item_colon'  => __( 'Parent Inbox Rule:', 'as-email-support' ),
			'not_found'          => __( 'No Inbox Rule Found.', 'as-email-support' ),
			'not_found_in_trash' => __( 'No inbox rule found in Trash.', 'as-email-support' )
		);

		/* Post type capabilities */
		$cap = apply_filters( 'wpas_as_inbox_rules_cap', array(
			'create_posts'           => true,
			'read'                   => 'edit_ticket',
			'read_post'              => 'edit_ticket',
			'read_private_posts'     => 'edit_private_ticket',
			'edit_post'              => 'delete_ticket',
			'edit_posts'             => 'delete_ticket',
			'edit_others_posts'      => 'administer_awesome_support',
			'edit_private_posts'     => 'administer_awesome_support',
			'edit_published_posts'   => 'administer_awesome_support',
			'publish_posts'          => 'administer_awesome_support',
			'delete_post'            => 'delete_ticket',
			'delete_posts'           => 'delete_ticket',
			'delete_private_posts'   => 'delete_private_ticket',
			'delete_published_posts' => 'delete_ticket',
			'delete_others_posts'    => 'delete_other_ticket'
		) );

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'show_in_admin_bar'  => false,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'asinboxrule' ),
			'capabilities'       => $cap,
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array('title' )
		);

		register_post_type( 'wpas_inbox_rules', $args );
	
	}	
	
	/**
	 * Turn off comments in custom post types
	 *
	 * @since  5.0.0
	 * @return void
	 */	
	public function turn_off_comments() {
		remove_post_type_support( 'wpas_mailbox_config', 'comments' );
		remove_post_type_support( 'wpas_unassigned_mail', 'comments' );
		remove_post_type_support( 'wpas_inbox_rules', 'comments' );
	}
	
	/**
	 * Register all submenu items.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function register_submenu_items() {		
		add_submenu_page( 'edit.php?post_type=ticket', __( 'Inbox Configurations', 'as-email-support' ), __( 'Inbox Configurations', 'as-email-support' ), 'administer_awesome_support', 'edit.php?post_type=wpas_mailbox_config' );
		add_submenu_page( 'edit.php?post_type=ticket', __( 'Inbox Rules', 'as-email-support' ), __( 'Inbox Rules', 'as-email-support' ), 'administer_awesome_support', 'edit.php?post_type=wpas_inbox_rules' );
		add_submenu_page( 'edit.php?post_type=ticket', __( 'Unassigned Messages', 'as-email-support' ), __( 'Unassigned Emails', 'as-email-support' ), 'view_unassigned_tickets', 'edit.php?post_type=wpas_unassigned_mail' );
	}

	/**
	 * Add ticket count in admin menu item.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function unknown_count() {

		global $submenu;

		$count = wpas_mail_count_messages();

		if ( 0 === $count ) {
			return;
		}

		if ( isset( $submenu['edit.php?post_type=ticket'] ) && is_array( $submenu['edit.php?post_type=ticket'] ) ) {
			foreach ( $submenu['edit.php?post_type=ticket'] as $key => $value ) {
				if ( $value[2] == 'edit.php?post_type=wpas_unassigned_mail' ) {
					$submenu['edit.php?post_type=ticket'][ $key ][0] .= ' <span class="awaiting-mod count-' . $count . '"><span class="pending-count">' . $count . '</span></span>';
				}
			}
		}

	}

	
	/**
	 * Create the set of columns that will be shown on the multi-mailbox CPT list screen.
	 * 
	 * Filter Hook: manage_edit-wpas_mailbox_config_column
	 *
	 * params $columns 	array 	An array that is returned to WordPress containing the list of columns and the text to display at the top.
	 *							Note that the actual column values are not returned here. That's for a different action hook!
	 *
	 */
	public function show_columns_on_mailboxes_cpt( $columns ) {

		$date = $columns['date'];
		unset( $columns['date'] );

		$columns['server'] 		= __( 'Server', 'as-email-support' );
		$columns['protocol'] 	= __( 'Protocol', 'as-email-support' );
		$columns['username'] 	= __( 'User Name', 'as-email-support' );
		$columns['port'] 		= __( 'Port', 'as-email-support' );
		$columns['secureflag'] 	= __( 'Secure?', 'as-email-support' );
		$columns['activeflag'] 	= __( 'Active?', 'as-email-support' );

		return $columns;
	}

	/**
	 * Output the data for a single column that will be shown on the multi-mailbox CPT list screen.
	 * 
	 * Filter Hook: manage_wpas_mailbox_config_custom_column
	 *
	 * params $column 	string 	The name of the column to display data for.
	 *		  $post_id	integer The id of the post being rendered in the list
	 *
	 */
	public function show_single_column_on_mailboxes_cpt( $column, $post_id ) {

		switch ($column ) {
			
			case 'server':
			
				$email_server = esc_html( get_post_meta ($post_id, 'wpas_multimailbox_email_server', true) );
				echo $email_server ;
				break;

			case 'protocol':
			
				$protocol = esc_html( get_post_meta ($post_id, 'wpas_multimailbox_protocol', true) );
				echo $protocol ;
				break;
				
			case 'username':
			
				$username = esc_html( get_post_meta ($post_id, 'wpas_multimailbox_username', true) );
				if ( empty($username) ) {
					echo __('***not configured!***');
				} else {
					echo $username ;
				}
				break;
				
			case 'port':
			
				$port = esc_html( get_post_meta ($post_id, 'wpas_multimailbox_port', true) );
				echo $port ;
				break;
				
			case 'secureflag':
			
				$secureflag = esc_html( get_post_meta ($post_id, 'wpas_multimailbox_secureportflag', true) );
				
				if ( '1' === (string) $secureflag ) {
					echo __( 'Y' ) ;
				} else {
					echo strtoupper( (string) $secureflag );
				}
				
				break;
				
			case 'activeflag':
			
				$activeflag = esc_html( get_post_meta ($post_id, 'wpas_multimailbox_active', true) );
				
				if ( '1' === (string) $activeflag ) {
					echo __( 'Y' ) ;
				} else {
					echo '';
				}
				
				break;
				
			default:
				echo __('no-data');
				break;
		}

	}

	/**
	 * Create the set of columns that will be shown on the Inbox Rules CPT list screen.
	 * 
	 * Filter Hook: manage_edit-wpas_inbox_rules_columns
	 *
	 * params $columns 	array 	An array that is returned to WordPress containing the list of columns and the text to display at the top.
	 *							Note that the actual column values are not returned here. That's for a different action hook!
	 *
	 */
	public function show_columns_on_inbox_rules_cpt( $columns ) {

		$date = $columns['date'];
		unset( $columns['date'] );

		$columns['type'] 	= __( 'Rule Type', 'as-email-support' );
		$columns['area' ] 	= __( 'Rule Area', 'as-email-support' );
		$columns['active']	= __( 'Active?', 'as-email-support' );
		$columns['action'] 	= __( 'Action', 'as-email-support' );

		return $columns;
	}
	
	/**
	 * Output the data for a single column that will be shown on the inbox rules CPT list screen.
	 * 
	 * Filter Hook: manage_wpas_inbox_rules_custom_column
	 *
	 * params $column 	string 	The name of the column to display data for.
	 *		  $post_id	integer The id of the post being rendered in the list
	 *
	 */
	public function show_single_column_on_inbox_rules_cpt( $column, $post_id ) {

		switch ($column ) {
			
			case 'type':
				
				$rule_type = esc_html( get_post_meta ($post_id, 'wpas_inboxrules_rule_type', true) );
				echo $rule_type ;
				
				break;
				
			case 'area':

				$rule_area = esc_html( get_post_meta ($post_id, 'wpas_inboxrules_rule_area', true) );
				echo $rule_area ;
				
				break;	
				
			case 'active':
				
				$rule_active = esc_html( get_post_meta ($post_id, 'wpas_inboxrules_rule_active', true) );
				
				if ( '1' === (string) $rule_active ) {
					echo __( 'Y' ) ;
				} else {
					echo '';
				}
				
				break;
				
			case 'action':

				$rule_action = esc_html( get_post_meta ($post_id, 'wpas_inboxrules_rule_action', true) );
				echo $rule_action ;
				
				break;		
				
			default:
				echo __('no-data');
				break;
		
		}
	}
	
	/**
	 * Register the metaboxes for the ticket screen
	 *
	 * The function below registers all the metaboxes used
	 * in the ticket edit screen.
	 *
	 * @since 3.0.0
	 */
	public function metaboxes() {

		/* Remove the publishing metabox */
		remove_meta_box( 'submitdiv', 'wpas_unassigned_mail', 'side' );

		/**
		 * Register the metaboxes.
		 */

		/* Details */
		add_meta_box(
			'wpas-mail-details',
			__( 'Details', 'as-email-support' ),
			array( $this, 'metabox_callback' ),
			'wpas_unassigned_mail',
			'normal',
			'high',
			array( 'template' => 'details' )
		);

		// Show the message attachments.
		add_meta_box(
			'wpas-mail-attachments',
			esc_html__( 'Attachments', 'as-email-support' ),
			array( $this, 'metabox_callback' ),
			'wpas_unassigned_mail',
			'normal',
			'high',
			array( 'template' => 'attachments' )
		);

		/* Details */
		add_meta_box(
			'wpas-mail-actions',
			__( 'Actions', 'as-email-support' ),
			array( $this, 'metabox_callback' ),
			'wpas_unassigned_mail',
			'side',
			'high',
			array( 'template' => 'actions' )
		);

	}

	/**
	 * Metabox callback function.
	 *
	 * The below function is used to call the metaboxes content.
	 * A template name is given to the function. If the template
	 * does exist, the metabox is loaded. If not, nothing happens.
	 *
	 * @param  integer $post Post ID
	 * @param  array   $args Additional arguments passed to the function
	 *
	 * @return mixed             False if template doesn't exist, null otherwise
	 * @since  0.1.0
	 */
	public function metabox_callback( $post, $args ) {

		if ( ! is_array( $args ) || ! isset( $args['args']['template'] ) ) {
			_e( 'An error occured while registering this metabox. Please contact support.', 'as-email-support' );
		}

		$template = $args['args']['template'];

		if ( ! file_exists( WPAS_MAIL_PATH . "includes/metaboxes/$template.php" ) ) {
			_e( 'An error occured while loading this metabox. Please contact support.', 'as-email-support' );
		}

		/* Include the metabox content */
		include_once( WPAS_MAIL_PATH . "includes/metaboxes/$template.php" );

	}

	
	/**
	 * Register the metaboxes for use in the mailboxes custom post type
	 *
	 * Action Hook: admin_init
	 *
	 * @since 5.0.0
	 */	
	public function mailboxes_cpt_metaboxes() {
		
		add_meta_box(
			'wpas-inbox-metabox-01',
			__( 'Inbox Configuration (IMAP/POP3)', 'as-email-support' ),
			array( $this, 'metabox_callback' ),
			'wpas_mailbox_config',
			'normal',
			'high',
			array( 'template' => 'multi-mailbox-config' )
		);		
		
	}
	
	/**
	 * Register the metaboxes for use in the inbox rules custom post type
	 *
	 * Action Hook: admin_init
	 *
	 * @since 5.0.0
	 */	
	public function inbox_rules_cpt_metaboxes() {
		
		add_meta_box(
			'wpas-inbox-rules-01',
			__( 'Inbox Rules', 'as-email-support' ),
			array( $this, 'metabox_callback' ),
			'wpas_inbox_rules',
			'normal',
			'high',
			array( 'template' => 'inbox-rules-config' )
		);		
		
	}	
	
	/**
	 * Save the configuration data entered into a mailbox CPT.
	 *
	 * This function is called while the CPT is being created.
	 * The call is done via an action hook.
	 *
	 * Action Hook: save_post_wpas_mailbox_config
	 *
	 * @param  string|integer $post_id ID of the mailbox CPT
	 * @param array           $post    Post data
	 *
	 * @return void
	 */
	public function mailboxes_cpt_save($post_id, $post) {
		
		/**
		 * First of all we remove the action in order to avoid infinite loops.
		 */
		remove_action( 'save_post_wpas_mailbox_config', array( $this, 'mailboxes_cpt_save' ), 10 );

		// Empty data?  Then return
		if ( empty( $post_id ) ) {
			return;
		}

		/* If no data is passed we try to get it from the POST */
		if ( empty( $post ) ) {
			global $_POST ;
			$post = $_POST;
		}

		// Get data from $post
		$email_server 	= filter_input(INPUT_POST, 'html_multimailbox_email_server', FILTER_SANITIZE_STRING);
		$protocol		= filter_input(INPUT_POST, 'html_multimailbox_protocol', FILTER_SANITIZE_STRING);
		$username		= filter_input(INPUT_POST, 'html_multimailbox_user_name', FILTER_SANITIZE_STRING);
		$password		= filter_input(INPUT_POST, 'html_multimailbox_password', FILTER_SANITIZE_STRING);
		$port			= filter_input(INPUT_POST, 'html_multimailbox_port', FILTER_SANITIZE_NUMBER_INT);
		$secureportflag	= filter_input(INPUT_POST, 'html_multimailbox_secureportflag', FILTER_SANITIZE_STRING);
		$timeout		= filter_input(INPUT_POST, 'html_multimailbox_timeout', FILTER_SANITIZE_NUMBER_INT);
		$activeflag		= filter_input(INPUT_POST, 'html_multimailbox_activeflag', FILTER_SANITIZE_NUMBER_INT);
		
		$defaultassignee	= filter_input(INPUT_POST, 'html_multimailbox_default_assignee', FILTER_SANITIZE_NUMBER_INT);
		$defaultdept		= filter_input(INPUT_POST, 'html_multimailbox_default_dept', FILTER_SANITIZE_NUMBER_INT);
		$defaultproduct		= filter_input(INPUT_POST, 'html_multimailbox_default_product', FILTER_SANITIZE_NUMBER_INT);
		$defaultpriority	= filter_input(INPUT_POST, 'html_multimailbox_default_priority', FILTER_SANITIZE_NUMBER_INT);
		$defaultchannel		= filter_input(INPUT_POST, 'html_multimailbox_default_channel', FILTER_SANITIZE_NUMBER_INT);
		
		$defaultstatus		= filter_input(INPUT_POST, 'html_multimailbox_default_status', FILTER_SANITIZE_STRING);
		$defaultpublicflag	= filter_input(INPUT_POST, 'html_multimailbox_default_public_flag', FILTER_SANITIZE_STRING);
		
		$defaultaddlpartyemail1	= filter_input(INPUT_POST, 'html_multimailbox_default_addl_party_email_1', FILTER_SANITIZE_STRING);
		$defaultaddlpartyemail2	= filter_input(INPUT_POST, 'html_multimailbox_default_addl_party_email_2', FILTER_SANITIZE_STRING);
		
		$defaultsecondaryassignee	= filter_input(INPUT_POST, 'html_multimailbox_default_secondary_assignee', FILTER_SANITIZE_NUMBER_INT);
		$defaulttertiaryassignee	= filter_input(INPUT_POST, 'html_multimailbox_default_tertiary_assignee', FILTER_SANITIZE_NUMBER_INT);
		

		//Now write the data...
		update_post_meta($post_id, 'wpas_multimailbox_email_server', $email_server);
		update_post_meta($post_id, 'wpas_multimailbox_protocol', $protocol);
		update_post_meta($post_id, 'wpas_multimailbox_username', $username);
		update_post_meta($post_id, 'wpas_multimailbox_password', $password);
		update_post_meta($post_id, 'wpas_multimailbox_port', $port);
		update_post_meta($post_id, 'wpas_multimailbox_secureportflag', $secureportflag);
		update_post_meta($post_id, 'wpas_multimailbox_timeout', $timeout);
		update_post_meta($post_id, 'wpas_multimailbox_active', $activeflag);
		
		update_post_meta($post_id, 'wpas_multimailbox_defaultassignee', $defaultassignee);
		update_post_meta($post_id, 'wpas_multimailbox_defaultdept', $defaultdept);
		update_post_meta($post_id, 'wpas_multimailbox_defaultproduct', $defaultproduct);
		update_post_meta($post_id, 'wpas_multimailbox_defaultpriority', $defaultpriority);
		update_post_meta($post_id, 'wpas_multimailbox_defaultchannel', $defaultchannel);
		
		update_post_meta($post_id, 'wpas_multimailbox_defaultstatus', $defaultstatus);
		update_post_meta($post_id, 'wpas_multimailbox_defaultpublicflag', $defaultpublicflag);
		
		update_post_meta($post_id, 'wpas_multimailbox_defaultaddlpartyemail1', $defaultaddlpartyemail1);
		update_post_meta($post_id, 'wpas_multimailbox_defaultaddlpartyemail2', $defaultaddlpartyemail2);
		
		update_post_meta($post_id, 'wpas_multimailbox_defaultsecondaryassignee', $defaultsecondaryassignee);
		update_post_meta($post_id, 'wpas_multimailbox_defaulttertiaryassignee', $defaulttertiaryassignee);
		
	}
	
	/**
	 * Check if the current user can attach a file.
	 *
	 * @since  3.0.0
	 * @return boolean True if the user has the capability, false otherwise
	 */
	public function can_attach_files( $can_attach ) {

		if ( false === boolval( wpas_get_option( 'enable_attachments' ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Save the configuration data entered into an inbox rule CPT screen.
	 *
	 * This function is called while the CPT is being created.
	 * The call is done via an action hook.
	 *
	 * Action Hook: save_post_wpas_mailbox_config
	 *
	 * @param  string|integer $post_id ID of the mailbox CPT
	 * @param array           $post    Post data
	 *
	 * @return void
	 */
	public function inbox_rules_cpt_save($post_id, $post) {

		/**
		 * First of all we remove the action in order to avoid infinite loops.
		 */
		remove_action( 'save_post_wpas_inbox_rules', array( $this, 'inbox_rules_cpt_save' ), 10 );	
		
		// Empty data?  Then return
		if ( empty( $post_id ) ) {
			return;
		}

		/* If no data is passed we try to get it from the POST */
		if ( empty( $post ) ) {
			global $_POST ;
			$post = $_POST;
		}

		// Get data from $post
		$rule_type 		= filter_input(INPUT_POST, 'html_inboxrules_rule_type', FILTER_SANITIZE_STRING);
		$rule_contents 	= filter_input(INPUT_POST, 'html_inboxrules_rule_contents', FILTER_SANITIZE_STRING);  //Not the best sanitization since this could also be a regex expression but its better than nothing...
		$rule_area 		= filter_input(INPUT_POST, 'html_inboxrules_rule_area', FILTER_SANITIZE_STRING);
		$rule_active 	= filter_input(INPUT_POST, 'html_inboxrules_rule_active', FILTER_SANITIZE_STRING);
		$rule_action 	= filter_input(INPUT_POST, 'html_inboxrules_rule_action', FILTER_SANITIZE_STRING);
		$rule_notes 	= filter_input(INPUT_POST, 'html_inboxrules_rule_notes', FILTER_SANITIZE_STRING);
		
		$new_assignee			= filter_input(INPUT_POST, 'html_inboxrules_rule_new_assignee', FILTER_SANITIZE_NUMBER_INT);
		$new_dept				= filter_input(INPUT_POST, 'html_inboxrules_rule_new_dept', FILTER_SANITIZE_NUMBER_INT);
		$new_product			= filter_input(INPUT_POST, 'html_inboxrules_rule_new_product', FILTER_SANITIZE_NUMBER_INT);
		$new_priority			= filter_input(INPUT_POST, 'html_inboxrules_rule_new_priority', FILTER_SANITIZE_NUMBER_INT);
		$new_channel			= filter_input(INPUT_POST, 'html_inboxrules_rule_new_channel', FILTER_SANITIZE_NUMBER_INT);
		
		$new_status				= filter_input(INPUT_POST, 'html_inboxrules_rule_new_status', FILTER_SANITIZE_STRING);
		$new_public_flag		= filter_input(INPUT_POST, 'html_inboxrules_rule_new_public_flag', FILTER_SANITIZE_STRING);
		
		$new_addlparty_email1	= filter_input(INPUT_POST, 'html_inboxrules_rule_new_addlparty_email1', FILTER_SANITIZE_STRING);
		$new_addlparty_email2	= filter_input(INPUT_POST, 'html_inboxrules_rule_new_addlparty_email2', FILTER_SANITIZE_STRING);
		
		$new_secondary_assignee	= filter_input(INPUT_POST, 'html_inboxrules_rule_new_secondary_assignee', FILTER_SANITIZE_NUMBER_INT);
		$new_tertiary_assignee	= filter_input(INPUT_POST, 'html_inboxrules_rule_new_tertiary_assignee', FILTER_SANITIZE_NUMBER_INT);

		//Write the data...
		update_post_meta($post_id, 'wpas_inboxrules_rule_type', $rule_type);
		update_post_meta($post_id, 'wpas_inboxrules_rule_contents', wp_slash( $rule_contents ) );
		update_post_meta($post_id, 'wpas_inboxrules_rule_area', $rule_area);
		update_post_meta($post_id, 'wpas_inboxrules_rule_active', $rule_active);
		update_post_meta($post_id, 'wpas_inboxrules_rule_action', $rule_action);
		update_post_meta($post_id, 'wpas_inboxrules_rule_notes', $rule_notes);
		
		update_post_meta($post_id, 'wpas_inboxrules_rule_new_assignee', $new_assignee);
		update_post_meta($post_id, 'wpas_inboxrules_rule_new_dept', $new_dept);
		update_post_meta($post_id, 'wpas_inboxrules_rule_new_product', $new_product);
		update_post_meta($post_id, 'wpas_inboxrules_rule_new_priority', $new_priority);
		update_post_meta($post_id, 'wpas_inboxrules_rule_new_channel', $new_channel);
		
		update_post_meta($post_id, 'wpas_inboxrules_rule_new_status', $new_status);
		update_post_meta($post_id, 'wpas_inboxrules_rule_new_public_flag', $new_public_flag);
		
		update_post_meta($post_id, 'wpas_inboxrules_rule_new_addlparty_email1', $new_addlparty_email1);
		update_post_meta($post_id, 'wpas_inboxrules_rule_new_addlparty_email2', $new_addlparty_email2);
		
		update_post_meta($post_id, 'wpas_inboxrules_rule_new_secondary_assignee', $new_secondary_assignee);
		update_post_meta($post_id, 'wpas_inboxrules_rule_new_tertiary_assignee', $new_tertiary_assignee);
	}
	
}
