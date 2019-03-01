<?php

/**
 * Handle Recently closed tickets
 */
class WPAS_PF_Recently_Closed {
	
	protected static $instance = null;
	
	/**
	 * Max number of recently closed tickets
	 * 
	 * @var int
	 */
	protected $limit = 10;
	
	/**
	 * Recently closed tickets
	 * 
	 * @var array
	 */
	protected $tickets = null;
	
	
	public function __construct() {
		
		add_action( 'wpas_start_ticket_listing' , array( $this, 'add_custom_fields' ),    11, 0 );
		add_action( 'wpas_end_ticket_listing' ,   array( $this, 'remove_custom_fields' ), 11, 0 );
		
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
	 * @param boolean $should_add
	 * @return boolean
	 */
	public function should_add_custom_fields( $should_add = false ) {
		global $pagenow;
		
		if ( false === $should_add ) {
			
			if( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
				$should_add = true;
			}
			
		}
		
		return $should_add;
	}
	
	
	/**
	 * Add custom fields relative to ticket listing
	 */
	public function add_custom_fields( ) {
		
		add_filter( 'add_ticket_column_custom_fields',	array( $this, 'should_add_custom_fields'), 11, 1 );
		
		$fields = WPAS()->custom_fields->get_custom_fields();
		WPAS_Tickets_List::get_instance()->add_custom_fields( $fields );
		
	}
	
	/**
	 * Remove custom fields relative to ticket listing
	 */
	public function remove_custom_fields() {
		
		WPAS()->custom_fields->remove_field( 'id' );
		WPAS()->custom_fields->remove_field( 'author' );
		WPAS()->custom_fields->remove_field( 'wpas-activity' );
	}
	
	
	/**
	 * Load whole view
	 * 
	 * @param int $post_id
	 * @param int $user_id
	 */
	public function display( $post_id = 0 ) {
		wp_enqueue_style( 'wpas-admin-styles' );
		
		echo '<div id="wpas_pf_ui_section_recently_closed" data-section="recently_closed" class="wpas_pf_ui_wrapper">';
		$this->items_listing();
		echo '</div>';
	}
	
	
	
	/**
	 * Load items view
	 * 
	 * @param int $user_id
	 */
	public function items_listing() {
		
		$tickets = $this->getTickets();
		
		?>
		
		<div id="wpas_pf_recently_closed_items" class="wpas_pf_data_items">
			<?php include WPAS_PF_PATH . 'includes/templates/recently_closed_tickets.php'; ?>
		</div>

		<?php
	}
	
	/**
	 * Check if we should display recently tickets metabox
	 * 
	 * @return boolean
	 */
	public static function should_display() {
		
		$_this = WPAS_PF_Recently_Closed::get_instance();
		$recently_closed_tickets = $_this->getTickets();
		
		if( count( $recently_closed_tickets ) > 0 ) {
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * Get recently closed tickets
	 * 
	 * @return array
	 */
	public function getTickets() {
		
		
		if( null === $this->tickets ) {
			
			$args = array(
				'wpas_tickets_query'	=> 'listing',
				'meta_key'		=> '_ticket_closed_on',
				'orderby'		=> 'meta_value',
				'order'			=> 'DESC',
				'posts_per_page'	=> $this->limit
			);
		
			$this->tickets = wpas_get_tickets( 'closed', $args );
		}
		
		return $this->tickets;
	}
}