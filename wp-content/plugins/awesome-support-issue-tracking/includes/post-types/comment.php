<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}


add_action( 'init', 'wpas_it_register_post_type_comment' );

/**
 * Registering new post type for issue comment
 *
 * @since 1.0.0
 * @return void
 */
function wpas_it_register_post_type_comment() {

	$labels = array(
		'menu_name'          => __( 'Issue Comments', 'wpas_it' ),
		'name'               => _x( 'Issue Comments', 'Post Type General Name', 'wpas_it' ),
		'singular_name'      => _x( 'Issue Comment', 'Post Type Singular Name', 'wpas_it' ),
		'add_new_item'       => __( 'Add New Issue Comment', 'wpas_it' ),
		'add_new'            => __( 'New Issue Comment', 'wpas_it' ),
		'not_found'          => __( 'No Issue Comment found', 'wpas_it' ),
		'not_found_in_trash' => __( 'No Issue Comment found in Trash', 'wpas_it' ),
		'parent_item_colon'  => __( 'Parent Issue Comment:', 'wpas_it' ),
		'all_items'          => __( 'Issue Comments', 'wpas_it' ),
		'view_item'          => __( 'View Issue Comment', 'wpas_it' ),
		'edit_item'          => __( 'Edit Issue Comment', 'wpas_it' ),
		'update_item'        => __( 'Update Issue Comments', 'wpas_it' ),
		'search_items'       => __( 'Search Issue Comments', 'wpas_it' ),
	);

	/* Post type capabilities */
	$cap = array(
		'read'					 => 'view_ticket',
		'read_post'				 => 'view_ticket',
		'read_private_posts' 	 => 'view_private_ticket',
		'edit_post'				 => 'edit_ticket',
		'edit_posts'			 => 'edit_ticket',
		'edit_others_posts' 	 => 'edit_other_ticket',
		'edit_private_posts' 	 => 'edit_private_ticket',
		'edit_published_posts' 	 => 'edit_ticket',
		'publish_posts'			 => 'create_ticket',
		'delete_post'			 => 'delete_ticket',
		'delete_posts'			 => 'delete_ticket',
		'delete_private_posts' 	 => 'delete_private_ticket',
		'delete_published_posts' => 'delete_ticket',
		'delete_others_posts' 	 => 'delete_other_ticket'
	);	
	
	$args = array(
		'labels'              => $labels,
		'hierarchical'        => false,
		'description'         => __( 'Issue Comment', 'wpas_it' ),
		'supports'            => array( 'editor' ),
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => false,
		'exclude_from_search' => true
	);

	register_post_type( 'wpas_it_comment', $args );
}



add_action( 'wp_ajax_wpas_it_add_issue_comment', 'wpas_it_add_comment' );

/**
 * Handle add new comment request
 * 
 * @global object $current_user
 */
function wpas_it_add_comment() {
	
	global $current_user;
	
	$error = "";
	
	$types = array_keys ( wpas_it_comment_types() );
	$status   = (int) filter_input( INPUT_POST, 'it_comment_status', FILTER_SANITIZE_NUMBER_INT );
	$type = filter_input( INPUT_POST, 'it_comment_type' );
	$content  = filter_input( INPUT_POST, 'it_comment_content'  );
	$issue_id = filter_input( INPUT_POST, 'it_comment_issue'  );
		
		
	if( ! wpas_it_ajax_nonce_check( 'add-issue-comment', 'it_nonce_wpas_it_add_issue_comment', false ) ) {
			
			$error = __( 'Sorry, you are not allowed to add comment.', 'wpas_it' );
			
	} elseif( !$status ) {
			
			$error = __( 'Please select comment status.', 'wpas_it' );
			
	} elseif( !$type || !in_array( $type, $types ) ) {
			
			$error = __( 'Please select comment type.', 'wpas_it' );
			
	} elseif( empty( trim( $content ) ) ) {
			
			$error = __( 'Content is required.', 'wpas_it' );
			
	}
		
		
	if( $error ) {
		wp_send_json_error( array( 'msg' => $error ) );
	} else {
		
		$user_id = $current_user->ID;
		
		$post_id = wp_insert_post(array (
			'post_type' => 'wpas_it_comment',
			'post_title' => 'Comment',
			'post_content' => $content,
			'post_author'  => $user_id,
			'post_parent'  => $issue_id,
			'post_status' => 'publish',
			'comment_status' => 'closed',   // if you prefer
			'ping_status' => 'closed',      // if you prefer
		));
		
		
		$data = array( 'msg' => __( 'Something went wrong, try again later.', 'wpas_it' ) );
		$success = false;
		
		if( $post_id ) {
			
			wp_set_object_terms( $post_id, (int) $status , 'wpas_it_cmt_status' );
			update_post_meta( $post_id, 'comment_type', $type );
			
			// Recalculate issue comments count
			$issue = new WPAS_IT_Issue( $issue_id ); 
			$issue->calculateCommentsCount();
			
			// Store last comment date in issue
			$comment = get_post( $post_id );
			$last_comment_date = $comment->post_date;
			update_post_meta( $issue_id, 'last_comment_date', $last_comment_date );
			
			
			do_action( 'wpas_it_after_comment_added', $issue_id, $post_id, $type );
			
			
			$data['location'] = add_query_arg( array(
				'action'       => 'edit',
				'post'         => $issue_id,
				'wpas-it-message'	=> 'it_comment_saved'
			), admin_url( 'post.php' ) );
			
			$data['msg'] = __( "Comment successfully added." , 'wpas_it' );
			$success = true;
		} 
		
		if( $success ) {
			wp_send_json_success( $data );
		} else {
			wp_send_json_error( $data );
		}
		
	}
	
	die();
}


add_action( 'wp', 'wpas_it_prevent_frontend_view' );
/**
 * Prevent front-end access to status single post
 *
 * The status post type is only used for storing custom status used for tickets. It is not at all used on the front-end
 * and we don't want users to land on a custom status single page.
 *
 * @since 1.0.0
 * @return void
 */
function wpas_it_prevent_frontend_view() {

	global $wp_query;
	
	$post_types = array( 'wpas_issue_tracking' , 'wpas_it_comment');

	if ( $wp_query->is_main_query() && $wp_query->is_single() && isset( $wp_query->query['post_type'] ) && in_array( $wp_query->query['post_type'], $post_types ) ) {
		$wp_query->set_404();
	}

}