<?php
/**
 * Free Live Chat And Web Form Widget
 *
 * @link        https://www.bitrix24.com
 * @version     1.0.0
 * @package     Bitrix24Forms
 *
 * Plugin Name: Free Live Chat And Web Form Widget
 * Description: This free Bitrix24 widget lets you insert live chat, call back request and various web forms into your website. All information from chat and forms is automatically imported into free Bitrix24 CRM. The widget supports up to 12 agents absolutely free, is easily customizable, and works on mobile, web and desktop apps.
 * Plugin URI: https://www.bitrix24.com
 * Version: 1.0.0
 * Author: Bitrix24
 * Author URI: https://www.bitrix24.com
 * Text Domian: bitrix24_forms
 * Domain Path: /languages/
 */

class Bitrix24Forms {

	private $settingsPage = 'bitrix24_forms_page';//settings page
	private $settingsGroup = 'bitrix24_forms_group';//settings group
	private $settingsName = 'bitrix24_forms';//settings name (array options)

	/*
	 * Set hooks.
	 */
	public function __construct() {
		add_filter('the_content', array($this, 'content_process'));
		add_filter('the_content', array($this, 'check_content'));

		add_action('wp_footer',array($this, 'page_process'));
		add_action('admin_menu', array($this, 'admin_settings_menu'));
		add_action('admin_init', array($this, 'admin_settings'));
	}

	/*
	 * Admin menu item.
	 */
	public function admin_settings_menu() {
		add_submenu_page(
			'options-general.php',
			__('Bitrix24', 'bitrix24_forms'),
			__('Bitrix24', 'bitrix24_forms'),
			'activate_plugins',
			$this->settingsPage,
			array($this, 'render_settings_page')
		);
	}

	/*
	 * Admin actions for settings.
	 */
	public function admin_settings() {

		register_setting($this->settingsGroup, $this->settingsName, array($this, 'sanitize'));
		add_settings_section($this->settingsName, '', array(&$this, 'display_settings_section') , $this->settingsPage);

		$settings = array(
			array('b24f_show_chat', __('Enable chat widget', 'bitrix24_forms'), 'checkbox'),
			array('b24f_chat_code', __('Widget code', 'bitrix24_forms'), 'textarea'),
			array('b24c_enable', __('Enable CRM connector', 'bitrix24_forms'), 'checkbox'),
			array('b24c_portal', __('Connector URL', 'bitrix24_forms'), 'text'),
			array('b24c_currency', __('Internetional currency code', 'bitrix24_forms'), 'text'),
		);

		foreach ($settings as $item) {
			add_settings_field($item[0], $item[1], array(&$this, 'display_settings'), $this->settingsPage, $this->settingsName, $item);
		}
	}

	/*
	 * Settings form render.
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h2><?php echo get_admin_page_title()?></h2>
			<form method="post" action="options.php">
				<?php
					settings_fields($this->settingsGroup);
					do_settings_sections($this->settingsPage);
					submit_button();
				?>
			</form>
		</div>
		<?
	}

	/*
	 * Settings section.
	 */
	public function display_settings_section() {
		echo '';
	}

	/*
	 * One item settings.
	 * @param array (id, title, type)
	 */
	public function display_settings($param) {

		static $option = null;
		static $optionName;

		if ($option === null) {
			$optionName = $this->settingsName;
			$option = get_option($optionName);
		}

		$id = $param[0];
		$type = $param[2];
		$value = esc_attr(stripslashes($option[$id]));

		if ($type == 'checkbox') {
			echo '<input type="checkbox" id="', $id, '" name="', $optionName, '[', $id, ']" value="1"', ($value ? ' checked="checked"' : ''), ' />';
		} elseif ($type == 'textarea') {
			echo '<textarea rows="5" cols="50" id="', $id, '" name="', $optionName, '[', $id, ']">', $option[$id], '</textarea>';
		} else {
			echo '<input class="regular-text" type="text" id="', $id, '" name="', $optionName, '[', $id, ']" value="', $value, '" />';
		}
	}

	/*
	 * Before save settings.
	 */
	public function sanitize($options) {
		foreach ($options as $name => &$val) {
			$val = strip_tags($val);
		}
		return $options;
	}

