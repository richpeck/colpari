<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Framework admin enqueue style and scripts
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if( ! function_exists( 'cs_admin_enqueue_scripts' ) ) {
  function cs_admin_enqueue_scripts() {

    // admin utilities
    wp_enqueue_media();

    // wp core styles
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_style( 'wp-jquery-ui-dialog' );

    // framework core styles
    wp_enqueue_style( 'cs-framework', CS_URI .'/assets/css/cs-framework.css', array(), '1.0.0', 'all' );
    wp_enqueue_style( 'font-awesome', CS_URI .'/assets/css/font-awesome.css', array(), '4.2.0', 'all' );
    // wp_enqueue_style( 'material-icons', 'https://fonts.googleapis.com/icon?family=Material+Icons', array(), '3.0.0', 'all' );
    wp_enqueue_style( 'feather-icons', CS_URI .'/assets/css/feather-icons.css', array(), '1.0', 'all' );
    // wp_enqueue_style( 'ionicons', CS_URI .'/assets/css/ionicons.css', array(), '3.0.0', 'all' );
    // wp_enqueue_style( 'foundation-icons', CS_URI .'/assets/css/foundation-icons.css', array(), '3.0.0', 'all' );
    // wp_enqueue_style( 'socicon', 'https://d1azc1qln24ryf.cloudfront.net/114779/Socicon/style-cf.css?rd5re8', array(), '3.5.2', 'all' );
    // wp_enqueue_style( 'icomoon', CS_URI .'/assets/css/icomoon-icons.css', array(), '2015', 'all' );

    if ( is_rtl() ) {
      wp_enqueue_style( 'cs-framework-rtl', CS_URI .'/assets/css/cs-framework-rtl.css', array(), '1.0.0', 'all' );
    }

    // wp core scripts
    // --------------------------------------------------------------------
    wp_enqueue_script( 'wp-color-picker' );
    wp_enqueue_script( 'jquery-ui-dialog' );
    wp_enqueue_script( 'jquery-ui-sortable' );
    wp_enqueue_script( 'jquery-ui-accordion' );

    // framework core scripts
    // --------------------------------------------------------------------
    // Bundled Plugins
    wp_enqueue_script( 'cs-plugins',    CS_URI .'/assets/js/cs-plugins.js',    array(), '1.0.0', true );
    
    // ACE scripts
    wp_enqueue_script( 'cs-vendor-ace', CS_URI .'/assets/js/vendor/ace/ace.js', array(), '1.0.0', true );
    wp_enqueue_script( 'cs-vendor-ace-mode', CS_URI .'/assets/js/vendor/ace/mode-css.js', array( 'cs-vendor-ace' ), '1.0.0', true );
    wp_enqueue_script( 'cs-vendor-ace-language_tools', CS_URI .'/assets/js/vendor/ace/ext-language_tools.js', array( 'cs-vendor-ace' ), '1.0.0', true );
    
    // Deserialize
    wp_enqueue_script( 'jquery-deserialize', CS_URI .'/assets/js/vendor/jquery.deserialize.js', array(), '1.0', true );

    // Framework
    wp_enqueue_script( 'cs-framework',  CS_URI .'/assets/js/cs-framework.js',  array( 'cs-plugins' ), '1.0.0', true );


    // Custom fields scripts and styles
    // @description Added to work with premium extensions by Castor Studio
    do_action( 'cs_enqueque_fields_scripts' );

  }
  add_action( 'admin_enqueue_scripts', 'cs_admin_enqueue_scripts' );
}
