<?php 

if( ! class_exists( 'Most_Recent_Tickets_Widget' ) ) {

require_once( 'base/tickets-report-base-widget.php' );

class Most_Recent_Tickets_Widget extends Base_Widget {

	private $tickets_number = 5;

	/**
	 * Check if the user has the permissions to view this widget
	 *
	 * @param $widget_slug
	 * @param $widget_title
	 *
	 * @return void
	 * @since 0.1.0
	*/
	public function __construct( $widget_slug, $widget_title ) {
		if( $this->should_show_widget() ) {
			wp_add_dashboard_widget(
		        $widget_slug,
		        '<i class="fa fa-ticket" aria-hidden="true"></i> ' . $widget_title,
				array( $this, 'register_most_recent_tickets_widget' )
	    	);
		}
	}

	/**
	 * Set the option for recent tickets number
	 *	
	 * @return void
	 * @since 0.1.0
	*/
	private function set_recent_tickets_num_option( $tickets_num ) {
		$current_user_id = get_current_user_id();
		$option_value = wpas_get_option( 'recent_tickets_num', array() );
		$option_value[$current_user_id] = $tickets_num;
		wpas_update_option( 'recent_tickets_num', $option_value, true );
	}

	/**
	 * Get the number of tickets to show for the current user
	 *	
	 * @return int
	 * @since 0.1.0
	*/
	private function get_recent_tickets_num() {
		$tickets_num_option = wpas_get_option( 'recent_tickets_num', array() );
		
		$current_user_id = get_current_user_id();
		$tickets_num = $this->tickets_number;
		if( array_key_exists( $current_user_id, $tickets_num_option ) ){
			$tickets_num = $tickets_num_option[$current_user_id];						
		}
		/** 
		 * If the $tickets_num is negative, intercept the value and set to default
		 * This is to avoid large query and loops that will slows down the dashboard page load	
		*/
		if( $tickets_num < 0 ){
			return $this->tickets_number;
		} else {
			return $tickets_num;
		}		
	}

	/**
	 * Get the number of tickets to show
	 *	
	 * @return int
	 * @since 0.1.0
	*/
	private function get_tickets_to_show_num() {
		if( array_key_exists( 'tickets_num', $_GET ) ) {
			$tickets_num = (int)$_GET['tickets_num'];
			$this->set_recent_tickets_num_option( $tickets_num );
		} else {
			$tickets_num = $this->get_recent_tickets_num();
		}

		return (int)$tickets_num;
	}

	/**
	 * The callback for the dashboard widget
	 *
	 * @return void
	 * @since 0.1.0
	*/
	public function register_most_recent_tickets_widget() {
		$tickets_to_show = $this->get_tickets_to_show_num();
		$args = array(
			'posts_per_page' => $tickets_to_show,
		);
		$current_user_id = get_current_user_id();
		$tickets = $this->should_get_all_tickets() 
			? wpas_get_tickets( 'open', $args ) 
			: $this->get_agent_tickets( 'open',  $current_user_id, $args );

		$this->get_template( 'most-recent-tickets', $tickets ); 
	}

}

}
