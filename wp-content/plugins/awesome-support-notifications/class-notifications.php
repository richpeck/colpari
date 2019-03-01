<?php
class WPAS_Notifications {

	/**
	 * Instance of this class.
	 *
	 * @since    0.1.0
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Array of available services.
	 *
	 * @since  0.1.0
	 * @var    array
	 */
	protected $services;
	
	/**
	 * Array of notifications to send after ticket data saved
	 * 
	 * @var array
	 */
	protected $pending_notifications = array();

	public function __construct() {
		
		// Use this variable to ctivate some action hooks even if ajax is running	
		// Usually you want these deactivated but for testing the FETCH button option in EMAIL SUPPORT you want to turn this option on
		// so you can get new ticket / new assignment alerts!
		// Note also that some of these hooks are DUPLICATES of the ones involved when not doing AJAX
		$force_notifications_in_ajax = ( (int) wpas_get_option( 'notifications_ajax_alerts_for_email_support' ) );
		
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX || 1 === $force_notifications_in_ajax ) {

			$this->services  = apply_filters( 'wpas_notification_services', array(
					'slack',
					// 'pushover',
					'pushbullet',
				)
			);

			add_action( 'wpas_add_reply_public_after',	array( $this, 'new_reply_whoever' ),          10, 2 );    // New reply from the client
			add_action( 'wpas_add_reply_admin_after',	array( $this, 'new_reply_whoever' ),          10, 2 );    // New reply from the agent
			add_action( 'wpas_email_reply_converted',	array( $this, 'new_reply_whoever' ),          10, 2 );    // An unknown reply piped by the e-mail addon was just converted into a ticket reply
			add_action( 'wpas_after_close_ticket',		array( $this, 'ticket_closed' ),              10, 2 );    // Ticket status updated
			add_action( 'wpas_after_reopen_ticket',		array( $this, 'ticket_reopened' ),            10, 2 );    // Ticket status updated
			add_action( 'wpas_ticket_assigned',		array( $this, 'ticket_assigned' ),            10, 2 );    // New assignment
			add_action( 'show_user_profile',		array( $this, 'user_profile_custom_fields' ), 10, 1 );    // Add user preferences
			add_action( 'personal_options_update',		array( $this, 'save_user_custom_fields' ),    10, 1 );    // Save the user preferences
			add_filter( 'wpas_logs_handles',		array( $this, 'register_log_handle' ),        10, 1 );
			add_action( 'wpas_ticket_status_updated',	array( $this , 'ticket_status_changed'),      10, 3 );    // Ticket status changed
			

			add_action( 'wpas_add_reply_complete',		array( $this, 'process_notifications' ),      10, 2 );    // send reply notifications after attachments are processed
			
			add_action( 'wpas_email_processed',		array( $this, 'process_notifications' ),      10, 2 );    // send notifications after ticket added from email
			
			
			add_action( 'wpas_unknown_ticket_created',	array( $this, 'unknown_ticket_created' ) ); // Send notification on unknown ticket created
			
			if( is_admin() ) {
				add_action( 'save_post_ticket',		array( $this, 'process_notifications' ),      20, 3 );    // Process notifications
				add_action( 'post_updated',		array( $this, 'is_ticket_status_changed' ),   10, 3 );    // is ticket status chnaged after post update
			} else {
				add_action( 'wpas_open_ticket_after',	array( $this, 'process_notifications' ),      20, 2 );
			}
			
		}
	
	}
	
	/**
	 * checking is ticket status changed from backend
	 * @param type $post_ID
	 * @param object $post_after
	 * @param object $post_before
	 * 
	 * @return
	 */
	public function is_ticket_status_changed( $post_ID, $post_after, $post_before ) {

		if( $post_before->post_type != 'ticket' ) {
			return;
		}


		if( $post_after->post_status == $post_before->post_status ) {
			return;
		}
		
		// We need to call status change hook after ticket is saved so data is available for notifications
		add_action( 'save_post_ticket', array( $this, 'after_ticket_status_changed' ), 20, 3 );

		
        }
	
	/**
	 * Call ticket status change kook
	 * @param int $post_id
	 * @param object $post
	 * @param boolean $update
	 */
	public function after_ticket_status_changed( $post_id, $post, $update ) {
		
		remove_action( 'save_post_ticket', array( $this, 'after_ticket_status_changed' ), 20, 3 );
		do_action( 'wpas_ticket_status_updated', $post_id, $post->post_status, $update );
		
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     3.0.0
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function register_log_handle( $handles ) {
		array_push( $handles, 'notifications' );
		return $handles;
	}

	public function new_ticket( $ticket_id, $data ) {
		$this->trigger( $ticket_id );
	}
	
	public function unknown_ticket_created( $ticket_id ) {
		$this->trigger( $ticket_id, 'unassigned_ticket_created' );
	}

	public function ticket_status_changed( $post_id, $status, $updated ) {
		$this->trigger( $post_id, "status_ac_{$status}" );
	}

	/**
	 * Trigger a notification for a ticket reply
	 *
	 * Because the context will be assessed later on by the Notify class we can use the same method whoever just posted
	 * the reply.
	 *
	 * @since 0.1.6
	 *
	 * @param int   $reply_id ID of the reply that was just posted
	 *
	 * @return void
	 */
	public function new_reply_whoever( $reply_id ) {
		$this->pending_notifications[] = array( $reply_id, '' );
	}

	public function ticket_closed( $post_id, $updated ) {
		$this->pending_notifications[] = array( $post_id, 'ticket_closed' );
	}

	public function ticket_reopened( $post_id, $updated ) {
		$this->trigger( $post_id, 'ticket_reopened' );
	}

	public function ticket_assigned( $ticket_id, $agent_id ) {
		$this->pending_notifications[] = array( $ticket_id, '' );
	}
	
	/**
	 * Trigger delayed notifications.
	 * 
	 * Some notifications are queued up into an array to be sent AFTER all saving is done.
	 * This is because the custom fields data is saved as a separate step in core and 
	 * we need to wait until that is done before we send out certain notifications.
	 * 
	 * @param int $post_id
	 * @param object $post
	 * @param boolean $update
	 */
	public function process_notifications( $post_id, $post, $update = false ) {
		
		foreach( $this->pending_notifications as $notification ) {
			$this->trigger( $notification[0], $notification[1] );
		}
		
		$this->pending_notifications = array();
	}
	
	/**
	 * Trigger the notification.
	 *
	 * @since  0.1.0
	 * @param  integer        $post_id ID of the post to notify about
	 * @param  string         $context The notification context
	 * @return boolean|object          The result of the notification
	 */
	public function trigger( $post_id, $context = '' ) {

		$notified     = true;
		$notification = new WPAS_Notify( $post_id, $context );

		foreach ( $this->services as $service ) {

			if ( '1' == wpas_get_option( "notification_$service" ) ) {
				$ping = $notification->notify( $service );
				if ( !$ping ) {
					$notified = false;
				}
			}

		}

		return $notified;

	}

	/**
	 * Add user preferences to the profile page.
	 *
	 * @since  3.0.0
	 * @return void
	 */
	public function user_profile_custom_fields( $user ) { ?>

		<h3 id="wpas-pushbullet"><?php _e( 'Pushbullet Notifications', 'as-instant-notifications' ); ?></h3>

		<table class="form-table">
			<tbody>
				<tr class="wpas-after-reply-wrap">
					<th><label for="wpas_after_reply"><?php _e( 'Pushbullet Token', 'as-instant-notifications' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" name="wpas_notification_pushbullet_token" id="wpas_notification_pushbullet_token" value="<?php echo esc_attr( get_the_author_meta( 'wpas_notification_pushbullet_token', $user->ID ) ); ?>">
						<p class="description"><?php printf( __( 'Your Pushbullet access token. You can get it in <a href="%s" target="_blank">your account page</a>.', 'as-instant-notifications' ), esc_url( 'https://www.pushbullet.com/account' ) ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
	<?php }

	/**
	 * Save the user preferences.
	 *
	 * @since  3.0.0
	 * @param  integer $user_id ID of the user to modify
	 * @return void
	 */
	public function save_user_custom_fields( $user_id ) {

		if ( !current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		update_user_meta( $user_id, 'wpas_notification_pushbullet_token', $_POST['wpas_notification_pushbullet_token'] );

	}

	/**
	 * Add a link to the settings tab of the addon.
	 *
	 * @since  0.1.1
	 * @param  array $links Plugin links
	 * @return array        Links with the settings
	 */
	public static function settings_page_link( $links ) {

		$link    = add_query_arg( array( 'post_type' => 'ticket', 'page' => 'settings', 'tab' => 'notifications' ), admin_url( 'edit.php' ) );
		$links[] = "<a href='$link'>" . __( 'Settings', 'as-instant-notifications' ) . "</a>";

		return $links;

	}

}
