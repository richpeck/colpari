<?php


class WPAS_IT_Additional_Agent extends WPAS_IT_Post_Meta {
	
	protected static $instance = null;
	
	/**
	 * Unique meta key
	 *
	 * @var string 
	 */
	protected $key = 'additional_agents';
	
	
	public function __construct() {
		global $post;
		
		add_action( 'wp_ajax_it_add_additional_agents',			array( $this, 'add_agent'       ) ); // Add new agent.
		
		add_action( 'wp_ajax_it_delete_additional_agents',		array( $this, 'delete_agent'    ) ); // Delete agent.
		
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
	 * Check if user have capability to add additional agent
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
			
		$window_title = __( 'Add Additional Agent', 'wpas_it' );
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
				'submit_text'	=> __( 'Add Additional Agent', 'wpas_it' ),
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
			<div class="no_item_msg"><?php echo _e( "No additional agent exist.", 'wpas_it' ); ?></div>
			<?php include $this->itemsTemplate(); ?>
		</div>

		<?php
	}
	
	/**
	 * Check if additional agent exists in issue
	 * 
	 * @param int $agent
	 * 
	 * @return boolean
	 */
	public function issue_has_additional_agent( $agent ) {
		
		if( $this->item_exist_by_column( 'user_id', $agent ) ) {
			return true;
		}
		
		return false;
		
	}
	
	/**
	 * Validate add/edit additional agent forms
	 * 
	 * @param string $type
	 * 
	 * @return array
	 */
	protected function validate( $type ) {
		
		$error = "";
		$result = array();
		
		$agent = filter_input( INPUT_POST, 'wpas_it_a_agent'  );
		
		$item_id = filter_input( INPUT_POST, 'id'     );
		
		$action = $this->actionName( $type );
		$nonce = $this->nonce( $action );
		
		if( ! wpas_it_ajax_nonce_check( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add() ) {
			
			$error = __( 'Sorry, you are not allowed to add additional agent.', 'wpas_it' );
			
		} elseif( empty( trim( $agent ) ) ) {
			
			$error = __( 'Please select an agent.', 'wpas_it' );
			
		}
		elseif( 'edit' === $type && !$this->item_exist( $item_id ) ) {
			
			$error = __( 'Agent doesn\'t exist.', 'wpas_it' );
			
		}
		elseif( 'add' === $type && $this->issue_has_additional_agent( $agent ) ) {
			
			$error = __( 'Agent already exist in this issue.', 'wpas_it' );
			
		} elseif( !$this->get_post_id() ) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_it' );
			
		}
		
		
		if( $error ) {
			$result['error'] = $error;
		} else {
			$result['item'] = array( 'user_id' => $agent );
			
			if( 'edit' === $type ) {
				$result['item_id'] = $item_id;
			}
		}
		
		
		return $result;
	}
	
	
	
	/**
	 * Handle add additional agent request
	 */
	public function add_agent() {
		
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

			wp_send_json_success( array( 'msg' => __( 'Agent added.', 'wpas_it' ), 'item' => $item_html ) );
			
		}
		
		die();
	}
	
	
	/**
	 * Handle delete additional agent request
	 */
	public function delete_agent() {
		
		$error = "";
		$success = false;
		
		$action = $this->actionName( 'delete' );
		$nonce = $this->nonce( $action );
		
		$post_id = filter_input( INPUT_POST, 'duid' );
		$item_id =  filter_input( INPUT_POST, 'id'  );
		
		$this->set_post( $post_id );
		
		
		if( !wpas_it_ajax_nonce_check( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add() ) {
			
			$error = __( 'Sorry, you are not allowed to remove additional agent.', 'wpas_it' );
			
		} elseif( !$this->get_post_id() ) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_it' );
			
		} elseif( ! $this->item_exist ( $item_id ) ) {
			
			$error = __( 'Addition agent doesn\'t exist.', 'wpas_it' );
			
		} else {
			
			$item = $this->get_item( $item_id );
			
			if( $this->delete( $item_id ) ) {
				
				$success = true;
			}
		}
		
		
		
		if( true === $success ) {
			wp_send_json_success( array( 'msg' => __( 'Additional agent deleted', 'wpas_it' ), 'item_id' => $item_id ) );
		} elseif( !empty( $error ) ) {
			wp_send_json_error( array( 'msg' => $error ) );
		}
		die();
		
	}
	
}