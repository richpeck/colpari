<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Return all week days
 * 
 * @return array
 */
function wpas_week_days() {
	return array(
		'mon'	=> __( 'Monday',	'wpas_sla' ),
		'tue'	=> __( 'Tuesday',	'wpas_sla' ),
		'wed'	=> __( 'Wednesday', 'wpas_sla' ),
		'thu'	=> __( 'Thursday',	'wpas_sla' ),
		'fri'	=> __( 'Friday',	'wpas_sla' ),
		'sat'	=> __( 'Saturday',	'wpas_sla' ),
		'sun'	=> __( 'Sunday',	'wpas_sla' ),
	);
}

/**
 * Return default workday start time and end work times
 * 
 * @return array
 */
function wpas_sla_default_work_times() {
	
	$all_days = wpas_week_days();
	
	unset( $all_days['sat'] );
	unset( $all_days['sun'] );
	
	
	$work_times = array();
	
	foreach ( $all_days as $day => $day_name ) {
		$work_times[ $day ] = array(
			'active'		=> true,
			'start_time'	=> 32400,
			'end_time'		=> 61200
		);
	}
	
	return $work_times;
}

/**
 * Return default time of a day
 * 
 * @param string $day
 * @param string $time
 */
function wpas_sla_get_day_default_setting( $day, $time ) {
	
	$default_times = wpas_sla_default_work_times();
	$default_time = isset( $default_times[ $day ] ) && isset( $default_times[ $day ][ $time ] ) ? $default_times[ $day ][ $time ] : '' ;
	
	return $default_time;
}

add_filter( 'wpas_ticket_list_activity_options', 'wpas_sla_ticket_list_activity_options' );
/**
 * Return ticket activity options based on due date
 * 
 * @param array $options
 * 
 * @return array
 */
function wpas_sla_ticket_list_activity_options( $options ) {
	
	$options['past_due']     = __( 'Past Due',		'wpas_sla' );
	$options['due_today']    = __( 'Due Today',		'wpas_sla' );
	$options['due_tomorrow'] = __( 'Due Tomorrow',	'wpas_sla' );
	
	return $options;
}

/**
 * Return all post types having sla id field
 * 
 * @return array
 */
function wpas_sla_get_post_types_having_sla_id_field() {
	return apply_filters( 'wpas_sla_get_post_types_having_sla_id_field', array(
		'wpas_issue_tracking',
		'wpass_status'
	));
}

/**
 * Return all taxonomies having sla id field
 * 
 * @return array
 */
function wpas_sla_get_taxonomies_having_sla_id_field() {
	return apply_filters( 'wpas_sla_get_taxonomies_having_sla_id_field', array(
		'department',
		'ticket_channel', 
		'ticket_priority',
		'sla_category'
	));
}

/**
 * Return all recipient types
 * 
 * @return array
 */
function wpas_sla_alert_recipient_types() {
	
	$_options = maybe_unserialize( get_option( 'wpas_options', array() ) );
		
	$aparty_1_label = isset( $_options[ 'label_for_first_addl_interested_party_email_singular' ] ) ? $_options[ 'label_for_first_addl_interested_party_email_singular' ] : __( 'Additional Interested Party Email #1', 'awesome-support' );
	$aparty_2_label = isset( $_options[ 'label_for_second_addl_interested_party_email_singular' ] ) ? $_options[ 'label_for_second_addl_interested_party_email_singular' ] : __( 'Additional Interested Party Email #2', 'awesome-support' );
	
	return apply_filters( 'wpas_sla_alert_recipient_types', array(
		
		'additional_party_1'	=> $aparty_1_label,
		'additional_party_2'	=> $aparty_2_label,
		'primary_agent'		=> __( 'Primary Agent',					'wpas_sla' ),
		'secondary_agent'	=> __( 'Secondary Agent',				'wpas_sla' ),
		'tertiary_agent'	=> __( 'Tertiary Agent',				'wpas_sla' ),
		'additional_emails'	=> __( 'Additional Emails On Ticket',	'wpas_sla' ),
		'additional_users'	=> __( 'Additional users on ticket',	'wpas_sla' ),
		'client'			=> __( 'Client/Customer',				'wpas_sla' ),
	));
}

/**
 * Return active day settings
 */
function wpas_sla_all_active_days() {
	
	$days = wpas_sla_get_workday_settings();
	
	$active_days = array();
	
	foreach( $days as $day_id => $day ) {
		
		$active = $day['active'];
		
		if( ( !$day['start_time'] || !$day['end_time'] ) && !$day['full_day'] ) {
			$active = false;
		} 
		
		if( $active ) {
			$active_days[] = $day;
		}
		
	}
	
	
	return $active_days;
	
}

/**
 * Return workday settings from backend settings
 * 
 * @return array
 */
function wpas_sla_get_workday_settings() {
	
	$days = wpas_week_days();
	
	$settings = array();
	
	foreach ( $days as $day_id => $day_name ) {
		
		$id_prefix = "workday_{$day_id}___";
		
		$default_active = wpas_sla_get_day_default_setting( $day_id, 'active' );
		$default_start_time = wpas_sla_get_day_default_setting( $day_id, 'start_time' );
		$default_end_time = wpas_sla_get_day_default_setting( $day_id, 'end_time' );
		
		$active      = wpas_sla_get_option( "{$id_prefix}active", $default_active );
		$start_time  = wpas_sla_get_option( "{$id_prefix}start_time", $default_start_time );
		$end_time    = wpas_sla_get_option( "{$id_prefix}end_time", $default_end_time );
		$cutoff_time = wpas_sla_get_option( "{$id_prefix}cutoff_time" );
		
		$no_cutoff_time_active = wpas_sla_get_option( "{$id_prefix}active_no_cutoff_time" );
		$full_day_active = wpas_sla_get_option( "{$id_prefix}active_full_day" );
		
		$full_day = false;
		if( $full_day_active ) {
			$start_time = 0;
			$end_time = 86400;
			$full_day = true;
			
			if( $no_cutoff_time_active ) {
				$cutoff_time = $end_time;
			}
		}
		
		if( !$cutoff_time ) {
			$cutoff_time = $end_time;
		}
		
		$settings[ $day_id ] = array(
			'active'		=> $active,
			'start_time'	=> $start_time,
			'end_time'		=> $end_time,
			'cutoff_time'	=> $cutoff_time,
			'full_day'		=> $full_day,
		);
		
	}
	
	return $settings;
}


