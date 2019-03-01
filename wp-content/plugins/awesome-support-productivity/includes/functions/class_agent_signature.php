<?php

/**
 * Handle Agent Signatures
 */
class WPAS_PF_Agent_Signature extends WPAS_PF_User_Info {
	
	protected static $instance = null;
	
	/**
	 * Unique meta key
	 *
	 * @var string 
	 */
	protected $key = 'signatures';
	
	
	public function __construct() {
		
		add_action( 'wp_ajax_pf_add_signatures',		array( $this, 'add_signature'       ) ); // Add new support signature.
		add_action( 'wp_ajax_pf_edit_signatures',		array( $this, 'edit_signature'      ) ); // Edit existing signature.
		add_action( 'wp_ajax_pf_delete_signatures',		array( $this, 'delete_signature'    ) ); // Delete signature.
		add_action( 'wp_ajax_pf_duplicate_signatures',		array( $this, 'duplicate_signature' ) ); // Duplicate signature.
		add_action( 'wp_ajax_pf_default_signatures',		array( $this, 'default_signature'   ) ); // Set signature as default.
		
		add_action( 'wp_ajax_pf_edit_win_signatures',		array( $this, 'edit_form'	    ) ); // Generate edit signature form
		
		add_action( 'edit_user_profile',			array( $this, 'user_profile_signatures' ) , 10, 1 ); // Display signatures on user profile page
		add_action( 'show_user_profile',			array( $this, 'user_profile_signatures' ) , 10, 1 ); // Display signatures on user profile page
		
		
		add_filter( 'wpas_pf_localize_script',	array( $this, 'localize_script' ), 11, 1 );
		
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
	 * Append default signature in reply field
	 * 
	 * @param string $content
	 * 
	 * @return string
	 */
	public function localize_script( $script = array() ) {
		
		$default_signature = $this->getDefaultSignature();
		
		if( $default_signature ) {
			$script['default_signature'] = $default_signature;
		}
		
		return $script;
	}
	
	/**
	 * Return default signature
	 * 
	 * @return array
	 */
	protected function getDefaultSignature() {
		
		$items = $this->getList();
		$default_item = array();
		foreach( $items as $item_id => $item ) {
			if( isset( $item['default'] ) && $item['default'] ) {
				$default_item = $item;
				$default_item['id'] = $item_id;
				break;
			}
		}
		
		return $default_item;
		
	}
	
	/**
	 * Check if user have access to signatures
	 * 
	 * @param int $user_id
	 * 
	 * @return boolean
	 */
	public function have_access( $user_id = 0 ) {
		
		$user_id = $this->get_user( $user_id );
		$user = get_user_by( 'id', $user_id );
		
		if( $user->has_cap( 'edit_ticket' ) || $user->has_cap( 'administer_awesome_support' ) || $user->has_cap( 'admin' ) ) {
			return true;
		}
		
		
		
		return false;
	}
	
	
	/**
	 * Check if we should display signatures metabox
	 * 
	 * @return boolean
	 */
	public static function should_display() {
		
		$_this = WPAS_PF_Agent_Signature::get_instance();
		
		if( $_this->have_access() ) {
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * Check if user have capability to add a signature
	 * 
	 * @param int $user_id
	 * @return boolean
	 */
	
	public function user_can_add_signature( $user_id = 0 ) {
		
		$user_id = $this->get_user( $user_id );
		
		if( $this->have_access( $user_id ) && $user_id === $this->data_user ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Return user signatures
	 * 
	 * @param int $user_id
	 * @param array $args
	 * 
	 * @return array
	 */
	public function getList( $user_id = 0, $args = array() ) {
		
		$items = parent::getList( $user_id );
		
		if( isset( $args['clear_default'] ) && $args['clear_default'] ) {
			foreach( $items  as $item_id => $item ) {
				$items[ $item_id ]['default'] = '0';
			}
		}
		
		return $items;
		
	}
	
	/**
	 * Display signatures on user profile page
	 * 
	 * @param object $user
	 */
	public function user_profile_signatures( $user ) {
		
		if( $this->have_access( $user->ID  ) ) :
			
			$this->data_user = $user->ID;
		?>
		<div id="wpas_pf_ui_sn_user_profile">
			<h3><?php _e( 'Signatures', 'wpas_productivity' ); ?></h3>
			<?php $this->display(); ?>
		</div>
		<?php
		
		endif;
	}
	
	
	
	/**
	 * Generate add new item button
	 */
	public function addItemButton() {
			
		$window_title = __( 'Add signature', 'wpas_productivity' );
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
		
		$user_can_add = $this->user_can_add_signature();
		
		
		echo '<div id="wpas_pf_ui_section_'.$this->key.'" data-section="' . $this->key . '" class="wpas_pf_ui_wrapper" data-duid="' . $this->data_user . '">';
		
		if( $user_can_add && $this->data_user ) {
			$this->addItemButton();
		}
			
		$this->items_listing( $this->data_user ) ;
		
		if( $user_can_add && $this->data_user ) {
			$this->form( array(
				'type'		=> 'add',
				'submit_text'	=> __( 'Add Signature' ),
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
		
		$default_signature = $this->getDefaultSignature();
		$default_signature_id = $default_signature ? $default_signature['id'] : '';
		?>
		
		<div data-default_signature="<?php echo $default_signature_id; ?>" id="<?php echo $id; ?>" class="wpas_pf_data_items">
			<div class="no_item_msg"><?php echo _e( "No signature exist.", 'wpas_productivity' ); ?></div>
			<?php include $this->itemsTemplate(); ?>
		</div>

		<?php
	}
	
	
	/**
	 * Generate edit signature form
	 */
	public function edit_form() {
		
		$this->data_user = (int) filter_input( INPUT_GET, 'duid' );
		
		$list = $this->getList();
		
		$id = filter_input( INPUT_GET, 'id' );
		$item = $list[ $id ];
		$item['id'] = $id;
		
		$this->form( array(
			'type'		=> 'edit',
			'submit_text'	=> __( 'Save Signature', 'wpas_productivity' ),
			'hidden'	=> false,
			'template'	=> 'add',
			'data'		=> $item
		) );
		
		die();
	}
	
	
	/**
	 * Handle add signature request
	 */
	public function add_signature() {
		
		$error = "";
		
		$user_id = $this->get_user();
		
		$action = $this->actionName( 'add' );
		$nonce = $this->nonce( $action );
		
		
		$signature =  filter_input( INPUT_POST, 'pf_signature_content_add'  );
		$is_default = filter_input( INPUT_POST, 'default'    ) ? '1' : '0';
		$this->data_user = (int) filter_input( INPUT_POST, 'duid'  );
		
		
		if( ! check_ajax_referer( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add_signature() ) {
			
			$error = __( 'Sorry, you are not allowed to add signature.', 'wpas_productivity' );
			
		} elseif( !$this->get_data_user() ) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_productivity' );
			
		} elseif( trim( empty( $signature ) ) ) {
			
			$error = __( 'Signature is required.', 'wpas_productivity' );
			
		}
		
		
		if( $error ) {
			wp_send_json_error( array( 'msg' => $error ) );
		} else {
			
			$item = array( 
				'signature' => $signature, 
				'default' => $is_default
			);
			
			$_args = array();

			if( $is_default ) {
				$_args['clear_default'] = true;
			}
			
			$item = $this->add( $item, 0, $_args );
			
			$item_id = $item['id'];

			ob_start();
			include $this->itemTemplate();
			$item_html = ob_get_clean();

			wp_send_json_success( array( 'msg' => __( 'Signature created', 'wpas_productivity' ), 'item' => $item_html, 'signature' => $item['signature'] ) );
			
		}
		
		die();
	}
	
	
	
	
	/**
	 * Handle edit signature request
	 */
	public function edit_signature() {
		
		$error = "";
		
		$action = $this->actionName( 'edit' );
		$nonce = $this->nonce( $action );
		
		$item_id =  filter_input( INPUT_POST, 'id'    );
		$signature    =  filter_input( INPUT_POST, 'pf_signature_content_edit'  );
		$is_default = filter_input( INPUT_POST, 'default'    ) ? '1' : '0';
		$this->data_user = (int) filter_input( INPUT_POST, 'duid'  );
		
		$list = $this->getList();
		
		
		if( ! check_ajax_referer( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add_signature() ) {
			
			$error = __( 'Sorry, you are not allowed to edit signature.', 'wpas_productivity' );
			
		} elseif( !$this->get_data_user() ) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_productivity' );
			
		} elseif( ! isset( $list[ $item_id ] ) ) {
			
			$error = __( 'Signature doesn\'t exist.', 'wpas_productivity' );
			
		} elseif( trim( empty( $signature ) ) ) {
			
			$error = __( 'Signature is required.', 'wpas_productivity' );
			
		}
		
		
		if( $error ) {
			wp_send_json_error( array( 'msg' => $error ) );
		} else {
			
			$item = array( 
				'signature'	=> $signature,
				'default'	=> $is_default
			);
			
			
			$_args = array();

			if( $is_default ) {
				$_args['clear_default'] = true;
			}
			
			
			$this->update( $item, $item_id, 0, $_args );
			
			ob_start();
			include $this->itemTemplate();
			$item_html = ob_get_clean();
			wp_send_json_success( array( 'msg' => __( 'Signature Saved', 'wpas_productivity' ), 'update_item' => $item_html, 'item_id' => $item_id, 'signature' => $item['signature'] ) );
		}
		
		
		
		die();
	}
	
	/**
	 * Handle delete signature request
	 */
	public function delete_signature() {
		
		$error = "";
		$success = false;
		
		$action = $this->actionName( 'delete' );
		
		$nonce = $this->nonce( $action );
		$item_id =  filter_input( INPUT_POST, 'id'  );
		$this->data_user = (int) filter_input( INPUT_POST, 'duid'  );
		
		$list = $this->getList();
		
		
		if( !check_ajax_referer( $nonce['action'], $nonce['name'], false ) ) {
			
			$error = __( 'Sorry, you are not allowed to remove signature.', 'wpas_productivity' );
			
		} elseif( !$this->get_data_user() ) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_productivity' );
			
		} elseif( !isset( $list[ $item_id ] ) ) {
			
			$error = __( 'Signature doesn\'t exist.', 'wpas_productivity' );
			
		} else {
			if( $this->delete( $item_id ) ) {
				$success = true;
			}
		}
		
		
		
		if( true === $success ) {
			wp_send_json_success( array( 'msg' => __( 'Signature delated', 'wpas_productivity' ), 'item_id' => $item_id ) );
		} elseif( !empty( $error ) ) {
			wp_send_json_error( array( 'msg' => $error ) );
		}
		die();
		
	}
	
	/**
	 * Handle duplicate signature request
	 */
	public function duplicate_signature() {
		
		$error = "";
		$success = false;
		
		$user_id = $this->get_user();
		
		$action = $this->actionName( 'duplicate' );
		
		$nonce = $this->nonce( $action );
		$id = filter_input( INPUT_POST, 'id' );
		$this->data_user = (int) filter_input( INPUT_POST, 'duid'  );
		
		$list = $this->getList();
		
		if( !check_ajax_referer( $nonce['action'], $nonce['name'], false ) ) {
			$error = __( 'Sorry, you are not allowed to add signature.', 'wpas_productivity' );
		} elseif( !$this->get_data_user() ) {
			$error = __( 'Something went wrong, try again later.', 'wpas_productivity' );
		} elseif( !isset( $list[ $id ] ) ) {
			$error = __( 'Signature doesn\'t exist.', 'wpas_productivity' );
		} else {
			$item = $list[ $id ];
			$item['default'] = '0';
			
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

			wp_send_json_success( array( 'msg' => __( 'Signature duplicated', 'wpas_productivity' ), 'item' => $item_html ) );
		}
		
		die();
		
	}
	
	/**
	 * Handle set default signature request
	 */
	public function default_signature() {
		
		$error = "";
		$success = false;
		
		$user_id = $this->get_user();
		
		$action = $this->actionName( 'default' );
		
		$nonce = $this->nonce( $action );
		$id = filter_input( INPUT_POST, 'id' );
		$this->data_user = (int) filter_input( INPUT_POST, 'duid'  );
		
		$list = $this->getList();
		
		if( !check_ajax_referer( $nonce['action'], $nonce['name'], false ) ) {
			$error = __( 'Sorry, you are not allowed to change default signature.', 'wpas_productivity' );
		} elseif( !$this->get_data_user() ) {
			$error = __( 'Something went wrong, try again later.', 'wpas_productivity' );
		} elseif( !isset( $list[ $id ] ) ) {
			$error = __( 'Signature doesn\'t exist.', 'wpas_productivity' );
		} else {
			$item = $list[ $id ];
			$item['default'] = "1";
			
			$this->update( $item, $id, 0, array( 'clear_default' => true ) );
			$item_id = $id;
			$success = true;
		}
				
		
		if( $error ) {
			wp_send_json_error( array( 'msg' => $error ) );
		} elseif( $success ) {
			
			ob_start();
			include $this->itemTemplate();
			$item_html = ob_get_clean();

			wp_send_json_success( array( 'msg' => __( 'Signature set as default', 'wpas_productivity' ), 'item' => $item_html, 'signature' => $item['signature'] ) );
		}
		
		die();
		
	}
}