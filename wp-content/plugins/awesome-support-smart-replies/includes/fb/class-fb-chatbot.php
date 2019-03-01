<?php


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


class WPAS_FB_CBOT {

	
	protected $api_namespace;
	protected $access_token;

	function __construct() {
		$this->register_hooks();
	}

	/**
	 * Make protected properties gettable so they're read-only.
	 *
	 * @param $name
	 *
	 * @return mixed
	 * @throws Exception
	 */
	function __get( $name ) {
		if ( property_exists( $this, $name ) ) {
			return $this->{$name};
		}

		throw new Exception( "Can not get property: {$name}", 1 );
	}

	/**
	 * Register any hooks that we need for plugin operation.
	 */
	function register_hooks() {
		
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ), 10 );
		add_action( 'wpas_fb_cbot_message_received', array( $this, 'init_bot' ) );
		
	}
	

	/**
	 * Retrieve and cache the page access token string from options table
	 * or use cached value if its set.
	 * @return string|false
	 */
	function get_page_access_token() {
		if ( ! $this->access_token ) {
			$this->access_token = wpas_get_option( 'cbot_fb_page_access_token' );
		}

		return $this->access_token;
	}

	/**
	 * Retrieves verification string transient or generates verification string and stores
	 * it as a transient and then returns it.
	 * @return string
	 */
	public static function get_verification_string() {
		$transient_name = 'wpas_cbot_verification_string';
		if ( ! $verification_string = get_site_transient( $transient_name ) ) {
			$verification_string = md5( WPAS_CBOT_NS . time() );
			set_site_transient( $transient_name, $verification_string );
		}

		return $verification_string;
	}

	/**
	 * Set up our webhook endpoint
	 */
	function rest_api_init() {
		$webhook_url = $this->get_webhook_url( true );
		register_rest_route( $webhook_url['namespace'], $webhook_url['endpoint'], array(
			'methods'  => 'GET,POST',
			'callback' => array( $this, 'receive_api_request' ),
		) );
	}

	/**
	 * Retrieve the url for the webhook endpoint
	 *
	 * @param bool $parts  If true, method will return an associative array of the url parts.
	 *
	 * @return array|string
	 */
	public static function get_webhook_url( $parts = false ) {
		
		$base_url = get_home_url( null, '', 'https' );
		
		$namespace   = apply_filters( 'wpas_cbot_fp_api_namespace', trim( WPAS_CBOT_NS, '_' ) );
		
		$endpoint    = apply_filters( 'wpas_fb_cbot_api_endpoint', 'webhook' );
		$rest_prefix = rest_get_url_prefix();

		$url_parts = compact( 'base_url', 'rest_prefix', 'namespace', 'endpoint' );

		if ( $parts ) {
			return $url_parts;
		}

		return implode( '/', $url_parts );
	}

	/**
	 * Handles requests to our webhook endpoint
	 *
	 * @param WP_REST_Response $req
	 *
	 * @return WP_REST_Response
	 */
	function receive_api_request( $req ) {
		do_action( 'wpas_fb_cbot_request_received', $req );

		$method = $req->get_method();
		

		if ( 'GET' === $method && isset( $req['hub_mode'] ) && 'subscribe' == $req['hub_mode'] ) {
			$this->verify_webhook_subscription( $req );
		}

		if ( 'POST' === $method && isset( $req['object'] ) && 'page' === $req['object'] && isset( $req['entry'] ) ) {
			

			$entries = $req['entry'];
			if ( ! is_array( $entries ) ) {
				$entries = array( $entries );
			}
			
			
			foreach ( $entries as $entry ) {
				
				if ( ! isset( $entry['messaging'] ) ) {
					continue;
				}
				
				if ( ! is_array( $entry['messaging'] ) ) {
					$entry['messaging'] = array( $entry['messaging'] );
				}
				foreach ( $entry['messaging'] as $message ) {
					
					do_action( 'wpas_fb_cbot_message_received', new WPAS_CBOT_Messaging( $message, $this ) );
					
				}
			}
		}

		return new WP_REST_Response( 0, 200 );
	}
	
	/**
	 * Handles webhook subscription verification for your facebook App
	 * @param WP_REST_Request $req
	 */
	function verify_webhook_subscription( $req ) {
		if ( $this->get_verification_string() === $req['hub_verify_token'] ) {
			http_response_code( 200 );
			update_site_option( 'wpas_fb_cbot_verified', true );
			exit( $req['hub_challenge'] );
		} else {
			error_log( 'Recieved invalid webhook validation request. Expected: "' . $this->get_verification_string . '" Received: "' . $req['hub_verify_token'] . '"' );
			http_response_code( 403 );
			exit( 0 );
		}
	}
	
	/**
	 * Start searching and replying to messages
	 * 
	 * @param WPAS_CBOT_Messaging $M
	 */
	public function init_bot( $M ) {
		
		
		$this->save_log( 'Bot Started' );
		if( $M->postback ) {
			$M->handle_post_back();
		} else {
			$M->maybe_send_reply();
		}
		$this->save_log( 'Bot Ended' );
		exit( 0 );
	}
	
	/**
	 * Save bot logs
	 * 
	 * @param string $content
	 * 
	 * @return void
	 */
	public function save_log( $content = '') {
		wpas_cbot_save_log( $content );
	}
	
}