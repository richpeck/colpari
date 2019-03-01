
<?php if( 0 !== count( $tickets ) ) : ?>

<div class="user_profile_tickets">
	
	<table class="wp-list-table widefat fixed striped posts">
		<thead>
			<tr>
				<th><?php _e( "ID", "wpas_productivity" ); ?></th>
				<th><?php _e( "Title", "wpas_productivity" ); ?></th>
				<th><?php _e( "Created By", "wpas_productivity" ); ?></th>
				<th><?php _e( "Agent", "wpas_productivity" ); ?></th>
				<th><?php _e( "Status", "wpas_productivity" ); ?></th>
				<th><?php _e( "Activity", "wpas_productivity" ); ?></th>
			</tr>
		</thead>
		<?php 
		
		do_action( 'wpas_start_ticket_listing' );
		
		foreach( $tickets as $ticket ) {
			
			$client = get_user_by( 'id', $ticket->post_author );
			$link = add_query_arg( array( 'post_type' => 'ticket', 'author' => $client->ID ), admin_url( 'edit.php' ) );
		?>
		<tr>
			<td><?php WPAS_Tickets_List::get_instance()->custom_columns_content( 'id' , $ticket->ID ); ?></td>
			<td><?php echo _draft_or_post_title( $ticket->ID ); ?></td>
			<td><?php echo "<a href='$link'>$client->display_name</a>"; ?></td>
			<td><?php WPAS_Tickets_List::get_instance()->custom_columns_content( 'assignee' , $ticket->ID ); ?></td>
			<td><?php WPAS_Tickets_List::get_instance()->custom_columns_content( 'status' , $ticket->ID ); ?></td>
			<td><?php WPAS_Tickets_List::get_instance()->custom_columns_content( 'wpas-activity' , $ticket->ID ); ?></td>
		</tr>
		<?php 
		
		} 
		
		do_action( 'wpas_end_ticket_listing' );
		
		?>
			
	</table>
</div>

<?php endif; ?>