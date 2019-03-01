<?php
/******************************************************************************************
This file contains the logic to extract admin settings related to the canvasjs charts 

All variables available in the calling file is also available here!

Some variables used here that are from the open-tickets.php file include:
	$template_for

Functions are declared at the top of this file while the actual template partial that uses
the functions are at the bottom.
********************************************************************************************/
?>

<?php 

/**
 * Get the classname for the font-awesome class based on the report name being run.
 *
 * @param $template_name string - The name of the report being run.
 *
 * @return string - The name of the font-awesome class to be used with the report
 *
 * @since 2.0.0
*/
if ( ! function_exists( 'wpas_rw_get_icon' ) ) {

	function wpas_rw_get_icon( $template_name ) {

		$icon_to_return = 'fa-user';
		
		switch ( $template_name ) {
			
			case 'product':
				$icon_to_return = 'fa-area-chart';
				break ;
			
			case 'agent':
				$icon_to_return = 'fa-user';
				break ;
		
			case 'priority': 
				$icon_to_return = 'fa-line-chart';
				break ;
		
			case 'channel': 
				$icon_to_return = 'fa-th';
				break ;
		
			case 'department':
				$icon_to_return = 'fa-picture-o';
				break;
			
			case 'custom-status-summary-chart':
				$icon_to_return = 'fa-crosshairs';
				break ;
		}				
		
		return $icon_to_return;		
	
	}
} // if function exists

/**
 * Get the default color to be used by line charts and other chart elements.
 *
 * @param $template_name string - The name of the report being run.
 *
 * @return string - The color string
 *
 * @since 2.0.0
*/
if ( ! function_exists( 'wpas_rw_get_chart_elements_color' ) ) {
	
	function wpas_rw_get_chart_elements_color($template_name) {
		
		$chart_element_color = wpas_get_option( 'asrw_default_chart_elements_color' );
		
		switch ( $template_name ) {
		
			case 'open-tickets':
				$chart_element_color = wpas_get_option( 'asrw_open_tickets_chart_elements_color' );
				break;		
		
			case 'product':
				$chart_element_color = wpas_get_option( 'asrw_product_chart_elements_color' );
				break;
			
			case 'department':
				$chart_element_color = wpas_get_option( 'asrw_department_chart_elements_color' );
				break;
			
			case 'agent':
				$chart_element_color = wpas_get_option( 'asrw_agent_chart_elements_color' );
				break;
			
			case 'priority':
				$chart_element_color = wpas_get_option( 'asrw_priority_chart_elements_color' );
				break;
				
			case 'channel':
				$chart_element_color = wpas_get_option( 'asrw_channel_chart_elements_color' );
				break;
				
			case 'custom-status-summary-chart':
				$chart_element_color = wpas_get_option( 'asrw_status_chart_elements_color' );
				break;
				
			case 'product-summary-chart':
				$chart_element_color = wpas_get_option( 'asrw_product_summary_chart_elements_color' );
				break;
				
			case 'priority-summary-chart':
				$chart_element_color = wpas_get_option( 'asrw_priority_summary_chart_elements_color' );
				break;
				
			case 'channel-summary-chart':
				$chart_element_color = wpas_get_option( 'asrw_channel_summary_chart_elements_color' );
				break;
				
			case 'department-summary-chart':
				$chart_element_color = wpas_get_option( 'asrw_dept_summary_chart_elements_color' );
				break;
			
			case 'agent-summary-chart':
				$chart_element_color = wpas_get_option( 'asrw_agent_summary_chart_elements_color' );
				break;
		}
	
		if ( empty( $chart_element_color ) ) {
			$chart_element_color = '#bbb';
		}

		return $chart_element_color ;
		
	}

} // if function exists


