<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class rhcss_pop_options {
	function __construct($plugin_id='rhcss',$capability='manage_options',$open=false){
		if(current_user_can($capability)){
			$this->id = $plugin_id;
			$this->open = $open;
			add_filter("pop-options_{$this->id}",array(&$this,'options'),10,1);		
			add_action('pop_handle_save',array(&$this,'pop_handle_save'),50,1);			
		}
	}
	
	function options($t){
		$i = count($t);
		//----
		$t[$i]=(object)array();
		$t[$i]->id 			= 'rhcss-editor'; 
		$t[$i]->open 			= $this->open; 
		$t[$i]->label 		= __('CSS Editor','rhcss');
		$t[$i]->right_label	= __('CSS Editor settings','rhcss');
		$t[$i]->page_title	= __('CSS Editor','rhcss');
		$t[$i]->theme_option = true;
		$t[$i]->plugin_option = true;
		$t[$i]->options = array(			
			(object)array(
				'id'		=> 'enable_css_cache_file',
				'label'		=> __('Enable css customization cache file','rhcss'),
				'type'		=> 'onoff',
				'value'		=> '1'==get_option('righthere_enable_css_file','0')?'1':'0',
				'description'=> sprintf('<p>%s</p><p>%s</p>',
					__('Check this option when you have finished customizing the plugin css.  It will generate a stylesheet file instead of rendering customization in line.','rhcss'),
					__('Observe that this applies to all RightHere plugin so if you enable or disable it on any of RightHere plugins, it will apply the same setting to all of them','rhcss')
				),
				'save_option'=>false,
				'load_option'=>false				
			)	
		);	
		
		$t[$i]->options[]=(object)array(
				'id'		=> 'enable_css_editor',
				'label'		=> __('Enable CSS Editor','rhcss'),
				'type'		=> 'onoff',
				'default'	=> '1',
				'description'=>  __('Choose yes to enable the CSS Editor for this plugin.','rhcss'),
				'el_properties'	=> array(),
				'hidegroup'	=> '#css_editor_group',
				'save_option'=>true,
				'load_option'=>true
				);
				
		$t[$i]->options[]=(object)array('type'	=> 'clear');
		$t[$i]->options[]=(object)array(
				'id'	=> 'css_editor_group',
				'type'=>'div_start'
			);		
		
		
						
		$t[$i]->options = apply_filters('rhcss-editor-options-'.$this->id, $t[$i]->options);
		
		$t[$i]->options[]=(object)array('type'	=> 'div_end');
		
		$t[$i]->options[]=(object)array(
				'type'=>'clear'
			);
		$t[$i]->options[]=(object)array(
				'type'	=> 'submit',
				'label'	=> __('Save','rhcss'),
				'class' => 'button-primary'
			);
			
		//----
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'rhcss-editor-troubleshooting'; 
		$t[$i]->label 		= __('Troubleshooting','rhcss');
		$t[$i]->right_label	= __('Troubleshooting','rhcss');
		$t[$i]->page_title	= __('Troubleshooting','rhcss');
		$t[$i]->theme_option = true;
		$t[$i]->plugin_option = true;
		
		//$t[$i]->options[]=;	
		
		$t[$i]->options = array(			
			(object)array(
				'id'		=> 'enable_css_editor_debug',
				'label'		=> __('Debug mode','rhcss'),
				'type'		=> 'onoff',
				'default'	=> '0',
				'description'=>  __('Toggle css editor debug mode on or off.','rhcss'),
				'el_properties'	=> array(),
				'save_option'=>true,
				'load_option'=>true
				),		
			(object)array(
				'id'		=> 'bootstrap_in_footer',
				'label'		=> __('Bootstrap in footer','rhcss'),
				'type'		=> 'yesno',
				'description'=> sprintf('<p>%s</p>',
					__('jQuery-ui and boostrap have a conflict with buttons.  Check yes if you are getting this javascript error in the console "cannot call methods on button prior to initialization; attempted to call method loading"','rhcss')
				),
				'save_option'=>true,
				'load_option'=>true				
			),
			(object)array(
				'id'		=> 'js_in_footer',
				'label'		=> __('Scripts in footer','rhcss'),
				'type'		=> 'yesno',
				'description'=> sprintf('<p>%s</p>',
					__('Print editor scripts in footer.  This may help with some themes that overwrite some of the editor js plugins.','rhcss')
				),
				'save_option'=>true,
				'load_option'=>true				
			),			
			(object)array(
				'id'		=> 'alternate_accordion',
				'label'		=> __('Load alternate accordion','rhcss'),
				'type'		=> 'yesno',
				'description'=> sprintf('<p>%s</p>',
					__('Some themes break the CSS Editor accordion.  Check yes to try with an alternate accordion script.','rhcss')
				),
				'save_option'=>true,
				'load_option'=>true				
			)					
		);				
		$t[$i]->options[]=(object)array(
				'type'=>'clear'
			);
		$t[$i]->options[]=(object)array(
				'type'	=> 'submit',
				'label'	=> __('Save','rhcss'),
				'class' => 'button-primary'
			);
		return $t;
	}
	
	function pop_handle_save($pop){
		//no validation needed since this hook is only added by capability.
		if(isset($_POST['enable_css_cache_file'])){
			if( $_POST['enable_css_cache_file']=='1' ){
				update_option('righthere_enable_css_file','1');
				do_action('saved-rh-css',null,null);
			}else{
				update_option('righthere_enable_css_file','0');
			}
		}
	}	
}
?>