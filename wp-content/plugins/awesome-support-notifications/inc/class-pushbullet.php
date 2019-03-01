<?php
/**
 * Slack Notifications
 *
 * @package   Awesome Support Notifications/Pushbullet
 * @author    Julien Liabeuf <julien@liabeuf.fr>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class WPAS_Pushbullet {

	/**
	 * Slack Webhook URL.
	 *
	 * @since  0.1.0
	 * @var string
	 */
	protected $endpoint = 'https://api.pushbullet.com/v2/pushes';

	/**
	 * Access token.
	 *
	 * @since  0.1.0
	 * @var    string
	 */
	protected $token;

	/**
	 * Notification data.
	 *
	 * @since  0.1.0
	 * @var    array
	 */
	protected $data;

	/**
	 * ID of the agent to ping.
	 *
	 * @since  0.1.0
	 * @var    integer
	 */
	protected $agent_id;

	public function __construct( $agent_id ) {

		global $current_user;

		$this->agent_id = $agent_id;
		$this->token    = esc_attr( get_the_author_meta( 'wpas_notification_pushbullet_token', $this->agent_id ) );

		if ( empty( $this->token ) ) {
			return false;
		}

	}

	/**
	 * Prepare the payload for Pushbullet.
	 *
	 * @since  0.1.0
	 * @return array An array containing the notification payload
	 */
	public function payload() {

		/* Basic payload data */
		$payload = array(
			'type'  => 'link',
		);

		switch( $this->data['context'] ) {
			case 'new_ticket':
				$payload['title'] = sprintf( __( 'New ticket: %s', 'wpas-notifications' ), $this->data['title'] );
				$payload['body']  = $this->data['excerpt'];
				$payload['url']   = $this->data['ticket_link_be'];
				break;

			case 'new_reply_client':
				$payload['title'] = sprintf( __( 'New reply to %s', 'wpas-notifications' ), $this->data['title'] );
				$payload['body']  = $this->data['excerpt'];
				$payload['url']   = $this->data['ticket_link_be'];
				break;

			case 'ticket_closed':
				$payload['title'] = sprintf( __( 'Ticket %s closed', 'wpas-notifications' ), $this->data['title'] );
				$payload['body']  = __( 'The ticket was closed', 'wpas-notifications' );
				$payload['url']   = $this->data['ticket_link_be'];
				break;

			case 'ticket_reopened':
				$payload['title'] = sprintf( __( 'Ticket %s re-opened', 'wpas-notifications' ), $this->data['title'] );
				$payload['body']  = $this->data['excerpt'];
				$payload['url']   = $this->data['ticket_link_be'];
				break;
			default :
				if( as_in_is_status_action( $this->data['context'] ) ) {
					$payload['title'] = sprintf( __( 'Status changed for ticket %s', 'wpas-notifications' ), $this->data['title'] );
					$payload['body']  = $this->data['excerpt'];
					$payload['url']   = $this->data['ticket_link_be'];
				}
				break;
		}

		return apply_filters( 'wpas_notification_pushbullet_payload', $payload );

	}

	public function build_query() {

		$payload = $this->payload();

		if ( !isset( $payload['url'] ) ) {
			return '';
		}

		$payload = json_encode( $payload );
		return $payload;
	}

	public function notify( $data ) {

		global $wp_version;

		$this->data = $data;
		$query      = $this->build_query();

		if ( empty( $query ) ) {
			return false;
		}

		$args = array(
			'method'      => 'POST',
			'timeout'     => 30,
			'redirection' => 5,
			'httpversion' => '1.0',
			'user-agent'  => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
			'headers'     => array( 'Content-Type' => 'application/json', 'Authorization' => 'Basic ' . base64_encode( $this->token ) ),
			'body'        => $query
		);

		$response = wp_remote_post( $this->endpoint, $args );

		return $response;
	}

}