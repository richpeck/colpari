<?php
/**
 * Slack Notifications
 *
 * @package   Awesome Support Notifications/Slack
 * @author    Julien Liabeuf <julien@liabeuf.fr>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class WPAS_Slack {

	/**
	 * Slack Webhook URL.
	 *
	 * @since  0.1.0
	 * @var string
	 */
	protected $webhook = '';

	/**
	 * Notification data.
	 *
	 * @since  0.1.0
	 * @var    array
	 */
	protected $data;

	public function __construct( $webhook = '' ) {

		$this->webhook = $webhook;

		if ( empty( $this->webhook ) ) {

			$this->webhook = wpas_get_option( 'notification_slack_webhook', false );

			if ( false === $this->webhook ) {
				return false;
			}

		}

		$custom_icon = wpas_get_option( 'notification_slack_icon', '' );

		/**
		 * Get icon URL if we have an attachment ID
		 */
		if ( is_numeric( $custom_icon ) ) {
			$custom_icon_src = wp_get_attachment_image_src( $custom_icon );
			$custom_icon     = $custom_icon_src[0];
		}

		$this->bot_name  = wpas_get_option( 'notification_slack_name', get_bloginfo( 'name' ) );
		$this->bot_emoji = wpas_get_option( 'notification_slack_emoji', ':zap:' );
		$this->bot_icon  = $custom_icon;

	}

	/**
	 * Prepare the payload for Slack.
	 *
	 * @since  0.1.0
	 * @return array An array containing the notification payload
	 */
	public function payload() {

		$pretext = '';

		switch( $this->data['context'] ) {
			case 'new_ticket':
				$pretext = sprintf( __( '<%s|%s> created a new ticket <%s|#%s>', 'wpas-notifications' ), $this->data['creator']['link'], $this->data['creator']['name'], $this->data['ticket_link_be'], $this->data['id'] );
				break;

			case 'new_reply_agent':
				$pretext = sprintf( __( '<%s|%s> replied to ticket <%s|#%s>', 'wpas-notifications' ), $this->data['support_staff']['link'], $this->data['support_staff']['name'], $this->data['ticket_link_be'], $this->data['id'] );
				break;

			case 'new_reply_client':
				$pretext = sprintf( __( '<%s|%s> replied to ticket <%s|#%s>', 'wpas-notifications' ), $this->data['creator']['link'], $this->data['creator']['name'], $this->data['ticket_link_be'], $this->data['id'] );
				break;

			case 'ticket_closed':
				global $current_user;
				$pretext = sprintf( __( 'Ticket *%s* (<%s|#%s>) was closed by %s (%s)', 'wpas-notifications' ), $this->data['title'], $this->data['ticket_link_be'], $this->data['id'], $current_user->data->display_name, wpas_get_user_nice_role( $current_user->roles[0] ) );
				break;

			case 'ticket_reopened':
				global $current_user;
				$pretext = sprintf( __( 'Ticket *%s* (<%s|#%s>) was re-opened by %s (%s)', 'wpas-notifications' ), $this->data['title'], $this->data['ticket_link_be'], $this->data['id'], $current_user->data->display_name, wpas_get_user_nice_role( $current_user->roles[0] ) );
				break;
			default :
				/* Status is dynamic so can't use normal 'case' to check - instead using this default section to do so. */
				if( as_in_is_status_action( $this->data['context'] ) ) {
					$pretext = sprintf( __( 'Status for ticket *%s* (<%s|#%s>) has been changed to *%s*', 'wpas-notifications' ), $this->data['title'], $this->data['ticket_link_be'], $this->data['id'], $this->data['status']);
				}
				break;
		}

		/* Basic payload data */
		$payload = array(
			'username'   => $this->bot_name,
		);

		/* Add the Emoji or custom icon */
		if ( ! empty( $this->bot_icon ) ) {
			$payload['icon_url'] = $this->bot_icon;
		} else {
			$payload['icon_emoji'] = $this->bot_emoji;
		}

		/* Get the assigned staff. This won't change based on who answered */
		$assigned            = get_post_meta( intval( $this->data['id'] ), '_wpas_assignee', true );
		$assigned_staff      = get_user_by( 'id', $assigned );
		$assigned_staff_link = add_query_arg( 'user_id', $assigned, admin_url( 'user-edit.php' ) );

		/* Format content */
		$content = str_replace( '<p>', '', $this->data['content'] );
		$content = str_replace( '</p>', "\r\n", $content );

		if ( version_compare( phpversion(), '5.3', '>=' ) ) {
			if ( class_exists( 'Markdownify\ConverterExtra' ) ) {
				$converter = new Markdownify\ConverterExtra;
				$content   = $converter->parseString( $content );
			}
		}

		/* Custom payload data based on the notification context */
		if ( in_array( $this->data['context'], array( 'ticket_closed', 'ticket_reopened' ) ) ) {
			$payload['text'] = $pretext;
		} else {
			$payload['fallback'] = $this->data['unformatted'];
			$payload['pretext']  = $pretext;
			$payload['color']    = '#3498db';
			$payload['fields']   = array(
				array(
					'title' => __( 'Creator', 'wpas-notifications' ),
					'value' => "<{$this->data['creator']['link']}|{$this->data['creator']['name']}> ({$this->data['creator']['role']})",
					'short' => true
				),
				array(
					'title' => __( 'Status', 'wpas-notifications' ),
					'value' => $this->data['status'],
					'short' => true
				),
				array(
					'title' => __( 'Title', 'wpas-notifications' ),
					'value' => $this->data['title'],
					'short' => true
				),
				array(
					'title' => __( 'Support Staff', 'wpas-notifications' ),
					'value' => "<{$assigned_staff_link}|{$assigned_staff->display_name}>",
					'short' => true
				),
				array(
					'title' => __( 'Content', 'wpas-notifications' ),
					'value' => $content,
					'short' => false
				),
			);
		}

		return apply_filters( 'wpas_notification_slack_payload', $payload );

	}

	public function build_query() {
		$payload = json_encode( $this->payload() );
		return "$payload";
	}

	public function notify( $data ) {

		global $wp_version;

		$this->data = $data;

		$args = array(
			'method'      => 'POST',
			'timeout'     => 30,
			'redirection' => 5,
			'httpversion' => '1.0',
			'user-agent'  => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
			'body'        => $this->build_query()
		);

		$response = wp_remote_post( $this->webhook, $args );

		return $response;
	}

}