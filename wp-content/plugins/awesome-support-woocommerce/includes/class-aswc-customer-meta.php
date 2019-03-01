<?php
/**
 * Awesome Support Customer Meta from WooCommerce.
 *
 * @package  ASWC_Customer_Meta
 * @category Integration
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class ASWC_Customer_Meta {

    protected static $instance = null;

    function __construct() {
	
		if ( is_admin() ) {

			add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );

			// Ticket Customer Metabox
			add_action( 'add_meta_boxes', array( $this, 'add_ticket_metabox' ) );

			// Order Ticket Metabox
			add_action( 'add_meta_boxes', array( $this, 'add_order_metabox' ) );
		
		} 

    }

	/**
	 * Start the Class when called
	 *
	 * @package Awesome Support WooCommerce
	 * @since   1.0.0
	 */

	public static function get_instance() {
	  // If the single instance hasn't been set, set it now.
	  if ( null == self::$instance ) {
		self::$instance = new self;
	  }
	  return self::$instance;
	}

	/**
	 * Assets
	 */
	public function assets() {

		$current_screen = get_current_screen();

		wp_register_style( 'aswc-ticket-screen', plugins_url( 'assets/css/admin/ticket-screen.css', plugin_dir_path( __FILE__ ) ), array(), ASWC_Init::VERSION );
		wp_register_style( 'aswc-order-screen', plugins_url( 'assets/css/admin/order-screen.css', plugin_dir_path( __FILE__ ) ), array(), ASWC_Init::VERSION );
		
		if ( $current_screen->id == 'ticket' ) {
			wp_enqueue_style( 'aswc-ticket-screen' );
		}

		if ( $current_screen->id == 'order' ) {
			//wp_enqueue_style( 'aswc-order-screen' );
		}

	}

	/**
	 * Adds a metabox for tickets with customer profile
	 *
	 * @return void
	 */
	public function add_ticket_metabox() {

		add_meta_box(
			'aswc-ticket-customer-profile',
			__( 'Customer Profile', 'awesome-support-woocommerce' ),
			array( $this, 'ticket_metabox_content' ),
			'ticket',
			'side',
			'high'
		);

	}

	/**
	 * Adds a metabox for orders with ticket(s) info
	 *
	 * @return void
	 */
	public function add_order_metabox() {

		add_meta_box(
			'aswc-order-ticket-info',
			__( 'Order Tickets', 'awesome-support-woocommerce' ),
			array( $this, 'order_metabox_content' ),
			'shop_order',
			'side',
			'core'
		);

	}

	/**
	 * Content for the metabox for tickets with customer profile
	 *
	 * @return void
	 */
	public function ticket_metabox_content( $post ) {

		include_once( 'views/html-admin-ticket-meta.php' );

	}

	/**
	 * Content for the metabox for orders with ticket(s) info
	 *
	 * @return void
	 */
	public function order_metabox_content( $post ) {

		include_once( 'views/html-admin-order-ticket-meta.php' );

	}

}