<?php
/**
 * Notifications
 *
 * @package   Awesome Support Notifications
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016 Awesome Support
 */

class WPAS_Notify {

	/**
	 * ID of the post to notify about.
	 *
	 * @since  0.1.0
	 * @var    integer
	 */
	protected $post_id = null;

	/**
	 * ID of the ticket concerned.
	 *
	 * The ticket ID might or might not be the same as $post_id.
	 * It will be different if the $post_id is a ticket reply.
	 *
	 * @since  0.1.0
	 * @var    null
	 */
	protected $ticket_id = null;

	/**
	 * Array containing the notification data.
	 *
	 * @var array
	 */
	protected $notification = array();

	/**
	 * List of supported services.
	 *
	 * @since  0.1.0
	 * @var    array
	 */
	protected $services;

	/**
	 * Notification context.
	 *
	 * An optional context can be passed to this class.
	 * If no context is passed we will deduct the current context
	 * for the notification.
	 *
	 * @since  0.1.0
	 * @var    string
	 */
	protected $context;

	public function __construct( $post_id, $context = '' ) {

		$this->post_id = $post_id;
		$this->context = $context;

		/* Initialize the class vars and run a few checks. */
		$this->init();

		/* Make sure that the post is a ticket or a ticket reply. */
		if ( false === $this->is_support() ) {
			return false;
		}

	}

	public function init() {

		$this->post      = get_post( $this->post_id );
		$this->ticket_id = 0 == $this->post->post_parent ? $this->post_id : intval( $this->post->post_parent );
		$this->ticket    = 0 == $this->post->post_parent ? $this->post : get_post( $this->ticket_id );
		$this->services  = apply_filters( 'wpas_notification_services', array(
				'slack',
				'pushover',
				'pushbullet',
			)
		);

		$this->get_data();

	}

	/**
	 * Check if the post is a ticket or a reply.
	 *
	 * @since  0.1.0
	 * @return boolean True if the post belongs to the support, false otherwise
	 */
	public function is_support() {

		if ( !in_array( $this->post->post_type, array( 'ticket', 'ticket_reply' ) ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Get the notification data.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function get_data() {

		global $current_user;

		$staff_id     = user_can( $current_user->ID, 'edit_ticket' ) ? $current_user->ID : get_post_meta( $this->ticket_id, '_wpas_assignee', true );
		$creator      = get_user_by( 'id', $this->ticket->post_author );
		$staff        = get_user_by( 'id', intval( $staff_id ) );
		$link_creator = add_query_arg( array( 'post_type' => 'ticket', 'author' => $this->post->post_author ), admin_url( 'edit.php' ) );
		$link_staff   = add_query_arg( 'user_id', $staff_id, admin_url( 'user-edit.php' ) );
		$status       = function_exists( 'wpas_get_ticket_status_state' ) ? wpas_get_ticket_status_state( $this->ticket_id ) : ucwords( wpas_get_ticket_status( $this->ticket_id ) );

		$this->notification = array(
			'id'             => $this->ticket_id,
			'ticket_link_be' => add_query_arg( array( 'post' => $this->ticket_id, 'action' => 'edit' ), admin_url( 'post.php' ) ),
			'ticket_link_fe' => get_permalink( $this->ticket_id ),
			'creator'        => array(
				'name' => $creator->data->display_name,
				'link' => $link_creator,
				'role' => wpas_get_user_nice_role( $creator->roles[0] )
			),
			'support_staff'  => array(
				'name' => $staff->data->display_name,
				'link' => $link_staff,
				'role' => wpas_get_user_nice_role( $staff->roles[0] )
			),
			'title'          => wp_strip_all_tags( $this->ticket->post_title ),
			'content'        => apply_filters( 'the_content', $this->post->post_content ),
			'unformatted'    => wp_strip_all_tags( $this->post->post_content, true ),
			'excerpt'        => $this->get_excerpt( $this->post->post_content, 100, ' [...]' ),
			'status'         => $status,
			'context'        => $this->context
		);

		/* Add the notification context if it hasn't been explicitly given. */
		if ( empty( $this->notification['context'] ) ) {

			/* This is a new ticket */
			if ( $this->post_id === $this->ticket_id ) {
				$this->notification['context'] = 'new_ticket';
			}

			/* This is a new reply */
			else {

				$post        = get_post( $this->post_id );
				$post_author = $post->post_author;

				/* New reply from an agent */
				if ( user_can( $post_author, 'edit_ticket' ) ) {
					$this->notification['context'] = 'new_reply_agent';
				}

				/* New reply from a client */
				else {
					$this->notification['context'] = 'new_reply_client';
				}

			}

		}

	}

	/**
	 * Get an excerpt of the post content.
	 *
	 * @since  0.1.0
	 * @param  string  $string  String to truncate
	 * @param  integer $limit   Maximum number of words in the string
	 * @param  string  $suffix  Suffix to add at the end of the truncated string
	 * @return string           Shortened string
	 */
	public function get_excerpt( $string, $limit = 100, $suffix = '' ) {

		$parts       = preg_split( '/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE );
		$parts_count = count( $parts );
		$length      = 0;
		$last_part   = 0;

		for (; $last_part < $parts_count; ++$last_part) {
			$length += strlen($parts[$last_part]);
			if ( $length > $limit ) { break; }
		}

		return implode( array_slice( $parts, 0, $last_part ) ) . $suffix;
	}

	/**
	 * Instanciate the service to notify.
	 *
	 * @since  0.1.0
	 * @param  string $service Name of the service to notify
	 * @return mixed           True on success or WP_Error on failure
	 */
	public function notify( $service ) {

		if ( !in_array( $service, $this->services ) ) {
			return new WP_Error( 'undefined_service', sprintf( __( 'The service %s is not supported', 'as-instant-notifications' ), "<strong>$service</strong>" ) );
		}

		if ( is_null( $this->post ) || is_null( $this->ticket_id ) ) {
			return new WP_Error( 'unknown_ticket', __( 'We were unable to identify the ticket to notify about', 'as-instant-notifications' ) );
		}

		/**
		 * Make sure that the user wants to notify in the current context.
		 */
		$context  = $this->notification['context'];
		if( as_in_is_status_action( $context ) ) {
			$contexts = maybe_unserialize( wpas_get_option( 'notification_status_notify' ) );
		} else {
			$contexts = maybe_unserialize( wpas_get_option( 'notification_notify' ) );
		}

		if ( !in_array( $context, $contexts ) ) {
			return false;
		}

		switch ( $service ) {

			case 'slack':

				$slack = new WPAS_Slack();
				$slack->notify( $this->notification );

				break;
			case 'pushbullet':

				$agent_id   = get_post_meta( $this->ticket_id, '_wpas_assignee', true );
				$pushbullet = new WPAS_Pushbullet( $agent_id );
				$pushbullet->notify( $this->notification, $agent_id );

				break;
			case 'email':
				$email = new WPAS_IN_Email( $this->notification, $this->post_id );
				$email->notify();
				
				break;
		}

		return true;

	}

}
