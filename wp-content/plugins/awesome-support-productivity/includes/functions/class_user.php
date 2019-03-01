<?php

/**
 * Handle User features
 */
class WPAS_PF_User {
	
	protected static $instance = null;
	
	public function __construct() {
		
		add_action( 'wpas_backend_ticket_stakeholders_before', array( $this, 'add_user_form' ), 10, 1 ); // Add new user popup and button
		add_action( 'wp_ajax_wpas_pf_createuser',	       array( $this, 'add_user' )     , 10, 0 ); // Process adding new user via ajax
		add_action( 'edit_user_profile',		       array( $this, 'user_tickets' ) , 10, 1 ); // Display tickets on user profile page
		add_filter( 'add_ticket_column_custom_fields',         array( $this, 'add_custom_fields'), 11, 1 );
		
		add_filter( 'wp_editor_settings',			array( $this, 'editor_settings' ), 20, 2 );
		
		add_filter( 'wpas_pf_general_setting_options',		array( $this, 'add_settings' ), 11, 1 ); // Add recaptcha settings
		add_action( 'wpas_after_registration_fields',		array( $this, 'add_recaptcha') );

		add_action( 'wpas_register_account_before',		array( $this, 'validate_recaptcha'), 11, 1 );

	}
	
	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	
	/**
	 * 
	 * @param type $should_add
	 * @return boolean
	 */
	function add_custom_fields( $should_add = false ) {
		global $pagenow;
		
		if ( false === $should_add ) {
			
			if( 'user-edit.php' === $pagenow ) {
				$should_add = true;
			}
			
		}
		
		return $should_add;
	}
	
	/**
	 * Display tickets on user profile page
	 * 
	 * @param object $user
	 */
	public function user_tickets( $user ) {
		wp_enqueue_style( 'wpas-admin-styles' );
		$tickets = wpas_get_tickets('any', array('author' => $user->ID));
		
		include WPAS_PF_PATH . 'includes/templates/client_tickets.php';
	}

	
	/**
	 * Handle add new user request
	 */
	public function add_user() {
		
		check_admin_referer( 'create-user', '_wpnonce_create-user' );
		
		$errors = array();
		$success = false;
		
		if ( ! current_user_can( 'create_users' ) ) {
			$errors[0] = __( 'Sorry, you are not allowed to create users.' );
		} 
		
		// We need to generate a random password for new user
		$_POST['pass1'] = $_POST['pass2'] = wp_generate_password( 24 );
		
		$user_id = edit_user();

		if ( is_wp_error( $user_id ) ) {
			$errors = reset( $user_id->errors );
		} else {
			$success = true;
		}
		
		
		if ( true === $success ) {
			wp_send_json_success( "User successfully added" );
		}
		
		
		wp_send_json_error( $errors[0] );
		die();
	}
	
	
	/**
	 * add user button and window html
	 * 
	 * @param int $post_id
	 */
	public function add_user_form( $post_id ) {
		add_thickbox();
		?>

		<div>
			<?php include WPAS_PF_PATH . 'includes/templates/add_user.php' ?>
			<div class="wpas_pf_add_user_button">
				<a href="#TB_inline?width=600&height=450&inlineId=wpas_add_user_wrapper" title="Add User">Add User</a>
			</div>
		</div>

		<?php
	}
	
	
	/**
	 * Implement editor settings based on user capability
	 * 
	 * @global string $post_type
	 * @param array $settings
	 * @param string $editor_id
	 * 
	 * @return array
	 */
	public function editor_settings( $settings, $editor_id ) {
		
		global $post_type;
		
		if( 'ticket' === $post_type && ( 'content' === $editor_id || 'wpas_reply' === $editor_id ) ) {
			$user = wp_get_current_user();
		
			if( $user->has_cap( 'edit_ticket_with_full_editor' ) ) {
				$settings['teeny'] = false;
			} else {
				$settings['teeny'] = true;
			}
		}
		
		return $settings;
	}
	
	
	/**
	 * Add Recaptcha settings
	 * 
	 * @param array $settings
	 * 
	 * @return array
	 */
	public function add_settings( $settings = array() ) {
		
		$settings[] = array(
			'name'    => __( 'Google recaptcha site key', 'wpas_productivity' ),
			'id'      => 'g_recaptcha_site_key',
			'type'    => 'text'
		);
		
		$settings[] = array(
			'name'    => __( 'Google recaptcha secret key', 'wpas_productivity' ),
			'id'      => 'g_recaptcha_secret_key',
			'type'    => 'text'
		);
		
		return $settings;
	}
	
	
	/**
	 * Add recaptcha field
	 */
	public function add_recaptcha() {
		
	
		$site_key =  wpas_get_option( 'g_recaptcha_site_key' );
		$secret_key = wpas_get_option( 'g_recaptcha_secret_key' );

		if( $site_key && $secret_key ) {
			echo '<style>.g-recaptcha .grecaptcha-user-facing-error{display:none;}</style>';
			echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
			printf('<div style="margin-bottom: 20px;" class="g-recaptcha" data-sitekey="%s" data-callback="onRegSubmit" data-badge="inline" data-size="invisible"></div>', $site_key );
		}
	
	}
	
	/**
	 * 
	 * @global WP_Post $post
	 * 
	 * @param array $user
	 * 
	 * @return void
	 */
	public function validate_recaptcha( $user ) {
		
		
		
		$site_key =  wpas_get_option( 'g_recaptcha_site_key' );
		$secret_key = wpas_get_option( 'g_recaptcha_secret_key' );
		
		if( !( $site_key && $secret_key ) ) {
			return;
		}
		
		$code = filter_input( INPUT_POST, 'g-recaptcha-response' );
		$error = false;
		
		if( empty( $code ) ) {
			$error = true;
		}
		
		
		if( !$error ) {
			
			$context_args = array(
				'ssl' => array(
					'verify_peer' => false,
					'method'  => 'POST',
					'header'  => 'Content-type: application/x-www-form-urlencoded',
					
				)
			);
			
			$query_args = array(
				'secret' => $secret_key,
				'remoteip' => $_SERVER['REMOTE_ADDR'],
				'response' => $code,
			);


			$url = 'https://www.google.com/recaptcha/api/siteverify?'. http_build_query( $query_args );
			$context = stream_context_create( $context_args );
			$response = file_get_contents( $url, false, $context );
			
			$response_data = json_decode( $response, true );

			if ( !( isset( $response_data['success'] ) && true === $response_data['success'] ) ) {
				$error = true;
			}
		}
		
		if( $error ) {
			
			$redirect_to = home_url();

			if ( isset( $_POST['redirect_to'] ) ) {
				$redirect_to = wp_sanitize_redirect( $_POST['redirect_to'] ); // If a redirect URL is specified we use it
			} else {

				global $post;

				// Otherwise we try to get the URL of the originating page
				if ( isset( $post ) && $post instanceof WP_Post ) {
					$redirect_to = wp_sanitize_redirect( get_permalink( $post->ID ) );
				}

			}
			
			
			wpas_add_error( 'registration_recaptcha_failed', __( 'Recaptcha failed, try again.', 'wpas_productivity' ) );
			wp_safe_redirect( $redirect_to );
			exit;
			
		}
	
		
	
	}
	
}