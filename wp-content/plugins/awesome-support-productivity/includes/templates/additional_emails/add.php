<?php

$submit_text = isset( $args['submit_text'] ) ? $args['submit_text'] : 'Save';
$type = isset( $args['type'] ) ? $args['type'] : 'add';

$email = isset( $args['data']['email'] ) ? $args['data']['email'] : '';
$active = isset( $args['data']['active'] ) && $args['data']['active'] ? true : false;

if( 'edit' === $args['type'] ) {
	echo '<input type="hidden" data-name="id" data-default="" value="' . $args['data']['id'] . '" />';
}
?>


<table class="form-table">
	<tr class="form-field form-required">
		<th scope="row">
			<label><?php _e('Email Address'); ?> <span class="description"><?php _e('(required)'); ?></span></label>
		</th>
		<td>
			<input type="text" data-name="email" data-default="" value="<?php echo $email; ?>" />
		</td>
	</tr>
	<tr>
		<th scope="row"></th>
		<td>
			<label><input type="checkbox"<?php echo ( ( $active ) ? ' checked="checked"' : '' ); ?> data-name="active" value="1" /> <?php _e('Receive Notification Emails'); ?></label>
		</td>
		
	</tr>
</table>
<?php pf_tb_footer( $submit_text ); ?>