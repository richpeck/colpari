<?php
/**
 * @package   Awesome Support Documentation/Settings
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      http://getawesomesupport.com
 * @copyright 2014-2017 Awesome Support
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_filter( 'asdoc_plugin_settings', 'asdoc_settings' );
/**
 * Register plugin settings
 *
 * @since 2.0.1
 *
 * @param array $def Plugin settings
 *
 * @return array
 */
function asdoc_settings( $def ) {

	$settings = array(
		'asdoc-general' => array(
			'name'    => __( 'General', 'wpas-documentation' ),
			'options' => array(
				array(
					'name'    => __( 'Reply &amp; Doc Closes', 'wpas-documentation' ),
					'id'      => 'reply_doc_close',
					'type'    => 'checkbox',
					'desc'    => __( 'Close tickets when replied using the <em>Reply &amp; DOC</em> button.', 'wpas-documentation' ),
					'default' => false
				),
				array(
					'name'    => __( 'Quick Documentation Links Template', 'wpas-documentation' ),
					'id'      => 'asdoc-quick_reply_template',
					'type'    => 'editor',
					'desc'    => sprintf( __( 'Reply to send to the client directing him to the documentation article. <a %s>Click here</a> to review all available template tags.', 'wpas-documentation' ), 'href="#contextual-help-link" onclick="document.getElementById(\'contextual-help-link\').click(); return false;"' ),
					'default' => '<p>Hey {client_name},</p><p>This question has been answered in our documentation. Please check out the answer here: {doc_link}.</p><p>I believe this will help you solve the problem. However, if you need further assistance, feel free to get back to me.</p><p>Cheers,<br>{agent_name}</p>',
				),
				array(
					'name'    => __( 'Rewrite Slug', 'wpas-documentation' ),
					'id'      => 'asdoc-slug',
					'type'    => 'text',
					'desc'    => sprintf( __( 'What should the slug be for documentation? The slug is the part that prefixes the question slug. Example: %s. Please refresh your permalinks if you change this option.', 'wpas-documentation' ), '<code>http://domain.com/<strong>mydocumentationslug</strong>/my-question</code>' ),
					'default' => 'documentation'
				),
			)
		),

		'asdoc-live_search' => array(
			'name'    => __( 'Live Search', 'wpas-documentation' ),
			'options' => array(
				array(
					'name'    => __( 'Live Search Notes', 'wpas-documentation' ),
					'type'    => 'heading',
					'desc'    => __( 'Options for searching as the user enters data into the title of the ticket. If FAQ is installed, both FAQ and Documentation results will show up in the live search drop-down', 'wpas-documentation' ),
				),
				array(
					'name'    => __( 'Live Search Fields', 'wpas-documentation' ),
					'id'      => 'asdoc-selectors',
					'type'    => 'text',
					'desc'    => sprintf( __( 'On which elements should the live search trigger? By default, it is enabled on the ticket submission form title field. You can add more form elements by specifying their selector. If you use multiple selectors, they must be separated by a comma (%s). <a %s>Read more about selectors</a>.', 'wpas-documentation' ), '<code>,</code>', 'href="http://www.w3schools.com/jquery/jquery_selectors.asp" target="_blank"' ),
					'default' => '#wpas_title'
				),
				array(
					'name'    => __( 'Delay', 'wpas-documentation' ),
					'id'      => 'asdoc-delay',
					'type'    => 'text',
					'desc'    => __( 'Delay (in <code>milliseconds</code>) after which the live search is triggered when the user types something.', 'wpas-documentation' ),
					'default' => 300
				),
				array(
					'name'    => __( 'Characters Min.', 'wpas-documentation' ),
					'id'      => 'asdoc-chars_min',
					'type'    => 'number',
					'desc'    => __( 'Minimum number of characters required to trigger the live search.', 'wpas-documentation' ),
					'default' => 3,
					'max'     => 10,
				),
				array(
					'name'    => __( 'Link Target', 'wpas-documentation' ),
					'id'      => 'asdoc-link_target',
					'type'    => 'select',
					'desc'    => __( 'Where do you want links to open?', 'wpas-documentation' ),
					'options' => array( '_blank' => esc_html__( 'New window/tab', 'wpas-documentation' ), '_self' => esc_html__( 'Same window/tab', 'wpas-documentation' ) ),
					'default' => '_self'
				),
				array(
					'name'    => __( 'Sort Results', 'wpas-documentation' ),
					'id'      => 'asdoc-sort_results',
					'type'    => 'select',
					'desc'    => __( 'How do you want live search results ot be displayed?', 'wpas-documentation' ),
					'options' => array(
						'date_asc'   => esc_html__( 'Date (ascending)', 'wpas-documentation' ),
						'date_desc'  => esc_html__( 'Date (descending)', 'wpas-documentation' ),
						'title_asc'  => esc_html__( 'Title (ascending)', 'wpas-documentation' ),
						'title_desc' => esc_html__( 'Date (descending)', 'wpas-documentation' ),
					),
					'default' => 'date_desc'
				),
				array(
					'name'    => __( 'Max. Results', 'wpas-documentation' ),
					'id'      => 'asdoc-display_max',
					'type'    => 'number',
					'desc'    => __( 'Maximum number of results to display.', 'wpas-documentation' ),
					'default' => 5,
					'max'     => 20,
				),
				
				array(
					'name'    => __( 'Live Search Results Styles', 'wpas-documentation' ),
					'type'    => 'heading',
					'desc'    => __( 'These options control the look of the live search results', 'wpas-documentation' ),
				),

				array(
					'name'    => __( 'Section Background Color', 'wpas-documentation' ),
					'id'      => 'asdoc-live-search-section-background-color',
					'type'    => 'color',
					'desc'    => __( 'Color of the background on which the live search results are shown', 'wpas-documentation' ),
					'default' => '#007cc4'
				),
				array(
					'name'    => __( 'Topic Title Color', 'wpas-documentation' ),
					'id'      => 'asdoc-live-search-topic-title-color',
					'type'    => 'color',
					'desc'    => __( 'Color of the topics / results that are shown', 'wpas-documentation' ),
					'default' => '#ffffff'
				),				
				
			)
		),
		
		'Display'	=> array (
			'name'		=> __( 'Display', 'wpas-documentation' ),
			'options'	=> array (

				array(
					'name'		=> __( 'Name', 'wpas-documentation' ),
					'id'		=> 'asdoc-customization-name',
					'type'		=> 'text',
					'desc'		=> sprintf( __( 'Please enter a title for the documentation page. Example: Awesome Support Documentation', 'wpas-documentation' ) ),
					'default'	=> 'Product Documentation',
				),
				array(
					'name'		=> __( 'Title Link', 'wpas-documentation' ),
					'id'		=> 'asdoc-customization-title-link',
					'type'		=> 'text',
					'desc'		=> sprintf( __( 'Where should the user be sent when the documentation title is clicked? This should be a FULL URL including the http:// or https:// prefix.', 'wpas-documentation' ) ),
					'default'	=> '#',
				),
				array(
					'name'		=> __( 'Logo', 'wpas-documentation' ),
					'id'		=> 'asdoc-customization-logo',
					'type'		=> 'upload',
					'desc'		=> sprintf( __( 'Upload a logo' , 'wpas-documentation' ) ),
				),
				array(
					'name'		=> __( 'Sidebar Color', 'wpas-documentation' ),
					'id'		=> 'asdoc-customization-sidebar-color',
					'type'		=> 'color',
					'desc'		=> sprintf( __( 'You can pick whatever color you like for the sidebar' , 'wpas-documentation' ) ),
					'default'	=> '#343131',
				),
				array(
					'name'		=> __( 'Top Bar Color', 'wpas-documentation' ),
					'id'		=> 'asdoc-customization-topbar-color',
					'type'		=> 'color',
					'desc'		=> sprintf( __( 'The color for the top bar', 'wpas-documentation' ) ),
					'default'	=> '#000000',
				),
				array(
					'name'		=> __( 'Product Background Color', 'wpas-documentation' ),
					'id'		=> 'asdoc-customization-product-bg-color',
					'type'		=> 'color',
					'desc'		=> sprintf( __( 'Pick a background color for the products in the navigation menu', 'wpas-documentation' ) ),
					'default'	=> '#5bbdbf',
				),
				array(
					'name'		=> __( 'Product Text Color', 'wpas-documentation' ),
					'id'		=> 'asdoc-customization-product-text-color',
					'type'		=> 'color',
					'desc'		=> sprintf( __( 'Pick a color for the products text in the navigation menu', 'wpas-documentation' ) ),
					'default'	=> '#ffffff',
				),
				array(
					'name'		=> __( 'Chapter Background Color', 'wpas-documentation' ),
					'id'		=> 'asdoc-customization-chapter-bg-color',
					'type'		=> 'color',
					'desc'		=> sprintf( __( 'A background color for the chapters', 'wpas-documentation' ) ),
					'default'	=> '#343131',
				),
				array(
					'name'		=> __( 'Chapter Text Color', 'wpas-documentation' ),
					'id'		=> 'asdoc-customization-chapter-text-color',
					'type'		=> 'color',
					'desc'		=> sprintf( __( 'Color for the chapter text', 'wpas-documentation' ) ),
					'default'	=> '#b3b3b3',
				),
				array(
					'name'		=> __( 'Version Background Color', 'wpas-documentation' ),
					'id'		=> 'asdoc-customization-version-bg-color',
					'type'		=> 'color',
					'desc'		=> sprintf( __( 'A background color for the versions', 'wpas-documentation' ) ),
					'default'	=> '#343131',
				),
				array(
					'name'		=> __( 'Version Text Color', 'wpas-documentation' ),
					'id'		=> 'asdoc-customization-version-text-color',
					'type'		=> 'color',
					'desc'		=> sprintf( __( 'Color for the versions text', 'wpas-documentation' ) ),
					'default'	=> '#b3b3b3',
				),
				array(
					'name'		=> __( 'Topic Background Color', 'wpas-documentation' ),
					'id'		=> 'asdoc-customization-topic-bg-color',
					'type'		=> 'color',
					'desc'		=> sprintf( __( 'A background color for the topics', 'wpas-documentation' ) ),
					'default'	=> '#d6d6d6',
				),
				array(
					'name'		=> __( 'Topic Text Color', 'wpas-documentation' ),
					'id'		=> 'asdoc-customization-topic-text-color',
					'type'		=> 'color',
					'desc'		=> sprintf( __( 'Color for the topics text', 'wpas-documentation' ) ),
					'default'	=> 'gray',
				),
				array(
					'name'		=> __( 'Menu Active Color', 'wpas-documentation' ),
					'id'		=> 'asdoc-customization-menu-active-color',
					'type'		=> 'color',
					'desc'		=> sprintf( __( 'Menu active state color', 'wpas-documentation' ) ),
					'default'	=> '#FCFCFC',
				),
				array(
					'name'		=> __( 'Copyright', 'wpas-documentation' ),
					'id'		=> 'asdoc-customization-copyright',
					'type'		=> 'text',
					'desc'		=> sprintf( __( 'Copyright Notice', 'wpas-documentation' ) ),
					'default'	=> 'Copyright © ' . date('Y'),
				),
				array(
					'name' 					=> __( 'Top Menu Font', 'wpas-documentation' ),
					'id' 					=> 'asdoc-customization-top-menu-font',
					'type' 					=> 'font',
					'desc' 					=> sprintf( __( 'You can pick your preferences for font styles that apply on the assigned WP menu', 'wpas-documentation' ) ),
					'show_font_weight' 		=> false,
					'show_font_style' 		=> false,
					'show_line_height' 		=> false,
					'show_letter_spacing' 	=> false,
					'show_text_transform' 	=> false,
					'show_font_variant' 	=> false,
					'show_text_shadow' 		=> false,
					'show_preview' 			=> false,
					'show_color'			=> false,
					'default'				=> array(
						'font-family'	=> 'Lato',
						'font-size'		=> '13px',
					),
				),
				array(
					'name' 					=> __( 'Product Font', 'wpas-documentation' ),
					'id' 					=> 'asdoc-customization-product-font',
					'type' 					=> 'font',
					'desc' 					=> sprintf( __( 'Select a font style for Product labels in the sidebar', 'wpas-documentation' ) ),
					'show_font_weight' 		=> false,
					'show_font_style' 		=> false,
					'show_line_height' 		=> false,
					'show_letter_spacing' 	=> false,
					'show_text_transform' 	=> false,
					'show_font_variant' 	=> false,
					'show_text_shadow' 		=> false,
					'show_preview' 			=> false,
					'show_color'			=> false,
					'default'				=> array(
						'font-family'	=> 'Open Sans',
						'font-size'		=> '18px',
					),
				),
				array(
					'name' 					=> __( 'Chapter Font', 'wpas-documentation' ),
					'id' 					=> 'asdoc-customization-chapter-font',
					'type' 					=> 'font',
					'desc' 					=> sprintf( __( 'Select a font style for chapters', 'wpas-documentation' ) ),
					'show_font_weight' 		=> false,
					'show_font_style' 		=> false,
					'show_line_height' 		=> false,
					'show_letter_spacing' 	=> false,
					'show_text_transform' 	=> false,
					'show_font_variant' 	=> false,
					'show_text_shadow' 		=> false,
					'show_preview' 			=> false,
					'show_color'			=> false,
					'default'				=> array(
						'font-family'	=> 'Lato',
						'font-size'		=> '15px',
					),
				),
				array(
					'name' 					=> __( 'Version Font', 'wpas-documentation' ),
					'id' 					=> 'asdoc-customization-version-font',
					'type' 					=> 'font',
					'desc' 					=> sprintf( __( 'Select a font style for versions', 'wpas-documentation' ) ),
					'show_font_weight' 		=> false,
					'show_font_style' 		=> false,
					'show_line_height' 		=> false,
					'show_letter_spacing' 	=> false,
					'show_text_transform' 	=> false,
					'show_font_variant' 	=> false,
					'show_text_shadow' 		=> false,
					'show_preview' 			=> false,
					'show_color'			=> false,
					'default'				=> array(
						'font-family'	=> 'Lato',
						'font-size'		=> '15px',
					),
				),
				array(
					'name' 					=> __( 'Topic Font', 'wpas-documentation' ),
					'id' 					=> 'asdoc-customization-topic-font',
					'type' 					=> 'font',
					'desc' 					=> sprintf( __( 'Select a font style for topics', 'wpas-documentation' ) ),
					'show_font_weight' 		=> false,
					'show_font_style' 		=> false,
					'show_line_height' 		=> false,
					'show_letter_spacing' 	=> false,
					'show_text_transform' 	=> false,
					'show_font_variant' 	=> false,
					'show_text_shadow' 		=> false,
					'show_preview' 			=> false,
					'show_color'			=> false,
					'default'				=> array(
						'font-family'	=> 'Lato',
						'font-size'		=> '15px',
					),
				),
				
				array(
					'name'		=> __( 'Show Versions', 'wpas-documentation' ),
					'id'		=> 'asdoc-customization-show-versions',
					'type'		=> 'checkbox',
					'desc'		=> sprintf( __( 'Would you like to show the versions in the documentation pages?', 'wpas-documentation' ) ),
					'default'	=> false,
				),
				
				array(
					'name'		=> __( 'Hide Topbar On Front-end', 'wpas-documentation' ),
					'id'		=> 'asdoc-customization-hide-top-bar',
					'type'		=> 'checkbox',
					'desc'		=> sprintf( __( 'Would you like to hide the top bar on the front end when the user is logged in?', 'wpas-documentation' ) ),
					'default'	=> false,
				),				
				
			),
		),
	);

	return array_merge( $def, $settings );

}
