<?php
namespace AS_Guest_Tickets;

/**
 * Implements guest login.
 *
 * @since 1.0.0
 */
class Login {

	/**
	 * Starts up the Login instance.
	 *
	 * @access public
	 * @since  1.0
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initializes guest login.
	 *
	 * @access public
	 * @since  1.0
	 */
	public function init() {
		// Register the shortcode. Uses the default [ticket-submit] display callback.
		add_shortcode( 'guest-ticket-submit', 'wpas_sc_submit_form' );

		// Ajax.
		$this->handle_ajax();

		// Handle redirecting guest submissions to the 'Thank You' page.
		add_filter( 'wpas_redirect_ticket_added', array( $this, 'capture_guest_submission_redirect' ), 100 );

		// Inject the Guest Log In form.
		add_action( 'wpas_before_login_form', array( $this, 'display_guest_login' ) );

		// Hides the ticket navigation for guest users.
		add_action( 'wpas_submission_form_inside_before_subject', array( $this, 'hide_ticket_navigation' ) );
	}

	/**
	 * Fire Ajax handlers.
	 *
	 * @access protected
	 * @since  1.0
	 */
	protected function handle_ajax() {
		// User authentication Ajax.
		add_action( 'wp_ajax_nopriv_gt_check_user', array( $this, 'check_user' ) );

		// Authenticate user via Ajax.
		add_action( 'wp_ajax_nopriv_gt_auth_user', array( $this, 'auth_user' ) );

		// Register User via Ajax
		add_action( 'wp_ajax_nopriv_gt_register_user', array( $this, 'register_user' ) );
	}


	/**
	 * Ajax handler for checking the user.
	 *
	 * @access public
	 * @since  1.0
	 */
	public function check_user() {

		$user = get_user_by( 'email', sanitize_email( $_POST['email'] ) );

		wp_send_json( array(
			'registered' => ( $user ) ? 1 : 0
		) );

		exit;

	}

	/**
	 * Ajax handler for register guest user
	 *
	 * @access public
	 * @since  1.1.1
	 */
	public function register_user() {

		$email = sanitize_email( $_POST['email'] );
		$user  = get_user_by( 'email', $email );

		// Check if a user already exists for this email address.
		if ( $user || ! is_email( $email ) ) {

			// Message is deliberately ambiguous to existing user vs invalid email.
			wp_send_json( array(
				'status'  => 0,
				'message' => __( 'The user email is invalid.', 'as-guest-tickets' )
			) );

		} else if ( ( intval( $_POST['privacy_notice'] ) === 0 ) && ( ! empty( wpas_get_option( 'gt_privacy_notice_short_desc_01', '' )  )  )  ) {

			wp_send_json( array(
				'status'  => 0,
				'message' => wpas_get_option( 'gt_privacy_notice_err_msg_01', __( 'You must agree to the privacy notice', 'as-guest-tickets' ) )
			) );

		} else {

			// Generate a password. This will never be used by the user.
			$pwd = wp_generate_password( 24, true );

			// Create the user account.
			$user_id = wp_insert_user( array(
				'user_login'   => $email,
				'user_email'   => $email,
				'display_name' => _x( 'Guest', 'guest user display name', 'as-guest-tickets' ),
				'user_pass'    => $pwd,
				'role'         => wpas_get_option( 'new_user_role', 'wpas_user' ),
			) );

			// If the user couldn't be created, bail.
			if ( is_wp_error( $user_id ) ) {

				wp_send_json( array(
					'status'  => 0,
					'message' => __( 'The guest user could not be created.', 'as-guest-tickets' )
				) );

			} else {
				/*
				 * Flag the account as a guest user. This is used as a signifier in the redirect step
				 * to log the guest user out and point to the 'Thank You' page.
				 */
				update_user_option( $user_id, '_guest_user', 1 );

				// Temporarily authenticate the new user.
				$user_data = get_userdata( $user_id );

				$user = wp_signon( array(
					'user_login'    => $user_data->user_login,
					'user_password' => $pwd
				) );

				if ( is_wp_error( $user ) ) {

					wp_send_json( array(
						'status'  => 0,
						'message' => __( 'The newly-created guest user was not found.', 'as-guest-tickets' )
					) );
					
				} else {

					wp_send_json( array(
						'status'  => 1,
						'url'     => wpas_get_submission_page_url()
					) );

				}
			}
		}

		wp_die();
	}

