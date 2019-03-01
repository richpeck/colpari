<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}


add_shortcode( 'manage_company_profiles' , 'wpas_cp_manage_company_profiles' );


/**
 * Manage company profile page short code 
 * 
 * @param array $args
 * 
 * @return string
 */
function wpas_cp_manage_company_profiles( $args ) {
	
	
	
	$user_id = get_current_user_id();
	
	$companies = wpas_cp_get_user_companies_having_permission( $user_id, 'manage_profile' );
	
	ob_start();
	?>
	
	<div id="wpas_cp_manage_company_profiles">
			
			
		<div id="wpas_cp_manage_company_messages"></div>
			
			
		<?php if( empty( $companies ) ) { ?>
			<div class="no_item_msg"><?php echo _e( "You don't have access to manage any company.", 'wpas_cp' ); ?></div>
		<?php } else { ?>
			
			<div id="wpas_cp_manage_company_items" class="wpas_cp_data_items">
				<?php include WPAS_CP_PATH . 'includes/templates/manage_company/items.php'; ?>
			</div>
		<?php } ?>
	</div>
	
	<?php
	
	return ob_get_clean();
	
}	



add_action( 'wp_ajax_cp_edit_support_user_view', 'wpas_cp_edit_support_user_view' );


/**
 * Return edit support user window content
 */
function wpas_cp_edit_support_user_view() {
	
	$company_id = filter_input( INPUT_POST, 'company_id' );
	$item_id = filter_input( INPUT_POST, 'id' );
	$user_id = filter_input( INPUT_POST, 'user_id' );
	
	$nonce = '';
	
	$current_user_id = get_current_user_id();
	
	
	$item = WPAS_Company_Support_User::getCompanySupportUser( $company_id, $user_id );
	
	
	if( $item && $item->id == $item_id ) {
		
		
		include WPAS_CP_PATH . 'includes/templates/manage_company/edit_support_user.php';
	}
	die();
	
}



add_action( 'wp_ajax_wpas_cp_manage_company_edit_support_user', 'wpas_cp_manage_company_edit_support_user' );

/**
 * Handle edit support user request from front end
 */
function wpas_cp_manage_company_edit_support_user() {
	
	$error				= "";
	$result				= array();
	
	$current_user_id = get_current_user_id();
	
	
	wpas_cp_ajax_nonce_check_fe( 'wpas_cp_mc_edit_su', 'wpas-cp-mc-edit-support-user' );
	
	
	$su_data = prepare_support_user_input_data( 'edit' );
	
	$su_valid = validate_support_user( $su_data, 'edit', 'fe_edit_support_user' );
	
	
	$error = null;
	
	if( is_wp_error( $su_valid ) ) {
		$error = $su_valid->get_error_message();
	}
	
	
	if( $error ) {
		wp_send_json_error( array( 'msg' => $error ) );
	} else {
		
		if( !isset( $su_data['user_id'] ) || !$su_data['user_id'] ) {
			$su = WPAS_Company_Support_User::getByItemID( $su_data['id'] );
			
			if( $su ) {
				$su_data['user_id'] = $su->user_id;
			}
		}
		
		$company_id = $su_data['company_id'];
		$su_data['profile_id'] = $company_id;
		
		$item = new WPAS_Company_Support_User( $su_data );
		
		

		$item_id = $item->id;

		$item->update();
		
		
		ob_start();	
		include WPAS_CP_PATH . 'includes/templates/manage_company/support_user_item.php';
		$item_html = ob_get_clean();

		wp_send_json_success( array( 
			'msg' => __( 'Support user saved.', 'wpas_cp' ), 
			'update_item' => $item_html, 
			'selector' => ".wpas_cp_list_subtable_support_user[data-company_id={$item->profile_id}] .wpas_cp_ui_item[data-item_id={$item->id}]",
			'info_selector' => '#wpas_cp_manage_company_messages'
		));
			
			
		die();
		
	}
}





