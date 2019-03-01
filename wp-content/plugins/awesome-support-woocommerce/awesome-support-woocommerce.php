<?php
/**
 * Plugin Name: Awesome Support: WooCommerce
 * Plugin URI: https://getawesomesupport.com/
 * Description: An Awesome Support integration plugin for WooCommerce.
 * Version: 1.5.0
 * Author: Awesome Support Team
 * Author URI: http://getawesomesupport.com/
 * Text Domain: awesome-support-woocommerce
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ASWC_Init' ) ) :

/**
 * Awesome Support WooCommerce main class.
 */
class ASWC_Init {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.5.0';

    /**
     * Plugin slug.
     *
     * @since  0.1.0
     * @var    string
     */
    protected $slug = 'awesome-support-woocommerce';

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin.
	 */
	private function __construct() {
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Checks that Awesome Support & WooCommerce are installed.
		if ( class_exists( 'Awesome_Support' ) && class_exists( 'WC_Integration' ) ) {

            /**
             * Helper functions.
             */
            include_once 'includes/aswc-functions.php';

            /**
             * Add the addon license field
             */
            if ( is_admin() ) {
                add_filter( 'wpas_addons_licenses', array( $this, 'addon_license' ),       10, 1 );
                add_action( 'admin_notices',        array( $this, 'license_notice' ),      10, 0 );
                add_filter( 'plugin_row_meta',      array( $this, 'license_notice_meta' ), 10, 4 );
            }

			// Integration classes.
			include_once 'includes/class-aswc-wc-integration.php';

			// Register the integration.
			add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );

			// Include all the things
            if ( version_compare( WPAS_VERSION, '3.2', '>=' ) ) {
                include_once 'includes/class-aswc-custom-fields.php';
            } else {
                include_once 'includes/class-aswc-custom-fields-compat.php';
            }

			include_once 'includes/class-aswc-customer-meta.php';

			// Load up all the things
			add_action( 'plugins_loaded', array( 'ASWC_Customer_Meta', 'get_instance' ) );

		} else {

			add_action( 'admin_notices', array( $this, 'awesome_support_missing_notice' ) );
		
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

    /**
     * Flush rewrite rules on activation.
     */
    public static function install() {
        flush_rewrite_rules();
    }

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'awesome-support-woocommerce' );

		load_textdomain( 'awesome-support-woocommerce', trailingslashit( WP_LANG_DIR ) . 'awesome-support-woocommerce/awesome-support-woocommerce-' . $locale . '.mo' );
		load_plugin_textdomain( 'awesome-support-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * WooCommerce fallback notice.
	 *
	 * @return string
	 */
	public function awesome_support_missing_notice() {
		echo '<div class="error"><p>' . sprintf( __( 'Either %s or %s is missing. Both are needed for this plugin to work!', 'awesome-support-woocommerce' ), '<a href="http://getawesomesupport.com/" target="_blank">' . __( 'Awesome Support', 'awesome-support-woocommerce' ) . '</a>', '<a href="http://www.woothemes.com/woocommerce/" target="_blank">' . __( 'WooCommerce', 'awesome-support-woocommerce' ) . '</a>' ) . '</p></div>';
	}

	/**
	 * Add a new integration to WooCommerce.
	 *
	 * @param  array $integrations WooCommerce integrations.
	 *
	 * @return array               Help Scout integration.
	 */
	public function add_integration( $integrations ) {
		$integrations[] = 'ASWC_WC_Integration';

		return $integrations;
	}

    /**
     * Add license option.
     *
     * @since  0.1.0
     * @param  array $licenses List of addons licenses
     * @return array           Updated list of licenses
     */
    public function addon_license( $licenses ) {
        $plugin_name = 'WooCommerce';
        $licenses[] = array(
            'name'      => 'WooCommerce',
            'id'        => "license_{$this->slug}",
            'type'      => 'edd-license',
            'default'   => '',
            'server'    => esc_url( 'http://getawesomesupport.com' ),
            'item_name' => $plugin_name,
            'item_id'   => 2527,
            'file'      => __FILE__
        );
        return $licenses;
    }
    /**
     * Display notice if user didn't set his Envato license code
     *
     * @since 0.1.0
     */
    public function license_notice() {
        /**
         * We only want to display the notice to the site admin.
         */
        if ( ! current_user_can( 'administrator' ) ) {
            return false;
        }
        /**
         * If the notice has already been dismissed we don't display it again.
         */
        if ( wpas_is_notice_dismissed( "license_{$this->slug}" ) ) {
            return false;
        }
        $license   = wpas_get_option( "license_{$this->slug}", '' );

        if ( ! empty( $license ) ) {
            return false;
        }

        /* Prepare the dismiss URL */
	    $args              = $_GET;
	    $args['notice_id'] = 'license_' . $this->slug;
	    $url               = wpas_do_url( '', 'dismiss_notice', $args );
	    $license_page      = wpas_get_settings_page_url( 'licenses' ); ?>

        <div class="updated error">
            <p><?php printf( __( 'Please <a href="%s">fill-in your product license</a> now. If you don\'t, your copy of Awesome Support: WooCommerce <strong>will never be updated</strong>.', 'wpbp' ), $license_page ); ?>
                <a href="<?php echo $url; ?>"><small>(<?php _e( 'I do NOT want the updates, dismiss this message', 'wpbp' ); ?>)</small></a></p>
        </div>

    <?php }
    /**
     * Add license warning in the plugin meta row
     *
     * @since 0.1.0
     */
    public function license_notice_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
        $license   = wpas_get_option( "license_{$this->slug}", '' );
        if( ! empty( $license ) ) {
            return $plugin_meta;
        }
        $license_page = add_query_arg( array( 'post_type' => 'ticket', 'page' => 'settings', 'tab' => 'licenses' ), admin_url( 'edit.php' ) );
        if ( plugin_basename( __FILE__ ) === $plugin_file ) {
            $plugin_meta[] = '<strong>' . sprintf( __( 'You must fill-in your product license in order to get future plugin updates. <a href="%s">Click here to do it</a>.', 'wpas' ), $license_page ) . '</strong>';
        }

        return $plugin_meta;
    }

}

add_action( 'plugins_loaded', array( 'ASWC_Init', 'get_instance' ), 0 );
register_activation_hook( __FILE__, array( 'ASWC_Init', 'install' ) );

endif;