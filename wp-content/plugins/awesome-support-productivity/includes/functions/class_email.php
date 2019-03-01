<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class WPAS_PF_Email {
	
	
	protected static $instance = null;
	
	
	/**
	 * New notification types
	 * 
	 * @var array
	 */
	protected $cases = array( 
	    'ticket_merge_closed',
	    'ticket_merge_reply_added'
	    
	);
	
	
	public function __construct() {
		
		
		add_filter( 'wpas_email_notifications_pre_fetch_content',	array( $this, 'get_body' ),     90, 3 );
		add_filter( 'wpas_email_notifications_pre_fetch_subject',	array( $this, 'get_subject' ),  90, 3 );
		add_filter( 'wpas_email_notifications_notify_user',		array( $this, 'notify_user' ),  90, 3 );
		
		add_filter( 'wpas_email_notifications_cases',			array( $this, 'register_cases' ), 11, 1 );
		add_filter( 'wpas_email_notifications_case_is_active',		array( $this, 'activate_cases' ), 10, 2 );
		add_filter( 'wpas_email_notifications_cases_active_option',	array( $this, 'active_option_names' ) , 10, 1 );
		
	}
        
	
	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	/**
	 * Activate registered email types
	 * 
	 * @param boolean $active
	 * @param string $case
	 * 
	 * @return boolean
	 */
	public function activate_cases( $active, $case ) {
		
		if (  $this->case_exist( $case ) ) {
			$active = wpas_get_option( "enable_{$case}", true );
		}
		
		return $active;

	}
	
	/**
	 * Set option names for active template field, its used to check if a template is enabled or disabled
	 * 
	 * @param array $cases
	 * 
	 * @return array
	 */
	function active_option_names( $cases ) {
		
		foreach( $this->cases as $case ) {
			$cases[ $case ] = "enable_{$case}";
		}
		
		return $cases;
	}
	
	
	/**
	 * Register email types
	 * 
	 * @param array $cases
	 * 
	 * @return array
	 */
	public function register_cases( $cases = array() ) {
		
		foreach( $this->cases as $case ) {
			
			if( !in_array( $case, $cases ) ) {
				$cases[ $case ] = $case;
			}
		}
		
		return $cases;
	}
	
	
	/**
	 * Check if email type exists
	 * 
	 * @param string $case
	 * 
	 * @return boolean
	 */
	public function case_exist( $case ) {
		
		if( in_array( $case, $this->cases ) ) {
			return true;
		}
		
		return false;
		
	}
	
	
	/**
	 * Set email body content
	 * 
	 * @param string $body
	 * @param int $ticket_id
	 * @param string $case
	 * 
	 * @return string
	 */
	public function get_body( $body, $ticket_id, $case ) {
		
		if( $this->case_exist( $case ) ) {
			$body = wpas_get_option( "content_email_{$case}" );
		}
		return $body;
	}
        
	/**
	 * Set email subject
	 * 
	 * @param string $subject
	 * @param int $ticket_id
	 * @param string $case
	 * 
	 * @return string
	 */
	public function get_subject( $subject, $ticket_id, $case ) {

		if( $this->case_exist( $case ) ) {
			
			$subject = wpas_get_option( "subject_email_{$case}" );
			
		}

		return $subject;
	}
	
	
	/**
	 * Set email user
	 * 
	 * @param mixed $user
	 * @param string $case
	 * @param int $ticket_id
	 * 
	 * @return object
	 */
	public function notify_user( $user, $case, $ticket_id ) {
		
		if( $this->case_exist( $case ) && $user === null ) {
			
			$ticket = get_post( $ticket_id );
			$user = get_user_by( 'id', $ticket->post_author );
			
		}
		
		return $user;
	}
	

}