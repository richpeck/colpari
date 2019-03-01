<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: CSS Animation Select
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSFramework_Option_animation_select extends CSFramework_Options {

    public function __construct( $field, $value = '', $unique = '' ) {
        parent::__construct( $field, $value, $unique );
    }

    public function output() {

        echo $this->element_before();
        
        $options    = [
            __('Fade FX')		=> [
                'fade_in'			=> __('Fade In'),
                'fade_in--top'		=> __('Fade In Top'),
                'fade_in--bottom'	=> __('Fade In Bottom'),
                'fade_in--left'		=> __('Fade In Left'),
                'fade_in--right'	=> __('Fade In Right'),
                'otro'              => 'otro'
            ],
            // __('Flip FX')			=> [
            //     'flipInX'			=> __('Flip In Horizontal'),
            //     'flipInY'			=> __('Flip In Vertical'),
            // ],
            // __('Rotate FX')			=> [
            //     'rotateIn'			=> __('Rotate In'),
            //     'rotateInDownLeft'	=> __('Rotate In Down-Left'),
            //     'rotateInDownRight'	=> __('Rotate In Down-Right'),
            //     'rotateInUpLeft'	=> __('Rotate In Up-Left'),
            //     'rotateInUpRight'	=> __('Rotate In Up-Right'),
            // ],
            // __('Slide FX')			=> [
            //     'slideIn'			=> __('Slide In'),
            //     'slideInUp'			=> __('Slide In Up'),
            //     'slideInRight'		=> __('Slide In Right'),
            //     'slideInDown'		=> __('Slide In Down'),
            //     'slideInLeft'		=> __('Slide In Left'),
            // ],
            // __('Zoom FX')			=> [
            //     'zoomIn'			=> __('Zoom In'),
            //     'zoomInUp'			=> __('Zoom In Up'),
            //     'zoomInRight'		=> __('Zoom In Right'),
            //     'zoomInDown'		=> __('Zoom In Down'),
            //     'zoomInLeft'		=> __('Zoom In Left'),
            // ]
        ];
        if( isset( $options ) ) {

            $class      = $this->element_class();
            $options    = ( is_array( $options ) ) ? $options : array_filter( $this->element_data( $options ) );
            $extra_name = ( isset( $this->field['attributes']['multiple'] ) ) ? '[]' : '';
            $chosen_rtl = ( is_rtl() && strpos( $class, 'chosen' ) ) ? 'chosen-rtl' : '';

            echo '<select name="'. $this->element_name( $extra_name ) .'"'. $this->element_class( $chosen_rtl ) . $this->element_attributes() .'>';

            echo ( isset( $this->field['default_option'] ) ) ? '<option value="">'.$this->field['default_option'].'</option>' : '';

            if( !empty( $options ) ){
                foreach ( $options as $key => $value ) {
                    if ( is_array($value) ) {
                        echo '<optgroup label="'.$key.'">';
                        foreach ($value as $key => $value) {
                            echo '<option value="'. $key .'" '. $this->checked( $this->element_value(), $key, 'selected' ) .'>'. $value .'</option>';
                        }
                        echo '</optgroup>';
                    } else {
                        echo '<option value="'. $key .'" '. $this->checked( $this->element_value(), $key, 'selected' ) .'>'. $value .'</option>';
                    }
                }
            }
            echo '</select>';
        }
        echo $this->element_after();
    }
}