<?php

namespace AsRulesEngine;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Condition related html field types work.
 */
if ( ! class_exists( 'Condition_Field' ) ) {
	class Condition_Field {
		/**
		 * $post_id current post id.
		 * @var int
		 */
		private $post_id;
		/**
		 * $name current field name.
		 * @var string
		 */
		private $name;
		/**
		 * $slug current field slug.
		 * @var string
		 */
		private $slug;
		/**
		 * $slug current field type.
		 * @var string
		 */
		private $type;
		/**
		 * $args current field data array.
		 * @var array
		 */
		private $args;
		/**
		 * $args current field type.
		 * @var string
		 */
		private $condition_field;

		/**
		 *
		 * Merge defaults for the args array
		 *
		 */
		public function __construct( $args ) {
			$this->post_id = get_the_id();
			$this->set_args( $args );
			$this->name = $this->args['name'];
			$this->type = $this->args['type'];
			$this->slug = $this->args['slug'];
		}

		/**
		 *
		 * Merge defaults for the args array
		 *
		 */
		private function set_args( $args ) {
			$defaults = ! empty( $args['type'] ) && $args['type'] === 'select'
				? $this->get_select_field_defaults()
				: $this->get_input_field_defaults();

			$args = wp_parse_args( $args, $defaults );
			$this->args = $args;
		}

		/**
		 *
		 * Get defaults for select field
		 *
		 * @return array
		 */
		private function get_select_field_defaults() {
			return array(
				'slug' => '',
				'name' => '',
				'type' => 'select',
				'multiple' => false,
				'options' => array(),
			);
		}

		/**
		 *
		 * Get defaults for input field
		 *
		 * @return array
		 */
		private function get_input_field_defaults() {
			return array(
				'slug' => '',
				'name' => '',
				'type' => 'text',
			);
		}

		/**
		 *
		 * Render the HTML for the field
		 *
		 */
		public function render_field() {
			switch ( $this->type ) {
				case 'wysiwyg':
					$this->render_wysiwyg_field();
					break;

				case 'select':
					$this->render_select_field();
					break;

				case 'checkbox':
					$this->render_checkbox_field();
					break;

				default:
					$this->render_input_field();
					break;
			}
		}

		/**
		  *
		  * Render the HTML for checkbox field
		  *
		  */
		private function render_checkbox_field() {
			$args = $this->args;

			if ( ! empty( $this->post_id ) ) {
				$this->condition_field = get_post_meta( $this->post_id, $this->slug, true );
				$args['condition_field'] = $this->condition_field;
			}

			extract( $args );
			require( AS_RE_PATH . 'includes/views/partials/condition_fields/checkbox.php' );
		}

		/**
		 *
		 * Render the HTML for select field
		 *
		 */
		private function render_select_field() {
			$args = $this->args;
			if ( ! empty( $this->post_id ) ) {
				$this->condition_field = get_post_meta( $this->post_id, $this->slug, true );
				$args['condition_field'] = $this->condition_field;
			}
			extract( $args );
			require( AS_RE_PATH . 'includes/views/partials/condition_fields/select.php' );
		}

		/**
		 *
		 * Render the HTML for input field
		 *
		 */
		private function render_input_field() {
			$args = $this->args;
			if ( ! empty( $this->post_id ) ) {
				$this->condition_field = get_post_meta( $this->post_id, $this->slug, true );
				$args['condition_field'] = $this->condition_field;
				if ( $this->slug === 'condition_client_attrs_value' ) {
					$args['client_meta_attrs'] = get_post_meta( $this->post_id, 'condition_client_attrs', true );
				}
			}

			extract( $args );
			require( AS_RE_PATH . 'includes/views/partials/condition_fields/input.php' );
		}

		/**
		  *
		  * Render the HTML for WYSIWYG field
		  *
		  */
		private function render_wysiwyg_field() {
			$args = $this->args;

			if ( ! empty( $this->post_id ) ) {
			    $this->condition_field = get_post_meta( $this->post_id, $this->slug, true );
			    $args['condition_field'] = $this->condition_field;
			}

			extract( $args );

			require( AS_RE_PATH . 'includes/views/partials/condition_fields/wysiwyg.php' );
		}

		/**
		  *
		  * Update the post meta for the field
		  *
		  */
		public function save() {

			if ( ! empty( $_POST ) && ! empty( $this->post_id ) ) {
				$post_id = $this->post_id;
				$operator_slug = $this->slug . '_operator';
				$extra_operator_slug = $this->slug . '_extra_operator';
				$regex_slug = $this->slug . '_regex';
				$value = ! empty( $_POST[ $this->slug ] ) ? $_POST[ $this->slug ] : '';
				if ( $this->type == 'number' && ! empty( $value ) && ! is_numeric( $value ) ) {
					return;
				}
				$operator = ! empty( $_POST[ $operator_slug ] ) ? $_POST[ $operator_slug ] : AS_RE_DEFAULT_OPERATOR;
				$regex = ! empty( $_POST[ $regex_slug ] ) ? $_POST[ $regex_slug ] : AS_RE_DEFAULT_REGEX;
				$extra_operator = ! empty( $_POST[ $extra_operator_slug ] ) ? $_POST[ $extra_operator_slug ] : '=';

				$meta_value = array(
					'value' => $value,
					'operator' => $operator,
					'extra_operator' => $extra_operator,
					'regex' => $regex,
				);
				$extra_value_field_slug = $this->slug . '_template';
				$extra_value_field = ( isset( $_POST[ $extra_value_field_slug ] ) && ! empty( $_POST[ $extra_value_field_slug ] )) ? $_POST[ $extra_value_field_slug ]: '';
				if ( ! empty( $extra_value_field ) ) {
					$meta_value[ $extra_value_field_slug ] = $extra_value_field;
				}
				$additional_field_input_slug = $this->slug . '_value';
				$additional_field_input = ( isset( $_POST[ $additional_field_input_slug ] ) && ! empty( $_POST[ $additional_field_input_slug ] )) ? $_POST[ $additional_field_input_slug ]: '';
				if ( ! empty( $additional_field_input ) ) {
					$meta_value['value_txt'] = $additional_field_input;
				}
				if ( 'condition_custom_field' === $this->slug ) {
					$value = ! empty( $_POST[ $this->slug ] ) ? $_POST[ $this->slug ] : '';
					$condition_custom_field_value = ! empty( $_POST[ $this->slug . '_value' ] ) ? $_POST[ $this->slug . '_value' ] : '';

					$operator_slug = $this->slug . '_operator';
					$operator = ! empty( $_POST[ $operator_slug ] ) ? $_POST[ $operator_slug ] : AS_RE_DEFAULT_OPERATOR;
					$condition_customfield_regx = ! empty( $_POST[ $this->slug . '_regex' ] ) ? $_POST[ $this->slug . '_regex' ] : '';

					$custom_fields_extra_field = ! empty( $_POST[ $this->slug . '_extra' ] ) ? $_POST[ $this->slug . '_extra' ] : array();
					$custom_fields_extra_value = ! empty( $_POST[ $this->slug . '_value_extra' ] ) ? $_POST[ $this->slug . '_value_extra' ] : array();
					$custom_fields_extra_operator = ! empty( $_POST[ $this->slug . '_value_operator_extra' ] ) ? $_POST['condition_custom_field_value_operator_extra'] : array();
					$custom_fields_regx = ! empty( $_POST[ $this->slug . '_value_regex_extra' ] ) ? $_POST['condition_custom_field_value_regex_extra'] : array();
					$custom_meta_values = array();
					$customfield_meta_values = array();

					if ( ! empty( $value ) && ! empty( $operator ) ) {
						$extra_custom_fields_array = array();
						if ( ! empty( $custom_fields_extra_field ) && ! empty( $custom_fields_extra_value ) && ! empty( $custom_fields_extra_operator ) && ! empty( $condition_customfield_regx ) ) {
							foreach ( $custom_fields_extra_field as $key => $field_value ) {
								if ( ! empty( $custom_fields_extra_value[ $key ] ) && ! empty( $custom_fields_extra_operator[ $key ] )  && ! empty( $condition_customfield_regx[ $key ] ) ) {
									$extra_custom_fields_array[] = array(
												'field' => $field_value,
												'value' => $custom_fields_extra_value[ $key ],
												'operator' => $custom_fields_extra_operator[ $key ],
												'regex' => $custom_fields_regx[ $key ],
											);
								}
							}
						}
						update_post_meta( $post_id, 'extra_customfields_data', $extra_custom_fields_array );

						$customfield_meta_values[] = array(
								'field' => $value,
								'value' => $condition_custom_field_value,
								'operator' => $operator,
								'regex' => $condition_customfield_regx,
							);
						$custom_fields = array(
							'value' => $value,
							'value_txt' => $condition_custom_field_value,
							'operator' => $operator,
							'regex' => $condition_customfield_regx,
						);

						update_post_meta( $post_id, $this->slug, $custom_fields );
						$customfield_meta_values = array_merge( $customfield_meta_values, $extra_custom_fields_array );

						if ( 'default' === $operator ) {
							// If first operator is default.
							//  Make Everything unselected for client attribute.
							update_post_meta( $post_id, $this->slug, array() );
							$customfield_meta_values = array();
						}
					}

					if ( ! empty( $customfield_meta_values ) ) {
						update_post_meta( $post_id, 'custom_field_data', $customfield_meta_values );
					}
				} else if ( 'condition_agent_attrs' === $this->slug ) {
					$value = ! empty( $_POST[ $this->slug ] ) ? $_POST[ $this->slug ] : '';
					$condition_agent_attrs_value = ! empty( $_POST[ $this->slug . '_value' ] ) ? $_POST[ $this->slug . '_value' ] : '';

					$operator_slug = $this->slug . '_operator';
					$operator = ! empty( $_POST[ $operator_slug ] ) ? $_POST[ $operator_slug ] : AS_RE_DEFAULT_OPERATOR;
					$condition_regx = ! empty( $_POST[ $this->slug . '_regex' ] ) ? $_POST[ $this->slug . '_regex' ] : '';

					$agent_attr_extra_field = ! empty( $_POST[ $this->slug . '_extra' ] ) ? $_POST[ $this->slug . '_extra' ] : array();
					$agent_attr_extra_value = ! empty( $_POST[ $this->slug . '_value_extra' ] ) ? $_POST[ $this->slug . '_value_extra' ] : array();
					$agent_attr_extra_operator = ! empty( $_POST[ $this->slug . '_value_operator_extra' ] ) ? $_POST['condition_agent_attrs_value_operator_extra'] : array();
					$agent_attr_regx = ! empty( $_POST[ $this->slug . '_value_regex_extra' ] ) ? $_POST['condition_agent_attrs_value_regex_extra'] : array();
					$agent_meta_values = array();
					if ( ! empty( $value ) && ! empty( $operator ) ) {
						$extra_agent_attr_array = array();
						if ( ! empty( $agent_attr_extra_field ) && ! empty( $agent_attr_extra_value ) && ! empty( $agent_attr_extra_operator ) && ! empty( $agent_attr_regx ) ) {
							foreach ( $agent_attr_extra_field as $key => $field_value ) {
								if ( ! empty( $agent_attr_extra_value[ $key ] ) && ! empty( $agent_attr_extra_operator[ $key ] )  && ! empty( $agent_attr_regx[ $key ] ) ) {
									$extra_agent_attr_array[] = array(
												'field' => $field_value,
												'value' => $agent_attr_extra_value[ $key ],
												'operator' => $agent_attr_extra_operator[ $key ],
												'regex' => $agent_attr_regx[ $key ],
											);
								}
							}
						}
						update_post_meta( $post_id, 'extra_agent_attributes_data', $extra_agent_attr_array );

						$agent_meta_values[] = array(
								'field' => $value,
								'value' => $condition_agent_attrs_value,
								'operator' => $operator,
								'regex' => $condition_regx,
							);
						$agent_attrs = array(
							'value' => $value,
							'value_txt' => $condition_agent_attrs_value,
							'operator' => $operator,
							'regex' => $condition_regx,
						);

						update_post_meta( $post_id, $this->slug, $agent_attrs );
						$agent_meta_values = array_merge( $agent_meta_values, $extra_agent_attr_array );

						if ( 'default' === $operator ) {
							// If first operator is default.
							//  Make Everything unselected for client attribute.
							update_post_meta( $post_id, $this->slug, array() );
							$agent_meta_values = array();
						}
					}
					update_post_meta( $post_id, 'agent_attributes_data', $agent_meta_values );
				} else if ( 'condition_client_attrs' === $this->slug ) {
					$value = ! empty( $_POST[ $this->slug ] ) ? $_POST[ $this->slug ] : '';
					$condition_client_attrs_value = ! empty( $_POST[ $this->slug . '_value' ] ) ? $_POST[ $this->slug . '_value' ] : '';

					$operator_slug = $this->slug . '_operator';
					$operator = ! empty( $_POST[ $operator_slug ] ) ? $_POST[ $operator_slug ] : AS_RE_DEFAULT_OPERATOR;
					$condition_regx = ! empty( $_POST[ $this->slug . '_regex' ] ) ? $_POST[ $this->slug . '_regex' ] : '';

					$client_attr_extra_field = ! empty( $_POST[ $this->slug . '_extra' ] ) ? $_POST[ $this->slug . '_extra' ] : array();
					$client_attr_extra_value = ! empty( $_POST[ $this->slug . '_value_extra' ] ) ? $_POST[ $this->slug . '_value_extra' ] : array();
					$client_attr_extra_operator = ! empty( $_POST[ $this->slug . '_value_operator_extra' ] ) ? $_POST['condition_client_attrs_value_operator_extra'] : array();
					$client_attr_regx = ! empty( $_POST[ $this->slug . '_value_regex_extra' ] ) ? $_POST['condition_client_attrs_value_regex_extra'] : array();
					$client_meta_values = array();
					if ( ! empty( $value ) && ! empty( $operator ) ) {
						$extra_attr_array = array();
						if ( ! empty( $client_attr_extra_field ) && ! empty( $client_attr_extra_value ) && ! empty( $client_attr_extra_operator ) && ! empty( $client_attr_regx ) ) {
							foreach ( $client_attr_extra_field as $key => $field_value ) {
								if ( ! empty( $client_attr_extra_value[ $key ] ) && ! empty( $client_attr_extra_operator[ $key ] )  && ! empty( $client_attr_regx[ $key ] ) ) {
									$extra_attr_array[] = array(
												'field' => $field_value,
												'value' => $client_attr_extra_value[ $key ],
												'operator' => $client_attr_extra_operator[ $key ],
												'regex' => $client_attr_regx[ $key ],
											);
								}
							}
						}
						update_post_meta( $post_id, 'extra_attributes_data', $extra_attr_array );

						$client_meta_values[] = array(
								'field' => $value,
								'value' => $condition_client_attrs_value,
								'operator' => $operator,
								'regex' => $condition_regx,
							);
						$client_attrs = array(
							'value' => $value,
							'value_txt' => $condition_client_attrs_value,
							'operator' => $operator,
							'regex' => $condition_regx,
						);

						update_post_meta( $post_id, $this->slug, $client_attrs );
						$client_meta_values = array_merge( $client_meta_values, $extra_attr_array );

						if ( 'default' === $operator ) {
							// If first operator is default.
							//  Make Everything unselected for client attribute.
							update_post_meta( $post_id, $this->slug, array() );
							$client_meta_values = array();
						}
					}
					update_post_meta( $post_id, 'client_attributes_data', $client_meta_values );
				} else {
					update_post_meta( $this->post_id, $this->slug, $meta_value );
				}
			}
		}

	}
}
