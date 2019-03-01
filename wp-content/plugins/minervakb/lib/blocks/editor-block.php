<?php
/**
 * Base class for all block editor blocks
 * Class KST_Editor_Block
 */
abstract class KST_Editor_Block {

    protected $ID = '';

    // attrs map array
    protected $attrs_map = array();

    public function __construct() {
        add_action( 'init', array( $this, 'register') );
    }

    abstract public function render($attrs);

    public function get_ID() {
        return $this->ID;
    }

    /**
     * Registers a shortcode for WP
     */
    public function register() {

        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        register_block_type( 'minervakb/' . $this->ID, array(
            'attributes'      => $this->get_block_attributes(),
            'render_callback' => array($this, 'wrapped_render')
        ));
    }

    public function wrapped_render($attrs) {
        $classnames = array('mkb-shortcode-container');

        if (isset($attrs['className'])) {
            array_push($classnames, $attrs['className']);
        }

        ob_start();

        ?><div class="<?php esc_attr_e(implode(' ', $classnames)); ?>"><?php
            $this->render($attrs);
        ?></div><?php

        return ob_get_clean();
    }

    public function get_block_attributes () {
        $all_settings = $this->get_block_settings();
        $attributes = array();

        foreach($this->attrs_map as $option_id => $attr_id) {
            if (!isset($all_settings[$option_id])) {
                continue;
            }

            $option = $all_settings[$option_id];

            $attributes[$attr_id] = array(
                'type' => $this->get_option_prop_type($option['type']),
                'default' => $option['default']
            );
        }

        return $attributes;
    }

    public function get_block_options_info () {
        $all_settings = $this->get_block_settings();
        $attributes = array();

        foreach($this->attrs_map as $option_id => $attr_id) {
            if (!isset($all_settings[$option_id])) {
                continue;
            }

            $attributes[$attr_id] = $all_settings[$option_id];
        }

        return $attributes;
    }

    private function get_block_settings() {
        return array_merge(MKB_Options::get_options_by_id(), $this->custom_options());
    }

    private function get_option_prop_type($option_type) {
        $types_map = array(
            'input' => 'string',
            'css_size' => 'object',
            'media' => 'object',
            'color' => 'string',
            'checkbox' => 'boolean',
            'range' => 'number'
        );

        return isset($types_map[$option_type]) ? $types_map[$option_type] : 'string';
    }

    /**
     * Maps user friendly params to real method properties
     * @param $args
     *
     * @return array
     */
    final protected function map_attributes_to_settings($attrs) {
        $settings = array();

        foreach($this->attrs_map as $option_id => $block_id) {
            if (isset($attrs[$block_id])) {
                $settings[$option_id] = $attrs[$block_id];
            }
        }

        return $settings;
    }

    public function custom_options() {
        return array();
    }
}