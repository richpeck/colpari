<?php

/**
 * Gravity Forms Mapping.
 *
 * @package   Awesome Support: Gravity Forms
 * @author    Robert W. Kramer III for Awesome Support <support@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016. Awesome Support
 *
 */
class WPAS_GF_Ticket {

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @var
	 */
	private $custom_fields;

	/**
	 * @var
	 */
	private $attachments;

	/**
	 * @var
	 */
	private $client;

	/**
	 * ID of the ticket this e-mail refers to.
	 *
	 * @var integer|boolean
	 */
	private $ticket_id;

	/**
	 * ID of the reply that's added.
	 *
	 * @var integer
	 */
	private $reply_id;


	/**
	 * Constructor method.
	 *
	 * @since    0.1.0
	 *
	 * @param $data
	 *
	 * @internal param array $form The e-mail to fetch
	 */
	public function __construct( $data, $custom_fields, $attachments ) {

		if ( ! is_array( $data ) ) {
			return false;
		}

		$this->data          = $data;
		$this->custom_fields = $custom_fields;
		$this->attachments   = $attachments;

		$this->ticket_id = isset( $this->data[ 'ticket_id' ] ) && ! empty( $this->data[ 'ticket_id' ] ) ? $this->data[ 'ticket_id' ] : false;

		// Action hooks
		add_action( 'gf_wpas_after_save_form', array($this,	'maybe_close_ticket', ), 10, 2 );  // Close ticket if custom field is mapped (demonstration of using this hook)

	}

	/**
	 * Save the form to a new ticket or ticket reply.
	 *
	 * @since  0.1.0
	 * @return array|boolean
	 */
	public function save_form() {

		// Ticket ID specified, assume ticket reply
		if ( $this->is_reply() ) {

			// Specified Ticket ID does not exist
			if ( $this->has_ticket() ) {
				$this->add_reply();
			} else {
				WPAS_GF::get_instance()->log( __FUNCTION__ . ' Ticket reply failed. Submitted ticket id (' . esc_html( $this->ticket_id ) . ') not valid. ' . ' (' . __( 'Not found.' ) . ')' );

				return false;
			}

		} // Create new ticket
		elseif ( $this->is_ticket() ) {
			
			// Important: custom fields, attachments etc will be updated in this hook!
			// Note the priority level - its set to make sure that custom fields etc are updated before anything 
			// else that might need them such as ruleset and automatic agent assignment.
			add_action( 'wpas_open_ticket_before_assigned', array( $this, 'action_open_ticket_before_assigned' ), 13, 2 );			
			
			$this->add_ticket( array(
				                   'post_content' => $this->data[ 'content' ],
				                   'post_author'  => absint( $this->client->ID ),
				                   'post_title'   => $this->data[ 'subject' ],
				                   'post_type'    => 'ticket',
			                   ), $this->get_agent_id() , 'open' );

			// Unhook the action since we no longer need it.
			remove_action( 'wpas_open_ticket_before_assigned', array( $this, 'action_open_ticket_before_assigned' ), 13 );					   
			
		} else {
			WPAS_GF::get_instance()->log( __FUNCTION__ . ' General Exception.' );

			return false;
		}

		//After save form action hook
		do_action( 'gf_wpas_after_save_form', $this->ticket_id, $this->data );

		return apply_filters( 'gf_wpas_save_form', array(
			'ticket_id' => $this->reply_id ? $this->reply_id : $this->ticket_id,
			'client'    => $this->client,
		),
		                      $this->ticket_id,
		                      $this->data
		);
	}

