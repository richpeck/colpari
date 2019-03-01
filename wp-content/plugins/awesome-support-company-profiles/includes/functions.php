<?php

add_action( 'init', 'wpas_cp_init_first_activated', 30, 0 );

/**
 * Configure add-on once activated
 */
function wpas_cp_init_first_activated() {
	
	$configured = get_option( 'wpas_cp_configured' );
	
	if( 'no' === $configured ) {
		
		// Adding default support user divisions
		$default_divisions = wpas_cp_default_divisions();
		foreach ( $default_divisions as $division ) {
			wp_insert_term( $division, 'wpas_cp_su_division' );
		}
		
		// Adding default support user reporting groups
		$default_reporting_groups = wpas_cp_default_reporting_groups();
		foreach ( $default_reporting_groups as $group ) {
			wp_insert_term( $group, 'wpas_cp_su_reporting_group' );
		}
		
		
		wpas_cp_create_pages();
		
		update_option( 'wpas_cp_configured', 'yes' );
		
	}
}

add_action( 'init', 'wpas_cp_init', 12 );
/**
 * Add new custom fields
 */
function wpas_cp_init() {
	
	wpas_add_custom_field( 'company_id', array(
				'core'           	=> false,
				'show_column'    	=> false,
				'sortable_column'	=> false,
				'filterable'        => false,
				'hide_front_end' 	=> false,
				'column_callback'	=> 'wpas_cf_display_company_name',
				'log'            	=> true,
				'field_type'		=> 'select',
				'title'          	=> __( 'Company', 'wpas_cp' )
			) );
	

}

/**
 * Create pages for front-end
 */
