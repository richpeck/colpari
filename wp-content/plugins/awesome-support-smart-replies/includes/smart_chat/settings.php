<?php

add_filter( 'wpas_plugin_settings', 'wpas_smart_chat_settings', 100, 1 );

/**
 * 
 * Add chat bot settings tab
 * 
 * @param array $def
 * 
 * @return array
 */
function wpas_smart_chat_settings( $def ) {
		
	$settings = array(
		'smartchat' => array(
			'name'    => __( 'Smart Chat', 'wpas_chatbot' ),
			'options' =>  array(

			    array(
					'name'    => __( 'Smart Chat', 'wpas_chatbot' ),
					'type'    => 'heading',
					'desc'    => __( 'Smart chat places a chat-like box on designated pages on your website.  Users can ask questions and answers can be returned using an automatic search. <br />' , 'wpas_chatbot' ) . __( 'You can configure the search parameters on the SMART RESPONSES tab.','wpas_chatbot' ),
				),
			    array(
					'name'    => __( 'Basic Configuration', 'wpas_chatbot' ),
					'type'    => 'heading',
				),
			    array(
					'name'    => __( 'Enable Smart Chat On Site', 'wpas_chatbot' ),
					'type'    => 'enable',
					'id'	  => 'sc_enable',
					'default' => wpas_sc_default_option_value( 'sc_enable' ),
					'desc'    => "",
				),
			    array(
					'name'    => __( 'Location', 'wpas_chatbot' ),
					'type'    => 'radio',
					'id'	  => 'sc_bubble_location',
					'desc'    => "",
					'options' => array(
					    
					    'lower_right' => 'Lower Right', 
					    'lower_left'  => 'Lower Left', 
					    'top_right'   => 'Top Right', 
					    'top_left'    => 'Top Left'
					),
					'default' => wpas_sc_default_option_value( 'sc_bubble_location' )
				),			    
			    array(
					'name'    => __( 'Minimized Title', 'wpas_chatbot' ),
					'id'      => 'sc_bubble_minimized_title',
					'type'    => 'text',
					'default' => wpas_sc_default_option_value( 'sc_bubble_minimized_title' ),
					'desc'    => __( 'The title of the bubble', 'wpas_chatbot' ),
				),			    
			    array(
					'name'    => __( 'Chat Box Title', 'wpas_chatbot' ),
					'id'      => 'sc_chat_box_title',
					'type'    => 'text',
					'default' => wpas_sc_default_option_value( 'sc_chat_box_title' ),
					'desc'    => __( 'The title of the chat box when its active', 'wpas_chatbot' ),
				),
			    
			    array(
					'name'    => __( 'Messages', 'wpas_chatbot' ),
					'type'    => 'heading',
				),
			    array(
					'name'    => __( 'Welcome Message', 'wpas_chatbot' ),
					'id'      => 'sc_chat_welcome_message',
					'type'    => 'editor',
					'default' => wpas_sc_default_option_value( 'sc_chat_welcome_message' ),
					'desc'    => __( 'The message that should be shown to the user when the chat box is first activated. TIP: You can also include links to your FAQs, Documentation, Contact form etc.', 'wpas_chatbot' ),
				),
			    array(
					'name'    => __( 'Reply Header', 'wpas_chatbot' ),
					'id'	  => 'sc_smart_chat_reply_header',
					'type'    => 'editor',
					'default' => "",
					'desc'    => __( "Add header text to smart chat reply.", 'wpas_chatbot' )
				),
			    array(
					'name'    => __( 'Reply Footer', 'wpas_chatbot' ),
					'id'      => 'sc_smart_chat_reply_footer',
					'type'    => 'editor',
					'default' => "",
					'desc'    => __( 'Add footer text to smart chat reply.', 'wpas_chatbot' ),
				),
			    array(
					'name'    => __( 'Placeholder Text For Message Box', 'wpas_chatbot' ),
					'id'      => 'sc_chat_box_message',
					'type'    => 'text',
					'default' => wpas_sc_default_option_value( 'sc_chat_box_message' ),
					'desc'    => __( 'Chat box placeholder message', 'wpas_chatbot' ),
				),
			    array(
					'name'    => __( 'Fallback Message', 'wpas_chatbot' ),
					'id'      => 'cbot_smart_chat_fallback_message',
					'type'    => 'textarea',
					'desc'    => __( 'This message will be sent if no post or keywords matches the users message', 'wpas_chatbot' ),
				),

			    array(
					'name'    => __( 'UI Options', 'wpas_chatbot' ),
					'type'    => 'heading',
				),								
			    array(
					'name'    => __( 'Primary Color', 'wpas_chatbot' ),
					'id'      => 'sc_primary_color',
					'type'    => 'color',
					'default' => wpas_sc_default_option_value( 'sc_primary_color' ),
					'desc'    => __( 'The primary color to be used for the chat box and bubble', 'wpas_chatbot' ),
				),
			    array(
					'name'    => __( 'Title Font and Size', 'wpas_chatbot' ),
					'id'      => 'sc_title_font_and_size',
					'type'    => 'font',
					//'show_color' => false,
					'show_font_weight' => false,
					'show_font_style' => false,
					'show_line_height' => false,
					'show_letter_spacing' => false,
					'show_text_transform' => false,
					'show_font_variant' => false,
					'show_text_shadow' => false,
					'show_preview' => false,
					'default' => wpas_sc_default_option_value( 'sc_title_font_and_size' ),
					'desc'    => __( 'The font and size to be used for the title of the box', 'wpas_chatbot' ),
				),
			    array(
					'name'    => __( 'Closing "X" Font and Size', 'wpas_chatbot' ),
					'id'      => 'sc_window_close_button_font_and_size',
					'type'    => 'font',
					//'show_color' => false,
					'show_font_weight' => false,
					'show_font_style' => false,
					'show_line_height' => false,
					'show_letter_spacing' => false,
					'show_text_transform' => false,
					'show_font_variant' => false,
					'show_text_shadow' => false,
					'show_preview' => false,
					'default' => wpas_sc_default_option_value( 'sc_window_close_button_font_and_size' ),
					'desc'    => __( 'The font and size to be used for closing X of the chat window', 'wpas_chatbot' ),
				),
			    array(
					'name'    => __( 'User Font and Size', 'wpas_chatbot' ),
					'id'      => 'sc_user_font_and_size',
					'type'    => 'font',
					//'show_color' => false,
					'show_font_weight' => false,
					'show_font_style' => false,
					'show_line_height' => false,
					'show_letter_spacing' => false,
					'show_text_transform' => false,
					'show_font_variant' => false,
					'show_text_shadow' => false,
					'show_preview' => false,
					'default' => wpas_sc_default_option_value( 'sc_user_font_and_size' ),
					'desc'    => __( 'The font and size to be used for questions asked by the user', 'wpas_chatbot' ),
				),
				
				array(
					'name'    => __( 'Message Field Font and Size', 'wpas_chatbot' ),
					'id'      => 'sc_message_field_font_and_size',
					'type'    => 'font',
					//'show_color' => false,
					'show_font_weight' => false,
					'show_font_style' => false,
					'show_line_height' => false,
					'show_letter_spacing' => false,
					'show_text_transform' => false,
					'show_font_variant' => false,
					'show_text_shadow' => false,
					'show_preview' => false,
					'default' => wpas_sc_default_option_value( 'sc_message_field_font_and_size' ),
					'desc'    => __( 'The font and size to be used for message field', 'wpas_chatbot' ),
				),
				
			    array(
					'name'    => __( 'Answer Font and Size', 'wpas_chatbot' ),
					'id'      => 'sc_answer_font_and_size',
					'type'    => 'font',
					//'show_color' => false,
					'show_font_weight' => false,
					'show_font_style' => false,
					'show_line_height' => false,
					'show_letter_spacing' => false,
					'show_text_transform' => false,
					'show_font_variant' => false,
					'show_text_shadow' => false,
					'show_preview' => false,
					'default' => wpas_sc_default_option_value( 'sc_answer_font_and_size' ),
					'desc'    => __( 'The font and size to be used for answers provided by the bot.', 'wpas_chatbot' ),
				),
			    
			    array(
					'name'    => __( 'Locations', 'wpas_chatbot' ),
					'type'    => 'heading',
				),							    
			    array(
					'name'    => __( 'Show on all pages', 'wpas_chatbot' ),
					'id'      => 'sc_show_on_all_pages',
					'type'    => 'checkbox',
					'default' => wpas_sc_default_option_value( 'sc_show_on_all_pages' ),
					'desc'    => __( 'Show on all pages except excluded urls', 'wpas_chatbot' ),
				),
			    array(
					'name'    => __( 'Include URLs', 'wpas_chatbot' ),
					'id'      => 'sc_include_url',
					'type'    => 'multi-text',
					'default' => '',
					'desc'    => __( 'The URLs where the chat box bubble is active', 'wpas_chatbot' ),
				),
			    array(
					'name'    => __( 'Exclude URLs', 'wpas_chatbot' ),
					'id'      => 'sc_exclude_url',
					'type'    => 'multi-text',
					'default' => '',
					'desc'    => __( 'The URLS where the chat box bubble should not be shown', 'wpas_chatbot' ),
				),
			    
			    array(
					'name'    => __( 'New Ticket Options', 'wpas_chatbot' ),
					'type'    => 'heading',
				),							    
			    array(
					'name'    => __( 'What should the chatbot do when there’s no answer found?', 'wpas_chatbot' ),
					'id'      => 'sc_no_answer_control',
					'type'    => 'radio',
					'default' => wpas_sc_default_option_value( 'sc_no_answer_control' ),
					'options' => array(
					    'open_ticket' => 'Allow user to open ticket',
					    'do_nothing' => 'Do nothing and show the fall-back message.'
					),
					'desc'    => __( 'When no answer is found the chatbot can return the fall-back message or ask the user to open a new ticket.', 'wpas_chatbot' ),
				),
			    array(
					'name'    => __( 'New ticket link message', 'wpas_chatbot' ),
					'id'      => 'sc_new_ticket_link_message',
					'type'    => 'textarea',
					'default' => wpas_sc_default_option_value( 'sc_new_ticket_link_message' ),
					'desc'    => __( 'The message that the user will see if a new ticket needs to be opened.', 'wpas_chatbot' ),
				),				
			    array(
					'name'    => __( 'Include chat content in new ticket', 'wpas_chatbot' ),
					'id'      => 'sc_open_ticket_include_chat_content',
					'type'    => 'checkbox',
					'default' => wpas_sc_default_option_value( 'sc_open_ticket_include_chat_content' ),
					'desc'    => __( 'Whether to automatically include the contents of the chat in the ticket', 'wpas_chatbot' ),
				),
			     array(
					'name'    => __( 'Success message after ticket is created', 'wpas_chatbot' ),
					'id'      => 'sc_open_ticket_success_message',
					'type'    => 'text',
					'default' => wpas_sc_default_option_value( 'sc_open_ticket_success_message' ),
					'desc'    => __( 'This is the message that the user will see after ticket is successfully created', 'wpas_chatbot' ),
				),
			    
			)
		)
	);
	
	
	
	
	return array_merge( $def, $settings );
}