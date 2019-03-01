<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

require_once(MINERVA_KB_PLUGIN_DIR . 'lib/helpers/fonts.php');

class MinervaKB_Assets {

	private $info;

	private $inline_styles;

	private $ajax;

	/**
	 * Constructor
	 * @param $deps
	 */
	public function __construct($deps) {

		$this->setup_dependencies( $deps );

		add_action( 'wp_enqueue_scripts', array($this, 'client_assets'), 100 );
		add_action( 'admin_enqueue_scripts', array($this, 'admin_assets'), 100 );
	}

	/**
	 * Sets up dependencies
	 * @param $deps
	 */
	private function setup_dependencies($deps) {
		if (isset($deps['info'])) {
			$this->info = $deps['info'];
		}

		if (isset($deps['inline_styles'])) {
			$this->inline_styles = $deps['inline_styles'];
		}

		if (isset($deps['ajax'])) {
			$this->ajax = $deps['ajax'];
		}
	}

	/**
	 * Client-side assets
	 */
	public function client_assets() {
		global $post;

		if (MKB_Options::option( 'typography_on' ) && !MKB_Options::option('dont_load_font')) {
			$all_fonts = mkb_get_all_fonts();
			$google_fonts = $all_fonts['GOOGLE'];
			$google_fonts = $google_fonts["fonts"];
			$selected_family = MKB_Options::option( 'style_font' );
			$selected_weights = MKB_Options::option( 'style_font_gf_weights' );
			$selected_languages = MKB_Options::option( 'style_font_gf_languages' );

			if (isset($google_fonts[$selected_family])) {
				wp_enqueue_style( 'minerva-kb-font/css', mkb_get_google_font_url(
					$selected_family, $selected_weights, $selected_languages
				), false, null );
			}
		}

		wp_enqueue_style( 'minerva-kb/css', MINERVA_KB_PLUGIN_URL . 'assets/css/dist/minerva-kb.css', false, MINERVA_KB_VERSION );

		if (!MKB_Options::option('no_font_awesome')) {
			wp_enqueue_style( 'minerva-kb/fa-css', MINERVA_KB_PLUGIN_URL . 'assets/css/vendor/font-awesome.css', false, null );
		}

		// dynamic styles
		wp_add_inline_style( 'minerva-kb/css', $this->inline_styles->get_css());

		// user custom CSS
		wp_add_inline_style( 'minerva-kb/css', $this->inline_styles->get_custom_css());

		if (MKB_Options::option('article_fancybox') && is_single() && $post->post_type == MKB_Options::option( 'article_cpt' )) {
			wp_enqueue_style( 'minerva-kb/fancybox-css', MINERVA_KB_PLUGIN_URL . 'assets/css/vendor/fancybox/jquery.fancybox-1.3.4.css', false, null );
			wp_enqueue_script( 'minerva-kb/fancybox-easing-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/fancybox/jquery.easing-1.3.pack.js', array( 'jquery' ), null, true );
			wp_enqueue_script( 'minerva-kb/fancybox-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/fancybox/jquery.fancybox-1.3.4.js', array( 'jquery', 'minerva-kb/fancybox-easing-js' ), null, true );
		}

		wp_enqueue_script( 'minerva-kb/js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb.js', array( 'jquery' ), MINERVA_KB_VERSION, true );

		wp_localize_script( 'minerva-kb/js', 'MinervaKB', $this->get_client_js_data() );
	}

