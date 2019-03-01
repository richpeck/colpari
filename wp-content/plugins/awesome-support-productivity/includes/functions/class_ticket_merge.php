<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class WPAS_PF_Ticket_Merge {
	
	protected static $instance = null;
	
	public function __construct() {
		
		add_action( 'admin_init',				array( $this, 'admin_notices' ),    10, 0 );
		add_action( 'wp_ajax_wpas_merge_ticket',		array( $this, 'merge' ),	     	10, 1 );
		add_action( 'wpas_backend_reply_content_after',		array( $this, 'merge_from_note'),   10, 1 );
		add_action( 'wpas_backend_ticket_content_after',	array( $this, 'merge_to_note' ),    10, 2 );
		add_action( 'wp_ajax_wpas_get_tickets',			array( $this, 'get_tickets_ajax' ), 10, 0 );
		
		add_filter( 'bulk_actions-edit-ticket',			array( $this, 'add_bulk_action' ), 11, 1 );
		
		add_filter( 'handle_bulk_actions-edit-ticket',		array( $this, 'handle_bulk_action_merge' ), 10, 3 );
		add_action( 'wp_ajax_wpas_pf_multi_ticket_merge_view',	array( $this, 'multi_ticket_merge_view' ) );
		add_filter( 'wpas_pf_localize_script',			array( $this, 'localize_script' ), 11, 1 );
		
		add_action( 'wpas_after_ticket_merge_closed',		array( $this, 'notify_ticket_merge_closed' ), 11, 3 );
		add_action( 'wpas_after_ticket_merge_reply_added',	array( $this, 'notify_ticket_merge_reply_added' ), 11, 3 );
		
		add_filter( 'wpas_plugin_settings',			array( $this, 'add_email_settings'), 10, 1 );
		
		add_filter( 'wpas_email_notifications_template_tags',	array( $this, 'add_merge_tags'   ), 11, 1 ); // Register new tags relative to merge
		
		add_filter( 'wpas_email_notifications_tags_values',	array( $this, 'tag_values' ), 10, 2 );
		
	}
	
	
	/**
	 * Set email tag values
	 * 
	 * @param array $new
	 * @param int $post_id
	 * 
	 * @return array
	 */
	public function tag_values( $new, $post_id ) {
		
		foreach($new as $k => $tag) {
			if ( '{merge_target_id}' === $tag['tag'] ) {
				$new[ $k ]['value'] = get_post_meta( $post_id, 'merged_to', true );
			} elseif ( '{merge_target_url}' === $tag['tag'] ) {
				$new[ $k ]['value'] =  get_permalink( get_post_meta( $post_id, 'merged_to', true ) );
			} 
		}
		
		return $new;
	}
	
	
	/**
	 * Add merge tags
	 * 
	 * @return array
	 */
	public function add_merge_tags( $tags ) {
		
		$tags[] = array(
			'tag' 	=> '{merge_target_id}',
			'desc' 	=> __( 'Merge target id', 'wpas_productivity' )
		);
		
		$tags[] = array(
			'tag' 	=> '{merge_target_url}',
			'desc' 	=> __( 'Merge target ticket\'s url', 'wpas_productivity' )
		);
		
		return $tags;
	}
	
	/**
	* Add setting to customize the rejected e-mail reply notification e-mail
	*
	* @since 0.2.5
	*
	* @param array $settings Awesome Support settings array
	*
	* @return array
	*/
       public function add_email_settings( $settings ) {

	       if ( ! isset( $settings['email'] ) ) {
		       return $settings;
	       }


	       $settings['email']['options'][] = array(
		       'name' => __( 'Ticket Merged and Closed', 'as-email-support' ),
		       'type' => 'heading',
	       );
	       
		$settings['email']['options'][] = array(
			'name'    => __( 'Enable', 'awesome-support' ),
			'id'      => 'enable_ticket_merge_closed',
			'type'    => 'checkbox',
			'default' => true,
			'desc'    => __( 'Do you want to activate this e-mail template?', 'awesome-support' )
		);

	       $settings['email']['options'][] = array(
		       'name'    => __( 'Subject', 'as-email-support' ),
		       'id'      => 'subject_email_ticket_merge_closed',
		       'type'    => 'text',
		       'default' => __( 'Ticket merged and closed: {ticket_title}', 'as-email-support' ),
	       );

	       $settings['email']['options'][] = array(
		       'name'     => __( 'Content', 'as-email-support' ),
		       'id'       => 'content_email_ticket_merge_closed',
		       'type'     => 'editor',
		       'default'  => '<p>Hi <strong><em>{client_name},</em></strong></p><p>The ticket (<a href="{ticket_admin_url}">#{ticket_id}</a>) has been merged  with Ticket # <strong>{merge_target_id} </strong>and closed.</p><p>If you believe that the ticket should be re-opened, please log into your account on our site directly and manually re-open the ticket.</p>',
		       'settings' => array( 'quicktags' => true, 'textarea_rows' => 7 ),
	       );
	       
	       
	       $settings['email']['options'][] = array(
		       'name' => __( 'Ticket Merged and Reply Added', 'as-email-support' ),
		       'type' => 'heading',
	       );

	       $settings['email']['options'][] = array(
			'name'    => __( 'Enable', 'awesome-support' ),
			'id'      => 'enable_ticket_merge_reply_added',
			'type'    => 'checkbox',
			'default' => true,
			'desc'    => __( 'Do you want to activate this e-mail template?', 'awesome-support' )
		);
	       
	       $settings['email']['options'][] = array(
		       'name'    => __( 'Subject', 'as-email-support' ),
		       'id'      => 'subject_email_ticket_merge_reply_added',
		       'type'    => 'text',
		       'default' => __( 'Ticket merged and new reply added: {ticket_title}', 'as-email-support' ),
	       );

	       $settings['email']['options'][] = array(
		       'name'     => __( 'Content', 'as-email-support' ),
		       'id'       => 'content_email_ticket_merge_reply_added',
		       'type'     => 'editor',
		       'default'  => '<p>Hi <strong><em>{client_name},</em></strong></p><p>The ticket (<a href="{ticket_admin_url}">#{ticket_id}</a>) has had replies from other tickets merged into it.</p><p>If you believe that this has been done in error please log into your account on our site directly and respond to the ticket so an agent can address your concerns.</p>',
		       'settings' => array( 'quicktags' => true, 'textarea_rows' => 7 ),
	       );

	       return $settings;

       }
	
	
       /**
        * Send notification for ticket merge reply added
        * 
        * @param int $merge_id
        * @param int $target_id
        * @param array $source_ids
        */
	public function notify_ticket_merge_reply_added( $merge_id, $target_id, $source_ids ) {
		
		wpas_email_notify( $merge_id, 'ticket_merge_reply_added' );
		
	}
	
	/**
	 * Send notification after merge ticket closed
	 * 
	 * @param int $ticket_id
	 * @param int $target_id
	 * @param int $merge_id
	 */
	public function notify_ticket_merge_closed( $ticket_id, $target_id, $merge_id ) {
		
		wpas_email_notify( $ticket_id, 'ticket_merge_closed' );
	}
	
	/**
	 * Localize merge error messages 
	 * 
	 * @param array $script
	 * 
	 * @return array
	 */
	public function localize_script( $script = array() ) {
		
		$script['multi_merge_msgs'] = array(
		    'post_error'	  => __( 'Please select tickets to merge.', 'wpas_productivity' ),
		    'target_ticket_error' => __( 'Please select target ticket.', 'wpas_productivity' )
		);
		
		return $script;
	}
	
	
	/**
	 * Add merge bulk action
	 * 
	 * @param array $bulk_actions
	 * @return array
	 */
	public function add_bulk_action( $bulk_actions ) {
		
		$bulk_actions['wpas_pf_multi_ticket_merge'] = __( 'Merge Tickets', 'wpas_productivity' );
		return $bulk_actions;
		
	}
	
	
	/**
	 * Multi ticket merge window view
	 */
	public function multi_ticket_merge_view() {
		
		$selected_tickets = filter_input( INPUT_GET, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		
		$ticket_ids = array();
		
		foreach( (Array) $selected_tickets as $ticket_id) {
			if( $this->is_mergeable( $ticket_id ) ) {
				$ticket_ids[] = $ticket_id;
			}
		}
		
		
		$source_dropdown_args = array( 
		    'multiple'			=> true, 
		    'selected'			=> $ticket_ids,
		    'name'			=> 'wpas_merge_source_tickets[]',
		    'id'			=> 'wpas_merge_source_tickets_dd',
		    'filter_req_fields'		=> array( '#wpas_merge_target_ticket_dd' )
		);
		
		
		$target_dropdown_args = array( 
		    'name'			=> 'wpas_merge_target_ticket',
		    'id'			=> 'wpas_merge_target_ticket_dd',
		    'filter_req_fields'		=> array( '#wpas_merge_source_tickets_dd' )
		);
		
		
		?>

		<div class="multi_ticket_merge_view">
			
			<div class="wpas_pf_msg"></div>
			
			<div class="multi_ticket_merge_view_field">
				<div><label><?php _e('Select tickets to merge'); ?> <span class="description"><?php _e('(required)'); ?></span></label></div>
				<div><?php echo $this->tickets_dropdown( '', $source_dropdown_args ); ?></div>
			</div>

			<div class="multi_ticket_merge_view_field">
				<div><label><?php _e('Select target ticket'); ?> <span class="description"><?php _e('(required)'); ?></span></label></div>
				<div><?php echo $this->tickets_dropdown('', $target_dropdown_args ); ?></div>
			</div>
			
			<?php pf_tb_footer( 'Merge' ); ?>
		</div>

		<?php
		
		die();
	}
	
	
	/**
	 * Handle multi merge action
	 * 
	 * @global object $current_user
	 * @param string $redirect_to
	 * @param string $action
	 * @param array $post_ids
	 * 
	 * @return string
	 */
	public function handle_bulk_action_merge( $redirect_to, $action, $post_ids ) {
		global $current_user;
		
		$merged = '0';
		
		$target_id = filter_input( INPUT_GET, 'target_id' );
		
		if ( $action !== 'wpas_pf_multi_ticket_merge' || empty( $post_ids ) || !$target_id ) {
			return $redirect_to;
		}
		
		$source_ids = array();
		
		foreach ( $post_ids as $pid ) {
			if( $this->is_mergeable( $pid ) ) {
				$source_ids[] = $pid;
			}
		}
		
		if ( empty( $source_ids ) ) {
			return $redirect_to;
		}
		

		$target_ticket = get_post( $target_id );
		
		$merge_content = "";
		$attachments = array();
		
		foreach( $source_ids as $post_id ) {
			$merge_data = $this->get_merge_post_content( $post_id );
			
			$merge_content .= $merge_data['content'];
			$attachments = array_merge( $attachments, $merge_data['attachments'] );
		}

		$data = array(
			'post_name'      => sprintf( __( 'Merge Reply', 'wpas_productivity' ) ),
			'post_title'     => sprintf( __( 'Merge Reply', 'wpas_productivity' ) ),
			'post_content'   => $merge_content,
			'post_status'    => 'unread',
			'post_type'      => 'ticket_reply',
			'post_author'    => $current_user->ID,
			'ping_status'    => 'closed',
			'comment_status' => 'closed',
		);
		
		
		
		if( class_exists( 'WPAS_Notifications' ) ) {
			$WPAS_Notifications = WPAS_Notifications::get_instance();
			remove_action( 'wpas_add_reply_admin_after',  array( $WPAS_Notifications, 'new_reply_whoever' ), 10 );
		}
		
		
		// We don't want to send new reply added notification while adding a merged reply.
		remove_action( 'wpas_add_reply_complete', 'wpas_notify_reply', 10 );
		
		$merge_id = wpas_add_reply( $data, $target_id );
		
		if ( $merge_id ) {
			
			// Its good place to add attachments to the merged reply.
			$this->merge_attachments( $attachments, $merge_id );
			
			$emails = array();
			
			$pf_notification_email = WPAS_PF_Ticket_Notification_Email::get_instance();
			
			// Getting email addresses from source tickets to insert in target ticket, so they also receive notifications
			foreach ( $source_ids as $_id ) {
				$emails = array_merge( $emails, $this->get_ticket_emails( $_id ) );
			}
			
			// Adding email addresses to target ticket
			foreach ( $emails as $eml ) {
				if( !$pf_notification_email->item_exist_by_column( 'email', $eml['email'], $target_id ) ) {
					$pf_notification_email->add( $eml, $target_id );
				}
			}
			
			
			do_action( 'wpas_after_ticket_merge_reply_added', $merge_id, $target_id, $source_ids );
			
			$this->record_merge( $source_ids, $target_id, $merge_id );
			$merged = '1';
		}
		
		// Adding removed actions again as merged process is done.
		if( class_exists( 'WPAS_Notifications' ) ) {
			add_action( 'wpas_add_reply_admin_after',  array( $WPAS_Notifications, 'new_reply_whoever' ), 10, 2 );
		}
		add_action( 'wpas_add_reply_complete', 'wpas_notify_reply', 10, 2 );
		

		$redirect_to = add_query_arg( 'bulk_merged', $merged, $redirect_to );
		
		return $redirect_to;
		
	}
	
	/**
	 * Return ticket/reply attachments
	 * 
	 * @param int $id
	 * @return array
	 */
	public function get_attachments( $id ) {
		
		$attachments = get_posts( array(
		    'post_type' => 'attachment',
		    'posts_per_page' => -1,
		    'post_parent' => $id
		) );
		
		return $attachments;
	}
	
	/**
	 * Add attachments to merged reply
	 * 
	 * @param array $attachments
	 * @param int $merge_id
	 * 
	 * @return void
	 */
	public function merge_attachments( $attachments = array(), $merge_id ) {
		
		
		if ( empty( $attachments ) ) {
			return;
		}
		
		$wpas_file_upload = WPAS_File_Upload::get_instance();
		$wpas_file_upload->process_attachments( $merge_id, array() );
		
		$upload_dir = wp_get_upload_dir();
		
		foreach( $attachments as $att ) {
			
			$file_path = get_attached_file( $att->ID );
			$pathinfo = pathinfo( $file_path );
     
			do {
			    $new_filename = "{$pathinfo['filename']}_".rand( 111111,999999 ) . '_' . time() . ".{$pathinfo['extension']}";
			    $new_file_path = "{$upload_dir['path']}/{$new_filename}";
			} while ( file_exists( $new_file_path ) );
     
			$new_url = "{$upload_dir['url']}/{$new_filename}";
     
			if( !copy( $file_path, $new_file_path ) ) {
				continue;
			}
				
			$wp_filetype = wp_check_filetype( $new_filename, null );
			
			$attachment_data = array(
				'guid'           => $new_url,
				'post_mime_type' => $wp_filetype['type'],
				'post_parent'    => $merge_id,
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $new_filename ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			);
			
			$attachment_id = wp_insert_attachment( $attachment_data, $new_file_path, $merge_id );
			
			if ( !is_wp_error( $attachment_id ) ) {

				$attach_data = wp_generate_attachment_metadata( $attachment_id, $new_file_path );

				if ( ! empty( $attach_data ) ) {
					wp_update_attachment_metadata( $attachment_id, $attach_data );
				} else {
					$fileMeta = array(
						'file' => $new_file_path
					);
					add_post_meta( $attachment_id, '_wp_attachment_metadata', $fileMeta );
				}
			}
		}

	}
	
	/**
	 * Return user email
	 * 
	 * @param int $user_id
	 * 
	 * @return string
	 */
	public function get_user_email( $user_id ) {
		
		$email = "";
		
		$user = get_user_by( 'id', $user_id );
		if( $user && $user->user_email ) {
			$email = $user->user_email;
		}
		
		return $email;
		
	}
	
	/**
	 * Return ticket email addresses
	 * 
	 * @param int $ticket_id
	 * @return array
	 */
	public function get_ticket_emails( $ticket_id ) {
		
		$emails = array();
		$ticket = get_post( $ticket_id );
		
		$pf_notification_email = WPAS_PF_Ticket_Notification_Email::get_instance();
		$emails = $pf_notification_email->getList( $ticket_id );
		
		$just_emails = array_column( $emails, 'email' );
		
		$user_contact = WPAS_PF_Ticket_User_Contact::get_instance();
		$user_contact_listing = $user_contact->getList( $ticket_id );
		
		
		foreach( $user_contact_listing as $uc ) {
			$uc_user = get_user_by( 'id', $uc['user_id'] );
			if( $uc_user && $uc_user->user_email && !in_array( $uc_user->user_email, $just_emails ) )  {
				$emails[] = array( 'email' => $uc_user->user_email, 'active' => true );
				$just_emails[] = $uc_user->user_email;
			}
		}
		
		
		$customer_email = $this->get_user_email( $ticket->post_author );
		if( $customer_email && !in_array( $customer_email, $just_emails ) ) {
			$emails[] = array( 'email' => $customer_email, 'active' => true );
			$just_emails[] = $customer_email;
		}
		
		$first_addl_email = wpas_cf_value('first_addl_interested_party_email',  $ticket_id );
		$second_addl_email= wpas_cf_value('second_addl_interested_party_email', $ticket_id );
		
		
		if( $first_addl_email && !in_array( $first_addl_email, $just_emails ) ) {
			$emails[] = array( 'email' => $first_addl_email, 'active' => true );
			$just_emails[] = $first_addl_email;
		}
		
		if( $second_addl_email && !in_array( $second_addl_email, $just_emails ) ) {
			$emails[] = array( 'email' => $second_addl_email, 'active' => true );
			$just_emails[] = $second_addl_email;
		}
		
		return $emails;
	}
			
	
	/**
	 * Record merge
	 * 
	 * @param array $post_ids
	 * @param int/array $target_id
	 * @param int $merge_id
	 */
	function record_merge( $post_ids, $target_id, $merge_id ) {
					
		update_post_meta( $merge_id,  'merged_from', $post_ids );
				
		foreach( $post_ids as $tid ) {
			update_post_meta( $tid, 'merged_to',   $target_id );
			update_post_meta( $tid, 'merged_as',   $merge_id );
			
			remove_action( 'wpas_after_close_ticket', 'wpas_notify_close', 10 );
			
			wpas_close_ticket( $tid );
			do_action( 'wpas_after_ticket_merge_closed', $tid, $target_id, $merge_id );
			
			add_action( 'wpas_after_close_ticket', 'wpas_notify_close', 10, 3 );
		}
					
	}

	
	/**
	 * Get tickets using Ajax
	 */
	public function get_tickets_ajax() {
		
		$args = array();
		$result = array();
		
		$keyword = sanitize_text_field( $_POST['q'] );
		if( isset( $_POST['q'] ) ) {
			$args['s'] = $keyword;
		}
		
		require( WPAS_PATH . 'includes/admin/functions-post.php' );
		
		$tickets_result_1 = wpas_get_agent_tickets( $args );
		$tickets_result_2 = array();
		
		$args = array();
		if( is_numeric( $keyword ) ) {
			$args['post__in'] = array( $keyword );
			$tickets_result_2 = wpas_get_agent_tickets( $args );
		}
		
		$tickets = array_merge( $tickets_result_1, $tickets_result_2 );
		$processed_tickets = array();
		
		if ( count( $tickets ) > 0 ) {
			$ticket_id = filter_input( INPUT_POST, 'ticket_id' );
			$merge_target_ticket = filter_input( INPUT_POST, 'wpas_merge_target_ticket' );
			$merge_source_tickets = filter_input( INPUT_POST, 'wpas_merge_source_tickets', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			
			$exclude = array();
			
			if( $ticket_id ) {
				$exclude[] = $ticket_id;
			}
			if( $merge_target_ticket ) {
				$exclude[] = $merge_target_ticket;
			}

			$merge_source_tickets = $merge_source_tickets ? $merge_source_tickets : array();
			$exclude = array_merge( $exclude, $merge_source_tickets );
			
			
			$statuses = wpas_get_post_status();
			
			foreach ( $tickets as $ticket ) {
				
				if( in_array( $ticket->ID, $processed_tickets ) ) {
					continue;
				}
				
				$processed_tickets[] = $ticket->ID;
				
				if( !in_array( $ticket->ID, $exclude ) && $this->is_mergeable( $ticket->ID ) ) {

					$ticket_creator = $this->user_display_name( $ticket->post_author );
					
					$status = isset( $statuses[ $ticket->post_status ] ) ? $statuses[ $ticket->post_status ] : $ticket->post_status;

					$result[] = array(
					    'ticket_id'     => $ticket->ID,
					    'text' => '#'.$ticket->ID . ' - ' . $ticket_creator . ' - ' . $ticket->post_title . ' - ' . $status
					);
				}
			}
		}
		
		
		
		
		echo json_encode( $result );
		die();
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
	 * Register admin notices on success or fail merge
	 */
	public function admin_notices() {

		if( isset( $_GET['merged'] ) ) {
			$_SERVER['REQUEST_URI'] = remove_query_arg( 'merged' );
			
			if ( '1' === $_GET['merged'] ) {
				add_action( 'admin_notices', array( $this, 'merge_success_notice' ) );
			} elseif ( '0' === $_GET['merged'] ) {
				add_action( 'admin_notices', array( $this, 'merge_failed_notice' ) );
			}
		} elseif( isset( $_GET['bulk_merged'] ) ) {
			$_SERVER['REQUEST_URI'] = remove_query_arg( 'bulk_merged' );
			
			if ( '1' === $_GET['bulk_merged'] ) {
				add_action( 'admin_notices', array( $this, 'bulk_merge_success_notice' ) );
			} elseif ( '0' === $_GET['bulk_merged'] ) {
				add_action( 'admin_notices', array( $this, 'bulk_merge_failed_notice' ) );
			}
		}
	
	}
	
	/**
	 * Print fail merge notice
	 */
	public function merge_failed_notice() {
		
		$class = 'notice notice-error is-dismissible';
		$message = __( 'Ticket merge failed', 'wpas-ticket-merge' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
	}
	
	/**
	 * Print successful merge notice
	 */
	public function merge_success_notice() {
		
		$class = 'updated notice notice-success is-dismissible';
		$message = __( 'Ticket merged', 'wpas-ticket-merge' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
	}
	
	/**
	 * Print fail merge notice
	 */
	public function bulk_merge_failed_notice() {
		
		$class = 'notice notice-error is-dismissible';
		$message = __( 'Tickets merge failed', 'wpas-ticket-merge' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
	}
	
	/**
	 * Print successful merge notice
	 */
	public function bulk_merge_success_notice() {
		
		$class = 'updated notice notice-success is-dismissible';
		$message = __( 'Tickets successfully merged', 'wpas-ticket-merge' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
	}
	
	/**
	 * Get author name
	 * @param int $author
	 * 
	 * @return string
	 */
	public function user_display_name( $author ) {
		
		$user_name = __( 'Anonymous', 'wpas_productivity' );
		
		if ( $author != 0 && $author ) {
			$user_name = get_the_author_meta( 'display_name', $author );
		}
		
		return $user_name;
		
	}
	
	
	
	/**
	 * Check if ticket can be merged
	 * 
	 * @param int $ticket_id
	 * 
	 * @return boolean
	 */
	private function is_mergeable( $ticket_id ) {
		
		$ticket = get_post( $ticket_id );
		
		if( $ticket->post_type === 'ticket' && !$this->is_merged( $ticket_id ) ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Check if ticket is already merged
	 * 
	 * @param int $ticket_id
	 * 
	 * @return boolean
	 */
	private function is_merged( $ticket_id ) {
		
		$merged_id = get_post_meta( $ticket_id, 'merged_to',  true );
		
		if( empty( $merged_id ) ) {
			return false;
		}
		
		return $merged_id;
	}
	
	/**
	 * Check if agent have access to a ticket
	 * 
	 * @global object $current_user
	 * @param int $ticket_id
	 * @return boolean
	 */
	private function agent_has_access( $ticket_id ) {
		
		global $current_user;
		
		require( WPAS_PATH . 'includes/admin/functions-post.php' );
		
		if( wpas_can_user_see_all_tickets() ) {
			return true;
		}
		
		$agents = wpas_get_ticket_agents( $ticket_id );
		
		if( in_array( $current_user->ID, $agents ) ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Merge ticket
	 * 
	 * @global object $current_user
	 */
	public function merge() {
		
		global $current_user;
		
		
		$target_ticket = filter_input( INPUT_POST, 'target_ticket' );
		$ticket_id = filter_input( INPUT_POST, 'ticket_id' );
		
		$location_args = array( 'post' => $ticket_id, 'action' => 'edit', 'merged' => '0' );
		
		$merged = false;
		
		if( $this->is_mergeable( $ticket_id ) && $this->agent_has_access( $ticket_id ) ) {
			
			$merge_data = $this->get_merge_post_content( $ticket_id );
			
			$merge_content = $merge_data['content'];
			$attachments = $merge_data['attachments'];
			
			$data = array(
				'post_name'      => sprintf( __( 'Merge Reply %s', 'wpas_productivity' ), "#$ticket_id" ),
				'post_title'     => sprintf( __( 'Merge Reply %s', 'wpas_productivity' ), "#$ticket_id" ),
				'post_content'   => $merge_content,
				'post_status'    => 'unread',
				'post_type'      => 'ticket_reply',
				'post_author'    => $current_user->ID,
				'ping_status'    => 'closed',
				'comment_status' => 'closed',
			);
			
			
			$merge_id = wpas_add_reply( $data, $target_ticket );
			
			if ( $merge_id ) {
				
				$this->merge_attachments( $attachments, $merge_id );
				
				$this->record_merge( array( $ticket_id ), $target_ticket, $merge_id );
				$location_args['merged'] = '1';
				
				$merged = true;
			}
		}
		
		$result_data = array( 'location' => add_query_arg( $location_args, admin_url( 'post.php' ) ) );
		
		if( true === $merged ) {
			wp_send_json_success( $result_data );
		}
		
		wp_send_json_error( $result_data );
		
	}
	
	
	/**
	 * Prepare content for merge post
	 * 
	 * @param int $ticket_id
	 * 
	 * @return string
	 */
	public function get_merge_post_content( $ticket_id ) {
		
		$attachments = $this->get_attachments( $ticket_id );
		
		
		
		$ticket = get_post( $ticket_id );
		
		$replies = wpas_get_replies( $ticket_id );
		
		$merge_post_content = '<div class="wpas-pf-merged-content-wrapper">
			
		<div class="wpas-merged-main-post">
			
			<div class="wpas-merged-main-post-title"><h3>' . $ticket->post_title . '</h3></div>
			<div class="wpas-merged-main-post-meta">
				<div class="wpas-merged-main-post-user"><strong>'.$this->user_display_name( $ticket->post_author ).'</strong></div>
				<div class="wpas-merged-main-post-time">'
					. date( get_option( 'date_format' ), strtotime( $ticket->post_date ) ) . 
				'</div>
			</div>
			<div class="wpas-merged-main-post-content">'.$ticket->post_content.'</div>
		</div>';
		
		
		$merge_post_content .= '<table class="wpas-merged-replies">';
		
		foreach( $replies as $reply ) {
			
			$attachments = array_merge( $attachments, $this->get_attachments( $reply->ID ) );
			
			$merge_post_content .=
				'<tr class="wpas-table-row wpas-ticket-reply-merged ticket-reply-merged-'.$reply->ID.'">
					<td>
						<div class="wpas-reply-meta">
							<div class="wpas-reply-user"><strong>'.$this->user_display_name( $reply->post_author ).'</strong></div>
							<div class="wpas-reply-time">'
								. date( get_option( 'date_format' ), strtotime( $reply->post_date ) ) . 
							'</div>
						</div>
						<div class="wpas-reply-content">'.$reply->post_content.'</div>
					</td>
				</tr>';
		}
		
		$merge_post_content .= "</table></div>";
		
		
		return  array( 'content' => $merge_post_content, 'attachments' => $attachments );
	}
	
	/**
	 * Get dropdown html for target ticket selection
	 * 
	 * @param int $post_id
	 * 
	 * @return string
	 */
	private function tickets_dropdown( $post_id = '', $args = array() ) {
		
		
		$selected = isset( $args['selected'] ) && is_array( $args['selected'] ) ? $args['selected'] : array();
		
		
		$options = "";
		
		if( !empty( $selected) ) {
			
			require( WPAS_PATH . 'includes/admin/functions-post.php' );
			
			
			$tickets = wpas_get_agent_tickets( $args );
			
			$statuses = wpas_get_post_status();
			foreach ( $selected as $tid ) {
				$ticket = get_post($tid);
				
				if( $ticket ) {

					$ticket_creator = $this->user_display_name( $ticket->post_author );
					
					$status = isset( $statuses[ $ticket->post_status ] ) ? $statuses[ $ticket->post_status ] : $ticket->post_status;
					
					$options .= "<option selected=\"selected\" value=\"{$ticket->ID}\">".'#'.$ticket->ID . ' - ' . $ticket_creator . ' - ' . $ticket->post_title . ' - ' . $status."</option>";
				}
			}
			
		}
		
		
		$data_attr = array( 'opt-type' => 'ticket-picker' );
		
		$filter_req_fields = isset( $args['filter_req_fields'] ) ? $args['filter_req_fields'] : array();
		if( !empty( $filter_req_fields ) ) {
			$data_attr['filter_req'] = implode( ',', $filter_req_fields );
		}
		
		$staff_atts = array(
			'name'      => isset( $args['name'] ) ? $args['name'] : 'wpas_merge_ticket',
			'id'        => isset( $args['id'] )   ? $args['id']   : 'wpas-merge-ticket-dd',
			'select2'   => true,
			'data_attr' => $data_attr,
			'multiple' =>  isset( $args['multiple'] ) ? $args['multiple'] : false
		);
		
		$dropdown = wpas_dropdown( $staff_atts, $options );
		
		
		
		return $dropdown;
	}
	
	/**
	 * Add merge fields
	 * 
	 * @param int $post_id
	 */
	public function add_field_html( $post_id ) {
		
		
		echo '<div class="wpas-form-group" id="wpas_ticket_merge_wrapper">';
		
		$target_ticket_id = $this->is_merged( $post_id );
		
		if ( !empty( $target_ticket_id ) ) {
			$link = add_query_arg( array('post'   => $target_ticket_id, 'action' => 'edit' ), admin_url( 'post.php' ) );
			echo sprintf( 'This ticket is merged to <a href="%s">%s</a>', $link ,"#{$target_ticket_id}" );
			
		} else {
			$dropdown = $this->tickets_dropdown( $post_id );
		?>
			<div class="tm_title">
				<label><?php _e( 'Merge Ticket', 'wpas_productivity' ) ?></label>
				<div class="spinner"></div>
				<div class="clear clearfloats"></div>
			</div>
				
			<div class="tm_target_ticket_dd"><?php echo $dropdown; ?></div>
			<div class="tm_target_ticket_btn">
				<button type="button" id="btn-merge" class="button button-primary button-large"><?php _e( 'Merge', 'wpas_productivity' ) ?></button>
			</div>
			<div class="clear clearfloats"></div>
		<?php
		}
		
		echo '</div>';
	}
	
	
	/**
	 * Add internal note to merged post in reply
	 * 
	 * @param type $reply_id
	 */
	public function merge_from_note( $reply_id ) {
		
		$ticket_ids = maybe_unserialize( get_post_meta( $reply_id, 'merged_from', true ) );
		
		if( !empty( $ticket_ids ) ) {
			
			
			if( !is_array( $ticket_ids ) ) {
				$ticket_ids = array( $ticket_ids );
			}
			
			$links = array();
			
			foreach( $ticket_ids as $ticket_id ) {
				$link = add_query_arg( array( 'post' => $ticket_id, 'action' => 'edit' ), admin_url( 'post.php' ) );
				
				$links[] = sprintf( '<a href="%s">#%s</a>', $link, $ticket_id );
			}
			
			
			echo '<div class="note">';
			echo sprintf( __( 'Note : This entry is being merged from ticket number(s) %s' ), implode(', ', $links ) );
			echo "</div>";
		}
				
	}
	
	
	/**
	 * Add internal note to merged ticket
	 * 
	 * @param int $ticket_id
	 * @param object $ticket
	 */
	public function merge_to_note( $ticket_id, $ticket ) {
		
		$target_ticket_id = get_post_meta( $ticket_id, 'merged_to', true );
		
		if( !empty( $target_ticket_id ) ) {
			
			$link = add_query_arg( array( 'post' => $target_ticket_id, 'action' => 'edit' ), admin_url( 'post.php' ) );
			
			echo '<div class="note">';
			echo sprintf( __( 'Note : This ticket has been merged into ticket number <a href="%s">#%s</a>' ), $link, $target_ticket_id );
			echo "</div>";
		}
				
	}
}