function wpas_cp_create_pages() {

	$manage_page_id = get_option( 'cp_manage_page' );
	
	if ( !$manage_page_id ) {

		$manage_page_args = array(
				'post_content'   => '[manage_company_profiles]',
				'post_title'     => wp_strip_all_tags( __( 'Manage Company Profiles', 'wpas_cp' ) ),
				'post_name'      => sanitize_title( __( 'Manage Company Profiles', 'wpas_cp' ) ),
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'ping_status'    => 'closed',
				'comment_status' => 'closed'
		);

		$manage_page_id = wp_insert_post( $manage_page_args, true );

		if ( ! is_wp_error( $manage_page_id ) && is_int( $manage_page_id ) ) {
			update_option( 'cp_manage_page', $manage_page_id );
		}

	}
	

	$add_company_page_id = get_option( 'cp_add_company_page' );
	if ( !$add_company_page_id ) {

		$add_company_args = array(
				'post_content'   => '[add_company_profile]',
				'post_title'     => wp_strip_all_tags( __( 'Add Company Profile', 'wpas_cp' ) ),
				'post_name'      => sanitize_title( __( 'Add Company Profile', 'wpas_cp' ) ),
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'ping_status'    => 'closed',
				'comment_status' => 'closed'
		);

		$add_company_page_id = wp_insert_post( $add_company_args, true );

		if ( ! is_wp_error( $add_company_page_id ) && is_int( $add_company_page_id ) ) {
			update_option( 'cp_add_company_page', $add_company_page_id );
		}
	}
	
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
function wpas_cp_ajax_nonce_check( $name, $key = 'security', $die = true ) {
	
	if( !check_ajax_referer( $name, $key, false ) || !current_user_can( 'edit_ticket' ) ) {
		
		if( $die ) {
			wp_send_json_error( array( 'msg' => __( "You don't have access to perform this action.", 'wpas_cp' ) ) );
			die();
		}
		
		return false;
	}
	
	return true;
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
function wpas_cp_ajax_nonce_check_fe( $name, $key = 'security', $die = true ) {
	
	if( !check_ajax_referer( $name, $key, false ) ) {
		
		if( $die ) {
			wp_send_json_error( array( 'msg' => __( "You don't have access to perform this action.", 'wpas_cp' ) ) );
			die();
		}
		
		return false;
	}
	
	return true;
}

/**
 * Return all company profile user types
 * 
 * @return array
 */
function wpas_cp_user_types() {
	
	return apply_filters( '', array(
		'employee'		=> __( 'EMPLOYEE',   'wpas_cp'), 
		'partner'		=> __( 'PARTNER',    'wpas_cp'), 
		'consultant'	=> __( 'CONSULTANT', 'wpas_cp'), 
		'other'			=> __( 'OTHER',      'wpas_cp'), 
	) );
}

/**
 * Return default devisions
 * 
 * @return array
 */
function wpas_cp_default_divisions() {
	return apply_filters( 'wpas_cp_default_divisions' , array( 
		__( 'All', 'wpas_cp' )
		) );
}

/**
 * Return default reporting groups
 * 
 * @return array
 */
function wpas_cp_default_reporting_groups() {
	return apply_filters( 'wpas_cp_default_reporting_groups' , array( 
		__( 'General', 'wpas_cp' )
		) );
}

/**
 * Return divisions dropdown
 * 
 * @param array $args
 * 
 * @return string
 */
function wpas_cp_user_divisions_dropdown( $args ) {
	$defaults = array(
		'name'          => 'divisions',
		'taxonomy'		=> 'wpas_cp_su_division',
		'multiple'		=> true, 
		'hide_empty'	=> false
	);

	$args = wp_parse_args( $args, $defaults );
	
	$data = array();
	
	$categories = get_terms( array(
		
		'taxonomy' => 'wpas_cp_su_division',
		'hide_empty' => false,
	) );
	
	
	foreach ( $categories as $cat ) {
		$data[$cat->term_id] = $cat->name;
	}
	
	$selected = isset( $args['selected'] ) && $args['selected'] ? $args['selected'] : array();
	
	$options = wpas_cp_dropdown_options( $data, $selected );
	
	return wpas_dropdown( $args, $options );
	
}

/**
 * Return reporting groups dropdown
 * 
 * @param array $args
 * 
 * @return string
 */
function wpas_cp_user_reporting_groups_dropdown( $args ) {
	$defaults = array(
		'name'          => 'wpas_user_type',
		'taxonomy' => 'wpas_cp_su_reporting_group',
		'hide_empty' => false,
		'echo'		=> false
	);
	
	$args = wp_parse_args( $args, $defaults );
	
	$data = array();
	
	$categories = get_terms( array(
		
		'taxonomy' => 'wpas_cp_su_reporting_group',
		'hide_empty' => false,
	) );
	
	foreach ( $categories as $cat ) {
		$data[$cat->term_id] = $cat->name;
	}
	
	$selected = isset( $args['selected'] ) && $args['selected'] ? $args['selected'] : array();
	
	$options = wpas_cp_dropdown_options( $data, $selected );
	
	return wpas_dropdown( $args, $options );
	
}

/**
 * Generate option tabs for dropdown
 * 
 * @param array $data
 * @param array $selected_options
 * 
 * @return string
 */
function wpas_cp_dropdown_options( $data , $selected_options = array() ) {
	
	$options = "";
	
	if( empty( $data ) ) {
		return $options;
	}
	
	$selected_options = is_array( $selected_options ) ? $selected_options : array( $selected_options );
	
	foreach( $data as $value => $label ) {
		
		$selected = in_array( $value, $selected_options ) ? ' selected="selected"' : '';
		
		$options .= "<option value=\"{$value}\"{$selected}>{$label}</option>";
	}
	
	return $options;
}

/**
 * Return user types dropdown
 * 
 * @param array $args
 * 
 * @return string
 */
function wpas_cp_user_types_dropdown( $args ) {
	
	$defaults = array(
		'name'          => 'wpas_user_type',
	);

	$args = wp_parse_args( $args, $defaults );
	
	$user_types = wpas_cp_user_types();
	
	$selected = isset( $args['selected'] ) && $args['selected'] ? $args['selected'] : array();
	
	$options = wpas_cp_dropdown_options($user_types, $selected );
	
	return wpas_dropdown( $args, $options );
}

/**
 * Generate link for popup window
 * 
 * @param array $args
 * 
 * @return string
 */
function wpas_window_link( $args ) {
	
	$defaults = array(
		'type'  => 'inline',
		'data'  => array(),
		'label' => '',
		'title' => '',
		'ajax_params' => array()
	);
	
	$args = wp_parse_args( $args, $defaults );
	
	$class = 'wpas_win_link ' . $args['class'];
	$title = isset( $args['title'] ) ? $args['title'] : "";
	
	$link = '#';
	
	$data_attrs = array_merge( $args['data'],  array( 'win_type' => $args['type'] ) );
	
	if( !empty( $args['ajax_params'] ) ) {
		$data_attrs['ajax_params'] = json_encode( $args['ajax_params']);
	}
	
	$data_attr_list = array();
	
	foreach( $data_attrs as $attr_name => $attr_val ) {
		$data_attr_list[] = "data-{$attr_name}=\"" . esc_attr($attr_val) . '"';
	}
	
	$data_params = implode( ' ', $data_attr_list );
	
	
	$label = $args['label'];
	
	return sprintf( '<a href="%s" %s title="%s" class="%s">%s</a>', $link, $data_params, $title, $class, $label );
	
}

/**
 * Return option tag for users selection dropdown
 * 
 * @param type $user_id
 * 
 * @return type
 */
function wpas_cp_user_selected_option( $user_id ) {
				
	$option = '';
	
	if( $user_id ) {
		$user         = get_user_by( 'ID', $user_id );
		if (! empty( $user ) ) {
			$option = "<option value=\"{$user_id}\" selected=\"selected\">{$user->display_name}</option>";
		}
	}
	
	return $option;
				
}

add_filter( 'wpas_can_submit_ticket', 'wpas_cp_can_user_submit_ticket' , 99, 1 );
/**
 * Check if user can submit ticket
 * 
 * @param boolean $can
 * 
 * @return boolean
 */
function wpas_cp_can_user_submit_ticket( $can ) {
	
	
	if( $can && !wpas_cp_user_can_open_ticket() ) {
		$can = false;
	}
	
	return $can;
}

/**
 * Return companies a user is associated with based on allowed permission
 * 
 * @param int $user_id
 * @param string $permission
 * 
 * @return array
 */
function wpas_cp_get_user_companies_having_permission( $user_id, $permission = 'open_ticket' ) {
	
	$user_id = ( null === $user_id ) ? get_current_user_id() : $user_id;
	
	$companies = array();
	
	$all_permissions = array(
			'reply_ticket'	 => 'can_reply_ticket',
			'close_ticket'	 => 'can_close_ticket',
			'open_ticket'	 =>	'can_open_ticket',
			'manage_profile' => 'can_manage_profile'
		);
	
	if( $user_id && array_key_exists( $permission, $all_permissions ) ) {
		
		$profiles = WPAS_Company_Support_User::getCompaniesByUser( $user_id );
		
		
		$permission = $all_permissions[ $permission ];
		
		
		foreach( $profiles as $profile ) {
			if( $profile['SupportUser']->{$permission} ) {
				$companies[] = $profile['Company'];
			}
		}
		
	}
	
	return $companies;
}

/**
 * Check if a front end user can open ticket
 * 
 * @param int $user_id
 * 
 * @return boolean
 */
function wpas_cp_user_can_open_ticket( $user_id = null ) {
	
	$user_id = ( null === $user_id ) ? get_current_user_id() : $user_id;
	
	$can_open_ticket = false;
	
	if( $user_id ) {
		
		$profiles = WPAS_Company_Support_User::getCompaniesByUser( $user_id );
		
		if( 0 === count( $profiles ) || wpas_cp_get_user_companies_having_permission( $user_id, 'open_ticket' ) ) {
			$can_open_ticket = true;
		}
		
	}
	
	return $can_open_ticket;
}

add_action( 'wpas_submission_form_inside_before_subject', 'wpas_cp_submission_form_before_submit' );
/**
 * Add company dropdown field in submit ticket form
 * 
 * @return void
 */
function wpas_cp_submission_form_before_submit() {
	
	$user_id = get_current_user_id();
	
	$all_user_companies = WPAS_Company_Support_User::getCompaniesByUser( $user_id );
	
	
	if( empty( $all_user_companies ) ) {
		return;
	}
	
	$open_ticket_companies = wpas_cp_get_user_companies_having_permission( $user_id, 'open_ticket' );
	
	if( empty( $open_ticket_companies ) ) {
		return;
	}
	
	if( 1 === count( $open_ticket_companies ) ) {
		
		$company = $open_ticket_companies[0];
		
		$field = sprintf( '<input type="hidden" id="wpas_company_id" name="wpas_company_id" value="%s" /><span>%s</span>', $company->ID, $company->post_title );
	} else {
		
		$field = '<select required id="wpas_company_id" class="wpas-form-control" name="wpas_company_id">';
		$field .= '<option value="">Please select</option>';
		foreach( $open_ticket_companies as $company ) {
			$field .= "<option value=\"{$company->ID}\">{$company->post_title}</option>";
		}

		$field .= '</select>';
	}
	
	echo
	'<div class="wpas-form-group" id="wpas_company_id_wrapper">
			<label for="wpas_company_id">Company</label>' . $field . '
	</div>';
}

add_filter( 'wpas_show_reply_form_front_end',	'wpas_cp_show_reply_form_front_end', 99, 2 );
add_filter( 'wpas_user_can_reply_ticket',		'wpas_cp_user_can_reply_ticket', 99, 2 );
add_filter( 'wpas_user_can_close_ticket',		'wpas_cp_user_can_close_ticket', 99, 2 );
add_filter( 'wpas_can_also_reply_ticket',		'wpas_cp_can_also_reply_ticket', 20, 4 );
/**
 * Front end check if user can reply a ticket to show or hide reply form
 * 
 * @param bboolean $can
 * @param int $post_id
 * @param int $author_id
 * @param int $case
 * 
 * @return boolean
 */
function wpas_cp_can_also_reply_ticket( $can, $post_id, $author_id, $case ) {
	
	if( 4 === $case ) {
		$can = wpas_cp_user_can_reply_ticket( $can, $post_id );
	}
	
	return $can;
}

/**
 * Show or hide reply form front end
 * 
 * @param boolean $show
 * @param object $ticket
 * 
 * @return boolean
 */
function wpas_cp_show_reply_form_front_end( $show, $ticket ) {
	
	return wpas_cp_user_can_reply_ticket( $show, $ticket->ID );
}

/**
 * Check if user can reply a ticket
 * 
 * @param boolean $can
 * @param int $ticket_id
 * 
 * @return boolean
 */
function wpas_cp_user_can_reply_ticket( $can, $ticket_id ) {
	
	$company_id = wpas_get_cf_value( 'company_id', $ticket_id );
	
	if( $company_id ) {
		$user_id = get_current_user_id();
		$can = WPAS_Company_Support_User::supportUserHavePermission( $company_id, $user_id, 'reply_ticket' ) ? true : false;
	}
	
	return $can;
}

/**
 * Check if user can close a ticket
 * 
 * @param boolean $can
 * @param int $ticket_id
 * 
 * @return boolean
 */
function wpas_cp_user_can_close_ticket( $can, $ticket_id ) {
		
	$company_id = wpas_get_cf_value( 'company_id', $ticket_id );
	
	
	if( $company_id ) {
		
		$user_id = get_current_user_id();
		$can = WPAS_Company_Support_User::supportUserHavePermission( $company_id, $user_id, 'close_ticket' ) ? true : false;
	}
	
	return $can;
}

add_action( 'wp_ajax_wpas_get_cp_companies', 'wpas_ajax_get_cp_companies' );
/**
 * Return companies for select2 dropdown
 */
function wpas_ajax_get_cp_companies() {
	$results = array();
		
	wpas_cp_ajax_nonce_check( 'wpas-get-cp-companies' );
	
	
	if( !isset( $_POST['q'] ) || empty( $_POST['q']  ) ) {
		wp_send_json( array() );
		die();
	}
	
	$keyword = sanitize_text_field( $_POST['q'] );
	
	
	require( WPAS_PATH . 'includes/admin/functions-post.php' );
	
	
	$args = array(
		'post_type'              => 'wpas_company_profile',
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
			    'company_id'     => $issue->ID,
			    'company_name' => $issue->post_title
			);
			
		}
	}
	
	echo json_encode( $results );
	die();
}

