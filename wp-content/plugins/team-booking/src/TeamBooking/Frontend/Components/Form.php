<?php

namespace TeamBooking\Frontend\Components;
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class Form
 *
 * @author VonStroheim
 */
class Form
{
    /**
     * @param string $name
     * @param string $times
     * @param string $coworker
     *
     * @return string
     */
    public static function header($name, $times = '', $coworker = '')
    {
        ob_start();
        ?>
        <div class="tbk-reservation-form-header">
            <div class="tbk-title">
                <?= sprintf('<span class="tbk-thin-italic">' . esc_html__('Reservation for %s', 'team-booking') . '</span>', esc_html($name)) ?>
            </div>
            <div class="tbk-meta" style="white-space: normal;">
                <!-- times string -->
                <?php if (!empty($times)) { ?>
                    <i class="wait tb-icon"></i>
                    <div class="tbk-reservation-form-header-times">
                        <?= $times ?>
                    </div>
                <?php } ?>
                <!-- coworker string -->
                <?php if (!empty($coworker)) { ?>
                    <div class="tbk-reservation-form-header-coworker">
                        <i class="user tb-icon"></i>
                        <?= $coworker ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @param $text
     *
     * @return string
     */
    public static function basic_header($text)
    {
        ob_start();
        ?>
        <div class="tbk-reservation-form-header">
            <div class="tbk-title">
                <span class="tbk-thin-italic"><?= $text ?></span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @param string $location
     * @param bool   $show_map
     *
     * @return string
     */
    public static function map($location, $show_map = TRUE)
    {
        ob_start();
        ?>
        <div class="ui horizontal tbk-divider tbk-map">
            <i class="marker tb-icon"></i>
        </div>
        <div class="tbk-map tbk-address">
            <?= $location ?>
            <a class="tbk-get-directions" target="_blank"
               href="//maps.google.com?daddr=<?= $location ?>">
                (<?= esc_html__('get directions', 'team-booking') ?>)
            </a>
        </div>
        <?php
        if ($show_map) {
            $style = '';
            $id = 'tbk-map-canvas' . \TeamBooking\Toolkit\randomNumber(10);
            if (!\TeamBooking\Functions\getSettings()->getMapStyleUseDefault()) {
                $style = htmlentities(json_encode(\TeamBooking\Functions\getSettings()->getMapStyle()));
            }
            ?>
            <div class="tbk-segment tbk-map tbk-map-canvas" id="<?= $id ?>"
                 data-zoom="<?= \TeamBooking\Functions\getSettings()->getGmapsZoomLevel() ?>"
                 data-style="<?= $style ?>"
                 data-address="<?= $location ?>">
            </div>
            <script>
                jQuery(document).ready(function ($) {
                    $('#<?=$id?>').tbkMaps({
                        address   : '<?= $location ?>',
                        mapstyle  : '<?= $style ?>',
                        zoom_level: <?= \TeamBooking\Functions\getSettings()->getGmapsZoomLevel() ?>
                    })
                });
            </script>
            <?php
        }

        return ob_get_clean();
    }

    /**
     * @param $description
     *
     * @return string
     */
    public static function serviceDescription($description)
    {
        ob_start();
        ?>
        <div class="tbk-service-description">
            <p><?= $description ?></p>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @param string $service_name
     *
     * @return string
     */
    public static function reservationsLimitReached($service_name)
    {
        ob_start();
        ?>
        <div style="margin:10px 0 20px 0;text-align: center;">
            <div class="tbk-reservation-limit-service-name"><?= $service_name ?></div>
            <?= esc_html__("You've reached the limit for that service!", 'team-booking') ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @param string $service_name
     *
     * @return string
     */
    public static function ticketsLimitReached($service_name)
    {
        ob_start();
        ?>
        <div style="margin:10px 0 20px 0;text-align: center;">
            <div class="tbk-reservation-limit-service-name"><?= $service_name ?></div>
            <?= esc_html__("You've reached the tickets limit for that slot!", 'team-booking') ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @return string
     */
    public static function adminNoLimitsAdvice()
    {
        ob_start();
        ?>
        <p class="ui negative message tbk-logged-admin-advice">
            <?= esc_html__("You are logged in as Administrator, any limit on tickets number won't be applied", 'team-booking') ?>
        </p>
        <?php
        return ob_get_clean();
    }

    /**
     * @return string
     */
    public static function adminReadOnlyAdvice()
    {
        ob_start();
        ?>
        <p class="ui negative message tbk-logged-admin-advice">
            <?= esc_html__('This service/slot is read-only because of the settings. Only administrators and service providers can see this message.', 'team-booking') ?>
        </p>
        <?php
        return ob_get_clean();
    }

    /**
     * @param bool $files
     *
     * @return string
     */
    public static function checkoutFooterActions($files)
    {
        ob_start();
        ?>
        <button class="tbk-button tbk-cart-cancel-process">
            <?= esc_html(__('Cancel', 'team-booking')) ?>
        </button>
        <button class="tbk-button tbk-cart-next-step" type="submit"
                data-files="<?= $files ? 1 : 0 ?>">
            <?= esc_html(__('Next', 'team-booking')) ?>
        </button>
        <?php
        return ob_get_clean();
    }

    /**
     * @param bool $files
     *
     * @return string
     */
    public static function editFormFooterActions($files)
    {
        ob_start();
        ?>
        <button class="tbk-button tbk-edit-form-action tbk-cancel">
            <?= esc_html(__('Back', 'team-booking')) ?>
        </button>
        <button class="tbk-button tbk-edit-form-action tbk-cart-next-step" type="submit"
                data-files="<?= $files ? 1 : 0 ?>">
            <?= esc_html(__('Save', 'team-booking')) ?>
        </button>
        <?php
        return ob_get_clean();
    }

}