add_action( 'wp_ajax_cp_manage_edit_company_profile_view', 'wpas_cp_manage_edit_company_profile_view' );

/**
 * Return edit company profile window content
 */
function wpas_cp_manage_edit_company_profile_view() {
	
	$company_id = filter_input( INPUT_POST, 'id' );
	
	$nonce = '';
	
	$user_id = get_current_user_id();
	
	
	$support_user = WPAS_Company_Support_User::getCompanySupportUser( $company_id, $user_id );
	
	if( $support_user && $support_user->can_manage_profile ) {
		include WPAS_CP_PATH . 'includes/templates/manage_company/edit_company.php';
	}
	die();
}




add_action( 'wp_ajax_wpas_cp_manage_company_edit', 'wpas_cp_manage_company_edit' );

/**
 * Handle request to update company profile from front end
 */
function wpas_cp_manage_company_edit() {
	
	$cp_data = prepare_company_profile_input_data();
	
	
	wpas_cp_ajax_nonce_check_fe( 'wpas_cp_mc_company_profile', 'wpas-cp-edit-company-profile' );
	
	$cp_validate = validate_company_profile( $cp_data, 'edit', 'fe_manage_company_profile' );
	
	$success = false;
	
	if( true === $cp_validate && !is_wp_error( $cp_validate) ) {
		
		$company_id = $cp_data['company_id'];
		
		update_post_meta( $company_id, 'address', $cp_data['address'] );
		update_post_meta( $company_id, 'email',   $cp_data['email'] );
		update_post_meta( $company_id, 'phone',   $cp_data['phone'] );
		update_post_meta( $company_id, 'fax',     $cp_data['fax'] );
		
		wp_update_post( array(
			'ID' => $company_id,
			'post_title' => $cp_data['name']
		));
		
		$success = true;
		
		$company = get_post( $company_id );
		
		ob_start();	
		include WPAS_CP_PATH . 'includes/templates/manage_company/company_item.php';
		$item_html = ob_get_clean();

		wp_send_json_success( array( 
			'msg' => __( 'Company successfully updated.', 'wpas_cp' ), 
			'update_item' => $item_html, 
			'selector' => "#wpas_cp_manage_company_profiles .wpas_cp_ui_items .wpas_cp_ui_item_company[data-item_id={$company_id}]",
			'info_selector' => '#wpas_cp_manage_company_messages'
			));
		
	} else {
		wp_send_json_error( array( 'msg' => $cp_validate->get_error_messages() ) );
	}
	
	die();
}



add_action( 'wp_ajax_wpas_cp_mcp_delete_support_user', 'wpas_cp_mcp_delete_support_user' );

/**
 * Handle request to delete support user
 */
function wpas_cp_mcp_delete_support_user() {
	
	$company_id = filter_input( INPUT_POST, 'company_id', FILTER_SANITIZE_NUMBER_INT );
	$item_id    = filter_input( INPUT_POST, 'id',		  FILTER_SANITIZE_NUMBER_INT );
	
	wpas_cp_ajax_nonce_check_fe( 'wpas_cp_mc_del_su' );
	
	
	
	$deleted = false;
	if( $company_id && $item_id ) {
		
		$support_user = WPAS_Company_Support_User::getByItemID( $item_id );
		
		if( $support_user && user_can_manage_company( $company_id ) && $support_user->profile_id === $company_id ) {
			$support_user->delete();
			
			$deleted = true;
		} 
	}
	
	
	
	if( false === $deleted ) {
		wp_send_json_error( array( 'msg' => __( 'Error while deleting support user, try again later.', 'wpas_cp' ), 'info_selector' => '#wpas_cp_manage_company_messages' ) );
	} else {
		wp_send_json_success( array( 'msg' => __( 'Support user deleted', 'wpas_cp' ), 'item_id' => $item_id, 'info_selector' => '#wpas_cp_manage_company_messages' ) );
	}
	
	die();
}