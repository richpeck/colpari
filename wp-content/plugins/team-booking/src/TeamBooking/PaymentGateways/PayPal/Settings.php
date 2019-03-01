<?php

defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Functions,
    TeamBooking\Admin\Framework,
    TeamBooking\Database,
    TeamBooking\PaymentGateways;

/**
 * Class TeamBooking_PaymentGateways_PayPal_Settings
 *
 * @author VonStroheim
 */
class TeamBooking_PaymentGateways_PayPal_Settings implements TeamBooking_PaymentGateways_Settings
{
    private $use_gateway;
    private $gateway_id;
    private $account_email;
    private $primary_email;
    private $logo_media_id;
    private $redirect_url;
    private $use_sandbox;
    private $save_ipn_logs;
    private $dump_ipn;
    private $use_curl;
    private static $already_called = FALSE;

    public function __construct()
    {
        $this->gateway_id = 'paypal';
        $this->use_gateway = FALSE;
        $this->use_sandbox = FALSE;
        $this->save_ipn_logs = FALSE;
        $this->dump_ipn = FALSE;
        $this->redirect_url = site_url();
        $this->use_curl = extension_loaded('curl');
    }

    public function __wakeup()
    {
        if (!self::$already_called) {
            add_action('tbk_admin_nags', array($this, 'sandbox_active_warning'));
        }
        self::$already_called = TRUE;
    }

    public function sandbox_active_warning()
    {
        if ($this->isActive() && $this->isUseSandbox()) {
            Framework\Notice::getNegative(__('PayPal Sandbox is active, remember to deactivate it when testing is finished!', 'team-booking'))->render();
        }
    }

    /**
     * @return bool
     */
    public function isOffsite()
    {
        return TRUE;
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
        return $this->use_gateway;
    }

    /**
     * @return string
     */
    public function getGatewayId()
    {
        return $this->gateway_id;
    }

    /**
     * @param string $email
     */
    public function setAccountEmail($email)
    {
        $this->account_email = $email;
    }

    /**
     * @return string
     */
    public function getAccountEmail()
    {
        return trim($this->account_email);
    }

    /**
     * @param string $email
     */
    public function setPrimaryEmail($email)
    {
        $this->primary_email = $email;
    }

    /**
     * @return string
     */
    public function getPrimaryEmail()
    {
        return (!trim($this->primary_email)) ? $this->getAccountEmail() : trim($this->primary_email);
    }

    /**
     * @param $id
     */
    public function setLogoMediaId($id)
    {
        $this->logo_media_id = $id;
    }

    /**
     * @return mixed
     */
    public function getLogoMediaId()
    {
        return $this->logo_media_id;
    }

    /**
     * @param string $url
     */
    public function setRedirectUrl($url)
    {
        $this->redirect_url = $url;
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirect_url;
    }

    /**
     * @param $boolean
     */
    public function setUseCurl($boolean)
    {
        $this->use_curl = (bool)$boolean;
    }