add_action( 'init', 'wpas_sla_init', 9 );
/**
 * Add new custom fields and register hooks
 */
function wpas_sla_init() {
	$taxonomies = wpas_sla_get_taxonomies_having_sla_id_field();

	foreach ( $taxonomies as $tax ) {

		add_action( "{$tax}_add_form_fields",  'wpas_sla_add_tax_sla_id_field' );
		add_action( "{$tax}_edit_form_fields", 'wpas_sla_edit_tax_sla_id_field', 10, 2 );

		add_action( "created_{$tax}", 'wpas_sla_save_tax_sla_id', 10, 2 );
		add_action( "edited_{$tax}",  'wpas_sla_save_tax_sla_id', 10, 2 );

	}
	
	add_action( 'edit_user_profile',		    'user_profile_add_sla_field' , 10, 1 );
	add_action( 'show_user_profile',		    'user_profile_add_sla_field' , 10, 1 );

	add_action( 'personal_options_update',		'user_profile_save_sla_field' );
	add_action( 'edit_user_profile_update',		'user_profile_save_sla_field' );

	wpas_add_custom_field( 'sla_id', array(
				'core'           	=> false,
				'show_column'    	=> true,
				'column_callback'   => 'wpas_sla_post_custom_columns_content',
				'sortable_column'	=> true,
				'filterable'        => true,
				'hide_front_end' 	=> true,
				'log'            	=> true,
				'title'          	=> __( 'SLA ID', 'wpas_sla' )
			) );

	wpas_add_custom_field( 'due_date', array(
				'core'           	=> false,
				'show_column'    	=> true,
				'sortable_column'	=> true,
				'filterable'        => false,
				'hide_front_end' 	=> true,
				'log'            	=> true,
				'field_type'		=> 'date-field',
				'title'          	=> __( 'Due Date', 'wpas_sla' )
			) );

	wpas_add_custom_field( 'due_date_lock', array(
				'core'           	=> false,
				'show_column'    	=> false,
				'sortable_column'	=> true,
				'filterable'        => true,
				'hide_front_end' 	=> true,
				'log'            	=> true,
				'title'          	=> __( 'Due Date Lock', 'wpas_sla' )
			) );

	/* Set up the SLA Categories taxonomy*/
	$do_sla_categories 		= boolval( wpas_sla_get_option( 'sla_category_enabled', true ) );
	$do_sla_categories_fe	= boolval( wpas_sla_get_option( 'sla_category_enabled_fe', true ) );
	
	if ( true ===  $do_sla_categories ) {

		/* Filter the sla category taxonomy labels */
		$labels = apply_filters( 'wpas_sla_category_taxonomy_labels', array(
				'label'        => 'SLA Category',
				'name'         => 'sla_category',
				'label_plural' => 'SLA Categories'
		) );


		/** Create the custom field for sla categories */
		wpas_add_custom_field( 'sla_category', array(
			'core'                  => false,
			'show_column'           => true,
			'hide_front_end'        => !$do_sla_categories_fe,  //inverse of what the user specified in settings because of how this attribute works...
			'backend_only'          => !$do_sla_categories_fe,
			'log'                   => true,
			'field_type'            => 'taxonomy',
			'taxo_std'              => false,
			'column_callback'       => 'wpas_cf_sla_category',
			'label'                 => $labels[ 'label' ],
			'name'                  => $labels[ 'name' ],
			'label_plural'          => $labels[ 'label_plural' ],
			'taxo_hierarchical'     => true,
			'update_count_callback' => 'wpas_update_ticket_tag_terms_count',
			'sortable_column'       => true,
			'select2'               => false,
			'filterable'            => true,
			'title'           		=> $labels[ 'label' ]
		) );	
	}	

}

/**
 * Return add-on setting value
 * 
 * @param string $option
 * @param string $default
 * 
 * @return string|array
 */
function wpas_sla_get_option( $option, $default = "" ) {
		$options = maybe_unserialize( get_option( 'wpassla_options', array() ) );

		/* Return option value if exists */
		$value = isset( $options[ $option ] ) ? $options[ $option ] : $default;

		return apply_filters( 'wpassla_option_' . $option, $value );
}


/**
 * Return sla id from custom status
 * 
 * @param string $status
 * 
 * @return type
 */
function wpas_sla_get_sla_id_from_custom_status( $status ) {
	
	$args = array(
		'post_type' => 'wpass_status',
		'meta_query' => array(
			array(
				'key' => 'wpas_custom_status_id',
				'value' => $status,
				'compare' => '='
				)
			)
		);
	
	$query = new WP_Query( $args );
	
	$sla_id = '';
	if( !empty( $query->posts ) ) {
		$sla_id = wpas_sla_get_sla_id( $query->posts[0]->ID );
	}
	
	return $sla_id;
}


/**
 * Search sla if from client profile, ticket priority, custom status or ticket channel
 * 
 * @param int $ticket_id
 * @param array $old_data
 * 
 * @return array
 */
