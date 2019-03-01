<?php
/**
 * @package   Awesome Support CUSTOMFAQ/Integrations
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      http://getawesomesupport.com
 * @copyright 2014 Awesome Support
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_filter( 'wpas_ticket_reply_controls', 'ascustomfaq_ticket_reply_controls_customfaq', 10, 3 );
/**
 * Add create CUSTOMFAQ link in the ticket replies controls
 *
 * @since 1.0
 *
 * @param array   $controls  List of existing controls
 * @param int     $ticket_id ID of the ticket current reply belongs to
 * @param WP_Post $reply     Reply post object
 *
 * @return array
 */
function ascustomfaq_ticket_reply_controls_customfaq( $controls, $ticket_id, $reply ) {

	// Only allow CUSTOMFAQ agent answers to be set as CUSTOMFAQ answer
	if ( 0 !== $ticket_id && user_can( $reply->post_author, 'edit_ticket' ) && is_object( $reply ) && is_a( $reply, 'WP_Post' ) ) {

		$link = add_query_arg( array(
			'post_type'   => ascustomfaq_get_cptname(),
			'ticket_id'   => $ticket_id,
			'reply_id'    => $reply->ID,
			'ascustomfaq_do'    => 'create_customfaq_wpas',
			'_create_customfaq' => wp_create_nonce( 'create_customfaq_ticket' )
		), admin_url( 'edit.php' ) );

		$controls['ascustomfaq_create_customfaq'] = sprintf( '<a href="%1$s" title="%2$s">%3$s</a>', esc_url( $link ), esc_html__( 'Create a new CUSTOMFAQ based on this reply', 'as-customfaq' ), esc_html__( 'Create CUSTOMFAQ', 'as-customfaq' ) );

	}

	return $controls;

}

add_action( 'ascustomfaq_do_create_customfaq_wpas', 'ascustomfaq_wpas_create_customfaq' );
/**
 * Create the new CUSTOMFAQ
 *
 * @since 1.0
 *
 * @param $args
 *
 * @return void
 */
function ascustomfaq_wpas_create_customfaq( $args ) {

	if ( ! isset( $args['_create_customfaq'] ) || ! wp_verify_nonce( $args['_create_customfaq'], 'create_customfaq_ticket' ) ) {
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

	$customfaq_title   = $ticket->post_title;
	$customfaq_content = $reply->post_content;

	$args = array(
		'post_title'   => $customfaq_title,
		'post_content' => $customfaq_content,
		'post_status'  => 'publish',
		'post_author'  => get_current_user_id(),
		'post_type'    => ascustomfaq_get_cptname(),
	);

	$customfaq_id = ascustomfaq_insert_customfaq( $args );

	if ( 0 === $customfaq_id ) {
		exit;
	}

	$redirect = add_query_arg( array( 'post' => $customfaq_id, 'action' => 'edit' ), admin_url( 'post.php' ) );
	wp_redirect( wp_sanitize_redirect( $redirect ) );
	exit;

}

/**
 * Add a new button after reply submission form to create CUSTOMFAQ
 *
 * If used, this button will post a new reply to the ticket and use
 * the freshly posted reply as the CUSTOMFAQ answer.
 *
 * @since 1.0
 *
 * @param $post_id
 *
 * @return void
 */
function ascustomfaq_reply_customfaq_button( $post_id ) {
	printf( '<button type="submit" name="wpas_do" class="button-secondary" value="ascustomfaq_reply_customfaq" title="%s">%s</button>', esc_html__( 'Post the reply and create a CUSTOMFAQ with this reply as the answer', 'as-customfaq' ), esc_html__( 'Reply &amp; CUSTOMFAQ', 'as-customfaq' ) );
}

add_action( 'wpas_post_reply_admin_after', 'ascustomfaq_reply_and_customfaq', 10, 3 );
/**
 * Add a CUSTOMFAQ just after a reply is posted
 *
 * @since 1.0
 *
 * @param int          $post_id The ticket ID
 * @param array        $data    Reply data inserted in the database
 * @param int|WP_Error $reply
 *
 * @return bool|int CUSTOMFAQ ID if inserted successfully, false otherwise
 */
function ascustomfaq_reply_and_customfaq( $post_id, $data, $reply ) {

	if ( ! isset( $_POST['wpas_do'] ) || 'ascustomfaq_reply_customfaq' !== $_POST['wpas_do'] ) {
		return false;
	}

	if ( is_wp_error( $reply ) ) {
		return false;
	}

	$ticket = get_post( $post_id );

	$args = array(
		'post_type'    => ascustomfaq_get_cptname(),
		'post_title'   => $ticket->post_title,
		'post_content' => $data['post_content'],
		'post_status'  => 'publish',
		'post_author'  => get_current_user_id(),
	);

	$customfaq_id = ascustomfaq_insert_customfaq( $args );

	if ( 0 !== $customfaq_id ) {
		wpas_add_notification( 'ascustomfaq_new_customfaq_added', sprintf( __( 'The new CUSTOMFAQ has been added. <a href="%s">View CUSTOMFAQ</a>', 'as-customfaq' ), esc_url( add_query_arg( array( 'post' => $customfaq_id, 'action' => 'edit' ), admin_url( 'post.php' ) ) ) ), 'ascustomfaq' );
	}

	// Set the wpas_do post var to close the ticket if setup this way
	if ( true === (bool) ascustomfaq_get_option( 'reply_customfaq_close', false ) ) {
		$_POST['wpas_do'] = 'reply_close';
	}

	return $customfaq_id;

}

