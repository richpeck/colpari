<?php

namespace TeamBooking\Shortcodes;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Cart;
use TeamBooking\Database\Services,
    TeamBooking\Toolkit,
    TeamBooking\Functions;
use TeamBooking\Frontend\Components\Dimmer;

/**
 * Class Calendar
 *
 * @author VonStroheim
 */
class Calendar
{
    /**
     * TeamBooking Calendar Shortcode
     *
     * @param $atts
     *
     * @return string
     * @throws \Exception
     */
    public static function render($atts)
    {
        extract(shortcode_atts(array(
            'booking'     => NULL,
            'service'     => NULL,
            'coworker'    => NULL,
            'read_only'   => FALSE,
            'logged_only' => FALSE,
            'nofilter'    => FALSE, // Hide filtering buttons
            'notimezone'  => FALSE, // Hide timezone selector
            'slot_style'  => Functions\getSettings()->getSlotStyle(),
        ), $atts, 'tb-calendar'));

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
            if (NULL !== $booking || NULL !== $service) {
                $booking = NULL !== $service ? $service : $booking;
            }
            if (NULL !== $booking && !empty($booking)) {
                $services = array_map('trim', explode(',', $booking));
                foreach ($services as $key => $booking) {
                    try {
                        // Remove inactive services
                        if (!Services::get($booking)->isActive()) {
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
                foreach ($services as $key => $service) {
                    if (!Services::get($service)->isActive()) {
                        unset($services[ $key ]);
                    }
                }
                if (count($services) <= 0) {
                    // Service(s) not specified, but no service available
                    return esc_html__('No active services', 'team-booking');
                }
            }
            $coworkers = NULL !== ($coworker) ? array_map('trim', explode(',', $coworker)) : array();
            $calendar = new \TeamBooking\Calendar();
            $parameters = new \TeamBooking\RenderParameters();
            $parameters->setServiceIds($services);
            $parameters->setRequestedServiceIds($services);
            $parameters->setCoworkerIds($coworkers);
            $parameters->setRequestedCoworkerIds($coworkers);
            $parameters->setInstance($unique_id);
            $parameters->setTimezone(Toolkit\getTimezone(Functions\parse_timezone_aliases(Cart::getPreference('timezone'))));
            $parameters->setIsAjaxCall(FALSE);
            $parameters->setNoFilter($nofilter);
            $parameters->setNoTimezone($notimezone);
            $parameters->setAltSlotStyle($slot_style);
            Functions\parse_query_params($parameters);
            ob_start();
            ?>
            <div class="ui calendar_main_container" id="tbk-container-<?= $unique_id ?>" aria-live="polite"
                 data-postid="<?= get_the_ID() ?>">
                <?php
                if (isset($_GET['tbk_date'])
                    && strtotime($_GET['tbk_date']) !== FALSE
                ) {
                    $skip = FALSE;
                    if (count($services) <= 1) {
                        $service = Services::get($services[0]);
                        if ($service->getClass() === 'unscheduled') $skip = TRUE;
                    }
                    if (!$skip) {
                        $date = strtotime($_GET['tbk_date']);
                        $parameters->setDay(Functions\date_i18n_tb('d', $date));
                        $parameters->setMonth(Functions\date_i18n_tb('m', $date));
                        $parameters->setYear(Functions\date_i18n_tb('Y', $date));
                        $slots = $calendar->getSlots($parameters->getRequestedServiceIds(), $parameters->getRequestedCoworkerIds(), NULL, NULL, FALSE, $parameters->getTimezone());
                        $slots_to_include = $slots->getSlotsByDate(str_pad($parameters->getDay(), 2, '0', STR_PAD_LEFT), str_pad($parameters->getMonth(), 2, '0', STR_PAD_LEFT), $parameters->getYear());
                        $parameters->setSlots($slots_to_include);
                        $parameters->setDirectScheduleCall(TRUE);
                        ?>
                        <div
                                class="<?= ($parameters->getIsWidget() === TRUE) ? 'tb-widget' : '' ?> tb-frontend-calendar tbk-slide-container"
                                data-params="<?= $parameters->encode() ?>"
                                data-instance='<?= $parameters->getInstance() ?>'>
                            <?= Dimmer::getMarkup() ?>
                            <div class="tbk-slide-canvas tbk-slide-0">
                                <div class="tbk-slide">
                                    <?php $calendar->getSchedule($parameters); ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    } else {
                        $calendar->getCalendar($parameters);
                    }
                } else {
                    $calendar->getCalendar($parameters);
                }
                ?>
            </div>
            <script>
                if (typeof tbkLoadInstance === "function") {
                    tbkLoadInstance(jQuery('#tbk-container-<?= $unique_id ?>'));
                }
            </script>
            <?php
            return ob_get_clean();
        }
    }
}