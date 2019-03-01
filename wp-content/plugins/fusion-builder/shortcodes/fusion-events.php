<?php

if ( fusion_is_element_enabled( 'fusion_events' ) ) {

	if ( ! class_exists( 'FusionSC_FusionEvents' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.0
		 */
		class FusionSC_FusionEvents extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * The events counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $fusion_events_counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_shortcode( 'fusion_events', array( $this, 'render' ) );

				add_filter( 'fusion_attr_events-shortcode', array( $this, 'attr' ) );
				add_filter( 'fusion_attr_events-shortcode-columns', array( $this, 'column_attr' ) );
				add_filter( 'fusion_events_shortcode_content', array( $this, 'get_post_content' ), 10, 3 );
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

				$html     = '';
				$defaults = FusionBuilder::set_shortcode_defaults(
					array(
						'column_spacing'  => ( isset( $args['column_spacing'] ) && '' === $args['column_spacing'] ) ? $fusion_settings->get( 'events_column_spacing' ) : '-1',
						'content_length'  => ( '' !== $fusion_settings->get( 'events_content_length' ) ) ? $fusion_settings->get( 'events_content_length' ) : 'no_text',
						'excerpt_length'  => ( '' !== $fusion_settings->get( 'excerpt_length_events' ) ) ? $fusion_settings->get( 'excerpt_length_events' ) : 55,
						'hide_on_mobile'  => fusion_builder_default_visibility( 'string' ),
						'class'           => '',
						'id'              => '',
						'cat_slug'        => '',
						'columns'         => '4',
						'number_posts'    => ( '' !== $fusion_settings->get( 'events_per_page' ) ) ? $fusion_settings->get( 'events_per_page' ) : '4',
						'pagination'      => 'no',
						'past_events'     => 'no',
						'picture_size'    => 'cover',
						'strip_html'      => ( '' !== $fusion_settings->get( 'events_strip_html_excerpt' ) ) ? $fusion_settings->get( 'events_strip_html_excerpt' ) : 'yes',
					),
					$args,
					'fusion_events'
				);

				$theme_option_content_padding = $fusion_settings->get( 'events_content_padding' );
				$padding_values = array();

				$padding_values['top']     = ( isset( $args['padding_top'] ) && '' !== $args['padding_top'] ) ? $args['padding_top'] : Fusion_Sanitize::size( $theme_option_content_padding['top'] );
				$padding_values['right']   = ( isset( $args['padding_right'] ) && '' !== $args['padding_right'] ) ? $args['padding_right'] : Fusion_Sanitize::size( $theme_option_content_padding['right'] );
				$padding_values['bottom']  = ( isset( $args['padding_bottom'] ) && '' !== $args['padding_bottom'] ) ? $args['padding_bottom'] : Fusion_Sanitize::size( $theme_option_content_padding['bottom'] );
				$padding_values['left']    = ( isset( $args['padding_left'] ) && '' !== $args['padding_left'] ) ? $args['padding_left'] : Fusion_Sanitize::size( $theme_option_content_padding['left'] );
				$content_padding = implode( ' ', $padding_values );

				$this->args = $defaults;

				extract( $defaults );

				if ( class_exists( 'Tribe__Events__Main' ) ) {

					// Check if there is paged content.
					$paged = 1;
					if ( 'no' !== $defaults['pagination'] ) {
						$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
						if ( is_front_page() ) {
							$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
						}
					}

					$args = array(
						'post_type'      => 'tribe_events',
						'paged'          => $paged,
						'posts_per_page' => $number_posts,
						'order'          => 'ASC',
					);

					if ( 'yes' === $defaults['past_events'] ) {
						$args['eventDisplay'] = 'custom';
					}

					if ( $cat_slug ) {
						$terms = explode( ',', $cat_slug );
						$args['tax_query'] = array(
							array(
								'taxonomy'  => 'tribe_events_cat',
								'field'     => 'slug',
								'terms'     => array_map( 'trim', $terms ),
							),
						);
					}

					wp_reset_postdata();
					$events = fusion_cached_query( $args );

					if ( ! $events->have_posts() ) {
						$this->fusion_events_counter++;
						return fusion_builder_placeholder( 'tribe_events', 'events' );
					}

					if ( $events->have_posts() ) {
						$html .= '<div ' . FusionBuilder::attributes( 'events-shortcode' ) . '>';
						$html .= '<div class="fusion-events-wrapper" data-pages="' . $events->max_num_pages . '">';
						$i       = 1;
						$last    = false;
						$columns = (int) $columns;

						while ( $events->have_posts() ) {
							$events->the_post();

							if ( $i == $columns ) {
								$last = true;
							}

							if ( $i > $columns ) {
								$i    = 1;
								$last = false;
							}

							if ( 1 == $columns ) {
								$last = true;
							}

							$html .= '<div ' . FusionBuilder::attributes( 'events-shortcode-columns', $last ) . '>';
							$html .= '<div class="fusion-column-wrapper">';
							$thumb_id = get_post_thumbnail_id();
							$thumb_link = wp_get_attachment_image_src( $thumb_id, 'full', true );
							$thumb_url = '';

							if ( has_post_thumbnail( get_the_ID() ) ) {
								$thumb_url = $thumb_link[0];
							} elseif ( class_exists( 'Tribe__Events__Pro__Main' ) ) {
								$thumb_url = esc_url( trailingslashit( Tribe__Events__Pro__Main::instance()->pluginUrl ) . 'src/resources/images/tribe-related-events-placeholder.png' );
							}

							$img_class = ( has_post_thumbnail( get_the_ID() ) ) ? '' : 'fusion-events-placeholder';

							if ( $thumb_url ) {
								$title = the_title_attribute(
									array(
										'echo' => false,
										'post' => get_the_ID(),
									)
								);

								$thumb_img = '<img class="' . $img_class . '" src="' . $thumb_url . '" alt="' . $title . '" />';
								if ( has_post_thumbnail( get_the_ID() ) && 'auto' == $picture_size ) {
									$thumb_img = get_the_post_thumbnail( get_the_ID(), 'full' );
								}
								$thumb_bg = '<span class="tribe-events-event-image" style="background-image: url(' . $thumb_url . '); -webkit-background-size: cover; background-size: cover; background-position: center center;"></span>';
							}
							$html .= '<div class="fusion-events-thumbnail hover-type-' . $fusion_settings->get( 'ec_hover_type' ) . '">';
							$html .= '<a href="' . get_the_permalink() . '" class="url" rel="bookmark" aria-label="' . the_title_attribute( array( 'echo' => false ) ) . '">';

							if ( $thumb_url ) {
								$html .= ( 'auto' == $picture_size ) ? $thumb_img : $thumb_bg;
							} else {
								ob_start();
								/**
								 * The avada_placeholder_image hook.
								 *
								 * @hooked fusion_render_placeholder_image - 10 (outputs the HTML for the placeholder image)
								 */
								do_action( 'fusion_render_placeholder_image', 'fixed' );

								$placeholder = ob_get_clean();
								$html .= str_replace( 'fusion-placeholder-image', ' fusion-placeholder-image tribe-events-event-image', $placeholder );
							}

							$html .= '</a>';
							$html .= '</div>';
							$html .= '<div class="fusion-events-content-wrapper" style="padding:' . $content_padding . ';">';
							$html .= '<div class="fusion-events-meta">';
							$html .= '<h2><a href="' . get_the_permalink() . '" class="url" rel="bookmark">' . get_the_title() . '</a></h2>';
							$html .= '<h4>' . tribe_events_event_schedule_details() . '</h4>';
							$html .= '</div>';

							if ( 'no_text' !== $defaults['content_length'] ) {
								$html .= '<div class="fusion-events-content">';
								$html .= apply_filters( 'fusion_events_shortcode_content', $defaults['content_length'], $defaults['excerpt_length'], $defaults['strip_html'] );
								$html .= '</div>';
							}

							$html .= '</div>';
							$html .= '</div>';
							$html .= '</div>';

							if ( $last && 'no' === $defaults['pagination'] ) {
								$html .= '<div class="fusion-clearfix"></div>';
							}
							$i++;
						}

						// @codingStandardsIgnoreLine
						wp_reset_query();

						if ( 'no' === $defaults['pagination'] ) {
							$html .= '<div class="fusion-clearfix"></div>';
						}

						$html .= '</div>';

						// Pagination.
						$pagination_type = ( '' !== $defaults['pagination'] ) ? $defaults['pagination'] : 'no';
						$pagination_html = '';

						if ( 'no' !== $pagination_type && 1 < esc_attr( $events->max_num_pages ) ) {

							// Pagination is set to "load more" button.
							if ( 'load_more_button' === $pagination_type && -1 !== intval( $number_posts ) ) {
								$button_margin = '';
								if ( '-1' !== $this->args['column_spacing'] ) {
									$button_margin  = 'margin-left: ' . ( $this->args['column_spacing'] / 2 ) . 'px;';
									$button_margin .= 'margin-right: ' . ( $this->args['column_spacing'] / 2 ) . 'px;';
									$style  = '<style type="text/css">';
									$style .= '.fusion-events-shortcode.fusion-events-shortcode-' . $this->fusion_events_counter . ' .fusion-load-more-button {' . $button_margin . '}';
									$style .= '.fusion-events-shortcode.fusion-events-shortcode-' . $this->fusion_events_counter . ' .fusion-loading-container {' . $button_margin . '}';
									$style .= '</style>';
									$pagination_html .= $style;
								}
								$pagination_html .= '<div class="fusion-load-more-button fusion-events-button fusion-clearfix">' . apply_filters( 'avada_load_more_events_name', esc_attr__( 'Load More Events', 'fusion-builder' ) ) . '</div>';
							}

							$infinite_pagination = false;
							if ( 'load_more_button' === $pagination_type || 'infinite' === $pagination_type ) {
								$infinite_pagination = true;
							}

							$pagination_html .= fusion_pagination( $events->max_num_pages, $fusion_settings->get( 'pagination_range' ), $events, $infinite_pagination, true );
						}

						$html .= $pagination_html;

						$html .= '</div>';
					}

					$this->fusion_events_counter++;

					return $html;
				}
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.6
			 * @return array
			 */
			public function attr() {
				$attr = array(
					'class' => 'fusion-events-shortcode fusion-events-shortcode-' . $this->fusion_events_counter,
				);

				if ( 'no' !== $this->args['pagination'] ) {
					$attr['class'] .= ' fusion-events-pagination-' . str_replace( '_', '-', $this->args['pagination'] );
				}

				// Add custom class.
				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				// Add custom id.
				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				if ( '-1' !== $this->args['column_spacing'] ) {
					$attr['style']  = 'margin-left: -' . ( $this->args['column_spacing'] / 2 ) . 'px;';
					$attr['style'] .= 'margin-right: -' . ( $this->args['column_spacing'] / 2 ) . 'px;';
				}

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				return $attr;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.6
			 * @param bool $last Holds value for last column in a row.
			 * @return array
			 */
			public function column_attr( $last = false ) {
				$attr = array(
					'class' => 'fusion-events-post',
				);

				$fusion_spacing = ( '-1' !== $this->args['column_spacing'] ) ? 'fusion-spacing-no' : 'fusion-spacing-yes';
				$attr['class'] .= ' ' . $fusion_spacing;

				$columns = (int) $this->args['columns'];

				switch ( $columns ) {
					case '1':
						$column_class = 'full-one';
						break;
					case '2':
						$column_class = 'one-half';
						break;
					case '3':
						$column_class = 'one-third';
						break;
					case '4':
						$column_class = 'one-fourth';
						break;
					case '5':
						$column_class = 'one-fifth';
						break;
					case '6':
						$column_class = 'one-sixth';
						break;
				}

				$attr['class'] .= ' fusion-' . $column_class . ' fusion-layout-column';
				$attr['class'] .= ( $last ) ? ' fusion-column-last' : '';

				if ( '-1' !== $this->args['column_spacing'] ) {
					$attr['style']  = 'padding:' . ( $this->args['column_spacing'] / 2 ) . 'px';
				}

				return $attr;
			}

			/**
			 * Echoes the post-content.
			 *
			 * @access public
			 * @since 1.6
			 * @param string $content_length Display excerpt / full content.
			 * @param int    $excerpt_length Excerpt length in words.
			 * @param string $strip_html     Yes/no option to strip html.
			 * @return string Excerpt / Full content of event.
			 */
			public function get_post_content( $content_length = 'excerpt', $excerpt_length = 55, $strip_html = 'yes' ) {
				if ( 'no_text' !== $content_length ) {
					$excerpt = 'no';
					if ( 'excerpt' === strtolower( $content_length ) ) {
						$excerpt = 'yes';
					}

					return fusion_get_post_content( '', $excerpt, $excerpt_length, $strip_html );
				}
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.6
			 * @return array $sections Events settings.
			 */
			public function add_options() {
				return array(
					'events_shortcode_section' => array(
						'label'       => esc_attr__( 'Events Element', 'fusion-builder' ),
						'description' => '',
						'id'          => 'events_shortcode_section',
						'default'     => '',
						'type'        => 'accordion',
						'fields'      => array(
							'events_per_page' => array(
								'label'       => esc_attr__( 'Number of Events Per Page', 'fusion-core' ),
								'description' => esc_attr__( 'Controls the number of events displayed per page for events element. Set to -1 to display all. Set to 0 to use the number of posts from Settings > Reading.', 'fusion-core' ),
								'id'          => 'events_per_page',
								'default'     => '4',
								'type'        => 'slider',
								'choices'     => array(
									'min'  => '-1',
									'max'  => '50',
									'step' => '1',
								),
							),
							'events_column_spacing' => array(
								'label'       => esc_attr__( 'Column Spacing', 'fusion-core' ),
								'description' => esc_attr__( 'Controls the column spacing for events items.', 'fusion-core' ),
								'id'          => 'events_column_spacing',
								'default'     => '40',
								'type'        => 'slider',
								'choices'     => array(
									'min'  => '-1',
									'max'  => '300',
									'step' => '1',
								),
							),
							'events_content_padding' => array(
								'label'       => esc_attr__( 'Events Content Padding', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the top/right/bottom/left padding of the events contents.', 'fusion-builder' ),
								'id'          => 'events_content_padding',
								'choices'     => array(
									'top'     => true,
									'bottom'  => true,
									'left'    => true,
									'right'   => true,
									'units'   => array( 'px', '%' ),
								),
								'default'     => array(
									'top'     => '20px',
									'bottom'  => '20px',
									'left'    => '20px',
									'right'   => '20px',
								),
								'type'        => 'spacing',
							),
							'events_content_length' => array(
								'label'       => esc_attr__( 'Events Text Display', 'fusion-core' ),
								'description' => esc_attr__( 'Choose how to display the post excerpt for events elements.', 'fusion-core' ),
								'id'          => 'events_content_length',
								'default'     => 'no_text',
								'type'        => 'radio-buttonset',
								'choices'     => array(
									'no_text'      => esc_attr__( 'No Text', 'fusion-core' ),
									'excerpt'      => esc_attr__( 'Excerpt', 'fusion-core' ),
									'full_content' => esc_attr__( 'Full Content', 'fusion-core' ),
								),
							),
							'excerpt_length_events' => array(
								'label'       => esc_attr__( 'Excerpt Length', 'fusion-core' ),
								'description' => esc_attr__( 'Controls the number of words in the excerpts for events elements.', 'fusion-core' ),
								'id'          => 'excerpt_length_events',
								'default'     => '55',
								'type'        => 'slider',
								'choices'     => array(
									'min'  => '0',
									'max'  => '500',
									'step' => '1',
								),
								'required'    => array(
									array(
										'setting'  => 'events_content_length',
										'operator' => '==',
										'value'    => 'excerpt',
									),
								),
							),
							'events_strip_html_excerpt' => array(
								'label'       => esc_attr__( 'Strip HTML from Excerpt', 'fusion-core' ),
								'description' => esc_attr__( 'Turn on to strip HTML content from the excerpt for events elements.', 'fusion-core' ),
								'id'          => 'events_strip_html_excerpt',
								'default'     => '1',
								'type'        => 'switch',
								'required'    => array(
									array(
										'setting'  => 'events_content_length',
										'operator' => '==',
										'value'    => 'excerpt',
									),
								),
							),
						),
					),
				);
			}

			/**
			 * Builds the dynamic styling.
			 *
			 * @access public
			 * @since 1.1
			 * @return array
			 */
			public function add_styling() {

				global $wp_version, $content_media_query, $six_fourty_media_query, $three_twenty_six_fourty_media_query, $ipad_portrait_media_query, $content_min_media_query, $fusion_library, $fusion_settings, $dynamic_css_helpers;

				$elements = array(
					'.fusion-events-shortcode .fusion-layout-column',
				);
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = $fusion_library->sanitize->color( $fusion_settings->get( 'timeline_bg_color' ) );

				$elements = array(
					'.fusion-events-shortcode .fusion-layout-column .fusion-column-wrapper',
					'.fusion-events-shortcode .fusion-events-thumbnail',
				);
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = $fusion_library->sanitize->color( $fusion_settings->get( 'timeline_color' ) );

				return $css;

			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 1.1
			 * @return void
			 */
			public function add_scripts() {

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-events',
					FusionBuilder::$js_folder_url . '/general/fusion-events.js',
					FusionBuilder::$js_folder_path . '/general/fusion-events.js',
					array( 'jquery', 'fusion-equal-heights', 'images-loaded', 'packery' ),
					'1',
					true
				);
			}
		}
	}

	new FusionSC_FusionEvents();

}

/**
 * Map shortcode to Fusion Builder
 *
 * @since 1.0
 */
function fusion_element_events() {
	global $fusion_settings;

	if ( class_exists( 'Tribe__Events__Main' ) ) {
		fusion_builder_map(
			array(
				'name'      => esc_attr__( 'Events', 'fusion-builder' ),
				'shortcode' => 'fusion_events',
				'icon'      => 'fusiona-tag',
				'params'    => array(
					array(
						'type'        => 'multiple_select',
						'heading'     => esc_attr__( 'Categories', 'fusion-builder' ),
						'description' => esc_attr__( 'Select a category or leave blank for all.', 'fusion-builder' ),
						'param_name'  => 'cat_slug',
						'value'       => fusion_builder_shortcodes_categories( 'tribe_events_cat' ),
						'default'     => '',
					),
					array(
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Display Past Events', 'fusion-builder' ),
						'description' => __( 'Turn on if you want the past events to be displayed.', 'fusion-builder' ),
						'param_name'  => 'past_events',
						'value'       => array(
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						),
						'default' => 'no',
					),
					array(
						'type'        => 'range',
						'heading'     => esc_attr__( 'Number of Events', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the number of events to display.', 'fusion-builder' ),
						'param_name'  => 'number_posts',
						'value'       => '',
						'min'         => '-1',
						'max'         => '25',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'events_per_page' ),
					),
					array(
						'type'        => 'range',
						'heading'     => esc_attr__( 'Maximum Columns', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the number of max columns to display.', 'fusion-builder' ),
						'param_name'  => 'columns',
						'value'       => '4',
						'min'         => '1',
						'max'         => '6',
						'step'        => '1',
					),
					array(
						'type'        => 'range',
						'heading'     => esc_attr__( 'Column Spacing', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the column spacing for events items. Setting to -1 will keep the default 4% column spacing.', 'fusion-builder' ),
						'param_name'  => 'column_spacing',
						'value'       => '',
						'min'         => '-1',
						'max'         => '300',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'events_column_spacing' ),
					),
					array(
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Picture Size', 'fusion-builder' ),
						'description' => __( 'cover = image will scale to cover the container, <br />auto = width and height will adjust to the image.', 'fusion-builder' ),
						'param_name'  => 'picture_size',
						'value'       => array(
							'cover' => esc_attr__( 'Cover', 'fusion-builder' ),
							'auto'  => esc_attr__( 'Auto', 'fusion-builder' ),
						),
						'default' => 'cover',
					),
					array(
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Content Padding ', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the padding for the event contents. Enter values including any valid CSS unit, ex: 20px, 20px, 20px, 20px.', 'fusion-builder' ),
						'param_name'       => 'content_padding',
						'value'            => array(
							'padding_top'    => '',
							'padding_right'  => '',
							'padding_bottom' => '',
							'padding_left'   => '',
						),
					),
					array(
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Text Display', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how to display the post excerpt.', 'fusion-builder' ),
						'param_name'  => 'content_length',
						'value'       => array(
							''             => esc_attr__( 'Default', 'fusion-builder' ),
							'no_text'      => esc_attr__( 'No Text', 'fusion-builder' ),
							'excerpt'      => esc_attr__( 'Excerpt', 'fusion-builder' ),
							'full_content' => esc_attr__( 'Full Content', 'fusion-builder' ),
						),
					),
					array(
						'type'        => 'range',
						'heading'     => esc_attr__( 'Excerpt Length', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert the number of words/characters you want to show in the excerpt.', 'fusion-builder' ),
						'param_name'  => 'excerpt_length',
						'value'       => '',
						'min'         => '0',
						'max'         => '500',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'excerpt_length_events' ),
						'dependency'  => array(
							array(
								'element'  => 'content_length',
								'value'    => 'excerpt',
								'operator' => '==',
							),
						),
					),
					array(
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Strip HTML', 'fusion-builder' ),
						'description' => esc_attr__( 'Strip HTML from the post excerpt.', 'fusion-builder' ),
						'param_name'  => 'strip_html',
						'value'       => array(
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						),
						'dependency'  => array(
							array(
								'element'  => 'content_length',
								'value'    => 'excerpt',
								'operator' => '==',
							),
						),
					),
					array(
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Pagination Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the type of pagination.', 'fusion-builder' ),
						'param_name'  => 'pagination',
						'default'     => 'no',
						'value'       => array(
							'no'               => esc_attr__( 'No Pagination', 'fusion-builder' ),
							'pagination'       => esc_attr__( 'Pagination', 'fusion-builder' ),
							'infinite'         => esc_attr__( 'Infinite Scrolling', 'fusion-builder' ),
							'load_more_button' => esc_attr__( 'Load More Button', 'fusion-builder' ),
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
}
add_action( 'wp_loaded', 'fusion_element_events' );
