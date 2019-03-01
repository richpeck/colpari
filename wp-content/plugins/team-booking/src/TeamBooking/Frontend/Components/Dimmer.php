<?php

namespace TeamBooking\Frontend\Components;

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class Dimmer
 *
 * @since  2.5.0
 * @author VonStroheim
 */
class Dimmer
{
    /**
     * @return string
     */
    public static function getMarkup()
    {
        if (!is_user_logged_in()) {
            if (\TeamBooking\Functions\getSettings()->getCookiePolicy() !== 2) {
                \TeamBooking\Cart::cleanPreference('keep_preferences');
            }
            $show_consent = \TeamBooking\Functions\getSettings()->getCookiePolicy() === 2
                && NULL === \TeamBooking\Cart::getPreference('keep_preferences');
            if ($show_consent) {
                return self::getConsentScreen();
            }
        }
        ob_start();
        ?>
        <div class="tbk-dimmer"></div>
        <?php
        return ob_get_clean();
    }

    /**
     * @return string
     */
    public static function getConsentScreen()
    {
        ob_start();
        ?>
        <div class="tbk-dimmer tbk-active">
            <div style="text-align:center;margin:10px 0 20px 0;padding: 0 20px;">
                <h1 style="font-weight: 700;"><?= esc_html__('User preferences consent') ?></h1>
                <p style="font-size: 16px;">
                    <?= esc_html__('This calendar uses technical cookies to provide the service. The cookies can be also used to keep your calendar preferences.') ?>
                </p>
                <p style="font-size: 16px;">
                    <?= esc_html__('To keep such preferences we need your explicit consent.') ?>
                </p>
                <div style="display: inline-block">
                    <a href="#"
                       class="tbk-button tbk-red tbk-cookie tbk-deny"><?= esc_html__('Deny', 'team-booking') ?></a>
                </div>
                <div style="display: inline-block">
                    <a href="#"
                       class="tbk-button tbk-green tbk-cookie tbk-allow"><?= esc_html__('Allow', 'team-booking') ?></a>
                </div>
                <p style="font-size: 12px;line-height: 12px;">
                    <?= esc_html__('If you deny, technical cookies will still be used to provide the service but your preferences will not be saved.') ?>
                    <?= esc_html__('To show this consent screen again, just clear your browser cookies. Calendar cookies are automatically cleared if you do not visit this site within 48hours.') ?>
                </p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}