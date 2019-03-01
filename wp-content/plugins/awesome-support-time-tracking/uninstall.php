<?php
// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( is_multisite() ) {
	global $wpdb;
	$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );
		as_time_tracking_uninstall();
	if ( $blogs ) {
		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog['blog_id'] );
			as_time_tracking_uninstall();
			restore_current_blog();
		}
	}
}
else {
	as_time_tracking_uninstall();
}

/**
 * Uninstall function. Checks the invoice counter setting and resets the counter if the setting was
 * set to "On".
 *
 * @since  3.0.0
 * @return void
 */
function as_time_tracking_uninstall() {
  $options = maybe_unserialize( get_option( 'wpas_options' ) );

  if( isset( $options['time_tracking_uninstall_delete_invoice_numbers'] ) ) {
    if( $options['time_tracking_uninstall_delete_invoice_numbers'] == "on" ) {
      delete_option( 'as_time_tracking_invoice_count' );
    }
  }
}
