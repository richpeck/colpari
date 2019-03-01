<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Box Shadow
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSFramework_Option_box_shadow extends CSFramework_Options {

	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function output() {

		echo $this->element_before();
		// box-shadow: none|h-shadow v-shadow blur spread color |inset|initial|inherit;
		$options = array(
			'hshadow'		=> ( isset($this->field['options']['hshadow'])) ? false : true,
			'vshadow'		=> ( isset($this->field['options']['vshadow'])) ? false : true,
			'blur'			=> ( isset($this->field['options']['blur'])) ? false : true,
			'spread'		=> ( isset($this->field['options']['spread'])) ? false : true,
			'color'			=> ( isset($this->field['options']['color'])) ? false : true,
			'type'			=> ( isset($this->field['options']['type'])) ? false : true,
			'unit'			=> ( empty($this->field['options']['unit'])) ? true : '',
		);

		$defaults_value = array(
			'hshadow'		=> '',
			'vshadow'		=> '',
			'blur'			=> '',
			'spread'		=> '',
			'color'			=> '',
			'type'			=> '',
			'unit'			=> '',
		);

		$value				= wp_parse_args( $this->element_value(), $defaults_value );
		$value_hshadow		= $value['hshadow'];
		$value_vshadow		= $value['vshadow'];
		$value_blur			= $value['blur'];
		$value_spread		= $value['spread'];
		$value_color		= $value['color'];
		$value_type			= $value['type'];
		$value_unit			= $value['unit'];
		$is_chosen			= ( isset( $this->field['chosen'] ) && $this->field['chosen'] === false ) ? '' : 'chosen ';
		$chosen_rtl			= ( is_rtl() && ! empty( $is_chosen ) ) ? 'chosen-rtl ' : '';

		echo '<div class="cs-box_shadow cs-multifield">';

		if ($options['hshadow'] === true) {
			echo cs_add_element( array(
				'pseudo'	=> true,
				'type'		=> 'text_addon',
				'name'		=> $this->element_name('[hshadow]'),
				'options'	=> array(
					'type'			=> 'append',
					'addon_value'	=> $value_unit,
				),
				'value'		=> $value_hshadow,
				'attributes' => [
					'placeholder' => 'hor'
				],
				'before'	=> '<label>'.__('X Offset').'</label>',
			) );
		}
		if ($options['vshadow'] === true) {
			echo cs_add_element( array(
				'pseudo'	=> true,
				'type'		=> 'text_addon',
				'name'		=> $this->element_name('[vshadow]'),
				'options'	=> array(
					'type'			=> 'append',
					'addon_value'	=> $value_unit,
				),
				'value'		=> $value_vshadow,
				'attributes' => [
					'placeholder' => 'vert'
				],
				'before'	=> '<label>'.__('Y Offset').'</label>',
			) );
		}
		if ($options['blur'] === true) {
			echo cs_add_element( array(
				'pseudo'	=> true,
				'type'		=> 'text_addon',
				'name'		=> $this->element_name('[blur]'),
				'options'	=> array(
					'type'			=> 'append',
					'addon_value'	=> $value_unit,
				),
				'value'		=> $value_blur,
				'attributes' => [
					'placeholder' => 'blur'
				],
				'before'	=> '<label>'.__('Blur').'</label>',
			) );
		}
		if ($options['spread'] === true) {
			echo cs_add_element( array(
				'pseudo'	=> true,
				'type'		=> 'text_addon',
				'name'		=> $this->element_name('[spread]'),
				'options'	=> array(
					'type'			=> 'append',
					'addon_value'	=> $value_unit,
				),
				'value'		=> $value_spread,
				'attributes' => [
					'placeholder' => 'spread'
				],
				'before'	=> '<label>'.__('Spread').'</label>',
			) );
		}
		if ($options['color'] === true) {
			echo cs_add_element( array(
				'pseudo'		=> true,
				'id'			=> $this->field['id'].'_color',
				'type'			=> 'color_picker',
				'name'			=> $this->element_name('[color]'),
				'attributes'	=> array(
					'data-atts'		=> 'boxshadowcolor',
				),
				'value'			=> $this->value['color'],
				'default'		=> ( isset( $this->field['default']['color'] ) ) ? $this->field['default']['color'] : '',
				'rgba'			=> ( isset( $this->field['rgba'] ) && $this->field['rgba'] === false ) ? false : '',
				'before'	=> '<label>'.__('Color').'</label>',
			) );
		}
		if ($options['type'] === true) {
			echo cs_add_element( array(
				'pseudo'	=> true,
				'type'		=> 'select',
				'name'		=> $this->element_name('[type]'),
				'options'	=> array(
					'initial'	=> 'initial',
					'inherit'	=> 'inherit',
					'inset'		=> 'inset',
				),
				'value'		=> $value_type,
				'before'	=> '<label>'.__('Type').'</label>',
			) );
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
				'before'	=> '<label>'.__('Unit').'</label>',
			) );
		}

		echo '</div>';
		echo $this->element_after();

	}

}