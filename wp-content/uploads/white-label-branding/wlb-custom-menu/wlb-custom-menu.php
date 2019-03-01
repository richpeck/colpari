<?php

/**
Plugin Name: WP Admin Custom Menu ( BETA )
Plugin URI: http://plugins.righthere.com/wlb-custom-menu/
Description: Add custom menus to WP Admin
Version: 1.0.0.55154
Author: (RightHere LLC)
Author URI: http://plugins.righthere.com
 **/

class plugin_wlb_custom_menu {
	var $map = array();
	function plugin_wlb_custom_menu(){
		global $wlb_plugin;
		$this->id = $wlb_plugin->id.'-nav';	
			
		add_filter("pop-options_{$this->id}",array(&$this,'options'),20,1);	
		
		add_action( 'admin_menu', array( &$this, 'admin_menu') );
	}
	
	function admin_menu(){
		global $wlb_plugin;
		
		$count = abs(intval($wlb_plugin->get_option('cm_count',0,true)));
		for($a=0;$a<$count;$a++){
			$prefix = 'cm'.$a.'_';
			$args=array();
			foreach( array('parent','menu_text','page_title','url','position','capability','behavior','frame_h') as $field){
				$option_name = $prefix.$field;
				$args[$field] = $wlb_plugin->get_option($option_name,'',true); 
			}
			extract($args);
			
			$id = 'wlb_cm_'.($a+1);
			
			$this->map[$id] = (object)array(
				'id' => $id,
				'url' => $url,
				'page_title' => $page_title,
				'behavior' => $behavior,
				'frame_h'	=> $frame_h
			);
	
			if( empty($parent) ){
				$page_id = add_menu_page( $menu_text, $menu_text, 'read', $id, array(&$this,'body'), '', $position );
			}else{
				$page_id = add_submenu_page( $parent, $menu_text, $menu_text, $capability, $id, array(&$this,'body') );	
			}
		
			add_action( 'load-' . $page_id, array( &$this, 'handle_custom_menu' ), 10, 1 );		
		}
		//

	}
	
	function handle_custom_menu( $args ){
		if( isset( $_REQUEST['page'] ) && isset( $this->map[$_REQUEST['page']] )){
			$o = $this->map[$_REQUEST['page']];
			$this->body = $o;
			
			if( $o->behavior == 'redirect' ){
				wp_redirect( $o->url );
				die();			
			}else if( $o->behavior == 'iframe' ){
				do_action( 'wlb_cm_head_'.$o->id, $o );
			}
		}
	}
	
	function body(){
		$id = $this->body->id;
		if( apply_filters( 'wlb_cm_body_'.$id, false, $this->body ) ){
			return;
		}
?>
<div class="wrap">
<?php screen_icon( $id ); ?>
<h2><?Php echo $this->body->page_title?></h2>
<iframe style="height:<?php echo $this->body->frame_h?>px" class="widefat" src="<?php echo $this->body->url?>"></iframe>
<div class="clear"></div>
<?php
	}
	
