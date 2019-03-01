<?php
/*
 * This class is used to show the graph
 *
 *
 *
 */
class chart {
	
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
	 *
	 * @return void
	 */
	
	static public function area_and_splinearea( $points , $second , $title , $xtitle ,$ytitle , $gid , $chart , $xaxis ) {
		
		$dataPoints = array();
		
		// if Second Dimension is not none
		if( $second != 'none' ) {
			
			$points = rns_drop_zero_row_column_from_points_array( $points , "2"  );
			
			$graphString = '';	$counter = 1;
				
			foreach( $points as $key=>$point ) {
				
				$display_name = rns_get_display_name_by_second_option( $second, $key );
				
				$graphString .= ' { type : '.$chart.',
									toolTipContent :  " {label} , {display} : {y}",
									showInLegend: true, 
									legendText: "'.  $display_name .'",
									dataPoints:     ';
				
	
				foreach($point as $label=>$val) {
					
					$dataPoints[] = array("y" => $val, "label" =>  $label  , "display" =>  $display_name  );
				
				}
								
				$graphString .= json_encode($dataPoints, JSON_NUMERIC_CHECK);	
				$dataPoints = array();
				$graphString .= ' }, ';
				$counter++;
			}
	
		echo '<script type="text/javascript">
				jQuery(function () {
					var chart = new CanvasJS.Chart("rns-graph-holder'.$gid.'", {
						theme: "theme2",
						zoomEnabled: true,
						animationEnabled: true,
						title: {
							text: "'. $title.' By '.rn_get_custom_fields_title_by_name( $second ).'"
						},
						axisY: {
							title: "'. $ytitle.'",
							interval: '.rns_get_interval_value_by_report().'
						},
						axisX: {
							title: "'. $xtitle.'"
						},
						data: [ '.$graphString.' ]
					});
					chart.render();
				});
			</script>';				 
								
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

		chart::area_and_splinearea( $points , $second , $title , $xtitle ,$ytitle , $gid , '\'area\'' , $xaxis ) ;
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
	static public function  chart_splineArea( $points , $second , $title , $xtitle ,$ytitle , $gid='' , $xaxis = '' ) {
		
		chart::area_and_splinearea( $points , $second , $title , $xtitle ,$ytitle , $gid , "'splineArea'" , $xaxis ) ;
	}
	
	
	/**
	 * This method is used to get the graph string from points array for spline and line column type
	 *
	 * @since 3.3
	 *
	 * @param array   $points  counts data like tickets , ticket replies  
	 * @param string  $second  containd second dimession value          
	 * @param string  $title   graph title value 
	 * @param string  $xtitle  x-axis title value
	 * @param string  $ytitle  y-axis title value
	 * @param string $gid     represent graph id  
	 * @param string  $chart   chart type value
	 * @param array | string   $xaxis   this array is used when we need to pass differnt values for x-axis 
	 *
	 * @return void
	 */
	static public function spline_and_line( $points , $second , $title , $xtitle ,$ytitle , $gid , $chart , $xaxis ) {
		
		$dataPoints = array();
		$xinterval= '';
		// if Second Dimension is  none
		if( $second == 'none') {
			
					$points = rns_drop_zero_row_column_from_points_array( $points , "1"  );
	
					$res_data   = rns_get_datapoints_xinterval_by_points( $points );
					$dataPoints = $res_data['datapoints'];
					$xinterval  = $res_data['xinterval'];
					
					echo '<script type="text/javascript">
							jQuery(function () {
								var chart = new CanvasJS.Chart("rns-graph-holder'.$gid.'", {
									theme: "theme2",
									zoomEnabled: true,
									animationEnabled: true,
									title: {
										text: "'. $title .'"
									},
									axisX: {
										title: "'. $xtitle.'"
										'.$xinterval.'
									},
									axisY: {
										title: "'. $ytitle.'",
										interval: '.rns_get_interval_value_by_report().'
									},
									data: [
									{
										type: "'.$chart.'",
										dataPoints: '.json_encode($dataPoints, JSON_NUMERIC_CHECK).'
									}
									]
								});
								chart.render();
							});
						</script>';
				}
				else {		
					
					$points = rns_drop_zero_row_column_from_points_array( $points , "2"  );
						
					 $graphString = '';	$counter = 1;
					
					foreach( $points as $key=>$point ) {
						
							
					
							$display_name = rns_get_display_name_by_second_option( $second, $key ); 
					
							$graphString .= ' { type : "'.$chart.'",
												toolTipContent :  " {label} ,  {display} : {y}",
												showInLegend: true, 
												legendText: "'.  $display_name .'",
												dataPoints:     ';
							
				
							foreach($point as $label=>$val){
								
								
								
								$dataPoints[] = array("y" => $val, "label" =>  $label ,"display" =>  $display_name  );
								
								if( is_numeric( $label ) ) {
									$xinterval = ", interval: ".rns_get_interval_value_by_report() ;
								}
								
							}
							
							
								
							
							$graphString .= json_encode($dataPoints, JSON_NUMERIC_CHECK);	
							$dataPoints = array();
							$graphString .= ' }, ';
							$counter++;
						}
								
					echo '<script type="text/javascript">
							jQuery(function () {
								var chart = new CanvasJS.Chart("rns-graph-holder'.$gid.'", {
									theme: "theme2",
									zoomEnabled: true,
									animationEnabled: true,
									title: {
										text: "'. $title.' By '.rn_get_custom_fields_title_by_name( $second ).'"
									},
									axisY: {
										title: "'. $ytitle.'",
										interval: '.rns_get_interval_value_by_report().'
									},
									axisX: {
										title: "'.$xtitle.'"
										'.$xinterval.'
									},
									data: [
									  '.$graphString.'
								
									  ]
								});
								chart.render();
							});
						</script>';				 
											
				}
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
	static public function  chart_spline( $points , $second , $title , $xtitle ,$ytitle , $gid='' , $xaxis = '' ) {
		 
		chart::spline_and_line( $points , $second , $title , $xtitle ,$ytitle , $gid , "spline" , $xaxis  ) ;
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
	static public function  chart_line( $points , $second , $title , $xtitle ,$ytitle , $gid='' , $xaxis = '' ) {
		
		chart::spline_and_line( $points , $second , $title , $xtitle ,$ytitle , $gid , "line" , $xaxis ) ;
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
	 * @param string $gid     represent graph id  
	 * @param array | string   $xaxis   this array is used when we need to pass differnt values for x-axis 
	 *
	 * @return void
	 */
	static public function  chart_column( $points , $second , $title , $xtitle ,$ytitle , $gid='' , $xaxis = '' ) {	
	
		chart::spline_and_line( $points , $second , $title , $xtitle ,$ytitle , $gid , "column" , $xaxis ) ;
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
		$xinterval = '';
		// if Second Dimension is  none
		if( $second == 'none' ) {
				
				$points = rns_drop_zero_row_column_from_points_array( $points , "1"  );
				
				//Make array for passing parameters to graph.
				$res_data   = rns_get_datapoints_xinterval_by_points( $points );
				$dataPoints = $res_data['datapoints'];
				$xinterval  = $res_data['xinterval'];

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
									title: "'.$ytitle.'",
									interval: '.rns_get_interval_value_by_report().'
								},
								axisX: {
									title: "'. $xtitle.'"
									'.$xinterval.'
									
								},
								data: [
								{
									indexLabelPlacement: "inside",
									indexLabelFontColor: "white",
									indexLabelFontWeight: 600,
									indexLabelFontFamily: "Verdana",
									type: "'.$chart.'",
									dataPoints: '.json_encode($dataPoints, JSON_NUMERIC_CHECK).'
								}
								]
							});
							chart.render();
						});
					</script>';
					
			
				
			} else {
				
					$points = rns_drop_zero_row_column_from_points_array( $points , "2"  );
					
					//preparing passing parameters to graph.
					$graphString = '';	$counter = 1;
					
					foreach( $points as $key=>$point ) {
						
						
						
						$display_name = rns_get_display_name_by_second_option( $second, $key );
						
						$graphString .= ' { type : "'.$chart.'", 
											toolTipContent :  " {label} ,  {display} : {y}",
											showInLegend: true, 
											legendText: "'. $display_name .'",
											dataPoints:     ';
						$x = 0;
						
						foreach( $point as $label=>$val ){
							
							
							
							// if xaxis value is set 						
							if( !empty( $xaxis ) ) {
								$x = $label;	
								$labeled = $x;
							} else {
								$x = $x	 +	10;	
								$labeled = $label;
							}
							
							// if bar  chart type selected
							if( $chart == 'bar' &&  is_numeric( $label ) ) {
												
								$dataPoints[] = array("x" => $x , "y" =>  $val , "label" =>  $labeled  ,"display" =>  $display_name  );
							
							} else {
								
								$dataPoints[] = array("y" => $val , "label" => $labeled  ,"display" =>  $display_name  );	
								
							}
							
							if( is_numeric( $label ) ) { // if label contain numeric value
								$xinterval = ", interval: ".rns_get_interval_value_by_report() ;
							}
							
						}
							
						
						$graphString .= json_encode($dataPoints, JSON_NUMERIC_CHECK);	
						$dataPoints = array();
						$graphString .= ' }, ';
						$counter++;
					}
					
				
				
				echo '<script type="text/javascript">
						jQuery(function () {
							var chart = new CanvasJS.Chart("rns-graph-holder'.$gid.'", {
								theme: "theme2",
								zoomEnabled: true,
								animationEnabled: true,
								title: {
									text: "'. $title.' By '.rn_get_custom_fields_title_by_name( $second ).'"
								},
								axisY: {
									title: "'. $ytitle.'",
									interval: '.rns_get_interval_value_by_report().'
								},
								axisX: {
									title: "'. $xtitle.'"
									'.$xinterval.'
								},
								data: [
								  '.$graphString.'
							
								  ]
							});
							chart.render();
						});
					</script>';	
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
	static public function  chart_bar( $points , $second , $title , $xtitle ,$ytitle , $gid='' , $xaxis = '' ) {
		
		chart::bar_and_stackedColumn( $points , $second , $title , $xtitle ,$ytitle , $gid , "bar" , $xaxis ) ;
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
		
		chart::bar_and_stackedColumn( $points , $second , $title , $xtitle ,$ytitle , $gid , "stackedColumn" , $xaxis ) ;		
	}
	
	
	/**
	 * This method is used to get the graph string from points array for pie and doughnut type
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
	static public function pie_and_doughnut( $points , $second , $title , $xtitle ,$ytitle , $gid , $chart , $xaxis  ) {
		$dataPoints = array();	
		
		$points = rns_drop_zero_row_column_from_points_array( $points , "1"  );
		
		foreach ( $points as $key => $val ) {
				
						$dataPoints[] = array("y" => $val, "name" => $key  , 'indexLabel' =>  $key );

				}

				echo '<script type="text/javascript">
						jQuery(function () {
							var chart = new CanvasJS.Chart("rns-graph-holder'.$gid.'", {
								theme: "theme2",
								zoomEnabled: true,
								animationEnabled: true,
								title: {
									text: "'. $title .'"
								},
								data: [
								{
									type: "'.$chart.'",
									indexLabelFontFamily: "Garamond",       
									indexLabelFontSize: 20,
									indexLabelFontWeight: "bold",
									startAngle:0,     
									indexLabelLineColor: "white", 
									indexLabelLineColor: "darkgrey", 
									indexLabelPlacement: "outside",
									showInLegend: true,
									dataPoints: '.json_encode($dataPoints, JSON_NUMERIC_CHECK).'
								}
								]
							});
							chart.render();
						});
					</script>';
	}
	
	
	/**
	 * This Method is used to get grpah for pie  chart type 
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
	static public function  chart_pie( $points , $second , $title , $xtitle ,$ytitle , $gid='' , $xaxis = ''  ) {
		
		chart::pie_and_doughnut( $points , $second , $title , $xtitle ,$ytitle , $gid , "pie" , $xaxis ) ;
	}
	
	/**
	 * This Method is used to get grpah for doughnut chart type 
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
	static public function  chart_doughnut( $points , $second , $title , $xtitle ,$ytitle , $gid='' , $xaxis = ''  ) {
		
		chart::pie_and_doughnut( $points , $second , $title , $xtitle ,$ytitle , $gid , "doughnut" , $xaxis ) ;
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
		
		chart::area_and_splinearea( $points , $second , $title , $xtitle ,$ytitle , $gid , "'stackedArea'" , $xaxis ) ;
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
		
		chart::area_and_splinearea( $points , $second , $title , $xtitle ,$ytitle , $gid , "'stackedBar'"  , $xaxis ) ;
		
	}
	
}
