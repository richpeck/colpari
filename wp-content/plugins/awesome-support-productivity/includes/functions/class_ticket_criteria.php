<?php

/**
 * Handle ticket criteria features
 */
class WPAS_PF_Ticket_Criteria extends WPAS_PF_User_Info {
	
	protected static $instance = null;
	
	/**
	 * Unique meta key
	 *
	 * @var string 
	 */
	protected $key = 'ticket_filter_options';
	
	
	
	public function __construct() {
		
		add_action( 'wp_ajax_pf_save_ticket_filter_options',		array( $this, 'save_criteria'       ) ); // Save filters
		add_action( 'wp_ajax_wpas_pf_load_saved_filter',		array( $this, 'get_saved_filter_url' ) );
		
		add_action('wp_ajax_pf_listing_win_ticket_filter_options',	array( $this, 'filters_listing' ) );
		
		add_action( 'wp_ajax_pf_delete_ticket_filter_options',		array( $this, 'delete_filter'    ) ); // Delete saved filter.
		
		add_filter( 'wpas_admin_tabs_tickets_tablenav',			array( $this, 'register_tabs'), 12, 1 ); // Register tab
		add_filter( 'wpas_admin_tabs_tickets_tablenav_saved_filters_content', array( $this, 'saved_filters_tab_content' ) ); // Add tab content
		
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
	
	/**
	 * Add content into saved filters tab
	 * 
	 * @return string
	 */
	public function saved_filters_tab_content() {
		ob_start();
		
		$this->add_save_filter_form( 'top' );
		return ob_get_clean();
	}
	
	/**
	 * Add saved filters tab
	 * 
	 * @param array $tabs
	 * 
	 * @return array
	 */
	public function register_tabs( $tabs ) {
		$tabs['saved_filters'] = __( 'Saved Filters', 'wpas_productivity' );
		
		return $tabs;
	}
	
	/**
	 * Generate save filter button
	 */
	public function saveItemButton() {
			
		$window_title = __( 'Save Filter', 'wpas_productivity' );
		$width = 600;
		$height = 450;
			
		$action_name = $this->actionName( 'save' );
		$window_id = "wpas_{$action_name}_wrapper";
		
		printf( '<a href="#TB_inline?width=%d&height=%d&inlineId=%s" class="wpas_pf_search_criteria_win_btn" title="%s">%s</a>', $width, $height, $window_id, $window_title, $window_title );
	}
	
	/**
	 * Generate delete filters window button
	 */
	public function deleteItemButton() {
		
		$window_title = __( 'Delete Filter', 'wpas_productivity' );
		
		$edit_view_link = add_query_arg( array( 
		    'action' => $this->actionName( 'listing_win' ),
		    'width' => 600,
		    'height' => 'auto'
		    ), admin_url( 'admin-ajax.php' ) 
		);
		
		printf( '<a href="%s" style="margin: 0 10px;" class="wpas_pf_search_criteria_listing_win_btn" title="%s">%s</a>', $edit_view_link, $window_title, $window_title );
		
	}
	
	/**
	 * Filter listing view to delete saved filters
	 */
	public function filters_listing() {
		
		$this->data_user = get_current_user_id();
		
		
		$items = $this->getList();
		$id = "wpas_pf_{$this->key}_items";
		
		echo '<div id="wpas_pf_ui_section_'.$this->key.'" data-section="' . $this->key . '" class="wpas_pf_ui_wrapper" data-duid="' . $this->data_user . '">';
		?>
		
		<div id="<?php echo $id; ?>" class="wpas_pf_data_items">
			<div class="no_item_msg"><?php echo _e( "No saved filter  exist.", 'wpas_productivity' ); ?></div>
			<?php include $this->itemsTemplate(); ?>
		</div>

		<?php
		echo '</div>';
		
		die();
	}
	
	
	/**
	 * Handle delete filter request
	 */
	public function delete_filter() {
		
		$error = "";
		$success = false;
		
		$action = $this->actionName( 'delete' );
		
		$nonce = $this->nonce( $action );
		$item_id =  filter_input( INPUT_POST, 'id'  );
		$this->data_user = (int) filter_input( INPUT_POST, 'duid'  );
		
		
		if( !check_ajax_referer( $nonce['action'], $nonce['name'], false ) ) {
			
			$error = __( 'Sorry, you are not allowed to remove saved filters.', 'wpas_productivity' );
			
		} elseif( !$this->get_data_user() ) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_productivity' );
			
		} elseif( ! $this->item_exist ( $item_id ) ) {
			
			$error = __( 'Filter doesn\'t exist.', 'wpas_productivity' );
			
		} else {
			if( $this->delete( $item_id ) ) {
				$success = true;
			}
		}
		
		
		
		if( true === $success ) {
			wp_send_json_success( array( 'msg' => __( 'Filter delated', 'wpas_productivity' ), 'item_id' => $item_id ) );
		} elseif( !empty( $error ) ) {
			wp_send_json_error( array( 'msg' => $error ) );
		}
		die();
	}
	
