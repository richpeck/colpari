<?php

add_action( 'wpas_ticket_submission_form_outside_top', 'wpas_ss_smart_submit_stages' );

/**
 * Add smart submit stages fields right before submission form
 */
function wpas_ss_smart_submit_stages( ) {
	
	if ( true === boolval( wpas_ss_get_option( 'ss_smart_submit_enable' ) ) ) {
		include WPAS_SS_PATH . 'views/open_ticket_stages.php';
	}
	
}

add_action( 'wpas_submission_form_inside_top', 'wpas_ss_submission_form_add_hidden_fields' );

/**
 * Add hidden fields inside submission form to store smart submit stages data
 */
function wpas_ss_submission_form_add_hidden_fields() {
	?>
	<input type="hidden" name="ss_category" value="" />
	<input type="hidden" name="ss_category_topic" value="" />
	<input type="hidden" name="ss_search_term" value="" />
	<?php
}


add_action( 'wpas_open_ticket_after', 'wpas_ss_save_smart_submit_data', 11, 2 );

/**
 * Store smart submit stages data after ticket is added
 * 
 * @param int $ticket_id
 * @param array $data
 */
function wpas_ss_save_smart_submit_data( $ticket_id, $data ) {

	if( !is_admin() ) {
		
		if( wpas_ss_stage_1_enabled() ) {
			$category		= filter_input( INPUT_POST, 'ss_category' );
			$category_topic = filter_input( INPUT_POST, 'ss_category_topic' );
			
			update_post_meta( $ticket_id, 'ss_category',		$category );
			update_post_meta( $ticket_id, 'ss_category_topic',	$category_topic );
		}
		
		if( wpas_ss_stage_2_enabled() ) {
			$search_term	= filter_input( INPUT_POST, 'ss_search_term' );
			update_post_meta( $ticket_id, 'ss_search_term',		$search_term );
		}
		
	}
}

/**
 * Check if stage 1 is enabled
 * 
 * @return boolean
 */
function wpas_ss_stage_1_enabled() {
	
	return wpas_ss_get_option( 'ss_smart_submit_stage_1_enabled' );
	
}

/**
 * Check if stage 2 is enabled
 * 
 * @return boolean
 */
function wpas_ss_stage_2_enabled() {
	
	return wpas_ss_get_option( 'ss_smart_submit_stage_2_enabled' );
	
}

/**
 * Return category view type selected in smart submit settings tab
 * 
 * @return string
 */
function wpas_ss_category_view_type() {
	
	return wpas_ss_get_option( 'ss_smart_submit_categories_view_type' );
	
}

/**
 * Check if chatbot add-on is active
 * 
 * @return boolean
 */
function wpas_ss_is_chatbot_addon_active() {
	
	if( class_exists( 'WPAS_SC_ChatBot' ) && class_exists( 'WPAS_SS_ChatBot' ) ) {
		return true;
	}
	
	return false;
}

/**
 * Return smart submit setting value for an option
 *
 * If the option is expected to return a text value
 * and the value is empty in the database, then 
 * try to get the value from the default array.
 * 
 * 
 * @param string 	$option 	Option to get_browser
 * @param boolean 	$only_text	True if the option is expected to return text
 * 
 * @return mixed
 */
function wpas_ss_get_option( $option, $only_text = false ) {
	
	$default = wpas_ss_default_option_value( $option );
	
	$value = maybe_unserialize( wpas_get_option( $option, $default ) );
	
	if ( empty( $value ) && $only_text ) {
		/* if the expected value is text and the value is empty, try to make sure we return something by returning the default value */
		$value = $default ;
	}
	
	return $value;
}

/**
 * Return all registered post types
 * 
 * @return array
 */
function wpas_ss_all_post_types() {
	
	$post_types = array();
	
	$results = get_post_types( array(), 'object' );
	
	foreach ( $results as $post_type ) {
		$post_types[ $post_type->name ] =  $post_type->label;
	}
	
	return $post_types;
}


/**
 * Return default value of a setting
 * 
 * @param string $option
 * 
 * @return mixed
 */
