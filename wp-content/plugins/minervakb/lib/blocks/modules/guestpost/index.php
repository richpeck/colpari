<?php

if (!defined('ABSPATH')) die;

class KST_GuestPost_Block extends KST_Editor_Block {

    protected $ID = 'guestpost';

    protected $attrs_map = array();

    public function render($attrs) {
        MKB_TemplateHelper::render_guestpost();
    }
}
