<?php

defined('ABSPATH') or die('No script kiddies please!');

add_action('vc_before_init', 'tb_vc_integrate');

function tb_vc_integrate()
{
    if (!\TeamBooking\Functions\getSettings() instanceof \TeamBookingSettings) return;
    $services = \TeamBooking\Database\Services::get();
    $services_array = array();
    foreach ($services as $service) {
        $services_array[ $service->getName(TRUE) ] = $service->getId();
    }
    $coworkers = \TeamBooking\Functions\getSettings()->getCoworkersData();
    $coworkers_array = array();
    foreach ($coworkers as $coworker) {
        $coworkers_array[ $coworker->getDisplayName() ] = $coworker->getId();
    }

    vc_map(array(
        'name'                    => esc_html_x('TeamBooking Calendar', 'Visual Composer element', 'team-booking'),
        'description'             => esc_html_x('Frontend booking calendar', 'Visual Composer element', 'team-booking'),
        'base'                    => 'tb-calendar',
        'admin_enqueue_css'       => TEAMBOOKING_URL . '/css/vc.css',
        'front_enqueue_js'        => TEAMBOOKING_URL . '/js/frontend.js',
        'front_enqueue_css'       => TEAMBOOKING_URL . (is_rtl() ? '/css/frontend_rtl.css' : '/css/frontend.css'),
        'icon'                    => TEAMBOOKING_URL . '/images/logo-black.png',
        'category'                => esc_html_x('Booking', 'Visual Composer category', 'team-booking'),
        'show_settings_on_create' => FALSE,
        'params'                  => array(
            array(
                'type'        => 'checkbox',
                'holder'      => 'span',
                'class'       => 'tbk-vc-read-only',
                'heading'     => esc_html_x('Read only', 'Visual Composer element', 'team-booking'),
                'param_name'  => 'read_only',
                'value'       => array(esc_html__('Yes', 'team-booking') => esc_html_x('Read only', 'Visual Composer element', 'team-booking')),
                'description' => esc_html_x('Reservations will be forbidden', 'Visual Composer element', 'team-booking')
            ),
            array(
                'type'        => 'checkbox',
                'holder'      => 'span',
                'class'       => 'tbk-vc-logged-only',
                'heading'     => esc_html_x('Registered users only', 'Visual Composer element', 'team-booking'),
                'param_name'  => 'logged_only',
                'value'       => array(esc_html__('Yes', 'team-booking') => esc_html_x('Registered users only', 'Visual Composer element', 'team-booking')),
                'description' => esc_html_x('Reservations will be allowed for registered users only', 'Visual Composer element', 'team-booking')
            ),
            array(
                'type'        => 'dropdown',
                'heading'     => esc_html_x('Alternative slot style', 'Visual Composer element', 'team-booking'),
                'param_name'  => 'slot_style',
                'value'       => array(
                    esc_attr__('Basic', 'team-booking')   => 0,
                    esc_attr__('Elegant', 'team-booking') => 1,
                ),
                'description' => esc_html_x('Renders the slots with an alternative style', 'Visual Composer element', 'team-booking'),
                'group'       => esc_html__('Style', 'team-booking')
            ),
            array(
                'type'        => 'checkbox',
                'heading'     => esc_html_x('Hide filters', 'Visual Composer element', 'team-booking'),
                'param_name'  => 'nofilter',
                'value'       => array(esc_html__('Yes', 'team-booking') => 'yes'),
                'description' => esc_html_x('Services and Coworkers selectors will be hidden', 'Visual Composer element', 'team-booking')
            ),
            array(
                'type'        => 'checkbox',
                'heading'     => esc_html_x('Hide timezones', 'Visual Composer element', 'team-booking'),
                'param_name'  => 'notimezone',
                'value'       => array(esc_html__('Yes', 'team-booking') => 'yes'),
                'description' => esc_html_x('Timezone selector will be hidden', 'Visual Composer element', 'team-booking')
            ),
            array(
                'type'        => 'checkbox',
                'heading'     => esc_html_x('Specific services', 'Visual Composer element', 'team-booking'),
                'param_name'  => 'booking',
                'value'       => $services_array,
                'description' => esc_html_x('Leave blank for all', 'Visual Composer element', 'team-booking'),
                'group'       => esc_html__('Services', 'team-booking')
            ),
            array(
                'type'        => 'checkbox',
                'heading'     => esc_html_x('Specific coworkers', 'Visual Composer element', 'team-booking'),
                'param_name'  => 'coworker',
                'value'       => $coworkers_array,
                'description' => esc_html_x('Leave blank for all', 'Visual Composer element', 'team-booking'),
                'group'       => esc_html__('Coworkers', 'team-booking')
            )
        )
    ));

    vc_map(array(
        'name'                    => esc_html_x('TeamBooking Upcoming', 'Visual Composer element', 'team-booking'),
        'description'             => esc_html_x('Upcoming events list', 'Visual Composer element', 'team-booking'),
        'base'                    => 'tb-upcoming',
        'admin_enqueue_css'       => TEAMBOOKING_URL . '/css/vc.css',
        'front_enqueue_js'        => TEAMBOOKING_URL . '/js/frontend.js',
        'front_enqueue_css'       => TEAMBOOKING_URL . (is_rtl() ? '/css/frontend_rtl.css' : '/css/frontend.css'),
        'icon'                    => TEAMBOOKING_URL . '/images/logo-black.png',
        'category'                => esc_html_x('Booking', 'Visual Composer category', 'team-booking'),
        'show_settings_on_create' => FALSE,
        'params'                  => array(
            array(
                'type'        => 'checkbox',
                'holder'      => 'span',
                'class'       => 'tbk-vc-read-only',
                'heading'     => esc_html_x('Read only', 'Visual Composer element', 'team-booking'),
                'param_name'  => 'read_only',
                'value'       => array(esc_html__('Yes', 'team-booking') => esc_html_x('Read only', 'Visual Composer element', 'team-booking')),
                'description' => esc_html_x('Reservations will be forbidden', 'Visual Composer element', 'team-booking')
            ),
            array(
                'type'        => 'checkbox',
                'holder'      => 'span',
                'class'       => 'tbk-vc-logged-only',
                'heading'     => esc_html_x('Registered users only', 'Visual Composer element', 'team-booking'),
                'param_name'  => 'logged_only',
                'value'       => array(esc_html__('Yes', 'team-booking') => esc_html_x('Registered users only', 'Visual Composer element', 'team-booking')),
                'description' => esc_html_x('Reservations will be allowed for registered users only', 'Visual Composer element', 'team-booking')
            ),
            array(
                'type'        => 'checkbox',
                'heading'     => esc_html_x('Show service descriptions', 'Visual Composer element', 'team-booking'),
                'param_name'  => 'descriptions',
                'value'       => array(esc_html__('Yes', 'team-booking') => 'yes'),
                'description' => esc_html_x('Shows service descriptions right under each slot', 'Visual Composer element', 'team-booking')
            ),
            array(
                'type'        => 'checkbox',
                'heading'     => esc_html_x('Show little calendar only once per day', 'Visual Composer element', 'team-booking'),
                'param_name'  => 'hide_same_days',
                'value'       => array(esc_html__('Yes', 'team-booking') => 'yes'),
                'std'         => 'yes',
                'description' => esc_html_x('The little calendar will be shown only once per day', 'Visual Composer element', 'team-booking'),
                'group'       => esc_html__('Style', 'team-booking')
            ),
            array(
                'type'        => 'checkbox',
                'heading'     => esc_html_x('Hide timezones', 'Visual Composer element', 'team-booking'),
                'param_name'  => 'notimezone',
                'value'       => array(esc_html__('Yes', 'team-booking') => 'yes'),
                'description' => esc_html_x('Timezone selector will be hidden', 'Visual Composer element', 'team-booking')
            ),
            array(
                'type'        => 'checkbox',
                'heading'     => esc_html_x('Specific services', 'Visual Composer element', 'team-booking'),
                'param_name'  => 'service',
                'value'       => $services_array,
                'description' => esc_html_x('Leave blank for all', 'Visual Composer element', 'team-booking'),
                'group'       => esc_html__('Services', 'team-booking')
            ),
            array(
                'type'        => 'checkbox',
                'heading'     => esc_html_x('Specific coworkers', 'Visual Composer element', 'team-booking'),
                'param_name'  => 'coworker',
                'value'       => $coworkers_array,
                'description' => esc_html_x('Leave blank for all', 'Visual Composer element', 'team-booking'),
                'group'       => esc_html__('Coworkers', 'team-booking')
            ),
            array(
                'type'        => 'dropdown',
                'heading'     => esc_html_x('Alternative slot style', 'Visual Composer element', 'team-booking'),
                'param_name'  => 'slot_style',
                'value'       => array(
                    esc_attr__('Basic', 'team-booking')   => 0,
                    esc_attr__('Elegant', 'team-booking') => 1,
                ),
                'description' => esc_html_x('Renders the slots with an alternative style', 'Visual Composer element', 'team-booking'),
                'group'       => esc_html__('Style', 'team-booking')
            ),
            array(
                'type'        => 'dropdown',
                'heading'     => esc_html_x('How many events', 'Visual Composer element', 'team-booking'),
                'param_name'  => 'shown',
                'value'       => array_combine($r = range(1, 40), $r),
                'std'         => 4,
                'description' => esc_html_x('Select how many events must be shown', 'Visual Composer element', 'team-booking')
            ),
            array(
                'type'        => 'checkbox',
                'heading'     => esc_html_x('Show more', 'Visual Composer element', 'team-booking'),
                'param_name'  => 'more',
                'value'       => array(esc_html__('Yes', 'team-booking') => 'yes'),
                'description' => esc_html_x('Allows the loading of more events', 'Visual Composer element', 'team-booking')
            ),
            array(
                'type'        => 'dropdown',
                'heading'     => esc_html_x('Maximum number of events to be shown', 'Visual Composer element', 'team-booking'),
                'param_name'  => 'limit',
                'value'       => array_merge(array(esc_attr__('Unlimited', 'team-booking') => 0), array_combine($r = range(1, 200), $r)),
                'std'         => 0,
                'description' => esc_html_x('If the loading of more events is allowed, you can set a limit for their maximum total number.', 'Visual Composer element', 'team-booking')
            ),
        )
    ));

    vc_map(array(
        'name'                    => esc_html_x('TeamBooking reservations', 'Visual Composer element', 'team-booking'),
        'description'             => esc_html_x('List of reservations placed by the current user', 'Visual Composer element', 'team-booking'),
        'base'                    => 'tb-reservations',
        'admin_enqueue_css'       => TEAMBOOKING_URL . '/css/vc.css',
        'front_enqueue_js'        => TEAMBOOKING_URL . '/js/frontend.js',
        'front_enqueue_css'       => TEAMBOOKING_URL . (is_rtl() ? '/css/frontend_rtl.css' : '/css/frontend.css'),
        'icon'                    => TEAMBOOKING_URL . '/images/logo-black.png',
        'category'                => esc_html_x('Booking', 'Visual Composer category', 'team-booking'),
        'show_settings_on_create' => FALSE,
        'params'                  => array(
            array(
                'type'        => 'checkbox',
                'holder'      => 'span',
                'class'       => 'tbk-vc-read-only',
                'heading'     => esc_html_x('Read only', 'Visual Composer element', 'team-booking'),
                'param_name'  => 'read_only',
                'value'       => array(esc_html__('Yes', 'team-booking') => esc_html_x('Read only', 'Visual Composer element', 'team-booking')),
                'description' => esc_html_x('No action can be performed', 'Visual Composer element', 'team-booking')
            )
        )
    ));

}