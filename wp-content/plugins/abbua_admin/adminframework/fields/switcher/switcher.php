<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Switcher
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSFramework_Option_switcher extends CSFramework_Options {

  public function __construct( $field, $value = '', $unique = '' ) {
    parent::__construct( $field, $value, $unique );
  }

  public function output() {

    echo $this->element_before();
    $label 		= ( isset( $this->field['label'] ) ) ? '<div class="cs-text-desc">'. $this->field['label'] . '</div>' : '';
    $label_on 	= ( isset($this->field['labels']['on']) ) ? $this->field['labels']['on'] : __( 'on', 'cs-framework' );
    $label_off 	= ( isset($this->field['labels']['off']) ) ? $this->field['labels']['off'] : __( 'off', 'cs-framework' );
    echo '<label><input type="checkbox" name="'. $this->element_name() .'" value="1"'. $this->element_class() . $this->element_attributes() . checked( $this->element_value(), 1, false ) .'/><em data-on="'.$label_on.'" data-off="'.$label_off.'"></em><span></span></label>' . $label;
    echo $this->element_after();

  }

}
