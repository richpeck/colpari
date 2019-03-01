<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Slider
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSFramework_Option_slider extends CSFramework_Options {

	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function output() {

		$defaults_value = array(
			'slider1'	=> '0',
			'slider2'	=> '0',
		);
		$this->value 	= wp_parse_args( $this->element_value(), $defaults_value );
		$value_slider1 	= $value['slider1'];
		$value_slider2 	= $value['slider2'];

		$options = array(
			'step'  	=> ( empty( $this->field['options']['step'] ) ) ? 1 : $this->field['options']['step'],
			'unit'  	=> ( empty( $this->field['options']['unit'] ) ) ? '' : $this->field['options']['unit'],
			'min'   	=> ( empty( $this->field['options']['min'] ) ) ? 0 : $this->field['options']['min'],
			'max'   	=> ( empty( $this->field['options']['max'] ) ) ? 100 : $this->field['options']['max'],
			'round' 	=> ( empty( $this->field['options']['round'] ) ) ? false : true,
			'tooltip'	=> ( empty( $this->field['options']['tooltip'] ) ) ? false : true,
			'input'		=> ( empty( $this->field['options']['input'] ) ) ? false : true,
			'handles'	=> ( empty( $this->field['options']['handles'] ) ) ? false : true,
			'slider1'	=> $this->value['slider1'],
			'slider2'	=> $this->value['slider2'],
		);

		$input_type 	= ($options['input']) ? 'text' : 'hidden';

		echo $this->element_before();
		echo '<div class="cs-slider" data-slider-options=\'' . json_encode( $options ) . '\'>';

		echo cs_add_element( array(
			'pseudo'		=> true,
			'type'			=> 'text_addon',
			'name'			=> $this->element_name('[slider1]'),
			'value'			=> $this->value['slider1'],
			'default'		=> $this->value['slider1'],
			'class'			=> 'cs-slider_handler1',
			'attributes' 	=> [
				'placeholder' 	=> $options['min'],
				'type'			=> $input_type
			],
			'options'		=> [
				'type'			=> 'append',
				'addon_value'	=> $options['unit']
			]
		) );
		
		echo '<div class="cs-slider-wrapper"></div>';
		
		if ($options['handles']) { 

			echo cs_add_element( array(
				'pseudo'		=> true,
				'type'			=> 'text',
				'name'			=> $this->element_name('[slider2]'),
				'value'			=> $this->value['slider2'],
				'default'		=> $this->value['slider2'],
				'class'			=> 'cs-slider_handler2',
				'attributes' 	=> [
					'placeholder' 	=> $options['max'],
					'type'			=> $input_type
				]
			) );
		}
		
		echo '</div>';

		echo $this->element_after();

	}

}