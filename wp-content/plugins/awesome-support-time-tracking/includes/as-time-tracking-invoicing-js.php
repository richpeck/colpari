<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Add JavaScript files that the invoicing needs. Pass through strings to allow for translations.
 *
 * @since 0.1.0
 * @return void
 */
function as_time_tracking_enqueue_invoicing_js() {

  global $current_screen;

  if( $current_screen->id == 'trackedtimes_page_trackedtimes-invoicing' ) {
    wp_enqueue_script( 'as_time_tracking_invoicing_js_script', AS_TT_URL . "js/as-time-tracking-invoicing.js", array( 'jquery' ) );
		wp_enqueue_script( 'as_time_tracking_invoicing_filter_setup', AS_TT_URL . "js/as-time-tracking-invoicing-filters.js", array( 'jquery' ) );
		wp_localize_script( 'as_time_tracking_invoicing_js_script', 'ajax_object',
            array(
							'ajax_url' => admin_url( 'admin-ajax.php' ),
              'ajax_nonce' => wp_create_nonce( 'as-time-tracking-invoicing-nonce' ),
							'site_url' => get_site_url(),
							'csv_fail' => __( "The CSV invoice file was not created. Please check the filter values or the permissions on the \"uploads\" directory.", "awesome-support-time-tracking" ),
							'invoice_success' => __( "The invoice CSV has been successfully created. You can download it through the below button and approve/dissaprove it.", "awesome-support-time-tracking" ),
							'invoice_default' => __( "The invoice CSV has been previously created and is awaiting approval/disapproval before another can be generated.", "awesome-support-time-tracking" ),
							'csv_delete_fail' => __( "There was a problem cancelling the invoiced records. Please check that the CSV file has the correct permissions.", "awesome-support-time-tracking" ),
							'csv_delete_success' => __( "The invoice run have been successfully cancelled.", "awesome-support-time-tracking" ),
							'csv_approve_success' => __( "The invoiced records have been successfully approved.", "awesome-support-time-tracking" ),
							'csv_approve_fail' => __( "There has been a problem approving the invoiced records. Please try again.", "awesome-support-time-tracking" )
							) );
	}
}

add_action( 'admin_enqueue_scripts', 'as_time_tracking_enqueue_invoicing_js' );

/**
* Handler function for ajax, gets data for previewing the CSV after it has been created.
*
* @since 	0.1.0
* @return void
*/
add_action( 'wp_ajax_csv_preview_action', 'as_time_tracking_csv_preview' );

function as_time_tracking_csv_preview() {
  check_ajax_referer( 'as-time-tracking-invoicing-nonce', 'security' );
	$file_contents = as_time_tracking_get_csv_contents();
	wp_send_json_success( $file_contents );
	wp_die();
}

/**
* Used as a helper function. Reads created csv file and returns the data.
*
* @since 	0.1.0
* @return array
*/
function as_time_tracking_get_csv_contents() {
  $upload_dir = wp_upload_dir();
  $base_upload_dir = $upload_dir['basedir'];
  $upload_path = $base_upload_dir . "/awesome-support/time-tracking";
  $custom_dir_exists = file_exists( $base_upload_dir . "/awesome-support/time-tracking" );

  if( $custom_dir_exists === false ) {
    return false;
  }

	$current_files = scandir( $upload_path );

	if( !in_array( "wpas_time_tracking_invoice.csv", $current_files ) ) {
		return false;
	} else {
		$file_contents = array();
		$file = fopen( $upload_path . "/wpas_time_tracking_invoice.csv","r" );

		while( !feof( $file ) ) {
			$file_contents[] = fgetcsv( $file );
		}

		fclose( $file );
		return $file_contents;
	}
}

/**
* Handler function for ajax, clears the inprocess flag as the cancel invoice run button was clicked.
*
* @since  0.1.0
* @return void
*/
add_action( 'wp_ajax_csv_file_disapprove_action', 'as_time_tracking_invoice_disapprove_csv' );

