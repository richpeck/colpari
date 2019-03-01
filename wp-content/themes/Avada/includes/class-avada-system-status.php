<?php
/**
 * Various helper methods for Avada's System Status page.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.6
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Various helper methods for Avada.
 *
 * @since 5.6
 */
class Avada_System_Status {

	/**
	 * The class constructor
	 *
	 * @access public
	 */
	public function __construct() {

		add_action( 'wp_ajax_fusion_check_api_status', array( $this, 'check_api_status' ) );
	}

	/**
	 * AJAX callback method, used to check various APIs status.
	 *
	 * @access public
	 */
	public function check_api_status() {

		if ( ! isset( $_GET['api_type'] ) || ! check_ajax_referer( 'fusion_check_api_status_nonce', 'nonce', false ) ) {
			echo wp_json_encode(
				array(
					'code'         => 200,
					'message'      => __( 'API type missing.', 'Avada' ),
					'api_response' => '',
				)
			);
			die();
		}

		$envato_string = '';
		$api_type      = trim( sanitize_text_field( wp_unslash( $_GET['api_type'] ) ) );
		$api_response  = array();
		$response      = array(
			'code'         => 200,
			'message'      => __( 'Tested API is working properly.', 'Avada' ),
			'api_response' => '',
		);

		if ( 'tf_updates' === $api_type ) {
			$api_response     = $this->check_tf_updates_status();
			$response['code'] = (int) trim( wp_remote_retrieve_response_code( $api_response ) );
		}

		if ( 'envato' === $api_type ) {
			$api_response = $this->check_envato_status( true );

			if ( is_wp_error( $api_response ) ) {
				$response['code'] = (int) trim( $api_response->get_error_code() );
				$envato_string    = str_replace( array( 'Unauthorized', 'Forbidden' ), '<br />Invalid Token', $api_response->get_error_message() );
			} elseif ( isset( $api_response['headers_data'] ) ) {
				$envato_string       = $api_response['headers_data'];
				$response['message'] = $response['message'] . ' ' . $envato_string;
			}
		}

		// Serialize whole array for easier debugging.
		$response['api_response'] = esc_textarea( maybe_serialize( $api_response ) );
		if ( 401 === $response['code'] ) {
			/* translators: HTTP response code */
			$response['message'] = sprintf( __( 'Server responded with unauthorized response code: %1$s. %2$s', 'Avada' ), $response['code'], $envato_string );
		} elseif ( 3 === (int) ( $response['code'] / 100 ) ) {
			/* translators: HTTP response code */
			$response['message'] = sprintf( __( 'Server responded with redirection response code: %1$s. %2$s', 'Avada' ), $response['code'], $envato_string );
		} elseif ( 4 === (int) ( $response['code'] / 100 ) ) {
			/* translators: HTTP response code */
			$response['message'] = sprintf( __( 'Error occured while checking API status. Response code: %1$s. %2$s', 'Avada' ), $response['code'], $envato_string );
		} elseif ( 5 === (int) ( $response['code'] / 100 ) ) {
			/* translators: HTTP response code */
			$response['message'] = sprintf( __( 'Internall server error occured while checking API status. Response code: %1$s. %2$s', 'Avada' ), $response['code'], $envato_string );
		} elseif ( 200 !== $response['code'] ) {
			/* translators: HTTP response code */
			$response['message'] = sprintf( __( 'Something went wrong while checking API status. Response code: %1$s. %2$s', 'Avada' ), $response['code'], $envato_string );
		}

		echo wp_json_encode( $response );
		die();
	}

	/**
	 * Helper method, pings ThemeFusion server.
	 *
	 * @access private
	 * @return array wp_remote_get response.
	 */
	private function check_tf_updates_status() {
		return wp_remote_get( Fusion_Patcher_Client::$remote_patches_uri );
	}

	/**
	 * Helper method, pings Envato server.
	 *
	 * @access private
	 * @param bool $headers_data Set to true if response headers should be provided.
	 * @return mixed array|WP_Error Depending on server response.
	 */
	private function check_envato_status( $headers_data = false ) {
		return Avada()->registration->envato_api()->request( 'https://api.envato.com/v2/market/buyer/download?item_id=2833226', array( 'headers_data' => $headers_data ) );
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
