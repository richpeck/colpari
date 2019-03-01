<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class rhcss_admin_forms extends module_righthere_css{
	function __construct($args=array()){
		add_action('admin_menu', array(&$this,'admin_menu'));
		//----
		$args['cb_init']=array(&$this,'cb_init');
		return parent::__construct($args);
	}

	function admin_menu(){
		add_submenu_page('admin.php', 'Sample form', 'Sample form', 'read', 'ace-forms', array(&$this, 'ace_forms') );
	}
	
	function ace_forms(){
?>
<div class="wrap">
	<h2><?php _e('Sample form elements','rhace')?></h2>
	<div class="postbox">
	<div class="inside">
		<ul>
			<li>
				<div class="updated acemsg aceupdated">Updated message</div>
			</li>
			<li>
				<div class="error acemsg aceerror">Error message</div>
			</li>
			<li>
				<button class="button-primary"><?php _e('Primary button','rhace')?></button>
				<button class="button-primary rh-primary-btn-hover"><?php _e('Hovered Primary button','rhace')?></button>
				<button class="button-primary rh-primary-btn-focus"><?php _e('Focus/Active Primary button','rhace')?></button>
			</li>
			
			
			<li>
				<button class="button"><?php _e('Secondary button','rhace')?></button>
				<button class="button rh-btn-hover"><?php _e('Hovered Secondary button','rhace')?></button>
				<button class="button rh-btn-focus"><?php _e('Focus/Active Secondary button','rhace')?></button>
			</li>
	
			<li>
				<h2 style="float:left;"><a class="add-new-h2" href="javascript:void(0);"><?php _e('Add new button','rhace')?></a></h2>
				<h2 style="float:left;"><a class="add-new-h2 rh-btn-addnew-hover" href="javascript:void(0);"><?php _e('Add new button (hover)','rhace')?></a></h2>
				<h2 style="float:left;"><a class="add-new-h2 rh-btn-addnew-focus" href="javascript:void(0);"><?php _e('Add new button (focus/active)','rhace')?></a></h2>
				<div style="clear:both"></div>
			</li>
	
					
			<li>
				<input type="text" value="" placeholder="<?php _e('Input fields','rhace')?>" />
			</li>
			<li>
				<textarea placeholder="<?php _e('Input fields','rhace')?>"></textarea>
			</li>
			<li>
				<select>
					<option><?php _e('Input fields','rhace')?></option>
				</select>
			</li>
			<li>
				<input checked="checked" type="checkbox" value="1">&nbsp;Checkboxes
			</li>
			<li>
				<input type="radio" checked="checked" name="radio" value="1">&nbsp;Radio button
			</li>
			<li>
				<input type="radio" name="radio" value="2">&nbsp;Radio button
			</li>
		</ul>	
	</div>
	</div>
</div>
<?php	
	}
	
	function cb_init(){
		//called on the head when editor is active.
		
	}
	
	function options($t=array()){
		$i = count($t);
		//-- Messages --------------------------------------------------------------------

		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace_form_messages'; 
		$t[$i]->label 		= __('Messages','rhace');
		$t[$i]->options = array();		

		$t[$i]->options[] =(object)array(
				'input_type'		=> 'raw_html',
				'html'				=> '<div class="acemsg" style="display:none;">&nbsp;</div>'
			);		

		$t[$i]->options[] =(object)array(
				'input_type'		=> 'raw_html',
				'html'				=> '<div class="aceupdated" style="display:none;"></div>'
			);		
			
		$t[$i]->options[] =(object)array(
				'input_type'		=> 'raw_html',
				'html'				=> '<div class="aceerror" style="display:none;"></div>'
			);		
		
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_form_messages_bgcolor',
				'type'				=> 'css',
				'label'				=> __('Body color','rhc'),
				'input_type'		=> 'color_or_something_else',
				'opacity'			=> true,
				'selector'			=> '.acemsg, body.wp-admin.wp-core-ui div.updated, body.wp-admin.wp-core-ui div.error',
				'property'			=> 'background-color',
				'real_time'			=> true,
				'btn_clear'			=> true
		);			
	
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_form_msg_error_lbc',
				'type'				=> 'css',
				'label'				=> __('Error, left border color','rhc'),
				'input_type'		=> 'color_or_something_else',
				'opacity'			=> true,
				'selector'			=> '.aceerror, .wp-admin .wrap div.error',
				'property'			=> 'border-left-color',
				'real_time'			=> true,
				'btn_clear'			=> true,
				'derived'			=> array()
		);
		
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_form_msg_upd_lbc',
				'type'				=> 'css',
				'label'				=> __('Update, left border color','rhc'),
				'input_type'		=> 'color_or_something_else',
				'opacity'			=> true,
				'selector'			=> '.aceupdated, .wp-admin .wrap div.updated',
				'property'			=> 'border-left-color',
				'real_time'			=> true,
				'btn_clear'			=> true,
				'derived'			=> array()
		);		
		
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_form_msg_font',
			'selector'	=> '.acemsg, .wp-admin .wrap div.updated, .wp-admin .wrap div.error',
			'labels'	=> (object)array(
				'family'	=> __('Font','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));		
/*		
		$t[$i]->options = $this->add_border_radius_options($t[$i]->options,array(
			'prefix'	=> 'ace_form_msg_radius',
			'selector'	=> ".acemsg, .wp-admin .wrap div.updated, .wp-admin .wrap div.error"
		));							
*/

		//-- Primary button --------------------------------------------------------------------
		$buttons = array(
			(object)array(
				'id'		=> 'ace_form_primary_btn',
				'label'		=> __('Primary button','rhace'),
				'selector'	=> 'body.wp-core-ui.wp-admin .button-primary, body.wp-core-ui.wp-admin .button.button-primary'
			),
			(object)array(
				'id'		=> 'ace_form_primary_btn_h',
				'label'		=> __('Primary button (hover)','rhace'),
				'selector'	=> 'body.wp-core-ui.wp-admin .button-primary:hover, body.wp-core-ui.wp-admin .button.button-primary:hover, body.wp-core-ui.wp-admin .button-primary.rh-primary-btn-hover'
			),
			(object)array(
				'id'		=> 'ace_form_primary_btn_focus',
				'label'		=> __('Primary button (focus/active)','rhace'),
				'selector'	=> 'body.wp-core-ui.wp-admin .button-primary:focus, body.wp-core-ui.wp-admin .button.button-primary:focus, body.wp-core-ui.wp-admin .button-primary:active, body.wp-core-ui.wp-admin .button.button-primary:active, body.wp-core-ui.wp-admin .button-primary.rh-primary-btn-focus'
			),
			(object)array(
				'id'		=> 'ace_form_secondary_btn',
				'label'		=> __('Secondary button','rhace'),
				'selector'	=> 'body.wp-core-ui.wp-admin .button'
			),
			(object)array(
				'id'		=> 'ace_form_secondary_btn_h',
				'label'		=> __('Secondary button (hover)','rhace'),
				'selector'	=> 'body.wp-core-ui.wp-admin .button:hover, body.wp-core-ui.wp-admin .button.rh-btn-hover'
			),
			(object)array(
				'id'		=> 'ace_form_secondary_btn_focus',
				'label'		=> __('Secondary button (focus/active)','rhace'),
				'selector'	=> 'body.wp-core-ui.wp-admin .button:focus, body.wp-core-ui.wp-admin .button:active, body.wp-core-ui.wp-admin .button.rh-btn-focus'
			),
			(object)array(
				'id'		=> 'ace_form_add_btn',
				'label'		=> __('Add new button','rhace'),
				'selector'	=> 'body.wp-core-ui.wp-admin .wrap .add-new-h2'
			),
			(object)array(
				'id'		=> 'ace_form_add_btn_h',
				'label'		=> __('Add new button (hover)','rhace'),
				'selector'	=> 'body.wp-core-ui.wp-admin .wrap .add-new-h2:hover, body.wp-core-ui.wp-admin .wrap .add-new-h2.rh-btn-addnew-hover'
			),
			(object)array(
				'id'		=> 'ace_form_add_btn_focus',
				'label'		=> __('Add new button (focus/active)','rhace'),
				'selector'	=> 'body.wp-core-ui.wp-admin .wrap .add-new-h2:focus, body.wp-core-ui.wp-admin .wrap .add-new-h2:active, body.wp-core-ui.wp-admin .wrap .add-new-h2.rh-btn-addnew-focus'
			)
			
		);
		
		foreach($buttons as $btn){
			$i++;
			$t[$i]=(object)array();
			$t[$i]->id 			= $btn->id; 
			$t[$i]->label 		= $btn->label;
			$t[$i]->options = array();	
					
			$t[$i]->options = $this->add_backgroud_options( $t[$i]->options, array(
				'label'		=> __('Button background','rhc'),
				'prefix'	=> $btn->id.'_bg',
				'selector'	=> $btn->selector
			));
			
			$t[$i]->options = $this->add_border_options($t[$i]->options,array(
				'prefix'	=> $btn->id.'_border',
				'selector'	=> $btn->selector	
			));		
									
			$t[$i]->options[] =(object)array(
					'id'				=> $btn->id.'_outline',
					'type'				=> 'css',
					'label'				=> __('Outline color','rhc'),
					'input_type'		=> 'color_or_something_else',
					'opacity'			=> true,
					'selector'			=> $btn->selector,
					'property'			=> 'outline-color',
					'real_time'			=> true,
					'btn_clear'			=> true,
					'derived'			=> array()
			);	
			
			$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
				'prefix'	=> $btn->id.'_font',
				'selector'	=> $btn->selector,
				'labels'	=> (object)array(
					'family'	=> __('Font','rhc'),
					'size'		=> __('Size','rhc'),
					'color'		=> __('Color','rhc')				
				)
			));	
			
			$t[$i]->options[] =(object)array(
					'id'				=> $btn->id.'_shadow',
					'type'				=> 'css',
					'label'				=> __('Shadow','rhace'),
					'input_type'		=> 'textshadow',
					'opacity'			=> true,
					'selector'			=> $btn->selector,
					'property'			=> 'box-shadow',
					'real_time'			=> true,
					'btn_clear'			=> true
				);					
		}

			
		//-- Inpput --------------------------------------------------------------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace_form_input'; 
		$t[$i]->label 		= __('Input fields','rhace');
		$t[$i]->options = array();				
		$t[$i]->options[] =(object)array(
			'input_type'=>'raw_html',
			'html' => implode('',array(
					'<div class="rh-admin-input" style="display:none;"></div>',
					'<div class="rh-admin-checkbox" style="display:none;"></div>',
					'<div class="rh-admin-radio" style="display:none;"></div>',
					'<div class="rh-admin-radio-marker" style="display:none;"></div>'								
				)
			)
		);
		
		$selector = '.rh-admin-input, body.wp-core-ui.wp-admin  textarea, body.wp-core-ui.wp-admin  input[type=text], body.wp-core-ui.wp-admin  input[type=password], body.wp-core-ui.wp-admin  input[type=email], body.wp-core-ui.wp-admin  input[type=number], body.wp-core-ui.wp-admin  input[type=search], body.wp-core-ui.wp-admin  input[type=tel], body.wp-core-ui.wp-admin  input[type=url], body.wp-core-ui.wp-admin select, .rh-admin-input';
		
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_form_input',
				'type'				=> 'css',
				'label'				=> __('Background color','rhc'),
				'input_type'		=> 'color_or_something_else',
				'opacity'			=> true,
				'selector'			=> $selector,
				'property'			=> 'background-color',
				'real_time'			=> true,
				'btn_clear'			=> true,
				'derived'			=> array()
		);	
			
		$t[$i]->options = $this->add_border_options( $t[$i]->options, array(
			'prefix'	=> 'ace_form_input_border',
			'selector'  => $selector
		) );
		//-- Checkboxes --------------------------------------------------------------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace_form_checkbox'; 
		$t[$i]->label 		= __('Checkboxes','rhace');
		$t[$i]->options = array();	
		
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_form_checkmark_color',
				'type'				=> 'css',
				'label'				=> __('Checkmark color','rhc'),
				'input_type'		=> 'color_or_something_else',
				'opacity'			=> true,
				'selector'			=> '.rh-admin-checkbox, body input[type=checkbox]:checked:before',
				'property'			=> 'color',
				'real_time'			=> true,
				'btn_clear'			=> true
		);				
		
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_form_checkbox_bg',
				'type'				=> 'css',
				'label'				=> __('Background color','rhc'),
				'input_type'		=> 'color_or_something_else',
				'opacity'			=> true,
				'selector'			=> '.rh-admin-checkbox, body.wp-core-ui.wp-admin input[type=checkbox]',
				'property'			=> 'background-color',
				'real_time'			=> true,
				'btn_clear'			=> true,
				'derived'			=> array()
		);	
		
		$t[$i]->options = $this->add_border_options( $t[$i]->options, array(
			'prefix'	=> 'ace_form_chk_border',
			'selector'  => '.rh-admin-checkbox, body.wp-core-ui.wp-admin input[type=checkbox]'
		) );
		
		$t[$i]->options = $this->add_border_radius_options( $t[$i]->options, array(
			'prefix'	=> 'ace_form_chk_radius',
			'selector'  => '.rh-admin-checkbox, body.wp-core-ui.wp-admin input[type=checkbox]'
		) );

		//-- Radio --------------------------------------------------------------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace_form_radio'; 
		$t[$i]->label 		= __('Radio buttons','rhace');
		$t[$i]->options = array();	

		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_form_radio_marker_color',
				'type'				=> 'css',
				'label'				=> __('Marker color','rhc'),
				'input_type'		=> 'color_or_something_else',
				'opacity'			=> true,
				'selector'			=> '.rh-admin-radio-marker, body input[type=radio]:checked:before',
				'property'			=> 'background-color',
				'real_time'			=> true,
				'btn_clear'			=> true
		);						
		
		$t[$i]->options[] =(object)array(
				'id'				=> 'ace_form_radio_bg',
				'type'				=> 'css',
				'label'				=> __('Background color','rhc'),
				'input_type'		=> 'color_or_something_else',
				'opacity'			=> true,
				'selector'			=> '.rh-admin-radio, body.wp-core-ui.wp-admin input[type=radio]',
				'property'			=> 'background-color',
				'real_time'			=> true,
				'btn_clear'			=> true,
				'derived'			=> array()
		);	
		
		$t[$i]->options = $this->add_border_options( $t[$i]->options, array(
			'prefix'	=> 'ace_form_radio_border',
			'selector'  => '.rh-admin-radio, body.wp-core-ui.wp-admin input[type=radio]'
		) );
		
		$t[$i]->options = $this->add_border_radius_options( $t[$i]->options, array(
			'prefix'	=> 'ace_form_radio_radius',
			'selector'  => '.rh-admin-radio, body.wp-core-ui.wp-admin input[type=radio]'
		) );
														
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