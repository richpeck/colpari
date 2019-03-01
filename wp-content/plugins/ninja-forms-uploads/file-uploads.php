<?php
/*
Plugin Name: Ninja Forms - File Uploads
Plugin URI: http://ninjaforms.com
Description: File Uploads add-on for Ninja Forms.
Version: 3.0.19
Author: The WP Ninjas
Author URI: http://ninjaforms.com
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main function responsible for returning the one true instance to functions everywhere.
 */
if( ! function_exists( 'NF_File_Uploads' ) ) {
    function NF_File_Uploads()
    {
        // Load our main plugin class
        require_once dirname(__FILE__) . '/includes/file-uploads.php';
        $version = '3.0.19';

        return NF_FU_File_Uploads::instance(__FILE__, $version);
    }
}

NF_File_Uploads();