function as_time_tracking_invoice_disapprove_csv() {
  check_ajax_referer( 'as-time-tracking-invoicing-nonce', 'security' );
  $upload_dir = wp_upload_dir();
  $upload_path = $upload_dir['basedir']  . "/awesome-support/time-tracking/";
	$file_name = $upload_path . "wpas_time_tracking_invoice.csv";
	$current_files = scandir( $upload_path );

  //If we can't find the csv file then exit
	if( !in_array( "wpas_time_tracking_invoice.csv", $current_files ) ) {
    $return_val = false;
    echo $return_val;
    wp_die();
	}

	$delete_passed = unlink( $file_name );

  //Collect all tracked time entries and reset/save the flag if needed
	if( $delete_passed === true ) {
		global $wpdb;
		$db_query = "SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_type = 'trackedtimes' AND post_status NOT LIKE '%auto-draft%'";
		$db_result = $wpdb->get_results( $db_query, OBJECT );

			foreach( $db_result as $id ) {
				$meta_info = get_post_meta( $id->ID, 'as_time_tracking_entry' );

				if( isset( $meta_info[0] ) && $meta_info[0]['invoiced'] == "in process" ) {
					$data_to_save = array(
						'ticket_id' => $meta_info[0]['ticket_id'],
						'ticket_reply' => $meta_info[0]['ticket_reply'],
						'entry_date_time' => $meta_info[0]['entry_date_time'],
						'start_date_time' => $meta_info[0]['start_date_time'],
						'end_date_time' => $meta_info[0]['end_date_time'],
						'notes' => $meta_info[0]['notes'],
						'agent' => $meta_info[0]['agent'],
						'invoiced' => '',
						'invoice_number' => '',
            'individual_time' => $meta_info[0]['individual_time'],
            'adjusted_time' => $meta_info[0]['adjusted_time'],
            'is_ticket_reply_multiple' => $meta_info[0]['is_ticket_reply_multiple'],
            'is_ticket_reply' => $meta_info[0]['is_ticket_reply'],
            'is_ticket_level' => $meta_info[0]['is_ticket_level']
					);

					update_post_meta( $id->ID, 'as_time_tracking_entry', $data_to_save );
				}
			}

			$return_val = true;
		} else {
			$return_val = false;
		}

		echo $return_val;

		wp_die();
}

/**
* Handler function for ajax, sets all invoice fields to approved.
*
* @since  0.1.0
* @return void
*/
add_action( 'wp_ajax_csv_file_approve_action', 'as_time_tracking_invoice_approve_csv' );

function as_time_tracking_invoice_approve_csv() {
  check_ajax_referer( 'as-time-tracking-invoicing-nonce', 'security' );
  $upload_dir = wp_upload_dir();
  $upload_path = $upload_dir['basedir']  . "/awesome-support/time-tracking/";
	$file_name = $upload_path . "wpas_time_tracking_invoice.csv";
	$delete_passed = unlink( $file_name );

  //Loops through tracked time entries and changes the in process entries to approved, then saves them.
	if( $delete_passed === true ) {
		global $wpdb;
		$db_query = "SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_type = 'trackedtimes' AND post_status NOT LIKE '%auto-draft%'  AND post_status != 'trash'";
		$db_result = $wpdb->get_results( $db_query, OBJECT );

			foreach( $db_result as $id ) {
				$meta_info = get_post_meta( $id->ID, 'as_time_tracking_entry' );

				if( isset( $meta_info[0] ) && $meta_info[0]['invoiced'] == "in process" ) {
					$data_to_save = array(
						'ticket_id' => $meta_info[0]['ticket_id'],
						'ticket_reply' => $meta_info[0]['ticket_reply'],
						'entry_date_time' => $meta_info[0]['entry_date_time'],
						'start_date_time' => $meta_info[0]['start_date_time'],
						'end_date_time' => $meta_info[0]['end_date_time'],
						'notes' => $meta_info[0]['notes'],
						'agent' => $meta_info[0]['agent'],
						'invoiced' => 'approved',
						'invoice_number' => $meta_info[0]['invoice_number'],
            'individual_time' => $meta_info[0]['individual_time'],
            'adjusted_time' => $meta_info[0]['adjusted_time'],
            'is_ticket_reply_multiple' => $meta_info[0]['is_ticket_reply_multiple'],
            'is_ticket_reply' => $meta_info[0]['is_ticket_reply'],
            'is_ticket_level' => $meta_info[0]['is_ticket_level']
					);

					update_post_meta( $id->ID, 'as_time_tracking_entry', $data_to_save );
				}
			}

			$return_val = true;
		} else {
			$return_val = false;
		}

		echo $return_val;

		wp_die();
}

