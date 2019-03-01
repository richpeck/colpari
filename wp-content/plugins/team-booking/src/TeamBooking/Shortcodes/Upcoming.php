<?php

namespace TeamBooking\Shortcodes;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Cart;
use TeamBooking\Database\Services,
    TeamBooking\Toolkit,
    TeamBooking\Slot,
    TeamBooking\Frontend\Schedule,
    TeamBooking\Functions,
    TeamBooking\Frontend\Components;

/**
 * Class Upcoming
 *
 * @since  2.3.0
 * @author VonStroheim
 */
class Upcoming
{
    /**
     * TeamBooking Upcoming Shortcode
     *
     * @param $atts
     *
     * @return mixed
     * @throws \Exception
     */
    public static function render($atts)
    {
        extract(shortcode_atts(array(
            'service'        => NULL,
            'coworker'       => NULL,
            'read_only'      => FALSE,
            'logged_only'    => FALSE,
            'shown'          => 4,
            'limit'          => 0,
            'more'           => FALSE,
            'slot_style'     => Functions\getSettings()->getSlotStyle(),
            'notimezone'     => FALSE,
            'hide_same_days' => TRUE,
            'descriptions'   => FALSE,
        ), $atts, 'tb-upcoming'));

        if (!defined('TBK_CALENDAR_SHORTCODE_FOUND')) {
            define('TBK_CALENDAR_SHORTCODE_FOUND', TRUE);
        }

        if (!wp_script_is('tb-frontend-script', 'registered')) {
            Functions\registerFrontendResources();
        }

        Functions\enqueueFrontendResources();

        // Read-only mode is identified by lenght of istance id
        $unique_id = !$read_only ? Toolkit\randomNumber(8) : Toolkit\randomNumber(6);

        if (!$logged_only || ($logged_only && is_user_logged_in())) {
            if (NULL !== $service && !empty($service)) {
                $services = array_map('trim', explode(',', $service));
                foreach ($services as $key => $booking) {
                    try {
                        // Remove inactive services
                        if (!Services::get($booking)->isActive() || Services::get($booking)->getClass() === 'unscheduled') {
                            unset($services[ $key ]);
                        }
                    } catch (\Exception $exc) {
                        unset($services[ $key ]);
                        continue;
                    }
                }
                if (empty($services)) {
                    return esc_html__('WARNING: service ID(s) not found. Please check the shortcode syntax and ensure at least one of the specified services is active.', 'team-booking');
                }
            } else {
                // Service(s) not specified, picking all of them
                $services = Functions\getSettings()->getServiceIdList();
                // Remove inactive services
                foreach ($services as $key => $booking) {
                    if (!Services::get($booking)->isActive() || Services::get($booking)->getClass() === 'unscheduled') {
                        unset($services[ $key ]);
                    }
                }
                if (count($services) <= 0) {
                    // Service(s) not specified, but no service available
                    return esc_html__('No active services', 'team-booking');
                }
            }
            $coworkers = NULL !== $coworker ? array_map('trim', explode(',', $coworker)) : array();
            $parameters = new \TeamBooking\RenderParameters();
            $parameters->setServiceIds($services);
            $parameters->setRequestedServiceIds($services);
            $parameters->setCoworkerIds($coworkers);
            $parameters->setRequestedCoworkerIds($coworkers);
            $parameters->setInstance($unique_id);
            $parameters->setTimezone(Toolkit\getTimezone(Functions\parse_timezone_aliases(Cart::getPreference('timezone'))));
            $parameters->setIsAjaxCall(FALSE);
            $parameters->setNoTimezone($notimezone);
            $parameters->setAltSlotStyle($slot_style);
            $parameters->setSlotsShown($shown);
            $parameters->setSlotsLimit($limit);
            $parameters->setShowMore($more);
            $parameters->setHideSameDaysLittleCal(filter_var($hide_same_days, FILTER_VALIDATE_BOOLEAN));
            $parameters->setShowServiceDescriptions($descriptions);
            Functions\parse_query_params($parameters);
            ob_start();
            ?>
            <div class="ui calendar_main_container tbk-upcoming" id="tbk-container-<?= $parameters->getInstance() ?>"
                 aria-live="polite"
                 data-postid="<?= get_the_ID() ?>">
                <?= static::getView($parameters, $read_only) ?>
            </div>
            <script>
                if (typeof tbkLoadInstance === "function") {
                    tbkLoadInstance(jQuery('#tbk-container-<?= $parameters->getInstance() ?>'));
                }
            </script>
            <?php
            return ob_get_clean();
        }
    }

