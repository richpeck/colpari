<div class="rns-outer-div rns-margintop-20">
    <div class="rns-header-report">
        <?php
        if( empty( $points ) ) {
            echo ' NO RECORD FOUND. ';
        }
        ?>
     </div>
    <div class="rns-header-report" >
        <?php
        $i=1;
        foreach( $points as $k => $point_data ) {
        ?>
        <div class="rns-graph-holder<?php echo $i; ?>" id="rns-graph-holder<?php echo $i; ?>" 
        style="width:100%; height:500px;"></div>
        <?php 
		  foreach( $labels[$k] as $o=>$point  ) {
		?>
        <div class="rns-header-report rns-scroll rns-marginbot-20" >
            <table cellpadding="10" border="1" cellspacing="0">
                     <?php
                        if( !isset( $second ) || ( isset( $second ) && $second == 'none' ) ) {
							
							$point = rns_drop_zero_row_column_from_points_array( $point , "1"  );
							
                            echo '<tr> <th> '.$o.'  </th> ';
                            
                            rns_generate_row_heading_from_points_array( $point , $statuses , $searchStatus = array() );	
                            
                            rns_generate_rows_data_from_points_array( $point );
                            
                        }else{
								$point = rns_drop_zero_row_column_from_points_array( $point , "2"  );
								
                               $column_sum = array();
							    echo '<tr> <th> '.$o.' Tickets </th> ';
                                foreach ( $point as $k => $l ) {
                                    foreach($point[$k] as $key => $label ) {
										
										
										
                                        if ( !empty( $searchStatus ) ) {
                                            $value = array_search( $key ,$statuses );
                                            if ( in_array( $value , $searchStatus ) ) {
                                                echo ' <th> '. $key .' </th> ';
                                            }
                                        } else {
                                            echo ' <th> '. $key .' </th> ';
                                        }
                                        $column_sum[$key] = 0;
                                    
                                    }
                                        break;
                                }
                                echo '  </tr> ';
								
                              
								
                                if(!empty($point)){
							    
                                $column_total = 0;
                                $total_tickets = 0;
                                foreach ( $point as $key => $val ) {
                                    
									
                                    if( taxonomy_exists( $second ) ) {
                                        $user_info = get_term_by( 'term_id', $key, $second );
                                        echo ' <tr> <td align="center"> <strong> ' .  ucwords( $user_info->name )  . ' </strong> </td> ';
                                    }
                                    else if( $second == 'assignee' || $second == 'clients' ) {
                                        $user_info = get_userdata($key);
                                        echo ' <tr> <td> <strong> ' .  ucwords( isset( $user_info->display_name ) ? $user_info->display_name : $user_info->user_login ) . ' </strong> </td> ';
                                    } else {
                                            echo '<tr><td align="center"> <strong> ' . ucwords( $key )  . ' </strong> </td> ';
                                    }
                                    
                                    $row_total = 0;
                                    foreach ($val as $status => $count) {
										
                                        $column_sum[$status] = $column_sum[$status] + $count;
                                        echo ' <td align="center"> ' . $count . ' </td>';
                                        $row_total = $row_total + $count;
                                    }
                                    
                                    echo ' </tr> ';
                                    $total_tickets = $total_tickets + $row_total;
                                }
                                echo '<tr> <th>  '.  __( 'Total', 'reports-and-statistics' ) .'  </th> ';
                                foreach($column_sum as $key => $total){
                                    echo ' <td align="center"> '.$total.' </td>';
                                }
                                
                                echo '<tr>';
                            }
                        }
            
                    ?>
            </table>
        </div>
        	
        <?php } ?>
       <?php $i++; } ?>
    </div>
</div>