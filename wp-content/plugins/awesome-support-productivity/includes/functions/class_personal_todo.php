<?php


class WPAS_PF_Personal_Todo extends WPAS_PF_User_Info {
	
	protected static $instance = null;
	
	/**
	 * Unique meta key
	 *
	 * @var string 
	 */
	protected $key = 'personal_todos';
	
	/**
	 * Todo statuses array
	 * 
	 * @var array
	 */
	protected $statuses = array();
	
	
	public function __construct() {
		
		add_action( 'wp_ajax_pf_add_personal_todos',	    array( $this, 'add_todo'       ) ); // Add new personal todo.
		add_action( 'wp_ajax_pf_edit_personal_todos',	    array( $this, 'edit_todo'      ) ); // Edit existing personal todo.
		add_action( 'wp_ajax_pf_delete_personal_todos',	    array( $this, 'delete_todo'    ) ); // Delete personal todo.
		add_action( 'wp_ajax_pf_duplicate_personal_todos',  array( $this, 'duplicate_todo' ) ); // Duplicate new personal todo.
		add_action( 'wp_ajax_pf_completed_personal_todos',  array( $this, 'mark_completed' ) ); // Duplicate new personal todo.
		
		
		
		add_action( 'wp_ajax_pf_edit_win_personal_todos',   array( $this, 'edit_form'	   ) ); // Generate edit todo form
		add_filter( 'wpas_pf_general_setting_options',      array( $this, 'add_setting' ), 10, 1 ); // Add new setting under Tickets -> Settings page
		
		add_action( 'edit_user_profile',		    array( $this, 'user_profile_todos' ) , 10, 1 ); // Display todos on user profile page
		add_action( 'show_user_profile',		    array( $this, 'user_profile_todos' ) , 10, 1 ); // Display todos on user profile page
		
		add_action( 'admin_enqueue_scripts',		    array( $this, 'enqueue_scripts') );

		
		$this->statuses = array(
			'not_started'	=> __( 'Not Started', 'wpas_productivity' ),
			'in_process'	=> __( 'In Process',  'wpas_productivity' ),
			'hold'		=> __( 'Hold',        'wpas_productivity' ),
			'completed'	=> __( 'Completed',   'wpas_productivity' )
		);
		
		$this->data_user = $this->get_user();
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
	 * 
	 * Add "Allow agents" setting
	 * 
	 * @param array $settings
	 * 
	 * @return array
	 */
	public function add_setting( $settings ) {
		
		$settings[] = array(
			'name'    => __( 'Allow Agents to Create Todo Lists', 'wpas_productivity' ),
			'id'      => 'pf_allow_personal_todo',
			'type'    => 'checkbox',
			'desc'    => __( 'Can an agent create todo lists?', 'wpas_productivity' ),
			'default' => true
		);
		
		return $settings;
	}
	
	/**
	 * Check if user have access to todo lists
	 * 
	 * @param int $user_id
	 * 
	 * @return boolean
	 */
	public function have_access( $user_id = 0 ) {
		
		$user_id = $this->get_user( $user_id );
		$user = get_user_by( 'id', $user_id );
		
		if( ( $user->has_cap( 'edit_ticket' ) && true === (bool)wpas_get_option( 'pf_allow_personal_todo' ) ) || $user->has_cap( 'administer_awesome_support' ) ) {
			return true;
		}
		
		
		
		return false;
	}
	
	/**
	 * Check if user have capability to add a todo
	 * 
	 * @param int $user_id
	 * @return boolean
	 */
	public function user_can_add_todo( $user_id = 0 ) {
		
		$user_id = $this->get_user( $user_id );
		
		if( $this->have_access( $user_id ) && $user_id === $this->data_user ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Display personal todos on user profile page
	 * 
	 * @param object $user
	 */
	public function user_profile_todos( $user ) {
		
		if( $this->have_access( $user->ID  ) ) :
			
			$this->data_user = $user->ID;
		?>
		<div id="wpas_pf_ui_sn_user_profile">
			<h3><?php  _e( 'Personal Todos', 'wpas_productivity' ); ?></h3>
			<?php $this->display(); ?>
		</div>
		<?php
		
		endif;
	}
	
	
	/**
	 * Generate add new item button
	 */
	public function addItemButton() {
			
		$window_title = __( 'Add personal todo', 'wpas_productivity' );
		$width = 600;
		$height = 450;
			
		$action_name = $this->actionName( 'add' );
		$window_id = "wpas_{$action_name}_wrapper";
		
		echo '<div class="wpas_pf_tb_button">';
			printf( '<a href="#TB_inline?width=%d&height=%d&inlineId=%s" class="button button-primary" title="%s">%s</a>', $width, $height, $window_id, $window_title, $window_title );
		echo '</div>';
		echo '<div class="clear clearfix"></div>';
	}
	
	/**
	 * Enqueue static resources
	 */
	public function enqueue_scripts() {
		add_thickbox();
		wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ) );
		wp_enqueue_style('jquery-ui-css', WPAS_PF_URL . 'assets/css/jquery-ui.min.css');
	}
	/**
	 * Load whole view
	 * 
	 * @param int $post_id
	 * @param int $user_id
	 */
	public function display( $post_id = 0 ) {
		
		$user_can_add = $this->user_can_add_todo();
		
		
		echo '<div id="wpas_pf_ui_section_'.$this->key.'" data-section="' . $this->key . '" class="wpas_pf_ui_wrapper" data-duid="' . $this->data_user . '">';
		
		if( $user_can_add && $this->data_user ) {
			$this->addItemButton();
		}
			
		$this->items_listing( $this->data_user ) ;
		
		if( $user_can_add && $this->data_user ) {
			$this->form( array(
				'type'		=> 'add',
				'submit_text'	=> __( 'Add Todo', 'wpas_productivity' ),
				'hidden'	=> true
			));
		}
		echo '</div>';
	}
	
	
	/**
	 * Load items view
	 * 
	 * @param int $user_id
	 */
	public function items_listing( $user_id = 0 ) {
		
		$items = $this->getList( $user_id );
		$id = "wpas_pf_{$this->key}_items";
		?>
		
		<div id="<?php echo $id; ?>" class="wpas_pf_data_items">
			<div class="no_item_msg"><?php echo _e( "No personal todo exist.", 'wpas_productivity' ); ?></div>
			<?php include $this->itemsTemplate(); ?>
		</div>

		<?php
	}
	
	/**
	 * Generate edit todo form
	 */
	public function edit_form() {
		
		//$this->data_user = filter_input( INPUT_GET, 'duid' );
		
		$list = $this->getList();
		
		$id = filter_input( INPUT_GET, 'id' );
		$item = $list[ $id ];
		$item['id'] = $id;
		
		$this->form( array(
			'type'		=> 'edit',
			'submit_text'	=> __( 'Save Todo', 'wpas_productivity' ),
			'hidden'	=> false,
			'template'	=> 'add',
			'data'		=> $item
		) );
		
		die();
	}
	
	/**
	 * Validate add/edit todo forms
	 * 
	 * @param string $type
	 * 
	 * @return array
	 */
	protected function validate( $type ) {
		
		$error = "";
		$result = array();
		
		$title = filter_input( INPUT_POST, 'title' );
		$body =  filter_input( INPUT_POST, 'body'  );
		$status =  filter_input( INPUT_POST, 'status'  );
		$date_due =  filter_input( INPUT_POST, 'date_due'  );
		$item_id =  filter_input( INPUT_POST, 'id'    );
		
		$action = $this->actionName( $type );
		$nonce = $this->nonce( $action );
		
		if( ! check_ajax_referer( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add_todo() ) {
			
			$error = __( 'Sorry, you are not allowed to add personal todos.', 'wpas_productivity' );
			
		} elseif( 'edit' === $type && ! $this->item_exist( $item_id ) ) {
			
			$error = __( 'Todo doesn\'t exist.', 'wpas_productivity' );
			
		} elseif( !$this->get_data_user() ) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_productivity' );
			
		} elseif( empty( trim( $title ) ) ) {
			
			$error = __( 'Title is required.', 'wpas_productivity' );
			
		} elseif( trim( empty( $body ) ) ) {
			
			$error = __( 'Body is required.', 'wpas_productivity' );
			
		} elseif( trim( empty( $status ) ) || !array_key_exists( $status, $this->statuses ) ) {
			
			$error = __( 'Select a status.', 'wpas_productivity' );
			
		}
		
		
		if( $error ) {
			$result['error'] = $error;
		} else {
			$result['item'] = array( 
				'title' => sanitize_text_field( $title ), 
				'body' => $body, 
				'status' => $status,
				'date_due' => $date_due,
				'time' => time()
			);
			
			if( 'edit' === $type ) {
				$item = $this->get_item( $item_id );
				$result['item_id'] = $item_id;
				$result['item']['time'] = $item['time'];
			}
		}
		
		
		return $result;
	}
	
	
	/**
	 * Handle add todo request
	 */
	public function add_todo() {
		
		$result = $this->validate( 'add' );
		
		if( isset( $result['error'] ) && !empty( $result['error'] ) ) {
			wp_send_json_error( array( 'msg' => $result['error'] ) );
		} else {
			

			$item = $this->add( $result['item'] );

			$item_id = $item['id'];

			ob_start();
			include $this->itemTemplate();
			$item_html = ob_get_clean();

			wp_send_json_success( array( 'msg' => __( 'Toto created', 'wpas_productivity' ), 'item' => $item_html ) );
			
		}
		
		die();
	}
	
	
	/**
	 * Handle edit todo request
	 */
	public function edit_todo() {
		
		
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
			wp_send_json_success( array( 'msg' => __( 'Todo Saved', 'wpas_productivity' ), 'update_item' => $item_html, 'item_id' => $item_id ) );
			
		}
		
		die();
		
	}
	
