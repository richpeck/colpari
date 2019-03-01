<?php

/**
 * This class will be responsible of detecting a page to edit and show the editor tools.
 *
 * @version $Id$
 * @copyright 2003 
 **/

class module_righthere_css {
	function __construct($args=array()){
		$defaults = array(
			'id'						=> 'plugin-righthere-css',
			'section'					=> 'main',
			'with_scope'				=> true,
			'tdom'						=> 'rh_css',			
			'version'					=> '1.1.4',
			'pluginurl'					=> '',
			'path'						=> plugin_dir_path(__FILE__),
			'url'						=> plugin_dir_url(__FILE__),
			'capability'				=> 'manage_options',
			'debug'						=> false,
			'cb_get_option'				=> null,
			'resources_path'			=> '',//url where the plugin puts resources (images, css etc)
			'options_varname' 			=> 'rhcss_demo',
			'file_queue_options_name' 	=> 'rhcss_file_queue',
			'upload_limit_per_index'	=> 10,
			'plugin_url'				=> '',
			'resources_path'			=> '',
			'cb_init'					=> false,
			'detect_selector'			=> '',
			'trigger_var'				=> 'rhedit',
			'trigger_val'				=> '',
			'bootstrap_in_footer'		=> false,
			'bootstrap_disable'			=> false,
			'in_admin'					=> false,
			'in_login'					=> false,
			'alternate_accordion'		=> false,
			'in_footer'					=> false
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}	
		//---
		if($this->in_login){
			add_action('init',array(&$this,'wp'));
		}else if($this->in_admin){
			add_action('admin_init',array(&$this,'wp'));
		}else{
			add_action('wp',array(&$this,'wp'));
		}	

		//--- init ajax
		require_once 'class.righthere_css_ajax.php';
		new righthere_css_ajax(array(
			'id'				=> $this->id,
			'cb_get_option' 	=> $this->cb_get_option,
			'resources_path'	=> $this->resources_path,
			'options_varname'	=> $this->options_varname,
			'file_queue_options_name'=> $this->file_queue_options_name,
			'upload_limit_per_index' => $this->upload_limit_per_index
		));
		
		//--- TODO Review if this is the best place for this filter 
		add_filter('filter_rh_css',array(&$this,'filter_rh_css'),10,1);
		
		$this->in_footer = '1'==$this->get_option('js_in_footer','0',true) ? true : false ;
	}
	
	function module_righthere_css( $args ){
		$this->__construct( $args );
	}
	
	function handle_demo_skin($skin, $css){
		$saved_options = get_option($this->options_varname.'_saved');
		$saved_options = is_array($saved_options)?$saved_options:array();	
		if( count($saved_options)>0 ){
			foreach($saved_options as $option){
				if(property_exists($option,'bundle') && $skin==$option->bundle ){
					$css_output = '';
					$css_output_arr = $option->options['css_output_sections'];				
					if( is_array($css_output_arr) && count($css_output_arr)>0 ){
						if(isset($css_output_arr[$this->section]) && is_array($css_output_arr[$this->section]) && count($css_output_arr[$this->section])>0){
							foreach( $css_output_arr[$this->section] as $scope => $value ){
								$css_output .= "\n/* section: {$this->section} scope: $scope */\n".$value;
							}			
						}
					}
					$upload_dir = wp_upload_dir();							
					$dcurl = $upload_dir['baseurl'].'/'.$this->resources_path.'/';
					$css_output = str_replace('{dcurl}',$dcurl,$css_output);	
			
					$css_output = str_replace('{pluginurl}',$this->plugin_url,$css_output);		
					return $css."\n/* START {$this->id} custom css */\n".$css_output."\n/* END {$this->id} custom css */\n";						
				}
			}
		}	
		return $css;
	}
	
	function filter_rh_css($css){
		if(isset($_REQUEST['rh_no_custom']))return $css;
		if(isset($_REQUEST['rhcss_skin'])){return $this->handle_demo_skin($_REQUEST['rhcss_skin'], $css);}
		$css_output = $this->get_option('css_output', '', true);	
		$css_output_arr = $this->get_option('css_output_sections', array(), true);				
		if( is_array($css_output_arr) && count($css_output_arr)>0 ){
			if(isset($css_output_arr[$this->section]) && is_array($css_output_arr[$this->section]) && count($css_output_arr[$this->section])>0){
				foreach( $css_output_arr[$this->section] as $scope => $value ){
					$css_output .= "\n/* section: {$this->section} scope: $scope */\n".$value;
				}			
			}
		}
		$upload_dir = wp_upload_dir();							
		$dcurl = $upload_dir['baseurl'].'/'.$this->resources_path.'/';
		$css_output = str_replace('{dcurl}',$dcurl,$css_output);	

		$css_output = str_replace('{pluginurl}',$this->plugin_url,$css_output);
		
		if( !empty( $css_output ) ){
			$css_output = "\n/* START {$this->id} custom css */\n".$css_output."\n/* END {$this->id} custom css */\n";
		}
		
		return $css.$css_output;
	}
		
