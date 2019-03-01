<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Border
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSFramework_Option_border extends CSFramework_Options {

	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function output() {

		echo $this->element_before();

		$options = array(
			'all'		=> ( $this->field['options']['all'] === true ) ? true : false,
			'top'		=> ( $this->field['options']['top'] === false ) ? false : true,
			'right'		=> ( $this->field['options']['right'] === false ) ? false : true,
			'bottom'	=> ( $this->field['options']['bottom'] === false ) ? false : true,
			'left'		=> ( $this->field['options']['left'] === false ) ? false : true,
			'style'		=> ( $this->field['options']['style'] === false ) ? false : true,
			'color'		=> ( $this->field['options']['color'] === false ) ? false : true,
		);

		$defaults_value = array(
			'all'		=> '',
			'top'		=> '',
			'right'		=> '',
			'bottom'	=> '',
			'left'		=> '',
			'style'		=> '',
			'color'		=> '',
		);

		$value			= wp_parse_args( $this->element_value(), $defaults_value );
		$value_all		= $value['all'];
		$value_top		= $value['top'];
		$value_right	= $value['right'];
		$value_bottom	= $value['bottom'];
		$value_left		= $value['left'];
		$value_unit		= $value['unit'];
		$value_style 	= $value['style'];
		$is_chosen		= ( isset( $this->field['chosen'] ) && $this->field['chosen'] === false ) ? '' : 'chosen ';
		$chosen_rtl		= ( is_rtl() && ! empty( $is_chosen ) ) ? 'chosen-rtl ' : '';

		echo '<div class="cs-border cs-multifield">';

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
				],
				'before'		=> '<label>'.__('All borders').'</label>',
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
					],
					'before'		=> '<label>'.__('Top Border').'</label>',
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
					],
					'before'		=> '<label>'.__('Right Border').'</label>',
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
					],
					'before'		=> '<label>'.__('Bottom Border').'</label>',
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
					],
					'before'		=> '<label>'.__('Left Border').'</label>',
				) );
			}
		}
		if ($options['style'] === true) {
			echo cs_add_element( array(
				'pseudo'	=> true,
				'type'		=> 'select',
				'name'		=> $this->element_name('[style]'),
				'options'	=> array(
					'none'		=> 'none',
					'solid'		=> 'solid',
					'dashed'	=> 'dashed',
					'dotted'	=> 'dotted',
					'double'	=> 'double',
				),
				'value'		=> $value_style,
				'before'		=> '<label>'.__('Border Style').'</label>',
			) );
		}
		if ($options['color'] === true) {
			echo cs_add_element( array(
				'pseudo'		=> true,
				'id'			=> $this->field['id'].'_color',
				'type'			=> 'color_picker',
				'name'			=> $this->element_name('[color]'),
				'attributes'	=> array(
					'data-atts'		=> 'bgcolor',
				),
				'value'			=> $this->value['color'],
				'default'		=> ( isset( $this->field['default']['color'] ) ) ? $this->field['default']['color'] : '',
				'rgba'			=> ( isset( $this->field['rgba'] ) && $this->field['rgba'] === false ) ? false : '',
				'before'		=> '<label>'.__('Border Color').'</label>',
			) );
		}

		echo '</div>';
		echo $this->element_after();

	}

}