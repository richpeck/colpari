<?php 
if( empty( $tickets ) ) {
	_e( 'There are no tickets', 'wpas-rw' );
	return;
}
$count = 0;

?>

<div class="as-widget">

<?php foreach( $tickets as $key => $report ) :
	if( ! empty( $report ) ) :
?>

	<?php 
		// This file includes very important variables that will be used later below when 
		// setting up chart display options and the icon used for the window/widget
		include WPASRW_PATH . 'includes/widgets/templates/partials/chart-config.php' ; 
	?>
	
	<?php if( $single == false ) : ?>		
		<span class="as-agent-name"><i class="fa <?php echo $icon; ?> " aria-hidden="true"></i> <?php echo $key; ?></span>
	<?php endif;
					
		$graph_id = "as-metric-graph-" . ++$count;
		if( ! empty( $template_for ) ) {
			$graph_id .= "-" . $template_for;
		}
 	?>
	
	<div class="as-widget-row">
		<div id="<?php echo $graph_id ?>" class="as-metric-graph">
			<?php
				/**
				 * Set the label here for i18n
				*/
				$today 		= __( 'Today', 'wpas-rw' );
				$yesterday 	= __( 'Yesterday', 'wpas-rw' );
				$two_days 	= __( '2 Days', 'wpas-rw' );
				$three_days = __( '3 Days', 'wpas-rw' );
				$four_days 	= __( '4 days', 'wpas-rw' );
				$five_days 	= __( '5 days', 'wpas-rw' );
			?>
			
			<?php 
			// Render the canvasjs javascript in-line
			include WPASRW_PATH . 'includes/widgets/templates/partials/canvasjs.php' ; 
			?>
		
		</div><!-- .as-metric-graph -->

		<div class="as-metric-group as-metric-group-singular">
			<h3 class="as-metric-number"><?php echo $report['5days_up'] ?></h3><!-- .as-metric-title -->
			<span class="as-metric-label"><?php _e( 'over 6 days', 'wpas-rw' ); ?></span><!-- .as-metric-title -->
		</div><!-- .as-metric-group -->
	</div><!-- .widget-row -->
<?php
	endif;
endforeach; ?>

</div><!-- .as-widget -->
