<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Add settings for the timer type. Uses the core plugin's functionality.
 *
 * @param  array $def 	Array of existing settings
 * @return array      	Updated settings
 */
function as_time_tracking_settings_time_tracking( $def ) {
  //Get all roles for billing option
  global $wp_roles;
  $all_roles = $wp_roles->roles;
  $role_arr = array();

  foreach( $all_roles as $key => $value ) {
    $role_arr[$key] = $value['name'];
  }

	$settings = array(
		'time_tracking' => array(
			'name'    => __( 'Time Tracking', 'awesome-support-time-tracking' ),
			'options' => array(
				array(
					'name' => __( 'Advanced Time Tracking Settings', 'awesome-support-time-tracking' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'Timer Type', 'awesome-support-time-tracking' ),
					'id'      => 'time_tracking_timer_type',
					'type'    => 'select',
					'default' => 'automatic',
					'options' => array( 'manual' => 'Manual', 'automatic' => 'Automatic' ),
					'desc'	  => __( '<b>Automatic</b> will start the timer as soon as the agent views a ticket; <b>Manual</b> will allow the agent to decide when to turn on the timer and when to turn it off', 'awesome-support-time-tracking' )
				),
				array(
					'name'    => __( 'Time Tracking Section Background Color', 'awesome-support-time-tracking' ),
					'id'      => 'time_tracking_section_background',
					'type'    => 'color',
					'default' => '#f1f1f1',
					'desc'	  => __( 'This is the background color of the time tracking data area shown under the reply section of the ticket', 'awesome-support-time-tracking' )
				),
				array(
					'name'    => __( 'Minimum Rounding Amount', 'awesome-support-time-tracking' ),
					'id'      => 'time_tracking_section_min_rounding',
					'desc'     => __( 'The minimum rounding amount in minutes. For example, if you would like to round to the nearest hour type 60. Any value above 60 will default to 1 minute.', 'awesome-support-time-tracking' ),
					'type'    => 'text',
					'default' => '1',
				),
		        array(
		          'name'     => __( 'Billing Rate Access', 'awesome-support-time-tracking' ),
		          'id'       => 'time_tracking_section_billing_rate_access',
		          'type'     => 'select',
		          'multiple' => true,
		          'desc'     => __( 'The roles allowed to have a billing rate.', 'awesome-support-time-tracking' ),
		          'options'  => $role_arr,
		          'default'  => ''
		        ),
				array(
					'name'    => __( 'Action When Ticket Is Deleted', 'awesome-support-time-tracking' ),
					'id'      => 'time_tracking_ticket_delete_action',
					'type'    => 'select',
					'default' => 'nothing',
					'options' => array( 'nothing' => 'Do nothing', 'delete' => 'Delete time log records when tickets are deleted', 'prevent' => 'Prevent ticket from being deleted if there is a timelog record' )
				),
				array(
					'name'    => __( 'Allow Multiple Time Entries Per Reply', 'awesome-support-time-tracking' ),
					'id'      => 'time_tracking_ticket_allow_multiple_entries',
					'desc'     => __( 'Most operations will only require one time entry per reply on a ticket. But sometimes you might want to allow an agent to manually enter multiple time entries for a particular reply. If you work that way then you should turn on this option.', 'awesome-support-time-tracking' ),
					'type'    => 'select',
					'default' => 'no',
					'options' => array( 'yes' => 'Yes', 'no' => 'No' )
				),
				array(
				  'name'    => __( 'Allow Logging Of Time At The Ticket Level', 'awesome-support-time-tracking' ),
				  'id'      => 'time_tracking_ticket_allow_ticket_level',
				  'desc'     => __( 'This will apply to the tickets page. Most time log entries are associated with a ticket reply.  However, you might want to log time without attaching it to a particular reply.  In that case you should turn this option on.', 'awesome-support-time-tracking' ),
				  'type'    => 'select',
				  'default' => 'no',
				  'options' => array( 'yes' => 'Yes', 'no' => 'No' )
				),
				array(
				  'name'    => __( 'Default Value for The Allow Logging Of Time At The Ticket Level Flag', 'awesome-support-time-tracking' ),
				  'id'      => 'time_tracking_ticket_save_ticket_level_time_checkbox',
				  'desc'     => __( 'This will apply to the tickets page when the "Allow Logging Of Time At The Ticket Level" is enabled', 'awesome-support-time-tracking' ),
				  'type'    => 'select',
				  'default' => 'no',
				  'options' => array( 'yes' => 'Yes', 'no' => 'No' )
				),
				array(
					'name'    => __( 'Delete Invoice Number Counter On Unintall', 'awesome-support-time-tracking' ),
					'id'      => 'time_tracking_uninstall_delete_invoice_numbers',
					'desc'     => __( 'If set to "On" this will delete the invoice number counter when the plugin is uninstalled.', 'awesome-support-time-tracking' ),
					'type'    => 'select',
					'default' => 'off',
					'options' => array( 'on' => 'On', 'off' => 'Off' )
				)
			)
		)
	);

	return apply_filters( 'ases_settings', array_merge( $def, $settings ) );
}

add_filter( 'wpas_plugin_settings', 'as_time_tracking_settings_time_tracking', 10, 1 );