	/**
	 * Save all other things associated with the ticket. 
	 * It is a hook that is set in the save_form() function in this class.
	 * It is set just before calling the add_ticket function and then unset
	 * immediately afterwards.  This ensures that ticket data is added and available
	 * for any hooks that might need to use it later in the ticket adding process.
	 *
	 * Action Hook: wpas_open_ticket_before_assigned
	 *
	 * @param null|int $ticket_id the id of the ticket or post object
	 * @param array $ticket_data the post object with the ticket contents
	 */		
	public function action_open_ticket_before_assigned( $ticket_id, $ticket_data ) {

		// Set the class level ticket id.
		// We can do this because we wouldn't be in here in this hook 
		// unless the ticket had been added.
		$this->ticket_id = $ticket_id ;

		if ( false == empty( $ticket_id ) && false == empty( $ticket_data ) && false == empty( $this->data ) ) {

			if ( isset( $this->data[ 'department' ] ) ) {
				wp_set_post_terms( $this->ticket_id, absint( $this->data[ 'department' ] ), 'department', false );
			}

			if ( isset( $this->data[ 'product' ] ) ) {
				wp_set_post_terms( $this->ticket_id, absint( $this->data[ 'product' ] ), 'product', false );
			}

			$this->update_status();

			/* This section of code commented out because its no longer needed.  The call to */
			/* $this->get_agent_id is done before we get here and includes all pertinent     */
			/* checks I think so this is just redundant.  NB - 1/12/2018 */
			/*
			if ( isset( $this->data[ 'assignee' ] ) ) {
				$this->update_assignee( $this->ticket_id, $this->data[ 'assignee' ] );
			} else {
				$this->update_assignee( $this->ticket_id, $this->get_agent_id() );				
			}
			*/
			
			$this->update_custom_fields();

			//After custom fields update action hook
			do_action( 'gf_wpas_after_custom_fields_update', $this->ticket_id, $this->data );

			$this->update_attachments();

			//After attachments update action hook
			do_action( 'gf_wpas_after_attachments_update', $this->ticket_id, $this->data );
			
		}
		
	}

	/**
	 *
	 */
	public function update_attachments() {

		$attachments = array();

		// Save filename and file contents
		foreach ( $this->attachments as $attachment ) {
			$attachments[] = array(
				'filename' => $attachment[ 'filename' ],
				'data'     => file_get_contents( trailingslashit( $attachment[ 'basedir' ] ) . $attachment[ 'filename' ] ),
			);
		}

		// Make sure there is a user logged in that can_attach (AS File Uploader class)
		$current_user = wp_get_current_user();

		if ( ! ( $current_user instanceof WP_User ) || $current_user->ID === 0 ) {

			add_filter( 'authenticate', array(
				$this,
				'allow_programmatic_login',
			), 10, 3 );    // hook in earlier than other callbacks to short-circuit them

			$current_user = wp_signon( array( 'user_login' => $this->client->user_login ) );

			remove_filter( 'authenticate', array( $this, 'allow_programmatic_login' ), 10 );

			if ( is_a( $current_user, 'WP_User' ) ) {
				wp_set_current_user( $current_user->ID, $current_user->user_login );
			}

			// Process the attachments to AS uploades
			$this->process_attachments( $attachments );

			wp_logout();

		} else {

			// Process the attachments to AS uploades
			$this->process_attachments( $attachments );
		}

	}

	/**
	 * @param $attachments
	 */
	public function process_attachments( $attachments ) {

		/**
		 * Instantiate the uploader class.
		 *
		 * @var WPAS_File_Upload $uploader
		 */
		$uploader = WPAS_File_Upload::get_instance();

		//@TODO: Why does the uploader need this to be set to the ticket ID when we're passing it directly into the funtion call a couple of lines below?
		$uploader->post_id = $this->ticket_id;

		// If this is a reply attach it to the reply instead of the parent ticket 
		$post_id = isset( $this->reply_id ) ? $this->reply_id : $this->ticket_id;

		// Add the attachments one by one.
		$uploader->process_attachments( $post_id, $attachments );

	}

	/**
	 * An 'authenticate' filter callback that authenticates the user using only the username.
	 *
	 * To avoid potential security vulnerabilities, this should only be used in the context of a programmatic login,
	 * and unhooked immediately after it fires.
	 *
	 * @param WP_User $user
	 * @param string  $username
	 * @param string  $password
	 *
	 * @return bool|WP_User a WP_User object if the username matched an existing user, or false if it didn't
	 */
	public function allow_programmatic_login( $user, $username, $password ) {
		return get_user_by( 'login', $username );
	}

