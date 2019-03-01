<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

require_once(MINERVA_KB_PLUGIN_DIR . 'lib/helpers/icon-options.php');

/**
 * Class MinervaKB_CPT
 * Manages custom post type creation and edit pages
 */
class MinervaKB_CPT {

	private $info;

	private $restrict;

	/**
	 * Constructor
	 */
	public function __construct($deps) {

		$this->setup_dependencies($deps);

		$article_cpt = MKB_Options::option('article_cpt');
		$topic_taxonomy = MKB_Options::option('article_cpt_category');

		// post types
		add_action('init', array($this, 'register_post_types'), 10);

		// topic settings
		add_action($topic_taxonomy . '_edit_form_fields', array($this, 'topic_edit_screen_html'), 10, 2);
		add_action('edited_' . $topic_taxonomy, array($this, 'save_topic_meta'), 10, 2);
		add_action('create_' . $topic_taxonomy, array($this, 'save_topic_meta'), 10, 2);
		add_action('delete_' . $topic_taxonomy, array($this, 'delete_topic_meta'), 10, 2);

		// Drag n Drop articles reorder
		add_action('pre_get_posts', array($this, 'admin_custom_articles_order'));
		add_action('pre_get_posts', array($this, 'custom_articles_order'));

		// extra post list columns
		add_filter('manage_' . $article_cpt . '_posts_columns', array($this, 'set_custom_edit_kb_columns'));
		add_action('manage_' . $article_cpt . '_posts_custom_column' , array($this, 'custom_kb_column'), 0, 2);
		add_filter('manage_edit-' . $article_cpt . '_sortable_columns', array($this, 'sortable_kb_column'));
		add_action('pre_get_posts', array($this, 'kb_list_orderby'));

		// filter topic & tags selects
		add_action( 'restrict_manage_posts', array($this, 'article_list_topic_filter'), 10, 2);
		add_filter( 'parse_query', array($this, 'filter_request_query_topic') , 10);
		add_action( 'restrict_manage_posts', array($this, 'article_list_tag_filter'), 10, 2);
		add_filter( 'parse_query', array($this, 'filter_request_query_tag') , 10);

        add_filter( 'use_block_editor_for_post_type', array($this, 'articles_block_editor_filter'), 10, 2 );
        add_filter( 'use_block_editor_for_post_type', array($this, 'faq_block_editor_filter'), 10, 2 );
	}

	/**
	 * Sets up dependencies
	 * @param $deps
	 */
	private function setup_dependencies($deps) {
		if (isset($deps['info'])) {
			$this->info = $deps['info'];
		}

		if (isset($deps['restrict'])) {
			$this->restrict = $deps['restrict'];
		}
	}

	/**
	 * Registers all configured custom post types
	 */
	public function register_post_types() {

		$this->register_article_cpt();
		$this->register_topic_taxonomy();
		$this->register_tag_taxonomy();

		if (MKB_Options::option('add_article_versions')) {
			$this->register_versions_taxonomy();
		}

		// flush rewrite rules for CPT that have public URLs
		$this->maybe_flush_rules();

		// Feedback
		$this->register_feedback_cpt();

		// FAQ
		if (!MKB_Options::option('disable_faq')) {
			$this->register_faq_cpt();
			$this->register_faq_taxonomy();
		}
	}

	/**
	 * Flush rewrite rules if never flushed
	 */
	private function maybe_flush_rules () {
		// NOTE: needed to make CPT visible after register (force WP rewrite rules flush)
		if (MKB_Options::need_to_flush_rules()) {
			flush_rewrite_rules(false);

			MKB_Options::update_flush_flags();
		}
	}

