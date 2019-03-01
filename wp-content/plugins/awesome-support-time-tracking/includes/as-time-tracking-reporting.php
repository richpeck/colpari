<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Add report page to the Tickets admin menu.
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_add_reporting() {
    add_submenu_page(
			'edit.php?post_type=trackedtimes',
			'Awesome Support - Time Tracking Reports',
			__( 'Time Tracking Reports', 'awesome-support-time-tracking' ),
			'manage_options',
			'trackedtimes-reports',
			'as_time_tracking_reports_output'
		);
}

add_action( 'admin_menu', 'as_time_tracking_add_reporting' );

/**
 * Outputs the report page tabs, filter and content area of the report content.
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_reports_output() {
?>
<div class="wrap nosubsub">
		<h1><?php _e( 'Awesome Support - Time Tracking Reports', 'awesome-support-time-tracking' ); ?></h1>
		<input id="as_time_tracking_report_type_hidden" type="hidden" value="agent" >
		<div id="as_time_tracking_reporting_tabs">
			<h2 class="nav-tab-wrapper">
				<a id="as_time_tracking_agent_tab" class="nav-tab nav-tab-active" href="#"><?php _e( 'Agent Report', 'awesome-support-time-tracking' ); ?></a>
				<a id="as_time_tracking_client_tab" class="nav-tab" href="#"><?php _e( 'Client/Support User Report', 'awesome-support-time-tracking' ); ?></a>
				<a id="as_time_tracking_ticket_tab" class="nav-tab" href="#"><?php _e( 'Ticket Report', 'awesome-support-time-tracking' ); ?></a>
				<a id="as_time_tracking_invoice_tab" class="nav-tab" href="#"><?php _e( 'Invoice Report', 'awesome-support-time-tracking' ); ?></a>
			</h2>
		</div>

		<div id="as_time_tracking_report_container">
			<div class="as_time_tracking_filter_list">
				<p><strong><?php _e( 'Filter:', 'awesome-support-time-tracking' ); ?></strong></p>
				<div class="as_time_tracking_date_range">
				 <label for="as_time_tracking_report_date_from"><?php _e( 'From', 'awesome-support-time-tracking' ); ?></label>
				 <input id="as_time_tracking_report_date_from" type="text" name="as_time_tracking_report_date_from" readonly="readonly" />
				 <label for="as_time_tracking_report_date_to"><?php _e( 'To', 'awesome-support-time-tracking' ); ?></label>
				 <input id="as_time_tracking_report_date_to" type="text" name="as_time_tracking_report_date_to" readonly="readonly"  />
				</div>
				<div class="as_time_tracking_agents">

					<input id="as_time_tracking_all_agents" type="checkbox" name="as_time_tracking_all_agents" checked><label id="as_time_tracking_all_agents_label" for="as_time_tracking_all_agents"><?php _e( 'All agents', 'awesome-support-time-tracking' ); ?></label>

					<div id="as_time_tracking_selected_agents_wrapper">
						<label id="as_time_tracking_selected_agents_label" for="as_time_tracking_selected_agents"><?php _e( 'Selected agents:', 'awesome-support-time-tracking' ); ?></label>
						<?php
						$args = array(
												'role' => 'wpas_agent',
											 );

						$agents = get_users( $args );
						?>
						<select id="as_time_tracking_selected_agents" name="as_time_tracking_selected_agents" multiple="multiple">
							<?php
							foreach( $agents as $agent ) {
								echo "<option value=" . $agent->ID . ">" . $agent->display_name . "</option>";
							}
							?>
						</select>
					</div>
				</div>

				<div class="as_time_tracking_customers">

					<input id="as_time_tracking_all_customers" type="checkbox" name="as_time_tracking_all_customers" checked><label id="as_time_tracking_all_customers_label" for="as_time_tracking_all_customers"><?php _e( 'All Customers', 'awesome-support-time-tracking' ); ?></label>

					<div id="as_time_tracking_selected_customers_wrapper">
						<label id="as_time_tracking_selected_customers_label" for="as_time_tracking_selected_customers"><?php _e( 'Selected customers:', 'awesome-support-time-tracking' ); ?></label>
						<?php
						$args = array(
												'role' => 'wpas_user',
											 );

						$support_users = get_users( $args );
						?>
						<select id="as_time_tracking_selected_customers" name="as_time_tracking_selected_customers" multiple="multiple" >
						</select>
					</div>
				</div>

				<div class="as_time_tracking_open_closed_tickets">
					<label for="as_time_tracking_all_closed_tickets"><?php _e( 'All or closed tickets:', 'awesome-support-time-tracking' ); ?></label>
					<select id="as_time_tracking_all_closed_tickets" name="as_time_tracking_all_closed_tickets">
						<option value="all"><?php _e( 'All', 'awesome-support-time-tracking' ); ?></option>
						<option value="closed"><?php _e( 'Closed', 'awesome-support-time-tracking' ); ?></option>
					</select>
				</div>

				<div class="as_time_tracking_all_selected_tickets">
					<input id="as_time_tracking_all_tickets" type="checkbox" name="as_time_tracking_all_tickets" checked><label id="as_time_tracking_all_tickets_label" for="as_time_tracking_all_tickets"><?php _e( 'All tickets', 'awesome-support-time-tracking' ); ?></label>
					<div id="as_time_tracking_selected_tickets_wrapper">
						<label id="as_time_tracking_selected_tickets_label" for="as_time_tracking_selected_tickets"><?php _e( 'Selected tickets:', 'awesome-support-time-tracking' ); ?></label>
						<select id="as_time_tracking_selected_tickets" name="as_time_tracking_selected_tickets" multiple="multiple">
							<?php
							global $wpdb;
							$db_query = "SELECT ID, post_title, post_status FROM " . $wpdb->prefix . "posts WHERE post_type = 'ticket' AND post_status NOT LIKE '%auto-draft%' AND post_status != 'trash'";
							$db_result = $wpdb->get_results( $db_query, OBJECT );

							foreach( $db_result as $ticket ) {
								$status = get_post_meta( $ticket->ID, "_wpas_status", true );

								if( $status === "open" ) {
									echo "<option value=" . $ticket->ID . ">" . $ticket->post_title . "</option>";
								}
							}
							?>
						</select>
					</div>
				</div>

				<a id="as_time_tracking_report_filter_submit" class="button button-primary" href="#"><?php _e( 'Filter report', 'awesome-support-time-tracking' ); ?></a>

			</div>

			<div class="as_time_tracking_report_content"></div>
		</div>
	</div>
<?php
}
