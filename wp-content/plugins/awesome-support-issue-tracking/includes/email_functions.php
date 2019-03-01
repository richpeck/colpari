<?php


add_filter( 'wpas_email_notifications_template_tags', 'wpas_it_email_notifications_template_tags' );

function wpas_it_email_notifications_template_tags( $tags ) {
	
	$new_tags = array(
			array(
				'tag' 	=> '{issue_id}',
				'desc' 	=> __( 'Converts into issue ID', 'wpas_it' )
			),
			
			array(
				'tag' 	=> '{issue_agent_name}',
				'desc' 	=> __( 'Converts into issue agent name', 'wpas_it' )
			),
			array(
				'tag' 	=> '{issue_agent_email}',
				'desc' 	=> __( 'Converts into issue agent e-mail address', 'wpas_it' )
			),
			
			array(
				'tag' 	=> '{issue_title}',
				'desc' 	=> __( 'Converts into issue title', 'wpas_it' )
			),
			
			array(
				'tag' 	=> '{issue_message}',
				'desc' 	=> __( 'Converts into current issue message', 'wpas_it' )
			),
			array(
				'tag' 	=> '{issue_comment}',
				'desc' 	=> __( 'Converts into last comment of an issue', 'wpas_it' )
			),
			
			array(
				'tag' 	=> '{full_issue}',
				'desc' 	=> __( 'Converts into full issue', 'wpas_it' )
			),
			
			array(
				'tag' 	=> '{issue_status}',
				'desc' 	=> __( 'Converts into issue status', 'wpas_it' )
			),
			
			array(
				'tag' 	=> '{issue_priority}',
				'desc' 	=> __( 'Converts into issue priority', 'wpas_it' )
			),
			
			array(
				'tag' 	=> '{issue_admin_link}',
				'desc' 	=> __( 'Displays a link to issue details in admin (for agents)', 'wpas_it' )
			),
			array(
				'tag' 	=> '{issue_admin_url}',
				'desc' 	=> __( 'Displays the URL <strong>only</strong> (not a link link) to issue details in admin (for agents)', 'wpas_it' )
			),
			
			array(
				'tag' 	=> '{comment}',
				'desc' 	=> __( 'Converts into comment content', 'wpas_it' )
			),
			
			array(
				'tag' 	=> '{comment_status}',
				'desc' 	=> __( 'Converts into comment status', 'wpas_it' )
			),
			array(
				'tag' 	=> '{comment_type}',
				'desc' 	=> __( 'Converts into comment type', 'wpas_it' )
			)
		);
	
	return array_merge( $tags, $new_tags );
}


/**
 * Return recipients from you ids
 * 
 * @param type $user_ids
 * 
 * @return array
 */
function wpas_it_user_ids_recipients( $user_ids ) {
	
	$recipients = array();
	
	foreach ( $user_ids as $user_id ) {
		$user = get_user_by( 'id', $user_id );
		
		if( $user && $user->user_email ) {
			$recipients[] = array( 'user_id' => $user_id, 'email' => $user->user_email );
		}
	}
	
	return $recipients;
}


/**
 * Return ticket agents for email alerts
 * 
 * @param int $ticket_id
 * 
 * @return array
 */
function wpas_it_ticket_agents( $ticket_id ) {
	$recipients = array();
	
	$agents = wpas_get_ticket_agents( $ticket_id );
	
	
	foreach( $agents as $agent ) {
		$recipients[] = array( 'user_id' => $agent->ID, 'email' => $agent->user_email );
	}
	
	return $recipients;
}



/**
 * Return recipients for private comment notification
 * 
 * @param int $post_id
 * @param WPAS_IT_Issue $issue
 * @param WPAS_IT_Comment $comment
 * @param int $ticket_id
 * 
 * @return array
 */
function wpas_it_alert_recipients_issue_private_comment_added( $post_id, $issue, $comment, $ticket_id ) {
	
	$recipients = array();
	
	if( ! $issue instanceof WPAS_IT_Issue ) {
		return $recipients;
	}
	
	$agent = $issue->getPrimaryAgent();
	
	if( $agent && $agent->user_email ) {
		$recipients[] = array( 'user_id' => $agent->ID, 'email' => $agent->user_email );
	}
	
	
	return $recipients;
}

/**
 * Return recipients for semi private comment notification
 * 
 * @param int $post_id
 * @param WPAS_IT_Issue $issue
 * @param WPAS_IT_Comment $comment
 * @param int $ticket_id
 * 
 * @return array
 */
function wpas_it_alert_recipients_issue_semi_private_comment_added( $post_id, $issue, $comment, $ticket_id ) {
	
	$recipients = wpas_it_alert_recipients_issue_private_comment_added( $post_id, $issue, $comment, $ticket_id );
	
	$additional_agents = $issue->getAdditionalAgentIDs();
	
	$additional_agent_recipients = wpas_it_user_ids_recipients( $additional_agents );
	
	$recipients = array_merge( $recipients, $additional_agent_recipients );
	
	
	return $recipients;
	
}

