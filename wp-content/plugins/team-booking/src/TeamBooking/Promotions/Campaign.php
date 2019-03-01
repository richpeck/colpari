<?php

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class TeamBooking_Promotions_Campaign
 *
 * @author VonStroheim
 */
class TeamBooking_Promotions_Campaign implements \TeamBooking\Promotions\Promotion
{
    private $name;
    private $services;
    private $discount;
    private $discount_type;
    private $start_time;
    private $end_time;
    private $start_bound;
    private $end_bound;
    private $status;
    private $limit;

    public function __construct()
    {
        $this->services = array();
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param bool $boolean
     */
    public function setStatus($boolean)
    {
        $this->status = (bool)$boolean;
    }

    /**
     * @return bool
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param array $services
     */
    public function addServices(array $services)
    {
        $this->services = $services;
    }

    /**
     * @param $service_id
     */
    public function addService($service_id)
    {
        $this->services[] = $service_id;
    }

    /**
     * @param $service_id
     */
    public function removeService($service_id)
    {
        foreach ($this->services as $id => $service) {
            if ($service_id == $service) {
                unset($this->services[ $id ]);
                break;
            }
        }

    }
    
    /**
     * @return array
     */
    public function getServices()
    {
        return (array)$this->services;
    }

    /**
     * @param $service_id
     *
     * @return bool
     */
    public function checkService($service_id)
    {
        return in_array($service_id, $this->services);
    }

    public function deleteServices()
    {
        $this->services = array();
    }

    /**
     * @param int $int
     */
    public function setDiscount($int)
    {
        $this->discount = (int)$int;
    }

    /**
     * @return mixed
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param $type
     */
    public function setDiscountType($type)
    {
        switch ($type) {
            case 'percentage':
                $this->discount_type = 'percentage';
                break;
            default:
                $this->discount_type = 'direct';
                break;
        }
    }

    /**
     * @return mixed
     */
    public function getDiscountType()
    {
        return $this->discount_type;
    }

    /**
     * @param $timestamp
     */
    public function setStartTime($timestamp)
    {
        $this->start_time = $timestamp;
    }

    /**
     * @return mixed
     */
    public function getStartTime()
    {
        return strtotime('today', $this->start_time);
    }

    /**
     * @param $timestamp
     */
    public function setEndTime($timestamp)
    {
        $this->end_time = $timestamp;
    }

    /**
     * @return mixed
     */
    public function getEndTime()
    {
        return strtotime('tomorrow', $this->end_time) - 1;
    }

    /**
     * @param $timestamp
     */
    public function setStartBound($timestamp)
    {
        $this->start_bound = $timestamp;
    }

    /**
     * @return mixed
     */
    public function getStartBound()
    {
        return $this->start_bound;
    }

    /**
     * @param $timestamp
     */
    public function setEndBound($timestamp)
    {
        $this->end_bound = $timestamp;
    }

    /**
     * @return mixed
     */
    public function getEndBound()
    {
        return $this->end_bound;
    }

    /**
     * @param $int
     */
    public function setLimit($int)
    {
        $this->limit = (int)$int;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return (int)$this->limit;
    }

    /**
     * @param $class_name
     *
     * @return bool
     */
    public function checkClass($class_name)
    {
        return $class_name === 'campaign';
    }
}