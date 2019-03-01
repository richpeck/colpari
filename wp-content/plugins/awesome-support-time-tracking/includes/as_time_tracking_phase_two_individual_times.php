<?php
/**
 * Callback function. Changes the saved tracked times structure in the database for phase two of the plugin.
 *
 * @since   0.1.0
 * @return  void
 */
function as_time_tracking_phase_two_db_time_strucutre() {
	$phase_two_option = get_option( "as_time_tracking_combined_times", "no" );

	if( $phase_two_option === "no" ) {
		$individual_time_changes = as_time_tracking_change_tracked_time_structure();
	} else {
		$individual_time_changes = true;
	}

	if( $individual_time_changes !== true ) {
		wp_die( "<p>Awesome Support - Time Tracking. A problem has occured in updating the indiviudal tracked time entries strucutre.</p>" );
	} else {
		update_option( "as_time_tracking_combined_times", "yes" );
	}

}

add_action( 'init', 'as_time_tracking_phase_two_db_time_strucutre' );

/**
 * Loops through the tracked times and moves the individual and adjusted time entries inside
 * the 'as_time_tracking_entry' serialized data.
 *
 * @since   0.1.0
 * @return  void
 */
function as_time_tracking_change_tracked_time_structure() {
	global $wpdb;
	//Make array of all ticket_reply post_ids which are individual entries
	$individual_adjusted_time_post_ids = as_time_tracking_get_ticket_reply_ids( $wpdb );

	//Get all serialized tracked time entries
	$tracked_time_data = as_time_tracking_get_tracked_time_data( $wpdb );

	foreach( $individual_adjusted_time_post_ids as $post_id ) {
		//Find index of tracked time with the same ticket reply id
		$tracked_time_array_index = as_time_tracking_get_tracked_time_index( $post_id, $tracked_time_data );

		$individual_time = get_post_meta( $post_id, '_wpas_individual_reply_ticket_time', true );
		$adjusted_time = get_post_meta( $post_id, '_wpas_ttl_adjustments_to_time_spent_on_ticket', true );
		$tracked_time_entry = $tracked_time_data[$tracked_time_array_index]['serialized_data'];
		$tracked_time_id = $tracked_time_data[$tracked_time_array_index]['id'];

		//Combine individual and adjusted times to the serialized data
		$tracked_time_entry['individual_time'] = $individual_time;
		$tracked_time_entry['adjusted_time'] = $adjusted_time;
		$tracked_time_entry['is_ticket_reply_multiple'] = false;
		$tracked_time_entry['is_ticket_reply'] = true;
		$tracked_time_entry['is_ticket_level'] = false;

		//Update the database entries with new serialized data
		update_post_meta( $tracked_time_id, 'as_time_tracking_entry', $tracked_time_entry );

		//Remove old individual and adjusted time postmeta entries
		delete_post_meta( $post_id, '_wpas_individual_reply_ticket_time' );
		delete_post_meta( $post_id, '_wpas_ttl_adjustments_to_time_spent_on_ticket' );
	}

	return true;

}

/**
 * Returns the index of the tracked time data array which contains the same ticket reply id
 * as the post id being currently searched.
 *
 * @since   0.1.0
 * @param 	integer $post_id    		Post id of the ticket reply currently being searched
 * @param 	array $tracked_time_data   	Array of tracked time data
 * @return  void
 */
function as_time_tracking_get_tracked_time_index( $post_id, $tracked_time_data ) {
	foreach( $tracked_time_data as $key => $data ) {
		$ticket_reply = $data['serialized_data']['ticket_reply'];
		if( $ticket_reply == $post_id ) {
			return $key;
		}
	}
}

/**
 * Gets all post_ids of individual ticket replies which have adjusted or individual
 * time saved.
 *
 * @since   0.1.0
 * @param 	object $wpdb   The global WordPress database object
 * @return  void
 */
function as_time_tracking_get_ticket_reply_ids( $wpdb ) {
	$db_query = "SELECT DISTINCT " . $wpdb->prefix . "postmeta.post_id FROM " . $wpdb->prefix . "postmeta INNER JOIN " . $wpdb->prefix . "posts ON " . $wpdb->prefix . "postmeta.post_id = " . $wpdb->prefix . "posts.ID AND " . $wpdb->prefix . "posts.post_type = 'ticket_reply' AND (" . $wpdb->prefix . "postmeta.meta_key ='_wpas_individual_reply_ticket_time' OR " . $wpdb->prefix . "postmeta.meta_key = '_wpas_ttl_adjustments_to_time_spent_on_ticket')";
	$db_result = $wpdb->get_results( $db_query, OBJECT );

	$individual_adjusted_time_post_ids = array();

	foreach( $db_result as $result ) {
		$individual_adjusted_time_post_ids[] = $result->post_id;
	}

	return $individual_adjusted_time_post_ids;
}

/**
 * Gets the tracked time data and returns it in an array.
 * time saved.
 *
 * @since   0.1.0
 * @param 	object $wpdb   The global WordPress database object
 * @return  array
 */
function as_time_tracking_get_tracked_time_data( $wpdb ) {
	$db_query = "SELECT DISTINCT " . $wpdb->prefix . "postmeta.post_id, " . $wpdb->prefix . "postmeta.meta_value FROM " . $wpdb->prefix . "postmeta INNER JOIN " . $wpdb->prefix . "posts ON " . $wpdb->prefix . "postmeta.post_id = " . $wpdb->prefix . "posts.ID AND " . $wpdb->prefix . "posts.post_type = 'trackedtimes' AND " . $wpdb->prefix . "postmeta.meta_key = 'as_time_tracking_entry'";
	$db_result = $wpdb->get_results( $db_query, OBJECT );

	$tracked_time_data = array();

	foreach( $db_result as $result ) {
		$serialized_data = array(
				'id' => $result->post_id,
				'serialized_data' => maybe_unserialize( $result->meta_value )
			);
		$tracked_time_data[] = $serialized_data;
	}

	return $tracked_time_data;
}
