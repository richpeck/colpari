<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 *
 */
class EAMetaFields
{

    // We need to compile with PHP 5.2
    // const T_INPUT    = 'INPUT';
    // const T_TEXTAREA = 'TEXTAREA';
    // const T_SELECT   = 'SELECT';

    function __construct()
    {
    }

    static function getMetaFieldsType()
    {
        return array(
            'INPUT'    => __('Input', 'easy_appointments'),
            'TEXTAREA' => __('Select', 'easy_appointments'),
            'SELECT'   => __('Text', 'easy_appointments'),
        );
    }
}