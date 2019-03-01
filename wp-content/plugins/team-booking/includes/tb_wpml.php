<?php

namespace TeamBooking\WPML;

use TeamBooking\Abstracts\FormElement;
use TeamBooking\Abstracts\Service;
use TeamBooking\Admin;
use TeamBooking\Database\Forms;
use TeamBooking\Database\Services;
use TeamBooking\Slot;

defined('ABSPATH') or die('No script kiddies please!');

function update($force = FALSE)
{
    $services = Services::get();
    foreach ($services as $service) {
        register_string_service_translation($service, $force);
        register_string_service_email_translation($service, $force);
        $custom_fields = Forms::getCustom($service->getForm());
        foreach ($custom_fields as $custom_field) {
            register_string_form_translation($custom_field, $service->getId(), $force);
        }
    }
}

/**
 * @param string $value
 * @param string $service_id
 * @param string $what
 *
 * @return string
 */
function get_string_service_translation($value, $service_id, $what)
{
    return apply_filters('wpml_translate_string', $value, $service_id . '(' . $what . ')', array(
        'kind' => 'TeamBooking Service',
        'name' => $service_id
    ));
}

/**
 * @param string                       $value
 * @param \TeamBooking_ReservationData $data
 * @param string                       $what
 *
 * @return string
 */
function get_string_service_email_translation($value, $data, $what)
{
    $accepted_strings = array(
        'reminder_subject',
        'reminder_body',
        'cancellation_subject',
        'cancellation_body',
        'confirmation_subject',
        'confirmation_body',
    );
    if (!in_array($what, $accepted_strings, TRUE)) {
        return $value;
    }
    if ($data->getFrontendLang()) {
        $context = 'teambooking-e-mail-' . $data->getServiceId();
        $name = $data->getServiceId() . '(' . $what . ')';

        return force_string_translation($value, $context, $name, $data->getFrontendLang());
    }

    return apply_filters('wpml_translate_string', $value, $data->getServiceId() . '(' . $what . ')', array(
        'kind' => 'TeamBooking E-mail',
        'name' => $data->getServiceId()
    ));
}

/**
 * @param string $value
 * @param string $hook
 * @param string $service_id
 * @param string $what
 *
 * @return string
 */
function get_string_form_translation($value, $hook, $service_id, $what)
{
    return apply_filters('wpml_translate_string', $value, $hook . '(' . $what . ')', array(
        'kind' => 'TeamBooking Form',
        'name' => $service_id
    ));
}

/**
 * @param FormElement $field
 * @param string      $service_id
 * @param bool        $force
 */
function register_string_form_translation($field, $service_id, $force = FALSE)
{
    if (!$force && !is_str_tr_available()) {
        return;
    }
    $package = array(
        'kind'  => 'TeamBooking Form',
        'name'  => $service_id,
        'title' => $service_id,
    );
    do_action('wpml_register_string', $field->getTitle(), $field->getHook() . '(label)', $package, $field->getHook() . '(label)', 'LINE');
    if ($field->getType() === 'paragraph') {
        do_action('wpml_register_string', $field->getDescription(), $field->getHook() . '(content)', $package, $field->getHook() . '(content)', 'VISUAL');
    } else {
        if ($field->getDescription() !== '') {
            do_action('wpml_register_string', $field->getDescription(), $field->getHook() . '(description)', $package, $field->getHook() . '(description)', 'VISUAL');
        } else {
            if (function_exists('icl_unregister_string')) {
                icl_unregister_string('teambooking-form-' . $service_id, $field->getHook() . '(description)');
            }
        }
    }
    if (($field->getType() === 'text_field') || ($field->getType() === 'text_area')) {
        $default_text = $field->getData('value');
        if ($default_text !== '') {
            do_action('wpml_register_string', $default_text, $field->getHook() . '(default_text)', $package, $field->getHook() . '(default_text)', 'LINE');
        } else {
            if (function_exists('icl_unregister_string')) {
                icl_unregister_string('teambooking-form-' . $service_id, $field->getHook() . '(default_text)');
            }
        }
    }
    if (($field->getType() === 'select') || ($field->getType() === 'radio')) {
        // deleting all the options
        icl_unregister_string_wildcard('teambooking-form-' . $service_id, $field->getHook() . '(option');
        $i = 1;
        foreach ($field->getData('options') as $option) {
            do_action('wpml_register_string', $option['text'], $field->getHook() . '(option_' . $i . ')', $package, $field->getHook() . '(option_' . $i . ')', 'LINE');
            $i++;
        }
    }

}

