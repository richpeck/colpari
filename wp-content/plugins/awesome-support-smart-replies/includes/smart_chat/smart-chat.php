<?php

add_action( 'wp_footer', 'load_smart_chat', 25 );

/**
 * Add smart chat window
 */
function load_smart_chat() {
	
	if( wpas_sc_is_smart_chat_enabled() && sc_is_url_allowed_smart_chat() && !sc_is_url_disallowed_smart_chat() ) {
		wpas_sc_message_item_template();
		include WPAS_CBOT_PATH . 'views/smart_chat.php';
	}
}

/**
 * Check if smart chat is enabled
 * 
 * @return boolean
 */
function wpas_sc_is_smart_chat_enabled() {
	
	return wpas_sc_get_option( 'sc_enable' );
}


add_action('wp_enqueue_scripts', 'wpas_sc_enqueue' );
/**
 * Enqueue javascript and css files
 */
function wpas_sc_enqueue() {
	
	wp_enqueue_style( 'pub_style', WPAS_CBOT_URL . 'assets/css/public/style.css' );
	
	wp_enqueue_script( 'wp-util' );
	
	add_thickbox();
	
	wp_enqueue_script( 'sc_pub_script', WPAS_CBOT_URL . 'assets/js/public/script.js', array( 'jquery', 'wp-util' ) );
	
	$localize_script = array(
	    
		'enable'		  			=> wpas_sc_get_option( 'sc_enable' ),
		'bubble_location'	  		=> wpas_sc_get_option( 'sc_bubble_location' ),
		'bubble_minimized_title'  	=> wpas_sc_get_option( 'sc_bubble_minimized_title' ),
		'chat_box_title'	  		=> wpas_sc_get_option( 'sc_chat_box_title' ),
		'chat_welcome_message'    	=> stripslashes( wpas_sc_get_option( 'sc_chat_welcome_message' ) ),
		'primary_color'		  		=> wpas_sc_get_option( 'sc_primary_color' ),
		'title_font_and_size'     	=> wpas_sc_get_option( 'sc_title_font_and_size' ),
		'user_font_and_size'      	=> wpas_sc_get_option( 'sc_user_font_and_size' ),
		'answer_font_and_size'    	=> wpas_sc_get_option( 'sc_answer_font_and_size' ),
		'no_answer_control'	  		=> wpas_sc_get_option( 'sc_no_answer_control'),
		'open_ticket_include_chat_content'    => wpas_sc_get_option( 'sc_open_ticket_include_chat_content')
	);
	
	wp_localize_script( 'sc_pub_script', 'wpas_sc', $localize_script );
	
}

/**
 * Get the url of current page
 * 
 * @return string
 */
function sc_get_current_page_url() {
	
	//@TODO: Replace this section of code with a call to wpas_filter_input_server( 'REQUEST_URI' ) once AS 4.3.3 has been released.	
	
	//This filtered input might not work in some versions of PHP - see this issue: https://github.com/xwp/stream/issues/254		
	$http_host = filter_input( INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_STRING );
	
	// Therefore we have a fallback...
	if ( empty( $http_host ) && isset( $_SERVER['HTTP_HOST'] ) ) {
		$http_host = filter_var( $_SERVER['HTTP_HOST'], FILTER_SANITIZE_STRING ) ;
	}		
	
	//This filtered input might not work in some versions of PHP - see this issue: https://github.com/xwp/stream/issues/254		
	$uri = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING );
	
	// Therefore we have a fallback...
	if ( empty( $uri ) && isset( $_SERVER['REQUEST_URI'] ) ) {
		$uri = filter_var( $_SERVER['REQUEST_URI'], FILTER_SANITIZE_STRING ) ;
	}	
	
	return rtrim( "//{$http_host}{$uri}" ,  '/' );
}


/**
 * Check if $url is current loaded url
 * 
 * @global type $wp
 * @param type $url
 * @return boolean
 */
function sc_is_current_url( $url ) {
	
	$current_url = sc_get_current_page_url();
	
	$url = rtrim( trim($url), '/' );
	$url = preg_replace("(^https?:)", "", $url );
	
	$matched = false;
	
	if( $url )  {
		$is_whildcard = substr( $url, -1) == '*' ? true : false;
		
		if( $is_whildcard ) {
			
			$url =  rtrim( substr($url, 0, -1), '/' );
			
			if( substr($current_url, 0, strlen($url) ) == $url ) {
				$matched = true;
			}
		} elseif( $url == $current_url ) {
			$matched = true;
		}
	}
	
	return $matched;
}

/**
 * Check if url is aloowed to show smart chat
 * 
 * @return boolean
 */
