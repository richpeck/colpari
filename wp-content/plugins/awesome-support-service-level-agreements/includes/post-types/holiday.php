<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}


add_action( 'init', 'wpas_sla_register_post_type_holiday' );

/**
 * Registering new post type for holidays
 *
 * @since 1.0.0
 * @return void
 */
function wpas_sla_register_post_type_holiday() {

	$labels = array(
		'menu_name'          => __( 'Holidays', 'wpas_sla' ),
		'name'               => _x( 'Holidays', 'Post Type General Name', 'wpas_sla' ),
		'singular_name'      => _x( 'Holidays', 'Post Type Singular Name', 'wpas_sla' ),
		'add_new_item'       => __( 'Add New Holiday', 'wpas_sla' ),
		'add_new'            => __( 'New Holiday', 'wpas_sla' ),
		'not_found'          => __( 'No Holiday found', 'wpas_sla' ),
		'not_found_in_trash' => __( 'No Holiday found in Trash', 'wpas_sla' ),
		'parent_item_colon'  => __( 'Parent Holiday:', 'wpas_sla' ),
		'all_items'          => __( 'Holidays', 'wpas_sla' ),
		'view_item'          => __( 'View Holiday', 'wpas_sla' ),
		'edit_item'          => __( 'Edit Holiday', 'wpas_sla' ),
		'update_item'        => __( 'Update Holiday', 'wpas_sla' ),
		'search_items'       => __( 'Search Holidays', 'wpas_sla' ),
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
		'description'         => __( 'Holidays', 'wpas_sla' ),
		'supports'            => array( 'title', 'editor' ),
		'public'              => false,
		'show_ui'             => false,
		'show_in_admin_bar'   => true,
		'show_in_menu'        => 'edit.php?post_type=wpas_sla',
		'publicly_queryable'  => true,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'can_export'          => true,
		'capabilities'        => $cap,				
		'capability_type'     => 'ticket_sla_admin'
	);
	
	global $current_user;
	
	if( $current_user && $current_user->has_cap( 'ticket_sla_admin' ) ) {
		$args['show_ui'] = true;
	}

	register_post_type( 'wpas_sla_holiday', $args );

}





add_action( 'save_post_wpas_sla_holiday', 'wpas_sla_save_holiday' );
/**
 * Save holiday post metaboxes
 *
 * @since 1.0.0
 *
 * @param int $post_id
 *
 * @return void
 */
