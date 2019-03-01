<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Angle
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSFramework_Option_angle extends CSFramework_Options {

	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function output() {

		// $defaults_value = array(
		// 	'slider1'	=> '0',
		// 	'slider2'	=> '0',
		// );
		// $this->value 	= wp_parse_args( $this->element_value(), $defaults_value );
		// $value_slider1 	= $value['slider1'];
		// $value_slider2 	= $value['slider2'];

        // distance: 1,
        // delay: 1,
        // snap: 1,
        // min: 0,
        // shiftSnap: 15,
        // value: 90,
        // clockwise: true
		$options = array(
			'distance'  	=> ( empty( $this->field['options']['distance'] ) ) ? 1 : $this->field['options']['distance'],
            'delay'  	    => ( empty( $this->field['options']['delay'] ) ) ? 1 : $this->field['options']['delay'],
            'snap'  	    => ( empty( $this->field['options']['snap'] ) ) ? 1 : $this->field['options']['snap'],
			'min'   	    => ( empty( $this->field['options']['min'] ) ) ? 0 : $this->field['options']['min'],
			'shiftSnap'   	=> ( empty( $this->field['options']['shiftSnap'] ) ) ? 15 : $this->field['options']['shiftSnap'],
			'clockwise' 	=> ( empty( $this->field['options']['clockwise'] ) ) ? false : true,
			'value'	        => $this->value,
		);

		$input_type 	= ($options['input']) ? 'text' : 'hidden';

		echo $this->element_before();
		echo '<div class="cs-anglepicker" data-angle-options=\'' . json_encode( $options ) . '\'>';
        
        echo '
        <div class="cs-field-text_addon"><div class="cs-input-addon-field cs-input-append">
        <input type="text" name="'. $this->element_name() .'" value="'. $this->element_value() .'"'. $this->element_class() . $this->element_attributes() .'/>
        <span class="cs-input-addon">deg</span></div></div>
        ';

        echo '<div class="cs-anglepicker-wrapper"><div class="anglepicker"></div></div>';

		echo '</div>';

		echo $this->element_after();

	}

}