/**
 * Get the chart type.
 *
 * @param $template_name string - The name of the report being run.
 *
 * @return string - The color string
 *
 * @since 2.0.0
*/
if ( ! function_exists( 'wpas_rw_get_chart_type' ) ) {
	
	function wpas_rw_get_chart_type($template_name) {
		
		$chart_type = wpas_get_option( 'asrw_default_chart_type' );
		
		switch ( $template_name ) {
			
			case 'open-tickets':
				$chart_type = wpas_get_option( 'asrw_open_tickets_chart_type' );
				break;			
			
			case 'product':
				$chart_type = wpas_get_option( 'asrw_product_chart_type' );
				break;

			case 'department':
				$chart_type = wpas_get_option( 'asrw_department_chart_type' );
				break;
			
			case 'agent':
				$chart_type = wpas_get_option( 'asrw_agent_chart_type' );
				break;
			
			case 'priority':
				$chart_type = wpas_get_option( 'asrw_priority_chart_type' );
				break;
			
			case 'channel':
				$chart_type = wpas_get_option( 'asrw_channel_chart_type' );
				break;
			
			case 'custom-status-summary-chart':
				$chart_type = wpas_get_option( 'asrw_status_chart_type' );
				break;
			
			case 'product-summary-chart':
				$chart_type = wpas_get_option( 'asrw_product_summary_chart_type' );
				break;

			case 'priority-summary-chart':
				$chart_type = wpas_get_option( 'asrw_priority_summary_chart_type' );
				break;
			
			case 'channel-summary-chart':
				$chart_type = wpas_get_option( 'asrw_channel_summary_chart_type' );
				break;
			
			case 'department-summary-chart':
				$chart_type = wpas_get_option( 'asrw_dept_summary_chart_type' );
				break;
			
			case 'agent-summary-chart':
				$chart_type = wpas_get_option( 'asrw_agent_summary_chart_type' );
				break;
			
		}
		
		if ( empty( $chart_type ) ) {
			$chart_type = 'line' ;
		}
		
		return $chart_type;
		
	}
	
}  // if function exists

/**
 * Get the datapoint width
 *
 * @param $template_name string - The name of the report being run.
 *
 * @return string - The width of the datapoint
 *
 * @since 2.0.0
*/
if ( ! function_exists( 'wpas_rw_get_datapoint_width' ) ) {
	
	function wpas_rw_get_datapoint_width($template_name) {
		
		$chart_data_point_width = wpas_get_option( 'asrw_default_data_point_width' );
		
		switch ( $template_name ) {
			
			case 'custom-status-summary-chart':
				$chart_data_point_width = wpas_get_option( 'asrw_status_report_data_point_width' );
				break ;
			
			case 'product-summary-chart':
				$chart_data_point_width = wpas_get_option( 'asrw_product_summary_chart_data_point_width' );
				break ;
			
			case 'priority-summary-chart':
				$chart_data_point_width = wpas_get_option( 'asrw_priority_summary_chart_data_point_width' );
				break ;
			
			case 'channel-summary-chart':
				$chart_data_point_width = wpas_get_option( 'asrw_channel_summary_chart_data_point_width' );
				break ;
			
			case 'department-summary-chart':
				$chart_data_point_width = wpas_get_option( 'asrw_dept_summary_chart_data_point_width' );
				break ;

			case 'agent-summary-chart':
				$chart_data_point_width = wpas_get_option( 'asrw_agent_summary_chart_data_point_width' );
				break ;

		}
		
		if ( is_null( $chart_data_point_width ) ) {
			$chart_data_point_width = 0 ;
		}
		
		return $chart_data_point_width;

	}
	
}  // if function exists

/**
 * Get the x-axis color
 *
 * @param $template_name string - The name of the report being run.
 *
 * @return string - The x-axis color
 *
 * @since 2.0.0
*/
if ( ! function_exists( 'wpas_rw_get_x_axis_color' ) ) {
	
	function wpas_rw_get_x_axis_color($template_name) {
		
		$chart_color_x_axis_line = wpas_get_option( 'asrw_default_x_axis_line_color' );
		
		switch ( $template_name ) {
			
			case 'custom-status-summary-chart':
				$chart_color_x_axis_line = wpas_get_option( 'asrw_status_summary_chart_report_x-axis_color' );
				break ;				
			
			case 'product-summary-chart':
				$chart_color_x_axis_line = wpas_get_option( 'asrw_product_summary_chart_report_x-axis_color' );
				break ;			
			
			case 'priority-summary-chart':
				$chart_color_x_axis_line = wpas_get_option( 'asrw_priority_summary_chart_report_x-axis_color' );
				break ;

			case 'department-summary-chart':
				$chart_color_x_axis_line = wpas_get_option( 'asrw_dept_summary_chart_report_x-axis_color' );
				break ;				
				
			case 'channel-summary-chart':
				$chart_color_x_axis_line = wpas_get_option( 'asrw_channel_summary_chart_report_x-axis_color' );
				break ;				
				
			case 'agent-summary-chart':
				$chart_color_x_axis_line = wpas_get_option( 'asrw_agent_summary_chart_report_x-axis_color' );
				break ;
		
		}
		
	
		if ( empty( $chart_color_x_axis_line ) ) {
			$chart_color_x_axis_line = 'transparent' ;
		}		
		
		return $chart_color_x_axis_line;
		
	}
	
}  // if function exists

