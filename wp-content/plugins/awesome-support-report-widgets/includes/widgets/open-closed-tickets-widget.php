<?php

if( ! class_exists( 'Open_Closed_Tickets_Widget' ) ) {

// Show report of open closed tickets
require_once( 'base/base-widget.php' );

class Open_Closed_Tickets_Widget extends Base_Widget {

	/**
	 * Register the widget
	 *
	 * @return void
	 * @since 0.1.0
	*/
	public function __construct() {
		if( $this->should_show_widget() ) {
			wp_add_dashboard_widget(
		        'open_closed_tickets',
		        '<i class="fa fa-ticket" aria-hidden="true"></i> ' . __( 'Open/Closed tickets', 'wpas-report-widgets' ),
		        array( $this, 'register_open_closed_tickets_widget' )
	    	);	
		}
		
	}

	/**
	 * The callback for the dashboard widget
	 *
	 * @return void
	 * @since 0.1.0
	*/
	public function register_open_closed_tickets_widget(){
		$current_user_id = get_current_user_id();
		if( $this->should_get_all_tickets() ) {
			$open_tickets = wpas_get_tickets( 'open' );
			$closed_tickets = wpas_get_tickets( 'closed' );	
		} else {
			$open_tickets = $this->get_agent_tickets( 'open', $current_user_id );
			$closed_tickets = $this->get_agent_tickets( 'closed', $current_user_id );
		}
		$tickets = array(
			'open_tickets' => count( $open_tickets ),
			'closed_tickets' => count( $closed_tickets ),
		);
		$this->get_template( 'open-closed-tickets', $tickets ); 
	}

}

}
