<?php


add_filter( 'wpas_plugin_settings', 'wpas_settings_reports', 10, 1 );
add_action( 'tf_save_admin_wpas', 'pre_save_options' ,10 ,0  ); // Used to copy data from the titan settings to wordpress options.  Its silly, but that's just the way its done.  


/**
 * Add settings for Reports and Statistic addon.
 *
 * @since 2.0
 *
 * @param  (array) $def Array of existing settings
 * @return (array)      Updated settings
 *
 */
function wpas_settings_reports($def){
	
	//fetch  who can save the report from database
	$save_Roles		=   rns_get_option_data_by_name( 'rns_save_roles' );
	
	//fetch  who can delete the report from database		
	$delete_Roles	=	rns_get_option_data_by_name( 'rns_delete_roles');
	
	//fetch  who can view the report menu from database
	$menu_Roles		=	rns_get_option_data_by_name( 'rns_report_menu_roles' );

	//assign roles permission for save report
	$saveReport = rns_get_roles_list_for_different_settings( );
		
	$settings = array(
				'reports-options' => array(
					'name'    => __( 'Reports', 'reports-and-statistics' ),
					'options' => array(	
						array(
                            'name'    => __( 'Security Roles For Reports', 'reports-and-statistics' ), 
                            'type'    => 'heading',
							'desc'    => __( 'Use these options to control who can save, view and delete reports.', 'reports-and-statistics' ), 							
						),
						array(
							'type'    => 'multicheck',
							'id'      =>  'rns_save_report_roles',
							'name'    =>  __( 'Which roles can save reports ' , 'reports-and-statistics' ),
							'desc'    =>  __( 'Which roles can save reports ' , 'reports-and-statistics' ),
							'options' => $saveReport,
							'default' => $save_Roles
						),
						array(
							'type' 	  => 'multicheck',
							'id'      =>  'rns_delete_report_roles',
							'name'    => __( 'Which roles can delete reports' , 'reports-and-statistics' ),
							'desc'    => __( 'Which roles can delete reports' , 'reports-and-statistics' ),
							'options' => $saveReport,
							'default'  => $delete_Roles
							
						),
						array(
							'type'    => 'multicheck',
							'id'      =>  'rns_menu_report_roles',
							'name'    => __( 'Which roles can see report menu' , 'reports-and-statistics' ),
							'desc'    => __( 'Which roles can see report menu' , 'reports-and-statistics' ),
							'options' => $saveReport,
							'default'  => $menu_Roles
							
						),

						array(
                            'name'    => __( 'General Options', 'reports-and-statistics' ), 
                            'type'    => 'heading',
							'desc'    => __( 'These options apply to all reports.', 'reports-and-statistics' ), 							
						),						
                         array(
                            'id'      => 'rns_interval', 
                            'type'    => 'number', 
                            'name'    => __( 'Set Interval Range', 'reports-and-statistics' ), 
                            'desc'    => __( 'The interval range is used to set the size of the chart grid relative to the data being charted.  For example, if the data being charted includes a maximum value of 100, you might set this to be 10 which will result in 10 grids on the chart.', 'reports-and-statistics' ), 
							'default' => get_option('rns_chart_interval' , 10)
 
						),
						array(
                            'id'      => 'rns_minutes_limit', 
                            'type'    => 'number', 
                            'name'    => __( 'Minutes Limit', 'reports-and-statistics' ), 
                            'desc'    => __( 'This option controls when minutes are switched to hours.  We generally display time based reports in minutes but when the number gets too high its best to switch to hours or days.  This value controls when that happens. By default if the data has bars showing 1000 minutes the time will start to display in hours instead.', 'reports-and-statistics' ), 
							'default' => get_option('rns_minutes_limit' , 1000)
 
						),
						
						array(
                            'name'    => __( 'Interval Ranges For Each Report', 'reports-and-statistics' ), 
                            'type'    => 'heading',
							'desc'    => __( 'The interval range is used to set the size of the chart grid relative to the data being charted.  For example, if the data being charted includes a maximum value of 100, you might set this to be 10 which will result in 10 grids on the chart.', 'reports-and-statistics' ), 							
						),
						array(
                            'id'      => 'rns_basic_interval', 
                            'type'    => 'number', 
                            'name'    => __( 'Ticket Count Interval Range', 'reports-and-statistics' ), 
                            'desc'    => __( 'The interval range for the Ticket Count report', 'reports-and-statistics' ), 
							'default' => get_option('rns_basic_interval' , 10)
 
						),
						array(
                            'id'      => 'rns_productivity_interval', 
                            'type'    => 'number', 
                            'name'    => __( 'Productivity Analysis Interval Range', 'reports-and-statistics' ), 
                            'desc'    => __( 'The interval range for the Productivity Analysis report', 'reports-and-statistics' ), 
							'default' => get_option('rns_productivity_interval' , 10)
 
						),
						array(
                            'id'      => 'rns_resolution_interval', 
                            'type'    => 'number', 
                            'name'    => __( 'Resolution Analysis Interval Range', 'reports-and-statistics' ), 
                            'desc'    => __( 'The interval range for the Resolution Analysis report', 'reports-and-statistics' ), 
							'default' => get_option('rns_resolution_interval' , 10)
 
						),
						array(
                            'id'      => 'rns_delay_interval', 
                            'type'    => 'number', 
                            'name'    => __( 'Delay Analysis Interval Range', 'reports-and-statistics' ), 
                            'desc'    => __( 'The interval range for the Delay Analysis report', 'reports-and-statistics' ), 
							'default' => get_option('rns_delay_interval' , 10)
 
						),
						array(
                            'id'      => 'rns_distribution_interval', 
                            'type'    => 'number', 
                            'name'    => __( 'Distribution Analysis Interval Range', 'reports-and-statistics' ), 
                            'desc'    => __( 'The interval range for the Distribution Analysis report', 'reports-and-statistics' ), 
							'default' => get_option('rns_distribution_interval' , 10)
 
						),
						array(
                            'id'      => 'rns_trend_interval', 
                            'type'    => 'number', 
                            'name'    => __( 'Trend Analysis Interval Range', 'reports-and-statistics' ), 
                            'desc'    => __( 'The interval range for the Trend Analysis report', 'reports-and-statistics' ), 
							'default' => get_option('rns_trend_interval' , 10)
 
						)
					)
				)
			);
	
			return array_merge( $def, $settings );
}


 /**
 * Function to save values collected from the titan framework into regular wordpress options.  
 * Its stupid but that's what we have to do for now.
 *
 * Action Hook: tf_save_admin_wpas
 *
 * @since 2.0.0
 *
 */	