/**
 * Return page link for front end
 * 
 * @param string $name
 * 
 * @return string
 */
function wpas_cp_get_page_link( $name ) {
	
	$option_name = "cp_{$name}_page";
	
	$page_id = get_option( $option_name );
	
	if( $page_id ) {
		
		return get_permalink( $page_id );
	}
	
	return '';
}

/**
 * Validate add/edit support user from front end
 * 
 * @param array $data
 * @param string $type
 * @param string $form_type
 * 
 * @return \WP_Error|boolean
 */
function validate_support_user( $data ,$type = 'add', $form_type = '') {
	
	$error				= "";
	
	if( 'fe_add_company' === $form_type ) {
	
		if( !$data['user_id'] ) {
			$error = __( 'Please select a user.', 'wpas_cp' );
		} 
	}
	
	if( $error ) {
		return new WP_Error( 'support_user', $error );
	}
		
	if( !$data['user_type'] ) {
		
		$error = __( 'Please select user type.', 'wpas_cp' );
		
	} elseif( empty( $data['divisions'] ) ) {
		
		$error = __( 'Please select a division.', 'wpas_cp' );
		
	} elseif( ! $data['reporting_group'] ) {

		$error = __( 'Please select a reporting group.', 'wpas_cp' );
		
	} elseif( 'edit' === $type && !WPAS_Company_Support_User::getByItemID( $data['id'] ) ) {
		
		$error = __( 'Support user doesn\'t exist.', 'wpas_cp' );
		
	} elseif( 'add' === $type && WPAS_Company_Support_User::getCompanySupportUser( $data['company_id'] ,$data['user_id'] ) ) {
		
		$error = __( 'Support user already associated with this company.', 'wpas_cp' );
		
	}
	
	
	if( $error ) {
		return new WP_Error( 'support_user', $error );
	} 
	
	return true;
	
}

