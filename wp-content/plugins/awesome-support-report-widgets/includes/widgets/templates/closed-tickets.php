<?php
if( empty( $tickets ) ) {
	_e( 'There are no tickets', 'wpas-rw' );
	return;
}

foreach( $tickets as $key => $report ) :
	if( ! empty( $report ) ) :
?>
<div class="as-widget">
	<?php if( $single == false ) : ?><span class="as-agent-name"><?php echo $key ?></span><?php endif; ?>

	<div class="as-widget-row as-closed-ticket-labels">
		<div class="as-closed-ticket-label today">
			<span class="as-closed-ticket-number"><?php echo $report['today']; ?></span>
			<span class="as-closed-ticket-title"><?php _e( 'Today', 'wpas-rw' ); ?></span>
		</div>

		<div class="as-closed-ticket-label">
			<span class="as-closed-ticket-number"><?php echo $report['yesterday']; ?></span>
			<span class="as-closed-ticket-title"><?php _e( 'Yesterday', 'wpas-rw' ) ?></span>
		</div><!-- .as-closed-ticket-label -->

		<div class="as-closed-ticket-label">
			<span class="as-closed-ticket-number"><?php echo $report['this_week']; ?></span>
			<span class="as-closed-ticket-title"><?php _e( 'This week', 'wpas-rw' ) ?></span>
		</div><!-- .as-closed-ticket-label -->

		<div class="as-closed-ticket-label">
			<span class="as-closed-ticket-number"><?php echo $report['last_week']; ?></span>
			<span class="as-closed-ticket-title"><?php _e( 'Last week', 'wpas-rw' ) ?></span>
		</div><!-- .as-closed-ticket-label -->
	</div><!-- .as-row -->


	<div class="as-widget-row as-closed-ticket-labels">
		<div class="as-closed-ticket-label">
			<span class="as-closed-ticket-number"><?php echo $report['this_month']; ?></span>
			<span class="as-closed-ticket-title"><?php _e( 'This month', 'wpas-rw' ) ?></span>
		</div><!-- .as-closed-ticket-label -->

		<div class="as-closed-ticket-label">
			<span class="as-closed-ticket-number"><?php echo $report['last_month']; ?></span>
			<span class="as-closed-ticket-title"><?php _e( 'Last month', 'wpas-rw' ) ?></span>
		</div><!-- .as-closed-ticket-label -->

		<div class="as-closed-ticket-label">
			<span class="as-closed-ticket-number"><?php echo $report['this_year']; ?></span>
			<span class="as-closed-ticket-title"><?php _e( 'This year', 'wpas-rw' ) ?></span>
		</div><!-- .as-closed-ticket-label -->

		<div class="as-closed-ticket-label">
			<span class="as-closed-ticket-number"><?php echo $report['last_year']; ?></span>
			<span class="as-closed-ticket-title"><?php _e( 'Last year', 'wpas-rw' ) ?></span>
		</div><!-- .as-closed-ticket-label -->
	</div><!-- .as-row -->

	<div class="as-widget-row as-closed-ticket-labels">
		<div class="as-closed-ticket-label total">
			<span class="as-closed-ticket-number"><?php echo $report['all_time']; ?></span>
			<span class="as-closed-ticket-title"><?php _e( 'All time', 'wpas-rw' ) ?></span>
		</div><!-- .as-closed-ticket-label -->
	</div><!-- .as-row -->

</div><!-- .as-widget -->
<?php
	endif;
endforeach;
?>



