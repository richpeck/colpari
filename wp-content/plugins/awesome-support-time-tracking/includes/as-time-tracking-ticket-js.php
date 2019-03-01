<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Add main javascript file for edit page of tickets and ticket replies.
 *
 * @since 	0.1.0
 * @return 	void
 */
 function as_time_tracking_ticket_enqueue_js() {
   global $current_screen;

	 if( $current_screen->id == 'edit-ticket' ) {
		 //wp_enqueue_script( 'as_time_tracking_ticket_edit_timer_script', AS_TT_URL . "js/as-time-tracking-timer.js", array( 'jquery' ) );
     //wp_enqueue_script( 'as_time_tracking_ticket_edit_js_script', AS_TT_URL . "js/as-time-tracking-ticket.js", array( 'jquery' ) );
	 }

   if( $current_screen->id == 'ticket' ) {
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

     wp_enqueue_script( 'as_time_tracking_ticket_js_script', AS_TT_URL . "js/as-time-tracking-ticket.js", array( 'jquery' ) );
     wp_localize_script( 'as_time_tracking_ticket_js_script', 'js_ticket_object',	array(
			 																										'ajax_nonce' => wp_create_nonce( 'as-time-tracking-ticket-nonce' ),
                                                          'manual_empty_timer' => __( 'Enter time spent in the timer metabox or start the timer to initiate automatic time tracking.', 'awesome-support-time-tracking' ),
                                                          'time_recorded_text' => __( 'Time currently recorded for this reply is: ', 'awesome-support-time-tracking' ),
                                                          'create_entry_timer' => __( 'Stop Timer and Create Time Entry', 'awesome-support-time-tracking' ),
                                                          'create_entry' => __( 'Create Time Entry', 'awesome-support-time-tracking' ),
																													'ticket_id' => get_the_ID(),
                                                          'ajax_url' => admin_url( 'admin-ajax.php' ),
                                                          'success' => __( 'The Tracked Time entry has been successfully saved. To view the updated detailed time information you will need to reload the page.', 'awesome-support-time-tracking' ),
                                                          'error' => __( 'There has been a problem creating the Tracked Time entry. Please try again.', 'awesome-support-time-tracking' ),
																											 ) );


   }
 }

add_action( 'admin_enqueue_scripts', 'as_time_tracking_ticket_enqueue_js' );

/**
* Add jQuery UI Datepicker javascript.
*
* @since 0.1.0
*/
function as_time_tracking_ticket_enqueue_datepicker() {
 global $current_screen;

 if( $current_screen->id == 'ticket' ) {
   wp_enqueue_script( 'jquery-ui-datepicker' );
 }
}

add_action( 'admin_enqueue_scripts', 'as_time_tracking_ticket_enqueue_datepicker' );

/**
 * Ajax handler to save on the ticket level only from the overlay.
 *
 * @since   0.1.0
 * @return  void
 */
