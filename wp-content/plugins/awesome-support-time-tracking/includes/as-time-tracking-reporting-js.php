<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Add reporting JavaScript file to reporting page.
 *
 * @since 0.1.0
 * @return void
 */
function as_time_tracking_enqueue_reporting_js() {
  global $current_screen;

  if( $current_screen->id == 'trackedtimes_page_trackedtimes-reports' ) {
		wp_enqueue_script( 'as_time_tracking_reporting_helper_script', AS_TT_URL . "js/as-time-tracking-filter-functions.js", array( 'jquery' ) );
    wp_enqueue_script( 'as_time_tracking_reporting_js_script', AS_TT_URL . "js/as-time-tracking-reporting.js", array( 'jquery' ) );
    wp_enqueue_script( 'as_time_tracking_reporting_filter_setup', AS_TT_URL . "js/as-time-tracking-reporting-filters.js", array( 'jquery' ) );
    wp_localize_script( 'as_time_tracking_reporting_js_script', 'ajax_object_reporting',
            array(
							'ajax_url' => admin_url( 'admin-ajax.php' ),
              'ajax_nonce' => wp_create_nonce( 'as-time-tracking-reporting-nonce' ),
							'agent_title' => __( 'Time Tracking For Each Agent', 'awesome-support-time-tracking' ),
							'client_title' => __( 'Time Tracking For Each Client/Support User', 'awesome-support-time-tracking' ),
							'ticket_title' => __( 'Time Tracking For Each Ticket', 'awesome-support-time-tracking' ),
              'invoice_title' => __( 'Uninvoiced Time For Each Client/Support User', 'awesome-support-time-tracking' ),
							'no_times' => __( 'No times found.', 'awesome-support-time-tracking' ),
							'no_times_missing_client' => __( ' Data was dropped because the original ticket was missing.', 'awesome-support-time-tracking' ),
							'missing_client' => __( 'Data was dropped from this report because the original ticket is missing.', 'awesome-support-time-tracking' ),
							'agent_heading' => __( 'Total Time Recorded For ', 'awesome-support-time-tracking' ),
							'client_ticket_heading' => __( 'Time spent on ', 'awesome-support-time-tracking' ),
              'client_invoice_heading' => __( 'Uninvoiced time for ', 'awesome-support-time-tracking' ),
							'entry_date' => __( 'Entry Date', 'awesome-support-time-tracking' ),
							'start_date' => __( 'Start Date', 'awesome-support-time-tracking' ),
							'end_date' => __( 'End Date', 'awesome-support-time-tracking' ),
							'ticket_id' => __( 'Ticket #', 'awesome-support-time-tracking' ),
							'ticket_reply_id' => __( 'Ticket Reply #', 'awesome-support-time-tracking' ),
							'time_recorded' => __( 'Time Recorded (minutes)', 'awesome-support-time-tracking' ),
							'total_time' => __( 'Total Time Spent: ', 'awesome-support-time-tracking' ),
							'hours' => __( 'hour(s)', 'awesome-support-time-tracking' ),
							'minutes' => __( 'minute(s)', 'awesome-support-time-tracking' ),
							'all_closed_tickets' => __( 'All closed tickets', 'awesome-support-time-tracking' ),
							'selected_closed_tickets' => __( 'Selected closed tickets', 'awesome-support-time-tracking' ),
							'all_tickets' => __( 'All tickets', 'awesome-support-time-tracking' ),
							'selected_tickets' => __( 'Selected tickets', 'awesome-support-time-tracking' ),
              'ticket_id' => __( 'Ticket #', 'awesome-support-time-tracking' ),
              'ticket_agent' => __( 'Agent', 'awesome-support-time-tracking' )
							) );
  }
}

add_action( 'admin_enqueue_scripts', 'as_time_tracking_enqueue_reporting_js' );

/**
 * Queries the $wpdb object
 *
 * @param		String  $query_str   The db query text
 *
 * @since   0.1.0
 * @return  object
 */
