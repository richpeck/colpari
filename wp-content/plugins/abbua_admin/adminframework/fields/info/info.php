<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Info
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSFramework_Option_info extends CSFramework_Options {

  public function __construct( $field, $value = '', $unique = '' ) {
    parent::__construct( $field, $value, $unique );
  }

  public function output() {

  	$title 		= $this->field['title'];
  	$content 	= $this->field['content'];

  	$options 	= ( isset($this->field['options']) ) ? $this->field['options'] : false;
  	$icon 		= ( isset($options['icon']) ) ? $options['icon'] : false;
  	$type 		= ( isset($options['type']) ) ? $options['type'] : 'notice';
  	$style 		= ( isset($options['style']) ) ? $options['style'] : 'success';

    echo $this->element_before();
    echo '<div class="cs-field-info--type_'.$type.' cs-field-info--style_'.$style.'">';
    if ($icon){
    	echo '<div class="cs-field-info__icon">';
    	echo '<i class="'.$icon.'"></i>';
    	echo '</div>';	
    }
    echo '<div class="cs-field-info__content">';
    if ($title) {
    	echo '<h4>'.$title.'</h4>';
    }
    echo '<p>'.$content.'</p>';
    echo '</div>';
    echo '</div>';
    echo $this->element_after();

  }

}