	function wp(){
		if( $this->detector() ){	
			//load the editor class.
			$this->is_admin_bar_showing = is_admin_bar_showing();			
			require_once 'class.righthere_css_editor.php';
			new righthere_css_editor(array(
				'id'				=> $this->id,
				'section'			=> $this->section,
				'with_scope'		=> $this->with_scope,
				'debug'				=> $this->debug,
				'url'				=> $this->url,
				'path'				=> $this->path,
				'options_callback'	=> array(&$this,'options'),
				'options_varname'	=> $this->options_varname,
				'file_queue_options_name'=> $this->file_queue_options_name,
				'cb_get_option'		=> $this->cb_get_option,
				'resources_path'	=> $this->resources_path,
				'cb_init'			=> $this->cb_init,
				'detect_selector'	=> $this->detect_selector,
				'bootstrap_in_footer'	=> $this->bootstrap_in_footer,
				'bootstrap_disable'		=> $this->bootstrap_disable,
				'in_admin'			=> $this->in_admin,
				'in_login'			=> $this->in_login,
				'alternate_accordion'=> $this->alternate_accordion,
				'in_footer'			=> $this->in_footer
			));
			
			//$this->handle_admin_bar();		
		}
	}
	/*
	function handle_admin_bar(){
		if(!$this->is_admin_bar_showing)return;
		global $wp_admin_bar;
		$wp_admin_bar->add_menu( array(
			//'parent' => 'new-content',
			'id' => 'rhc_css_editor',
			'title' => __('CSS Editor'),
			'href' => 'javascript:void(0);'
		));		
	}
	*/	
	//overloaded methods:
	function get_option($name,$default,$default_if_empty=true){
		if(is_callable($this->cb_get_option)){
			return call_user_func( $this->cb_get_option, $name, $default, $default_if_empty);
		}
		return '';	
	}
	
	function detector(){
		//to be overloaded with a method that tests if we should load our css editor.
		$this->trigger_val = ''==$this->trigger_val ? $this->id : $this->trigger_val;
		if(isset($_REQUEST[$this->trigger_var]) && $this->trigger_val==$_REQUEST[$this->trigger_var] && current_user_can( $this->capability ) ){
			return true;
		}
		return false;
	}
	
	function options($t=array()){
		return $t;
	}
	//end overloaded methods.
	
