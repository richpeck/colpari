<?php

if ( fusion_is_element_enabled( 'fusion_imageframe' ) ) {

	if ( ! class_exists( 'FusionSC_Imageframe' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.0
		 */
		class FusionSC_Imageframe extends Fusion_Element {

			/**
			 * The image-frame counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $imageframe_counter = 1;

			/**
			 * The image data.
			 *
			 * @access private
			 * @since 1.0
			 * @var false|array
			 */
			private $image_data = false;

			/**
			 * The lightbox image data.
			 *
			 * @access private
			 * @since 1.7
			 * @var false|array
			 */
			private $lightbox_image_data = false;

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
				add_filter( 'fusion_attr_image-shortcode', array( $this, 'attr' ) );
				add_filter( 'fusion_attr_image-shortcode-link', array( $this, 'link_attr' ) );

				add_shortcode( 'fusion_imageframe', array( $this, 'render' ) );

			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode paramters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				global $fusion_library, $fusion_settings;

				$defaults = FusionBuilder::set_shortcode_defaults(
					array(
						'align'               => '',
						'alt'                 => '',
						'animation_direction' => 'left',
						'animation_offset'    => $fusion_settings->get( 'animation_offset' ),
						'animation_speed'     => '',
						'animation_type'      => '',
						'blur'                => $fusion_settings->get( 'imageframe_blur' ),
						'bordercolor'         => $fusion_settings->get( 'imgframe_border_color' ),
						'borderradius'        => intval( $fusion_settings->get( 'imageframe_border_radius' ) ) . 'px',
						'bordersize'          => $fusion_settings->get( 'imageframe_border_size' ),
						'class'               => '',
						'gallery_id'          => '',
						'hide_on_mobile'      => fusion_builder_default_visibility( 'string' ),
						'hover_type'          => 'none',
						'id'                  => '',
						'lightbox'            => 'no',
						'lightbox_image'      => '',
						'lightbox_image_id'   => '',
						'link'                => '',
						'linktarget'          => '_self',
						'max_width'           => '',
						'style'               => '',
						'stylecolor'          => $fusion_settings->get( 'imgframe_style_color' ),
						'style_type'          => $fusion_settings->get( 'imageframe_style_type' ),
						'image_id'            => '',
					),
					$args,
					'fusion_imageframe'
				);

				$defaults['blur']         = FusionBuilder::validate_shortcode_attr_value( $defaults['blur'], 'px' );
				$defaults['borderradius'] = FusionBuilder::validate_shortcode_attr_value( $defaults['borderradius'], 'px' );
				$defaults['bordersize']   = FusionBuilder::validate_shortcode_attr_value( $defaults['bordersize'], 'px' );

				if ( ! $defaults['style'] ) {
					$defaults['style'] = $defaults['style_type'];
				}

				if ( $defaults['borderradius'] && 'bottomshadow' === $defaults['style'] ) {
					$defaults['borderradius'] = '0';
				}

				if ( 'round' === $defaults['borderradius'] ) {
					$defaults['borderradius'] = '50%';
				}

				extract( $defaults );

				$this->args = $defaults;

				$border_radius = '';

				if ( '0' != $borderradius && '0px' !== $borderradius ) {
					$border_radius .= "-webkit-border-radius:{$borderradius};-moz-border-radius:{$borderradius};border-radius:{$borderradius};";
				}

				// Alt tag.
				$title = $alt_tag = $image_url = $image_id = $image_width = $image_height = '';

				// The URL is added as element content, but where image ID was not available.
				if ( false === strpos( $content, '<img' ) && $content ) {
					$src = $content;
				} else {

					// Old version, where the img tag was added in element contant.
					preg_match( '/(src=["\'](.*?)["\'])/', $content, $src );
					if ( array_key_exists( '2', $src ) ) {
						$src = $src[2];
					}
				}

				if ( $src ) {

					$src = str_replace( '&#215;', 'x', $src );

					$image_url = $this->args['pic_link'] = $src;

					$lightbox_image = $this->args['pic_link'];
					if ( $this->args['lightbox_image'] ) {
						$lightbox_image = $this->args['lightbox_image'];

						$this->lightbox_image_data = $fusion_library->images->get_attachment_data_by_helper( $this->args['lightbox_image_id'], $this->args['lightbox_image'] );
					}

					$this->image_data = $fusion_library->images->get_attachment_data_by_helper( $this->args['image_id'], $this->args['pic_link'] );

					if ( $this->image_data['url'] ) {
						$image_url = $this->image_data['url'];
					}

					$image_width  = $this->image_data['width'];
					$image_height = $this->image_data['height'];
					$image_id     = $this->image_data['id'];
					$alt_tag      = $this->image_data['alt'];
					$title        = $this->image_data['title_attribute'];

					// For pre 5.0 shortcodes extract the alt tag.
					preg_match( '/(alt=["\'](.*?)["\'])/', $content, $legacy_alt );
					if ( array_key_exists( '2', $legacy_alt ) && '' !== $legacy_alt[2] ) {
						$alt_tag = $legacy_alt[2];
					} elseif ( $alt ) {
						$alt_tag = $alt;
					}

					if ( false !== strpos( $content, 'alt=""' ) && $alt_tag ) {
						$content = str_replace( 'alt=""', $alt_tag, $content );
					} elseif ( false === strpos( $content, 'alt' ) && $alt_tag ) {
						$content = str_replace( '/> ', $alt_tag . ' />', $content );
					}

					if ( 'no' === $lightbox && ! $link ) {
						$title = ' title="' . $title . '"';
					} else {
						$title = '';
					}

					$content = '<img src="' . $image_url . '" width="' . $image_width . '" height="' . $image_height . '" alt="' . $alt_tag . '"' . $title . ' />';
				}

				$img_classes = 'img-responsive';

				if ( ! empty( $image_id ) ) {
					$img_classes .= ' wp-image-' . $image_id;
				}

				// Get custom classes from the img tag.
				preg_match( '/(class=["\'](.*?)["\'])/', $content, $classes );

				if ( ! empty( $classes ) ) {
					$img_classes .= ' ' . $classes[2];
				}

				$img_classes = 'class="' . $img_classes . '"';

				// Add custom and responsive class to the img tag.
				if ( ! empty( $classes ) ) {
					$content = str_replace( $classes[0], $img_classes, $content );
				} else {
					$content = str_replace( '/>', $img_classes . '/>', $content );
				}

				$fusion_library->images->set_grid_image_meta(
					array(
						'layout' => 'large',
						'columns' => '1',
					)
				);

				if ( function_exists( 'wp_make_content_images_responsive' ) ) {
					$content = wp_make_content_images_responsive( $content );
				}

				$fusion_library->images->set_grid_image_meta( array() );

				// Set the lightbox image to the dedicated link if it is set.
				if ( $lightbox_image ) {
					$this->args['pic_link'] = $lightbox_image;
				}

				$output = do_shortcode( $content );

				if ( 'yes' === $lightbox || $link ) {
					$output = '<a ' . FusionBuilder::attributes( 'image-shortcode-link' ) . '>' . do_shortcode( $content ) . '</a>';
				}

				$html = '<span ' . FusionBuilder::attributes( 'image-shortcode' ) . '>' . $output . '</span>';

				if ( 'liftup' === $hover_type || ( 'bottomshadow' === $style && ( 'none' === $hover_type || 'zoomin' === $hover_type || 'zoomout' === $hover_type ) ) ) {
					$stylecolor   = ( '#' === $this->args['stylecolor'][0] ) ? Fusion_Color::new_color( $this->args['stylecolor'] )->get_new( 'alpha', '0.4' )->to_css( 'rgba' ) : Fusion_Color::new_color( $this->args['stylecolor'] )->to_css( 'rgba' );

					if ( 'liftup' === $hover_type ) {
						$wrapper_classes = 'imageframe-liftup';
						$element_styles  = '';

						if ( 'left' === $align ) {
							$wrapper_classes .= ' fusion-imageframe-liftup-left';
						} elseif ( 'right' === $align ) {
							$wrapper_classes .= ' fusion-imageframe-liftup-right';
						}

						if ( $border_radius ) {
							$element_styles   = '.imageframe-liftup.imageframe-' . $this->imageframe_counter . ':before{' . $border_radius . '}';
							$wrapper_classes .= ' imageframe-' . $this->imageframe_counter;
						}

						if ( 'bottomshadow' === $style ) {
							$element_styles  .= '.element-bottomshadow.imageframe-' . $this->imageframe_counter . ':before, .element-bottomshadow.imageframe-' . $this->imageframe_counter . ':after{';
							$element_styles  .= '-webkit-box-shadow: 0 17px 10px ' . $stylecolor . ';box-shadow: 0 17px 10px ' . $stylecolor . ';}';
							$wrapper_classes .= ' fusion-image-frame-bottomshadow image-frame-shadow-' . $this->imageframe_counter;
						}

						if ( $element_styles ) {
							$element_styles = '<style scoped="scoped">' . $element_styles . '</style>';
						}
					} else {
						$wrapper_classes = 'fusion-image-frame-bottomshadow image-frame-shadow-' . $this->imageframe_counter;
						$element_styles  = '';
						$element_styles  = '<style scoped="scoped">.element-bottomshadow.image-frame-shadow-' . $this->imageframe_counter . '{';
						if ( 'left' === $align ) {
							$element_styles .= 'margin-right:25px;float:left;';
						} elseif ( 'right' === $align ) {
							$element_styles  .= 'margin-left:25px;float:right;';
						}
						$element_styles  .= 'display:inline-block}';

						$element_styles  .= '.element-bottomshadow.imageframe-' . $this->imageframe_counter . ':before, .element-bottomshadow.imageframe-' . $this->imageframe_counter . ':after{';
						$element_styles  .= '-webkit-box-shadow: 0 17px 10px ' . $stylecolor . ';box-shadow: 0 17px 10px ' . $stylecolor . ';}';

						$element_styles .= '</style>';
					}

					$wrapper_classes = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $wrapper_classes );

					$wrapper_styles = '';
					if ( $max_width ) {
						$wrapper_styles = 'style="max-width:' . $max_width . ';"';
					}

					$html = '<div ' . FusionBuilder::attributes( $wrapper_classes ) . $wrapper_styles . '>' . $element_styles . $html . '</div>';
				}

				if ( 'center' === $align ) {
					$html = '<div ' . FusionBuilder::attributes( 'imageframe-align-center' ) . '>' . $html . '</div>';
				}

				$this->imageframe_counter++;

				return $html;

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				global $fusion_settings;

				$attr = array(
					'style' => '',
				);

				$visibility_classes_need_set = false;
				$bordercolor  = $this->args['bordercolor'];
				$stylecolor   = ( '#' === $this->args['stylecolor'][0] ) ? Fusion_Color::new_color( $this->args['stylecolor'] )->get_new( 'alpha', '0.3' )->to_css( 'rgba' ) : Fusion_Color::new_color( $this->args['stylecolor'] )->to_css( 'rgba' );
				$blur         = $this->args['blur'];
				$blur_radius  = ( (int) $blur + 4 ) . 'px';
				$bordersize   = $this->args['bordersize'];
				$borderradius = $this->args['borderradius'];
				$style        = $this->args['style'];
				$img_styles   = '';

				if ( '0' != $bordersize && '0px' !== $bordersize ) {
					$img_styles .= "border:{$bordersize} solid {$bordercolor};";
				}

				if ( '0' != $borderradius && '0px' !== $borderradius ) {
					$img_styles .= "-webkit-border-radius:{$borderradius};-moz-border-radius:{$borderradius};border-radius:{$borderradius};";

					if ( '50%' === $borderradius || 100 < intval( $borderradius ) ) {
							$img_styles .= '-webkit-mask-image: -webkit-radial-gradient(circle, white, black);';
					}
				}

				if ( 'glow' === $style ) {
					$img_styles .= "-webkit-box-shadow: 0 0 {$blur} {$stylecolor};box-shadow: 0 0 {$blur} {$stylecolor};";
				} elseif ( 'dropshadow' === $style ) {
					$img_styles .= "-webkit-box-shadow: {$blur} {$blur} {$blur_radius} {$stylecolor};box-shadow: {$blur} {$blur} {$blur_radius} {$stylecolor};";
				}

				if ( $img_styles ) {
					$attr['style'] .= $img_styles;
				}

				$attr['class'] = 'fusion-imageframe imageframe-' . $this->args['style'] . ' imageframe-' . $this->imageframe_counter;

				if ( 'bottomshadow' === $this->args['style'] ) {
					$attr['class'] .= ' element-bottomshadow';
				}

				if ( 'liftup' !== $this->args['hover_type'] && ( 'bottomshadow' !== $this->args['style'] && ( 'zoomin' !== $this->args['hover_type'] || 'zoomout' !== $this->args['hover_type'] ) ) ) {
					$visibility_classes_need_set = true;

					if ( 'left' === $this->args['align'] ) {
						$attr['style'] .= 'margin-right:25px;float:left;';
					} elseif ( 'right' === $this->args['align'] ) {
						$attr['style'] .= 'margin-left:25px;float:right;';
					}
				}

				if ( $this->args['max_width'] && 'liftup' !== $this->args['hover_type'] && 'bottomshadow' !== $this->args['style'] ) {
					$attr['style'] .= 'max-width:' . $this->args['max_width'] . ';';
				}

				if ( 'liftup' !== $this->args['hover_type'] ) {
					$attr['class'] .= ' hover-type-' . $this->args['hover_type'];
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

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

				if ( $visibility_classes_need_set ) {
					return fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );
				}
				return $attr;
			}

			/**
			 * Builds the link attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function link_attr() {

				$attr = array();

				if ( 'yes' === $this->args['lightbox'] ) {
					$attr['href']  = $this->args['pic_link'];
					$attr['class'] = 'fusion-lightbox';

					if ( $this->args['gallery_id'] || '0' === $this->args['gallery_id'] ) {
						$attr['data-rel'] = 'iLightbox[' . $this->args['gallery_id'] . ']';
					} else {
						$attr['data-rel'] = 'iLightbox[' . substr( md5( $this->args['pic_link'] ), 13 ) . ']';
					}

					if ( $this->lightbox_image_data ) {
						$attr['data-caption'] = $this->lightbox_image_data['caption'];
						$attr['data-title']   = $this->lightbox_image_data['title'];
					} elseif ( $this->image_data ) {
						$attr['data-caption'] = $this->image_data['caption'];
						$attr['data-title']   = $this->image_data['title'];
					}

					if ( $this->image_data ) {
						$attr['title']        = $this->image_data['title'];
					}
				} elseif ( $this->args['link'] ) {
					$attr['class']  = 'fusion-no-lightbox';
					$attr['href']   = $this->args['link'];
					$attr['target'] = $this->args['linktarget'];
					$attr['aria-label'] = ( $this->image_data ) ? $this->image_data['title'] : '';
					if ( '_blank' === $this->args['linktarget'] ) {
						$attr['rel'] = 'noopener noreferrer';
					}
				}

				return $attr;

			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Image Frame settings.
			 */
			public function add_options() {

				return array(
					'image_shortcode_section' => array(
						'label'       => esc_html__( 'Image Element', 'fusion-builder' ),
						'description' => '',
						'id'          => 'image_shortcode_section',
						'type'        => 'accordion',
						'fields'      => array(
							'imageframe_style_type' => array(
								'label'       => esc_html__( 'Image Style Type', 'fusion-builder' ),
								'description' => esc_html__( 'Select the style type.', 'fusion-builder' ),
								'id'          => 'imageframe_style_type',
								'default'     => 'none',
								'type'        => 'radio-buttonset',
								'choices'     => array(
									'none'         => esc_attr__( 'None', 'fusion-builder' ),
									'glow'         => esc_attr__( 'Glow', 'fusion-builder' ),
									'dropshadow'   => esc_attr__( 'Drop Shadow', 'fusion-builder' ),
									'bottomshadow' => esc_attr__( 'Bottom Shadow', 'fusion-builder' ),
								),
							),
							'imageframe_blur' => array(
								'label'       => esc_html__( 'Image Glow / Drop Shadow Blur', 'fusion-builder' ),
								'description' => esc_html__( 'Choose the amount of blur added to glow or drop shadow effect.', 'fusion-builder' ),
								'id'          => 'imageframe_blur',
								'default'     => '3',
								'type'        => 'slider',
								'choices'     => array(
									'min'  => '0',
									'max'  => '50',
									'step' => '1',
								),
								'required'    => array(
									array(
										'setting'  => 'imageframe_style_type',
										'operator' => '!=',
										'value'    => 'none',
									),
									array(
										'setting'  => 'imageframe_style_type',
										'operator' => '!=',
										'value'    => 'bottomshadow',
									),
								),
							),
							'imgframe_style_color' => array(
								'label'       => esc_html__( 'Image Style Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the style color for all style types except border. Hex colors will use a subtle auto added alpha level to produce a nice effect.', 'fusion-builder' ),
								'id'          => 'imgframe_style_color',
								'default'     => '#000000',
								'type'        => 'color-alpha',
								'required'    => array(
									array(
										'setting'  => 'imageframe_style_type',
										'operator' => '!=',
										'value'    => 'none',
									),
								),
							),
							'imageframe_border_size' => array(
								'label'       => esc_html__( 'Image Border Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border size of the image.', 'fusion-builder' ),
								'id'          => 'imageframe_border_size',
								'default'     => '0',
								'type'        => 'slider',
								'choices'     => array(
									'min'  => '0',
									'max'  => '50',
									'step' => '1',
								),
							),
							'imgframe_border_color' => array(
								'label'       => esc_html__( 'Image Border Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border color of the image.', 'fusion-builder' ),
								'id'          => 'imgframe_border_color',
								'default'     => '#f6f6f6',
								'type'        => 'color-alpha',
								'required'    => array(
									array(
										'setting'  => 'imageframe_border_size',
										'operator' => '!=',
										'value'    => '0',
									),
								),
							),
							'imageframe_border_radius' => array(
								'label'       => esc_html__( 'Image Border Radius', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border radius of the image.', 'fusion-builder' ),
								'id'          => 'imageframe_border_radius',
								'default'     => '0px',
								'type'        => 'dimension',
								'choices'     => array( 'px', '%' ),
							),
						),
					),
				);
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 1.1
			 * @return void
			 */
			public function add_scripts() {
				Fusion_Dynamic_JS::enqueue_script( 'fusion-animations' );
				Fusion_Dynamic_JS::enqueue_script( 'fusion-lightbox' );
			}
		}
	}

	new FusionSC_Imageframe();

}

/**
 * Map shortcode to Fusion Builder.
 *
 * @since 1.0
 */
function fusion_element_image() {

	global $fusion_settings;

	fusion_builder_map(
		array(
			'name'       => esc_attr__( 'Image', 'fusion-builder' ),
			'shortcode'  => 'fusion_imageframe',
			'icon'       => 'fusiona-image',
			'preview'    => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-image-frame-preview.php',
			'preview_id' => 'fusion-builder-block-module-image-frame-preview-template',
			'params'     => array(
				array(
					'type'        => 'upload',
					'heading'     => esc_attr__( 'Image', 'fusion-builder' ),
					'description' => esc_attr__( 'Upload an image to display.', 'fusion-builder' ),
					'param_name'  => 'element_content',
					'value'       => '',
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Image ID', 'fusion-builder' ),
					'description' => esc_attr__( 'Image ID from Media Library.', 'fusion-builder' ),
					'param_name'  => 'image_id',
					'value'       => '',
					'hidden'      => true,
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Image Max Width', 'fusion-builder' ),
					'description' => esc_attr__( 'Set the maximum width the image should take up. Enter value including any valid CSS unit, ex: 200px. Leave empty to use full image width.', 'fusion-builder' ),
					'param_name'  => 'max_width',
					'value'       => '',
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Style Type', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the style type.', 'fusion-builder' ),
					'param_name'  => 'style_type',
					'value'       => array(
						''             => esc_attr__( 'Default', 'fusion-builder' ),
						'none'         => esc_attr__( 'None', 'fusion-builder' ),
						'glow'         => esc_attr__( 'Glow', 'fusion-builder' ),
						'dropshadow'   => esc_attr__( 'Drop Shadow', 'fusion-builder' ),
						'bottomshadow' => esc_attr__( 'Bottom Shadow', 'fusion-builder' ),
					),
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Glow / Drop Shadow Blur', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose the amount of blur added to glow or drop shadow effect. In pixels.', 'fusion-builder' ),
					'param_name'  => 'blur',
					'value'       => '',
					'min'         => '0',
					'max'         => '50',
					'step'        => '1',
					'default'     => $fusion_settings->get( 'imageframe_blur' ),
					'dependency'  => array(
						array(
							'element'  => 'style_type',
							'value'    => 'none',
							'operator' => '!=',
						),
						array(
							'element'  => 'style_type',
							'value'    => 'bottomshadow',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Style Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the style color for all style types except border. Hex colors will use a subtle auto added alpha level to produce a nice effect.', 'fusion-builder' ),
					'param_name'  => 'stylecolor',
					'value'       => '',
					'default'     => $fusion_settings->get( 'imgframe_style_color' ),
					'dependency'  => array(
						array(
							'element'  => 'style_type',
							'value'    => 'none',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Hover Type', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the hover effect type.', 'fusion-builder' ),
					'param_name'  => 'hover_type',
					'value'       => array(
						'none'    => esc_attr__( 'None', 'fusion-builder' ),
						'zoomin'  => esc_attr__( 'Zoom In', 'fusion-builder' ),
						'zoomout' => esc_attr__( 'Zoom Out', 'fusion-builder' ),
						'liftup'  => esc_attr__( 'Lift Up', 'fusion-builder' ),
					),
					'default'     => 'none',
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Border Size', 'fusion-builder' ),
					'description' => esc_attr__( 'In pixels.', 'fusion-builder' ),
					'param_name'  => 'bordersize',
					'value'       => '',
					'min'         => '0',
					'max'         => '50',
					'step'        => '1',
					'default'     => $fusion_settings->get( 'imageframe_border_size' ),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the border color. ', 'fusion-builder' ),
					'param_name'  => 'bordercolor',
					'value'       => '',
					'default'     => $fusion_settings->get( 'imgframe_border_color' ),
					'dependency'  => array(
						array(
							'element'  => 'bordersize',
							'value'    => '0',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Border Radius', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the image border radius. In pixels (px), ex: 1px, or "round". ', 'fusion-builder' ),
					'param_name'  => 'borderradius',
					'value'       => '',
					'dependency'  => array(
						array(
							'element'  => 'style_type',
							'value'    => 'bottomshadow',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Align', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose how to align the image.', 'fusion-builder' ),
					'param_name'  => 'align',
					'value'       => array(
						'none'   => esc_attr__( 'Text Flow', 'fusion-builder' ),
						'left'   => esc_attr__( 'Left', 'fusion-builder' ),
						'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						'center' => esc_attr__( 'Center', 'fusion-builder' ),
					),
					'default'     => 'none',
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Image lightbox', 'fusion-builder' ),
					'description' => esc_attr__( 'Show image in lightbox. Lightbox must be enabled in Theme Options or the image will open up in the same tab by itself.', 'fusion-builder' ),
					'param_name'  => 'lightbox',
					'value'       => array(
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'default'     => 'no',
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Gallery ID', 'fusion-builder' ),
					'description' => esc_attr__( 'Set a name for the lightbox gallery this image should belong to.', 'fusion-builder' ),
					'param_name'  => 'gallery_id',
					'value'       => '',
					'dependency'  => array(
						array(
							'element'  => 'lightbox',
							'value'    => 'no',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'upload',
					'heading'     => esc_attr__( 'Lightbox Image', 'fusion-builder' ),
					'description' => esc_attr__( 'Upload an image that will show up in the lightbox.', 'fusion-builder' ),
					'param_name'  => 'lightbox_image',
					'value'       => '',
					'dependency'  => array(
						array(
							'element'  => 'lightbox',
							'value'    => 'no',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Lightbox Image ID', 'fusion-builder' ),
					'description' => esc_attr__( 'Lightbox Image ID from Media Library.', 'fusion-builder' ),
					'param_name'  => 'lightbox_image_id',
					'value'       => '',
					'hidden'      => true,
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Image Alt Text', 'fusion-builder' ),
					'description' => esc_attr__( 'The alt attribute provides alternative information if an image cannot be viewed.', 'fusion-builder' ),
					'param_name'  => 'alt',
					'value'       => '',
				),
				array(
					'type'        => 'link_selector',
					'heading'     => esc_attr__( 'Picture Link URL', 'fusion-builder' ),
					'description' => esc_attr__( 'Add the URL the picture will link to, ex: http://example.com.', 'fusion-builder' ),
					'param_name'  => 'link',
					'value'       => '',
					'dependency'  => array(
						array(
							'element'  => 'lightbox',
							'value'    => 'yes',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Link Target', 'fusion-builder' ),
					'description' => __( '_self = open in same window<br />_blank = open in new window.', 'fusion-builder' ),
					'param_name'  => 'linktarget',
					'value'       => array(
						'_self'  => esc_attr__( '_self', 'fusion-builder' ),
						'_blank' => esc_attr__( '_blank', 'fusion-builder' ),
					),
					'default'     => '_self',
					'dependency'  => array(
						array(
							'element'  => 'lightbox',
							'value'    => 'yes',
							'operator' => '!=',
						),
						array(
							'element'  => 'link',
							'value'    => '',
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
					'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
					'param_name'  => 'class',
					'value'       => '',
					'group'       => esc_attr__( 'General', 'fusion-builder' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
					'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
					'param_name'  => 'id',
					'value'       => '',
					'group'       => esc_attr__( 'General', 'fusion-builder' ),
				),
			),
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_image' );
