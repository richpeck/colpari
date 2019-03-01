<?php

namespace TeamBooking\Frontend\Components;
defined('ABSPATH') or die('No script kiddies please!');

class NavigationHeader
{
    /**
     * Basic header with back arrow and text
     *
     * @param $text
     *
     * @return string
     */
    public static function basicBack($text)
    {
        ob_start();
        ?>
        <div class="ui tbk-grid" style="margin: 0">
            <div class="tbk-row">
                <div class="three wide tbk-column tbk-back-to" tabindex="0"
                     aria-label="<?= esc_html__('back', 'team-booking') ?>">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </div>
                <div class="ten wide tbk-column">
                    <?= \TeamBooking\Functions\tb_mb_strtoupper(esc_html($text)) ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Basic header with text
     *
     * @param $text
     *
     * @return string
     */
    public static function basic($text)
    {
        ob_start();
        ?>
        <div class="ui tbk-grid" style="margin: 0">
            <div class="tbk-row">
                <div class="sixteen wide tbk-column">
                    <?= \TeamBooking\Functions\tb_mb_strtoupper(esc_html($text)) ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Reservation form's header navigation row
     *
     * @param string $date
     *
     * @return string
     */
    public static function InReservationForm($date)
    {
        $additional_classes = '';
        if (\TeamBooking\Functions\getSettings()->allowCart()) {
            $additional_classes .= 'tbk-show-cart-menu';
        }
        ob_start();
        ?>
        <div class="ui tbk-grid" style="margin: 0">
            <div class="tbk-row tbk-reservation-form-navigation">
                <div class="three wide tbk-column tbk-back-to <?= $additional_classes ?>" tabindex="0"
                     aria-label="<?= esc_html__('back', 'team-booking') ?>">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </div>
                <div class="ten wide tbk-column tbk-schedule-date">
                    <?= $date ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Reservation review's header navigation row
     *
     * @param $service_name
     *
     * @return string
     */
    public static function InReservationReview($service_name)
    {
        ob_start();
        ?>
        <div class="ui tbk-grid" style="margin: 0">
            <div class="tbk-row tbk-resevation-review-navigation">
                <div class="three wide tbk-column tbk-back-to tbk-back-to-form" tabindex="0"
                     aria-label="<?= esc_html__('back to form', 'team-booking') ?>">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </div>
                <div class="ten wide tbk-column">
                    <?= \TeamBooking\Functions\tb_mb_strtoupper($service_name) ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Payment choice header navigation row
     *
     * @param $service_name
     *
     * @return string
     */
    public static function InPaymentChoice($service_name)
    {
        ob_start();
        ?>
        <div class="ui tbk-grid" style="margin: 0">
            <div class="tbk-row tbk-resevation-review-navigation">
                <div class="three wide tbk-column tbk-back-to tbk-back-to-review" tabindex="0"
                     aria-label="<?= esc_html__('back to reservation review', 'team-booking') ?>">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </div>
                <div class="ten wide tbk-column">
                    <?= \TeamBooking\Functions\tb_mb_strtoupper($service_name) ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Onsite payment form header navigation row
     *
     * @param null $amount
     *
     * @return string
     */
    public static function InPaymentForm($amount = NULL)
    {
        ob_start();
        ?>
        <div class="ui tbk-grid" style="margin: 0">
            <div class="tbk-row tbk-resevation-review-navigation">
                <div class="three wide tbk-column tbk-back-to tbk-back-to-payment-choice" tabindex="0"
                     aria-label="<?= esc_html__('back to payment choice', 'team-booking') ?>">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </div>
                <div class="ten wide tbk-column">
                    <?= \TeamBooking\Functions\tb_mb_strtoupper(esc_html__('Payment', 'team-booking')) ?>
                    <?= NULL === $amount ? '' : (' - <span class="tbk-navigation-header-emphasis">' . \TeamBooking\Functions\getSettings()->getCurrencyCode() . ' ' . $amount . '</span>') ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Successful payment header navigation row
     *
     * @return string
     */
    public static function InPaymentSuccess()
    {
        ob_start();
        ?>
        <div class="ui tbk-grid" style="margin: 0">
            <div class="tbk-row tbk-payment-done-navigation">
                <div class="sixteen wide tbk-column">
                    <?= \TeamBooking\Functions\tb_mb_strtoupper(esc_html__('Payment done', 'team-booking')) ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Successful reservation header navigation row
     *
     * @return string
     */
    public static function InReservationSuccess()
    {
        ob_start();
        ?>
        <div class="ui tbk-grid" style="margin: 0">
            <div class="tbk-row tbk-reservation-done-navigation">
                <div class="sixteen wide tbk-column">
                    <?= \TeamBooking\Functions\tb_mb_strtoupper(esc_html__('Thank you', 'team-booking')) ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Error header navigation row
     *
     * @return string
     */
    public static function InError()
    {
        ob_start();
        ?>
        <div class="ui tbk-grid" style="margin: 0">
            <div class="tbk-row tbk-reservation-error-navigation">
                <div class="three wide tbk-column tbk-back-to" tabindex="0"
                     aria-label="<?= esc_html__('go back', 'team-booking') ?>">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </div>
                <div class="ten wide tbk-column">
                    <?= \TeamBooking\Functions\tb_mb_strtoupper(esc_html__('Error', 'team-booking')) ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Slots list header navigation row
     *
     * @param \TeamBooking\RenderParameters $parameters
     * @param \TeamBooking\SlotsResults     $slots
     *
     * @return string
     */
    public static function InSlotsList(\TeamBooking\RenderParameters $parameters, \TeamBooking\SlotsResults $slots)
    {
        ob_start();
        ?>
        <div class="ui tbk-grid" style="margin: 0">
            <div class="tbk-row tbk-schedule-list-navigation">
                <?php if ($parameters->isDirectScheduleCall()) { ?>
                    <div class="three wide tbk-column"></div>
                <?php } else { ?>
                    <div class="three wide tbk-column tbk-back-to tbk-back-to-calendar" tabindex="0"
                         aria-label="<?= esc_html__('back to calendar', 'team-booking') ?>">
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                    </div>
                <?php } ?>
                <div class="ten wide tbk-column tbk-schedule-date">
                    <?= date_i18n(get_option('date_format'), mktime(0, 0, 0, $parameters->getMonth(), $parameters->getDay(), $parameters->getYear())) ?>
                </div>
                <div class="three wide tbk-column" style="padding: 8px 0 0 0;">
                    <div class="tbk-schedule-filter-icons">
                        <?php
                        $coworkers_list = $slots->getShownCoworkers();
                        $locations_list = $slots->getLocationsList();
                        ?>
                        <i class="wait tb-icon tbk-schedule-filter-icon" data-target="time" tabindex="0"
                           aria-label="<?= esc_html__('filter by time', 'team-booking') ?>"></i>
                        <?php if (count($locations_list) > 1) { ?>
                            <i class="marker tb-icon tbk-schedule-filter-icon" data-target="location"
                               tabindex="0"
                               aria-label="<?= esc_html__('filter by location', 'team-booking') ?>"></i>
                        <?php } ?>
                        <?php if (count($coworkers_list) > 1) { ?>
                            <i class="user tb-icon tbk-schedule-filter-icon" data-target="coworker"
                               tabindex="0"
                               aria-label="<?= esc_html__('filter by coworker', 'team-booking') ?>"></i>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

}