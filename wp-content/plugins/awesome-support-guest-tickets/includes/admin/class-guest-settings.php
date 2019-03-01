<?php
namespace AS_Guest_Tickets\Admin;

/**
 * Implements the Guest Tickets settings panel and settings.
 *
 * @since 1.0.0
 */
class Settings {

	/**
	 * Starts up the Settings instance.
	 *
	 * @access public
	 * @since  1.0
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initializes the Guest Tickets settings panel.
	 *
	 * @access public
	 * @since  1.0
	 */
	public function init() {
		add_filter( 'wpas_plugin_settings', array( $this, 'general_settings' ), 100 );
	}

	/**
	 * Registers 'General' settings for the Guest Tickets panel.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param array $settings Array of existing settings.
	 * @return array<string,array> Filtered Awesome Support settings.
	 */
	public function general_settings( $settings ) {
		$settings['guest-tickets'] = array(
			'name'    => __( 'Guest Tickets', 'as-guest-tickets' ),
			'options' => array(
				array(
					'name'    => __( 'Thank You Page', 'as-guest-tickets' ),
					'id'      => 'gt_thank_you_page',
					'type'    => 'select',
					'desc'    => __( 'Choose a page to redirect guest users to after submitting a ticket.','as-guest-tickets' ),
					'default' => '',
					'options' => wpas_list_pages()
				),
				array(
					'name'    => __( 'Who Should Receive New User Notifications?', 'as-guest-tickets' ),
					'id'      => 'gt_notify_users',
					'type'    => 'radio',
					'options' => array( 'none' => __('No One','as-guest-tickets'), 'user' => __('Only The Customer','as-guest-tickets'), 'admin' => __('Only The Site Admin','as-guest-tickets'), 'both' => __('Customer and Admin','as-guest-tickets') ),
					'default' => 'both,',
				),
				array(
					'name'    => __( 'Do Not Send WordPress Password Email To Customer', 'as-guest-tickets' ),
					'id'      => 'gt_no_password_email_all',
					'type'    => 'custom',
					'custom' =>  __( 'The above option applies only to new guest ticket users. WordPress will automatically send all other new users a password reset email.  To prevent all other new WordPress users from receiving password reset emails <b>regardless of how the user was added</b>, please add a <b> define ( ‘ASGT_NO_NEW_USER_CONFIRMATION’, true ); </b> statement in your wp-config.php file. <p>Note that other plugins may override this choice so if adding that statement does not work then please disable your other plugins to see if their presence is interferring with this function!</p>','as-guest-tickets' ),
				),
				array(
					'name'    => __( 'Leave guest user logged in', 'as-guest-tickets' ),
					'id'      => 'gt_leave_guest_user_logged_in',
					'type'    => 'checkbox',
					'desc'    => __( 'Do not automatically log out the guest user from their new account', 'as-guest-tickets' ),
					'default' => false
				),
				array(
					'name'     => __( 'Email address description', 'as-guest-tickets' ),
					'id'       => 'gt_reg_email_desc',
					'type'     => 'text',
					'desc'    => __( 'This text will show up underneath the email address field on the guest login area of the user registration form', 'as-guest-tickets' ),					
					'default'  => '',
				),
				array(
					'name'     => __( 'Privacy Notice: Short Description', 'as-guest-tickets' ),
					'id'       => 'gt_privacy_notice_short_desc_01',
					'type'     => 'text',
					'default'  => '',
					'desc'     => __( 'If you fill this in, a mandatory checkbox will be added in the registration form. Users won\'t be able to register if they don\'t tick the checkbox.  It is best to keep this brief - eg: Receive Emails? or Join Email List?', 'as-guest-tickets' ),
				),
				array(
					'name'     => __( 'Privacy Notice: Long Description', 'as-guest-tickets' ),
					'id'       => 'gt_privacy_notice_long_desc_01',
					'type'     => 'editor',
					'default'  => '',
					'desc'     => __( 'If you add notice terms in this box, a mandatory checkbox will be added in the registration form. Users won\'t be able to register if they don\'t accept these notice terms.  It is best to keep this notice to one or two lines.', 'as-guest-tickets' ),
					'settings' => array( 'quicktags' => true, 'textarea_rows' => 7 )
				),
				array(
					'name'     => __( 'Privacy Notice: Error Message', 'as-guest-tickets' ),
					'id'       => 'gt_privacy_notice_err_msg_01',
					'type'     => 'text',
					'default'  => 'You must agree to the privacy notice',
					'desc'     => __( 'If the user did not check the box they will be shown this message', 'as-guest-tickets' ),
				),		
				array(
                    'name' => __( 'Google reCAPTCHA', 'as-guest-tickets' ),
                    'type' => 'heading'
				),		
				array(
					'name'    => __( 'Use Google reCAPTCHA', 'as-guest-tickets' ),
					'id'      => 'gt_use_recaptcha',
					'type'    => 'checkbox',
					'desc'    => __( 'Add an extra layer of security to login form using Google Invisible reCAPTCHA', 'as-guest-tickets' ),
					'default' => false
				),
				array(
					'name'     => __( 'Site key', 'as-guest-tickets' ),
					'id'       => 'gt_recaptcha_site_key',
					'type'     => 'text'
				),
				array(
					'name'     => __( 'Secret key', 'as-guest-tickets' ),
					'id'       => 'gt_recaptcha_secret_key',
					'type'     => 'text'
				),
			),
		);

		return $settings;
	}

}
