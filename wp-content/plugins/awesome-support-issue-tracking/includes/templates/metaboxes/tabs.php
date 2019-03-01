<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
} 




/**
 * Add tabs in issue edit page
 * 
 * @param array $tabs
 * 
 * @return array
 */
function wpas_it_admin_tabs_issue( $tabs ) {
	 
	$tabs['tickets'] = __( 'Tickets', 'wpas_it' );
	$tabs['additional_agents'] = __( 'Additional Agents', 'wpas_it' );
	$tabs['ai_parties'] = __( 'Additional Interested Parties', 'wpas_it' );
	
	 
	return $tabs;
}


/**
 * Return content for tickets tab in edit issue page
 * 
 * @global type $post_id
 * 
 * @return string
 */
function wpas_it_admin_tabs_issue_tickets_content() {
	global $post_id;
	
	$issue = new WPAS_IT_Issue(	$post_id );
	
	$tickets = $issue->getTickets();
	
	
	$content = "";
	if( !empty( $tickets ) ) {
		
		add_filter( 'add_ticket_column_custom_fields',	'__return_true', 11, 1 );
	
		$fields = WPAS()->custom_fields->get_custom_fields();
		WPAS_Tickets_List::get_instance()->add_custom_fields( $fields );

		ob_start();
		include 'it-tickets-tab.php';

		remove_filter( 'add_ticket_column_custom_fields',	'__return_true', 11 );

		$content = ob_get_clean();
		
	}
	
	return $content;
	
}
	

/**
 * Return content for additional interested parties in edit issue page
 * 
 * @global type $post_id
 * 
 * @return content
 */
function wpas_it_admin_tabs_issue_additional_agents_content() {
	global $post_id;
	
	$ai_parties = WPAS_IT_Additional_Agent::get_instance();
	
	ob_start();
	$ai_parties->display( $post_id );
	
	return ob_get_clean();
}

	
/**
 * Return content for additional interested parties in edit issue page
 * 
 * @global type $post_id
 * 
 * @return content
 */
function wpas_it_admin_tabs_issue_ai_parties_content() {
	global $post_id;
	
	$ai_parties = WPAS_IT_Additional_party::get_instance();
	
	ob_start();
	$ai_parties->display( $post_id );
	
	return ob_get_clean();
}


add_filter( 'wpas_admin_tabs_issue', 'wpas_it_admin_tabs_issue' );
add_filter( 'wpas_admin_tabs_issue_tickets_content', 'wpas_it_admin_tabs_issue_tickets_content' );
add_filter( 'wpas_admin_tabs_issue_ai_parties_content', 'wpas_it_admin_tabs_issue_ai_parties_content' );
add_filter( 'wpas_admin_tabs_issue_additional_agents_content', 'wpas_it_admin_tabs_issue_additional_agents_content' );
?>


<div class="wpas-ticket-addl-parties-mb">
	<?php echo wpas_admin_tabs( 'issue' ); ?>
</div>