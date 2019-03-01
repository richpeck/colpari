<?php

namespace TeamBooking\Shortcodes;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Toolkit,
    TeamBooking\Functions,
    TeamBooking\Database;

/**
 * Class Reservations
 *
 * @author VonStroheim
 */
class Reservations
{
    /**
     * @param $atts
     *
     * @return mixed
     */
    public static function render($atts)
    {
        if (!is_user_logged_in()) {
            return FALSE;
        }

        // Set attributes
        extract(shortcode_atts(array(
            'read_only' => FALSE,
        ), $atts, 'tb-reservations'));

        if (!defined('TBK_RESERV_SHORTCODE_FOUND')) {
            define('TBK_RESERV_SHORTCODE_FOUND', TRUE);
        }

        if (!wp_script_is('tb-frontend-script', 'registered')) {
            Functions\registerFrontendResources();
        }

        Functions\enqueueFrontendResources();

        $user_id = get_current_user_id();
        $timezone = Toolkit\getTimezone();
        $now = current_time('timestamp', TRUE);
        $random_append = Toolkit\randomNumber(6);
        $reservations = Database\Reservations::getAll();
        uasort($reservations, function ($a, $b) {
            /* @var $a \TeamBooking_ReservationData */
            /* @var $b \TeamBooking_ReservationData */
            return ($a->getStart() < $b->getStart()) ? -1 : (($a->getStart() > $b->getStart()) ? 1 : 0);
        });

        ob_start();
        echo "<div class='tbk-reservations-list-container'>";
        echo "<table class='tb-reservations-list'>";
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . esc_html__('Service', 'team-booking') . '</th>';
        echo '<th>' . esc_html__('When', 'team-booking') . '</th>';
        echo '<th>' . esc_html__('Who', 'team-booking') . '</th>';
        echo '<th>' . esc_html__('Status', 'team-booking') . '</th>';
        if (!$read_only) {
            echo '<th>' . esc_html__('Actions', 'team-booking') . '</th>';
        }
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($reservations as $id => $reservation) {
            $timezone = Toolkit\getTimezone(Functions\parse_timezone_aliases($reservation->getCustomerTimezone()));
            if ($reservation->getCustomerUserId() == $user_id) {
                if ($reservation->getServiceClass() === 'unscheduled') {
                    $date_time_object = \DateTime::createFromFormat('U', $reservation->getCreationInstant());
                    if ($reservation->isDone()) {
                        continue;
                    }
                    $when_value = esc_html__('Unscheduled', 'team-booking');
                    $date_time_object->setTimezone($timezone);
                    $slot_starting_time_in_seconds = $date_time_object->getTimestamp() + $date_time_object->getOffset();
                } else {
                    $date_time_object = new \DateTime($reservation->getSlotStart(), $timezone);
                    if ($date_time_object->getTimestamp() < $now) {
                        continue;
                    }
                    $date_time_object->setTimezone($timezone);
                    $slot_starting_time_in_seconds = $date_time_object->getTimestamp() + $date_time_object->getOffset();
                    if ($reservation->isAllDay()) {
                        $when_value = Functions\dateFormatter($reservation->getStart(), TRUE, $timezone)->date;
                    } else {
                        $when_value = Functions\dateFormatter($reservation->getStart(), FALSE, $timezone)->date
                            . ' '
                            . Functions\dateFormatter($reservation->getStart(), FALSE, $timezone)->time
                            . ' - '
                            . Functions\dateFormatter($reservation->getEnd(), FALSE, $timezone)->time;
                    }
                }
                $allow_customer_canc = FALSE;
                $coworker_name = '';
                try {
                    $service = Database\Services::get($reservation->getServiceId());
                    if (($slot_starting_time_in_seconds - $now) >= $service->getSettingsFor('cancellation_allowed_until')) {
                        $allow_customer_canc = $service->getSettingsFor('customer_cancellation');
                    }
                    if ($service->getSettingsFor('show_coworker')) {
                        $coworker_name = ucwords(Functions\getSettings()->getCoworkerData($reservation->getCoworker())->getDisplayName());
                    }
                } catch (\Exception $e) {
                    // nothing
                }
                switch ($reservation->getStatus()) {
                    case 'confirmed' :
                        if ($reservation->getServiceClass() === 'unscheduled') {
                            $status_label = '<div class="tbk-slot-label blue">' . esc_html__('todo', 'team-booking') . '</div>';
                        } else {
                            $status_label = '<div class="tbk-slot-label green">' . esc_html__('confirmed', 'team-booking') . '</div>';
                        }
                        break;
                    case 'done':
                        $status_label = '<div class="tbk-slot-label green">' . esc_html__('done', 'team-booking') . '</div>';
                        break;
                    case 'waiting_approval' :
                        $status_label = '<div class="tbk-slot-label yellow">' . esc_html__('waiting approval', 'team-booking') . '</div>';
                        break;
                    case 'pending' :
                        $status_label = '<div class="tbk-slot-label yellow">' . esc_html__('pending', 'team-booking') . ' </div>';
                        break;
                    case 'cancelled' :
                        $status_label = '<div class="tbk-slot-label red">' . esc_html__('cancelled', 'team-booking') . '</div>';
                        break;
                }

                echo '<tr>';
                echo '<td>' . $reservation->getServiceName(TRUE)
                    . ($reservation->getServiceClass() === 'event'
                        ? ' (' . esc_html(sprintf(_n('%s ticket', '%s tickets', $reservation->getTickets()), $reservation->getTickets())) . ')'
                        : '')
                    . '</td>';
                echo '<td>' . $when_value . '</td>';
                echo '<td>' . $coworker_name . '</td>';
                echo '<td>' . $status_label . '</td>';
                if (!$read_only) {
                    echo '<td>'
                        . ((!$reservation->isCancelled()
                            && $allow_customer_canc
                            && $reservation->getServiceClass() !== 'unscheduled') ?
                            '<a href="#" class="tb-cancel-reservation" data-id="'
                            . $id . '" data-hash="' . $reservation->getToken() . '"'
                            . ' data-modal="' . $random_append . '">'
                            . esc_html__('cancel', 'team-booking')
                            . '</a>'
                            : '')
                        . '</td>';
                }
                echo '</tr>';
            }
        }
        echo '</tbody>';
        echo '</table>';

        ?>
        <div class="tbk-reservation-cancel-modal remodal" id="tbk-modal-<?= $random_append ?>"
             data-remodal-options="hashTracking: false, closeOnConfirm: false">
            <h1><?= esc_html__('Are you sure?', 'team-booking') ?></h1>
            <p>
                <?= esc_html__("You're going to cancel this reservation, the action is irreversible.", 'team-booking') ?>
            </p>
            <br>
            <button data-remodal-action="cancel"
                    class="remodal-cancel"><?= esc_html__('Cancel', 'team-booking') ?></button>
            <button data-remodal-action="confirm"
                    class="remodal-confirm"><?= esc_html__('Proceed', 'team-booking') ?></button>
        </div>
        <?php
        echo '</div>';

        return ob_get_clean();
    }
}