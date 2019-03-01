<?php

add_filter( 'wpas_logs_handles', 'wpas_cbot_register_log_handle', 10, 1 );

/**
 * Register new log handler
 * 
 * @param array $handles
 * 
 * @return array
 */
function wpas_cbot_register_log_handle( $handles ) {
		array_push( $handles, 'chatbot' );
		return $handles;
}


/**
 * Return all registered post types
 * 
 * @return array
 */
function wpas_cbot_all_post_types() {
	
	$post_types = array();
	
	$results = get_post_types( array(), 'object' );
	
	foreach ( $results as $post_type ) {
		$post_types[ $post_type->name ] =  $post_type->label;
	}
	
	return $post_types;
}



/**
 * Return a list of selected post types for chat bot search
 * 
 * @return array
 */
function wpas_cbot_default_response_post_types() {
	
	return array(
		'faq' => array(
			'active' => true
		),
		'documentation' => array(
			'active' => true
		)
	);
}


/**
 * Return a list of selected post types for chat bot search
 * 
 * @return array
 */
function wpas_cbot_post_types() {
	
	
	$settings = maybe_unserialize( wpas_get_option( 'cbot_search_post_types', wpas_cbot_default_response_post_types() ) );
	
	
	$post_types = array();
	
	foreach ( $settings as $pt => $pt_setting ) {
		
		if( $pt_setting && !is_array( $pt_setting ) ) {
			$post_types[ $pt_setting ] = array(
				'active' => true
			);
		} else {
			$post_types[ $pt ] = $pt_setting;
		}
	}
	
	
	return $post_types;
}


/**
 * Return test for chat bot links, it will be shown on top of buttons
 * 
 * @return string
 */
function wpas_cbot_links_text() {
	
	return wpas_get_option( 'cbot_fb_links_text', __( 'We found some related information for you.', 'wpas_chatbot' ) );
	
}

/**
 * Return text for the link returned to the user via api.ai
 * 
 * @return string
 */
function wpas_cbot_apiai_search_links_page_info_text() {
	
	return wpas_get_option( 'cbot_apiai_search_links_page_info_text', __( 'We found some related information for you.', 'wpas_chatbot' ) );
	
}

/**
 * Return text for search page link
 * 
 * @return string
 */
function wpas_cbot_apiai_search_links_page_anchor_text() {
	
	return wpas_get_option( 'cbot_apiai_search_links_page_anchor_text', __( 'Click here for results.', 'wpas_chatbot' ) );
	
}



/**
 * Generate view log button for settings page
 * 
 * @return string
 */
function wpas_cbot_log_button() {
    
    $ajax_url = add_query_arg( 
            array( 
                'action' => 'wpas_cbot_log_win_content', 
                'height' => '500', 
                '&width' => '700' 
                ), 'admin-ajax.php'); 
    
    $content = '<a title="Log" href="'.$ajax_url.'" class="thickbox">View Log</a>';
    
    return $content;
}


/**
 * Check if logging is enabled
 * 
 * @return boolean
 */
function wpas_cbot_is_log_enabled() {
	return wpas_get_option( 'cbot_enable_log', false );
}


add_action( 'wp_ajax_wpas_cbot_log_win_content', 'wpas_cbot_log_win_content' );


/**
 * Return content for log window
 */
function wpas_cbot_log_win_content() {


	$content = "";
	
	$log = new WPAS_Logger( 'chatbot' );
	$file_path = $log->get_log_file_path();
	
	if( is_file( $file_path ) ) {
	    $content = file_get_contents( $file_path );
	}
	
	?>
	<div id="wpas_cbot_log_win">
		<textarea rows="40" style="width:100%;height:380px;margin-top:30px;"><?php echo $content; ?></textarea>
	</div>

	<?php
	die();
}


/**
 * Return all keyword match types
 * 
 * @return array
 */
function wpas_cbot_keyword_match_types() {
	
	return apply_filters( 'wpas_cbot_keyword_match_types', array(
	    'contain'	=> __( 'Match text contain', 'wpas_chatbot' ),
	    'exact'	=> __( 'Exact match', 'wpas_chatbot' ), 
	    'similar'	=> __( 'Match similar text' , 'wpas_chatbot' ),
	    'regex'	=> __( 'Match regular expression', 'wpas_chatbot' )
	) );
	
}


/**
 * Save chat bot logs
 * 
 * @param string $log
 * 
 * @return void
 */
function wpas_cbot_save_log( $log ) {
	
	if( empty( $log ) || !wpas_cbot_is_log_enabled() ) {
		return;
	}
	wpas_write_log( 'chatbot', $log );
}

