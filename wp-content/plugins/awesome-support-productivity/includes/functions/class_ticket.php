<?php


class WPAS_PF_Ticket {
	
	protected static $instance = null;
	
	
	public function __construct() {
		
		add_filter( 'wpas_pf_localize_script',		array( $this, 'localize_script' ), 11, 1 );
		
		add_action('wp_ajax_wpas_pf_save_inline_edit', array( $this, 'save_bulk_edit' ) );
		
		add_action( 'admin_init',			array( $this, 'admin_notices' ) ,    10, 0 );
		add_filter( 'bulk_actions-edit-ticket',		array( $this, 'add_bulk_actions' ), 11, 1 );
		add_filter( 'handle_bulk_actions-edit-ticket',	array( $this, 'handle_bulk_action_close_tickets' ), 10, 3 );
		
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
	
	
	public function localize_script( $script = array() ) {
		
		$statuses = wpas_get_post_status();
		$script['statuses'] = $statuses;
		return $script;
	}
	
	/**
	 * Get sendback url after bulk edit processed
	 * 
	 * @return string
	 */
	public function bulk_edit_sendback_url() {
		
		$pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
		$sendback = remove_query_arg( array('trashed', 'untrashed', 'deleted', 'locked', 'ids'), wp_get_referer() );
		if ( ! $sendback ) {
			$sendback = add_query_arg( array( 'post_type' => 'ticket', 'paged' => $pagenum ) , admin_url( 'edit.php' ) );
		}
		
		return $sendback;
	}
	
	/**
	 * Handle bulk edit request
	 */
	public function save_bulk_edit() {
		
		
		check_admin_referer('bulk-posts');
		
		
		$sendback = $this->bulk_edit_sendback_url();
		
		$data = $_POST;
		$custom_fields = array();
		
		$editable_bulk_custom_fields = array( 'wpas_assignee' );
		$fields = WPAS()->custom_fields->get_custom_fields();
		$post_IDs = array_map( 'intval', (array) $data['post'] );
		
		

		
		foreach( $post_IDs as $post_id ) { // Prepare custom fields data for each ticket
			
			$custom_fields[ $post_id ] = array();
		
			foreach ( $fields as $f_name => $field_args ) {

				$field = new WPAS_Custom_Field( $f_name, $field_args );

				$field_value = "";
				
				
				if( 'taxonomy' === $field->field_type ) {
					if( isset( $data['tax_input'] ) && isset( $data['tax_input'][ $f_name ] ) ) {
						$field_value = $this->bulk_edit_get_selected_term( $data['tax_input'], $f_name );
					}
				} else {
					
					if( in_array( "wpas_{$f_name}", $editable_bulk_custom_fields ) && isset( $data[ "wpas_{$f_name}" ] ) ) {
						$field_value = sanitize_text_field( $data[ "wpas_{$f_name}" ] );
					}
				}
				
				
				if( !$field_value ) {
					$field_value = $field->get_field_value( '', $post_id );
				}
				$custom_fields[ $post_id ][ "wpas_{$f_name}" ] = $field_value;
			}
		}
		
		
		/**
		 * We need to remove custom fields from post data array so wordpress won't process it as we have our own function to save such data
		 */
		foreach ( $fields as $f_name => $field_args ) {
			if( isset( $data['tax_input'][ $f_name ] ) ) {
				unset( $data['tax_input'][ $f_name ] );
			}
		}
		
		$done = bulk_edit_posts( $data );

		if ( is_array($done) ) {
			$done['updated'] = count( $done['updated'] );
			$done['skipped'] = count( $done['skipped'] );
			$done['locked'] = count( $done['locked'] );
			$sendback = add_query_arg( $done, $sendback );
		}
		
		/**
		 * Lets save custom fields data for each ticket in bulk edit list
		 */
		foreach( $custom_fields as $cf_data_post_id => $cf_data ) {
			WPAS()->custom_fields->save_custom_fields( $cf_data_post_id, $cf_data );
		}
		$sendback = remove_query_arg( array('action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status', 'post', 'bulk_edit', 'post_view'), $sendback );
		
		wp_send_json_success( array( 'location' => $sendback ) );
		die();
		
	}
	
	
	/**
	 * Return single selected value by user from bulk edit
	 * 
	 * @param array $data
	 * @param string $tax
	 * 
	 * @return string|int
	 */
	function bulk_edit_get_selected_term( $data, $tax ) {
		
		$term = 0;
		
		$terms = isset( $data[ $tax ] ) ? array_map( 'intval', (array) $data[ $tax ] ) : array();
		
		foreach( $terms as $tax_term_id ) {
			if( $tax_term_id ) {
				$term = $tax_term_id;
				continue;
			}
		}
		
		return $term;
	}
	
	
	/**
	 * Add close tickets bulk action
	 * 
	 * @param array $bulk_actions
	 * 
	 * @return array
	 */
	public function add_bulk_actions( $bulk_actions ) {

		$bulk_actions['wpas_pf_close_tickets'] = __( 'Close Tickets', 'wpas_productivity' );
		
		// Edit bulk action on top
		$bulk_actions = array_merge( array(
		    'edit' => __( 'Edit', 'wpas_productivity' )
		), $bulk_actions );
		
		return $bulk_actions;

	}

	/**
	 * Handle bulk action : Close Tickets
	 * 
	 * @param string $redirect_to
	 * @param srting $action
	 * @param array $post_ids
	 * 
	 * @return string
	 */
	public function handle_bulk_action_close_tickets( $redirect_to, $action, $post_ids ) {

		$closed = 0;

		if ( $action !== 'wpas_pf_close_tickets' || empty( $post_ids ) ) {
			return $redirect_to;
		}

		foreach( $post_ids as $ticket_id ) {
			wpas_close_ticket( $ticket_id );
			$closed++;
		}


		$redirect_to = add_query_arg( 'bulk_ticket_closed', $closed, $redirect_to );

		return $redirect_to;

	}
	
	/**
	 * Set admin notices on success or fail actions
	 */
	public function admin_notices() {
		
		if( isset( $_GET['bulk_ticket_closed'] ) ) {
			$_SERVER['REQUEST_URI'] = remove_query_arg( 'bulk_ticket_closed' );
			
			if ( 1 <= $_GET['bulk_ticket_closed'] ) {
				add_action( 'admin_notices', array( $this, 'bulk_ticket_close_success_notice' ) );
			} else {
				add_action( 'admin_notices', array( $this, 'bulk_ticket_close_failed_notice' ) );
			}
		} 
	}
	
	/**
	 * Print success bulk ticket close notice
	 */
	public function bulk_ticket_close_success_notice() {
		
		$class = 'updated notice notice-success is-dismissible';
		$message = __( 'Tickets closed', 'wpas_productivity' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
		
	}
	
	/**
	 * Print fail bulk ticket close notice
	 */
	public function bulk_ticket_close_failed_notice() {
		
		$class = 'notice notice-error is-dismissible';
		$message = __( 'Ticket close action failed', 'wpas_productivity' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
		
	}
	
	
}