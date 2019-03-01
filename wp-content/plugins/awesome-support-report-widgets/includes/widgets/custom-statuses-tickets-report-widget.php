<?php 

if( ! class_exists( 'Custom_Statuses_Tickets_Report_Widget' ) ) {

require_once( 'base/tickets-report-base-widget.php' );

class Custom_Statuses_Tickets_Report_Widget extends Tickets_Report_Base_Widget{
	//private $ticket_status;

	/**
	 * Check if the user has the permissions to view this widget
	 *
	 *@param $ticket_status
	 *@param $widget_slug
	 *@param $widget_title
	 *
	 * @return void
	 * @since 0.1.0
	*/
	public function __construct( $ticket_status, $widget_slug, $widget_title ) {
		if( $this->should_show_widget() ) {
			parent::__construct( $ticket_status, $widget_slug, $widget_title );
			$this->ticket_status = $ticket_status;

			if ( 'custom_statuses_open_tickets_report' === $widget_slug ) {
				wp_add_dashboard_widget(
					$widget_slug,
					'<i class="fa fa-ticket" aria-hidden="true"></i> ' . $widget_title,
					array( $this, 'register_custom_statuses_tickets_report_widget' )
				);
			}

			if ( 'custom_statuses_open_tickets_chart_report' === $widget_slug ) {
				wp_add_dashboard_widget(
					$widget_slug,
					'<i class="fa fa-ticket" aria-hidden="true"></i> ' . $widget_title,
					array( $this, 'register_custom_statuses_tickets_report_chart_widget' )
				);
			}
		}

	}

	/**
	 * Get all tickets for a single status
	 *
	 *@param $status - the status of the ticket open/closed
	 *
	 * @return int
	 * @since 0.1.0
	*/
	private function get_tickets_for_status( $status ){
		if ( empty( $status) ) {
			return;
		}

		$args = array(
			'posts_per_page' => -1,
			'post_status' => $status,
		);
//		$tickets = wpas_get_tickets	( $this->ticket_status, $args );
		$tickets = $this->get_tickets_by_agent_helper( $args ) ;
		return count( $tickets ); 
	}

	/**
	 * Get tickets for each status 
	 *
	 * @return array
	 * @since 0.1.0
	*/
	private function get_tickets_by_status() {
		$statuses = wpas_get_post_status();

		$tickets_by_status = array();
		foreach ( $statuses as $status => $label ) {
			$tickets_for_status = $this->get_tickets_for_status( $status );
			$tickets_by_status[$label] = $tickets_for_status;
		}

		return $tickets_by_status;
	}

	/**
	 * The callback for the text dashboard widget
	 *
	 * @return void
	 * @since 0.1.0
	*/
	public function register_custom_statuses_tickets_report_widget( ) {
			$tickets = $this->get_tickets_by_status();
			$this->get_template( 'custom-statuses', $tickets, false, 'custom-status-summary' );		
	}
	
	/**
	 * The callback for the chart dashboard widget
	 *
	 * @return void
	 * @since 0.1.0
	*/
	public function register_custom_statuses_tickets_report_chart_widget( ) {
		$tickets = $this->get_tickets_by_status();
		$this->get_template( 'single-chart', $tickets, false, 'custom-status-summary-chart' );
	}

}

}
