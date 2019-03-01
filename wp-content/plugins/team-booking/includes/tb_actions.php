<?php

namespace TeamBooking\Actions;

use TeamBooking\Abstracts\FormElement;
use TeamBooking\Admin\Service;
use TeamBooking\Slot;

defined('ABSPATH') or die('No script kiddies please!');

////////////////
//  ACTIONS   //
////////////////

/**
 * @param $params \TeamBooking\RenderParameters
 */
function calendar_click_on_day($params)
{
    do_action('tbk_calendar_click_on_day', $params);
}

/**
 * @param $params \TeamBooking\RenderParameters
 */
function calendar_change_month($params)
{
    do_action('tbk_calendar_change_month', $params);
}

/**
 * @param $slot \TeamBooking\Slot
 */
function schedule_slot_parse($slot)
{
    do_action('tbk_schedule_slot_parse', $slot);
}

/**
 * @param $slot \TeamBooking\Slot
 */
function schedule_slot_render($slot)
{
    do_action('tbk_schedule_slot_render', $slot);
}

/**
 * @param $data \TeamBooking_ReservationData
 */
function reservation_before_processing($data)
{
    do_action('tbk_reservation_before_processing', $data);
}

/**
 * @param string                       $who
 * @param \TeamBooking\EmailHandler    $email
 * @param \TeamBooking_ReservationData $data
 */
function reservation_email_to($who = 'admin', $email, $data)
{
    if ($who === 'admin') {
        do_action('tbk_reservation_email_to_admin', $email, $data);
    } elseif ($who === 'coworker') {
        do_action('tbk_reservation_email_to_coworker', $email, $data);
    } elseif ($who === 'customer') {
        do_action('tbk_reservation_email_to_customer', $email, $data);
    }
}

/**
 * @param string                       $who
 * @param \TeamBooking\EmailHandler    $email
 * @param \TeamBooking_ReservationData $data
 */
function cancellation_email_to($who = 'admin', $email, $data)
{
    if ($who === 'admin') {
        do_action('tbk_cancellation_email_to_admin', $email, $data);
    } elseif ($who === 'customer') {
        do_action('tbk_cancellation_email_to_customer', $email, $data);
    }
}

/**
 * @param \TeamBooking\Abstracts\Service $service
 * @param \TeamBooking_ReservationData   $data
 * @param string                         $what
 */
function email_send_begin($service, $data, $what = 'confirmation')
{
    do_action('tbk_email_begin_' . $what, $service, $data);
}

/**
 * @param \TeamBooking\Abstracts\Service $service
 * @param \TeamBooking_ReservationData   $data
 * @param string                         $what
 */
function email_send_end($service, $data, $what = 'confirmation')
{
    do_action('tbk_email_end_' . $what, $service, $data);
}

/**
 * @param                              $text
 * @param \TeamBooking_ReservationData $data
 * @param                              $what
 *
 * @return string
 */
function email_before_hook_replacement($text, \TeamBooking_ReservationData $data, $what)
{
    return apply_filters('tbk_email_before_hook_replacement', $text, $data, $what);
}

/**
 * @param \TeamBooking\Frontend\Form $form
 */
function reservation_form_header($form)
{
    do_action('tbk_reservation_form_header', $form);
}

/**
 * @param \TeamBooking\Frontend\Form $form
 */
function reservation_form_description($form)
{
    do_action('tbk_reservation_form_description', $form);
}

/**
 * @param \TeamBooking\Frontend\Form $form
 */
function reservation_form_map($form)
{
    do_action('tbk_reservation_form_map', $form);
}

/**
 * @param string $text
 */
function reservation_review_header($text)
{
    do_action('tbk_reservation_review_header', $text);
}

/**
 * @param \TeamBooking_ReservationData $data
 */
function reservation_review_details($data)
{
    do_action('tbk_reservation_review_details', $data);
}

/**
 * @param \TeamBooking\Abstracts\Service $service
 */
function reservation_review_footer($service)
{
    do_action('tbk_reservation_review_footer', $service);
}

/**
 * @param \TeamBooking\Abstracts\Service $service
 * @param Slot                           $slot
 */
function frontend_form_add_ticket_row($service, $slot)
{
    do_action('tbk_frontend_form_add_ticket_row', $service, $slot);
}

