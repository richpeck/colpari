<?php
/**
 * Connect to the mail server and check for new e-mails.
 *
 * @since  0.1.0
 * @return string|WP_Error Constructed string of messages and number of emails imported, error message otherwise
 */
function wpas_check_mails() {

	// Get all mailbox configs from our mailbox config custom post type
	$mailbox_configs = ases_get_mailbox_configs();

	$allcount = 0; // holds the count for the total number of emails retrieved across all mailboxes
	
	// Get mailbox object using the defaults from TICKETS->SETTINGS->EMAIL PIPING tab
	$mailbox  = new ASES_Mailbox(null,null,null,null,null,null,null);

	// Retrieve and process emails in that mailbox
	if ( true == is_wp_error( $mailbox->mailbox ) ) {
		
		// Let user know there was an error
		$display_message = '<div class="as-esn-error-text">' . __( 'DEFAULT MAILBOX RESULTS: ERROR', 'as-email-support' ) . "\r\n" . '</div>' ;
		$display_message = $display_message . ( $mailbox->mailbox->get_error_message() ) . "\r\n" ;
		$display_message = $display_message . '<hr />';		
		
	} else {

		// We are good to go - Read and process emails in the mail box
		$all_counts = wpas_read_and_import_emails_from_mailbox( $mailbox, null );
		
		// Get count of emails successfully processed from return array
		$count = $all_counts[0][1] ;
	
		// Update the master count...
		$allcount = $allcount + (int) $count ;

		// Update message to be displayed to user
		$display_message = __( 'DEFAULT MAILBOX RESULTS:', 'as-email-support' ) . "\r\n" ;	

		foreach( $all_counts as $message ) {		
			$display_message = $display_message . ' ' . $message[0] . ' ' . (string) $message[1] . "\r\n" ;		
		}
		
		// Add a horizontal separator to the display message
		$display_message = $display_message . '<hr />';
		
	}
	
	
	// Now we have to repeat the same code above here so we can process all the secondary mailboxes defined in the mailbox CPT.
	// Yes, we hate duplicate code.
	// No, there's no alternative that is clean if we want to maintain backwards compatibility with 3.3.4.
	foreach ( $mailbox_configs as $mb_config ) {

		// read the data from the cpt
		$server 	= get_post_meta( $mb_config->ID, 'wpas_multimailbox_email_server', true);
		$protocol 	= get_post_meta( $mb_config->ID, 'wpas_multimailbox_protocol', true);
		$username 	= get_post_meta( $mb_config->ID, 'wpas_multimailbox_username', true);
		$password 	= get_post_meta( $mb_config->ID, 'wpas_multimailbox_password', true);
		$port 		= get_post_meta( $mb_config->ID, 'wpas_multimailbox_port', true);
		$secure		= get_post_meta( $mb_config->ID, 'wpas_multimailbox_secureportflag', true);
		$timout 	= get_post_meta( $mb_config->ID, 'wpas_multimailbox_timeout', true);
		$activeflag	= get_post_meta( $mb_config->ID, 'wpas_multimailbox_active', true);

		$ticket_defaults = array();
		$ticket_defaults['mailboxtitle']	= $mb_config->post_title;
		$ticket_defaults['defaultassignee']	= get_post_meta( $mb_config->ID, 'wpas_multimailbox_defaultassignee', true);
		$ticket_defaults['defaultdept'] 	= get_post_meta( $mb_config->ID, 'wpas_multimailbox_defaultdept', true);
		$ticket_defaults['defaultproduct']	= get_post_meta( $mb_config->ID, 'wpas_multimailbox_defaultproduct', true);
		$ticket_defaults['defaultpriority']	= get_post_meta( $mb_config->ID, 'wpas_multimailbox_defaultpriority', true);
		$ticket_defaults['defaultchannel']	= get_post_meta( $mb_config->ID, 'wpas_multimailbox_defaultchannel', true);
		$ticket_defaults['defaultstatus']	= get_post_meta( $mb_config->ID, 'wpas_multimailbox_defaultstatus', true);
		$ticket_defaults['defaultpublicflag']	= get_post_meta( $mb_config->ID, 'wpas_multimailbox_defaultpublicflag', true);
		$ticket_defaults['defaultaddlpartyemail1']	= get_post_meta( $mb_config->ID, 'wpas_multimailbox_defaultaddlpartyemail1', true);
		$ticket_defaults['defaultaddlpartyemail2']	= get_post_meta( $mb_config->ID, 'wpas_multimailbox_defaultaddlpartyemail2', true);
		$ticket_defaults['defaultsecondaryassignee']	= get_post_meta( $mb_config->ID, 'wpas_multimailbox_defaultsecondaryassignee', true);
		$ticket_defaults['defaulttertiaryassignee']	= get_post_meta( $mb_config->ID, 'wpas_multimailbox_defaulttertiaryassignee', true);

		$other 		= array();

		if ( 1 === (int) $activeflag ) {

			// Instantiate the mailbox...
			$mailbox  = new ASES_Mailbox($server, $protocol, $username, $password, $port, $secure, $other);		

			if ( true == is_wp_error( $mailbox->mailbox ) ) {
		
				// Let user know there was an error
				$display_message = $display_message . '<div class="as-esn-error-text">' . __( 'MAILBOX ERROR: ', 'as-email-support' ) . $mb_config->post_title . "\r\n" . '</div>';
				$display_message = $display_message . ( $mailbox->mailbox->get_error_message() ) . "\r\n" ;
				$display_message = $display_message . '<hr />';				
		
			} else {

				// Keep track of the count of emails we've received
				$count    = 0; // Count the number of emails we have imported for the current mailbox.
			
				// Retrieve and process emails in that mailbox
				$all_counts = wpas_read_and_import_emails_from_mailbox( $mailbox, $ticket_defaults );
				
				// Get count of emails successfully processed from return array
				$count = $all_counts[0][1] ;
			
				// Update master count...
				$allcount = $allcount + (int) $count ;
				
				// Update message to be displayed to user
				$display_message = $display_message . __( 'MAILBOX: ', 'as-email-support' ) . $mb_config->post_title . "\r\n" ;
				foreach( $all_counts as $message ) {		
					$display_message = $display_message . ' ' . $message[0] . ' ' . (string) $message[1] . "\r\n" ;		
				}
				
				// Add a horizontal separator to the display message
				$display_message = $display_message . '<hr />';
			}
		}
		
	}  // end for each mailbox loop
	
	// Add a final summary to the display message	
	$display_message = $display_message . sprintf( __( 'Successfully imported %s e-mails from all mailboxes.', 'as-email-support' ), (string) $allcount);

	// Write out the display string / status to a log file
	wpas_write_log('email-piping', $display_message);

	//return the message to be displayed to the user
	return $display_message;

}

