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

add_action( 'init', 'wpasss_rewrite_endpoint' );
add_action( 'init', 'wpasss_rewrite_thumbs_up_endpoint' );
add_action( 'init', 'wpasss_rewrite_thumbs_down_endpoint' );
add_action( 'init', 'wpasss_rewrite_click_to_close_thumbs_up_endpoint' );
add_action( 'init', 'wpasss_rewrite_click_to_close_thumbs_down_endpoint' );

/**
 * Register a new rewrite endpoint
 *
 * Creates the "satisfaction-survey" endpoint for tickets so that the link between ticket and survey is tighter and
 * reduces the risk of conflicts with other plugins.
 *
 * @since 0.1.2
 */
 function wpasss_rewrite_endpoint() {
	add_rewrite_endpoint( wpasss_get_rewrite_slug(), EP_PERMALINK | EP_PAGES );
}
function wpasss_rewrite_thumbs_up_endpoint() {
	add_rewrite_endpoint( wpasss_get_rewrite_thumbs_up_slug(), EP_PERMALINK | EP_PAGES );
}
function wpasss_rewrite_thumbs_down_endpoint() {
	add_rewrite_endpoint( wpasss_get_rewrite_thumbs_down_slug(), EP_PERMALINK | EP_PAGES );
}
function wpasss_rewrite_click_to_close_thumbs_up_endpoint() {
	if ( true === boolval( wpas_get_option( 'ss_enable_survey_links_in_agent_replies' , false ) ) ) {	
		add_rewrite_endpoint( wpasss_get_rewrite_click_to_close_thumbs_up_slug(), EP_PERMALINK | EP_PAGES );	
	}
}
function wpasss_rewrite_click_to_close_thumbs_down_endpoint() {
	if ( true === boolval( wpas_get_option( 'ss_enable_survey_links_in_agent_replies' , false ) ) ){	
		add_rewrite_endpoint( wpasss_get_rewrite_click_to_close_thumbs_down_slug(), EP_PERMALINK | EP_PAGES );
	}
}

add_filter( 'the_content', 'wpasss_replace_ticket_by_survey', 9 );
/**
 * Display the satisfaction survey
 *
 * If the satisfaction survey endpoint is being accessed, we replace the ticket content by the satisfaction survey.
 *
 * @since 0.1.2
 *
 * @param string $content Original content
 *
 * @return string
 */
