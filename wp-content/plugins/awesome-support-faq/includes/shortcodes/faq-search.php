<?php
/**
 * @package   Awesome Support FAQ/Shortcode/Search
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_shortcode( 'faq-search', 'asfaq_shortcode_search' );
/**
 * Register the FAQ search shortcode
 *
 * @since 1.0.5
 *
 * @return string
 */
function asfaq_shortcode_search() {
	return asfaq_get_search_field();
}