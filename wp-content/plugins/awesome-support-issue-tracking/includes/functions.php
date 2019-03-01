<?php

/**
 * Check ajax nonce
 * 
 * @param string $name
 * @param string $key
 * @param boolean $die
 * 
 * @return boolean
 */
function wpas_it_ajax_nonce_check( $name, $key = 'security', $die = true ) {
	
	if( !check_ajax_referer( $name, $key, false ) || !current_user_can( 'edit_ticket' ) ) {
		
		if( $die ) {
			wp_send_json_error( array( 'message' => __( "You don't have access to perform this action.", 'wpas_it' ) ) );
			die();
		}
		
		return false;
	}
	
	return true;
}


add_action( 'wp_ajax_wpas_get_select2_issues', 'wpas_get_select2_issues' );

/**
 * Return issues via ajax request
 */
function wpas_get_select2_issues() {
	
	
	$results = array();
		
	
	if( !isset( $_POST['q'] ) || empty( $_POST['q']  ) || ! wpas_it_ajax_nonce_check( 'wpas-get-issues' ) ) {
		wp_send_json( array() );
		die();
	}
	
	$keyword = sanitize_text_field( $_POST['q'] );
	
	
	require( WPAS_PATH . 'includes/admin/functions-post.php' );
	
	
	$args = array(
		'post_type'              => 'wpas_issue_tracking',
		'post_status'            => 'any',
		'posts_per_page'         => -1
	);
	
	
	$args['s'] = $keyword;
	
	$query = new WP_Query( $args );
	
	$tickets_result_1 = $query->posts ? $query->posts : array();
	
	$tickets_result_2 = array();
	
	
	if( is_numeric( $keyword ) ) {
		unset( $args['s'] );
		$args['post__in'] = array( $keyword );
		
		$query2 = new WP_Query( $args );
		
		$tickets_result_2 = $query2->posts ? $query2->posts : array();
	}
	
	$issues = array_merge( $tickets_result_1, $tickets_result_2 );
	
	
	if ( count( $issues ) > 0 ) {
		
		
		foreach ( $issues as $issue ) {

			$results[] = array(
			    'id'     => $issue->ID,
			    'text' => $issue->post_title
			);
			
		}
	}
	
	
	
	
	echo json_encode( $results );
	die();
}



add_action( 'wpas_it_start_ticket_listing' , 'wpas_it_ticket_add_custom_fields',    11, 0 );
add_action( 'wpas_it_end_ticket_listing' ,   'wpas_it_ticket_remove_custom_fields', 11, 0 );

/**
 * Add custom fields relative to ticket listing
 */
function wpas_it_ticket_add_custom_fields( ) {
		
	add_filter( 'add_ticket_column_custom_fields', '__return_true', 13, 1 );
		
	$fields = WPAS()->custom_fields->get_custom_fields();
	
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		require_once( WPAS_PATH . 'includes/admin/functions-misc.php' );
		require_once( WPAS_PATH . 'includes/admin/class-admin-tickets-list.php' );
	}
	
	WPAS_Tickets_List::get_instance()->add_custom_fields( $fields );
		
}

/**
 * Remove custom fields relative to ticket listing
 */
function wpas_it_ticket_remove_custom_fields() {
	
	remove_filter( 'add_ticket_column_custom_fields', '__return_true', 13 );
	
	WPAS()->custom_fields->remove_field( 'id' );
	WPAS()->custom_fields->remove_field( 'author' );
	WPAS()->custom_fields->remove_field( 'wpas-activity' );
}

/**
 * Return a saved setting
 * 
 * @param string $option
 * @param string $default
 * 
 * @return string
 */
function wpas_it_get_option( $option, $default = "" ) {
		$options = maybe_unserialize( get_option( 'wpasit_options', array() ) );

		/* Return option value if exists */
		$value = isset( $options[ $option ] ) ? $options[ $option ] : $default;

		return apply_filters( 'wpasit_option_' . $option, $value );
}

/**
 * Return all issue comment types
 * 
 * @return array
 */
function wpas_it_comment_types() {
	
	return apply_filters( 'wpas_it_comment_types', array(
		'private'		=> 'Private',
		'semi_private'	=> 'Semi-Private',
		'regular'		=> 'Regular'
		) );
}



add_action( 'wp_ajax_wpas_it_close_issue', 'wpas_it_action_close_issue' );

/**
 * Handle request to close an issue
 * 
 * @return void
 */
function wpas_it_action_close_issue() {
	
    $issue_id = filter_input( INPUT_POST, 'issue_id', FILTER_SANITIZE_NUMBER_INT );
    $close_tickets = filter_input( INPUT_POST, 'close_tickets', FILTER_SANITIZE_STRING );
	$nonce = filter_input( INPUT_POST, 'security', FILTER_SANITIZE_STRING );
	
	
	if ( ! is_admin() || ! $issue_id || !$nonce || ! wpas_it_ajax_nonce_check( 'it-close-issue' ) ) {
		return;
	}
	
	
	$issue = new WPAS_IT_Issue( $issue_id );
	$issue->close();
	
	// maybe close all tickets linked to an issue
	if( $close_tickets ) {
		$issue_tickets = $issue->getTickets();

		foreach ( $issue_tickets as $ticket ) {

			if( wpas_close_ticket( $ticket->ID, 0, true ) ) {
				do_action( 'wpas_after_close_issue_ticket', $issue_id, $ticket->ID );
			}
			
		}
	}
	
	
	// Read-only redirect
	
	$redirect_to = add_query_arg( array(
		'action'       => 'edit',
		'post'         => $issue_id,
		'wpas-it-message' =>  'issue_closed' . ( $close_tickets ? '_2' : '_1')
	), admin_url( 'post.php' ) );
	
	
	wp_send_json_success( array( 'location' => $redirect_to ) );
	die();
}




