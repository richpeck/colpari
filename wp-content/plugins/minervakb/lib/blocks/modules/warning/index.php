<?php

if (!defined('ABSPATH')) die;

class KST_Warning_Block extends KST_Editor_Block {

    protected $ID = 'warning';

    protected $attrs_map = array(
        'message' => 'message'
    );

    public function render($attrs) {
        $attributes = wp_parse_args($attrs, array(
            'message' => ''
        ));

        ?>
        <div class="mkb-warning">
            <div class="mkb-warning__icon">
                <i class="fa fa-lg <?php esc_attr_e(MKB_Options::option( 'warning_icon' )); ?>"></i>
            </div>
            <div class="mkb-warning__content">
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
