<?php


class WPAS_PF_Personal_Note extends WPAS_PF_User_Info {
	
	protected static $instance = null;
	
	/**
	 * Unique meta key
	 *
	 * @var string 
	 */
	protected $key = 'personal_notes';
	
	
	public function __construct() {
		
		add_action( 'wp_ajax_pf_add_personal_notes',	    array( $this, 'add_note'       ) ); // Add new personal note.
		add_action( 'wp_ajax_pf_edit_personal_notes',	    array( $this, 'edit_note'      ) ); // Edit existing personal note.
		add_action( 'wp_ajax_pf_delete_personal_notes',	    array( $this, 'delete_note'    ) ); // Delete personal note.
		add_action( 'wp_ajax_pf_duplicate_personal_notes',   array( $this, 'duplicate_note' ) ); // Duplicate new personal note.
		
		add_action( 'wp_ajax_pf_edit_win_personal_notes',    array( $this, 'edit_form'	   ) ); // Generate edit note form
		//add_filter( 'init',				    array( $this, 'add_capability' ) ); // Add new capability
		
		add_action( 'edit_user_profile',		    array( $this, 'user_profile_notes' ) , 10, 1 ); // Display tickets on user profile page
		add_action( 'show_user_profile',		    array( $this, 'user_profile_notes' ) , 10, 1 ); // Display tickets on user profile page
		
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
	
	
	public function have_access( $user_id = 0 ) {
		
		$user_id = $this->get_user( $user_id );
		$user = get_user_by( 'id', $user_id );
		
		if( $user->has_cap( 'edit_ticket' ) || $user->has_cap( 'administer_awesome_support' ) ) {
			return true;
		}
		
		
		
		return false;
	}
	
	/**
	 * Check if user have capability to add a note
	 * 
	 * @param int $user_id
	 * @return boolean
	 */
	public function user_can_add_note( $user_id = 0 ) {
		
		$user_id = $this->get_user( $user_id );
		
		$user = get_user_by( 'id', $user_id );
		if( $user->has_cap( 'edit_ticket' ) && $user_id === $this->data_user ) {
			return true;
		}
		
		return false;
		
		
		
	}
	
	/**
	 * Display personal notes on user profile page
	 * 
	 * @param object $user
	 */
	public function user_profile_notes( $user ) {
		
		if( $this->have_access( $user->ID  ) ) :
			
			$this->data_user = $user->ID;
		?>
		<div id="wpas_pf_ui_sn_user_profile">
			<h3><?php _e( 'Personal Notes', 'wpas_productivity' ); ?></h3>
			<?php $this->display(); ?>
		</div>
		<?php
		
		endif;
	}
	
	
	/**
	 * Generate add new item button
	 */
	public function addItemButton() {
			
		$window_title = __( 'Add personal note', 'wpas_productivity' );
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
	 * Load whole view
	 * 
	 * @param int $post_id
	 * @param int $user_id
	 */
	public function display( $post_id = 0 ) {
		
		add_thickbox();
		
		$user_can_add = $this->user_can_add_note();
		
		
		echo '<div id="wpas_pf_ui_section_'.$this->key.'" data-section="' . $this->key . '" class="wpas_pf_ui_wrapper" data-duid="' . $this->data_user . '">';
		
		if( $user_can_add && $this->data_user ) {
			$this->addItemButton();
		}
			
		$this->items_listing( $this->data_user ) ;
		
		if( $user_can_add && $this->data_user ) {
			$this->form( array(
				'type'		=> 'add',
				'submit_text'	=> __( 'Add Note', 'wpas_productivity' ),
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
			<div class="no_item_msg"><?php echo _e( "No personal note exist.", 'wpas_productivity' ) ?></div>
			<?php include $this->itemsTemplate(); ?>
		</div>

		<?php
	}
	
	/**
	 * Generate edit note form
	 */
	public function edit_form() {
		
		$this->data_user = filter_input( INPUT_GET, 'duid' );
		
		$list = $this->getList();
		
		$id = filter_input( INPUT_GET, 'id' );
		$item = $list[ $id ];
		$item['id'] = $id;
		
		$this->form( array(
			'type'		=> 'edit',
			'submit_text'	=> __( 'Save Note', 'wpas_productivity' ),
			'hidden'	=> false,
			'template'	=> 'add',
			'data'		=> $item
		) );
		
		die();
	}
	
	
	/**
	 * Handle add note request
	 */
	public function add_note() {
		
		$error = "";
		
		$user_id = $this->get_user();
		
		$action = $this->actionName( 'add' );
		$nonce = $this->nonce( $action );
		
		$title = filter_input( INPUT_POST, 'title' );
		$body =  filter_input( INPUT_POST, 'body'  );
		
		
		if( ! check_ajax_referer( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add_note() ) {
			
			$error = __( 'Sorry, you are not allowed to add personal notes.', 'wpas_productivity' );
			
		} elseif( !$this->get_data_user() ) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_productivity' );
			
		} elseif( empty( trim( $title ) ) ) {
			
			$error = __( 'Title is required.', 'wpas_productivity' );
			
		} elseif( trim( empty( $body ) ) ) {
			
			$error = __( 'Body is required.', 'wpas_productivity' );
			
		}
		
		
		if( $error ) {
			wp_send_json_error( array( 'msg' => $error ) );
		} else {
			
			$item = array( 
				'title' => sanitize_text_field( $title ), 
				'body' => $body, 
				'time' => time()
			);

			$item = $this->add( $item );

			$item_id = $item['id'];

			ob_start();
			include $this->itemTemplate();
			$item_html = ob_get_clean();

			wp_send_json_success( array( 'msg' => __( 'Note created', 'wpas_productivity' ), 'item' => $item_html ) );
			
		}
		
		die();
	}
	
	
	/**
	 * Handle edit note request
	 */
	public function edit_note() {
		
		$error = "";
		
		$action = $this->actionName( 'edit' );
		$nonce = $this->nonce( $action );
		
		$item_id =  filter_input( INPUT_POST, 'id'    );
		$title   =  filter_input( INPUT_POST, 'title' );
		$body    =  filter_input( INPUT_POST, 'body'  );
		
		$list = $this->getList();
		
		if( ! check_ajax_referer( $nonce['action'], $nonce['name'], false ) || ! $this->user_can_add_note() ) {
			
			$error = __( 'Sorry, you are not allowed to edit personal notes.', 'wpas_productivity' );
			
		} elseif( !$this->get_data_user() ) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_productivity' );
			
		} elseif( ! isset( $list[ $item_id ] ) ) {
			
			$error = __( 'Note doesn\'t exist.', 'wpas_productivity' );
			
		} elseif( empty( trim( $title ) ) ) {
			
			$error = __( 'Title is required.', 'wpas_productivity' );
			
		} elseif( trim( empty( $body ) ) ) {
			
			$error = __( 'Body is required.', 'wpas_productivity' );
			
		}
		
		
		if( $error ) {
			wp_send_json_error( array( 'msg' => $error ) );
		} else {
			
			$item = array( 
				'title' => sanitize_text_field( $title ), 
				'body'	=> $body,
				'time'	=> $list[ $item_id ]['time']
			);

			$this->update( $item, $item_id );
			
			ob_start();
			include $this->itemTemplate();
			$item_html = ob_get_clean();
			wp_send_json_success( array( 'msg' => __( 'Note Saved', 'wpas_productivity' ), 'update_item' => $item_html, 'item_id' => $item_id ) );
		}
		
		
		
		die();
	}
	
	/**
	 * Handle delete note request
	 */
	public function delete_note() {
		
		$error = "";
		$success = false;
		
		$action = $this->actionName( 'delete' );
		
		$nonce = $this->nonce( $action );
		$item_id =  filter_input( INPUT_POST, 'id'  );
		
		$list = $this->getList();
		
		
		if( !check_ajax_referer( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add_note() ) {
			
			$error = __( 'Sorry, you are not allowed to remove personal notes.', 'wpas_productivity' );
			
		} elseif( !$this->get_data_user() ) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_productivity' );
			
		} elseif( !isset( $list[ $item_id ] ) ) {
			
			$error = __( 'Note doesn\'t exist.', 'wpas_productivity' );
			
		} else {
			if( $this->delete( $item_id ) ) {
				$success = true;
			}
		}
		
		
		
		if( true === $success ) {
			wp_send_json_success( array( 'msg' => __( 'Note delated' ), 'item_id' => $item_id ) );
		} elseif( !empty( $error ) ) {
			wp_send_json_error( array( 'msg' => $error ) );
		}
		die();
		
	}
	
	/**
	 * Handle duplicate note request
	 */
	public function duplicate_note() {
		
		$error = "";
		$success = false;
		
		$user_id = $this->get_user();
		
		$action = $this->actionName( 'duplicate' );
		
		$nonce = $this->nonce( $action );
		$id = filter_input( INPUT_POST, 'id' );
		
		$list = $this->getList();
		
		if( !check_ajax_referer( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add_note() ) {
			$error = __( 'Sorry, you are not allowed to add personal notes.', 'wpas_productivity' );
		} elseif( !$this->get_data_user() ) {
			$error = __( 'Something went wrong, try again later.', 'wpas_productivity' );
		} elseif( !isset( $list[ $id ] ) ) {
			$error = __( 'Note doesn\'t exist.', 'wpas_productivity' );
		} else {
			$item = $list[ $id ];
			$item['time'] = time();
			$item['added_by'] = $user_id;
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

			wp_send_json_success( array( 'msg' => __( 'Note duplicated', 'wpas_productivity' ), 'item' => $item_html ) );
		}
		
		die();
		
	}
}