/**
 * Get the y-axis color
 *
 * @param $template_name string - The name of the report being run.
 *
 * @return string - The y-axis color
 *
 * @since 2.0.0
*/
if ( ! function_exists( 'wpas_rw_get_y_axis_color' ) ) {
	
	function wpas_rw_get_y_axis_color($template_name) {
		
		$chart_color_y_axis_line = wpas_get_option( 'asrw_default_y_axis_line_color' );
		
		switch ( $template_name ) {

			case 'custom-status-summary-chart':
				$chart_color_y_axis_line = wpas_get_option( 'asrw_status_summary_chart_report_y-axis_color' );
				break ;
				
			case 'product-summary-chart':
				$chart_color_y_axis_line = wpas_get_option( 'asrw_product_summary_chart_report_y-axis_color' );
				break ;
			
			case 'priority-summary-chart':
				$chart_color_y_axis_line = wpas_get_option( 'asrw_priority_summary_chart_report_y-axis_color' );
				break ;
				
			case 'department-summary-chart':
				$chart_color_y_axis_line = wpas_get_option( 'asrw_dept_summary_chart_report_y-axis_color' );
				break ;				

			case 'channel-summary-chart':
				$chart_color_y_axis_line = wpas_get_option( 'asrw_channel_summary_chart_report_y-axis_color' );
				break ;
				
			case 'agent-summary-chart':
				$chart_color_y_axis_line = wpas_get_option( 'asrw_agent_summary_chart_report_y-axis_color' );
				break ;

		}
		
	
		if ( empty( $chart_color_y_axis_line ) ) {
			$chart_color_y_axis_line = 'transparent' ;
		}		
		
		return $chart_color_y_axis_line;
		
	}
	
}  // if function exists

/**
 * Get the x-axis label font color
 *
 * @param $template_name string - The name of the report being run.
 *
 * @return string - The x-axis label font color
 *
 * @since 2.0.0
*/
if ( ! function_exists( 'wpas_rw_get_x_axis_label_font_color' ) ) {
	
	function wpas_rw_get_x_axis_label_font_color($template_name) {
		
		$chart_color_x_axis_label_font = wpas_get_option( 'asrw_default_x_axis_font_color' );
		
		switch ( $template_name ) {

			case 'custom-status-summary-chart':
				$chart_color_x_axis_label_font = wpas_get_option( 'asrw_status_summary_chart_report_x-axis_font_color' );
				break ;			
		
			case 'product-summary-chart':
				$chart_color_x_axis_label_font = wpas_get_option( 'asrw_product_summary_chart_report_x-axis_font_color' );
				break ;			

			case 'priority-summary-chart':
				$chart_color_x_axis_label_font = wpas_get_option( 'asrw_priority_summary_chart_report_x-axis_font_color' );
				break ;				

			case 'department-summary-chart':
				$chart_color_x_axis_label_font = wpas_get_option( 'asrw_dept_summary_chart_report_x-axis_font_color' );
				break ;

			case 'channel-summary-chart':
				$chart_color_x_axis_label_font = wpas_get_option( 'asrw_channel_summary_chart_report_x-axis_font_color' );
				break ;				
				
			case 'agent-summary-chart':
				$chart_color_x_axis_label_font = wpas_get_option( 'asrw_agent_summary_chart_report_x-axis_font_color' );
				break ;

		}
		
	
		if ( empty( $chart_color_x_axis_label_font ) ) {			
			$chart_color_x_axis_label_font = '#000' ;
		}		
		
		return $chart_color_x_axis_label_font;
		
	}
	
}  // if function exists

