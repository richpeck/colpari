<?php

namespace TeamBooking\Promotions;
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Interface Promotion
 *
 * @author VonStroheim
 */
interface Promotion
{
    public function setName($name);

    public function getName();

    public function setStatus($boolean);

    public function getStatus();

    public function addServices(array $services);

    public function addService($service_id);

    public function removeService($service_id);

    public function getServices();

    public function checkService($service_id);

    public function deleteServices();

    public function setDiscount($int);

    public function getDiscount();

    public function setDiscountType($type);

    public function getDiscountType();

    public function setStartTime($timestamp);

    public function getStartTime();

    public function setEndTime($timestamp);

    public function getEndTime();

    public function setStartBound($timestamp);

    public function getStartBound();

    public function setEndBound($timestamp);

    public function getEndBound();

    public function setLimit($int);

    public function getLimit();

    public function checkClass($class_name);
}