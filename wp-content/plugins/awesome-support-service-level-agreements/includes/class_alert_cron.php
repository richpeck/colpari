<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

class WPAS_SLA_Alert_Cron {
    
    
    public $log;
    
    public $currentAlertData = null;
    
    
    public function __construct() {
        
    }
    
    
    /**
     * Get queued ticket alerts
	 * 
     * @global object $wpdb
	 * 
     * @return array
     */
    private function get_ticket_alerts() {
        global $wpdb;
		
        $current_time = date( SLA_DATE_FORMAT . ' ' . SLA_TIME_FORMAT, time() ) ;
		
		$q = "SELECT * FROM {$wpdb->prefix}sla_ticket_alerts a "
			. "INNER JOIN {$wpdb->prefix}postmeta pm ON pm.post_id = a.ticket_id "
			. "WHERE sent != 1 AND alert_due_time < %s AND pm.meta_key = %s AND pm.meta_value != %s";
		
			
		$results = $wpdb->get_results( $wpdb->prepare( $q, $current_time, '_wpas_status', 'closed' ) );
        
        return $results;
    }
    
    
    /**
     * Write log
	 * 
     * @param string $log
     */
    private function addLog( $log ) {
        if( wpas_sla_get_option( 'enable_cron_log' ) ) {
			wpas_write_log( 'wpas_sla', $log );
		}
    }
    
    
	/**
	 * Process alerts
	 * 
	 * @global object $wpdb
	 * 
	 * @return void
	 */
	public function process() {
		
		global $wpdb;
		
		
		$this->addLog( "Starting getting alerts" );
		
		$results = $this->get_ticket_alerts();
		
		if( empty( $results ) ) {
			$this->addLog( "There is no alert pending due", true );
			return;
		} 

		add_filter( 'wp_mail_content_type', array( $this, 'set_content_type' ) );
		
		$this->addLog( count( $results ) . " Alert(s) pending due." );
		
		
		foreach( $results as $alert ) {
			
			$this->addLog( "Processing Ticket #{$alert->ticket_id}, SLA #{$alert->sla_id}, Alert #{$alert->alert_id}" );
			
			$alerts = maybe_unserialize( get_post_meta( $alert->sla_id, 'sla_alerts', true ) );
			$alerts = $alerts && is_array( $alerts) ? $alerts : array();
			
			$sla_alert = isset( $alerts[ $alert->alert_id] ) ? $alerts[ $alert->alert_id] : array();
			
			if( !$sla_alert ) {
				continue;
			}
			
			$this->addLog( "Starting sending alert message." );
			if( $this->sendMessage( $alert, $sla_alert ) ) {
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}sla_ticket_alerts SET `sent`=%d WHERE id=%d", 1, $alert->id ) );
			}
			
			$this->addLog( "End processing Ticket #{$alert->ticket_id}, Alert #{$alert->sla_id}, Alert #{$alert->alert_id}" );
		}
		
		
		
		$this->addLog( "------------------------------------------------------------" );
	    
	    remove_filter( 'wp_mail_content_type', array( $this, 'set_content_type' ) );
	}
	
	
    /**
     * Return notification subject
	 * 
     * @param string $subject
     * @param int $ticket_id
	 * 
     * @return string
     * 
     */
    public function email_notification_subject( $subject, $ticket_id, $case ) {
		
		if( 'wpas_sla' !== $case ) {
			return $subject;
		}
		
        if( null !== $this->currentAlertData && !empty( $this->currentAlertData ) ) {
			
            $subject = $this->currentAlertData['sla_alert']['subject'];
        }
        
        return $subject;
    }

    /**
     * Return notification content
	 * 
     * @param string $message
     * @param int $ticket_id
	 * 
     * @return string
     * 
     */
    public function email_notification_body( $message, $ticket_id, $case ) {
		
		if( 'wpas_sla' !== $case ) {
			return $message;
		}
		
		if( null !== $this->currentAlertData && !empty( $this->currentAlertData ) ) {
            $message = $this->currentAlertData['sla_alert']['content'];
        }
        
        return $message;
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
		
		if( $case == 'wpas_sla' ) {
			
			
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
	 * 
	 * @param array $email
	 * @param string $case
	 * @param int $ticket_id
	 * 
	 * @return array
	 */
	public function email_notifications_email( $args, $case, $ticket_id ) {
		
		if( 'wpas_sla' !== $case ) {
			return $args;
		}
		
		$recipient = $this->currentAlertData['sla_alert']['recipients'];
		
		$emails = $args['recipient_email'];
		if( $recipient ) {
			$emails[] = $recipient;
		}
		
		
		$types =  $this->currentAlertData['sla_alert']['recipient_types'];
		
		$meta_key_user_types = array(
		    'primary_agent'   => '_wpas_assignee',
		    'secondary_agent' => '_wpas_secondary_assignee',
		    'tertiary_agent'  => '_wpas_tertiary_assignee',
		);
		
		$meta_key_email_types = array(
		    'additional_party_1' => '_wpas_first_addl_interested_party_email',
		    'additional_party_2' => '_wpas_second_addl_interested_party_email'
		);
		
		$user_ids = array();
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
		

		
		return $args;
	}
	
	
	/**
	 * Send alert message
	 * 
	 * @param object $alert
	 * @param array $sla_alert
	 * 
	 * @return boolean
	 */
    private function sendMessage( $alert, $sla_alert ) {
        
        $this->currentAlertData = array(
			'ticket_alert' => $alert,
			'sla_alert' => $sla_alert,
			'ticket' => get_post( $alert->ticket_id )
		);
        
		

        
        add_filter( 'wpas_email_notifications_pre_fetch_subject', array( $this, 'email_notification_subject'), 99, 3 );
        add_filter( 'wpas_email_notifications_pre_fetch_content', array( $this, 'email_notification_body'), 99, 3 );
        add_filter( 'wpas_email_notifications_email', array( $this, 'email_notifications_email' ), 99, 3 );
        add_filter( 'wpas_email_notifications_email', array( $this, 'set_unique_emails' ), 100, 3 );
		
		
        $sent = wpas_email_notify( $alert->ticket_id, 'wpas_sla' );
        
        remove_filter( 'wpas_email_notifications_pre_fetch_subject', array( $this, 'email_notification_subject' ), 99 );
        remove_filter( 'wpas_email_notifications_pre_fetch_content', array( $this, 'email_notification_body' ), 99 );
        remove_filter( 'wpas_email_notifications_email', array( $this, 'email_notifications_email' ), 99 );
		remove_filter( 'wpas_email_notifications_email', array( $this, 'set_unique_emails' ), 100 );
		
		
		if( is_wp_error( $sent ) ) {
			$sent = false;
            $this->addLog( "Error while sending alert message." );
            
            $this->addLog( "Error Message : " . $sent->get_error_message() );
            
		} else {
			$this->addLog( "Message Sent." );
			$sent = true;
		}
		
		
        $this->currentAlertData = null;
        
        return $sent;
    }
    
    
    /**
     * Set email content type 
	 * 
     * @param string $content_type
	 * 
     * @return string
     */
    public function set_content_type( $content_type ) {
        return 'text/html';
    }
    
}