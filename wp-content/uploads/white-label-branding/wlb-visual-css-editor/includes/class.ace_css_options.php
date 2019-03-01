<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
class ace_css_options {
	function __construct($args=array()){
		add_filter('rhcss-editor-options-'.$args['plugin_id'], array(&$this,'pop_tab_css_editor_options'),10,1);
		if($args['admin_bar']){
			add_action('init',array(&$this,'init'),9999 );
		}
		add_action('admin_head-events_page_ace-css-options',array(&$this,'admin_head_options'));
	}

	function admin_head_options(){
?>
<style>
.pt-option-yesno .pt-label {
display:inline-block;
min-width:220px;
}
</style>
<?php
	}
	
	
	function pop_tab_css_editor_options($options){
		//add more options to the CSS Editor Tab
		//------------------------------------------------------------------------
		return $options;
	}
		
	function init(){
		global $ace_plugin;
		if( '1'!=$ace_plugin->get_option('enable_css_editor','1',true) )return;//editor is not enabled. do not add items to the menu.
	
		// Provide access to the css editor
		//-- create editor quick access links
		new admin_bar_editor_access(array(
			'nodes'=>array(
				array(
					'id' => 'wp-admin', 
					'title' => 'White Label Branding', 
					'parent' => 'rh-css-editor-root',
					'href'		=> '#',
					'meta'		=> array('onclick'=>'javascript:jQuery(this).parent().toggleClass("hover");')
				),
				array(
				 	'id' 		=> 'wp-admin-menu', 
					'title' 	=> __('Menu','rhace'), 
					'parent' 	=> 'wp-admin',
					'href'		=> $this->addURLParameter( admin_url('/') , 'ace_edit', 'menu') 
				),
				array(
				 	'id' 		=> 'wp-admin-main', 
					'title' 	=> __('Body','rhace'), 
					'parent' 	=> 'wp-admin',
					'href'		=> $this->addURLParameter( admin_url('/') , 'ace_edit', 'admin_main') 
				),
				array(
				 	'id' 		=> 'toolbar', 
					'title' 	=> __('Toolbar','rhace'), 
					'parent' 	=> 'wp-admin',
					'href'		=> $this->addURLParameter( admin_url('/') , 'ace_edit', 'toolbar') 
				),
				array(
				 	'id' 		=> 'wp-admin-forms', 
					'title' 	=> __('Admin forms','rhace'), 
					'parent' 	=> 'wp-admin',
					'href'		=> $this->addURLParameter( admin_url('/admin.php?page=ace-forms') , 'ace_edit', 'admin_forms') 
				),
				array(
				 	'id' 		=> 'wp-admin-help-tab', 
					'title' 	=> __('Help & Screen options tab','rhace'), 
					'parent' 	=> 'wp-admin',
					'href'		=> $this->addURLParameter( admin_url('/') , 'ace_edit', 'admin_help_tab') 
				),
				array(
				 	'id' 		=> 'wp-login', 
					'title' 	=> __('Default WP Login','rhace'), 
					'parent' 	=> 'wp-admin',
					'href'		=> $this->addURLParameter( $this->get_login_url(), 'ace_edit', 'wp_login' )
				)
				
				
			)
		));		
	}
	
	function get_login_url( $action='login' ){
		return $this->addURLParameter( wp_login_url(), 'action', $action );
	}
	
	function addURLParameter($url, $paramName, $paramValue) {
	     if(trim($url)=='')return '';
		 $url_data = parse_url($url);
	     if(!isset($url_data["query"])){
		 	$url_data["query"]="";
		 }
	     $params = array();
	     parse_str($url_data['query'], $params);
	     $params[$paramName] = $paramValue;
	     $url_data['query'] = http_build_query($params);
	     return $this->build_url($url_data);
	}

	function build_url($url_data) {
	    $url="";
	    if(isset($url_data['host']))
	    {
	        $url .= $url_data['scheme'] . '://';
	        if (isset($url_data['user'])) {
	            $url .= $url_data['user'];
	                if (isset($url_data['pass'])) {
	                    $url .= ':' . $url_data['pass'];
	                }
	            $url .= '@';
	        }
	        $url .= $url_data['host'];
	        if (isset($url_data['port'])) {
	            $url .= ':' . $url_data['port'];
	        }
	    }
	    $url .= $url_data['path'];
	    if (isset($url_data['query'])) {
	        $url .= '?' . $url_data['query'];
	    }
	    if (isset($url_data['fragment'])) {
	        $url .= '#' . $url_data['fragment'];
	    }
	    return $url;
	}		
}

?>