	/**
	 * Print saved filters dropdown
	 * 
	 * @param string $post_type
	 * @param string $which
	 */
	public function saved_filters_dropdown( $post_type, $which ) {
		
		$this->data_user = get_current_user_id();
		
		
		if ( 'ticket' !== $post_type || 'top' !== $which ) {
			return;
		}
		
		$this->items_dropdown( $this->data_user );
		
	}
	
	/**
	 * Add button to launch save filter window
	 * 
	 * @param string $post_type
	 * @param string $which
	 * 
	 */
	public function add_save_filter_button( $post_type, $which ) {
		
		$this->data_user = get_current_user_id();
		
		
		if ( 'ticket' !== $post_type || 'top' !== $which ) {
			return;
		}
		
		
		echo '<span class="wpas_pf_tb_button" style="line-height: 28px; margin: 0 10px;">';
			$this->saveItemButton();
			$this->deleteItemButton();
		echo '</span>';
		
	}
	
	/**
	 * @global string $post_type
	 * 
	 * @param string $which
	 * 
	 */
	public function add_save_filter_form( $which ) {
		global $post_type;
		
		if ( 'ticket' !== $post_type || 'top' !== $which ) {
			return;
		}
		
		$this->data_user = get_current_user_id();
		
		add_thickbox();
		
		echo '<div class="clear clearfix"></div>';
		
		
		echo '<div id="wpas_pf_ui_section_'.$this->key.'" data-section="' . $this->key . '" class="wpas_pf_ui_wrapper" data-duid="' . $this->data_user . '">';
			$this->saved_filters_dropdown( $post_type, $which );
			$this->add_save_filter_button( $post_type, $which );
		
			$this->form( array(
				'type'		=> 'save',
				'submit_text'	=> __( 'Save Ticket Criteria', 'wpas_productivity' ),
				'hidden'	=> true,
				'template' => 'save'
			));
			
		echo '</div>';
		
		
	}
	
	/**
	 * Return url of saved filters via ajax
	 */
	public function get_saved_filter_url() {
		
		$this->data_user = get_current_user_id();
		$item_id = filter_input( INPUT_POST, 'id' );
		
		$link = "";
		
		if( $item_id ) {
			$item = $this->get_item($item_id);
			if( $item ) {
				$query_args = array( 'post_type' => 'ticket' );
				
				if( isset( $item['criteria'] ) && is_array( $item['criteria'] ) && !empty( $item['criteria'] ) ) {
					$query_args = array_merge( $query_args , $item['criteria'] );
				}
				$link = add_query_arg( $query_args , admin_url( 'edit.php' ) );
				
			}
		}
		
		if( $link ) {
			wp_send_json_success( array( 'location' => $link ) );
		} else {
			wp_send_json_error();
		}
		die();
	}
	
	
	/**
	 * Print dropdown of saved filters
	 * 
	 * @param int $user_id
	 * 
	 */
	public function items_dropdown( $user_id = 0 ) {
		
		$items = $this->getList( $user_id );
		
		
		$dropdown = "<select class=\"pf_saved_filters_dropdown\" data-name=\"pf_saved_filters\">";
		
		$dropdown .= sprintf('<option value="">%s</option>', __( 'Load a saved search', 'wpas_productivity' ) );
		foreach ( $items as $item_id => $item ) {
			$dropdown .= "<option value=\"{$item_id}\">{$item['name']}</option>";
		}
		$dropdown .= "</select>";
		
		echo $dropdown;
	}
	
	
	/**
	 * Return a list of filter fields
	 * 
	 * @return array
	 */
	public function filter_fields() {
		
		
		$custom_fields = WPAS()->custom_fields->get_custom_fields();
		
		
		$filter_fields = array(
		    's',
		    'post_status',
		    'author',
		    'id',
		    'm',
		    'orderby',
		    'order'
		);
		
		
		foreach ( $custom_fields as $f_name => $field_args ) {

			$field = new WPAS_Custom_Field( $f_name, $field_args );
			
			if( isset( $field->field_args['filterable'] ) && $field->field_args['filterable'] && !in_array( $f_name, $filter_fields ) ) {
				$filter_fields[] = $f_name;
			}
				
		}
		
		return apply_filters( 'wpas_ticket_search_criteria_fields',  $filter_fields );
		
	}
	
