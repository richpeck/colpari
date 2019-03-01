<?php

/**
 * @package   Awesome Support Front-end agents Addon
 * @author    Ante Laca <ante.laca@gmail.com>
 * @link      https://antelaca.xyz
 * @copyright 2018 Awesome Support
 *  *
 * Plugin Name:       Awesome Support: Agent Front-end
 * Description:       This add-on to the Awesome Support WordPress Helpdesk Plugin provides a simple interface that your agents can use to respond to existing tickets on the front-end of the site. 
 * Version:           2.0.3
 * Author:            The Awesome Support Team
 * Text Domain:       awesome-support-frontend-agents
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
				<?php printf( __( 'The LATEST version of Awesome Support needs to be installed in order to activate the Agent Front-end add-on for Awesome Support. Please <a href="%s" target="_blank">install Awesome Support</a> before continuing.', 'as-private-notes' ), esc_url( 'https://getawesomesupport.com' ) ); ?>
			</p>
		</div>
			
		<?php	
			
		});
		
		return;
	}
}

/*
----------------------------------------------------------------------------*
 * Instantiate the plugin
 *----------------------------------------------------------------------------*/

/**
 * Register the activation hook
 */
register_activation_hook( __FILE__, array( 'AS_Frontend_Agents_Loader', 'activate_frontend_agents' ) );

/**
 * Register the uninstall hook
 */
register_uninstall_hook( __FILE__, array( 'AS_Frontend_Agents_Loader', 'uninstall_frontend_agents' ) );

add_action( 'plugins_loaded', array( 'AS_Frontend_Agents_Loader', 'get_instance' ), 9 );
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
class AS_Frontend_Agents_Loader extends WPAS_Extension_Base {
	
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
		
		$this->setVersionRequired( '5.1.0' );	 // Set required version of core
		$this->setPhpVersionRequired( '5.6' );	 // Set required version of php
		$this->setSlug( 'front-end-agents' );	 // Set addon slug
		$this->setUid( 'front-end-agents' );	 // Set short unique id
		$this->setTextDomain( 'awesome-support-frontend-agents' ); // Set text domain for translation
		$this->setVersion( '2.0.3' );		     // Set addon version
		$this->setItemId( 1217355 );		     // Set addon item id
		
		parent::__construct();
	}	

	/**
	 * Actions to execute after plugin is activated.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public static function activate_frontend_agents() {

	}

	/**
	 * Actions to execute after plugin is uninstalled.

	 * @return void
	 */
	public static function uninstall_frontend_agents() {
		


	}

	/**
	 * Load the addon.
	 *
	 * Include all necessary files and instantiate the addon.
	 *
	 * @since  2.0.0
	 *
	 * @return void
	 */
	public function load() {

		// Load the addon here.
		include dirname( $this->get_addon_path() ) . '/includes/classes/frontend-agents.php';
		
		AS_Frontend_Agents::get_instance()->run();

	}

}
