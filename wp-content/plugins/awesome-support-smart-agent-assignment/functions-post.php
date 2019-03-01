<?php
/**
 * Find an available agent to assign a ticket to.
 *
 * It finds the agent on following conditions
 * 1. product check -if agent doesnot provides support for project move to next agent
 * 2. day -if agent doesnot provides support on this particular day move to next agent
 * 3. time -if agent doesnot provides support on the time of the day move to next agent
 * If agent is found assign to the one with the less tickets currently open.
 * If no agent is found assign to the default assignee set in settings.
 * @since  1.0
 *
 * @param  boolean|integer $agent_id $ticket_id The ticket that needs an agent
 *
 * @return integer  ID of the best agent for the job
 */

function new_wpas_find_agent( $agent_id , $ticket_id = false ) {
	
	do_action( 'wpas_open_ticket_before_assigned_smart_agent' , $ticket_id , $data );


	if ( defined( 'WPAS_DISABLE_AUTO_ASSIGN' ) && true === WPAS_DISABLE_AUTO_ASSIGN ) {
		return apply_filters( 'wpas_find_available_agent' , wpas_get_option( 'assignee_default' ) , $ticket_id );
	}

	$users = shuffle_assoc( wpas_get_users( apply_filters( 'wpas_find_agent_get_users_args' , array( 'cap' => 'edit_ticket' ) ) ) );
	$agent = array();

	//get product associated with ticket
	if( wpas_get_option( 'support_products' ) ) {
		$pid				=	'';
		$ticket_taxonomy	=	get_post_taxonomies( $ticket_id );
		if( in_array( 'product' , $ticket_taxonomy ) ) {
			$terms			= 	get_the_terms( $ticket_id , 'product' );
			foreach( $terms as $term ) {
				$pid	=	$term->term_id;
			}
		}
	}
	
	foreach ( $users->members as $user ) {

		$wpas_agent	=	new WPAS_Member_Agent( $user );

		/**
		 * Make sure the user really is an agent and that he can currently be assigned
		 */
		if ( true !== $wpas_agent->is_agent() || false === $wpas_agent->can_be_assigned() ) {
			continue;
		}
		
		$products	=	$days	=	$time_from	=	$time_to	=	array();	
		
		if( isset( $pid ) && !empty( $pid ) ) {
			//products the agent provide support for
			$products	=	get_user_option( 'esa_product', $user->ID  ); 
			if( empty( $products ) ) {
				$products	=	[];
			}
		
			//check user provides support for the product		
			if( !empty( $pid ) && in_array( $pid , $products ) !== true ) {
				continue;
			}
		}
		
		$now_day		=	date( 'l' );
		$current_time	=	date( 'g:ia' );
		
		//days and time agent is available
		$days_available =  get_user_option( 'esa_days_available' , $user->ID ) ; 
		
		if( empty( $days_available ) ){
			
			$days		=	[];
			$time_from	=	[];
			$time_to	=	[];
		
		} else {
			
			foreach ( $days_available as $dav ) {
				$days[]			=	$dav[0];
				$time_from[]	=	$dav[1];
				$time_to[]		=	$dav[2];
			}

		}
			
		$ntime	=	DateTime::createFromFormat( 'g:ia' , $current_time );
		
		//check if agent is available on ticket submission
		if( in_array( $now_day , $days ) !== true ) {
			continue;
		}
			
		//get key of the day
		$key	=	array_search( $now_day , $days );
		
		//get from and to time of that day
		$ftime	=	DateTime::createFromFormat( 'g:ia' , $time_from[$key] );
		$ttime	=	DateTime::createFromFormat( 'g:ia' , $time_to[$key] );
		
		//24 hour time rep
		$ntime	=	$ntime->format( 'H:i' );
		$ftime	=	$ftime->format( 'H:i' );
		$ttime	=	$ttime->format( 'H:i' );
	 				
		//check time within the agents support time
		if ( ( $ntime >= $ftime && $ntime <= $ttime ) != 1 ) {
			continue;
		}
		//end extra
		$count	=	$wpas_agent->open_tickets(); // Total number of open tickets for this agent

		if ( empty( $agent ) ) {
			
			$agent = array( 'tickets' => $count, 'user_id' => $user->ID );
			
		} else {
			
			if ( $count < $agent['tickets'] ) {
				
				$agent = array( 'tickets' => $count , 'user_id' => $user->ID );
				
			}
			
		}
	}
	
	if ( is_array( $agent ) && isset( $agent['user_id'] ) ) {
		
		$agent_id	=	$agent['user_id'];
		
	} else {

		$default_id	=	wpas_get_option( 'assignee_default' , 1 );

		if ( empty( $default_id ) ) {
			$default_id	=	1;
		}

		$agent_id	=	$default_id;

	}
	return $agent_id;
}