	/**
	 * Prepare user input filter fields to store in database
	 * 
	 * @param array $input
	 * 
	 * @return array
	 */
	public function prepare_criteria_to_store( $input = array() ) {
		
		$criteria_fields = array_intersect( array_keys( $input ), $this->filter_fields() ); // Return only criteria filter fields
		
		$criteria = array();
		
		foreach( $input as $c_name => $c_value ) {
			
			if( !in_array( $c_name, $criteria_fields ) || $c_value == '' || $c_value == '0' ) { // skip fields with empty values
				continue;
			}
			
			if( ( 'post_status' === $c_name || 'status' === $c_name ) && 'any' === $c_value )  {
				continue;
			}
			
			$criteria[ $c_name ] = $c_value;
		}
		
		return $criteria;
	}
	
	/**
	 * Check if filter options are already saved by user
	 * 
	 * @param array $criteria
	 * 
	 * @return boolean
	 */
	public function filter_options_already_saved( $criteria = array() ) {
		$items = $this->getList();
		
		$encoded_criteria = json_encode( $criteria );
		
		$exists = false;
		
		foreach( $items as $item ) {
			
			if( isset( $item['criteria'] ) && $encoded_criteria === json_encode( $item['criteria'] ) ) {
				$exists = true;
				break;
			}
			
		}
		
		return $exists;
	}
	
	/**
	 * Handle save filters request
	 */
	public function save_criteria() {
		
		$error = "";
		
		$action = $this->actionName( 'save' );
		$nonce = $this->nonce( $action );
		
		$name = filter_input( INPUT_POST, 'name' );
		$this->data_user = filter_input( INPUT_POST, 'duid'  );
		
		
		$criteria = $this->prepare_criteria_to_store( filter_input( INPUT_POST, 'criteria', FILTER_DEFAULT , FILTER_REQUIRE_ARRAY ) );
		
		
		if( ! check_ajax_referer( $nonce['action'], $nonce['name'], false ) ) {
			
			$error = __( 'Sorry, you are not allowed to add filter criteria.', 'wpas_productivity' );
			
		} elseif( !$this->get_data_user() ) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_productivity' );
			
		} elseif( empty( trim( $name ) ) ) {
			
			$error = __( 'Name is required.', 'wpas_productivity' );
			
		} elseif( empty( $criteria ) ) {
			
			$error = __( 'No filter is set.', 'wpas_productivity' );
			
		} elseif( $this->filter_options_already_saved( $criteria ) ) {
			
			$error = __( 'A search is already saved with selected filters.', 'wpas_productivity' );
			
		}
		
		
		if( $error ) {
			wp_send_json_error( array( 'msg' => $error ) );
		} else {
			
			$item = array( 
				'name' => sanitize_text_field( $name ), 
				'criteria' => $criteria
			);

			$item = $this->add( $item );
			$item_id = $item['id'];

			
			$item_html = "<option value=\"{$item_id}\">{$name}</option>";

			wp_send_json_success( array( 'msg' => __( 'Filter options are saved', 'wpas_productivity' ), 'item' => $item_html ) );
			
		}
		
		die();
	}
	
	
	
}