<?php
/**
 * @package   Awesome Support Documentation
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      http://getawesomesupport.com
 * @copyright 2014-2017 Awesome Support
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Insert a new DOCUMENTATION in the database
 *
 * @since 2.0.1
 *
 * @param array $data DOCUMENTATION post data
 *
 * @return bool|int
 */
function asdoc_insert_doc( $data ) {

	$defaults = array(
		'post_type'    => 'documentation',
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
 * Get one specific DOCUMENTATION Item
 *
 * @since 2.0.1
 *
 * @param int $doc_id Post ID of the DOCUMENTATION item to retrieve
 *
 * @return array
 */
function asdoc_get_doc( $doc_id ) {
	return asdoc_get_docs( array( 'posts_per_page' => 1, 'p' => (int) $faq_id ) );
}

/**
 * Get DOCs
 *
 * Helper function to get the DOCUMENTATION posts.
 *
 * @since 1.0
 *
 * @param array $args DOCs arguments (see WP_Query)
 *
 * @return array
 */
function asdoc_get_docs( $args ) {

	$defaults = array(
		'post_type'              => 'documentation',
		'post_status'            => 'publish',
		'posts_per_page'         => 20,
		'no_found_rows'          => true,
		'cache_results'          => false,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
	);

	/**
	 * Filters arguments for the current asdoc_get_docs() call.
	 *
	 * @since 2.0.1
	 *
	 * @param array $args Arguments used in asdpc_get_docss().
	 */
	$args  = apply_filters( 'asdoc_get_docs_args', wp_parse_args( $args, $defaults ) );
	$query = new WP_Query( $args );

	if ( empty( $query->posts ) ) {
		return array();
	}

	return $query->posts;

}

/**
 * Retrieves a DOCUMENTATION template from the AS template hierarchy.
 *
 * Simply a wrapper for wpas_get_template() that adds the 'asdoc' prefix.
 *
 * @since 2.0.1
 *
 * @param string $name Name of the template to include.
 * @param array  $args Variables to pass to the template.
 *
 * @return boolean True if a template is loaded, false otherwise
 */
function asdoc_get_template( $name, $args = array() ) {
	return wpas_get_template( "asdoc-{$name}", $args );
}
