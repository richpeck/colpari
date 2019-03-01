<?php

if ( fusion_is_element_enabled( 'fusion_chart' ) ) {

	if ( ! class_exists( 'FusionSC_Chart' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.5
		 */
		class FusionSC_Chart extends Fusion_Element {

			/**
			 * Chart SC counter.
			 *
			 * @access protected
			 * @since 1.5
			 * @var int
			 */
			protected $chart_sc_counter = 1;

			/**
			 * The chart dataset counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $chart_dataset_counter = 0;

			/**
			 * Parent SC arguments.
			 *
			 * @access protected
			 * @since 1.5
			 * @var array
			 */
			protected $parent_args;

			/**
			 * Child SC arguments.
			 *
			 * @access protected
			 * @since 1.5
			 * @var array
			 */
			protected $child_args;

			/**
			 * Child legend text colors.
			 *
			 * @access protected
			 * @since 1.5
			 * @var array
			 */
			protected $child_legend_text_colors = array();

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_chart-shortcode', array( $this, 'parent_attr' ) );
				add_shortcode( 'fusion_chart', array( $this, 'render_parent' ) );

				add_filter( 'fusion_attr_chart-dataset-shortcode', array( $this, 'child_attr' ) );
				add_shortcode( 'fusion_chart_dataset', array( $this, 'render_child' ) );
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
			public function render_parent( $args, $content = '' ) {
				global $fusion_settings;

				$defaults = FusionBuilder::set_shortcode_defaults(
					array(
						'hide_on_mobile'           => fusion_builder_default_visibility( 'string' ),
						'title'                    => '',
						'chart_padding'            => '',
						'chart_type'               => '',
						'bg_colors'                => '',
						'border_colors'            => '',
						'chart_legend_position'    => $fusion_settings->get( 'chart_legend_position' ),
						'legend_labels'            => '',
						'legend_text_colors'       => '',
						'x_axis_labels'            => '',
						'x_axis_label'             => '',
						'y_axis_label'             => '',
						'show_tooltips'            => $fusion_settings->get( 'chart_show_tooltips' ),
						'chart_border_size'        => 1,
						'chart_border_type'        => 'smooth',
						'chart_fill'               => 'start',
						'chart_point_style'        => '',
						'chart_point_size'         => '',
						'chart_point_bg_color'     => '',
						'chart_point_border_color' => '',
						'chart_bg_color'           => $fusion_settings->get( 'chart_bg_color' ),
						'chart_axis_text_color'    => $fusion_settings->get( 'chart_axis_text_color' ),
						'chart_gridline_color'     => $fusion_settings->get( 'chart_gridline_color' ),
						'class'                    => '',
						'id'                       => '',
					),
					$args,
					'fusion_chart'
				);

				$defaults['chart_padding'] = array(
					'top'    => isset( $args['padding_top'] ) && '' !== $args['padding_top'] ? $args['padding_top'] : 0,
					'right'  => isset( $args['padding_right'] ) && '' !== $args['padding_right'] ? $args['padding_right'] : 0,
					'bottom' => isset( $args['padding_bottom'] ) && '' !== $args['padding_bottom'] ? $args['padding_bottom'] : 0,
					'left'   => isset( $args['padding_left'] ) && '' !== $args['padding_left'] ? $args['padding_left'] : 0,
				);

				$this->parent_args = $defaults;

				$html  = '<div ' . FusionBuilder::attributes( 'chart-shortcode' ) . '>';
				$html .= do_shortcode( $content );

				if ( '' !== $this->parent_args['title'] ) {
					$html .= '<h4 class="fusion-chart-title">' . esc_html( $this->parent_args['title'] ) . '</h4>';
				}

				$html .= '<div class="fusion-chart-inner">';
				$html .= '<div class="fusion-chart-wrap">';
				$html .= '<canvas></canvas>';
				$html .= '</div>';

				if ( 'off' !== $this->parent_args['chart_legend_position'] ) {
					$html .= '<div class="fusion-chart-legend-wrap"></div>';
				}

				$html .= '</div>';
				$html .= '</div>';

				$styles = '';

				if ( '' !== $this->parent_args['chart_bg_color'] ) {
					$styles .= '#fusion-chart-' . $this->chart_sc_counter . '{background-color: ' . $this->parent_args['chart_bg_color'] . ';}';
				}

				if ( ! empty( $this->parent_args['chart_padding'] ) && is_array( $this->parent_args['chart_padding'] ) ) {
					$styles .= '#fusion-chart-' . $this->chart_sc_counter . '{padding: ' . implode( ' ', $this->parent_args['chart_padding'] ) . ';}';
				}

				if ( '' !== $this->parent_args['legend_text_colors'] ) {
					if ( 'pie' === $this->parent_args['chart_type'] || 'doughnut' === $this->parent_args['chart_type'] || 'polarArea' === $this->parent_args['chart_type'] || ( ( 'bar' === $this->parent_args['chart_type'] || 'horizontalBar' === $this->parent_args['chart_type'] ) && 1 === $this->chart_dataset_counter ) ) {
						$colors = explode( '|', $this->parent_args['legend_text_colors'] );
					} else {
						$colors = $this->child_legend_text_colors;
					}

					$color_count = count( $colors );
					for ( $i = 0; $i < $color_count; $i++ ) {
						if ( '' !== $colors[ $i ] ) {
							$styles .= '#fusion-chart-' . $this->chart_sc_counter . ' .fusion-chart-legend-wrap li:nth-child(' . ( $i + 1 ) . ') span{color: ' . $colors[ $i ] . ';}';
						}
					}
				}

				if ( $styles ) {
					$styles = '<style type="text/css" scoped="scoped">' . $styles . '</style>';
				}

				$this->chart_sc_counter++;

				// Reset child element counter.
				$this->chart_dataset_counter    = 0;
				$this->child_legend_text_colors = array();

				return $styles . $html;
			}

			/**
			 * Builds the prent attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function parent_attr() {

				$attr = fusion_builder_visibility_atts(
					$this->parent_args['hide_on_mobile'],
					array(
						'id'    => 'fusion-chart-' . $this->chart_sc_counter,
						'class' => 'fusion-chart',
					)
				);

				if ( $this->parent_args['chart_type'] ) {
					$attr['data-type'] = $this->parent_args['chart_type'];
				}

				if ( $this->parent_args['chart_legend_position'] && 'off' !== $this->parent_args['chart_legend_position'] ) {
					$attr['class'] .= ' legend-' . $this->parent_args['chart_legend_position'];

					$attr['data-chart_legend_position'] = $this->parent_args['chart_legend_position'];
				}

				if ( $this->parent_args['x_axis_labels'] ) {
					$attr['data-x_axis_labels'] = $this->parent_args['x_axis_labels'];
				}

				if ( $this->parent_args['x_axis_label'] ) {
					$attr['data-x_axis_label'] = $this->parent_args['x_axis_label'];
				}

				if ( $this->parent_args['y_axis_label'] ) {
					$attr['data-y_axis_label'] = $this->parent_args['y_axis_label'];
				}

				if ( $this->parent_args['show_tooltips'] ) {
					$attr['data-show_tooltips'] = $this->parent_args['show_tooltips'];
				}

				if ( $this->parent_args['bg_colors'] ) {
					$attr['data-bg_colors'] = $this->parent_args['bg_colors'];
				}

				if ( $this->parent_args['border_colors'] ) {
					$attr['data-border_colors'] = $this->parent_args['border_colors'];
				}

				if ( $this->parent_args['legend_labels'] ) {
					$attr['data-legend_labels'] = $this->parent_args['legend_labels'];
				}

				if ( $this->parent_args['chart_border_size'] ) {
					$attr['data-border_size'] = $this->parent_args['chart_border_size'];
				}

				if ( $this->parent_args['chart_border_type'] ) {
					$attr['data-border_type'] = $this->parent_args['chart_border_type'];
				}

				if ( $this->parent_args['chart_fill'] ) {
					$attr['data-chart_fill'] = $this->parent_args['chart_fill'];
				}

				if ( $this->parent_args['chart_point_style'] ) {
					$attr['data-chart_point_style'] = $this->parent_args['chart_point_style'];
				}

				if ( $this->parent_args['chart_point_size'] ) {
					$attr['data-chart_point_size'] = $this->parent_args['chart_point_size'];
				}

				if ( $this->parent_args['chart_point_bg_color'] ) {
					$attr['data-chart_point_bg_color'] = $this->parent_args['chart_point_bg_color'];
				}

				if ( $this->parent_args['chart_point_border_color'] ) {
					$attr['data-chart_point_border_color'] = $this->parent_args['chart_point_border_color'];
				}

				if ( $this->parent_args['chart_axis_text_color'] ) {
					$attr['data-chart_axis_text_color'] = $this->parent_args['chart_axis_text_color'];
				}

				if ( $this->parent_args['chart_gridline_color'] ) {
					$attr['data-chart_gridline_color'] = $this->parent_args['chart_gridline_color'];
				}

				if ( $this->parent_args['class'] ) {
					$attr['class'] .= ' ' . $this->parent_args['class'];
				}

				if ( $this->parent_args['id'] ) {
					$attr['id'] = $this->parent_args['id'];
				}

				return $attr;

			}

			/**
			 * Render the child shortcode
			 *
			 * @access public
			 * @since 1.5
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_child( $args, $content = '' ) {
				global $fusion_library, $fusion_settings;

				$defaults = FusionBuilder::set_shortcode_defaults(
					array(
						'title'             => '',
						'values'            => '',
						'legend_text_color' => '',
						'background_color'  => '',
						'border_color'      => '',
					),
					$args,
					'fusion_chart_dataset'
				);

				$this->child_args = $defaults;

				$this->child_legend_text_colors[] = $this->child_args['legend_text_color'];

				$html  = '<div ' . FusionBuilder::attributes( 'chart-dataset-shortcode' ) . '></div>';

				$this->chart_dataset_counter++;

				return $html;

			}

			/**
			 * Builds the child attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function child_attr() {

				$attr = array(
					'class' => 'fusion-chart-dataset',
					// 'style' => 'display:none;',
				);

				if ( $this->child_args['title'] ) {
					$attr['data-label'] = $this->child_args['title'];
				} else {
					$attr['data-label'] = ' ';
				}

				if ( $this->child_args['values'] ) {
					$attr['data-values'] = $this->child_args['values'];
				}

				if ( $this->child_args['background_color'] ) {
					$attr['data-background_color'] = $this->child_args['background_color'];
				}

				if ( $this->child_args['border_color'] ) {
					$attr['data-border_color'] = $this->child_args['border_color'];
				}

				return $attr;

			}

			/**
			 * Builds the dynamic styling.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function add_styling() {
				global $content_media_query, $dynamic_css_helpers;

				$css = array();

				$elements = array(
					'.fusion-chart.legend-right .fusion-chart-inner',
					'.fusion-chart.legend-left .fusion-chart-inner',
				);
				$css[ $content_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['flex-direction'] = 'column';

				$elements = array(
					'.fusion-chart.legend-right .fusion-chart-inner .fusion-chart-legend-wrap li',
					'.fusion-chart.legend-left .fusion-chart-inner .fusion-chart-legend-wrap li',
				);
				$css[ $content_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['display'] = 'inline-block';

				$elements = array(
					'.fusion-chart.legend-right .fusion-chart-legend-wrap',
					'.fusion-chart.legend-left .fusion-chart-legend-wrap',
				);
				$css[ $content_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['padding-top'] = '20px';

				$css[ $content_media_query ]['.fusion-chart.legend-right .fusion-chart-legend-wrap']['padding-left'] = '0';
				$css[ $content_media_query ]['.fusion-chart.legend-left .fusion-chart-legend-wrap']['padding-right'] = '0';

				$css[ $content_media_query ]['.fusion-chart.legend-left .fusion-chart-legend-wrap']['order'] = 2;

				return $css;
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.5
			 * @return array $sections Chart settings.
			 */
			public function add_options() {

				return array(
					'chart_shortcode_section' => array(
						'label'       => esc_html__( 'Chart Element', 'fusion-builder' ),
						'description' => '',
						'id'          => 'chart_shortcode_section',
						'type'        => 'accordion',
						'fields'      => array(
							'chart_legend_position' => array(
								'label'       => esc_attr__( 'Legend Position', 'fusion-builder' ),
								'description' => esc_attr__( 'Set chart legend position. Note that on mobile devices legend will be positioned below the chart when left or right position is used.', 'fusion-builder' ),
								'id'          => 'chart_legend_position',
								'default'     => 'top',
								'type'        => 'radio-buttonset',
								'choices'     => array(
									'top'    => esc_attr__( 'Top', 'fusion-builder' ),
									'right'  => esc_attr__( 'Right', 'fusion-builder' ),
									'bottom' => esc_attr__( 'Bottom', 'fusion-builder' ),
									'left'   => esc_attr__( 'Left', 'fusion-builder' ),
									'off'    => esc_attr__( 'Off', 'fusion-builder' ),
								),
							),
							'chart_show_tooltips' => array(
								'label'       => esc_attr__( 'Show Tooltips', 'fusion-builder' ),
								'description' => esc_attr__( 'Choose whether tooltips should be displayed on hover. If your chart is in a column and the column has a hover type or link, tooltips are disabled.', 'fusion-builder' ),
								'id'          => 'chart_show_tooltips',
								'default'     => 'yes',
								'type'        => 'radio-buttonset',
								'choices'     => array(
									'yes'  => esc_attr__( 'Yes', 'fusion-builder' ),
									'no'   => esc_attr__( 'No', 'fusion-builder' ),
								),
							),
							'chart_bg_color' => array(
								'label'       => esc_attr__( 'Chart Background Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the background of the chart.', 'fusion-builder' ),
								'id'          => 'chart_bg_color',
								'default'     => 'rgba(255,255,255,0)',
								'type'        => 'color-alpha',
							),
							'chart_axis_text_color' => array(
								'label'       => esc_attr__( 'Chart Axis Text Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the text color of the x-axis and y-axis.', 'fusion-builder' ),
								'id'          => 'chart_axis_text_color',
								'default'     => '#666666',
								'type'        => 'color-alpha',
							),
							'chart_gridline_color' => array(
								'label'       => esc_attr__( 'Chart Gridline Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the color of the chart background grid lines and values.', 'fusion-builder' ),
								'id'          => 'chart_gridline_color',
								'default'     => 'rgba(0,0,0,0.1)',
								'type'        => 'color-alpha',
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
					'fusion-chart',
					FusionBuilder::$js_folder_url . '/general/fusion-chart.js',
					FusionBuilder::$js_folder_path . '/general/fusion-chart.js',
					array( 'jquery', 'fusion-chartjs' ),
					'1',
					true
				);
			}
		}
	}

	new FusionSC_Chart();

}

/**
 * Map shortcode to Fusion Builder
 *
 * @since 1.0
 */
function fusion_element_chart() {
	global $fusion_settings;
	if ( ! $fusion_settings ) {
		$fusion_settings = Fusion_Settings::get_instance();
	}

	fusion_builder_map(
		array(
			'name'          => esc_attr__( 'Chart', 'fusion-builder' ),
			'shortcode'     => 'fusion_chart',
			'icon'          => 'fusiona-bar-chart',
			'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-chart-preview.php',

			'custom_settings_view_name'     => 'ModuleSettingsChartView',
			'custom_settings_view_js'       => FUSION_BUILDER_PLUGIN_URL . 'inc/templates/custom/js/fusion-chart-settings.js',
			'custom_settings_template_file' => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/custom/fusion-chart-settings.php',
			'on_save'           => 'chartShortcodeFilter',
			'admin_enqueue_js'  => FUSION_BUILDER_PLUGIN_URL . 'shortcodes/js/fusion-chart.js',
			'preview_id'    => 'fusion-builder-block-module-chart-preview-template',
			'params'        => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Title', 'fusion-builder' ),
					/* translators: Link containing the "Theme Options" text. */
					'description' => sprintf( esc_html__( 'The chart title utilizes all the H4 typography settings in %s except for top and bottom margins.', 'fusion-builder' ), '<a href="' . esc_url_raw( $fusion_settings->get_setting_link( 'h4_typography' ) ) . '" target="_blank">' . esc_attr__( 'Theme Options', 'fusion-builder' ) . '</a>' ),
					'param_name'  => 'title',
					'value'       => '',
				),
				array(
					'type'        => 'select',
					'heading'     => esc_attr__( 'Chart Type', 'fusion-builder' ),
					'description' => esc_attr__( 'Select chart type.', 'fusion-builder' ),
					'param_name'  => 'chart_type',
					'value'       => array(
						'bar'           => esc_attr__( 'Bar', 'fusion-builder' ),
						'horizontalBar' => esc_attr__( 'Horizontal Bar', 'fusion-builder' ),
						'line'          => esc_attr__( 'Line', 'fusion-builder' ),
						'pie'           => esc_attr__( 'Pie', 'fusion-builder' ),
						'doughnut'      => esc_attr__( 'Doughnut', 'fusion-builder' ),
						'radar'         => esc_attr__( 'Radar', 'fusion-builder' ),
						'polarArea'     => esc_attr__( 'Polar Area', 'fusion-builder' ),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Legend Position', 'fusion-builder' ),
					'description' => esc_attr__( 'Set chart legend position. Note that on mobile devices legend will be positioned below the chart when left or right position is used.', 'fusion-builder' ),
					'param_name'  => 'chart_legend_position',
					'value'       => array(
						''       => esc_attr__( 'Default', 'fusion-builder' ),
						'top'    => esc_attr__( 'Top', 'fusion-builder' ),
						'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						'bottom' => esc_attr__( 'Bottom', 'fusion-builder' ),
						'left'   => esc_attr__( 'Left', 'fusion-builder' ),
						'off'    => esc_attr__( 'Off', 'fusion-builder' ),
					),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'X Axis Label', 'fusion-builder' ),
					'description' => esc_attr__( 'Set X axis label.', 'fusion-builder' ),
					'param_name'  => 'x_axis_label',
					'value'       => '',
					'dependency'  => array(
						array(
							'element'  => 'chart_type',
							'value'    => 'pie',
							'operator' => '!=',
						),
						array(
							'element'  => 'chart_type',
							'value'    => 'doughnut',
							'operator' => '!=',
						),
						array(
							'element'  => 'chart_type',
							'value'    => 'polarArea',
							'operator' => '!=',
						),
						array(
							'element'  => 'chart_type',
							'value'    => 'radar',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Y Axis Label', 'fusion-builder' ),
					'description' => esc_attr__( 'Set Y axis label.', 'fusion-builder' ),
					'param_name'  => 'y_axis_label',
					'value'       => '',
					'dependency'  => array(
						array(
							'element'  => 'chart_type',
							'value'    => 'pie',
							'operator' => '!=',
						),
						array(
							'element'  => 'chart_type',
							'value'    => 'doughnut',
							'operator' => '!=',
						),
						array(
							'element'  => 'chart_type',
							'value'    => 'polarArea',
							'operator' => '!=',
						),
						array(
							'element'  => 'chart_type',
							'value'    => 'radar',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Show Tooltips', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose whether tooltips should be displayed on hover. If your chart is in a column and the column has a hover type or link, tooltips are disabled.', 'fusion-builder' ),
					'param_name'  => 'show_tooltips',
					'value'       => array(
						''    => esc_attr__( 'Default', 'fusion-builder' ),
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
				),
				array(
					'type'        => 'select',
					'heading'     => esc_attr__( 'Border Type', 'fusion-builder' ),
					'description' => esc_attr__( 'Select border type.', 'fusion-builder' ),
					'param_name'  => 'chart_border_type',
					'value'       => array(
						'smooth'        => esc_attr__( 'Smooth', 'fusion-builder' ),
						'non_smooth'    => esc_attr__( 'Non smooth', 'fusion-builder' ),
						'stepped'       => esc_attr__( 'Stepped', 'fusion-builder' ),
					),
					'default'     => 'smooth',
					'dependency'  => array(
						array(
							'element'  => 'chart_type',
							'value'    => 'line',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'select',
					'heading'     => esc_attr__( 'Chart Fill', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose how line chart should be filled.', 'fusion-builder' ),
					'param_name'  => 'chart_fill',
					'value'       => array(
						'start'  => esc_attr__( 'Start', 'fusion-builder' ),
						'end'    => esc_attr__( 'End', 'fusion-builder' ),
						'origin' => esc_attr__( 'Origin', 'fusion-builder' ),
						'off'    => esc_attr__( 'Not filled', 'fusion-builder' ),
					),
					'default'  => 'off',
					'dependency'  => array(
						array(
							'element'  => 'chart_type',
							'value'    => 'line',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'select',
					'heading'     => esc_attr__( 'Point Style', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose point style for line charts.', 'fusion-builder' ),
					'param_name'  => 'chart_point_style',
					'value'       => array(
						'circle'      => esc_attr__( 'Circle', 'fusion-builder' ),
						'cross'       => esc_attr__( 'Cross', 'fusion-builder' ),
						'crossRot'    => esc_attr__( 'Cross Rotated', 'fusion-builder' ),
						'dash'        => esc_attr__( 'Dash', 'fusion-builder' ),
						'line'        => esc_attr__( 'Line', 'fusion-builder' ),
						'rect'        => esc_attr__( 'Rectangle', 'fusion-builder' ),
						'rectRounded' => esc_attr__( 'Rectangle Rounded', 'fusion-builder' ),
						'rectRot'     => esc_attr__( 'Rectangle Rotated', 'fusion-builder' ),
						'star'        => esc_attr__( 'Star', 'fusion-builder' ),
						'triangle'    => esc_attr__( 'Triangle', 'fusion-builder' ),
					),
					'default'     => 'circle',
					'dependency'  => array(
						array(
							'element'  => 'chart_type',
							'value'    => 'line',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Point Size', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose point size for line charts.', 'fusion-builder' ),
					'param_name'  => 'chart_point_size',
					'value'       => '3',
					'min'         => '0',
					'max'         => '20',
					'step'        => '1',
					'dependency'  => array(
						array(
							'element'  => 'chart_type',
							'value'    => 'line',
							'operator' => '==',
						),
						array(
							'element'  => 'chart_point_style',
							'value'    => 'cross',
							'operator' => '!=',
						),
						array(
							'element'  => 'chart_point_style',
							'value'    => 'crossRot',
							'operator' => '!=',
						),
						array(
							'element'  => 'chart_point_style',
							'value'    => 'line',
							'operator' => '!=',
						),
						array(
							'element'  => 'chart_point_style',
							'value'    => 'dash',
							'operator' => '!=',
						),
						array(
							'element'  => 'chart_point_style',
							'value'    => 'star',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Point Background Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose point background color for line charts.', 'fusion-builder' ),
					'param_name'  => 'chart_point_bg_color',
					'dependency'  => array(
						array(
							'element'  => 'chart_type',
							'value'    => 'line',
							'operator' => '==',
						),
						array(
							'element'  => 'chart_point_style',
							'value'    => 'cross',
							'operator' => '!=',
						),
						array(
							'element'  => 'chart_point_style',
							'value'    => 'crossRot',
							'operator' => '!=',
						),
						array(
							'element'  => 'chart_point_style',
							'value'    => 'line',
							'operator' => '!=',
						),
						array(
							'element'  => 'chart_point_style',
							'value'    => 'dash',
							'operator' => '!=',
						),
						array(
							'element'  => 'chart_point_style',
							'value'    => 'star',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Point Border Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose point border color for line charts.', 'fusion-builder' ),
					'param_name'  => 'chart_point_border_color',
					'dependency'  => array(
						array(
							'element'  => 'chart_type',
							'value'    => 'line',
							'operator' => '==',
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
add_action( 'fusion_builder_before_init', 'fusion_element_chart' );
