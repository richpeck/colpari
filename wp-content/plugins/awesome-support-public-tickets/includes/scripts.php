<?php

/**
 * @package   Awesome Support Pubic Tickets Addon Scripts
 * @author    TBC
 */

/* If this file is called directly, abort. */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register all back-end assets
 *
 * @return void
 */

add_action( 'admin_enqueue_scripts', 'as_pt_add_admin_scripts', 5 );

function as_pt_add_admin_scripts() {
	if( ! wpas_is_plugin_page() ) {
		return;
	}
	
	wp_register_script('custom_scripts', WPAS_PT_URL .'assets/js/custom.js');
	wp_enqueue_script('custom_scripts');
	wp_localize_script( 'custom_scripts', 'ascs', array(
    	'public_txt' => __( "Mark as Public", 'as-public-tickets' ),
    	'private_txt' => __( "Mark as Private", 'as-public-tickets' ),
	));

	// Load jquery related scripts...
	wp_register_script('wpas-addon-jquery-ui-js', WPAS_PT_URL .'assets/js/jquery-ui.js');
	wp_enqueue_script('wpas-addon-jquery-ui-js');
}

/**
 * Register all front-end assets
 *
 * @return void
 */

add_action( 'wp_footer', 'as_pt_add_frontend_scripts', 10);

function as_pt_add_frontend_scripts() {

	wp_register_script('wpas-addon-scripts', WPAS_PT_URL .'assets/js/custom.js');
	wp_enqueue_script('wpas-addon-scripts');
	wp_localize_script( 'wpas-addon-scripts', 'ascs', array(
    	'public_txt' => __( "Mark as Public", 'as-public-tickets' ),
    	'private_txt' => __( "Mark as Private", 'as-public-tickets' ),
	));

	wp_register_script('wpas-addon-jquery-ui-js', WPAS_PT_URL .'assets/js/jquery-ui.js');
	wp_enqueue_script('wpas-addon-jquery-ui-js');

	wp_register_style( 'wpas-addon-styles', WPAS_PT_URL .'assets/css/view.css' );
	wp_enqueue_style( 'wpas-addon-styles' );

	wp_register_style( 'wpas-addon-jquery-ui-css', WPAS_PT_URL .'assets/css/jquery-ui.css' );
	wp_enqueue_style( 'wpas-addon-jquery-ui-css' );
}