	/**
	 * Registers KB article custom post type
	 */
	private function register_article_cpt() {
		$labels = array(
			'name' => MKB_Options::option( 'cpt_label_name' ),
			'singular_name' => MKB_Options::option( 'cpt_label_singular_name' ),
			'menu_name' => 'MinervaKB',
			'all_items' => MKB_Options::option( 'cpt_label_menu_name' ),
			'view_item' => MKB_Options::option( 'cpt_label_view_item' ),
			'add_new_item' => MKB_Options::option( 'cpt_label_add_new_item' ),
			'add_new' => MKB_Options::option( 'cpt_label_add_new' ),
			'edit_item' => MKB_Options::option( 'cpt_label_edit_item' ),
			'update_item' => MKB_Options::option( 'cpt_label_update_item' ),
			'search_items' => MKB_Options::option( 'cpt_label_search_items' ),
			'not_found' => MKB_Options::option( 'cpt_label_not_found' ),
			'not_found_in_trash' => MKB_Options::option( 'cpt_label_not_found_in_trash' ),
		);

		$args = array(
			'description' => __( 'KB Articles', 'minerva-kb' ),
			'labels' => $labels,
			'supports' => array(
				'title',
				'editor',
				'excerpt',
				'thumbnail',
				'author',
				'comments',
				'revisions',
				'custom-fields',
			),
			'taxonomies' => array(
				MKB_Options::option( 'article_cpt_category' ),
				MKB_Options::option( 'article_cpt_tag' )
			),
			'hierarchical' => false,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => true,
			'show_in_admin_bar' => true,
			'menu_position' => 5,
			'menu_icon' => MINERVA_KB_IMG_URL . 'minerva-icon.png',
			'can_export' => true,
			'has_archive' => (bool) !MKB_Options::option('cpt_archive_disable_switch'),
			'exclude_from_search' => false,
			'publicly_queryable' => true,
			'capability_type' => 'post',
            'show_in_rest' => true
		);

		if (MKB_Options::option( 'cpt_slug_switch' )) {
			$args["rewrite"] = array(
				"slug" => MKB_Options::option( 'article_slug' ),
				"with_front" => MKB_Options::option( 'cpt_slug_front_switch' )
			);
		}

		register_post_type( MKB_Options::option( 'article_cpt' ), $args );
	}

	/**
	 * Registers KB topic custom taxonomy
	 */
	private function register_topic_taxonomy() {
		$args = array(
			'labels' => array(
				'name' => MKB_Options::option( 'cpt_topic_label_name' ),
				'add_new_item' => MKB_Options::option( 'cpt_topic_label_add_new' ),
				'new_item_name' => MKB_Options::option( 'cpt_topic_label_new_item_name' )
			),
			'show_ui' => true,
			'show_tagcloud' => false,
			'hierarchical' => true,
            'show_in_rest' => true
		);

		if (MKB_Options::option( 'cpt_category_slug_switch' )) {
			$args["rewrite"] = array(
				"slug" => MKB_Options::option( 'category_slug' ),
				"with_front" => MKB_Options::option( 'cpt_category_slug_front_switch' )
			);
		}

		register_taxonomy(
			MKB_Options::option( 'article_cpt_category' ),
			MKB_Options::option( 'article_cpt' ),
			$args
		);
	}

	/**
	 * Registers KB tag custom taxonomy
	 */
	private function register_tag_taxonomy() {
		$args = array(
			'labels' => array(
				'name' => MKB_Options::option( 'cpt_tag_label_name' ),
				'add_new_item' => MKB_Options::option( 'cpt_tag_label_add_new' ),
				'new_item_name' => MKB_Options::option( 'cpt_tag_label_new_item_name' )
			),
			'show_ui' => true,
			'publicly_queryable' => !MKB_Options::option( 'tags_disable' ),
			'show_tagcloud' => true,
			'hierarchical' => false,
            'show_in_rest' => true
		);

		if (MKB_Options::option( 'cpt_tag_slug_switch' )) {
			$args["rewrite"] = array(
				"slug" => MKB_Options::option( 'tag_slug' ),
				"with_front" => MKB_Options::option( 'cpt_tag_slug_front_switch' )
			);
		}

		register_taxonomy(
			MKB_Options::option( 'article_cpt_tag' ),
			MKB_Options::option( 'article_cpt' ),
			$args
		);
	}

