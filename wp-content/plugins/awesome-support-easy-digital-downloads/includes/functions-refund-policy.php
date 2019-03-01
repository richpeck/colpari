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
 * Add the refund policy status
 *
 * @since 0.1.0
 *
 * @return string
 */
function asedd_refund_policy_status() {

	if ( ! asedd_get_refund_policy_days() ) {
		return;
	}

	$refund_status = asedd_get_refund_policy_status();
	$refund_label  = $refund_status ? __( 'Active', 'as-edd' ) : __( 'Expired', 'as-edd' );
	$refund_class  = $refund_status ? 'active' : 'expired';
	$bgcolor       = $refund_status ? '#81d742' : '#dd3333';
	$refund        = sprintf( '<span class="wpas-label as-edd-refund-policy-%s" style="background-color:%s;">%s</span>', $refund_class, $bgcolor, $refund_label );

	echo sprintf( '<div class="%s" id="%s">%s %s</div>', 'wpas-form-group', 'wpas_edd_refund_policy_status', __( 'Refund Policy:' ), $refund );

}

/**
 * Get the number of days for the refund policy
 *
 * If the result is an empty string then the function is not active
 *
 * @since 0.1.0
 * @return int|string|bool
 */
function asedd_get_refund_policy_days() {
	return wpas_get_option( 'edd_refund_policy_days', false );
}

/**
 * Get the refund policy status for a purchase
 *
 * @since 0.1.0
 * @return bool Whether or not the purchase is still un the refund delay
 */
function asedd_get_refund_policy_status() {

	global $post;

	if ( ! is_object( $post ) ) {
		return false;
	}

	$days = asedd_get_refund_policy_days();

	if ( ! function_exists( 'edd_get_payment_by' ) ) {
		return false;
	}


	$edd_order = get_post_meta( $post->ID, '_wpas_edd_order_num', true );
	$payment   = edd_get_payment_by( 'id', (int) $edd_order );
	$days      = (int) $days;

	if ( ! $payment ) {
		return false;
	}

	if ( ! isset( $payment->date ) ) {
		return false;
	}

	$limit  = strtotime( "$payment->date +$days days" );
	$status = time() <= $limit ? true : false;

	return $status;

}