<?php

/**
Plugin Name: Visual CSS Editor for White Label Branding
Plugin URI: http://visual-css-editor.righthere.com
Description: Visual CSS Editor for WordPress is a easy to use CSS editor, which makes it possible to tweak CSS settings and fonts without any knowledge of CSS.
Version: 1.1.2.76738
Author: Alberto Lau (RightHere LLC)
Author URI: http://plugins.righthere.com
 **/

define('ACE_VERSION','1.1.2'); 
define("ACE_SLUG", plugin_basename( __FILE__ ) );
define("ACE_ADMIN_ROLE", 'administrator');

if(defined('ACE_PATH')) throw new Exception( __('A duplicate of this addon/plugin is already active.','wlb') );
 
if(defined('WLB_ADDON_PATH')){
	define('ACE_PATH', trailingslashit(WLB_ADDON_PATH . dirname($addon)) ); 
	define("ACE_URL", trailingslashit(WLB_ADDON_URL . dirname($addon)) );
}else{
	define('ACE_PATH', plugin_dir_path(__FILE__) ); 
	define("ACE_URL", plugin_dir_url(__FILE__) );
} 

 
require_once ACE_PATH.'includes/class.plugin_admin_css_editor.php';

global $ace_plugin,$wlb_plugin;

if( defined('WLB_VERSION') ){
	if( $wlb_plugin->multisite ){
		
		require_once ACE_PATH.'class.wlb_visual_css_editor_multisite.php';
		$wlbms_ace = new wlb_visual_css_editor_multisite(array(
			'id'					=> 'acewlbms'
		));
		
		$ace_plugin = new plugin_admin_css_editor( array(
			'id'					=> 'acewlbms',
			'registration'			=> false,
			'downloadables'			=> false,
			'resources_path'		=> $wlb_plugin->resources_path,
			'options_capability'	=> 'wlb_branding',
			'license_capability'	=> 'wlb_branding',
			'cb_get_option'			=> array( $wlbms_ace, 'get_option' ),	
			'load_pop'				=> false,
			'pop_hook'				=> 'wlb_pop_before_options',
			'option_menu_parent'	=> $wlb_plugin->id,
			'menu_text'				=> __('CSS Editor','wlb'),
			'page_title'			=> __('CSS Editor','wlb')
		) );	
	}else{
		$ace_plugin = new plugin_admin_css_editor( array(
			'registration'			=> false,
			'downloadables'			=> false,
			'resources_path'		=> $wlb_plugin->resources_path,
			'load_pop'				=> false,
			'pop_hook'				=> 'wlb_pop_before_options',
			'option_menu_parent'	=> $wlb_plugin->id,
			'menu_text'				=> __('CSS Editor','wlb'),
			'page_title'			=> __('CSS Editor','wlb')
		) );
	}
}

?>