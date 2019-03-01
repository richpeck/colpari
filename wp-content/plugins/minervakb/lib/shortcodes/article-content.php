<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_ArticleContentShortcode extends KST_Shortcode implements KST_Shortcode_Interface {

	protected $ID = 'article-content';
	protected $name = 'Article Content';
	protected $description = 'Displays article content';
	protected $icon = 'fa fa-text';
	protected $has_content = false;

	public function render($atts, $content = '') {
		do_action('minerva_single_article_content');
	}
}