function as_time_tracking_query_wpdb_obj( $query_str ) {
  global $wpdb;
	$db_result = $wpdb->get_results( $query_str, OBJECT );

  return $db_result;
}

/**
 * Handler function for ajax, resets the values when the filter ticket status changes.
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_report_filter_status_change() {
  check_ajax_referer( 'as-time-tracking-reporting-nonce', 'security' );
  $query_str = "SELECT ID, post_title, post_status FROM " . AS_TT_DB_PREFIX . "posts WHERE post_type = 'ticket' AND post_status NOT LIKE '%auto-draft%' AND post_status != 'trash'";
  $db_result = as_time_tracking_query_wpdb_obj( $query_str );
  $ticket_status = sanitize_text_field( $_POST['status'] );
  $updated_tickets = array();

	//Search through tickets, if "all" option is chosen anything but "closed" status can be returned
	foreach( $db_result as $ticket ) {
		$status = get_post_meta( $ticket->ID, "_wpas_status", true );
		if( $ticket_status === "closed" && $status === "closed" ) {
			$updated_tickets[] = $ticket;
		} elseif( $ticket_status !== "closed" && $status !== "closed" ) {
			$updated_tickets[] = $ticket;
		}
	}

	wp_send_json_success( $updated_tickets );

	wp_die();
}

add_action( 'wp_ajax_as_time_tracking_report_filter_status_change', 'as_time_tracking_report_filter_status_change' );

/**
 * Validates the agents field of the filter. Used as a helped function for other ajax calls.
 *
 * @param 	boolean $all_agent_check				Indicator if all agents filter field was checked
 * @param 	boolean $selected_agent_check		Indicator if selected agents filter field was checked
 * @param 	string  $selected_agent_val			Agents selected in the filter
 * @param 	boolean $filter_passed					The current passed boolean value
 * @param 	object  $agent									Agent information
 *
 * @since 	0.1.0
 * @return 	boolean
 */
 function as_time_tracking_filter_agent_check( $all_agent_check, $selected_agent_check, $selected_agent_val, $filter_passed, $agent ) {
	 $selected_agent_val = ( array )$selected_agent_val; //force array as empty entries returns string

	 //If previous check failed we know that the entry won't be valid
	 	if( $filter_passed == FALSE ) {
	 		return false;
	 	}

	 //Logic is all checkbox isn't selected
	 if( $all_agent_check == "false" && $selected_agent_check == "true" && $filter_passed == TRUE ) {
		 if( !empty( $selected_agent_val ) ) {
			 if( in_array( $agent->ID, $selected_agent_val ) ) {
				 $updated_filter_passed = true;
			 } else {
				 $updated_filter_passed = false;
			 }
		 } else {
			 $updated_filter_passed = false;
		 }
	 } else {
		 $updated_filter_passed = true; //true when all checkbox is selected
	 }

	 return $updated_filter_passed;
 }

/**
 * Validates the customer fields on the filter. Used as a helped function for other ajax calls.
 *
 * @param 	boolean $all_customer_check					Indicator if all customers filter field was checked
 * @param 	boolean $selected_customer_check		Indicator if selected customers filter field was checked
 * @param 	string  $selected_customer_val			Customers selected in the filter
 * @param 	object  $client											Client information
 * @param 	boolean $filter_passed							The current passed boolean value
 *
 * @since 	0.1.0
 * @return 	boolean
 */
function as_time_tracking_filter_customer_check( $all_customer_check, $selected_customer_check, $selected_customer_val, $client, $filter_passed ) {
	$selected_customer_val = ( array )$selected_customer_val; //force array as empty entries returns string

	//If previous check failed we know that the entry won't be valid
	if( $filter_passed == FALSE ) {
		return false;
	}

	//Logic is all checkbox isn't selected
	if( $all_customer_check == "false" && $selected_customer_check == "true" && $filter_passed == TRUE ) {
		if( !empty( $selected_customer_val ) ) {
			if( isset( $client->ID ) ) {
				if( in_array( $client->ID, $selected_customer_val ) ) {
					$updated_filter_passed = true;
				} else {
					$updated_filter_passed = false;
				}
			} else {
				$updated_filter_passed = false;
			}
		} else {
			$updated_filter_passed = false;
		}
	} else {
		$updated_filter_passed = true; //true when all checkbox is selected
	}

	return $updated_filter_passed;
}

