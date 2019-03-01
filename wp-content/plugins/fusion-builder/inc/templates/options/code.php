<?php
$settings = array();
if ( function_exists( 'wp_enqueue_code_editor' ) ) {
	$settings = wp_enqueue_code_editor( array() );
}
?>
<textarea
	name="{{ param.param_name }}"
	id="{{ param.param_name }}"
	class="fusion-builder-code-block"
	cols="20"
	rows="5"
	data-language="{{ ( 'undefined' !== typeof param.language ) ? param.language : 'default' }}"
	<# if ( param.placeholder ) { #>
		data-placeholder="{{ param.value }}"
	<# } #>
>{{ option_value }}</textarea>
<textarea style="display: none;" class="hidden {{ param.param_name }}"><?php echo wp_json_encode( $settings ); ?></textarea>