/**
 * Get the y-axis label font color
 *
 * @param $template_name string - The name of the report being run.
 *
 * @return string - The y-axis label font color
 *
 * @since 2.0.0
*/
if ( ! function_exists( 'wpas_rw_get_y_axis_label_font_color' ) ) {
	
	function wpas_rw_get_y_axis_label_font_color($template_name) {
		
		$chart_color_y_axis_label_font = wpas_get_option( 'asrw_default_y_axis_font_color' );
		
		switch ( $template_name ) {

			case 'custom-status-summary-chart':
				$chart_color_y_axis_label_font = wpas_get_option( 'asrw_status_summary_chart_report_y-axis_font_color' );
				break ;

			case 'product-summary-chart':
				$chart_color_y_axis_label_font = wpas_get_option( 'asrw_product_summary_chart_report_y-axis_font_color' );
				break ;

			case 'priority-summary-chart':
				$chart_color_y_axis_label_font = wpas_get_option( 'asrw_priority_summary_chart_report_y-axis_font_color' );
				break ;

			case 'department-summary-chart':
				$chart_color_y_axis_label_font = wpas_get_option( 'asrw_dept_summary_chart_report_y-axis_font_color' );
				break ;

			case 'channel-summary-chart':
				$chart_color_y_axis_label_font = wpas_get_option( 'asrw_channel_summary_chart_report_y-axis_font_color' );
				break ;				
			
			case 'agent-summary-chart':
				$chart_color_y_axis_label_font = wpas_get_option( 'asrw_agent_summary_chart_report_y-axis_font_color' );
				break ;

		}
		
	
		if ( empty( $chart_color_y_axis_label_font ) ) {
			$chart_color_y_axis_label_font = 'transparent' ;
		}		
		
		return $chart_color_y_axis_label_font;
		
	}
	
}  // if function exists

/**
 * Get the x-axis label font size
 *
 * @param $template_name string - The name of the report being run.
 *
 * @return int - The x-axis label font size
 *
 * @since 2.0.0
*/
if ( ! function_exists( 'wpas_rw_get_x_axis_label_font_size' ) ) {
	
	function wpas_rw_get_x_axis_label_font_size($template_name) {
		
		$chart_x_axis_label_font_size = 0;  // Automatic sizing
		
		switch ( $template_name ) {

			case 'custom-status-summary-chart':
				$chart_x_axis_label_font_size = wpas_get_option( 'asrw_status_summary_chart_report_x-axis_font_size' );
				break ;
				
			case 'product-summary-chart':
				$chart_x_axis_label_font_size = wpas_get_option( 'asrw_product_summary_chart_report_x-axis_font_size' );
				break ;

			case 'priority-summary-chart':
				$chart_x_axis_label_font_size = wpas_get_option( 'asrw_priority_summary_chart_report_x-axis_font_size' );
				break ;

			case 'department-summary-chart':
				$chart_x_axis_label_font_size = wpas_get_option( 'asrw_dept_summary_chart_report_x-axis_font_size' );
				break ;

			case 'channel-summary-chart':
				$chart_x_axis_label_font_size = wpas_get_option( 'asrw_channel_summary_chart_report_x-axis_font_size' );
				break ;
				
			case 'agent-summary-chart':
				$chart_x_axis_label_font_size = wpas_get_option( 'asrw_agent_summary_chart_report_x-axis_font_size' );
				break ;

		}
		
	
		if ( empty( $chart_x_axis_label_font_size ) ) {			
			$chart_x_axis_label_font_size = 0 ;
		}		
		
		return $chart_x_axis_label_font_size;
		
	}
	
}  // if function exists

