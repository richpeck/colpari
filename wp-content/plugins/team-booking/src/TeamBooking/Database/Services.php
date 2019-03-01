<?php

namespace TeamBooking\Database;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Cache,
    TeamBooking\Abstracts,
    TeamBooking\Services\Appointment,
    TeamBooking\Services\Event,
    TeamBooking\Services\Factory,
    TeamBooking\Services\Unscheduled;

/**
 * Database interface for Services
 *
 * @since    2.2.0
 * @author   VonStroheim
 */
class Services
{
    public static function post_type()
    {
        $args = array(
            'label'               => __('Service', 'team-booking'),
            'description'         => __('TeamBooking Service', 'team-booking'),
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
        register_post_type('tbk_service', $args);

    }

    /**
     * Update / Add a new Service
     *
     * @param Abstracts\Service $service
     *
     * @return mixed
     */
    public static function add(Abstracts\Service $service)
    {
        if (!isset($service->post_id) && NULL === $service->getForm()) {
            $form_id = Forms::add(array());
            $element = \TeamBooking\FormElements\Factory::createTextField('first_name');
            $element->setTitle(__('First name', 'team-booking'));
            $element->setRequired(TRUE);
            Forms::addElement($form_id, $element);
            $element = \TeamBooking\FormElements\Factory::createTextField('second_name');
            $element->setTitle(__('Last name', 'team-booking'));
            $element->setRequired(TRUE);
            Forms::addElement($form_id, $element);
            $element = \TeamBooking\FormElements\Factory::createTextField('email');
            $element->setTitle(__('Email', 'team-booking'));
            $element->setRequired(TRUE);
            Forms::addElement($form_id, $element);
            $element = \TeamBooking\FormElements\Factory::createTextField('address');
            $element->setTitle(__('Address', 'team-booking'));
            $element->setVisible(FALSE);
            Forms::addElement($form_id, $element);
            $element = \TeamBooking\FormElements\Factory::createTextField('phone');
            $element->setTitle(__('Phone number', 'team-booking'));
            $element->setVisible(FALSE);
            Forms::addElement($form_id, $element);
            $element = \TeamBooking\FormElements\Factory::createTextField('url');
            $element->setTitle(__('Website', 'team-booking'));
            $element->setVisible(FALSE);
            Forms::addElement($form_id, $element);

            $service->setForm($form_id);
        }
        $service_array = array();
        foreach ($service->getProperties() as $key => $value) {
            if ($key === 'settings') {
                foreach ($value as $setting_key => $setting) {
                    $service_array[ '_tbk_' . $setting_key ] = $setting;
                }
            } else {
                $service_array[ 'tbk_' . $key ] = $value;
            }
        }
        $post_args = array(
            'post_title'   => $service->getName(),
            'post_content' => $service->getDescription(),
            'post_name'    => $service->getId(),
            'post_type'    => 'tbk_service',
            'post_status'  => 'publish',
            'meta_input'   => $service_array
        );

        if (isset($service->post_id)) {
            // Update
            $post_args['ID'] = $service->post_id;
            $id = wp_update_post($post_args);
        } else {
            // Add new
            $id = wp_insert_post($post_args);
        }

        global $wp_version;
        if ($id && version_compare($wp_version, '4.4.0', '<')) {
            foreach ($service_array as $field => $value) {
                update_post_meta($id, $field, $value);
            }
        }

        return $id;
    }

    /**
     * Get Services
     *
     * @param null|string $id
     * @param string      $order_by (date, name)
     * @param string      $sorting
     *
     * @return Appointment[]|Event[]|Unscheduled[]|Appointment|Event|Unscheduled
     * @throws \Exception
     */
    public static function get($id = NULL, $order_by = 'date', $sorting = 'ASC')
    {
        if (NULL !== Cache::get('services' . $order_by . $sorting)) {
            $services = Cache::get('services' . $order_by . $sorting);
        } else {
            $post_args = array(
                'post_type' => 'tbk_service',
                'nopaging'  => TRUE,
                'order'     => $sorting === 'ASC' ? 'ASC' : 'DESC'
            );
            $posts = get_posts($post_args);
            $services = array();
            foreach ($posts as $post) {
                $properties = get_post_custom($post->ID);
                if ($properties['tbk_class'][0] === 'appointment') {
                    $service = Factory::getAppointment($properties);
                    $service->post_id = $post->ID;
                    $services[ $service->getId() ] = $service;
                } elseif ($properties['tbk_class'][0] === 'event') {
                    $service = Factory::getEvent($properties);
                    $service->post_id = $post->ID;
                    $services[ $service->getId() ] = $service;
                } elseif ($properties['tbk_class'][0] === 'unscheduled') {
                    $service = Factory::getUnscheduled($properties);
                    $service->post_id = $post->ID;
                    $services[ $service->getId() ] = $service;
                }
            }
            Cache::add($services, 'services' . $order_by . $sorting);
        }

        if (NULL !== $id) {
            if (!isset($services[ $id ])) {
                throw new \Exception('Service id "' . $id . '" not found');
            }

            return $services[ $id ];
        }

        if ($order_by === 'name') {
            uasort($services, function ($a, $b) use ($sorting) {
                /** @var $a Abstracts\Service */
                /** @var $b Abstracts\Service */
                if ($sorting === 'ASC') {
                    return strcmp(strtolower($a->getName()), strtolower($b->getName()));
                }

                return strcmp(strtolower($b->getName()), strtolower($a->getName()));
            });
        }

        return $services;
    }

    /**
     * Delete a Service
     *
     * @param string $id
     */
    public static function delete($id)
    {
        try {
            $service = self::get($id);
            wp_delete_post($service->getForm(), TRUE);
            wp_delete_post($service->post_id, TRUE);
            \TeamBooking\WPML\remove_string_form_translations($id);
            \TeamBooking\WPML\remove_string_service_translations($id);
        } catch (\Exception $e) {
            // nothing
        }
    }

}