/**
 * Check percent required to consider similar text matched
 * 
 * @return int
 */
function wpas_cbot_similar_text_match_percent() {
	return wpas_get_option( 'cbot_similar_text_match_percent', 60 );
}


/**
 * Return random single message
 * 
 * @param array $list
 * 
 * @return string
 */
function wpas_cbot_get_single_message( $list = array() ) {
		
	$text = "";
		
	if( 1 === count( $list ) ) {
		$text = $list[0];
	} elseif( 1 < count( $list ) ) {
		$rand = rand( 0, count( $list ) - 1 );
			
		$text = $list[ $rand ];
	} 
		
	return $text;
}

/**
 * Search posts
 * 
 * @param string $text
 * @param int $page
 * @param int $limit
 * @param string $payload_name
 * 
 * @return array
 */
function wpas_cbot_search_posts( $text = '', $page = 1, $limit = 3, $payload_name = 'wpas_cbot_get_more_links' ) {
		
	$post_type_settings = wpas_cbot_post_types();
	
	if( empty( $post_type_settings ) || empty( $text ) ) {
		return array();
	}
	
	
	$post_types = array();

	foreach( $post_type_settings as $pt => $pt_setting ) {
		
		$active = true;
		
		if( is_array( $pt_setting ) && !empty( $pt_setting ) ) {
			$active = isset( $pt_setting['active'] ) && $pt_setting['active'] ? true : false;
		} 
		
		if( $active ) {
			$post_types[] = $pt;
		}
		
	}
	
	
	if( empty( $post_types ) ) {
		return array();
	}
	
	
	$args = array(
	    's' => $text ,
		'post_type' => $post_types,
	    'posts_per_page' => $limit
	);
	
	
	if( ! ( -1 === $limit ) ) {
		$offset = ( $page * $limit ) - $limit;
		$args['offset'] = $offset;
	}
	
	add_filter( 'posts_clauses', 'wpas_cbot_links_search_clauses',		20, 2 );
	
	$results = new WP_Query( $args );
	
	remove_filter( 'posts_clauses', 'wpas_cbot_links_search_clauses',	20 );
	
	$data = array();
	
	
	if( isset( $results->posts ) && is_array( $results->posts ) && !empty( $results->posts ) ) {
		
		$next_page = $page < $results->max_num_pages ? $page + 1 : false;
		$links = array();
		
		foreach ( $results->posts as $post ) {
			$links[] = array(
			    'type' => "web_url",
			    'url' => get_permalink( $post->ID ),
			    'title' => $post->post_title
			    );
		}
			
		$links_text = 1 === $page ? wpas_cbot_links_text() : __( 'Here are more links', 'wpas_chatbot' );
		
		$data = array( 'links' => $links, 'links_text' => $links_text );
		
		if( $next_page ) {
			$data['next_page_payload'] = "{$payload_name}:::{$next_page}:::{$text}";
		}
		
	}
	
	
	return $data;
}

/**
 * Add clauses for search links based on post type and exclude settings
 * 
 * @global object $wpdb
 * @param array $pieces
 * @param object $wp_query
 * 
 * @return string
 */
function wpas_cbot_links_search_clauses( $pieces , $wp_query ) {
	global $wpdb;
	
	
	$post_type_settings = wpas_cbot_post_types();

	if( empty( $post_type_settings ) ) {
		return $pieces;
	}
	
	$post_types = array();

	$post_type_clauses = array();

	foreach( $post_type_settings as $pt => $pt_setting ) {

		$active = true;

		if( is_array( $pt_setting ) && !empty( $pt_setting ) ) {
			$active = isset( $pt_setting['active'] ) && $pt_setting['active'] ? true : false;
		} 


		if( $active ) {
			$post_types[] = $pt;
			
			$exclude_post_ids   = isset( $pt_setting['exclude_post_ids'] )   ? wpas_cbot_list_to_array( $pt_setting['exclude_post_ids'] )	 : '';
			$exclude_post_dates = isset( $pt_setting['exclude_post_dates'] ) ? wpas_cbot_list_to_array( $pt_setting['exclude_post_dates'], 'date' ) : '';
			$exclude_categories = isset( $pt_setting['exclude_categories'] ) ? $pt_setting['exclude_categories'] : array();


			$clauses = array();
			$clauses[] = "{$wpdb->posts}.post_type='{$pt}'";
			if( !empty( $exclude_post_ids ) ) {
				$clauses[] = "{$wpdb->posts}.ID NOT IN (" . implode(', ', $exclude_post_ids ) . ")";
			}

			if( !empty( $exclude_post_dates ) ) {
				$clauses[] = "DATE( {$wpdb->posts}.post_date ) NOT IN ('" . implode("', '", $exclude_post_dates ) . "')";
			}

			if( !empty( $exclude_categories ) ) {
				$clauses[] = "{$wpdb->posts}.ID NOT IN ( SELECT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN (" . implode(', ', $exclude_categories ) . ") )";
			}


			$post_type_clauses[] = '(' . implode(' AND ', $clauses ) . ')';

		}

	}
	
	
	if( !empty( $post_type_clauses ) ) {
		$pieces['where'] .= ' AND ( ' . implode( ' OR ', $post_type_clauses ) . ' )';
	}
	
	return $pieces;
}