/**
 * @param Service $service
 * @param bool    $force
 */
function register_string_service_translation(Service $service, $force = FALSE)
{
    if (!$force && !is_str_tr_available()) {
        return;
    }
    $package = array(
        'kind'  => 'TeamBooking Service',
        'name'  => $service->getId(),
        'title' => $service->getId(),
    );
    do_action('wpml_register_string', $service->getName(), $service->getId() . '(name)', $package, $service->getId() . '(name)', 'LINE');
    if ($service->getDescription() !== '') {
        do_action('wpml_register_string', $service->getDescription(), $service->getId() . '(description)', $package, $service->getId() . '(description)', 'VISUAL');
    } else {
        if (function_exists('icl_unregister_string')) {
            icl_unregister_string('teambooking-service-' . $service->getId(), $service->getId() . '(description)');
        }
    }
}

/**
 * @param Service $service
 * @param bool    $force
 */
function register_string_service_email_translation(Service $service, $force = FALSE)
{
    if (!$force && !is_str_tr_available()) {
        return;
    }
    /** @var \TeamBooking\Services\Appointment | \TeamBooking\Services\Event | \TeamBooking\Services\Unscheduled $service */
    $package = array(
        'kind'  => 'TeamBooking E-mail',
        'name'  => $service->getId(),
        'title' => $service->getId(),
    );
    // Confirmation e-mail
    if ($service->getEmailToCustomer('subject') !== '') {
        do_action('wpml_register_string', $service->getEmailToCustomer('subject'), $service->getId() . '(confirmation_subject)', $package, $service->getId() . '(confirmation_subject)', 'LINE');
    } else {
        if (function_exists('icl_unregister_string')) {
            icl_unregister_string('teambooking-email-' . $service->getId(), $service->getId() . '(confirmation_subject)');
        }
    }
    if ($service->getEmailToCustomer('body') !== '') {
        do_action('wpml_register_string', $service->getEmailToCustomer('body'), $service->getId() . '(confirmation_body)', $package, $service->getId() . '(confirmation_body)', 'VISUAL');
    } else {
        if (function_exists('icl_unregister_string')) {
            icl_unregister_string('teambooking-email-' . $service->getId(), $service->getId() . '(confirmation_body)');
        }
    }
    // Cancellation e-mail
    if ($service->getEmailCancellationToCustomer('subject') !== '') {
        do_action('wpml_register_string', $service->getEmailCancellationToCustomer('subject'), $service->getId() . '(cancellation_subject)', $package, $service->getId() . '(cancellation_subject)', 'LINE');
    } else {
        if (function_exists('icl_unregister_string')) {
            icl_unregister_string('teambooking-email-' . $service->getId(), $service->getId() . '(cancellation_subject)');
        }
    }
    if ($service->getEmailCancellationToCustomer('body') !== '') {
        do_action('wpml_register_string', $service->getEmailCancellationToCustomer('body'), $service->getId() . '(cancellation_body)', $package, $service->getId() . '(cancellation_body)', 'VISUAL');
    } else {
        if (function_exists('icl_unregister_string')) {
            icl_unregister_string('teambooking-email-' . $service->getId(), $service->getId() . '(cancellation_body)');
        }
    }
    // Reminder e-mail
    if ($service->getClass() !== 'unscheduled') {
        if ($service->getEmailReminder('subject') !== '') {
            do_action('wpml_register_string', $service->getEmailReminder('subject'), $service->getId() . '(reminder_subject)', $package, $service->getId() . '(reminder_subject)', 'LINE');
        } else {
            if (function_exists('icl_unregister_string')) {
                icl_unregister_string('teambooking-email-' . $service->getId(), $service->getId() . '(reminder_subject)');
            }
        }
        if ($service->getEmailReminder('body') !== '') {
            do_action('wpml_register_string', $service->getEmailReminder('body'), $service->getId() . '(reminder_body)', $package, $service->getId() . '(reminder_body)', 'VISUAL');
        } else {
            if (function_exists('icl_unregister_string')) {
                icl_unregister_string('teambooking-email-' . $service->getId(), $service->getId() . '(reminder_body)');
            }
        }
    }
}

