<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2016 @KonstruktStudio
 */

/**
 * Class MKB_PageOptions
 * Page Options container
 */
class MKB_PageOptions {

	const OPTION_KEY = 'minerva-kb-options';

	public static function get_options_defaults() {
		return array_reduce(self::get_non_ui_options(), function($defaults, $option) {
			$defaults[$option["id"]] = $option["default"];
			return $defaults;
		}, array());
	}

	public static function get_options() {

		return array(
			/**
			 * General
			 */
			array(
				'id' => 'layout_tab',
				'type' => 'tab',
				'label' => __( 'Page Builder', 'minerva-kb' ),
				'icon' => 'fa-server'
			),
			array(
				'id' => 'page_editor',
				'type' => 'layout_editor',
				'label' => __( 'Page content', 'minerva-kb' ),
				'description' => __( 'Configure the content to display on page', 'minerva-kb' )
			),
			/**
			 * Second
			 */
			array(
				'id' => 'settings_tab',
				'type' => 'tab',
				'label' => __( 'Page Settings', 'minerva-kb' ),
				'icon' => 'fa-cog'
			),
			array(
				'id' => 'add_container',
				'type' => 'checkbox',
				'label' => __( 'Add container to page?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'You can add container to limit content width. Page will be fullwidth otherwise (unless theme adds custom paddings or margins)', 'minerva-kb' )
			),
			array(
				'id' => 'show_title',
				'type' => 'checkbox',
				'label' => __( 'Show page title?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'You can show or hide page title', 'minerva-kb' )
			),
			array(
				'id' => 'page_top_padding',
				'type' => 'css_size',
				'label' => __( 'Page top padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => __( 'Distance between header and page content', 'minerva-kb' )
			),
			array(
				'id' => 'page_bottom_padding',
				'type' => 'css_size',
				'label' => __( 'Page bottom padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => __( 'Distance between page content and footer', 'minerva-kb' )
			),
		);
	}

	/**
	 * TODO: move to base class
	 * @return array
	 */
	protected static function get_non_ui_options() {
		return array_filter(self::get_options(), function($option) {
			return $option['type'] !== 'tab' &&
			       $option['type'] !== 'title' &&
			       $option['type'] !== 'description' &&
			       $option['type'] !== 'code';
		});
	}

	/**
	 * Gets saved page options and normalizes them
	 * @return array
	 */
	public static function get_saved_values() {
		return self::normalize_values(json_decode(get_option(self::OPTION_KEY), true));
	}

	/**
	 * Value normalizer
	 * TODO: move to base class
	 * @param $settings
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

	/**
	 * Gets page options value
	 * @param $key
	 * @return null
	 */
	public static function option($key) {
		$settings = self::get_page_settings();

		if (isset($settings) && isset($settings[$key])) {
			return $settings[$key];
		} else {
			return null;
		}
	}

	/**
	 * Detects page builder home page
	 * @return bool
	 */
	public static function is_builder_page() {
		return (bool) get_post_meta(get_the_ID(), '_mkb_enable_home_page', true);
	}

	/**
	 * Gets saved page builder sections
	 * @return array|mixed
	 */
	public static function get_builder_sections() {
		global $post;

		$sections = array();

		if ($post) {
			$sections = get_post_meta($post->ID, '_mkb_page_sections', true);

			if (isset($sections) && !empty($sections)) {
				$sections = array_map(function($str) {
					return stripslashes_deep(json_decode($str, true));
				}, $sections);
			}
		}

		return $sections;
	}

	/**
	 * Gets page settings
	 * TODO: refactor, remove duplicate properties in page save handler
	 * @return array
	 */
	public static function get_page_settings() {
		global $post;

		$settings = array(
			"add_container" => false,
			"show_title" => false,
			"page_top_padding" => array("unit" => 'em', "size" => "0"),
			"page_bottom_padding" => array("unit" => 'em', "size" => "0")
		);

		if ($post) {
			$stored_settings = get_post_meta($post->ID, '_mkb_page_settings', true);

			if ($stored_settings) {
				$settings = wp_parse_args($stored_settings, $settings);
			}
		}

		return $settings;
	}
}