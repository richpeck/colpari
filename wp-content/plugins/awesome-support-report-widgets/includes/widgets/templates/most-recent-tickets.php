<?php

if( empty( $tickets ) ) {
	_e( 'There are no tickets', 'wpas-rw' );
	return;
}

$tickets_to_show = count( $tickets );
?>
<div class="as-recent-tickets-wrapper as-widget">
	<div class="as-recent-tickets-list">
<?php
foreach ( $tickets as $ticket ) :
	$ticket_url = get_edit_post_link( $ticket );
	$ticket_timestamp = get_post_time( 'U', true, $ticket );
	$time_diff = human_time_diff( $ticket_timestamp, current_time( 'U', true ) );
	$tickets_to_show = count( $tickets );
?>	
	<a href="<?php echo $ticket_url; ?>" class="as-recent-ticket-item">
		<span class="as-recent-ticket-status"></span>
		<strong class="as-recent-ticket-title"><?php echo $ticket->post_title; ?></strong><!-- .as-recent-ticket-title -->
		<em class="as-recent-ticket-date"><?php echo $time_diff; ?></em>
	</a><!-- .as-recent-ticket-item -->
<?php endforeach; ?>
	</div><!-- .as-recent-tickets-list -->

	<footer class="as-recent-tickets-footer">
		<form class='wpas-reports-recent-tickets'>
			<input type='number' name='tickets_num' min='0' placeholder='Tickets to show: <?php echo $tickets_to_show; ?>'>
			<button class="button" type='submit'><?php _e( 'Submit', 'wpas-rw' ) ?></button>
		</form>
	</footer><!-- .as-recent-tickets-footer -->
</div><!-- .as-recent-tickets-wrapper -->