/**
 * Validates the ticket fields on the filter. Used as a helped function for other ajax calls.
 *
 * @param 	boolean $all_ticket_check						Indicator if all tickets filter field was checked
 * @param 	boolean $selected_ticket_check			Indicator if selected tickets filter field was checked
 * @param 	string  $all_closed_val							The filter ticket status value
 * @param 	string  $selected_ticket_val				The selected tickets value(s)
 * @param 	boolean $filter_passed							The current passed boolean value
 * @param 	string  $ticket_status							The status of the ticket
 * @param 	object  $meta_info									Meta information taken from postmeta
 *
 * @since 	0.1.0
 * @return 	boolean
 */
function as_time_tracking_filter_ticket_check( $all_ticket_check, $selected_ticket_check, $all_closed_val, $selected_ticket_val, $filter_passed, $ticket_status, $meta_info ) {
	$selected_ticket_val = ( array )$selected_ticket_val; //force array as empty entries returns string

	//If the previous step failed we already know the entry failed the filter
	if( $filter_passed == FALSE ) {
		return false;
	}

	/** Logic if the all checkbox not set, unlike the other filters we need two checks for closed and non closed
	 * 	ticket statuses. */
	if( $all_ticket_check == "true" && $selected_ticket_check == "false" && $filter_passed == TRUE ) {
		$updated_filter_passed = as_time_tracking_reporting_ticket_status_check_true( $all_closed_val, $ticket_status );
	} elseif( $all_ticket_check == "false" && $selected_ticket_check == "true" && $filter_passed == TRUE ) {
		$updated_filter_passed = as_time_tracking_reporting_ticket_status_check_false( $all_closed_val, $selected_ticket_val, $meta_info, $ticket_status );
	} else {
		$updated_filter_passed = true; //true when all checkbox is selected
	}

	return $updated_filter_passed;
}

/**
 * Used as a helper function to check ticket status for filter validation. Used when the
 * all tickets checkbox is true.
 *
 * @param 	string $all_closed_val		The value of the filter ticket status field
 * @param 	string $ticket_status			The ticket status
 *
 * @since 	0.1.0
 * @return 	boolean
 */
function as_time_tracking_reporting_ticket_status_check_true( $all_closed_val, $ticket_status ) {
  if( $all_closed_val == "closed" ) {
    if( $ticket_status == "closed" ) {
      $updated_filter_passed = true;
    } else {
      $updated_filter_passed = false;
    }
  } else {
    if( $ticket_status != "closed" ) {
      $updated_filter_passed = true;
    } else {
      $updated_filter_passed = false;
    }
  }

  return $updated_filter_passed;
}

/**
 * Used as a helper function to check ticket status for filter validation. Used when the
 * all tickets checkbox is false.
 *
 * @param 	string 	$all_closed_val				The value of the filter ticket status field
 * @param 	array 	$selected_ticket_val	The selected ticket value(s) from the filter
 * @param 	object 	$meta_info						Meta information taken from postmeta
 * @param 	string 	$ticket_status				The ticket status
 *
 * @since 	0.1.0
 * @return 	boolean
 */
function as_time_tracking_reporting_ticket_status_check_false( $all_closed_val, $selected_ticket_val, $meta_info, $ticket_status ) {
  if( $all_closed_val == "closed" ) {
    if( !empty( $selected_ticket_val ) ) {
      $updated_filter_passed = determine_time_report_ticket_status_closed( $meta_info, $selected_ticket_val, $ticket_status );
    } else {
      $updated_filter_passed = false;
    }
  } else {
    $updated_filter_passed = determine_time_report_ticket_status_open( $meta_info, $selected_ticket_val, $ticket_status );
  }

  return $updated_filter_passed;
}

