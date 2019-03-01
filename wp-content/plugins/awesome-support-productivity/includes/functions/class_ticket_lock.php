<?php


/**
 * Add ticket lock feature
 */
class WPAS_PF_Ticket_Lock {
	
	protected static $instance = null;
	
	public function __construct() {
		
		add_action( 'init',	array( $this, 'add_custom_field' ) );																// Register the ticket-lock custom field
		add_action( 'wpas_before_customer_reopen_ticket', array( $this, 'deny_reopen_ticket' ) );								// Check to see if ticket should be reopened
		add_action( 'wpas_after_close_ticket', array ($this , 'wpas_pf_auto_lock_ticket' ), 10, 3 );							// Automatically lock ticket when closed		
		
		add_action( 'wpas_system_tools_table_after', array ( $this, 'wpas_pf_add_mark_all_tickets_lock_button'), 10, 0 );		// Add a button to mark all closed tickets as locked to the TOOLS page
		add_action( 'wpas_system_tools_table_after', array ( $this, 'wpas_pf_add_mark_all_tickets_unlocked_button'), 10, 0 );	// Add a button to mark all closed tickets as unlocked to the TOOLS page		
		add_action('execute_additional_tools', array ( $this, 'wpas_pf_execute_tools' ),10,2);									// The function that evaluates the button pressed from the TOOLS page
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
	 * Add ticket lock field
	 * 
	 * @param int $post_id
	 */
	public function add_field_html( $post_id ) {
		
		$value = (int) wpas_get_cf_value( 'ticket_lock', $post_id );
		$value = $value === 1 ? '1' : '0';
		
		?>

		<div class="wpas-form-group" id="wpas_ticket_lock_wrapper">
			<input type="hidden" name="wpas_ticket_lock" id="wpas_ticket_lock" value="<?php echo $value; ?>" />
			<p>
				<label for="wpas_ticket_lock_cb">
					<input id="wpas_ticket_lock_cb" type="checkbox" <?php checked($value); ?> class="wpas-form-control" /> 
					<?php _e( 'Lock Ticket', 'wpas_productivity' ); ?>
				</label>
			</p>
		</div>
		
		<?php
	}
	
	/**
	 * Stop opening ticket if its locked
	 * 
	 * @param int $ticket_id
	 */
	public function deny_reopen_ticket( $ticket_id ) {
		
		if( !is_admin() ) {
			
			$user_id = get_current_user_id();
			$ticket = get_post( $ticket_id );
			$locked = (int) wpas_get_cf_value('ticket_lock', $ticket_id) === 1 ? true : false;
			
			if ( $ticket && (int) $ticket->post_author === $user_id && $locked ) {
				wpas_add_error( 'cannot_reopen_ticket', __( 'This ticket has been locked and cannot be replied to. Please open a new ticket', 'wpas_productivity' ) );
				wpas_redirect( 'ticket_reopen', wpas_get_tickets_list_page_url() );
				exit;
			}
		}
	}
	
	/**
	* Automatically lock tickets when they are closed.
	*
	* @param int $ticket_id
	* @param mixed $update - int reprsents the id of a new post_meta value, true/false indicates whether the close flag was successfully updated in the parent.
	* @param int $user_id
	*/
	public function wpas_pf_auto_lock_ticket ( $ticket_id, $update, $user_id ) {
		// Get the option value to see if we need to automatically lock the ticket on close
		$auto_lock_on_close = boolval( wpas_get_option( 'pf_auto_lock_ticket_on_close' ) ); 
		
		If ( true === $auto_lock_on_close ) {
			
			if ( 'ticket' == get_post_type( $ticket_id ) ) {
			
				update_post_meta( $ticket_id, '_wpas_ticket_lock', '1' );

			}		
		}		
	}	


	/**
	* Add the button to the TOOLS page that allows for marking all closed tickets as locked.
	*/
	public function wpas_pf_add_mark_all_tickets_lock_button () { ?>
		<tr>
			<td class="row-title"><label for="tablecell"><?php _e( 'Lock all closed tickets', 'wpas_productivity' ); ?></label></td>
			<td>
				<a href="<?php echo wpas_tool_link( 'lock_closed_tickets' ); ?>" class="button-secondary"><?php _e( 'Lock Tickets', 'wpas_productivity' ); ?></a>
				<span class="wpas-system-tools-desc"><?php _e( 'All closed tickets will be LOCKED - users will not be able to reopen them.', 'wpas_productivity' ); ?></span>
			</td>
		</tr>
	<?php }

	
	/**
	* Add the button to the TOOLS page that allows for marking all closed tickets as NOT locked.
	*/
	public function wpas_pf_add_mark_all_tickets_unlocked_button () { ?>
		<tr>
			<td class="row-title"><label for="tablecell"><?php _e( 'Unlock all closed tickets', 'wpas_productivity' ); ?></label></td>
			<td>
				<a href="<?php echo wpas_tool_link( 'unlock_closed_tickets' ); ?>" class="button-secondary"><?php _e( 'Unlock Tickets', 'wpas_productivity' ); ?></a>
				<span class="wpas-system-tools-desc"><?php _e( 'All closed tickets will be UNLOCKED - users will be able to reopen them at any time.', 'wpas_productivity' ); ?></span>
			</td>
		</tr>
	<?php }
	

	/** 
	* Hook into the TOOLS script in the core plugin to execute the choice the user made
	*/
	function wpas_pf_execute_tools ( $thecase ){
		
		switch ( $thecase ) {
			
			/* lock all closed tickets */
			case 'lock_closed_tickets';
				$this->wpas_pf_lock_or_unlock_all_closed_tickets('1');
				break;	
				
			/* unlock all closed tickets */
			case 'unlock_closed_tickets';
				$this->wpas_pf_lock_or_unlock_all_closed_tickets('0');
				break;				
		}
	}	

	/**
	* Lock or unlock all closed tickets
	* 
	* @param string $lockstatus - Update the ticket lock flag to whatever is passed in here, usually a 1 (locked) or 0 (unlocked)
	*/
	private function wpas_pf_lock_or_unlock_all_closed_tickets($lockstatus = '0') {
		$args = array(
				'post_type'              => 'ticket',
				'post_status'            => 'any',
				'posts_per_page'         => -1,
				'no_found_rows'          => true,
				'cache_results'          => false,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'meta_key'		 => '_wpas_status',
				'meta_value'		 => 'closed'
			);

			$query   = new WP_Query( $args );
			$reset = true;
			
			if ( 0 == $query->post_count ) {
				return false;
			}

			
			foreach( $query->posts as $post ) {
				update_post_meta( $post->ID, '_wpas_ticket_lock', $lockstatus );
			}
			
			return $reset;			
	}
	
	/**
	 * Register ticket lock custom field
	 */
	public function add_custom_field() {
		
		wpas_add_custom_field( 'ticket_lock', array(
		    'core'		=> false,
		    'show_column'	=> false,
		    'hide_front_end'	=> true,
		    'log'		=> false,
		    'title'		=> __( 'Ticket Lock', 'wpas_productivity' )
		) );
		
	}
	
}