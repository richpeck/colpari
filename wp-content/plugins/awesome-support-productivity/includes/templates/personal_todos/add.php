<?php

$submit_text = isset( $args['submit_text'] ) ? $args['submit_text'] : 'Save';
$type = isset( $args['type'] ) ? $args['type'] : 'add';

$title = isset( $args['data']['title'] ) ? $args['data']['title'] : '';
$body = isset( $args['data']['body'] ) ? $args['data']['body'] : '';

$status = isset( $args['data']['status'] ) ? $args['data']['status'] : '';

$date_due = isset( $args['data']['date_due'] ) ? $args['data']['date_due'] : '';

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
	
	<tr class="form-field form-required">
		<th scope="row">
			<label><?php _e('Status'); ?> <span class="description"><?php _e('(required)'); ?></span></label>
		</th>
		<td>
			<select data-name="status" data-default="">
				<option value="">Select Status...</option>
				<?php 
				foreach( $this->statuses as $status_key => $status_name ) { ?>
				<option value="<?php echo $status_key; ?>" <?php selected( $status, $status_key ); ?>><?php echo $status_name; ?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	
	<tr class="form-field form-required">
		<th scope="row">
			<label><?php _e('Date Due'); ?> <span class="description"><?php _e('(required)'); ?></span></label>
		</th>
		<td>
			<input type="text" data-name="date_due" data-default="" value="<?php echo $date_due; ?>" class="wpas_pf_date_field" />
		</td>
	</tr>
</table>
<?php pf_tb_footer( $submit_text ); ?>