<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

add_shortcode( 'add_company_profile', 'wpas_cp_add_company_profile' );


/**
 * Add company profile page short code
 * 
 * @param array $args
 * 
 * @return string
 */
function wpas_cp_add_company_profile( $args ) {
	
	$can_add = wpas_cp_user_can_add_company_profile();
	
	
	ob_start();
	
	if( is_wp_error( $can_add ) ) {
		echo $can_add->get_error_message();
	} else {
		include WPAS_CP_PATH . 'includes/templates/add_company_profile/form.php';
	}
	
	return ob_get_clean();
}


add_action( 'wp_ajax_wpas_cp_fe_add_company_profile', 'wpas_cp_process_add_company_profile' );


/**
 * Handle add company profile request from front end
 */
function wpas_cp_process_add_company_profile() {
	
	
	wpas_cp_ajax_nonce_check_fe( 'wpas_cp_add_company_profile', 'wpas-cp-add-cp' );
	
	$cp_data = prepare_company_profile_input_data();
	
	$su_data = prepare_support_user_input_data();
	
	$user_can = wpas_cp_user_can_add_company_profile();
	
	$error = null;
	
	$cp_validate = validate_company_profile( $cp_data, 'add', 'fe_add_company_profile' );
	
	
	$su_data['user_id'] = get_current_user_id();
	
	if( is_wp_error( $user_can ) ) {
		$error = $user_can->get_error_message();
	} elseif( is_wp_error( $cp_validate ) ) {
		$error = $cp_validate->get_error_message();
	} else {
		$support_user_validate_result = validate_support_user( $su_data, 'add', 'fe_add_company' );
	
		if( is_wp_error( $support_user_validate_result ) ) {
			$error = $support_user_validate_result->get_error_message();
		}
	}
	
	
	$success = false;
	
	
	if( $error ) {
		wp_send_json_error( array( 'msg' => $error ) );
	} else {
		
		
		$name   = wp_strip_all_tags( $cp_data['name'] );
		$user_id = get_current_user_id();
		
		$post_data = array(
			'post_content'   => '',
			'post_title'     => $name,
			'post_status'    => 'queued',
			'post_type'      => 'wpas_company_profile',
			'post_author'    => $su_data['user_id'],
			'ping_status'    => 'closed',
			'comment_status' => 'closed',
		);
		
		
		
		
		$company_id = wp_insert_post( $post_data, false );
	
		if ( false === $company_id ) {

			$error = "Error while adding company profile, try again later.";

		} else {
			
			update_post_meta( $company_id, 'address', $cp_data['address'] );
			update_post_meta( $company_id, 'email',   $cp_data['email'] );
			update_post_meta( $company_id, 'phone',   $cp_data['phone'] );
			update_post_meta( $company_id, 'fax',     $cp_data['fax'] );
			
			
			$su_data['profile_id'] = $company_id;
			
			$support_user = new WPAS_Company_Support_User( $su_data );
			
			$support_user->add();
		}
		
		
		if( $error ) {
			wp_send_json_error( array( 'msg' => $error ) );
		} else {
			wp_send_json_success( array( 'location' => add_query_arg( 'profile_added', '', wpas_get_tickets_list_page_url() ) ) );
		}
		
		die();
		
	}
	
}


add_action( 'wpas_before_tickets_list', 'wpas_cp_company_added_notification' );

/**
 * Print success message after company profile is added from front-end
 */
function wpas_cp_company_added_notification() {
	
	if( isset( $_GET['profile_added'] ) ) {
		echo wpas_get_notification_markup( 'success', sprintf( __( 'Company profile successfully added.', 'wpas_cp' ), wpas_get_submission_page_url() ) );
	}
	
}