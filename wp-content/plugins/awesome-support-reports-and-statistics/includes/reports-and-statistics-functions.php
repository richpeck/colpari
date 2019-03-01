<?php
/*
* Function to get Agents List.
* param string
* @return array of agent list.
*/

function rns_get_agents_list( $type = "" ) {
	 $wpdb = rns_get_wpdb(); 
	 
	 if($type=="authors") {
		//Query to get authors of all tickets
		$query = 'Select p.*,u.* from '.$wpdb->prefix.'posts p LEFT JOIN '.$wpdb->base_prefix.'users u on (p.post_author = u.ID)  where p.post_type =%s   group by u.ID';
	
		$agentsList		= $wpdb->get_results ( $wpdb->prepare( $query , 'ticket' ) );		 
		 
	 } else {
		//Query to get Agents List as per Role	
		$query = 'Select U.*,UM.* from '.$wpdb->prefix.'postmeta PM LEFT JOIN '.$wpdb->base_prefix.'users U on (U.ID = PM.meta_value) LEFT JOIN '.$wpdb->base_prefix.'usermeta UM on (UM.user_id = U.ID) where PM.meta_key =%s  and U.user_login!="" group by U.ID';
	
		$agentsList		= $wpdb->get_results ( $wpdb->prepare( $query , '_wpas_assignee' ) );
	 }
		
	return $agentsList;
}


/*
* Function to get Ticket status color
* @Params: Status of ticket and slug of ticket.
* @Return String Color code for ticket status.
*/

function rns_get_ticket_color_by_status( $status , $slug ) {
	
	$ticketColor	= wpas_get_option( 'color_'.$status );
	
	//if ticket color is empty
	if ( $ticketColor == '' ) {
		$args = array(
			'name'        => $slug,
			'post_type'   => 'wpass_status',
			'post_status' => 'publish',
			'numberposts' => 1
		);
		
		$posts_array	= get_posts( $args );
		if ( $posts_array ) {
			$ticketColor	= get_post_meta( $posts_array[0]->ID, 'status_color' ,true );
		}
	}
	
	return $ticketColor;
}
/*
* Function to retrieve Array from comma separated values.
* @Params: Comma separated string.
* @Return: Array of values extracted from passwrded string.
*/

function rns_get_array_values_from_comma_separated_string( $string ) {
	//if string not equal to emoty
	if ( $string != '' ) {
		return explode( ',', $string );
	}
	else {
		return '';
	}
}

/*
* Function to retrieve string for custom tag name .
* @Params: Custom tag type and key .
* @return  String  get name by key and custom field type.
*/

function rns_get_display_name_by_second_option( $second, $key='' ) {
		
		if( $second == 'assignee' || $second == 'clients' ) { // if second dimenssion is assignee
			$user_info = get_userdata($key);
			$display_name = isset( $user_info->display_name ) ? $user_info->display_name : $user_info->user_login ;
		} 
		else if ( $second  != '' &&  taxonomy_exists($second)  ) { // if second dimenssion is taxonmoy
			$user_info = get_term_by( 'term_id', $key, $second ) ;
			$display_name = $user_info->name;			
		}else {
			$display_name = $key;	
		}
		
		return $display_name;
}

/* 
* Function to  get filtered status list
* @Params: status_get | All status 
* @return array list of  filtered statuses.
*/

function rns_get_filtered_status( $status_get, $statuss ) {
	
	$statuses = array();
	// if selected status is not empty
	if( !empty( $status_get ) ) {
		$status = explode(",",$status_get);
		
		foreach( $status as $k=>$val ) {
			$statuses[$val]	= $statuss[$val];
			
		}
	} else {
		$statuses = $statuss;
	}
	
	return $statuses;
}


/* 
* Function to  get filtered agent list
* @Params: agents id 
* @Return: Array of agents.
*/

function rns_get_filtered_agent_list( $staff_get , $user_type = "agents" ) {
	
	$agent_iist = rns_get_agents_list( $user_type );
	
	$filtered_agent_list = array();
	//filter agents if selected in filter option
	if( isset( $staff_get ) && $staff_get != '' ) {
		$filter_agents	= explode( ',' , $staff_get );
		
		foreach($agent_iist as $agent){
			
			if( in_array($agent->ID, $filter_agents) ){ //if agent id exist in filter agents
				$filtered_agent_list[$agent->ID]	= ucwords( $agent->display_name );
			}	
		}
		
	}else{
		
		foreach($agent_iist as $agent){
		
			$filtered_agent_list[$agent->ID]	= ucwords( $agent->display_name );		
		}
	}
	
	return $filtered_agent_list;
}


/* 
* Function to  get filtered deparment list
* @Params: taxonomy | department get id 
* @Return: Array of departments.
*/

function rns_get_filtered_dept_list( $second , $taxonomy_get ) {
	
	$departments	= get_terms( array(
							   'taxonomy'   => $second,
							   'hide_empty' => true
							)
						);
	
	
	
					
	$department_get  = isset($taxonomy_get['tx_'.$second])?$taxonomy_get['tx_'.$second]:'';
	$filtered_dept_list = array();
	//filter departments if selected in filter option
	if( isset( $department_get ) && $department_get != '' ) {
		$filter_departments	= explode( ',' , $department_get );
		
		foreach($departments as $department) {
			
			if( in_array( $department->term_id, $filter_departments) ) { //if department id exist in filtered departments
				$filtered_dept_list[$department->term_id]	= ucwords( $department->name );
			}	
	   }	
  } else {

		foreach( $departments as $department ){
			
			//if department id is set and its value not equal to empty
			if(isset( $department->term_id ) && !empty( $department->term_id )) {	
				$filtered_dept_list[$department->term_id]	= ucwords( $department->name );
			}
			
		}
		
	}
	
	return $filtered_dept_list;
}


/* 
* Function to  get custom field  list
* @Params: field_type | custom feild get id 
* @Return: Array of custom fields.
*/

function rns_get_custom_field_filter_list( $second , $cus_fields_get  ) {
	
	$departments = rn_get_custom_fields_option( $second ); 
	
	
	$department_get  = isset($cus_fields_get['cpf_'.$second])?$cus_fields_get['cpf_'.$second]:'';
	$filtered_dept_list = array();
	//filter departments if selected in filter option
	if( isset( $department_get ) && $department_get != '' ) {
		$filter_departments	= explode( ',' , $department_get );
		
		foreach($departments as $department=>$department_val) {
			
			if( in_array( $department, $filter_departments) ) { 
				$filtered_dept_list[$department]	= ucwords( $department_val );
			}	
	   }	
  } else {

		foreach( $departments as $department=>$department_val ){
			
			//if custom field is set and its value not equal to empty
			if( isset( $department ) && !empty( $department )) {	
				$filtered_dept_list[$department]	= ucwords( $department_val );
			}
			
		}
		
	}
	
	return $filtered_dept_list;
}

/* 
* Function to get graph points by chart type 
* @Params: Second dimension |Status | Search Filter | Status Get | Agent |  Start Date | End Date | State | Taxonomy |       *          Custom Fields
* @Return: Array of graph points and label.
*/

