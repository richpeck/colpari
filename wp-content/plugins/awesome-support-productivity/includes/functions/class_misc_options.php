<?php
/**
 * Implements the options in the new MISC options tab.
 * Options are declared in the settings-misc.php file.
 *
 * @package   Productivity
 * @author    Awesome Support <contact@awesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2017 Awesome Support
 */

class WPAS_PF_Misc_Options {
	
	protected static $instance = null;
	
	public function __construct() {
		
		add_filter( 'wpas_get_custom_fields', array( $this, 'wpas_pf_make_product_required' ), 10, 1 );											// Makes product field mandatory
		add_filter( 'wpas_get_custom_fields', array( $this, 'wpas_pf_make_dept_required' ), 10, 1 );											// Makes dept field mandatory
		add_filter( 'wpas_before_submit_new_ticket_checks', array( $this, 'wpas_pf_limit_concurrently_open_tickets' ), 10, 1 );					// Limit number of concurrent open tickets
		add_filter( 'wpas_before_submit_new_ticket_checks', array( $this, 'wpas_pf_limit_max_tickets' ), 10, 1 );								// Limit the total tickets a user can open (combined open and closed)
		add_filter( 'wpas_user_profile_tickets_open_limit', array( $this, 'wpas_pf_limit_open_tickets_in_user_profile_metabox' ), 10, 1 );		// Limit the number of open tickets that show up in the user profile metabox on the back end.
		add_filter( 'wpas_user_profile_tickets_closed_limit', array( $this, 'wpas_pf_limit_closed_tickets_in_user_profile_metabox' ), 10, 1 );	// Limit the number of closed tickets that show up in the user profile metabox on the back end.
		
		add_filter( 'wpas_subject_field_args', array( $this, 'wpas_pf_ticket_form_subject_default' ), 10, 1 );									// Set a default value for the subject field...
		add_filter( 'wpas_description_field_args', array( $this, 'wpas_pf_ticket_form_desc_default' ), 10, 1 );									// Set a default value for the description field...
		
		
		add_action( 'wpas_submission_form_inside_before_submit', array( $this,'wpas_pf_content_before_submit_button' ), 10, 1 ); 		// Output content before submit button
		add_action( 'wpas_submission_form_inside_before_subject', array( $this,'wpas_pf_content_before_subject_line' ), 10, 1 ); 		// Output content before subject line
		
		add_filter( 'wpas_ticket_attachments_field_args', array( $this, 'wpas_pf_set_attachments_label' ), 10, 1 );						// Set the label for the attachments field on the submit ticket form on the front end
		
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
	 * Make the products field required
	 *
	 * Filter Hook: wpas_get_custom_fields
	 *
	 * @param array $custom_fields Registered custom fields
	 *
	 * @return array
	 */
	function wpas_pf_make_product_required( $custom_fields ) {
		

		// Get the option value to see if we need to make the product field mandatory
		$mandatory_product = boolval( wpas_get_option( 'pf_make_product_mandatory' ) ); 

		if (true === $mandatory_product) {
			if ( isset( $custom_fields['product'] ) ) {
				$custom_fields['product']['args']['required'] = $mandatory_product;
			}
		}
		
		return $custom_fields;

	}
	
	/**
	 * Make the dept field required
	 *
	 * Filter Hook: wpas_get_custom_fields
	 *
	 * @param array $custom_fields Registered custom fields
	 *
	 * @return array
	 */
	function wpas_pf_make_dept_required( $custom_fields ) {
		

		// Get the option value to see if we need to make the department field mandatory
		$mandatory_dept = boolval( wpas_get_option( 'pf_make_department_mandatory' ) ); 
		
		if (true === $mandatory_dept) {
			if ( isset( $custom_fields['department'] ) ) {
				$custom_fields['department']['args']['required'] = $mandatory_dept;
			}
		}
		
		return $custom_fields;

	}	
	
	/**
	 * Limit the Number of Concurrent Open Tickets
	 * 
	 * Filter Hook: wpas_before_submit_new_ticket_checks
	 *
	 * @param bool|WP_Error $go Submission status
	 *
	 * @return bool|WP_Error
	 */	
	function wpas_pf_limit_concurrently_open_tickets( $go ) {
		
		// Get the option value to see how many tickets we can have open at a time
		$max_open_tickets = (int) wpas_get_option( 'pf_max_number_of_open_tickets' ) ; 
				
		$user_id      = get_current_user_id();

		$open_tickets = wpas_get_user_tickets( $user_id, 'open' );	// Array of users open tickets

		$count        = count( $open_tickets );

		if ( $count >= $max_open_tickets && $max_open_tickets > 0 ) {
			// Make sure $go is not already errored
			if ( ! is_wp_error( $go ) ) {
				$go = new WP_Error();
			}
			// Add a custom error message
			$go->add( 'too_many_open_tickets', sprintf( 'You can not have more than %1$d tickets open at the same time (you currently have %2$d open tickets). Please close your tickets before opening a new one.', $max_open_tickets, $count ) );
		}
		return $go;
	}	
	
	/**
	 * Limit the Maximum number of tickets a user can have open in their account.
	 * 
	 * Filter Hook: wpas_before_submit_new_ticket_checks
	 *
	 * @param bool|WP_Error $go Submission status
	 *
	 * @return bool|WP_Error
	 */		
	function wpas_pf_limit_max_tickets( $go ) {
		// Get the option value to see how many tickets the user can have in their account
		$max_tickets = (int) wpas_get_option( 'pf_max_tickets_per_user' ) ; 
		$error_msg = (string) wpas_get_option( 'pf_message_to_show_user_with_max_tickets' ) ;
				
		$user_id      = get_current_user_id();

		$ticket_count_open   = wpas_get_user_tickets( $user_id, 'open' );	// Array of users open tickets
		$ticket_count_closed = wpas_get_user_tickets( $user_id, 'closed' );	// Array of users closed tickets
		
		$count = count( $ticket_count_open ) + count( $ticket_count_closed );
		
		if ( $count >= $max_tickets && $max_tickets > 0 ) {
			// Make sure $go is not already errored
			if ( ! is_wp_error( $go ) ) {
				$go = new WP_Error();
			}
			// Add a custom error message
			$go->add( 'too_many_tickets_in_account', $error_msg );
		}
		return $go;		
	}
	
	/**
	*
	* Output custom text before the submit button
	* 	
	* Action Hook: wpas_submission_form_inside_before_submit
	* 
	*/
	function wpas_pf_content_before_submit_button () {
		// Get the option value containing the text to output
		$output_text = wpas_get_option( 'pf_content_before_submit_ticket_button' ) ;
		
		// Output the text....
		echo wp_kses_post($output_text) ;
	}
	
	/**
	*
	* Output custom text before the subject line 	
	* 	
	* Action Hook: wpas_submission_form_inside_before_subject
	* 	 
	*/
	function wpas_pf_content_before_subject_line () {
		// Get the option value containing the text to output
		$output_text = wpas_get_option( 'pf_content_before_subject_line' ) ;
		
		// Output the text....
		echo wp_kses_post($output_text) ;
	}	
	
	/**
	*
	* Limit the number of OPEN tickets that show up in the user profile metabox
	*
	* Filter Hook: wpas_user_profile_tickets_open_limit
	* 
	*/
	function wpas_pf_limit_open_tickets_in_user_profile_metabox () {
		// Get the option value containing the limit
		$output_limit = (int) wpas_get_option( 'pf_max_number_of_open_tickets_user_profile_widget' ) ;		
		
		return $output_limit ;
		
	}

	/**
	*
	* Limit the number of CLOSED tickets that show up in the user profile metabox
	*
	* Filter Hook: wpas_user_profile_tickets_closed_limit
	*
	*/
	function wpas_pf_limit_closed_tickets_in_user_profile_metabox () {
		// Get the option value containing the limit
		$output_limit = (int) wpas_get_option( 'pf_max_number_of_closed_tickets_user_profile_widget' ) ;		
		
		return $output_limit ;
		
	}
	
	/**
	*
	* Set a default value for the subject field
	*
	* Filter Hook: wpas_subject_field_args
	*
	*/
	function wpas_pf_ticket_form_subject_default ($subject_field_args) {
		
		$subject_field_args['args']['default'] = wpas_get_option('pf_ticket_form_default_subject');
		return $subject_field_args;
		
	}
	
	/**
	*
	* Set a default value for the description field
	*
	* Filter Hook: wpas_description_field_args
	*
	*/
	function wpas_pf_ticket_form_desc_default ($desc_field_args) {
		
		$desc_field_args['args']['default'] = wpas_get_option('pf_ticket_form_default_description');
		$desc_field_args['args']['sanitize'] = 'sanitize_textarea_field';
		return $desc_field_args;
		
	}
	
	/**
	*
	* Set a label for the attachment field on the front end ticket form
	*
	* Filter Hook: wpas_ticket_attachments_field_args
	*
	*/
	function wpas_pf_set_attachments_label ($attachment_field_args) {
		
		$attachment_field_args['args']['label'] = wpas_get_option('label_for_attachment', __( 'Attachments', 'wpas_productivity' ) );

		return $attachment_field_args;
		
	}	
				
}