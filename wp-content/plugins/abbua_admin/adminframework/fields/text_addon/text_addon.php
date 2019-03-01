<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Text Addon
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSFramework_Option_text_addon extends CSFramework_Options {

	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function output(){

		$options = array(
			'type'			=> ( empty( $this->field['options']['type'] ) ) ? 'prepend' : $this->field['options']['type'],
			'icon'			=> ( empty( $this->field['options']['icon'] ) ) ? false : $this->field['options']['icon'],
			'addon_value'	=> ( empty( $this->field['options']['addon_value'] ) ) ? '' : $this->field['options']['addon_value'],
		);

		$addon_icon = ($options['icon']) ? 'cs-input-addon-icon' : '';

		echo $this->element_before();

		if ($options['type'] === 'prepend') {
			echo '<div class="cs-input-addon-field cs-input-prepend">';
			echo '<span class="cs-input-addon '.$addon_icon.'">'.$options['addon_value'].'</span>';
			echo '<input type="text" name="'. $this->element_name() .'" value="'. $this->element_value() .'"'. $this->element_class() . $this->element_attributes() .'/>';
			echo '</div>';
		} else if ($options['type'] === 'append') {
			echo '<div class="cs-input-addon-field cs-input-append">';
			echo '<input type="text" name="'. $this->element_name() .'" value="'. $this->element_value() .'"'. $this->element_class() . $this->element_attributes() .'/>';
			echo '<span class="cs-input-addon '.$addon_icon.'">'.$options['addon_value'].'</span>';
			echo '</div>';
		}
		echo $this->element_after();

	}

}