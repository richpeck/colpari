<?php

$edit_view_link = add_query_arg( array( 
		'action' => $this->actionName('edit_win'), 
		'id' => $item_id,
		'width' => 600,
		'height' => 'auto'
	), 
	admin_url( 'admin-ajax.php' ) 
);



?>

<div class="wpas_pf_ui_item" data-item_id="<?php echo $item_id ?>">
	<div class="wpas_pf_ui_item_msg">
		<span class="msg"></span>
		<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'wpas_productivity' ); ?></span></button>
	</div>
	<div>
		<div class="pf_ui_item_header">
			<span class="title"><?php echo $item['name']; ?></span>
		</div>
		<ul class="actions">
			<li><a href="#" data-action="<?php echo $this->actionName('delete'); ?>" class="wpas_pf_ui_item_action wpas_pf_ui_item_action_delete" data-confirm="<?php _e( "Are you sure you want to delete this saved filter?" ) ?>"><?php _e( 'Delete', 'wpas_productivity' ); ?></a></li>
		</ul>
		<div class="clear clearfix"></div>
	</div>
	
</div>