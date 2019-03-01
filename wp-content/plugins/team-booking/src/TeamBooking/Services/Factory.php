<?php

namespace TeamBooking\Services;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Abstracts;

/**
 * Factory class for Services
 *
 * @since    2.2.0
 * @author   VonStroheim
 */
class Factory
{
    /**
     * Create Appointment
     *
     * @param string $id
     * @param string $name
     *
     * @return Appointment
     */
    public static function createAppointment($id, $name)
    {
        $object = new Appointment();
        $object->setId($id);
        $object->setName($name);

        return $object;
    }

    /**
     * Get Appointment
     *
     * @param array $post_array
     *
     * @return Appointment
     */
    public static function getAppointment(array $post_array)
    {
        $object = new Appointment();
        $object->setId($post_array['tbk_id'][0]);
        $object->setName(\TeamBooking\Toolkit\unfilterInput($post_array['tbk_name'][0]));
        $object->setActive($post_array['tbk_active'][0]);
        if (isset($post_array['tbk_slot_duration'][0])) $object->setSlotDuration($post_array['tbk_slot_duration'][0]);
        if (isset($post_array['tbk_color'][0])) $object->setColor($post_array['tbk_color'][0]);
        $object->setDescription(\TeamBooking\Toolkit\unfilterInput($post_array['tbk_description'][0]));
        $object->setLocation($post_array['tbk_location'][0]);
        $object->setPrice($post_array['tbk_price'][0]);
        $object->setRedirectUrl($post_array['tbk_redirect_url'][0]);
        $email = unserialize($post_array['tbk_email_notification_admin'][0]);
        if ($email) {
            $object->setEmailToAdmin('subject', $email['subject']);
            $object->setEmailToAdmin('body', $email['body']);
            $object->setEmailToAdmin('send', $email['send']);
            $object->setEmailToAdmin('to', $email['to']);
            $object->setEmailToAdmin('attachments', $email['attachments']);
        }
        $email = unserialize($post_array['tbk_email_notification_customer'][0]);
        if ($email) {
            $object->setEmailToCustomer('subject', $email['subject']);
            $object->setEmailToCustomer('body', $email['body']);
            $object->setEmailToCustomer('send', $email['send']);
            $object->setEmailToCustomer('from', isset($email['from']) ? $email['from'] : 'admin');
        }
        $email = unserialize($post_array['tbk_email_cancellation_admin'][0]);
        if ($email) {
            $object->setEmailCancellationToAdmin('subject', $email['subject']);
            $object->setEmailCancellationToAdmin('body', $email['body']);
            $object->setEmailCancellationToAdmin('send', $email['send']);
        }
        $email = unserialize($post_array['tbk_email_cancellation_customer'][0]);
        if ($email) {
            $object->setEmailCancellationToCustomer('subject', $email['subject']);
            $object->setEmailCancellationToCustomer('body', $email['body']);
            $object->setEmailCancellationToCustomer('send', $email['send']);
            $object->setEmailCancellationToCustomer('from', isset($email['from']) ? $email['from'] : 'admin');
        }
        $email = unserialize($post_array['tbk_email_reminder_customer'][0]);
        if ($email) {
            $object->setEmailReminder('subject', $email['subject']);
            $object->setEmailReminder('body', $email['body']);
            $object->setEmailReminder('send', $email['send']);
            $object->setEmailReminder('days_before', $email['days_before']);
            $object->setEmailReminder('from', isset($email['from']) ? $email['from'] : 'admin');
        }
        self::legacyProperties($post_array, $object);
        if (isset($post_array['_tbk_location'][0])) $object->setSettingsFor('location', $post_array['_tbk_location'][0]);
        if (isset($post_array['_tbk_location_visibility'][0])) $object->setSettingsFor('location_visibility', $post_array['_tbk_location_visibility'][0]);
        if (isset($post_array['_tbk_redirect'][0])) $object->setSettingsFor('redirect', $post_array['_tbk_redirect'][0]);
        if (isset($post_array['_tbk_payment'][0])) $object->setSettingsFor('payment', $post_array['_tbk_payment'][0]);
        if (isset($post_array['_tbk_bookable'][0])) $object->setSettingsFor('bookable', $post_array['_tbk_bookable'][0]);
        if (isset($post_array['_tbk_customer_cancellation'][0])) $object->setSettingsFor('customer_cancellation', $post_array['_tbk_customer_cancellation'][0]);
        if (isset($post_array['_tbk_cancellation_reason_allowed'][0])) $object->setSettingsFor('cancellation_reason_allowed', $post_array['_tbk_cancellation_reason_allowed'][0]);
        if (isset($post_array['_tbk_show_times'][0])) $object->setSettingsFor('show_times', $post_array['_tbk_show_times'][0]);
        if (isset($post_array['_tbk_show_soldout'][0])) $object->setSettingsFor('show_soldout', $post_array['_tbk_show_soldout'][0]);
        if (isset($post_array['_tbk_treat_discarded_free_slots'][0])) $object->setSettingsFor('treat_discarded_free_slots', $post_array['_tbk_treat_discarded_free_slots'][0]);
        if (isset($post_array['_tbk_show_coworker'][0])) $object->setSettingsFor('show_coworker', $post_array['_tbk_show_coworker'][0]);
        if (isset($post_array['_tbk_show_coworker_url'][0])) $object->setSettingsFor('show_coworker_url', $post_array['_tbk_show_coworker_url'][0]);
        if (isset($post_array['_tbk_show_service_name'][0])) $object->setSettingsFor('show_service_name', $post_array['_tbk_show_service_name'][0]);
        if (isset($post_array['_tbk_slot_duration'][0])) $object->setSettingsFor('slot_duration', $post_array['_tbk_slot_duration'][0]);
        if (isset($post_array['_tbk_cancellation_allowed_until'][0])) $object->setSettingsFor('cancellation_allowed_until', $post_array['_tbk_cancellation_allowed_until'][0]);
        if (isset($post_array['_tbk_approval_rule'][0])) $object->setSettingsFor('approval_rule', $post_array['_tbk_approval_rule'][0]);
        if (isset($post_array['_tbk_free_until_approval'][0])) $object->setSettingsFor('free_until_approval', $post_array['_tbk_free_until_approval'][0]);
        if (isset($post_array['_tbk_show_map'][0])) $object->setSettingsFor('show_map', $post_array['_tbk_show_map'][0]);
        if (isset($post_array['_tbk_show_attendees'][0])) $object->setSettingsFor('show_attendees', $post_array['_tbk_show_attendees'][0]);
        if (isset($post_array['tbk_form'][0])) $object->setForm($post_array['tbk_form'][0]);

        return $object;
    }