/**
 * Prepare data for company profile add/edit from front end
 * 
 * @param string $type
 * 
 * @return array
 */
function prepare_company_profile_input_data( $type = 'add' ) {
	
	$address = filter_input ( INPUT_POST, 'cp_address', FILTER_SANITIZE_STRING );
	$email	 = filter_input ( INPUT_POST, 'cp_email',   FILTER_SANITIZE_STRING );
	$fax     = filter_input ( INPUT_POST, 'cp_fax',     FILTER_SANITIZE_STRING );
	$name    = filter_input ( INPUT_POST, 'cp_name',    FILTER_SANITIZE_STRING );
	$phone   = filter_input ( INPUT_POST, 'cp_phone',   FILTER_SANITIZE_STRING );
	$company_id   = filter_input ( INPUT_POST, 'company_id',   FILTER_SANITIZE_NUMBER_INT );
	
	return array(
		'address'		=> $address ,
		'email'			=> $email ,
		'fax'			=> $fax ,
		'name'			=> $name ,
		'phone'			=> $phone,
		'company_id'	=> $company_id
	);
}

/**
 * Prepare data for support user add/edit from front end
 * 
 * @param string $type
 * 
 * @return array
 */
function prepare_support_user_input_data( $type = 'add' ) {
	
	$user_type			= filter_input( INPUT_POST, 'user_type',		FILTER_SANITIZE_STRING );
	$divisions			= filter_input( INPUT_POST, 'divisions',		FILTER_DEFAULT ,FILTER_REQUIRE_ARRAY  );
	$reporting_group	= filter_input( INPUT_POST, 'reporting_group',	FILTER_SANITIZE_NUMBER_INT );
	$is_primary_user    = filter_input( INPUT_POST, 'is_primary_user',    FILTER_SANITIZE_NUMBER_INT );
	$can_reply_ticket   = filter_input( INPUT_POST, 'can_reply_ticket',   FILTER_SANITIZE_NUMBER_INT );
	$can_close_ticket   = filter_input( INPUT_POST, 'can_close_ticket',   FILTER_SANITIZE_NUMBER_INT );
	$can_open_ticket    = filter_input( INPUT_POST, 'can_open_ticket',	  FILTER_SANITIZE_NUMBER_INT );
	$can_manage_profile = filter_input( INPUT_POST, 'can_manage_profile', FILTER_SANITIZE_NUMBER_INT );
	$id					= filter_input( INPUT_POST, 'id',				FILTER_SANITIZE_NUMBER_INT );
	$company_id			= filter_input( INPUT_POST, 'company_id',		FILTER_SANITIZE_NUMBER_INT );
	
	return
	array(
		'id'				 => $id,
		'company_id'		 => $company_id,
		'user_type'			 => $user_type,
		'divisions'			 => $divisions,
		'reporting_group'	 => $reporting_group,
		'primary'			 => $is_primary_user,
		'can_reply_ticket'	 => $can_reply_ticket,
		'can_close_ticket'	 => $can_close_ticket,
		'can_open_ticket'	 => $can_open_ticket,
		'can_manage_profile' => $can_manage_profile,
	);
}


