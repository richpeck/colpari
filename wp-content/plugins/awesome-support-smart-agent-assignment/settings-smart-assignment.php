<?php

add_action( 'wp_ajax_post_algo' , 'post_algo' , 10 , 0 );
add_action( 'wp_ajax_post_user_roles' , 'post_user_roles' , 10 , 0 );
add_filter( 'wpas_plugin_settings' , 'wpas_settings_smart_assignment' , 10 , 1 );
add_action('wpas_settings_smart_assignment', array('Smart_Agent_Assignment', 'load_my_transl'));

if ( is_admin() && isset( $_GET['page'] ) && isset( $_GET['tab'] ) && ( $_GET['page'] == 'wpas-settings' ) && ( $_GET['tab'] == 'smart-assignment-options' ) ) {
	
	add_action( 'admin_enqueue_scripts' ,'sm_enqueue_scripts' , 10 , 0 );

}

/**
 * Add settings for Smart Agent Assignment addon.
 * @since 2.0
 * @param  (array) $def Array of existing settings
 * @return (array)      Updated settings
 */

function wpas_settings_smart_assignment( $def ) {
	
	$algos			=	get_algorithms();
	$roles 			=	new WP_Roles();
	$roleFields 	=	'';
	$default_role	=	'wpas_agent';

	$default_algo	=	5; //default algo set to 5th one
	
	$sma_algo		=	wpas_get_option('smart_agent_algorithm');  //get algorithm value
	
	if( !$sma_algo ) { //if default setting is not saved
	
		$opt		=	unserialize( get_option( 'wpas_options' ) );
		$opt['smart_agent_algorithm']	=	$default_algo;
		update_option( 'wpas_options' ,serialize( $opt ) );

	}

	$currRoles		=	get_option( 'wpas_role_agents' , true );

	if( $currRoles != ''  && ! empty( $currRoles ) ) {
		$selectedRoles	=	explode( "," , $currRoles );
	}
	else {
		$selectedRoles	=	array();
	}
	
	
	$note	=	'<p></p>';
	
	if( !wpas_get_option( 'support_products' ) ) {
		
		$note	.=	__( '<p><em><strong>NOTE:</strong> Enable multiple product support in Awesome Support settings for algorithms - Product and Agent Availability #1, Product and Agent Availability #2</em></p>' , 'smart-agent-assignment' );
			
	}
	if( !wpas_get_option( 'departments' ) ) {
		
		$note	.=	__( '<p><em><strong>NOTE:</strong> Enable departments in Awesome Support settings for algorithms - Department and Agent Availability #1, Department and Agent Availability #2</em></p>' , 'smart-agent-assignment' );	
	
	}
	
	
	
	if( !$default_role ) { //if default setting is not saved
	
		$opt		=	unserialize( get_option( 'wpas_options' ) );
		$opt['smart-assignment-options-roles']	=	$default_role;
		update_option( 'wpas_options' , serialize( $opt ) );
	
	}
	$tabHTML		=	'<tr class="row-1 odd" valign="top"><th scope="row" class="first">
							<label for="wpas_smart_agent_algorithm">'.__( 'User Roles' , 'smart-agent-assignment' ).'</label>
						</th>
						<td class="second tf-select">
						{FIELDS}	
						</td></tr>';
	$Fields			=	'';
	
	foreach( $roles->get_names() as $key=>$val ) {
		
			if( !empty( $selectedRoles ) && in_array( $key , $selectedRoles ) ) {
				$Fields	.=	'<input type="checkbox" class="wpas_agent_role_type" name="wpas_agent_role_type[]" checked="checked" id="' . $key . '" value="' . $key . '" ><label for="' . $key . '">' . __( $val , 'smart-agent-assignment' ) . '</label><br/>';
			}
			else {
				$Fields	.=	'<input type="checkbox" class="wpas_agent_role_type" name="wpas_agent_role_type[]" id="' . $key . '" value="' . $key . '" ><label for="' . $key . '">' . __( $val , 'smart-agent-assignment' ) . '</label><br/>';
			}
				
	}
	
	$tabHTML	=	str_replace( '{FIELDS}' , $Fields , $tabHTML );
	
	$settings	=	array(
		'smart-assignment-options' => array(
			'name'    => __( 'Smart Assignment' , 'smart-agent-assignment' ),
			'options' => array(
				array(
					'name' => __( 'Set the algorithm used to check available agents' , 'smart-agent-assignment' ) ,
					'type' => 'heading' ,
				),
				array(
					'name'		=>	__( "Assignment Algorithm" , 'smart-agent-assignment' ) ,
					'id'		=>	'smart_agent_algorithm' ,
					'type'		=>	'select' ,
					'default'	=>	$default_algo ,
					'options'	=>	$algos ,
					'desc'		=>	$note ,
				) ,
				array(
					'name'		=>	__( 'Smart Assignment Roles' , 'smart-agent-assignment' ) ,
					'id'		=>	'smart_agent_roles_section' ,
					'type'		=>	'heading' ,
					'desc'		=>	__( 'Agents with the selected roles below will be the only ones that tickets are assigned to.' , 'smart-agent-assignment' ) ,
				) ,
				
				array(
					'type'		=>	'heading' ,
					'name'		=>	$tabHTML ,
				) ,
				
				array(
					'name'    => __( 'Roles That Can Set Agent Availability', 'awesome-support' ),
					'id'      => 'smart_agent_set_availability',
					'type'    => 'text',
					'desc'    => __( 'Admins can always set the working hours for agents.  However, you can set additional roles that are allowed to update agent working hours. Enter a comma separated list of roles in this text box. Roles should be the internal WordPress role id such as wpas_support_agent and are case sensitive. There should be no spaces between the commas and role names when entering multiple roles.', 'smart-agent-assignment' ),
					'default' => ''
				),				
				
				array(
					'name'		=>	__( 'Other' , 'smart-agent-assignment' ) ,
					'type'		=>	'heading' ,
					'desc'		=>	__( 'Misc settings.' , 'smart-agent-assignment' ) ,
				) ,
				
				array(
					'name'		=>	__( 'Channel Exclusions' , 'smart-agent-assignment' ) ,
					'id'		=>	'sa_channel_exclusions' ,
					'type'		=>	'text' ,
					'desc'		=>	__( 'Requires AS 4.4.0 or later: Do NOT run the selected smart algorithm for the following channels (enter channel ids separated by commas)' , 'smart-agent-assignment' ) ,
				) ,				
				
			)
		)
	);

	return array_merge( $def , $settings );

}

