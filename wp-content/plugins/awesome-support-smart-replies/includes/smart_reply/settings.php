<?php

add_filter( 'wpas_plugin_settings', 'wpas_smart_reply_settings', 100, 1 );

/**
 * 
 * Add smart replies settings tab
 * 
 * @param array $def
 * 
 * @return array
 */
function wpas_smart_reply_settings( $def ) {
	
	
		
	$settings = array(
		'smartreplies' => array(
			'name'    => __( 'Smart Replies', 'wpas_chatbot' ),
			'options' =>  array(

			    array(
					'name'    => __( 'Smart Replies', 'wpas_chatbot' ),
					'type'    => 'heading',
					'desc'    => __( 'Smart replies will take new tickets that are received and send its contents through our search process in order to create an automatic reply that will be added to the ticket.', 'wpas_chatbot' ),
				),
			
			    array(
					'name'    => __( 'Enable', 'wpas_chatbot' ),
					'type'    => 'enable',
					'id'	  => 'sc_smart_replies_enabled',
					'default' => wpas_sc_default_option_value( 'sc_smart_replies_enabled' ),
					'desc'    => "",
				),
			    
			    array(
					'name'    => __( 'Enable Manual', 'wpas_chatbot' ),
					'type'    => 'enable',
					'id'	  => 'sc_smart_replies_manual_enabled',
					'default' => wpas_sc_default_option_value( 'sc_smart_replies_manual_enabled' ),
					'desc'    => __( "Enable manual smart replies." , 'wpas_chatbot' ),
				),

			    array(
					'name'    => __( 'Send Fall-back Message', 'wpas_chatbot' ),
					'type'    => 'enable',
					'id'	  => 'sc_smart_replies_send_fallback_message',
					'default' => false,
					'desc'    => "Send the fall-back message below even when there are no search results.",
				),
			    
			    array(
					'name'    => __( 'Fallback Message', 'wpas_chatbot' ),
					'id'      => 'cbot_smart_replies_fallback_message',
					'type'    => 'textarea',
					'default' => wpas_sc_default_option_value( 'sc_smart_replies_fallback_message' ),
					'desc'    => __( 'This message will be sent if no post or keywords matches the users message and the above checkbox is enabled', 'wpas_chatbot' ),
				),
			    
			    array(
					'name'    => __( 'Reply Header', 'wpas_chatbot' ),
					'id'	  => 'sc_smart_replies_reply_header',
					'type'    => 'editor',
					'default' => wpas_sc_default_option_value( 'sc_smart_replies_header_message' ),
					'desc'    => __( "Content/text that will be added to the top of the search results.", 'wpas_chatbot' )
				),
			    
			    array(
					'name'    => __( 'Reply Footer', 'wpas_chatbot' ),
					'id'      => 'sc_smart_replies_reply_footer',
					'type'    => 'editor',
					'default' => wpas_sc_default_option_value( 'sc_smart_replies_footer_message' ),
					'desc'    => __( 'Content/text that will be added to the bottom of the search results.', 'wpas_chatbot' ),
				),
				
			    array(
					'name' => __( 'Show Google Natural Languge Tags Below Ticket', 'wpas_chatbot' ),
					'id'   => 'cbot_gnl_show_tags_below_ticket',
					'type' => 'checkbox',
					'desc' => __( 'Enable', 'wpas_chatbot' ),
					'default' => true ,
				),				
				
			)
		)
	);
	
	
	
	
	return array_merge( $def, $settings );
}