/**
 * Check if user can add company profile
 * 
 * @param int $user_id
 * 
 * @return \WP_Error|boolean
 */
function wpas_cp_user_can_add_company_profile( $user_id = null ) {
	
	$user_id = $user_id ? $user_id : get_current_user_id();
	
	if( !$user_id ) {
		$error = "You don't have access to this page";
	} else {
		
		$user = get_user_by( 'id', $user_id );
	
		$user_companies = WPAS_Company_Support_User::getCompaniesByUser( $user_id );

		$error = null;

		if( 0 < count( $user_companies ) ) {
			$error = 'You are already associated with a company.';
		} elseif ( !$user->has_cap( 'ticket_company_profile_self_add' ) ) {
			$error = 'You can\'t add a company profile.';
		}

	}
	
	if( $error ) {
		return new WP_Error( 'user_can_add_company', $error );
	}
	
	return true;
	
}


/**
 * Check if user can manage company
 * 
 * @param int $company_id
 * @param int $user_id
 * 
 * @return boolean
 */
function user_can_manage_company( $company_id, $user_id = null ) {
	
	$user_id = $user_id ? $user_id : get_current_user_id();
	
	$support_user = WPAS_Company_Support_User::getCompanySupportUser( $company_id, $user_id );
	
	$can_manage = false;
	if( $support_user && $support_user->can_manage_profile ) {
		$can_manage = true;
	}
	
	return $can_manage;
	
}

