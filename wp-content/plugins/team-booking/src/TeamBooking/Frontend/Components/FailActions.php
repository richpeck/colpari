<?php

namespace TeamBooking\Frontend\Components;
defined('ABSPATH') or die("No script kiddies please!");

/**
 * Class FailActions
 *
 * Shows the action buttons after a failed reservation
 *
 * @author VonStroheim
 * @since  2.5.0
 */
class FailActions
{
    /**
     * @param \TeamBooking_ReservationData $data
     */
    public static function render(\TeamBooking_ReservationData $data)
    {
        ?>
        <div class="tbk-after-reservation-actions">
            <button class="tbk-button tbk-refresh" tabindex="0">
                <?= esc_html__('Go back', 'team-booking') ?>
            </button>
        </div>
        <?php
    }

    /**
     * @param string $order_id
     */
    public static function render_order($order_id)
    {
        ?>
        <div class="tbk-after-reservation-actions">
            <button class="tbk-button tbk-refresh" tabindex="0">
                <?= esc_html__('Go back', 'team-booking') ?>
            </button>
        </div>
        <?php
    }

    /**
     * @param \TeamBooking_ReservationData $data
     *
     * @return string
     */
    public static function get(\TeamBooking_ReservationData $data)
    {
        ob_start();
        self::render($data);

        return ob_get_clean();
    }

    /**
     * @param string $order_id
     *
     * @return string
     */
    public static function get_order($order_id)
    {
        ob_start();
        self::render_order($order_id);

        return ob_get_clean();
    }

}