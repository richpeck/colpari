<?php

$edit_view_link = add_query_arg( array( 
		'action' => $this->actionName('edit_win'), 
		'id' => $item_id,
		'width' => 600,
		'ticket_id' => $this->get_ticket_id(),
		'height' => 'auto'
	), 
	admin_url( 'admin-ajax.php' ) 
);


$item_user_id = isset( $item['user_id'] ) ? $item['user_id'] : 0 ;

$title = "";
if( $item_user_id ) {
	$item_user = get_user_by( 'id',  $item_user_id );
	if( $item_user ) {
		$title = '#' . $item_user->ID . ' - ' . $item_user->display_name . ' - ' . $item_user->user_email;
	}
}

?>

<div class="wpas_pf_ui_item" data-item_id="<?php echo $item_id ?>">
	<div class="wpas_pf_ui_item_msg">
		<span class="msg"></span>
		<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'wpas_productivity' ); ?></span></button>
	</div>
	<div>
		<div class="pf_ui_item_header">
			
			
			<input data-action_name="active" value="1" data-name="active" data-action="<?php echo $this->actionName('active'); ?>" type="checkbox"<?php echo (( $item['active'] ) ? ' checked="checked"' : ''); ?> class="active-cb" />
			
			<span class="title"><?php echo $title; ?></span>
		</div>
		<?php if( $this->user_can_add() ) : ?>
		<ul class="actions">
			<li><a href="<?php echo $edit_view_link; ?>" data-action="edit" title="<?php _e( 'Edit Contact', 'wpas_productivity' ); ?>" class="wpas_pf_ui_item_action wpas_pf_ui_item_action_edit"><?php _e( 'Edit', 'wpas_productivity' ); ?></a></li>
			<li><a href="#" data-action="<?php echo $this->actionName('delete'); ?>" class="wpas_pf_ui_item_action wpas_pf_ui_item_action_delete" data-confirm="<?php _e( "Are you sure you want to delete this user?" ) ?>"><?php _e( 'Delete', 'wpas_productivity' ); ?></a></li>
		</ul>
		<?php endif; ?>
		<div class="clear clearfix"></div>
	</div>
	
</div>