/**
 * Validate add/edit company profile from front end
 * 
 * @param array $cp_data
 * @param string $type
 * @param string $form_type
 * 
 * @return \WP_Error|boolean
 */
function validate_company_profile( $cp_data, $type = 'add', $form_type = 'fe_add_company_profile' ) {
	
	$error = null;
	
	if( 'fe_add_company_profile' === $form_type ) {
		
		$can_add = wpas_cp_user_can_add_company_profile();
		
		if( is_wp_error( $can_add ) ) {
			$error = $can_add->get_error_message();
		}
	} elseif( 'fe_manage_company_profile' === $form_type ) {
		
		$user_id = get_current_user_id();
	
		if( !$cp_data['company_id'] ) {
			$error = __( 'Something went wrong, try again later.', 'wpas_cp' );
		} elseif( !user_can_manage_company( $cp_data['company_id'] ) ) {
			$error = "You don't have access to manage this company profile";
		}
	}
	
	if( !$error ) {
		if( !$cp_data['name'] ) {
			$error = 'Company name is required';
		}
	}
	
	if( $error ) {
		return new WP_Error( 'company_profile', $error );
	}
	
	return true;
}


/**
 * Display support user type
 * 
 * @param string $type
 * 
 * @return string
 */
function wpas_cp_display_user_type( $type ) {
	
	$types = wpas_cp_user_types();
	
	return isset( $types[ $type ] ) ? $types[ $type ] : "";
}


add_filter( 'wpas_frontend_add_nav_buttons', 'wpas_cp_frontend_add_nav_buttons' );
/**
 * Add buttons on front end ticket listing page
 */
function wpas_cp_frontend_add_nav_buttons() {
	
	wpas_make_button( __( 'Manage Company Profiles', 'wpas_cp' ), array( 'type' => 'link', 'link' => wpas_cp_get_page_link( 'manage' ), 'class' => 'wpas-btn wpas-btn-default wpas-link-cp-manage' ) );
	
	$can_add_profile = wpas_cp_user_can_add_company_profile();
	if( true === $can_add_profile && !is_wp_error( $can_add_profile ) ) {
		wpas_make_button( __( 'Add Company Profile', 'wpas_cp' ), array( 'type' => 'link', 'link' => wpas_cp_get_page_link( 'add_company' ), 'class' => 'wpas-btn wpas-btn-default wpas-link-cp-newcompany' ) );
	}
	
}

/**
 * Generate user profile link tag with user display name
 * 
 * @param int $user_id
 * 
 * @return string
 */
