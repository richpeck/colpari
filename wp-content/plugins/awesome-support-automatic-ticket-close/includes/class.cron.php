<?php

class WPAC_Cron extends WPAC_Object {
    
    
    public $todate;
    
    public $time;
    
    public $warning_messages;
    
    public $statuses;
    
    public $log;
    
    public $currentWarning = null;
    
    public $currentTicket = null;
    
    public $warning_messages_by_status;
    
    public $user = null;
    
    public $cron_username = 'as_auto_close_system_user';
    
    public function __construct($args = array()) {
        parent::__construct($args);
        
        
        $this->warning_messages = WPAC_WarningMessage::getAll();
        
        $statuses = array();
        
        
        
        foreach($this->warning_messages as $wm) {
            $this->warning_messages_by_status[$wm->status][$wm->id] = $wm;
            $statuses[] = $wm->status;
        }
        
        
        $this->statuses = $statuses;
        
        $this->todate = date('Y-m-d');
        
        $this->time = time();
    }
    
    
    
    /**
     * 
     * @return string
     */
    private function query_limit() {
            $cron_recurrence = wpas_get_option('autoclose_cron_recurrence',  'hourly');
            
            $cron_limit = wpas_get_option('autoclose_cron_limit',  '0');
            
            $cron_limit = is_numeric($cron_limit) ? $cron_limit : "0";
            
            
            
            
            if($cron_limit == 0) {
                $this->addLog("No Limit set, we will process all tickets per occurrence");
                return "";
            } elseif($cron_limit == -1) {
                $total_found = $this->count_tickets();
                
                // we need to count tickets per day so we can process each ticket
                if(get_option('autoclose_last_count_date') != $this->todate) {
                    update_option('autoclose_last_count_date', $this->todate);
                    update_option('autoclose_tickets_count', $total_found);
                } else {
                    $total_found = get_option('autoclose_tickets_count');
                }


                $limit = $total_found;

                switch($cron_recurrence) {

                    case 'every5min' :
                        $limit = $total_found / (24 * 12);
                        break;
                    case 'every10min':
                        $limit = $total_found / (24 * 6);
                        break;
                    case 'every20min':
                        $limit = $total_found / (24 * 3) ;
                        break;
                    case 'every30min':
                        $limit = $total_found / (24 * 2);
                        break;
                    case 'hourly':
                        $limit = $total_found / 24;
                        break;
                    case 'every2ndhour':
                        $limit = $total_found / 12;
                        break;
                    case 'every4thhour':
                        $limit = $total_found / 6;
                        break;
                    case 'every6thhour':
                        $limit = $total_found / 4;
                        break;
                    case 'twicedaily':
                        $limit = $total_found / 2;
                        break;
                    default :

                        break;
                }

                $limit = round($limit) + 1;
                $this->addLog("Limit set as auto, we will process {$limit} ticket(s) per occurrence");
                return " LIMIT 0, {$limit}";
            } else {
                $this->addLog("Limit set to {$cron_limit} ticket(s) per occurrence");
                return " LIMIT 0, {$cron_limit}";
            }
    }
    
    /**
     * 
     * @return string
     */
    private function query_statuses_clause() {
        $statuses_clause = array();
        foreach($this->statuses as $s) {
            $statuses_clause[] = "p.post_status = '{$s}'";
        }
        return $statuses_clause;
    }
    
    
    /***
     *
     * @param string $type
     * @param boolean $limit 
     * @return string
     */
    public function query($type = 'results' , $limit = true) {
        global $wpdb;
        $select = 'p.*';
        
        if($type == 'count') {
            $select = 'count(p.ID)';
        }
        
        $statuses_clause = $this->query_statuses_clause();
        
        $q = "SELECT {$select} FROM {$wpdb->prefix}posts p 
                LEFT JOIN {$wpdb->prefix}postmeta AS pm  ON ( p.ID = pm.post_id AND pm.meta_key = '_wpas_status' )
                LEFT JOIN {$wpdb->prefix}postmeta        ON ( p.ID = {$wpdb->prefix}postmeta.post_id AND {$wpdb->prefix}postmeta.meta_key = 'last_autoclose_check_date' )  
                LEFT JOIN {$wpdb->prefix}postmeta AS pm2 ON ( p.ID = pm2.post_id ) WHERE 1=1 AND (
                    {$wpdb->prefix}postmeta.post_id IS NULL
                        OR 
                    ( pm2.meta_key = 'last_autoclose_check_date' AND pm2.meta_value != '{$this->todate}' )
                ) AND (" . implode(' OR ', $statuses_clause) .  ") AND pm.meta_value = 'open' AND p.post_type = 'ticket' GROUP BY p.ID" . (($limit) ? $this->query_limit() : "");
                    
        return $q;
    }
    
