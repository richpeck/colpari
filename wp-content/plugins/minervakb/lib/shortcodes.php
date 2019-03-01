<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

class MinervaKB_Shortcodes {

	private $shortcodes = array();
	private $settings_helper;

	/**
	 * Constructor
	 */
	public function __construct () {

		// add Visual Composer custom fields
		$this->extend_vc();

		$this->shortcodes = array(
			new MinervaKB_SearchShortcode(),
			new MinervaKB_TopicsShortcode(),
			new MinervaKB_TopicShortcode(),
			new MinervaKB_TipShortcode(),
			new MinervaKB_InfoShortcode(),
			new MinervaKB_WarningShortcode(),
			new MinervaKB_AnchorShortcode(),
			new MinervaKB_RelatedShortcode(),
			new MinervaKB_GuestPostShortcode(),
			new MinervaKB_ArticleContentShortcode()
		);

		if (!MKB_Options::option('disable_faq')) {
			array_push($this->shortcodes, new MinervaKB_FAQShortcode());
		}

		foreach($this->shortcodes as $shortcode) {
			$shortcode->register();
		}

		add_action( 'init', array($this, 'minerva_mce_buttons'), 9999 );
	}

	public function extend_vc() {
		if ( !defined( 'WPB_VC_VERSION' ) ) {
			return;
		}

		$this->settings_helper = new MKB_SettingsBuilder(array("vc" => true));

		$script_url = MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-vc.js?v=' . MINERVA_KB_VERSION;

		vc_add_shortcode_param( 'mkb_articles_list' , array($this, 'vc_control_articles_list'), $script_url );
		vc_add_shortcode_param( 'mkb_layout_select' , array($this, 'vc_control_layout_select'), $script_url );
		vc_add_shortcode_param( 'mkb_term_select' , array($this, 'vc_control_term_select'), $script_url );
		vc_add_shortcode_param( 'mkb_css_size' , array($this, 'vc_control_css_size'), $script_url );
		vc_add_shortcode_param( 'mkb_image_select' , array($this, 'vc_control_image_select'), $script_url );
		vc_add_shortcode_param( 'mkb_checkbox' , array($this, 'vc_control_checkbox'), $script_url );
	}

	public function vc_control_articles_list ($settings, $value) {
		unset($settings["description"]);

		ob_start();
		$this->settings_helper->articles_list($value, array(
			"id" => $settings['param_name'],
			"label" => '',
		));
		return ob_get_clean();
	}

	public function vc_control_css_size ($settings, $value) {
		unset($settings["description"]);

		ob_start();
		$this->settings_helper->css_size($value, array(
			"id" => $settings['param_name'],
			"label" => '',
			"default" => isset($settings['value']) ? $settings['value'] : '',
		));
		return ob_get_clean();
	}

	public function vc_control_checkbox ($settings, $value) {
		unset($settings["description"]);

		ob_start();
		$this->settings_helper->toggle($value, array(
			"id" => $settings['param_name'],
			"label" => '',
			"default" => isset($settings['value']) ? $settings['value'] : '',
		));
		return ob_get_clean();
	}

	public function vc_control_layout_select ($settings, $value) {
		unset($settings["description"]);

		ob_start();
		$this->settings_helper->layout_select($value, array(
			"id" => $settings['param_name'],
			"label" => '',
			"options" => $settings['value'],
			"default" => isset($settings['std']) ? $settings['std'] : '',
		));

		return ob_get_clean();
	}

	public function vc_control_term_select ($settings, $value) {
		unset($settings["description"]);

		ob_start();
		$this->settings_helper->term_select($value, array(
			"id" => $settings['param_name'],
			"label" => '',
			"tax" => $settings['tax'],
			"extra_items" => isset($settings['extra_items']) ? $settings['extra_items'] : null,
			"default" => isset($settings['std']) ? $settings['std'] : '',
		));

		return ob_get_clean();
	}

	public function vc_control_image_select ($settings, $value) {
		unset($settings["description"]);

		ob_start();
		$this->settings_helper->image_select($value, array(
			"id" => $settings['param_name'],
			"label" => '',
			"options" => $settings['options'],
			"default" => isset($settings['std']) ? $settings['std'] : '',
		));

		return ob_get_clean();
	}

	/**
	 * Gets shortcode options by id
	 * @param $id
	 */
	public function get_options_for($id) {
		foreach($this->shortcodes as $shortcode) {
			if ($shortcode->get_id() == $id) {
				return $shortcode->get_options();
			}
		}

		return array();
	}

	public function minerva_mce_buttons() {
		add_filter( 'mce_external_plugins', array($this, 'add_buttons') );
		add_filter( 'mce_buttons', array($this, 'register_buttons') );
	}

	public function add_buttons( $plugin_array ) {
		$plugin_array['minervakb'] = MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-editor.js';
		return $plugin_array;
	}

	public function register_buttons( $buttons ) {
		array_push( $buttons, 'minervakb' );
		return $buttons;
	}
}