	function add_backgroud_options($options,$args){
		$defaults = array(
			'label'				=> __('Image','rhcss'),
			'label_bg'			=> __('Background color','rhcss'),
			'background_size'	=> true,
			'prefix' 			=> 'undefined',
			'selector'			=> 'undefined',
			'repeat_options'	=> array(
					''				=> '',
					'repeat'		=> __('repeat','rhcss'),
					'repeat-x'		=> __('repeat-x','rhcss'),
					'repeat-y'		=> __('repeat-y','rhcss'),
					'no-repeat'		=> __('no-repeat','rhcss'),
					'inherit'		=> __('inherit','rhcss')
				),
			'attachment_options'=> array(
					''				=> '',
					'scroll'		=> __('scroll','rhcss'),
					'fixed'			=> __('fixed','rhcss'),
					'inherit'		=> __('inherit','rhcss')
				),
			'position_options'	=> array(
					''					=> '',
					'left top'			=> __('left top','rhcss'),
					'left center'		=> __('left center','rhcss'),
					'left bottom'		=> __('left bottom','rhcss'),
					'right top'			=> __('right top','rhcss'),
					'right center'		=> __('right center','rhcss'),
					'right bottom'		=> __('right bottom','rhcss'),
					'center top'		=> __('center top','rhcss'),
					'center center'		=> __('center center','rhcss'),
					'center bottom'		=> __('center bottom','rhcss'),
					//'x% y%'				=> __('x% y%','rhcss'),
					//'xpos ypos'			=> __('xpos ypos','rhcss'),
					'inherit'			=> __('inherit','rhcss')
				),
			'size_options'		=> array(
					''					=> '',
					'length'			=> __('Length','rhcss'),
					'percentage'		=> __('Percentage','rhcss'),
					'cover'				=> __('Cover','rhcss'),
					'contain'			=> __('Contain','rhcss')
				),
			'derived_color'	=> false,
			'queue' => false
		);
		
		foreach($defaults as $varname => $default){
			$$varname = isset($args[$varname])?$args[$varname]:$default;
		}
		
		$options[]=(object)array(
				'id'				=> $prefix.'-image',
				'type'				=> 'css',
				'label'				=> $label,
				'input_type'		=> 'background_image',
				'selector'			=> $selector,
				'property'			=> 'background-image',
				'opacity'			=> true,
				'btn_clear'			=> true,
				'real_time'			=> true,
				'queue'				=> (false===$queue? $prefix.'-image' : $queue)
			);
			
		$tmp=(object)array(
				'id'				=> $prefix.'-color',
				'type'				=> 'css',
				'label'				=> $label_bg,
				'input_type'		=> 'color_or_something_else',
				'selector'			=> $selector,
				'property'			=> 'background-color',
				'other_options'		=> array(
					'transparent'	=> 'transparent'
				),
				'btn_clear'			=> true,
				'opacity'			=> true,
				'real_time'			=> true
			);
		if(false!==$derived_color){
			$tmp->derived = $derived_color;
		}
		$options[]=$tmp;
		$options[]=(object)array('input_type'		=> 'grid_start');
		$options[]=(object)array(
				'id'				=> $prefix.'-repeat',
				'type'				=> 'css',
				'label'				=> __('Repeat','rhcss'),
				'input_type'		=> 'select',
				'class'				=> 'input-wide',
				'holder_class'		=> 'span4',
				'selector'			=> $selector,
				'options'			=> $repeat_options,
				'property'			=> 'background-repeat',
				'real_time'			=> true
			);
		$options[]=(object)array(
				'id'				=> $prefix.'-attachment',
				'type'				=> 'css',
				'label'				=> __('Attachment','rhcss'),
				'input_type'		=> 'select',
				'class'				=> 'input-wide',
				'holder_class'		=> 'span4',				
				'selector'			=> $selector,
				'options'			=> $attachment_options,
				'property'			=> 'background-attachment',
				'real_time'			=> true
			);
		$options[]=			(object)array(
				'id'				=> $prefix.'-position',
				'type'				=> 'css',
				'label'				=> __('Position','rhcss'),
				'input_type'		=> 'select',
				'class'				=> 'input-wide',
				'holder_class'		=> 'span4',				
				'selector'			=> $selector,
				'options'			=> $position_options,
				'property'			=> 'background-position',
				'real_time'			=> true
			);
		$options[]=(object)array( 'input_type'		=> 'grid_end');
		
		if($background_size)
		$options[]=			(object)array(
				'id'				=> $prefix.'-size',
				'type'				=> 'css',
				'label'				=> __('Size','rhl'),
				'input_type'		=> 'background_size',
				'class'				=> '',
				'holder_class'		=> '',				
				'selector'			=> $selector,
				'other_options'		=> $size_options,
				'property'			=> 'background-size',
				'real_time'			=> true
			);	
	
		return $options;
	}
	