/**
 * @param string $service_id
 */
function remove_string_service_translation($service_id)
{
    if (function_exists('icl_unregister_string')) {
        icl_unregister_string('teambooking-service-' . $service_id, $service_id . '(name)');
        icl_unregister_string('teambooking-service-' . $service_id, $service_id . '(description)');
    }
}

/**
 * @param string $hook
 * @param string $service_id
 */
function remove_string_form_translation($hook, $service_id)
{
    if (function_exists('icl_unregister_string')) {
        icl_unregister_string('teambooking-form-' . $service_id, $hook . '(label)');
        icl_unregister_string('teambooking-form-' . $service_id, $hook . '(content)');
        icl_unregister_string('teambooking-form-' . $service_id, $hook . '(description)');
        icl_unregister_string('teambooking-form-' . $service_id, $hook . '(default_text)');
        icl_unregister_string_wildcard('teambooking-form-' . $service_id, $hook . '(option');
    }
}

/**
 * @param string $service_id
 */
function remove_string_form_translations($service_id)
{
    do_action('wpml_delete_package_action', $service_id, 'TeamBooking Form');
}

/**
 * @param string $service_id
 */
function remove_string_service_translations($service_id)
{
    do_action('wpml_delete_package_action', $service_id, 'TeamBooking Service');
}

/**
 * @param string $context
 * @param string $partial_name
 */
function icl_unregister_string_wildcard($context, $partial_name)
{
    global $wpdb;
    $string_ids = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings
                                                WHERE context=%s AND name LIKE '%%%s%%'",
        $context, $partial_name));
    foreach ($string_ids as $string_id) {
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}icl_strings WHERE id=%d", $string_id->id));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}icl_string_translations WHERE string_id=%d", $string_id->id));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}icl_string_positions WHERE string_id=%d", $string_id->id));
        do_action('icl_st_unregister_string', $string_id->id);
    }
}

/**
 * @return bool
 */
function is_str_tr_available()
{
    return class_exists('SitePress')
        && class_exists('WPML_String_Translation')
        && class_exists('WPML_TM_Loader');
}

/**
 * @param string $domain
 * @param string $type
 *
 * @return string
 */
function get_translations_link($domain, $type = 'service')
{
    $domain = 'teambooking-' . $type . '-' . $domain;

    return admin_url('admin.php?page=wpml-string-translation/menu/string-translation.php&show_results=all&context=' . $domain);
}

/**
 * @param string $content
 * @param string $field_name
 * @param string $service_id
 *
 * @return string
 */