    /**
     * 
     * @global object $wpdb
     * @return int
     */
    private function count_tickets() {
        global $wpdb;
        $q = $this->query('count', false);
        return $wpdb->get_var($q);
    }
    
    /**
     * 
     * @global object $wpdb
     * @return array
     */
    private function get_tickets() {
        global $wpdb;
        $q = $this->query();
        $results = $wpdb->get_results($q);
        
        return $results;
    }
    
    /**
     * 
     * @param object $ticket
     * @return object
     */
    private function getLastReply($ticket) {
                
        $replies = $this->get_replies_query( $ticket->ID );
        
        $last_reply = $ticket;
        
        if ( !empty( $replies->posts ) ) {
            $last = $replies->post_count - 1;
            $last_reply = $replies->posts[ $last ];
        }
        
        return $last_reply;
    }
    
    /**
     * 
     * @param type $ticket_id
     * @return \WP_Query
     */
    public function get_replies_query( $ticket_id ) {

	$q = wp_cache_get( 'replies_query_' . $ticket_id, 'wpas' );

	if ( false === $q ) {

		$args = array(
			'post_parent'            => $ticket_id,
			'post_type'              => 'ticket_reply',
			'post_status'            => array( 'unread', 'read' ),
			'posts_per_page'         => - 1,
			'orderby'                => 'date',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'cache_results'          => false,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		);

		$q = new WP_Query( $args );

		// Cache the result
		wp_cache_add( 'replies_query_' . $ticket_id, $q, 'wpas', 600 );

	}

	return $q;

    }
    
    /**
     * 
     * @param object $last_reply
     * @return boolean
     */
    private function isLastReplyFromAgent($last_reply) {
        $agent = new WPAS_Member_Agent( $last_reply->post_author );
        return $agent->is_agent();
    }
    
    /**
     * 
     * @param string $log
     */
    private function addLog( $log, $print = false ) {
        $this->log .= $log . "\n";
	
	if( $print ) {
		$this->print_log();
	}
    }
    
    /**
     * write log into log file
     */
    private function print_log() {
        
        $path = wpac_log_file_dir();

	if ( !is_dir( $path ) ) {
		$dir = mkdir( $path );
		if ( !$dir ) {
			return false;
		}
	}

	
        $file = fopen( wpac_log_file_path(), 'a' );
        
        
	if ( $file && is_resource( $file ) ) {
            $time = date_i18n( 'm-d-Y @ H:i:s -' ); // Grab Time
            fwrite( $file, $time . " " . $this->log . "\n" );
	}
        
    }
    
	public function get_wm_templates_by_status( $status ) {
			    
		if( isset( $this->warning_messages_by_status[ $status ] ) ) {
			return $this->warning_messages_by_status[ $status ];
		}
			    
		return array();
	}
	
	public function get_messages_process_limit() {
		
		 $limit = (int) wpas_get_option( 'autoclose_message_process_limit', '1' );
		 
		 return $limit;
	}
	
	
	public function processed_messages_states( $ticket_id ) {
				
		$sent_messages_data = maybe_unserialize( get_post_meta( $ticket_id, 'ac_sent_wmsgs', true ) );
		
		if( !$sent_messages_data || "" === $sent_messages_data ) {
			$sent_messages_data = array();
		}
				
		return $sent_messages_data;
	}
	
	public function process_messages_dates( $ticket_id, $process_id ) {
		$sent_messages_data = $this->processed_messages_states( $ticket_id );

		$sent_messages_dates = array();
		if( !empty( $sent_messages_data ) ) {
			$sent_messages_dates = isset( $sent_messages_data[ $process_id ] ) ? $sent_messages_data[ $process_id ] : array();
		}
		
		return $sent_messages_dates;
	}
	
	public function get_process_id( $ticket_id ) {
		
		$process_id = get_post_meta( $ticket_id, 'autoclose_process_id', true );
				
		if( !$process_id ) {
			$process_id = wpac_generate_cron_process_id( $ticket_id );
		}
				
		return $process_id;
	}
	
	
	public function maybe_close_ticket( $ticket, $wm ) {
		
		$closed = false;
		
		if( $wm->close ) {
			if( wpac_is_debug_mode_active() ) {
			    $this->addLog( "we won't auto close this ticket in test mode." );
			    $closed = true;
			} else {
			    $this->close_ticket( $ticket );
			    $this->addLog( "Ticket Closed." );
			    $closed = true;
			}
		}
		
		return $closed;
	}
    
