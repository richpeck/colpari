<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_SearchShortcode extends KST_Shortcode implements KST_Shortcode_Interface {

	protected $ID = 'search';
	protected $name = 'Search';
	protected $description = 'Live KB search with custom themes';
	protected $icon = 'fa fa-search';

	protected $args_map = array(
		'search_title' => 'title',
		'search_title_size' => 'title_size',
		'search_theme' => 'theme',
		'search_min_width' => 'min_width',
		'search_container_padding_top' => 'top_padding',
		'search_container_padding_bottom' => 'bottom_padding',
		'search_placeholder' => 'placeholder',
		'search_topics' => 'topics',
		'disable_autofocus' => 'no_focus',
		'show_search_tip' => 'show_tip',
		'search_tip' => 'tip',
		'show_topic_in_results' => 'show_topic',
		'search_results_multiline' => 'results_multiline',
		'search_result_topic_label' => 'topic_label',
		'search_title_color' => 'title_color',
		'search_border_color' => 'border_color',
		'search_container_bg' => 'bg',
		'search_container_image_bg' => 'image_bg',
		'add_gradient_overlay' => 'add_gradient',
		'search_container_gradient_from' => 'gradient_from',
		'search_container_gradient_to' => 'gradient_to',
		'search_container_gradient_opacity' => 'gradient_opacity',
		'add_pattern_overlay' => 'add_pattern',
		'search_container_image_pattern' => 'pattern',
		'search_container_image_pattern_opacity' => 'pattern_opacity',
		'search_tip_color' => 'tip_color',
		'search_results_topic_bg' => 'topic_bg',
		'search_results_topic_color' => 'topic_color',
		'search_results_topic_use_custom' => 'topic_custom_colors',
		'search_icons_left' => 'icons_left',
		'show_search_icon' => 'show_search_icon',
		'search_icon' => 'search_icon',
		'search_clear_icon' => 'clear_icon'
	);

	public function render($atts, $content = '') {
		// shortcode defaults
		$args = wp_parse_args($atts, $this->get_defaults());

		// legacy parameters
		if (isset($args['topic_ids'])) {
			$args['topics'] = $args['topic_ids'];
		}

		MKB_TemplateHelper::render_search($this->map_params($this->args_map, $args));
	}

	/**
	 * Returns all shortcode options
	 * @return array
	 */
	public static function get_options() {
		return array(
			array(
				'id' => 'title',
				'type' => 'input',
				'label' => __( 'Search title', 'minerva-kb' ),
				'default' => '',
				'admin_label' => true
			),
			array(
				'id' => 'title_size',
				'type' => 'input',
				'label' => __( 'Search title font size', 'minerva-kb' ),
				'default' => __( '3em', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 3em or 20px',
				'dependency' => array(
					'target' => 'title',
					'type' => 'NEQ',
					'value' => ''
				)
			),
			array(
				'id' => 'theme',
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
				'default' => 'clean',
				'description' => __( 'Use predefined styles for search bar', 'minerva-kb' ),
				'admin_label' => true
			),
			array(
				'id' => 'min_width',
				'type' => 'input',
				'label' => __( 'Search input minimum width', 'minerva-kb' ),
				'default' => __( '38em', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 40em or 300px. em are better for mobile devices'
			),
			array(
				'id' => 'top_padding',
				'type' => 'input',
				'label' => __( 'Search container top padding', 'minerva-kb' ),
				'default' => __( '2em', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 3em or 50px'
			),
			array(
				'id' => 'bottom_padding',
				'type' => 'input',
				'label' => __( 'Search container bottom padding', 'minerva-kb' ),
				'default' => __( '1em', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 3em or 50px'
			),
			array(
				'id' => 'placeholder',
				'type' => 'input',
				'label' => __( 'Search placeholder', 'minerva-kb' ),
				'default' => __( 'ex.: Installation', 'minerva-kb' ),
				'admin_label' => true
			),
			array(
				'id' => 'topics',
				'type' => 'term_select',
				'label' => __( 'Optional: you can limit search to specific topics', 'minerva-kb' ),
				'default' => '',
				'tax' => MKB_Options::option('article_cpt_category'),
				'description' => __( 'You can leave it empty to search all topics (default).', 'minerva-kb' )
			),
			array(
				'id' => 'no_focus',
				'type' => 'checkbox',
				'label' => __( 'Disable search field autofocus?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'show_tip',
				'type' => 'checkbox',
				'label' => __( 'Show search tip?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'tip',
				'type' => 'input',
				'label' => __( 'Search tip (under the input)', 'minerva-kb' ),
				'default' => __( 'Tip: Use arrows to navigate results, ESC to focus search input', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_tip',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_topic',
				'type' => 'checkbox',
				'label' => __( 'Show topic in results?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'results_multiline',
				'type' => 'checkbox',
				'label' => __( 'Allow multiline titles in results?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'By default, results are fit in one line. You can change this to allow multiline titles', 'minerva-kb' )
			),
			array(
				'id' => 'topic_label',
				'type' => 'input',
				'label' => __( 'Search result topic label', 'minerva-kb' ),
				'default' => __( 'Topic', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_topic',
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
				'id' => 'title_color',
				'type' => 'color',
				'label' => __( 'Search title color', 'minerva-kb' ),
				'default' => '#333333',
				'dependency' => array(
					'target' => 'title',
					'type' => 'NEQ',
					'value' => ''
				)
			),
			array(
				'id' => 'border_color',
				'type' => 'color',
				'label' => __( 'Search wrap border color (not in all themes)', 'minerva-kb' ),
				'default' => '#ffffff'
			),
			array(
				'id' => 'bg',
				'type' => 'color',
				'label' => __( 'Search container background color', 'minerva-kb' ),
				'default' => '#ffffff',
				'admin_label' => true
			),
			array(
				'id' => 'image_bg',
				'type' => 'media',
				'label' => __( 'Search container background image URL (optional)', 'minerva-kb' ),
				'default' => ''
			),
			array(
				'id' => 'add_gradient',
				'type' => 'checkbox',
				'label' => __( 'Add gradient overlay?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'gradient_from',
				'type' => 'color',
				'label' => __( 'Search container gradient from', 'minerva-kb' ),
				'default' => '#00c1b6',
				'dependency' => array(
					'target' => 'add_gradient',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'gradient_to',
				'type' => 'color',
				'label' => __( 'Search container gradient to', 'minerva-kb' ),
				'default' => '#136eb5',
				'dependency' => array(
					'target' => 'add_gradient',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'gradient_opacity',
				'type' => 'input',
				'label' => __( 'Search container background gradient opacity', 'minerva-kb' ),
				'default' => 1,
				'description' => __( 'Use any CSS opacity value, for example 1 or 0.7', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_gradient',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'add_pattern',
				'type' => 'checkbox',
				'label' => __( 'Add pattern overlay?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'pattern',
				'type' => 'media',
				'label' => __( 'Search container background pattern image URL (optional)', 'minerva-kb' ),
				'default' => '',
				'dependency' => array(
					'target' => 'add_pattern',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'pattern_opacity',
				'type' => 'input',
				'label' => __( 'Search container background pattern opacity', 'minerva-kb' ),
				'default' => 1,
				'description' => __( 'Use any CSS opacity value, for example 1 or 0.7. You can also use transparent .png and set opacity to 1', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_pattern',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'tip_color',
				'type' => 'color',
				'label' => __( 'Search tip color', 'minerva-kb' ),
				'default' => '#888888',
				'dependency' => array(
					'target' => 'show_tip',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_bg',
				'type' => 'color',
				'label' => __( 'Search results topic background', 'minerva-kb' ),
				'default' => '#4a90e2',
				'dependency' => array(
					'target' => 'show_topic',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_color',
				'type' => 'color',
				'label' => __( 'Search results topic color', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'show_topic',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_custom_colors',
				'type' => 'checkbox',
				'label' => __( 'Use custom topic colors in search results?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'show_topic',
					'type' => 'EQ',
					'value' => true
				)
			),
			// ICONS
			array(
				'id' => 'icons_title',
				'type' => 'title',
				'label' => __( 'Search icons', 'minerva-kb' ),
				'description' => __( 'Configure search icons', 'minerva-kb' )
			),
			array(
				'id' => 'icons_left',
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
				'id' => 'clear_icon',
				'type' => 'icon_select',
				'label' => __( 'Search clear icon', 'minerva-kb' ),
				'default' => 'fa-times-circle'
			)
		);
	}

	public function vc_params () {
		return array(
			array(
				'type' => 'attach_image',
				'heading' => __( 'Search container background image URL (optional)', 'minerva-kb' ),
				'param_name' => 'image_bg',
				'value' => '',
				'admin_label' => true,
			),
			array(
				'type' => 'attach_image',
				'heading' => __( 'Search container background pattern image URL (optional)', 'minerva-kb' ),
				'param_name' => 'pattern',
				'value' => '',
				'dependency' => array(
					'target' => 'add_pattern',
					'type' => 'EQ',
					'value' => true
				)
			),
		);
	}
}