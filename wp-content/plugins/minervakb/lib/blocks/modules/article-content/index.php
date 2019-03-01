<?php

if (!defined('ABSPATH')) die;

class KST_ArticleContent_Block extends KST_Editor_Block {

    protected $ID = 'article-content';

    protected $attrs_map = array();

    public function render($attrs) {
        if (defined( 'REST_REQUEST' ) && REST_REQUEST) {
            return;
        }

        if (!is_singular(MKB_Options::option('article_cpt'))) {
            echo __('<p>Warning! Article content block should be used only in single article custom templates</p>', 'minervakb');

            return;
        }

        do_action('minerva_single_article_content');
    }
}