function wpas_cp_display_user_link( $user_id ) {
	
	$user = get_user_by( 'id', $user_id );

	if ( ! empty( $user ) ) {
		$link = get_edit_user_link( $user_id );

		return "<a href='$link'>$user->display_name</a>";
	
	}
	
}

/**
 * Display company name from ticket id
 * 
 * @param string $name
 * 
 * @param int $post_id
 */
function wpas_cf_display_company_name( $name, $post_id ) {
	
	$company_id = get_post_meta( $post_id, '_wpas_company_id', true );
	
	$name = "";
	
	if( $company_id ) {
		$company = get_post( $company_id );
		
		if( $company ) {
			$name = $company->post_title;
		}
	}
	
	echo $name;
}

/**
 * Return all company ids a user is associated with
 * 
 * @param int $user_id
 * 
 * @return array
 */
function wpas_cp_user_company_ids( $user_id = '' ) {
	
	$user_id = $user_id ? $user_id : get_current_user_id();
	
	$companies =  WPAS_Company_Support_User::getByUserID( $user_id );
	
	$company_ids = array();
	
	foreach( $companies as $c ) {
		$company_ids[] = $c->profile_id;
	}
	
	return $company_ids;
	
}

add_filter( 'wpas_tickets_shortcode_query_args', 'wpas_cp_tickets_shortcode_query_args', 20, 1 );
/**
 * Set query args for front end ticket listing
 * 
 * @param array $args
 * 
 * @return boolean
 */
function wpas_cp_tickets_shortcode_query_args( $args ) {
	
	$args['tickets_shortcode_query'] = true;
	
	add_filter( 'posts_clauses', 'wpas_cp_filter_fe_tickets',		20, 2 );
	
	return $args;
}

/**
 * Modify ticket listing query to add ticket added by other users associated with same companies
 * 
 * @global object $wpdb
 * 
 * @param array $pieces
 * @param object $wp_query
 * 
 * @return array
 */
function wpas_cp_filter_fe_tickets( $pieces , $wp_query ) {
		global $wpdb;
		
		
	remove_filter( 'posts_clauses',	'wpas_cp_filter_fe_tickets', 20 );
	
	if( !isset( $wp_query->query['tickets_shortcode_query'] ) && !$wp_query->query['tickets_shortcode_query'] ) {
		return $pieces;
	}
	
	$company_ids = wpas_cp_user_company_ids();
	
	if( empty( $company_ids ) ) {
		return $pieces;
	}
	
	preg_match( "/AND {$wpdb->posts}\.post_author IN \((.*?)\)/i", $pieces['where'], $matches );
	
	$user_clause = $matches[0];
	
	$where = str_replace( $user_clause, '', $pieces['where'] );
	
	$where = 'AND ( cppm.meta_value IN (' . implode(',', $company_ids) . ') OR ' . substr( $user_clause, 4 ) . ')' . $where;
	
	$pieces['join'] .=  " LEFT JOIN {$wpdb->postmeta} cppm ON ( {$wpdb->posts}.ID = cppm.post_id ) AND cppm.meta_key = '_wpas_company_id'";
	$pieces['where'] = $where;
	
	$pieces['groupby'] = "{$wpdb->posts}.ID";
	
	return $pieces;
}
	
	
add_filter( 'wpas_can_view_ticket', 'wpas_cp_user_can_view_ticket', 12, 3 );
/**
 * Check if user can view a ticket from front end
 * 
 * @param boolean $can
 * 
 * @param int $post_id
 * @param int $author_id
 * 
 * @return boolean
 */
function wpas_cp_user_can_view_ticket( $can, $post_id, $author_id ) {
	
	
	if( is_admin() ) {
		return $can;
	}
	
	$company_id = get_post_meta( $post_id, '_wpas_company_id', true );
	
	if( $company_id && !$can ) {
		
		$company_ids = wpas_cp_user_company_ids();
		
		if( !empty( $company_ids ) && in_array( $company_id, $company_ids ) ) {
			$can = true;
		}
		
	}
	
	return $can;
}

add_action( 'wpas_cp_company_profile_updated', array( 'WPAS_CP_Log', 'add_log' ), 10, 4  );
add_filter( 'wpas_pf_security_profile_fields' , 'wpas_cp_security_profile_fields' ,20, 1 );
/**
 * Add companies security field in productivity security profiles
 * 
 * @param array $fields
 * 
 * @return array
 */
