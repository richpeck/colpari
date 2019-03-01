<?php

/**
 * @package   Awesome Support Notifications
 * @author    Awesome Support
 * @link      http://www.getawesomesupport.com
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * get all notification actions
 * 
 * @return array
 */
function as_in_notification_actions() {
    
    
	return array_merge(
		as_in_default_notification_actions() , 
		as_in_status_notification_actions()
		);
}


/**
 * get default notification actions
 * 
 * @return array
 */
function as_in_default_notification_actions() {
	return array(
		'new_ticket'		=> __( 'New ticket', 'as-instant-notifications' ),
		'new_reply_agent'	=> __( 'New reply (from agent)', 'as-instant-notifications' ),
		'new_reply_client'	=> __( 'New reply (from client)', 'as-instant-notifications' ),
		'ticket_closed'		=> __( 'Ticket closed', 'as-instant-notifications' ),
		'ticket_reopened'	=> __( 'Ticket re-opened', 'as-instant-notifications' ),
		'unassigned_ticket_created' => __( 'Unassigned Ticket Created', 'as-instant-notifications' )
	);
}

/**
 * get ticket status actions
 * 
 * @return array
 */
function as_in_status_notification_actions() {
	$status_list = wpas_get_post_status();

	$status_actions = array();

	foreach( $status_list as $status_name => $status_title ) {
	    $status_actions["status_ac_" . $status_name] = __( $status_title, 'as-instant-notifications' );
	}

	return $status_actions;
}



/**
 * check if action is a status action
 * @param string $action
 * 
 * @return boolean
 */
function as_in_is_status_action( $action = '' ) {
	
	return $action && substr( $action, 0, 10 ) == 'status_ac_';
	
}



/**
 * adding email as a new notification service
 * @param array $services
 * 
 * @return array
 */
function as_in_notification_services( $services = array() ) {
	
	if( !in_array('email', $services) ) {
		$services[] = 'email';
	}
	return $services;
}



/**
 * adding a new notification case for emails
 * @param array $cases
 * 
 * @return array
 */
function as_in_register_notification_case( $cases = array() ) {
	
	if( !in_array('notification_email', $cases) ) {
		$cases['notification_email'] = 'notification_email';
	}

	return $cases;
}

/**
 * Return default 3rd party notification types
 * 
 * @return array
 */
function as_in_default_email_active_types() {
	
	return array(
		'additional_party_1',
		'additional_party_2',
		'additional_emails',
		'additional_users'
	);
}

add_filter( 'wpas_notification_services',     'as_in_notification_services'            );
add_filter( 'wpas_email_notifications_cases', 'as_in_register_notification_case', 11, 1);