function sc_is_url_allowed_smart_chat() {
	
	$allowed = false;
	
	
	if( wpas_sc_get_option( 'sc_show_on_all_pages' ) ) {
		return true;
	}
	
	$urls = wpas_sc_get_option( 'sc_include_url' );
	
	$urls = $urls && is_array( $urls ) ? $urls : array();
	
	foreach ( $urls as $url ) {
	
		if( sc_is_current_url( $url ) ) {
			$allowed = true;
			break;
		}
	
	}
	
	return $allowed;
}

/**
 * Check if user is disallowed to show smart chat
 * 
 * @return boolean
 */
function sc_is_url_disallowed_smart_chat() {
	
	$disallowed = false;
	
	$urls = wpas_sc_get_option( 'sc_exclude_url' );
	
	$urls = $urls && is_array( $urls ) ? $urls : array();
	
	foreach ( $urls as $url ) {
	
		if( sc_is_current_url( $url ) ) {
			$disallowed = true;
			break;
		}
	
	}
	
	return $disallowed;
}


/**
 * Smart chat item template
 */
function wpas_sc_message_item_template() {
	?>
	
	<script type="text/html" id="tmpl-wpas-sc-message-item" src="">

		<li class="{{{data.sender_type}}} clearfix">
			<div class="message-content clearfix">
			    <p>{{{data.message}}}</p>
			</div>

			<div class="info">
				{{{data.from}}} at {{{data.time}}}
			</div>
		</li>

	</script>
	
	<?php
}

add_action( 'wp_ajax_wpas_sc_get_reply', 'wpas_sc_reply_message' );
add_action( 'wp_ajax_nopriv_wpas_sc_get_reply', 'wpas_sc_reply_message' );

/**
 * Get reply of user message
 */
function wpas_sc_reply_message() {
	
	wpas_sc_ajax_nonce_check( 'wpas-sc-smartchat-message' );
	
	$message = isset( $_POST['message'] ) ? $_POST['message'] : '';
	$message = str_replace( array( '?', '.', '!' ), '', $message );
	
	if( $message ) {
		
		
		$bot = new WPAS_SC_ChatBot( $message, 'smart_chat' );
		
		$reply = $bot->get_reply();
		
		if( is_array( $reply ) ) {
			
			if( $reply['origin'] == 'fallback' && wpas_sc_get_option( 'sc_no_answer_control' ) == 'open_ticket' ) {
				
				$reply['action'] = 'init_open_ticket';
				
				$ajax_url = add_query_arg( 
					array( 
						'action' => 'sc_init_open_ticket', 
						'height' => '500', 
						'&width' => '700' 
					), '/wp-admin/admin-ajax.php'); 
				
				
				
				$reply['text'] = $reply['text'] . sprintf( '<div><a class="thickbox" href="%s">%s</a></div>', $ajax_url, wpas_get_option('sc_new_ticket_link_message', __( 'Unfortunately we did not find any results for your search. Click this link to open new support ticket or please rephrase your question.', 'wpas_chatbot' ) ) );
				
			} 
			
			// Add headers/footers if there are valid links in the reply...
			if ( $reply['origin'] <> 'fallback' ) {
				$reply['text'] = stripslashes( wpas_sc_get_option( 'sc_smart_chat_reply_header' ) ) . ' ' . $reply['text'] . ' ' . stripslashes( wpas_sc_get_option( 'sc_smart_chat_reply_footer' ) );
			}
				
			wp_send_json_success( $reply );
				
			
		} else {
			wp_send_json_success( $reply );
		}
		
	}
}

add_action('wp_ajax_sc_init_open_ticket', 'wpas_sc_init_open_ticket');
add_action('wp_ajax_nopriv_sc_init_open_ticket', 'wpas_sc_init_open_ticket');
/**
 * Return open ticket window content
 */
function wpas_sc_init_open_ticket() {
	include WPAS_CBOT_PATH . 'views/open_ticket.php';
	die();
}


add_action( 'wpas_do_sc_submit_new_ticket', 'wpas_do_sc_submit_new_ticket', 11, 1 );
/**
 * Submit new ticket
 * 
 * @global object $current_user
 * 
 * @param array $data
 */
