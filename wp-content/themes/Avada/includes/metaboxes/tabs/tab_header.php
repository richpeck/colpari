<?php
/**
 * Header Metabox options.
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

$this->remove_deprecated();

$this->radio_buttonset(
	'display_header',
	esc_attr__( 'Display Header', 'Avada' ),
	array(
		'yes' => esc_attr__( 'Yes', 'Avada' ),
		'no'  => esc_attr__( 'No', 'Avada' ),
	),
	esc_html__( 'Choose to show or hide the header.', 'Avada' )
);

$this->radio_buttonset(
	'header_100_width',
	esc_html__( '100% Header Width', 'Avada' ),
	array(
		'default' => esc_attr__( 'Default', 'Avada' ),
		'yes'     => esc_attr__( 'Yes', 'Avada' ),
		'no'      => esc_attr__( 'No', 'Avada' ),
	),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Choose to set header width to 100&#37; of the browser width. Select "No" for site width. %s', 'Avada' ), Avada()->settings->get_default_description( 'header_100_width', '', 'yesno' ) ),
	'',
	array(
		array(
			'field'      => 'display_header',
			'value'      => 'yes',
			'comparison' => '==',
		),
	)
);

$this->color(
	'combined_header_bg_color',
	esc_html__( 'Background Color', 'Avada' ),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Controls the background color for the header. Hex code or rgba value, ex: #000. %s', 'Avada' ), Avada()->settings->get_default_description( 'header_bg_color' ) ),
	true,
	array(
		array(
			'field'      => 'display_header',
			'value'      => 'yes',
			'comparison' => '==',
		),
	),
	Avada()->settings->get( 'header_bg_color' )
);

$this->color(
	'mobile_header_bg_color',
	esc_html__( 'Mobile Header Background Color', 'Avada' ),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Controls the background color for the header on mobile devices. Hex code or rgba value, ex: #000. %s', 'Avada' ), Avada()->settings->get_default_description( 'mobile_header_bg_color' ) ),
	true,
	array(
		array(
			'field'      => 'display_header',
			'value'      => 'yes',
			'comparison' => '==',
		),
	),
	Avada()->settings->get( 'mobile_header_bg_color' )
);

$this->upload(
	'header_bg',
	esc_attr__( 'Background Image', 'Avada' ),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Select an image for the header background. If left empty, the header background color will be used. For top headers the image displays on top of the header background color and will only display if header opacity is set to 1. For side headers the image displays behind the header background color so the header opacity must be set below 1 to see the image. %s', 'Avada' ), Avada()->settings->get_default_description( 'header_bg_image', 'thumbnail' ) ),
	array(
		array(
			'field'      => 'display_header',
			'value'      => 'yes',
			'comparison' => '==',
		),
	)
);

$this->radio_buttonset(
	'header_bg_full',
	esc_html__( '100% Background Image', 'Avada' ),
	array(
		'no'  => esc_attr__( 'No', 'Avada' ),
		'yes' => esc_attr__( 'Yes', 'Avada' ),
	),
	esc_html__( 'Choose to have the background image display at 100%.', 'Avada' ),
	'',
	array(
		array(
			'field'      => 'display_header',
			'value'      => 'yes',
			'comparison' => '==',
		),
		array(
			'field'      => 'header_bg',
			'value'      => '',
			'comparison' => '!=',
		),
	)
);

$this->select(
	'header_bg_repeat',
	esc_attr__( 'Background Repeat', 'Avada' ),
	array(
		'repeat'    => esc_attr__( 'Tile', 'Avada' ),
		'repeat-x'  => esc_attr__( 'Tile Horizontally', 'Avada' ),
		'repeat-y'  => esc_attr__( 'Tile Vertically', 'Avada' ),
		'no-repeat' => esc_attr__( 'No Repeat', 'Avada' ),
	),
	esc_html__( 'Select how the background image repeats.', 'Avada' ),
	array(
		array(
			'field'      => 'display_header',
			'value'      => 'yes',
			'comparison' => '==',
		),
		array(
			'field'      => 'header_bg',
			'value'      => '',
			'comparison' => '!=',
		),
	)
);

$menus                  = get_terms(
	'nav_menu',
	array(
		'hide_empty' => false,
	)
);
$menu_select['default'] = 'Default Menu';

foreach ( $menus as $menu ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
	$menu_select[ $menu->term_id ] = $menu->name;
}

$this->select(
	'displayed_menu',
	esc_attr__( 'Main Navigation Menu', 'Avada' ),
	$menu_select,
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Select which menu displays on this page. %s', 'Avada' ), Avada()->settings->get_default_description( 'main_navigation', '', 'menu' ) ),
	array(
		array(
			'field'      => 'display_header',
			'value'      => 'yes',
			'comparison' => '==',
		),
	)
);

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
