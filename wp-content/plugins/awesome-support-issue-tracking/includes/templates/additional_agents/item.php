<?php


$item_user_id = isset( $item['user_id'] ) ? $item['user_id'] : 0 ;

$title = "";
if( $item_user_id ) {
	$item_user = get_user_by( 'id',  $item_user_id );
	if( $item_user ) {
		$title = '#' . $item_user->ID . ' - ' . $item_user->display_name . ' - ' . $item_user->user_email;
	}
}

?>

<div class="wpas_it_ui_item" data-item_id="<?php echo $item_id ?>">
	<div class="wpas_it_ui_item_msg">
		<span class="msg"></span>
		<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'wpas_it' ); ?></span></button>
	</div>
	<div>
		<div class="it_ui_item_header">
			
			<span class="title"><?php echo $title; ?></span>
		</div>
		<?php if( $this->user_can_add() ) : ?>
		<ul class="actions">
			<li><a href="#" data-action="<?php echo $this->actionName('delete'); ?>" class="wpas_it_ui_item_action wpas_it_ui_item_action_delete" data-confirm="<?php _e( "Are you sure you want to delete this user?" ) ?>"><?php _e( 'Delete', 'wpas_it' ); ?></a></li>
		</ul>
		<?php endif; ?>
		<div class="clear clearfix"></div>
	</div>
	
</div>