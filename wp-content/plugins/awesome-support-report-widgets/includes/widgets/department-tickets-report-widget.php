<?php 

if( ! class_exists( 'Department_Tickets_Report_Widget' ) ) {

require_once( 'base/tickets-report-base-widget.php' );

class Department_Tickets_Report_Widget extends Tickets_Report_Base_Widget {

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

		if( $this->should_show_widget() ) {
			parent::__construct( $ticket_status, $widget_slug, $widget_title );
			$this->user_id = get_current_user_id();

			if ( 'department_open_tickets_report' === $widget_slug || 'department_closed_tickets_report' === $widget_slug  ) {
				wp_add_dashboard_widget(
					$widget_slug,
					'<i class="fa fa-ticket" aria-hidden="true"></i> ' . $widget_title,
					array( $this, 'register_department_tickets_report_widget' )
				);
			}
			
			if ( 'department_open_tickets_chart_report' === $widget_slug ) {
				wp_add_dashboard_widget(
					$widget_slug,
					'<i class="fa fa-ticket" aria-hidden="true"></i> ' . $widget_title,
					array( $this, 'register_department_tickets_summary_chart_widget' )
				);
			}
	    }
	}
	
	/**
	 * Get the ids of departments
	 *
	 * @return array
	 * @since 0.1.0
	*/
	private function get_department_ids() {
		$department_ids = array();
		if( taxonomy_exists( 'department' ) ) {
			$args = array(
				'fields' => 'ids',
			);
			$department_ids = get_terms( 'department', $args );
		}
		return $department_ids;
	}

	/**
	 * Get tickets for single department
	 *
	 * @param $department_id - the id of the department to get tickets for
	 *
	 * @return array
	 * @since 0.1.0
	*/
	private function get_department_tickets( $department_id ) {
		$args = array(
			'tax_query' => array(
				array(	
					'taxonomy' => 'department',
					'field' => 'id',
					'terms' => $department_id,
				),
			),
		);
		return $this->get_tickets_by_agent_helper( $args ) ;
	}

	/**
	 * Get report for single department
	 *
	 * @param $department_tickets - Array of the tickets for department
	 *
	 * @return array
	 * @since 0.1.0
	*/
	private function get_department_tickets_report( $department_tickets ) {
		return $this->ticket_status == 'open'
				? $this->get_tickets_report_by_date_short( $department_tickets ) 
				: $this->get_tickets_report_by_date_long( $department_tickets );
	}

	/**
	 * Get tickets for each currently existing department, that hasn't been deleted.
	 *
	 * @param $department_ids - the ids of the departments to get tickets for
	 *
	 * @return array
	 * @since 0.1.0
	*/
	private function get_existing_department_tickets( $department_ids ) {
		$tickets = array();
		if( ! empty( $department_ids ) ) {	
			foreach( $department_ids as $department_id ){
				$department_tickets = $this->get_department_tickets( $department_id );
				$department_tickets_report = $this->get_department_tickets_report( $department_tickets );
				
				$department = get_term_by( 'id', $department_id, 'department' );  
				if ( empty( $department ) ) {
					$department = get_term( $piority_id );		// Just in case nothing was returned in the prior call, try again...
				}				
				
				$tickets[$department->name] = $department_tickets_report;
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
	private function get_deleted_departments() {
		$tickets = array();
		// do nothing - this is placeholder for maybe later use
		return $tickets;
	}

	/**
	 * Get tickets for each department.
	 *
	 * @param $department_ids - the ids of the departments to get tickets for
	 *
	 * @return array
	 * @since 0.1.0
	*/
	private function get_departments_tickets( $department_ids ) {
		$tickets = $this->get_existing_department_tickets( $department_ids );
		$deleted_departments = $this->get_deleted_departments();
		$tickets = wp_parse_args( $deleted_departments, $tickets );
		return $tickets;
	}

	/**
	 * The callback for the dashboard widget  - individual panes for each department
	 *
	 * @return void
	 * @since 0.1.0
	*/
	public function register_department_tickets_report_widget() {
		$ids = $this->get_department_ids();
		$tickets = $this->get_departments_tickets( $ids );
		$this->ticket_status == 'open' 
			? $this->get_template( 'open-tickets', $tickets, false, 'department' ) 
			: $this->get_template( 'closed-tickets', $tickets );
	}
	
	/**
	 * The callback for the dashboard widget - summary chart pane for departments on open tickets only
	 *
	 * @return void
	 * @since 0.1.0
	*/
	public function register_department_tickets_summary_chart_widget() {
		$ids = $this->get_department_ids();
		$tickets = $this->get_departments_tickets( $ids );
		$this->get_template( 'single-chart', $tickets, false, 'department-summary-chart' );
	}	
}

}