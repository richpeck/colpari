<?php
/**
 * @package   Awesome Support FAQ/Shortcode/FAQ
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_shortcode( 'faq', 'asfaq_shortcode_faq' );
/**
 * Register the FAQ shortcode
 *
 * @since 1.0
 *
 * @param array $atts Shortcode attributes
 *
 * @return string
 */
function asfaq_shortcode_faq( $atts ) {

	$atts = shortcode_atts( array(
		'limit'         => 50,
		'before_title'  => '<h3 class="%1$s">',
		'after_title'   => '</h3>',
		'before_answer' => '<div class="%1$s">',
		'after_answer'  => '</div>',
		'cat'           => '',
		'search'        => false,
	), $atts, 'faq' );

	$args = array( 'posts_per_page' => (int) $atts['limit'] );

	// Add the taxonomy terms if any
	if ( ! empty( $atts['cat'] ) ) {

		$cats              = array_filter( explode( ',', $atts['cat'] ) );
		$args['tax_query'] = array(
				array(
						'taxonomy' => 'as-faq-category',
						'field'    => 'term_id',
						'terms'    => $cats,
						'operator' => 'IN'
				)
		);

	}

	$faqs = asfaq_get_faqs( $args );

	if ( empty( $faqs ) ) {
		return '';
	}

	$output = '';

	if ( $atts['search'] ) {
		$output .= '<div id="asfaq_sc_search"><form id="asfaq_sc_search_form" action="" method="post"><input id="asfaq_sc_search_input" type="text" value="" placeholder="' . esc_html__( 'Search the FAQs...', 'as-faq' ) . '"><div id="asfaq_sc_search_clear">×</div></form><div id="asfaq_sc_search_count"></div></div>';
	}

	foreach ( $faqs as $faq ) {

		if ( ! is_object( $faq ) || ! is_a( $faq, 'WP_Post' ) ) {
			continue;
		}

		$title   = apply_filters( 'the_title', $faq->post_title );
		$content = apply_filters( 'the_content', $faq->post_content );

		// Process the wrappers
		$before        = sprintf( '<div class="%1$s" data-id="%2$s">', "asfaq_item asfaq_item_$faq->ID", $faq->ID );
		$after         = '</div>';
		$before_title  = sprintf( $atts['before_title'], "asfaq_title" );
		$before_answer = sprintf( $atts['before_answer'], 'asfaq_answer' );

		$output .= $before . $before_title . $title . $atts['after_title'] . $before_answer . $content . $atts['after_answer'] . $after;

	}

	// Wrap the output
	$output = "<div class='asfaq-shortcode-wrapper'>$output</div>";

	return $output;

}