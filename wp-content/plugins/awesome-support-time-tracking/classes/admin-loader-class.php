<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class AS_Time_Tracking_Setup {
	public function __construct() {
		$this->includes();
	}

	/**
	 * Include files for the plugin
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function includes() {
	  require_once(AS_TT_PATH . 'includes/as-time-tracking-post-type.php');
	  require_once(AS_TT_PATH . 'includes/as-time-tracking-ticket.php');
	  require_once(AS_TT_PATH . 'includes/as-time-tracking-settings.php');
	  require_once(AS_TT_PATH . 'includes/as-time-tracking-reporting.php');
	  require_once(AS_TT_PATH . 'includes/as-time-tracking-reporting-js.php');
	  require_once(AS_TT_PATH . 'includes/as-time-tracking-css.php');
	  require_once(AS_TT_PATH . 'includes/as-time-tracking-js.php');
	  require_once(AS_TT_PATH . 'includes/as-time-tracking-ticket-js.php');
		require_once(AS_TT_PATH . 'includes/as-time-tracking-admin-user.php');
		require_once(AS_TT_PATH . 'includes/as-time-tracking-invoicing.php');
		require_once(AS_TT_PATH . 'includes/as-time-tracking-invoicing-js.php');
		require_once(AS_TT_PATH . 'includes/as-time-tracking-deactivation.php');
		require_once(AS_TT_PATH . 'includes/as_time_tracking_phase_two_individual_times.php');
	}
}