	function add_border_radius_options($options,$args){
		$defaults = array(
			'prefix' 			=> 'undefined',
			'selector'			=> 'undefined',
			'label'			=> array(
				'tl'	=> __('Top left radius','rhcss'),
				'tr'	=> __('Top right radius','rhcss'),
				'bl'	=> __('Bottom left radius','rhcss'),
				'br'	=> __('Bottom right radius','rhcss')
			),
			'min' => 0,
			'max' => 100,
			'step'=> 1
		);	
		foreach($defaults as $varname => $default){
			$$varname = isset($args[$varname])?$args[$varname]:$default;
		}
//------
		$options[]=(object)array('input_type'=>'grid_start');	
		$options[]=	(object)array(
				'id'				=> $prefix.'_tl_radius',
				'type'				=> 'css',
				'label'				=> $label['tl'],
				'input_type'		=> 'number',
				'unit'				=> 'px',
				'class'				=> '',
				'holder_class'		=> 'span6',
				'class'				=> 'input-mini',
				'min'				=> $min,
				'max'				=> $max,
				'step'				=> $step,
				'selector'			=> $selector,
				'property'			=> 'border-top-left-radius',
				'real_time'			=> true
			);	
		$options[]=	(object)array(
				'id'				=> $prefix.'_tr_radius',
				'type'				=> 'css',
				'label'				=> $label['tr'],
				'input_type'		=> 'number',
				'unit'				=> 'px',
				'class'				=> '',
				'holder_class'		=> 'span6',
				'class'				=> 'input-mini',
				'min'				=> $min,
				'max'				=> $max,
				'step'				=> $step,
				'selector'			=> $selector,
				'property'			=> 'border-top-right-radius',
				'real_time'			=> true
			);				
		$options[]=(object)array('input_type'=>'grid_end');		
		
		$options[]=(object)array('input_type'=>'grid_start');	
		$options[]=	(object)array(
				'id'				=> $prefix.'_bl_radius',
				'type'				=> 'css',
				'label'				=> $label['bl'],
				'input_type'		=> 'number',
				//'input_type'		=> 'element_size',
				'unit'				=> 'px',
				'class'				=> '',
				'holder_class'		=> 'span6',
				'class'				=> 'input-mini',
				'min'				=> $min,
				'max'				=> $max,
				'step'				=> $step,
				'selector'			=> $selector,
				'property'			=> 'border-bottom-left-radius',
				'real_time'			=> true
			);	
		$options[]=	(object)array(
				'id'				=> $prefix.'_br_radius',
				'type'				=> 'css',
				'label'				=> $label['br'],
				'input_type'		=> 'number',
				//'input_type'		=> 'element_size',
				'unit'				=> 'px',
				'class'				=> '',
				'holder_class'		=> 'span6',
				'class'				=> 'input-mini',
				'min'				=> $min,
				'max'				=> $max,
				'step'				=> $step,
				'selector'			=> $selector,
				'property'			=> 'border-bottom-right-radius',
				'real_time'			=> true
			);				
		$options[]=(object)array('input_type'=>'grid_end');		
			
		return $options;		
	}
	
	function add_margin_options($options,$args){
		if(!isset($args['label'])){
			$args['label'] = array(
				'top'		=> __('Top margin','rhcss'),
				'right'		=> __('Right margin','rhcss'),
				'bottom'	=> __('Bottom margin','rhcss'),
				'left'		=> __('Left margin','rhcss')
			);
		}
		if(!isset($args['property'])){
			$args['property'] = array(
				'top'	=> 'margin-top',
				'right'	=> 'margin-right',
				'bottom'=> 'margin-bottom',
				'left'	=> 'margin-left'
			);
		}
		return $this->add_padding_options($options,$args);
	}
	
	function add_padding_options($options,$args){
		$defaults = array(
			'prefix' 			=> 'undefined',
			'selector'			=> 'undefined',
			'label'			=> array(
				'top'		=> __('Top padding','rhcss'),
				'right'		=> __('Right padding','rhcss'),
				'bottom'	=> __('Bottom padding','rhcss'),
				'left'		=> __('Left padding','rhcss')
			),
			'unit'=> array(
				'top'	=> 'px',
				'right'	=> 'px',
				'bottom'=> 'px',
				'left'	=> 'px'
			),
			'property'		=> array(
				'top'	=> 'padding-top',
				'right'	=> 'padding-right',
				'bottom'=> 'padding-bottom',
				'left'	=> 'padding-left'
			),
			'min' => 0,
			'max' => 100,
			'step'=> 1,
			'top'=>true,
			'bottom'=>true,
			'left'=>true,
			'right'=>true
		);
		
		foreach($defaults as $varname => $default){
			$$varname = isset($args[$varname])?$args[$varname]:$default;
		}		
//-----------		
		if($top){
			$options[]=(object)array('input_type'=>'grid_start');
			$options[]=	(object)array(
					'id'				=> $prefix.'_padding_top',
					'type'				=> 'css',
					'label'				=> $label['top'],
					'input_type'		=> 'number',
					'unit'				=> $unit['top'],
					'class'				=> '',
					'holder_class'		=> 'span6 offset3',
					'class'				=> 'input-mini',
					'min'				=> $min,
					'max'				=> $max,
					'step'				=> $step,
					'selector'			=> $selector,
					'property'			=> $property['top'],
					'real_time'			=> true
				);			
			$options[]=(object)array('input_type'=>'grid_end');			
		}

		if($left&&$right){
			$options[]=(object)array('input_type'=>'grid_start');	
			if($left){
				$options[]=	(object)array(
						'id'				=> $prefix.'_padding_left',
						'type'				=> 'css',
						'label'				=> $label['left'],
						'input_type'		=> 'number',
						//'input_type'		=> 'element_size',
						'unit'				=> $unit['left'],
						'class'				=> '',
						'holder_class'		=> 'span6',
						'class'				=> 'input-mini',
						'min'				=> $min,
						'max'				=> $max,
						'step'				=> $step,
						'selector'			=> $selector,
						'property'			=> $property['left'],
						'real_time'			=> true
					);				
			}

			if($right){
				$options[]=	(object)array(
						'id'				=> $prefix.'_padding_right',
						'type'				=> 'css',
						'label'				=> $label['right'],
						'input_type'		=> 'number',
						//'input_type'		=> 'element_size',
						'unit'				=> $unit['right'],
						'class'				=> '',
						'holder_class'		=> 'span6',
						'class'				=> 'input-mini',
						'min'				=> $min,
						'max'				=> $max,
						'step'				=> $step,
						'selector'			=> $selector,
						'property'			=> $property['right'],
						'real_time'			=> true
					);				
			}	

			$options[]=(object)array('input_type'=>'grid_end');				
		}

		if($bottom){
			$options[]=(object)array('input_type'=>'grid_start');
			$options[]=	(object)array(
					'id'				=> $prefix.'_padding_bottom',
					'type'				=> 'css',
					'label'				=> $label['bottom'],
					'input_type'		=> 'number',
					'unit'				=> $unit['bottom'],
					'class'				=> '',
					'holder_class'		=> 'span6 offset3',
					'class'				=> 'input-mini',
					'min'				=> $min,
					'max'				=> $max,
					'step'				=> $step,
					'selector'			=> $selector,
					'property'			=> $property['bottom'],
					'real_time'			=> true
				);			
			$options[]=(object)array('input_type'=>'grid_end');				
		}
			
//----------------
		return $options;
	}
	
