<?php

$submit_text = isset( $args['submit_text'] ) ? $args['submit_text'] : 'Save';
$type = isset( $args['type'] ) ? $args['type'] : 'add';

$email = isset( $args['data']['email'] ) ? $args['data']['email'] : '';
$name = isset( $args['data']['name'] ) ? $args['data']['name'] : '';
$active = isset( $args['data']['active'] ) && $args['data']['active'] ? true : false;

if( 'edit' === $args['type'] ) {
	echo '<input type="hidden" data-name="id" data-default="" value="' . $args['data']['id'] . '" />';
}
?>


<table class="form-table">
	<tr class="form-field form-required">
		<th scope="row">
			<label><?php _e( 'Name' ); ?> <span class="description"><?php _e( '(required)' ); ?></span></label>
		</th>
		<td>
			<input type="text" data-name="name" data-default="" value="<?php echo $name; ?>" />
		</td>
	</tr>
		
	<tr class="form-field form-required">
		<th scope="row">
			<label><?php _e( 'Email Address' ); ?> <span class="description"><?php _e( '(required)' ); ?></span></label>
		</th>
		<td>
			<input type="text" data-name="email" data-default="" value="<?php echo $email; ?>" />
		</td>
	</tr>

	<tr>
		<th scope="row"></th>
		<td>
			<label><input type="checkbox"<?php echo ( ( $active ) ? ' checked="checked"' : '' ); ?> data-name="active" value="1" /> <?php _e( 'Receive Notification Emails' ); ?></label>
		</td>
		
	</tr>
</table>
<div class="tb_footer">
	<p class="submit">
		<input type="button" class="button button-primary" value="<?php _e( $submit_text, 'wpas_it' ); ?>">
	</p>
	<p class="it_tb_close_btn">
		<input type="button" class="button button-primary" value="<?php _e( 'Close', 'wpas_it' ); ?>">
	</p>
	<div class="clear clearfix"></div>
</div>