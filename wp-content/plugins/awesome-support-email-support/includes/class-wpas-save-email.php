<?php

class WPAS_Save_Email {

	/**
	 * The e-mail to fetch.
	 *
	 * @var Zend\Mail\Storage\Message
	 */
	private $email;

	/**
	 * Holds the email contents.
	 *
	 * The variable holds the different content types included in the email.
	 *
	 * @var array
	 */
	protected $contents = array( 'text' => '', 'html' => '', 'attachments' => array() );

	/**
	 * Holds the headers object from the email.
	 *
	 * @since 0.5.0
	 * @var WPAS_Email_Header_Zend
	 */
	private $headers;

	/**
	 * ID of the ticket this e-mail refers to.
	 *
	 * @var integer|boolean
	 */
	private $ticket_id;

	/**
	 * ID of the item that's been added.
	 *
	 * @var integer
	 */
	private $item_id;

	/**
	 * Set if the reply was rejected
	 *
	 * @since 0.2.5
	 * @var bool
	 */
	private $rejected = false;
	
	/**
	 * Set reason why reply was rejected
	 *
	 * @since 5.0.1
	 * @var string
	 */
	private $reject_reason = '';
	
	/**
	 * Is the message a duplicate of an existing ticket?
	 *
	 * @since 5.0.2
	 * @var string
	 */
	private $duplicate_flag = false;	

	/**
	 * User id retrieved from email address
	 *
	 * @var int
	 */
	private $email_user_id;

	/**
	 * Sender email address
	 *
	 * @var string
	 */
	private $user_email;
	
	/**
	 * Sender name (as specified in the email)
	 *
	 * @var string
	 */
	private $sender_name;

	/**
	 * who sent email ie agent, customer, secondary agent etc
	 *
	 * @var int
	 */
	private $email_user_type;

	/**
	 * user id of reply
	 *
	 * @var int
	 */
	private $reply_user_id;

	/**
	 * defaults for certain ticket fields
	 *
	 * @var array
	 */
	private $ticket_defaults;
	
	/**
	 * Rules status holds the value of the rule action if a rule is satisfied.
	 *
	 * @var string
	 */
	private $email_rules_status;
	
	/**
	 * Rules id holds the value of the last rule id whose conditions were satisified..
	 *
	 * @var string
	 */
	private $email_rules_id;
	
	/**
	 * Strip html tags?
	 *
	 * @var 
	 */	
	private $strip_all_html_tags_option = '0' ;
	
	/**
	 * Which HTML tags to keep in incoming message?
	 *
	 * @var string
	 */	
	private $allowed_html_tags_option = '' ;
	
	/**
	 * Convert data to a special characterset?
	 *
	 * @var string
	 */	
	private $char_set_conversion = '' ;	

	/**
	 * Constructor method.
	 *
	 * @since  0.1.0
	 *
	 * @param  Zend\Mail\Storage\Message $email The e-mail to fetch
	 * @param  array $in_ticket_defaults defaults for ticket such as priority, dept etc.
	 */
	public function __construct( $email, $in_ticket_defaults = array() ) {
		
		// Make sure the email we got is, indeed, an object of the Zend Mail framework.
		if ( ! is_object( $email ) || is_object( $email ) && ! is_a( $email, 'Zend\Mail\Storage\Message' ) ) {
			return;
		}

		$headers = $email->getHeaders();

		if ( ! is_object( $headers ) || is_object( $headers ) && ! is_a( $headers, 'Zend\Mail\Headers' ) ) {
			return;
		}
		
		// Set the character encoding to be used for all text data...
		$this->set_char_encoding();

		// Set a couple of instance variables to hold the options for stripping html tags...
		$this->set_strip_tags_options();
		
		// Set ticket defaults instance variable
		$this->ticket_defaults = $in_ticket_defaults;
		
		// Set the email object as a property of the object.
		$this->email = $email;

		// Set the headers variable.
		$this->headers = new WPAS_Email_Header_Zend( $this->email->getHeaders() );

		// Get and set the message parts.
		$this->set_parts( $this->email );

		// Try and extract a ticket ID from the email subject line.
		$this->ticket_id = $this->get_ticket_id();

		// We want to know the user email address in order to "authenticate" the sender and acknowledge the email as being a ticket reply.
		$this->user_email = $this->headers->get_user_email();

		// Now that we have an email address, let's lookup the ticket author and save its user ID.
		$this->email_user_id = $this->get_user_id();
		
		// And, in case we need to create a user we get the sender name if it exists...
		$this->sender_name = $this->headers->get_from_name() ;

		// Get the type of the user who sent the message.
		$this->email_user_type = $this->get_email_user_type();

		$this->reply_user_id = $this->get_reply_user_id();
		
		// Check to see if this is a duplicate message
		$this->check_duplicates();

		// Save the email to the database.
		$this->save_email();

	}

	/**
	 * Set the various message parts.
	 *
	 * Process the various parts of the email and set all relevant information such as plain content, HTML content and
	 * attachments.
	 *
	 * @param $message Zend\Mail\Storage\Message|Zend\Mail\Storage\Part Email message object
	 *
	 * @since 0.5.0
	 *
	 * @return void
	 */
	protected function set_parts( $message ) {

		// First, check to see if the part count is zero. If no parts available get the whole damn message instead...
		if ( 0 === $message->countParts() ) {
			$this->set_contents( 'text', $message->getContent() );
		} else {

			// We're here so we have at least one part in the message...
			for ( $i = 1; $i <= $message->countParts(); $i ++ ) {

				// Get parts and contents of email
				$part     = $message->getPart( $i );
				$contents = $part->getContent();
				
				// Get the transfer encoding type
				$transfer_encoding = '';
				if ( isset($part->contenttransferencoding) ) {
					$transfer_encoding = $part->getHeader( 'contenttransferencoding')->getTransferEncoding(); 
				}				
				
				// Get the character encoding type
				$char_encoding = '' ;
				if ( isset($part->charset) ) {
					$char_encoding = $part->getHeader( 'charset')->getEncoding(); 
				}

				switch ( $part->getHeader( 'contenttype' )->getType() ) {

					case 'text/plain':
						$this->set_contents( 'text', $contents, $transfer_encoding, $char_encoding );
						break;

					case 'text/html':
						$this->set_contents( 'html', $contents, $transfer_encoding, $char_encoding );
						break;

					case 'multipart/alternative':
						$this->set_contents( 'text', $part->getPart( 1 ), $transfer_encoding, $char_encoding );
						$this->set_contents( 'html', $part->getPart( 2 ), $transfer_encoding, $char_encoding );
						break;

					case 'multipart/related':
						// Recursive call!
						// Needed because zend sometimes embeds the most recent message into a part when messages are forwarded or otherwise embedded within each other.
						$this->set_parts( $part );
						break;

					default:
						// Assume attachment so get filename
						$filename = $part->getHeader( 'contenttype' )->getParameter( 'name' );

						// If no filename then create one
						if ( empty( $filename ) ) {
							$filename = uniqid( 'no-name-', true ) . '.txt';
						}

						// Add the whole thing to our array
						$this->set_contents( 'attachments', array(
							'filename' => $filename,
							'data'     => base64_decode( $part->getContent() ),
						) );

				}
			}
		}

	}

