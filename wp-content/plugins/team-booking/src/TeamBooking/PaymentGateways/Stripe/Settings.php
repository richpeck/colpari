<?php
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Functions,
    TeamBooking\Admin\Framework,
    TeamBooking\PaymentGateways;

/**
 * Class TeamBooking_PaymentGateways_Stripe_Settings
 *
 * @author VonStroheim
 */
class TeamBooking_PaymentGateways_Stripe_Settings implements TeamBooking_PaymentGateways_Settings
{
    private $use_gateway;
    private $gateway_id;
    private $secret_key;
    private $pub_key;
    private $send_receipt;
    private $load_library;

    public function __construct()
    {
        $this->gateway_id = 'stripe';
        $this->use_gateway = FALSE;
        $this->send_receipt = FALSE;
        $this->load_library = TRUE;
    }

    /**
     * @return bool
     */
    public function isOffsite()
    {
        return FALSE;
    }

    /**
     * @param $bool
     */
    public function setUseGateway($bool)
    {
        $this->use_gateway = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        if (extension_loaded('curl')) {
            return $this->use_gateway;
        } else {
            return FALSE;
        }
    }

    /**
     * @return string
     */
    public function getGatewayId()
    {
        return $this->gateway_id;
    }

    /**
     * @param $key
     */
    public function setSecretKey($key)
    {
        $this->secret_key = $key;
    }

    /**
     * @return mixed
     */
    public function getSecretKey()
    {
        return $this->secret_key;
    }

    /**
     * @param $key
     */
    public function setPublishableKey($key)
    {
        $this->pub_key = $key;
    }

    /**
     * @return mixed
     */
    public function getPublishableKey()
    {
        return $this->pub_key;
    }

