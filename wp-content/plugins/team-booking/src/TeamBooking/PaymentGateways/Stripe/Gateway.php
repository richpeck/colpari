<?php

namespace TeamBooking\PaymentGateways\Stripe;
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class Gateway
 *
 * @author VonStroheim
 */
class Gateway implements \TeamBooking\PaymentGateways\Gateway
{
    private $api_key;
    private $token;
    private $currency;
    private $receipt_email;
    private $items = array();
    private $order = '';

    public function __construct()
    {
    }

    /**
     * @param $key
     */
    public function setApiKey($key)
    {
        $this->api_key = $key;
    }

    /**
     * @param $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @param string $code
     */
    public function setCurrency($code)
    {
        $this->currency = $code;
    }

    /**
     * @param string $order_id
     */
    public function setOrderId($order_id)
    {
        $this->order = $order_id;
    }

    /**
     * @param string $email
     */
    public function setReceiptEmail($email)
    {
        $this->receipt_email = $email;
    }

    /**
     * @param string $name
     * @param        $unit_price
     * @param int    $quantity
     */
    public function addItem($name, $unit_price, $quantity = 1)
    {
        $this->items[] = array('name' => $name, 'amount' => $unit_price, 'quantity' => $quantity);
    }

    /**
     * @return array|\TeamBooking_Error
     */
    public function processPayment()
    {
        require_once __DIR__ . '/lib/init.php';
        \Stripe\Stripe::setApiKey($this->api_key);
        $description = '';
        if (!empty($this->order)) {
            $description = sprintf(esc_html__('Order %s', 'team-booking'), $this->order);
        } else {
            foreach ($this->items as $item) {
                $description .= $item['name'] . ', ';
            }
        }
        $parameters = array(
            'amount'      => $this->generateAmount(),
            // amount in cents, again
            'currency'    => $this->currency,
            'source'      => $this->token,
            'description' => rtrim($description, ', '),
        );
        if (NULL !== $this->receipt_email) {
            $parameters['receipt_email'] = $this->receipt_email;
        }
        try {
            $charge = \Stripe\Charge::create($parameters);
            $charge_array = $charge->__toArray();
            $return_array['charge id'] = $charge_array['id'];
            $return_array['receipt number'] = $charge_array['receipt_number'];
            $return_array['invoice id'] = $charge_array['invoice'];

            return $return_array;
        } catch (\Stripe\Error\Card $e) {
            // The card has been declined
            $body = $e->getJsonBody();
            $code = $body['error']['code'];
            $message = $body['error']['message'];
        } catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
            $body = $e->getJsonBody();
            $code = $body['error']['code'];
            $message = $body['error']['message'];
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            $body = $e->getJsonBody();
            $code = $body['error']['code'];
            $message = $body['error']['message'];
        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            $body = $e->getJsonBody();
            $code = $body['error']['code'];
            $message = $body['error']['message'];
        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
            $body = $e->getJsonBody();
            $code = $body['error']['code'];
            $message = $body['error']['message'];
        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user
            $body = $e->getJsonBody();
            $code = $body['error']['code'];
            $message = $body['error']['message'];
        } catch (\Exception $e) {
            // Something else happened, completely unrelated to Stripe
            $code = $e->getCode();
            $message = $e->getMessage();
        }

        $error = new \TeamBooking_Error(60);
        $error->setExternalCode($code);
        $error->setMessage($message);

        return $error;
    }

    /**
     * @return mixed
     */
    public function generateAmount()
    {
        $zero_decimal_currencies = array(
            'BIF' => 'Burundian Franc',
            'CLP' => 'Chilean Peso',
            'DJF' => 'Djiboutian Franc',
            'GNF' => 'Guinean Franc',
            'JPY' => 'Japanese Yen',
            'KMF' => 'Comorian Franc',
            'KRW' => 'South Korean Won',
            'MGA' => 'Malagasy Ariary',
            'PYG' => 'Paraguayan Guaraní',
            'RWF' => 'Rwandan Franc',
            'VND' => 'Vietnamese Đồng',
            'VUV' => 'Vanuatu Vatu',
            'XAF' => 'Central African Cfa Franc',
            'XOF' => 'West African Cfa Franc',
            'XPF' => 'Cfp Franc',
        );

        $amount = 0;
        if (array_key_exists($this->currency, $zero_decimal_currencies)) {
            foreach ($this->items as $item) {
                $amount += $item['amount'] * $item['quantity'];
            }
        } else {
            foreach ($this->items as $item) {
                $amount += $item['amount'] * $item['quantity'] * 100;
            }
        }

        return $amount;
    }

}
