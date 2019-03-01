<?php

/* Exit if accessed directly */
if( !defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// Report grouping options
$group_options = array(
	'day'   => __( 'Day', 'wpas_sla' ),
	'week'  => __( 'Week', 'wpas_sla' ),
	'month' => __( 'Month', 'wpas_sla' )
);
	
	
$group_by = filter_input( INPUT_GET, 'group_by', FILTER_SANITIZE_STRING );
$start_date = filter_input( INPUT_GET, 'start_date', FILTER_SANITIZE_STRING );
$end_date = filter_input( INPUT_GET, 'end_date', FILTER_SANITIZE_STRING );


$group_by = $group_by && array_key_exists( $group_by, $group_options ) ? $group_by : 'day';



// default select last 1 month if no date is selected
$_date = new DateTime('NOW');

if( !$end_date ) {
	$end_date = $_date->format( SLA_DATE_FORMAT );
}

if( !$start_date ) {
	$_date->modify( '-1 month' );
	$start_date = $_date->format( SLA_DATE_FORMAT );
}

$args = compact( 'start_date', 'end_date', 'group_by' );

$results = wpas_sla_get_report_results( $args );
	

?>

<div class="wrap">
		
		<h2><?php _e( 'SLA Report', 'wpas_sla' ); ?></h2>
		
		<div class="wpas_sla_reports">
			<div class="wpas_sla_report">
				<div class="wpas_sla_report_filter_form">
					<form method="get" action="">
						<input type="hidden" name="page" value="wpas-sla-reports" />
						<input type="hidden" name="post_type" value="wpas_sla" />
						<ul>
							<li>
								<label><?php _e( 'Start Date', 'wpas_sla' ); ?></label>
								<input type="text" class="wpas_sla_report_start_date" name="start_date" value="<?php echo $start_date; ?>"/>
							</li>
							<li>
								<label><?php _e( 'End Date', 'wpas_sla' ); ?></label>
								<input type="text" class="wpas_sla_report_end_date" name="end_date" value="<?php echo $end_date; ?>"/>
							</li>
							<li>
								<label><?php _e( 'Group by', 'wpas_sla' ); ?></label>
								<select name="group_by">
									<?php foreach ( $group_options as $group_name => $group_label ) {
										$selected = $group_name === $group_by ? ' selected="selected"' : '';
										echo "<option value=\"{$group_name}\"{$selected}>{$group_label}</option>";
									} ?>

								</select>
							</li>
							<li>
								<input type="submit" name="filter_report" class="button" value="<?php _e( 'Filter', 'wpas_sla' ); ?>" />
							</li>
						</ul>
					</form>
						
				</div>
					<div class="clear clearfix"></div>
					
				<div class="wpas_sla_report_results">
					<?php
					if( 0 === count( $results ) ) {
						echo '<div>'.__( 'No result found.', 'wpas_sla' ).'</div>';
					} else {
					
					?>
						
					<table class="wp-list-table widefat fixed striped posts">
						<thead>
							<tr>
								<th><?php echo $group_options[ $group_by ]; ?></th>
								<th><?php _e( 'Tickets closed before due date', 'wpas_sla' ); ?></th>
								<th><?php _e( 'Tickets closed after due date', 'wpas_sla'); ?></th>
							</tr>
						</thead>


						<tbody>
							<?php foreach ( $results as $result ) { 
								
								
								
								$duration = '';
								
								if( 'month' === $group_by ) {
									
									$dt = DateTime::createFromFormat('!m', $result->month );
									$duration = $dt->format('F') . ' ' . $result->year;
								} elseif( 'week' === $group_by ) {
									
									$duration = (new DateTime())->setISODate($result->year, $result->week)->format('Y-m-d') . ' - ' . 
												(new DateTime())->setISODate($result->year, $result->week, 7)->format('Y-m-d');
									
								} else {
									$dt = DateTime::createFromFormat( SLA_DATE_FORMAT . ' ' . SLA_TIME_FORMAT, $result->close_date );
									
									$duration = $dt->format( SLA_DATE_FORMAT );
								}
								
								?>

							<tr>
									<td><?php echo $duration; ?></td>
									<td><?php echo $result->closed_before_due_date; ?></td>
									<td><?php echo $result->closed_after_due_date; ?></td>
							</tr>

							<?php } ?>

						</tbody>
					</table>
						
					<?php
					}
					?>
				</div>
			</div>
		</div>
	

</div>