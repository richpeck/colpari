<?php

if ( fusion_is_element_enabled( 'fusion_syntax_highlighter' ) ) {

	if ( ! class_exists( 'FusionSC_Syntax_Highlighter' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.5
		 */
		class FusionSC_Syntax_Highlighter extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.5
			 * @var array
			 */
			protected $args;

			/**
			 * The element counter.
			 *
			 * @access private
			 * @since 1.5
			 * @var int
			 */
			private $counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.5
			 */
			public function __construct() {
				parent::__construct();

				add_filter( 'fusion_attr_syntax-highlighter-container', array( $this, 'syntax_highlighter_container_attr' ) );
				add_filter( 'fusion_attr_syntax-highlighter-textarea', array( $this, 'syntax_highlighter_textarea_attr' ) );
				add_filter( 'fusion_attr_syntax-highlighter-copy-code-title', array( $this, 'syntax_highlighter_copy_code_title_attr' ) );

				add_shortcode( 'fusion_syntax_highlighter', array( $this, 'render' ) );
			}

			/**
			 * Render the shortcode.
			 *
			 * @access public
			 * @since 1.5
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          Highlighted code.
			 */
			public function render( $args, $content = '' ) {
				global $fusion_settings;

				$defaults = FusionBuilder::set_shortcode_defaults(
					array(
						'border_style'                 => $fusion_settings->get( 'syntax_highlighter_border_style' ),
						'border_size'                  => $fusion_settings->get( 'syntax_highlighter_border_size' ),
						'border_color'                 => $fusion_settings->get( 'syntax_highlighter_border_color' ),
						'background_color'             => $fusion_settings->get( 'syntax_highlighter_background_color' ),
						'class'                        => '',
						'copy_to_clipboard'            => $fusion_settings->get( 'syntax_highlighter_copy_to_clipboard' ),
						'copy_to_clipboard_text'       => $fusion_settings->get( 'syntax_highlighter_copy_to_clipboard_text' ),
						'font_size'                    => $fusion_settings->get( 'syntax_highlighter_font_size' ),
						'hide_on_mobile'               => fusion_builder_default_visibility( 'string' ),
						'id'                           => '',
						'language'                     => '',
						'line_numbers'                 => $fusion_settings->get( 'syntax_highlighter_line_numbers' ),
						'line_number_background_color' => $fusion_settings->get( 'syntax_highlighter_line_number_background_color' ),
						'line_number_text_color'       => $fusion_settings->get( 'syntax_highlighter_line_number_text_color' ),
						'line_wrapping'                => $fusion_settings->get( 'syntax_highlighter_line_wrapping' ),
						'margin_top'                   => $fusion_settings->get( 'syntax_highlighter_margin', 'top' ),
						'margin_left'                  => $fusion_settings->get( 'syntax_highlighter_margin', 'left' ),
						'margin_bottom'                => $fusion_settings->get( 'syntax_highlighter_margin', 'bottom' ),
						'margin_right'                 => $fusion_settings->get( 'syntax_highlighter_margin', 'right' ),
						'theme'                        => $fusion_settings->get( 'syntax_highlighter_theme' ),
					),
					$args,
					'fusion_syntax_highlighter'
				);

				// Validate margin values.
				$defaults['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_top'], 'px' );
				$defaults['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_left'], 'px' );
				$defaults['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_bottom'], 'px' );
				$defaults['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_right'], 'px' );

				// Validate border size value.
				$defaults['border_size'] = FusionBuilder::validate_shortcode_attr_value( $defaults['border_size'], 'px' );

				// Validate font size value.
				$defaults['font_size'] = FusionBuilder::validate_shortcode_attr_value( $defaults['font_size'], 'px' );

				$defaults = apply_filters( 'fusion_builder_default_args', $defaults, 'fusion_syntax_highlighter', $args );

				$this->args = $defaults;

				if ( base64_encode( base64_decode( $content ) ) === $content ) {
					$content = base64_decode( $content );
				}

				// Remove <br> tags added by WP from the code.
				$content = str_replace( '<br />', '', $content );

				$html = '<div ' . FusionBuilder::attributes( 'syntax-highlighter-container' ) . '>';

				if ( 'yes' === $this->args['copy_to_clipboard'] ) {
					$html .= '<div ' . FusionBuilder::attributes( 'syntax-highlighter-copy-code' ) . '>';
					$html .= '<span ' . FusionBuilder::attributes( 'syntax-highlighter-copy-code-title' ) . '>' . $this->args['copy_to_clipboard_text'] . '</span>';
					$html .= '</div>';
				}

				// Enqueue code editor and settings for manipulating CSS.
				if ( function_exists( 'wp_enqueue_code_editor' ) ) {
					wp_enqueue_code_editor( array() );

					$type = in_array( $this->args['language'], array( 'json', 'xml' ) ) ? 'application' : 'text';

					// Get CodeMirror options.
					$settings                 = array();
					$settings['readOnly']     = 'nocursor';
					$settings['lineNumbers']  = ( 'yes' === $this->args['line_numbers'] ) ? true : false;
					$settings['lineWrapping'] = ( 'break' === $this->args['line_wrapping'] ) ? true : false;
					$settings['theme']        = $this->args['theme'];

					if ( isset( $this->args['language'] ) && '' !== $this->args['language'] ) {
						$settings['mode'] = $type . '/' . $this->args['language'];
					}

					$html .= '<textarea ' . FusionBuilder::attributes( 'syntax-highlighter-textarea', $settings ) . '>' . $content . '</textarea>';
				} else {
					// Compatibility for WP < 4.9.
					$html .= '<pre id="fusion_syntax_highlighter_' . $this->counter . '">' . $content . '</pre>';
				}
				$html .= '</div>';

				$style = '<style type="text/css" scopped="scopped">';

				if ( $this->args['background_color'] ) {
					$style .= '.fusion-syntax-highlighter-' . $this->counter . ' > .CodeMirror, .fusion-syntax-highlighter-' . $this->counter . ' > .CodeMirror .CodeMirror-gutters {' . sprintf( 'background-color:%s;', $this->args['background_color'] ) . '}';
				}

				if ( 'no' !== $this->args['line_numbers'] ) {
					$style .= '.fusion-syntax-highlighter-' . $this->counter . ' > .CodeMirror .CodeMirror-gutters { background-color: ' . $this->args['line_number_background_color'] . '; }';
					$style .= '.fusion-syntax-highlighter-' . $this->counter . ' > .CodeMirror .CodeMirror-linenumber { color: ' . $this->args['line_number_text_color'] . '; }';
				}

				$style .= '</style>';

				$html = $style . $html;

				$this->counter++;

				return $html;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function syntax_highlighter_container_attr() {
				$attr = array(
					'class' => 'fusion-syntax-highlighter-container',
					'style' => 'opacity:0;',
				);

				$attr['class'] .= ' fusion-syntax-highlighter-' . $this->counter;

				$theme = ( 'default' === $this->args['theme'] || 'elegant' === $this->args['theme'] ) ? 'light' : 'dark';

				$attr['class'] .= ' fusion-syntax-highlighter-theme-' . $theme;

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				if ( $this->args['margin_top'] ) {
					$attr['style'] .= 'margin-top:' . $this->args['margin_top'] . ';';
				}

				if ( $this->args['margin_left'] ) {
					$attr['style'] .= 'margin-left:' . $this->args['margin_left'] . ';';
				}

				if ( $this->args['margin_bottom'] ) {
					$attr['style'] .= 'margin-bottom:' . $this->args['margin_bottom'] . ';';
				}

				if ( $this->args['margin_right'] ) {
					$attr['style'] .= 'margin-right:' . $this->args['margin_right'] . ';';
				}

				if ( $this->args['font_size'] ) {
					$attr['style'] .= 'font-size:' . $this->args['font_size'] . ';';
				}

				if ( '' !== $this->args['border_size'] ) {
					$attr['style'] .= 'border-width:' . $this->args['border_size'] . ';';

					if ( $this->args['border_style'] ) {
						$attr['style'] .= 'border-style:' . $this->args['border_style'] . ';';
					}

					if ( $this->args['border_color'] ) {
						$attr['style'] .= 'border-color:' . $this->args['border_color'] . ';';
					}
				}

				// Compatibility for WP < 4.9.
				if ( ! function_exists( 'wp_enqueue_code_editor' ) && $this->args['background_color'] ) {
					$attr['style'] .= 'background-color:' . $this->args['background_color'] . ';';
					$attr['style'] .= 'padding:0 1em;';
				}

				return $attr;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @param array $settings Highlighter settings for CodeMirror.
			 * @return array
			 */
			public function syntax_highlighter_textarea_attr( $settings ) {
				$attr = array(
					'class' => 'fusion-syntax-highlighter-textarea',
					'id'    => 'fusion_syntax_highlighter_' . $this->counter,
					'style' => '',
				);

				foreach ( $settings as $setting => $value ) {
					$attr[ 'data-' . $setting ] = $value;
				}

				return $attr;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function syntax_highlighter_copy_code_title_attr() {
				$attr = array(
					'class'            => 'syntax-highlighter-copy-code-title',
					'data-id'          => 'fusion_syntax_highlighter_' . $this->counter,
					'style'            => '',
				);

				if ( $this->args['font_size'] ) {
					$attr['style'] .= 'font-size:' . $this->args['font_size'] . ';';
				}

				return $attr;
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
					'fusion-syntax-highlighter',
					FusionBuilder::$js_folder_url . '/general/fusion-syntax-highlighter.js',
					FusionBuilder::$js_folder_path . '/general/fusion-syntax-highlighter.js',
					array( 'jquery' ),
					'1',
					true
				);
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.5
			 * @return array $sections Syntax highlighter settings.
			 */
			public function add_options() {
				global $fusion_settings;

				$code_mirror_themes = apply_filters(
					'fusion_syntax_highlighter_themes',
					array(
						'default'      => esc_attr__( 'Light 1', 'fusion-builder' ),
						'elegant'      => esc_attr__( 'Light 2', 'fusion-builder' ),
						'hopscotch'    => esc_attr__( 'Dark 1', 'fusion-builder' ),
						'oceanic-next' => esc_attr__( 'Dark 2', 'fusion-builder' ),
					)
				);

				return array(
					'syntax_highlighter_shortcode_section' => array(
						'label'       => esc_attr__( 'Syntax Highlighter Element', 'fusion-builder' ),
						'description' => '',
						'id'          => 'syntax_highlighter_shortcode_section',
						'default'     => '',
						'type'        => 'accordion',
						'fields'      => array(
							'syntax_highlighter_theme' => array(
								'type'        => 'radio-buttonset',
								'label'       => esc_attr__( 'Highlighter Theme', 'fusion-builder' ),
								'description' => esc_attr__( 'Select which theme you want to use for code highlighting.', 'fusion-builder' ),
								'id'          => 'syntax_highlighter_theme',
								'choices'     => $code_mirror_themes,
								'default'     => 'default',
							),
							'syntax_highlighter_line_numbers' => array(
								'type'        => 'radio-buttonset',
								'label'       => esc_attr__( 'Line Numbers', 'fusion-builder' ),
								'description' => esc_attr__( 'Choose if you want to display or hide line numbers.', 'fusion-builder' ),
								'id'          => 'syntax_highlighter_line_numbers',
								'choices'     => array(
									'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
									'no'  => esc_attr__( 'No', 'fusion-builder' ),
								),
								'default' => 'yes',
							),
							'syntax_highlighter_line_number_background_color' => array(
								'type'        => 'color-alpha',
								'label'       => esc_attr__( 'Line Numbers Background Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the background color for the line numbers. If left empty, color from selected theme will be used.', 'fusion-builder' ),
								'id'          => 'syntax_highlighter_line_number_background_color',
								'value'       => '',
								'default'     => '',
								'required'    => array(
									array(
										'setting'  => 'syntax_highlighter_line_numbers',
										'value'    => 'yes',
										'operator' => '==',
									),
								),
							),
							'syntax_highlighter_line_number_text_color' => array(
								'type'        => 'color-alpha',
								'label'       => esc_attr__( 'Line Numbers Text Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the color for line number text. If left empty, color from selected theme will be used.', 'fusion-builder' ),
								'id'          => 'syntax_highlighter_line_number_text_color',
								'value'       => '',
								'default'     => '',
								'required'    => array(
									array(
										'setting'  => 'syntax_highlighter_line_numbers',
										'value'    => 'yes',
										'operator' => '==',
									),
								),
							),
							'syntax_highlighter_line_wrapping' => array(
								'type'        => 'radio-buttonset',
								'label'       => esc_attr__( 'Line Wrapping', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls whether the long line should break or add horizontal scroll.', 'fusion-builder' ),
								'id'          => 'syntax_highlighter_line_wrapping',
								'choices'     => array(
									'scroll' => esc_attr__( 'Scroll', 'fusion-builder' ),
									'break'  => esc_attr__( 'Break', 'fusion-builder' ),
								),
								'default' => 'scroll',
							),
							'syntax_highlighter_copy_to_clipboard' => array(
								'type'        => 'radio-buttonset',
								'label'       => esc_attr__( 'Copy to Clipboard', 'fusion-builder' ),
								'description' => esc_attr__( 'Choose if you want to allow your visitors to easily copy your code with a click of the button.', 'fusion-builder' ),
								'id'          => 'syntax_highlighter_copy_to_clipboard',
								'choices'     => array(
									'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
									'no'  => esc_attr__( 'No', 'fusion-builder' ),
								),
								'default'     => 'yes',
							),
							'syntax_highlighter_copy_to_clipboard_text' => array(
								'type'        => 'text',
								'label'       => esc_attr__( 'Copy to Clipboard Text', 'fusion-builder' ),
								'description' => esc_attr__( 'Enter text to be displayed for user to click to copy.', 'fusion-builder' ),
								'id'          => 'syntax_highlighter_copy_to_clipboard_text',
								'default'     => esc_attr__( 'Copy to Clipboard', 'fusion-builder' ),
								'required'    => array(
									array(
										'setting'  => 'syntax_highlighter_copy_to_clipboard',
										'value'    => 'yes',
										'operator' => '==',
									),
								),
							),
							'syntax_highlighter_font_size' => array(
								'type'        => 'slider',
								'label'       => esc_attr__( 'Font Size', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the font size of the syntax highlight code.', 'fusion-builder' ),
								'id'          => 'syntax_highlighter_font_size',
								'default'     => '14',
								'choices'     => array(
									'min'         => '10',
									'max'         => '100',
									'step'        => '1',
								),
							),
							'syntax_highlighter_background_color' => array(
								'type'        => 'color-alpha',
								'label'       => esc_attr__( 'Background Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the background color for code highlight area.', 'fusion-builder' ),
								'id'          => 'syntax_highlighter_background_color',
								'value'       => '',
								'default'     => '',
							),
							'syntax_highlighter_border_size' => array(
								'type'        => 'slider',
								'label'       => esc_attr__( 'Border Size', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the border size of the syntax highlighter. In pixels.', 'fusion-builder' ),
								'id'          => 'syntax_highlighter_border_size',
								'default'     => '1',
								'choices'     => array(
									'min'         => '0',
									'max'         => '50',
									'step'        => '1',
								),
							),
							'syntax_highlighter_border_color' => array(
								'type'        => 'color-alpha',
								'label'       => esc_attr__( 'Border Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the border color.', 'fusion-builder' ),
								'id'          => 'syntax_highlighter_border_color',
								'default'     => $fusion_settings->get( 'sep_color' ),
								'required'    => array(
									array(
										'setting'  => 'syntax_highlighter_border_size',
										'value'    => '0',
										'operator' => '!=',
									),
								),
							),
							'syntax_highlighter_border_style' => array(
								'type'        => 'radio-buttonset',
								'label'       => esc_attr__( 'Border Style', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the border style.', 'fusion-builder' ),
								'id'          => 'syntax_highlighter_border_style',
								'default'     => 'solid',
								'required'    => array(
									array(
										'setting'  => 'syntax_highlighter_border_size',
										'value'    => '0',
										'operator' => '!=',
									),
								),
								'choices'     => array(
									'solid'  => esc_attr__( 'Solid', 'fusion-builder' ),
									'dashed' => esc_attr__( 'Dashed', 'fusion-builder' ),
									'dotted' => esc_attr__( 'Dotted', 'fusion-builder' ),
								),
							),
							'syntax_highlighter_margin' => array(
								'label'       => esc_html__( 'Margins', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the margin around syntax highlighter element.', 'fusion-builder' ),
								'id'          => 'syntax_highlighter_margin',
								'type'        => 'spacing',
								'choices'     => array(
									'top'    => true,
									'left'   => true,
									'bottom' => true,
									'right'  => true,
									'units'   => array( 'px', '%' ),
								),
								'default'     => array(
									'top'     => '0px',
									'left'    => '0px',
									'bottom'  => '0px',
									'right'   => '0px',
								),
							),
						),
					),
				);
			}
		}
	}

	new FusionSC_Syntax_Highlighter();

}

/**
 * Map shortcode to Fusion Builder.
 *
 * @since 1.5
 */
function fusion_element_syntax_highlighter() {
	global $fusion_settings;

	$code_mirror_themes = apply_filters(
		'fusion_syntax_highlighter_themes',
		array(
			''             => esc_attr__( 'Default', 'fusion-builder' ),
			'default'      => esc_attr__( 'Light 1', 'fusion-builder' ),
			'elegant'      => esc_attr__( 'Light 2', 'fusion-builder' ),
			'hopscotch'    => esc_attr__( 'Dark 1', 'fusion-builder' ),
			'oceanic-next' => esc_attr__( 'Dark 2', 'fusion-builder' ),
		)
	);

	fusion_builder_map(
		array(
			'name'        => esc_attr__( 'Syntax Highlighter', 'fusion-builder' ),
			'shortcode'   => 'fusion_syntax_highlighter',
			'icon'        => 'fusiona-code',
			'escape_html' => true,
			'params'      => array(
				array(
					'type'        => 'code',
					'heading'     => esc_attr__( 'Code to Highlight', 'fusion-builder' ),
					'description' => esc_attr__( 'Enter some code to be displayed with highlighted syntax.', 'fusion-builder' ),
					'param_name'  => 'element_content',
					'value'       => '',
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Highlighter Theme', 'fusion-builder' ),
					'description' => esc_attr__( 'Select which theme you want to use for code highlighting.', 'fusion-builder' ),
					'param_name'  => 'theme',
					'value'       => $code_mirror_themes,
					'default'     => '',
				),
				array(
					'type'        => 'select',
					'heading'     => esc_attr__( 'Code Language', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the language the code is in.', 'fusion-builder' ),
					'param_name'  => 'language',
					'value'       => array(
						'x-sh'       => esc_attr__( 'bash', 'fusion-builder' ),
						'css'        => esc_attr__( 'css', 'fusion-builder' ),
						'conf'       => esc_attr__( 'conf', 'fusion-builder' ),
						'diff'       => esc_attr__( 'diff', 'fusion-builder' ),
						'html'       => esc_attr__( 'html', 'fusion-builder' ),
						'htm'        => esc_attr__( 'htm', 'fusion-builder' ),
						'http'       => esc_attr__( 'http', 'fusion-builder' ),
						'javascript' => esc_attr__( 'javascript', 'fusion-builder' ),
						'json'       => esc_attr__( 'json', 'fusion-builder' ),
						'jsx'        => esc_attr__( 'jsx', 'fusion-builder' ),
						'x-less'     => esc_attr__( 'less', 'fusion-builder' ),
						'md'         => esc_attr__( 'md', 'fusion-builder' ),
						'patch'      => esc_attr__( 'patch', 'fusion-builder' ),
						'x-php'      => esc_attr__( 'php', 'fusion-builder' ),
						'phtml'      => esc_attr__( 'phtml', 'fusion-builder' ),
						'x-sass'     => esc_attr__( 'sass', 'fusion-builder' ),
						'x-scss'     => esc_attr__( 'scss', 'fusion-builder' ),
						'sql'        => esc_attr__( 'sql', 'fusion-builder' ),
						'svg'        => esc_attr__( 'svg', 'fusion-builder' ),
						'txt'        => esc_attr__( 'txt', 'fusion-builder' ),
						'xml'        => esc_attr__( 'xml', 'fusion-builder' ),
						'yaml'       => esc_attr__( 'yaml', 'fusion-builder' ),
						'yml'        => esc_attr__( 'yml', 'fusion-builder' ),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Line Numbers', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose if you want to display or hide line numbers.', 'fusion-builder' ),
					'param_name'  => 'line_numbers',
					'value'       => array(
						''    => esc_attr__( 'Default', 'fusion-builder' ),
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'default'     => '',
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Line Wrapping', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls whether the long line should break or add horizontal scroll.', 'fusion-builder' ),
					'param_name'  => 'line_wrapping',
					'value'       => array(
						''       => esc_attr__( 'Default', 'fusion-builder' ),
						'scroll' => esc_attr__( 'Scroll', 'fusion-builder' ),
						'break'  => esc_attr__( 'Break', 'fusion-builder' ),
					),
					'default'     => '',
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Copy to Clipboard', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose if you want to allow your visitors to easily copy your code with a click of the button.', 'fusion-builder' ),
					'param_name'  => 'copy_to_clipboard',
					'value'       => array(
						''    => esc_attr__( 'Default', 'fusion-builder' ),
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'default'     => '',
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Copy to Clipboard Text', 'fusion-builder' ),
					'description' => esc_attr__( 'Enter text to be displayed for user to click to copy.', 'fusion-builder' ),
					'param_name'  => 'copy_to_clipboard_text',
					'value'       => $fusion_settings->get( 'syntax_highlighter_copy_to_clipboard_text' ),
					'placeholder' => true,
					'dependency'  => array(
						array(
							'element'  => 'copy_to_clipboard',
							'value'    => 'no',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Font Size', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the font size of the syntax highlight code. In pixels.', 'fusion-builder' ),
					'param_name'  => 'font_size',
					'default'     => intval( $fusion_settings->get( 'syntax_highlighter_font_size' ) ),
					'value'       => '',
					'choices'     => array(
						'min'         => '10',
						'max'         => '100',
						'step'        => '1',
					),
					'group'       => esc_attr__( 'Design', 'fusion-builder' ),
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Border Size', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the border size of the syntax highlighter. In pixels.', 'fusion-builder' ),
					'param_name'  => 'border_size',
					'default'     => intval( $fusion_settings->get( 'syntax_highlighter_border_size' ) ),
					'value'       => '',
					'choices'     => array(
						'min'         => '0',
						'max'         => '50',
						'step'        => '1',
					),
					'group'       => esc_attr__( 'Design', 'fusion-builder' ),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the border color.', 'fusion-builder' ),
					'param_name'  => 'border_color',
					'value'       => '',
					'default'     => $fusion_settings->get( 'syntax_highlighter_border_color' ),
					'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					'dependency'  => array(
						array(
							'element'  => 'border_size',
							'value'    => '0',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Border Style', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the border style.', 'fusion-builder' ),
					'param_name'  => 'border_style',
					'default'     => '',
					'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					'dependency'  => array(
						array(
							'element'  => 'border_size',
							'value'    => '0',
							'operator' => '!=',
						),
					),
					'value'       => array(
						''       => esc_attr__( 'Default', 'fusion-builder' ),
						'solid'  => esc_attr__( 'Solid', 'fusion-builder' ),
						'dashed' => esc_attr__( 'Dashed', 'fusion-builder' ),
						'dotted' => esc_attr__( 'Dotted', 'fusion-builder' ),
					),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the background color for code highlight area.', 'fusion-builder' ),
					'param_name'  => 'background_color',
					'value'       => '',
					'default'     => $fusion_settings->get( 'syntax_highlighter_background_color' ),
					'group'       => esc_attr__( 'Design', 'fusion-builder' ),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Line Number Background Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the line number background color for code highlight area.', 'fusion-builder' ),
					'param_name'  => 'line_number_background_color',
					'value'       => '',
					'default'     => $fusion_settings->get( 'syntax_highlighter_line_number_background_color' ),
					'dependency'  => array(
						array(
							'element'  => 'line_numbers',
							'value'    => 'no',
							'operator' => '!=',
						),
					),
					'group'       => esc_attr__( 'Design', 'fusion-builder' ),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Line Number Text Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the line number text color for code highlight area.', 'fusion-builder' ),
					'param_name'  => 'line_number_text_color',
					'value'       => '',
					'default'     => $fusion_settings->get( 'syntax_highlighter_line_number_text_color' ),
					'dependency'  => array(
						array(
							'element'  => 'line_numbers',
							'value'    => 'no',
							'operator' => '!=',
						),
					),
					'group'       => esc_attr__( 'Design', 'fusion-builder' ),
				),
				array(
					'type'             => 'dimension',
					'remove_from_atts' => true,
					'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
					'param_name'       => 'margin',
					'value'            => array(
						'margin_top'    => '',
						'margin_left'   => '',
						'margin_bottom' => '',
						'margin_right'  => '',
					),
					'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					'description'      => esc_attr__( 'Control spacing around the syntax highlighter. In px, em or %, e.g. 10px.', 'fusion-builder' ),
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
add_action( 'fusion_builder_before_init', 'fusion_element_syntax_highlighter' );
