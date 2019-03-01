<?php
class WPAS_Email_Assign {

	/**
	 * Instance of this class.
	 *
	 * @since    0.1.0
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * ID of the post to assign.
	 *
	 * @since  0.1.0
	 * @var    integer
	 */
	public $post_id;

	/**
	 * Data to update the post with.
	 *
	 * @since  0.1.0
	 * @var    array
	 */
	public $data;

	/**
	 * Current post object.
	 *
	 * @since  0.1.0
	 * @var    object
	 */
	protected $post = null;

	/**
	 * Post parent object.
	 *
	 * @since  0.1.0
	 * @var    object
	 */
	protected $parent;
	
	/**
	 * new ticket id
	 * 
	 * @var int
	 */
	protected $new_ticket_id = 0;
	
	protected $converted = false;
	
	public function __construct( $post_id = '' ) {

		/* If a post ID is given we store it for possible re-use */
		if ( !empty( $post_id ) ) {
			$this->post_id = intval( $post_id );
		}

		add_filter( 'redirect_post_location', array( $this, 'redirect' ), 10, 2 );

	}

	/**
	 * Instantiate an assignation.
	 *
	 * @since     0.1.0
	 *
	 * @param integer $post_id ID of the post we're trying to convert
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function save_hook( $post_id ) {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		self::$instance->save( $post_id );
	}

	/**
	 * Assign the post to a ticket.
	 *
	 * @param  string|integer $post_id ID of the post to assign
	 * @param array           $data    Post data
	 *
	 * @return void
	 */
	public function save( $post_id = '', $data = array() ) {

		/**
		 * First of all we remove the action in order to avoid infinite loops.
		 */
		remove_action( 'save_post_wpas_unassigned_mail', array( 'WPAS_Email_Assign', 'save_hook' ), 10 );

		if ( ! empty( $post_id ) ) {
			$this->post_id = $post_id;
		}

		if ( empty( $this->post_id ) ) {
			return;
		}

		/* If no data is passed we try to get it from the POST */
		if ( empty( $data ) ) {
			$data = $_POST;
		}

		$this->data = $data;
		
		// Create new ticket and assign it to selected customer
		if( isset( $this->data['assign_action'] ) && $this->data['assign_action'] == 'assign_to_customer' ) {
			
			$this->assign_to_customer();
			
		} else {
			
			if ( isset( $this->data['wpas_message_creator'] ) && ! empty( $this->data['wpas_message_creator'] ) ) {
				$this->assign_author( $this->data['wpas_message_creator'] );
			}

			if ( isset( $this->data['wpas_parent_ticket'] ) && ! empty( $this->data['wpas_parent_ticket'] ) ) {
				$this->assign_ticket( $this->data['wpas_parent_ticket'] );
			}
			
			/* If everything is fixed we convert the post into a ticket reply */
			if ( $this->data['assign_action'] == 'assign_to_ticket' && $this->is_assigned() ) {
				$this->convert();
			}
			
		}
		
	}
	
	public function assign_to_customer() {
		
		if ( isset( $this->data['wpas_message_creator'] ) && ! empty( $this->data['wpas_message_creator'] ) ) {
			
			$author_id = $this->data['wpas_message_creator'];
			if ( ! $this->can_user_be_assigned( $author_id ) ) {
				return false;
			}
			
			if ( is_null( $this->post ) ) {
				$this->post = get_post( $this->post_id );
			}
			
			$data = array (
				'post_content'   => $this->post->post_content,
				'post_name'      => $this->post->post_title,
				'post_title'     => $this->post->post_title,
				'post_status'    => 'queued',
				'post_type'      => 'ticket',
				'post_author'    => $author_id,
				'ping_status'    => 'closed',
				'comment_status' => 'closed'
			);
			
			
			
			$ticket_id = wpas_insert_ticket( $data, false, false, 'email' );
			
			if ( 0 === $ticket_id ) {
				return false;
			} else {
				$this->new_ticket_id = $ticket_id;
				wp_delete_post( $this->post_id, true );
			}
			
			return $ticket_id;
			
		} 
		
		return false;
		
	}

	/**
	 * Redirect after assigning.
	 *
	 * If the message was correctly assigned we redirect
	 * the user to the ticket page.
	 *
	 * @since  0.1.0
	 * @param  string  $location Original redirection URL
	 * @param  integer $post_id  Post ID
	 * @return string            Possibly custom redirect URL
	 */
	public function redirect( $location, $post_id ) {

		if ( 'wpas_unassigned_mail' === get_post_type() ) {
			
			if( $this->new_ticket_id ) {
				$location = add_query_arg( array( 'post' => $this->new_ticket_id, 'action' => 'edit' ), admin_url( 'post.php' ) );
			} elseif ($this->converted) {
				$location = add_query_arg( array( 'post' => $this->parent->ID, 'action' => 'edit' ), admin_url( 'post.php' ) ) . '#wpas-post-' . $post_id;
			}
		}
		
		return $location;
		
	}