function build_translations_backend_info($content, $field_name, $service_id)
{
    if (!is_str_tr_available()) {
        return $content;
    }
    if ($field_name === 'event[booking_type_name]' || $field_name === 'event[info]') {
        $file_url = plugins_url() . '/sitepress-multilingual-cms/res/img/icon-16-black.png';
        $file_path = str_replace('team-booking', 'sitepress-multilingual-cms', TEAMBOOKING_PATH)
            . DIRECTORY_SEPARATOR . 'res'
            . DIRECTORY_SEPARATOR . 'img'
            . DIRECTORY_SEPARATOR . '/icon-16-black.png';

        return $content
            . '<span style="margin:2px;display: block;font-style: italic;">'
            . (file_exists($file_path) ? '<img style="vertical-align:middle;margin-right: 2px;" src="' . $file_url . '">' : '')
            . '<a style="vertical-align:middle;" href="'
            . get_translations_link($service_id, 'service') . '">'
            . esc_html__('Translations', 'team-booking')
            . '</a></span>';
    }

    return $content;
}

/**
 * @param string $content
 * @param string $field_name
 * @param string $service_id
 *
 * @return string
 */
function build_translations_backend_email_info($content, $field_name, $service_id)
{
    if (!is_str_tr_available()) {
        return $content;
    }
    if ($field_name === 'event[front_end_email]'
        || $field_name === 'event[reminder_email]'
        || $field_name === 'event[cancellation_email]') {
        $file_url = plugins_url() . '/sitepress-multilingual-cms/res/img/icon-16-black.png';
        $file_path = str_replace('team-booking', 'sitepress-multilingual-cms', TEAMBOOKING_PATH)
            . DIRECTORY_SEPARATOR . 'res'
            . DIRECTORY_SEPARATOR . 'img'
            . DIRECTORY_SEPARATOR . '/icon-16-black.png';

        return $content
            . '<span style="margin:2px;display: block;font-style: italic;">'
            . (file_exists($file_path) ? '<img style="vertical-align:middle;margin-right: 2px;" src="' . $file_url . '">' : '')
            . '<a style="vertical-align:middle;" href="'
            . get_translations_link($service_id, 'e-mail') . '">'
            . esc_html__('Translations', 'team-booking')
            . '</a></span>';
    }

    return $content;
}

/**
 * @param FormElement $field
 * @param string      $field_name
 */
function build_translations_backend_info_form($field, $field_name)
{
    if (is_str_tr_available() && !$field->isBuiltIn()) {

        $file_url = plugins_url() . '/sitepress-multilingual-cms/res/img/icon-16-black.png';
        $file_path = str_replace('team-booking', 'sitepress-multilingual-cms', TEAMBOOKING_PATH)
            . DIRECTORY_SEPARATOR . 'res'
            . DIRECTORY_SEPARATOR . 'img'
            . DIRECTORY_SEPARATOR . '/icon-16-black.png';

        echo '<tr class="tb-hide"><td><span style="margin:2px;display: block;font-style: italic;">'
            . (file_exists($file_path) ? '<img style="vertical-align:middle;margin-right: 2px;" src="' . $file_url . '">' : '')
            . '<a style="vertical-align:middle;" href="'
            . get_translations_link($field->getServiceId(), 'form') . '">'
            . esc_html__('Translations', 'team-booking')
            . '</a></span></td></tr>';
    }
}

/**
 * @param \TeamBooking_ReservationData $data
 */
function store_frontend_language(\TeamBooking_ReservationData $data)
{
    if (defined('ICL_LANGUAGE_CODE')) {
        $data->setFrontendLang(ICL_LANGUAGE_CODE);
    }
}

/**
 * @param  string                      $name
 * @param \TeamBooking_ReservationData $data
 *
 * @return string
 */
function translate_reservation_service_name($name, \TeamBooking_ReservationData $data)
{
    $service_name = \TeamBooking\WPML\get_string_service_translation($name, $data->getServiceId(), 'name');
    if (!empty($service_name)) {
        return $service_name;
    }

    return $name;
}

/**
 * @param string $name
 * @param Slot   $slot
 *
 * @return string
 */
function translate_slot_service_name($name, Slot $slot)
{
    $service_name = \TeamBooking\WPML\get_string_service_translation($name, $slot->getServiceId(), 'name');
    if (!empty($service_name)) {
        return $service_name;
    }

    return $name;
}

