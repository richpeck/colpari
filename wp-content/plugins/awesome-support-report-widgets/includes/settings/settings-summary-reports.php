<?php
add_filter( 'wpas_plugin_settings', 'wpas_asrw_settings_summary_widget_reports', 5, 1 );
/**
 * Add misc useful settings
 *
 * @param  array $def Array of existing settings
 *
 * @return array      Updated settings
 */
function wpas_asrw_settings_summary_widget_reports( $def ) {
	$settings = array(
		'asrwsettings2' => array(
			'name'    => __( 'Report Widgets #2', 'wpas-rw' ),
			'options' => array(
				
				array(
					'name' => __( 'About These Summary Reports', 'wpas-rw' ),
					'desc' => __( 'This section include options to control the behavior of the summary chart report widgets. The summary chart reports are each one row of a single chart that displays the open ticket count for the item being shown.  For example you can view a single barchart showing the ticket counts for all open tickets by priority.<br /> <br /> For all of these reports there is limited viewing space in the admin dashboard so it is recommended that you use the options below to limit the amount of items shown. ', 'wpas-rw' ),
					'type' => 'note',
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
					'name'    => __( 'Chart Margin', 'wpas-rw' ),
					'id'      => 'asrw_product_summary_chart_margin',
					'desc'	  => __( 'Set margin for this chart measured from left of chart container.  The best is zero but negative values are allowed.', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '0',
					'min'	  => '-100',
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
					'name'    => __( 'Color For X-axis', 'wpas-rw' ),
					'id'      => 'asrw_product_summary_chart_report_x-axis_color',
					'desc'    => __( 'Sets the color used for the x-axis line for this chart', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#CDCDCD',
				),
				array(
					'name'    => __( 'Color For Y-axis', 'wpas-rw' ),
					'id'      => 'asrw_product_summary_chart_report_y-axis_color',
					'desc'    => __( 'Sets the color used for the y-axis line for this chart', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#CDCDCD',
				),
				
				array(
					'name'    => __( 'X-axis Font Color', 'wpas-rw' ),
					'id'      => 'asrw_product_summary_chart_report_x-axis_font_color',
					'type'    => 'color',
					'default' => '#000',
				),
				array(
					'name'    => __( 'Y-axis Font Color', 'wpas-rw' ),
					'id'      => 'asrw_product_summary_chart_report_y-axis_font_color',
					'type'    => 'color',
					'default' => 'transparent',
				),
				array(
					'name'    => __( 'X-axis Font Size', 'wpas-rw' ),
					'id'      => 'asrw_product_summary_chart_report_x-axis_font_size',
					'type'    => 'number',
					'desc'    => __( 'Sets the size of the font used for the x-axis line for this chart. Set to zero for automatic sizing. Change to a manual value if all data-points on the x-axis are not being labelled and you need to see the labels.', 'wpas-rw' ),
					'default' => '0',
					'max'	  => '25',
				),
				array(
					'name'    => __( 'Y-axis Font Size', 'wpas-rw' ),
					'id'      => 'asrw_product_summary_chart_report_y-axis_font_size',
					'desc'    => __( 'Sets the size of the font used for the y-axis line for this chart. Set to zero for automatic sizing.', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '0',
					'max'	  => '25',
				),
				array(
					'name'    => __( 'Show Index Labels?', 'wpas-rw' ),
					'id'      => 'asrw_product_summary_chart_report_show_index_labels',
					'type'    => 'checkbox',
					'desc'    => __( 'Index labels appear at the top of bar/column charts and in tooltips for each data point on the chart', 'wpas-rw' ),
					'default' => false,
				),
				array(
					'name'    => __( 'Index Label Direction', 'wpas-rw' ),
					'id'      => 'asrw_product_summary_chart_report_index_label_direction',
					'type'    => 'radio',
					'desc'    => __( 'Horizontal is appropriate for bar charts while vertical is appropriate for column charts', 'wpas-rw' ),
					'options' => array(
						'vertical'       => __( 'Vertical', 'wpas-rw' ),
						'horizontal'     => __( 'Horizontal', 'wpas-rw' ),
					),					
					'default' => ''
				),
				array(
					'name'    => __( 'Widget Height', 'wpas-rw' ),
					'id'      => 'asrw_product_summary_chart_report_admin_widget_height',
					'desc'    => __( 'Set this to match the height of the data shown in your chart. This will prevent widgets with summary charts from overlapping.', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '500',
					'max'	  => '2000',
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
					'name'    => __( 'Chart Margin', 'wpas-rw' ),
					'id'      => 'asrw_priority_summary_chart_margin',
					'desc'	  => __( 'Set margin for this chart measured from left of chart container.  The best is zero but negative values are allowed.', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '0',
					'min'	  => '-100',
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
					'name'    => __( 'Color For X-axis', 'wpas-rw' ),
					'id'      => 'asrw_priority_summary_chart_report_x-axis_color',
					'desc'    => __( 'Sets the color used for the x-axis line for this chart', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#CDCDCD',
				),
				array(
					'name'    => __( 'Color For Y-axis', 'wpas-rw' ),
					'id'      => 'asrw_priority_summary_chart_report_y-axis_color',
					'desc'    => __( 'Sets the color used for the y-axis line for this chart', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#CDCDCD',
				),
				array(
					'name'    => __( 'X-axis Font Color', 'wpas-rw' ),
					'id'      => 'asrw_priority_summary_chart_report_x-axis_font_color',
					'type'    => 'color',
					'default' => '#000',
				),
				array(
					'name'    => __( 'Y-axis Font Color', 'wpas-rw' ),
					'id'      => 'asrw_priority_summary_chart_report_y-axis_font_color',
					'type'    => 'color',
					'default' => 'transparent',
				),
				array(
					'name'    => __( 'X-axis Font Size', 'wpas-rw' ),
					'id'      => 'asrw_priority_summary_chart_report_x-axis_font_size',
					'type'    => 'number',
					'desc'    => __( 'Sets the size of the font used for the x-axis line for this chart. Set to zero for automatic sizing. Change to a manual value if all data-points on the x-axis are not being labelled and you need to see the labels.', 'wpas-rw' ),
					'default' => '0',
					'max'	  => '25',
				),
				array(
					'name'    => __( 'Y-axis Font Size', 'wpas-rw' ),
					'id'      => 'asrw_priority_summary_chart_report_y-axis_font_size',
					'desc'    => __( 'Sets the size of the font used for the y-axis line for this chart. Set to zero for automatic sizing.', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '0',
					'max'	  => '25',
				),
				array(
					'name'    => __( 'Show Index Labels?', 'wpas-rw' ),
					'id'      => 'asrw_priority_summary_chart_report_show_index_labels',
					'type'    => 'checkbox',
					'desc'    => __( 'Index labels appear at the top of bar/column charts and in tooltips for each data point on the chart', 'wpas-rw' ),
					'default' => false,
				),
				array(
					'name'    => __( 'Index Label Direction', 'wpas-rw' ),
					'id'      => 'asrw_priority_summary_chart_report_index_label_direction',
					'type'    => 'radio',
					'desc'    => __( 'Horizontal is appropriate for bar charts while vertical is appropriate for column charts', 'wpas-rw' ),
					'options' => array(
						'vertical'       => __( 'Vertical', 'wpas-rw' ),
						'horizontal'     => __( 'Horizontal', 'wpas-rw' ),
					),					
					'default' => ''
				),
				array(
					'name'    => __( 'Widget Height', 'wpas-rw' ),
					'id'      => 'asrw_priority_summary_chart_report_admin_widget_height',
					'desc'    => __( 'Set this to match the height of the data shown in your chart. This will prevent widgets with summary charts from overlapping.', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '500',
					'max'	  => '2000',
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
					'name'    => __( 'Chart Margin', 'wpas-rw' ),
					'id'      => 'asrw_channel_summary_chart_margin',
					'desc'	  => __( 'Set margin for this chart measured from left of chart container.  The best is zero but negative values are allowed.', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '0',
					'min'	  => '-100',
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
					'name'    => __( 'Color For X-axis', 'wpas-rw' ),
					'id'      => 'asrw_channel_summary_chart_report_x-axis_color',
					'desc'    => __( 'Sets the color used for the x-axis line for this chart', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#CDCDCD',
				),
				array(
					'name'    => __( 'Color For Y-axis', 'wpas-rw' ),
					'id'      => 'asrw_channel_summary_chart_report_y-axis_color',
					'desc'    => __( 'Sets the color used for the y-axis line for this chart', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#CDCDCD',
				),
				
				array(
					'name'    => __( 'X-axis Font Color', 'wpas-rw' ),
					'id'      => 'asrw_channel_summary_chart_report_x-axis_font_color',
					'type'    => 'color',
					'default' => '#000',
				),
				array(
					'name'    => __( 'Y-axis Font Color', 'wpas-rw' ),
					'id'      => 'asrw_channel_summary_chart_report_y-axis_font_color',
					'type'    => 'color',
					'default' => 'transparent',
				),
				array(
					'name'    => __( 'X-axis Font Size', 'wpas-rw' ),
					'id'      => 'asrw_channel_summary_chart_report_x-axis_font_size',
					'type'    => 'number',
					'desc'    => __( 'Sets the size of the font used for the x-axis line for this chart. Set to zero for automatic sizing. Change to a manual value if all data-points on the x-axis are not being labelled and you need to see the labels.', 'wpas-rw' ),
					'default' => '0',
					'max'	  => '25',
				),
				array(
					'name'    => __( 'Y-axis Font Size', 'wpas-rw' ),
					'id'      => 'asrw_channel_summary_chart_report_y-axis_font_size',
					'desc'    => __( 'Sets the size of the font used for the y-axis line for this chart. Set to zero for automatic sizing.', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '0',
					'max'	  => '25',
				),				
				array(
					'name'    => __( 'Show Index Labels?', 'wpas-rw' ),
					'id'      => 'asrw_channel_summary_chart_report_show_index_labels',
					'type'    => 'checkbox',
					'desc'    => __( 'Index labels appear at the top of bar/column charts and in tooltips for each data point on the chart', 'wpas-rw' ),
					'default' => false,
				),
				array(
					'name'    => __( 'Index Label Direction', 'wpas-rw' ),
					'id'      => 'asrw_channel_summary_chart_report_index_label_direction',
					'type'    => 'radio',
					'desc'    => __( 'Horizontal is appropriate for bar charts while vertical is appropriate for column charts', 'wpas-rw' ),
					'options' => array(
						'vertical'       => __( 'Vertical', 'wpas-rw' ),
						'horizontal'     => __( 'Horizontal', 'wpas-rw' ),
					),					
					'default' => ''
				),
				array(
					'name'    => __( 'Widget Height', 'wpas-rw' ),
					'id'      => 'asrw_channel_summary_chart_report_admin_widget_height',
					'desc'    => __( 'Set this to match the height of the data shown in your chart. This will prevent widgets with summary charts from overlapping.', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '500',
					'max'	  => '2000',
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
					'name'    => __( 'Chart Margin', 'wpas-rw' ),
					'id'      => 'asrw_dept_summary_chart_margin',
					'desc'	  => __( 'Set margin for this chart measured from left of chart container.  The best is zero but negative values are allowed.', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '0',
					'min'	  => '-100',
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
					'name'    => __( 'Color For X-axis', 'wpas-rw' ),
					'id'      => 'asrw_dept_summary_chart_report_x-axis_color',
					'desc'    => __( 'Sets the color used for the x-axis line for this chart', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#CDCDCD',
				),
				array(
					'name'    => __( 'Color For Y-axis', 'wpas-rw' ),
					'id'      => 'asrw_dept_summary_chart_report_y-axis_color',
					'desc'    => __( 'Sets the color used for the y-axis line for this chart', 'wpas-rw' ),
					'type'    => 'color',
					'default' => '#CDCDCD',
				),
				
				array(
					'name'    => __( 'X-axis Font Color', 'wpas-rw' ),
					'id'      => 'asrw_dept_summary_chart_report_x-axis_font_color',
					'type'    => 'color',
					'default' => '#000',
				),
				array(
					'name'    => __( 'Y-axis Font Color', 'wpas-rw' ),
					'id'      => 'asrw_dept_summary_chart_report_y-axis_font_color',
					'type'    => 'color',
					'default' => 'transparent',
				),
				array(
					'name'    => __( 'X-axis Font Size', 'wpas-rw' ),
					'id'      => 'asrw_dept_summary_chart_report_x-axis_font_size',
					'type'    => 'number',
					'desc'    => __( 'Sets the size of the font used for the x-axis line for this chart. Set to zero for automatic sizing. Change to a manual value if all data-points on the x-axis are not being labelled and you need to see the labels.', 'wpas-rw' ),
					'default' => '0',
					'max'	  => '25',
				),
				array(
					'name'    => __( 'Y-axis Font Size', 'wpas-rw' ),
					'id'      => 'asrw_dept_summary_chart_report_y-axis_font_size',
					'desc'    => __( 'Sets the size of the font used for the y-axis line for this chart. Set to zero for automatic sizing.', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '0',
					'max'	  => '25',
				),				
				array(
					'name'    => __( 'Show Index Labels?', 'wpas-rw' ),
					'id'      => 'asrw_dept_summary_chart_report_show_index_labels',
					'type'    => 'checkbox',
					'desc'    => __( 'Index labels appear at the top of bar/column charts and in tooltips for each data point on the chart', 'wpas-rw' ),
					'default' => false,
				),
				array(
					'name'    => __( 'Index Label Direction', 'wpas-rw' ),
					'id'      => 'asrw_dept_summary_chart_report_index_label_direction',
					'type'    => 'radio',
					'desc'    => __( 'Horizontal is appropriate for bar charts while vertical is appropriate for column charts', 'wpas-rw' ),
					'options' => array(
						'vertical'       => __( 'Vertical', 'wpas-rw' ),
						'horizontal'     => __( 'Horizontal', 'wpas-rw' ),
					),					
					'default' => ''
				),
				array(
					'name'    => __( 'Widget Height', 'wpas-rw' ),
					'id'      => 'asrw_dept_summary_chart_report_admin_widget_height',
					'desc'    => __( 'Set this to match the height of the data shown in your chart. This will prevent widgets with summary charts from overlapping.', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '500',
					'max'	  => '2000',
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
					'default' => false,
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
					'name'    => __( 'Widget Height', 'wpas-rw' ),
					'id'      => 'asrw_agent_summary_chart_report_admin_widget_height',
					'desc'    => __( 'Set this to match the height of the data shown in your chart. This will prevent widgets with summary charts from overlapping.', 'wpas-rw' ),
					'type'    => 'number',
					'default' => '500',
					'max'	  => '2000',
				),				
			)			
		)
	);

	return array_merge( $def, $settings );

}