	/**
	 * Handle delete todo request
	 */
	public function delete_todo() {
		
		$error = "";
		$success = false;
		
		$action = $this->actionName( 'delete' );
		
		$nonce = $this->nonce( $action );
		$item_id =  filter_input( INPUT_POST, 'id'  );
		
		
		if( !check_ajax_referer( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add_todo() ) {
			
			$error = __( 'Sorry, you are not allowed to remove personal todos.', 'wpas_productivity' );
			
		} elseif( !$this->get_data_user() ) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_productivity' );
			
		} elseif( ! $this->item_exist ( $item_id ) ) {
			
			$error = __( 'Todo doesn\'t exist.', 'wpas_productivity' );
			
		} else {
			if( $this->delete( $item_id ) ) {
				$success = true;
			}
		}
		
		
		
		if( true === $success ) {
			wp_send_json_success( array( 'msg' => __( 'Todo delated', 'wpas_productivity' ), 'item_id' => $item_id ) );
		} elseif( !empty( $error ) ) {
			wp_send_json_error( array( 'msg' => $error ) );
		}
		die();
		
	}
	
	/**
	 * Handle duplicate todo request
	 */
	public function duplicate_todo() {
		
		$error = "";
		$success = false;
		
		$action = $this->actionName( 'duplicate' );
		
		$nonce = $this->nonce( $action );
		$id = filter_input( INPUT_POST, 'id' );
		
		$item = $this->get_item( $id );
		
		if( !check_ajax_referer( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add_todo() ) {
			$error = __( 'Sorry, you are not allowed to add personal todos.', 'wpas_productivity' );
		} elseif( !$this->get_data_user() ) {
			$error = __( 'Something went wrong, try again later.', 'wpas_productivity' );
		} elseif( false === $item ) {
			$error = __( 'Todo doesn\'t exist.', 'wpas_productivity' );
		} else {
			$item['time'] = time();
			$item = $this->add( $item );
			$item_id = $item['id'];
			$success = true;
		}
				
		
		if( $error ) {
			wp_send_json_error( array( 'msg' => $error ) );
		} elseif( $success ) {
			
			ob_start();
			include $this->itemTemplate();
			$item_html = ob_get_clean();

			wp_send_json_success( array( 'msg' => __( 'Todo duplicated', 'wpas_productivity' ), 'item' => $item_html ) );
		}
		
		die();
		
	}
	
	/**
	 * Mark a todo as completed
	 */
	public function mark_completed() {
		$error = "";
		$success = false;
		
		$action = $this->actionName( 'completed' );
		
		$nonce = $this->nonce( $action );
		$item_id = filter_input( INPUT_POST, 'id' );
		
		$item = $this->get_item( $item_id );
		
		if( !check_ajax_referer( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add_todo() ) {
			$error = __( 'Sorry, you are not allowed to change todo status.', 'wpas_productivity' );
		} elseif( !$this->get_data_user() ) {
			$error = __( 'Something went wrong, try again later.', 'wpas_productivity' );
		} elseif( false === $item ) {
			$error = __( 'Todo doesn\'t exist.', 'wpas_productivity' );
		} else {
			$item['status'] = 'completed';
			$this->update( $item, $item_id );
			$success = true;
		}
				
		
		if( $error ) {
			wp_send_json_error( array( 'msg' => $error ) );
		} elseif( $success ) {
			
			ob_start();
			include $this->itemTemplate();
			$item_html = ob_get_clean();

			wp_send_json_success( array( 'msg' => __( 'Todo marked as completed', 'wpas_productivity' ), 'item' => $item_html, 'item_id' => $item_id ) );
		}
		
		die();
	}
}