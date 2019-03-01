<?php
/**
 * Icon picker methods.
 *
 * @package Fusion-Library
 * @since 1.0.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Icons handler.
 *
 * @package Fusion-Library
 * @since 1.0.0
 */
class Fusion_Icon {
	/**
	 * Associative Array of Icon Data.
	 *
	 * @access private
	 * @since 1.0
	 * @var array
	 */
	private $data = array();

	/**
	 * Iterator.
	 *
	 * @access private
	 * @since 1.0
	 * @var object Iterator
	 */
	private $iterator;

	/**
	 * Constructor.
	 *
	 * @param object $iterator The iterator class.
	 * @param string $class    Icon css class.
	 * @param string $unicode  Unicode character reference.
	 * @param string $subset   The FA subset.
	 */
	public function __construct( $iterator, $class, $unicode, $subset ) {

		$this->iterator = $iterator;

		// Set Basic Data.
		$this->data['class']   = $class;
		$this->data['unicode'] = $unicode;
		$this->data['subset']  = $subset;
	}

	/**
	 * Simple getter.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $key The key we'll be looking for in the array.
	 */
	public function __get( $key ) {

		if ( strtolower( $key ) === 'name' ) {
			return $this->get_name( $this->__get( 'class' ) );
		}

		if ( is_array( $this->data ) && isset( $this->data[ $key ] ) ) {
			return $this->data[ $key ];
		}
	}

	/**
	 * Gets the icon name.
	 *
	 * @access private
	 * @since 1.0
	 * @param string $class The icon class.
	 * @return string
	 */
	private function get_name( $class ) {

		// Remove Prefix.
		$name = substr( $class, strlen( $this->iterator->getPrefix() ) + 1 );

		// Convert Hyphens to Spaces.
		$name = str_replace( '-', ' ', $name );

		// Capitalize Words.
		$name = ucwords( $name );

		// Show Directional Variants in Parenthesis.
		$directions = array(
			'/up$/i',
			'/down$/i',
			'/left$/i',
			'/right$/i',
		);
		$directions_format = array( '(Up)', '(Down)', '(Left)', '(Right)' );
		$name = preg_replace( $directions, $directions_format, $name );

		// Use Word "Outlined" in Place of "O".
		$outlined_variants = array( '/\so$/i', '/\so\s/i' );
		$name = preg_replace( $outlined_variants, ' Outlined ', $name );

		// Remove Trailing Characters.
		$name = trim( $name );

		return $name;
	}
}

if ( ! function_exists( 'fusion_get_icons_array' ) ) {
	/**
	 * Get an array of available icons.
	 *
	 * @return array
	 */
	function fusion_get_icons_array() {
		$path = Fusion_Font_Awesome::is_fa_pro_enabled() ? '/assets/fonts/fontawesome/icons_pro.php' : '/assets/fonts/fontawesome/icons_free.php';

		return include wp_normalize_path( FUSION_LIBRARY_PATH . $path );
	}
}
