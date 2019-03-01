<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


require_once WPAS_IT_PATH . 'includes/email_functions.php';


class WPAS_IT_Email_Notification extends WPAS_Email_Notification {
	
	/**
	 * ID of the post to notify about.
	 * 
	 * @var integer
	 */
	protected $post_id;

	
	public $issue_id;
	
	Public $Issue;
	
	Public $Comment;
	
	/**
	 * Class constructor.
	 * 
	 * @param integer $post_id ID of the post to notify about
	 */
	
	public function __construct( $post_id, $ticket_id = '' ) {
		
		/* Make sure the given post belongs to our plugin. */
		if ( !in_array( get_post_type( $post_id ), array( 'wpas_issue_tracking', 'wpas_it_comment' ) ) ) {
			return new WP_Error( 'incorrect_post_type', __( 'The post ID provided does not match any of the plugin post types', 'wpas_it' ) );
		}
		
		$this->ticket_id = $ticket_id;
		

		/* Set the e-mail content type to HTML */
		add_filter( 'wp_mail_content_type', array( $this, 'set_html_mime_type' ) );

		/* Set the post ID */
		$this->post_id  = $post_id;
		
		
		
		if ( 'wpas_issue_tracking' === get_post_type( $this->post_id ) ) {
			$this->issue_id = $this->post_id;
		} else {
			$comment = $this->getComment();
			$this->issue_id = $comment->getIssueID();
		}
		
		
		$this->Issue = new WPAS_IT_Issue( $this->issue_id );

	}
	
	/**
	 * Also return ticket with get_reply method
	 * 
	 * @return type
	 */
	public function get_reply() {
		
		return $this->get_ticket();
	}
	/**
	 * Return comment
	 * 
	 * @return boolean | Object
	 */
	public function getComment() {
		if ( isset( $this->Comment ) ) {
			return $this->Comment;
		}

		if ( 'wpas_it_comment' !== get_post_type( $this->post_id ) ) {
			return false;
		}

		$this->Comment = new WPAS_IT_Comment( $this->post_id );

		return $this->Comment;
	}
	
	public function is_active( $case ) {
		return true;
	}
	
		
	/**
	 * Convert tags within a string.
	 *
	 * Takes a string (subject or body) and replace the tags
	 * with their current value if any.
	 *
	 * @param  string $contents String to convert tags from
	 * @return string           String with tags converted into their corresponding value
	 */
	public function fetch( $contents ) {

		$tags = $this->get_tags_values();
		
		foreach ( $tags as $tag ) {

			$id       = $tag['tag'];
			$value    = isset( $tag['value'] ) ? $tag['value'] : '';
			$contents = str_replace( $id, $value, $contents );
			
		}

		return $contents;

	}

	