function wpas_ss_default_option_value( $option = '' ) {
	
	$default_values = array(
	    
		'ss_smart_submit_categories_view_type'		=> 'dropdown',
		'ss_smart_submit_category_objects'			=> array(
						'faq',
						'documentation',
						'post'
					),
		'ss_smart_submit_search_post_types' => array(
						'faq',
						'documentation',
		),
		'ss_smart_submit_enable'				=> true,
		'ss_smart_submit_stage_2_enabled'		=> true,
		'ss_smart_submit_stage_1_enabled'		=> true,
		'ss_smart_submit_text_b4_stage1'		=> __( 'Before submitting a ticket, we will help you to browse through our help, faqs and other resources to see if the answer to your question already exists. <br />If your category or topic does not appear please select the one closest to your issue - you will eventually be given the option to open a support ticket.', 'wpas_ss' ),
		'ss_smart_submit_text_b4_stage2'		=> __( 'Aw, bummer. Ok, lets do a search then. <br /><br />', 'wpas_ss' ),
		'ss_smart_submit_text_category'			=> __( 'Select A Category', 'wpas_ss' ),				
		'ss_smart_submit_text_topic'			=> __( 'Select A Topic', 'wpas_ss' ),
		'ss_smart_submit_text_please_select'	=> __( 'Please select an item', 'wpas_ss' ),
		'ss_smart_submit_text_fix_issue'		=> __( 'Yay! This fixed my issue!', 'wpas_ss' ),
		'ss_smart_submit_text_yes'				=> __( 'Yes', 'wpas_ss' ),
		'ss_smart_submit_text_no'				=> __( 'No, This did not fix my issue', 'wpas_ss' ),
		'ss_smart_submit_text_search'			=> __( 'Search', 'wpas_ss' ),
		'ss_smart_submit_text_search_btn'		=> __( 'Search', 'wpas_ss' ),
		'ss_smart_submit_text_open_tkt'			=> __( 'I still need to open a ticket', 'wpas_ss' ),
		'ss_smart_submit_reply_header'			=> __( '', 'wpas_ss' ),
		'ss_smart_submit_reply_footer'			=> __( '', 'wpas_ss' ),
		'ss_smart_submit_text_before_links'		=> __( 'We found some related information for you.', 'wpas_ss' ),
		'ss_smart_submit_send_fallback_message' => true,
		'ss_smart_submit_fallback_message'		=> __( 'Unfortunately we could not find any results for your search terms.  Please enter a different search phrase.' , 'wpas_ss' )

	);
	
	if( empty( $option ) || !isset( $default_values[ $option ] ) ) {
		return '';
	}
		
	return apply_filters( "{$option}_default_value" , $default_values[ $option ] );
}


/**
 * Return all taxonomies of post types used in categories dropdown
 * 
 * @return array
 */
function wpas_ss_category_post_types() {
	
	return 
	array(
		'documentation' => 'as-doc-category',
		'faq'  => 'as-faq-category',
		'post' => 'category'
	);
	
}


/**
 * Return categories taxonomies
 * 
 * @return array
 */
function wpas_ss_category_taxonomies(  ) {
	$post_types = wpas_ss_get_option( 'ss_smart_submit_category_objects' );
	
	$all_post_types = wpas_ss_category_post_types();
	
	$taxonomies = array();
	
	foreach( $post_types as $post_type ) {
		$taxonomies[] = $all_post_types[ $post_type ];
	}
	
	return $taxonomies;
}

/**
 * Return category taxonomy terms
 * 
 * @return array
 */
function wpas_ss_categories() {
	
	$cat_taxonomies = wpas_ss_category_taxonomies();
	
	$terms = get_terms( array(
		'taxonomy' => $cat_taxonomies,
		'hide_empty' => false,
	) );
	
	return $terms;
}

/**
 * Return post type name of a taxonomy
 * 
 * @param string $taxonomy
 * 
 * @return string
 */
function wpas_ss_taxonomy_post_type_name( $taxonomy ) {
	
	$all_post_types = wpas_ss_category_post_types();
	
	$post_type = array_search( $taxonomy, $all_post_types );
	
	$post_type_obj = get_post_type_object( $post_type );
		
	return $post_type_obj->labels->singular_name;
	
}

/**
 * Return categories accordion
 * 
 * @return string
 */
function wpas_ss_categories_accordion() {
	
	$categories = wpas_ss_categories();
	
	$all_post_types = wpas_ss_category_post_types();
	
	
	
	$accordion = '<div class="accordion">';
	foreach( $categories as $cat ) {
		$val = $cat->taxonomy . ':' . $cat->term_id;
		
		$post_type = array_search( $cat->taxonomy, $all_post_types );
		
		$topics = wpas_ss_category_topics( $post_type, $cat->taxonomy, $cat->term_id );
		
		$post_type_name = wpas_ss_taxonomy_post_type_name( $cat->taxonomy );
		$cat_name = $cat->name . " [{$post_type_name}]";
		
		$accordion .= '<div class="accordion-item">
			<div class="accordion-header">'.$cat_name.'</div>
			<div class="accordion-body"><ul class="category_topics">';
				
				foreach( $topics as $topic ) {
					$accordion .= '<li><a href="#" data-id="'.$topic['id'].'">'. $topic['title'] . '</a></li>';
				}
				
				$accordion .= '</ul></div>
		</div>';
		
	}
	
	$accordion .= '</div>';
	
	
	return $accordion;
}

/**
 * Return categories links
 * 
 * @return string
 */
