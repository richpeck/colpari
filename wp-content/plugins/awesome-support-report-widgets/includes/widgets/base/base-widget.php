<?php 
if( ! class_exists( 'Base_Widget' ) ) {

class Base_Widget {
	/**
	 * Get tickets assigned to agent
	 *
	 * @since 0.1.0
	 *
	 * @param string  $status - The status of the tickets to be fetched
	 * @param int $wpas_agend_id - The id of the user asignee
	 *
	 * @return array - Tickets or and empty array
	*/
	protected function get_agent_tickets( $status, $wpas_agent_id, $additional_args = array() ) {

		$args = array();
		$args['meta_query'][] = array(
				'key'     => '_wpas_assignee',
				'value'   => $wpas_agent_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
		);
		$args = wp_parse_args( $additional_args, $args );

		return wpas_get_tickets( $status, $args );

	}


	/**
	 * Check if current user is admin or WPAS Admin
	 * 
	 * @since 0.1.0
	 * @return bool
	*/
	protected function is_wpas_admin() {
		return current_user_can( 'view_all_tickets' ) || current_user_can( 'administrator' ) || current_user_can( 'administer_awesome_support' );
	}

	/**
	 * Check if the user has the enough permissions to get reports for all tickets
	 * 
	 * @since 0.1.0
	 * @return bool
	*/
	protected function should_get_all_tickets() {
		return $this->is_wpas_admin() || (bool)wpas_get_option( 'agent_see_all' ) === true;
	}

	/**
	 * Check if the current user has the permission to view a widget
	 * 
	 * @since 0.1.0
	 * @return bool
	*/
	protected function should_show_widget() {
		return current_user_can( 'edit_ticket' ) || $this->is_wpas_admin();
	}

	protected function get_template( $template_name, $tickets = array(), $single = false, $template_for='' ) {
		require( WPASRW_PATH . 'includes/widgets/templates/' . $template_name . '.php'  );
	}

}

}