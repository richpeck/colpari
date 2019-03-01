<?php
/******************************************************************************************
This file contains the primary canvasjs javascript.  It references some variables from 

All variables available in the calling file is also available here!

Some variables used here that are from the open-tickets.php file include:
	$template_for
	$chart_element_color
	$chart_line_thickness
	$chart_type
	$chart_data_point_width
	chart_color_day_0, chart_color_day_1, etc.
	
********************************************************************************************/
?> 

			<script type="text/javascript">
				jQuery(document).ready(function($) {
					var chart = new CanvasJS.Chart("<?php echo $graph_id ?>", {
						theme: '<?php echo $chart_theme; ?>',
						colorSet: '<?php echo $chart_colorset; ?>',
					    axisY: {
					        gridColor: "<?php echo $chart_color_grid_lines; ?>" , 
					        labelFontColor: '<?php echo $chart_color_y_axis_label_font; ?>',
					        lineColor: '<?php echo $chart_color_y_axis_line; ?>',
							lineThickness: 1,
					        tickColor: 'transparent',
					        margin: <?php echo $chart_margin; ?>,
					    },
					    axisX: {
					        labelFontColor: '<?php echo $chart_color_x_axis_label_font; ?>',							
					        lineColor: '<?php echo $chart_color_x_axis_line; ?>',  
							lineThickness: 1,
					        tickLength: 5,
					        tickColor: 'transparent',
							<?php echo $chart_x_axis_label_font_size_string ?>  // insert entire string for font size for x-axis if it exists otherwise this wil be just a blank line
					    },
					    toolTip: {
					    	enabled: true,
					    },
					    dataPointWidth: <?php echo $chart_data_point_width; ?>,   
						data: [{
							color: '<?php echo $chart_color_other; ?>',   
							type: '<?php echo $chart_type; ?>',
							lineThickness: <?php echo $chart_line_thickness; ?>,
					        lineColor: '<?php echo $chart_element_color; ?>',
					        markerColor: 'white',
					        markerBorderColor: '#bbb',
					        markerBorderThickness: 1,
					        markerSize: 5,
							indexLabel: "{y}",
						    indexLabelPlacement: "outside",  
						    indexLabelOrientation: "horizontal",
						    indexLabelFontColor: '#000',							
							dataPoints: [
								<?php include WPASRW_PATH . 'includes/widgets/templates/partials/open-tickets-datapoints.php' ; ?>
							]
						}],
					});
					chart.render();
				});
			</script>	