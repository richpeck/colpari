<?php if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Plugin Name: Ninja Forms - Save Progress
 * Description: Save Progress add-on for Ninja Forms.
 * Version: 3.0.15
 * Author: The WP Ninjas
 * Author URI: http://ninjaforms.com
 * Text Domain: ninja-forms-save-progress
 * Domain Path: /lang/
 *
 * Copyright 2016 WP Ninjas.
 */

if( version_compare( get_option( 'ninja_forms_version', '0.0.0' ), '3', '<' ) || get_option( 'ninja_forms_load_deprecated', FALSE ) ) {

    // This section intentionally left blank

} else {

    require_once plugin_dir_path( __FILE__ ) . 'includes/autoload.php';

    if( ! function_exists( 'NF_SaveProgress' ) ) {
        function NF_SaveProgress()
        {
            static $instance;
            if( ! isset( $instance ) ) {
                $instance = new NF_SaveProgress( '3.0.15', __FILE__ );
            }
            return $instance;
        }
    }
    NF_SaveProgress();
}
