<?php
/**
 * Enqueues scripts and styles.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.0.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Handle enqueueing scrips.
 */
class Avada_Scripts {

	/**
	 * The theme version.
	 *
	 * @static
	 * @access private
	 * @since 5.0.0
	 * @var string
	 */
	private static $version;

	/**
	 * The CSS-compiling mode.
	 *
	 * @access private
	 * @since 5.1.5
	 * @var string
	 */
	private $compiler_mode;

	/**
	 * The media-query assets.
	 *
	 * @static
	 * @access private
	 * @since 5.6
	 * @var array
	 */
	private static $media_query_assets = array();

	/**
	 * The class construction
	 *
	 * @access public
	 */
	public function __construct() {
		self::$version = Avada::get_theme_version();

		$dynamic_css_obj     = Fusion_Dynamic_CSS::get_instance();
		$this->compiler_mode = ( method_exists( $dynamic_css_obj, 'get_mode' ) ) ? $dynamic_css_obj->get_mode() : $dynamic_css_obj->mode;

		if ( ! is_admin() && ! in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) ) {
			add_action( 'wp', array( $this, 'wp_action' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
			add_action( 'script_loader_tag', array( $this, 'add_async' ), 10, 2 );

			// This is added with a priority of 999 because it has to run after all other scripts have been added.
			add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_scripts' ), 999 );
		}

		if ( class_exists( 'WooCommerce' ) ) {
			add_filter( 'woocommerce_enqueue_styles', array( $this, 'remove_woo_scripts' ) );
		}

		add_filter( 'fusion_dynamic_css_final', array( $this, 'combine_stylesheets' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_css' ), 999 );
		add_action( 'admin_head', array( $this, 'admin_styles' ) );

		// Handle media-query styles.
		$this->add_media_query_styles();
		$this->combine_media_query_files();
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_media_query_styles' ), 900 );
		add_filter( 'fusion_dynamic_css_final', array( $this, 'compile_media_query_styles' ), 999 );

		// Disable emojis script.
		add_action( 'init', array( $this, 'disable_emojis' ) );
	}

	/**
	 * A method that runs on 'wp'.
	 *
	 * @access public
	 * @since 5.1.0
	 * @return void
	 */
	public function wp_action() {

		$this->enqueue_scripts();
		$this->localize_scripts();

	}

	/**
	 * Adds our scripts using Fusion_Dynamic_JS.
	 *
	 * @access protected
	 * @since 5.1.0
	 * @return void
	 */
	protected function enqueue_scripts() {

		global $wp_styles, $woocommerce, $fusion_library;
		$multilingual = $fusion_library->multilingual;

		$page_id = Avada()->fusion_library->get_page_id();

		$js_folder_suffix = '/assets/min/js';
		$js_folder_url    = Avada::$template_dir_url . $js_folder_suffix;
		$js_folder_path   = Avada::$template_dir_path . $js_folder_suffix;

		$privacy_options = Avada()->privacy_embeds->get_options();

		$scripts = array(
			array(
				'bootstrap-scrollspy',
				$js_folder_url . '/library/bootstrap.scrollspy.js',
				$js_folder_path . '/library/bootstrap.scrollspy.js',
				array( 'jquery' ),
				'3.3.2',
				true,
			),
			array(
				'avada-comments',
				$js_folder_url . '/general/avada-comments.js',
				$js_folder_path . '/general/avada-comments.js',
				array( 'jquery' ),
				self::$version,
				true,
			),
			array(
				'avada-general-footer',
				$js_folder_url . '/general/avada-general-footer.js',
				$js_folder_path . '/general/avada-general-footer.js',
				array( 'jquery' ),
				self::$version,
				true,
			),
			array(
				'avada-quantity',
				$js_folder_url . '/general/avada-quantity.js',
				$js_folder_path . '/general/avada-quantity.js',
				array( 'jquery' ),
				self::$version,
				true,
			),
			array(
				'avada-scrollspy',
				$js_folder_url . '/general/avada-scrollspy.js',
				$js_folder_path . '/general/avada-scrollspy.js',
				( ! is_page_template( 'blank.php' ) && 'no' != fusion_get_page_option( 'display_header', $page_id ) ) ? array( 'avada-header', 'fusion-waypoints', 'bootstrap-scrollspy' ) : array( 'fusion-waypoints', 'bootstrap-scrollspy' ),
				self::$version,
				true,
			),
			array(
				'avada-select',
				$js_folder_url . '/general/avada-select.js',
				$js_folder_path . '/general/avada-select.js',
				array( 'jquery' ),
				self::$version,
				true,
			),
			array(
				'avada-sidebars',
				$js_folder_url . '/general/avada-sidebars.js',
				$js_folder_path . '/general/avada-sidebars.js',
				array( 'jquery', 'modernizr' ),
				self::$version,
				true,
			),
			array(
				'jquery-sticky-kit',
				$js_folder_url . '/library/jquery.sticky-kit.js',
				$js_folder_path . '/library/jquery.sticky-kit.js',
				array( 'jquery' ),
				self::$version,
				true,
			),
			array(
				'avada-tabs-widget',
				$js_folder_url . '/general/avada-tabs-widget.js',
				$js_folder_path . '/general/avada-tabs-widget.js',
				array( 'jquery' ),
				self::$version,
				true,
			),
		);

		// Conditional scripts.
		$available_languages = $multilingual->get_available_languages();
		if ( ! empty( $available_languages ) ) {
			$scripts[] = array(
				'avada-wpml',
				$js_folder_url . '/general/avada-wpml.js',
				$js_folder_path . '/general/avada-wpml.js',
				array( 'jquery' ),
				self::$version,
				true,
			);
		}
		if ( $privacy_options['privacy_embeds'] || $privacy_options['privacy_bar'] || Avada()->settings->get( 'slidingbar_widgets' ) ) {
			$scripts[] = array(
				'avada-container-scroll',
				$js_folder_url . '/general/avada-container-scroll.js',
				$js_folder_path . '/general/avada-container-scroll.js',
				array( 'jquery' ),
				self::$version,
				true,
			);
		}
		if ( is_page_template( 'side-navigation.php' ) ) {
			$scripts[] = array(
				'avada-side-nav',
				$js_folder_url . '/general/avada-side-nav.js',
				$js_folder_path . '/general/avada-side-nav.js',
				array( 'jquery', 'jquery-hover-intent' ),
				self::$version,
				true,
			);
		}
		if ( ! is_page_template( 'blank.php' ) && 'no' != fusion_get_page_option( 'display_header', $page_id ) ) {
			$scripts[] = array(
				'avada-header',
				$js_folder_url . '/general/avada-header.js',
				$js_folder_path . '/general/avada-header.js',
				array( 'modernizr', 'jquery', 'jquery-easing' ),
				self::$version,
				true,
			);
			$scripts[] = array(
				'avada-menu',
				$js_folder_url . '/general/avada-menu.js',
				$js_folder_path . '/general/avada-menu.js',
				array( 'modernizr', 'jquery', 'avada-header' ),
				self::$version,
				true,
			);
		}
		if ( Avada()->settings->get( 'status_totop' ) || Avada()->settings->get( 'status_totop_mobile' ) ) {
			$scripts[] = array(
				'jquery-to-top',
				$js_folder_url . '/library/jquery.toTop.js',
				$js_folder_path . '/library/jquery.toTop.js',
				array( 'jquery' ),
				'1.2',
				true,
			);
			$scripts[] = array(
				'avada-to-top',
				$js_folder_url . '/general/avada-to-top.js',
				$js_folder_path . '/general/avada-to-top.js',
				array( 'jquery', 'cssua', 'jquery-to-top' ),
				self::$version,
				true,
			);
		}
		if ( Avada()->settings->get( 'slidingbar_widgets' ) ) {
			$scripts[] = array(
				'avada-sliding-bar',
				$js_folder_url . '/general/avada-sliding-bar.js',
				$js_folder_path . '/general/avada-sliding-bar.js',
				array( 'modernizr', 'jquery', 'jquery-easing', 'avada-container-scroll' ),
				self::$version,
				true,
			);
		}
		if ( Avada()->settings->get( 'avada_styles_dropdowns' ) ) {
			$scripts[] = array(
				'avada-drop-down',
				$js_folder_url . '/general/avada-drop-down.js',
				$js_folder_path . '/general/avada-drop-down.js',
				array( 'jquery', 'avada-select' ),
				self::$version,
				true,
			);
		}
		if ( 'Top' !== Avada()->settings->get( 'header_position' ) ) {
			$scripts[] = array(
				'avada-side-header-scroll',
				$js_folder_url . '/general/avada-side-header-scroll.js',
				$js_folder_path . '/general/avada-side-header-scroll.js',
				array( 'modernizr', 'jquery' ),
				self::$version,
				true,
			);
		}

		$avada_rev_styles = get_post_meta( $page_id, 'pyre_avada_rev_styles', true );
		if ( class_exists( 'RevSliderFront' ) && ( 'no' === $avada_rev_styles || ( Avada()->settings->get( 'avada_rev_styles' ) && 'yes' !== $avada_rev_styles ) ) ) {

			// If revolution slider is active.  Can't check for rev styles option as it can be enabled in page options.
			$scripts[] = array(
				'avada-rev-styles',
				$js_folder_url . '/general/avada-rev-styles.js',
				$js_folder_path . '/general/avada-rev-styles.js',
				array( 'jquery' ),
				self::$version,
				true,
			);
		}
		if ( 'footer_parallax_effect' === Avada()->settings->get( 'footer_special_effects' ) ) {
			$scripts[] = array(
				'avada-parallax-footer',
				$js_folder_url . '/general/avada-parallax-footer.js',
				$js_folder_path . '/general/avada-parallax-footer.js',
				array( 'jquery', 'modernizr' ),
				self::$version,
				true,
			);
		}
		if ( ! Avada()->settings->get( 'disable_mobile_image_hovers' ) ) {
			$scripts[] = array(
				'avada-mobile-image-hover',
				$js_folder_url . '/general/avada-mobile-image-hover.js',
				$js_folder_path . '/general/avada-mobile-image-hover.js',
				array( 'jquery', 'modernizr' ),
				self::$version,
				true,
			);
		}
		if ( Avada()->settings->get( 'page_title_fading' ) ) {

			// If we add a page option for this, it will need to be changed here too.
			$scripts[] = array(
				'avada-fade',
				$js_folder_url . '/general/avada-fade.js',
				$js_folder_path . '/general/avada-fade.js',
				array( 'jquery', 'cssua', 'jquery-fade' ),
				self::$version,
				true,
			);
		}
		if ( defined( 'WPCF7_PLUGIN' ) ) {
			$scripts[] = array(
				'avada-contact-form-7',
				$js_folder_url . '/general/avada-contact-form-7.js',
				$js_folder_path . '/general/avada-contact-form-7.js',
				array( 'jquery' ),
				self::$version,
				true,
			);
		}
		if ( class_exists( 'GFForms' ) && Avada()->settings->get( 'avada_styles_dropdowns' ) ) {
			$scripts[] = array(
				'avada-gravity-forms',
				$js_folder_url . '/general/avada-gravity-forms.js',
				$js_folder_path . '/general/avada-gravity-forms.js',
				array( 'jquery', 'avada-select' ),
				self::$version,
				true,
			);
		}
		if ( Avada()->settings->get( 'status_eslider' ) ) {
			$scripts[] = array(
				'jquery-elastic-slider',
				$js_folder_url . '/library/jquery.elasticslider.js',
				$js_folder_path . '/library/jquery.elasticslider.js',
				array( 'jquery' ),
				self::$version,
				true,
			);
			$scripts[] = array(
				'avada-elastic-slider',
				$js_folder_url . '/general/avada-elastic-slider.js',
				$js_folder_path . '/general/avada-elastic-slider.js',
				array( 'jquery', 'jquery-elastic-slider' ),
				self::$version,
				true,
			);
		}
		if ( class_exists( 'WooCommerce' ) ) {
			$scripts[] = array(
				'avada-woocommerce',
				$js_folder_url . '/general/avada-woocommerce.js',
				$js_folder_path . '/general/avada-woocommerce.js',
				array( 'jquery', 'modernizr', 'fusion-equal-heights' ),
				self::$version,
				true,
			);
		}
		if ( function_exists( 'is_bbpress' ) ) {
			$scripts[] = array(
				'avada-bbpress',
				$js_folder_url . '/general/avada-bbpress.js',
				$js_folder_path . '/general/avada-bbpress.js',
				array( 'jquery' ),
				self::$version,
				true,
			);
		}
		if ( class_exists( 'Tribe__Events__Main' ) ) {
			$scripts[] = array(
				'avada-events',
				$js_folder_url . '/general/avada-events.js',
				$js_folder_path . '/general/avada-events.js',
				array( 'jquery' ),
				self::$version,
				true,
			);
		}

		if ( Avada()->settings->get( 'smooth_scrolling' ) ) {
			$scripts[] = array(
				'jquery-nicescroll',
				$js_folder_url . '/library/jquery.nicescroll.js',
				$js_folder_path . '/library/jquery.nicescroll.js',
				array( 'jquery' ),
				'3.7.4',
				true,
			);
			$scripts[] = array(
				'avada-nicescroll',
				$js_folder_url . '/general/avada-nicescroll.js',
				$js_folder_path . '/general/avada-nicescroll.js',
				array( 'jquery', 'modernizr', 'jquery-nicescroll' ),
				self::$version,
				true,
			);
		}

		if ( $privacy_options['privacy_embeds'] || $privacy_options['privacy_bar'] ) {
			$scripts[] = array(
				'avada-privacy',
				$js_folder_url . '/general/avada-privacy.js',
				$js_folder_path . '/general/avada-privacy.js',
				array( 'jquery', 'avada-container-scroll' ),
				self::$version,
				true,
			);
		}

		if ( is_page_template( 'contact.php' ) && Avada()->settings->get( 'recaptcha_public' ) && Avada()->settings->get( 'recaptcha_private' ) && ! function_exists( 'recaptcha_get_html' ) ) {
			$scripts[] = array(
				'avada-contact',
				$js_folder_url . '/general/avada-contact.js',
				$js_folder_path . '/general/avada-contact.js',
				array( 'jquery' ),
				self::$version,
				true,
			);
		}

		if ( ! class_exists( 'FusionBuilder' ) ) {
			$scripts[] = array(
				'fusion-carousel',
				str_replace( Avada::$template_dir_url, FUSION_LIBRARY_URL, $js_folder_url ) . '/general/fusion-carousel.js',
				str_replace( Avada::$template_dir_path, FUSION_LIBRARY_PATH, $js_folder_path ) . '/general/fusion-carousel.js',
				array( 'jquery-caroufredsel', 'jquery-touch-swipe' ),
				'1',
				true,
			);
			$scripts[] = array(
				'fusion-blog',
				str_replace( Avada::$template_dir_url, FUSION_LIBRARY_URL, $js_folder_url ) . '/general/fusion-blog.js',
				str_replace( Avada::$template_dir_path, FUSION_LIBRARY_PATH, $js_folder_path ) . '/general/fusion-blog.js',
				array( 'jquery', 'isotope', 'fusion-lightbox', 'fusion-flexslider', 'jquery-infinite-scroll', 'images-loaded' ),
				'1',
				true,
			);
		}

		foreach ( $scripts as $script ) {
			Fusion_Dynamic_JS::enqueue_script(
				$script[0],
				$script[1],
				$script[2],
				$script[3],
				$script[4],
				$script[5]
			);
		}

		Fusion_Dynamic_JS::enqueue_script( 'fusion-alert' );

		if ( ! class_exists( 'FusionBuilder' ) ) {
			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/shared.min.css', Avada::$template_dir_url . '/assets/css/shared.min.css' );
		}

		if ( Avada()->settings->get( 'status_lightbox' ) && ! class_exists( 'FusionBuilder' ) ) {
			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/ilightbox.min.css', Avada::$template_dir_url . '/assets/css/ilightbox.min.css' );
		}

		if ( Avada()->settings->get( 'use_animate_css' ) && ! class_exists( 'FusionBuilder' ) ) {
			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/animations.min.css', Avada::$template_dir_url . '/assets/css/animations.min.css' );
		}

		if ( class_exists( 'WooCommerce' ) ) {
			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/woocommerce.min.css', Avada::$template_dir_url . '/assets/css/woocommerce.min.css' );
		}

		if ( class_exists( 'bbPress' ) ) {
			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/bbpress.min.css', Avada::$template_dir_url . '/assets/css/bbpress.min.css' );
		}

		if ( ! get_the_ID() || avada_is_page_title_bar_active( get_the_ID() ) ) {
			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/page-title-bar.min.css', Avada::$template_dir_url . '/assets/css/page-title-bar.min.css' );
		}

		if ( class_exists( 'GFForms' ) ) {
			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/gravityforms.min.css', Avada::$template_dir_url . '/assets/css/gravityforms.min.css' );
		}

		if ( class_exists( 'WPCF7' ) ) {
			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/contactform7.min.css', Avada::$template_dir_url . '/assets/css/contactform7.min.css' );
		}

		if ( class_exists( 'Tribe__Events__Main' ) ) {
			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/events-calendar.min.css', Avada::$template_dir_url . '/assets/css/events-calendar.min.css' );
		}

		if ( Avada()->settings->get( 'slidingbar_widgets' ) ) {
			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/slidingbar.min.css', Avada::$template_dir_url . '/assets/css/slidingbar.min.css' );
		}
	}

	/**
	 * Localize the dynamic JS files.
	 *
	 * @access protected
	 * @since 5.1.0
	 * @return void
	 */
	protected function localize_scripts() {

		global $wp_styles, $woocommerce, $fusion_library;
		$multilingual     = $fusion_library->multilingual;
		$page_bg_layout   = get_post_meta( Avada()->fusion_library->get_page_id(), 'pyre_page_bg_layout', true );
		$avada_rev_styles = get_post_meta( Avada()->fusion_library->get_page_id(), 'pyre_avada_rev_styles', true );
		$layout           = ( 'boxed' === $page_bg_layout || 'wide' === $page_bg_layout ) ? $page_bg_layout : Avada()->settings->get( 'layout' );
		$avada_rev_styles = ( 'no' === $avada_rev_styles || ( Avada()->settings->get( 'avada_rev_styles' ) && 'yes' !== $avada_rev_styles ) ) ? 1 : 0;
		$privacy_options  = Avada()->privacy_embeds->get_options();

		$side_header_breakpoint = Avada()->settings->get( 'side_header_break_point' );
		if ( ! $side_header_breakpoint ) {
			$side_header_breakpoint = 800;
		}

		$cookie_args      = class_exists( 'Avada_Privacy_Embeds' ) && $privacy_options['privacy_embeds'] ? Avada()->privacy_embeds->get_cookie_args() : false;
		$consents         = class_exists( 'Avada_Privacy_Embeds' ) && $privacy_options['privacy_embeds'] ? array_keys( Avada()->privacy_embeds->get_embed_types() ) : array();
		$default_consents = class_exists( 'Avada_Privacy_Embeds' ) && $privacy_options['privacy_embeds'] ? Avada()->privacy_embeds->get_default_consents() : array();

		$scripts = array(
			array(
				'avada-header',
				'avadaHeaderVars',
				array(
					'header_position'            => strtolower( Avada()->settings->get( 'header_position' ) ),
					'header_layout'              => Avada()->settings->get( 'header_layout' ),
					'header_sticky'              => Avada()->settings->get( 'header_sticky' ),
					'header_sticky_type2_layout' => Avada()->settings->get( 'header_sticky_type2_layout' ),
					'side_header_break_point'    => (int) $side_header_breakpoint,
					'header_sticky_mobile'       => Avada()->settings->get( 'header_sticky_mobile' ),
					'header_sticky_tablet'       => Avada()->settings->get( 'header_sticky_tablet' ),
					'mobile_menu_design'         => Avada()->settings->get( 'mobile_menu_design' ),
					'sticky_header_shrinkage'    => Avada()->settings->get( 'header_sticky_shrinkage' ),
					'nav_height'                 => (int) Avada()->settings->get( 'nav_height' ),
					'nav_highlight_border'       => ( 'bar' === Avada()->settings->get( 'menu_highlight_style' ) ) ? (int) Avada()->settings->get( 'nav_highlight_border' ) : '0',
					'nav_highlight_style'        => Avada()->settings->get( 'menu_highlight_style' ),
					'logo_margin_top'            => ( '' !== Avada()->settings->get( 'logo', 'url' ) || '' !== Avada()->settings->get( 'logo_retina', 'url' ) ) ? Avada()->settings->get( 'logo_margin', 'top' ) : '0px',
					'logo_margin_bottom'         => ( '' !== Avada()->settings->get( 'logo', 'url' ) || '' !== Avada()->settings->get( 'logo_retina', 'url' ) ) ? Avada()->settings->get( 'logo_margin', 'bottom' ) : '0px',
					'layout_mode'                => strtolower( $layout ),
					'header_padding_top'         => Avada()->settings->get( 'header_padding', 'top' ),
					'header_padding_bottom'      => Avada()->settings->get( 'header_padding', 'bottom' ),
					'offset_scroll'              => Avada()->settings->get( 'scroll_offset' ),
				),
			),
			array(
				'avada-menu',
				'avadaMenuVars',
				array(
					'header_position'         => Avada()->settings->get( 'header_position' ),
					'logo_alignment'          => Avada()->settings->get( 'logo_alignment' ),
					'header_sticky'           => Avada()->settings->get( 'header_sticky' ),
					'side_header_break_point' => (int) $side_header_breakpoint,
					'mobile_menu_design'      => Avada()->settings->get( 'mobile_menu_design' ),
					'dropdown_goto'           => __( 'Go to...', 'Avada' ),
					'mobile_nav_cart'         => __( 'Shopping Cart', 'Avada' ),
					'mobile_submenu_open'     => esc_attr__( 'Open Sub Menu', 'Avada' ),
					'mobile_submenu_close'    => esc_attr__( 'Close Sub Menu', 'Avada' ),
					'submenu_slideout'        => Avada()->settings->get( 'mobile_nav_submenu_slideout' ),
				),
			),
			array(
				'avada-comments',
				'avadaCommentVars',
				array(
					'title_style_type'    => Avada()->settings->get( 'title_style_type' ),
					'title_margin_top'    => Avada()->settings->get( 'title_margin', 'top' ),
					'title_margin_bottom' => Avada()->settings->get( 'title_margin', 'bottom' ),
				),
			),
			array(
				'jquery-to-top',
				'toTopscreenReaderText',
				array(
					'label' => esc_attr__( 'Go to Top', 'Avada' ),
				),
			),
			array(
				'avada-to-top',
				'avadaToTopVars',
				array(
					'status_totop_mobile' => Avada()->settings->get( 'status_totop_mobile' ),
				),
			),
			array(
				'avada-wpml',
				'avadaLanguageVars',
				array(
					'language_flag' => $multilingual->get_active_language(),
				),
			),
			array(
				'avada-sidebars',
				'avadaSidebarsVars',
				array(
					'header_position'            => strtolower( Avada()->settings->get( 'header_position' ) ),
					'header_layout'              => Avada()->settings->get( 'header_layout' ),
					'header_sticky'              => Avada()->settings->get( 'header_sticky' ),
					'header_sticky_type2_layout' => Avada()->settings->get( 'header_sticky_type2_layout' ),
					'side_header_break_point'    => (int) $side_header_breakpoint,
					'header_sticky_tablet'       => Avada()->settings->get( 'header_sticky_tablet' ),
					'sticky_header_shrinkage'    => Avada()->settings->get( 'header_sticky_shrinkage' ),
					'nav_height'                 => (int) Avada()->settings->get( 'nav_height' ),
					'content_break_point'        => Avada()->settings->get( 'content_break_point' ),
				),
			),
			array(
				'avada-side-nav',
				'avadaSideNavVars',
				array(
					'sidenav_behavior' => Avada()->settings->get( 'sidenav_behavior' ),
				),
			),
			array(
				'avada-side-header-scroll',
				'avadaSideHeaderVars',
				array(
					'side_header_break_point' => (int) $side_header_breakpoint,
					'footer_special_effects'  => Avada()->settings->get( 'footer_special_effects' ),
				),
			),
			array(
				'avada-rev-styles',
				'avadaRevVars',
				array(
					'avada_rev_styles' => $avada_rev_styles,
				),
			),
			array(
				'avada-parallax-footer',
				'avadaParallaxFooterVars',
				array(
					'side_header_break_point' => (int) $side_header_breakpoint,
					'header_position'         => Avada()->settings->get( 'header_position' ),
				),
			),
			array(
				'avada-mobile-image-hover',
				'avadaMobileImageVars',
				array(
					'side_header_break_point' => (int) $side_header_breakpoint,
				),
			),
			array(
				'avada-nicescroll',
				'avadaNiceScrollVars',
				array(
					'side_header_width' => ( 'Top' !== Avada()->settings->get( 'header_position' ) ) ? (int) Avada()->settings->get( 'side_header_width' ) : '0',
					'smooth_scrolling'  => Avada()->settings->get( 'smooth_scrolling' ),
				),
			),
			array(
				'avada-woocommerce',
				'avadaWooCommerceVars',
				array(
					'order_actions'                   => __( 'Details', 'Avada' ),
					'title_style_type'                => Avada()->settings->get( 'title_style_type' ),
					'woocommerce_shop_page_columns'   => Avada()->settings->get( 'woocommerce_shop_page_columns' ),
					'woocommerce_checkout_error'      => esc_attr__( 'Not all fields have been filled in correctly.', 'Avada' ),
					'woocommerce_single_gallery_size' => Fusion_Sanitize::number( Avada()->settings->get( 'woocommerce_single_gallery_size' ) ),
					'related_products_heading_size'   => ( false === avada_is_page_title_bar_enabled( get_the_ID() ) ? '2' : '3' ),
				),
			),
			array(
				'avada-bbpress',
				'avadaBbpressVars',
				array(
					'alert_box_text_align'     => Avada()->settings->get( 'alert_box_text_align' ),
					'alert_box_text_transform' => Avada()->settings->get( 'alert_box_text_transform' ),
					'alert_box_dismissable'    => Avada()->settings->get( 'alert_box_dismissable' ),
					'alert_box_shadow'         => Avada()->settings->get( 'alert_box_shadow' ),
					'alert_border_size'        => Avada()->settings->get( 'alert_border_size' ),
				),
			),
			array(
				'avada-elastic-slider',
				'avadaElasticSliderVars',
				array(
					'tfes_autoplay'  => Avada()->settings->get( 'tfes_autoplay' ),
					'tfes_animation' => Avada()->settings->get( 'tfes_animation' ),
					'tfes_interval'  => (int) Avada()->settings->get( 'tfes_interval' ),
					'tfes_speed'     => (int) Avada()->settings->get( 'tfes_speed' ),
					'tfes_width'     => (int) Avada()->settings->get( 'tfes_width' ),
				),
			),
			array(
				'avada-fade',
				'avadaFadeVars',
				array(
					'page_title_fading' => Avada()->settings->get( 'page_title_fading' ),
					'header_position'   => Avada()->settings->get( 'header_position' ),
				),
			),
			array(
				'avada-privacy',
				'avadaPrivacyVars',
				array(
					'name'     => $cookie_args ? $cookie_args['name'] : 'privacy_embeds',
					'days'     => $cookie_args ? $cookie_args['days'] : '30',
					'path'     => $cookie_args ? $cookie_args['path'] : '/',
					'types'    => $consents ? $consents : array(),
					'defaults' => $default_consents ? $default_consents : array(),
					'button'   => $privacy_options['privacy_bar_button_save'],
				),
			),
			array(
				'avada-contact',
				'avadaContactVars',
				array(
					'badge_position'   => 'hide' === Avada()->settings->get( 'recaptcha_badge_position' ) ? 'inline' : Avada()->settings->get( 'recaptcha_badge_position' ),
					'recaptcha_public' => Avada()->settings->get( 'recaptcha_public' ),
				),
			),
		);

		foreach ( $scripts as $script ) {
			Fusion_Dynamic_JS::localize_script(
				$script[0],
				$script[1],
				$script[2]
			);

		}

	}

	/**
	 * Takes care of enqueueing all our scripts.
	 *
	 * @access public
	 */
	public function wp_enqueue_scripts() {

		wp_enqueue_script( 'jquery' );

		// The comment-reply script.
		if ( is_singular() && get_option( 'thread_comments' ) && comments_open() ) {
			wp_enqueue_script( 'comment-reply', '', array(), false, true );
		}

		if ( function_exists( 'novagallery_shortcode' ) ) {
			wp_enqueue_script( 'novagallery_modernizr' );
		}

		if ( function_exists( 'ccgallery_shortcode' ) ) {
			wp_enqueue_script( 'ccgallery_modernizr' );
		}

		wp_enqueue_style( 'avada-stylesheet', Avada::$template_dir_url . '/assets/css/style.min.css', array(), self::$version );

		wp_enqueue_style( 'avada-IE', Avada::$template_dir_url . '/assets/css/ie.min.css', array(), self::$version );
		wp_style_add_data( 'avada-IE', 'conditional', 'IE' );

		$form_bg_color = Avada()->settings->get( 'form_bg_color' ) ? Avada()->settings->get( 'form_bg_color' ) : '#ffffff';
		wp_add_inline_style( 'avada-IE', '.avada-select-parent .select-arrow{background-color:' . $form_bg_color . '}' );
		wp_add_inline_style( 'avada-IE', '.select-arrow{background-color:' . $form_bg_color . '}' );

		if ( Avada()->settings->get( 'status_lightbox' ) && class_exists( 'WooCommerce' ) ) {
			wp_dequeue_script( 'prettyPhoto' );
			wp_dequeue_script( 'prettyPhoto-init' );
			wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
		}

		if ( is_rtl() && 'file' !== $this->compiler_mode ) {
			wp_enqueue_style( 'avada-rtl', Avada::$template_dir_url . '/assets/css/rtl.min.css', array(), self::$version );
		}

		if ( is_page_template( 'contact.php' ) ) {
			$options = get_option( Avada::get_option_name() );
			if ( $options['recaptcha_public'] && $options['recaptcha_private'] && ! function_exists( 'recaptcha_get_html' ) ) {
				if ( version_compare( PHP_VERSION, '5.3' ) >= 0 && ! class_exists( 'ReCaptcha' ) ) {
					if ( 'v2' === Avada()->settings->get( 'recaptcha_version' ) ) {
						wp_enqueue_script( 'recaptcha-api', 'https://www.google.com/recaptcha/api.js?hl=' . get_locale() );
					} else {
						wp_enqueue_script( 'recaptcha-api', 'https://www.google.com/recaptcha/api.js?render=explicit&hl=' . get_locale() . '&onload=fusionOnloadCallback', array(), self::$version );
					}
				}
			}
		}
	}

	/**
	 * Adds assets to the compiled CSS.
	 *
	 * @access public
	 * @since 5.1.5
	 * @param string $original_styles The compiled styles.
	 * @return string The compiled styles with any additional CSS appended.
	 */
	public function combine_stylesheets( $original_styles ) {
		$styles = '';

		if ( 'off' !== Avada()->settings->get( 'css_cache_method' ) ) {
			if ( is_rtl() ) {
				// Stylesheet ID: avada-rtl.
				$styles .= @file_get_contents( Avada::$template_dir_path . '/assets/css/rtl.min.css' );
			}
		}

		if ( function_exists( 'wpcf7_plugin_path' ) ) {
			$cf7_styles = @file_get_contents( wpcf7_plugin_path( 'includes/css/styles.css' ) );
			if ( function_exists( 'wpcf7_is_rtl' ) && wpcf7_is_rtl() ) {
				$cf7_styles .= @file_get_contents( wpcf7_plugin_path( 'includes/css/styles-rtl.css' ) );
			}
			$cf7_styles = str_replace( '../../images/ajax-loader.gif', wpcf7_plugin_url( 'images/ajax-loader.gif' ), $cf7_styles );

			$styles .= $cf7_styles;
		}

		return $styles . $original_styles;
	}

	/**
	 * Removes WooCommerce scripts.
	 *
	 * @access public
	 * @since 5.0.0
	 * @param array $scripts The WooCommerce scripts.
	 * @return array
	 */
	public function remove_woo_scripts( $scripts ) {

		if ( isset( $scripts['woocommerce-layout'] ) ) {
			unset( $scripts['woocommerce-layout'] );
		}
		if ( isset( $scripts['woocommerce-smallscreen'] ) ) {
			unset( $scripts['woocommerce-smallscreen'] );
		}
		if ( isset( $scripts['woocommerce-general'] ) ) {
			unset( $scripts['woocommerce-general'] );
		}
		return $scripts;

	}

	/**
	 * Calculates media-queries.
	 *
	 * @static
	 * @access public
	 * @since 5.4
	 * @param array  $args      An array of arguments.
	 * @param string $context   Example: 'only screen'.
	 * @param bool   $add_media Whether we should prepend "@media" or not.
	 * @return string
	 */
	public static function get_media_query( $args, $context = 'only screen', $add_media = false ) {

		$master_query_array = array();
		$query_array        = array( $context );
		$query              = '';
		foreach ( $args as $what => $when ) {
			// If an array then we have multiple media-queries here
			// and we need to process each one separately.
			if ( is_array( $when ) ) {
				$query_array = array( $context );
				foreach ( $when as $sub_what => $sub_when ) {
					// Make sure pixels are integers.
					$sub_when      = ( false !== strpos( $sub_when, 'px' ) && false === strpos( $sub_when, 'dppx' ) ) ? absint( $sub_when ) . 'px' : $sub_when;
					$query_array[] = "({$sub_what}: $sub_when)";
				}
				$master_query_array[] = implode( ' and ', $query_array );
				continue;
			}
			// Make sure pixels are integers.
			$when          = ( false !== strpos( $when, 'px' ) && false === strpos( $when, 'dppx' ) ) ? absint( $when ) . 'px' : $when;
			$query_array[] = "({$what}: $when)";
		}

		// If we've got multiple queries, then need to be separated using a comma.
		if ( ! empty( $master_query_array ) ) {
			$query = implode( ', ', $master_query_array );
		}
		// If we don't have multiple queries we need to separate arguments with "and".
		$query = ( ! $query ) ? implode( ' and ', $query_array ) : $query;

		if ( $add_media ) {
			return '@media ' . $query;
		}
		return $query;
	}

	/**
	 * Enqueues media-query styles if needed.
	 *
	 * @access public
	 * @since 5.6
	 * @return void
	 */
	public function enqueue_media_query_styles() {
		// No reason to proceed any further if we're including the files inside the compiler.
		if ( '0' === Avada()->settings->get( 'media_queries_async' ) ) {
			return;
		}
		$media_queries = array();
		foreach ( self::$media_query_assets as $asset ) {
			if ( ! isset( $media_queries[ $asset[4] ] ) ) {
				$media_queries[ $asset[4] ] = array();
			}
			$media_queries[ $asset[4] ][] = $asset;
		}

		foreach ( $media_queries as $media_query ) {
			if ( ! isset( $media_query[1] ) ) {
				// We only have 1 asset for this media-query. Enqueue it.
				wp_enqueue_style( $media_query[0][0], $media_query[0][1], $media_query[0][2], $media_query[0][3], $media_query[0][4] );
				continue;
			}
			$handles = array();
			$paths   = array();
			$deps    = array();
			$ver     = '';
			$query   = '';
			foreach ( $media_query as $asset ) {
				$handles[] = $asset[0];
				$paths[]   = str_replace( array( '.min.min.css', '.css' ), '', str_replace( get_template_directory_uri() . '/assets/css/media/', '', $asset[1] ) );
				$deps      = array_merge( $deps, $asset[2] );
				$ver       = $asset[3];
				$query     = $asset[4];
			}
			$handle = 'avada-' . str_replace( 'avada-', '-', implode( '-', $handles ) );
			$handle = str_replace( array( '_', '--' ), '-', $handle );
			$url    = add_query_arg(
				array(
					'action' => 'avada-get-styles',
					'mq'     => implode( ',', $paths ),
				),
				get_site_url()
			);
			wp_enqueue_style( $handle, $url, $deps, $ver, $query );
		}
	}

	/**
	 * Get combine media-query files.
	 *
	 * @access private
	 * @since 5.6
	 * @return void
	 */
	private function combine_media_query_files() {

		if ( ! isset( $_GET['action'] ) || 'avada-get-styles' !== sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) {
			return;
		}

		// Output as CSS file.
		header( 'Content-type: text/css', true );

		$styles = array();
		if ( isset( $_GET['mq'] ) ) {
			$styles = explode( ',', $_GET['mq'] ); // WPCS: CSRF ok sanitization ok.
		}
		foreach ( $styles as $style ) {
			$style = trim( $style );
			if ( file_exists( Avada::$template_dir_path . "/assets/css/media/{$style}.min.css" ) ) {
				include_once Avada::$template_dir_path . "/assets/css/media/{$style}.min.css";
			} elseif ( file_exists( Avada::$template_dir_path . "/assets/css/media/{$style}.css" ) ) {
				include_once Avada::$template_dir_path . "/assets/css/media/{$style}.css";
			}
		}
		exit();
	}

	/**
	 * Adds media-query styles to the compiler if needed.
	 *
	 * @access public
	 * @since 5.6
	 * @param string $styles The css styles where we'll be adding our compiled styles.
	 * @return string
	 */
	public function compile_media_query_styles( $styles ) {
		// No reason to proceed any further if we're including the files inside the compiler.
		if ( '1' === Avada()->settings->get( 'media_queries_async' ) ) {
			return $styles;
		}
		$template_dir_url  = get_template_directory_uri();
		$template_dir_path = get_template_directory();
		foreach ( self::$media_query_assets as $asset ) {
			// The file-path.
			$path = wp_normalize_path( str_replace( $template_dir_url, $template_dir_path, $asset[1] ) );
			// Add the contents of the file to $styles.
			$styles .= '@media ' . $asset[4] . '{';
			$styles .= file_get_contents( $path );
			$styles .= '}';
		}
		return $styles;
	}

	/**
	 * Adds media-query styles.
	 *
	 * @access public
	 * @since 6.0.0
	 */
	public function add_media_query_styles() {
		$side_header_breakpoint = Avada()->settings->get( 'side_header_break_point' );
		if ( ! $side_header_breakpoint ) {
			$side_header_breakpoint = 800;
		}

		if ( '0' === Avada()->settings->get( 'responsive' ) || 0 === Avada()->settings->get( 'responsive' ) || ! Avada()->settings->get( 'responsive' ) ) {
			self::$media_query_assets[] = array(
				'avada-max-shbp-not-responsive',
				get_template_directory_uri() . '/assets/css/media/max-shbp-not-responsive.min.css',
				array(),
				self::$version,
				self::get_media_query(
					array(
						'max-width' => (int) $side_header_breakpoint . 'px',
					)
				),
			);

			self::$media_query_assets[] = array(
				'avada-max-shbp-18-not-responsive',
				get_template_directory_uri() . '/assets/css/media/max-shbp-18-not-responsive.min.css',
				array(),
				self::$version,
				self::get_media_query(
					array(
						'max-width' => (int) $side_header_breakpoint - 18 . 'px',
					)
				),
			);
			return;
		}

		// Responsive mode.
		$side_header_width = ( 'Top' === Avada()->settings->get( 'header_position' ) ) ? 0 : (int) Avada()->settings->get( 'side_header_width' );

		// # Grid System.
		$main_break_point = (int) Avada()->settings->get( 'grid_main_break_point' );
		if ( 640 < $main_break_point ) {
			$breakpoint_range = $main_break_point - 640;
		} else {
			$breakpoint_range = 360;
		}

		$breakpoint_interval = (int) ( $breakpoint_range / 5 );

		$six_columns_breakpoint   = $main_break_point + $side_header_width;
		$five_columns_breakpoint  = $six_columns_breakpoint - $breakpoint_interval;
		$four_columns_breakpoint  = $five_columns_breakpoint - $breakpoint_interval;
		$three_columns_breakpoint = $four_columns_breakpoint - $breakpoint_interval;
		$two_columns_breakpoint   = $three_columns_breakpoint - $breakpoint_interval;
		$one_column_breakpoint    = $two_columns_breakpoint - $breakpoint_interval;

		self::$media_query_assets[] = array(
			'avada-max-1c',
			get_template_directory_uri() . '/assets/css/media/max-1c.min.css',
			array(),
			self::$version,
			self::get_media_query(
				array(
					'max-width' => $one_column_breakpoint . 'px',
				)
			),
		);

		self::$media_query_assets[] = array(
			'avada-max-2c',
			get_template_directory_uri() . '/assets/css/media/max-2c.min.css',
			array(),
			self::$version,
			self::get_media_query(
				array(
					'max-width' => $two_columns_breakpoint . 'px',
				)
			),
		);

		self::$media_query_assets[] = array(
			'avada-min-2c-max-3c',
			get_template_directory_uri() . '/assets/css/media/min-2c-max-3c.min.css',
			array(),
			self::$version,
			self::get_media_query(
				array(
					'min-width' => $two_columns_breakpoint . 'px',
					'max-width' => $three_columns_breakpoint . 'px',
				)
			),
		);

		self::$media_query_assets[] = array(
			'avada-min-3c-max-4c',
			get_template_directory_uri() . '/assets/css/media/min-3c-max-4c.min.css',
			array(),
			self::$version,
			self::get_media_query(
				array(
					'min-width' => $three_columns_breakpoint . 'px',
					'max-width' => $four_columns_breakpoint . 'px',
				)
			),
		);

		self::$media_query_assets[] = array(
			'avada-min-4c-max-5c',
			get_template_directory_uri() . '/assets/css/media/min-4c-max-5c.min.css',
			array(),
			self::$version,
			self::get_media_query(
				array(
					'min-width' => $four_columns_breakpoint . 'px',
					'max-width' => $five_columns_breakpoint . 'px',
				)
			),
		);

		self::$media_query_assets[] = array(
			'avada-min-5c-max-6c',
			get_template_directory_uri() . '/assets/css/media/min-5c-max-6c.min.css',
			array(),
			self::$version,
			self::get_media_query(
				array(
					'min-width' => $five_columns_breakpoint . 'px',
					'max-width' => $six_columns_breakpoint . 'px',
				)
			),
		);

		self::$media_query_assets[] = array(
			'avada-min-shbp',
			get_template_directory_uri() . '/assets/css/media/min-shbp.min.css',
			array(),
			self::$version,
			self::get_media_query(
				array(
					'min-width' => (int) $side_header_breakpoint . 'px',
				)
			),
		);

		self::$media_query_assets[] = array(
			'avada-max-shbp',
			get_template_directory_uri() . '/assets/css/media/max-shbp.min.css',
			array(),
			self::$version,
			self::get_media_query(
				array(
					'max-width' => (int) $side_header_breakpoint . 'px',
				)
			),
		);

		self::$media_query_assets[] = array(
			'avada-max-sh-shbp',
			get_template_directory_uri() . '/assets/css/media/max-sh-shbp.min.css',
			array(),
			self::$version,
			self::get_media_query(
				array(
					'max-width' => (int) ( $side_header_width + $side_header_breakpoint ) . 'px',
				)
			),
		);

		// IPAD.
		self::$media_query_assets[] = array(
			'avada-min-768-max-1024-p',
			get_template_directory_uri() . '/assets/css/media/min-768-max-1024-p.min.css',
			array(),
			self::$version,
			self::get_media_query(
				array(
					'min-device-width' => '768px',
					'max-device-width' => '1024px',
					'orientation'      => 'portrait',
				)
			),
		);

		self::$media_query_assets[] = array(
			'avada-min-768-max-1024-l',
			get_template_directory_uri() . '/assets/css/media/min-768-max-1024-l.min.css',
			array(),
			self::$version,
			self::get_media_query(
				array(
					'min-device-width' => '768px',
					'max-device-width' => '1024px',
					'orientation'      => 'landscape',
				)
			),
		);

		self::$media_query_assets[] = array(
			'avada-max-sh-cbp',
			get_template_directory_uri() . '/assets/css/media/max-sh-cbp.min.css',
			array(),
			self::$version,
			self::get_media_query(
				array(
					'max-width' => (int) ( $side_header_width + Avada()->settings->get( 'content_break_point' ) ) . 'px',
				)
			),
		);

		self::$media_query_assets[] = array(
			'avada-max-sh-sbp',
			get_template_directory_uri() . '/assets/css/media/max-sh-sbp.min.css',
			array(),
			self::$version,
			self::get_media_query(
				array(
					'max-width' => (int) ( $side_header_width + Avada()->settings->get( 'sidebar_break_point' ) ) . 'px',
				)
			),
		);

		self::$media_query_assets[] = array(
			'avada-max-sh-640',
			get_template_directory_uri() . '/assets/css/media/max-sh-640.min.css',
			array(),
			self::$version,
			self::get_media_query(
				array(
					'max-width' => (int) ( $side_header_width + 640 ) . 'px',
				)
			),
		);

		self::$media_query_assets[] = array(
			'avada-max-shbp-18',
			get_template_directory_uri() . '/assets/css/media/max-shbp-18.min.css',
			array(),
			self::$version,
			self::get_media_query(
				array(
					'max-width' => (int) ( $side_header_breakpoint - 18 ) . 'px',
				)
			),
		);

		self::$media_query_assets[] = array(
			'avada-max-shbp-32',
			get_template_directory_uri() . '/assets/css/media/max-shbp-32.min.css',
			array(),
			self::$version,
			self::get_media_query(
				array(
					'max-width' => (int) ( $side_header_breakpoint - 32 ) . 'px',
				)
			),
		);

		self::$media_query_assets[] = array(
			'avada-min-sh-cbp',
			get_template_directory_uri() . '/assets/css/media/min-sh-cbp.min.css',
			array(),
			self::$version,
			self::get_media_query(
				array(
					'min-width' => (int) ( $side_header_width + Avada()->settings->get( 'content_break_point' ) ) . 'px',
				)
			),
		);

		self::$media_query_assets[] = array(
			'avada-max-640',
			get_template_directory_uri() . '/assets/css/media/max-640.min.css',
			array(),
			self::$version,
			self::get_media_query(
				array(
					'max-device-width' => '640px',
				)
			),
		);

		// bbPress.
		if ( function_exists( 'is_bbpress' ) ) {
			self::$media_query_assets[] = array(
				'avada-max-640-bbpress',
				get_template_directory_uri() . '/assets/css/media/max-640-bbpress.min.css',
				array(),
				self::$version,
				self::get_media_query(
					array(
						'max-width' => '640px',
					)
				),
			);

			self::$media_query_assets[] = array(
				'avada-max-sh-640-bbpress',
				get_template_directory_uri() . '/assets/css/media/max-sh-640-bbpress.min.css',
				array(),
				self::$version,
				self::get_media_query(
					array(
						'max-width' => (int) ( $side_header_width + 640 ) . 'px',
					)
				),
			);

			self::$media_query_assets[] = array(
				'avada-max-sh-cbp-bbpress',
				get_template_directory_uri() . '/assets/css/media/max-sh-cbp-bbpress.min.css',
				array(),
				self::$version,
				self::get_media_query(
					array(
						'max-width' => (int) ( $side_header_width + Avada()->settings->get( 'content_break_point' ) ) . 'px',
					)
				),
			);

			self::$media_query_assets[] = array(
				'avada-min-sh-cbp-bbpress',
				get_template_directory_uri() . '/assets/css/media/min-sh-cbp-bbpress.min.css',
				array(),
				self::$version,
				self::get_media_query(
					array(
						'min-width' => (int) ( $side_header_width + Avada()->settings->get( 'content_break_point' ) ) . 'px',
					)
				),
			);
		}

		// Gravity Forms.
		if ( class_exists( 'GFForms' ) ) {
			self::$media_query_assets[] = array(
				'avada-max-640-gravity',
				get_template_directory_uri() . '/assets/css/media/max-640-gravity.min.css',
				array(),
				self::$version,
				self::get_media_query(
					array(
						'max-width' => '640px',
					)
				),
			);

			self::$media_query_assets[] = array(
				'avada-max-sh-cbp-gravity',
				get_template_directory_uri() . '/assets/css/media/max-sh-cbp-gravity.min.css',
				array(),
				self::$version,
				self::get_media_query(
					array(
						'max-width' => (int) ( $side_header_width + Avada()->settings->get( 'content_break_point' ) ) . 'px',
					)
				),
			);
		}

		// WPCF7.
		if ( defined( 'WPCF7_PLUGIN' ) ) {
			self::$media_query_assets[] = array(
				'avada-max-sh-cbp-cf7',
				get_template_directory_uri() . '/assets/css/media/max-sh-cbp-cf7.min.css',
				array(),
				self::$version,
				self::get_media_query(
					array(
						'max-width' => (int) ( $side_header_width + Avada()->settings->get( 'content_break_point' ) ) . 'px',
					)
				),
			);
		}

		// LayerSlider & RevSlider.
		if ( defined( 'LS_PLUGIN_SLUG' ) || defined( 'RS_PLUGIN_PATH' ) ) {
			self::$media_query_assets[] = array(
				'avada-max-640-sliders',
				get_template_directory_uri() . '/assets/css/media/max-640-sliders.min.css',
				array(),
				self::$version,
				self::get_media_query(
					array(
						'max-width' => '640px',
					)
				),
			);

			self::$media_query_assets[] = array(
				'avada-max-sh-cbp-sliders',
				get_template_directory_uri() . '/assets/css/media/max-sh-cbp-sliders.min.css',
				array(),
				self::$version,
				self::get_media_query(
					array(
						'max-width' => (int) ( $side_header_width + Avada()->settings->get( 'content_break_point' ) ) . 'px',
					)
				),
			);
		}

		// Elastic Slider.
		if ( Avada()->settings->get( 'status_eslider' ) ) {
			self::$media_query_assets[] = array(
				'avada-max-sh-cbp-eslider',
				get_template_directory_uri() . '/assets/css/media/max-sh-cbp-eslider.min.css',
				array(),
				self::$version,
				self::get_media_query(
					array(
						'max-width' => (int) ( $side_header_width + Avada()->settings->get( 'content_break_point' ) ) . 'px',
					)
				),
			);
		}

		// CSS only added for the admin-bar.
		if ( is_admin_bar_showing() ) {
			self::$media_query_assets[] = array(
				'avada-max-782-adminbar',
				get_template_directory_uri() . '/assets/css/media/max-782-adminbar.min.css',
				array(),
				self::$version,
				self::get_media_query(
					array(
						'max-width' => '782px',
					)
				),
			);
		}

		// WooCommerce.
		if ( class_exists( 'WooCommerce' ) ) {
			self::$media_query_assets[] = array(
				'avada-min-768-max-1024-woo',
				get_template_directory_uri() . '/assets/css/media/min-768-max-1024-woo.min.css',
				array(),
				self::$version,
				self::get_media_query(
					array(
						'min-device-width' => '768px',
						'max-device-width' => '1024px',
					)
				),
			);

			self::$media_query_assets[] = array(
				'avada-max-sh-640-woo',
				get_template_directory_uri() . '/assets/css/media/max-sh-640-woo.min.css',
				array(),
				self::$version,
				self::get_media_query(
					array(
						'max-width' => (int) ( $side_header_width + 640 ) . 'px',
					)
				),
			);

			self::$media_query_assets[] = array(
				'avada-max-sh-cbp-woo',
				get_template_directory_uri() . '/assets/css/media/max-sh-cbp-woo.min.css',
				array(),
				self::$version,
				self::get_media_query(
					array(
						'max-width' => (int) ( $side_header_width + Avada()->settings->get( 'content_break_point' ) ) . 'px',
					)
				),
			);

			self::$media_query_assets[] = array(
				'avada-min-sh-cbp-woo',
				get_template_directory_uri() . '/assets/css/media/min-sh-cbp-woo.min.css',
				array(),
				self::$version,
				self::get_media_query(
					array(
						'min-width' => (int) ( $side_header_width + Avada()->settings->get( 'content_break_point' ) ) . 'px',
					)
				),
			);
		}

		// Events Calendar.
		if ( class_exists( 'Tribe__Events__Main' ) ) {
			self::$media_query_assets[] = array(
				'avada-max-768-ec',
				get_template_directory_uri() . '/assets/css/media/max-768-ec.min.css',
				array(),
				self::$version,
				self::get_media_query(
					array(
						'max-width' => '768px',
					)
				),
			);
			self::$media_query_assets[] = array(
				'avada-max-sh-cbp-ec',
				get_template_directory_uri() . '/assets/css/media/max-sh-cbp-ec.min.css',
				array(),
				self::$version,
				self::get_media_query(
					array(
						'max-width' => (int) ( $side_header_width + Avada()->settings->get( 'content_break_point' ) ) . 'px',
					)
				),
			);
		}
	}

	/**
	 * Add admin CSS
	 *
	 * @access public
	 */
	public function admin_css() {
		wp_enqueue_style( 'avada_wp_admin_css', get_template_directory_uri() . '/assets/admin/css/admin.css', false, self::$version );
	}

	/**
	 * Add async to avada javascript file for performance
	 *
	 * @access public
	 * @param  string $tag    The script tag.
	 * @param  string $handle The script handle.
	 */
	public function add_async( $tag, $handle ) {
		return ( 'avada' == $handle ) ? preg_replace( '/(><\/[a-zA-Z][^0-9](.*)>)$/', ' async $1 ', $tag ) : $tag;
	}

	/**
	 * Add extra admin styles.
	 *
	 * @access public
	 * @since 5.1.2
	 */
	public function admin_styles() {

		$font_url = FUSION_LIBRARY_URL . '/assets/fonts/icomoon';
		$font_url = str_replace( array( 'http://', 'https://' ), '//', $font_url );
		?>
		<style type="text/css">
			@font-face {
				font-family: 'icomoon';
				src:url('<?php echo esc_url_raw( $font_url ); ?>/icomoon.eot');
				src:url('<?php echo esc_url_raw( $font_url ); ?>/icomoon.eot?#iefix') format('embedded-opentype'),
					url('<?php echo esc_url_raw( $font_url ); ?>/icomoon.woff') format('woff'),
					url('<?php echo esc_url_raw( $font_url ); ?>/icomoon.ttf') format('truetype'),
					url('<?php echo esc_url_raw( $font_url ); ?>/icomoon.svg#icomoon') format('svg');
				font-weight: normal;
				font-style: normal;
			}
		</style>
		<?php

	}

	/**
	 * Add or remove block-styles.
	 *
	 * @access public
	 * @since 5.8
	 */
	public function dequeue_scripts() {

		// Dequeue block styles if no blocks exist.
		if ( function_exists( 'has_blocks' ) && ! has_blocks() ) {
			wp_dequeue_style( 'wp-block-library' );
		}

		// Dequeue CF7 styles.
		// These get added in our dynamic-css (see combine_stylesheets() method).
		wp_dequeue_style( 'contact-form-7' );
		wp_dequeue_style( 'contact-form-7-rtl' );
	}

	/**
	 * Removes all emoji related scripts and styles.
	 *
	 * @since 5.8.1
	 */
	public function disable_emojis() {

		if ( 'disabled' !== Avada()->settings->get( 'emojis_disabled' ) ) {
			return;
		}

		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'tiny_mce_plugins', array( $this, 'disable_emojis_tinymce' ) );
		add_filter( 'wp_resource_hints', array( $this, 'disable_emojis_remove_dns_prefetch' ), 10, 2 );

		if ( '1' === get_option( 'use_smilies' ) ) {
			update_option( 'use_smilies', '0' );
		}
	}

	/**
	 * Filter function used to remove the tinymce emoji plugin.
	 *
	 * @since 5.8.1
	 * @param array $plugins Array of TinyMCE plugins.
	 * @return array Difference betwen the two arrays
	 */
	public function disable_emojis_tinymce( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, array( 'wpemoji' ) );
		}

		return array();
	}

	/**
	 * Remove emoji CDN hostname from DNS prefetching hints.
	 *
	 * @since 5.8.1
	 * @param  array  $urls URLs to print for resource hints.
	 * @param  string $relation_type The relation type the URLs are printed for.
	 * @return array  Difference betwen the two arrays.
	 */
	public function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {

		if ( 'dns-prefetch' === $relation_type ) {
			$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/11/svg/' );
			$urls          = array_diff( $urls, array( $emoji_svg_url ) );
		}

		return $urls;
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