add_action( 'wpas_do_admin_trash_issue_comment', 'wpas_it_admin_action_trash_comment' );
/**
 * Trash a comment
 * 
 * @param $data
 *
 * @return void
 */
function wpas_it_admin_action_trash_comment( $data ) {

	if ( ! is_admin() || ! isset( $data['comment_id'] ) ) {
		return;
	}

	$comment_id = (int) $data['comment_id'];
	
	$comment = new WPAS_IT_Comment( $comment_id );
	$comment->trash();

	// Read-only redirect
	$redirect_to = add_query_arg( array(
		'action'       => 'edit',
		'post'         => $data['post'],
	), admin_url( 'post.php' ) );

	wp_redirect( wp_sanitize_redirect( "{$redirect_to}#wpas-post-{$comment_id}" ) );
	exit;

}


add_action( 'admin_notices', 'wpas_it_admin_notices' );
/**
 * Display custom admin notices.
 *
 * Custom admin notices are usually triggered by custom actions.
 *
 * @since  3.0.0
 * @return void
 */
function wpas_it_admin_notices() {

	if ( isset( $_GET['wpas-it-message'] ) ) {

		switch ( $_GET['wpas-it-message'] ) {

			case 'issue_closed_1':
				?>
				<div class="updated">
					<p><?php printf( __( 'The issue #%s has been closed.', 'wpas_it' ), intval( $_GET['post'] ) ); ?></p>
				</div>
				<?php
				break;

			case 'issue_closed_2':
				?>
				<div class="updated">
					<p><?php printf( __( 'The issue #%s has been closed including attached tickets.', 'wpas_it' ), intval( $_GET['post'] ) ); ?></p>
				</div>
				<?php
				break;
			case 'it_comment_saved':
				?>
				<div class="updated">
					<p><?php _e( 'Comment successfully added.', 'wpas_it' ); ?></p>
				</div>
				<?php
				
				break;

		}

	}
}


/**
 * Return default issue statuses
 * 
 * @return array
 */
function wpas_it_default_issue_statuses() {
	return apply_filters( 'wpas_it_default_issue_statuses' , array( 
		__( 'Open', 'wpas_it' ) , 
		__( 'On Hold', 'wpas_it' ) , 
		__( 'Closed' , 'wpas_it' ) , 
		) );
}

/**
 * Return default issue priorities
 * 
 * @return array
 */
function wpas_it_default_issue_priorities() {
	
	return apply_filters( 'wpas_it_default_issue_priorities' , array( 
		__( 'Low',		'wpas_it' ) , 
		__( 'Medium',	'wpas_it' ) , 
		__( 'High',		'wpas_it' ) , 
		__( 'Urgent',	'wpas_it' ) , 
		) );
}

/**
 * Return default comment statuses
 * 
 * @return array
 */
function wpas_it_default_comment_statuses() {
	return apply_filters( 'wpas_it_default_comment_statuses' , array( 
		
		array(
			'name' => __( 'Standard', 'wpas_it' ),
			'color' => '#006400'
		)
		
		) );
}

add_action( 'init', 'wpas_it_init_first_activated', 30, 0 );

/**
 * Configure add-on once activated
 */
function wpas_it_init_first_activated() {
	
	$configured = get_option( 'wpas_it_configured' );
	
	if( 'no' === $configured ) {
		
		// Adding default issue statuses
		$default_issue_statuses = wpas_it_default_issue_statuses();
		foreach ( $default_issue_statuses as $status ) {
			wp_insert_term( $status, 'wpas_it_status' );
		}
		
		
		// Adding default issue priorities
		$default_issue_priorities = wpas_it_default_issue_priorities();
		foreach ( $default_issue_priorities as $priority ) {
			wp_insert_term( $priority, 'wpas_it_priority' );
		}
		
		
		// Adding default issue comment statuses
		$default_comment_statuses = wpas_it_default_comment_statuses();
		foreach ( $default_comment_statuses as $status ) {
			$term = wp_insert_term( $status['name'], 'wpas_it_cmt_status' );
			
			if( !is_wp_error( $term ) && isset( $status['color'] ) ) {
				update_term_meta( $term['term_id'], 'color', $status['color'] );
			}
		}
		
		
		update_option( 'wpas_it_configured', 'yes' );
	}
}

/**
 * Return term by a term field
 * 
 * @param string $field
 * @param string $value
 * @param string $taxonomy
 * 
 * @return array | object
 */
function wpas_it_get_term_by( $field, $value, $taxonomy ) {
	$term = get_term_by( $field, $value, $taxonomy );
		
	return ( is_wp_error( $term ) || !$term ? array() : $term );
}

/**
 * Return display user name
 * 
 * @param int $author
 * @param boolean $return_default
 * @param string $default
 * 
 * @return string
 */
function wpas_it_user_display_name( $author, $return_default = true, $default =  '' ) {

	if ( $author != 0 && $author ) {
		$user_name = get_user_option( 'display_name', $author );
	} elseif( $return_default ) {
		
		if ( !$default ) {
			$default = __( 'Anonymous', 'wpas_it' );
		}
		$user_name = $default;
	}
	
	return $user_name;
}

/**
 * Return All issue states
 * 
 * @return array
 */
function wpas_it_issue_states() {
	
	return apply_filters( 'wpas_it_issue_states', array(
		'open'		=> __( 'Open', 'wpas_it' ),
		'closed'	=> __( 'Closed', 'wpas_it' )
	));
	
}