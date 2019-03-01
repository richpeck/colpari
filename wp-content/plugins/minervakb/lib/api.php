<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB {
	/**
	 * Renders search
	 * @param $config
	 */
	public static function put_search($config) {
		MKB_TemplateHelper::render_search(self::parse_search_params($config));
	}

	/**
	 * Gets search args map
	 * @return array
	 */
	private static function get_search_args_map() {
		return array(
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
			'search_result_topic_label' => 'topic_label',
			'search_title_color' => 'title_color',
			'search_border_color' => 'border_color',
			'search_container_bg' => 'bg',
			'search_container_image_bg' => 'image_bg',

			'search_tip_color' => 'tip_color',
			'add_pattern_overlay' => 'add_pattern',
			'search_container_image_pattern' => 'pattern',
			'add_gradient_overlay' => 'add_gradient',
			'search_container_gradient_from' => 'gradient_from',
			'search_container_gradient_to' => 'gradient_to',
			'search_container_gradient_opacity' => 'gradient_opacity',
			'search_icons_left' => 'icons_left',
			'show_search_icon' => 'show_search_icon',
			'search_icon' => 'search_icon',
			'search_clear_icon' => 'clear_icon',
			'search_clear_icon_tooltip' => 'clear_icon_tooltip',


			'search_results_topic_bg' => 'topic_bg',
			'search_results_topic_color' => 'topic_color'
		);
	}

	/**
	 * Renders topics with given options
	 * Used in shortcodes
	 *
	 * @param $config
	 */
	public static function put_topics($config) {
		MKB_TemplateHelper::render_topics(self::parse_topics_params($config));
	}

	/**
	 * Wrapper for topic option
	 * @param $term
	 * @param $key
	 *
	 * @return string
	 */
	public static function topic_option($term, $key) {
		return MKB_TemplateHelper::get_topic_option($term, $key);
	}

	/**
	 * Gets topics args map
	 * Adds extra user-friendly args mapping for internal options values
	 *
	 * @return array
	 */
	private static function get_topics_args_map() {
		return array(
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
	}

	/**
	 * Maps API search config params to DB settings
	 * @param $params
	 *
	 * @return array
	 */
	private static function parse_search_params($params) {
		return self::map_params(self::get_search_args_map(), $params);
	}

	/**
	 * Maps API topics config params to DB settings
	 * @param $params
	 *
	 * @return array
	 */
	private static function parse_topics_params($params) {
		return self::map_params(self::get_topics_args_map(), $params);
	}

	/**
	 * Maps params to args map
	 * @param $args_map
	 * @param $args
	 *
	 * @return array
	 */
	private static function map_params($args_map, $args) {
		$settings = array();

		foreach($args_map as $key => $value) {
			if (isset($args[$value])) {
				$settings[$key] = $args[$value];
			}
		}

		return self::normalize_values($settings);
	}

	/**
	 * TODO: move to global helper
	 * @param $settings
	 *
	 * @return array
	 */
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
}