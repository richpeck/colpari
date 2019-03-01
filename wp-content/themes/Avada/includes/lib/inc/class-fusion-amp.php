<?php
/**
 * AMP methods for Avada & Fusion-Builder.
 *
 * @package Fusion-Library
 * @since 1.8.1
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * AMP methods.
 *
 * @since 1.8.1
 */
class Fusion_AMP {

	/**
	 * Determine if an AMP plugin is active.
	 *
	 * @static
	 * @access public
	 * @since 1.8.1
	 * @return bool
	 */
	public static function is_plugin_active() {

		// Check for the "AMP" plugin.
		if ( function_exists( 'is_amp_endpoint' ) ) {
			return true;
		}

		// Check for the "AMP for WP – Accelerated Mobile Pages" plugin.
		if ( function_exists( 'ampforwp_is_amp_endpoint' ) ) {
			return true;
		}

		// Check for the "AMP WP" plugin.
		if ( function_exists( 'is_amp_wp' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if we're on an AMP endpoint.
	 *
	 * @static
	 * @access public
	 * @since 1.8.1
	 * @return bool
	 */
	public static function is_amp_endpoint() {

		// If an AMP plugin is not active then this is definitely not an AMP endpoint.
		if ( ! self::is_plugin_active() ) {
			return false;
		}

		// Check for the "AMP" plugin.
		if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
			return true;
		}

		// Check for the "AMP for WP – Accelerated Mobile Pages" plugin.
		if ( function_exists( 'ampforwp_is_amp_endpoint' ) && ampforwp_is_amp_endpoint() ) {
			return true;
		}

		// Check for the "AMP WP" plugin.
		if ( function_exists( 'is_amp_wp' ) && is_amp_wp() ) {
			return true;
		}

		return false;
	}
}
