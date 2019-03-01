<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

class MinervaKB_DynamicStyles {

	private $info;

	/**
	 * Constructor
	 * @param $deps
	 */
	public function __construct($deps) {
		$this->setup_dependencies( $deps );
	}

	/**
	 * Sets up dependencies
	 * @param $deps
	 */
	private function setup_dependencies($deps) {
		if (isset($deps['info'])) {
			$this->info = $deps['info'];
		}
	}

	/**
	 * Returns minified inline css
	 * @return mixed
	 */
	public function get_css () {
		ob_start();
		$this->print_css();
		return $this->css_compress(ob_get_clean());
	}

	/**
	 * Returns minified custom css
	 * @return mixed
	 */
	public function get_custom_css () {
		return $this->css_compress(MKB_Options::option('custom_css'));
	}

	/**
	 * Outputs all inline styles
	 */
	public function print_css() {

$container_width = MKB_Options::option('container_width');
?>
.mkb-container {
	width: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($container_width)); ?>;
}
<?php

$content_width_setting = MKB_Options::option('content_width');

try {
	$content_width = (int) $content_width_setting['size'];
	$sidebar_width = 100 - ($content_width);
} catch (Exception $e) {
	$content_width = 70;
	$sidebar_width = 30;
}

$sticky_sidebar_min_width = MKB_Options::option('article_sidebar_sticky_min_width');
$article_sidebar_sticky_top = MKB_Options::option('article_sidebar_sticky_top');
?>
.mkb-content-main.mkb-content-main--has-sidebar {
	width: <?php echo esc_attr($content_width); ?>%;
}

.mkb-sidebar {
	width: <?php echo esc_attr($sidebar_width); ?>%;
}

@media (min-width: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($sticky_sidebar_min_width)); ?>) {
	.mkb-sidebar.mkb-fixed {
		position: fixed;
		top: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($article_sidebar_sticky_top)); ?>;
	}

	.mkb-sidebar.mkb-fixed-bottom {
		position: absolute;
	}
}

.kb-search__results-summary .kb-search-request-indicator,
.mkb-widget .kb-search__results-summary .kb-search-request-indicator {
	color: <?php echo esc_attr(MKB_Options::option( 'search_request_icon_color' )); ?>;
}

.mkb-anchors-list__item-link.active:before {
	background: <?php echo esc_attr(MKB_Options::option( 'scrollspy_bg' )); ?>;
}

.mkb-anchors-list__item-link.active .mkb-anchors-list__item-link-label {
	color: <?php echo esc_attr(MKB_Options::option( 'scrollspy_color' )); ?>;
}

.mkb-article-text .mkb-anchors-list,
.mkb-single-content .mkb-anchors-list {
	width: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string(MKB_Options::option('toc_max_width'))); ?>;
}

.mkb-article-text .mkb-anchors-list.mkb-anchors-list--hierarchical,
.mkb-single-content .mkb-anchors-list.mkb-anchors-list--hierarchical {
	width: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string(MKB_Options::option('toc_max_width_h'))); ?>;
}

.kb-topic__count,
.mkb-article-item__topic {
	color: <?php echo esc_attr(MKB_Options::option( 'articles_count_color' )); ?>;
	background: <?php echo esc_attr(MKB_Options::option( 'articles_count_bg' )); ?>;
}

<?php if (MKB_Options::option( 'home_topics_stretch' )): ?>
.kb-topic__inner {
	height: 100%;
}
<?php endif; ?>

.mkb-article-extra__like,
.mkb-article-extra__like:focus,
.mkb-article-extra__like:active,
.mkb-article-extra__like:visited,
.mkb-article-extra__like:hover,
.mkb-article-item__likes i {
	color: <?php echo esc_attr(MKB_Options::option( 'like_color' )); ?>;
}

