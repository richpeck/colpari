<?php

$edit_view_link = add_query_arg( array( 
		'action' => $this->actionName('edit_win'), 
		'id' => $item_id,
		'width' => 600,
		'height' => 'auto'
	), 
	admin_url( 'admin-ajax.php' ) 
);

$status = 	$item['status'];
$status_name = 	$this->statuses[ $status ];
?>

<div class="wpas_pf_ui_item" data-item_id="<?php echo $item_id ?>">
	<div class="wpas_pf_ui_item_msg">
		<span class="msg"></span>
		<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'wpas_productivity' ); ?></span></button>
	</div>
	<div>
		<div class="pf_ui_item_header">
			<span class="title"><?php echo $item['title']; ?></span>
			<span class="date_due"><?php echo $item['date_due']; ?></span>
			<span class="status"><?php echo $status_name; ?></span>
			
		</div>
		<?php if( $this->user_can_add_todo() ) : ?>
		<ul class="actions">
			<?php if( 'completed' !== $item['status'] ) : ?>
			<li><a href="#" data-action="<?php echo $this->actionName('completed'); ?>" data-action_name="completed" class="wpas_pf_ui_item_action wpas_pf_ui_item_action_normal"><?php _e( 'Mark Completed', 'wpas_productivity' ); ?></a></li>
			<?php endif; ?>
			<li><a href="<?php echo $edit_view_link; ?>" data-action="edit" title="<?php _e( 'Edit Todo', 'wpas_productivity' ); ?>" class="wpas_pf_ui_item_action wpas_pf_ui_item_action_edit"><?php _e( 'Edit', 'wpas_productivity' ); ?></a></li>
			<li><a href="#" data-action="<?php echo $this->actionName('duplicate'); ?>" data-action_name="duplicate" class="wpas_pf_ui_item_action wpas_pf_ui_item_action_normal"><?php _e( 'Duplicate', 'wpas_productivity' ); ?></a></li>
			<li><a href="#" data-action="<?php echo $this->actionName('delete'); ?>" class="wpas_pf_ui_item_action wpas_pf_ui_item_action_delete" data-confirm="<?php _e( "Are you sure you want to delete this todo?" ) ?>"><?php _e( 'Delete', 'wpas_productivity' ); ?></a></li>
		</ul>
		<?php endif; ?>
		<div class="clear clearfix"></div>
	</div>
	
	<div>
		<?php echo $item['body'] ?>
	</div>
	
</div>