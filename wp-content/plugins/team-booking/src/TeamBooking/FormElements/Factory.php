<?php

namespace TeamBooking\FormElements;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Abstracts;

/**
 * Factory class for FormElements
 *
 * @since    2.2.0
 * @author   VonStroheim
 */
class Factory
{
    /**
     * Create TextField
     *
     * @param string $hook
     *
     * @return TextField
     */
    public static function createTextField($hook)
    {
        $object = new TextField();
        $object->setHook($hook);

        return $object;
    }

    /**
     * Get Text Field
     *
     * @param array $post_array
     *
     * @return TextField
     */
    public static function getTextField($post_array)
    {
        $object = new TextField();
        $object->setHook($post_array['hook']);
        $object->setDescription(\TeamBooking\Toolkit\unfilterInput($post_array['description']));
        $object->setRequired($post_array['required']);
        $object->setVisible($post_array['visible']);
        $object->setTitle(\TeamBooking\Toolkit\unfilterInput($post_array['title']));
        $object->setData('validation', array_replace_recursive($object->getData('validation'), $post_array['data']['validation']));
        $object->setData('value', isset($post_array['data']['value']) ? $post_array['data']['value'] : '');
        $object->setData('prefill', isset($post_array['data']['prefill']) ? $post_array['data']['prefill'] : '');
        $object->setData('value_confirmation', isset($post_array['data']['value_confirmation']) ? $post_array['data']['value_confirmation'] : '');

        return $object;
    }

    /**
     * @param string $hook
     *
     * @return Paragraph
     */
    public static function createParagraph($hook)
    {
        $object = new Paragraph();
        $object->setHook($hook);

        return $object;
    }

    /**
     * @param array $post_array
     *
     * @return Paragraph
     */
    public static function getParagraph($post_array)
    {
        $object = new Paragraph();
        $object->setHook($post_array['hook']);
        $object->setDescription(\TeamBooking\Toolkit\unfilterInput($post_array['description']));
        $object->setVisible($post_array['visible']);
        $object->setTitle(\TeamBooking\Toolkit\unfilterInput($post_array['title']));

        return $object;
    }

    /**
     * Create TextArea
     *
     * @param string $hook
     *
     * @return TextArea
     */
    public static function createTextArea($hook)
    {
        $object = new TextArea();
        $object->setHook($hook);

        return $object;
    }

    /**
     * Get Text Area
     *
     * @param array $post_array
     *
     * @return TextArea
     */
    public static function getTextArea($post_array)
    {
        $object = new TextArea();
        $object->setHook($post_array['hook']);
        $object->setDescription(\TeamBooking\Toolkit\unfilterInput($post_array['description']));
        $object->setRequired($post_array['required']);
        $object->setVisible($post_array['visible']);
        $object->setTitle(\TeamBooking\Toolkit\unfilterInput($post_array['title']));
        $object->setData('value', isset($post_array['data']['value']) ? $post_array['data']['value'] : '');
        $object->setData('prefill', isset($post_array['data']['prefill']) ? $post_array['data']['prefill'] : '');

        return $object;
    }

    /**
     * Create Checkbox
     *
     * @param string $hook
     *
     * @return Checkbox
     */
    public static function createCheckbox($hook)
    {
        $object = new Checkbox();
        $object->setHook($hook);

        return $object;
    }

    /**
     * Get Checkbox
     *
     * @param array $post_array
     *
     * @return Checkbox
     */
    public static function getCheckbox($post_array)
    {
        $object = new Checkbox();
        $object->setHook($post_array['hook']);
        $object->setDescription(\TeamBooking\Toolkit\unfilterInput($post_array['description']));
        $object->setRequired($post_array['required']);
        $object->setVisible($post_array['visible']);
        $object->setTitle(\TeamBooking\Toolkit\unfilterInput($post_array['title']));
        $object->setData('value', isset($post_array['data']['value']) ? $post_array['data']['value'] : '');
        $object->setData('price_increment', (float)$post_array['data']['price_increment']);
        $object->setData('checked', (bool)$post_array['data']['checked']);

        return $object;
    }

    /**
     * Create File Upload
     *
     * @param string $hook
     *
     * @return FileUpload
     */
    public static function createFileUpload($hook)
    {
        $object = new FileUpload();
        $object->setHook($hook);

        return $object;
    }

    /**
     * Get FileUpload
     *
     * @param array $post_array
     *
     * @return FileUpload
     */
    public static function getFileUpload($post_array)
    {
        $object = new FileUpload();
        $object->setHook($post_array['hook']);
        $object->setDescription(\TeamBooking\Toolkit\unfilterInput($post_array['description']));
        $object->setRequired($post_array['required']);
        $object->setVisible($post_array['visible']);
        $object->setTitle(\TeamBooking\Toolkit\unfilterInput($post_array['title']));
        $object->setData('max_size', $post_array['data']['max_size']);
        $object->setData('file_extensions', $post_array['data']['file_extensions']);

        return $object;
    }

    /**
     * Create Radio Group
     *
     * @param string $hook
     *
     * @return Radio
     */
    public static function createRadio($hook)
    {
        $object = new Radio();
        $object->setHook($hook);
        $object->setData('options', array(
            0 => array(
                'text'            => __('Option one', 'team-booking'),
                'price_increment' => 0
            ),
            1 => array(
                'text'            => __('Option two', 'team-booking'),
                'price_increment' => 0
            ),
            2 => array(
                'text'            => __('Option three', 'team-booking'),
                'price_increment' => 0
            )
        ));

        return $object;
    }

    /**
     * Get Radio Group
     *
     * @param array $post_array
     *
     * @return Radio
     */
    public static function getRadio($post_array)
    {
        $object = new Radio();
        $object->setHook($post_array['hook']);
        $object->setDescription(\TeamBooking\Toolkit\unfilterInput($post_array['description']));
        $object->setVisible($post_array['visible']);
        $object->setTitle(\TeamBooking\Toolkit\unfilterInput($post_array['title']));
        $object->setData('options', $post_array['data']['options']);

        return $object;
    }

    /**
     * Create Select
     *
     * @param string $hook
     *
     * @return Select
     */
    public static function createSelect($hook)
    {
        $object = new Select();
        $object->setHook($hook);
        $object->setData('options', array(
            0 => array(
                'text'            => __('Option one', 'team-booking'),
                'price_increment' => 0
            ),
            1 => array(
                'text'            => __('Option two', 'team-booking'),
                'price_increment' => 0
            ),
            2 => array(
                'text'            => __('Option three', 'team-booking'),
                'price_increment' => 0
            )
        ));

        return $object;
    }

    /**
     * Get Select
     *
     * @param array $post_array
     *
     * @return Select
     */
    public static function getSelect($post_array)
    {
        $object = new Select();
        $object->setHook($post_array['hook']);
        $object->setDescription(\TeamBooking\Toolkit\unfilterInput($post_array['description']));
        $object->setRequired($post_array['required']);
        $object->setVisible($post_array['visible']);
        $object->setTitle(\TeamBooking\Toolkit\unfilterInput($post_array['title']));
        $object->setData('options', $post_array['data']['options']);

        return $object;
    }
}