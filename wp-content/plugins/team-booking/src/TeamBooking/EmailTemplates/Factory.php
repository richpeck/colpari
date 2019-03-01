<?php

namespace TeamBooking\EmailTemplates;
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Factory class for EmailTemplates
 *
 * @since    2.2.0
 * @author   VonStroheim
 */
class Factory
{
    /**
     * @param string $id
     * @param string $name
     *
     * @return EmailTemplate
     */
    public static function createTemplate($id, $name)
    {
        $object = new EmailTemplate();
        $object->setId($id);
        $object->setName($name);

        return $object;
    }

    /**
     * Get EmailTemplate
     *
     * @param array $post_array
     *
     * @return EmailTemplate
     */
    public static function getTemplate(array $post_array)
    {
        $object = new EmailTemplate();
        $object->setId($post_array['tbk_id'][0]);
        $object->setName(\TeamBooking\Toolkit\unfilterInput($post_array['tbk_name'][0]));
        $object->setDescription(\TeamBooking\Toolkit\unfilterInput($post_array['tbk_description'][0]));
        $object->setContent($post_array['tbk_content'][0]);

        return $object;
    }

}