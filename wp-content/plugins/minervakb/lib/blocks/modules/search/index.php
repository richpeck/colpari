<?php

if (!defined('ABSPATH')) die;

class KST_Search_Block extends KST_Editor_Block {

    protected $ID = 'search';

    protected $attrs_map = array(
        'search_title' => 'title',
        'search_title_size' => 'titleSize',
        'search_theme' => 'theme',
        'search_min_width' => 'minWidth',
        'search_container_padding_top' => 'topPadding',
        'search_container_padding_bottom' => 'bottomPadding',
        'search_placeholder' => 'placeholder',
        'search_topics' => 'topics',
        'disable_autofocus' => 'noFocus',
        'show_search_tip' => 'showTip',
        'search_tip' => 'tip',
        'show_topic_in_results' => 'showTopic',
        'search_results_multiline' => 'resultsMultiline',
        'search_result_topic_label' => 'topicLabel',
        'search_title_color' => 'titleColor',
        'search_border_color' => 'borderColor',
        'search_container_bg' => 'bg',
        'search_container_image_bg' => 'imageBg',
        'add_gradient_overlay' => 'addGradient',
        'search_container_gradient_from' => 'gradientFrom',
        'search_container_gradient_to' => 'gradientTo',
        'search_container_gradient_opacity' => 'gradientOpacity',
        'add_pattern_overlay' => 'addPattern',
        'search_container_image_pattern' => 'pattern',
        'search_container_image_pattern_opacity' => 'patternOpacity',
        'search_tip_color' => 'tipColor',
        'search_results_topic_bg' => 'topicBg',
        'search_results_topic_color' => 'topicColor',
        'search_results_topic_use_custom' => 'topicCustomColors',
        'search_icons_left' => 'iconsLeft',
        'show_search_icon' => 'showSearchIcon',
        'search_icon' => 'searchIcon',
        'search_clear_icon' => 'clearIcon'
    );

    public function render($attrs) {
        MKB_TemplateHelper::render_search($this->map_attributes_to_settings($attrs));
    }
}