/**
 * @param \TeamBooking_ReservationData $reservation
 */
function reservation_completed(\TeamBooking_ReservationData $reservation)
{
    do_action('tbk_reservation_completed', $reservation);
}

/**
 * @param string $order_id
 * @param array  $outcomes
 */
function order_completed($order_id, $outcomes)
{
    do_action('tbk_order_completed', $order_id, $outcomes);
}

/**
 * @param Slot $slot
 */
function cart_slot_added(Slot $slot)
{
    do_action('tbk_slot_added_in_cart', $slot);
}

/**
 * @param $slot_id
 */
function cart_slot_removed($slot_id)
{
    do_action('tbk_slot_removed_from_cart', $slot_id);
}

/**
 * @param FormElement $field
 * @param string      $fieldname_root
 */
function backend_form_field_add_content($field, $fieldname_root)
{
    do_action('tbk_backend_form_field_add_content', $field, $fieldname_root);
}

/**
 * @param array                          $parsed_data
 * @param \TeamBooking\Abstracts\Service $service
 * @param string                         $old_hook
 */
function backend_form_field_save($parsed_data, $service, $old_hook)
{
    do_action('tbk_backend_form_field_save', $parsed_data, $service, $old_hook);
}


/**
 * @return mixed
 */
function backend_core_advanced_after_content()
{
    return do_action('tbk_backend_core_advanced_after_content');
}

////////////////
//  FILTERS   //
////////////////

/**
 * @param $content
 *
 * @return mixed
 */
function api_page_main_content($content)
{
    return apply_filters('tbk_api_page_main_content', $content);
}

/**
 * @param $id
 *
 * @return mixed
 */
function generate_order_id($id)
{
    return apply_filters('tbk_order_id', $id);
}

/**
 * @param $prefix
 *
 * @return mixed
 */
function order_id_prefix($prefix)
{
    return apply_filters('tbk_order_id_prefix', $prefix);
}

/**
 * @param $hook
 * @param $all_values
 *
 * @return mixed
 */
function email_hook_replace($hook, $all_values)
{
    return apply_filters('tbk_email_hook_replace', isset($all_values[ $hook ]) ? $all_values[ $hook ] : $hook, $hook, $all_values);
}

/**
 * @param FormElement[]                  $fields
 * @param \TeamBooking\Abstracts\Service $service
 * @param Slot                           $slot
 *
 * @return FormElement[]
 */
function manipulate_frontend_form_fields($fields, $service, $slot)
{
    return apply_filters('tbk_frontend_form_manipulate_fields', $fields, $service, $slot);
}

/**
 * @param array                          $hooks
 * @param \TeamBooking\Abstracts\Service $service
 * @param array                          $form_data
 *
 * @return array
 */
function manipulate_expected_form_field_hooks($hooks, $service, $form_data)
{
    return apply_filters('tbk_frontend_form_manipulate_expected_hooks', $hooks, $service, $form_data);
}

/**
 * @param string                       $content
 * @param \TeamBooking_ReservationData $data
 *
 * @return string
 */
function modify_thankyou_content($content, $data)
{
    return apply_filters('tbk_frontend_thankyou_content', $content, $data);
}

/**
 * @param $redirect_url
 * @param $service_id
 *
 * @return string
 */
function service_redirect_url($redirect_url, $service_id)
{
    return apply_filters('tbk_redirect_url', $redirect_url, $service_id);
}

/**
 * @param $order_id
 * @param $outcomes
 *
 * @return string
 */
function order_redirect_url($order_id, $outcomes)
{
    add_filter('tbk_order_redirect_url', '\\TeamBooking\\Functions\\prepare_order_redirect_url', 10, 3);

    return apply_filters('tbk_order_redirect_url', '', $order_id, $outcomes);
}

/**
 * @param $date
 * @param $time
 *
 * @return string
 */
function review_start_datetime($date, $time)
{
    return apply_filters('tbk_frontend_review_start_date_time', $date . ' ' . $time, $date, $time);
}

/**
 * @param $date
 * @param $time
 *
 * @return string
 */
function review_end_datetime($date, $time)
{
    return apply_filters('tbk_frontend_review_end_date_time', $date . ' ' . $time, $date, $time);
}

/**
 * @param  string                      $name
 * @param \TeamBooking_ReservationData $data
 *
 * @return string
 */
