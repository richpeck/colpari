<?php 
if( empty( $tickets ) ) {
	_e( 'There are no tickets', 'wpas-rw' );
	return;
}
$count = 0;

?>

<div class="as-widget">

	<?php 
		// This file includes very important variables that will be used later below when 
		// setting up chart display options and the icon used for the window/widget
		include WPASRW_PATH . 'includes/widgets/templates/partials/chart-config.php' ; 
	?>

	<!--
	// Just below are dynamically generated styles to make sure that SUMMARY charts can fit in the widget space and not overflow it.
	// This is a workdaround because existing CSS isn't working properly and summary charts end up overlapping each other.
	// It uses the admin widget id to force a HEIGHT style.
	// Note that the $this variable referenced is part of the PARENT class!
	-->
	<style TYPE="text/css">
	<!--
		<?php	
		if ( $admin_widget_height > 0 ) {
			echo '#'. $this->widget_slug . ' {' ;
			echo 'height: ' . (string) $admin_widget_height . 'px;';
			echo '}' ;
		}
		?>
	-->
	</style>
	
	<?php
					
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

	</div><!-- .widget-row -->

</div><!-- .as-widget -->
