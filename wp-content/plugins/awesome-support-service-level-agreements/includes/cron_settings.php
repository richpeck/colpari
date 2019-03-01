<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Return cron recurrences
 * 
 * @return array
 */
function wpas_sla_cron_recurrences() {

	return array(
			'every5min'    => array(
				'interval' => 5 * MINUTE_IN_SECONDS,
				'display'  => __( 'Every 5 Minutes', 'wpas_sla' ),
			),
			'every10min'   => array(
				'interval' => 10 * MINUTE_IN_SECONDS,
				'display'  => __( 'Every 10 Minutes', 'wpas_sla' ),
			),
			'every20min'   => array(
				'interval' => 20 * MINUTE_IN_SECONDS,
				'display'  => __( 'Every 20 Minutes', 'wpas_sla' ),
			),
			'every30min'   => array(
				'interval' => 30 * MINUTE_IN_SECONDS,
				'display'  => __( 'Every 30 Minutes', 'wpas_sla' ),
			),
			'hourly'       => array(
				'interval' => HOUR_IN_SECONDS,
				'display'  => __( 'Once Hourly', 'wpas_sla' ),
			),
			'every2ndhour' => array(
				'interval' => 2 * HOUR_IN_SECONDS,
				'display'  => __( 'Every 2nd Hour', 'wpas_sla' ),
			),
			'every4thhour' => array(
				'interval' => 4 * HOUR_IN_SECONDS,
				'display'  => __( 'Every 4th Hour', 'wpas_sla' ),
			),
			'every6thhour' => array(
				'interval' => 6 * HOUR_IN_SECONDS,
				'display'  => __( 'Every 6th Hour', 'wpas_sla' ),
			),
			'twicedaily'   => array(
				'interval' => 12 * HOUR_IN_SECONDS,
				'display'  => __( 'Twice Daily', 'wpas_sla' ),
			),
			'daily'        => array(
				'interval' => DAY_IN_SECONDS,
				'display'  => __( 'Once Daily', 'wpas_sla' ),
			),
		);

}

add_action( 'init', 'wpas_sla_init_cron' );

/**
 * init cron
 */
function wpas_sla_init_cron() {
	
    if ( !wp_next_scheduled( 'wpas_sla_alerts_event' ) ) {
        $cron_recurrence = wpas_get_option( 'wpas_sla_cron_recurrence',  'hourly' );
		wpas_sla_schedule_cron( $cron_recurrence );
    }
	
}

/**
 * Schedule cron
 * 
 * @param string $recurrence
 */
function wpas_sla_schedule_cron( $recurrence ) {
	
	wp_schedule_event( time(), $recurrence, 'wpas_sla_alerts_event' );
}


add_action( 'wpas_sla_alerts_event', 'wpas_sla_process_cron' );

/**
 * Run cron
 */
function wpas_sla_process_cron() {
	$cron = new WPAS_SLA_Alert_Cron();
	$cron->process();
}


add_filter( 'cron_schedules', 'wpas_sla_register_cron_schedules' );

function wpas_sla_register_cron_schedules( $schedules ) {
	
	$recurrences = wpas_sla_cron_recurrences();
	
	foreach ( $recurrences as $cr_opt_key => $cr_opt ) {

		if( !in_array( $cr_opt_key, array_keys( $schedules ) ) ) {
			$schedules[ $cr_opt_key ] = $cr_opt;
		}
	}
	return $schedules;
}


add_action( 'tf_pre_save_admin_wpassla', 'wpas_sla_pre_save_admin', 11, 3 );

function wpas_sla_pre_save_admin( $admin, $activeTab, $options ) {
	if( $activeTab->settings['id'] == 'cron' ) {
		
		$old_cron_recurrence = wpas_get_option( 'cron_recurrence',  'hourly' );
		
		$new_cron_recurrence = isset( $_POST['wpassla_cron_recurrence'] ) && $_POST['wpassla_cron_recurrence'] ? $_POST['wpassla_cron_recurrence'] : 'hourly';

		if( $old_cron_recurrence != $new_cron_recurrence ) {
			wp_clear_scheduled_hook( 'wpas_sla_alerts_event' );
			wpas_sla_schedule_cron( $new_cron_recurrence );
		}
	}
                
}



add_filter( 'wpas_logs_handles', 'wpas_sla_register_log_handle' );
function wpas_sla_register_log_handle( $handles ) {
	
	array_push( $handles, 'wpas_sla' );

	return $handles;
}




add_action( 'wp_ajax_wpas_sla_log_win_content', 'wpas_sla_log_win_content' );
/**
 * Return content for log window
 */
function wpas_sla_log_win_content() {


	$content = "";
	
	$log = new WPAS_Logger( 'wpas_sla' );
	$file_path = $log->get_log_file_path();
	
	if( is_file( $file_path ) ) {
	    $content = file_get_contents( $file_path );
	}
	
	?>
	<div id="wpas_sla_log_win">
		<textarea rows="40" style="width:100%;height:380px;margin-top:30px;"><?php echo $content; ?></textarea>
	</div>

	<?php
	die();
}




add_filter( 'wpas_email_notifications_cases', 'wpas_sla_register_notification_case' );
function wpas_sla_register_notification_case( $cases = array() ) {
            
	if( !in_array( 'wpas_sla', $cases ) ) {
        $cases[ 'wpas_sla' ] = 'wpas_sla';
        if( !wpas_get_option( 'wpas_sla' ) ) {
            wpas_update_option( 'wpas_sla', 'wpas_sla' );
        }
    }
            
    return $cases;
}



add_filter( 'wpas_email_notifications_case_is_active', 'wpas_sla_activate_email_notifications', 11, 2 );

function wpas_sla_activate_email_notifications( $active , $case ) {
	
	if( 'wpas_sla' === $case ) {
		$active = true;
	}
	
	return $active;
}