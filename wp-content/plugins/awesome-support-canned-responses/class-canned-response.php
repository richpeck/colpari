<?php
/**
 * Canned Response
 *
 * @package   Awesome Support Canned Responses
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016 Awesome Support
 */

class WPASCR_Canned_Response {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;

	protected $responses = null;
	protected $post_id = null;

	public function __construct() {

		add_action( 'init',                 array( $this, 'register_post_type' ),     10, 0 );
		add_action( 'admin_menu',           array( $this, 'register_submenu_items' ),  9, 0 );
		add_action( 'admin_footer',         array( $this, 'add_menu_script' ),        10, 0 );
		add_filter( 'wpas_addons_licenses', array( $this, 'addon_license' ),          10, 1 );

		// Load the plugin translation.
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ), 15 );

		if ( isset( $_GET['post'] ) ) {

			$this->post_id = intval( $_GET['post'] );

			add_filter( 'contextual_help',          array( $this, 'contextual_help' ), 10, 0 );
			add_filter( 'wpas_admin_tabs_after_reply_wysiwyg', array( $this , 'add_tab' ) , 13, 1 ); // Add tab for canned responses
			add_filter( 'wpas_admin_tabs_after_reply_wysiwyg_canned_responses_content', array( $this, 'tab_content' ) , 11, 1 ); // Add content for canned responses tab
			add_action( 'admin_enqueue_scripts',    array( $this, 'enqueue_styles' ), 10, 0 );
			add_action( 'admin_enqueue_scripts',    array( $this, 'enqueue_scripts' ), 10, 0 );

		}
	}
	
	/**
	 * Return an instance of this class.
	 *
	 * @since     3.0.0
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
	 * Activate the plugin.
	 *
	 * The activation method just checks if the main plugin
	 * Awesome Support is installed (active or inactive) on the site.
	 * If not, the addon installation is aborted and an error message is displayed.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public static function activate() {

		if ( !class_exists( 'Awesome_Support' ) ) {
			deactivate_plugins( basename( __FILE__ ) );
			wp_die(
				sprintf( __( 'You need Awesome Support to activate this addon. Please <a href="%s" target="_blank">install Awesome Support</a> before continuing.', 'as-canned-responses' ), esc_url( 'https://getawesomesupport.com' ) )
			);
		}

	}

	/**
	 * Add license option.
	 *
	 * @since  0.1.0
	 * @param  array $licenses List of addons licenses
	 * @return array           Updated list of licenses
	 */
	public function addon_license( $licenses ) {

		$licenses[] = array(
			'name'      => __( 'Canned Responses', 'as-canned-responses' ),
			'id'        => 'license_canned_responses',
			'type'      => 'edd-license',
			'default'   => '',
			'server'    => esc_url( 'https://getawesomesupport.com' ),
			'item_name' => 'Canned Responses',
			'file'      => WPASCR_PATH . 'canned-responses.php'
		);

		return $licenses;
	}

	public function enqueue_styles() {

		global $post;

		if ( !isset( $post ) || 'canned-response' === $post->post_type ) {
			return false;
		}

		wp_enqueue_style( 'wpascr-admin-style', WPASCR_URL . 'assets/css/admin.css', array(), WPASCR_VERSION, 'all' );
	}

	public function enqueue_scripts() {

		global $post;

		if ( !isset( $post ) || 'canned-response' === $post->post_type ) {
			return false;
		}

		wp_enqueue_script( 'wpascr-admin-script', WPASCR_URL . 'assets/js/admin.js', array( 'jquery', 'editor' ), WPASCR_VERSION, true );
	}

	public function add_menu_script() {

		global $pagenow;

		if ( 'post-new.php' !== $pagenow || !isset( $_GET['post_type'] ) || 'canned-response' !== $_GET['post_type'] ) {
			return;
		}

		?><script type="text/javascript">jQuery('#menu-posts-ticket, #menu-posts-ticket > a').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu wp-menu-open');</script><?php
	}

	/**
	 * Register the canned responses post type.
	 *
	 * @since  0.1.0
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Canned Responses', 'post type general name', 'as-canned-responses' ),
			'singular_name'      => _x( 'Canned Response', 'post type singular name', 'as-canned-responses' ),
			'menu_name'          => _x( 'Canned Responses', 'admin menu', 'as-canned-responses' ),
			'name_admin_bar'     => _x( 'Canned Response', 'add new on admin bar', 'as-canned-responses' ),
			'add_new'            => _x( 'Add New', 'book', 'as-canned-responses' ),
			'add_new_item'       => __( 'Add New Canned Response', 'as-canned-responses' ),
			'new_item'           => __( 'New Canned Response', 'as-canned-responses' ),
			'edit_item'          => __( 'Edit Canned Response', 'as-canned-responses' ),
			'view_item'          => __( 'View Canned Response', 'as-canned-responses' ),
			'all_items'          => __( 'All Canned Responses', 'as-canned-responses' ),
			'search_items'       => __( 'Search Canned Responses', 'as-canned-responses' ),
			'parent_item_colon'  => __( 'Parent Canned Response:', 'as-canned-responses' ),
			'not_found'          => __( 'No canned responses found.', 'as-canned-responses' ),
			'not_found_in_trash' => __( 'No canned responses found in Trash.', 'as-canned-responses' )
		);

		/* Post type capabilities */
		$cap = array(
			'read'					 => 'view_ticket',
			'read_post'				 => 'view_ticket',
			'read_private_posts' 	 => 'view_private_ticket',
			'edit_post'				 => 'edit_ticket',
			'edit_posts'			 => 'edit_ticket',
			'edit_others_posts' 	 => 'edit_other_ticket',
			'edit_private_posts' 	 => 'edit_private_ticket',
			'edit_published_posts' 	 => 'edit_ticket',
			'publish_posts'			 => 'create_ticket',
			'delete_post'			 => 'delete_ticket',
			'delete_posts'			 => 'delete_ticket',
			'delete_private_posts' 	 => 'delete_private_ticket',
			'delete_published_posts' => 'delete_ticket',
			'delete_others_posts' 	 => 'delete_other_ticket'
		);

		$args = array(
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_admin_bar'   => true,
			'query_var'           => true,
			'rewrite'             => array( 'slug' => 'canned-response' ),
			'capability_type'     => 'edit_ticket',
			'capabilities'        => $cap,
			'has_archive'         => false,
			'hierarchical'        => false,
			'menu_position'       => null,
			'supports'            => array( 'title', 'editor' ),
			'can_export'          => true
		);

		register_post_type( 'canned-response', $args );
	}

	/**
	 * Add tickets submenu items.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function register_submenu_items() {
		/*Note: NB 12-1-2016: This menu is now restricted to roles with "create_users" capabilities because we don't have an explicit AS ADMIN capability -- yet.*/
		add_submenu_page( 'edit.php?post_type=ticket', __( 'Canned Responses', 'as-canned-responses' ), __( 'Canned Responses', 'as-canned-responses' ), 'create_users', 'edit.php?post_type=canned-response' );
	}

	/**
	 * Add contextual help.
	 *
	 * The contextual help shows all the available tags
	 * and how to use them in canned responses.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function contextual_help() {

		global $post;

		if ( isset( $_GET['post_type'] ) && 'canned-response' === $_GET['post_type'] || isset( $post ) && 'canned-response' === $post->post_type ):

			/**
			 * Gather the list of e-mail template tags and their description
			 */
			$emails    = new WPAS_Email_Notification( false );
			$list_tags = $emails->get_tags();

			$tags = '<table class="widefat"><thead><th class="row-title">' . __( 'Tag', 'as-canned-responses' ) . '</th><th>' . __( 'Description', 'as-canned-responses' ) . '</th></thead><tbody>';

			foreach ( $list_tags as $the_tag ) {
				$tags .= '<tr><td class="row-title"><strong>' . $the_tag['tag'] . '</strong></td><td>' . $the_tag['desc'] . '</td></tr>';
			}

			$tags .= '</tbody></table>';
			
			$screen = get_current_screen();
			
			$screen->add_help_tab( array(
				'id'      => 'template-tags',
				'title'   => __( 'Template Tags', 'as-canned-responses' ),
				'content' => sprintf( __( '<p>When setting up your canned responses, you can use a certain number of template tags allowing you to dynamically add ticket-related information at the moment the reply is sent. Here is the list of available tags:</p>%s', 'as-canned-responses' ), $tags )
			) );

			return true;

		else:
			return false;
		endif;
	}

	public function get_canned_responses() {

		if ( !is_null( $this->responses ) && is_array( $this->responses ) ) {
			return $this->responses;
		}

		$args = array(
			'post_type'              => 'canned-response',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'cache_results'          => false,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		);
		
		$query = new WP_Query( $args );

		if ( empty( $query->posts ) ) {
			return array();
		} else {
			return $query->posts;
		}
		

	}

	public function has_canned_responses() {

		if ( is_null( $this->responses ) ) {
			$this->responses = $this->get_canned_responses();
		}

		if ( empty( $this->responses ) ) {
			return false;
		} else {
			return true;
		}

	}

	public function get_canned_responses_markup() {

		if ( !$this->has_canned_responses() ) {
			return false;
		}

		$output    = '<div class="wpas-canned-responses-wrap"><h2>Canned Responses</h2>';
		$emails    = new WPAS_Email_Notification( $this->post_id, true );
		$responses = $this->get_canned_responses();

		foreach ( $responses as $response ) {
			$title   = esc_attr( $response->post_title );
			$content = htmlentities( wpautop( str_replace( '\'', '&apos;', $emails->fetch( $response->post_content ) ) ) );
			$output  .= "<a href='#' class='wpas-canned-response' data-message='$content'>$title</a>";
		}

		$output = $output . '</div>';

		return $output;

	}
	
	/**
	 * Add tab for canned responses
	 * 
	 * @param array $tabs
	 * 
	 * @return array
	 */
	public function add_tab( $tabs ) {
		
		$tabs['canned_responses'] = __( 'Canned Responses', 'as-canned-responses' );
	
		return $tabs;
	}		

	/**
	 * Return content for canned responses tab
	 * 
	 * @return string
	 */
	public function tab_content() {
		
		ob_start();
		
		$this->display_canned_responses();
		
		return ob_get_clean();
	}

	public function display_canned_responses() {
		echo $this->get_canned_responses_markup();
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * With the introduction of plugins language packs in WordPress loading the textdomain is slightly more complex.
	 *
	 * We now have 3 steps:
	 *
	 * 1. Check for the language pack in the WordPress core directory
	 * 2. Check for the translation file in the plugin's language directory
	 * 3. Fallback to loading the textdomain the classic way
	 *
	 * @since   0.1.2
	 * @return boolean True if the language file was loaded, false otherwise
	 */
	public function load_plugin_textdomain() {

		$lang_dir       = WPASCR_ROOT . 'languages/';
		$lang_path      = WPASCR_PATH . 'languages/';
		$locale         = apply_filters( 'plugin_locale', get_locale(), 'as-canned-responses' );
		$mofile         = "as-private-notes-$locale.mo";
		$glotpress_file = WP_LANG_DIR . '/plugins/awesome-support-canned-responses/' . $mofile;

		// Look for the GlotPress language pack first of all
		if ( file_exists( $glotpress_file ) ) {
			$language = load_textdomain( 'as-canned-responses', $glotpress_file );
		} elseif ( file_exists( $lang_path . $mofile ) ) {
			$language = load_textdomain( 'as-canned-responses', $lang_path . $mofile );
		} else {
			$language = load_plugin_textdomain( 'as-canned-responses', false, $lang_dir );
		}

		return $language;

	}

}