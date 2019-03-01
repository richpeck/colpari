<?php

if (!defined('ABSPATH')) die;

class KST_FAQ_Block extends KST_Editor_Block {

    protected $ID = 'faq';

    protected $attrs_map = array(
        'home_faq_title' => 'title',
        'home_faq_title_size' => 'titleSize',
        'home_faq_title_color' => 'titleColor',
        'home_faq_margin_top' => 'marginTop',
        'home_faq_margin_bottom' => 'marginBottom',
        'home_faq_limit_width_switch' => 'limitWidth',
        'home_faq_width_limit' => 'width',
        'home_faq_controls_margin_top' => 'controlsMarginTop',
        'home_faq_controls_margin_bottom' => 'controlsMarginBottom',
        'home_show_faq_filter' => 'showFilter',
        'home_show_faq_toggle_all' => 'showToggleAll',
        'home_faq_categories' => 'categories',
        'home_show_faq_categories' => 'showCategories',
        'home_show_faq_category_count' => 'showCount',
    );

    public function render($attrs) {
        $settings = $this->map_attributes_to_settings($attrs);

        // flag to render post content properly
        $settings['is_block_editor'] = true;

        MKB_TemplateHelper::render_faq($settings);
    }
}

