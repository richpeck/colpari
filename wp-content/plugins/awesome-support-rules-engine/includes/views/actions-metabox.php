<section class="conditions-wrapper">
    <?php
	$exclude = array(
		'action_note_ticket',
		'action_reply_ticket',
		'action_edit_ticket_user',
		'action_note_ticket_user',
		'action_send_email',
		'action_asre_email_titles',
		'action_call_webhook',
		'action_zapier_notification',
		'action_execute_http_action',
		'action_execute_http_action_text',
		'action_assignee',
		'action_assignee_template',
		'action_customer',
		'action_customer_template',
		'action_secondary_assignee',
		'action_secondary_assignee_template',
		'action_tertiary_assignee',
		'action_tertiary_assignee_template',
		'action_first_interested_party',
		'action_first_interested_party_template',
		'action_second_interested_party',
		'action_second_interested_party_template',
	);

	if( ! empty ( $actions ) ) {
		foreach( $actions as $action_key => $action ) {
			if ( !in_array($action_key, $exclude) ) {
				$action->render_field();
			}
		}
	}?>
	<div id="mytabs">
		<?php
			$current_user_role = $this->wpas_rules_get_current_user_role();
			$actions_tab_array = array(
				'action_note_ticket' => __( 'Add Note To Ticket', 'as-rules-engine' ),
				'action_reply_ticket' => __( 'Add Reply To Ticket', 'as-rules-engine' ),
				'action_call_webhook' => __( 'Webhook / HTTP Action', 'as-rules-engine' ),
				'action_send_email' => __( 'Send Email', 'as-rules-engine' ),
				'action_zapier_notification' => __( 'Zapier', 'as-rules-engine' ),
			);
			$actions_tab_ids = array(
				'action_note_ticket' => 'add-note-2-ticket',
				'action_reply_ticket' => 'add-reply-2-ticket',
				'action_call_webhook' => 'webhook-http-action',
				'action_send_email' => 'send-email-tab',
				'action_zapier_notification' => 'zapier-tab',
			);
			if ( isset( $actions ) && ! empty( $actions ) ) {
				echo '<ul class="category-tabs">';
				foreach ( $actions_tab_array as $key => $value ) {

					if ( ( isset( $actions[ $key ] ) && ! empty( $actions[ $key ] ) ) ||  'administrator' === $current_user_role ||  'administer_awesome_support' === $current_user_role ) {
						echo '<li><a href="#' . $actions_tab_ids[ $key ] . '">' . $value . '</a></li>';
					}
				}
				echo '</ul>';
			}
			echo '<br class="clear" />';
		?>
		<?php if ( isset( $actions['action_note_ticket'] ) ) :  ?>
			<div id="add-note-2-ticket">
				<?php if ( isset( $actions['action_note_ticket_user'] ) ) { $actions['action_note_ticket_user']->render_field(); } ?>
				<?php if ( isset( $actions['action_note_ticket'] ) ) { $actions['action_note_ticket']->render_field(); } ?>
			</div>
		<?php endif; ?>
		<?php if ( isset( $actions['action_reply_ticket'] ) ) :  ?>
			<div class="hidden" id="add-reply-2-ticket">
				<?php if ( isset( $actions['action_edit_ticket_user'] ) ) { $actions['action_edit_ticket_user']->render_field(); } ?>
				<?php if ( isset( $actions['action_reply_ticket'] ) ) { $actions['action_reply_ticket']->render_field(); } ?>
			</div>
		<?php endif; ?>
		<?php if ( isset( $actions['action_call_webhook'] ) ) :  ?>
			<div class="hidden" id="webhook-http-action">
				<?php if ( isset( $actions['action_call_webhook'] ) ) { $actions['action_call_webhook']->render_field(); } ?>
				<?php if ( isset( $actions['action_execute_http_action'] ) ) { $actions['action_execute_http_action']->render_field(); } ?>
				<div class='condition'>
					<h3 style="padding-left: 15px;"><?php _e( 'Select data fields', 'as-rules-engine' ); ?></h3>			
					<?php
					$hooks_data = get_post_meta( get_the_ID(),'hooks_notification_data', true );
					$hooks_fields = array(
						'title' => 'Ticket Title',
						'creator' => 'Ticket creator',
						'support' => 'Agent',
						'status' => 'Current status',
						'trigger' => 'Awesome Trigger',
						'state' => 'State',
						'custom_fields' => 'Custom Fields',
						'client_arttributes' => 'Client Attributes',
						'agent_arttributes' => 'Agent Attributes',
						'ticket_contents' => 'Ticket Contents',
						'ticket_excerpt' => 'Ticket Excerpt',
						'reply_contents' => 'Reply Contents',
						'note_contents' => 'Note Contents',
					);
					?>
					<ul>
						<?php foreach ( $hooks_fields as $tagname => $taglabel ) : ?>
						<li class="condition action_change_state">
							<label>
								<input type='checkbox' name='hooks_data[]' value='<?php echo $tagname; ?>'<?php if ( ! empty( $hooks_data ) && in_array( $tagname, $hooks_data ) ) :   echo ' checked /'; endif; ?>>
								<?php echo $taglabel; ?>
							</label>
						</li>	
						<?php endforeach; ?>
						<?php do_action( 'more_hooks_datafields', $hooks_data );?>
						
					</ul>
				</div><!-- condition -->
			</div>
		<?php endif; ?>
		<?php if ( isset( $actions['action_send_email'] ) ) :  ?>
			<div class="hidden" id="send-email-tab">
				<p class="description"><?php echo sprintf( '(<i>%s</i>)', __( 'Add multiple emails seperated by comma.', 'as-rules-engine' ) );?></p>
				<?php if ( isset( $actions['action_send_email'] ) ) { $actions['action_send_email']->render_field(); } ?>
				<?php if ( isset( $actions['action_asre_email_titles'] ) ) { $actions['action_asre_email_titles']->render_field(); } ?>
				<?php if ( $multiple_agents || $third_party ) :  ?>
					<div class="condition">
						<label><strong><?php _e( 'Also send emails to:', 'as-rules-engine' ) ?></strong></label>
					</div>
				<?php endif; ?>
				<?php
				$action_assignee = get_post_meta( get_the_ID(), 'action_assignee', true );
				$action_customer = get_post_meta( get_the_ID(), 'action_customer', true );
				$action_assignee_check = isset( $action_assignee['value'] ) ? $action_assignee['value']:false;
				$action_customer_check = isset( $action_customer['value'] ) ? $action_customer['value']:false;
				?>
				<!--  Add Assignee and Customer fields here -->	
				<?php if ( isset( $actions['action_assignee_template'] ) ) :  ?>
					<div class="multiple-recipent-checkbox">
						<input name="action_assignee" id="action_assignee" type="checkbox" <?php echo ( $action_assignee_check ) ? ' checked':''; ?> />				
					</div>			
					<?php $actions['action_assignee_template']->render_field();?>
				<?php endif; ?>
				
				<?php if ( isset( $actions['action_customer_template'] ) ) :  ?>
					<div class="multiple-recipent-checkbox">
						<input name="action_customer" id="action_customer" type="checkbox" <?php echo ( $action_customer_check ) ? ' checked':''; ?>/>
					</div>
					<?php $actions['action_customer_template']->render_field();?>
				<?php endif; ?>
				<?php if ( $multiple_agents ) :  ?>
					<?php
					$secondary_assignee = get_post_meta( get_the_ID(), 'action_secondary_assignee', true );
					$tertiary_assignee = get_post_meta( get_the_ID(), 'action_tertiary_assignee', true );
					$sa_check = isset( $secondary_assignee['value'] ) ? $secondary_assignee['value']:false;
					$ta_check = isset( $tertiary_assignee['value'] ) ? $tertiary_assignee['value']:false;
					?>
					<?php if ( isset( $actions['action_secondary_assignee_template'] ) ) {?>
					<div class="multiple-recipent-checkbox">
						<input name="action_secondary_assignee" id="action_secondary_assignee" type="checkbox"<?php echo ( $sa_check ) ? ' checked':''; ?>>
					</div>
					<?php $actions['action_secondary_assignee_template']->render_field(); } ?>
					<?php if ( isset( $actions['action_tertiary_assignee_template'] ) ) { ?>
					<div class="multiple-recipent-checkbox">
						<input name="action_tertiary_assignee" id="action_tertiary_assignee" type="checkbox"<?php echo ( $ta_check ) ? ' checked':''; ?>>
					</div>
					<?php $actions['action_tertiary_assignee_template']->render_field(); } ?>
				<?php endif; ?>
				<?php if ( $third_party ) :  ?>
					<?php
					$first_interested_party = get_post_meta( get_the_ID(), 'action_first_interested_party', true );
					$second_interested_party = get_post_meta( get_the_ID(), 'action_second_interested_party', true );
					$fip_check = isset( $first_interested_party['value'] ) ? $first_interested_party['value']:false;
					$sip_check = isset( $second_interested_party['value'] ) ? $second_interested_party['value']:false;
					?>
					<?php if ( isset( $actions['action_first_interested_party_template'] ) ) { ?>
					<div class="multiple-recipent-checkbox">
						<input name="action_first_interested_party" id="action_first_interested_party" type="checkbox"<?php echo ( $fip_check ) ? ' checked':''; ?>>
					</div>
					<?php $actions['action_first_interested_party_template']->render_field(); } ?>
					<?php if ( isset( $actions['action_second_interested_party_template'] ) ) { ?>
					<div class="multiple-recipent-checkbox">
						<input name="action_second_interested_party" id="action_second_interested_party" type="checkbox"<?php echo ( $sip_check ) ? ' checked':''; ?>>
					</div>
					<?php $actions['action_second_interested_party_template']->render_field(); } ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if ( isset( $actions['action_zapier_notification'] ) ) :  ?>
		 	<div class="hidden" id="zapier-tab">   
				<?php if ( isset( $actions['action_zapier_notification'] ) ) {
					$actions['action_zapier_notification']->render_field(); } ?>
				<div class='condition'>
					<h3 style="padding-left: 15px;"><?php _e( 'Select data fields', 'as-rules-engine' ); ?></h3>			
					<?php
					$zapier_data = get_post_meta( get_the_ID(),'zapier_notification_data', true );
					$zapier_fields = array(
						'title' => 'Ticket Title',
						'creator' => 'Ticket creator',
						'support' => 'Agent',
						'status' => 'Current status',
						'trigger' => 'Awesome Trigger',
						'state' => 'State',
						'custom_fields' => 'Custom Fields',
						'client_arttributes' => 'Client Attributes',
						'agent_arttributes' => 'Agent Attributes',
						'ticket_contents' => 'Ticket Contents',
						'ticket_excerpt' => 'Ticket Excerpt',
						'reply_contents' => 'Reply Contents',
						'note_contents' => 'Note Contents',
					);
					?>
					<ul>
						<?php foreach ( $zapier_fields as $tagname => $taglabel ) : ?>
						<li class="condition action_change_state">
							<label>
								<input type='checkbox' name='ticket_data[]' value='<?php echo $tagname; ?>'<?php if ( ! empty( $zapier_data ) && in_array( $tagname, $zapier_data ) ) :   echo ' checked /'; endif; ?>>
								<?php echo __( $taglabel, 'as-rules-engine' ); ?>
							</label>
						</li>	
						<?php endforeach; ?>
						<?php do_action( 'more_zapier_datafields', $zapier_data );?>
						<li class="condition action_change_state">
							<label>
								<?php
									$test_zap_array = array(
									      'title' => 'test11119999',
									      'creator' => 'paidsupportcustomer',
									      'support' => 'supportagent',
									      'status' => 'On Hold',
									      'trigger' => 'ticket_updated',
									      'state' => 'open',
									      'product_custom_field' => 'Immediate Support - One Time Purchase',
									      'admin_only_custom_field' => 'Standard Ticket Form',
									      'modelxx' => 'NNN',
									      'as_year_manufactured' => 2014,
										      'as_cf_box_types' => array( 'Medium' ),
									      'as_cf_ordercomments' => 'Order comment text',
									      'as_cf_shipping_method' => 'ups',
									      'as_cf_colors' => array( 'red','blue' ),
									      'ramnish_field' => array( 'first option', 'Second Option' ),
									      'as_cf_lastused' => '2017-08-25',
									      'as_cf_addl_desc' => 'some text',
									      'client_ID' => 134,
									      'client_user_login' => 'paidsupportcustomer',
									      'client_user_nicename' => 'paidsupportcustomer',
									      'client_user_email' => 'client@gmail.com',
									      'client_user_registered' => '2017-05-29 09:28:27',
									      'client_display_name' => 'Paid Support User',
									      'agent_ID' => 149,
									      'agent_user_login' => 'supportagent',
									      'agent_user_nicename' => 'supportagent',
									      'agent_user_email' => 'supportagent54@gmail.com',
									      'agent_user_registered' => '2017-08-11 15:38:04',
									      'agent_display_name' => 'Support Agent',
									      'ticket_contents' => 'Ticket content',
									      'ticket_excerpt' => 'Ticket short content',
									      'reply_contents' => 'Reply 2',
									      'note_contents' => 'Some note2',
									      'reply_id' => 12,
									      'ticket_id' => 1112,
									);
									$test_zap_json = json_encode( $test_zap_array );
								?>
								<input type='hidden' id='rules-testzap-data' value='<?php echo $test_zap_json; ?>'>
								<a onclick="sendZap(this)" class="button-secondary"><?php _e( 'Send Test Zap', 'as-rules-engine' ); ?></a>
							</label>
							<span id="message-status"></span>
						</li>	
						
					</ul>
				</div><!-- condition -->
			</div>
		<?php endif; ?>
	</div>
</section>