    /**
     * @return bool
     */
    public function getUseCurl()
    {
        if (extension_loaded('curl')) {
            if (NULL === $this->use_curl) {
                return TRUE;
            } else {
                return $this->use_curl;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * @param $boolean
     */
    public function setUseSandbox($boolean)
    {
        $this->use_sandbox = (bool)$boolean;
    }

    /**
     * @return bool
     */
    public function isUseSandbox()
    {
        return $this->use_sandbox;
    }

    /**
     * @param $boolean
     */
    public function setSaveIpnLogs($boolean)
    {
        $this->save_ipn_logs = (bool)$boolean;
    }

    /**
     * @return bool
     */
    public function isSaveIpnLogs()
    {
        return $this->save_ipn_logs;
    }

    /**
     * @param $boolean
     */
    public function setDumpIPN($boolean)
    {
        $this->dump_ipn = (bool)$boolean;
    }

    /**
     * @return bool
     */
    public function getDumpIPN()
    {
        return (bool)$this->dump_ipn;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return __('via PayPal', 'team-booking');
    }

    /**
     * @return string
     */
    public function getPayButton()
    {
        ob_start();
        ?>
        <div class="tb-icon tbk-button tbk-blue tbk-pay-button" data-offsite="<?= $this->isOffsite() ?>"
             data-gateway="<?= $this->gateway_id ?>" tabindex="0">
            <i class="paypal tb-icon"></i>

            <div class="tbk-content">
                <?= esc_html__('Pay with PayPal', 'team-booking') ?>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }

    /**
     * @param array  $items
     * @param string $order_redirect
     *
     * @return bool
     */
    public function getDataForm($items, $order_redirect)
    {
        return FALSE;
    }

    /**
     * @param TeamBooking_ReservationData[] $items
     * @param null                          $additional_parameter
     *
     * @return string
     * @throws Exception
     */
    public function prepareGateway(array $items, $additional_parameter = NULL)
    {
        $paypal = new PaymentGateways\PayPal\Gateway();
        foreach ($items as $item) {
            $paypal->addItem($item->getServiceName() . ' | #' . $item->getDatabaseId(TRUE), $item->getPriceIncremented(), $item->getTickets());
        }
        $paypal->setIpnId($additional_parameter);
        $lead = reset($items);
        $paypal->setCurrency($lead->getCurrencyCode());
        // should we override the redirect URL for this service? TODO: review this
        if (Database\Services::get($lead->getServiceId())->getSettingsFor('redirect')) {
            $paypal->setRedirectUrl(Database\Services::get($lead->getServiceId())->getRedirectUrl($lead->getDatabaseId()));
        } else {
            $paypal->setRedirectUrl($this->getRedirectUrl());
        }

        return $paypal->processPayment();
    }

    /**
     * @param array  $settings
     * @param string $new_currency_code
     */
    public function saveBackendSettings(array $settings, $new_currency_code)
    {
        if (isset($settings['use_gateway'])) {
            (!empty($settings['account_email']) && $this->verifyCurrency($new_currency_code)) ? $this->setUseGateway($settings['use_gateway']) : $this->setUseGateway(FALSE);
        }
        isset($settings['account_email']) ? $this->setAccountEmail(strtolower($settings['account_email'])) : $this->setAccountEmail('');
        isset($settings['primary_email']) ? $this->setPrimaryEmail(strtolower($settings['primary_email'])) : $this->setPrimaryEmail('');
        isset($settings['checkout_logo']) ? $this->setLogoMediaId($settings['checkout_logo']) : $this->setLogoMediaId('');
        isset($settings['redirect_url']) ? $this->setRedirectUrl($settings['redirect_url']) : $this->setRedirectUrl('');
        if (isset($settings['use_sandbox'])) {
            $this->setUseSandbox($settings['use_sandbox']);
        }
        isset($settings['save_ipn_logs']) ? $this->setSaveIpnLogs(TRUE) : $this->setSaveIpnLogs(FALSE);
        isset($settings['ipn_dump']) ? $this->setDumpIPN(TRUE) : $this->setDumpIPN(FALSE);
        if ($settings['use_curl'] === 'yes') {
            $this->setUseCurl(TRUE);
        } else {
            $this->setUseCurl(FALSE);
        }
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
            'text'   => Framework\Html::img(array('src' => 'https://www.paypalobjects.com/webstatic/i/logo/rebrand/ppcom.png', 'alt' => __('PayPal', 'team-booking'))),
            'escape' => FALSE,
            'target' => '_blank',
            'href'   => 'https://www.paypal.com'
        ))));