/**
* Handler function for ajax, creates invoice CSV.
*
* @since  0.1.0
* @return void
*/
add_action( 'wp_ajax_invoice_csv_action', 'as_time_tracking_invoice_csv' );

function as_time_tracking_invoice_csv() {
  check_ajax_referer( 'as-time-tracking-invoicing-nonce', 'security' );

	//Variables passed from JavaScript
  $field_values = as_time_tracking_invoicing_sanitize_values( $_POST );

	//CSV logic
	global $wpdb;
	$db_query = "SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_type = 'trackedtimes' AND post_status NOT LIKE '%auto-draft%' AND post_status != 'trash'";
	$db_result = $wpdb->get_results( $db_query, OBJECT );

	//Prepare csv data before creating csv and totals
	$ticket_info = as_time_tracking_invoicing_prepare_csv_data( $db_result, $field_values );

	//If the results aren't empty we attempt to create the CSV file and set the invoice flag on traked time records
	$csv_data = array();
	$csv_data[] = array(
		__( "Invoice Number", "awesome-support-time-tracking" ),
		__( "First Name", "awesome-support-time-tracking" ),
		__( "Last Name", "awesome-support-time-tracking" ),
		__( "Email", "awesome-support-time-tracking" ),
		__( "From Date", "awesome-support-time-tracking" ),
		__( "To Date", "awesome-support-time-tracking" ),
		__( "Total Minutes", "awesome-support-time-tracking" ),
		__( "Billing Total", "awesome-support-time-tracking" ),
	);

	$times_to_process = array();

	if( empty( $ticket_info ) ) {
		$return_val = false; //No records so CSV can't be created
	} else {
		$latest_invoice_number = get_option( 'as_time_tracking_invoice_count' );
		$old_invoice_number = $latest_invoice_number; //Used to reset counter if file fails
		$latest_invoice_number = (int)$latest_invoice_number + 1;

		foreach( $ticket_info as $info ) {
			$invoice_number = sprintf( "%06d", $latest_invoice_number );
      $time_total = 0;
      $total_entry = array();
      $float_billing_total = 0;
			$individual_agent_info = array();

			//Here calculate individual time and add to totals
			foreach( $info as $entry ) {
				$billing_rate = determine_billing_field( $entry['ticket_id'], $entry['user_id'], $entry['agent_id'] );

        $time_total = (int)$entry['calc_time'] + $time_total;
				$individual_agent_info[$entry['agent_id']][] = array(
					$billing_rate[0],
					$entry['calc_time'],
          $billing_rate[1]
				);

        $total_entry[0] = $invoice_number;
        $total_entry[1] = $entry['user_firstname'];
        $total_entry[2] = $entry['user_lastname'];
        $total_entry[3] = $entry['user_email'];
        $total_entry[4] = $entry['filter_from_date'];
        $total_entry[5] = $entry['filter_to_date'];

				$times_to_process[] = array( $entry['id'], $invoice_number );
			}

			$ticket_total = 0;
			$ticket_price_info = 0;

      foreach( $individual_agent_info as $info ) {
        foreach( $info as $time ) {
          $ticket_total = $ticket_total + $time[1];
					$ticket_price_info = $ticket_price_info + $time[1] / 60 * $time[0];
        }

				$float_billing_total = $ticket_price_info;
        $float_billing_total = number_format( $float_billing_total, 2, ".", "" );
      }

      $total_entry[] = $time_total;
      $total_entry[] = $float_billing_total;
      $csv_data[] = $total_entry;

			//After all user info is looped add totals to final array for CSV
			update_option( 'as_time_tracking_invoice_count', $latest_invoice_number );
			$latest_invoice_number++;
		}

		$return_val = as_time_tracking_generate_csv( $csv_data );

		//If CSV created, set inprocess flags to tracked times collected
		if( $return_val === true ) {
			$return_val = set_invoice_in_process_flags( $times_to_process );
		}

		//Reset invoice counter if file fails
		if( $return_val === false ) {
			update_option( 'as_time_tracking_invoice_count', $old_invoice_number );
		}
	}

	echo $return_val;

  wp_die();
}

