<?php

add_filter( 'wpas_plugin_settings', 'wpas_cbot_general_settings', 100, 1 );

/**
 * 
 * Add chat bot settings tab
 * 
 * @param array $def
 * 
 * @return array
 */
function wpas_cbot_general_settings( $def ) {
		
	$settings = array(
		'chatbot' => array(
			'name'    => __( 'FB Chat Bot', 'wpas_chatbot' ),
			'options' =>  array(

			    array(
					'name'    => __( 'Facebook API', 'wpas_chatbot' ),
					'type'    => 'heading',
					'desc'    => __( 'Data needed to connnect to your FB APP.  Plesse see our documentation for more information!' . '<br />' , 'wpas_chatbot' ) . '<a href=https://getawesomesupport.com/documentation/facebook-chat-bot/configure-facebook-connection/>Documentation</a>',
				),
			
			    array(
					'name'    => __( 'Webhook URL', 'wpas_chatbot' ),
					'type'    => 'note',
					'desc'    => WPAS_FB_CBOT::get_webhook_url(),
				),
			    
			    array(
					'name'    => __( 'Verification String', 'wpas_chatbot' ),
					'type'    => 'note',
					'desc'    => WPAS_FB_CBOT::get_verification_string(),
				),
			    
			    array(
					'name'    => __( 'Facebook Page Access Token', 'wpas_chatbot' ),
					'id'      => 'cbot_fb_page_access_token',
					'type'    => 'textarea',
					'desc'    => __( 'Generate page access token from facebook app settings page and paste it here.', 'wpas_chatbot' ),
				)
			    )
		    ) , 
	    
		    'smartresponses' => array(
			
			'name'    => __( 'Smart Responses', 'wpas_chatbot' ),
			'options' =>  array(

			    array(
					'name'    => __( 'Responses', 'wpas_chatbot' ),
					'type'    => 'heading',
					'desc'    => __( 'Configure your responses to users messages.  You can choose to search selected posts or send back a fixed response. <br />' , 'wpas_chatbot' ) . __( 'These settings apply to responses sent back to Facebook Messenger, Smart Chat and Smart Replies (Automatic Ticket Replies)' , 'wpas_chatbot' ),
				),							
			    		    
			    array(
					'name'    => __( 'Text to Send Before Links', 'wpas_chatbot' ),
					'id'      => 'cbot_fb_links_text',
					'type'    => 'text',
					'default' => wpas_cbot_links_text(),
					'desc'    => __( 'This is the text that describes the list of links that are returned to the user. It will be included in search results sent to Facebook Messenger users as well as Smart Replies and Smart Chat', 'wpas_chatbot' ),
				),
			    
			    array(
					'name'    => __( 'Fallback Message', 'wpas_chatbot' ),
					'id'      => 'cbot_fb_fallback_message',
					'type'    => 'textarea',
					'desc'    => __( 'This message will be sent if no post or keywords matches the users message. This only applies to Facebook Messenger users.', 'wpas_chatbot' ),
				),
			    
			    array(
					'name'    => __( 'Posts', 'wpas_chatbot' ),
					'id'      => 'cbot_search_post_types',
					'type'    => 'searchposttype',
					'options' => wpas_cbot_all_post_types(),
					'default' => wpas_cbot_default_response_post_types(),
					'desc'    => __( 'Select post types to search.', 'wpas_chatbot' ),
				),
				
				array(
					'name' => __( 'Google Natural Language', 'wpas_chatbot' ),
					'type' => 'heading',
					'desc' => __( 'You can use the Google Natural Language service to pre-process a message or ticket and extract relevant keywords. These keywords can then be used to drive the search functions.  Using just these keywords, the search functions generally return more relevant results.', 'wpas_chatbot' ),
				),

			    array(
					'name' => __( 'Enable Google Natural Language', 'wpas_chatbot' ),
					'id'   => 'cbot_enable_gnl',
					'type' => 'checkbox',
					'desc' => __( 'Enable', 'wpas_chatbot' ),
				),
				
				array(
					'name' => __( 'Json Config', 'wpas_chatbot' ),
					'id'   => 'cbot_gnl_json_file',
					'type' => 'custom-textarea',
					'desc' => __( 'Provide json config file content here - you can obtain this file from your Google Natural Language account - please see the documentation for more details.', 'wpas_chatbot' ),
				),
				
				array(
					'name'		=> __( 'Salience Score', 'wpas_chatbot' ),
					'id'		=> 'cbot_gnl_salience_score',
					'default'	=> wpas_cbot_get_option( 'cbot_gnl_salience_score' ),
					'desc' => __( 'This is the minimum Salience Score for a keyword to be used, max is 1. The higher a salience score the more likely it is to be a relevant keyword.', 'wpas_chatbot' ),
				),
			    
			    array(
					'name' => __( 'Entity Types', 'wpas_chatbot' ),
					'id'   => 'cbot_gnl_entity_types',
					'type' => 'multicheck',
					'options' => wpas_cbot_entity_types(),
					'default' => wpas_cbot_get_option( 'cbot_gnl_entity_types' ),
					
					'desc' => __( 'Select keyword entity types to use in posts search', 'wpas_chatbot' ),
				),
				
				
				array(
					'name' => __( 'Part of speech', 'wpas_chatbot' ),
					'id'   => 'cbot_gnl_part_of_speech',
					'type' => 'multicheck',
					'options' => wpas_cbot_part_of_speech_options(),
					'desc' => __( 'Select keyword parts of speech to use in posts search', 'wpas_chatbot' ),
				),
			    
				array(
					'name'		=> __( 'Results per keyword', 'wpas_chatbot' ),
					'id'		=> 'cbot_gnl_keyword_results_limit',
					'default'	=> wpas_cbot_get_option( 'cbot_gnl_keyword_results_limit' ),
					'desc' => __( 'Provide max results per keyword', 'wpas_chatbot' ),
				),

			    array(
					'name' => __( 'Dialogflow (API.ai)', 'wpas_chatbot' ),
					'type' => 'heading',
					'desc' => __( 'You can use the Google Dialogflow service (formerly API.ai) to return responses instead of searching WordPress post types. <br />
								Dialogflow is a smart learning engine that uses artificial intelligence to determine appropriate responses to input.', 'wpas_chatbot' ),
				),

			    array(
					'name' => __( 'Enable Dialogflow (API.ai)', 'wpas_chatbot' ),
					'id'   => 'cbot_enable_api_ai',
					'type' => 'checkbox',
					'desc' => __( 'Enabling this will disable searches on post-types. To enable searches on post-types you will need to create a fulfillment hook inside the Google Dialogflow console', 'wpas_chatbot' ),
				),
			    
			    array(
					'name' => __( 'Dialogflow Client Access Token', 'wpas_chatbot' ),
					'id'   => 'cbot_api_ai_client_access_token',
					'type' => 'text',
					'desc' => __( 'Dialogflow (API.AI) Client Access Token', 'wpas_chatbot' ),
				),
			    
			    array(
					'name'    => __( 'Fulfillment Webhook URL', 'wpas_chatbot' ),
					'type'    => 'note',
					'desc'    => WPAS_CBOT_API_AI_Fulfillment::get_webhook_url(),
				),
			    
			    array(
					'name'    => __( 'Dialogflow : Text to Send Before Links', 'wpas_chatbot' ),
					'id'      => 'cbot_apiai_search_links_page_info_text',
					'type'    => 'text',
					'default' => wpas_cbot_apiai_search_links_page_info_text(),
					'desc'    => __( 'This is the text that describes the link that is returned to the user via api.ai', 'wpas_chatbot' ),
				),
			    
			    array(
					'name'    => __( 'Dialogflow : Search page link text', 'wpas_chatbot' ),
					'id'      => 'cbot_apiai_search_links_page_anchor_text',
					'type'    => 'text',
					'default' => wpas_cbot_apiai_search_links_page_anchor_text(),
					'desc'    => __( 'This is the text for search page link', 'wpas_chatbot' ),
				),
				
			    array(
					'name' => __( 'Miscellenaous', 'wpas_chatbot' ),
					'type' => 'heading',
				),
				
			    array(
					'name' => __( 'Similar text match percent', 'wpas_chatbot' ),
					'id'   => 'cbot_similar_text_match_percent',
					'type' => 'number',
					'min' => 1,
					'max' => 100,
					'default' => wpas_cbot_similar_text_match_percent(),
					'desc' => __( 'This setting applies only when matching a keyword using the SIMILARTEXT match function provided with PHP.', 'wpas_chatbot' ),
				),
			    
			    array(
					'name'    => __( 'Enable Log', 'wpas_chatbot' ),
					'id'      => 'cbot_enable_log',
					'type'    => 'checkbox',
					'default' => wpas_cbot_is_log_enabled() ,
					'desc'    => __( 'Enable chat bot logs. ', 'wpas_chatbot' ) . wpas_cbot_log_button()
				),
			    
			    array(
					'name' => __( 'Keywords', 'wpas_chatbot' ),
					'type' => 'heading',
					'desc' => __( 'Use keywords to return specific responses to user input.  
								For example if the user simply enters HI or HELLO you can return a specific response such as HELLO, CHATBOT HERE - HOW CAN I HELP YOU?.  <br />
								You can group similar keywords together if they return the same response - just separate them with a semi-colon. Eg: HELLO;HI;HOWDY ', 'wpas_chatbot' ),
				),
			    
			    array(
					'name' => ' ',
					'type' => 'add-cbot-keyword',
				),
			    
			    array(
					'name'    => __( 'Keywords', 'wpas_chatbot' ),
					'id'      => 'cbot_search_keywords',
					'type'    => 'multi-cbot-keyword',
				)
			)
		)
	);
	
	
	return array_merge( $def, $settings );
}