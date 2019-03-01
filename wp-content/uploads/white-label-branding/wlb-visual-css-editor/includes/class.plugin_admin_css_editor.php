<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class plugin_admin_css_editor {
	var $id = 'ace-css';
	var $righthere_css_version='1.0.1';
	var $options_varname = 'ace_options';
	function __construct( $args=array() ){		
		//------------
		$defaults = array(
			'id'				=> 'ace',
			'tdom'				=> 'ace',
			'plugin_code'		=> 'ACE',
			'options_varname'	=> 'ace_options',
			'cb_get_option'		=> null,
			'options_parameters'=> array(),
			'options_capability'=> 'manage_options',
			'license_capability'=> 'manage_options',
			'resources_path'	=> 'admin-css-editor',
			'options_panel_version'	=> '2.6.1',
			'debug_menu'		=> false,
			'autoupdate'		=> true,
			'registration'			=> true,
			'downloadables'			=> true,
			'option_menu_parent'	=> 'options-general.php',
			'pop_layout'			=> 'horizontal',
			'menu_text'				=> __('Admin CSS','rhace'),
			'page_title'			=> __('Admin CSS','rhace'),
			'load_pop'				=> true,
			'pop_hook'				=> 'plugins_loaded'
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}

		//-----------
		// Integration point #1
		// Register the bundled righthere_css module.
		require_once ACE_PATH.'righthere-css/load.php';//this file contains the same as load.pop.php from options panel, so only one needs be loaded.
		rh_register_php('righthere-css',ACE_PATH.'righthere-css/class.module_righthere_css.php', $this->righthere_css_version);		
		rh_register_php('righthere-css-frontend',ACE_PATH.'righthere-css/class.righthere_css_frontend.php', $this->righthere_css_version);	
		rh_register_php('rh-google-fonts-admin',ACE_PATH.'righthere-css/class.google_web_fonts_admin.php', $this->righthere_css_version);
		rh_register_php('rh-functions', ACE_PATH.'righthere-css/rh-functions.php', '1.0.0');
		rh_register_php('rh-edit-admin-bar',ACE_PATH.'righthere-css/class.admin_bar_editor_access.php', $this->righthere_css_version);	
		rh_register_php('rhcss-options',ACE_PATH.'righthere-css/class.rhcss_pop_options.php', $this->righthere_css_version);		
		//-----------
		if( is_callable($this->cb_get_option) ){
			$this->options = call_user_func( $this->cb_get_option, $this->options_varname );
		}else{
			$this->options = get_option($this->options_varname);			
		}
		$this->options = is_array($this->options)?$this->options:array();	
		//-----------
		if( $this->load_pop && is_admin()){
			require_once ACE_PATH.'options-panel/load.pop.php';
			rh_register_php('options-panel',ACE_PATH.'options-panel/class.PluginOptionsPanelModule.php', $this->options_panel_version);
			rh_register_php('rh-functions', ACE_PATH.'options-panel/rh-functions.php', $this->options_panel_version);
		}
		//-----------
		add_action('plugins_loaded', array(&$this,'plugins_loaded'));
		add_action( $this->pop_hook, array(&$this,'load_options_panel'));
		
		
		add_action('admin_menu',array(&$this,'admin_init'));	
		
		//--bug fix ms
		if( is_multisite() ){
			add_filter( 'authenticate', array( &$this, 'wp_authenticate_user' ), 999, 3 );			
		}
	}
	
	function wp_authenticate_user( $a, $b, $c ){
		if( isset( $_REQUEST['ace_edit'] ) ){	
			return new WP_Error( 'ace', __( "Using css editor.", "ace" ) );;//do not rediret if ace editor is active.
		}
		return $a;
	}
		
	function admin_init(){
		wp_enqueue_style( 'ace-bootstrap-fix', ACE_URL.'includes/css/style.css', array(),'1.0.0');	
	}
	
	function plugins_loaded(){
		global $ace_plugin;
		// Integration point #2
		//usually por loading pop-panel, but now also loads the css editor module.
		do_action('rh-php-commons');	

		new google_web_fonts_admin(array(
			'path' 	=> ACE_PATH.'righthere-css/',
			'url'	=> ACE_URL.'righthere-css/'
		));
		
		//load the frontend output
		new righthere_css_frontend();	
	
		$editor_enabled = $ace_plugin->get_option('enable_css_editor','1',true);
		$editor_debug 	= $ace_plugin->get_option('enable_css_editor_debug','',true);
		$editor_debug 	= '1'==$editor_debug?true:false;
		
		if($editor_enabled){
			// Integration point #3
			// Include the integration class by the current plugin

			require_once ACE_PATH.'includes/class.rhcss_admin_menu.php';		
			$settings = array(
				'url'						=> ACE_URL.'righthere-css/',
				'path'						=> ACE_PATH.'righthere-css/',
				'plugin_id'					=> $this->id,
				'version'					=> '1.0.0',
				'capability'				=> 'manage_options',
				'options_varname'			=> $this->options_varname,
				'cb_get_option'				=> array(&$this,'get_option'),
				'resources_path'			=> 'admin-css-editor',			
				'file_queue_options_name' 	=> 'ace_queue',
				'upload_limit_per_index'	=> 20,		
				'debug'						=> $editor_debug,
				'detect_selector'			=> 'body.wp-admin',
				//--
				'id'						=> $this->id,
				'trigger_var'				=> 'ace_edit',
				'trigger_val'				=> 'menu',
				'bootstrap_in_footer'		=> true,
				'in_admin'					=> true,
				'in_login'					=> false,
				'queue'						=> 'wlb_backgrounds' 
				
			);			

			new rhcss_admin_menu($settings);		
			
			require_once ACE_PATH.'includes/class.rhcss_admin_bar.php';	
			//$settings['id']			='all_views';
			$settings['section']	='toolbar';
			$settings['trigger_val']='toolbar';
			new rhcss_admin_bar($settings);
			
			require_once ACE_PATH.'includes/class.rhcss_admin_main.php';	
			//$settings['id']			='all_views';
			$settings['section']	='admin_main';
			$settings['trigger_val']='admin_main';
			new rhcss_admin_main($settings);
			
			require_once ACE_PATH.'includes/class.rhcss_admin_help_screen_options.php';	
			//$settings['id']			='all_views';
			$settings['section']	='admin_help_tab';
			$settings['trigger_val']='admin_help_tab';
			new rhcss_admin_help_screen_options($settings);
			
			require_once ACE_PATH.'includes/class.rhcss_admin_forms.php';	
			//$settings['id']			='all_views';
			$settings['section']	='admin_forms';
			$settings['trigger_val']='admin_forms';
			new rhcss_admin_forms($settings);
			
			require_once ACE_PATH.'includes/class.rhcss_wp_login.php';	
			//$settings['id']			='all_views';
			$settings['section']	='wp_login';
			$settings['trigger_val']='wp_login';
			$settings['in_login']=true;
			$settings['in_admin']=false;
			$settings['alternate_accordion']=true;
			
			$settings['detect_selector']='body.login';
			new rhcss_wp_login($settings);			

			/*
			//----- Include a second editable content
			require_once ACE_PATH.'includes/class.rhcss_editor_all_views.php';	
			//$settings['id']			='all_views';
			$settings['section']	='all_views';
			$settings['trigger_val']='all_views';
			new rhcss_editor_all_views($settings);
			*/
		}	

		//option fields and admin bar links
		require_once ACE_PATH.'includes/class.ace_css_options.php';
		new ace_css_options(array('plugin_id'=>$this->id, 'admin_bar'=>$editor_enabled));			


		
	}

	function load_options_panel(){
		// Integration point #5
		// add tab to options panel
		if( is_admin() ){
			global $ace_plugin;			
			//Creates the CSS Editor tab in the calendarize-it options
			new rhcss_pop_options($this->id,'manage_options',true);
		
			//--- create a separate menu for CSS Editor
			$license_keys = $this->get_option('license_keys',array());
			$license_keys = is_array($license_keys)?$license_keys:array();
			
			$api_url = 'secondary'==$this->get_option('righthere_api_url','',true) ? 'http://plugins.albertolau.com/' : 'http://plugins.righthere.com/';
						
			$dc_options = array(
				'id'			=> $this->id.'-dc',
				'plugin_id'		=> $this->id,
				'capability'	=> $this->options_capability,
				'resources_path'=> $this->resources_path,
				'parent_id'		=> $this->option_menu_parent,
				'menu_text'		=> __('Admin CSS Downloads','rhc'),
				'page_title'	=> __('Downloadable content - Visual CSS Editor for WordPress','ace'),
				'license_keys'	=> $license_keys,
				'plugin_code'	=> $this->plugin_code,
				'api_url'		=> $api_url,
				'product_name'	=> __('Calendarize-it','rhc'),
				'options_varname' => $this->options_varname,
				'tdom'			=> 'rhc'
			);
						
			$settings = array(				
				'id'					=> $this->id,
				'plugin_id'				=> $this->id,
				'capability'			=> $this->options_capability,
				'capability_license'	=> $this->license_capability,
				'options_varname'		=> $ace_plugin->options_varname,
				'menu_id'				=> 'ace-css-options',
				'page_title'			=> $this->page_title,
				'menu_text'				=> $this->menu_text,
				'option_menu_parent'	=> $this->option_menu_parent,
				//'option_menu_parent'	=> $this->id,
				'notification'			=> (object)array(
					'plugin_version'=> ACE_VERSION,
					'plugin_code' 	=> 'ACE',
					'message'		=> __('Visual CSS Editor for WordPress update %s is available! <a href="%s">Please update now</a>','rch')
				),
				'theme'					=> false,
				'stylesheet'			=> 'ace-options',
				'option_show_in_metabox'=> true,
				'path'					=> ACE_PATH.'options-panel/',
				'url'					=> ACE_URL.'options-panel/',
				'pluginslug'			=> ACE_SLUG,
				//'api_url' 		=> "http://localhost"
				'api_url' 				=> "http://plugins.righthere.com",
				'dc_options'			=> $dc_options,
				'layout'				=> $this->pop_layout
			);						
			
			$settings['registration'] = $this->registration;
			$settings['downloadables'] = $this->downloadables;
			
			new PluginOptionsPanelModule($settings);						
		}
	}
	
	function get_option($name,$default='',$default_if_empty=false){
		$value = isset($this->options[$name])?$this->options[$name]:$default;
		if($default_if_empty){
			$value = ''==$value?$default:$value;
		}
		return $value;
	}	
	
	function update_options($options){
		update_option($this->options_varname,$options);
	}		
}
?>