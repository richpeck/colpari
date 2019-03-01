<?php

$location = wpas_sc_get_option( 'sc_bubble_location' );
$minimized_title = wpas_sc_get_option( 'sc_bubble_minimized_title' );
$window_title = wpas_sc_get_option( 'sc_chat_box_title' );


$primary_color = wpas_sc_get_option( 'sc_primary_color' );

$chatbox_message = wpas_sc_get_option( 'sc_chat_box_message' );

?>



<div id="wpas_smart_chat" class="location-<?php echo $location; ?>">
    
	<a href="#" class="wpas_smart_chat_button" style="background-color: <?php echo $primary_color; ?>;"><?php echo $minimized_title; ?></a>
	    
        
	<div class="smart_chat_window">
                <div class="sc_header" style="background-color: <?php echo $primary_color; ?>;">
			<span class="title" style="<?php echo wpas_sc_get_style( 'sc_title_font_and_size' ); ?>"> <?php echo $window_title; ?> </span>
			<span class="ti-close" style="<?php echo wpas_sc_get_style( 'sc_window_close_button_font_and_size' ); ?>">X</span>
                </div>
                <div class="sc_messages">
			<ul class="chat"></ul>
                </div>
                <div class="sc_footer">
			<div class="sc_textbox">
					<input id="btn-input" type="text" style="<?php echo wpas_sc_get_style( 'sc_message_field_font_and_size' ); ?>" class="form-control input-sm message_input" placeholder="<?php echo $chatbox_message; ?>" />
				
				<input type="hidden" class="wpas_sc_nonce" value="<?php echo wp_create_nonce( 'wpas-sc-smartchat-message' ); ?>" />
				
			</div>
			<span class="sc_send_btn"></span>
                </div>
        </div>
        
    
</div>
