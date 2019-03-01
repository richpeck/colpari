<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}


add_action( 'init', 'wpas_it_register_post_type_issue_tracking' );

/**
 * Registering new post type for issue tracking
 *
 * @since 1.0.0
 * @return void
 */
function wpas_it_register_post_type_issue_tracking() {

	$labels = array(
		'menu_name'          => __( 'Issue Tracking', 'wpas_it' ),
		'name'               => _x( 'Issue Tracking', 'Post Type General Name', 'wpas_it' ),
		'singular_name'      => _x( 'Issue Tracking', 'Post Type Singular Name', 'wpas_it' ),
		'add_new_item'       => __( 'Add New Issue Tracking', 'wpas_it' ),
		'add_new'            => __( 'New Issue', 'wpas_it' ),
		'not_found'          => __( 'No Issue Tracking found', 'wpas_it' ),
		'not_found_in_trash' => __( 'No Issue Tracking found in Trash', 'wpas_it' ),
		'parent_item_colon'  => __( 'Parent Issue Tracking:', 'wpas_it' ),
		'all_items'          => __( 'All Issues', 'wpas_it' ),
		'view_item'          => __( 'View Issue Tracking', 'wpas_it' ),
		'edit_item'          => __( 'Edit Issue Tracking', 'wpas_it' ),
		'update_item'        => __( 'Update Issue Tracking', 'wpas_it' ),
		'search_items'       => __( 'Search Issue Tracking', 'wpas_it' ),
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
		'hierarchical'        => true,
		'description'         => __( 'Issue Tracking', 'wpas_it' ),
		'supports'            => array( 'title', 'editor' ),
		'public'              => false,
		'show_ui'             => true,
		'show_in_admin_bar'   => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'can_export'          => true,
		'capabilities'        => $cap,				
		'capability_type'     => 'edit_ticket'
	);

	register_post_type( 'wpas_issue_tracking', $args );

}




add_action( 'save_post_wpas_issue_tracking', 'wpas_it_save_issue_tracking' );
/**
 * Save Issue Tracking post
 *
 * @since 1.0.0
 *
 * @param int $post_id
 *
 * @return void
 */
