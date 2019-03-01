<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Add invoice page to the Tickets admin menu.
 *
 * @since   0.1.0
 * @return  void
 */
add_action( 'admin_menu', 'as_time_tracking_add_invoicing' );

function as_time_tracking_add_invoicing() {
  add_submenu_page(
		'edit.php?post_type=trackedtimes',
		'Awesome Support - Time Tracking Invoicing',
		__( 'Time Tracking Invoicing', 'awesome-support-time-tracking' ),
		'manage_options',
		'trackedtimes-invoicing',
		'as_time_tracking_invoicing_output'
	);
}

/**
 * Outputs the invoicing page filter and process for generating csv.
 *
 * @since   0.1.0
 * @return  void
 */
function as_time_tracking_invoicing_output() {
?>

<div class="wrap nosubsub">
		<h1><?php _e( 'Awesome Support - Tracked Times Invoicing', 'awesome-support-time-tracking' ); ?></h1>

    <div id="as_time_tracking_invoicing_container">
      <div class="as_time_tracking_filter_list">
        <p class="invoicing-filter-title"><strong><?php _e( 'Filter:', 'awesome-support-time-tracking' ); ?></strong></p>
        <div class="as_time_tracking_date_range">
         <label for="as_time_tracking_invoice_date_from"><?php _e( 'From', 'awesome-support-time-tracking' ); ?></label>
         <input id="as_time_tracking_invoice_date_from" type="text" name="as_time_tracking_invoice_date_from" readonly="readonly" />
         <label for="as_time_tracking_invoice_date_to"><?php _e( 'To', 'awesome-support-time-tracking' ); ?></label>
         <input id="as_time_tracking_invoice_date_to" type="text" name="as_time_tracking_invoice_date_to" readonly="readonly"  />
				 <span class="as-time-tracking-notes"><?php _e( '*If the date range is left empty then the date range will be from the current day plus 30 days ahead.', 'awesome-support-time-tracking' ) ?></span>
        </div>

				<div class="as_time_tracking_agents">
					<input id="as_time_tracking_invoice_all_agents" type="checkbox" name="as_time_tracking_invoice_all_agents" checked><label id="as_time_tracking_invoice_all_agents_label" for="as_time_tracking_invoice_all_agents"><?php _e( 'All agents', 'awesome-support-time-tracking' ); ?></label>
					<div id="as_time_tracking_invoice_selected_agents_wrapper">
						<label id="as_time_tracking_invoice_selected_agents_label" for="as_time_tracking_invoice_selected_agents_label"><?php _e( 'Selected agents:', 'awesome-support-time-tracking' ); ?></label>
						<?php
						$args = array(
												'role' => 'wpas_agent',
											 );

						$agents = get_users( $args );
						?>
						<select id="as_time_tracking_invoice_selected_agents" name="as_time_tracking_invoice_selected_agents" multiple="multiple">
							<?php
							foreach( $agents as $agent ) {
								echo "<option value=" . $agent->ID . ">" . $agent->display_name . "</option>";
							}
							?>
						</select>
					</div>
				</div>

				<div class="as_time_tracking_customers">
					<input id="as_time_tracking_invoice_all_customers" type="checkbox" name="as_time_tracking_invoice_all_customers" checked><label id="as_time_tracking_invoice_all_customers_label" for="as_time_tracking_invoice_all_customers"><?php _e( 'All Customers', 'awesome-support-time-tracking' ); ?></label>
					<div id="as_time_tracking_invoice_selected_customers_wrapper">
						<label id="as_time_tracking_invoice_selected_customers_label" for="as_time_tracking_invoice_selected_customers"><?php _e( 'Selected customers:', 'awesome-support-time-tracking' ); ?></label>
						<?php
						$args = array(
												'role' => 'wpas_user',
											 );

						$support_users = get_users( $args );
						?>
						<select id="as_time_tracking_invoice_selected_customers" name="as_time_tracking_invoice_selected_customers" multiple="multiple" >
						</select>
					</div>
				</div>

				<div class="as_time_tracking_open_closed_tickets">
					<label for="as_time_tracking_invoice_all_closed_tickets"><?php _e( 'All or closed tickets:', 'awesome-support-time-tracking' ); ?></label>
					<select id="as_time_tracking_invoice_all_closed_tickets" name="as_time_tracking_invoice_all_closed_tickets">
						<option value="all"><?php _e( 'All', 'awesome-support-time-tracking' ); ?></option>
						<option value="closed"><?php _e( 'Closed', 'awesome-support-time-tracking' ); ?></option>
					</select>
				</div>

				<div class="as_time_tracking_all_selected_tickets">
					<input id="as_time_tracking_invoice_all_tickets" type="checkbox" name="as_time_tracking_invoice_all_tickets" checked><label id="as_time_tracking_invoice_all_tickets_label" for="as_time_tracking_invoice_all_tickets"><?php _e( 'All tickets', 'awesome-support-time-tracking' ); ?></label>
					<div id="as_time_tracking_invoice_selected_tickets_wrapper">
						<label id="as_time_tracking_invoice_selected_tickets_label" for="as_time_tracking_invoice_selected_tickets"><?php _e( 'Selected tickets:', 'awesome-support-time-tracking' ); ?></label>
						<select id="as_time_tracking_invoice_selected_tickets" name="as_time_tracking_invoice_selected_tickets" multiple="multiple">
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

        <a id="as_time_tracking_invoice_filter_submit" class="button button-primary" href="#"><?php _e( 'Generate CSV', 'awesome-support-time-tracking' ); ?></a>

				<div class="as_time_tracking_message_container"></div>
      </div>

			<div class="as_time_tracking_csv_created_container">
					<p class="initial_text"></p>
					<a id="as_time_tracking_download_csv_btn" href="<?php echo get_site_url(); ?>/wp-content/uploads/awesome-support/time-tracking/wpas_time_tracking_invoice.csv" class="button button-primary"><?php _e( "Download Invoice File", "awesome-support-time-tracking" ) ?></a>
					<div class="as_time_tracking_csv_preview"></div>
					<div class="approve_disapprove_container">
						<a id="as_time_tracking_invoice_approve_btn" href="#" class="button button-primary" ><?php _e( "Approve", "awesome-support-time-tracking" ) ?></a>
						<a id="as_time_tracking_invoice_disapprove_btn" href="#" class="button button-primary" ><?php _e( "Cancel invoice run", "awesome-support-time-tracking" ) ?></a>
					</div>
					<div class="as_time_tracking_message_container"></div>
			</div>
    </div>
</div>
<?php
}
