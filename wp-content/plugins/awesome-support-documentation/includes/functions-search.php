<?php
/**
 * @package   Awesome Support Documentation/Search
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
 * Search DOCs by term
 *
 * @since 2.0.2
 *
 * @param string $term
 *
 * @return array
 */
function asdoc_search_docs( $term = '' ) {

	if ( empty( $term ) ) {
		return array();
	}

	$args = array(
		'posts_per_page' => asdoc_get_option( 'display_max', 5 ),
		's'              => sanitize_text_field( $term ),
	);

	// Add sorting options
	$sort = asdoc_get_option( 'sort_results', 'date_desc' );

	switch ( $sort ) {

		case 'date_desc':
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
			break;

		case 'date_asc':
			$args['orderby'] = 'date';
			$args['order']   = 'ASC';
			break;

		case 'title_desc':
			$args['orderby'] = 'title';
			$args['order']   = 'DESC';
			break;

		case 'title_asc':
			$args['orderby'] = 'title';
			$args['order']   = 'ASC';
			break;

	}

	return asdoc_get_docs( $args );

}

add_action( 'wp_ajax_asdoc_search_doc', 'asdoc_search_docs_ajax' );
add_action( 'wp_ajax_nopriv_asdoc_search_doc', 'asdoc_search_docs_ajax' );
/**
 * Search the DOCs by term via Ajax
 *
 * @since 2.0.2
 *
 * @param string $term Term to search
 *
 * @return array|string Array of WP_Post objects or empty string if no results
 */
function asdoc_search_docs_ajax( $term = '' ) {

	if ( empty( $term ) ) {
		if ( isset( $_POST['asdoc_term'] ) ) {
			$term = filter_input( INPUT_POST, 'asdoc_term', FILTER_SANITIZE_STRING );
		}
	}

	if ( empty( $term ) ) {
		echo '';
		die();
	}

	$term   = sanitize_text_field( $term );
	$result = asdoc_search_docs( $term );
	$result = asdoc_clean_ajax_results( $result );
	
	echo json_encode( $result );
	die();

}

/**
 * Clean the results after Ajax request
 *
 * Only return the useful data and avoid giving all the post data away
 * in the Ajax request response.
 *
 * @since 0.1.0
 *
 * @param array $results
 *
 * @return array
 */
function asdoc_clean_ajax_results( $results ) {

	if ( empty( $results ) ) {
		return array();
	}

	$clean = array();
	
	// Add items
	foreach ( $results as $result ) {
		$clean[] = array(
			'title'   => apply_filters( 'the_title', __( 'DOC: ', 'wpas-documentation' ) . $result->post_title ),
			'content' => ! empty( $result->post_excerpt ) ? apply_filters( 'get_the_excerpt', $result->post_excerpt ) : '',
			'link'    => get_permalink( $result->ID )
		);
	}
	
	return $clean;

}

/**
 * Set the search results style markup
 *
 * Action Hook: wpas_submission_form_inside_before_subject
 *
 * @since 3.0.1
 *
 * @return void
 */
 add_action( 'wpas_submission_form_inside_before_subject', 'asdoc_set_live_search_results_markup' );
function asdoc_set_live_search_results_markup() {
	
	// opening stylesheet marker
	$markup = '<style type="text/css">' ;
	
	// General area div markup 
	$markup .= '.asdoc-results {' ;
	$markup .= 'color:' . asdoc_get_option( 'asdoc-live-search-topic-title-color', '#ffffff' ) . ';' ;
	$markup .= 'background-color:' . asdoc_get_option( 'asdoc-live-search-section-background-color', '#007cc4' ) . ';' ;
	$markup .= 'border: 1px solid' . asdoc_get_option( 'asdoc-live-search-topic-title-color', '#ffffff' ) . ';' ;
	$markup .= '}';
	
	// Links markup
	$markup .= '.asdoc-results a {' ;
	$markup .= 'color:' . asdoc_get_option( 'asdoc-live-search-topic-title-color', '#ffffff' ) . ';' ;
	$markup .= 'border: 1px solid' . asdoc_get_option( 'asdoc-live-search-topic-title-color', '#ffffff' ) . ';' ;
	$markup .= '}';
	
	// close stylesheet marker
	$markup .= '</style>';
	
	echo $markup ;
	
}

/**
 * Get the DOC search field markup
 *
 * @since 1.0.5
 * @return string
 */
function asdoc_get_search_field() {
	return '<div id="asdoc_sc_search"><form id="asdoc_sc_search_form" action="" method="post"><input id="asdoc_sc_search_input" type="text" value="" placeholder="' . esc_html__( 'Search the documentation...', 'wpas-documentation' ) . '"><div id="asdoc_sc_search_clear">×</div></form><div id="asdoc_sc_search_count"></div></div>';
}