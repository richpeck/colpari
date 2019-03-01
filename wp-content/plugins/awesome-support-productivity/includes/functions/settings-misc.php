<?php
add_filter( 'wpas_plugin_settings', 'wpas_pf_misc_settings', 5, 1 );
/**
 * Add misc useful settings
 *
 * @param  array $def Array of existing settings
 *
 * @return array      Updated settings
 */
function wpas_pf_misc_settings( $def ) {

	$settings = array(
		'pfMisc01' => array(
			'name'    => __( 'PF Options', 'wpas_productivity' ),
			'options' => array(
				array(
					'name' => __( 'Front End Ticket Content', 'wpas_productivity' ),
					'desc' => __( 'These options allow you to customize what shows up before or after certain items on the front-end ticket page', 'wpas_productivity' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'Content BEFORE The Submit Button', 'wpas_productivity' ),
					'id'      => 'pf_content_before_submit_ticket_button',
					'type'    => 'editor',
					'desc'    => __( 'Enter the content you want to show just before the Submit Ticket button', 'wpas_productivity' ),
					'settings' => array( 'quicktags' => true, 'textarea_rows' => 7 )
				),
				
				array(
					'name'    => __( 'Content BEFORE The Subject Line', 'wpas_productivity' ),
					'id'      => 'pf_content_before_subject_line',
					'type'    => 'editor',
					'desc'    => __( 'Enter the content you want to show just before the Subject line.', 'wpas_productivity' ),
					'settings' => array( 'quicktags' => true, 'textarea_rows' => 7 )
				),

				array(
					'name' => __( 'Mandatory Fields', 'wpas_productivity' ),
					'desc' => __( 'Set certain fields as mandatory', 'wpas_productivity' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'Make Product Mandatory', 'wpas_productivity' ),
					'id'      => 'pf_make_product_mandatory',
					'type'    => 'checkbox',
					'desc'    => __( 'Check this box to force the user to always enter a product when submitting a ticket.', 'wpas_productivity' ),
					'default' => false
				),
				array(
					'name'    => __( 'Make Department Mandatory', 'wpas_productivity' ),
					'id'      => 'pf_make_department_mandatory',
					'type'    => 'checkbox',
					'desc'    => __( 'Check this box to force the user to always enter a department when submitting a ticket.', 'wpas_productivity' ),
					'default' => false
				),				
				
				array(
					'name' => __( 'Ticket Limits', 'wpas_productivity' ),
					'desc' => __( 'Options to set ticket limits in various areas of the system', 'wpas_productivity' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'Maximum Number Of Open Tickets', 'wpas_productivity' ),
					'id'      => 'pf_max_number_of_open_tickets',
					'type'    => 'number',
					'desc'    => __( 'Limit the number of tickets that a user can have open at any one time.', 'wpas_productivity' ),
					'default' => 10
				),
				array(
					'name'    => __( 'Maximum Open Tickets In User Profile Widget', 'wpas_productivity' ),
					'id'      => 'pf_max_number_of_open_tickets_user_profile_widget',
					'type'    => 'number',
					'desc'    => __( 'Limit the number of open tickets that show up in the user profile widget when an agent or admin is viewing a ticket. Set to -1 for all tickets; If set to zero it will use the system default of 10.', 'wpas_productivity' ),
					'default' => 999,
					'min'	  => -1
				),
				array(
					'name'    => __( 'Maximum Closed Tickets In User Profile Widget', 'wpas_productivity' ),
					'id'      => 'pf_max_number_of_closed_tickets_user_profile_widget',
					'type'    => 'number',
					'desc'    => __( 'Limit the number of closed tickets that show up in the user profile widget when an agent or admin is viewing a ticket.  Set to -1 for all tickets; If set to zero it will use the system default of 10.', 'wpas_productivity' ),
					'default' => 999,
					'min'	  => -1
				),
				array(
					'name'    => __( 'Maximum Tickets Per User', 'wpas_productivity' ),
					'id'      => 'pf_max_tickets_per_user',
					'type'    => 'number',
					'desc'    => __( 'Set the maximum number of tickets a user is allowed in their account - includes both open and closed tickets.  Set to -1 for all tickets. You can use this option to prevent users from abusing your support system.', 'wpas_productivity' ),
					'default' => -1,
					'min'	  => -1
				),
				array(
					'name'    => __( 'Message To Show User With Max Tickets', 'wpas_productivity' ),
					'id'      => 'pf_message_to_show_user_with_max_tickets',
					'type'    => 'editor',
					'desc'    => __( 'Enter the message to be displayed to the user when they have hit the maximum number of tickets allowed in their account.', 'wpas_productivity' ),
					'default' => __( 'We apologize for the inconvenience but it looks like you have hit the maximum number of tickets allowed in your account.  This limit was set to prevent abuse of our support system.  Please use our contact form to send us a note instead.', 'wpas_productivity' ),
					'settings' => array( 'quicktags' => true, 'textarea_rows' => 7 )
				),
				
				array(
					'name' => __( 'One Step Click-To-Close', 'wpas_productivity' ),
					'desc' => __( 'Use this section to enable and control the behavior of a single click click-to-close link that can be embedded in ticket confirmation emails. <b>Note that if you turn this option on, you must also go to the WordPress SETTINGS->PERMALINKS screen and click the save button at least once!</b>', 'wpas_productivity' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'Enable automatic click-to-close email template tag link', 'wpas_productivity' ),
					'id'      => 'pf_enable_automatic_close_email_template_tag_link',
					'type'    => 'checkbox',
					'desc'    => __( 'Enabling this will create a new template tag for use in emails  - one that will allow the user to close a ticket with one click.', 'wpas_productivity' ),
					'default' => false
				),
				array(
					'name'    => __( 'Slug', 'wpas-ss' ),
					'id'      => 'pf_one_step_click_to_close_slug',
					'type'    => 'text',
					'default' => 'click-to-close',
					'desc'    => __( 'The slug used to access the one step click-to-close page. Defaults to click-to-close', 'wpas_productivity' ),
				),
				array(
					'name'    => __( 'Success Message', 'wpas-ss' ),
					'id'      => 'pf_one_step_click_to_close_post_close_success_message',
					'type'    => 'editor',
					'default' => '<h3>Thank you for closing your ticket and making our workflow a bit smoother.  We hope you are satisfied with our service.</h3>',
					'desc'    => __( 'Please enter a nice message to display to the user when a ticket has successfully been closed.', 'wpas_productivity' ),
				),
				array(
					'name'    => __( 'Failure Message', 'wpas-ss' ),
					'id'      => 'pf_one_step_click_to_close_post_close_failure_message',
					'type'    => 'editor',
					'default' => '<h3>There was a problem closing this ticket. Maybe it was already closed?</h3>',
					'desc'    => __( 'The message displayed to the user when an error is enountered or the ticket could not be closed.', 'wpas_productivity' ),
				),				
				array(
					'name'    => __( 'Keep user logged in after ticket is closed?', 'wpas_productivity' ),
					'id'      => 'pf_keep_user_logged_in_post_close',
					'type'    => 'checkbox',
					'desc'    => __( 'Enabling this is good for the user experience but bad for security.  This setting only applies if the user was not already logged in.', 'wpas_productivity' ),
					'default' => false
				),
				array(
					'name'    => __( 'Clear link after ticket is closed?', 'wpas_productivity' ),
					'id'      => 'pf_clear_close_link_after_close',
					'type'    => 'checkbox',
					'desc'    => __( 'If enabled, the link will not be able to be used again to access the ticket.', 'wpas_productivity' ),
					'default' => false
				),
				
				array(
					'name' => __( 'One Step Click-To-View', 'wpas_productivity' ),
					'desc' => __( 'Use this section to enable and control the behavior of a single-click click-to-view link that can be embedded in ticket confirmation emails. <b>Note that if you turn this option on, you must also go to the WordPress SETTINGS->PERMALINKS screen and click the save button at least once!</b>  Also, note that this is a security issue - anyone with one of the ticket links can log into the users account!', 'wpas_productivity' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'Enable the single-click click-to-view email template tag link', 'wpas_productivity' ),
					'id'      => 'pf_enable_click_to_view_email_template_tag_link',
					'type'    => 'checkbox',
					'desc'    => __( 'Enabling this will create a new template tag for use in emails  - one that will allow the user to click and view a ticket immediately, bypassing the login screen!', 'wpas_productivity' ),
					'default' => false
				),				
				array(
					'name'    => __( 'Slug', 'wpas-ss' ),
					'id'      => 'pf_one_step_click_to_view_slug',
					'type'    => 'text',
					'default' => 'click-to-view',
					'desc'    => __( 'The slug used to access the one step click-to-view page. Defaults to click-to-view', 'wpas_productivity' ),
				),
				array(
					'name'    => __( 'Clear link after ticket is closed?', 'wpas_productivity' ),
					'id'      => 'pf_clear_view_link_after_close',
					'type'    => 'checkbox',
					'desc'    => __( 'If enabled, the link will not be able to be used again to access the ticket.', 'wpas_productivity' ),
					'default' => false
				),
				
				array(
					'name' => __( 'Front-end Ticket Defaults', 'wpas_productivity' ),
					'desc' => __( 'Default values for subject and description fields', 'wpas_productivity' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'Default value for subject', 'wpas-ss' ),
					'id'      => 'pf_ticket_form_default_subject',
					'type'    => 'text',
					'desc'    => __( 'The default value to be shown in the SUBJECT field in the ticket form', 'wpas_productivity' ),
				),
				array(
					'name'    => __( 'Default value for description', 'wpas-ss' ),
					'id'      => 'pf_ticket_form_default_description',
					'type'    => 'textarea',
					'desc'    => __( 'The default value to be shown in the DESCRIPTION field in the ticket form', 'wpas_productivity' ),
				),					
				
				
				array(
					'name' => __( 'Other', 'wpas_productivity' ),
					'desc' => __( 'Options with no other place to go!', 'wpas_productivity' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'Automatically Lock Ticket On Close', 'wpas_productivity' ),
					'id'      => 'pf_auto_lock_ticket_on_close',
					'type'    => 'checkbox',
					'desc'    => __( 'Check this box to automatically lock tickets when they are closed.', 'wpas_productivity' ),
					'default' => false
				),

				
			)			
		)
	);

	return array_merge( $def, $settings );

}

