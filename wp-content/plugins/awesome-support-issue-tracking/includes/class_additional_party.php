<?php


class WPAS_IT_Additional_party extends WPAS_IT_Post_Meta {
	
	protected static $instance = null;
	
	/**
	 * Unique meta key
	 *
	 * @var string 
	 */
	protected $key = 'ai_parties';
	
	
	public function __construct() {
		global $post;
		
		add_action( 'wp_ajax_it_add_ai_parties',		array( $this, 'add_party'       ) ); // Add new party.
		add_action( 'wp_ajax_it_edit_ai_parties',		array( $this, 'edit_party'      ) ); // Edit existing party.
		add_action( 'wp_ajax_it_delete_ai_parties',		array( $this, 'delete_party'    ) ); // Delete party.
		add_action( 'wp_ajax_it_active_ai_parties',		array( $this, 'activate_party'  ) ); // Activate email to receive notifications.
		
		add_action( 'wp_ajax_it_edit_win_ai_parties',	array( $this, 'edit_form'	   ) ); // Generate edit additional interested party form
		
		
		if( $post ) {
			$this->set_post( $post->ID );
		}
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
	 * Set issue id
	 * 
	 * @param int $post_id
	 */
	public function set_post( $post_id ) {
		
		$this->post_id = null;
		
		if( $post_id && 'wpas_issue_tracking' === get_post_type( $post_id ) ) {
			$this->post_id = $post_id;
		}
		
		$this->issue_id = $this->post_id;
	}
	
	/**
	 * Check if user have access to issues
	 * 
	 * @param int $user_id
	 * 
	 * @return boolean
	 */
	public function have_access( $user_id = 0 ) {
		
		$user_id = $this->get_user( $user_id );
		$user = get_user_by( 'id', $user_id );
		
		if( $user->has_cap( 'edit_ticket' ) ) {
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * Check if we should display metabox
	 * 
	 * @return boolean
	 */
	public static function should_display() {
		
		$_this = self::get_instance();
		
		if( $_this->have_access() ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Check if user have capability to add additional interested party
	 * 
	 * @param int $user_id
	 * 
	 * @return boolean
	 */
	public function user_can_add( $user_id = 0 ) {
		
		$user_id = $this->get_user( $user_id );
		
		if( $this->have_access( $user_id ) ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Generate add new item button
	 */
	public function addItemButton() {
			
		$window_title = __( 'Add Additional Interested Party', 'wpas_it' );
		$width = 600;
		$height = 450;
			
		$action_name = $this->actionName( 'add' );
		$window_id = "wpas_{$action_name}_wrapper";
		
		echo '<div class="wpas_it_tb_button">';
			printf( '<a href="#TB_inline?width=%d&height=%d&inlineId=%s" class="button button-primary" title="%s">%s</a>', $width, $height, $window_id, $window_title, $window_title );
		echo '</div>';
		echo '<div class="clear clearfix"></div>';
	}
	
	
	/**
	 * Load whole view
	 * 
	 * @param int $post_id
	 */
	public function display( $post_id = 0 ) {
		
		$this->set_post( $post_id );
		
		
		$user_can_add = $this->user_can_add();
		
		
		echo '<div id="wpas_it_ui_section_'.$this->key.'" data-section="' . $this->key . '" class="wpas_it_ui_wrapper" data-duid="' . $this->get_post_id( $post_id ) . '">';
		
		if( $user_can_add ) {
			$this->addItemButton();
		}
			
		$this->items_listing() ;
		
		if( $user_can_add ) {
			
			$this->form( array(
				'type'		=> 'add',
				'submit_text'	=> __( 'Add Additional Interested Parties', 'wpas_it' ),
				'hidden'	=> true
			));
		}
		echo '</div>';
	}
	
	
	/**
	 * Load items view
	 */
	public function items_listing() {
		
		$items = $this->getList();
		
		
		$id = "wpas_it_{$this->key}_items";
		?>
		
		<div id="<?php echo $id; ?>" class="wpas_it_data_items">
			<div class="no_item_msg"><?php echo _e( "No party exist.", 'wpas_it' ); ?></div>
			<?php include $this->itemsTemplate(); ?>
		</div>

		<?php
	}
	
	
	/**
	 * Generate edit additional interested party form
	 */
	public function edit_form() {
		
		$id = filter_input( INPUT_GET, 'id' );
		$post_id = filter_input( INPUT_GET, 'duid' );
		
		$this->set_post( $post_id );
		
		$list = $this->getList();
		
		$item = $list[ $id ];
		$item['id'] = $id;
		
		$this->form( array(
			'type'		=> 'edit',
			'submit_text'	=> __( 'Save Additional Interested Party', 'wpas_it' ),
			'hidden'	=> false,
			'template'	=> 'add',
			'data'		=> $item
		) );
		
		die();
	}
	
	/**
	 * Check if additional interested party exists in issue
	 * 
	 * @param int $party
	 * 
	 * @return boolean
	 */
	public function issue_has_party( $party ) {
		
		$items = $this->getList();
		
		$parties = array();
		
		foreach( $items  as $item ) {
			if( is_array( $item ) && isset( $item['user_id'] ) ) {
				$parties[] = $item['user_id'];
			}
		}
		
		if( in_array( $party, $parties ) ) {
			return true;
		}
		
		return false;
		
	}
	
	/**
	 * Validate add/edit party forms
	 * 
	 * @param string $type
	 * 
	 * @return array
	 */
	protected function validate( $type ) {
		
		$error = "";
		$result = array();
		
		$party = filter_input( INPUT_POST, 'wpas_it_ai_party'  );
		
		$email   = filter_input( INPUT_POST, 'email'  );
		$name   = filter_input( INPUT_POST, 'name'  );
		$active  = filter_input( INPUT_POST, 'active' );
		$item_id = filter_input( INPUT_POST, 'id'     );
		
		$action = $this->actionName( $type );
		$nonce = $this->nonce( $action );
		
		if( ! wpas_it_ajax_nonce_check( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add() ) {
			
			$error = __( 'Sorry, you are not allowed to add additional interested party.', 'wpas_it' );
			
		} elseif( empty( trim( $email ) ) || !is_email( $email ) ) {
			
			$error = __( 'Please provide valid email address.', 'wpas_it' );
			
		} 
		elseif( 'edit' === $type && !$this->item_exist( $item_id ) ) {
			
			$error = __( 'Party doesn\'t exist.', 'wpas_it' );
			
		}
		elseif( 'add' === $type && $this->issue_has_party( $party ) ) {
			
			$error = __( 'Party already exist in this issue.', 'wpas_it' );
			
		} elseif( !$this->get_post_id() ) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_it' );
			
		}
		
		
		if( $error ) {
			$result['error'] = $error;
		} else {
			$result['item'] = array( 
				'email' => $email, 
				'name'	=> $name,
				'active' => $active ? true : false
			);
			
			if( 'edit' === $type ) {
				$result['item_id'] = $item_id;
			}
		}
		
		
		return $result;
	}
	
	
	
	/**
	 * Handle add party request
	 */
	public function add_party() {
		
		$post_id = filter_input( INPUT_POST, 'duid' );
		
		$this->set_post( $post_id );
		
		$result = $this->validate( 'add' );
		
		if( isset( $result['error'] ) && !empty( $result['error'] ) ) {
			wp_send_json_error( array( 'msg' => $result['error'] ) );
		} else {
			
			$item = $this->add( $result['item'] );
			$item_id = $item['id'];

			ob_start();
			include $this->itemTemplate();
			$item_html = ob_get_clean();

			wp_send_json_success( array( 'msg' => __( 'Party added.', 'wpas_it' ), 'item' => $item_html ) );
			
		}
		
		die();
	}
	
	
	/**
	 * Handle edit additional interested party request
	 */
	public function edit_party() {
		
		$post_id = filter_input( INPUT_POST, 'duid' );
		
		$this->set_post( $post_id );
		
		$result = $this->validate( 'edit' );
		
		if( isset( $result['error'] ) && !empty( $result['error'] ) ) {
			wp_send_json_error( array( 'msg' => $result['error'] ) );
		} else {
			
			$item_id = $result['item_id'];
			$item = $result['item'];
			
			$this->update( $item, $item_id );
			
			ob_start();
			include $this->itemTemplate();
			$item_html = ob_get_clean();
			wp_send_json_success( array( 'msg' => __( 'Additional interested party saved', 'wpas_it' ), 'update_item' => $item_html, 'item_id' => $item_id ) );
			
		}
		
		die();
		
	}
	
	
	/**
	 * Activate additional interested party to receive notifications
	 */
	public function activate_party() {
		
		$post_id = filter_input( INPUT_POST, 'duid' );
		$this->set_post( $post_id );
		
		$item_id =  filter_input( INPUT_POST, 'id'     );
		$active  =  filter_input( INPUT_POST, 'active' );
		
		$action = $this->actionName( 'active' );
		$nonce = $this->nonce( $action );
		
		$error = "";
		
		if( ! wpas_it_ajax_nonce_check( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add() ) {
			
			$error = __( 'Sorry, you are not allowed to add add additional interested party.', 'wpas_it' );
			
		} elseif( !$this->get_post_id() || !$this->item_exist( $item_id )) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_it' );
			
		} 
		
		if( $error ) {
			wp_send_json_error( array( 'msg' => $error ) );
		} else {
			
			$item = $this->get_item( $item_id );
			
			$item['active'] = ($active ? true : false);
			
			$this->update( $item, $item_id );
			
			ob_start();
			include $this->itemTemplate();
			$item_html = ob_get_clean();
			wp_send_json_success( array( 'msg' => __( 'Additional interested party saved', 'wpas_it' ), 'update_item' => $item_html, 'item_id' => $item_id ) );
		}
		
		die();
	}
	
	
	/**
	 * Handle delete party request
	 */
	public function delete_party() {
		
		$error = "";
		$success = false;
		
		$action = $this->actionName( 'delete' );
		$nonce = $this->nonce( $action );
		
		$post_id = filter_input( INPUT_POST, 'duid' );
		$item_id =  filter_input( INPUT_POST, 'id'  );
		
		$this->set_post( $post_id );
		
		
		if( !wpas_it_ajax_nonce_check( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add() ) {
			
			$error = __( 'Sorry, you are not allowed to remove party.', 'wpas_it' );
			
		} elseif( !$this->get_post_id() ) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_it' );
			
		} elseif( ! $this->item_exist ( $item_id ) ) {
			
			$error = __( 'Party doesn\'t exist.', 'wpas_it' );
			
		} else {
			
			$item = $this->get_item( $item_id );
			
			if( $this->delete( $item_id ) ) {
				
				$success = true;
			}
		}
		
		
		
		if( true === $success ) {
			wp_send_json_success( array( 'msg' => __( 'Party deleted', 'wpas_it' ), 'item_id' => $item_id ) );
		} elseif( !empty( $error ) ) {
			wp_send_json_error( array( 'msg' => $error ) );
		}
		die();
		
	}
	
}