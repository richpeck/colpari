<?php 

if( ! class_exists( 'Tickets_Report_Base_Widget' ) ) {

require_once( 'base-widget.php' );

class Tickets_Report_Base_Widget extends Base_Widget {
	protected $ticket_status;
	protected $widget_slug;
	protected $widget_title;

	/**
	 * Set the variables for the widget
	 *
	 *@param $ticket_status
	 *@param $widget_slug
	 *@param $widget_title
	 *
	 * @return void
	 * @since 0.1.0
	*/
	public function __construct( $ticket_status = 'open', $widget_slug = "open_tickets_report", $widget_title = "Open tickets" ) {
		$this->ticket_status = $ticket_status;
		$this->widget_slug = $widget_slug;
		$this->widget_title = $widget_title;
	}

	/**
	 * Get the timediff from current time to specified time
	 *
	 * @since 0.1.0
	 *
	 * @param $time - the time to get the timediff to
	 * @return float
	*/
	protected function get_post_diff_days( $time ) {
		$diff_seconds = current_time( 'timestamp' ) - $time; 
		$diff_days =  $diff_seconds / DAY_IN_SECONDS;
		return round( $diff_days );
	}

	/**
	 * Get tickets report for the past 5 days
	 *
	 * @since 0.1.0
	 *
	 * @param $tickets - the tickets to get report for
	 * @return array
	*/
	protected function get_tickets_report_by_date_short( $tickets ) {
		
		if( empty( $tickets ) || ! is_array( $tickets ) ) {
			return;
		}
		
		$ticket_report = array(
			'today' => 0,
			'1day' => 0,
			'2days' => 0,
			'3days' => 0,
			'4days' => 0,
			'5days' => 0,
			'5days_up' => 0,
		);

		foreach ( $tickets as $ticket ) {
			$days_ago = $this->get_ticket_days_ago( $ticket->ID );
			
			switch ( $days_ago ) {
				case 0:
					$ticket_report['today']++;
					break;
				case 1:
					$ticket_report['1day']++;
					break;
				case 2:
					$ticket_report['2days']++;
					break;
				case 3:
					$ticket_report['3days']++;
					break;
				case 4:
					$ticket_report['4days']++;
					break;
				case 5:
					$ticket_report['5days']++;
					break;
				default:
					$ticket_report['5days_up']++;
					break;
			}
			
		}
		return $ticket_report;
		
	}

	/**
	 * Get tickets report for the past 2 years
	 *
	 * @since 0.1.0
	 *
	 * @param $tickets - the tickets to get report for
	 * @return array
	*/
	protected function get_tickets_report_by_date_long( $tickets ) {
		
		if( empty( $tickets ) || ! is_array( $tickets ) ) {
			return;
		}

		$tickets_report = array(
			'today' => 0,
			'yesterday' => 0,
			'this_week' => 0,
			'last_week' => 0,
			'this_month' => 0,
			'last_month' => 0,
			'this_year' => 0,
			'last_year' => 0,
			'all_time' => 0,
		);
		$two_years_ago = date('Y') - 10;
		$all_time_timestamp = mktime( 0, 0, 0, 01, 01, $two_years_ago );

		$dates = array(
			'now' => strtotime( "now" ),
			'today' => strtotime( "today" ),
			'yesterday' => strtotime( "yesterday" ),
			'this_week' => strtotime( "this week" ),
			'this_week_cutoff' => strtotime( "last sunday 00:00:00" ),
			'last_week' => strtotime( "last week" ),
			'last_week_cutoff' => strtotime( "-1 week" ),
			'this_month' => strtotime( "first day of this month 00:00:00" ),
			'last_month' => strtotime( "first day of last month 00:00:00" ),
			'this_year' => strtotime( "this year" ),
			'last_year' => strtotime( "last year" ),
			'all_time' => $all_time_timestamp,
		);

		foreach ( $tickets as $ticket ) {
			if( ! empty( $ticket ) ) {
				$time_closed = (int)$this->get_ticket_time( $ticket->ID );

				/** 
				 * Today ( using timestamp )
				 * If the closed date < now && closed date > today(cut-off)
				*/
				if( ( $time_closed < $dates['now'] ) && ( $time_closed > $dates['today'] ) ) {
					/** 
					 * We only use $dates['today'] for comparing the yesterday
					 * and we use $dates['now'] as cut-off to get today
					*/
					$tickets_report['today']++;
				}
				
				/** 
				 * Yesterday ( using timestamp )
				 * If the closed date < today && closed date > yesterday
				*/
				if( ( $time_closed < $dates['today'] ) && ( $time_closed > $dates['yesterday'] ) ) {
					$tickets_report['yesterday']++;
				}
				
				/** 
				 * This week ( using timestamp )
				 * If the closed date < now && closed date > this week cut-off( first sunday )
				*/
				if( $time_closed < $dates['now'] && $time_closed > $dates['this_week_cutoff'] ) {
					$tickets_report['this_week']++;
				} 
				
				/** 
				 * Last week ( using timestamp )
				 * If the closed date > last week cut-off( first sunday of last week ) && closed date < this week cut off( first sunday )
				*/
				if( $time_closed > $dates['last_week_cutoff'] && $time_closed < $dates['this_week_cutoff'] ) {
					$tickets_report['last_week']++;
				} 
				
				/** 
				 * This month ( using timestamp )
				 * If the closed date > this month
				*/
				if( $time_closed > $dates['this_month'] ) {
					$tickets_report['this_month']++;
				} 
				
				/** 
				 * Last Month
				 * If the closed year date > last month && < this month 
				*/
				if( ( $time_closed > $dates['last_month'] ) && ( $time_closed < $dates['this_month'] ) ) {
					$tickets_report['last_month']++;
				} 
				
				/** Get year date() format for comparison */
				$last_year = date( "Y", $dates['last_year'] );
				$this_year = date( "Y", $dates['this_year'] );
				
				/** Time closed year date() format */
				$time_closed_year = date( "Y", $time_closed );

				
				/** 
				 * This Year
				 * If the closed year YYYY === this year YYYY 
				*/
				if( $time_closed_year === $this_year ) {
					$tickets_report['this_year']++;
				} 
				
				/** 
				 * Last Year
				 * If the closed year YYYY <= last year YYYY
				*/
				if( $time_closed_year <= $last_year ) {
					$tickets_report['last_year']++;
				}
				
				$tickets_report['all_time']++;
				
			}
			
		}
		
		return $tickets_report;
		
	}

	/**
	 * Sort multidimensional associative array by its sub-arrays sizes
	 *
	 * @since 0.1.0
	 *
	 * @param $tickets - the tickets to get report for
	 * @return array
	*/
	protected function sort_tickets( &$tickets ) {
		uasort( $tickets, function( $tickets1, $tickets2) {
			return ( count( $tickets1 ) - count( $tickets2 ) );
		} );
	}

	/**
	 * Get the time when a ticket was opened or closed
	 * If the ticket was closed check if the _ticket_closed_on is not empty and used
	 * otherwise fallback of post's last modified time
	 *
	 * @since 0.1.0
	 *
	 * @param $ticket_id
	 * @return array
	*/
	protected function get_ticket_time( $ticket_id ) {
		$ticket_status = wpas_get_ticket_status( $ticket_id );
		$ticket_time = null;
		if( $ticket_status == 'closed' ) {
			$ticket_time = get_post_meta( $ticket_id, '_ticket_closed_on_gmt', true );
			if( empty ( $ticket_time ) ){
				$ticket_time = get_post_meta( $ticket_id, 'wpas_ticket_closed_time', true );
			}
		}
		//return ! empty( $ticket_time ) ? strtotime( $ticket_time ) : get_post_modified_time( 'U', false, $ticket_id );
		return ! empty( $ticket_time ) ? strtotime( $ticket_time ) : get_post_time( 'U', false, $ticket_id );
	}

	/**
	 * Get how many days ago the ticket was closed
	 * If ticket status is closed and the meta for the time when the ticket was closed is set use it
	 * otherwise fallback post time 
	 * 
	 * @since 0.1.0
	 *
	 * @param $ticket_id
	 * @return array
	*/
	protected function get_ticket_days_ago( $ticket_id ) {
		$ticket_time = $this->get_ticket_time( $ticket_id );
		return $this->get_post_diff_days( $ticket_time );
	}
	
	/**
	 * Takes a set of wp_query args related to tickets and returns
	 * the set of matching tickets.  However, the set is automatically 
	 * resricted based on the logged in user.
	 * 
	 * @since 2.0.4
	 *
	 * @param $args array of ticket query arguments (formatted for wp_query)
	 * @return array array of tickets
	 * 
	*/	
	protected function get_tickets_by_agent_helper( $args ) {
		
		$current_user_id = get_current_user_id();
		if( $this->should_get_all_tickets() ) {
			// return all tickets
			return wpas_get_tickets( $this->ticket_status, $args );			
		} else {
			// reuturn tickets just for the logged in agent
			return $this->get_agent_tickets( $this->ticket_status, $current_user_id, $args );
		}		
		
	}
	
	/**
	 * Utility function that takes a ticket query arg and modifies it 
	 * so that the query only returns tickets for the logged in agent.
	 * 
	 * @since 2.0.4
	 *
	 * @param $args array of ticket query arguments (formatted for wp_query)
	 * @return array array of ticket query arguments with logged in agent restriction (formatted for wp_query)
	 * 
	 * @TODO: This function needs to be deleted - no longer in use.  Use the get_agent_tickets() function located in base-widget.php instead.
	*/
	protected function restrict_ticket_query_by_agent( $args ) {
		
		// Figure out if we need to restrict by agent/current logged in user
		$args2 = array();
		if ( ! current_user_can( 'administer_awesome_support' ) ) {
			$args2 = array (
				'meta_query' => array(
					array(
						'key'     => '_wpas_assignee',
						'value'   => get_current_user_id()
					)
				)
			);
		}
		
		// Merge the two arrays
		$args = array_merge( $args, $args2 ) ;
		
		return $args ;
		
	}

}

}