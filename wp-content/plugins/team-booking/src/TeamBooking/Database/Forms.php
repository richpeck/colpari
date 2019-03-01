<?php

namespace TeamBooking\Database;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Abstracts\FormElement,
    TeamBooking\Cache,
    TeamBooking\FormElements\Factory;

/**
 * Database interface for reservation Forms
 *
 * @since    2.2.0
 * @author   VonStroheim
 */
class Forms
{
    public static function post_type()
    {
        $args = array(
            'label'               => __('Reservation Form', 'team-booking'),
            'description'         => __('TeamBooking reservation form', 'team-booking'),
            'supports'            => array('custom-fields'),
            'hierarchical'        => FALSE,
            'public'              => FALSE,
            'show_ui'             => TRUE,
            'show_in_menu'        => FALSE,
            'menu_position'       => 5,
            'show_in_admin_bar'   => FALSE,
            'show_in_nav_menus'   => FALSE,
            'can_export'          => TRUE,
            'has_archive'         => TRUE,
            'exclude_from_search' => TRUE,
            'publicly_queryable'  => FALSE,
            'rewrite'             => FALSE,
            'capabilities'        => array(
                'publish_posts'       => 'update_core',
                'edit_others_posts'   => 'update_core',
                'delete_posts'        => 'update_core',
                'delete_others_posts' => 'update_core',
                'read_private_posts'  => 'update_core',
                'edit_post'           => 'update_core',
                'delete_post'         => 'update_core',
                'read_post'           => 'update_core',
            )
        );
        register_post_type('tbk_form', $args);

    }

    /**
     * Add a new Form
     *
     * @param FormElement[] $elements
     *
     * @return mixed
     */
    public static function add(array $elements)
    {
        $elements_array = array();
        $order = array();
        foreach ($elements as $key => $element) {
            /** @var $element FormElement */
            $elements_array[ 'tbk_' . $element->getHook() ] = $element->getProperties();
            $order[] = $element->getHook();
        }
        $elements_array['_tbk_order'] = $order;
        $post_args = array(
            'post_type'   => 'tbk_form',
            'post_status' => 'publish',
            'meta_input'  => $elements_array
        );

        $id = wp_insert_post($post_args);

        global $wp_version;
        if ($id && version_compare($wp_version, '4.4.0', '<')) {
            foreach ($elements_array as $field => $value) {
                update_post_meta($id, $field, $value);
            }
        }

        return $id;
    }

    /**
     * Get the Form elements
     *
     * @param $form_id
     *
     * @return FormElement[]
     */
    public static function get($form_id, $visible_only = FALSE)
    {
        if (NULL !== Cache::get('form_' . $form_id)) {
            $elements = Cache::get('form_' . $form_id);
        } else {
            $elements = array();
            $form = get_post($form_id);
            if (NULL !== $form) {
                $properties = get_post_custom($form->ID);
                $order = array();
                foreach ($properties as $key => $value) {
                    if ($key === '_tbk_order') {
                        $order = maybe_unserialize($value[0]);
                        continue;
                    }
                    if (0 !== strpos($key, 'tbk_')) continue;
                    $prop_array = maybe_unserialize($value[0]);
                    if ($visible_only && (bool)$prop_array['visible'] === FALSE) continue;
                    switch ($prop_array['type']) {
                        case 'text_field':
                            $elements[ $prop_array['hook'] ] = Factory::getTextField($prop_array);
                            break;
                        case 'text_area':
                            $elements[ $prop_array['hook'] ] = Factory::getTextArea($prop_array);
                            break;
                        case 'select':
                            $elements[ $prop_array['hook'] ] = Factory::getSelect($prop_array);
                            break;
                        case 'radio':
                            $elements[ $prop_array['hook'] ] = Factory::getRadio($prop_array);
                            break;
                        case 'file_upload':
                            $elements[ $prop_array['hook'] ] = Factory::getFileUpload($prop_array);
                            break;
                        case 'checkbox':
                            $elements[ $prop_array['hook'] ] = Factory::getCheckbox($prop_array);
                            break;
                        case 'paragraph':
                            $elements[ $prop_array['hook'] ] = Factory::getParagraph($prop_array);
                            break;
                    }
                }
                if (!empty($order)) {
                    $elements = array_merge(array_intersect_key(array_flip($order), $elements), $elements);
                }
            }
            Cache::add($elements, 'form_' . $form_id);
        }

        return $elements;
    }

    public static function getBuiltIn($form_id)
    {
        $elements = self::get($form_id);
        foreach ($elements as $hook => $field) {
            if (!$field->isBuiltIn()) unset($elements[ $hook ]);
        }

        return $elements;
    }

    /**
     * @param $form_id
     *
     * @return FormElement[]
     */
    public static function getCustom($form_id)
    {
        $elements = self::get($form_id);
        foreach ($elements as $hook => $field) {
            if ($field->isBuiltIn()) unset($elements[ $hook ]);
        }

        return $elements;
    }

    /**
     * Remove a Form
     *
     * @param       $form_id
     *
     * @return mixed
     */
    public static function remove($form_id)
    {
        return wp_delete_post($form_id, TRUE);
    }

