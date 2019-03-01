<?php


class WPAS_PF_Favorite_Ticket {
	
	protected static $instance = null;
	
	
	private $tickets = null;
	
	public function __construct() {
		
		add_filter( 'wpas_pf_general_setting_options',  array( $this, 'add_setting' ), 10, 1 ); // Add new setting under Tickets -> Settings page
		
		add_action( 'admin_notices',			array( $this, 'admin_notices' )		, 11, 0 );
		
		
		if( $this->is_active() ) {
			add_filter( 'post_row_actions',			 array( $this, 'wpas_ticket_action_row' ), 10, 2 );
			add_action( 'wpas_do_pf_favorite_ticket',	 array( $this, 'favorite_ticket' )	 , 11, 1 );
			add_action( 'wpas_do_pf_unfavorite_ticket',	 array( $this, 'unfavorite_ticket' )	 , 11, 1 );
			add_action( 'wpas_backend_ticket_content_after', array( $this, 'print_button' )		 , 11, 1 );
			add_action( 'wp_ajax_pf_do_favorite',		 array( $this, 'do_favorite' )		 , 11, 0 );
			add_action( 'wp_ajax_pf_do_unfavorite',		 array( $this, 'do_favorite' )		 , 11, 0 );
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
	 * print favorite/unfavorite success messages
	 */
	public function admin_notices() {

		if ( isset( $_GET['wpas-pf-message'] ) ) {

			switch ( $_GET['wpas-pf-message'] ) {

				case 'favorite':
					?>
					<div class="updated">
						<p><?php printf( __( 'The ticket #%s has been added to your favorites list.', 'wpas_productivity' ), intval( $_GET['post'] ) ); ?></p>
					</div>
					<?php
					break;

				case 'unfavorite':
					?>
					<div class="updated">
						<p><?php printf( __( 'The ticket #%s has been removed from your favorites list.', 'wpas_productivity' ), intval( $_GET['post'] ) ); ?></p>
					</div>
					<?php
					break;

			}

		}
	}
	
	
	/**
	 * Add ticket in favorites listing
	 * 
	 * @param array $data
	 * @return
	 */
	public function favorite_ticket( $data ) {
		
		if ( ! is_admin() ) {
			return;
		}

		if ( ! isset( $data['post'] ) ) {
			return;
		}

		$post_id = (int) $data['post'];
		
		$this->add_ticket( $post_id );
		
		
		$redirect_to = add_query_arg( array(
			'post_type'		=> 'ticket',
			'post'			=> $post_id,
			'wpas-pf-message'	=> 'favorite'
		), admin_url( 'edit.php' ) );
		

		wp_redirect( wp_sanitize_redirect( $redirect_to ) );
		exit;
	}
	
	/**
	 * Remove ticket in favorites listing
	 * 
	 * @param array $data
	 * @return
	 */
	public function unfavorite_ticket( $data ) {
		
		if ( ! is_admin() ) {
			return;
		}
		

		if ( ! isset( $data['post'] ) ) {
			return;
		}

		$post_id = (int) $data['post'];
		
		$this->remove_ticket( $post_id );
		
		
		$redirect_to = add_query_arg( array(
			'post_type'		=> 'ticket',
			'post'			=> $post_id,
			'wpas-pf-message'	=> 'unfavorite'
		), admin_url( 'edit.php' ) );
		
		
		wp_redirect( wp_sanitize_redirect( $redirect_to ) );
		exit;
	}
	
	/**
	 * Favorite/Unfavorite ticket
	 */
	public function do_favorite() {
		
		$ticket_id = filter_input( INPUT_POST, 'id' );
		$action = filter_input( INPUT_POST, 'action' );
		$nonce = filter_input( INPUT_POST, 'nonce' );
		
		$data = array();
		$success = false;
		
		$actions = array( 'pf_do_favorite', 'pf_do_unfavorite' );
		
		if( !in_array( $action, $actions ) ||  !wp_verify_nonce( $nonce, $action ) ) {
			$data['error'] = __( 'Sorry, we can\'t perform this action, try again later.', 'wpas_productivity' );
		} else {
			
			if( 'pf_do_favorite' === $action ) {
				$this->add_ticket( $ticket_id );
				$data['msg'] = __( 'Ticket has been added to your favorites list.', 'wpas_productivity' );
			} else {
				$this->remove_ticket( $ticket_id );
				$data['msg'] = __( 'Ticket has been removed from your favorites list.', 'wpas_productivity' );
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
	 * Add favorite link in action row
	 * 
	 * @param array $actions
	 * @param object $post
	 * @return array
	 */
	public function wpas_ticket_action_row( $actions, $post ) {
		
		if ( 'ticket' === $post->post_type ) {
			
			$url = add_query_arg( 'post_type', 'ticket', admin_url( 'edit.php' ) );

			if ( !$this->is_ticket_in_favorite( $post->ID ) ) {
				$favorite_url = wpas_do_url( $url, 'pf_favorite_ticket', array( 'post' => $post->ID ) );
				$actions['favorite'] = '<a href="' . $favorite_url . '">' . __( 'Favorite', 'wpas_productivity' ) . '</a>';
			} else {
				$unfavorite_url = wpas_do_url( $url, 'pf_unfavorite_ticket', array( 'post' => $post->ID ) );
				$actions['unfavorite'] = '<a href="' . $unfavorite_url . '">' . __( 'Unfavorite', 'wpas_productivity' ) . '</a>';
			}
		}

		return $actions;
	}
	
	/**
	 * Return favorite button link
	 * 
	 * @param int $post_id
	 * @return string
	 */
	public function button( $post_id ) {
		
		$button = "";
		$action = $this->is_ticket_in_favorite( $post_id ) ?  "pf_do_unfavorite" : "pf_do_favorite" ;
		$nonce = wp_create_nonce( $action );
		
		$button_text = 'pf_do_favorite' === $action ? __( 'Add this ticket to your favorites list', 'wpas_productivity' ) : __( 'Remove this ticket from your favorites list', 'wpas_productivity' );
		$button = '<a class="pf_favorite_btn" href="#" data-nonce="'.$nonce.'" data-action="'.$action.'" data-ticket="'.$post_id.'">' . $button_text . '</a>';
		
		return $button;
		
	}
	
	/**
	 * print favorite button
	 * 
	 * @param int $post_id
	 */
	public function print_button( $post_id ) {
		
		echo '<div class="wpas_pf_widget" id="wpas_favorite_button_widget">';
		echo $this->button( $post_id );
		
		echo '<div class="pf_msg">
			<p></p>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__( 'Dismiss this notice.', 'wpas_productivity' ).'</span></button>
		</div>';
		
		echo '</div>';
	}
	
	
	
	/**
	 * 
	 * Add "Enable feature" setting
	 * 
	 * @param array $settings
	 * 
	 * @return array
	 */
	public function add_setting( $settings ) {
		
		
		
		$settings[] = array(
			'name'    => __( 'Enable Favorite Tickets for Agents', 'wpas_productivity' ),
			'id'      => 'pf_favorite_active',
			'type'    => 'checkbox',
			'desc'    => __( 'Can an agent make a list of favorite tickets?', 'wpas_productivity' ),
			'default' => true
		);
		
		
		
		return $settings;
	}
	
	
	/**
	 * Check if favorite tickets feature is active
	 * 
	 * @param int $user_id
	 * @return boolean
	 */
	public function is_active() {
		
		if( ( true === (bool)wpas_get_option( 'pf_favorite_active', true ) ) ) {
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * Check if we should display favorite tickets metabox
	 * 
	 * @return boolean
	 */
	public static function should_display() {
		
		$_this = WPAS_PF_Favorite_Ticket::get_instance();
		$tickets = $_this->getTickets();
		
		if( $_this->is_active() && count( $tickets ) > 0 ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Get favorite tickets
	 * 
	 * @return array
	 */
	public function getTickets( $user_id = 0 ) {
		
		if( null === $this->tickets ) {
			
			$ids = $this->favorite_ids( $user_id );
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
	 * Check if a ticket is already in favorites list
	 * 
	 * @param int $ticket_id
	 * @param int $user_id
	 * @return boolean
	 */
	public function is_ticket_in_favorite( $ticket_id , $user_id = 0 ) {
		
		$ids = $this->favorite_ids( $user_id );
		
		if( in_array( $ticket_id, $ids ) ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Return a list of favorite tickets for a user
	 * 
	 * @param int $user_id
	 * @return array
	 */
	public function favorite_ids( $user_id = 0 ) {
		
		if( 0 === $user_id ) {
			$user_id = get_current_user_id();
		}
		
		$favorites = maybe_unserialize( get_user_meta( $user_id, 'favorite_tickets', true ) );
		$favorites = is_array( $favorites ) ? $favorites : array();
		
		return $favorites;
		
	}
	
	/**
	 * Add ticket in favorite listing
	 * 
	 * @param int $ticket_id
	 */
	public function add_ticket( $ticket_id ) {
		
		$user_id = get_current_user_id();
		$ids = $this->favorite_ids( $user_id );
		
		if( !$this->is_ticket_in_favorite( $ticket_id ) ) {
			$ids[] = $ticket_id;
			update_user_meta( $user_id, 'favorite_tickets', $ids );
		}
		
	}
	
	/**
	 * Remove ticket from favorites listing
	 * 
	 * @param int $ticket_id
	 */
	public function remove_ticket( $ticket_id ) {
		
		$user_id = get_current_user_id();
		$ids = $this->favorite_ids( $user_id );
		
		if( $this->is_ticket_in_favorite( $ticket_id ) ) {
			$key = array_search( $ticket_id, $ids );
			unset( $ids[ $key ] );
			update_user_meta( $user_id, 'favorite_tickets', $ids );
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