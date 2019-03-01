<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Add javascript file for the CPT timer functionality.
 *
 * @since 	0.1.0
 * @return  void
 */
function as_time_tracking_enqueue_cpt_timer() {
  global $current_screen;
	if( $current_screen->id == 'trackedtimes' ) {
    wp_enqueue_script( 'as_time_tracking_ticket_timer_script', AS_TT_URL . "js/as-time-tracking-timer.js", array( 'jquery' ) );
    wp_localize_script( 'as_time_tracking_ticket_timer_script', 'js_object_timer',	array(
                                                        'stop' => __( 'Stop', 'awesome-support-time-tracking' ),
                                                        'start' => __( 'Start', 'awesome-support-time-tracking' ),
                                                        'resume' => __( 'Resume', 'awesome-support-time-tracking' ),
                                                        'pause' => __( 'Pause', 'awesome-support-time-tracking' ),
                                                        'hours_num' => __( 'The time tracking hour(s) must be a number!', 'awesome-support-time-tracking' ),
                                                        'start_hours_num' => __( 'The time tracking start hour(s) must be a number!', 'awesome-support-time-tracking' ),
                                                        'end_hours_num' => __( 'The time tracking end hour(s) must be a number!', 'awesome-support-time-tracking' ),
                                                        'minutes_num' => __( 'The time tracking minute(s) must be a number!', 'awesome-support-time-tracking' ),
                                                        'start_minutes_num' => __( 'The time tracking start minute(s) must be a number!', 'awesome-support-time-tracking' ),
                                                        'end_minutes_num' => __( 'The time tracking end minute(s) must be a number!', 'awesome-support-time-tracking' ),
                                                        'seconds_num' => __( 'The time tracking second(s) must be a number!', 'awesome-support-time-tracking' ),
                                                        'start_seconds_num' => __( 'The time tracking start second(s) must be a number!', 'awesome-support-time-tracking' ),
                                                        'end_seconds_num' => __( 'The time tracking end second(s) must be a number!', 'awesome-support-time-tracking' ),
                                                        'start_empty' => __( 'The time tracking start date can\'t be empty!', 'awesome-support-time-tracking' ),
                                                        'start_format' => __( 'The time tracking start date must be in a yyyy-mm-dd format!', 'awesome-support-time-tracking' ),
                                                        'end_empty' => __( 'The time tracking end date can\'t be empty!', 'awesome-support-time-tracking' ),
                                                        'end_format' => __( 'The time tracking end date must be in a yyyy-mm-dd format!', 'awesome-support-time-tracking' ),
                                                        'end_start' => __( 'The time tracking end date can\'t be before the start date!', 'awesome-support-time-tracking' ),
                                                        'tab_text' => __( 'Timer is running...', 'awesome-support-time-tracking' ),
                                                        'clock_favicon_url' => __( AS_TT_URL . 'images/clock_favicon.ico', 'awesome-support-time-tracking' ),
                                                        'as_favicon_url' => __( AS_TT_URL . 'images/as_favicon.ico', 'awesome-support-time-tracking' ),
                                                        'current_page' => $current_screen->id
                                                      ) );
  }
}

add_action( 'admin_enqueue_scripts', 'as_time_tracking_enqueue_cpt_timer' );

/**
 * Add javascript file for all view filtering.
 *
 * @since 	0.1.0
 * @return  void
 */
function as_time_tracking_enqueue_filter_js() {
  global $current_screen;

	if( $current_screen->id == 'edit-trackedtimes' ) {
    wp_enqueue_script( 'as_time_tracking_filter_js_script', AS_TT_URL . "js/as-time-tracking-filter-js.js", array( 'jquery' ) );
    wp_localize_script( 'as_time_tracking_filter_js_script', 'ajax_object',
            array(
							'ajax_url' => admin_url( 'admin-ajax.php' )
							) );
  }
}

add_action( 'admin_enqueue_scripts', 'as_time_tracking_enqueue_filter_js' );

/**
 * Add select2 js file for filter.
 *
 * @since 	0.1.0
 * @return  void
 */
