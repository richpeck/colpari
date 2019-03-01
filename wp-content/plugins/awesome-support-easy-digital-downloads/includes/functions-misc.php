<?php
/**
 * @package   Awesome Support Easy Digital Downloads
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016 Awesome Support
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Get all client orders
 *
 * @since 0.1.0
 * @return bool|object
 */
function asedd_get_user_orders() {

	if ( ! function_exists( 'edd_get_users_purchases' ) ) {
		return array();
	}

	return edd_get_users_purchases( get_current_user_id(), -1, true, 'any' );

}

/**
 * Add a new error to a WP_Error object
 *
 * @since 0.1.0
 *
 * @param bool|WP_Error $thing   The thing that's supposed to be a WP_Error
 * @param string        $code    The error code
 * @param string        $message The error message
 *
 * @return WP_Error
 */
function asedd_add_error( $thing, $code, $message ) {

	if ( ! is_wp_error( $thing ) ) {
		$thing = new WP_Error;
	}

	$thing->add( $code, $message );

	return $thing;

}

add_filter( 'user_has_cap', 'asedd_allow_edd_customers', 0, 3 );
/**
 * Virtually give EDD customers the AS client capabilities
 *
 * @since 0.1.0
 * @todo  Client capabilities should not be hardcoded but should be retrieved from a core function instead
 *
 * @param array $allcaps User full capabilities
 * @param array $cap     Required capability
 *
 * @return array User full capabilities with maybe ours in addition
 */
function asedd_allow_edd_customers( $allcaps, $cap ) {

	$client_cap = apply_filters( 'wpas_user_capabilities_client', array(
		'view_ticket',
		'create_ticket',
		'close_ticket',
		'reply_ticket',
		'attach_files'
	) );

	if ( isset( $cap[0] ) && in_array( $cap[0], $client_cap ) ) {

		if ( empty( $allcaps[ $cap[0] ] ) && ! current_user_can( 'frontend_vendor' ) && edd_has_purchases() ) {
			foreach ( $client_cap as $c ) {
				$allcaps[ $c ] = true;
			}
		}

	}

	return $allcaps;

}