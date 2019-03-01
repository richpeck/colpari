<?php

if (!defined('ABSPATH')) die;

class KST_Info_Block extends KST_Editor_Block {

    protected $ID = 'info';

    protected $attrs_map = array(
        'message' => 'message'
    );

    public function render($attrs) {
        $attributes = wp_parse_args($attrs, array(
            'message' => ''
        ));

        ?>
        <div class="mkb-info">
            <div class="mkb-info__icon">
                <i class="fa fa-lg <?php echo esc_attr(MKB_Options::option('info_icon')); ?>"></i>
            </div>
            <div class="mkb-info__content">
                <?php echo wp_kses_post( $attributes['message']); ?>
            </div>
        </div>
        <?php
    }
    /**
     * TODO: add some default $attributes array, without mapping to options
     */
    public function custom_options() {
        return array(
            'message' => array(
                'id' => 'message',
                'type' => 'input',
                'default' => ''
            )
        );
    }
}
