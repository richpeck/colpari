<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Border Radius
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSFramework_Option_border_radius extends CSFramework_Options {

	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function output() {

		echo $this->element_before();

		$options = array(
			'all'			=> ( isset($this->field['options']['all'])) ? true : '',
			'topleft'		=> ( empty($this->field['options']['topleft'])) ? true : '',
			'topright'		=> ( empty($this->field['options']['topright'])) ? true : '',
			'bottomleft'	=> ( empty($this->field['options']['bottomleft'])) ? true : '',
			'bottomright'	=> ( empty($this->field['options']['bottomright'])) ? true : '',
			'unit'			=> ( empty($this->field['options']['unit'])) ? true : '',
		);

		$defaults_value = array(
			'all'			=> '',
			'topleft'		=> '',
			'topright'		=> '',
			'bottomleft'	=> '',
			'bottomright'	=> '',
			'unit'			=> '',
		);

		$value				= wp_parse_args( $this->element_value(), $defaults_value );
		$value_all			= $value['all'];
		$value_topleft		= $value['topleft'];
		$value_topright		= $value['topright'];
		$value_bottomleft	= $value['bottomleft'];
		$value_bottomright	= $value['bottomright'];
		$value_unit		= $value['unit'];
		$is_chosen		= ( isset( $this->field['chosen'] ) && $this->field['chosen'] === false ) ? '' : 'chosen ';
		$chosen_rtl		= ( is_rtl() && ! empty( $is_chosen ) ) ? 'chosen-rtl ' : '';

		echo '<div class="cs-border_radius cs-multifield">';

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

			if ($options['topleft'] === true) {
				echo cs_add_element( array(
					'pseudo'	=> true,
					'type'		=> 'text_addon',
					'name'		=> $this->element_name('[topleft]'),
					'options'	=> array(
						'addon_value'	=> '<i class="im im-arrow-up-left"></i>',
					),
					'value'		=> $value_topleft,
					'attributes' => [
						'placeholder' => 'top left'
					]
				) );
			}
			if ($options['topright'] === true) {
				echo cs_add_element( array(
					'pseudo'	=> true,
					'type'		=> 'text_addon',
					'name'		=> $this->element_name('[topright]'),
					'options'	=> array(
						'addon_value'	=> '<i class="im im-arrow-up-right"></i>',
					),
					'value'		=> $value_topright,
					'attributes' => [
						'placeholder' => 'top right'
					]
				) );
			}
			if ($options['bottomleft'] === true) {
				echo cs_add_element( array(
					'pseudo'	=> true,
					'type'		=> 'text_addon',
					'name'		=> $this->element_name('[bottomleft]'),
					'options'	=> array(
						'addon_value'	=> '<i class="im im-arrow-down-left"></i>',
					),
					'value'		=> $value_bottomleft,
					'attributes' => [
						'placeholder' => 'bottom left'
					]
				) );
			}
			if ($options['bottomright'] === true) {
				echo cs_add_element( array(
					'pseudo'	=> true,
					'type'		=> 'text_addon',
					'name'		=> $this->element_name('[bottomright]'),
					'options'	=> array(
						'addon_value'	=> '<i class="im im-arrow-down-right"></i>',
					),
					'value'		=> $value_bottomright,
					'attributes' => [
						'placeholder' => 'bottom right'
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