function rns_get_points_array_according_to_chart_type( $second, $statuses, $search_filter, $status_get, $staff_get,  $sDate_get, $eDate_get, $state_get , $taxonomy_get , $cus_fields_get , $ticket_author ) {
	
	$points = $labels = $colors = array();
	
	
	
	if( $second == 'none' ) { // if second dimenssion in none
	
		if ( ! empty( $statuses ) ) { // if status array is not empty
		
		if ( isset( $search_filter ) && $search_filter != '' ) { // if search filter is set and its value is not empty
	
	
			if ( $state_get == 'open' || $state_get == 'both' ) { // if state is open or closed 
					$state = 'open';
				if ( isset( $status_get ) && !empty( $status_get ) ) { // if status is selected for filter list
					$searchStatus = explode( ',',$status_get );
				}
				else {
					$searchStatus = array();
				}
		
				foreach ( $statuses as $status => $label ) {
					
					if ( !empty( $searchStatus ) ) { // if search status is not empty 
 
						if ( in_array( $status,$searchStatus ) ) { // if search status exist in statues array
					
								 $points[$label]	= rns_get_ticket_count_by_status_and_assignee( $status , $state , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
								$labels[]	= $label;
								$colors[]	= rns_get_ticket_color_by_status( $status, $status );
								
													}
						
					} else {
						
						
						$count		= rns_get_ticket_count_by_status_and_assignee( $status , $state , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
						$points[$label]	= $count;
						$labels[]	= $label . '('. $count . ')';
						$colors[]	= rns_get_ticket_color_by_status( $status , $status );
						
					}
				}
				
					
									
			} 
			
			if( $state_get == 'both' ||  $state_get == 'closed') {	// if state is closed or both
				
				$points['closed']	= rns_get_ticket_count_by_status_and_assignee( '' , 'closed' , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
				$labels[]	= 'closed';
				$colors[]	= rns_get_ticket_color_by_status( 'closed', 'closed' );
			}
			
		} else {
			foreach ( $statuses as $status => $label ) {
				
				$count		= rns_get_ticket_count_by_status_and_assignee( $status , $state_get , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
				$points[ucwords( $label )]	= $count;
				$labels[]	= ucwords( $label ) . '(' . $count. ' )';
				$colors[]	= rns_get_ticket_color_by_status( $status , $status );
				
			}
			$labels['closed']	= "Closed";
			$colors['closed']	= rns_get_ticket_color_by_status( 'closed' , 'closed' );
		}
	}
	
	
	} elseif( $second == 'assignee' || $second == 'clients' ) { //Following code will work if second dimension is selected as Agent and clients
			
			if( $second == 'assignee') {	
				$filtered_agent_list = rns_get_filtered_agent_list( $staff_get , "agents"  ) ;
			} else {
				$filtered_agent_list = rns_get_filtered_agent_list( $ticket_author , "authors"  ) ;
			}	
		
		// Check for chosen state
		if ( $state_get == 'open' || $state_get == 'both' ) {
			 $state = 'open';	
			//check for chosen status
			if ( isset( $status_get ) && !empty( $status_get ) ) {
				$searchStatus = explode( ',',$status_get );
			}
			else {
				$searchStatus = array();
			}
			
			foreach ( $statuses as $status => $label ) {
				//Get ticket count by each agent as per filter params
				foreach($filtered_agent_list as $key => $agent) {
					if ( !empty( $searchStatus ) ) { // if search status is not empty
						if ( in_array( $status,$searchStatus ) ) { //if search status is exist in the status array
						
								if( $second == 'assignee') {	
								
								$points[$key][$label]	= rns_get_ticket_count_by_status_and_assignee( $status , $state , $key ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author  );
								
								} else {
								
								$points[$key][$label]	= rns_get_ticket_count_by_status_and_assignee( $status , $state , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $key   );
								
								}
								
								$labels[$key][]	= $label;
								$colors[$key][]	= rns_get_ticket_color_by_status( $status, $status );
								
							
						}
						
					} else {
						
						if( $second == 'assignee') {	
						
						$count		= rns_get_ticket_count_by_status_and_assignee( $status , $state , $key ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
						
						} else {
						
						$count		= rns_get_ticket_count_by_status_and_assignee( $status , $state , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $key );
						
						}
						
						$points[$key][$label]	= $count;
						$labels[$key][]	= $label . '('. $count . ')';
						$colors[$key][]	= rns_get_ticket_color_by_status( $status , $status );
						
					}
				}
			}
		} 
		
		if( $state_get == 'both' || $state_get == 'closed'  ) {	
			foreach($filtered_agent_list as $key => $agent) {
				
				if( $second == 'assignee') {	
				
				$points[$key]['closed']	= rns_get_ticket_count_by_status_and_assignee( '', 'closed' , $key ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
				
				} else {
				
				$points[$key]['closed']	= rns_get_ticket_count_by_status_and_assignee( '', 'closed' , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $key );
				
				}
				
				$labels[$key][]	= 'closed';
				$colors[$key][]	= rns_get_ticket_color_by_status( 'closed', 'closed' );
			}
		}
	}

	elseif ( $second != '' ) { //Following code will work if second dimension is not empty
	
	if(taxonomy_exists( $second )) { // if second dimenssion is taxonomy
	
		$filtered_dept_list = rns_get_filtered_dept_list( $second , $taxonomy_get ) ;
		
	} else {
		
		$filtered_dept_list = rns_get_custom_field_filter_list( $second , $cus_fields_get ) ;
	}

	if ( $state_get == 'open' || $state_get == 'both' ) { //if state is open or both
		$state = 'open';
		//check for chosen status
		if ( isset( $status_get ) && !empty( $status_get ) ) {
			$searchStatus = explode( ',',$status_get );
		}
		else {
			$searchStatus = array();
		}
		
		foreach ( $statuses as $status => $label ) {
			//Get ticket count by each agent as per filter params
			foreach($filtered_dept_list as $key => $department) {
				
				$key_label = taxonomy_exists( $second ) ? $key : $department;
				
				if ( !empty( $searchStatus ) ) {  //search status is not empty
					if ( in_array( $status,$searchStatus ) ) {  // if search status is exist in status array
					
							$points[$key_label][$label]	= rns_get_ticket_count_by_status_and_assignee( $status , $state ,  $staff_get , $sDate_get , $eDate_get ,  $taxonomy_get , $second , $key , $cus_fields_get , $ticket_author  );
							$labels[$key_label][]	= $label;
							$colors[$key_label][]	= rns_get_ticket_color_by_status( $status, $status );
					}
					
				} else {
					
					
					$count		= rns_get_ticket_count_by_status_and_assignee( $status , $state , $staff_get  , $sDate_get , $eDate_get ,  $taxonomy_get , $second , $key , $cus_fields_get , $ticket_author );
					$points[$key_label][$label]	= $count;
					$labels[$key_label][]	= $label . '('. $count . ')';
					$colors[$key_label][]	= rns_get_ticket_color_by_status( $status , $status );
					
				}
				
			}
		}
	} 
	if ( $state_get == 'closed' || $state_get == 'both' ) { // if state is closed or both
		foreach($filtered_dept_list as $key => $department) {
			$key_label = taxonomy_exists( $second ) ? $key : $department;
			
			$points[$key_label]['closed']	= rns_get_ticket_count_by_status_and_assignee( '', 'closed' , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , $key , $cus_fields_get , $ticket_author );
			$labels[$key_label][]	= 'closed';
			$colors[$key_label][]	= rns_get_ticket_color_by_status( 'closed', 'closed' );
		}
	}		
}
	
	$data = array(
					"points"	=> $points,
					"labels"	=> $labels,
					"colors"	=> $colors
			);
			
	return($data);
	
}

/* 
* Function to get Ticket count
* @Params: Status | State | Agent |  Start Date | End Date | Taxonomy | Second dimension | Department |  Custom Fields
* @return Integer Count for ticket as per passed parameters.
*/

function rns_get_ticket_count_by_status_and_assignee( $status = 'queued', $wpasStatus = 'open', $staff = '',  $sDate='', $eDate = '' , $taxonomy_get = '' , $second ,$department , $cus_fields_get , $ticket_author ) {
	
	$args = rns_child_get_agrs_by_status_wpasstatus( $status , $wpasStatus , $ticket_author ) ;
	
	$query = rns_get_ticket_by_arguments($status, $wpasStatus, $staff,  $sDate, $eDate, $taxonomy_get , $second ,$department , $cus_fields_get,$args);
	
	return $query->post_count;

}


/* 
* Function to get argument  by status and state
* @Params: Status, wapsstatus
* @return: array | string  arguments values by status and wpasstatus.
*/

function rns_child_get_agrs_by_status_wpasstatus( $status , $wpasStatus , $ticket_author = "" ) {
	
	// Check for State of ticket.
	if( $wpasStatus == 'open' || $wpasStatus == 'both' ) {
		$args = array(
			'post_type'       	=> 'ticket',
			'post_status'       => $status ,
			'posts_per_page'    => - 1,
		);
			
	} else  {
		$args = array(
			'post_type'       	=> 'ticket',
			'posts_per_page'    => - 1,
		);
		
	}
	
	// if ticket author value is set and not empty
	if( isset( $ticket_author ) && !empty( $ticket_author ) ) {
		
		$args['author'] = 	$ticket_author;	
		
	}
	
	
	return $args;
}


/* 
* Function to get custon field  type
* @Params: Custom Field Name
* @return  string type of custom fields.
*/

function  rn_get_custom_fields_type_by_name( $search_field ) {
	
	$field_type = '';	
	
	// get all custom fields
	$custom_fields = WPAS()->custom_fields->get_custom_fields(); 
	
	if( ! empty( $custom_fields ) ) { // if fields array is not empty 
		foreach ( $custom_fields as $field ) {
			
			if( $field['name']==$search_field ) { //if field name is equal to search field
				$field_type = $field['args']['field_type'];		
			}
			
		}	
	}
    	
	return ( $field_type ) ;
}




/* 
* Function to get custon field  title
* @Params: Custom Field Name
* @return  string type of custom field title.
*/

function  rn_get_custom_fields_title_by_name( $search_field ) {
	
	$field_type = $search_field ;	
	
	// get all custom fields
	$custom_fields = WPAS()->custom_fields->get_custom_fields(); 
	
	if( ! empty( $custom_fields ) ) { // if fields array is not empty 
		foreach ( $custom_fields as $field ) {
			
			if( $field['name']==$search_field ) { //if field name is equal to search field
				$field_type = $field['args']['title'];		
			}
			
		}	
	}
    	
	return ( $field_type ) ;
}


/* 
* Function to get custon field  options
* @Params: Field Name
* @return  array list of options extracted by custom fields.
*/

function  rn_get_custom_fields_option( $search_field ) {
	
	$data = array();	
	$wpdb = rns_get_wpdb(); 
    		// get all custom fields
			$fields = WPAS()->custom_fields->get_custom_fields(); 
			
    		if( ! empty( $fields ) ) { //if custom fields array is empty
	    		foreach ( $fields as $field ) {

	    		$field_type = $field['args']['field_type'];
					if($field['name']==$search_field) { // if search field name is exist 
					
						// if field type is checkbox |radio |select
						if($field_type=='select' || $field_type=='radio'  || $field_type=='checkbox' ) {  	
							$data = $field['args']['options'];
						}else {
							
							 $metas = $wpdb->get_results( 
							 $wpdb->prepare("SELECT distinct(meta_value) as meta_value FROM $wpdb->postmeta where meta_key = %s", '_wpas_'.$field['name'])
						   );
						   foreach( $metas as  $meta) {
							 
								 $data[ $meta->meta_value ] = $meta->meta_value;
							 
						   }
						}
					}
	    			
	    		}	
    		}

	return $data;
}


/* 
* Function to check custom field exist or not 
* @Params: Field Name
* @Return: boolean true if custom field exist .
*/

function rn_custom_field_exist( $search_field ) {
	
	// get all custom fields
	$fields = WPAS()->custom_fields->get_custom_fields();
	
	$types  = array( "select" , "radio" , "checkbox" , "text" , "date-field" );
	if( ! empty( $fields ) ) { // if fields array is not empty
		foreach ( $fields as $field ) {
			// if search field name is exist   
			if( $field[ 'name' ] == $search_field && ( in_array( $field['args']['field_type'] , $types ) ) ) {
				return true;
			}
		}	
	}	
	return false;		
}


/* 
* Function to get custom query string 
* @Params: query string
* @Return: arrray of custom taxonomy and custom field .
*/

function rns_get_query_string( $query_string ) {
	
	$taxonomy_get	 = array();
	$cus_fields_get  = array();
	
	foreach($query_string as $key=>$val) {
		
		if(strstr($key,"tx_")) { //if  taxonomy value submitted from filter 
			$taxonomy_get[$key] = $val;
		}
		if(strstr($key,"cpf_")) { //if custom field value submitted from filter
			$cus_fields_get[$key] = $val;
		}

	}
	
	return ( array( $taxonomy_get, $cus_fields_get  ) );
}


/*
* Function to get chart view by chart type ,points and label
* @Params: chart type | array of points and label
* @Return: void .
*/

function rns_get_chart_by_points_label_and_chart_type( $points , $labels , $colors , $second , $chart_type ) {
	
	// if points array is not empty		
	if( !empty( $points ) ) {
			
		$function  = "chart_".$chart_type;
		chart::$function($points , $second ,  __( "Ticket Count With Status", 'reports-and-statistics' ) ,  __( "Status", 'reports-and-statistics' ) ,  __( "Number of Tickets", 'reports-and-statistics' ) );
	}		

}


/* 
* Function to get Ticket count
* @Params: Status | State | Agent |  Start Date | End Date | Taxonomy | Second dimension | Custom Fields
* @return array Count for ticket as per passed parameters.
*/

function rns_get_reply_count_by_status_and_assignee( $status = 'queued', $wpasStatus = 'open', $staff = '',  $sDate='', $eDate = '' , $taxonomy_get = '' , $second ,$department , $cus_fields_get , $ticket_author  ) {
	
	// Check for State of ticket.
	$args = rns_child_get_agrs_by_status_wpasstatus( $status , $wpasStatus , $ticket_author ) ;
	
	// if status is both
	if( $wpasStatus == 'both' ) {
		$wpasStatus_search = 'open,closed' ;
	}else {
		$wpasStatus_search = $wpasStatus ;
	}
	
	$query = rns_get_ticket_by_arguments($status, $wpasStatus_search, $staff,  $sDate, $eDate, $taxonomy_get , $second ,$department , $cus_fields_get,$args);
	

	$reply_data = $datas = $data = array();
	$types = array( "all", "client" ,"agent");
	$statuses = array( "average", "maximum" ,"median");
	
	
	foreach( $types as $type ) {
		foreach( $statuses as $status ) {
			$datas[$type][$status] = 0;
		}
	}
		// if post count is greater that 0
		if( $query->post_count>0 ) {
		
			foreach( $query->posts as $posts ) {
			 
				$reply_data[] = rn_get_reply_count_by_ticketid( $posts->ID , $sDate , $eDate );
			}
		
		$data['all_total']  =  $data['client_total'] = $data['agent_total'] = 0; 
		$no_of_reply_records = count( $reply_data );
		
		for( $i=0;$i<$no_of_reply_records; $i++ ) {
	
			$data['alls'][$i]	  = $reply_data[$i]['all'];
			$data['clients'][$i]  = $reply_data[$i]['client'];
			$data['agents'][$i]   = $reply_data[$i]['agent'];
			$data['all_total']+=    $reply_data[$i]['all'];
			$data['client_total']+= $reply_data[$i]['client'];
			$data['agent_total']+=  $reply_data[$i]['agent'];
		}
		
		$datas['all']['average'] 	= number_format(( $data['all_total'] / $query->post_count ) , 2 ,'.','' );
	    $datas['all']['median'] 	= rns_calculate_median( $data['alls'] );
		$datas['all']['maximum'] 	= max( $data['alls'] );
		$datas['client']['average'] = number_format( ( $data['client_total'] / $query->post_count ) , 2 ,'.','' );
	    $datas['client']['median'] 	= rns_calculate_median( $data['clients'] );
		$datas['client']['maximum']	= max( $data['clients'] );
		$datas['agent']['average']  = number_format( ( $data['agent_total'] / $query->post_count ) , 2 ,'.','' );
	    $datas['agent']['median'] 	= rns_calculate_median( $data['agents'] );
		$datas['agent']['maximum'] 	= max( $data['agents'] );
	} 
	
	return $datas;
}


/* 
* Function to get replies count by ticket id
* @Params: Ticket Id
* @return:  arrray reply counts for all , agents and cleints.
*/

function  rn_get_reply_count_by_ticketid( $ticket_id , $sdate , $edate ) {
	
		
	$wpdb = rns_get_wpdb(); 
	//Query to get reply count as per Ticket id
	
	$between = '' ;
		
	$count = array( "all" => 0, "client" => 0, "agent" => 0 );
	
	 $query = 'SELECT * FROM '.$wpdb->prefix.'posts p,'.$wpdb->base_prefix.'usermeta u WHERE p.post_author=u.user_id and  p.post_type="%s"  and p.post_parent="%d" and u.meta_key="%s" '.$between.' ';
	
	$result		= $wpdb->get_results ( $wpdb->prepare( $query , 'ticket_reply' ,  $ticket_id, $wpdb->prefix.'capabilities' ) );
	
	$count['all'] = count( $result );
	
	
	$query = 'SELECT * FROM '.$wpdb->prefix.'posts p,'.$wpdb->base_prefix.'usermeta u WHERE p.post_author=u.user_id and  p.post_type="%s"  and p.post_parent="%d" and u.meta_key="%s" and u.meta_value like "%s" '.$between.' ' ;

	$result		= $wpdb->get_results ( $wpdb->prepare( $query , 'ticket_reply' , $ticket_id, $wpdb->prefix.'capabilities' ,'%wpas_user%' ) );
	
	$count['client'] = count( $result );
	
	$query = 'SELECT * FROM '.$wpdb->prefix.'posts p,'.$wpdb->base_prefix.'usermeta u WHERE p.post_author=u.user_id and  p.post_type="%s"  and p.post_parent="%d" and u.meta_key="%s" and u.meta_value not like "%s" '.$between.' ' ;
	
	$result		= $wpdb->get_results ( $wpdb->prepare( $query , 'ticket_reply'  , $ticket_id, $wpdb->prefix.'capabilities' ,'%wpas_user%' ) );
	
	$count['agent'] = count( $result );
	
	
	return $count;
	
}


/* 
* Function to get graph points by chart type 
* @Params: Second dimension | Status |  Search Filter | State | Status Get | Agent |  Start Date | End Date | State | Taxonomy | Custom Fields
* @Return: Array of graph points and label.
*/

function rns_get_average_reply_according_to_chart_type( $second, $statuses, $search_filter, $status_get, $staff_get,  $sDate_get, $eDate_get, $state_get , $taxonomy_get , $cus_fields_get , $ticket_author ) {
	
	$points = $labels = $colors = array();
	
	$status = implode( "," , array_keys( $statuses ) );
	
	// if second dimenssion is none 
	if( $second == 'none' ) {
				
		 $reply_counts = rns_get_reply_count_by_status_and_assignee( $status , $state_get , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
		 
		 foreach( $reply_counts as  $key=>$counts ) {
	   	 
			foreach( $counts as $label => $count ) {
				$points[$key][$label] =	$count;
				$labels[$key][]	     = $label;
				$colors[$key][]	     = rns_get_ticket_color_by_status( $status, $status );
			}		
		}
	
			
	} elseif( $second == 'assignee' || $second == 'clients' ) { //Following code will work if second dimension is selected as Agent and clients
			
			if( $second == 'assignee') {	
				$filtered_agent_list = rns_get_filtered_agent_list( $staff_get , "agents"  ) ;
			} else {
				$filtered_agent_list = rns_get_filtered_agent_list( $ticket_author , "authors"  ) ;
			}	
			//Get ticket count by each agent as per filter params
			foreach($filtered_agent_list as $key => $agent) {
				
				if( $second == 'assignee') {
				
				$reply_counts = rns_get_reply_count_by_status_and_assignee( $status , $state_get , $key ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
				
				} else {
				
				$reply_counts = rns_get_reply_count_by_status_and_assignee( $status , $state_get , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $key );
				
				}
				
				
			 foreach($reply_counts as  $type=>$counts ) {
				foreach( $counts as $label => $count ) {
					$points[$type][$key][$label] =	$count;
					$labels[$type][$key][]	     = $label;
					$colors[$type][$key][]	     = rns_get_ticket_color_by_status( $status, $status );
				}
			 }
			}	
	}
	
	elseif( $second == 'status' ) { //Following code will work if second dimension is selected as status.
	
	 	if( $state_get=='open' || $state_get=='both' ) {
			//Get ticket count by each agent as per filter params
			foreach($statuses as $key => $valu) {
												
				$reply_counts = rns_get_reply_count_by_status_and_assignee( $key , $state_get , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
			 foreach($reply_counts as  $type=>$counts ) {
				foreach( $counts as $label => $count ) {
					$points[$type][$valu][$label] =	$count;
					$labels[$type][$valu][]	     = $label;
					$colors[$type][$valu][]	     = rns_get_ticket_color_by_status( $status, $status );
				}	
			 }
			}
		}
		
		if( $state_get=='closed' || $state_get=='both'  ) {  //if state is closed or both
			$valu =  'closed';
			$reply_counts = rns_get_reply_count_by_status_and_assignee( $valu , 'closed' , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
			 foreach($reply_counts as  $type=>$counts ) {
				foreach( $counts as $label => $count ) {
					$points[$type][$valu][$label] =	$count;
					$labels[$type][$valu][]	     = $label;
					$colors[$type][$valu][]	     = rns_get_ticket_color_by_status( $status, $status );
				}	
			 }
		}
		
		
	}
	
	elseif ( $second != ''  ) { //Following code will work if second dimension is not empty.
		
		if(taxonomy_exists( $second )) { // if second dimenssion is taxonomy
			$filtered_dept_list = rns_get_filtered_dept_list( $second , $taxonomy_get ) ;
		} else {	
			$filtered_dept_list = rns_get_custom_field_filter_list( $second , $cus_fields_get ) ;
		}
		
		
		foreach($filtered_dept_list as $key => $department) {
				$key_label = taxonomy_exists( $second ) ? $key : $department;		
							
		$reply_counts = rns_get_reply_count_by_status_and_assignee( $status , $state_get , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , $key , $cus_fields_get , $ticket_author );
			foreach($reply_counts as  $type=>$counts ) {	
				foreach( $counts as $label => $count ) { 
				$points[$type][$key_label][$label] =	$count;
				$labels[$type][$key_label][]	     = $label;
				$colors[$type][$key_label][]	     = rns_get_ticket_color_by_status( $status, $status );
			}
			}
		}
				
	}
	
	$data = array(
					"points"	=> $points,
					"labels"	=> $labels,
					"colors"	=> $colors
			);
		
	return($data);
	
}


/*
* Function to retrieve graph for productivity analysis report by points .
* @Params:  points | labels | colors | second | chart type for graph.
* @Return: void .
*/

function rns_get_productivity_chart_by_points_label_and_chart_type( $points , $labels , $colors , $second , $chart_type ) {
	
	//if points array not empty
	if( !empty( $points ) ) {
		$i=1;
		foreach( $points as $k =>	$point ) {
			
			$function  = "chart_".$chart_type;
			chart::$function($point , $second , __( "Average/Median/Maximum" , 'reports-and-statistics' ) .  " ( ".ucfirst($k )." ".__("Replies" , 'reports-and-statistics' )  ." ) " , "" ,__( "Number of Replies" , 'reports-and-statistics' ) , $i);
			$i++;
		}	
	}		
}

/*
* Function to retrieve second dimenssion filter option by report .
* @Params:  string for report name | filter for second dimenssion .
* @Return: boolean return true or false .
*/

function rns_show_second_dimenssion_filter_option_by_report( $report , $filter ) {
	
	// if report is basic report and second dimenssion is status
	if( $report=='basic_report' &&  $filter=='status' ) {
		return false ;	
	}
	
	// if report is resolution report and second dimenssion is state
	if( $report=='resolution_report' &&  $filter=='state' ) {
		return false ;	
	}
	
		return true ;	
}



/* 
* Function to get resolution report graph
* @Params: Second dimension |  Status | Search Filter | Status Get | Agent |  Start Date | End Date | State | Taxonomy | Custom Fields
* @Return: Array of graph points and label.
*/

function rns_get_resolution_analysis_according_to_chart_type( $second, $statuses, $search_filter, $status_get, $staff_get,  $sDate_get, $eDate_get, $state_get , $taxonomy_get , $cus_fields_get , $ticket_author ) {
	
	$points = $labels = $colors = array();
	
	$status = implode( "," , array_keys( $statuses ) );
	
	// if second dimenssion is none
	if( $second == 'none' ) {
		
					
		 $reply_counts = rns_get_resolution_count_by_status_and_assignee( $status , $state_get , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
		 
		 foreach($reply_counts as   $label => $count ) {
	   	 
				$points[$label] =	$count;
				$labels[]	     = $label;
				$colors[]	     = rns_get_ticket_color_by_status( $status, $status );
					
		}
	
			
	} elseif( $second == 'assignee' || $second == 'clients' ) { //Following code will work if second dimension is selected as Agent and clients
			
			if( $second == 'assignee') {	
				$filtered_agent_list = rns_get_filtered_agent_list( $staff_get , "agents"  ) ;
			} else {
				$filtered_agent_list = rns_get_filtered_agent_list( $ticket_author , "authors"  ) ;
			}
	
			//Get ticket count by each agent as per filter params
			foreach($filtered_agent_list as $key => $agent) {
				
				if( $second == 'assignee') {	
												
				$reply_counts = rns_get_resolution_count_by_status_and_assignee( $status , $state_get , $key ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
				
				} else  {
				
				$reply_counts = rns_get_resolution_count_by_status_and_assignee( $status , $state_get , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $key );
				
				}
				
			 foreach($reply_counts as  $label => $count ) {	

					$points[$key][$label] =	$count;
					$labels[$key][]	     = $label;
					$colors[$key][]	     = rns_get_ticket_color_by_status( $status, $status );
					
			 }
			}
		
		
	}
	elseif( $second == 'status' ) { //Following code will work if second dimension is selected as Agent.
	
	
	 	if( $state_get=='open' || $state_get=='both' ) { // if state is open or both
			//Get ticket count by each agent as per filter params
			foreach($statuses as $key => $valu) {
												
				$reply_counts = rns_get_resolution_count_by_status_and_assignee( $key , $state_get , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
			 foreach($reply_counts as $label => $count ) {	

					$points[$valu][$label] =	$count;
					$labels[$valu][]	     = $label;
					$colors[$valu][]	     = rns_get_ticket_color_by_status( $status, $status );
				}	
			 
			}
		}
		
		if( $state_get=='closed' || $state_get=='both'  ) { //if state is closed or both
			$valu =  'closed';
			$reply_counts = rns_get_resolution_count_by_status_and_assignee( $valu , 'closed' , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
			 foreach($reply_counts as  $label => $count ) {	
 
					$points[$valu][$label] =	$count;
					$labels[$valu][]	     = $label;
					$colors[$valu][]	     = rns_get_ticket_color_by_status( $status, $status );
					
			 }
		}
		
	}
	
	elseif ( $second != '' ) { //Following code will work if second dimension is not empty.
		
	
		if(taxonomy_exists( $second )) { // if second dimenssion is texonomy
			$filtered_dept_list = rns_get_filtered_dept_list( $second , $taxonomy_get ) ;
		} else {	
			$filtered_dept_list = rns_get_custom_field_filter_list( $second , $cus_fields_get ) ;
		}

		foreach($filtered_dept_list as $key => $department) {
					$key_label = taxonomy_exists( $second ) ? $key : $department;			
							
		$reply_counts = rns_get_resolution_count_by_status_and_assignee( $status , $state_get , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , $key , $cus_fields_get , $ticket_author );
			foreach($reply_counts as  $label => $count ) {	
				
				$points[$key_label][$label] =	$count;
				$labels[$key_label][]	     = $label;
				$colors[$key_label][]	     = rns_get_ticket_color_by_status( $status, $status );
			
			}
		}
				
	}
	
	$data = array(
					"points"	=> $points,
					"labels"	=> $labels,
					"colors"	=> $colors
			);
		
	return($data);
	
}


/* 
* Function to check minutes value is greater than the limit define in settings
* @Params: Points | Second 
* @Return: boolean true if minutes or hours grater than limit
*/

function rns_check_minutes_value_in_points_array( $points, $second ) {
	
	$limit = get_option('rns_minutes_limit' , 1000 );
	
	$response = false;
	
	if($second=="none") { // if second dimenssion none 
		
		foreach( $points as $key => $val ) {
			
				if( $val>=$limit ) { // if value is greater than or equal to minutes limit
					$response = true;
				}
		}
		
	} else {
		
		foreach( $points as $key=>$val ) {
			foreach( $points[$key] as $k=>$v ) {	
				
				if( $v>=$limit ) { // if value is greater than or equal to minutes limit
					$response = true;
				}
					
			}
		}
	}
	
	return $response;
}

/* 
* Function to conver time from minutes to hours 
* @Params: Points | Second 
* @Return  array of points with coverssion hours data.
*/

function rns_convert_points_data_minutes_to_hour($points,$second) {
	
	if($second=="none") { // if second dimenssion none 
		
		foreach($points as $key=>$val) {
				$points[$key] = number_format(($val/60),2,'.','');				
		}
		
	} else {
		
		foreach($points as $key=>$val) {
			foreach($points[$key] as $k=>$v) {	
					$points[$key][$k] = number_format(($v/60),2,'.','')	;
			}
		}
	}
	
	return $points;
}


/*
* Function to conver time from hours to days
* @Params: Points | Second
* @Return array of points with coverssion days data.
*/

function rns_convert_points_data_hours_to_day($points,$second) {
	
	if($second=="none") { // if second dimenssion none
		
		foreach($points as $key=>$val) {
				$points[$key] = number_format(($val/24),2,'.','');
		}
		
	} else {
		
		foreach($points as $key=>$val) {
			foreach($points[$key] as $k=>$v) {
					$points[$key][$k] = number_format(($v/24),2,'.','')	;
			}
		}
	}
	
	return $points;
}



/*
* Function to set query argument
* @Params: Status | State | Agent | Start Date, | End Date |  Taxonomy | Second dimension | Department| Custom Fields | Argument
* @Return:  array type of tickets.
*/
function rns_get_ticket_by_arguments($status, $wpasStatus_search, $staff,  $sDate, $eDate, $taxonomy_get , $second ,$department , $cus_fields_get, $args) {
	 
	// Check for Agent filter.
	if ( $staff != '' ) {
		
		$args['meta_query'] = array(
								'relation' => 'AND',
							   array(
								   'key'     => '_wpas_assignee',
								   'value'   => $staff,
								   'compare' => 'in',
							   )
						   );
	}
	
	// meta query for status
	if ( empty ( $args['meta_query'] ) ) {
			$args['meta_query']	= array (
									'relation' => 'AND',
									 array(
										'key'     => '_wpas_status',
									   	'value'   => $wpasStatus_search,
									   	'compare' => 'in',
								   	)
								);
		} else {
			$args['meta_query'][]	= array (
									'relation' => 'AND',
								   	array(
										'key'     => '_wpas_status',
									   	'value'   => $wpasStatus_search,
									   	'compare' => 'in',
								   	)
								 );
		}
	
	
	// check for custom post field filter
	if(rn_custom_field_exist($second) && $second!='assignee' ) {
		$field_type = rn_get_custom_fields_type_by_name( $second );
		if( isset( $field_type ) && $field_type == 'checkbox' ) {
			
				if( is_array( $department ) && count( $department ) == 2 ) {
					

						$args['meta_query'][] = array(
										'relation' => 'or',
									   array(
										   'key'     => '_wpas_' . $second,
										   'value'   => '"'.$department[0].'"',
										   'compare' => 'like',
									   ),
									   array(
										   'key'     => '_wpas_' . $second,
										   'value'   => '"'.$department[1].'"',
										   'compare' => 'like',
									   )
						);
					
				}else {
					
					if( is_array( $department ) && count($department)== 1 ) {
						$department = $department[0];
					}
					
					$args['meta_query'][] = array(
									'relation' => 'AND',
								   array(
									   'key'     => '_wpas_' . $second,
									   'value'   => '"'.$department.'"',
									   'compare' => 'like',
								   )
					);
				}
			
		} else {
				$args['meta_query'][] = array(
										'relation' => 'AND',
									   array(
										   'key'     => '_wpas_' . $second,
										   'value'   => $department,
										   'compare' => 'in',
									   )
				);
		}
	}
		// check for taxonomy fiter
	
		$args['tax_query'] = array();
		$argsTax = array();
		if ( $second  != '' &&  taxonomy_exists($second)  ) {
			$argsTax[] = array(
					'taxonomy' => $second ,
					'field'    => 'term_id',
					'terms'    => $department,
					'operator' => 'IN',
				);
	   }
	   
	   // Filter apply for selected taxonomy filter
	   
	   if( isset ( $taxonomy_get ) && count($taxonomy_get)>0 && is_array( $taxonomy_get ) ) {
			foreach($taxonomy_get as $key=>$value) {

				$taxonomy = str_replace("tx_","",$key);
				if($second!== $taxonomy) {
					$department = $value;
					if ( $department != '' ) {
						$argsTax[] =
							array(
								'relation' => 'AND',
								'taxonomy' => $taxonomy,
								'field'    => 'term_id',
								'terms'    => array($department),
								'operator' => 'IN',
							);
					}
				}
			
			}
		}
	$args['tax_query'] = $argsTax;
	if(empty($argsTax)) {
		unset($args['tax_query']);
	}
	
	// Filter apply for selected custom post field filter 
	
	if( isset ( $cus_fields_get ) && count($cus_fields_get)>0 && is_array( $cus_fields_get ) ) {
			foreach($cus_fields_get as $key=>$value) {
				
				$custom_field = str_replace("cpf_","",$key);
				if($second!== $custom_field) {
					$custom_field_value = $value;
					if ( $custom_field_value != '' ) {
						
							$args['meta_query'][] = array(
								'relation' => 'AND', 
							   array(
								   'key'     => '_wpas_' . $custom_field,
								   'value'   => $custom_field_value,
								   'compare' => 'in',
							   )
							);
					}
				}
			
			}
		}
			
	// Filter applied as per Date field.
	
	
	// if action not equal to reply_report and resolution reporrt
	
		if ( $sDate != '' && $eDate != '' ) { //if start date and end date is not empty
			
			$filterStartDate = explode( '-' , $sDate );
			$filterEndDate = explode( '-' , $eDate );
			$args['date_query'] = array(
				'after'     => array(
					'year'  => $filterStartDate[0],
					'month' => $filterStartDate[1],
					'day'   => $filterStartDate[2],
				),
				'before'    => array(
					'year'  => $filterEndDate[0],
					'month' => $filterEndDate[1],
					'day'   => $filterEndDate[2],
				),
				'inclusive' => true,
			);					
		}
	

	$query = new WP_Query( $args );
	return 	$query;
}



/* 
* Function to get Ticket count
* @Params: Statu | State | Agent | Department | Tag | Start Date | End Date | Taxonomy | Second dimension | Custom Fields
* @return array Count for average and median time as per passed parameters.
*/

function rns_get_resolution_count_by_status_and_assignee( $status = 'queued', $wpasStatus = 'open', $staff = '',  $sDate='', $eDate = '' , $taxonomy_get = '' , $second ,$department , $cus_fields_get , $ticket_author  ) {
	
	// Check for State of ticket.
	$wpdb = rns_get_wpdb(); 
	
	$args = array(
			'post_type'       	=> 'ticket',
			'post_status'       => $status ,
			'posts_per_page'    => - 1,
		);
		
	if( isset( $ticket_author ) && !empty( $ticket_author ) ) {
		
		$args['author'] = 	$ticket_author;	
		
	}	
		
	$wpasStatus_search = 'closed' ;
	
	$query = rns_get_ticket_by_arguments($status, $wpasStatus_search, $staff,  $sDate, $eDate, $taxonomy_get , $second ,$department , $cus_fields_get,$args);
	
	$datas = array();
	$statuses = array( "average", "median");
	
		foreach( $statuses as $status ) {
			$datas[$status] = 0;	
		}
		
		// if start date and end date is not empty from filter
		$first_con = $second_con = $third_con = "" ;
		
		
		// if post count greater than 0
		if( $query->post_count>0 ) {
			
			$time = array(0=>0); $i = 0;
			
			foreach( $query->posts as $posts ) {  
			
				$post_query = "SELECT * FROM ".$wpdb->prefix."posts p , ".$wpdb->prefix."postmeta pm  where p.ID=pm.post_id and  p.ID = '".$posts->ID."' and pm.meta_key='_ticket_closed_on' ".$first_con."  ";
				
				$result	= $wpdb->get_row (  $post_query  );	
				
				// if result array is set and count of array is greater that 0
				if( isset( $result ) && count( $result ) > 0 )  {	
				
					$time[$i]  = rns_get_time_difference( $result->post_date , $result->meta_value  ) ;	
					$i++;
					
				} else {
				   
				    $post_query = "SELECT * FROM ".$wpdb->prefix."posts where  post_parent = '".$posts->ID."' and post_type='ticket_reply' ".$second_con."  order by id desc limit 1   ";
				   
					$result	= $wpdb->get_row (  $post_query  );	
					// if result array is set and count of array is greater that 0	
					if( isset( $result ) && count( $result ) > 0 )  {	
					
					    $time[$i]  = rns_get_time_difference( $posts->post_date , $result->post_modified  ) ;	
						$i++;
						
					} else {
						//if search date filter is empty
						if(!empty( $third_con )) {
						
							$post_date = date( "Y-m-d" , strtotime( $posts->post_modified ) ) ;
							//if closed time betwenn search date					
							if( $post_date >= $sDate && $post_date <= $eDate ) {
								$time[$i]  = rns_get_time_difference( $posts->post_date , $posts->post_modified  ) ;	
								$i++;
							}
						} else {	
								$time[$i]  = rns_get_time_difference( $posts->post_date , $posts->post_modified  ) ;	
								$i++;
						}
					}
				}
				
				
			}
			$total_time    =  array_sum( $time );
			$total_tickets =  count( $time );
			$datas['average'] = number_format( ( $total_time/$total_tickets ) , 2 ,'.','' );
			$datas['median'] = rns_calculate_median( $time );
				
	} 
	
	return $datas;
	
}


/* 
* Function to get time difference between two dates
* @Params: post_date | modified table
* @Return: string .
*/

function rns_get_time_difference( $date1 , $date2 ) {
	
	$seconds = strtotime( $date2 ) - strtotime( $date1 );
	$minutes = floor(($seconds )/60);
	
	if( $minutes < 0 ) { // if minutes value is less than zero	
		$minutes = 0;
	}

	return $minutes;
}


/* 
* Function to get resolution chart view by chart type ,points and label 
* @Params: points | labels | colors | second | chart type for graph.
* @Return: void 
*/

function rns_get_resolution_chart_by_points_label_and_chart_type( $points , $labels , $colors , $second , $chart_type , $time_in  ) {	
	
	// if points array is not empty
	if( !empty( $points ) ) {	
		
		$function  = "chart_".$chart_type;
		chart::$function($points , $second , __( "Average Time In" , 'reports-and-statistics' )." ".$time_in." ". __("To Close A Ticket " , 'reports-and-statistics' ) , "" , $time_in  );
		
	}		
}



/* 
* Function to get delay analysis graph data
* @Params: Second dimension | Status | Search filter | Status Get | Agent |  Start Date | End Date | State | Taxonomy |   Custom Fields
* @Return: Array of graph points and label.
*/

function rns_get_delay_reply_according_to_chart_type( $second, $statuses, $search_filter, $status_get, $staff_get,  $sDate_get, $eDate_get, $state_get , $taxonomy_get , $cus_fields_get , $ticket_author ) {
	
	$points = $labels = $colors = array();
	
	$status = implode( "," , array_keys( $statuses ) );
	
	// if secnd dimenssion is none
	if( $second == 'none' ) {
				
		 $reply_counts = rns_get_delay_time_by_status_and_assignee( $status , $state_get , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
		 
		 foreach( $reply_counts as  $key=>$counts ) {
	   	 
			foreach( $counts as $label => $count ) {
				$points[$key][$label] =	$count;
				$labels[$key][]	     = $label;
				$colors[$key][]	     = rns_get_ticket_color_by_status( $status, $status );
			}		
		}
	
			
	} elseif( $second == 'assignee' || $second == 'clients' ) { //Following code will work if second dimension is selected as Agent and clients
			
			if( $second == 'assignee') {	
				$filtered_agent_list = rns_get_filtered_agent_list( $staff_get , "agents"  ) ;
			} else {
				$filtered_agent_list = rns_get_filtered_agent_list( $ticket_author , "authors"  ) ;
			}
	
			//Get ticket count by each agent as per filter params
			foreach($filtered_agent_list as $key => $agent) {
				
				if( $second == 'assignee') {
					
				$reply_counts = rns_get_delay_time_by_status_and_assignee( $status , $state_get , $key ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
				
				} else {
				
				$reply_counts = rns_get_delay_time_by_status_and_assignee( $status , $state_get , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $key );
				
				}
				
			 foreach($reply_counts as  $type=>$counts ) {
				foreach( $counts as $label => $count ) {
					$points[$type][$key][$label] =	$count;
					$labels[$type][$key][]	     = $label;
					$colors[$type][$key][]	     = rns_get_ticket_color_by_status( $status, $status );
				}
			 }
			}
	}
	elseif( $second == 'status' ) { //Following code will work if second dimension is selected as status.
	
		// if state is open or both
	 	if( $state_get=='open' || $state_get=='both' ) {
			//Get ticket count by each agent as per filter params
			foreach($statuses as $key => $valu) {
												
				$reply_counts = rns_get_delay_time_by_status_and_assignee( $key , $state_get , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
			 foreach($reply_counts as  $type=>$counts ) {
				foreach( $counts as $label => $count ) {
					$points[$type][$valu][$label] =	$count;
					$labels[$type][$valu][]	     = $label;
					$colors[$type][$valu][]	     = rns_get_ticket_color_by_status( $status, $status );
				}	
			 }
			}
		}
		
		// if state is closed or both
		if( $state_get=='closed' || $state_get=='both'  ) {
			$valu =  'closed';
			$reply_counts = rns_get_delay_time_by_status_and_assignee( $valu , 'closed' , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
			 foreach($reply_counts as  $type=>$counts ) {
				foreach( $counts as $label => $count ) {
					$points[$type][$valu][$label] =	$count;
					$labels[$type][$valu][]	     = $label;
					$colors[$type][$valu][]	     = rns_get_ticket_color_by_status( $status, $status );
				}	
			 }
		}
		
		
	}
	
	elseif ( $second != '' ) { //Following code will work if second dimension is not empty.
		
		if( taxonomy_exists( $second )) { // if second dimenssion is taxonomy 
			$filtered_dept_list = rns_get_filtered_dept_list( $second , $taxonomy_get ) ;
		} else {	
			$filtered_dept_list = rns_get_custom_field_filter_list( $second , $cus_fields_get ) ;
		}
		
		
		foreach($filtered_dept_list as $key => $department) {
				$key_label = taxonomy_exists( $second ) ? $key : $department;			
							
		$reply_counts = rns_get_delay_time_by_status_and_assignee( $status , $state_get , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , $key , $cus_fields_get , $ticket_author );
			foreach($reply_counts as  $type=>$counts ) {	
				foreach( $counts as $label => $count ) { 
				$points[$type][$key_label][$label] =	$count;
				$labels[$type][$key_label][]	     = $label;
				$colors[$type][$key_label][]	     = rns_get_ticket_color_by_status( $status, $status );
			}
			}
		}
				
	}
	 	
	$data = array(
					"points"	=> $points,
					"labels"	=> $labels,
					"colors"	=> $colors
			);
		
	return($data);
	
}



/* 
* Function to check minutes value is greater than limit set from settings page
* @Params: Points | Second 
* @Return: boolean true if minutes or hours grater than limit 
*/

function rns_delay_report_check_minutes_value_in_points_array( $points, $second ) {
	
	$limit = get_option('rns_minutes_limit' , 1000);
	
	$response = false;
	
	if($second=="none") { // if second dimenssion none 
		
		foreach( $points as $key=>$val ) {
			foreach( $points[$key] as $k=>$v ) {	
				
				if( $v>=$limit ) { // if value is greater than or equal to minutes limit
					$response = true;
				}
					
			}
		}
		
	} else {
		
		foreach( $points as $val ) {
			foreach( $val as $va ) {	
				foreach($va as $k=>$v) {
					if( $v>=$limit ) { // if value is greater than or equal to minutes limit
						$response = true;
					}
				}
					
			}
		}
	}
	
	return $response;
}

/* 
* Function to convert time from minutes to hours 
* @Params: Points | Second 
* @Return: array of coverted points data from minutes to hours
*/

function rns_delay_report_convert_points_data_minutes_to_hour( $points = array() ,$second ) {
	
	if($second=="none") { // if second dimenssion none 
		
		foreach($points as $key=>$val) {
			foreach($points[$key] as $k=>$v) {	
					$points[$key][$k] = number_format(($v/60),2,'.','')	;
			}
		}
		
	} else {
		
		foreach( $points as $key=>$val ) {
			foreach( $val as $ke=>$va ) {	
				foreach($va as $k=>$v) {
					$points[$key][$ke][$k] = number_format(($v/60),2,'.','')	;
				}
			}	
		}

	}
	
	return $points;
}



/* 
* Function to conver time from hours to days 
* @Params: array | string 
* @Return:  array of covert points data hours to days
*/

function rns_delay_report_convert_points_data_hours_to_day( $points,$second ) {
	
	if($second=="none") { // if second dimenssion none 
		
		foreach($points as $key=>$val) {
			foreach($points[$key] as $k=>$v) {	
					$points[$key][$k] = number_format(($v/24),2,'.','')	;
			}
		}
		
	} else {
		
		foreach( $points as $key=>$val ) {
			foreach( $val as $ke=>$va ) {	
				foreach($va as $k=>$v) {
					$points[$key][$ke][$k] = number_format(($v/24),2,'.','')	;
				}
			}	
		}

	}
	
	return $points;
}


/*
* Function to retrieve graph for Delay analysis report by points .
* @Params:  points | labels | colors | second | chart type for graph.
* @Return: void .
*/

function rns_get_delay_chart_by_points_label_and_chart_type( $points , $labels , $colors , $second , $chart_type , $time_in ) {
	
	// if points array is not empty
	if( !empty( $points ) ) {
		$i=1;
		foreach( $points as $k => $point ) {
			
			$function  = "chart_".$chart_type;
			
			// check graph is by fisrt reply or for all replies 
			if( ucfirst( $k ) == "First" ) { $rep_label =  __( 'Reply by Agent', 'reports-and-statistics' ) ;   } else { $rep_label =  __('Replies' , 'reports-and-statistics' ) ;  }
			
			chart::$function($point , $second , __( 'Average Time For', 'reports-and-statistics' )." ".ucfirst( $k )." ".$rep_label , "" , $time_in , $i);
			$i++;
		}	
	}		
}


/* 
* Function to get delay time
* @Params: Status | State | Agent |  Start Date | End Date | Taxonomy | Second dimension  | Department |  Custom Fields
* @return array Count for ticket as per passed parameters.
*/

function rns_get_delay_time_by_status_and_assignee( $status = 'queued', $wpasStatus = 'open', $staff = '',  $sDate='', $eDate = '' , $taxonomy_get = '' , $second ,$department , $cus_fields_get , $ticket_author  ) {
	
	// if state is open or closed
	if( $wpasStatus == 'open' || $wpasStatus == 'both' ) {
		$args = array(
			'post_type'       	=> 'ticket',
			'post_status'       => $status ,
			'posts_per_page'    => - 1,
		);
			
	} else  {
		$args = array(
			'post_type'       	=> 'ticket',
			'posts_per_page'    => - 1,
		);
		
	}
	
	// if ticket author value is not empty
	if( isset( $ticket_author ) && !empty( $ticket_author ) ) {
		
		$args['author'] = 	$ticket_author;	
		
	}
	
	// if state is both
	if( $wpasStatus == 'both' ) {
		$wpasStatus_search = 'open,closed' ;
	}else {
		$wpasStatus_search = $wpasStatus ;
	}
	
	$query = rns_get_ticket_by_arguments($status, $wpasStatus_search, $staff,  $sDate, $eDate, $taxonomy_get , $second ,$department , $cus_fields_get,$args);
	
	$reply_data = $data = $datas = array();
	 
	$types = array("first","all");
	$statuses = array("average");
	
	
	foreach( $types as $type ) {
		foreach( $statuses as $status ) {
			$datas[$type][$status] = 0;
		}
	}
	
		//if post count is greater than 0
		if( $query->post_count>0 ) {
		
			foreach( $query->posts as $posts ) {
			 
				$reply_data[] = rn_get_delay_reply_time_by_ticketid( $posts->ID );
			}
		
		
		$data['all_total']  =   $data['agent_total'] = 0; 
		$no_of_reply_records = count( $reply_data );
		
		for( $i=0;$i<$no_of_reply_records; $i++ ) {
	
			$data['alls'][$i]	  = $reply_data[$i]['all'];
			$data['agents'][$i]   = $reply_data[$i]['first'];
			$data['all_total']+=    $reply_data[$i]['all'];	
			$data['agent_total']+=  $reply_data[$i]['first'];
		}
		
		$datas['all']['average'] 	= number_format(( $data['all_total'] / $query->post_count ) , 2 ,'.','');
		$datas['first']['average']  = number_format( ( $data['agent_total'] / $query->post_count ) , 2,'.','' );
		
	} 
	
	return $datas;
}


/* 
* Function to get delay reply time by ticket id
* @Params: Ticket Id
* @return:  array delay reply time
*/

function  rn_get_delay_reply_time_by_ticketid( $ticket_id ) {
	
	
	$wpdb = rns_get_wpdb(); 
	//Query to get reply count as per Ticket id
	
	$count = array( "all" => 0, "first" => 0 );
	
	$query = 'SELECT * FROM '.$wpdb->prefix.'posts p,'.$wpdb->base_prefix.'usermeta u WHERE p.post_author=u.user_id and  p.post_type="%s"  and p.post_parent="%d" and u.meta_key="%s" and u.meta_value not like "%s" order by p.ID asc limit 1 ' ;
	
	$result		= $wpdb->get_results ( $wpdb->prepare( $query , 'ticket_reply'  , $ticket_id, $wpdb->prefix.'capabilities' ,'%wpas_user%' ) );
	
	$all_time = $time = 0; 
	
	// if result id is set and its value is greater than 0
	if( isset( $result[0]->ID ) && $result[0]->ID>0 ) {
		
		$ticket_time = get_the_date( 'Y-m-d H:i:s', $result[0]->post_parent );
		$time =	rns_get_time_difference( $ticket_time, $result[0]->post_date );	
	}

	$count['first'] = $time ;
		
	 $query = 'SELECT * FROM '.$wpdb->prefix.'posts p,'.$wpdb->base_prefix.'usermeta u WHERE p.post_author=u.user_id and  p.post_type="%s"  and p.post_parent="%d" and u.meta_key="%s" ';
	
	$result		= $wpdb->get_results ( $wpdb->prepare( $query , 'ticket_reply' ,  $ticket_id, $wpdb->prefix.'capabilities' ) );
	$time_arr = array();
	
	// if result array  is set and its count is greater than 0
	if( isset( $result ) && count( $result )>0 ) {
		foreach( $result as $key=>$val ) {
			
			$ticket_time = get_the_date( 'Y-m-d H:i:s', $val->post_parent );
			$time_arr[] =	rns_get_time_difference( $ticket_time, $val->post_date );	
				
		}
		
		$all_time = array_sum( $time_arr ) / count( $time_arr );
		
	}

	$count['all'] = $all_time;
		
	return $count;
	
}


/*
* Function to retrieve graph for Distribution analysis report by points .
* @Params:  points | labels | colors | second | chart type | xaxis.
* @Return: void .
*/

function rns_get_distribution_chart_by_points_label_and_chart_type( $points , $labels , $colors , $second , $chart_type , $xaxis ) {
	
	// if points array is not empty
	if( !empty( $points ) ) {
		$i=1;
		foreach( $points as $k => $point ) {
			
			$function  = "chart_".$chart_type;
			chart::$function($point , $second , __( "Distribution Analysis For" , 'reports-and-statistics' ) ." ". ucfirst( $k )." ".__( "Replies" , 'reports-and-statistics' ) , __(  "No. of Replies", 'reports-and-statistics' ) ,__(  "Number Of Tickets" , 'reports-and-statistics' ), $i , $xaxis[$k] );
			$i++;
		}	
	}		
}



/* 
* Function to get graph points by chart type 
* @Params: Second dimension | Status | Search Filter  | Status Get | Agent | Department | Tag | Start Date | End Date | State 		   | Taxonomy | Custom Fields
* @Return: Array of graph points and label.
*/

function rns_get_distribution_reply_according_to_chart_type( $second, $statuses, $search_filter, $status_get, $staff_get,  $sDate_get, $eDate_get, $state_get , $taxonomy_get , $cus_fields_get , $ticket_author ) {
	
	$points = $labels = $colors = $xaxis = array();
	
	$status = implode( "," , array_keys( $statuses ) );
	
	//if second dimenssion is none
	
	if( $second == 'none' ) {
				
		 $reply_counts = rns_get_distribution_count_by_status_and_assignee( $status , $state_get , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
		 		 
		 foreach( $reply_counts['replies'] as  $key=>$counts ) {
	   	 	
			foreach( $counts as $label => $count ) {
				$points[$key][$count] 	=	$reply_counts['tickets'][$key][$label];
				$labels[$key][]	     	= $label;
				$colors[$key][]	     	= rns_get_ticket_color_by_status( $status, $status );
				$xaxis[$key][$label] 	= $reply_counts['tickets'][$key];
			}		
			
		}
	
			
	} elseif( $second == 'assignee' || $second == 'clients' ) { //Following code will work if second dimension is selected as Agent.
			
			if( $second == 'assignee') {	
				$filtered_agent_list = rns_get_filtered_agent_list( $staff_get , "agents"  ) ;
			} else {
				$filtered_agent_list = rns_get_filtered_agent_list( $ticket_author , "authors"  ) ;
			}
			
			
			//Get ticket count by each agent as per filter params
			foreach($filtered_agent_list as $key => $agent) {
			
				if( $second == 'assignee') {	
				
					$reply_counts = rns_get_distribution_count_by_status_and_assignee( $status , $state_get , $key ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
					
				} else {
				
					$reply_counts = rns_get_distribution_count_by_status_and_assignee( $status , $state_get , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $key );
					
				}
				
			 foreach( $reply_counts['replies'] as  $type=>$counts ) {
			    
					foreach( $counts as $label => $count ) {
						
							$points[$type][$key][$count] =	$reply_counts['tickets'][$type][$label];
							$labels[$type][$key][]	     = $label;
							$colors[$type][$key][]	     = rns_get_ticket_color_by_status( $status, $status );
							$xaxis[$type][$key][$count]  = $reply_counts['tickets'][$type][$label];
						
					}
				}
			 
			}
	}
	
	elseif( $second == 'status' ) { //Following code will work if second dimension is selected as status
	
	 	if( $state_get=='open' || $state_get=='both' ) {
			//Get ticket count by each agent as per filter params
			foreach($statuses as $key => $valu) {
												
				$reply_counts = rns_get_distribution_count_by_status_and_assignee( $key , $state_get , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
				
				 foreach( $reply_counts['replies'] as  $type=>$counts ) {
					 
						foreach( $counts as $label => $count ) {
							
								$points[$type][$valu][$count] =	$reply_counts['tickets'][$type][$label];
								$labels[$type][$valu][]	     = $label;
								$colors[$type][$valu][]	     = rns_get_ticket_color_by_status( $status, $status );
								$xaxis[$type][$valu][$label] = $reply_counts['tickets'][$type][$label];
							}
						
					  }
			}
		}
		
		
		// if  state is closed ot both
		if( $state_get=='closed' || $state_get=='both'  ) {
			$valu =  'closed';
			$reply_counts = rns_get_distribution_count_by_status_and_assignee( $valu , 'closed' , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
			 foreach( $reply_counts['replies'] as  $type=>$counts ) {
				
				foreach( $counts as $label => $count ) {
					
						$points[$type][$valu][$count] =	$reply_counts['tickets'][$type][$label];
						$labels[$type][$valu][]	     = $label;
						$colors[$type][$valu][]	     = rns_get_ticket_color_by_status( $status, $status );
						$xaxis[$type][$valu][$label]       = $reply_counts['tickets'][$type][$label];
					
				}	
				
			 }
		}
		
	}
	
	elseif ( $second != '' ) { //Following code will work if second dimension is not empty
		
		if(taxonomy_exists( $second )) { // if second dimession in taxonomy
			$filtered_dept_list = rns_get_filtered_dept_list( $second , $taxonomy_get ) ;
		} else {	
			$filtered_dept_list = rns_get_custom_field_filter_list( $second , $cus_fields_get ) ;
		}
		
		foreach($filtered_dept_list as $key => $department) {
						
				$key_label = taxonomy_exists( $second ) ? $key : $department;				
		$reply_counts = rns_get_distribution_count_by_status_and_assignee( $status , $state_get , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , $key , $cus_fields_get , $ticket_author );
			
			foreach( $reply_counts['replies'] as  $type=>$counts ) {	
			    
				foreach( $counts as $label => $count ) { 
					
						$points[$type][$key_label][$count] =	$reply_counts['tickets'][$type][$label];
						$labels[$type][$key_label][]	     = $label;
						$colors[$type][$key_label][]	     = rns_get_ticket_color_by_status( $status, $status );
						$xaxis[$type][$key_label][$label]       = $reply_counts['tickets'][$type][$label];
					
				}
			}
			
		}
		
	}
	
	// if second dimenssion is not none 
	if($second != 'none' ) {
		$keys = array();	
			foreach( $points as $k => $point ) {
				foreach($point as $key=>$label) {
					foreach($label as $l=>$val) {	
						$keys[$l] =  $l;
					}
				}				
			}
			
			foreach( $points as $k => $point ) {
				foreach($point as $key=>$label) {			
					foreach($label as $l=>$val) {
						ksort($points[$k][$key]);
						foreach($keys as $in=>$vl ) {
							if(isset($points[$k][$key][$in])) {
								$points[$k][$key][$l] = $val;
							} else {
								$points[$k][$key][$in] = 0;
							}
						}
					}
					
				}				
			}
		}
	
	$data = array(
					"points"	=> $points,
					"labels"	=> $labels,
					"colors"	=> $colors,
					"xaxis"     => $xaxis 
			);
		
	return($data);
	
}


/* 
* Function to get Ticket count
* @Params: Status | State | Agent |  Start Date | End Date | Taxonomy | Second dimension | Department |  Custom Fields
* @return array Count for ticket as per passed parameters.
*/

function rns_get_distribution_count_by_status_and_assignee( $status = 'queued', $wpasStatus = 'open', $staff = '',  $sDate='', $eDate = '' , $taxonomy_get = '' , $second ,$department , $cus_fields_get , $ticket_author  ) {
	
	
	// if state is open or both
	if( $wpasStatus == 'open' || $wpasStatus == 'both' ) {
		$args = array(
			'post_type'       	=> 'ticket',
			'post_status'       => $status ,
			'posts_per_page'    => - 1,
		);
			
	} else  {
		$args = array(
			'post_type'       	=> 'ticket',
			'posts_per_page'    => - 1,
		);
		
	}
	
	// if ticket author value is not empty
	if( isset( $ticket_author ) && !empty( $ticket_author ) ) {
		
		$args['author'] = 	$ticket_author;	
		
	}
	
	// if state is  both
	if( $wpasStatus == 'both' ) {
		$wpasStatus_search = 'open,closed' ;
	}else {
		$wpasStatus_search = $wpasStatus ;
	}
	
	$query = rns_get_ticket_by_arguments($status, $wpasStatus_search, $staff,  $sDate, $eDate, $taxonomy_get , $second ,$department , $cus_fields_get,$args);
	

	
	$datas = array();
	$data = array();
	$tickets = array();
	
	$datas['tickets']['all'][0]    = 0;
	$datas['tickets']['client'][0] = 0;
	$datas['tickets']['agent'][0]  = 0;
	
	$datas['replies']['all'][0]	   = 0;
	$datas['replies']['client'][0] = 0;
	$datas['replies']['agent'][0]  = 0;
	

	
	// if post count is greater than 0
	if( $query->post_count>0 ) {
		
			foreach( $query->posts as $posts ) {
			 
				$tickets[] = $posts->ID ;
				
			}
			
			$total_tickets = count($tickets);
			
			$tickets_id =  implode(",",$tickets);
			
			//get reply count data
			$reply_data = rn_get_ticket_and_reply_count($tickets_id);
				
			$no_of_records = count($reply_data['all']);
			
			$data['all_total_tickets']  =  $data['agent_total_ticket'] = $data['client_total_ticket'] = 0; 
			
			for( $i=0; $i<$no_of_records; $i++) {
			
			 	//if the tickets exist with replies for all users
				if(isset($reply_data['all'][$i]->tickets) && $reply_data['all'][$i]->tickets>0) {					
			
					$datas['tickets']['all'][$i+1] 	= (isset($reply_data['all'][$i]->tickets)?$reply_data['all'][$i]->tickets:'0') ;	
					$data['all_total_tickets']+=$datas['tickets']['all'][$i+1];
					$datas['replies']['all'][$i+1] 	= isset($reply_data['all'][$i]->reply_count)?$reply_data['all'][$i]->reply_count:'0' ;
					
				}
				
				//if the tickets exist with replies for client
				if(isset($reply_data['client'][$i]->tickets) && $reply_data['client'][$i]->tickets>0) {
				
					$datas['tickets']['client'][$i+1] = (isset($reply_data['client'][$i]->tickets)?$reply_data['client'][$i]->tickets:'0');
					$data['client_total_ticket']+=$datas['tickets']['client'][$i+1];
					$datas['replies']['client'][$i+1] = isset($reply_data['client'][$i]->reply_count)?$reply_data['client'][$i]->reply_count:'0';
					
				}
				
				//if the tickets exist with replies for agent
				if(isset($reply_data['agent'][$i]->tickets) && $reply_data['agent'][$i]->tickets>0) {
				
				$datas['tickets']['agent'][$i+1]  = (isset( $reply_data['agent'][$i]->tickets )?$reply_data['agent'][$i]->tickets:'0'); 
				$data['agent_total_ticket']+=$datas['tickets']['agent'][$i+1];
				$datas['replies']['agent'][$i+1]  = isset($reply_data['agent'][$i]->reply_count)?$reply_data['agent'][$i]->reply_count:'0';
				
				}
			
			
			}
			
			$datas['tickets']['all'][0]    = $total_tickets - $data['all_total_tickets'] ;
			$datas['tickets']['client'][0] = $total_tickets - $data['client_total_ticket'] ;
			$datas['tickets']['agent'][0]  = $total_tickets - $data['agent_total_ticket'];
			
			$datas['replies']['all'][0]="0";
			$datas['replies']['client'][0]="0";
			$datas['replies']['agent'][0]="0";
	} 

	return $datas;
}

/* 
* Function to get replycount by ticket id
* @Params: Ticket Id
* @return:  array reply count by ticket id
*/

function  rn_get_ticket_and_reply_count( $ticket_id ) {
	

	$wpdb = rns_get_wpdb(); 
	//Query to get reply count as per Ticket id
	
	$count = array( "all" => 0, "client" => 0, "agent" => 0 );
	
	 $query = "select count(*) as tickets,reply_count from  ( SELECT post_parent,count(*) as reply_count FROM ".$wpdb->prefix."posts p,".$wpdb->base_prefix."usermeta u WHERE p.post_author=u.user_id and  p.post_type='%s'  and p.post_parent in (".$ticket_id.") and u.meta_key='%s' group by p.post_parent ) x group by reply_count";
	
	$result		= $wpdb->get_results ( $wpdb->prepare( $query , 'ticket_reply' ,   $wpdb->prefix.'capabilities' ) );	
	
	$count['all'] = $result;
	
	
	
	$query = 'select count(*) as tickets,reply_count from  (SELECT post_parent,count(*) as reply_count FROM '.$wpdb->prefix.'posts p,'.$wpdb->base_prefix.'usermeta u WHERE p.post_author=u.user_id and  p.post_type="%s"  and p.post_parent in ('.$ticket_id.') and u.meta_key="%s" and u.meta_value like "%s" group by p.post_parent ) x group by reply_count ' ;

	$result		= $wpdb->get_results ( $wpdb->prepare( $query , 'ticket_reply' ,  $wpdb->prefix.'capabilities' ,'%wpas_user%' ) );
	
	$count['client'] =  $result ;
	
	$query = 'select count(*) as tickets,reply_count from  (SELECT post_parent,count(*) as reply_count FROM '.$wpdb->prefix.'posts p,'.$wpdb->base_prefix.'usermeta u WHERE p.post_author=u.user_id and  p.post_type="%s"  and p.post_parent in ('.$ticket_id.') and u.meta_key="%s" and u.meta_value not like "%s" group by p.post_parent ) x group by reply_count ' ;
	
	$result		= $wpdb->get_results ( $wpdb->prepare( $query , 'ticket_reply'  ,  $wpdb->prefix.'capabilities' ,'%wpas_user%' ) );
	
	$count['agent'] =  $result ;
		
	return $count;
	
}


/* 
* Function to get graph points by chart type 
* @Params: Second dimension | Status | Search Filter | Status Get | Agent | Start Date | End Date | State | Taxonomy 
			| Custom Fields
* @Return: Array of graph points and label.
*/

function rns_get_trend_tickets_according_to_chart_type( $second, $statuses, $search_filter, $status_get, $staff_get,  $sDate_get, $eDate_get, $state_get , $taxonomy_get , $cus_fields_get , $ticket_author ) {
		
	$department = $points = $labels = $colors = array();
	
	$status = implode( "," , array_keys( $statuses ) );
	
	
	if( $state_get == "open" ) { // if state is open
		
		$stateData = array("open"=>"open");
		
	} else if( $state_get == "closed" ) { // if state is closed
		
		$stateData = array("closed"=>"closed");
		
	}else {
		
		$stateData = array("open"=>"open","closed"=>"closed");
	}
	
	// if second dimenssion none
	if( $second == 'none' ) {
				
		
		 foreach($stateData as $skey=>$sval) {
			  $reply_counts = rns_get_trend_count_by_status_and_assignee( $status , $skey , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
		 foreach( $reply_counts as  $key=>$counts ) {
	   	 
			foreach( $counts as $label => $count ) {
				$points[$key][$label][$skey] =	$count;
				$labels[$key][$skey][$label] = $count;
				$colors[$key][]	     = rns_get_ticket_color_by_status( $status, $status );
			}		
		}
		 }
	
			
	} elseif( $second == 'assignee' || $second == 'clients' ) { //Following code will work if second dimension is selected as Agent or client.
	
			if( $second == 'assignee') {	
				$filtered_agent_list = rns_get_filtered_agent_list( $staff_get , "agents"  ) ;
			} else {
				$filtered_agent_list = rns_get_filtered_agent_list( $ticket_author , "authors"  ) ;
			}
	
			//Get ticket count by each agent as per filter params
			
			foreach($stateData as $skey=>$sval) {
			foreach($filtered_agent_list as $key => $agent) {
				
				if( $second == 'assignee') {	
				
					$reply_counts = rns_get_trend_count_by_status_and_assignee( $status , $skey , $key ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
					
				} else  {
					
					$reply_counts = rns_get_trend_count_by_status_and_assignee( $status , $skey , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $key );
					
				}
			 foreach($reply_counts as  $type=>$counts ) {
				foreach( $counts as $label => $count ) {
					$points[$type][$key][$label][$skey] = $count;
					$labels[$type][$skey][$key][$label] = $count;
					$colors[$skey][$type][$key][]	     = rns_get_ticket_color_by_status( $status, $status );
				}
			 }
			}	
			}
			
	}
	
	elseif( $second == 'status' ) { //Following code will work if second dimension is selected as Agent.
	
	 	
			//Get ticket count by each agent as per filter params
			foreach($stateData as $skey=>$sval) {
			foreach($statuses as $key => $valu) {
												
				$reply_counts = rns_get_trend_count_by_status_and_assignee( $key , $skey , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , '' , $cus_fields_get , $ticket_author );
			 foreach($reply_counts as  $type=>$counts ) {
				foreach( $counts as $label => $count ) {
					$points[$type][$valu][$label][$skey] =	$count;
					$labels[$type][$skey][$valu][$label] = $count;
					$colors[$skey][$type][$valu][]	     = rns_get_ticket_color_by_status( $status, $status );
				}	
			 }
			}
			}
	}
	
	elseif ( $second != ''  ) { //Following code will work if second dimension is not empty.
		
		if(taxonomy_exists( $second )) { // if second dimession is taxonomy
			
			$filtered_dept_list = rns_get_filtered_dept_list( $second , $taxonomy_get ) ;
			
		} else {	
		
			$filtered_dept_list = rns_get_custom_field_filter_list( $second , $cus_fields_get ) ;
			
		}
		
		foreach($stateData as $skey=>$sval) {
		foreach($filtered_dept_list as $key => $department) {
				$key_label = taxonomy_exists( $second ) ? $key : $department;		
							
		$reply_counts = rns_get_trend_count_by_status_and_assignee( $status , $skey , $staff_get ,   $sDate_get, $eDate_get ,  $taxonomy_get , $second , $key , $cus_fields_get , $ticket_author );
			foreach($reply_counts as  $type=>$counts ) {	
				foreach( $counts as $label => $count ) { 
				$points[$type][$key_label][$label][$skey]   = $count;
				$labels[$type][$skey][$key_label][$label]   = $count;
				$colors[$type][$key_label][]	     = rns_get_ticket_color_by_status( $status, $status );
			}
			}
		}
		}
				
	}
	
	
	$data = array(
					"points"	=> $points,
					"labels"	=> $labels,
					"colors"	=> $colors
			);
		
	return($data);
	
}


/*
* Function to retrieve graph for Trend analysis report by points .
* @Params:  points | labels | colors | second | chart type for graph.
* @Return: void .
*/

function rns_get_trend_chart_by_points_label_and_chart_type( $points , $labels , $colors , $second , $chart_type ) {
	
	// if points array not empty
	if( !empty( $points ) ) {
		$i=1;
		foreach( $points as $k => $point ) {
			
		    $function  = "chart_".$chart_type; 
			charts::$function($point , $second , __( "The number of tickets opened last n" , 'reports-and-statistics') . "(".ucfirst($k)." )" , ucfirst($k) ,__( "Number of Tickets", 'reports-and-statistics'), $i);
			$i++;
		}	
	}		
}



/* 
* Function to get Ticket counts
* @Params: Statu | State | Agent |  Start Date | End Date | Taxonomy | Second dimension |  department | Custom Fields
* @return array Count for ticket as per passed parameters.
*/

function rns_get_trend_count_by_status_and_assignee( $status = 'queued', $wpasStatus = 'open', $staff = '',  $sDate='', $eDate = '' , $taxonomy_get = '' , $second ,$department , $cus_fields_get , $ticket_author  ) {
	
	
	
	// if state is open or closed
	if( $wpasStatus == 'open' || $wpasStatus == 'both' ) {
		$args = array(
			'post_type'       	=> 'ticket',
			'post_status'       => $status ,
			'posts_per_page'    => - 1,
		);
			
	} else  {
		$args = array(
			'post_type'       	=> 'ticket',
			'posts_per_page'    => - 1,
		);
		
	}
	
	// if ticket author value is not empty
	if( isset( $ticket_author ) && !empty( $ticket_author ) ) {
		
		$args['author'] = 	$ticket_author;	
		
	}
	
	//if state is both
	if( $wpasStatus == 'both' ) {
		$wpasStatus_search = 'open,closed' ;
	}else {
		$wpasStatus_search = $wpasStatus ;
	}
	
	$query = rns_get_ticket_by_arguments($status, $wpasStatus_search, $staff,  $sDate, $eDate, $taxonomy_get , $second ,$department , $cus_fields_get,$args);

	$tickets = array();
	$datas = array();
	
		//if post count is greater than 0
		if( $query->post_count>0 ) {
			foreach( $query->posts as $posts ) {
			 
				$tickets[] = $posts->ID ;	
			}
			
			$tickets_id =  implode(",",$tickets);
			$datas = rn_get_trend_open_and_closed_count( $tickets_id , $wpasStatus_search );
		}	 
	
	return $datas;
}


/* 
* Function to get custon field  type
* @Params: $ticket_id | $state
* @return  array trend tickets open closed data
*/

function  rn_get_trend_open_and_closed_count( $ticket_id , $state ) {
	

	$wpdb = rns_get_wpdb(); 
	//Query to get reply count as per Ticket id
	$count = array();
	$types = array( "days", "weeks" ,"month");
	$statuses = array(0,1,2,3,4,5,6,7);
	
	foreach( $types as $type ) {
		foreach( $statuses as $status ) {
			$count[$type][$status] = 0;
		}
	}
	
	// if state is open 
	if($state == 'open') {
	
		$query = "SELECT count(id) as counts, TIMESTAMPDIFF(DAY, post_date, CURDATE()) as days FROM ".$wpdb->prefix."posts p where ID in (".$ticket_id.") group by days having days<=7 ";
		
		// get count arrays of open tickets for days
		$count = rns_get_array_of_tickets_by_result_data( $query , "days" , $count  ) ;
	
		$query = "SELECT count(id) as counts, floor(TIMESTAMPDIFF(DAY, post_date, CURDATE())/7) as days FROM ".$wpdb->prefix."posts p where ID in (".$ticket_id.") group by days having days<=7 ";
		
		// get count arrays of open tickets for weeks
		$count = rns_get_array_of_tickets_by_result_data( $query , "weeks" , $count  ) ;
			
		$query = "SELECT count(id) as counts, TIMESTAMPDIFF(MONTH, post_date, CURDATE()) as days FROM ".$wpdb->prefix."posts p where ID in (".$ticket_id.") group by days having days<=7 ";
		
		// get count arrays of open tickets for months
		$count = rns_get_array_of_tickets_by_result_data( $query , "month" , $count  ) ;
		
	} else  {
		
		    // Count closed tickets by days
		
			$query = "SELECT count(p.id) as counts, TIMESTAMPDIFF(DAY, pm.meta_value, CURDATE()) as days FROM ".$wpdb->prefix."posts p , ".$wpdb->prefix."postmeta pm  where p.ID=pm.post_id and  p.ID in (".$ticket_id.") and pm.meta_key='_ticket_closed_on'  group by days having days<=7 ";
	
			$result	= $wpdb->get_results (  $query  );	
			
			// if result data is not set or result array count equal to 0		
			if( !isset( $result ) || count( $result ) == 0  )	 {
				
				 $query = "select count(id) as counts,TIMESTAMPDIFF(DAY, post_date, CURDATE()) as days from ( select *  from (SELECT * FROM ".$wpdb->prefix."posts  where post_parent in (".$ticket_id.") and post_type='ticket_reply' order by post_date desc) x group by post_parent ) y group by days having days<=7  ";
				 
				 $result	= $wpdb->get_results (  $query  );	
				 
				 // if result data is not set or result array count equal to 0		
				 if( !isset( $result ) || count( $result ) == 0  )	 {
					 					
					  $query = "select count(id) as counts,TIMESTAMPDIFF(DAY, post_date, CURDATE()) as days  from ".$wpdb->prefix."posts  where ID in (".$ticket_id.") group by days having days<=7  ";
					  
					  
				 }
			}
			
			// get count arrays of closed tickets for days
			$count = rns_get_array_of_tickets_by_result_data( $query , "days" , $count  ) ;
			
		// Count closed tickets by weeks	
	
		$query = "SELECT count(id) as counts, floor(TIMESTAMPDIFF(DAY, pm.meta_value, CURDATE())/7) as days FROM ".$wpdb->prefix."posts p , ".$wpdb->prefix."postmeta pm  where p.ID=pm.post_id and  p.ID in (".$ticket_id.") and pm.meta_key='_ticket_closed_on' group by days having days<=7 ";
	
			$result	= $wpdb->get_results (  $query  );	
			
			// if result data is not set or result array count equal to 0		
			if( !isset( $result ) || count( $result ) == 0  )	 {
				
				 $query = "SELECT count(id) as counts, floor(TIMESTAMPDIFF(DAY, post_date, CURDATE())/7) as days from ( select *  from (SELECT * FROM ".$wpdb->prefix."posts  where post_parent in (".$ticket_id.") and post_type='ticket_reply' order by post_date desc) x group by post_parent ) y group by days having days<=7  ";
				 
				 $result	= $wpdb->get_results (  $query  );	
				 
				 // if result data is not set or result array count equal to 0		
				 if( !isset( $result ) || count( $result ) == 0  )	 {
					 					
					  $query = "SELECT count(id) as counts, floor(TIMESTAMPDIFF(DAY, post_date, CURDATE())/7) as days  from ".$wpdb->prefix."posts  where ID in (".$ticket_id.") group by days having days<=7  ";
					  
					  
				 }
			}
			
			// get count arrays for weeks
			$count = rns_get_array_of_tickets_by_result_data( $query , "weeks" , $count  ) ;
		
					
		// Count closed tickets by month
		
	
			$query = "SELECT count(id) as counts, TIMESTAMPDIFF(MONTH, pm.meta_value, CURDATE()) as days FROM ".$wpdb->prefix."posts p , ".$wpdb->prefix."postmeta pm  where p.ID=pm.post_id and  p.ID in (".$ticket_id.") and pm.meta_key='_ticket_closed_on' group by days having days<=7 ";
	
			$result	= $wpdb->get_results (  $query  );	
			
			// if result data is not set or result array count equal to 0		
			if( !isset( $result ) || count( $result ) == 0  )	 {
				
				 $query = "SELECT count(id) as counts, TIMESTAMPDIFF(MONTH, post_date, CURDATE()) as days  from ( select *  from (SELECT * FROM ".$wpdb->prefix."posts  where post_parent in (".$ticket_id.") and post_type='ticket_reply' order by post_date desc) x group by post_parent ) y group by days having days<=7  ";
				 
				 $result	= $wpdb->get_results (  $query  );	
				 
				 // if result data is not set or result array count equal to 0		
				 if( !isset( $result ) || count( $result ) == 0  )	 {
					 					
					  $query = "SELECT count(id) as counts, TIMESTAMPDIFF(MONTH, post_date, CURDATE()) as days   from ".$wpdb->prefix."posts  where ID in (".$ticket_id.") group by days having days<=7  ";
					  
					 
				 }
			}
		
		// get count arrays for months
		$count = rns_get_array_of_tickets_by_result_data( $query , "month" , $count  ) ;
	
	}
	
	
	return $count;
	
}


/* 
* Function to get result array by query , array type
* @Params: $query | $type | $count
* @return array tickets numbers by day,months and weeks
*/

function rns_get_array_of_tickets_by_result_data( $query , $type , $count ) {
	
	$wpdb = rns_get_wpdb(); 
	
	$result	= $wpdb->get_results (  $query  );	
	
	// if result data is set and have alteast one value	
	if( isset( $result ) && count( $result ) >0  )	 {
		foreach( $result as $key => $val ) {	
			$count[$type][$val->days] = $val->counts;	
		}
	}
	
	return $count ;
}


/* 
* Function to get chart description by report 
* @Params: $report
* @return string description of charts 
*/

function rns_get_description_of_chart_by_report( $report ) {
	
	$description = '' ;
	
	/* Description for Basic Report */
	if( $report == "basic_report" ) {
		
		$description.= "<h2>" . __('Ticket Count Charts and Tables' , 'reports-and-statistics' ) . "</h2>"
		
					  . "<p>" . __('This report show the number of tickets for each status. By default it shows open ticket counts but you can view closed tickets or both open and closed by adjusting the filter in the filter side-bar.' , 'reports-and-statistics' ) . "</p>"		
		
					  . "<p>" . __('You can further break down the data in this report by choosing a 2nd dimension from the filters side-bar.' , 'reports-and-statistics' ) ."</p>" 

					  . "<p>" . __('For example, if you choose DEPARTMENT as the 2nd dimension you can see the number of tickets associated with each department.' , 'reports-and-statistics' ) ."</p>" 		
		
					  . "<p>" . __('If you choose a second dimension the best chart type to use is the Stacked Column Chart or Stacked Bar Chart.' , 'reports-and-statistics' ) ."</p>" ;  
		
	}
	
	/* Description for Productivity Report */
	if( $report == "reply_report" ) {
		
		$description.= "<h2>" . __('Productivity Analysis Charts and Tables' , 'reports-and-statistics' ) . "</h2>"
		
					  . "<p>" . __('This report consists of three sub-charts.' , 'reports-and-statistics' ) . "</p>"
		
					  . "<p>" . __('The first chart and associated table show the average, median and maximum number of ALL replies (count of replies) in a set of tickets.  Control which tickets are included in this analysis by using the filters on the left side of the screen.' , 'reports-and-statistics' ) . "</p>"  
		
					  . "<p>" . __('You can further break down the data in this report by choosing a 2nd dimension from the filters side-bar.' , 'reports-and-statistics' ) ."</p>" 
		
					  . "<p>" . __('For example, if you choose ASSIGNEE as the 2nd dimension you can see the average/median/maximum number of replies for each assignee (agent).' , 'reports-and-statistics' ) ."</p>" 
		
					  . "<p>" . __('If you choose a second dimension the best chart type to use is the Column Chart.' , 'reports-and-statistics' ) ."</p>"   
		
					  . "<p>" .__('The second chart and associated table shows the average/median/maximum number of replies (count of replies) from just customers/clients.' , 'reports-and-statistics' )."</p>"
		
					  . "<p>" .__('The third chart and associated table shows the average/median/maximum number of replies (count of replies) from just agents (assignees).' , 'reports-and-statistics' )."</p>";

	}
	
	/* Description for Resolution Report */
	if( $report == "resolution_report" ) {
		
		$description.= "<h2>" . __('Resolution Analysis Report' , 'reports-and-statistics' ) . "</h2>"
		
					   . "<p>" . __('This simple report show the average time it takes to close a ticket. Depending on the length of time you might be shown the data in minutes, hours or days.' , 'reports-and-statistics' ) . "</p>"
		
					   . "<p>" . __('While it might looks simple power of this report lies in the use of the 2nd dimension. You can further break down the data in this report by choosing a 2nd dimension from the filters side-bar.' , 'reports-and-statistics' ) . "</p>"
					   . "<p>" . __('For example, if you choose ASSIGNEE as the 2nd dimension you can quickly see which agents are taking the longest times to close out tickets. Or, if you choose PRODUCT or DEPARTMENT as the 2nd dimension you can get an idea of which products or departments absorb most of your agents time.' , 'reports-and-statistics' ) ."</p>" 

					   . "<p>" . __('If you choose a second dimension the best chart type to use is the Column Chart or the Stacked Bar Chart.' , 'reports-and-statistics' ) ."</p>" ; 
					   
	}
	
	/* Description for Delay Report */
	if( $report == "delay_report" ) {
		
		$description.= "<h2>" . __('Delay Analysis Charts and Tables' , 'reports-and-statistics' ) . "</h2>"
		
					  . "<p>" . __('This report consists of two sub-charts.' , 'reports-and-statistics' ) . "</p>"
		
					  .	"<p>". __('The first chart show the average time it takes for a ticket to get its first reply by an agent. Depending on the length of time you might be shown the data in minutes, hours or days.' , 'reports-and-statistics' )."</p>"  
		
					  . "<p>" . __('As with other reports you can further break down the data by choosing a 2nd dimension from the filters side-bar.' , 'reports-and-statistics' ) ."</p>" 
		
					  . "<p>" . __('If you choose a second dimension the best chart type to use is the Column Chart.' , 'reports-and-statistics' ) ."</p>" ; 		
	
		
	}
	
	/* Description for Distribution Report */
	if( $report == "distribution_report" ) {
		
		$description.= 	"<h2>" . __('Distribution Analysis Charts and Tables' , 'reports-and-statistics' ) . "</h2>"
		
					    . "<p>" . __('This report consists of three sub-charts showing similar information.' , 'reports-and-statistics' ) . "</p>"
		
						. "<p>".__('The first chart show the number of TICKETS containing a certain number of replies.  So, the first bar on the chart will show the number of tickets containing just one reply. The second bar will show the number of tickets containing two replies and so on.' , 'reports-and-statistics' )."</p>"  

						. "<p>" . __('As with other reports you can further break down the data by choosing a 2nd dimension from the filters side-bar.' , 'reports-and-statistics' ) ."</p>" 
		
						. "<p>" . __('If you choose a second dimension the best chart type to use is the Stacked Bar Chart or Stacked Column Chart.' , 'reports-and-statistics' ) ."</p>" 
		
						. "<p>" . __('The second chart shows the same data but only takes replies from CLIENTS/USERS into consideration. The third chart only includes counts of replies by agents.' , 'reports-and-statistics' ) ."</p>" ;		
		
	}
	
	/* Description for Trend Report */
	if( $report == "trend_report" ) {
		
		$description.=   "<h2>" . __('Trend Analysis Charts and Tables' , 'reports-and-statistics' ) . "</h2>"
		
						. "<p>" . __('This report consists of three sub-charts showing similar information.' , 'reports-and-statistics' ) . "</p>"
		
						. "<p>".__('The first chart show the number of TICKETS opened each day for the last 8 days.  Day 0 is TODAY.  The second chart shows the number of TICKETS opened each week for the last 8 weeks and the third chart shows the number of tickets opened each month for the last 8 months.' , 'reports-and-statistics' )."</p>"
		
						. "<p>" . __('As with other reports you can further break down the data by choosing a 2nd dimension from the filters side-bar.' , 'reports-and-statistics' ) ."</p>" 
						
						. "<p>" . __('For example, is you choose ASSIGNEE as the 2nd dimension you can see how many tickets are being assigned to each agent over each day/week/month. If you run multiple products or departments you can get an idea of how the tickets are being distributed among those dimensions.' , 'reports-and-statistics' ) ."</p>" 
		
						. "<p>" . __('If you choose a second dimension the best chart type to use is the Stacked Bar Chart or Stacked Column Chart.' , 'reports-and-statistics' ) ."</p>" ;
		
	}
	
	/* Description for all reports */
	$description.= "<h2>" . __('Filters, Settings and 2nd Dimennsions' , 'reports-and-statistics' ) . "</h2>";

	$description.= "<p>" . __('All reports default to showing data for open tickets. However you can restrict the tickets by using the filter side-bar.  To access this bar use the TOGGLE FILTERS link on the upper left of the screen - under the REPORTS title. Click the APPLY FILTERS button to refresh the charts and tables with your selection.' , 'reports-and-statistics' ) ."</p>" ;	
	$description.= "<p>" . __('You can select which custom fields show up in the filter-bar under the TICKETS->REPORT SETTINGS menu option.' , 'reports-and-statistics' ) ."</p>" ;
	
	$description.= "<p>" . __('The SECOND DIMENSION sub-panel in the filter side-bar allow you to break down basic reports into additional "buckets".' , 'reports-and-statistics' ) ."</p>" ;	
	
	$description.= "<h2>" . __('General Guidelines' , 'reports-and-statistics' ) . "</h2>";
	$description.= "<p>" . __('The interval value field allows you to control the spacing of the grid lines.  Sometimes there are too many grid lines (or too few) which can make the chart look awkward. Change the value and click the APPLY FILTERS button to update the charts.' , 'reports-and-statistics' ) . "</p>";
	
	$description.= "<h2>" . __('About Time Based Charts' , 'reports-and-statistics' ) . "</h2>";
	$description.= "<p>" . __('For those charts that show data in minutes/hours/days, you can control when the conversion occurs from minutes to hours or hours to days in the REPORT SETTINGS screen. Just change the field labeled SET MINUTES LIMIT' , 'reports-and-statistics' ) . "</p>";
	
	
	return ( $description ) ;
}


/*
* Function to get wpdb object.
* @return object of wpdb class
*/

function rns_get_wpdb() {
	global $wpdb;
	
	return 	$wpdb;
}


/*
* Function to get columns of table from points array.
* @return void
*/

function rns_generate_row_heading_from_points_array( $point , $statuses , $searchStatus ) {
	
	foreach ( $point as $key => $label ) {
		
		if ( !empty( $searchStatus ) ) {
			$value = array_search( $key ,$statuses );
			if ( in_array( $value , $searchStatus ) ) {
				echo ' <th> '. $key .' </th> ';
			}
		} else {
			echo ' <th> '. $key  .' </th> ';
		}
	}
		
}

/*
* Function to get rows data of table from points array.
* @return void
*/

function rns_generate_rows_data_from_points_array( $point ) {
	
	 if(!empty($point)){
		echo ' <tr > <td align="center"> <strong>  '.  __( 'Tickets', 'reports-and-statistics' ) .'  </strong> </td> ';
		$total = 0;
		foreach ( $point as $key => $val ) {
			
			$total+=$val;
			echo ' <td align="center"> ' . $val . ' </td>';
			
		}
		
		echo ' </tr> ';
	}	
	
}

/*
* Function to get columns of table from points array.
* @return void
*/

function  rns_add_or_update_custom_option_from_settings( $option_name , $option_data) {
	 
	if( isset( $option_data ) && !empty( $option_data ) ) { // if option data array is not empty
	
				if( ! is_array( $option_data ) ) {
					$option_data = unserialize($option_data);			
				} 
				
				$roles	     =	implode( ',', $option_data );

				//if option exist in the database
				rns_add_or_update_option_value( $option_name , $roles );
				
			} else {
				update_option( $option_name , '');
	}	
	
}


/*
* Function to get list of saved report .
* @Params: $user_id | $role
* @return  array of saved reports  
*/

function rns_get_save_report_list( $user_id='' , $role ='' ) {
	
	
	
	$args = array(
		
    	'post_type'=> 'rns_report',
   		'order'    => 'ASC',
		'orderby' => 'meta_value',
		'meta_key' => '_rns_sort_order',
		'posts_per_page' => -1
    );              
	
	if( !empty( $user_id ) && empty( $role )  ) { // if user id is not empty but role is empty
			$args['author']  =  $user_id; 			
	}
	
	

	
	if( !empty( $role ) ) {  // if role value is not empty
		
		if( $role == "administrator" ) {	// if role value is administrator
		
			$args['meta_query'] = array(
								'relation' => 'and',
							    array(
								   'key'     => '_rns_author_id',
								   'value'   => $user_id,
								   'compare' => '!=',
							   )
							   
						   );	
		} else {
			
			$args['meta_query'] = array(
								'relation' => 'and',
							   array(
								   'key'     => '_rns_assign_roles',
								   'value'   => $role,
								   'compare' => 'like',
							   ),
							   array(
								   'key'     => '_rns_author_id',
								   'value'   => $user_id,
								   'compare' => '!=',
							   )
							   
						   );
			
			
		}
	}
	
	
	$query = new WP_Query( $args );	

	
	return $query->posts; 
}

/*
* Function to get current user role  .
* @return  string role name  
*/

function rns_get_current_user_role() {
	
	$user = wp_get_current_user();
	return $user->roles[0];
	
}

/*
* Function to check role permission on save and delete report  .
* @return  boolean
*/
function rns_check_role_save_delete_permission( $option_name ) {
	
	$role = rns_get_current_user_role();
	$save_Roles	= get_option( $option_name , true );
	$save_role = explode( ",", $save_Roles );	
	
	return ( in_array( $role , $save_role ) || $role=='administrator' || is_super_admin() );
	
}


/*
* Function to match report author id with current user id  .
* @return  boolean
*/

function rns_check_save_report_author_id( $post_id ) {
	
	$author_id = get_post_field ( "post_author", $post_id );
	
	// if user logged in id equal to author id
	return(  get_current_user_id() == $author_id || rns_get_current_user_role()=="administrator" ) ;
		 
}



/*
* Function to match report author id with current user id  .
* @return  void
*/
 
function rns_list_report_from_array( $reports  ) {

	$str = " ";	
	 
	foreach($reports as $save_key => $save_val ) {
	
		$str.= '<li>'
			. '<label>'
			. '<a class="has-tooltip" aria-describedby="rns_tt_'.$save_val->ID.'"  href="'. $save_val->_rns_report_link.'&report_id='.$save_val->ID.'" target="_blank">'. $save_val->post_title .'</a>'
			. '</label>'			
			. '<div class="rns-saved-reports-small-text">' 
			. $save_val->post_content 
			. ' <br />' 
			. __('By: ', 'reports-and-statistics') 
			. get_user_option('display_name', $save_val->post_author) 
			. '	</div> 
		</li>';
	
	}
		
	echo $str;		
		
}



/*
* Function to add or update option by option name and option value .
* @return  void
*/
function rns_add_or_update_option_value( $option_name , $option_value ) {
	
	if(  !empty( $option_value )  && $option_value !=="0" ) {
	
		// if option already exist
		if( get_option( $option_name ) !== false  ){
	
			update_option( $option_name , $option_value );
						
		} else {
			
			add_option( $option_name , $option_value );
		
		}
	
	}
	
}


/*
* Function list roles checkbox for save,delete and view pwermission
* @return  array of user roles
*/

function rns_get_roles_list_for_different_settings(  ) {
	
	$Roles_Data = array();
	
	$roles 			=	new WP_Roles();
	
	
	foreach( $roles->get_names() as $key=>$val ) {
		$Roles_Data[$key] = $val;
	}

	return $Roles_Data;	
	
}


/*
* Function get interval value by report .
* @return  string 
*/

function rns_get_interval_value_by_report( $filter_interval = "" ) {
		
	$report =  filter_input( INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS, array("options" => array( "default" => "basic_report") ));
	
	$report_array =  array( "basic_report" => 'rns_basic_interval'  , "reply_report" => 'rns_productivity_interval' , "resolution_report" => 'rns_resolution_interval' , "delay_report" =>'rns_delay_interval' , "distribution_report" => 'rns_distribution_interval' ,"trend_report" =>'rns_trend_interval' );
	
	
	$interval = get_option( $report_array[$report] , 10 );
	
	// if interval value is set and va;ue is greater than 0 
	if( !empty( $filter_interval ) && count( $filter_interval ) > 0 ) {
	
		update_option( $report_array[$report] , $filter_interval );
		
	}
	
	return $interval;
	
}


/* 
* Function to get median 
* @Params: array 
* @return: mixed of median value
*/
	
function rns_calculate_median($arr) {
	
	$arr = array_unique($arr);
	sort($arr);

    $count = count($arr); //total numbers in array
	
    $middleval = floor(($count-1)/2); // find the middle value, or the lowest middle value
	
    if($count % 2) { // odd number, middle is the median
	
        $median = $arr[$middleval];
		
    } else { // even number, calculate avg of 2 medians
	
        $low = $arr[$middleval];
        $high = $arr[$middleval+1];
        $median = (($low+$high)/2);
		
    }
	
    return $median;
}



/* 
* Function to option data by name 
* @Params: string option name 
* @return: array of option data.
*/

function rns_get_option_data_by_name( $option_name ) {
	
	$save_Roles		=	get_option( $option_name , true );

	if( $save_Roles != '' ) { //if roles array is not empty
		$saveRoles	=	explode( "," , $save_Roles );
	}
	else {
		$saveRoles	=	array();
	}
		
	return 	$saveRoles;
	
}


/*
* Function to get report name
* @Params: action
* @return: string contain report title
*/
function rns_get_report_name_by_action( $action ) {

	switch( $action ) {
	
		case 'basic_report':
		$name = __( "Ticket Count" , 'reports-and-statistics' );
		break;
		
		case 'reply_report':
		$name = __( "Productivity Analysis" , 'reports-and-statistics' );
		break;
		
		case 'resolution_report':
		$name = __( "Resolution Analysis" , 'reports-and-statistics' );
		break;
		
		case 'delay_report':
		$name = __( "Delay Analysis" , 'reports-and-statistics' );
		break;
		
		case 'distribution_report':
		$name = __( "Distribution Analysis" , 'reports-and-statistics' );
		break;
		
		case 'trend_report':
		$name = __( "Trend Analysis", 'reports-and-statistics' );
		break;
		
		default:
		$name = __( "Reports" , 'reports-and-statistics' );
		
	}

	return $name;
}



/*
* Function to get dropdown of clients
* @Params: array | array
* @return: void
*/
function rns_get_client_dropdown( $ticket_author , $ticket_author_get )  {
	// clients dropdown list
	 $options ='';
	 
	 foreach ( $ticket_author as $user ) { 
	 	
		if( is_array($ticket_author_get) &&  in_array($user->ID,$ticket_author_get))  {
			$options .=  '<option value="'. $user->ID .'"  selected="selected"   >'. $user->user_nicename . '</option>';
		}
	 } 
	 
	 $args = array(  'please_select'=>true, 'select2' => true, 'name' => 'ticket_author[]', 'id' => 'ticket_author', 'data_attr' => array( 'capability' => 'create_ticket' ) ,"class"=> "ticket_author filter-selectbox" ,"multiple"=>true );	
		
	 echo wpas_dropdown( $args, $options );	
}

/*
* Function to get dropdown of clients
* @Params: array 
* @return: array
*/

function rns_get_datapoints_xinterval_by_points( $points ) {
	
	$dataPoints = array();
	$xinterval= '';
	
	foreach ( $points as $key => $val ) {
		
		
		
		if( is_numeric( $key ) ) { // if key data is numeric
			
			$dataPoints[] = array("y" => $val, "label" =>  $key , "x" =>  $key  );
			
		} else {
			
			$dataPoints[] = array("y" => $val, "label" =>  $key   );							
		}
		
		
		// if label value is numeric then set interval value
		if(is_numeric($key)) {
			$xinterval = ", interval: ".rns_get_interval_value_by_report() ;
		}
	
	}
	
	$data = array( "xinterval"=>$xinterval, "datapoints"=>$dataPoints );
	
	return $data;
}


/*
* Function to get combine the code of two chart type in trend reports
* @Params:  points | second | title | xtitle | ytitle | gid | chart | xaxis 
* @return: void
*/


function rns_get_graph_data_in_trend_reports_com( $points , $second , $title , $xtitle ,$ytitle , $gid , $chart , $xaxis ) {
		
		$graphString = '';	$counter = 1; $dataPoints = array();
				
			foreach( $points as $key=>$point ) {
				
				$display_name = rns_get_display_name_by_second_option( $second, $key );
				
				$graphString .= ' { type : '.$chart.',
									toolTipContent :  " {display} : {y}",
									showInLegend: true, 
									legendText: "'.  $display_name .'",
									dataPoints:     ';
				
				$point = rns_drop_zero_row_column_from_points_array( $point , "2"  );
				
				foreach($point as $label=>$val){
					foreach($val as $l => $d ) {
						$dataPoints[] = array("y" => $d, "x" =>  $label  , "display" =>   $display_name .' , '.$xtitle.' '.$label.' , '.$l  );
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
							text: "'.$title.' By '.rn_get_custom_fields_title_by_name( $second ).'"
						},
						axisY: {
							title: "'. $ytitle.'",
									interval: '.rns_get_interval_value_by_report().'
						},
						axisX: {
							title: "'. $xtitle .'"
						},
						data: [ '.$graphString.' ]
					});
					chart.render();
				});
			</script>';	
	
}


/*
* Function to remove zero column or row from array
* @Params:  points | second 
* @return: array of filtered points data
*/

function rns_drop_zero_row_column_from_points_array( $points , $type ) {

	$drop_zero_rows		 	= filter_input(INPUT_GET, 'drop_zero_rows',  FILTER_SANITIZE_SPECIAL_CHARS); 
	$drop_zero_columns		= filter_input(INPUT_GET, 'drop_zero_columns',  FILTER_SANITIZE_SPECIAL_CHARS); 
	
	
	
	/*  if second dimenssion is none and  basic report opened */
	if( $type=="1" ) {
			
		foreach ( $points as $key => $label ) {
			
			// if drop zero columns checked from filter
			if( $drop_zero_columns == "1" &&  $points[$key]==0) {
					unset($points[$key]);	
			}	
		}
			
	}
	
	/*  if second dimenssion is not none and  basic report opened */	
	if( $type=="2"  ) {
		
		foreach ( $points as $k => $l ) {
			
			foreach($points[$k] as $key => $label ) {
				
				// if drop zero columns checked from filter
				if( $drop_zero_columns == "1" &&  array_sum(array_column($points,$key))==0) {
					unset($points[$k][$key]);	
				}
			
			}
			
			// if drop zero rows checked from filter
			if( $drop_zero_rows == 1 && array_sum($points[$k])== 0 ) {
					unset($points[$k]);
			}
		}
		
	}
	
		return $points;
}

