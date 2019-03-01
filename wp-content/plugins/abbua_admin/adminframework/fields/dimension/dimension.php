<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Dimension
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSFramework_Option_dimension extends CSFramework_Options {

	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function output() {

		$options = array(
			'all'		=> ( isset($this->field['options']['all'])) ? true : '',
			'width'		=> ( $this->field['options']['width'] === false ) ? false : true,
			'height'	=> ( $this->field['options']['height'] === false ) ? false : true,
			'unit'		=> ( $this->field['options']['unit'] === false ) ? false : true,
		);

		$defaults_value = array(
			'all'		=> '',
			'width'		=> '',
			'height'	=> '',
			'unit'		=> ''
		);

		$value 			= wp_parse_args( $this->element_value(), $defaults_value );
		$value_all		= $value['all'];
		$value_width	= $value['width'];
		$value_height	= $value['height'];
		$value_unit 	= $value['unit'];
		$is_chosen		= ( isset( $this->field['chosen'] ) && $this->field['chosen'] === false ) ? '' : 'chosen ';
		$chosen_rtl		= ( is_rtl() && ! empty( $is_chosen ) ) ? 'chosen-rtl ' : '';

		echo $this->element_before();
		echo '<div class="cs-dimension cs-multifield">';

		if ($options['all'] === true) {
			echo cs_add_element( array(
				'pseudo'	=> true,
				'type'		=> 'text_addon',
				'name'		=> $this->element_name('[all]'),
				'options'	=> array(
					'addon_value'	=> '<i class="fa fa-arrows"></i>',
				),
				'value'		=> $value_all,
				'attributes' => [
					'placeholder' => 'all'
				]
			) );
		} else {
			if ($options['width'] === true) {
				echo cs_add_element( array(
					'pseudo'	=> true,
					'type'		=> 'text_addon',
					'name'		=> $this->element_name('[width]'),
					'options'	=> array(
						'addon_value'	=> '<i class="fa fa-arrows-h"></i>',
					),
					'value'		=> $value_width,
					'attributes' => [
						'placeholder' => 'width'
					]
				) );
			}
			if ($options['height'] === true) {
				echo cs_add_element( array(
					'pseudo'	=> true,
					'type'		=> 'text_addon',
					'name'		=> $this->element_name('[height]'),
					'options'	=> array(
						'addon_value'	=> '<i class="fa fa-arrows-v"></i>',
					),
					'value'		=> $value_height,
					'attributes' => [
						'placeholder' => 'height'
					]
				) );
			}
		}
		if ($options['unit'] === true) {
			echo cs_add_element( array(
				'pseudo'	=> true,
				'type'		=> 'select',
				'name'		=> $this->element_name('[unit]'),
				'options'	=> array(
					'em'	=> 'em',
					'px'	=> 'px',
					'%'		=> '%',
				),
				'value'		=> $value_unit,
			) );
		}

		echo '</div>';
		echo $this->element_after();
	}

}




// Add 
add_action( 'cs_enqueque_fields_scripts', 'cs_enqueue_scripts');

function cs_enqueue_scripts(){
	// wp_enqueue_script( 'cs-framework',  CS_URI .'/laruta/js/cs-dimension.js',  array( 'cs-plugins' ), '1.0.0', true );
}