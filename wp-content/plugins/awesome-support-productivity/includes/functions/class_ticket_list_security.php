<?php


class WPAS_PF_Ticket_List_Security {
	
	protected static $instance = null;
	
	protected $profile_fields = null;
	
	
	public function __construct() {
		
		add_action( 'init',				array( $this, 'init' ) );
		add_action( 'admin_menu',			array( $this, 'add_groups_submenu') );
		
		add_action( 'edit_user_profile',		array( $this, 'user_profile_add_fields' ),	10, 1 ); // Display group and profile fields on user profile page
		add_action( 'show_user_profile',		array( $this, 'user_profile_add_fields' ),	10, 1 ); // Display group and profile fields on user profile page
		
		add_action( 'personal_options_update',		array( $this, 'user_profile_save_fields' ) );
		add_action( 'edit_user_profile_update',		array( $this, 'user_profile_save_fields' ) );
		
		add_action( 'add_meta_boxes',			array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_as_security_profile',	array( $this, 'save_profile' ) );
		
		add_filter( 'posts_clauses',			array( $this, 'filter_tickets' ),		20, 2 );
		add_filter( 'wpas_assignee_meta_query',		array( $this, 'assignee_meta_query' ),		11, 3 ) ;
		add_action( 'admin_enqueue_scripts',		array( $this, 'enqueue_select2_assets' ) );
		
		add_action( 'wp_ajax_wpas_get_products',	array( $this, 'get_products_ajax' ),		10, 0 );
		
		add_filter( 'post_updated_messages',		array( $this, 'profile_updated_messages' ),	11, 1 );
		add_action( 'manage_users_columns',		array( $this, 'add_user_groups_column' ),	11, 1 );
		add_action( 'manage_users_custom_column',	array( $this, 'user_groups_column_content' ),	11, 3 );
		
		add_action( 'admin_head',			array( $this, 'admin_head' ),			11, 0 );


	}
	
	
	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		
		return self::$instance;
	}
	
	
	
	public function enqueue_select2_assets() {
		
		if ( false === wp_style_is( 'wpas-select2', 'enqueued' ) ) {
			wp_enqueue_style( 'wpas-select2' );
		}
		
		if ( false === wp_script_is( 'wpas-select2', 'enqueued' ) ) {
			wp_enqueue_script( 'wpas-select2' );
		}
		
		if ( false === wp_script_is( 'wpas-users', 'enqueued' ) ) {
			wp_enqueue_script( 'wpas-users' );
		}
		
		
	}
	
	/**
	 * Register security profile post and as_user_group taxonomy
	 */
	
	public function init() {
		
		$this->register_group_taxonomy();
		$this->register_post_type_security_profile();
	}
	
	/**
	 * Add noindex meta tag
	 */
	public function admin_head() {
		global $pagenow, $post_type;
		
		if( ( 'edit.php' === $pagenow || 'post.php' === $pagenow ) &&  'as_security_profile' === $post_type ) {
			echo '<meta name="robots" content="noindex" />';
		}
	}
	
	/**
	 * Security profile post changes messages
	 * 
	 * @param array $messages
	 */
	public function profile_updated_messages( $messages ) {
		
		if( isset( $messages['post'] ) ) {
			$profile_messages = $messages['post'];
			$profile_messages[6] = __( "Security Profile Saved! You can now assign it to any agent", "wpas_productivity" );
			$messages['as_security_profile'] = $profile_messages;
		}
		return $messages;
	}
	
	/**
	 * Add groups column
	 * 
	 * @param array $columns
	 * @return array
	 */
	public function add_user_groups_column( $columns ) {
		$columns['groups'] = __( 'Groups', 'wpas_productivity' );
		return $columns;
	}
	
