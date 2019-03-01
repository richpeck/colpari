<?php 

if( ! class_exists( 'Priority_Tickets_Report_Widget' ) ) {

require_once( 'base/tickets-report-base-widget.php' );

class Priority_Tickets_Report_Widget extends Tickets_Report_Base_Widget {

	private $user_id;

	/**
	 * Register the widget
  	 *
  	 * @param $ticket_status
	 * @param $widget_slug
	 * @param $widget_title
	 *
	 * @return void
	 * @since 0.1.0
	*/
	public function __construct( $ticket_status, $widget_slug, $widget_title ) {
		$support_priority = (bool)wpas_get_option( 'support_priority' );

		if( $this->should_show_widget() && $support_priority === true ) {
			parent::__construct( $ticket_status, $widget_slug, $widget_title );
			$this->user_id = get_current_user_id();

			if ( 'priority_open_tickets_report' === $widget_slug || 'priority_closed_tickets_report' === $widget_slug  ) {
				wp_add_dashboard_widget(
					$widget_slug,
					'<i class="fa fa-ticket" aria-hidden="true"></i> ' . $widget_title,
					array( $this, 'register_priority_tickets_report_widget' )
				);
			}
			
			if ( 'priority_open_tickets_chart_report' === $widget_slug ) {			
				wp_add_dashboard_widget(
					$widget_slug,
					'<i class="fa fa-ticket" aria-hidden="true"></i> ' . $widget_title,
					array( $this, 'register_priority_tickets_summary_chart_widget' )
				);
			}			
	    }
	}
	
	/**
	 * Get the ids of priorities
	 *
	 * @return array
	 * @since 0.1.0
	*/
	private function get_priority_ids() {
		$priority_ids = array();
		if( taxonomy_exists( 'ticket_priority' ) ) {
			$args = array(
				'fields' => 'ids',
			);
			$priority_ids = get_terms( 'ticket_priority', $args );
		}
		return $priority_ids;
	}

	/**
	 * Get tickets for single priority
	 *
	 * @param $priority_id - the id of the priority to get tickets for
	 *
	 * @return array
	 * @since 0.1.0
	*/
	private function get_priority_tickets( $priority_id ) {
		$args = array(
			'tax_query' => array(
				array(	
					'taxonomy' => 'ticket_priority',
					'field' => 'id',
					'terms' => $priority_id,
				),
			),
		);
		return $this->get_tickets_by_agent_helper( $args ) ;
	}

	/**
	 * Get report for single priority
	 *
	 * @param $priority_tickets - Array of the tickets for priority
	 *
	 * @return array
	 * @since 0.1.0
	*/
	private function get_priority_tickets_report( $priority_tickets ) {
		return $this->ticket_status == 'open'
				? $this->get_tickets_report_by_date_short( $priority_tickets ) 
				: $this->get_tickets_report_by_date_long( $priority_tickets );
	}

	/**
	 * Get tickets for each currently existing priority, that hasn't been deleted.
	 *
	 * @param $priority_ids - the ids of the priorities to get tickets for
	 *
	 * @return array
	 * @since 0.1.0
	*/
	private function get_existing_priority_tickets( $priority_ids ) {
		$tickets = array();
		if( ! empty( $priority_ids ) ) {	
			foreach( $priority_ids as $priority_id ){
				$priority_tickets = $this->get_priority_tickets( $priority_id );
				$priority_tickets_report = $this->get_priority_tickets_report( $priority_tickets );
				
				$priority = get_term_by( 'id', $priority_id, 'ticket_priority' );  
				if ( empty( $priority ) ) {
					$priority = get_term( $piority_id );		// Just in case nothing was returned in the prior call, try again...
				}				
				
				$tickets[$priority->name] = $priority_tickets_report;
			}
		}
		return $tickets;
	}

	/**
	 * Get tickets for each deleted product
	 *
	 * @return array
	 * @since 0.1.0
	*/
	private function get_deleted_priorities() {
		$tickets = array();
		// do nothing - this is placeholder for maybe later use
		return $tickets;
	}

	/**
	 * Get tickets for each priority.
	 *
	 * @param $priority_ids - the ids of the priorities to get tickets for
	 *
	 * @return array
	 * @since 0.1.0
	*/
	private function get_priorities_tickets( $priority_ids ) {
		$tickets = $this->get_existing_priority_tickets( $priority_ids );
		$deleted_priorities = $this->get_deleted_priorities();
		$tickets = wp_parse_args( $deleted_priorities, $tickets );
		return $tickets;
	}

	/**
	 * The callback for the dashboard widget - individual panes for each priority
	 *
	 * @return void
	 * @since 0.1.0
	*/
	public function register_priority_tickets_report_widget() {
		$ids = $this->get_priority_ids();
		$tickets = $this->get_priorities_tickets( $ids );
		$this->ticket_status == 'open' 
			? $this->get_template( 'open-tickets', $tickets, false, 'ticket_priority' ) 
			: $this->get_template( 'closed-tickets', $tickets );
	}
	
	/**
	 * The callback for the dashboard widget - summary chart pane for priority on open tickets only
	 *
	 * @return void
	 * @since 0.1.0
	*/
	public function register_priority_tickets_summary_chart_widget() {
		$ids = $this->get_priority_ids();
		$tickets = $this->get_priorities_tickets( $ids );
		$this->get_template( 'single-chart', $tickets, false, 'priority-summary-chart' ) ;
	}
}

}