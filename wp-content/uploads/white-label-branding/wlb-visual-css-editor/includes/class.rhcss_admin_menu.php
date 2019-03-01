<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class rhcss_admin_menu extends module_righthere_css{
	function __construct($args=array()){
		//$args['cb_init']=array(&$this,'cb_init');
		return parent::__construct($args);
	}

	function cb_init(){
		//called on the head when editor is active.
		
	}
	
	function options($t=array()){
		$i = count($t);
		//-- Menu Container --------------------------------------------------------------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-default'; 
		$t[$i]->label 		= __('Default','rhace');
		$t[$i]->options = array();	

		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_container_font_family',
			'selector'	=> implode(',',array(
				'body #adminmenu'
			)),
			'labels'	=> (object)array(
				'family'	=> __('Default font family','rhace'),
				'size'		=> __('Size','rhace'),
				'color'		=> __('Color','rhace')
								
			)
		));		
		
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_menu_bak',
				'type'				=> 'css',
				'label'				=> __('Menu container color','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(',',array(
					'body.wp-admin.wp-core-ui #adminmenuback'
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
								'sel'	=> 'body.wp-admin.wp-core-ui #adminmenuback',
								'arg'	=> array(
									(object)array(
										'name' => 'z-index',
										'tpl'	=>'0;'
									)
								)
							)					
				)		
			);				
		//-- Menu --------------------------------------------------------------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-menu'; 
		$t[$i]->label 		= __('Menu','rhace');
		$t[$i]->options = array();	
		
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_menu_bg',
				'type'				=> 'css',
				'label'				=> __('Background Color','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(',',array(
					'body #adminmenu',
					'body #adminmenuwrap',
					'body #adminmenuback'
				)),
				'property'			=> 'background-color',
				'other_options'		=> array(
					'transparent'	=> 'transparent'
				),
				'btn_clear'			=> true,
				'opacity'			=> true,
				'real_time'			=> true
			);	
			
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_menu_hover_bg',
				'type'				=> 'css',
				'label'				=> __('Background Color(Hover)','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(',',array(
					'body #adminmenu li.menu-top:hover',
					'body #adminmenu li.opensub>a.menu-top',
					'body #adminmenu li>a.menu-top:focus',
					
					'body #adminmenu li.wp-not-current-submenu .wp-menu-arrow',
					'body #adminmenu li.wp-not-current-submenu .wp-menu-arrow div',
					'body #adminmenu li.current a.menu-top',
					'body #adminmenu a:hover'//midnight admin skin
				)),
				'property'			=> 'background-color',
				'other_options'		=> array(
					'transparent'	=> 'transparent'
				),
				'btn_clear'			=> true,
				'opacity'			=> true,
				'real_time'			=> true
			);	
			
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_menu_font',
			'selector'	=> implode(',',array(
				'body #adminmenu a',
				'body #adminmenu a.menu-top',
				'body #adminmenu .wp-submenu-head'
			)),
			'labels'	=> (object)array(
				'family'	=> __('Font','rhace'),
				'size'		=> __('Size','rhace'),
				'color'		=> __('Color','rhace')
								
			)
		));		
			
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_menu_hover_font',
			'selector'	=> implode(',',array(
				'body #adminmenu li.menu-top:hover',
				'body #adminmenu li.opensub>a.menu-top',
				'body #adminmenu li>a.menu-top:focus',
				'body #adminmenu a:hover'
			)),
			'labels'	=> (object)array(
				'family'	=> __('Font(Hover)','rhace'),
				'size'		=> __('Size','rhace'),
				'color'		=> __('Color','rhace')
								
			)
		));							
		//-- Menu Hover --------------------------------------------------------------------
