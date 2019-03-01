<?php
/**
 * @package   Awesome Support FAQ/Install
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
 * Activate the addon
 *
 * @since 1.0.1
 * @return void
 */
function asfaq_activate() {

	// Set plugin default options
	asfaq_set_default_options();

}

/**
 * Set default options
 *
 * @since 1.0.1
 * @return void
 */
function asfaq_set_default_options() {
	
	/*
	$defaults = array(
		'selectors'            => '#wpas_title',
		'delay'                => 300,
		'chars_min'            => 3,
		'link_target'          => '_self',
		'sort_results'         => 'date_desc',
		'display_max'          => 5,
		'reply_faq_close'      => 0,
		'slug'                 => 'question',
		'quick_reply_template' => '<p>Hey {client_name},</p><p>This question has been answered in our FAQ. Please check out the answer here: {faq_link}.</p><p>I believe this will help you solve the problem. However, if you need further assistance, feel free to get back to me.</p><p>Cheers,<br>{agent_name}</p>',
	);
	*/
	
	// Get existing titan options....
	$existingoptions = maybe_unserialize( get_option( 'asfaq_options' ) );

	// Add new options to existing options array	
	if ( empty( $existingoptions['selectors'] ) ) {
		$existingoptions['selectors'] = '#wpas_title';
	}
	
	if ( empty( $existingoptions['delay'] ) ) {
		$existingoptions['delay'] = 300;
	}
	
	if ( empty( $existingoptions['chars_min'] ) ) {
		$existingoptions['chars_min'] = 3;
	}
	
	if ( empty( $existingoptions['link_target'] ) ) {
		$existingoptions['link_target'] = '_self';
	}

	if ( empty( $existingoptions['sort_results'] ) ) {
		$existingoptions['sort_results'] = 'date_desc';
	}
	
	if ( empty( $existingoptions['display_max'] ) ) {
		$existingoptions['display_max'] = 5;
	}
	
	if ( empty( $existingoptions['customfaq_close'] ) ) {
		$existingoptions['customfaq_close'] = 0;
	}
	
	if ( empty( $existingoptions['slug'] ) ) {
		$existingoptions['slug'] = 'question';
	}
	
	if ( empty( $existingoptions['quick_reply_template'] ) ) {
		$existingoptions['quick_reply_template'] = '<p>Hey {client_name},</p><p>This question has been answered in our FAQ. Please check out the answer here: {faq_link}.</p><p>I believe this will help you solve the problem. However, if you need further assistance, feel free to get back to me.</p><p>Cheers,<br>{agent_name}</p>';
	}	
	
	// Rewrite new and existing titan options for FAQ module...
	update_option( 'asfaq_options', serialize( $existingoptions ) );

}