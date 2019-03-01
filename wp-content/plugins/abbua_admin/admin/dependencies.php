<?php
/* ===============================================================================================
    ADMIN FRAMEWORK
   =============================================================================================== */
function cs_framework_init_check() {
    if( ! function_exists( 'cs_framework_init' ) && ! class_exists( 'CSFramework' ) ) {
        // Plugin location of cs-framework.php
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'adminframework/cs-framework.php';
    }
}
add_action( 'plugins_loaded', 'cs_framework_init_check' );

/**
* Show/Hide Codestar Framework Examples
*
* @since 1.0
*
* Location of actual files is in the inc/codestar-framework/config/ directory
* Optionally, set each desired, to "false" to not display after plugin activation
**/
// define( 'CS_ACTIVE_FRAMEWORK', false); // default true
// define( 'CS_ACTIVE_METABOX', false); // default true
// define( 'CS_ACTIVE_TAXONOMY', false); // default true
define( 'CS_ACTIVE_SHORTCODE', false); // default true
define( 'CS_ACTIVE_CUSTOMIZE', false); // default true




/* ===============================================================================================
    LOAD PLUGIN EXTERNAL DEPENDENCY FILES
   =============================================================================================== */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/functions.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/define.php'; // Set plugin constants

// Init other actions before everything
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/class-abbua_admin-before-activator.php';
Abbua_admin_Before_Activator::activate();

// Admin Pages
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/page-menu_manager.php'; // Admin Page
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/page-dashboard.php';   // Admin Page