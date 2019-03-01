<?php
/**
 *
 * @package   Awesome Support: Satisfaction Survey
 * @author    Robert W. Kramer III for Awesome Support <support@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016. Awesome Support
 *
 */

/**
 * Add settings for Satisfaction Survey integration.
 *
 * @param  (array) $def Array of existing settings
 *
 * @return (array)      Updated settings
 *
 */
add_filter( 'wpas_plugin_settings', 'wpasss_settings', 10, 1 );

function wpasss_settings( $def ) {

	$settings = array(
		'satisfaction_survey' => array(
			'name'    => __( 'Satisfaction Survey', 'wpas-ss' ),
			'options' => array(
				array(
					'name' => __( 'Important Configuration Notes', 'wpas-ss' ),
					'type' => 'heading',
					'desc' => __( 'After installation you <b>MUST</b> go to your WordPress <b>SETTINGS->PERMALINKS</b> page and click the Save button at least once!  Otherwise users who click your survey links will encounter a \'page not found\' error!', 'wpas-ss' ),					
				),
				array(
					'name' => __( 'Survey Behavior', 'wpas-ss' ),
					'type' => 'heading',
					'desc'    => __( 'Please select only ONE of the following three options! (We will allow combinations in a future release.)', 'wpas-ss' ),
				),
				array(
					'name'    => __( 'Send By E-mail', 'wpas-ss' ),
					'id'      => 'ss_type_send_by_email',
					'type'    => 'checkbox',
					'default' => true,
					'desc'    => __( 'Send survey via e-mail after ticket is closed?', 'wpas-ss' ),
				),
				array(
					'name'    => __( 'Pop-up after close', 'wpas-ss' ),
					'id'      => 'ss_type_pop_up_after_close',
					'type'    => 'checkbox',
					'default' => true,
					'desc'    => __( 'Show the survey immediately after close. <b>Important Note:</b> Not compatible with the click-to-close template tags in our powerpack/productivity add-on!', 'wpas-ss' ),
				),
				array(
					'name'    => __( 'Survey Links In Agent Replies', 'wpas-ss' ),
					'id'      => 'ss_enable_survey_links_in_agent_replies',
					'type'    => 'checkbox',
					'default' => false,
					'desc'    => __( 'Enable certain quick-survey links in outgoing agent replies (requires you to also add the proper template tags to the agent reply notifications in TICKETS->SETTINGS->EMAILS.  Please see the user manual for more information.)', 'wpas-ss' ),
				),				
				
				array(
					'name' => __( 'Miscellaneous', 'wpas-ss' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'Delay', 'wpas-ss' ),
					'id'      => 'ss_email_send_delay',
					'type'    => 'number',
					'min'     => 0,
					'max'     => ( 14 * 24 * 60 ),
					'default' => ( 24 * 60 ),
					'unit'    => 'minutes',
					'size'    => 'medium',
					'step'    => 1,
					'desc'    => __( 'Delay in minutes after ticket is closed before sending Satisfaction Survey email.'
					                 . '<br/>'
					                 . 'Default is 1440 (24 hours). Maximum is 20160 (14 days).<br/>',
						'wpas-ss' ),
				),
				array(
					'name'    => __( 'Delete survey on ticket reopen', 'wpas-ss' ),
					'id'      => 'ss_delete_existing_survey_on_reopen',
					'type'    => 'checkbox',
					'default' => true,
					'desc'    => __( 'Delete existing satisfaction survey when ticket is reopened?', 'wpas-ss' ),
				),
				array(
					'name'    => __( 'Display Rating column', 'wpas-ss' ),
					'id'      => 'ss_display_rating_column',
					'type'    => 'checkbox',
					'default' => true,
					'desc'    => __( 'Display rating column on ticket lists page?', 'wpas-ss' ),
				),
				
				array(
					'name' => __( 'Slugs (custom urls)', 'wpas-ss' ),
					'type' => 'heading',
					'desc' => __( 'If you change an item in this section then you must go to your WordPress SETTINGS->PERMALINKS page and click the Save button at least once!', 'wpas-ss' ),
				),				
				array(
					'name'    => __( 'Slug For Main Survey', 'wpas-ss' ),
					'id'      => 'ss_slug',
					'type'    => 'text',
					'default' => 'satisfaction-survey',
					'desc'    => __( 'The slug used to access the Satisfaction Survey. Defaults to satisfaction-survey', 'wpas-ss' ),
				),
				array(
					'name'    => __( 'Slug For Thumbs Up', 'wpas-ss' ),
					'id'      => 'ss_slug_quick_link_thumbs_up',
					'type'    => 'text',
					'default' => 'satisfaction-survey-thumbs-up',
					'desc'    => __( 'The slug used to access the Satisfaction Survey thumbs-up quick-link. Defaults to satisfaction-survey-thumbs-up', 'wpas-ss' ),
				),
				array(
					'name'    => __( 'Slug For Thumbs Down', 'wpas-ss' ),
					'id'      => 'ss_slug_quick_link_thumbs_down',
					'type'    => 'text',
					'default' => 'satisfaction-survey-thumbs-down',
					'desc'    => __( 'The slug used to access the Satisfaction Survey thumbs-down quick-link. Defaults to satisfaction-survey-thumbs-down', 'wpas-ss' ),
				),
				array(
					'name'    => __( 'Slug For Click-To-Close With Thumbs Up', 'wpas-ss' ),
					'id'      => 'ss_slug_quick_link_click_to_close_thumbs_up',
					'type'    => 'text',
					'default' => 'satisfaction-survey-click-to-close-thumbs-up',
					'desc'    => __( 'The slug used to close the ticket assign a rating of 100%. Defaults to satisfaction-survey-click-to-close-thumbs-up', 'wpas-ss' ),
				),
				array(
					'name'    => __( 'Slug For Click-To-Close With Thumbs Down', 'wpas-ss' ),
					'id'      => 'ss_slug_quick_link_click_to_close_thumbs_down',
					'type'    => 'text',
					'default' => 'satisfaction-survey-click-to-close-thumbs-down',
					'desc'    => __( 'The slug used to close the ticket assign a rating of (zero) 0%. Defaults to satisfaction-survey-click-to-close-thumbs-down', 'wpas-ss' ),
				),
				
				
				array(
					'name' => __( 'Rating Scale', 'wpas-ss' ),
					'type' => 'heading',
					'desc' => 'The rating scale is used for a standard form survey.  It is not used for the thumbs-up or thumbs-down link (good/bad quick-links)',
				),
				array(
					'name'    => __( 'Degrees', 'wpas-ss' ),
					'id'      => 'ss_rating_scale',
					'type'    => 'select',
					'default' => '2',
					'options' => array(
						'2'  => __( '2', 'wpas-ss' ),
						'5'  => __( '5', 'wpas-ss' ),
						'10' => __( '10', 'wpas-ss' ),
					),
					'desc'    => __( 'How many degrees of rating presented to client.', 'wpas-ss' ),
				),
				array(
					'name'    => __( 'Bad Label', 'wpas-ss' ),
					'id'      => 'ss_bad_label',
					'type'    => 'text',
					'default' => 'Bad',
					'desc'    => __( 'Label displayed at bad end of rating scale. Can be blank for no label.', 'wpas-ss' ),
				),
				array(
					'name'    => __( 'Good Label', 'wpas-ss' ),
					'id'      => 'ss_good_label',
					'type'    => 'text',
					'default' => 'Good',
					'desc'    => __( 'Label displayed at good end of rating scale. Can be blank for no label.'
					                 . '<br/><br/><div>'
					                 . '<p><strong>CSS Classes and Ids:</strong></p>'
					                 . '<p>'
					                 . '<strong>#rating_form</strong> - '
					                 . '<br/><strong>.scale_label_bad</strong> - '
					                 . '<br/><strong>.scale_label_good</strong> - '
					                 . '<br/><strong>.scale_input_radio</strong> - '
					                 . '<br/><strong>.scale_unsatisifed_reasons</strong> - '
					                 . '<br/><strong>.survey_comment</strong> - '
					                 . '<br/><strong>.rating_choices</strong> - '
					                 . '<br/><strong>.scale_2</strong> - '
					                 . '<br/><strong>.scale_5</strong> - '
					                 . '<br/><strong>.scale_10</strong> - '
					                 . '</p>'
					                 . '</div>'
						, 'wpas-ss' ),
				),
				array(
					'name' => __( 'Unsatisfied Reasons', 'wpas-ss' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'Dropdown Trigger', 'wpas-ss' ),
					'id'      => 'ss_unsatisfied_reasons_dropdown_trigger',
					'type'    => 'number',
					'min'     => 0,
					'max'     => 100,
					'default' => '50',
					'unit'    => 'percent',
					'size'    => 'medium',
					'step'    => 1,
					'desc'    => __( 'Percentage-based threshold determines when Unsatisified Reasons dropdown is triggered.', 'wpas-ss' ),
				),
				array(
					'name'    => __( 'Unsatisfied Reasons', 'wpas-ss' ),
					'id'      => 'ss_unsatisfied_reasons',
					'type'    => 'textarea',
					'default' => __( "The issue took too long to resolve." . chr( 0x0D ) . chr( 0x0A )
					                 . "The agent's knowledge was unsatisfactory." . chr( 0x0D ) . chr( 0x0A )
					                 . "The agent's attitude was unsatisfactory." . chr( 0x0D ) . chr( 0x0A )
					                 . "The issue was not resolved." . chr( 0x0D ) . chr( 0x0A )
					                 . "Other", 'wpas-ss' ),
					'desc'    => 'Enter the main reasons presented to client. (One reason per line.)',
				),
				array(
					'name' => __( 'Email Template', 'wpas-ss' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'Subject', 'wpas-ss' ),
					'id'      => "subject_satisfaction_survey_email_message",
					'type'    => 'text',
					'default' => __( 'Satisfaction Survey: {ticket_title}', 'wpas-ss' ),
					'desc'    => __( 'Subject of Satisfaction Survey email sent to client.', 'wpas-ss' ),
				),
				array(
					'name'     => __( 'Content', 'wpas-ss' ),
					'id'       => "content_satisfaction_survey_email_message",
					'type'     => 'editor',
					'default'  => __( '<p>Hi <strong><em>{client_name}</em></strong>, the following inquiry has been closed:</p>'
					                  . '<p>'
					                  . '<span style="width: 75px; margin: 10px 20px 5px 20px;">Ticket ID</span>: <a href="{ticket_url}">#{ticket_id}</a><br/>'
					                  . '<span style="width: 75px; margin: 10px 20px 5px 20px;">Ticket Title</span>: <a href="{ticket_url}">{ticket_title}</a><br/>'
					                  . '</p>'
					                  . '<p>Please take a moment to let us know how well our staff performed in helping you.</p>'
					                  . '<p>'
					                  . '<a href="{satisfaction_survey_url}" >'
					                  . '<span style="color:green;margin:3px 5px;float:left;line-height:1em;">Bad</span>'
					                  . '<input type="radio" name="" style="margin:5px 10px;float:left;line-height:1em;" />'
					                  . '<input type="radio" name="" style="margin:5px 10px;float:left;line-height:1em;" />'
					                  . '<input type="radio" name="" style="margin:5px 10px;float:left;line-height:1em;" />'
					                  . '<input type="radio" name="" style="margin:5px 10px;float:left;line-height:1em;" />'
					                  . '<input type="radio" name="" style="margin:5px 10px;float:left;line-height:1em;" />'
					                  . '<span style="color:red;margin:3px 5px;float:unset;line-height:1em;">Good</span>'
					                  . '</a>'
					                  . '</p>'
					                  . '<p><br/></p>'
					                  . '<p>Regards,</p>'
					                  . '<p><br/><br/>{site_name}</p>', 'wpas-ss' ),
					'settings' => array( 'quicktags' => true, 'textarea_rows' => 7 ),
					'desc'     => __( 'Message content of Satisfaction Survey email sent to client.' , 'wpas-ss' ),
				),
				array(
					'name'     => __( 'Valid Template Tags', 'wpas-ss' ),
					'id'       => "wpass_valid_template_tags",
					'type'     => 'note',
					'desc'     => __( 'The valid template tags that can be used in the email above are:'
					                  . '<br/><br/><div>'
					                  . '<strong>{ticket_id}</strong>, '
					                  . '<strong>{site_name}</strong>, '
					                  . '<strong>{agent_name}</strong>, '
					                  . '<strong>{agent_email}</strong>, '
					                  . '<strong>{client_name}</strong>, '
					                  . '<strong>{client_email}</strong>, '
					                  . '<strong>{ticket_title}</strong>, '
					                  . '<strong>{ticket_link}</strong>, '
					                  . '<strong>{ticket_url}</strong>, '
					                  . '<strong>{date}</strong>, '
					                  . '<strong>{admin_email}</strong>, '
					                  . '<strong>{message}</strong>, '
					                  . '<strong>{satisfaction_survey_link}</strong>, '
					                  . '<strong>{satisfaction_survey_url}</strong>, '
									  . '<br/><br/>'
									  . '<i>Quick Rating Links (one-click to rate 0 or 100):</i>'
									  . '<br/><br/>'
									  . '<strong>{satisfaction_survey_thumbs_up_link}</strong>, '
									  . '<strong>{satisfaction_survey_thumbs_up_url}</strong>, '
									  . '<strong>{satisfaction_survey_thumbs_down_link}</strong>, '
									  . '<strong>{satisfaction_survey_thumbs_down_url}</strong>,'
									  . '<br/><br/>'
									  . '<i>Quick Links that will close a ticket (these can also be used in agent reply emails in TICKETS->SETTINGS->EMAILS):</i>'
									  . '<br/><br/>'
									  . '<strong>{satisfaction_survey_close_ticket_with_thumbs_up_url}</strong>,'
									  . '<strong>{satisfaction_survey_close_ticket_with_thumbs_down_url}</strong>,'
									  . '<strong>{satisfaction_survey_close_ticket_with_thumbs_up_link}</strong>,'
									  . '<strong>{satisfaction_survey_close_ticket_with_thumbs_down_link}</strong>'
					                  . '</p>'
					                  . '</div>'
						, 'wpas-ss' ),
				),
				
				array(
					'name' => __( 'Thank You Confirmation Messages', 'wpas-ss' ),
					'type' => 'heading',
				),
				
				array(
					'name'     => __( 'Thank You Message For Primary Survey Form', 'wpas-ss' ),
					'id'       => "wpass_thank_you_main_survey_form",
					'type'     => 'editor',
					'default'  => __('Thank you for your feedback - It is very much appreciated!', 'wpas-ss' ),
				),
				array(
					'name'     => __( 'Thank You Message For Thumbs-up Quick Link', 'wpas-ss' ),
					'id'       => "wpass_thank_you_thumbs_up",
					'type'     => 'editor',
					'default'  => __('Thank you for letting us know we did a great job!', 'wpas-ss' ),
				),
				array(
					'name'     => __( 'Thank You Message For Thumbs-down Quick Link', 'wpas-ss' ),
					'id'       => "wpass_thank_you_thumbs_down",
					'type'     => 'editor',
					'default'  => __('We apologize for your bad experience and thank you for your feedback - it is very much appreciated! We will use your input to continue to improve our support operations.', 'wpas-ss' ),
				),
				array(
					'name'     => __( 'Thank You Message For Closing A Ticket With 100% rating', 'wpas-ss' ),
					'id'       => "wpass_thank_you_close_ticket_with_thumbs_up",
					'type'     => 'editor',
					'default'  => __('Thank you for closing your ticket and for letting us know we did a great job!', 'wpas-ss' ),
				),
				array(
					'name'     => __( 'Thank You Message For Closing A Ticket With 0% rating', 'wpas-ss' ),
					'id'       => "wpass_thank_you_close_ticket_with_thumbs_down",
					'type'     => 'editor',
					'default'  => __('Thank you for closing your ticket - it helps streamline our operations.  We apologize that you have had a bad experience and thank you for your feedback - it is very much appreciated! We will use your input to continue to improve our support operations.', 'wpas-ss' ),
				),				
			),
		),
	);

	return array_merge( $def, $settings );
}

