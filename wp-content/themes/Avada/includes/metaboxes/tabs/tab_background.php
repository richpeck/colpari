<?php
/**
 * Background Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

$this->radio_buttonset(
	'page_bg_layout',
	esc_attr__( 'Layout', 'Avada' ),
	array(
		'default' => esc_attr__( 'Default', 'Avada' ),
		'wide'    => esc_attr__( 'Wide', 'Avada' ),
		'boxed'   => esc_attr__( 'Boxed', 'Avada' ),
	),
	/* translators: Additional description (defaults). */
	sprintf( esc_attr__( 'Select boxed or wide layout. %s', 'Avada' ), Avada()->settings->get_default_description( 'layout', '', 'select' ) )
);

// Dependency check for boxed mode.
$boxed_dependency = array();

$page_bg_color = Fusion_Color::new_color(
	array(
		'color'    => Avada()->settings->get( 'bg_color' ),
		'fallback' => '#ffffff',
	)
);
$this->color(
	'page_bg_color',
	esc_attr__( 'Background Color For Page', 'Avada' ),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Controls the background color for the page. When the color value is set to anything below 100&#37; opacity, the color will overlay the background image if one is uploaded. Hex code, ex: #000. %s', 'Avada' ), Avada()->settings->get_default_description( 'bg_color' ) ),
	true,
	$boxed_dependency,
	$page_bg_color->color
);

$this->upload(
	'page_bg',
	esc_attr__( 'Background Image For Page', 'Avada' ),
	/* translators: Additional description (defaults). */
	sprintf( esc_attr__( 'Select an image to use for a full page background. %s', 'Avada' ), Avada()->settings->get_default_description( 'bg_image', 'url' ) ),
	$boxed_dependency
);

// Also add check for background image.
$boxed_dependency[] = array(
	'field'      => 'page_bg',
	'value'      => '',
	'comparison' => '!=',
);

$this->radio_buttonset(
	'page_bg_full',
	esc_attr__( '100% Background Image', 'Avada' ),
	array(
		'default' => esc_attr__( 'Default', 'Avada' ),
		'no'      => esc_attr__( 'No', 'Avada' ),
		'yes'     => esc_attr__( 'Yes', 'Avada' ),
	),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Choose to have the background image display at 100&#37;. %s', 'Avada' ), Avada()->settings->get_default_description( 'bg_full', '', 'yesno' ) ),
	'',
	$boxed_dependency
);

$this->select(
	'page_bg_repeat',
	esc_attr__( 'Background Repeat', 'Avada' ),
	array(
		'default'   => esc_attr__( 'Default', 'Avada' ),
		'repeat'    => esc_attr__( 'Tile', 'Avada' ),
		'repeat-x'  => esc_attr__( 'Tile Horizontally', 'Avada' ),
		'repeat-y'  => esc_attr__( 'Tile Vertically', 'Avada' ),
		'no-repeat' => esc_attr__( 'No Repeat', 'Avada' ),
	),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Select how the background image repeats. %s', 'Avada' ), Avada()->settings->get_default_description( 'bg_repeat', '', 'select' ) ),
	$boxed_dependency
);

// Dependency check for wide mode.
$wide_dependency = array();

$content_bg_color = Fusion_Color::new_color(
	array(
		'color'    => Avada()->settings->get( 'content_bg_color' ),
		'fallback' => '#ffffff',
	)
);
$this->color(
	'wide_page_bg_color',
	esc_attr__( 'Background Color for Main Content Area', 'Avada' ),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Controls the background color for the main content area. Hex code, ex: #000. %s', 'Avada' ), Avada()->settings->get_default_description( 'content_bg_color' ) ),
	true,
	$wide_dependency,
	$content_bg_color->color
);

$this->upload(
	'wide_page_bg',
	esc_attr__( 'Background Image for Main Content Area', 'Avada' ),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Select an image to use for the main content area. %s', 'Avada' ), Avada()->settings->get_default_description( 'content_bg_image', 'url' ) ),
	$wide_dependency
);

// Also add check for background image.
$wide_dependency[] = array(
	'field'      => 'wide_page_bg',
	'value'      => '',
	'comparison' => '!=',
);

$this->radio_buttonset(
	'wide_page_bg_full',
	esc_html__( '100% Background Image', 'Avada' ),
	array(
		'default' => esc_attr__( 'Default', 'Avada' ),
		'no'      => esc_attr__( 'No', 'Avada' ),
		'yes'     => esc_attr__( 'Yes', 'Avada' ),
	),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Choose to have the background image display at 100&#37;. %s', 'Avada' ), Avada()->settings->get_default_description( 'content_bg_full', '', 'yesno' ) ),
	'',
	$wide_dependency
);

$this->select(
	'wide_page_bg_repeat',
	esc_attr__( 'Background Repeat', 'Avada' ),
	array(
		'default'   => esc_attr__( 'Default', 'Avada' ),
		'repeat'    => esc_attr__( 'Tile', 'Avada' ),
		'repeat-x'  => esc_attr__( 'Tile Horizontally', 'Avada' ),
		'repeat-y'  => esc_attr__( 'Tile Vertically', 'Avada' ),
		'no-repeat' => esc_attr__( 'No Repeat', 'Avada' ),
	),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Select how the background image repeats. %s', 'Avada' ), Avada()->settings->get_default_description( 'content_bg_repeat', '', 'select' ) ),
	$wide_dependency
);

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
