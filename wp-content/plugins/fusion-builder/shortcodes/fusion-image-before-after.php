<?php

if ( fusion_is_element_enabled( 'fusion_image_before_after' ) ) {

	if ( ! class_exists( 'FusionSC_ImageBeforeAfter' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.5
		 */
		class FusionSC_ImageBeforeAfter extends Fusion_Element {

			/**
			 * The before-after counter.
			 *
			 * @access private
			 * @since 1.5
			 * @var int
			 */
			private $before_after_counter = 1;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.5
			 * @var array
			 */
			protected $args = array();

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.5
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_image-before-after-shortcode', array( $this, 'attr' ) );
				add_filter( 'fusion_attr_image-before-after-wrapper', array( $this, 'wrapper_attr' ) );
				add_filter( 'fusion_attr_image-before-image', array( $this, 'before_image_attr' ) );
				add_filter( 'fusion_attr_image-after-image', array( $this, 'after_image_attr' ) );
				add_filter( 'fusion_attr_before-after-overlay', array( $this, 'before_after_overlay' ) );
				add_filter( 'fusion_attr_before-after-handle-type', array( $this, 'handle_type_attr' ) );

				add_shortcode( 'fusion_image_before_after', array( $this, 'render' ) );

			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.5
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				global $fusion_settings, $fusion_library;
				if ( ! $fusion_settings ) {
					$fusion_settings = Fusion_Settings::get_instance();
				}

				$defaults = FusionBuilder::set_shortcode_defaults(
					array(
						'type'                => ( '' !== $fusion_settings->get( 'before_after_type' ) ) ? strtolower( $fusion_settings->get( 'before_after_type' ) ) : 'before_after',
						'before_image'        => '',
						'before_image_id'     => '',
						'before_label'        => '',
						'after_image'         => '',
						'after_image_id'      => '',
						'after_label'         => '',
						'font_size'           => $fusion_settings->get( 'before_after_font_size' ),
						'accent_color'        => $fusion_settings->get( 'before_after_accent_color' ),
						'label_placement'     => $fusion_settings->get( 'before_after_label_placement' ),
						'handle_type'         => $fusion_settings->get( 'before_after_handle_type' ),
						'handle_bg'           => $fusion_settings->get( 'before_after_handle_bg' ),
						'handle_color'        => $fusion_settings->get( 'before_after_handle_color' ),
						'transition_time'     => $fusion_settings->get( 'before_after_transition_time' ),
						'offset'              => $fusion_settings->get( 'before_after_offset' ),
						'orientation'         => $fusion_settings->get( 'before_after_orientation' ),
						'handle_movement'     => $fusion_settings->get( 'before_after_handle_movement' ),
						'hide_on_mobile'      => fusion_builder_default_visibility( 'string' ),
						'animation_type'      => '',
						'animation_direction' => 'left',
						'animation_speed'     => '',
						'animation_offset'    => $fusion_settings->get( 'animation_offset' ),
						'class'               => '',
						'id'                  => '',
					),
					$args,
					'fusion_image_before_after'
				);

				$defaults['offset']    = $defaults['offset'] / 100;
				$defaults['font_size'] = FusionBuilder::validate_shortcode_attr_value( $defaults['font_size'], 'px' );

				$this->args = $defaults;

				$styles = $html = '';

				if ( isset( $this->args['handle_color'] ) && 'before_after' === $this->args['type'] ) {
					$color   = Fusion_Sanitize::color( $this->args['handle_color'] );
					$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle {';
					$styles .= 'border-color:' . $color . ';';
					$styles .= '}';
					if ( 'horizontal' === $this->args['orientation'] ) {
						$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-left-arrow {';
						$styles .= 'border-right-color:' . $color . ';';
						$styles .= '}';
						$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-right-arrow {';
						$styles .= 'border-left-color:' . $color . ';';
						$styles .= '}';
						if ( isset( $this->args['handle_type'] ) && 'diamond' === $this->args['handle_type'] ) {
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle-diamond .fusion-image-before-after-left-arrow::before {';
							$styles .= 'border-color:' . $color . ' !important;';
							$styles .= '}';
						} else if ( isset( $this->args['handle_type'] ) && 'circle' === $this->args['handle_type'] ) {
							$color_obj = Fusion_Color::new_color( $color );

							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle-circle {';
							$styles .= 'background:' . $color . ' !important;';
							$styles .= '}';
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle-circle .fusion-image-before-after-left-arrow::before {';
							$styles .= 'border-color:' . $color_obj->getNew( 'alpha', $color_obj->alpha * 0.6 )->toCSS( 'rgba' ) . ' !important;';
							$styles .= '}';
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle-circle .fusion-image-before-after-left-arrow {';
							$styles .= 'border-right-color:' . Fusion_Helper::fusion_auto_calculate_accent_color( $color ) . ' !important;';
							$styles .= '}';
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle-circle .fusion-image-before-after-right-arrow {';
							$styles .= 'border-left-color:' . Fusion_Helper::fusion_auto_calculate_accent_color( $color ) . ' !important;';
							$styles .= '}';
						}
					} else if ( 'vertical' === $this->args['orientation'] ) {
						$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-up-arrow {';
						$styles .= 'border-bottom-color:' . $color . ';';
						$styles .= '}';
						$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-down-arrow {';
						$styles .= 'border-top-color:' . $color . ';';
						$styles .= '}';
						if ( isset( $this->args['handle_type'] ) && 'diamond' === $this->args['handle_type'] ) {
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle-diamond .fusion-image-before-after-down-arrow::before {';
							$styles .= 'border-color:' . $color . ' !important;';
							$styles .= '}';
						} else if ( isset( $this->args['handle_type'] ) && 'circle' === $this->args['handle_type'] ) {
							$color_obj = Fusion_Color::new_color( $color );

							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle-circle {';
							$styles .= 'background:' . $color . ' !important;';
							$styles .= '}';
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle-circle .fusion-image-before-after-down-arrow::before {';
							$styles .= 'border-color:' . $color_obj->getNew( 'alpha', $color_obj->alpha * 0.6 )->toCSS( 'rgba' ) . ' !important;';
							$styles .= '}';
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle-circle .fusion-image-before-after-up-arrow {';
							$styles .= 'border-bottom-color:' . Fusion_Helper::fusion_auto_calculate_accent_color( $color ) . ' !important;';
							$styles .= '}';
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle-circle .fusion-image-before-after-down-arrow {';
							$styles .= 'border-top-color:' . Fusion_Helper::fusion_auto_calculate_accent_color( $color ) . ' !important;';
							$styles .= '}';
						}
					} // End if().
					$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle::after {';
					$styles .= 'background:' . $color . ';';
					if ( 'vertical' !== $this->args['orientation'] ) {
						$styles .= 'box-shadow: 0 3px 0 ' . $color . ', 0 0 12px rgba(51,51,51,.5);';
					}
					$styles .= '}';
					$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle::before {';
					$styles .= 'background:' . $color . ';';
					if ( 'vertical' !== $this->args['orientation'] ) {
						$styles .= 'box-shadow: 0 3px 0 ' . $color . ', 0 0 12px rgba(51,51,51,.5);';
					}
					$styles .= '}';

				} // End if().

				if ( isset( $this->args['handle_bg'] ) && 'before_after' === $this->args['type'] ) {
					$bg_color = Fusion_Sanitize::color( $this->args['handle_bg'] );
					if ( 'circle' !== $this->args['handle_type'] && 'arrows' !== $this->args['handle_type'] ) {
						if ( 'diamond' !== $this->args['handle_type'] ) {
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle {';
							$styles .= 'background:' . $bg_color . ';';
							$styles .= '}';
						} else {
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-down-arrow:before,';
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-left-arrow:before {';
							$styles .= 'background:' . $bg_color . ';';
							$styles .= '}';
						}
					}
				}

				if ( isset( $this->args['font_size'] ) && 'before_after' === $this->args['type'] ) {
					$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-before-label:before';
					$styles .= ',.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-after-label:before';
					if ( 'out-image-up-down' === $this->args['label_placement'] ) {
						$styles .= ',.fusion-image-before-after-wrapper-' . $this->before_after_counter . ' .fusion-image-before-after-before-label:before';
						$styles .= ',.fusion-image-before-after-wrapper-' . $this->before_after_counter . ' .fusion-image-before-after-after-label:before';
					}
					$styles .= '{';
					$styles .= 'font-size:' . $this->args['font_size'] . ';';
					$styles .= '}';
				}

				if ( isset( $this->args['accent_color'] ) && 'before_after' === $this->args['type'] ) {

					$color     = Fusion_Sanitize::color( $this->args['accent_color'] );
					$color_obj = Fusion_Color::new_color( $color );
					$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-before-label:before';
					$styles .= ',.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-after-label:before';
					if ( 'out-image-up-down' === $this->args['label_placement'] ) {
						$styles .= ',.fusion-image-before-after-wrapper-' . $this->before_after_counter . ' .fusion-image-before-after-before-label:before';
						$styles .= ',.fusion-image-before-after-wrapper-' . $this->before_after_counter . ' .fusion-image-before-after-after-label:before';
					}
					$styles .= '{';
					$styles .= 'color:' . $color . ';';
					if ( 'out-image-up-down' !== $this->args['label_placement'] ) {
						$styles .= 'background:' . $color_obj->getNew( 'alpha', $color_obj->alpha * 0.15 )->toCSS( 'rgba' ) . ';';
					} else {
						$styles .= 'background:transparent;';
					}
					$styles .= '}';
				}

				if ( 'switch' === $this->args['type'] && isset( $this->args['transition_time'] ) ) {
					$styles .= '.fusion-image-switch.fusion-image-before-after-' . $this->before_after_counter . ' img{';
					$styles .= 'transition: ' . $this->args['transition_time'] . 's ease-in-out;';
					$styles .= '}';
				}

				if ( '' !== $styles ) {
					$style_tag = '<style type="text/css" scoped="scoped">' . $styles . '</style>';
				}

				if ( 'before_after' === $this->args['type'] ) {
					$html .= '<div ' . FusionBuilder::attributes( 'image-before-after-wrapper' ) . '>';
				}

				if ( is_rtl() && 'vertical' !== $this->args['orientation'] ) {
					if ( '' !== $this->args['before_label'] && '' !== $this->args['after_label'] && 'before_after' === $this->args['type'] && 'out-image-up-down' === $this->args['label_placement'] ) {
						$html .= '<div class="fusion-image-before-after-after-label before-after-label-out-image-up-down" data-content="' . esc_attr( $this->args['after_label'] ) . '"></div>';
					}
				} else {
					if ( '' !== $this->args['before_label'] && '' !== $this->args['after_label'] && 'before_after' === $this->args['type'] && 'out-image-up-down' === $this->args['label_placement'] ) {
						$html .= '<div class="fusion-image-before-after-before-label before-after-label-out-image-up-down" data-content="' . esc_attr( $this->args['before_label'] ) . '"></div>';
					}
				}

				$html .= '<div ' . FusionBuilder::attributes( 'image-before-after-shortcode' ) . '>';
				if ( is_rtl() && 'vertical' !== $this->args['orientation'] ) {
					if ( isset( $this->args['after_image'] ) ) {

						$html .= '<img ' . FusionBuilder::attributes( 'image-after-image' ) . '>';
					}

					if ( isset( $this->args['before_image'] ) ) {
						$html .= '<img ' . FusionBuilder::attributes( 'image-before-image' ) . '>';
					}
				} else {
					if ( isset( $this->args['before_image'] ) ) {
						$html .= '<img ' . FusionBuilder::attributes( 'image-before-image' ) . '>';
					}

					if ( isset( $this->args['after_image'] ) ) {

						$html .= '<img ' . FusionBuilder::attributes( 'image-after-image' ) . '>';
					}
				}

				if ( '' !== $this->args['before_label'] && '' !== $this->args['after_label'] && 'before_after' === $this->args['type'] && ( 'image-centered' === $this->args['label_placement'] || 'image-up-down' === $this->args['label_placement'] ) ) {
					$html .= '<div ' . FusionBuilder::attributes( 'before-after-overlay' ) . '>';
					$html .= '<div class="fusion-image-before-after-before-label" data-content="' . esc_attr( $this->args['before_label'] ) . '"></div>';
					$html .= '<div class="fusion-image-before-after-after-label" data-content="' . esc_attr( $this->args['after_label'] ) . '"></div>';
					$html .= '</div>';
				}

				if ( 'before_after' === $this->args['type'] ) {
					$before_direction = ( 'vertical' === $this->args['orientation'] ? 'down' : 'left' );
					$after_direction  = ( 'vertical' === $this->args['orientation'] ? 'up' : 'right' );
					$html .= '<div ' . FusionBuilder::attributes( 'before-after-handle-type' ) . '>';
					$html .= '<span class="fusion-image-before-after-' . $before_direction . '-arrow"></span>';
					$html .= '<span class="fusion-image-before-after-' . $after_direction . '-arrow"></span>';
					$html .= '</div>';
				}

				$html .= '</div>';

				if ( is_rtl() && 'vertical' !== $this->args['orientation'] ) {
					if ( '' !== $this->args['before_label'] && '' !== $this->args['after_label'] && 'before_after' === $this->args['type'] && 'out-image-up-down' === $this->args['label_placement'] ) {
						$html .= '<div class="fusion-image-before-after-before-label before-after-label-out-image-up-down" data-content="' . esc_attr( $this->args['before_label'] ) . '"></div>';
					}
				} else {
					if ( '' !== $this->args['before_label'] && '' !== $this->args['after_label'] && 'before_after' === $this->args['type'] && 'out-image-up-down' === $this->args['label_placement'] ) {
						$html .= '<div class="fusion-image-before-after-after-label before-after-label-out-image-up-down" data-content="' . esc_attr( $this->args['after_label'] ) . '"></div>';
					}
				}

				if ( 'before_after' === $this->args['type'] ) {
					$html .= '</div>';
				}

				$this->before_after_counter++;

				return $style_tag . $html;

			}

			/**
			 * Builds the before image attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function before_image_attr() {
				global $fusion_library;

				$image_data = $fusion_library->images->get_attachment_data_by_helper( $this->args['before_image_id'], $this->args['before_image'] );

				$alt = ( $image_data['alt'] ) ? $image_data['alt'] : $this->args['before_label'];

				if ( $image_data['url'] ) {
					$this->args['before_image'] = $image_data['url'];
				}

				$attr = array(
					'alt'   => $alt,
					'class' => ( 'before_after' === $this->args['type'] ? 'fusion-image-before-after-before' : 'fusion-image-switch-before' ),
					'src'   => $this->args['before_image'],
				);

				return $attr;
			}

			/**
			 * Builds the after image attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function after_image_attr() {
				global $fusion_library;

				$image_data = $fusion_library->images->get_attachment_data_by_helper( $this->args['after_image_id'], $this->args['after_image'] );

				$alt = ( $image_data['alt'] ) ? $image_data['alt'] : $this->args['after_label'];

				if ( isset( $image_data['url'] ) ) {
					$this->args['after_image'] = $image_data['url'];
				}

				$attr = array(
					'alt'   => $alt,
					'class' => ( 'before_after' === $this->args['type'] ? 'fusion-image-before-after-after' : 'fusion-image-switch-after' ),
					'src'   => $this->args['after_image'],
				);

				return $attr;
			}

			/**
			 * Builds the overlay attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function before_after_overlay() {
				$attr = array(
					'class' => 'fusion-image-before-after-overlay',
				);

				if ( $this->args['label_placement'] ) {
					$attr['class'] .= ' before-after-overlay-' . $this->args['label_placement'];
				}

				return $attr;
			}

			/**
			 * Builds the wrapper attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function wrapper_attr() {

				$attr = fusion_builder_visibility_atts(
					$this->args['hide_on_mobile'],
					array(
						'class' => 'fusion-image-before-after-wrapper',
					)
				);

				if ( $this->args['orientation'] ) {
					$attr['class'] .= ' fusion-image-before-after-' . $this->args['orientation'];
				}

				$attr['class'] .= ' fusion-image-before-after-wrapper-' . $this->before_after_counter;

				return $attr;
			}

			/**
			 * Builds the handle type attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function handle_type_attr() {

				$attr = array(
					'class' => 'fusion-image-before-after-handle',
				);

				if ( $this->args['handle_type'] ) {
					$attr['class'] .= ' fusion-image-before-after-handle-' . $this->args['handle_type'];
				}

				return $attr;

			}
			/**
			 * Builds the prent attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function attr() {

				$attr = array(
					'class' => 'fusion-image-before-after-element',
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

				if ( 'switch' === $this->args['type'] ) {
					$attr['class'] .= ' fusion-image-switch';
				} else if ( 'before_after' === $this->args['type'] ) {
					$attr['class'] .= ' fusion-image-before-after fusion-image-before-after-container';

					if ( $this->args['offset'] || 0 === $this->args['offset'] ) {
						$attr['data-offset'] = $this->args['offset'];
					}

					if ( $this->args['orientation'] ) {
						$attr['data-orientation'] = $this->args['orientation'];
					}
					if ( $this->args['handle_movement'] ) {
						if ( 'drag_click' === $this->args['handle_movement'] ) {
							$attr['data-move-with-handle-only'] = 'true';
							$attr['data-click-to-move']         = 'true';
						} else if ( 'drag' === $this->args['handle_movement'] ) {
							$attr['data-move-with-handle-only'] = 'true';
						} else if ( 'hover' === $this->args['handle_movement'] ) {
							$attr['data-move-slider-on-hover'] = 'true';
						}
					}
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				$attr['class'] .= ' fusion-image-before-after-' . $this->before_after_counter;

				return $attr;

			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.5
			 * @return array $sections Image Before & Afte settings.
			 */
			public function add_options() {

				return array(
					'image_before_after_shortcode_section' => array(
						'label'       => __( ' Image Before & After Element', 'fusion-builder' ),
						'description' => '',
						'id'          => 'image_before_after_shortcode_section',
						'type'        => 'accordion',
						'fields'      => array(
							'before_after_type' => array(
								'label'       => esc_html__( 'Effect Type', 'fusion-builder' ),
								'description' => esc_html__( 'Select which type of effect your before and after image uses. "Slide" provides a handle to move back and forth while "Fade" changes the image on mouse hover.', 'fusion-builder' ),
								'id'          => 'before_after_type',
								'default'     => 'before_after',
								'type'        => 'radio-buttonset',
								'choices'     => array(
									'before_after' => esc_attr__( 'Slide', 'fusion-builder' ),
									'switch'       => esc_attr__( 'Fade', 'fusion-builder' ),
								),
							),
							'before_after_font_size' => array(
								'type'        => 'slider',
								'label'       => esc_attr__( 'Label Font Size', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the font size of the label text. Note: font family is controlled by body font in theme options.', 'fusion-builder' ),
								'id'          => 'before_after_font_size',
								'default'     => '13',
								'choices'     => array(
									'min'         => '10',
									'max'         => '100',
									'step'        => '1',
								),
								'required'    => array(
									array(
										'setting'  => 'before_after_type',
										'operator' => '!=',
										'value'    => 'switch',
									),
								),
							),
							'before_after_accent_color' => array(
								'label'       => esc_html__( 'Label Accent Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the label background and text. Text takes 100% of this color, background takes a % of it.', 'fusion-builder' ),
								'id'          => 'before_after_accent_color',
								'default'     => '#ffffff',
								'type'        => 'color-alpha',
								'required'    => array(
									array(
										'setting'  => 'before_after_type',
										'operator' => '!=',
										'value'    => 'switch',
									),
								),
							),
							'before_after_label_placement' => array(
								'label'       => esc_html__( 'Label Placement', 'fusion-builder' ),
								'description' => esc_html__( 'Choose if labels are on top of the image and centered, on top of the image up & down or outside of the image up & down.', 'fusion-builder' ),
								'id'          => 'before_after_label_placement',
								'default'     => 'image-centered',
								'type'        => 'select',
								'choices'     => array(
									'image-centered'    => esc_html__( 'Image Centered', 'fusion-builder' ),
									'image-up-down'     => esc_html__( 'Image Up & Down', 'fusion-builder' ),
									'out-image-up-down' => esc_html__( 'Outside Image Up & Down', 'fusion-builder' ),
								),
								'required'    => array(
									array(
										'setting'  => 'before_after_type',
										'operator' => '!=',
										'value'    => 'switch',
									),
								),
							),
							'before_after_handle_type' => array(
								'label'       => esc_html__( 'Handle Design Style', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the design of the handle used to change the before and after image.', 'fusion-builder' ),
								'id'          => 'before_after_handle_type',
								'default'     => 'default',
								'type'        => 'select',
								'choices'     => array(
									'default'    => esc_html__( 'Circle With Arrows', 'fusion-builder' ),
									'square'     => esc_html__( 'Square With Arrows', 'fusion-builder' ),
									'rectangle'  => esc_html__( 'Rectangle With Arrows', 'fusion-builder' ),
									'arrows'     => esc_html__( 'Arrows', 'fusion-builder' ),
									'diamond'    => esc_html__( 'Diamond', 'fusion-builder' ),
									'circle'     => esc_html__( 'Single Circle', 'fusion-builder' ),
								),
								'required'    => array(
									array(
										'setting'  => 'before_after_type',
										'operator' => '!=',
										'value'    => 'switch',
									),
								),
							),
							'before_after_handle_color' => array(
								'label'       => esc_html__( 'Handle Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the before and after image handle line and arrows. ex: #ffffff.', 'fusion-builder' ),
								'id'          => 'before_after_handle_color',
								'default'     => '#ffffff',
								'type'        => 'color-alpha',
								'required'    => array(
									array(
										'setting'  => 'before_after_type',
										'operator' => '!=',
										'value'    => 'switch',
									),
								),
							),
							'before_after_handle_bg' => array(
								'label'       => esc_html__( 'Handle Background Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the background color of the before and after image handle switch. ex: #000000.', 'fusion-builder' ),
								'id'          => 'before_after_handle_bg',
								'default'     => 'rgba(255,255,255,0)',
								'type'        => 'color-alpha',
								'required'    => array(
									array(
										'setting'  => 'before_after_type',
										'operator' => '!=',
										'value'    => 'switch',
									),
									array(
										'setting'  => 'before_after_handle_type',
										'operator' => '!=',
										'value'    => 'circle',
									),
									array(
										'setting'  => 'before_after_handle_type',
										'operator' => '!=',
										'value'    => 'arrows',
									),
								),
							),
							'before_after_offset' => array(
								'label'       => esc_html__( 'Handle Offset', 'fusion-builder' ),
								'description' => esc_html__( 'Controls where the handle will be positioned on page load allowing you to control how much of each image displays by default. In percentage.', 'fusion-builder' ),
								'id'          => 'before_after_offset',
								'default'     => '50',
								'type'        => 'slider',
								'choices'     => array(
									'min'  => '0',
									'max'  => '100',
									'step' => '1',
								),
								'required'    => array(
									array(
										'setting'  => 'before_after_type',
										'operator' => '!=',
										'value'    => 'switch',
									),
								),
							),
							'before_after_orientation' => array(
								'label'       => esc_html__( 'Handle Orientation', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the position of the before and after image handle.', 'fusion-builder' ),
								'id'          => 'before_after_orientation',
								'default'     => 'horizontal',
								'type'        => 'radio-buttonset',
								'choices'     => array(
									'horizontal' => esc_attr__( 'Horizontal', 'fusion-builder' ),
									'vertical'   => esc_attr__( 'Vertical', 'fusion-builder' ),
								),
								'required'    => array(
									array(
										'setting'  => 'before_after_type',
										'operator' => '!=',
										'value'    => 'switch',
									),
								),
							),
							'before_after_handle_movement' => array(
								'label'       => esc_html__( 'Handle Movement Control', 'fusion-builder' ),
								'description' => esc_html__( 'Controls how the viewer interacts with the image handler. The image handle can use Drag & Click, Drag Only, or Hover.', 'fusion-builder' ),
								'id'          => 'before_after_handle_movement',
								'default'     => 'drag_click',
								'type'        => 'radio-buttonset',
								'choices'     => array(
									'drag_click' => esc_attr__( 'Drag & Click', 'fusion-builder' ),
									'drag'       => esc_attr__( 'Drag Only', 'fusion-builder' ),
									'hover'      => esc_attr__( 'Hover', 'fusion-builder' ),
								),
								'required'    => array(
									array(
										'setting'  => 'before_after_type',
										'operator' => '!=',
										'value'    => 'switch',
									),
								),
							),
							'before_after_transition_time' => array(
								'label'       => esc_html__( 'Image Fade Transition Speed', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the speed of the fade transition on mouse hover. In seconds.', 'fusion-builder' ),
								'id'          => 'before_after_transition_time',
								'default'     => '0.5',
								'type'        => 'slider',
								'choices'     => array(
									'min'  => '0',
									'max'  => '1',
									'step' => '0.1',
								),
								'required'    => array(
									array(
										'setting'  => 'before_after_type',
										'operator' => '!=',
										'value'    => 'before_after',
									),
								),
							),
						),
					),
				);
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 1.5
			 * @return void
			 */
			public function add_scripts() {

				Fusion_Dynamic_JS::enqueue_script(
					'jquery-event-move',
					FusionBuilder::$js_folder_url . '/library/jquery.event.move.js',
					FusionBuilder::$js_folder_path . '/library/jquery.event.move.js',
					array( 'jquery' ),
					'2.0',
					true
				);

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-image-before-after',
					FusionBuilder::$js_folder_url . '/general/fusion-image-before-after.js',
					FusionBuilder::$js_folder_path . '/general/fusion-image-before-after.js',
					array( 'jquery', 'jquery-event-move' ),
					'1.0',
					true
				);
			}
		}
	} // End if().

	new FusionSC_ImageBeforeAfter();

} // End if().

/**
 * Map shortcode to Fusion Builder
 *
 * @since 1.0
 */
function fusion_element_image_before_after() {
	global $fusion_settings;
	if ( ! $fusion_settings ) {
		$fusion_settings = Fusion_Settings::get_instance();
	}

	fusion_builder_map(
		array(
			'name'          => __( 'Image Before & After', 'fusion-builder' ),
			'shortcode'     => 'fusion_image_before_after',
			'icon'          => 'fusiona-object-ungroup',
			'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-image-before-after-preview.php',
			'preview_id'    => 'fusion-builder-block-module-image-before-after-preview-template',
			'params'        => array(
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Effect Type', 'fusion-builder' ),
					'description' => esc_attr__( 'Select which type of effect your before and after image uses. “Slide” provides a handle to move back and forth while “Fade” changes the image on mouse hover.', 'fusion-builder' ),
					'param_name'  => 'type',
					'default'     => '',
					'value'       => array(
						''             => esc_attr__( 'Default', 'fusion-builder' ),
						'before_after' => esc_attr__( 'Slide', 'fusion-builder' ),
						'switch'       => esc_attr__( 'Fade', 'fusion-builder' ),
					),
				),
				array(
					'type'        => 'upload',
					'heading'     => esc_attr__( 'Before Image', 'fusion-builder' ),
					'description' => esc_attr__( 'Upload a before image to display.', 'fusion-builder' ),
					'param_name'  => 'before_image',
					'value'       => '',
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Before Image ID', 'fusion-builder' ),
					'description' => esc_attr__( 'Before Image ID from Media Library.', 'fusion-builder' ),
					'param_name'  => 'before_image_id',
					'value'       => '',
					'hidden'      => true,
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Before Image Label', 'fusion-builder' ),
					'description' => esc_attr__( 'Add text that will be displayed as a label on the before image when hovered. If left empty, no label will show.', 'fusion-builder' ),
					'param_name'  => 'before_label',
					'value'       => '',
					'dependency'  => array(
						array(
							'element'  => 'before_image',
							'value'    => '',
							'operator' => '!=',
						),
						array(
							'element'  => 'type',
							'value'    => 'switch',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'upload',
					'heading'     => esc_attr__( 'After Image', 'fusion-builder' ),
					'description' => esc_attr__( 'Upload an after image to display.', 'fusion-builder' ),
					'param_name'  => 'after_image',
					'value'       => '',
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'After Image ID', 'fusion-builder' ),
					'description' => esc_attr__( 'After Image ID from Media Library.', 'fusion-builder' ),
					'param_name'  => 'after_image_id',
					'value'       => '',
					'hidden'      => true,
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'After Image Label', 'fusion-builder' ),
					'description' => esc_attr__( 'Add text that will be displayed as a label on the after image when hovered. If left empty, no label will show.', 'fusion-builder' ),
					'param_name'  => 'after_label',
					'value'       => '',
					'dependency'  => array(
						array(
							'element'  => 'after_image',
							'value'    => '',
							'operator' => '!=',
						),
						array(
							'element'  => 'type',
							'value'    => 'switch',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Label Font Size', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the font size of the label text. In Pixels. Note: font family is controlled by body font in theme options.', 'fusion-builder' ),
					'param_name'  => 'font_size',
					'default'     => intval( $fusion_settings->get( 'before_after_font_size' ) ),
					'value'       => '',
					'choices'     => array(
						'min'         => '10',
						'max'         => '100',
						'step'        => '1',
					),
					'dependency'  => array(
						array(
							'element'  => 'after_image',
							'value'    => '',
							'operator' => '!=',
						),
						array(
							'element'  => 'before_image',
							'value'    => '',
							'operator' => '!=',
						),
						array(
							'element'  => 'before_label',
							'value'    => '',
							'operator' => '!=',
						),
						array(
							'element'  => 'after_label',
							'value'    => '',
							'operator' => '!=',
						),
						array(
							'element'  => 'type',
							'value'    => 'switch',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Label Accent Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the color of the label background and text. Text takes 100% of this color, background takes a % of it.', 'fusion-builder' ),
					'param_name'  => 'accent_color',
					'value'       => '',
					'default'     => $fusion_settings->get( 'before_after_accent_color' ),
					'dependency'  => array(
						array(
							'element'  => 'after_image',
							'value'    => '',
							'operator' => '!=',
						),
						array(
							'element'  => 'before_image',
							'value'    => '',
							'operator' => '!=',
						),
						array(
							'element'  => 'before_label',
							'value'    => '',
							'operator' => '!=',
						),
						array(
							'element'  => 'after_label',
							'value'    => '',
							'operator' => '!=',
						),
						array(
							'element'  => 'type',
							'value'    => 'switch',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'select',
					'heading'     => esc_attr__( 'Label Placement', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose if labels are on top of the image and centered, on top of the image up & down or outside of the image up & down.', 'fusion-builder' ),
					'param_name'  => 'label_placement',
					'value'       => array(
						''                   => __( 'Default', 'fusion-builder' ),
						'image-centered'     => __( 'Image Centered', 'fusion-builder' ),
						'image-up-down'      => __( 'Image Up & Down', 'fusion-builder' ),
						'out-image-up-down'  => __( 'Outside Image Up & Down', 'fusion-builder' ),
					),
					'dependency'  => array(
						array(
							'element'  => 'after_image',
							'value'    => '',
							'operator' => '!=',
						),
						array(
							'element'  => 'before_image',
							'value'    => '',
							'operator' => '!=',
						),
						array(
							'element'  => 'before_label',
							'value'    => '',
							'operator' => '!=',
						),
						array(
							'element'  => 'after_label',
							'value'    => '',
							'operator' => '!=',
						),
						array(
							'element'  => 'type',
							'value'    => 'switch',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Image Fade Transition Speed', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the speed of the fade transition on mouse hover. In seconds.', 'fusion-builder' ),
					'param_name'  => 'transition_time',
					'value'       => '',
					'default'     => $fusion_settings->get( 'before_after_transition_time' ),
					'min'         => '0',
					'max'         => '1',
					'step'        => '.1',
					'dependency'  => array(
						array(
							'element'  => 'type',
							'value'    => 'before_after',
							'operator' => '!=',
						),
					),

				),
				array(
					'type'        => 'select',
					'heading'     => esc_attr__( 'Handle Design Style', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the design of the handle used to change the before and after image.', 'fusion-builder' ),
					'param_name'  => 'handle_type',
					'value'       => array(
						''          => esc_attr__( 'Default', 'fusion-builder' ),
						'default'   => esc_attr__( 'Circle With Arrows', 'fusion-builder' ),
						'square'    => esc_attr__( 'Square With Arrows', 'fusion-builder' ),
						'rectangle' => esc_attr__( 'Rectangle With Arrows', 'fusion-builder' ),
						'arrows'    => esc_attr__( 'Arrows', 'fusion-builder' ),
						'diamond'   => esc_attr__( 'Diamond', 'fusion-builder' ),
						'circle'    => esc_attr__( 'Single Circle', 'fusion-builder' ),
					),
					'dependency'  => array(
						array(
							'element'  => 'type',
							'value'    => 'switch',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Handle Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the color of the before and after image handle line and arrows. ex: #ffffff.', 'fusion-builder' ),
					'param_name'  => 'handle_color',
					'value'       => '',
					'default'     => $fusion_settings->get( 'before_after_handle_color' ),
					'dependency'  => array(
						array(
							'element'  => 'type',
							'value'    => 'switch',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Handle Background Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the background color of the before and after image handle switch. ex: #000000.', 'fusion-builder' ),
					'param_name'  => 'handle_bg',
					'value'       => '',
					'default'     => $fusion_settings->get( 'before_after_handle_bg' ),
					'dependency'  => array(
						array(
							'element'  => 'type',
							'value'    => 'switch',
							'operator' => '!=',
						),
						array(
							'element'  => 'handle_type',
							'value'    => 'arrows',
							'operator' => '!=',
						),
						array(
							'element'  => 'handle_type',
							'value'    => 'circle',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Handle Offset', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls where the handle will be positioned on page load allowing you to control how much of each image displays by default. In percentage.', 'fusion-builder' ),
					'param_name'  => 'offset',
					'value'       => '',
					'default'     => $fusion_settings->get( 'before_after_offset' ),
					'min'         => '0',
					'max'         => '100',
					'step'        => '1',
					'dependency'  => array(
						array(
							'element'  => 'type',
							'value'    => 'switch',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Handle Orientation', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the position of the before and after image handle.', 'fusion-builder' ),
					'param_name'  => 'orientation',
					'default'     => '',
					'value'       => array(
						''           => esc_attr__( 'Default', 'fusion-builder' ),
						'horizontal' => esc_attr__( 'Horizontal', 'fusion-builder' ),
						'vertical'   => esc_attr__( 'Vertical', 'fusion-builder' ),
					),
					'dependency'  => array(
						array(
							'element'  => 'type',
							'value'    => 'switch',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Handle Movement Control', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls how the viewer interacts with the image handler. The image handle can use Drag & Click, Drag Only, or Hover.', 'fusion-builder' ),
					'param_name'  => 'handle_movement',
					'default'     => '',
					'value'       => array(
						''           => esc_attr__( 'Default', 'fusion-builder' ),
						'drag_click' => __( 'Drag & Click', 'fusion-builder' ),
						'drag'       => esc_attr__( 'Drag Only', 'fusion-builder' ),
						'hover'      => esc_attr__( 'Hover', 'fusion-builder' ),
					),
					'dependency'  => array(
						array(
							'element'  => 'type',
							'value'    => 'switch',
							'operator' => '!=',
						),
					),
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
					'param_name'  => 'hide_on_mobile',
					'value'       => fusion_builder_visibility_options( 'full' ),
					'default'     => fusion_builder_default_visibility( 'array' ),
					'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
					'param_name'  => 'class',
					'value'       => '',
					'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
					'param_name'  => 'id',
					'value'       => '',
					'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
				),
			),
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_image_before_after' );
