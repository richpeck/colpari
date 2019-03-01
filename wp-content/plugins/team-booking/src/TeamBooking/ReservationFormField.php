<?php

defined('ABSPATH') or die('No script kiddies please!');

/**
 * This class is more handy than the setting's form classes
 * to just transport values for reservation purposes
 *
 * @author VonStroheim
 */
class TeamBooking_ReservationFormField
{
    private $value;
    private $label;
    private $name;
    private $price_increment;
    private $service_id;

    /**
     * @param bool $filtered
     *
     * @return string
     */
    public function getValue($filtered = FALSE)
    {
        if ($filtered) {
            return \TeamBooking\Actions\reservation_form_field_value($this->value, $this);
        }

        return $this->value;
    }

    /**
     * @param $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param bool $filtered
     *
     * @return string
     */
    public function getLabel($filtered = FALSE)
    {
        if ($filtered) {
            return \TeamBooking\Actions\reservation_form_field_label($this->label, $this);
        }

        return $this->label;
    }

    /**
     * @param $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return int
     */
    public function getPriceIncrement()
    {
        if (!$this->price_increment) {
            return 0;
        } else {
            return $this->price_increment;
        }
    }

    /**
     * @param $float
     */
    public function setPriceIncrement($float)
    {
        $this->price_increment = (float)$float;
    }

    /**
     * @param string $service_id
     */
    public function setServiceId($service_id)
    {
        $this->service_id = $service_id;
    }

    /**
     * @return mixed
     */
    public function getServiceId()
    {
        return $this->service_id;
    }

}