function pre_save_options(  ) {
	
	rns_add_or_update_custom_option_from_settings( 'rns_save_roles' , wpas_get_option('rns_save_report_roles') );
	
	rns_add_or_update_custom_option_from_settings( 'rns_delete_roles' , wpas_get_option('rns_delete_report_roles') );
	
	rns_add_or_update_custom_option_from_settings( 'rns_report_menu_roles' , wpas_get_option('rns_menu_report_roles') );
	 
	rns_add_or_update_option_value( 'rns_chart_interval' , (int) wpas_get_option('rns_chart_interval') );

	// set minutes limit value
	rns_add_or_update_option_value( 'rns_minutes_limit' , (int) wpas_get_option('rns_minutes_limit')  );
	
	// set  interval value for  basic report
	rns_add_or_update_option_value( 'rns_basic_interval' , (int) wpas_get_option('rns_basic_interval')  );
	
	// set  interval value for productivity report
	rns_add_or_update_option_value( 'rns_productivity_interval' , (int) wpas_get_option('rns_productivity_interval')  );
	
	// set  interval value for resolution report
	rns_add_or_update_option_value( 'rns_resolution_interval' , (int) wpas_get_option('rns_resolution_interval')  );
	
	// set  interval value for delay report
	rns_add_or_update_option_value( 'rns_delay_interval' , (int) wpas_get_option('rns_delay_interval')  );
	
	// set  interval value   for distribution report
	rns_add_or_update_option_value( 'rns_distribution_interval' , (int) wpas_get_option('rns_distribution_interval') );
	
	// set interval value  for trend report
	rns_add_or_update_option_value( 'rns_trend_interval' , (int) wpas_get_option('rns_trend_interval')  );
	
}


