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

add_action( 'plugins_loaded', 'ascustomfaq_do_action', 11 );
/**
 * Run plugin specific actions if something is specified in the GET variables
 *
 * @since 1.0
 * @return void
 */
function ascustomfaq_do_action() {

	if ( isset( $_GET['ascustomfaq_do'] ) ) {

		$action = filter_input( INPUT_GET, 'ascustomfaq_do', FILTER_SANITIZE_STRING );

		if ( ! empty( $action ) ) {

			$args = $_GET;
			unset( $args['ascustomfaq_do'] );

			$args = array_map( 'sanitize_text_field', $args );

			do_action( "ascustomfaq_do_$action", $args );

		}

	}

}

add_action( 'admin_notices', 'ascustomfaq_notify_new_customfaq' );
/**
 * Display an admin notice when a new CUSTOMFAQ is created during the reply submission process
 *
 * @since 1.0
 * @return void
 */
function ascustomfaq_notify_new_customfaq() {

	$notice = wpas_get_notification( 'ascustomfaq_new_customfaq_added', '', 'ascustomfaq' );

	if ( ! empty( $notice ) ) {

		printf( '<div class="%s"><p>%s</p></div>', 'updated ascustomfaq_new_customfaq_added', $notice );

		wpas_clean_notifications( 'ascustomfaq' );

	}

}

add_action( 'admin_enqueue_scripts', 'ascustomfaq_enqueue_resources_admin' );
/**
 * Load admin resources
 *
 * @since 1.0
 * @return void
 */
function ascustomfaq_enqueue_resources_admin() {

	wp_register_style( 'ascustomfaq-quick-links', AS_CUSTOMFAQ_URL . 'assets/css/admin/customfaq-quick-links.css', array(), AS_CUSTOMFAQ_VERSION, 'all' );
	wp_register_script( 'ascustomfaq-quick-links', AS_CUSTOMFAQ_URL . 'assets/js/admin/customfaq-quick-links.js', array( 'jquery' ), AS_CUSTOMFAQ_VERSION, true );

	global $post;


	if ( isset( $post ) && is_object( $post ) && is_a( $post, 'WP_Post' ) && 'ticket' === $post->post_type ) {
		wp_enqueue_script( 'ascustomfaq-quick-links' );
		wp_enqueue_style( 'ascustomfaq-quick-links' );
	}

}

add_action( 'wp_enqueue_scripts', 'ascustomfaq_enqueue_styles' );
/**
 * Register and load addon styles
 *
 * @since 1.0
 * @return void
 */
function ascustomfaq_enqueue_styles() {

	wp_register_style( 'ascustomfaq-main', AS_CUSTOMFAQ_URL . 'assets/css/customfaq.css', array(), AS_CUSTOMFAQ_VERSION, 'all' );

	if ( ! is_admin() ) {
		wp_enqueue_style( 'ascustomfaq-main' );
	}

}

add_action( 'wp_enqueue_scripts', 'ascustomfaq_enqueue_scripts' );
/**
 * Register and load addon scripts
 *
 * @since 1.0
 * @return void
 */
function ascustomfaq_enqueue_scripts() {

	wp_register_script( 'ascustomfaq-main', AS_CUSTOMFAQ_URL . 'assets/js/customfaq.js', array( 'jquery' ), AS_CUSTOMFAQ_VERSION, true );
	wp_register_script( 'ascustomfaq-live-search', AS_CUSTOMFAQ_URL . 'assets/js/customfaq-live-search.js', array( 'jquery' ), AS_CUSTOMFAQ_VERSION, true );
	wp_localize_script( 'ascustomfaq-live-search', 'ascustomfaq', array(
		'ajaxurl'  => admin_url( 'admin-ajax.php' ),
		'settings' => array(
			'selectors'   => ascustomfaq_get_selectors(),
			'delay'       => (int) ascustomfaq_get_option( 'customfaq_delay', 300 ),
			'chars_min'   => (int) ascustomfaq_get_option( 'customfaq_chars_min', 3 ),
			'link_target' => ascustomfaq_get_option( 'customfaq_link_target', '_self' ),
		)
	) );

	if ( ! is_admin() ) {
		wp_enqueue_script( 'ascustomfaq-main' );
		wp_enqueue_script( 'ascustomfaq-live-search' );
	}

}