/**
 * Read and process all emails from the passed mailbox object/array.
 * 
 * Primarily called by wpas_check_mails()
 *
 * @since  5.1.0
 *
 * @param array $mailbox mailbox object array
 * @param array $ticket_defaults defaults for ticket such as priority, dept etc.  Not used directly but passed to the WPAS_Save_Email class to be used there...
 *
 * @return array Number of mails of each type processed
 */
  function wpas_read_and_import_emails_from_mailbox( $mailbox = array(), $ticket_defaults = array() ){
	  
	$count	= 0; // Count the number of emails we have imported for the current mailbox. We also set a marker $i to limit the number of emails imported at once.	
	$count_replies_added = 0 ;
	$count_rejected_replies = 0 ;
	$count_skipped_emails = 0 ;
	$count_tickets_closed = 0 ;
	$count_rules_failed = 0 ;
	$count_duplicates = 0 ;
	
	$limit	= apply_filters( 'ases_check_mails_loop_limit', 25 );

	// We are processing emails in a reverse order to avoid the issue with deleting tickets while the UID has been expunged.
	// @see https://framework.zend.com/issues/browse/ZF-5655
	for ( $i = $total_emails = $mailbox->get_emails_count(); $i; -- $i ) {

		// Limit the number of emails we retrieve to avoid runtime errors.
		// This is just in case we have customers who receive hundreds of tickets in an email batch (what the heck kind of support operation would that be???).
		if ( $total_emails - $i >= $limit ) {
			break;
		}

		// Now we get the email object.
		$email = $mailbox->get_mailbox()->getMessage( $i );

		// Let's roll! We look into this particular email and, if everything's fine and we have all the contents, then we save it.
		$fetch = new WPAS_Save_Email( $email, $ticket_defaults );

		if ( true === $fetch->is_reply_added() ) {

			// Delete the message
			$mailbox->delete_message( $i );

			// Good job! The email was correctly imported so update counters
			$count ++; 
			
			// Update the replies_added specific counter...
			$count_replies_added++;
			
			// Now, in our twisted spaghettti based logic, the user might have decided to set rules to 
			// close the ticket after adding the reply. So handle that here...
			if ( 'update_and_close' === $fetch->email_rules_status() ) {
			
				// close the ticket
				wpas_close_ticket( $fetch->get_final_ticket_id() );
				
				// Update counter - this will cause the detailed counters to show more counts than email received since we're doing two actions!
				$count_tickets_closed++ ;				
			}
			
		} elseif ( true === $fetch->is_duplicate() ) {
		
			// Delete the message
			$mailbox->delete_message( $i );
			
			// Update counter
			$count_duplicates++;
		
		} elseif ( true === $fetch->is_reply_rejected() ) {
		
			// Delete the message
			$mailbox->delete_message( $i );
			
			// Update counter
			$count_rejected_replies++;
		
		} elseif ( 'skip' === $fetch->email_rules_status() ) {
		
			// nothing was added because the email rules says to "skip" the message...
			// So literally do nothing here other than update a counter...
			$count_skipped_emails++ ;

		} elseif ( 'close' === $fetch->email_rules_status() ) {

			// close the ticket
			wpas_close_ticket( $fetch->get_final_ticket_id() );
			
			// delete the message after close			
			$mailbox->delete_message( $i );
			
			// Update counter
			$count_tickets_closed++ ;
			
					
		} elseif ( 'update_and_close' === $fetch->email_rules_status() ) {
			
			// We shouldn't really get here but if we do, well, then, just clean up - close the ticket and update the counters.

			// close the ticket
			wpas_close_ticket( $fetch->get_final_ticket_id() );
			
			// delete the message after close
			$mailbox->delete_message( $i );
			
			// Update counter
			$count_tickets_closed++ ;
			
					
		} elseif ( 'update' === $fetch->email_rules_status() ) {
			
			// We really should not get here but just in case lets do some clean up...
			
			// delete the message 
			$mailbox->delete_message( $i );
						
			// Ticket was updated so update counter...			
			$count_replies_added++;

		} elseif ( 'no_action' <> $fetch->email_rules_status() ) {
		
			// nothing was added because the email rules check failed so delete_message
			// Note: We could have updated the is_reply_rejected() function in the wpas-save-email class to check for rules status as well
			// but figured it's better to break it out explicitly so we know why we're deleting the message.  Especially if we want to
			// to do a little logging here later on.
			$mailbox->delete_message( $i );
			
			// Update counter...
			$count_rules_failed++ ;
		}
	}
	

	// Construct an array with the counters - this is what we will return to the calling function
	$all_counts = array (
		array( __( 'Successful Emails Processed and Imported:', 'as-email-support' ), $count),
		array( __( 'Replies Added:', 'as-email-support' ), $count_replies_added),
		array( __( 'Replies Rejected (These have been deleted):', 'as-email-support' ), $count_rejected_replies),
		array( __( 'Skipped Emails (These remain in your mailbox):', 'as-email-support' ), $count_skipped_emails),
		array( __( 'Tickets Closed:', 'as-email-support' ), $count_tickets_closed),
		array( __( 'Emails With Rules Failed (These have been deleted):', 'as-email-support' ), $count_rules_failed),
		array( __( 'Emails that are duplicates (These have been deleted):', 'as-email-support' ), $count_duplicates)
	);
	
	return $all_counts ;
 }


