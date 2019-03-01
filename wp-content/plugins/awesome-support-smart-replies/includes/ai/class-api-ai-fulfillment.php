<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


require_once WPAS_CBOT_PATH.'vendor/autoload.php';

use ApiAi\Client;



class WPAS_CBOT_API_AI_Fulfillment {
	
	protected $text;
	
	
	public function __construct() {
		
		add_action( 'rest_api_init', array( $this, 'register_rest_route' ), 10 );
	}
	
	/**
	 * Retrieve the url for the fulfillment webhook endpoint
	 *
	 * @param bool $parts  If true, method will return an associative array of the url parts.
	 *
	 * @return array|string
	 */
	public static function get_webhook_url( $parts = false ) {
		
		$base_url    = rest_url();
		$namespace   = 'wpascbotaifulfillment';
		$endpoint    = 'webhook';
		
		$url_parts = compact( 'base_url', 'namespace', 'endpoint' );

		if ( $parts ) {
			return $url_parts;
		}
		
		$url = rest_url( "{$namespace}/$endpoint" );

		return set_url_scheme( $url, 'https' );
	}
	
	
	
	/**
	 * Set up our webhook endpoint
	 */
	function register_rest_route() {
		$webhook_url = $this->get_webhook_url( true );
		register_rest_route( $webhook_url['namespace'], $webhook_url['endpoint'], array(
			'methods'  => 'GET,POST',
			'callback' => array( $this, 'receive_api_request' ),
		) );
	}
	
	
	/**
	 * Handles requests to our webhook endpoint
	 *
	 * @param WP_REST_Response $req
	 *
	 * @return WP_REST_Response
	 */
	function receive_api_request( $req ) {
		
		$method = $req->get_method();
		
		wpas_cbot_save_log( "API AI FullFillment Call : " . json_encode( $req['result'] ) );

		if ( 'POST' === $method && isset( $req['result'] ) && isset( $req['result']['action'] ) ) {
			
			$text = $req['result']['resolvedQuery'];
			
			$this->sendReply( $text, $req );
			
		}

		return new WP_REST_Response( 0, 200 );
	}
	
	
	
	/**
	 * Send response to API.AI
	 * 
	 * @param string $text
	 * @param array $req
	 */
	function sendReply( $text = '', $req ) {
		
		if( wpas_cbot_get_option( 'cbot_enable_gnl' ) ) {
			$links = WPAS_CBOT_Google_Natural_Language::search_posts( $text, 1, 25, 'wpas_cbot_get_more_ffment_fb_links' );
		} else {
			$links = wpas_cbot_search_posts( $text, 1, 25, 'wpas_cbot_get_more_ffment_fb_links' );
		}
		
		wp_send_json(
			array(
			    "source" => $req["result"]["source"],
			    "speech" => ( isset( $links['links_text'] ) ? $links['links_text'] : '' ),
			    "displayText" => ( isset( $links['links_text'] ) ? $links['links_text'] : '' ),
			    "contextOut" => array(),
			    'data' => array( 'facebook' => $links )
			    )
			);
		
		die();
	}
	
}