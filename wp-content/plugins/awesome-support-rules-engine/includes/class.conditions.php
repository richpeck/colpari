<?php
namespace AsRulesEngine;
use WP_Query;
use DateTime;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class: RE_Conditions handle rules engine condition related work.
 */
if ( ! class_exists( 'RE_Conditions' ) ) {

	class RE_Conditions {

		/**
		 * Array of applied condition on rules
		 *
		 * @var array $meta_condition values.
		 */
		private $meta_condition = array();
		/**
		 * Current Rules post ID
		 *
		 * @var int $post_id values.
		 */
		private $post_id;
		/**
		 * Current Ticket ID
		 *
		 * @var int $ticket_id values.
		 */
		private $ticket_id;
		/**
		 * Current Reply ID
		 *
		 * @var int $reply_id values.
		 */
		private $reply_id;
		/**
		 * An array of all condition and filters fields objects
		 *
		 * @var array $conditions values.
		 */
		private $conditions;

		/**
		 * RE_Conditions Constructor.
		 */
		public function __construct( $instances = '' ) {
			if ( ! empty( $instances ) && ( is_array( $instances ) || is_object( $instances ) ) ) {
				foreach ( $instances as $key => $value ) {
					$this->$key = $value;
				}
			}
		}

		/**
		 * Function to check conditional of ruleset.
		 *
		 * @param int $post_id ruleset ID.
		 * @param int $ticket_id ticket ID.
		 *
		 * @return boolean $statement result of conditional statement for action.
		 */
		public function check( $post_id, $ticket_id, $reply_id ) {
			$this->post_id = $post_id;
			$this->ticket_id = $ticket_id;
			$this->reply_id = $reply_id;
			$this->get_conditions();
			$statement = false;
			if ( ! empty( $this->meta_condition ) ) {
				/**
				 * Remove unnecessary keys
				 */
				if ( isset( $this->meta_condition['condition_client_attrs_value'] ) ) {
					unset( $this->meta_condition['condition_client_attrs_value'] );
				}
				if ( isset( $this->meta_condition['condition_custom_field_value'] ) ) {
					unset( $this->meta_condition['condition_custom_field_value'] );
				}
				if ( isset( $this->meta_condition['condition_agent_attrs_value'] ) ) {
					unset( $this->meta_condition['condition_agent_attrs_value'] );
				}
				if ( isset( $this->meta_condition ) && ! empty( $this->meta_condition ) ) {

					// add a default value for $prev_statement
					$prev_statement = true;

					foreach ( $this->meta_condition as $key => $value ) {
						/**
						 * Get condition value from ticket data
						 * NOTE: Ticket data and not from meta data
						 */
						$db_value = $this->get_condition_value( $key );
						$key = preg_replace( '/condition_/', '', $key );

						// Get current condition operator and value.
						$extra = '';
						$operator = $value['operator'];
						if ( isset( $value['extra_operator'] ) && ! empty( $value['extra_operator'] ) ) {
							$extra = $value['extra_operator'] ;
						}

						$condition_value = '';
						if ( isset( $value['value'] ) ) {
							$condition_value = $value['value'];
						}
						$regex = '';
						if ( isset( $value['regex'] ) ) {
							$regex = $value['regex'];
						}

						// call a function that will handle conditions based on passed OR, AND, NOT.
						$prev_statement = $this->re_parse_conditions( $prev_statement, $operator, $condition_value, $db_value, $key, $extra, $regex );
						if ( true === $prev_statement && 'or' === $operator ) {
							unset( $this->meta_condition );
							return true;
						}
						$statement = $prev_statement;
					}
				} else {
					unset( $this->meta_condition );
					return true;
				}
			} else {
				unset( $this->meta_condition );
				return true;
			}
				unset( $this->meta_condition );
				return $statement;
		}

		/**
		 * Get Custom field type of Awesome support.
		 *
		 * @param  string $field_name Field name.
		 * @return string $field_type the type custom fields.
		 */
		function re_get_custom_field_type( $field_name ) {
			$wpas_custom_fields = WPAS()->custom_fields->get_custom_fields();
			$field_type = false;
			if( !empty( $wpas_custom_fields ) ){
				foreach ( $wpas_custom_fields as $key => $value ) {
					if ( isset( $value['name'] ) && $value['name'] === $field_name ) {
						if( isset( $value['args']['field_type'] ) ){
							$field_type = $value['args']['field_type'];
						}
					}
				}				
			}
			return $field_type;
		}

		/**
		 * $prev_statement ->  what was the previous result?
		 * $operator -> What is the current operator?
		 * $condition_value -> what is the value?
		 * $key -> what is the key?
		 */
		/**
		 * Function to manage parsing of query based on different operator used.
		 * @param  boolean $prev_statement  result return by previous query.
		 * @param  string $operator        Operator used for query.
		 * @param  array|string  $condition_value value assign in condition.
		 * @param  string $db_value        value stored for ticket
		 * @param  string $key             key of condition param.
		 * @param  array $extra           Extra values passed for some conditions.
		 * @param  string $regex          Starts/Equals/Contains/Regex key
		 *
		 * @return   boolean		conditional statement result.
		 */
		function re_parse_conditions( $prev_statement, $operator, $condition_value, $db_value, $key, $extra, $regex  ) {
			if ( ! empty( $prev_statement ) || false === $prev_statement ) {

				if ( 'and' === $operator || 'not' === $operator ) {
					if ( true === $prev_statement ) {
						// IF previous condition was true then do some AND work here.
						$result = $this->generate_condition_result( $key, $condition_value, $db_value, $operator, $extra, $regex );
						return $result;
					} else {
						// If the previous condition was not true that means the entire condition is not valid.
						return false;
					}
				}
				if ( 'or' === $operator ) {
					// If Operator is OR then
					//  Check if previous value is true and then no need to check just return true.
					if ( true === $prev_statement ) {
						return true;
					} else {
						//  If previous value is false then we will have to check condition for OR operator.
						$result = $this->generate_condition_result( $key, $condition_value, $db_value, $operator, $extra, $regex );
						return $result;
					}
				}
			} else {
				// simply run the condition here.
				$result = $this->generate_condition_result( $key, $condition_value, $db_value, $operator, $extra, $regex );
				return $result;
			}
			return false;
		}

		/**
		 * Function to return result after condition check.
		 *
		 * @param  string $key             key of condition param.
		 * @param  array|string  $condition_value value assign in condition.
		 * @param  string $db_value        value stored for ticket
		 * @param  string $operator        Operator use for condition.
		 * @param  array $extra           Extra values passed for some conditions.
		 *
		 * @return   boolean		conditional statement result.
		 */
		private function generate_condition_result( $key, $condition_value, $db_value, $operator, $extra, $regex ) {
			$client_ID = $this->get_client_ID();
			$agent_ID = $this->get_agent_ID();
			switch ( $key ) {
				/**
				 * Client attributes( main key )
				*/
				case 'custom_field':
					if ( ! empty( $condition_value ) && is_array( $condition_value ) ) {
						$and_array = array();
						$or_array = array();
						foreach ( $condition_value as $key => $value ) {
							if ( 'and' === $value['operator']  || 'not' === $value['operator'] ) {
								$and_array[] = $value;
							}

							if ( 'or' === $value['operator'] ) {
								$or_array[] = $value;
							}
						}
						/**
						 * For AND operator condition array: I have to check all condition should be true. If any one get false stop
						 * there.
						 *
						 *  For OR operator condition array: I have to check if any one condition get true stop there.
						 *
						 */
						$condition_result = false;
						if ( ! empty( $and_array ) ) {
							foreach ( $and_array as $and_key => $and_value ) {
								// do some and related work here.
								$db_value = get_post_meta( $this->ticket_id, '_wpas_' . $and_value['field'], true );
								$field_type = $this->re_get_custom_field_type( $and_value['field'] );
								$regex = $and_value['regex'];

								if ( 'checkbox' === $field_type || 'upload' === $field_type  ) {

									if ( 'and' === $and_value['operator'] ) {
										if ( ! empty( $db_value ) && is_array( $db_value ) ) {
											if ( in_array( $and_value['value'], $db_value ) ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} else {
											$condition_result = false;
											break;
										}
									}
									if ( 'not' === $and_value['operator'] ) {
										if ( ! empty( $db_value )  && is_array( $db_value ) ) {
											if ( ! in_array( $and_value['value'], $db_value ) ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} else {
											$condition_result = false;
											break;
										}
									}
								} elseif ( 'taxonomy' === $field_type ) {

									$ticket_term = get_term( $and_value['value'] );
									if ( 'and' === $and_value['operator'] ) {
										if ( ! empty( $ticket_term ) ) {
											if ( true === has_term( $and_value['value'], $ticket_term->taxonomy, $this->ticket_id ) ) {
												// if Category exist in ticket.
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} else {
											$condition_result = false;
											break;
										}
									}
									if ( 'not' === $and_value['operator'] ) {
										if ( ! empty( $ticket_term ) ) {
											if ( true !== has_term( $and_value['value'], $ticket_term->taxonomy, $this->ticket_id ) ) {
												// if Category not exist in ticket.
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} else {
											$condition_result = false;
											break;
										}
									}
								} elseif ( 'date-field' === $field_type ) { 
									// If it is date field.
									// for all string and url type
									if ( 'and' === $and_value['operator'] ) {
										if ( '>' === $regex ) {
											if ( strtotime($db_value) > strtotime($and_value['value'])) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( '<' === $regex ) {
											if ( strtotime($db_value) < strtotime($and_value['value']) ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( 'equals' === $regex ) {
											if ( strtotime($and_value['value']) == strtotime($db_value) ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										}
									}
									if ( 'not' === $and_value['operator'] ) {
										if ( '>' === $regex ) {
											if ( strtotime($db_value) <= strtotime($and_value['value']) ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( '<' === $regex ) {
											if ( strtotime($db_value) >= strtotime($and_value['value']) ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( 'equals' === $regex ) {
											if ( strtotime($and_value['value']) !== strtotime($db_value) ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										}
									}
								} else {
									// for all string and url type
									if ( 'and' === $and_value['operator'] ) {
										if ( 'regex' === $regex ) {
											if ( @preg_match( $and_value['value'], $db_value ) == true ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( 'starts' === $regex ) {
											$search_length = strlen( $and_value['value'] );
											$db_value_beginning = substr( $db_value, 0, $search_length );

											if ( strtolower( $db_value_beginning ) === strtolower( $and_value['value'] ) ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( 'equals' === $regex ) {
											if ( strtolower( $and_value['value'] ) === strtolower( $db_value ) ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( 'contains' === $regex ) {
											$data_contains_check = @preg_match( "/{$and_value['value']}/i", $db_value );
											if ( $data_contains_check ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
	 									}
									}
									if ( 'not' === $and_value['operator'] ) {
										if ( 'regex' === $regex ) {
											if ( @preg_match( $and_value['value'], $db_value ) != true ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( 'starts' === $regex ) {
											$search_length = strlen( $and_value['value'] );
											$db_value_beginning = substr( $db_value, 0, $search_length );

											if ( strtolower( $db_value_beginning ) !== strtolower( $and_value['value'] ) ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( 'equals' === $regex ) {
											if ( strtolower( $and_value['value'] ) !== strtolower( $db_value ) ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( 'contains' === $regex ) {
											$data_contains_check = @preg_match( "/{$and_value['value']}/i", $db_value );
											if ( ! $data_contains_check ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
	 									}
									}
								}
							}
						}

						if ( ! empty( $or_array ) && (false === $condition_result) ) {
							foreach ( $or_array as $or_key => $or_value ) {
								$regex = $or_value['regex'];
								// do some or related work here.
								$db_value = get_post_meta( $this->ticket_id, '_wpas_' . $or_value['field'], true );
								$field_type = $this->re_get_custom_field_type( $or_value['field'] );
								if ( 'checkbox' === $field_type || 'upload' === $field_type  ) {
									if ( ! empty( $db_value ) && is_array( $db_value ) ) {
										if ( in_array( $or_value['value'], $db_value ) ) {
											$condition_result = true;
											break;
										} else {
											$condition_result = false;
										}
									} else {
										$condition_result = false;
									}
								} elseif ( 'date-field' === $field_type ) { 
									// If it is date field.
									if ( '>' === $regex ) {
										if ( strtotime($db_value) > strtotime($or_value['value'])) {
											$condition_result = true;
											break;
										} else {
											$condition_result = false;
										}
									} elseif ( '<' === $regex ) {
										if ( strtotime($db_value) < strtotime($or_value['value']) ) {
											$condition_result = true;
											break;
										} else {
											$condition_result = false;
										}
									} elseif ( 'equals' === $regex ) {
										if ( strtotime($or_value['value']) == strtotime($db_value) ) {
											$condition_result = true;
											break;
										} else {
											$condition_result = false;
										}
									}
								} elseif ( 'taxonomy' === $field_type ) {
									$ticket_term = get_term( $or_value['value'] );
									if ( ! empty( $ticket_term ) ) {
										$taxonomy = has_term( $or_value['value'], $ticket_term->taxonomy, $this->ticket_id );
										if ( true === has_term( $or_value['value'], $ticket_term->taxonomy, $this->ticket_id ) ) {
											// if Category exist in ticket.
											$condition_result = true;
											break;
										} else {
											$condition_result = false;
										}
									} else {
										$condition_result = false;
									}
								} else {
									// For all string and URL'S
									if ( 'regex' === $regex ) {
										if ( @preg_match( $or_value['value'], $db_value ) == true ) {
											$condition_result = true;
											break;
										} else {
											$condition_result = false;
										}
									} elseif ( 'starts' === $regex ) {
										$search_length = strlen( $or_value['value'] );
										$db_value_beginning = substr( $db_value, 0, $search_length );

										if ( strtolower( $db_value_beginning ) === strtolower( $or_value['value'] ) ) {
											$condition_result = true;
											break;
										} else {
											$condition_result = false;
										}
									} elseif ( 'equals' === $regex ) {
										if ( strtolower( $or_value['value'] ) === strtolower( $db_value ) ) {
											$condition_result = true;
											break;
										} else {
											$condition_result = false;
										}
									} elseif ( 'contains' === $regex ) {
										$data_contains_check = @preg_match( "/{$or_value['value']}/i", $db_value );
										if ( $data_contains_check ) {
											$condition_result = true;
											break;
										} else {
											$condition_result = false;
										}
	 								}
								}
							}
						}
						return $condition_result;

					}
				break;
				case 'client_attrs':
					if ( ! empty( $condition_value ) && is_array( $condition_value ) ) {
						$and_array = array();
						$or_array = array();
						foreach ( $condition_value as $key => $value ) {
							if ( 'and' === $value['operator']  || 'not' === $value['operator'] ) {
								$and_array[] = $value;
							}

							if ( 'or' === $value['operator'] ) {
								$or_array[] = $value;
							}
						}

						/**
						 * For AND operator condition array: I have to check all condition should be true. If any one get false stop
						 * there.
						 *
						 *  For OR operator condition array: I have to check if any one condition get true stop there.
						 *
						 */
						$condition_result = false;
						if ( ! empty( $and_array ) ) {

							foreach ( $and_array as $and_key => $and_value ) {
								// do some and related work here.
								$regex = $and_value['regex'];
								$db_value = get_user_meta( $client_ID, $and_value['field'], true );
								if ( 'user_name' === $and_value['field'] ) {
									$userdata = get_userdata( $client_ID );
									$db_value = $userdata->user_login;
								}
								if ( 'email' === $and_value['field'] ) {
									$userdata = get_userdata( $client_ID );
									$db_value = $userdata->user_email;
								}
								if ( 'display_name' === $and_value['field'] ) {
									$userdata = get_userdata( $client_ID );
									$db_value = $userdata->display_name;
								}

								if ( 'and' === $and_value['operator'] ) {
									if ( empty( $db_value ) ) {
										$condition_result = false;
										break;
									}

									if ( is_array( $db_value ) ) {
										// If Db value is array then do array checking here.
										if ( true === in_array( $and_value['value'], $db_value ) ) {
											$condition_result = true;
										} else {
											$condition_result = false;
											break;
										}
									} else {
										if ( 'regex' === $regex ) {
											if ( @preg_match( $and_value['value'], $db_value ) == true ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( 'starts' === $regex ) {
											$search_length = strlen( $and_value['value'] );
											$db_value_beginning = substr( $db_value, 0, $search_length );

											if ( strtolower( $db_value_beginning ) === strtolower( $and_value['value'] ) ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( 'equals' === $regex ) {
											if ( strtolower( $and_value['value'] ) === strtolower( $db_value ) ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( 'contains' === $regex ) {
											$data_contains_check = @preg_match( "/{$and_value['value']}/i", $db_value );
											if ( $data_contains_check ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
	 									}
									}
								}

								if ( 'not' === $and_value['operator'] ) {
									if ( empty( $db_value ) ) {
										$condition_result = false;
										break;
									}

									if ( is_array( $db_value ) ) {
										// If Db value is array then do array checking here.
										if ( true !== in_array( $and_value['value'], $db_value ) ) {
											$condition_result = true;
										} else {
											$condition_result = false;
											break;
										}
									} else {
										if ( 'regex' === $regex ) {
											if ( @preg_match( $and_value['value'], $db_value ) != true ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( 'starts' === $regex ) {
											$search_length = strlen( $and_value['value'] );
											$db_value_beginning = substr( $db_value, 0, $search_length );

											if ( strtolower( $db_value_beginning ) !== strtolower( $and_value['value'] ) ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( 'equals' === $regex ) {
											if ( strtolower( $and_value['value'] ) !== strtolower( $db_value ) ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( 'contains' === $regex ) {
											$data_contains_check = @preg_match( "/{$and_value['value']}/i", $db_value );
											if ( ! $data_contains_check ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
	 									}
									}
								}
							}
						}

						if ( ! empty( $or_array ) && (false === $condition_result) ) {
							$regex = $or_value['regex'];
							foreach ( $or_array as $or_key => $or_value ) {
								// do some or related work here.
								$db_value = get_user_meta( $client_ID, $or_value['field'] );
								if ( 'user_name' === $or_value['field'] ) {
									$userdata = get_userdata( $client_ID );
									$db_value = $userdata->user_login;
								}
								if ( 'email' === $and_value['field'] ) {
									$userdata = get_userdata( $client_ID );
									$db_value = $userdata->user_email;
								}
								if ( 'display_name' === $and_value['field'] ) {
									$userdata = get_userdata( $client_ID );
									$db_value = $userdata->display_name;
								}

								if ( empty( $db_value ) ) {
									$condition_result = false;
								}

								if ( is_array( $db_value ) ) {
									// If Db value is array then do array checking here.
									if ( true === in_array( $or_value['value'], $db_value ) ) {
										$condition_result = true;
										break;
									} else {
										$condition_result = false;
									}
								} else {

									if ( 'regex' === $regex ) {
										if ( @preg_match( $or_value['value'], $db_value ) == true ) {
											$condition_result = true;
											break;
										} else {
											$condition_result = false;
										}
									} elseif ( 'starts' === $regex ) {
										$search_length = strlen( $or_value['value'] );
										$db_value_beginning = substr( $db_value, 0, $search_length );

										if ( strtolower( $db_value_beginning ) === strtolower( $or_value['value'] ) ) {
											$condition_result = true;
											break;
										} else {
											$condition_result = false;
										}
									} elseif ( 'equals' === $regex ) {
										if ( strtolower( $or_value['value'] ) === strtolower( $db_value ) ) {
											$condition_result = true;
											break;
										} else {
											$condition_result = false;
										}
									} elseif ( 'contains' === $regex ) {
										$data_contains_check = @preg_match( "/{$or_value['value']}/i", $db_value );
										if ( $data_contains_check ) {
											$condition_result = true;
											break;
										} else {
											$condition_result = false;
										}
	 								}
	 							}
							}
						}
						return $condition_result;
					}
				break;
				case 'agent_attrs':
					if ( ! empty( $condition_value ) && is_array( $condition_value ) ) {
						$and_array = array();
						$or_array = array();
						foreach ( $condition_value as $key => $value ) {
							if ( 'and' === $value['operator']  || 'not' === $value['operator'] ) {
								$and_array[] = $value;
							}

							if ( 'or' === $value['operator'] ) {
								$or_array[] = $value;
							}
						}

						/**
						 * For AND operator condition array: I have to check all condition should be true. If any one get false stop
						 * there.
						 *
						 *  For OR operator condition array: I have to check if any one condition get true stop there.
						 *
						 */
						$condition_result = false;
						if ( ! empty( $and_array ) ) {

							foreach ( $and_array as $and_key => $and_value ) {
								// do some and related work here.
								$regex = $and_value['regex'];
								$db_value = get_user_meta( $agent_ID, $and_value['field'], true );
								if ( 'user_name' === $and_value['field'] ) {
									$userdata = get_userdata( $agent_ID );
									$db_value = $userdata->user_login;
								}
								if ( 'email' === $and_value['field'] ) {
									$userdata = get_userdata( $agent_ID );
									$db_value = $userdata->user_email;
								}
								if ( 'display_name' === $and_value['field'] ) {
									$userdata = get_userdata( $agent_ID );
									$db_value = $userdata->display_name;
								}

								if ( 'and' === $and_value['operator'] ) {
									if ( empty( $db_value ) ) {
										$condition_result = false;
										break;
									}

									if ( is_array( $db_value ) ) {
										// If Db value is array then do array checking here.
										if ( true === in_array( $and_value['value'], $db_value ) ) {
											$condition_result = true;
										} else {
											$condition_result = false;
											break;
										}
									} else {
										if ( 'regex' === $regex ) {
											if ( @preg_match( $and_value['value'], $db_value ) == true ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( 'starts' === $regex ) {
											$search_length = strlen( $and_value['value'] );
											$db_value_beginning = substr( $db_value, 0, $search_length );

											if ( strtolower( $db_value_beginning ) === strtolower( $and_value['value'] ) ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( 'equals' === $regex ) {

											if ( strtolower( $and_value['value'] ) === strtolower( $db_value ) ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( 'contains' === $regex ) {
											$data_contains_check = @preg_match( "/{$and_value['value']}/i", $db_value );
											if ( $data_contains_check ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
	 									}
									}
								}

								if ( 'not' === $and_value['operator'] ) {
									if ( empty( $db_value ) ) {
										$condition_result = false;
										break;
									}

									if ( is_array( $db_value ) ) {
										// If Db value is array then do array checking here.
										if ( true !== in_array( $and_value['value'], $db_value ) ) {
											$condition_result = true;
										} else {
											$condition_result = false;
											break;
										}
									} else {
										if ( 'regex' === $regex ) {
											if ( @preg_match( $and_value['value'], $db_value ) != true ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( 'starts' === $regex ) {
											$search_length = strlen( $and_value['value'] );
											$db_value_beginning = substr( $db_value, 0, $search_length );

											if ( strtolower( $db_value_beginning ) !== strtolower( $and_value['value'] ) ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( 'equals' === $regex ) {
											if ( strtolower( $and_value['value'] ) !== strtolower( $db_value ) ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
										} elseif ( 'contains' === $regex ) {
											$data_contains_check = @preg_match( "/{$and_value['value']}/i", $db_value );
											if ( ! $data_contains_check ) {
												$condition_result = true;
											} else {
												$condition_result = false;
												break;
											}
	 									}
									}
								}
							}
						}

						if ( ! empty( $or_array ) && (false === $condition_result) ) {
							$regex = $or_value['regex'];
							foreach ( $or_array as $or_key => $or_value ) {
								// do some or related work here.
								$db_value = get_user_meta( $agent_ID, $or_value['field'] );
								if ( 'user_name' === $or_value['field'] ) {
									$userdata = get_userdata( $agent_ID );
									$db_value = $userdata->user_login;
								}
								if ( 'email' === $and_value['field'] ) {
									$userdata = get_userdata( $agent_ID );
									$db_value = $userdata->user_email;
								}
								if ( 'display_name' === $and_value['field'] ) {
									$userdata = get_userdata( $agent_ID );
									$db_value = $userdata->display_name;
								}

								if ( empty( $db_value ) ) {
									$condition_result = false;
								}

								if ( is_array( $db_value ) ) {
									// If Db value is array then do array checking here.
									if ( true === in_array( $or_value['value'], $db_value ) ) {
										$condition_result = true;
										break;
									} else {
										$condition_result = false;
									}
								} else {

									if ( 'regex' === $regex ) {
										if ( @preg_match( $or_value['value'], $db_value ) == true ) {
											$condition_result = true;
											break;
										} else {
											$condition_result = false;
										}
									} elseif ( 'starts' === $regex ) {
										$search_length = strlen( $or_value['value'] );
										$db_value_beginning = substr( $db_value, 0, $search_length );

										if ( strtolower( $db_value_beginning ) === strtolower( $or_value['value'] ) ) {
											$condition_result = true;
											break;
										} else {
											$condition_result = false;
										}
									} elseif ( 'equals' === $regex ) {
										if ( strtolower( $or_value['value'] ) === strtolower( $db_value ) ) {
											$condition_result = true;
											break;
										} else {
											$condition_result = false;
										}
									} elseif ( 'contains' === $regex ) {
										$data_contains_check = @preg_match( "/{$or_value['value']}/i", $db_value );
										if ( $data_contains_check ) {
											$condition_result = true;
											break;
										} else {
											$condition_result = false;
										}
	 								}
	 							}
							}
						}
						return $condition_result;
					}
				break;
				case 'age_last_agent_reply':
				case 'age_last_customer_reply':
				case 'age_ticket':
					if ( ! empty( $condition_value ) && ! empty( $extra ) ) {

						if ( 'not' === $operator ) {
							if ( '>' === $extra ) {
								return ( $db_value > $condition_value )? false : true;
							}

							if ( '<' === $extra ) {
								return ( $db_value < $condition_value)? false : true;
							}

							if ( '=' === $extra ) {
								return ( $condition_value == $db_value)? false : true;
							}
						} else {
							if ( '>' === $extra ) {
								return ( $db_value > $condition_value )? true : false;
							}

							if ( '<' === $extra ) {
								return ( $db_value < $condition_value)? true : false;
							}

							if ( '=' === $extra ) {
								return ( $condition_value == $db_value)? true : false;
							}
						}
					}
				break;
				case 'agent_attrs_caps':
				case 'client_caps_fields':
					// If default option is selected for status field then allow condition to move.
					if( is_array( $condition_value ) && in_array( 'default', $condition_value ) ){
						return true;
					}
					if ( ! empty( $condition_value ) ) {
						if ( 'not' === $operator ) {
							$count_intersect = count( array_intersect( $condition_value, $db_value ) );
							return ( ! empty( $count_intersect )) ? true : false;
						} else {
							$count_intersect = count( array_intersect( $condition_value, $db_value ) );
							return ( ! empty( $count_intersect )) ? true : false;
						}
					}
				break;
				case 'reply_contents':
					if ( ! empty( $condition_value ) ) {
						if ( 'not' === $operator ) {
							if ( 'regex' === $regex ) {
								if ( ! empty( $db_value ) && is_array( $db_value ) ) {
									foreach ( $db_value as $reply ) {
										if ( @preg_match( $condition_value, $reply ) != true ) {
											return true;
										} else {
											return false;
										}
									}
								}
							} elseif ( 'starts' === $regex ) {
								$search_length = strlen( $condition_value );
								if ( ! empty( $db_value ) && is_array( $db_value )  ) {
									foreach ( $db_value as $reply ) {
										$reply_beginning = substr( $reply, 0, $search_length );
										if ( strtolower( $reply_beginning ) !== strtolower( $condition_value ) ) {
											return true;
										} else {
											return false;
										}
									}
								}
							} elseif ( 'equals' === $regex ) {
								if ( ! empty( $db_value ) && is_array( $db_value )  ) {
									foreach ( $db_value as $reply ) {
										if ( strtolower( $reply ) !== strtolower( $condition_value ) ) {
											return true;
										} else {
											return false;
										}
									}
								}
							} elseif ( 'contains' === $regex ) {
								if ( ! empty( $db_value ) && is_array( $db_value ) ) {
									foreach ( $db_value as $reply ) {
										$data_contains_check = @preg_match( "/{$condition_value}/i", $reply );
										if ( ! $data_contains_check ) {
											return true;
										} else {
											return false;
										}
									}
								}
							}
						} else {
							if ( 'regex' === $regex ) {
								if ( ! empty( $db_value ) && is_array( $db_value )  ) {
									foreach ( $db_value as $reply ) {
										if ( true == @preg_match( $condition_value, $reply ) ) {
											return true;
										} else {
											return false;
										}
									}
								}
							} elseif ( 'starts' === $regex ) {
								$search_length = strlen( $condition_value );
								if ( ! empty( $db_value ) && is_array( $db_value ) ) {
									foreach ( $db_value as $reply ) {
										$reply_beginning = substr( $reply, 0, $search_length );
										if ( strtolower( $reply_beginning ) === strtolower( $condition_value ) ) {
											return true;
										} else {
											return false;
										}
									}
								}
							} elseif ( 'equals' === $regex ) {
								if ( ! empty( $db_value ) && is_array( $db_value ) ) {
									foreach ( $db_value as $reply ) {
										if ( strtolower( $reply ) === strtolower( $condition_value ) ) {
											return true;
										} else {
											return false;
										}
									}
								}
							} elseif ( 'contains' === $regex ) {
								if ( ! empty( $db_value ) && is_array( $db_value ) ) {
									foreach ( $db_value as $reply ) {
										$data_contains_check = @preg_match( "/{$condition_value}/i", $reply );
										if ( $data_contains_check ) {
											return true;
										} else {
											return false;
										}
									}
								}
							}
						}
					}
				break;
				case 'time':
					if ( ! empty( $condition_value ) ) {
						$condition_value = explode( 'T', $condition_value );
						$condition_value = $condition_value[0];

					}

					if ( ! empty( $db_value ) ) {
						$db_value_date = explode( ' ', $db_value );
					}

					if ( ! empty( $db_value_date ) ) {
						if ( 'not' === $operator ) {
							if ( '>' === $extra ) {
								return ( $db_value_date[0] > $condition_value )? false : true;
							}

							if ( '<' === $extra ) {
								return ( $db_value_date[0] < $condition_value)? false : true;
							}

							if ( '=' === $extra ) {
								return ( $condition_value == $db_value_date[0])? false : true;
							}
						} else {
							if ( '>' === $extra ) {
								return ( $db_value_date[0] > $condition_value )? true : false;
							}

							if ( '<' === $extra ) {
								return ( $db_value_date[0] < $condition_value)? true : false;
							}

							if ( '=' === $extra ) {
								return ( $condition_value == $db_value_date[0])? true : false;
							}
						}
					}

				break;
				case 'state':
					if ( 'both' === $condition_value ) { return true;}

					if ( 'not' === $operator ) {
						if ( $condition_value !== $db_value ) {
							return true;
						} else {
							return false;
						}
					} else {
						if ( $condition_value === $db_value ) {
							return true;
						} else {
							return false;
						}
					}

				break;
				case 'contents':
				case 'subject':
					if ( ! empty( $condition_value ) && ! empty( $regex ) ) {
						if ( 'not' === $operator ) {
							if ( 'regex' === $regex ) {
								return (@preg_match( $condition_value, $db_value ) != true);
							} elseif ( 'starts' === $regex ) {
								$search_length = strlen( $condition_value );
								$db_value_beginning = substr( $db_value, 0, $search_length );
								return (strtolower( $db_value_beginning ) !== strtolower( $condition_value ));
							} elseif ( 'equals' === $regex ) {
								return (strtolower( $condition_value ) !== strtolower( $db_value ));
							} elseif ( 'contains' === $regex ) {
								return (preg_match( "/{$condition_value}/i", $db_value ) != true);
							}
						} else {
							if ( 'regex' === $regex ) {
								return (@preg_match( $condition_value, $db_value ) == true);
							} elseif ( 'starts' === $regex ) {
								$search_length = strlen( $condition_value );
								$db_value_beginning = substr( $db_value, 0, $search_length );
								return (strtolower( $db_value_beginning ) === strtolower( $condition_value ));
							} elseif ( 'equals' === $regex ) {
								return (strtolower( $condition_value ) === strtolower( $db_value ));
							} elseif ( 'contains' === $regex ) {
								return (preg_match( "/{$condition_value}/i", $db_value ) == true);
							}
						}
					}
				break;

				case 'agent_name':
				case 'client_email':
				case 'client_name':

					if ( 'not' === $operator ) {
						if ( ! empty( $condition_value ) && is_array( $condition_value ) ) {
							if ( ! in_array( $db_value, $condition_value ) ) {
								return true;
							} else {
								return false;
							}
						}
					} else {
						if ( ! empty( $condition_value ) && is_array( $condition_value ) ) {
							if ( in_array( $db_value, $condition_value ) ) {
								return true;
							} else {
								return false;
							}
						}
					}

				break;
				case 'status':
					// If default option is selected for status field then allow condition to move.
					if( is_array( $condition_value ) && in_array( 'default', $condition_value ) ){
						return true;
					}

					$is_array = is_array( $condition_value );
					$condition_value = array_values( $condition_value );
					$original_status = get_post_meta( $this->ticket_id, 'previous_ticket_status', true );
					if ( ! empty( $original_status ) ) {
						if ( 'auto-draft' === $original_status ) {
							$db_value = 'queued'	;
						} else {
							$db_value = $original_status;
						}
					}
					$condition = in_array( $db_value, $condition_value );
					if ( 'not' === $operator ) {

						if ( $is_array && false === $condition ) {
							return true;
						} else {
							return false;
						}
					} else {

						if ( $is_array && $condition ) {
							return true;
						} else {
							return false;
						}
					}

				break;
				case 'tags_ticket':
					// If default option is selected for tags field then allow condition to move.
					if( is_array( $condition_value ) && in_array( 'default', $condition_value ) ){
						return true;
					}

					if ( ! empty( $condition_value ) ) {
						$db_tag_ids = array();
						if ( ! empty( $db_value ) && is_array( $db_value ) ) {
							foreach ( $db_value as $v ) {
								$db_tag_ids[] = $v['term_id'];
							}
						}

						$common_tags = array_intersect( $condition_value, $db_tag_ids );
						if ( 'not' === $operator ) {
							return ( count( $common_tags ) === 0 ) ? true : false;
						} else {
							return ( count( $common_tags ) > 0 ) ? true : false;
						}
					}

				break;
			}
			return false;
		}

		/**
		 * Get client ID
		*/
		private function get_client_ID() {
			if ( empty( $this->ticket_id ) ) {
				return new WP_Error( 'ticket_id_is_missing', __( 'Error: Ticket ID is needed.', 'as-rules-engine' ) );
			}
			$ticket = get_post( $this->ticket_id );
			return $ticket->post_author;
		}

		/**
		 * Get agent ID
		*/
		private function get_agent_ID() {
			if ( empty( $this->ticket_id ) ) {
				return new WP_Error( 'ticket_id_is_missing', __( 'Error: Ticket ID is needed.', 'as-rules-engine' ) );
			}
			return get_post_meta( $this->ticket_id, '_wpas_assignee', true );
		}

		/**
		 * Check the value type
		 * If string, enclosed in ""
		*/
		private function e_value( $value ) {
			if ( is_string( $value ) ) {
				/**
				 * Check if this string contains numeric
				*/
				if ( ! preg_match( '/[^0-9]/', $value ) ) {
					return $value;
				} else {
					return '"' . $value . '"';
				}
			}
			return $value;
		}

		/**
		 * Get conditions from meta data
		*/
		private function get_conditions() {
			if ( ! empty( $this->conditions ) ) {
				foreach ( $this->conditions as $key => $value ) {
					$meta = get_post_meta( $this->post_id, $key, true );
					/**
					 * In some cases, we need to exclude the special
					 * condition where it should accept "default" operator
					*/
					$special_conditions = array(
						'condition_age_ticket',
						'condition_age_last_customer_reply',
						'condition_age_last_agent_reply',
						'condition_client_attrs',
						'condition_agent_attrs',
						'condition_custom_field',
						'condition_time',
					);

					if ( in_array( $key, $special_conditions ) ) {
						if ( ! empty( $meta ) ) {
							switch ( $key ) {
								case 'condition_age_ticket':
								case 'condition_time':
								case 'condition_age_last_customer_reply':
								case 'condition_age_last_agent_reply':
									if ( 'default' !== $meta['operator'] ) {
										$this->meta_condition[ $key ] = $meta;
									}
								break;
								/**
								 * The rest of cloned data of client attributes
								*/
								case 'condition_client_attrs':
									$meta_data = get_post_meta( $this->post_id, 'client_attributes_data', true );
									$operator = $meta_data[0]['operator'];

									$this->meta_condition[ $key ] = array(
											'value' => $meta_data,
											'operator' => $operator,
										);
								break;
								/**
								 * The rest of cloned data of Agent attributes
								*/
								case 'condition_agent_attrs':
									$meta_data = get_post_meta( $this->post_id, 'agent_attributes_data', true );
									$operator = $meta_data[0]['operator'];

									$this->meta_condition[ $key ] = array(
											'value' => $meta_data,
											'operator' => $operator,
										);
								break;
								case 'condition_custom_field':
									$meta_data = get_post_meta( $this->post_id, 'custom_field_data', true );
									$operator = $meta_data[0]['operator'];

									$this->meta_condition[ $key ] = array(
											'value' => $meta_data,
											'operator' => $operator,
										);
								break;
							}
						}
					} else {
						/**
						 * Do not include the key to conditions array
						 * if the value is "default" or empty
						*/
						if ( ! empty( $meta ) && ( isset( $meta['operator'] ) && $meta['operator'] !== 'default' ) ) {
							$this->meta_condition[ $key ] = $meta;
						}
					}
				}
			}
		}

		/**
		 * Convert the opertator from word to special character
		*/
		private function convert_regex( $regex ) {
			switch ( $regex ) {
				case 'startm_contains':
					return '';
				break;
				case 'equal':
					return '';
				break;
				default:
					return ''; //REGEXP
				break;
			}
		}

		/**
		 * Convert the opertator from word to special character
		*/
		private function convert_operator( $operator ) {
			switch ( $operator ) {
				case 'and':
					return '&&';
				break;
				case 'or':
					return '||';
				break;
				case 'not':
					return '&& !';
				break;
				default:
					return '||';
				break;
			}
		}

		/**
		 * Determine the comparison operator
		 * This will be used inside parenthesis
		*/
		private function get_operator( $condition ) {
			$filter = substr( $condition, 10 );
			switch ( $filter ) {
				case 'tags_ticket':
				case 'status':
				case 'state':
				case 'subject':
				case 'contents':
				case 'client_name':
				case 'client_email':
				case 'agent_wp_role':
				case 'agent_name':
				case 'agent_attrs_caps':
				case 'reply_contents':
				case 'source':
					return '==';
				break;
				case 'age_ticket':
				case 'age_last_customer_reply':
				case 'age_last_agent_reply':
					return '<=';
				break;
				default:
					return '==';
				break;
			}
		}

		/**
		 * Get the condition value from ticket data
		 * NOTE: This is a ticket data and not on ruleset
		 * conditions meta data value
		*/
		private function get_condition_value( $condition ) {

			if ( empty( $condition ) ) {
				return new WP_Error( 'condition_is_missing', __( 'Condition should not be empty!', 'as-rules-engine' ) );
			}
			$filter = substr( $condition, 10 );
			switch ( $filter ) {
				case 'tags_ticket':
					return $this->tags_ticket();
				break;
				case 'age_ticket':
					return $this->age_ticket();
				break;
				case 'age_last_customer_reply':
					return $this->customer_last_reply();
				break;
				case 'age_last_agent_reply':
					return $this->agent_last_reply();
				break;
				case 'status':
					return $this->get_status();
				break;
				case 'state':
					return $this->get_state();
				break;
				case 'subject':
					return $this->get_subject();
				break;
				case 'contents':
					return $this->get_contents();
				break;				
				case 'client_email':
					return $this->get_client_email();
				break;
				case 'client_name':
					return $this->get_client_name();
				break;
				case 'client_attrs_caps':
					return $this->get_client_cap();
				break;
				case 'agent_wp_role':

				break;
				case 'agent_name':
					return $this->get_agent_name();
				break;
				case 'agent_attrs_caps':
					$agent_role_set = get_role( 'wpas_agent' )->capabilities;
					$agent_roles_list = array_keys( $agent_role_set );
					return $agent_roles_list;
				break;

				case 'client_caps_fields':
					$client_role_set = get_role( 'wpas_user' )->capabilities;
					$client_roles_list = array_keys( $client_role_set );
					return $client_roles_list;
				break;

				case 'custom_field':

				break;
				case 'custom_field_value':

				break;
				case 'reply_contents':
					return $this->get_reply_content();
				break;
				case 'source':

				break;
				case 'client_attrs':
					return  array();
				break;
				case 'time':
					return $this->get_ticket_time();
				break;
			}
		}

		/**
		 * Get taxonomy
		*/
		public function tags_ticket() {
			if ( empty( $this->ticket_id ) ) {
				return new WP_Error( 'ticket_id_is_missing', __( 'Error: Ticket ID is needed.', 'as-rules-engine' ) );
			}

			/**
			 * @TODO: Check why this can't find the ticket-tag
			 * The ticket-tag is part of AS main plugin
			*/

		            //$terms = wp_get_object_terms( $this->ticket_id, 'ticket-tag' );

		            global $wpdb;

		            $query = "select * from {$wpdb->prefix}term_relationships as tr
		            INNER JOIN {$wpdb->prefix}term_taxonomy as tt ON tr.term_taxonomy_id =  tt.term_taxonomy_id
		            INNER JOIN {$wpdb->prefix}terms as t ON t.term_id = tt.term_id
		            WHERE tr.object_id = $this->ticket_id AND tt.taxonomy='ticket-tag'";

		            $result = $wpdb->get_results( $query, ARRAY_A );

			if ( $result ) {
				return $result;
			}

		            return false;
		}

		/**
		 * Get ticket age
		*/
		private function age_ticket() {
			if ( empty( $this->ticket_id ) ) {
				return new WP_Error( 'ticket_id_is_missing', __( 'Error: Ticket ID is needed.', 'as-rules-engine' ) );
			}

			$ticket = get_post( $this->ticket_id );
			if ( ! empty( $ticket ) ) {
				if ( $ticket->post_type == 'ticket' ) {

					/**
					 * Set date using DateTime class
					*/
					$post_date = new DateTime( date( 'Y-m-d', strtotime( $ticket->post_date ) ) );
					$now = new DateTime( date( 'Y-m-d', strtotime( 'NOW' ) ) );

					/**
					 * Return days difference
					*/
					return $now->diff( $post_date )->format( '%a' );
				} else {
					return new WP_Error( 'invalid_id', __( 'Error: This ID is a ticket post type.', 'as-rules-engine' ) );
				}
			} else {
				return new WP_Error( 'invalid_id', __( 'Error: This ID is invalid.', 'as-rules-engine' ) );
			}
		}

		/**
		 * Get last reply from the customer
		*/
		private function customer_last_reply() {
			if ( empty( $this->ticket_id ) ) {
				return new WP_Error( 'ticket_id_is_missing', __( 'Error: Ticket ID is needed.', 'as-rules-engine' ) );
			}
			$args = array(
				'post_parent' 	=> $this->ticket_id,
				'post_status' 	=> array( 'read', 'unread' ),
				'post_type' 	=> 'ticket_reply',
				'order_by' 		=> 'ID',
				'order'         => 'DESC',
			);

			$replies = get_posts( $args );
			if ( ! empty( $replies ) && is_array( $replies ) ) {
				$post_date = new DateTime( $replies[0]->post_modified );
				$now = new DateTime();
				return $now->diff( $post_date )->days;
			}
			return null;
		}

		/**
		 * Get last reply from the agent
		*/
		private function agent_last_reply() {
			if ( empty( $this->ticket_id ) ) {
				return new WP_Error( 'ticket_id_is_missing', __( 'Error: Ticket ID is needed.', 'as-rules-engine' ) );
			}
			$args = array(
				'post_parent' 	=> $this->ticket_id,
				'post_status' 	=> array( 'read', 'unread' ),
				'post_type' 	=> 'ticket_reply',
				'order_by' 		=> 'ID',
				'order'         => 'DESC',
			);

			$replies = get_posts( $args );

			if ( ! empty( $replies ) && is_array( $replies ) ) {
				if ( ! empty( $replies ) && is_array( $replies ) ) {
					$post_date = new DateTime( $replies[0]->post_modified );
					$now = new DateTime();
					return $now->diff( $post_date )->days;
				}
			}
			return null;
		}

		/**
		 * Get ticket state
		*/
		private function get_state() {
			if ( empty( $this->ticket_id ) ) {
				return new WP_Error( 'ticket_id_is_missing', __( 'Error: Ticket ID is needed.', 'as-rules-engine' ) );
			}
			$state = get_post_meta( $this->ticket_id, '_wpas_status', true );
			return $state;
		}

		/**
		 * Get ticket status
		*/
		private function get_status() {
			/**
			 * Get available statuses.
			 * Get post status
			 */
			$post_status = get_post_status( $this->ticket_id );
			$post_status = ! empty( $post_status ) ? $post_status : '';
			return $post_status;
		}

		/**
		 * Get client email
		*/
		private function get_client_email() {
			if ( empty( $this->ticket_id ) ) {
				return new WP_Error( 'ticket_id_is_missing', __( 'Error: Ticket ID is needed.', 'as-rules-engine' ) );
			}
			$ticket = get_post( $this->ticket_id );
			/**
			 * We're using author ID/post_author instead of email from Select2 update
			*/
			return $ticket->post_author;
		}

		/**
		 * Get client name
		*/
		private function get_client_name() {
			if ( empty( $this->ticket_id ) ) {
				return new WP_Error( 'ticket_id_is_missing', __( 'Error: Ticket ID is needed.', 'as-rules-engine' ) );
			}
			$ticket = get_post( $this->ticket_id );
			/**
			 * We're using author ID/post_author instead of name concatenation from Select2 update
			*/
			return $ticket->post_author;
		}

		/**
		 * Get ticket subject/title
		*/
		private function get_subject() {
			if ( empty( $this->ticket_id ) ) {
				return new WP_Error( 'ticket_id_is_missing', __( 'Error: Ticket ID is needed.', 'as-rules-engine' ) );
			}
			$ticket = get_post( $this->ticket_id );
			return $ticket->post_title;
		}
		
		/**
		 * Get ticket contents
		*/
		private function get_contents() {
			if ( empty( $this->ticket_id ) ) {
				return new WP_Error( 'ticket_id_is_missing', __( 'Error: Ticket ID is needed.', 'as-rules-engine' ) );
			}
			$ticket = get_post( $this->ticket_id );
			return $ticket->post_content;
		}		

		/**
		 * Get client name
		 * NOTE: Client name is ambigous, use nicename for now
		*/
		private function get_client_cap() {
			if ( empty( $this->ticket_id ) ) {
				return new WP_Error( 'ticket_id_is_missing', __( 'Error: Ticket ID is needed.', 'as-rules-engine' ) );
			}
			$ticket = get_post( $this->ticket_id );
			$client = get_user_by( 'ID', $ticket->post_author );
			return null;
		}

		/**
		 * Get client name
		 * NOTE: Client name is ambigous, use nicename for now
		*/
		private function get_agent_name() {
			if ( empty( $this->ticket_id ) ) {
				return new WP_Error( 'ticket_id_is_missing', __( 'Error: Ticket ID is needed.', 'as-rules-engine' ) );
			}

			/**
			 * Agent name is inaccurate for if else statement
			 * use agent ID instead
			*/
			$agent_id = get_post_meta( $this->ticket_id, '_wpas_assignee', true );
			return $agent_id;
		}

		/**
		 * Get ticket time submitted
		*/
		private function get_ticket_time() {
			if ( empty( $this->ticket_id ) ) {
				return new WP_Error( 'ticket_id_is_missing', __( 'Error: Ticket ID is needed.', 'as-rules-engine' ) );
			}
			$ticket = get_post( $this->ticket_id );
			return $ticket->post_date;
		}

		/**
		 * Get reply content
		*/
		private function get_reply_content() {
			if ( empty( $this->ticket_id ) ) {
				return new WP_Error( 'ticket_id_is_missing', __( 'Error: Ticket ID is needed.', 'as-rules-engine' ) );
			}
			$content = array();
			if ( isset( $this->reply_id ) && ! empty( $this->reply_id ) ) {
				$recent_reply = get_post( $this->reply_id );
				if ( isset( $recent_reply->post_content ) ) {
					$content[] = $recent_reply->post_content;
				}
			}

			return $content;
		}

		/**
		 * Get client attributes value
		*/
		private function get_clients_attr( $attribute ) {
			global $wpdb;
			switch ( $attribute ) {
				case 'email':
					$user_info = get_userdata( $this->get_client_ID() );
					return $user_info->user_email;
				break;
				case 'user_name':
					$user_info = get_userdata( $this->get_client_ID() );
					return $user_info->user_login;
				break;
				default:
					return get_user_meta( $this->get_client_ID(), $attribute, true );
				break;
			}
		}

		/**
		 * Function to check if a given ruleset id's rules match any existing tickets.
		 *
		 * @param int $ruleset_id ruleset ID.
		 *
		 * @return array $matching_tickets Ticket post objects which matched the ruleset's conditions
		 */
		public function tickets_matching_ruleset( $ruleset_id ) {
		  //If ruleset ID doesn't match an existing ruleset then return false
		  $ruleset = get_post( $ruleset_id );

		  if( $ruleset != null ) {
		    if( $ruleset->post_type != 'ruleset' ) {
		      return array();
		    }
		  } else {
		    return array();
		  }

		  //To determine which tickets to take (instead of all of them which gives slow performance)
		  //check on the ruleset's "State" to see which kind of tickets should be queried.
		  $ruleset_closed_only = false;
		  $ruleset_open_only = false;
		  $ruleset_both = false;
		  $ruleset_no_tickets = false;

		  $state_condition = get_post_meta( $ruleset_id, "condition_state" );

		  if(isset( $state_condition[0]) ) {
		    switch( $state_condition[0]['operator'] ) {
		      case 'and':
		      case 'or':
		        if( $state_condition[0]['value'] == 'close' ) {
		          $ruleset_closed_only = true;
		        } elseif( $state_condition[0]['value'] == 'open' ) {
		          $ruleset_open_only = true;
		        } elseif( $state_condition[0]['value'] == 'both' ) {
		          $ruleset_both = true;
		        }
		        break;
		      case 'not':
		        if( $state_condition[0]['operator'] == 'not' ) {
		          if( $state_condition[0]['value'] == 'close' ) {
		            $ruleset_open_only = true;
		          } elseif( $state_condition[0]['value'] == 'open' ) {
		            $ruleset_closed_only = true;
		          } elseif( $state_condition[0]['value'] == 'both' ) {
		            $ruleset_no_tickets = true;
		          }
		        }
		        break;
		      case 'default':
		        $ruleset_open_only = true;
		        break;
		    }

		    //If "State" condition is set to "not" and "both" it means no tickets so end function
		    if( $ruleset_no_tickets == true ) {
		      return array();
		    }
		  }

		  global $wpdb;
		  $db_query = "SELECT posts.ID FROM " . $wpdb->prefix . "posts posts INNER JOIN " . $wpdb->prefix . "postmeta postmeta ON posts.ID = postmeta.post_id WHERE postmeta.meta_key = '_wpas_status' AND ";

		   if( $ruleset_closed_only == true ) {
		     $db_query .= "postmeta.meta_value = 'closed'";
		   } elseif( $ruleset_open_only == true ) {
		     $db_query .= "postmeta.meta_value = 'open'";
		   } elseif( $ruleset_both == true ) {
		     $db_query .= "postmeta.meta_value = 'open' OR postmeta.meta_value = 'closed'";
		   }

		   $db_result = $wpdb->get_results( $db_query, ARRAY_A );

		   //Get the ids in a easier format that what get_results() gives
		   $ticket_ids = array();

		   foreach( $db_result as $result ) {
		     $ticket_ids[] = $result['ID'];
		   }

		  //Loop through each ticket and use this classes 'check' function to see if the conditions are met
		  $matching_tickets = array();

		  foreach( $ticket_ids as $ticket_id ) {
		    /*
		    * If "Status" condition is in this ruleset we need to do similar code to the "save_trigger_status_changed"
		    * function of the Implementation class otherwise some tickets will falsely not pass the test. For example
		    * setting a custom status and calling this function directly will not update the "previous_ticket_status"
		    * post meta value which the "Status" condition check of this classes uses to determine if the condition passes.
		    */
		    $status_condition = get_post_meta( $ruleset_id, "condition_status" );

		    if(isset($status_condition[0])) {
		      if($status_condition[0]['operator'] != 'default') {
		        $status = get_post_status( $ticket_id );
		        update_post_meta( $ticket_id, 'previous_ticket_status', $status );
		      }
		    }

		    //Run the condition checks
		    $statement = $this->check( $ruleset_id, $ticket_id, "" );

		    if( !empty( $statement ) ) {
		      $matching_tickets[] = get_post( $ticket_id );
		    }
		  }

		  return $matching_tickets;
		}
	}
}
