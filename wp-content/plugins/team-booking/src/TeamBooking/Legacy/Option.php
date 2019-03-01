<?php

// Blocks direct access to this file
defined('ABSPATH') or die("No script kiddies please!");

use TeamBooking\Toolkit;

/**
 * @deprecated 2.2.0 No longer used by internal code
 *
 * Class TeamBooking_Components_Form_Option
 */
class TeamBooking_Components_Form_Option
{

    private $text;
    private $price_increment;

    public function __construct($text = FALSE)
    {
        if ($text) {
            $this->setText($text);
            $this->setPriceIncrement(0.00);
        }
    }

    //------------------------------------------------------------

    public function setText($text)
    {
        $this->text = Toolkit\filterInput($text);
    }

    public function getText()
    {
        return htmlspecialchars(Toolkit\unfilterInput($this->text), ENT_QUOTES, 'UTF-8');
    }

    public function setPriceIncrement($float)
    {
        $this->price_increment = (float)$float;
    }

    public function getPriceIncrement()
    {
        return $this->price_increment;
    }

}