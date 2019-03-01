<?php
add_filter( 'wpas_plugin_settings', 'wpas_asrw_settings_defaults', 5, 1 );
/**
 * Add default settings for all charts
 *
 * @param  array $def Array of existing settings
 *
 * @return array      Updated settings
 */
function wpas_asrw_settings_defaults( $def ) {
	$settings = array(
		'asrwsettings3' => array(
			'name'    => __( 'Report Widgets #3', 'wpas-rw' ),
			'options' => array(
				array(
					'name' => __( 'Defaults For Charts', 'wpas-rw' ),
					'desc' => __( 'These settings are the ones used for all charts unless overwritten by a report-specific option.', 'wpas-rw' ),
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

