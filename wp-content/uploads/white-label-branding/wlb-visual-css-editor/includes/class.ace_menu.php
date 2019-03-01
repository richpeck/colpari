<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class ace_menu extends module_righthere_css{
	function __construct($args=array()){
		//$args['cb_init']=array(&$this,'cb_init');
		return parent::__construct($args);
	}

	function cb_init(){
		//called on the head when editor is active.
	}
	
	function options($t=array()){
		$i = count($t);
		//require RHL_PATH.'includes/admin_frontend_options.php';
		//----------------------------------------------------------------------
		$i++;
		$t[$i]=(object)array();
		$t[$i]->id 			= 'ace-current-menu'; 
		$t[$i]->label 		= __('Curren menu','rhace');
		$t[$i]->options = array();
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'ace_current_menu',
			'selector'	=> 'body #adminmenu li.current a.menu-top',
			'labels'	=> (object)array(
				'family'	=> __('Font','rhace'),
				'size'		=> __('Size','rhace'),
				'color'		=> __('Color','rhace')
								
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