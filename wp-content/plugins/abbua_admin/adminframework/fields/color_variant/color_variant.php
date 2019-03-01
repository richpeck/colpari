<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Color Variant
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSFramework_Option_color_variant extends CSFramework_Options {

	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function output() {

		echo $this->element_before();

		$options = array(
			'darker'    => ( empty($this->field['options']['darker']) ) ? false : true,
			'dark'      => ( empty($this->field['options']['dark']) ) ? false : true,
			'normal'    => ( empty($this->field['options']['normal']) ) ? false : true,
            'light'     => ( empty($this->field['options']['light']) ) ? false : true,
			'lighter'   => ( empty($this->field['options']['lighter']) ) ? false : true,
			'palettes'  => ( empty($this->field['options']['palettes']) ) ? false : true,
		);

		$defaults_value = array(
			'darker'    => '',
			'dark'      => '',
			'normal'    => '',
            'light'     => '',
			'lighter'   => '',
		);

		$value			= wp_parse_args( $this->element_value(), $defaults_value );
		$value_darker	= $value['darker'];
		$value_dark	    = $value['dark'];
		$value_normal	= $value['normal'];
        $value_light	= $value['light'];
		$value_lighter	= $value['lighter'];

		echo '<div class="cs-link_variant cs-multifield">';

		if ($options['darker'] === true) {
			echo cs_add_element( array(
				'pseudo'		=> true,
				'id'			=> $this->field['id'].'_color_darker',
				'type'			=> 'color_picker',
				'name'			=> $this->element_name('[darker]'),
				'attributes'	=> array(
					'data-atts'		=> 'bgcolor',
				),
				'value'			=> $this->value['darker'],
				'default'		=> ( isset( $this->field['default']['darker'] ) ) ? $this->field['default']['darker'] : '',
				'rgba'			=> ( isset( $this->field['rgba'] ) && $this->field['rgba'] === false ) ? false : '',
				'palettes'		=> ( isset( $this->field['options']['palettes'] ) ) ? $this->field['options']['palettes'] : false,
				'before'		=> '<label>'.__('Darker').'</label>',
			) );
		}
        if ($options['dark'] === true) {
			echo cs_add_element( array(
				'pseudo'		=> true,
				'id'			=> $this->field['id'].'_color_dark',
				'type'			=> 'color_picker',
				'name'			=> $this->element_name('[dark]'),
				'attributes'	=> array(
					'data-atts'		=> 'bgcolor',
				),
				'value'			=> $this->value['dark'],
				'default'		=> ( isset( $this->field['default']['dark'] ) ) ? $this->field['default']['dark'] : '',
				'rgba'			=> ( isset( $this->field['rgba'] ) && $this->field['rgba'] === false ) ? false : '',
				'palettes'		=> ( isset( $this->field['options']['palettes'] ) ) ? $this->field['options']['palettes'] : false,
				'before'		=> '<label>'.__('Dark').'</label>',
			) );
        }
        if ($options['normal'] === true) {
			echo cs_add_element( array(
				'pseudo'		=> true,
				'id'			=> $this->field['id'].'_color_normal',
				'type'			=> 'color_picker',
				'name'			=> $this->element_name('[normal]'),
				'attributes'	=> array(
					'data-atts'		=> 'bgcolor',
				),
				'value'			=> $this->value['normal'],
				'default'		=> ( isset( $this->field['default']['normal'] ) ) ? $this->field['default']['normal'] : '',
				'rgba'			=> ( isset( $this->field['rgba'] ) && $this->field['rgba'] === false ) ? false : '',
				'palettes'		=> ( isset( $this->field['options']['palettes'] ) ) ? $this->field['options']['palettes'] : false,
				'before'		=> '<label>'.__('Normal').'</label>',
			) );
		}
        if ($options['light'] === true) {
			echo cs_add_element( array(
				'pseudo'		=> true,
				'id'			=> $this->field['id'].'_color_light',
				'type'			=> 'color_picker',
				'name'			=> $this->element_name('[light]'),
				'attributes'	=> array(
					'data-atts'		=> 'bgcolor',
				),
				'value'			=> $this->value['light'],
				'default'		=> ( isset( $this->field['default']['light'] ) ) ? $this->field['default']['light'] : '',
				'rgba'			=> ( isset( $this->field['rgba'] ) && $this->field['rgba'] === false ) ? false : '',
				'palettes'		=> ( isset( $this->field['options']['palettes'] ) ) ? $this->field['options']['palettes'] : false,
				'before'		=> '<label>'.__('Light').'</label>',
			) );
        }
        if ($options['lighter'] === true) {
			echo cs_add_element( array(
				'pseudo'		=> true,
				'id'			=> $this->field['id'].'_color_lighter',
				'type'			=> 'color_picker',
				'name'			=> $this->element_name('[lighter]'),
				'attributes'	=> array(
					'data-atts'		=> 'bgcolor',
				),
				'value'			=> $this->value['lighter'],
				'default'		=> ( isset( $this->field['default']['lighter'] ) ) ? $this->field['default']['lighter'] : '',
				'rgba'			=> ( isset( $this->field['rgba'] ) && $this->field['rgba'] === false ) ? false : '',
				'palettes'		=> ( isset( $this->field['options']['palettes'] ) ) ? $this->field['options']['palettes'] : false,
				'before'		=> '<label>'.__('Lighter').'</label>',
			) );
		}

		echo '</div>';
		echo $this->element_after();

	}

}