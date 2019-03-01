<?php
/*
 * Class charts to  show grapch  by points array
 * This class in only for trend analysis report
 *
 *
 */
 
class charts {
	
	/**
	 * This method is used to get the graph string from points array for area and spline area chart type
	 *
	 * @since 3.3
	 *
	 * @param array   $points  counts data like tickets , ticket replies
	 * @param string  $second  containd second dimession value
	 * @param string  $title   graph title value
	 * @param string  $xtitle  x-axis title value
	 * @param string  $ytitle  y-axis title value
	 * @param string  $gid     represent graph id
	 * @param string  $chart   chart type value
	 * @param array | string   $xaxis   this array is used when we need to pass differnt values for x-axis
	 *
	 * @return void
	 */
	static public function area_and_splinearea( $points , $second , $title , $xtitle ,$ytitle , $gid , $chart , $xaxis ) {
		
		// if Second Dimension is not none
		if( $second != 'none' ) {
				rns_get_graph_data_in_trend_reports_com( $points , $second , $title , $xtitle ,$ytitle , $gid , $chart , $xaxis );						
		}
	}
	
	/**
	 * This Method is used to  grpah for area chart type
	 *
	 * @since 3.3
	 *
	 * @param array   $points  counts data like tickets , ticket replies  
	 * @param string  $second  containd second dimession value          
	 * @param string  $title   graph title value 
	 * @param string  $xtitle  x-axis title value
	 * @param string  $ytitle  y-axis title value
	 * @param string  $gid     represent graph id  
	 * @param array | string   $xaxis   this array is used when we need to pass differnt values for x-axis 
	 *
	 * @return void
	 */
	static public function  chart_area( $points , $second , $title , $xtitle ,$ytitle , $gid='' , $xaxis = array() ) {

		charts::area_and_splinearea( $points , $second , $title , $xtitle ,$ytitle , $gid , '\'area\'' , $xaxis ) ;
	}
	
	
	/**
	 * This Method is used to get grpah for spline area chart type 
	 *
	 * @since 3.3
	 *
	 * @param array   $points  counts data like tickets , ticket replies  
	 * @param string  $second  containd second dimession value          
	 * @param string  $title   graph title value 
	 * @param string  $xtitle  x-axis title value
	 * @param string  $ytitle  y-axis title value
	 * @param string  $gid     represent graph id  
	 * @param array | string   $xaxis   this array is used when we need to pass differnt values for x-axis 
	 *
	 * @return void
	 */

	static public function  chart_splineArea( $points , $second , $title , $xtitle ,$ytitle , $gid='' , $xaxis = array() ) {
		
		charts::area_and_splinearea( $points , $second , $title , $xtitle ,$ytitle , $gid , "'splineArea'" , $xaxis ) ;
	}

	
	
	/**
	 * This Method is used to get grpah for spline chart type 
	 *
	 * @since 3.3
	 *
	 * @param array   $points  counts data like tickets , ticket replies  
	 * @param string  $second  containd second dimession value          
	 * @param string  $title   graph title value 
	 * @param string  $xtitle  x-axis title value
	 * @param string  $ytitle  y-axis title value
	 * @param string  $gid     represent graph id  
	 * @param array | string   $xaxis   this array is used when we need to pass differnt values for x-axis 
	 *
	 * @return void
	 */
	 
	static public function  chart_spline( $points , $second , $title , $xtitle ,$ytitle , $gid='' , $xaxis = array() ) {
		 
		charts::bar_and_stackedColumn( $points , $second , $title , $xtitle ,$ytitle , $gid , "'spline'" , $xaxis  ) ;
	}
	
	
	
	/**
	 * This Method is used to get grpah for line chart type
	 *
	 * @since 3.3
	 *
	 * @param array   $points  counts data like tickets , ticket replies  
	 * @param string  $second  containd second dimession value          
	 * @param string  $title   graph title value 
	 * @param string  $xtitle  x-axis title value
	 * @param string  $ytitle  y-axis title value
	 * @param string  $gid     represent graph id  
	 * @param array | string   $xaxis   this array is used when we need to pass differnt values for x-axis 
	 *
	 * @return void
	 */
	static public function  chart_line( $points , $second , $title , $xtitle ,$ytitle , $gid='' , $xaxis = array() ) {
		
		charts::bar_and_stackedColumn( $points , $second , $title , $xtitle ,$ytitle , $gid , "'line'" , $xaxis ) ;
	}
	

	
	/**
	 * This Method is used to get grpah for column  chart type
	 *
	 * @since 3.3
	 *
	 * @param array   $points  counts data like tickets , ticket replies  
	 * @param string  $second  containd second dimession value          
	 * @param string  $title   graph title value 
	 * @param string  $xtitle  x-axis title value
	 * @param string  $ytitle  y-axis title value
	 * @param string  $gid     represent graph id  
	 * @param array | string   $xaxis   this array is used when we need to pass differnt values for x-axis 
	 *
	 * @return void
	 */
	static public function  chart_column( $points , $second , $title , $xtitle ,$ytitle , $gid='' , $xaxis = array() ) {	
	
		charts::bar_and_stackedColumn( $points , $second , $title , $xtitle ,$ytitle , $gid , "'column'" , $xaxis ) ;
  	}
	
	
	
