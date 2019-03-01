<?php
/**
 * @package   Awesome Support CUSTOMFAQ
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      http://getawesomesupport.com
 * @copyright 2014 Awesome Support
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Insert a new CUSTOMFAQ in the database
 *
 * @since 1.0
 *
 * @param array $data CUSTOMFAQ post data
 *
 * @return bool|int
 */
function ascustomfaq_insert_customfaq( $data ) {

	$defaults = array(
		'post_type'    => ascustomfaq_get_cptname(),
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
 * Get one specific CUSTOMFAQ
 *
 * @since 1.0
 *
 * @param int $customfaq_id Post ID of the CUSTOMFAQ to retrieve
 *
 * @return array
 */
function ascustomfaq_get_customfaq( $customfaq_id ) {
	return ascustomfaq_get_customfaqs( array( 'posts_per_page' => 1, 'p' => (int) $customfaq_id ) );
}

/**
 * Get CUSTOMFAQs
 *
 * Helper function to get the CUSTOMFAQs posts.
 *
 * @since 1.0
 *
 * @param array $args CUSTOMFAQs arguments (see WP_Query)
 *
 * @return array
 */
function ascustomfaq_get_customfaqs( $args ) {

	$defaults = array(
		'post_type'              => ascustomfaq_get_cptname(),
		'post_status'            => 'publish',
		'posts_per_page'         => 20,
		'no_found_rows'          => true,
		'cache_results'          => false,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
	);

	/**
	 * Filters arguments for the current ascustomfaq_get_customfaqs() call.
	 *
	 * @since 1.0
	 *
	 * @param array $args Arguments used in ascustomfaq_get_customfaqs().
	 */
	$args  = apply_filters( 'ascustomfaq_get_customfaqs_args', wp_parse_args( $args, $defaults ) );
	$query = new WP_Query( $args );

	if ( empty( $query->posts ) ) {
		return array();
	}

	return $query->posts;

}

 /**
 * Retrieves the name of the Custom FAQ Post Type From Settings
 *
 *
 * @since 1.0.0
 *
 *
 * @return string
 */
function ascustomfaq_get_cptname() {
	$optionvalue = wpas_get_option( 'customfaq_cpt' ) ;
	return isset( $optionvalue ) ? $optionvalue : 'post';
}
 
 /**
 * Retrieve plugin option
 *
 * @since 1.0
 *
 * @param string $option  ID of the option to lookup
 * @param mixed  $default Value to return in case the option doesn't exist
 *
 * @return mixed
 */
function ascustomfaq_get_option( $option, $default = '' ) {

	$asoptionvalue = wpas_get_option( $option ) ;
	return isset( $asoptionvalue ) ? $asoptionvalue : $default;

}

