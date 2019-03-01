<?php 

if( ! class_exists( 'Channel_Tickets_Report_Widget' ) ) {

require_once( 'base/tickets-report-base-widget.php' );

class Channel_Tickets_Report_Widget extends Tickets_Report_Base_Widget {

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

			if ( 'channel_open_tickets_report' === $widget_slug || 'channel_closed_tickets_report' === $widget_slug  ) {
				wp_add_dashboard_widget(
					$widget_slug,
					'<i class="fa fa-ticket" aria-hidden="true"></i> ' . $widget_title,
					array( $this, 'register_channel_tickets_report_widget' )
				);
			}
			
			if ( 'channel_open_tickets_chart_report' === $widget_slug ) {
				wp_add_dashboard_widget(
					$widget_slug,
					'<i class="fa fa-ticket" aria-hidden="true"></i> ' . $widget_title,
					array( $this, 'register_channel_tickets_summary_chart_widget' )
				);
			}			
	    }
	}
	
	/**
	 * Get the ids of channels
	 *
	 * @return array
	 * @since 0.1.0
	*/
	private function get_channel_ids() {
		$channel_ids = array();
		if( taxonomy_exists( 'ticket_channel' ) ) {
			$args = array(
				'fields' => 'ids',
			);
			$channel_ids = get_terms( 'ticket_channel', $args );
		}
		return $channel_ids;
	}

	/**
	 * Get tickets for single channel
	 *
	 * @param $channel_id - the id of the channel to get tickets for
	 *
	 * @return array
	 * @since 0.1.0
	*/
	private function get_channel_tickets( $channel_id ) {
		$args = array(
			'tax_query' => array(
				array(	
					'taxonomy' => 'ticket_channel',
					'field' => 'id',
					'terms' => $channel_id,
				),
			),
		);
		return $this->get_tickets_by_agent_helper( $args ) ;
	}

	/**
	 * Get report for single channel
	 *
	 * @param $channel_tickets - Array of the tickets for channel
	 *
	 * @return array
	 * @since 0.1.0
	*/
	private function get_channel_tickets_report( $channel_tickets ) {
		return $this->ticket_status == 'open'
				? $this->get_tickets_report_by_date_short( $channel_tickets ) 
				: $this->get_tickets_report_by_date_long( $channel_tickets );
	}

	/**
	 * Get tickets for each currently existing channel, that hasn't been deleted.
	 *
	 * @param $channel_ids - the ids of the channels to get tickets for
	 *
	 * @return array
	 * @since 0.1.0
	*/
	private function get_existing_channel_tickets( $channel_ids ) {
		$tickets = array();
		if( ! empty( $channel_ids ) ) {	
			foreach( $channel_ids as $channel_id ){
				$channel_tickets = $this->get_channel_tickets( $channel_id );
				$channel_tickets_report = $this->get_channel_tickets_report( $channel_tickets );
				
				$channel = get_term_by( 'id', $channel_id, 'ticket_channel' );  
				if ( empty( $channel ) ) {
					$channel = get_term( $piority_id );		// Just in case nothing was returned in the prior call, try again...
				}				
				
				$tickets[$channel->name] = $channel_tickets_report;
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
	private function get_deleted_channels() {
		$tickets = array();
		// do nothing - this is placeholder for maybe later use
		return $tickets;
	}

	/**
	 * Get tickets for each channel.
	 *
	 * @param $channel_ids - the ids of the channels to get tickets for
	 *
	 * @return array
	 * @since 0.1.0
	*/
	private function get_channels_tickets( $channel_ids ) {
		$tickets = $this->get_existing_channel_tickets( $channel_ids );
		$deleted_channels = $this->get_deleted_channels();
		$tickets = wp_parse_args( $deleted_channels, $tickets );
		return $tickets;
	}

	/**
	 * The callback for the dashboard widget - individual panes for each channel
	 *
	 * @return void
	 * @since 0.1.0
	*/
	public function register_channel_tickets_report_widget() {
		$ids = $this->get_channel_ids();
		$tickets = $this->get_channels_tickets( $ids );
		$this->ticket_status == 'open' 
			? $this->get_template( 'open-tickets', $tickets, false, 'ticket_channel' ) 
			: $this->get_template( 'closed-tickets', $tickets );
	}
	
	/**
	 * The callback for the dashboard widget - summary chart pane for channels on open tickets only
	 *
	 * @return void
	 * @since 0.1.0
	*/
	public function register_channel_tickets_summary_chart_widget() {
		$ids = $this->get_channel_ids();
		$tickets = $this->get_channels_tickets( $ids );
		$this->get_template( 'single-chart', $tickets, false, 'channel-summary-chart' ); 
	}
}

}