    /**
     * @param $bool
     */
    public function setSendReceipt($bool)
    {
        $this->send_receipt = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function getSendReceipt()
    {
        return $this->send_receipt;
    }

    /**
     * @param $bool
     */
    public function setLoadLibrary($bool)
    {
        $this->load_library = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function isLoadLibrary()
    {
        return $this->load_library;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return __('via Stripe', 'team-booking');
    }

    /**
     * @return string
     */
    public function getPayButton()
    {
        ob_start();
        ?>
        <div class="tb-icon tbk-button tbk-red tbk-pay-button" data-offsite="<?= $this->isOffsite() ?>"
             data-gateway="<?= $this->gateway_id ?>" tabindex="0">
            <i class="stripe tb-icon"></i>

            <div class="tbk-content">
                <?= esc_html__('Pay with credit card', 'team-booking') ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @param TeamBooking_ReservationData[] $items
     * @param null                          $additional_parameter
     *
     * @return array|TeamBooking_Error
     */
    public function prepareGateway(array $items, $additional_parameter = NULL)
    {
        $stripe = new PaymentGateways\Stripe\Gateway();
        $stripe->setApiKey($this->secret_key);
        $stripe->setToken($additional_parameter);
        foreach ($items as $item) {
            $stripe->addItem($item->getServiceName() . ' | #' . $item->getDatabaseId(TRUE), $item->getPriceIncremented(), $item->getTickets());
        }
        $lead = reset($items);
        $stripe->setCurrency($lead->getCurrencyCode());
        if ($this->send_receipt) {
            $stripe->setReceiptEmail($lead->getCustomerEmail());
        }
        if (NULL !== $lead->getOrderId()) {
            $stripe->setOrderId($lead->getOrderId());
        }

        return $stripe->processPayment();
    }

    /**
     * @param array  $settings
     * @param string $new_currency_code
     */
    public function saveBackendSettings(array $settings, $new_currency_code)
    {
        if (isset($settings['use_gateway'])) {
            if (!empty($settings['secret_key'])
                && !empty($settings['pub_key'])
                && $this->verifyCurrency($new_currency_code)
            ) {
                $this->setUseGateway($settings['use_gateway']);
            } else {
                $this->setUseGateway(FALSE);
            }
        }
        isset($settings['secret_key']) ? $this->setSecretKey(trim($settings['secret_key'])) : $this->setSecretKey('');
        isset($settings['pub_key']) ? $this->setPublishableKey(trim($settings['pub_key'])) : $this->setPublishableKey('');
        if (isset($settings['send_receipt'])) $this->setSendReceipt($settings['send_receipt']);
        if (isset($settings['load_library'])) $this->setLoadLibrary($settings['load_library']);
    }

    /**
     * @return Framework\PanelWithForm
     */
    public function getBackendSettingsTab()
    {
        $panel = new Framework\PanelWithForm(NULL);
        $panel->setAction('tbk_save_payments');
        $panel->setNonce('team_booking_options_verify');
        $panel->addTitleContent(Framework\ElementFrom::content(Framework\Html::anchor(array(
            'text'   => Framework\Html::img(array('src' => 'https://stripe.com/img/logo.png', 'alt' => __('Stripe', 'team-booking'))),
            'escape' => FALSE,
            'target' => '_blank',
            'href'   => 'https://stripe.com'
        ))));

        // Use Stripe gateway
        $element = new Framework\PanelSettingYesOrNo(__('Use Stripe gateway', 'team-booking'));
        if (empty($this->pub_key) || empty($this->secret_key)) {
            $element->addDescription(__("You can't activate the Stripe gateway if the Secret Key and Publishable Key fields are empty.", 'team-booking'));
            $element->setDisabled(TRUE);
        }
        if (!extension_loaded('curl')) {
            $element->addNotice(__('Your server does not have cURL extension active.', 'team-booking'));
            $element->setDisabled(TRUE);
        }
        if (!$this->verifyCurrency(Functions\getSettings()->getCurrencyCode())) {
            $element->addAlert(__("The selected currency is not supported by Stripe. Stripe gateway can't be activated.", 'team-booking'));
        }
        $element->addFieldname('gateway_settings[' . $this->gateway_id . '][use_gateway]');
        $element->setState($this->isActive());
        $element->appendTo($panel);

        // Secret key
        $element = new Framework\PanelSettingText(__('Secret Key', 'team-booking'));
        $element->addFieldname('gateway_settings[' . $this->gateway_id . '][secret_key]');
        $element->addDescription(Framework\Html::anchor(array(
            'href'   => 'https://dashboard.stripe.com/account/apikeys',
            'text'   => __('where?', 'team-booking'),
            'target' => '_blank'
        )), FALSE);
        $element->addDefaultValue($this->secret_key);
        $element->addNotice(__('use the test one for testing, the live one for real payments', 'team-booking'));
        $element->appendTo($panel);

        // Publishable key
        $element = new Framework\PanelSettingText(__('Publishable Key', 'team-booking'));
        $element->addFieldname('gateway_settings[' . $this->gateway_id . '][pub_key]');
        $element->addDescription(Framework\Html::anchor(array(
            'href'   => 'https://dashboard.stripe.com/account/apikeys',
            'text'   => __('where?', 'team-booking'),
            'target' => '_blank'
        )), FALSE);
        $element->addDefaultValue($this->pub_key);
        $element->addNotice(__('use the test one for testing, the live one for real payments', 'team-booking'));
        $element->appendTo($panel);

        // Send receipt
        $element = new Framework\PanelSettingYesOrNo(__('Send receipt to the customer', 'team-booking'));
        $element->addDescription(__('The customer email provided in the reservation form will be used', 'team-booking'));
        $element->addFieldname('gateway_settings[' . $this->gateway_id . '][send_receipt]');
        $element->setState($this->send_receipt);
        $element->appendTo($panel);

        // Load Stripe.js library
        $element = new Framework\PanelSettingYesOrNo(__('Load Stripe.js library', 'team-booking'));
        $element->addDescription(__('Remove this option only if another plugin is loading the same library', 'team-booking'));
        $element->addFieldname('gateway_settings[' . $this->gateway_id . '][load_library]');
        $element->setState($this->load_library);
        $element->appendTo($panel);

        // Save changes
        $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'tbk_save_payments');
        $element->appendTo($panel);

        return $panel;
    }

    /**
     * @param TeamBooking_ReservationData[] $items
     * @param string                        $order_redirect
     *
     * @return string
     */
    public function getDataForm($items, $order_redirect = '')
    {
        ob_start();
        $form_id = 'tb-stripe-payment-form-' . mt_rand(100000, 999999);
        $lead = reset($items);
        $order = new \TeamBooking\Order();
        $order->setItems($items);
        $order->setId($lead->getOrderId());
        $url = 'https://stripe.com';
        $amount = NULL === $order->getId() ? $lead->getPriceIncremented() * $lead->getTickets() : $order->get_to_be_paid_amount();
        ?>
        <?= \TeamBooking\Frontend\Components\NavigationHeader::InPaymentForm(Functions\priceFormat($amount)) ?>
        <div class="tbk-payment-form-collect">
            <form action="" method="POST" id="<?= $form_id ?>">
                <div class="tbk-field">
                    <label><?= esc_html__('Credit Card number', 'team-booking') ?></label>
                    <input class="tbk-cc-num" type="tel" data-stripe="number" autocomplete="cc-number"
                           placeholder="••••  ••••  ••••  ••••"/>
                </div>
                <div class="two tbk-fields">
                    <div class="tbk-field">
                        <label><?= esc_html__('Expiring', 'team-booking') ?></label>
                        <input class="tbk-cc-exp" data-stripe="exp" autocomplete="cc-exp" placeholder="MM / YY"/>
                    </div>
                    <div class="tbk-field">
                        <label><?= esc_html__('CVC', 'team-booking') ?></label>
                        <input class="tbk-cc-cvc" data-stripe="cvc" autocomplete="off"/>
                    </div>
                </div>
                <div style="white-space: normal;font-size: 12px;margin: 0 0 2em;">
                    <?= sprintf(wp_kses(__('Powered by <a href="%s" target="_blank">Stripe</a>.', 'team-booking'), array('a' => array('href' => array(), 'target' => array('_blank')))), esc_url($url)) ?>
                    <?= esc_html__('Stripe JavaScript library is used for securely sending your payment information to Stripe directly from your browser.', 'team-booking') ?>
                    <?= esc_html__('No sensitive payment data is transmitted to nor collected by this website.', 'team-booking') ?>
                </div>
                <div class="tbk-submit tbk-blue tbk-button" style="width: 100%;" tabindex="0">
                    <?= esc_html__('Submit payment', 'team-booking') ?>
                </div>
                <!-- Error section -->
                <div class='tbk-error-message-form'>
                    <div class="tbk-message-header">
                        <?= esc_html__("You've got some errors...", 'team-booking') ?>
                    </div>
                    <p class="payment-errors"></p>
                </div>
            </form>
        </div>
        <script>
            var $tbk_form;
            jQuery('input.tbk-cc-num').payment('formatCardNumber');
            jQuery('input.tbk-cc-exp').payment('formatCardExpiry');
            jQuery('input.tbk-cc-cvc').payment('formatCardCVC');
            Stripe.setPublishableKey('<?= $this->pub_key ?>');
            var stripeResponseHandler = function (status, response) {
                if (response.error) {
                    // Show the errors on the form
                    $tbk_form.find('.payment-errors').text(response.error.message);
                    $tbk_form.find('.tbk-error-message-form').addClass('tbk-visible');
                    $tbk_form.find('.tbk-submit').removeClass('disabled tbk-loading');
                    $tbk_form.closest('.tb-frontend-calendar').trigger('tbk:slider:adapt');
                } else {
                    // token contains id, last4, and card type
                    var token = response.id;
                    // and re-submit
                    var ajax_url = '<?= admin_url('admin-ajax.php') ?>';
                    var ajax_nonce = '<?= wp_create_nonce('teambooking_process_payment_onsite') ?>';
                    jQuery.post(
                        ajax_url,
                        {
                            action                 : 'tb_process_onsite_payment',
                            gateway_id             : '<?= $this->gateway_id ?>',
                            additional_parameter   : token,
                            reservation_database_id: '<?= $lead->getDatabaseId() ?>',
                            order_id               : '<?= $order->getId() ?>',
                            order_redirect         : '<?= $order_redirect ?>',
                            nonce                  : ajax_nonce
                        },
                        function (response) {
                            if (response.status == 'redirect') {
                                window.location.href = response.redirect;
                            }
                            // Load the response
                            var $slider = jQuery.tbkSliderGet($tbk_form.closest('.tb-frontend-calendar'));
                            $slider.goToSlide($slider.addSlide(response.content).index());
                            $tbk_form.find('.tbk-submit').removeClass('disabled tbk-loading');
                        }, "json"
                    );
                }
            };
            jQuery('form[id^="tb-stripe-payment-form-"]')
                .on('input', 'input', function (e) {
                    switch (jQuery(this).data('stripe')) {
                        case 'number':
                            if (Stripe.card.validateCardNumber(jQuery(this).val()) === false) {
                                jQuery(this).closest('.tbk-field').addClass('tbk-error');
                            } else {
                                jQuery(this).closest('.tbk-field').removeClass('tbk-error');
                            }
                            break;
                        case 'cvc':
                            if (Stripe.card.validateCVC(jQuery(this).val()) === false) {
                                jQuery(this).closest('.tbk-field').addClass('tbk-error');
                            } else {
                                jQuery(this).closest('.tbk-field').removeClass('tbk-error');
                            }
                            break;
                        case 'exp':
                            if (Stripe.card.validateExpiry(jQuery(this).val()) === false) {
                                jQuery(this).closest('.tbk-field').addClass('tbk-error');
                            } else {
                                jQuery(this).closest('.tbk-field').removeClass('tbk-error');
                            }
                            break;
                        default:
                            break;
                    }
                })
                .on('click keydown', '.tbk-submit', function (e) {
                    e.stopPropagation();
                    if (jQuery(this).hasClass('disabled tbk-loading')) {
                        return false;
                    }
                    if (e.which == 13 || e.which == 32 || e.which == 1) {
                        $tbk_form = jQuery(this).closest('form');
                        // Reset the errors
                        $tbk_form.find('.tbk-error-message-form').removeClass('tbk-visible');
                        $tbk_form.find('.payment-errors').text('');
                        $tbk_form.closest('.tb-frontend-calendar').trigger('tbk:slider:adapt');
                        // Disable the submit button to prevent repeated clicks
                        jQuery(this).addClass('disabled tbk-loading');
                        Stripe.card.createToken($tbk_form, stripeResponseHandler);
                        // Prevent the form from submitting with the default action
                        return false;
                    }
                });
        </script>

        <?php
        return ob_get_clean();
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    public function verifyCurrency($code)
    {
        $supported_currencies = array();
        $supported_currencies['AED'] = 'United Arab Emirates Dirham';
        $supported_currencies['AFN'] = 'Afghan Afghani';
        $supported_currencies['ALL'] = 'Albanian Lek';
        $supported_currencies['AMD'] = 'Armenian Dram';
        $supported_currencies['ANG'] = 'Netherlands Antillean Gulden';
        $supported_currencies['AOA'] = 'Angolan Kwanza';
        $supported_currencies['ARS'] = 'Argentine Peso';
        $supported_currencies['AUD'] = 'Australian Dollar';
        $supported_currencies['AWG'] = 'Aruban Florin';
        $supported_currencies['AZN'] = 'Azerbaijani Manat';
        $supported_currencies['BAM'] = 'Bosnia & Herzegovina Convertible Mark';
        $supported_currencies['BBD'] = 'Barbadian Dollar';
        $supported_currencies['BDT'] = 'Bangladeshi Taka';
        $supported_currencies['BGN'] = 'Bulgarian Lev';
        $supported_currencies['BIF'] = 'Burundian Franc';
        $supported_currencies['BMD'] = 'Bermudian Dollar';
        $supported_currencies['BND'] = 'Brunei Dollar';
        $supported_currencies['BOB'] = 'Bolivian Boliviano';
        $supported_currencies['BRL'] = 'Brazilian Real';
        $supported_currencies['BSD'] = 'Bahamian Dollar';
        $supported_currencies['BWP'] = 'Botswana Pula';
        $supported_currencies['BZD'] = 'Belize Dollar';
        $supported_currencies['CAD'] = 'Canadian Dollar';
        $supported_currencies['CDF'] = 'Congolese Franc';
        $supported_currencies['CHF'] = 'Swiss Franc';
        $supported_currencies['CLP'] = 'Chilean Peso';
        $supported_currencies['CNY'] = 'Chinese Renminbi Yuan';
        $supported_currencies['COP'] = 'Colombian Peso';
        $supported_currencies['CRC'] = 'Costa Rican Colón';
        $supported_currencies['CVE'] = 'Cape Verdean Escudo';
        $supported_currencies['CZK'] = 'Czech Koruna';
        $supported_currencies['DJF'] = 'Djiboutian Franc';
        $supported_currencies['DKK'] = 'Danish Krone';
        $supported_currencies['DOP'] = 'Dominican Peso';
        $supported_currencies['DZD'] = 'Algerian Dinar';
        $supported_currencies['EGP'] = 'Egyptian Pound';
        $supported_currencies['ETB'] = 'Ethiopian Birr';
        $supported_currencies['EUR'] = 'Euro';
        $supported_currencies['FJD'] = 'Fijian Dollar';
        $supported_currencies['FKP'] = 'Falkland Islands Pound';
        $supported_currencies['GBP'] = 'British Pound';
        $supported_currencies['GEL'] = 'Georgian Lari';
        $supported_currencies['GIP'] = 'Gibraltar Pound';
        $supported_currencies['GMD'] = 'Gambian Dalasi';
        $supported_currencies['GNF'] = 'Guinean Franc';
        $supported_currencies['GTQ'] = 'Guatemalan Quetzal';
        $supported_currencies['GYD'] = 'Guyanese Dollar';
        $supported_currencies['HKD'] = 'Hong Kong Dollar';
        $supported_currencies['HNL'] = 'Honduran Lempira';
        $supported_currencies['HRK'] = 'Croatian Kuna';
        $supported_currencies['HTG'] = 'Haitian Gourde';
        $supported_currencies['HUF'] = 'Hungarian Forint';
        $supported_currencies['IDR'] = 'Indonesian Rupiah';
        $supported_currencies['ILS'] = 'Israeli New Sheqel';
        $supported_currencies['INR'] = 'Indian Rupee';
        $supported_currencies['ISK'] = 'Icelandic Króna';
        $supported_currencies['JMD'] = 'Jamaican Dollar';
        $supported_currencies['JPY'] = 'Japanese Yen';
        $supported_currencies['KES'] = 'Kenyan Shilling';
        $supported_currencies['KGS'] = 'Kyrgyzstani Som';
        $supported_currencies['KHR'] = 'Cambodian Riel';
        $supported_currencies['KMF'] = 'Comorian Franc';
        $supported_currencies['KRW'] = 'South Korean Won';
        $supported_currencies['KYD'] = 'Cayman Islands Dollar';
        $supported_currencies['KZT'] = 'Kazakhstani Tenge';
        $supported_currencies['LAK'] = 'Lao Kipa';
        $supported_currencies['LBP'] = 'Lebanese Pound';
        $supported_currencies['LKR'] = 'Sri Lankan Rupee';
        $supported_currencies['LRD'] = 'Liberian Dollar';
        $supported_currencies['LSL'] = 'Lesotho Loti';
        $supported_currencies['MAD'] = 'Moroccan Dirham';
        $supported_currencies['MDL'] = 'Moldovan Leu';
        $supported_currencies['MGA'] = 'Malagasy Ariary';
        $supported_currencies['MKD'] = 'Macedonian Denar';
        $supported_currencies['MNT'] = 'Mongolian Tögrög';
        $supported_currencies['MOP'] = 'Macanese Pataca';
        $supported_currencies['MRO'] = 'Mauritanian Ouguiya';
        $supported_currencies['MUR'] = 'Mauritian Rupee';
        $supported_currencies['MVR'] = 'Maldivian Rufiyaa';
        $supported_currencies['MWK'] = 'Malawian Kwacha';
        $supported_currencies['MXN'] = 'Mexican Peso';
        $supported_currencies['MYR'] = 'Malaysian Ringgit';
        $supported_currencies['MZN'] = 'Mozambican Metical';
        $supported_currencies['NAD'] = 'Namibian Dollar';
        $supported_currencies['NGN'] = 'Nigerian Naira';
        $supported_currencies['NIO'] = 'Nicaraguan Córdoba';
        $supported_currencies['NOK'] = 'Norwegian Krone';
        $supported_currencies['NPR'] = 'Nepalese Rupee';
        $supported_currencies['NZD'] = 'New Zealand Dollar';
        $supported_currencies['PAB'] = 'Panamanian Balboa';
        $supported_currencies['PEN'] = 'Peruvian Nuevo Sol';
        $supported_currencies['PGK'] = 'Papua New Guinean Kina';
        $supported_currencies['PHP'] = 'Philippine Peso';
        $supported_currencies['PKR'] = 'Pakistani Rupee';
        $supported_currencies['PLN'] = 'Polish Złoty';
        $supported_currencies['PYG'] = 'Paraguayan Guaraní';
        $supported_currencies['QAR'] = 'Qatari Riyal';
        $supported_currencies['RON'] = 'Romanian Leu';
        $supported_currencies['RSD'] = 'Serbian Dinar';
        $supported_currencies['RUB'] = 'Russian Ruble';
        $supported_currencies['RWF'] = 'Rwandan Franc';
        $supported_currencies['SAR'] = 'Saudi Riyal';
        $supported_currencies['SBD'] = 'Solomon Islands Dollar';
        $supported_currencies['SCR'] = 'Seychellois Rupee';
        $supported_currencies['SEK'] = 'Swedish Krona';
        $supported_currencies['SGD'] = 'Singapore Dollar';
        $supported_currencies['SHP'] = 'Saint Helenian Pound';
        $supported_currencies['SLL'] = 'Sierra Leonean Leone';
        $supported_currencies['SOS'] = 'Somali Shilling';
        $supported_currencies['SRD'] = 'Surinamese Dollar';
        $supported_currencies['STD'] = 'São Tomé and Príncipe Dobra';
        $supported_currencies['SZL'] = 'Swazi Lilangeni';
        $supported_currencies['THB'] = 'Thai Baht';
        $supported_currencies['TJS'] = 'Tajikistani Somoni';
        $supported_currencies['TOP'] = 'Tongan Paʻanga';
        $supported_currencies['TRY'] = 'Turkish Lira';
        $supported_currencies['TTD'] = 'Trinidad and Tobago Dollar';
        $supported_currencies['TWD'] = 'New Taiwan Dollar';
        $supported_currencies['TZS'] = 'Tanzanian Shilling';
        $supported_currencies['UAH'] = 'Ukrainian Hryvnia';
        $supported_currencies['UGX'] = 'Ugandan Shilling';
        $supported_currencies['USD'] = 'United States Dollar';
        $supported_currencies['UYU'] = 'Uruguayan Peso';
        $supported_currencies['UZS'] = 'Uzbekistani Som';
        $supported_currencies['VEF'] = 'Venezuelan Bolívar';
        $supported_currencies['VND'] = 'Vietnamese Đồng';
        $supported_currencies['VUV'] = 'Vanuatu Vatu';
        $supported_currencies['WST'] = 'Samoan Tala';
        $supported_currencies['XAF'] = 'Central African Cfa Franc';
        $supported_currencies['XCD'] = 'East Caribbean Dollar';
        $supported_currencies['XOF'] = 'West African Cfa Franc';
        $supported_currencies['XPF'] = 'Cfp Franc';
        $supported_currencies['YER'] = 'Yemeni Rial';
        $supported_currencies['ZAR'] = 'South African Rand';
        $supported_currencies['ZMW'] = 'Zambian Kwacha';
        if (array_key_exists($code, $supported_currencies)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * @param array $post_data
     */
    public function listenerIPN($post_data)
    {
    }

    /**
     * @return string
     */
    public function get_json()
    {
        $encoded = json_encode(get_object_vars($this));
        if ($encoded) {
            return $encoded;
        }

        return '[]';
    }

    /**
     * @param string $json
     */
    public function inject_json($json)
    {
        $array = json_decode($json, TRUE);
        if (!array()) {
            $array = array();
        }
        if (isset($array['use_gateway'])) $this->setUseGateway($array['use_gateway']);
        if (isset($array['secret_key'])) $this->setSecretKey($array['secret_key']);
        if (isset($array['pub_key'])) $this->setPublishableKey($array['pub_key']);
        if (isset($array['send_receipt'])) $this->setSendReceipt($array['send_receipt']);
        if (isset($array['load_library'])) $this->setLoadLibrary($array['load_library']);
    }

}
