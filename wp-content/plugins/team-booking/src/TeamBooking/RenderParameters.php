<?php

namespace TeamBooking;
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class RenderParameters
 *
 * @author VonStroheim
 */
class RenderParameters
{
    private $month;
    private $year;
    private $day;
    private $service_ids;
    private $requested_service_ids;
    private $coworker_ids;
    private $requested_coworker_ids;
    private $is_widget;
    private $is_ajax_call;
    private $is_direct_schedule_call;
    private $no_filter;
    private $no_timezone;
    private $instance;
    /* @var $timezone \DateTimeZone */
    private $timezone;
    private $slots; // Array
    private $alt_slot_style;
    private $slots_shown;
    private $slots_limit;
    private $show_service_descriptions;
    private $show_more;
    private $hide_same_days_little_cal;

    /**
     * @return mixed
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @param $month
     */
    public function setMonth($month)
    {
        $this->month = $month; // 'm' 01–12
    }

    /**
     * @return mixed
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param $year
     */
    public function setYear($year)
    {
        $this->year = $year; // 'Y'
    }

    /**
     * @return mixed
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param $day
     */
    public function setDay($day)
    {
        $this->day = $day; // 'd' 01–31
    }

    /**
     * @return mixed
     */
    public function getServiceIds()
    {
        return $this->service_ids;
    }

    /**
     * @param array $service_ids
     */
    public function setServiceIds(array $service_ids)
    {
        $this->service_ids = $service_ids;
    }

    /**
     * @return array
     */
    public function getRequestedServiceIds()
    {
        return $this->requested_service_ids;
    }

    /**
     * @param array $service_ids
     */
    public function setRequestedServiceIds(array $service_ids)
    {
        $this->requested_service_ids = $service_ids;
    }

    /**
     * @return array
     */
    public function getRequestedCoworkerIds()
    {
        return $this->requested_coworker_ids;
    }

    /**
     * @param array $coworker_ids
     */
    public function setRequestedCoworkerIds(array $coworker_ids)
    {
        $this->requested_coworker_ids = $coworker_ids;
    }

    /**
     * @return array
     */
    public function getCoworkerIds()
    {
        return $this->coworker_ids;
    }

    /**
     * @param array $coworker_ids
     */
    public function setCoworkerIds(array $coworker_ids)
    {
        $this->coworker_ids = $coworker_ids;
    }

    /**
     * @return \DateTimeZone
     */
    public function getTimezone()
    {
        return new \DateTimeZone($this->timezone);
    }

    /**
     * @param \DateTimeZone $timezone_obj
     */
    public function setTimezone(\DateTimeZone $timezone_obj)
    {
        $this->timezone = $timezone_obj->getName();
    }

    /**
     * @param $bool
     */
    public function setIsWidget($bool)
    {
        $this->is_widget = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function getIsWidget()
    {
        return $this->is_widget;
    }

    /**
     * @param $bool
     */
    public function setIsAjaxCall($bool)
    {
        $this->is_ajax_call = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function getIsAjaxCall()
    {
        return $this->is_ajax_call;
    }

    /**
     * @param $bool
     */
    public function setDirectScheduleCall($bool)
    {
        $this->is_direct_schedule_call = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function isDirectScheduleCall()
    {
        return $this->is_direct_schedule_call;
    }

    /**
     * @param $bool
     */
    public function setNoFilter($bool)
    {
        $this->no_filter = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function getNoFilter()
    {
        return $this->no_filter;
    }

    /**
     * @param $bool
     */
    public function setNoTimezone($bool)
    {
        $this->no_timezone = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function getNoTimezone()
    {
        return $this->no_timezone;
    }

    /**
     * @param int $int
     */
    public function setAltSlotStyle($int)
    {
        $this->alt_slot_style = (int)$int;
    }

    /**
     * @return int
     */
    public function getAltSlotStyle()
    {
        if (!$this->alt_slot_style) return 0;

        return $this->alt_slot_style;
    }

    /**
     * @param int $int
     */
    public function setSlotsShown($int)
    {
        $this->slots_shown = (int)$int;
    }

    /**
     * @return int
     */
    public function getSlotsShown()
    {
        if (!$this->slots_shown) return 0;

        return $this->slots_shown;
    }

    /**
     * @param int $int
     */
    public function setSlotsLimit($int)
    {
        $this->slots_limit = (int)$int;
    }

    /**
     * @return int
     */
    public function getSlotsLimit()
    {
        if (!$this->slots_limit) return 0;

        return $this->slots_limit;
    }

    /**
     * @param $bool
     */
    public function setShowServiceDescriptions($bool)
    {
        $this->show_service_descriptions = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function getShowServiceDescriptions()
    {
        return (bool)$this->show_service_descriptions;
    }

    /**
     * @param $bool
     */
    public function setShowMore($bool)
    {
        $this->show_more = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function getShowMore()
    {
        return (bool)$this->show_more;
    }

    /**
     * @param $bool
     */
    public function setHideSameDaysLittleCal($bool)
    {
        $this->hide_same_days_little_cal = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function getHideSameDaysLittleCal()
    {
        return (bool)$this->hide_same_days_little_cal;
    }

    /**
     * @return mixed
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @param $instance
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;
    }

    /**
     * @return array
     */
    public function getSlots()
    {
        if (!empty($this->slots)) {
            return $this->slots;
        } else {
            return array();
        }
    }

    /**
     * @param $slots
     */
    public function setSlots($slots)
    {
        $this->slots = $slots;
    }

    /**
     * @param null $data
     *
     * @return string
     */
    public function encode($data = NULL)
    {
        if (NULL === $data) {
            return base64_encode(gzdeflate(serialize($this)));
        } else {
            return base64_encode(gzdeflate(serialize($data)));
        }
    }

    /**
     * @param string $encoded
     *
     * @return RenderParameters
     */
    public function decode($encoded)
    {
        return unserialize(gzinflate(base64_decode($encoded)));
    }

}
