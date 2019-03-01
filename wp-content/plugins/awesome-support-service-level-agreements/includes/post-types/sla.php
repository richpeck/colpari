<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}


add_action( 'init', 'wpas_sla_register_post_type_sla' );

/**
 * Registering new post type for sla
 *
 * @since 1.0.0
 * @return void
 */
function wpas_sla_register_post_type_sla() {

	$labels = array(
		'menu_name'          => __( 'Service Level Agreements', 'wpas_sla' ),
		'name'               => _x( 'Service Level Agreement', 'Post Type General Name', 'wpas_sla' ),
		'singular_name'      => _x( 'Service Level Agreement', 'Post Type Singular Name', 'wpas_sla' ),
		'add_new_item'       => __( 'Add New Service Level Agreement', 'wpas_sla' ),
		'add_new'            => __( 'New Service Level Agreement', 'wpas_sla' ),
		'not_found'          => __( 'No Service Level Agreement found', 'wpas_sla' ),
		'not_found_in_trash' => __( 'No Service Level Agreements found in Trash', 'wpas_sla' ),
		'parent_item_colon'  => __( 'Parent Service Level Agreement:', 'wpas_sla' ),
		'all_items'          => __( 'Service Level Agreements', 'wpas_sla' ),
		'view_item'          => __( 'View Service Level Agreement', 'wpas_sla' ),
		'edit_item'          => __( 'Edit Service Level Agreement', 'wpas_sla' ),
		'update_item'        => __( 'Update Service Level Agreement', 'wpas_sla' ),
		'search_items'       => __( 'Search Service Level Agreements', 'wpas_sla' ),
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
		'description'         => __( 'Service Level Agreements', 'wpas_sla' ),
		'supports'            => array( 'title', 'editor' ),
		'public'              => false,
		'show_ui'             => false,
		'show_in_admin_bar'   => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'can_export'          => true,
		'capabilities'        => $cap,
		'capability_type'     => 'ticket_sla_admin'
	);
	
	global $current_user;
	
	if( $current_user && $current_user->has_cap('ticket_sla_admin') ) {
		$args['show_ui'] = true;
	}

	register_post_type( 'wpas_sla', $args );

}


add_action( 'save_post_wpas_sla', 'wpas_sla_save_sla_post' );
/**
 * Save metaboxes data
 *
 * @since 1.0.0
 *
 * @param int $post_id
 *
 * @return void
 */
