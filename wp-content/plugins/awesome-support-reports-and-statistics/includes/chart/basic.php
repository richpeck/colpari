<?php
$statuses = wpas_get_post_status();
$ticketCountReport = array();

$second 		  = sanitize_text_field ( isset( $_GET['second'] ) ? $_GET['second'] : 'none' ); 
$search_filter	  =	sanitize_text_field ( isset( $_GET['search_filter'] ) ? $_GET['search_filter'] : ''  ); 
$status_get       = sanitize_text_field ( isset( $_GET['status'] ) ? $_GET['status'] : ''  );
$staff_get        = sanitize_text_field ( isset( $_GET['staff'] ) ? $_GET['staff'] : ''  );
$sDate_get        = sanitize_text_field ( isset( $_GET['sDate'] ) ? $_GET['sDate'] : ''  );
$eDate_get        = sanitize_text_field ( isset( $_GET['eDate'] ) ? $_GET['eDate'] : ''  );
$state_get        = sanitize_text_field ( isset( $_GET['state'] ) ? $_GET['state'] : 'open' );
$chart_type       = sanitize_text_field ( isset( $_GET['type_of_chart'] ) ? $_GET['type_of_chart'] : 'bar' );

$ticket_author    = sanitize_text_field ( isset( $_GET['ticket_author'] ) ? $_GET['ticket_author'] : '' );

$taxonomy_get	 = array();
$cus_fields_get  = array();


$query_cust = rns_get_query_string( $_GET );
$taxonomy_get 	= isset( $query_cust[0] ) ? $query_cust[0] : '' ;
$cus_fields_get = isset( $query_cust[1] ) ? $query_cust[1] : '' ;

$result_data = rns_get_points_array_according_to_chart_type( $second, $statuses, $search_filter, $status_get, $staff_get,  $sDate_get, $eDate_get,  $state_get , $taxonomy_get , $cus_fields_get , $ticket_author  );

$points = $result_data['points'];
$labels = $result_data['labels'];
$colors = $result_data['colors'];

rns_get_chart_by_points_label_and_chart_type( $points , $labels , $colors ,  $second , $chart_type );

$row_title =  __( "Ticket Count" , 'reports-and-statistics' );


