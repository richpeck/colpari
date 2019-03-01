<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}


add_action( 'init', 'wpas_cp_register_post_type_company_profile' );

/**
 * Registering new post type for issue tracking
 *
 * @since 1.0.0
 * @return void
 */
function wpas_cp_register_post_type_company_profile() {

	$labels = array(
		'menu_name'          => __( 'Company Profiles', 'wpas_cp' ),
		'name'               => _x( 'Company Profile', 'Post Type General Name', 'wpas_cp' ),
		'singular_name'      => _x( 'Company Profile', 'Post Type Singular Name', 'wpas_cp' ),
		'add_new_item'       => __( 'Add New Company Profile', 'wpas_cp' ),
		'add_new'            => __( 'New Company Profile', 'wpas_cp' ),
		'not_found'          => __( 'No Company Profile found', 'wpas_cp' ),
		'not_found_in_trash' => __( 'No Company Profile found in Trash', 'wpas_cp' ),
		'parent_item_colon'  => __( 'Parent Company Profile:', 'wpas_cp' ),
		'all_items'          => __( 'Company Profiles', 'wpas_cp' ),
		'view_item'          => __( 'View Company Profile', 'wpas_cp' ),
		'edit_item'          => __( 'Edit Company Profile', 'wpas_cp' ),
		'update_item'        => __( 'Update Company Profile', 'wpas_cp' ),
		'search_items'       => __( 'Search Company Profile', 'wpas_cp' ),
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
		'description'         => __( 'Company Profile', 'wpas_cp' ),
		'supports'            => array( 'title', 'editor' ),
		'public'              => false,
		'show_ui'             => false,
		'show_in_admin_bar'   => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'can_export'          => true,
		'capabilities'        => $cap,				
		'capability_type'     => 'ticket_manage_company_profiles'
	);

	
	global $current_user;
	
	if( $current_user && $current_user->has_cap( 'ticket_manage_company_profiles' ) ) {
		$args['show_ui'] = true;
	}
	
	register_post_type( 'wpas_company_profile', $args );

}




add_action( 'save_post_wpas_company_profile', 'wpas_cp_save_company_profile' );


/**
 * Save company profile post meta info
 *
 * @param int $post_id
 *
 * @return void
 */