	/**
	 * Check if is ticket reply
	 *
	 * @return bool
	 */
	public function is_reply() {
		return false !== $this->ticket_id;
	}

	/**
	 * Check if form submission refers to an existing ticket.
	 *
	 * @since  0.0.8
	 *
	 * @return boolean True if the form refers to a ticket, false otherwise
	 */
	public function has_ticket() {

		if ( false === $this->ticket_id ) {
			WPAS_GF::get_instance()->log( __FUNCTION__ . ' Ticket ID not submitted' );

			return false;
		}

		$ticket = get_post( $this->ticket_id );

		return 'ticket' === $ticket->post_type;

	}

	/**
	 * Insert the reply.
	 *
	 * Insert the reply in database using the Tickets API.
	 * We also add a couple of extra information as post metas.
	 *
	 * @return integer
	 *
	 * @since  0.1.0
	 */
	public function add_reply() {

		$data = array(
			'post_content'   => $this->data[ 'content' ],
			'post_status'    => 'unread',
			'post_type'      => 'ticket_reply',
			'ping_status'    => 'closed',
			'comment_status' => 'closed',
			'post_parent'    => $this->ticket_id,
			'parent_id'      => $this->ticket_id,
		);

		$user_id  = $this->get_user_id();
		$reply_id = false;

		if ( $user_id ) {
			wp_set_current_user( $user_id );

			$this->email_notifications( true );

			$reply_id = wpas_add_reply( $data, $this->ticket_id, $user_id );
		}

		if ( $reply_id ) {
			$this->reply_id = $reply_id;
			WPAS_GF::get_instance()->log( __FUNCTION__ . ' Reply id ' . $this->reply_id . ' added to ticket id ' . $this->ticket_id );

		} else {
			WPAS_GF::get_instance()->log( __FUNCTION__ . ' ERROR: Failed adding reply to ' . $this->ticket_id );
			$this->reply_id = - 1;
		}

		$this->email_notifications( false );

		return $this->reply_id;

	}

	/**
	 * Attempt to identify client
	 *
	 * @return bool|false|WP_User
	 *
	 */
	public function determine_client() {

		if ( ! isset( $this->data[ 'email' ] ) || false === $this->data[ 'email' ] ) {

			if ( ! $this->ticket_id ) {

				WPAS_GF::get_instance()->log( __FUNCTION__ . ' User email not submitted in form.' );

				return false;

			} else {

				// get user from ticket
				$ticket = get_post( $this->ticket_id );

				if ( 'ticket' !== $ticket->post_type ) {
					return false;
				}

				return get_user_by( 'id', $ticket->post_author );
			}

		} else {

			return get_user_by( 'email', $this->data[ 'email' ] );
		}

	}

	/**
	 * Find the user who sent the e-mail.
	 *
	 * If there is a user with the e-mail's sender address
	 * we assume that he is the the author of the message.
	 *
	 * @since  0.1.0
	 * @return integer|boolean The user ID if a user is found in the database, false otherwise
	 */
	public function get_user_id() {

		if ( isset( $this->client ) && isset( $this->client->ID ) ) {
			WPAS_GF::get_instance()->log( __FUNCTION__ . ' Client exists: ' . $this->client->user_email );

			return $this->client->ID;
		}

		$user = $this->determine_client();

		if ( false === $user ) {

			// Create a new user profile
			$user = $this->create_user();

			// User exists now?
			if ( false === $user ) {
				return false;
			}
		}

		WPAS_GF::get_instance()->log( __FUNCTION__ . ' Found user: ' . $user->user_email );

		/* Save the user data for later use. */
		$this->client = $user;

		// Return user ID
		return $user->ID;

	}

