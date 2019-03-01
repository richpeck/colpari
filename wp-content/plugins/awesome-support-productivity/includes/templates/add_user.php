<div id="wpas_add_user_wrapper">
	
	<div id="createuserform">
		<?php
		wp_nonce_field( 'create-user', '_wpnonce_create-user' );
		$new_user_role = get_option('default_role');
		?>
		
		
		<div class="pf_add_user_msg"></div>
		
		<table class="form-table">
			<tr class="form-field form-required">
				<th scope="row">
					<label for="user_login"><?php _e('Username'); ?> <span class="description"><?php _e('(required)'); ?></span></label>
				</th>
				<td>
					<input name="user_login" type="text" id="user_login" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" />
				</td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row">
					<label for="email"><?php _e('Email'); ?> <span class="description"><?php _e('(required)'); ?></span></label>
				</th>
				<td>
					<input name="email" type="email" id="email" value="" />
				</td>
			</tr>

			<tr class="form-field">
				<th scope="row"><label for="first_name"><?php _e('First Name') ?> </label></th>
				<td><input name="first_name" type="text" id="first_name" value="" /></td>
			</tr>
			<tr class="form-field">
				<th scope="row"><label for="last_name"><?php _e('Last Name') ?> </label></th>
				<td><input name="last_name" type="text" id="last_name" value="" /></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Send User Notification' ) ?></th>
				<td><label for="send_user_notification"><input type="checkbox" name="send_user_notification" id="send_user_notification" value="1" /> <?php _e( 'Send the new user an email about their account.' ); ?></label></td>
			</tr>

			<tr class="form-field">
				<th scope="row"><label for="role"><?php _e('Role'); ?></label></th>
				<td>
					<select name="role" id="role">
						<?php wp_dropdown_roles($new_user_role); ?>
					</select>
				</td>
			</tr>
		</table>
		<?php pf_tb_footer( 'Add New User' ); ?>
		
	</div>
	
</div>
			