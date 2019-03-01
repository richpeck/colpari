<?php
if( empty( $tickets ) || ! array_key_exists( 'open_tickets', $tickets ) || ! array_key_exists( 'closed_tickets', $tickets ) ) {
	_e( 'There are no tickets', 'wpas-rw' );
	return;
}
?>

<!--
	* Developer note:
	* ==========================================================================
	* This is the markup only with static data
	* Using the .as-<class-name> prefix for all classes inside the awesome support
	* widgets. The stylings come from sass file where changing the prefix is easy.
-->
<div class="as-widget">
	<div class="as-widget-row">
		<div class="as-metric-group as-tickets-opened">
			<h3 class="as-metric-number"><?php echo $tickets['open_tickets'] ?></h3><!-- .as-metric-title -->
			<span class="as-metric-label"><?php _e( 'Opened', 'wpas-rw') ?></span><!-- .as-metric-title -->
		</div><!-- .as-metric-group -->

		<div class="as-metric-group as-tickets-closed">
			<h3 class="as-metric-number"><?php echo $tickets['closed_tickets'] ?></h3><!-- .as-metric-title -->
			<span class="as-metric-label"><?php _e( 'Closed', 'wpas-rw') ?></span><!-- .as-metric-title -->
		</div><!-- .as-metric-group -->

		<div class="as-button-column">
			<a href="<?php echo admin_url( 'edit.php?post_type=ticket' ); ?>" class="as-button"><i class="fa fa-eye fa-1x" aria-hidden="true"></i> <?php _e( 'View open tickets', 'wpas-rw') ?></a>
		</div><!-- .as-button-column -->
	</div><!-- .widget-row -->
</div><!-- .as-widget -->
