<?php
/**
 * @package   Awesome Support FAQ/Titan
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'plugins_loaded', 'asfaq_init_titan', 11 );
/**
 * Instantiate Titan Framework for the FAQ addon
 *
 * @since 1.0
 * @return void
 */
function asfaq_init_titan() {

	if ( ! class_exists( 'TitanFramework' ) ) {
		require_once( WPAS_PATH . 'vendor/gambitph/titan-framework/titan-framework.php' );
	}

	$titan = TitanFramework::getInstance( 'asfaq' );

	$settings = $titan->createAdminPage( array(
			'name'       => esc_html__( 'Settings', 'as-faq' ),
			'title'      => esc_html__( 'Awesome Support: FAQ - Settings', 'as-faq' ),
			'id'         => 'asfaq-settings',
			'parent'     => 'edit.php?post_type=faq',
			'capability' => 'settings_tickets'
		)
	);

	/**
	 * Get plugin core options
	 *
	 * @since 1.0
	 * @var  array $options Addon options (filtered)
	 */
	$options = apply_filters( 'asfaq_plugin_settings', array() );

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
 * @since 1.0
 *
 * @param string $option  ID of the option to lookup
 * @param mixed  $default Value to return in case the option doesn't exist
 *
 * @return mixed
 */
function asfaq_get_option( $option, $default = '' ) {
	
	$options = unserialize( get_option( 'asfaq_options' ) );
	
	return isset( $options[ $option ] ) ? $options[ $option ] : $default;

}

add_action( 'tf_admin_options_saved_asfaq', 'asfaq_clean_options_session' );
/**
 * Unset the session var that contains our settings
 *
 * When settings are saved, we need to unset the session var that's used as cache.
 * Otherwise, the newly saved options would not appear when called using asfas_get_option().
 *
 * @since 1.0
 * @return void
 */
function asfaq_clean_options_session() {
	WPAS()->session->clean( 'asfaq_options' );
}