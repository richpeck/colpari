<?php
/**
 * Avada Options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      4.0.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Privacy settings.
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_privacy( $sections ) {
	$embed_types = array();
	if ( class_exists( 'Avada_Privacy_Embeds' ) ) {
		$embed_types    = Avada()->privacy_embeds->get_embed_defaults( true );
		$embed_defaults = array_keys( $embed_types );
	}

	$settings = get_option( Avada::get_option_name() );

	$sections['privacy'] = array(
		'label'    => esc_html__( 'Privacy', 'Avada' ),
		'id'       => 'heading_privacy',
		'priority' => 25,
		'icon'     => 'el-icon-user',
		'fields'   => array(
			'privacy_note'                   => array(
				'label'       => '',
				'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> The options in this section will help to easier comply with data privacy regulations, like the European GDPR. When the "Privacy Consent" option is used, Avada will create a cookie with the name <b>"privacy_embeds"</b> on user clients browsing your site to manage and store user consent to load the different third party embeds and tracking scripts. You may want to add information about this cookie to your privacy page.', 'Avada' ) . '</div>',
				'id'          => 'privacy_note',
				'type'        => 'custom',
			),
			'gfonts_load_method'             => array(
				'id'          => 'gfonts_load_method',
				'label'       => esc_html__( 'Google & FontAwesome Fonts Mode', 'Avada' ),
				'description' => esc_html__( 'When set to "Local", the Google and FontAwesome fonts set in Theme Options will be downloaded to your server. Set to "CDN" to use the Google and FontAwesome CDNs.', 'Avada' ),
				'type'        => 'radio-buttonset',
				'default'     => 'cdn',
				'choices'     => array(
					'local' => esc_attr__( 'Local', 'Avada' ),
					'cdn'   => esc_attr__( 'CDN', 'Avada' ),
				),
			),
			'privacy_embeds'                 => array(
				'label'       => esc_html__( 'Privacy Consent', 'Avada' ),
				'description' => esc_html__( 'Turn on to prevent embeds and scripts from loading until user consent is given.', 'Avada' ),
				'id'          => 'privacy_embeds',
				'default'     => '0',
				'type'        => 'switch',
			),
			'privacy_expiry'                 => array(
				'label'       => esc_html__( 'Privacy Consent Cookie Expiration', 'Avada' ),
				'description' => esc_html__( 'Controls how long the consent cookie should be stored for.  In days.', 'Avada' ),
				'id'          => 'privacy_expiry',
				'default'     => '30',
				'type'        => 'slider',
				'choices'     => array(
					'min'  => '1',
					'max'  => '366',
					'step' => '1',
				),
				'required'    => array(
					array(
						'setting'  => 'privacy_embeds',
						'operator' => '==',
						'value'    => '1',
					),
				),
			),
			'privacy_embed_types'            => array(
				'label'       => esc_html__( 'Privacy Consent Types', 'Avada' ),
				'description' => esc_html__( 'Select the types of embeds which you would like to require consent.', 'Avada' ),
				'id'          => 'privacy_embed_types',
				'default'     => $embed_defaults,
				'type'        => 'select',
				'multi'       => true,
				'choices'     => $embed_types,
				'required'    => array(
					array(
						'setting'  => 'privacy_embeds',
						'operator' => '==',
						'value'    => '1',
					),
				),
			),
			'privacy_embed_defaults'         => array(
				'label'       => esc_html__( 'Privacy Selected Consent Types', 'Avada' ),
				'description' => esc_html__( 'Select the types of embeds which you would like to have checked by default.  This applies to both the privacy bar and the privacy element.', 'Avada' ),
				'id'          => 'privacy_embed_defaults',
				'default'     => array(),
				'type'        => 'select',
				'multi'       => true,
				'choices'     => $embed_types,
				'required'    => array(
					array(
						'setting'  => 'privacy_embeds',
						'operator' => '==',
						'value'    => '1',
					),
				),
			),
			'privacy_bg_color'               => array(
				'label'       => esc_html__( 'Privacy Placeholder Background Color', 'Avada' ),
				'description' => esc_html__( 'Controls the background color for the privacy placeholders.', 'Avada' ),
				'id'          => 'privacy_bg_color',
				'type'        => 'color-alpha',
				'default'     => 'rgba(0,0,0,0.1)',
				'required'    => array(
					array(
						'setting'  => 'privacy_embeds',
						'operator' => '==',
						'value'    => '1',
					),
				),
			),
			'privacy_color'                  => array(
				'label'       => esc_html__( 'Privacy Placeholder Text Color', 'Avada' ),
				'description' => esc_html__( 'Controls the text color for the embed placeholders.', 'Avada' ),
				'id'          => 'privacy_color',
				'type'        => 'color-alpha',
				'default'     => 'rgba(0,0,0,0.3)',
				'required'    => array(
					array(
						'setting'  => 'privacy_embeds',
						'operator' => '==',
						'value'    => '1',
					),
				),
			),
			'privacy_bar'                    => array(
				'label'       => esc_html__( 'Privacy Bar', 'Avada' ),
				'description' => esc_html__( 'Turn on to enable a privacy bar at the bottom of the page.', 'Avada' ),
				'id'          => 'privacy_bar',
				'default'     => '0',
				'type'        => 'switch',
			),
			'privacy_bar_padding'            => array(
				'label'       => esc_html__( 'Privacy Bar Padding', 'Avada' ),
				'description' => esc_html__( 'Controls the top/right/bottom/left paddings of the privacy bar area.', 'Avada' ),
				'id'          => 'privacy_bar_padding',
				'default'     => array(
					'top'    => '15px',
					'bottom' => '15px',
					'left'   => '30px',
					'right'  => '30px',
				),
				'choices'     => array(
					'top'    => true,
					'bottom' => true,
					'left'   => true,
					'right'  => true,
				),
				'type'        => 'spacing',
				'required'    => array(
					array(
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					),
				),
			),
			'privacy_bar_bg_color'           => array(
				'label'       => esc_html__( 'Privacy Bar Background Color', 'Avada' ),
				'description' => esc_html__( 'Controls the background color for the privacy bar.', 'Avada' ),
				'id'          => 'privacy_bar_bg_color',
				'type'        => 'color-alpha',
				'default'     => '#363839',
				'required'    => array(
					array(
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					),
				),
			),
			'privacy_bar_font_size'          => array(
				'label'       => esc_html__( 'Privacy Bar Font Size', 'Avada' ),
				'description' => esc_html__( 'Controls the font size for the privacy bar content.', 'Avada' ),
				'id'          => 'privacy_bar_font_size',
				'default'     => '13px',
				'type'        => 'dimension',
				'choices'     => array(
					'units' => array( 'px', 'em' ),
				),
				'required'    => array(
					array(
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					),
				),
			),
			'privacy_bar_color'              => array(
				'label'       => esc_html__( 'Privacy Bar Text Color', 'Avada' ),
				'description' => esc_html__( 'Controls the text color for the privacy bar content.', 'Avada' ),
				'id'          => 'privacy_bar_color',
				'type'        => 'color-alpha',
				'default'     => '#8C8989',
				'required'    => array(
					array(
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					),
				),
			),
			'privacy_bar_link_color'         => array(
				'label'       => esc_html__( 'Privacy Bar Link Color', 'Avada' ),
				'description' => esc_html__( 'Controls the link color for the privacy bar.', 'Avada' ),
				'id'          => 'privacy_bar_link_color',
				'type'        => 'color-alpha',
				'default'     => '#bfbfbf',
				'required'    => array(
					array(
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					),
				),
			),
			'privacy_bar_link_hover_color'   => array(
				'label'       => esc_html__( 'Privacy Bar Link Hover Color', 'Avada' ),
				'description' => esc_html__( 'Controls the link hover color for the privacy bar.', 'Avada' ),
				'id'          => 'privacy_bar_link_hover_color',
				'type'        => 'color-alpha',
				'default'     => '#a0ce4e',
				'required'    => array(
					array(
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					),
				),
			),
			'privacy_bar_text'               => array(
				'label'       => esc_html__( 'Privacy Bar Text', 'Avada' ),
				'description' => esc_html__( 'Enter the text which you want to appear on the privacy bar.', 'Avada' ),
				'id'          => 'privacy_bar_text',
				'default'     => 'This website uses cookies and third party services.',
				'type'        => 'textarea',
				'required'    => array(
					array(
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					),
				),
			),
			'privacy_bar_button_text'        => array(
				'label'       => esc_attr__( 'Privacy Bar Button Text', 'Avada' ),
				'description' => esc_attr__( 'Controls the button text for the privacy bar acceptance.', 'Avada' ),
				'id'          => 'privacy_bar_button_text',
				'default'     => esc_html__( 'Ok', 'Avada' ),
				'type'        => 'text',
				'required'    => array(
					array(
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					),
				),
			),
			'privacy_bar_button_save'        => array(
				'label'       => esc_attr__( 'Privacy Bar Button Save On Click', 'Avada' ),
				'description' => esc_attr__( 'If enabled, when the button is clicked it will save the default consent selection.  If disabled the button will only save the preferences after a checkbox has been changed (bar will be hidden however).', 'Avada' ),
				'id'          => 'privacy_bar_button_save',
				'default'     => '0',
				'type'        => 'switch',
				'required'    => array(
					array(
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					),
					array(
						'setting'  => 'privacy_embeds',
						'operator' => '==',
						'value'    => '1',
					),
				),
			),
			'privacy_bar_more'               => array(
				'label'       => esc_attr__( 'Privacy Bar Settings', 'Avada' ),
				'description' => esc_attr__( 'If enabled, a settings section will be added to show more information and to provide checkboxes for tracking and third party embeds.', 'Avada' ),
				'id'          => 'privacy_bar_more',
				'default'     => '0',
				'type'        => 'switch',
				'required'    => array(
					array(
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					),
				),
			),
			'privacy_bar_more_text'          => array(
				'label'       => esc_attr__( 'Privacy Bar Settings Text', 'Avada' ),
				'description' => esc_attr__( 'Controls the link text for the privacy bar settings.', 'Avada' ),
				'id'          => 'privacy_bar_more_text',
				'default'     => esc_html__( 'Settings', 'Avada' ),
				'type'        => 'text',
				'required'    => array(
					array(
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					),
					array(
						'setting'  => 'privacy_bar_more',
						'operator' => '!=',
						'value'    => '0',
					),
				),
			),
			'privacy_bar_update_text'        => array(
				'label'       => esc_attr__( 'Privacy Bar Update Button Text', 'Avada' ),
				'description' => esc_attr__( 'Controls the button text for the privacy bar after a checkbox has changed.', 'Avada' ),
				'id'          => 'privacy_bar_update_text',
				'default'     => esc_html__( 'Update Settings', 'Avada' ),
				'type'        => 'text',
				'required'    => array(
					array(
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					),
					array(
						'setting'  => 'privacy_bar_more',
						'operator' => '!=',
						'value'    => '0',
					),
					array(
						'setting'  => 'privacy_embeds',
						'operator' => '==',
						'value'    => '1',
					),
				),
			),
			'privacy_bar_headings_font_size' => array(
				'label'       => esc_html__( 'Privacy Bar Heading Font Size', 'Avada' ),
				'description' => esc_html__( 'Controls the font size for the privacy bar heading text.', 'Avada' ),
				'id'          => 'privacy_bar_headings_font_size',
				'default'     => '13px',
				'type'        => 'dimension',
				'choices'     => array(
					'units' => array( 'px', 'em' ),
				),
				'required'    => array(
					array(
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					),
					array(
						'setting'  => 'privacy_bar_more',
						'operator' => '!=',
						'value'    => '0',
					),
				),
			),
			'privacy_bar_headings_color'     => array(
				'label'       => esc_html__( 'Privacy Bar Headings Color', 'Avada' ),
				'description' => esc_html__( 'Controls the text color of the privacy bar heading font.', 'Avada' ),
				'id'          => 'privacy_bar_headings_color',
				'default'     => '#dddddd',
				'type'        => 'color-alpha',
				'required'    => array(
					array(
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					),
					array(
						'setting'  => 'privacy_bar_more',
						'operator' => '!=',
						'value'    => '0',
					),
				),
			),
			'privacy_bar_content'            => array(
				'label'       => esc_html__( 'Privacy Bar Content', 'Avada' ),
				'description' => esc_html__( 'The privacy bar content uses a repeater field to select the content for each column. Click the "Add" button to add additional columns.', 'Avada' ),
				'id'          => 'privacy_bar_content',
				'default'     => array(),
				'type'        => 'repeater',
				'bind_title'  => 'title',
				'limit'       => 6,
				'fields'      => array(
					'type'        => array(
						'type'        => 'select',
						'description' => esc_html__( 'Select the type of cookie/content to display.', 'Avada' ),
						'default'     => 'none',
						'choices'     => array(
							'custom'   => 'Custom',
							'tracking' => 'Tracking Cookies',
							'embeds'   => 'Third Party Embeds',
						),
					),
					'title'       => array(
						'type'    => 'text',
						'label'   => esc_html__( 'Title for the content', 'Avada' ),
						'default' => '',
					),
					'description' => array(
						'type'    => 'textarea',
						'label'   => esc_html__( 'Description for the content', 'Avada' ),
						'default' => '',
					),
				),
				'required'    => array(
					array(
						'setting'  => 'privacy_bar_more',
						'operator' => '!=',
						'value'    => '0',
					),
					array(
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					),
				),
			),
		),
	);

	return $sections;

}
