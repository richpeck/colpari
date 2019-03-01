<?php


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


global $post_id;

$logs = WPAS_CP_Log::getCompanyLogs( $post_id );


?>


<table class="form-table wpas-table-cp-logs">
	<tbody>
	<?php 
	foreach ( $logs as $log ) { 
		
		/* Filter the content before we display it */
		$content = apply_filters( 'the_content', $log->content );
		
		$date = human_time_diff( mysql2date( 'U', $log->date ), current_time( 'timestamp' ) );
		
	?>

		<tr valign="top" class="wpas-table-row wpas-cp-log wpas-cp-log-published" id="wpas-cp-log-<?php echo $log->id; ?>">

			<td colspan="3">
				<span><em class='wpas-time'><?php printf( __( '%s ago', 'wpas_cp' ), $date ); ?></em></span>
				<div class="wpas-action-details"><?php echo $content; ?></div>
			</td>

		</tr>
	<?php } ?>
	</tbody>

</table>