/**
 * Helper function for as_time_tracking_filter_ticket_check, checks ticket status.
 *
 * @param		object   $meta_info             The meta info taken from the database
 * @param		array    $selected_ticket_val   The selected tickets from the filter
 * @param		string   $ticket_status         The ticket status
 *
 * @since   0.1.0
 * @return  boolean
 */
function determine_time_report_ticket_status_closed( $meta_info, $selected_ticket_val, $ticket_status ) {
  if( in_array( $meta_info[0]['ticket_id'], $selected_ticket_val ) && $ticket_status == "closed" ) {
    $updated_filter_passed = true;
  } else {
    $updated_filter_passed = false;
  }

  return $updated_filter_passed;
}

/**
 * Used as a helper function. Checks that the entry and ticket status are open.
 *
 * @param		object   $meta_info             The meta info taken from the database
 * @param		array    $selected_ticket_val   The selected tickets from the filter
 * @param		string   $ticket_status         The ticket status
 *
 * @since   0.1.0
 * @return  boolean
 */
function determine_time_report_ticket_status_open( $meta_info, $selected_ticket_val, $ticket_status ) {
  if( in_array( $meta_info[0]['ticket_id'], $selected_ticket_val ) && $ticket_status != "closed" ) {
    $updated_filter_passed = true;
  } else {
    $updated_filter_passed = false;
  }

  return $updated_filter_passed;
}

/**
 * Helper function which returns json decoded values if not empty
 *
 * @param		string  $val_to_check   The value to check
 *
 * @since   0.1.0
 * @return  array
 */
function determine_selected_report_filter_val( $val_to_check ) {
	$final_val = wp_unslash( $val_to_check );

	if( strlen( $final_val ) > 0 ) {
		$final_val = json_decode( $final_val );
	}

	return $final_val;
}

/**
 * Checks the date ranges
 *
 * @param		string  $end_date   End date to check
 * @param		string  $from_date  From date to check
 * @param		string  $to_date    To date to check
 *
 * @since   0.1.0
 * @return  boolean
 */
function as_time_tracking_date_range_check( $end_date, $from_date, $to_date ) {
  if ( ( $end_date >= $from_date ) && ( $end_date <= $to_date )	) {
    $filter_passed = true;
  } else {
    $filter_passed = false;
  }

  return $filter_passed;
}

/**
 * Helper function to determine date fields needed for checks/report data.
 *
 * @param		object  $meta_info   The meta information
 *
 * @since   0.1.0
 * @return  array
 */
function as_time_tracking_get_date_info( $meta_info ) {
  $date_info = array();

  $date_info['entry_date'] = substr( $meta_info[0]['entry_date_time'], 0, 10 );
  $date_info['s_date_no_time'] = substr( $meta_info[0]['start_date_time'], 0, 10 );
  $date_info['e_date_no_time'] = substr( $meta_info[0]['end_date_time'], 0, 10 );
  $date_info['e_date'] = date( $date_info['e_date_no_time'] );
  $date_info['end_date'] = strtotime( $date_info['e_date'] );

  return $date_info;
}

/**
 * Helper function to sanitize posted data used for reports from filter and return them as an array.
 *
 * @param		string  $post   Posted information
 *
 * @since   0.1.0
 * @return  array
 */