function wpas_sla_lookup_sla_id( $ticket_id, $old_data ) {
	
	$ticket = get_post( $ticket_id );
	
	$lookup_fields = 
	array(
		'sla_category' => array(
			'type' => 'tax',
			'old_value' => isset( $old_data['sla_category'] ) ? $old_data['sla_category'] : '',
			'new_value' => wpas_sla_get_post_terms( $ticket_id, 'sla_category', true, 'term_id' )
		),	
		'post_author' => array(
			'type' => 'user',
			'old_value' => isset( $old_data['post_author'] ) ? $old_data['post_author'] : '',
			'new_value' => $ticket->post_author
		),
		'ticket_priority' => array(
			'type' => 'tax',
			'old_value' => isset( $old_data['ticket_priority'] ) ? $old_data['ticket_priority'] : '',
			'new_value' => wpas_sla_get_post_terms( $ticket_id, 'ticket_priority', true, 'term_id' )
		),
		'custom_status' => array(
			'type' => 'post',
			'old_value' => isset( $old_data['status'] ) ? $old_data['status'] : '',
			'new_value' => $ticket->post_status
		),
		'department' => array(
			'type' => 'tax',
			'old_value' => isset( $old_data['department'] ) ? $old_data['department'] : '',
			'new_value' => wpas_sla_get_post_terms( $ticket_id, 'department', true, 'term_id' )
		),
		'ticket_channel' => array(
			'type' => 'tax',
			'old_value' => isset( $old_data['ticket_channel'] ) ? $old_data['ticket_channel'] : '',
			'new_value' => wpas_sla_get_post_terms( $ticket_id, 'ticket_channel', true, 'term_id' )
		)
		
	);
	
	$sla_id = '';
	$sla_origin = '';
	
	foreach( $lookup_fields as $field_name => $field_args ) {
		
		
		if( 'tax' == $field_args['type'] || 'post' == $field_args['type'] ) {
			
			if( 'custom_status' === $field_name ) {
				$sla_id = wpas_sla_get_sla_id_from_custom_status( $field_args['new_value'] );
			} else {
				
				if( 'tax' == $field_args['type'] ) {
					$sla_id = wpas_sla_get_sla_id( $field_args['new_value'],  $field_args['type'] );
				} else {
					$sla_id = wpas_sla_get_sla_id( $ticket->ID,  $field_args['type'] );
				}
			}
		} elseif( 'post_author' == $field_name ) {
			$sla_id = wpas_sla_get_sla_id( $ticket->post_author,  $field_args['type'] );
		}
		
		
		if( $sla_id ) {
			$sla_origin = $field_name;
			break;
		}
		
	}

	return array( 'sla_id' => $sla_id, 'origin' => $sla_origin );
}




/**
 * Get all ticket linked to a sla id
 * 
 * @param int $sla_id
 * 
 * @return array
 */
function wpas_sla_get_tickets( $sla_id ) {
	
	$args = array(
		'post_type'              => 'ticket',
		'post_status'            => 'any',
		'posts_per_page'         => -1,
		'meta_query'			 => array(
			array(
					'key'     => '_wpas_sla_id',
					'value'   => $sla_id,
					'compare' => '=',
					'type'    => 'CHAR'
			)
			
		)
	);

	
	$query = new WP_Query( $args );
	
	$tickets = !empty( $query->posts ) ? $query->posts : array();

	return $tickets;
	
}

add_filter( 'posts_clauses', 'wpas_sla_filter_tickets',		20, 2 );
/**
 * Filter ticket with new activity filter options
 * 
 * @global string $pagenow
 * @global object $wpdb
 * 
 * @param array $pieces
 * @param object $wp_query
 * 
 * @return array
 */
function wpas_sla_filter_tickets( $pieces , $wp_query ) {
	
	global $pagenow, $wpdb;
		
	$is_listing_query = isset( $wp_query->query['wpas_tickets_query'] ) && 'listing' === $wp_query->query['wpas_tickets_query'];
		
	if( !$is_listing_query ) {
			
		if ( ( ! is_admin()
				|| 'edit.php' !== $pagenow
				|| ! isset( $_GET['post_type'] )
				|| 'ticket' !== $_GET['post_type']
				|| ! $wp_query->is_main_query() )
		) {
				return $pieces;
		}
	}
	
	$activity = filter_input( INPUT_GET, 'activity', FILTER_SANITIZE_STRING );
	
	$activities = array( 'past_due', 'due_today', 'due_tomorrow' );
	
	if( $activity && in_array($activity, $activities) ) {
		
		$pieces['join'] .=  " INNER JOIN {$wpdb->postmeta} AS apm ON ( {$wpdb->posts}.ID = apm.post_id )";
		
		$where = "AND apm.meta_key = '_wpas_due_date' AND DATE(apm.meta_value) ";
		
		$today_date = DateTime::createFromFormat( SLA_DATE_FORMAT, date( SLA_DATE_FORMAT, time() ) );
		
		switch( $activity ) {
			case "past_due":
				$where .= "< '" . $today_date->format( SLA_DATE_FORMAT ) . "'";
				break;
			case "due_today":
			case "due_tomorrow":
				
				if( 'due_tomorrow' === $activity ) {
					$today_date->modify( "-1 day" );
				}
				$where .= "= '" . $today_date->format( SLA_DATE_FORMAT ) . "'";
				break;
			
		}
		
		$pieces['where'] =  $where . ' ' . $pieces['where'];
	}
	
	return $pieces;
}

/**
 * Return past due date tag
 * 
 * @param int $ticket_id
 * 
 * @return string
 */
function wpas_sla_ticket_past_due_tag( $ticket_id ) {
	$due_date = get_post_meta( $ticket_id, '_wpas_due_date', true );
	$ticket_status = get_post_meta( $ticket_id, '_wpas_status', true );
	
	$tag = "";
	if( $due_date ) {
		$due_time = strtotime( $due_date );
		$time = time();
		
		if( $due_time < $time && 'closed' != $ticket_status ) {
			
			$tag = "<span class=\"wpas-label\" style=\"background:#ff72cc\">" . __( 'Past Due', 'wpas_sla' ) . "</span>";
		}
	}
	
	return $tag;
}


add_filter( 'wpas_ticket_listing_activity_tags', 'wpas_sla_ticket_listing_add_past_due_tag', 11, 2 );
/**
 * Add past due tag in ticket listing activity column
 * 
 * @param array $tags
 * @param int $ticket_id
 * 
 * @return array
 */
function wpas_sla_ticket_listing_add_past_due_tag( $tags , $ticket_id ) {
	
	$tag = wpas_sla_ticket_past_due_tag( $ticket_id );
	
	if( $tag ) {
		array_push( $tags, $tag );
	}
	
	return $tags;
	
}

add_action( 'wpas_after_close_ticket', 'wpas_sla_after_close_ticket', 11, 3 );
/**
 * Mark ticket as closed before or after due date
 * 
 * @param int $ticket_id
 * @param boolean $update
 * @param int $user_id
 * 
 * @return void
 */
