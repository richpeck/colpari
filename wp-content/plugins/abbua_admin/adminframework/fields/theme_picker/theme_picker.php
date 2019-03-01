<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Theme Picker
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSFramework_Option_theme_picker extends CSFramework_Options {

	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function output(){

		$options = array(
			'type'			=> ( empty( $this->field['options']['type'] ) ) ? 'prepend' : $this->field['options']['type'],
			'icon'			=> ( empty( $this->field['options']['icon'] ) ) ? false : $this->field['options']['icon'],
			'addon_value'	=> ( empty( $this->field['options']['addon_value'] ) ) ? '' : $this->field['options']['addon_value'],
		);

		$theme_options = cs_uls_get_theme_list();

		echo $this->element_before();
		echo '<div class="cs-theme_picker cs-multifield">';

		echo '<span class="spinner">Loading theme settings...</span>';

		echo '<div class="cs-theme_picker-wrapper">';
		echo cs_add_element( array(
			'pseudo'	=> true,
			'type'		=> 'select',
			'name'		=> $this->element_name( '[theme]' ),
			'options'	=> $theme_options,
			'value'		=> $value_theme,
			'default_option' => __('Select a theme'),
			'class'		=> 'cs-theme',
		));
		echo cs_add_element( array(
			'pseudo'	=> true,
			'type'		=> 'button',
			'name'		=> $this->element_name('[themeload]'),
			'class'		=> 'cs-theme-load',
			'value'		=> __('Load Theme'),
			'options'	=> array(
				'type'	=> 'primary',
			),
		) );
		echo cs_add_element( array(
			'pseudo'	=> true,
			'type'		=> 'button',
			'name'		=> $this->element_name('[themesave]'),
			'class'		=> 'cs-theme-save',
			'value'		=> __('Save As New Theme'),
		) );
		echo cs_add_element( array(
			'pseudo'	=> true,
			'type'		=> 'button',
			'name'		=> $this->element_name('[themedelete]'),
			'class'		=> 'cs-warning-primary cs-theme-delete',
			'value'		=> '<i class="dashicons dashicons-trash"></i>',
		) );
		echo '</div>';

		echo '</div>';
		echo $this->element_after();

	}

}