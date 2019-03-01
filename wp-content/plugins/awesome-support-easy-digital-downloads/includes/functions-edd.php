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

add_action( 'add_meta_boxes', 'asedd_show_purchase_metabox' );
/**
 * Register the EDD purchase metabox
 *
 * @since 0.1.0
 * @return void
 */
function asedd_show_purchase_metabox() {
	add_meta_box( 'asedd_metabox_purchase', __( 'EDD Purchase', 'as-edd' ), 'asedd_show_purchase_metabox_content', 'ticket', 'side' );
}

/**
 * EDD purchase metabox content
 *
 * @since 0.1.0
 * @return void
 */
function asedd_show_purchase_metabox_content() {
	require( AS_EDD_PATH . 'includes/views/metabox-purchase.php' );
}

/**
 * Checks if a given product ID is an EDD download
 *
 * Product IDs passed in the ticket submission form are referring to
 * taxonomy terms IDs. If the selected product is an EDD download, then
 * the taxonomy term will be a placeholder for the actual download
 * (this is handled by the WPAS_Product_Sync class).
 *
 * @since 0.1.0
 *
 * @param $product_id
 *
 * @return bool|WP_Post The post object if the product is an EDD download, false otherwise
 */
function asedd_is_product_download( $product_id ) {

	global $wpdb;

	/* We use a SQL query because get_term() would give us a filtered result */
	$query     = $wpdb->prepare( "SELECT * FROM $wpdb->terms WHERE term_id = '%d'", (int) $product_id );
	$term_name = $wpdb->get_col( $query, 1 );

	if ( ! is_array( $term_name ) || ! isset( $term_name[0] ) ) {
		return false;
	}

	$term_name = $term_name[0];

	if ( ! is_numeric( $term_name ) ) {
		return false;
	}

	$post_id = (int) $term_name;

	if ( get_post_type( $post_id ) !== 'download' ) {
		return false;
	}

	return get_post( $post_id );

}

add_action( 'init', 'asedd_check_user_order_id' );
/**
 * Make sure the possibly pre-selected order belongs to the current user
 *
 * If a purchase number is passed as GET variable, let's make sure that it actually
 * belongs to the current user. Otherwise clients could use eachother's purchase numbers
 * and more importantly someone could get another client's license keys.
 *
 * @since 0.1.0
 * @return void
 */
function asedd_check_user_order_id() {

	$order_id = filter_input( INPUT_GET, 'wpas_edd_order_num', FILTER_SANITIZE_NUMBER_INT );

	if ( empty( $order_id ) ) {
		return;
	}

	$payment = edd_get_payment_by( 'id', $order_id );

	if ( ! is_object( $payment ) || ! is_a( $payment, 'EDD_Payment' ) || get_current_user_id() !== (int) $payment->user_info['id'] ) {
		wp_die( sprintf( __( 'Don&#039;t use other clients orders please. <a href="%s">Click here to submit a ticket</a>.', 'as-edd' ), wpas_get_submission_page_url() ), __( 'Incorrect order', 'as-edd' ) );
	}

}