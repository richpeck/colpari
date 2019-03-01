<?php
/**
 * @package   Awesome Support FAQ/Integrations
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_filter( 'wpas_ticket_reply_controls', 'asfaq_ticket_reply_controls_faq', 10, 3 );
/**
 * Add create FAQ link in the ticket replies controls
 *
 * @since 1.0
 *
 * @param array   $controls  List of existing controls
 * @param int     $ticket_id ID of the ticket current reply belongs to
 * @param WP_Post $reply     Reply post object
 *
 * @return array
 */
function asfaq_ticket_reply_controls_faq( $controls, $ticket_id, $reply ) {

	// Only allow FAQ agent answers to be set as FAQ answer
	if ( 0 !== $ticket_id && user_can( $reply->post_author, 'edit_ticket' ) && is_object( $reply ) && is_a( $reply, 'WP_Post' ) ) {

		$link = add_query_arg( array(
			'post_type'   => 'faq',
			'ticket_id'   => $ticket_id,
			'reply_id'    => $reply->ID,
			'asfaq_do'    => 'create_faq_wpas',
			'_create_faq' => wp_create_nonce( 'create_faq_ticket' )
		), admin_url( 'edit.php' ) );

		$controls['asfaq_create_faq'] = sprintf( '<a href="%1$s" title="%2$s">%3$s</a>', esc_url( $link ), esc_html__( 'Create a new FAQ based on this reply', 'as-faq' ), esc_html__( 'Create FAQ', 'as-faq' ) );

	}

	return $controls;

}

add_action( 'asfaq_do_create_faq_wpas', 'asfaq_wpas_create_faq' );
/**
 * Create the new FAQ
 *
 * @since 1.0
 *
 * @param $args
 *
 * @return void
 */
function asfaq_wpas_create_faq( $args ) {

	if ( ! isset( $args['_create_faq'] ) || ! wp_verify_nonce( $args['_create_faq'], 'create_faq_ticket' ) ) {
		return;
	}

	if ( ! isset( $args['ticket_id'] ) || empty( $args['ticket_id'] ) ) {
		return;
	}

	if ( ! isset( $args['reply_id'] ) || empty( $args['reply_id'] ) ) {
		return;
	}

	$ticket_id = (int) $args['ticket_id'];
	$reply_id  = (int) $args['reply_id'];

	$ticket = get_post( $ticket_id );
	$reply  = get_post( $reply_id );

	if ( 'ticket' !== $ticket->post_type || 'ticket_reply' !== $reply->post_type ) {
		return;
	}

	$faq_title   = $ticket->post_title;
	$faq_content = $reply->post_content;

	$args = array(
		'post_title'   => $faq_title,
		'post_content' => $faq_content,
		'post_status'  => 'publish',
		'post_author'  => get_current_user_id(),
		'post_type'    => 'faq',
	);

	$faq_id = asfaq_insert_faq( $args );

	if ( 0 === $faq_id ) {
		exit;
	}

	$redirect = add_query_arg( array( 'post' => $faq_id, 'action' => 'edit' ), admin_url( 'post.php' ) );
	wp_redirect( wp_sanitize_redirect( $redirect ) );
	exit;

}

/**
 * Add a new button after reply submission form to create FAQ
 *
 * If used, this button will post a new reply to the ticket and use
 * the freshly posted reply as the FAQ answer.
 *
 * @since 1.0
 *
 * @param $post_id
 *
 * @return void
 */
function asfaq_reply_faq_button( $post_id ) {
	printf( '<button type="submit" name="wpas_do" class="button-secondary" value="asfaq_reply_faq" title="%s">%s</button>', esc_html__( 'Post the reply and create a FAQ with this reply as the answer', 'as-faq' ), esc_html__( 'Reply &amp; FAQ', 'as-faq' ) );
}

add_action( 'wpas_post_reply_admin_after', 'asfaq_reply_and_faq', 10, 3 );
/**
 * Add a FAQ just after a reply is posted
 *
 * @since 1.0
 *
 * @param int          $post_id The ticket ID
 * @param array        $data    Reply data inserted in the database
 * @param int|WP_Error $reply
 *
 * @return bool|int FAQ ID if inserted successfully, false otherwise
 */
function asfaq_reply_and_faq( $post_id, $data, $reply ) {

	if ( ! isset( $_POST['wpas_do'] ) || 'asfaq_reply_faq' !== $_POST['wpas_do'] ) {
		return false;
	}

	if ( is_wp_error( $reply ) ) {
		return false;
	}

	$ticket = get_post( $post_id );

	$args = array(
		'post_type'    => 'faq',
		'post_title'   => $ticket->post_title,
		'post_content' => $data['post_content'],
		'post_status'  => 'publish',
		'post_author'  => get_current_user_id(),
	);

	$faq_id = asfaq_insert_faq( $args );

	if ( 0 !== $faq_id ) {
		wpas_add_notification( 'asfaq_new_faq_added', sprintf( __( 'The new FAQ has been added. <a href="%s">View FAQ</a>', 'as-faq' ), esc_url( add_query_arg( array( 'post' => $faq_id, 'action' => 'edit' ), admin_url( 'post.php' ) ) ) ), 'asfaq' );
	}

	// Set the wpas_do post var to close the ticket if setup this way
	if ( true === (bool) asfaq_get_option( 'reply_faq_close', false ) ) {
		$_POST['wpas_do'] = 'reply_close';
	}

	return $faq_id;

}