<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class rhcss_wp_login extends module_righthere_css{
	function __construct( $args = array() ) {
		if ( isset( $_REQUEST[ $args['trigger_var'] ] ) && $_REQUEST[ $args['trigger_var'] ] == $args['trigger_val'] ) {
			add_filter( 'login_message', array(  $this, 'login_message'), 10, 1 );
		}

		return parent::__construct( $args );
	}

	function login_message( $message ) {
		if ( empty( $message ) ) {
			$message = sprintf( '<div id="login_error">%s</div>', __( 'Login error', 'spl' ) );
			$message.= sprintf( '<p class="message">%s</p>', __( 'Other messages', 'spl' ) );
		}
		
		return $message;
	}
	
	function options($t=array()){
		$this->footer();
		//----
		$i = count($t);
		$t[$i]=(object)array();
		$t[$i]->id 			= 'spl-wp-login-logo'; 
		$t[$i]->label 		= __('General Settings','spl');
		$t[$i]->options = array();	

		$t[$i]->options[] =(object)array(
			'id'			=> 'spl_wp_login_logo_display',
			'type'			=> 'css',
			'label'			=> __('WordPress Logo','spl'),
			'input_type'	=> 'select',
			'options'		=> array(
				'none' 	=> __('Hide Logo', 'spl'),
				'block' => __('Show Logo', 'spl')
			),
			'selector'		=> '.login.wp-core-ui #login > h1:first-child > a',
			'property'		=> 'display',
			'real_time'		=> true
		);

		$t[$i]->options[] =(object)array(
				'id'				=> 'spl_wp_login_width',
				'type'				=> 'css',
				'label'				=> __('Width','rhc'),
				'input_type'		=> 'number',
				'unit'				=> 'px',
				'class'				=> 'input-mini',
				'min'				=> '250',
				'max'				=> '2048',
				'step'				=> '1',
				'selector'	=> implode(',',array(
					'.login.wp-core-ui #login'
				)),	
				'property'			=> 'width',
				'real_time'			=> true
			);	
			
		$t[$i]->options = $this->add_border_radius_options($t[$i]->options,array(
			'prefix'	=> 'spl_wp_login_rad_',
			'selector'	=> implode(',',array(
				'.login.wp-core-ui #login form'
			))
		));		
		
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> 'spl_wp_login_def_font',
			'selector'	=> implode(',',array(
				'.login.wp-core-ui #login form'
			)),			
			'labels'	=> (object)array(
				'family'	=> __('Default font','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));		
		
		//---- CUSTOM LOGO ---------------
		$id = 'spl_wp_login_custom_logo';
		$selector = '.login.wp-core-ui #login > h1:first-child';
		$i = count($t);
		$t[$i]=(object)array();
		$t[$i]->id 			= $id; 
		$t[$i]->label 		= __('Custom Logo','spl');
		$t[$i]->options = array();									
		
		$t[$i]->options[] =(object)array(
				'id'				=> $id.'_height',
				'type'				=> 'css',
				'label'				=> __('Height','spl'),
				'input_type'		=> 'number',
				'unit'				=> 'px',
				'class'				=> 'input-mini',
				'min'				=> '20',
				'max'				=> '600',
				'step'				=> '1',
				'selector'	=> implode(',',array(
					$selector
				)),	
				'property'			=> 'height',
				'real_time'			=> true
			);	

		$t[$i]->options[] =(object)array(
				'id'				=> $id.'_sep1',
				'type'				=> 'css',
				'label'				=> __('Logo - messages/form separation','spl'),
				'input_type'		=> 'number',
				'unit'				=> 'px',
				'class'				=> 'input-mini',
				'min'				=> '0',
				'max'				=> '200',
				'step'				=> '1',
				'selector'	=> implode(',',array(
					'.login.wp-core-ui #login > h1'
				)),	
				'property'			=> 'margin-bottom',
				'real_time'			=> true
			);		

		$t[$i]->options[] =(object)array(
				'id'				=> $id.'_sep2',
				'type'				=> 'css',
				'label'				=> __('Messages - form separation','spl'),
				'input_type'		=> 'number',
				'unit'				=> 'px',
				'class'				=> 'input-mini',
				'min'				=> '0',
				'max'				=> '200',
				'step'				=> '1',
				'selector'	=> implode(',',array(
					'.login.wp-core-ui #login form'
				)),	
				'property'			=> 'margin-top',
				'real_time'			=> true
			);	
					
		$t[$i]->options = $this->add_backgroud_options( $t[$i]->options, array(
			'label'		=> __('Background','spl'),
			'prefix'	=> $id.'_bg-',
			'selector'	=> $selector	
		));		
		
		//---- MSG LOGIN ERROR ---------------
		$id = 'spl_wp_login_error';
		$selector = '.login.wp-core-ui #login #login_error';
		$i = count($t);
		$t[$i]=(object)array();
		$t[$i]->id 			= $id; 
		$t[$i]->label 		= __('Login error','spl');
		$t[$i]->options = array();				

		$t[$i]->options[] = (object)array(
				'id'				=> $id.'-border-left',
				'type'				=> 'css',
				'label'				=> __('Left border color','rhc'),
				'input_type'		=> 'color_or_something_else',
				'holder_class'		=> '',
				'opacity'			=> true,
				'btn_clear'			=> true,
				'selector'	=> implode(',',array(
					$selector
				)),
				'property'			=> 'border-left-color',			
				'real_time'			=> true
			);			
		
		$t[$i]->options = $this->add_backgroud_options( $t[$i]->options, array(
			'label'		=> __('Background','spl'),
			'prefix'	=> $id.'_bg_',
			'selector'	=> $selector	
		));				
		
		//---- MESSAGE ---------------
		$id = 'spl_wp_login_msg';
		$selector = '.login.wp-core-ui #login > .message';
		$i = count($t);
		$t[$i]=(object)array();
		$t[$i]->id 			= $id; 
		$t[$i]->label 		= __('Message','spl');
		$t[$i]->options = array();				

		$t[$i]->options[] = (object)array(
				'id'				=> $id.'-border-left',
				'type'				=> 'css',
				'label'				=> __('Left border color','rhc'),
				'input_type'		=> 'color_or_something_else',
				'holder_class'		=> '',
				'opacity'			=> true,
				'btn_clear'			=> true,
				'selector'	=> implode(',',array(
					$selector
				)),
				'property'			=> 'border-left-color',			
				'real_time'			=> true
			);			
		
		$t[$i]->options = $this->add_backgroud_options( $t[$i]->options, array(
			'label'		=> __('Background','spl'),
			'prefix'	=> $id.'_bg_',
			'selector'	=> $selector	
		));				
		
		//----- BACKGROUND
		$i = count($t);
		$t[$i]=(object)array();
		$t[$i]->id 			= 'spl-wp-login-bg'; 
		$t[$i]->label 		= __('Background','spl');
		$t[$i]->options = array();	
		$t[$i]->options = $this->add_backgroud_options( $t[$i]->options, array(
			'label'		=> __('Background','spl'),
			'prefix'	=> 'spl_wp_login_bg_',
			'selector'	=> 'BODY.login.wp-core-ui'	
		));

		// Form Background
		$i = count( $t );
		$t[ $i ] = (object) array();
		$t[ $i ]->id      = 'spl-wp-login-form-bg'; 
		$t[ $i ]->label   = __( 'Form Background', 'rhc' );
		$t[ $i ]->options = array();	
		$t[ $i ]->options = $this->add_backgroud_options( $t[ $i ]->options, array(
			'label'    => __( 'Background', 'rhc' ),
			'prefix'   => 'spl_wp_login_form_bg_',
			'selector' => 'BODY.login.wp-core-ui form'
		) );

		$t[ $i ]->options = $this->add_border_options( $t[ $i ]->options, array(
			'prefix'   => 'spl_wp_login_form_border_',
			'selector' => 'BODY.wp-core-ui #login form'
		) );

		$t[ $i ]->options[] =(object)array(
			'id'         => 'spl-wp-login-form-shadow',
			'type'       => 'css',
			'label'      => __( 'Shadow', 'rhc' ),
			'input_type' => 'textshadow',
			'opacity'    => true,
			'selector'   => 'BODY.wp-core-ui #login form',
			'property'   => 'box-shadow',
			'real_time'  => true,
			'btn_clear'  => true
		);

		// Input fields
		$id = 'spl_wp_login_inp';						
		$selector = '.login.wp-core-ui #login form input.input';
		
		$i = count($t);
		$t[$i]=(object)array();
		$t[$i]->id 			= $id; 
		$t[$i]->label 		= __('Input fields','spl');
		$t[$i]->options = array();		

		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> $id.'-label_font',
			'selector'	=> implode(',',array(
				'.login.wp-core-ui #login form label'
			)),			
			'labels'	=> (object)array(
				'family'	=> __('Label font','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));		
		
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> $id.'-font',
			'selector'	=> implode(',',array(
				$selector
			)),			
			'labels'	=> (object)array(
				'family'	=> __('Input font','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));		

		$t[$i]->options[] = (object)array(
				'id'				=> $id.'-bgcolor',
				'type'				=> 'css',
				'label'				=> __('Background color','rhc'),
				'input_type'		=> 'color_or_something_else',
				'holder_class'		=> '',
				'opacity'			=> true,
				'btn_clear'			=> true,
				'selector'	=> implode(',',array(
					$selector
				)),
				'property'			=> 'background-color',			
				'real_time'			=> true
			);	

		
		$t[$i]->options = $this->add_border_options($t[$i]->options,array(
			'prefix'	=> $id.'-border_',
			'selector'	=> implode(',',array(
				$selector
			))
		));			

		$t[$i]->options = $this->add_border_radius_options($t[$i]->options,array(
			'prefix'	=> $id.'-rad_',
			'selector'	=> implode(',',array(
				$selector
			))
		));			
		
		//----- BUTTON
		$id = 'spl_wp_login_btn';
		$selector = '.login.wp-core-ui .button-primary';
		
		$i = count($t);
		$t[$i]=(object)array();
		$t[$i]->id 			= $id; 
		$t[$i]->label 		= __('Button','spl');
		$t[$i]->options = array();		
		
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> $id.'_font',
			'selector'	=> implode(',',array(
				$selector
			)),			
			'labels'	=> (object)array(
				'family'	=> __('Font','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));		
		
		$t[$i]->options = $this->add_backgroud_options( $t[$i]->options, array(
			'label'		=> __('Background','rhc'),
			'prefix'	=> $id.'-bg_',
			'selector'	=> implode(',',array(
				$selector
			))
		));		
		
		$t[$i]->options = $this->add_border_options($t[$i]->options,array(
			'prefix'	=> $id.'-border_',
			'selector'	=> implode(',',array(
				$selector
			))
		));		

		$t[$i]->options = $this->add_border_radius_options($t[$i]->options,array(
			'prefix'	=> $id.'-rad_',
			'selector'	=> implode(',',array(
				$selector
			))
		));							
		//----- BUTTON HOVER
		$id = 'spl_wp_login_btn_h';
		$selector = '.login.wp-core-ui .button-primary:hover';
				
		$i = count($t);
		$t[$i]=(object)array();
		$t[$i]->id 			= $id; 
		$t[$i]->label 		= __('Button Hover','spl');
		$t[$i]->options = array();		
		
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> $id.'_font',
			'selector'	=> implode(',',array(
				$selector
			)),			
			'labels'	=> (object)array(
				'family'	=> __('Font','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));		
		
		$t[$i]->options = $this->add_backgroud_options( $t[$i]->options, array(
			'label'		=> __('Background','rhc'),
			'prefix'	=> $id.'-bg_',
			'selector'	=> implode(',',array(
				$selector
			))
		));		
		
		$t[$i]->options = $this->add_border_options($t[$i]->options,array(
			'prefix'	=> $id.'-border_',
			'selector'	=> implode(',',array(
				$selector
			))
		));		

		$t[$i]->options = $this->add_border_radius_options($t[$i]->options,array(
			'prefix'	=> $id.'-rad_',
			'selector'	=> implode(',',array(
				$selector
			))
		));		
		
		//----- NAVE
		$id = 'spl_wp_login_nav';
		$selector = implode(',',array(
				'.login.wp-core-ui #nav a',
				'.login.wp-core-ui #backtoblog a'
			));
			
		$i = count($t);
		$t[$i]=(object)array();
		$t[$i]->id 			= $id; 
		$t[$i]->label 		= __('Bottom Links','spl');
		$t[$i]->options = array();	

		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> $id.'_font',
			'selector'	=> $selector,			
			'labels'	=> (object)array(
				'family'	=> __('Font','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));		

		$selector = implode(',',array(
				'.login.wp-core-ui #nav a:hover',
				'.login.wp-core-ui #backtoblog a:hover'
			));		
		
		$t[$i]->options = $this->add_font_options( $t[$i]->options, array(
			'prefix'	=> $id.'_font_h',
			'selector'	=> $selector,			
			'labels'	=> (object)array(
				'family'	=> __('Font hover','rhc'),
				'size'		=> __('Size','rhc'),
				'color'		=> __('Color','rhc')				
			)
		));		
		
		$t[$i]->options[] = (object)array(
			'input_type'  	=> 'raw_html',
			'html'			=> '<div style="height:145px;display:block;"></div>'
		);		
		//-- Saved and DC  -----------------------		
		$i = count($t);
		$t[$i]=(object)array();
		$t[$i]->id 			= 'rh-saved-list'; 
		$t[$i]->label 		= __('Templates','spl');
		$t[$i]->options = array(
			(object)array(
				'id'				=> 'rh_saved_settings',
				'input_type'		=> 'backup_list'
			)			
		);			
//----------------------------------------------------------------------
		return $t;
	}
	
	function footer(){

	}
}
?>