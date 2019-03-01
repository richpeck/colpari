<?php
/**
 * Post Functions.
 *
 * @package   Awesome Support E-Mail Support/Admin
 * @author    Julien Liabeuf <julien@liabeuf.fr>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_filter( 'wpas_email_notifications_cases', 'ases_register_notification_case', 10, 1 );
/**
 * Register the rejected reply case to the e-mail notification class
 *
 * @since  0.2.5
 *
 * @param array $cases Existing notification cases
 *
 * @return array
 */
function ases_register_notification_case( $cases ) {

	if ( ! in_array( 'email_reply_rejected', $cases ) ) {
		$cases[] = 'email_reply_rejected';
	}
	
	if ( ! in_array( 'email_reply_lock_rejected', $cases ) ) {
		$cases[] = 'email_reply_lock_rejected';
	}

	return $cases;

}

add_filter( 'wpas_email_notifications_case_is_active', 'ases_activate_rejection_notification_case', 10, 2 );
/**
 * Returns email notification template status
 *
 * @since 0.2.5
 *
 * @param bool   $active Notification activation status
 * @param string $case   Case of the notification being sent out
 *
 * @return bool
 */
function ases_activate_rejection_notification_case( $active, $case ) {

	if ( 'email_reply_rejected' === $case || 'email_reply_lock_rejected' === $case ) {
		$active = wpas_get_option( "enable_{$case}", true );
	}

	return $active;

}

add_filter( 'wpas_email_notifications_cases_active_option', 'ases_rejection_notification_active_option_name', 10, 1 );
/**
 * Set the rejection notification active option name
 *
 * @since 0.2.5
 *
 * @param array $cases Array of cases with their "enable" option name
 *
 * @return array
 */
function ases_rejection_notification_active_option_name( $cases ) {
	$cases['email_reply_rejected'] = 'enable_email_reply_rejected';
	$cases['email_reply_lock_rejected'] = 'enable_email_reply_lock_rejected';

	return $cases;
}

add_filter( 'wpas_email_notifications_notify_user', 'ases_set_rejection_notification_recipient', 10, 3 );
/**
 * Set the recipient for the rejection e-mail notification
 *
 * @since 0.2.5
 *
 * @param null|WP_User $user      A user object or null if no case previously matched
 * @param string       $case      Case of the notification being sent out
 * @param int          $ticket_id ID of the ticket being processed
 *
 * @return WP_User
 */
function ases_set_rejection_notification_recipient( $user, $case, $ticket_id ) {

	if ( 'email_reply_rejected' !== $case && 'email_reply_lock_rejected' !== $case ) {
		return $user;
	}

	$ticket = get_post( $ticket_id );

	return get_user_by( 'id', $ticket->post_author );

}

add_filter( 'wpas_email_notifications_pre_fetch_content', 'ases_get_rejection_notification_content', 10, 3 );
/**
 * Get the rejection e-mail notification content
 *
 * @since 0.2.5
 *
 * @param string $value     Notification content
 * @param int    $ticket_id ID of the ticket being processed
 * @param string $case      Case of the notification being sent
 *
 * @return string
 */
function ases_get_rejection_notification_content( $value, $ticket_id, $case ) {

	if ( 'email_reply_rejected' === $case ) {
		$value = wpas_get_option( 'content_email_reply_rejected' );
	} elseif ( 'email_reply_lock_rejected' === $case ) {
		$value = wpas_get_option( 'content_email_reply_lock_rejected' );
	}

	return $value;

}

add_filter( 'wpas_email_notifications_pre_fetch_subject', 'ases_get_rejection_notification_subject', 10, 3 );
/**
 * Get the rejection e-mail notification subject
 *
 * @since 0.2.5
 *
 * @param string $value     Notification subject
 * @param int    $ticket_id ID of the ticket being processed
 * @param string $case      Case of the notification being sent
 *
 * @return string
 */
function ases_get_rejection_notification_subject( $value, $ticket_id, $case ) {

	if ( 'email_reply_rejected' === $case ) {
		$value = wpas_get_option( 'subject_email_reply_rejected' );
	} elseif ( 'email_reply_lock_rejected' === $case ) {
		$value = wpas_get_option( 'subject_email_reply_lock_rejected' );
	}

	return $value;

}