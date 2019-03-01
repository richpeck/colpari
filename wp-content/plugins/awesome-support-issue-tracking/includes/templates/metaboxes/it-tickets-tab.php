
<?php if( 0 !== count( $tickets ) ) : ?>

<div class="user_profile_tickets">
	<h3><?php _e( "Tickets", "wpas_it" ); ?></h3>
	<table class="wp-list-table widefat fixed striped posts">
		<thead>
			<tr>
				<th><?php _e( "ID", "wpas_it" ); ?></th>
				<th><?php _e( "Title", "wpas_it" ); ?></th>
				<th><?php _e( "Last Reply Date", "wpas_it" ); ?></th>
				<th><?php _e( "Status", "wpas_it" ); ?></th>
				<th><?php _e( "Activity", "wpas_it" ); ?></th>
			</tr>
		</thead>
		<?php 
		foreach( $tickets as $ticket ) {
			
			$replies = WPAS_Tickets_List::get_instance()->get_replies_query( $ticket->ID );
			$last_reply_date = "";
			if ( 0 !== $replies->post_count ) {
				$last_reply = $replies->posts[ $replies->post_count - 1 ];
				$last_reply_date = get_the_time( __( 'Y/m/d' ), $last_reply );
			}
			
		?>
		<tr>
			<td><?php WPAS_Tickets_List::get_instance()->custom_columns_content( 'id' , $ticket->ID ); ?></td>
			<td><?php echo _draft_or_post_title( $ticket->ID ); ?></td>
			<td><?php echo $last_reply_date; ?></td>
			<td><?php WPAS_Tickets_List::get_instance()->custom_columns_content( 'status' , $ticket->ID ); ?></td>
			<td><?php WPAS_Tickets_List::get_instance()->custom_columns_content( 'wpas-activity' , $ticket->ID ); ?></td>
		</tr>
		<?php } ?>
			
	</table>
</div>

<?php endif; ?>