<?php

namespace WPAS_Remote_Tickets;

class Authentication {
	
	/**
	 * @var
	 */
	protected static $_instance;

	/**
	 * Only make one instance of \RCP_Avatax\Authentication
	 *
	 * @return Authentication
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof Authentication ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	protected function __construct() {
		add_filter( 'determine_current_user', array( $this, 'rest_api_auth_handler' ), 21 );
	}

	/**
	 * Custom authorization for remote tickets
	 *
	 * @param $input_user
	 *
	 * @return int|\WP_Error
	 */
	public function rest_api_auth_handler( $input_user ) {

		// Don't authenticate twice
		if ( ! empty( $input_user ) ) {
			return $input_user;
		}

		if ( ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return $input_user;
		}

		global $wp;

		$route     = isset( $wp->query_vars['rest_route'] ) ? $wp->query_vars['rest_route'] : '';
		$whitelist = array( '/wpas-api/v1/tickets', '/wpas-api/v1/attachments' );
		$data      = \WP_REST_Server::get_raw_data();

		if ( ! $data && ! empty( $_POST ) ) {
			$data = json_encode( $_POST );
		}

		if ( empty( $data ) || ! in_array( $route, $whitelist ) ) {
			return $input_user;
		}

		$data = json_decode( $data );

		if ( ! isset( $data->email, $data->gadget_id ) ) {
			return $input_user;
		}

		// check to see if the authentication has been disabled
		if ( get_post_meta( $data->gadget_id, '_wpas_disable_authentication', true ) ) {
			$user = get_user_by( 'email', $data->email );

			if ( isset( $user->ID ) ) {
				return $user->ID;
			}

			$user_id = wpas_insert_user( array(
				'email' => $data->email,
				'first_name' => $data->first_name,
				'last_name'  => $data->last_name,
				'pwd'   => wp_generate_password()
			) );

			if ( ! is_wp_error( $user_id ) ) {
				return $user_id;
			}

		} else {
			$user = get_user_by( 'email', $data->email );

			if ( empty( $user->ID ) ) {
				return $input_user;
			}

			if ( wp_check_password( $data->password, $user->user_pass, $user->ID ) ) {
				return $user->ID;
			}
		}

		return $input_user;
	}

}