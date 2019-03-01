<?php
/**
 * Documentation Admin.
 *
 * @package   Awesome Support/Documentation
 * @author    AwesomeSupport <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2014-2017 AwesomeSupport
 */

class WPAS_Documentation_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    0.1.0
	 * @var      object
	 */
	protected static $instance = null;

	public function __construct() {
		
		/* With all actions removed I don't think this class is even needed any more but keeping it around just in case. */
		
		// add_action( 'admin_menu',           array( $this, 'register_submenu_items' ),  9, 0 );
		// add_action( 'add_meta_boxes', array( $this, 'metaboxes' ) );
		// add_action( 'save_post_documentation', array( $this, 'save_custom_fields' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.1.0
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Add tickets submenu items.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function register_submenu_items() {
		add_submenu_page( 'edit.php?post_type=ticket', __( 'Documentations', 'wpas-documentation' ), __( 'Documentations', 'wpas-documentation' ), 'administrator', 'edit.php?post_type=documentation' );
	}

	public function metaboxes() {

		add_meta_box(
			'wpas_doc_details',
			__( 'Documentation Details', 'wpas-documentation' ),
			array( $this, 'metabox_callback' ),
			'documentation',
			'normal',
			'default',
			array( 'template' => 'details' )
		);
	}

	public function metabox_callback( $post, $args ) {

		if ( ! is_array( $args ) || ! isset( $args['args'] ) || ! isset( $args['args']['template'] ) ) {
			return false;
		}

		$template = $args['args']['template'];

		if ( file_exists( WPAS_DOC_PATH . "includes/views/metaboxes/$template.php" ) ) {
			include( WPAS_DOC_PATH . "includes/views/metaboxes/$template.php" );
		} else {
			return false;
		}

	}

}