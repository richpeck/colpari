<?php

add_filter( 'wpas_plugin_settings', 'gf_settings_gravity_forms', 10, 1 );
/**
 * Add settings for Gravity Forms integration.
 *
 * @param  (array) $def Array of existing settings
 *
 * @return (array)      Updated settings
 */
function gf_settings_gravity_forms( $def ) {

	$settings = array(
		'gravity_forms' => array(
			'name'    => __( 'Gravity Forms', 'wpas-gravity-forms' ),
			'options' => array(
				array(
					'name' => __( 'Gravity Forms Mappings', 'wpas-gf' ),
					'type' => 'heading',
					'css'  => 'first-row',
				),
				array(
					'name' => __( 'Add New Mapping', 'wpas-gf' ),
					'id'   => 'gravity_form_list[0]',
					'type' => 'select',
					'desc' => __( 'Add New Mapping.', 'wpas-gf' ),
					'css'  => 'add-new-mapping last-row',
				),
			),
		),
	);

	return array_merge( $def, $settings );

}