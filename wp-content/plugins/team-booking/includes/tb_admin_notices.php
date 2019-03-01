<?php

namespace TeamBooking\Admin\Notices;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Functions,
    TeamBooking\Admin\Framework,
    TeamBooking\Toolkit;

//////////////////////
// General  notices //
//////////////////////
function not_configured()
{
    Framework\Notice::getNegative(__('Team Booking is active, yet not configured!', 'team-booking'))->render();
}

function timezone_approx()
{
    $timezone = Toolkit\getTimezone();
    $message = sprintf(
        __('Please set your TimeZone in WordPress general settings! DSTs are not compatible with manual GMT offsets, so TeamBooking is assuming %s as your equivalent Timezone.', 'team-booking'),
        $timezone->getName()
    );
    Framework\Notice::getNegative($message)->render();
}

function generic_success()
{
    Framework\Notice::getPositive(__('Operation completed!', 'team-booking'))->render();
}

////////////////////
// Core notices   //
////////////////////
function core_saved()
{
    Framework\Notice::getPositive(__('Settings saved!', 'team-booking'))->render();
}

function core_imported()
{
    Framework\Notice::getPositive(__('Settings imported!', 'team-booking'))->render();
}

function core_version_mismatch()
{
    Framework\Notice::getNegative(__('This settings file comes from a different plugin version and cannot be imported', 'team-booking'))->render();
}

function core_token_added()
{
    Framework\Notice::getPositive(__('API token created!', 'team-booking'))->render();
}

function core_token_removed()
{
    Framework\Notice::getPositive(__('API token removed!', 'team-booking'))->render();
}

function core_database_repaired()
{
    Framework\Notice::getPositive(__('Database repaired. Please perform a full sync of your Google Calendars now!', 'team-booking'))->render();
}

function core_wrong_file()
{
    Framework\Notice::getNegative(__('Sorry, this is not a TeamBooking settings file!', 'team-booking'))->render();
}

/////////////////////////////
// Manage Services notices //
/////////////////////////////
function service_added()
{
    Framework\Notice::getPositive(__('Service added successfully!', 'team-booking'))->render();
}

function service_updated()
{
    Framework\Notice::getPositive(__('Service updated successfully!', 'team-booking'))->render();
}

function service_deleted()
{
    Framework\Notice::getPositive(__('Service removed successfully!', 'team-booking'))->render();
}

function services_deleted()
{
    Framework\Notice::getPositive(__('Services removed successfully!', 'team-booking'))->render();
}

///////////////////////////////
// Personal Settings notices //
///////////////////////////////
function personal_updated()
{
    Framework\Notice::getPositive(__('Settings saved!', 'team-booking'))->render();
}

function personal_deleted()
{
    Framework\Notice::getPositive(__('Settings deleted successfully!', 'team-booking'))->render();
}

function personal_partial()
{
    $additional_content = sprintf(
            wp_kses(__('Go <a href="%s">here</a> and revoke auth to ', 'team-booking'), array('a' => array('href' => array()))),
            'https://security.google.com/settings/security/permissions'
        )
        . '<strong>' . Functions\getSettings()->getApplicationProjectName() . '</strong><br><br>';
    Framework\Notice::getNegative(
        __('Personal settings deleted, but for some reason you should revoke Google Auth manually!', 'team-booking'),
        $additional_content
    )->render();
}

function personal_success()
{
    Framework\Notice::getPositive(__('You are now fully authorized!', 'team-booking'))->render();
}

function personal_failed()
{
    Framework\Notice::getNegative(__('Something went wrong, are you sure you have asked for authorization?', 'team-booking'))->render();
}

function personal_no_refresh()
{
    Framework\Notice::getNegative(__('It seems that the authorization was already given in the past, but was not correctly revoked lately. Browse your authorized apps using the link in the useful links section, and try to manually revoke it, before trying again. It could also be possible that the Google Account you are logged in is already in use by another coworker.', 'team-booking'))->render();
}

function personal_already_used()
{
    Framework\Notice::getNegative(__('It seems that the Google Account you are logged in is already in use by another coworker.', 'team-booking'))->render();
}

function personal_duplicated_event_title($title, $service_name)
{
    $message = sprintf(esc_html__('%s event title already used for service %s', 'team-booking'), '<code>' . $title . '</code>', '<strong>' . $service_name . '</strong>');
    Framework\Notice::getNegative($message)->render(FALSE);
}

function personal_duplicated_event_booked_title($title, $service_name)
{
    $message = sprintf(esc_html__('%s event booked title already used for service %s', 'team-booking'), '<code>' . $title . '</code>', '<strong>' . $service_name . '</strong>');
    Framework\Notice::getNegative($message)->render(FALSE);
}

function personal_event_title_not_allowed()
{
    $message = sprintf(esc_html__('%s or %s character pairs are not allowed in event titles.', 'team-booking'), '<code>||</code>', '<code>>></code>');
    Framework\Notice::getNegative($message)->render(FALSE);
}

function personal_revoke_failed()
{
    $message = sprintf(__('Error revoking authorization: %s', 'team-booking'), urldecode($_GET['reset_failed']));
    Framework\Notice::getNegative($message)->render();
}

///////////////////////
// Overview  notices //
///////////////////////

function overview_reservation_cancelled()
{
    Framework\Notice::getPositive(__('Reservation successfully cancelled!', 'team-booking'))->render();
}

function overview_reservation_confirmed()
{
    Framework\Notice::getPositive(__('Reservation successfully confirmed!', 'team-booking'))->render();
}

function overview_reservation_not_revokable()
{
    Framework\Notice::getNegative(__('Cancellation not possible for Unscheduled Service class!', 'team-booking'))->render();
}

function overview_generic_error()
{
    Framework\Notice::getNegative(urldecode($_GET['nag_generic_error']))->render();
}

function overview_logs_cleaned()
{
    Framework\Notice::getPositive(__('Error logs successfully cleaned!', 'team-booking'))->render();
}