/*
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-menu-hover'; 
		$t[$i]->label 		= __('Menu (Hover)','rhace');
		$t[$i]->options = array();	
		
*/

		//--- Current Menu -------------------------------------------------------------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-current-menu'; 
		$t[$i]->label 		= __('Open menu header','rhace');
		$t[$i]->options = array();
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_current_menu_font',
			'selector'	=> 'body #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu',
			'labels'	=> (object)array(
				'family'	=> __('Font','rhace'),
				'size'		=> __('Size','rhace'),
				'color'		=> __('Color','rhace')
								
			)
		));
		
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_current_menu_font_shadow',
				'type'				=> 'css',
				'label'				=> __('Font shadow','rhace'),
				'input_type'		=> 'textshadow',
				'opacity'			=> true,
				'selector'			=> 'body #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu',
				'property'			=> 'text-shadow',
				'real_time'			=> true,
				'btn_clear'			=> true
			);	
			
		$t[$i]->options[] = (object)array(
				'id'				=> 'ace_current_menu_bg',
				'type'				=> 'css',
				'label'				=> __('Background','rhace'),
				'input_type'		=> 'background_image',
				'holder_class'		=> '',
				'opacity'			=> true,
				'btn_clear'			=> true,
				'selector'			=> implode(',',array(
					"body #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu",
					"body #adminmenu .wp-menu-arrow",//a vertical spacer next to the arrow
					"body #adminmenu .wp-menu-arrow div"//menu arrow
				)),
				'property'			=> 'background-image',				
				'real_time'			=> true
			);	
		
		$t[$i]->options[] =(object)array(
			'input_type'=>'raw_html',
			'html' => implode('',array(
					'<div class="ace_menu_arrow_color"></div>'		
				)
			)
		);
		
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_menu_arrow',
				'type'				=> 'css',
				'label'				=> __('Arrow Color','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> '.ace_menu_arrow_color',
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
								'sel'	=> 'body.wp-admin.wp-core-ui ul#adminmenu a.wp-has-current-submenu:after, body.wp-admin.wp-core-ui ul#adminmenu>li.current>a.current:after',
								'arg'	=> array(
									(object)array(
										'name' => 'border-right-color',
										'tpl'	=>'__value__;'
									)
								)
							)							
						)					
			);				
		//---- Submenu body ------------------------------------------------------------------
