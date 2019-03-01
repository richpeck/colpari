

<tr class="wpas_cp_ui_item" data-item_id="<?php echo $item_id ?>">
		
	<td width="24%">
				<div class="su_user_name <?php echo ($item->primary ? 'primary' : ''); ?>"> <?php echo wpas_cp_display_user_link( $item->user_id ); ?></div>
			<div>( <?php echo $item->user_type; ?> )</div>
		</td>
		<td width="20%"><?php echo $item->getDivisions(); ?></td>
		<td width="20%"><?php echo $item->getReportingGroup(); ?></td>
		<td width="20%">
		
		<?php 
		
		echo $item->display_permissions();
		
		?>
	</td>
		
	<td width="16%">
		
		<ul class="actions">
			<li><?php echo wpas_window_link( array(
				'type'  => 'ajax',
				'title' => __( 'Edit', 'wpas_cp' ),
				'class' => 'wpas_cp_ui_item_action cp_icon cp_edit_icon',
				'ajax_params' => array(
					'action' => $this->actionName( 'edit_win' ), 
					'id' => $item_id,
					'duid' => $this->get_post_id()
				),
				'data'  => array(
					'action' => 'edit'
				)
			));
			
			?></li>
			<li><a href="#" title="<?php _e( 'Delete', 'wpas_cp' );?>" data-action="<?php echo $this->actionName('delete'); ?>" class="wpas_cp_ui_item_action wpas_cp_ui_item_action_delete cp_icon cp_delete_icon" data-confirm="<?php _e( "Are you sure you want to delete this support user?" ) ?>"></a></li>
			
		</ul>
		
						
	</td>
		
		
</tr>