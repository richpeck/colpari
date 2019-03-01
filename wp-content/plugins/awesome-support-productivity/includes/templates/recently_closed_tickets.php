
<?php if( 0 !== count( $tickets ) ) : ?>

<div class="user_profile_tickets">
	
	<table class="wp-list-table widefat fixed striped posts">
		<thead>
			<tr>
				<th><?php _e( "ID", "wpas_productivity" ); ?></th>
				<th><?php _e( "Title", "wpas_productivity" ); ?></th>
				<th><?php _e( "Close Date", "wpas_productivity" ); ?></th>
				<th><?php _e( "Status", "wpas_productivity" ); ?></th>
				<th><?php _e( "Activity", "wpas_productivity" ); ?></th>
			</tr>
		</thead>
		<?php 
		
		do_action( 'wpas_start_ticket_listing' );
		
		foreach( $tickets as $ticket ) {
			$close_date = get_post_meta( $ticket->ID, '_ticket_closed_on', true );
			
		?>
		<tr>
			<td><?php WPAS_Tickets_List::get_instance()->custom_columns_content( 'id' , $ticket->ID ); ?></td>
			<td><?php echo _draft_or_post_title( $ticket->ID ); ?></td>
			<td><?php echo $close_date; ?></td>
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