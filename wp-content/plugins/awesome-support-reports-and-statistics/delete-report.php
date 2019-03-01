<?php 
include dirname( dirname( dirname( dirname( __FILE__  )))).'/wp-config.php';
$wpdb = rns_get_wpdb();
header("content-type:application/json");

$report_id = filter_input( INPUT_GET, 'report_id', FILTER_SANITIZE_SPECIAL_CHARS) ;

if( !empty( $report_id ) && $report_id>0 ) {
	wp_delete_post($report_id);
	die(json_encode(array("success"=>true,"message"=>"Report deleted successfully.")));
}