/**
 * Process e-mails.
 *
 * This function is used for Ajax processes. It will process
 * all possible e-mails and echo the result.
 *
 * @since  0.1.0
 * @return string Number of mails processed or error message on failure
 */
function wpas_check_mail_ajax() {
	
	$mailbox = wpas_check_mails();
	$output  = array( 'status' => '', 'content' => '' );

	if ( is_wp_error( $mailbox ) ) {
		$output['status']  = 'error';
		$output['content'] = $mailbox->get_error_message();

	} else {

		$output['status']  = 'success';

		$output['content'] = str_replace("\n",'<br />',$mailbox) ;
		
		/*
		if ( 0 === $mailbox ) {
			$output['content'] = sprintf( __( 'No new e-mails', 'as-email-support' ), $mailbox );
		} else {
			$output['content'] = sprintf( __( 'Successfully imported %s e-mails', 'as-email-support' ), $mailbox );
		}
		*/

	}

	/* Reschedule the task for later */
	ases_cron_reschedule_task();

	echo json_encode( $output );	
	
	die();
}

/**
 * Try to establish a connection to the mailbox
 *
 * @since 0.1.4
 * @return array
 */
function ases_mailbox_test_connect() {

	$mailbox = new ASES_Mailbox(null,null,null,null,null,null,null);
	$result  = array( 'result' => 1, 'message' => __( 'Connection successful', 'as-email-support' ) );

	if ( is_wp_error( $mailbox->get_mailbox() ) ) {
		$result['result']  = 0;
		$result['message'] = $mailbox->get_mailbox()->get_error_message();
	}

	return $result;

}

add_action( 'admin_bar_menu', 'ases_manual_fetch_admin_bar', 999 );
/**
 * Add a manual e-mail fetch button to the admin bar
 *
 * @since 0.2.0
 * @return void
 */