/**
 * Return id of links search page
 * 
 * @return int
 */
function wpas_cbot_get_links_search_page() {
	
	$page_id = wpas_cbot_get_links_search_page_exists();
	
	if( !$page_id ) {
		$page_id = wpas_cbot_add_links_search_page();
	}
	
	return $page_id;
}


/**
 * Check if links search page exist and return if it exists
 * 
 * @return int
 */
function wpas_cbot_get_links_search_page_exists() {
	
	$page_id = get_option( 'wpas_cbot_search_links_page' );
	
	if( $page_id ) {
		$page = get_post( $page_id );
		
		if( !$page ) {
			return 0;
		} 
	}
	
	return $page_id;
}

/**
 * Create links search page
 * 
 * @return int
 */
function wpas_cbot_add_links_search_page() {
	
	$page_id = wpas_cbot_get_links_search_page_exists();
	
	if( !$page_id ) {
		
		$args = array(
			'post_title'    => __( 'Search Links', 'wpas_chatbot' ),
			'post_content'  => '[wpas_cbot_search_links]',
			'post_status'   => 'publish',
			'post_author'   => get_current_user_id(),
			'post_type'     => 'page',
		);

		$page_id = wp_insert_post( $args );
		
		add_option( 'wpas_cbot_search_links_page', $page_id );
	}
	
	return $page_id;
}

/**
 * Remove links search page
 */
function wpas_cbot_remove_links_search_page() {
	
	$page_id = wpas_cbot_get_links_search_page_exists();
	
	if( $page_id ) {
		wp_delete_post( $page_id );
	}
	
	delete_option( 'wpas_cbot_search_links_page' );
}


add_shortcode( 'wpas_cbot_search_links' , 'wpas_cbot_shortcode_search_links' );

/**
 * shortcode callback function for links search page content
 * 
 * @return type
 */
function wpas_cbot_shortcode_search_links() {
	
	$text = isset( $_GET['question'] ) ? $_GET['question'] : '';
	
	$content = '';
	
	if( $text ) {
		$links_data = wpas_cbot_search_posts( $text, 1, -1 );
		
		
		if( !empty( $links_data['links'] ) ) {
			
			$content .= sprintf( '<div>%s</div>', wpas_cbot_links_text() );
			
			$content .= '<ul style="list-style:none; maring:0; padding:0;">';
			foreach( $links_data['links'] as $link ) {
				$content .= sprintf( '<li style="padding: 2px 0;"><a href="%s">%s</a></li>', $link['url'], $link['title'] );
			}
			$content .= "</ul>";
		}
	}
	
	if( empty( $content ) ) {
		$content = sprintf( '<div>%s</div>', __( 'No result found', 'wpas_chatbot' ) );
	}
	
	return $content;
	
}


/**
 * Return default value of a setting
 * 
 * @param string $option
 * @return mixed
 */