function wpas_it_save_issue_tracking( $post_id ) {

	// Verify nonce
	if ( ! ( isset( $_POST['wpas-it-add-issue-nonce'] ) && wp_verify_nonce( $_POST['wpas-it-add-issue-nonce'], 'wpas_it_issue' ) ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$post = get_post( $post_id );
	
	
	
	if ( 'wpas_issue_tracking' == $_POST['post_type'] && $post->post_type == 'wpas_issue_tracking' ) {
		
		
		$primary_agent = filter_input( INPUT_POST, 'wpas_it_primary_agent',   FILTER_SANITIZE_NUMBER_INT );

		$status   = filter_input( INPUT_POST, 'wpas_it_status',   FILTER_SANITIZE_NUMBER_INT );
		$priority = filter_input( INPUT_POST, 'wpas_it_priority', FILTER_SANITIZE_NUMBER_INT );
		
		
		$state = get_post_meta( $post_id, '_wpas_it_state', true );
		
		if( "" === $state ) {
			update_post_meta( $post_id, '_wpas_it_state', 'open' );
		}
		
		update_post_meta( $post_id, 'wpas_it_primary_agent',		$primary_agent     );
		
		$num_comments = get_post_meta( $post_id, 'comments_count', true );
		$num_tickets  = get_post_meta( $post_id, 'tickets_count', true );
		
		$num_comments = $num_comments ? $num_comments : '0';
		$num_tickets = $num_tickets ? $num_tickets : '0';
		
		update_post_meta( $post_id, 'comments_count', $num_comments );
		update_post_meta( $post_id, 'tickets_count', $num_tickets );
		
		
		wp_set_object_terms( $post_id, (int) $status,   'wpas_it_status' );
		wp_set_object_terms( $post_id, (int) $priority, 'wpas_it_priority' );
		
	}

}



add_action( 'add_meta_boxes', 'wpas_it_meta_boxes' );
/**
 * Register metaboxes
 *
 * @since 1.0.0
 * @return void
 */
function wpas_it_meta_boxes() {
	
	if( isset( $_GET['post'] ) ) {
	
		add_meta_box( 'wpas-it-mb-replies',   __( 'Comments', 'wpas_it' ),      'wpas_it_metabox_callback', 'wpas_issue_tracking', 'normal', 'high', array( 'template' => 'comments' ) );
		
	}
	
	add_meta_box( 'wpas-it-mb-details',   __( 'Issue Details', 'wpas_it' ), 'wpas_it_metabox_callback', 'wpas_issue_tracking', 'side', 'high', array( 'template' => 'details' ) );	
	
	add_meta_box( 'wpas-it-mb-tabs',   __( 'Tabs', 'wpas_it' ),      'wpas_it_metabox_callback', 'wpas_issue_tracking', 'normal', 'high', array( 'template' => 'tabs' ) );
	
	add_filter( 'wpas_admin_tabs_after_ticket', 'wpas_it_after_ticket_tabs' );
	
	add_meta_box( 'wpas-mb-ticket-tabs',     __( 'Tabs', 'wpas_it' ), 'wpas_it_ticket_tabs', 'ticket', 'normal', 'default' );
	
}



/**
 * Render issue tracking metaboxes
 * 
 * @param object $post
 * @param array $args
 */
function wpas_it_metabox_callback( $post, $args ) {
	

	if ( ! is_array( $args ) || ! isset( $args['args']['template'] ) ) {
		_e( 'An error occurred while registering this metabox. Please contact support.', 'wpas_it' );
	}

	$template = $args['args']['template'];

	if ( ! file_exists( WPAS_IT_PATH . "includes/templates/metaboxes/$template.php" ) ) {
		_e( 'An error occured while loading this metabox. Please contact support.', 'wpas_it' );
	}

	/* Include the metabox content */
	include_once( WPAS_IT_PATH . "includes/templates/metaboxes/$template.php" );

}


add_action( 'admin_menu', 'wpas_it_remove_submitdiv_metabox',  9, 0 );
/**
 * Remove default submitdiv metabox
 */
function wpas_it_remove_submitdiv_metabox() {
    remove_meta_box( 'submitdiv', 'wpas_issue_tracking', 'normal' );
};


/**
 * After ticket tabs metabox
 */
function wpas_it_ticket_tabs() {
	
	echo wpas_admin_tabs( 'after_ticket' );
}


/**
 * Add Issues tab in ticket page
 * 
 * @param array $tabs
 * 
 * @return array
 */
function wpas_it_after_ticket_tabs( $tabs ) {
	
	
	add_filter( "wpas_admin_tabs_after_ticket_issues_content",  "wpas_admin_tabs_after_ticket_issues_content" );
	
	
	$tabs['issues'] = __( 'Issues', 'wpas_it' );
	return $tabs;
}

/**
 * Issues tab content for ticket details page
 * 
 * @global int $post_id
 * 
 * @param string $content
 * 
 * @return string
 */
function wpas_admin_tabs_after_ticket_issues_content( $content ) {
	global $post_id;
	
	ob_start();
	
	$ticket_issue = WPAS_IT_Ticket_Issue::get_instance();
	
	$ticket_issue->display( $post_id );
	
	
	$content = ob_get_clean();
	
	return $content;
}



add_filter( 'manage_wpas_issue_tracking_posts_columns', 'wpas_it_issue_tracking_columns' );

/**
 * Add new columns in issues listing table
 * 
 * @param array $columns
 * 
 * @return array
 */
function wpas_it_issue_tracking_columns( $columns ) {
	
	unset( $columns['date'] );
	unset( $columns['title'] );
	
	$columns['status']				= __( 'Status', 'wpas_it' );
	$columns['title']				= __( 'Title', 'wpas_it' );
	$columns['priority']			= __( 'Priority', 'wpas_it' );
	$columns['num_comments']		= __( 'Comments', 'wpas_it' );
	$columns['date_opened']			= __( 'Opened', 'wpas_it' );
	$columns['last_comment_date']	= __( 'Last comment', 'wpas_it' );
	$columns['agent']				= __( 'Agent', 'wpas_it' );
	$columns['num_tickets']			= __( 'Ticket Count', 'wpas_it' );
	
	$columns['date']				= __( 'Date', 'wpas_it' );
	
	return $columns;
	
}



add_action( 'manage_wpas_issue_tracking_posts_custom_column' , 'wpas_it_issue_tracking_posts_custom_column', 10, 2 );

/**
 * Print values for each custom column for issue listing table
 * 
 * @param type $column
 * 
 * @param type $post_id
 */
function wpas_it_issue_tracking_posts_custom_column( $column, $post_id ) {
	
	
	$issue = new WPAS_IT_Issue( $post_id );
	
	switch ( $column ) {
		
		case 'status' :
			echo $issue->display_status();
			break;
		case 'priority' :
			echo $issue->display_priority();
			break;
		case 'num_comments' :
			echo $issue->getCommentsCount();
			break;
		case 'date_opened' :
			echo $issue->Post->post_date;
			break;
		case 'last_comment_date' :
			
			if( $issue->is_closed() ) {
				echo $issue->close_date( true );
			} else {
				echo $issue->Post->last_comment_date;
			}
			
			break;
		case 'agent' :
			echo $issue->getPrimaryAgentName();
			break;
		case 'num_tickets' :
			echo $issue->getTicketsCount();
			break;
		
	}
}



add_action( 'restrict_manage_posts', 'wpas_it_issue_filters_tablenav', 8, 2 );

/**
 * Add filtering option in issue listing page
 * 
 * @param string $post_type
 * @param string $which
 * 
 * @return void
 */
function wpas_it_issue_filters_tablenav( $post_type, $which ) {

		if ( 'wpas_issue_tracking' !== $post_type || 'top' !== $which ) {
			return;
		}
		
		// States Dropdown
		$states = wpas_it_issue_states();
		
		$selected_state = filter_input( INPUT_GET, 'state', FILTER_SANITIZE_STRING );
		$state_options = '<option value="">' . __( 'All States' ) . '</option>';
		foreach( $states as $state_value => $state_name ) {
			
			$selected = $selected_state === $state_value ? ' selected="selected"' : '';
			
			$state_options .= "<option value=\"{$state_value}\"{$selected}>{$state_name}</option>";
		}
		
		echo wpas_dropdown( array(
			'name'          => 'state',
			'id'            => 'it_filter_state',
		), $state_options );

		
		$tax_filters = array( 'wpas_it_status', 'wpas_it_priority' );
		
		// Print status and priority dropdowns
		foreach ( $tax_filters as $tax_slug ) {

			$tax_obj = get_taxonomy( $tax_slug );

			$args = array(
				'show_option_all' => __( 'All ' . $tax_obj->label ),
				'taxonomy'        => $tax_slug,
				'name'            => $tax_obj->name,
				'orderby'         => 'name',
				'hierarchical'    => $tax_obj->hierarchical,
				'show_count'      => true,
				'hide_empty'      => true,
				'hide_if_empty'   => true,
				'value_field'     => 'slug'
			);

			if ( isset( $_GET[ $tax_slug ] ) ) {
				$args[ 'selected' ] = filter_input( INPUT_GET, $tax_slug, FILTER_SANITIZE_STRING );
			}

			wp_dropdown_categories( $args );

		}
		
		
		/* Print agent field */
		$selected       = __( 'All Agents', 'wpas_it' );
		$selected_value = '';

		if ( isset( $_GET[ 'assignee' ] ) && ! empty( $_GET[ 'assignee' ] ) ) {
			$staff_id = (int) $_GET[ 'assignee' ];
			$agent    = new WPAS_Member_Agent( $staff_id );

			if ( $agent->is_agent() ) {
				$user           = get_user_by( 'ID', $staff_id );
				$selected       = $user->display_name;
				$selected_value = $staff_id;
			}
		}

		$staff_atts = array(
			'name'      => 'assignee',
			'id'        => 'assignee',
			'disabled'  => ! current_user_can( 'assign_ticket' ) ? true : false,
			'select2'   => true,
			'data_attr' => array(
				'capability'  => 'edit_ticket',
				'allowClear'  => true,
				'placeholder' => $selected,
			),
		);

		if ( isset( $staff_id ) ) {
			$staff_atts[ 'selected' ] = $staff_id;
		}

		echo wpas_dropdown( $staff_atts, "<option value='" . $selected_value . "'>" . $selected . "</option>" );

}



add_filter( 'parse_query', 'custom_taxonomy_filter_convert_id_term', 10, 1 );

/**
 * Filter issues based on selected filter options
 * 
 * @global string $pagenow
 * 
 * @param object $wp_query
 */
function custom_taxonomy_filter_convert_id_term( $wp_query ) {
	global $pagenow;

	/* Check if we are in the correct post type */
	if ( is_admin()
	     && 'edit.php' == $pagenow
	     && isset( $_GET[ 'post_type' ] )
	     && 'wpas_issue_tracking' === $_GET[ 'post_type' ]
	     && $wp_query->is_main_query()
	) {
		
		$filter_meta_keys = array();
		
		
		$state = filter_input( INPUT_GET, 'state', FILTER_SANITIZE_STRING );
		$all_states = wpas_it_issue_states();
		
		$assignee = filter_input( INPUT_GET, 'assignee', FILTER_SANITIZE_NUMBER_INT );
		
		// Filter based on selected issue support staff
		if ( $assignee ) {
			
			$agent = new WPAS_Member_Agent( $assignee );
			
			if ( $agent->is_agent() ) {
				
				$filter_meta_keys[] = array(
						'key'     => 'wpas_it_primary_agent',
						'value'   => $assignee,
						'compare' => '=',
						'type'    => 'NUMERIC',
					);
			}
		} 
		
		// Filter based on selected issue state
		if ( $state && array_key_exists( $state, $all_states ) ) {
			
			$filter_meta_keys[] = array(
				'key'     => '_wpas_it_state',
				'value'   => $state,
				'compare' => '=',
				'type'    => 'char'
				);
		}
		
		
		if( !empty( $filter_meta_keys ) ) {
			$meta_query = $wp_query->get( 'meta_query' );
			$meta_query = ! is_array( $meta_query ) ? array_filter( (array) $meta_query ) : $meta_query;
			
			$meta_query = array_merge( $meta_query, $filter_meta_keys );
			if ( ! isset( $meta_query[ 'relation' ] ) ) {
				$meta_query[ 'relation' ] = 'AND';
			}

			$wp_query->set( 'meta_query', $meta_query );
			
			
		}
		

	}
}



add_filter( 'manage_edit-wpas_issue_tracking_sortable_columns', 'wpas_it_custom_columns_sortable', 10, 1 );

/**
 * Making custom columns sortable
 * 
 * @param array $columns
 * 
 * @return array
 */
function wpas_it_custom_columns_sortable( $columns ) {
	
	$columns['status']				= 'status';
	$columns['priority']			= 'priority';
	$columns['num_comments']		= 'num_comments';
	$columns['date_opened']			= 'date_opened';
	$columns['last_comment_date']	= 'last_comment_date';
	$columns['agent']				= 'agent';
	$columns['num_tickets']			= 'num_tickets';
	
	return $columns;
	
}


add_action( 'pre_get_posts', 'wpas_it_custom_columns_set_ordering_query_var', 100, 1 );


/**
 * Change query for custom columns sorting
 * 
 * @global string $pagenow
 * 
 * @param object $query
 * 
 * @return void
 */
function wpas_it_custom_columns_set_ordering_query_var( $query ) {
	
	global $pagenow;

	if ( ! isset( $_GET[ 'post_type' ] ) || 'wpas_issue_tracking' !== $_GET[ 'post_type' ]
	     || 'edit.php' !== $pagenow
	     || $query->query[ 'post_type' ] !== 'wpas_issue_tracking'
	     || ! $query->is_main_query()
	) {
		return;
	}
	
	
	
	$orderby = $query->get( 'orderby');
	
	switch ( $orderby ) {
		
		case 'num_comments':
			$query->set( 'meta_key', 'comments_count' );
			$query->set( 'orderby', 'meta_value_num' );
			break;
		
		case 'num_tickets':
			$query->set( 'meta_key', 'tickets_count' );
			$query->set( 'orderby', 'meta_value_num' );
			break;
	}
	
	
	
	return;
	
}



add_filter( 'wpas_it_localize_script', 'wpas_it_issue_localize_script', 11, 1 );

/**
 * Adding data to localize script so we can access it from javascript
 * 
 * @param array $data
 * 
 * @return array
 */
function wpas_it_issue_localize_script( $data ) {
	
	$data['support_staff_required_msg'] = __( 'Support staff is required.', 'wpas_it' );
	
	return $data;
	
}



add_action( 'before_delete_post', 'wpas_it_before_delete_issue' ,11, 1 );

/**
 * Delete associated ticket meta keys once an issue is deleted
 * 
 * @global object $wpdb
 * 
 * @param int $post_id
 */
function wpas_it_before_delete_issue( $post_id ) {
	global $wpdb;
	
	if( 'wpas_issue_tracking' === get_post_type( $post_id ) ) {
		$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => "wpas_ticket_issue_{$post_id}" ), array( '%s' ) );
	}
	
}

