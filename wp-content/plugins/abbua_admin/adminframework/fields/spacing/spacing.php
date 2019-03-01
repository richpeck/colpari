<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Spacing
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSFramework_Option_spacing extends CSFramework_Options {

	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function output() {

		$options = array(
			'all'		=> ( isset($this->field['options']['all'])) ? true : '',
			'top'		=> ( empty($this->field['options']['top'])) ? true : '',
			'right'		=> ( empty($this->field['options']['right'])) ? true : '',
			'bottom'	=> ( empty($this->field['options']['bottom'])) ? true : '',
			'left'		=> ( empty($this->field['options']['left'])) ? true : '',
			'unit'		=> ( empty($this->field['options']['unit'])) ? true : '',
		);

		$defaults_value = array(
			'all'		=> '',
			'top'		=> '',
			'right'		=> '',
			'bottom'	=> '',
			'left'		=> '',
			'unit'		=> '',
		);

		$value 			= wp_parse_args( $this->element_value(), $defaults_value );
		$value_all		= $value['all'];
		$value_top		= $value['top'];
		$value_right	= $value['right'];
		$value_bottom	= $value['bottom'];
		$value_left		= $value['left'];
		$value_unit		= $value['unit'];
		$is_chosen		= ( isset( $this->field['chosen'] ) && $this->field['chosen'] === false ) ? '' : 'chosen ';
		$chosen_rtl		= ( is_rtl() && ! empty( $is_chosen ) ) ? 'chosen-rtl ' : '';

		echo $this->element_before();
		echo '<div class="cs-spacing cs-multifield">';

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
			if ($options['top'] === true) {
				echo cs_add_element( array(
					'pseudo'	=> true,
					'type'		=> 'text_addon',
					'name'		=> $this->element_name('[top]'),
					'options'	=> array(
						'addon_value'	=> '<i class="fa fa-long-arrow-up"></i>',
					),
					'value'		=> $value_top,
					'attributes' => [
						'placeholder' => 'top'
					]
				) );
			}
			if ($options['right'] === true) {
				echo cs_add_element( array(
					'pseudo'	=> true,
					'type'		=> 'text_addon',
					'name'		=> $this->element_name('[right]'),
					'options'	=> array(
						'addon_value'	=> '<i class="fa fa-long-arrow-right"></i>',
					),
					'value'		=> $value_right,
					'attributes' => [
						'placeholder' => 'right'
					]
				) );
			}
			if ($options['bottom'] === true) {
				echo cs_add_element( array(
					'pseudo'	=> true,
					'type'		=> 'text_addon',
					'name'		=> $this->element_name('[bottom]'),
					'options'	=> array(
						'addon_value'	=> '<i class="fa fa-long-arrow-down"></i>',
					),
					'value'		=> $value_bottom,
					'attributes' => [
						'placeholder' => 'bottom'
					]
				) );
			}
			if ($options['left'] === true) {
				echo cs_add_element( array(
					'pseudo'	=> true,
					'type'		=> 'text_addon',
					'name'		=> $this->element_name('[left]'),
					'options'	=> array(
						'addon_value'	=> '<i class="fa fa-long-arrow-left"></i>',
					),
					'value'		=> $value_left,
					'attributes' => [
						'placeholder' => 'left'
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