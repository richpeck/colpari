<?php

if (!defined('ABSPATH')) die;

class KST_Topics_Block extends KST_Editor_Block {

    protected $ID = 'topics';

    protected $attrs_map = array(
        'topics_title' => 'title',
        'topics_title_color' => 'titleColor',
        'topics_title_size' => 'titleSize',
        'home_topics' => 'topics',
        'home_view' => 'view',
        'home_layout' => 'columns',
        'show_articles_count' => 'showCount',
        'home_topics_show_description' => 'showDescription',
        'show_all_switch' => 'showAll',
        'show_all_label' => 'showAllLabel',
        'home_topics_hide_children' => 'hideChildren',
        'home_topics_articles_limit' => 'articlesLimit',
        'home_topics_limit' => 'limit',
        'articles_count_bg' => 'countBg',
        'articles_count_color' => 'countColor',
        'show_topic_icons' => 'showTopicIcons',
        'show_article_icons' => 'showArticleIcons',
        'article_icon' => 'articleIcon',
        'topic_color' => 'topicColor',
        'force_default_topic_color' => 'forceTopicColor',
        'force_default_topic_icon' => 'forceTopicIcon',
        'box_view_item_bg' => 'boxItemBg',
        'box_view_item_hover_bg' => 'boxItemHoverBg',
        'topic_icon' => 'topicIcon',
        'use_topic_image' => 'useTopicImage',
        'image_size' => 'imageSize',
        'topic_icon_padding_top' => 'iconPaddingTop',
        'topic_icon_padding_bottom' => 'iconPaddingBottom'
    );

    public function render($attrs) {
        MKB_TemplateHelper::render_topics($this->map_attributes_to_settings($attrs));
    }

    public function custom_options() {
        return array(
            'topics_title' => array(
                'id' => 'topics_title',
                'type' => 'input',
                'label' => __( 'Topics title', 'minerva-kb' ),
                'default' => __( 'Popular topics', 'minerva-kb' )
            ),
            'topics_title_size' => array(
                'id' => 'topics_title_size',
                'type' => 'css_size',
                'label' => __( 'Topics title font size', 'minerva-kb' ),
                'default' => array('unit' => 'em', 'size' => '2'),
                'description' => 'Use any CSS value, for ex. 2em or 20px'
            ),
            'topics_title_color' => array(
                'id' => 'topics_title_color',
                'type' => 'color',
                'label' => __( 'Topics title color', 'minerva-kb' ),
                'default' => '#333333'
            )
        );
    }
}

