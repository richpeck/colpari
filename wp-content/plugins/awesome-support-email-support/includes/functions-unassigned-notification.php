<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class ASES_Unassigned_Email_Notification extends WPAS_Email_Notification {
	
	
	public function __construct( $post_id ) {

		/* Make sure the given post belongs to our plugin. */
		if ( 'wpas_unassigned_mail' !== get_post_type( $post_id ) ) {
			return new WP_Error( 'incorrect_post_type', __( 'The post ID provided does not match any of the plugin post types', 'awesome-support' ) );
		}

		/* Set the e-mail content type to HTML */
		add_filter( 'wp_mail_content_type', array( $this, 'set_html_mime_type' ) );

		/* Set the post ID */
		$this->post_id = $post_id;
		
		
		/**
		 * Define the ticket ID, be it $post_id or not.
		 */
		if ( 'wpas_unassigned_mail' === get_post_type( $post_id ) ) {
			$this->ticket_id = $post_id;
		}

	}
	
	/**
	 * Get the post object for the unassigned ticket.
	 *
	 * @since  3.0.2
	 * @return boolean|object The ticket object if there is a reply, false otherwise
	 */
	public function get_ticket() {

		if ( isset( $this->ticket ) ) {
			return $this->ticket;
		}

		if ( 'wpas_unassigned_mail' !== get_post_type( $this->ticket_id ) ) {
			return false;
		}

		$this->ticket = get_post( $this->ticket_id );

		return $this->ticket;

	}
	
		
	/**
	 * Get the available template tags for unassigned ticket.
	 * 
	 * @return type
	 */
	public static function get_tags() {
		
		
		$parent_tags = parent::get_tags();
		
		$tags = array();
		
		$skip = array('{agent_name}', '{agent_email}'); // Those tags are not available for unassigned tickets
		
		
		foreach( $parent_tags as $tag ) {
			
			if( !in_array( $tag['tag'], $skip ) ) {
				$tags[] = $tag;
			}
		}
		
		return $tags;
		
	}
}


add_filter( 'wpas_email_notifications_email', 'ases_set_unassigned_notification_recipient', 10, 3 );

/**
 * Set recipients for unassigned ticket e-mail notification
 * 
 * @param array $args
 * @param string $case
 * @param int $ticket_id
 * 
 * @return array
 */
function ases_set_unassigned_notification_recipient( $args, $case, $ticket_id ) {

	if ( 'unassigned_ticket_created' === $case ) {
		$args['recipient_email'] = wpas_get_option( 'recipients_email_unassigned_ticket_created' );
	}
	
	return $args;

}

add_filter( 'wpas_email_notifications_pre_fetch_content', 'ases_get_unassigned_notification_content', 10, 3 );
/**
 * Get unassigned e-mail notification content
 *
 * @param string $value     Notification content
 * @param int    $ticket_id ID of the ticket being processed
 * @param string $case      Case of the notification being sent
 *
 * @return string
 */
function ases_get_unassigned_notification_content( $value, $ticket_id, $case ) {

	if ( 'unassigned_ticket_created' === $case ) {
		$value = wpas_get_option( 'content_email_unassigned_ticket_created' );
	}

	return $value;

}

add_filter( 'wpas_email_notifications_pre_fetch_subject', 'ases_get_unassigned_notification_subject', 9, 3 );

/**
 * Get unassigned ticket e-mail notification subject
 *
 * @param string $value     Notification subject
 * @param int    $ticket_id ID of the ticket being processed
 * @param string $case      Case of the notification being sent
 *
 * @return string
 */
function ases_get_unassigned_notification_subject( $value, $ticket_id, $case ) {
	
	if ( 'unassigned_ticket_created' === $case ) {
		$value = wpas_get_option( 'subject_email_unassigned_ticket_created' );
	}
	
	return $value;
}


add_filter( 'wpas_email_notifications_cases', 'ases_register_unassigned_notification_case', 10, 1 );

/**
 * Register unassigned ticket case
 
 * @param array $cases Existing notification cases
 *
 * @return array
 */
function ases_register_unassigned_notification_case( $cases ) {

	if ( ! in_array( 'unassigned_ticket_created', $cases ) ) {
		$cases[] = 'unassigned_ticket_created';
	}

	return $cases;
}

add_filter( 'wpas_email_notifications_case_is_active', 'ases_activate_unassigned_notification_case', 10, 2 );

/**
 * Set email notification active
 * 
 * @param bool   $active Notification activation status
 * @param string $case   Case of the notification being sent out
 *
 * @return bool
 */
function ases_activate_unassigned_notification_case( $active, $case ) {

	if ( 'unassigned_ticket_created' === $case ) {
		$active = wpas_get_option( 'enable_unassigned_ticket_created', true );
	}

	return $active;

}


add_filter( 'wpas_email_notifications_cases_active_option', 'ases_unassigned_notification_active_option_name', 10, 1 );

/**
 * Set unassigned notification active option name
 *
 * @param array $cases Array of cases with their "enable" option name
 *
 * @return array
 */
function ases_unassigned_notification_active_option_name( $cases ) {
	$cases['unassigned_ticket_created'] = 'enable_unassigned_ticket_created';

	return $cases;
}