    public static function getView(\TeamBooking\RenderParameters $parameters, $read_only = FALSE)
    {
        $calendar = new \TeamBooking\Calendar();
        $slots = $calendar->getSlots($parameters->getServiceIds(), $parameters->getCoworkerIds(), NULL, NULL, FALSE, $parameters->getTimezone());
        $slots = $slots->getAllSlotsRawSortedByTime();
        if (count($slots) < 1) {
            ob_start();
            echo '<p>' . esc_html__('There are no upcoming events', 'team-booking') . '</p>';

            return ob_get_clean();
        }
        /** @var $slots Slot[] */
        $all_slots_num = count($slots);
        $slots = array_slice($slots, 0, $parameters->getSlotsShown());
        $picked_slots_num = count($slots);
        $timezone_identifier = NULL === Cart::getPreference('timezone') ? $parameters->getTimezone()->getName() : Cart::getPreference('timezone');
        ob_start();
        if (!$parameters->getIsAjaxCall()) {
            ?>
            <div class="tbk-main-calendar-settings tbk-noselection">
                <?php
                echo \TeamBooking\Frontend\Calendar::getCalendarStyle();
                if (Functions\getSettings()->allowCart()) {
                    echo Components\Cart::getCartButton($parameters->getIsWidget());
                }
                if (!$parameters->getNoTimezone()) { ?>
                    <div>
                        <?php if (!$parameters->getNoTimezone() && in_array(TRUE, Functions\getSettings()->getContinentsAllowed(), TRUE)) { ?>
                            <div class="tbk-setting-button tbk-timezones" tabindex="0" style="margin: 0"
                                 title="<?= esc_html__('Timezone', 'team-booking') ?>"
                                 aria-label="<?= esc_html__('Timezone', 'team-booking') ?>">
                                <i class="world tb-icon"></i>
                                <?= Functions\timezone_list($timezone_identifier, $parameters->getIsWidget()) ?>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if (!$parameters->getIsAjaxCall()) { ?>
        <div
        class="<?= ($parameters->getIsWidget() === TRUE) ? 'tb-widget' : '' ?> tb-frontend-calendar tbk-slide-container"
        data-params="<?= $parameters->encode() ?>" data-instance='<?= $parameters->getInstance() ?>'>
        <?= Components\Dimmer::getMarkup() ?>
        <div class="tbk-slide-canvas tbk-slide-0">
        <div class="tbk-slide">
    <?php } ?>
        <ul>
            <?php
            $prev_day = '';
            foreach ($slots as $slot) {
                try {
                    $service = Services::get($slot->getServiceId());
                } catch (\Exception $e) {
                    continue;
                }

                $slot->setTimezone($parameters->getTimezone()->getName());

                $classes = 'tbk-upcoming-slot tbk-alt-' . $parameters->getAltSlotStyle();
                if (Functions\getSettings()->allowCart() && Cart::isSlotIn($slot)) {
                    $classes .= ' tbk-in-cart';
                }
                $location = $slot->getLocation();
                $attributes_to_add = ' data-address="' . $location . '" ';

                if ($parameters->getAltSlotStyle() === 1) {
                    $attributes_to_add .= ' style="border-left-color: '
                        . Toolkit\hex2RGBa($service->getColor(), 0.5)
                        . '"';
                } else {
                    $attributes_to_add .= ' style="border: 2px solid '
                        . Toolkit\hex2RGBa($service->getColor(), 0.5)
                        . ';background: ' . Toolkit\hex2RGBa($service->getColor(), 0.2) . '"';
                }

                if ($service->getSettingsFor('bookable') === 'logged_only' && !is_user_logged_in() && !$slot->isSoldout()) {
                    $attributes_to_add .= 'class="' . $classes . ' '
                        . 'tb-book-advice' . '" '
                        . 'data-event="' . $slot->getEventId() . '" ';
                } else {
                    if (!$read_only && !$slot->isSoldout() && !Functions\getSettings()->allowCart()) {
                        $classes .= ' tb-book';
                    } elseif ($read_only || $slot->isSoldout()) {
                        $classes .= ' tbk-read-only';
                    }
                    $attributes_to_add .= 'class="' . $classes . '"';
                    if (!$read_only) {
                        $attributes_to_add .= 'data-slot="' . Toolkit\objEncode($slot, TRUE, $slot->getUniqueId()) . '" ';
                        $attributes_to_add .= 'data-slot-id="' . Toolkit\objEncode($slot->getUniqueId()) . '" ';
                        // Map logic
                        $style = '';
                        if (!empty($location) && !Functions\getSettings()->getMapStyleUseDefault()) {
                            $style = htmlentities(json_encode(Functions\getSettings()->getMapStyle()));
                        }
                        $attributes_to_add .= 'data-mapstyle="' . $style . '" ';
                    }
                    if ($service->getSettingsFor('show_coworker')) {
                        $attributes_to_add .= 'data-coworker="' . $slot->getCoworkerId() . '" ';
                    }
                    if ($slot->isSoldout()) {
                        $attributes_to_add .= ' tabindex="0"';
                    }
                }
                echo '<li>';
                echo '<div class="tbk-calendar-date';
                if ($parameters->getHideSameDaysLittleCal() && $slot->getDateFormatted('Ymd') === $prev_day) echo ' tbk-hidden';
                echo '">';
                echo '<span class="tbk-month">'
                    . date_i18n(
                        (($parameters->getIsWidget() === TRUE) ? 'M' : 'F'),
                        strtotime($slot->getDateFormatted('Y')
                            . '-' . $slot->getDateFormatted('m')
                            . '-01'
                        ))
                    . '</span>';
                echo '<span class="tbk-day">' . $slot->getDateFormatted('d') . '</span>';
                echo '<span class="tbk-weekday">'
                    . date_i18n(
                        (($parameters->getIsWidget() === TRUE) ? 'D' : 'l'),
                        strtotime($slot->getDateFormatted('Y')
                            . '-' . $slot->getDateFormatted('m')
                            . '-' . $slot->getDateFormatted('d')
                        ))
                    . '</span>';
                echo '</div>';
                echo '<div ' . $attributes_to_add . '>';
                echo '<div style="display: block"><div class="tbk-service-name">' . $slot->getServiceName(TRUE)
                    . '</div>'
                    . Schedule::getPriceTag($slot)
                    . Schedule::getTicketsLeft($slot, $service)
                    . '</div>';
                echo '<span class="tbk-times"><i class="wait tb-icon"></i>' . $slot->getTimesString() . '</span>';
                if ($service->getSettingsFor('show_coworker')) {
                    echo '<span class="tbk-coworker"><i class="user tb-icon"></i>' . $slot->getCoworkerDisplayString() . '</span>';
                }

                if ($service->getSettingsFor('show_attendees') !== 'no' && $slot->getAttendeesNumber() > 0) { ?>
                    <div class="description">
                        <div class="tbk-cell">
                            <i class="users tb-icon"></i>
                        </div>
                        <div class="tbk-cell">
                            <?= $slot->getAttendeesList() ?>
                        </div>
                        <div class="tbk-cell" style="text-align: right">
                        </div>
                    </div>
                <?php }

                if ($slot->getLocation() != NULL) {
                    echo '<span class="tbk-location"><i class="marker tb-icon"></i>' . esc_html(ucwords($slot->getLocation())) . '</span>';
                }

                // Custom WordPress hook
                \TeamBooking\Actions\schedule_slot_render($slot);

                if (!$read_only && !$slot->isSoldout() && Functions\getSettings()->allowCart()) {
                    if ($service->getSettingsFor('bookable') === 'logged_only' && !is_user_logged_in()) {
                        echo Components\Slot::actionButtonsDimmer(Toolkit\hex2RGBa($service->getColor(), 0.5));
                    } else {
                        if ($slot->isReadOnly()) {
                            echo Components\Slot::actionButtonsReadOnly(Toolkit\hex2RGBa($service->getColor(), 0.5));
                        } else {
                            echo Components\Slot::actionButtons(Toolkit\hex2RGBa($service->getColor(), 0.5));
                        }
                    }
                }
                if ($parameters->getShowServiceDescriptions()) {
                    echo '<div class="tbk-service-desc">' . $service->getDescription(TRUE) . '</div>';
                }
                echo '</div>';
                echo '</li>';
                $prev_day = $slot->getDateFormatted('Ymd');
            }
            ?>
        </ul>
        <?php if ($all_slots_num !== $picked_slots_num
        && $parameters->getShowMore()
        && ($picked_slots_num < $parameters->getSlotsLimit() || $parameters->getSlotsLimit() === 0)
    ) { ?>
        <div class="tbk-button tbk-show-more" data-increment="4"
             data-limit="<?= $parameters->getSlotsLimit() ?>">
            <?= esc_html__('Show more', 'team-booking') ?>
        </div>
    <?php } ?>
        <?php if (!$parameters->getIsAjaxCall()) { ?>
        </div>
        </div>
        </div>
    <?php } ?>
        <?php
        return ob_get_clean();
    }

}