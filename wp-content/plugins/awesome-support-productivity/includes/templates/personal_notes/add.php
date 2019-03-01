<?php

$submit_text = isset( $args['submit_text'] ) ? $args['submit_text'] : 'Save';
$type = isset( $args['type'] ) ? $args['type'] : 'add';

$title = isset( $args['data']['title'] ) ? $args['data']['title'] : '';
$body = isset( $args['data']['body'] ) ? $args['data']['body'] : '';

if( 'edit' === $args['type'] ) {
	echo '<input type="hidden" data-name="id" data-default="" value="' . $args['data']['id'] . '" />';
}
?>


<table class="form-table">
	<tr class="form-field form-required">
		<th scope="row">
			<label><?php _e('Title'); ?> <span class="description"><?php _e('(required)'); ?></span></label>
		</th>
		<td>
			<input type="text" data-name="title" data-default="" value="<?php echo $title; ?>" />
		</td>
	</tr>
	<tr class="form-field form-required">
		<th scope="row">
			<label><?php _e('Body'); ?> <span class="description"><?php _e('(required)'); ?></span></label>
		</th>
		<td>
			<textarea rows="6" data-name="body" data-default=""><?php  echo esc_textarea( $body ); ?></textarea>
		</td>
	</tr>
</table>
<?php pf_tb_footer( $submit_text ); ?>