	/**
	 * Create User
	 *
	 * @return bool|WP_User
	 */
	public function create_user() {

		if ( ! isset( $this->data[ 'email' ] ) || false === $this->data[ 'email' ] ) {
			return false;
		}

		$user_email = $this->data[ 'email' ];

		// Allowed to create new user?
		if ( ! $this->data[ 'options' ][ 'allow_create_user' ] ) {
			WPAS_GF::get_instance()->log( __FUNCTION__ . ' allow_create_user disabled.' );

			return false;
		}

		$random_password = wp_generate_password( $length = 12, $inc_std_spec_chars = false );
		$user_id         = wp_insert_user( array(
			                                   'user_email' => $user_email,
			                                   'user_login' => $user_email,
			                                   'user_pass'  => $random_password,
			                                   'role'       => 'wpas_user',
		                                   ) );
		$user            = get_user_by( 'id', $user_id );

		if ( is_wp_error( $user ) ) {
			// New user was not created ok, so log things..
			WPAS_GF::get_instance()->log( __FUNCTION__ . ' create_user() FAIL' . $user->get_error_message() );

		} else {
			// New user created ok..
			WPAS_GF::get_instance()->log( __FUNCTION__ . ' create_user() ID: ' . $user_id . ' with Email: ' . esc_html( $user_email ) );
			
			// Send standard wp notifications for new users?
			// Unfortunately, for now users will not be receiving any emails since this option isn't actually implemented in the mapping screen.
			$receive_alert = $this->get_new_user_email_option();
			if ( 'none' <> $receive_alert ) {
				wp_new_user_notification( $user_id, null, $receive_alert );
			}
		}

		return $user;
	}

	/**
	 * Returns an indicator whether or not the standard new user emails from WP should be sent out.
	 *
	 * @return string Return 'admin', 'both', 'user', 'none'.
	 */	
	public function get_new_user_email_option() {
		$new_user_email_option = 'none';
		if ( true == boolval( $this->data[ 'options' ][ 'new_user_send_wp_email_to_user' ] ) && true == boolval( $this->data[ 'options' ][ 'new_user_send_wp_email_to_admin' ] ) ) {
			$new_user_email_option = 'both';
		} else if ( true == boolval( $this->data[ 'options' ][ 'new_user_send_wp_email_to_user' ] ) ) {
			$new_user_email_option = 'user' ;
		} else if ( true == boolval( $this->data[ 'options' ][ 'new_user_send_wp_email_to_admin' ] ) ) {
			$new_user_email_option = 'admin' ;
		}
		
		return $new_user_email_option ;
	}

	/**
	 * Validate form submission data.
	 *
	 * @return bool|int     Return user id or false
	 */
	public function is_ticket() {

		if ( $this->data[ 'subject' ] && $this->data[ 'content' ] && ! $this->ticket_id ) {
			return $this->get_user_id();
		}

		return false;
	}

	/**
	 * Add a new ticket
	 *
	 * @param $args
	 * @param $agent_id
	 * @param $status
	 *
	 * @return mixed
	 */
	public function add_ticket( $args, $agent_id = 1, $status = 'open' ) {

		$defaults = array(
			'post_content'   => '',
			'post_title'     => '',
			'post_status'    => 'queued',
			'post_type'      => 'ticket',
			'post_author'    => '',
			'ping_status'    => 'closed',
			'post_parent'    => 0,
			'comment_status' => 'closed',
		);

		$args = apply_filters( 'wpas_mail_insert_post_args', wp_parse_args( $args, $defaults ) );

		if ( empty( $args[ 'post_content' ] ) ) {
			WPAS_GF::get_instance()->log( __FUNCTION__ . ' ERROR: No post content.' );

			return false;
		}

		if ( empty( $args[ 'post_author' ] ) || false === get_user_by( 'id', $args[ 'post_author' ] ) ) {
			WPAS_GF::get_instance()->log( __FUNCTION__ . ' ERROR: No post author.' );

			return false;
		}

		$this->email_notifications( true );
		$this->ticket_id = wpas_insert_ticket( $args, false, $agent_id, 'gravity-forms-add-on' );

		if ( false !== $this->ticket_id ) {

			do_action( 'gf_wpas_after_ticket_insert_success', $this->ticket_id, $this->data, $args );

		} else {

			// No ticket id so ticket creation failed.  Allow a hook to handle this 
			do_action( 'gf_wpas_after_ticket_insert_failed', $this->data, $args );
		}

		WPAS_GF::get_instance()->log( __FUNCTION__ . ': ' . ( $this->ticket_id ? $this->ticket_id : 'ERROR' ) );

		$this->email_notifications( false );

		return $this->ticket_id;

	}