function wpas_sla_after_close_ticket( $ticket_id, $update, $user_id ) {
	
	
	$due_date = get_post_meta( $ticket_id, '_wpas_due_date', true );
	
	if( !$update || !$due_date ) {
		return;
	}
	
	
	$close_date = get_post_meta( $ticket_id, '_ticket_closed_on', true );
	
	$ticket_closed_before_due_date = 'yes';
	
	if( strtotime( $close_date ) > strtotime( $due_date ) ) {
		$ticket_closed_before_due_date = 'no';
	}
	
	update_post_meta( $ticket_id, 'closed_before_due_date', $ticket_closed_before_due_date );
	
}

add_action( 'wpas_after_reopen_ticket', 'wpas_sla_after_reopen_ticket', 11, 2 );
/**
 * Remove mark of closed before or after due date
 * 
 * @param int $ticket_id
 * @param boolean $update
 * 
 * @return void
 */
function wpas_sla_after_reopen_ticket( $ticket_id, $update ) {
	
	if( !$update ) {
		return;
	}
	
	delete_post_meta( $ticket_id, 'closed_before_due_date' );
	
}

add_action( 'pre_post_update', 'wpas_sla_prepare_ticket_update', 11, 2 );
/**
 * cache ticket data before a ticket is updated
 * 
 * @param int $post_id
 * @param array $data
 * 
 * @return void
 */
function wpas_sla_prepare_ticket_update( $post_id, $data ) {
	
	
	if( 'ticket' !== $data['post_type'] ) {
		return;
	}
	
	$status = $data['post_status'];
	$post_author = $data['post_author'];
	
	$ticket_priority = wpas_sla_get_post_terms( $post_id, 'ticket_priority', true, 'term_id' );
	$ticket_channel = wpas_sla_get_post_terms( $post_id, 'ticket_channel', true, 'term_id' );
	$sla_category = wpas_sla_get_post_terms( $post_id, 'sla_category', true, 'term_id' );	
	
	$sla_id = get_post_meta( $post_id, '_wpas_sla_id', true );
	$due_date = get_post_meta( $post_id, '_wpas_due_date', true );
	$due_date_lock = get_post_meta( $post_id, '_wpas_due_date_lock', true );
	
	$_data = compact( 'sla_category', 'ticket_priority', 'department', 'ticket_channel', 'status', 'post_author', 'sla_id', 'due_date', 'due_date_lock' );
	
	$GLOBALS['wpas_sla_old_ticket_data'] = $_data;
}



add_action( 'save_post_ticket', 'wpas_sla_prepare_ticket_save', 4, 3 );

/**
 * Cache ticket data before a ticket is saved for new and existing tickets
 * 
 * @param int $post_id
 * @param array $data
 * 
 * @return void
 */
function wpas_sla_prepare_ticket_save( $post_id, $post, $update ) {
	global $wpas_sla_old_ticket_data;
	
	
	if( $update ) {
		$_POST['wpas_sla_id'] = isset( $wpas_sla_old_ticket_data['sla_id'] ) ? $wpas_sla_old_ticket_data['sla_id'] : '';
		$_POST['wpas_due_date'] = isset( $wpas_sla_old_ticket_data['due_date'] ) ? $wpas_sla_old_ticket_data['due_date'] : '';
		
		if( !current_user_can( 'ticket_edit_due_date' ) ) {
			$_POST['wpas_due_date_lock'] = $wpas_sla_old_ticket_data['due_date_lock'];
		}
	} else {
		
		if( !current_user_can( 'ticket_edit_due_date' ) ) {
			$_POST['wpas_due_date_lock'] = '';
		}
		
		$wpas_sla_old_ticket_data = array();
	} 
	
}


add_action( 'wpas_submission_form_inside_after', 'wpas_sla_ticket_form_add_nonce_field' );
/**
 * Add nonce field to search sla posts via ajax
 */
function wpas_sla_ticket_form_add_nonce_field() {
	wp_nonce_field( 'wpas-sla-ticket-form', 'wpas_sla_ticket_form_nonce' );
}


add_action( 'wpas_open_ticket_after', 'wpas_sla_save_ticket' ); // Calls once a new ticket is created from front-end
add_action( 'wpas_post_new_ticket_admin', 'wpas_sla_save_ticket' ); // Calls once new ticket created from back-end
add_action( 'wpas_ticket_after_update_admin_success', 'wpas_sla_save_ticket' ); // Calls Once existing ticket is updated from back-end
/**
 * Store sla data and calculate due date if data is updated or its a new ticket
 * 
 * @global array $wpas_sla_old_ticket_data
 * 
 * @param int $post_id
 * 
 * @return void
 */
function wpas_sla_save_ticket( $post_id ) {
	global $wpas_sla_old_ticket_data, $current_user;
	
	
	if( !$current_user || !wpas_sla_ajax_nonce_check( 'wpas-sla-ticket-form', 'wpas_sla_ticket_form_nonce', false ) ) {
		return;
	}
	
	$custom_sla_id = filter_input( INPUT_POST, 'sla_id', FILTER_SANITIZE_NUMBER_INT );
	$custom_due_date = filter_input( INPUT_POST, 'sla_due_date' );
	$due_date_locked = get_post_meta( $post_id, '_wpas_due_date_lock', true );
	
	$old_sla_id	= isset( $wpas_sla_old_ticket_data['sla_id'] ) ? $wpas_sla_old_ticket_data['sla_id'] : '';
	$old_due_date = isset( $wpas_sla_old_ticket_data['due_date'] ) ? $wpas_sla_old_ticket_data['due_date'] : '';
	
	$sla_id_changed = false;
	
	$sla_id_change_type = 'auto';
	$due_date_change_type = 'auto';
	
	$sla_id_origin = '';
	
	if( $custom_sla_id && $custom_sla_id != $old_sla_id && $current_user->has_cap( 'ticket_sla_admin' ) ) {
		
		$sla_id = $custom_sla_id;
		$sla_id_change_type = 'manual';
		$sla_id_changed = true;
		$sla_id_origin = 'manual';
	} else {
		
		$sla_lookup_result		= wpas_sla_lookup_sla_id( $post_id, $wpas_sla_old_ticket_data );
		
		$sla_id = $sla_lookup_result['sla_id'];
		$sla_id_origin = $sla_lookup_result['origin'];
		
		if( $sla_id && $sla_id != $old_sla_id ) {
			$sla_id_changed = true;
		}
		
	}
	
	
	if( $custom_due_date != $old_due_date ) {
		$due_date_change_type = 'manual';
	}
	
	if( $sla_id_changed ) {
		wpas_sla_update_ticket_sla_id( $post_id, $sla_id, $sla_id_origin );
	}
	
	// update due date if due date is not locked
	if( !$due_date_locked ) {
		if( 'manual' === $due_date_change_type ) {
			if( $current_user->has_cap( 'ticket_edit_due_date' ) ) {
				wpas_sla_update_ticket_due_date( $post_id, $custom_due_date );
			}
		} elseif( $sla_id_changed ) {
				$due_date = wpas_sla_calculate_ticket_due_date( $post_id );
		}
	}
	
	wpas_sla_maybe_add_log( $post_id );

}