/**
 * Return recipients for regular comment notification
 * 
 * @param int $post_id
 * @param WPAS_IT_Issue $issue
 * @param WPAS_IT_Comment $comment
 * @param int $ticket_id
 * 
 * @return array
 */
function wpas_it_alert_recipients_issue_regular_comment_added( $post_id, $issue, $comment, $ticket_id ) {
	$recipients = wpas_it_alert_recipients_issue_semi_private_comment_added( $post_id, $issue, $comment, $ticket_id );
	
	$parties = $issue->getAdditionalInterestedParties();
	
	foreach( $parties as $party ) {
		
		if( isset( $party['active']) && $party['active'] ) {
			
			$email = $party['email'];
			
			if( isset( $party['name'] ) && $party['name'] ) {
				$email = $party['name'] . ' <'. $email  .'>';
			}
			
			$recipients[] = $email;
		}
	}
	
	return $recipients;
	
}

/**
 * Return recipients for issue closed notification
 * 
 * @param int $post_id
 * @param WPAS_IT_Issue $issue
 * @param WPAS_IT_Comment $comment
 * @param int $ticket_id
 * 
 * @return array
 */
function wpas_it_alert_recipients_issue_closed( $post_id, $issue, $comment, $ticket_id ) {
	return wpas_it_alert_recipients_issue_semi_private_comment_added( $post_id, $issue, $comment, $ticket_id );
}

/**
 * Return recipients for private comment notification for issue ticket
 * 
 * @param int $post_id
 * @param WPAS_IT_Issue $issue
 * @param WPAS_IT_Comment $comment
 * @param int $ticket_id
 * 
 * @return array
 */
function wpas_it_alert_recipients_ticket_issue_private_comment_added( $post_id, $issue, $comment, $ticket_id ) {
	
	
	$agent_id    = intval( get_post_meta( $ticket_id, '_wpas_assignee', true ) );
	
	
	$recipients = wpas_it_user_ids_recipients( array( $agent_id ) );
	
	return $recipients;
}

/**
 * Return recipients for semi private comment notification for issue ticket
 * 
 * @param int $post_id
 * @param WPAS_IT_Issue $issue
 * @param WPAS_IT_Comment $comment
 * @param int $ticket_id
 * 
 * @return array
 */
function wpas_it_alert_recipients_ticket_issue_semi_private_comment_added( $post_id, $issue, $comment, $ticket_id ) {
	return wpas_it_ticket_agents( $ticket_id );
	
}


/**
 * Return recipients for regular comment notification for issue ticket
 * 
 * @param int $post_id
 * @param WPAS_IT_Issue $issue
 * @param WPAS_IT_Comment $comment
 * @param int $ticket_id
 * 
 * @return array
 */
function wpas_it_alert_recipients_ticket_issue_regular_comment_added( $post_id, $issue, $comment, $ticket_id ) {
	
	$recipients = wpas_it_ticket_agents( $ticket_id );
	
	$first_addl_email = wpas_get_cf_value( 'first_addl_interested_party_email',  $ticket_id );
	$second_addl_email= wpas_get_cf_value( 'second_addl_interested_party_email', $ticket_id );
	
	if( $first_addl_email ) {
		$recipients[] = $first_addl_email;
	}
	
	if( $second_addl_email ) {
		$recipients[] = $second_addl_email;
	}
	
	
	
	$client_id = get_post_field( 'post_author', $ticket_id );
	$client_recipients = array();
	
	
	if ( ! empty( $client_id ) ) {
		$client_recipients = wpas_it_user_ids_recipients( array( $client_id ) );
	}
	
	$recipients = array_merge( $client_recipients, $recipients );
	
	return $recipients;
	
}

/**
 * Return recipients for issue closed notification for issue ticket
 * 
 * @param int $post_id
 * @param WPAS_IT_Issue $issue
 * @param WPAS_IT_Comment $comment
 * @param int $ticket_id
 * 
 * @return array
 */
function wpas_it_alert_recipients_ticket_issue_closed( $post_id, $Issue, $Comment, $ticket_id ) {
	
	return wpas_it_alert_recipients_ticket_issue_regular_comment_added( $post_id, $issue, $comment, $ticket_id );
}


add_filter( 'wpas_it_email_notifications_email', 'wpas_it_set__notification_recipient', 10, 6 );

/**
 * 
 * Set recipients for issue related e-mail notification
 * 
 * @param array $args
 * @param string $case
 * @param int $post_id
 * @param Object $Issue
 * @param null| Object $Comment
 * @param int $ticket_id
 * 
 * @return array
 */
function wpas_it_set__notification_recipient( $args, $case, $post_id, $Issue, $Comment, $ticket_id ) {

	$cases  = array_keys( wpas_it_notification_actions() );
	
	if ( in_array( $case, $cases ) ) {
		
		$args['recipient_email'] = call_user_func( "wpas_it_alert_recipients_{$case}", $post_id, $Issue, $Comment, $ticket_id );

	}
	
	return $args;

}