.mkb-article-extra__message {
	border-color: <?php echo esc_attr(MKB_Options::option( 'rating_message_border_color' )); ?>;
	background: <?php echo esc_attr(MKB_Options::option( 'rating_message_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'rating_message_color' )); ?>;
}

.mkb-article-extra__feedback-sent-message {
	border-color: <?php echo esc_attr(MKB_Options::option( 'feedback_message_border_color' )); ?>;
	background: <?php echo esc_attr(MKB_Options::option( 'feedback_message_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'feedback_message_color' )); ?>;
}

.mkb-article-extra__feedback-form-submit a {
	background: <?php echo esc_attr(MKB_Options::option( 'feedback_submit_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'feedback_submit_color' )); ?>;
}

.mkb-article-extra__like.mkb-voted,
.mkb-article-extra__like.mkb-voted:focus,
.mkb-article-extra__like.mkb-voted:active,
.mkb-article-extra__like.mkb-voted:visited,
.mkb-article-extra__like.mkb-voted:hover {
	background-color: <?php echo esc_attr(MKB_Options::option( 'like_color' )); ?>;
	border-color: <?php echo esc_attr(MKB_Options::option( 'like_color' )); ?>;
	color: #efefef;
}

.mkb-article-extra__dislike,
.mkb-article-extra__dislike:focus,
.mkb-article-extra__dislike:active,
.mkb-article-extra__dislike:visited,
.mkb-article-extra__dislike:hover,
.mkb-article-item__dislikes i {
	color: <?php echo esc_attr(MKB_Options::option( 'dislike_color' )); ?>;
}

.mkb-article-extra__dislike.mkb-voted,
.mkb-article-extra__dislike.mkb-voted:focus,
.mkb-article-extra__dislike.mkb-voted:active,
.mkb-article-extra__dislike.mkb-voted:visited,
.mkb-article-extra__dislike.mkb-voted:hover {
	background-color: <?php echo esc_attr(MKB_Options::option( 'dislike_color' )); ?>;
	border-color: <?php echo esc_attr(MKB_Options::option( 'dislike_color' )); ?>;
	color: #efefef;
}

.kb-search .kb-search__result-topic-name {
	color: <?php echo esc_attr(MKB_Options::option( 'search_results_topic_color' )); ?>;
	background: <?php echo esc_attr(MKB_Options::option( 'search_results_topic_bg' )); ?>;
}

.kb-topic .kb-topic__box-header,
.kb-topic .kb-topic__title-link {
	color: <?php echo esc_attr(MKB_Options::option( 'topic_color' )); ?>;
}

.kb-header {
	background: <?php echo esc_attr(MKB_Options::option( 'search_container_bg' )); ?>;
}

.kb-faq .kb-faq__questions .kb-faq__question-title {
	font-size: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string(MKB_Options::option( 'faq_question_font_size' ))); ?>;
}

.kb-faq__question-title {
	background: <?php echo esc_attr(MKB_Options::option( 'faq_question_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'faq_question_color' )); ?>;
}

.kb-faq__question-title:hover {
	background: <?php echo esc_attr(MKB_Options::option( 'faq_question_bg_hover' )); ?>;
}

.kb-faq__answer {
	background: <?php echo esc_attr(MKB_Options::option( 'faq_answer_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'faq_answer_color' )); ?>;
}

.kb-faq__no-results {
	background: <?php echo esc_attr(MKB_Options::option( 'faq_no_results_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'faq_no_results_color' )); ?>;
}

.kb-faq__count {
	background: <?php echo esc_attr(MKB_Options::option( 'faq_count_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'faq_count_color' )); ?>;
}

.kb-faq .kb-faq__toggle-all .kb-faq__toggle-all-link {
	background: <?php echo esc_attr(MKB_Options::option( 'faq_toggle_all_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'faq_toggle_all_color' )); ?>;
}

.kb-faq .kb-faq__toggle-all .kb-faq__toggle-all-link:hover {
	background: <?php echo esc_attr(MKB_Options::option( 'faq_toggle_all_bg_hover' )); ?>;
}

<?php
	$faq_category_margin_top = MKB_Options::option( 'faq_category_margin_top' );
	$faq_category_margin_bottom = MKB_Options::option( 'faq_category_margin_bottom' );
?>

.kb-faq .kb-faq__category-title {
	margin-top: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($faq_category_margin_top)); ?>;
	margin-bottom: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($faq_category_margin_bottom)); ?>;
}

/* Shortcodes */

.mkb-info {
	background: <?php echo esc_attr(MKB_Options::option( 'info_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'info_color' )); ?>;
	border-color: <?php echo esc_attr(MKB_Options::option( 'info_border' )); ?>;
}

.mkb-info__icon {
	color: <?php echo esc_attr(MKB_Options::option( 'info_icon_color' )); ?>;
}

.mkb-tip {
	background: <?php echo esc_attr(MKB_Options::option( 'tip_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'tip_color' )); ?>;
	border-color: <?php echo esc_attr(MKB_Options::option( 'tip_border' )); ?>;
}

.mkb-tip__icon {
	color: <?php echo esc_attr(MKB_Options::option( 'tip_icon_color' )); ?>;
}

.mkb-warning {
	background: <?php echo esc_attr(MKB_Options::option( 'warning_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'warning_color' )); ?>;
	border-color: <?php echo esc_attr(MKB_Options::option( 'warning_border' )); ?>;
}

.mkb-warning__icon {
	color: <?php echo esc_attr(MKB_Options::option( 'warning_icon_color' )); ?>;
}

.mkb-related-content {
	background: <?php echo esc_attr(MKB_Options::option( 'related_content_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'related_content_label_color' )); ?>;
	border-color: <?php echo esc_attr(MKB_Options::option( 'related_content_border' )); ?>;
}

.mkb-related-content a {
	color: <?php echo esc_attr(MKB_Options::option( 'related_content_links_color' )); ?>;
}

.kb-topic.kb-topic--box-view .kb-topic__inner {
	background: <?php echo esc_attr(MKB_Options::option( 'box_view_item_bg' )); ?>;
}

.kb-topic.kb-topic--box-view .kb-topic__inner:hover {
	background: <?php echo esc_attr(MKB_Options::option( 'box_view_item_hover_bg' )); ?>;
}

<?php if (MKB_Options::option( 'widget_style_on' )): ?>
.mkb-widget {
	background: <?php echo esc_attr(MKB_Options::option( 'widget_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'widget_color' )); ?>;
}

.mkb-widget a {
	color: <?php echo esc_attr(MKB_Options::option( 'widget_link_color' )); ?>;
}

.mkb-widget i.fa {
	color: <?php echo esc_attr(MKB_Options::option( 'widget_icon_color' )); ?>;
}

.mkb-widget .mkb-widget-title {
	color: <?php echo esc_attr(MKB_Options::option( 'widget_heading_color' )); ?>;
}
<?php endif; ?>

<?php if (!MKB_Options::option( 'widget_icons_on' )): ?>
	.mkb-widget.widget.mkb_recent_articles_widget ul li,
	.mkb-widget.widget.mkb_recent_topics_widget ul li {
		padding-left: 0;
	}

	.mkb-widget.mkb_content_tree_widget .mkb-widget-content-tree__article-title {
		padding-left: 0.6em;
	}
<?php endif; ?>

.mkb-widget-content-tree__article--active .mkb-widget-content-tree__article-title:after {
	background: <?php echo esc_attr(MKB_Options::option( 'content_tree_widget_active_color' )); ?>;
}

<?php if (MKB_Options::option( 'typography_on' )): ?>
	<?php
	$content_size = MKB_Options::option( 'content_font_size' );
	$content_line = MKB_Options::option( 'content_line_height' );

	$h1_size = MKB_Options::option( 'h1_font_size' );
	$h2_size = MKB_Options::option( 'h2_font_size' );
	$h3_size = MKB_Options::option( 'h3_font_size' );
	$h4_size = MKB_Options::option( 'h4_font_size' );
	$h5_size = MKB_Options::option( 'h5_font_size' );
	$h6_size = MKB_Options::option( 'h6_font_size' );

	$widget_size = MKB_Options::option( 'widget_font_size' );
	$widget_heading_size = MKB_Options::option( 'widget_heading_font_size' );
?>

.mkb-root,
.kb-search,
.kb-search input,
.mkb-shortcode-container,
#mkb-client-editor,
.mkb-floating-helper-wrap,
.wp-block[data-type^=minervakb] {
	font-family: '<?php echo esc_attr(MKB_Options::option( 'style_font' )); ?>';
}

.mkb-root .kb-search ::-webkit-input-placeholder { /* Chrome/Opera/Safari */
	font-family: '<?php echo esc_attr(MKB_Options::option( 'style_font' )); ?>';
}

.mkb-root .kb-search ::-moz-placeholder { /* Firefox 19+ */
	font-family: '<?php echo esc_attr(MKB_Options::option( 'style_font' )); ?>';
}

.mkb-root .kb-search :-ms-input-placeholder { /* IE 10+ */
	font-family: '<?php echo esc_attr(MKB_Options::option( 'style_font' )); ?>';
}

.mkb-root .kb-search :-moz-placeholder { /* Firefox 18- */
	font-family: '<?php echo esc_attr(MKB_Options::option( 'style_font' )); ?>';
}

.mkb-root .mkb-single-content .mkb-article-text,
.mkb-root .mkb-related-articles,
#mkb-client-editor {
	font-size: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($content_size)); ?>;
}

.mkb-root .mkb-single-content .mkb-article-text {
	line-height: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($content_line)); ?>;
}

@media (min-width: 481px) {
	.mkb-root h1 {
		font-size: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($h1_size)); ?>;
	}

	.mkb-root h2 {
		font-size: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($h2_size)); ?>;
	}

	.mkb-root h3 {
		font-size: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($h3_size)); ?>;
	}

	.mkb-root h4 {
		font-size: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($h4_size)); ?>;
	}

	.mkb-root h5 {
		font-size: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($h5_size)); ?>;
	}

	.mkb-root h6 {
		font-size: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($h6_size)); ?>;
	}
}

.mkb-widget {
	font-size: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($widget_size)); ?>;
}

.mkb-widget .mkb-widget-title {
	font-size: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($widget_heading_size)); ?>;
}

<?php endif; ?>

.mkb-root .mkb-article-text,
.mkb-root .mkb-article-header,
.mkb-root .mkb-article-item__excerpt {
	color: <?php echo esc_attr(MKB_Options::option( 'text_color' )); ?>;
}

.mkb-root .mkb-article-text a,
.mkb-root .mkb-article-header a,
.mkb-article-item--detailed .mkb-entry-title a {
	color: <?php echo esc_attr(MKB_Options::option( 'text_link_color' )); ?>;
}

.mkb-root h1 {
	color: <?php echo esc_attr(MKB_Options::option( 'h1_color' )); ?>;
}

.mkb-root h2 {
	color: <?php echo esc_attr(MKB_Options::option( 'h2_color' )); ?>;
}

.mkb-root h3 {
	color: <?php echo esc_attr(MKB_Options::option( 'h3_color' )); ?>;
}

.mkb-root h4 {
	color: <?php echo esc_attr(MKB_Options::option( 'h4_color' )); ?>;
}

.mkb-root h5 {
	color: <?php echo esc_attr(MKB_Options::option( 'h5_color' )); ?>;
}

.mkb-root h6 {
	color: <?php echo esc_attr(MKB_Options::option( 'h6_color' )); ?>;
}

<?php
$breadcrumbs_size = MKB_Options::option( 'breadcrumbs_font_size' );
$breadcrumbs_align = MKB_Options::option( 'breadcrumbs_align' );
$breadcrumbs_top_padding = MKB_Options::option( 'breadcrumbs_top_padding' );
$breadcrumbs_bottom_padding = MKB_Options::option( 'breadcrumbs_bottom_padding' );
$breadcrumbs_left_padding = MKB_Options::option( 'breadcrumbs_left_padding' );
$breadcrumbs_right_padding = MKB_Options::option( 'breadcrumbs_right_padding' );
$breadcrumbs_bg = MKB_Options::option( 'breadcrumbs_bg_color' );
$breadcrumbs_text_color = MKB_Options::option( 'breadcrumbs_text_color' );
$breadcrumbs_link_color = MKB_Options::option( 'breadcrumbs_link_color' );
$breadcrumbs_image_bg = MKB_SettingsBuilder::media_url(MKB_Options::option( 'breadcrumbs_image_bg' ));
$breadcrumbs_add_gradient = MKB_Options::option( 'breadcrumbs_add_gradient' );
$breadcrumbs_add_pattern = MKB_Options::option( 'breadcrumbs_add_pattern' );
$breadcrumbs_add_shadow = MKB_Options::option( 'breadcrumbs_add_shadow' );
$breadcrumbs_inset_shadow = MKB_Options::option( 'breadcrumbs_inset_shadow' );
?>

.mkb-breadcrumbs {
	font-size: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($breadcrumbs_size)); ?>;
	text-align: <?php echo esc_attr($breadcrumbs_align); ?>;
	background-color: <?php echo esc_attr($breadcrumbs_bg); ?>;
	color: <?php echo esc_attr($breadcrumbs_text_color); ?>;
	padding-top: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($breadcrumbs_top_padding)); ?>;
	padding-left: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($breadcrumbs_left_padding)); ?>;
	padding-right: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($breadcrumbs_right_padding)); ?>;
	padding-bottom: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($breadcrumbs_bottom_padding)); ?>;
<?php if ($breadcrumbs_image_bg): ?>
	background-image: url(<?php echo esc_url($breadcrumbs_image_bg); ?>);
<?php endif; ?>
<?php if ($breadcrumbs_add_shadow): ?>
	<?php if ($breadcrumbs_inset_shadow): ?>
		box-shadow: inset 0 0 5px rgba(0,0,0,0.25);
	<?php else: ?>
		box-shadow: 0 1px 3px rgba(0,0,0,0.2);
	<?php endif; ?>
<?php endif; ?>
}

.mkb-breadcrumbs li a {
	color: <?php echo esc_attr($breadcrumbs_link_color); ?>;
}

<?php if ($breadcrumbs_add_gradient): ?>
.mkb-breadcrumbs .mkb-breadcrumbs__gradient {
	background: linear-gradient(45deg, <?php echo esc_attr(MKB_Options::option( 'breadcrumbs_gradient_from' )); ?> 0%, <?php echo esc_attr(MKB_Options::option( 'breadcrumbs_gradient_to' )); ?> 100%);
	opacity: <?php echo esc_attr(MKB_Options::option( 'breadcrumbs_gradient_opacity' )); ?>;
}
<?php endif; ?>

<?php if ($breadcrumbs_add_pattern): ?>
.mkb-breadcrumbs .mkb-breadcrumbs__pattern {
	background-image: url(<?php echo esc_url(MKB_SettingsBuilder::media_url(MKB_Options::option( 'breadcrumbs_image_pattern' ))); ?>);
	opacity: <?php echo esc_attr(MKB_Options::option( 'breadcrumbs_image_pattern_opacity' )); ?>;
}
<?php endif; ?>

<?php
// page paddings
$single_top_padding = MKB_Options::option( 'single_top_padding' );
$single_bottom_padding = MKB_Options::option( 'single_bottom_padding' );

$topic_top_padding = MKB_Options::option( 'topic_top_padding' );
$topic_bottom_padding = MKB_Options::option( 'topic_bottom_padding' );

$home_top_padding = MKB_Options::option( 'home_top_padding' );
$home_bottom_padding = MKB_Options::option( 'home_bottom_padding' );

$search_top_padding = MKB_Options::option( 'search_results_top_padding' );
$search_bottom_padding = MKB_Options::option( 'search_results_bottom_padding' );
?>
.mkb-single .mkb-root {
	padding-top: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($single_top_padding)); ?>;
	padding-bottom: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($single_bottom_padding)); ?>;
}

.mkb-archive .mkb-root {
	padding-top: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($topic_top_padding)); ?>;
	padding-bottom: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($topic_bottom_padding)); ?>;
}

.mkb-settings-home-page .mkb-root {
	padding-top: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($home_top_padding)); ?>;
	padding-bottom: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($home_bottom_padding)); ?>;
}

.mkb-search .mkb-root {
	padding-top: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($search_top_padding)); ?>;
	padding-bottom: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($search_bottom_padding)); ?>;
}