add_action( 'wpas_it_after_issue_assigned_to_ticket', 'wpas_sla_assign_sla_id_from_issue', 11, 2 );

/**
 * Assign sla id from issue once an issue is assigned to a ticket
 * 
 * @global object $current_user
 * 
 * @param int $ticket_id
 * @param int $issue_id
 * 
 * @return void
 */
function wpas_sla_assign_sla_id_from_issue( $ticket_id, $issue_id ) {
	global $current_user;
	
	
	$sla_id = wpas_sla_get_sla_id( $issue_id );
	
	if( !$current_user || !$sla_id ) {
		return;
	}
	
	$old_sla_id = get_post_meta( $ticket_id, '_wpas_sla_id', true );
	
	if( $sla_id != $old_sla_id ) {
		
		wpas_sla_update_ticket_sla_id( $ticket_id, $sla_id, 'ticket_issue' );
		
		
		$due_date_locked = get_post_meta( $ticket_id, '_wpas_due_date_lock', true );
		
		if( !$due_date_locked ) {
			wpas_sla_calculate_ticket_due_date( $ticket_id );
		}
	}
	
	wpas_sla_maybe_add_log( $ticket_id );
	
}

/**
 * Calculate and store ticket due date
 * 
 * @param int $ticket_id
 * 
 * @return string
 */
function wpas_sla_calculate_ticket_due_date( $ticket_id ) {
	
	// don't calculate due date if all days are inactive
	if( 0 === count( wpas_sla_all_active_days() ) ) {
		return;
	}
	
	$ticket = get_post( $ticket_id );
	
	$sla_id = get_post_meta( $ticket_id, '_wpas_sla_id', true );
	$time_frame = get_post_meta( $sla_id, 'time_frame', true );
	
	$ticket_time = $ticket->post_date;
	
	$obj_due_date = new WPAS_SLA_DUE_DATE( $time_frame, $ticket_time );
	
	$due_date = $obj_due_date->calculate();
	
	wpas_sla_update_ticket_due_date( $ticket_id, $due_date );
	
	return $due_date;
	
}

/**
 * Update ticket due date
 * 
 * @param int $ticket_id
 * @param string $due_date
 */
function wpas_sla_update_ticket_due_date( $ticket_id, $due_date ) {
	global $sla_custom_fields_log_data;
	
	$sla_custom_fields_log_data['due_date'] = array(
		'old' => get_post_meta( $ticket_id, '_wpas_due_date', true ),
		'new' => $due_date
	);
	
	update_post_meta( $ticket_id, '_wpas_due_date', $due_date );
	
	SLA_Ticket_Alert::set_ticket_alerts( $ticket_id );
}


/**
 * Update ticket sla id
 * 
 * @param int $ticket_id
 * @param string $due_date
 */
function wpas_sla_update_ticket_sla_id( $ticket_id, $sla_id, $sla_id_origin = '' ) {
	global $sla_custom_fields_log_data;
	
	$old_sla_id = get_post_meta( $ticket_id, '_wpas_sla_id', true );
	
	$sla_custom_fields_log_data['sla_id'] = array(
		'old' => $old_sla_id,
		'new' => $sla_id
	);
	
	
	update_post_meta( $ticket_id, '_wpas_sla_id', $sla_id );
	update_post_meta( $ticket_id, 'sla_id_origin', $sla_id_origin );
	do_action( 'wpas_sla_ticket_sla_id_changed', $ticket_id, $old_sla_id, $sla_id );
	
}

add_action( 'wp_ajax_wpas_sla_get_test_due_date', 'wpas_sla_get_test_due_date' );
/**
 * Handle ajax request to calculate test due date
 */
function wpas_sla_get_test_due_date() {
	
	wpas_sla_ajax_nonce_check( 'wpas-sla-get-test-due-date' ) ;
	
	$ticket_id = filter_input( INPUT_POST, 'ticket_id', FILTER_SANITIZE_NUMBER_INT );
	$ticket_receipt_date = filter_input( INPUT_POST, 'ticket_receipt_date', FILTER_SANITIZE_STRING );
	
	$error = "";
	
	if( !$ticket_id ) { 
		$error = __( 'Ticket does not exist', 'wpas_sla' );
	} elseif( !$ticket_receipt_date ) {
		$error = __( 'Please select ticket receipt date', 'wpas_sla' );
	}
		
	if( $error ) {
		wp_send_json_error( array( 'msg' => $error ) );
		die();
	}
	
	
	$sla_id = get_post_meta( $ticket_id, '_wpas_sla_id', true );
	
	if( $sla_id ) {
		$time_frame = get_post_meta( $sla_id, 'time_frame', true );
	}
	
	if( !$sla_id ) {
		$error = __( 'No service level agreement is set in this ticket.', 'wpas_sla' );
	} elseif( !$time_frame ) {
		$error = __( 'Please add time frame to attached service level agreement.', 'wpas_sla' );
	}  elseif( 0 === count( wpas_sla_all_active_days() ) ) {
		$error = __( 'Please setup workdays to calculate due day.', 'wpas_sla' );
	}
	
	if( $error ) {
		wp_send_json_error( array( 'msg' => $error ) );
		die();
	}
	
	$ticket_time = date( SLA_DATE_FORMAT . ' ' . SLA_TIME_FORMAT , strtotime( $ticket_receipt_date ) );
	
	$obj_due_date = new WPAS_SLA_DUE_DATE( $time_frame, $ticket_time );
	$due_date = $obj_due_date->calculate();
	
	wp_send_json_success( array( 'due_date' => $due_date ) );
	die();
}