    /**
     * Create Event
     *
     * @param string $id
     * @param string $name
     *
     * @return Event
     */
    public static function createEvent($id, $name)
    {
        $object = new Event();
        $object->setId($id);
        $object->setName($name);

        return $object;
    }

    /**
     * Get Event
     *
     * @param array $post_array
     *
     * @return Event
     */
    public static function getEvent(array $post_array)
    {
        $object = new Event();
        $object->setId($post_array['tbk_id'][0]);
        $object->setName(\TeamBooking\Toolkit\unfilterInput($post_array['tbk_name'][0]));
        $object->setActive($post_array['tbk_active'][0]);
        if (isset($post_array['tbk_slot_duration'][0])) $object->setSlotDuration($post_array['tbk_slot_duration'][0]);
        if (isset($post_array['tbk_color'][0])) $object->setColor($post_array['tbk_color'][0]);
        $object->setDescription(\TeamBooking\Toolkit\unfilterInput($post_array['tbk_description'][0]));
        $object->setLocation($post_array['tbk_location'][0]);
        $object->setPrice($post_array['tbk_price'][0]);
        $object->setRedirectUrl($post_array['tbk_redirect_url'][0]);
        $object->setSlotMaxTickets($post_array['tbk_slot_max_tickets'][0]);
        $object->setSlotMaxUserTickets($post_array['tbk_slot_max_user_tickets'][0]);
        $email = unserialize($post_array['tbk_email_notification_admin'][0]);
        if ($email) {
            $object->setEmailToAdmin('subject', $email['subject']);
            $object->setEmailToAdmin('body', $email['body']);
            $object->setEmailToAdmin('send', $email['send']);
            $object->setEmailToAdmin('to', $email['to']);
            $object->setEmailToAdmin('attachments', $email['attachments']);
        }
        $email = unserialize($post_array['tbk_email_notification_customer'][0]);
        if ($email) {
            $object->setEmailToCustomer('subject', $email['subject']);
            $object->setEmailToCustomer('body', $email['body']);
            $object->setEmailToCustomer('send', $email['send']);
            $object->setEmailToCustomer('from', isset($email['from']) ? $email['from'] : 'admin');
        }
        $email = unserialize($post_array['tbk_email_cancellation_admin'][0]);
        if ($email) {
            $object->setEmailCancellationToAdmin('subject', $email['subject']);
            $object->setEmailCancellationToAdmin('body', $email['body']);
            $object->setEmailCancellationToAdmin('send', $email['send']);
            $email = unserialize($post_array['tbk_email_cancellation_customer'][0]);
            $object->setEmailCancellationToCustomer('subject', $email['subject']);
            $object->setEmailCancellationToCustomer('body', $email['body']);
            $object->setEmailCancellationToCustomer('send', $email['send']);
            $object->setEmailCancellationToCustomer('from', isset($email['from']) ? $email['from'] : 'admin');
        }
        $email = unserialize($post_array['tbk_email_reminder_customer'][0]);
        if ($email) {
            $object->setEmailReminder('subject', $email['subject']);
            $object->setEmailReminder('body', $email['body']);
            $object->setEmailReminder('send', $email['send']);
            $object->setEmailReminder('days_before', $email['days_before']);
            $object->setEmailReminder('from', isset($email['from']) ? $email['from'] : 'admin');
        }
        self::legacyProperties($post_array, $object);
        if (isset($post_array['_tbk_location'][0])) $object->setSettingsFor('location', $post_array['_tbk_location'][0]);
        if (isset($post_array['_tbk_location_visibility'][0])) $object->setSettingsFor('location_visibility', $post_array['_tbk_location_visibility'][0]);
        if (isset($post_array['_tbk_redirect'][0])) $object->setSettingsFor('redirect', $post_array['_tbk_redirect'][0]);
        if (isset($post_array['_tbk_payment'][0])) $object->setSettingsFor('payment', $post_array['_tbk_payment'][0]);
        if (isset($post_array['_tbk_bookable'][0])) $object->setSettingsFor('bookable', $post_array['_tbk_bookable'][0]);
        if (isset($post_array['_tbk_customer_cancellation'][0])) $object->setSettingsFor('customer_cancellation', $post_array['_tbk_customer_cancellation'][0]);
        if (isset($post_array['_tbk_cancellation_reason_allowed'][0])) $object->setSettingsFor('cancellation_reason_allowed', $post_array['_tbk_cancellation_reason_allowed'][0]);
        if (isset($post_array['_tbk_show_times'][0])) $object->setSettingsFor('show_times', $post_array['_tbk_show_times'][0]);
        if (isset($post_array['_tbk_show_soldout'][0])) $object->setSettingsFor('show_soldout', $post_array['_tbk_show_soldout'][0]);
        if (isset($post_array['_tbk_treat_discarded_free_slots'][0])) $object->setSettingsFor('treat_discarded_free_slots', $post_array['_tbk_treat_discarded_free_slots'][0]);
        if (isset($post_array['_tbk_show_coworker'][0])) $object->setSettingsFor('show_coworker', $post_array['_tbk_show_coworker'][0]);
        if (isset($post_array['_tbk_show_service_name'][0])) $object->setSettingsFor('show_service_name', $post_array['_tbk_show_service_name'][0]);
        if (isset($post_array['_tbk_show_coworker_url'][0])) $object->setSettingsFor('show_coworker_url', $post_array['_tbk_show_coworker_url'][0]);
        if (isset($post_array['_tbk_slot_duration'][0])) $object->setSettingsFor('slot_duration', $post_array['_tbk_slot_duration'][0]);
        if (isset($post_array['_tbk_cancellation_allowed_until'][0])) $object->setSettingsFor('cancellation_allowed_until', $post_array['_tbk_cancellation_allowed_until'][0]);
        if (isset($post_array['_tbk_approval_rule'][0])) $object->setSettingsFor('approval_rule', $post_array['_tbk_approval_rule'][0]);
        if (isset($post_array['_tbk_free_until_approval'][0])) $object->setSettingsFor('free_until_approval', $post_array['_tbk_free_until_approval'][0]);
        if (isset($post_array['_tbk_show_tickets_left'][0])) $object->setSettingsFor('show_tickets_left', $post_array['_tbk_show_tickets_left'][0]);
        if (isset($post_array['_tbk_show_tickets_left_threeshold'][0])) $object->setSettingsFor('show_tickets_left_threeshold', $post_array['_tbk_show_tickets_left_threeshold'][0]);
        if (isset($post_array['_tbk_show_map'][0])) $object->setSettingsFor('show_map', $post_array['_tbk_show_map'][0]);
        if (isset($post_array['_tbk_show_attendees'][0])) $object->setSettingsFor('show_attendees', $post_array['_tbk_show_attendees'][0]);
        if (isset($post_array['tbk_form'][0])) $object->setForm($post_array['tbk_form'][0]);

        return $object;
    }