	/**
	 * Registers KB versions custom taxonomy
	 */
	private function register_versions_taxonomy() {
		$args = array(
			'labels' => array(
				'name' => __( 'Versions', 'minerva-kb' ),
				'add_new_item' => __( 'Add version', 'minerva-kb' ),
				'new_item_name' => __( 'New version', 'minerva-kb' )
			),
			'show_ui' => true,
			'publicly_queryable' => (bool)MKB_Options::option( 'enable_versions_archive' ),
			'show_tagcloud' => true,
			'hierarchical' => false,
            'show_in_rest' => true
		);

		if (MKB_Options::option( 'enable_versions_archive' )) {
			$args["rewrite"] = array(
				"slug" => MKB_Options::option('versions_slug'),
				"with_front" => false
			);
		}

		register_taxonomy(
			'mkb_version',
			MKB_Options::option( 'article_cpt' ),
			$args
		);
	}

	/**
	 * Registers feedback custom post type
	 */
	private function register_feedback_cpt() {
		/**
		 * Feedback
		 */
		$feedback_labels = array(
			'name' => __('KB Feedback', 'minerva-kb'),
			'singular_name' => __('KB Feedback', 'minerva-kb'),
			'menu_name' => __('KB Feedback', 'minerva-kb'),
			'all_items' => __('All suggestions', 'minerva-kb'),
			'view_item' => __('View suggestion', 'minerva-kb'),
			'add_new_item' => __('Add new suggestion', 'minerva-kb'),
			'add_new' => __('Add new', 'minerva-kb'),
			'edit_item' => __('Edit suggestion', 'minerva-kb'),
			'update_item' => __('Update suggestion', 'minerva-kb'),
			'search_items' => __('Search suggestions', 'minerva-kb'),
			'not_found' => __('Suggestions not found', 'minerva-kb'),
			'not_found_in_trash' => __('Suggestions not found in trash', 'minerva-kb'),
		);

		$feedback_args = array(
			'description' => __( 'KB Feedback', 'minerva-kb' ),
			'labels' => $feedback_labels,
			'supports' => array(
				'title',
				'editor',
				'author',
				'revisions'
			),
			'hierarchical' => false,
			'public' => false,
			'show_ui' => false,
			'show_in_menu' => false,
			'show_in_nav_menus' => false,
			'show_in_admin_bar' => false,
			'menu_position' => 5,
			'menu_icon' => 'dashicons-welcome-learn-more',
			'can_export' => false,
			'has_archive' => false,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'capability_type' => 'post',
            'show_in_rest' => true
		);

		register_post_type( 'mkb_feedback', $feedback_args );
	}

	/**
	 * Registers FAQ custom post type
	 */
	private function register_faq_cpt() {
		/**
		 * FAQ
		 */
		$faq_labels = array(
			'name' => 'KB FAQ',
			'singular_name' => 'KB FAQ',
			'menu_name' => 'KB FAQ',
			'all_items' => 'All questions',
			'view_item' => 'View question',
			'add_new_item' => 'Add new question',
			'add_new' => 'Add new',
			'edit_item' => 'Edit question',
			'update_item' => 'Update question',
			'search_items' => 'Search question',
			'not_found' => 'Questions not found',
			'not_found_in_trash' => 'Questions not found in trash',
		);

		$faq_args = array(
			'description' => __( 'KB FAQ', 'minerva-kb' ),
			'labels' => $faq_labels,
			'supports' => array(
				'title',
				'editor',
				'author',
				'revisions'
			),
			'hierarchical' => false,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => false,
			'show_in_admin_bar' => false,
			'menu_position' => 5,
			'menu_icon' => 'dashicons-welcome-learn-more',
			'can_export' => true,
			'has_archive' => false,
			'exclude_from_search' => ! (bool) MKB_Options::option('faq_include_in_search'),
			'publicly_queryable' => (bool) MKB_Options::option('faq_enable_pages'),
			'capability_type' => 'post',
			'rewrite' => array(
				"slug" => MKB_Options::option('faq_slug'),
				"with_front" => true
			),
            'show_in_rest' => true
		);

		register_post_type( 'mkb_faq', $faq_args );
	}

