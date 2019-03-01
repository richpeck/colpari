<?php

namespace TeamBooking\Frontend;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Cart;
use TeamBooking\Frontend\Components;
use TeamBooking\Toolkit,
    TeamBooking\Database,
    TeamBooking\Functions;

/**
 * Class Calendar
 *
 * @author VonStroheim
 */
class Calendar
{
    /** @var \TeamBooking\SlotsResults */
    public $slots_obj;
    private $params;
    private $now;

    public function __construct(\TeamBooking\RenderParameters $params)
    {
        // Day number of today
        $this->now = \DateTime::createFromFormat('U', current_time('timestamp', TRUE));
        $this->now->setTimezone($params->getTimezone());
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        // Start of week (day), 0 = Sunday
        $week_starting_day = get_option('start_of_week');
        // This should be plain English, not localized
        $week_starting_day_textual = date('D', strtotime("Sunday +{$week_starting_day} days"));
        // Week day of month's first day
        $first_month_day_position_from_week_start = date_i18n('N', mktime(0, 0, 0, $this->params->getMonth(), 1, $this->params->getYear())) - $week_starting_day;
        // If negative, add 7
        if ($first_month_day_position_from_week_start < 0) {
            $first_month_day_position_from_week_start += 7;
        }
        // Number of days in month
        $number_of_month_days = date_i18n('t', strtotime($this->params->getYear() . '-' . $this->params->getMonth() . '-01'));
        // Number of graphic calendar rows needed
        $rows = ceil(($number_of_month_days + $first_month_day_position_from_week_start) / 7);
        // Matrix index init
        $j = 0;
        $timezone_identifier = NULL === Cart::getPreference('timezone') ? $this->params->getTimezone()->getName() : Cart::getPreference('timezone');
        ob_start();
        // Let's avoid double styling and filtering renders due to ajax calls
        if (!$this->params->getIsAjaxCall()) {
            echo static::getCalendarStyle();
            ?>
            <!-- calendar list settings row -->
            <div class="tbk-main-calendar-settings tbk-noselection">

                <div class="tbk-filters">
                    <?php if (!$this->params->getNoFilter()) { ?>
                        <?php if (count($this->params->getServiceIds()) > 1) {
                            $this->getServicesFilterButton();
                        }
                        if (count($this->params->getCoworkerIds()) !== 1) {
                            $this->getCoworkersFilterButton();
                        } ?>
                    <?php } ?>
                    <?php if (Functions\getSettings()->allowCart()) {
                        echo Components\Cart::getCartButton($this->params->getIsWidget());
                    } ?>
                </div>

                <div>
                    <?php if (!$this->params->getNoTimezone() && in_array(TRUE, Functions\getSettings()->getContinentsAllowed(), TRUE)) { ?>
                        <div class="tbk-setting-button tbk-timezones" tabindex="0" style="margin: 0"
                             title="<?= esc_html__('Timezone', 'team-booking') ?>"
                             aria-label="<?= esc_html__('Timezone', 'team-booking') ?>">
                            <i class="world tb-icon"></i>
                            <?= Functions\timezone_list($timezone_identifier, $this->params->getIsWidget()) ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php
        } ?>

        <?php if (!$this->params->getIsAjaxCall()) { ?>
        <div
        class="<?= ($this->params->getIsWidget() === TRUE) ? 'tb-widget' : '' ?> tb-frontend-calendar tbk-slide-container"
        data-params="<?= $this->params->encode() ?>" data-instance='<?= $this->params->getInstance() ?>'>
        <?= Components\Dimmer::getMarkup() ?>
        <div class="tbk-slide-canvas tbk-slide-0">
        <div class="tbk-slide">
    <?php } ?>

        <div class="tbk-calendar-view-header">
            <div class="ui tbk-grid" style="margin: 0">
                <!-- calendar main navigation row -->
                <div class="tbk-row tbk-main-calendar-navigation tbk-noselection" role="navigation"
                     aria-label="<?= esc_html__('calendar controls', 'team-booking') ?>">
                    <div class="three wide tbk-column tb-change-month" tabindex="0"
                         aria-label="<?= esc_html__('previous month', 'team-booking') ?>"
                         data-month="<?= date_i18n('n', strtotime($this->params->getYear() . '-' . $this->params->getMonth() . '-01 - 1 month')) ?>"
                         data-year="<?= date_i18n('Y', strtotime($this->params->getYear() . '-' . $this->params->getMonth() . '-01 - 1 month')) ?>">
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                    </div>
                    <div class="ten wide tbk-column">
                        <?php
                        echo '<span class="tbk-calendar-month-selector" tabindex="0">';
                        if ($this->params->getIsWidget() === TRUE) {
                            echo Functions\tb_mb_strtoupper(date_i18n('M', strtotime($this->params->getYear() . '-' . $this->params->getMonth() . '-01')));
                        } else {
                            echo Functions\tb_mb_strtoupper(date_i18n('F', strtotime($this->params->getYear() . '-' . $this->params->getMonth() . '-01')));
                        }
                        echo '</span><span class="tbk-calendar-year-selector" tabindex="0">';
                        if ($this->params->getIsWidget() === TRUE) {
                            echo Functions\tb_mb_strtoupper(date_i18n('y', strtotime($this->params->getYear() . '-' . $this->params->getMonth() . '-01')));
                        } else {
                            echo Functions\tb_mb_strtoupper(date_i18n('Y', strtotime($this->params->getYear() . '-' . $this->params->getMonth() . '-01')));
                        }
                        echo '</span>';
                        ?>
                    </div>
                    <div class="three wide tbk-column tb-change-month" tabindex="0"
                         aria-label="<?= esc_html__('next month', 'team-booking') ?>"
                         data-month="<?= date_i18n('n', strtotime($this->params->getYear() . '-' . $this->params->getMonth() . '-01 + 1 month')) ?>"
                         data-year="<?= date_i18n('Y', strtotime($this->params->getYear() . '-' . $this->params->getMonth() . '-01 + 1 month')) ?>">
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </div>
                </div>
                <!-- month selector row -->
                <div class="tb-fast-selector-month-panel lifted equal width centered tbk-row">
                    <?php for ($i = 1; $i <= 12; $i++) { ?>
                        <div
                                class="four wide tbk-column aligned tbk-month-selector
                                <?= $this->now->format('n') == $i ? 'current' : '' ?>
                                <?= $this->params->getMonth() == $i ? 'selected' : '' ?>"
                                tabindex="0"
                                data-month="<?= date_i18n('m', mktime(0, 0, 0, $i, 1, date('Y'))) ?>"
                                data-instance="<?= $this->params->getInstance() ?>">
                            <?php
                            $value = date_i18n('M', mktime(0, 0, 0, $i, 1, date('Y')));
                            echo $value;
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <!-- year selector row -->
                <div class="tb-fast-selector-year-panel lifted equal width centered tbk-row">
                    <?php for ($i = 1; $i <= 4; $i++) {
                        $value = date_i18n('Y', mktime(0, 0, 0, 1, 1, date('Y') + ($i - 1)));
                        ?>
                        <div
                                class="four wide tbk-column aligned tbk-year-selector
                                <?= $this->now->format('Y') == $value ? 'current' : '' ?>
                                <?= $this->params->getYear() == $value ? 'selected' : '' ?>"
                                data-year="<?= $value ?>"
                                data-instance="<?= $this->params->getInstance() ?>">
                            <?= $value ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="tbk-calendar-view-content">
            <div class="tbk-monthly-view ui tbk-grid tbk-slide-out" style="margin: 0;">
                <div class="tb-calendar-line equal width tbk-column tbk-row">
                    <?php
                    $i = 0;
                    while ($i < 7) {
                        $date_string = date_i18n('D', strtotime("last $week_starting_day_textual + $i day"));
                        ?>
                        <div class="tbk-column">
                            <div class="tb-weekline-day">
                                <?php
                                if ($this->params->getIsWidget() === TRUE) {
                                    if (extension_loaded('mbstring')) {
                                        echo mb_substr($date_string, 0, 1);
                                    } else {
                                        echo $date_string[0];
                                    }
                                } else {
                                    echo $date_string;
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                        $i++;
                    }
                    ?>

                </div>

                <?php
                while ($j < $rows) {
                    $i = 0;
                    ?>
                    <div class="equal width tbk-column tbk-row tb-days">
                        <?php
                        while ($i < 7) {
                            // Grid index
                            $absolute_counter = $i + 1 + $j * 7;
                            // Day index
                            $relative_counter = $absolute_counter - $first_month_day_position_from_week_start;
                            $css_classes = $this->getDayCssClass($relative_counter);
                            // Get slots to include (to serve the slots list)
                            $slots_to_include = $this->slots_obj->getSlotsByDate(str_pad($relative_counter, 2, '0', STR_PAD_LEFT), str_pad($this->params->getMonth(), 2, '0', STR_PAD_LEFT), $this->params->getYear());
                            $slots_to_include = $this->params->encode($slots_to_include);
                            ?>
                            <div class="tbk-column"><?php
                                if ($relative_counter >= 1 && $relative_counter <= $number_of_month_days) {
                                    ?>
                                    <div class="ui tb-day <?= $css_classes ?>"
                                        <?= (strpos($css_classes, 'slots') !== FALSE) ? (' tabindex="0" aria-label="' . esc_html('available', 'team-booking') . '"') : '' ?>
                                         data-day="<?= $relative_counter ?>"
                                         data-slots="<?= $slots_to_include ?>">
                                        <div>
                                            <?= $relative_counter ?>
                                        </div>
                                        <?php
                                        if ($this->params->getIsWidget() != TRUE) {
                                            $this->getReservationsLeftLabel($relative_counter);
                                        }
                                        ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <?php
                            $i++;
                        }
                        ?></div>
                    <?php
                    $j++;
                }
                ?>
            </div>
        </div>
        <?php if (!$this->params->getIsAjaxCall()) { ?>
        </div>
        </div>
        </div>
    <?php } ?>
        <?php
        return ob_get_clean();
    }

    /**
     * Renders the small numbered dots label
     *
     * @param $relative_counter
     *
     * @throws \Exception
     */
    private function getReservationsLeftLabel($relative_counter)
    {
        if (Functions\getSettings()->getNumberedDotsLogic() === 'hide') {
            return;
        }
        $slots = $this->slots_obj->getSlotsByDate(str_pad($relative_counter, 2, '0', STR_PAD_LEFT), str_pad($this->params->getMonth(), 2, '0', STR_PAD_LEFT), $this->params->getYear());
        if (!empty($slots)) {
            $reservations = '';
            $sorted_slots = array();
            foreach ($slots as $slot) {
                /* @var $slot \TeamBooking\Slot */
                if ($slot->isSoldout()) {
                    continue;
                }
                try {
                    if (isset($sorted_slots[ $slot->getServiceId() ])) {
                        if (Functions\getSettings()->getNumberedDotsLogic() === 'slots'
                            || Functions\getSettings()->getNumberedDotsLogic() === 'slots_service'
                            || Database\Services::get($slot->getServiceId())->getClass() === 'appointment'
                        ) {
                            $sorted_slots[ $slot->getServiceId() ]++;
                        } elseif (Functions\getSettings()->getNumberedDotsLogic() === 'tickets'
                            || Functions\getSettings()->getNumberedDotsLogic() === 'tickets_service'
                        ) {
                            $sorted_slots[ $slot->getServiceId() ] += Database\Services::get($slot->getServiceId())->getSlotMaxTickets() - $slot->getAttendeesNumber();
                        } else {
                            $sorted_slots[ $slot->getServiceId() ]++;
                        }
                    } else {
                        if (Functions\getSettings()->getNumberedDotsLogic() === 'slots'
                            || Functions\getSettings()->getNumberedDotsLogic() === 'slots_service'
                            || Database\Services::get($slot->getServiceId())->getClass() === 'appointment'
                        ) {
                            $sorted_slots[ $slot->getServiceId() ] = 1;
                        } elseif (Functions\getSettings()->getNumberedDotsLogic() === 'tickets'
                            || Functions\getSettings()->getNumberedDotsLogic() === 'tickets_service'
                        ) {
                            $sorted_slots[ $slot->getServiceId() ] = Database\Services::get($slot->getServiceId())->getSlotMaxTickets() - $slot->getAttendeesNumber();
                        } else {
                            $sorted_slots[ $slot->getServiceId() ] = 1;
                        }
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            foreach ($sorted_slots as $service_id => $number) {
                $reservations .= $this->getReservationsLeftDot($number, $service_id);
            }
            if (!empty($reservations)) {
                ?>
                <div class="ui small pointing above label computer only tbk-column">
                    <?= $reservations ?>
                </div>
                <?php
            }
        }
    }

    /**
     * Renders a single numbered slot
     *
     * @param $number
     * @param $service_id
     *
     * @return string
     */
    private function getReservationsLeftDot($number, $service_id)
    {
        try {
            $color = Database\Services::get($service_id)->getColor();
            $name = Database\Services::get($service_id)->getName(TRUE);
        } catch (\Exception $e) {
            return NULL;
        }
        if ($number < Functions\getSettings()->getNumberedDotsLowerBound()) {
            $number = NULL;
        }
        switch (Functions\getSettings()->getNumberedDotsLogic()) {
            case 'slots_service':
                $string = $name . (NULL === $number ? '' : ' (' . $number . ')');
                break;
            case 'tickets_service':
                $string = $name . (NULL === $number ? '' : ' (' . $number . ')');
                break;
            case 'service':
                $string = $name;
                break;
            default:
                $string = $number;
                break;
        }
        ob_start();
        ?>
        <span class='ui mini tbk-circular label tb-pointing-label-dots'
              style='background-color:<?= $color ?>;color:<?= Functions\getRightTextColor($color, TRUE) ?>;'
              title='<?= $string ?>'>
            <?= $string ?>
        </span>
        <?php
        return ob_get_clean();
    }

    /**
     * @param $relative_counter
     *
     * @return string
     */
    private function getDayCssClass($relative_counter)
    {
        $slots = $this->slots_obj->getSlotsByDate(str_pad($relative_counter, 2, '0', STR_PAD_LEFT), str_pad($this->params->getMonth(), 2, '0', STR_PAD_LEFT), $this->params->getYear());
        $css_classes = '';
        if ($relative_counter == $this->now->format('j')
            && $this->params->getMonth() == $this->now->format('m')
            && $this->params->getYear() == $this->now->format('Y')
        ) {
            $css_classes = 'today';
        }
        if ((
                $this->params->getYear() > $this->now->format('Y')
            ) || (
                $this->params->getMonth() > $this->now->format('m')
                && $this->params->getYear() >= $this->now->format('Y')
            ) || ($relative_counter >= $this->now->format('j')
                && $this->params->getMonth() == $this->now->format('m')
                && $this->params->getYear() == $this->now->format('Y')
            )
        ) {
            if (!empty($slots)) {
                $free_places_detected = FALSE;
                foreach ($slots as $slot) {
                    /* @var $slot \TeamBooking\Slot */
                    if (!$slot->isSoldout()) {
                        $free_places_detected = TRUE;
                        break;
                    }
                }
                if ($free_places_detected) {
                    $css_classes .= ' slots';
                } else {
                    $css_classes .= ' slots soldout';
                }
            }
        } else {
            $css_classes .= ' pastday';
        }

        return $css_classes;
    }

    private function getServicesFilterButton()
    {
        ?>
        <div class="tbk-setting-button tbk-services" tabindex="0" title="<?= esc_html__('Services', 'team-booking') ?>"
             aria-label="<?= esc_html__('service selector', 'team-booking') ?>">
            <i class="unordered list tb-icon"></i>
            <?php if (!$this->params->getIsWidget()) { ?>
                <span class="tbk-text"><?= esc_html__('What', 'team-booking') ?></span>
            <?php } ?>
            <div class="<?= $this->params->getIsWidget() ? 'mini' : 'tiny' ?> tbk-menu">
                <?php
                if (is_array($this->params->getRequestedServiceIds())) {
                    $services = $this->params->getRequestedServiceIds();
                } else {
                    $services = $this->params->getServiceIds();
                }
                ?>
                <?php foreach ($services as $service_id) { ?>
                    <div class="tbk-menu-item" data-service="<?= $service_id ?>"
                         data-instance="<?= $this->params->getInstance() ?>">
                        <div class="ui empty tbk-circular label"
                             style="background-color:<?= Database\Services::get($service_id)->getColor() ?>"></div>
                        <?php
                        echo Database\Services::get($service_id)->getName(TRUE);
                        if (Database\Services::get($service_id)->getPrice() > 0) {
                            $price = Database\Services::get($service_id)->getPrice();
                            $discounted_price = Functions\getDiscountedPrice(Database\Services::get($service_id));
                            if ($price != $discounted_price) {
                                $price_string = Functions\currencyCodeToSymbol()
                                    . '<del>' . Functions\priceFormat($price) . '</del> <span class="tbk-discounted-price">' . Functions\priceFormat($discounted_price) . '</span>';
                            } else {
                                $price_string = Functions\currencyCodeToSymbol(Database\Services::get($service_id)->getPrice());
                            }
                            ?>
                            <span class="tbk-item-detail"><?= $price_string ?></span>
                        <?php } ?>
                    </div>
                    <?php
                }
                ?>
                <div class="tbk-divider"></div>
                <div class="tbk-menu-item tbk-reset-filter tbk-selected"
                     data-services="<?= base64_encode(serialize($services)) ?>">
                    <?= esc_html__('All', 'team-booking') ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function getCoworkersFilterButton()
    {
        $coworkers_requested = $this->params->getRequestedCoworkerIds();
        if (!empty($coworkers_requested)) {
            $coworkers = $coworkers_requested;
        } else {
            $coworkers = Functions\getAuthCoworkersIdList();
            if (count($coworkers) < 2) return;
        }
        ?>
        <div class="tbk-setting-button tbk-coworkers" tabindex="0"
             title="<?= esc_html__('Coworkers', 'team-booking') ?>"
             aria-label="<?= esc_html__('coworker selector', 'team-booking') ?>">
            <i class="user tb-icon"></i>
            <?php if (!$this->params->getIsWidget()) { ?>
                <span class="tbk-text"><?= esc_html__('Who', 'team-booking') ?></span>
            <?php } ?>
            <div class="<?= $this->params->getIsWidget() ? 'mini' : 'tiny' ?> tbk-menu">
                <?php foreach ($coworkers as $coworker) { ?>
                    <div class="tbk-menu-item" data-coworker="<?= $coworker ?>"
                         data-instance="<?= $this->params->getInstance() ?>">
                        <?= Functions\getSettings()->getCoworkerData($coworker)->getDisplayName() ?>
                    </div>
                    <?php
                }
                ?>
                <div class="tbk-menu-item tbk-reset-filter tbk-selected"
                     data-coworkers="<?= base64_encode(serialize($coworkers)) ?>">
                    <?= esc_html__('All', 'team-booking') ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * @param $content
     */
    public function getFormDirectly($content)
    {
        ?>
        <?= static::getCalendarStyle() ?>
        <div
                class="<?= ($this->params->getIsWidget() === TRUE) ? 'tb-widget' : '' ?> tb-frontend-calendar tbk-slide-container"
                data-params="<?= $this->params->encode() ?>" data-instance='<?= $this->params->getInstance() ?>'>
            <div class="tbk-calendar-view-content">
                <?= Components\Dimmer::getMarkup() ?>
                <div class="tbk-slide-canvas tbk-slide-0">
                    <div class="tbk-slide tbk-active"><?= $content ?></div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * @param $content
     */
    public function getAllUnscheduled($content)
    {
        ?>
        <?= static::getCalendarStyle() ?>
        <div class="tbk-main-calendar-settings tbk-noselection">
            <div class="tbk-filters">
                <?php if (count($this->params->getServiceIds()) > 1) {
                    $this->getServicesFilterButton();
                }
                ?>
            </div>
        </div>
        <div
                class="<?= ($this->params->getIsWidget() === TRUE) ? 'tb-widget' : '' ?> tb-frontend-calendar tbk-slide-container"
                data-params="<?= $this->params->encode() ?>" data-instance='<?= $this->params->getInstance() ?>'>
            <div class="tbk-calendar-view-content">
                <?= Components\Dimmer::getMarkup() ?>
                <div class="tbk-slide-canvas tbk-slide-0">
                    <div class="tbk-slide tbk-active"><?= $content ?></div>
                </div>
            </div>
        </div>
        <?php
    }

    public static function getCalendarStyle()
    {
        $border = Functions\getSettings()->getBorder();
        $pattern = Functions\getSettings()->getPattern();
        ob_start();
        ?>
        <style>
            .tb-frontend-calendar {
                background: <?= Functions\getSettings()->getColorBackground() ?>;
                background-image: url(<?= Toolkit\getPattern($pattern['calendar'], Functions\getSettings()->getColorBackground())?>);
                border: <?= $border['size'] .'px solid '. $border['color'] ?>;
                border-radius: <?= $border['radius'] ?>px;
            }

            .tb-frontend-calendar {
                color: <?= Functions\getRightTextColor(Functions\getSettings()->getColorBackground()) ?>;
            }

            .tb-frontend-calendar .ui.tbk-divider {
                color: <?= Functions\getRightTextColor(Functions\getSettings()->getColorBackground()) ?>;
            }

            .tb-frontend-calendar .ui.tb-day.pastday {
                color: <?= Functions\getRightHoverColor(Functions\getSettings()->getColorBackground()) ?>;
            }

            .tbk-calendar-year-selector {
                color: <?= Functions\getRightHoverColor(Functions\getSettings()->getColorBackground()) ?>;
            }

            .tbk-schedule-filter-item,
            .tbk-schedule-filter-icon {
                background: <?= Functions\getRightBackgroundColor(Functions\getSettings()->getColorBackground()) ?>;
            }

            .tbk-schedule-filter-item:hover,
            .tbk-schedule-filter-item:focus,
            .tbk-schedule-filter-icon:hover,
            .tbk-schedule-filter-icon:focus {
                background: <?= Functions\getRightHoverColor(Functions\getSettings()->getColorBackground()) ?>;
                outline: 0;
            }

            .tb-frontend-calendar .ui.tb-day.today,
            .tb-change-month:hover,
            .tb-change-month:focus,
            .tbk-back-to:hover,
            .tbk-back-to:focus,
            .tbk-column .tb-day:not(.pastday):hover,
            .tbk-column .tb-day:not(.pastday):focus,
            div.tb-fast-selector-month-panel .tbk-month-selector:hover,
            div.tb-fast-selector-month-panel .tbk-month-selector:focus,
            div.tb-fast-selector-year-panel .tbk-year-selector:hover,
            div.tb-fast-selector-year-panel .tbk-year-selector:focus {
                background-color: <?= Functions\getRightHoverColor(Functions\getSettings()->getColorBackground()) ?>;
                outline: 0;
            }

            .tb-frontend-calendar .ui.tb-day.slots {
                background-color: <?= Functions\getSettings()->getColorFreeSlot() ?>;
                color: <?= Functions\getRightTextColor(Functions\getSettings()->getColorFreeSlot()) ?>;
            }

            .tb-frontend-calendar .ui.tb-day.slots.soldout {
                background-color: <?= Functions\getSettings()->getColorSoldoutSlot() ?>;
                color: <?= Functions\getRightTextColor(Functions\getSettings()->getColorSoldoutSlot()) ?>;
            }

            .tb-frontend-calendar .ui.tb-day.slots:hover,
            .tb-frontend-calendar .ui.tb-day.slots:focus {
                background: <?= Functions\getSettings()->getColorFreeSlot() ?>;
                background: linear-gradient(to bottom, rgba(0, 0, 0, 0.15) 0%, rgba(0, 0, 0, 0.15) 100%),
                linear-gradient(to bottom,  <?= Functions\getSettings()->getColorFreeSlot() ?> 0%,<?= Functions\getSettings()->getColorFreeSlot() ?> 100%); /* W3C */
                outline: 0;
            }

            .tb-frontend-calendar .ui.tb-day.slots.soldout:hover,
            .tb-frontend-calendar .ui.tb-day.slots.soldout:focus {
                background: <?= Functions\getSettings()->getColorSoldoutSlot() ?>;
                background: linear-gradient(to bottom, rgba(0, 0, 0, 0.15) 0%, rgba(0, 0, 0, 0.15) 100%),
                linear-gradient(to bottom,  <?= Functions\getSettings()->getColorSoldoutSlot() ?> 0%,<?= Functions\getSettings()->getColorSoldoutSlot() ?> 100%); /* W3C */
                outline: 0;
            }

            .tb-calendar-line.equal.width.tbk-column.tbk-row {
                background: <?= Functions\getSettings()->getColorWeekLine() ?>;
                background-image: url(<?= Toolkit\getPattern($pattern['weekline'], Functions\getSettings()->getColorWeekLine())?>);
                color: <?= Functions\getRightTextColor(Functions\getSettings()->getColorWeekLine()) ?>;
            }
        </style>

        <?php
        return ob_get_clean();
    }

}
