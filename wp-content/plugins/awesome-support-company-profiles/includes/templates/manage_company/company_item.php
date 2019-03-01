<table class="wp-list-table widefat fixed striped posts wpas_cp_ui_item_company" data-item_id="<?php echo $company_id; ?>">
	<tr class="wpas_cp_ui_item">

		<td width="12%"><?php echo $company->post_title; ?></td>
		<td width="28%"><?php echo $company->address; ?></td>
		<td width="18%"><?php echo $company->email; ?></td>
		<td width="18%"><?php echo $company->fax; ?></td>
		<td width="8%">
			<ul class="actions">
				<li><?php echo wpas_window_link( array(
					'type'  => 'ajax',
					'title' => __( 'Edit', 'wpas_cp' ),
					'class' => 'wpas_cp_ui_item_action cp_icon cp_edit_icon',
					'ajax_params' => array(
						'action' => 'cp_manage_edit_company_profile_view', 
						'id' => $company_id
					),
					'data'  => array(
						'action' => 'edit'
					)
					));
					
					?>
				</li>
				
				<li><a href="#" title="<?php _e( 'Support Users', 'wpas_cp' ); ?>" class="wpas_cp_ui_item_action_toggle_support_users wpas_cp_ui_item_action cp_icon cp_users_icon"></a></li>
			</ul>
		</td>
	</tr>
</table>