function wpasss_replace_ticket_by_survey( $content ) {

	// Make sure this is a ticket
	if ( ! is_main_query() || ! is_singular( 'ticket' ) ) {
		return $content;
	}

	global $wp_query, $post;

	// If the satisfaction survey is being requested we replace the ticket content
	if ( isset( $wp_query->query_vars[ wpasss_get_rewrite_slug() ] ) ) {

		remove_filter( 'the_content', 'wpas_single_ticket' );

		// Make sure the ticket is closed
		if ( 'closed' !== wpas_get_ticket_status( $post->ID ) ) {
			$content = '<div class="wpas-alert wpas-alert-danger">' . esc_html__( 'You cannot review a ticket that is still open.', 'wpas-ss' ) . '</div>';
		} // Make sure that the survey is active for the ticket
		elseif ( true === wpasss_was_survey_submitted( $post->ID ) ) {
			
			//$content = '<div class="wpas-alert wpas-alert-success">' . esc_html__( 'Thank you for taking the time to let us know how we are doing.', 'wpas-ss' ) . '</div>';			
			// Show message...
			$default_msg = '<div class="wpas-alert wpas-alert-success">' . esc_html__( 'Your survey response has been recorded. Thank you for taking the time to let us know how we are doing.', 'wpas-ss' ) . '</div>';
			$msg_to_show = wpas_get_option( 'wpass_thank_you_main_survey_form', $default_msg );	
			$content = $msg_to_show;
			
		} // Finally, if everything is correct, we display the form
		else {

			$action  = esc_url( get_permalink( $post->ID ) . 'satisfaction-survey' );
			$content = '<form id="wpas_submit_survey" action="' . $action . '" method="post">';
			$content .= wpas_do_field( 'record_satisfaction_survey', '', false );
			$content .= '<input type="hidden" name="ticket_id" id="ticket_id" value="' . $post->ID . '" />';

			$content .= WPAS_Satisfaction_Survey::get_instance()->render_survey_field();

			$content .= '<div class="wpas_unsatisfied_reasons">';
			$content .= '<label for="wpas_unsatisfied_reasons">' . __( 'Please tell us the main reason you are unsatisfied:<br/>', 'wpas-ss' ) . '</label>';
			$content .= '<select id="wpas_unsatisfied_reasons" class="scale_unsatisified_reasons" name="wpas_unsatisfied_reasons" >';

			$reasons = wpas_get_option( 'ss_unsatisfied_reasons', '' );
			$reasons = explode( "\n", str_replace( "\r", "", $reasons ) );

			$content .= '<option value="" selected>' . __('Select the main reason ...', 'wpas-ss') . '</option>';

			foreach ( $reasons as $reason ) {
				$content .= '<option value="' . stripslashes( $reason ) . '">' . stripslashes( $reason ) . '</option>';
			}

			$content .= '</select>';
			$content .= '</div>';

			$content .= '<div class="survey_comment">';
			$content .= '<label for="wpasss_comment">' . __( "Comment (optional):<br/>", 'wpas-ss' ) . '</label>';
			$content .= '<textarea rows="7" name="wpasss_comment" id="wpasss_comment" ></textarea>';
			$content .= '</div>';
			$content .= '<br/><br/>';
			$content .= '<input type="submit" value="' . __( 'Submit', 'wpas-ss' ) . '" />';
			$content .= '</form>';

			$content .= '<div class="wpas-alert wpas-alert-info">' . __( 'This survey relates to the ticket with this topic: ', 'wpas-ss' );
			$content .= '<br/>' . get_the_title( $post->ID );
			$content .= '</div>';

		}

	}

	return $content;

}

add_filter( 'the_content', 'wpasss_replace_ticket_by_thumbs_up_survey', 9 );
/**
 * Process a thumbs-up 100% rating click
 *
  * @since 1.0.6
 *
 * @param string $content Original content
 *
 * @return string
 */
 function wpasss_replace_ticket_by_thumbs_up_survey( $content ) {

	// Make sure this is a ticket
	if ( ! is_main_query() || ! is_singular( 'ticket' ) ) {
		return $content;
	}

	global $wp_query, $post;

	// If the satisfaction survey is being requested we replace the ticket content
	if ( isset( $wp_query->query_vars[ wpasss_get_rewrite_thumbs_up_slug() ] ) ) {

		remove_filter( 'the_content', 'wpas_single_ticket' );

		// Make sure the ticket is closed
		if ( 'closed' !== wpas_get_ticket_status( $post->ID ) ) {
			
			$content = '<div class="wpas-alert wpas-alert-danger">' . esc_html__( 'You cannot review a ticket that is still open.', 'wpas-ss' ) . '</div>';
			
		} // Make sure that the survey is active for the ticket
		elseif ( true === wpasss_was_survey_submitted( $post->ID ) ) {
			
			// We shouldn't really get here because the survey was already submitted so just throw a nice message!
			$content = '<div class="wpas-alert wpas-alert-success">' . esc_html__( 'Your survey response has been recorded. Thank you for taking the time to let us know how we are doing.', 'wpas-ss' ) . '</div>';
			
		} // Finally, if everything is correct, we record the data...
		else {
			
			wpasss_trigger_survey_thumbs_up_record( $post->ID );
			
			// Show message...
			$default_msg = '<div class="wpas-alert wpas-alert-success">' . esc_html__( 'Thank you for taking the time to let us know we are doing a good job!', 'wpas-ss' ) . '</div>';
			$msg_to_show = wpas_get_option( 'wpass_thank_you_thumbs_up', $default_msg );
			
			$content = $msg_to_show ;
			
		}
	}
	
	return $content;
	
 }
 
 add_filter( 'the_content', 'wpasss_replace_ticket_by_thumbs_down_survey', 9 );