/**
 * Verify defaults for all settings
 */
function wpas_ss_set_default_options() {

	$options = array(
		'ss_email_send_delay'                        => ( 24 * 60 ),
		'ss_delete_existing_survey_on_reopen'        => true,
		'ss_display_rating_column'                   => true,
		'ss_slug'                                    => 'satisfaction-survey',
		'ss_rating_scale'                            => '2',
		'ss_bad_label'                               => 'Bad',
		'ss_good_label'                              => 'Good',
		'ss_unsatisfied_reasons_dropdown_trigger'    => '50',
		'ss_unsatisfied_reasons'                     => __( "The issue took too long to resolve." . chr( 0x0D ) . chr( 0x0A )
		                                                    . "The agent's knowledge was unsatisfactory." . chr( 0x0D ) . chr( 0x0A )
		                                                    . "The agent's attitude was unsatisfactory." . chr( 0x0D ) . chr( 0x0A )
		                                                    . "The issue was not resolved." . chr( 0x0D ) . chr( 0x0A )
		                                                    . "Other", 'wpas-ss' ),
		'enable_satisfaction_survey'                 => true,
		'subject_satisfaction_survey_email_message'  => __( 'Satisfaction Survey: {ticket_title}', 'wpas-ss' ),
		'content_satisfaction_survey_email_message'  => __( '<p>Hi <strong><em>{client_name}</em></strong>, the following inquiry has been closed:</p>'
		                                                    . '<p>'
		                                                    . '<span style="width: 75px; margin: 10px 20px 5px 20px;">Ticket ID</span>: <a href="{ticket_url}">#{ticket_id}</a><br/>'
		                                                    . '<span style="width: 75px; margin: 10px 20px 5px 20px;">Ticket Title</span>: <a href="{ticket_url}">{ticket_title}</a><br/>'
		                                                    . '</p>'
		                                                    . '<p>Please take a moment to let us know how well our staff performed in helping you.</p>'
		                                                    . '<p>'
		                                                    . '<a href="{satisfaction_survey_url}" >'
		                                                    . '<span style="color:green;margin:3px 5px;float:left;line-height:1em;">Bad</span>'
		                                                    . '<input type="radio" name="" style="margin:5px 10px;float:left;line-height:1em;" />'
		                                                    . '<input type="radio" name="" style="margin:5px 10px;float:left;line-height:1em;" />'
		                                                    . '<input type="radio" name="" style="margin:5px 10px;float:left;line-height:1em;" />'
		                                                    . '<input type="radio" name="" style="margin:5px 10px;float:left;line-height:1em;" />'
		                                                    . '<input type="radio" name="" style="margin:5px 10px;float:left;line-height:1em;" />'
		                                                    . '<span style="color:red;margin:3px 5px;float:unset;line-height:1em;">Good</span>'
		                                                    . '</a>'
		                                                    . '</p>'
		                                                    . '<p><br/></p>'
		                                                    . '<p>Regards,</p>'
		                                                    . '<p><br/><br/>{site_name}</p>', 'wpas-ss' ),
	);


	foreach ( $options as $key => $option ) {
		wpas_update_option( $key, $option, true );
	}
}
