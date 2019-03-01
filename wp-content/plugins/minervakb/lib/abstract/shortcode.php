<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

/**
 * Interface KST_EditScreen_Interface
 * Common WP Dashboard Edit Screen with meta boxes
 */
interface KST_Shortcode_Interface {

	function render($atts, $content = '');

	function get_html($atts, $content = '');

	static function get_options();

}

/**
 * Base class for all shortcodes
 * Class KST_Shortcode
 */
abstract class KST_Shortcode {

	// shortcode prefix
	protected $PREFIX = 'mkb-';

	protected $ID = '';
	protected $name = '';
	protected $description = '';
	protected $icon = '';
	protected $has_content = false;

	// args map array
	protected $args_map = array();

	abstract public function render($atts, $content = '');

	// just a dummy method
	public static function get_options() {
		return array();
	}

	/**
	 * Gets shortcode html
	 * @param $atts
	 *
	 * @return string
	 */
	final public function get_html($atts, $content = '') {
		ob_start();
		?><div class="mkb-shortcode-container"><?php
		$this->render($atts, $content);
		?></div><?php
		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Registers a shortcode for WP
	 */
	final public function register() {
		add_shortcode( $this->PREFIX . $this->ID, array($this, 'get_html'));

		if ( defined( 'WPB_VC_VERSION' ) && $this->icon) {
			add_action( 'init', array( $this, 'register_vc_params' ) );
		} else if ( defined( 'WPB_VC_VERSION' ) && method_exists($this, 'map_vc')) {
			add_action( 'init', array( $this, 'map_vc' ) );
		}
	}

	final public function register_vc_params() {
		vc_map( array(
			"name" => $this->name,
			"description" => $this->description,
			"base" => $this->PREFIX . $this->ID,
			"class" => "",
			"controls" => "full",
			"icon" => $this->icon,
			"category" => 'MinervaKB',
			"params" => $this->vc_params_adapter(
				$this->vc_params()
			)
		));
	}

	final protected function vc_params_adapter($custom_params = array()) {
		$params = array();

		// Minerva types => vc types
		$types_map = array(
			"input" => "textfield",
			"css_size" => "mkb_css_size",
			"checkbox" => "mkb_checkbox",
			"select" => "dropdown",
			"image_select" => "mkb_image_select",
			"icon_select" => "iconpicker",
			"color" => "colorpicker",
			"articles_list" => "mkb_articles_list",
			"layout_select" => "mkb_layout_select",
			"term_select" => "mkb_term_select",
		);

		// some fields need to be skipped
		$skip_types = array(
			"title"
		);

		// equivalent fields map
		$fields_map = array(
			"id" => "param_name",
			"label" => "heading",
			"admin_label" => "admin_label",
			"description" => "description"
		);

		// add content to appropriate shortcodes
		if ($this->has_content) {
			array_push($params, array(
				"type" => "textarea_html",
				"holder" => "div",
				"class" => "",
				"heading" => __("Content", 'minerva-kb'),
				"param_name" => "content",
				"value" => __("Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.", 'minerva-kb'),
				"description" => __("Edit shortcode content.", 'minerva-kb')
			));
		}

		// parse options
		$options = $this->get_options();

		if (empty($options)) {
			// some shortcodes have only content
			return $params;
		}

		foreach($options as $option) {
			// skip descriptive types
			if (in_array($option["type"], $skip_types)) {
				continue;
			}

			$id = $option["id"];

			// first, search for custom defined params from shortcode array
			$param_in_custom = array_values(array_filter($custom_params, function($param) use ($id) {
				return $param["param_name"] == $id;
			}));

			if (isset($param_in_custom[0])) {

				if (isset($param_in_custom[0]['dependency'])) {
					$dep = $param_in_custom[0]['dependency'];

					if (($dep["type"] == "NEQ" && $dep["value"] == '') ||
					    ($dep["type"] == "EQ" && $dep["value"] == true)) {
						$param_in_custom[0]['dependency'] = array(
							'element' => $dep["target"],
							'not_empty' => true
						);
					} else if ($dep["type"] == "EQ" && $dep["value"] != true) {
						$param_in_custom[0]['dependency'] = array(
							'element' => $dep["target"],
							'value' => array($dep["value"])
						);
					}
				}

				array_push($params, $param_in_custom[0]);
				continue;
			}

			if (!isset($types_map[$option["type"]])) {
				// unknown type, skip
				continue;
			}

			$param = array(
				"type" => $types_map[$option["type"]]
			);

			// next, map equivalent fields directly
			foreach($option as $key => $value) {

				// custom iconpicker settings
				if ($param["type"] == 'iconpicker') {
					$param["settings"] = array(
						'emptyIcon' => false,
						'type' => 'fontawesome',
						'iconsPerPage' => 200,
					);
				}

				if (isset($fields_map[$key])) {
					$param[$fields_map[$key]] = $value;
				}

				switch ($key){
					case "default":
						if ($param["type"] == 'dropdown') {
							$param["std"] = $value;
						} else if ($option["type"] == "css_size") {
							$param["value"] = $value["size"] . $value["unit"];
						} else if ($param["type"] == "iconpicker") {
							$param["value"] = 'fa ' . $value;
						} else {
							$param["value"] = $value;
						}
						break;

					case "options":
						if ($option["type"] == "select") {
							$param["value"] = array_flip($value);
						} else if ($option["type"] == "image_select") {
							$param["options"] = $value;
						} else if ($option["type"] == "layout_select") {
							$param["value"] = $value;
						} else if ($option["type"] == "term_select") {
							$param["value"] = $value;
						}
						break;

					case "tax":
						if ($option["type"] == "term_select") {
							$param["tax"] = $value;
						}
						break;

					case "extra_items":
						if ($option["type"] == "term_select") {
							$param["extra_items"] = $value;
						}
						break;

					case "dependency":
						if (($value["type"] == "NEQ" && $value["value"] == '') ||
						    ($value["type"] == "EQ" && $value["value"] == true)) {
							$param["dependency"] = array(
								'element' => $value["target"],
								'not_empty' => true
							);
						} else if ($value["type"] == "EQ" && $value["value"] != true) {
							$param["dependency"] = array(
								'element' => $value["target"],
								'value' => array($value["value"])
							);
						}
						break;

					default:
						break;
				}
			}

			array_push($params, $param);
		}

		return $params;
	}

	public function vc_params () {
		return array();
	}

	/**
	 * Gets shortcode id
	 * @return string
	 */
	final public function get_id() {
		return $this->ID;
	}

	/**
	 * Maps user friendly params to real method properties
	 * @param $args_map
	 * @param $args
	 *
	 * @return array
	 */
	final protected function map_params($args_map, $args) {
		$settings = array();

		foreach($args_map as $key => $value) {
			if (isset($args[$value])) {
				$settings[$key] = $args[$value];
			}
		}

		return $this->normalize_values($settings);
	}

	/**
	 * Normalizes settings
	 * @param $settings
	 *
	 * @return array
	 */
	final protected function normalize_values($settings) {
		foreach($settings as $id => $value) {
			if ($value === 'true' || $value === 'on') {
				$settings[$id] = true;
			} else if ($value === 'false' || $value === 'off') {
				$settings[$id] = false;
			} else if (is_string($value) && strpos($value, 'fa ') !== false) {
				$settings[$id] = str_replace('fa ', '', $value);
			} else if (in_array($id, array('search_container_image_bg', 'search_container_image_pattern'), true)) {
				$settings[$id] = json_encode(array(
					'isUrl' => !is_numeric($value),
					'img' => $value
				));
			}
		}

		return $settings;
	}

	/**
	 * Gets shortcode defaults
	 */
	final protected function get_defaults() {
		return array_reduce(MKB_Options::get_non_ui_options($this->get_options()),
			function($store, $option) {
				$store[$option['id']] = $option['default'];
				return $store;
			}, array());
	}
}