	/**
	 * processing tickets for sending warning messages and auto closing
	 */
	public function process() {

		$this->addLog("Starting getting tickets");

		if( empty( $this->warning_messages ) || empty( $this->statuses ) ) {
			$this->addLog( "There is no warning message set", true );
			return;
		} 

		$tickets = $this->get_tickets();

		if( empty( $tickets ) ) {
			$this->addLog( "No ticket require sending warning message.", true );
			return;
		} 

		add_filter( 'wp_mail_content_type', array($this, 'set_content_type') );

		$this->addLog( count( $tickets ) . " Tickets found." );

		$remaining_time_of_day = $this->time - strtotime( $this->todate . ' 00:00:00' );
		$message_process_limit = $this->get_messages_process_limit();

		foreach( $tickets as $ticket ) {

			$is_diff_within_a_day = false;

			$sent_messages_dates = array();

			$this->addLog("Processing Ticket #{$ticket->ID}.");

			$last_reply = $this->getLastReply($ticket);
			if ( true !== $this->isLastReplyFromAgent($last_reply) ) {
				$this->addLog("End processing Ticket # {$ticket->ID} because last response was from customer.");
				continue;
			}

			$warning_messages = $this->get_wm_templates_by_status( $ticket->post_status );

			// Stop processing if no warning message is set with same status as of current processing $ticket
			if( empty( $warning_messages ) ) {
				continue;
			}
			
			$warning_messages_count = count( $warning_messages );

			$last_reply_created_time     = strtotime( $last_reply->post_date );		
			$time_since_last_reply       = $this->time - $last_reply_created_time;		// Time in seconds since last reply was created
			$sent_messages_data          = $this->processed_messages_states( $ticket->ID );
			$process_id		     = $this->get_process_id( $ticket->ID );		// Get process id of current ticket

			if( !empty( $sent_messages_data ) ) {
				$sent_messages_dates = isset( $sent_messages_data[ $process_id ] ) ? $sent_messages_data[ $process_id ] : array();
			}

			$email_sent_count = 0;
			$current_process_warning_messages = 0;
			
			foreach( $warning_messages as $wm ) {
				$current_process_warning_messages++;
				$wm_age_in_seconds = $wm->age * MINUTE_IN_SECONDS;

				$this->addLog( "Time passed from last reply : " . round( $time_since_last_reply / 60 ) . " minutes | Warning message age : {$wm->age} minutes" );
				if ( $time_since_last_reply >= $wm_age_in_seconds ) {

					if( !isset( $sent_messages_dates[ $wm->id ] ) || !is_array( $sent_messages_dates[ $wm->id ] ) ) {
						$sent_messages_dates[ $wm->id ] = array();
					}

					if( empty( $sent_messages_dates[ $wm->id ] ) ) {
						$this->addLog( "Starting sending warning message as customer didn't reply after {$wm->age} Minutes." );
						$this->login_user();
						if( $this->sendMessage( $ticket, $wm ) ) {
							$sent_messages_dates[ $wm->id ][] = time();
							$email_sent_count++;
						}

						$this->maybe_close_ticket( $ticket, $wm );

					} else {
						$this->addLog( "Message was already sent." );
					}
				}
				
				if( !$is_diff_within_a_day && $time_since_last_reply + $remaining_time_of_day >= $wm_age_in_seconds ) {
					$is_diff_within_a_day = true;
				}

				if( $message_process_limit != 0 && $email_sent_count >= $message_process_limit ) {
					$is_diff_within_a_day = true;
					break;
				}
			}

			$sent_messages_data[ $process_id ] = $sent_messages_dates;
			$this->addLog( "End processing Ticket # {$ticket->ID}." );
			update_post_meta( $ticket->ID, 'ac_sent_wmsgs', $sent_messages_data );	    


			if( count( $sent_messages_dates ) === $warning_messages_count ) {
				update_post_meta( $ticket->ID, 'autoclose_course_completed', 'yes' );
			} else {
				update_post_meta( $ticket->ID, 'autoclose_course_completed', 'no' );
			}
			
			if( !$is_diff_within_a_day ) {
				update_post_meta( $ticket->ID, 'last_autoclose_check_date', $this->todate );
			}


			update_post_meta( $ticket->ID, 'last_autoclose_process_date', $this->time );

			$this->addLog("------------------------------------------------------------" );
		}

	    $this->print_log();
	    remove_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
	    $this->logout_user();
	}
    
    
    /**
     * 
     * @param object $ticket
     */
    private function close_ticket($ticket) {
	wpas_close_ticket($ticket->ID);
    }
    
