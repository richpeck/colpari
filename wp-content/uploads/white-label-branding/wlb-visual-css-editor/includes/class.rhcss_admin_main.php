<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class rhcss_admin_main extends module_righthere_css{
	function __construct($args=array()){
		$args['cb_init']=array(&$this,'cb_init');
		return parent::__construct($args);
	}

	function cb_init(){
		//called on the head when editor is active.
		
	}
	
	function options($t=array()){
		$i = count($t);
	
									
		//---- Body link ------------------------------------------------------------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-main'; 
		$t[$i]->label 		= __('Body','rhace');
		$t[$i]->options = array();	
		
		$t[$i]->options[] =(object)array(
			'input_type'=>'raw_html',
			'html' => implode('',array(
					'<div class="ace_main_body_bg"></div>',
					'<div class="ace_main_boxes_inner"></div>'								
				)
			)
		);
		
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_main_body_bg',
				'type'				=> 'css',
				'label'				=> __('Main content Color','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> '.ace_main_body_bg',
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
								'sel'	=> 'html.wp-toolbar',//assumes wp-toolbar is always active in admin- there is no other way atm to select html in the admin.
								'arg'	=> array(
									(object)array(
										'name' => 'background',
										'tpl'	=>'__value__;'
									)
								)
							),					
							array(
								'type'	=> 'same',
								'val'	=> '5',
								'sel'	=> 'body ul#adminmenu a.wp-has-current-submenu:after, body ul#adminmenu>li.current>a.current:after',
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
				'id'				=> 'ace_main-font-family',
				'type'				=> 'css',
				'label'				=> __('Default font family','rhace'),
				'input_type'		=> 'font',
				'class'				=> '',
				'holder_class'		=> '',
				'selector'			=> 'body',
				'property'			=> 'font-family',
				'real_time'			=> true
			);	
			
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_main_title_h2',
			'selector'	=> implode(',',array(
				'body .wrap h2',
				'body .subtitle'
			)),
			'labels'	=> (object)array(
				'family'	=> __('Page title font','rhace'),
				'size'		=> __('Size','rhace'),
				'color'		=> __('Color','rhace')
								
			)
		));		
		
		//---- Body bg image ------------------------------------------------------------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-admin-bg'; 
		$t[$i]->label 		= __('Body background','rhace');
		$t[$i]->options = array();			

		$t[$i]->options = $this->add_backgroud_options( $t[$i]->options, array(
			'label'		=> __('Background image','rhc'),
			'prefix'	=> 'ace_admin_bg',
			'selector'	=> 'body.wp-admin.wp-core-ui, body.wp-admin.wp-core-ui #wpwrap'
		));			

		//-- Metaboxes ----------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-main-metaboxes'; 
		$t[$i]->label 		= __('Metaboxes','rhace');
		$t[$i]->options = array();	

		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_main_boxes_body',
				'type'				=> 'css',
				'label'				=> __('Content color','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(',',array(
					'body .welcome-panel',
					'body .postbox',
					'body table.widefat',
					'body .wp-editor-container',
					'body .stuffbox',
					'body p.popular-tags',
					'body .widgets-holder-wrap',
					'body .popular-tags',
					'body .feature-filter',
					'body .imgedit-group',
					'body .toggle-option .option-title',
					'body .toggle-option .inside'
				)),
				'property'			=> 'background-color',
				'other_options'		=> array(
					'transparent'	=> 'transparent'
				),
				'btn_clear'			=> true,
				'opacity'			=> true,
				'real_time'			=> true				
			);		
		
		$t[$i]->options = $this->add_border_options($t[$i]->options,array(
			'prefix'	=> 'ace_main_boxes_border',
			'selector'			=> implode(',',array(
				'body .widget-top',
				'body .menu-item-handle',
				'body .menu-item-settings',
				'body .widget-inside',
				'body .postbox',
				'body #menu-settings-column .accordion-container',
				'body #menu-management .menu-edit',
				'body .manage-menus',
				'body table.widefat',
				'body .stuffbox',
				'body p.popular-tags',
				'body .widgets-holder-wrap',
				'body .welcome-panel',
				'body .wp-editor-container',
				'body #post-status-info',
				'body .popular-tags',
				'body .feature-filter',
				'body .imgedit-group'
			))
		));							
			
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_main_boxes_inner',
				'type'				=> 'css',
				'label'				=> __('Inner content color','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(', ',array(
					'.ace_main_boxes_inner',
					'body #activity-widget #the-comment-list .comment',
					'body .alternate, body .alt'
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
				'id'				=> 'ace_main_header_bborder',
				'type'				=> 'css',
				'label'				=> __('Header border color','rhc'),
				'input_type'		=> 'color_or_something_else',
				'opacity'			=> true,
				'selector'			=> '.wp-admin.wp-core-ui .widefat thead th, body.wp-admin.wp-core-ui .postbox h3, body.wp-admin.wp-core-ui #namediv h3, body.wp-admin.wp-core-ui #submitdiv h3',
				'property'			=> 'border-bottom-color',
				'real_time'			=> true,
				'btn_clear'			=> true,
				'derived'			=> array()
		);
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_main_footer_bborder',
				'type'				=> 'css',
				'label'				=> __('Footer border color','rhc'),
				'input_type'		=> 'color_or_something_else',
				'opacity'			=> true,
				'selector'			=> 'body.wp-admin.wp-core-ui #dashboard_primary .rss-widget, body.wp-admin.wp-core-ui .activity-block',
				'property'			=> 'border-bottom-color',
				'real_time'			=> true,
				'btn_clear'			=> true,
				'derived'			=> array(
							array(
								'type'	=> 'same',
								'val'	=> '5',
								'sel'	=> '.wp-admin.wp-core-ui .widefat tfoot th',
								'arg'	=> array(
									(object)array(
										'name' => 'border-top-color',
										'tpl'	=>'__value__;'
									)
								)
							),
							array(
								'type'	=> 'same',
								'val'	=> '5',
								'sel'	=> 'body.wp-admin.wp-core-ui #dashboard_activity .subsubsub',
								'arg'	=> array(
									(object)array(
										'name' => 'border-top-color',
										'tpl'	=>'__value__;'
									)
								)
							)
				)
				
		);	
		
		//-- Metaboxes ----------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-main-metaboxes-fonts'; 
		$t[$i]->label 		= __('Metaboxes fonts','rhace');
		$t[$i]->options = array();	
		
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_main_mbox_default_font',
			'selector'	=> 'body.wp-admin #poststuff, body.wp-admin .metabox-holder, body.wp-admin .postbox',
			'labels'	=> (object)array(
				'family'	=> __('Default font','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));		
		
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_main_mbox_font',
			'selector'	=> 'body.wp-admin #poststuff h3, body.wp-admin .metabox-holder h3',
			'labels'	=> (object)array(
				'family'	=> __('Header font (h3)','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));		

		//-- Metaboxes ----------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-main-metaboxes-icon'; 
		$t[$i]->label 		= __('Metaboxes icon','rhace');
		$t[$i]->options = array();			
		
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_mbox_icon_toggle',
				'type'				=> 'css',
				'label'				=> __('Toggle icon color','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(',',array(
					'body.wp-admin.wp-core-ui .widget-action',
					'body.wp-admin.wp-core-ui .handlediv',
					'body.wp-admin.wp-core-ui .item-edit',
					'body.wp-admin.wp-core-ui .sidebar-name-arrow',
					'body.wp-admin.wp-core-ui .accordion-section-title:after'
				)),				
				'property'			=> 'color',
				'btn_clear'			=> true,
				'opacity'			=> true,
				'real_time'			=> true
			);		
		
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_mbox_icon_toggle',
				'type'				=> 'css',
				'label'				=> __('Toggle icon color (hover)','rhace'),
				'input_type'		=> 'color_or_something_else',
				'selector'			=> implode(',',array(
					'body.wp-admin.wp-core-ui .widget-action:hover',
					'body.wp-admin.wp-core-ui .handlediv:hover',
					'body.wp-admin.wp-core-ui .item-edit:hover',
					'body.wp-admin.wp-core-ui .sidebar-name:hover .sidebar-name-arrow',
					'body.wp-admin.wp-core-ui .accordion-section-title:hover:after'
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