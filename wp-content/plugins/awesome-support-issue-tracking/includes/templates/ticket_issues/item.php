

<div class="wpas_it_ui_item" data-item_id="<?php echo $item_id ?>">
		
				
	<table class="wp-list-table widefat fixed striped posts">
		<tbody>
				
				<tr>
					<td width="12%"><?php echo $item->getID(); ?></td>
					<td width="30%">
							<a class="row-title" href="<?php echo add_query_arg( array( 'action' => 'edit', 'post' => $item->getID() ), admin_url( 'post.php' ) ); ?>">
							<?php echo $item->getTitle(); ?>
							</a>
					</td>
					<td width="18%"></td>
					<td width="24%"><?php echo $item->display_status(); ?></td>
					<td width="16%">

						<?php if( $this->user_can_add() ) : ?>
						<ul class="actions">
							<li><a href="#" data-action="<?php echo $this->actionName('delete'); ?>" class="wpas_it_ui_item_action wpas_it_ui_item_action_delete" data-confirm="<?php _e( "Are you sure you want to delete this issue?" ) ?>"><?php _e( 'Delete', 'wpas_it' ); ?></a></li>
						</ul>
						<?php endif; ?>
						<div class="clear clearfix"></div>
					</td>
				</tr>
		</tbody>
	</table>
	<table class="wp-list-table widefat fixed striped posts it_issue_tickets">
		
		<?php 
		
		
		
		do_action( 'wpas_it_start_ticket_listing' );
		
		$tickets = $item->getTickets();
		
		WPAS_Tickets_List::get_instance()->add_custom_fields( array() );
		
		foreach( $tickets as $ticket ) {

			$replies = WPAS_Tickets_List::get_instance()->get_replies_query( $ticket->ID );
			$last_reply_date = "";
			if ( 0 !== $replies->post_count ) {
				$last_reply = $replies->posts[ $replies->post_count - 1 ];
				$last_reply_date = get_the_time( __( 'Y/m/d' ), $last_reply );
			}

		?>
			
			
		<tr>
			<td width="2%"></td>
			<td width="10%"><?php WPAS_Tickets_List::get_instance()->custom_columns_content( 'id' , $ticket->ID ); ?></td>
			<td width="30%"><?php echo _draft_or_post_title( $ticket->ID ); ?></td>
			<td width="18%"><?php echo $last_reply_date; ?></td>
			<td width="16%"><?php WPAS_Tickets_List::get_instance()->custom_columns_content( 'status' , $ticket->ID ); ?></td>
			<td width="24%"><?php WPAS_Tickets_List::get_instance()->custom_columns_content( 'wpas-activity' , $ticket->ID ); ?></td>
		</tr>
		<?php
		
		
		}
		do_action( 'wpas_it_end_ticket_listing' );
		
		?>

	</table>
		
</div>