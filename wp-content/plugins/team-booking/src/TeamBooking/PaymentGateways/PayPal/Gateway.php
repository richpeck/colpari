<?php

namespace TeamBooking\PaymentGateways\PayPal;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Functions;

/**
 * Class Gateway
 *
 * @author VonStroheim
 */
class Gateway implements \TeamBooking\PaymentGateways\Gateway
{
    private $currency;
    private $id;
    private $redirect_url;
    private $items = array();

    public function __construct()
    {
    }

    /**
     * @param string $code
     */
    public function setCurrency($code)
    {
        $this->currency = $code;
    }

    /**
     * @param $id
     */
    public function setIpnId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $url
     */
    public function setRedirectUrl($url)
    {
        $this->redirect_url = $url;
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
     * @return string
     */
    public function processPayment()
    {
        // Prepare GET data
        $query = array();
        #$query['notify_url'] = 'http://XXXXXXX.ngrok.io/wp-admin/' . 'admin-ajax.php?action=teambooking_ipn_listener&paypal=1';
        $query['notify_url'] = admin_url() . 'admin-ajax.php?action=teambooking_ipn_listener&paypal=1';
        if (count($this->items) > 1) {
            $query['cmd'] = '_cart';
            $query['upload'] = '1';
            $i = 1;
            foreach ($this->items as $item) {
                $query[ 'item_name_' . $i ] = $item['name'];
                $query[ 'quantity_' . $i ] = $item['quantity'];
                $query[ 'amount_' . $i ] = $item['amount'];
                $i++;
            }
        } else {
            $query['cmd'] = '_xclick';
            $query['item_name'] = $this->items[0]['name'];
            $query['quantity'] = $this->items[0]['quantity'];
            $query['amount'] = $this->items[0]['amount'];
        }
        $query['cbt'] = 'Return to ' . get_bloginfo('name');
        $query['currency_code'] = $this->currency;
        $query['business'] = Functions\getSettings()->getPaymentGatewaySettingObject('paypal')->getAccountEmail();
        $image_url = wp_get_attachment_image_src(Functions\getSettings()->getPaymentGatewaySettingObject('paypal')->getLogoMediaId());
        if (is_array($image_url)) {
            $query['image_url'] = $image_url[0];
        }
        $query['custom'] = $this->id;
        $query['return'] = $this->redirect_url;
        $query['charset'] = 'UTF-8';
        $query['lc'] = $this->getCountryCode();

        // Prepare query string
        $query_string = http_build_query($query);

        // Return
        return $this->getPayPalUrl() . $query_string;
    }

    /**
     * @return string
     */
    private function getPayPalUrl()
    {
        if (Functions\getSettings()->getPaymentGatewaySettingObject('paypal')->isUseSandbox()) {
            return 'https://www.sandbox.paypal.com/cgi-bin/webscr?';
        } else {
            return 'https://www.paypal.com/cgi-bin/webscr?';
        }
    }

    /**
     * @return string
     */
    private function getCountryCode()
    {
        $pp_locales = array(
            'AU',
            'AT',
            'BE',
            'BR',
            'CA',
            'CH',
            'CN',
            'DE',
            'ES',
            'GB',
            'FR',
            'IT',
            'NL',
            'PL',
            'PT',
            'RU',
            'US',
            'da_DK',
            'he_IL',
            'id_ID',
            'ja_JP',
            'no_NO',
            'pt_BR',
            'ru_RU',
            'sv_SE',
            'th_TH',
            'tr_TR',
            'zh_CN',
            'zh_HK',
            'zh_TW',
        );
        $wp_locale = get_locale();
        if (in_array(get_locale(), $pp_locales)) {
            return $wp_locale;
        } else {
            $wp_cc = substr($wp_locale, -2);
            if (in_array($wp_cc, $pp_locales)) {
                return $wp_cc;
            } else {
                return 'US';
            }
        }
    }

}
