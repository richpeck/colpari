<?php

/**
 * Handle Next and Previous link in edit ticket page
 */
class WPAS_PF_Ticket_Navigate {
	
	protected static $instance = null;
	
	public function __construct() {
		
		add_action( 'wpas_backend_ticket_status_content_before', array( $this, 'add_buttons' ) );
		
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
	 * print next, previous links
	 * 
	 * @param int $post_id
	 */
	public function add_buttons( $post_id ) {
		
		if( !isset( $this->back_ticket ) ) {
			$this->back_ticket = wpas_get_previous_ticket( $post_id );
		}
		
		if( !isset( $this->next_ticket) ) {
			$this->next_ticket = wpas_get_next_ticket( $post_id );
		}
		
		$back_link = "";
		$next_link = "";
		
		if( $this->back_ticket ) {
			$back_link = add_query_arg( array( 'post'   => $this->back_ticket, 'action' => 'edit'), admin_url( 'post.php' ) );
		}
		
		if( $this->next_ticket ) {
			$next_link = add_query_arg( array( 'post'   => $this->next_ticket, 'action' => 'edit'), admin_url( 'post.php' ) );
		}
		
		?>

		<div class="wpas-row wpas-status-navigation">

			<div class="wpas-col previous">
				<?php if( !empty( $back_link ) ) : 
				echo sprintf( '<a href="%s">%s</a>', $back_link, __( 'Previous Ticket', 'wpas_productivity' ) );
				endif; ?>
			</div>
			<div class="wpas-col next">
				<?php if( !empty( $next_link ) ) : 
				echo sprintf( '<a href="%s">%s</a>', $next_link, __( 'Next Ticket', 'wpas_productivity' ) );
				endif; ?>
			</div>
		</div>

		<?php
	}
	
}