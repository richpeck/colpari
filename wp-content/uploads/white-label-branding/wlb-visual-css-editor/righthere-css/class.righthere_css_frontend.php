<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class righthere_css_frontend {
	var $enable_cached_file;
	function __construct( $footer=false ){
		//--- this is shared by righthere plugins, should only run one time for all plugins and uses the following site option names:
		//righthere_css_url
		//righthere_enable_css_file
		if(defined('RH_CSS_FRONTEND'))return;
		define('RH_CSS_FRONTEND',1);
		$this->enable_cached_file = get_option('righthere_enable_css_file',false)?true:false;
		if( $footer ){
			add_action('wp_footer',array(&$this,'wp_head'),999999);
			add_action('admin_footer',array(&$this,'wp_head'),999999);
			add_action('login_footer',array(&$this,'wp_head'),999999);		
		}else{
			add_action('wp_head',array(&$this,'wp_head'),999999);
			add_action('admin_head',array(&$this,'wp_head'),999999);
			add_action('login_head',array(&$this,'wp_head'),999999);
		}
		
		add_action('rh_css',array(&$this,'rh_css'));	
		if( $this->enable_cached_file ){
			add_filter('righthere_css_url',array(&$this,'righthere_css_url'),10,1);
		}
		add_action('saved-rh-css',array(&$this,'rh_css_settings_saved'),99,2);
	}
	
	function wp_head(){
		if(did_action('rh_css')>0)return;//do this only once.
		do_action('rh_css');
	}
	
	function rh_css(){	
		if(did_action('after_rh_css')>0)return;//do this only once.
		$url = apply_filters('righthere_css_url','');
		if($url!==''){
			echo sprintf('<link id="righthere-plugins-stylesheet" type="text/css" media="all" rel="stylesheet"  href="%s" />',$url);
		}else{
			$css = apply_filters('filter_rh_css','');
			echo sprintf('<style id="righthere_css" type="text/css">%s</style>',$css);		
		}
		do_action('after_rh_css');
	}	
	//--- use a saved file
	function righthere_css_url($url){
		return get_option('righthere_styles_url','');
	}
	
	function rh_css_settings_saved($notused1,$notused2){
		$css = apply_filters('filter_rh_css','');
		$upload_dir = wp_upload_dir();
		if(isset($upload_dir['error']) && false==$upload_dir['error']){
			$filename = wp_unique_filename( $upload_dir['path'], 'righthere_styles.css' );
			$save_filename = $upload_dir['path']."/".$filename;
			if(false!==file_put_contents($save_filename,$css)){
				$url = $upload_dir['url']."/".$filename;
				update_option('righthere_styles_url',$url);
			}
		}
	}
}
 
?>