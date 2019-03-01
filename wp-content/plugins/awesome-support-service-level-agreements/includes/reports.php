<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

add_action( 'admin_menu', 'wpas_sla_add_report_submenu' );
/**
 * Add reports menu
 */
function wpas_sla_add_report_submenu() {
	add_submenu_page( 'edit.php?post_type=wpas_sla', __( 'Reports', 'wpas_sla' ), __( 'Reports', 'wpas_sla' ), 'ticket_sla_admin', 'wpas-sla-reports', 'wpas_sla_display_reports_page' );
}

/**
 * Return report results
 * 
 * @global object $wpdb
 * 
 * @param array $args
 * 
 * @return array
 */
function wpas_sla_get_report_results( $args = array() ) {
	global $wpdb;
	
	
	$start_date = $args['start_date'];
	$end_date = $args['end_date'];
	$group_by = $args['group_by'];
	
	$query = "SELECT close_date,
				YEAR(close_date)  `year`,
				MONTH(close_date) `month`,
				WEEK(close_date)  `week`,
				sum( closed_before_due_date ) closed_before_due_date,
				sum( closed_after_due_date ) closed_after_due_date
			FROM (
				SELECT  pm.post_id, 
						pm.meta_value as close_date,
						pm2.closed_before_due_date,
						pm2.closed_after_due_date
					FROM {$wpdb->postmeta} pm
						INNER JOIN {$wpdb->postmeta} pm3 ON pm3.post_id = pm.post_id AND pm3.meta_key = '_wpas_status'
						INNER JOIN (
							SELECT  post_id,
									IF(meta_value = 'yes' , 1,0) as closed_before_due_date,
									IF(meta_value = 'no' , 1,0) as closed_after_due_date
								FROM {$wpdb->postmeta} WHERE meta_key = 'closed_before_due_date'
							) as pm2 ON pm2.post_id = pm.post_id 
					WHERE pm.meta_key = '_ticket_closed_on_gmt' AND ( date(pm.meta_value) BETWEEN '{$start_date}' AND '{$end_date}' ) AND pm3.meta_value = 'closed' 
					GROUP BY pm.post_id
				) AS report GROUP BY {$group_by}(close_date)";


	$results = $wpdb->get_results( $query );
	
	return $results;
}

/**
 * Display reports page
 */
function wpas_sla_display_reports_page() {
	
	include WPAS_SLA_PATH . 'includes/templates/report.php';
	
}