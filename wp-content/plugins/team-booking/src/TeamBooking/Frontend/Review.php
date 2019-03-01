<?php

namespace TeamBooking\Frontend;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Cart,
    TeamBooking\Functions,
    TeamBooking\Actions,
    TeamBooking\Database,
    TeamBooking\Toolkit,
    TeamBooking\Frontend\Components,
    TeamBooking\ProcessReservation;

/**
 * Class Review
 *
 * @author VonStroheim
 */
class Review
{
    /**
     * @param \TeamBooking_ReservationData $data
     *
     * @return string
     */
    public static function get(\TeamBooking_ReservationData $data)
    {
        add_action('tbk_reservation_review_details', array(
            'TeamBooking\\Frontend\\Review',
            'review_details',
        ));
        add_action('tbk_reservation_review_footer', array(
            'TeamBooking\\Frontend\\Review',
            'review_footer',
        ));
        ob_start();
        try {
            $service = Database\Services::get($data->getServiceId());
            ?>
            <div class="tbk-reservation-review-container" data-reservation="<?= Toolkit\objEncode($data) ?>">
                <div class="tbk-reservation-review-header">
                    <span class="tbk-thin-italic"><?= esc_html__('Review your reservation', 'team-booking') ?></span>
                </div>
                <div class="tbk-reservation-review-content">
                    <?php Actions\reservation_review_details($data); ?>
                </div>
                <div class="tbk-reservation-review-footer">
                    <?php Actions\reservation_review_footer($service); ?>
                </div>
            </div>
            <?php
        } catch (\Exception $e) {
            echo ProcessReservation::getErrorMessage($e->getMessage());
        }

        return ob_get_clean();
    }

    /**
     * @param $text
     */
    public static function review_header($text)
    {
        echo Components\NavigationHeader::InReservationReview($text);
    }

    /**
     * @param \TeamBooking_ReservationData $data
     */
    public static function review_details($data)
    {
        try {
            $service = Database\Services::get($data->getServiceId());
        } catch (\Exception $e) {
            echo ProcessReservation::getErrorMessage($e->getMessage());
            exit;
        }

        ?>
        <table>
            <?php if ($data->getServiceClass() !== 'unscheduled' && $service->getSettingsFor('show_times') !== 'no') {
                $timezone = new \DateTimeZone(Functions\parse_timezone_aliases($data->getCustomerTimezone()));
                $start = Functions\dateFormatter($data->getStart(), $data->isAllDay(), $timezone);
                $end = Functions\dateFormatter($data->getEnd(), $data->isAllDay(), $timezone);
                ?>
                <!-- times row -->
                <tr>
                    <th scope="row">
                        <?= $service->getSettingsFor('show_times') === 'start_time_only' ? esc_html__('When', 'team-booking') : esc_html__('Start', 'team-booking') ?>
                    </th>
                    <td>
                        <?= Actions\review_start_datetime($start->date, $start->time) ?>
                    </td>
                </tr>
                <?php
                if ($service->getSettingsFor('show_times') !== 'start_time_only') {
                    ?>
                    <tr>
                        <th scope="row">
                            <?= esc_html__('End', 'team-booking') ?>
                        </th>
                        <td>
                            <?= Actions\review_end_datetime($end->date, $end->time) ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
            <?php if ($service->getSettingsFor('show_coworker') && NULL !== $data->getCoworker()) { ?>
                <!-- coworker row -->
                <tr>
                    <th scope="row">
                        <?= esc_html__('With', 'team-booking') ?>
                    </th>
                    <td>
                        <?= ucwords(Functions\getSettings()->getCoworkerData($data->getCoworker())->getDisplayName()) ?>
                    </td>
                </tr>
            <?php } ?>
            <!-- form rows -->
            <?php
            foreach ($data->getFormFields() as $field) {
                ?>
                <tr>
                    <th><?= htmlspecialchars_decode($field->getLabel(TRUE), ENT_QUOTES) ?></th>
                    <td>
                        <?= !$field->getValue()
                            ? ('<span class="tbk-not-provided">' . esc_html__('Not provided', 'team-booking') . '</span>')
                            : $field->getValue(TRUE) ?>
                    </td>
                </tr>
            <?php } ?>
            <?php if ($service->getClass() === 'event') { ?>
                <!-- tickets row -->
                <tr>
                    <th scope="row">
                        <?= esc_html__('Tickets', 'team-booking') ?>
                    </th>
                    <td>
                        <?= $data->getTickets() ?>
                    </td>
                </tr>
            <?php } ?>
            <?php if ($data->getPriceIncremented() > 0) { ?>
                <!-- amount row -->
                <tr>
                    <th scope="row">
                        <?= esc_html__('Total amount', 'team-booking') ?>
                    </th>
                    <td class="tbk-total-amount">
                        <?= Functions\currencyCodeToSymbol($data->getTickets() * $data->getPriceIncremented(), $data->getCurrencyCode()) ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <?php if (Functions\isAdmin() && $service->getPrice() > 0) { ?>
        <div class="ui negative message">
            <?= esc_html__('You are logged-in as Administrator. You will skip any eventual payment step!', 'team-booking') ?>
        </div>
    <?php }
    }

    /**
     * @param \TeamBooking\Abstracts\Service $service
     */
    public static function review_footer($service)
    {
        ?>
        <button class="tbk-book-confirmation-button">
            <?= count(Functions\getSettings()->getPaymentGatewaysActive()) === 1
            && $service->getSettingsFor('payment') === 'immediately'
            && $service->getPrice() > 0
                ? esc_html__('Confirm and pay', 'team-booking')
                : esc_html__('Confirm', 'team-booking') ?>
        </button>
        <?php
    }
}