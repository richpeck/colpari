<?php

namespace TeamBooking\Frontend\Components;

use TeamBooking\Database\Services, TeamBooking\Order;

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class AfterReservation
 *
 * @since  2.5.0
 * @author VonStroheim
 */
class AfterReservation
{
    /**
     * @param \TeamBooking_ReservationData $reservation
     *
     * @return string
     */
    public static function get_positive_waiting_approval(\TeamBooking_ReservationData $reservation)
    {
        ob_start();
        ?>
        <div class="tbk-slide-body">
            <div class="tbk-positive-message-form">
                <div class="tbk-message-header">
                    <?= \TeamBooking\Actions\modify_thankyou_content(esc_html__('Thank you for your reservation!', 'team-booking'), $reservation) ?>
                </div>
                <?php
                try {
                    $service = Services::get($reservation->getServiceId());
                    if ($service->getEmailToCustomer('send')) {
                        echo '<p>' . esc_html__("We'll send you an email when the reservation gets approved!", 'team-booking') . '</p>';
                    }
                } catch (\Exception $e) {
                    // nothing
                }
                ?>
            </div>
            <?php SuccessActions::render($reservation) ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @param \TeamBooking_ReservationData $reservation
     *
     * @return string
     */
    public static function get_positive_maybe_payment(\TeamBooking_ReservationData $reservation)
    {
        ob_start();
        try {
            $service = Services::get($reservation->getServiceId());
        } catch (\Exception $e) {
            $service = FALSE;
        }
        ?>
        <div class="tbk-slide-body">
            <div class="tbk-positive-message-form">
                <div class="tbk-message-header">
                    <?= \TeamBooking\Actions\modify_thankyou_content(esc_html__('Thank you for your reservation!', 'team-booking'), $reservation) ?>
                </div>
                <?php if ($service && $service->getEmailToCustomer('send')) {
                    echo '<p>' . esc_html__('We have sent you an email with details.', 'team-booking') . '</p>';
                } ?>
            </div>
            <?php
            if ($service
                && !$reservation->isPaid()
                && $reservation->getPriceIncremented() > 0
                && $service->getSettingsFor('payment') !== 'later'
                && !\TeamBooking\Functions\isAdmin()
                && \TeamBooking\Functions\getSettings()->thereIsAtLeastOneActivePaymentGateway()
            ) {
                ?>
                <div class="ui centered tbk-header">
                    <?= esc_html__('Do you want to pay right now?', 'team-booking') ?>
                </div>
                <?php
                PaymentChoices::render($reservation, FALSE);
            }
            ?>
            <br>
            <?php SuccessActions::render($reservation) ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    public static function get_order_positive($order)
    {
        ob_start();
        ?>
        <div class="tbk-slide-body">
            <div class="ui positive message">
                <div class="tbk-header">
                    <?= esc_html__('Thank you!', 'team-booking') ?>
                </div>
                <p>
                    <?= sprintf(
                        esc_html__('Your reservations are grouped under the order %s, please take note of it.', 'team-booking'),
                        '<strong>' . $order->getId() . '</strong>'
                    ) ?>
                </p>
            </div>
            <?php SuccessActions::render_order($order->getId()) ?>
        </div>
        <?php
        return ob_get_clean();

    }

    /**
     * @param   Order $order
     * @param bool    $all
     *
     * @return string
     * @throws \Exception
     */
    public static function get_order_errors($order, $all = FALSE)
    {
        ob_start();
        ?>
        <div class="tbk-slide-body">
            <div class="ui warning message">
                <div class="tbk-header">
                    <?php if ($all) {
                        echo esc_html__('There are some issues with the reservations which are therefore discarded', 'team-booking');
                    } else {
                        echo esc_html__('The order is placed, anyway there are some issues with the following reservations which are therefore discarded', 'team-booking');
                    } ?>
                </div>
                <ul>
                    <?php
                    foreach ($order->getItemsWithError() as $item) {
                        $slot = \TeamBooking\Mappers\reservationDataToSlot($item);
                        echo '<li>'
                            . '#' . $item->getDatabaseId(TRUE)
                            . ' ' . $item->getServiceName(TRUE) . ' '
                            . '<span style="display: inline-block;">'
                            . $slot->getDateString() . ' '
                            . $slot->getTimesString()
                            . '</span>'
                            . '<span style="display: block;">('
                            . $item->error_text
                            . ')</span>'
                            . '</li>';
                    }
                    ?>
                </ul>
                <?php if (!$all) { ?>
                    <p>
                        <?= sprintf(
                            esc_html__('Your reservations are grouped under the order %s, please take note of it.', 'team-booking'),
                            '<strong>' . $order->getId() . '</strong>'
                        ) ?>
                    </p>
                <?php } ?>
            </div>
            <?php if ($all) {
                FailActions::render_order($order->getId());
            } else {
                SuccessActions::render_order($order->getId());
            } ?>
        </div>
        <?php
        return ob_get_clean();

    }

    /**
     * @param Order $order
     *
     * @return string
     */
    public static function get_order_positive_payment($order)
    {
        ob_start();
        echo NavigationHeader::basic(__('Payment', 'team-booking'));
        ?>
        <div class="tbk-slide-body">
            <div class="ui info message">
                <div class="tbk-header">
                    <?= esc_html__('Order created', 'team-booking') ?>
                </div>
                <p>
                    <?= sprintf(
                        esc_html__('Your reservations are grouped under the order %s, please take note of it.', 'team-booking'),
                        '<strong>' . $order->getId() . '</strong>'
                    ) ?>
                </p>
            </div>
            <div class="ui centered tbk-header">
                <?= esc_html__('Please choose a payment method', 'team-booking') ?>
            </div>
        </div>
        <?php
        echo PaymentChoices::get_order($order);

        return ob_get_clean();
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    public static function get_order_errors_payment($order)
    {
        $decreased = 0;
        ob_start();
        echo NavigationHeader::basic(__('Payment', 'team-booking'));
        ?>
        <div class="tbk-slide-body">
            <div class="ui warning message">
                <div class="tbk-header">
                    <?= esc_html__('The order is placed, anyway there are some issues with the following reservations which are therefore discarded', 'team-booking') ?>
                </div>
                <ul>
                    <?php
                    foreach ($order->getItemsWithError() as $item) {
                        if ($item->isToBePaid()) {
                            $decreased += $item->getPriceIncremented() * $item->getTickets();
                        }
                        $slot = \TeamBooking\Mappers\reservationDataToSlot($item);
                        echo '<li>'
                            . '#' . $item->getDatabaseId(TRUE)
                            . ' ' . $item->getServiceName(TRUE) . ' '
                            . '<span style="display: inline-block;">'
                            . $slot->getDateString() . ' '
                            . $slot->getTimesString()
                            . '</span>'
                            . '<span style="display: block;">('
                            . $item->error_text
                            . ')</span>'
                            . '</li>';
                    }
                    ?>
                </ul>
                <p>
                    <?= sprintf(
                        esc_html__('Your reservations are grouped under the order %s, please take note of it.', 'team-booking'),
                        '<strong>' . $order->getId() . '</strong>'
                    ) ?>
                </p>
            </div>
            <div class="ui centered tbk-header">
                <?= esc_html__('Please choose a payment method', 'team-booking') ?>
                <?php if ($decreased > 0) {
                    $new_amount = $order->get_to_be_paid_amount();
                    $old_amount_f = \TeamBooking\Functions\currencyCodeToSymbol($new_amount + $decreased);
                    $new_amount_f = \TeamBooking\Functions\currencyCodeToSymbol($new_amount);
                    echo ' ('
                        . sprintf(esc_html__('the amount to pay now is decreased from %s to %s', 'team-booking'), $old_amount_f, $new_amount_f)
                        . ')';
                } ?>
            </div>
        </div>
        <?php
        echo PaymentChoices::get_order($order);

        return ob_get_clean();
    }
}