function as_time_tracking_save_ticket_level_description() {
	check_ajax_referer( 'as-time-tracking-ticket-nonce', 'security' );
  $allowed_multiple = wpas_get_option( 'time_tracking_ticket_allow_multiple_entries', 'no' );
  if( $allowed_multiple == 'yes' ) {
    $allowed_multiple = true;
  } else {
    $allowed_multiple = false;
  }

  $data_to_save = array();
  $data_to_save['ticket_id'] = sanitize_text_field( $_POST['ticket_id'] );
  $data_to_save['ticket_reply'] = '';
  $data_to_save['entry_date_time'] = date( 'Y-m-d H:i:s', time() );
  $data_to_save['start_date_time'] = sanitize_text_field( $_POST['start_date_time'] );
  $data_to_save['end_date_time'] = sanitize_text_field( $_POST['end_date_time'] );
  $data_to_save['notes'] = sanitize_text_field( $_POST['description'] );
  $data_to_save['agent'] = get_current_user_id();
  $data_to_save['invoiced'] = '';
  $data_to_save['invoice_number'] = '';

  $time_vals = array(
    'as_time_tracking_tickets_hours_field' => $_POST['individual_hours'],
    'as_time_tracking_tickets_minutes_field' => $_POST['individual_minutes'],
    'as_time_tracking_tickets_seconds_field' => $_POST['individual_seconds']
  );
  $time_hours_minutes = as_time_tracking_caluclate_individual_time( $time_vals );
  $unrounded_individual_time = $time_hours_minutes['individual_time_hours'] + $time_hours_minutes['individual_time_minutes'];
  $rounding_final_time_recorded = as_time_tracking_calculate_time_entered_rounding( $unrounded_individual_time );
  $final_individual_time = $rounding_final_time_recorded['hours'] * 60 + $rounding_final_time_recorded['rounded_amount'];

  $data_to_save['individual_time'] = $final_individual_time;
  $data_to_save['adjusted_time'] = 0;
  $data_to_save['is_ticket_reply_multiple'] = $allowed_multiple;
  $data_to_save['is_ticket_reply'] = false;
  $data_to_save['is_ticket_level'] = true;

  if( $data_to_save['is_ticket_level'] == true ) {
    $title = __( 'Ticket #', 'awesome-support-time-tracking' ) . $data_to_save['ticket_id'];
  } else {
    $title = __( 'Ticket #', 'awesome-support-time-tracking' ) . $data_to_save['ticket_id'] . __( ', Ticket reply #', 'awesome-support-time-tracking' ) . $data_to_save['ticket_reply'];
  }

	//Using the Divi theme there is a strange error which will occur when Using update_post_meta
	//with the below wp_insert_post and the time tracking post type. To get around this we set the
	//post type here to post, then after the update_post_meta calls change back the post type.
  $cpt_id = wp_insert_post( array(
    'post_title'=> $title,
    'post_type'=> 'post',
    'post_content' => '',
    'post_status' => 'publish' )
  );

  //Save the time tracking post meta entry
  update_post_meta( $cpt_id, 'as_time_tracking_entry', $data_to_save );

  //Update totals on the ticket
  $tracked_time_data = array();
  global $wpdb;
  $db_query = "SELECT DISTINCT " . $wpdb->prefix . "postmeta.post_id, " . $wpdb->prefix . "postmeta.meta_value FROM " . $wpdb->prefix . "postmeta INNER JOIN " . $wpdb->prefix . "posts ON " . $wpdb->prefix . "postmeta.post_id = " . $wpdb->prefix . "posts.ID AND " . $wpdb->prefix . "posts.post_type = 'trackedtimes' AND " . $wpdb->prefix . "postmeta.meta_key = 'as_time_tracking_entry'";
  $db_result = $wpdb->get_results( $db_query, OBJECT );

	//The above query won't get our latest time entry so we manually add it to the results here, otherwise, the total time is wrong
	$latest_meta = get_post_meta( $cpt_id, 'as_time_tracking_entry' );
	$db_entry_serialized = serialize( $latest_meta[0] );

	//Cast the entry into an simple object to simulate the structure of the other entries collected from the db call
	$db_entry = (object) [
		'post_id' => $cpt_id,
		'meta_value' => $db_entry_serialized
	];

	$db_result[] = $db_entry;
  $tracked_time_data = array();

  foreach( $db_result as $result ) {
    $serialized_data = array(
        'id' => $result->post_id,
        'serialized_data' => maybe_unserialize( $result->meta_value )
      );
    $tracked_time_data[] = $serialized_data;
  }

  $ticket_time_data = array();

  foreach( $tracked_time_data as $time_data ) {
    if( $time_data['serialized_data']['ticket_id'] == $data_to_save['ticket_id'] ) {
      $ticket_time_data[] = $time_data['serialized_data'];
    }
  }

  $calculated_time = 0;
  $time_adjustments = 0;

  if( isset( $ticket_time_data ) ) {
    foreach( $ticket_time_data as $time_data ) {
      $individual_time = $time_data['individual_time'];
      ( $individual_time != '' ?  $calculated_time += $individual_time : '' );
      $adjusted_time = $time_data['adjusted_time'];
      $adjusted_time = (int)$adjusted_time;
      ( $adjusted_time < 0 ? $time_adjustments -= substr( $adjusted_time, 1 ) : $time_adjustments += $adjusted_time );
    }
  }

  update_post_meta( $data_to_save['ticket_id'], '_wpas_ttl_adjustments_to_time_spent_on_ticket', $time_adjustments );

  //Update calculated time
  update_post_meta( $data_to_save['ticket_id'], '_wpas_ttl_calculated_time_spent_on_ticket', $calculated_time );

  //Update adjustment time
  $final_time = $time_adjustments + $calculated_time;
  update_post_meta( $data_to_save['ticket_id'], '_wpas_final_time_spent_on_ticket', $final_time );

	//Set the new post's post type back to trackedtimes
	global $wpdb;
	$table_name = $wpdb->prefix . "posts";

	$wpdb->update(
		$table_name,
		array(
			'post_type' => 'trackedtimes'
		),
		array( 'ID' => $cpt_id )
	);

	unset( $_POST );

  echo true;

  wp_die();
}

add_action( 'wp_ajax_ticket_level_description_save_action', 'as_time_tracking_save_ticket_level_description' );
