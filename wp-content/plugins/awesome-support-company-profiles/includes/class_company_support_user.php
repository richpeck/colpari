<?php


class WPAS_CP_Support_User extends WPAS_CP_Post_Meta {
	
	protected static $instance = null;
	
	/**
	 * Unique meta key
	 *
	 * @var string 
	 */
	protected $key = 'support_users';
	
	protected $data_user;


	protected $view_type = 'company_profile'; // possible options are : 1 - company_profile, 2 - user_profile, 3 - fe_manage_user
	
	
	public function __construct() {
		global $post;
		
		add_action( 'wp_ajax_cp_add_support_users',			array( $this, 'add_support_user'       ) ); // Add new support user.
		add_action( 'wp_ajax_cp_edit_support_users',		array( $this, 'edit_support_user'      ) ); // Edit support user.
		add_action( 'wp_ajax_cp_delete_support_users',		array( $this, 'delete_support_user'    ) ); // Delete support user.
		
		add_action( 'wp_ajax_cp_unlink_user_company_support_users',		array( $this, 'delete_support_user'    ) ); // Unlink company from support user.
		
		
		add_action( 'wp_ajax_cp_edit_win_support_users',	array( $this, 'edit_form'	   ) ); // Generate edit support user form
		
		
		add_action( 'edit_user_profile',			array( $this, 'user_profile_companies' ) , 10, 1 ); // Display companies on user profile page
		add_action( 'show_user_profile',			array( $this, 'user_profile_companies' ) , 10, 1 ); // Display companies on user profile page
		
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
	 * Set page view type
	 * 
	 * @param string $type
	 */
	public function set_view_type( $type ) {
		
		$this->view_type = $type ? $type : 'company_profile';
	}
	
	/**
	 * Set company id
	 * 
	 * @param int $post_id
	 */
	public function set_post( $post_id ) {
		
		$this->post_id = null;
		
		if( $post_id && 'wpas_company_profile' === get_post_type( $post_id ) ) {
			$this->post_id = $post_id;
		}
		
	}
	
	
	/**
	 * Check if user have access to companies
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
		
		if( $this->have_access( $user_id ) ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Generate add new support user button
	 */
	public function addItemButton() {
			
		$window_title = __( 'Add Support User', 'wpas_cp' );
		
			
		$action_name = $this->actionName( 'add' );
		$window_id = "wpas_{$action_name}_wrapper";
		
		
		echo '<div class="wpas_cp_add_item_button">';
			
		
		echo wpas_window_link( array(
			'label' => $window_title,
			'class' => 'button button-primary',
			'data'  => array(
				'win_src' => "#{$window_id}"
				)
				));
					
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
		
		
		echo '<div id="wpas_cp_ui_section_'.$this->key.'" data-section="' . $this->key . '" class="wpas_cp_ui_wrapper" data-duid="' . $this->get_post_id( $post_id ) . '">';
		
		
		echo '<div class="wpas_cp_msg"></div>';
		
		if( $user_can_add ) {
			$this->addItemButton();
		}
			
		$this->items_listing() ;
		
		if( $user_can_add ) {
			
			$this->form( array(
				'type'		=> 'add',
				'submit_text'	=> __( 'Add Support User', 'wpas_cp' ),
				'hidden'	=> true,
				'view_type' => 'company_profile'
			));
		}
		echo '</div>';
	}
	
	
	public function getList( $post_id = 0, $args = array() ) {
		
		$users = WPAS_Company_Support_User::get_profile_support_users( $this->post_id );
		
		return $users;
	}
	
	
	/**
	 * Load items view
	 */
	public function items_listing() {
		
		$items = $this->getList();
		
		
		$id = "wpas_cp_{$this->key}_items";
		?>
		
		<div id="<?php echo $id; ?>" class="wpas_cp_data_items">
			<div class="no_item_msg"><?php echo _e( "No support user exist.", 'wpas_cp' ); ?></div>
			<?php include $this->itemsTemplate(); ?>
		</div>

		<?php
	}
	
	
	/**
	 * Generate edit support user form
	 */
	public function edit_form() {
		
		$id = filter_input( INPUT_POST, 'id' );
		$post_id = filter_input( INPUT_POST, 'duid' );
		
		
		if( !$id || !$post_id ) {
			die();
		}
		
		
		$this->set_post( $post_id );
		
		$list = $this->getList();
		
		$item = $list[ $id ];
		
		
		
		
		$this->form( array(
			'type'		=> 'edit',
			'submit_text'	=> __( 'Save Support User', 'wpas_cp' ),
			'hidden'	=> false,
			'template'	=> 'add',
			'view_type' => 'company_profile',
			'data'		=> array( 'item' => $item )
		) );
		
		die();
	}
	
	
	
	/**
	 * Validate add/edit support user forms
	 * 
	 * @param string $type
	 * 
	 * @return array
	 */
	protected function validate( $type ) {
		
		
		
		$error				= "";
		$result				= array();
		
		$user_id			= filter_input( INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT );
		$company_id			= filter_input( INPUT_POST, 'company_id', FILTER_SANITIZE_NUMBER_INT );
		
		
		$company_id = 'company_profile' === $this->view_type ? $this->post_id : $company_id;
		$user_id	= 'user_profile' === $this->view_type ? $this->data_user : $user_id;
		
		
		
		
		
		$user_type			= filter_input( INPUT_POST, 'user_type' );
		$divisions			= filter_input( INPUT_POST, 'divisions', FILTER_DEFAULT ,FILTER_REQUIRE_ARRAY  );
		$reporting_group	= filter_input( INPUT_POST, 'reporting_group', FILTER_SANITIZE_NUMBER_INT );
		
		$is_primary_user    = filter_input( INPUT_POST, 'is_primary_user' );
		$can_reply_ticket   = filter_input( INPUT_POST, 'can_reply_ticket' );
		$can_close_ticket   = filter_input( INPUT_POST, 'can_close_ticket' );
		$can_open_ticket    = filter_input( INPUT_POST, 'can_open_ticket' );
		$can_manage_profile = filter_input( INPUT_POST, 'can_manage_profile' );
		
		
		$item_id = filter_input( INPUT_POST, 'id'     );
		
		$action = $this->actionName( $type );
		$nonce = $this->nonce( $action );
		
		if( ! wpas_cp_ajax_nonce_check( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add() ) {
			
			$error = __( 'Sorry, you are not allowed to add support user.', 'wpas_cp' );
			
		} elseif( !$user_id && 'company_profile' ===  $this->view_type ) {
			
			$error = __( 'Please select a user.', 'wpas_cp' );
			
		} elseif( !$company_id && 'user_profile' ===  $this->view_type ) {
			
			$error = __( 'Please select a company.', 'wpas_cp' );
			
		} elseif( !$user_type ) {
			
			$error = __( 'Please select user type.', 'wpas_cp' );
			
		} elseif( empty( $divisions ) ) {
			
			$error = __( 'Please select a division.', 'wpas_cp' );
			
		} elseif( ! $reporting_group ) {

			$error = __( 'Please select a reporting group.', 'wpas_cp' );
			
		} elseif( ( !$this->get_post_id() && 'company_profile' ===  $this->view_type ) || ( !$this->data_user && 'user_profile' ===  $this->view_type ) ) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_cp' );
			
		} elseif( 'edit' === $type && !$this->item_exist( $item_id ) ) {
			
			$error = __( 'Support user doesn\'t exist.', 'wpas_cp' );
			
		} elseif( 'add' === $type && $this->user_exist_in_profile( $company_id ,$user_id ) ) {
			
			$error = __( 'Support user already associated with this company.', 'wpas_cp' );
			
		}
		
		
		
		if( $error ) {
			$result['error'] = $error;
		} else {
			$result['item'] = array( 
				'user_id'			=> $user_id,
				'user_type'			=> $user_type,
				'divisions'			=> $divisions,
				'profile_id'		=> $company_id,
				'reporting_group'	=> $reporting_group,
				'primary'			=> $is_primary_user,
				'can_reply_ticket'  => $can_reply_ticket,
				'can_close_ticket'  => $can_close_ticket,
				'can_open_ticket'   => $can_open_ticket,
				'can_manage_profile'=> $can_manage_profile,
			);
			
			if( 'edit' === $type ) {
				$result['item_id'] = $item_id;
				$result['item']['id'] = $item_id;
			}
		}
		
		
		return $result;
	}
	
	
	
	/**
	 * Handle add support user request
	 */
	public function add_support_user() {
		
		$this->set_view_type( filter_input( INPUT_POST, 'view_type' ) );
		
		$post_id = '';
		$data_user_id = '';
		
		
		if( 'user_profile' === $this->view_type ) {
			$data_user_id = filter_input( INPUT_POST, 'duid' );
		} else {
			$post_id = filter_input( INPUT_POST, 'duid' );
		}
		
		
		$this->set_post( $post_id );
		$this->set_data_user( $data_user_id );
		
		$result = $this->validate( 'add' );
		
		if( isset( $result['error'] ) && !empty( $result['error'] ) ) {
			wp_send_json_error( array( 'msg' => $result['error'] ) );
		} else {
			
			
			
			$item = new WPAS_Company_Support_User( $result['item'] );
			
			$item_id = $item->add();
			
			
			if( 'user_profile' === $this->view_type ) {
				$item = Array( 'Company' => get_post( $item->profile_id ), 'SupportUser' => $item );
			}
			

			ob_start();
			
			
			$item_template = 'company_profile' === $this->view_type ? 'item' : 'company_item';
			
			include $this->itemTemplate( $item_template );
			
			
			$item_html = ob_get_clean();

			wp_send_json_success( array( 'msg' => __( 'Support user associated with selected company.', 'wpas_cp' ), 'item' => $item_html ) );
			
		}
		
		die();
	}
	
	
	/**
	 * Handle edit support user request
	 */
	public function edit_support_user() {
		
		$post_id = filter_input( INPUT_POST, 'duid' );
		
		$this->set_post( $post_id );
		
		$result = $this->validate( 'edit' );
		
		if( isset( $result['error'] ) && !empty( $result['error'] ) ) {
			wp_send_json_error( array( 'msg' => $result['error'] ) );
		} else {
			
			$item = new WPAS_Company_Support_User( $result['item'] );
			
			$item_id = $item->id;
			
			$item->update();
			
			
			ob_start();
			include $this->itemTemplate();
			$item_html = ob_get_clean();
			wp_send_json_success( array( 'msg' => __( 'Support user saved', 'wpas_cp' ), 'update_item' => $item_html, 'item_id' => $item_id , 'msg_item' => '#wpas_cp_ui_section_support_users .wpas_cp_msg' ) );
			
		}
		
		die();
		
	}
	
	
	
	/**
	 * Handle delete support user request
	 */
	public function delete_support_user() {
		
		$error = "";
		$success = false;
		
		$action = $this->actionName( 'delete' );
		$nonce = $this->nonce( $action );
		
		
		$post_id = filter_input( INPUT_POST, 'duid' );
		$item_id =  filter_input( INPUT_POST, 'id'  );
		
		if( 'cp_unlink_user_company_support_users' === $_POST['action'] )  {
			
			$user_id = filter_input( INPUT_POST, 'duid' );
			
			$post_id =  filter_input( INPUT_POST, 'id'  );
			
			$support_user = WPAS_Company_Support_User::getCompanySupportUser( $post_id, $user_id );
			
			if( $support_user ) {
				$item_id = $support_user->id;
			}
			
			
		}
		
		
		$this->set_post( $post_id );
		
		
		$item = $this->get_item( $item_id );
		
		
		if( !wpas_cp_ajax_nonce_check( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add() ) {
			
			$error = __( 'Sorry, you are not allowed to remove support user.', 'wpas_cp' );
			
		} elseif( !$this->get_post_id() ) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_cp' );
			
		} elseif( ! $item || $item->profile_id != $post_id) {
			
			$error = __( 'Support user doesn\'t exist.', 'wpas_cp' );
			
		} else {
			
			if( $item->delete() ) {
				$success = true;
			}
		}
		
		
		
		if( true === $success ) {
			wp_send_json_success( array( 'msg' => __( 'Support user deleted', 'wpas_cp' ), 'item_id' => $item_id ) );
		} elseif( !empty( $error ) ) {
			wp_send_json_error( array( 'msg' => $error ) );
		}
		die();
		
	}
	
	/**
	 * Check if user is already associated with a company
	 * 
	 * @param int $profile_id
	 * @param int $user_id
	 * 
	 * @return boolean
	 */
	public function user_exist_in_profile( $profile_id ,$user_id ) {
		
		return WPAS_Company_Support_User::user_exist_in_profile( $profile_id ,$user_id );
	}
	
	
	/**
	 * Return Item with item id
	 * 
	 * @param int $item_id
	 * @param int $post_id
	 * @param array $args Extra configuration
	 * 
	 * @return array/boolean
	 */
	public function get_item( $item_id, $post_id = 0, $args = array() ) {
		
		return WPAS_Company_Support_User::getByItemID( $item_id );
		
	}
	
	
	/**
	 * Check if an item exists
	 * 
	 * @param string $item_id
	 * @param int $post_id
	 * 
	 * @return boolean
	 */
	public function item_exist( $item_id, $post_id = 0 ) {
		
		$item = $this->get_item( $item_id, $post_id );
		
		if( $item ) {
			return true;
		}
		
		return false;
	}
	
	
	
	/**
	 * Display companies on user profile page
	 * 
	 * @param object $user
	 */
	public function user_profile_companies( $user ) {
		
		
		$this->data_user = $user->ID;
		$this->set_view_type( 'user_profile' );
			
		?>
		<div id="wpas_cp_ui_sn_user_profile">
			<h3><?php _e( 'Associated Companies', 'wpas_cp' ); ?></h3>
			<?php $this->display_user_companies(); ?>
		</div>
		<?php
		
		
	}
	
	
	/**
	 * Load companies view in user profile page
	 * 
	 * @param int $post_id
	 */
	public function display_user_companies( $post_id = 0 ) {
		
		$this->set_post( $post_id );
		
		
		$user_can_add = $this->user_can_add();
		
		
		echo '<div id="wpas_cp_ui_section_'.$this->key.'" data-section="' . $this->key . '" class="wpas_cp_ui_wrapper" data-duid="' . $this->data_user . '">';
		
		echo '<div class="wpas_cp_msg"></div>';
		if( $user_can_add ) {
			$this->addCompanyButton();
		}
			
		
		
		
		
		$items = $companies = WPAS_Company_Support_User::getCompaniesByUser( $this->data_user );
		
		
		
		
		
		$id = "wpas_cp_{$this->key}_items";
		?>
		
		<div id="<?php echo $id; ?>" class="wpas_cp_data_items">
			<div class="no_item_msg"><?php echo _e( "No company associated with this user.", 'wpas_cp' ); ?></div>
			<?php include $this->base_template_path . $this->key . '/user_companies.php'; ?>
		</div>

		<?php
		
		
		
		
		if( $user_can_add ) {
			
			$this->form( array(
				'type'		=> 'add',
				'submit_text'	=> __( 'Associate New Company', 'wpas_cp' ),
				'hidden'	=> true,
				'view_type' => 'user_profile',
			));
		}
		echo '</div>';
	}
	
	
	
	/**
	 * Generate associate company button
	 */
	public function addCompanyButton() {
			
		$window_title = __( 'Associate New Company', 'wpas_cp' );
		$width = 600;
		$height = 450;
			
		$action_name = $this->actionName( 'add' );
		$window_id = "wpas_{$action_name}_wrapper";
		
		
		
		
		
		echo '<div class="wpas_cp_add_item_button">';
			
		
			echo wpas_window_link( array(
						'label' => $window_title,
						'class' => 'button button-primary',
						'data'  => array(
							'win_src' => "#{$window_id}"
						)
					));
					
			
		echo '</div>';
		echo '<div class="clear clearfix"></div>';
	}
	
	
}