/**
 * Get the y-axis label font size
 *
 * @param $template_name string - The name of the report being run.
 *
 * @return int - The y-axis label font size
 *
 * @since 2.0.0
*/
if ( ! function_exists( 'wpas_rw_get_y_axis_label_font_size' ) ) {
	
	function wpas_rw_get_y_axis_label_font_size($template_name) {
		
		$chart_y_axis_label_font_size = 0;  // Automatic sizing
		
		switch ( $template_name ) {

			case 'custom-status-summary-chart':
				$chart_y_axis_label_font_size = wpas_get_option( 'asrw_status_summary_chart_report_y-axis_font_size' );
				break ;		
		
			case 'product-summary-chart':
				$chart_y_axis_label_font_size = wpas_get_option( 'asrw_product_summary_chart_report_y-axis_font_size' );
				break ;

			case 'priority-summary-chart':
				$chart_y_axis_label_font_size = wpas_get_option( 'asrw_priority_summary_chart_report_y-axis_font_size' );
				break ;

			case 'department-summary-chart':
				$chart_y_axis_label_font_size = wpas_get_option( 'asrw_dept_summary_chart_report_y-axis_font_size' );
				break ;

			case 'channel-summary-chart':
				$chart_y_axis_label_font_size = wpas_get_option( 'asrw_channel_summary_chart_report_y-axis_font_size' );
				break ;
				
			case 'agent-summary-chart':
				$chart_y_axis_label_font_size = wpas_get_option( 'asrw_agent_summary_chart_report_y-axis_font_size' );
				break ;

		}
		
	
		if ( empty( $chart_y_axis_label_font_size ) ) {			
			$chart_y_axis_label_font_size = 0 ;
		}		
		
		return $chart_y_axis_label_font_size;
		
	}
	
}  // if function exists

/**
 * Get the show index label option
 *
 * @param $template_name string - The name of the report being run.
 *
 * @return boolean - true/false
 *
 * @since 2.0.0
*/
if ( ! function_exists( 'wpas_rw_get_show_index_label' ) ) {
	
	function wpas_rw_get_show_index_label($template_name) {
		
		$chart_show_index_label = false ;
		
		switch ( $template_name ) {

			case 'custom-status-summary-chart':
				$chart_show_index_label = (boolean) wpas_get_option( 'asrw_status_summary_chart_report_show_index_labels' );
				break ;
				
			case 'product-summary-chart':
				$chart_show_index_label = (boolean) wpas_get_option( 'asrw_product_summary_chart_report_show_index_labels' );
				break ;

			case 'priority-summary-chart':
				$chart_show_index_label = (boolean) wpas_get_option( 'asrw_priority_summary_chart_report_show_index_labels' );
				break ;

			case 'department-summary-chart':
				$chart_show_index_label = (boolean) wpas_get_option( 'asrw_dept_summary_chart_report_show_index_labels' );
				break ;

			case 'channel-summary-chart':
				$chart_show_index_label = (boolean) wpas_get_option( 'asrw_channel_summary_chart_report_show_index_labels' );
				break ;
				
			case 'agent-summary-chart':
				$chart_show_index_label = (boolean) wpas_get_option( 'asrw_agent_summary_chart_report_show_index_labels' );
				break ;
		
		}
		
	
		if ( empty( $chart_show_index_label ) ) {
			$chart_show_index_label = false;
		}		

		return $chart_show_index_label;
		
	}
	
}  // if function exists

/**
 * Get the index label direction
 *
 * @param $template_name string - The name of the report being run.
 *
 * @return string - The index label direction (horizontal/vertical)
 *
 * @since 2.0.0
*/
if ( ! function_exists( 'wpas_rw_get_index_label_direction' ) ) {
	
	function wpas_rw_get_index_label_direction($template_name) {
		
		$chart_index_label_direction = '';
		
		switch ( $template_name ) {

			case 'custom-status-summary-chart':
				$chart_index_label_direction = wpas_get_option( 'asrw_status_summary_chart_report_index_label_direction' );
				break ;
				
			case 'product-summary-chart':
				$chart_index_label_direction = wpas_get_option( 'asrw_product_summary_chart_report_index_label_direction' );
				break ;
				
			case 'priority-summary-chart':
				$chart_index_label_direction = wpas_get_option( 'asrw_priority_summary_chart_report_index_label_direction' );
				break ;

			case 'department-summary-chart':
				$chart_index_label_direction = wpas_get_option( 'asrw_dept_summary_chart_report_index_label_direction' );
				break ;

			case 'channel-summary-chart':
				$chart_index_label_direction = wpas_get_option( 'asrw_channel_summary_chart_report_index_label_direction' );
				break ;
				
			case 'agent-summary-chart':
				$chart_index_label_direction = wpas_get_option( 'asrw_agent_summary_chart_report_index_label_direction' );
				break ;
		
		}
		
	
		if ( empty( $chart_index_label_direction ) ) {
			$chart_index_label_direction = '' ;
		}		
		
		return $chart_index_label_direction;
		
	}
	
}  // if function exists


