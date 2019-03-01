<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class admin_bar_editor_access {
	function __construct($args=array()){
		$defaults = array(
			'capability'	=> 'manage_options',
			'nodes'			=> array()
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}
		//-----------
		if(!empty($this->nodes) && current_user_can($this->capability)){
			add_action( 'admin_bar_menu', array(&$this,'wp_before_admin_bar_render'), 15 );
			//add_action( 'wp_before_admin_bar_render', array(&$this,'wp_before_admin_bar_render') );
		}
	}
	
	function wp_before_admin_bar_render(){
		global $wp_admin_bar;

		if(null==$wp_admin_bar->get_node('rh-css-editor')){
			$wp_admin_bar->add_group( array('id'=>'rh-css-editor') );
			//-------
		    $args = array(
				'id' => 'rh-css-editor-root', 
				'title' => __('CSS Editor','rhcss')
			); 
		    $wp_admin_bar->add_node($args);		
		}
		
		foreach($this->nodes as $node){
			$wp_admin_bar->add_node($node);	
		}	    	
	}
}
?>