/**
 * Process a thumbs-down 0% rating click
 *
  * @since 1.0.6
 *
 * @param string $content Original content
 *
 * @return string
 */
 function wpasss_replace_ticket_by_thumbs_down_survey( $content ) {

	// Make sure this is a ticket
	if ( ! is_main_query() || ! is_singular( 'ticket' ) ) {
		return $content;
	}

	global $wp_query, $post;

	// If the satisfaction survey is being requested we replace the ticket content
	if ( isset( $wp_query->query_vars[ wpasss_get_rewrite_thumbs_down_slug() ] ) ) {

		remove_filter( 'the_content', 'wpas_single_ticket' );

		// Make sure the ticket is closed
		if ( 'closed' !== wpas_get_ticket_status( $post->ID ) ) {
			
			$content = '<div class="wpas-alert wpas-alert-danger">' . esc_html__( 'You cannot review a ticket that is still open.', 'wpas-ss' ) . '</div>';
			
		} // Make sure that the survey is active for the ticket
		elseif ( true === wpasss_was_survey_submitted( $post->ID ) ) {
			
			// We shouldn't really get here because the survey was already submitted so just throw a nice message!
			$content = '<div class="wpas-alert wpas-alert-success">' . esc_html__( 'Your survey response has been recorded. Thank you for taking the time to let us know how we are doing.', 'wpas-ss' ) . '</div>';
			
		} // Finally, if everything is correct, we record the data...
		else {
			
			wpasss_trigger_survey_thumbs_down_record( $post->ID );
			
			// Show message...
			$default_msg = '<div class="wpas-alert wpas-alert-success">' . esc_html__( 'Thank you for taking the time to offer your feedback.  We really appreciate it!', 'wpas-ss' ) . '</div>';
			$msg_to_show = wpas_get_option( 'wpass_thank_you_thumbs_down', $default_msg );
			
			$content = $msg_to_show ;
			
		}
	}
	
	return $content;
	
 }
 
 add_filter( 'the_content', 'wpasss_replace_ticket_by_click_to_close_thumbs_up_survey', 9 );
