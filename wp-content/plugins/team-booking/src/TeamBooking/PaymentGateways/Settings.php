<?php

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Interface TeamBooking_PaymentGateways_Settings
 *
 * This is the payment gateway settings interface
 *
 * @author VonStroheim
 */
interface TeamBooking_PaymentGateways_Settings
{

    /**
     * It must return the gateway unique id (i.e."paypal")
     */
    public function getGatewayId();

    /**
     * It must return TRUE if the gateway is active
     */
    public function isActive();

    /**
     * It must return TRUE if the gateway is offsite (i.e. PayPal standard)
     */
    public function isOffsite();

    /**
     * It must return the gateway specific settings tab for the backend
     */
    public function getBackendSettingsTab();

    /**
     * It must return the frontend gateway pay button
     */
    public function getPayButton();

    /**
     * It must return the gateway small label used in reservations list
     */
    public function getLabel();

    /**
     * It prepares the gateway to payment.
     *
     * Based on the particular gateway (onsite/offsite), it can
     * process the payment or redirect the customer.
     *
     * It can accept an additional parameter (array or whatewer)
     * to pass to the gateway (i.e. token)
     *
     * @param array $items
     * @param mixed $additional_parameter
     */
    public function prepareGateway(array $items, $additional_parameter = NULL);

    /**
     * It must return the data collecting form specific to the gateway.
     *
     * This is usually presented to the customer after he chose
     * to pay with this gateway, and this gateway is onsite type.
     *
     * Offsite gateways must return FALSE
     *
     * @param array  $items
     * @param string $order_redirect
     */
    public function getDataForm($items, $order_redirect);

    /**
     * This method is called from the backend to save
     * the gateway's specific settings.
     *
     * Important: the general currency code setting must
     * be passed too, in order to check the gateway compatibility
     * and make a decision for activation/deactivation accordingly.
     *
     * @param array  $data_array the specific settings data
     * @param string $new_currency_code
     */
    public function saveBackendSettings(array $data_array, $new_currency_code);

    /**
     * It checks the compatibility of a general currency code
     * with the gateway.
     *
     * It returns TRUE if the code is compatible,
     * FALSE otherwise.
     *
     * @param string $code
     */
    public function verifyCurrency($code);

    /**
     * The listener for IPN callbacks.
     *
     * Offsite gateways should simply return;
     *
     * @param array $post_data
     */
    public function listenerIPN($post_data);

    /**
     * Exports the object parameters as JSON
     *
     * @return string
     */
    public function get_json();

    /**
     * Imports the object parameters from JSON
     *
     * @param $json
     */
    public function inject_json($json);
}