	/**
	 * Registers KB topic custom taxonomy
	 */
	private function register_faq_taxonomy() {
		$args = array(
			'labels' => array(
				'name' => __( 'Categories', 'minerva-kb' ),
				'add_new_item' => __( 'Add category', 'minerva-kb' ),
				'new_item_name' => __( 'New category', 'minerva-kb' )
			),
			'show_ui' => true,
			'show_tagcloud' => false,
			'hierarchical' => true,
            'show_in_rest' => true
		);

		register_taxonomy(
			'mkb_faq_category',
			'mkb_faq',
			$args
		);
	}

	/**
	 * KB Topic edit screen settings
	 * @param $term
	 */
	public function topic_edit_screen_html($term) {

		$term_id = $term->term_id;
		$term_meta = get_option( "taxonomy_" . MKB_Options::option( 'article_cpt_category' ) . '_' . $term_id );

		$settings_helper = new MKB_SettingsBuilder(array(
			'topic' => true,
			'no_tabs' => true
		));

		$pages_args = array(
			'sort_order' => 'asc',
			'sort_column' => 'post_title',
			'hierarchical' => 1,
			'exclude' => '',
			'include' => '',
			'meta_key' => '',
			'meta_value' => '',
			'authors' => '',
			'child_of' => 0,
			'parent' => - 1,
			'exclude_tree' => '',
			'number' => '',
			'offset' => 0,
			'post_type' => 'page',
			'post_status' => 'publish,private,draft'
		);

		$pages = get_pages($pages_args);

		$page_options = array(
			'' => __('Please, select page', 'minerva-kb')
		);

		if ($pages) {
			$page_options = array_reduce($pages, function ($all, $page) {
				$all[ $page->ID ] = $page->post_title;
				return $all;
			}, $page_options);
		}

		$options = array(
			array(
				'id' => 'topic_color',
				'type' => 'color',
				'label' => __( 'Topic color', 'minerva-kb' ),
				'default' => '#4a90e2',
				'description' => __( 'Select a color for this topic (optional)', 'minerva-kb' )
			),
			array(
				'id' => 'topic_icon',
				'type' => 'icon_select',
				'label' => __( 'Topic icon', 'minerva-kb' ),
				'default' => 'fa-list-alt',
				'description' => __( 'Select an icon for this topic (optional)', 'minerva-kb' )
			),
			array(
				'id' => 'topic_image',
				'type' => 'media',
				'label' => __( 'Topic image', 'minerva-kb' ),
				'default' => '',
				'description' => __( 'You can use URL or select image from media library', 'minerva-kb' )
			),
			array(
				'id' => 'topic_page_switch',
				'type' => 'checkbox',
				'label' => __('Display page content instead of topic?', 'minerva-kb'),
				'default' => false,
				'description' => __('You can use page content with shortcodes to display more complex KB structures', 'minerva-kb')
			),
			array(
				'id' => 'topic_page',
				'type' => 'page_select',
				'label' => __( 'Select page to use as topic content', 'minerva-kb' ),
				'options' => $page_options,
				'default' => '',
				'description' => __('Page content will be displayed instead of this topic', 'minerva-kb')
			),
			array(
				'id' => 'topic_no_title_switch',
				'type' => 'checkbox',
				'label' => __('Hide title?', 'minerva-kb'),
				'default' => false,
				'description' => __('You can remove topic title from this topic. Useful when you add alternative heading in page content', 'minerva-kb')
			),
			array(
				'id' => 'topic_no_description_switch',
				'type' => 'checkbox',
				'label' => __('Hide description?', 'minerva-kb'),
				'default' => false,
				'description' => __('You can remove topic description from this topic', 'minerva-kb')
			),
			array(
				'id' => 'topic_no_breadcrumbs_switch',
				'type' => 'checkbox',
				'label' => __('Hide breadcrumbs?', 'minerva-kb'),
				'default' => false,
				'description' => __('You can remove breadcrumbs from this topic', 'minerva-kb')
			),
			array(
				'id' => 'topic_no_search_switch',
				'type' => 'checkbox',
				'label' => __('Hide search?', 'minerva-kb'),
				'default' => false,
				'description' => __('You can remove search from this topic', 'minerva-kb')
			),
		);

		/**
		 * Top level options
		 */
		if ($term->parent == '0') {

			$result = array( "" => __('Use default', 'minerva-kb') );

			$pages_args = array(
				'sort_order' => 'asc',
				'sort_column' => 'post_title',
				'hierarchical' => 1,
				'exclude' => '',
				'include' => '',
				'meta_key' => '',
				'meta_value' => '',
				'authors' => '',
				'child_of' => 0,
				'parent' => - 1,
				'exclude_tree' => '',
				'number' => '',
				'offset' => 0,
				'post_type' => 'page',
				'post_status' => 'publish'
			);

			$pages = get_pages($pages_args);

			if ($pages) {
				$result = array_reduce($pages, function ($all, $page) {
					$all[ $page->ID ] = $page->post_title;

					return $all;
				}, $result);
			}

			$top_level_options = array(
				array(
					'id' => 'topic_parent',
					'type' => 'page_select',
					'label' => __('Topic Knowledge Base Home', 'minerva-kb'),
					'options' => $result,
					'default' => '',
					'description' => __('This is optional. You can select different knowledge base root page for each topic (this affects KB Home link in breadcrumbs)', 'minerva-kb')
				),
				array(
					'id' => 'topic_product_switch',
					'type' => 'checkbox',
					'label' => __('Turn this topic into a product root?', 'minerva-kb'),
					'default' => false,
					'description' => __('If you make this topic a product root, all nested KB elements (like search and widgets) will be scoped to this topic and its children', 'minerva-kb')
				),
				array(
					'id' => 'topic_sidebar_switch',
					'type' => 'checkbox',
					'label' => __('Customize sidebar display for this topic?', 'minerva-kb'),
					'default' => false,
					'description' => __('When you use page content it may be helpful to remove sidebar or change it\'s position', 'minerva-kb')
				),
				array(
					'id' => 'topic_sidebar',
					'type' => 'image_select',
					'label' => __('Topic sidebar position', 'minerva-kb'),
					'options' => array(
						'none' => array(
							'label' => __('None', 'minerva-kb'),
							'img' => MINERVA_KB_IMG_URL . 'no-sidebar.png'
						),
						'left' => array(
							'label' => __('Left', 'minerva-kb'),
							'img' => MINERVA_KB_IMG_URL . 'left-sidebar.png'
						),
						'right' => array(
							'label' => __('Right', 'minerva-kb'),
							'img' => MINERVA_KB_IMG_URL . 'right-sidebar.png'
						),
					),
					'default' => 'right',
					'description' => __('You can add widgets to sidebars under Appearance - Widgets', 'minerva-kb')
				)
			);

			$options = array_merge($options, $top_level_options);
		}

		/**
		 * Restriction
		 */
		if (MKB_Options::option('restrict_on')):

			$restrict_options = array(
				array(
					'id' => 'topic_restrict_role',
					'type' => 'roles_select',
					'label' => __( 'Content restriction: who can view topic?', 'minerva-kb' ),
					'default' => 'none',
					'description' => __('You can restrict access not only for specific articles, but also to topics.', 'minerva-kb')
				),
			);

			$options = array_merge($options, $restrict_options);

		endif;

		?>

		</tbody>
		<tbody class="mkb-term-settings">

		<?php

		foreach ( $options as $option ):

			?>

			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="term_meta[<?php echo esc_attr($option["id"]); ?>]">
						<?php echo esc_html($option["label"]); ?></label>
				</th>
				<td>

					<?php

					$value = isset( $term_meta[$option["id"]] ) ? stripslashes($term_meta[$option["id"]]) : $option['default'];

					$settings_helper->render_option(
						$option["type"],
						$value,
						$option
					);

					?>

					<p class="description"><?php echo esc_html($option["description"]); ?></p>

				</td>
			</tr>

		<?php

		endforeach;

		?>

		<!-- WPML controls box fix begin -->
		<tr class="form-field">
			<th scope="row" valign="top"></th>
			<td></td>
		</tr>
		<!-- WPML controls box fix end -->

		</tbody>
	<?php
	}