	/*
	 * On page load.
	 */
	public function page_process() {
		if (!is_admin()) {
			$option = get_option($this->settingsName);
			if (B24Gate::getConfig('b24f_show_chat')) {
				$code = trim(B24Gate::getConfig('b24f_chat_code'));
				if (strpos($code, '<script') !== 0) {
					$code = '<script data-skip-moving="true">' . $code . '</script>';
				}
				echo $code;
			}
		}
	}

	/*
	 * Filter content for replace link to form.
	 */
	public function content_process($content) {
		if (preg_match_all('#https://([^/]+)/pub/form/([\d]+)_[^/]+/([^/]+)/#i', $content, $matches)) {
			foreach ($matches[0] as $i => $match) {
				$content = str_replace($match,
										$this->process_get_form_js(
													$matches[1][$i],
													$matches[3][$i],
													$matches[2][$i]
												),
										$content);
			}
		}
		return $content;
    }

	/*
	 * Get JS for form.
	 */
	private function process_get_form_js($portal, $code, $id) {
		static $count = 0;
		static $lang = null;
		if ($lang === null) {
			$lang = get_locale();
			if (strpos($lang, '_') !== false) {
				list($lang) = explode('_', $lang);
			}
		}
		$count++;
		return '<div id="bx24_form_inline_' . $count . '"></div>
				<script id="bx24_form_inline" data-skip-moving="true">
					(function(w,d,u,b){w[\'Bitrix24FormObject\']=b;w[b] = w[b] || function(){arguments[0].ref=u;
					(w[b].forms=w[b].forms||[]).push(arguments[0])};
					if(w[b][\'forms\']) return;
					s=d.createElement(\'script\');r=1*new Date();s.async=1;s.src=u+\'?\'+r;
					h=d.getElementsByTagName(\'script\')[0];h.parentNode.insertBefore(s,h);
					})(window,document,\'https://' . $portal . '/bitrix/js/crm/form_loader.js\',\'b24form\');
					b24form({"id":"' . $id . '","lang":"' . $lang . '","sec":"' . $code . '","type":"inline",
					"node": document.getElementById(\'bx24_form_inline_' . $count . '\')});
				</script>';
	}

	/**************************************************************************
	 * Connector.
	 **************************************************************************
	 */

	private function processing_require() {
		if (B24Gate::getConfig('b24c_enable')) {
			require('bitrix24_processing.php');
			require('bitrix24_class.php');
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check content for special markers.
	 * @param string $content
	 * @return string
	 */
	public function check_content($content) {
		if (
			strpos($content, '[woocommerce_checkout]') !== false &&
			isset($_REQUEST['order-received'])
		) {
			if ($this->processing_require()) {
				Bitrix24Processing::processing_woocommerce($_REQUEST['order-received']);
			}
		}
		elseif (
			strpos($content, '[transactionresults]') !== false &&
			isset($_REQUEST['sessionid'])
		) {
			if ($this->processing_require()) {
				Bitrix24Processing::processing_ecommerce($_REQUEST['sessionid']);
			}
		}
		elseif (
			strpos($content, '[edd_receipt]') !== false
		) {
			if ($this->processing_require()) {
				Bitrix24Processing::processing_digital_downloads();
			}
		}
		return $content;
	}


}

new Bitrix24Forms;






/**
 * Bitrix24 connector's gate.
 *
 * @version     1.0.0
 * @author      Bitrix24
 * @copyright   2016 Bitrix24
 * @link        https://bitrix24.com
 */
class B24Gate
{
	/**
	 * Save config.
	 * @param string $name
	 * @param string $value
	 */
	public static function saveConfig($name, $value)
	{
		$option = (array)get_option('bitrix24_forms');
		$option[$name] = $value;
		update_option('bitrix24_forms', $option);
	}

	/**
	 * Get config.
	 * @param string $name
	 * @return string|boolean
	 */
	public static function getConfig($name)
	{
		static $option = null;

		if ($option === null)
		{
			$option = (array)get_option('bitrix24_forms');
		}

		if (isset($option[$name]))
		{
			return $option[$name];
		}

		return false;
	}

	/**
	 * Get title of connector.
	 * @return string
	 */
	public static function getConnectorName()
	{
		return  ($val = get_option('blogname')) ? $val : 'Wordpress';
	}

	/**
	 * Get id of connector.
	 * @return string
	 */
	public static function getConnectorId()
	{
		return 'WORDPRESS';
	}

	/**
	 * Get host of connector.
	 * @return string
	 */
	public static function getConnectorHost()
	{
		return  ($val = get_option('siteurl')) ? $val : 'https://localhost';
	}
}