/**
 * Get all algorithm options
 *
 * @return array The list of algo names
 * @since  2.0
 */

function get_algorithms() {

	$algos	=	array(
		'1'		=>	__( '1. Product and Agent Availability #1' , 'smart-agent-assignment' ) ,
		'2'		=>	__( '2. Product and Agent Availability #2' , 'smart-agent-assignment' ) ,
		'3'		=>	__( '3. Departments and Agent Availability #1' , 'smart-agent-assignment' ) ,
		'4'		=>	__( '4. Departments and Agent Availability #2' , 'smart-agent-assignment' ) ,
		'5'		=>	__( '5. Agent Availability #1' , 'smart-agent-assignment' ) ,
		'6'		=>	__( '6. Product and Agent Availability #3' , 'smart-agent-assignment' ) ,
		'999'	=>	__( 'None - Use Core Algorithm' , 'smart-agent-assignment' ) ,
	);
	
	return $algos;
	
}
/**
* Scripts to include on settings page
*/
function sm_enqueue_scripts() {
	 
	wp_register_script( 'esa-admin-script' , AS_ESA_URL . 'js/algo.js' , array( 'jquery' ) , AS_ESA_VERSION , true );
	wp_localize_script( 'esa-admin-script' , 'esma' , array(
		'algourl'	=>	AS_ESA_URL . 'assignment-algorithms.php' ,
		'ajaxurl'	=>	admin_url( 'admin-ajax.php' ) ,
	));
	wp_enqueue_script( 'esa-admin-script' );
		
}

/**
* Get algorithm detail using ajax
* requires id of the algo $_POST['algo']
* the description sent as response
*/	