	/**
	 * Store the contents of a message or a part.
	 *
	 * @since 5.0
	 *
	 * @param string       $where Where to store the contents (basically, what type of content it is).
	 * @param string|array $what  What content to store (the message/part contents).
	 *
	 * @return false|array The contents array on success of false on failure.
	 */
	protected function set_contents( $where = 'text', $what, $transfer_encoding = '', $char_encoding = '' ) {

		// Make sure the type of content is valid.
		if ( ! in_array( $where, array( 'text', 'html', 'attachments' ) ) ) {
			return false;
		}

		switch ( $where ) {
			
			case 'attachments':
				$this->contents['attachments'][] = $what;			
				break ;
			
			default:
				
				switch ( $transfer_encoding ) {
					
					case 'base64': 
						$this->contents[ $where ] .= quoted_printable_decode( base64_decode( $what ) ); // Decode the textual content in base 64
						break;

					case '' :  // no specified transfer encoding
						$testdecode = base64_decode( $what, true ); // test to see if it is base64 encoded...			
						if ( false === $testdecode ) {
							$this->contents[ $where ] .= quoted_printable_decode( $what ); // Decode the textual content.							
						} else {
							$this->contents[ $where ] .= quoted_printable_decode( base64_decode( $what ) ); // Decode the textual content in base 64							
						}
						
						// Do character translation here if necessary
						if ( ! empty( $this->char_set_conversion ) ) {
							if ( empty( $char_encoding ) ) {
								// No FROM character encoding specified so use the default embedded in the character string
								$this->contents[ $where ] = mb_convert_encoding( $this->contents[ $where ], $this->char_set_conversion) ;
							} else {
								// Convert FROM the character encoding specified.
								$this->contents[ $where ] = mb_convert_encoding( $this->contents[ $where ], $this->char_set_conversion, $char_encoding) ;
							}
						}
						
						break ;
					
					default: 
						$this->contents[ $where ] .= quoted_printable_decode( $what ); // Decode the textual content.
						
						// Do character translation here if necessary
						if ( ! empty( $this->char_set_conversion ) ) {
							
							if ( ! empty( $char_encoding ) ) {
							
								// Convert to encoding specified in $char_encoding
								$this->contents[ $where ] = mb_convert_encoding( $this->contents[ $where ], $this->char_set_conversion, $char_encoding) ;
								
							} else {
								
								// Force internal encoding.  This is important because if we don't at least do this then
								// foreign characters will cause WP to silently fail the ticket creation process which 
								// just screws up everything else!!!
								$this->contents[ $where ] = mb_convert_encoding( $this->contents[ $where ], $this->char_set_conversion) ;
								
							}
							
						}
						
						break ;
				}
				
				break ;
		}

		return $this->contents;

	}

	/**
	 * Get some of the email contents.
	 *
	 * @since 0.5.0
	 *
	 * @param string $type    Type of content desired.
	 * @param mixed  $default Default value to return if the $type is empty.
	 *
	 * @return string|array
	 */
	public function get_contents( $type = 'text', $default = '' ) {
		if ( isset( $this->contents[ $type ] ) ) {
			return $this->contents[ $type ];
		} else {
			return $default;
		}
	}

	/**
	 * Check if the e-mail refers to an existing ticket.
	 *
	 * If the e-mail has a ticket ID in the X-header we check if this ID
	 * actually matches a ticket in the system.
	 *
	 * @since  0.1.0
	 * @return boolean True if the mail refers to a ticket, false otherwise
	 */
	public function has_ticket() {

		if ( false === $this->ticket_id ) {
			return false;
		}

		$ticket = get_post( $this->ticket_id );

		if ( ! empty( $ticket ) && 'ticket' !== $ticket->post_type ) {
			return false;
		}

		return true;

	}

	/**
	 * Get the ID of the ticket the mail refers to.
	 *
	 * @since  0.1.0
	 * @return integer|boolean The ID of the ticket if any, false if no ticket ID is found
	 */
	public function get_ticket_id() {

		// Get the email subject that (should) contain a reference to a ticket ID.
		$subject = $this->email->subject;

		// Set the pattern for extracting the ticket ID from the email subject.
		$pattern = '.*?(\\(#\\d+#\\))';

		// Match the pattern occurrences.
		preg_match_all( "/" . $pattern . "/is", $subject, $matches );

		// Return the ticket ID if it was found based on the $pattern.
		return isset( $matches[1][0] ) ? trim( $matches[1][0], '(#)' ) : false;

	}
	
	/**
	 * Return the ID of the ticket that is stored in the global instance variable.
	 *
	 * @since 5.0.0
	 *
	 * @return integer The instance variable $ticket_id
	 */
	public function get_final_ticket_id() {
		return $this->ticket_id ;
	}
	 
	/**
	 * Find the user who sent the e-mail.
	 *
	 * If there is a user with the e-mail's sender address
	 * we assume that he is the the author of the message.
	 *
	 * @since  0.1.0
	 * @return integer|boolean The user ID if a user is found in the database, false otherwise
	 */
	public function get_user_id() {

		// First, let's make sure we have the user email (which we should have at this point as it's being fetched in init()).
		if ( is_null( $this->user_email ) ) {
			$this->user_email = $this->headers->get_user_email();
		}

		$user_id = false;

		if ( isset( $this->client ) && isset( $this->client->ID ) ) {
			$user_id = $this->client->ID;
		}

		$user = get_user_by( 'email', $this->user_email );

		if ( false !== $user ) {
			/* Save the user data for later use. */
			$this->client = $user;
			$user_id      = $user->ID;
		}

		return apply_filters( 'ases_get_user_id', $user_id, $this->user_email, $this );

	}

	/**
	 * Get raw content from the e-mail.
	 * This whole section is messy because we need to handle two cases - strip all tags or strip some tags.
	 * Also, if the option to strip some tags is set, we have to always return the HTML part by default instead of the text part.
	 *
	 * @since 0.2.2
	 * @return string
	 */
	public function get_raw_content() {
		// Lets get the content from both text and html, strippped of all tags, to see which part has content in it.
		
		// First, though, we are going to setup the HTML Purifier processor - but only if the strip_all_html_tags option is '1'.
		if ( '1' == $this->strip_all_html_tags_option ) {
				require_once( WPAS_MAIL_PATH . 'vendor/ezyang/htmlpurifier/library/HTMLPurifier.auto.php' );
				
				$purifier_config = HTMLPurifier_Config::createDefault();
				
				if ( ! empty( $this->allowed_html_tags_option ) ) {
					$purifier_config->set('HTML.Allowed', $this->allowed_html_tags_option);
				}
				
				$purifier = new HTMLPurifier($purifier_config);
		}

		// We're either going to strip all tags or strip just some of the tags which is why we have this giant switch/case statement below		
		switch ( $this->strip_all_html_tags_option ) {
			
			case '0':
				$text_contents = wp_strip_all_tags( $this->get_contents( 'text' ) );
				$html_contents = wp_strip_all_tags( $this->get_contents( 'html' ) );
				break;
				
			case '1':
				$text_contents = $purifier->purify( $this->get_contents( 'text' ) );
				$html_contents = $purifier->purify( $this->get_contents( 'html' ) );
				break;
			
			default:
				$text_contents = wp_strip_all_tags( $this->get_contents( 'text' ) );
				$html_contents = wp_strip_all_tags( $this->get_contents( 'html' ) );
				break ;
		}
		
		// Lets calculate the similarity level of the two texts...
		$similarity_level = 0 ;  
		similar_text( strtoupper( $text_contents ), strtoupper( $html_contents ), $similarity_level);

		// Now figure out which part is best to return.
		// Notice that we are applying the strip_all_tags function AFTER the filter is applied. 
		// This allows a user to embed additional content via the filter while still allowing us to 
		// protect by stripping the tags.
		// Also, we're either going to strip all tags or strip just some of the tags which is why we have this giant switch/case statement below.
		if ( ( $similarity_level > 50 && '1' <> $this->strip_all_html_tags_option ) || empty( $this->get_contents( 'html' ) )  ) {			
		
			// return just the text string since they're most likely the same or there's no html part...
			switch ( $this->strip_all_html_tags_option ) {
				case '0':
					return wp_strip_all_tags( apply_filters( 'ases_get_raw_content_filter', $this->get_contents( 'text' ), $this ) );
					break;
				case '1':
					return $purifier->purify( apply_filters( 'ases_get_raw_content_filter', $this->get_contents( 'text' ), $this ) );
					break; 
				default:
					return wp_strip_all_tags( apply_filters( 'ases_get_raw_content_filter', $this->get_contents( 'text' ), $this ) );
					break;
			}
		} else {

			// At this point we either return the HTML tag only because the user has choosen to strip just some of thet tags or we return both the html and text parts together.
			if ( '1' == $this->strip_all_html_tags_option  && false == empty( $this->get_contents( 'html' ) ) ) {
				// return just the HTML portion since the user has choosen to keep some html tags
				switch ( $this->strip_all_html_tags_option ) {
					case '0':
						return wp_strip_all_tags( apply_filters( 'ases_get_raw_content_filter', $this->get_contents( 'html' ), $this ) );
						break;
					case '1':
						return $purifier->purify( apply_filters( 'ases_get_raw_content_filter', $this->get_contents( 'html' ), $this ) );
						break; 
					default:
						return wp_strip_all_tags( apply_filters( 'ases_get_raw_content_filter', $this->get_contents( 'html' ), $this ) );
						break;
				}				
			} else {

				// return both strings concatenated
				switch ( $this->strip_all_html_tags_option ) {
					case '0':
						return wp_strip_all_tags( apply_filters( 'ases_get_raw_content_filter', $this->get_contents( 'text' ) . $this->get_contents( 'html' ), $this ) );
						break;
					case '1':
						return $purifier->purify( apply_filters( 'ases_get_raw_content_filter', $this->get_contents( 'text' ) . $this->get_contents( 'html' ), $this ) );
						break ;
					default:
						return wp_strip_all_tags( apply_filters( 'ases_get_raw_content_filter', $this->get_contents( 'text' ) . $this->get_contents( 'html' ), $this ) );
						break;				
				}
			}
		}
	}
	