	/**
	 * Gets client side JS data
	 */
	private function get_client_js_data() {
		return array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'siteUrl' => site_url(),
			'platform' => $this->info->platform(),
			'info' => array(
				'isSingle' => $this->info->is_single(),
				'isRTL' => $this->info->is_rtl(),
			),
			'nonce' => array(
				'nonce' => wp_create_nonce( $this->ajax->get_nonce() ),
				'nonceKey' =>$this->ajax->get_nonce_key(),
			),
			'settings' => array(
				'show_like_message' => MKB_Options::option( 'show_like_message' ),
				'show_dislike_message' => MKB_Options::option( 'show_dislike_message' ),
				'enable_feedback' => MKB_Options::option( 'enable_feedback' ),
				'single_template' => MKB_Options::option( 'single_template' ),
				'feedback_mode' => MKB_Options::option( 'feedback_mode' ),
				'track_search_with_results' => MKB_Options::option( 'track_search_with_results' ),
				'ga_good_search_category' => MKB_Options::option( 'ga_good_search_category' ),
				'ga_good_search_action' => MKB_Options::option( 'ga_good_search_action' ),
				'ga_good_search_value' => MKB_Options::option( 'ga_good_search_value' ),
				'track_search_without_results' => MKB_Options::option( 'track_search_without_results' ),
				'ga_bad_search_category' => MKB_Options::option( 'ga_bad_search_category' ),
				'ga_bad_search_action' => MKB_Options::option( 'ga_bad_search_action' ),
				'ga_bad_search_value' => MKB_Options::option( 'ga_bad_search_value' ),
				'track_article_likes' => MKB_Options::option( 'track_article_likes' ),
				'ga_like_category' => MKB_Options::option( 'ga_like_category' ),
				'ga_like_action' => MKB_Options::option( 'ga_like_action' ),
				'ga_like_label' => MKB_Options::option( 'ga_like_label' ),
				'ga_like_value' => MKB_Options::option( 'ga_like_value' ),
				'track_article_dislikes' => MKB_Options::option( 'track_article_dislikes' ),
				'ga_dislike_category' => MKB_Options::option( 'ga_dislike_category' ),
				'ga_dislike_action' => MKB_Options::option( 'ga_dislike_action' ),
				'ga_dislike_label' => MKB_Options::option( 'ga_dislike_label' ),
				'ga_dislike_value' => MKB_Options::option( 'ga_dislike_value' ),
				'track_article_feedback' => MKB_Options::option( 'track_article_feedback' ),
				'ga_feedback_category' => MKB_Options::option( 'ga_feedback_category' ),
				'ga_feedback_action' => MKB_Options::option( 'ga_feedback_action' ),
				'ga_feedback_label' => MKB_Options::option( 'ga_feedback_label' ),
				'ga_feedback_value' => MKB_Options::option( 'ga_feedback_value' ),
				'search_delay' => MKB_Options::option( 'search_delay' ),
				'live_search_show_excerpt' => MKB_Options::option( 'live_search_show_excerpt' ),
				'live_search_use_post' => MKB_Options::option( 'live_search_use_post' ),
				'show_back_to_top' => MKB_Options::option( 'show_back_to_top' ),
				'scrollspy_switch' => MKB_Options::option( 'scrollspy_switch' ),
				'toc_in_content_disable' => MKB_Options::option( 'toc_in_content_disable' ),
				'article_fancybox' => MKB_Options::option( 'article_fancybox' ),
				'article_sidebar' => MKB_Options::option( 'article_sidebar' ),
				'article_sidebar_sticky' => MKB_Options::option( 'article_sidebar_sticky' ),
				'article_sidebar_sticky_top' => MKB_Options::option( 'article_sidebar_sticky_top' ),
				'article_sidebar_sticky_min_width' => MKB_Options::option( 'article_sidebar_sticky_min_width' ),
				'back_to_top_position' => MKB_Options::option( 'back_to_top_position' ),
				'back_to_top_text' => MKB_Options::option( 'back_to_top_text' ),
				'show_back_to_top_icon' => MKB_Options::option( 'show_back_to_top_icon' ),
				'back_to_top_icon' => MKB_Options::option( 'back_to_top_icon' ),
				'back_to_site_top' => MKB_Options::option( 'back_to_site_top' ),
				'toc_scroll_offset' => MKB_Options::option( 'toc_scroll_offset' ),
				'search_mode' => MKB_Options::option( 'search_mode' ),
				'search_needle_length' => MKB_Options::option( 'search_needle_length' ),
				'search_request_fe_cache' => MKB_Options::option( 'search_request_fe_cache' ),
				'live_search_disable_mobile' => MKB_Options::option( 'live_search_disable_mobile' ),
				'live_search_disable_tablet' => MKB_Options::option( 'live_search_disable_tablet' ),
				'live_search_disable_desktop' => MKB_Options::option( 'live_search_disable_desktop' ),
				'faq_filter_open_single' => MKB_Options::option( 'faq_filter_open_single' ),
				'faq_slow_animation' => MKB_Options::option( 'faq_slow_animation' ),
				'faq_toggle_mode' => MKB_Options::option( 'faq_toggle_mode' ),
				'content_tree_widget_open_active_branch' => MKB_Options::option( 'content_tree_widget_open_active_branch' ),
				'toc_url_update' => MKB_Options::option( 'toc_url_update' ),
				'faq_url_update' => MKB_Options::option( 'faq_url_update' ),
				'faq_scroll_offset' => MKB_Options::option( 'faq_scroll_offset' ),
				'toc_headings_exclude' => MKB_Options::option( 'toc_headings_exclude' ),
				'antispam_failed_message' => MKB_Options::option( 'antispam_failed_message' ),
				'submit_success_message' => MKB_Options::option( 'submit_success_message' ),
				'submit_content_editor_skin' => MKB_Options::option( 'submit_content_editor_skin' ),
				'fh_show_delay' => MKB_Options::option( 'fh_show_delay' ),
			),
			'i18n' => self::get_i18n()
		);
	}

	/**
	 * Static i18n strings
	 * @return array
	 */
	public static function get_i18n () {
		return array(
			'no-results' => MKB_Options::option( 'search_no_results_text' ),
			'results' => MKB_Options::option( 'search_results_text' ),
			'result' => MKB_Options::option( 'search_result_text' ),
			'questions' => MKB_Options::option( 'questions_text' ),
			'question' => MKB_Options::option( 'question_text' ),
			'like_message_text' => MKB_Options::option( 'like_message_text' ),
			'dislike_message_text' => MKB_Options::option( 'dislike_message_text' ),
			'feedback_label' => MKB_Options::option( 'feedback_label' ),
			'feedback_submit_label' => MKB_Options::option( 'feedback_submit_label' ),
			'feedback_submit_request_label' => MKB_Options::option( 'feedback_submit_request_label' ),
			'feedback_info_text' => MKB_Options::option( 'feedback_info_text' ),
			'feedback_sent_text' => MKB_Options::option( 'feedback_sent_text' ),
			'submission_empty_title' => __('Title must not be empty', 'minerva-kb'),
			'submission_empty_content' => __('Content must not be empty', 'minerva-kb'),
		);
	}

	/**
	 * Assets required for admin
	 */
	public function admin_assets() {
		$screen = get_current_screen();

		wp_enqueue_media();

		wp_enqueue_style( 'minerva-kb/admin-css', MINERVA_KB_PLUGIN_URL . 'assets/css/dist/minerva-kb-admin.css', false, MINERVA_KB_VERSION );
		wp_enqueue_style( 'minerva-kb/admin-fa-css', MINERVA_KB_PLUGIN_URL . 'assets/css/vendor/font-awesome.css', false, null );

		// dynamic admin styles
		ob_start();
		?>
		#adminmenu li.menu-icon-<?php esc_attr_e(MKB_Options::option('article_cpt')); ?> .wp-menu-image img {
			width: 20px;
			margin-top: -2px;
			margin-left: -2px;
		}

		#menu-posts-<?php esc_attr_e(MKB_Options::option('article_cpt')); ?> a[href$="minerva-kb-submenu-uninstall"] {
			color: #C85C5E;
		}

		#menu-posts-<?php esc_attr_e(MKB_Options::option('article_cpt')); ?> a[href$="minerva-kb-submenu-uninstall"]:hover {
			color: red;
		}
		<?php
		wp_add_inline_style( 'minerva-kb/admin-css', ob_get_clean());

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script( 'wp-util' );

		// toastr
		wp_enqueue_style( 'minerva-kb/admin-toastr-css', MINERVA_KB_PLUGIN_URL . 'assets/css/vendor/toastr/toastr.min.css', false, '2.1.3' );
		wp_enqueue_script( 'minerva-kb/admin-toastr-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/toastr/toastr.min.js', array(), '2.1.3', true );

		/**
		 * Common Admin UI
		 */
		wp_enqueue_script( 'minerva-kb/admin-ui-js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-ui.js', array(
			'underscore',
			'jquery',
			'wp-color-picker'
		), MINERVA_KB_VERSION, true );

		wp_localize_script( 'minerva-kb/admin-ui-js', 'MinervaKB', $this->get_admin_js_data() );

		/**
		 * Page builder UI
		 */
		if (isset($screen) && $screen->id == 'page' && $screen->post_type == 'page') {
			wp_enqueue_script( 'minerva-kb/admin-page-js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-page.js', array(
				'minerva-kb/admin-ui-js'
			), MINERVA_KB_VERSION, true );
		}

		/**
		 * Taxonomy UI
		 */
		if (isset($screen) && ($screen->base == 'term' || $screen->base == 'edit-tags')) {
			wp_enqueue_script( 'minerva-kb/admin-term-js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-term.js', array(
				'minerva-kb/admin-ui-js'
			), MINERVA_KB_VERSION, true );
		}

		wp_enqueue_script( 'minerva-kb/admin-articles-js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-articles.js', array(
			'jquery',
			'jquery-ui-sortable',
			'minerva-kb/admin-ui-js'
		), MINERVA_KB_VERSION, true );
	}

	/**
	 * Data for admin js
	 * @return array
	 */
	private function get_admin_js_data() {
		return array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'pluginUrl' => MINERVA_KB_PLUGIN_URL,
			'siteUrl' => site_url(),
			'info' => array(
				'isDemoImported' => $this->info->is_demo_imported(),
				'isDemoSkipped' => $this->info->is_demo_skipped()
			),
			'nonce' => array(
				'nonce' => wp_create_nonce( $this->ajax->get_nonce() ),
				'nonceKey' => $this->ajax->get_nonce_key(),
			),
			'i18n' => array(
				'no-results' => MKB_Options::option('search_no_results_text'),
				'results' => MKB_Options::option('search_results_text'),
				'result' => MKB_Options::option('search_result_text'),
				'no-related' => __('No related articles selected', 'minerva-kb' ),
				'no-attachments' => __('No attachments added for this article', 'minerva-kb' ),
				'loading' => __('Loading...', 'minerva-kb' ),
				'tip' => __('Tip', 'minerva-kb' ),
				'info' => __('Info', 'minerva-kb' ),
				'warning' => __('Warning', 'minerva-kb' ),
				'topic' => __('Topic', 'minerva-kb' ),
				'topics' => __('Topics', 'minerva-kb' ),
				'search' => __('Search', 'minerva-kb' ),
				'anchor' => __('Anchor', 'minerva-kb' ),
				'related' => __('Related', 'minerva-kb' ),
				'submission' => __('Guest Post Form', 'minerva-kb' ),
				'faq' => __('FAQ', 'minerva-kb' ),
				'select-shortcode' => __('Select shortcode', 'minerva-kb' ),
				'loading-options' => __('Loading options...', 'minerva-kb' ),
				'configure-shortcode' => __('Configure shortcode', 'minerva-kb' ),
				'update' => __('Update', 'minerva-kb' ),
				'insert' => __('Insert', 'minerva-kb' ),
				'more-than-one-shortcode' => __('More than 1 shortcode selected, cannot parse', 'minerva-kb' ),
				'minervakb-shortcodes' => __('MinervaKB Shortcodes', 'minerva-kb' ),
				'reset-confirm' => __('Are you sure you want to reset all the settings?', 'minerva-kb' ),
			),
			'optionPrefix' => MINERVA_KB_OPTION_PREFIX,
			'settings' => MKB_Options::get()/* array(
				'enable_articles_reorder' => MKB_Options::option('enable_articles_reorder'),
				'article_cpt' => MKB_Options::option('article_cpt'),
				'article_cpt_category' => MKB_Options::option('article_cpt_category'),
				'article_cpt_tag' => MKB_Options::option('article_cpt_tag'),
				'disable_faq' => MKB_Options::option('disable_faq'),
				'info_icon' => MKB_Options::option('info_icon'),
				'tip_icon' => MKB_Options::option('tip_icon'),
				'warning_icon' => MKB_Options::option('warning_icon'),
				'faq_filter_theme' => MKB_Options::option('faq_filter_theme'),
			)*/,
			'fontAwesomeIcons' => mkb_icon_options(),
			'articleEdit' => array(
				'attachments' => MinervaKB_ArticleEdit::get_attachments_data(),
				'attachmentsTracking' => MinervaKB_ArticleEdit::get_attachments_tracking_data(),
				'attachmentsIconMap' => MinervaKB_ArticleEdit::get_attachments_icon_map(),
				'attachmentsIconDefault' => MinervaKB_ArticleEdit::get_attachments_icon_default(),
			)
		);
	}
}