	/**
	 * Assign agent to ticket
	 *
	 * @param $ticket_id
	 * @param $agent_id
	 *
	 */
	public function update_assignee( $ticket_id, $agent_id ) {

		$post = get_post( $ticket_id );

		if ( ! $post ) {
			return;
		}

		if ( 'ticket' === $post->post_type || 'ticket_reply' === $post->post_type ) {

			$id = $post->post_type === 'ticket' ? $ticket_id : $post->parent;

			/* Assign an agent to the ticket */
			wpas_assign_ticket( $id, apply_filters( 'wpas_new_ticket_agent_id', $agent_id, $id, $agent_id ), false );

		}
	}

	/**
	 * Returns an available agent ID
	 *
	 * @return mixed|void
	 */
	public function get_agent_id() {

		if ( isset( $this->data[ 'assignee' ] ) && $this->data[ 'assignee' ] ) {
			
			$agent_id = $this->data[ 'assignee' ];
			
		} else {
			
			$agent_id = wpas_find_agent();

			if ( ! $agent_id ) {

				/* Use the default assignee as the message author */
				$agent_id = wpas_get_option( 'assignee_default' );

				/* In case there is no default assignee set (wtf) we try to use user 1 who should be the admin */
				if ( empty( $agent_id ) ) {
					$agent_id = 1;
				}

			}
		}

		return apply_filters( 'wpas_gf_agent_id', $agent_id );

	}

	/**
	 * Update ticket State/Status
	 *
	 */
	public function update_status() {
		
		if ( isset( $this->data[ 'status' ] ) && ! empty( $this->data[ 'status' ] ) ) {

			$current_status = get_post_meta( $this->ticket_id, '_wpas_status', true );
			$new_status     = $this->data[ 'status' ];
			
			$updated        = update_post_meta( $this->ticket_id, '_wpas_status', $new_status );

			if ( 0 !== intval( $updated ) ) {
				WPAS_GF::get_instance()->log( __FUNCTION__ . sprintf( __( ' Ticket status current is %s', 'wpas-gf' ), $current_status ) );
				WPAS_GF::get_instance()->log( __FUNCTION__ . sprintf( __( ' Ticket status changed to %s', 'wpas-gf' ), $new_status ) );
			} else {
				WPAS_GF::get_instance()->log( __FUNCTION__ . sprintf( __( ' Unable to update ticket status (%s)', 'wpas-gf' ), $updated ) );
			}
		}

		if ( isset( $this->data[ 'ticket_state' ] ) && ! empty( $this->data[ 'ticket_state' ] ) ) {

			$current_state = wpas_get_ticket_status( $this->ticket_id );
			$new_state     = $this->data[ 'ticket_state' ];
			$updated       = wpas_update_ticket_status( $this->ticket_id, $new_state );

			if ( 0 !== intval( $updated ) ) {
				WPAS_GF::get_instance()->log( __FUNCTION__ . sprintf( __( ' Ticket state current is %s', 'wpas-gf' ), $current_state ) );
				WPAS_GF::get_instance()->log( __FUNCTION__ . sprintf( __( ' Ticket state changed to %s', 'wpas-gf' ), $new_state ) );
			} else {
				WPAS_GF::get_instance()->log( __FUNCTION__ . sprintf( __( ' Unable to update ticket state (%s)', 'wpas-gf' ), $updated ) );
			}
		}
	}