	/**
	 * Return content for groups column 
	 * 
	 * @param string $value
	 * @param dtring $column_name
	 * @param int $user_id
	 * 
	 * @return string
	 */
	public function user_groups_column_content( $value, $column_name, $user_id ) {
		
		if ( 'groups' === $column_name ) {
			
			$groups = array();
			$terms = wp_get_object_terms( $user_id, 'as_user_group' );
			foreach( $terms as $term ) {
				$groups[] = $term->name;
			}
			
			$value = implode( ', ', $groups );
		}
		
		return $value;
	}
	
	
	/**
	 * Filter tickets based on logged in agent security profile while listing tickets
	 * 
	 * @global string $pagenow
	 * @global object $wpdb
	 * @param array $pieces
	 * @param object $wp_query
	 * 
	 * @return array
	 */
	public function filter_tickets( $pieces , $wp_query ) {
		global $pagenow, $wpdb;
		
		
		$is_listing_query = isset( $wp_query->query['wpas_tickets_query'] ) && 'listing' === $wp_query->query['wpas_tickets_query'];
		
		if( !$is_listing_query ) {
			
			if ( ( ! is_admin()
				|| 'edit.php' !== $pagenow
				|| ! isset( $_GET['post_type'] )
				|| 'ticket' !== $_GET['post_type']
				|| ! $wp_query->is_main_query() )
			) {
				return $pieces;
			}
		}
		
		$skip_fields = isset ( $wp_query->query['wpas_tickets_query_skip_filters'] ) ? $wp_query->query['wpas_tickets_query_skip_filters'] : array();
		
		$user_id = get_current_user_id();
		
		$fields = $this->get_profile_fields();
		$profile = $this->get_user_profile( $user_id );
		
		$__post_query = $__meta_query = $__tax_query = $__joins = $clauses = array();
		
		
		
		$meta_join_num = 0;
		$tax_join_num = 0;
		
		$just_my_tickets = isset( $profile['just_my_tickets'] ) ? $profile['just_my_tickets'] : '' ;
		$all_tickets = isset( $profile['all_tickets'] ) ? $profile['all_tickets'] : '';
		
		if( 'yes' === $all_tickets || empty( $profile ) ) {
			return $pieces;
		}
		
		if ( 'yes' === $just_my_tickets ) {
			
			$my_ticket_fields = array();
			$my_ticket_fields['primary_agents'] = $fields['primary_agents'];
			$profile['primary_agents']['value'] = array( $user_id );
			$profile['primary_agents']['selection_type'] = 'selected';
			
			
			if( isset( $fields['secondary_agents'] ) ) {
				$my_ticket_fields['secondary_agents'] = $fields['secondary_agents'];
				$profile['secondary_agents']['value'] = array( $user_id );
				$profile['secondary_agents']['selection_type'] = 'selected';
			}
			
			if( isset( $fields['tertiary_agents'] ) ) {
				$my_ticket_fields['tertiary_agents'] = $fields['tertiary_agents'];
				$profile['tertiary_agents']['value'] = array( $user_id );
				$profile['tertiary_agents']['selection_type'] = 'selected';
			}
			
			
			$fields = $my_ticket_fields;
		}
		
		$match_relation = isset( $profile['match_relation'] ) && $profile['match_relation'] ? $profile['match_relation'] : 'or';
		$match_relation = strtoupper( $match_relation );
		
		if( !empty( $fields ) ) {
			
			foreach ( $fields as $f_name => $f ) {
				
				if( in_array( $f_name, $skip_fields ) ) {
					continue;
				}

				$selection_type = '';
				$value = array();

				if( isset( $profile[ $f_name ] ) ) {
					$selection_type = $profile[ $f_name ]['selection_type'];
					$value = $profile[ $f_name ]['value'];
				}


				if( 'selected' === $selection_type && !empty( $value ) ) {

					if( isset( $f['type'] ) && 'tax' ===  $f['type'] ) {
						
						$tax_join_num++;

						$term_tax_ids = $this->get_term_tax_ids($f['tax'], $value);

						$join_field = isset( $f['filter'] ) && isset( $f['filter']['join_field'] ) ? $f['filter']['join_field'] : 'ID';
						
						if( isset( $f['filter'] ) && isset( $f['filter']['join_name'] ) ) {
							$join_name =  $f['filter']['join_name'];
						} else {
							$join_name = "pfsptj{$tax_join_num}";
						}
						
						
						$__joins[] = "LEFT JOIN {$wpdb->term_relationships} AS $join_name ON ({$wpdb->posts}.{$join_field} = {$join_name}.object_id)";
						 

						$__tax_query[] = "{$join_name}.term_taxonomy_id IN (" . implode(', ', $term_tax_ids) . ")";

					} elseif( isset( $f['filter'] ) ) {

						$data_type = isset ( $f['filter']['data_type'] ) ? $f['filter']['data_type'] : 'int';


						$value_in =  'string' === $data_type ? "'" . implode( "', '", $value ) . "'" : implode( ', ', $value );

						if( 'meta' === $f['filter']['type'] ) {
							$meta_join_num++;
							$join_id = "pfmt{$meta_join_num}";
							$__joins[] = "LEFT JOIN {$wpdb->postmeta} AS {$join_id} ON {$join_id}.post_id = {$wpdb->posts}.ID";
							$__meta_query[] = "( {$join_id}.meta_key = '{$f['filter']['key']}' AND {$join_id}.meta_value {$f['filter']['compare']} ({$value_in}) )";
						} elseif( 'field' === $f['filter']['type'] ) {
							$__post_query[] = "{$wpdb->posts}.{$f['filter']['key']} IN ({$value_in})";
						}

					}
				}
			}
			
		}
		
		
		
		
		
		$clauses = array_merge( $__post_query, $__tax_query, $__meta_query );
		
		if( !empty( $clauses ) ) {
			$pieces['where'] .= " AND (" . implode(" {$match_relation} ", $clauses ) . ")";
		}
		
		if( !empty( $__joins ) ) {
			$pieces['join'] .= " " . implode(' ', $__joins );
		}
		
		
		$pieces['groupby'] = "{$wpdb->posts}.ID";
		
		return $pieces;
	}
	
	
	/**
	 * Return term taxonomy ids
	 * 
	 * @param dtring $tax
	 * @param array $term_ids
	 * 
	 * @return array
	 */
	public function get_term_tax_ids( $tax, $term_ids ) {
			
		$term_taxonomy_ids = array();
		
		if( is_array( $term_ids ) && !empty( $term_ids ) ) {
			foreach( $term_ids as $term_id ) {
				$term = get_term_by( 'id', (int) $term_id, $tax );
				if( $term ) {
					$term_taxonomy_ids[] = $term->term_taxonomy_id;
				}
			}
		}
		
		return $term_taxonomy_ids;
	}
	
