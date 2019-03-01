<?php if( isset( $item['Company'] ) && $item['Company'] ) { ?>

<tr class="wpas_cp_ui_item wpas_cp-status-<?php echo $item['Company']->post_status; ?>" data-item_id="<?php echo $item['Company']->ID ?>">
		
	<td><?php echo $item['Company']->post_title; ?></td>
	<td><?php echo $item['Company']->address; ?></td>
	<td><?php echo $item['Company']->email; ?></td>
	<td><?php echo $item['Company']->fax; ?></td>
	<th><?php echo $item['SupportUser']->user_type; ?></th>
	<th><?php echo $item['SupportUser']->display_permissions(); ?></th>
	
	<td>
	
	<ul class="actions">
		<li><a href="#" data-action="<?php echo $this->actionName('unlink_user_company'); ?>" class="wpas_cp_ui_item_action wpas_cp_ui_item_action_delete" data-confirm="<?php _e( "Are you sure you want to delete this company?" ) ?>">Delete</a></li>
	</ul>
	
					
	</td>
	
</tr>

<?php } ?>