	/**
	 * Get tags and their value in the current context.
	 *
	 * @since  3.0.0
	 * @return array Array of tag / value pairs
	 */
	public function get_tags_values() {

		/* Get all available tags */
		
		if( $this->get_ticket() ) {
			$tags = parent::get_tags_values();
		} else {
			$tags = $this->get_tags();
		}

		/* This is where we save the tags with their new value */
		$new = array();

		/* Get the involved users' information */
		$agent_id = $this->Issue->getPrimaryAgentID();

		// Fallback to the default assignee if for some reason there is no agent assigned
		if ( empty( $agent_id ) ) {
			$agent_id = wpas_get_option( 'assignee_default', 1 );
		}

		$agent  = get_user_by( 'id', (int) $agent_id  );

		/* Get the ticket links */
		
		$url_private = add_query_arg( array( 'post' => $this->issue_id, 'action' => 'edit' ), admin_url( 'post.php' ) );

		/* Add the tag value in the current context */
		foreach ( $tags as $key => $tag ) {

			$name = trim( $tag['tag'], '{}' );

			switch ( $name ) {

				/* Name of the website */
				case 'site_name':
					$tag['value'] = get_bloginfo( 'name' );
					break;
				
				case 'date':
					$tag['value'] = date( get_option( 'date_format' ) );
					break;

				case 'admin_email':
					$tag['value'] = get_bloginfo( 'admin_email' );
					break;
				
				case 'message':
					$tag['value'] = $this->ticket_id ? $this->get_ticket()->post_content : "";
					break;

				/* Ticket ID */
				case 'issue_id';
					$tag['value'] = $this->Issue->getID();
					break;
				/* Name of the agent assigned to this ticket */
				case 'issue_agent_name':
					$tag['value'] = $agent->display_name;
					break;

				/* E-mail of the agent assigned to this ticket */
				case 'issue_agent_email':
					$tag['value'] = $agent->user_email;
					break;


				case 'issue_title':
					$tag['value'] = wp_strip_all_tags( $this->Issue->getTitle() );
					break;
				
				case 'issue_message':
					$tag['value'] = wp_strip_all_tags( $this->Issue->getContent() );
					break;
				case 'issue_comment':
					$tag['value'] = wp_strip_all_tags( $this->Issue->getLastCommentContent() );
					break;
				case 'full_issue':
					$tag['value'] = $this->Issue->full_issue();
					break;
				case 'issue_status':
					$tag['value'] = wp_strip_all_tags( $this->Issue->getStatusName() );
					break;
				case 'issue_priority':
					$tag['value'] = wp_strip_all_tags( $this->Issue->getPriorityName() );
					break;

				case 'issue_admin_link':
					$tag['value'] = '<a href="' . $url_private . '">' . $url_private . '</a>';
					break;

				case 'issue_admin_url':
					$tag['value'] = $url_private;
					break;
				
				case 'comment_status':
					$tag['value'] = $this->Comment ? $this->Comment->getStatusName() : '';
					break;
				
				case 'comment_type':
					$tag['value'] = $this->Comment ? $this->Comment->getTypeName() : '';
					break;
				
				case 'comment':
					$tag['value'] = $this->Comment ? $this->Comment->getContent() : '';
					break;

			}

			array_push( $new, $tag );

		}

		/* Replace the valueless tags array by the new one */
		$tags = apply_filters( 'wpas_email_notifications_tags_values', $new, $this->post_id );

		return $tags;

	}

	/**
	 * Get e-mail content.
	 *
	 * Get the content for the given part.
	 *
	 * @since  3.0.2
	 *
	 * @param  string $part Part of the e-mail to retrieve
	 * @param  string $case Which notification is requested
	 *
	 * @return string       The content with tags converted into their values
	 */
	private function get_content( $part, $case ) {

		if ( ! in_array( $part, array( 'subject', 'content' ) ) ) {
			return false;
		}
		
		$value = wpas_it_get_option( "{$case}__{$part}" );

		$pre_fetch_content = apply_filters( 'wpas_it_email_notifications_pre_fetch_' . $part, $value, $this->post_id, $case, $this->Issue, $this->Comment );
		
		return $this->fetch( $pre_fetch_content );
		
	}

	/**
	 * Get e-mail subject.
	 *
	 * @param $case string The type of e-mail notification that's being sent
	 *
	 * @since  3.0.2
	 * @return string E-mail subject
	 */
	private function get_subject( $case ) {
		return apply_filters( 'wpas_email_notifications_subject', $this->get_content( 'subject', $case ), $this->post_id, $case );
	}

	/**
	 * Get e-mail body.
	 *
	 * @param $case string The type of e-mail notification that's being sent
	 *
	 * @since  3.0.2
	 * @return string E-mail body
	 */
	private function get_body( $case ) {
		return apply_filters( 'wpas_email_notifications_body', stripcslashes ( $this->get_content( 'content', $case ) ), $this->post_id, $case );
	}

