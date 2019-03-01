<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Layout Builder - Details View
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSFramework_Option_builder_detailsview extends CSFramework_Options {

	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function output(){

        $elements = [
            'heading'   => [
                'name'  => __('Full Heading'),
                'slug'  => 'heading',
            ],
            'image'  => [
                'name'  => __('Company Logo'),
                'slug'  => 'image',
            ],
            'about' => [
                'name'  => __('About Info'),
                'slug'  => 'about'
            ],
            'contact' => [
                'name'  => __('Contact Info'),
                'slug'  => 'contact'
            ],
            'gallery' => [
                'name'  => __('Image Gallery'),
                'slug'  => 'gallery'
            ],
            'social' => [
                'name'  => __('Social Links'),
                'slug'  => 'social'
            ]
        ];

        $defaults_value = array(
            'top'	    => '',
            'left'      => '',
            'right'     => '',
            'bottom'    => '',
            'elements'  => array_keys($elements)
		);
        $is_json = function($string) {
            return !empty($string) && is_string($string) && is_array(json_decode($string, true)) && json_last_error() == 0;
        };

		$value			= wp_parse_args( $this->element_value(), $defaults_value );
        $value_top		= json_decode($value['top']);
        $value_left		= json_decode($value['left']);
        $value_right	= json_decode($value['right']);
        $value_bottom	= json_decode($value['bottom']);
        $value_elements = ($is_json($value['elements'])) ? json_decode($value['elements']) : $value['elements'];
        $value_elements = array_diff($value_elements,$value_top,$value_left,$value_right,$value_bottom);
        
        $parse_value = function($value) use($elements) {
            $tpl;
            foreach ( $value as $key ) {
                $tpl .= '<div class="cs-uls-layout-element layout-element__'.$key.'" data-layout-element-name="'.$key.'">'.$elements[$key]['name'].'</div>';
            }
            return $tpl;
        };

		echo $this->element_before();
        // echo '<div class="cs-layout-builder-ex">';

        echo '
            <div class="cs-uls-layout-builder">
                <div class="cs-uls-layout__design uls-layout-detailsview">
                    <div class="cs-uls-layout-section layout-section__top" data-layout-section="top">'.$parse_value($value_top).'</div>
                    <div class="cs-uls-layout-section layout-section__middle">
                        <div class="cs-uls-layout-section layout-section__left" data-layout-section="left">'.$parse_value($value_left).'</div>
                        <div class="cs-uls-layout-section layout-section__right" data-layout-section="right">'.$parse_value($value_right).'</div>
                    </div>
                    <div class="cs-uls-layout-section layout-section__bottom" data-layout-section="bottom">'.$parse_value($value_bottom).'</div>
                </div>
                <div class="cs-uls-layout__elements">
                    <div class="cs-uls-layout-title">Available Elements</div>
                    '.$parse_value($value_elements).'
                </div>
            </div>
        ';

        echo cs_add_element( array(
			'pseudo'	=> true,
			'type'		=> 'text',
			'name'		=> $this->element_name('[top]'),
            'value'		=> $this->value['top'],
            'class'		=> 'section__top',
			'attributes'	=> [
				'type'	=> 'hidden',
			]
        ) );
        echo cs_add_element( array(
			'pseudo'	=> true,
			'type'		=> 'text',
			'name'		=> $this->element_name('[left]'),
            'value'		=> $this->value['left'],
            'class'		=> 'section__left',
			'attributes'	=> [
				'type'	=> 'hidden',
			]
        ) );
        echo cs_add_element( array(
			'pseudo'	=> true,
			'type'		=> 'text',
			'name'		=> $this->element_name('[right]'),
            'value'		=> $this->value['right'],
            'class'		=> 'section__right',
			'attributes'	=> [
				'type'	=> 'hidden',
			]
        ) );
        echo cs_add_element( array(
			'pseudo'	=> true,
			'type'		=> 'text',
			'name'		=> $this->element_name('[bottom]'),
            'value'		=> $this->value['bottom'],
            'class'		=> 'section__bottom',
			'attributes'	=> [
				'type'	=> 'hidden',
			]
        ) );
        echo cs_add_element( array(
			'pseudo'	=> true,
			'type'		=> 'text',
			'name'		=> $this->element_name('[elements]'),
            'value'		=> $this->value['elements'],
            'class'		=> 'section__elements',
			'attributes'	=> [
				'type'	=> 'hidden',
			]
		) );

		// echo '</div>';
		echo $this->element_after();

	}

}