        // Use PayPal gateway
        $element = new Framework\PanelSettingYesOrNo(__('Use PayPal gateway', 'team-booking'));
        if (empty($this->account_email)) {
            $element->addDescription(__("You can't activate the PayPal gateway if the account e-mail field is empty.", 'team-booking'));
            $element->setDisabled(TRUE);
        }
        if ($this->verifyCurrency(Functions\getSettings()->getCurrencyCode())) {
            $element->addNotice(__('be sure that your PayPal account actually holds the selected currency, otherwise your payments may require manual confirmation, depending on Payment Receiving Preferences in your PayPal profile.', 'team-booking'));
        } else {
            $element->addAlert(__("The selected currency is not supported by PayPal. PayPal gateway can't be activated.", 'team-booking'));
        }
        $element->addFieldname('gateway_settings[' . $this->gateway_id . '][use_gateway]');
        $element->setState($this->use_gateway);
        $element->appendTo($panel);

        // PayPal account email
        $element = new Framework\PanelSettingText(__('PayPal account e-mail', 'team-booking'));
        $element->addFieldname('gateway_settings[' . $this->gateway_id . '][account_email]');
        $element->addDescription(__('Payments will be addressed to this e-mail.', 'team-booking'));
        $element->addNotice(__('the usage of either a Business or Premium PayPal account is recommended to avoid possible issues, as you are not supposed to make commercial transactions with a Personal PayPal account.', 'team-booking'));
        $element->addAlert(__('if you are using the PayaPal Sandbox for testing, please ensure to put here an e-mail address generated in the Sandbox and not the one of your live account, or the IPN will fail.', 'team-booking'));
        $element->addDefaultValue($this->account_email);
        $element->appendTo($panel);

        // PayPal primary email
        $element = new Framework\PanelSettingText(__('PayPal primary e-mail (optional)', 'team-booking'));
        $element->addFieldname('gateway_settings[' . $this->gateway_id . '][primary_email]');
        $element->addDescription(__('Payments received will be checked against this e-mail. Use this setting if your PayPal account handles multiple e-mail and the payments are not addressed to your primary one.', 'team-booking'));
        $element->addNotice(__('ensure that your primary PayPal e-mail address is actually this one, otherwise payment notifications will fail.', 'team-booking'));
        $element->addDefaultValue($this->primary_email);
        $element->appendTo($panel);

        // Redirect URL
        $element = new Framework\PanelSettingText(__('Redirect URL', 'team-booking'));
        $element->addFieldname('gateway_settings[' . $this->gateway_id . '][redirect_url]');
        $element->addDescription(__('After PayPal payment, the customer will be redirected to this URL.', 'team-booking'));
        $element->addDefaultValue($this->redirect_url);
        $element->appendTo($panel);

        // Logo
        $element = new Framework\PanelSettingInsertMedia(__('Checkout logo (optional)', 'team-booking'));
        $element->addDescription(__('Choose an image that will be shown in PayPal checkout page. It must be 150x50px.', 'team-booking'));
        $element->addFieldname('gateway_settings[' . $this->gateway_id . '][checkout_logo]');
        $element->addDefaultMediaId($this->logo_media_id);
        $element->appendTo($panel);

        // IPN listener method
        $element = new Framework\PanelSettingRadios(__('IPN listener method', 'team-booking'));
        $element->addFieldname('gateway_settings[' . $this->gateway_id . '][use_curl]');
        $element->addDescription(__('cURL is default, if supported by your server. If you have issues like reservations kept in pending status, try to change method.', 'team-booking'));
        $element->addOption(array(
            'label'    => 'cURL' . (!extension_loaded('curl') ? ' (' . __('not supported', 'team-booking') . ')' : ''),
            'disabled' => extension_loaded('curl') ? FALSE : TRUE,
            'value'    => 'yes',
            'checked'  => $this->getUseCurl()
        ));
        $element->addOption(array(
            'label'   => 'fsockopen',
            'value'   => 'no',
            'checked' => !$this->getUseCurl()
        ));
        $element->appendTo($panel);