	/**
	 * This method is used to get the graph string from points array for bar and stacked column type
	 *
	 * @since 3.3
	 *
	 * @param array   $points  counts data like tickets , ticket replies  
	 * @param string  $second  containd second dimession value          
	 * @param string  $title   graph title value 
	 * @param string  $xtitle  x-axis title value
	 * @param string  $ytitle  y-axis title value
	 * @param string  $gid     represent graph id  
	 * @param string  $chart   chart type value
	 * @param array | string   $xaxis   this array is used when we need to pass differnt values for x-axis 
	 *
	 * @return void
	 */
	static public function bar_and_stackedColumn( $points , $second , $title , $xtitle ,$ytitle , $gid , $chart , $xaxis ) {
		$dataPoints = array();	
		
		// if Second Dimension is none
		if( $second == 'none' ) {
			$points = rns_drop_zero_row_column_from_points_array( $points , "2"  );
				//Make array for passing parameters to graph.
				foreach ( $points as $key => $val ) {
				
					foreach($val as $l => $d ) {
					
						$dataPoints[] = array("y" => $d, "label" =>  $key  , "display" =>  $xtitle.' '.$key.' , '.$l   );
						
					}
				}
				//theme: "theme2",
				echo '<script type="text/javascript">
						jQuery(function () {
							var chart = new CanvasJS.Chart("rns-graph-holder'.$gid.'", {
								
								zoomEnabled: true,
								animationEnabled: true,
								title: {
									text: "'. $title.'"
								},
								axisY: {
									title: "'. $ytitle.'",
									interval: '.rns_get_interval_value_by_report().'
								},
								axisX: {
									title: "'. $xtitle.'"
								},
								data: [
								{
									indexLabelPlacement: "inside",
									indexLabelFontColor: "white",
									indexLabelFontWeight: 600,
									indexLabelFontFamily: "Verdana",
									type: '.$chart.',
									toolTipContent :  "  {display} : {y}",
									dataPoints: '.json_encode($dataPoints, JSON_NUMERIC_CHECK).'
								}
								]
							});
							chart.render();
						});
					</script>';
					
			
				
			} else {
					rns_get_graph_data_in_trend_reports_com( $points , $second , $title , $xtitle ,$ytitle , $gid , $chart , $xaxis );				
			}	
			
			
	}
	
	
	/**
	 * This Method is used to get grpah for bar  chart type 
	 *
	 * @since 3.3
	 *
	 * @param array   $points  counts data like tickets , ticket replies  
	 * @param string  $second  containd second dimession value          
	 * @param string  $title   graph title value 
	 * @param string  $xtitle  x-axis title value
	 * @param string  $ytitle  y-axis title value
	 * @param string  $gid     represent graph id  
	 * @param array | string   $xaxis   this array is used when we need to pass differnt values for x-axis 
	 *
	 * @return void
	 */
	 
	static public function  chart_bar( $points , $second , $title , $xtitle ,$ytitle , $gid='' , $xaxis = array() ) {
		
		charts::bar_and_stackedColumn( $points , $second , $title , $xtitle ,$ytitle , $gid , "'bar'" , $xaxis ) ;
	}
	
	
	
	/**
	 * This Method is used to get grpah for stackedcolumn  chart type
	 *
	 * @since 3.3
	 *
	 * @param array   $points  counts data like tickets , ticket replies  
	 * @param string  $second  containd second dimession value          
	 * @param string  $title   graph title value 
	 * @param string  $xtitle  x-axis title value
	 * @param string  $ytitle  y-axis title value
	 * @param string  $gid     represent graph id  
	 * @param array | string   $xaxis   this array is used when we need to pass differnt values for x-axis 
	 *
	 * @return void
	 */
	 
	static public function  chart_stackedColumn( $points , $second , $title , $xtitle ,$ytitle , $gid='' , $xaxis = ''  ) {
		
		charts::bar_and_stackedColumn( $points , $second , $title , $xtitle ,$ytitle , $gid , "'stackedColumn'" , $xaxis ) ;		
	}
		
	
	/**
	 * This Method is used to get grpah for stackedArea  chart type 
	 *
	 * @since 3.3
	 *
	 * @param array   $points  counts data like tickets , ticket replies  
	 * @param string  $second  containd second dimession value          
	 * @param string  $title   graph title value 
	 * @param string  $xtitle  x-axis title value
	 * @param string  $ytitle  y-axis title value
	 * @param string  $gid     represent graph id  
	 * @param array | string   $xaxis   this array is used when we need to pass differnt values for x-axis 
	 *
	 * @return void
	 */
	static public function  chart_stackedArea( $points , $second , $title , $xtitle ,$ytitle , $gid='' , $xaxis = ''  ) {
		
		charts::area_and_splinearea( $points , $second , $title , $xtitle ,$ytitle , $gid , "'stackedArea'" , $xaxis ) ;
	}
	

	/**
	 * This Method is used to get grpah for stackedBar  chart type 
	 *
	 * @since 3.3
	 *
	 * @param array   $points  counts data like tickets , ticket replies  
	 * @param string  $second  containd second dimession value          
	 * @param string  $title   graph title value 
	 * @param string  $xtitle  x-axis title value
	 * @param string  $ytitle  y-axis title value
	 * @param string  $gid     represent graph id  
	 * @param array | string   $xaxis   this array is used when we need to pass differnt values for x-axis 
	 *
	 * @return void
	 */
	static public function  chart_stackedBar( $points , $second , $title , $xtitle ,$ytitle , $gid='' , $xaxis = ''  ) {
		
		charts::area_and_splinearea( $points , $second , $title , $xtitle ,$ytitle , $gid , "'stackedBar'"  , $xaxis ) ;
		
	}
	
}