function wpas_sla_save_holiday( $post_id ) {

	// Verify nonce
	if ( ! ( isset( $_POST['wpas_sla_holiday_nonce'] ) && wp_verify_nonce( $_POST['wpas_sla_holiday_nonce'], basename( __FILE__ ) ) ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) || defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	
	$post = get_post( $post_id );
	
	
	if ( 'wpas_sla_holiday' == $_POST['post_type'] && $post->post_type == 'wpas_sla_holiday' ) {
		
		
		$date = filter_input( INPUT_POST, 'holiday_date', FILTER_SANITIZE_STRING );
		
		$holiday_date = '';
		
		if( $date ) {
			$holiday_date = date( SLA_DATE_FORMAT, strtotime( $date ) );
		}
		
		update_post_meta( $post_id, 'holiday_date', $holiday_date );
	}

}




add_action( 'add_meta_boxes', 'wpas_sla_holiday_meta_boxes' );

/**
 * Register holiday post metaboxes
 *
 * @since 1.0.0
 * @return void
 */
function wpas_sla_holiday_meta_boxes() {
	add_meta_box( 'wpas_sla_meta_box_settings', __( 'Settings', 'wpas_sla' ), 'wpas_sla_meta_box_holiday_settings', 'wpas_sla_holiday', 'side', 'default' );
}

/**
 * Holiday Settings metabox
 *
 * @global int $post_ID
 */
function wpas_sla_meta_box_holiday_settings() {

	global $post_ID;

	$holiday_date = '';
	$date = '';
	
	if ( $post_ID ) {
		$holiday_date = get_post_meta( $post_ID, 'holiday_date', true );
		
		if( $holiday_date ) {
			
			$datetime = DateTime::createFromFormat( SLA_DATE_FORMAT, $holiday_date );
		
			if( $datetime ) {
				$date =  $datetime->format('F d, Y');
			}
			
		}
	}
	?>

	<div>
		<label><strong><?php _e( 'Date', 'wpas_sla' ) ?> : </strong></label>
		<p>
				<input type="text" name="holiday_date" value="<?php echo $date; ?>" />
		</p>
	</div>

	<input type="hidden" name="wpas_sla_holiday_nonce" value="<?php echo wp_create_nonce( basename( __FILE__ ) ); ?>">
	
	
	<?php
}

add_action( 'wp', 'wpas_sla_prevent_frontend_view' );
/**
 * Prevent front-end access to sla and holiday single post
 *
 * @since 1.0.0
 * @return void
 */
function wpas_sla_prevent_frontend_view() {

	global $wp_query;
	
	
	$post_types = array( 'wpas_sla' , 'wpas_sla_holiday' );

	if ( $wp_query->is_main_query() && $wp_query->is_single() && isset( $wp_query->query['post_type'] ) && in_array( $wp_query->query['post_type'], $post_types ) ) {
		$wp_query->set_404();
	}

}

add_filter( 'manage_wpas_sla_holiday_posts_columns', 'wpas_sla_holiday_add_columns' );

/**
 * Add custom columns in holidays listing
 * 
 * @param array $columns
 * 
 * @return array
 */
function wpas_sla_holiday_add_columns( $columns ) {
	
	unset( $columns['date'] );
	$columns['holiday_date'] = __( 'Holiday Date', 'wpas_sla' );

	
	return $columns;
}



add_action( 'manage_wpas_sla_holiday_posts_custom_column' , 'wpas_sla_holiday_custom_columns_content', 10, 2 );
/**
 * Print custom columns content for holiday post
 * 
 * @param string $column
 * @param int $post_id
 */
function wpas_sla_holiday_custom_columns_content( $column, $post_id ) {
	
	
	if( 'holiday_date' === $column ) {
		echo esc_html( get_post_meta( $post_id, 'holiday_date', true ) );	
	}
	
}

add_filter( 'manage_edit-wpas_sla_holiday_sortable_columns', 'wpas_sla_holiday_custom_sortable_columns' );

/**
 * Add custom sortable columns in holidays listing
 * 
 * @param array $columns
 * 
 * @return array
 */
function wpas_sla_holiday_custom_sortable_columns( $columns ) {
	
    $columns['holiday_date'] = 'holiday_date';
	
    return $columns;
}

add_action( 'pre_get_posts', 'wpas_sla_holiday_handle_sortable_columns' );

/**
 * Handle custom sort query for holiday posts
 * 
 * @global string $pagenow
 * 
 * @param object $query
 * 
 * @return void
 */
function wpas_sla_holiday_handle_sortable_columns( $query ) {
    
	global $pagenow;

	if ( ! is_admin() || ! isset( $_GET[ 'post_type' ] ) || 'wpas_sla_holiday' !== $_GET[ 'post_type' ]
	     || 'edit.php' !== $pagenow
	     || $query->query[ 'post_type' ] !== 'wpas_sla_holiday'
	     || ! $query->is_main_query()
	) {
		return;
	}
	
	
	$input_orderby = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING );
	
	$orderby = $query->get( 'orderby' );
	$order = $query->get( 'order' );
	
	if( !$input_orderby ) {
		$orderby = 'holiday_date';
		$order = 'desc';
	}
	
	
    if( 'holiday_date' == $orderby ) {
		
        $query->set( 'meta_key', 'holiday_date' );
        $query->set( 'orderby', 'meta_value' );
		$query->set( 'order', $order );
    }
	
	return;
}