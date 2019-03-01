<?php
class ASWC_WC_Account_Endpoint {

	/**
	 * Custom endpoint name.
	 *
	 * @var string
	 */
	public static $endpoint = 'support';

	/**
	 * Plugin actions.
	 */
	public function __construct() {
		// Actions used to insert a new endpoint in the WordPress.
		add_action( 'init', array( $this, 'add_endpoints' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

		// Change the My Accout page title.
		add_filter( 'the_title', array( $this, 'endpoint_title' ) );

		// Insering your new tab/page into the My Account page.
		add_filter( 'woocommerce_account_menu_items', array( $this, 'new_menu_items' ) );
		add_action( 'woocommerce_account_' . self::$endpoint .  '_endpoint', array( $this, 'endpoint_content' ) );

		// Scripts/styles for AS
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
	}

	/**
	 * Register new endpoint to use inside My Account page.
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
	 */
	public function add_endpoints() {
		add_rewrite_endpoint( self::$endpoint, EP_ROOT | EP_PAGES );
	}

	/**
	 * Add new query var.
	 *
	 * @param array $vars
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = self::$endpoint;

		return $vars;
	}

	/**
	 * Set endpoint title.
	 *
	 * @param string $title
	 * @return string
	 */
	public function endpoint_title( $title ) {
		global $wp_query;

		$is_endpoint = isset( $wp_query->query_vars[ self::$endpoint ] );

		if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
			// New page title.
			$title = apply_filters( 'aswc_account_tab_name', __( 'Support', 'awesome-support-woocommerce' ) );

			remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
		}

		return $title;
	}

	/**
	 * Insert the new endpoint into the My Account menu.
	 *
	 * @param array $items
	 * @return array
	 */
	public function new_menu_items( $items ) {
		// Remove the logout menu item.
		$logout = isset($items['customer-logout']) ? $items['customer-logout'] : false;
		if ($logout) {
			unset( $items['customer-logout'] );
		}

		// Insert your custom endpoint.
		$items[ self::$endpoint ] = apply_filters( 'aswc_account_tab_name', __( 'Support', 'awesome-support-woocommerce' ) );

		// Insert back the logout item.
		if ($logout) {
			$items['customer-logout'] = $logout;
		}

		return $items;
	}

	/**
	 * Endpoint HTML content.
	 */
	public function endpoint_content() {
		global $current_user;
		
		echo do_shortcode( '[tickets]' );
	}

	/**
	 * Enqueue AS assets.
	 */
	public function assets() {
		// only on WC account page
		if ( is_account_page() ) {
			wp_enqueue_style( 'wpas-plugin-styles' );
			$stylesheet = wpas_get_theme_stylesheet();
			if ( file_exists( $stylesheet ) && true === boolval( wpas_get_option( 'theme_stylesheet' ) ) ) {
				wp_register_style( 'wpas-theme-styles', wpas_get_theme_stylesheet_uri(), array(), WPAS_VERSION );
				wp_enqueue_style( 'wpas-theme-styles' );
			}
		wp_enqueue_script( 'wpas-plugin-script' );
		}
	}
}

new ASWC_WC_Account_Endpoint();