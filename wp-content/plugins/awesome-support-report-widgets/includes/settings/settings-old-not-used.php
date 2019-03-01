<?php
add_filter( 'wpas_plugin_settings', 'wpas_asrw_settings', 5, 1 );
/**
 * Add misc useful settings
 *
 * @param  array $def Array of existing settings
 *
 * @return array      Updated settings
 */
function wpas_asrw_settings( $def ) {
	$settings = array(
		'asrwsettings' => array(
			'name'    => __( 'Report Widgets', 'wpas-rw' ),
			'options' => array(
				array(
					'name' => __( 'General Reports', 'wpas-rw' ),
					'desc' => __( 'This section include options to turn on and off the basic reports', 'wpas-rw' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'Show Open Tickets Widget?', 'wpas-rw' ),
					'id'      => 'asrw_open_tickets',
					'type'    => 'checkbox',
					'desc'    => __( 'The open tickets widget is a simple chart of the count of open tickets by day for the last 5 days', 'wpas-rw' ),
					'default' => true
				),
				array(
					'name'    => __( 'Show Closed Tickets Widget?', 'wpas-rw' ),
					'id'      => 'asrw_closed_tickets',
					'type'    => 'checkbox',
					'desc'    => __( 'The closed tickets widget shows the number of tickets closed for 8 different periods', 'wpas-rw' ),
					'default' => true
				),
				array(
					'name'    => __( 'Show The Recent Tickets Widget?', 'wpas-rw' ),
					'id'      => 'asrw_most_recent_tickets',
					'type'    => 'checkbox',
					'desc'    => __( 'This widget will show the last 5 tickets opened in Awesome Suppport', 'wpas-rw' ),
					'default' => true
				),

				array(
					'name' => __( 'Tickets By Status', 'wpas-rw' ),
					'type' => 'heading',
					'desc'    => __( 'The tickets by status widgets include two separate widgets for OPEN tickets. One displays numbers and the other shows a chart. Both widgets show a count of open tickets by status.', 'wpas-rw' ),
				),
				array(
					'name'    => __( 'Open Tickets By Status Summary', 'wpas-rw' ),
					'id'      => 'asrw_open_tickets_by_status_summary',
					'type'    => 'checkbox',
					'desc'    => __( 'Show the open tickets by status summary report widget on the admin dashboard?', 'wpas-rw' ),
					'default' => true
				),
				array(
					'name'    => __( 'Open Tickets By Status Chart', 'wpas-rw' ),
					'id'      => 'asrw_open_tickets_by_status_chart',
					'type'    => 'checkbox',
					'desc'    => __( 'Show the open tickets by status chart report widget on the admin dashboard?', 'wpas-rw' ),
					'default' => true
				),
				array(
					'name'    => __( 'Chart Type For Report', 'wpas-rw' ),
					'id'      => 'asrw_status_chart_type',
					'desc'	  => __( 'Please select one of the 9 chart types above. If you select a bar or column chart you might want to change the default data point width in the default section below to something smaller.', 'wpas-rw' ),
					'type'    => 'select',
					'default' => 'bar',
					'options' => array(
						'line'       => __( 'Line', 'wpas-rw' ),
						'column'     => __( 'Column', 'wpas-rw' ),
						'bar'        => __( 'Bar', 'wpas-rw' ),
						'spline'     => __( 'Spline', 'wpas-rw' ),
						'area'       => __( 'Area', 'wpas-rw' ),
						'pie'        => __( 'Pie', 'wpas-rw' ),						
						'splineArea' => __( 'Spline Area', 'wpas-rw' ),
						'stepLine'   => __( 'Step Line', 'wpas-rw' ),
						'stepArea'   => __( 'Step Area', 'wpas-rw' ),
					)					
				),
				array(
					'name'    => __( 'Color For Chart Elements', 'wpas-rw' ),
					'id'      => 'asrw_status_chart_elements_color',
					'desc'    => __( 'Sets the color of the line used in the open tickets by status line charts', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#bbb',
				),
				array(
					'name'    => __( 'Width For Data Points', 'wpas-rw' ),
					'id'      => 'asrw_status_report_data_point_width',
					'desc'	  => __( 'The width of chart elements such as bars and columns - generally used for charts that are not line charts', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '0',
					'max'	  => '100',
				),				
				array(
					'name'    => __( 'Maximum Number Of Statuses To Show On Chart', 'wpas-rw' ),
					'id'      => 'asrw_status_report_max_bars',
					'desc'	  => __( 'The chart area is small so its best to limit the number of statuses to 5 or less', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '5',
					'min'	  => '3',
					'max'	  => '25',
				),
				array(
					'name'    => __( 'Statuses To Exclude', 'wpas-rw' ),
					'id'      => 'asrw_status_chart_excludes',
					'desc'    => __( 'Exclude these statuses from the chart - separate each value with a comma. The values entered here should be the user visible text, NOT the slug. Note: This is an experimental feature.', 'wpas-rw' ),
					'type'    => 'textarea',
				),				
				
				array(
					'name' => __( 'Product Summary Chart Widget', 'wpas-rw' ),
					'type' => 'heading',
					'desc'    => __( 'This widget shows the open tickets for each product on a single chart', 'wpas-rw' ),
				),
				array(
					'name'    => __( 'Show The Product Summary Chart?', 'wpas-rw' ),
					'id'      => 'asrw_open_tickets_by_product_summary_chart',
					'type'    => 'checkbox',
					'desc'    => __( 'Displays the number of open tickets for each product - one bar or data point for each product is shown on a single chart', 'wpas-rw' ),
					'default' => true
				),
				array(
					'name'    => __( 'Chart Type For Report', 'wpas-rw' ),
					'id'      => 'asrw_product_summary_chart_type',
					'desc'	  => __( 'Please select one of the 9 chart types above. If you select a bar or column chart you might want to change the default data point width in the default section below to something smaller.', 'wpas-rw' ),
					'type'    => 'select',
					'default' => 'bar',
					'options' => array(
						'line'       => __( 'Line', 'wpas-rw' ),
						'column'     => __( 'Column', 'wpas-rw' ),
						'bar'        => __( 'Bar', 'wpas-rw' ),
						'spline'     => __( 'Spline', 'wpas-rw' ),
						'area'       => __( 'Area', 'wpas-rw' ),
						'pie'        => __( 'Pie', 'wpas-rw' ),						
						'splineArea' => __( 'Spline Area', 'wpas-rw' ),
						'stepLine'   => __( 'Step Line', 'wpas-rw' ),
						'stepArea'   => __( 'Step Area', 'wpas-rw' ),
					)					
				),
				array(
					'name'    => __( 'Color For Chart Elements', 'wpas-rw' ),
					'id'      => 'asrw_product_summary_chart_elements_color',
					'desc'    => __( 'Sets the color of the line used in the open tickets by product line charts', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#bbb',
				),
				array(
					'name'    => __( 'Width For Data Points', 'wpas-rw' ),
					'id'      => 'asrw_product_summary_chart_data_point_width',
					'desc'	  => __( 'The width of chart elements such as bars and columns - generally used for charts that are not line charts', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '0',
					'max'	  => '100',
				),
				array(
					'name'    => __( 'Maximum Number Of Products To Show On Chart', 'wpas-rw' ),
					'id'      => 'asrw_product_summary_chart_report_max_bars',
					'desc'	  => __( 'The chart area is small so its best to limit the number of products to 5 or less', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '5',
					'min'	  => '3',
					'max'	  => '25',
				),
				
				array(
					'name' => __( 'Product Detail Report Widgets', 'wpas-rw' ),
					'type' => 'heading',
					'desc'    => __( 'There are two widgets for product details. One shows charts based on tickets that are still open; the other shows numbers for closed tickets. Each widget shows one chart or block of numbers for each product - which means that these two widgets can be quite lengthy if you have a lot of products', 'wpas-rw' ),
				),
				array(
					'name'    => __( 'Open Tickets By Product (Charts)', 'wpas-rw' ),
					'id'      => 'asrw_opened_tickets_by_product',
					'type'    => 'checkbox',
					'desc'    => __( 'Show the open tickets by product aging report widget on the admin dashboard?', 'wpas-rw' ),
					'default' => true
				),
				array(
					'name'    => __( 'Closed Tickets By Product', 'wpas-rw' ),
					'id'      => 'asrw_closed_tickets_by_product',
					'type'    => 'checkbox',
					'desc'    => __( 'Show the closed tickets by product aging report widget on the admin dashboard?', 'wpas-rw' ),
					'default' => true
				),
				array(
					'name'    => __( 'Chart Type For Product Reports', 'wpas-rw' ),
					'id'      => 'asrw_product_chart_type',
					'desc'	  => __( 'Please select one of the 9 chart types above. If you select a bar or column chart you might want to change the default data point width in the default section below to something smaller.', 'wpas-rw' ),
					'type'    => 'select',
					'default' => 'line',
					'options' => array(
						'line'       => __( 'Line', 'wpas-rw' ),
						'column'     => __( 'Column', 'wpas-rw' ),
						'bar'        => __( 'Bar', 'wpas-rw' ),
						'spline'     => __( 'Spline', 'wpas-rw' ),
						'area'       => __( 'Area', 'wpas-rw' ),
						'pie'        => __( 'Pie', 'wpas-rw' ),						
						'splineArea' => __( 'Spline Area', 'wpas-rw' ),
						'stepLine'   => __( 'Step Line', 'wpas-rw' ),
						'stepArea'   => __( 'Step Area', 'wpas-rw' ),
					)					
				),
				array(
					'name'    => __( 'Color For Product Report Chart Elements', 'wpas-rw' ),
					'id'      => 'asrw_product_chart_elements_color',
					'desc'    => __( 'Sets the color of the line used in the product line charts', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#bbb',
				),
				
				array(
					'name' => __( 'Priority Summary Chart Widget', 'wpas-rw' ),
					'type' => 'heading',
					'desc'    => __( 'This widget shows the open tickets for each priority on a single chart', 'wpas-rw' ),
				),
				array(
					'name'    => __( 'Show The Priority Summary Chart?', 'wpas-rw' ),
					'id'      => 'asrw_open_tickets_by_priority_summary_chart',
					'type'    => 'checkbox',
					'desc'    => __( 'Displays the number of open tickets for each priority - one bar or data point for each priority is shown on a single chart', 'wpas-rw' ),
					'default' => true
				),
				array(
					'name'    => __( 'Chart Type For Report', 'wpas-rw' ),
					'id'      => 'asrw_priority_summary_chart_type',
					'desc'	  => __( 'Please select one of the 9 chart types above. If you select a bar or column chart you might want to change the default data point width in the default section below to something smaller.', 'wpas-rw' ),
					'type'    => 'select',
					'default' => 'bar',
					'options' => array(
						'line'       => __( 'Line', 'wpas-rw' ),
						'column'     => __( 'Column', 'wpas-rw' ),
						'bar'        => __( 'Bar', 'wpas-rw' ),
						'spline'     => __( 'Spline', 'wpas-rw' ),
						'area'       => __( 'Area', 'wpas-rw' ),
						'pie'        => __( 'Pie', 'wpas-rw' ),						
						'splineArea' => __( 'Spline Area', 'wpas-rw' ),
						'stepLine'   => __( 'Step Line', 'wpas-rw' ),
						'stepArea'   => __( 'Step Area', 'wpas-rw' ),
					)					
				),
				array(
					'name'    => __( 'Color For Chart Elements', 'wpas-rw' ),
					'id'      => 'asrw_priority_summary_chart_elements_color',
					'desc'    => __( 'Sets the color of the line used in the open tickets by priority line charts', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#bbb',
				),
				array(
					'name'    => __( 'Width For Data Points', 'wpas-rw' ),
					'id'      => 'asrw_priority_summary_chart_data_point_width',
					'desc'	  => __( 'The width of chart elements such as bars and columns - generally used for charts that are not line charts', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '0',
					'max'	  => '100',
				),
				array(
					'name'    => __( 'Maximum Number Of Priorities To Show On Chart', 'wpas-rw' ),
					'id'      => 'asrw_priority_summary_chart_report_max_bars',
					'desc'	  => __( 'The chart area is small so its best to limit the number of priorities to 5 or less', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '5',
					'min'	  => '3',
					'max'	  => '25',
				),
				
				array(
					'name' => __( 'Priority Detail Report Widgets', 'wpas-rw' ),
					'type' => 'heading',
					'desc'    => __( 'There are two widgets for priority details. One shows charts based on tickets that are still open; the other shows numbers for closed tickets. Each widget shows one chart or block of numbers for each priority - which means that these two widgets can be quite lengthy if you have a lot of priorities defined.', 'wpas-rw' ),					
				),
				array(
					'name'    => __( 'Open Tickets By Priority (Charts)', 'wpas-rw' ),
					'id'      => 'asrw_opened_tickets_by_priority',
					'type'    => 'checkbox',
					'desc'    => __( 'Show the open tickets by priority aging report widget on the admin dashboard?', 'wpas-rw' ),
					'default' => true
				),
				array(
					'name'    => __( 'Closed Tickets By priority', 'wpas-rw' ),
					'id'      => 'asrw_closed_tickets_by_priority',
					'type'    => 'checkbox',
					'desc'    => __( 'Show the closed tickets by priority aging report widget on the admin dashboard?', 'wpas-rw' ),
					'default' => true
				),
				array(
					'name'    => __( 'Chart Type For Priority Reports', 'wpas-rw' ),
					'id'      => 'asrw_priority_chart_type',
					'desc'	  => __( 'Please select one of the 9 chart types above. If you select a bar or column chart you might want to change the default data point width in the default section below to something smaller.', 'wpas-rw' ),
					'type'    => 'select',
					'default' => 'line',
					'options' => array(
						'line'       => __( 'Line', 'wpas-rw' ),
						'column'     => __( 'Column', 'wpas-rw' ),
						'bar'        => __( 'Bar', 'wpas-rw' ),
						'spline'     => __( 'Spline', 'wpas-rw' ),
						'area'       => __( 'Area', 'wpas-rw' ),
						'pie'        => __( 'Pie', 'wpas-rw' ),						
						'splineArea' => __( 'Spline Area', 'wpas-rw' ),
						'stepLine'   => __( 'Step Line', 'wpas-rw' ),
						'stepArea'   => __( 'Step Area', 'wpas-rw' ),
					)					
				),				
				array(
					'name'    => __( 'Color For Priority Report Chart Elements', 'wpas-rw' ),
					'id'      => 'asrw_priority_chart_elements_color',
					'desc'    => __( 'Sets the color of the line used in the priority line charts', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#bbb',
				),
				
				array(
					'name' => __( 'Channel Summary Chart Widget', 'wpas-rw' ),
					'type' => 'heading',
					'desc'    => __( 'This widget shows the open tickets for each channel on a single chart', 'wpas-rw' ),
				),
				array(
					'name'    => __( 'Show The Channel Summary Chart?', 'wpas-rw' ),
					'id'      => 'asrw_open_tickets_by_channel_summary_chart',
					'type'    => 'checkbox',
					'desc'    => __( 'Displays the number of open tickets for each channel - one bar or data point for each channel is shown on a single chart', 'wpas-rw' ),
					'default' => true
				),
				array(
					'name'    => __( 'Chart Type For Report', 'wpas-rw' ),
					'id'      => 'asrw_channel_summary_chart_type',
					'desc'	  => __( 'Please select one of the 9 chart types above. If you select a bar or column chart you might want to change the default data point width in the default section below to something smaller.', 'wpas-rw' ),
					'type'    => 'select',
					'default' => 'bar',
					'options' => array(
						'line'       => __( 'Line', 'wpas-rw' ),
						'column'     => __( 'Column', 'wpas-rw' ),
						'bar'        => __( 'Bar', 'wpas-rw' ),
						'spline'     => __( 'Spline', 'wpas-rw' ),
						'area'       => __( 'Area', 'wpas-rw' ),
						'pie'        => __( 'Pie', 'wpas-rw' ),						
						'splineArea' => __( 'Spline Area', 'wpas-rw' ),
						'stepLine'   => __( 'Step Line', 'wpas-rw' ),
						'stepArea'   => __( 'Step Area', 'wpas-rw' ),
					)					
				),
				array(
					'name'    => __( 'Color For Chart Elements', 'wpas-rw' ),
					'id'      => 'asrw_channel_summary_chart_elements_color',
					'desc'    => __( 'Sets the color of the line used in the open tickets by channel line charts', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#bbb',
				),
				array(
					'name'    => __( 'Width For Data Points', 'wpas-rw' ),
					'id'      => 'asrw_channel_summary_chart_data_point_width',
					'desc'	  => __( 'The width of chart elements such as bars and columns - generally used for charts that are not line charts', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '0',
					'max'	  => '100',
				),
				array(
					'name'    => __( 'Maximum Number Of Channels To Show On Chart', 'wpas-rw' ),
					'id'      => 'asrw_channel_summary_chart_report_max_bars',
					'desc'	  => __( 'The chart area is small so its best to limit the number of channels to 5 or less', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '5',
					'min'	  => '3',
					'max'	  => '25',
				),				

				array(
					'name' => __( 'Channel Detail Report Widgets', 'wpas-rw' ),
					'type' => 'heading',
					'desc'    => __( 'There are two widgets for channel details. One shows charts based on tickets that are still open; the other shows numbers for closed tickets. Each widget shows one chart or block of numbers for each channel - which means that these two widgets can be quite lengthy if you have a lot of channels that are actively used.', 'wpas-rw' ),										
				),
				array(
					'name'    => __( 'Open Tickets By Channel (Charts)', 'wpas-rw' ),
					'id'      => 'asrw_opened_tickets_by_channel',
					'type'    => 'checkbox',
					'desc'    => __( 'Show the open tickets by channel aging report widget on the admin dashboard?', 'wpas-rw' ),
					'default' => true
				),
				array(
					'name'    => __( 'Closed Tickets By Channel', 'wpas-rw' ),
					'id'      => 'asrw_closed_tickets_by_channel',
					'type'    => 'checkbox',
					'desc'    => __( 'Show the closed tickets by channel aging report widget on the admin dashboard?', 'wpas-rw' ),
					'default' => true
				),
				array(
					'name'    => __( 'Chart Type For Channel Reports', 'wpas-rw' ),
					'id'      => 'asrw_channel_chart_type',
					'desc'	  => __( 'Please select one of the 9 chart types above. If you select a bar or column chart you might want to change the default data point width in the default section below to something smaller.', 'wpas-rw' ),
					'type'    => 'select',
					'default' => 'line',
					'options' => array(
						'line'       => __( 'Line', 'wpas-rw' ),
						'column'     => __( 'Column', 'wpas-rw' ),
						'bar'        => __( 'Bar', 'wpas-rw' ),
						'spline'     => __( 'Spline', 'wpas-rw' ),
						'area'       => __( 'Area', 'wpas-rw' ),
						'pie'        => __( 'Pie', 'wpas-rw' ),						
						'splineArea' => __( 'Spline Area', 'wpas-rw' ),
						'stepLine'   => __( 'Step Line', 'wpas-rw' ),
						'stepArea'   => __( 'Step Area', 'wpas-rw' ),
					)					
				),				
				array(
					'name'    => __( 'Color For Channel Report Chart Elements', 'wpas-rw' ),
					'id'      => 'asrw_channel_chart_elements_color',
					'desc'    => __( 'Sets the color of the line used in the channel line charts', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#bbb',
				),
				
				array(
					'name' => __( 'Department Summary Chart Widget', 'wpas-rw' ),
					'type' => 'heading',
					'desc'    => __( 'This widget shows the open tickets for each department on a single chart', 'wpas-rw' ),
				),
				array(
					'name'    => __( 'Show The Department Summary Chart?', 'wpas-rw' ),
					'id'      => 'asrw_open_tickets_by_dept_summary_chart',
					'type'    => 'checkbox',
					'desc'    => __( 'Displays the number of open tickets for each department - one bar or data point for each department is shown on a single chart', 'wpas-rw' ),
					'default' => true
				),
				array(
					'name'    => __( 'Chart Type For Report', 'wpas-rw' ),
					'id'      => 'asrw_dept_summary_chart_type',
					'desc'	  => __( 'Please select one of the 9 chart types above. If you select a bar or column chart you might want to change the default data point width in the default section below to something smaller.', 'wpas-rw' ),
					'type'    => 'select',
					'default' => 'bar',
					'options' => array(
						'line'       => __( 'Line', 'wpas-rw' ),
						'column'     => __( 'Column', 'wpas-rw' ),
						'bar'        => __( 'Bar', 'wpas-rw' ),
						'spline'     => __( 'Spline', 'wpas-rw' ),
						'area'       => __( 'Area', 'wpas-rw' ),
						'pie'        => __( 'Pie', 'wpas-rw' ),						
						'splineArea' => __( 'Spline Area', 'wpas-rw' ),
						'stepLine'   => __( 'Step Line', 'wpas-rw' ),
						'stepArea'   => __( 'Step Area', 'wpas-rw' ),
					)					
				),
				array(
					'name'    => __( 'Color For Chart Elements', 'wpas-rw' ),
					'id'      => 'asrw_dept_summary_chart_elements_color',
					'desc'    => __( 'Sets the color of the line used in the open tickets by department line charts', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#bbb',
				),
				array(
					'name'    => __( 'Width For Data Points', 'wpas-rw' ),
					'id'      => 'asrw_dept_summary_chart_data_point_width',
					'desc'	  => __( 'The width of chart elements such as bars and columns - generally used for charts that are not line charts', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '0',
					'max'	  => '100',
				),
				array(
					'name'    => __( 'Maximum Number Of Departments To Show On Chart', 'wpas-rw' ),
					'id'      => 'asrw_dept_summary_chart_report_max_bars',
					'desc'	  => __( 'The chart area is small so its best to limit the number of departments to 5 or less', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '5',
					'min'	  => '3',
					'max'	  => '25',
				),				
				
				array(
					'name' => __( 'Department Detail Report Widgets (Charts)', 'wpas-rw' ),
					'type' => 'heading',
					'desc'    => __( 'There are two widgets for department details. One shows charts based on tickets that are still open; the other shows numbers for closed tickets. Each widget shows one chart or block of numbers for each department - which means that these two widgets can be quite lengthy if you have a lot of departments.', 'wpas-rw' ),										
				),
				array(
					'name'    => __( 'Open Tickets By Department', 'wpas-rw' ),
					'id'      => 'asrw_opened_tickets_by_department',
					'type'    => 'checkbox',
					'desc'    => __( 'Show the open tickets by department aging report widget on the admin dashboard?', 'wpas-rw' ),
					'default' => true
				),
				array(
					'name'    => __( 'Closed Tickets By Department', 'wpas-rw' ),
					'id'      => 'asrw_closed_tickets_by_department',
					'type'    => 'checkbox',
					'desc'    => __( 'Show the closed tickets by department aging report widget on the admin dashboard?', 'wpas-rw' ),
					'default' => true
				),
				array(
					'name'    => __( 'Chart Type For Department Reports', 'wpas-rw' ),
					'id'      => 'asrw_department_chart_type',
					'desc'	  => __( 'Please select one of the 9 chart types above. If you select a bar or column chart you might want to change the default data point width in the default section below to something smaller.', 'wpas-rw' ),
					'type'    => 'select',
					'default' => 'line',
					'options' => array(
						'line'       => __( 'Line', 'wpas-rw' ),
						'column'     => __( 'Column', 'wpas-rw' ),
						'bar'        => __( 'Bar', 'wpas-rw' ),
						'spline'     => __( 'Spline', 'wpas-rw' ),
						'area'       => __( 'Area', 'wpas-rw' ),
						'pie'        => __( 'Pie', 'wpas-rw' ),						
						'splineArea' => __( 'Spline Area', 'wpas-rw' ),
						'stepLine'   => __( 'Step Line', 'wpas-rw' ),
						'stepArea'   => __( 'Step Area', 'wpas-rw' ),
					)					
				),				
				array(
					'name'    => __( 'Color For Department Report Chart Elements', 'wpas-rw' ),
					'id'      => 'asrw_department_chart_elements_color',
					'desc'    => __( 'Sets the color of the line used in the department line charts', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#bbb',
				),				

				array(
					'name' => __( 'Agent Summary Chart Widget', 'wpas-rw' ),
					'type' => 'heading',
					'desc'    => __( 'This widget shows the open tickets for each agent on a single chart', 'wpas-rw' ),
				),
				array(
					'name'    => __( 'Show The Agent Summary Chart?', 'wpas-rw' ),
					'id'      => 'asrw_open_tickets_by_agent_summary_chart',
					'type'    => 'checkbox',
					'desc'    => __( 'Displays the number of open tickets for each agent - one bar or data point for each agent is shown on a single chart', 'wpas-rw' ),
					'default' => true
				),
				array(
					'name'    => __( 'Chart Type For Report', 'wpas-rw' ),
					'id'      => 'asrw_agent_summary_chart_type',
					'desc'	  => __( 'Please select one of the 9 chart types above. If you select a bar or column chart you might want to change the default data point width in the default section below to something smaller.', 'wpas-rw' ),
					'type'    => 'select',
					'default' => 'bar',
					'options' => array(
						'line'       => __( 'Line', 'wpas-rw' ),
						'column'     => __( 'Column', 'wpas-rw' ),
						'bar'        => __( 'Bar', 'wpas-rw' ),
						'spline'     => __( 'Spline', 'wpas-rw' ),
						'area'       => __( 'Area', 'wpas-rw' ),
						'pie'        => __( 'Pie', 'wpas-rw' ),						
						'splineArea' => __( 'Spline Area', 'wpas-rw' ),
						'stepLine'   => __( 'Step Line', 'wpas-rw' ),
						'stepArea'   => __( 'Step Area', 'wpas-rw' ),
					)					
				),
				array(
					'name'    => __( 'Color For Chart Elements', 'wpas-rw' ),
					'id'      => 'asrw_agent_summary_chart_elements_color',
					'desc'    => __( 'Sets the color of the line used in the open tickets by agent line charts', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#bbb',
				),
				array(
					'name'    => __( 'Width For Data Points', 'wpas-rw' ),
					'id'      => 'asrw_agent_summary_chart_data_point_width',
					'desc'	  => __( 'The width of chart elements such as bars and columns - generally used for charts that are not line charts', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '0',
					'max'	  => '100',
				),
				array(
					'name'    => __( 'Chart Margin', 'wpas-rw' ),
					'id'      => 'asrw_agent_summary_chart_margin',
					'desc'	  => __( 'Set margin for this chart measured from left of chart container.  The best is zero but negative values are allowed.', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '0',
					'min'	  => '-100',
					'max'	  => '100',
				),				
				array(
					'name'    => __( 'Maximum Number Of Agents To Show On Chart', 'wpas-rw' ),
					'id'      => 'asrw_agent_summary_chart_report_max_bars',
					'desc'	  => __( 'The chart area is small so its best to limit the number of agents to 5 or less', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '5',
					'min'	  => '1',
					'max'	  => '25',
				),
				array(
					'name'    => __( 'Color For X-axis', 'wpas-rw' ),
					'id'      => 'asrw_agent_summary_chart_report_x-axis_color',
					'desc'    => __( 'Sets the color used for the x-axis line for this chart', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#CDCDCD',
				),
				array(
					'name'    => __( 'Color For Y-axis', 'wpas-rw' ),
					'id'      => 'asrw_agent_summary_chart_report_y-axis_color',
					'desc'    => __( 'Sets the color used for the y-axis line for this chart', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#CDCDCD',
				),
				
				array(
					'name'    => __( 'X-axis Font Color', 'wpas-rw' ),
					'id'      => 'asrw_agent_summary_chart_report_x-axis_font_color',
					'type'    => 'color',
					'default' => '#000',
				),
				array(
					'name'    => __( 'Y-axis Font Color', 'wpas-rw' ),
					'id'      => 'asrw_agent_summary_chart_report_y-axis_font_color',
					'type'    => 'color',
					'default' => 'transparent',
				),
				array(
					'name'    => __( 'X-axis Font Size', 'wpas-rw' ),
					'id'      => 'asrw_agent_summary_chart_report_x-axis_font_size',
					'type'    => 'number',
					'desc'    => __( 'Sets the size of the font used for the x-axis line for this chart. Set to zero for automatic sizing. Change to a manual value if all data-points on the x-axis are not being labelled and you need to see the labels.', 'wpas-rw' ),
					'default' => '0',
					'max'	  => '25',
				),
				array(
					'name'    => __( 'Y-axis Font Size', 'wpas-rw' ),
					'id'      => 'asrw_agent_summary_chart_report_y-axis_font_size',
					'desc'    => __( 'Sets the size of the font used for the y-axis line for this chart. Set to zero for automatic sizing.', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '0',
					'max'	  => '25',
				),				
				array(
					'name'    => __( 'Show Index Labels?', 'wpas-rw' ),
					'id'      => 'asrw_agent_summary_chart_report_show_index_labels',
					'type'    => 'checkbox',
					'desc'    => __( 'Index labels appear at the top of bar/column charts and in tooltips for each data point on the chart', 'wpas-rw' ),
					'default' => true
				),
				array(
					'name'    => __( 'Index Label Direction', 'wpas-rw' ),
					'id'      => 'asrw_agent_summary_chart_report_index_label_direction',
					'type'    => 'radio',
					'desc'    => __( 'Horizontal is appropriate for bar charts while vertical is appropriate for column charts', 'wpas-rw' ),
					'options' => array(
						'vertical'       => __( 'Vertical', 'wpas-rw' ),
						'horizontal'     => __( 'Horizontal', 'wpas-rw' ),
					),					
					'default' => ''
				),				
				
				array(
					'name' => __( 'Agent Detail Report Widgets (Charts)', 'wpas-rw' ),
					'type' => 'heading',
					'desc'    => __( 'There are two widgets for agent details. One shows charts based on tickets that are still open; the other shows numbers for closed tickets. Each widget shows one chart or block of numbers for each agent - which means that these two widgets can be quite lengthy if you have a lot of agents defined.', 'wpas-rw' ),										
				),
				array(
					'name'    => __( 'Open Tickets By Agent', 'wpas-rw' ),
					'id'      => 'asrw_opened_tickets_by_agent',
					'type'    => 'checkbox',
					'desc'    => __( 'Show the open tickets by agent aging report widget on the admin dashboard?', 'wpas-rw' ),
					'default' => true
				),
				array(
					'name'    => __( 'Closed Tickets By Agent', 'wpas-rw' ),
					'id'      => 'asrw_closed_tickets_by_agent',
					'type'    => 'checkbox',
					'desc'    => __( 'Show the closed tickets by agent aging report widget on the admin dashboard?', 'wpas-rw' ),
					'default' => true
				),
				array(
					'name'    => __( 'Chart Type For Agent Reports', 'wpas-rw' ),
					'id'      => 'asrw_agent_chart_type',
					'desc'	  => __( 'Please select one of the 9 chart types above. If you select a bar or column chart you might want to change the default data point width in the default section below to something smaller.', 'wpas-rw' ),
					'type'    => 'select',
					'default' => 'line',
					'options' => array(
						'line'       => __( 'Line', 'wpas-rw' ),
						'column'     => __( 'Column', 'wpas-rw' ),
						'bar'        => __( 'Bar', 'wpas-rw' ),
						'spline'     => __( 'Spline', 'wpas-rw' ),
						'area'       => __( 'Area', 'wpas-rw' ),
						'pie'        => __( 'Pie', 'wpas-rw' ),						
						'splineArea' => __( 'Spline Area', 'wpas-rw' ),
						'stepLine'   => __( 'Step Line', 'wpas-rw' ),
						'stepArea'   => __( 'Step Area', 'wpas-rw' ),
					)					
				),				
				array(
					'name'    => __( 'Color For Agent Report Chart Elements', 'wpas-rw' ),
					'id'      => 'asrw_agent_chart_elements_color',
					'desc'    => __( 'Sets the color of the line used in the agent line chart reports', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#bbb',
				),

				array(
					'name' => __( 'Chart Defaults', 'wpas-rw' ),
					'desc' => __( 'Set default colors for certain chart elements', 'wpas-rw' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'Default Color For Chart Elements', 'wpas-rw' ),
					'id'      => 'asrw_default_chart_elements_color',
					'type'    => 'color',
					'default' => '#bbb',
				),
				array(
					'name'    => __( 'Default Line Width For Line Charts', 'wpas-rw' ),
					'id'      => 'asrw_default_chart_line_thickness',
					'type'    => 'number',
					'default' => '1',
					'min'	  => '1',
					'max'	  => '10',
				),
				array(
					'name'    => __( 'Default Width For Data Points', 'wpas-rw' ),
					'id'      => 'asrw_default_data_point_width',
					'desc'	  => __( 'The width of chart elements such as bars and columns - generally used for charts that are not line charts', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '0',
					'max'	  => '100',
				),
				array(
					'name'    => __( 'Default Margins', 'wpas-rw' ),
					'id'      => 'asrw_default_chart_margin',
					'desc'	  => __( 'Offset of chart from left margin - negative margins are valid', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '-15',
					'min'	  => '-100',	
					'max'	  => '100',
				),				
				array(
					'name'    => __( 'Default Chart Type', 'wpas-rw' ),
					'id'      => 'asrw_default_chart_type',
					'desc'	  => __( 'Please select one of the 9 chart types below. If you select a bar or column chart you might want to change the data point width above to something smaller.', 'wpas-rw' ),
					'type'    => 'radio',
					'default' => 'line',
					'options' => array(
						'line'       => __( 'Line', 'wpas-rw' ),
						'column'     => __( 'Column', 'wpas-rw' ),
						'bar'        => __( 'Bar', 'wpas-rw' ),
						'spline'     => __( 'Spline', 'wpas-rw' ),
						'area'       => __( 'Area', 'wpas-rw' ),
						'pie'        => __( 'Pie', 'wpas-rw' ),						
						'splineArea' => __( 'Spline Area', 'wpas-rw' ),
						'stepLine'   => __( 'Step Line', 'wpas-rw' ),
						'stepArea'   => __( 'Step Area', 'wpas-rw' ),
					)					
				),
				array(
					'name'    => __( 'Default Chart Theme', 'wpas-rw' ),
					'id'      => 'asrw_default_chart_theme',
					'desc'	  => __( 'BETA: Option for future use', 'wpas-rw' ),
					'type'    => 'radio',
					'default' => 'theme1',
					'options' => array(
						''   	   => __( 'None', 'wpas-rw' ),
						'theme1'   => __( 'Theme #1', 'wpas-rw' ),
						'theme2'   => __( 'Theme #2', 'wpas-rw' ),
						'theme3'   => __( 'Theme #3', 'wpas-rw' ),
					)					
				),
				array(
					'name'    => __( 'Default Colorset', 'wpas-rw' ),
					'id'      => 'asrw_default_chart_colorset',
					'desc'	  => __( 'BETA: Option for future use', 'wpas-rw' ),
					'type'    => 'radio',
					'default' => 'colorSet1',
					'options' => array(
						''          => __( 'None', 'wpas-rw' ),
						'colorSet1'   => __( 'Set #1', 'wpas-rw' ),
						'colorSet2'   => __( 'Set #2', 'wpas-rw' ),
						'colorSet3'   => __( 'Set #3', 'wpas-rw' ),
					)					
				),

				array(
					'name' => __( 'Default Colors For Barcharts and Column Charts', 'wpas-rw' ),
					'desc' => __( 'Set the colors used for each column/bar on the charts', 'wpas-rw' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'Default Color Today', 'wpas-rw' ),
					'id'      => 'asrw_default_chart_color_day_0',
					'type'    => 'color',
					'default' => '#369EAD',
				),
				array(
					'name'    => __( 'Default Color Yesterday', 'wpas-rw' ),
					'id'      => 'asrw_default_chart_color_day_1',
					'type'    => 'color',
					'default' => '#C24642',
				),
				array(
					'name'    => __( 'Default Color 2 days ago', 'wpas-rw' ),
					'id'      => 'asrw_default_chart_color_day_2',
					'type'    => 'color',
					'default' => '#7F6084',
				),
				array(
					'name'    => __( 'Default Color 3 days ago', 'wpas-rw' ),
					'id'      => 'asrw_default_chart_color_day_3',
					'type'    => 'color',
					'default' => '#C24642',
				),
								array(
					'name'    => __( 'Default Color 4 days ago', 'wpas-rw' ),
					'id'      => 'asrw_default_chart_color_day_4',
					'type'    => 'color',
					'default' => '#86B402',
				),
				array(
					'name'    => __( 'Default Color 5 days ago', 'wpas-rw' ),
					'id'      => 'asrw_default_chart_color_day_5',
					'type'    => 'color',
					'default' => '#A2D1CF',
				),				
				array(
					'name'    => __( 'Default Color additional data point #1', 'wpas-rw' ),
					'id'      => 'asrw_default_chart_color_addl_pt_1',
					'type'    => 'color',
					'default' => '#C8B631',
				),
				array(
					'name'    => __( 'Default Color additional data point #2', 'wpas-rw' ),
					'id'      => 'asrw_default_chart_color_addl_pt_2',
					'type'    => 'color',
					'default' => '#6DBCEB',
				),
				array(
					'name'    => __( 'Default Color additional data point #3', 'wpas-rw' ),
					'id'      => 'asrw_default_chart_color_addl_pt_3',
					'type'    => 'color',
					'default' => '#52514E',
				),
				array(
					'name'    => __( 'Default Color additional data point #4', 'wpas-rw' ),
					'id'      => 'asrw_default_chart_color_addl_pt_4',
					'type'    => 'color',
					'default' => '#4F81BC',
				),
				
				array(
					'name' => __( 'Default Axis And Grid Colors', 'wpas-rw' ),
					'desc' => __( 'You can type the word \'transparent\' into the color picker field to remove colors completely.', 'wpas-rw' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'X-Axis Line Color', 'wpas-rw' ),
					'id'      => 'asrw_default_x_axis_line_color',
					'type'    => 'color',
					'default' => 'transparent',
				),				
				array(
					'name'    => __( 'Y-Axis Line Color', 'wpas-rw' ),
					'id'      => 'asrw_default_y_axis_line_color',
					'type'    => 'color',
					'default' => 'transparent',
				),
				array(
					'name'    => __( 'Grid Lines Color', 'wpas-rw' ),
					'id'      => 'asrw_default_grid_lines_color',
					'type'    => 'color',
					'default' => 'transparent',
				),
				array(
					'name'    => __( 'X-Axis Font Color', 'wpas-rw' ),
					'id'      => 'asrw_default_x_axis_font_color',
					'type'    => 'color',
					'default' => '#000',
				),				
				array(
					'name'    => __( 'Y-Axis Font Color', 'wpas-rw' ),
					'id'      => 'asrw_default_y_axis_font_color',
					'type'    => 'color',
					'default' => 'transparent',
				),				
				
				array(
					'name' => __( 'Default Colors For Other Chart Types', 'wpas-rw' ),
					'desc' => __( 'Set the colors used for charts not set elsewhere', 'wpas-rw' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'Default Color For Other Chart Types', 'wpas-rw' ),
					'id'      => 'asrw_default_chart_color_other',
					'type'    => 'color',
					'default' => '#05AAE8',
				),				
			)			
		)
	);

	return array_merge( $def, $settings );

}