add_action( 'save_post', 'wpas_sla_save_sla_id' );
/**
 * save sla id
 * 
 * @param int $post_id
 * 
 * @return void
 */
function wpas_sla_save_sla_id( $post_id ) {
	
	// Verify nonce
	if ( !wpas_sla_ajax_nonce_check( 'wpas-sla-sla-id', 'sla_id_nonce', false ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) || defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	
	$post_types = wpas_sla_get_post_types_having_sla_id_field();
	$post = get_post( $post_id );
	
	if ( in_array( $post->post_type, $post_types ) ) {
		$sla_id = filter_input( INPUT_POST, 'sla_id', FILTER_SANITIZE_NUMBER_INT );
		update_post_meta( $post_id, 'sla_id', $sla_id );
	}
}


/**
 * Return all sla posts
 * 
 * @param array $args
 * 
 * @return array
 */
function wpas_sla_get_sla_posts( $args = array() ) {
	
	$defaults = array(
		'post_type'              => 'wpas_sla',
		'posts_per_page'         => - 1
	);

	$args  = wp_parse_args( $args, $defaults );
	
	
	$query = new WP_Query( $args );
	if ( empty( $query->posts ) ) {
		return array();
	} else {
		return $query->posts;
	}
	
}

/**
 * Search sla posts
 * 
 * @param string $q
 * 
 * @return array
 */
function wpas_sla_search_sla_posts( $q ) {
	$args = array(
		'post_type'			=> 'wpas_sla',
		'posts_per_page'	=> - 1
	);
	
	$results = array();
	
	$args['s'] = $q;
	
	require( WPAS_PATH . 'includes/admin/functions-post.php' );
	
	$results = wpas_sla_get_sla_posts( $args );
	
	if( is_numeric( $q ) ) {
		unset( $args['s'] );
		$args['post__in'] = array( $q );
		$results = array_merge( $args, wpas_sla_get_sla_posts( $args ) );
	}
	
	return $results;
}

add_action( 'wp_ajax_wpas_get_sla', 'wpas_sla_ajax_get_sla' );
/**
 * Return sla posts for sla id dropdown
 * 
 * @return void
 */
function wpas_sla_ajax_get_sla() {
	
	
	
	wpas_sla_ajax_nonce_check( 'wpas-get-sla' );
	
	$q = filter_input( INPUT_POST, 'q' , FILTER_SANITIZE_STRING );
	
	
	if( !( $q && strlen( $q ) >= 3 ) ) {
		return;
	}
	
	$result = array();
	
	$posts = wpas_sla_search_sla_posts( $q );
	
	if ( count( $posts ) > 0 ) {
		
		foreach ( $posts as $post ) {
			$result[] = array(
			    'id'	=> $post->ID,
			    'text'	=> $post->post_title
			);
		}
	}
	
	echo json_encode( $result );
	die();
}

/**
 * Return sla id from posts or taxonomies
 * 
 * @param int $id
 * @param string $type
 * 
 * @return int
 */
function wpas_sla_get_sla_id( $id, $type = 'post' ) {
	
	$sla_id = "";
	
	if( 'post' === $type ) {
		if( 'ticket' === get_post_type( $id ) ) {
			$sla_id = get_post_meta( $id, '_wpas_sla_id', true );
		} else {
			$sla_id = get_post_meta( $id, 'sla_id', true );
		}
	} elseif ( 'tax' === $type ) {
		$sla_id = get_term_meta( $id, 'sla_id', true );
	} elseif ( 'user' === $type ) {
		$sla_id = get_user_option( 'sla_id', $id );
	} 
	
	return $sla_id;
}

/**
 * Generate sla id dropdown
 * 
 * @param int $id
 * @param string $type
 */
function wpas_sla_sla_id_field( $id = '', $type = 'post' ) {
	
	$sla_id = wpas_sla_get_sla_id( $id, $type );
	
	$select2 = wpas_sla_get_option( 'sla_id_dropdown_select2', false );
	
	if( $select2 ) {
	
		$options = "";

		if( $sla_id ) {
			$sla_post = get_post( $sla_id );

			$options = sprintf( '<option selected="selected" value="%s">%s</option>', $sla_id, $sla_post->post_title );
		}


		$dd_atts = array(
			'name'      => 'sla_id',
			'id'        => 'sla_id',
			'select2'   => true,
			'data_attr' => array( 'opt-type' => 'sla_id_picker' )
		);

		echo wpas_dropdown( $dd_atts, $options );
	} else {
		wp_dropdown_pages(array(
					'post_type'			=> 'wpas_sla', 
					'selected'			=> $sla_id, 
					'name'				=> 'sla_id', 
					'show_option_none' 	=> __( 'Select SLA ID', 'wpas_sla' ), 
				));
	}
	
	wp_nonce_field( 'wpas-sla-sla-id', 'sla_id_nonce' );
	
}




add_action( 'add_meta_boxes', 'wpas_sla_add_meta_boxes' );
/**
 * Register metaboxes
 *
 * @since 1.0.0
 * @return void
 */
function wpas_sla_add_meta_boxes() {
	
	$post_types = wpas_sla_get_post_types_having_sla_id_field();
	
	add_meta_box( 'wpas_sla_meta_box_settings', __( 'SLA', 'wpas_sla' ), 'wpas_sla_meta_box_sla', $post_types, 'side' );
	
	if ( current_user_can( 'ticket_sla_admin' ) ) {
	
		add_meta_box( 'wpas_sla_test_sla_meta_box', __( 'SLA Test Due Date', 'wpas_sla' ), 'wpas_sla_test_sla_meta_box', 'ticket', 'side' );
	}
}


/**
 * Save sla id from user profile
 * 
 * @param int $user_id
 */
function user_profile_save_sla_field( $user_id ) {
	if ( current_user_can( 'edit_user', $user_id ) ) { 
			
			if( isset( $_POST['sla_id'] ) ) {
				
				$sla_id = filter_input( INPUT_POST, 'sla_id', FILTER_SANITIZE_NUMBER_INT );
				update_user_option( $user_id, 'sla_id', $sla_id );
			}
		}
}

/**
 * Save sla id from taxonomies
 * 
 * @param int $term_id
 * @param int $tt_id
 * 
 * @return void
 */
function wpas_sla_save_tax_sla_id( $term_id, $tt_id ) {
	
	if( !wpas_sla_ajax_nonce_check( 'wpas-sla-sla-id', 'sla_id_nonce', false ) ) {
		return;
	}
	
	if( isset( $_POST['sla_id'] ) ) {
		
		$sla_id = filter_input( INPUT_POST, 'sla_id', FILTER_SANITIZE_NUMBER_INT );
		
		update_term_meta( $term_id, 'sla_id', $sla_id );
	}
}

/**
 * Add sla id field in user profile
 * 
 * @param object $user
 */
function user_profile_add_sla_field( $user ) {

	if ( current_user_can( 'ticket_sla_admin' ) ) {
		?>
		<div class="sla_id_picker">
				<p>
					<label> <strong><?php _e( 'SLA ID', 'wpas_sla' ) ?> : </strong> </label>
					<div><?php wpas_sla_sla_id_field( $user->ID, 'user' ); ?></div>
				</p>
		</div>
		<?php 
	}
	
}

/**
 * Add sla metabox content
 * 
 * @global int $post_id
 */
function wpas_sla_meta_box_sla() {
	
	global $post_id;
	?>

	<div class="sla_id_picker">
			<p>
				<label> <strong><?php _e( 'SLA ID', 'wpas_sla' ) ?> : </strong> </label>
				<div><?php wpas_sla_sla_id_field( $post_id ); ?></div>
						
			</p>
	</div>
	<?php
	
	do_action( 'wpas_after_sla_field' );
	
}


add_action( 'wpas_backend_ticket_status_before_actions', 'wpas_sla_ticket_sla_fields' );
/**
 * Add sla id, due date and lock due fields in ticket details metabox
 * 
 * @param int $ticket_id
 */
function wpas_sla_ticket_sla_fields( $ticket_id ) {
	include WPAS_SLA_PATH . 'includes/templates/ticket_sla_fields.php';
}


add_action( 'wpas_after_sla_field', 'wpas_sla_add_sla_dropdown_nonce_field' );
/*@TODO: Need function header here*/
function wpas_sla_add_sla_dropdown_nonce_field() {
	wp_nonce_field( 'wpas-get-sla', 'sla_nonce_wpas_get_sla' );
}


/**
 * Add test due date metabox in edit ticket page
 */
function wpas_sla_test_sla_meta_box() {
	include WPAS_SLA_PATH . 'includes/templates/ticket_sla_test_metabox.php';
}


/**
 * Add sla id field in add term form
 * 
 * @param String $taxonomy
 */
function wpas_sla_add_tax_sla_id_field( $taxonomy ) {
	
	?>

	<div class="form-field term-sla_id-wrap">
		<label for="term-sla_id"><?php echo _e( 'SLA ID', 'awesome-support' ); ?></label>
		
		<?php wpas_sla_sla_id_field( '', 'tax' ); ?>
		
		<p class="description"><?php echo _e( 'Set SLA ID.', 'awesome-support' ); ?></p>
	</div>

	<?php
	
	do_action( 'wpas_after_sla_field' );
}

/**
 * Add sla id field in edit term form
 * 
 * @param Object $term
 * @param String $taxonomy
 */
function wpas_sla_edit_tax_sla_id_field( $term, $taxonomy ) {
	
	?>

	<tr class="form-field term-color-wrap">
		<th scope="row" valign="top">
			<label for="term-sla_id"><?php echo _e( 'SLA ID', 'awesome-support' ); ?></label>
		</th>
		<td>
			<?php wpas_sla_sla_id_field( $term->term_id , 'tax' ); ?>
			<p class="description"><?php echo _e( 'Set SLA ID.', 'awesome-support' ); ?></p>
		</td>
	</tr>

	<?php
	
	do_action( 'wpas_after_sla_field' );
}


/**
 * Return taxonomy terms by term field
 * 
 * @param string $field
 * @param string $value
 * @param string $taxonomy
 * 
 * @return array|object
 */
function wpas_sla_get_term_by( $field, $value, $taxonomy ) {
	$term = get_term_by( $field, $value, $taxonomy );
		
	return ( is_wp_error( $term ) || !$term ? array() : $term );
}

/**
 * Return post terms
 * 
 * @param int $post_id
 * @param taxonomy $tax
 * @param boolean $single
 * @param string $field
 * 
 * @return mixed
 */
function wpas_sla_get_post_terms( $post_id, $tax, $single=true, $field = '' ) {
	
	$terms = wp_get_post_terms( $post_id, $tax );
	
	$terms = !empty( $terms ) && !is_wp_error( $terms ) ? $terms : array();
	
	if( $single && !empty( $terms ) ) {
		
		if( $field ) {
			return $terms[0]->{$field};
		} else {
			return $terms[0];
		}
	}
	
	$value = "";
	
	if( $single ) {
		
		if( $field ) {
			$value = empty( $terms ) ? '' : $terms[0]->{$field};
		} else {
			$value = empty( $terms ) ? Array() : $terms[0];
		}
		
		return $value;
	}
	
	return $terms;
}

/**
 * Check ajax nonce
 * 
 * @param string $name
 * @param string $key
 * @param boolean $die
 * 
 * @return boolean
 */
function wpas_sla_ajax_nonce_check( $name, $key = 'security', $die = true ) {
	
	if( !check_ajax_referer( $name, $key, false ) ) {
		
		if( $die ) {
			wp_send_json_error( array( 'msg' => __( "You don't have access to perform this action.", 'wpas_sla' ) ) );
			die();
		}
		
		return false;
	}
	
	return true;
}

/**
 * Return link tag to sla edit post page
 * 
 * @param int $sla_id
 * 
 * @return string
 */
function sla_id_post_link( $sla_id ) {
	
	if( !$sla_id ) {
		return;
	}
	
	$sla_post = get_post( $sla_id );
	
	$link = '';
	
	if( $sla_post ) {
		$url = add_query_arg( array(
			'action'    => 'edit',
			'post'      => $sla_id
		), admin_url( 'post.php' ) );
			
			
		$link = sprintf( '<a href="%s">%s</a>', $url, $sla_post->post_title );
	}
	
	return $link;
}



add_filter( 'manage_edit-ticket_priority_columns' , 'wpas_sla_taxonomy_add_sla_id_column' );
add_filter( 'manage_edit-ticket_channel_columns' , 'wpas_sla_taxonomy_add_sla_id_column' );
add_filter( 'manage_edit-department_columns' , 'wpas_sla_taxonomy_add_sla_id_column' );
add_filter( 'manage_edit-sla_category_columns' , 'wpas_sla_taxonomy_add_sla_id_column' );
add_filter( 'manage_wpass_status_posts_columns', 'wpas_sla_post_add_sla_id_column' );
add_filter( 'manage_ticket_posts_columns', 'wpas_sla_ticket_add_sla_id_column' );

/**
 * Add sla id column in priority and channel taxonomy
 * 
 * @param array $columns
 * 
 * @return array
 */
function wpas_sla_taxonomy_add_sla_id_column( $columns ) {
	
	$columns['sla_id'] = __( 'SLA ID', 'wpas_sla' );
	
	return $columns;
}

/**
 * Add sla id column in custom status post
 * 
 * @param array $columns
 * 
 * @return array
 */
function wpas_sla_post_add_sla_id_column( $columns ) {
	
	unset( $columns['date'] );
	
	$columns['sla_id'] = __( 'SLA ID', 'wpas_sla' );
	$columns['date'] = __( 'Date', 'wpas_sla' );
	
	return $columns;
}

/**
 * Add sla name column in ticket listing
 * 
 * @param array $columns
 * 
 * @return array
 */
function wpas_sla_ticket_add_sla_id_column( $columns ) {
	
	// Only add sla column if its enabled from settings page
	if( wpas_sla_get_option( 'sla_ticket_listing_column_enabled', false ) ) {
		unset( $columns['date'] );
	
		$columns['sla_id'] = __( 'SLA', 'wpas_sla' );
		$columns['date'] = __( 'Date', 'wpas_sla' );

	}
	
	return $columns;
}



add_filter( 'manage_ticket_priority_custom_column', 'wpas_sla_taxonomy_custom_columns_content', 10, 3 );
add_filter( 'manage_ticket_channel_custom_column', 'wpas_sla_taxonomy_custom_columns_content', 10, 3 );
add_filter( 'manage_department_custom_column', 'wpas_sla_taxonomy_custom_columns_content', 10, 3 );
add_filter( 'manage_sla_category_custom_column', 'wpas_sla_taxonomy_custom_columns_content', 10, 3 );
/**
 * Return content for sla id column in taxonomies
 * 
 * @param string $content
 * @param string $column_name
 * @param int $term_id
 * 
 * @return string
 */
function wpas_sla_taxonomy_custom_columns_content( $content, $column_name, $term_id ) {
	
	if( 'sla_id' === $column_name ) {
		
		$sla_id = wpas_sla_get_sla_id( $term_id, 'tax' );
		
		$content = sla_id_post_link( $sla_id );
	}
	
	return $content;
}

add_action( 'manage_wpass_status_posts_custom_column' , 'wpas_sla_post_custom_columns_content', 10, 2 );
/**
 * Print sla id column content for posts
 * 
 * @param string $column
 * @param int $post_id
 */
function wpas_sla_post_custom_columns_content( $column, $post_id ) {
	
	
	if( 'sla_id' === $column ) {
		
		$sla_id = wpas_sla_get_sla_id( $post_id, 'post' );
		
		echo sla_id_post_link( $sla_id );
	}
	
}

add_action( 'wpas_backend_ticket_content_after', 'wpas_sla_ticket_content_after',	20, 1 );

/**
 * Add past due tag in edit ticket page
 * @param type $ticket_id
 */
function wpas_sla_ticket_content_after( $ticket_id ) {
	echo wpas_sla_ticket_past_due_tag( $ticket_id );
}

/**
 * Add ticket logs once sla custom fields changed
 * 
 * @global array $sla_custom_fields_log_data
 * 
 * @param int $ticket_id
 * 
 * @return void
 */
function wpas_sla_maybe_add_log( $ticket_id ) {
	global $sla_custom_fields_log_data;
	
	if( !isset( $sla_custom_fields_log_data ) || !is_array( $sla_custom_fields_log_data ) || empty( $sla_custom_fields_log_data ) ) {
		return;
	}
	
	$custom_fields = WPAS()->custom_fields->get_custom_fields();

	
	$sla_custom_fields = array( 'sla_id', 'due_date' );
	
	$logs = array();
	
	foreach( $sla_custom_fields as $field_name ) {
		
		$field = $custom_fields[ $field_name ];
		
		if( !isset( $sla_custom_fields_log_data[ $field_name ] ) || !( true === $field['args']['log'] ) ) {
			continue;
		}
		
		$values = $sla_custom_fields_log_data[ $field_name ];
		
		if( 'sla_id' === $field_name ) {
			$sla_link = get_edit_post_link( $values['new'] );
			$edit_ticket_link = '<a href="' . esc_url( $sla_link ) . '">' . get_the_title( $values['new'] ) . '</a>';
		}
		
		
		if( $values['old'] &&  $values['new'] ) {
			$log_action = 'updated';
		} elseif( $values['old'] &&  !$values['new'] ) {
			$log_action = 'deleted';
		} elseif( $values['new'] ) {
			$log_action = 'added';
		}
		
		
		/* Only add this to the log if something was done to the field value */
		if ( $log_action ) {
			$logs[] = array(
				'action'   => $log_action,
				'label'    => wpas_get_field_title( $field ),
				'value'    => (  'sla_id' === $field_name ) ? $edit_ticket_link : $values['new'],
				'field_id' => $field['name']
			);
		}
		
	}
	
	if ( ! empty( $logs ) ) {
		wpas_log( $ticket_id, $logs );
	}
	
}


?>