	/**
	 * Assign a new author to the post.
	 *
	 * @since  0.1.0
	 *
	 * @param integer $author_id ID of the reply author
	 *
	 * @return integer Author ID
	 */
	
	
	protected function can_user_be_assigned( $user_id ) {
		
		$user_id = intval( $user_id );
		
		/* First of all we make sure the user actually exists */
		$user = get_user_by( 'id', $user_id );
		
		if ( false === $user ) {
			return false;
		}

		/* We make sure that the user can reply to a ticket but is not an agent */
		if ( ! user_can( $user->ID, 'reply_ticket' ) ) {
			return false;
		}
		
		return true;
	}
	
	protected function assign_author( $author_id ) {

		if ( ! $this->can_user_be_assigned( $author_id ) ) {
			return false;
		}
		
		

		$update = update_post_meta( $this->post_id, 'assigned_creator', $author_id );

		if ( 0 === $update ) {
			return false;
		}

		delete_post_meta( $this->post_id, '_wpas_mail_unknown_author', '1' );

		return $author_id;

	}

	/**
	 * Attach the post to a ticket.
	 *
	 * @since  0.1.0
	 *
	 * @param integer $ticket_id ID of the parent ticket
	 *
	 * @return integer Ticket ID
	 */
	protected function assign_ticket( $ticket_id ) {

		$ticket_id = intval( $ticket_id );

		/* First of all we make sure the post actually exists */
		$this->parent = get_post( $ticket_id );

		if ( is_null( $this->parent ) ) {
			return false;
		}

		/* Now we make sure the post really is a ticket */
		if ( 'ticket' !== $this->parent->post_type ) {
			return false;
		}
		
		$update = update_post_meta($this->post_id, 'assign_to_ticket', $this->parent->ID);

		if ( 0 === $update ) {
			return false;
		}

		if ( is_null( $this->post ) ) {
			$this->post = get_post( $this->post_id );
		}

		/* Now let's notify someone that there is a new reply */
		//$this->notify();

		return $this->parent->ID;

	}

	/**
	 * Check if the post has all required information.
	 *
	 * In order to be assigned, the post must have a parent post
	 * and its author must be a support user.
	 *
	 * We don't check the post type during the process because
	 * this method is used before converting the post to a ticket reply.
	 *
	 * @since  0.1.0
	 * @return boolean Whether or not the post is correctly assigned
	 */
	public function is_assigned() {

		if ( is_null( $this->post ) ) {
			$this->post = get_post( $this->post_id );
		}

		if ( is_null( $this->post ) ) {
			return false;
		}
		
		$unknown = get_post_meta( $this->post_id, '_wpas_mail_unknown_author', true );
		
		/* If the unassigned marker is still there we have a problem */
		if ( ! empty( $unknown ) ) {
			return false;
		}
		
		if ( ! user_can( $this->post->post_author, 'reply_ticket' ) ) {
			return false;
		}
		
		if ( !isset($this->parent->ID) || 0 === $this->parent->ID || 'ticket' !== get_post_type( $this->parent->ID ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Convert the post into a ticket reply.
	 *
	 * @since  0.1.0
	 * @return integer Reply ID
	 */
	protected function convert() {

		$author = get_post_meta( $this->post_id, 'assigned_creator', true );
		/* Switch to ticket reply post type */
		$this->post_id = wp_update_post( array( 'ID' => $this->post_id, 'post_type' => 'ticket_reply', 'post_status' => 'unread', 'post_author' =>  $author, 'post_parent' => $this->parent->ID ) );
		
		if ( 0 === $this->post_id ) {
			return false;
		}

		if ( is_null( $this->post ) ) {
			$this->post = get_post( $this->post_id );
		}
		
		delete_post_meta($this->post_id, 'assigned_creator');
		delete_post_meta($this->post_id, 'assign_to_ticket');
		
		$status = get_post_meta( $this->post->post_parent, '_wpas_status', true );

		/* Now let's notify someone that there is a new reply */
		$this->notify();

		/* If the ticket was closed we re-open it */
		if ( 'closed' === $status ) {
			wpas_reopen_ticket( $this->post->post_parent );
		}

		/**
		 * Fire an action after the unknown reply is converted into an actual ticket reply
		 *
		 * @since 0.2.2
		 */
		do_action( 'wpas_email_reply_converted', $this->post_id, $status );
		
		$this->converted = true;
		return $this->post_id;

	}

	/**
	 * Send an e-mail notification.
	 *
	 * Once the reply is converted we send an e-mail notification
	 * to either the agent (if the reply is from the client) or the client
	 * (if the reply is from an agent).
	 *
	 * @since  0.1.0
	 * @return  bool  Whether or not the e-mail was sent
	 */
	protected function notify() {

		if ( empty( $this->post_id ) ) {
			return false;
		}

		/* The new reply is from an agent */
		if ( user_can( $this->post->post_author, 'edit_ticket' ) ) {
			$notified = wpas_email_notify( $this->post_id, 'agent_reply' );
		}

		/* The new reply is from a user */
		else {
			$notified = wpas_email_notify( $this->post_id, 'client_reply' );
		}

		return $notified;

	}

}