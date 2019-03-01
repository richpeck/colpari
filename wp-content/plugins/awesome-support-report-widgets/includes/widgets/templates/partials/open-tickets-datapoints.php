<?php
/******************************************************************************************
This file contains the datapoint logic used for canvasjs charts in the open-tickets.php 
All variables available in that file is also available here!

Some variables used here that are from the open-tickets.php file include:
	$template_for
	$report
	$today, $yesterday, $two_days etc.
	$chart_color_day_0, $chart_color_day_1 etc.
	$tickets
********************************************************************************************/
?>

<?php
switch ( $template_for ){
	
	case 'custom-status-summary-chart':
		/* The $tickets array holds data that looks as follows: 
			[New] => 206
			[In Progress] => 88
			[On Hold] => 2		
		*/

		$icount = 0 ;  // counter inside the for-loop below
		
		$imaxbars = wpas_get_option( 'asrw_status_report_max_bars') ;  // maximum number of bars to render since we have limited space
		if ( empty( $imaxbars ) ) {
			$imaxbars = 5 ;
		}
		
		$colors = wpas_rw_get_color_array() ;  // get a list of colors to use for charts

		$status_excludes = wpas_get_option( 'asrw_status_chart_excludes') ; // Do not show these statuses on chart - should be comma separated list of values
		
		foreach ($tickets as $chartlabel => $chart_value) {	
			
			// Check to see if status value is allowed on chart.  It not the continue to the next value...
			if ( ! empty (strstr( $status_excludes, $chartlabel ) ) ) {
				continue;
			}
		
			// Good to go but we will only plot non-zero values since space is small
			if ( $chart_value <> 0 ) {		
			
				// What color to use for the data element if this turns out to be a barchart/column chart?
				$color = '';
				if ( $icount <= 9 && isset( $colors[$icount+1] ) ) {
					$color = $colors[$icount+1];
				}
				
				// Show index labels?
				$index_label_string = '' ;
				$index_label_orientation = '';
				if ( true === $chart_show_index_labels) {
					
					$index_label_string = ', indexLabel:' . '"' . $chartlabel. '(' . (string) $chart_value . ')' . '"' ;
					$index_label_orientation = ', indexLabelOrientation:' . '"' . $chart_index_label_direction . '"' ;

				}				
				
				?>
				
				<!-- set the data element -->
				{ y: <?php echo $chart_value; ?>, label: "<?php echo $chartlabel; ?>", color: '<?php echo $color; ?>' <?php echo $index_label_string; ?> <?php echo $index_label_orientation; ?> },
				
				<?php
			
				$icount++;
			}
			
			// Can only show a maximum of $imaxbars values since space is tiny
			if ( $icount >= $imaxbars ) {
				break ;
			}
			
		} // end for each
		break ;
		
	case 'product-summary-chart':
	case 'priority-summary-chart':
	case 'channel-summary-chart':
	case 'department-summary-chart':
	case 'agent-summary-chart':
		/* The $tickets array holds data that looks as follows: 
			[Product 6] => Array
					(
						[today] => 0
						[1day] => 0
						[2days] => 0
						[3days] => 0
						[4days] => 0
						[5days] => 0
						[5days_up] => 3
					)
		*/

		$icount = 0 ;  // counter inside the for-loop below

		$imaxbars = get_max_report_bars( $template_for );  // maximum number of bars to render since we have limited space

		$colors = wpas_rw_get_color_array() ;  // get a list of colors to use for charts

		foreach ($tickets as $chartlabel => $chart_values_array) {	
			
			// Get the total number of tickets...		
			$chart_value = sum_tickets_in_aging_array($chart_values_array) ;  // This sum function is in the functions/general.php file
			
			// Good to go but we will only plot non-zero values since space is small
			if ( $chart_value <> 0 ) {		
			
				// What color to use for the data element if this turns out to be a barchart/column chart?
				$color = '';
				if ( $icount <= 9 && isset( $colors[$icount+1] ) ) {
					$color = $colors[$icount+1];
				}
				
				// Show index labels?
				$index_label_string = '' ;
				$index_label_orientation = '';
				if ( true === $chart_show_index_labels) {
					
					$index_label_string = ', indexLabel:' . '"' . $chartlabel. '(' . (string) $chart_value . ')' . '"' ;
					$index_label_orientation = ', indexLabelOrientation:' . '"' . $chart_index_label_direction . '"' ;

				}
			
				?>
				<!-- set the data element -->
				{ y: <?php echo $chart_value; ?>, label: "<?php echo $chartlabel; ?>", color: '<?php echo $color; ?>' <?php echo $index_label_string; ?> <?php echo $index_label_orientation; ?>   },
				
				<?php
			
				$icount++;
			}
			
			// Can only show a maximum of $imaxbars values since space is tiny
			if ( $icount >= $imaxbars ) {
				break ;
			}
		} // end for each
		break ;		
		
	default:
		// report arrays formatted with today/yesterday etc.
		?>
		{ y: <?php echo $report['today']; ?>, label: "<?php echo $today; ?>", color: '<?php echo $chart_color_day_0; ?>'  },
		{ y: <?php echo $report['1day']; ?>, label: "<?php echo $yesterday; ?>", color: '<?php echo $chart_color_day_1; ?>'  },
		{ y: <?php echo $report['2days']; ?>, label: "<?php echo $two_days; ?>", color: '<?php echo $chart_color_day_2; ?>'  },
		{ y: <?php echo $report['3days']; ?>, label: "<?php echo $three_days; ?>", color: '<?php echo $chart_color_day_3; ?>' },
		{ y: <?php echo $report['4days']; ?>, label: "<?php echo $four_days; ?>", color: '<?php echo $chart_color_day_4; ?>'  },
		{ y: <?php echo $report['5days']; ?>, label: "<?php echo $five_days; ?>", color: '<?php echo $chart_color_day_5; ?>'  },
		<?php
}
?>