/**
 * Get the live search form elements selectors
 *
 * @since 1.0
 * @return array
 */
function ascustomfaq_get_selectors() {

	$selectors = ascustomfaq_get_option( 'customfaq_selectors', '' );
	$selectors = array_filter( explode( ',', $selectors ) );

	return array_map( 'trim', $selectors );

}

/**
 * Sorts a list of objects, based on one or more orderby arguments.
 *
 * Serves as back-compat shim for wp_list_sort() in pre-WP 4.7.0 installs.
 *
 * @since 1.1
 *
 * @param array        $list          An array of objects to filter.
 * @param string|array $orderby       Optional. Either the field name to order by or an array
 *                                    of multiple orderby fields as $orderby => $order.
 * @param string       $order         Optional. Either 'ASC' or 'DESC'. Only used if $orderby
 *                                    is a string.
 * @param bool         $preserve_keys Optional. Whether to preserve keys. Default false.
 * @return array The sorted array.
 */
function ascustomfaq_list_sort( $list, $orderby = array(), $order = 'ASC', $preserve_keys = false ) {

	if ( function_exists( 'wp_list_sort' ) ) {

		$list = wp_list_sort( $list, $orderby, $order, true );

	} else {

		if ( empty( $orderby ) ) {
			return $list;
		}

		if ( is_string( $orderby ) ) {
			$orderby = array( $orderby => $order );
		}

		foreach ( $orderby as $field => $direction ) {
			$orderby[ $field ] = 'DESC' === strtoupper( $direction ) ? 'DESC' : 'ASC';
		}

		if ( true === $preserve_keys ) {

			uasort( $list, function( $a, $b ) use ( $orderby ) {
				$a = $a->to_array();
				$b = $b->to_array();

				foreach ( $orderby as $field => $direction ) {
					if ( ! isset( $a[ $field ] ) || ! isset( $b[ $field ] ) ) {
						continue;
					}

					if ( $a[ $field ] == $b[ $field ] ) {
						continue;
					}

					$results = 'DESC' === $direction ? array( 1, -1 ) : array( -1, 1 );

					if ( is_numeric( $a[ $field ] ) && is_numeric( $b[ $field ] ) ) {
						return ( $a[ $field ] < $b[ $field ] ) ? $results[0] : $results[1];
					}

					return 0 > strcmp( $a[ $field ], $b[ $field ] ) ? $results[0] : $results[1];
				}

				return 0;

			} );

		} else {

			/*
			 * This is basically verbatim code duplication with the uasort() closure used above, but
			 * by using a closure. we're able to inherit $orderby via the use keyword without having
			 * to create an even greater abstraction later, e.g. a list sorting class with properties
			 * for back-compat with the core WP_List_Util class.
			 */
			usort( $list, function( $a, $b ) use ( $orderby ) {
				$a = $a->to_array();
				$b = $b->to_array();

				foreach ( $orderby as $field => $direction ) {
					if ( ! isset( $a[ $field ] ) || ! isset( $b[ $field ] ) ) {
						continue;
					}

					if ( $a[ $field ] == $b[ $field ] ) {
						continue;
					}

					$results = 'DESC' === $direction ? array( 1, -1 ) : array( -1, 1 );

					if ( is_numeric( $a[ $field ] ) && is_numeric( $b[ $field ] ) ) {
						return ( $a[ $field ] < $b[ $field ] ) ? $results[0] : $results[1];
					}

					return 0 > strcmp( $a[ $field ], $b[ $field ] ) ? $results[0] : $results[1];
				}

				return 0;

			} );

		}

	}

	return $list;

}