add_filter( 'wpas_email_notifications_cases', 'wpas_it_register_notification_case', 10, 1 );

/**
 * Register issue notification cases
 
 * @param array $cases Existing notification cases
 *
 * @return array
 */
function wpas_it_register_notification_case( $cases ) {
	
	$actions = wpas_it_notification_actions();
	foreach( $actions as $case => $action_name ) {
		$cases[] = $case;
	}
	
	return $cases;
}

add_filter( 'wpas_email_notifications_case_is_active', 'wpas_it_activate_notification_case', 10, 2 );

/**
 * Set active email notification
 * 
 * @param bool   $active Notification activation status
 * @param string $case   Case of the notification being sent out
 *
 * @return bool
 */
function wpas_it_activate_notification_case( $active, $case ) {
	
	$actions = wpas_it_notification_actions();
	
	$cases = array_keys( $actions );
	
	if( in_array( $case, $cases ) ) {
		$active = true;
	}

	return $active;

}


add_filter( 'wpas_email_notifications_cases_active_option', 'wpas_it_notification_active_option_name', 10, 1 );

/**
 * Set active option names for issue related notifications
 *
 * @param array $cases Array of cases with their "enable" option name
 *
 * @return array
 */
function wpas_it_notification_active_option_name( $cases ) {
	
	$actions = wpas_it_notification_actions();
	foreach( $actions as $case => $action_name ) {
		$cases[$case] = "enable_{$case}";
	}
	
	return $cases;
}



add_action( 'wpas_it_after_comment_added', 'wpas_it_after_comment_added' , 11, 3 );


/**
 * Send appropriate notification once a comment is added to an issue
 * 
 * @param int $issue_id
 * @param int $comment_id
 * @param string $comment_type
 */
function wpas_it_after_comment_added( $issue_id, $comment_id, $comment_type ) {
	
	$issue = new WPAS_IT_Issue( $issue_id );
		
	$tickets = $issue->getTickets();
	
	/* block notifications for semi-private and private comments from other add-ons */
	$pf_ticket_notification_blocked = false;
	$pf_ticket_user_contact_blocked = false;
	if( !empty( $tickets ) && 'regular' !== $comment_type  ) {
		if( class_exists( 'WPAS_PF_Ticket_Notification_Email' ) ) {
			$pf_ticket_notification = WPAS_PF_Ticket_Notification_Email::get_instance();
			remove_filter( 'wpas_email_notifications_email', array( $pf_ticket_notification, 'set_notification_emails'), 11 );
		}
		if( class_exists( 'WPAS_PF_Ticket_User_Contact' ) ) {
			$pf_ticket_user_contact = WPAS_PF_Ticket_User_Contact::get_instance();
			remove_filter( 'wpas_email_notifications_email', array( $pf_ticket_user_contact, 'set_user_contact_emails'), 11 );
		}
	}
	
	
	
		
	foreach( $tickets as $ticket ) {
		$notify = new WPAS_IT_Email_Notification( $comment_id, $ticket->ID );
			
		if ( !is_wp_error( $notify ) ) {
			$notify->notify( "ticket_issue_{$comment_type}_comment_added" );
		}
			
	}
	
	// add removed notification filters back
	if( $pf_ticket_notification_blocked ) {
		add_filter( 'wpas_email_notifications_email', array( $pf_ticket_notification, 'set_notification_emails'), 11, 3 );
	}
	
	if( $pf_ticket_user_contact_blocked ) {
		add_filter( 'wpas_email_notifications_email', array( $pf_ticket_user_contact, 'set_user_contact_emails' ), 11, 3 );
	}
		
	$notify = new WPAS_IT_Email_Notification( $comment_id );
	if ( !is_wp_error( $notify ) ) {
		$notify->notify( "issue_{$comment_type}_comment_added" );
	}
	
}



add_action( 'wpas_after_close_issue', 'wpas_it_after_issue_closed',	11, 1 );

/**
 * Send appropriate notification once an is closed
 * 
 * @param int $issue_id
 */
function wpas_it_after_issue_closed( $issue_id ) {
	$notify = new WPAS_IT_Email_Notification( $issue_id );
			
	if ( !is_wp_error( $notify ) ) {
		$notify->notify( "issue_closed" );
	}
}


add_action( 'wpas_after_close_issue_ticket', 'wpas_it_after_issue_ticket_closed', 11, 2 );

/**
 * Send a notification once a ticket is closed with issue
 * 
 * @param int $issue_id
 * @param int $ticket_id
 */
function wpas_it_after_issue_ticket_closed( $issue_id, $ticket_id ) {
	$notify = new WPAS_IT_Email_Notification( $issue_id, $ticket_id );
			
	if ( !is_wp_error( $notify ) ) {
		$notify->notify( "ticket_issue_closed" );
	}
}

