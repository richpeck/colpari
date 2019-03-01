<?php
/**
 * Implements the options in the new CUSTOM CSS options tab.
 * Options are declared in the settings-misc.php file.
 *
 * @package   Productivity
 * @author    Awesome Support <contact@awesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2017 Awesome Support
 */

class WPAS_PF_Custom_CSS {
	
	protected static $instance = null;
	
	public function __construct() {
		
		if (true === boolval( wpas_get_option( 'pf_use_custom_css', false ) ) ) {

			add_action( 'wpas_submission_form_inside_before_submit', array( $this,'wpas_pf_content_before_submit_button' ), 10, 1 ); 		// Output CSS content before submit button on the submission page
			add_action( 'wpas_before_template', 					 array( $this,'wpas_pf_before_front_end_list_template' ), 10, 3 ); 		// Output CSS content before the ticket list on the front end
			add_action( 'admin_head', 					 			 array( $this,'wpas_pf_before_admin_ticket_list' ), 10 ); 				// Output CSS content before the ticket list on the backend
			add_action( 'wpas_backend_ticket_content_before',		 array( $this,'wpas_pf_content_before_admin_ticket_detail' ), 10, 1 ); 	// Output CSS content before the detail ticket screen in admin
			add_action( 'wpas_before_login_form',				 	 array( $this,'wpas_pf_before_login' ), 10, 1 ); 						// Output CSS content before the login/registration form
		}
		
		
	}
	
	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	/**
	*
	* Output custom CSS before the submit button on the front-end submission form
	* 	
	* Action Hook: wpas_submission_form_inside_before_submit
	* 
	*/
	function wpas_pf_content_before_submit_button () {

		//Opening style tag...
		$output_css = $this->wpas_pf_opening_style_tag() ;
		
		
		// Get the option value containing the text to output
		$output_css .= wpas_get_option( 'pf_custom_css_submit_ticket' ) ;
		
		// Closing style tag
		$output_css .= $this->wpas_pf_closing_style_tag();
		
		// Output the css....
		echo ($output_css) ;
		
	}
	
	/**
	*
	* Output custom css for the front-end ticket list
	* 	
	* Action Hook: wpas_before_template
	*
	* @param string $name - template name 
	* @param string $template - template file name
	* @param array $args - arguments
	* 	 
	*/
	function wpas_pf_before_front_end_list_template ( $name, $template, $args ) {
		
			//Opening style tag...
			$output_css = $this->wpas_pf_opening_style_tag() ;
			
			
			// Get the option value containing the text to output
			$output_css .= wpas_get_option( 'pf_custom_css_front_end_ticket_list' ) ;
			
			// Closing style tag
			$output_css .= $this->wpas_pf_closing_style_tag();
			
			// Output the css....
			echo ($output_css) ;

	}	
	
	/**
	*
	* Output custom CSS before the ticket list in admin...
	* 	
	* Action Hook: admin_head
	* 
	*/
	function wpas_pf_before_admin_ticket_list () {
		
		if ( is_admin() && wpas_is_plugin_page() ) {

			//Opening style tag...
			$output_css = $this->wpas_pf_opening_style_tag() ;
			
			
			// Get the option value containing the text to output
			$output_css .= wpas_get_option( 'pf_custom_css_back_end_ticket_list' ) ;
			
			// Closing style tag
			$output_css .= $this->wpas_pf_closing_style_tag();
			
			// Output the css....
			echo ($output_css) ;

		}
			
	}
	
	/**
	*
	* Output custom CSS before the ticket detail screen in admin...
	* 	
	* Action Hook: wpas_submission_form_inside_before_subject
	* 
	*/
	function wpas_pf_content_before_admin_ticket_detail() {
		
			//Opening style tag...
			$output_css = $this->wpas_pf_opening_style_tag() ;
			
			
			// Get the option value containing the text to output
			$output_css .= wpas_get_option( 'pf_custom_css_back_end_ticket_detail' ) ;
			
			// Closing style tag
			$output_css .= $this->wpas_pf_closing_style_tag();
			
			// Output the css....
			echo ($output_css) ;		
		
	}
	
	/**
	*
	* Output custom CSS before the login or registration form...
	* 	
	* Action Hook: wpas_before_login_form
	* 
	*/
	function wpas_pf_before_login() {
		
			//Opening style tag...
			$output_css = $this->wpas_pf_opening_style_tag() ;
			
			
			// Get the option value containing the text to output
			$output_css .= wpas_get_option( 'pf_custom_css_registration_screen' ) ;
			
			// Closing style tag
			$output_css .= $this->wpas_pf_closing_style_tag();
			
			// Output the css....
			echo ($output_css) ;		
		
	}
	
	
	/**
	* return the open tag for the style block that will be ouput as part of the custom css...
	*/
	function wpas_pf_opening_style_tag() {
		return '<style type="text/css">' ; 
	}
	
	/**
	* return the close tag for the style block that will be ouput as part of the custom css...
	*/
	function wpas_pf_closing_style_tag() {
		return '</style>'; ; 
	}
		
	

				
}