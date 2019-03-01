<?php
add_filter( 'wpas_plugin_settings', 'wpas_settings_email', 10, 1 );
/**
 * Add settings for MailChimp addon.
 *
 * @param  array $def Array of existing settings
 *
 * @return array      Updated settings
 */
function wpas_settings_email( $def ) {

	if ( class_exists( 'TitanFrameworkOption' ) ) {
		require_once( WPAS_MAIL_PATH . 'includes/class-wpas-tf-test-config.php' );
	}

	$schedules = ases_get_schedules();

	$settings = array(
		'email_support' => array(
			'name'    => __( 'E-Mail Piping', 'as-email-support' ),
			'options' => array(
				array(
					'name' => __( 'Connection Information', 'as-email-support' ),
					'desc'	  => __( 'Use these settings for your first mailbox. If you have more than one mailbox then configure the rest of your mailboxes in TICKETS->Inbox Configurations', 'as-email-support'),					
					'type' => 'heading',
				),
				array(
					'name'    => __( 'E-Mail Server', 'as-email-support' ),
					'id'      => 'email_server',
					'type'    => 'text',
					'default' => '',
				),
				array(
					'name'    => __( 'Protocol', 'as-email-support' ),
					'id'      => 'email_protocol',
					'type'    => 'select',
					'default' => 'imap',
					'options' => array( 'pop3' => 'POP3', 'imap' => 'IMAP' )
				),
				array(
					'name'    => __( 'Email Account or Username', 'as-email-support' ),
					'id'      => 'email_username',
					'type'    => 'text',
					'default' => '',
					'desc'    => __( 'This is typically your email address but could be something else - check with your email service provider if you are unsure.', 'as-email-support' )
				),
				array(
					'name'        => __( 'Password', 'as-email-support' ),
					'id'          => 'email_password',
					'type'        => 'text',
					'default'     => '',
					'is_password' => true
				),
				array(
					'name' => __( 'Advanced Settings', 'as-email-support' ),
					'type' => 'heading',
				),
				array(
					'name' => __( 'Advanced Settings', 'as-email-support' ),
					'type' => 'note',
					'desc' => __( 'If you don&#39;t know what the following options are, please do NOT modify them.', 'as-email-support' )
				),
				array(
					'name'    => __( 'Port', 'as-email-support' ),
					'id'      => 'email_port',
					'type'    => 'text',
					'default' => '',
					'desc'    => __( 'This should not be blank. Common ports are 993, 995 and 110', 'as-email-support' )
				),
				array(
					'name'    => __( 'Secure Port', 'as-email-support' ),
					'id'      => 'email_secure',
					'type'    => 'radio',
					'options' => array( 'ssl' => 'SSL', 'tls' => 'TLS', 'none' => 'None' ),
					'default' => 'ssl,',
					'desc'    => __( 'What type of secure connection should be used? Note: TLS support is experimental - if you need it you can try it to see if it works. But most servers use SSL for IMAP/POP3 incoming mailboxes.', 'as-email-support' )
				),
				array(
					'name'    => __( 'Timeout', 'as-email-support' ),
					'id'      => 'email_timeout',
					'type'    => 'text',
					'default' => '30',
					'desc'    => __( 'The timeout value must be in seconds.', 'as-email-support' )
				),
				array(
					'name' => __( 'Testing', 'as-email-support' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'Test Settings', 'as-email-support' ),
					'id'      => 'email_test_settings',
					'type'    => 'email-test-config',
					'default' => '30',
					'desc'    => __( 'Please make sure to save any changed settings before testing the configuration.', 'as-email-support' )
				),
				
				array(
					'name'    => __( 'Global Email Settings', 'as-email-support' ),
					'id'      => 'email_global_settings',
					'type'    => 'heading',
					'desc'    => __( 'All sections below apply to ALL mailboxes!', 'as-email-support' )
				),				

				array(
					'name' => __( 'Email Handling', 'as-email-support' ),
					'type' => 'heading',
				),				
				array(
					'name'    => __( 'Unassigned Email Handling', 'as-email-support' ),
					'id'      => 'ticket_settings',
					'type'    => 'radio',
					'default' => '0',
					'desc'    => __( 'How is incoming email without a ticket number handled? Please remember that some of this could be spam so be careful with your choice!', 'as-email-support' ),					
					'options' => array(
						'0' => __( 'Leave them in the "Unassigned" folder - an agent will manually review and move/assign/delete as necessary', 'as-email-support' ),
						'1' => __( 'Create a new ticket and, if necessary, a new user ', 'as-email-support' ),
						'2' => __( 'Create new ticket if email address matches an existing user; otherwise leave in "Unassigned" folder.', 'as-email-support' ),
					),
				),
				array(
					'name'    => __( 'Closed Tickets Replies', 'as-email-support' ),
					'id'      => 'replied_to_closed',
					'type'    => 'radio',
					'default' => 0,
					'desc'    => __( 'What happens when a client replies to a ticket that&#039;s been closed?', 'as-email-support' ),
					'options' => array(
						'0' => __( 'Reject reply (and send acknowledgment to client)', 'as-email-support' ),
						'1' => __( 'Re-open the ticket', 'as-email-support' ),
					),
				),
				array(
					'name'    => __( 'User Name Construction', 'as-email-support' ),
					'id'      => 'as_es_user_name_construction',
					'type'    => 'radio',
					'default' => 0,
					'desc'    => __( 'How should we attempt to create a user name when a new user is detected? [Requires AS 4.0.3 or later]', 'as-email-support' ),
					'options' => array(
						'0' => __( 'Default - Uses the first part of email address', 'as-email-support' ),
						'1' => __( 'Use the entire email address', 'as-email-support' ),
						'2' => __( 'Use a random number', 'as-email-support' ),
						'3' => __( 'Use a GUID', 'as-email-support' ),
						'4' => __( 'Use the full name as retrieved from the email headers', 'as-email-support' ),
					),
				),

				array(
					'name' => __( 'HTML Handling', 'as-email-support' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'Which HTML Tags Should Be Stripped?', 'as-email-support' ),
					'id'      => 'as_es_strip_all_html_tags',
					'type'    => 'radio',
					'default' => '0',
					'desc'    => __( 'Please select which HTML Tags should be allowed or stripped out of incoming emails.', 'as-email-support' ),
					'options' => array(
						'0' => __( 'Strip All tags', 'as-email-support' ),
						'1' => __( 'Allow the tags listed below ( NOTE: This is a BETA option - you might get unexpected formatting and behavior especially if your incoming emails include any javascript or extra line breaks.)', 'as-email-support' )
					),
				),
				array(
					'name'    => __( 'Allowed HTML Tags', 'as-email-support' ),
					'id'      => 'as_es_allowed_html_tags',
					'type'    => 'textarea',
					'default' => 'b, i, br, u, ul, ol, li, h1, h2, h3, h4, h5, h6, p, a, a[href]',
					'desc'    => __( 'Which HTML tags should be allowed in incoming emails? Enter just the opening tag, not the closing tag. <br /> eg: b, i, br, u, ul, ol, li, h1, h2, h3, h4, h5, h6, p, a, a[href] <br /> See the htmlpurifier.org HTML.Allowed directive for more information on what is allowed here. Do NOT leave this field blank!', 'as-email-support' ),
				),
				array(
					'name' => __( 'Character Set Conversion', 'as-email-support' ),
					'type' => 'heading',
					'desc' => __( 'Do not change anything in this section unless absolutely necessary. Setting this incorrectly will cause all your emails to be silently rejected by the database.', 'as-email-support' ),
				),
				array(
					'name' => __( 'Convert all data to this characterset', 'as-email-support' ),
					'id'   => 'as_es_char_set_conversion',
					'type' => 'text',
					'desc'    => sprintf( __( 'If your mysql server is using a different character set than the one most of your customers use then enter the character set here. <a href="%s" target="_blank">List of acceptable character sets.</a> Some common character sets include UTF-8, ASCII and ISO-8859-1. The default is utf-8. </b><b>Warning: This allows for the character set to be matched to the MYSQL server but could result in the loss of email data for strings or characters that cannot be converted!', 'as-email-support' ), esc_url( 'http://php.net/manual/en/mbstring.supported-encodings.php' ) ),
					'default' => '',
				),
				
				array(
					'name' => __( 'Duplicate Handling', 'as-email-support' ),					
					'type' => 'heading',
					'desc'    => __( 'If your site is being bombarded by duplicate emails this can help you avoid creating unwanted tickets.  BUT, receiving many duplicate emails are generally symptoms of a configuration issue that creates a feedback loop. So this is only a stop-gap measure - you should investigate to find the source of the issue.', 'as-email-support' ),					
				),		

				array(
					'name'    => __( 'Prevent Duplicate Tickets', 'as-email-support' ),
					'id'      => 'as_es_no_duplicates',
					'type'    => 'checkbox',
					'desc'    => __( 'Do not import emails that are duplicates', 'as-email-support' ),
					'default' => false
				),
				array(
					'name'    => __( 'Recent Tickets To Check', 'as-email-support' ),
					'id'      => 'as_es_recent_tickets_to_check',
					'type'    => 'number',
					'desc'    => __( 'Check for duplicates by looking at this number of most recent tickets', 'as-email-support' ),
					'default' => 10
				),				
				
				array(
					'name' => __( 'Piping Settings', 'as-email-support' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'Polling Mode', 'as-email-support' ),
					'id'      => 'email_polling_mode',
					'type'    => 'radio',
					'default' => 0,
					'desc'    => __( 'Which polling method should trigger mail server checks?', 'as-email-support' ),
					'options' => array(
						'0' => __( 'WP Cron (Recommended)', 'as-email-support' ),
						'1' => __( 'jQuery Heartbeat', 'as-email-support' ),
					),
				),
				array(
					'name'    => __( 'Interval', 'as-email-support' ),
					'id'      => 'email_interval',
					'type'    => 'select',
					'default' => '3600',
					'options' => $schedules,
					'desc'    => __( 'How often shall we check for new e-mails?', 'as-email-support' )
				),
			)
		)
	);

	return apply_filters( 'ases_settings', array_merge( $def, $settings ) );

}