	function add_border_options($options,$args){	
		$defaults = array(
			'prefix' 			=> 'undefined',
			'selector'			=> 'undefined',
			'label'			=> array(
				'color'	=> __('Border color','rhc'),
				'style' => __('Border style','rhc'),
				'size'	=> __('Width','rhc'),
				'collapse'	=> __('Collapse', 'rhc'),
				'spacing'	=> __('Spacing', 'rhc')
			),
			'property'	=>array(
				'color'		=> 'border-color',
				'style' 	=> 'border-style',
				'size'		=> 'border-width',
				'collapse'	=> 'border-collapse',
				'spacing'	=> 'border-spacing'
			),
			'style_options' => array(
				''		=> '',
				'none'	=> 'none',
				'solid'	=> 'solid',
				'double'=> 'double',
				'dotted'=> 'dotted',
				'groove'=> 'groove',
				'inset'=> 'inset',
				'outset'=> 'outset',
				'ridge'=> 'ridge'
			),
			'collapse_options' => array(
				''			=> '',
				'inherit'	=> 'inherit',
				'separate'	=> 'separate',
				'collapse'	=> 'collapse',
				'initial'	=> 'initial'
			),
			'spacing_options' => array(
				''			=> '',
				'initial'	=> 'initial',
				'inherit'	=> 'inherit',
				'length'	=> 'length'
			),
			'show_collapse'	=> false,
			'show_spacing'	=> false
		);

		foreach($defaults as $varname => $default){
			$$varname = isset($args[$varname])?$args[$varname]:$default;
		}
		
		$options[] = (object)array(
				'id'				=> $prefix.'_color',
				'type'				=> 'css',
				'label'				=> $label['color'],
				'input_type'		=> 'color_or_something_else',
				'holder_class'		=> '',
				'opacity'			=> true,
				'btn_clear'			=> true,
				'selector'			=> $selector,
				'property'			=> $property['color'],
				'other_options'		=> array(
					'transparent'	=> 'transparent'
				),				
				'real_time'			=> true
			);
		$options[] = (object)array('input_type'=>'grid_start');
		$options[] = (object)array(
				'id'				=> $prefix.'_style',
				'type'				=> 'css',
				'label'				=> $label['style'],
				'input_type'		=> 'select',
				'class'				=> 'input-small',
				'holder_class'		=> 'span6 ',				
				'selector'			=> $selector,
				'options'			=> $style_options,
				'property'			=> $property['style'],
				'real_time'			=> true
			);
		$options[] = (object)array(
				'id'				=> $prefix.'_size',
				'type'				=> 'css',
				'label'				=> $label['size'],
				'input_type'		=> 'number',
				'unit'				=> 'px',
				'class'				=> '',
				'holder_class'		=> 'span6',
				'class'				=> 'input-mini',
				'min'				=> '0',
				'max'				=> '100',
				'step'				=> '1',
				'selector'			=> $selector,
				'property'			=> $property['size'],
				'real_time'			=> true
			);
		$options[] = (object)array('input_type'=>'grid_end');

		if( $show_collapse ){
			$options[] = (object)array(
					'id'				=> $prefix.'_collapse',
					'type'				=> 'css',
					'label'				=> $label['collapse'],
					'input_type'		=> 'select',
					'class'				=> '',
					'holder_class'		=> '',				
					'selector'			=> $selector,
					'options'			=> $collapse_options,
					'property'			=> $property['collapse'],
					'real_time'			=> true
				);
		}	
		
		if( $show_spacing ){

				
				
			$options[]=			(object)array(
					'id'				=> $prefix.'_spacing',
					'type'				=> 'css',
					'label'				=> $label['spacing'],
					'input_type'		=> 'background_size',
					'auto'				=> false,
					'x_label'			=> __('Horizontal','rhl'),
					'y_label'			=> __('Vertical','rhl'),
					'class'				=> '',
					'holder_class'		=> '',				
					'selector'			=> $selector,
					'other_options'		=> $spacing_options,
					'property'			=> $property['spacing'],
					'real_time'			=> true
				);					
				
		}
				
		return $options;
	}
	
