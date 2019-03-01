

<div class="updated below-h2 hidden"><p></p></div>
<div style="position: relative;">
	
	<div class="pf_overlay"></div>

	<table class="widefat wpas-system-tools-table" id="wpas-system-tools">
		<thead>
			<tr>
				<th data-override="key" class="row-title"><?php _e( 'Capabilities', 'wpas_productivity' ); ?></th>
				<th data-override="value"></th>
			</tr>
		</thead>
		<tbody>
			<tr>
			    <td class="row-title" style="width: 200px"><label for="tablecell"><?php _e( 'Role', 'wpas_productivity' ); ?></label></td>
				<td>
					<select name="role" id="pf_settings_cap_role">
						<option value=""><?php _e( 'Select a Role...', 'wpas_productivity' ); ?></option>
						<?php wp_dropdown_roles();?>
					</select>
				</td>
			</tr>
			
			
			<tr class="wpas_pf_quickset_btns_row">
				<td class="row-title"><label for="tablecell"><?php _e( 'Quickset', 'wpas_productivity' ); ?></label></td>
				<td>
					
				    <ul>
					<li><a href="#" data-preset="user" class="button button-primary wpas_pf_quickset_btn">Support User</a></li>
					<li><a href="#" data-preset="agent" class="button button-primary wpas_pf_quickset_btn">Support Agent</a></li>
					<li><a href="#" data-preset="manager" class="button button-primary wpas_pf_quickset_btn">Support Manager</a></li>
					<li><a href="#" data-preset="supervisor" class="button button-primary wpas_pf_quickset_btn">Support Supervisor</a></li>
				    </ul>
				    
				</td>
			</tr>
			
			
			<tr class="wpas_pf_caps_row">
				<td class="row-title"><label for="tablecell"><?php _e( 'Capabilities', 'wpas_productivity' ); ?></label></td>
				<td>

					<div class="options checkbox_dropdown" id="wpas_pf_role_caps_options">

						<?php
						foreach( WPAS_PF_Role_Capability::all_role_capabilities() as $cap ) {
							echo '<div class="option"><label><input name="cap[]" type="checkbox" value="'.$cap.'" /> '.$cap.'</label></div>';
						}

						?>
					</div>

				</td>
			</tr>

			<tr>
				<td class="row-title"></td>
				<td>

					<input type="button" value="<?php _e( 'Save', 'wpas_productivity' ) ?>" class="button button-primary" id="pf_update_caps_btn" />

				</td>
			</tr>

			<?php do_action( 'wpas_caps_table_after' ); ?>
		</tbody>
	</table>
</div>