function wpas_sla_save_sla_post( $post_id ) {

	// Verify nonce
	if ( ! ( isset( $_POST['wpas_sla_nonce'] ) && wp_verify_nonce( $_POST['wpas_sla_nonce'], basename( __FILE__ ) ) ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) || defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}


	$post = get_post( $post_id );
	
	if ( 'wpas_sla' == $_POST['post_type'] && $post->post_type == 'wpas_sla' ) {
		
		
		$old_time_frame = get_post_meta( $post_id, 'time_frame', true );
		
		$time_frame = filter_input( INPUT_POST, 'time_frame', FILTER_SANITIZE_NUMBER_INT );
		$sla_alerts = filter_input( INPUT_POST, 'sla_alerts', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		
		$sla_alerts = $sla_alerts && is_array( $sla_alerts ) ? $sla_alerts : array();
		
		
		update_post_meta( $post_id, 'time_frame', $time_frame );
		update_post_meta( $post_id, 'sla_alerts', $sla_alerts );
		
		
		SLA_Ticket_Alert::set_alerts( $post_id );
		
	}

}


add_action( 'add_meta_boxes', 'wpas_sla_meta_boxes' );
/**
 * Register sla post metaboxes
 *
 * @since 1.0.0
 * @return void
 */
function wpas_sla_meta_boxes() {
	add_meta_box( 'wpas_sla_meta_box_settings', __( 'Settings', 'wpas_sla' ), 'wpas_sla_meta_box_settings', 'wpas_sla', 'side', 'high' );
	add_meta_box( 'wpas_sla_meta_box_content', __( 'Content', 'wpas_sla' ), 'wpas_sla_meta_box_content', 'wpas_sla', 'normal' );
	add_meta_box( 'wpas_sla_meta_box_alerts', __( 'Alerts', 'wpas_sla' ), 'wpas_sla_meta_box_alerts', 'wpas_sla', 'normal' );
	
	add_filter( "postbox_classes_wpas_sla_wpas_sla_meta_box_content", 'wpas_sla_content_metabox_classes' );

}

/**
 * Add content metabox
 */
function wpas_sla_meta_box_content() {
	echo '<div class="wpas_sla_post_content"></div>';
}


/**
 * Add closed class to new content metabox to make content metabox minimized
 * 
 * @param array $classes
 * 
 * @return array
 */
function wpas_sla_content_metabox_classes( $classes ) {
	
    array_push( $classes, 'closed' );
	return $classes;
}

/**
 * Settings metabox for sla post
 *
 * @global int $post_ID
 */
function wpas_sla_meta_box_settings() {

	global $post_ID;

	$time_frame = '';

	if ( $post_ID ) {
		$time_frame = get_post_meta( $post_ID, 'time_frame', true );
	}
	?>



	<div class="wpas_sla_post_settings">
			
		<div>
			<div class="wpas_sla_recalculate_due_dates_msg"><p></p></div>
			<input type="button" class="wpas_sla_recalculate_due_dates button button-primary" value="<?php _e( 'Recalculate due dates', 'wpas_sla' ); ?>" />
		</div>
			
		<p>
			<label><strong><?php _e( 'Target Time (Minutes)', 'wpas_sla' ) ?> </strong></label><br />
			<input type="number" name="time_frame" value="<?php echo $time_frame; ?>" />
		</p>
		
		
		
		<div id="wpas_sla_time_convert_helper">
				
				<h3><?php _e( 'Time Conversion Calculator', 'wpas_sla' ); ?></h3>
				<p><?php _e( 'Since the time above needs to be set in minutes, here is a handy-dandy calculator to allow you to convert hours and days into minutes.', 'wpas_sla' ); ?></p>
				
				<p>
					<label><?php _e( 'Enter hours, days or weeks', 'wpas_sla' ); ?> : </label>
					<input type="number" class="wpas_sla_time_convert_time" />
				</p>
				
				<p>
					<select class="wpas_sla_time_convert_option">
						<option value="60"><?php _e( 'Hours', 'wpas_sla' ); ?></option>
						<option value="1440"><?php _e( 'Days', 'wpas_sla' ); ?></option>
						<option value="10080"><?php _e( 'Weeks', 'wpas_sla' ); ?></option>
					</select>
				</p>
				<div>
					<span class="wpas_sla_time_convert_answer"></span>
					<a href="#" class="wpas_sla_set_target_time_btn"><?php _e( 'SET TARGET TIME', 'wpas_sla' ) ?></a>
					<div class="clear clearfix"></div>
				</div>
				
		</div>
		<?php wp_nonce_field( 'wpas-sla-recalculate-due-dates', 'sla_nonce_wpas_sla_recalculate_due_dates' ); ?>
		
		
		
	</div>

	<input type="hidden" name="wpas_sla_nonce" value="<?php echo wp_create_nonce( basename( __FILE__ ) ); ?>">
	<?php
}


/**
 * Alerts metabox for sla post
 * 
 * @global type $post_id
 */
function wpas_sla_meta_box_alerts() {
	global $post_id;
	
	$alerts = maybe_unserialize( get_post_meta( $post_id, 'sla_alerts', true ) );
	
	$alerts = $alerts && is_array( $alerts) ? $alerts : array();
	
	?>
	
	
	<a href="#" class="button button-primary" id="wpas_sla_add_email_alert_btn"><?php _e( 'Add new alert', 'wpas_sla' ); ?></a>
	
	<div id="wpas_sla_add_email_alerts_wrapper">
		<?php include WPAS_SLA_PATH . 'includes/templates/add_email_alert_item.php'; ?>
	</div>
	<div id="wpas_sla_email_alerts">
	<?php
	
	foreach( $alerts as $alert_id => $alert ) {
		include WPAS_SLA_PATH . 'includes/templates/email_alert_item.php';
	}
	?>
	</div>
	<?php
	
}


add_action( 'wp_ajax_wpas_sla_recalculate_due_dates', 'wpas_sla_recalculate_due_dates' );

function wpas_sla_recalculate_due_dates() {
	
	wpas_sla_ajax_nonce_check( 'wpas-sla-recalculate-due-dates' ) ;
	
	$sla_id = filter_input( INPUT_POST, 'sla_id', FILTER_SANITIZE_STRING );
	
	$error_msg = "";
	
	
	if( $sla_id ) {
		
		$tickets = wpas_sla_get_tickets( $sla_id );
		
		if( count( $tickets ) > 0 ) {
			foreach ( $tickets as $ticket ) {
				
				$locked = get_post_meta( $ticket->ID, '_wpas_due_date_lock' , true );
				$ticket_status = get_post_meta( $ticket->ID, '_wpas_status' , true );
				if( !$locked && 'closed' !== $ticket_status ) {
					wpas_sla_calculate_ticket_due_date( $ticket->ID );
				}
			}
		} else {
			$error_msg = __( 'No ticket found.', 'wpas_sla' );
		}
			
	} else {
		$error_msg = __( 'There is an error try again later.', 'wpas_sla' );
	}
	
	if ( $error_msg ) {
		wp_send_json_error( array( 'msg' => $error_msg ) );
	} else {
		wp_send_json_success( array( 'msg' => __( 'Due dates successfully recalculated.', 'wpas_sla' ) ) );
	}
	
	die();
}

add_filter( 'manage_wpas_sla_posts_columns', 'wpas_sla_add_sla_post_columns' );

/**
 * Add new columns in sla post
 * 
 * @param array $columns
 * 
 * @return array
 */
function wpas_sla_add_sla_post_columns( $columns ) {
	
	unset( $columns['date'] );
	$columns['target_time'] = __( 'Target Time', 'wpas_sla' );
	$columns['date'] = __( 'Date', 'wpas_sla' );
	
	return $columns;
}

add_action( 'manage_wpas_sla_posts_custom_column' , 'wpas_sla_sla_post_custom_columns_content', 10, 2 );
/**
 * Print sla post custom columns content
 * 
 * @param string $column
 * 
 * @param int $post_id
 */
function wpas_sla_sla_post_custom_columns_content( $column, $post_id ) {
	
	
	if( 'target_time' === $column ) {
		
		$time = get_post_meta( $post_id, 'time_frame', true );
		
		if( $time ) {
			echo "{$time} " . __( 'Minutes', 'wpas_sla' );
		}
	}
	
}