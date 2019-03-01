<?php 

require_once( 'base/tickets-report-base-widget.php' );

if( ! class_exists( 'Agent_Tickets_Report_Widget' ) ) {


class Agent_Tickets_Report_Widget extends Tickets_Report_Base_Widget {

	private $user_id;

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
		if( $this->is_wpas_admin() ) {
			parent::__construct( $ticket_status, $widget_slug, $widget_title );

	    	$this->user_id = get_current_user_id();
			
			if ( 'agent_open_tickets_report' === $widget_slug || 'agent_closed_tickets_report' === $widget_slug  ) {
				wp_add_dashboard_widget(
					$widget_slug,
					'<i class="fa fa-ticket" aria-hidden="true"></i> ' . $widget_title,
					array( $this, 'register_agent_tickets_report_widget' )
				);
			}
			
			if ( 'agent_open_tickets_chart_report' === $widget_slug ) {
				wp_add_dashboard_widget(
					$widget_slug,
					'<i class="fa fa-ticket" aria-hidden="true"></i> ' . $widget_title,
					array( $this, 'register_agent_tickets_summary_chart_widget' )
				);
			}			
			
		}
	}

	/**
	 * Get all tickets asignees
	 *
	 * @since 0.1.0
	 *
	 * @return array of asignees ids
	*/
	private function get_tickets_assignees() {
		global $wpdb;
		$sql = "
			SELECT DISTINCT meta_value AS ID
			FROM {$wpdb->prefix}postmeta
			WHERE meta_key = '_wpas_assignee'
		";
		$assignees = $wpdb->get_results( $sql );
		return $assignees;
	}


	/**
	 * Get each agent and the assigned tickets
	 *
	 * @since 0.1.0
	 *
	 * @return array
	*/
	private function get_agents_tickets() {
		$agents = $this->get_tickets_assignees();
		$agents_tickets = array();

		foreach( $agents as $agent ) {
			$tickets = $this->get_agent_tickets( $this->ticket_status, $agent->ID );
			$report_by_date = $this->ticket_status == 'open' 
				? $this->get_tickets_report_by_date_short( $tickets ) 
				: $this->get_tickets_report_by_date_long( $tickets );
			
			if( ! empty( $report_by_date ) ){
				$userdata = get_userdata( $agent->ID );
				$deleted_agents = wpas_get_option( '_deleted_agents');
				if( ! empty( $userdata ) ) {
					//$agents_tickets[$userdata->user_nicename] = $report_by_date;
					$agents_tickets[$userdata->display_name] = $report_by_date;
				} elseif( array_key_exists( $agent->ID, $deleted_agents ) ) {
					$deleted_agent_nicename = $deleted_agents[$agent->ID];
					$agents_tickets[$deleted_agent_nicename] = $report_by_date;
				} else {
					$agents_tickets[$agent->ID] = $report_by_date;
				}			
			}
			
		}
		return $agents_tickets;
	}

	/**
	 * The callback for the dashboard widget - individual panes for each agent
	 *
	 * @return void
	 * @since 0.1.0
	*/
	public function register_agent_tickets_report_widget() {
		$tickets = $this->get_agents_tickets();
		$this->sort_tickets( $tickets );
		
		$this->ticket_status == 'open' 
			? $this->get_template( 'open-tickets', $tickets, false, 'agent' )
			: $this->get_template( 'closed-tickets', $tickets );	
	}
	
	/**
	 * The callback for the dashboard widget - summary chart pane for agents on open tickets only
	 *
	 * @return void
	 * @since 0.1.0
	*/
	public function register_agent_tickets_summary_chart_widget() {
		$tickets = $this->get_agents_tickets();
		$this->sort_tickets( $tickets );
		
		$this->get_template( 'single-chart', $tickets, false, 'agent-summary-chart' );

	}

}

}
