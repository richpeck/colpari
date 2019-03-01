<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Calculate ticket due date
 */

class WPAS_SLA_DUE_DATE {
	
	private $time_frame,
			$ticket_time,
			$ticket_date,
			$remaining_day_time,
			$start_type,
			$workdays;
	
	
	/**
	 * Construct function
	 * 
	 * @param int $time_frame
	 * @param string $ticket_time
	 */
	public function __construct( $time_frame, $ticket_time ) {
		
		$this->time_frame = $time_frame * 60;
		$this->ticket_time = $ticket_time;
		$this->ticket_date = date( SLA_DATE_FORMAT , strtotime( $this->ticket_time ) );
		$this->workdays = wpas_sla_get_workday_settings();
		
		
		$_time = $this->get_date( $this->ticket_time, false, true );
		
		
		$this->remaining_day_time = $this->week_day_time( $this->ticket_date, 'end' ) - ( $_time->format('H') * 60* 60 + $_time->format('i') * 60 + $_time->format('s') );
	}
	
	
	public static function get_date( $date, $midnight = true, $datetime = false ) {
		
		$format = SLA_DATE_FORMAT;
		
		if( $datetime || $midnight ) {
			$format .= ' H:i:s';
		}
		
		if( $midnight && !preg_match("/\d+:\d+:\d+/", $date ) ) {
			$date .= " 00:00:00";
		}
		
		
		return DateTime::createFromFormat( $format, $date );
		
	}
	
	/**
	 * Calculate due date
	 * 
	 * @return string
	 */
	public function calculate() {
		
		// First get adjusted start time
		$adjusted_start_time = $this->adjusted_start_time();
		
		
		$time_to_count = $this->time_frame;
		
		$counted_date = $adjusted_start_time;
		
		
		$moved_to_next_day = ( 'next_day' === $this->start_type ) ? true : false;
		
		do {
			$workday_time = $this->weekday_working_time_count( $counted_date );
			
			
			if( 'next_day' != $this->start_type && !$moved_to_next_day && $this->remaining_day_time ) {
				$workday_time = $this->remaining_day_time;
			}
			
			
			if( $time_to_count - $workday_time > 0 ) {
				$time_to_count = $time_to_count - $workday_time;
				
				$counted_date = $this->get_date( $this->get_next_working_day( $counted_date->format( SLA_DATE_FORMAT ) ), true );
				
				$day_work_start_time = $this->week_day_time( $counted_date, 'start' );
				$counted_date->modify( "+ {$day_work_start_time} seconds" );
				
				
			} else {
				
				if( $moved_to_next_day ) {
					$add_time = $time_to_count;
				} else {
					$add_time = $time_to_count;
					$counted_date = $this->get_date( $this->ticket_time, false, true );
				}
				
				
				$counted_date->modify( "+{$add_time} seconds" );
				$time_to_count = 0;
			}
			
			$moved_to_next_day = true;
			
		} while ( $time_to_count > 0 );
		
		
		return $counted_date->format( SLA_DATE_FORMAT. ' H:i:s' );
		
	}
	
	/**
	 * Return any type of work day time set in settings, ie, work start time, work end time, cutoff time
	 * 
	 * @param Object|String $date
	 * @param string $type
	 * 
	 * @return int
	 */
	function week_day_time( $date, $type = 'start' ) {
		
		if( !is_object( $date ) ) {
			$date = DateTime::createFromFormat( SLA_DATE_FORMAT, $date );
		}
		
		$day = strtolower( $date->format( 'D' ) );
		
		if( 'start' === $type ) {
			$time = $this->workdays[ $day ]['start_time'];
		} elseif( 'end' === $type ) {
			$time = $this->workdays[ $day ]['end_time'];
		} elseif( 'cutoff' === $type ) {
			$time = $this->workdays[ $day ]['cutoff_time'];
		}
		
		return $time;
	}
	
	/**
	 * Return work day time in seconds between start and end time
	 * 
	 * @param Object|String $date
	 * 
	 * @return int
	 */
	function weekday_working_time_count( $date ) {
		
		if( !is_object( $date ) ) {
			$date = DateTime::createFromFormat( SLA_DATE_FORMAT, $date );
		}
		
		$start_time = $this->week_day_time( $date, 'start' );
		$end_time = $this->week_day_time( $date, 'end' );
		
		$calculated = $end_time - $start_time;
		
		return $calculated;
	}
	
	
	
	/**
	 * Return adjusted start time
	 * 
	 * @return object
	 */
	private function adjusted_start_time() {
		
		
		$start_work_date = '';
		
		$next_day = true;
		
		
		if( $this->is_workday( $this->ticket_date ) ) {
			
			$cutoff_time = $this->week_day_time( $this->ticket_date, 'cutoff' );
			$ticket_time = DateTime::createFromFormat( SLA_DATE_FORMAT . ' H:i:s', $this->ticket_time );
			$ticket_time_sec = (int) $ticket_time->format('H') * 60 * 60 + (int)$ticket_time->format('i') * 60 + (int)$ticket_time->format('s');
			
			if( $ticket_time_sec < $cutoff_time ) {
				$next_day = false;
			}
			
			$start_work_date = $ticket_time;
		}
		
		if( $next_day ) {
			
			$this->start_type = 'next_day';
			$work_date = $this->get_next_working_day( $this->ticket_date );
			
			$start_work_date = DateTime::createFromFormat( SLA_DATE_FORMAT . ' H:i:s', $work_date . ' 00:00:00' );
			$start_time = $this->week_day_time( $work_date, 'start' );
			$start_work_date->modify( "+{$start_time} seconds" );
		}
		
		return $start_work_date;
	}
	
	/**
	 * Find out next working day
	 * 
	 * @param string $date
	 * 
	 * @return object
	 */
	private function get_next_working_day( $date ) {
		
		$is_working_day = false;
		
		
		$_date = DateTime::createFromFormat ( SLA_DATE_FORMAT , $date );
		
		do {
			
			$_date->modify( '+1 day' );
			$is_working_day = $this->is_workday( $_date->format( SLA_DATE_FORMAT ) );
		} while ( !$is_working_day );
		
		return $_date->format( SLA_DATE_FORMAT );
		
	}
	
	/**
	 * Check if a day is holiday
	 * 
	 * @global object $wpdb
	 * 
	 * @param string $date
	 * 
	 * @return boolean
	 */
	private function is_holiday( $date ) {
		global $wpdb;
		
		
		$query = $wpdb->prepare( "SELECT p.ID FROM {$wpdb->posts} p 
			INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = 'holiday_date' 
			WHERE p.post_type = 'wpas_sla_holiday' AND meta_value = '%s'", $date );
			
			
		$post_id = $wpdb->get_var( $query );
			
			
		if( $post_id ) {
			return true;
		}
			
		return false;
	}
	
	/**
	 * Check if a day is off day
	 * 
	 * @param string $date
	 * 
	 * @return boolean
	 */
	private function is_off_day( $date ) {
		
		$_date = DateTime::createFromFormat ( SLA_DATE_FORMAT , $date );
		
		
		$day = strtolower( $_date->format('D') );
		
		
		if( !array_key_exists( $day, $this->workdays ) || !$this->workdays[ $day ]['active'] ) {
			return true;
		}
		
		return false;
		
	}
	
	/**
	 * Check if a day is working day
	 * 
	 * @param string $date
	 * 
	 * @return boolean
	 */
	private function is_workday( $date ) {
		
		if( !$this->is_off_day( $date ) && !$this->is_holiday( $date ) ) {
			return true;
		}
		
		return false;
		
	}
	
}