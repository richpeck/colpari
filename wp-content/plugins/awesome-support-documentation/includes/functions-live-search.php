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

add_action( 'plugins_loaded', 'asdoc_do_action', 11 );
/**
 * Run plugin specific actions if something is specified in the GET variables
 *
 * @since 2.0.2
 * @return void
 */
function asdoc_do_action() {

	if ( isset( $_GET['asdoc_do'] ) ) {

		$action = filter_input( INPUT_GET, 'asdoc_do', FILTER_SANITIZE_STRING );

		if ( ! empty( $action ) ) {

			$args = $_GET;
			unset( $args['asdoc_do'] );

			$args = array_map( 'sanitize_text_field', $args );

			do_action( "asdoc_do_$action", $args );

		}

	}

}

add_action( 'admin_notices', 'asdoc_notify_new_doc' );
/**
 * Display an admin notice when a new documentation is created during the reply submission process
 *
 * @since 2.0.2
 * @return void
 */
function asdoc_notify_new_doc() {

	$notice = wpas_get_notification( 'asdoc_new_oc_added', '', 'asdoc' );

	if ( ! empty( $notice ) ) {

		printf( '<div class="%s"><p>%s</p></div>', 'updated asdoc_new_doc_added', $notice );

		wpas_clean_notifications( 'asdoc' );

	}

}

/**
 * Get the live search form elements selectors
 *
 * @since 2.0.2
 * @return array
 */
function asdoc_get_selectors() {

	$selectors = asdoc_get_option( 'asdoc-selectors', '' );

	$selectors = array_filter( explode( ',', $selectors ) );

	return array_map( 'trim', $selectors );	

}

/**
 * Sorts a list of objects, based on one or more orderby arguments.
 *
 * Serves as back-compat shim for wp_list_sort() in pre-WP 4.7.0 installs.
 *
 * @since 2.0.2
 *
 * @param array        $list          An array of objects to filter.
 * @param string|array $orderby       Optional. Either the field name to order by or an array
 *                                    of multiple orderby fields as $orderby => $order.
 * @param string       $order         Optional. Either 'ASC' or 'DESC'. Only used if $orderby
 *                                    is a string.
 * @param bool         $preserve_keys Optional. Whether to preserve keys. Default false.
 * @return array The sorted array.
 */
function asdoc_list_sort( $list, $orderby = array(), $order = 'ASC', $preserve_keys = false ) {

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

//add_filter( 'archive_template', 'asdoc_filter_category_archive_template' );
/**
 * Filters the Documentation category archive template to use the one bundled with the plugin
 * if one is not already present in the theme.
 *
 * @since 2.0.2
 *
 * @param string $template Archive template file path.
 * @return string (Maybe) filtered DOC category archive template file path.
 */
function asdoc_filter_category_archive_template( $template ) {
	// First check to see if the theme or something else has provided a category archive template.
	if ( false === strpos( $template, 'as-doc-category' ) ) {
		$template = WPAS_DOC_PATH . 'themes/default/doc/taxonomy-as-doc-category.php';
	}

	return $template;
}

//add_filter( 'single_template', 'asdoc_filter_single_faq_template', 100, 3 );
/**
 * Filters the DOC item single template to use the one bundled with the plugin
 * if one is not already present in the theme.
 *
 * @since 2.0.2
 *
 * @param string $template  Path to the template. See locate_template().
 * @param string $type      Filename without extension.
 * @param array  $templates A list of template candidates, in descending order of priority.
 * @return string (Maybe) filtered single FAQ template file path.
 */
function asdoc_filter_single_doc_template( $template, $type, $templates ) {
	if ( in_array( 'single-doc.php', $templates ) ) {
		$template = WPAS_DOC_PATH . 'themes/default/doc/single-faq.php';
	}

	return $template;
}