/**
 * Add new intervals for the cron tasks.
 *
 * @param  array The list of existing intervals
 * @return array The list of intervals with our custom values
 * @since  0.1.0
 */
function ases_get_schedules() {

	$schedules = array(
		'60'    => __( 'Every Minute', 'as-email-support' ),
		'300'   => __( 'Every 5 Minutes', 'as-email-support' ),
		'600'   => __( 'Every 10 Minutes', 'as-email-support' ),
		'900'   => __( 'Every 15 Minutes', 'as-email-support' ),
		'1200'  => __( 'Every 20 Minutes', 'as-email-support' ),
		'1800'  => __( 'Every 30 Minutes', 'as-email-support' ),
		'3600'  => __( 'Every Hour', 'as-email-support' ),
		'43200' => __( 'Twice Daily', 'as-email-support' ),
		'86200' => __( 'Once Daily', 'as-email-support' ),
	);

	return $schedules;

}

add_filter( 'wpas_plugin_settings', 'ases_rejection_email_settings', 10, 1 );
/**
 * Add setting to customize the rejected e-mail reply notification e-mail
 *
 * @since 0.2.5
 *
 * @param array $settings Awesome Support settings array
 *
 * @return array
 */
function ases_rejection_email_settings( $settings ) {

	if ( ! isset( $settings['email'] ) ) {
		return $settings;
	}

	$settings['email']['options'][] = array(
		'name' => __( 'E-Mail Reply Rejected', 'as-email-support' ),
		'type' => 'heading',
	);
	
	$settings['email']['options'][] = array(
		'name'    => __( 'Enable', 'awesome-support' ),
		'id'      => 'enable_email_reply_rejected',
		'type'    => 'checkbox',
		'default' => true,
		'desc'    => __( 'Do you want to activate this e-mail template?', 'awesome-support' )
	);

	$settings['email']['options'][] = array(
		'name'    => __( 'Subject', 'as-email-support' ),
		'id'      => 'subject_email_reply_rejected',
		'type'    => 'text',
		'default' => __( 'Reply rejected: {ticket_title}', 'as-email-support' ),
	);

	$settings['email']['options'][] = array(
		'name'     => __( 'Content', 'as-email-support' ),
		'id'       => 'content_email_reply_rejected',
		'type'     => 'editor',
		'default'  => '<p>Hi <strong><em>{client_name},</em></strong></p>The ticket (<a href="{ticket_admin_url}">#{ticket_id}</a>) has been closed, which means it cannot be replied to by e-mail.</p><p>If you believe that the ticket should be re-opened, please log into your account on our site directly and manually re-open the ticket.</p>',
		'settings' => array( 'quicktags' => true, 'textarea_rows' => 7 ),
	);
	
	
	
	
	$settings['email']['options'][] = array(
		'name' => __( 'E-Mail Reply Rejected Because of Ticket Lock', 'as-email-support' ),
		'type' => 'heading',
	);

	$settings['email']['options'][] = array(
		'name'    => __( 'Enable', 'awesome-support' ),
		'id'      => 'enable_email_reply_lock_rejected',
		'type'    => 'checkbox',
		'default' => true,
		'desc'    => __( 'Do you want to activate this e-mail template?', 'awesome-support' )
	);
	
	$settings['email']['options'][] = array(
		'name'    => __( 'Subject', 'as-email-support' ),
		'id'      => 'subject_email_reply_lock_rejected',
		'type'    => 'text',
		'default' => __( 'Reply rejected: {ticket_title}', 'as-email-support' ),
	);

	$settings['email']['options'][] = array(
		'name'     => __( 'Content', 'as-email-support' ),
		'id'       => 'content_email_reply_lock_rejected',
		'type'     => 'editor',
		'default'  => '<p>Hi <strong><em>{client_name},</em></strong></p>The ticket (<a href="{ticket_admin_url}">#{ticket_id}</a>) has been closed and locked, which means it cannot be replied to by e-mail.</p><p>Please open a new ticket instead.</p>',
		'settings' => array( 'quicktags' => true, 'textarea_rows' => 7 ),
	);
	
	
	$settings['email']['options'][] = array(
		'name' => __( 'Unassigned Ticket Created', 'as-email-support' ),
		'type' => 'heading',
	);
	
	$settings['email']['options'][] = array(
		'name'    => __( 'Enable', 'awesome-support' ),
		'id'      => 'enable_unassigned_ticket_created',
		'type'    => 'checkbox',
		'default' => true,
		'desc'    => __( 'Do you want to activate this e-mail template?', 'awesome-support' )
	);

	$settings['email']['options'][] = array(
		'name'    => __( 'Recipients (Comma separated list of emails)', 'as-email-support' ),
		'id'      => 'recipients_email_unassigned_ticket_created',
		'type'    => 'text'
	);
	
	$settings['email']['options'][] = array(
		'name'    => __( 'Subject', 'as-email-support' ),
		'id'      => 'subject_email_unassigned_ticket_created',
		'type'    => 'text',
		'default' => __( 'Unassigned Ticket Created: {ticket_title}', 'as-email-support' ),
	);

	$settings['email']['options'][] = array(
		'name'     => __( 'Content', 'as-email-support' ),
		'id'       => 'content_email_unassigned_ticket_created',
		'type'     => 'editor',
		'default'  => '<p>Hello</p>A new unassigned item has been created.  Please make sure you review new unassigned items in TICKETS->UNASSIGNED! <hr><p>Regards,<br>{site_name}</p>',
		'settings' => array( 'quicktags' => true, 'textarea_rows' => 7 ),
	);

	return $settings;

}