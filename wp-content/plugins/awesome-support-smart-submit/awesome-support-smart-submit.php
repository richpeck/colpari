<?php
/**
 * @package   Awesome Support Smart Submit
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016 - 2018 Awesome Support
 *
 * @wordpress-plugin
 * Plugin Name:       Awesome Support: Smart Submit
 * Plugin URI:        http://getawesomesupport.com/addons/notifications/?utm_source=internal&utm_medium=plugin_meta&utm_campaign=smart_submit
 * Description:       Smart Submit will force users to engage with your FAQs, Documentation and other help resources before submitting a ticket
 * Version:           1.0.0
 * Author:            The Awesome Support Team
 * Author URI:        https://getawesomesupport.com?utm_source=internal&utm_medium=plugin_meta&utm_campaign=smart_submit
 * Text Domain:       wpas_ss
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/* 
 * Include extension base class and make sure that the Awesome Support plugin is enabled.
 *
 * To do this, we perform checks in the following order:
 *  1. Check if the WPAS_Extension_Base class is available.  
 *  2. If not, check if the variable WPAS_ROOT is defined and the file is available in that path.
 *  3. If not, check if the variable WPAS_AS_FOLDER is defined and the file is available in that path.
 * 
 * If the file does not exist, we throw up an error message.
 */
if ( !class_exists( 'WPAS_Extension_Base' ) ) {
	
	$wpas_dir = defined( 'WPAS_ROOT' )  ? WPAS_ROOT : ( defined( 'WPAS_AS_FOLDER' ) ? WPAS_AS_FOLDER : 'awesome-support' );
	$wpas_eb_file = trailingslashit( WP_PLUGIN_DIR . '/' . $wpas_dir ) . 'includes/class-extension-base.php';
	
	if( file_exists( $wpas_eb_file ) ) {
		require_once ( $wpas_eb_file );
	} else {
		add_action( 'admin_notices', function() {
		?>	
		
		<div class="error">
			<p>
				<?php printf( __( 'The LATEST version of Awesome Support needs to be installed in order to activate the Smart Submit add-on for Awesome Support. Please <a href="%s" target="_blank">install Awesome Support</a> before continuing.', 'wpas_ss' ), esc_url( 'https://getawesomesupport.com' ) ); ?>
			</p>
		</div>
			
		<?php	
			
		});
		
		return;
	}
}

/*----------------------------------------------------------------------------*
 * Instantiate the plugin
 *----------------------------------------------------------------------------*/

/**
 * Register the activation hook
 */
register_activation_hook(   __FILE__, array( 'WPAS_Smart_Submit', 'activate'   ) );

add_action( 'plugins_loaded', array( 'WPAS_Smart_Submit', 'get_instance' ), 9 );


/**
 * Instantiate the addon.
 *
 * This method runs a few checks to make sure that the addon
 * can indeed be used in the current context, after what it
 * registers the addon to the core plugin for it to be loaded
 * when the entire core is ready.
 *
 * @since  0.1.0
 * @return void
 */
class WPAS_Smart_Submit extends WPAS_Extension_Base {

	/**
	 * Instance of this loader class.
	 *
	 * @since    0.1.0
	 * @var      object
	 */
	protected static $instance = null;
	
	/**
	 * addon construct method
	 */
	public function __construct() {
		
		
		
		$this->setVersionRequired( '5.1.1' );		// Set required version of core
		$this->setPhpVersionRequired( '5.6' );		// Set required version of php
		$this->setSlug( 'smartsubmit' );			// Set addon slug
		$this->setUid( 'SS' );						// Set short unique id
		$this->setTextDomain( 'wpas_ss' );			// Set text domain for translation
		$this->setVersion( '1.0.0' );				// Set addon version
		$this->setItemId( 1261696 );				// Set addon item id
		
		parent::__construct();
		
		
	}
	
	/**
	 * Load required files
	 */
	public function load() {
		

		require_once( WPAS_SS_PATH . 'includes/functions.php' );

		
		require_once( WPAS_SS_PATH . 'includes/settings.php' );
		
		
		self::get_instance();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
		
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10 );

	}
	
	function enqueue_scripts() {
		if ( !is_admin() && true === boolval( wpas_ss_get_option( 'ss_smart_submit_enable' ) ) ) {
			
			wp_enqueue_script( 'wpas-ss-script', WPAS_SS_URL . 'assets/js/script.js', array( 'jquery', 'wp-util' ) );
			
			wp_enqueue_style( 'wpas-ss-style', WPAS_SS_URL . 'assets/css/public/style.css' );
			
			$localize_script = array(
				'stage_1_enabled' => wpas_ss_stage_1_enabled(),
				'stage_2_enabled' => wpas_ss_stage_2_enabled(),
				'category_view_type' => wpas_ss_category_view_type()
			);

			if( !empty($localize_script) ) {
				wp_localize_script( 'wpas-ss-script', 'wpas_ss', $localize_script );
			}
			
		}
	}
	
	function admin_enqueue_scripts() {
		
			wp_enqueue_style( 'wpas-ss-admin-style', WPAS_SS_URL . 'assets/css/admin/style.css' );
			
	}
	

	/**
	 * Check if the vendor dependencies are available
	 *
	 * @since 0.1.3
	 * @return bool
	 */
	protected function dependencies_available() {
		return true;
	}

}