/*
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-submenu'; 
		$t[$i]->label 		= __('Submenu body','rhace');
		$t[$i]->options = array();	
*/
	
		//---- Submenu------------------------------------------------------------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-floating-submenu'; 
		$t[$i]->label 		= __('Submenu','rhace');
		$t[$i]->options = array();	
		
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_submenu_bg',
				'type'				=> 'css',
				'label'				=> __('Background Color','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(',',array(
					"body #adminmenu .wp-submenu",
					"body #adminmenu .wp-has-current-submenu .wp-submenu"
				)),
				'property'			=> 'background-color',
				'other_options'		=> array(
					'transparent'	=> 'transparent'
				),
				'btn_clear'			=> true,
				'opacity'			=> true,
				'real_time'			=> true,
				'derived'			=> array(
							/*
							array(
								'type'	=> 'same',
								'val'	=> '5',
								'sel'	=> ".fbd-ul li.fbd-tabs.fbd-active-tab",
								'arg'	=> array(
									(object)array(
										'name' => 'visibility',
										'tpl'	=>'visible;'
									)
								)
							),
							*/
							array(
								'type'	=> 'same',
								'val'	=> '5',
								'sel'	=> "#adminmenuwrap #adminmenu li.wp-has-submenu.wp-not-current-submenu.opensub:hover:after",
								'arg'	=> array(
									(object)array(
										'name' => 'border-right-color',
										'tpl'	=>'__value__;'
									)
								)
							)
						)	
			);	

	
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_submenu_hover_bg',
				'type'				=> 'css',
				'label'				=> __('Hover Background (Floating)','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(',',array(
					"body #adminmenu .wp-submenu a:hover",
					"body #adminmenu .wp-submenu a:focus"
				)),
				'property'			=> 'background-color',
				'other_options'		=> array(
					'transparent'	=> 'transparent'
				),
				'btn_clear'			=> true,
				'opacity'			=> true,
				'real_time'			=> true
			);	

		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_submenu_hover_bg2',
				'type'				=> 'css',
				'label'				=> __('Hover Background ','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(',',array(
					"body.wp-admin #adminmenu .wp-has-current-submenu .wp-submenu:hover"
				)),
				'property'			=> 'background-color',
				'other_options'		=> array(
					'transparent'	=> 'transparent'
				),
				'btn_clear'			=> true,
				'opacity'			=> true,
				'real_time'			=> true
			);	
	
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_submenu_shadow',
				'type'				=> 'css',
				'label'				=> __('Submenu shadow','rhace'),
				'input_type'		=> 'textshadow',
				'opacity'			=> true,
				'selector'			=> 'body #adminmenu .wp-submenu',
				'property'			=> 'box-shadow',
				'real_time'			=> true,
				'btn_clear'			=> true
			);	

		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_submenu_font_current',
			'selector'	=> implode(',',array(
				'#adminmenuwrap #adminmenu .wp-submenu li.current a',
				'#adminmenuwrap #adminmenu .wp-submenu li.current a:hover'
			)),
			'labels'	=> (object)array(
				'family'	=> __('Font(Loaded page)','rhace'),
				'size'		=> __('Size','rhace'),
				'color'		=> __('Color','rhace')
								
			)
		));		
		
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_submenu_font',
			'selector'	=> implode(',',array(
				'#adminmenuwrap #adminmenu .wp-submenu li a',
				'#adminmenuwrap #adminmenu .wp-has-current-submenu .wp-submenu a'		
			)),
			'labels'	=> (object)array(
				'family'	=> __('Font','rhace'),
				'size'		=> __('Size','rhace'),
				'color'		=> __('Color','rhace')
								
			)
		));	
		
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_submenu_font_hover',
			'selector'	=> implode(',',array(
				'#adminmenuwrap #adminmenu .wp-submenu li a:hover',
				'#adminmenuwrap #adminmenu .wp-has-current-submenu .wp-submenu a:hover'
			)),
			'labels'	=> (object)array(
				'family'	=> __('Font(Hover)','rhace'),
				'size'		=> __('Size','rhace'),
				'color'		=> __('Color','rhace')
								
			)
		));	

		//--- Icons -------------------------------------------------------------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-menu-icons'; 
		$t[$i]->label 		= __('Icons','rhace');
		$t[$i]->options = array();	
		//this is needed because the js cannot read the :before selector.
		$t[$i]->options[] =(object)array(
				'input_type'		=> 'raw_html',
				'html'				=> '<div class="ace_menu_icon_color" style="display:none;"></div>'
			);

		$t[$i]->options[] =(object)array(
				'input_type'		=> 'raw_html',
				'html'				=> '<div class="ace_menu_icon_color_hover" style="display:none;"></div>'
			);
		
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_menu_icon_color',
				'type'				=> 'css',
				'label'				=> __('Color','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(',',array(
					".ace_menu_icon_color, body #adminmenuwrap #adminmenu div.wp-menu-image:before"
				)),
				'property'			=> 'color',
				'btn_clear'			=> true,
				'opacity'			=> true,
				'real_time'			=> true
			);

		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_menu_icon_color_hover',
				'type'				=> 'css',
				'label'				=> __('Color (Hover)','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(',',array(
					".ace_menu_icon_color_hover, body #adminmenuwrap #adminmenu li:hover div.wp-menu-image:before"
				)),
				'property'			=> 'color',
				'btn_clear'			=> true,
				'opacity'			=> true,
				'real_time'			=> true
			);	
	
				
		//--- Separator -------------------------------------------------------------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-menu-separator'; 
		$t[$i]->label 		= __('Separator','rhace');
		$t[$i]->options = array();	
		
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_menu_separator_bg',
				'type'				=> 'css',
				'label'				=> __('Background Color','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(',',array(
					"body #adminmenu li.wp-menu-separator"
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
							'type'	=> 'color_darken',
							'val'	=> '1',
							'sel'	=> "body #adminmenu li.wp-menu-separator",
							'arg'	=> array(
								(object)array(
									'name' => 'border-color',
									'tpl'	=>'__value__;'
								)
							)
						),	
						array(
							'type'	=> 'color_darken',
							'val'	=> '-1',
							'sel'	=> "body #adminmenu div.separator",
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
				'id'				=> 'ace_menu_separator_height',
				'type'				=> 'css',
				'label'				=> __('Height','rhace'),
				'input_type'		=> 'number',
				'unit'				=> 'px',
				'class'				=> 'input-font-size',
				'min'				=> '0',
				'max'				=> '10',
				'step'				=> '1',
				'selector'			=> 'body #adminmenu li.wp-menu-separator, body #adminmenu div.separator',
				'property'			=> 'height',
				'real_time'			=> true
			);	

		$t[$i]->options = $this->add_border_options($t[$i]->options,array(
			'prefix'	=> 'ace_menu_separator_border_top',
			'selector'			=> implode(',',array(
				'body #adminmenu li.wp-menu-separator'
			)),
			'label'			=> array(
				'color'	=> __('Top Border color','rhc'),
				'style' => __('Border style','rhc'),
				'size'	=> __('Width','rhc')
			),
			'property'	=>array(
				'color'	=> 'border-top-color',
				'style' => 'border-top-style',
				'size'	=> 'border-width'
			)
		));	

		$t[$i]->options = $this->add_border_options($t[$i]->options,array(
			'prefix'	=> 'ace_menu_separator_border_bottom',
			'selector'			=> implode(',',array(
				'body #adminmenu li.wp-menu-separator'
			)),
			'label'			=> array(
				'color'	=> __('Bottom Border color','rhc'),
				'style' => __('Border style','rhc'),
				'size'	=> __('Width','rhc')
			),
			'property'	=>array(
				'color'	=> 'border-bottom-color',
				'style' => 'border-bottom-style',
				'size'	=> 'border-bottom-width'
			)
		));					
		//--- Update circle -------------------------------------------------------------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-menu-update-circle'; 
		$t[$i]->label 		= __('Update circle','rhace');
		$t[$i]->options = array();	

		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_update_circle_color',
				'type'				=> 'css',
				'label'				=> __('Circle color','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(',',array(
					"#adminmenuwrap #adminmenu .awaiting-mod",
					"#adminmenuwrap #adminmenu .update-plugins"
					//,
					//"#sidemenu a .update-plugins",
					//"#rightnow .reallynow"
				)),
				'property'			=> 'background-color',
				'btn_clear'			=> true,
				'opacity'			=> true,
				'real_time'			=> true
			);	

		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_update_circle_font_color',
				'type'				=> 'css',
				'label'				=> __('Font color','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(',',array(
					"#adminmenuwrap #adminmenu .awaiting-mod",
					"#adminmenuwrap #adminmenu .update-plugins"
					//,
					//"#sidemenu a .update-plugins",
					//"#rightnow .reallynow"
				)),
				'property'			=> 'color',
				'btn_clear'			=> true,
				'opacity'			=> true,
				'real_time'			=> true
			);	
									
		//---- Collapse link ------------------------------------------------------------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-collapse'; 
		$t[$i]->label 		= __('Collapse','rhace');
		$t[$i]->options = array();	
		$t[$i]->options[] =(object)array(
				'input_type'		=> 'raw_html',
				'html'				=> implode('', array(
						'<div class="ace_collapse_icon" style="display:none;"></div>',
						'<div class="ace_collapse_icon_hover" style="display:none;"></div>'						
					)
				) 
			);
			
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_collapse_font',
			'selector'	=> implode(',',array(
				'body #adminmenu #collapse-menu'
			)),
			'labels'	=> (object)array(
				'family'	=> __('Font','rhace'),
				'size'		=> __('Size','rhace'),
				'color'		=> __('Color','rhace')
								
			)
		));		
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_collapse_font_hover',
			'selector'	=> implode(',',array(
				'body #adminmenu #collapse-menu:hover'
			)),
			'labels'	=> (object)array(
				'family'	=> __('Font(hover)','rhace'),
				'size'		=> __('Size','rhace'),
				'color'		=> __('Color','rhace')
								
			)
		));			
					
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_collapse_icon',
				'type'				=> 'css',
				'label'				=> __('Icon color','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(',',array(
					".ace_collapse_icon, body #collapse-button div:after"
				)),
				'property'			=> 'color',
				'btn_clear'			=> true,
				'opacity'			=> true,
				'real_time'			=> true
			);

		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_collapse_icon_hover',
				'type'				=> 'css',
				'label'				=> __('Icon color (Hover)','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(',',array(
					".ace_collapse_icon_hover, body #collapse-menu:hover #collapse-button div:after"
				)),
				'property'			=> 'color',
				'btn_clear'			=> true,
				'opacity'			=> true,
				'real_time'			=> true
			);				
								
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