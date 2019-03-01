<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
*
* Field: Button
*
* @since 1.0.0
* @version 1.0.0
*
*/
class CSFramework_Option_button extends CSFramework_Options {

	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function output(){
		$value = ( empty( $this->element_value() ) ) ? __('Button Name') : $this->element_value();

		$options = array(
			'type'	=> ( empty( $this->field['options']['type'] ) ) ? '' : 'button-'.$this->field['options']['type'],
		);

		echo $this->element_before();
		echo '<a href="#" onclick="return false;" name="'. $this->element_name() .'" '. $this->element_class('button '.$options['type']) . $this->element_attributes() .'>'. $value .'</a>';
		echo $this->element_after();

	}

}
