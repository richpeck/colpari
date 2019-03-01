<?php
/**
 * @package   Awesome Support: Satisfaction Survey
 * @author    Julien Liabeuf <julien@liabeuf.fr>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016 Awesome Support
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Save a survey
 *
 * @since 0.1.2
 *
 * @param array $data      Survey data submitted by the user
 * @param int   $ticket_id ID of the ticket being surveyed
 *
 * @return void
 */
function wpasss_save_survey( $data = array(), $ticket_id ) {

	// Set the default values
	$defaults = array(
		'comment' => '',
		'rating'  => 0,
		'reason'  => '',
	);

	$data = array_merge( $defaults, $data );

	update_post_meta( $ticket_id, '_wpasss_comment', wp_kses_post( $data['comment'] ) );
	update_post_meta( $ticket_id, '_wpasss_reason', $data['reason'] );
	update_post_meta( $ticket_id, '_wpasss_rating', $data['rating'] );

	// Stop survey from being submitted again
	delete_post_meta( $ticket_id, '_wpasss_hash' ) ;
	
	/**
	 *  Remove existing cron event for this post if one exists
	 *  Note that this is duplicate code that also exists in the class-satsifaction-survey.php class file.
	 *  Not sure how to call the cancel_event_reminder_email() function that's in there instead of 
	 *  duplicating the code here.  
	 */
	delete_post_meta( $ticket_id, '_wpasss_email_invite' );
	wp_clear_scheduled_hook( 'wpas_ss_send_reminder_email', array( (int) $ticket_id ) );	

}

add_action( 'wpas_do_record_satisfaction_survey', 'wpasss_trigger_survey_record' );
/**
 * Save the user's satisfaction input
 *
 * @since 0.1.2
 *
 * @param array $data POST data
 *
 * @return false|null
 */
function wpasss_trigger_survey_record( $data ) {

	if ( ! isset( $data['ticket_id'] ) ) {
		return false;
	}

	$input = array(
		'comment' => $data['wpasss_comment'],
		'rating'  => $data['wpasss_rating'],
		'reason'  => $data['wpas_unsatisfied_reasons'],
	);

	wpasss_save_survey( $input, (int) $data['ticket_id'] );

	wpas_add_notification( 'survey_submitted', esc_html__( 'Thank you for taking the time to let us know how we are doing.', 'wpas-ss' ) );
	wp_redirect( add_query_arg( 'submission', 'success', wpasss_get_survey_link( $data['ticket_id'] ) ) );
	exit;

}

/**
 * Record a 100% satisfaction Rating
 *
 * @since 1.0.6
 *
 * @param array $ticket_id Ticket ID 
 *
 * @return false|null
 */
function wpasss_trigger_survey_thumbs_up_record( $ticket_id ) {

	if ( ! isset( $ticket_id ) ) {
		return false;
	}

	$input = array(
		'comment' => '',
		'rating'  => 100,
		'reason'  =>'',
	);

	// Save the survey
	wpasss_save_survey( $input, (int) $ticket_id );

}

/**
 * Record a 0% satisfaction Rating
 *
 * @since 1.0.6
 *
 * @param array $ticket_id Ticket ID 
 *
 * @return false|null
 */
function wpasss_trigger_survey_thumbs_down_record( $ticket_id ) {

	if ( ! isset( $ticket_id ) ) {
		return false;
	}

	$input = array(
		'comment' => '',
		'rating'  => 0,
		'reason'  =>'',
	);

	// Save the survey
	wpasss_save_survey( $input, (int) $ticket_id );

}

/**
 * Check if the survey has already been submitted or not
 *
 * @since 0.1.2
 *
 * @param int $ticket_id Ticket ID
 *
 * @return bool
 */
function wpasss_was_survey_submitted( $ticket_id ) {
    
	return ( '' === get_post_meta( $ticket_id, '_wpasss_hash', true ) && '' !== get_post_meta( $ticket_id, '_wpasss_rating', true ) ) ? true : false;
}

/**
 * Delete a survey submission
 *
 * @since 0.1.2
 *
 * @param int $ticket_id ID of the ticket for which we want to delete the survey
 *
 * @return void
 */
function wpasss_remove_survey( $ticket_id ) {

	delete_post_meta( $ticket_id, '_wpasss_email_invite' );
	delete_post_meta( $ticket_id, '_wpasss_reason' );
	delete_post_meta( $ticket_id, '_wpasss_comment' );
	delete_post_meta( $ticket_id, '_wpasss_rating' );
	delete_post_meta( $ticket_id, '_wpasss_hash' );

}

/**
 * Get the survey URL for a specific ticket
 *
 * @since 0.1.2
 *
 * @param int    $ticket_id   ID of the ticket to survey
 * @param string $querystring Additional parameters appended to URL ( ie: '?thash=..." )
 *
 * @return string
 */
function wpasss_get_survey_link( $ticket_id, $querystring = '' ) {
	return esc_url( get_permalink( $ticket_id ) . wpasss_get_rewrite_slug() . $querystring );
}

/**
 * Get the survey URL for a 100% quick link rating for a specific ticket
 *
 * @since 1.0.6
 *
 * @param int    $ticket_id   ID of the ticket to survey
 * @param string $querystring Additional parameters appended to URL ( ie: '?thash=..." )
 *
 * @return string
 */
function wpasss_get_survey_thumbs_up_link( $ticket_id, $querystring = '' ) {
	return esc_url( get_permalink( $ticket_id ) . wpasss_get_rewrite_thumbs_up_slug() . $querystring );
}

/**
 * Get the survey URL for a 0% quick link rating for a specific ticket
 *
 * @since 1.0.6
 *
 * @param int    $ticket_id   ID of the ticket to survey
 * @param string $querystring Additional parameters appended to URL ( ie: '?thash=..." )
 *
 * @return string
 */
function wpasss_get_survey_thumbs_down_link( $ticket_id, $querystring = '' ) {
	return esc_url( get_permalink( $ticket_id ) . wpasss_get_rewrite_thumbs_down_slug() . $querystring );
}

/**
 * Get the survey URL for the quick close and 100% rating link for a specific ticket
 *
 * @since 1.0.6
 *
 * @param int    $ticket_id   ID of the ticket to survey
 * @param string $querystring Additional parameters appended to URL ( ie: '?thash=..." )
 *
 * @return string
 */
function wpasss_get_survey_click_to_close_with_thumbs_up_link( $ticket_id, $querystring = '' ) {
	return esc_url( get_permalink( $ticket_id ) . wpasss_get_rewrite_click_to_close_thumbs_up_slug() . $querystring );
}

/**
 * Get the survey URL for the quick close and 0% rating link for a specific ticket
 *
 * @since 1.0.6
 *
 * @param int    $ticket_id   ID of the ticket to survey
 * @param string $querystring Additional parameters appended to URL ( ie: '?thash=..." )
 *
 * @return string
 */
function wpasss_get_survey_click_to_close_with_thumbs_down_link( $ticket_id, $querystring = '' ) {
	return esc_url( get_permalink( $ticket_id ) . wpasss_get_rewrite_click_to_close_thumbs_down_slug() . $querystring );
}



/**
 * Return a random hash.  Note that we should move this function along 
 * with its counterpart in productivity to core so we dont' keep duplicating this code.
 *
 * @since 1.0.6
 *
 *
 * @return string
 */
function wpasss_random_hash() {
	
	if ( function_exists( 'wpas_random_hash' ) ) {
		
		return wpas_random_hash();
		
	} else {
	
		$time  = time();
		$the_hash = md5( $time . (string) random_int(0, getrandmax()) );
		
		return $the_hash;
		
	}
}

