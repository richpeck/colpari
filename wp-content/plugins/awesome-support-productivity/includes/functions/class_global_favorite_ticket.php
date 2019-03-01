<?php


class WPAS_PF_Global_Favorite_Ticket {
	
	protected static $instance = null;
	
	
	private $tickets = null;
	
	private $option = 'wpas_pf_hostlist';
	
	public function __construct() {
		
		if( $this->user_can_add() ) {
			
			add_filter( 'post_row_actions',				array( $this, 'wpas_ticket_action_row' ) , 10, 2 );
			add_action( 'wpas_do_pf_global_favorite_ticket',	array( $this, 'favorite_ticket' )	 , 11, 1 );
			add_action( 'wpas_do_pf_global_unfavorite_ticket',	array( $this, 'unfavorite_ticket' )	 , 11, 1 );
			add_action( 'wpas_backend_ticket_content_after',	array( $this, 'print_button' )		 , 10, 1 );
			add_action( 'wp_ajax_pf_do_global_favorite',		array( $this, 'do_favorite' )		 , 11, 0 );
			add_action( 'wp_ajax_pf_do_global_unfavorite',		array( $this, 'do_favorite' )		 , 11, 0 );
			add_action( 'admin_notices',				array( $this, 'admin_notices' )		 , 11, 0 );
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
	 * Check if current user can add tickets to global favorites list
	 * 
	 * @return boolean
	 */
	public function user_can_add() {
		
		if( current_user_can( 'administer_awesome_support' ) || current_user_can( 'add_tickets_to_hotlist' ) ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * print favorite/unfavorite success messages
	 */
	public function admin_notices() {

		if ( isset( $_GET['wpas-pf-message'] ) ) {

			switch ( $_GET['wpas-pf-message'] ) {

				case 'global_favorite':
					?>
					<div class="updated">
						<p><?php printf( __( 'The ticket #%s has been added to the hotlist.', 'wpas_productivity' ), intval( $_GET['post'] ) ); ?></p>
					</div>
					<?php
					break;

				case 'global_unfavorite':
					?>
					<div class="updated">
						<p><?php printf( __( 'The ticket #%s has been removed from the hotlist.', 'wpas_productivity' ), intval( $_GET['post'] ) ); ?></p>
					</div>
					<?php
					break;

			}

		}
	}
	
	
	/**
	 * Add ticket in global favorites listing
	 * 
	 * @param array $data
	 * @return
	 */
	public function favorite_ticket( $data ) {
		
		if ( ! is_admin() || ! isset( $data['post'] ) || !$this->user_can_add() ) {
			return;
		}


		$post_id = (int) $data['post'];
		
		$this->add_ticket( $post_id );
		
		
		$redirect_to = add_query_arg( array(
			'post_type'		=> 'ticket',
			'post'			=> $post_id,
			'wpas-pf-message'	=> 'global_favorite'
		), admin_url( 'edit.php' ) );
		

		wp_redirect( wp_sanitize_redirect( $redirect_to ) );
		exit;
	}
	
	/**
	 * Remove ticket from global favorites listing
	 * 
	 * @param array $data
	 * @return
	 */
	public function unfavorite_ticket( $data ) {
		
		if ( ! is_admin() || ! isset( $data['post'] ) || !$this->user_can_add() ) {
			return;
		}

		$post_id = (int) $data['post'];
		
		$this->remove_ticket( $post_id );
		
		
		$redirect_to = add_query_arg( array(
			'post_type'		=> 'ticket',
			'post'			=> $post_id,
			'wpas-pf-message'	=> 'global_unfavorite'
		), admin_url( 'edit.php' ) );
		
		
		wp_redirect( wp_sanitize_redirect( $redirect_to ) );
		exit;
	}
	
	/**
	 * Favorite/Unfavorite ticket
	 */
	public function do_favorite() {
		
		$ticket_id = filter_input( INPUT_POST, 'id' );
		$action    = filter_input( INPUT_POST, 'action' );
		$nonce     = filter_input( INPUT_POST, 'nonce' );
		
		$data = array();
		$success = false;
		
		$actions = array( 'pf_do_global_favorite', 'pf_do_global_unfavorite' );
		
		if( !in_array( $action, $actions ) ||  !wp_verify_nonce( $nonce, $action ) || !$this->user_can_add() ) {
			$data['error'] = __( 'Sorry, we can\'t perform this action, try again later.', 'wpas_productivity' );
		} else {
			
			if( 'pf_do_global_favorite' === $action ) {
				$this->add_ticket( $ticket_id );
				$data['msg'] = __( 'Ticket has been added to the hotlist.', 'wpas_productivity' );
			} else {
				$this->remove_ticket( $ticket_id );
				$data['msg'] = __( 'Ticket has been removed from the hotlist.', 'wpas_productivity' );
			}
			$data['button'] = $this->button( $ticket_id );
			$success = true;
			
		}
		
		if( $success ) {
			wp_send_json_success( $data );
		}
		
		wp_send_json_error( $data );
		exit;
	}
	
	/**
	 * Add global favorite link in action row
	 * 
	 * @param array $actions
	 * @param object $post
	 * @return array
	 */
	public function wpas_ticket_action_row( $actions, $post ) {
		
		if ( 'ticket' === $post->post_type ) {
			
			$url = add_query_arg( 'post_type', 'ticket', admin_url( 'edit.php' ) );

			if ( !$this->is_ticket_in_favorite( $post->ID ) ) {
				$favorite_url = wpas_do_url( $url, 'pf_global_favorite_ticket', array( 'post' => $post->ID ) );
				$actions['global_favorite'] = sprintf( '<a href="%s">%s</a>', $favorite_url, __( 'Hotlist', 'wpas_productivity' ) );
			} else {
				$unfavorite_url = wpas_do_url( $url, 'pf_global_unfavorite_ticket', array( 'post' => $post->ID ) );
				$actions['global_unfavorite'] = sprintf( '<a href="%s">%s</a>', $unfavorite_url, __( 'Remove Hotlist', 'wpas_productivity' ) );
			}
		}

		return $actions;
	}
	
	/**
	 * Return global favorite button link
	 * 
	 * @param int $post_id
	 * 
	 * @return string
	 */
	public function button( $post_id ) {
		
		$action = $this->is_ticket_in_favorite( $post_id ) ?  "pf_do_global_unfavorite" : "pf_do_global_favorite" ;
		$nonce = wp_create_nonce( $action );
		
		$button_text = 'pf_do_global_favorite' === $action ? __( 'Add this ticket to hotlist', 'wpas_productivity' ) : __( 'Remove this ticket from hotlist', 'wpas_productivity' );
		
		return sprintf('<a class="pf_global_favorite_btn" href="#" data-nonce="%s" data-action="%s" data-ticket="%s">%s</a>', $nonce, $action, $post_id, $button_text );
	}
	
	/**
	 * print favorite button
	 * 
	 * @param int $post_id
	 */
	public function print_button( $post_id ) {
		
		echo '<div class="wpas_pf_widget" id="wpas_global_favorite_button_widget">';
		echo $this->button( $post_id );
		
		echo '<div class="pf_msg">
			<p></p>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__( 'Dismiss this notice.', 'wpas_productivity' ).'</span></button>
		</div>';
		
		echo '</div>';
	}
	
	
	/**
	 * Check if we should display global favorite tickets metabox
	 * 
	 * @return boolean
	 */
	public static function should_display() {
		
		$_this = self::get_instance();
		$tickets = $_this->getTickets();
		
		if( count( $tickets ) > 0 ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Get global favorite tickets
	 * 
	 * @return array
	 */
	public function getTickets( $user_id = 0 ) {
		
		if( null === $this->tickets ) {
			
			$ids = $this->favorite_ids();
			if( empty( $ids ) ) {
				$this->tickets = array();
			} else {
				
				$args = array(
					'wpas_tickets_query'	=> 'listing',
					'order'			=> 'DESC',
					'post__in'		=> $ids
				);

				$this->tickets = wpas_get_tickets( 'any', $args );
				
			}
		}
		
		return $this->tickets;
	}
	
	/**
	 * Check if a ticket is already in global favorites list
	 * 
	 * @param int $ticket_id
	 * @param int $user_id
	 * @return boolean
	 */
	public function is_ticket_in_favorite( $ticket_id , $user_id = 0 ) {
		
		$ids = $this->favorite_ids();
		
		if( in_array( $ticket_id, $ids ) ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Return a list of global favorite tickets
	 * 
	 * @param int $user_id
	 * @return array
	 */
	public function favorite_ids() {
		
		$favorites = maybe_unserialize( get_option( $this->option, array() ) );
		$favorites = is_array( $favorites ) ? $favorites : array();
		
		return $favorites;
		
	}
	
	/**
	 * Add ticket in global favorite listing
	 * 
	 * @param int $ticket_id
	 */
	public function add_ticket( $ticket_id ) {
		
		$user_id = get_current_user_id();
		$ids = $this->favorite_ids();
		
		if( !$this->is_ticket_in_favorite( $ticket_id ) ) {
			$ids[] = $ticket_id;
			update_option( $this->option, $ids );
		}
		
	}
	
	/**
	 * Remove ticket from global favorites listing
	 * 
	 * @param int $ticket_id
	 */
	public function remove_ticket( $ticket_id ) {
		
		$user_id = get_current_user_id();
		$ids = $this->favorite_ids();
		
		if( $this->is_ticket_in_favorite( $ticket_id ) ) {
			$key = array_search( $ticket_id, $ids );
			unset( $ids[ $key ] );
			update_option( $this->option, $ids );
		}
		
	}
	
	
	
	/**
	 * Load whole view
	 * 
	 * @param int $post_id
	 * @param int $user_id
	 */
	public function display( $post_id = 0 ) {
		
		wp_enqueue_style( 'wpas-admin-styles' );
		
		echo '<div id="wpas_pf_ui_section_favorite_tickets" data-section="favorite_tickets" class="wpas_pf_ui_wrapper">';
		$this->items_listing();
		echo '</div>';
	}
	
	
	/**
	 * Load items view
	 * 
	 * @param int $user_id
	 */
	public function items_listing( $user_id = 0 ) {
		
		$tickets = $this->getTickets();
		
		?>
		
		<div id="wpas_pf_favorite_items" class="wpas_pf_data_items">
			<?php include WPAS_PF_PATH . 'includes/templates/favorite_tickets.php'; ?>
		</div>

		<?php
	}
	
	
	
}