	function add_font_options($options,$args){
		$defaults = array(
			'prefix' 			=> 'undefined',
			'selector'			=> 'undefined',
			'labels'			=> array(
				'family'	=> __('Font family','rhcss'),
				'size'		=> __('Size','rhcss'),
				'color'		=> __('Color','rhcss'),
				'weight'	=> __('Weight','rhcss')
			),
			'derived_color' => false,
			'enable_size'	=> true
		);
		
		foreach($defaults as $varname => $default){
			$$varname = isset($args[$varname])?$args[$varname]:$default;
		}	
		
		$options[] =(object)array(
				'id'				=> $prefix.'-font-family',
				'type'				=> 'css',
				'label'				=> $labels->family,
				'input_type'		=> 'font',
				'bold'				=> false,
				'class'				=> '',
				'holder_class'		=> '',
				//'class'				=> 'input-mini pop_rangeinput',
				'selector'			=> $selector,
				'property'			=> 'font-family',
				'real_time'			=> true
			);

		$options[] =(object)array(
				'id'				=> $prefix.'-font-weight',
				'type'				=> 'css',
				'label'				=> @$labels->weight,
				'input_type'		=> 'select',
				'options'	=> array(
					'initial'	=> __('Initial','rhcss'),
					'normal'	=> __('Normal','rhcss'),
					'bold'		=> __('Bold','rhcss'),
					'100'		=> '100',
					'200'		=> '200',
					'300'		=> '300',
					'400'		=> '400',
					'500'		=> '500',
					'600'		=> '600',
					'700'		=> '700',
					'800'		=> '800',
					'900'		=> '900'
				),
				'class'				=> '',
				'holder_class'		=> '',
				//'class'				=> 'input-mini pop_rangeinput',
				'selector'			=> $selector,
				'property'			=> 'font-weight',
				'real_time'			=> true
			);
		
		$options[] =(object)array('input_type'		=> 'grid_start');
			
		$tmp =(object)array(
				'id'				=>  $prefix.'-font-color',
				'type'				=> 'css',
				'label'				=> $labels->color,
				'input_type'		=> 'colorpicker',
				'holder_class'		=> 'span8 input-no-label',
				'opacity'			=> true,
				'btn_clear'			=> true,
				'selector'			=> $selector,
				'property'			=> 'color',
				'real_time'			=> true
			);
		if(false!==$derived_color){
			$tmp->derived = $derived_color;
		}
		$options[] = $tmp;	
		
		if($enable_size):
		$options[] =(object)array(
				'id'				=> $prefix.'-font-size',
				'type'				=> 'css',
				'label'				=> $labels->size,
				'input_type'		=> 'number',
				//'input_type'		=> 'element_size',
				'unit'				=> 'px',
				'class'				=> 'input-font-size',
				'holder_class'		=> 'span4 input-no-label',
				//'class'				=> 'input-mini pop_rangeinput',
				'min'				=> '6',
				'max'				=> '144',
				'step'				=> '1',
				'selector'			=> $selector,
				'property'			=> 'font-size',
				'real_time'			=> true
			);
		endif;
			
		$options[] =(object)array( 'input_type'		=> 'grid_end');
				
		return $options;
	}
}
?>