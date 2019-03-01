<?php

$submit_text = isset( $args['submit_text'] ) ? $args['submit_text'] : 'Save';
$type = 'add';

?>


<div class="wpas_cp_edit_support_user_win wpas_cp_window_wrapper">
		
		
	<h3> <?php  _e( 'Associate your self with this company', 'wpas_cp'); ?> </h3>
		
	<table class="form-table">
			
		<tr class="form-field form-required">
			<th scope="row">
				<label><?php _e( 'Select User Type', 'wpas_cp' ); ?> <span class="description"><?php _e( '(required)', 'wpas_cp' ); ?></span></label>
			</th>
			<td>

				<?php echo wpas_cp_user_types_dropdown( array(
					'please_select' => true,
					'name' => 'user_type'
				) ); ?>

			</td>
		</tr>


		<tr class="form-field form-required">
			<th scope="row">
				<label><?php _e( 'Divisions', 'wpas_cp' ); ?> <span class="description"><?php _e( '(required)', 'wpas_cp' ); ?></span></label>
			</th>
			<td>
				<?php echo wpas_cp_user_divisions_dropdown( array(
					'name' => 'divisions[]'
				) ); ?>
			</td>
		</tr>

		<tr class="form-field form-required">
			<th scope="row">
				<label><?php _e( 'Reporting Group', 'wpas_cp' ); ?> <span class="description"><?php _e( '(required)', 'wpas_cp' ); ?></span></label>
			</th>
			<td>
				<?php echo wpas_cp_user_reporting_groups_dropdown( array(
					'please_select' => true,
					'name' => 'reporting_group'
				) ); ?>
			</td>
		</tr>



		<tr class="form-field form-required">
			<th scope="row"></th>
			<td>
					<label><input type="checkbox" name="is_primary_user" value="1" /> <?php _e( 'Primary', 'wpas_cp' ); ?></label>
			</td>
		</tr>

		<tr class="form-field form-required">
			<th scope="row"></th>
			<td>
					<label><input type="checkbox" name="can_reply_ticket" value="1" /> <?php _e( 'Can Reply Ticket', 'wpas_cp' ); ?></label>
			</td>
		</tr>


		<tr class="form-field form-required">
			<th scope="row"></th>
			<td>
					<label><input type="checkbox" name="can_close_ticket" value="1" /> <?php _e( 'Can Close Ticket', 'wpas_cp' ); ?></label>
			</td>
		</tr>

		<tr class="form-field form-required">
			<th scope="row"></th>
			<td>
					<label><input type="checkbox" name="can_open_ticket" value="1" /> <?php _e( 'Can Open Ticket', 'wpas_cp' ); ?></label>
			</td>
		</tr>

		<tr class="form-field form-required">
			<th scope="row"></th>
			<td>
					<label><input type="checkbox" name="can_manage_profile" value="1" /> <?php _e( 'Can Manage Profile', 'wpas_cp' ); ?></label>
			</td>
		</tr>

	</table>
</div>