function wpas_ss_categories_links() {
	
	$categories = wpas_ss_categories();
	
	$links = '<ul class="categoty_view_links">';
	foreach( $categories as $term ) {
		$val = $term->taxonomy . ':' . $term->term_id;
		$post_type_name = wpas_ss_taxonomy_post_type_name( $term->taxonomy );
		$name = $term->name . " [{$post_type_name}]";
		$links .= '<li class="li_link"><a href="#" name="cat" data-value="'.$val.'" />'.$name.'</a></li>';
	}
	
	$links .= '</ul>';
	
	return $links;
	
}

/**
 * Return categories radio buttons
 * 
 * @return string
 */
function wpas_ss_categories_radio_buttons() {
	$categories = wpas_ss_categories();
	
	$radio_buttons = '';
	foreach( $categories as $term ) {
		$val = $term->taxonomy . ':' . $term->term_id;
		$post_type_name = wpas_ss_taxonomy_post_type_name( $term->taxonomy );
		$name = $term->name . " [{$post_type_name}]";
		$radio_buttons .= '<label class="radio"><input type="radio" name="cat" value="'.$val.'" /><span>'.$name.'</span></label>';
	}
	
	
	return $radio_buttons;
}

/**
 * Return categories dropdown
 * 
 * @return string
 */
function wpas_ss_categories_dropdown() {
	
	$categories = wpas_ss_categories();
	
	
	$options = "";
	
	foreach( $categories as $term ) {
		$val = $term->taxonomy . ':' . $term->term_id;
		$post_type_name = wpas_ss_taxonomy_post_type_name( $term->taxonomy );
		$name = $term->name . " [{$post_type_name}]";
		$options .= "<option value=\"{$val}\">{$name}</option>";
	}
	
	
	return wpas_dropdown( array(
		'name' => 'cat',
		'please_select' => true
	), $options );
	
}



add_action( 'wp_ajax_wpas_ss_search_anwser', 'wpas_ss_search_anwser' );

/**
 * Handle search request via ajax
 */
function wpas_ss_search_anwser() {
	
	
	$q = filter_input( INPUT_POST, 'q', FILTER_SANITIZE_STRING );
	
	if( !$q ) {
		wp_send_json_error( array( 'msg' => 'Search term can\'t be empty' ) );
		die();
	}
	
	require_once( WPAS_SS_PATH . 'includes/class_chatbot.php' );
	
	if( wpas_ss_is_chatbot_addon_active() ) {
		$results = wpas_ss_chatbot_search( $q );
	} else {
		$results = wpas_ss_posts_search( $q );
	}
	
	
	if( empty( $results ) ) {
		wp_send_json_error( array( 'msg' => 'No result found.' ) );
	} else {
		wp_send_json_success( array( 'answer' => $results ) );
	}
	
	die();
}



/**
 * Return posts search results
 * 
 * @param string $text
 * @param int $page
 * @param int $limit
 * 
 * @return string
 */
function wpas_ss_posts_search( $text, $page = 1, $limit = -1 ) {
	
	$post_types = wpas_ss_get_option( 'ss_smart_submit_search_post_types' );
	
	
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
	
	$results = new WP_Query( $args );
	
	$data = array();
	
	$response_text = '';

	if( isset( $results->posts ) && is_array( $results->posts ) && !empty( $results->posts ) ) {
		
		$links = array();

		foreach ( $results->posts as $post ) {
			$links[] = array(
				'type' => "web_url",
				'url' => get_permalink( $post->ID ),
				'title' => $post->post_title
				);
		}

		$links_text = __( 'We found some related information for you.', 'wpas_ss' );

		$data = array( 'links' => $links, 'links_text' => $links_text );
		
		
		$response_text = "";
		
		
		foreach ( $links as $link ) {
			$response_text .= sprintf( '<a href="%s" class="reply_link" target="_blank">%s</a><br />', $link['url'], $link['title'] );
		}

		$response_text = "<div>{$links_text}</div>" . $response_text;
		
	}


	return $response_text;
	
	
}


/**
 * Return chatbot search results
 * 
 * @param string $q
 * 
 * @return string
 */