function wpas_do_sc_submit_new_ticket( $data ) {
	
	$error = "";
	
	$email   = isset( $data['wpas_email'] ) ? wp_strip_all_tags( $data['wpas_email'] ) : false;
	$title   = isset( $data['wpas_title'] ) ? wp_strip_all_tags( $data['wpas_title'] ) : false;
	$content = isset( $data['wpas_message'] ) ? wp_kses( $data['wpas_message'], wp_kses_allowed_html( 'post' ) ) : false;

	// Verify the nonce first
	if ( ! isset( $data['wpas_nonce'] ) || ! wp_verify_nonce( $data['wpas_nonce'], 'sc_new_ticket' ) ) {
		$error = __( 'The authenticity of your submission could not be validated. If this ticket is legitimate please try submitting again.', 'awesome-support' );
	}
	
	// Make sure we have at least a title and a message
	elseif ( false === $title || empty( $title ) ) {
		$error = __( 'It is mandatory to provide a title for your issue.', 'awesome-support' );
	}
	elseif ( false === $content || empty( $content ) ) {
		$error = __( 'It is mandatory to provide a description for your issue.', 'awesome-support' );
	} 
	elseif ( !is_user_logged_in() && ( false === $email || empty( $email ) || !is_email( $email ) ) ) {
		$error = __( 'It is mandatory to provide a valid email address.', 'awesome-support' );
	}
	
	
	if( $error ) {
		wp_send_json_error( array( 'message' => $error ) );
		die();
	}
	
	
	$temp_user_logged_in = false;
	
	if ( !is_user_logged_in() ) {
		
		
		$user = get_user_by( 'email', $email );
		
		if( false === $user ) {
			$user = get_user_by( 'login', $email );
		}
		
		if( false === $user ) {
		
			$password = wp_generate_password( 12, false );
			$user_id = wp_create_user( $email, $password, $email );
			
			$user = new WP_User( $user_id );
			$user->set_role( 'wpas_user' );
		}
		
		
		if( $user ) {
			
			wp_clear_auth_cookie();
			wp_set_current_user( $user->ID );
			wp_set_auth_cookie( $user->ID );
			update_user_caches( $user );
			
			$user_id = $user->ID;
			$temp_user_logged_in = true;
		}
	} else {
		global $current_user;

		$user_id = $current_user->ID;
	}
	
	/**
	 * Gather current user info
	 */
	if ( !is_user_logged_in() ) {
		$error = __( 'Only registered accounts can submit a ticket. Please register first.', 'awesome-support' );
	} 
	
	// Verify user capability
	elseif ( !current_user_can( 'create_ticket' ) ) {
		$error = __( 'You do not have the capacity to open a new ticket.', 'awesome-support' );
	}

	if( $error ) {
		wp_send_json_error( array( 'message' => $error ) );
		die();
	}
	
	/**
	 * Allow the submission.
	 *
	 * This variable is used to add additional checks in the submission process.
	 * If the $go var is set to true, it gives a green light to this method
	 * and the ticket will be submitted. If the var is set to false, the process
	 * will be aborted.
	 *
	 * @since  3.0.0
	 */
	$go = apply_filters( 'wpas_before_submit_new_ticket_checks', true );

	/* Check for the green light */
	if ( is_wp_error( $go ) ) {
		wp_send_json_error( array( 'message' => $go->get_error_messages() ) );
		die();
	}

	
	$include_chat_content = wpas_sc_get_option( 'sc_open_ticket_include_chat_content' );
	
	if( $include_chat_content && isset( $data['chat_content'] ) && !empty( $data['chat_content'] ) ) {
		
		$chat_content = sprintf( '<div class="ticket_chat_content"><ul>%s</ul></div>', $data['chat_content'] );
		
		$content .= $chat_content;
	}

	/**
	 * Submit the ticket.
	 *
	 * Now that all the verifications are passed
	 * we can proceed to the actual ticket submission.
	 */
	$post = apply_filters( 'wpas_open_ticket_data', array(
		'post_content'   => $content,
		'post_name'      => $title,
		'post_title'     => $title,
		'post_status'    => 'queued',
		'post_type'      => 'ticket',
		'post_author'    => $user_id,
		'ping_status'    => 'closed',
		'comment_status' => 'closed',
	) );

	$ticket_id = wpas_insert_ticket( $post, false, false, 'chat' );
	
	/////////////////////////////////////////////////////////////////////////////////////////////
	

	if( $temp_user_logged_in ) {
		wp_logout();
	}
	
	/* Submission succeeded */
	if ( !( false === $ticket_id ) ) {
		wp_send_json_success( array( 'message'  => wpas_sc_get_option( 'sc_open_ticket_success_message' ) ) );
	} 
	
	/* Submission failure */
	wp_send_json_error( array( 'message' => __( 'The ticket couldn\'t be submitted for an unknown reason.', 'awesome-support' ) ) );
	
	die();
	
}


/**
 * Return css style from setting field to render
 * 
 * @param string $setting
 * 
 * @return string
 */
function wpas_sc_get_style( $setting ) {
	
	if( !$setting ) {
		return '';
	}
	
	$raw_style = wpas_sc_get_option( $setting );
	
	$style = array();

	if( $raw_style && is_array( $raw_style ) && !empty( $raw_style ) ) {
		foreach ( $raw_style as $prop_name => $prop_value ) {
			if( 'font-family' === $prop_name ) {
				$prop_value = "'" . $prop_value . "'";
			}

			$style[] = "{$prop_name}:{$prop_value}";
		}
	}
	
	return implode( ';', $style );
	
}