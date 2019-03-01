<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Add CSS file for the plugin
 *
 * @since 	0.1.0
 * @return  void
 */
function load_as_time_tracking_styles() {
	wp_enqueue_style( 'as_time_tracking_styles', AS_TT_URL . 'css/as-time-tracking-styles.css', false, '1.0.0' );
}

add_action( 'admin_enqueue_scripts', 'load_as_time_tracking_styles' );

/**
* Add jQuery UI CSS
*
* @since		0.1.0
* @return		void
*/
function load_as_time_tracking_ui_css() {
	wp_enqueue_style( 'as_time_tracking_ui_styles', AS_TT_URL . 'css/jquery-ui.css', false, '1.0.0' );
}

add_action( 'admin_enqueue_scripts', 'load_as_time_tracking_ui_css' );

/**
 * Add CSS for reports and select2. Select2 is loaded from the CDN provided on https://select2.github.io/
 *
 * @since 	0.1.0
 * @return  void
 */
function load_as_time_tracking_reporting_select_css() {
  global $current_screen;

  if( $current_screen->id == 'trackedtimes_page_trackedtimes-reports' ) {
    wp_enqueue_style( 'as_time_tracking_reporting_styles', AS_TT_URL . 'css/as-time-tracking-reporting-styles.css', false, '1.0.0' );
  }

	if(
		$current_screen->id == 'trackedtimes' ||
		$current_screen->id == 'edit-trackedtimes' ||
		$current_screen->id == 'trackedtimes_page_trackedtimes-reports' ||
		$current_screen->id == 'trackedtimes_page_trackedtimes-invoicing'
	) {
		wp_register_style( 'as_time_tracking_select2_styles', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' );
		wp_enqueue_style( 'as_time_tracking_select2_styles' );
	}
}

add_action( 'admin_enqueue_scripts', 'load_as_time_tracking_reporting_select_css' );
