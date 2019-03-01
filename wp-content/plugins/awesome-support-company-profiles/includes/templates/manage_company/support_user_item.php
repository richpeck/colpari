<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$delete_item_params = array(
	'action' => 'wpas_cp_mcp_delete_support_user',
	'id' => $item_id,
	'security' => wp_create_nonce( 'wpas_cp_mc_del_su' ),
	'company_id' => $company_id
);


$display_name = $item->user_id;
$suser = get_user_by( 'id', $item->user_id );
if( $suser ) {
	$display_name = $suser->display_name;
}

$primary_class = $item->primary ? 'primary' : '';


$permissions = array();

if( $item->can_reply_ticket ) {
	$permissions[] = 'can_reply_ticket';
}

if( $item->can_close_ticket ) {
	$permissions[] = 'can_close_ticket';
}


$permissions_list[] = array();



?>

		
				
<div class="wpas_cp_ui_item wpas_cp_ui_item_su_item" data-item_id="<?php echo $item_id ?>">
		
		<div>
			
			<span class="su_user_name <?php echo ( $item->primary ? 'primary' : '' ); ?>"><?php echo $display_name; ?> (<?php echo wpas_cp_display_user_type( $item->user_type ); ?>)</span>
			<span><?php echo $item->getDivisions(); ?></span>
			<span><?php echo $item->getReportingGroup(); ?></span>
		</div>
		
		<div>
			
			<?php echo $item->display_permissions(); ?>
				
			
			<ul class="actions">
				<li><?php echo wpas_window_link( array(
					'type'  => 'ajax',
					'title' => 'Edit',
					'class' => 'wpas_cp_ui_item_action cp_icon cp_edit_icon',
					'ajax_params' => array(
						'action' => 'cp_edit_support_user_view', 
						'id' => $item_id,
						'user_id' => $item->user_id,
						'company_id' => $company_id
					),
					'data'  => array(
						'action' => 'edit'
					)
				));

				?></li>
				<li><a href="#" title="delete" data-ajax_params="<?php echo esc_attr(json_encode( $delete_item_params )); ?>" class="wpas_cp_ui_item_action wpas_cp_ui_item_action_delete cp_icon cp_delete_icon" data-confirm="<?php _e( "Are you sure you want to delete this support user?" ) ?>"></a></li>
			</ul>
			
				
			<div class="clear clearfix"></div>
		</div>
</div>
			
		