<?php
/**
 * @package   Awesome Support Documentation/Install
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      http://getawesomesupport.com
 * @copyright 2014-2017 Awesome Support
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Activate the addon
 *
 * @since 2.0.1
 * @return void
 */
function asdoc_activate() {

	// Set plugin default options
	asdoc_set_default_options();

}

/**
 * Set default options
 *
 * @since 2.0.1
 * @return void
 */
function asdoc_set_default_options() {

	$options = unserialize( get_option( 'asdoc_options' ) );

	if ( empty( $options ) ) {

		$defaults = array(
			'selectors'            => '#wpas_title',
			'delay'                => 300,
			'chars_min'            => 3,
			'link_target'          => '_self',
			'sort_results'         => 'date_desc',
			'display_max'          => 5,
			'reply_faq_close'      => 0,
			'slug'                 => 'question',
			'quick_reply_template' => '<p>Hey {client_name},</p><p>This question has been answered in our documentation. Please check out the answer here: {doc_link}.</p><p>I believe this will help you solve the problem. However, if you need further assistance, feel free to get back to me.</p><p>Cheers,<br>{agent_name}</p>',
		);

		update_option( 'asdoc_options', serialize( $defaults ) );
	}

}