/**
 * Get the chart margins
 *
 * @param $template_name string - The name of the report being run.
 *
 * @return integer - chart margin
 *
 * @since 2.0.0
*/
if ( ! function_exists( 'wpas_rw_get_chart_margin' ) ) {
	
	function wpas_rw_get_chart_margin($template_name) {
		
		$chart_margin = wpas_get_option( 'asrw_default_chart_margin' );
		
		switch ( $template_name ) {
			case 'custom-status-summary-chart':
				$chart_margin = wpas_get_option( 'asrw_status_summary_chart_margin' );
				break ;
			
			case 'product-summary-chart':
				$chart_margin = wpas_get_option( 'asrw_product_summary_chart_margin' );
				break ;
			
			case 'priority-summary-chart':
				$chart_margin = wpas_get_option( 'asrw_priority_summary_chart_margin' );
				break ;
			
			case 'channel-summary-chart':
				$chart_margin = wpas_get_option( 'asrw_channel_summary_chart_margin' );
				break ;
			
			case 'department-summary-chart':
				$chart_margin = wpas_get_option( 'asrw_dept_summary_chart_margin' );
				break ;

			case 'agent-summary-chart':
				$chart_margin = wpas_get_option( 'asrw_agent_summary_chart_margin' );
				break ;				

		}
		
		if ( is_null( $chart_margin ) ) {
			$chart_margin = -15 ;
		}
		
		return $chart_margin;

	}
	
}  // if function exists

/**
 * Get the Widget height
 *
 * @param $template_name string - The name of the report being run.
 *
 * @return integer - admin widget height
 *
 * @since 2.0.0
*/
if ( ! function_exists( 'wpas_rw_get_admin_widget_height' ) ) {
	
	function wpas_rw_get_admin_widget_height($template_name) {
		
		$admin_widget_height = 0 ;
		
		switch ( $template_name ) {
			case 'custom-status-summary-chart':
				$admin_widget_height = wpas_get_option( 'asrw_status_summary_chart_report_admin_widget_height' );
				break ;
			
			case 'product-summary-chart':
				$admin_widget_height = wpas_get_option( 'asrw_product_summary_chart_report_admin_widget_height' );
				break ;
			
			case 'priority-summary-chart':
				$admin_widget_height = wpas_get_option( 'asrw_priority_summary_chart_report_admin_widget_height' );
				break ;
			
			case 'channel-summary-chart':
				$admin_widget_height = wpas_get_option( 'asrw_channel_summary_chart_report_admin_widget_height' );
				break ;
			
			case 'department-summary-chart':
				$admin_widget_height = wpas_get_option( 'asrw_dept_summary_chart_report_admin_widget_height' );
				break ;

			case 'agent-summary-chart':
				$admin_widget_height = wpas_get_option( 'asrw_agent_summary_chart_report_admin_widget_height' );
				break ;				

		}
		
		if ( is_null( $admin_widget_height ) ) {
			$admin_widget_height = 0 ;
		}
		
		return $admin_widget_height;

	}
	
}  // if function exists

?>