	/**
	 * Handle topic settings save
	 * @param $term_id
	 */
	public function save_topic_meta( $term_id ) {
		if ( isset( $_POST['term_meta'] ) ) {

			$term_meta = get_option( "taxonomy_" . MKB_Options::option( 'article_cpt_category' ) . '_' . $term_id );
			$cat_keys = array_keys( $_POST['term_meta'] );

			foreach ( $cat_keys as $key ) {
				if ( isset ( $_POST['term_meta'][$key] ) ) {
					$term_meta[$key] = $_POST['term_meta'][$key];
				}
			}

			$checkboxes = array(
				'topic_page_switch',
				'topic_sidebar_switch',
				'topic_product_switch',
				'topic_no_title_switch',
				'topic_no_description_switch',
				'topic_no_breadcrumbs_switch',
				'topic_no_search_switch',
			);

			foreach($checkboxes as $cb) {
				if (in_array($cb, $cat_keys)) {
					continue;
				}

				$term_meta[ $cb ] = 'off';
			}

			update_option( "taxonomy_" . MKB_Options::option( 'article_cpt_category' ) . '_' . $term_id, $term_meta );
		}

		$this->restrict->invalidate_restriction_cache();
	}

	/**
	 * Handle topic settings delete
	 * @param $term_id
	 */
	public function delete_topic_meta( $term_id ) {
		delete_option( "taxonomy_" . MKB_Options::option( 'article_cpt_category' ) . '_' . $term_id );
	}