function wpas_ss_chatbot_search( $q ) {
	
	
	$smart_replies = array();
	
	
	$bot = new WPAS_SS_ChatBot( $q, 'smart_replies' );
	
	$reply = $bot->get_reply();
		

	if( 'fallback' !== $reply['origin'] && ( 'text' == $reply['type'] || 'buttons' == $reply['type'] ) && !empty( $reply['text'] ) ) {
		$smart_replies[] = sprintf( '<div class="smart_reply">%s</div>', $reply['text'] );
	}
	
	
	$smart_reply_content = '';
	$smart_reply_header = '';
	$smart_reply_footer = '';
	
	
	if( empty( $smart_replies ) ) {

		if ( true === boolval( wpas_ss_get_option( 'ss_smart_submit_send_fallback_message' ) ) ) {
		
			$smart_replies[] = sprintf( '<div class="fallback_smart_reply">%s</div>', wpas_ss_get_option( 'ss_smart_submit_fallback_message' ) );
		}
		
	} else {

		$smart_reply_header = wpas_ss_get_option( 'ss_smart_submit_reply_header' );
		$smart_reply_footer = wpas_ss_get_option( 'ss_smart_submit_reply_footer' );

	}
	
	// At this point the $smart_replies array might have contents so just convert to string if its not empty...
	$smart_reply_content = '' ;
	if ( ! empty( $smart_replies ) ) {
		$smart_reply_content = $smart_reply_header . implode( '', $smart_replies ) . $smart_reply_footer;		
		
		return $smart_reply_content;
	}
}


add_action( 'wp_ajax_wpas_ss_get_topic_anwser', 'wpas_ss_get_topic_anwser' );

/**
 * Return topic post content via ajax
 */
function wpas_ss_get_topic_anwser() {
	$topic = filter_input( INPUT_POST, 'topic', FILTER_SANITIZE_STRING );
	
	$post = get_post( $topic );
	
	$data = array();
	
	if( $post ) {
		$data['answer'] = $post->post_content;
	}
	
	wp_send_json_success( $data );
	
	die();
}


add_action( 'wp_ajax_wpas_ss_get_topics', 'wpas_ss_ajax_get_topics' );

/**
 * Return category topics via ajax
 */
function wpas_ss_ajax_get_topics() {
	
	
	$cat = filter_input( INPUT_POST, 'cat', FILTER_SANITIZE_STRING );
	
	$cat_parts =  explode( ':', $cat );
	
	$cat_tax = $cat_parts[0];
	$cat_id = $cat_parts[1];
	
	
	$all_post_types = wpas_ss_category_post_types();
	
	$post_type = array_search( $cat_tax, $all_post_types );
	
	$data = wpas_ss_category_topics( $post_type, $cat_tax, $cat_id );
	
	wp_send_json_success( $data );
	die();
}

/**
 * Return category posts
 * 
 * @param string $post_type
 * @param string $tax
 * @param int $category_id
 * 
 * @return array
 */
function wpas_ss_category_topics( $post_type, $tax , $category_id ) {
	
	
	$topics = get_posts( array (
		'post_type' => $post_type,
		'numberposts' => -1,
		'tax_query' => array(
			array(
				'taxonomy' => $tax,
				'field' => 'id',
				'terms' => $category_id
			))
		));
	
	$data = array();
	
	foreach ( $topics as $topic ) {
		$data[] = array( 'id' => $topic->ID, 'title' => $topic->post_title );
	}
	
	return $data;
}

add_action( 'wpas_backend_ticket_content_after', 'wpas_ss_smart_submit_backend_view', 100, 2 );


/**
 * Print stored smart submit data on backend under ticket content
 * 
 * @param int $post_id
 * @param object $post
 * 
 * @return void
 */
function wpas_ss_smart_submit_backend_view( $post_id, $post ) {
	
	
	$category			= get_post_meta( $post_id, 'ss_category',		true );
	$category_topic		= get_post_meta( $post_id, 'ss_category_topic',	true );
	$search_term		= get_post_meta( $post_id, 'ss_search_term',	true );
	
	
	if( !$category && !$category_topic && !$search_term ) {
		return;
	}
	
	
	$category_name = "";
	
	$category_topic_name = "";
	
	if( $category ) {

		$cat_parts =  explode( ':', $category );
	
		$cat_tax = $cat_parts[0];
		$cat_id = $cat_parts[1];
		
		$category_term = get_term( $cat_id );
		
		if( $category_term ) {
			$post_type_name = wpas_ss_taxonomy_post_type_name( $cat_tax );
			$category_name  = $category_term->name . " [{$post_type_name}]";
		}
		
	}
	
	if( $category_topic ) {
		
		$category_topic_post = get_post( $category_topic );
		
		if( $category_topic_post ) {
			$category_topic_name = $category_topic_post->post_title;
		}
	}
	
	
	?>
	
	<div class="smart_submit_be_view">
			<div class="ss_heading"><?php _e( 'Smart Submit Sequence Data', 'wpa_ss' ); ?></div>
			<?php if( $category ) { ?>
			<div> <?php _e( 'Category', 'wpas_ss' ); ?> : <?php echo $category_name; ?></div>
			<?php } if( $category_topic ) { ?>
			<div> <?php _e( 'Category Topic', 'wpas_ss' ); ?> : <?php echo $category_topic_name; ?></div>
			<?php }
			
			if( $search_term ) { ?>
			<div> <?php _e( 'Search Term', 'wpas_ss' ); ?> : <?php echo $search_term; ?></div>
			<?php } ?>
	</div>
	
	<?php
	
}