	/**
	 * Returns the static variable holding the email address of the sender
	 *
	 * @since 5.0.0
	 * @return string
	 */
	public function get_sender_email() {
		return $this->user_email;
	}

	/**
	 * Get the e-mail content.
	 *
	 * Get the e-mail text content, clean it and format it
	 * in a WordPress style.
	 *
	 * @since  0.1.0
	 * @return string The e-mail content
	 */
	public function get_content() {

		// Get raw content.
		$raw = $this->get_raw_content();

		// Get the delimiter used to separate the original message and the user reply.
		$delimiter = ases_get_message_delimiter();

		// Remove the original content and keep the user reply only.
		$content = false === strpos( $raw, $delimiter ) ? $raw : trim( substr( $raw, 0, strpos( $raw, $delimiter ) ), '<> ' );

		// Clean the email footer.
		$content = $this->clean_footer( $content );

		// Format the email content correctly.
		$content = wpautop( $content );

		return apply_filters( 'ases_post_clean_content_filter', $content, $raw, $this );

	}

	/**
	 * Clean the email footer.
	 *
	 * We now clean the bits of "extra" text added by various e-mail clients.
	 * This is not a comprehensive list but we clean strings added by the well-known
	 * e-mail clients.
	 *
	 * @since 0.5.0
	 *
	 * @param string $body The email body
	 *
	 * @return string
	 */
	public function clean_footer( $body ) {

		/**
		 * Gmail / MacOS X
		 */
		preg_match_all( "/\b(On ).*?(wrote)(:)/is", $body, $gmail );

		if ( isset( $gmail[0][0] ) ) {
			$body = substr( $body, 0, strpos( $body, $gmail[0][0] ) );
		}

		/**
		 * Sent from a mobile
		 */
		$mobiles = array(
			'Sent from my iPhone',
			'Sent from my BlackBerry',
		);

		foreach ( $mobiles as $mobile ) {
			if ( strpos( $body, $mobile ) !== false ) {
				$body = substr( $body, 0, strpos( $body, $mobile ) );
			}
		}

		return $body;

	}

	/**
	 * Get the e-mail subject.
	 *
	 * @since  0.1.0
	 * @return string E-mail subject (empty if subject can't be found)
	 */
	public function get_subject() {

		if ( isset( $this->email->subject ) ) {
			return wp_strip_all_tags( $this->email->subject );
		} else {
			return '';
		}

	}

	/**
	 * Save the e-mail headers for future reference.
	 *
	 * @since  0.1.0
	 *
	 * @param  integer $post_id ID of the post to attach the headers to
	 *
	 * @return void
	 */
	public function log_headers( $post_id ) {
		add_post_meta( $post_id, '_wpas_source', 'email', true );
		add_post_meta( $post_id, '_wpas_email_headers', $this->email->getHeaders(), true ); // The headers are saved as a Zend\Mail\Headers object.
	}

	/**
	 * Save the e-mail raw content in case the processed content is incorrect
	 *
	 * @since  0.2.2
	 *
	 * @param  integer $post_id ID of the post to attach the headers to
	 *
	 * @return void
	 */
	public function log_raw_content( $post_id ) {
		add_post_meta( $post_id, '_wpas_email_raw_content', $this->get_contents( 'text' ), true );
	}