	/**
	 * Custom DnD articles order for admin screens
	 * @param $wp_query
	 */
	public function admin_custom_articles_order($wp_query) {
		if (!$this->info->is_admin() ||  // only for admin screen
		    !MKB_Options::option( 'enable_articles_reorder' ) || // only if reorder enabled
		    !isset($_GET[MKB_Options::option( 'article_cpt_category' )]) // only for articles list on topic edit screen
		) {
			return;
		}

		// NOTE: we cannot use $info->is_topic() here, since wp_query is not yet ready
		if ( isset( $wp_query->query['post_type'] ) &&
		     ! isset( $_GET['orderby'] ) &&
		     $wp_query->query['post_type'] === MKB_Options::option( 'article_cpt' ) ) {

			$wp_query->set( 'orderby', 'menu_order' );
			$wp_query->set( 'order', 'ASC' );
		}
	}

	/**
	 * Client side articles custom order
	 * @param $wp_query
	 */
	public function custom_articles_order($wp_query) {
		if ($this->info->is_admin()) {
			return;
		}

		// NOTE: we cannot use $info->is_topic() here, since wp_query is not yet ready
		if ( isset( $wp_query->tax_query ) &&
		     isset( $wp_query->tax_query->queries ) &&
		     ! empty( $wp_query->tax_query->queries ) &&
		     ! isset( $_GET['orderby'] )
		) {

			foreach ( $wp_query->tax_query->queries as $tax_query ) {
				if ( isset( $tax_query['taxonomy'] ) && $tax_query['taxonomy'] === MKB_Options::option( 'article_cpt_category' ) ) {

					if (MKB_Options::option( 'enable_articles_reorder' )) {
						$wp_query->set( 'orderby', 'menu_order' );
						$wp_query->set( 'order', 'ASC' );
					} else {
						$wp_query->set( 'orderby', MKB_Options::option('articles_orderby') );
						$wp_query->set( 'order', MKB_Options::option('articles_order') );
					}

					break;
				}
			}
		}
	}