/**
 * Close the ticket and process a thumbs-up 100% rating click 
 *
 * @since 1.0.7
 *
 * @param string $content Original content
 *
 * @return string
 */
 function wpasss_replace_ticket_by_click_to_close_thumbs_up_survey( $content ) {

	// Make sure this is a ticket
	if ( ! is_main_query() || ! is_singular( 'ticket' ) ) {
		return $content;
	}

	global $wp_query, $post;

	// If the close and assign rating survey is being requested we replace the screen content
	if ( isset( $wp_query->query_vars[ wpasss_get_rewrite_click_to_close_thumbs_up_slug() ] )  ||  isset( $wp_query->query_vars[ wpasss_get_rewrite_click_to_close_thumbs_down_slug() ] ) ) {

		remove_filter( 'the_content', 'wpas_single_ticket' );
		
		// Make sure we have a ticket id!
		$ticket_id = wpasss_convert_post_id_to_ticket_id( $post->ID ) ;
		
		// Make sure the ticket id is valid!
		if ( false === $ticket_id || $ticket_id < 0 || empty( $ticket_id ) ) {
			
			$content = '<div class="wpas-alert wpas-alert-danger">' . esc_html__( 'This is not a valid ticket id!', 'wpas-ss' ) . '</div>';
		
		} // Make sure the ticket is NOT closed
		elseif ( 'closed' === wpas_get_ticket_status( $post->ID ) ) {
			
			$content = '<div class="wpas-alert wpas-alert-danger">' . esc_html__( 'This ticket was already closed - you cannot close it or assign a rating to it now.', 'wpas-ss' ) . '</div>';
			
		} // ok to continue since the ticket is open
		else {
			
			// Get some data from the ticket
			$ticket_id 	= $post->ID; // set variable $ticket_id because its clearer than the generic $post->ID.
			$client		= get_userdata( $post->post_author );
			$client_id 	= $client->ID;
			
			
			// Make sure that there is a hash value provided in the URL
			$the_hash = filter_input(INPUT_GET, 'thash', FILTER_SANITIZE_STRING);
			if ( ! isset( $the_hash ) || empty( $the_hash ) ) {
				// hash is empty so set content and move on...
				$content = __('Security Error: Required hash does not exist - ticket cannot be closed or rated! Please login to your account and close the ticket there.', 'wpas-ss') ;
				return $content;
			}
			
			// Make sure that the hashes compare properly!
			if ( $the_hash <> wpasss_get_hash_from_ticket($ticket_id) ) {
				// hash does not compare so set content and move on...
				$content = __('Security Error: Incorrect security hash was passed - ticket cannot be closed or rated! Please login to your account and close the ticket there.', 'wpas_productivity') ;
				return $content;			
			}			
			
			// Remove the close filter that SS adds normally 
			remove_action( 'wpas_after_close_ticket', array( WPAS_Satisfaction_Survey::get_instance(),'ticket_closed_schedule_invite' ), 10 ) ;
			
			// Close the ticket...
			if ( true === wpasss_close_ticket( $post ) ) {
							
				// Record the survey and show a message. 
				// The survey results and message will vary though depending on whether we're recording a 100% rating or a zero rating...
				if ( isset( $wp_query->query_vars[ wpasss_get_rewrite_click_to_close_thumbs_up_slug() ] ) ) {
					// 100% rating
					
					// Record the survey...
					wpasss_trigger_survey_thumbs_up_record( $post->ID );
					
					// Show the message
					$default_msg = '<div class="wpas-alert wpas-alert-success">' . esc_html__( 'Thank you closing your ticket and for taking the time to let us know we are doing a good job!', 'wpas-ss' ) . '</div>';
					$msg_to_show = wpas_get_option( 'wpass_thank_you_close_ticket_with_thumbs_up', $default_msg );
				}
				
				if ( isset( $wp_query->query_vars[ wpasss_get_rewrite_click_to_close_thumbs_down_slug() ] ) ) {
					// 0% rating message
					
					// Record the survey...
					wpasss_trigger_survey_thumbs_down_record( $post->ID );
					
					// Show the message
					$default_msg = '<div class="wpas-alert wpas-alert-success">' . esc_html__( 'Thank you closing your ticket and for taking the time to offer your feedback.  We really appreciate it!', 'wpas-ss' ) . '</div>';
					$msg_to_show = wpas_get_option( 'wpass_thank_you_close_ticket_with_thumbs_down', $default_msg );
				}				
				
				$content = $msg_to_show ;
				
			} else {
				
				$default_msg = '<div class="wpas-alert wpas-alert wpas-alert-danger">' . esc_html__( 'An unexpected error was encountered while closing this ticket!!', 'wpas-ss' ) . '</div>';
				
				$content = $msg_to_show ;
			}
		}
	}
	
	return $content;
	
 }
 
 /**
 * Function to close ticket
 *
 * @param $ticket post|ticket
 *
 * @since 1.0.7
 *
 * @return boolean
 */
 function wpasss_close_ticket( $ticket ) {
	 
	// Get some data from the ticket
	$ticket_id 	= $ticket->ID; // set variable $ticket_id because its clearer than the generic $post->ID.
	$client		= get_userdata( $ticket->post_author );
	$client_id 	= $client->ID;
	
	// Define return variable
	$ticket_close_status = false ;
	
	// At this point we have to log the user in silently...
	$user_was_already_logged_in = false ;
	if ( $client_id === get_current_user_id() ) {
		$user_was_already_logged_in = true ;
	}
	
	if ( ! version_compare( WPAS_VERSION, '4.0.4', '>=' ) ) { 
		// need to login user if AS version is before 4.0.4
		if( ! $user_was_already_logged_in ) {
			// These are likely to throw a warning in the debug.log file:  Cannot modify header information - headers already sent by...
			wp_clear_auth_cookie();  
			wp_set_current_user( $client_id );
			wp_set_auth_cookie( $client_id );
			update_user_caches( $client );
		}			
	}			

	// Close ticket here
	if ( ! version_compare( WPAS_VERSION, '4.0.4', '>=' ) ) { 
		// pre AS 4.0.4 did not have the third parameter to the close function.
		$ticket_close_status = wpas_close_ticket( $ticket_id, $client_id ) ;
	}
	else {
		$ticket_close_status = wpas_close_ticket( $ticket_id, $client_id, true )  ;
	}

	// Log user out
	if ( ! version_compare( WPAS_VERSION, '4.0.4', '>=' ) ) { 
		// need to logout user if AS version is before 4.0.4
		if ( ! $user_was_already_logged_in ) {
			if ( ! boolval( wpas_get_option( 'pf_keep_user_logged_in_post_close' ) ) ) {
				wp_logout();
			}
		}
	}				
	 
	// Return status of close operation
	return $ticket_close_status;
	 
 }
 
 /**
 * Takes an id and finds its parent ticket and returns ticket_id or false.
 *
 * @TODO
 * This function should probably be in core.
 *
 * @param $post_id string|int
 *
 * @since 1.0.7
 *
 * @return string|int|boolean
 */
