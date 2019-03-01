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

add_action( 'plugins_loaded', 'asfaq_do_action', 11 );
/**
 * Run plugin specific actions if something is specified in the GET variables
 *
 * @since 1.0
 * @return void
 */
function asfaq_do_action() {

	if ( isset( $_GET['asfaq_do'] ) ) {

		$action = filter_input( INPUT_GET, 'asfaq_do', FILTER_SANITIZE_STRING );

		if ( ! empty( $action ) ) {

			$args = $_GET;
			unset( $args['asfaq_do'] );

			$args = array_map( 'sanitize_text_field', $args );

			do_action( "asfaq_do_$action", $args );

		}

	}

}

add_action( 'admin_notices', 'asfaq_notify_new_faq' );
/**
 * Display an admin notice when a new FAQ is created during the reply submission process
 *
 * @since 1.0
 * @return void
 */
function asfaq_notify_new_faq() {

	$notice = wpas_get_notification( 'asfaq_new_faq_added', '', 'asfaq' );

	if ( ! empty( $notice ) ) {

		printf( '<div class="%s"><p>%s</p></div>', 'updated asfaq_new_faq_added', $notice );

		wpas_clean_notifications( 'asfaq' );

	}

}

add_action( 'admin_enqueue_scripts', 'asfaq_enqueue_resources_admin' );
/**
 * Load admin resources
 *
 * @since 1.0
 * @return void
 */
function asfaq_enqueue_resources_admin() {

	wp_register_style( 'asfaq-quick-links', AS_FAQ_URL . 'assets/css/admin/faq-quick-links.css', array(), AS_FAQ_VERSION, 'all' );
	wp_register_script( 'asfaq-quick-links', AS_FAQ_URL . 'assets/js/admin/faq-quick-links.js', array( 'jquery' ), AS_FAQ_VERSION, true );

	global $post;


	if ( isset( $post ) && is_object( $post ) && is_a( $post, 'WP_Post' ) && 'ticket' === $post->post_type ) {
		wp_enqueue_script( 'asfaq-quick-links' );
		wp_enqueue_style( 'asfaq-quick-links' );
	}

}

add_action( 'wp_enqueue_scripts', 'asfaq_enqueue_styles' );
/**
 * Register and load addon styles
 *
 * @since 1.0
 * @return void
 */
function asfaq_enqueue_styles() {

	wp_register_style( 'asfaq-main', AS_FAQ_URL . 'assets/css/faq.css', array(), AS_FAQ_VERSION, 'all' );

	if ( ! is_admin() ) {
		wp_enqueue_style( 'asfaq-main' );
	}

}

add_action( 'wp_enqueue_scripts', 'asfaq_enqueue_scripts' );
/**
 * Register and load addon scripts
 *
 * @since 1.0
 * @return void
 */
function asfaq_enqueue_scripts() {

	wp_register_script( 'asfaq-main', AS_FAQ_URL . 'assets/js/faq.js', array( 'jquery' ), AS_FAQ_VERSION, true );
	wp_register_script( 'asfaq-live-search', AS_FAQ_URL . 'assets/js/faq-live-search.js', array( 'jquery' ), AS_FAQ_VERSION, true );
	wp_localize_script( 'asfaq-live-search', 'asfaq', array(
		'ajaxurl'  => admin_url( 'admin-ajax.php' ),
		'settings' => array(
			'selectors'   => asfaq_get_selectors(),
			'delay'       => (int) asfaq_get_option( 'delay', 300 ),
			'chars_min'   => (int) asfaq_get_option( 'chars_min', 3 ),
			'link_target' => asfaq_get_option( 'link_target', '_self' ),
		)
	) );

	if ( ! is_admin() ) {
		wp_enqueue_script( 'asfaq-main' );
		wp_enqueue_script( 'asfaq-live-search' );
	}

}

/**
 * Get the live search form elements selectors
 *
 * @since 1.0
 * @return array
 */
function asfaq_get_selectors() {

	$selectors = asfaq_get_option( 'selectors', '' );
	$selectors = array_filter( explode( ',', $selectors ) );

	return array_map( 'trim', $selectors );

}