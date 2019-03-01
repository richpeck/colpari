<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Color Link
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSFramework_Option_color_link extends CSFramework_Options {

	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function output() {

		echo $this->element_before();

		$options = array(
			'regular'		=> ( empty($this->field['options']['regular']) ) ? false : true,
			'hover'		    => ( empty($this->field['options']['hover']) ) ? false : true,
			'visited'	    => ( empty($this->field['options']['visited']) ) ? false : true,
            'active'		=> ( empty($this->field['options']['active']) ) ? false : true,
            'focus'         => ( empty($this->field['options']['focus']) ) ? false : true,
		);

		$defaults_value = array(
			'regular'		=> '',
			'hover'		    => '',
			'visited'	    => '',
            'active'		=> '',
            'focus'         => '',
		);

		$value			= wp_parse_args( $this->element_value(), $defaults_value );
		$value_regular	= $value['regular'];
		$value_hover	= $value['hover'];
		$value_visited	= $value['visited'];
        $value_active	= $value['active'];
        $value_focus	= $value['focus'];

		echo '<div class="cs-link_color cs-multifield">';

		if ($options['regular'] === true) {
			echo cs_add_element( array(
				'pseudo'		=> true,
				'id'			=> $this->field['id'].'_color_regular',
				'type'			=> 'color_picker',
				'name'			=> $this->element_name('[regular]'),
				'attributes'	=> array(
					'data-atts'		=> 'bgcolor',
				),
				'value'			=> $this->value['regular'],
				'default'		=> ( isset( $this->field['default']['regular'] ) ) ? $this->field['default']['regular'] : '',
				'rgba'			=> ( isset( $this->field['rgba'] ) && $this->field['rgba'] === false ) ? false : '',
				'before'		=> '<label>'.__('Regular').'</label>',
			) );
		}
        if ($options['hover'] === true) {
			echo cs_add_element( array(
				'pseudo'		=> true,
				'id'			=> $this->field['id'].'_color_hover',
				'type'			=> 'color_picker',
				'name'			=> $this->element_name('[hover]'),
				'attributes'	=> array(
					'data-atts'		=> 'bgcolor',
				),
				'value'			=> $this->value['hover'],
				'default'		=> ( isset( $this->field['default']['hover'] ) ) ? $this->field['default']['hover'] : '',
				'rgba'			=> ( isset( $this->field['rgba'] ) && $this->field['rgba'] === false ) ? false : '',
				'before'		=> '<label>'.__('Hover').'</label>',
			) );
        }
        if ($options['visited'] === true) {
			echo cs_add_element( array(
				'pseudo'		=> true,
				'id'			=> $this->field['id'].'_color_visited',
				'type'			=> 'color_picker',
				'name'			=> $this->element_name('[visited]'),
				'attributes'	=> array(
					'data-atts'		=> 'bgcolor',
				),
				'value'			=> $this->value['visited'],
				'default'		=> ( isset( $this->field['default']['visited'] ) ) ? $this->field['default']['visited'] : '',
				'rgba'			=> ( isset( $this->field['rgba'] ) && $this->field['rgba'] === false ) ? false : '',
				'before'		=> '<label>'.__('Visited').'</label>',
			) );
		}
        if ($options['active'] === true) {
			echo cs_add_element( array(
				'pseudo'		=> true,
				'id'			=> $this->field['id'].'_color_active',
				'type'			=> 'color_picker',
				'name'			=> $this->element_name('[active]'),
				'attributes'	=> array(
					'data-atts'		=> 'bgcolor',
				),
				'value'			=> $this->value['active'],
				'default'		=> ( isset( $this->field['default']['active'] ) ) ? $this->field['default']['active'] : '',
				'rgba'			=> ( isset( $this->field['rgba'] ) && $this->field['rgba'] === false ) ? false : '',
				'before'		=> '<label>'.__('Active').'</label>',
			) );
        }
        if ($options['focus'] === true) {
			echo cs_add_element( array(
				'pseudo'		=> true,
				'id'			=> $this->field['id'].'_color_focus',
				'type'			=> 'color_picker',
				'name'			=> $this->element_name('[focus]'),
				'attributes'	=> array(
					'data-atts'		=> 'bgcolor',
				),
				'value'			=> $this->value['focus'],
				'default'		=> ( isset( $this->field['default']['focus'] ) ) ? $this->field['default']['focus'] : '',
				'rgba'			=> ( isset( $this->field['rgba'] ) && $this->field['rgba'] === false ) ? false : '',
				'before'		=> '<label>'.__('Focus').'</label>',
			) );
		}

		echo '</div>';
		echo $this->element_after();

	}

}