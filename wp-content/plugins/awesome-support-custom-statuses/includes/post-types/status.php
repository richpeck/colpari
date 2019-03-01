<?php
/**
 * @package   Awesome Support Statuses
 * @author    Awesome Support
 * @link      http://www.getawesomesupport.com
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Registering new post type for statuses
 *
 * @since 1.0.0
 * @return void
 */
function wpass_register_post_type_status() {

	$labels = array(
		'menu_name'          => __( 'Status and Labels', 'wpass_statuses' ),
		'name'               => _x( 'Status', 'Post Type General Name', 'wpass_statuses' ),
		'singular_name'      => _x( 'Status', 'Post Type Singular Name', 'wpass_statuses' ),
		'add_new_item'       => __( 'Add New Status', 'wpass_statuses' ),
		'add_new'            => __( 'New Status', 'wpass_statuses' ),
		'not_found'          => __( 'No Status found', 'wpass_statuses' ),
		'not_found_in_trash' => __( 'No Status found in Trash', 'wpass_statuses' ),
		'parent_item_colon'  => __( 'Parent Status:', 'wpass_statuses' ),
		'all_items'          => __( 'Custom Status', 'wpass_statuses' ),
		'view_item'          => __( 'View Status', 'wpass_statuses' ),
		'edit_item'          => __( 'Edit Status', 'wpass_statuses' ),
		'update_item'        => __( 'Update Status', 'wpass_statuses' ),
		'search_items'       => __( 'Search Status', 'wpass_statuses' ),
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
		'description'         => __( 'Ticket Status', 'wpass_statuses' ),
		'supports'            => array( 'title' ),
		'public'              => false,
		'show_ui'             => true,
		//'show_in_menu'        => 'edit.php?post_type=ticket',
		'show_in_menu'        => false,
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'can_export'          => true,
		'capabilities'        => $cap,				
		'capability_type'     => 'edit_ticket'
	);

	register_post_type( 'wpass_status', $args );

}

add_action( 'admin_menu', 'register_submenu_items',  9, 0 );
/**
 * Add tickets submenu items.
 *
 * @since  1.0.3
 *
 * @return void
 */
function register_submenu_items() {
	
	/*Note: NB 12-1-2016: This menu is restricted to roles with "create_users" capabilities because we don't have an explicit AS ADMIN capability -- yet.*/
	/*Note: NB 06-1-2017: Capability hanged to administer_awesome_support after the release of AS 4.0.0.*/
	add_submenu_page( 'edit.php?post_type=ticket', __( 'Custom Status', 'wpass_status' ), __( 'Status and Labels', 'wpass_status' ), 'administer_awesome_support', 'edit.php?post_type=wpass_status' );
}

add_action( 'save_post_wpass_status', 'wpass_status_save' );
/**
 * Save metaboxes data
 *
 * @since 1.0.0
 *
 * @param int $post_id
 *
 * @return void
 */
