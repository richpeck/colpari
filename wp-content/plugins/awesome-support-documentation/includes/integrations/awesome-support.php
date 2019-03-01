<?php
/**
 * @package   Awesome Support Documentation/Integrations
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      http://getawesomesupport.com
 * @copyright 2014-2017 Awesome Support
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_filter( 'wpas_ticket_reply_controls', 'asdoc_ticket_reply_controls_doc', 10, 3 );
/**
 * Add create documentation link in the ticket replies controls
 *
 * @since 2.0.3
 *
 * @param array   $controls  List of existing controls
 * @param int     $ticket_id ID of the ticket current reply belongs to
 * @param WP_Post $reply     Reply post object
 *
 * @return array
 */
function asdoc_ticket_reply_controls_doc( $controls, $ticket_id, $reply ) {

	// Only allow Documentation agent answers to be set as DOC answer
	if ( 0 !== $ticket_id && user_can( $reply->post_author, 'edit_ticket' ) && is_object( $reply ) && is_a( $reply, 'WP_Post' ) ) {

		$link = add_query_arg( array(
			'post_type'   => 'documentation',
			'ticket_id'   => $ticket_id,
			'reply_id'    => $reply->ID,
			'asdoc_do'    => 'create_doc_wpas',
			'_create_doc' => wp_create_nonce( 'create_doc_ticket' )
		), admin_url( 'edit.php' ) );

		$controls['asdoc_create_doc'] = sprintf( '<a href="%1$s" title="%2$s">%3$s</a>', esc_url( $link ), esc_html__( 'Create a new Documentation based on this reply', 'wpas-documentation' ), esc_html__( 'Create Documentation', 'wpas-documentation' ) );

	}

	return $controls;

}

add_action( 'asdoc_do_create_doc_wpas', 'asdoc_wpas_create_doc' );
/**
 * Create the new documentation item
 *
 * @since 1.0
 *
 * @param $args
 *
 * @return void
 */
function asdoc_wpas_create_doc( $args ) {

	if ( ! isset( $args['_create_doc'] ) || ! wp_verify_nonce( $args['_create_doc'], 'create_doc_ticket' ) ) {
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

	$doc_title   = $ticket->post_title;
	$doc_content = $reply->post_content;

	$args = array(
		'post_title'   => $doc_title,
		'post_content' => $doc_content,
		'post_status'  => 'publish',
		'post_author'  => get_current_user_id(),
		'post_type'    => 'documentation',
	);

	$doc_id = asdoc_insert_doc( $args );

	if ( 0 === $doc_id ) {
		exit;
	}

	$redirect = add_query_arg( array( 'post' => $doc_id, 'action' => 'edit' ), admin_url( 'post.php' ) );
	wp_redirect( wp_sanitize_redirect( $redirect ) );
	exit;

}

/**
 * Add a new button after reply submission form to create documentation
 *
 * If used, this button will post a new reply to the ticket and use
 * the freshly posted reply as the documentation answer.
 *
 * @since 1.0
 *
 * @param $post_id
 *
 * @return void
 */
function asdoc_reply_doc_button( $post_id ) {
	printf( '<button type="submit" name="wpas_do" class="button-secondary" value="asdoc_reply_doc" title="%s">%s</button>', esc_html__( 'Post the reply and create a documentation item with this reply as the answer', 'wpas-documentation' ), esc_html__( 'Reply &amp; Documentation', 'wpas-documentation' ) );
}

add_action( 'wpas_post_reply_admin_after', 'asdoc_reply_and_doc', 10, 3 );
/**
 * Add a documentation item just after a reply is posted
 *
 * @since 1.0
 *
 * @param int          $post_id The ticket ID
 * @param array        $data    Reply data inserted in the database
 * @param int|WP_Error $reply
 *
 * @return bool|int DOC ID if inserted successfully, false otherwise
 */
function asdoc_reply_and_doc( $post_id, $data, $reply ) {

	if ( ! isset( $_POST['wpas_do'] ) || 'asdoc_reply_doc' !== $_POST['wpas_do'] ) {
		return false;
	}

	if ( is_wp_error( $reply ) ) {
		return false;
	}

	$ticket = get_post( $post_id );

	$args = array(
		'post_type'    => 'documentation',
		'post_title'   => $ticket->post_title,
		'post_content' => $data['post_content'],
		'post_status'  => 'publish',
		'post_author'  => get_current_user_id(),
	);

	$doc_id = asdoc_insert_doc( $args );

	if ( 0 !== $doc_id ) {
		wpas_add_notification( 'asdoc_new_doc_added', sprintf( __( 'The new documentation item has been added. <a href="%s">View FAQ</a>', 'wpas-documentation' ), esc_url( add_query_arg( array( 'post' => $doc_id, 'action' => 'edit' ), admin_url( 'post.php' ) ) ) ), 'asdoc' );
	}

	// Set the wpas_do post var to close the ticket if setup this way
	if ( true === (bool) asdoc_get_option( 'reply_doc_close', false ) ) {
		$_POST['wpas_do'] = 'reply_close';
	}

	return $doc_id;

}

add_filter( 'wpas_locate_template', 'asdoc_locate_template', 100, 2 );
/**
 * Filters the template path to allow loading templates from the AS Documentation themes directory.
 *
 * @since 1.1
 *
 * @param string $template Template path (if found).
 * @param string $name     Name of the template to locate.
 *
 * @return string (Maybe) modified template path.
 */
function asdoc_locate_template( $template, $name ) {

	$templates = array(
		'asdoc-accordion',
		'asdoc-category-boxes',
		'asdoc-category-lists',
		'asdoc-columns',
		'asdoc-knowledge-base'
	);

	if ( in_array( $name, $templates, true ) ) {
		if ( false !== strpos( $template, WPAS_PATH . 'themes' ) ) {
			$template = WPAS_DOC_PATH . "themes/default/doc/partials/{$name}.php";
		}
	}

	return $template;
}
