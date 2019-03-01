<?php

/**
 * Return an array of colors from the settings table
 *
 * @return array color values
 * @since  2.0.0
 */
function wpas_rw_get_color_array( ) {
	$colors = array () ;
	
	$colors[1] = wpas_get_option( 'asrw_default_chart_color_day_0' );
	$colors[2] = wpas_get_option( 'asrw_default_chart_color_day_1' );
	$colors[3] = wpas_get_option( 'asrw_default_chart_color_day_2' );
	$colors[4] = wpas_get_option( 'asrw_default_chart_color_day_3' );
	$colors[5] = wpas_get_option( 'asrw_default_chart_color_day_4' );
	$colors[6] = wpas_get_option( 'asrw_default_chart_color_day_5' ); 
	$colors[7] = wpas_get_option( 'asrw_default_chart_color_addl_pt_1' ); 
	$colors[8] = wpas_get_option( 'asrw_default_chart_color_addl_pt_2' ); 
	$colors[9] = wpas_get_option( 'asrw_default_chart_color_addl_pt_3' ); 
	$colors[10] = wpas_get_option( 'asrw_default_chart_color_addl_pt_4' ); 
	
	return $colors;
	
}


/**
 * Accepts an array in a particular format and returns a single value that is the sum of certain elements in the array.
 *
 * The array is in the following format:
		Array
			(
				[today] => 0
				[1day] => 0
				[2days] => 0
				[3days] => 0
				[4days] => 0
				[5days] => 0
				[5days_up] => 8
			)

 *
 * @param $tickets array 
 *
 * @return int
 *
 * @since  2.0.0
 */

function sum_tickets_in_aging_array($tickets) {
	$ttl_tickets = 0 ;  // initialize total variable.

	isset( $tickets['today'] ) ? $ttl_tickets = $ttl_tickets + $tickets['today'] : $ttl_tickets ;
	isset( $tickets['1day'] ) ? $ttl_tickets = $ttl_tickets + $tickets['1day'] : $ttl_tickets ;
	isset( $tickets['2days'] ) ? $ttl_tickets = $ttl_tickets + $tickets['2days'] : $ttl_tickets ;
	isset( $tickets['3days'] ) ? $ttl_tickets = $ttl_tickets + $tickets['3days'] : $ttl_tickets ;
	isset( $tickets['4days'] ) ? $ttl_tickets = $ttl_tickets + $tickets['4days'] : $ttl_tickets ;
	isset( $tickets['5days'] ) ? $ttl_tickets = $ttl_tickets + $tickets['5days'] : $ttl_tickets ;
	isset( $tickets['5days_up'] ) ? $ttl_tickets = $ttl_tickets + $tickets['5days_up'] : $ttl_tickets ;

	return $ttl_tickets ;
}

/**
 * Returns the max bars configured for a chart report/widget based on the name passed.
 *
 * @return int max bars defaulted to 5
 *
 * @since  2.0.0
 */
function get_max_report_bars($report_id = '') {

	$max_bars = 0 ;
	
	switch ( $report_id ) {
		case 'product-summary-chart':
			$max_bars = wpas_get_option( 'asrw_product_summary_chart_report_max_bars') ;
			break ;
			
		case 'priority-summary-chart':
			$max_bars = wpas_get_option( 'asrw_priority_summary_chart_report_max_bars') ;
			break ;
			
		case 'channel-summary-chart':
			$max_bars = wpas_get_option( 'asrw_channel_summary_chart_report_max_bars') ;
			break ;			
			
		case 'department-summary-chart':
			$max_bars = wpas_get_option( 'asrw_dept_summary_chart_report_max_bars') ;
			break ;			
	}
	
	if ( empty( $max_bars ) ) {
		$max_bars = 5 ;
	}
	
	return $max_bars ;

	
}

?>