        // Use PayPal Sandbox
        $element = new Framework\PanelSettingYesOrNo(__('Use PayPal Sandbox for testing', 'team-booking'));
        $element->addFieldname('gateway_settings[' . $this->gateway_id . '][use_sandbox]');
        $element->addDescription(__('Fake payments. Activate this option only if you really know what the sandbox is.', 'team-booking'));
        $element->addNotice(__("if you're testing the payments in a localhost environment, the PayPal IPN won't reach your computer, and the plugin will act as if payment was never made.", 'team-booking'));
        $element->setState($this->use_sandbox);
        $element->appendTo($panel);

        // Debugging IPN errors
        $element = new Framework\PanelSettingCheckboxes(__('Debugging IPN errors', 'team-booking'));
        $element->addCheckbox(array(
            'label'     => Framework\ElementFrom::content(
                esc_html__('Activate this option to save the IPN response error logs', 'team-booking') . ' (' . Framework\Html::anchor(array('id' => 'tb-paypal-ipn-log', 'text' => esc_html__('read', 'team-booking'))) . ')'
            ),
            'checked'   => $this->save_ipn_logs,
            'fieldname' => 'gateway_settings[' . $this->gateway_id . '][save_ipn_logs]',
            'escape'    => FALSE
        ));
        $element->addCheckbox(array(
            'label'     => Framework\ElementFrom::content(
                esc_html__('Activate this option to dump the last raw IPN response', 'team-booking') . ' (' . Framework\Html::anchor(array('id' => 'tb-paypal-ipn-dump', 'text' => esc_html__('read', 'team-booking'))) . ')'
            ),
            'checked'   => $this->dump_ipn,
            'fieldname' => 'gateway_settings[' . $this->gateway_id . '][ipn_dump]',
            'escape'    => FALSE
        ));
        $element->appendTo($panel);