	/**
	 * Update or Add custom fields
	 */
	public function update_custom_fields() {

		foreach ( $this->custom_fields as $key => $custom_field ) {

			if ( 'taxonomy' === $this->custom_fields[ $key ][ 'args' ][ 'field_type' ] ) {
				
				if ( isset( $this->data[ $key ] ) ) {

					wp_set_post_terms( $this->ticket_id,
					                   absint( $this->data[ $key ] ),
					                   $key,
					                   true
					);
				}

			} else {

				$value = get_post_meta( $this->ticket_id, '_wpas_' . $custom_field[ 'name' ], true );

				if ( ! empty( $value ) ) {
					if ( isset( $this->data[ $key ] ) ) {
						update_post_meta( $this->ticket_id, '_wpas_' . $custom_field[ 'name' ], $this->data[ $key ] );
					}
				} else if ( isset( $this->data[ $key ] ) ) {
					add_post_meta( $this->ticket_id, '_wpas_' . $custom_field[ 'name' ], $this->data[ $key ] );
				}

			}
		}

	}

	/**
	 * Get the ID of the added reply.
	 *
	 * @since  0.1.0
	 * @return integer|false Reply ID if available, false otherwise
	 */
	public function get_reply_id() {
		return isset( $this->reply_id ) && 0 !== $this->reply_id ? $this->reply_id : false;
	}

	/**
	 * @param bool $enable
	 */
	public function email_notifications( $enable = false ) {

		if ( $enable ) {
			add_filter( 'wpas_email_notifications_cases', array( $this, 'email_notifications_cases' ), 100, 1 );
			add_filter( 'wpas_email_notifications_case_is_active', array(
				$this,
				'email_notifications_case_is_active',
			), 10, 2 );
			add_filter( 'wpas_email_notifications_cases_active_option', array(
				$this,
				'email_notifications_cases_active_option',
			), 10, 1 );
			add_filter( 'wpas_email_notifications_template_tags', array(
				$this,
				'email_notifications_template_tags',
			), 10, 1 );
			add_filter( 'wpas_email_notifications_tags_values', array(
				$this,
				'email_notifications_tags_values',
			), 10, 2 );
			add_filter( 'wpas_email_notifications_notify_user', array(
				$this,
				'email_notifications_notify_user',
			), 10, 3 );
			add_filter( 'wpas_email_notifications_pre_fetch_content', array(
				$this,
				'email_notifications_pre_fetch_content',
			), 10, 3 );
			add_filter( 'wpas_email_notifications_pre_fetch_subject', array(
				$this,
				'email_notifications_pre_fetch_subject',
			), 10, 3 );
		} else {
			remove_filter( 'wpas_email_notifications_cases', array( $this, 'email_notifications_cases' ) );
			remove_filter( 'wpas_email_notifications_case_is_active', array(
				$this,
				'email_notifications_case_is_active',
			) );
			remove_filter( 'wpas_email_notifications_cases_active_option', array(
				$this,
				'email_notifications_cases_active_option',
			) );
			remove_filter( 'wpas_email_notifications_template_tags', array(
				$this,
				'email_notifications_template_tags',
			) );
			remove_filter( 'wpas_email_notifications_tags_values', array( $this, 'email_notifications_tags_values' ) );
			remove_filter( 'wpas_email_notifications_notify_user', array( $this, 'email_notifications_notify_user' ) );
			remove_filter( 'wpas_email_notifications_pre_fetch_content', array(
				$this,
				'email_notifications_pre_fetch_content',
			) );
			remove_filter( 'wpas_email_notifications_pre_fetch_subject', array(
				$this,
				'email_notifications_pre_fetch_subject',
			) );
		}

		WPAS_GF::get_instance()->log( __FUNCTION__ . sprintf( __( ' Enable: (%s)', 'wpas-gf' ), $enable ) );

	}

