<?php
/**
 * @package   Awesome Support CUSTOMFAQ/Settings
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      http://getawesomesupport.com
 * @copyright 2014 Awesome Support
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_filter( 'wpas_plugin_settings', 'ascustomfaq_settings' );
/**
 * Register plugin settings
 *
 * @since 1.0
 *
 * @param array $def Plugin settings
 *
 * @return array
 */
function ascustomfaq_settings( $def ) {

	$settings = array(
		'customFAQgeneral' => array(
			'name'    => __( 'Custom FAQ', 'as-customfaq' ),
			'options' => array(
				array(
					'name'    => __( 'Custom Post Type', 'as-customfaq' ),
					'id'      => 'customfaq_cpt',
					'type'    => 'text',
					'desc'    => __( 'Enter the internal name of the Custom Post Type to use for your FAQs.  You might need to contact your plugin provider or software developer to get this name.', 'as-customfaq' ),
					'default' => 'post'
				),			
			
				array(
					'name'    => __( 'Reply &amp; CUSTOMFAQ Closes', 'as-customfaq' ),
					'id'      => 'reply_customfaq_close',
					'type'    => 'checkbox',
					'desc'    => __( 'Close tickets when replied using the <em>Reply &amp; CUSTOMFAQ</em> button.', 'as-customfaq' ),
					'default' => false
				),
				array(
					'name'    => __( 'Quick CUSTOMFAQ Links Template', 'as-customfaq' ),
					'id'      => 'customfaq_quick_reply_template',
					'type'    => 'editor',
					'desc'    => sprintf( __( 'Reply to send to the client for directing him to the CUSTOMFAQ article. <a %s>Click here</a> to review all available template tags.', 'as-customfaq' ), 'href="#contextual-help-link" onclick="document.getElementById(\'contextual-help-link\').click(); return false;"' ),
					'default' => '<p>Hey {client_name},</p><p>This question has been answered in our CUSTOMFAQ. Please check out the answer here: {customfaq_link}.</p><p>I believe this will help you solve the problem. However, if you need further assistance, feel free to get back to me.</p><p>Cheers,<br>{agent_name}</p>',
				),
				array(
					'name'    => __( 'Rewrite Slug', 'as-customfaq' ),
					'id'      => 'customfaq_slug',
					'type'    => 'text',
					'desc'    => sprintf( __( 'What should the slug be for CUSTOMFAQs? The slug is the part that prefixes the question slug. Example: %s. Please refresh your permalinks if you change this option.', 'as-customfaq' ), '<code>http://domain.com/<strong>question</strong>/my-question</code>' ),
					'default' => 'question'
				),
				
				array(
					'name'    => __( 'Live Search', 'as-customfaq' ),
					'id'      => 'customfaq-live-search-label',
					'type'    => 'heading',
					'desc'    =>  __( 'Thse parameters control how the search function works on the front-end as the user types in characters into the ticket subject field', 'as-customfaq' ),
					'default' => '#wpas_title'
				),				
				
				array(
					'name'    => __( 'Live Search Elements', 'as-customfaq' ),
					'id'      => 'customfaq_selectors',
					'type'    => 'text',
					'desc'    => sprintf( __( 'On which elements should the live search trigger? By default, it is enabled on the ticket submission form title field. You can add more form elements by specifying their selector. If you use multiple selectors, they must be separated by a comma (%s). <a %s>Read more about selectors</a>.', 'as-customfaq' ), '<code>,</code>', 'href="http://www.w3schools.com/jquery/jquery_selectors.asp" target="_blank"' ),
					'default' => '#wpas_title'
				),
				array(
					'name'    => __( 'Delay', 'as-customfaq' ),
					'id'      => 'customfaq_delay',
					'type'    => 'text',
					'desc'    => __( 'Delay (in <code>milliseconds</code>) after which the live search is triggered when the user types something.', 'as-customfaq' ),
					'default' => 300
				),
				array(
					'name'    => __( 'Characters Min.', 'as-customfaq' ),
					'id'      => 'customfaq_chars_min',
					'type'    => 'number',
					'desc'    => __( 'Minimum number of characters required to trigger the live search.', 'as-customfaq' ),
					'default' => 3,
					'max'     => 10,
				),
				array(
					'name'    => __( 'Link Target', 'as-customfaq' ),
					'id'      => 'customfaq_link_target',
					'type'    => 'select',
					'desc'    => __( 'Where do you want links to open?', 'as-customfaq' ),
					'options' => array( '_blank' => esc_html__( 'New window/tab', 'as-customfaq' ), '_self' => esc_html__( 'Same window/tab', 'as-customfaq' ) ),
					'default' => '_self'
				),
				array(
					'name'    => __( 'Sort Results', 'as-customfaq' ),
					'id'      => 'customfaq_sort_results',
					'type'    => 'select',
					'desc'    => __( 'How do you want live search results ot be displayed?', 'as-customfaq' ),
					'options' => array(
						'date_asc'   => esc_html__( 'Date (ascending)', 'as-customfaq' ),
						'date_desc'  => esc_html__( 'Date (descending)', 'as-customfaq' ),
						'title_asc'  => esc_html__( 'Title (ascending)', 'as-customfaq' ),
						'title_desc' => esc_html__( 'Date (descending)', 'as-customfaq' ),
					),
					'default' => 'date_desc'
				),
				array(
					'name'    => __( 'Max. Results', 'as-customfaq' ),
					'id'      => 'customfaq_display_max',
					'type'    => 'number',
					'desc'    => __( 'Maximum number of results to display.', 'as-customfaq' ),
					'default' => 5,
					'max'     => 20,
				),

				array(
					'name'    => __( 'Live Search Results Styles', 'as-customfaq' ),
					'type'    => 'heading',
					'desc'    => __( 'These options control the look of the live search results', 'as-customfaq' ),
				),
				array(
					'name'    => __( 'Section Background Color', 'as-customfaq' ),
					'id'      => 'customfaq-live-search-section-background-color',
					'type'    => 'color',
					'desc'    => __( 'Color of the background on which the live search results are shown', 'as-customfaq' ),
					'default' => '#F5A623'
				),
				array(
					'name'    => __( 'Topic Title Color', 'as-customfaq' ),
					'id'      => 'customfaq-live-search-topic-title-color',
					'type'    => 'color',
					'desc'    => __( 'Color of the topics / results that are shown', 'as-customfaq' ),
					'default' => '#ffffff'
				),								
			)
		),
	);

	return array_merge( $def, $settings );

}