        // Save changes
        $wildcard = new Framework\PanelSettingWildcard(NULL);
        $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'tbk_save_payments');
        $wildcard->addContent($element);

        // IPN error log modal
        $element = new Framework\Modal('tb-paypal-ipn-log-modal');
        $element->setButtons(FALSE);
        $element->setHeaderText(array('main' => __('IPN response error logs', 'team-booking')));
        $element->addContent('<textarea style="width: 100%" rows="10" readonly="readonly">');
        if (file_exists(__DIR__ . '/ipn_errors.log')) {
            $element->addContent(file_get_contents(__DIR__ . '/ipn_errors.log'));
        }
        $element->addContent('</textarea>');
        $wildcard->addContent($element);

        // IPN last dump modal
        $element = new Framework\Modal('tb-paypal-ipn-dump-modal');
        $element->setButtons(FALSE);
        $element->setHeaderText(array('main' => __('Last IPN dump', 'team-booking')));
        $element->addContent('<textarea style="width: 100%" rows="10" readonly="readonly">');
        if (file_exists(__DIR__ . '/ipn_dump.log')) {
            $element->addContent(file_get_contents(__DIR__ . '/ipn_dump.log'));
        }
        $element->addContent('</textarea>');
        $wildcard->addContent($element);

        // Scripts
        ob_start();
        ?>
        <script>
            jQuery('#tb-paypal-ipn-log-modal')
                .uiModal('attach events', '#tb-paypal-ipn-log', 'show')
            ;
            jQuery('#tb-paypal-ipn-dump-modal')
                .uiModal('attach events', '#tb-paypal-ipn-dump', 'show')
            ;
            jQuery('#tb-paypal-ipn-log, #tb-paypal-ipn-dump').click(function (e) {
                e.preventDefault();
            });
        </script>
        <?php
        $element = Framework\ElementFrom::content(ob_get_clean());
        $wildcard->addContent($element);
        $panel->addElement($wildcard);

        return $panel;

    }

    /**
     * @param string $code
     *
     * @return bool
     */
    public function verifyCurrency($code)
    {
        $supported_currencies = array(
            'AUD' => array(
                'label'  => 'Australian Dollar',
                'format' => '$ %s',
            ),
            'BRL' => array(
                'label'  => 'Brazilian Real',
                'format' => 'R$ %s',
            ),
            'CAD' => array(
                'label'  => 'Canadian Dollar',
                'format' => '$ %s',
            ),
            'CZK' => array(
                'label'  => 'Czech Koruna',
                'format' => '%s Kč',
            ),
            'DKK' => array(
                'label'  => 'Danish Krone',
                'format' => '%s kr',
            ),
            'EUR' => array(
                'label'  => 'Euro',
                'format' => '€ %s',
            ),
            'HKD' => array(
                'label'  => 'Hong Kong Dollar',
                'format' => '$ %s',
            ),
            'HUF' => array(
                'label'  => 'Hungarian Forint',
                'format' => '%s Ft',
            ),
            'ILS' => array(
                'label'  => 'Israeli New Sheqel',
                'format' => '₪ %s',
            ),
            'JPY' => array(
                'label'  => 'Japanese Yen',
                'format' => '¥ %s',
            ),
            'MYR' => array(
                'label'  => 'Malaysian Ringgit',
                'format' => 'RM %s',
            ),
            'MXN' => array(
                'label'  => 'Mexican Peso',
                'format' => '$ %s',
            ),
            'NOK' => array(
                'label'  => 'Norwegian Krone',
                'format' => '%s kr',
            ),
            'NZD' => array(
                'label'  => 'N.Z. Dollar',
                'format' => '$ %s',
            ),
            'PHP' => array(
                'label'  => 'Philippine Peso',
                'format' => '₱ %s',
            ),
            'PLN' => array(
                'label'  => 'Polish Zloty',
                'format' => '%s zł',
            ),
            'GBP' => array(
                'label'  => 'Pound Sterling',
                'format' => '£ %s',
            ),
            'SGD' => array(
                'label'  => 'Singapore Dollar',
                'format' => '$ %s',
            ),
            'SEK' => array(
                'label'  => 'Swedish Krona',
                'format' => '%s kr',
            ),
            'CHF' => array(
                'label'  => 'Swiss Franc',
                'format' => '%s Fr',
            ),
            'TWD' => array(
                'label'  => 'New Taiwan Dollar',
                'format' => 'NT$ %s',
            ),
            'THB' => array(
                'label'  => 'Thai Baht',
                'format' => '฿ %s',
            ),
            'RUB' => array(
                'label'  => 'Russian Ruble',
                'format' => '₽ %s',
            ),
            'USD' => array(
                'label'  => 'U.S. Dollar',
                'format' => '$ %s',
            ),
        );
        if (array_key_exists($code, $supported_currencies)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * @param array $ipn_data
     */
    public function listenerIPN($ipn_data)
    {
        // Log IPN errors
        if ($this->isSaveIpnLogs()) {
            ini_set('log_errors', TRUE);
            ini_set('error_log', __DIR__ . '/ipn_errors.log');
        }
        // instantiate the IPN listener
        include_once __DIR__ . '/lib/tb_ipnlistener.php';
        $listener = new TbkIpnListener();

        // tell the IPN listener whether to use the PayPal test sandbox or not
        $listener->use_sandbox = $this->isUseSandbox();

        // tell the IPN listener whether to use cURL or fsockopen
        $listener->use_curl = $this->getUseCurl();

        // try to process the IPN POST
        try {
            $listener->requirePostMethod();
            $verified = $listener->processIpn($ipn_data);
        } catch (Exception $e) {
            // Log IPN errors
            if ($this->isSaveIpnLogs()) {
                error_log($e->getMessage());
            }
            // Dump IPN
            if ($this->getDumpIPN()) {
                ob_start();
                var_dump($ipn_data);
                $output = ob_get_clean();
                $outputFile = __DIR__ . '/ipn_dump.log';
                $filehandle = fopen($outputFile, 'a') or die();
                fwrite($filehandle, $output);
                fclose($filehandle);
            }
            exit;
        }

        if ($verified) {
            if (isset($ipn_data['charset'])) {
                $charset = $ipn_data['charset'];
                // If not UTF-8, convert all the values
                if (Functions\tb_mb_strtoupper($charset) !== 'UTF-8') {
                    foreach ($ipn_data as $key => &$value) {
                        $value = Functions\tb_mb_convert_encoding($value, 'UTF-8', $charset);
                    }
                    unset($value);
                }
                // Store the charset values for future implementation
                $ipn_data['charset'] = 'UTF-8';
                $ipn_data['charset_original'] = $charset;
            }

            $reservation = Database\Reservations::getByToken($ipn_data['custom']);
            $reservations_order = Database\Reservations::getByOrderId($ipn_data['custom']);
            $order = new \TeamBooking\Order();
            $order->setItems($reservations_order);
            $order->setId($ipn_data['custom']);

            $errmsg = '';   // stores errors from fraud checks

            if (!$reservation && empty($reservations_order)) { // Reservation is not found!
                $errmsg .= 'Reservation token not found: ';
                $errmsg .= $ipn_data['custom'] . "\n";
                $price = 0;
            } else {
                if (!$reservation) {
                    $price = number_format($order->get_to_be_paid_amount(), 2, '.', '');
                } else {
                    $reservation->setToBePaid(TRUE);
                    if ($reservation->getServiceClass() === 'event') {
                        $price = number_format($reservation->getPriceIncremented() * $reservation->getTickets(), 2, '.', '');
                    } else {
                        $price = number_format($reservation->getPriceIncremented(), 2, '.', '');
                    }
                }

            }

            // 1. Make sure the payment status is "Completed"
            $multi_currency = FALSE;
            if ($ipn_data['payment_status'] !== 'Completed') {
                if ($ipn_data['payment_status'] === 'Pending' && $ipn_data['pending_reason'] === 'multi_currency') {
                    // Warning: the PayPal account
                    // settings requires manual confirmation of payments
                    // with currency that merchant don't holds
                    $body = "WARNING: You are asking for payments in a currency that your PayPal account doesn't hold! Please either change the currency, or change your PayPal -> Profile -> Payment receiving preferences for 'Allow payments sent to me in a currency I do not hold' to 'Yes, accept and convert them'.\n\n";
                    $body .= $listener->getTextReport();
                    wp_mail(get_bloginfo('admin_email'), 'Currency not held by your PayPal account', $body);
                    $multi_currency = TRUE;
                    unset($body);
                } else {
                    exit;
                }
            }
            // 2. Make sure seller email matches your primary account email.
            if (strtolower($ipn_data['receiver_email']) !== strtolower($this->getPrimaryEmail())) {
                $errmsg .= "'receiver_email' does not match: \n";
                $errmsg .= $ipn_data['receiver_email'] . "\n";
                $errmsg .= $this->getPrimaryEmail() . "\n";
            }
            // 3. Make sure the amount(s) paid match
            $paid_amount = $ipn_data['mc_gross'];
            $taxes = 0.00;
            if (isset($ipn_data['tax']) && is_numeric($ipn_data['tax'])) {
                $taxes = $ipn_data['tax'];
            }
            if (($paid_amount - $taxes) != $price) {
                $errmsg .= "'mc_gross' does not match: ";
                $errmsg .= $paid_amount . "\n";
            }
            // 4. Make sure the currency code matches
            if ($ipn_data['mc_currency'] != Functions\getSettings()->getCurrencyCode()) {
                $errmsg .= "'mc_currency' does not match: ";
                $errmsg .= $ipn_data['mc_currency'] . "\n";
            }
            // 5. Ensure the transaction is not a duplicate.
            $txn_id = $ipn_data['txn_id'];
            foreach (Database\Reservations::getByPaymentGateway('paypal', TRUE) as $res_id => $details) {
                /* @var $details array */
                if ($details['transaction id'] == $txn_id) {
                    $errmsg .= "'txn_id' has already been processed: " . $txn_id . "\n";
                    $errmsg .= "Reservation id: " . $res_id . "\n";
                }
            }

            if (!empty($errmsg)) {
                // manually investigate errors from the fraud checking
                $body = "IPN failed fraud checks: \n$errmsg\n\n";
                $body .= $listener->getTextReport();
                wp_mail(get_bloginfo('admin_email'), 'IPN Fraud Warning', $body);
            } else {
                $payment_details_array = array();
                if ($multi_currency) {
                    $payment_details_array['notes'] = esc_html__('Done in a currency not held by your PayPal account at the moment of the transaction.', 'team-booking');
                }
                $payment_details_array['transaction id'] = $txn_id;
                $payment_details_array['paid amount'] = $paid_amount;
                $payment_details_array['payer email'] = $ipn_data['payer_email'];

                $reservations = empty($reservations_order) ? array($reservation) : $reservations_order;
                foreach ($reservations as $reservation) {
                    if (!$reservation->isToBePaid()) continue;
                    $reservation->setPaid(TRUE);
                    $reservation->setPaymentGateway($this->gateway_id);
                    $reservation->setPaymentDetails($payment_details_array);
                    if (!$reservation->isPending()) {
                        // Reservation was not pending, updating the placed one
                        Database\Reservations::update($reservation);
                    } else {
                        // Reservation was pending, do the reservation...
                        $var = new TeamBooking_Reservation($reservation);
                        $attempted = $var->doReservation();
                        // Check for errors
                        if ($attempted instanceof TeamBooking_Error) {
                            // At this point, payment is made but
                            // the reservation attempt throws errors.
                            $reservation->setStatusPending();
                            $errmsg = $attempted->getMessage();
                            $reservation->setPendingReason($errmsg);
                            Database\Reservations::update($reservation);
                            // TODO better handling
                            $body = "Error message: \n$errmsg\n\n";
                            $body .= $listener->getTextReport();
                            wp_mail(get_bloginfo('admin_email'), 'Reservation error after payment is done.', $body);
                        } else {
                            // No errors
                            $reservation->setStatusConfirmed();
                            Database\Reservations::update($reservation);
                            // Send e-mail messages
                            if ($var->getServiceObj()->getEmailToAdmin('send')) {
                                $var->sendNotificationEmail();
                            }
                            if (Functions\getSettings()->getCoworkerData($reservation->getCoworker())->getCustomEventSettings($reservation->getServiceId())->getGetDetailsByEmail()) {
                                $var->sendNotificationEmailToCoworker();
                            }
                            if ($reservation->getCustomerEmail() && $var->getServiceObj()->getEmailToCustomer('send')) {
                                $var->sendConfirmationEmail();
                            }
                        }
                    }
                }
            }
            if ($this->getDumpIPN()) {
                ob_start();
                var_dump($errmsg);
                echo '<br>';
                var_dump($ipn_data);
                $output = ob_get_clean();
                $outputFile = __DIR__ . '/ipn_dump.log';
                $filehandle = fopen($outputFile, 'w') or die();
                fwrite($filehandle, $output);
                fclose($filehandle);
            }
            exit;
        } else {
            // manually investigate the invalid IPN
            wp_mail(get_bloginfo('admin_email'), 'Invalid IPN', $listener->getTextReport());
            exit;
        }
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
        if (isset($array['account_email'])) $this->setAccountEmail($array['account_email']);
        if (isset($array['primary_email'])) $this->setPrimaryEmail($array['primary_email']);
        if (isset($array['logo_media_id'])) $this->setLogoMediaId($array['logo_media_id']);
        if (isset($array['redirect_url'])) $this->setRedirectUrl($array['redirect_url']);
        if (isset($array['use_sandbox'])) $this->setUseSandbox($array['use_sandbox']);
        if (isset($array['save_ipn_logs'])) $this->setSaveIpnLogs($array['save_ipn_logs']);
        if (isset($array['dump_ipn'])) $this->setDumpIPN($array['dump_ipn']);
        if (isset($array['use_curl'])) $this->setUseCurl($array['use_curl']);
    }

}