	/**
	 * Email Notification: Enable Satisfaction Survey
	 *
	 * @since  0.1.3
	 *
	 * @param $cases
	 *
	 * @return string[]
	 *
	 */
	public function email_notifications_cases( $cases ) {

		if ( ! in_array( 'gravity_forms', $cases ) ) {
			$cases[] = 'gravity_forms';
		}


		WPAS_GF::get_instance()->log( __FUNCTION__ . sprintf( __( ' : (%s)', 'wpas-gf' ), implode( ', ', $cases ) ) );

		return $cases;

	}

	/**
	 * Returns email notification template status
	 *
	 * @param $option
	 * @param $case
	 *
	 * @return bool
	 */
	public function email_notifications_case_is_active( $option, $case ) {

		if ( $case === 'gravity_forms' && false === $option ) {
			$option = true;
		}

		WPAS_GF::get_instance()->log( __FUNCTION__ . sprintf( __( ' : (%s, %s)', 'wpas-gf' ), $option, $case ) );

		return $option;

	}

	/**
	 * Email Notification: Enable Satisfaction Survey
	 *
	 * @since  0.1.3
	 *
	 * @param $cases
	 *
	 * @return array<string,string>
	 *
	 */
	public function email_notifications_cases_active_option( $cases ) {

		if ( ! array_key_exists( 'gravity_forms', $cases ) ) {
			$cases[ 'gravity_forms' ] = 'enable_gravity_forms';
		}

		WPAS_GF::get_instance()->log( __FUNCTION__ . sprintf( __( ' : (%s)', 'wpas-gf' ), implode( ', ', $cases ) ) );

		return $cases;

	}

	/**
	 * Email Notification: Translate template tags
	 *
	 * @param $tags
	 *
	 * @return array[]
	 *
	 */
	public function email_notifications_template_tags( $tags ) {

		$tags[] = array(
			'tag'  => '{gf_settings}',
			'desc' => __( 'Displays the WPAS-GF settings for the form.', 'wpas-ss' ),
		);

		//WPAS_GF::get_instance()->log( __FUNCTION__ . sprintf( __( ' tags: (%s)', 'wpas-gf' ), implode( ', ', $tags ) ) );

		return $tags;

	}

	/**
	 * Email Notification: Tag Values
	 *
	 * @since  0.1.3
	 *
	 * @param $new
	 *
	 * @param $post_id
	 *
	 * @return mixed
	 *
	 */
	public function email_notifications_tags_values( $new, $post_id ) {

		foreach ( $new as $key => $tag ) {

			$name = trim( $tag[ 'tag' ], '{}' );

			switch ( $name ) {

				case 'gravity_forms_form_details':
					$tag[ 'value' ] = '{gravity_forms_form_details}';
					break;

				case 'gravity_forms_entry_details':
					$tag[ 'value' ] = '{gravity_forms_entry_details}';
					break;

				case 'gravity_forms_mapping_details':
					$tag[ 'value' ] = '{gravity_forms_mapping_details}';
					break;

				case 'gf_settings':
					$tag[ 'value' ] = '{gf_settings}';
					break;
			}

			$new[ $key ] = $tag;

			if ( array_key_exists( 'value' , $tag ) ) {
				WPAS_GF::get_instance()->log( __FUNCTION__ . sprintf( __( ' name: %s, value: %s', 'wpas-gf' ), $name, $tag[ 'value' ] ) );
			} else {
				WPAS_GF::get_instance()->log( __FUNCTION__ . sprintf( __( ' name: %s, value: %s', 'wpas-gf' ), $name, 'No value found for this email tag in the email tags array - maybe an error?' ) );
			}

		}

		return $new;

	}

