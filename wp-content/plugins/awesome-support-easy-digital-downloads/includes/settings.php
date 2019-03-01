<?php
/**
 * @package   Awesome Support Easy Digital Downloads
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016 Awesome Support
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_filter( 'wpas_plugin_settings', 'wpas_addon_settings_edd', 11, 1 );
/**
 * Add plugin settings.
 *
 * @param  array $def Array of existing settings
 *
 * @return array      Updated settings
 */
function wpas_addon_settings_edd( $def ) {

	$settings = array(
		'edd' => array(
			'name'    => 'EDD',
			'options' => array(
				array(
					'name' => __( 'Refund Days', 'as-edd' ),
					'id'   => 'edd_refund_policy_days',
					'type' => 'number',
					'desc' => __( 'If you have a time-limited refund policy, you can specify how many days after purchase refunds can be granted. A visual identification will be added in the ticket edit screen. Leave blank if you do not have a policy.', 'as-edd' ),
					'min'  => 0,
					'max'  => 365,
				),
				array(
					'name' => __( 'Treat INACTIVE licenses as still valid', 'as-edd' ),
					'id'   => 'edd_as_inactive_license_is_valid',
					'type' => 'checkbox',
					'desc' => __( 'Turn this on to treat any license not activated as being valid for support', 'as-edd' ),
					'default' => false,
				),
				array(
					'name' => __( 'Treat INVALID PRODUCTS as still valid', 'as-edd' ),
					'id'   => 'edd_as_invalid_product_is_valid',
					'type' => 'checkbox',
					'desc' => __( 'Turn this on to treat any item id as being valid for support', 'as-edd' ),
					'default' => false,
				),
				array(
					'name' => __( 'Treat EXPIRED licenses as still valid', 'as-edd' ),
					'id'   => 'edd_as_expired_license_is_valid',
					'type' => 'checkbox',
					'desc' => __( 'Turn this on to treat expired licenses as still being valid for support', 'as-edd' ),
					'default' => false,
				),
				
				array(
					'name' => __( 'Labels', 'as-edd' ),
					'id'   => 'edd_as_labels',
					'type' => 'Heading',
					'desc' => __( 'Set labels for license and EDD order number fields - these are the labels that the user will see when filling out a ticket.', 'as-edd' ),										

				),
				
				array(
					'name' => __( 'Title for Order Field', 'as-edd' ),
					'id'   => 'edd_as_order_number_title',
					'type' => 'text',
					'default' => 'EDD Order Number',
				),
				array(
					'name' => __( 'Description for Order Field', 'as-edd' ),
					'id'   => 'edd_as_order_number_desc',
					'type' => 'text',
					'default' => 'The number of the order concerned by this support request',
				),
				array(
					'name' => __( 'Title for License Field', 'as-edd' ),
					'id'   => 'edd_as_license_title',
					'type' => 'text',
					'default' => 'Product License',
				),
				array(
					'name' => __( 'Description for License Field', 'as-edd' ),
					'id'   => 'edd_as_license_desc',
					'type' => 'text',
					'default' => 'License key of the product you\'re requesting support for',
				),				
				
				array(
					'name' => __( 'Debug', 'as-edd' ),
					'id'   => 'edd_as_debug',
					'type' => 'Heading',
					'desc' => __( 'Debug Flags - do not turn these on unless advised by an Awesome Support technical support representative.', 'as-edd' )
				),				
				array(
					'name' => __( 'Show Post and Term IDS in License Dropdown', 'as-edd' ),
					'id'   => 'edd_as_show_ids_in_license_dropdown',
					'type' => 'checkbox',
					'desc' => __( 'Turning this on adds the internal product post ID and term ids to the license dropdowns', 'as-edd' ),
					'default' => false,
				),
			)
		),
	);

	return array_merge( $def, $settings );

}