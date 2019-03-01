<?php

if( ! class_exists( 'Tickets_Report_Widget' ) ) {

require_once( 'base/tickets-report-base-widget.php' );

class Tickets_Report_Widget extends Tickets_Report_Base_Widget {

	/**
	 * Register the widget
	 *
	 * @return void
	 * @since 0.1.0
	*/
	public function __construct( $ticket_status, $widget_slug, $widget_title ) {
		if( $this->should_show_widget() ) {
			parent::__construct( $ticket_status, $widget_slug, $widget_title );

			wp_add_dashboard_widget(
		        $widget_slug,
		        '<i class="fa fa-ticket" aria-hidden="true"></i> ' . $widget_title,
				array( $this, 'register_tickets_report_widget' )
		    );	
		}
	}

	/**
	 * Get the markup for the widget 
	 *
	 * @return void
	 * @since 0.1.0
	*/
	private function get_widget_markup( $tickets ){
		if( $this->ticket_status == 'open' ) {
			$tickets_report = array( $this->get_tickets_report_by_date_short( $tickets ) );
			$this->get_template( 'open-tickets', $tickets_report, true,'open-tickets' );
		} else {
			$tickets_report = array( $this->get_tickets_report_by_date_long( $tickets ) );
			$this->get_template( 'closed-tickets', $tickets_report, true );
		}

		// return $markup;
	}

	/**
	 * The callback for the dashboard widget
	 * Show report for all tickets if admin is logged in, else show tickets report for the current agent
	 *
	 * @return void
	 * @since 0.1.0
	*/
	public function register_tickets_report_widget(){
		$current_user_id = get_current_user_id();	

		$tickets = $this->should_get_all_tickets() 
			? wpas_get_tickets( $this->ticket_status ) 
			: $this->get_agent_tickets( $this->ticket_status, $current_user_id );
		
	   	$this->get_widget_markup( $tickets );
	}

}

}
