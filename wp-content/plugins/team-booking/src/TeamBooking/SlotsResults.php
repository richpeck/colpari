<?php

namespace TeamBooking;
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class SlotsResults
 *
 * @author VonStroheim
 */
class SlotsResults
{
    // This array is ordered by service id
    private $main_array = array();
    // Current sorting
    private $current_sorting = 'service';
    // Timezone
    private $timezone;

    public function __construct($timezone = NULL)
    {
        if (NULL === $timezone) {
            $this->timezone = Toolkit\getTimezone();
        } else {
            $this->timezone = $timezone;
        }
    }

    /**
     * @param Slot $slot
     */
    public function addSlot(Slot $slot)
    {
        $this->main_array[ $slot->getServiceId() ][] = $slot;
    }

    /**
     * @param Slot[] $slots
     */
    public function addSlotsFromArray(array $slots)
    {
        foreach ($slots as $slot) {
            $slot->setTimezone($this->timezone->getName());
            $this->addSlot($slot);
        }
    }

    /**
     * @param array $service_ids
     *
     * @return Slot[]
     */
    public function getSlotsByService(array $service_ids)
    {
        $results_array = array();
        $this->sortMainArrayByService();
        foreach ($service_ids as $service_id) {
            if (isset($this->main_array[ $service_id ])) {
                $results_array = array_merge($this->main_array[ $service_id ], $results_array);
            }
        }

        return $this->sort($results_array);
    }

    /**
     * @param array $coworker_ids
     *
     * @return Slot[]
     */
    public function getSlotsByCoworker(array $coworker_ids)
    {
        $results_array = array();
        $this->sortMainArrayByCoworker();
        foreach ($coworker_ids as $coworker_id) {
            if (isset($this->main_array[ $coworker_id ])) {
                $results_array = array_merge($this->main_array[ $coworker_id ], $results_array);
            }
        }

        return $this->sort($results_array);
    }

    public function sortMainArrayByCoworker()
    {
        if ($this->current_sorting === 'coworker') {
            // Let's reorder based on numbers of slots
            uasort($this->main_array, function ($a, $b) {
                return count($b) - count($a);
            });

            return;
        }
        $results_array = array();
        foreach ($this->main_array as $slots_array) {
            foreach ($slots_array as $slot) {
                /* @var $slot Slot */
                $results_array[ $slot->getCoworkerId() ][] = $slot;
            }
        }
        $this->main_array = $results_array;
        // Let's reorder based on numbers of slots
        uasort($this->main_array, function ($a, $b) {
            return count($b) - count($a);
        });
        $this->current_sorting = 'coworker';
    }

    public function sortMainArrayByService()
    {
        if ($this->current_sorting !== 'service') {
            $results_array = array();
            foreach ($this->main_array as $slots_array) {
                foreach ($slots_array as $slot) {
                    /* @var $slot Slot */
                    $results_array[ $slot->getServiceId() ][] = $slot;
                }
            }
            $this->main_array = $results_array;
            $this->current_sorting = 'service';
        }
        // Let's reorder based on numbers of slots
        uasort($this->main_array, function ($a, $b) {
            return count($b) - count($a);
        });
    }

    public function sortMainArrayByDate()
    {
        if ($this->current_sorting === 'date') {
            return;
        }
        $results_array = array();
        foreach ($this->main_array as $slots_array) {
            foreach ($slots_array as $slot) {
                /* @var $slot Slot */
                $start_time = new \DateTime($slot->getStartTime());
                if (!$slot->isAllDay()) {
                    $start_time->setTimezone($this->timezone);
                }
                $results_array[ $start_time->format('d-m-Y') ][] = $slot;
            }
        }
        $this->main_array = $results_array;
        $this->current_sorting = 'date';
    }

    /**
     * @return Slot[]
     */
    public function getAllSlots()
    {
        $this->sortMainArrayByService();
        $return = array();
        foreach ($this->main_array as $slots_array) {
            foreach ($slots_array as $slot) {
                $return[] = $slot;
            }
        }

        return $this->sort($return);
    }

    /**
     * @return Slot[]
     */
    public function getAllSlotsRawSortedByTime()
    {
        $return = array();
        foreach ($this->main_array as $slots_array) {
            foreach ($slots_array as $slot) {
                $return[] = $slot;
            }
        }

        return $this->sortSlotsByTime($return);
    }

    /**
     * @return array
     */
    public function getServiceIds()
    {
        $this->sortMainArrayByService();

        return array_keys($this->main_array);
    }

    /**
     * @return array
     */
    public function getCoworkerIds()
    {
        $this->sortMainArrayByCoworker();

        return array_keys($this->main_array);
    }

    /**
     * @return array
     */
    public function getShownCoworkerServiceIds()
    {
        $services = $this->getServiceIds();
        $return = array();
        foreach ($services as $id) {
            try {
                if (Database\Services::get($id)->getSettingsFor('show_coworker')) {
                    $return[] = $id;
                }
            } catch (\Exception $e) {
                // do nothing
            }
        }

        return $return;
    }

    /**
     * @return array
     */
    public function getShownCoworkers()
    {
        $service_ids = $this->getShownCoworkerServiceIds();
        $return = array();
        foreach ($this->getAllSlots() as $slot) {
            if (in_array($slot->getServiceId(), $service_ids)) {
                $return[ $slot->getCoworkerId() ] = 1;
            }
        }

        return array_keys($return);
    }

