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

add_action( 'edd_purchase_history_header_after', 'asedd_purchase_history_support_header', 99, 0 );
/**
 * Add a new column header in the purchase history table
 *
 * @since 0.1.0
 * @return void
 */
function asedd_purchase_history_support_header() {

	if ( ! current_user_can( 'create_ticket' ) ) {
		return;
	}

	printf( '<th class="%s">%s</th>', 'as_edd_purchase_support_header', __( 'Support', 'as-edd' ) );

}

add_action( 'edd_purchase_history_row_end', 'asedd_purchase_history_support', 99, 2 );
/**
 * Add new column content in the purchase history table
 *
 * @since 0.1.0
 *
 * @param int   $post_id       Purchase ID
 * @param array $purchase_data Purchase details
 *
 * @return void
 */
function asedd_purchase_history_support( $post_id, $purchase_data ) {

	if ( ! current_user_can( 'create_ticket' ) ) {
		return;
	}

	$support_link = esc_url( add_query_arg( 'wpas_edd_order_num', $post_id, wpas_get_submission_page_url() ) );
	$label        = __( 'Get Help', 'as-edd' );

	printf( '<td class="%s">%s</td>', 'as_edd_purchase_support_content', "<a href='$support_link'>$label</a>" );

}