	/**
	 * Email Notification: Email Message Template
	 *
	 * @param string $value     The e-mail content
	 * @param int    $ticket_id The ticket ID
	 * @param string $case      The current case being executed
	 *
	 * @return string
	 */
	public function email_notifications_pre_fetch_content( $value, $ticket_id, $case ) {

		if ( $case === 'gravity_forms' ) {
			$value = wpas_get_option( 'content_gravity_forms_exceptions' );
		}

		WPAS_GF::get_instance()->log( __FUNCTION__ . sprintf( __( ' case: %s, value: %s', 'wpas-gf' ), $case, $value ) );

		return $value;

	}

	/**
	 * Email Notification: Email Subject
	 *
	 * @since  0.1.3
	 *
	 * @param string $value     The e-mail subject
	 * @param int    $ticket_id The ticket ID
	 * @param string $case      The case being executed
	 *
	 * @return string
	 */
	public function email_notifications_pre_fetch_subject( $value, $ticket_id, $case ) {

		if ( $case === 'gravity_forms' ) {
			$value = wpas_get_option( 'subject_gravity_forms_exceptions' );
		}

		WPAS_GF::get_instance()->log( __FUNCTION__ . sprintf( __( ' case: %s, $value: %s', 'wpas-gf' ), $case, $value ) );

		return $value;

	}

	/**
	 * Email Notification: Recipient Email
	 *
	 * @since  0.1.3
	 *
	 * @param $user
	 *
	 * @param $case
	 *
	 * @param $ticket_id
	 *
	 * @return mixed
	 *
	 */
	public function email_notifications_notify_user( $user, $case, $ticket_id ) {

		if ( $case === 'gravity_forms' ) {
			$user = get_user_by( 'id', $this->get_agent_id() );
		}

		if ( ! empty( $user ) && ! is_wp_error( $user ) ) {
			WPAS_GF::get_instance()->log( __FUNCTION__ . sprintf( __( ' case: %s, user: %s', 'wpas-gf' ), $case, $user->user_email ) );
		} else {
			WPAS_GF::get_instance()->log( __FUNCTION__ . sprintf( __( ' acquired agent id is invalid. case: %s', 'wpas-gf' ), $case ) );
		}

		return $user;
	}

	/**
	 * Reopen this ticket if its closed.
	 *
	 * @since  0.1.0
	 * @return integer|boolean Reply ID
	 */
	protected function ticket_reopen() {

		if ( 0 === $this->ticket_id ) {
			return false;
		}

		$status = get_post_meta( $this->ticket_id, '_wpas_status', true );

		/* If the ticket was closed we re-open it */
		if ( 'closed' === $status ) {
			wpas_reopen_ticket( $this->ticket_id );
		}

		$status = get_post_meta( $this->ticket_id, '_wpas_status', true );
		WPAS_GF::get_instance()->log( __FUNCTION__ . ' Ticket status: ' . $status );

		return $this->ticket_id;

	}

	/**
	 * Maybe close the ticket if close ticket has been mapped for the form.
	 *
	 * Only works with reply forms.
	 *
	 * Note that this duplicates the function provided by mapping to the STATE field.
	 * Either way would allow you to close a ticket now.
	 * This is used as a demonstration of how to use custom fields to trigger additional functions inside of the
	 * Gravity Forms bridge and Awesome Support. Also, this allows close events to trigger whereas the direct STATE
	 * mapping does not seem to do that.
	 *
	 * Hook: gf_wpas_after_save_form
	 *
	 * @author Jamie Madden (https://github.com/digitalchild)
	 *
	 */
	public function maybe_close_ticket( $ticket_id, $data ) {

		if ( isset( $this->data[ 'gf_close_ticket' ] ) && $this->data[ 'gf_close_ticket' ] && ! empty ( $this->data[ 'gf_close_ticket' ] ) && '1' == $this->data[ 'gf_close_ticket' ] ) {

			wpas_close_ticket( $this->ticket_id );

			wpas_add_notification( 'reply_added_closed', __( 'Thanks for your reply. The ticket is now closed.', 'wpas-gf' ) );

			if ( false !== $link = wpas_get_reply_link( $this->reply_id ) ) {
				wpas_redirect( 'reply_added', $link );
				exit;
			}

		}

	}

}