<?php if ($this->info->is_builder_home()):
	$page_top_padding = MKB_PageOptions::option('page_top_padding');
	$page_bottom_padding = MKB_PageOptions::option('page_bottom_padding');
?>
.mkb-builder-home-page .mkb-root {
	padding-top: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($page_top_padding)); ?>;
	padding-bottom: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($page_bottom_padding)); ?>;
}
<?php endif; ?>
<?php if (MKB_Options::option( 'restrict_on' )): ?>
.mkb-restricted-message {
	background-color: <?php echo esc_attr(MKB_Options::option( 'restrict_message_bg' )); ?>;
	border-color: <?php echo esc_attr(MKB_Options::option( 'restrict_message_border' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'restrict_message_color' )); ?>;
}

.mkb-restricted-message .mkb-restricted-message__icon {
	color: <?php echo esc_attr(MKB_Options::option( 'restrict_message_icon_color' )); ?>;
}

.mkb-article-restricted-excerpt.mkb-article-restricted-excerpt--overlayed:after {
	background: linear-gradient(0deg, <?php echo esc_attr(MKB_Options::option( 'restrict_article_excerpt_gradient_start' ));
?> 0%, rgba(255,255,255,0) 100%);
}
	<?php if (!MKB_Options::option('restrict_disable_form_styles')):

		$form_width = MKB_Options::option('restrict_login_form_width');
		?>

