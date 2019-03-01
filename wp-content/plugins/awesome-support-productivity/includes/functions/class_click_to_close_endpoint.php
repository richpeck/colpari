<?php
/**
 * Implements options related to custom endpoints including
 * the following:
 * 	1. An endpoint to allow the close of a ticket with just one click.
 *
 * @package   Productivity
 * @author    Awesome Support <contact@awesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2017 Awesome Support
 */

class WPAS_PF_Click_to_Close_Endpoint {
	
	protected static $instance = null;
	
	public function __construct() {
		
		add_action( 'wpas_open_ticket_after', array( $this, 'create_ticket_close_endpoint_hash' ), 8, 2 );					// Add a field to the ticket to hold an endpoint hash for the click-to-close url
		add_action( 'wpas_after_reopen_ticket', array( $this, 'create_ticket_close_endpoint_hash' ), 8, 2 );				// Use the same function as above if the ticket is reopened.
		
		if ( true === boolval( wpas_get_option( 'pf_enable_automatic_close_email_template_tag_link' ) ) ) {
			// These items are very cpu intensive so only define them if the click_to_view option is turned on!
			add_action( 'init', array( $this, 'click_to_close_rewrite_endpoint' ), 10 );										// Define the click-to-close URL base slug
			add_filter( 'the_content', array( $this, 'click_to_close_contents' ), 9 );											// Replace the ticket contents with the click-to-close data...
			add_filter( 'wpas_email_notifications_template_tags', array( $this, 'email_notifications_template_tags' ), 10, 1 ); // Display valid tag values in the emails tab
			add_filter( 'wpas_email_notifications_tags_values', array( $this, 'email_notifications_tags_values' ), 10, 2 );		// Replace valid tag values as necessary
		}
		
		add_action( 'wpas_after_close_ticket', array( $this, 'clear_click_to_close_hash' ), 10, 3 );						// Clear the hash that forms the url when the ticket is closed.
		
		
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
	 * Adds a field to the ticket to hold an md5 hash that will act as the final portion of the ticket close endpoint.
	 *
	 * Action Hook: wpas_open_ticket_after
	 *
	 * @since 2.2.0
	 *
	 * @param int $ticket_id the id of the ticket or post object
	 * @param array $ticket the post object with the ticket contnets
	 */		 
	 public function create_ticket_close_endpoint_hash( $ticket_id, $ticket ) {
		 
		$the_hash = wpas_pf_random_hash();

		/** Save hash that will act as the last part of the URL */
		add_post_meta( $ticket_id, '_wpas_pf_ticket_close_hash', $the_hash, true );		 

	 }
	 
	 /**
	 * When ticket is closed clear the hash.
	 *
	 * Action Hook: wpas_after_close_ticket
	 *
	 * @since 2.2.0
	 *
	 * @param int $ticket_id the id of the ticket or post object
	 * @param array $ticket the post object with the ticket contnets
	 */		 
	 public function clear_click_to_close_hash( $ticket_id, $update, $user_id ) {

		 if ( true === boolval( wpas_get_option( 'pf_clear_close_link_after_close' ) ) ) {

			delete_post_meta( $ticket_id, '_wpas_pf_ticket_close_hash' );	
			
		 }
	 }
	 
	 /**
	 * Define the click-to-close end point
	 *
	 * Action Hook: init
	 *
	 * @since 2.2.0
	 *
	 */	
	 public function click_to_close_rewrite_endpoint() {
		 if ( true === boolval( wpas_get_option( 'pf_enable_automatic_close_email_template_tag_link' ) ) ) {
			add_rewrite_endpoint( $this->get_click_to_close_rewrite_slug(), EP_PERMALINK | EP_PAGES );
		 }
	 }
	 
	 /**
	 * Replace the ticket content being accessed with the custom contents in this routine that confirms the ticket was closed.
	 *
	 * Filter Hook: the_contents
	 *
	 * @since 2.2.0
	 *
	 */		 
	 public function click_to_close_contents( $content ) {
		 	 
		// Make sure this is a ticket
		if ( ! is_main_query() || ! is_singular( 'ticket' ) ) {
			return $content;
		}

		// Declare unavoidable globals
		global $wp_query, $post;
		
		if ( isset( $wp_query->query_vars[ $this->get_click_to_close_rewrite_slug() ] ) ) {

			// Declare some variables to be used later
			$ticket_id 	= $post->ID; // set variable $ticket_id because its clearer than the generic $post->ID.
			$client		= get_userdata( $post->post_author );
			$client_id 	= $client->ID;		

		
			// Don't show the ticket!
			remove_filter( 'the_content', 'wpas_single_ticket' );
			
			// Make sure that there is a hash value provided in the URL
			$the_hash = filter_input(INPUT_GET, 'the_hash', FILTER_SANITIZE_STRING);
			if ( ! isset( $the_hash ) || empty( $the_hash ) ) {
				// hash is empty so set content and move on...
				$content = __('Security Error: Required hash does not exist - ticket cannot be closed!', 'wpas_productivity') ;
				return $content;
			}
			
			// Make sure that the hashes compare properly!
			if ( $the_hash <> get_post_meta( $ticket_id, '_wpas_pf_ticket_close_hash', true ) ) {
				// hash does not compare so set content and move on...
				$content = __('Security Error: Incorrect security hash was passed - ticket cannot be closed!', 'wpas_productivity') ;
				return $content;			
			}

			// At this point we have to log the user in silently...
			$user_was_already_logged_in = false ;
			if ( $client_id === get_current_user_id() ) {
				$user_was_already_logged_in = true ;
			}
			
			if ( ! version_compare( WPAS_VERSION, '4.0.4', '>=' ) ) { 
				// need to login user if AS version is before 4.0.4
				if( ! $user_was_already_logged_in ) {
					// These are likely to throw a warning in the debug.log file:  Cannot modify header information - headers already sent by...
					wp_clear_auth_cookie();  
					wp_set_current_user( $client_id );
					wp_set_auth_cookie( $client_id );
					update_user_caches( $client );
				}			
			}			

			// Close ticket here
			if ( ! version_compare( WPAS_VERSION, '4.0.4', '>=' ) ) { 
				// pre AS 4.0.4 did not have the third parameter to the close function.
				$ticket_close_status = wpas_close_ticket( $ticket_id, $client_id ) ;
			}
			else {
				$ticket_close_status = wpas_close_ticket( $ticket_id, $client_id, true )  ;
			}
			
			if (true === $ticket_close_status ) {
				// Show success message
				$content = wpas_get_option( 'pf_one_step_click_to_close_post_close_success_message', __('This ticket was successfully closed!', 'wpas_productivity') ) ;
			} else {
				// show failure message
				$content = wpas_get_option( 'pf_one_step_click_to_close_post_close_failure_message', __('Unfortunately this ticket could not be closed - maybe it was already closed?', 'wpas_productivity') ) ;
			}
			
			// Log user out
			if ( ! version_compare( WPAS_VERSION, '4.0.4', '>=' ) ) { 
				// need to logout user if AS version is before 4.0.4
				if ( ! $user_was_already_logged_in ) {
					if ( ! boolval( wpas_get_option( 'pf_keep_user_logged_in_post_close' ) ) ) {
						wp_logout();
					}
				}
			}
				
			return $content ;
			
		} else {
			// Not a lot you can do here.
			// Just realize that if you're here something is seriously wrong...
		}
		
		return $content ;
	 }
	 
	 /**
	 * Returns the slug to be used for the click-to-close endpoint.
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	 public function get_click_to_close_rewrite_slug() {
		 return apply_filters( 'wpas_pf__click_to_close_endpoint_slug', wpas_get_option( 'pf_one_step_click_to_close_slug', 'click-to-close' ) );
	 }
	 

	/**
	 * Get the click-to-close URL for a specific ticket
	 *
	 * @since 2.2.0
	 *
	 * @param int    $ticket_id   ID of the ticket to close
	 * @param string $querystring Additional parameters appended to URL ( ie: '?thash=..." )
	 *
	 * @return string
	 */
	function wpas_pf_get_click_to_close_link( $ticket_id, $querystring = '' ) {
		return esc_url( get_permalink( $ticket_id ) . $this->get_click_to_close_rewrite_slug() . $querystring );
	}

	/**
	 * Email Notification: Show valid template tags in the help tab of the emails settings tab.
	 *
	 * @since 2.2.0
	 *
	 * Filter hook: wpas_email_notifications_template_tags
	 *
	 * @param $tags array - existing list of tags.  We add to this list and return it.
	 *
	 * @return array[]
	 *
	 */
	public function email_notifications_template_tags( $tags ) {

		$tags[] = array(
			'tag'  => '{click_to_close_url}',
			'desc' => __( 'Displays the URL <strong>only</strong> (not an actual link) for the click-to-close link', 'wpas_productivity' ),
		);
		$tags[] = array(
			'tag'  => '{click_to_close_link}',
			'desc' => __( 'Displays a link to the click-to-close page', 'wpas_productivity' ),
		);

		return $tags;
	}
	
	/**
	 * Email Notification: Translate Tag Values
	 *
	 * @since  2.2.0
	 *
	 * Filter hook: wpas_email_notifications_tags_values
	 *
	 * @param $new
	 *
	 * @param $post_id integer - ticket id or reply id
	 *
	 * @return mixed
	 *
	 */
	public function email_notifications_tags_values( $new, $post_id ) {

		// Get an instance of the post...
		$the_post = get_post($post_id);

		// Get the ticket id depending on what type of post we have - reply or ticket..
		$ticket_id = '' ;
		switch ( get_post_type( $the_post ) ) {
			case 'ticket_reply':
				$ticket_id = $the_post->post_parent;
				break ;

			case 'ticket':
				$ticket_id = $post_id;
				break;

			default:
				$ticket_id = '' ;
		}
		
		// Now that we have the ticket id we can translate the tags...
		if (! empty($ticket_id) ) {
	
			// We can get multiple tags in the $new variable so need to loop through and process each one...
			foreach ( $new as $key => $tag ) {

				$name       = trim( $tag['tag'], '{}' );
				$the_hash      = '?the_hash=' . get_post_meta( $ticket_id, '_wpas_pf_ticket_close_hash', true );
				$survey_url = $this->wpas_pf_get_click_to_close_link( $ticket_id, $the_hash );

				switch ( $name ) {

					case 'click_to_close_link':
						$tag['value'] = '<a href="' . $survey_url . '">' . __( 'Click Here To Close This ticket in one step - just one click is required.', 'wpas_productivity') . '</a>';
						break;

					case 'click_to_close_url':
						$tag['value'] = $survey_url;
						break;

				}

				$new[ $key ] = $tag;
			}
		}

		return $new;

	}	
	
}