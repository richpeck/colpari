<?php
/**
 * @package   Awesome Support CUSTOMFAQ/Install
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
 * Activate the addon
 *
 * @since 1.0.0
 * @return void
 */
function ascustomfaq_activate() {

	// Set plugin default options
	ascustomfaq_set_default_options();

}

/**
 * Set default options
 *
 * @since 1.0.0
 * @return void
 */
function ascustomfaq_set_default_options() {

/*
	$defaults = array(
		'customfaq_cpt'					 => 'post',
		'customfaq_selectors'            => '#wpas_title',
		'customfaq_delay'                => 300,
		'customfaq_chars_min'            => 3,
		'customfaq_link_target'          => '_self',
		'customfaq_sort_results'         => 'date_desc',
		'customfaq_display_max'          => 5,
		'reply_customfaq_close'      	 => 0,
		'customfaq_slug'                 => 'question',
		'customfaq_quick_reply_template' => '<p>Hey {client_name},</p><p>This question has been answered in our CUSTOMFAQ. Please check out the answer here: {customfaq_link}.</p><p>I believe this will help you solve the problem. However, if you need further assistance, feel free to get back to me.</p><p>Cheers,<br>{agent_name}</p>',
	);
*/

	// Get existing titan options....
	$existingoptions = maybe_unserialize( get_option( 'wpas_options' ) );	


	// Add new options to existing options array
	if ( empty( wpas_get_option( 'customfaq_cpt' ) ) ) {
		$existingoptions['customfaq_cpt'] = 'post';
	}
	
	if ( empty( wpas_get_option( 'customfaq_selectors' ) ) ) {
		$existingoptions['customfaq_selectors'] = '#wpas_title';
	}
	
	if ( empty( wpas_get_option( 'customfaq_delay' ) ) ) {
		$existingoptions['customfaq_delay'] = 300;
	}
	
	if ( empty( wpas_get_option( 'customfaq_chars_min' ) ) ) {
		$existingoptions['customfaq_chars_min'] = 3;
	}
	
	if ( empty( wpas_get_option( 'customfaq_link_target' ) ) ) {
		$existingoptions['customfaq_link_target'] = '_self';
	}

	if ( empty( wpas_get_option( 'customfaq_sort_results' ) ) ) {
		$existingoptions['customfaq_sort_results'] = 'date_desc';
	}
	
	if ( empty( wpas_get_option( 'customfaq_display_max' ) ) ) {
		$existingoptions['customfaq_display_max'] = 5;
	}
	
	if ( empty( wpas_get_option( 'customfaq_customfaq_close' ) ) ) {
		$existingoptions['customfaq_customfaq_close'] = 0;
	}
	
	if ( empty( wpas_get_option( 'customfaq_slug' ) ) ) {
		$existingoptions['customfaq_slug'] = 'question';
	}
	
	if ( empty( wpas_get_option( 'customfaq_quick_reply_template' ) ) ) {
		$existingoptions['customfaq_quick_reply_template'] = '<p>Hey {client_name},</p><p>This question has been answered in our CUSTOMFAQ. Please check out the answer here: {customfaq_link}.</p><p>I believe this will help you solve the problem. However, if you need further assistance, feel free to get back to me.</p><p>Cheers,<br>{agent_name}</p>';
	}	
		
	// Rewrite new and existing titan options...
	update_option( 'wpas_options', serialize( $existingoptions ) );		

}