<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * New titan settings field class for multiple facebook chat bot keywords
 */
class TitanFrameworkOptionCustomTextarea extends TitanFrameworkOptionTextarea {
        
    
   /**
	 * Clean value before saving
	 * 
	 * @param array $value
	 * 
	 * @return array
	 */
	public function cleanValueForSaving( $value ) {
		
		$value = stripslashes_deep( $value );
		
		
		return $value;
	}
	
	public function cleanValueForGetting( $value ) {
		
		return $value;
	}
	
	/*
	 * Display for options and meta
	 */
	public function display() {
		$this->echoOptionHeader( true );
		printf("<textarea class='large-text %s' name=\"%s\" placeholder=\"%s\" id=\"%s\" rows='10' cols='50'>%s</textarea>",
			$this->settings['is_code'] ? 'code' : '',
			$this->getID(),
			$this->settings['placeholder'],
			$this->getID(),
			esc_textarea( $this->getValue() )
		);
		$this->echoOptionFooter( false );
	}
    
}