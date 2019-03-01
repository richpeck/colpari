<?php
include dirname( dirname( dirname( dirname( __FILE__  )))).'/wp-config.php';
$wpdb = rns_get_wpdb();
header("content-type:application/json");

if(isset($_POST)) { // if data post from form
	
	$assigned_roles = "";
	$short_name   	=  filter_input( INPUT_POST, 'sname', FILTER_SANITIZE_SPECIAL_CHARS );
	$long_name    	=  filter_input( INPUT_POST, 'lname', FILTER_SANITIZE_SPECIAL_CHARS );
	$description  	=  filter_input( INPUT_POST, 'desc', FILTER_SANITIZE_SPECIAL_CHARS );
	$sort_order   	=  filter_input( INPUT_POST, 'report_order', FILTER_SANITIZE_SPECIAL_CHARS );
	$report_link  	=  filter_input( INPUT_POST, 'report_link', FILTER_DEFAULT );
	$roles_post   	=  filter_input( INPUT_POST, 'rns_role_type', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	$report_id      =  filter_input( INPUT_POST, 'rns_report_id', FILTER_DEFAULT );  
		
	
	if( isset( $roles_post ) && count( $roles_post ) > 0  ) {//if roles select by user	
		$assigned_roles = implode( ",", $roles_post );
	}
	
	$post_meta = array(
						"_rns_sort_order"   => $sort_order,
						"_rns_report_link"  => $report_link,
						"_rns_assign_roles" => $assigned_roles,
						"_rns_long_name"    => $long_name,
						"_rns_author_id"    => get_current_user_id()
					  );
				  
	if( !empty( $report_id )  &&  rns_check_save_report_author_id( $report_id ) ) { // if current user post this report 
					  
		wp_update_post( array( 'ID' => $report_id ,'post_title'=>$short_name, 'post_type'=>'rns_report', 'post_content'=>$description));
		foreach( $post_meta as $meta_key=>$meta_value ) {
	
			update_post_meta($report_id, $meta_key, $meta_value );
		}
		
		die(json_encode(array("success"=>true,"message"=>"<font color='#009900'>".__( "Report updated successfully - we will now return you to your report screen." , 'reports-and-statistics' )."</font>")));
		
	} else {
		
		$post_id = wp_insert_post(array('post_title'=>$short_name, 'post_type'=>'rns_report', 'post_content'=>$description));				  		
		foreach( $post_meta as $meta_key=>$meta_value ) {
	
			add_post_meta($post_id, $meta_key, $meta_value, true);
		}
		
		die(json_encode(array("success"=>true,"message"=>"<font color='#009900'>".__( "Report saved successfully - we will now return you to your report screen.", 'reports-and-statistics'  ) ."</font>")));
		
	}
	
}