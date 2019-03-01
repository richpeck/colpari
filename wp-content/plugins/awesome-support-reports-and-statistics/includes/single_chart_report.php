<div class="rns-outer-div rns-margintop-20">
    <div class="rns-header-report">
        <?php 
        if( empty( $points ) ) {
        	echo  __( 'NO RECORD FOUND.' , 'reports-and-statistics' );	
        }
        ?>
        <div class="rns-graph-holder" id="rns-graph-holder" style="height:500px;"></div>
        
    </div>
    <div class="rns-header-report rns-scroll" >
        <h2 class="rns-padding_top_bottom">&nbsp;</h2>
        <table cellpadding="10" border="1" cellspacing="0">
     
                <?php
				
					if( !isset( $second ) || ( isset( $second ) && $second == 'none' ) ) {
                        echo '<tr> <th> &nbsp; </th> ';
						
						$points = rns_drop_zero_row_column_from_points_array( $points , "1"  );
						
                        rns_generate_row_heading_from_points_array( $points , $statuses , $searchStatus = array() );	
						
                        echo ' <th> '.  __( 'Total', 'reports-and-statistics' ) .' </th></tr>';
                            if(!empty($points)){
                                echo ' <tr> <td align="center"> <strong>  '. $row_title .'  </strong> </td> ';
                                $total = 0;
                                foreach ( $points as $key => $val ) {
									
	                                    $total+= (is_numeric ( $val ) ? $val : '0') ;
	                                    echo ' <td align="center"> ' . $val . ' </td>';																											
									
                                }
                                echo ' <td align="center"> ' . $total . ' </td>';
                                echo ' </tr> ';
                            }
                    }else{
							$points = rns_drop_zero_row_column_from_points_array( $points , "2"  );
							$column_sum =array();
                            echo '<tr> <th> &nbsp; </th> ';
                            foreach ( $points as $k => $l ) {
                                foreach($points[$k] as $key => $label ) {
								
                                    if ( !empty( $searchStatus ) ) {
                                        $value = array_search( $key ,$statuses );
                                        if ( in_array( $value , $searchStatus ) ) {
                                            echo ' <th> '.$key .' </th> ';
                                        }
                                    } else {
                                        echo ' <th> '. $key .' </th> ';
                                    }
									
									
									
                                    $column_sum[$key] = 0;
                                
                                }
                                    break;
                            }
                            echo ' <th> '.  __( 'Total', 'reports-and-statistics' ) .' </th> </tr> ';
                            
                            if(!empty($points)){
                            $column_total = 0;
                            $total_tickets = 0;
                            foreach ( $points as $key => $val ) {
                               
									if( taxonomy_exists( $second ) ) {
										$user_info = get_term_by( 'term_id', $key, $second );
										echo ' <tr> <td align="center"> <strong> ' .  ucwords( $user_info->name )  . ' </strong> </td> ';
									}
									else if( $second == 'assignee' || $second == 'clients' ) {
										$user_info = get_userdata($key);
										echo ' <tr> <td> <strong> ' .  ucwords( isset( $user_info->display_name ) ? $user_info->display_name : $user_info->user_login )  . ' </strong> </td> ';
									}
									else {
											echo '<tr><td align="center"> <strong> ' .  ucwords( $key )  . ' </strong> </td> ';
									}
									
									$row_total = 0;
									foreach ($val as $status => $count) {
										
										
										$column_sum[$status] = $column_sum[$status] + $count;
										echo ' <td align="center"> ' . $count . ' </td>';
										$row_total = $row_total + $count;
									}
									echo ' <td align="center"> ' . $row_total . ' </td> ';
									echo ' </tr> ';
									$total_tickets = $total_tickets + $row_total;
								
								
                            }
                            echo '<tr> <th>  '.  __( 'Total', 'reports-and-statistics' ) .'  </th> ';
							
                            foreach($column_sum as $key => $total){
                                echo ' <td align="center"> '.$total.' </td>';
                            }
                            echo '<td align="center"> <strong> ' . $total_tickets . ' </strong>  </td>';
                            echo '<tr>';
                        }
                    }
                ?>
        </table>
    </div>
</div>