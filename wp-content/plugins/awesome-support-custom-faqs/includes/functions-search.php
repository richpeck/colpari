<?php
/**
 * @package   Awesome Support CUSTOMFAQ/Search
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
 * Search CUSTOMFAQs by term
 *
 * @since 1.0.0
 *
 * @param string $term
 *
 * @return array
 */
function ascustomfaq_search_customfaqs( $term = '' ) {

	if ( empty( $term ) ) {
		return array();
	}

	$args = array(
		'posts_per_page' => ascustomfaq_get_option( 'customfaq_display_max', 5 ),
		's'              => sanitize_text_field( $term ),
	);

	// Add sorting options
	$sort = ascustomfaq_get_option( 'customfaq_sort_results', 'date_desc' );

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

	return ascustomfaq_get_customfaqs( $args );

}

add_action( 'wp_ajax_ascustomfaq_search_customfaq', 'ascustomfaq_search_customfaqs_ajax' );
add_action( 'wp_ajax_nopriv_ascustomfaq_search_customfaq', 'ascustomfaq_search_customfaqs_ajax' );
/**
 * Search the CUSTOMFAQs by term via Ajax
 *
 * @since 1.0.0
 *
 * @param string $term Term to search
 *
 * @return array|string Array of WP_Post objects or empty string if no results
 */
function ascustomfaq_search_customfaqs_ajax( $term = '' ) {

	if ( empty( $term ) ) {
		if ( isset( $_POST['ascustomfaq_term'] ) ) {
			$term = filter_input( INPUT_POST, 'ascustomfaq_term', FILTER_SANITIZE_STRING );
		}
	}

	if ( empty( $term ) ) {
		echo '';
		die();
	}

	$term   = sanitize_text_field( $term );
	$result = ascustomfaq_search_customfaqs( $term );
	$result = ascustomfaq_clean_ajax_results( $result );

	echo json_encode( $result );
	die();

}

/**
 * Clean the results after Ajax request
 *
 * Only return the useful data and avoid giving all the post data away
 * in the Ajax request response.
 *
 * @since 1.0.0
 *
 * @param array $results
 *
 * @return array
 */
function ascustomfaq_clean_ajax_results( $results ) {

	if ( empty( $results ) ) {
		return array();
	}

	$clean = array();

	foreach ( $results as $result ) {
		$clean[] = array(
			'title'   => apply_filters( 'the_title',  __( 'FAQ: ', 'as-customfaq' ) . $result->post_title ),
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
 add_action( 'wpas_submission_form_inside_before_subject', 'ascustomfaq_set_live_search_results_markup' );
function ascustomfaq_set_live_search_results_markup() {
	
	// opening stylesheet marker
	$markup = '<style type="text/css">' ;
	
	// General area div markup 
	$markup .= '.ascustomfaq-results {' ;
	$markup .= 'color:' . wpas_get_option( 'customfaq-live-search-topic-title-color', '#ffffff' ) . ';' ;
	$markup .= 'background-color:' . wpas_get_option( 'customfaq-live-search-section-background-color', '#F5A623' ) . ';' ;
	$markup .= 'border: 1px solid' . wpas_get_option( 'customfaq-live-search-topic-title-color', '#ffffff' ) . ';' ;
	$markup .= '}';
	
	// Links markup
	$markup .= '.ascustomfaq-results a {' ;
	$markup .= 'color:' . wpas_get_option( 'customfaq-live-search-topic-title-color', '#ffffff' ) . ';' ;
	$markup .= 'border: 1px solid' . wpas_get_option( 'customfaq-live-search-topic-title-color', '#ffffff' ) . ';' ;
	$markup .= '}';
	
	// close stylesheet marker
	$markup .= '</style>';
	
	echo $markup ;
	
}

/**
 * Get the CUSTOMFAQ search field markup
 *
 * @since 1.0.5
 * @return string
 */
function ascustomfaq_get_search_field() {
	return '<div id="ascustomfaq_sc_search"><form id="ascustomfaq_sc_search_form" action="" method="post"><input id="ascustomfaq_sc_search_input" type="text" value="" placeholder="' . esc_html__( 'Search the CUSTOMFAQs...', 'as-customfaq' ) . '"><div id="ascustomfaq_sc_search_clear">×</div></form><div id="ascustomfaq_sc_search_count"></div></div>';
}