function wpas_sc_default_option_value( $option = '' ) {
	
	
	
	$default_values = array(
	    
		'sc_enable'					=> true,
		'sc_bubble_location'		=> 'lower_right',
		'sc_bubble_minimized_title'	=> __( 'SMART CHAT', 'wpas_chatbot' ),
		'sc_chat_box_title'			=> __( 'CHAT', 'wpas_chatbot' ),
		'sc_chat_welcome_message'	=> __ ( 'Welcome to smart chat', 'wpas_chatbot' ),
		'sc_chat_box_message'		=> __( 'Type your message here...', 'wpas_chatbot' ),
		'sc_primary_color'			=> '#e4392b',
		'sc_show_on_all_pages'		=> true,
		'sc_title_font_and_size'	=> array(
							'font-family' => 'Exo 2',
							'color' => '#ffffff',
							'font-size' => '14px',
							),
	    
	    'sc_window_close_button_font_and_size' => array(
							'font-family' => 'Arial',
							'color' => '#000',
							'font-size' => '14px',
							),
	    
		'sc_user_font_and_size'		=> array(
							'font-family' => 'Exo 2',
							'color' => '#888888',
							'font-size' => '14px',
							),
		
		'sc_message_field_font_and_size' => array(
							'font-family' => 'Arial',
							'color' => '#000',
							'font-size' => '13px',
							),
		
	    
		'sc_answer_font_and_size'	=> array(
							'font-family' => 'Exo 2',
							'color' => '#888888',
							'font-size' => '14px',
							),
	    
		'sc_no_answer_control'		=> 'do_nothing',
	    
		'sc_open_ticket_include_chat_content' 	=> true,
		'sc_open_ticket_success_message'		=> __("Thank you for submitting a ticket.  An agent will review it and get back to you shortly.", 'wpas_chatbot' ),
		'sc_new_ticket_link_message' 			=> __('Unfortunately we did not find any results for your search. Please click this link to open a new ticket or please rephrase your question.', 'wpas_chatbot' ),
	    
		'sc_smart_replies_enabled'				=> false,
		'sc_smart_replies_manual_enabled'		=> true,
		'sc_smart_replies_fallback_message' 	=> __('We wanted to get an answer to you as soon as possible so we conducted an automated smart search on our database to try to find an answer to your question.  Unfortunately we did not find anything useful.  Please sit tight and an agent will respond to your ticket shortly!', 'wpas_chatbot' ),
		'sc_smart_replies_header_message'		=> __('We just conducted a smart search to try to find an answer to your question.  We found a few links that might be useful.', 'wpas_chatbot' ),
		'sc_smart_replies_footer_message'		=> __('If these links helped with your issue please take a moment and close the ticket. We really appreciate it. Otherwise an agent will respond to your ticket soon!', 'wpas_chatbot' ),				
	    
	);
	
	if( empty( $option ) || !isset( $default_values[ $option ] ) ) {
		return '';
	}
		
	return apply_filters( "{$option}_default_value" , $default_values[ $option ] );
	
}

/**
 * Return setting value
 * 
 * @param string $option
 * 
 * @return mixed
 */
function wpas_sc_get_option( $option ) {
	
	$default = wpas_sc_default_option_value( $option );
	
	$value = maybe_unserialize( wpas_get_option( $option, $default ) );
	
	
	$font_fields = array( 'sc_title_font_and_size',
			 'sc_user_font_and_size',
			'sc_message_field_font_and_size',
			 'sc_answer_font_and_size' );
	
	
	if( in_array( $option, $font_fields ) ) {
		
		$value = array( 'font-family' => $value['font-family'] , 'color' => $value['color'], 'font-size' => $value['font-size'] );
	}
	
	return $value;
}

add_filter( 'tf_get_value_font_wpas', 'wpas_sc_font_field_default_value', 3, 11 );
/**
 * 
 * @param mixed $value
 * @param int $postID
 * @param object $field
 * 
 * @return array
 */
function wpas_sc_font_field_default_value( $value, $postID, $field ) {
	
	
	$font_fields = array( 'sc_title_font_and_size',
			 'sc_user_font_and_size',
			 'sc_answer_font_and_size' );
	
	
	
	
	$field_id = $field->settings['id'];
	
	if( in_array( $field_id, $font_fields ) ) {
		if( !is_array( $value ) ) {
			$value = wpas_sc_default_option_value( $field_id );
		}
		
	}
	
	
	return $value;
}


add_action( 'wpas_open_ticket_after', 'wpas_sc_open_ticket_after', 20, 2 );


/**
 * Send auto reply after ticket is added
 * 
 * @param int $ticket_id
 * @param array $data
 * 
 * @return void
 */
function wpas_sc_open_ticket_after( $ticket_id, $data ) {
	
	// Make sure smart reply is enabled
	if( !wpas_sc_get_option( 'sc_smart_replies_enabled' ) || !( isset( $data['post_content'] ) && !empty( $data['post_content'] ) ) ) {
		return;
	}
	
	$content = $data['post_content'] . '. ' . $data['post_content'];
	
	WPAS_SC_Smart_Reply::send_auto_reply( $ticket_id, $content );
}


// Add buttons function if not exist
if( !function_exists( 'wpas_backend_ticket_content_after_buttons' ) ) {
	
	add_action( 'wpas_backend_ticket_content_after', 'wpas_backend_ticket_content_after_buttons', 12, 1 );

	// Add buttons under ticket content
	function wpas_backend_ticket_content_after_buttons( $ticket_id ) {

		$buttons = apply_filters( 'wpas_backend_ticket_content_after_buttons', array(), $ticket_id );


		if( empty( $buttons ) ) {
			return;
		}

		$buttons_content = 

			'<div class="wpas_backend_ticket_content_after_buttons">
				
				<div class="pf_msg">
					<p></p>
					<button type="button" class="notice-dismiss">
						<span class="screen-reader-text">'.__( 'Dismiss this notice.', 'wpas_chatbot' ).'</span>
					</button>
				</div>

				<ul>';
		foreach( $buttons as $button ) {

			$buttons_content .= "<li>{$button}</li>";
		}

		$buttons_content .= '<li class="clear clearfix"></li>
			</ul>
		</div>';


		echo $buttons_content;
	}
	
}



