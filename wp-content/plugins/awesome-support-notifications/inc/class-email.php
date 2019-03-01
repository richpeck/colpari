<?php
/**
 * @package   Awesome Support Notifications/Email
 * @author    Awesome Support
 * @link      http://www.getawesomesupport.com
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class WPAS_IN_Email {
    
    
	protected $notification_name = 'notification_email';

	/**
	 * Notification data.
	 *
	 * @since  0.1.0
	 * @var    array
	 */
    
	protected $notification;
    
	/**
	 * Check if its a new reply notification
	 * @var boolean
	 */
	protected $is_reply_notification = false;
    
    
	/**
	 * could be ticket id or reply id
	 * @var int
	 */
	protected $post_id;

	/**
	 * 
	 * @param array $notification
	*/
	public function __construct( $notification, $post_id ) {
		$this->notification = $notification;
        
		if( $this->notification['context'] == 'new_reply_agent' || $this->notification['context'] == 'new_reply_client' ) {
			$this->is_reply_notification = true;
		}
		
		$this->post_id = $post_id;
		
	}  
	
	/**
	 * Set unique notification context with email args, so we can obtain active 3rd party notification types
	 * 
	 * @param array $args
	 * @param string $case
	 * @param int $ticket_id
	 * 
	 * @return array
	 */
	public function set_context( $args, $case, $ticket_id ) {
		
		$args['context'] = $this->notification['context'];
		
		return $args;
	}
	
	
	/**
	 * Return unique email addresses or user ids
	 * 
	 * @param array $args
	 * @param string $case
	 * @param int $ticket_id
	 * 
	 * @return array
	 */
	public function set_unique_emails( $args, $case, $ticket_id ) {
		
		if( $case == $this->notification_name ) {
			
			
			$emails = $args['recipient_email'];
			$just_emails = array();
			$just_ids = array();
			
			$unique_emails = array();
			
			foreach( $emails as $_email ) {
				$email = is_array( $_email ) ? $_email['email'] : $_email;
				
				if( is_array( $_email ) ) {
					
					if( !in_array( $_email['user_id'], $just_ids ) ) {
						$unique_emails[] = $_email;
						$just_ids[] = $_email['user_id'];
					}
					
				} else if( !in_array( $_email, $just_emails ) ) {
					$just_emails[] = $_email;
					$unique_emails[] = $_email;
				}
				
			}
			
			
			$args['recipient_email'] = $unique_emails;
		} 

		return $args;
	}
	
	/**
	 * Get email recipients
	 * @param array $email
	 * @param string $case
	 * @param int $ticket_id
	 * 
	 * @return array
	 */
	public function get_email( $args, $case, $ticket_id ) {
            
		if( $case == $this->notification_name ) {
			
			$recipient = wpas_get_option( "{$this->notification['context']}__recipient" );
			
			$emails = $args['recipient_email'];
			if( $recipient ) {
				$emails[] = $recipient;
			}
			
			
			$types = $this->get_active_email_types();
			
			$meta_key_user_types = array(
			    'primary_agent'   => '_wpas_assignee',
			    'secondary_agent' => '_wpas_secondary_assignee',
			    'tertiary_agent'  => '_wpas_tertiary_assignee',
			);
			
			$meta_key_email_types = array(
			    'additional_party_1' => '_wpas_first_addl_interested_party_email',
			    'additional_party_2' => '_wpas_second_addl_interested_party_email'
			);
			
			
			// Get assignee email addresses
			foreach( $meta_key_user_types as $utype_fname => $utype_metakeey ) {
				if( in_array( $utype_fname, $types ) ) {
					$user_id = get_post_meta( $ticket_id, $utype_metakeey, true );
					if( $user_id ) {
						$user = get_user_by( 'id', $user_id );
						$emails[] = array( 'user_id' => $user->ID, 'email' => $user->user_email );
					}
				}
			}
			
			
			// Get additional interested party email addresses
			foreach( $meta_key_email_types as $etype_fname => $etype_metakeey ) {
				if( in_array( $etype_fname, $types ) ) {
					$email = get_post_meta( $ticket_id, $etype_metakeey, true );
					if( $email ) {
						$emails[] = $email;
					}
				}
			}
			
			
			// Get ticket author email address (only if its turned on as a receipient type)
			if( in_array( 'client', $types ) ) {			
				$ticket_creator = get_post_field( 'post_author', $ticket_id );
				if ( ! empty( $ticket_creator ) ) {
					$ticket_creator_data = get_userdata( $ticket_creator );
					if ( ! empty( $ticket_creator_data ) ) {
						if ( ! empty ( $ticket_creator_data->user_email ) ) {
							$emails[] = $ticket_creator_data->user_email ;
						}
					}
				}
			}
			
			
			// set up return variable with the new email array
			$args['recipient_email'] = $emails;
		} 

		
		return $args;
	}
        
	/**
	 * Get email body content
	 * @param string $body
	 * @param int $ticket_id
	 * @param string $case
	 * 
	 * @return string
	 */
	public function get_body( $body, $ticket_id, $case ) {
		
		if( $case == $this->notification_name ) {
			$body = wpas_get_option( "{$this->notification['context']}__content" );
		}
		return $body;
	}
        
	/**
	 * Get email subject
	 * @param string $subject
	 * @param int $ticket_id
	 * @param string $case
	 * 
	 * @return string
	 */
	public function get_subject( $subject, $ticket_id, $case ) {

		if( $case == $this->notification_name ) {
			$subject = wpas_get_option( "{$this->notification['context']}__subject" );
		}

		return $subject;
	}
	
	
	/**
	 * 
	 * @param mixed $user
	 * @param string $case
	 * @param int $ticket_id
	 * 
	 * @return type
	 */
	public function notify_user($user, $case, $ticket_id) {
		if( $case == $this->notification_name && $user === null) {
			$user = get_user_by( 'id', intval( get_post_meta( $ticket_id, '_wpas_assignee', true ) ) );
		}
		
		return $user;
	}
	

	public function get_active_email_types() {
		
		$default_types = as_in_default_email_active_types();
		$active_email_types = maybe_unserialize( wpas_get_option( "{$this->notification['context']}__active_email_types", $default_types ) ) ;
		
		return $active_email_types;
	}
	
	/**
	 * Send Event Emails
	 * 
	 * @return boolean
	 */
	public function notify() {
		
		
		add_filter( 'wpas_email_notifications_email',                 array( $this, 'set_context' ),  9, 3 );
		add_filter( 'wpas_email_notifications_email',                 array( $this, 'get_email' ),    99, 3 );
		add_filter( 'wpas_email_notifications_email',                 array( $this, 'set_unique_emails' ),    99, 3 );
		
		add_filter( 'wpas_email_notifications_pre_fetch_content',     array( $this, 'get_body' ),     99, 3 );
		add_filter( 'wpas_email_notifications_pre_fetch_subject',     array( $this, 'get_subject' ),  99, 3 );
		add_filter( 'wpas_email_notifications_notify_user',	      array( $this, 'notify_user' ),  99, 3 );

		
		$totify_post_id = $this->is_reply_notification ? $this->post_id : $this->notification['id'];
		$sent = wpas_email_notify( $totify_post_id, $this->notification_name );

		remove_filter( 'wpas_email_notifications_email',              array( $this, 'set_context' ),  9 );
		remove_filter( 'wpas_email_notifications_email',              array( $this, 'set_unique_emails' ), 99 );
		remove_filter( 'wpas_email_notifications_pre_fetch_content',  array( $this, 'get_body' ),     99 );
		remove_filter( 'wpas_email_notifications_pre_fetch_subject',  array( $this, 'get_subject' ),  99 );
		remove_filter( 'wpas_email_notifications_email',              array( $this, 'get_email' ),    99 );
		remove_filter( 'wpas_email_notifications_notify_user',	      array( $this, 'notify_user' ),  99 );

		if( $sent && !is_wp_error( $sent ) ) {
			return true;
		}
		return false;
	}

}