/**
* Used as a helper function. Sanitizes posted values and returns them.
*
* @param object $post   The posted values
*
* @since  0.1.0
* @return array
*/
function as_time_tracking_invoicing_sanitize_values( $post ) {
  $field_values = array();

  $field_values['from_date'] = strtotime( sanitize_text_field( $post['from_date'] ) );
	$field_values['to_date'] = strtotime( sanitize_text_field( $post['to_date'] ) );
	$field_values['text_from_date'] = sanitize_text_field( $post['from_date'] );
	$field_values['text_to_date'] = sanitize_text_field( $post['to_date'] );

	$field_values['all_agent_check'] =  sanitize_text_field( $post['all_agent_check'] );
	$field_values['selected_agent_check'] = sanitize_text_field( $post['selected_agent_check'] );
	$field_values['selected_agent_val'] = sanitize_text_field( $post['selected_agent_val'] );
	$field_values['selected_agent_val'] = wp_unslash( $field_values['selected_agent_val'] );
	( strlen( $field_values['selected_agent_val'] ) > 0 ? $field_values['selected_agent_val'] = json_decode( $field_values['selected_agent_val'] ) : "" );

	$field_values['all_customer_check'] =  sanitize_text_field( $post['all_customer_check'] );
	$field_values['selected_customer_check'] = sanitize_text_field( $post['selected_customer_check'] );
	$field_values['selected_customer_val'] = sanitize_text_field( $post['selected_customer_val'] );
	$field_values['selected_customer_val'] = wp_unslash( $field_values['selected_customer_val'] );
	( strlen( $field_values['selected_customer_val'] ) > 0 ? $field_values['selected_customer_val'] = json_decode( $field_values['selected_customer_val'] ) : "" );

	$field_values['all_closed_val'] = sanitize_text_field( $post['all_closed_val'] );
	$field_values['all_ticket_check'] = sanitize_text_field( $post['all_ticket_check'] );
	$field_values['selected_ticket_check'] = sanitize_text_field( $post['selected_ticket_check'] );
	$field_values['selected_ticket_val'] = sanitize_text_field( $post['selected_ticket_val'] );
	$field_values['selected_ticket_val'] = wp_unslash( $field_values['selected_ticket_val'] );
	( strlen( $field_values['selected_ticket_val'] ) > 0 ? $field_values['selected_ticket_val'] = json_decode( $field_values['selected_ticket_val'] ) : "" );

  return $field_values;
}

