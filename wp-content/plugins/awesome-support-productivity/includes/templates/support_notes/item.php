<?php

$edit_view_link = add_query_arg( array( 
		'action' => $this->actionName('edit_win'), 
		'id' => $item_id,
		'duid' => $this->get_data_user(),
		'width' => 600
	), 
	admin_url( 'admin-ajax.php' ) 
);


$header_line = array();
$header_line['date'] = date( 'F d, Y H:i:s', $item['time'] );

$note_by = "";
if( isset( $item['added_by'] ) ) {
	$user = get_user_by( 'ID', $item['added_by'] );
	if( $user ) {
		$note_by = '<a href="'.get_edit_profile_url($user->ID).'">'.$user->display_name.'</a>';
	}
}

if( $note_by ) {
	$header_line['note_by'] = $note_by;
}

$header_line['title'] = $item['title'];

?>

<div class="wpas_pf_ui_item" data-item_id="<?php echo $item_id ?>">
	<div class="wpas_pf_ui_item_msg">
		<span class="msg"></span>
		<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
	</div>
	<div>
		<div class="pf_ui_item_header"><?php echo implode( ' - ', $header_line ); ?></div>
		<?php if( $this->user_can_add_note() ) : ?>
		<ul class="actions">
			<li><a href="<?php echo $edit_view_link; ?>" data-action="edit" title="Edit Note" class="wpas_pf_ui_item_action thickbox">Edit</a></li>
			<li><a href="#" data-action_name="duplicate" data-action="<?php echo $this->actionName('duplicate'); ?>" class="wpas_pf_ui_item_action wpas_pf_ui_item_action_normal">Duplicate</a></li>
			<li><a href="#" data-action="<?php echo $this->actionName('delete'); ?>" class="wpas_pf_ui_item_action wpas_pf_ui_item_action_delete" data-confirm="<?php _e( "Are you sure you want to delete this note?" ) ?>">Delete</a></li>
		</ul>
		<?php endif; ?>
		<div class="clear clearfix"></div>
	</div>
	<div>
		<?php echo $item['body'] ?>
	</div>
	
</div>