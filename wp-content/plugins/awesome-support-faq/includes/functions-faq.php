<?php
/**
 * @package   Awesome Support FAQ
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Insert a new FAQ in the database
 *
 * @since 1.0
 *
 * @param array $data FAQ post data
 *
 * @return bool|int
 */
function asfaq_insert_faq( $data ) {

	$defaults = array(
		'post_type'    => 'faq',
		'post_title'   => '',
		'post_content' => '',
		'post_status'  => 'publish',
		'post_author'  => get_current_user_id(),
	);

	$data = wp_parse_args( $data, $defaults );

	if ( empty( $data['post_title'] ) || empty( $data['post_title'] ) ) {
		return false;
	}

	return wp_insert_post( $data );

}

/**
 * Get one specific FAQ
 *
 * @since 1.0
 *
 * @param int $faq_id Post ID of the FAQ to retrieve
 *
 * @return array
 */
function asfaq_get_faq( $faq_id ) {
	return asfaq_get_faqs( array( 'posts_per_page' => 1, 'p' => (int) $faq_id ) );
}

/**
 * Get FAQs
 *
 * Helper function to get the FAQs posts.
 *
 * @since 1.0
 *
 * @param array $args FAQs arguments (see WP_Query)
 *
 * @return array
 */
function asfaq_get_faqs( $args ) {

	$defaults = array(
		'post_type'              => 'faq',
		'post_status'            => 'publish',
		'posts_per_page'         => 20,
		'no_found_rows'          => true,
		'cache_results'          => false,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
	);

	$args  = apply_filters( 'asfaq_get_faqs_args', wp_parse_args( $args, $defaults ) );
	$query = new WP_Query( $args );

	if ( empty( $query->posts ) ) {
		return array();
	}

	return $query->posts;

}