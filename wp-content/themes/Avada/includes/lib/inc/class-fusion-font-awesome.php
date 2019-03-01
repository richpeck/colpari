<?php
/**
 * Fusion Font Awesome Class.
 *
 * @package Fusion-Library
 * @since 1.8.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * A collection of sanitization methods.
 */
class Fusion_Font_Awesome {

	/**
	 * Font Awesome version.
	 *
	 * @access protected
	 * @var string
	 */
	public static $fa_version = '5.7.1';

	/**
	 * Font Awesome URL.
	 *
	 * @access protected
	 * @var string
	 */
	protected static $font_base_url = null;

	/**
	 * Class constructor.
	 *
	 * @since 1.8.0
	 * @return void
	 */
	public function __construct() {
		add_filter( 'fusion_dynamic_css_final', array( $this, 'add_to_dynamic_css' ) );
	}

	/**
	 * Adds Font Awesome CSS to dynamic CSS.
	 *
	 * @param string $styles Dynamic CSS.
	 *
	 * @since 1.8.0
	 * @return string
	 */
	public function add_to_dynamic_css( $styles ) {

		if ( self::is_fa_enabled() ) {
			$styles .= $this->get_css();
		}

		return $styles;
	}

	/**
	 * Generates Font Awesome CSS based on enabled subsets.
	 *
	 * @since 1.8.0
	 * @return string
	 */
	public function get_css() {
		global $fusion_settings;

		$transient_name = 'fusion_fontawesome';
		if ( '' !== Fusion_Multilingual::get_active_language() && 'all' !== Fusion_Multilingual::get_active_language() ) {
			$transient_name .= '_' . Fusion_Multilingual::get_active_language();
		}

		$css = get_transient( $transient_name );

		if ( ! $css ) {
			$subsets = $fusion_settings->get( 'status_fontawesome' );
			$icons   = fusion_get_icons_array();

			$css .= $this->get_extras();
			$css .= ( 'local' === $fusion_settings->get( 'gfonts_load_method' ) && true === self::is_fa_pro_enabled() ) ? $this->get_local_subsets_font_face() : $this->get_subsets_font_face();

			foreach ( $icons as $icon ) {
				foreach ( $icon[1] as $icon_subsets ) {
					if ( is_array( $subsets ) && in_array( $icon_subsets, $subsets, true ) ) {
						$css .= '.' . $icon[0] . ':before{content:"\\' . $icon[2] . '"}';

						// No need to add same icon multiple times.
						break;
					}
				}
			}

			if ( '1' === $fusion_settings->get( 'fontawesome_v4_compatibility' ) ) {
				$css .= $this->get_v4_shims();
			}

			// Replace font family name if Pro is enabled.
			if ( true === self::is_fa_pro_enabled() ) {
				$css = str_replace( 'Font Awesome 5 Free', 'Font Awesome 5 Pro', $css );
			}

			set_transient( $transient_name, $css );
		}

		return $css;
	}

	/**
	 * Returns Font Awesome CSS URL, intended to be used only in backend.
	 *
	 * @since 1.8.0
	 * @return string
	 */
	public static function get_base_css_url() {

		if ( null === self::$font_base_url ) {
			self::$font_base_url = true === self::is_fa_pro_enabled() ? 'https://pro.fontawesome.com/releases/v' . self::$fa_version : FUSION_LIBRARY_URL . '/assets/fonts/fontawesome';
		}

		return self::$font_base_url;
	}

	/**
	 * Returns Font Awesome CSS URL, intended to be used only in backend.
	 *
	 * @since 1.8.0
	 * @return string
	 */
	public static function get_backend_css_url() {
		return true === self::is_fa_pro_enabled() ? self::get_base_css_url() . '/css/all.css' : self::get_base_css_url() . '/font-awesome.min.css';
	}

	/**
	 * Returns Font Awesome CSS URL, intended to be used only in backend.
	 *
	 * @since 1.8.0
	 * @return string
	 */
	public static function get_backend_shims_css_url() {
		return true === self::is_fa_pro_enabled() ? self::get_base_css_url() . '/css/v4-shims.css' : self::get_base_css_url() . '/v4-shims.min.css';
	}

	/**
	 * Checks if Font Awesome is enabled or not.
	 *
	 * @since 1.8.0
	 * @return bool
	 */
	public static function is_fa_enabled() {
		global $fusion_settings;

		return '' !== $fusion_settings->get( 'status_fontawesome' ) ? true : false;
	}