function as_time_tracking_retrieve_time_reporting_post_values( $post ) {
	$posted_vals = array();
	$posted_vals['from_date'] = strtotime( sanitize_text_field( $post['from_date'] ) );
	$posted_vals['to_date'] = strtotime( sanitize_text_field( $post['to_date'] ) );
	$posted_vals['all_agent_check'] = sanitize_text_field( $post['all_agent_check'] );
	$posted_vals['selected_agent_check'] = sanitize_text_field( $post['selected_agent_check'] );
	$posted_vals['selected_agent_val'] = sanitize_text_field( $post['selected_agent_val'] );
	$posted_vals['selected_agent_val'] = determine_selected_report_filter_val( $posted_vals['selected_agent_val'] );
	$posted_vals['all_customer_check'] = sanitize_text_field( $post['all_customer_check'] );
	$posted_vals['selected_customer_check'] = sanitize_text_field( $post['selected_customer_check'] );
	$posted_vals['selected_customer_val'] =  sanitize_text_field( $post['selected_customer_val'] );
	$posted_vals['selected_customer_val'] = determine_selected_report_filter_val( $posted_vals['selected_customer_val'] );
	$posted_vals['all_closed_val'] = sanitize_text_field( $post['all_closed_val'] );
	$posted_vals['all_ticket_check'] = sanitize_text_field( $post['all_ticket_check'] );
	$posted_vals['selected_ticket_check'] = sanitize_text_field( $post['selected_ticket_check'] );
	$posted_vals['selected_ticket_val'] = sanitize_text_field( $post['selected_ticket_val'] );
	$posted_vals['selected_ticket_val'] = determine_selected_report_filter_val( $posted_vals['selected_ticket_val'] );

	return $posted_vals;
}

/**
* Handler function for ajax, generates invoice report information.
*
* @since 	0.1.0
* @return void
*/
function as_time_tracking_invoice_report() {
  check_ajax_referer( 'as-time-tracking-reporting-nonce', 'security' );
  $posted_vals = as_time_tracking_retrieve_time_reporting_post_values( $_POST );
  $support_info = array();
  $missing_client = false;
  $query_str = "SELECT ID FROM " . AS_TT_DB_PREFIX . "posts WHERE post_type = 'trackedtimes' AND post_status NOT LIKE '%auto-draft%' AND post_status != 'trash'";
  $db_result = as_time_tracking_query_wpdb_obj( $query_str );

	foreach( $db_result as $id ) {
		$meta_info = get_post_meta( $id->ID, 'as_time_tracking_entry' );

    if( !empty( $meta_info ) ) {
      $date_info = as_time_tracking_get_date_info( $meta_info );
  		$agent = get_userdata( $meta_info[0]['agent'] );
  		$ticket_obj = get_post( $meta_info[0]['ticket_id'] );
			( !empty( $ticket_obj->post_author ) ? $client = get_userdata( $ticket_obj->post_author ) : $client = "" );
  		$ticket_status = wpas_get_ticket_status( $meta_info[0]['ticket_id'] );
      $filter_passed = as_time_tracking_date_range_check( $date_info['end_date'], $posted_vals['from_date'], $posted_vals['to_date'] );
  		$filter_passed = as_time_tracking_filter_agent_check( $posted_vals['all_agent_check'], $posted_vals['selected_agent_check'], $posted_vals['selected_agent_val'], $filter_passed, $agent );
  		$filter_passed = as_time_tracking_filter_customer_check( $posted_vals['all_customer_check'], $posted_vals['selected_customer_check'], $posted_vals['selected_customer_val'], $client, $filter_passed );
  		$filter_passed = as_time_tracking_filter_ticket_check( $posted_vals['all_ticket_check'], $posted_vals['selected_ticket_check'], $posted_vals['all_closed_val'], $posted_vals['selected_ticket_val'], $filter_passed, $ticket_status, $meta_info );
  		//If all filter checks passed then add entry to final Array
  		if( $filter_passed === true ) {
        if( $meta_info[0]['invoiced'] == "" ) {
          $query_str = 'SELECT post_author FROM ' . AS_TT_DB_PREFIX . 'posts WHERE ID = ' . $meta_info[0]['ticket_id'];
          $support_user_id = as_time_tracking_query_wpdb_obj( $query_str );
          if(
            ((current_user_can( 'view_other_time_reports' ) && $meta_info[0]['agent'] != get_current_user_id()) ||
            (current_user_can( 'view_own_time_reports' ) && $meta_info[0]['agent'] == get_current_user_id())) && !empty( $support_user_id )
          ) {
      			$calculated_time = $meta_info[0]['individual_time'];
      			$support_user = get_userdata( $support_user_id[0]->post_author );
            $agent_name = $agent->first_name . " " . $agent->last_name;
						( $agent->first_name == '' && $agent->last_name == '' ? $agent_name = $agent->user_login : '' );
      			$support_info[$support_user->display_name . " (#" . $support_user_id[0]->post_author . ")"][] = array(
                                                      'agent_name' => $agent_name,
      																								'ticket_id' => $meta_info[0]['ticket_id'],
      																								'ticket_reply' => $meta_info[0]['ticket_reply'],
      																								'individual_time' => $calculated_time,
      																								'entry_date' => $date_info['entry_date'],
      																								'start_date' => $date_info['s_date_no_time'],
      																								'end_date' => $date_info['e_date_no_time']
      																							);
          } elseif( empty( $support_user_id ) ) {
          	$missing_client = true;
          }
        }
  		}
    }
	}

	$returnData = array( $support_info, $missing_client );

	wp_send_json_success( $returnData );

  wp_die();
}

