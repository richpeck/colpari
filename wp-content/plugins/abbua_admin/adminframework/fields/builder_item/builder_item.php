<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Layout Builder - Item
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSFramework_Option_builder_item extends CSFramework_Options {

	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function output(){

        $elements = [
            'title' => [
                'name'  => __('Item Title'),
                'slug'  => 'title'
            ],
            'description' => [
                'name'  => __('Item Short Description'),
                'slug'  => 'description'
            ],
            'social'    => [
                'name'  => __('Social Links'),
                'slug'  => 'social'
            ],
            'button_external' => [
                'name'  => __('External Link'),
                'slug'  => 'button_external'
            ],
            'button_detailsview' => [
                'name'  => __('Details View Link'),
                'slug'  => 'button_detailsview'
            ]
        ];
        
        $is_json = function($string) {
            return !empty($string) && is_string($string) && is_array(json_decode($string, true)) && json_last_error() == 0;
        };

        $defaults_value = array(
            'main'      => '',
            'buttonbar' => '',
            'elements'  => array_keys($elements)
        );

		$value			    = wp_parse_args( $this->element_value(), $defaults_value );
        $value_main	        = json_decode($value['main']);
        $value_main         = array_diff($value_main, ["button_external", "button_detailsview"]);
        $value_buttonbar    = json_decode($value['buttonbar']);
        $value_elements     = ($is_json($value['elements'])) ? json_decode($value['elements']) : $value['elements'];
        $value_elements     = array_diff($value_elements,$value_main,$value_buttonbar);

        $parse_value = function($value) use($elements) {
            $tpl;
            foreach ( $value as $key ) {
                if ($key == 'buttonbar'){
                    // Exception for the buttonbar section
                    $tpl .= "BUTTONBARPLACEHOLDER";
                } else {
                    $tpl .= '<div class="cs-uls-layout-element layout-element__'.$key.'" data-layout-element-name="'.$key.'">'.$elements[$key]['name'].'</div>';
                }
            }
            return $tpl;
        };

        $section_main       = $parse_value($value_main);
        $section_buttonbar  = '<div class="cs-uls-layout-section layout-section__buttonbar cs-uls-layout-element" data-layout-section="buttonbar" data-layout-element-name="buttonbar">'.$parse_value($value_buttonbar).'</div>';
        if ($value_main || $value_buttonbar){
            $section_main       = str_replace("BUTTONBARPLACEHOLDER",$section_buttonbar,$section_main);
        } else {
            $section_main       .= $section_buttonbar;
        }

		echo $this->element_before();
        // echo '<div class="cs-layout-builder-ex">';

        echo '
            <div class="cs-uls-layout-builder">
                <div class="cs-uls-layout__design uls-layout-item">
                    <div class="cs-uls-layout-section layout-section__main" data-layout-section="main">
                        '.$section_main.'
                    </div>
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
			'name'		=> $this->element_name('[main]'),
            'value'		=> $this->value['main'],
            'class'		=> 'section__main',
			'attributes'	=> [
				'type'	=> 'hidden',
			]
        ) );
        echo cs_add_element( array(
			'pseudo'	=> true,
			'type'		=> 'text',
			'name'		=> $this->element_name('[buttonbar]'),
            'value'		=> $this->value['buttonbar'],
            'class'		=> 'section__buttonbar',
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