	/**
	 * Clear assignee meta_query while listing tickets
	 * 
	 * @param array $meta_query
	 * @param int $user_id
	 * @param boolean $profile_filter
	 * 
	 * @return array
	 */
	function assignee_meta_query( $meta_query, $user_id, $profile_filter ) {
		
		if( true === $profile_filter ) {
			
			$profile = $this->get_user_profile( $user_id );
			
			$just_my_tickets = isset( $profile['just_my_tickets'] ) ? $profile['just_my_tickets'] : '' ;
			$all_tickets = isset( $profile['all_tickets'] ) ? $profile['all_tickets'] : '';
			
			
			if( !empty( $profile ) ) {
				$meta_query = array();
			}
		}
		
		
		return $meta_query;
	}
	
	
	/**
	 * Add groups sub menu taxonomy page to users menu
	 */
	public function add_groups_submenu() {

		$tax = get_taxonomy( 'as_user_group' );

		add_users_page(
			esc_attr( $tax->labels->menu_name ),
			esc_attr( $tax->labels->menu_name ),
			$tax->cap->manage_terms,
			'edit-tags.php?taxonomy=' . $tax->name
		);
	}
	
	/**
	 * Register group taxonomy
	 */
	public function register_group_taxonomy() {
		
		$args = array(
			'public' => true,
			'labels' => array(
				'name' => __( 'Groups', 'wpas_productivity' ),
				'singular_name' => __( 'Group', 'wpas_productivity' ),
				'menu_name' => __( 'Ticket User Groups', 'wpas_productivity' ),
				'search_items' => __( 'Search Groups', 'wpas_productivity' ),
				'popular_items' => __( 'Popular Groups', 'wpas_productivity' ),
				'all_items' => __( 'All Groups', 'wpas_productivity' ),
				'edit_item' => __( 'Edit Group', 'wpas_productivity' ),
				'update_item' => __( 'Update Group', 'wpas_productivity' ),
				'add_new_item' => __( 'Add New Group', 'wpas_productivity' ),
				'new_item_name' => __( 'New Group Name', 'wpas_productivity' ),
				'separate_items_with_commas' => __( 'Separate Groups with commas', 'wpas_productivity' ),
				'add_or_remove_items' => __( 'Add or remove Groups', 'wpas_productivity' ),
				'choose_from_most_used' => __( 'Choose from the most popular Groups', 'wpas_productivity' ),
			)
		);
		
		register_taxonomy( 'as_user_group', 'user', $args );
	}
	
	/**
	 * Add groups and security profile fields to user profiles
	 * 
	 * @param object $user
	 */
	public function user_profile_add_fields( $user ) {
		
		if( $user->has_cap( 'wpas_user' ) ) :
			
			$terms = wp_get_object_terms( $user->ID, 'as_user_group' );
			$groups = array();
			
			foreach( $terms as $term ) {
				$groups[] = $term->term_id;
			}
			
		?>
		<div id="wpas_pf_ui_sn_user_groups">
			<table class="form-table">
				<tr>
					<th><label><?php _e( 'Groups', 'wpas_productivity' ); ?></label></th>
					<td>
						<input type="hidden" name="wpas_pf_user_group" value="" />
						<?php echo $this->groups_dropdown( $groups ); ?></td>
				</tr>
			</table>
			
		</div>
		<?php
		
		elseif( $user->has_cap( 'edit_ticket' ) && current_user_can( 'administer_awesome_support' ) ) :
			
			$profile = get_user_meta( $user->ID, 'wpas_pf_profile', true );
		
		?>
		<div id="wpas_pf_ui_sn_user_groups">
			<table class="form-table">
				<tr>
					<th><label><?php _e( 'Ticket Security Profile', 'wpas_productivity' ); ?></label></th>
					<td><?php echo $this->profiles_dropdown( $profile ); ?></td>
				</tr>
			</table>
			
		</div>
		<?php
		
		
		
		endif;
	}
	