<?php
	// This is start of the actual template partial
	
	// Get some default colors...
	$chart_element_color = wpas_rw_get_chart_elements_color( $template_for ) ;
	
	// Get classname for icon
	$icon = wpas_rw_get_icon( $template_for ) ;	
	
	// Get charttype
	$chart_type = wpas_rw_get_chart_type( $template_for ) ;

	// Get datapoint widget
	$chart_data_point_width = wpas_rw_get_datapoint_width( $template_for );
	
	// x-axis color
	$chart_color_x_axis_line = wpas_rw_get_x_axis_color( $template_for );
	
	// y-axis color
	$chart_color_y_axis_line = wpas_rw_get_y_axis_color( $template_for );	
	
	// x-axis label font color
	$chart_color_x_axis_label_font = wpas_rw_get_x_axis_label_font_color( $template_for );
	
	// y-axis label font color
	$chart_color_y_axis_label_font = wpas_rw_get_y_axis_label_font_color( $template_for );	
	
	// x-axis label font size
	$chart_x_axis_label_font_size = wpas_rw_get_x_axis_label_font_size( $template_for );
	
	// y-axis label font size
	$chart_y_axis_label_font_size = wpas_rw_get_y_axis_label_font_size( $template_for );
	
	// Show index labels?
	$chart_show_index_labels =wpas_rw_get_show_index_label( $template_for ) ;
	
	// Index label direction
	$chart_index_label_direction = wpas_rw_get_index_label_direction( $template_for ) ;
	
	// Chart margin
	$chart_margin = wpas_rw_get_chart_margin( $template_for ) ;
	
	// Admin widget height for summary reports...
	$admin_widget_height = wpas_rw_get_admin_widget_height( $template_for ) ;

	// Get line thickness
	$chart_line_thickness = wpas_get_option( 'asrw_default_chart_line_thickness' );
	if ( empty( $chart_line_thickness ) ) {
		$chart_line_thickness = 1 ;
	}

	// Get individual datapoint colors....
	$chart_color_day_0 = wpas_get_option( 'asrw_default_chart_color_day_0' );
	if ( empty( $chart_color_day_0 ) ) {
		$chart_color_day_0 = '#369EAD' ;
	}
	$chart_color_day_1 = wpas_get_option( 'asrw_default_chart_color_day_1' );
	if ( empty( $chart_color_day_1 ) ) {
		$chart_color_day_1 = '#369EAD' ;
	}
	$chart_color_day_2 = wpas_get_option( 'asrw_default_chart_color_day_2' );
	if ( empty( $chart_color_day_2 ) ) {
		$chart_color_day_2 = '#369EAD' ;
	}
	$chart_color_day_3 = wpas_get_option( 'asrw_default_chart_color_day_3' );
	if ( empty( $chart_color_day_3 ) ) {
		$chart_color_day_3 = '#369EAD' ;
	}		
	$chart_color_day_4 = wpas_get_option( 'asrw_default_chart_color_day_4' );
	if ( empty( $chart_color_day_4 ) ) {
		$chart_color_day_4 = '#369EAD' ;
	}
	$chart_color_day_5 = wpas_get_option( 'asrw_default_chart_color_day_5' );
	if ( empty( $chart_color_day_5 ) ) {
		$chart_color_day_5 = '#369EAD' ;
	}
	
	// gridlines color
	$chart_color_grid_lines = wpas_get_option( 'asrw_default_grid_lines_color' );
	if ( empty( $chart_color_grid_lines ) ) {
		$chart_color_grid_lines = 'transparent' ;
	}
	
	// Default color for chart elements
	$chart_color_other = wpas_get_option( 'asrw_default_chart_color_other' );
	if ( empty( $chart_color_other ) ) {
		$chart_color_other = '05AAE8' ;
	}
	
	// Get theme
	$chart_theme = wpas_get_option( 'asrw_default_chart_theme' );
	if ( empty( $chart_theme ) ) {
		$chart_theme = 'theme1' ;
	}

	// Get Colorset
	$chart_colorset = wpas_get_option( 'asrw_default_chart_colorset' );
	if ( empty( $chart_colorset ) ) {
		$chart_colorset = 'colorSet1' ;
	}
	
	// Convert font-size for x and y axies to a string that can be inserted directly inside the canvasJS javascript call.
	// We're doign this here because it applies to all charts so no point doing it inside the 
	// open-tickets-datapoints.php file since that is specific to the datapoints formating.
	
	$chart_x_axis_label_font_size_string = '' ; 
	if ( $chart_x_axis_label_font_size > 0 ) {
		$chart_x_axis_label_font_size_string = 'labelFontSize: ' . (string) $chart_x_axis_label_font_size . ',' ;
	}
	
	
?>