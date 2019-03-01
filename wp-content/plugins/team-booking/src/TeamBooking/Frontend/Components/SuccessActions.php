<?php

namespace TeamBooking\Frontend\Components;
defined('ABSPATH') or die("No script kiddies please!");

/**
 * Class SuccessActions
 *
 * Shows the action buttons after a successful reservation
 *
 * @author VonStroheim
 * @since  2.5.0
 */
class SuccessActions
{
    /**
     * @param \TeamBooking_ReservationData $data
     */
    public static function render(\TeamBooking_ReservationData $data)
    {
        ?>
        <div class="tbk-after-reservation-actions">
            <?php
            if ($data->getServiceClass() !== 'unscheduled'
                && \TeamBooking\Functions\getSettings()->getShowIcal()
            ) {
                ?>
                <form id="tb-get-ical-form" method="POST" action="<?= admin_url() . 'admin-post.php' ?>">
                    <input type="hidden" name="action" value="tb_get_ical">
                    <?php wp_nonce_field('team_booking_options_verify') ?>
                    <input type="hidden" name="reservation"
                           value="<?= \TeamBooking\Toolkit\objEncode($data->getDatabaseId(), TRUE) ?>">
                    <button type="submit" class="tbk-button tb-get-ical" tabindex="0">
                        <i class="tb-icon calendar outline"></i>
                        <?= esc_html__('Save on my calendar', 'team-booking') ?>
                    </button>
                </form>
            <?php } ?>
            <button class="tbk-button tbk-green tbk-refresh" tabindex="0">
                <?= esc_html__('Ok', 'team-booking') ?>
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
            <?php
            if (\TeamBooking\Functions\getSettings()->getShowIcal()) {
                ?>
                <form id="tb-get-ical-form" method="POST" action="<?= admin_url() . 'admin-post.php' ?>">
                    <input type="hidden" name="action" value="tb_get_ical">
                    <?php wp_nonce_field('team_booking_options_verify') ?>
                    <input type="hidden" name="order"
                           value="<?= \TeamBooking\Toolkit\objEncode($order_id, TRUE) ?>">
                    <button type="submit" class="tbk-button tb-get-ical" tabindex="0">
                        <i class="tb-icon calendar outline"></i>
                        <?= esc_html__('Save on my calendar', 'team-booking') ?>
                    </button>
                </form>
            <?php } ?>
            <button class="tbk-button tbk-green tbk-refresh" tabindex="0">
                <?= esc_html__('Ok', 'team-booking') ?>
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