<?php

$submit_text = isset( $args['submit_text'] ) ? $args['submit_text'] : 'Save';
$type = isset( $args['type'] ) ? $args['type'] : 'add';

?>


<table class="form-table">
	<tr class="form-field form-required">
		<th scope="row">
			<label><?php _e('Save as'); ?> <span class="description"><?php _e('(required)'); ?></span></label>
		</th>
		<td>
			<input type="text" data-name="name" data-default="" value="" />
		</td>
	</tr>
</table>
<div class="tb_footer">
	<p class="submit">
		<a href="#" class="button button-primary save_criteria_btn"><?php _e( $submit_text, 'wpas_productivity' ); ?></a>
	</p>
	<p class="tb_close_btn">
		<input type="button" class="button button-primary" value="<?php _e( 'Close', 'wpas_productivity' ); ?>">
	</p>
	<div class="clear clearfix"></div>
</div>