add_action( 'wp_ajax_invoice_report_full_action', 'as_time_tracking_invoice_report' );

/**
* Handler function for ajax, generates agent report information.
*
* @since 	0.1.0
* @return void
*/
function as_time_tracking_agent_report() {
  check_ajax_referer( 'as-time-tracking-reporting-nonce', 'security' );
  $posted_vals = as_time_tracking_retrieve_time_reporting_post_values( $_POST );
  $agent_info = array();
  $query_str = "SELECT ID FROM " . AS_TT_DB_PREFIX . "posts WHERE post_type = 'trackedtimes' AND post_status NOT LIKE '%auto-draft%' AND post_status != 'trash'";
  $db_result = as_time_tracking_query_wpdb_obj( $query_str );

	foreach( $db_result as $id ) {
		$meta_info = get_post_meta( $id->ID, 'as_time_tracking_entry' );

    if( !empty( $meta_info ) ) {
      $date_info = as_time_tracking_get_date_info( $meta_info );
  		$agent = get_userdata( $meta_info[0]['agent'] );
  		$ticket_obj = get_post( $meta_info[0]['ticket_id'] );
			( !empty( $ticket_obj->post_author ) ? $client = get_userdata( $ticket_obj->post_author ) : $client = "" );
  		$ticket_status = wpas_get_ticket_status( $meta_info[0]['ticket_id'] );
      $filter_passed = as_time_tracking_date_range_check( $date_info['end_date'], $posted_vals['from_date'], $posted_vals['to_date'] );
      $filter_passed = as_time_tracking_filter_agent_check( $posted_vals['all_agent_check'], $posted_vals['selected_agent_check'], $posted_vals['selected_agent_val'], $filter_passed, $agent );
      $filter_passed = as_time_tracking_filter_customer_check( $posted_vals['all_customer_check'], $posted_vals['selected_customer_check'], $posted_vals['selected_customer_val'], $client, $filter_passed );
      $filter_passed = as_time_tracking_filter_ticket_check( $posted_vals['all_ticket_check'], $posted_vals['selected_ticket_check'], $posted_vals['all_closed_val'], $posted_vals['selected_ticket_val'], $filter_passed, $ticket_status, $meta_info );
  		//If all filter checks passed then add entry to final Array
  		if( $filter_passed === true ) {
        if(
          ((current_user_can( 'view_other_time_reports' ) && $meta_info[0]['agent'] != get_current_user_id()) ||
          (current_user_can( 'view_own_time_reports' ) && $meta_info[0]['agent'] == get_current_user_id()))
        ) {
					$calculated_time = $meta_info[0]['individual_time'];
    			$agent_info[$agent->display_name . " (#" . $meta_info[0]['agent'] . ")"][] = array(
    																																										'ticket_id' => $meta_info[0]['ticket_id'],
    																																										'ticket_reply' => $meta_info[0]['ticket_reply'],
    																																										'individual_time' => $calculated_time,
    																																										'entry_date' => $date_info['entry_date'],
    																																										'start_date' => $date_info['s_date_no_time'],
    																																										'end_date' => $date_info['e_date_no_time']
    																																									);
        }
      }
    }
	}

	$returnData = array( $agent_info, false ); //We pass false as no messages needed on this report for missing clients

  wp_send_json_success( $returnData );

  wp_die();
}