	function options($t){
		$i = count($t);
		//-----
		$i = count($t);
		@$t[$i]->id 			= 'wlb_custom_menu'; 
		$t[$i]->label 		= __('Custom Menu','wlb');//title on tab
		$t[$i]->right_label	= __('Custom Menu','wlb');//title on tab
		$t[$i]->page_title	= __('Custom Menu','wlb');//title on content
		$t[$i]->options = array(
			(object)array(
				'type'=>'subtitle',
				'label'=>__('Custom WP Admin menus','wlb')	
			)									
		);		
		
		$t[$i]->options[]=(object)array(
				'id'	=> 'cm_count',
				'type'	=> 'range',
				'label'	=> __('Number of custom menus','wlb'),
				'description'=> __('Specify the number of custom menu items you would like to add.  After saving the form will be updated.','wlb'),
				'min'	=> 0,
				'max'	=> 100,
				'step'	=> 1,
				'default'=> 1,
				'save_option'=>true,
				'load_option'=>true
			);				
		
		
		
		global $wlb_plugin,$menu;
	
		$count = abs(intval($wlb_plugin->get_option( 'cm_count', '1', true)));
		
		for($a=0;$a<$count;$a++){
			$t[$i]->options[]=(object)array(
				'type'=>'subtitle',
				'label'=> sprintf( __('Custom menu %s','wlb'), $a+1 )	
			);	

			$prefix = 'cm'.$a.'_';
			
			$t[$i]->options[]=	(object)array(
					'id'		=> $prefix.'parent',
					'type'		=>'select',
					'label'		=>__('Parent menu','wlb'),
					'options'	=> $this->get_parent_options(),
//					'el_properties' => array('class'=>'text-width-full'),
					'save_option'=>true,
					'load_option'=>true
				);
					
			$t[$i]->options[]=	(object)array(
					'id'		=> $prefix.'behavior',
					'type'		=>'select',
					'label'		=>__('URL behavior','wlb'),
					'options'	=> apply_filters('wlb_cm_behaviors', array('redirect'=>__('Redirect','wlb'),'iframe'=>__('Load in iframe','wlb'))),
					'default'	=> 'redirect',
					'save_option'=>true,
					'load_option'=>true
				);		
				
			$t[$i]->options[]=(object)array(
					'id'	=> $prefix.'frame_h',
					'type'	=> 'range',
					'label'=>__('Frame height','wlb'),
					'min'	=> 0,
					'max'	=> 2048,
					'step'	=> 1,
					'default'=> '600',
					'save_option'=>true,
					'load_option'=>true
				);								
								
			$t[$i]->options[]=	(object)array(
					'id'	=> $prefix.'menu_text',
					'type'=>'text',
					'label'=>__('Menu text','wlb'),
					'el_properties' => array('class'=>'text-width-full'),
					'save_option'=>true,
					'load_option'=>true
				);			
								
			$t[$i]->options[]=	(object)array(
					'id'	=> $prefix.'page_title',
					'type'=>'text',
					'label'=>__('Page title','wlb'),
					'el_properties' => array('class'=>'text-width-full'),
					'save_option'=>true,
					'load_option'=>true
				);	
				
			$t[$i]->options[]=	(object)array(
					'id'	=> $prefix.'url',
					'type'=>'text',
					'label'=>__('URL','wlb'),
					'el_properties' => array('class'=>'text-width-full'),
					'save_option'=>true,
					'load_option'=>true
				);	
				
			$WP_Roles = new WP_Roles();
			$t[$i]->options[]=	(object)array(
					'id'		=> $prefix.'capability',
					'type'		=>'select',
					'label'		=>__('Capability','wlb'),
					'options'	=> $this->get_all_caps_from_wp_roles($WP_Roles),
					'default'	=> 'read',
//					'el_properties' => array('class'=>'text-width-full'),
					'save_option'=>true,
					'load_option'=>true
				);	
				
				
			$t[$i]->options[]=(object)array(
					'id'	=> $prefix.'position',
					'type'	=> 'range',
					'label'=>__('Position','wlb'),
					'min'	=> 0,
					'max'	=> 100,
					'step'	=> .1,
					'default'=> $this->random_menu_position(),
					'save_option'=>true,
					'load_option'=>true
				);					
										
		}		
					
		$t[$i]->options[]=(object)array('type'=>'clear');
		$t[$i]->options[]=(object)array('label'=>__('Save Changes','wlb'),'type'=>'submit','class'=>'button-primary', 'value'=> '' );
		
		return $t;
	}	
	
	function get_parent_options(){
		global $menu;
		$options = array(''=>__('--no parent--','wlb'));
		foreach($menu as $m){
			if( in_array($m[2], array('separator1','separator2','separator-last')) ) continue;
			$options[$m[2]]=$m[0];
		}
		return $options;
	}
	
	function random_menu_position( $min = 20, $max = 100 ) {
    	return $min + mt_rand() / mt_getrandmax() * ($max - $min);
	}
	
	function get_all_caps_from_wp_roles($WP_Roles){
		$all_caps = array();
		if(count($WP_Roles->roles)>0){
			foreach($WP_Roles->roles as $role_id => $row){
				foreach($row['capabilities'] as $capability => $allowed){
					$all_caps[$capability]=$capability;
				}
			}
		}
		return $all_caps;	
	}	
}

new plugin_wlb_custom_menu();
?>