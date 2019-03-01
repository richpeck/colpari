<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class rhcss_admin_bar extends module_righthere_css{
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
		$t[$i]->id 			= 'ace-bar'; 
		$t[$i]->label 		= __('Toolbar','rhace');
		$t[$i]->options = array();	

		$t[$i]->options[] =(object)array(
				'input_type'		=> 'raw_html',
				'html'				=> '<div class="ace_bar_bg_hover"></div>'		
			);			
		
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_bar_bg',
				'type'				=> 'css',
				'label'				=> __('Background color','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(', ',array(
					'body #wpadminbar'
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
				'id'				=> 'ace_bar_bg_hover',
				'type'				=> 'css',
				'label'				=> __('Background color (hover)','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(', ',array(
					'body #wpadminbar .ab-top-menu>li>.ab-item:focus',
					'body #wpadminbar .ab-top-menu>li>.ab-item:hover',
					'body #wpadminbar .ab-top-menu>li.menupop.hover>.ab-item',
					'.ace_bar_bg_hover'
				)),
				'property'			=> 'background-color',
				'other_options'		=> array(
					'transparent'	=> 'transparent'
				),
				'btn_clear'			=> true,
				'opacity'			=> true,
				'real_time'			=> true				
			);	

		//-- Submenu --------------------------------------------------------------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-bar-submenu'; 
		$t[$i]->label 		= __('Submenu','rhace');
		$t[$i]->options = array();				
			
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_bar_submenu_bg',
				'type'				=> 'css',
				'label'				=> __('Submenu color','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(', ',array(
					'body #wpadminbar .menupop .ab-sub-wrapper, body #wpadminbar .shortlink-input',
					'body #wpadminbar .ab-top-menu>li:hover>.ab-item, body #wpadminbar .ab-top-menu>li.hover>.ab-item',
					'body #wpadminbar .quicklinks .menupop ul.ab-sub-secondary'
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
				'id'				=> 'ace_bar_submenu_bg_hover',
				'type'				=> 'css',
				'label'				=> __('Submenu color (hover)','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(', ',array(
					'body #wpadminbar .quicklinks .menupop ul li a:hover',
					'body #wpadminbar .quicklinks .menupop.hover ul li a:hover',
					'body #wpadminbar.nojs .quicklinks .menupop:hover ul li a:hover'
				)),
				'property'			=> 'background-color',
				'other_options'		=> array(
					'transparent'	=> 'transparent'
				),
				'btn_clear'			=> true,
				'opacity'			=> true,
				'real_time'			=> true				
			);	

	/*	
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_bar_font',
			'selector'	=> 'body #wpadminbar',
			'labels'	=> (object)array(
				'family'	=> __('Default font','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));		
*/
		//-- Logo --------------------------------------------------------------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-bar-logo'; 
		$t[$i]->label 		= __('Logo','rhace');
		$t[$i]->options = array();		

		$t[$i]->options[] =(object)array(
				'input_type'		=> 'raw_html',
				'html'				=> '<div class="ace_bar_logo_show"></div>'		
			);	

		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_bar_logo_show',
				'type'				=> 'css',
				'label'				=> __('Hide WordPress Logo','rhace'),
				'input_type'		=> 'select',
				'selector'			=> implode(', ',array(
					'.ace_bar_logo_show, body #wpadminbar #wp-admin-bar-wp-logo>.ab-item .ab-icon:before '
				)),
				'property'			=> 'display',
				'options'		=> array(
					'inline'	=> __('No','rhace'),
					'none'		=> __('Yes','rhace')
				),
				'real_time'			=> true										
			);	

		$t[$i]->options[] = (object)array(
				'id'				=> 'ace_bar_logo_img_w',
				'type'				=> 'css',
				'label'				=> __('Width','rhace'),
				'input_type'		=> 'number',
				'class'				=> 'input-mini',
				'unit'				=> 'px',
				'min'				=> 0,
				'max'				=> 200,
				'step'				=> 1,
				'holder_class'		=> '',
				'selector'			=> "body #wpadminbar #wp-admin-bar-wp-logo",
				'property'			=> 'width',
				'real_time'			=> true
			);	
			
		$t[$i]->options = $this->add_backgroud_options( $t[$i]->options, array(
			'label'		=> __('Logo','rhc'),
			'label_bg'	=> __('Background color','rhc'),
			'prefix'	=> 'ace_bar_logo_img',
			'selector'	=> 'body #wpadminbar #wp-admin-bar-wp-logo',
			'derived_color'=> array(
						array(
							'type'	=> 'color_darken',
							'val'	=> '5',
							'sel'	=> "body #wpadminbar #wp-admin-bar-wp-logo .ab-item",
							'arg'	=> array(
								(object)array(
									'name' => 'background-color',
									'tpl'	=>'transparent !important;'
								)
							)
						)
					)											
			)			
		);	
		
		//-- Icons --------------------------------------------------------------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-bar-icons'; 
		$t[$i]->label 		= __('Icons','rhace');
		$t[$i]->options = array();		

		$t[$i]->options[] =(object)array(
				'input_type'		=> 'raw_html',
				'html'				=> '<div class="ace_bar_icons"></div><div class="ace_bar_icons_h"></div>'		
			);		
		
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_bar_icons',
				'type'				=> 'css',
				'label'				=> __('Icon color','rhc'),
				'input_type'		=> 'color_or_something_else',
				'opacity'			=> true,
				'selector'			=> '.ace_bar_icons, body #wpadminbar .ab-icon:before, body #wpadminbar .ab-item:before, body #wpadminbar #adminbarsearch:before',
				'property'			=> 'color',
				'real_time'			=> true,
				'btn_clear'			=> true,
				'derived'			=> array()
		);
		
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_bar_icons_h',
				'type'				=> 'css',
				'label'				=> __('Icon hover color','rhc'),
				'input_type'		=> 'color_or_something_else',
				'opacity'			=> true,
				'selector'			=> '.ace_bar_icons_h, body #wpadminbar .quicklinks .menupop ul li a:hover, body #wpadminbar .quicklinks .menupop ul li a:focus, body #wpadminbar .quicklinks .menupop ul li a:hover strong, body #wpadminbar .quicklinks .menupop ul li a:focus strong, body #wpadminbar .quicklinks .menupop.hover ul li a:hover, body #wpadminbar .quicklinks .menupop.hover ul li a:focus, body #wpadminbar.nojs .quicklinks .menupop:hover ul li a:hover, body #wpadminbar.nojs .quicklinks .menupop:hover ul li a:focus, body #wpadminbar li:hover .ab-icon:before, body #wpadminbar li:hover .ab-item:before, body #wpadminbar li a:focus .ab-icon:before, body #wpadminbar li .ab-item:focus:before, body #wpadminbar li.hover .ab-icon:before, body #wpadminbar li.hover .ab-item:before, body #wpadminbar li:hover #adminbarsearch:before',
				'property'			=> 'color',
				'real_time'			=> true,
				'btn_clear'			=> true,
				'derived'			=> array()
		);

		//-- Menu fonts --------------------------------------------------------------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-bar-menu-fonts'; 
		$t[$i]->label 		= __('Menu fonts','rhace');
		$t[$i]->options = array();	

		$t[$i]->options[] =(object)array(
				'input_type'		=> 'raw_html',
				'html'				=> '<div class="ace_bar_menu_fonts_h"></div>'		
			);	
			
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_bar_default_fonts',
			'selector'			=> implode(', ',array(
				'body #wpadminbar',
				'body #wpadminbar *'
			)),
			'labels'	=> (object)array(
				'family'	=> __('Default font','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));
		
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_bar_menu_fonts',
			'selector'			=> implode(', ',array(
				'body #wpadminbar a.ab-item',
				'body #wpadminbar>#wp-toolbar span.ab-label',
				'body #wpadminbar>#wp-toolbar span.noticon'
			)),
			'labels'	=> (object)array(
				'family'	=> __('Menu font','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));

		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_bar_menu_fonts_h',
			'selector'			=> implode(', ',array(
				'.ace_bar_menu_fonts_h',
				'body.admin-bar #wpadminbar .ab-top-menu>li>.ab-item:focus',
				'body.admin-bar #wpadminbar .ab-top-menu>li>.ab-item:active',
				'body.admin-bar #wpadminbar .ab-top-menu>li>.ab-item:hover',
				'body.admin-bar #wpadminbar .ab-top-menu>li:hover>.ab-item',
				'body.admin-bar #wpadminbar .ab-top-menu>li.hover>.ab-item',
				'body.admin-bar #wpadminbar.nojq .quicklinks .ab-top-menu>li>.ab-item:focus',
				'body.admin-bar #wpadminbar .ab-top-menu>li:hover>.ab-item',
				'body.admin-bar #wpadminbar>#wp-toolbar li:hover span.ab-label',
				'body.admin-bar #wpadminbar>#wp-toolbar li.hover span.ab-label' ,
				'body.admin-bar #wpadminbar>#wp-toolbar a:focus span.ab-label'				
			)),
			'labels'	=> (object)array(
				'family'	=> __('Menu font (hover)','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));

		//-- Submenu fonts --------------------------------------------------------------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-bar-submenu-fonts'; 
		$t[$i]->label 		= __('Submenu fonts','rhace');
		$t[$i]->options = array();	

		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_bar_submenu_fonts',
			'selector'			=> implode(', ',array(
				'body #wpadminbar .quicklinks .menupop ul li a',
				'body #wpadminbar .quicklinks .menupop.hover ul li a',
				'body #wpadminbar.nojs .quicklinks .menupop:hover ul li a'
			)),
			'labels'	=> (object)array(
				'family'	=> __('Submenu font','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));

		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_bar_submenu_fonts_h',
			'selector'			=> implode(', ',array(
				'body #wpadminbar .quicklinks .menupop ul li a:hover',
				'body #wpadminbar .quicklinks .menupop.hover ul li a:hover',
				'body #wpadminbar.nojs .quicklinks .menupop:hover ul li a:hover'
			)),
			'labels'	=> (object)array(
				'family'	=> __('Submenu font (hover)','rhc'),
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
//----------------------------------------------------------------------
		return $t;
	}
}
?>