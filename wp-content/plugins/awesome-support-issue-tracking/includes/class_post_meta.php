<?php


abstract class WPAS_IT_Post_Meta {
	
	
	protected $key = '';
	
	public $base_template_path = WPAS_IT_PATH . 'includes/templates/';
	
	/**
	 * Data post id
	 * @var int 
	 */
	public $post_id;


	public function __construct() {
		
	}
	
	
	/**
	 * Return post id
	 * 
	 * @param int $post_id
	 * 
	 * @return int
	 */
	public function get_post_id( $post_id = 0 ) {
		
		if( !$post_id ) {
			$post_id = $this->post_id;
		}
		return $post_id;
	}
	
	/**
	 * Return all data items per type
	 * 
	 * @param int $post_id
	 * @param array $args Extra configuration
	 * 
	 * @return array
	 */
	public function getList( $post_id = 0, $args = array() ) {
		
		$post_id = $this->get_post_id( $post_id );
		
		$items = array();
		
		if( $post_id ) {
			$items = maybe_unserialize( get_post_meta( $post_id , $this->key, true ) );
		}
		
		return ( !is_array( $items ) ? array() : $items ) ;
	}
	
	/**
	 * Return items view file path based on data type
	 * 
	 * @return string
	 */
	public function itemsTemplate() {
		return $this->base_template_path . $this->key . '/items.php';
	}
	
	/**
	 * Return single item view file path based on data type
	 * 
	 * @return string
	 */
	public function itemTemplate() {
		return $this->base_template_path . $this->key . '/item.php';
	}
	
	/**
	 * Return a template file path based on template name and data type
	 * 
	 * @param string $template
	 * 
	 * @return string
	 */
	public function templatePath( $template ) {
		return $this->base_template_path . "{$this->key}/{$template}.php";
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
		return "it_{$type}_{$this->key}";
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
		    'submit_text'	=> __( 'Add', 'wpas_it' ),
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
	
				<div class="wpas_it_tb_window_wrapper" data-section="<?php echo $this->key; ?>">
					<?php wp_nonce_field( $nonce['action'], $nonce['name'] ); ?>

					<input type="hidden" class="wpas_it_form_action" value="<?php echo $action; ?>" />

					<div class="wpas_it_msg"></div>

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
	 * Adds new item based on data type set in child class
	 * 
	 * @param array $item
	 * @param int $post_id
	 * @param array $args Extra configuration
	 * 
	 * @return array
	 */
	public function add( $item, $post_id = 0, $args = array() ) {
		
		$post_id = $this->get_post_id( $post_id );
		
		$list = $this->getList( $post_id, $args );
		
		$id = '';
		do {
			$id = $this->generateID();
		} while ( array_key_exists( $id, $list ) );
		
		$list[ $id ] = $item;
		
		update_post_meta( $post_id, $this->key, $list );
		
		$item['id'] = $id;
		return $item;
	}
	
	/**
	 * Update existing item
	 * 
	 * @param array $item
	 * @param int $id
	 * @param int $post_id
	 * @param array $args Extra configuration
	 * 
	 * @return array
	 */
	public function update( $item, $id, $post_id = 0, $args = array() ) {
		
		$post_id = $this->get_post_id( $post_id );
		
		$list = $this->getList( $post_id, $args );
		$list[ $id ] = $item;
		
		update_post_meta( $post_id, $this->key, $list );
		
		return $list;
	}
	
	/**
	 * Delete an item
	 * 
	 * @param int $id
	 * @param int $post_id
	 * @param array $args Extra configuration
	 * 
	 * @return boolean
	 */
	public function delete( $id, $post_id = 0, $args = array() ) {
		
		$post_id = $this->get_post_id( $post_id );
		
		$list = $this->getList( $post_id, $args );
		unset( $list[ $id ] );
		update_post_meta( $post_id, $this->key, $list );
		
		return true;
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
		
		if( false === $item ) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Check if item exists by data column name
	 * 
	 * @param string $column_name
	 * @param string $column_value
	 * @param int $post_id
	 * 
	 * @return boolean
	 */
	public function item_exist_by_column( $column_name, $column_value , $post_id = 0 ) {
		
		
		$items = $this->getList( $post_id );
		
		foreach( $items as $item ) {
			if( isset( $item[ $column_name ] ) &&  $column_value === $item[ $column_name ] ) {
				return true;
			}
		}

		
		return false;
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
		$list = $this->getList( $post_id, $args );
		
		if( array_key_exists( $item_id, $list ) ) {
			return $list[ $item_id ];
		}
		
		return false;
	}
	
}