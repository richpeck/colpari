<?php
add_filter( 'wpas_plugin_settings', 'wpas_core_settings_labels', 5, 1 );
/**
 * Add plugin label settings.
 *
 * @param  array $def Array of existing settings
 *
 * @return array      Updated settings
 */
function wpas_core_settings_labels( $def ) {

	$settings = array(
		'labels' => array(
			'name'    => __( 'Labels', 'wpas_productivity' ),
			'options' => array(
				array(
					'name' => __( 'Column Only Labels', 'wpas_productivity' ),
					'desc' => __( 'These labels affect only the columns in the primary ticket list. They do NOT affect the labels in the single ticket view!', 'wpas_productivity' ),
					'type' => 'heading',
				),
				array(
					'name'    => __( 'Agent', 'wpas_productivity' ),
					'id'      => 'label_for_agent_singular',
					'type'    => 'text',
					'desc'    => __( 'Enter the label that will be used for the agent column header in the main ticket list', 'wpas_productivity' ),
					'default' => 'Agent'
				),
				
				array(
					'name'    => __( 'Status', 'wpas_productivity' ),
					'id'      => 'label_for_status_singular',
					'type'    => 'text',
					'desc'    => __( 'Enter the label that will be used for the status column header in the main ticket list', 'wpas_productivity' ),
					'default' => 'Status'
				),				
				
				array(
					'name' => __( 'List Field Labels (Taxonomy Labels)', 'wpas_productivity' ),
					'desc' => __( 'These labels affect BOTH column headers in the main ticket list AND the labels in the single ticket view!', 'wpas_productivity' ),
					'type' => 'heading',
				),
				
				array(
					'name'    => __( 'Tag - Singular', 'wpas_productivity' ),
					'id'      => 'label_for_ticket_tag_singular',
					'type'    => 'text',
					'desc'    => __( 'Enter the label that will be used to identify the TAG field in your tickets', 'wpas_productivity' ),
					'default' => 'Tag'
				),
				
				array(
					'name'    => __( 'Tag - Plural', 'wpas_productivity' ),
					'id'      => 'label_for_ticket_tag_plural',
					'type'    => 'text',
					'desc'    => __( 'Enter the label that will be used to identify the TAG field in your tickets', 'wpas_productivity' ),
					'default' => 'Tags'
				),

				array(
					'name'    => __( 'Product - Singular', 'wpas_productivity' ),
					'id'      => 'label_for_product_singular',
					'type'    => 'text',
					'desc'    => __( 'Enter the label that will be used to identify the PRODUCT field in your tickets', 'wpas_productivity' ),
					'default' => 'Product'
				),
				
				array(
					'name'    => __( 'Product - Plural', 'wpas_productivity' ),
					'id'      => 'label_for_product_plural',
					'type'    => 'text',
					'desc'    => __( 'Enter the label that will be used to identify the PRODUCT field in your tickets', 'wpas_productivity' ),
					'default' => 'Products'
				),								

				array(
					'name'    => __( 'Department - Singular', 'wpas_productivity' ),
					'id'      => 'label_for_department_singular',
					'type'    => 'text',
					'desc'    => __( 'Enter the label that will be used to identify the DEPARTMENT field in your tickets', 'wpas_productivity' ),
					'default' => 'Department'
				),

				array(
					'name'    => __( 'Department - Plural', 'wpas_productivity' ),
					'id'      => 'label_for_department_plural',
					'type'    => 'text',
					'desc'    => __( 'Enter the label that will be used to identify the DEPARTMENT field in your tickets', 'wpas_productivity' ),
					'default' => 'Departments'
				),		

				array(
					'name'    => __( 'Priority - Singular', 'wpas_productivity' ),
					'id'      => 'label_for_priority_singular',
					'type'    => 'text',
					'desc'    => __( 'Enter the label that will be used to identify the PRIORITY field in your tickets', 'wpas_productivity' ),
					'default' => 'Priority'
				),

				array(
					'name'    => __( 'Priority - Plural', 'wpas_productivity' ),
					'id'      => 'label_for_priority_plural',
					'type'    => 'text',
					'desc'    => __( 'Enter the label that will be used to identify the PRIORITY field in your tickets', 'wpas_productivity' ),
					'default' => 'Priorities'
				),

				array(
					'name'    => __( 'Channel - Singular', 'wpas_productivity' ),
					'id'      => 'label_for_channel_singular',
					'type'    => 'text',
					'desc'    => __( 'Enter the label that will be used to identify the CHANNEL field in your tickets', 'wpas_productivity' ),
					'default' => 'Channel'
				),

				array(
					'name'    => __( 'Channel - Plural', 'wpas_productivity' ),
					'id'      => 'label_for_channel_plural',
					'type'    => 'text',
					'desc'    => __( 'Enter the label that will be used to identify the CHANNEL field in your tickets', 'wpas_productivity' ),
					'default' => 'Channels'
				),				

				array(
					'name' => __( 'Statistics: Reply Count Labels', 'wpas_productivity' ),
					'desc' => __( 'These labels are for the statistics related to reply counts shown in the ticket screen in the back end.  These will probably be rarely used.', 'wpas_productivity' ),
					'type' => 'heading',
				),
				
				array(
					'name'    => __( 'Number of replies by agent', 'wpas_productivity' ),
					'id'      => 'label_for_ttl_replies_by_agent_singular',
					'type'    => 'text',
					'default' => 'Number of Replies By Agent'
				),
				
				array(
					'name'    => __( 'Number of replies by customer', 'wpas_productivity' ),
					'id'      => 'label_for_ttl_replies_by_customer_singular',
					'type'    => 'text',
					'default' => 'Number of Replies By Customer'
				),

				array(
					'name'    => __( 'Total replies on ticket', 'wpas_productivity' ),
					'id'      => 'label_for_ttl_replies_singular',
					'type'    => 'text',
					'default' => 'Total Replies On Ticket'
				),
				
				array(
					'name' => __( 'Labels For Time Tracking Fields', 'wpas_productivity' ),
					'desc' => __( 'These labels are for the time tracking fields shown in the ticket screen in the back end.', 'wpas_productivity' ),
					'type' => 'heading',
				),

				array(
					'name'    => __( 'Gross Time', 'wpas_productivity' ),
					'id'      => 'label_for_gross_time_singular',
					'type'    => 'text',
					'default' => 'Gross Time'
				),

				array(
					'name'    => __( 'Time Adjustments', 'wpas_productivity' ),
					'id'      => 'label_for_time_adjustments_singular',
					'type'    => 'text',
					'default' => 'Time Adjustments'
				),
				
				array(
					'name'    => __( '+ive or -ive Adj?', 'wpas_productivity' ),
					'id'      => 'label_for_time_adjustments_dir_singular',
					'type'    => 'text',
					'default' => '+ive or -ive Adj?'
				),
				
				array(
					'name'    => __( 'Final Time', 'wpas_productivity' ),
					'id'      => 'label_for_final_time_singular',
					'type'    => 'text',
					'default' => 'Final Time'
				),

				array(
					'name'    => __( 'Time Notes', 'wpas_productivity' ),
					'id'      => 'label_for_time_notes_singular',
					'type'    => 'text',
					'default' => 'Notes'
				),
				
				array(
					'name' => __( 'Labels For Additional Interested Party Fields Fields', 'wpas_productivity' ),
					'desc' => __( 'These labels are for the additional interested parties shown in the ticket screen in the back end.', 'wpas_productivity' ),
					'type' => 'heading',
				),
				
				array(
					'name'    => __( 'Name Of Additional Interested Party #1', 'wpas_productivity' ),
					'id'      => 'label_for_first_addl_interested_party_name_singular',
					'type'    => 'text',
					'default' => 'Name Of Additional Interested Party #1'
				),
				
				array(
					'name'    => __( 'Additional Interested Party Email #1', 'wpas_productivity' ),
					'id'      => 'label_for_first_addl_interested_party_email_singular',
					'type'    => 'text',
					'default' => 'Additional Interested Party Email #1'
				),
									
				array(
					'name'    => __( 'Name Of Additional Interested Party #2', 'wpas_productivity' ),
					'id'      => 'label_for_second_addl_interested_party_name_singular',
					'type'    => 'text',
					'default' => 'Name Of Additional Interested Party #2'
				),								
				
				array(
					'name'    => __( 'Additional Interested Party Email #2', 'wpas_productivity' ),
					'id'      => 'label_for_second_addl_interested_party_email_singular',
					'type'    => 'text',
					'default' => 'Additional Interested Party Email #2'
				),
				
				array(
					'name' => __( 'Other Labels', 'wpas_productivity' ),
					'type' => 'heading',
				),
				// Note that the filter function that uses this variable is directly in this productivity add-on instead of in core AS as the other ones are!
				array(
					'name'    => __( 'Attachments', 'wpas_productivity' ),
					'id'      => 'label_for_attachment',
					'type'    => 'text',
					'default' => 'Attachments',
					'desc' => __( 'Label for the attachment field on the front-end ticket form', 'wpas_productivity' ),					
				),
				
			)
		),
	);

	return array_merge( $def, $settings );

}