function wpass_status_save( $post_id ) {

	// Verify nonce
	if ( ! ( isset( $_POST['wpass_status_nonce'] ) && wp_verify_nonce( $_POST['wpass_status_nonce'], basename( __FILE__ ) ) ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$post = get_post( $post_id );
	
	if ( 'wpass_status' == $_POST['post_type'] && $post->post_type == 'wpass_status' ) {
		
		$status_key = get_post_meta( $post_id, 'wpas_custom_status_id', true );
		
		if( empty( $status_key ) ) {
			wpass_save_custom_status_id( $post_id );
		}
		
		update_post_meta( $post_id, 'status_color', sanitize_text_field( $_POST['status_color'] ) );
	}

}

/**
 * Save custom status short id
 * @param int $post_id
 * @return string
 */
function wpass_save_custom_status_id( $post_id ) {
	
	$post = get_post( $post_id );
	
	$status_key = strlen( $post->post_name ) <= 20 ? $post->post_name : wpass_unique_custom_status_key( $post_id );
	update_post_meta( $post_id, 'wpas_custom_status_id', $status_key );
	
	return $status_key;
}

/**
 * Return unique short id of custom status
 * @param int $post_id
 * @return string
 */
function wpass_unique_custom_status_key( $post_id ) {
	
	$post = get_post( $post_id );
	$key = substr( $post->post_name, 0, 20 );
	
	$exists = wpass_custom_status_key_exists( $key );
	
	
	if ( $exists ) {
		$suffix = 1;
		do {
			$limit = 20 - ( strlen( $suffix ) + 1 );
			$new_key = substr( $key,  0, $limit ) . "-$suffix";
			$exists = wpass_custom_status_key_exists( $new_key );
			$suffix++;
		} while ( $exists );
		
		$key = $new_key;
	}
	
	return $key;
	
}

/**
 * 
 * @global object $wpdb
 * @param string $key
 * @return boolean
 */
function wpass_custom_status_key_exists($key) {
	global $wpdb;
	
	$sql = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1";
	return $wpdb->get_var( $wpdb->prepare( $sql, 'wpas_custom_status_id' , $key ) );
}

add_action( 'add_meta_boxes', 'wpass_status_meta_boxes' );
/**
 * Register metaboxes
 *
 * @since 1.0.0
 * @return void
 */
function wpass_status_meta_boxes() {
	add_meta_box( 'wpass_status_meta_box_settings', 'Settings', 'wpass_status_meta_box_settings', 'wpass_status', 'normal', 'high' );
}

/**
 * Settings metabox
 *
 * @global int $post_ID
 */
function wpass_status_meta_box_settings() {

	global $post_ID;

	$color = '';

	if ( $post_ID ) {
		$color = get_post_meta( $post_ID, 'status_color', true );
	}
	?>

	<div>
		<label><strong><?= __( 'Color', 'wpass_statuses' ) ?> : </strong></label>
		<p>
			<input type="text" name="status_color" value="<?= $color ?>" class="status-color"/>
		</p>
	</div>

	<input type="hidden" name="wpass_status_nonce" value="<?= wp_create_nonce( basename( __FILE__ ) ) ?>">
	<?php
}

add_action( 'wp', 'wpass_status_prevent_frontend_view' );
/**
 * Prevent front-end access to status single post
 *
 * The status post type is only used for storing custom status used for tickets. It is not at all used on the front-end
 * and we don't want users to land on a custom status single page.
 *
 * @since 1.0.0
 * @return void
 */
function wpass_status_prevent_frontend_view() {

	global $wp_query;

	if ( $wp_query->is_main_query() && $wp_query->is_single() && isset( $wp_query->query['post_type'] ) && 'wpass_status' === $wp_query->query['post_type'] ) {
		$wp_query->set_404();
	}

}


/**
 * check if status is in use
 * 
 * @global object $wpdb
 * @param object $status
 * 
 * @return boolean
 */
function is_status_assigned_to_open_ticket( $status ) {
	
	global $wpdb;
	
	$status_key = get_post_meta( $status->ID, 'wpas_custom_status_id', true );
		
	$query = "SELECT COUNT(p.ID) FROM {$wpdb->posts} p 
			INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '%s' 
			WHERE p.post_type = '%s' AND p.post_status = '%s' AND pm.meta_value = '%s'";
			
	$tickets_count = $wpdb->get_var( $wpdb->prepare( $query, '_wpas_status', 'ticket', $status_key, 'open' ) );
	
	return ( ( $tickets_count > 0 ) ? true : false );
}



add_action( 'before_delete_post', 'maybe_prevent_deletion',  10, 1 );
add_action( 'wp_trash_post',   'maybe_prevent_deletion',     10, 1 );


/**
 * Prevent deleting status if its in use
 * @param boolean $delete
 * @param object $post
 * @param boolean $force_delete
 * 
 * @return boolean
 */
function should_prevent_deletion( $delete, $post, $force_delete ) {
	
	if( $post->post_type === 'wpass_status' ) {
		if( true === is_status_assigned_to_open_ticket( $post ) ) {
			$delete = false;
		}
	}
	
	return $delete;
}


/**
 * Prevent deleting status if its in use
 * 
 * @global string $sendback
 * @param int $post_id
 */
function maybe_prevent_deletion( $post_id ) {
	
	global $sendback;
	
	$post = get_post( $post_id );
	
	if( $post->post_type === 'wpass_status' ) {
		
		if( true === is_status_assigned_to_open_ticket( $post ) ) {
			wp_redirect( add_query_arg( array( 'deleted' => 0, 'ids' => $post_id, 'status_delete' => 'failed' ), $sendback ) );
			exit;
		}
	}
}


/**
 * Display error if user tries to delete in used status
 */
function wpass_after_status_delete_notice() {
	
	$class = 'notice notice-error';
	$message = __( 'Failed : Status is linked with open tickets', 'sample-text-domain' );

	printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
	
}



add_action('admin_init', 'wpass_custom_status_admin_init');

function wpass_custom_status_admin_init() {

	if( isset( $_GET['status_delete'] ) && 'failed' == $_GET['status_delete'] ) {
		$_SERVER['REQUEST_URI'] = remove_query_arg( 'status_delete' );
		add_action( 'admin_notices', 'wpass_after_status_delete_notice' );
	}
	
}