	/**
	 * Save the e-mail to the database.
	 *
	 * The function runs a number of verifications and identifies
	 * where should the e-mail attached, then saves it into the database.
	 * If we can't figure out what the e-mail refers to, we save it
	 * with a special status and ask for manual intervention.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function save_email() {
		
		// If this is a duplicate message just return to the calling program which will delete the message from the mailbox
		if ( true === $this->is_duplicate() ) {
			return ;
		}
		
		// Check to see if email passes the rules
		$this->email_rules_status = $this->apply_inbox_filter_rules();
		$rule_status = $this->email_rules_status();
		
		// If the rules did not pass take appropriate action...
		if ( $rule_status <> 'no_action' ) {
			switch ( $rule_status ) {
				case 'drop':
					// delete the email and move on - make no record of it..
					return ;  // do nothing but return to the calling program which will just delete the thing...
					break;
					
				case 'unassigned':
					// move the email to unassgned
					$this->create_unassigned_ticket();
					return ;
					break;
					
				case 'skip' :
					// do nothing, but the calling program better not delete the email!
					// generally the calling program is the wpas_read_and_import_emails_from_mailbox() function in functions-emails.php.
					return;
					break;
					
				case 'close' :
					// do nothing if we have a ticket id but the calling program should close the ticket
					// generally the calling program is the wpas_read_and_import_emails_from_mailbox() function in functions-emails.php.

					if ( $this->has_ticket() ) {
						return;
					}
					break;
					
				case 'action' :
					// do nothing - this is a POST SAVE rule that acts after the reply/ticket has been saved.
					// We're including this here for completness so that the reader knows to look
					// elsewhere for the code that processes this case.
					break;
			}			
		}
		// End if rules did not passs
			
		//Rules passed so we can proceed with saving the email...	
		$ticket_settings = wpas_get_option( 'ticket_settings' );

		if ( $this->has_ticket() ) {
			$this->maybe_add_reply(); // Let's see if we can add the reply...
		} else {
			
			switch ( $ticket_settings ) {

				case '0': // leave in unassigned folder
					
					$this->create_unassigned_ticket();
			
					break;
					
				case '1': // Create new ticket and new user
					
					if ( empty( $this->email_user_id ) ) {
						$new_user_id = $this->register_new_user();

						/* Save ticket in saved user. */
						$this->add_new_ticket( $new_user_id );
					} else {
						// we know the user s why the hell are we here?
						// Regardless, since we know the user, might as well add a ticket anywya
						$this->add_new_ticket( $this->email_user_id );
					}
					
					break;
					
				case '2': // Create new ticket if email address matches an existing user; otherwise leave in "Unassigned" folder
					
					if ( ! empty( $this->email_user_id ) ) {
						// add ticket
						$this->add_new_ticket( $this->email_user_id );
					} else {
						// unknown email address so put in unassigned
						$this->create_unassigned_ticket();
					}	
				
					break;
			}
		}
		
		// Apply UPDATE action rules
		// if ( 'update' === $rule_status ) {
		// 	$this->apply_inbox_update_rules();
		//}
		
		do_action( 'wpas_email_processed', $this->item_id, array() );
		
		return ;
	}
	
	/**
	 * Run through the rules for the inbox and 
	 * return an appropriate status for the current message 
	 * based on the rules configured in the wpas_inbox_rules posttype.
	 *
	 * We will check all types of rules EXCEPT UPDATE action rules.
	 * UPDATE action rules run AFTER a ticket/reply has been added.
	 *
	 * @since 5.0.0
	 * 
	 * @return string
	 */ 
	
	function apply_inbox_filter_rules() {
		
		// Set default return status
		$return_status = 'no_action';
		
		// Get the list of rules
		$mailbox_rules = $this->get_mailbox_rules() ;
		
		if ( false === empty($mailbox_rules) ) {
			
			// We've got a list of rules so lets go through them one at a time...
			foreach ( $mailbox_rules as $mb_rule ) {
				
				// if we haven't errored out yet, then continue processing...
				if ( $return_status === 'no_action' ) {
				
					// read the data from the CPT
					$rule_name		= $mb_rule->post_title;
					$rule_type 		= get_post_meta( $mb_rule->ID, 'wpas_inboxrules_rule_type', true);
					$rule_contents 	= get_post_meta( $mb_rule->ID, 'wpas_inboxrules_rule_contents', true);
					$rule_area 		= get_post_meta( $mb_rule->ID, 'wpas_inboxrules_rule_area', true);
					$rule_active 	= get_post_meta( $mb_rule->ID, 'wpas_inboxrules_rule_active', true);
					$rule_action 	= get_post_meta( $mb_rule->ID, 'wpas_inboxrules_rule_action', true);
					
					//convert both subject and body to the same case - will be used for simple/normal matching
					$u_subject 		= strtoupper( $this->get_subject() ) ;
					$u_body			= strtoupper( $this->get_content() ) ;
					$u_senderemail	= strtoupper( $this->get_sender_email() ) ;

					if ( 1 === (int) $rule_active ) {
												
						// This rule is active - lets see what kind it is...
						switch ( $rule_type ) {
							case 'normal':

								// Simple search of the subject or email body for a matching text string...
								// Note that we have to check the type returned by the strpos function because of wacky behavior when the match occurs at the beginning of the string....
								if ( ( ( $rule_area === 'subject' or $rule_area === 'both' ) && gettype( strpos( $u_subject, strtoupper( $rule_contents) ) ) <> 'boolean' ) or 
									 ( ( $rule_area === 'body' or $rule_area === 'both' ) && gettype( strpos( $u_body, strtoupper( $rule_contents) ) ) <> 'boolean' ) or
									 ( ( $rule_area === 'header' or $rule_area === 'both' ) && gettype( strpos( $u_senderemail, strtoupper( $rule_contents) ) ) <> 'boolean' )
								) {

									$this->email_rules_id = $mb_rule->ID; // record the rule id that was matched - we keep the last matched rule in this class variable.
									$return_status = $rule_action ;  // rule matched so set the return value to whatever action needs to be taken...

								}
								
								break;
								
							case 'regex' :
													
								// Regex search...
								if ( ( ( $rule_area === 'subject' or $rule_area === 'both' ) && preg_match( $rule_contents, $this->get_subject()  ) > 0 ) or 
									 ( ( $rule_area === 'body' or $rule_area === 'both' ) && preg_match( $rule_contents, $this->get_content() ) > 0 ) or 
									 ( ( $rule_area === 'header' or $rule_area === 'both' ) && preg_match( $rule_contents, $this->get_sender_email() ) > 0 )
								) {
							
									$this->email_rules_id = $mb_rule->ID; // record the rule id that was matched - we keep the last matched rule in this class variable.
									$return_status = $rule_action ;  // rule matched so set the return value to whatever action needs to be taken...

								}
								
								break ;
						
						} // end switch
											
					} // end if $rule_active = 1
				
				} // end if $return_status = no_action
				
				// If we have a rule match, there's no point in keeping going through the for-each loop - just break.
				//  Only one rule can be applied for each email message.
				if ( $return_status <> 'no_action' ) {
					break ;
				}
				
			}  // end foreach
			
		} // end empty($mailbox_rules)

		return $return_status;
	}
	
	/**
	 * Run through the UPDATE action rules for the inbox and 
	 * execute them.
	 *
	 * This is called after a ticket or reply is created and
	 * the rule that matched was an UPDATE action rule.
	 *
	 * @since 5.0.0
	 *
	 * @param: $rule_id The postID of the rule CPT to execute
	 * @param: $in_ticket_id The ticket post_id to update
	 * 
	 * @return string
	 */
	 function apply_inbox_update_rules( $rule_id = null, $in_ticket_id = null ) {
		 
		 // Take params and assign them to local variables...
		 $rule_id_to_execute = $rule_id ;
		 $ticket_id_to_update = $in_ticket_id;
		 
		 // If no ruleid was passed in then use the one in the instance variable.
		 if ( empty( $rule_id_to_execute) ) {
			 $rule_id_to_execute = $this->email_rules_id;
		 }

		 // If we still have no rule id that we can execute against then return - nothing to do.
		 if ( empty( $rule_id_to_execute) ) {
			return ;
		 }
		 
		 // If a ticket id is passed in, make sure that its an actual TICKET object.
		 // If not, set it to null and let the steps below figure out the real ticket to use
		 // (If this function is being called from a REPLY hook then the id passed in is a REPLY ID and not a TICKET id.)
		 if ( 'ticket' <> get_post_type($ticket_id_to_update) ) {
			 $ticket_id_to_update = null ;
		 }		 
		 
		 // If no ticket id was passed see if we can get one from the class instance variable
		 if ( empty( $ticket_id_to_update ) ) {
			 $ticket_id_to_update = $this->ticket_id;
		 }
		 
		 // if ticket id we want to update is still empty we just return....
		 if ( empty( $ticket_id_to_update ) ) {
			 return;
		 }
		 
		 // make sure the rule is active AND an UPDATE action rule...
		$rule_active	= esc_html( get_post_meta ($rule_id_to_execute, 'wpas_inboxrules_rule_active', true) ); 	// can be 1 / 0 (checkbox) 1=active 0=inactive
		$rule_action	= esc_html( get_post_meta ($rule_id_to_execute, 'wpas_inboxrules_rule_action', true) ); 	// can be 1 = drop completely; 2 = add to unassigned inbox
		if ( ( 1 <> (int) $rule_active ) or ( ( 'update' <> $rule_action) and ( 'update_and_close' <> $rule_action) ) ) {
			return ;
		}

		 // Now get a query object with the rule...
		 $mailbox_rules = $this->get_mailbox_rules( $rule_id_to_execute ) ;

		 //we're supposed to only get at most one row but just in case we're gonna loop through anyway...
		 foreach ( $mailbox_rules as $mb_rule ) {

			// Get data from the server about this inbox rule
			$rule_type		= esc_html( get_post_meta ($mb_rule->ID, 'wpas_inboxrules_rule_type', true) );  	// can be 'regex' or 'normal'
			$rule_contents	= get_post_meta ($mb_rule->ID, 'wpas_inboxrules_rule_contents', true) ;  			// either a regex rule or a series of characters to be matched.  Do not escape this value otherwise key characters will be lost from a regex expression!
			$rule_area		= esc_html( get_post_meta ($mb_rule->ID, 'wpas_inboxrules_rule_area', true) );  	// What area does a rule apply? "subject", "body", "both"
			$rule_active	= esc_html( get_post_meta ($mb_rule->ID, 'wpas_inboxrules_rule_active', true) ); 	// can be 1 / 0 (checkbox) 1=active 0=inactive
			$rule_action	= esc_html( get_post_meta ($mb_rule->ID, 'wpas_inboxrules_rule_action', true) ); 	// can be 1 = drop completely; 2 = add to unassigned inbox			
			$rule_notes		= esc_html( get_post_meta ($mb_rule->ID, 'wpas_inboxrules_rule_notes', true) );  	// user defined notes to clarify what the rule is trying to accomplish
			
			// Get additional data for fields that might need to be updated when this rule is executed.
			$new_assignee			= esc_html( get_post_meta ($mb_rule->ID, 'wpas_inboxrules_rule_new_assignee', true) );
			$new_dept				= esc_html( get_post_meta ($mb_rule->ID, 'wpas_inboxrules_rule_new_dept', true) );
			$new_product			= esc_html( get_post_meta ($mb_rule->ID, 'wpas_inboxrules_rule_new_product', true) );
			$new_priority			= esc_html( get_post_meta ($mb_rule->ID, 'wpas_inboxrules_rule_new_priority', true) );
			$new_channel			= esc_html( get_post_meta ($mb_rule->ID, 'wpas_inboxrules_rule_new_channel', true) );
			
			$new_status				= esc_html( get_post_meta ($mb_rule->ID, 'wpas_inboxrules_rule_new_status', true) );
			$new_public_flag		= esc_html( get_post_meta ($mb_rule->ID, 'wpas_inboxrules_rule_new_public_flag', true) );
			
			$new_addlparty_email1	= esc_html( get_post_meta ($mb_rule->ID, 'wpas_inboxrules_rule_new_addlparty_email1', true) );
			$new_addlparty_email2	= esc_html( get_post_meta ($mb_rule->ID, 'wpas_inboxrules_rule_new_addlparty_email2', true) );
			
			$new_secondary_assignee	= esc_html( get_post_meta ($mb_rule->ID, 'wpas_inboxrules_rule_new_secondary_assignee', true) );
			$new_tertiary_assignee	= esc_html( get_post_meta ($mb_rule->ID, 'wpas_inboxrules_rule_new_tertiary_assignee', true) );
			
			//Write the data...		
			if ( false === empty( $new_assignee ) && $new_assignee > 0 ) {
				update_post_meta( $ticket_id_to_update, '_wpas_assignee', $new_assignee);
			}
			
			if ( false === empty( $new_dept ) && $new_dept > 0  ) {
				wp_set_post_terms( $ticket_id_to_update, $new_dept, 'department', false );
			}
			
			if ( false === empty( $new_product ) && $new_product > 0  ) {
				wp_set_post_terms( $ticket_id_to_update, $new_product, 'product', false );
			}

			if ( false === empty( $new_priority ) && $new_priority > 0  ) {
				wp_set_post_terms( $ticket_id_to_update, $new_priority, 'ticket_priority', false );
			}
			
			if ( false === empty( $new_channel ) && $new_channel > 0 ) {
				wp_set_post_terms( $ticket_id_to_update, $new_channel, 'ticket_channel', false );
			}
			
			if ( false === empty( $new_public_flag ) ) {
				update_post_meta( $ticket_id_to_update, '_wpas_pbtk_flag', $new_public_flag);
			}
			
			if ( false === empty( $new_addlparty_email1 ) ) {			
				update_post_meta( $ticket_id_to_update, '_wpas_first_addl_interested_party_email', $new_addlparty_email1);
			}
			
			if ( false === empty( $new_addlparty_email2 ) ) {
				update_post_meta( $ticket_id_to_update, '_wpas_second_addl_interested_party_email', $new_addlparty_email2);
			}
			
			if ( false === empty( $new_secondary_assignee ) && $new_secondary_assignee > 0  ) {
				update_post_meta( $ticket_id_to_update, '_wpas_secondary_assignee', $new_secondary_assignee);
			}
			
			if ( false === empty( $new_tertiary_assignee ) && $new_tertiary_assignee > 0 ) {
				update_post_meta( $ticket_id_to_update, '_wpas_tertiary_assignee', $new_tertiary_assignee);
			}
			
			// Update custom status
			if ( false === empty( $new_status ) ) {
				$ticket_to_update = array(
					  'ID'           => $ticket_id_to_update,
					  'post_status'  => $new_status
				);			
				// Update the post into the database so that custom status can be updated
				wp_update_post( $ticket_to_update );			  
			}
			
		 }		 
	 }
	
	/**
	 * Get all mailbox rules from the custom post type
	 *
	 *
	 * @since 5.0.0
	 *
	 * @param $mailbox_rule_id Optional post-id for the mailboxrule we are looking for.  If not passed we will send back all rules.
	 *
	 * @return array Array consisting of all records in the wpas_inbox_rules posttype
	 */
	function get_mailbox_rules($mailbox_rule_id = null) {
		
		if ( is_null($mailbox_rule_id) ) {
			// send back all rows for the CPT
			$args = array(
				'post_type'              => 'wpas_inbox_rules',
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'no_found_rows'          => true,
				'cache_results'          => false,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false
			);
		} else {
			
			$args = array(
				'p'					 	 => $mailbox_rule_id,
				'post_type'              => 'wpas_inbox_rules',
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'no_found_rows'          => true,
				'cache_results'          => false,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false
			);			
		}
			
		$query = new WP_Query( $args );

		if ( empty( $query->posts ) ) {
			return array();
		}

		return $query->posts;

	}
	
	/**
	 * Check to see if the incoming message matches a recent ticket.
	 *
	 * @since 5.0.2
	 *
	 * @param none
	 *
	 * @return boolean A flag indicating whether the current message is a duplicate or not
	 */	
	function check_duplicates() {
		
		if ( true === (boolean) wpas_get_option( 'as_es_no_duplicates', false ) ) {
			
			// Get the contents of the message
			$msg = $this->get_content();
			
			// Get a list of recent tickets...
			$args = array(
				'post_type'              => 'ticket',
				'posts_per_page'         => (int) wpas_get_option( 'as_es_recent_tickets_to_check', 10 ),
				'no_found_rows'          => true,
				'orderby'				 => 'date',
				'order'					 => 'DESC',
				'cache_results'          => false,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false
			);			

			$tickets = [] ;
			$ticket_query = new WP_Query( $args );
			
			if ( ! empty( $ticket_query ) ){
				$tickets = $ticket_query->posts;
			}
			
			// List of tickets acquired and is in the $tickets variable.  Loop through and compare 
			// message contents againts the ticket content...
			foreach ( $tickets as $ticket ) {
				
				if ( trim( $msg ) == trim( $ticket->post_content ) ) {
					// Match - set duplicate flag variable and break!
					$this->duplicate_flag = true ;
					break ;
				}
				
			}			
		}
	}

	/**
	 * Add unknown message
	 *
	 * @since 0.4.0
	 * 
	 * @return void
	 */ 
	function create_unassigned_ticket() {

		/* First use the default assignee as the message author */
		$agent = wpas_get_option( 'assignee_default' );

		/* In case there is no default assignee set (wtf) we try to use user 1 who should be the admin */
		if ( empty( $agent ) ) {
			$agent = 1;
		}						
		
		/* Save the message as an unknown */
		$this->add_unknown_message( array(
			'post_content' => $this->get_content(),
			'post_author'  => $agent,
			'post_title'   => $this->get_subject(),
		), false );	
	}

	/**
	 * Save email attachments.
	 *
	 * Once an item has been added, we check if there were any attachments to the email and, if any, save them with the
	 * item.
	 *
	 * Action Hook: wpas_open_ticket_before_assigned
	 *
	 * @since 0.5.0
	 *
	 * @param int $ticket_id ID of the post to attach the file to
	 *
	 * @return int Number of attachments saved.
	 */
	public function save_attachments( $ticket_id ) {

		// Set the number of saved attachments to 0 for starters.
		$saved = 0;

		// If there is no attachment, we return early to avoid loading useless stuff.
		if ( 0 === count( $this->get_attachments() ) ) {
			return $saved;
		}

		/**
		 * Instantiate the uploader class.
		 *
		 * @var WPAS_File_Upload $uploader
		 */
		$uploader = WPAS_File_Upload::get_instance();

		// These files need to be included for email attachments.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		// Add the attachments one by one.
		$uploader->process_attachments( $ticket_id, $this->get_attachments() );

		return count( $this->get_attachments() );

	}

	/**
	 * Get email attachments.
	 *
	 * @since 0.5.0
	 * @return mixed
	 */
	protected function get_attachments() {

		$attachments = array();

		// Clean attachments by making sure all the data we need is there.
		if ( isset( $this->contents['attachments'] ) && is_array( $this->contents['attachments'] ) ) {
			$attachments = $this->contents['attachments'];
		}

		return $attachments;

	}

	/**
	 * Check if a new reply can be added to the ticket
	 *
	 * If we receive an e-mail reply that can be identified, we make sure that the reply can be added first.
	 *
	 * If the ticket is still open, no problem. Otherwise, we need to check how the admin wants things to be processed.
	 * Either we re-open the ticket, or we simply reject the reply.
	 *
	 * @since 0.2.5
	 * @return void
	 */
	public function maybe_add_reply() {

		// Get ticket object
		$ticket = get_post( $this->ticket_id );	

		// Verify that the ticket is valid otherwise return
		if ( empty( $ticket ) ) {			
			$this->reject_reason = 'Attempt to add a reply but the parent ticket is empty or invalid.  The parent ticket id was: ' . (string) $this->ticket_id ;
			$this->rejected = true;
			wpas_write_log('email-piping', $this->reject_reason );	
			return ;
		}
		
		// Get ticket status
		$ticket_status = wpas_get_ticket_status( $this->ticket_id );
		
		// Is email being sent by a customer?
		$is_a_customer = $this->is_customer();
		
		if ( 'open' === $ticket_status ) {
			$this->add_reply();
		} else {
	
			/**
			 *
			 * This section of code figures out how to handle closed tickets and locked tickets.
			 *
			 */

			// See what options are set for handling closed tickets
			$closed_ticket_options = (int) wpas_get_option( 'replied_to_closed', 0 );
			
			// Get locked status of ticket
			$locked = ( (int) wpas_get_cf_value( 'ticket_lock', $this->ticket_id ) === 1 && defined( 'AS_PF_VERSION' ) ) ? true : false;

			// Handle closed tickets			
			switch ( $closed_ticket_options ) {

				case 0: // Reject the reply and send an acknowledgment e-mail to the user
					
					// Notify the person who opened the ticket if the email was from them.
					if ( $is_a_customer ) {	
						wpas_email_notify( $this->ticket_id, 'email_reply_rejected' );
					}
					
					// Need to add checks and send notifications here for primary, secondary and tertiary agents as well as interested parties.
					// This part not completed yet.
					
					// Regardless of whether a notification was sent out, reject the message.
					$this->reject_reason = esc_html__( 'An e-mail reply was rejected by the system. Reason: cannot reply to closed tickets by email.', 'as-email-support' ) ;
					$this->rejected = true;
					wpas_write_log('email-piping', $this->reject_reason );
					wpas_log( $this->ticket_id, $this->reject_reason );

					break;

				case 1: // Re-open the ticket and then add the reply - but only if not locked
					if (false === $locked ) {
						wpas_reopen_ticket( $this->ticket_id );
						$this->add_reply();
					}
					break;
			}
			
			// Handle locked tickets notifications...only if productivity is installed	
			if ( true === $locked ) {
			
				// Notify the person who opened the ticket if the email was from them.
				if ( $is_a_customer ) {	
					wpas_email_notify( $this->ticket_id, 'email_reply_lock_rejected' );
				}
				
				// Need to add checks and send notifications here for primary, secondary and tertiary agents as well as interested parties.
				// This part not completed yet.
				
				// Regardless of whether a notification was sent out, reject the message.
				$this->reject_reason = sprintf( esc_html__( 'An e-mail reply was rejected by the system. Reason: cannot reply to locked tickets. Email was sent by: %s' , 'as-email-support' ), $this->user_email );
				$this->rejected = true;
				wpas_write_log('email-piping', $this->reject_reason );
				wpas_log( $this->ticket_id, $this->reject_reason );
			
			}		
		}
	}
	
	/**
	 * Return whether or not the current reply is from a customer/client
	 *
	 * @return boolean
	 */
	public function is_customer() {
		// Get ticket object
		$ticket = get_post( $this->ticket_id );	
		
		// Return false and exit function if not a WordPress post...
		if ( ! is_a( $ticket, 'WP_Post' ) ) {
			return false;
		}
		
		// Initialize return variable
		$is_a_customer = false ;

		if ( ( (int) $ticket->post_author === (int) $this->reply_user_id ) && 'customer' == $this->email_user_type ) {
			$is_a_customer = true ;
		}
		return $is_a_customer ;
	 }
	
	
	/**
	 * Get user type of email
	 *
	 * @return string
	 */
	public function get_email_user_type() {

		$user_type = 'NOT_FOUND';

		if ( $this->is_email_from_primary_agent() ) {
			$user_type = 'agent';
		} elseif ( $this->is_email_from_secondary_or_tertiary_agent() ) {
			$user_type = 'secondary_agent';
		} elseif ( $this->is_email_from_3rd_party() ) {
			$user_type = '3rd_party_user';
		} elseif ( $this->is_email_from_customer() ) {
			$user_type = 'customer';
		}

		return $user_type;

	}

	/**
	 * Reply user id
	 *
	 * @return int
	 */
	public function get_reply_user_id() {

		if ( 'agent' === $this->email_user_type || 'secondary_agent' === $this->email_user_type ) {
			$user_id = $this->email_user_id;
		} elseif ( '3rd_party_user' === $this->email_user_type ) {
			$user_id = get_post_meta( $this->ticket_id, '_wpas_assignee', true );
		} else {
			$user_id = $this->get_ticket_customer();
		}

		return $user_id;
	}

	/**
	 * Get customer of found ticket
	 *
	 * @return int
	 */
	public function get_ticket_customer() {

		$customer = null;
		if ( $this->ticket_id ) {
			$ticket = get_post( $this->ticket_id );
			
			// Return false and exit function if not a WordPress post...
			if ( is_a( $ticket, 'WP_Post' ) ) {
				$customer = $ticket->post_author;
			}
		}
		return $customer;
	}

	/**
	 * Insert the reply.
	 *
	 * Insert the reply in database using the Tickets API.
	 * We also add a couple of extra information as post meta.
	 *
	 * @since  0.1.0
	 */
	public function add_reply() {

		$ticket = get_post( $this->ticket_id );
		
		// If the ticket is invalid, return right away...
		if ( empty( $ticket ) ) {
			return new WP_Error( 'invalid_ticket_id', __( 'Invalid Ticket ID', 'as-email-support' ) );
		}
		
//		$locked = false ;
//		if ( ! empty( $ticket ) ) {
//			$locked = (int) wpas_get_cf_value( 'ticket_lock', $ticket->ID ) === 1 ? true : false;
//		}

		$data = array(
			'post_content'   => $this->get_content(),
			'post_status'    => 'unread',
			'post_type'      => 'ticket_reply',
			'ping_status'    => 'closed',
			'comment_status' => 'closed',
			'post_parent'    => $this->ticket_id,
			'post_date'      => $this->headers->get_date(),
			'post_date_gmt'  => $this->headers->get_date_gmt(),
		);

		add_action( 'wpas_add_reply_complete', array( $this, 'save_attachments' ), 9, 2 );
		add_action( 'wpas_add_reply_complete', array( $this, 'save_new_ticket_mailbox_rules_defaults' ), 9, 2 );
		
		$this->item_id = wpas_add_reply( $data, $this->ticket_id, $this->reply_user_id );
		
		remove_action( 'wpas_add_reply_complete', array( $this, 'save_attachments' ), 9 );
		remove_action( 'wpas_add_reply_complete', array( $this, 'save_new_ticket_mailbox_rules_defaults' ), 9 );

		if ( false === is_a( $this->item_id, 'WP_Error' ) )	{
			
			if ( 0 !== $this->item_id ) {
				$this->log_headers( $this->item_id );
				$this->log_raw_content( $this->item_id );

				update_post_meta( $this->item_id, 'email_user_type', $this->email_user_type );
				update_post_meta( $this->item_id, 'email_user_email', $this->user_email );
			}
		} else {

			// whoops - we have an error so log it to the log files!
			$this->reject_reason = esc_html__( 'An e-mail reply could not be saved. Maybe the reply has characters that WordPress does not like in posts? Check for UTF-8 encoded characters in the message that look like regular ansi characters!', 'as-email-support' ) ;
			wpas_write_log('email-piping', $this->reject_reason );
			wpas_write_log('email-piping', print_r($this->item_id,true));
			
			// And attempt to log it on the ticket too!
			wpas_log( $this->ticket_id, $this->reject_reason );

			// And set the rejection flag so it can be deleted by the calling function...
			$this->rejected = true;			
			
		}
		
		return $this->item_id;

	}

	/**
	 * Save the message as an unknown reply.
	 *
	 * @since 0.1.0
	 *
	 * @param array $args         Message arguments
	 * @param bool  $known_author Whether or not the author has been identified
	 *
	 * @return int Post ID
	 */
	public function add_unknown_message( $args, $known_author = true ) {

		$args['post_date']     = $this->headers->get_date();
		$args['post_date_gmt'] = $this->headers->get_date();

		/* We need to remove that action, so it wont try to assign it right away */
		remove_action( 'save_post_wpas_unassigned_mail', array( 'WPAS_Email_Assign', 'save_hook' ), 10 );

		/* Save this message an unknown but specify the correct sender */
		$this->item_id = wpas_mail_add_message( $args );

		if ( 0 !== $this->item_id ) {

			$this->log_headers( $this->item_id );
			$this->log_raw_content( $this->item_id );
			$this->save_attachments( $this->item_id );

			if ( ! $known_author ) {
				add_post_meta( $this->item_id, '_wpas_mail_unknown_author', 1, true );
			}
		}
		
		do_action( 'wpas_unassigned_ticket_created', $this->item_id );

		return $this->item_id;

	}

	/**
	 * Check if the e-mail has been added as a reply.
	 *
	 * @since  0.1.0
	 * @return boolean True if a reply was added, false otherwise
	 */
	public function is_reply_added() {
		return isset( $this->item_id ) && ! empty( $this->item_id ) && 0 !== $this->item_id && false === is_a( $this->item_id, 'WP_Error' ) ? true : false;
	}
	
	/**
	* Return private variable that contains the status / state of the email rules
	*
	* @since  5.0.0
	*
	* @return string contents of private variable $email_rules_status
	*/
	public function email_rules_status() {
		return $this->email_rules_status;
	}
	
	/**
	 * Return the private instance variable that contains the last rule that was satisfied.
	 */
	 public function get_satisfied_rules_id() {
		 return $this->email_rules_id;
	 }

	/**
	 * Check if the reply has been rejected
	 *
	 * @since 0.2.5
	 * @return bool
	 */
	public function is_reply_rejected() {
		return $this->rejected;
	}
	