	/**
	 * Admin articles list custom columns
	 */
	public function set_custom_edit_kb_columns($columns) {

		unset($columns['author']);
		unset($columns['date']);
		unset($columns['comments']);

		$columns['mkb_topics'] = __( 'Topics', 'minerva-kb' );
		$columns['mkb_tags'] = __( 'Tags', 'minerva-kb' );
		$columns['mkb_views'] = __( '<i class="fa fa-eye" title="Views"></i>', 'minerva-kb' );
		$columns['mkb_likes'] = __( '<i class="fa fa-thumbs-o-up" title="Likes"></i>', 'minerva-kb' );
		$columns['mkb_dislikes'] = __( '<i class="fa fa-thumbs-o-down" title="Dislikes"></i>', 'minerva-kb' );
		$columns['mkb_feedback'] = __( '<i class="fa fa-bullhorn" title="Feedback"></i>', 'minerva-kb' );

		$columns['author'] = __( 'Author', 'minerva-kb' );
		$columns['date'] = __( 'Date', 'minerva-kb' );
		$columns['comments'] = '<span class="vers comment-grey-bubble" title="Comments"><span class="screen-reader-text">Comments</span></span>';

		return $columns;
	}

	public function custom_kb_column( $column, $post_id ) {
		switch ( $column ) {

			case 'mkb_views':
				$views = get_post_meta($post_id, '_mkb_views', true);
				echo esc_html($views > 0 ? $views : 0);
				break;

			case 'mkb_likes':
				$likes = get_post_meta($post_id, '_mkb_likes', true);
				echo esc_html($likes > 0 ? $likes : 0);
				break;

			case 'mkb_dislikes':
				$dislikes = get_post_meta($post_id, '_mkb_dislikes', true);
				echo esc_html($dislikes > 0 ? $dislikes : 0);
				break;

			case 'mkb_topics':
				echo get_the_term_list( $post_id, MKB_Options::option('article_cpt_category'), '', ', ' );
				break;

			case 'mkb_tags':
				echo get_the_term_list( $post_id, MKB_Options::option('article_cpt_tag'), '', ', ' );
				break;

			case 'mkb_feedback':

				$feedback_args = array(
					'posts_per_page'   => - 1,
					'offset'           => 0,
					'category'         => '',
					'category_name'    => '',
					'orderby'          => 'DATE',
					'order'            => 'DESC',
					'include'          => '',
					'exclude'          => '',
					'meta_key'         => 'feedback_article_id',
					'meta_value'       => get_the_ID(),
					'post_type'        => 'mkb_feedback',
					'post_mime_type'   => '',
					'post_parent'      => '',
					'author'           => '',
					'author_name'      => '',
					'post_status'      => 'publish'
				);

				$feedback = get_posts( $feedback_args );

				echo esc_html(count($feedback));

				break;

			default:
				break;
		}
	}

	/**
	 * Make custom columns sortable
	 */
	public function sortable_kb_column( $columns ) {
		$columns['mkb_views'] = 'mkb_views';
		$columns['mkb_likes'] = 'mkb_likes';
		$columns['mkb_dislikes'] = 'mkb_dislikes';

		return $columns;
	}

	/**
	 * Order by custom columns
	 */
	public function kb_list_orderby( $query ) {
		if( !$this->info->is_admin() )
			return;

		$orderby = $query->get( 'orderby');

		if ( 'mkb_views' == $orderby ) {
			$query->set('orderby','meta_value_num title');
			$query->set('meta_query', array(
				'relation' => 'OR',
				array(
					'key' => '_mkb_views',
					'compare' => 'EXISTS',
				),
				array(
					'key' => '_mkb_views',
					'compare' => 'NOT EXISTS'
				)
			));
		} else if ( 'mkb_likes' == $orderby ) {
			$query->set('orderby','meta_value_num title');
			$query->set('meta_query', array(
				'relation' => 'OR',
				array(
					'key' => '_mkb_likes',
					'compare' => 'EXISTS',
				),
				array(
					'key' => '_mkb_likes',
					'compare' => 'NOT EXISTS'
				)
			));
		} else if ('mkb_dislikes' == $orderby) {
			$query->set('orderby','meta_value_num title');
			$query->set('meta_query', array(
				'relation' => 'OR',
				array(
					'key' => '_mkb_dislikes',
					'compare' => 'EXISTS',
				),
				array(
					'key' => '_mkb_dislikes',
					'compare' => 'NOT EXISTS'
				)
			));
		}
	}
	
