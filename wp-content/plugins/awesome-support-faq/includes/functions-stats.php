<?php
/**
 * @package   Awesome Support FAQ/Search
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'wp', 'asfaq_count_visits' );
/**
 * Add new view to a FAQ
 *
 * @since 1.0
 * @return void
 */
function asfaq_count_visits() {

	global $post;

	// Do not count visits from the agents
	if ( is_user_logged_in() && current_user_can( 'edit_ticket' ) ) {
		return;
	}

	if ( ! is_single() ) {
		return;
	}

	asfaq_increment_faq_view_count( $post->ID );

}

add_action( 'wp_ajax_asfaq_count_view', 'asfaq_count_view_ajax' );
add_action( 'wp_ajax_nopriv_asfaq_count_view', 'asfaq_count_view_ajax' );
/**
 * Increment FAQ views with Ajax
 *
 * @since 1.0
 * @return void
 */
function asfaq_count_view_ajax() {

	$faq_id = filter_input( INPUT_POST, 'faq_id', FILTER_SANITIZE_NUMBER_INT );

	// Do not count visits from the agents
	if ( is_user_logged_in() && current_user_can( 'edit_ticket' ) ) {
		echo 0;
		die;
	}

	$count = asfaq_increment_faq_view_count( $faq_id );

	echo $count;
	die;

}

/**
 * Increment the FAQ counts
 *
 * Increment the counters of both the overall FAQ
 * and the FAQ post being viewed.
 *
 * @since 0.1.0
 *
 * @param int $faq_id Post ID of the FAQ to increment
 *
 * @return int Number of views for this FAQ
 */
function asfaq_increment_faq_view_count( $faq_id ) {

	if ( 'faq' !== get_post_type( $faq_id ) ) {
		return 0;
	}

	$count      = (int) get_option( 'as_faq_count', 0 );
	$count_post = (int) get_post_meta( $faq_id, '_as_faq_count', true );

	// Increment both counts
	++ $count;
	++ $count_post;

	update_option( 'as_faq_count', $count );
	update_post_meta( $faq_id, '_as_faq_count', $count_post );

	return $count_post;

}

/**
 * Get the views ratio for a specific FAQ
 *
 * The result is the percentage of views for this FAQ
 * out of the views of all FAQs.
 *
 * @since 0.1.0
 *
 * @param $post_id
 *
 * @return int
 */
function asfaq_get_views_ratio( $post_id ) {

	$count = (int) get_option( 'as_faq_count', 0 );

	if ( 0 === $count ) {
		return 0;
	}

	if ( 'faq' !== get_post_type( $post_id ) ) {
		return 0;
	}

	$count_post = (int) get_post_meta( $post_id, '_as_faq_count', true );

	return $count_post * 100 / $count;

}