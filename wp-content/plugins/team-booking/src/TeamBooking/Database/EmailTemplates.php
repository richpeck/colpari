<?php

namespace TeamBooking\Database;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Admin\Framework\Html;
use TeamBooking\Cache,
    TeamBooking\Abstracts,
    TeamBooking\EmailTemplates\Factory;
use TeamBooking\EmailTemplates\EmailTemplate;

/**
 * Database interface for EmailTemplates
 *
 * @since    2.5.0
 * @author   VonStroheim
 */
class EmailTemplates
{
    public static function post_type()
    {
        $args = array(
            'label'               => __('E-mail templates', 'team-booking'),
            'labels'              => array(
                'edit_item'    => __('Edit e-mail template', 'team-booking'),
                'add_new_item' => __('Add new e-mail template', 'team-booking'),
                'search_items' => __('Search e-mail templates', 'team-booking')
            ),
            'supports'            => array('custom-fields', 'content', 'excerpt', 'title'),
            'hierarchical'        => FALSE,
            'public'              => FALSE,
            'show_ui'             => \TeamBooking\Functions\isAdmin() ? TRUE : FALSE,
            'show_in_menu'        => \TeamBooking\Functions\isAdmin() ? 'team-booking' : FALSE,
            'menu_icon'           => 'dashicons-schedule',
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
        register_post_type('tbk_email_template', $args);
        add_action('do_meta_boxes', array('TeamBooking\\Database\\EmailTemplates', 'fix_metaboxes'));
        add_action('admin_head-post.php', array('TeamBooking\\Database\\EmailTemplates', 'hide_minor_actions'));
        add_action('admin_head-post-new.php', array('TeamBooking\\Database\\EmailTemplates', 'hide_minor_actions'));
        add_action('save_post', array('TeamBooking\\Database\\EmailTemplates', 'save_template_content'), 10, 1);
    }

    public static function fix_metaboxes()
    {
        remove_meta_box('postcustom', 'tbk_email_template', 'normal');
        remove_meta_box('postexcerpt', 'tbk_email_template', 'normal');
        add_meta_box('postexcerpt', __('Template description', 'team-booking'), 'post_excerpt_meta_box', 'tbk_email_template', 'normal', 'high');
        add_meta_box('wp-content-editor-container', __('Template content', 'team-booking'), array('TeamBooking\\Database\\EmailTemplates', 'editor'), 'tbk_email_template', 'normal', 'high');
    }

    public function editor($post)
    {
        echo Html::h4(
            __('Editing a template does NOT automatically update the e-mail contents where you imported the template before! You must re-import the template, eventually!', 'team-booking')
        );
        echo Html::paragraph(
            __("You are writing e-mail content, so feel free to use HTML and inline CSS, but please don't use WordPress shortcodes here as they won't work. Don't rely too much on the visual editor, as it might be affected by local CSS. Do not use the typical blog-like WordPress tags. Always test your template by sending an actual e-mail.", 'team-booking')
        );
        wp_nonce_field(plugin_basename(TEAMBOOKING_FILE_PATH), 'tbk_email_template_nonce');
        wp_editor(
            $post->post_content,
            'tbk-email-template-content',
            array(
                'media_buttons' => FALSE,
                'textarea_name' => 'contents',
                'tinymce'       => TRUE,
                'textarea_rows' => 16,
                'teeny'         => TRUE
            )
        );
    }

    public static function save_template_content($post_id)
    {
        // check if we're on the post page and if the nonce has been set
        if (!isset($_POST['post_type']) || !isset($_POST['tbk_email_template_nonce'])) {
            return;
        }
        // check if the we're on the custom post page
        if ('tbk_email_template' !== $_POST['post_type']) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!wp_verify_nonce($_POST['tbk_email_template_nonce'], plugin_basename(TEAMBOOKING_FILE_PATH))) {
            return;
        }
        if (!current_user_can('update_core')) {
            return;
        }
        // unhook this function so it doesn't loop infinitely
        remove_action('save_post', array('TeamBooking\\Database\\EmailTemplates', 'save_template_content'));
        // update the post, which calls save_post again
        $data_content = $_POST['contents'];
        $my_post = array();
        $my_post['ID'] = $post_id;
        $my_post['post_content'] = $data_content;
        wp_update_post($my_post);
        // re-hook this function
        add_action('save_post', array('TeamBooking\\Database\\EmailTemplates', 'save_template_content'));
    }

    public static function hide_minor_actions()
    {
        global $post;
        if ($post->post_type === 'tbk_email_template') {
            echo '
                <style type="text/css">
                    #misc-publishing-actions,
                    #minor-publishing-actions{
                        display:none;
                    }
                    
                    #icl_div_config {
                        display:none;
                    }
                </style>
            ';
        }
    }

    /**
     * Update / Add a new EmailTemplate
     *
     * @param Abstracts\EmailTemplate $template
     *
     * @return int
     */
    public static function add(Abstracts\EmailTemplate $template)
    {
        $service_array = array();
        foreach ($template->getProperties() as $key => $value) {
            if ($key === 'settings') {
                foreach ($value as $setting_key => $setting) {
                    $service_array[ '_tbk_' . $setting_key ] = $setting;
                }
            } else {
                $service_array[ 'tbk_' . $key ] = $value;
            }
        }
        $post_args = array(
            'post_title'   => $template->getName(),
            'post_excerpt' => $template->getDescription(),
            'post_content' => $template->getContent(),
            'post_name'    => $template->getId(),
            'post_type'    => 'tbk_email_template',
            'post_status'  => 'publish',
            'meta_input'   => $service_array
        );

        if (isset($template->post_id)) {
            // Update
            $post_args['ID'] = $template->post_id;
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
     * Get EmailTemplates
     *
     * @param null|string $id
     * @param string      $sorting
     *
     * @return EmailTemplate[]|EmailTemplate|bool
     */
    public static function get($id = NULL, $sorting = 'ASC')
    {
        if (NULL !== Cache::get('email_templates' . $sorting)) {
            $templates = Cache::get('email_templates' . $sorting);
        } else {
            $post_args = array(
                'post_type'        => 'tbk_email_template',
                'nopaging'         => TRUE,
                'order'            => $sorting === 'ASC' ? 'ASC' : 'DESC',
                'suppress_filters' => TRUE
            );
            $posts = get_posts($post_args);
            $templates = array();
            foreach ($posts as $post) {
                $properties = get_post_custom($post->ID);
                $template = Factory::getTemplate($properties);
                $template->post_id = $post->ID;
                $template->setContent($post->post_content);
                $template->setDescription($post->post_excerpt);
                $template->setName($post->post_title);
                $templates[ $template->getId() ] = $template;
            }
            Cache::add($templates, 'email_templates' . $sorting);
        }

        if (NULL !== $id) {
            if (!isset($templates[ $id ])) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    trigger_error("E-mail template id {$id} not found", E_USER_NOTICE);
                }

                return FALSE;
            }

            return $templates[ $id ];
        }

        return $templates;
    }

    /**
     * Delete an EmailTemplate
     *
     * @param string $id
     */
    public static function delete($id)
    {
        $template = self::get($id);
        if ($template) {
            wp_delete_post($template->post_id, TRUE);
        }
    }

}