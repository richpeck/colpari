<?php
	/**
	 * Awesome Support Cron.
	 *
	 * In order to check the incoming e-mails on a regular basis
	 * we need to use a cron task.
	 *
	 * We are not using wp_cron() because we need the action to be executed
	 * on the current page (wp_cron() triggers the task by querying the site
	 * using the HTTP API).
	 *
	 * What we need is a mix of wp_cron() and the Heartbeat API. The Hearbeat API
	 * itself is not sufficient because it doest tell when the task is started.
	 * We need to know when the task is started in order to tell the user that
	 * the inbox is being checked.
	 */

	/**
	 * Schedule the cron task.
	 *
	 * @since  0.1.0
	 * @return integer Timestamp of the next event
	 */
	function ases_cron_schedule_task() {

		$task = get_option( 'wpas_check_mails', false );

		if ( !$task ) {
			$next = strtotime( "now" );
			update_option( 'wpas_check_mails', $next );
		} else {
			$next = ases_cron_reschedule_task();
		}

		return $next;

	}

	/**
	 * Reschedule the task.
	 *
	 * @since  0.1.0
	 * @return integer Timestamp of the next event
	 */
	function ases_cron_reschedule_task() {

		$options = maybe_unserialize( get_option( 'wpas_options', array() ) );
		$recurrence = isset( $options[ 'email_interval' ] ) ? intval( $options[ 'email_interval' ] ) : 0;
		$next = strtotime( 'now' ) + $recurrence;

		update_option( 'wpas_check_mails', $next );

		return $next;

	}

	/**
	 * Get next occurence of the event.
	 *
	 * @since  0.1.0
	 * @return integer Timestamp of the next event
	 */
	function ases_cron_get_next_event() {
		return get_option( 'wpas_check_mails', false );
	}

	/**
	 * Check if the cron task should be run now.
	 *
	 * @since  0.1.0
	 * @return bool  Whether or not the task should be run
	 */
	function ases_cron_is_time() {

		if ( '1' !== wpas_get_option( 'email_polling_mode', '0' ) ) {
			return false;
		} elseif ( strtotime( 'now' ) >= ases_cron_get_next_event() ) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Runs the cron task and reschedule the event.
	 *
	 * @since  0.1.0
	 * @return mixed
	 */
	function ases_cron_run_task() {
		ases_cron_reschedule_task();

		return wpas_check_mails();
	}

	if ( '1' === wpas_get_option( 'email_polling_mode', '0' ) ) {
		add_filter( 'heartbeat_received', 'ases_heartbeat_cron', 10, 2 );
	}
	/**
	 * Hook into the Heartbeat API.
	 *
	 * @param  array $response Heartbeat tick response
	 * @param  array $data Heartbeat tick data
	 * @return array           Updated Heartbeat tick response
	 */
	function ases_heartbeat_cron( $response, $data ) {

		if ( isset( $data[ 'wpas_check_mails_now' ] ) ) {
			$response[ 'wpas_check_mails_now' ] = ases_cron_is_time();
		}

		return $response;

	}


	/**
	 * Schedule WP Cron polling.
	 */
	function schedule_wp_cron() {

		/**
		 * Avoid rescheduling cron if it's already scheduled.
		 */
		if ( !wp_next_scheduled( 'wpas_es_check_mail' ) ) { //, $args ) ) {

			/**
			 * Schedule mail server polling.
			 */
			wp_schedule_event( time(), 'wpas_es_email_interval', 'wpas_es_check_mail' ); //, $args );
		}

	}


	/**
	 * WP Cron custom schedule Every 5 minutes by default. Set on settings tab.
	 *
	 * @param array $schedules An array of non-default cron schedules.
	 * @return array Filtered array of non-default cron schedules.
	 */
	function custom_cron_schedule( $schedules ) {

		$schedules[ 'wpas_es_email_interval' ] = array(
			'interval' => wpas_get_option( 'email_interval', 300 ),
			'display'  => __( 'WPAS ES Email Interval', 'as-email-support' ),
		);

		return $schedules;
	}