	/**
	 * Save groups and security profile fields
	 * 
	 * @param int $user_id
	 */
	public function user_profile_save_fields( $user_id ) {
		
		if ( current_user_can( 'edit_user', $user_id ) ) { 
			
			if( isset( $_POST['wpas_pf_user_group']) ) {
				
				$new_ids = filter_input( INPUT_POST, 'wpas_pf_user_group', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
				$new_ids = is_array( $new_ids ) ? $new_ids : array();
				
				$term_ids = array();
				
				foreach( $new_ids as $term_id ) {
					$term_ids[] = (int) $term_id;
				}
				
				wp_set_object_terms( $user_id, $term_ids, 'as_user_group' );
				
			} 
			elseif( isset( $_POST['wpas_pf_profile']) && current_user_can( 'administer_awesome_support' ) ) {
				$wpas_pf_profile = filter_input( INPUT_POST, 'wpas_pf_profile', FILTER_SANITIZE_NUMBER_INT );
				update_user_meta( $user_id, 'wpas_pf_profile', $wpas_pf_profile );
			}
		}
	}
	
	/**
	 * Return all groups
	 * @return array
	 */
	public function get_groups() {
		
		return get_terms( array(
		    'taxonomy' => 'as_user_group',
		    'hide_empty' => false
		) );
	}
	
	/**
	 * Return user's display name
	 * 
	 * @param int $user_id
	 * 
	 * @return string
	 */
	public function get_user( $user_id ) {
		
		$user = get_user_by('id', $user_id);
		
		if ( $user ) {
			return $user->display_name;
		}
		return '';
	}
	
	/**
	 * Return product name
	 * 
	 * @param int $product_id
	 * 
	 * @return string
	 */
	
	public function get_product( $product_id ) {
		
		if( $product_id ) {
			$term = get_term_by( 'term_id', $product_id, 'product' );
			if( $term ) {
				return $term->name;
			}
		}
		
		return '';
	}
	
		
	
	/**
	 * Generate groups dropdown
	 * 
	 * @param array $selected
	 * @return string
	 */
	public function groups_dropdown( $selected = array() ) {
		$groups = $this->get_groups();
		
		$dropdown = '<select multiple name="wpas_pf_user_group[]">';
		foreach( $groups as $group ) {
			$_selected = in_array( $group->term_id, $selected ) ? ' selected="selected"' : '';
			$dropdown .= '<option value="' . $group->term_id . '"' . $_selected . '>' . $group->name . '</option>';
		}
		
		$dropdown .= '</select>';
		
		return $dropdown;
	}
	
	
	public function select2_dropdown( $options, $args = array() ) {
		
		$name = $args['name'];
		$selected = isset( $args['selected'] ) && $args['selected'] ? $args['selected'] : array();
		$item_cb = isset( $args['item_cb'] ) ? $args['item_cb'] : '';
		$options = '';
		
		$data_attr = isset( $args['data_attr'] ) ? $args['data_attr'] : array();
		
		if( $item_cb ) {
			foreach( $selected as $item ) {
				$item_name = call_user_func_array( array( $this, $item_cb ), array( $item ) );
				$options .= "<option selected=\"selected\" value=\"{$item}\">{$item_name}</option>";
			}
		}
		
		$staff_atts = array(
			'name'      => $name . '[]',
			'id'        => $name,
			'select2'   => true,
			'data_attr' => $data_attr,
			'multiple' => true
		);
		
		$dropdown = wpas_dropdown( $staff_atts, $options );
		
		return $dropdown;
	}
	
	
	/**
	 * Generate multi select dropdown with checkboxes for security profile fields
	 * 
	 * @param array $options
	 * @param array $args
	 * @return string
	 */
	public function checkbox_dropdown( $options, $args = array() ) {
		
		$name = $args['name'];
		$selected = isset( $args['selected'] ) && $args['selected'] ? $args['selected'] : array();
		
		$dropdown = '';
		
		foreach( $options as $opt_id => $opt ) {
			
			if( $opt instanceof WP_Term ) {
				$opt_id = $opt->term_id;
				$opt_name = $opt->name;
			} else {
				$opt_name = $opt;
			}
			
			$_selected = in_array( $opt_id, $selected ) ? ' checked="checked"' : '';
			
			$dropdown .= '<div class="option"><label><input name="'.$name.'[]" type="checkbox"'.$_selected.' value="'.$opt_id.'" /> '.$opt_name.'</label></div>';
			
		}
		
		return $dropdown;
	}
	
	
	/**
	 * Return all departments
	 * 
	 * @return array
	 */
	function get_departments() {
		$departments = array();
		
		if ( true === boolval( wpas_get_option( 'departments' ) ) ) {
			$departments = get_terms( array(
				'taxonomy'   => 'department',
				'hide_empty' => false,
			) );
		}
		
		return $departments;
	}
	
	/**
	 * Return all products
	 * 
	 * @return array
	 */
	function get_products( $args = array() ) {
		$products = array();
		
		if ( true === boolval( wpas_get_option( 'support_products' ) ) ) {
			$products = get_terms( array(
				'taxonomy'   => 'product',
				'hide_empty' => false,
				'search' => isset( $args['s'] ) ? $args['s'] : ''
			) );
		}
		
		
		
		return $products;
	}
	
	function get_products_ajax() {
		
		$args = array();
		$result = array();
		
		if( isset( $_POST['q'] ) ) {
			$args['s'] = sanitize_text_field( $_POST['q'] );
		}
		
		$products = $this->get_products( $args );
		
		
		if ( count( $products ) > 0 ) {
			
			foreach ( $products as $product ) {
				$result[] = array(
				    'ticket_id'     => $product->term_id,
				    'text' => $product->name
				);
			}
		}
		
		echo json_encode( $result );
		die();
		
	}
	
	/**
	 * Return all channels
	 * 
	 * @return array
	 */
	function get_channels() {
		
		$products = get_terms( array(
			'taxonomy'   => 'ticket_channel',
			'hide_empty' => false,
		) );
		
		return $products;
	}
	
	/**
	 * Return all priorities
	 * 
	 * @return array
	 */
	private function get_priorities() {
		
		$priorities = array();
		
		if ( true === boolval( wpas_get_option( 'support_priority' ) ) ) {
			$priorities = get_terms( array(
				'taxonomy'   => 'ticket_priority',
				'hide_empty' => false,
			) );
		}
		
		
		
		return $priorities;
	}
	
	/**
	 * Return all users
	 * 
	 * @return array
	 */
	private function get_users(){
		$users = array();
		
		
		$_users = get_users(array('role' => 'wpas_user'));
		
		foreach( $_users as $user ) {
			$users[ $user->ID ] = $user->display_name;
		}
		
		return $users;
	}
	
	/**
	 * Return all user groups
	 * 
	 * @return array
	 */
	private function get_user_groups(){
		
		$groups = get_terms( array(
			'taxonomy'   => 'as_user_group',
			'hide_empty' => false,
		) );
		
		return $groups;
	}
	
	/**
	 * Return all ticket statuses
	 * 
	 * @return array
	 */
	private function get_statuses() {
		return wpas_get_post_status();
	}
	
	/**
	 * Return ticket states
	 * 
	 * @return array
	 */
	private function get_states() {
		return array(
		    'open'   => __( 'Open',   'wpas_productivity' ),
		    'closed' => __( 'Closed', 'wpas_productivity' )
		);
	}
	
	/**
	 * Register new post type 'as_security_profile'
	 */
	public function register_post_type_security_profile() {

		$labels = array(
			'menu_name'          => __( 'Security Profiles', 'wpas_productivity' ),
			'name'               => _x( 'Security Profiles', 'Post Type General Name', 'wpas_productivity' ),
			'singular_name'      => _x( 'Security Profile', 'Post Type Singular Name', 'wpas_productivity' ),
			'add_new_item'       => __( 'Add New Security Profile', 'wpas_productivity' ),
			'add_new'            => __( 'New Security Profile', 'wpas_productivity' ),
			'not_found'          => __( 'No Security Profile found', 'wpas_productivity' ),
			'not_found_in_trash' => __( 'No Security Profile found in Trash', 'wpas_productivity' ),
			'parent_item_colon'  => __( 'Parent Security Profile:', 'wpas_productivity' ),
			'all_items'          => __( 'Security Profiles', 'wpas_productivity' ),
			'view_item'          => __( 'View Security Profile', 'wpas_productivity' ),
			'edit_item'          => __( 'Edit Security Profile', 'wpas_productivity' ),
			'update_item'        => __( 'Update Security Profile', 'wpas_productivity' ),
			'search_items'       => __( 'Search Security Profiles', 'wpas_productivity' ),
		);

		/* Post type capabilities */
		$cap = array(
			'read'			 => 'administer_awesome_support',
			'read_post'		 => 'administer_awesome_support',
			'read_private_posts' 	 => 'administer_awesome_support',
			'edit_post'		 => 'administer_awesome_support',
			'edit_posts'		 => 'administer_awesome_support',
			'edit_others_posts' 	 => 'administer_awesome_support',
			'edit_private_posts' 	 => 'administer_awesome_support',
			'edit_published_posts' 	 => 'administer_awesome_support',
			'publish_posts'		 => 'administer_awesome_support',
			'delete_post'		 => 'administer_awesome_support',
			'delete_posts'		 => 'administer_awesome_support',
			'delete_private_posts' 	 => 'delete_private_ticket',
			'delete_published_posts' => 'administer_awesome_support',
			'delete_others_posts' 	 => 'administer_awesome_support'
		);	

		$args = array(
			'labels'              => $labels,
			'hierarchical'        => true,
			'description'         => __( 'Ticket security profiles', 'wpas_productivity' ),
			'supports'            => array( 'title' ),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=ticket',
			'show_in_admin_bar'   => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'can_export'          => true,
			'capabilities'        => $cap,
			'capability_type'     => 'administer_awesome_support',
			'query_var'	      => false
		);

		
		
		//'public' => false, has_archive => false, publicaly_queryable => false, 
		
		register_post_type( 'as_security_profile', $args );
	
	}
	
	
	/**
	 * Return all security profile data based on profile_id
	 * 
	 * @param int $profile_id
	 * 
	 * @return array
	 */
	private function get_profile( $profile_id ) {
		
		$profile = array();
		
		$fields = $this->get_profile_fields();
		
		if( $profile_id ){
			$post = get_post( $profile_id );
			
			if( $post ) {
				foreach( $fields as $f_name => $f ) {
					
					$name = "pf_{$f_name}";
					$selection_type_key = "{$name}_selection_type";
					
					$value = get_post_meta( $profile_id, $name, true );
					$value = $value ? $value : array();
					
					$selection_type = get_post_meta( $profile_id, $selection_type_key, true );
					
					$profile[ $f_name ] = array( 
						'value' => $value, 
						'selection_type' => $selection_type 
						);
				}
				
				$profile['just_my_tickets'] = get_post_meta( $profile_id, 'pf_just_my_tickets', true );
				$profile['all_tickets'] = get_post_meta( $profile_id, 'pf_all_tickets', true );
				$profile['match_relation'] = get_post_meta( $profile_id, 'pf_match_relation', true );
			}
		}
		
		return $profile;
		
	}
	
	/**
	 * Return user security profile
	 * 
	 * @param int $user_id
	 * @return array
	 */
	function get_user_profile( $user_id ) {
		$profile_id = get_user_meta( $user_id ,'wpas_pf_profile', true );
		
		$profile = array();
		if( $profile_id ) {
			$profile = $this->get_profile( $profile_id );
		}
		
		return $profile;
	}
	
	/**
	 * Return all security profile fields
	 * 
	 * @return array
	 */
	function get_profile_fields() {
		
		if( null === $this->profile_fields ) {
			
			$agents = $this->get_agents();
			
			$profile_fields = array();
			
			if( true === boolval( wpas_get_option( 'departments' ) ) ) {
				$profile_fields['departments'] = array(
					'field_label'		=> __( 'Departments', 'wpas_productivity' ),
					'all_label'		=> __( 'All Departments', 'wpas_productivity' ),
					'selected_label'	=> __( 'Selected Departments', 'wpas_productivity' ),
					'options'		=> $this->get_departments(),
					'type'			=> 'tax',
					'tax'			=> 'department',
					'field_active_opt'	=> 'departments',
					'field_view'		=> 'checkbox_dropdown'
				);
			}
			
			if( true === boolval( wpas_get_option( 'support_products' ) ) ) {
				$profile_fields['products'] = array(
					'field_label'		=> __( 'Products', 'wpas_productivity' ),
					'all_label'		=> __( 'All Products', 'wpas_productivity' ),
					'selected_label'	=> __( 'Selected Products', 'wpas_productivity' ),
					'options'		=> $this->get_products(),
					'type'			=> 'tax',
					'tax'			=> 'product',
					'field_active_opt'	=> 'support_products',
					'field_view'		=> 'select2_dropdown',
					'get_item_cb'		=> 'get_product',
					'data_attr'		=> array( 'opt-type' => 'product-picker' )
				);
			}
			
			$profile_fields['channels'] = array(
					'field_label'		=> __( 'Channels', 'wpas_productivity' ),
					'all_label'		=> __( 'All Channels', 'wpas_productivity' ),
					'selected_label'	=> __( 'Selected Channels', 'wpas_productivity' ),
					'options'		=> $this->get_channels(),
					'type'			=> 'tax',
					'tax'			=> 'ticket_channel',
					'field_view'		=> 'checkbox_dropdown'
				);
			
			if( true === boolval( wpas_get_option( 'support_priority' ) ) ) {
				$profile_fields['priorities'] = array(
					'field_label'		=> __( 'Priorities', 'wpas_productivity' ),
					'all_label'		=> __( 'All Priorities', 'wpas_productivity' ),
					'selected_label'	=> __( 'Selected Priorities', 'wpas_productivity' ),
					'options'		=> $this->get_priorities(),
					'type'			=> 'tax',
					'tax'			=> 'ticket_priority',
					'field_active_opt'	=> 'support_priority',
					'field_view'		=> 'checkbox_dropdown'
				);
			}
			
			
				
				
			$profile_fields['users'] = array(
				'field_label'		=> __( 'Users', 'wpas_productivity' ),
				'all_label'		=> __( 'All Users', 'wpas_productivity' ),
				'selected_label'	=> __( 'Selected Users', 'wpas_productivity' ),
				'options'		=> $this->get_users(),
				'filter'		=> array(
				    'type'		=> 'field',
				    'key'		=> 'post_author'
				),
				'field_view'		=> 'select2_dropdown',
				'get_item_cb'		=> 'get_user',
				'data_attr'		=> array( 'capability' => 'create_ticket' )
			);
				
			$profile_fields['user_groups'] = array(
				'field_label'		=> __( 'User Groups', 'wpas_productivity' ),
				'all_label'		=> __( 'All User Groups', 'wpas_productivity' ),
				'selected_label'	=> __( 'Selected User Groups', 'wpas_productivity' ),
				'options'		=> $this->get_user_groups(), 
				'tax'			=> 'as_user_group',
				'type'			=> 'tax',
				'filter'		=> array(
				    'join_field'	=> 'post_author',
				    'join_name'		=> 'pf_ug_tr'
				),
				'field_view'		=> 'checkbox_dropdown'
			);
			
			$profile_fields['status'] = array(
				'field_label'		=> __( 'Status', 'wpas_productivity' ),
				'all_label'		=> __( 'All Status', 'wpas_productivity' ),
				'selected_label'	=> __( 'Selected Status', 'wpas_productivity' ),
				'options'		=> $this->get_statuses(),
				'filter'		=> array(
				    'type'		=> 'field',
				    'key'		=> 'post_status',
				    'data_type'		=> 'string',
				    'compare'		=> 'IN'
				),
				'field_view'		=> 'checkbox_dropdown'
			);
			
			$profile_fields['states'] = array(
				'field_label'		=> __( 'States', 'wpas_productivity' ),
				'all_label'		=> __( 'All States', 'wpas_productivity' ),
				'selected_label'	=> __( 'Selected States', 'wpas_productivity' ),
				'options'		=> $this->get_states(),
				'filter'		=> array(
				    'type'		=> 'meta',
				    'key'		=> '_wpas_status',
				    'data_type'		=> 'string',
				    'compare'		=> 'IN',
				),
				'field_view'		=> 'checkbox_dropdown'
			);
			
			$profile_fields['primary_agents'] = array(
				'field_label'		=> __( 'Primary Agents', 'wpas_productivity' ),
				'all_label'		=> __( 'All Primary Agents', 'wpas_productivity' ),
				'selected_label'	=> __( 'Selected Primary Agents', 'wpas_productivity' ),
				'options'		=> $agents,
				'filter'		=> array(
				    'type'		=> 'meta',
				    'key'		=> '_wpas_assignee',
				    'compare'		=> 'IN'
				),
				'field_view'		=> 'select2_dropdown',
				'get_item_cb'		=> 'get_user',
				'data_attr'		=> array( 'capability' => 'edit_ticket' )
			);
				
			
			
			
			if( wpas_is_multi_agent_active() ) {
				
				$profile_fields['secondary_agents'] = array(
					'field_label'		=> __( 'Secondary Agents', 'wpas_productivity' ),
					'all_label'		=> __( 'All Secondary Agents', 'wpas_productivity' ),
					'selected_label'	=> __( 'Selected Secondary Agents', 'wpas_productivity' ),
					'options'		=> $agents,
					'filter'		=> array(
					    'type'		=> 'meta',
					    'key'		=> '_wpas_secondary_assignee',
					    'compare'		=> 'IN'
					),
					'field_view'		=> 'select2_dropdown',
					'get_item_cb'		=> 'get_user',
					'data_attr'		=> array( 'capability' => 'edit_ticket' )
				);
				
				
				$profile_fields['tertiary_agents'] = array(
					'field_label'		=> __( 'Tertiary Agents', 'wpas_productivity' ),
					'all_label'		=> __( 'All Tertiary Agents', 'wpas_productivity' ),
					'selected_label'	=> __( 'Selected Tertiary Agents', 'wpas_productivity' ),
					'options'		=> $agents,
					'filter'		=> array(
					    'type'		=> 'meta',
					    'key'		=> '_wpas_tertiary_assignee',
					    'compare'		=> 'IN'
					),
					'field_view'		=> 'select2_dropdown',
					'get_item_cb'		=> 'get_user',
					'data_attr'		=> array( 'capability' => 'edit_ticket' )
				);
			}
			
			$this->profile_fields = $profile_fields;
			
		}
		
		return $this->profile_fields;
	}
	
	/**
	 * Register metaboxes for security profile
	 */
	function add_meta_boxes() {
		add_meta_box( 'wpass_profile_meta_box_settings', __( 'Settings', 'wpas_productivity' ), array( $this, 'meta_box_settings' ), 'as_security_profile', 'normal', 'high' );
	}
	
	/**
	 * Save security profile data
	 * 
	 * @param int $post_id
	 * @return
	 */
	public function save_profile( $post_id ) {
		
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		
		
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		
		if ( ! ( isset( $_POST['wpas_pf_profile'] ) && wp_verify_nonce( $_POST['wpas_pf_profile'], 'wpas_pf_save_profile' ) ) ) {
			return;
		}
		

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		
		$post = get_post( $post_id );
		
		if ( 'as_security_profile' == $_POST['post_type'] && $post->post_type == 'as_security_profile' ) {
			
			$just_my_tickets = isset( $_POST['pf_just_my_tickets'] ) && 'yes' === $_POST['pf_just_my_tickets'] ? 'yes' : '';
			$all_tickets = isset( $_POST['pf_all_tickets'] ) && 'yes' === $_POST['pf_all_tickets'] ? 'yes' : '';
			
			$relations = array( 'or', 'and' );
			
			$match_relation = isset( $_POST['pf_match_relation'] ) && in_array( $_POST['pf_match_relation'], $relations ) ? $_POST['pf_match_relation'] : '';
			
			
			update_post_meta( $post_id, 'pf_just_my_tickets', $just_my_tickets );
			update_post_meta( $post_id, 'pf_all_tickets', $all_tickets );
			update_post_meta( $post_id, 'pf_match_relation', $match_relation );
			
			
			$selection_types = array('all', 'selected');
			
			$clear_fields = 'yes' === $just_my_tickets || 'yes' === $all_tickets ? true : false;
			
			$fields = $this->get_profile_fields();
			foreach( $fields as $f_name => $f ) {
				$key = "pf_{$f_name}";
				$selection_type_key = "{$key}_selection_type";
				
				if( true === $clear_fields ) {
					$selection_type = '';
					$value = array();
				} else {
					
					$selection_type = filter_input( INPUT_POST, $selection_type_key );
					$value = filter_input( INPUT_POST, $key, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
					
					$selection_type = in_array( $selection_type, $selection_types ) ? $selection_type : '';
					$value = 'all' === $selection_type ? array() : $value;
					
				}
				
				update_post_meta( $post_id, $selection_type_key , $selection_type );
				update_post_meta( $post_id, $key , $value );
			}
			
			
			
		}
	}
	
	/**
	 * Security profile settings meta box
	 * 
	 * @global int $post_id
	 */
	function meta_box_settings() {
		global $post_id;
		
		$fields = $this->get_profile_fields();
		
		
		wp_nonce_field( 'wpas_pf_save_profile', 'wpas_pf_profile' );
		
		$just_my_tickets = get_post_meta( $post_id, 'pf_just_my_tickets', true );
		$all_tickets = get_post_meta( $post_id, 'pf_all_tickets', true );
		
		$match_relation = get_post_meta( $post_id, 'pf_match_relation', true );
		?>

		<script type="text/javascript">
		jQuery(function() {
			jQuery('input#publish[name="publish"]').val('Save');
		});
		</script>
		<div class="wpas_pf_profile_criteria_fields">
			<div class="wpas_pf_profile_main_criteria_fields">
				<div class="option"><label><input type="checkbox" name="pf_just_my_tickets"<?php echo ( ('yes' === $just_my_tickets) ? ' checked="checked"' : '' ) ?> value="yes" /><?php _e( 'Just My Tickets', 'wpas_productivity' ); ?></label></div>
				<div class="option"><label><input type="checkbox" name="pf_all_tickets"<?php echo ( ('yes' === $all_tickets) ? ' checked="checked"' : '' ) ?> value="yes" /><?php _e( 'All Tickets', 'wpas_productivity' ); ?></label></div>
			</div>

			
			
			<div class="wpas_pf_profile_criteria_field">
					<div class="field_heading"><?php _e( 'Match Relation', 'wpas_productivity' ); ?></div>
					<div class="inner">
						<div class="select_field_type">
							<div class="option"><label><input type="radio" name="pf_match_relation"<?php echo ( ('or' === $match_relation) ? ' checked="checked"' : '' ) ?> value="or"><?php _e( 'OR', 'wpas_productivity' ); ?></label></div>
							<div class="option"><label><input type="radio" name="pf_match_relation"<?php echo ( ('and' === $match_relation) ? ' checked="checked"' : '' ) ?> value="and"><?php _e( 'AND', 'wpas_productivity' ); ?></label></div>
						</div>
					</div>
			</div>
			
			
			<?php foreach( $fields as $f_name => $f ) {


				$name = "pf_{$f_name}";
				$selection_type_key = "{$name}_selection_type";

				$value = get_post_meta( $post_id, $name, true );
				$selection_type = get_post_meta( $post_id, $selection_type_key, true );

				?>

				<div class="wpas_pf_profile_criteria_field">
					<div class="field_heading"><?php echo $f['field_label'] ?></div>
					<div class="inner">
						<div class="select_field_type">
							<div class="option"><label><input type="radio"<?php echo ( ('all' === $selection_type) ? ' checked="checked"' : '' ) ?> name="<?php echo $selection_type_key; ?>" value="all" /><?php echo $f['all_label'] ?></label></div>
							<div class="option"><label><input type="radio"<?php echo ( ('selected' === $selection_type) ? ' checked="checked"' : '' ) ?> name="<?php echo $selection_type_key; ?>" value="selected" /><?php echo $f['selected_label'] ?></label></div>
						</div>
						<div class="options checkbox_dropdown" style="<?php echo ( ( 'selected' === $selection_type) ? 'display:block;' : '' ) ?>">
						<?php  
						if( isset( $f['options'] ) && is_array( $f['options'] ) ) :
							
							$view_callback = isset( $f['field_view'] ) ? $f['field_view'] : 'checkbox_dropdown';
							$item_cb = isset( $f['get_item_cb'] ) ? $f['get_item_cb'] : '';
							$data_attr = isset( $f['data_attr'] ) ? $f['data_attr'] : array();
							
							$field_args = array( 
								'name' => $name, 
								'selected' => $value, 
								'item_cb' => $item_cb, 
								'data_attr' => $data_attr 
								);
							
							echo call_user_func_array( array( $this, $view_callback ) , array( $f['options'], $field_args ) );
						endif;
						?>
						</div>
					</div>
				</div>


			<?php } ?>

		</div>
		<?php
	}
	
	/**
	 * Return all security profiles
	 * 
	 * @return type
	 */
	public function get_profiles() {
		
		$profiles = get_posts( array( 'post_type' => 'as_security_profile', 'numberposts' => -1 ) );
		
		return $profiles;
	}
	
	/**
	 * Generate security profiles dropdown
	 * 
	 * @param int $selected
	 * 
	 * @return string
	 */
	public function profiles_dropdown( $selected = '' ) {
		$profiles = $this->get_profiles();
		
		$dropdown = '<select name="wpas_pf_profile">';
		$dropdown .= '<option value="">Select a profile...</option>';
		foreach( $profiles as $profile ) {
			$_selected = $selected == $profile->ID ? ' selected="selected"' : '';
			$dropdown .= '<option value="' . $profile->ID . '"' . $_selected . '>' . $profile->post_title . '</option>';
		}
		
		$dropdown .= '</select>';
		
		return $dropdown;
	}
	
	/**
	 * Return all agents
	 * 
	 * @return array
	 */
	private function get_agents() {
		$users = array();
		
		$_users = wpas_get_users( array( 'cap' => 'edit_ticket' ) );
		
		foreach( $_users->members as $user ) {
			$users[ $user->ID ] = $user->display_name;
		}
		
		return $users;
		
	}
}