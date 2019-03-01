<?php

$submit_text = isset( $args['submit_text'] ) ? $args['submit_text'] : 'Save';
$type = isset( $args['type'] ) ? $args['type'] : 'add';

$signature = isset( $args['data']['signature'] ) ? $args['data']['signature'] : '';

$default = isset( $args['data']['default'] ) ? $args['data']['default'] : '';

if( 'edit' === $args['type'] ) {
	echo '<input type="hidden" data-name="id" data-default="" value="' . $args['data']['id'] . '" />';
}
?>


<table class="form-table">
	
	<tr class="form-field form-required">
		<th scope="row">
			<label><?php _e('Signature'); ?> <span class="description"><?php _e('(required)'); ?></span></label>
		</th>
		<td>
			
			<?php 
			
			wp_editor( $signature, "pf_signature_content_{$type}", array(
			'media_buttons' => false,
			'teeny'         => true,
			'quicktags'     => true,
			'editor_height' => '200',
			'editor_width' => '400'
		) );
			
			?>
		</td>
	</tr>
	<tr>
		<th scope="row"></th>
		<td>
			<label><input type="checkbox"<?php echo ( ( $default ) ? ' checked="checked"' : '' ); ?> data-name="default" value="1" /> <?php _e('Default'); ?></label>
		</td>
		
	</tr>
</table>
<?php pf_tb_footer( $submit_text ); ?>