	/**
	 * Send out the e-mail notification.
	 *
	 * @since  3.0.2
	 * @param  string         $case The notification case
	 * @return boolean|object       True if the notification was sent, WP_Error otherwise
	 */
	public function notify( $case ) {

		if ( !$this->notification_exists( $case ) ) {
			return new WP_Error( 'unknown_notification', __( 'The requested notification does not exist', 'wpas_it' ) );
		}

		if ( !$this->is_active( $case ) ) {
			return new WP_Error( 'disabled_notification', __( 'The requested notification is disabled', 'wpas_it' ) );
		}

		
		$user = apply_filters( 'wpas_email_notifications_notify_user', null, $case, $this->ticket_id );

		$recipients = $recipient_emails = array();
		
		if( $user ) {
			$recipients[] = $user;
		}
		
				
		foreach( $recipients as $recipient ) {
			if( $recipient instanceof WP_User ) {
				$recipient_emails[] = array( 'user_id' => $recipient->ID, 'email' => $recipient->user_email );
			}

		}
		
		/**
		 * Get the sender information
		 */
		$sender      = $this->get_sender();
		$from_name   = $sender['from_name'];
		$from_email  = $sender['from_email'];
		$reply_name  = $sender['reply_name'];
		$reply_email = $sender['reply_email'];

		/**
		 * Get e-mail subject
		 *
		 * @var  string
		 */
		$subject = $this->get_subject( $case );

		/**
		 * Get the e-mail body and filter it before the template is being applied
		 *
		 * @var  string
		 */
		$body = apply_filters( 'wpas_email_notification_body_before_template', $this->get_body( $case ), $case, $this->ticket_id );

		/**
		 * Filter the e-mail body after the template has been applied
		 *
		 * @since 3.3.3
		 * @var string
		 */
		$body = apply_filters( 'wpas_email_notification_body_after_template', $this->get_formatted_email( $body ), $case, $this->ticket_id );

		/**
		 * Prepare e-mail headers
		 * 
		 * @var array
		 */
		$headers = array(
			"MIME-Version: 1.0",
			"Content-type: text/html; charset=utf-8",
			"From: $from_name <$from_email>",
			"Reply-To: $reply_name <$reply_email>",
			// "Subject: $subject",
			"X-Mailer: Awesome Support/" . WPAS_VERSION,
		);

		/**
		 * Merge all the e-mail variables and apply the wpas_email_notifications_email filter.
		 */
		$email = apply_filters( 'wpas_it_email_notifications_email', array(
			'recipient_email' => $recipient_emails,
			'subject'         => $subject,
			'body'            => $body,
			'headers'         => $headers
			),
			$case,
			$this->post_id, 
			$this->Issue,
			$this->Comment,
			$this->ticket_id
		);
		
		
		if( $this->ticket_id ) {
			$email = apply_filters( 'wpas_email_notifications_email', $email,
				$case,
				$this->ticket_id
			);
		}
		
		if( !is_array( $email['recipient_email'] ) ) {
			$email['recipient_email'] = array( $email['recipient_email'] );
		}
		
		
		// We need to send notifications separately per recipient.
		$mail = false;
		foreach( $email['recipient_email'] as $r_email ) {
			
			
			if( empty( $email['subject'] ) && empty( $email['body'] ) ) {
				continue;
			}
			
			$email_headers = $email['headers'];
			
			$to_email = $r_email;
			
			if( is_array( $r_email ) &&  isset( $r_email['email'] ) && $r_email['email'] ) {
				$to_email = $r_email['email'];
			}
			
			if( is_array( $r_email ) && isset( $r_email['cc_addresses'] ) && !empty( $r_email['cc_addresses'] ) ) {
				$email_headers[] = 'Cc: ' . implode( ',', $r_email['cc_addresses'] );
			}
			
			if( wp_mail( $to_email, $email['subject'], $email['body'], $email_headers ) ) {
				$mail = true;
			}
		}		
		
		
		
		return $mail;

	}
	
}