    /**
     * Add/update an Element to the Form
     *
     * @param             $form_id
     * @param FormElement $element
     *
     * @return mixed (int if the field exists, boolean otherwise)
     */
    public static function addElement($form_id, FormElement $element)
    {
        $order = maybe_unserialize(get_post_meta($form_id, '_tbk_order', TRUE));
        $all_hooks = static::getAllHooks($form_id);
        if (empty($order)) {
            $order = array();
        } else {
            $order = static::cleanOrder($order, $all_hooks);
        }
        if (count($all_hooks) !== count($order)) {
            $order = static::regenerateOrder($order, $all_hooks);
        }
        if (!in_array($element->getHook(), $order)) {
            $order[] = $element->getHook();
        }
        $order = array_values($order);
        update_post_meta($form_id, '_tbk_order', $order);

        return update_post_meta($form_id, 'tbk_' . $element->getHook(), $element->getProperties());
    }

    /**
     * Move a form element into a new position
     *
     * @param        $form_id
     * @param string $element_hook
     * @param        $new_position
     */
    public static function moveElement($form_id, $element_hook, $new_position)
    {
        $order = maybe_unserialize(get_post_meta($form_id, '_tbk_order', TRUE));
        $all_hooks = static::getAllHooks($form_id);
        if (empty($order)) {
            $order = array();
        } else {
            $order = static::cleanOrder($order, $all_hooks);
        }
        if (count($all_hooks) !== count($order)) {
            $order = static::regenerateOrder($order, $all_hooks);
        }
        $to_be_moved = array_search($element_hook, $order);
        if ($to_be_moved !== FALSE) {
            unset($order[ $to_be_moved ]);
            $order = array_values($order);
            array_splice($order, $new_position, 0, $element_hook);
            $order = array_values($order);
            update_post_meta($form_id, '_tbk_order', $order);
        }
    }

    /**
     * Remove an Element from the Form
     *
     * @param $form_id
     * @param $element_hook
     *
     * @return mixed
     */
    public static function removeElement($form_id, $element_hook)
    {
        $built_in = static::getBuiltInHooks();
        if (in_array($element_hook, $built_in)) return FALSE;
        $order = maybe_unserialize(get_post_meta($form_id, '_tbk_order', TRUE));
        $all_hooks = static::getAllHooks($form_id);
        if (empty($order)) {
            $order = array();
        } else {
            $order = static::cleanOrder($order, $all_hooks);
        }
        if (count($all_hooks) !== count($order)) {
            $order = static::regenerateOrder($order, $all_hooks);
        }
        $to_be_deleted = array_search($element_hook, $order);
        if ($to_be_deleted !== FALSE) {
            unset($order[ $to_be_deleted ]);
            $order = array_values($order);
            update_post_meta($form_id, '_tbk_order', $order);
        }

        return delete_post_meta($form_id, 'tbk_' . $element_hook);
    }

    /**
     * List of active hooks (file fields and paragraphs excluded)
     *
     * @param  $form_id
     *
     * @return array
     */
    public static function getActiveHooks($form_id)
    {
        $return = array();
        foreach (self::get($form_id) as $field) {
            if ($field->getType() === 'file_upload' || $field->getType() === 'paragraph') continue;
            if ($field->isVisible()) {
                $return[] = $field->getHook();
            }
        }

        return $return;
    }

    /**
     * Returns a field title by the hook
     *
     * @param $form_id
     * @param $hook
     *
     * @return bool|string
     */
    public static function getTitleFromHook($form_id, $hook)
    {
        foreach (self::get($form_id) as $field) {
            if ($field->getHook() === $hook) {
                return $field->getTitle();
            }
        }

        return FALSE;
    }

    /**
     * Returns a field option price increment by the option's value
     *
     * @param int    $form_id
     * @param string $hook
     * @param string $value
     *
     * @return bool|int
     */
    public static function getPriceIncrementFromOptionValue($form_id, $hook, $value)
    {
        foreach (self::get($form_id) as $field) {
            if ($field->getHook() !== $hook) {
                continue;
            }
            if (NULL !== $field->getData('options')) {
                foreach ($field->getData('options') as $option) {
                    if ($option['text'] === $value) return $option['price_increment'];
                }
            }
        }

        return FALSE;
    }

    /**
     * Returns the list of built-in field hooks
     *
     * @return array
     */
    public static function getBuiltInHooks()
    {
        return array('first_name', 'second_name', 'email', 'address', 'phone', 'url');
    }

    /**
     * @param $form_id
     *
     * @return array
     */
    public static function getAllHooks($form_id)
    {
        $return = array();
        foreach (self::get($form_id) as $field) {
            $return[] = $field->getHook();
        }

        return $return;
    }

    /**
     * @param array $order
     * @param array $hooks
     *
     * @return array
     */
    public static function cleanOrder(array $order, array $hooks)
    {
        foreach ($order as $key => $hook) {
            if (!in_array($hook, $hooks)) unset($order[ $key ]);
        }

        return array_values($order);
    }

    /**
     * Regenerates the field sorting order if the stored order
     * appears to be broken or partial
     *
     * @param array $order
     * @param array $hooks
     *
     * @return array
     */
    public static function regenerateOrder(array $order, array $hooks)
    {
        foreach ($hooks as $hook) {
            if (!in_array($hook, $order)) $order[] = $hook;
        }

        return array_values($order);
    }

}