<?php

if (!defined('ABSPATH')) die;

class KST_Topic_Block extends KST_Editor_Block {

    protected $ID = 'topic';

    protected $attrs_map = array(
        'id' => 'id',
        'view' => 'view',
        'columns' => 'columns',
        'limit' => 'limit'
    );

    public function render($attrs) {
        MKB_TemplateHelper::render_topic($this->map_attributes_to_settings($attrs));
    }

    public function custom_options() {
        return array(
            'id' => array(
                'id' => 'topics_title',
                'type' => 'term_single_select',
                'label' => __( 'Select topic', 'minerva-kb' ),
                'tax' => MKB_Options::option('article_cpt_category'),
                'default' => ''
            ),
            'view' => array(
                'id' => 'view',
                'type' => 'image_select',
                'label' => __( 'Child topics view (if any)', 'minerva-kb' ),
                'options' => array(
                    'list' => array(
                        'label' => __( 'List view', 'minerva-kb' ),
                        'img' => MINERVA_KB_IMG_URL . 'list-view.png'
                    ),
                    'box' => array(
                        'label' => __( 'Box view', 'minerva-kb' ),
                        'img' => MINERVA_KB_IMG_URL . 'box-view.png'
                    )
                ),
                'default' => 'box'
            ),
            'columns' => array(
                'id' => 'columns',
                'type' => 'image_select',
                'label' => __( 'Child topics layout (if any)', 'minerva-kb' ),
                'options' => array(
                    '2col' => array(
                        'label' => __( '2 columns', 'minerva-kb' ),
                        'img' => MINERVA_KB_IMG_URL . 'col-2.png'
                    ),
                    '3col' => array(
                        'label' => __( '3 columns', 'minerva-kb' ),
                        'img' => MINERVA_KB_IMG_URL . 'col-3.png'
                    ),
                    '4col' => array(
                        'label' => __( '4 columns', 'minerva-kb' ),
                        'img' => MINERVA_KB_IMG_URL . 'col-4.png'
                    ),
                ),
                'default' => '3col'
            ),
            'limit' => array(
                'id' => 'limit',
                'type' => 'input',
                'label' => __( 'Number of articles to display', 'minerva-kb' ),
                'default' => 5,
                'description' => __( 'Use -1 to display all', 'minerva-kb' ),
            )
        );
    }
}