function post_algo() {

	$algo	=	filter_input( INPUT_POST , 'algo' , FILTER_SANITIZE_NUMBER_INT );
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) { 
		switch ( $algo ) {
			case 1:
				echo ( __( "<strong>Product and Agent Availability #1</strong><br/>1. Check for a set of agents that supports the product the user selected on the ticket. Then, from that set of agents, check for an agent currently working (based on the day and times set in their agent/user profile). <br/><br/> 2. If a working agent isn't found, then assign the ticket to the default agent (currently any agent with the least number of tickets). <br/><br/>3. If no product is entered on the ticket then check for any agent currently working regardless of product. If an agent is not found then assign the ticket to the default agent (any agent with the last number of tickets)." , 'smart-agent-assignment' ) );
				break;
	
			case 2:
				echo( __( "<strong>Product And Agent Availability #2</strong><br/>1. Check for a set of agents that supports the product the user selected on the ticket. Then, from that set of agents, check for an agent currently working (based on the day and times set in their agent/user profile). <br/><br/> 2. If an agent isn't found, then check for any agent assigned to that product regardless of working hours. <br/><br/>3. If one is not found then check for any agent with current working hours regardless of product. <br/><br/>4. If an agent is still not found then assign the ticket to the default agent (any agent with the least number of tickets). <br/> <br/>5. If no product is entered on the ticket then check for any currently working agent agent. If no agent is found then assign to the default agent." , 'smart-agent-assignment' ) );
				break;
	
			case 3:
				echo( __( "<strong>Departments And Agent Availability #1</strong><br/>1. Try to find a current working agent who has the same department as the ticket. <br/><br/>2. If no match then use the default agent (any agent with the least number of tickets)." , 'smart-agent-assignment' ) );
				break;
			
			case 4:
				echo( __( "<strong>Departments And Agent Availability #2</strong><br/>1. Try to find a current agent who has the same department as the ticket. <br/><br/>2. If no agent is found then use any available agent regardless of department. <br/><br/>3. If an agent is still not found then use the default agent (any agent with the least number of tickets)." , 'smart-agent-assignment' ) );
				break;
			
			case 5:
				echo( __( "<strong>Agent Availability #1</strong><br/>Check for a set of agents based on agents day and time availabilty only. If no agents exist use the default agent (any agent with the least number of tickets)." , 'smart-agent-assignment' ) );
				break;
				
			case 6:
				echo( __( "<strong>Product And Agent Availability #3</strong><br/>1. Check for a set of agents that supports the product the user selected on the ticket. Then, from that set of agents, check for an agent currently working (based on the day and times set in their agent/user profile). <br/><br/> 2. If an agent isn't found, then check for any agent assigned to that product regardless of working hours. <br/><br/>3. If one is not found then check for any agent with current working hours regardless of product. <br/> <br/>4. If no agent is found then assign to the default agent. <br/> <br/>Note. This is just a slight variation on algorithm #1 in that if there is no product set on the ticket it immediately assigns the default agent." , 'smart-agent-assignment' ) );
				break;
				
				
			case 999:
				echo( __( "<strong>Use Core</strong><br/> No smart assignment - just use the core algorithm (the agent with the least number of open tickets)." , 'smart-agent-assignment' ) );
				break;
				
			default:
				echo( __( "Undefined Algorithm" , 'smart-agent-assignment' ) );
				break ;
		}
		
		die();
		
	} else {
		
		echo( __( "No algorithm selected" , 'smart-agent-assignment' ) );
		exit();

	}
}

function post_user_roles() {
	
	if( isset( $_POST ) ) {
		
		if( isset( $_POST['wpas_agent_role_type'] ) && !empty( $_POST['wpas_agent_role_type'] ) ) {
			$roles = array();
			if ( get_option( 'wpas_role_agents' )	!==	false ) {
				
				$roles	=	implode(',' , $_POST['wpas_agent_role_type'] );
				update_option( 'wpas_role_agents' , $roles );
				
			}else{
				
				add_option( 'wpas_role_agents' , $roles );
				
			}
			
		}else{
			
			update_option( 'wpas_role_agents' , '' );
			
		}
		
	}
	
	die();
	
}