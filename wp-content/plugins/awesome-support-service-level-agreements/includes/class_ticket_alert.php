<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}


/**
 * Class to handle queued alerts and sla_ticket_alerts table
 */
class SLA_Ticket_Alert  {
	
	private static $table = 'sla_ticket_alerts';
    
    public  $id,
			$sla_id,
			$ticket_id,
			$alert_id,
			$alert_due_time,
			$time,
			$sent,
			$process_id,
			
			$ticket_due_time,
	
			$key;

    /**
	 * construction method
	 * 
	 * @param type $args
	 */
    public function __construct( $args = array() ) {
		
		foreach( $args as $key => $val ) {
            if( property_exists( $this, $key ) ) {
                $this->{$key} = $val;
            }
        }
		
		
		
		if( !$this->ticket_due_time ) {
			$this->ticket_due_time = get_post_meta( $this->ticket_id, '_wpas_due_date', true );
		}
		
		if( !$this->process_id ) {
			$this->process_id = $this->getProcessID();
		}
		
		
		
		
		if( !$this->alert_due_time ) {
			$this->setAlertDueTime();
		}
		
		$this->key = $this->getKey();
		
		
	}
	
	
	/**
	 * Set alert due time
	 */
	public function setAlertDueTime() {
		
		if( $this->ticket_due_time ) {
			
			$ticket_due_time = DateTime::createFromFormat( 'Y-m-d H:i:s', $this->ticket_due_time );
			
			
			
			$ticket_due_time->modify( "-{$this->time} minutes" );
			$this->alert_due_time = $ticket_due_time->format('Y-m-d H:i:s');
			
		}
	}

	/**
	 * Return unique alert key
	 * 
	 * @return string
	 */
	public function getKey() {
		return "{$this->process_id}.{$this->sla_id}.{$this->alert_id}";
	}
	
	/**
	 * Return alerts process id
	 * 
	 * @return string
	 */
	public function getProcessID() {
		
		
		$process_id = get_post_meta( $this->ticket_id, 'sla_alert_process_id', true );
		
		if( !$process_id ) {
			$process_id = wp_generate_password( 6, false, false ) . ".{$this->ticket_id}";
			update_post_meta( $this->ticket_id, 'sla_alert_process_id', $process_id );
		}
		
		return $process_id;
	}
	
	
	/**
	 * Add alerts in queue for all tickets linked to a  post
	 * 
	 * @param int $sla_id
	 */
	public static function set_alerts( $sla_id ) {
		
		$tickets = wpas_sla_get_tickets( $sla_id );
		
		foreach( $tickets as $ticket ) {
			self::set_ticket_alerts( $ticket->ID );
		}
		
	}
	
	/**
	 * Add alerts in queue for a ticket
	 * 
	 * @param int $ticket_id
	 * 
	 * @return void
	 */
	public static function set_ticket_alerts( $ticket_id ) {
		
		$sla_id = get_post_meta( $ticket_id, '_wpas_sla_id', true );
		
		if( !$sla_id ) {
			return;
		}
		
		
		$sla_alerts = maybe_unserialize( get_post_meta( $sla_id, 'sla_alerts', true ) );
		$sla_alerts = $sla_alerts && is_array( $sla_alerts ) ? $sla_alerts : array();
		
		
		$existing_alerts = self::getAll( $sla_id, $ticket_id );
		
		
		foreach( $existing_alerts as $e_alert ) {
			if( !array_key_exists( $e_alert->alert_id , $sla_alerts ) ) {
				unset( $existing_alerts[ $e_alert->key ] );
				$e_alert->delete();
			}
		}
		
		
		foreach( $sla_alerts as $alert_id => $alert ) {
			
			$args = $alert;
			$args['sla_id'] = $sla_id;
			$args['alert_id'] = $alert_id;
			$args['ticket_id'] = $ticket_id;
			$ticket_alert = new self( $args );
			
			if( !array_key_exists( $ticket_alert->key, $existing_alerts ) ) {
				if( $ticket_alert->alert_due_time ) {
					$ticket_alert->add();
				}
			} else {
				$ticket_alert->id = $existing_alerts[ $ticket_alert->key ]->id;
				$ticket_alert->setAlertDueTime();
				$ticket_alert->update();
			}
			
		}
		
	}
	
	
	/**
	 * Return all queued alerts based on sla and ticket id
	 * 
	 * @global object $wpdb
	 * 
	 * @param int $sla_id
	 * @param int $ticket_id
	 * 
	 * @return array
	 */
    public static function getAll( $sla_id, $ticket_id = '' ) {
        global $wpdb;
        
		$table = $wpdb->prefix .  self::$table;
		
		$q = "SELECT * FROM {$table}  WHERE sla_id = %s";
		
		$param_arr = array( 1 => $sla_id );
		
		if( $ticket_id ) {
			$q .= " AND ticket_id = %s";
			$param_arr[] = $ticket_id;
		}
		
		$param_arr[0] = $q;
		
		ksort( $param_arr );
		
        $results = $wpdb->get_results( call_user_func_array( array( $wpdb, 'prepare' ), $param_arr ) , ARRAY_A );
        
        
        $alerts = array();
        foreach( $results as $r ) {
			
			$alert = new self( $r );
			$alerts[ $alert->key ] = $alert;
		}
        
        
        return $alerts;
    }
    
    
    /**
     * Update existing queued alert
	 * 
     * @global object $wpdb
	 * 
     * @return int|boolean
     */
    public function update() {
        global $wpdb;
        
		$data = array( 'alert_due_time'	=> $this->alert_due_time );
		$format = array( '%s' );
		$where = array( 'id' => $this->id );
		$where_format = array( '%d' );
		
        
        return $wpdb->update( $wpdb->prefix . self::$table, $data, $where , $format, $where_format );

    }
    
    
    /**
     * Delete queued alert
	 * 
     * @global object $wpdb
	 * 
     * @return int|boolean
     */
    public function delete() {
        global $wpdb;
        return $wpdb->delete( $wpdb->prefix . self::$table, array( 'id' => $this->id ), array( '%d' ) );
    }
    
    /**
     * Add new alert in queue
	 * 
     * @global object $wpdb
	 * 
     * @return int
     */
    public function add() {
        global $wpdb;
		
		
		$data = array(
			'sla_id'			=> $this->sla_id,
			'ticket_id'			=> $this->ticket_id,
			'alert_id'			=> $this->alert_id,
			'alert_due_time'	=> $this->alert_due_time,
			'process_id'		=> $this->process_id 
		);
		
		$format = array( '%d', '%d', '%s', '%s', '%s' );
		
		if( $wpdb->insert( $wpdb->prefix . self::$table, $data, $format ) ) {
			return $wpdb->insert_id;
		}
		
		return '';
    }
	
}