/**
* Used as a helper function. Prepares the data to be created in the invoice csv and returns it.
*
* @param object $db_result      The time tracking entries
* @param array  $field_values   The values posted to the as_time_tracking_invoice_csv function
*
* @since  0.1.0
* @return array
*/
function as_time_tracking_invoicing_prepare_csv_data( $db_result, $field_values ) {
  $ticket_info = array();
  foreach( $db_result as $id ) {
    $meta_info = get_post_meta( $id->ID, 'as_time_tracking_entry' );
    if( isset( $meta_info[0] ) ) {
      $e_date_no_time = substr( $meta_info[0]['end_date_time'], 0, 10 );
      $e_date = date( $e_date_no_time );
      $end_date = strtotime( $e_date );
      $agent = get_userdata( $meta_info[0]['agent'] );
      $ticket_obj = get_post( $meta_info[0]['ticket_id'] );
      ( isset( $ticket_obj->post_author ) ? $client = get_userdata( $ticket_obj->post_author ) : $client = "" );
      $ticket_status = wpas_get_ticket_status( $meta_info[0]['ticket_id'] );
      $invoice_status = $meta_info[0]['invoiced'];

      //First must be in date range. We then go though each check using helper functions.
      if ( ( $end_date >= $field_values['from_date'] ) && ( $end_date <= $field_values['to_date'] )	) {
        $filter_passed = true;
      } else {
        $filter_passed = false;
      }

      $filter_passed = as_time_tracking_filter_agent_check( $field_values['all_agent_check'], $field_values['selected_agent_check'], $field_values['selected_agent_val'], $filter_passed, $agent );
      $filter_passed = as_time_tracking_filter_customer_check( $field_values['all_customer_check'], $field_values['selected_customer_check'], $field_values['selected_customer_val'], $client, $filter_passed );
      $filter_passed = as_time_tracking_filter_ticket_check( $field_values['all_ticket_check'], $field_values['selected_ticket_check'], $field_values['all_closed_val'], $field_values['selected_ticket_val'], $filter_passed, $ticket_status, $meta_info );

      //If all filter checks passed then add entry to final Array
      if( $filter_passed === true && $invoice_status != "approved" && isset( $client->ID ) ) {
        $calculated_time = $meta_info[0]['individual_time'];
        $assigned_agent = get_post_meta( $meta_info[0]['ticket_id'], '_wpas_assignee', true );
        $ticket_info[$client->ID][] = array(
                          'id' => $id->ID,
                          'calc_time' => $calculated_time,
                          'agent_id' => $agent->ID,
                          'assigned_agent' => $assigned_agent,
                          'user_firstname' => get_user_option( 'first_name', $client->ID ),
                          'user_lastname' => get_user_option( 'last_name', $client->ID ),
                          'user_email' => $client->user_email,
                          'filter_from_date' => $field_values['text_from_date'],
                          'filter_to_date' => $field_values['text_to_date'],
                          'ticket_id' => $meta_info[0]['ticket_id'],
                          'user_id' => $client->ID
                            );
      }
    }
  }

  return $ticket_info;
}

/**
* Used as a helper function. Determines which billing type will be used between ticket/client/agent billing.
*
* @param integer  $ticket_id  The ticket id
* @param integer  $user_id    The user id
* @param integer  $agent_id   The agent id
*
* @since  0.1.0
* @return array
*/
function determine_billing_field( $ticket_id, $user_id, $agent_id ) {
  $ticket_rate = get_post_meta( $ticket_id, 'as_time_tracking_ticket_number_rate', true );

  if( (float)$ticket_rate > 0.00 ) {
    return array( $ticket_rate, "T" );
  }

  $client_rate = get_user_option( 'as_time_tracking_client_billing_rate', $user_id );

  if( (float)$client_rate > 0.00 ) {
    return array( $client_rate, "U" );
  }

  $agent_rate = get_user_option( 'as_time_tracking_agent_billing_rate', $agent_id );

  if( (float)$agent_rate > 0.00 ) {
    return array( (float)$agent_rate, "A" );
  }

  return array( 0, "Billing rate not set" );
}

/**
* Used as a helper function. Updates time tracked entries in $data to in process and adds the invoice number generated.
*
* @param array  $data  The tracked time id and invoice number to update
*
* @since  0.1.0
* @return boolean
*/
function set_invoice_in_process_flags( $data ) {
	foreach( $data as $time ) {
		$saved_data = get_post_meta( $time[0], 'as_time_tracking_entry' );

		$data_to_save = array(
			'ticket_id' => $saved_data[0]['ticket_id'],
			'ticket_reply' => $saved_data[0]['ticket_reply'],
			'entry_date_time' => $saved_data[0]['entry_date_time'],
			'start_date_time' => $saved_data[0]['start_date_time'],
			'end_date_time' => $saved_data[0]['end_date_time'],
			'notes' => $saved_data[0]['notes'],
			'agent' => $saved_data[0]['agent'],
			'invoiced' => 'in process',
			'invoice_number' => $time[1],
      'individual_time' => $saved_data[0]['individual_time'],
      'adjusted_time' => $saved_data[0]['adjusted_time'],
      'is_ticket_reply_multiple' => $saved_data[0]['is_ticket_reply_multiple'],
      'is_ticket_reply' => $saved_data[0]['is_ticket_reply'],
      'is_ticket_level' => $saved_data[0]['is_ticket_level']
		);

		update_post_meta( $time[0], 'as_time_tracking_entry', $data_to_save );
	}

	return true;
}