/**
 * @param string  $name
 * @param Service $service
 *
 * @return string
 */
function translate_service_name($name, Service $service)
{
    $service_name = get_string_service_translation($name, $service->getId(), 'name');
    if (!empty($service_name)) {
        return $service_name;
    }

    return $name;
}

/**
 * @param string  $description
 * @param Service $service
 *
 * @return string
 */
function translate_service_description($description, Service $service)
{
    $service_description = get_string_service_translation($description, $service->getId(), 'description');
    if (!empty($service_description)) {
        return $service_description;
    }

    return $description;
}

/**
 * @param string $description
 * @param Slot   $slot
 *
 * @return string
 */
function translate_slot_service_description($description, Slot $slot)
{
    $service_description = \TeamBooking\WPML\get_string_service_translation($description, $slot->getServiceId(), 'description');
    if (!empty($service_description)) {
        return $service_description;
    }

    return $description;
}

/**
 * @param string                            $label
 * @param \TeamBooking_ReservationFormField $field
 *
 * @return string
 */
function translate_reservation_form_field_label($label, \TeamBooking_ReservationFormField $field)
{
    $field_label = \TeamBooking\WPML\get_string_form_translation($label, $field->getName(), $field->getServiceId(), 'label');
    if (!empty($field_label)) {
        return $field_label;
    }

    return $label;
}

/**
 * @param string                       $code
 * @param \TeamBooking_ReservationData $data
 *
 * @return string
 */
function expand_lang_code($code, \TeamBooking_ReservationData $data)
{
    global $wpdb;
    $language = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT code, english_name, active, tag, name
            FROM {$wpdb->prefix}icl_languages lang
            INNER JOIN {$wpdb->prefix}icl_languages_translations trans
            ON lang.code = trans.language_code
            AND lang.code = %s
            AND trans.display_language_code=%s"
            , $code, $code
        )
    );
    if ($language) {
        return $language->name . ' (' . $language->english_name . ')';
    }

    return $code;
}

/**
 * @param string                            $value
 * @param \TeamBooking_ReservationFormField $field
 *
 * @return string
 */
function translate_reservation_form_field_value($value, \TeamBooking_ReservationFormField $field)
{
    if (NULL === $field->getServiceId() || in_array($field->getName(), Forms::getBuiltInHooks())) {
        return $value;
    }
    try {
        $service = Services::get($field->getServiceId());
        $form_fields = Forms::getCustom($service->getForm());
        $field_label = '';
        foreach ($form_fields as $form_field) {
            if ($form_field->getHook() === $field->getName()) {
                if (NULL !== $form_field->getData('options')) {
                    $i = 1;
                    foreach ($form_field->getData('options') as $option) {
                        if ($option['text'] === $value) {
                            $field_label = \TeamBooking\WPML\get_string_form_translation($value, $field->getName(), $field->getServiceId(), 'option_' . $i);
                            break 2;
                        }
                        $i++;
                    }
                }
                break;
            }
        }
        if (!empty($field_label)) {
            return $field_label;
        }

        return $value;
    } catch (\Exception $e) {
        return $value;
    }
}

/**
 * Returns the translation of a string in a specific language if it exists or the original if it does not.
 *
 * @param $string
 * @param $context
 * @param $name
 * @param $lang
 *
 * @return string
 */
function force_string_translation($string, $context, $name, $lang)
{
    $output = $string;
    if (!empty($lang) && is_str_tr_available()) {
        global $wpdb;
        $table1 = $wpdb->prefix . 'icl_strings';
        $table2 = $wpdb->prefix . 'icl_string_translations';
        $sql = "SELECT * 
            FROM 
                $table1, $table2
            WHERE 
                $table1.context = %s
            AND
                $table1.name = %s
            AND
                $table1.status = '10'
            AND
                $table1.id = $table2.string_id
            AND
                $table2.language = %s
            ";
        $safe_sql = $wpdb->prepare($sql, $context, $name, $lang);
        $result = $wpdb->get_row($safe_sql, ARRAY_A);
        if (NULL !== $result) {
            $output = $result['value'];
        }
    }

    return $output;
}

