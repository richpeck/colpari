<?php

namespace TeamBooking\Frontend\Components;

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class AfterPayment
 *
 * @since  2.5.0
 * @author VonStroheim
 */
class AfterPayment
{
    /**
     * @param \TeamBooking_ReservationData $reservation
     *
     * @return string
     */
    public static function get_positive(\TeamBooking_ReservationData $reservation)
    {
        ob_start();
        ?>
        <div class="tbk-slide-body">
            <div class="ui positive message">
                <div class="tbk-header">
                    <?= esc_html__('Thank you!', 'team-booking') ?>
                </div>
                <p><?= esc_html__('Your payment was successful!', 'team-booking') ?></p>
            </div>
            <?php SuccessActions::render($reservation) ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @param  string $order_id
     *
     * @return string
     */
    public static function get_positive_order($order_id)
    {
        ob_start();
        ?>
        <div class="tbk-slide-body">
            <div class="ui positive message">
                <div class="tbk-header">
                    <?= esc_html__('Your payment was successful!', 'team-booking') ?>
                </div>
                <p>
                    <?= sprintf(
                        esc_html__('Your reservations are grouped under the order %s, please take note of it.', 'team-booking'),
                        '<strong>' . $order_id . '</strong>'
                    ) ?>
                </p>
            </div>
            <?php SuccessActions::render_order($order_id) ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @param array                          $single_errors
     * @param \TeamBooking_ReservationData[] $reservations
     *
     * @return string
     */
    public static function get_half_positive_order(array $single_errors, array $reservations)
    {
        ob_start();
        $order_id = reset($reservations)->getOrderId();
        ?>
        <div class="tbk-slide-body">
            <div class="ui positive message">
                <div class="tbk-header">
                    <?= esc_html__('Thank you!', 'team-booking') ?>
                </div>
                <p><?= esc_html__('Your payment was successful!', 'team-booking') ?></p>
                <p><?= esc_html__('However, some minor technical issues occurred about the following reservations:', 'team-booking') ?></p>
                <ul>
                    <?php foreach (array_keys($single_errors) as $reservation_id) {
                        $reservation = $reservations[ $reservation_id ];
                        echo '<li><strong>#' . $reservation->getDatabaseId(TRUE) . '</strong> ' . $reservation->getServiceName(TRUE) . '</li>';
                    } ?>
                </ul>
                <p><?= sprintf(esc_html__('Please get in touch with us providing your order ID (%s), so we can assist you further.', 'team-booking'), $order_id) ?></p>
            </div>
            <?php SuccessActions::render_order($order_id) ?>
        </div>
        <?php
        return ob_get_clean();
    }
}