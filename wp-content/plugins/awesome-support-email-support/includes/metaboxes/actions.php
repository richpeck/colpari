<div id="wpas-unknown-message">
	<?php
	/**
	 * wpas_backend_mail_unknown_actions_before hook
	 *
	 * @param  integer $post_id Current post ID
	 * @param  object  $post    Current post object
	 * @since  0.1.0
	 */
	do_action( 'wpas_backend_mail_unknown_actions_before', $post->ID, $post );
	?>

	<?php 
	/**
	 * If the current post author is an admin it means we were unable
	 * to identify the client who sent it. Thus we need to figure it out
	 * and specify a new post author for this message to be converted
	 * into a ticket reply.
	 */
	
	
	$assigned_author_id = get_post_meta( $post->ID, 'assigned_creator', true );
	$assigned_author_name = '';
	
	if( !empty( $assigned_author_id ) ) {
		$assigned_author = get_user_by( 'id', $assigned_author_id );
		$assigned_author_name = !empty( $assigned_author ) ? $assigned_author->data->display_name : '';
	}
	

	?>
	
	<input type="hidden" name="assign_action" value="save" />
	<label for="wpas_message_creator"><strong><?php _e( 'Creator', 'wpas-mail' ); ?></strong></label>
	<p><?php _e( 'Select the creator of this reply from your database if he has an account.', 'wpas-mail' ); ?></p>
	<p>
			
			
			
	<?php
			
			
		$args = array(
			'select2' => true, 
			'name' => 'wpas_message_creator', 
			'id' => 'wpas-issuer'
		    );
			
			
		if ( version_compare( WPAS_VERSION, '3.3', '>=' ) ) {
			$args['agent_fallback'] = true;
			$args['data_attr'] = array( 'capability' => 'create_ticket');
		}
			
		if ( ! empty( $unknown ) ) {
			$args['please_select'] = true;
			$args['selected']      = false;
		}
		
		if ( version_compare( WPAS_VERSION, '3.3', '>=' ) ) {
			$option = "";
			if( !empty($assigned_author_id) ) {
				$option = "<option value=\"{$assigned_author_id}\" selected='selected'>{$assigned_author_name}</option>";
			}
			echo wpas_dropdown( $args, $option );
		} else {
			if( !empty($assigned_author_id) ) {
				$args['selected'] = $assigned_author_id;
			}
			echo wpas_users_dropdown( $args );
		}
			
		?>
	</p>
		
	<div class="assign_to_customer_btn" style="<?php echo ( !empty($assigned_author_id) ? 'display:block' : '' ); ?>">
		<button type="button" class="button-primary" data-assign_action="assign_to_customer"><?php _e( 'Create New Ticket', 'wpas-mail' ); ?></button>
	</div>

	<?php
	/**
	 * If the message has not parent post it means that we were unable to identify
	 * to which ticket this reply refers to. Thus we need to figure out which one
	 * is the parent ticket (open or closed) and attach this reply to it.
	 */
	
	$assigned_to_ticket = get_post_meta($post->ID, 'assign_to_ticket', true);
	
	$is_assign_ticket_btn_active = $assigned_to_ticket ? true : false;
	?>
	
	<label for="wpas_parent_ticket"><strong><?php _e( 'Ticket', 'wpas-mail' ); ?></strong></label>
	<p><?php _e( 'If you attach this reply to a closed ticket, the ticket will be re-opened.', 'wpas-mail' ); ?></p>
	<p><?php wpas_tickets_dropdown( array( 'name' => 'wpas_parent_ticket', 'id' => 'wpas_parent_ticket', 'please_select' => true, 'select2' => true, 'selected' => $assigned_to_ticket ) ); ?></p>
	
	<div class="assign_to_ticket_btn" style="<?php echo ($is_assign_ticket_btn_active ? 'display:block' : '' ); ?>">
		<button type="button" class="button-primary" data-assign_action="assign_to_ticket"><?php _e( 'Add As Reply To This Ticket', 'wpas-mail' ); ?></button>
		<div class="assign_to_ticket_reply_description_text">
			<br />
			<?php _e( 'Please make sure you select a Creator above before clicking the Add As Reply Button!', 'wpas-mail' ); ?>
		</div>		
	</div>

	

	<?php
	/**
	 * wpas_backend_mail_unknown_actions_after hook
	 *
	 * @param  integer $post_id Current post ID
	 * @param  object  $post    Current post object
	 * @since  0.1.0
	 */
	do_action( 'wpas_backend_mail_unknown_actions_after', $post->ID, $post );
	?>
	
	<div>
		<button type="button" class="assign_btn button-primary" data-assign_action="save"><?php _e( 'Save Selections But Stay On This Page', 'wpas-mail' ); ?></button>
		<br />	<br />	
		<hr />
		<a href="<?php echo get_delete_post_link( $post->ID, '', true ); ?>" class="submitdelete deletion" onClick="return confirm('<?php _e( 'Are you sure? There is no coming back!', 'wpas-mail' ); ?>')"><?php _e( 'Delete', 'wpas-mail' ); ?></a>
	</div>
</div>