function wpasss_convert_post_id_to_ticket_id( $post_id ) {

	// If not a valid id then just return false.
	if ( empty( $post_id ) || ! is_numeric( $post_id) ) {
		
		return false ;
	}

	if ( ! empty( $post_id) ) {
		
		$the_post = get_post( $post_id );

		switch ( get_post_type( $the_post ) ) {
			case 'ticket':
				return $post_id ;
				break ;
				
			case 'ticket_reply':
				return $the_post->post_parent;
				break ;
		}
		
	}
	
	return false ;
} 

/**
 * Gets the unique hash value for this ticket and url rewrite endpoint to the ticket.
 *
 * @TODO
 * Note: this is a DUPLICATE of a similarly named function in the class-satisfaction-survey file.  But because of the
 * the way this add-on is structured we need to duplicate this out here - gah!
 *
 * @param int $ticket_id
 *
 * @return string
 *
 */	
 function wpasss_get_hash_from_ticket( $ticket_id ) {
	 
	 $thash = get_post_meta( $ticket_id, '_wpasss_hash', true );
	 return $thash ;
 }

/**
 * Get the satisfaction survey slug
 *
 * @since 0.1.2
 * @return string
 */
function wpasss_get_rewrite_slug() {
	return apply_filters( 'wpasss_survey_endpoint_slug', wpas_get_option( 'satisfaction_survey_slug', 'satisfaction-survey' ) );
}
function wpasss_get_rewrite_thumbs_up_slug() {
	return apply_filters( 'ss_slug_quick_link_thumbs_up', wpas_get_option( 'satisfaction_survey_slug', 'satisfaction-survey-thumbs-up' ) );
}
function wpasss_get_rewrite_thumbs_down_slug() {
	return apply_filters( 'ss_slug_quick_link_thumbs_down', wpas_get_option( 'satisfaction_survey_slug', 'satisfaction-survey-thumbs-down' ) );
}
function wpasss_get_rewrite_click_to_close_thumbs_up_slug() {
	return apply_filters( 'ss_slug_quick_link_click_to_close_thumbs_up', wpas_get_option( 'ss_slug_quick_link_click_to_close_thumbs_up', 'satisfaction-survey-click-to-close-thumbs-up' ) );
}
function wpasss_get_rewrite_click_to_close_thumbs_down_slug() {
	return apply_filters( 'ss_slug_quick_link_click_to_close_thumbs_down', wpas_get_option( 'ss_slug_quick_link_click_to_close_thumbs_down', 'satisfaction-survey-click-to-close-thumbs-down' ) );
}