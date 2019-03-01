<?php
// This file contains functions shared by all classes in this add-on.

// Return a random hash...
function wpas_pf_random_hash() {
	
	if ( function_exists( 'wpas_random_hash' ) ) {
		
		return wpas_random_hash();
		
	} else {
	
		$time  = time();
		$the_hash = md5( $time . (string) random_int(0, getrandmax()) );
		
		return $the_hash;
		
	}
	
}



/**
 * User preset capabilities
 * 
 * @return array
 */
function wpas_pf_user_preset_capabilities() {
	
	return apply_filters( 'wpas_user_capabilities_client', array(
		'view_ticket',
		'create_ticket',
		'close_ticket',
		'reply_ticket',
		'attach_files'
	) );

}

/**
 * Agent preset capabilities
 * 
 * @return array
 */
function wpas_pf_agent_preset_capabilities() {
	
	
	return apply_filters( 'wpas_user_capabilities_agent', array(
		'view_ticket',
		'view_private_ticket',
		'edit_ticket',
		'edit_other_ticket',
		'edit_private_ticket',
		'assign_ticket',
		'close_ticket',
		'reply_ticket',
		'create_ticket',
		'delete_reply',
		'attach_files',
		'ticket_manage_tags',
		'ticket_manage_products',
		'ticket_manage_departments',
		'ticket_manage_priorities',
		'ticket_manage_channels'
	) );
	
	
}

/**
 * Manager preset capabilities
 * 
 * @return array
 */
function wpas_pf_manager_preset_capabilities() {
	
	return apply_filters( 'wpas_user_capabilities_full', array(
		'view_ticket',
		'view_private_ticket',
		'edit_ticket',
		'edit_other_ticket',
		'edit_private_ticket',
		'delete_ticket',
		'delete_reply',
		'delete_private_ticket',
		'delete_other_ticket',
		'assign_ticket',
		'close_ticket',
		'reply_ticket',
		'settings_tickets',
		'ticket_taxonomy',
		'create_ticket',
		'attach_files',
		'view_all_tickets',
		'view_unassigned_tickets',
		'manage_licenses_for_awesome_support',
		'administer_awesome_support',
		'ticket_manage_tags',
		'ticket_edit_tags',
		'ticket_delete_tags',
		'ticket_manage_products',
		'ticket_edit_products',
		'ticket_delete_products',
		'ticket_manage_departments',
		'ticket_edit_departments',
		'ticket_delete_departments',
		'ticket_manage_priorities',
		'ticket_edit_priorities',
		'ticket_delete_priorities',
		'ticket_manage_channels',
		'ticket_edit_channels',
		'ticket_delete_channels'
	) );
	
}


/**
 * Supervisor preset capabilities
 * 
 * @return array
 */
function wpas_pf_supervisor_preset_capabilities() {
	
	return wpas_pf_manager_preset_capabilities();
}


/**
 * Return capabilities based on preset
 * 
 * @param string $preset
 * @return array/null
 */
function wpas_pf_preset_capabilities( $preset ) {
	
	if( function_exists( "wpas_pf_{$preset}_preset_capabilities" ) ) {
		return call_user_func( "wpas_pf_{$preset}_preset_capabilities" );
	}
	
	return null;
}



/**
 * Return user's display name or a default text if user id is not provided
 * 
 * @param int $author
 * @param boolean $return_default
 * @param string $default
 * 
 * @return string
 */
function wpas_pf_user_display_name( $author, $return_default = true, $default =  '' ) {

	if ( $author != 0 && $author ) {
		$user_name = get_the_author_meta( 'display_name', $author );
	} elseif( $return_default ) {
		
		if ( !$default ) {
			$default = __( 'Anonymous', 'wpas_productivity' );
		}
		$user_name = $default;
	}
	
	return $user_name;
}


