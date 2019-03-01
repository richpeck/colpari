<?php
/**
 * @package   Awesome Support Documentation/Titan
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      http://getawesomesupport.com
 * @copyright 2014-2017 Awesome Support
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'plugins_loaded', 'asdoc_init_titan', 30 );

/**
 * Instantiate Titan Framework for the Documentation addon
 *
 * @since 2.0.1
 * @return void
 */
function asdoc_init_titan() {

	if ( ! class_exists( 'TitanFramework' ) ) {
		require_once( WPAS_PATH . 'vendor/gambitph/titan-framework/titan-framework.php' );
	}

	$titan = TitanFramework::getInstance( 'asdoc' );

	$settings = $titan->createAdminPage( array(
			'name'       => esc_html__( 'Settings', 'wpas-documentation' ),
			'title'      => esc_html__( 'Awesome Support: Documentation - Settings', 'wpas-documentation' ),
			'id'         => 'asdoc-settings',
			'parent'     => 'edit.php?post_type=documentation',
			'capability' => 'settings_tickets'
		)
	);

	/**
	 * Get plugin core options
	 *
	 * @since 2.0.1
	 * @var  array $options Addon options (filtered)
	 */
	$options = apply_filters( 'asdoc_plugin_settings', array() );

	/* Parse options */
	foreach ( $options as $tab => $content ) {

		/* Add a new tab */
		$tab = $settings->createTab( array(
				'name'  => $content['name'],
				'title' => isset( $content['title'] ) ? $content['title'] : $content['name'],
				'id'    => $tab
			)
		);

		/* Add all options to current tab */
		foreach ( $content['options'] as $option ) {
			$tab->createOption( $option );
		}

		$tab->createOption( array( 'type' => 'save', ) );

	}

}

/**
 * Retrieve plugin option
 *
 * @since 2.0.1
 *
 * @param string $option  ID of the option to lookup
 * @param mixed  $default Value to return in case the option doesn't exist
 *
 * @return mixed
 */
function asdoc_get_option( $option, $default = '' ) {

	$options = unserialize( get_option( 'asdoc_options' ) );	

	return isset( $options[ $option ] ) ? $options[ $option ] : $default;

}

add_action( 'tf_admin_options_saved_asdoc', 'asdoc_clean_options_session' );
/**
 * Unset the session var that contains our settings
 *
 * When settings are saved, we need to unset the session var that's used as cache.
 * Otherwise, the newly saved options would not appear when called using asdoc_get_option().
 *
 * @since 2.0.1
 * @return void
 */
function asdoc_clean_options_session() {
	WPAS()->session->clean( 'asdoc_options' );
}