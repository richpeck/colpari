<?php
/**
 * Fusion-Builder Shortcode Element.
 *
 * @package Fusion-Core
 * @since 3.5.2
 */

if ( function_exists( 'fusion_is_element_enabled' ) && fusion_is_element_enabled( 'fusion_privacy' ) ) {

	if ( ! class_exists( 'FusionSC_Privacy' ) && class_exists( 'Fusion_Element' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-core
		 * @since 3.5.2
		 */
		class FusionSC_Privacy extends Fusion_Element {

			/**
			 * Element counter, used for CSS.
			 *
			 * @since 3.5.2
			 * @var int $args
			 */
			private $privacy_counter = 0;

			/**
			 * Posted data if set.
			 *
			 * @since 3.5.2
			 * @var array
			 */
			private $data = false;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @static
			 * @access public
			 * @since 3.5.2
			 * @var array
			 */
			public static $args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.5.2
			 */
			public function __construct() {

				parent::__construct();

				add_action( 'template_redirect', array( $this, 'save_consents' ) );
				add_filter( 'fusion_attr_privacy-shortcode', array( $this, 'attr' ) );
				add_filter( 'fusion_attr_privacy-content', array( $this, 'content_attr' ) );
				add_filter( 'fusion_attr_privacy-form', array( $this, 'form_attr' ) );
				add_shortcode( 'fusion_privacy', array( $this, 'render' ) );
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 3.5.2
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				global $fusion_settings, $fusion_library;

				$defaults = apply_filters(
					'fusion_privacy_default_parameter',
					FusionBuilder::set_shortcode_defaults(
						array(
							'animation_direction'   => 'left',
							'animation_offset'      => $fusion_settings->get( 'animation_offset' ),
							'animation_speed'       => '',
							'animation_type'        => '',
							'class'                 => '',
							'form_field_layout'     => 'stacked',
							'hide_on_mobile'        => fusion_builder_default_visibility( 'string' ),
							'id'                    => '',
						),
						$args,
						'fusion_privacy'
					)
				);

				self::$args = $defaults;

				$this->privacy_counter++;

				$html = '<div ' . FusionBuilder::attributes( 'privacy-shortcode' ) . '>';

				$html .= '<div ' . FusionBuilder::attributes( 'privacy-content' ) . '>' . wpautop( $content, false ) . '</div>';

				if ( class_exists( 'Avada_Privacy_Embeds' ) && Avada()->settings->get( 'privacy_embeds' ) ) {
					$html .= $this->privacy_embed_form();
				}

				$html .= '</div>';

				return $html;
			}

			/**
			 * Gets the HTML for the privacy embed form.
			 *
			 * @access public
			 * @since 3.5.2
			 * @return string
			 */
			public function privacy_embed_form() {
				$html = '';
				$html .= $this->get_alert();

				$embeds   = Avada()->privacy_embeds->get_embed_types();
				$consents = Avada()->privacy_embeds->get_consents();

				if ( is_array( $embeds ) ) {
					$html .= '<form ' . FusionBuilder::attributes( 'privacy-form' ) . '>';
					$html .= '<ul>';

					// Loop each embed type and add a checkbox.
					foreach ( $embeds as $id => $embed ) {
						$selected = Avada()->privacy_embeds->is_selected( $id ) ? 'checked' : '';

						$html .= '<li>';
						$html .= '<label for="' . $id . '">';
						$html .= '<input name="consents[]" type="checkbox" value="' . $id . '" ' . $selected . ' id="' . $id . '">';
						$html .= $embed['label'];
						$html .= '</label>';
						$html .= '</li>';
					}

					$html .= '</ul>';
					$html .= wp_referer_field( false );
					$html .= '<input type="hidden" name="privacyformid" value="' . $this->privacy_counter . '">';
					$html .= '<input type="hidden" name="consents[]" value="consent">';
					$html .= '<input class="fusion-button fusion-button-default fusion-button-default-size" type="submit" value="' . esc_attr__( 'Update', 'fusion-core' ) . '" >';
					$html .= '</form>';
				}
				return $html;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.5.2
			 * @return array
			 */
			public function attr() {

				global $fusion_settings;

				$attr = fusion_builder_visibility_atts(
					self::$args['hide_on_mobile'],
					array(
						'class' => 'fusion-privacy-element fusion-privacy-element-' . $this->privacy_counter,
					)
				);

				// Add custom class.
				if ( self::$args['class'] ) {
					$attr['class'] .= ' ' . self::$args['class'];
				}

				// Add custom id.
				if ( self::$args['id'] ) {
					$attr['id'] = self::$args['id'];
				}

				// Add animation classes.
				if ( self::$args['animation_type'] ) {
					$animations = FusionBuilder::animations(
						array(
							'type'      => self::$args['animation_type'],
							'direction' => self::$args['animation_direction'],
							'speed'     => self::$args['animation_speed'],
							'offset'    => self::$args['animation_offset'],
						)
					);

					$attr = array_merge( $attr, $animations );

					$attr['class'] .= ' ' . $attr['animation_class'];
					unset( $attr['animation_class'] );
				}

				return $attr;
			}

			/**
			 * Builds the attributes array for the content div.
			 *
			 * @access public
			 * @since 3.5.2
			 * @return array
			 */
			public function content_attr() {

				$attr = array(
					'class' => 'fusion-privacy-form-intro',
				);

				return $attr;
			}

			/**
			 * Builds the attributes array for the form.
			 *
			 * @access public
			 * @since 3.5.2
			 * @return array
			 */
			public function form_attr() {

				$attr = array(
					'id'     => 'fusion-privacy-form-' . $this->privacy_counter,
					'action' => '',
					'method' => 'post',
					'class'  => 'fusion-privacy-form fusion-privacy-form-' . self::$args['form_field_layout'],
				);

				return $attr;
			}

			/**
			 * Save the consents if submitted.
			 *
			 * @since 3.5.2
			 * @return void
			 */
			public function save_consents() {

				if ( isset( $_POST ) && isset( $_POST['privacyformid'] ) ) {  // WPCS: CSRF ok.

					$query_args = array(
						'success' => 1,
						'id'      => (int) $_POST['privacyformid'], // WPCS: CSRF ok.
					);

					if ( isset( $_POST['consents'] ) ) {
						Avada()->privacy_embeds->save_cookie( array_map( 'esc_attr', wp_unslash( $_POST['consents'] ) ) ); // WPCS: CSRF ok, sanitization ok.
					} else {
						Avada()->privacy_embeds->clear_cookie();
						$query_args['success'] = 2;
					}

					if ( isset( $_POST['_wp_http_referer'] ) ) {
						$redirection_link = wp_unslash( $_POST['_wp_http_referer'] ); // WPCS: CSRF ok, sanitization ok.
						$redirection_link = add_query_arg( $query_args, $redirection_link );
						wp_safe_redirect( $redirection_link );
					}
				}
			}

			/**
			 * Get the alert markup.
			 *
			 * @since 3.5.2
			 * @return string The alert.
			 */
			public function get_alert() {
				$alert = '';

				if ( isset( $_GET ) && isset( $_GET['success'] ) && isset( $_GET['id'] ) && $this->privacy_counter === (int) $_GET['id'] ) {
					if ( 1 === (int) $_GET['success'] ) {
						if ( shortcode_exists( 'fusion_alert' ) ) {
							$alert = do_shortcode( '[fusion_alert type="success"]' . esc_html__( 'Your embed preferences have been updated.', 'fusion-core' ) . '[/fusion_alert]' );
						} else {
							$alert = '<h3 style="color:#468847;">' . esc_html__( 'Your embed preferences have been updated.', 'fusion-core' ) . '</h3>';
						}
					} else {
						if ( shortcode_exists( 'fusion_alert' ) ) {
							$alert = do_shortcode( '[fusion_alert type="success"]' . esc_html__( 'Your embed preferences have been cleared.', 'fusion-core' ) . '[/fusion_alert]' );
						} else {
							$alert = '<h3 style="color:#b94a48;">' . esc_html__( 'Your embed preferences have been cleared.', 'fusion-core' ) . '</h3>';
						}
					}
				}

				return $alert;
			}
		}
	} // End if().

	new FusionSC_Privacy();
} // End if().

/**
 * Map shortcode to Fusion Builder.
 *
 * @since 3.5.2
 */
function fusion_element_privacy() {

	global $fusion_settings, $pagenow;
	if ( class_exists( 'Avada_Privacy_Embeds' ) && Avada()->settings->get( 'privacy_embeds' ) ) {
		fusion_builder_map(
			array(
				'name'       => esc_attr__( 'Privacy', 'fusion-core' ),
				'shortcode'  => 'fusion_privacy',
				'icon'       => 'fusiona-privacy',
				'preview'    => FUSION_CORE_PATH . '/shortcodes/previews/fusion-privacy-preview.php',
				'preview_id' => 'fusion-builder-block-module-privacy-preview-template',
				'params'     => array(
					array(
						'type'        => 'tinymce',
						'heading'     => esc_attr__( 'Privacy Text', 'fusion-core' ),
						'description' => esc_attr__( 'Controls the privacy text which will show above the form.', 'fusion-core' ),
						'param_name'  => 'element_content',
						'value'       => esc_attr__( 'Your Content Goes Here', 'fusion-core' ),
						'placeholder' => true,
					),
					array(
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Form Field Layout', 'fusion-core' ),
						'description' => esc_attr__( 'Choose if form checkboxes should be stacked and full width, or if they should be floated.', 'fusion-core' ),
						'param_name'  => 'form_field_layout',
						'value'       => array(
							'stacked' => esc_attr__( 'Stacked', 'fusion-core' ),
							'floated' => esc_attr__( 'Floated', 'fusion-core' ),
						),
						'default'     => 'stacked',
					),
					array(
						'type'        => 'select',
						'heading'     => esc_attr__( 'Animation Type', 'fusion-core' ),
						'description' => esc_attr__( 'Select the type of animation to use on the element.', 'fusion-core' ),
						'param_name'  => 'animation_type',
						'value'       => fusion_builder_available_animations(),
						'default'     => '',
						'group'       => esc_attr__( 'Animation', 'fusion-core' ),
					),
					array(
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Direction of Animation', 'fusion-core' ),
						'description' => esc_attr__( 'Select the incoming direction for the animation.', 'fusion-core' ),
						'param_name'  => 'animation_direction',
						'value'       => array(
							'down'   => esc_attr__( 'Top', 'fusion-core' ),
							'right'  => esc_attr__( 'Right', 'fusion-core' ),
							'up'     => esc_attr__( 'Bottom', 'fusion-core' ),
							'left'   => esc_attr__( 'Left', 'fusion-core' ),
							'static' => esc_attr__( 'Static', 'fusion-core' ),
						),
						'default'     => 'left',
						'group'       => esc_attr__( 'Animation', 'fusion-core' ),
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
						'heading'     => esc_attr__( 'Speed of Animation', 'fusion-core' ),
						'description' => esc_attr__( 'Type in speed of animation in seconds (0.1 - 1).', 'fusion-core' ),
						'param_name'  => 'animation_speed',
						'min'         => '0.1',
						'max'         => '1',
						'step'        => '0.1',
						'value'       => '0.3',
						'group'       => esc_attr__( 'Animation', 'fusion-core' ),
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
						'heading'     => esc_attr__( 'Offset of Animation', 'fusion-core' ),
						'description' => esc_attr__( 'Controls when the animation should start.', 'fusion-core' ),
						'param_name'  => 'animation_offset',
						'value'       => array(
							''                => esc_attr__( 'Default', 'fusion-core' ),
							'top-into-view'   => esc_attr__( 'Top of element hits bottom of viewport', 'fusion-core' ),
							'top-mid-of-view' => esc_attr__( 'Top of element hits middle of viewport', 'fusion-core' ),
							'bottom-in-view'  => esc_attr__( 'Bottom of element enters viewport', 'fusion-core' ),
						),
						'default'     => '',
						'group'       => esc_attr__( 'Animation', 'fusion-core' ),
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
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-core' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-core' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-core' ),
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-core' ),
						'param_name'  => 'class',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-core' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-core' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-core' ),
						'param_name'  => 'id',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-core' ),
					),
				),
			)
		);
	}
}
add_action( 'wp_loaded', 'fusion_element_privacy' );