	/**
	 * Checks if Font Awesome Pro is enabled or not.
	 *
	 * @since 1.8.0
	 * @return bool
	 */
	public static function is_fa_pro_enabled() {
		global $fusion_settings;

		return '1' === $fusion_settings->get( 'status_fontawesome_pro' ) ? true : false;
	}

	/**
	 * Returns Font Awesome Font Face CSS.
	 *
	 * @since 1.8.0
	 * @return string
	 */
	protected function get_subsets_font_face() {
		global $fusion_settings;

		$css       = '';
		$subsets   = $fusion_settings->get( 'status_fontawesome' );
		$clean_url = str_replace( array( 'http://', 'https://' ), '//', self::get_base_css_url() );

		if ( is_array( $subsets ) && in_array( 'fab', $subsets, true ) ) {
			$css .= '@font-face{font-family:"Font Awesome 5 Brands";font-style:normal;font-weight:normal;src:url(' . $clean_url . '/webfonts/fa-brands-400.eot);src:url(' . $clean_url . '/webfonts/fa-brands-400.eot?#iefix) format("embedded-opentype"),url(' . $clean_url . '/webfonts/fa-brands-400.woff2) format("woff2"),url(' . $clean_url . '/webfonts/fa-brands-400.woff) format("woff"),url(' . $clean_url . '/webfonts/fa-brands-400.ttf) format("truetype"),url(' . $clean_url . '/webfonts/fa-brands-400.svg#fontawesome) format("svg")}.fab{font-family:"Font Awesome 5 Brands"}';
		}

		if ( is_array( $subsets ) && in_array( 'far', $subsets, true ) ) {
			$css .= '@font-face{font-family:"Font Awesome 5 Free";font-style:normal;font-weight:400;src:url(' . $clean_url . '/webfonts/fa-regular-400.eot);src:url(' . $clean_url . '/webfonts/fa-regular-400.eot?#iefix) format("embedded-opentype"),url(' . $clean_url . '/webfonts/fa-regular-400.woff2) format("woff2"),url(' . $clean_url . '/webfonts/fa-regular-400.woff) format("woff"),url(' . $clean_url . '/webfonts/fa-regular-400.ttf) format("truetype"),url(' . $clean_url . '/webfonts/fa-regular-400.svg#fontawesome) format("svg")}.far{font-family:"Font Awesome 5 Free";font-weight:400}';
		}

		if ( is_array( $subsets ) && in_array( 'fas', $subsets, true ) ) {
			$css .= '@font-face{font-family:"Font Awesome 5 Free";font-style:normal;font-weight:900;src:url(' . $clean_url . '/webfonts/fa-solid-900.eot);src:url(' . $clean_url . '/webfonts/fa-solid-900.eot?#iefix) format("embedded-opentype"),url(' . $clean_url . '/webfonts/fa-solid-900.woff2) format("woff2"),url(' . $clean_url . '/fa-solid-900.woff) format("woff"),url(' . $clean_url . '/webfonts/fa-solid-900.ttf) format("truetype"),url(' . $clean_url . '/webfonts/fa-solid-900.svg#fontawesome) format("svg")}.fa,.fas{font-family:"Font Awesome 5 Free";font-weight:900}';
		}

		if ( true === self::is_fa_pro_enabled() && is_array( $subsets ) && in_array( 'fal', $subsets, true ) ) {
			$css .= '@font-face{font-family:"Font Awesome 5 Pro";font-style:normal;font-weight:300;src:url(' . $clean_url . '/webfonts/fa-light-300.eot);src:url(' . $clean_url . '/webfonts/fa-light-300.eot?#iefix) format("embedded-opentype"),url(' . $clean_url . '/webfonts/fa-light-300.woff2) format("woff2"),url(' . $clean_url . '/webfonts/fa-light-300.woff) format("woff"),url(' . $clean_url . '/webfonts/fa-light-300.ttf) format("truetype"),url(' . $clean_url . '/webfonts/fa-light-300.svg#fontawesome) format("svg")}.fal{font-family:"Font Awesome 5 Pro";font-weight:300}';
		}

		return $css;
	}

	/**
	 * Returns local Font Awesome Font Face CSS.
	 *
	 * @since 1.8.0
	 * @return string
	 */
	protected function get_local_subsets_font_face() {
		global $fusion_settings;

		$subsets = $fusion_settings->get( 'status_fontawesome' );
		$css     = '';

		foreach ( $subsets as $subset ) {
			$family = new Fusion_FA_Font_Downloader( $subset );
			$css   .= $family->get_fontface_css();
		}

		return $css;
	}

