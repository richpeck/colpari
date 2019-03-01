<?php
add_filter( 'wpas_plugin_settings', 'wpas_settings_notifications', 10, 1 );
/**
 * Add settings for Notifications.
 *
 * @param  (array) $def Array of existing settings
 * @return (array)      Updated settings
 */
function wpas_settings_notifications( $def ) {

	$multiple = boolval( wpas_get_option( 'support_products', false ) );


        

        $options = array(
            array(
			'name'    => __( 'Active Notifications', 'as-instant-notifications' ),
			'type'    => 'heading',
		),
		array(
			'name'    => __( 'Actions', 'as-instant-notifications' ),
			'id'      => 'notification_notify',
			'type'    => 'multicheck',
			'desc'    => __( 'What shall we notify you about?', 'as-instant-notifications' ),
			'options' => as_in_default_notification_actions()
		),

                array(
			'name'    => __( 'Status Actions', 'as-instant-notifications' ),
			'id'      => 'notification_status_notify',
			'type'    => 'multicheck',
			'desc'    => __( 'What shall we notify you about?', 'as-instant-notifications' ),
			'options' => as_in_status_notification_actions()
		),
		array(
			'name'    => __( 'Slack', 'as-instant-notifications' ),
			'type'    => 'heading',
		),
		array(
			'name'    => __( 'Enable', 'as-instant-notifications' ),
			'id'      => 'notification_slack',
			'type'    => 'checkbox',
			'default' => false,
			'desc'    => __( 'Enable Slack notifications', 'as-instant-notifications' )
		),
		array(
			'name'    => __( 'Webhook URL', 'as-instant-notifications' ),
			'id'      => 'notification_slack_webhook',
			'type'    => 'text',
			'default' => '',
			'desc'    => sprintf( __( 'The Webhook URL provided by Slack. Create a new <a href="%s" target="_blank">incoming webhook integration</a> in your Slack team.', 'as-instant-notifications' ), esc_url( 'https://my.slack.com/services/new/incoming-webhook/' ) )
		),
		array(
			'name'    => __( 'Bot Name', 'as-instant-notifications' ),
			'id'      => 'notification_slack_name',
			'type'    => 'text',
			'default' => get_bloginfo( 'name' ),
			'desc'    => __( 'The name that&#39;s going to appear in the Slack chat.', 'as-instant-notifications' )
		),
		array(
			'name'    => __( 'Bot Emoji', 'as-instant-notifications' ),
			'id'      => 'notification_slack_emoji',
			'type'    => 'text',
			'default' => ':zap:',
			'desc'    => sprintf( __( 'The icon emoji to display in the chat. Can be any <a href="%s" target="_blank">emoji available in the Slack conversations</a>.', 'as-instant-notifications' ), esc_url( 'http://www.emoji-cheat-sheet.com/' ) )
		),
		array(
			'name'    => __( 'Bot Custom Icon', 'as-instant-notifications' ),
			'id'      => 'notification_slack_icon',
			'type'    => 'upload',
			'desc'    => __( 'Upload your custom icon for your Slack bot. Images can\'t be larger than 128px in width or height, and must be smaller than 64K in file size.', 'as-instant-notifications' )
		),
		array(
			'name'    => __( 'Pushbullet', 'as-instant-notifications' ),
			'type'    => 'heading',
		),
		array(
			'name'    => __( 'Enable', 'as-instant-notifications' ),
			'id'      => 'notification_pushbullet',
			'type'    => 'checkbox',
			'default' => false,
			'desc'    => __( 'Enable Pushbullet notifications', 'as-instant-notifications' )
		),
		array(
			'type'    => 'note',
			'desc'    => sprintf( __( 'To activate Pushbullet notifications, <a href="%s">please set your access token in your profile</a>.', 'as-instant-notifications' ), admin_url( 'profile.php' ) . '#wpas-pushbullet' )
		),
                array(
			'name'    => __( 'Email', 'as-instant-notifications' ),
			'type'    => 'heading',
		),
		array(
			'name'    => __( 'Enable', 'as-instant-notifications' ),
			'id'      => 'notification_email',
			'type'    => 'checkbox',
			'default' => false,
			'desc'    => __( 'Enable Email notifications', 'as-instant-notifications' )
		)
        );


        // adding Email options dynamically
        $email_sections = array();
        $actions_list = as_in_notification_actions();

        foreach( $actions_list as $action_key => $action_name ) {

                $is_status_action = as_in_is_status_action( $action_key );

                $heading = $is_status_action ? "Status changed email : {$action_name}" : "{$action_name} Email";

                $email_sections[] = array(
                        'name'    => __( $heading, 'as-instant-notifications' ),
                        'type'    => 'heading',
                        'id'      => $heading . rand(1, 99999)
                );

                $email_sections[] = array(
                        'name'    => __( 'Recipient', 'as-instant-notifications' ),
                        'id'      => "{$action_key}__recipient",
                        'type'    => 'text',
                        'default' => '',
                        'desc'    => __( 'Recipients (Comma separated list of emails)', 'as-instant-notifications' )
                );

                $email_sections[] = array(
                        'name'    => __( 'Subject', 'as-instant-notifications' ),
                        'id'      => "{$action_key}__subject",
                        'type'    => 'text',
                        'default' => ''
                );

                $email_sections[] = array(
                        'name'    => __( 'Content', 'as-instant-notifications' ),
                        'id'      => "{$action_key}__content",
                        'type'    => 'editor',
                        'default' => '',
                        'desc'    => __( 'Email Content', 'as-instant-notifications' )
                );
			
			
			if( 'unassigned_ticket_created' != $action_key ) {
				
				
				$_options = maybe_unserialize( get_option( 'wpas_options', array() ) );
			
		
				$additional_party_1_label = isset( $_options[ 'label_for_first_addl_interested_party_email_singular' ] ) ? $_options[ 'label_for_first_addl_interested_party_email_singular' ] : __( 'Additional Interested Party Email #1', 'awesome-support' );
				$additional_party_2_label = isset( $_options[ 'label_for_second_addl_interested_party_email_singular' ] ) ? $_options[ 'label_for_second_addl_interested_party_email_singular' ] : __( 'Additional Interested Party Email #2', 'awesome-support' );

				$email_sections[] = array(
					'name'    => __( 'Who should receive notifications', 'as-instant-notifications' ),
					'id'      => "{$action_key}__active_email_types",
					'type'    => 'multicheck',
					'default' => as_in_default_email_active_types(),
					'options' => array(
						'additional_party_1'	=> $additional_party_1_label,
						'additional_party_2'	=> $additional_party_2_label,
						'primary_agent'		=> __('Primary Agent', 'as-instant-notifications' ),
						'secondary_agent'	=> __('Secondary Agent', 'as-instant-notifications' ),
						'tertiary_agent'	=> __('Tertiary Agent', 'as-instant-notifications' ),
						'additional_emails'	=> __('Additional Emails On Ticket', 'as-instant-notifications' ),
						'additional_users'	=> __('Additional users on ticket', 'as-instant-notifications' ),
						'client'			=> __('Client/Customer', 'as-instant-notifications' ),
					)
				);				
			}
		
        }
		
		// Add in a section to turn on certain test options...		
		$email_sections[] = array(
                        'name'    => __( 'Ajax Settings', 'as-instant-notifications' ),
                        'type'    => 'heading',
                        'id'      => 'notifications_ajax_settings',
						'desc'	  => __('These settings are used for testing and trouble-shooting purposes only!', 'as-instant-notifications' ),
                );
				
		$email_sections[] = array(
                        'name'    => __( 'Ajax Alerts For Email Support', 'as-instant-notifications' ),
                        'id'      => "notifications_ajax_alerts_for_email_support",
                        'type'    => 'checkbox',
                        'default' => false,
                        'desc'    => __( 'Certain email notifications are not sent out if done via AJAX. You can trigger those by turning this flag on. This primarily affects the FETCH button used in the EMAIL SUPPORT add-on.', 'as-instant-notifications' )
                );						
		
		// Merge the dynamic notifications array and testing section array elements into one array...
		$settings = array(
			'notifications'   => array(
				'name'    => __( 'Notifications', 'as-instant-notifications' ),
				'options' => array_merge( $options, $email_sections )
			)
		);
		
	return array_merge( $def, $settings );

}