<?php

$submit_text = isset( $args['submit_text'] ) ? $args['submit_text'] : 'Save';
$type = 'add';

?>


<table class="form-table">
	<tr class="form-field form-required">
		<th scope="row">
			<label><?php _e( 'Select user' ); ?> <span class="description"><?php _e('(required)'); ?></span></label>
		</th>
		<td>
			
				
			<?php
			
			$options = "";
			
			$staff_atts = array(
				'name'      => 'wpas_it_a_agent',
				'id'        => 'wpas_it_a_agent',
				'select2'   => true,
				'data_attr' => array( 
					'capability'  => 'edit_ticket',
					'action'      => 'wpas_get_users',
					'result_id'   => 'user_id',
					'result_text' => 'user_name'
				)
				
			);
			
			
			echo wpas_dropdown( $staff_atts, $options );
			
			?>
			
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