/**
	* Return private variable that contains whether or not the message is a duplicate
	*
	* @since  5.0.2
	*
	* @return boolen contents of private variable $duplicate_flag
	*/
	public function is_duplicate() {
		return $this->duplicate_flag;
	}	

	/**
	 * Get the ID of the added item.
	 *
	 * @since  0.1.0
	 *
	 * @return integer|false Reply ID if available, false otherwise
	 */
	public function get_item_id() {
		return isset( $this->item_id ) && 0 !== $this->item_id ? $this->item_id : false;
	}


	/**
	 * Check if email is from primary agent
	 *
	 * @return boolean
	 */
	public function is_email_from_primary_agent() {

		if ( $this->email_user_id && $this->ticket_id ) {
			$agent_id = get_post_meta( $this->ticket_id, '_wpas_assignee', true );

			if ( (int) $agent_id === $this->email_user_id ) {
				return $agent_id;
			}
		}

		return false;
	}

	/**
	 * Check if email is from secondary or tertiary agent
	 *
	 * @return boolean
	 */
	public function is_email_from_secondary_or_tertiary_agent() {

		$is_email_from_secondary_or_tertiary_agent = false;

		if ( $this->email_user_id && $this->ticket_id ) {

			$agent_metas = array( '_wpas_secondary_assignee', '_wpas_tertiary_assignee' );

			foreach ( $agent_metas as $meta_key ) {
				$agent_id = get_post_meta( $this->ticket_id, $meta_key, true );
				if ( $agent_id == $this->email_user_id ) {
					$is_email_from_secondary_or_tertiary_agent = true;
					break;
				}
			}
		}

		return $is_email_from_secondary_or_tertiary_agent;
	}

	/**
	 * Check if email is from customer
	 *
	 * @return boolean
	 */
	public function is_email_from_customer() {

		$is_email_from_customer = false;

		if ( $this->email_user_id && $this->ticket_id ) {
			$ticket = get_post( $this->ticket_id );
			
			if ( false === empty( $ticket )  && ( 'ticket' === get_post_type( $ticket ) ) ) {
				if ( $ticket->post_author == $this->email_user_id ) {
					$is_email_from_customer = true;
				}
			}
		}

		return $is_email_from_customer;

	}

	/**
	 * Check if email is from 3rd party
	 *
	 * @return boolean
	 */
	public function is_email_from_3rd_party() {

		$is_email_from_3rd_party = false;

		if ( $this->ticket_id ) {

			$metas = array( '_wpas_first_addl_interested_party_email', '_wpas_second_addl_interested_party_email' );

			foreach ( $metas as $meta_key ) {
				$party_email = get_post_meta( $this->ticket_id, $meta_key, true );
				if ( ! empty( $party_email ) && $party_email === $this->user_email ) {
					$is_email_from_3rd_party = true;
					break;
				}
			}
		}

		return $is_email_from_3rd_party;

	}


	/**
	 * Register new user
	 *
	 * @return int
	 */
	public function register_new_user() {

		/* First, figure out how to construct the user name...*/
		$name_ary  		= explode( '@', $this->user_email ); 	// extract whatever name we can from the email address...
		$full_name_ary 	= explode( ' ', $this->sender_name);	// extract first and last names if they exist....
		
		$user_name_construction = wpas_get_option( 'as_es_user_name_construction' );		
		
		switch ( $user_name_construction ) {
			case 0 :
				// use the first part of the email address
				$user_name  = $name_ary[0];
				break;
				
			case 1:
				// use the full email address
				$user_name = $this->user_email;
				break;
				
			case 2:
				// use a random number
				$user_name = mt_rand();
				break;
				
			case 3:
				// use a guid
				$user_name = wpas_create_pseudo_guid();
				break;
				
			case 4:
				// user the sender name
				$user_name = $this->sender_name;
				break ;
				
			default: 
				$user_name  = $name_ary[0];
				break;
		}		
	
		/* set variables for first and last name */
		if ( isset( $full_name_ary[0] ) ) {
			$first_name  = $full_name_ary[0];
		} else {
			$first_name  = $name_ary[0];
		}
		
		if ( isset( $full_name_ary[1] ) ) {
			$last_name  = $full_name_ary[1];
		} else {
			$last_name  = $name_ary[0];
		}		
		
		/* Save new user */
		$user_param = array(
			'email'      => $this->headers->get_user_email(),
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'pwd'        => wp_generate_password( 8, false ),
			'user_login' => $user_name,
		);

		return wpas_insert_user( $user_param );
	}

	/**
	 * add new ticket
	 *
	 * @param null|int $post_author
	 */
	public function add_new_ticket( $post_author = null ) {

		if ( null === $post_author ) {
			$post_author = $this->email_user_id;
		}


		$post_param = apply_filters( 'wpas_open_ticket_data', array(
			'post_content'   => $this->get_content(),
			'post_name'      => $this->get_subject(),
			'post_title'     => $this->get_subject(),
			'post_status'    => 'queued',
			'post_type'      => 'ticket',
			'post_author'    => $post_author,
			'ping_status'    => 'closed',
			'comment_status' => 'closed',
		) );

		// The order and priority of these events are important so change them at your own peril.
		// In particular, the priority levels for *save_new_ticket_mailbox_defaults* and *save_new_ticket_mailbox_rules_defaults*
		// are set to be numerically higher than the ones for functions in core that uses the same *wpas_open_ticket_before_assigned hook*
		add_action( 'wpas_open_ticket_before_assigned', array( $this, 'save_attachments' ), 8, 2 );
		add_action( 'wpas_open_ticket_before_assigned', array( $this, 'save_new_ticket_mailbox_defaults' ), 13, 2 );
		add_action( 'wpas_open_ticket_before_assigned', array( $this, 'save_new_ticket_mailbox_rules_defaults' ), 14, 2 );
		
		$this->item_id = wpas_insert_ticket( $post_param, false, false, 'email' );

		remove_action( 'wpas_open_ticket_before_assigned', array( $this, 'save_attachments' ), 8 );
		remove_action( 'wpas_open_ticket_before_assigned', array( $this, 'save_new_ticket_mailbox_defaults' ), 13 );
		remove_action( 'wpas_open_ticket_before_assigned', array( $this, 'save_new_ticket_mailbox_rules_defaults' ), 14 );
	}
	
	/**
	 * Save defaults defined in the mailbox (such as priority, channel etc) to new ticket.	 
	 * Note that defaults are held in the instance variable $ticket_defaults;
	 * This variable is set in the constructor of this class (passed in as a parameter)
	 *
	 * Action Hook: wpas_open_ticket_before_assigned
	 *
	 * @param null|int $ticket_id the id of the ticket or post object
	 * @param array $ticket the post object with the ticket contents
	 */	
	 public function save_new_ticket_mailbox_defaults( $ticket_id, $ticket ) {

		 if (false == empty( $ticket_id ) && false == empty( $this->ticket_defaults ) ) {

			if ( false === empty( $this->ticket_defaults['defaultpriority'] ) && $this->ticket_defaults['defaultpriority'] > 0 ) {
				wp_set_post_terms( $ticket_id, $this->ticket_defaults['defaultpriority'], 'ticket_priority', false );				
			}
			
			if ( false === empty( $this->ticket_defaults['defaultproduct'] ) && $this->ticket_defaults['defaultproduct'] > 0 ) {
				wp_set_post_terms( $ticket_id, $this->ticket_defaults['defaultproduct'], 'product', false );
			}
			
			if ( false === empty( $this->ticket_defaults['defaultchannel'] ) && $this->ticket_defaults['defaultchannel'] > 0  ) {
				wp_set_post_terms( $ticket_id, $this->ticket_defaults['defaultchannel'], 'ticket_channel', false );
			}
			
			if ( false === empty( $this->ticket_defaults['defaultdept'] ) && $this->ticket_defaults['defaultdept'] > 0 ) {
				wp_set_post_terms( $ticket_id, $this->ticket_defaults['defaultdept'], 'department', false );
			}

			if ( false === empty( $this->ticket_defaults['defaultassignee'] ) && $this->ticket_defaults['defaultassignee'] > 0 ) {
				update_post_meta( $ticket_id, '_wpas_assignee', $this->ticket_defaults['defaultassignee'] );  				// Note that this is liable to be overwritten by the regular assignment function! So save it again below to different field as well.
				update_post_meta( $ticket_id, '_wpas_assignee_email_default', $this->ticket_defaults['defaultassignee'] ); // stick the default assignee into a second field so that auto-assign might potentially use it later.				
			}
			
			if ( false === empty( $this->ticket_defaults['defaultsecondaryassignee'] ) && $this->ticket_defaults['defaultsecondaryassignee'] > 0  ) {
				add_post_meta( $ticket_id, '_wpas_secondary_assignee', $this->ticket_defaults['defaultsecondaryassignee'] );
			}
			
			if ( false === empty( $this->ticket_defaults['defaulttertiaryassigneeassignee'] ) && $this->ticket_defaults['defaulttertiaryassignee'] > 0  ) {
				add_post_meta( $ticket_id, '_wpas_tertiary_assignee', $this->ticket_defaults['defaulttertiaryassignee'] );
			}
			
			if ( false === empty( $this->ticket_defaults['defaultaddlpartyemail1'] ) ) {
				add_post_meta( $ticket_id, '_wpas_first_addl_interested_party_email', $this->ticket_defaults['defaultaddlpartyemail1'] );
			}
			if ( false === empty( $this->ticket_defaults['defaultaddlpartyemail2'] ) ) {
				add_post_meta( $ticket_id, '_wpas_second_addl_interested_party_email', $this->ticket_defaults['defaultaddlpartyemail2'] );
			}
			
			if ( false === empty( $this->ticket_defaults['defaultpublicflag'] ) ) {
				add_post_meta( $ticket_id, '_wpas_pbtk_flag', $this->ticket_defaults['defaultpublicflag'] );
			}
			
			add_post_meta( $ticket_id, '_wpas_mailboxname', $this->ticket_defaults['mailboxtitle'] );
			
	
			// Update custom status
			if ( false === empty( $this->ticket_defaults['defaultstatus'] ) ) {
				  $ticket = array(
					  'ID'           => $ticket_id,
					  'post_status'  => $this->ticket_defaults['defaultstatus']
				  );

				// Update the post into the database so that custom status can be updated
				 wp_update_post( $ticket );
			}
			  
		 }
		 
	 }
	 
	/**
	 * Save defaults defined in the mailbox RULES (such as priority, channel etc) to new ticket.	 
	 * Note that defaults are held in the instance variable $ticket_defaults;
	 * This variable is set in the constructor of this class (passed in as a parameter)
	 *
	 * Action Hook: wpas_open_ticket_before_assigned
	 *
	 * It is important to realize that this will fire AFTER save_new_ticket_mailbox_defaults.
	 * This way the defaults set by the mailbox configuration is written first and then 
	 * the rules defaults take over.
	 *
	 * @param null|int $ticket_id the id of the ticket or post object
	 * @param array $ticket the post object with the ticket contnets
	 */		 
	 public function save_new_ticket_mailbox_rules_defaults( $ticket_id, $ticket ) {

		 $this->apply_inbox_update_rules(null, $ticket_id);
	 }
	 
	/**
	 * Set the character encoding set to be used 
	 *
	 * @since  5.0.1
	 *
	 * @return void
	 */
	public function set_char_encoding() {

		$this->char_set_conversion = wpas_get_option( 'as_es_char_set_conversion' );
		
		if ( empty( $this->char_set_conversion ) ) {
			$this->char_set_conversion = 'UTF-8';
		}
	}
	
	/**
	 * Set the options that control which tags to strip
	 *
	 * @since  5.0.1
	 *
	 * @return void
	 */	
	public function set_strip_tags_options() {
		
		// Set a couple of instance variables to hold the options for stripping html tags...
		$this->strip_all_html_tags_option = wpas_get_option( 'as_es_strip_all_html_tags' );
		$this->allowed_html_tags_option = wpas_get_option( 'as_es_allowed_html_tags' );
		
		if ( empty( $this->strip_all_html_tags_option ) ) {
			$this->strip_all_html_tags_option = '0';
		}
		
		if ( empty( $this->allowed_html_tags_option ) ) {
			$this->allowed_html_tags_option = '';
		}		
	}
}