/**
 * @return string
 */
function get_languages_dropdown($lang = NULL)
{
    $languages = apply_filters('wpml_active_languages', NULL, 'orderby=id&order=desc');
    ob_start();
    echo '<select class="tbk-language-select tbk-toggle-on-edit" style="display:none;">';
    foreach ($languages as $language) {
        $active = (NULL !== $lang ? ($lang === $language['language_code']) : $language['active']);
        echo '<option' . ($active ? ' selected="selected"' : '') . ' value="' . $language['language_code'] . '">'
            . $language['native_name'] . ' (' . $language['translated_name']
            . ')</option>';
    }
    echo '</select>';

    return ob_get_clean();
}

function add_language_switcher_to_modal(\TeamBooking_ReservationData $data)
{
    echo get_languages_dropdown($data->getFrontendLang());
}

function add_regenerate_button()
{
    if (is_str_tr_available()) {
        ?>
        <form id="team-booking-wpml-regenerate-records" method="POST"
              action="<?= Admin::add_params_to_admin_url(admin_url('admin-post.php')) ?>">
            <?php echo get_submit_button(__('Regenerate WPML records', 'team-booking'), 'secondary', 'team-booking-regenerate-wpml', FALSE); ?>
            <input type="hidden" name="action" value="tbk_wpml_regenerate_records">
            <?php wp_nonce_field('team_booking_options_verify') ?>
        </form>
        <?php
    }
}

function regenerate_strings()
{
    if (\TeamBooking\Functions\isAdmin()) {
        check_admin_referer('team_booking_options_verify');
        update();
    }
    wp_redirect(admin_url('admin.php?page=team-booking-general&nag_success=1'));
    exit;
}

add_filter('tbk_backend_panel_setting_after_content', 'TeamBooking\WPML\build_translations_backend_info', 10, 3);
add_filter('tbk_backend_email_editor_after_content', 'TeamBooking\WPML\build_translations_backend_email_info', 10, 3);
add_filter('tbk_service_name_from_reservation', 'TeamBooking\WPML\translate_reservation_service_name', 10, 2);
add_filter('tbk_service_name_from_slot', 'TeamBooking\WPML\translate_slot_service_name', 10, 2);
add_filter('tbk_service_description_from_slot', 'TeamBooking\WPML\translate_slot_service_description', 10, 2);
add_filter('tbk_service_name', 'TeamBooking\WPML\translate_service_name', 10, 2);
add_filter('tbk_service_description', 'TeamBooking\WPML\translate_service_description', 10, 2);
add_filter('tbk_reservation_form_field_label', 'TeamBooking\WPML\translate_reservation_form_field_label', 10, 2);
add_filter('tbk_reservation_form_field_value', 'TeamBooking\WPML\translate_reservation_form_field_value', 10, 2);
add_filter('tbk_reservation_frontend_language', 'TeamBooking\WPML\expand_lang_code', 10, 2);
add_filter('tbk_email_before_hook_replacement', 'TeamBooking\WPML\get_string_service_email_translation', 10, 3);

add_action('tbk_backend_form_field_add_content', 'TeamBooking\WPML\build_translations_backend_info_form', 10, 2);
add_action('tbk_reservation_data_instantiate', 'TeamBooking\WPML\store_frontend_language');
add_action('tbk_backend_reservation_details_modal_lang_content', 'TeamBooking\WPML\add_language_switcher_to_modal');
add_action('tbk_backend_core_advanced_after_content', 'TeamBooking\WPML\add_regenerate_button');

add_action('admin_post_tbk_wpml_regenerate_records', 'TeamBooking\WPML\regenerate_strings');