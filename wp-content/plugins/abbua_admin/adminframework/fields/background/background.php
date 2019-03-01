<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Background
 *
 * @since 1.0.0
 * @version 1.0.1
 *
 */
class CSFramework_Option_background extends CSFramework_Options {

  public function __construct( $field, $value = '', $unique = '' ) {
    parent::__construct( $field, $value, $unique );
  }

  public function output() {

    echo $this->element_before();

    $options = array(
			'repeat'		  => ( $this->field['options']['repeat'] === false ) ? false : true,
			'position'		=> ( $this->field['options']['position'] === false ) ? false : true,
			'attachment'	=> ( $this->field['options']['attachment'] === false ) ? false : true,
			'size'		    => ( $this->field['options']['size'] === false ) ? false : true,
			'color'		    => ( !isset($this->field['options']['color']) || $this->field['options']['color'] === false ) ? false : true,
		);

    $value_defaults = array(
      'image'       => '',
      'repeat'      => '',
      'position'    => '',
      'attachment'  => '',
      'size'        => '',
      'color'       => '',
    );

    $this->value  = wp_parse_args( $this->element_value(), $value_defaults );

    if( isset( $this->field['settings'] ) ) { extract( $this->field['settings'] ); }
    $upload_type  = ( isset( $upload_type  ) ) ? $upload_type  : 'image';
    $button_title = ( isset( $button_title ) ) ? $button_title : __( 'Upload', 'cs-framework' );
    $frame_title  = ( isset( $frame_title  ) ) ? $frame_title  : __( 'Upload', 'cs-framework' );
    $insert_title = ( isset( $insert_title ) ) ? $insert_title : __( 'Use Image', 'cs-framework' );

    $preview = '';
    $value   = $this->value['image'];
    $add     = ( ! empty( $this->field['add_title'] ) ) ? $this->field['add_title'] : __( 'Add Image', 'cs-framework' );
    $hidden  = ( empty( $value ) ) ? ' hidden' : '';
    
    if( ! empty( $value ) ) {
      $attachment = wp_get_attachment_image_src( $value, 'thumbnail' );
      $preview    = $attachment[0];
    }

    echo '<div class="cs-image-preview'. $hidden .'"><div class="cs-preview"><i class="fa fa-times cs-remove"></i><img src="'. $preview .'" alt="preview" /></div></div>';
    
    echo '<div class="cs-field-upload-form">';
    echo '<input type="text" name="'. $this->element_name( '[image]' ) .'" value="'. $this->value['image'] .'"'. $this->element_class() . $this->element_attributes() .'/>';
    echo '<a href="#" class="button button-primary cs-add" data-frame-title="'. $frame_title .'" data-upload-type="'. $upload_type .'" data-insert-title="'. $insert_title .'">'. $button_title .'</a>';
    echo '</div>';

    // background attributes
    echo '<fieldset>';
    if ($options['repeat'] === true){
      echo cs_add_element( array(
          'pseudo'          => true,
          'type'            => 'select',
          'name'            => $this->element_name( '[repeat]' ),
          'options'         => array(
            ''              => 'repeat',
            'repeat-x'      => 'repeat-x',
            'repeat-y'      => 'repeat-y',
            'no-repeat'     => 'no-repeat',
            'inherit'       => 'inherit',
          ),
          'attributes'      => array(
            'data-atts'     => 'repeat',
          ),
          'value'           => $this->value['repeat']
      ) );
    }
    if ($options['position'] === true){
      echo cs_add_element( array(
          'pseudo'          => true,
          'type'            => 'select',
          'name'            => $this->element_name( '[position]' ),
          'options'         => array(
            ''              => 'left top',
            'left center'   => 'left center',
            'left bottom'   => 'left bottom',
            'right top'     => 'right top',
            'right center'  => 'right center',
            'right bottom'  => 'right bottom',
            'center top'    => 'center top',
            'center center' => 'center center',
            'center bottom' => 'center bottom'
          ),
          'attributes'      => array(
            'data-atts'     => 'position',
          ),
          'value'           => $this->value['position']
      ) );
    }
    if ($options['attachment'] === true){
      echo cs_add_element( array(
          'pseudo'          => true,
          'type'            => 'select',
          'name'            => $this->element_name( '[attachment]' ),
          'options'         => array(
            ''              => 'scroll',
            'fixed'         => 'fixed',
          ),
          'attributes'      => array(
            'data-atts'     => 'attachment',
          ),
          'value'           => $this->value['attachment']
      ) );
    }
    if ($options['size'] === true){
      echo cs_add_element( array(
          'pseudo'          => true,
          'type'            => 'select',
          'name'            => $this->element_name( '[size]' ),
          'options'         => array(
            ''              => 'size',
            'cover'         => 'cover',
            'contain'       => 'contain',
            'inherit'       => 'inherit',
            'initial'       => 'initial',
          ),
          'attributes'      => array(
            'data-atts'     => 'size',
          ),
          'value'           => $this->value['size']
      ) );
    }
    if ($options['color'] === true){
      echo cs_add_element( array(
          'pseudo'          => true,
          'id'              => $this->field['id'].'_color',
          'type'            => 'color_picker',
          'name'            => $this->element_name('[color]'),
          'attributes'      => array(
            'data-atts'     => 'bgcolor',
          ),
          'value'           => $this->value['color'],
          'default'         => ( isset( $this->field['default']['color'] ) ) ? $this->field['default']['color'] : '',
          'rgba'            => ( isset( $this->field['rgba'] ) && $this->field['rgba'] === false ) ? false : '',
      ) );
    }
    echo '</fieldset>';

    echo $this->element_after();

  }
}
