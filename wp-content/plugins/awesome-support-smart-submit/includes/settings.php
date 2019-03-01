<?php

add_filter( 'wpas_plugin_settings', 'wpas_smart_submit_settings', 100, 1 );

/**
 * 
 * Add smart submit settings tab
 * 
 * @param array $def
 * 
 * @return array
 */
function wpas_smart_submit_settings( $def ) {
		
	$settings = array(
		'smartsubmit' => array(
			'name'    => __( 'Smart Submit', 'wpas_ss' ),
			'options' =>  array(
			
			    array(
					'name'    => __( 'Smart Submit', 'wpas_ss' ),
					'type'    => 'heading',
					'desc'    => __( 'Smart Submit requires the user to look at categories from FAQ, Documentation or Posts and/or perform a keyword search before submitting a ticket.  It is divided into two stages which can be turned on or off by the administrator.', 'wpas_ss' ),
				),
				array(
					'name'    => __( 'Enable Smart Submit', 'wpas_ss' ),
					'type'    => 'enable',
					'id'	  => 'ss_smart_submit_enable',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_enable' ),
				),				

			    array(
					'name'    => __( 'Stage 1', 'wpas_ss' ),
					'type'    => 'heading',
					'desc'    => __( 'Stage 1 of the smart submit sequence shows the user a list of categories and topics before allowing the user to proceed to open a new ticket', 'wpas_ss' ),
				),
				
				array(
					'name'    => __( 'Enable Stage 1', 'wpas_ss' ),
					'type'    => 'enable',
					'id'	  => 'ss_smart_submit_stage_1_enabled',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_stage_1_enabled' ),
				),
			
			    array(
					'name'    => __( 'Categories taken from:', 'wpas_ss' ),
					'type'    => 'multicheck',
					'id'	  => 'ss_smart_submit_category_objects',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_category_objects' ),
					'options' => array(
						'faq'			=> 'FAQ',
						'documentation' => 'Documentation',
						'post'			=> 'Posts'
					)
				),				
				array(
					'name'    => __( 'Categories present as:', 'wpas_ss' ),
					'type'    => 'radio',
					'id'	  => 'ss_smart_submit_categories_view_type',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_categories_view_type' ),
					'options' => array(
						'dropdown'		=> 'Dropdown',
						'radio'			=> 'Radio Buttons',
						'accordion'		=> 'Accordion',
						'links'			=> 'Links'
					)
				),

			    array(
					'name'    => __( 'Stage 2', 'wpas_ss' ),
					'type'    => 'heading',
					'desc'    => __( 'Stage 2 of the smart submit sequence requires that the user perform a keyword search before showing the ticket screen', 'wpas_ss' ),
				),
				
			    array(
					'name'    => __( 'Enable Stage 2', 'wpas_ss' ),
					'type'    => 'enable',
					'id'	  => 'ss_smart_submit_stage_2_enabled',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_stage_2_enabled' ),
				),
				array(
					'name'    => __( 'Post Types', 'wpas_ss' ),
					'id'      => 'ss_smart_submit_search_post_types',
					'type'    => 'multicheck',
					'options' => wpas_ss_all_post_types(),
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_search_post_types' ),
					'desc'    => __( 'Select the post types to search.', 'wpas_ss' ),
				),
				
			    array(
					'name'    => __( 'Headers and Footers', 'wpas_ss' ),
					'type'    => 'heading',
				),
			    array(
					'name'    => __( 'Text Before Stage 1 ', 'wpas_ss' ),
					'type'    => 'editor',
					'id'	  => 'ss_smart_submit_text_b4_stage1',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_text_b4_stage1' ),
					'desc'    => __( 'Use this text to let the user know what to expect before submitting a ticket', 'wpas_ss' ),
				),
			    array(
					'name'    => __( 'Text Before Stage 2 ', 'wpas_ss' ),
					'type'    => 'editor',
					'id'	  => 'ss_smart_submit_text_b4_stage2',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_text_b4_stage2' ),
					'desc'    => __( 'Use this text to let the user know what to expect during stage 2', 'wpas_ss' ),
				),
				
				
				array(
					'name'    => __( 'Text to Send Before Links', 'wpas_ss' ),
					'id'      => 'ss_smart_submit_text_before_links',
					'type'    => 'text',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_text_before_links' ),
					'desc'    => __( 'This is the text that describes the list of links that are returned to the user. It will be included in search results sent to Facebook Messenger users as well as Smart Replies and Smart Chat', 'wpas_ss' ),
				),
				
				array(
					'name'    => __( 'Smart Replies Search Links Header', 'wpas_ss' ),
					'id'	  => 'ss_smart_submit_reply_header',
					'type'    => 'editor',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_reply_header' ),
					'desc'    => __( "When the search is run through the Smart Replies add-on algorithms, this is the content/text that will be added to the top of the search results.", 'wpas_ss' )
				),
			    
			    array(
					'name'    => __( 'Smart Replies Search Links Footer', 'wpas_ss' ),
					'id'      => 'ss_smart_submit_reply_footer',
					'type'    => 'editor',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_reply_footer' ),
					'desc'    => __( 'When the search is run through the Smart Replies add-on algorithms, this is the content/text that will be added to the bottom of the search results.', 'wpas_ss' ),
				),	
				
				array(
					'name'    => __( 'Labels and Text', 'wpas_ss' ),
					'type'    => 'heading',
					'desc'    => __( 'Set the text and labels used for every stage. If you are using multiple languages then leave these fields blank - the default text will be in your language files where you can translate them.', 'wpas_ss' ),
				),
			    array(
					'name'    => __( 'Select a Category', 'wpas_ss' ),
					'type'    => 'text',
					'id'	  => 'ss_smart_submit_text_category',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_text_category' ),
				),
			    array(
					'name'    => __( 'Select a Topic', 'wpas_ss' ),
					'type'    => 'text',
					'id'	  => 'ss_smart_submit_text_topic',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_text_topic' ),
				),
			    array(
					'name'    => __( 'Please Select', 'wpas_ss' ),
					'type'    => 'text',
					'id'	  => 'ss_smart_submit_text_please_select',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_text_please_select' ),
				),
			    array(
					'name'    => __( 'Did This Fix Your Issue', 'wpas_ss' ),
					'type'    => 'text',
					'id'	  => 'ss_smart_submit_text_fix_issue',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_text_fix_issue' ),
				),
			    array(
					'name'    => __( 'Yes', 'wpas_ss' ),
					'type'    => 'text',
					'id'	  => 'ss_smart_submit_text_yes',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_text_yes' ),
				),
			    array(
					'name'    => __( 'No', 'wpas_ss' ),
					'type'    => 'text',
					'id'	  => 'ss_smart_submit_text_no',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_text_no' ),
				),
			    array(
					'name'    => __( 'Search', 'wpas_ss' ),
					'type'    => 'text',
					'id'	  => 'ss_smart_submit_text_search',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_text_search' ),
				),
			    array(
					'name'    => __( 'Search Button', 'wpas_ss' ),
					'type'    => 'text',
					'id'	  => 'ss_smart_submit_text_search_btn',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_text_search_btn' ),
				),
			    array(
					'name'    => __( 'Search Button', 'wpas_ss' ),
					'type'    => 'text',
					'id'	  => 'ss_smart_submit_text_open_tkt',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_text_open_tkt' ),
				),				
				array(
					'name'    => __( 'Enable A Special <i>No Search Results</i> Found Message', 'wpas_ss' ),
					'type'    => 'enable',
					'id'	  => 'ss_smart_submit_send_fallback_message',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_send_fallback_message' ),
					'desc'    => __( 'Send the message below when there are no search results.', 'wpas_ss' ),
				),
			    
			    array(
					'name'    => __( 'Fallback Message', 'wpas_ss' ),
					'id'      => 'ss_smart_submit_fallback_message',
					'type'    => 'textarea',
					'default' => wpas_ss_default_option_value( 'ss_smart_submit_fallback_message' ),
					'desc'    => __( 'This message will be sent if the above checkbox is enabled and search results are found', 'wpas_ss' ),
				),								
				
				
			)
		)
	);
	
	return array_merge( $def, $settings );
}