function service_name_from_reservation($name, \TeamBooking_ReservationData $data)
{
    return apply_filters('tbk_service_name_from_reservation', $name, $data);
}

/**
 * @param string $name
 * @param Slot   $slot
 *
 * @return string
 */
function service_name_from_slot($name, Slot $slot)
{
    return apply_filters('tbk_service_name_from_slot', $name, $slot);
}

/**
 * @param  string                        $name
 * @param \TeamBooking\Abstracts\Service $service
 *
 * @return string
 */
function service_name($name, \TeamBooking\Abstracts\Service $service)
{
    return apply_filters('tbk_service_name', $name, $service);
}

/**
 * @param  string                        $description
 * @param \TeamBooking\Abstracts\Service $service
 *
 * @return string
 */
function service_description($description, \TeamBooking\Abstracts\Service $service)
{
    return apply_filters('tbk_service_description', $description, $service);
}

/**
 * @param string $name
 * @param Slot   $slot
 *
 * @return string
 */
function service_description_from_slot($name, Slot $slot)
{
    return apply_filters('tbk_service_description_from_slot', $name, $slot);
}

/**
 * @param string                            $label
 * @param \TeamBooking_ReservationFormField $field
 *
 * @return string
 */
function reservation_form_field_label($label, \TeamBooking_ReservationFormField $field)
{
    return apply_filters('tbk_reservation_form_field_label', $label, $field);
}

/**
 * @param string                            $value
 * @param \TeamBooking_ReservationFormField $field
 *
 * @return string
 */
function reservation_form_field_value($value, \TeamBooking_ReservationFormField $field)
{
    return apply_filters('tbk_reservation_form_field_value', $value, $field);
}

/**
 * @param string $content
 * @param string $setting_name
 * @param string $service_id
 *
 * @return mixed
 */
function backend_panel_setting_after_content($content, $setting_name, $service_id = '')
{
    return apply_filters('tbk_backend_panel_setting_after_content', $content, $setting_name, $service_id);
}

/**
 * @param string $content
 * @param string $fieldname
 * @param string $service_id
 *
 * @return mixed
 */
function backend_email_editor_after_content($content, $fieldname, $service_id = '')
{
    return apply_filters('tbk_backend_email_editor_after_content', $content, $fieldname, $service_id);
}

/**
 * @param string                       $lang_code
 * @param \TeamBooking_ReservationData $data
 *
 * @return string
 */
function reservation_frontend_language($lang_code, \TeamBooking_ReservationData $data)
{
    return apply_filters('tbk_reservation_frontend_language', $lang_code, $data);
}

/**
 * @param \TeamBooking_ReservationData $data
 *
 * @return string
 */
function reservation_id(\TeamBooking_ReservationData $data)
{
    return apply_filters('tbk_reservation_id', $data->getDatabaseId());
}

/**
 * @param \TeamBooking_ReservationData $data
 */
function reservation_data_instantiate(\TeamBooking_ReservationData $data)
{
    do_action('tbk_reservation_data_instantiate', $data);
}

/**
 * @param \TeamBooking_ReservationData $data
 */
function reservation_data_mapped(\TeamBooking_ReservationData $data)
{
    do_action('tbk_reservation_data_mapped', $data);
}

/**
 * @param string $modal_id
 */
function backend_modal_end_content($modal_id)
{
    do_action('tbk_backend_modal_end_content', $modal_id);
}

/**
 * @param \TeamBooking_ReservationData $data
 */
function backend_reservation_details_modal_lang_content(\TeamBooking_ReservationData $data)
{
    do_action('tbk_backend_reservation_details_modal_lang_content', $data);
}

/**
 * @param bool $bool
 *
 * @return bool
 */
function parser_min_time_cut($bool)
{
    return (bool)apply_filters('tbk_parser_min_time_cut', $bool);
}

/**
 * @param $hook
 *
 * @return string
 */
function customer_phone_field_hook($hook)
{
    return apply_filters('tbk_customer_phone_field_hook', $hook);
}

/**
 * @param $hook
 *
 * @return string
 */
function customer_address_field_hook($hook)
{
    return apply_filters('tbk_customer_address_field_hook', $hook);
}

/**
 * @param $response
 */
function api_response($response)
{
    do_action('tbk_api_response', $response);
}