function wpas_cp_security_profile_fields( $fields ) {
	
	$fields['company_id'] = array(
					'field_label'		=> __( 'Companies',			 'wpas_cp' ),
					'all_label'			=> __( 'All Companies',		 'wpas_cp' ),
					'selected_label'	=> __( 'Selected Companies', 'wpas_cp' ),
					'options'			=> array(),
					'filter'			=> array(
					    'type'			=> 'meta',
					    'key'			=> '_wpas_company_id',
					    'compare'		=> 'IN'
					),
					'field_callback'	=> 'wpas_cp_companies_s2_dropdown',
					'get_item_cb'		=> 'wpas_get_cp_companies', 
					'data_attr'			=> array( 'action' => 'wpas_get_cp_companies', 'result_id' => 'company_id', 'result_text' => 'company_name', 'default' => '' )
				);
	
	return $fields;
	
}

/**
 * Return companies field for security profiles
 * 
 * @param array $options
 * @param array $args
 * 
 * @return string
 */
function wpas_cp_companies_s2_dropdown( $options, $args = array() ) {
	
	$field = "";
	
	ob_start();
	wp_nonce_field( 'wpas-get-cp-companies', 'cp_nonce_wpas_get_cp_companies' );
	
	$name = $args['name'];
	
	$selected = isset( $args['selected'] ) && $args['selected'] ? $args['selected'] : array();
	
	$options = '';
	
	foreach( $selected as $company_id ) {
		$company = get_post( $company_id );
		if( $company ) {
			$options .= "<option selected=\"selected\" value=\"{$company_id}\">{$company->post_title}</option>";
		}
	}
	
	echo wpas_dropdown( array(
		'name'      => $name . '[]',
		'id'        => $name,
		'select2'   => true,
		'class' => 'cp-select2',
		'multiple' => true,
		'data_attr' => array( 'action' => 'wpas_get_cp_companies', 'result_id' => 'company_id', 'result_text' => 'company_name', 'default' => '' )
	), $options );
	
	
	$field = ob_get_clean();
	
	return $field;
	
}

add_filter( 'wpas_tickets_list_columns', 'wpas_cp_tickets_list_custom_columns' );
/**
 * Add user column in front end ticket listing page
 * 
 * @param array $columns
 * 
 * @return array
 */
function wpas_cp_tickets_list_custom_columns( $columns ) {

	$columns['user'] = array(
            'title' => 'User',
            'callback' => 'wpas_cp_display_user_name'
        );
	
	
	return $columns;
	
}

/**
 * Print content for user column
 * 
 * @param string $column
 * @param int $ticket_id
 */
function wpas_cp_display_user_name( $column, $ticket_id ) {
	
	$ticket = get_post( $ticket_id );
	
	$name = "";
	
	if( $ticket ) {
		$user = get_user_by( 'id', $ticket->post_author );

		if ( ! empty( $user ) ) {
			$name = $user->display_name;
		}
	}
	
	echo $name;
}


add_action( 'wp_enqueue_scripts', 'wpas_cp_fe_enqueue' );

/**
 * Enqueue style & scripts for front end pages
 * 
 * @global object $post
 */
function wpas_cp_fe_enqueue() {
	global $post;
	
	$page_ids = array(
		get_option( 'cp_manage_page' ),
		get_option( 'cp_add_company_page' )
	);
	
	if(  $post && in_array( $post->ID, $page_ids ) ) {
		
		
		wp_enqueue_script( 'wpas-cp-magnific-script', WPAS_CP_URL . 'assets/js/jquery.magnific-popup.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'wpas-cp-script', WPAS_CP_URL . 'assets/public/js/script.js', array( 'jquery' ) );

		wp_enqueue_style('wpas-cp-mp-style', WPAS_CP_URL . 'assets/css/magnific-popup.css');
		wp_enqueue_style('wpas-cp-style', WPAS_CP_URL . 'assets/public/css/style.css');
		
		add_action( 'wp_head', 'wpas_cp_fe_ajaxurl' );
	}
	
}

/**
 * Wordpress does not add js var ajaxurl on front end, so add it manually
 */
function wpas_cp_fe_ajaxurl() {
	echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
}