.mkb-restricted-login.mkb-restricted-login--custom {
	text-align: <?php echo esc_attr(MKB_Options::option( 'restrict_login_form_align' )); ?>;
}

.mkb-restricted-login.mkb-restricted-login--custom form[name="loginform"] {
	width: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($form_width)); ?>;
	background: <?php echo esc_attr(MKB_Options::option( 'restrict_login_bg' )); ?>;
}

.mkb-restricted-login.mkb-restricted-login--custom form[name="loginform"] label {
	color: <?php echo esc_attr(MKB_Options::option( 'restrict_login_label_color' )); ?>;
}

.mkb-restricted-login.mkb-restricted-login--custom form[name="loginform"] input[type="text"],
.mkb-restricted-login.mkb-restricted-login--custom form[name="loginform"] input[type="password"] {
	background: <?php echo esc_attr(MKB_Options::option( 'restrict_login_input_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'restrict_login_input_text_color' )); ?>;
}

.mkb-restricted-login.mkb-restricted-login--custom form[name="loginform"] input[type="submit"] {
	background: <?php echo esc_attr(MKB_Options::option( 'restrict_login_btn_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'restrict_login_btn_color' )); ?>;
	box-shadow: 1px 3px 1px <?php echo esc_attr(MKB_Options::option( 'restrict_login_btn_shadow' )); ?>;
}

.mkb-restricted-login.mkb-restricted-login--custom form[name="loginform"] .mkb-register-link a {
	background: <?php echo esc_attr(MKB_Options::option( 'restrict_register_btn_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'restrict_register_btn_color' )); ?>;
	box-shadow: 1px 3px 1px <?php echo esc_attr(MKB_Options::option( 'restrict_register_btn_shadow' )); ?>;
}
	<?php endif; ?>
<?php endif; ?>
.mkb-search-match {
	background: <?php echo esc_attr(MKB_Options::option( 'search_results_match_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'search_results_match_color' )); ?>;
}

.mkb-pagination ul li {
	background: <?php echo esc_attr(MKB_Options::option( 'pagination_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'pagination_color' )); ?>;
}

.mkb-pagination ul li a {
	color: <?php echo esc_attr(MKB_Options::option( 'pagination_link_color' )); ?>;
}

/**
 * Guest posts
 */
.mkb-client-submission-send {
	background: <?php echo esc_attr(MKB_Options::option( 'submit_send_button_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'submit_send_button_color' )); ?>;
}

/**
 * Versions
 */
.mkb-article-version,
.mkb-article-version:hover,
.mkb-article-version:active,
.mkb-article-version:focus,
.mkb-article-version:visited
.mkb-article-versions a,
.mkb-article-versions a:hover,
.mkb-article-versions a:active,
.mkb-article-versions a:focus,
.mkb-article-versions a:visited {
	background: <?php echo esc_attr(MKB_Options::option( 'version_label_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'version_label_text_color' )); ?>;
}

/**
 * Floating Helper
 */

.mkb-floating-helper-btn {
	background: <?php echo esc_attr(MKB_Options::option( 'fh_btn_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'fh_btn_color' )); ?>;
	width: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string(MKB_Options::option( 'fh_btn_size' ))); ?>;
	height: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string(MKB_Options::option( 'fh_btn_size' ))); ?>;
}

.mkb-floating-helper-btn .mkb-floating-helper-btn-icon {
	font-size: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string(MKB_Options::option( 'fh_btn_icon_size' ))); ?>;
	height: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string(MKB_Options::option( 'fh_btn_icon_size' ))); ?>;
}

.mkb-floating-helper-content {
	background: <?php echo esc_attr(MKB_Options::option( 'fh_content_bg' )); ?>;
	color: <?php echo esc_attr(MKB_Options::option( 'fh_content_color' )); ?>;
	width: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string(MKB_Options::option( 'fh_content_width' ))); ?>;
}

.mkb-floating-helper-content .kb-header .kb-search__results {
	max-height: <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string(MKB_Options::option( 'fh_content_search_max_height' ))); ?>;
}

.mkb-floating-helper-content .kb-header .kb-search__form {
	max-height: calc(43px + <?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string(MKB_Options::option( 'fh_content_search_max_height' ))); ?>);
}

<?php
	}

	// TODO: split to modules
	/**
	 * Common styles
	 */
	private function common_css () {

	}

	/**
	 * Home related styles
	 */
	private function home_css () {

	}

	/**
	 * Article related styles
	 */
	private function single_css () {

	}

	/**
	 * Archive related styles
	 */
	private function archive_css () {

	}

	/**
	 * CSS minifier
	 * @param $minify
	 * @return mixed
	 */
	private function css_compress( $minify ) {
		/* remove tabs, newlines, and multiple spaces etc. */
		$minify = str_replace( array("\r\n", "\r", "\n", "\t"), '', $minify );
		$minify = str_replace( array("  ", "   ", "    "), ' ', $minify );

		return $minify;
	}
}
