<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class google_web_fonts_frontend {
	var $option_varname = 'enabled_google_fonts';
	function __construct(){
		add_action('wp_head',array(&$this,'wp_head'));
	}
	function wp_head(){
		$enabled_fonts = get_option( $this->option_varname );
		
		echo "<pre>";
		print_r($enabled_fonts);
		echo "</pre>";
		die();
	}
}
?>