<?php
add_filter( 'wpas_plugin_settings', 'wpas_settings_envato', 10, 1 );
/**
 * Add settings for Envato validation.
 *
 * @param  array $def Array of existing settings
 *
 * @return array      Updated settings
 */
function wpas_settings_envato( $def ) {

	$multiple = boolval( wpas_get_option( 'support_products', false ) );

	$settings = array(
		'envato' => array(
			'name'    => __( 'Envato', 'as-envato' ),
			'options' => array(
				array(
					'name' => __( 'Credentials', 'as-envato' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'Personal Token', 'as-envato' ),
					'id'      => 'envato_token',
					'type'    => 'text',
					'default' => '',
					'desc'    => sprintf( __( 'Enter your Envato personal token. <a href="%s" target="_blank">Click here if you don\'t know how to get a personal token</a>.', 'as-envato' ), esc_url( 'https://youtu.be/IacpdaVZnEM' ) )
				),
				array(
					'name' => __( 'Settings', 'as-envato' ),
					'type' => 'heading',
				),
				
				array(
					'name'    => __( 'License Mandatory', 'as-envato' ),
					'id'      => 'envato_mandatory',
					'type'    => 'checkbox',
					'desc'    => false === $multiple ? __( 'Do you want to prevent users without a valid Envato license for one of your products to submit tickets?', 'as-envato' ) : __( 'Do you want to prevent users without a valid Envato license for one of your products to submit tickets? License validation can be set on a per-product basis (in the product details page).', 'as-envato' ),
					'default' => false
				),
			)
		),
	);

	if ( true === $multiple ) {

		array_push( $settings['envato']['options'], array(
			'name'    => __( 'Match Product', 'as-envato' ),
			'id'      => 'envato_match_product',
			'type'    => 'checkbox',
			'desc'    => __( 'By default, a license for any of your products is considered valid, even if it is not for the product the user has selected on your support site. If you wish to make sure the license entered is for the selected product you need to enable this option and set the product ID in the product details page.', 'as-envato' ),
			'default' => false
			)
		);

	}

	if ( false === $multiple ) {

		array_push( $settings['envato']['options'], array(
				'name'    => __( 'Product ID', 'as-envato' ),
				'id'      => 'envato_product_id',
				'type'    => 'text',
				'default' => '',
				'desc'    => sprintf( __( 'ID of the product to check the license for. <a href="%s" target="_blank">Click here if you don\'t know how to get the product ID</a>.', 'as-envato' ), esc_url( 'http://youtu.be/iUU3FvVQzQ0' ) )
			)
		);

	}

	return array_merge( $def, $settings );

}