<?php

$submit_text = isset( $args['submit_text'] ) ? $args['submit_text'] : 'Save';
$type = 'add';

$item = isset( $args['data']['item'] ) ? $args['data']['item'] : null;

$id					= '';
$user_id			= '';
$profile_id			= '';
$user_type			= '';
$divisions			= '';
$reporting_group	= '';
$primary			= '';
$can_reply_ticket	= '';
$can_close_ticket	= '';
$can_open_ticket	= '';
$can_manage_profile = '';

if( $item ) {
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
	
	
	printf( '<input type="hidden" data-name="id" data-default="" value="%s" />', $id );
}

?>



<input type="hidden" data-name="view_type" value="<?php echo $this->view_type; ?>" />

<table class="form-table">
		
	<?php if( 'user_profile' === $this->view_type ) { ?>
	<tr class="form-field form-required">
		<th scope="row">
			<label><?php _e( 'Select Company', 'wpas_cp' ); ?> <span class="description"><?php _e('(required)'); ?></span></label>
		</th>
		<td>
			
			<?php
			
			wp_nonce_field( 'wpas-get-cp-companies', 'cp_nonce_wpas_get_cp_companies' );
			
			echo wpas_dropdown( array(
				'name'      => 'company_id',
				'id'        => 'company_id',
				'select2'   => true,
				'class' => 'cp-select2',
				'data_attr' => array( 'action' => 'wpas_get_cp_companies', 'result_id' => 'company_id', 'result_text' => 'company_name', 'default' => '' )
			), "" );					
			
			?>
			
		</td>
	</tr>
	<?php } else { ?>
		
		<tr class="form-field form-required">
		<th scope="row">
			<label><?php _e( 'Select User', 'wpas_cp' ); ?> <span class="description"><?php _e('(required)'); ?></span></label>
		</th>
		<td>
			
			<?php
			
			
			echo wpas_dropdown( array(
				'name'      => 'user_id',
				'id'        => 'user_id',
				'select2'   => true,
				'class' => 'cp-select2',
				'data_attr' => array( 'capability' => 'create_ticket', 'action' => 'wpas_get_users', 'result_id' => 'user_id', 'result_text' => 'user_name', 'default' => '' )
			), wpas_cp_user_selected_option($user_id) );					
			
			?>
			
		</td>
	</tr>
	
	
	
	<?php } ?>
	
	
	<tr class="form-field form-required">
		<th scope="row">
			<label><?php _e( 'Select User Type', 'wpas_cp' ); ?> <span class="description"><?php _e('(required)'); ?></span></label>
		</th>
		<td>
			
			<?php echo wpas_cp_user_types_dropdown( array(
				'selected' => $user_type,
				'please_select' => true,
				'data_attr' => array( 'name' => 'user_type', 'default' => '' )
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
				'data_attr' => array( 'name' => 'divisions[]', 'default' => '' )
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
				'data_attr' => array( 'name' => 'reporting_group', 'default' => '' )
			) ); ?>
		</td>
	</tr>
	
	
	
	<tr class="form-field form-required">
		<th scope="row"></th>
		<td>
				<label><input <?php checked( '1', $primary ); ?> type="checkbox" data-name="is_primary_user" value="1" data-default="" /> <?php _e( 'Primary', 'wpas_cp' ); ?></label>
		</td>
	</tr>
	
	<tr class="form-field form-required">
		<th scope="row"></th>
		<td>
				<label><input <?php checked( '1', $can_reply_ticket ); ?> type="checkbox" data-name="can_reply_ticket" value="1" data-default="" /> <?php _e( 'Can Reply Ticket', 'wpas_cp' ); ?></label>
		</td>
	</tr>
	
	
	<tr class="form-field form-required">
		<th scope="row"></th>
		<td>
				<label><input <?php checked( '1', $can_close_ticket ); ?> type="checkbox" data-name="can_close_ticket" value="1" data-default="" /> <?php _e( 'Can Close Ticket', 'wpas_cp' ); ?></label>
		</td>
	</tr>
	
	<tr class="form-field form-required">
		<th scope="row"></th>
		<td>
				<label><input <?php checked( '1', $can_open_ticket ); ?> type="checkbox" data-name="can_open_ticket" value="1" data-default="" /> <?php _e( 'Can Open Ticket', 'wpas_cp' ); ?></label>
		</td>
	</tr>
	
	<tr class="form-field form-required">
		<th scope="row"></th>
		<td>
				<label><input <?php checked( '1', $can_manage_profile ); ?> type="checkbox" data-name="can_manage_profile" value="1" data-default="" /> <?php _e( 'Can Manage Profile', 'wpas_cp' ); ?></label>
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