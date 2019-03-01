<?php

namespace TeamBooking\Frontend;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Cart,
    TeamBooking\Functions,
    TeamBooking\Actions,
    TeamBooking\Toolkit,
    TeamBooking\Slot,
    TeamBooking\Database,
    TeamBooking\Frontend\Components;

/**
 * Class Schedule
 *
 * @author VonStroheim
 */
class Schedule
{
    /** @var  $slots Slot[] */
    private $slots;
    private $time_format;
    private $date_format;
    private $read_only;
    private $slots_obj;
    private $modal_id;
    private $params;
    private $timezone;

    /**
     * Schedule constructor.
     *
     * @param \TeamBooking\RenderParameters $parameters
     */
    public function __construct(\TeamBooking\RenderParameters $parameters)
    {
        $this->time_format = get_option('time_format');
        $this->date_format = get_option('date_format');
        $this->params = $parameters;
        $this->timezone = $parameters->getTimezone();
        // Check if the instance is read_only
        $this->read_only = (strlen($parameters->getInstance()) !== 8);
        $this->slots_obj = new \TeamBooking\SlotsResults($this->timezone);
        $this->modal_id = 'tb-modal-list-' . $this->params->getInstance();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getContent()
    {
        ob_start();
        ?>
        <!-- schedule list navigation row -->
        <?= Components\NavigationHeader::InSlotsList($this->params, $this->slots_obj) ?>
        <!-- schedule list -->
        <div class="tbk-schedule-list">
            <div class="tbk-schedule-slots">
                <?php
                if (!empty($this->slots)) {
                    ?>
                    <div class="tbk-esortation">
                        <div class="tbk-schedule-filters">
                            <?php $this->getTimeFilter(); ?>
                            <?php $this->getLocationFilter(); ?>
                            <?php $this->getCoworkerFilter(); ?>
                        </div>
                        <?php if (!$this->read_only && !Functions\getSettings()->allowCart()) { ?>
                            <div><?= esc_html__('Choose a timeslot', 'team-booking') ?></div>
                        <?php } ?>
                    </div>
                    <?php
                    if (Functions\getSettings()->isGroupSlotsByTime()) {
                        echo $this->sortSlots();
                    } else {
                        // Grouping
                        if (Functions\getSettings()->isGroupSlotsByService()) {
                            $services_sorting = $this->slots_obj->getServiceIds();
                            $services_order = $this->params->getServiceIds();
                            usort($services_sorting, function ($a, $b) use ($services_order) {
                                return (array_search($a, $services_order) >= array_search($b, $services_order));
                            });
                            foreach ($services_sorting as $service_id) {
                                $this->slots = $this->slots_obj->getSlotsByService(array($service_id));
                                ?>
                                <div class="ui horizontal tbk-divider"
                                     style="font-weight: 300;text-transform: capitalize;overflow: visible;">
                                            <span class="ui mini tbk-circular label tb-pointing-label-dots"
                                                  style="background-color:<?= Database\Services::get($service_id)->getColor() ?>"></span>
                                    <?= esc_html(Database\Services::get($service_id)->getName(TRUE)) ?>
                                </div>
                                <div class="tbk-slots-wrapper">
                                    <?php
                                    echo $this->sortSlots();
                                    ?>
                                </div>
                                <?php
                            }
                        } elseif (Functions\getSettings()->isGroupSlotsByCoworker()) {
                            foreach ($this->slots_obj->getCoworkerIds() as $coworker_id) {
                                $this->slots = $this->slots_obj->getSlotsByCoworker(array($coworker_id));
                                ?>
                                <div class="ui horizontal tbk-divider"
                                     style="font-weight: 300;text-transform: capitalize;overflow: visible;">
                                    <i class='user tb-icon'></i> <?= esc_html(Functions\getSettings()->getCoworkerData($coworker_id)->getDisplayName()) ?>
                                </div>
                                <div class="tbk-slots-wrapper">
                                    <?php
                                    echo $this->sortSlots();
                                    ?>
                                </div>
                                <?php
                            }
                        }
                    }
                }
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @param Slot                           $slot
     * @param \TeamBooking\Abstracts\Service $service
     *
     * @return bool|string
     */
    public static function getTicketsLeft(Slot $slot, \TeamBooking\Abstracts\Service $service)
    {
        if ($service->getSettingsFor('show_tickets_left') && !$slot->isSoldout()) {
            /** @var $service \TeamBooking\Services\Event */
            $tickets_left = $slot->getTicketsLeft();
            if ($tickets_left <= 0) {
                return FALSE;
            }
            $threeshold = $service->getSettingsFor('show_tickets_left_threeshold');
            if ($tickets_left >= $threeshold && $threeshold > 0) {
                return FALSE;
            }
            $percentage = 20;
            $label_color = (($tickets_left / $slot->getMaxTickets()) * 100 <= $percentage || $tickets_left === 1) ? 'orange' : 'green';
            ob_start();
            ?>
            <div class="tbk-slot-label <?= $label_color ?>">
                <?= $tickets_left ?>
                <div class="detail"><?= esc_html__('left', 'team-booking') ?></div>
            </div>
            <?php
            return ob_get_clean();
        }

        if (!$slot->isSoldout()) {
            ob_start();
            ?>
            <div class="tbk-slot-label green">
                <?= esc_html__('available', 'team-booking') ?>
            </div>
            <?php
            return ob_get_clean();
        }

        if ($service->getClass() === 'appointment') {
            $string_sold_out = __('booked', 'team-booking');
        } else {
            $string_sold_out = __('sold out', 'team-booking');
        }
        ob_start();
        ?>
        <div class="tbk-slot-label red">
            <?= esc_html($string_sold_out) ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @param Slot $slot
     *
     * @return string
     */
    public static function getPriceTag(Slot $slot)
    {
        if ($slot->getPriceBase() != $slot->getPriceDiscounted()) {
            $price_string = Functions\currencyCodeToSymbol()
                . '<del>' . Functions\priceFormat($slot->getPriceBase()) . '</del><span class="tbk-discounted-price"> ' . Functions\priceFormat($slot->getPriceDiscounted()) . '</span>';
        } else {
            $price_string = Functions\currencyCodeToSymbol($slot->getPriceBase());
        }
        ob_start();
        if ($slot->getPriceBase() > 0) {
            ?>
            <div class="tbk-slot-label-price tbk-slot-label <?= Functions\getSettings()->getPriceTagColor() ?>">
                <?= $price_string ?>
            </div>
            <?php
        }

        return ob_get_clean();
    }

    /**
     * @param Slot $slot
     * @param bool $return_object
     *
     * @return \DateTime|string
     */
    private function getStartTimeOnly(Slot $slot, $return_object = FALSE)
    {
        $start_date_time_object = new \DateTime($slot->getStartTime());
        $start_date_time_object->setTimezone($this->timezone);
        if (!$return_object) {
            return $start_date_time_object->format($this->time_format);
        } else {
            return $start_date_time_object;
        }
    }

    /**
     * @return string
     */
    private function sortSlots()
    {
        ob_start();
        foreach ($this->slots as $slot) {
            try {
                $service = Database\Services::get($slot->getServiceId());
            } catch (\Exception $e) {
                continue;
            }

            $basic_classes = 'tbk-schedule-slot';
            if (Functions\getSettings()->allowCart() && Cart::isSlotIn($slot)) {
                $basic_classes .= ' tbk-in-cart';
            }
            $attributes_to_add = '';
            if ($this->params->getAltSlotStyle() !== 0) {
                $basic_classes .= ' tbk-alt-' . $this->params->getAltSlotStyle();
            }
            $location = $slot->getLocation();
            if ($service->getSettingsFor('location_visibility') === 'visible') {
                $attributes_to_add = ' data-address="' . $location . '" ';
            }

            if ($service->getSettingsFor('bookable') === 'logged_only' && !is_user_logged_in() && !$slot->isSoldout()) {
                $attributes_to_add .= 'class="' . $basic_classes . ' '
                    . 'tb-book-advice' . '" '
                    . 'data-event="' . $slot->getEventId() . '" '
                    . 'data-service="' . $slot->getServiceId() . '" ';
            } else {
                if ($slot->isSoldout()) {
                    $attributes_to_add .= 'class="' . $basic_classes . '"';
                } else {
                    if (!$this->read_only && !Functions\getSettings()->allowCart()) {
                        $basic_classes .= ' tb-book';
                    } elseif ($this->read_only) {
                        $basic_classes .= ' tbk-read-only';
                    }
                    $attributes_to_add .= 'class="link ' . $basic_classes . '" ';
                    if (!$this->read_only) {
                        $attributes_to_add .= 'data-slot="' . Toolkit\objEncode($slot, TRUE, $slot->getUniqueId()) . '" ';
                        $attributes_to_add .= 'data-slot-id="' . Toolkit\objEncode($slot->getUniqueId()) . '" ';
                    }
                }
            }
            $start_end_value = $slot->getTimesString();
            $attributes_to_add .= 'data-timeint="' . (NULL !== $start_end_value ? $this->getStartTimeOnly($slot, TRUE)->format('G') : '24') . '" ';
            if ($service->getSettingsFor('show_coworker')) {
                $attributes_to_add .= 'data-coworker="' . $slot->getCoworkerId() . '" ';
            }
            if ($this->params->getAltSlotStyle() === 1) {
                $attributes_to_add .= ' style="border-left-color: '
                    . Toolkit\hex2RGBa($service->getColor(), 0.5)
                    . '"';
            } else {
                $attributes_to_add .= ' style="border: 2px solid '
                    . Toolkit\hex2RGBa($service->getColor(), 0.5)
                    . ';background: ' . Toolkit\hex2RGBa($service->getColor(), 0.2) . '"';
            }
            if (!$slot->isSoldout()) {
                $attributes_to_add .= ' tabindex="0"';
            }
            ?>
            <div <?= $attributes_to_add ?>>
                <div class="tbk-slot-container">
                    <!-- main content -->
                    <div class="tbk-header-row">
                        <div class="tbk-cell">
                            <i class="wait tb-icon"></i>
                        </div>
                        <div class="tbk-cell">
                            <?php
                            if (Functions\getSettings()->isGroupSlotsByTime()) {
                                echo $start_end_value;
                            } else {
                                echo $start_end_value;
                            }
                            ?>
                        </div>
                        <div class="tbk-cell" style="text-align: right">
                            <?= static::getTicketsLeft($slot, $service); ?>
                        </div>
                    </div>

                    <div class="tbk-meta" style="text-transform:none;">
                        <div class="tbk-cell">
                        </div>
                        <div class="tbk-cell">
                            <?php if ($service->getSettingsFor('show_service_name')) { ?>
                                <span class="tb-service-info"><?= esc_html($slot->getServiceName(TRUE)) ?></span>
                            <?php } ?>
                        </div>
                        <div class="tbk-cell" style="text-align: right">
                            <?= static::getPriceTag($slot) ?>
                        </div>
                    </div>

                    <?php if ($service->getSettingsFor('show_coworker')) { ?>
                        <div class="description">
                            <div class="tbk-cell">
                                <i class="user tb-icon"></i>
                            </div>
                            <div class="tbk-cell">
                                <?= $slot->getCoworkerDisplayString() ?>
                            </div>
                            <div class="tbk-cell" style="text-align: right">
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ($service->getSettingsFor('show_attendees') !== 'no' && $slot->getAttendeesNumber() > 0) { ?>
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
                    <?php } ?>

                    <?php if (!empty($location) && $service->getSettingsFor('location_visibility') === 'visible') { ?>
                        <div class="description">
                            <div class="tbk-cell">
                                <i class="marker tb-icon"></i>
                            </div>
                            <div class="tbk-cell">
                                <?= esc_html($slot->getLocation()) ?>
                            </div>
                            <div class="tbk-cell">
                            </div>
                        </div>
                    <?php } ?>
                    <?php if (!$slot->canBeEdited() && Functions\isAdminOrCoworker()) { ?>
                        <div class="description">
                            <div class="tbk-cell">
                                <i class="warning sign tb-icon"></i>
                            </div>
                            <div class="tbk-cell">
                                <?= esc_html__('This slot is read-only because it was not created by the owner of the calendar where it resides.', 'team-booking') ?>
                                <?= esc_html__('Only admins and coworkers can see this warning.', 'team-booking') ?>
                            </div>
                            <div class="tbk-cell">
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php
                // Custom WordPress hook
                Actions\schedule_slot_render($slot);
                if (!$this->read_only && !$slot->isSoldout() && Functions\getSettings()->allowCart()) {
                    if ($service->getSettingsFor('bookable') === 'logged_only' && !is_user_logged_in()) {
                        echo Components\Slot::actionButtonsDimmer(Toolkit\hex2RGBa($service->getColor(), 0.5));
                    } else {
                        if ($slot->isReadOnly()) {
                            echo Components\Slot::actionButtonsReadOnly(Toolkit\hex2RGBa($service->getColor(), 0.5));
                        } else {
                            echo Components\Slot::actionButtons(Toolkit\hex2RGBa($service->getColor(), 0.5));
                        }
                    }
                } ?>
            </div>
            <?php
        }

        return ob_get_clean();
    }

    /**
     * @param $slots
     */
    public function setSlots($slots)
    {
        // Populating the TeamBooking_SlotsResults object for easy grouping
        $this->slots_obj->addSlotsFromArray($slots);
        $this->slots_obj->sortMainArrayByDate();
        $this->slots = $this->slots_obj->getAllSlots();
    }

    /**
     * @param int    $lower_bound
     * @param int    $upper_bound
     * @param string $increment
     *
     * @return array
     */
    private function getFilterTimesArray($lower_bound = 1, $upper_bound = 24, $increment = 'hour')
    {
        $start = date_create('midnight');
        //$return = array($start->format($this->time_format));
        $return = array();
        for ($i = $lower_bound; $i <= $upper_bound; $i++) {
            $to_add = clone $start;
            date_add($to_add, date_interval_create_from_date_string($i . ' ' . $increment));
            $return[ $i ] = $to_add->format($this->time_format);
        }

        return $return;
    }

    private function getCoworkerFilter()
    {
        $coworkers_list = $this->slots_obj->getShownCoworkers();
        if (count($coworkers_list) < 2 || Functions\getSettings()->isGroupSlotsByCoworker()) return;
        ?>
        <div class="tbk-coworker-filter-panel lifted">
            <div class="tbk-schedule-coworker-select">
                <?php
                foreach ($coworkers_list as $coworker_id) {
                    $coworker_data = Functions\getSettings()->getCoworkerData($coworker_id);
                    ?>
                    <div class="tbk-schedule-filter-item" data-value="<?= esc_attr($coworker_id) ?>" tabindex="0">
                        <?= esc_html($coworker_data->getDisplayName()) ?>
                    </div>
                <?php } ?>
                <div class="tbk-schedule-filter-item tbk-selected" data-value="all" tabindex="0">
                    <?= esc_html__('All', 'team-booking') ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function getLocationFilter()
    {
        $locations_list = $this->slots_obj->getLocationsList();
        if (count($locations_list) < 2) return;
        ?>
        <div class="tbk-location-filter-panel lifted">
            <div class="tbk-schedule-location-select">
                <?php
                foreach ($locations_list as $location) {
                    ?>
                    <div class="tbk-schedule-filter-item" data-value="<?= esc_attr($location) ?>" tabindex="0">
                        <?= esc_html(ucwords($location)) ?>
                    </div>
                <?php } ?>
                <div class="tbk-schedule-filter-item tbk-selected" data-value="all" tabindex="0">
                    <?= esc_html__('All', 'team-booking') ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function getTimeFilter()
    {
        $start_times = array();
        foreach ($this->slots_obj->getAllSlots() as $slot) {
            if (!$slot->isSoldout()) {
                $start_times[] = $this->getStartTimeOnly($slot, TRUE);
            }
        }
        if (empty($start_times)) {
            return; // All seem booked
        }
        $lower_bound = (int)min($start_times)->format('G');
        $upper_bound = (int)max($start_times)->format('G');
        unset($start_times);
        ?>
        <div class="tbk-time-filter-panel lifted">
            <div class="tbk-schedule-time-select">
                <?= esc_html__('From:', 'team-booking') ?>
                <?php
                foreach ($this->getFilterTimesArray($lower_bound, $upper_bound) as $timeint => $time) {
                    ?>
                    <div class="tbk-schedule-filter-item" data-value="<?= $timeint ?>" tabindex="0">
                        <?= $time ?>
                    </div>
                <?php } ?>
                <div class="tbk-schedule-filter-item tbk-selected" data-value="all" tabindex="0">
                    <?= esc_html__('All', 'team-booking') ?>
                </div>
            </div>
        </div>
        <?php
    }

}