add_action( 'wp_ajax_agent_report_full_action', 'as_time_tracking_agent_report' );

/**
 * Handler function for ajax, generates client report information.
 *
 * @since 0.1.0
 * @return void
 */
function as_time_tracking_client_report() {
  check_ajax_referer( 'as-time-tracking-reporting-nonce', 'security' );
  $posted_vals = as_time_tracking_retrieve_time_reporting_post_values( $_POST );
  $support_info = array();
  $missing_client = false;
  $query_str = "SELECT ID FROM " . AS_TT_DB_PREFIX . "posts WHERE post_type = 'trackedtimes' AND post_status NOT LIKE '%auto-draft%' AND post_status != 'trash'";
  $db_result = as_time_tracking_query_wpdb_obj( $query_str );

	foreach( $db_result as $id ) {
		$meta_info = get_post_meta( $id->ID, 'as_time_tracking_entry' );

    if( !empty( $meta_info ) ) {
      $date_info = as_time_tracking_get_date_info( $meta_info );
  		$agent = get_userdata( $meta_info[0]['agent'] );
  		$ticket_obj = get_post( $meta_info[0]['ticket_id'] );
			( !empty( $ticket_obj->post_author ) ? $client = get_userdata( $ticket_obj->post_author ) : $client = "" );
  		$ticket_status = wpas_get_ticket_status( $meta_info[0]['ticket_id'] );
      $filter_passed = as_time_tracking_date_range_check( $date_info['end_date'], $posted_vals['from_date'], $posted_vals['to_date'] );
  		$filter_passed = as_time_tracking_filter_agent_check( $posted_vals['all_agent_check'], $posted_vals['selected_agent_check'], $posted_vals['selected_agent_val'], $filter_passed, $agent );
  		$filter_passed = as_time_tracking_filter_customer_check( $posted_vals['all_customer_check'], $posted_vals['selected_customer_check'], $posted_vals['selected_customer_val'], $client, $filter_passed );
  		$filter_passed = as_time_tracking_filter_ticket_check( $posted_vals['all_ticket_check'], $posted_vals['selected_ticket_check'], $posted_vals['all_closed_val'], $posted_vals['selected_ticket_val'], $filter_passed, $ticket_status, $meta_info );
  		//If all filter checks passed then add entry to final Array
  		if( $filter_passed === true ) {
        $query_str = 'SELECT post_author FROM ' . AS_TT_DB_PREFIX . 'posts WHERE ID = ' . $meta_info[0]['ticket_id'];
        $support_user_id = as_time_tracking_query_wpdb_obj( $query_str );
        if(
          ((current_user_can( 'view_other_time_reports' ) && $meta_info[0]['agent'] != get_current_user_id()) ||
          (current_user_can( 'view_own_time_reports' ) && $meta_info[0]['agent'] == get_current_user_id())) &&
          !empty( $support_user_id )
        ) {
      			$calculated_time = $meta_info[0]['individual_time'];
      			$support_user = get_userdata( $support_user_id[0]->post_author );
            $agent_name = $agent->first_name . " " . $agent->last_name;
            ( $agent->first_name == '' && $agent->last_name == '' ? $agent_name = $agent->user_login : '' );
      			$support_info[$support_user->display_name . " (#" . $support_user_id[0]->post_author . ")"][] = array(
                                                      'agent_name' => $agent_name,
      																								'ticket_id' => $meta_info[0]['ticket_id'],
      																								'ticket_reply' => $meta_info[0]['ticket_reply'],
      																								'individual_time' => $calculated_time,
      																								'entry_date' => $date_info['entry_date'],
      																								'start_date' => $date_info['s_date_no_time'],
      																								'end_date' => $date_info['e_date_no_time']
      																							);
        } elseif( empty( $support_user_id ) ) {
        	$missing_client = true;
        }
  		}
    }
	}

	$return_data = array( $support_info, $missing_client );

	wp_send_json_success( $return_data );

  wp_die();
}