	/**
	 * Ajax handler for authenticating an existing user based on email and password.
	 *
	 * @access public
	 * @since  1.1.1
	 */
	public function auth_user() {

		if ( empty( $_POST['email'] ) || empty( $_POST['password'] ) ) {

			wp_send_json( array(
				'status'  => 0,
				'message' => __( 'An email address and password must be provided.', 'as-guest-tickets' )
			) );

		}

		$email = sanitize_email( $_POST['email'] );
		$user  = get_user_by( 'email', $email );

		if ( ! $user || ! is_email( $email ) ) {

			wp_send_json( array(
				'status'  => 0,
				'message' => __( 'The user email is invalid.', 'as-guest-tickets' )
			) );

		} else {

			// Authenticate the user.
			$sign_on = wp_signon( array(
				'user_login'     => $user->user_login,
				'user_password'  => sanitize_text_field( $_POST['password'] )
			) );

			if ( is_wp_error( $sign_on ) ) {

				wp_send_json( array(
					'status'  => 0,
					'message' => $sign_on->get_error_message()
				) );

			} else {

				wp_send_json( array(
					'status'  => 1,
					'url'     => wpas_get_submission_page_url()
				) );

			}
		}
	}

	/**
	 * Handles filtering the redirect for guest submissions to the 'Thank You' page.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param string $location Redirect URL.
	 * @return string (Maybe) filtered redirect URL.
	 */
	public function capture_guest_submission_redirect( $location ) {
		$current_user_id = get_current_user_id();
		$guest_user      = get_user_option( '_guest_user', $current_user_id );

		if ( $guest_user ) {
			if ( $thank_you_redirect = \wpas_get_option( 'gt_thank_you_page' ) ) {
				$location = get_permalink( $thank_you_redirect );
			} else {
				$location = home_url();
			}

			// Unset the guest user status.
			delete_user_option( $current_user_id, '_guest_user' );

			// Log the user out.
			if ( false === boolval( wpas_get_option( 'gt_leave_guest_user_logged_in', false ) ) ) {
				wp_destroy_current_session();
				wp_clear_auth_cookie();
			}

			// Send the new user notification.
			$receive_alert = wpas_get_option('gt_notify_users', 'both');
			if ( 'none' <> $receive_alert ) {
				wp_new_user_notification( $current_user_id, null, $receive_alert );
			}

			/**
			 * Filters the guest ticket submission redirect URL.
			 *
			 * Only fires if the '_guest_user' user meta is present for the current user.
			 *
			 * @since 1.0.0
			 *
			 * @param string    $location           Redirect URL.
			 * @param int|false $thank_you_redirect Thank You redirect page ID if set, otherwise false.
			 */
			$location = apply_filters( 'guest_ticket_submission_redirect', $location, $thank_you_redirect );
		}

		return $location;
	}

	/**
	 * Displays the Guest Log In form on the registration template.
	 *
	 * @access public
	 * @since  1.0
	 */
	public function display_guest_login() {

		$post = get_post();

		$this->enqueue_scripts_styles();

		// include login/register form
		include AS_GT_PATH . 'includes/views/login_form.php';

	}

	/**
	 * Enqueues scripts and styles for the Guest Login UI.
	 *
	 * @access protected
	 * @since  1.0
	 */
	protected function enqueue_scripts_styles() {

		wp_enqueue_script( 'wpas-gt-check-user', AS_GT_URL . 'assets/public/js/check-user.js', array( 'jquery' ), AS_GT_VERSION );

		// load google recaptcha api
		if ( wpas_get_option( 'gt_use_recaptcha', false )  ) {
			wp_enqueue_script( 'wpas-google-recaptcha', 'https://www.google.com/recaptcha/api.js?onload=gt_onload_callback&render=explicit', null, null, true );
		}

		wp_enqueue_style( 'wpas-gt-css', AS_GT_URL . 'assets/public/css/style.css', AS_GT_VERSION );

		// Pass message strings to the script.
		wp_localize_script( 'wpas-gt-check-user', 'gtl10n', array(
			'existingUserMsg' => __( 'It looks like you already have a support account. Please enter your password:', 'as-guest-tickets' ),
			'ajaxurl'         => admin_url( 'admin-ajax.php' ),
			'adminurl'        => admin_url()
		) );
	}

	/**
	 * Hides the ticket navigation for guest users.
	 *
	 * @access public
	 * @since  1.0
	 */
	public function hide_ticket_navigation() {
		if ( get_user_option( get_current_user_id(), '_guest_user' ) ) {
			?><style type="text/css">.wpas-ticket-buttons-top { display: none; }</style><?php
		}
	}

}