	public function article_list_topic_filter($post_type){
		if (MKB_Options::option('article_cpt') !== $post_type){
			return; //check to make sure this is articles
		}

		$taxonomy_slug = MKB_Options::option('article_cpt_category');
		$taxonomy = get_taxonomy($taxonomy_slug);

		$selected = '';
		$request_attr = 'kbtopic_id'; //this will show up in the url
		if ( isset($_REQUEST[$request_attr] ) ) {
			$selected = $_REQUEST[$request_attr]; //in case the current page is already filtered
		}
		wp_dropdown_categories(array(
			'show_option_all' =>  __("Show All {$taxonomy->label}"),
			'taxonomy'        =>  $taxonomy_slug,
			'name'            =>  $request_attr,
			'orderby'         =>  'name',
			'selected'        =>  $selected,
			'hierarchical'    =>  true,
			'depth'           =>  3,
			'show_count'      =>  true, // Show number of post in parent term
			'hide_empty'      =>  false, // Don't show posts w/o terms
		));
	}

	public function filter_request_query_topic($query){
		//modify the query only if it is admin and main query.
		if ( !(is_admin() AND $query->is_main_query()) ){
			return $query;
		}

		//we want to modify the query for the targeted custom post.
		if ( MKB_Options::option('article_cpt') !== $query->query['post_type'] ){
			return $query;
		}

		//type filter
		if ( isset($_REQUEST['kbtopic_id']) &&  0 != $_REQUEST['kbtopic_id']){
			$term =  $_REQUEST['kbtopic_id'];
			$taxonomy_slug = MKB_Options::option('article_cpt_category');
			$query->query_vars['tax_query'] = array(
				array(
					'taxonomy'  => $taxonomy_slug,
					'field'     => 'ID',
					'terms'     => array($term)
				)
			);
		}

		return $query;
	}	
	
	public function article_list_tag_filter($post_type){
		if (MKB_Options::option('article_cpt') !== $post_type){
			return; //check to make sure this is articles
		}

		$taxonomy_slug = MKB_Options::option('article_cpt_tag');
		$taxonomy = get_taxonomy($taxonomy_slug);

		$selected = '';
		$request_attr = 'kbtag_id'; //this will show up in the url
		if ( isset($_REQUEST[$request_attr] ) ) {
			$selected = $_REQUEST[$request_attr]; //in case the current page is already filtered
		}
		wp_dropdown_categories(array(
			'show_option_all' =>  __("Show All {$taxonomy->label}"),
			'taxonomy'        =>  $taxonomy_slug,
			'name'            =>  $request_attr,
			'orderby'         =>  'name',
			'selected'        =>  $selected,
			'hierarchical'    =>  true,
			'depth'           =>  3,
			'show_count'      =>  true, // Show number of post in parent term
			'hide_empty'      =>  false, // Don't show posts w/o terms
		));
	}

	public function filter_request_query_tag($query){
		//modify the query only if it is admin and main query.
		if ( !(is_admin() AND $query->is_main_query()) ){
			return $query;
		}

		//we want to modify the query for the targeted custom post.
		if ( MKB_Options::option('article_cpt') !== $query->query['post_type'] ){
			return $query;
		}

		//type filter
		if ( isset($_REQUEST['kbtag_id']) &&  0 != $_REQUEST['kbtag_id']){
			$term =  $_REQUEST['kbtag_id'];
			$taxonomy_slug = MKB_Options::option('article_cpt_tag');
			$query->query_vars['tax_query'] = array(
				array(
					'taxonomy'  => $taxonomy_slug,
					'field'     => 'ID',
					'terms'     => array($term)
				)
			);
		}

		return $query;
	}

    public function articles_block_editor_filter( $use_block_editor, $post_type ) {
        if ( MKB_Options::option('article_cpt' ) === $post_type && MKB_Options::option('article_disable_block_editor')) {
            return false;
        }

        return $use_block_editor;
    }

    public function faq_block_editor_filter( $use_block_editor, $post_type ) {
        if ( 'mkb_faq' === $post_type && MKB_Options::option('faq_disable_block_editor')) {
            return false;
        }

        return $use_block_editor;
    }
}
