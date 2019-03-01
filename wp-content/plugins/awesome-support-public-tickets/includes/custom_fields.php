<?php

/**
 * Add custom fields
 */

 // If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
 
if ( function_exists( 'wpas_add_custom_field' ) ){

	$is_public = boolval( wpas_get_option( 'pbtk_public' ) );
	$is_shw_public = boolval( wpas_get_option( 'pbtk_shw_flag' ) );
	$is_shw_in_ticket_list = boolval( wpas_get_option( 'pbtk_shw_flag_ticketlist' ) );
		
	if($is_public == true){
		wpas_add_custom_field( 'pbtk_flag',
		array(
			'title' 			=> __( 'Public/Private'),
			'field_type' 		=> 'select',
			'options' 			=> array(
								'public' =>'Public', 
								'private' => 'Private'
								),
			'sortable_column' 	=> true,
			'filterable'		=> true,
			'show_column' 		=> $is_shw_in_ticket_list,
			'backend_only'		=> ! $is_shw_public
			)
		);
	} else{
		wpas_add_custom_field( 'pbtk_flag',  array(
			'title' 	 => __( 'Public/Private' ),
			'field_type' => 'select',
			'options'    => array(
				'private'=> 'Private',
				'public' => 'Public'
				),
			'sortable_column' => true,
			'filterable'		=> true,
			'show_column' 	  => $is_shw_in_ticket_list ,
			'backend_only'		=> ! $is_shw_public				
			) 
		);
	}

}