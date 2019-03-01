<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_TopicsShortcode extends KST_Shortcode implements KST_Shortcode_Interface {

	protected $ID = 'topics';
	protected $name = 'Topics';
	protected $description = 'A set of KB topics';
	protected $icon = 'fa fa-th-large';

	protected $args_map = array(
		'topics_title' => 'title',
		'topics_title_color' => 'title_color',
		'topics_title_size' => 'title_size',
		'home_topics' => 'topics',
		'home_view' => 'view',
		'home_layout' => 'columns',
		'show_articles_count' => 'show_count',
		'home_topics_show_description' => 'show_description',
		'show_all_switch' => 'show_all',
		'show_all_label' => 'show_all_label',
		'home_topics_hide_children' => 'hide_children',
		'home_topics_articles_limit' => 'articles_limit',
		'home_topics_limit' => 'limit',
		'articles_count_bg' => 'count_bg',
		'articles_count_color' => 'count_color',
		'show_topic_icons' => 'show_topic_icons',
		'show_article_icons' => 'show_article_icons',
		'article_icon' => 'article_icon',
		'topic_color' => 'topic_color',
		'force_default_topic_color' => 'force_topic_color',
		'force_default_topic_icon' => 'force_topic_icon',
		'box_view_item_bg' => 'box_item_bg',
		'box_view_item_hover_bg' => 'box_item_hover_bg',
		'topic_icon' => 'topic_icon',
		'use_topic_image' => 'use_topic_image',
		'image_size' => 'image_size',
		'topic_icon_padding_top' => 'icon_padding_top',
		'topic_icon_padding_bottom' => 'icon_padding_bottom'
	);

	/**
	 * Renders shortcode
	 * @param $atts
	 * @param string $content
	 */
	public function render($atts, $content = '') {

		// shortcode defaults
		$args = wp_parse_args($atts, $this->get_defaults());

		if ($args['limit'] == -1) { $args['limit'] = 0; }

		MKB_TemplateHelper::render_topics($this->map_params($this->args_map, $args));
	}

	/**
	 * Shortcode options
	 * @return array
	 */
	public static function get_options() {
		return array(
			array(
				'id' => 'title',
				'type' => 'input',
				'label' => __( 'Topics title', 'minerva-kb' ),
				'default' => __( 'Popular topics', 'minerva-kb' ),
				'admin_label' => true
			),
			array(
				'id' => 'title_size',
				'type' => 'input',
				'label' => __( 'Topics title font size', 'minerva-kb' ),
				'default' => __( '2em', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 2em or 20px',
				'dependency' => array(
					'target' => 'title',
					'type' => 'NEQ',
					'value' => ''
				)
			),
			array(
				'id' => 'view',
				'type' => 'image_select',
				'label' => __( 'Topics view', 'minerva-kb' ),
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
				'default' => 'box',
				'admin_label' => true
			),
			array(
				'id' => 'columns',
				'type' => 'image_select',
				'label' => __( 'Topics layout', 'minerva-kb' ),
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
				'default' => '3col',
				'admin_label' => true
			),
			array(
				'id' => 'topics',
				'type' => 'term_select',
				'label' => __( 'Select topics to display', 'minerva-kb' ),
				'default' => '',
				'tax' => MKB_Options::option('article_cpt_category'),
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
				'description' => __( 'You can leave it empty to display all recent topics. NOTE: dynamic topics only work for list view', 'minerva-kb' ),
				'admin_label' => true
			),
			array(
				'id' => 'limit',
				'type' => 'input',
				'label' => __( 'Number of topics to display', 'minerva-kb' ),
				'default' => -1,
				'description' => __( 'Used in case no specific topics are selected. You can use -1 to display all', 'minerva-kb' ),
				'admin_label' => true
			),
			array(
				'id' => 'hide_children',
				'type' => 'checkbox',
				'label' => __( 'Hide child topics?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'If you don\'t select specific topics, you can use this option to show only top-level topics', 'minerva-kb' )
			),
			array(
				'id' => 'articles_limit',
				'type' => 'input',
				'label' => __( 'Number of article to display', 'minerva-kb' ),
				'default' => 5,
				'description' => __( 'You can use -1 to display all', 'minerva-kb' ),
				'admin_label' => true
			),
			array(
				'id' => 'show_description',
				'type' => 'checkbox',
				'label' => __( 'Show description?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'view',
					'type' => 'EQ',
					'value' => 'box'
				)
			),
			array(
				'id' => 'show_all',
				'type' => 'checkbox',
				'label' => __( 'Add "Show all" link?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'show_all_label',
				'type' => 'input',
				'label' => __( 'Show all link label', 'minerva-kb' ),
				'default' => __( 'Show all', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_all',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_count',
				'type' => 'checkbox',
				'label' => __( 'Show articles count?', 'minerva-kb' ),
				'default' => true
			),

			// COLORS
			array(
				'id' => 'topic_colors_title',
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
				'id' => 'force_topic_color',
				'type' => 'checkbox',
				'label' => __( 'Force topic color (override topic custom colors)?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'By default, colors from topic settings have higher priority. You can override it with this setting', 'minerva-kb' )
			),
			array(
				'id' => 'title_color',
				'type' => 'color',
				'label' => __( 'Topics title color', 'minerva-kb' ),
				'default' => '#333333',
				'dependency' => array(
					'target' => 'title',
					'type' => 'NEQ',
					'value' => ''
				)
			),
			array(
				'id' => 'box_item_bg',
				'type' => 'color',
				'label' => __( 'Box view items background', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'view',
					'type' => 'EQ',
					'value' => 'box'
				)
			),
			array(
				'id' => 'box_item_hover_bg',
				'type' => 'color',
				'label' => __( 'Box view items hover background', 'minerva-kb' ),
				'default' => '#f8f8f8',
				'dependency' => array(
					'target' => 'view',
					'type' => 'EQ',
					'value' => 'box'
				)
			),
			array(
				'id' => 'count_bg',
				'type' => 'color',
				'label' => __( 'List view articles count background', 'minerva-kb' ),
				'default' => '#4a90e2',
				'dependency' => array(
					'target' => 'show_count',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'count_color',
				'type' => 'color',
				'label' => __( 'List view articles count color', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'show_count',
					'type' => 'EQ',
					'value' => true
				)
			),
			// ICONS
			array(
				'id' => 'topic_icons_title',
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
				'label' => __( 'Topic icon', 'minerva-kb' ),
				'default' => 'fa-list-alt',
				'description' => __( 'Note, that topic icon can be changed for each topic individually on topic edit page', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_topic_icons',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'force_topic_icon',
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
				'type' => 'input',
				'label' => __( 'Topic image size', 'minerva-kb' ),
				'default' => __( '10em', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 2em or 20px',
				'dependency' => array(
					'target' => 'show_topic_icons',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'icon_padding_top',
				'type' => 'input',
				'label' => __( 'Topic icon/image top padding', 'minerva-kb' ),
				'default' => __( '0', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 2em or 20px',
				'dependency' => array(
					'target' => 'show_topic_icons',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'icon_padding_bottom',
				'type' => 'input',
				'label' => __( 'Topic icon/image bottom padding', 'minerva-kb' ),
				'default' => __( '0', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 2em or 20px',
				'dependency' => array(
					'target' => 'show_topic_icons',
					'type' => 'EQ',
					'value' => true
				)
			),

			// ARTICLES
			array(
				'id' => 'articles_title',
				'type' => 'title',
				'label' => __( 'Articles settings', 'minerva-kb' ),
				'description' => __( 'Configure how articles list should look', 'minerva-kb' )
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
			)
		);
	}
}