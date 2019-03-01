<?php
/**
 * Documentation.
 *
 * @package   Awesome Support/Documentation
 * @author    AwesomeSupport <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2014-2017 AwesomeSupport
 */

class WPAS_Documentation {

	/**
	 * Instance of this class.
	 *
	 * @since    0.1.0
	 * @var      object
	 */
	protected static $instance = null;

	public function __construct() {
		add_action( 'init',						array( $this, 'register_post_type' ),				11, 0 );
		add_action( 'init',						array( $this, 'register_documentation_tags' ),		11, 0 );
		add_action( 'init',						array( $this, 'register_documentation_category' ),	11, 0 );
		add_action( 'init',						array( $this, 'register_documentation_section' ),	11, 0 );
		add_action( 'init',						array( $this, 'register_documentation_chapter' ),	11, 0 );
		add_action( 'init',						array( $this, 'register_documentation_version' ),	11, 0 );
		add_action( 'init',						array( $this, 'register_my_menu' ),					11, 0 );
		add_action( 'widgets_init',				array( $this, 'register_sidebar' ),					10, 0 );
		add_action( 'widgets_init',				array( $this, 'register_widgets' ),					10, 0 );
		add_action( 'wp_enqueue_scripts',		array( $this, 'load_scripts' ),						10, 0 );
		add_action( 'wp_enqueue_scripts',		array( $this, 'load_styles' ),						10, 0 );
		add_action( 'admin_enqueue_scripts',	array( $this, 'load_admin_styles_and_scripts' ),	10, 0 );
		add_filter( 'wpas_plugin_post_types',	array( $this, 'register_as_post_type' ),			10, 1 );
		add_filter( 'single_template',			array( $this, 'set_custom_post_type_template' ),	10, 1 );
		add_filter( 'body_class',				array( $this, 'body_classes' ) );
		add_filter( 'show_admin_bar',			array( $this, 'hide_admin_bar_from_front_end' ) );

		/* Set actions and filters that control sorting in the CPT list screen */
		add_action('manage_edit-documentation_columns',					array( $this, 'add_new_documentation_columns' ), 		10, 1 );  	// Add non-taxonomy columns to the CPT list
		add_action('manage_documentation_posts_custom_column',			array( $this, 'show_documentation_order_columns_data' ),10, 1 );	// Render the data in the column when its called.
		add_filter('manage_edit-documentation_sortable_columns',		array( $this, 'order_documentation_sortable_columns' ), 10, 1 );	// Manage sortable columns in the CPT list

		/* Set actions and filters that control drop down filters at the top of the CPT list screen - chapters */
		add_action('restrict_manage_posts',								array( $this, 'custom_filter_post_type_by_chapter_taxonomy' ), 	10, 1 );
		add_filter('parse_query',										array( $this, 'convert_id_to_term_in_query_for_chapters' ), 	10, 1 );

		/* Set actions and filters that control drop down filters at the top of the CPT list screen - versions */
		add_action('restrict_manage_posts',								array( $this, 'custom_filter_post_type_by_version_taxonomy' ), 	10, 1 );
		add_filter('parse_query', 										array( $this, 'convert_id_to_term_in_query_for_versions' ), 	10, 1 );

		/* Set actions and filters that control drop down filters at the top of the CPT list screen - sections */
		add_action('restrict_manage_posts', 							array( $this, 'custom_filter_post_type_by_section_taxonomy' ), 	10, 1 );
		add_filter('parse_query', 										array( $this, 'convert_id_to_term_in_query_for_sections' ), 	10, 1 );

		/* Set actions and filters that control drop down filters at the top of the CPT list screen - categories */
		add_action('restrict_manage_posts', 							array( $this, 'custom_filter_post_type_by_category_taxonomy' ), 10, 1 );
		add_filter('parse_query', 										array( $this, 'convert_id_to_term_in_query_for_categories' ), 	10, 1 );

		/* Set actions and filters that control drop down filters at the top of the CPT list screen - products */
		add_action('restrict_manage_posts', 							array( $this, 'custom_filter_post_type_by_product_taxonomy' ), 10, 1 );
		add_filter('parse_query', 										array( $this, 'convert_id_to_term_in_query_for_products' ), 	10, 1 );
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

	public function load_scripts() {

		wp_enqueue_script( 'sidebar', WPAS_DOC_URL . 'assets/js/sidebar.js', array( 'jquery' ), WPAS_DOC_VERSION, true );

		wp_register_script( 'asdoc-main', WPAS_DOC_URL . 'assets/js/as-documentation.js', array( 'jquery' ), WPAS_DOC_VERSION, true );
		wp_register_script( 'as-documentation-live-search-js', WPAS_DOC_URL . 'assets/js/as-documentation-live-search.js', array( 'jquery' ), WPAS_DOC_VERSION, true );		

		// pass some parameters to the live search javascript
		wp_localize_script( 'as-documentation-live-search-js', 'asdoc', array(
			'ajaxurl'  => admin_url( 'admin-ajax.php' ),
				'settings' => array(
					'selectors'   => asdoc_get_selectors(),
					'delay'       => (int) asdoc_get_option( 'delay', 300 ),
					'chars_min'   => (int) asdoc_get_option( 'chars_min', 3 ),
					'link_target' => asdoc_get_option( 'link_target', '_self' ),
				)
			)
		);


		if ( ! is_admin() ) {
			wp_enqueue_script( 'asdoc-main' );
			wp_enqueue_script( 'as-documentation-live-search-js' );
		}

	}

	public function load_styles() {
		if ( get_post_type() == 'documentation' ) {
			wp_enqueue_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
			wp_enqueue_style( 'theme-css', WPAS_DOC_URL . 'assets/css/theme.css', array(), WPAS_DOC_VERSION, 'all' );
		}
		
		wp_register_style( 'asdoc-main', WPAS_DOC_URL . 'assets/css/as-documentation.css', array(), WPAS_DOC_VERSION, 'all' );

		if ( ! is_admin() ) {
			wp_enqueue_style( 'asdoc-main' );
		}		
	}

	/**
	* Load up some styles when in the admin screen on a ticket
	*/
	public function load_admin_styles_and_scripts() {
		global $post;
		if ( isset( $post ) && is_object( $post ) && is_a( $post, 'WP_Post' ) && 'ticket' === $post->post_type ) {
			wp_enqueue_style( 'as-documentation-quick-links-css', WPAS_DOC_URL . 'assets/css/admin/doc-quick-links.css', null, WPAS_DOC_VERSION, 'all' );
			wp_enqueue_script( 'as-documentation-quick-links-js', WPAS_DOC_URL . 'assets/js/admin/doc-quick-links.js', array( 'jquery' ), WPAS_DOC_VERSION, 'all' );
		}
	}

	/**
	 * Register the documentation post type.
	 *
	 * @since  0.1.0
	 */
	public function register_post_type() {
		
		$rewrite_slug = asdoc_get_option( 'asdoc-slug', 'documentation');
		
		$labels = array(
			'name'               => _x( 'Documentations', 'post type general name', 'wpas-documentation' ),
			'singular_name'      => _x( 'Documentation', 'post type singular name', 'wpas-documentation' ),
			'menu_name'          => _x( 'Documentation', 'admin menu', 'wpas-documentation' ),
			'name_admin_bar'     => _x( 'Documentation', 'add new on admin bar', 'wpas-documentation' ),
			'add_new'            => _x( 'Add Documentation', 'book', 'wpas-documentation' ),
			'add_new_item'       => __( 'Add New Documentation', 'wpas-documentation' ),
			'new_item'           => __( 'New Documentation', 'wpas-documentation' ),
			'edit_item'          => __( 'Edit Documentation', 'wpas-documentation' ),
			'view_item'          => __( 'View Documentation', 'wpas-documentation' ),
			'all_items'          => __( 'All Documentation', 'wpas-documentation' ),
			'search_items'       => __( 'Search Documentation', 'wpas-documentation' ),
			'parent_item_colon'  => __( 'Parent Documentation:', 'wpas-documentation' ),
			'not_found'          => __( 'No documentations found.', 'wpas-documentation' ),
			'not_found_in_trash' => __( 'No documentations found in Trash.', 'wpas-documentation' )
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
			'public'              => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'query_var'           => true,
			'rewrite'             => array( 'slug' => $rewrite_slug ),
			'capability_type'     => 'edit_ticket',
			'capabilities'        => $cap,
			'has_archive'         => true,
			'hierarchical'        => true,
			'menu_position'       => null,
			'supports'            => array( 'title', 'editor', 'page-attributes' ),
			'can_export'          => true
		);

		if ( taxonomy_exists( 'product' ) ) {
			$args['taxonomies'] = array( 'product' );
		}

		register_post_type( 'documentation', $args );
	}

	/**
	 * Register the post type as an Awesome Support post type
	 *
	 * This is mandatory to have the front-end JS object loaded.
	 *
	 * @param $post_types array List of AS post types
	 *
	 * @return array Updated list of AS post types
	 */
	public function register_as_post_type( $post_types ) {

		array_push( $post_types, 'documentation' );

		return $post_types;
	}

	/**
	 * Register documentation tags.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function register_documentation_tags() {

		$labels = array(
			'name'                       => _x( 'Tags', 'taxonomy general name', 'wpas-documentation' ),
			'singular_name'              => _x( 'Tag', 'taxonomy singular name', 'wpas-documentation' ),
			'search_items'               => __( 'Search Tags', 'wpas-documentation' ),
			'popular_items'              => __( 'Popular Tags', 'wpas-documentation' ),
			'all_items'                  => __( 'All Tags', 'wpas-documentation' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Tag', 'wpas-documentation' ),
			'update_item'                => __( 'Update Tag', 'wpas-documentation' ),
			'add_new_item'               => __( 'Add New Tag', 'wpas-documentation' ),
			'new_item_name'              => __( 'New Tag Name', 'wpas-documentation' ),
			'separate_items_with_commas' => __( 'Separate tags with commas', 'wpas-documentation' ),
			'add_or_remove_items'        => __( 'Add or remove tags', 'wpas-documentation' ),
			'choose_from_most_used'      => __( 'Choose from the most used tags', 'wpas-documentation' ),
			'not_found'                  => __( 'No tags found.', 'wpas-documentation' ),
			'menu_name'                  => __( 'Tags', 'wpas-documentation' ),
		);

		$args = array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'documentation_tag' ),
		);

		register_taxonomy( 'documentation_tag', 'documentation', $args );

	}

	/**
	 * Register documentation categories
	 *
	 * @since  0.4.0
	 * @return void
	 */
	function register_documentation_category() {

		$labels = array(
			'name'              => _x( 'Categories', 'wpas-documentation' ),
			'singular_name'     => _x( 'Category', 'wpas-documentation' ),
			'search_items'      => __( 'Search Categories', 'wpas-documentation' ),
			'all_items'         => __( 'All Categories', 'wpas-documentation' ),
			'parent_item'       => __( 'Parent Category', 'wpas-documentation' ),
			'parent_item_colon' => __( 'Parent Category:', 'wpas-documentation' ),
			'edit_item'         => __( 'Edit Category', 'wpas-documentation' ),
			'update_item'       => __( 'Update Category', 'wpas-documentation' ),
			'add_new_item'      => __( 'Add New Category', 'wpas-documentation' ),
			'new_item_name'     => __( 'New Category Name', 'wpas-documentation' ),
			'menu_name'         => __( 'Categories', 'wpas-documentation' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'as-documentation-category' ),
		);

		register_taxonomy( 'as-doc-category', array( 'documentation' ), $args );

	}

	/**
	 * Register documentation sections
	 *
	 * @since  0.4.0
	 * @return void
	 */
	function register_documentation_section() {

		$labels = array(
			'name'              => _x( 'Sections', 'wpas-documentation' ),
			'singular_name'     => _x( 'Section', 'wpas-documentation' ),
			'search_items'      => __( 'Search Sections', 'wpas-documentation' ),
			'all_items'         => __( 'All Sections', 'wpas-documentation' ),
			'parent_item'       => __( 'Parent Section', 'wpas-documentation' ),
			'parent_item_colon' => __( 'Parent Section:', 'wpas-documentation' ),
			'edit_item'         => __( 'Edit Section', 'wpas-documentation' ),
			'update_item'       => __( 'Update Section', 'wpas-documentation' ),
			'add_new_item'      => __( 'Add New Section', 'wpas-documentation' ),
			'new_item_name'     => __( 'New Section Name', 'wpas-documentation' ),
			'menu_name'         => __( 'Sections', 'wpas-documentation' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'as-documentation-section' ),
		);

		register_taxonomy( 'as-doc-section', array( 'documentation' ), $args );

	}

	/**
	 * Register documentation Chapters
	 *
	 * @since  0.4.0
	 * @return void
	 */
	function register_documentation_chapter() {

		$labels = array(
			'name'              => _x( 'Chapters', 'wpas-documentation' ),
			'singular_name'     => _x( 'Chapter', 'wpas-documentation' ),
			'search_items'      => __( 'Search Chapters', 'wpas-documentation' ),
			'all_items'         => __( 'All Chapters', 'wpas-documentation' ),
			'parent_item'       => __( 'Parent Chapter', 'wpas-documentation' ),
			'parent_item_colon' => __( 'Parent Chapter:', 'wpas-documentation' ),
			'edit_item'         => __( 'Edit Chapter', 'wpas-documentation' ),
			'update_item'       => __( 'Update Chapter', 'wpas-documentation' ),
			'add_new_item'      => __( 'Add New Chapter', 'wpas-documentation' ),
			'new_item_name'     => __( 'New Chapter Name', 'wpas-documentation' ),
			'menu_name'         => __( 'Chapters', 'wpas-documentation' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'as-documentation-chapter' ),
		);

		register_taxonomy( 'as-doc-chapter', array( 'documentation' ), $args );

	}

	/**
	 * Register documentation Versions
	 *
	 * @since  0.4.0
	 * @return void
	 */
	function register_documentation_version() {

		$labels = array(
			'name'              => _x( 'Versions', 'wpas-documentation' ),
			'singular_name'     => _x( 'Version', 'wpas-documentation' ),
			'search_items'      => __( 'Search Versions', 'wpas-documentation' ),
			'all_items'         => __( 'All Versions', 'wpas-documentation' ),
			'parent_item'       => __( 'Parent Version', 'wpas-documentation' ),
			'parent_item_colon' => __( 'Parent Version:', 'wpas-documentation' ),
			'edit_item'         => __( 'Edit Version', 'wpas-documentation' ),
			'update_item'       => __( 'Update Version', 'wpas-documentation' ),
			'add_new_item'      => __( 'Add New Version', 'wpas-documentation' ),
			'new_item_name'     => __( 'New Version Name', 'wpas-documentation' ),
			'menu_name'         => __( 'Versions', 'wpas-documentation' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'as-documentation-version' ),
		);

		register_taxonomy( 'as-doc-version', array( 'documentation' ), $args );

	}

	/**
	 * Register the documentation page sidebar.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function register_sidebar() {
		register_sidebar( array(
			'name'          => __( 'Documentation', 'wpas-documentation' ),
			'id'            => 'wpas-documentation',
			'class'         => 'wpas-documentation-sidebar',
			'description'   => __( 'Widgets in this area will be shown on the documentation pages.', 'wpas-documentation' ),
			'before_widget' => '<div id="%1$s" class="widget wpas-doc-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		) );
	}

	public function register_widgets() {

		register_widget( 'WPAS_Documentation_TOC' ); // Table of Contents

	}


	/**
	 * Add new columns to the CPT list screen
	 *
	 * @since  2.0.0
	 * @return void
	 */
	function add_new_documentation_columns($documentation_columns) {

		if ( taxonomy_exists( 'product' ) ) {
			$documentation_columns['product'] = __( 'Product', 'wpas-documentation' );
		}
		$documentation_columns['menu_order'] = __( 'Order', 'wpas-documentation' );

		return $documentation_columns;
	}

	/**
	 * Render the data in the custom columns on the CPT list screen as the columns are called by WP
	 *
	 * @since  2.0.0
	 * @return void
	 */
	function show_documentation_order_columns_data($name) {
		global $post;

		switch ($name) {
			case 'menu_order':
				$order = $post->menu_order;
				echo $order;
				break;

			case 'product':
				//echo $post->product;  // this doesn't work - need to get taxonomy term
				$terms = wp_get_post_terms( $post->ID, 'product');

				 if ( ( $terms != null ) && ( !is_wp_error( $terms ) ) ) {
					foreach( $terms as $term ) {
						// Print the name method from $term which is an OBJECT
						echo $term->name ;
						// add newline character
						echo '<br />';
						// Get rid of the other data stored in the object, since it's not needed
						unset($term);
					}
				} else {
					// WP_Error
					echo '—';
				}
				break;

			default:
				break;
	   }
	}

	/**
	 * Set certain columns as being sortable
	 *
	 * @since  2.0.0
	 * @return array<string,string>
	 */
	function order_documentation_sortable_columns($columns) {
		$columns['menu_order'] 		= 'menu_order';
		$columns['as-doc-chapter'] 	= 'as-doc-chapter';
		$columns['as-doc-version'] 	= 'as-doc-version';
		return $columns;
	}


	/**
	 * Display a custom taxonomy dropdown for chapters in admin list for the documentation CPT
	 *
	 * @since  2.0.0
	 * @return void
	 */
	function custom_filter_post_type_by_chapter_taxonomy() {
		$this->custom_filter_post_type_by_taxonomy('documentation', 'as-doc-chapter');
	}

	/**
	 * Filter the posts by chapter after user selects a chapter from the custom taxonomy dropdown in the admin list for the documentation CPT
	 *
	 * @since  2.0.0
	 * @return void
	 */
	function convert_id_to_term_in_query_for_chapters($query) {
		$this->convert_id_to_term_in_query($query, 'documentation', 'as-doc-chapter');
	}

	/**
	 * Display a custom taxonomy dropdown for versions in admin list for the documentation CPT
	 *
	 * @since  2.0.0
	 * @return void
	 */
	function custom_filter_post_type_by_version_taxonomy() {
		$this->custom_filter_post_type_by_taxonomy('documentation', 'as-doc-version');
	}

	/**
	 * Filter the posts by version after user selects a version from the custom taxonomy dropdown in the admin list for the documentation CPT
	 *
	 * @since  2.0.0
	 * @return void
	 */
	function convert_id_to_term_in_query_for_versions($query) {
		$this->convert_id_to_term_in_query($query, 'documentation', 'as-doc-version');
	}

	/**
	 * Display a custom taxonomy dropdown for sections in admin list for the documentation CPT
	 *
	 * @since  2.0.0
	 * @return void
	 */
	function custom_filter_post_type_by_section_taxonomy() {
		$this->custom_filter_post_type_by_taxonomy('documentation', 'as-doc-section');
	}

	/**
	 * Filter the posts by section after user selects a section from the custom taxonomy dropdown in the admin list for the documentation CPT
	 *
	 * @since  2.0.0
	 * @return void
	 */
	function convert_id_to_term_in_query_for_sections($query) {
		$this->convert_id_to_term_in_query($query, 'documentation', 'as-doc-section');
	}


	/**
	 * Display a custom taxonomy dropdown for categories in admin list for the documentation CPT
	 *
	 * @since  2.0.0
	 * @return void
	 */
	function custom_filter_post_type_by_category_taxonomy() {
		$this->custom_filter_post_type_by_taxonomy('documentation', 'as-doc-category');
	}

	/**
	 * Filter the posts by category after user selects a category from the custom taxonomy dropdown in the admin list for the documentation CPT
	 *
	 * @since  2.0.0
	 * @return void
	 */
	function convert_id_to_term_in_query_for_categories($query) {
		$this->convert_id_to_term_in_query($query, 'documentation', 'as-doc-category');
	}

	/**
	 * Display a custom taxonomy dropdown for products in admin list for the documentation CPT
	 *
	 * @since  2.0.0
	 * @return void
	 */
	function custom_filter_post_type_by_product_taxonomy() {
		$this->custom_filter_post_type_by_taxonomy('documentation', 'product');
	}

	/**
	 * Filter the posts by product after user selects a product from the custom taxonomy dropdown in the admin list for the documentation CPT
	 *
	 * @since  2.0.0
	 * @return void
	 */
	function convert_id_to_term_in_query_for_products($query) {
		$this->convert_id_to_term_in_query($query, 'documentation', 'product');
	}

	/**
	 * Display a custom taxonomy dropdown for section/chapter/category in admin list for the documentation CPT
	 * @author Mike Hemberger
	 * @link http://thestizmedia.com/custom-post-type-filter-admin-custom-taxonomy/
	 *
	 * @since  2.0.0
	 * @return void
	 */
	function custom_filter_post_type_by_taxonomy($posttype = 'documentation', $taxo = 'as-doc-chapter') {
		global $typenow;
		$post_type = $posttype; // change to your post type
		$taxonomy  = $taxo; 	// change to your taxonomy

		if ( $typenow == $post_type && taxonomy_exists( $taxonomy ) ) {
			$selected      = isset( $_GET[$taxonomy] ) ? $_GET[$taxonomy] : '';
			$info_taxonomy = get_taxonomy( $taxonomy );
			wp_dropdown_categories(array(
				'show_option_all' => __( "Show All {$info_taxonomy->label}" ),
				'taxonomy'        => $taxonomy,
				'name'            => $taxonomy,
				'orderby'         => 'name',
				'selected'        => $selected,
				'show_count'      => true,
				'hide_empty'      => true,
			) );
		};
	}

	/**
	 * Filter the posts after user selects a section/chaper/category from the custom taxonomy dropdown in the admin list for the documentation CPT
	 * @author Mike Hemberger
	 * @link http://thestizmedia.com/custom-post-type-filter-admin-custom-taxonomy/
	 *
	 * @since  2.0.0
	 * @return void
	 */
	function convert_id_to_term_in_query($query, $posttype = 'documentation', $taxo = 'as-doc-chapter') {
		global $pagenow;
		$post_type = $posttype; // change to your post type
		$taxonomy  = $taxo; 	// change to your taxonomy

		// return if taxonomy does not exist
		if ( !taxonomy_exists( $taxonomy ) ) { return; }

		$q_vars    = &$query->query_vars;
		if ( $pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0 ) {
			$term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
			$q_vars[$taxonomy] = $term->slug;
		}
	}

	/**
	 * Set custom post type template to the one we have in the /templates dir
	 */
	function set_custom_post_type_template( $single_template ) {
		global $post;

		if ( $post->post_type == 'documentation' ) {
			if( file_exists( dirname( __FILE__ ) . '/templates/single-documentation.php') ) {
				$single_template = dirname( __FILE__ ) . '/templates/single-documentation.php';
			}
		}

		return $single_template;
	}

	/**
	 * Removes admin bar dump from the template
	 */
	function hide_admin_bar_from_front_end() {
		if ( is_blog_admin() ) {
			return true;
		}
		
		if ( true === boolval( asdoc_get_option( 'asdoc-customization-hide-top-bar', false ) ) ) {
			// Even though this is wrapped in an option, turning the option off doesn't seem to do anything - the top bar is always hidden on the front-end for some reason. CSS related?
			remove_action( 'wp_head', '_admin_bar_bump_cb' );
		}
		
		return false;
	}

	function body_classes( $classes ) {
	 	$classes[] = 'dx-wpas-docs';

		return $classes;
	}


	function register_my_menu() {
		register_nav_menu( 'wpas-docs-top-menu', __( 'Documentation Top Menu', 'wpas-documentation' ) );
	}
}