function wpas_cp_save_company_profile( $post_id ) {

	// Verify nonce
	if ( ! ( isset( $_POST['wpas-cp-add-company-profile-nonce'] ) && wp_verify_nonce( $_POST['wpas-cp-add-company-profile-nonce'], 'wpas_cp_company_profile' ) ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$post = get_post( $post_id );
	
	
	if ( 'wpas_company_profile' == $_POST['post_type'] && $post->post_type == 'wpas_company_profile' ) {
		
		
		
		$address = filter_input( INPUT_POST, 'cp_address',		FILTER_SANITIZE_STRING );
		$email   = filter_input( INPUT_POST, 'cp_email',		FILTER_SANITIZE_EMAIL );
		$phone   = filter_input( INPUT_POST, 'cp_phone',		FILTER_SANITIZE_STRING );
		$fax     = filter_input( INPUT_POST, 'cp_fax',			FILTER_SANITIZE_STRING );
		
		
		
		update_post_meta( $post_id, 'address', $address );
		update_post_meta( $post_id, 'email',   $email );
		update_post_meta( $post_id, 'phone',   $phone );
		update_post_meta( $post_id, 'fax',     $fax );
		
		
	}

}


add_action( 'add_meta_boxes', 'wpas_cp_meta_boxes' );


/**
 * Register metaboxes
 *
 * @return void
 */
function wpas_cp_meta_boxes() {
	
	add_meta_box( 'wpas-cp-mb-company-details', __( 'Company Details', 'wpas_cp' ), 'wpas_cp_metabox_callback', 'wpas_company_profile', 'normal', 'high', array( 'template' => 'company_details' ) );
	add_meta_box( 'wpas-cp-mb-support-users',   __( 'Support Users', 'wpas_cp' ),   'wpas_cp_metabox_callback', 'wpas_company_profile', 'normal', 'high', array( 'template' => 'support_users' ) );
	add_meta_box( 'wpas-cp-mb-support-logs',    __( 'Logs', 'wpas_cp' ),            'wpas_cp_metabox_callback', 'wpas_company_profile', 'normal', 'high', array( 'template' => 'logs' ) );
}



/**
 * Render company profile metaboxes
 * 
 * @param object $post
 * @param array $args
 */
function wpas_cp_metabox_callback( $post, $args ) {
	

	if ( ! is_array( $args ) || ! isset( $args['args']['template'] ) ) {
		_e( 'An error occurred while registering this metabox. Please contact support.', 'wpas_cp' );
	}

	$template = $args['args']['template'];

	if ( ! file_exists( WPAS_CP_PATH . "includes/templates/metaboxes/$template.php" ) ) {
		_e( 'An error occured while loading this metabox. Please contact support.', 'wpas_cp' );
	}

	/* Include the metabox content */
	include_once( WPAS_CP_PATH . "includes/templates/metaboxes/$template.php" );

}



add_action( 'wpas_backend_ticket_status_before_actions', 'wpas_cp_ticket_company_field' );

/**
 * Add company profile field in ticket edit page
 * 
 * @global int $post_id
 */
function wpas_cp_ticket_company_field() {
	
	global $post_id;
	
	?>

	<div class="wpas_cp_company_id_picker">
		<p>
			<label> <strong><?php _e( 'Company Profile', 'wpas_cp' ); ?></strong> </label>
			<div>
					
				<?php
				
				wp_nonce_field( 'wpas-get-cp-companies', 'cp_nonce_wpas_get_cp_companies' );
					
					$company_id = get_post_meta( $post_id, '_wpas_company_id', true );
	
					$options = "";

					if( $company_id ) {
						$company = get_post( $company_id );
						if( $company ) {
							$options = sprintf( '<option selected="selected" value="%s">%s</option>', $company_id, $company->post_title );
						}
					}


					$dd_atts = array(
						'name'      => 'wpas_company_id',
						'id'        => 'wpas_company_id',
						'select2'   => true,
						'class' => 'cp-select2',
						'data_attr' => array( 'action' => 'wpas_get_cp_companies', 'result_id' => 'company_id', 'result_text' => 'company_name', 'default' => '' )
					);

					echo wpas_dropdown( $dd_atts, $options );
				?>
					
			</div>
		</p>
	</div>

	<?php
}




add_filter( 'manage_wpas_company_profile_posts_columns', 'wpas_cp_company_profile_columns' );

/**
 * Add new columns in company profiles listing table
 * 
 * @param array $columns
 * 
 * @return array
 */
function wpas_cp_company_profile_columns( $columns ) {
	
	unset( $columns['date'] );
	
	$columns['address']			= __( 'Address', 'wpas_cp' );
	$columns['email']			= __( 'Email',   'wpas_cp' );
	$columns['phone']			= __( 'Phone',   'wpas_cp' );
	$columns['fax']				= __( 'Fax',     'wpas_cp' );
	$columns['date']			= __( 'Date',    'wpas_cp' );
	
	return $columns;
	
}



add_action( 'manage_wpas_company_profile_posts_custom_column' , 'wpas_cp_company_profile_posts_custom_column', 10, 2 );

/**
 * Print values for each custom column for issue listing table
 * 
 * @param type $column
 * 
 * @param type $post_id
 */
function wpas_cp_company_profile_posts_custom_column( $column, $post_id ) {
	
	
	switch ( $column ) {
		
		case 'address' :
			echo get_post_meta( $post_id, 'address', true );
			break;
		case 'email' :
			echo get_post_meta( $post_id, 'email', true );
			break;
		case 'phone' :
			echo get_post_meta( $post_id, 'phone', true );
			break;
		case 'fax' :
			echo get_post_meta( $post_id, 'fax', true );
			break;
		
	}
}


/**
 * check if company profile is linked with a ticket
 * 
 * @global object $wpdb
 * 
 * @param int $company_id
 * 
 * @return boolean
 */
function is_company_assigned_to_ticket( $company_id ) {
	global $wpdb;
		
	$query = "SELECT COUNT(p.ID) FROM {$wpdb->posts} p 
			INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '%s' 
			WHERE p.post_type = '%s' AND pm.meta_value = '%s'";
			
	$tickets_count = $wpdb->get_var( $wpdb->prepare( $query, '_wpas_company_id', 'ticket', $company_id ) );
	
	return ( ( $tickets_count > 0 ) ? true : false );
}




add_action( 'before_delete_post', 'wpas_cp_maybe_prevent_deletion',  10, 1 );
add_action( 'wp_trash_post',   'wpas_cp_maybe_prevent_deletion',     10, 1 );

/**
 * Prevent deleting company profile if its used in a ticket
 * 
 * @global string $sendback
 * 
 * @param int $post_id
 */
function wpas_cp_maybe_prevent_deletion( $post_id ) {
	
	global $sendback;
	
	$post = get_post( $post_id );
	
	
	if( $post->post_type === 'wpas_company_profile' ) {
		
		if( true === is_company_assigned_to_ticket( $post_id ) ) {
			wp_redirect( add_query_arg( array( 'deleted' => 0, 'ids' => $post_id, 'company_delete' => 'failed' ), $sendback ) );
			die();
		}
	}
}


/**
 * Display error if user tries to delete in used company profile
 */
function wpas_company_delete_failed_notice() {
	
	$class = 'notice notice-error';
	$message = __( 'Failure Notice : Your attempt to delete this company has failed becaused the company is already linked to existing tickets.', 'wpas_cp' );

	printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
	
}



add_action( 'admin_init', 'wpas_cp_add_admin_noties' );

/**
 * Register notice if company deletion failed
 */
function wpas_cp_add_admin_noties() {

	if( isset( $_GET['company_delete'] ) && 'failed' == $_GET['company_delete'] ) {
		$_SERVER['REQUEST_URI'] = remove_query_arg( 'company_delete' );
		add_action( 'admin_notices', 'wpas_company_delete_failed_notice' );
	}
	
}




add_action( 'delete_post', 'wpas_cp_delete_company_profile' );

/**
 * Remove user association after a company is permanently removed
 * 
 * @global object $wpdb
 * 
 * @param int $company_id
 */
function wpas_cp_delete_company_profile( $company_id ) {
	global $wpdb;
	
	
	$post = get_post( $company_id );
	
	
	if( 'wpas_company_profile' === $post->post_type ) {
		
		$tbl = "{$wpdb->prefix}company_support_users";
		$q = "DELETE FROM {$tbl} WHERE	profile_id = %d";
		$wpdb->query( $wpdb->prepare( $q, $company_id ) );
	}
}