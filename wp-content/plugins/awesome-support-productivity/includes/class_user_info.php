<?php


abstract class WPAS_PF_User_Info {
	
	protected static $instance = null;
	
	protected $key = '';
	
	/**
	 * Data user id
	 * @var int 
	 */
	protected $data_user = null;


	public function __construct() {
		
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
	 * Return all user data items per type
	 * 
	 * @param int $user_id
	 * @param array $args Extra configuration
	 * 
	 * @return array
	 */
	public function getList( $user_id = 0, $args = array() ) {
		
		$user_id = $this->get_data_user( $user_id );
		
		$items = array();
		
		if( $user_id ) {
			$items = maybe_unserialize( get_user_meta( $user_id , $this->key, true ) );
		}
		
		return ( !is_array( $items ) ? array() : $items ) ;
	}
	
	/**
	 * Return items view file path based on data type
	 * 
	 * @return string
	 */
	public function itemsTemplate() {
		return WPAS_PF_PATH . 'includes/templates/' . $this->key . '/items.php';
	}
	
	/**
	 * Return single item view file path based on data type
	 * 
	 * @return string
	 */
	public function itemTemplate() {
		return WPAS_PF_PATH . 'includes/templates/' . $this->key . '/item.php';
	}
	
	/**
	 * Return a template file path based on template name and data type
	 * 
	 * @param string $template
	 * 
	 * @return string
	 */
	public function templatePath( $template ) {
		return WPAS_PF_PATH . 'includes/templates/' . "{$this->key}/{$template}.php";
	}
	
	/**
	 * Generate an id for new or duplicated items
	 * 
	 * @return string
	 */
	public function generateID() {
		return wp_generate_password( 8, false );
	}
	
	/**
	 * Get unique action name for each action based on data type
	 * 
	 * @param string $type
	 * 
	 * @return string
	 */
	public function actionName( $type ) {
		return "pf_{$type}_{$this->key}";
	}
	
	/**
	 * Return nonce keys to generate and verify nonce
	 * 
	 * @param string $action
	 * 
	 * @return string
	 */
	public function nonce( $action ) {
		
		$nonce_action = str_replace( '_', '-', $action );
		
		$nonce = array( 
			'action' => $nonce_action , 
			'name'   => '_wpnonce_'.$nonce_action 
		);
		
		return $nonce;
	}
	
	
	/**
	 * Generate add/edit etc forms
	 * 
	 * @param string $args
	 */
	public function form( $args = array() ) {
		
		
		$defaults = array(
		    'type'		=> 'add',
		    'submit_text'	=> __( 'Add', 'wpas_productivity' ),
		    'hidden'		=> false,
		    'data'		=> array()
		);
		
		$defaults['template'] = $defaults['type'];
		
		$args  = wp_parse_args( $args, $defaults );
		
		$action = $this->actionName( $args['type'] );
		$nonce = $this->nonce( $action );
		$window_id = "wpas_{$action}_wrapper";
		
		?>

		<div class="wpas_tb_window">
			<div id="<?php echo $window_id; ?>" <?php echo ( ( true === $args['hidden'] ) ? 'style="display: none;"' : '' ); ?>>
	
				<div class="wpas_tb_window_wrapper" data-section="<?php echo $this->key; ?>">
					<?php wp_nonce_field( $nonce['action'], $nonce['name'] ); ?>

					<input type="hidden" class="wpas_pf_form_action" value="<?php echo $action; ?>" />

					<div class="wpas_pf_msg"></div>

					<?php include $this->templatePath( $args['template'] ); ?>
				</div>
	
			</div>
		</div>

		<?php
	}
	
	
	/**
	 * Return logged in user id if not provided
	 * 
	 * @param int $user_id
	 * 
	 * @return int
	 */
	protected function get_user( $user_id = 0 ) {
		
		if( 0 === $user_id || empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		
		return $user_id;
	}
	
	/**
	 * Return data user id
	 * 
	 * @param int $user_id
	 * 
	 * @return int
	 */
	protected function get_data_user( $user_id = 0 ) {
		
		if( 0 === $user_id || empty( $user_id ) ) {
			$user_id = $this->data_user;
		}
		
		return $user_id;
	}
	
	
	/**
	 * Adds new item based on data type set in child class
	 * 
	 * @param array $item
	 * @param int $user_id
	 * @param array $args Extra configuration
	 * 
	 * @return array
	 */
	public function add( $item, $user_id = 0, $args = array() ) {
		
		$user_id = $this->get_data_user( $user_id );
		
		$list = $this->getList( $user_id, $args );
		
		$id = '';
		do {
			$id = $this->generateID();
		} while ( array_key_exists( $id, $list ) );
		
		$list[ $id ] = $item;
		
		update_user_meta( $user_id, $this->key, $list );
		
		$item['id'] = $id;
		return $item;
	}
	
	/**
	 * Update existing item
	 * 
	 * @param array $item
	 * @param int $id
	 * @param int $user_id
	 * @param array $args Extra configuration
	 * 
	 * @return array
	 */
	public function update( $item, $id, $user_id = 0, $args = array() ) {
		
		$user_id = $this->get_data_user( $user_id );
		
		$list = $this->getList( $user_id, $args );
		$list[ $id ] = $item;
		
		update_user_meta( $user_id, $this->key, $list );
		
		return $list;
	}
	
	/**
	 * Delete an item
	 * 
	 * @param int $id
	 * @param int $user_id
	 * @param array $args Extra configuration
	 * 
	 * @return boolean
	 */
	public function delete( $id, $user_id = 0, $args = array() ) {
		
		$user_id = $this->get_data_user( $user_id );
		
		$list = $this->getList( $user_id, $args );
		unset( $list[ $id ] );
		update_user_meta( $user_id, $this->key, $list );
		
		return true;
	}
	
	public function item_exist( $item_id, $user_id = 0 ) {
		
		$item = $this->get_item( $item_id, $user_id );
		
		if( false === $item ) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * 
	 * @param int $item_id
	 * @param int $user_id
	 * @param array $args Extra configuration
	 * 
	 * @return array/boolean
	 */
	public function get_item( $item_id, $user_id = 0, $args = array() ) {
		$list = $this->getList( $user_id, $args );
		
		if( array_key_exists( $item_id, $list ) ) {
			return $list[ $item_id ];
		}
		
		return false;
	}
	
}