<?php
if( empty( $tickets ) ) {
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
		<?php foreach ( $tickets as $status => $count ) : ?>
			<?php if ( $count > 0 ) { ?>
				<div class="as-metric-group">
					<h3 class="as-metric-number"><?php echo $count; ?></h3><!-- .as-metric-title -->
					<span class="as-metric-label"><?php echo $status; ?></span><!-- .as-metric-title -->			
				</div><!-- .as-metric-group -->	
			<?php } ?>
		<?php endforeach; ?>

	</div><!-- .widget-row -->
</div><!-- .as-widget -->
