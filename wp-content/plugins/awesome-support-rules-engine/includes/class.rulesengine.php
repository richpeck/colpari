<?php
namespace AsRulesEngine;
/**
 * File contain all required and most used functions in plugin.
 * This file basically include all plugin related files.
 *
 * @package   Awesome Support Rules Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Condition_Field' ) ) {
	// add condition related fields
	require_once( AS_RE_PATH . 'includes/class.condition_field.php' );
}

if ( ! class_exists( 'RE_Implementation' ) ) {
	// Add functionality implementation related work here.
	require_once( AS_RE_PATH . 'includes/class.implementation.php' );
}

/**
 * Class contain core functions, Ajax call, CTP initializations, Rules Metabox addup for trigger, conditions, actions.
 *
 * @since  0.1.0
 */
if ( ! class_exists( 'Rules_Engine' ) ) {
	class Rules_Engine {
		/**
		 * Rules Trigger array
		 * @var array
		 */
		private $triggers;
		/**
		 * Rules Cron Inteval Trigger array
		 * @var array
		 */
		private $cron_inteval_triggers;
		/**
		 * Rules Condition array
		 * @var array
		 */
		public $conditions;
		/**
		 * Rules Action array
		 * @var array
		 */
		private $actions;
		/**
		 * Rules Operator array
		 * @var array
		 */
		private $operators;
		/**
		 * Rules Regexes fields array
		 * @var array
		 */
		private $regexes;
		/**
		 * Rules RE_Implementation class object.
		 * @var object
		 */
		public $implementation;

		/**
		 * Rules_Engine Constructor.
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'add_ruleset_cpt' ) );
			add_action( 'init', array( $this, 'setGlobal' ) );
			add_action( 'add_meta_boxes_' . AS_RE_RULESET_CPT, array( $this, 'set_variables' ) );
			add_action( 'save_post_' . AS_RE_RULESET_CPT, array( $this, 'set_variables' ), 90 );
			add_action( 'save_post_' . AS_RE_RULESET_CPT, array( $this, 'save_ruleset' ), 95, 2 );
			add_filter( 'post_row_actions', array( $this, 'rs_action_row' ), 10, 2 );
			add_action( 'wp_ajax_rs_duplicate',  array( $this, 'rs_action_row_process' ) );
			add_action( 'wp_ajax_get_as_agents_email',  array( $this, 'get_as_agents_email' ) );
			add_action( 'wp_ajax_get_client_attr_value',  array( $this, 'get_client_attr_value' ) );
			add_action( 'wp_ajax_get_agent_attr_value',  array( $this, 'get_agent_attr_value' ) );
			add_action( 'wp_ajax_remove_client_attr_extra',  array( $this, 'remove_client_attr_extra' ) );
			add_action( 'wp_ajax_remove_custom_field_extra',  array( $this, 'remove_custom_field_extra' ) );
			add_action( 'wp_ajax_get_client_names',  array( $this, 'get_client_names' ) );
			add_action( 'wp_ajax_get_client_email_list',  array( $this, 'get_client_email_list' ) );
			add_action( 'wp_ajax_get_agent_names',  array( $this, 'get_agent_names' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			$this->setObject();

		}

		/**
		 * Create object to Implementation class.
		 */
		private function setObject() {
			if ( ! $this->implementation ) {
				require_once( AS_RE_PATH . 'includes/class.implementation.php' );
				$this->implementation = new RE_Implementation( array( 'conditions' => $this->get_conditions( true ) ) );
			}
		}

		/**
		 * Add duplicate button on rows action
		*/
		public function rs_action_row( $actions, $post ) {
			if ( $post->post_type == AS_RE_RULESET_CPT ) {
				$actions['duplicate_ruleset'] = sprintf( '<a href="%s" class="%s" %s>%s</a>',
					'#',
					'duplicate-ruleset',
					'data-postid="' . $post->ID . '"',
				__( 'Duplicate', 'as-rules-engine' ) );
			}
			return $actions;
		}

		/**
		 * Ajax callback for duplicating the ruleset
		*/
		public function rs_action_row_process() {
			global $wpdb;
			$original_id = $_REQUEST['original_id'];

			if ( $_REQUEST['action'] == 'rs_duplicate' && ! empty( $original_id ) ) {
				$post = get_post( $original_id, 'ARRAY_A' );
				if ( $post['post_type'] == AS_RE_RULESET_CPT ) {
					$copied_post = array();
					foreach ( $post as $key => $value ) {
						$copied_post[ $key ] = $value;
					}

					/**
					 * Unset ID on copied_post
					*/
					unset( $copied_post['ID'] );

					/**
					 * Add 'Copy of' on post_title
					*/
					$copied_post['post_title'] = __( 'Copy of ', 'as-rules-engine' ) . $copied_post['post_title'];

					/**
					 * Insert new copied ruleset
					*/
					$copied_post_id = wp_insert_post( $copied_post );

					$taxonomies = get_object_taxonomies( $post['post_type'] );
					foreach ( $taxonomies as $taxonomy ) {
						$terms = wp_get_post_terms( $original_id, $taxonomy, array( 'fields' => 'names' ) );
						wp_set_object_terms( $copied_post_id, $terms, $taxonomy );
					}

					/**
					 * Copy all meta data
					*/
					$custom_fields = get_post_custom( $original_id );
					foreach ( $custom_fields as $key => $value ) {
					    if ( is_array( $value ) && count( $value ) > 0 ) {
							foreach ( $value as $i => $v ) {
								$result = $wpdb->insert( $wpdb->prefix . 'postmeta', array(
									'post_id' => $copied_post_id,
									'meta_key' => $key,
									'meta_value' => $v,
								));
							}
						}
				    }
					echo $copied_post_id;
				}
			}
			wp_die();
		}

		/**
		 * Include admin script for duplicate ruleset
		 * through Ajax
		*/
		public function admin_scripts( $hook ) {
			$current_screen = get_current_screen();
			if ( ( $hook == 'edit.php' && $current_screen->id == 'edit-ruleset' ) || $current_screen->id == 'ruleset' ) {
				wp_enqueue_style( 'asps', AS_RE_URL . 'assets/css/rules-engine.css' );
				wp_enqueue_script( 'asps', AS_RE_URL . 'assets/script/rules-engine.js', array( 'jquery', 'jquery-ui-tabs' ) );
				wp_localize_script( 'asps', 'AS_Rules_Engine', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
			}

			if ( $current_screen->id == 'ruleset' ) {
				wp_enqueue_script( 'asre', AS_RE_URL . 'assets/script/rules-engine-auto-complete.js', array( 'jquery' ), '1.5' );
				wp_localize_script( 'asre',
					'AS_Rules_Engine',
					array(
						'ajax_url' => admin_url( 'admin-ajax.php' ),
						'client_attrs' => $this->get_client_attr_extra(),
						'agent_attrs' => $this->get_agent_attr_extra(),
						'agent_attrs_default' => $this->get_agent_attr_extra_default(),
						'client_attrs_default' => $this->get_client_attr_extra_default(),
						'custom_fields' => $this->get_custom_fields_extra(),
						'custom_field_default' => $this->get_custom_field_extra_default(),
					)
				);
			}

			if ( ($hook == 'post.php' || $hook == 'post-new.php') && $current_screen->id == 'ruleset' ) {

				wp_enqueue_style( 'select2', AS_RE_URL . 'assets/css/select2.min.css' );
				wp_enqueue_script( 'select2', AS_RE_URL . 'assets/script/select2.min.js', array( 'jquery' ), AS_RE_VERSION );
				//dissabled select2-tabfix since not in use at the moment. its afftecting asre-select2-script
				//wp_enqueue_script( 'select2-tabfix', AS_RE_URL . 'assets/script/select2-tab-fix.min.js', array('jquery','select2'), AS_RE_VERSION);
				if ( wp_script_is( 'select2', 'enqueued' ) ) {
					wp_enqueue_script( 'asre-select2-script', AS_RE_URL . 'assets/script/chosen-script.js', array( 'jquery', 'select2' ), AS_RE_VERSION );
				}
			}

		}

		/**
		 * Make rules conditions globally available on init.
		 */
		public function setGlobal() {
			global $conditions;
			$conditions = $this->get_conditions( true );
		}

		/**
		 * Set the variables
		 */
		public function set_variables() {
			$this->operators = $this->get_operators();
			$this->regexes = $this->get_regexes();
			$this->triggers = $this->get_triggers();
			$this->conditions = $this->get_conditions();
			$this->actions = $this->get_actions();
		}

		/**
		 *
		 * Register the CPT for ruleset
		 *
		 */
		public function add_ruleset_cpt() {

			$labels = array(
				'name'                => __( 'AS Ruleset', 'as-rules-engine' ),
				'singular_name'       => __( 'AS Ruleset', 'as-rules-engine' ),
				'add_new'             => _x( 'Add New Ruleset', 'as-rules-engine', 'as-rules-engine' ),
				'add_new_item'        => __( 'Add New Ruleset', 'as-rules-engine' ),
				'edit_item'           => __( 'Edit Ruleset', 'as-rules-engine' ),
				'new_item'            => __( 'New Ruleset', 'as-rules-engine' ),
				'view_item'           => __( 'View Ruleset', 'as-rules-engine' ),
				'search_items'        => __( 'Search Rulesets', 'as-rules-engine' ),
				'not_found'           => __( 'No Rulesets found', 'as-rules-engine' ),
				'not_found_in_trash'  => __( 'No Rulesets found in Trash', 'as-rules-engine' ),
				'parent_item_colon'   => __( 'Parent Ruleset:', 'as-rules-engine' ),
				'menu_name'           => __( 'AS Ruleset', 'as-rules-engine' ),
			);
			/* Post type capabilities */
			$cap = apply_filters( 'wpas_ticket_type_cap', array(
					'read'					 => 'view_ticket',
					'read_post'				 => 'view_ticket',
					'read_private_posts' 	 => 'view_private_ticket',
					'edit_post'				 => 'edit_ticket',
					'edit_posts'			 => 'edit_ticket',
					'edit_others_posts' 	 => 'edit_other_ticket',
					'edit_private_posts' 	 => 'edit_private_ticket',
					'edit_published_posts' 	 => 'edit_ticket',
					'publish_posts'			 => 'create_ticket',
					'delete_post'			 => 'delete_ticket',
					'delete_posts'			 => 'delete_ticket',
					'delete_private_posts' 	 => 'delete_private_ticket',
					'delete_published_posts' => 'delete_ticket',
					'delete_others_posts' 	 => 'delete_other_ticket'
			) );
			$args = array(
				'labels'                   => $labels,
				'hierarchical'        => false,
				'description'         => 'description',
				'taxonomies'          => array(),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => null,
				'menu_icon'           => null,
				'show_in_nav_menus'   => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'has_archive'         => false,
				'query_var'           => true,
				'can_export'          => true,
				'rewrite'             => false,
				'capability_type'     => 'view_ticket',
				'capabilities'        => $cap,
				'supports'            => array(
					'title'
				),
				'register_meta_box_cb' => array( $this, 'add_metaboxes' ),
			);
			register_post_type( AS_RE_RULESET_CPT, $args );
		}


		/**
		 *
		 * Update the post meta for a ruleset
		 *
		 * @param  int $post_id Rules post id.
		 * @param  array $post Rules post array.
		 */
		public function save_ruleset( $post_id, $post ) {

			$cfmetatag = 'condition_custom_field';
			$metatag = 'condition_client_attrs';
		            $custom_fields = array();
			$client_attrs = array();

		    // adding condition client attribute extra values
			if ( isset( $_POST[ $metatag . '_fields_extra' ] ) ) {
				$client_attrs['fields'] = $_POST[ $metatag . '_fields_extra' ];
			}
			if ( isset( $_POST[ $metatag . '_value_extra' ] ) ) {
				$client_attrs['values'] = $_POST[ $metatag . '_value_extra' ];
			}
			if ( isset( $_POST[ $metatag . '_value_operator_extra' ] ) ) {
				$client_attrs['operator'] = $_POST[ $metatag . '_value_operator_extra' ];
			}
			if ( isset( $_POST[ $metatag . '_value_operator_extra' ] ) ) {
				$client_attrs['operator'] = $_POST[ $metatag . '_value_operator_extra' ];
			}
			if ( isset( $_POST[ $metatag . '_value_regex_extra' ] ) ) {
				$client_attrs['regex'] = $_POST[ $metatag . '_value_regex_extra' ];
			}
			if ( ! empty( $client_attrs ) ) {
				update_post_meta( $post_id, $metatag, $client_attrs );
			}

		    // adding condition custom fields extra values
			if ( isset( $_POST[ $metatag . '_fields_extra' ] ) && isset( $_POST[ $cfmetatag . '_extra' ] ) ) {
				$custom_fields['fields'] = $_POST[ $cfmetatag . '_extra' ];
			}
			if ( isset( $_POST[ $metatag . '_value_extra' ] ) && isset( $_POST[ $cfmetatag . '_value_extra' ] ) ) {
				$custom_fields['values'] = $_POST[ $cfmetatag . '_value_extra' ];
			}
			if ( isset( $_POST[ $metatag . '_value_operator_extra' ] ) && isset( $_POST[ $cfmetatag . '_value_operator_extra' ] ) ) {
				$custom_fields['operator'] = $_POST[ $cfmetatag . '_value_operator_extra' ];
			}
			if ( isset( $_POST[ $metatag . '_value_regex_extra' ] ) && isset( $_POST[ $cfmetatag . '_value_regex_extra' ] ) ) {
				$custom_fields['regex'] = $_POST[ $cfmetatag . '_value_regex_extra' ];
			}
			if ( ! empty( $custom_fields ) ) {
				update_post_meta( $post_id, $cfmetatag, $custom_fields );
			}

			// save triggers.
			if ( isset( $this->triggers ) && ! empty( $this->triggers ) ) {
				$triggers_slugs = array_keys( $this->triggers );

				//$this->triggers only has the check box ones, so now we will add the radio button ones set from the metabox view
				$triggers_slugs['cron_intervals'] = array(
					'every1min',
					'every5min',
					'every10min',
					'every20min',
					'every30min',
					'hourly',
					'every2ndhour',
					'every4thhour',
					'every6thhour',
					'twicedaily',
					'daily'
				);

				$this->save_metabox_data( $triggers_slugs, $post_id );
			}

			//update meta for actions.
			if ( isset( $this->actions ) && ! empty( $this->actions ) ) {
				foreach ( $this->actions as $slug => $action ) {
					if ( isset( $_POST['ticket_data'] ) && ! empty( $_POST['ticket_data'] ) ) {
						$ticket_data = $_POST['ticket_data'];
						update_post_meta( $post_id, 'zapier_notification_data', $ticket_data );
					}
					// Update Hooks data.
					if ( isset( $_POST['hooks_data'] ) && ! empty( $_POST['hooks_data'] ) ) {
						$hooks_data = $_POST['hooks_data'];
						update_post_meta( $post_id, 'hooks_notification_data', $hooks_data );
					}
					$action->save();
				}
			}

			//update meta for conditions.
			foreach ( $this->conditions as $slug => $condition ) {
				$condition->save();
			}
		}

		/**
		 *
		 * Update data from meta box to post meta
		 *
		 * @param array $slugs meta keys array.
		 * @param int $post_id Rules ID.
		 */
		private function save_metabox_data( $slugs, $post_id ) {
			$operators_slugs = array_keys( $this->operators );

			wp_parse_args( $slugs, $_POST );
			/**
			 * Checkbox saving for Triggers
			*/
			foreach ( $slugs as $key => $slug ) {
				if($key !== 'cron_intervals') {
					$cb_slug = $slug . '-cb';
					if ( ! isset( $_POST['post_view'] ) ) {
						if ( array_key_exists( $cb_slug, $_POST ) ) {
							update_post_meta( $post_id, $cb_slug, $_POST[ $cb_slug ] );
						} else {
							delete_post_meta( $post_id, $cb_slug );
						}
					}
				} elseif($key == 'cron_intervals') {
					if(gettype($slugs[$key]) == 'array') {
						foreach( $slugs[$key] as $key => $val ) {
							if(isset($_POST[AS_RE_TRIGGER_META_PREFIX . 'cron_intervals-rb'])) {
								update_post_meta( $post_id, AS_RE_TRIGGER_META_PREFIX . 'cron_intervals-rb', $_POST[AS_RE_TRIGGER_META_PREFIX . 'cron_intervals-rb'] );
							}
						}
					}
				}
			}

			/**
			 * Operator saving
			*/
			foreach ( $slugs as $key => $slug ) {
				if($key !== 'cron_intervals') {
					if ( array_key_exists( $slug, $_POST ) ) {
						$operator = $_POST[ $slug ];
						if ( empty( $operator ) || ! in_array( $operator, $operators_slugs ) ) {
							$operator = AS_RE_DEFAULT_OPERATOR;
						}
						update_post_meta( $post_id, $slug, $operator );
					}
				}
			}
		}

		/**
		 *
		 * Add meta boxes for triggers, conditions and actions
		 *
		 */
		public function add_metaboxes() {
			// Add meta box for triggers
			add_meta_box(
				'metabox-triggers',
				__( 'Triggers', 'as-rules-engine' ),
				array( $this, 'add_triggers_metabox' ),
				'ruleset',
				'normal',
				'high'
			);

			// Add meta box for conditions and filters
			add_meta_box(
				'metabox-conditions',
				__( 'Conditions & Filters', 'as-rules-engine' ),
				array( $this, 'add_conditions_metabox' ),
				'ruleset',
				'normal',
				'high'
			);

			// Add meta box for actions
			add_meta_box(
				'metabox-actions',
				__( 'Actions', 'as-rules-engine' ),
				array( $this, 'add_actions_metabox' ),
				'ruleset',
				'normal',
				'high'
			);
		}

		/**
		 * Add trigger metabox content.
		 *
		 * @param object $post Rules post id.
		 */
		public function add_triggers_metabox( $post ) {
			$post_custom = get_post_custom( $post->ID );
			$args = array(
				'triggers' => $this->triggers,
				'operators' => $this->operators,
				'post_custom' => $post_custom,
			);
			$this->load_view( 'triggers-metabox', $args );
		}

		/**
		 * Add condition metabox content.
		 */
		public function add_conditions_metabox() {
			$args = array(
				'conditions' => $this->conditions,
			);
			$this->load_view( 'conditions-metabox', $args );
		}

		/**
		 * Add action metabox content
		 */
		public function add_actions_metabox() {

			$args = array(
				'actions' => $this->actions,
				'multiple_agents' => $this->get_as_option( 'multiple_agents_per_ticket' ),
				'third_party' => $this->get_as_option( 'show_third_party_fields' ),
				'templates' => $this->as_get_post_title(),
			);

			$this->load_view( 'actions-metabox', $args );
		}

		/**
		 * get all As related fields setting options.
		 *
		 * @param string $field_key option field key.
		 */
		public function get_as_option( $field_key ) {
			$as_options = maybe_unserialize( get_option( 'wpas_options' ) );

			if ( ! $as_options ) {
				return array();
			}
			if ( isset( $as_options[ $field_key ] ) ) {
				return $as_options[ $field_key ];
			} else {
				return array();
			}
		}

		/**
		 * Function to get current user role.
		 */
		public function wpas_rules_get_current_user_role() {
			$user = wp_get_current_user();
			if ( ! empty( $user->roles[0] ) ) {
				$current_user_role = $user->roles[0];
			} else {
				return false;
			}
			return $current_user_role;
		}

		/**
		 * Get trigger array.
		 */
		public function get_triggers() {

			$prefix = AS_RE_TRIGGER_META_PREFIX;

			$current_user_role = $this->wpas_rules_get_current_user_role();

			$rules_meta_fields = array(
				$prefix . 'new_ticket' => __( 'New ticket', 'as-rules-engine' ),
				$prefix . 'ticket_reply_received' => __( 'Client replied to ticket', 'as-rules-engine' ),
				$prefix . 'agent_replied_ticket' => __( 'Agent replied to ticket', 'as-rules-engine' ),
				$prefix . 'status_changed' => __( 'Status changed', 'as-rules-engine' ),
				$prefix . 'ticket_closed' => __( 'Ticket closed', 'as-rules-engine' ),
				$prefix . 'ticket_updated' => __( 'Ticket updated', 'as-rules-engine' ),
				$prefix . 'ticket_trashed' => __( 'Ticket trashed', 'as-rules-engine' ),
				$prefix . 'cron' => __( 'Cron', 'as-rules-engine' ),
			);

			if ( !current_user_can( 'administrator' ) && !current_user_can( 'administer_awesome_support' ) ) {
				foreach ( $rules_meta_fields as $key => $field ) {
					$rule_approved_roles = (array) $this->get_as_option( $key . '_role' );
					if ( count( $rule_approved_roles ) > 0 ) {
						if ( ! in_array( $current_user_role, $rule_approved_roles ) ) {
							unset( $rules_meta_fields[ $key ] );
						}
					} else {
						unset( $rules_meta_fields[ $key ] );
					}
				}
			}

			return $rules_meta_fields;
		}

		/**
		 * Get ticket related tags terms.
		 * @return array tags array.
		 */
		private function get_ticket_tags() {
			$tags = array();
			if ( taxonomy_exists( 'ticket-tag' ) ) {
				$args = array(
					'taxonomy' => 'ticket-tag',
					'hide_empty' => false,
					'fields' => 'id=>name',
				);
				$tags = get_terms( $args );
			}
			return $tags;
		}

		/**
		 * Get custom fields for ticket form
		 * Filter the custom fields by 'hide_front_end'
		 * 'backend_only' and 'core' arguments
		*/
		private function ticket_custom_fields() {
			$fields = WPAS()->custom_fields->get_custom_fields();
			$custom_fields = array();
			if ( ! empty( $fields ) ) {
				foreach ( $fields as $name => $field ) {
					if ( isset( $field['args']['core'] ) && $field['args']['core'] === false ) {
						$custom_fields[ $name ] = $field['args']['title'] . ' (' . __( 'Type: ', 'as-rules-engine' ) . $field['args']['field_type'] . ')';
					}
				}
			}
			return $custom_fields;
		}
		/**
		 * Add field type.
		 */
		private function ticket_custom_fields_type() {
			$fields = WPAS()->custom_fields->get_custom_fields();
			$custom_fields_type = array();
			if ( ! empty( $fields ) ) {
				foreach ( $fields as $name => $field ) {
					if ( isset( $field['args']['core'] ) && $field['args']['core'] === false ) {
						$custom_fields_type[ $name ] = $field['args']['field_type'];
					}
				}
			}
			return $custom_fields_type;
		}

		/**
		*
		* Fetch capabilities for a role
		*
		* @param $role_slug  - the slug of the role to fetch capabilities
		* @return array[role_slug] => role_slug
		*/
		private function get_role_caps( $role_slug ) {
			$role = get_role( $role_slug );
			$capabilities = $role->capabilities;
			if ( ! empty( $capabilities ) ) {
				foreach ( $capabilities as $capability => $value ) {
					$capabilities[ $capability ] = $capability;
				}
			}
			return $capabilities;
		}

		/**
		*
		* Return an array of all condition and filters fields objects
		*
		* @return array
		*/
		public function get_conditions( $just_array = false ) {
			$conditions_array = $this->get_conditions_array( $just_array );
			$conditions = array();
			foreach ( $conditions_array as $slug => $condition ) {

				$condition['slug'] = $slug;
				$conditions[ $slug ] = new Condition_Field( $condition );
			}
			return $conditions;
		}

		/**
		*
		* Return an array of all conditions and filters
		*
		* @return array
		*/
		private function get_conditions_array( $just_array = false ) {
			global $wp_roles;

			$prefix = AS_RE_CONDITIONS_META_PREFIX;
			$current_user_role = $this->wpas_rules_get_current_user_role();
			$regex_array = $this->regexes;
			if( empty( $regex_array )){
				$regex_array = array();
			}
			$rules_meta_fields = array(
				$prefix . 'tags_ticket' => array(
					'name' => __( 'Tags on ticket', 'as-rules-engine' ),
					'type' => 'select',
					'multiple' => true,
					'condition_operators' => $this->operators,
					'condition_extra_operators' => array(),
					'options' =>  array( 'default' => __( 'Select Tags', 'as-rules-engine' ) ) + $this->get_ticket_tags(),
				),
				$prefix . 'age_ticket' => array(
					'name' => __( 'Age of ticket(In days)', 'as-rules-engine' ),
					'type' => 'number',
					'min' => 0,
					'condition_operators' => $this->operators,
					'condition_extra_operators' => array( '=' => '=', '>' => '>', '<' => '<' ),
				),
				$prefix . 'age_last_customer_reply' => array(
					'name' => __( 'Age from last customer reply(In days)', 'as-rules-engine' ),
					'type' => 'number',
					'min' => 0,
					'condition_operators' => $this->operators,
					'condition_extra_operators' => array( '=' => '=', '>' => '>', '<' => '<' ),
				),
				$prefix . 'age_last_agent_reply' => array(
					'name' => __( 'Age from last agent reply(In days)', 'as-rules-engine' ),
					'type' => 'number',
					'min' => 0,
					'condition_operators' => $this->operators,
					'condition_extra_operators' => array( '=' => '=', '>' => '>', '<' => '<' ),
				),
				$prefix . 'status' => array(
					'name' => __( 'Status', 'as-rules-engine' ),
					'type' => 'select',
					'multiple' => true,
					'options' =>  array( 'default' => __( 'Select Status', 'as-rules-engine' ) ) + wpas_get_post_status(),
					'condition_operators' => $this->operators,
					'condition_extra_operators' => array(),
				),
				$prefix . 'state' => array(
					'name' => __( 'State', 'as-rules-engine' ),
					'type' => 'select',
					'multiple' => false,
					'options' => array( 'open' => 'Open', 'close' => 'Close', 'both' => 'Both' ),
					'condition_operators' => $this->operators,
					'condition_extra_operators' => array(),
				),
				$prefix . 'subject' => array(
					'name' => __( 'Subject', 'as-rules-engine' ),
					'condition_operators' => $this->operators,
					'condition_regex_operators' => $this->regexes,
				),
				$prefix . 'contents' => array(
					'name' => __( 'Ticket Contents', 'as-rules-engine' ),
					'condition_operators' => $this->operators,
					'condition_regex_operators' => $this->regexes,
				),
				$prefix . 'client_name' => array(
					'name' => __( 'Client Name', 'as-rules-engine' ),
					'type' => 'select',
					'multiple' => true,
					'options' => $this->get_client_names(),
					'condition_operators' => $this->operators,
					'condition_extra_operators' => array(),
				),
				$prefix . 'client_email' => array(
					'name' => __( 'Client email address', 'as-rules-engine' ),
					'type' => 'select',
					'multiple' => true,
					'options' => $this->get_client_email_list(),
					'condition_operators' => $this->operators,
					'condition_extra_operators' => array(),
				),
				$prefix . 'client_attrs' => array(
					'name' => __( 'Client Attributes', 'as-rules-engine' ),
					'type' => 'select',
					'options' => array( 'default' => __( 'Please select', 'as-rules-engine' ) ) + $this->get_as_user_attr( 'client_attr' ),
					'condition_operators' => $this->operators,
					'condition_regex_operators' => $this->regexes,
					'extra_value_field' => array( array( 'name' => 'condition_client_attrs_value', 'type' => 'input' ), array( 'name' => 'add_extra_client_attr', 'type' => 'button' ) ),
				),
				$prefix . 'client_caps_fields' => array(
					'name' => __( 'Client Capabilities', 'as-rules-engine' ),
					'type' => 'select',
					'multiple' => true,
					'options' => array( 'default' => __( 'Please select', 'as-rules-engine' ) ) + $this->get_all_wp_caps(),
					'condition_operators' => $this->operators,
					'condition_extra_operators' => array(),
				),
				$prefix . 'agent_name' => array(
					'name' => __( 'Agent Name', 'as-rules-engine' ),
					'type' => 'select',
					'multiple' => true,
					'options' => $this->get_agent_names(),
					'condition_operators' => $this->operators,
					'condition_extra_operators' => array(),
				),
				$prefix . 'agent_attrs' => array(
					'name' => __( 'Agent Attributes', 'as-rules-engine' ),
					'type' => 'select',
					'options' => array( 'default' => __( 'Please select', 'as-rules-engine' ) ) + $this->get_as_user_attr( 'agent_attr' ),
					'condition_operators' => $this->operators,
					'condition_regex_operators' => $this->regexes,
					'extra_value_field' => array( array( 'name' => 'condition_agent_attrs_value', 'type' => 'input' ), array( 'name' => 'add_extra_agent_attr', 'type' => 'button' ) ),
				),
				$prefix . 'agent_attrs_caps' => array(
					'name' => __( 'Agent Capabilities', 'as-rules-engine' ),
					'type' => 'select',
					'multiple' => true,
					'options' =>  array( 'default' => __( 'Please select', 'as-rules-engine' ) ) + $this->get_role_caps( 'wpas_agent' ),
					'condition_operators' => $this->operators,
					'condition_extra_operators' => array(),
				),
				$prefix . 'custom_field' => array(
					'name' => __( 'Custom field', 'as-rules-engine' ),
					'type' => 'select',
					'options' => array( 'default' => __( 'Please select', 'as-rules-engine' ) ) + $this->ticket_custom_fields(),
					'options_field_type' => $this->ticket_custom_fields_type(),
					'condition_operators' => $this->operators,
					'condition_regex_operators' => array_merge( $regex_array , array( '>' => __( 'Greater than(>)' , 'as-rules-engine' ), '<' =>  __( 'Less than(<)', 'as-rules-engine' ) ) ),
					'extra_value_field' => array( array( 'name' => 'condition_custom_field_value', 'type' => 'input' ), array( 'name' => 'add_extra_custom_field', 'type' => 'button' ) ),
				),
				$prefix . 'reply_contents' => array(
					'name' => __( 'Reply contents', 'as-rules-engine' ),
					'condition_operators' => $this->operators,
					'condition_regex_operators' => $this->regexes,

				),
	 			$prefix . 'time' => array(
	 				'name' => __( 'Date', 'as-rules-engine' ),
	 				'type' => 'date',
	 				'condition_operators' => $this->operators,
	 				'condition_extra_operators' => array( '=' => '=', '>' => '>', '<' => '<' ),
	 			),
			);

			if ( method_exists( $wp_roles, 'get_names' ) ) {
				$agent_names = array(
					$prefix . 'agent_wp_role' => array(
						'name' => __( 'Agent WP Role', 'as-rules-engine' ),
						'type' => 'select',
						'multiple' => true,
						'options' => $wp_roles->get_names(),
					),
				);
				//array_splice( $rules_meta_fields, 13, 0, $agent_names );
			}

			/**
			 * If we need the whole array, it will be filtered by default
			 * Add option where we can pull the array list by default for
			 * Implementation class
			*/
			if ( $just_array ) {
				return $rules_meta_fields;
			}

			// hide the ticket tag rules item if there are no ticket tags found

			if ( count( $this->get_ticket_tags() ) == 0 ) {
				unset( $rules_meta_fields[ $prefix . 'tags_ticket' ] );
			}

			if ( !current_user_can( 'administrator' ) && !current_user_can( 'administer_awesome_support' ) ) {

				foreach ( $rules_meta_fields as $key => $field ) {
					$rule_approved_roles = (array) $this->get_as_option( $key . '_role' );

					if ( count( $rule_approved_roles ) > 0 ) {
						if ( ! in_array( $current_user_role, $rule_approved_roles ) ) {
							unset( $rules_meta_fields[ $key ] );
						}
					} else {
						unset( $rules_meta_fields[ $key ] );
					}
				}
			}
			return $rules_meta_fields;

		}

		/**
		 * Get client names
		 * Search users with nearest term and should be
		 * wpas_user role only
		*/
		private function get_client_names() {
			$users = array();
			if ( isset( $_REQUEST['qn'] ) ) {
				if ( isset( $_REQUEST['qn']['term'] ) ) {
					$args = array(
						'search'      => array(
							'query'    => sanitize_text_field( $_REQUEST['qn']['term'] ),
							'fields'   => array( 'user_nicename', 'display_name' ),
							'relation' => 'OR'
						),
						'cap' => 'create_ticket'
					);
					$wpas_users = wpas_get_users( $args);
					if ( isset( $wpas_users->members ) && ! empty( $wpas_users->members ) ) {
						foreach ( $wpas_users->members as $key => $v ) {
							$author_info = get_userdata( $v->ID );
							$get_info = $author_info->first_name . ' ' . $author_info->last_name;
							if ( ! empty( $author_info->first_name ) && ! empty( $author_info->last_name ) ) {
								$users[ $v->ID ] = $get_info;
							} else {
								$users[ $v->ID ] = $v->data->user_nicename;
							}
						}
					}
					wp_reset_query();

				} else {
					$users = array_slice( $users, 0, 5 );
				}
				$json_user = array();
				foreach ( $users as $k => $v ) {
					$json_user[] = array( 'id' => $k, 'text' => $v );
				}
				wp_send_json( $json_user );
			}
			return $users;
		}

		/**
		 * Get client emails
		 * Search users with nearest term and should be
		 * wpas_user role only
		*/
		private function get_client_email_list() {
			$user_emails = array();
			if ( isset( $_REQUEST['qr'] ) ) {
				if ( isset( $_REQUEST['qr']['term'] ) ) {
					$args = array(
						'search'      => array(
							'query'    => sanitize_text_field( $_REQUEST['qr']['term'] ),
							'fields'   => array( 'user_email' ),
							'relation' => 'OR'
						),
						'cap' => 'create_ticket'
					);
					$wpas_users = wpas_get_users( $args);
					if ( isset( $wpas_users->members ) && ! empty( $wpas_users->members ) ) {
						foreach ( $wpas_users->members as $key => $v ) {
							$user_obj = get_userdata( $v->ID );
							$user_emails[ $v->ID ] = $user_obj->user_email;
						}
					}
					wp_reset_query();
				}
				$json_user_email = array();
				foreach ( $user_emails as $k => $v ) {
					$json_user_email[] = array( 'id' => $k, 'text' => $v );
				}
				wp_send_json( $json_user_email );
			}
			return $user_emails;
		}

		/**
		 * Get agent names
		*/
		private function get_agent_names() {
			$users = array();
			if ( isset( $_REQUEST['qc'] ) ) {
				if ( isset( $_REQUEST['qc']['term'] ) ) {
					$args = array(
						'search'      => array(
							'query'    => sanitize_text_field( $_REQUEST['qc']['term'] ),
							'fields'   => array( 'user_nicename', 'display_name' ),
							'relation' => 'OR'
						),
						'cap' => 'edit_ticket'
					);
					$wpas_users = wpas_get_users( $args);
					if ( isset( $wpas_users->members ) && ! empty( $wpas_users->members ) ) {
						foreach ( $wpas_users->members as $key => $v ) {
							$author_info = get_userdata( $v->ID );
							$get_info = $author_info->first_name . ' ' . $author_info->last_name;
							if ( ! empty( $author_info->first_name ) && ! empty( $author_info->last_name ) ) {
								$users[ $v->ID ] = $get_info;
							} else {
								$users[ $v->ID ] = $v->data->user_nicename;
							}
						}
					}
					wp_reset_query();

				} else {
					$users = array_slice( $users, 0, 5 );
				}
				$json_user = array();
				foreach ( $users as $k => $v ) {
					$json_user[] = array( 'id' => $k, 'text' => $v );
				}
				wp_send_json( $json_user );
			}
			return $users;
		}

		/**
		 *
		 * Return an array of all actions fields objects
		 *
		 * @return array
		 */
		private function get_actions() {
			$actions_array = $this->get_actions_array();
			$actions = array();
			foreach ( $actions_array as $slug => $action ) {
				$action['slug'] = $slug;
				//  $action['operators'] = $this->operators;
				$actions[ $slug ] = new Condition_Field( $action );
			}
			return $actions;
		}


		/**
		*
		* Return an array of all actions
		*
		* @return array
		*/
		private function get_actions_array() {

			$rules_meta_fields = $this->create_initial_actions_array() ;

			$rules_meta_fields = $this->filter_actions_array_by_feature( $rules_meta_fields );

			$rules_meta_fields = $this->filter_actions_array_by_role( $rules_meta_fields );

			return $rules_meta_fields;

		}


		/**
		*
		* Return an array of all actions
		*
		* @return array
		*/
		private function create_initial_actions_array() {

			$prefix = AS_RE_ACTION_META_PREFIX;

			$rules_meta_fields = array(
				$prefix . 'change_status' => array(
					'name' => __( 'Change status', 'as-rules-engine' ),
					'type' => 'select',
					'options' => array_merge( array( 'default' => __( 'Please select', 'as-rules-engine' ) ), wpas_get_post_status() ),
				),

				$prefix . 'close_ticket' => array(
					'name' => __( 'Close Ticket', 'as-rules-engine' ),
					'type' => 'checkbox'
				),

				$prefix . 'change_state' => array(
					'name' => __( 'Change state', 'as-rules-engine' ),
					'type' => 'select',
					'options' => array_merge( array( 'default' => __( 'Please select', 'as-rules-engine' ) ), array( 'open' => 'Open', 'close' => 'Close' ) ),
				),

				$prefix . 'change_priority' => array(
					'name' => __( 'Change priority', 'as-rules-engine' ),
					'type' => 'select',
					'options' => array( 'default' => __( 'Please select', 'as-rules-engine' ) ) + $this->get_as_taxonomy( 'ticket_priority' ),
				),

				$prefix . 'change_dept' => array(
					'name' => __( 'Change department', 'as-rules-engine' ),
					'type' => 'select',
					'options' => array( 'default' => __( 'Please select', 'as-rules-engine' ) ) + $this->get_as_taxonomy( 'department' ),
				),


				$prefix . 'change_channel' => array(
					'name' => __( 'Change channel', 'as-rules-engine' ),
					'type' => 'select',
					'options' => array( 'default' => __( 'Please select', 'as-rules-engine' ) ) + $this->get_as_taxonomy( 'ticket_channel' ),
				),

				$prefix . 'change_agent' => array(
					'name' => __( 'Change agent', 'as-rules-engine' ),
					'type' => 'select',
					'options' => array( 'default' => __( 'Please select', 'as-rules-engine' ) ) + $this->get_as_agents(),
				),

				$prefix . 'change_agent2' => array(
					'name' => __( 'Change secondary agent', 'as-rules-engine' ),
					'type' => 'select',
					'options' => array( 'default' => __( 'Please select', 'as-rules-engine' ) ) + $this->get_as_agents(),
				),

				$prefix . 'change_agent3' => array(
					'name' => __( 'Change tertiary agent', 'as-rules-engine' ),
					'type' => 'select',
					'options' => array( 'default' => __( 'Please select', 'as-rules-engine' ) ) + $this->get_as_agents(),
				),

				$prefix . 'change_first_interested_party_email' => array(
					'name' => __( 'Change first interested party email', 'as-rules-engine' ),
				),

				$prefix . 'change_second_interested_party_email' => array(
					'name' => __( 'Change second interested party email', 'as-rules-engine' ),
				),

				$prefix . 'note_ticket_user' => array(
					'name' => __( 'Select Agent Under Whose Name This Note Will Be Entered', 'as-rules-engine' ),
					'type' => 'select',
					'options' => array( 'default' => __( 'Please select', 'as-rules-engine' ) ) + $this->get_as_agents(),
				),

				$prefix . 'note_ticket' => array(
					'name' => __( 'Enter the note that will be added to the ticket', 'as-rules-engine' ),
					'type' => 'wysiwyg',
				),

				$prefix . 'edit_ticket_user' => array(
					'name' => __( 'Select Agent Under Whose Name This Reply Will Be Added', 'as-rules-engine' ),
					'type' => 'select',
					'options' => array( 'default' => __( 'Please select', 'as-rules-engine' ) ) + $this->get_as_agents(),
				),

				$prefix . 'reply_ticket' => array(
					'name' => __( 'Enter the reply to be added to the ticket' ),
					'type' => 'wysiwyg',
				),
				$prefix . 'send_email' => array(
					'name' => __( 'Enter One or More Email Addresses', 'as-rules-engine' ),
					'extra_value_field' => array( array( 'name' => 'action_send_email_template', 'type' => 'select', 'options' => array( 'default' => __( 'Please select', 'as-rules-engine' ) ) + $this->as_get_post_title() ) ),
				),

				$prefix . 'trash_ticket' => array(
					'name' => __( 'Move Ticket To Trash', 'as-rules-engine' ),
					'type' => 'checkbox',
				),
				$prefix . 'call_webhook' => array( 'name' => __( 'Enter The Endpoint For The Webhook', 'as-rules-engine' ) ),

				$prefix . 'execute_http_action' => array(
					'name' => __( 'Choose POST or GET and Then Select Fields To Be Sent', 'as-rules-engine' ),
					'type' => 'select',
					'default' => 'post',
					'options' => array( 'post' => 'post' , 'get' => 'get' ),
				),

				$prefix . 'zapier_notification' => array( 'name' => __( 'Zap URL', 'as-rules-engine' ) ),

				$prefix . 'assignee' => array(
					'name' => __( 'Assignee (agent)', 'as-rules-engine' ),
					'type' => 'checkbox',
				),

				$prefix . 'customer' => array(
					'name' => __( 'Customer (client)', 'as-rules-engine' ),
					'type' => 'checkbox',
				),

				$prefix . 'secondary_assignee' => array(
					'name' => __( 'Secondary Assignee', 'as-rules-engine' ),
					'type' => 'checkbox',
				),

				$prefix . 'tertiary_assignee' => array(
					'name' => __( 'Tertiary Assignee', 'as-rules-engine' ),
					'type' => 'checkbox',
				),

				$prefix . 'first_interested_party' => array(
					'name' => __( 'Additional Interested Party #1', 'as-rules-engine' ),
					'type' => 'checkbox',
				),

				$prefix . 'second_interested_party' => array(
					'name' => __( 'Additional Interested Party #2', 'as-rules-engine' ),
					'type' => 'checkbox',
				),

				$prefix . 'assignee_template' => array(
					'name' => __( 'Assignee (agent)', 'as-rules-engine' ),
					'type' => 'select',
					'options' => $this->as_get_post_title(),
				),

				$prefix . 'customer_template' => array(
					'name' => __( 'Customer (client)', 'as-rules-engine' ),
					'type' => 'select',
					'options' => $this->as_get_post_title(),
				),

	 			$prefix . 'secondary_assignee_template' => array(
					'name' => __( 'Secondary Assignee', 'as-rules-engine' ),
					'type' => 'select',
					'options' => $this->as_get_post_title(),
				),

				$prefix . 'tertiary_assignee_template' => array(
					'name' => __( 'Tertiary Assignee', 'as-rules-engine' ),
					'type' => 'select',
					'options' => $this->as_get_post_title(),
				),

				$prefix . 'first_interested_party_template' => array(
					'name' => __( 'Additional Interested Party #1', 'as-rules-engine' ),
					'type' => 'select',
					'options' => $this->as_get_post_title(),
				),

				$prefix . 'second_interested_party_template' => array(
					'name' => __( 'Additional Interested Party #2', 'as-rules-engine' ),
					'type' => 'select',
					'options' => $this->as_get_post_title(),
				),
			);

			return $rules_meta_fields;
		}

		/**
		*
		* Return an array of actions with any actions related to inactive features removed.
		* Right now that means looking to see if departments and priorities are active.
		*
		* @param array $rules_meta_fields array of actions.
		* 				This array is usually created by a call to the create_initial_actions_array function.
		*
		* @return array
		*/
		private function filter_actions_array_by_feature( $rules_meta_fields ) {

			$prefix = AS_RE_ACTION_META_PREFIX;

			/* Unset some items from the array if not enabled in the core Awesome Support Options */
			if ( false === boolval( wpas_get_option( 'departments', false ) ) ) {
				/* Departments not enabled so remove it...*/
				unset( $rules_meta_fields[$prefix . 'change_dept']) ;
			}
			if ( false === boolval( wpas_get_option( 'support_priority', false ) ) ) {
				/* priority not enabled so remove it...*/
				unset( $rules_meta_fields[$prefix . 'change_priority']) ;
			}

			return $rules_meta_fields;

		}

		/**
		*
		* Return an array of actions with any actions not allowed by security rules
		* for the current logged in user removed.
		*
		* @param array $rules_meta_fields array of actions.
		* 				This array is usually created by a call to the create_initial_actions_array function.
		*
		* @return array
		*/
		private function filter_actions_array_by_role( $rules_meta_fields ) {

			/* Remove any items from the array where the the users role does not allow them to see it */
			$current_user_role = $this->wpas_rules_get_current_user_role();

			if ( !current_user_can( 'administrator' ) && !current_user_can( 'administer_awesome_support' ) ) {
				foreach ( $rules_meta_fields as $key => $field ) {
					$rule_approved_roles = (array) $this->get_as_option( $key . '_role' );
					if ( count( $rule_approved_roles ) > 0 ) {
						if ( ! in_array( $current_user_role, $rule_approved_roles ) ) {
							unset( $rules_meta_fields[ $key ] );
						}
					} else {
						$send_email_approved_roles = (array) $this->get_as_option( 'action_send_email_role' );
						$exclude_email_param = 'action_assignee,action_customer,action_secondary_assignee,action_tertiary_assignee,action_first_interested_party,action_second_interested_party,action_assignee_template,action_customer_template, action_secondary_assignee_template,action_tertiary_assignee_template,action_first_interested_party_template,action_second_interested_party_template';
						$send_email_param_array = explode(',', $exclude_email_param );
						// when keys to exclude are send email param.
						if ( in_array( $key, $send_email_param_array ) ) {
							// If send email setting not enabled for current user.
							if( !in_array( $current_user_role, $send_email_approved_roles ) ){

								unset( $rules_meta_fields[ $key ] );
							}
						}else{
							unset( $rules_meta_fields[ $key ] );
						}
					}
				}
			}

			return $rules_meta_fields;

		}

		/**
		 * Set user meta attributes.
		 * @param string $type type of User Agent or Client.
		 */
		public function get_as_user_attr( $type ) {
			$get_as_user_attr = array(
				'rich_editing' => __( 'Visual Editor', 'as-rules-engine' ),
				'comment_shortcuts' => __( 'Keyboard Shortcuts', 'as-rules-engine' ),
				'show_admin_bar_front' => __( 'Toolbar', 'as-rules-engine' ),
				'user_name' => __( 'User Name', 'as-rules-engine' ),
				'first_name' => __( 'First Name', 'as-rules-engine' ),
				'last_name' => __( 'Last Name', 'as-rules-engine' ),
				'display_name' => __( 'Display Name', 'as-rules-engine' ),
				'nickname' => __( 'Nickname', 'as-rules-engine' ),
				'email' => __( 'Email', 'as-rules-engine' ),
				'user_url' => __( 'Website', 'as-rules-engine' ),
				'description' => __( 'Biographical Info', 'as-rules-engine' ),
				'email' => __( 'Email', 'as-rules-engine' ),
			);

			if ( 'agent_attr' === $type ) {
				$get_as_user_attr = apply_filters( 'asre_agent_attr', $get_as_user_attr );
			}
			if ( 'client_attr' === $type ) {
				$get_as_user_attr = apply_filters( 'asre_client_attr', $get_as_user_attr );
			}
			return $get_as_user_attr;
		}

		/**
		 * Get client user capabilities.
		 * @return array #client_caps array of client user capabilities for Awesome support work.
		 */
		private function get_client_caps() {
			$client_caps = array(
				'wpas_can_be_assigned' => 'Can Be Assigned',
				'wpas_smart_tickets_order' => 'Smart Tickets Order',
				'wpas_after_reply' => 'After Reply',
			);

			return $client_caps;
		}

		/**
		 * Get all user capabilities
		 *
		 * @return array User capabilities
		 */
		private function get_all_wp_caps() {

			$all_wp_caps = array();

			foreach ( get_role( 'administrator' )->capabilities as $key => $v ) {
				$all_wp_caps[ $key ] = $key;
			}

			$all_wp_caps = array_unique( $all_wp_caps );

			return $all_wp_caps;
		}

		/**
		 * Get all agent capabilities
		 *
		 * @return array Agent capabilities
		 */
		private function get_as_agents() {
			$agent = array();
			$as_user_obj = wpas_get_users( array( 'cap' => 'edit_ticket' ) );
			foreach ( $as_user_obj->members as $user ) {
				$user_obj = get_userdata( $user->ID );
				$agent[ $user->ID ] = '' !== $user_obj->first_name ? $user_obj->first_name . ' ' . $user_obj->last_name : $user_obj->display_name;
			}
			return $agent;
		}

		/**
		 * Get an array of terms from a single AS taxonomy
		 *
		 * @param string $tax name of taxonomy
		 *
		 * @return array List of taxonomy terms
		 *
		 * @since 1.1.0
		 */
		private function get_as_taxonomy( $tax ) {

			$taxonomies = array();

			/* Get the terms */
			$tax = get_terms( array(
				'taxonomy'   => $tax,
				'hide_empty' => false,
			) );

			/* Now loop through them and create a simple array...*/
			if ( ! is_wp_error( $tax ) && ! empty( $tax ) ) {

				foreach ( $tax as $the_term ) {
					$taxonomies[$the_term->term_id] = $the_term->name ;
				}

			}

			return $taxonomies;
		}

		/**
		 * Get all agent Emails
		 *
		 * @return string Agent Emails
		 */
		public function get_as_agents_email() {

			$agent_emails = array();
			$as_user_obj = wpas_get_users( array( 'cap' => 'edit_ticket' ) );
			foreach ( $as_user_obj->members as $user ) {
				$user_obj = get_userdata( $user->ID );
				$agent_emails[ $user->user_email ] = $user_obj->user_email;
			}

			foreach ( $agent_emails as $key => $v ) {
				if ( strpos( $v, $_REQUEST['term'] ) === false ) {
					unset( $agent_emails[ $key ] );
				}
			}

			array_values( $agent_emails );

			wp_send_json( $agent_emails );
		}

		/**
		 * Get Agent attribute filed value on dropdown change.
		 * @return json $data Agent attribute field data.
		 */
		public function get_agent_attr_value() {
			global $current_user;
			$req_field = $_REQUEST['attr_field'];
			$users_meta = array();
			$users_meta['email'] = $current_user->user_email;
			$users_meta['user_nicename'] = $current_user->user_nicename;
			$users_meta['user_name'] = $current_user->user_login;
			$users_meta['user_url'] = $current_user->user_url;

			if ( get_user_meta( $current_user->ID, $_REQUEST['attr_field'], true ) != '' ) {
				$data = get_user_meta( $current_user->ID, $_REQUEST['attr_field'], true );
			} else {
				$data = isset( $users_meta[ $req_field ] ) ? $users_meta[ $req_field ] : '';
			}

			wp_send_json( $data );
		}

		/**
		 * Function to return client attribute values
		 *
		 * @return string client attributes.
		 */
		public function get_client_attr_value() {
			global $current_user;
			$req_field = $_REQUEST['attr_field'];
			$users_meta = array();
			$users_meta['email'] = $current_user->user_email;
			$users_meta['user_nicename'] = $current_user->user_nicename;
			$users_meta['user_name'] = $current_user->user_login;
			$users_meta['user_url'] = $current_user->user_url;

			if ( get_user_meta( $current_user->ID, $_REQUEST['attr_field'], true ) != '' ) {
				$data = get_user_meta( $current_user->ID, $_REQUEST['attr_field'], true );
			} else {
				$data = isset( $users_meta[ $req_field ] ) ? $users_meta[ $req_field ] : '';
			}

			wp_send_json( $data );
		}

		/**
		 * Ajax callback to remove client attributes
		 */
		public function remove_client_attr_extra() {

			if ( ( isset( $_REQUEST['post_id'] ) && ! empty( $_REQUEST['post_id'] ) ) &&
				( isset( $_REQUEST['metaid'] ) )
			) {
				$post_id = $_REQUEST['post_id'];
				$meta_id = $_REQUEST['metaid'];
				$post = get_post( $post_id );

				if ( ! empty( $post ) ) {
					$get_client_extra = get_post_meta( $post->ID, AS_RE_CONDITIONS_META_PREFIX . 'client_attrs', true );
					if ( ! empty( $get_client_extra ) ) {
						if ( isset( $get_client_extra['fields'] ) && ! empty( $get_client_extra['fields'] ) ) {
							unset( $get_client_extra['fields'][ $meta_id ] );
							if ( isset( $get_client_extra['values'][ $meta_id ] ) && ! empty( $get_client_extra['values'][ $meta_id ] ) ) {
								unset( $get_client_extra['values'][ $meta_id ] );
							}
							if ( isset( $get_client_extra['operator'][ $meta_id ] ) && ! empty( $get_client_extra['operator'][ $meta_id ] ) ) {
								unset( $get_client_extra['operator'][ $meta_id ] );
							}
						}
					}
				}

				wp_send_json( 'success' );
				wp_die();
			} else {
				wp_send_json( 'failed' );
				wp_die();
			}
		}

		/**
		 * Ajax callback to remove custom field repeated values.
		 */
		public function remove_custom_field_extra() {

			if ( ( isset( $_REQUEST['post_id'] ) && ! empty( $_REQUEST['post_id'] ) ) &&
				( isset( $_REQUEST['metaid'] ) )
			) {
				$post_id = $_REQUEST['post_id'];
				$meta_id = $_REQUEST['metaid'];
				$post = get_post( $post_id );

				if ( ! empty( $post ) ) {
					$get_client_extra = get_post_meta( $post->ID, AS_RE_CONDITIONS_META_PREFIX . 'custom_field', true );
					if ( ! empty( $get_client_extra ) ) {
						if ( isset( $get_client_extra['fields'] ) && ! empty( $get_client_extra['fields'] ) ) {
							unset( $get_client_extra['fields'][ $meta_id ] );
							if ( isset( $get_client_extra['values'][ $meta_id ] ) && ! empty( $get_client_extra['values'][ $meta_id ] ) ) {
								unset( $get_client_extra['values'][ $meta_id ] );
							}
							if ( isset( $get_client_extra['operator'][ $meta_id ] ) && ! empty( $get_client_extra['operator'][ $meta_id ] ) ) {
								unset( $get_client_extra['operator'][ $meta_id ] );
							}
						}
					}
					//update_post_meta( $post->ID, AS_RE_CONDITIONS_META_PREFIX . 'client_attrs', $get_client_extra );
				}

				wp_send_json( 'success' );
				wp_die();
			} else {
				wp_send_json( 'failed' );
				wp_die();
			}
		}

		/**
		*
		* Load view for meta box
		*
		* @param string $file File name for load.
		* @param array $args other argument.
		*
		*/
		private function load_view( $file, $args = array() ) {
			if ( ! empty( $args ) && is_array( $args ) ) {
				extract( $args );
			}
			$path = AS_RE_PATH . 'includes/views/' . $file . '.php';
			require_once( $path );
		}

		/**
		 * Get all Email template title
		 * @param  string $post_type post type name
		 *
		 * @return array email template array.
		 */
		public function as_get_post_title( $post_type = 'asre_email' ) {
			$post_titles = array();
			$args = array( 'post_type' => $post_type, 'order' => 'ASC', 'posts_per_page' => -1 );
			$posts = get_posts( $args );
			if ( $posts ) {
				foreach ( $posts as $post ) {
					$post_titles[ $post->ID ] = $post->post_title;
				}
			}
			return $post_titles;
		}

		/**
		 * Get operator array used in condition.
		 *
		 * @return array conditional operators.
		 */
		public function get_operators() {
			return array(
				'default' => __( 'Select operator', 'as-rules-engine' ),
				'not' => __( 'NOT', 'as-rules-engine' ),
				'or' => __( 'OR', 'as-rules-engine' ),
				'and' => __( 'AND', 'as-rules-engine' ),
			);
		}

		/**
		 * Get Regex field values array.
		 *
		 * @return array conditional operators.
		 */
		public function get_regexes() {
			return array(
				'regex' => __( 'Regex', 'as-rules-engine' ),
				'starts' => __( 'Starts Contains', 'as-rules-engine' ),
				'equals' => __( 'Equals', 'as-rules-engine' ),
				'contains' => __( 'Contains', 'as-rules-engine' ),
			);
		}

		/**
		 * Get client attributes raw data
		 * Iterate and re-sconstruct new array for easier
		 * jQuery appending
		*/
		private function get_client_attr_extra() {
			global $post;
			$get_client_extra = get_post_meta( $post->ID, 'extra_attributes_data', true );
			if ( ! empty( $get_client_extra ) ) {
				$operators = $this->get_operators();
				$fields = $this->get_as_user_attr( 'client_attr' );
				$regexes = $this->get_regexes();
				$html = '';

				foreach ( $get_client_extra as $data_key => $data ) {
					$hightlight_class = ( isset( $data['operator'] ) && ! empty( $data['operator'] ) && 'default' != $data['operator'] ) ? 'condition_highlight' : '';
					$html .= '<div class="condition extra_client_attr ' . $hightlight_class . '">';
					$html .= '<select name="condition_client_attrs_value_operator_extra[]" class="condition-operators condition_client_attrs_value_operator">';
					foreach ( $operators as $key => $value ) {
						$html .= '<option value="' . $key . '" ' . selected( $data['operator'], $key, false ) . '>' . $value . '</option>';
					}
					$html .= '</select>';

					$html .= '<select name="condition_client_attrs_extra[]" class="condition_client_attrs">';
					foreach ( $fields as $key => $value ) {
						$html .= '<option value="' . $key . '" ' . selected( $data['field'], $key, false ) . '>' . $value . '</option>';
					}
					$html .= '</select>';
					$html .= '<select name="condition_client_attrs_value_regex_extra[]" class=" condition_client_attrs_value_regex">';
					foreach ( $regexes as $key => $value ) {
						$html .= '<option value="' . $key . '" ' . selected( $data['regex'], $key, false ) . '>' . $value . '</option>';
					}
					$html .= '</select>';
					$html .= '<input class="condition_client_attrs_value" type="text" name="condition_client_attrs_value_extra[]" value="' . $data['value'] . '">';

					$html .= '<button id="' . $data_key . '" post-id="' . $post->ID . '" type="button" class="remove_extra_client_attr">-</button>';
					$html .= '</div>';
				}
				return $html;
			}
			return $get_client_extra;
		}

		/**
		 * Get client attributes raw data
		 * Iterate and re-sconstruct new array for easier
		 * jQuery appending
		*/
		function get_agent_attr_extra() {
			global $post;
			$get_client_extra = get_post_meta( $post->ID, 'extra_agent_attributes_data', true );
			if ( ! empty( $get_client_extra ) ) {
				$operators = $this->get_operators();
				$fields = $this->get_as_user_attr( 'agent_attr' );
				$regexes = $this->get_regexes();
				$html = '';

				foreach ( $get_client_extra as $data_key => $data ) {
					$hightlight_class = ( isset( $data['operator'] ) && ! empty( $data['operator'] ) && 'default' != $data['operator'] ) ? 'condition_highlight' : '';
					$html .= '<div class="condition extra_client_attr ' . $hightlight_class . '">';
						$html .= '<select name="condition_agent_attrs_value_operator_extra[]" class="condition-operators condition_agent_attrs_value_operator">';
					foreach ( $operators as $key => $value ) {
						$html .= '<option value="' . $key . '" ' . selected( $data['operator'], $key, false ) . '>' . $value . '</option>';
					}
						$html .= '</select>';

						$html .= '<select name="condition_agent_attrs_extra[]" class="condition_agent_attrs">';
					foreach ( $fields as $key => $value ) {
						$html .= '<option value="' . $key . '" ' . selected( $data['field'], $key, false ) . '>' . $value . '</option>';
					}
						$html .= '</select>';

						$html .= '<select name="condition_agent_attrs_value_regex_extra[]" class=" condition_agent_attrs_value_regex">';
					foreach ( $regexes as $key => $value ) {
						$html .= '<option value="' . $key . '" ' . selected( $data['regex'], $key, false ) . '>' . $value . '</option>';
					}
						$html .= '</select>';

						$html .= '<input class="condition_agent_attrs_value" type="text" name="condition_agent_attrs_value_extra[]" value="' . $data['value'] . '">';

						$html .= '<button id="' . $data_key . '" post-id="' . $post->ID . '" type="button" class="remove_extra_agent_attr">-</button>';
					$html .= '</div>';
				}
				return $html;
			}
			return $get_client_extra;
		}


		/**
		 * Get custom fields raw data
		 * Iterate and re-sconstruct new array for easier
		 * jQuery appending
		*/
		private function get_custom_fields_extra() {
			global $post;
			$get_custom_field_extra = get_post_meta( $post->ID, 'extra_customfields_data', true );
			if ( ! empty( $get_custom_field_extra ) ) {
				$operators = $this->get_operators();
				$regexes = $this->get_regexes();
				$regexes = array_merge( $regexes,  array( '>' => __( 'Greater than(>)', 'as-rules-engine' ), '<' => __( 'Less then(<)', 'as-rules-engine' ) ) );
				$fields = $this->ticket_custom_fields();
				$fields_type = $this->ticket_custom_fields_type();
				$html = '';
				foreach ( $get_custom_field_extra as $data_key => $data ) {
					$hightlight_class = ( isset( $data['operator'] ) && ! empty( $data['operator'] ) && 'default' != $data['operator'] ) ? 'condition_highlight' : '';
					$html .= '<div class="condition extra_custom_field ' . $hightlight_class . '">';
					$html .= '<select name="condition_custom_field_value_operator_extra[]" class="condition-operators condition_custom_field_value_operator">';
					$html .= '<option value="default" data-field_type="">'. __( 'Please Select', 'as-rules-engine' ) .'</option>';
					foreach ( $operators as $key => $value ) {
						$html .= '<option value="' . $key . '" ' . selected( $data['operator'], $key, false ) . '>' . $value . '</option>';
					}
					$html .= '</select>';

					$html .= '<select name="condition_custom_field_extra[]" class="condition_custom_field">';
					$selected_field_type = '';
					foreach ( $fields as $key => $value ) {
						$as_field_type = isset( $fields_type[$key] )? $fields_type[$key]: '';
						$check = selected( $data['field'], $key, false );
						if( $check ){
							$selected_field_type = $as_field_type;
						}
						$html .= '<option value="' . $key . '" ' . selected( $data['field'], $key, false ) . ' data-field_type="'. $as_field_type .'">' . $value . '</option>';
					}
					$html .= '</select>';
					$html .= '<select name="condition_custom_field_value_regex_extra[]" class="condition_custom_field_value_regex">';
					foreach ( $regexes as $key => $value ) {
						if( 'date-field' == $selected_field_type ){
							if( '>'== $key || '<' == $key || 'equals' == $key ){
								$html .= '<option value="' . $key . '" ' . selected( $data['regex'], $key, false ) . '>' . $value . '</option>';
							} else{
								$html .= '<option value="' . $key . '" ' . selected( $data['regex'], $key, false ) . ' disabled>' . $value . '</option>';
							}
						}else{
							if( '>'== $key || '<' == $key ){
								$html .= '<option value="' . $key . '" ' . selected( $data['regex'], $key, false ) . ' disabled>' . $value . '</option>';
							} else{
								$html .= '<option value="' . $key . '" ' . selected( $data['regex'], $key, false ) . '>' . $value . '</option>';
							}
						}
					}
					$html .= '</select>';

					$html .= '<input class="condition_custom_field_value" type="text" name="condition_custom_field_value_extra[]" value="' . $data['value'] . '">';

					$html .= '<button id="' . $data_key . '" post-id="' . $post->ID . '" type="button" class="remove_extra_custom_field">-</button>';
					$html .= '</div>';
				}
				return $html;
			}
			return $get_custom_field_extra;
		}

		/**
		 * Construct HTML markup for client attributes
		 * We will append this on add button through Ajax
		*/
		private function get_client_attr_extra_default() {
			$operators = $this->get_operators();
			$regexes = $this->get_regexes();
			$fields = $this->get_as_user_attr( 'client_attr' );
			$html = '<div class="condition extra_client_attr">';
			$html .= '<select name="condition_client_attrs_value_operator_extra[]" class="condition-operators condition_client_attrs_value_operator">';
			foreach ( $operators as $key => $value ) {
				$html .= '<option value="' . $key . '">' . $value . '</option>';
			}
			$html .= '</select>';
			$html .= '<select name="condition_client_attrs_extra[]" class="condition_client_attrs">';
			foreach ( $fields as $key => $value ) {
				$html .= '<option value="' . $key . '">' . $value . '</option>';
			}
			$html .= '</select>';

			$html .= '<select name="condition_client_attrs_value_regex_extra[]" class=" condition_client_attrs_value_regex">';
			foreach ( $regexes as $key => $value ) {
				$html .= '<option value="' . $key . '">' . $value . '</option>';
			}
			$html .= '</select>';

			$html .= '<input class="condition_client_attrs_value" type="text" name="condition_client_attrs_value_extra[]">';

			$html .= '<button id="#" type="button" class="remove_extra_client_attr">-</button>';
			$html .= '</div>';
			return $html;
		}

		/**
		 * Construct HTML markup for agent attributes
		 * We will append this on add button through Ajax
		*/
		function get_agent_attr_extra_default() {
			$operators = $this->get_operators();
			$regexes = $this->get_regexes();
			$fields = $this->get_as_user_attr( 'agent_attr' );
			$html = '<div class="condition extra_client_attr">';
				$html .= '<select name="condition_agent_attrs_value_operator_extra[]" class="condition-operators condition_agent_attrs_value_operator">';
			foreach ( $operators as $key => $value ) {
				$html .= '<option value="' . $key . '">' . $value . '</option>';
			}
				$html .= '</select>';

				$html .= '<select name="condition_agent_attrs_extra[]" class="condition_agent_attrs">';
			foreach ( $fields as $key => $value ) {
				$html .= '<option value="' . $key . '">' . $value . '</option>';
			}
				$html .= '</select>';

				$html .= '<select name="condition_agent_attrs_value_regex_extra[]" class=" condition_agent_attrs_value_regex">';
			foreach ( $regexes as $key => $value ) {
				$html .= '<option value="' . $key . '">' . $value . '</option>';
			}
				$html .= '</select>';

				$html .= '<input class="condition_agent_attrs_value" type="text" name="condition_agent_attrs_value_extra[]">';

				$html .= '<button id="#" type="button" class="remove_extra_agent_attr">-</button>';
			$html .= '</div>';
			return $html;
		}

		/**
		 * Custom field related repeated fields html function.
		 *
		 * @return string repeated field html.
		 */
		private function get_custom_field_extra_default() {
			$operators = $this->get_operators();
			$regexes = $this->get_regexes();
			$regexes = array_merge( $regexes,  array( '>' => __( 'Greater than(>)', 'as-rules-engine' ), '<' => __( 'Less than(<)', 'as-rules-engine' ) ) );
			$fields = $this->ticket_custom_fields();
			$fields_type = $this->ticket_custom_fields_type();
			$html = '<div class="condition extra_custom_field">';
				$html .= '<select name="condition_custom_field_value_operator_extra[]" class="condition-operators condition_custom_field_value_operator">';

			if ( ! empty( $operators ) && is_array( $operators ) ) {
				foreach ( $operators as $key => $value ) {
					$html .= '<option value="' . $key . '">' . $value . '</option>';
				}
			}
				$html .= '</select>';

				$html .= '<select name="condition_custom_field_extra[]" class="condition_custom_field">';
				$html .= '<option value="default" data-field_type="">'. __( 'Please Select', 'as-rules-engine' ) .'</option>';
			foreach ( $fields as $key => $value ) {
				$as_field_type = isset( $fields_type[$key] )? $fields_type[$key]: '';
				$html .= '<option value="' . $key . '"  data-field_type="'. $as_field_type .'">' . $value . '</option>';
			}
				$html .= '</select>';

				$html .= '<select name="condition_custom_field_value_regex_extra[]" class="condition_custom_field_value_regex">';
			foreach ( $regexes as $key => $value ) {
				if( '>'== $key || '<' == $key ){
					$html .= '<option value="' . $key . '" disabled>' . $value . '</option>';
				} else{
					$html .= '<option value="' . $key . '">' . $value . '</option>';
				}
			}
				$html .= '</select>';

				$html .= '<input class="condition_custom_field_value" type="text" name="condition_custom_field_value_extra[]">';

				$html .= '<button id="#" type="button" class="remove_extra_custom_field">-</button>';
			$html .= '</div>';
			return $html;
		}
	}
}
