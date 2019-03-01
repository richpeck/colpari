<?php

if ( fusion_is_element_enabled( 'fusion_table' ) ) {

	if ( ! class_exists( 'FusionSC_FusionTable' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.0
		 */
		class FusionSC_FusionTable extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_shortcode( 'fusion_table', array( $this, 'render' ) );

				add_filter( 'fusion_attr_table-element', array( $this, 'attr' ) );

				add_filter( 'fusion_table_content', 'shortcode_unautop' );
				add_filter( 'fusion_table_content', 'do_shortcode' );
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				global $fusion_settings;

				$defaults = FusionBuilder::set_shortcode_defaults(
					array(
						'animation_type'      => '',
						'animation_direction' => 'left',
						'animation_speed'     => '',
						'animation_offset'    => $fusion_settings->get( 'animation_offset' ),
						'class'               => '',
						'fusion_table_type'   => '',
						'hide_on_mobile'      => fusion_builder_default_visibility( 'string' ),
						'id'                  => '',
					),
					$args,
					'fusion_table'
				);
				$defaults = apply_filters( 'fusion_builder_default_args', $defaults, 'fusion_table_element', $args );

				$this->args = $defaults;

				$this->args['content'] = $content;

				if ( $this->args['fusion_table_type'] ) {
					$content = str_replace( '<div class="table-' . $this->args['fusion_table_type'] . '">', '<div ' . FusionBuilder::attributes( 'table-element' ) . '>', $content );
				}

				return apply_filters(
					'fusion_table_content',
					fusion_builder_fix_shortcodes( $content )
				);
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {
				global $fusion_library;

				if ( $this->args['fusion_table_type'] ) {
					$table_style = $this->args['content'][19];

					if ( ( '1' === $table_style || '2' === $table_style ) && $table_style !== $this->args['fusion_table_type'] ) {
						$this->args['fusion_table_type'] = $table_style;
					}

					$attr = fusion_builder_visibility_atts(
						$this->args['hide_on_mobile'],
						array(
							'class' => 'table-' . $this->args['fusion_table_type'],
						)
					);

					if ( $this->args['animation_type'] ) {
						$animations = FusionBuilder::animations(
							array(
								'type'      => $this->args['animation_type'],
								'direction' => $this->args['animation_direction'],
								'speed'     => $this->args['animation_speed'],
								'offset'    => $this->args['animation_offset'],
							)
						);

						$attr = array_merge( $attr, $animations );

						$attr['class'] .= ' ' . $attr['animation_class'];
						unset( $attr['animation_class'] );
					}

					if ( $this->args['class'] ) {
						$attr['class'] .= ' ' . $this->args['class'];
					}

					if ( $this->args['id'] ) {
						$attr['id'] = $this->args['id'];
					}

					return $attr;
				}
			}
		}
	}

	new FusionSC_FusionTable();

}

/**
 * Map shortcode to Fusion Builder.
 */
function fusion_element_table() {
	fusion_builder_map(
		array(
			'name'             => __( 'Table', 'fusion-builder' ),
			'shortcode'        => 'fusion_table',
			'icon'             => 'fusiona-table',
			'allow_generator'  => true,
			'admin_enqueue_js' => FUSION_BUILDER_PLUGIN_URL . 'shortcodes/js/fusion-table.js',
			'params'           => array(
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Type', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the table style.', 'fusion-builder' ),
					'param_name'  => 'fusion_table_type',
					'value'       => array(
						'1' => esc_attr__( 'Style 1', 'fusion-builder' ),
						'2' => esc_attr__( 'Style 2', 'fusion-builder' ),
					),
					'default'          => '1',
				),
				array(
					'type'        => 'select',
					'heading'     => esc_attr__( 'Number of Columns', 'fusion-builder' ),
					'description' => esc_attr__( 'Select how many columns to display.', 'fusion-builder' ),
					'param_name'  => 'fusion_table_columns',
					'value'       => array(
						''  => esc_attr__( 'Select Columns', 'fusion-builder' ),
						'1' => esc_attr__( '1 Column', 'fusion-builder' ),
						'2' => esc_attr__( '2 Columns', 'fusion-builder' ),
						'3' => esc_attr__( '3 Columns', 'fusion-builder' ),
						'4' => esc_attr__( '4 Columns', 'fusion-builder' ),
						'5' => esc_attr__( '5 Columns', 'fusion-builder' ),
						'6' => esc_attr__( '6 Columns', 'fusion-builder' ),
					),
					'default'          => '',
					'remove_from_atts' => true,
				),
				array(
					'type'        => 'tinymce',
					'heading'     => esc_attr__( 'Table', 'fusion-builder' ),
					'description' => esc_attr__( 'Table content will appear here.', 'fusion-builder' ),
					'param_name'  => 'element_content',
					'value'       => '',
				),
				array(
					'type'        => 'select',
					'heading'     => esc_attr__( 'Animation Type', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the type of animation to use on the element.', 'fusion-builder' ),
					'param_name'  => 'animation_type',
					'value'       => fusion_builder_available_animations(),
					'default'     => '',
					'group'       => esc_attr__( 'Animation', 'fusion-builder' ),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Direction of Animation', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the incoming direction for the animation.', 'fusion-builder' ),
					'param_name'  => 'animation_direction',
					'value'       => array(
						'down'   => esc_attr__( 'Top', 'fusion-builder' ),
						'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						'up'     => esc_attr__( 'Bottom', 'fusion-builder' ),
						'left'   => esc_attr__( 'Left', 'fusion-builder' ),
						'static' => esc_attr__( 'Static', 'fusion-builder' ),
					),
					'default'     => 'left',
					'group'       => esc_attr__( 'Animation', 'fusion-builder' ),
					'dependency'  => array(
						array(
							'element'  => 'animation_type',
							'value'    => '',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Speed of Animation', 'fusion-builder' ),
					'description' => esc_attr__( 'Type in speed of animation in seconds (0.1 - 1).', 'fusion-builder' ),
					'param_name'  => 'animation_speed',
					'min'         => '0.1',
					'max'         => '1',
					'step'        => '0.1',
					'value'       => '0.3',
					'group'       => esc_attr__( 'Animation', 'fusion-builder' ),
					'dependency'  => array(
						array(
							'element'  => 'animation_type',
							'value'    => '',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'select',
					'heading'     => esc_attr__( 'Offset of Animation', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls when the animation should start.', 'fusion-builder' ),
					'param_name'  => 'animation_offset',
					'value'       => array(
						''                => esc_attr__( 'Default', 'fusion-builder' ),
						'top-into-view'   => esc_attr__( 'Top of element hits bottom of viewport', 'fusion-builder' ),
						'top-mid-of-view' => esc_attr__( 'Top of element hits middle of viewport', 'fusion-builder' ),
						'bottom-in-view'  => esc_attr__( 'Bottom of element enters viewport', 'fusion-builder' ),
					),
					'default'     => '',
					'group'       => esc_attr__( 'Animation', 'fusion-builder' ),
					'dependency'  => array(
						array(
							'element'  => 'animation_type',
							'value'    => '',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'checkbox_button_set',
					'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					'param_name'  => 'hide_on_mobile',
					'value'       => fusion_builder_visibility_options( 'full' ),
					'default'     => fusion_builder_default_visibility( 'array' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
					'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
					'param_name'  => 'class',
					'value'       => '',
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
					'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
					'param_name'  => 'id',
					'value'       => '',
				),
			),
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_table' );