    /**
     * Create Unscheduled
     *
     * @param string $id
     * @param string $name
     *
     * @return Unscheduled
     */
    public static function createUnscheduled($id, $name)
    {
        $object = new Unscheduled();
        $object->setId($id);
        $object->setName($name);

        return $object;
    }

    /**
     * Get Unscheduled
     *
     * @param array $post_array
     *
     * @return Unscheduled
     */
    public static function getUnscheduled(array $post_array)
    {
        $object = new Unscheduled();
        $object->setId($post_array['tbk_id'][0]);
        $object->setName(\TeamBooking\Toolkit\unfilterInput($post_array['tbk_name'][0]));
        $object->setActive($post_array['tbk_active'][0]);
        if (isset($post_array['tbk_color'][0])) $object->setColor($post_array['tbk_color'][0]);
        $object->setDescription(\TeamBooking\Toolkit\unfilterInput($post_array['tbk_description'][0]));
        $object->setLocation($post_array['tbk_location'][0]);
        $object->setPrice($post_array['tbk_price'][0]);
        $object->setRedirectUrl($post_array['tbk_redirect_url'][0]);
        $object->setDirectCoworkerId($post_array['tbk_direct_coworker_id'][0]);
        $object->setMaxReservationsUser($post_array['tbk_max_reservations_per_user'][0]);
        $email = unserialize($post_array['tbk_email_notification_admin'][0]);
        if ($email) {
            $object->setEmailToAdmin('subject', $email['subject']);
            $object->setEmailToAdmin('body', $email['body']);
            $object->setEmailToAdmin('send', $email['send']);
            $object->setEmailToAdmin('to', $email['to']);
            $object->setEmailToAdmin('attachments', $email['attachments']);
        }
        $email = unserialize($post_array['tbk_email_notification_customer'][0]);
        if ($email) {
            $object->setEmailToCustomer('subject', $email['subject']);
            $object->setEmailToCustomer('body', $email['body']);
            $object->setEmailToCustomer('send', $email['send']);
            $object->setEmailToCustomer('from', isset($email['from']) ? $email['from'] : 'admin');
            $email = unserialize($post_array['tbk_email_cancellation_admin'][0]);
            $object->setEmailCancellationToAdmin('subject', $email['subject']);
            $object->setEmailCancellationToAdmin('body', $email['body']);
            $object->setEmailCancellationToAdmin('send', $email['send']);
        }
        $email = unserialize($post_array['tbk_email_cancellation_customer'][0]);
        if ($email) {
            $object->setEmailCancellationToCustomer('subject', $email['subject']);
            $object->setEmailCancellationToCustomer('body', $email['body']);
            $object->setEmailCancellationToCustomer('send', $email['send']);
            $object->setEmailCancellationToCustomer('from', isset($email['from']) ? $email['from'] : 'admin');
        }
        self::legacyProperties($post_array, $object);
        if (isset($post_array['_tbk_location'][0])) $object->setSettingsFor('location', $post_array['_tbk_location'][0]);
        if (isset($post_array['_tbk_location_visibility'][0])) $object->setSettingsFor('location_visibility', $post_array['_tbk_location_visibility'][0]);
        if (isset($post_array['_tbk_redirect'][0])) $object->setSettingsFor('redirect', $post_array['_tbk_redirect'][0]);
        if (isset($post_array['_tbk_payment'][0])) $object->setSettingsFor('payment', $post_array['_tbk_payment'][0]);
        if (isset($post_array['_tbk_bookable'][0])) $object->setSettingsFor('bookable', $post_array['_tbk_bookable'][0]);
        if (isset($post_array['_tbk_customer_cancellation'][0])) $object->setSettingsFor('customer_cancellation', $post_array['_tbk_customer_cancellation'][0]);
        if (isset($post_array['_tbk_cancellation_reason_allowed'][0])) $object->setSettingsFor('cancellation_reason_allowed', $post_array['_tbk_cancellation_reason_allowed'][0]);
        if (isset($post_array['_tbk_show_coworker'][0])) $object->setSettingsFor('show_coworker', $post_array['_tbk_show_coworker'][0]);
        if (isset($post_array['_tbk_show_coworker_url'][0])) $object->setSettingsFor('show_coworker_url', $post_array['_tbk_show_coworker_url'][0]);
        if (isset($post_array['_tbk_assignment_rule'][0])) $object->setSettingsFor('assignment_rule', $post_array['_tbk_assignment_rule'][0]);
        if (isset($post_array['_tbk_show_map'][0])) $object->setSettingsFor('show_map', $post_array['_tbk_show_map'][0]);
        if (isset($post_array['_tbk_approval_rule'][0])) $object->setSettingsFor('approval_rule', $post_array['_tbk_approval_rule'][0]);
        if (isset($post_array['tbk_form'][0])) $object->setForm($post_array['tbk_form'][0]);

        return $object;
    }

    private static function legacyProperties($post_array, Abstracts\Service $object)
    {
        if (isset($post_array['_tbk_logged_only'][0])) {
            $object->setSettingsFor(
                'bookable',
                $post_array['_tbk_logged_only'][0] ? 'logged_only' : 'everyone'
            );
        }

    }

}