function as_time_tracking_enqueue_filter_select2_js() {
	global $current_screen;

	if(
    $current_screen->id == 'trackedtimes' ||
    $current_screen->id == 'edit-trackedtimes' ||
    $current_screen->id == 'trackedtimes_page_trackedtimes-reports' ||
    $current_screen->id == 'trackedtimes_page_trackedtimes-invoicing'
  ) {
    wp_register_script( 'as_time_tracking_filter_select2_js_script', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js' );
    wp_enqueue_script( 'as_time_tracking_filter_select2_js_script' );
  }
}

add_action( 'admin_enqueue_scripts', 'as_time_tracking_enqueue_filter_select2_js' );

/**
 * Add main javascript file for edit page of the custom post type.
 *
 * @since 	0.1.0
 * @return  void
 */
function as_time_tracking_enqueue_js() {
  global $current_screen;

  if( $current_screen->id == 'trackedtimes' ) {
    wp_enqueue_script( 'as_time_tracking_js_script', AS_TT_URL . "js/as-time-tracking-js.js", array( 'jquery' ) );
		wp_localize_script( 'as_time_tracking_js_script', 'js_object',	array(
																												'title_text' => __( '*the title will be auto generated when the ticket and reply ids are entered. Only valid ids will generate the title.', 'awesome-support-time-tracking' ),
																												'ticket_id_title' => __( 'Ticket #', 'awesome-support-time-tracking' ),
																												'ticket_reply_id_title' => __( ', Ticket reply #', 'awesome-support-time-tracking' ),
                                                        'start' => __( 'Start', 'awesome-support-time-tracking' )
																											) );
  }
}

add_action( 'admin_enqueue_scripts', 'as_time_tracking_enqueue_js' );

/**
 * Add main JavaScript/ajax file.
 *
 * @since 	0.1.0
 * @return  void
 */
function as_time_tracking_enqueue_ajax() {
  global $current_screen;

  if( $current_screen->id == 'trackedtimes' ) {
    global $post;
    $allow_multiple_entries = wpas_get_option( 'time_tracking_ticket_allow_multiple_entries', 'no' );
    wp_enqueue_script( 'ajax-script', AS_TT_URL . "js/as-time-tracking-ajax.js", array( 'jquery' ) );
		wp_localize_script( 'ajax-script', 'ajax_object',	array(
																														'ajax_url' => admin_url( 'admin-ajax.php' ),
																														'ajax_nonce' => wp_create_nonce( 'as-time-tracking-cpt-nonce' ),
																														'post_id' => $post->ID,
																														'ticket_id_title' => __( 'Ticket #', 'awesome-support-time-tracking' ),
																														'ticket_reply_id_title' => __( ', Ticket reply #', 'awesome-support-time-tracking' ),
																														'ticket_reply_id' => __( 'Ticket Reply #', 'awesome-support-time-tracking' ),
																														'ticket_reply_content' => __( 'Ticket Reply Content', 'awesome-support-time-tracking' ),
                                                            'ticket_reply_meta_status' => __( 'Availability', 'awesome-support-time-tracking' ),
																														'ticket_title' => __( 'Ticket Title', 'awesome-support-time-tracking' ),
																														'status' => __( 'Status', 'awesome-support-time-tracking' ),
																														'agent' => __( 'Agent', 'awesome-support-time-tracking' ),
																														'client_customer' => __( 'Client/Customer', 'awesome-support-time-tracking' ),
																														'show_more' => __( 'Show more', 'awesome-support-time-tracking' ),
                                                            'allow_multiple_entries' => __( $allow_multiple_entries, 'awesome-support-time-tracking' )
																														) );
  }
}

add_action( 'admin_enqueue_scripts', 'as_time_tracking_enqueue_ajax' );

/**
 * Handler function for ajax, returns the duplicate data for a ticket reply if the multiple entries setting is
 * enabled and the user has just clicked the copy/duplicate.
 *
 * @since   0.1.0
 * @return  void
 */
function as_time_tracking_return_duplicate_ticket_reply_data() {
	check_ajax_referer( 'as-time-tracking-cpt-nonce', 'security' );
  $tracked_time_data = get_post_meta( sanitize_text_field( $_POST['post_id'] ), 'as_time_tracking_entry' );
  $tracked_time_data[0]['post_title'] = get_the_title( sanitize_text_field( $_POST['post_id'] ) );


  //RECORDED TIME WHERE ITS SAVED WILL CHANGE
  if( $tracked_time_data !== false ) {
    $saved_time_data = get_post_meta( $tracked_time_data[0]['ticket_reply'], '_wpas_individual_reply_ticket_time', true );
    $tracked_time_data[0]['recorded_time'] = $saved_time_data;
  }

  wp_send_json_success( $tracked_time_data );
  wp_die();
}

add_action( 'wp_ajax_duplicate_ticket_reply_data_action', 'as_time_tracking_return_duplicate_ticket_reply_data' );

/**
 * Handler function for ajax, checks if the entered ticket and reply ids are valid or not valid.
 * If they are not valid the old title is returned for further processing in JavaScript.
 *
 * @since 	0.1.0
 * @return  void
 */
function as_time_tracking_check_created_title() {
	check_ajax_referer( 'as-time-tracking-cpt-nonce', 'security' );
  $current_post_title = get_the_title( $_POST['post_id'] );
  $ticket_id = sanitize_text_field( $_POST['ticket_id'] );
  $ticket_reply_id = sanitize_text_field( $_POST['ticket_reply_id'] );
  $ticket_level = sanitize_text_field( $_POST['ticket_level'] );
  $tickets_valid = false;
  global $wpdb;
  $db_query = "SELECT ID FROM " . $wpdb->prefix . "posts WHERE ID = " . ( int )$ticket_id . " AND post_type = 'ticket' AND post_status != 'trash'";
  $db_result = $wpdb->get_results( $db_query, OBJECT );

  if( $ticket_level == 'true' ) {
    if( count( $db_result ) > 0 ) {
      echo __( 'Ticket #', 'awesome-support-time-tracking' ) . $ticket_id;
      wp_die();
    }
  } elseif( $ticket_level == 'false' ) {
    if( count( $db_result ) > 0 ) {
      if( is_numeric( $ticket_reply_id ) ) {
        $ticket_reply_parent = wp_get_post_parent_id( $ticket_reply_id );
        if( $ticket_reply_parent == $ticket_id ) {
          $updated_title = __( 'Ticket #', 'awesome-support-time-tracking' ) . $ticket_id;
          $updated_title .= __( ' Ticket reply #', 'awesome-support-time-tracking' ) . $ticket_reply_id;
          echo $updated_title;
          wp_die();
        }
      }
    }
  }

  wp_die();

}

add_action( 'wp_ajax_ticket_and_reply_id_changed_action', 'as_time_tracking_check_created_title' );

/**
 * Handler function for ajax, checks if the entered ticket and reply ids are valid or not valid.
 * If they are not valid the old title is returned for further processing in JavaScript.
 *
 * @since 	0.1.0
 * @return  void
 */
function as_time_tracking_ticket_level_get_assigned_agent() {
	check_ajax_referer( 'as-time-tracking-cpt-nonce', 'security' );
  $ticket_id = sanitize_text_field( $_POST['ticket_id'] );
  $agent_id = get_post_meta( $ticket_id, '_wpas_assignee', true );

  echo $agent_id;

  wp_die();

}

add_action( 'wp_ajax_ticket_level_get_agent_action', 'as_time_tracking_ticket_level_get_assigned_agent' );

/**
 * Handler function for ajax, checks if the entered ticket id are valid or not valid.
 * If they are valid the ID of the agent is returned so the select value can be set in the response.
 *
 * @since		0.1.0
 * @return  void
 */
function as_time_tracking_check_if_agent_set() {
	check_ajax_referer( 'as-time-tracking-cpt-nonce', 'security' );
  $ticket_reply_id = sanitize_text_field( $_POST['ticket_reply_id'] );
  $post_obj = get_post( $ticket_reply_id );
  $agent_id = $post_obj->post_author;

  echo $agent_id;

  wp_die();
}

add_action( 'wp_ajax_determine_agent_action', 'as_time_tracking_check_if_agent_set' );

/**
 * Handler function for ajax, checks if the ticket id entered exists for the lookup.
 *
 * @since 	0.1.0
 * @return  void
 */
function as_time_tracking_check_display_reply_lookup() {
	check_ajax_referer( 'as-time-tracking-cpt-nonce', 'security' );
	$ticket_id = sanitize_text_field( $_POST['ticket_id'] );

	global $wpdb;
	$query = new WP_Query( array(
													'post_type' => 'ticket',
													'post_status' => 'any',
													'posts_per_page' => -1
												) );

	$ticket_ids = wp_list_pluck( $query->posts, 'ID' );

	if( in_array( $ticket_id, $ticket_ids ) ) {
		echo "exists";
	} else {
		echo "not_ticket";
	}

  wp_die();
}

add_action( 'wp_ajax_determine_reply_lookup_display', 'as_time_tracking_check_display_reply_lookup' );

/**
 * Handler function for ajax, checks if the ticket id entered exists for the lookup when changed.
 *
 * @since 	0.1.0
 * @return  void
 */
function as_time_tracking_lookup_check_ticket_id() {
  $ticket_id = sanitize_text_field( $_POST['ticket_id'] );

	global $wpdb;
	$query = new WP_Query( array (
													'post_type' => 'ticket',
													'post_status' => 'any',
													'posts_per_page' => -1
												) );

	$ticket_ids = wp_list_pluck( $query->posts, 'ID' );

	if( in_array( $ticket_id, $ticket_ids ) ) {
		echo "exists";
	} else {
		echo "not_ticket";
	}

  wp_die();
}

add_action( 'wp_ajax_reply_ticket_id_lookup_changed_action', 'as_time_tracking_lookup_check_ticket_id' );

/**
 * Handler function for ajax, looks up existing ticket replys based on the ticket.
 *
 * @since 	0.1.0
 * @return  void
 */
function as_time_tracking_lookup_ticket_reply_ids() {
	check_ajax_referer( 'as-time-tracking-cpt-nonce', 'security' );
	$ticket_id = sanitize_text_field( $_POST['ticket_id'] );
	global $wpdb;
  $db_query = "SELECT ID, post_content FROM " . $wpdb->prefix . "posts WHERE post_parent = " . ( int )$ticket_id . " AND post_type = 'ticket_reply' AND post_status != 'trash'";
  $db_result = $wpdb->get_results( $db_query, OBJECT );

  $meta_query = "SELECT * FROM " . $wpdb->prefix . "postmeta WHERE meta_key = 'as_time_tracking_entry'";
  $meta_objects = $wpdb->get_results( $meta_query, OBJECT );
  $ticket_replies = array();
  $allow_multiple_entries = wpas_get_option( 'time_tracking_ticket_allow_multiple_entries', 'no' );

	/** Sets up the content to show for ticket replies. As content can be quite long there is
	 *  a check so that only the first 50 characters are used.
	 */
	foreach( $db_result as $ids ) {
		$content = substr( $ids->post_content, 0, 50 );
		( strlen( $ids->post_content ) > 50 ? $content .= "..." : "" );

    if( empty( $meta_objects ) ) {
      $tracked_time_status = __( 'Available for use.', 'awesome-support-time-tracking' ); //No tracked times so everything will be available to use
    } else {
      $tracked_time_status = "";
    }

    foreach( $meta_objects as $obj ) {
        $meta_info = unserialize( $obj->meta_value );
        if(
          $meta_info['ticket_id'] == $ticket_id &&
          $meta_info['ticket_reply'] == $ids->ID &&
          $meta_info['invoiced'] == 'approved'
        ) {
          $tracked_time_status = __( 'Already invoiced – cannot be used again', 'awesome-support-time-tracking' );
          break;
        } elseif(
          $meta_info['ticket_id'] == $ticket_id &&
          $meta_info['ticket_reply'] == $ids->ID &&
          $meta_info['invoiced'] == 'in process'
        ) {
          $tracked_time_status = __( 'Invoice In Process – cannot be used again', 'awesome-support-time-tracking' );
          break;
        } elseif( $allow_multiple_entries == 'no' ) {
          if(
            $meta_info['ticket_id'] == $ticket_id &&
            $meta_info['ticket_reply'] == $ids->ID &&
            $meta_info['invoiced'] == ''
          ) {
            $tracked_time_status = __( 'Ticket # and Ticket Reply # already used for a Tracked Time - cannot be used again', 'awesome-support-time-tracking' );
            break;
          }
        } elseif( $allow_multiple_entries == 'yes' ) {
          $tracked_time_status = __( 'Available to use', 'awesome-support-time-tracking' );
          break;
        }

        if( $tracked_time_status == "" ) {
          $tracked_time_status = __( 'Available to use', 'awesome-support-time-tracking' );
        }
    }

		$ticket_data = array(
				'id' => $ids->ID,
				'content' => $content,
        'tracked_time_status' => $tracked_time_status
				);

		$ticket_replies[] = $ticket_data;
	}

	wp_send_json_success( $ticket_replies );

  wp_die();
}

add_action( 'wp_ajax_ticket_reply_id_lookup_action', 'as_time_tracking_lookup_ticket_reply_ids' );

/**
 * Add jQuery UI Datepicker javascript.
 *
 * @since 	0.1.0
 * @return  void
 */
function as_time_tracking_enqueue_datepicker() {
  global $current_screen;

  if($current_screen->id == 'trackedtimes' || $current_screen->id == 'trackedtimes_page_trackedtimes-reports' || $current_screen->id == 'trackedtimes_page_trackedtimes-invoicing' ) {
    wp_enqueue_script('jquery-ui-datepicker');
  }
}

add_action( 'admin_enqueue_scripts', 'as_time_tracking_enqueue_datepicker' );

/**
 * Handler function for ajax, checks the search term for the ticket lookups.
 *
 * @since 	0.1.0
 * @return  void
 */
function as_time_tracking_check_ticket_id_lookup_search() {
	check_ajax_referer( 'as-time-tracking-cpt-nonce', 'security' );
  $search_text = sanitize_text_field( $_POST['searchText'] );
  $ticket_info = array();

  global $wpdb;
	$db_query = "SELECT ID, post_title, post_status, post_author FROM " . $wpdb->prefix . "posts WHERE post_title LIKE '%" . $search_text . "%' AND post_type = 'ticket' AND post_status != 'trash'";
	$db_result = $wpdb->get_results( $db_query, OBJECT );


  foreach( $db_result as $result ) {
    $status = wpas_get_ticket_status( $result->ID );

    //status logic here is taken from the core plugin's logic
    if ( 'closed' === $status ) {
      $status = "Closed";
    } else {
      $post          = get_post( $result->ID );
      $post_status   = $result->post_status;
      $custom_status = wpas_get_post_status();

      if ( !array_key_exists( $post_status, $custom_status ) ) {
        $status = "Open";
      } else {
        $status = $custom_status[ $post_status ];
      }
    }

    $agent_id = get_post_meta( $result->ID, '_wpas_assignee', true );
    $agent_info = get_userdata( $agent_id );

		//Stops notice occuring
		if( $agent_info != false ) {
    	$agent_name = $agent_info->display_name;
		} else {
			$agent_name = "";
		}

    $client = get_userdata( $result->post_author );
    $client_name = $client->data->display_name;

    $ticket_info[] = array(
      "id" => $result->ID,
      "title" => $result->post_title,
      "status" => $status,
      "agent" => $agent_name,
      "client" => $client_name
    );
  }

  wp_send_json_success( $ticket_info );
  wp_die();
}

add_action( 'wp_ajax_as_time_tracking_ticket_id_lookup_select', 'as_time_tracking_check_ticket_id_lookup_search' );
