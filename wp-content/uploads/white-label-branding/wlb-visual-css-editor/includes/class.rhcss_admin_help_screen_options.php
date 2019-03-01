<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class rhcss_admin_help_screen_options extends module_righthere_css{
	function __construct($args=array()){
		$args['cb_init']=array(&$this,'cb_init');
		return parent::__construct($args);
	}

	function cb_init(){
		//called on the head when editor is active.
		
	}
	
	function options($t=array()){
		$i = count($t);
	
									
				
		//-- Help TAb ----------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-main-help'; 
		$t[$i]->label 		= __('Help tab / Screen options tab','rhace');
		$t[$i]->options = array();				

		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_main_help_bg',
				'type'				=> 'css',
				'label'				=> __('Content color','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(', ',array(
					'body #contextual-help-link-wrap',
					'body #screen-options-link-wrap',
					'body #screen-meta'
				)),
				'property'			=> 'background-color',
				'other_options'		=> array(
					'transparent'	=> 'transparent'
				),
				'btn_clear'			=> true,
				'opacity'			=> true,
				'real_time'			=> true,
				'derived'			=> array(
							array(
								'type'	=> 'same',
								'val'	=> '5',
								'sel'	=> 'body.wp-admin .contextual-help-tabs .active',
								'arg'	=> array(
									(object)array(
										'name' => 'border-color',
										'tpl'	=>'__value__;'
									)
								)
							)				
				)	
			);	

		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_main_help_itab_color',
				'type'				=> 'css',
				'label'				=> __('Active tab left border color','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(', ',array(
					'body.wp-admin.wp-core-ui .contextual-help-tabs li.active'
				)),
				'property'			=> 'border-color',
				'other_options'		=> array(
					'transparent'	=> 'transparent'
				),
				'btn_clear'			=> true,
				'opacity'			=> true,
				'real_time'			=> true				
			);	
			
		$t[$i]->options = $this->add_border_options($t[$i]->options,array(
			'prefix'	=> 'ace_main_help_border',
			'selector'	=> "body #contextual-help-link-wrap, body #screen-options-link-wrap, body #screen-meta"
		));	
										

			
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_main_help_content_bg',
				'type'				=> 'css',
				'label'				=> __('Help box content color','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(', ',array(
					'body #contextual-help-back',
					'body .contextual-help-tabs .active a',
					'body .contextual-help-tabs .active a:hover'
				)),
				'property'			=> 'background-color',
				'other_options'		=> array(
					'transparent'	=> 'transparent'
				),
				'btn_clear'			=> true,
				'opacity'			=> true,
				'real_time'			=> true				
			);	
						
		//-- Help TAb fonts ----------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-main-help-tab-fonts'; 
		$t[$i]->label 		= __('Tab fonts','rhace');
		$t[$i]->options = array();	
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_main_help_font',
			'selector'	=> 'body.wp-admin #contextual-help-link, body.wp-admin #show-settings-link, body.wp-admin #screen-meta-links a.show-settings',
			'labels'	=> (object)array(
				'family'	=> __('Font','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));	
			
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_main_help_font_h',
			'selector'	=> 'body #contextual-help-link:hover, body #show-settings-link:hover, body.wp-admin #screen-meta-links a.show-settings:hover',
			'labels'	=> (object)array(
				'family'	=> __('Font(hover)','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));		
		
		//-- Help content fonts ----------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-admin-help-content-fonts'; 
		$t[$i]->label 		= __('Content fonts','rhace');
		$t[$i]->options = array();			

		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_help_content_fonts',
			'selector'	=> 'body.wp-admin #screen-meta',
			'labels'	=> (object)array(
				'family'	=> __('Default font','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));		
		
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_help_a_font',
			'selector'	=> 'body.wp-admin #screen-meta a',
			'labels'	=> (object)array(
				'family'	=> __('Links font','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));
		
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_help_a_font_h',
			'selector'	=> 'body.wp-admin #screen-meta a:hover',
			'labels'	=> (object)array(
				'family'	=> __('Links font (hover)','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));		
		
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_help_navigation_fonts',
			'selector'	=> 'body.wp-admin.wp-core-ui .contextual-help-tabs a',
			'labels'	=> (object)array(
				'family'	=> __('Help Navigation font','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));	
		
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_help_navigation_fonts_h',
			'selector'	=> 'body.wp-admin.wp-core-ui #screen-meta .contextual-help-tabs a:hover, body.wp-admin.wp-core-ui .contextual-help-tabs a:active, body.wp-admin.wp-core-ui .contextual-help-tabs a:focus',
			'labels'	=> (object)array(
				'family'	=> __('Help Navigation font(hover,active,focus)','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));		
		
		
		//-- Saved and DC  -----------------------		
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'rh-saved-list'; 
		$t[$i]->label 		= __('Templates','rhc');
		$t[$i]->options = array(
			(object)array(
				'id'				=> 'rh_saved_settings',
				'input_type'		=> 'backup_list'
			)			
		);			
//endif;		
//----------------------------------------------------------------------
		return $t;
	}
}
?>