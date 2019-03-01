<?php
/**
 * Awesome Support Integration.
 *
 * @package  ASWC_WC_Integration
 * @category Integration
 */

class ASWC_WC_Integration extends WC_Integration {

	/**
	 * Init and hook in the integration.
	 */
	public function __construct() {
		global $woocommerce;

		$this->id                 = 'awesome-support';
		$this->method_title       = __( 'Awesome Support', 'awesome-support-woocommerce' );
		$this->method_description = __( 'Perfectly integrate Awesome Support with WooCommerce.', 'awesome-support-woocommerce' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->allow_non_customer 	= $this->get_option( 'allow_non_customer' );

		// Actions.
		add_action( 'woocommerce_update_options_integration_' .  $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'admin_init', array( $this, 'grant_ticket_caps' ) );
		add_action( 'wp_head', array( $this, 'allow_non_customers' ) );

		// Customer "My Orders" actions.
		add_action( 'woocommerce_view_order', array( $this, 'view_order_create_conversation' ), 40 );
		add_action( 'woocommerce_my_account_my_orders_actions', array( $this, 'orders_actions' ), 10, 2 );
		
		if ( version_compare( WC()->version, '2.6.0', '>=') ) {
			include_once plugin_dir_path( __FILE__ ) . 'wc/class-aswc-wc-account-endpoint.php';
		} else {
			add_action( 'woocommerce_after_my_account', array( $this, 'my_account_conversations_table' ), 10 );
		}
		
		// Scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );

	}

	/**
	 * Settings Wrapper
	 *
	 * @return array
	 */

	public function wrapper() {

		$settings = array(
			'allow_non_customer' => $this->allow_non_customer,
		);

		return $settings;

	}


	/**
	 * Front-end scripts.
	 *
	 * @return void
	 */
	public function frontend_scripts() {

		if ( is_account_page() ) {

			wp_enqueue_style( 'wpas-theme-styles' );
			wp_enqueue_style( $this->id . '-myaccount-styles', plugins_url( 'assets/css/frontend/myaccount-page.css', plugin_dir_path( __FILE__ ) ), array(), ASWC_Init::VERSION, 'all' );

		}

	}


	/**
	 * Initialize integration settings form fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'allow_non_customer' => array(
				'title'       => __( 'Allow Non-Customers', 'awesome-support-woocommerce' ),
				'type'        => 'checkbox',
				'description' => __( 'Checking this option will allow non-customers to open a ticket on a Submit Ticket page.', 'awesome-support-woocommerce' ),
				'desc_tip'    => false,
				'default'     => ''
			),
		);
	}

	/**
	 * Grant ticket capabilities to all customers
	 */

	public function grant_ticket_caps() {

		// gets the customer role
    	$role = get_role( 'customer' );

    	// adds the capabilities
    	$role->add_cap( 'view_ticket' );
    	$role->add_cap( 'close_ticket' );
    	$role->add_cap( 'create_ticket' );
    	$role->add_cap( 'reply_ticket' );
    	$role->add_cap( 'attach_files' );

	}

	/**
	 * Allow non customers
	 */

	public function allow_non_customers() {

		global $post;
      	wp_get_current_user();

		if ( ( $this->allow_non_customer !== 'yes' ) && ( $post->ID == wpas_get_option( 'ticket_submit' ) ) ) {
			if ( is_user_logged_in() ) {
				$allow = true;
			} else {
				$allow = false;
			}
		} else {
			$allow = true;
		}

		if ( $allow ) {
			add_filter( 'wpas_can_submit_ticket', '__return_true' );
		} else {
			add_filter( 'wpas_can_submit_ticket', '__return_false' );
		}

	}

	/**
	 * Create conversation form.
	 *
	 * @param  int    $order_id Order ID.
	 *
	 * @return string           Conversation HTML form.
	 */
	public function view_order_create_conversation( $order_id ) {
		include_once( 'views/html-order-create-conversation.php' );
	}

	/**
	 * Added support button in order actions.
	 *
	 * @param  array    $actions Order actions.
	 * @param  WC_Order $order   Order data.
	 *
	 * @return array
	 */
	public function orders_actions( $actions, $order ) {
		global $woocommerce;

		$order_url = $order->get_view_order_url();

		/**
		 * Only show the button if the order has an allowed status.
		 * Default: completed, processing, on-hold.
		 */
		$allowed_statuses = apply_filters( 'aswc_help_allowed_statuses', array( 'completed', 'processing', 'on-hold' ) );

		if ( in_array( $order->get_status(), $allowed_statuses ) ) {
			$actions[ $this->id ] = array(
				'url'  => $order_url . '#start-conversation',
				'name' => __( 'Get Help', 'awesome-support-woocommerce' )
			);
		}

		return $actions;
	}

	/**
	 * Display a table with the user conversations in My Account page.
	 *
	 * @return string Tickets table.
	 */
	public function my_account_conversations_table() {
		global $current_user;

		echo '<h2 id="start-conversation">' . __( 'My Tickets', 'awesome-support-woocommerce' ) . '</h2>';

		echo do_shortcode( '[tickets]' );

	}

}