    /**
     * @return array
     */
    public function getShownLocationServiceIds()
    {
        $services = $this->getServiceIds();
        $return = array();
        foreach ($services as $id) {
            try {
                if (Database\Services::get($id)->getSettingsFor('location_visibility') === 'visible') {
                    $return[] = $id;
                }
            } catch (\Exception $e) {
                // do nothing
            }
        }

        return $return;
    }

    /**
     * @return array
     */
    public function getLocationsList()
    {
        $service_ids = $this->getShownLocationServiceIds();
        $return = array();
        foreach ($this->getAllSlots() as $slot) {
            if (in_array($slot->getServiceId(), $service_ids)) {
                $location = $slot->getLocation();
                if (!empty($location)) {
                    $return[ $location ] = 1;
                }
            }
        }

        return array_keys($return);
    }

    /**
     * @return array The dates are in the format DD-MM-YYYY
     */
    public function getDates()
    {
        $this->sortMainArrayByDate();

        return array_keys($this->main_array);
    }

    /**
     * @param bool|string $day   i.e. 24
     * @param bool|string $month i.e. 05
     * @param bool|string $year  i.e. 2017
     *
     * @return array
     */
    public function getSlotsByDate($day = FALSE, $month = FALSE, $year = FALSE)
    {
        $results_array = array();
        $this->sortMainArrayByDate();
        if (isset($this->main_array[ $day . '-' . $month . '-' . $year ])) {
            $results_array = array_merge($this->main_array[ $day . '-' . $month . '-' . $year ], $results_array);
        }

        return $this->sort($results_array);
    }

    /**
     * @param array $results_array
     *
     * @return Slot[]
     */
    public function sort(array $results_array)
    {
        /**
         * Let's sort the slots by the defined rule
         */
        if (Functions\getSettings()->isGroupSlotsByTime()) {
            return $this->sortSlotsByTime($results_array);
        } elseif (Functions\getSettings()->isGroupSlotsByService()) {
            return $this->sortSlotsByService($results_array);
        } elseif (Functions\getSettings()->isGroupSlotsByCoworker()) {
            return $this->sortSlotsByCoworker($results_array);
        } else {
            return $this->sortSlotsByTime($results_array); // Default sorting
        }
    }

    /**
     * Sort slots by time.
     *
     * @param array $slots
     *
     * @return Slot[]
     */
    private function sortSlotsByTime(array $slots)
    {
        usort($slots, function ($a, $b) {
            /** @var $a Slot */
            /** @var $b Slot */
            if (strtotime($a->getStartTime()) === strtotime($b->getStartTime())) {
                if ($a->getServiceName() === $b->getServiceName()) {
                    return 0;
                } else {
                    return strtolower($a->getServiceName()) > strtolower($b->getServiceName()) ? 1 : -1;
                }
            }

            return strtotime($a->getStartTime()) > strtotime($b->getStartTime()) ? 1 : -1;
        });

        return $slots;
    }

    /**
     * Sort slots by coworker.
     *
     * @param array $slots
     *
     * @return Slot[]
     */
    private function sortSlotsByCoworker(array $slots)
    {
        uasort($slots, function ($a, $b) {
            /** @var $a Slot */
            /** @var $b Slot */
            if ($a->getCoworkerId() === $b->getCoworkerId()) {
                return strtotime($a->getStartTime()) > strtotime($b->getStartTime());
            }

            return $a->getCoworkerId() > $b->getCoworkerId();
        });

        return $slots;
    }

    /**
     * Sort slots by service name (alphabetically).
     *
     * @param array $slots
     *
     * @return Slot[]
     */
    private function sortSlotsByService(array $slots)
    {
        uasort($slots, function ($a, $b) {
            /** @var $a Slot */
            /** @var $b Slot */
            if ($a->getServiceName() === $b->getServiceName()) {
                return strtotime($a->getStartTime()) > strtotime($b->getStartTime());
            }

            return $a->getServiceName() > $b->getServiceName();
        });

        return $slots;
    }

    /**
     * Get the closest slot in time
     *
     * @return Slot
     */
    public function getClosestSlot()
    {
        $results_array = array();
        foreach ($this->main_array as $slots_array) {
            $results_array = array_merge($results_array, $slots_array);
        }
        $results_array = $this->sortSlotsByTime($results_array);

        return $results_array[0];
    }

    public function thereAreSlots()
    {
        return !empty($this->main_array);
    }

    /**
     * @param $min_time
     * @param $max_time
     */
    public function trimSlots($min_time, $max_time)
    {
        foreach ($this->main_array as $key_1 => $slots_array) {
            foreach ($slots_array as $key_2 => $slot) {
                /* @var $slot Slot */
                $start_time = new \DateTime($slot->getStartTime());
                $start_time->setTimezone($this->timezone);
                if (NULL !== $min_time && NULL !== $max_time && $start_time->format('U') > strtotime($max_time)) {
                    unset($this->main_array[ $key_1 ][ $key_2 ]);
                }
            }
        }
    }

    /**
     * @param \DateTimeZone $timezone
     */
    public function setTimezone(\DateTimeZone $timezone)
    {
        $this->timezone = $timezone;
    }

}
