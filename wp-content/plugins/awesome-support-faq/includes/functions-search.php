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

/**
 * Search FAQs by term
 *
 * @since 0.1.0
 *
 * @param string $term
 *
 * @return array
 */
function asfaq_search_faqs( $term = '' ) {

	if ( empty( $term ) ) {
		return array();
	}

	$args = array(
		'posts_per_page' => asfaq_get_option( 'display_max', 5 ),
		's'              => sanitize_text_field( $term ),
	);

	// Add sorting options
	$sort = asfaq_get_option( 'sort_results', 'date_desc' );

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

	return asfaq_get_faqs( $args );

}

add_action( 'wp_ajax_asfaq_search_faq', 'asfaq_search_faqs_ajax' );
add_action( 'wp_ajax_nopriv_asfaq_search_faq', 'asfaq_search_faqs_ajax' );
/**
 * Search the FAQs by term via Ajax
 *
 * @since 0.1.0
 *
 * @param string $term Term to search
 *
 * @return array|string Array of WP_Post objects or empty string if no results
 */
function asfaq_search_faqs_ajax( $term = '' ) {

	if ( empty( $term ) ) {
		if ( isset( $_POST['asfaq_term'] ) ) {
			$term = filter_input( INPUT_POST, 'asfaq_term', FILTER_SANITIZE_STRING );
		}
	}

	if ( empty( $term ) ) {
		echo '';
		die();
	}

	$term   = sanitize_text_field( $term );
	$result = asfaq_search_faqs( $term );
	$result = asfaq_clean_ajax_results( $result );

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
function asfaq_clean_ajax_results( $results ) {

	if ( empty( $results ) ) {
		return array();
	}

	$clean = array();

	foreach ( $results as $result ) {
		$clean[] = array(
			'title'   => apply_filters( 'the_title', __( 'FAQ: ', 'as-faq' ) . $result->post_title ),
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
 add_action( 'wpas_submission_form_inside_before_subject', 'asfaq_set_live_search_results_markup' );
function asfaq_set_live_search_results_markup() {
	
	// opening stylesheet marker
	$markup = '<style type="text/css">' ;
	
	// General area div markup 
	$markup .= '.asfaq-results {' ;
	$markup .= 'color:' . wpas_get_option( 'faq-live-search-topic-title-color', '#ffffff' ) . ';' ;
	$markup .= 'background-color:' . wpas_get_option( 'faq-live-search-section-background-color', '#64CA92' ) . ';' ;
	$markup .= 'border: 1px solid' . wpas_get_option( 'faq-live-search-topic-title-color', '#ffffff' ) . ';' ;
	$markup .= '}';
	
	// Links markup
	$markup .= '.asfaq-results a {' ;
	$markup .= 'color:' . wpas_get_option( 'faq-live-search-topic-title-color', '#ffffff' ) . ';' ;
	$markup .= 'border: 1px solid' . wpas_get_option( 'faq-live-search-topic-title-color', '#ffffff' ) . ';' ;
	$markup .= '}';
	
	// close stylesheet marker
	$markup .= '</style>';
	
	echo $markup ;
	
}

/**
 * Get the FAQ search field markup
 *
 * @since 1.0.5
 * @return string
 */
function asfaq_get_search_field() {
	return '<div id="asfaq_sc_search"><form id="asfaq_sc_search_form" action="" method="post"><input id="asfaq_sc_search_input" type="text" value="" placeholder="' . esc_html__( 'Search the FAQs...', 'as-faq' ) . '"><div id="asfaq_sc_search_clear">×</div></form><div id="asfaq_sc_search_count"></div></div>';
}