<?php

/**
 * Fired when the plugin is uninstalled.
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option('timify_widget_id');
delete_option('timify_widget_language');
delete_option('timify_widget_position');
delete_option('timify_widget_button_label');
