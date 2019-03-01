<?php


class WPAS_IT_Ticket_Issue extends WPAS_IT_Post_Meta {
	
	protected static $instance = null;
	
	/**
	 * Unique meta key
	 *
	 * @var string 
	 */
	protected $key = 'ticket_issues';
	
	public $ticket_id;
	
	
	public function __construct() {
		global $post;
		
		add_action( 'wp_ajax_it_add_ticket_issues',			array( $this, 'add_issue'       ) ); // Add new issue.
		
		add_action( 'wp_ajax_it_delete_ticket_issues',		array( $this, 'delete_issue'    ) ); // Delete issue.
		
		add_action( 'admin_enqueue_scripts',				array( $this, 'enqueue_scripts' ) );
		
		
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
	 * Set ticket id
	 * 
	 * @param int $post_id
	 */
	public function set_post( $post_id ) {
		
		$this->post_id = null;
		
		if( $post_id && 'ticket' === get_post_type( $post_id ) ) {
			$this->post_id = $post_id;
		}
		
		$this->ticket_id = $this->post_id;
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
	 * Check if user have capability to add a issue
	 * 
	 * @param int $user_id
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
			
		$window_title = __( 'Add Issue', 'wpas_it' );
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
	 * Enqueue static resources
	 */
	public function enqueue_scripts() {
		add_thickbox();
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
				'submit_text'	=> __( 'Add Issue', 'wpas_it' ),
				'hidden'	=> true
			));
		}
		echo '</div>';
	}
	
	/**
	 * Return all ticket issues
	 * 
	 * @param int $ticket_id
	 * @param array $args
	 * 
	 * @return array
	 */
	public function getList( $ticket_id = 0, $args = array() ) {
		
		
		if( isset( $args['type'] ) && 'post' === $args['type']  ) {
			$ticket_id = $this->get_post_id( $ticket_id );
		
			$items = array();

			if( $ticket_id ) {


				$items = $this->get_ticket_issues( $ticket_id );
			}

			
			return ( !is_array( $items ) ? array() : $items ) ;
			
		}
		
		
		return parent::getList( $ticket_id, $args );
	}
	
	
	/**
	 * Load items view
	 */
	public function items_listing() {
		
		$items = $this->getList( $this->ticket_id, array( 'type' => 'post' ) );
		
		
		$id = "wpas_it_{$this->key}_items";
		?>
		
		<div id="<?php echo $id; ?>" class="wpas_it_data_items">
			<div class="no_item_msg"><?php echo _e( "No issue exist.", 'wpas_it' ); ?></div>
			<?php include $this->itemsTemplate(); ?>
		</div>

		<?php
	}
	
	
	/**
	 * Check if an issue exist in a ticket
	 * 
	 * @param type $issue_id
	 * @return type
	 */
	public function ticket_has_issue( $issue_id ) {
		
		$_issue_id = get_post_meta($this->ticket_id, "wpas_ticket_issue_{$issue_id}", true );
		
		return $issue_id == $_issue_id;
		
	}
	
	/**
	 * Validate add/edit issue forms
	 * 
	 * @param string $type
	 * 
	 * @return array
	 */
	protected function validate( $type ) {
		
		$error = "";
		$result = array();
		
		$issue = filter_input( INPUT_POST, 'wpas_ticket_issue'  );
		
		$item_id = filter_input( INPUT_POST, 'id'     );
		
		$action = $this->actionName( $type );
		$nonce = $this->nonce( $action );
		
		if( ! wpas_it_ajax_nonce_check( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add() ) {
			
			$error = __( 'Sorry, you are not allowed to add issues.', 'wpas_it' );
			
		} elseif( !$issue ) {
			
			$error = __( 'Please select an issue.', 'wpas_it' );
			
		}
		
		elseif( 'edit' === $type && !$this->item_exist( $item_id ) ) {
			
			$error = __( 'Issue doesn\'t exist.', 'wpas_it' );
			
		}
		elseif( 'add' === $type && $this->ticket_has_issue( $issue ) ) {
			
			$error = __( 'Issue already exist in this ticket.', 'wpas_it' );
			
		} elseif( !$this->get_post_id() ) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_it' );
			
		} elseif( empty( trim( $issue ) ) ) {
			
			$error = __( 'Please select an issue.', 'wpas_it' );
			
		} 
		
		
		if( $error ) {
			$result['error'] = $error;
		} else {
			$result['item'] = array( 
				'issue' => $issue, 
			);
			
			if( 'edit' === $type ) {
				$result['item_id'] = $item_id;
			}
		}
		
		
		return $result;
	}
	
	/**
	 * Return all ticket issues
	 * 
	 * @param int $ticket_id
	 * 
	 * @return array
	 */
	public function get_ticket_issues( $ticket_id ) {
		
		$_issues = WPAS_IT_Issue::get_ticket_issues( $ticket_id );
		
		
		$issues = array();
		
		$list = parent::getList( $ticket_id );
		
		foreach( $list as $item_id => $item ) {
			
			$item_key = '';
			
			foreach ( $_issues as $index => $issue ) {
				
				if( $item['issue'] == $issue->issue_id ) {
					$item_key = $item_id;
					break;
				}
				
			}
			
			if( $item_key ) {
				$issues[ $item_key ] = $issue;
			} else {
				$this->delete( $item_id );
			}
		}
		
		
		return $issues;

	}
	
	
	
	/**
	 * Handle add issue request
	 */
	public function add_issue() {
		
		$ticket_id = filter_input( INPUT_POST, 'duid' );
		
		$this->set_post( $ticket_id );
		
		$result = $this->validate( 'add' );
		
		if( isset( $result['error'] ) && !empty( $result['error'] ) ) {
			wp_send_json_error( array( 'msg' => $result['error'] ) );
		} else {
			
			$issue = $result['item']['issue'];
			
			$_item = $this->add( $result['item'] );
			
			update_post_meta( $this->ticket_id, "wpas_ticket_issue_{$issue}",     $issue     );
			
			// Calculate issue tickets count
			$item = new WPAS_IT_Issue( $_item['issue'] );
			$item->calculateTicketsCount();
			
			
			do_action( 'wpas_it_after_issue_assigned_to_ticket', $this->ticket_id , $issue );

			$item_id = $_item['id'];

			ob_start();
			include $this->itemTemplate();
			$item_html = ob_get_clean();

			wp_send_json_success( array( 'msg' => __( 'Issue added.', 'wpas_it' ), 'item' => $item_html ) );
			
		}
		
		die();
	}
	
	
	/**
	 * Handle delete issue request
	 */
	public function delete_issue() {
		
		$error = "";
		$success = false;
		
		$action = $this->actionName( 'delete' );
		$nonce = $this->nonce( $action );
		
		$ticket_id = filter_input( INPUT_POST, 'duid' );
		$item_id =  filter_input( INPUT_POST, 'id'  );
		
		$this->set_post( $ticket_id );
		
		if( !wpas_it_ajax_nonce_check( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add() ) {
			
			$error = __( 'Sorry, you are not allowed to remove issues.', 'wpas_it' );
			
		} elseif( !$this->get_post_id() ) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_it' );
			
		} elseif( ! $this->item_exist ( $item_id ) ) {
			
			$error = __( 'Issue doesn\'t exist.', 'wpas_it' );
			
		} else {
			
			$item = $this->get_item( $item_id );
			
			
			$issue_id = $item['issue'];
			
			if( $this->delete( $item_id ) ) {
				delete_post_meta( $this->ticket_id, "wpas_ticket_issue_{$issue_id}" );
				
				do_action( 'wpas_it_after_issue_removed_from_ticket', $this->ticket_id , $issue_id );
				
				// Calculate issue tickets count
				$issue = new WPAS_IT_Issue( $issue_id );
				$issue->calculateTicketsCount();
				
				$success = true;
			}
		}
		
		
		
		if( true === $success ) {
			wp_send_json_success( array( 'msg' => __( 'Issue deleted', 'wpas_it' ), 'item_id' => $item_id ) );
		} elseif( !empty( $error ) ) {
			wp_send_json_error( array( 'msg' => $error ) );
		}
		die();
		
	}
	
}