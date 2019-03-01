<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

class MKB_Options {

	const OPTION_KEY = 'minerva-kb-options';

	const WPML_DOMAIN = 'MinervaKB';

	public function __construct() {
		self::register();
	}

	public static function register() {}

	public static function get_options_defaults() {
		return array_reduce(self::get_non_ui_options(self::get_options()), function($defaults, $option) {
			$defaults[$option["id"]] = $option["default"];
			return $defaults;
		}, array());
	}

	/**
	 * Returns all options by id key
	 * @return mixed
	 */
	public static function get_options_by_id() {
		return array_reduce(self::get_non_ui_options(self::get_options()), function($options, $option) {
			$options[$option["id"]] = $option;
			return $options;
		}, array());
	}

	public static function get_options() {
		return array(
			/**
			 * Home page
			 */
			array(
				'id' => 'home_tab',
				'type' => 'tab',
				'label' => __( 'Home page: Layout', 'minerva-kb' ),
				'icon' => 'fa-home'
			),
			array(
				'id' => 'home_content_title',
				'type' => 'title',
				'label' => __( 'Home page content & layout', 'minerva-kb' ),
				'description' => __( 'Configure the content to display on home KB page', 'minerva-kb' )
			),
			array(
				'id' => 'home_options_info',
				'type' => 'info',
				'label' => 'This section controls parameters of KB Home Page created with plugin settings. ' .
							'Currently you can also use shortcode builder or VC elements to create KB Home Page. Shortcodes are more flexible, '.
				            'as they allow you to easily insert your content between KB sections and add multiple KB blocks as well. ' .
				            'Note, that if you\'re using page created with shortcodes or page builder, these settings won\'t apply to it, so you will not see changes. ',
			),
			array(
				'id' => 'kb_page',
				'type' => 'page_select',
				'label' => __( 'Select page to display KB content', 'minerva-kb' ),
				'options' => self::get_pages_options(),
				'default' => '',
				'description' => __( 'Don\'t forget to save settings before page preview', 'minerva-kb' )
			),
			array(
				'id' => 'kb_page_wpml_warning',
				'type' => 'warning',
				'label' => __( 'WPML note: home page via settings works only for one-language sites. To create multiple home pages for WPML, please use our page builder or shortcode builder.', 'minerva-kb' ),
				'show_if' => defined('ICL_LANGUAGE_CODE')
			),
			array(
				'id' => 'home_sections_switch',
				'type' => 'checkbox',
				'label' => __( 'Let me select home page sections', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Alternatively you can use page builder in page edit screen', 'minerva-kb' )
			),
			array(
				'id' => 'home_sections',
				'type' => 'layout_select',
				'label' => __( 'Select topics to display on home page', 'minerva-kb' ),
				'default' => 'search,topics',
				'options' => self::get_home_sections_options(),
				'description' => __( 'You can leave it empty to display all recent topics. NOTE: dynamic topics only work for list view', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'home_sections_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'page_template',
				'type' => 'select',
				'label' => __( 'Which page template to use?', 'minerva-kb' ),
				'options' => array(
					'theme' => __( 'Theme page template', 'minerva-kb' ),
					'plugin' => __( 'Plugin page template', 'minerva-kb' )
				),
				'default' => 'plugin',
				'experimental' => __( 'This is experimental feature and depends a lot on theme styles and layout', 'minerva-kb' ),
				'description' => __( 'Note, that you can override plugin templates in your theme. See documentation for details', 'minerva-kb' )
			),
			array(
				'id' => 'home_top_padding',
				'type' => 'css_size',
				'label' => __( 'Home page top padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => __( 'Distance between header and home page content', 'minerva-kb' )
			),
			array(
				'id' => 'home_bottom_padding',
				'type' => 'css_size',
				'label' => __( 'Home page bottom padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => __( 'Distance between home page content and footer', 'minerva-kb' )
			),
			array(
				'id' => 'home_page_container_switch',
				'type' => 'checkbox',
				'label' => __( 'Add container to home page content?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'You can use this if your theme supports fullwidth layout', 'minerva-kb' )
			),
			array(
				'id' => 'home_page_title_switch',
				'type' => 'checkbox',
				'label' => __( 'Show home page title?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'show_page_content',
				'type' => 'select',
				'label' => __( 'Show page content?', 'minerva-kb' ),
				'options' => array(
					'no' => __( 'No', 'minerva-kb' ),
					'before' => __( 'Before KB', 'minerva-kb' ),
					'after' => __( 'After KB', 'minerva-kb' )
				),
				'default' => 'no'
			),
			array(
				'id' => 'page_sidebar',
				'type' => 'image_select',
				'label' => __( 'Page sidebar position', 'minerva-kb' ),
				'options' => array(
					'none' => array(
						'label' => __( 'None', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'no-sidebar.png'
					),
					'left' => array(
						'label' => __( 'Left', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'left-sidebar.png'
					),
					'right' => array(
						'label' => __( 'Right', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'right-sidebar.png'
					),
				),
				'default' => 'none',
				'description' => __( 'You can add widgets to sidebars under Appearance - Widgets', 'minerva-kb' )
			),
			/**
			 * Home page: Topics
			 */
			array(
				'id' => 'home_topics_tab',
				'type' => 'tab',
				'label' => __( 'Home page: Topics', 'minerva-kb' ),
				'icon' => 'fa-home'
			),
			array(
				'id' => 'home_topics_title',
				'type' => 'title',
				'label' => __( 'Home page topics', 'minerva-kb' ),
				'description' => __( 'Configure the display of topics on home KB page', 'minerva-kb' )
			),
			array(
				'id' => 'home_view',
				'type' => 'image_select',
				'label' => __( 'Home topics view', 'minerva-kb' ),
				'options' => array(
					'list' => array(
						'label' => __( 'List view', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'list-view.png'
					),
					'box' => array(
						'label' => __( 'Box view', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'box-view.png'
					)
				),
				'default' => 'list'
			),
			array(
				'id' => 'home_layout',
				'type' => 'image_select',
				'label' => __( 'Page topics layout', 'minerva-kb' ),
				'options' => array(
					'2col' => array(
						'label' => __( '2 columns', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-2.png'
					),
					'3col' => array(
						'label' => __( '3 columns', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-3.png'
					),
					'4col' => array(
						'label' => __( '4 columns', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-4.png'
					),
				),
				'default' => '3col'
			),
			array(
				'id' => 'home_topics',
				'type' => 'term_select',
				'label' => __( 'Select topics to display on home page', 'minerva-kb' ),
				'default' => '',
				'tax' => self::get_saved_option('article_cpt_category', 'kbtopic'),
				'extra_items' => array(
					array(
						'key' => 'recent',
						'label' => __('Recent', 'minerva-kb')
					),
					array(
						'key' => 'updated',
						'label' => __('Recently updated', 'minerva-kb')
					),
					array(
						'key' => 'top_views',
						'label' => __('Most viewed', 'minerva-kb')
					),
					array(
						'key' => 'top_likes',
						'label' => __('Most liked', 'minerva-kb')
					)
				),
				'description' => __( 'You can leave it empty to display all recent topics. NOTE: dynamic topics only work for list view', 'minerva-kb' )
			),
			array(
				'id' => 'home_topics_limit',
				'type' => 'input',
				'label' => __( 'Number of topics to display', 'minerva-kb' ),
				'default' => -1,
				'description' => __( 'Used in case no specific topics are selected. You can use -1 to display all', 'minerva-kb' )
			),
			array(
				'id' => 'home_topics_hide_children',
				'type' => 'checkbox',
				'label' => __( 'Hide child topics?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'If you don\'t select specific topics, you can use this option to show only top-level topics', 'minerva-kb' )
			),
			array(
				'id' => 'home_topics_articles_limit',
				'type' => 'input',
				'label' => __( 'Number of article to display', 'minerva-kb' ),
				'default' => 5,
				'description' => __( 'You can use -1 to display all', 'minerva-kb' )
			),
			array(
				'id' => 'home_topics_show_description',
				'type' => 'checkbox',
				'label' => __( 'Show description?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'home_view',
					'type' => 'EQ',
					'value' => 'box'
				)
			),
			array(
				'id' => 'show_all_switch',
				'type' => 'checkbox',
				'label' => __( 'Add "Show all" link?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'show_all_label',
				'type' => 'input_text',
				'label' => __( 'Show all link label', 'minerva-kb' ),
				'default' => __( 'Show all', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_all_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_articles_count',
				'type' => 'checkbox',
				'label' => __( 'Show articles count?', 'minerva-kb' ),
				'default' => true
			),

			// COLORS
			array(
				'id' => 'home_topic_colors_title',
				'type' => 'title',
				'label' => __( 'Topic colors', 'minerva-kb' ),
				'description' => __( 'Configure topic colors', 'minerva-kb' )
			),
			array(
				'id' => 'topic_color',
				'type' => 'color',
				'label' => __( 'Topic color', 'minerva-kb' ),
				'default' => '#4a90e2',
				'description' => __( 'Note, that topic color can be changed for each topic individually on topic edit page', 'minerva-kb' )
			),
			array(
				'id' => 'force_default_topic_color',
				'type' => 'checkbox',
				'label' => __( 'Force topic color (override topic custom colors)?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'By default, colors from topic settings have higher priority. You can override it with this setting', 'minerva-kb' )
			),
			array(
				'id' => 'box_view_item_bg',
				'type' => 'color',
				'label' => __( 'Box view background', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'home_view',
					'type' => 'EQ',
					'value' => 'box'
				)
			),
			array(
				'id' => 'box_view_item_hover_bg',
				'type' => 'color',
				'label' => __( 'Box view hover background', 'minerva-kb' ),
				'default' => '#f8f8f8',
				'dependency' => array(
					'target' => 'home_view',
					'type' => 'EQ',
					'value' => 'box'
				)
			),
			array(
				'id' => 'articles_count_bg',
				'type' => 'color',
				'label' => __( 'Articles count background', 'minerva-kb' ),
				'default' => '#4a90e2',
				'dependency' => array(
					'target' => 'show_articles_count',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'articles_count_color',
				'type' => 'color',
				'label' => __( 'Articles count color', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'show_articles_count',
					'type' => 'EQ',
					'value' => true
				)
			),

			// ICONS
			array(
				'id' => 'home_topic_icons_title',
				'type' => 'title',
				'label' => __( 'Topic icons', 'minerva-kb' ),
				'description' => __( 'Configure topic icons settings', 'minerva-kb' )
			),
			array(
				'id' => 'show_topic_icons',
				'type' => 'checkbox',
				'label' => __( 'Show topic icons?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'topic_icon',
				'type' => 'icon_select',
				'label' => __( 'Default topic icon', 'minerva-kb' ),
				'default' => 'fa-list-alt',
				'description' => __( 'Note, that topic icon can be changed for each topic individually on topic edit page', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_topic_icons',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'force_default_topic_icon',
				'type' => 'checkbox',
				'label' => __( 'Force topic icon (override topic custom icons)?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'By default, icons from topic settings have higher priority. You can override it with this setting', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_topic_icons',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'use_topic_image',
				'type' => 'checkbox',
				'label' => __( 'Box view only: Show image instead of icon? Image URL can be added on each topic page', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'show_topic_icons',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'image_size',
				'type' => 'css_size',
				'label' => __( 'Topic image size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "10"),
				'description' => 'Use any CSS value, for ex. 2em or 20px',
				'dependency' => array(
					'target' => 'show_topic_icons',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_icon_padding_top',
				'type' => 'css_size',
				'label' => __( 'Topic icon/image top padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => 'Use any CSS value, for ex. 2em or 20px',
				'dependency' => array(
					'target' => 'show_topic_icons',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_icon_padding_bottom',
				'type' => 'css_size',
				'label' => __( 'Topic icon/image bottom padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => 'Use any CSS value, for ex. 2em or 20px',
				'dependency' => array(
					'target' => 'show_topic_icons',
					'type' => 'EQ',
					'value' => true
				)
			),

			// ARTICLES
			array(
				'id' => 'home_articles_title',
				'type' => 'title',
				'label' => __( 'Articles settings', 'minerva-kb' ),
				'description' => __( 'Configure how articles list should look on home KB page', 'minerva-kb' )
			),
			array(
				'id' => 'show_article_icons',
				'type' => 'checkbox',
				'label' => __( 'List view only: Show article icons?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'article_icon',
				'type' => 'icon_select',
				'label' => __( 'Article icon', 'minerva-kb' ),
				'default' => 'fa-book',
				'dependency' => array(
					'target' => 'show_article_icons',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_article_views',
				'type' => 'checkbox',
				'label' => __( 'List view only: Show article views?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'show_article_likes',
				'type' => 'checkbox',
				'label' => __( 'List view only: Show article likes?', 'minerva-kb' ),
				'default' => false
			),
			/**
			 * Search home
			 */
			array(
				'id' => 'search_home_tab',
				'type' => 'tab',
				'label' => __( 'Home page: Search', 'minerva-kb' ),
				'icon' => 'fa-home'
			),
			array(
				'id' => 'home_search_title',
				'type' => 'title',
				'label' => __( 'Home page search', 'minerva-kb' ),
				'description' => __( 'Configure the display of search box on home KB page', 'minerva-kb' )
			),
			array(
				'id' => 'search_title',
				'type' => 'input_text',
				'label' => __( 'Search title', 'minerva-kb' ),
				'default' => __( 'Need some help?', 'minerva-kb' )
			),
			array(
				'id' => 'search_title_size',
				'type' => 'css_size',
				'label' => __( 'Search title font size', 'minerva-kb' ),
				'default' => array('unit' => 'em', 'size' => '3'),
				'description' => 'Use any CSS value, for ex. 3em or 20px',
				'dependency' => array(
					'target' => 'search_title',
					'type' => 'NEQ',
					'value' => ''
				)
			),
			array(
				'id' => 'search_theme',
				'type' => 'select',
				'label' => __( 'Which search input theme to use?', 'minerva-kb' ),
				'options' => array(
					'minerva' => __( 'Minerva', 'minerva-kb' ),
					'clean' => __( 'Clean', 'minerva-kb' ),
					'mini' => __( 'Mini', 'minerva-kb' ),
					'bold' => __( 'Bold', 'minerva-kb' ),
					'invisible' => __( 'Invisible', 'minerva-kb' ),
					'thick' => __( 'Thick', 'minerva-kb' ),
					'3d' => __( '3d', 'minerva-kb' ),
				),
				'default' => 'minerva',
				'description' => __( 'Use predefined styles for search bar', 'minerva-kb' )
			),
			array(
				'id' => 'search_min_width',
				'type' => 'css_size',
				'label' => __( 'Search input minimum width', 'minerva-kb' ),
                'default' => array('unit' => 'em', 'size' => '38'),
				'description' => 'Use any CSS value, for ex. 40em or 300px. em are better for mobile devices'
			),
			array(
				'id' => 'search_container_padding_top',
				'type' => 'css_size',
				'label' => __( 'Search container top padding', 'minerva-kb' ),
                'default' => array('unit' => 'em', 'size' => '3'),
				'description' => 'Use any CSS value, for ex. 3em or 50px'
			),
			array(
				'id' => 'search_container_padding_bottom',
				'type' => 'css_size',
				'label' => __( 'Search container bottom padding', 'minerva-kb' ),
                'default' => array('unit' => 'em', 'size' => '3'),
				'description' => 'Use any CSS value, for ex. 3em or 50px'
			),
			array(
				'id' => 'search_placeholder',
				'type' => 'input_text',
				'label' => __( 'Search placeholder', 'minerva-kb' ),
				'default' => __( 'ex.: Installation', 'minerva-kb' )
			),
			array(
				'id' => 'search_topics',
				'type' => 'term_select',
				'label' => __( 'Optional: you can limit search to specific topics', 'minerva-kb' ),
				'default' => '',
				'tax' => self::get_saved_option('article_cpt_category', 'kbtopic'),
				'description' => __( 'You can leave it empty to search all topics (default).', 'minerva-kb' )
			),
			array(
				'id' => 'disable_autofocus',
				'type' => 'checkbox',
				'label' => __( 'Disable search field autofocus?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'show_search_tip',
				'type' => 'checkbox',
				'label' => __( 'Show search tip?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'search_tip',
				'type' => 'input_text',
				'label' => __( 'Search tip (under the input)', 'minerva-kb' ),
				'default' => __( 'Tip: Use arrows to navigate results, ESC to focus search input', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_search_tip',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_topic_in_results',
				'type' => 'checkbox',
				'label' => __( 'Show topic in results?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'search_results_multiline',
				'type' => 'checkbox',
				'label' => __( 'Allow multiline titles in results?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'By default, results are fit in one line. You can change this to allow multiline titles', 'minerva-kb' )
			),
			array(
				'id' => 'search_result_topic_label',
				'type' => 'input_text',
				'label' => __( 'Search result topic label', 'minerva-kb' ),
				'default' => __( 'Topic', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_topic_in_results',
					'type' => 'EQ',
					'value' => true
				)
			),
			// COLORS
			array(
				'id' => 'home_search_colors_title',
				'type' => 'title',
				'label' => __( 'Search colors and background', 'minerva-kb' ),
				'description' => __( 'Configure search style', 'minerva-kb' )
			),
			array(
				'id' => 'search_title_color',
				'type' => 'color',
				'label' => __( 'Search title color', 'minerva-kb' ),
				'default' => '#333333',
				'dependency' => array(
					'target' => 'search_title',
					'type' => 'NEQ',
					'value' => ''
				)
			),
			array(
				'id' => 'search_border_color',
				'type' => 'color',
				'label' => __( 'Search wrap border color (not in all themes)', 'minerva-kb' ),
				'default' => '#ffffff'
			),
			array(
				'id' => 'search_container_bg',
				'type' => 'color',
				'label' => __( 'Search container background color', 'minerva-kb' ),
				'default' => '#ffffff'
			),
			array(
				'id' => 'search_container_image_bg',
				'type' => 'media',
				'label' => __( 'Search container background image URL (optional)', 'minerva-kb' ),
				'default' => array('isUrl' => true, 'img' => '')
			),
			array(
				'id' => 'add_gradient_overlay',
				'type' => 'checkbox',
				'label' => __( 'Add gradient overlay?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'search_container_gradient_from',
				'type' => 'color',
				'label' => __( 'Container gradient from', 'minerva-kb' ),
				'default' => '#00c1b6',
				'dependency' => array(
					'target' => 'add_gradient_overlay',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'search_container_gradient_to',
				'type' => 'color',
				'label' => __( 'Container gradient to', 'minerva-kb' ),
				'default' => '#136eb5',
				'dependency' => array(
					'target' => 'add_gradient_overlay',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'search_container_gradient_opacity',
				'type' => 'range',
				'label' => __( 'Search container background gradient opacity', 'minerva-kb' ),
				'default' => 1,
				'min' => 0,
				'max' => 1,
				'step' => 0.05,
				'description' => __( 'Use any CSS opacity value, for example 1 or 0.7', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_gradient_overlay',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'add_pattern_overlay',
				'type' => 'checkbox',
				'label' => __( 'Add pattern overlay?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'search_container_image_pattern',
				'type' => 'media',
				'label' => __( 'Search container background pattern image (optional)', 'minerva-kb' ),
                'default' => array('isUrl' => true, 'img' => ''),
				'dependency' => array(
					'target' => 'add_pattern_overlay',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'search_container_image_pattern_opacity',
				'type' => 'range',
				'label' => __( 'Search container background pattern opacity', 'minerva-kb' ),
				'default' => 1,
                'min' => 0,
                'max' => 1,
                'step' => 0.05,
				'description' => __( 'Use any CSS opacity value, for example 1 or 0.7. You can also use transparent .png and set opacity to 1', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_pattern_overlay',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'search_tip_color',
				'type' => 'color',
				'label' => __( 'Search tip color', 'minerva-kb' ),
				'default' => '#cccccc',
				'dependency' => array(
					'target' => 'show_search_tip',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'search_results_topic_bg',
				'type' => 'color',
				'label' => __( 'Search results topic background', 'minerva-kb' ),
				'default' => '#4a90e2',
				'dependency' => array(
					'target' => 'show_topic_in_results',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'search_results_topic_color',
				'type' => 'color',
				'label' => __( 'Search results topic color', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'show_topic_in_results',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'search_results_topic_use_custom',
				'type' => 'checkbox',
				'label' => __( 'Use custom topic colors in search results?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Topic custom color will be used as background color for topic label', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_topic_in_results',
					'type' => 'EQ',
					'value' => true
				)
			),

			// ICONS
			array(
				'id' => 'home_search_icons_title',
				'type' => 'title',
				'label' => __( 'Search icons', 'minerva-kb' ),
				'description' => __( 'Configure search icons', 'minerva-kb' )
			),
			array(
				'id' => 'search_icons_left',
				'type' => 'checkbox',
				'label' => __( 'Show search bar icons on the left side?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'show_search_icon',
				'type' => 'checkbox',
				'label' => __( 'Show search icon?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'search_icon',
				'type' => 'icon_select',
				'label' => __( 'Search icon', 'minerva-kb' ),
				'default' => 'fa-search',
				'dependency' => array(
					'target' => 'show_search_icon',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'search_clear_icon',
				'type' => 'icon_select',
				'label' => __( 'Search clear icon', 'minerva-kb' ),
				'default' => 'fa-times-circle'
			),
			/**
			 * FAQ home
			 */
			array(
				'id' => 'faq_home_tab',
				'type' => 'tab',
				'label' => __( 'Home page: FAQ', 'minerva-kb' ),
				'icon' => 'fa-home'
			),
			array(
				'id' => 'home_faq_section_title',
				'type' => 'title',
				'label' => __( 'Home page FAQ section', 'minerva-kb' ),
				'description' => __( 'Configure the display of FAQ on home KB page', 'minerva-kb' )
			),
			array(
				'id' => 'home_faq_title',
				'type' => 'input_text',
				'label' => __( 'FAQ title', 'minerva-kb' ),
				'default' => __( 'Frequently Asked Questions', 'minerva-kb' )
			),
			array(
				'id' => 'home_faq_title_size',
				'type' => 'css_size',
				'label' => __( 'FAQ title font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "3"),
				'description' => 'Use any CSS value, for ex. 3em or 20px',
				'dependency' => array(
					'target' => 'home_faq_title',
					'type' => 'NEQ',
					'value' => ''
				)
			),
			array(
				'id' => 'home_faq_title_color',
				'type' => 'color',
				'label' => __( 'FAQ title color', 'minerva-kb' ),
				'default' => '#333333',
				'dependency' => array(
					'target' => 'home_faq_title',
					'type' => 'NEQ',
					'value' => ''
				)
			),
			array(
				'id' => 'home_faq_layout_section_title',
				'type' => 'title',
				'label' => __( 'Home FAQ layout', 'minerva-kb' ),
				'description' => __( 'Configure FAQ layout on home page', 'minerva-kb' )
			),
			array(
				'id' => 'home_faq_margin_top',
				'type' => 'css_size',
				'label' => __( 'FAQ section top margin', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "3"),
				'description' => __( 'Distance between FAQ and previous section', 'minerva-kb' ),
			),
			array(
				'id' => 'home_faq_margin_bottom',
				'type' => 'css_size',
				'label' => __( 'FAQ section bottom margin', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "3"),
				'description' => __( 'Distance between FAQ and next sections', 'minerva-kb' ),
			),
			array(
				'id' => 'home_faq_limit_width_switch',
				'type' => 'checkbox',
				'label' => __( 'Limit FAQ container width?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'home_faq_width_limit',
				'type' => 'css_size',
				'label' => __( 'FAQ container maximum width', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "60"),
				'description' => __( 'You can make FAQ section more narrow, than your content width', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'home_faq_limit_width_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'home_faq_controls_margin_top',
				'type' => 'css_size',
				'label' => __( 'FAQ controls top margin', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "2"),
				'description' => __( 'Distance between FAQ controls and title', 'minerva-kb' ),
			),
			array(
				'id' => 'home_faq_controls_margin_bottom',
				'type' => 'css_size',
				'label' => __( 'FAQ controls bottom margin', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "2"),
				'description' => __( 'Distance between FAQ controls and questions', 'minerva-kb' ),
			),
			array(
				'id' => 'home_faq_controls_section_title',
				'type' => 'title',
				'label' => __( 'Home FAQ controls', 'minerva-kb' ),
				'description' => __( 'Configure FAQ controls on home page', 'minerva-kb' )
			),
			array(
				'id' => 'home_show_faq_filter',
				'type' => 'checkbox',
				'label' => __( 'Show FAQ live filter?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'home_show_faq_toggle_all',
				'type' => 'checkbox',
				'label' => __( 'Show FAQ toggle all button?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'home_faq_categories_section_title',
				'type' => 'title',
				'label' => __( 'FAQ categories settings', 'minerva-kb' ),
				'description' => __( 'Configure FAQ categories', 'minerva-kb' )
			),
			array(
				'id' => 'home_faq_categories',
				'type' => 'term_select',
				'label' => __( 'Select FAQ categories to display on home page', 'minerva-kb' ),
				'default' => '',
				'tax' => 'mkb_faq_category',
				'description' => __( 'You can leave it empty to display all categories.', 'minerva-kb' )
			),

			array(
				'id' => 'home_show_faq_categories',
				'type' => 'checkbox',
				'label' => __( 'Show FAQ categories?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'home_show_faq_category_count',
				'type' => 'checkbox',
				'label' => __( 'Show FAQ category question count?', 'minerva-kb' ),
				'default' => true,
			),
			array(
				'id' => 'home_faq_styles_note_title',
				'type' => 'title',
				'label' => __( 'NOTE: You can configure FAQ styles in FAQ (global)', 'minerva-kb' )
			),
			/**
			 * General
			 */
			array(
				'id' => 'general_tab',
				'type' => 'tab',
				'label' => __( 'General', 'minerva-kb' ),
				'icon' => 'fa-cogs'
			),
			array(
				'id' => 'general_content_title',
				'type' => 'title',
				'label' => __( 'General settings', 'minerva-kb' ),
				'description' => __( 'Configure general KB settings', 'minerva-kb' )
			),
			array(
				'id' => 'layout_title',
				'type' => 'title',
				'label' => __( 'Layout', 'minerva-kb' ),
				'description' => __( 'Configure KB layout', 'minerva-kb' )
			),
			array(
				'id' => 'container_width',
				'type' => 'css_size',
				'label' => __( 'Root container width', 'minerva-kb' ),
				'default' => array("unit" => 'px', "size" => "1180"),
				'units' => array('px', '%'),
				'description' => __( 'Container is the top level element that limits the width of KB content', 'minerva-kb' )
			),
			array(
				'id' => 'content_width',
				'type' => 'css_size',
				'label' => __( 'Content width (%)', 'minerva-kb' ),
				'default' => array("unit" => '%', "size" => "66"),
				'units' => array('%'),
				'description' => __( 'Use this setting to configure width of content vs sidebar, when sidebar is on. Sidebar will take rest of available space', 'minerva-kb' )
			),
			array(
				'id' => 'css_title',
				'type' => 'title',
				'label' => __( 'Custom CSS', 'minerva-kb' ),
				'description' => __( 'Add custom styling', 'minerva-kb' )
			),
			array(
				'id' => 'custom_css',
				'type' => 'textarea',
				'label' => __( 'CSS to add after plugin styles', 'minerva-kb' ),
				'height' => 20,
				'width' => 80,
				'default' => __( '', 'minerva-kb' )
			),
			array(
				'id' => 'pagination_title',
				'type' => 'title',
				'label' => __( 'Pagination', 'minerva-kb' ),
				'description' => __( 'Configure KB pagination', 'minerva-kb' )
			),
			array(
				'id' => 'pagination_style',
				'type' => 'select',
				'label' => __( 'Which pagination style to use on topic, tag, archive and search results pages?', 'minerva-kb' ),
				'options' => array(
					'plugin' => __( 'Minerva', 'minerva-kb' ),
					'theme' => __( 'WordPress default', 'minerva-kb' )
				),
				'default' => 'plugin',
				'description' => __( 'When WordPress default selected, theme styled pagination should appear', 'minerva-kb' )
			),
			array(
				'id' => 'pagination_bg',
				'type' => 'color',
				'label' => __( 'Pagination item background color', 'minerva-kb' ),
				'default' => '#f7f7f7',
				'dependency' => array(
					'target' => 'pagination_style',
					'type' => 'EQ',
					'value' => 'plugin'
				)
			),
			array(
				'id' => 'pagination_color',
				'type' => 'color',
				'label' => __( 'Pagination item text color', 'minerva-kb' ),
				'default' => '#333',
				'dependency' => array(
					'target' => 'pagination_style',
					'type' => 'EQ',
					'value' => 'plugin'
				)
			),
			array(
				'id' => 'pagination_link_color',
				'type' => 'color',
				'label' => __( 'Pagination item link color', 'minerva-kb' ),
				'default' => '#007acc',
				'dependency' => array(
					'target' => 'pagination_style',
					'type' => 'EQ',
					'value' => 'plugin'
				)
			),
			/**
			 * Styles
			 */
			array(
				'id' => 'styles_tab',
				'type' => 'tab',
				'label' => __( 'Typography & Styles', 'minerva-kb' ),
				'icon' => 'fa-paint-brush'
			),
			array(
				'id' => 'typography_title',
				'type' => 'title',
				'label' => __( 'Typography', 'minerva-kb' ),
				'description' => __( 'Configure KB fonts', 'minerva-kb' )
			),
			// typography
			array(
				'id' => 'typography_on',
				'type' => 'checkbox',
				'label' => __( 'Enable typography options?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'When off, theme styles will be used', 'minerva-kb' )
			),
			array(
				'id' => 'style_font',
				'type' => 'font',
				'label' => __( 'Font', 'minerva-kb' ),
				'default' => 'Roboto',
				'description' => __( 'Select font to use for KB', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'style_font_gf_weights',
				'type' => 'google_font_weights',
				'label' => __( 'Font weights to load (for Google Fonts only)', 'minerva-kb' ),
				'default' => array('400', '600'),
				'description' => __( 'Font weights to load from Google. Use Shift or Ctrl/Cmd to select multiple values. Note: more weights mean more load time', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'style_font_gf_languages',
				'type' => 'google_font_languages',
				'label' => __( 'Font languages to load (for Google Fonts only)', 'minerva-kb' ),
				'default' => array(),
				'description' => __( 'Font languages to load from Google. Latin set is always loaded. Use Shift or Ctrl/Cmd to select multiple values. Note: more languages mean more load time', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'dont_load_font',
				'type' => 'checkbox',
				'label' => __( 'Don\'t load font?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Can be useful if your theme or other plugin loads this font already', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'content_font_size',
				'type' => 'css_size',
				'label' => __( 'Article content font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1"),
				'description' => __( 'Content font size is used to proportionally change size article text', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'content_line_height',
				'type' => 'css_size',
				'label' => __( 'Article content line-height', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1.7"),
				'description' => __( 'Content line height', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'h1_font_size',
				'type' => 'css_size',
				'label' => __( 'H1 heading font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "2"),
				'description' => __( 'H1 heading', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'h2_font_size',
				'type' => 'css_size',
				'label' => __( 'H2 heading font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1.8"),
				'description' => __( 'H2 heading', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'h3_font_size',
				'type' => 'css_size',
				'label' => __( 'H3 heading font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1.6"),
				'description' => __( 'H3 heading', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'h4_font_size',
				'type' => 'css_size',
				'label' => __( 'H4 heading font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1.4"),
				'description' => __( 'H4 heading', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'h5_font_size',
				'type' => 'css_size',
				'label' => __( 'H5 heading font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1.2"),
				'description' => __( 'H5 heading', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'h6_font_size',
				'type' => 'css_size',
				'label' => __( 'H6 heading font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1"),
				'description' => __( 'H6 heading', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'widget_font_size',
				'type' => 'css_size',
				'label' => __( 'Widget content font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1"),
				'description' => __( 'Widget content font size', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'widget_heading_font_size',
				'type' => 'css_size',
				'label' => __( 'Widget heading font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1.3"),
				'description' => __( 'Widget heading font size', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			// text colors
			array(
				'id' => 'content_colors_title',
				'type' => 'title',
				'label' => __( 'Text styles', 'minerva-kb' ),
				'description' => __( 'Configure text and heading colors', 'minerva-kb' )
			),
			array(
				'id' => 'text_color',
				'type' => 'color',
				'label' => __( 'Article text color', 'minerva-kb' ),
				'default' => '#333'
			),
			array(
				'id' => 'text_link_color',
				'type' => 'color',
				'label' => __( 'Article text link color', 'minerva-kb' ),
				'default' => '#007acc'
			),
			array(
				'id' => 'h1_color',
				'type' => 'color',
				'label' => __( 'H1 heading color', 'minerva-kb' ),
				'default' => '#333'
			),
			array(
				'id' => 'h2_color',
				'type' => 'color',
				'label' => __( 'H2 heading color', 'minerva-kb' ),
				'default' => '#333'
			),
			array(
				'id' => 'h3_color',
				'type' => 'color',
				'label' => __( 'H3 heading color', 'minerva-kb' ),
				'default' => '#333'
			),
			array(
				'id' => 'h4_color',
				'type' => 'color',
				'label' => __( 'H4 heading color', 'minerva-kb' ),
				'default' => '#333'
			),
			array(
				'id' => 'h5_color',
				'type' => 'color',
				'label' => __( 'H5 heading color', 'minerva-kb' ),
				'default' => '#333'
			),
			array(
				'id' => 'h6_color',
				'type' => 'color',
				'label' => __( 'H6 heading color', 'minerva-kb' ),
				'default' => '#333'
			),

			/**
			 * Widgets
			 */
			array(
				'id' => 'widgets_tab',
				'type' => 'tab',
				'label' => __( 'Widgets', 'minerva-kb' ),
				'icon' => 'fa-cube'
			),
			array(
				'id' => 'widget_icons_on',
				'type' => 'checkbox',
				'label' => __( 'Show topic/article icons in widgets?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'content_tree_widget_icon',
				'type' => 'icon_select',
				'label' => __( 'Content tree widget topic icon', 'minerva-kb' ),
				'default' => 'fa-folder'
			),
			array(
				'id' => 'content_tree_widget_icon_open',
				'type' => 'icon_select',
				'label' => __( 'Content tree widget topic icon (open)', 'minerva-kb' ),
				'default' => 'fa-folder-open'
			),
			array(
				'id' => 'content_tree_widget_active_color',
				'type' => 'color',
				'label' => __( 'Content tree widget current article indicator color', 'minerva-kb' ),
				'default' => '#32CD32',
			),
			array(
				'id' => 'content_tree_widget_open_active_branch',
				'type' => 'checkbox',
				'label' => __( 'Open current article branch?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'widget_style_on',
				'type' => 'checkbox',
				'label' => __( 'Enable general widget styling?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'When off, theme styles will be used', 'minerva-kb' )
			),
			array(
				'id' => 'widget_bg',
				'type' => 'color',
				'label' => __( 'Widget background color', 'minerva-kb' ),
				'default' => '#f7f7f7',
				'dependency' => array(
					'target' => 'widget_style_on',
					'type' => 'EQ',
					'value' => true
				)
			),

			array(
				'id' => 'widget_color',
				'type' => 'color',
				'label' => __( 'Widget text color', 'minerva-kb' ),
				'default' => '#888',
				'dependency' => array(
					'target' => 'widget_style_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'widget_link_color',
				'type' => 'color',
				'label' => __( 'Widget link color', 'minerva-kb' ),
				'default' => '#888',
				'dependency' => array(
					'target' => 'widget_style_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'widget_icon_color',
				'type' => 'color',
				'label' => __( 'Widget icons color', 'minerva-kb' ),
				'default' => '#888',
				'dependency' => array(
					'target' => 'widget_style_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'widget_heading_color',
				'type' => 'color',
				'label' => __( 'Widget heading color', 'minerva-kb' ),
				'default' => '#333',
				'dependency' => array(
					'target' => 'widget_style_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			/**
			 * FAQ
			 */
			array(
				'id' => 'faq_tab',
				'type' => 'tab',
				'label' => __( 'FAQ (global)', 'minerva-kb' ),
				'icon' => 'fa-question-circle'
			),
			array(
				'id' => 'disable_faq',
				'type' => 'checkbox',
				'label' => __( 'Disable FAQ?', 'minerva-kb' ),
				'default' => false
			),
            array(
                'id' => 'faq_disable_block_editor',
                'type' => 'checkbox',
                'label' => __( 'Disable block editor for FAQ? (WordPress v5.0+)', 'minerva-kb' ),
                'default' => false
            ),
			// cpt
			array(
				'id' => 'faq_title',
				'type' => 'title',
				'label' => __( 'FAQ global settings', 'minerva-kb' ),
				'description' => __( 'Configure FAQ settings', 'minerva-kb' )
			),
			array(
				'id' => 'faq_enable_pages',
				'type' => 'checkbox',
				'label' => __( 'Enable standalone answer pages?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'When enabled, each FAQ Q/A will have its own page with unique URL.', 'minerva-kb' ),
			),
			array(
				'id' => 'faq_slug',
				'type' => 'input',
				'label' => __( 'FAQ items URL sluq (must be unique and not used by posts or pages)', 'minerva-kb' ),
				'default' => __( 'questions', 'minerva-kb' ),
				'description' => __( 'NOTE: these setting affects WordPress rewrite rules. After changing them you need to go to Settings - Permalinks and press Save to update rewrite rules.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'faq_enable_pages',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'faq_include_in_search',
				'type' => 'checkbox',
				'label' => __( 'Include faq answers in global search results?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'When enabled, wordpress search will include matches from FAQ. Standard posts templates will be used.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'faq_enable_pages',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'faq_enable_reorder',
				'type' => 'checkbox',
				'label' => __( 'Enable FAQ Drag n Drop reorder?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'faq_url_update',
				'type' => 'checkbox',
				'label' => __( 'Add question hash to URL on question open?', 'minerva-kb' ),
				'default' => false,
			),
			array(
				'id' => 'faq_scroll_offset',
				'type' => 'css_size',
				'label' => __( 'Scroll offset for FAQ question', 'minerva-kb' ),
				'units' => array('px'),
				'default' => array("unit" => 'px', "size" => "0"),
			),
			array(
				'id' => 'faq_slow_animation',
				'type' => 'checkbox',
				'label' => __( 'Slow FAQ open animation?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'faq_toggle_mode',
				'type' => 'checkbox',
				'label' => __( 'Toggle mode?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'In toggle mode opening one item closes others', 'minerva-kb' )
			),
			array(
				'id' => 'faq_toggle_all_title',
				'type' => 'title',
				'label' => __( 'FAQ Toggle All button', 'minerva-kb' ),
				'description' => __( 'Configure toggle all styling', 'minerva-kb' )
			),
			array(
				'id' => 'faq_toggle_all_open_text',
				'type' => 'input_text',
				'label' => __( 'FAQ Toggle All open text', 'minerva-kb' ),
				'default' => __( 'Open all', 'minerva-kb' ),
			),
			array(
				'id' => 'faq_toggle_all_close_text',
				'type' => 'input_text',
				'label' => __( 'FAQ Toggle All close text', 'minerva-kb' ),
				'default' => __( 'Close all', 'minerva-kb' ),
			),
			array(
				'id' => 'show_faq_toggle_all_icon',
				'type' => 'checkbox',
				'label' => __( 'Show FAQ toggle all icon?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'faq_toggle_all_icon',
				'type' => 'icon_select',
				'label' => __( 'FAQ toggle all icon (open)', 'minerva-kb' ),
				'default' => 'fa-plus-circle',
				'dependency' => array(
					'target' => 'show_faq_toggle_all_icon',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'faq_toggle_all_icon_open',
				'type' => 'icon_select',
				'label' => __( 'FAQ toggle all icon (close)', 'minerva-kb' ),
				'default' => 'fa-minus-circle',
				'dependency' => array(
					'target' => 'show_faq_toggle_all_icon',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'faq_toggle_all_bg',
				'type' => 'color',
				'label' => __( 'FAQ toggle all background color', 'minerva-kb' ),
				'default' => '#4bb7e5'
			),
			array(
				'id' => 'faq_toggle_all_bg_hover',
				'type' => 'color',
				'label' => __( 'FAQ toggle all background color on mouse hover', 'minerva-kb' ),
				'default' => '#64bee5'
			),
			array(
				'id' => 'faq_toggle_all_color',
				'type' => 'color',
				'label' => __( 'FAQ toggle all link color', 'minerva-kb' ),
				'default' => '#ffffff'
			),
			array(
				'id' => 'faq_questions_title',
				'type' => 'title',
				'label' => __( 'FAQ Questions style', 'minerva-kb' ),
				'description' => __( 'Configure questions styling', 'minerva-kb' )
			),
			array(
				'id' => 'show_faq_question_icon',
				'type' => 'checkbox',
				'label' => __( 'Show FAQ question icon?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'faq_question_icon',
				'type' => 'icon_select',
				'label' => __( 'FAQ question icon', 'minerva-kb' ),
				'default' => 'fa-plus-circle'
			),
			array(
				'id' => 'faq_question_icon_open_action',
				'type' => 'select',
				'label' => __( 'FAQ question icon action on open', 'minerva-kb' ),
				'options' => array(
					'rotate' => __( 'Rotate', 'minerva-kb' ),
					'change' => __( 'Change', 'minerva-kb' )
				),
				'default' => 'change'
			),
			array(
				'id' => 'faq_question_open_icon',
				'type' => 'icon_select',
				'label' => __( 'FAQ question open icon', 'minerva-kb' ),
				'default' => 'fa-minus-circle',
				'dependency' => array(
					'target' => 'faq_question_icon_open_action',
					'type' => 'EQ',
					'value' => 'change'
				)
			),
			array(
				'id' => 'faq_question_bg',
				'type' => 'color',
				'label' => __( 'FAQ question background color', 'minerva-kb' ),
				'default' => '#4bb7e5'
			),
			array(
				'id' => 'faq_question_bg_hover',
				'type' => 'color',
				'label' => __( 'FAQ question background color on mouse hover', 'minerva-kb' ),
				'default' => '#64bee5'
			),
			array(
				'id' => 'faq_question_font_size',
				'type' => 'css_size',
				'label' => __( 'Question font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1.5"),
			),
			array(
				'id' => 'faq_question_color',
				'type' => 'color',
				'label' => __( 'FAQ question text color', 'minerva-kb' ),
				'default' => '#ffffff'
			),
			array(
				'id' => 'faq_question_shadow',
				'type' => 'checkbox',
				'label' => __( 'Add FAQ question shadow?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'faq_answers_title',
				'type' => 'title',
				'label' => __( 'FAQ Answers style', 'minerva-kb' ),
				'description' => __( 'Configure answers styling', 'minerva-kb' )
			),
			array(
				'id' => 'faq_answer_bg',
				'type' => 'color',
				'label' => __( 'FAQ answer background color', 'minerva-kb' ),
				'default' => '#ffffff'
			),
			array(
				'id' => 'faq_answer_color',
				'type' => 'color',
				'label' => __( 'FAQ answer text color', 'minerva-kb' ),
				'default' => '#333'
			),
			array(
				'id' => 'faq_categories_title',
				'type' => 'title',
				'label' => __( 'FAQ Categories style', 'minerva-kb' ),
				'description' => __( 'Configure categories styling', 'minerva-kb' )
			),
			array(
				'id' => 'faq_category_margin_top',
				'type' => 'css_size',
				'label' => __( 'Category name top margin', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1"),
				'description' => __( 'Distance between category title and previous section', 'minerva-kb' ),
			),
			array(
				'id' => 'faq_category_margin_bottom',
				'type' => 'css_size',
				'label' => __( 'Category name bottom margin', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0.3"),
				'description' => __( 'Distance between category title and questions', 'minerva-kb' ),
			),
			array(
				'id' => 'faq_count_bg',
				'type' => 'color',
				'label' => __( 'FAQ category count background color', 'minerva-kb' ),
				'default' => '#4bb7e5',
			),
			array(
				'id' => 'faq_count_color',
				'type' => 'color',
				'label' => __( 'FAQ category count text color', 'minerva-kb' ),
				'default' => '#ffffff',
			),
			array(
				'id' => 'faq_filter_title',
				'type' => 'title',
				'label' => __( 'FAQ Live Filter style', 'minerva-kb' ),
				'description' => __( 'Configure filter styling', 'minerva-kb' )
			),
			array(
				'id' => 'faq_filter_theme',
				'type' => 'select',
				'label' => __( 'FAQ filter theme', 'minerva-kb' ),
				'options' => array(
					'minerva' => __( 'Minerva', 'minerva-kb' ),
					'invisible' => __( 'Invisible', 'minerva-kb' )
				),
				'default' => 'minerva'
			),
			array(
				'id' => 'faq_filter_placeholder',
				'type' => 'input_text',
				'label' => __( 'FAQ filter placeholder', 'minerva-kb' ),
				'default' => __( 'FAQ filter', 'minerva-kb' ),
			),
			array(
				'id' => 'show_faq_filter_icon',
				'type' => 'checkbox',
				'label' => __( 'Show FAQ filter icon?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'faq_filter_icon',
				'type' => 'icon_select',
				'label' => __( 'FAQ filter icon', 'minerva-kb' ),
				'default' => 'fa-filter',
			),
			array(
				'id' => 'faq_filter_clear_icon',
				'type' => 'icon_select',
				'label' => __( 'FAQ filter clear icon', 'minerva-kb' ),
				'default' => 'fa-times-circle',
			),
			array(
				'id' => 'faq_no_results_text',
				'type' => 'input_text',
				'label' => __( 'FAQ filter no results text', 'minerva-kb' ),
				'default' => __( 'No questions matching current filter', 'minerva-kb' ),
			),
			array(
				'id' => 'faq_no_results_bg',
				'type' => 'color',
				'label' => __( 'FAQ no results background color', 'minerva-kb' ),
				'default' => '#f7f7f7'
			),
			array(
				'id' => 'faq_no_results_color',
				'type' => 'color',
				'label' => __( 'FAQ no results text color', 'minerva-kb' ),
				'default' => '#333'
			),
			array(
				'id' => 'faq_filter_open_single',
				'type' => 'checkbox',
				'label' => __( 'Open question when single item matches filter?', 'minerva-kb' ),
				'default' => false,
			),
			/**
			 * Post type
			 */
			array(
				'id' => 'cpt_tab',
				'type' => 'tab',
				'label' => __( 'Post type & URLs', 'minerva-kb' ),
				'icon' => 'fa-address-card-o'
			),
			array(
				'id' => 'article_cpt_section_info',
				'type' => 'info',
				'label' => 'Note: this section modifies WordPress rewrite rules, which are usually cached. ' .
				               'If you experience any 404 errors after editing these settings, go to ' .
				               '<a href="' . esc_attr(admin_url('options-permalink.php')) . '">' .
				               'Settings - Permalinks' . '</a>' . ' and press Save ' .
				               'without editing to clear rewrite rules cache.',
			),
			// cpt
			array(
				'id' => 'article_cpt_title',
				'type' => 'title',
				'label' => __( 'Article URL', 'minerva-kb' ),
				'description' => __( 'Configure article post type URL', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_archive_disable_switch',
				'type' => 'checkbox',
				'label' => __( 'Disable article archive?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'By default, articles archive takes same URL as article URL base (for example, /kb), so disabling archive will allow you to use this slug for your KB home page', 'minerva-kb' ),
			),
			array(
				'id' => 'cpt_slug_switch',
				'type' => 'checkbox',
				'label' => __( 'Edit article slug (URL part)?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'article_slug',
				'type' => 'input',
				'label' => __( 'Article slug (URL part)', 'minerva-kb' ),
				'default' => 'kb',
				'description' => __( 'Use only lowercase letters, underscores and dashes. Slug must be a valid URL part', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'cpt_slug_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'cpt_slug_front_switch',
				'type' => 'checkbox',
				'label' => __( 'Add global front base to article url?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'If you have configured global front base, like /blog, you can remove it for KB items with this switch', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'cpt_slug_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			// topics
			array(
				'id' => 'article_cpt_category_title',
				'type' => 'title',
				'label' => __( 'Topic URL', 'minerva-kb' ),
				'description' => __( 'Configure topic taxonomy URL slug', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_category_slug_switch',
				'type' => 'checkbox',
				'label' => __( 'Edit topic slug (URL part)?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'category_slug',
				'type' => 'input',
				'label' => __( 'Topic slug (URL part)', 'minerva-kb' ),
				'default' => 'kbtopic',
				'description' => __( 'Use only lowercase letters, underscores and dashes. Slug must be a valid URL part', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'cpt_category_slug_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'cpt_category_slug_front_switch',
				'type' => 'checkbox',
				'label' => __( 'Add global front base to topic url?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'If you have configured global front base, like /blog, you can remove it for KB items with this switch', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'cpt_category_slug_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			// tags
			array(
				'id' => 'article_cpt_tag_title',
				'type' => 'title',
				'label' => __( 'Tag URL', 'minerva-kb' ),
				'description' => __( 'Configure tag taxonomy URL slug', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_tag_slug_switch',
				'type' => 'checkbox',
				'label' => __( 'Edit tag slug (URL part)', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'tag_slug',
				'type' => 'input',
				'label' => __( 'Tag slug (URL part)', 'minerva-kb' ),
				'default' => 'kbtag',
				'description' => __( 'Use only lowercase letters, underscores and dashes. Slug must be a valid URL part', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'cpt_tag_slug_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'cpt_tag_slug_front_switch',
				'type' => 'checkbox',
				'label' => __( 'Add global front base to tag url?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'If you have configured global front base, like /blog, you can remove it for KB items with this switch', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'cpt_tag_slug_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			// CPT advanced
			array(
				'id' => 'article_cpt_names_title',
				'type' => 'title',
				'label' => __( 'Post type and taxonomy advanced settings', 'minerva-kb' ),
				'description' => __( 'These setting are available to resolve conflicts with other plugins', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_advanced_switch',
				'type' => 'checkbox',
				'label' => __( 'Edit post type settings?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'article_cpt_warning',
				'type' => 'warning',
				'label' => __( 'Following settings are available for compatibility with other plugins and change the actual post type and taxonomy. ' .
				               'If you change them, already added KB content will be hidden until you change it back. ' .
				               'If you need to change URL part, please use the slug settings above instead.', 'minerva-kb' ),
			),
			array(
				'id' => 'article_cpt',
				'type' => 'input',
				'label' => __( 'Article post type', 'minerva-kb' ),
				'default' => 'kb',
				'description' => __( 'Use only lowercase letters. Note, that if you have already added articles changing this setting will make them invisible.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'cpt_advanced_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_cpt_category',
				'type' => 'input',
				'label' => __( 'Article topic taxonomy', 'minerva-kb' ),
				'default' => 'kbtopic',
				'description' => __( 'Use only lowercase letters. Do not use "category", as it is reserved for standard posts. Note, that if you have already added topics changing this setting will make them invisible.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'cpt_advanced_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_cpt_tag',
				'type' => 'input',
				'label' => __( 'Article tag taxonomy', 'minerva-kb' ),
				'default' => 'kbtag',
				'description' => __( 'Use only lowercase letters. Do not use "tag", as it is reserved for standard posts. Note, that if you have already added tags changing this setting will make them invisible.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'cpt_advanced_switch',
					'type' => 'EQ',
					'value' => true
				)
			),

			/**
			 * Search global
			 */
			array(
				'id' => 'search_global_tab',
				'type' => 'tab',
				'label' => __( 'Search (global)', 'minerva-kb' ),
				'icon' => 'fa-search'
			),
			// search global title
			array(
				'id' => 'search_global_title',
				'type' => 'title',
				'label' => __( 'Global search settings', 'minerva-kb' ),
				'description' => __( 'Configure search results page and other search options here', 'minerva-kb' )
			),
			array(
				'id' => 'search_mode',
				'type' => 'select',
				'label' => __( 'Which search mode to use?', 'minerva-kb' ),
				'options' => array(
					'blocking' => __( 'Blocking', 'minerva-kb' ),
					'nonblocking' => __( 'Non-blocking (default)', 'minerva-kb' )
				),
				'default' => 'nonblocking',
				'description' => __( 'Blocking mode does not send any requests to server until user finishes typing, can be useful for reducing load on server.', 'minerva-kb' ),
			),
			array(
				'id' => 'search_request_fe_cache',
				'type' => 'checkbox',
				'label' => __( 'Enable search requests caching on client side?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'When enabled, already received search results won\'t be send again to the server until user refreshes the page', 'minerva-kb' ),
			),
			array(
				'id' => 'search_request_icon',
				'type' => 'icon_select',
				'label' => __( 'Search request icon', 'minerva-kb' ),
				'default' => 'fa-circle-o-notch',
			),
			array(
				'id' => 'search_request_icon_color',
				'type' => 'color',
				'label' => __( 'Search request icon color', 'minerva-kb' ),
				'default' => '#2ab77b'
			),
			array(
				'id' => 'search_include_tag_matches',
				'type' => 'checkbox',
				'label' => __( 'Include tag matches in search results?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Only exact matches are added, for ex. search for install will match articles with tag install, not installation', 'minerva-kb' ),
			),
			array(
				'id' => 'search_delay',
				'type' => 'input',
				'label' => __( 'Live Search delay/throttle (ms)', 'minerva-kb' ),
				'default' => 1000,
				'description' => __( 'Delay before search after the moment user stops typing query, in milliseconds. For non-blocking mode - minimum interval between requests', 'minerva-kb' )
			),
			array(
				'id' => 'search_product_prefix',
				'type' => 'input_text',
				'label' => __( 'Text prefix when showing results for product in multi-product mode', 'minerva-kb' ),
				'default' => __('Showing results for', 'minerva-kb'),
				'description' => __( 'This will be displayed before search results together with current product name', 'minerva-kb' )
			),
			array(
				'id' => 'search_needle_length',
				'type' => 'input',
				'label' => __( 'Number of characters to trigger search', 'minerva-kb' ),
				'default' => 3,
				'description' => __( 'Search will not run until user types at least this amount of characters', 'minerva-kb' )
			),
			array(
				'id' => 'live_search_show_excerpt',
				'type' => 'checkbox',
				'label' => __( 'Show excerpt in live search results?', 'minerva-kb' ),
				'default' => false,
			),
			array(
				'id' => 'live_search_excerpt_length',
				'type' => 'input',
				'label' => __( 'Live search results excerpt length (in characters)', 'minerva-kb' ),
				'default' => 140
			),
			array(
				'id' => 'live_search_disable_mobile',
				'type' => 'checkbox',
				'label' => __( 'Disable live search on mobile?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'When disabled, search page will be shown instead', 'minerva-kb' ),
			),
			array(
				'id' => 'live_search_disable_tablet',
				'type' => 'checkbox',
				'label' => __( 'Disable live search on tablet?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'When disabled, search page will be shown instead', 'minerva-kb' ),
			),
			array(
				'id' => 'live_search_disable_desktop',
				'type' => 'checkbox',
				'label' => __( 'Disable live search on desktop?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'When disabled, search page will be shown instead', 'minerva-kb' ),
			),
			array(
				'id' => 'live_search_use_post',
				'type' => 'checkbox',
				'label' => __( 'Use POST http method for search requests?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Can be useful if you have conflicts with caching plugins', 'minerva-kb' ),
			),
			/**
			 * Search results page
			 */
			array(
				'id' => 'search_results_title',
				'type' => 'title',
				'label' => __( 'Search results page settings', 'minerva-kb' ),
				'description' => __( 'Configure appearance and display mode of search results page', 'minerva-kb' )
			),
			array(
				'id' => 'search_results_top_padding',
				'type' => 'css_size',
				'label' => __( 'Search results page top padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => __( 'Distance between header and search results page content', 'minerva-kb' )
			),
			array(
				'id' => 'search_results_bottom_padding',
				'type' => 'css_size',
				'label' => __( 'Search results  page bottom padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => __( 'Distance between search results page content and footer', 'minerva-kb' )
			),
			array(
				'id' => 'search_sidebar',
				'type' => 'image_select',
				'label' => __( 'Search results page sidebar position', 'minerva-kb' ),
				'options' => array(
					'none' => array(
						'label' => __( 'None', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'no-sidebar.png'
					),
					'left' => array(
						'label' => __( 'Left', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'left-sidebar.png'
					),
					'right' => array(
						'label' => __( 'Right', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'right-sidebar.png'
					),
				),
				'default' => 'right',
				'description' => __( 'You can add widgets to sidebars under Appearance - Widgets', 'minerva-kb' )
			),
			array(
				'id' => 'search_results_per_page',
				'type' => 'input',
				'label' => __( 'Number of search results per page. Use -1 to show all', 'minerva-kb' ),
				'default' => __( '10', 'minerva-kb' )
			),
			array(
				'id' => 'show_search_page_search',
				'type' => 'checkbox',
				'label' => __( 'Show search box on results page?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Search settings from topic search will be used', 'minerva-kb' ),
			),
			array(
				'id' => 'show_breadcrumbs_search',
				'type' => 'checkbox',
				'label' => __( 'Show breadcrumbs on search results page?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Enable/disable breadcrumbs for search results page', 'minerva-kb' ),
			),
			array(
				'id' => 'search_results_breadcrumbs_label',
				'type' => 'input_text',
				'label' => __( 'Search breadcrumbs label', 'minerva-kb' ),
				'default' => __( 'Search results for %s', 'minerva-kb' ),
				'description' => __( '%s will be replaced with search term', 'minerva-kb' ),
			),
			array(
				'id' => 'search_results_page_title',
				'type' => 'input_text',
				'label' => __( 'Search page title', 'minerva-kb' ),
				'default' => __( 'Found %s results for: %s', 'minerva-kb' ),
				'description' => __( '%s will be replaced with number of results and search term', 'minerva-kb' ),
			),
			array(
				'id' => 'search_results_layout',
				'type' => 'select',
				'label' => __( 'Which search results page layout to use?', 'minerva-kb' ),
				'options' => array(
					'simple' => __( 'Simple', 'minerva-kb' ),
					'detailed' => __( 'Detailed (with excerpt)', 'minerva-kb' )
				),
				'default' => 'detailed'
			),
			array(
				'id' => 'search_results_detailed_title',
				'type' => 'title',
				'label' => __( 'Search results detailed layout settings', 'minerva-kb' ),
				'description' => __( 'Configure settings of detailed mode for search results', 'minerva-kb' )
			),

			array(
				'id' => 'search_results_match_color',
				'type' => 'color',
				'label' => __( 'Search match in excerpt color', 'minerva-kb' ),
				'default' => '#000'
			),
			array(
				'id' => 'search_results_match_bg',
				'type' => 'color',
				'label' => __( 'Search match in excerpt background color', 'minerva-kb' ),
				'default' => 'rgba(255,255,255,0)'
			),
			array(
				'id' => 'show_search_page_topic',
				'type' => 'checkbox',
				'label' => __( 'Show article topic on results page?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'search_results_settings_info',
				'type' => 'info',
				'label' => 'Please note, that more Detailed view settings can be found in "Topics" section of settings.',
			),
			array(
				'id' => 'search_no_results_title',
				'type' => 'input_text',
				'label' => __( 'Search no results page title', 'minerva-kb' ),
				'default' => __( 'Nothing Found', 'minerva-kb' )
			),
			array(
				'id' => 'search_no_results_subtitle',
				'type' => 'input_text',
				'label' => __( 'Search no results page subtitle', 'minerva-kb' ),
				'default' => __( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'minerva-kb' )
			),

			/**
			 * Article
			 */
			array(
				'id' => 'single_tab',
				'type' => 'tab',
				'label' => __( 'Article', 'minerva-kb' ),
				'icon' => 'fa-file-text-o'
			),
			array(
				'id' => 'single_template',
				'type' => 'select',
				'label' => __( 'Which template to use?', 'minerva-kb' ),
				'options' => array(
					'theme' => __( 'Theme single template', 'minerva-kb' ),
					'plugin' => __( 'Plugin article template', 'minerva-kb' )
				),
				'default' => 'plugin',
				'experimental' => __( 'This is experimental feature and depends a lot on theme styles and layout', 'minerva-kb' ),
				'description' => __( 'Note, that you can override plugin templates in your theme. See documentation for details', 'minerva-kb' )
			),
            array(
                'id' => 'article_disable_block_editor',
                'type' => 'checkbox',
                'label' => __( 'Disable block editor for Articles? (WordPress v5.0+)', 'minerva-kb' ),
                'default' => false
            ),
			array(
				'id' => 'single_top_padding',
				'type' => 'css_size',
				'label' => __( 'Article page top padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "3"),
				'description' => __( 'Distance between header and article content', 'minerva-kb' )
			),
			array(
				'id' => 'single_bottom_padding',
				'type' => 'css_size',
				'label' => __( 'Article page bottom padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "3"),
				'description' => __( 'Distance between article content and footer', 'minerva-kb' )
			),
			array(
				'id' => 'show_last_modified_date',
				'type' => 'checkbox',
				'label' => __( 'Show last modified date?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'show_article_title',
				'type' => 'checkbox',
				'label' => __( 'Show article title?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'You may remove article title in case theme already displays it', 'minerva-kb' )
			),
			array(
				'id' => 'last_modified_date_text',
				'type' => 'input_text',
				'label' => __( 'Last modified date label', 'minerva-kb' ),
				'default' => __( 'Last modified:', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_last_modified_date',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_article_versions',
				'type' => 'checkbox',
				'label' => __( 'Show article versions?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'article_versions_text',
				'type' => 'input_text',
				'label' => __( 'Article versions label', 'minerva-kb' ),
				'default' => __( 'For versions:', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_article_versions',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'enable_versions_links',
				'type' => 'checkbox',
				'label' => __( 'Enable links to versions archive (version archives must be enabled)?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'show_article_versions',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_reading_estimate',
				'type' => 'checkbox',
				'label' => __( 'Show estimated reading time?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'estimated_time_text',
				'type' => 'input_text',
				'label' => __( 'Estimated reading time text', 'minerva-kb' ),
				'default' => __( 'Estimated reading time:', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_reading_estimate',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'estimated_time_less_than_min',
				'type' => 'input_text',
				'label' => __( 'Estimated reading less than 1 minute text', 'minerva-kb' ),
				'default' => __( '< 1 min', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_reading_estimate',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'estimated_time_min',
				'type' => 'input_text',
				'label' => __( 'Estimated reading minute text', 'minerva-kb' ),
				'default' => __( 'min', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_reading_estimate',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'estimated_time_icon',
				'type' => 'icon_select',
				'label' => __( 'Estimated time icon', 'minerva-kb' ),
				'default' => 'fa-clock-o',
				'dependency' => array(
					'target' => 'show_reading_estimate',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_pageviews',
				'type' => 'checkbox',
				'label' => __( 'Show pageviews count?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'pageviews_label',
				'type' => 'input_text',
				'label' => __( 'Views label', 'minerva-kb' ),
				'default' => __( 'Views:', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_pageviews',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'enable_comments',
				'type' => 'checkbox',
				'label' => __( 'Enable comments?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'comments_position',
				'type' => 'select',
				'label' => __( 'Comments position', 'minerva-kb' ),
				'options' => array(
					'after_content' => __( 'After article content', 'minerva-kb' ),
					'inside_container' => __( 'Inside container', 'minerva-kb' ),
					'after_container' => __( 'After container', 'minerva-kb' )
				),
				'default' => 'after_container',
				'experimental' => __( 'This is experimental feature and depends a lot on theme styles and layout', 'minerva-kb' ),
				'description' => __( 'You can choose where to display comments box: right after article content, inside the container element or after the container element', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'enable_comments',
					'type' => 'NEQ',
					'value' => 'none'
				)
			),
			array(
				'id' => 'article_include_base_html',
				'type' => 'checkbox',
				'label' => __( 'Include base HTML styles in article?', 'minerva-kb' ),
				'description' => __( 'Compatibility option for themes that remove basic HTML styles.', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'article_no_content_filter',
				'type' => 'checkbox',
				'label' => __( 'Do not use content filter for article in Theme template', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'You may enable this option if you want to build custom layout for KB article pages via external page builder. You will need to use article content shortcode to display KB article elements', 'minerva-kb' )
			),
			array(
				'id' => 'article_sidebar',
				'type' => 'image_select',
				'label' => __( 'Article sidebar position', 'minerva-kb' ),
				'options' => array(
					'none' => array(
						'label' => __( 'None', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'no-sidebar.png'
					),
					'left' => array(
						'label' => __( 'Left', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'left-sidebar.png'
					),
					'right' => array(
						'label' => __( 'Right', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'right-sidebar.png'
					),
				),
				'default' => 'right',
				'description' => __( 'You can add widgets to sidebars under Appearance - Widgets', 'minerva-kb' )
			),
			array(
				'id' => 'article_sidebar_sticky',
				'type' => 'checkbox',
				'label' => __( 'Make article sidebar sticky?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'You can make sidebar stick to top of the window on scroll', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'article_sidebar',
					'type' => 'NEQ',
					'value' => 'none'
				)
			),
			array(
				'id' => 'article_sidebar_sticky_top',
				'type' => 'css_size',
				'label' => __( 'Sticky sidebar top position', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "3"),
				'description' => __( 'Distance between top of page and sidebar when in sticky mode', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'article_sidebar',
					'type' => 'NEQ',
					'value' => 'none'
				)
			),
			array(
				'id' => 'article_sidebar_sticky_min_width',
				'type' => 'css_size',
				'label' => __( 'Disable sticky sidebar when screen width less than', 'minerva-kb' ),
				'default' => array("unit" => 'px', "size" => "1025"),
				'units' => array('px'),
				'description' => __( 'You can set the minimum required browser width for sticky sidebar', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'article_sidebar',
					'type' => 'NEQ',
					'value' => 'none'
				)
			),
			array(
				'id' => 'article_pagination_label',
				'type' => 'input_text',
				'label' => __( 'Article pagination label', 'minerva-kb' ),
				'default' => __( 'Pages:', 'minerva-kb' )
			),
			array(
				'id' => 'article_fancybox',
				'type' => 'checkbox',
				'label' => __( 'Add fancybox to article images?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'NOTE: To enable fancybox for image, you need to set <b>Link To</b> option to <b>Media file</b> when adding media to article', 'minerva-kb' ),
			),
			array(
				'id' => 'add_article_html',
				'type' => 'checkbox',
				'label' => __( 'Add custom HTML at the bottom of each article?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'show_related_articles',
				'type' => 'checkbox',
				'label' => __( 'Show related articles?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'related_articles_label',
				'type' => 'input_text',
				'label' => __( 'Related articles title', 'minerva-kb' ),
				'default' => __( 'Related articles', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_related_articles',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_html',
				'type' => 'textarea_text',
				'label' => __( 'Article custom HTML', 'minerva-kb' ),
				'default' => '',
				'description' => __( 'This HTML will be displayed after each article content. You can use it to display additional support contacts or info.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_article_html',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_article_author',
				'type' => 'checkbox',
				'label' => __( 'Show article author?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'article_author_text',
				'type' => 'input_text',
				'label' => __( 'Article author text', 'minerva-kb' ),
				'default' => __( 'Written by:', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_article_author',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_article_tags',
				'type' => 'checkbox',
				'label' => __( 'Show article tags?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'show_article_tags_icon',
				'type' => 'checkbox',
				'label' => __( 'Show article tags icon?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'show_article_tags',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_tags_icon',
				'type' => 'icon_select',
				'label' => __( 'Article tags icon', 'minerva-kb' ),
				'default' => 'fa-tag',
				'dependency' => array(
					'target' => 'show_article_tags',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_tags_label',
				'type' => 'input_text',
				'label' => __( 'Tags label', 'minerva-kb' ),
				'default' => __( 'Tags:', 'minerva-kb' ),
				'description' => __( 'Set this field empty to remove text label', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_article_tags',
					'type' => 'EQ',
					'value' => true
				)
			),
			/**
			 * Article attachments
			 */
			array(
				'id' => 'article_attachments_tab',
				'type' => 'tab',
				'label' => __( 'Article attachments', 'minerva-kb' ),
				'icon' => 'fa-paperclip'
			),
			array(
				'id' => 'article_attachments_title',
				'type' => 'title',
				'label' => __( 'Article attachments settings', 'minerva-kb' ),
				'description' => __( 'Configure appearance and display mode of article attachments', 'minerva-kb' )
			),
			array(
				'id' => 'article_attach_label',
				'type' => 'input_text',
				'label' => __( 'Attachments label', 'minerva-kb' ),
				'default' => __( 'Attachments', 'minerva-kb' ),
				'description' => __( 'Set this field empty to remove text label', 'minerva-kb' )
			),
			array(
				'id' => 'attach_archive_file_label',
				'type' => 'select',
				'label' => __( 'File label', 'minerva-kb' ),
				'options' => array(
					'title' => __( 'Attachment title', 'minerva-kb' ),
					'filename' => __( 'Attachment filename', 'minerva-kb' ),
				),
				'default' => 'title',
				'description' => __( 'You can use filename with extension or attachment title', 'minerva-kb' )
			),
			array(
				'id' => 'show_attach_size',
				'type' => 'checkbox',
				'label' => __( 'Show attachment size?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'attach_icons_off',
				'type' => 'checkbox',
				'label' => __( 'Disable file icons?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'attach_archive_icon',
				'type' => 'icon_select',
				'label' => __( 'Archive file icon', 'minerva-kb' ),
				'default' => 'fa-file-archive-o'
			),
			array(
				'id' => 'attach_archive_color',
				'type' => 'color',
				'label' => __( 'Archive file color', 'minerva-kb' ),
				'default' => '#555759'
			),
			array(
				'id' => 'attach_pdf_icon',
				'type' => 'icon_select',
				'label' => __( 'PDF file icon', 'minerva-kb' ),
				'default' => 'fa-file-pdf-o'
			),
			array(
				'id' => 'attach_pdf_color',
				'type' => 'color',
				'label' => __( 'Pdf file color', 'minerva-kb' ),
				'default' => '#f02f13'
			),
			array(
				'id' => 'attach_text_icon',
				'type' => 'icon_select',
				'label' => __( 'Text file icon', 'minerva-kb' ),
				'default' => 'fa-file-text-o'
			),
			array(
				'id' => 'attach_text_color',
				'type' => 'color',
				'label' => __( 'Text file color', 'minerva-kb' ),
				'default' => '#555759'
			),
			array(
				'id' => 'attach_image_icon',
				'type' => 'icon_select',
				'label' => __( 'Image file icon', 'minerva-kb' ),
				'default' => 'fa-file-image-o'
			),
			array(
				'id' => 'attach_image_color',
				'type' => 'color',
				'label' => __( 'Image file color', 'minerva-kb' ),
				'default' => '#df0000'
			),
			array(
				'id' => 'attach_excel_icon',
				'type' => 'icon_select',
				'label' => __( 'Spreadsheet file icon', 'minerva-kb' ),
				'default' => 'fa-file-excel-o'
			),
			array(
				'id' => 'attach_excel_color',
				'type' => 'color',
				'label' => __( 'Spreadsheet file color', 'minerva-kb' ),
				'default' => '#24724B'
			),
			array(
				'id' => 'attach_word_icon',
				'type' => 'icon_select',
				'label' => __( 'Word file icon', 'minerva-kb' ),
				'default' => 'fa-file-word-o'
			),
			array(
				'id' => 'attach_word_color',
				'type' => 'color',
				'label' => __( 'Word file color', 'minerva-kb' ),
				'default' => '#295698'
			),
			array(
				'id' => 'attach_video_icon',
				'type' => 'icon_select',
				'label' => __( 'Video file icon', 'minerva-kb' ),
				'default' => 'fa-file-video-o'
			),
			array(
				'id' => 'attach_video_color',
				'type' => 'color',
				'label' => __( 'Video file color', 'minerva-kb' ),
				'default' => '#19b7ea'
			),
			array(
				'id' => 'attach_audio_icon',
				'type' => 'icon_select',
				'label' => __( 'Audio file icon', 'minerva-kb' ),
				'default' => 'fa-file-audio-o'
			),
			array(
				'id' => 'attach_audio_color',
				'type' => 'color',
				'label' => __( 'Audio file color', 'minerva-kb' ),
				'default' => '#faa703'
			),
			array(
				'id' => 'attach_default_icon',
				'type' => 'icon_select',
				'label' => __( 'Default file icon', 'minerva-kb' ),
				'default' => 'fa-file-o'
			),
			array(
				'id' => 'attach_default_color',
				'type' => 'color',
				'label' => __( 'Default file color', 'minerva-kb' ),
				'default' => '#555759'
			),

			/**
			 * Article versions
			 */
			array(
				'id' => 'article_versions_tab',
				'type' => 'tab',
				'label' => __( 'Article versions', 'minerva-kb' ),
				'icon' => 'fa-flag'
			),
			array(
				'id' => 'article_versions_title',
				'type' => 'title',
				'label' => __( 'Article versions', 'minerva-kb' ),
				'description' => __( 'You can use versions if your main product is software and you need to indicate for which software versions article is written', 'minerva-kb' )
			),
			array(
				'id' => 'add_article_versions',
				'type' => 'checkbox',
				'label' => __( 'Enable versions tag for articles? (you will need to refresh the page after changing this)', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'enable_versions_archive',
				'type' => 'checkbox',
				'label' => __( 'Enable versions archive (displays all articles for given version)?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'add_article_versions',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'versions_slug',
				'type' => 'input',
				'label' => __( 'Versions URL sluq (must be unique and not used by posts or pages)', 'minerva-kb' ),
				'default' => __( 'kbversion', 'minerva-kb' ),
				'description' => __( 'NOTE: this setting affects WordPress rewrite rules. After changing it you need to go to Settings - Permalinks and press Save to update rewrite rules.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_article_versions',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'version_label_bg',
				'type' => 'color',
				'label' => __( 'Version label background color', 'minerva-kb' ),
				'default' => '#00a0d2',
				'dependency' => array(
					'target' => 'add_article_versions',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'version_label_text_color',
				'type' => 'color',
				'label' => __( 'Version label text color', 'minerva-kb' ),
				'default' => '#fff',
				'dependency' => array(
					'target' => 'add_article_versions',
					'type' => 'EQ',
					'value' => true
				)
			),

			/**
			 * Article search
			 */
			array(
				'id' => 'article_search_tab',
				'type' => 'tab',
				'label' => __( 'Article search', 'minerva-kb' ),
				'icon' => 'fa-search'
			),
			array(
				'id' => 'add_article_search',
				'type' => 'checkbox',
				'label' => __( 'Add search in articles?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'article_search_title',
				'type' => 'input_text',
				'label' => __( 'Article search title', 'minerva-kb' ),
				'default' => __( '', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_title_color',
				'type' => 'color',
				'label' => __( 'Search title color', 'minerva-kb' ),
				'default' => '#333333',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_title_size',
				'type' => 'input',
				'label' => __( 'Search title font size', 'minerva-kb' ),
				'default' => __( '1.2em', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 3em or 20px',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_theme',
				'type' => 'select',
				'label' => __( 'Which search input theme to use?', 'minerva-kb' ),
				'options' => array(
					'minerva' => __( 'Minerva', 'minerva-kb' ),
					'clean' => __( 'Clean', 'minerva-kb' ),
					'mini' => __( 'Mini', 'minerva-kb' ),
					'bold' => __( 'Bold', 'minerva-kb' ),
					'invisible' => __( 'Invisible', 'minerva-kb' ),
					'thick' => __( 'Thick', 'minerva-kb' ),
					'3d' => __( '3d', 'minerva-kb' ),
				),
				'default' => 'mini',
				'description' => __( 'Use predefined styles for search bar', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_border_color',
				'type' => 'color',
				'label' => __( 'Search wrap border color (not in all themes)', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_min_width',
				'type' => 'input',
				'label' => __( 'Search input minimum width', 'minerva-kb' ),
				'default' => __( '100%', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 40em or 300px. em are better for mobile devices',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_container_padding_top',
				'type' => 'input',
				'label' => __( 'Search container top padding', 'minerva-kb' ),
				'default' => __( '0', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 3em or 50px',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_container_padding_bottom',
				'type' => 'input',
				'label' => __( 'Search container bottom padding', 'minerva-kb' ),
				'default' => __( '0', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 3em or 50px',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_placeholder',
				'type' => 'input_text',
				'label' => __( 'Article search placeholder', 'minerva-kb' ),
				'default' => __( 'ex.: Installation', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_disable_autofocus',
				'type' => 'checkbox',
				'label' => __( 'Disable search field autofocus?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_show_search_tip',
				'type' => 'checkbox',
				'label' => __( 'Show search tip?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_tip',
				'type' => 'input_text',
				'label' => __( 'Article search tip (under the input)', 'minerva-kb' ),
				'default' => __( 'Tip: Use arrows to navigate results, ESC to focus search input', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_tip_color',
				'type' => 'color',
				'label' => __( 'Search tip color', 'minerva-kb' ),
				'default' => '#cccccc',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_container_bg',
				'type' => 'color',
				'label' => __( 'Search container background color', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_container_image_bg',
				'type' => 'media',
				'label' => __( 'Search container background image URL (optional)', 'minerva-kb' ),
				'default' => '',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_add_gradient_overlay',
				'type' => 'checkbox',
				'label' => __( 'Add gradient overlay?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_container_gradient_from',
				'type' => 'color',
				'label' => __( 'Search container gradient from', 'minerva-kb' ),
				'default' => '#00c1b6',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_container_gradient_to',
				'type' => 'color',
				'label' => __( 'Search container gradient to', 'minerva-kb' ),
				'default' => '#136eb5',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_container_gradient_opacity',
				'type' => 'input',
				'label' => __( 'Search container background gradient opacity', 'minerva-kb' ),
				'default' => 1,
				'description' => __( 'Use any CSS opacity value, for example 1 or 0.7', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_add_pattern_overlay',
				'type' => 'checkbox',
				'label' => __( 'Add pattern overlay?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_container_image_pattern',
				'type' => 'media',
				'label' => __( 'Search container background pattern image URL (optional)', 'minerva-kb' ),
				'default' => '',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_container_image_pattern_opacity',
				'type' => 'input',
				'label' => __( 'Search container background pattern opacity', 'minerva-kb' ),
				'default' => 1,
				'description' => __( 'Use any CSS opacity value, for example 1 or 0.7. You can also use transparent .png and set opacity to 1', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_show_topic_in_results',
				'type' => 'checkbox',
				'label' => __( 'Show topic in results?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			/**
			 * Guest posting
			 */
			array(
				'id' => 'submission_tab',
				'type' => 'tab',
				'label' => __( 'Guest posting', 'minerva-kb' ),
				'icon' => 'fa-paper-plane-o'
			),
			array(
				'id' => 'submit_settings_title',
				'type' => 'title',
				'label' => __( 'Guest posting settings', 'minerva-kb' ),
				'description' => __( 'You can allow users or guests to submit KB content without giving them access to Dashboard. To do so you need to insert Submission form shortcode on any page. Submitted articles will be saved as new Drafts. NOTE, you can insert only one form per page.', 'minerva-kb' )
			),
			array(
				'id' => 'submit_usage',
				'type' => 'code',
				'label' => __( 'Submit form shortcode example', 'minerva-kb' ),
				'default' => '[mkb-guestpost]'
			),
			array(
				'id' => 'submit_disable',
				'type' => 'checkbox',
				'label' => __( 'Disable submission forms?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'submit_disable_message',
				'type' => 'input_text',
				'label' => __( 'Submit disabled message (optional)', 'minerva-kb' ),
				'default' => __( 'Content submission is currently disabled.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'submit_disable',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'antispam_quiz_enable',
				'type' => 'checkbox',
				'label' => __( 'Enable anti-spam question?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'antispam_quiz_question',
				'type' => 'input_text',
				'label' => __( 'Anti-spam question', 'minerva-kb' ),
				'default' => __( '3 + 5 = ?', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'antispam_quiz_enable',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'antispam_quiz_answer',
				'type' => 'input_text',
				'label' => __( 'Anti-spam answer', 'minerva-kb' ),
				'default' => __( '8', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'antispam_quiz_enable',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'antispam_failed_message',
				'type' => 'input_text',
				'label' => __( 'Anti-spam answer error message', 'minerva-kb' ),
				'default' => __( 'Wrong security question answer, try again.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'antispam_quiz_enable',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'submit_restrict_enable',
				'type' => 'checkbox',
				'label' => __( 'Enable submission restriction by user role?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'submit_restrict_role',
				'type' => 'roles_select',
				'label' => __( 'Who can submit articles?', 'minerva-kb' ),
				'default' => 'none',
				'flush' => false,
				'view_log' => false,
				'description' => __( 'Select roles, that have access to articles submission on client side. By default, anyone can submit', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'submit_restrict_enable',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'submit_restriction_failed_message',
				'type' => 'input_text',
				'label' => __( 'Submit restriction failed message (optional)', 'minerva-kb' ),
				'default' => __( 'You are not allowed to submit content, please register or sign in.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'submit_restrict_enable',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'submit_form_heading_label',
				'type' => 'input_text',
				'label' => __( 'Submit form heading label', 'minerva-kb' ),
				'default' => __( 'Submit your article', 'minerva-kb' )
			),
			array(
				'id' => 'submit_form_subheading_label',
				'type' => 'input_text',
				'label' => __( 'Submit form subheading label', 'minerva-kb' ),
				'default' => __( 'Article will be submitted and published after review.', 'minerva-kb' )
			),
			array(
				'id' => 'submit_article_title_label',
				'type' => 'input_text',
				'label' => __( 'Submit article title label', 'minerva-kb' ),
				'default' => __( 'Article title:', 'minerva-kb' )
			),
			array(
				'id' => 'submit_unique_titles',
				'type' => 'checkbox',
				'label' => __( 'Require unique titles?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'submit_unique_titles_error_message',
				'type' => 'input_text',
				'label' => __( 'Non-unique title error message', 'minerva-kb' ),
				'default' => __( 'Article title already exists, please select unique one', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'submit_unique_titles',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'submit_content_label',
				'type' => 'input_text',
				'label' => __( 'Submit content label', 'minerva-kb' ),
				'default' => __( 'Article content:', 'minerva-kb' )
			),
			array(
				'id' => 'submit_content_editor_skin',
				'type' => 'select',
				'label' => __( 'Content editor style', 'minerva-kb' ),
				'options' => array(
					'snow' => __( 'Fixed toolbar', 'minerva-kb' ),
					'bubble' => __( 'Floating toolbar', 'minerva-kb' )
				),
				'default' => 'snow'
			),
			array(
				'id' => 'submit_content_default_text',
				'type' => 'input_text',
				'label' => __( 'Submit content initial value', 'minerva-kb' ),
				'default' => __( 'Start writing your article here...', 'minerva-kb' )
			),
			array(
				'id' => 'submit_allow_topics_select',
				'type' => 'checkbox',
				'label' => __( 'Allow users to select topics?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'submit_topic_select_label',
				'type' => 'input_text',
				'label' => __( 'Submit topic select label', 'minerva-kb' ),
				'default' => __( 'Select topic:', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'submit_allow_topics_select',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'submit_send_button_label',
				'type' => 'input_text',
				'label' => __( 'Submit button label', 'minerva-kb' ),
				'default' => __( 'Submit article', 'minerva-kb' )
			),
			array(
				'id' => 'submit_send_button_bg',
				'type' => 'color',
				'label' => __( 'Submit button background color', 'minerva-kb' ),
				'default' => '#4a90e2',
			),
			array(
				'id' => 'submit_send_button_color',
				'type' => 'color',
				'label' => __( 'Submit button text color', 'minerva-kb' ),
				'default' => '#ffffff',
			),
			array(
				'id' => 'submit_success_message',
				'type' => 'input_text',
				'label' => __( 'Submit success message', 'minerva-kb' ),
				'default' => __( 'Your content has been submitted, thank you!', 'minerva-kb' )
			),

			/**
			 * Topics
			 */
			array(
				'id' => 'topic_tab',
				'type' => 'tab',
				'label' => __( 'Topics', 'minerva-kb' ),
				'icon' => 'fa-address-book-o'
			),
			array(
				'id' => 'topic_template',
				'type' => 'select',
				'label' => __( 'Which topic template to use?', 'minerva-kb' ),
				'options' => array(
					'theme' => __( 'Theme archive template', 'minerva-kb' ),
					'plugin' => __( 'Plugin topic template', 'minerva-kb' )
				),
				'default' => 'plugin',
				'experimental' => __( 'This is experimental feature and depends a lot on theme styles and layout', 'minerva-kb' ),
				'description' => __( 'Note, that you can override plugin templates in your theme. See documentation for details', 'minerva-kb' )
			),
			array(
				'id' => 'topic_template_view',
				'type' => 'select',
				'label' => __( 'Which topic layout to use?', 'minerva-kb' ),
				'options' => array(
					'simple' => __( 'Simple (default)', 'minerva-kb' ),
					'detailed' => __( 'Detailed', 'minerva-kb' )
				),
				'default' => 'simple',
				'description' => __( 'Detailed view provides extra information about articles', 'minerva-kb' )
			),
			array(
				'id' => 'show_topic_title',
				'type' => 'checkbox',
				'label' => __( 'Show topic title?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'You can remove topic title if theme already shows it', 'minerva-kb' )
			),
			array(
				'id' => 'show_topic_description',
				'type' => 'checkbox',
				'label' => __( 'Show topic description?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'You can remove topic description if theme already shows it', 'minerva-kb' )
			),
			array(
				'id' => 'search_results_excerpt_length',
				'type' => 'input',
				'label' => __( 'Detailed view excerpt length (characters)', 'minerva-kb' ),
				'default' => __( '300', 'minerva-kb' )
			),
			array(
				'id' => 'show_search_page_views',
				'type' => 'checkbox',
				'label' => __( 'Detailed view: Show article views count?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Views will be displayed only when > 0', 'minerva-kb' )
			),
			array(
				'id' => 'show_search_page_likes',
				'type' => 'checkbox',
				'label' => __( 'Detailed view: Show article likes count?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Likes will be displayed only when > 0', 'minerva-kb' )
			),
			array(
				'id' => 'show_search_page_dislikes',
				'type' => 'checkbox',
				'label' => __( 'Detailed view: Show article dislikes count?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Dislikes will be displayed only when > 0', 'minerva-kb' )
			),
			array(
				'id' => 'show_search_page_last_edit',
				'type' => 'checkbox',
				'label' => __( 'Detailed view: Show article last modified date?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'topic_articles_per_page',
				'type' => 'input',
				'label' => __( 'Number of articles per page. Use -1 to show all', 'minerva-kb' ),
				'default' => __( '10', 'minerva-kb' )
			),
			array(
				'id' => 'topic_top_padding',
				'type' => 'css_size',
				'label' => __( 'Topic page top padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "3"),
				'description' => __( 'Distance between header and topic page content', 'minerva-kb' )
			),
			array(
				'id' => 'topic_bottom_padding',
				'type' => 'css_size',
				'label' => __( 'Topic page bottom padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "3"),
				'description' => __( 'Distance between topic page content and footer', 'minerva-kb' )
			),
			array(
				'id' => 'topic_list_layout',
				'type' => 'image_select',
				'label' => __( 'Topic list layout', 'minerva-kb' ),
				'options' => array(
					'1col' => array(
						'label' => __( '1 column', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-1.png'
					),
					'2col' => array(
						'label' => __( '2 columns', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-2.png'
					),
					'3col' => array(
						'label' => __( '3 columns', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-3.png'
					)
				),
				'default' => '1col'
			),
			array(
				'id' => 'topic_sidebar',
				'type' => 'image_select',
				'label' => __( 'Topic sidebar position', 'minerva-kb' ),
				'options' => array(
					'none' => array(
						'label' => __( 'None', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'no-sidebar.png'
					),
					'left' => array(
						'label' => __( 'Left', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'left-sidebar.png'
					),
					'right' => array(
						'label' => __( 'Right', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'right-sidebar.png'
					),
				),
				'default' => 'right',
				'description' => __( 'You can add widgets to sidebars under Appearance - Widgets', 'minerva-kb' )
			),
			array(
				'id' => 'topic_children_layout',
				'type' => 'image_select',
				'label' => __( 'Sub-topics', 'minerva-kb' ),
				'options' => array(
					'2col' => array(
						'label' => __( '2 columns', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-2.png'
					),
					'3col' => array(
						'label' => __( '3 columns', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-3.png'
					),
					'4col' => array(
						'label' => __( '4 columns', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-4.png'
					),
				),
				'default' => '2col'
			),
			array(
				'id' => 'topic_children_view',
				'type' => 'image_select',
				'label' => __( 'Sub-topics view', 'minerva-kb' ),
				'options' => array(
					'list' => array(
						'label' => __( 'List view', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'list-view.png'
					),
					'box' => array(
						'label' => __( 'Box view', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'box-view.png'
					)
				),
				'default' => 'box'
			),
			array(
				'id' => 'home_topics_stretch',
				'type' => 'checkbox',
				'label' => __( 'Make home page topic boxes equal height (modern browsers only)?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Sometimes if topics have different content, it is a good idea to stretch smaller columns to bigger ones', 'minerva-kb' ),
			),
			array(
				'id' => 'topic_children_include_articles',
				'type' => 'checkbox',
				'label' => __( 'Include articles from child topics?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'When enabled, articles from nested categories will be included in current topic page', 'minerva-kb' ),
			),
			array(
				'id' => 'raw_topic_description_switch',
				'type' => 'checkbox',
				'label' => __( 'Allow HTML output in topic description?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Some plugins replace topic description editor with visual editor. This option allows to output HTML in topic description on client side.', 'minerva-kb' ),
			),
			array(
				'id' => 'topic_customize_title',
				'type' => 'checkbox',
				'label' => __( 'Customize topic titles?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'By default, standard WordPress category title format is used', 'minerva-kb' ),
			),
			array(
				'id' => 'topic_custom_title_prefix',
				'type' => 'input_text',
				'label' => __( 'Custom topic title prefix', 'minerva-kb' ),
				'default' => __( 'Topic: ', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'topic_customize_title',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_box_single_item_article_link',
				'type' => 'checkbox',
				'label' => __( 'Box view: link directly to article when only one article in topic?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'You can enable this to direct customers to article if there are no other articles in topic', 'minerva-kb' )
			),
			array(
				'id' => 'topic_show_child_topics_list',
				'type' => 'checkbox',
				'label' => __( 'List view: show child topics list before articles list?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'topic_child_topic_list_icon',
				'type' => 'icon_select',
				'label' => __( 'Child topic icon', 'minerva-kb' ),
				'default' => 'fa-folder',
				'dependency' => array(
					'target' => 'topic_show_child_topics_list',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'enable_articles_reorder',
				'type' => 'checkbox',
				'label' => __( 'Enable articles Drag n Drop custom order?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'When enabled, you will be able to reorder articles using Drag n Drop. By default, they\'re shown by date', 'minerva-kb' ),
			),
			array(
				'id' => 'articles_orderby',
				'type' => 'select',
				'label' => __( 'Articles order parameter', 'minerva-kb' ),
				'options' => array(
					'date' => __( 'Date', 'minerva-kb' ),
					'modified' => __( 'Last modified', 'minerva-kb' ),
					'title' => __( 'Title', 'minerva-kb' ),
					'ID' => __( 'ID', 'minerva-kb' ),
					'name' => __( 'Slug', 'minerva-kb' ),
					'comment_count' => __( 'Comments count', 'minerva-kb' ),
				),
				'default' => 'date',
				'dependency' => array(
					'target' => 'enable_articles_reorder',
					'type' => 'NEQ',
					'value' => true
				)
			),
			array(
				'id' => 'articles_order',
				'type' => 'select',
				'label' => __( 'Articles order', 'minerva-kb' ),
				'options' => array(
					'ASC' => __( 'Ascending', 'minerva-kb' ),
					'DESC' => __( 'Descending', 'minerva-kb' )
				),
				'default' => 'DESC',
				'dependency' => array(
					'target' => 'enable_articles_reorder',
					'type' => 'NEQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_no_results_subtitle',
				'type' => 'input_text',
				'label' => __( 'Text to display for empty archives', 'minerva-kb' ),
				'default' => __( 'We can&rsquo;t find what you&rsquo;re looking for. Try searching maybe.', 'minerva-kb' )
			),
			/**
			 * Dynamic topics
			 */
			array(
				'id' => 'dynamic_topic_tab',
				'type' => 'tab',
				'label' => __( 'Dynamic Topics', 'minerva-kb' ),
				'icon' => 'fa-address-book'
			),
			array(
				'id' => 'recent_topic_label',
				'type' => 'input_text',
				'label' => __( 'Recent label', 'minerva-kb' ),
				'default' => __( 'Recent', 'minerva-kb' )
			),
			array(
				'id' => 'recent_topic_description',
				'type' => 'input_text',
				'label' => __( 'Recent description', 'minerva-kb' ),
				'default' => __( 'Recently added articles', 'minerva-kb' )
			),
			array(
				'id' => 'updated_topic_label',
				'type' => 'input_text',
				'label' => __( 'Recently updated label', 'minerva-kb' ),
				'default' => __( 'Recently updated', 'minerva-kb' )
			),
			array(
				'id' => 'updated_topic_description',
				'type' => 'input_text',
				'label' => __( 'Updated description', 'minerva-kb' ),
				'default' => __( 'Recently updated articles', 'minerva-kb' )
			),
			array(
				'id' => 'most_viewed_topic_label',
				'type' => 'input_text',
				'label' => __( 'Most viewed label', 'minerva-kb' ),
				'default' => __( 'Most viewed', 'minerva-kb' )
			),
			array(
				'id' => 'most_viewed_topic_description',
				'type' => 'input_text',
				'label' => __( 'Most viewed description', 'minerva-kb' ),
				'default' => __( 'Articles with most pageviews', 'minerva-kb' )
			),
			array(
				'id' => 'most_liked_topic_label',
				'type' => 'input_text',
				'label' => __( 'Most liked label', 'minerva-kb' ),
				'default' => __( 'Most liked', 'minerva-kb' )
			),
			array(
				'id' => 'most_liked_topic_description',
				'type' => 'input_text',
				'label' => __( 'Most liked description', 'minerva-kb' ),
				'default' => __( 'Most useful articles, calculated by article likes', 'minerva-kb' )
			),
			/**
			 * Topic search
			 */
			array(
				'id' => 'topic_search_tab',
				'type' => 'tab',
				'label' => __( 'Topic search', 'minerva-kb' ),
				'icon' => 'fa-search'
			),
			array(
				'id' => 'add_topic_search',
				'type' => 'checkbox',
				'label' => __( 'Add search in topics?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'topic_search_title',
				'type' => 'input_text',
				'label' => __( 'Topic search title', 'minerva-kb' ),
				'default' => __( '', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_title_color',
				'type' => 'color',
				'label' => __( 'Search title color', 'minerva-kb' ),
				'default' => '#333333',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_title_size',
				'type' => 'input',
				'label' => __( 'Search title font size', 'minerva-kb' ),
				'default' => __( '1.2em', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 3em or 20px',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_theme',
				'type' => 'select',
				'label' => __( 'Which search input theme to use?', 'minerva-kb' ),
				'options' => array(
					'minerva' => __( 'Minerva', 'minerva-kb' ),
					'clean' => __( 'Clean', 'minerva-kb' ),
					'mini' => __( 'Mini', 'minerva-kb' ),
					'bold' => __( 'Bold', 'minerva-kb' ),
					'invisible' => __( 'Invisible', 'minerva-kb' ),
					'thick' => __( 'Thick', 'minerva-kb' ),
					'3d' => __( '3d', 'minerva-kb' ),
				),
				'default' => 'mini',
				'description' => __( 'Use predefined styles for search bar', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_border_color',
				'type' => 'color',
				'label' => __( 'Search wrap border color (not in all themes)', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_min_width',
				'type' => 'input',
				'label' => __( 'Search input minimum width', 'minerva-kb' ),
				'default' => __( '100%', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 40em or 300px. em are better for mobile devices',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_container_padding_top',
				'type' => 'input',
				'label' => __( 'Search container top padding', 'minerva-kb' ),
				'default' => __( '0', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 3em or 50px',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_container_padding_bottom',
				'type' => 'input',
				'label' => __( 'Search container bottom padding', 'minerva-kb' ),
				'default' => __( '0', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 3em or 50px',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_placeholder',
				'type' => 'input_text',
				'label' => __( 'Topic search placeholder', 'minerva-kb' ),
				'default' => __( 'ex.: Installation', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_disable_autofocus',
				'type' => 'checkbox',
				'label' => __( 'Disable search field autofocus?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_show_search_tip',
				'type' => 'checkbox',
				'label' => __( 'Show search tip?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_tip',
				'type' => 'input_text',
				'label' => __( 'Topic search tip (under the input)', 'minerva-kb' ),
				'default' => __( 'Tip: Use arrows to navigate results, ESC to focus search input', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_tip_color',
				'type' => 'color',
				'label' => __( 'Search tip color', 'minerva-kb' ),
				'default' => '#cccccc',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_container_bg',
				'type' => 'color',
				'label' => __( 'Search container background color', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_container_image_bg',
				'type' => 'media',
				'label' => __( 'Search container background image URL (optional)', 'minerva-kb' ),
				'default' => '',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_add_gradient_overlay',
				'type' => 'checkbox',
				'label' => __( 'Add gradient overlay?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_container_gradient_from',
				'type' => 'color',
				'label' => __( 'Search container gradient from', 'minerva-kb' ),
				'default' => '#00c1b6',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_container_gradient_to',
				'type' => 'color',
				'label' => __( 'Search container gradient to', 'minerva-kb' ),
				'default' => '#136eb5',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_container_gradient_opacity',
				'type' => 'input',
				'label' => __( 'Search container background gradient opacity', 'minerva-kb' ),
				'default' => 1,
				'description' => __( 'Use any CSS opacity value, for example 1 or 0.7', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_add_pattern_overlay',
				'type' => 'checkbox',
				'label' => __( 'Add pattern overlay?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_container_image_pattern',
				'type' => 'media',
				'label' => __( 'Search container background pattern image URL (optional)', 'minerva-kb' ),
				'default' => '',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_container_image_pattern_opacity',
				'type' => 'input',
				'label' => __( 'Search container background pattern opacity', 'minerva-kb' ),
				'default' => 1,
				'description' => __( 'Use any CSS opacity value, for example 1 or 0.7. You can also use transparent .png and set opacity to 1', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_show_topic_in_results',
				'type' => 'checkbox',
				'label' => __( 'Show topic in results?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			/**
			 * Tags
			 */
			array(
				'id' => 'tags_tab',
				'type' => 'tab',
				'label' => __( 'Tags', 'minerva-kb' ),
				'icon' => 'fa-tags'
			),
			array(
				'id' => 'tags_disable',
				'type' => 'checkbox',
				'label' => __( 'Disable tags archive?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'You can use tags for description purposes, but remove tags archive and tag links from articles', 'minerva-kb' ),
			),
			array(
				'id' => 'tag_template',
				'type' => 'select',
				'label' => __( 'Which tag template to use?', 'minerva-kb' ),
				'options' => array(
					'theme' => __( 'Theme archive template', 'minerva-kb' ),
					'plugin' => __( 'Plugin tag template', 'minerva-kb' )
				),
				'default' => 'plugin',
				'experimental' => __( 'This is experimental feature and depends a lot on theme styles and layout', 'minerva-kb' ),
				'description' => __( 'Note, that you can override plugin templates in your theme. See documentation for details', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'tags_disable',
					'type' => 'NEQ',
					'value' => true
				)
			),
			array(
				'id' => 'tag_articles_per_page',
				'type' => 'input',
				'label' => __( 'Number of articles per tag page. Use -1 to show all', 'minerva-kb' ),
				'default' => __( '10', 'minerva-kb' )
			),
			array(
				'id' => 'tag_sidebar',
				'type' => 'image_select',
				'label' => __( 'Tag sidebar position', 'minerva-kb' ),
				'options' => array(
					'none' => array(
						'label' => __( 'None', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'no-sidebar.png'
					),
					'left' => array(
						'label' => __( 'Left', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'left-sidebar.png'
					),
					'right' => array(
						'label' => __( 'Right', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'right-sidebar.png'
					),
				),
				'default' => 'right',
				'description' => __( 'You can add widgets to sidebars under Appearance - Widgets', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'tags_disable',
					'type' => 'NEQ',
					'value' => true
				)
			),
			/**
			 * Breadcrumbs
			 */
			array(
				'id' => 'breadcrumbs_tab',
				'type' => 'tab',
				'label' => __( 'Breadcrumbs', 'minerva-kb' ),
				'icon' => 'fa-ellipsis-h'
			),
			array(
				'id' => 'breadcrumbs_home_label',
				'type' => 'input_text',
				'label' => __( 'Breadcrumbs home page label', 'minerva-kb' ),
				'default' => __( 'KB Home', 'minerva-kb' )
			),
			array(
				'id' => 'breadcrumbs_custom_home_switch',
				'type' => 'checkbox',
				'label' => __( 'Set custom home page link?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'This can be useful if you are building KB home page with shortcodes', 'minerva-kb' )
			),
			array(
				'id' => 'breadcrumbs_custom_home_page',
				'type' => 'select',
				'label' => __( 'Breadcrumbs custom home page', 'minerva-kb' ),
				'options' => self::get_pages_options(),
				'default' => '',
				'description' => __( 'Select breadcrumbs custom home page', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'breadcrumbs_custom_home_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'breadcrumbs_label',
				'type' => 'input_text',
				'label' => __( 'Breadcrumbs label', 'minerva-kb' ),
				'default' => __( 'You are here:', 'minerva-kb' )
			),
			array(
				'id' => 'breadcrumbs_separator_icon',
				'type' => 'icon_select',
				'label' => __( 'Breadcrumbs separator', 'minerva-kb' ),
				'default' => 'fa-caret-right'
			),
			array(
				'id' => 'breadcrumbs_font_size',
				'type' => 'css_size',
				'label' => __( 'Breadcrumbs font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1"),
				'description' => __( 'Breadcrumbs font size', 'minerva-kb' )
			),
			array(
				'id' => 'breadcrumbs_align',
				'type' => 'select',
				'label' => __( 'Breadcrumbs text align', 'minerva-kb' ),
				'options' => array(
					'left' => __( 'Left', 'minerva-kb' ),
					'center' => __( 'Center', 'minerva-kb' ),
					'right' => __( 'Right', 'minerva-kb' )
				),
				'default' => 'left',
				'description' => __( 'Select text align for breadrumbs', 'minerva-kb' )
			),
			array(
				'id' => 'breadcrumbs_top_padding',
				'type' => 'css_size',
				'label' => __( 'Breadcrumbs top padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => __( 'Breadcrumbs container top padding', 'minerva-kb' )
			),
			array(
				'id' => 'breadcrumbs_bottom_padding',
				'type' => 'css_size',
				'label' => __( 'Breadcrumbs bottom padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => __( 'Breadcrumbs container bottom padding', 'minerva-kb' )
			),
			array(
				'id' => 'breadcrumbs_left_padding',
				'type' => 'css_size',
				'label' => __( 'Breadcrumbs left padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => __( 'Breadcrumbs container left padding', 'minerva-kb' )
			),
			array(
				'id' => 'breadcrumbs_right_padding',
				'type' => 'css_size',
				'label' => __( 'Breadcrumbs right padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => __( 'Breadcrumbs container right padding', 'minerva-kb' )
			),
			array(
				'id' => 'breadcrumbs_bg_color',
				'type' => 'color',
				'label' => __( 'Breadcrumbs container background color (transparent by default)', 'minerva-kb' ),
				'default' => 'rgba(255,255,255,0)'
			),
			array(
				'id' => 'breadcrumbs_image_bg',
				'type' => 'media',
				'label' => __( 'Breadcrumbs background image URL (optional)', 'minerva-kb' ),
				'default' => ''
			),
			array(
				'id' => 'breadcrumbs_add_gradient',
				'type' => 'checkbox',
				'label' => __( 'Add gradient overlay?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'breadcrumbs_gradient_from',
				'type' => 'color',
				'label' => __( 'Breadcrumbs gradient from', 'minerva-kb' ),
				'default' => '#00c1b6',
				'dependency' => array(
					'target' => 'breadcrumbs_add_gradient',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'breadcrumbs_gradient_to',
				'type' => 'color',
				'label' => __( 'Breadcrumbs gradient to', 'minerva-kb' ),
				'default' => '#136eb5',
				'dependency' => array(
					'target' => 'breadcrumbs_add_gradient',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'breadcrumbs_gradient_opacity',
				'type' => 'input',
				'label' => __( 'Breadcrumbs background gradient opacity', 'minerva-kb' ),
				'default' => 1,
				'description' => __( 'Use any CSS opacity value, for example 1 or 0.7', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'breadcrumbs_add_gradient',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'breadcrumbs_add_pattern',
				'type' => 'checkbox',
				'label' => __( 'Add pattern overlay?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'breadcrumbs_image_pattern',
				'type' => 'media',
				'label' => __( 'Breadcrumbs background pattern image URL (optional)', 'minerva-kb' ),
				'default' => '',
				'dependency' => array(
					'target' => 'breadcrumbs_add_pattern',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'breadcrumbs_image_pattern_opacity',
				'type' => 'input',
				'label' => __( 'Breadcrumbs background pattern opacity', 'minerva-kb' ),
				'default' => 1,
				'description' => __( 'Use any CSS opacity value, for example 1 or 0.7. You can also use transparent .png and set opacity to 1', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'breadcrumbs_add_pattern',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'breadcrumbs_text_color',
				'type' => 'color',
				'label' => __( 'Breadcrumbs text color', 'minerva-kb' ),
				'default' => '#888'
			),
			array(
				'id' => 'breadcrumbs_link_color',
				'type' => 'color',
				'label' => __( 'Breadcrumbs link color', 'minerva-kb' ),
				'default' => '#888'
			),
			array(
				'id' => 'breadcrumbs_add_shadow',
				'type' => 'checkbox',
				'label' => __( 'Add shadow to breadcrumbs container?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'breadcrumbs_inset_shadow',
				'type' => 'checkbox',
				'label' => __( 'Inner shadow?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'breadcrumbs_add_shadow',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_breadcrumbs_category',
				'type' => 'checkbox',
				'label' => __( 'Show breadcrumbs in category?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'show_breadcrumbs_single',
				'type' => 'checkbox',
				'label' => __( 'Show breadcrumbs in article?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'show_breadcrumbs_current_title',
				'type' => 'checkbox',
				'label' => __( 'Show current article title in breadcrumbs?', 'minerva-kb' ),
				'default' => true
			),
			/**
			 * Rating
			 */
			array(
				'id' => 'rating_tab',
				'type' => 'tab',
				'label' => __( 'Rating', 'minerva-kb' ),
				'icon' => 'fa-star-o'
			),
			array(
				'id' => 'rating_block_label',
				'type' => 'input_text',
				'label' => __( 'Rating block label', 'minerva-kb' ),
				'default' => __( 'Was this article helpful?', 'minerva-kb' )
			),
			array(
				'id' => 'likes_title',
				'type' => 'title',
				'label' => __( 'Likes settings', 'minerva-kb' ),
				'description' => __( 'Configure rating likes', 'minerva-kb' )
			),
			array(
				'id' => 'show_likes_button',
				'type' => 'checkbox',
				'label' => __( 'Show likes button?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'like_label',
				'type' => 'input_text',
				'label' => __( 'Like label', 'minerva-kb' ),
				'default' => __( 'Like', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_likes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_likes_icon',
				'type' => 'checkbox',
				'label' => __( 'Show likes icon?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'show_likes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'like_icon',
				'type' => 'icon_select',
				'label' => __( 'Like icon', 'minerva-kb' ),
				'default' => 'fa-smile-o',
				'dependency' => array(
					'target' => 'show_likes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'like_color',
				'type' => 'color',
				'label' => __( 'Like button color (used also for messages and feedback form button)', 'minerva-kb' ),
				'default' => '#4BB651',
				'dependency' => array(
					'target' => 'show_likes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_likes_count',
				'type' => 'checkbox',
				'label' => __( 'Show likes count?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'show_likes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_like_message',
				'type' => 'checkbox',
				'label' => __( 'Show message after like?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'show_likes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'like_message_text',
				'type' => 'textarea_text',
				'label' => __( 'Like message text', 'minerva-kb' ),
				'default' => __( '<i class="fa fa-smile-o"></i> Great!<br/><strong>Thank you</strong> for your vote!', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_likes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'dislikes_title',
				'type' => 'title',
				'label' => __( 'Dislikes settings', 'minerva-kb' ),
				'description' => __( 'Configure rating dislikes', 'minerva-kb' )
			),
			array(
				'id' => 'show_dislikes_button',
				'type' => 'checkbox',
				'label' => __( 'Show dislikes button?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'dislike_label',
				'type' => 'input_text',
				'label' => __( 'Dislike label', 'minerva-kb' ),
				'default' => __( 'Dislike', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_dislikes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_dislikes_icon',
				'type' => 'checkbox',
				'label' => __( 'Show dislikes icon?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'show_dislikes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'dislike_icon',
				'type' => 'icon_select',
				'label' => __( 'Dislike icon', 'minerva-kb' ),
				'default' => 'fa-frown-o',
				'dependency' => array(
					'target' => 'show_dislikes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'dislike_color',
				'type' => 'color',
				'label' => __( 'Dislike button color', 'minerva-kb' ),
				'default' => '#C85C5E',
				'dependency' => array(
					'target' => 'show_dislikes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_dislikes_count',
				'type' => 'checkbox',
				'label' => __( 'Show dislikes count?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'show_dislikes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_dislike_message',
				'type' => 'checkbox',
				'label' => __( 'Show message after dislike?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'show_dislikes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'dislike_message_text',
				'type' => 'textarea_text',
				'label' => __( 'Dislike message text', 'minerva-kb' ),
				'default' => __( 'Thank you for your vote!', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_dislikes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'rating_message_bg',
				'type' => 'color',
				'label' => __( 'Like / dislike message background color', 'minerva-kb' ),
				'default' => '#f7f7f7'
			),
			array(
				'id' => 'rating_message_color',
				'type' => 'color',
				'label' => __( 'Like / dislike message text color', 'minerva-kb' ),
				'default' => '#888'
			),
			array(
				'id' => 'rating_message_border_color',
				'type' => 'color',
				'label' => __( 'Like / dislike message border color', 'minerva-kb' ),
				'default' => '#eee'
			),
			array(
				'id' => 'show_rating_total',
				'type' => 'checkbox',
				'label' => __( 'Show rating total?', 'minerva-kb' ),
				'default' => false,
				'description' => 'A line of text, like: 3 of 10 found this article helpful'
			),
			array(
				'id' => 'rating_total_text',
				'type' => 'input_text',
				'label' => __( 'Rating total text', 'minerva-kb' ),
				'default' => __( '%d of %d found this article helpful.', 'minerva-kb' ),
				'description' => 'First %d is replaced with likes, second - with total sum of votes',
				'dependency' => array(
					'target' => 'show_rating_total',
					'type' => 'EQ',
					'value' => true
				)
			),
			/**
			 * Feedback
			 */
			array(
				'id' => 'feedback_tab',
				'type' => 'tab',
				'label' => __( 'Feedback', 'minerva-kb' ),
				'icon' => 'fa-bullhorn'
			),
			array(
				'id' => 'enable_feedback',
				'type' => 'checkbox',
				'label' => __( 'Enable article feedback?', 'minerva-kb' ),
				'default' => false,
				'description' => 'Allow users to leave feedback on articles'
			),
			array(
				'id' => 'feedback_mode',
				'type' => 'select',
				'label' => __( 'Feedback form display mode?', 'minerva-kb' ),
				'options' => array(
					'dislike' => __( 'Show after dislike', 'minerva-kb' ),
					'like' => __( 'Show after like', 'minerva-kb' ),
					'any' => __( 'Show after like or dislike', 'minerva-kb' ),
					'always' => __( 'Always present', 'minerva-kb' )
				),
				'default' => 'dislike',
				'description' => __( 'Select when to display feedback form', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'feedback_label',
				'type' => 'input_text',
				'label' => __( 'Set feedback form label', 'minerva-kb' ),
				'default' => __( 'You can leave feedback:', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'feedback_submit_label',
				'type' => 'input_text',
				'label' => __( 'Set feedback submit button label', 'minerva-kb' ),
				'default' => __( 'Send feedback', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'feedback_submit_request_label',
				'type' => 'input_text',
				'label' => __( 'Set feedback submit button label to show when request in progress', 'minerva-kb' ),
				'default' => __( 'Sending...', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'feedback_submit_bg',
				'type' => 'color',
				'label' => __( 'Feedback submit button background color', 'minerva-kb' ),
				'default' => '#4a90e2',
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'feedback_submit_color',
				'type' => 'color',
				'label' => __( 'Feedback submit button text color', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'feedback_info_text',
				'type' => 'textarea_text',
				'label' => __( 'You can add extra description to feedback form', 'minerva-kb' ),
				'default' => __( 'We will use your feedback to improve this article', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'feedback_sent_text',
				'type' => 'textarea_text',
				'label' => __( 'Text to display after feedback sent. You can use HTML', 'minerva-kb' ),
				'default' => __( 'Thank you for your feedback, we will do our best to fix this soon', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'feedback_message_bg',
				'type' => 'color',
				'label' => __( 'Feedback message background color', 'minerva-kb' ),
				'default' => '#f7f7f7',
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'feedback_message_color',
				'type' => 'color',
				'label' => __( 'Feedback message text color', 'minerva-kb' ),
				'default' => '#888',
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'feedback_message_border_color',
				'type' => 'color',
				'label' => __( 'Feedback message border color', 'minerva-kb' ),
				'default' => '#eee',
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			/**
			 * Shortcodes
			 */
			array(
				'id' => 'shortcodes_tab',
				'type' => 'tab',
				'label' => __( 'Shortcodes', 'minerva-kb' ),
				'icon' => 'fa-code'
			),
			array(
				'id' => 'info_title',
				'type' => 'title',
				'label' => __( 'Info shortcode', 'minerva-kb' ),
				'description' => __( 'Highlight interesting information using this shortcode', 'minerva-kb' ),
				'preview_image' => MINERVA_KB_IMG_URL . 'info-shortcode.png'
			),
			array(
				'id' => 'info_usage',
				'type' => 'code',
				'label' => __( 'Info use example', 'minerva-kb' ),
				'default' => '[mkb-info]Lorem Ipsum is simply dummy text of the printing and typesetting industry.[/mkb-info]'
			),
			array(
				'id' => 'info_icon',
				'type' => 'icon_select',
				'label' => __( 'Info icon', 'minerva-kb' ),
				'default' => 'fa-info-circle'
			),
			array(
				'id' => 'info_bg',
				'type' => 'color',
				'label' => __( 'Info background', 'minerva-kb' ),
				'default' => '#d9edf7'
			),			
			array(
				'id' => 'info_border',
				'type' => 'color',
				'label' => __( 'Info border color', 'minerva-kb' ),
				'default' => '#bce8f1'
			),
			array(
				'id' => 'info_icon_color',
				'type' => 'color',
				'label' => __( 'Info icon color', 'minerva-kb' ),
				'default' => '#31708f'
			),
			array(
				'id' => 'info_color',
				'type' => 'color',
				'label' => __( 'Info text color', 'minerva-kb' ),
				'default' => '#333333'
			),
			array(
				'id' => 'tip_title',
				'type' => 'title',
				'label' => __( 'Tip shortcode', 'minerva-kb' ),
				'description' => __( 'Highlight interesting information using this shortcode', 'minerva-kb' ),
				'preview_image' => MINERVA_KB_IMG_URL . 'tip-shortcode.png'
			),
			array(
				'id' => 'tip_usage',
				'type' => 'code',
				'label' => __( 'Tip use example', 'minerva-kb' ),
				'default' => '[mkb-tip]Lorem Ipsum is simply dummy text of the printing and typesetting industry.[/mkb-tip]'
			),
			array(
				'id' => 'tip_icon',
				'type' => 'icon_select',
				'label' => __( 'Tip icon', 'minerva-kb' ),
				'default' => 'fa-lightbulb-o'
			),
			array(
				'id' => 'tip_bg',
				'type' => 'color',
				'label' => __( 'Tip background', 'minerva-kb' ),
				'default' => '#fcf8e3'
			),
			array(
				'id' => 'tip_border',
				'type' => 'color',
				'label' => __( 'Tip border color', 'minerva-kb' ),
				'default' => '#faebcc'
			),
			array(
				'id' => 'tip_icon_color',
				'type' => 'color',
				'label' => __( 'Tip icon color', 'minerva-kb' ),
				'default' => '#8a6d3b'
			),
			array(
				'id' => 'tip_color',
				'type' => 'color',
				'label' => __( 'Tip text color', 'minerva-kb' ),
				'default' => '#333333'
			),
			array(
				'id' => 'warning_title',
				'type' => 'title',
				'label' => __( 'Warning shortcode', 'minerva-kb' ),
				'description' => __( 'Highlight important information using this shortcode', 'minerva-kb' ),
				'preview_image' => MINERVA_KB_IMG_URL . 'warning-shortcode.png'
			),
			array(
				'id' => 'warning_usage',
				'type' => 'code',
				'label' => __( 'Warning use example', 'minerva-kb' ),
				'default' => '[mkb-warning]Lorem Ipsum is simply dummy text of the printing and typesetting industry.[/mkb-warning]'
			),
			array(
				'id' => 'warning_icon',
				'type' => 'icon_select',
				'label' => __( 'Warning icon', 'minerva-kb' ),
				'default' => 'fa-exclamation-triangle'
			),
			array(
				'id' => 'warning_bg',
				'type' => 'color',
				'label' => __( 'Warning background', 'minerva-kb' ),
				'default' => '#f2dede'
			),
			array(
				'id' => 'warning_border',
				'type' => 'color',
				'label' => __( 'Warning border color', 'minerva-kb' ),
				'default' => '#ebccd1'
			),
			array(
				'id' => 'warning_icon_color',
				'type' => 'color',
				'label' => __( 'Warning icon color', 'minerva-kb' ),
				'default' => '#a94442'
			),
			array(
				'id' => 'warning_color',
				'type' => 'color',
				'label' => __( 'Warning text color', 'minerva-kb' ),
				'default' => '#333333'
			),
			// Related content
			array(
				'id' => 'related_content_title',
				'type' => 'title',
				'label' => __( 'Related content shortcode', 'minerva-kb' ),
				'description' => __( 'Show links to related content with this shortcode', 'minerva-kb' ),
				'preview_image' => MINERVA_KB_IMG_URL . 'related-content-shortcode.png'
			),
			array(
				'id' => 'related_content_usage',
				'type' => 'code',
				'label' => __( 'Related use example. Add list of ids', 'minerva-kb' ),
				'default' => '[mkb-related ids="7,8,19"][/mkb-related]'
			),
			array(
				'id' => 'related_content_label',
				'type' => 'input_text',
				'label' => __( 'Related content shortcode label', 'minerva-kb' ),
				'default' => __( 'See also:', 'minerva-kb' )
			),
			array(
				'id' => 'related_content_bg',
				'type' => 'color',
				'label' => __( 'Related content background', 'minerva-kb' ),
				'default' => '#e8f9f2'
			),
			array(
				'id' => 'related_content_border',
				'type' => 'color',
				'label' => __( 'Related content border color', 'minerva-kb' ),
				'default' => '#2ab77b'
			),
			array(
				'id' => 'related_content_links_color',
				'type' => 'color',
				'label' => __( 'Related content links color', 'minerva-kb' ),
				'default' => '#007acc'
			),
			array(
				'id' => 'related_content_label_color',
				'type' => 'color',
				'label' => __( 'Related content label color', 'minerva-kb' ),
				'default' => '#333333'
			),

			/**
			 * TOC
			 */
			array(
				'id' => 'toc_tab',
				'type' => 'tab',
				'label' => __( 'Table of contents', 'minerva-kb' ),
				'icon' => 'fa-list-ol'
			),
			array(
				'id' => 'toc_title',
				'type' => 'title',
				'label' => __( 'Table of contents', 'minerva-kb' ),
				'description' => __( 'Build dynamic table of contents using heading tags or anchor shortcode', 'minerva-kb' ),
				'preview_image' => MINERVA_KB_IMG_URL . 'toc-shortcode.png',
				'width' => 200
			),
			array(
				'id' => 'toc_global_info',
				'type' => 'info',
				'label' => 'Table of contents is built dynamically from h1-h6 heading tags inside article. ' .
				           'To use table of contents as sidebar widget, you need to disable it in article body, using option below.'.
				           'You can also build table of contents manually, using mkb-anchor shortcode.',
			),
			array(
				'id' => 'toc_dynamic_enable',
				'type' => 'checkbox',
				'label' => __( 'Enable dynamic table of contents?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Dynamic table of contents is built from headings found in article. NOTE: if [mkb-anchor] shortcodes are found in article, dynamic TOC will switch to shortcode (manual) mode', 'minerva-kb' ),
			),
			array(
				'id' => 'toc_hierarchical_enable',
				'type' => 'checkbox',
				'label' => __( 'Hierarchical table of contents?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'When enabled, will build hierarchical tree, h1 - top level, h6 - bottom level. NOTE: lower level headings without parents will be treated as root entries', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'toc_dynamic_enable',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'toc_max_width',
				'type' => 'css_size',
				'label' => __( 'Width of TOC in article', 'minerva-kb' ),
				'default' => array("unit" => '%', "size" => "30"),
				'description' => __( 'Width of table of contents in article body', 'minerva-kb' )
			),
			array(
				'id' => 'toc_max_width_h',
				'type' => 'css_size',
				'label' => __( 'Width of hierarchical TOC in article', 'minerva-kb' ),
				'default' => array("unit" => '%', "size" => "40"),
				'description' => __( 'Width of hierarchical table of contents in article body', 'minerva-kb' )
			),
			array(
				'id' => 'toc_numbers_enable',
				'type' => 'checkbox',
				'label' => __( 'Add numbers to table of contents items?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'You can disable this to remove numbers before items.', 'minerva-kb' ),
			),
			array(
				'id' => 'toc_headings_exclude',
				'type' => 'input',
				'label' => __( 'Exclude specific headings from table of contents', 'minerva-kb' ),
				'default' => __( '', 'minerva-kb' ),
				'description' => __( 'Comma-separated list of headings you want to exclude from dynamic table of contents. Example value: "h1,h3,h5"', 'minerva-kb' ),
			),
			array(
				'id' => 'toc_content_parse',
				'type' => 'checkbox',
				'label' => __( 'Parse article content (shortcodes) before generating table of contents?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Some plugins, like VC, add their own heading shortcodes. Turn this on if you need those headings in table of contents.', 'minerva-kb' ),
			),
			array(
				'id' => 'toc_url_update',
				'type' => 'checkbox',
				'label' => __( 'Update page url on section select?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'You can use this option to update URL on each section navigation, so that link is always actual.', 'minerva-kb' ),
			),
			array(
				'id' => 'toc_scroll_offset',
				'type' => 'css_size',
				'label' => __( 'Table of contents scroll offset', 'minerva-kb' ),
				'default' => array("unit" => 'px', "size" => "0"),
				'units' => array("px"),
				'description' => __( 'Can be useful if you have sticky header that overlaps content. You can use negative values here as well.', 'minerva-kb' )
			),

			array(
				'id' => 'toc_label',
				'type' => 'input_text',
				'label' => __( 'Table of contents label', 'minerva-kb' ),
				'default' => __( 'In this article', 'minerva-kb' )
			),
			// back to top
			array(
				'id' => 'toc_back_to_top_title',
				'type' => 'title',
				'label' => __( 'Back to top', 'minerva-kb' ),
				'description' => __( 'Configure Back to top links for TOC', 'minerva-kb' )
			),
			array(
				'id' => 'show_back_to_top',
				'type' => 'checkbox',
				'label' => __( 'Show back to top link in anchors?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'back_to_site_top',
				'type' => 'checkbox',
				'label' => __( 'Scroll back to site top?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'By default, back to top scrolls to article text top', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_back_to_top',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'back_to_top_text',
				'type' => 'input_text',
				'label' => __( 'Back to top text', 'minerva-kb' ),
				'default' => __( 'To top', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_back_to_top',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_back_to_top_icon',
				'type' => 'checkbox',
				'label' => __( 'Add back to top icon?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'show_back_to_top',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'back_to_top_icon',
				'type' => 'icon_select',
				'label' => __( 'Back to top icon', 'minerva-kb' ),
				'default' => 'fa-long-arrow-up',
				'dependency' => array(
					'target' => 'show_back_to_top',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'back_to_top_position',
				'type' => 'select',
				'label' => __( 'Where to display back to top?', 'minerva-kb' ),
				'options' => array(
					'inline' => __( 'Inline with section title', 'minerva-kb' ),
					'under' => __( 'Under section title', 'minerva-kb' )
				),
				'default' => 'inline',
				'dependency' => array(
					'target' => 'show_back_to_top',
					'type' => 'EQ',
					'value' => true
				)
			),
			// scrollspy
			array(
				'id' => 'scrollspy_title',
				'type' => 'title',
				'label' => __( 'Table of contents Widget / ScrollSpy settings', 'minerva-kb' ),
				'description' => __( 'Configure TOC widget', 'minerva-kb' )
			),
			array(
				'id' => 'toc_in_content_disable',
				'type' => 'checkbox',
				'label' => __( 'Remove table of contents from article body?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'This must be on if you plan to use table of contents widget in article sidebar.', 'minerva-kb' ),
			),
			array(
				'id' => 'toc_sidebar_desktop_only',
				'type' => 'checkbox',
				'label' => __( 'Always show TOC in article body on mobile/tablets?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'On mobile devices sidebar is displayed under the content, so table of contents works better in article body.', 'minerva-kb' ),
			),
			array(
				'id' => 'scrollspy_switch',
				'type' => 'checkbox',
				'label' => __( 'Enable ScrollSpy?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'scrollspy_bg',
				'type' => 'color',
				'label' => __( 'Active link background color', 'minerva-kb' ),
				'default' => '#00aae8',
				'dependency' => array(
					'target' => 'scrollspy_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'scrollspy_color',
				'type' => 'color',
				'label' => __( 'Active link text color', 'minerva-kb' ),
				'default' => '#fff',
				'dependency' => array(
					'target' => 'scrollspy_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			// manual
			array(
				'id' => 'toc_manual_title',
				'type' => 'title',
				'label' => __( 'Table of contents manual mode', 'minerva-kb' ),
			),
			array(
				'id' => 'toc_manual_info',
				'type' => 'info',
				'label' => 'Table of contents can be build using shortcodes instead of headings, in case you can not use h1-h6 tags in article text. ' .
				           'To use table of contents in manual mode you need to disable dynamic table of contents above and use mkb-anchor shortcodes, see example below.',
			),
			array(
				'id' => 'toc_manual_usage',
				'type' => 'code',
				'label' => __( 'Table of contents manual mode (shortcode) use example', 'minerva-kb' ),
				'default' => '[mkb-anchor]Section name[/mkb-anchor]'
			),
			/**
			 * Restrict content
			 */
			array(
				'id' => 'restrict_tab',
				'type' => 'tab',
				'label' => __( 'Restrict Access', 'minerva-kb' ),
				'icon' => 'fa-lock'
			),
			array(
				'id' => 'restrict_title',
				'type' => 'title',
				'label' => __( 'Content restriction settings', 'minerva-kb' ),
				'description' => __( 'You can customize who can see the knowledge base content here', 'minerva-kb' )
			),
			array(
				'id' => 'restrict_on',
				'type' => 'checkbox',
				'label' => __( 'Enable content restriction?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'By default, we disable restrict functionality, since you might use external plugin for this', 'minerva-kb' ),
			),
			array(
				'id' => 'restrict_article_role',
				'type' => 'roles_select',
				'label' => __( 'Global restriction: who can view articles?', 'minerva-kb' ),
				'default' => 'none',
				'flush' => true,
				'view_log' => true,
				'description' => __( 'Select roles, that have access to articles on client side.<br/> If you want to restrict specific articles or topics, you do so on article and topic pages', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_remove_from_archives',
				'type' => 'checkbox',
				'label' => __( 'Remove restricted articles & topics from home page and archives?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'You can display or remove restricted articles from topics', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_remove_from_search',
				'type' => 'checkbox',
				'label' => __( 'Remove restricted articles from search results?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'You can display or remove restricted articles from search results', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_remove_search_for_restricted',
				'type' => 'checkbox',
				'label' => __( 'Remove search sections when user has no access to knowledge base?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'You can remove search modules completely for users who do not have access to content.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_article_message',
				'type' => 'textarea_text',
				'label' => __( 'Restricted article message', 'minerva-kb' ),
				'description' => __( 'Message to display when unauthorized user is trying to access restricted article. You can use HTML here', 'minerva-kb' ),
				'default' => __( 'The content you are trying to access is for members only. Please login to view it.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_message_icon',
				'type' => 'icon_select',
				'label' => __( 'Restrict message icon', 'minerva-kb' ),
				'default' => 'fa-lock',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_message_bg',
				'type' => 'color',
				'label' => __( 'Restrict message background', 'minerva-kb' ),
				'default' => '#fcf8e3',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_message_border',
				'type' => 'color',
				'label' => __( 'Restrict message border color', 'minerva-kb' ),
				'default' => '#faebcc',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_message_icon_color',
				'type' => 'color',
				'label' => __( 'Restrict message icon color', 'minerva-kb' ),
				'default' => '#8a6d3b',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_message_color',
				'type' => 'color',
				'label' => __( 'Restrict message text color', 'minerva-kb' ),
				'default' => '#333333',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_show_article_breadcrumbs',
				'type' => 'checkbox',
				'label' => __( 'Show breadcrumbs on restricted articles?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Control the visibility of breadcrumbs on restricted articles', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_show_article_search',
				'type' => 'checkbox',
				'label' => __( 'Show articles search section on restricted articles?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Control the visibility of search on restricted articles', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_show_article_related',
				'type' => 'checkbox',
				'label' => __( 'Show related articles section on restricted articles?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Control the visibility of related articles section on restricted articles', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_show_article_excerpt',
				'type' => 'checkbox',
				'label' => __( 'Show excerpt for restricted articles?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Control the teaser/excerpt for restricted articles. NOTE, the text added to excerpt box displayed, not dynamically generated', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_show_article_toc',
				'type' => 'checkbox',
				'label' => __( 'Show table of contents widget for restricted articles?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Control the TOC display for restricted articles.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_show_excerpt_gradient',
				'type' => 'checkbox',
				'label' => __( 'Show excerpt gradient overlay?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'A semi-transparent gradient, that hides the ending of the excerpt', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_article_excerpt_gradient_start',
				'type' => 'color',
				'label' => __( 'Start color for overlay gradient', 'minerva-kb' ),
				'default' => '#fff',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_message_before_html',
				'type' => 'textarea_text',
				'label' => __( 'Restricted article additional HTML (before login form)', 'minerva-kb' ),
				'description' => __( 'Use this field if you need to display any extra HTML content before login form', 'minerva-kb' ),
				'default' => __( '', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_message_after_html',
				'type' => 'textarea_text',
				'label' => __( 'Restricted article additional HTML (after login form)', 'minerva-kb' ),
				'description' => __( 'Use this field if you need to display any extra HTML content after messages and login form', 'minerva-kb' ),
				'default' => __( '', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_title',
				'type' => 'title',
				'label' => __( 'Restricted content login form', 'minerva-kb' ),
				'description' => __( 'Configure the appearance for the login form', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_show_login_form',
				'type' => 'checkbox',
				'label' => __( 'Show login form after restricted content message?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Control the login form display for restricted articles', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_form_width',
				'type' => 'css_size',
				'label' => __( 'Login form width', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "26"),
				'description' => __( 'Minimum width for login form', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_form_align',
				'type' => 'select',
				'label' => __( 'Login form align in container', 'minerva-kb' ),
				'options' => array(
					'left' => __( 'Left', 'minerva-kb' ),
					'center' => __( 'Center', 'minerva-kb' ),
					'right' => __( 'Right', 'minerva-kb' ),
				),
				'default' => 'center',
				'description' => __( 'Select login form align', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_bg',
				'type' => 'color',
				'label' => __( 'Login form background', 'minerva-kb' ),
				'default' => '#f7f7f7',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_label_color',
				'type' => 'color',
				'label' => __( 'Login form label color', 'minerva-kb' ),
				'default' => '#999',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_input_bg',
				'type' => 'color',
				'label' => __( 'Login form input background', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_input_text_color',
				'type' => 'color',
				'label' => __( 'Login form input text color', 'minerva-kb' ),
				'default' => '#333',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_username_label_text',
				'type' => 'input_text',
				'label' => __( 'Login form username/email label text', 'minerva-kb' ),
				'default' => __( 'Username or Email Address', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_password_label_text',
				'type' => 'input_text',
				'label' => __( 'Login form password label text', 'minerva-kb' ),
				'default' => __( 'Password', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_remember_label_text',
				'type' => 'input_text',
				'label' => __( 'Login form Remember me label text', 'minerva-kb' ),
				'default' => __( 'Remember Me', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_text',
				'type' => 'input_text',
				'label' => __( 'Login button text', 'minerva-kb' ),
				'default' => __( 'Log in', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_btn_bg',
				'type' => 'color',
				'label' => __( 'Login button background', 'minerva-kb' ),
				'default' => '#F7931E',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_btn_shadow',
				'type' => 'color',
				'label' => __( 'Login button shadow', 'minerva-kb' ),
				'default' => '#e46d19',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_btn_color',
				'type' => 'color',
				'label' => __( 'Login button text color', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_show_register_link',
				'type' => 'checkbox',
				'label' => __( 'Show register button inside login form?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Control the register button display in login form', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_show_or',
				'type' => 'checkbox',
				'label' => __( 'Also show separator label between login and register?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Text between login and register buttons', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_or_text',
				'type' => 'input_text',
				'label' => __( 'Separator label text', 'minerva-kb' ),
				'default' => __( 'Or', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_register_text',
				'type' => 'input_text',
				'label' => __( 'Register button text', 'minerva-kb' ),
				'default' => __( 'Register', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_register_btn_bg',
				'type' => 'color',
				'label' => __( 'Login register button background', 'minerva-kb' ),
				'default' => '#29ABE2',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_register_btn_shadow',
				'type' => 'color',
				'label' => __( 'Register button shadow', 'minerva-kb' ),
				'default' => '#287eb1',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_register_btn_color',
				'type' => 'color',
				'label' => __( 'Register button text color', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_disable_form_styles',
				'type' => 'checkbox',
				'label' => __( 'Disable custom form and styles?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Default theme login form and style will apply', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),

			/**
			 * Floating Helper
			 */
			array(
				'id' => 'floating_helper_tab',
				'type' => 'tab',
				'label' => __( 'Floating helper', 'minerva-kb' ),
				'icon' => 'fa-sticky-note'
			),
			array(
				'id' => 'floating_helper_switch',
				'type' => 'checkbox',
				'label' => __( 'Enable floating helper?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Turn this on to enable floating helper globally', 'minerva-kb' ),
			),
			array(
				'id' => 'fh_display_title',
				'type' => 'title',
				'label' => __( 'Display options', 'minerva-kb' ),
				'description' => __( 'Configure where to display helper', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_hide_on_kb',
				'type' => 'checkbox',
				'label' => __( 'Do not display on KB pages?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Turn this on if you don\'t need helper on all KB pages', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_hide_on_pages',
				'type' => 'checkbox',
				'label' => __( 'Do not display on regular pages?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Turn this on if you don\'t need helper on regular pages', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_hide_on_pages_ids',
				'type' => 'input',
				'label' => __( 'List of page IDs (optional)', 'minerva-kb' ),
				'default' => '',
				'description' => __( 'You can specify a comma-separated list of page IDs where helper should not appear', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_hide_on_blog',
				'type' => 'checkbox',
				'label' => __( 'Do not display on blog pages?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Turn this on if you don\'t need helper on blog posts, categories, etc', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_hide_on_mobile',
				'type' => 'checkbox',
				'label' => __( 'Do not display on mobile?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Turn this on if you don\'t need helper on mobile devices', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_hide_on_tablet',
				'type' => 'checkbox',
				'label' => __( 'Do not display on tablet?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Turn this on if you don\'t need helper on tablet devices', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_hide_on_desktop',
				'type' => 'checkbox',
				'label' => __( 'Do not display on desktop?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Turn this on if you don\'t need helper on desktop devices', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_label_text',
				'type' => 'input_text',
				'label' => __( 'Helper label text', 'minerva-kb' ),
				'default' => __( 'Have questions? Search our knowledgebase.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_search_placeholder_text',
				'type' => 'input_text',
				'label' => __( 'Helper search placeholder text', 'minerva-kb' ),
				'default' => __( 'Search knowledge base', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_style_title',
				'type' => 'title',
				'label' => __( 'Style options', 'minerva-kb' ),
				'description' => __( 'Configure helper style', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_content_search_max_height',
				'type' => 'css_size',
				'label' => __( 'Helper search results height limit', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "20"),
				'units' => array('em', 'rem', 'px'),
				'description' => __( 'You can change this if you want helper to stay within some height limit', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_content_width',
				'type' => 'css_size',
				'label' => __( 'Helper content width', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "36"),
				'units' => array('em', 'rem', 'px'),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_content_bg',
				'type' => 'color',
				'label' => __( 'Helper background color', 'minerva-kb' ),
				'default' => '#4a90e2',
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_content_color',
				'type' => 'color',
				'label' => __( 'Helper text color', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_btn_icon',
				'type' => 'icon_select',
				'label' => __( 'Helper button icon', 'minerva-kb' ),
				'default' => 'fa-info',
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_btn_bg',
				'type' => 'color',
				'label' => __( 'Helper button background color', 'minerva-kb' ),
				'default' => '#4a90e2',
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_btn_color',
				'type' => 'color',
				'label' => __( 'Helper button text / icon color', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_btn_size',
				'type' => 'css_size',
				'label' => __( 'Helper button height', 'minerva-kb' ),
				'default' => array("unit" => 'px', "size" => "78"),
				'units' => array('px'),
				'description' => __( 'Floating button height', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_btn_icon_size',
				'type' => 'css_size',
				'label' => __( 'Helper button icon size', 'minerva-kb' ),
				'default' => array("unit" => 'px', "size" => "38"),
				'units' => array('px'),
				'description' => __( 'Floating button icon size', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_display_position',
				'type' => 'select',
				'label' => __( 'Helper display position', 'minerva-kb' ),
				'options' => array(
					'btm_right' => __( 'Bottom right', 'minerva-kb' ),
					'btm_left' => __( 'Bottom left', 'minerva-kb' )
				),
				'default' => 'btm_right',
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_bottom_html',
				'type' => 'textarea',
				'label' => __( 'HTML to add after helper search box', 'minerva-kb' ),
				'height' => 20,
				'width' => 80,
				'default' => __( '', 'minerva-kb' )
			),
			array(
				'id' => 'fh_show_delay',
				'type' => 'input',
				'label' => __( 'Delay before showing helper button (ms)', 'minerva-kb' ),
				'default' => 3000,
				'description' => __( 'You can specify a delay before helper icon is shown', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			/**
			 * Auto Updates
			 */
			array(
				'id' => 'auto_updates_tab',
				'type' => 'tab',
				'label' => __( 'Registration / Updates', 'minerva-kb' ),
				'icon' => 'fa-refresh'
			),
			array(
				'id' => 'auto_updates_title',
				'type' => 'title',
				'label' => __( 'Registration & Auto-Updates configuration', 'minerva-kb' ),
				'description' => __( 'To activate automatic updates you will need your purchase code from Envato', 'minerva-kb' )
			),
			array(
				'id' => 'auto_updates_switch',
				'type' => 'checkbox',
				'label' => __( 'Enable automatic check for updates?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Plugin will check for updates periodically, you will be able to run update when it is available via Plugins menu page', 'minerva-kb' ),
			),
			array(
				'id' => 'auto_updates_verification',
				'type' => 'envato_verify',
				'label' => __( 'Please, enter your Purchase Code', 'minerva-kb' ),
				'default' => '',
				'description' => __( 'Purchase code can be downloaded at Envato dashboard / Downloads / MinervaKB / Download > License certificate & purchase code.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'auto_updates_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			/**
			 * Google Analytics
			 */
			array(
				'id' => 'ga_tab',
				'type' => 'tab',
				'label' => __( 'Google Analytics', 'minerva-kb' ),
				'icon' => 'fa-line-chart'
			),
			array(
				'id' => 'ga_title',
				'type' => 'title',
				'label' => __( 'Google Analytics custom events integration', 'minerva-kb' ),
				'description' => __( 'Please note: MinervaKB does not add Google Analytics tracking code, this is usually done in theme templates. Please follow the instructions on Google Analytics tracking code page.', 'minerva-kb' )
			),
			// ok search
			array(
				'id' => 'track_search_with_results',
				'type' => 'checkbox',
				'label' => __( 'Track search with results?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Search keyword will be used as Event Label', 'minerva-kb' ),
			),
			array(
				'id' => 'ga_good_search_category',
				'type' => 'input',
				'label' => __( 'Successful search: Event category', 'minerva-kb' ),
				'default' => __( 'Knowledge Base', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_search_with_results',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_good_search_action',
				'type' => 'input',
				'label' => __( 'Successful search: Event action', 'minerva-kb' ),
				'default' => __( 'Search success', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_search_with_results',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_good_search_value',
				'type' => 'input',
				'label' => __( 'Successful search: Event value (integer, optional)', 'minerva-kb' ),
				'default' => __( '', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_search_with_results',
					'type' => 'EQ',
					'value' => true
				)
			),
			// failed search
			array(
				'id' => 'track_search_without_results',
				'type' => 'checkbox',
				'label' => __( 'Track search without results?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Search keyword will be used as Event Label', 'minerva-kb' ),
			),
			array(
				'id' => 'ga_bad_search_category',
				'type' => 'input',
				'label' => __( 'Failed search: Event category', 'minerva-kb' ),
				'default' => __( 'Knowledge Base', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_search_without_results',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_bad_search_action',
				'type' => 'input',
				'label' => __( 'Failed search: Event action', 'minerva-kb' ),
				'default' => __( 'Search fail', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_search_without_results',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_bad_search_value',
				'type' => 'input',
				'label' => __( 'Failed search: Event value (integer, optional)', 'minerva-kb' ),
				'default' => __( '', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_search_without_results',
					'type' => 'EQ',
					'value' => true
				)
			),
			//likes
			array(
				'id' => 'track_article_likes',
				'type' => 'checkbox',
				'label' => __( 'Track article likes?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'ga_like_category',
				'type' => 'input',
				'label' => __( 'Like: Event category', 'minerva-kb' ),
				'default' => __( 'Knowledge Base', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_article_likes',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_like_action',
				'type' => 'input',
				'label' => __( 'Like: Event action', 'minerva-kb' ),
				'default' => __( 'Article like', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_article_likes',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_like_label',
				'type' => 'select',
				'label' => __( 'Like: Event Label', 'minerva-kb' ),
				'options' => array(
					'article_id' => __( 'Article ID', 'minerva-kb' ),
					'article_title' => __( 'Article title', 'minerva-kb' )
				),
				'default' => 'article_id',
				'dependency' => array(
					'target' => 'track_article_likes',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_like_value',
				'type' => 'input',
				'label' => __( 'Like: Event value (integer, optional)', 'minerva-kb' ),
				'default' => __( '', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_article_likes',
					'type' => 'EQ',
					'value' => true
				)
			),
			// dislikes
			array(
				'id' => 'track_article_dislikes',
				'type' => 'checkbox',
				'label' => __( 'Track article dislikes?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'ga_dislike_category',
				'type' => 'input',
				'label' => __( 'Dislike: Event category', 'minerva-kb' ),
				'default' => __( 'Knowledge Base', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_article_dislikes',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_dislike_action',
				'type' => 'input',
				'label' => __( 'Dislike: Event action', 'minerva-kb' ),
				'default' => __( 'Article dislike', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_article_dislikes',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_dislike_label',
				'type' => 'select',
				'label' => __( 'Dislike: Event Label', 'minerva-kb' ),
				'options' => array(
					'article_id' => __( 'Article ID', 'minerva-kb' ),
					'article_title' => __( 'Article title', 'minerva-kb' )
				),
				'default' => 'article_id',
				'dependency' => array(
					'target' => 'track_article_dislikes',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_dislike_value',
				'type' => 'input',
				'label' => __( 'Dislike: Event value (integer, optional)', 'minerva-kb' ),
				'default' => __( '', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_article_dislikes',
					'type' => 'EQ',
					'value' => true
				)
			),
			// feedback
			array(
				'id' => 'track_article_feedback',
				'type' => 'checkbox',
				'label' => __( 'Track article feedback?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'ga_feedback_category',
				'type' => 'input',
				'label' => __( 'Feedback: Event category', 'minerva-kb' ),
				'default' => __( 'Knowledge Base', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_article_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_feedback_action',
				'type' => 'input',
				'label' => __( 'Feedback: Event action', 'minerva-kb' ),
				'default' => __( 'Article feedback', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_article_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_feedback_label',
				'type' => 'select',
				'label' => __( 'Feedback: Event Label', 'minerva-kb' ),
				'options' => array(
					'article_id' => __( 'Article ID', 'minerva-kb' ),
					'article_title' => __( 'Article title', 'minerva-kb' )
				),
				'default' => 'article_id',
				'dependency' => array(
					'target' => 'track_article_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_feedback_value',
				'type' => 'input',
				'label' => __( 'Feedback: Event value (integer, optional)', 'minerva-kb' ),
				'default' => __( '', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_article_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),

			/**
			 * Localization
			 */
			array(
				'id' => 'localization_tab',
				'type' => 'tab',
				'label' => __( 'Localization', 'minerva-kb' ),
				'icon' => 'fa-language'
			),
			array(
				'id' => 'localization_title',
				'type' => 'title',
				'label' => __( 'Plugin localization', 'minerva-kb' ),
				'description' => __( 'Here will be general text strings used in plugin. Section specific texts are found in appropriate sections. Alternative you can use WPML or other plugin to translate KB text fields', 'minerva-kb' )
			),

			array(
				'id' => 'articles_text',
				'type' => 'input_text',
				'label' => __( 'Article plural text', 'minerva-kb' ),
				'default' => __( 'articles', 'minerva-kb' )
			),
			array(
				'id' => 'article_text',
				'type' => 'input_text',
				'label' => __( 'Article singular text', 'minerva-kb' ),
				'default' => __( 'article', 'minerva-kb' )
			),
			array(
				'id' => 'questions_text',
				'type' => 'input_text',
				'label' => __( 'Question plural text', 'minerva-kb' ),
				'default' => __( 'questions', 'minerva-kb' )
			),
			array(
				'id' => 'question_text',
				'type' => 'input_text',
				'label' => __( 'Question singular text', 'minerva-kb' ),
				'default' => __( 'question', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_labels_title',
				'type' => 'title',
				'label' => __( 'Post type labels', 'minerva-kb' ),
				'description' => __( 'Change post type labels text', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_name',
				'type' => 'input_text',
				'label' => __( 'Post type name', 'minerva-kb' ),
				'default' => __( 'KB Articles', 'minerva-kb' ),
			),
			array(
				'id' => 'cpt_label_singular_name',
				'type' => 'input',
				'label' => __( 'Post type singular name', 'minerva-kb' ),
				'default' => __( 'KB Article', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_menu_name',
				'type' => 'input',
				'label' => __( 'Post type menu name', 'minerva-kb' ),
				'default' => __( 'Knowledge Base', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_all_articles',
				'type' => 'input',
				'label' => __( 'Post type: All articles', 'minerva-kb' ),
				'default' => __( 'All Articles', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_view_item',
				'type' => 'input',
				'label' => __( 'Post type: View item', 'minerva-kb' ),
				'default' => __( 'View Article', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_add_new_item',
				'type' => 'input',
				'label' => __( 'Post type: Add new item', 'minerva-kb' ),
				'default' => __( 'Add New Article', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_add_new',
				'type' => 'input',
				'label' => __( 'Post type: Add new', 'minerva-kb' ),
				'default' => __( 'Add New Article', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_edit_item',
				'type' => 'input',
				'label' => __( 'Post type: Edit item', 'minerva-kb' ),
				'default' => __( 'Edit Article', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_update_item',
				'type' => 'input',
				'label' => __( 'Post type: Update item', 'minerva-kb' ),
				'default' => __( 'Update Article', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_search_items',
				'type' => 'input',
				'label' => __( 'Post type: Search items', 'minerva-kb' ),
				'default' => __( 'Search Articles', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_not_found',
				'type' => 'input',
				'label' => __( 'Post type: Not found', 'minerva-kb' ),
				'default' => __( 'Not Found', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_not_found_in_trash',
				'type' => 'input',
				'label' => __( 'Post type: Not found in trash', 'minerva-kb' ),
				'default' => __( 'Not Found In Trash', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_topic_labels_title',
				'type' => 'title',
				'label' => __( 'Post type category labels', 'minerva-kb' ),
				'description' => __( 'Change post type category labels text', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_topic_label_name',
				'type' => 'input_text',
				'label' => __( 'Post type category name', 'minerva-kb' ),
				'default' => __( 'Topics', 'minerva-kb' ),
			),
			array(
				'id' => 'cpt_topic_label_add_new',
				'type' => 'input',
				'label' => __( 'Post type category: Add new', 'minerva-kb' ),
				'default' => __( 'Add New Topic', 'minerva-kb' ),
			),
			array(
				'id' => 'cpt_topic_label_new_item_name',
				'type' => 'input',
				'label' => __( 'Post type category: New item name', 'minerva-kb' ),
				'default' => __( 'New Topic', 'minerva-kb' ),
			),
			array(
				'id' => 'cpt_tag_labels_title',
				'type' => 'title',
				'label' => __( 'Post type tag labels', 'minerva-kb' ),
				'description' => __( 'Change post type tag labels text', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_tag_label_name',
				'type' => 'input',
				'label' => __( 'Post type tag name', 'minerva-kb' ),
				'default' => __( 'Tags', 'minerva-kb' ),
			),
			array(
				'id' => 'cpt_tag_label_add_new',
				'type' => 'input',
				'label' => __( 'Post type tag: Add new', 'minerva-kb' ),
				'default' => __( 'Add New Tag', 'minerva-kb' ),
			),
			array(
				'id' => 'cpt_tag_label_new_item_name',
				'type' => 'input',
				'label' => __( 'Post type tag: New item name', 'minerva-kb' ),
				'default' => __( 'New Tag', 'minerva-kb' ),
			),
			array(
				'id' => 'localization_search_title',
				'type' => 'title',
				'label' => __( 'Search labels', 'minerva-kb' )
			),
			array(
				'id' => 'search_results_text',
				'type' => 'input_text',
				'label' => __( 'Search multiple results text', 'minerva-kb' ),
				'default' => __( 'results', 'minerva-kb' )
			),
			array(
				'id' => 'search_result_text',
				'type' => 'input_text',
				'label' => __( 'Search single result text', 'minerva-kb' ),
				'default' => __( 'result', 'minerva-kb' )
			),
			array(
				'id' => 'search_no_results_text',
				'type' => 'input_text',
				'label' => __( 'Search no results text', 'minerva-kb' ),
				'default' => __( 'No results', 'minerva-kb' )
			),
			array(
				'id' => 'search_clear_icon_tooltip',
				'type' => 'input_text',
				'label' => __( 'Clear icon tooltip', 'minerva-kb' ),
				'default' => __( 'Clear search', 'minerva-kb' )
			),
			array(
				'id' => 'localization_pagination_title',
				'type' => 'title',
				'label' => __( 'Pagination labels', 'minerva-kb' )
			),
			array(
				'id' => 'pagination_prev_text',
				'type' => 'input_text',
				'label' => __( 'Previous page link text', 'minerva-kb' ),
				'default' => __( 'Previous', 'minerva-kb' )
			),
			array(
				'id' => 'pagination_next_text',
				'type' => 'input_text',
				'label' => __( 'Next page link text', 'minerva-kb' ),
				'default' => __( 'Next', 'minerva-kb' )
			),
			/**
			 * Theme compatibility
			 */
			array(
				'id' => 'compatibility_tab',
				'type' => 'tab',
				'label' => __( 'Theme options', 'minerva-kb' ),
				'icon' => 'fa-handshake-o'
			),
			array(
				'id' => 'compatibility_title',
				'type' => 'title',
				'label' => __( 'Theme compatibility tools', 'minerva-kb' ),
				'description' => __( 'MinervaKB tries to play well with most themes, but some themes need extra steps. Do not edit these settings unless you experience issues with theme templates', 'minerva-kb' )
			),
			array(
				'id' => 'font_awesome_theme_title',
				'type' => 'title',
				'label' => __( 'Font loading settings', 'minerva-kb' ),
				'description' => __( 'In case your theme loads Font Awesome, you can disable loading it from plugin', 'minerva-kb' )
			),
			array(
				'id' => 'no_font_awesome',
				'type' => 'checkbox',
				'label' => __( 'Do not load Font Awesome assets?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'compatibility_headers_title',
				'type' => 'title',
				'label' => __( 'Template headers and footers', 'minerva-kb' ),
				'description' => __( 'Most often single / category templates are used as standalone pages. But sometimes themes load them from inside other templates. In this scenario we do not need to load header and footer', 'minerva-kb' )
			),
			array(
				'id' => 'no_article_header',
				'type' => 'checkbox',
				'label' => __( 'Do not load header in article template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_article_footer',
				'type' => 'checkbox',
				'label' => __( 'Do not load footer in article template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_topic_header',
				'type' => 'checkbox',
				'label' => __( 'Do not load header in topic template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_topic_footer',
				'type' => 'checkbox',
				'label' => __( 'Do not load footer in topic template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_page_header',
				'type' => 'checkbox',
				'label' => __( 'Do not load header in page template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_page_footer',
				'type' => 'checkbox',
				'label' => __( 'Do not load footer in page template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_tag_header',
				'type' => 'checkbox',
				'label' => __( 'Do not load header in tag template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_tag_footer',
				'type' => 'checkbox',
				'label' => __( 'Do not load footer in tag template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_archive_header',
				'type' => 'checkbox',
				'label' => __( 'Do not load header in archive template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_archive_footer',
				'type' => 'checkbox',
				'label' => __( 'Do not load footer in archive template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_search_header',
				'type' => 'checkbox',
				'label' => __( 'Do not load header in search results template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_search_footer',
				'type' => 'checkbox',
				'label' => __( 'Do not load footer in search results template?', 'minerva-kb' ),
				'default' => false
			),
			/**
			 * Demo import
			 */
			array(
				'id' => 'demo_import_tab',
				'type' => 'tab',
				'label' => __( 'Demo import', 'minerva-kb' ),
				'icon' => 'fa-gift'
			),
			array(
				'id' => 'demo_import',
				'type' => 'demo_import',
				'label' => __( 'One-click Demo Import', 'minerva-kb' ),
				'default' => '',
				'description' => __( 'You can import dummy articles, topics and pages for quick testing. Press Skip if you don\'t want this tab to open by default (you will still be able to use import later)', 'minerva-kb' ),
			),
			/**
			 * Import / Export
			 */
			array(
				'id' => 'export_import_tab',
				'type' => 'tab',
				'label' => __( 'Import / Export', 'minerva-kb' ),
				'icon' => 'fa-cloud-download'
			),
			array(
				'id' => 'settings_export',
				'type' => 'export',
				'label' => __( 'Settings export. You can copy and save this content:', 'minerva-kb' ),
				'default' => '',
				'description' => __( 'NOTE: Only saved settings are exported, if you have unsaved changes you need to save them before exporting.', 'minerva-kb' ),
			),
			array(
				'id' => 'settings_import',
				'type' => 'import',
				'label' => __( 'Settings import. Paste saved settings here:', 'minerva-kb' ),
				'default' => ''
			),
		);
	}

	protected static function get_pages_options() {
		$result = array("" => __('Please select page', 'minerva-kb'));

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
			'parent' => -1,
			'exclude_tree' => '',
			'number' => '',
			'offset' => 0,
			'post_type' => 'page',
			'post_status' => 'publish'
		);

		$pages = get_pages($pages_args);

		if ($pages) {
			$result = array_reduce($pages, function($all, $page) {
				$all[$page->ID] = $page->post_title;

				return $all;
			}, $result);
		}

		return $result;
	}

	protected static function get_home_layout_options() {
		return array(
			array(
				'key' => 'search',
				'label' => __('Search', 'minerva-kb'),
				'icon' => 'fa-eye'
			),
			array(
				'key' => 'topics',
				'label' => __('Topics', 'minerva-kb'),
				'icon' => 'fa-eye'
			),
			array(
				'key' => 'tagcloud',
				'label' => __('Tag cloud', 'minerva-kb'),
				'icon' => 'fa-eye'
			),
			array(
				'key' => 'top_articles',
				'label' => __('Top articles', 'minerva-kb'),
				'icon' => 'fa-eye'
			)
		);
	}

	protected static function get_user_roles_options() {
		return array(
			'none' => __('Not restricted', 'minerva-kb'),
			'administrator' => __('Administrator', 'minerva-kb'),
			'editor' => __('Editor', 'minerva-kb'),
			'author' => __('Author', 'minerva-kb'),
			'contributor' => __('Contributor', 'minerva-kb'),
			'subscriber' => __('Subscriber', 'minerva-kb'),
		);
	}

	public static function get_topics_options() {
		$saved = self::get_saved_values();
		$category = isset($saved['article_cpt_category']) ?
			$saved['article_cpt_category'] :
			'topic'; // TODO: use separate defaults

		$options = array(
			array(
				'key' => 'recent',
				'label' => __('Recent', 'minerva-kb')
			),
			array(
				'key' => 'updated',
				'label' => __('Recently updated', 'minerva-kb')
			),
			array(
				'key' => 'top_views',
				'label' => __('Most viewed', 'minerva-kb')
			),
			array(
				'key' => 'top_likes',
				'label' => __('Most liked', 'minerva-kb')
			)
		);

		$topics = get_terms( $category, array(
			'hide_empty' => false,
		) );

		if (isset($topics) && !is_wp_error($topics) && !empty($topics)) {
			foreach ( $topics as $item ):
				array_push($options, array(
					'key' => $item->term_id,
					'label' => $item->name,
				));
			endforeach;
		}

		return $options;
	}
	
	public static function get_faq_categories_options() {
		$options = array();

		$categories = get_terms( 'mkb_faq_category', array(
			'hide_empty' => false,
		) );

		if (isset($categories) && !is_wp_error($categories) && !empty($categories)) {
			foreach ( $categories as $item ):
				array_push($options, array(
					'key' => $item->term_id,
					'label' => $item->name,
				));
			endforeach;
		}

		return $options;
	}

	public static function get_search_topics_options() {
		$saved = self::get_saved_values();
		$category = isset($saved['article_cpt_category']) ?
			$saved['article_cpt_category'] :
			'topic'; // TODO: use separate defaults

		$options = array();

		$topics = get_terms( $category, array(
			'hide_empty' => false,
		) );

		if (isset($topics) && !is_wp_error($topics) && !empty($topics)) {
			foreach ( $topics as $item ):
				array_push($options, array(
					'key' => $item->term_id,
					'label' => $item->name,
				));
			endforeach;
		}

		return $options;
	}

	/**
	 * To be used inside options method
	 * @param $key
	 */
	protected static function get_saved_option($key, $default = null) {
		$saved = self::get_saved_values();
		return isset($saved[$key]) ? $saved[$key] : $default;
	}

	/**
	 * @return array
	 */
	public static function get_home_sections_options() {
		$saved = self::get_saved_values();
		$faq_disable = isset($saved['disable_faq']) ? $saved['disable_faq'] : false;

		$options = array(
			array(
				'key' => 'search',
				'label' => __('Search', 'minerva-kb')
			),
			array(
				'key' => 'topics',
				'label' => __('Topics', 'minerva-kb')
			)
		);

		if (!$faq_disable) {
			array_push($options, array(
				'key' => 'faq',
				'label' => __('FAQ', 'minerva-kb')
			));
		}

		return $options;
	}

	public static function get_non_ui_options($options) {
		return array_filter($options, function($option) {
			return !in_array($option['type'], array(
				'tab',
				'title',
				'description',
				'code',
				'info',
				'warning',
				'demo_import',
				'export',
				'import'
			));
		});
	}

	public static function save($options) {
		self::add_wpml_string_options($options);
		update_option(self::OPTION_KEY, json_encode($options));

		// invalidate options cache
		global $minerva_kb_options_cache;
		$minerva_kb_options_cache = null;

		global $minerva_kb;
		$minerva_kb->restrict->invalidate_restriction_cache();
	}

	/**
	 * Imports previously saved settings
	 * @param $import_data
	 *
	 * @return bool
	 */
	public static function import($import_data) {
		$parse_data = null;

		try {

			$parse_data = json_decode(stripslashes_deep($import_data), true);

			if (empty($parse_data)) {
				return false;
			}

			$all_options = self::get();

			foreach($all_options as $key => $value) {
				if (isset($parse_data[$key])) {
					$all_options[$key] = $parse_data[$key];
				}
			}

			self::save($all_options);

		} catch (Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * Registers options that require translations
	 * @param $options
	 */
	private function add_wpml_string_options($options) {

		if (!function_exists ( 'icl_register_string' )) { return; }

		$all_options = self::get_options_by_id();

		foreach($options as $id => $value) {
			if (!isset($all_options[$id]) ||
			    ($all_options[$id]['type'] !== 'input_text' && $all_options[$id]['type'] !== 'textarea_text')) {
				continue;
			}

			icl_register_string(self::WPML_DOMAIN, $all_options[$id]['label'], $value);
		}
	}

	/**
	 * Translates saved values
	 * @param $options
	 *
	 * @return mixed
	 */
	private static function translate_values($options) {

		if (!function_exists( 'icl_register_string' )) {
			return $options;
		}

		$all_options = self::get_options_by_id();

		foreach($options as $id => $value) {
			if (!isset($all_options[$id]) ||
			    ($all_options[$id]['type'] !== 'input_text' && $all_options[$id]['type'] !== 'textarea_text')) {
				continue;
			}

			$options[$id] = apply_filters('wpml_translate_single_string', $value, self::WPML_DOMAIN, $all_options[$id]['label']);
		}

		return $options;
	}

	public static function save_option($key, $value) {
		$all_options = self::get();
		$all_options[$key] = $value;
		self::save($all_options);
	}

	public static function reset() {
		update_option(self::OPTION_KEY, json_encode(self::get_options_defaults()));
	}

	public static function get() {
		global $minerva_kb_options_cache;

		if (!$minerva_kb_options_cache) {
			$minerva_kb_options_cache = self::translate_values(
				wp_parse_args(self::get_saved_values(), self::get_options_defaults())
			);
		}

		return $minerva_kb_options_cache;
	}

	public static function get_saved_values() {
		$options = json_decode(get_option(self::OPTION_KEY), true);

		$options = !empty($options) ? $options : array();

		return self::normalize_values(stripslashes_deep($options));
	}

	public static function normalize_values($settings) {
		return array_map(function($value) {
			if ($value === 'true') {
				return true;
			} else if ($value === 'false') {
				return false;
			} else {
				return $value;
			}
		}, $settings);
	}

	public static function option($key) {
		$all_options = self::get();

		return isset($all_options[$key]) ? $all_options[$key] : null;
	}

	/**
	 * Detects if flush rules was called for current set of CPT slugs
	 * @return bool
	 */
	public static function need_to_flush_rules() {
		$flushed_cpt = get_option('_mkb_flushed_rewrite_cpt');
		$flushed_topic = get_option('_mkb_flushed_rewrite_topic');
		$flushed_tag = get_option('_mkb_flushed_rewrite_tag');

		$cpt_slug = self::option('cpt_slug_switch') ? self::option('article_slug') : self::option('article_cpt');
		$cpt_category_slug = self::option('cpt_category_slug_switch') ? self::option('category_slug') : self::option('article_cpt_category');
		$cpt_tag_slug = self::option('cpt_tag_slug_switch') ? self::option('tag_slug') : self::option('article_cpt_tag');

		return $cpt_slug != $flushed_cpt ||
		       $cpt_category_slug != $flushed_topic ||
		       $cpt_tag_slug != $flushed_tag;
	}

	/**
	 * Sets flush flags not to flush on every load
	 */
	public static function update_flush_flags() {
		$cpt_slug = self::option('cpt_slug_switch') ? self::option('article_slug') : self::option('article_cpt');
		$cpt_category_slug = self::option('cpt_category_slug_switch') ? self::option('category_slug') : self::option('article_cpt_category');
		$cpt_tag_slug = self::option('cpt_tag_slug_switch') ? self::option('tag_slug') : self::option('article_cpt_tag');

		update_option('_mkb_flushed_rewrite_cpt', $cpt_slug);
		update_option('_mkb_flushed_rewrite_topic', $cpt_category_slug);
		update_option('_mkb_flushed_rewrite_tag', $cpt_tag_slug);
	}

	/**
	 * Removes flags on uninstall
	 */
	public static function remove_flush_flags() {
		delete_option('_mkb_flushed_rewrite_cpt');
		delete_option('_mkb_flushed_rewrite_topic');
		delete_option('_mkb_flushed_rewrite_tag');
	}

	/**
	 * Removes all plugin data from options table
	 */
	public static function remove_data() {
		delete_option(self::OPTION_KEY);
	}
}

global $minerva_kb_options;

$minerva_kb_options = new MKB_Options();