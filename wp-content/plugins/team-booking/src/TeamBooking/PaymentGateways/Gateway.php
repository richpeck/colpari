<?php

namespace TeamBooking\PaymentGateways;

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Interface Gateway
 *
 * This is the payment gateway interface
 *
 * @author VonStroheim
 */
interface Gateway
{
    /**
     * The main payment routine
     */
    public function processPayment();
}