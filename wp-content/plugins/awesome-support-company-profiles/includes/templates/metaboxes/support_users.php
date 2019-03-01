<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

global $pagenow, $post;

$support_user_widget = WPAS_CP_Support_User::get_instance();


$support_user_widget->display( $post->ID );
	
?>