	/**
	 * Returns Font Awesome Font global CSS.
	 *
	 * @since 1.8.0
	 * @return string
	 */
	protected function get_extras() {
		return '.fa,.fab,.fal,.far,.fas{-moz-osx-font-smoothing:grayscale;-webkit-font-smoothing:antialiased;display:inline-block;font-style:normal;font-variant:normal;text-rendering:auto;line-height:1}.fa-lg{font-size:1.33333em;line-height:.75em;vertical-align:-.0667em}.fa-xs{font-size:.75em}.fa-sm{font-size:.875em}.fa-1x{font-size:1em}.fa-2x{font-size:2em}.fa-3x{font-size:3em}.fa-4x{font-size:4em}.fa-5x{font-size:5em}.fa-6x{font-size:6em}.fa-7x{font-size:7em}.fa-8x{font-size:8em}.fa-9x{font-size:9em}.fa-10x{font-size:10em}.fa-fw{text-align:center;width:1.25em}.fa-ul{list-style-type:none;margin-left:2.5em;padding-left:0}.fa-ul>li{position:relative}.fa-li{left:-2em;position:absolute;text-align:center;width:2em;line-height:inherit}.fa-border{border:solid .08em #eee;border-radius:.1em;padding:.2em .25em .15em}.fa-pull-left{float:left}.fa-pull-right{float:right}.fa.fa-pull-left,.fab.fa-pull-left,.fal.fa-pull-left,.far.fa-pull-left,.fas.fa-pull-left{margin-right:.3em}.fa.fa-pull-right,.fab.fa-pull-right,.fal.fa-pull-right,.far.fa-pull-right,.fas.fa-pull-right{margin-left:.3em}.fa-spin{-webkit-animation:fa-spin 2s infinite linear;animation:fa-spin 2s infinite linear}.fa-pulse{-webkit-animation:fa-spin 1s infinite steps(8);animation:fa-spin 1s infinite steps(8)}@-webkit-keyframes fa-spin{0%{-webkit-transform:rotate(0);transform:rotate(0)}100%{-webkit-transform:rotate(360deg);transform:rotate(360deg)}}@keyframes fa-spin{0%{-webkit-transform:rotate(0);transform:rotate(0)}100%{-webkit-transform:rotate(360deg);transform:rotate(360deg)}}.fa-rotate-90{-webkit-transform:rotate(90deg);transform:rotate(90deg)}.fa-rotate-180{-webkit-transform:rotate(180deg);transform:rotate(180deg)}.fa-rotate-270{-webkit-transform:rotate(270deg);transform:rotate(270deg)}.fa-flip-horizontal{-webkit-transform:scale(-1,1);transform:scale(-1,1)}.fa-flip-vertical{-webkit-transform:scale(1,-1);transform:scale(1,-1)}.fa-flip-horizontal.fa-flip-vertical{-webkit-transform:scale(-1,-1);transform:scale(-1,-1)}:root .fa-flip-horizontal,:root .fa-flip-vertical,:root .fa-rotate-180,:root .fa-rotate-270,:root .fa-rotate-90{-webkit-filter:none;filter:none}.fa-stack{display:inline-block;height:2em;line-height:2em;position:relative;vertical-align:middle;width:2.5em}.fa-stack-1x,.fa-stack-2x{left:0;position:absolute;text-align:center;width:100%}.fa-stack-1x{line-height:inherit}.fa-stack-2x{font-size:2em}.fa-inverse{color:#fff}';
	}

	/**
	 * Returns Font Awesome Font v4 shims.
	 *
	 * @since 1.8.0
	 * @return string
	 */
	protected function get_v4_shims() {
		$wp_filesystem = Fusion_Helper::init_filesystem();

		$file_contents = $wp_filesystem->get_contents( FUSION_LIBRARY_PATH . '/assets/fonts/fontawesome/v4-shims.min.css' );

		// If it failed, try file_get_contents().
		if ( ! $file_contents ) {
			$file_contents = @file_get_contents( FUSION_LIBRARY_PATH . '/assets/fonts/fontawesome/v4-shims.min.css' );
		}

		return $file_contents;
	}

}

/* Omit closing PHP tag to avoid 'Headers already sent' issues. */
