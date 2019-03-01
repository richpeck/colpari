<?php

$submit_text = isset( $args['submit_text'] ) ? $args['submit_text'] : 'Save';
$type = 'add';



$id					= $item->id;
$user_id			= $item->user_id;
$profile_id			= $item->profile_id;
$user_type			= $item->user_type;
$divisions			= $item->divisions;
$reporting_group	= $item->reporting_group;
$primary			= $item->primary;
$can_reply_ticket	= $item->can_reply_ticket;
$can_close_ticket	= $item->can_close_ticket;
$can_open_ticket	= $item->can_open_ticket;
$can_manage_profile = $item->can_manage_profile;
	

?>


<div class="wpas_cp_edit_support_user_win white-popup wpas_cp_window_wrapper">
		
	<div class="wpas_cp_msg"></div>
	<form class="wpas-form" role="form" method="post" action="" id="wpas-cp-edit-support-user">
	
	<?php
	
	wp_nonce_field( 'wpas_cp_mc_edit_su', 'wpas-cp-mc-edit-support-user' );
	printf( '<input type="hidden" name="id" data-default="" value="%s" />', $id );
	
	
	
	?>

		<input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />
		<input type="hidden" name="action" value="wpas_cp_manage_company_edit_support_user" />



	<table class="form-table">

			<tr class="form-field form-required">
			<th scope="row">
				<label><?php _e( 'Select User', 'wpas_cp' ); ?> <span class="description"><?php _e('(required)'); ?></span></label>
			</th>
			<td>

				<?php
				if( $user_id ) {
					$user         = get_user_by( 'ID', $user_id );
					if (! empty( $user ) ) {
						echo $user->display_name;
					}
				}
				?>

			</td>
		</tr>


		<tr class="form-field form-required">
			<th scope="row">
				<label><?php _e( 'Select User Type', 'wpas_cp' ); ?> <span class="description"><?php _e('(required)'); ?></span></label>
			</th>
			<td>

				<?php echo wpas_cp_user_types_dropdown( array(
					'selected' => $user_type,
					'please_select' => true,
					'name' => 'user_type'
				) ); ?>

			</td>
		</tr>


		<tr class="form-field form-required">
			<th scope="row">
				<label><?php _e( 'Divisions', 'wpas_cp' ); ?> <span class="description"><?php _e('(required)'); ?></span></label>
			</th>
			<td>
				<?php echo wpas_cp_user_divisions_dropdown( array(
					'selected' => $divisions,
					'name' => 'divisions[]'
				) ); ?>
			</td>
		</tr>

		<tr class="form-field form-required">
			<th scope="row">
				<label><?php _e( 'Reporting Group', 'wpas_cp' ); ?> <span class="description"><?php _e('(required)'); ?></span></label>
			</th>
			<td>
				<?php echo wpas_cp_user_reporting_groups_dropdown( array(
					'selected' => $reporting_group,
					'please_select' => true,
					'name' => 'reporting_group'
				) ); ?>
			</td>
		</tr>



		<tr class="form-field form-required">
			<th scope="row"></th>
			<td>
					<label><input <?php checked( '1', $primary ); ?> type="checkbox" name="is_primary_user" value="1" /> <?php _e( 'Primary', 'wpas_cp' ); ?></label>
			</td>
		</tr>

		<tr class="form-field form-required">
			<th scope="row"></th>
			<td>
					<label><input <?php checked( '1', $can_reply_ticket ); ?> type="checkbox" name="can_reply_ticket" value="1" /> <?php _e( 'Can Reply Ticket', 'wpas_cp' ); ?></label>
			</td>
		</tr>


		<tr class="form-field form-required">
			<th scope="row"></th>
			<td>
					<label><input <?php checked( '1', $can_close_ticket ); ?> type="checkbox" name="can_close_ticket" value="1" /> <?php _e( 'Can Close Ticket', 'wpas_cp' ); ?></label>
			</td>
		</tr>

		<tr class="form-field form-required">
			<th scope="row"></th>
			<td>
					<label><input <?php checked( '1', $can_open_ticket ); ?> type="checkbox" name="can_open_ticket" value="1" /> <?php _e( 'Can Open Ticket', 'wpas_cp' ); ?></label>
			</td>
		</tr>

		<tr class="form-field form-required">
			<th scope="row"></th>
			<td>
					<label><input <?php checked( '1', $can_manage_profile ); ?> type="checkbox" name="can_manage_profile" value="1" /> <?php _e( 'Can Manage Profile', 'wpas_cp' ); ?></label>
			</td>
		</tr>

	</table>
	<div class="wpas_win_footer">
		<p class="submit">
			<input type="button" class="button button-primary" value="<?php _e( $submit_text, 'wpas_cp' ); ?>">
		</p>
		<p class="wpas_win_close_btn">
			<input type="button" class="button button-primary" value="<?php _e( 'Close', 'wpas_cp' ); ?>">
		</p>
		<div class="clear clearfix"></div>
	</div>

</form>
</div>