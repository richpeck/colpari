<?php

/**
 * @package   Awesome Support: Satisfaction Survey
 * @author    Robert W. Kramer III <support@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016. Awesome Support
 */

global $post;

echo '<div>';

if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {

	if ( isset( $_GET['wpas-do'] ) && $_GET['wpas-do'] == 'admin_resend_survey_email' ) {
		$this->ticket_closed_schedule_invite( $post->ID );

	} elseif ( isset( $_GET['wpas-do'] ) && $_GET['wpas-do'] == 'admin_resend_survey_email_now' ) {
		$this->send_event_reminder_email( $post->ID );

	} elseif ( isset( $_GET['wpas-do'] ) && $_GET['wpas-do'] == 'admin_cancel_survey_email' ) {
		$this->cancel_event_reminder_email( $post->ID );
	}

}


$thash              = get_post_meta( $post->ID, '_wpasss_hash', true );
$comment            = get_post_meta( $post->ID, '_wpasss_comment', true );
$rating             = get_post_meta( $post->ID, '_wpasss_rating', true );
$timestamp_to_event = wp_next_scheduled( 'wpas_ss_send_reminder_email', array( (int) $post->ID ) );
$ticket_closed      = wpas_get_ticket_status( $post->ID ) == 'closed';
$unsatisfied_reason = get_post_meta( $post->ID, '_wpasss_reason', true );

$show_reschedule_button = false;
$show_cancel_button     = false;
$show_send_now_button   = false;


// Ticket closed.
if ( $ticket_closed ) {

	// Cron scheduled to send survey invitation
	if ( $timestamp_to_event ) {

		echo '<div id="cron-countdown" data-seconds="' . (string) ( $timestamp_to_event - time() ) . '"></div>';

		echo '<div class="wrapper_countdown">';
		echo 'Sending survey email to client in:';
		echo '<div class="wpas-row wpas-ss-countdown wpasss">';
		echo '<div class="wpas-col countdown-col"><strong id="cron-days">00</strong>Days</div>';
		echo '<div class="wpas-col countdown-col"><strong id="cron-hours">00</strong>Hrs</div>';
		echo '<div class="wpas-col countdown-col"><strong id="cron-minutes">00</strong>Mins</div>';
		echo '<div class="wpas-col countdown-col"><strong id="cron-seconds">00</strong>Secs</div>';
		echo '</div>';
		echo '</div>';

		$show_reschedule_button = true;
		$show_send_now_button   = true;
		$show_cancel_button     = true;

	} elseif ( ! empty( $thash ) && empty( $rating ) ) {
		echo __( 'Client submission pending.', 'wpas-ss' ) . '<br/>';

		$show_reschedule_button = true;
		$show_send_now_button   = true;
	}

	/**
	 * Survey invitation sent and responded to
	 */
	elseif ( $thash === '' && is_numeric( $rating ) ) {
		echo $this->render_survey_field( 'disabled', $post->ID );

		echo '<div class="wpas-row wpas-up-stats wpasss">';

		// Satisfaction Rating
		echo '<div class="wpas-col rating">';
		echo '<strong>' . round( $rating, 0 ) . '</strong>';
		echo __( 'Rating', 'wpas-ss' );
		echo '</div>';


		// Comment
		$disabled = $comment !== '' ? '' : ' disabled';
		$title    = $comment;
		$label    = __( 'Comment', 'wpas-ss' );

		echo '<div class="wpas-col comment' . $disabled . '">';
		echo '<a title="' . $title . '"' . $disabled . '>';
		echo '<span> </span><br/>';
		echo $label . '</a>';
		echo '</div>';


		// Unsatisifed Reason
		$disabled = $unsatisfied_reason ? '' : ' disabled';
		$title    = $unsatisfied_reason ? $unsatisfied_reason : '';
		$label    = __( 'Reason', 'wpas-ss' );

		echo '<div class="wpas-col reason' . $disabled . '">';
		echo '<a title="' . $title . '"' . $disabled . '>';
		echo '<span> </span><br/>';
		echo $label . '</a>';
		echo '</div>';

		echo '</div>';

	} else {
		echo __( 'Not scheduled (or submitted?).', 'wpas-ss' ) . '<br/>';

		$show_reschedule_button = true;
		$show_send_now_button   = true;
	}

	if ( $show_reschedule_button ) {
		echo '<br/>'
		     . '<a id="ss-reschedule-btn" href="' . admin_url()
		     . 'post.php?action=edit&post=' . $post->ID
		     . '&wpas-do=admin_resend_survey_email&wpas-do-nonce='
		     . wp_create_nonce( 'admin_resend_survey_email' . $post->ID )
		     . '" class="button button-primary button-large">'
		     . __( 'Reschedule', 'wpas-ss' ) . '</a>';
	}
	if ( $show_cancel_button ) {
		echo ' '
		     . '<a id="ss-cancel-btn" href="' . admin_url()
		     . 'post.php?action=edit&post=' . $post->ID
		     . '&wpas-do=admin_cancel_survey_email&wpas-do-nonce='
		     . wp_create_nonce( 'admin_cancel_survey_email' . $post->ID )
		     . '" class="button button-primary button-large">'
		     . __( 'Cancel', 'wpas-ss' ) . '</a>';
	}
	if ( $show_send_now_button ) {
		echo ' '
		     . '<a id="ss-send-now-btn" href="' . admin_url()
		     . 'post.php?action=edit&post=' . $post->ID
		     . '&wpas-do=admin_resend_survey_email_now&wpas-do-nonce='
		     . wp_create_nonce( 'admin_resend_survey_email_now' . $post->ID )
		     . '" class="button button-primary button-large">'
		     . __( 'Send Now', 'wpas-ss' ) . '</a>';
	}
} // Ticket still open.
else {
	echo __( 'Ticket still open.', 'wpas-ss' ) . '<br/>';
}

echo '<div class="wp_cron_notice">';
echo '<a title="' . __( 'If the email is not received by the client then your installation most likely has issues with wp-cron. Troubleshooting WordPress wp-cron issues is outside our scope of support.', 'wpas-ss' ) . '">';
echo __( 'Having e-mail issues?', 'wpas-ss' ) . '</a>';
echo '</div>';


echo '</div>';
