<?php


class WPAS_PF_User_Additional_Email extends WPAS_PF_User_Info {
	
	protected static $instance = null;
	
	/**
	 * Unique meta key
	 *
	 * @var string 
	 */
	protected $key = 'additional_emails';
	
	
	
	public function __construct() {
		
		add_action( 'wp_ajax_pf_add_additional_emails',		array( $this, 'add_email'       ) ); // Add new email.
		add_action( 'wp_ajax_pf_edit_additional_emails',	array( $this, 'edit_email'      ) ); // Edit existing email.
		add_action( 'wp_ajax_pf_delete_additional_emails',	array( $this, 'delete_email'    ) ); // Delete email.
		add_action( 'wp_ajax_pf_active_additional_emails',	array( $this, 'activate_email'  ) ); // Activate email to receive notifications.
		
		
		
		add_action( 'wp_ajax_pf_edit_win_additional_emails',	array( $this, 'edit_form'	   ) ); // Generate edit email form
		
		
		add_action( 'edit_user_profile',			array( $this, 'user_profile_emails' ) , 10, 1 ); // Display emails on user profile page
		add_action( 'show_user_profile',			array( $this, 'user_profile_emails' ) , 10, 1 ); // Display emails on user profile page
		
		add_action( 'admin_enqueue_scripts',			array( $this, 'enqueue_scripts') );
		
		add_filter( 'wpas_email_notifications_email',		array( $this, 'set_additional_emails'), 99, 3 );
		
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
	 * Check if user have access to additional emails lists
	 * 
	 * @param int $user_id
	 * 
	 * @return boolean
	 */
	public function have_access( $user_id = 0 ) {
		
		$user_id = $this->get_user( $user_id );
		$user = get_user_by( 'id', $user_id );
		
		if( $user->has_cap( 'administer_awesome_support' ) ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Check if user have capability to add an email
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
	 * Display additional emails on user profile page
	 * 
	 * @param object $user
	 */
	public function user_profile_emails( $user ) {
		
		$this->data_user = $user->ID;
		
		$items = $this->getList();
		
		if( !$this->user_can_add() && 0 === count( $items ) ) {
			return;
		}
			
		?>
		<div id="wpas_pf_ui_sn_user_profile">
			<h3><?php  _e( 'Additional Emails', 'wpas_productivity' ); ?></h3>
			<?php $this->display(); ?>
		</div>
		<?php
		
	}
	
	/**
	 * Return list of active email addresses
	 * 
	 * @param type $user_id
	 * 
	 * @return array
	 */
	public function getActiveEmails( $user_id = 0 ) {
		
		$items = $this->getList( $user_id );
		
		$emails = array();
		foreach ( $items as $item ) {
			if( $item['active'] ) {
				$emails[] = $item['email'];
			}
		}
		
		return $emails;
	}
	
	
	/**
	 * Set additional email addresses while sending notification emails
	 * 
	 * @param array $args
	 * @param string $case
	 * @param int $ticket_id
	 * 
	 * @return array
	 */
	public function set_additional_emails( $args, $case, $ticket_id ) {
		
		$emails = $args['recipient_email'];
		
		if( !is_array( $emails ) ) {
			$emails = array( $emails );
		}
		
		
		foreach( $emails as $key => $r_email ) {
			if( is_array( $r_email ) && isset( $r_email['user_id'] ) ) {
				$active_additional_emails = $this->getActiveEmails( $r_email['user_id'] );
				
				if( !empty( $active_additional_emails ) ) {
					
					$cc_emails = ( isset( $r_email['cc_addresses'] ) && is_array( $r_email['cc_addresses'] ) ) ? $r_email['cc_addresses'] : array();
					$emails[ $key ]['cc_addresses'] = $cc_emails + $active_additional_emails;
				}
			}
		}
		
		$args['recipient_email'] = $emails;
		return $args;
	}
	
	/**
	 * Generate add new item button
	 */
	public function addItemButton() {
			
		$window_title = __( 'Add additional email', 'wpas_productivity' );
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
		
		$user_can_add = $this->user_can_add();
		
		
		echo '<div id="wpas_pf_ui_section_'.$this->key.'" data-section="' . $this->key . '" class="wpas_pf_ui_wrapper" data-duid="' . $this->data_user . '">';
		
		if( $user_can_add && $this->data_user ) {
			$this->addItemButton();
		}
			
		$this->items_listing( $this->data_user ) ;
		
		if( $user_can_add && $this->data_user ) {
			$this->form( array(
				'type'		=> 'add',
				'submit_text'	=> __( 'Add Email', 'wpas_productivity' ),
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
			<div class="no_item_msg"><?php echo _e( "No additional email exist.", 'wpas_productivity' ); ?></div>
			<?php include $this->itemsTemplate(); ?>
		</div>

		<?php
	}
	
	/**
	 * Generate edit email form
	 */
	public function edit_form() {
		
		$this->data_user = filter_input( INPUT_GET, 'duid' );
		
		$list = $this->getList();
		
		$id = filter_input( INPUT_GET, 'id' );
		$item = $list[ $id ];
		$item['id'] = $id;
		
		$this->form( array(
			'type'		=> 'edit',
			'submit_text'	=> __( 'Save Email', 'wpas_productivity' ),
			'hidden'	=> false,
			'template'	=> 'add',
			'data'		=> $item
		) );
		
		die();
	}
	
	
	
	/**
	 * Validate add/edit email forms
	 * 
	 * @param string $type
	 * 
	 * @return array
	 */
	protected function validate( $type ) {
		
		$error = "";
		$result = array();
		
		$email   = filter_input( INPUT_POST, 'email'  );
		$active  = filter_input( INPUT_POST, 'active' );
		$item_id = filter_input( INPUT_POST, 'id'     );
		
		$action = $this->actionName( $type );
		$nonce = $this->nonce( $action );
		
		if( ! check_ajax_referer( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add() ) {
			
			$error = __( 'Sorry, you are not allowed to add emails.', 'wpas_productivity' );
			
		} elseif( 'edit' === $type && !$this->item_exist( $item_id ) ) {
			
			$error = __( 'Email doesn\'t exist.', 'wpas_productivity' );
			
		} elseif( !$this->get_data_user() ) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_productivity' );
			
		} elseif( empty( trim( $email ) ) || !is_email( $email ) ) {
			
			$error = __( 'Please provide valid email address.', 'wpas_productivity' );
			
		} 
		
		
		if( $error ) {
			$result['error'] = $error;
		} else {
			$result['item'] = array( 
				'email' => $email, 
				'active' => $active ? true : false
			);
			
			if( 'edit' === $type ) {
				$result['item_id'] = $item_id;
			}
		}
		
		
		return $result;
	}
	
	
	/**
	 * Activate email to receive notifications
	 */
	public function activate_email () {
		
		$this->data_user = (int) filter_input( INPUT_POST, 'duid'  );
		
		$item_id =  filter_input( INPUT_POST, 'id'     );
		$active  =  filter_input( INPUT_POST, 'active' );
		
		$action = $this->actionName( 'active' );
		$nonce = $this->nonce( $action );
		
		$error = "";
		
		if( ! check_ajax_referer( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add() ) {
			
			$error = __( 'Sorry, you are not allowed to add emails.', 'wpas_productivity' );
			
		} elseif( !$this->get_data_user() || !$this->item_exist( $item_id )) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_productivity' );
			
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
			wp_send_json_success( array( 'msg' => __( 'Email Saved', 'wpas_productivity' ), 'update_item' => $item_html, 'item_id' => $item_id ) );
		}
		
		die();
	}
	
	
	/**
	 * Handle add email request
	 */
	public function add_email() {
		
		$this->data_user = (int) filter_input( INPUT_POST, 'duid'  );
		
		$result = $this->validate( 'add' );
		
		if( isset( $result['error'] ) && !empty( $result['error'] ) ) {
			wp_send_json_error( array( 'msg' => $result['error'] ) );
		} else {
			

			$item = $this->add( $result['item'] );

			$item_id = $item['id'];

			ob_start();
			include $this->itemTemplate();
			$item_html = ob_get_clean();

			wp_send_json_success( array( 'msg' => __( 'Email added.', 'wpas_productivity' ), 'item' => $item_html ) );
			
		}
		
		die();
	}
	
	
	/**
	 * Handle edit email request
	 */
	public function edit_email() {
		
		$this->data_user = (int) filter_input( INPUT_POST, 'duid'  );
		
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
			wp_send_json_success( array( 'msg' => __( 'Email Saved', 'wpas_productivity' ), 'update_item' => $item_html, 'item_id' => $item_id ) );
			
		}
		
		die();
		
	}
	
	/**
	 * Handle delete email request
	 */
	public function delete_email() {
		
		$error = "";
		$success = false;
		
		$action = $this->actionName( 'delete' );
		$nonce = $this->nonce( $action );
		
		$this->data_user = (int) filter_input( INPUT_POST, 'duid'  );
		$item_id =  filter_input( INPUT_POST, 'id'  );
		
		
		if( !check_ajax_referer( $nonce['action'], $nonce['name'], false ) || !$this->user_can_add() ) {
			
			$error = __( 'Sorry, you are not allowed to remove emails.', 'wpas_productivity' );
			
		} elseif( !$this->get_data_user() ) {
			
			$error = __( 'Something went wrong, try again later.', 'wpas_productivity' );
			
		} elseif( ! $this->item_exist ( $item_id ) ) {
			
			$error = __( 'Email doesn\'t exist.', 'wpas_productivity' );
			
		} else {
			if( $this->delete( $item_id ) ) {
				$success = true;
			}
		}
		
		
		
		if( true === $success ) {
			wp_send_json_success( array( 'msg' => __( 'Email deleted', 'wpas_productivity' ), 'item_id' => $item_id ) );
		} elseif( !empty( $error ) ) {
			wp_send_json_error( array( 'msg' => $error ) );
		}
		die();
		
	}
	
	

}