add_filter( 'wpas_backend_ticket_content_after_buttons', 'wpas_sc_add_manual_smart_reply_button', 12, 2 );

/**
 * Add smart reply button under ticket content to fill reply field
 * 
 * @param array $buttons
 * @param int $ticket_id
 * 
 * @return array
 */
function wpas_sc_add_manual_smart_reply_button( $buttons , $ticket_id ) {
	
	$manual_enabled = wpas_sc_get_option( 'sc_smart_replies_manual_enabled' );
	
	if( $manual_enabled ) {
		$buttons[] =  WPAS_SC_Smart_Reply::manual_button( $ticket_id );
	}
	
	return $buttons;
}

/**
 * Check ajax nonce
 * 
 * @param string $name
 * @param string $key
 */
function wpas_sc_ajax_nonce_check( $name, $key = 'security' ) {
	
	if( !check_ajax_referer( $name, $key, false ) ) {
		wp_send_json_error( array( 'message' => __( "You don't have access to perform this action.", 'wpas_chatbot' ) ) );
		die();
	}
}


add_action('wp_ajax_wpas_sc_manual_smart_reply', 'wpas_sc_manual_smart_reply' );

/**
 * Return manual smart reply content via ajax
 */
function wpas_sc_manual_smart_reply() {
	
	wpas_sc_ajax_nonce_check( 'wpas-sc-manual-smart-reply' );
	
	$ticket_id = filter_input( INPUT_POST, 'ticket_id');
	$manual_enabled = wpas_sc_get_option( 'sc_smart_replies_manual_enabled' );
	
	if( $ticket_id && $manual_enabled ) {
		
		$content = WPAS_SC_Smart_Reply::get_reply( $ticket_id );
		
		if( $content ) {
			wp_send_json_success( array( 'content' => $content ) );
			die();
		}
		
		
	}
	
	wp_send_json_error( array( 'message' => __( "No smart reply found.", 'wpas_chatbot' ) ) );
	die();
}

/**
 * Return fallback message for facebook, smart chat and smart reply
 * 
 * @param string $type
 * 
 * @return string
 */
function wpas_sc_fallback_message( $type = 'fb' ) {
			
	return stripslashes( wpas_sc_get_option( "cbot_{$type}_fallback_message" ) );
}


/**
 * Return dropdown for a taxonomy 
 * 
 * @param array $args
 * 
 * @return string
 */
function wpas_cbot_categories_dropdown( $args ) {
											
	$taxonomy = isset( $args['tax'] ) ? $args['tax'] : 'category';
	$categories = get_categories( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
	$selected_options = isset( $args['selected'] ) && is_array( $args['selected'] ) ? $args['selected'] : array();
	
	$options = "";
	foreach ( $categories as $cat ) {
		$selected = in_array( $cat->term_id, $selected_options ) ? 'selected="selected"' : '';
		$options .= sprintf( '<option value="%s" %s>%s</option>', $cat->term_id, $selected , $cat->name ) ;
	}
	
	return wpas_dropdown( $args, $options );
}


/**
 * Check if date is valid
 * 
 * @param string $date
 * @param string $format
 * 
 * @return type
 */
function wpas_cbot_validate_date( $date, $format = 'Y-m-d' ) {
    $d = DateTime::createFromFormat( $format, $date );
	
	if( $d && $d->format( $format ) == $date ) {
		return true;
	}
	
    return false;
}


/**
 * Turn a list into array and check data type
 * 
 * @param string $text
 * @param string $type
 * 
 * @return array
 */
function wpas_cbot_list_to_array( $text, $type = 'int' ) {
				
	$result = array();
	
	if( $text ) {
		$items = explode( ',', str_replace( ' ', '', $text ) );
		foreach( $items as $item ) {
			if( '' === $item ) {
				continue;
			}

			if( 'int' === $type ) {
				if( is_numeric( $item ) ) {
					$result[] = $item;
				}
			} elseif( 'date' === $type ) {
				if( wpas_cbot_validate_date( $item ) ) {
					$result[] = $item;
				}
			} 
			
		}
		
	}
	
	return $result;
}