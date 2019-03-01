<?php
/**
 * @package   Awesome Support FAQ/Settings
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_filter( 'asfaq_plugin_settings', 'asfaq_settings' );
/**
 * Register plugin settings
 *
 * @since 1.0
 *
 * @param array $def Plugin settings
 *
 * @return array
 */
function asfaq_settings( $def ) {

	$settings = array(
		'general' => array(
			'name'    => __( 'General', 'as-faq' ),
			'options' => array(
				array(
					'name'    => __( 'Reply &amp; FAQ Closes', 'as-faq' ),
					'id'      => 'reply_faq_close',
					'type'    => 'checkbox',
					'desc'    => __( 'Close tickets when replied using the <em>Reply &amp; FAQ</em> button.', 'as-faq' ),
					'default' => false
				),
				array(
					'name'    => __( 'Quick FAQ Links Template', 'as-faq' ),
					'id'      => 'quick_reply_template',
					'type'    => 'editor',
					'desc'    => sprintf( __( 'Reply to send to the client for directing him to the FAQ article. <a %s>Click here</a> to review all available template tags.', 'as-faq' ), 'href="#contextual-help-link" onclick="document.getElementById(\'contextual-help-link\').click(); return false;"' ),
					'default' => '<p>Hey {client_name},</p><p>This question has been answered in our FAQ. Please check out the answer here: {faq_link}.</p><p>I believe this will help you solve the problem. However, if you need further assistance, feel free to get back to me.</p><p>Cheers,<br>{agent_name}</p>',
				),
				array(
					'name'    => __( 'Rewrite Slug', 'as-faq' ),
					'id'      => 'slug',
					'type'    => 'text',
					'desc'    => sprintf( __( 'What should the slug be for FAQs? The slug is the part that prefixes the question slug. Example: %s. Please refresh your permalinks if you change this option.', 'as-faq' ), '<code>http://domain.com/<strong>question</strong>/my-question</code>' ),
					'default' => 'question'
				),
			)
		),
		'live_search' => array(
			'name'    => __( 'Live Search', 'as-faq' ),
			'options' => array(
				array(
					'name'    => __( 'Live Search', 'as-faq' ),
					'id'      => 'selectors',
					'type'    => 'text',
					'desc'    => sprintf( __( 'On which elements should the live search trigger? By default, it is enabled on the ticket submission form title field. You can add more form elements by specifying their selector. If you use multiple selectors, they must be separated by a comma (%s). <a %s>Read more about selectors</a>.', 'as-faq' ), '<code>,</code>', 'href="http://www.w3schools.com/jquery/jquery_selectors.asp" target="_blank"' ),
					'default' => '#wpas_title'
				),
				array(
					'name'    => __( 'Delay', 'as-faq' ),
					'id'      => 'delay',
					'type'    => 'text',
					'desc'    => __( 'Delay (in <code>milliseconds</code>) after which the live search is triggered when the user types something.', 'as-faq' ),
					'default' => 300
				),
				array(
					'name'    => __( 'Characters Min.', 'as-faq' ),
					'id'      => 'chars_min',
					'type'    => 'number',
					'desc'    => __( 'Minimum number of characters required to trigger the live search.', 'as-faq' ),
					'default' => 3,
					'max'     => 10,
				),
				array(
					'name'    => __( 'Link Target', 'as-faq' ),
					'id'      => 'link_target',
					'type'    => 'select',
					'desc'    => __( 'Where do you want links to open?', 'as-faq' ),
					'options' => array( '_blank' => esc_html__( 'New window/tab', 'as-faq' ), '_self' => esc_html__( 'Same window/tab', 'as-faq' ) ),
					'default' => '_self'
				),
				array(
					'name'    => __( 'Sort Results', 'as-faq' ),
					'id'      => 'sort_results',
					'type'    => 'select',
					'desc'    => __( 'How do you want live search results ot be displayed?', 'as-faq' ),
					'options' => array(
						'date_asc'   => esc_html__( 'Date (ascending)', 'as-faq' ),
						'date_desc'  => esc_html__( 'Date (descending)', 'as-faq' ),
						'title_asc'  => esc_html__( 'Title (ascending)', 'as-faq' ),
						'title_desc' => esc_html__( 'Date (descending)', 'as-faq' ),
					),
					'default' => 'date_desc'
				),
				array(
					'name'    => __( 'Max. Results', 'as-faq' ),
					'id'      => 'display_max',
					'type'    => 'number',
					'desc'    => __( 'Maximum number of results to display.', 'as-faq' ),
					'default' => 5,
					'max'     => 20,
				),
				
				array(
					'name'    => __( 'Live Search Results Styles', 'as-faq' ),
					'type'    => 'heading',
					'desc'    => __( 'These options control the look of the live search results', 'as-faq' ),
				),
				array(
					'name'    => __( 'Section Background Color', 'as-faq' ),
					'id'      => 'faq-live-search-section-background-color',
					'type'    => 'color',
					'desc'    => __( 'Color of the background on which the live search results are shown', 'as-faq' ),
					'default' => '#64CA92'
				),
				array(
					'name'    => __( 'Topic Title Color', 'as-faq' ),
					'id'      => 'faq-live-search-topic-title-color',
					'type'    => 'color',
					'desc'    => __( 'Color of the topics / results that are shown', 'as-faq' ),
					'default' => '#ffffff'
				),				
			)
		),
	);

	return array_merge( $def, $settings );

}