add_action( 'wp_ajax_client_report_full_action', 'as_time_tracking_client_report' );

/**
 * Handler function for ajax, generates ticket report information.
 *
 * @since 0.1.0
 * @return void
 */
function as_time_tracking_ticket_report() {
  check_ajax_referer( 'as-time-tracking-reporting-nonce', 'security' );
  $posted_vals = as_time_tracking_retrieve_time_reporting_post_values( $_POST );
  $ticket_info = array();
  $query_str = "SELECT ID FROM " . AS_TT_DB_PREFIX . "posts WHERE post_type = 'trackedtimes' AND post_status NOT LIKE '%auto-draft%' AND post_status != 'trash'";
  $db_result = as_time_tracking_query_wpdb_obj( $query_str );

	foreach( $db_result as $id ) {
		$meta_info = get_post_meta( $id->ID, 'as_time_tracking_entry' );

    if( !empty( $meta_info ) ) {
      $date_info = as_time_tracking_get_date_info( $meta_info );
  		$calculated_time = $meta_info[0]['individual_time'];
  		$ticket_name = get_the_title( $meta_info[0]['ticket_id'] );

  		$agent = get_userdata( $meta_info[0]['agent'] );
  		$ticket_obj = get_post( $meta_info[0]['ticket_id'] );
			( !empty( $ticket_obj->post_author ) ? $client = get_userdata( $ticket_obj->post_author ) : $client = "" );
  		$ticket_status = wpas_get_ticket_status( $meta_info[0]['ticket_id'] );
      $filter_passed = as_time_tracking_date_range_check( $date_info['end_date'], $posted_vals['from_date'], $posted_vals['to_date'] );
  		$filter_passed = as_time_tracking_filter_agent_check( $posted_vals['all_agent_check'], $posted_vals['selected_agent_check'], $posted_vals['selected_agent_val'], $filter_passed, $agent );
  		$filter_passed = as_time_tracking_filter_customer_check( $posted_vals['all_customer_check'], $posted_vals['selected_customer_check'], $posted_vals['selected_customer_val'], $client, $filter_passed );
  		$filter_passed = as_time_tracking_filter_ticket_check( $posted_vals['all_ticket_check'], $posted_vals['selected_ticket_check'], $posted_vals['all_closed_val'], $posted_vals['selected_ticket_val'], $filter_passed, $ticket_status, $meta_info );
  		//If all filter checks passed then add entry to final Array
  		if( $filter_passed === true ) {
        if(
          ((current_user_can( 'view_other_time_reports' ) && $meta_info[0]['agent'] != get_current_user_id()) ||
          (current_user_can( 'view_own_time_reports' ) && $meta_info[0]['agent'] == get_current_user_id()))
        ) {

          $agent_name = $agent->first_name . " " . $agent->last_name;
          ( $agent->first_name == '' && $agent->last_name == '' ? $agent_name = $agent->user_login : '' );
    			$ticket_info[$ticket_name][] = array(
                                            'ticket_id' => $meta_info[0]['ticket_id'],
                                            'agent_name' => $agent_name,
    																				'ticket_reply' => $meta_info[0]['ticket_reply'],
    																				'individual_time' => $calculated_time,
    																				'entry_date' => $date_info['entry_date'],
    																				'start_date' => $date_info['s_date_no_time'],
    																				'end_date' => $date_info['e_date_no_time']
    																			);
        }
  		}
    }
	}

	$returnData = array( $ticket_info, false ); //We pass false as no messages needed on this report for missing clients

	wp_send_json_success( $returnData );

  wp_die();
}

add_action( 'wp_ajax_ticket_report_full_action', 'as_time_tracking_ticket_report' );