/**
* Used as a helper function. Generates the csv file.
*
* @param array  $csv_info  The data to write to the csv
*
* @since  0.1.0
* @return boolean
*/
function as_time_tracking_generate_csv( $csv_info ) {
  $upload_dir = wp_upload_dir();
  $base_upload_dir = $upload_dir['basedir'];
  $upload_path = $base_upload_dir . "/awesome-support/time-tracking";
  $custom_dir_exists = file_exists( $base_upload_dir . "/awesome-support/time-tracking" );

  //Create directory to store the csv file if it doesn't exist, if we can't create it exit.
  if( $custom_dir_exists === false ) {
    $dir_created = mkdir( $base_upload_dir . "/awesome-support/time-tracking", 0755, true );

    if( $dir_created === false ) {
      return false;
    }
  }

	$csv_exists = as_time_tracking_csv_file_exists();
	$success = false;

	if( $csv_exists === true ) {
		return false;
	} else {
	  if ( !( $handle = fopen( $upload_path . "/wpas_time_tracking_invoice.csv", "w" ) ) ) {
			return false;
		} else {
			foreach( $csv_info as $user_info ) {
    		fputcsv( $handle, $user_info );
			}

			fclose( $handle );

			return true;
		}
	}
}

/**
* Used as a helper function. Checks if a csv already exists.
*
* @since  0.1.0
* @return boolean
*/
function as_time_tracking_csv_file_exists() {
  $upload_dir = wp_upload_dir();
  $upload_path = $upload_dir['basedir']  . "/awesome-support/time-tracking";
	$current_files = scandir( $upload_path );

	if( !in_array( "wpas_time_tracking_invoice.csv", $current_files ) ) {
		return false;
	} else {
		return true;
	}
}

/**
* When invoice page loads to check if CSV file exists. Used by invoicing JavaScript files.
*
* @since  0.1.0
* @return void
*/
function as_time_tracking_csv_file_exists_default() {
  check_ajax_referer( 'as-time-tracking-invoicing-nonce', 'security' );
  $upload_dir = wp_upload_dir();
  $base_upload_dir = $upload_dir['basedir'];
  $upload_path = $base_upload_dir . "/awesome-support/time-tracking";
  $custom_dir_exists = file_exists( $base_upload_dir . "/awesome-support/time-tracking" );

  if( $custom_dir_exists === false ) {
    echo 0;
    wp_die();
  }

	$current_files = scandir( $upload_path );

  if( !in_array( "wpas_time_tracking_invoice.csv", $current_files ) ) {
    echo 0;
  } else {
    echo 1;
  }

	wp_die();
}

add_action( 'wp_ajax_csv_file_exists', 'as_time_tracking_csv_file_exists_default' );

/**
 * Handler function for ajax, resets the values when the filter ticket status changes.
 *
 * @since 0.1.0
 */
function as_time_tracking_invoice_filter_status_change() {
  check_ajax_referer( 'as-time-tracking-invoicing-nonce', 'security' );
	//Get all tickets which don't have a draft or trash status
	global $wpdb;
	$updated_tickets = array();
	$ticket_status = sanitize_text_field( $_POST['status'] );
	$db_query = "SELECT ID, post_title, post_status FROM " . $wpdb->prefix . "posts WHERE post_type = 'ticket' AND post_status NOT LIKE '%auto-draft%' AND post_status != 'trash'";
	$db_result = $wpdb->get_results( $db_query, OBJECT );

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

add_action( 'wp_ajax_as_time_tracking_invoice_filter_status_change', 'as_time_tracking_invoice_filter_status_change' );
