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
 * Advanced settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_advanced( $sections ) {
	$settings = get_option( Avada::get_option_name() );

	$sections['advanced'] = array(
		'label'    => esc_html__( 'Advanced', 'Avada' ),
		'id'       => 'heading_advanced',
		'is_panel' => true,
		'priority' => 25,
		'icon'     => 'el-icon-puzzle',
		'fields'   => array(
			'tracking_head_body_section' => array(
				'label'  => esc_html__( 'Code Fields (Tracking etc.)', 'Avada' ),
				'id'     => 'tracking_head_body_section',
				'icon'   => true,
				'type'   => 'sub-section',
				'fields' => array(
					'google_analytics' => array(
						'label'       => esc_html__( 'Tracking Code', 'Avada' ),
						'description' => esc_html__( 'Paste your tracking code here. This will be added into the header template of your theme. Place code inside &lt;script&gt; tags.', 'Avada' ),
						'id'          => 'google_analytics',
						'default'     => '',
						'type'        => 'code',
						'choices'     => array(
							'language' => 'html',
							'height'   => 300,
							'theme'    => 'chrome',
						),
					),
					'space_head'       => array(
						'label'       => esc_html__( 'Space before &lt;/head&gt;', 'Avada' ),
						'description' => esc_html__( 'Only accepts javascript code wrapped with &lt;script&gt; tags and HTML markup that is valid inside the &lt;/head&gt; tag.', 'Avada' ),
						'id'          => 'space_head',
						'default'     => '',
						'type'        => 'code',
						'choices'     => array(
							'language' => 'html',
							'height'   => 350,
							'theme'    => 'chrome',
						),
					),
					'space_body'       => array(
						'label'       => esc_html__( 'Space before &lt;/body&gt;', 'Avada' ),
						'description' => esc_html__( 'Only accepts javascript code, wrapped with &lt;script&gt; tags and valid HTML markup inside the &lt;/body&gt; tag.', 'Avada' ),
						'id'          => 'space_body',
						'default'     => '',
						'type'        => 'code',
						'choices'     => array(
							'language' => 'html',
							'height'   => 350,
							'theme'    => 'chrome',
						),
					),
				),
			),
			'theme_features_section'     => array(
				'label'  => esc_html__( 'Theme Features', 'Avada' ),
				'id'     => 'theme_features_section',
				'icon'   => true,
				'type'   => 'sub-section',
				'fields' => array(
					'dependencies_status'             => array(
						'label'       => esc_attr__( "Avada's Option Network Dependencies", 'Avada' ),
						'description' => esc_html__( "Avada's Option Network consists of Fusion Theme Options, Page Options, Builder options and each of them have dependent options ON by default. This means the only options you see are the only ones currently available for your selection. However, if you wish to disable this feature, simply turn this option off, and all dependencies will be disabled (requires save & refresh).", 'Avada' ),
						'id'          => 'dependencies_status',
						'default'     => '1',
						'type'        => 'switch',
					),
					'pw_jpeg_quality'                 => array(
						'label'       => esc_html__( 'WordPress jpeg Quality', 'Avada' ),
						/* translators: "Regenerate Thumbnails" plugin link. */
						'description' => sprintf( esc_html__( 'Controls the quality of the generated image sizes for every uploaded image. Ranges between 0 and 100 percent. Higher values lead to better image qualities but also higher file sizes. NOTE: After changing this value, please install and run the %s plugin once.', 'Avada' ), '<a target="_blank" href="' . admin_url( 'plugin-install.php?s=Regenerate+Thumbnails&tab=search&type=term' ) . '" title="' . esc_html__( 'Regenerate Thumbnails', 'Avada' ) . '">' . esc_html__( 'Regenerate Thumbnails', 'Avada' ) . '</a>' ),
						'id'          => 'pw_jpeg_quality',
						'default'     => '82',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
					),
					'smooth_scrolling'                => array(
						'label'       => esc_html__( 'Smooth Scrolling', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable smooth scrolling. This will replace default browser scrollbar with a dark scrollbar.', 'Avada' ),
						'id'          => 'smooth_scrolling',
						'default'     => '0',
						'type'        => 'switch',
					),
					'disable_code_block_encoding'     => array(
						'label'       => esc_html__( 'Code Block Encoding', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable encoding in the Fusion Builder code block and syntax highlighting elements.', 'Avada' ),
						'id'          => 'disable_code_block_encoding',
						'default'     => '1',
						'type'        => 'switch',
					),
					'disable_megamenu'                => array(
						'label'       => esc_html__( 'Mega Menu', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable Avada\'s mega menu.', 'Avada' ),
						'id'          => 'disable_megamenu',
						'default'     => '1',
						'type'        => 'switch',
					),
					'avada_rev_styles'                => array(
						'label'       => esc_html__( 'Avada Styles For Slider Revolution', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the Avada styles and use the default Slider Revolution styles.', 'Avada' ),
						'id'          => 'avada_rev_styles',
						'default'     => '1',
						'type'        => 'switch',
					),
					'avada_styles_dropdowns'          => array(
						'label'       => esc_html__( 'Avada Dropdown Styles', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the Avada styles for dropdown/select fields site wide. This should be done if you experience any issues with 3rd party plugin dropdowns.', 'Avada' ),
						'id'          => 'avada_styles_dropdowns',
						'default'     => '1',
						'type'        => 'switch',
					),
					'use_animate_css'                 => array(
						'label'       => esc_html__( 'CSS Animations', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable CSS animations on elements.', 'Avada' ),
						'id'          => 'use_animate_css',
						'default'     => '1',
						'type'        => 'switch',
					),
					'disable_mobile_animate_css'      => array(
						'label'       => esc_html__( 'CSS Animations on Mobiles', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable CSS animations on mobiles.', 'Avada' ),
						'id'          => 'disable_mobile_animate_css',
						'default'     => '0',
						'type'        => 'switch',
					),
					'disable_mobile_image_hovers'     => array(
						'label'       => esc_html__( 'CSS Image Hover Animations on Mobiles', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable CSS image hover animations on mobiles.', 'Avada' ),
						'id'          => 'disable_mobile_image_hovers',
						'default'     => '1',
						'type'        => 'switch',
					),
					'status_yt'                       => array(
						'label'       => esc_html__( 'Youtube API Scripts', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable Youtube API scripts.', 'Avada' ),
						'id'          => 'status_yt',
						'default'     => '1',
						'type'        => 'switch',
					),
					'status_vimeo'                    => array(
						'label'       => esc_html__( 'Vimeo API Scripts', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable Vimeo API scripts.', 'Avada' ),
						'id'          => 'status_vimeo',
						'default'     => '1',
						'type'        => 'switch',
					),
					'status_gmap'                     => array(
						'label'       => esc_html__( 'Google Map Scripts', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable google map.', 'Avada' ),
						'id'          => 'status_gmap',
						'default'     => '1',
						'type'        => 'switch',
					),
					'status_totop'                    => array(
						'label'       => esc_html__( 'ToTop Script', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the ToTop script which adds the scrolling to top functionality.', 'Avada' ),
						'id'          => 'status_totop',
						'default'     => '1',
						'type'        => 'switch',
					),
					'status_totop_mobile'             => array(
						'label'       => esc_html__( 'ToTop Script on mobile', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the ToTop script on mobile devices.', 'Avada' ),
						'id'          => 'status_totop_mobile',
						'default'     => '0',
						'type'        => 'switch',
					),
					'status_fusion_slider'            => array(
						'label'       => esc_html__( 'Fusion Slider', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the fusion slider.', 'Avada' ),
						'id'          => 'status_fusion_slider',
						'default'     => '1',
						'type'        => 'switch',
					),
					'status_eslider'                  => array(
						'label'       => esc_html__( 'Elastic Slider', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the elastic slider.', 'Avada' ),
						'id'          => 'status_eslider',
						'default'     => '1',
						'type'        => 'switch',
					),
					'status_fontawesome'              => array(
						'label'       => esc_html__( 'FontAwesome', 'Avada' ),
						'description' => esc_html__( 'Choose which Font Awesome icon subsets you want to load. Note that Light subset can only be used if Font Awesome Pro is enabled.', 'Avada' ),
						'id'          => 'status_fontawesome',
						'default'     => array( 'fab', 'far', 'fas' ),
						'type'        => 'select',
						'multi'       => true,
						'choices'     => array(
							'fab' => esc_html__( 'Brands', 'Avada' ),
							'far' => esc_html__( 'Regular', 'Avada' ),
							'fas' => esc_html__( 'Solid', 'Avada' ),
							'fal' => esc_html__( 'Light', 'Avada' ),
						),
					),
					'fontawesome_v4_compatibility'    => array(
						'label'       => esc_html__( 'FontAwesome v4 Compatibility', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable support for FontAwesome 4 icon code format.', 'Avada' ),
						'id'          => 'fontawesome_v4_compatibility',
						'default'     => '0',
						'type'        => 'switch',
					),
					'status_fontawesome_pro'          => array(
						'label'       => esc_html__( 'FontAwesome Pro', 'Avada' ),
						/* translators: %1$s = license text & link. %2$s: whitelist text & link. */
						'description' => sprintf( esc_html__( 'Font Awesome Pro %1$s is required and you need to %2$s your domain.', 'Avada' ), '<a href="https://fontawesome.com/buy/standard" target="_blank" rel="noopener noreferrer">license</a>', '<a href="https://fontawesome.com/account/domains" target="_blank" rel="noopener noreferrer">whitelist</a>' ),
						'id'          => 'status_fontawesome_pro',
						'default'     => '0',
						'type'        => 'switch',
					),
					'status_outline'                  => array(
						'label'       => esc_html__( 'CSS Outlines', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable browser specific CSS element outlines used to improve accessibility.', 'Avada' ),
						'id'          => 'status_outline',
						'default'     => '0',
						'type'        => 'switch',
					),
					'status_opengraph'                => array(
						'label'       => esc_html__( 'Open Graph Meta Tags', 'Avada' ),
						'description' => __( 'Turn on to enable open graph meta tags which are mainly used when sharing pages on social networking sites like Facebook. <strong>IMPORTANT:</strong> Some optimization plugins, like e.g. Yoast SEO, add their own implementation of this, and if you want to use that, this option should be disabled.', 'Avada' ),
						'id'          => 'status_opengraph',
						'default'     => '1',
						'type'        => 'switch',
					),
					'disable_date_rich_snippet_pages' => array(
						'label'       => esc_html__( 'Rich Snippets', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable rich snippets data site wide. If set to "On", you can also control individual items below. If set to "Off" all items will be disabled.', 'Avada' ),
						'id'          => 'disable_date_rich_snippet_pages',
						'default'     => '1',
						'type'        => 'switch',
					),
					'disable_rich_snippet_title'      => array(
						'label'       => esc_html__( 'Rich Snippets Title', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable title rich snippet data site wide.', 'Avada' ),
						'id'          => 'disable_rich_snippet_title',
						'default'     => '1',
						'type'        => 'switch',
						'required'    => array(
							array(
								'setting'  => 'disable_date_rich_snippet_pages',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'disable_rich_snippet_author'     => array(
						'label'       => esc_html__( 'Rich Snippets Author Info', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable author rich snippet data site wide.', 'Avada' ),
						'id'          => 'disable_rich_snippet_author',
						'default'     => '1',
						'type'        => 'switch',
						'required'    => array(
							array(
								'setting'  => 'disable_date_rich_snippet_pages',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'disable_rich_snippet_date'       => array(
						'label'       => esc_html__( 'Rich Snippets Last Update Date', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable udate date rich snippet data site wide.', 'Avada' ),
						'id'          => 'disable_rich_snippet_date',
						'default'     => '1',
						'type'        => 'switch',
						'required'    => array(
							array(
								'setting'  => 'disable_date_rich_snippet_pages',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
				),
			),
		),
	);

	return $sections;

}
