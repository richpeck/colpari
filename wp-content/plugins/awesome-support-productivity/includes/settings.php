<?php

add_filter( 'wpas_plugin_settings', 'wpas_pf_general_settings', 10, 1 );

/**
 * 
 * Add productivity settings section
 * 
 * @param array $settings
 * 
 * @return array
 */
function wpas_pf_general_settings( $def ) {
	
	$options = apply_filters( 'wpas_pf_general_setting_options', array() );
	
	if( !empty( $options ) ) {
		
		$settings = array(
			'productivity' => array(
				'name'    => __( 'Productivity', 'wpas_productivity' ),
				'options' => $options
			)
		);
		
		return array_merge( $def, $settings );
	}
	
	return $def;

}


function wpas_pf_add_default_editor() {
	wp_editor( "", "wpas_pf_editor", array(
		'media_buttons' => false,
		'teeny'         => true,
		'quicktags'     => true,
		//'editor_height' => '200',
		//'editor_width' => '400'
	) );
}


function pf_tb_footer( $button_label ) {
?>
	<div class="tb_footer">
		<p class="submit">
			<input type="button" class="button button-primary" value="<?php _e( $button_label, 'wpas_productivity' ); ?>">
		</p>
		<p class="tb_close_btn">
			<input type="button" class="button button-primary" value="<?php _e( 'Close', 'wpas_productivity' ); ?>">
		</p>
		<div class="clear clearfix"></div>
	</div>
<?php
}