    /**
     * 
     * @param string $subject
     * @param int $ticket_id
     * @return string
     * 
     */
    public function email_notification_subject($subject, $ticket_id) {
        if(null !== $this->currentWarning) {
            $subject = $this->currentWarning->subject ? $this->currentWarning->subject : "Reminder";
        }
        
        return $subject;
    }

    /**
     * 
     * @param string $message
     * @param int $ticket_id
     * @return string
     * 
     */
    public function email_notification_body($message, $ticket_id) {
        
        if(null !== $this->currentWarning) {
            $message = $this->currentWarning->message;
        }
        
        return $message;
    }
    
    /**
     * Set Recipient Email
     * 
     * @param string $email
     * @param string $case
     * @param int $ticket_id
     * @return string
     */
    public function email_notifications_email($email, $case, $ticket_id) {
        
        if(null !== $this->currentTicket) {
            $member = get_user_by('id', $this->currentTicket->post_author);
            $email['recipient_email'] = array( 'user_id' => $member->ID, 'email' => array( $member->user_email ) );
        } 
        
        return $email;
    }
    
    /**
     * 
     * @param object $ticket
     * @param object $warn
     * @return int
     */
    private function sendMessage($ticket, $warn) {
        $note = "";
        
        $this->currentWarning = $warn;
        $this->currentTicket = $ticket;
        
        add_filter( 'wpas_email_notifications_pre_fetch_subject', array($this, 'email_notification_subject') , 99, 2 );
        add_filter( 'wpas_email_notifications_pre_fetch_content', array($this, 'email_notification_body') , 99, 2 );
        add_filter( 'wpas_email_notifications_email', array($this, 'email_notifications_email') , 99, 3 );
        
        $sent = wpas_email_notify($ticket->ID, WPASS_AUTOCLOSE_TEXT_DOMAIN);
        
        remove_filter( 'wpas_email_notifications_pre_fetch_subject', array($this, 'email_notification_subject') , 99);
        remove_filter( 'wpas_email_notifications_pre_fetch_content', array($this, 'email_notification_body') , 99);
        remove_filter( 'wpas_email_notifications_email', array($this, 'email_notifications_email') , 99);
        
        if($sent && !is_wp_error($sent)) {
            $this->addLog("Warning Message Sent.");
            $days = round(($warn->age / 60) / 24);
            $note = "{$this->todate} : A closing ticket warning message was sent to the customer because no replies were received after {$days} Days.";
        } else {
            $this->addLog("Error while sending warning message.");
            
            if(is_wp_error($sent)) {
                $this->addLog("Error Message : " . $sent->get_error_message());
            }
        }
        if($note) {
            wpas_log($ticket->ID, $note);
        }
        
        $this->currentWarning = null;
        $this->currentTicket = null;
        
        return $sent;
    }
    
    
    /**
     * 
     * @param string $content_type
     * @return string
     */
    public function set_content_type($content_type) {
        return 'text/html';
    }
    
    /**
     * auto login cron user
     */
    private function login_user() {
        
        $this->addLog("within login_user fun");
        
        
        if($this->user === null) {
            $user = get_user_by('login', $this->cron_username );
            
            

            if(!($user instanceof WP_User)) {
                $user = $this->add_cron_user();
            }
            
            $this->user = $user;
        }
        
        
        
        // login once
        
        $current_user = wp_get_current_user();
        
        if(!$current_user->ID || $current_user->user_login != $this->cron_username ) {

            wp_clear_auth_cookie();
            wp_set_current_user( $this->user->ID );
            wp_set_auth_cookie( $this->user->ID );
            update_user_caches($this->user);
        }
    }
    
    /**
     * logout cron user
     */
    private function logout_user() {
        if($this->user) {
            wp_logout();
        }
    }
    
    /**
     * register cron user
     * @return object
     */
    private function add_cron_user() {
        
        $user = null;
        
        $password = wp_generate_password( 12, true );
        $user_id = wp_create_user($this->cron_username, $password);
        
        if(!is_wp_error($user_id)) {
            $user = get_user_by('id', $user_id);
            $user->add_cap('close_ticket');
        }
        
        return $user;
    }
    
}