function ases_manual_fetch_admin_bar() {

	if ( ! is_admin() ) {
		return;
	}

	if ( ! current_user_can( 'administrator' ) ) {
		return;
	}

	/**
	 * @var WP_Admin_Bar $wp_admin_bar
	 */
	global $wp_admin_bar;

	$args = array(
		'id'     => 'ases_mail_fetch',
		'parent' => null,
		'group'  => null,
		'title'  => sprintf( '<span class="ab-icon"></span> %s', __( 'Fetch Mails', 'as-email-support' ) ),
		'href'   => '#',
		'meta'   => array(
			'target' => '_self',
			'class'  => 'wpas-ab-email-fetch',
		),
	);

	$wp_admin_bar->add_menu( $args );

}

add_action( 'wpas_mb_replies_under_avatar', 'ases_email_reply_identifier', 10, 2 );
/**
 * If the reply was sent by e-mail we add an e-mail icon and display the e-mail headers
 *
 * @since 0.2.0
 *
 * @param int $reply_id Post ID of the reply.
 * @param int $user_id  ID of the reply author.
 *
 * @return void
 */
function ases_email_reply_identifier( $reply_id, $user_id ) {

	// Check the reply source.
	$source = get_post_meta( $reply_id, '_wpas_source', true );

	// If the source is not email, bail.
	if ( 'email' !== $source ) {
		return;
	}

	// Now retrieve the email headers.
	$headers = ases_read_email_headers( get_post_meta( $reply_id, '_wpas_email_headers', true ) );

	// Here too, if there is no headers, we bail.
	if ( false === $headers ) {
		return;
	}

	// Show the icon under the user avatar.
	printf( '<span class="wpas-reply-email-icon"><a href="%s" class="thickbox">&#9993;</a></span>', '#TB_inline?width=800&height=600&inlineId=wpas-email-reply-headers-' . $reply_id );

	// Load thickbox for displaying the headers in a modal.
	add_thickbox();

	// Finally output the actual headers.
	// Because we're outputting an object, we need to switch to HTML mode and use print_r().
	?>
	<div id="wpas-email-reply-headers-<?php echo $reply_id; ?>" style="display:none;">
		<textarea name="wpas_header_raw_dump" id="wpas_header_raw_dump" style="width:100%; height:100%;"><?php print_r( $headers ); ?></textarea>
	</div>

<?php }

function ases_stringify_array_recursive( $thing, $glue = ', ' ) {

	if ( is_array( $thing ) ) {

		$out = array();

		foreach ( $thing as $key => $value ) {

			if ( is_array( $value ) ) {
				$value = ases_stringify_array_recursive( $value );
			}

			array_push( $out, "<strong>$key:</strong> $value" );

		}

		$thing = implode( $glue, $out );
	}

	return $thing;

}

/**
 * Get the e-mail body delimiter.
 *
 * The delimiter is used to identify the actual reply text within incoming e-mail ticket replies.
 *
 * This delimiter has to be the exact same used in the e-mail body and during the processing of incoming e-mails.
 *
 * @since 0.2.7
 *
 * @param bool $style Whether to add styling to the delimiter (for displaying in the e-mail) or not
 *
 * @return string
 */
function ases_get_message_delimiter( $style = false ) {

	$text      = esc_attr_x( 'Please type your reply above this line', 'Delimiter text used in the e-mail body', 'as-email-support' );
	$delimiter = "##- $text -##";

	if ( true === $style ) {
		$delimiter = "<div style='color:#b5b5b5'>$delimiter</div>";
	}

	return $delimiter;

}

/**
 * Read email headers.
 *
 * This helper function takes an email header (email headers are saved during import) and decides which class to use
 * for reading its contents.
 *
 * This function is helpful for ensuring backward compatibility with email headers saved when processed by the Flourish
 * library (now discarded).
 *
 * @since 0.5.0
 *
 * @param array|Zend\Mail\Headers $headers The email headers
 *
 * @return false|WPAS_Email_Header
 */
function ases_read_email_headers( $headers ) {

	if ( is_object( $headers ) && is_a( $headers, 'Zend\Mail\Headers' ) ) {
		return new WPAS_Email_Header_Zend( $headers );
	} elseif( is_array( $headers ) && array_key_exists( 'x-apparently-to', $headers ) ) {
		return new WPAS_Email_Header_Flourish( $headers );
	} else {
		return false;
	}

}

/**
 * Get all mailbox configs from the custom post type
 *
 *
 * @since 5.1.0
 *
 *
 * @return array
 */
function ases_get_mailbox_configs() {

	$args = array(
		'post_type'              => 'wpas_mailbox_config',
		'post_status'            => 'publish',
		'posts_per_page'         => -1,
		'no_found_rows'          => true,
		'cache_results'          => false,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
	);

	$query = new WP_Query( $args );

	if ( empty( $query->posts ) ) {
		return array();
	}

	return $query->posts;

}