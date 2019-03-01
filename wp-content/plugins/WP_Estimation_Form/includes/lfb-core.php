<?php

if (!defined('ABSPATH'))
    exit;

class LFB_Core {

    /**
     * The single instance
     * @var    object
     * @access  private
     * @since    1.0.0
     */
    private static $_instance = null;

    /**
     * Settings class object
     * @var     object
     * @access  public
     * @since   1.0.0
     */
    public $settings = null;

    /**
     * The version number.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_version;

    /**
     * The token.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_token;

    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $file;

    /**
     * The main plugin directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $dir;

    /**
     * The plugin assets directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_dir;

    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_url;

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $templates_url;

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $script_suffix;

    /**
     * For menu instance
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $menu;

    /**
     * For template
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $plugin_slug;

    /*
     *  Current forms on page
     */
    public $currentForms;

    /*
     *  Is analytics loaded ?
     */
    public $checkAnalytics = false;

    /*
     *  Analytics ID
     */
    public $analyticsID = '';

    /*
     * Must load or not the js files ?
     */
    private $add_script;
    private $formToPayKey = "";
    private $formToPayID = 0;
    private $modeManageData = false;

    /**
     * Constructor function.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function __construct($file = '', $version = '1.6.0') {

        $this->_version = $version;
        $this->_token = 'lfb';
        $this->plugin_slug = 'lfb';
        $this->currentForms = array();
        $this->checkedSc = false;

        $this->file = $file;
        $this->dir = dirname($this->file);
        $this->assets_dir = trailingslashit($this->dir) . 'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));
        $this->tdgn_url = esc_url(trailingslashit(plugins_url('/includes/tdgn/', $this->file)));
        $this->templates_url = esc_url(trailingslashit(plugins_url('/templates/', $this->file)));
        $this->tmp_url = esc_url(trailingslashit(plugins_url('/export/', $this->file)));
        $upload_dir = wp_upload_dir();
        if (!is_dir($upload_dir['basedir'] . '/CostEstimationPayment')) {
            mkdir($upload_dir['basedir'] . '/CostEstimationPayment');
            chmod($upload_dir['basedir'] . '/CostEstimationPayment', 0747);
        }
        $this->uploads_dir = $upload_dir['basedir'] . '/CostEstimationPayment/';
        $this->uploads_url = $upload_dir['baseurl'] . '/CostEstimationPayment/';

        add_shortcode('estimation_form', array($this, 'wpt_shortcode'));
        add_action('wp_ajax_nopriv_lfb_cart_save', array($this, 'cart_save'));
        add_action('wp_ajax_lfb_cart_save', array($this, 'cart_save'));
        add_action('wp_ajax_nopriv_lfb_cartdd_save', array($this, 'cartdd_save'));
        add_action('wp_ajax_lfb_cartdd_save', array($this, 'cartdd_save'));
        add_action('wp_ajax_nopriv_send_email', array($this, 'send_email'));
        add_action('wp_ajax_send_email', array($this, 'send_email'));
        add_action('wp_ajax_nopriv_get_currentRef', array($this, 'get_currentRef'));
        add_action('wp_ajax_get_currentRef', array($this, 'get_currentRef'));
        add_action('wp_ajax_nopriv_lfb_upload_form', array($this, 'uploadFormFiles'));
        add_action('wp_ajax_lfb_upload_form', array($this, 'uploadFormFiles'));
        add_action('wp_ajax_nopriv_lfb_removeFile', array($this, 'removeFile'));
        add_action('wp_ajax_lfb_removeFile', array($this, 'removeFile'));
        add_action('wp_ajax_nopriv_lfb_sendCt', array($this, 'sendContact'));
        add_action('wp_ajax_lfb_sendCt', array($this, 'sendContact'));
        add_action('wp_ajax_nopriv_lfb_checkCaptcha', array($this, 'checkCaptcha'));
        add_action('wp_ajax_lfb_checkCaptcha', array($this, 'checkCaptcha'));
        add_action('wp_ajax_nopriv_lfb_applyCouponCode', array($this, 'applyCouponCode'));
        add_action('wp_ajax_lfb_applyCouponCode', array($this, 'applyCouponCode'));
        add_action('wp_ajax_nopriv_lfb_getFormToPay', array($this, 'getFormToPay'));
        add_action('wp_ajax_lfb_getFormToPay', array($this, 'getFormToPay'));
        add_action('wp_ajax_nopriv_lfb_validPayForm', array($this, 'validPayForm'));
        add_action('wp_ajax_lfb_validPayForm', array($this, 'validPayForm'));
        add_action('wp_ajax_nopriv_lfb_getBusyDates', array($this, 'getBusyDates'));
        add_action('wp_ajax_lfb_getBusyDates', array($this, 'getBusyDates'));
        add_action('wp_ajax_nopriv_lfb_loginManD', array($this, 'loginManD'));
        add_action('wp_ajax_lfb_loginManD', array($this, 'loginManD'));
        add_action('wp_ajax_nopriv_lfb_forgotPassManD', array($this, 'forgotPassManD'));
        add_action('wp_ajax_lfb_forgotPassManD', array($this, 'forgotPassManD'));
        add_action('wp_ajax_nopriv_lfb_downloadDataMan', array($this, 'downloadDataMan'));
        add_action('wp_ajax_lfb_downloadDataMan', array($this, 'downloadDataMan'));
        add_action('wp_ajax_nopriv_lfb_confirmModifyData', array($this, 'confirmModifyData'));
        add_action('wp_ajax_lfb_confirmModifyData', array($this, 'confirmModifyData'));
        add_action('wp_ajax_nopriv_lfb_confirmDeleteData', array($this, 'confirmDeleteData'));
        add_action('wp_ajax_lfb_confirmDeleteData', array($this, 'confirmDeleteData'));
        add_action('wp_ajax_nopriv_lfb_manSignOut', array($this, 'manSignOut'));
        add_action('wp_ajax_lfb_manSignOut', array($this, 'manSignOut'));

        add_action('woocommerce_add_order_item_meta', array($this, 'customDataToWooFinalOrder'), 1, 2);
        add_action('woocommerce_before_cart_item_quantity_zero', array($this, 'removeCustomDataWoo'), 1, 1);
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'), 10, 1);
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_styles'), 10, 1);
        if (isset($_GET['lfb_action']) && $_GET['lfb_action'] == 'preview') {
            add_filter('template_include', array($this, 'load_lfb_template'));
        }
        add_filter('the_content', array($this, 'preview_content'));
        add_action('wp', array($this, 'executeCron'));
        add_filter('the_posts', array($this, 'conditionally_add_scripts_and_styles'));
        add_action('plugins_loaded', array($this, 'init_localization'));
        add_filter('query_vars', array($this, 'lfb_query_vars'));
        add_action('generate_rewrite_rules', array($this, 'lfb_rewrite_rules'));
        add_action('parse_request', array($this, 'lfb_parse_request'));
    }

    // adds plugin variable to allowed url variables
    public function lfb_query_vars($vars) {
        $new_vars = array('EPFormsBuilder');
        $vars = $new_vars + $vars;
        return $vars;
    }

    public function lfb_rewrite_rules($wp_rewrite) {
        $new_rules = array('EPFormsBuilder/paypal' => 'index.php?EPFormsBuilder=paypal');
        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
        return $wp_rewrite->rules;
    }

    // execute url variables
    public function lfb_parse_request($wp) {
        if (array_key_exists('EPFormsBuilder', $wp->query_vars) && $wp->query_vars['EPFormsBuilder'] == 'paypal') {
            $this->cbb_proccess_paypal_ipn($wp);
        }
        if (array_key_exists('EPFormsBuilder', $wp->query_vars) && $wp->query_vars['EPFormsBuilder'] == 'payOrder') {
            $this->lfb_payForm($wp);
        }
        if (array_key_exists('EPFormsBuilder', $wp->query_vars) && $wp->query_vars['EPFormsBuilder'] == 'checkMyData') {
            $this->lfb_manageCustomerDatas($wp);
            return $wp;
        }
    }

    public function executeCron() {
        //if (isset($_GET['EPFormsBuilder']) && $_GET['EPFormsBuilder'] == 'executeCron') {
        global $wpdb;
        $settings = $this->getSettings();

        if (in_array(get_option('timezone_string'), timezone_identifiers_list())) {
            date_default_timezone_set(get_option('timezone_string'));
        }

        $table_nameR = $wpdb->prefix . "wpefc_calendarReminders";
        $remindersData = $wpdb->get_results("SELECT * FROM $table_nameR WHERE isSent=0");
        foreach ($remindersData as $reminder) {
            $table_nameE = $wpdb->prefix . "wpefc_calendarEvents";
            $event = $wpdb->get_results("SELECT * FROM $table_nameE WHERE id=" . $reminder->eventID . " LIMIT 1");
            if (count($event) > 0) {
                $event = $event[0];

                $startDate = new DateTime($event->startDate);
                $hours = $reminder->delayValue;
                if ($reminder->delayType == 'days') {
                    $hours *= 24;
                } else if ($reminder->delayType == 'weeks') {
                    $hours *= (24 * 7);
                } else if ($reminder->delayType == 'month') {
                    $hours *= (24 * 30);
                }

                $alertDate = new DateTime(date("Y-m-d H:i:s", strtotime($event->startDate)));
                $alertDate->modify('-' . $hours . ' hours');
                if ($alertDate->format('Y-m-d H:i:s') < date('Y-m-d H:i:s')) {
                    $content = $reminder->content;
                    $chkLog = false;
                    if ($event->orderID > 0) {
                        $table_nameL = $wpdb->prefix . 'wpefc_logs';
                        $log = $wpdb->get_results('SELECT * FROM ' . $table_nameL . ' WHERE id=' . $event->orderID . ' LIMIT 1');
                        if (count($log) > 0) {
                            $chkLog = true;
                            $log = $log[0];
                            $log->email = $this->stringDecode($log->email, $settings->encryptDB);
                            $content = str_replace('[ref]', $log->ref, $content);
                            $content = str_replace('[customer_email]', $log->email, $content);
                        }
                    }
                    if (!$chkLog) {
                        $content = str_replace('[ref]', '', $content);
                        $content = str_replace('[customer_email]', '', $content);
                    }
                    $content = str_replace('[customerAddress]', $this->stringDecode($event->customerAddress, $settings->encryptDB), $content);
                    $content = str_replace('[customerEmail]', $this->stringDecode($event->customerEmail, $settings->encryptDB), $content);
                    if ($event->fullDay == 0) {
                        $content = str_replace('[time]', date(get_option('time_format'), strtotime($event->startDate)), $content);
                    } else {
                        $content = str_replace('[time]', '', $content);
                    }
                    $content = str_replace('[date]', date(get_option('date_format'), strtotime($event->startDate)), $content);

                    add_filter('wp_mail_content_type', create_function('', 'return "text/html"; '));

                    if (wp_mail($reminder->email, $reminder->title, $content)) {
                        $wpdb->update($table_nameR, array('isSent' => 1), array('id' => $reminder->id));
                    }
                }
            }
        }
        //  }
    }

    public function lfb_payForm($wp) {
        global $wpdb;
        $this->formToPayKey = sanitize_text_field($_GET['h']);
        $table_name = $wpdb->prefix . "wpefc_logs";
        $logReq = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE paymentKey='%s' LIMIT 1", $this->formToPayKey));
        if (count($logReq) > 0) {
            $log = $logReq[0];
            $this->formToPayID = $log->formID;
        }
    }

    public function lfb_manageCustomerDatas($wp) {
        global $wpdb;
        $this->modeManageData = true;
    }

    public function validPayForm() {

        global $wpdb;
        $settings = $this->getSettings();
        $formID = sanitize_text_field($_POST['formID']);
        $orderKey = sanitize_text_field($_POST['orderKey']);
        $stripeToken = sanitize_text_field($_POST['stripeToken']);
        $stripeTokenB = sanitize_text_field($_POST['stripeTokenB']);

        $table_name = $wpdb->prefix . "wpefc_forms";
        $formReq = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE id='%s' LIMIT 1", $formID));
        if (count($formReq) > 0) {
            $form = $formReq[0];
            $table_name = $wpdb->prefix . "wpefc_logs";
            $logReq = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE paymentKey='%s' LIMIT 1", $orderKey));
            if (count($logReq) > 0) {
                $log = $logReq[0];

                $chkStripe = false;
                $useStripe = false;
                if ($stripeToken != "" && $form->use_stripe) {
                    $useStripe = true;
                    $chkStripe = $this->doStripePayment($log->id, $stripeToken, $stripeTokenB);
                }
            }
        }
        die();
    }

    public function getFormToPay() {
        global $wpdb;
        $logKey = sanitize_text_field($_POST['key']);
        $table_name = $wpdb->prefix . "wpefc_logs";
        $logReq = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE paymentKey='%s' LIMIT 1", $logKey));
        if (count($logReq) > 0) {
            $log = $logReq[0];
            $settings = $this->getSettings();

            $log->email = $this->stringDecode($log->email, $settings->encryptDB);
            $log->content = $this->stringDecode($log->content, $settings->encryptDB);
            $log->contentUser = $this->stringDecode($log->contentUser, $settings->encryptDB);

            if ($log->paid == 0) {
                $this->currentForms[] = $log->formID;


                $table_name = $wpdb->prefix . "wpefc_forms";
                $formReq = $wpdb->get_results("SELECT * FROM $table_name WHERE id=" . $log->formID . " LIMIT 1");
                if (count($formReq) > 0) {
                    $form = $formReq[0];

                    if ($form->use_stripe || ($form->use_paypal && $form->paypal_useIpn)) {

                        $this->options_custom_styles();
                        $txt_orderType = $form->txt_invoice;
                        if (!$order->paid) {
                            $txt_orderType = $form->txt_quotation;
                        }

                        $log->contentUser = str_replace("[order_type]", $txt_orderType, $log->contentUser);
                        $log->contentUser = str_replace("[payment_link]", "", $log->contentUser);


                        $response .= '<div style="text-align: center;">';
                        if ($form->use_stripe) {

                            $response .= '<form id="lfb_stripeForm" action="" data-title="' . $form->title . '" method="post">';

                            $response .= '
                    <div class="form-group">
                    <label>
                      <span>' . $form->stripe_label_creditCard . '</span>
                    </label>
                    ';
                            if (!$form->inlineLabels) {
                                $response .= '<br/>';
                            }
                            $response .= '<input type="text" size="20" data-stripe="number" class="form-control">
                  </div>
                  <span class="payment-errors"></span>
                  <div class="form-group">
                    <label>
                      <span>' . $form->stripe_label_expiration . ' (MM/YY)</span>
                    </label>
                    ';
                            if (!$form->inlineLabels) {
                                $response .= '<br/>';
                            }
                            $response .= '<input type="text" size="2" data-stripe="exp_month" class="form-control" style="display: inline-block;margin-right: 8px; width: 60px;">
                    <span style="font-size: 24px;"> / </span>
                    <input type="text" size="2" data-stripe="exp_year" class="form-control" style="display: inline-block;margin-left: 8px; width: 60px;">
                  </div>

                  <div class="form-group">
                    <label>
                      <span>' . $form->stripe_label_cvc . '</span>
                    </label>
                    ';
                            if (!$form->inlineLabels) {
                                $response .= '<br/>';
                            }
                            $response .= '<input type="text" size="4" data-stripe="cvc"  class="form-control" style="width: 110px;">
                  </div>
                  ';
                            $response .= '<p style="margin-top: 38px; margin-bottom: -28px;" class="lfb_btnNextContainer"><input type="submit" value="' . $form->last_btn . '"  id="wpe_btnOrderStripe"  class="btn btn-wide btn-primary">';

                            $response .= '</p>';
                            $response .= '</form>';
                        } else if ($form->use_paypal) {
                            if ($form->paypal_useSandbox == 1) {
                                $response .= '<form id="wtmt_paypalForm" action="https://www.sandbox.paypal.com/cgi-bin/webscr" data-useipn="1" method="post">';
                            } else {
                                $response .= '<form id="wtmt_paypalForm" action="https://www.paypal.com/cgi-bin/webscr"  data-useipn="1" method="post">';
                            }

                            $response .= '<p style="" class="text-center lfb_btnNextContainer">'
                                    . '<a href="javascript:" id="btnOrderPaypal" class="btn btn-wide btn-primary">' . $finalIcon . $form->last_btn . '</a>';

                            $response .= '</p>
                            <input type="submit" style="display: none;" name="submit"/>
                            <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">';
                            if ($log->totalSubscription > 0) {
                                $response .= '<input type="hidden" name="cmd" value="_xclick-subscriptions">
                            <input type="hidden" name="no_note" value="1">
                            <input type="hidden" name="src" value="1">
                            <input type="hidden" name="a3" value="15.00">
                            <input type="hidden" name="p3" value="' . $form->paypal_subsFrequency . '">
                            <input type="hidden" name="t3" value="' . $form->paypal_subsFrequencyType . '">
                            <input type="hidden" name="bn" value="PP-SubscriptionsBF:btn_subscribeCC_LG.gif:NonHostedGuest">';
                            } else {
                                $response .= '<input type="hidden" name="cmd" value="_xclick">
                            <input type="hidden" name="amount" value="1">';
                            }
                            $lang = '';
                            if ($form->paypal_languagePayment != "") {
                                $lang = '<input type="hidden" name="lc" value="' . $form->paypal_languagePayment . '"><input type="hidden" name="country" value="' . $form->paypal_languagePayment . '">';
                            }
                            $response .= '<input type="hidden" name="business" value="' . $form->paypal_email . '">
                            <input type="hidden" name="business_cs_email" value="' . $form->paypal_email . '">
                            <input type="hidden" name="item_name" value="' . $form->title . '">
                            <input type="hidden" name="item_number" value="A00001">
                            <input type="hidden" name="charset" value="utf-8">
                            <input type="hidden" name="no_shipping" value="1">
                            <input type="hidden" name="cn" value="Message">
                            <input type="hidden" name="custom" value="Form content">
                            <input type="hidden" name="currency_code" value="' . $form->paypal_currency . '">
                            <input type="hidden" name="return" value="' . $form->close_url . '">
                                ' . $lang . '
                        </form>';
                        }
                        $response .= '</div>';

                        $log->contentUser .= $response;
                        echo $log->contentUser;
                    }
                }
            }
        }
        die();
    }

    public function cbb_proccess_paypal_ipn($wp) {
        global $wpdb;
        require_once ('IpnListener.php');
        if (isset($_POST['item_number'])) {
            $item_number = sanitize_text_field($_POST['item_number']);
            $table_name = $wpdb->prefix . "wpefc_logs";


            $logReq = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE ref='%s' LIMIT 1", $item_number));
            if (count($logReq) > 0) {
                $log = $logReq[0];

                $table_name = $wpdb->prefix . "wpefc_forms";
                $formReq = $wpdb->get_results("SELECT * FROM $table_name WHERE id=" . $log->formID . " LIMIT 1");
                $form = $formReq[0];
                $listener = new IpnListener();
                if ($form->paypal_useSandbox) {
                    $listener->use_sandbox = true;
                }
                if ($verified = $listener->processIpn()) {
                    
                } else {

                    $transactionData = $listener->getPostData();
                    if ($_POST['payment_status'] == 'Completed') {

                        $table_name = $wpdb->prefix . "wpefc_logs";
                        $wpdb->update($table_name, array('paid' => 1), array('ref' => $item_number));
                        if (!$log->checked) {
                            $this->sendOrderEmail($item_number, $log->formID);
                        }
                    }
                }
            }
        }
    }

    public function getBusyDates() {
        global $wpdb;
        $formID = sanitize_text_field($_POST['formID']);
        $calendarIDs = $_POST['calendarsIDs'];

        $rep = new stdClass();
        $rep->calendars = array();


        foreach ($_POST['calendarsIDs'] as $calendarID) {
            $calendar = new stdClass();
            $calendar->id = $calendarID;
            $table_name = $wpdb->prefix . "wpefc_calendarEvents";
            $calendar->events = $wpdb->get_results($wpdb->prepare("SELECT calendarID,isBusy,startDate,endDate,fullDay FROM $table_name WHERE calendarID=%s AND isBusy=1", $calendarID));

            $rep->calendars[] = $calendar;
        }
        echo json_encode($rep);
        die();
    }

    private function getKeyS() {
        if (get_option('lfbK') !== false) {
            $key = get_option('lfbK');
        } else {
            $key = md5(uniqid(rand(), true));
            update_option('lfbK', $key);
        }
        return $key;
    }

    public function stringEncode($value, $enableCrypt) {
        if (!$enableCrypt) {
            $text = $value;
        } else {
            if ($value != "") {
                $iv = openssl_random_pseudo_bytes(16);
                $text = openssl_encrypt($value, 'aes128', $this->getKeyS(), null, $iv);
                $text = $this->safe_b64encode($text . '::' . $iv);
            } else {
                $text = "";
            }
        }
        return $text;
    }

    public function stringDecode($value, $enableCrypt) {
        if (!$enableCrypt) {
            $text = $value;
        } else {
            if ($value != "") {
                $encrypted_data = "";
                $iv = "";
                list($encrypted_data, $iv) = explode('::', $this->safe_b64decode($value), 2);
                $text = openssl_decrypt($encrypted_data, 'aes128', $this->getKeyS(), null, $iv);
            } else {
                $text = "";
            }
        }
        return $text;
    }

    public function safe_b64encode($string) {
        $data = base64_encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
        return $data;
    }

    public function safe_b64decode($string) {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    public function loginManD() {
        global $wpdb;
        $settings = $this->getSettings();
        $email = sanitize_text_field($_POST['email']);
        $pass = sanitize_text_field($_POST['pass']);
        $table_name = $wpdb->prefix . "wpefc_customers";
        $customersData = $wpdb->get_results("SELECT * FROM $table_name");
        foreach ($customersData as $customerData) {
            if ($this->stringDecode($customerData->email, $settings->encryptDB) == $email) {
                if ($this->stringDecode($customerData->password, true) == $pass) {
                    $rep = 1;
                    session_start();
                    $_SESSION['lfb_loginMan'] = $customerData->id;
                }
            }
        }

        echo $rep;
        die();
    }

    public function forgotPassManD() {
        global $wpdb;
        $settings = $this->getSettings();
        $email = sanitize_text_field($_POST['email']);
        $table_name = $wpdb->prefix . "wpefc_customers";

        $chkEmail = false;
        $rep = 0;

        $customersData = $wpdb->get_results("SELECT * FROM $table_name");
        foreach ($customersData as $customerData) {
            if ($this->stringDecode($customerData->email, $settings->encryptDB) == $email) {
                $pass = $this->generatePassword();
                $wpdb->update($table_name, array('password' => $this->stringEncode($pass, true)), array('id' => $customerData->id));
                $chkEmail = true;
            }
        }
        if ($chkEmail) {
            $customersDataUrl = get_site_url() . '/?EPFormsBuilder=checkMyData&e=' . $email;

            $txtMail = ($settings->txtCustomersDataForgotPassMail);
            $txtMail = str_replace('[url]', $customersDataUrl, $txtMail);
            $txtMail = str_replace("[password]", $pass, $txtMail);
            if (wp_mail($email, $settings->txtCustomersDataForgotMailSubject, $txtMail)) {
                
            }
            $rep = 1;
        }
        echo $rep;
        die();
    }

    public function manSignOut() {
        session_start();
        session_destroy();
        die();
    }

    public function confirmModifyData() {
        session_start();
        if (isset($_SESSION['lfb_loginMan']) && isset($_POST['details'])) {
            global $wpdb;
            $details = sanitize_text_field($_POST['details']);
            $custID = $_SESSION['lfb_loginMan'];
            $table_name = $wpdb->prefix . "wpefc_customers";
            $customerData = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE id=%s LIMIT 1", $custID));
            if (count($customerData) > 0) {
                $settings = $this->getSettings();
                $customerData = $customerData[0];
                $customerData->email = $this->stringDecode($customerData->email, $settings->encryptDB);
                $settings = $this->getSettings();

                $mailContent = '<p>' . $settings->txtCustomersDataModifyMailSubject . ' <strong>' . $customerData->email . '</strong> :</p>';
                $mailContent .= '<p>' . nl2br($details) . '</p>';
                $linkAdmin = get_site_url() . '/wp-admin/admin.php?page=lfb_menu';
                $mailContent .= '<p><a href="' . $linkAdmin . '">' . $linkAdmin . '</a></p>';

                add_filter('wp_mail_content_type', create_function('', 'return "text/html"; '));
                if (wp_mail($settings->customerDataAdminEmail, $settings->txtCustomersDataModifyMailSubject, $mailContent)) {
                    
                }
            }
        }
        die();
    }

    public function confirmDeleteData() {
        session_start();
        if (isset($_SESSION['lfb_loginMan'])) {
            global $wpdb;
            $custID = $_SESSION['lfb_loginMan'];
            $table_name = $wpdb->prefix . "wpefc_customers";
            $customerData = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE id=%s LIMIT 1", $custID));
            if (count($customerData) > 0) {
                $settings = $this->getSettings();
                $customerData = $customerData[0];
                $customerData->email = $this->stringDecode($customerData->email, $settings->encryptDB);

                $mailContent = '<p>' . $settings->txtCustomersDataDeleteMailSubject . ' : <strong>' . $customerData->email . '</strong></p>';
                $linkAdmin = get_site_url() . '/wp-admin/admin.php?page=lfb_menu';
                $mailContent .= '<p><a href="' . $linkAdmin . '">' . $linkAdmin . '</a></p>';
                $headers = "";
                add_filter('wp_mail_content_type', create_function('', 'return "text/html"; '));
                if (wp_mail($settings->customerDataAdminEmail, $settings->txtCustomersDataDeleteMailSubject, $mailContent)) {
                    
                }
            }
        }
        die();
    }

    public function downloadDataMan() {
        session_start();
        if (isset($_SESSION['lfb_loginMan'])) {
            global $wpdb;
            $jsonData = new stdClass();
            $custID = $_SESSION['lfb_loginMan'];
            $table_name = $wpdb->prefix . "wpefc_customers";
            $customerData = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE id=%s LIMIT 1", $custID));
            if (count($customerData) > 0) {
                $settings = $this->getSettings();
                $customerData = $customerData[0];
                $jsonData->email = $this->stringDecode($customerData->email, $settings->encryptDB);
                $jsonData->orders = array();

                $table_nameL = $wpdb->prefix . "wpefc_logs";
                $logs = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_nameL WHERE customerID=%s ORDER BY id ASC", $custID));
                foreach ($logs as $log) {
                    $order = new stdClass();
                    $order->date = $log->dateLog;
                    $order->reference = $log->ref;
                    $order->content = str_replace('[n]', '\n', $this->stringDecode($log->contentTxt, $settings->encryptDB));
                    $order->firstName = $this->stringDecode($log->firstName, $settings->encryptDB);
                    $order->lastName = $this->stringDecode($log->lastName, $settings->encryptDB);
                    $order->phone = $this->stringDecode($log->phone, $settings->encryptDB);
                    $order->address = $this->stringDecode($log->address, $settings->encryptDB);
                    $order->city = $this->stringDecode($log->city, $settings->encryptDB);
                    $order->country = $this->stringDecode($log->country, $settings->encryptDB);
                    $order->city = $this->stringDecode($log->city, $settings->encryptDB);
                    $order->state = $this->stringDecode($log->state, $settings->encryptDB);
                    $order->zip = $this->stringDecode($log->zip, $settings->encryptDB);
                    $order->totalPrice = $log->totalPrice;
                    $order->totalSubscription = $log->totalSubscription;
                    $jsonData->orders[] = $order;
                }
            }
            echo json_encode($jsonData);
        }
        die();
    }

    /**
     * Load popup template.
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function load_lfb_template($template) {
        $file = plugin_dir_path(__FILE__) . '../templates/lfb-preview.php';
        if (file_exists($file)) {
            return $file;
        }
    }

    /*
     * Plugin init localization
     */

    public function init_localization() {
        $moFiles = scandir(trailingslashit($this->dir) . 'languages/');
        if (get_locale() == "") {
            load_textdomain('lfb', trailingslashit($this->dir) . 'languages/WP_Estimation_Form.mo');
            return;
        }
        foreach ($moFiles as $moFile) {
            if (strlen($moFile) > 3 && substr($moFile, -3) == '.mo' && strpos($moFile, get_locale()) > -1) {
                load_textdomain('lfb', trailingslashit($this->dir) . 'languages/' . $moFile);
            }
        }
    }

    public function preview_content($content) {
        if (isset($_GET['lfb_action']) && $_GET['lfb_action'] == 'preview') {
            $content = do_shortcode('[estimation_form form_id="' . sanitize_text_field($_GET['form']) . '" fullscreen="true"]');
        }
        return $content;
    }

    public function frontend_enqueue_styles($hook = '') {
        $settings = $this->getSettings();
        if (isset($_GET['lfb_action']) && $_GET['lfb_action'] == 'preview') {
            global $wp_styles;
            wp_register_style($this->_token . '-designerFrontend', esc_url($this->assets_url) . 'css/lfb_formDesigner_frontend.min.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-designerFrontend');
        }
        if ($this->formToPayKey != "" || $this->modeManageData) {

            wp_register_style($this->_token . '-reset', esc_url($this->assets_url) . 'css/reset.min.css', array(), $this->_version);
            wp_register_style($this->_token . '-bootstrap', esc_url($this->assets_url) . 'css/bootstrap.min.css', array(), $this->_version);
            wp_register_style($this->_token . '-bootstrap-select', esc_url($this->assets_url) . 'css/bootstrap-select.min.css', array(), $this->_version);
            wp_register_style($this->_token . '-flat-ui', esc_url($this->assets_url) . 'css/flat-ui_frontend.min.css', array(), $this->_version);
            wp_register_style($this->_token . '-dropzone', esc_url($this->assets_url) . 'css/dropzone.min.css', array(), $this->_version);
            wp_register_style($this->_token . '-colpick', esc_url($this->assets_url) . 'css/colpick.min.css', array(), $this->_version);
            wp_register_style($this->_token . '-fontawesome', esc_url($this->assets_url) . 'css/font-awesome.min.css', array(), $this->_version);
            wp_register_style($this->_token . '-estimationpopup', esc_url($this->assets_url) . 'css/lfb_forms.min.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-reset');
            wp_enqueue_style($this->_token . '-bootstrap');
            wp_enqueue_style($this->_token . '-bootstrap-select');
            wp_enqueue_style($this->_token . '-flat-ui');
            wp_enqueue_style($this->_token . '-dropzone');
            wp_enqueue_style($this->_token . '-colpick');
            wp_enqueue_style($this->_token . '-fontawesome');
            wp_enqueue_style($this->_token . '-estimationpopup');
        }

        if ($this->modeManageData) {
            wp_register_style($this->_token . '-manageDatas', esc_url($this->assets_url) . 'css/lfb_manageDatas.min.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-manageDatas');
        }
    }

    private function jsonRemoveUnicodeSequences($struct) {
        return json_encode($struct, JSON_UNESCAPED_UNICODE);
        //return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode($struct));
    }

    public function conditionally_add_scripts_and_styles($posts) {
        if (empty($posts))
            return $posts;
        global $wpdb;
        if (!$this->checkedSc) {
            $shortcode_found = false;
            $form_id = 0;
            $this->currentForms[] = array();

            if (!isset($_GET['cornerstone_preview'])) {
                foreach ($posts as $post) {
                    $lastPos = 0;
                    while (($lastPos = strpos($post->post_content, '[estimation_form', $lastPos)) !== false) {
                        $shortcode_found = true;
                        $this->checkedSc = true;
                        $pos_start = strpos($post->post_content, 'form_id="', $lastPos + 16) + 9;
                        // $pos_end=strpos($post->post_content, '"', strpos($post->post_content, 'form_id="', strpos($post->post_content, '[estimation_form') + 16) + 10)-1;
                        $pos_end = strpos($post->post_content, '"', $pos_start);
                        $form_id = substr($post->post_content, $pos_start, $pos_end - $pos_start);
                        if ($form_id && $form_id > 0 && !is_array($form_id)) {
                            $this->currentForms[] = $form_id;
                        } else {
                            $table_name = $wpdb->prefix . "wpefc_forms";
                            $formReq = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id ASC LIMIT 1");
                            if (count($formReq) > 0) {
                                $form = $formReq[0];
                                if (!in_array($form->id, $this->currentForms)) {
                                    $this->currentForms[] = $form->id;
                                }
                            }
                        }
                        $lastPos = $lastPos + 16;
                    }
                }
            }
            if (isset($_GET['cornerstone_preview'])) {


                wp_register_style($this->_token . '-reset', esc_url($this->assets_url) . 'css/reset.min.css', array(), $this->_version);
                wp_register_style($this->_token . '-bootstrap', esc_url($this->assets_url) . 'css/bootstrap.min.css', array(), $this->_version);
                wp_register_style($this->_token . '-bootstrap-select', esc_url($this->assets_url) . 'css/bootstrap-select.min.css', array(), $this->_version);
                wp_register_style($this->_token . '-flat-ui', esc_url($this->assets_url) . 'css/flat-ui_frontend.min.css', array(), $this->_version);
                wp_register_style($this->_token . '-dropzone', esc_url($this->assets_url) . 'css/dropzone.min.css', array(), $this->_version);
                wp_register_style($this->_token . '-colpick', esc_url($this->assets_url) . 'css/colpick.min.css', array(), $this->_version);
                wp_register_style($this->_token . '-fontawesome', esc_url($this->assets_url) . 'css/font-awesome.min.css', array(), $this->_version);
                wp_register_style($this->_token . '-estimationpopup', esc_url($this->assets_url) . 'css/lfb_forms.min.css', array(), $this->_version);
                wp_enqueue_style($this->_token . '-reset');
                wp_enqueue_style($this->_token . '-bootstrap');
                wp_enqueue_style($this->_token . '-bootstrap-select');
                wp_enqueue_style($this->_token . '-flat-ui');
                wp_enqueue_style($this->_token . '-dropzone');
                wp_enqueue_style($this->_token . '-colpick');
                wp_enqueue_style($this->_token . '-fontawesome');
                wp_enqueue_style($this->_token . '-estimationpopup');
            } else if (!$shortcode_found && defined('CNR_DEV')) {
                $table_name = $wpdb->prefix . "wpefc_forms";
                $formReq = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id ASC");
                if (count($formReq) > 0) {
                    $shortcode_found = true;
                    $this->checkedSc = true;
                    foreach ($formReq as $form) {
                        if (!in_array($form->id, $this->currentForms)) {
                            $this->currentForms[] = $form->id;
                        }
                    }
                }
            }

            //loadAllPages
            $table_name = $wpdb->prefix . "wpefc_forms";
            $formReq = $wpdb->get_results("SELECT * FROM $table_name WHERE loadAllPages=1 ORDER BY id ASC");
            if (count($formReq) > 0) {
                $shortcode_found = true;
                $this->checkedSc = true;
                foreach ($formReq as $form) {
                    if (!in_array($form->id, $this->currentForms)) {
                        $this->currentForms[] = $form->id;
                    }
                }
            }

            if (isset($_GET['lfb_action']) && $_GET['lfb_action'] == 'preview') {
                $shortcode_found = true;
                if (!in_array(sanitize_text_field($_GET['form']), $this->currentForms)) {
                    $this->currentForms[] = sanitize_text_field($_GET['form']);
                }
            }

            if ($shortcode_found && count($this->currentForms) > 0 && !is_admin()) {
                $settings = $this->getSettings();

                // styles                             
                wp_register_style($this->_token . '-frontend-libs', esc_url($this->assets_url) . 'css/lfb_frontendPackedLibs.min.css', array(), $this->_version);
                wp_enqueue_style($this->_token . '-frontend-libs');
                wp_register_style($this->_token . '-estimationpopup', esc_url($this->assets_url) . 'css/lfb_forms.min.css', array($this->_token . '-frontend-libs'), $this->_version);
                wp_enqueue_style($this->_token . '-estimationpopup');

                // scripts            
                wp_register_script($this->_token . '-frontend-libs', esc_url($this->assets_url) . 'js/lfb_frontendPackedLibs.min.js', array("jquery-ui-core", "jquery-ui-tooltip", "jquery-ui-slider", "jquery-ui-position", "jquery-ui-datepicker"), $this->_version);
                wp_enqueue_script($this->_token . '-frontend-libs');
                wp_register_script($this->_token . '-estimationpopup', esc_url($this->assets_url) . 'js/lfb_form.min.js', array($this->_token . '-frontend-libs'), $this->_version);
                wp_enqueue_script($this->_token . '-estimationpopup');

                include_once(ABSPATH . 'wp-admin/includes/plugin.php');
                $js_data = array();
                $formsDone = array();

                foreach ($this->currentForms as $formID) {

                    if ($formID > 0 && !is_array($formID)) {
                        if (!in_array($formID, $formsDone)) {
                            $formsDone[] = $formID;
                            $form = $this->getFormDatas($formID);

                            if ($form) {

                                if ($form->gmap_key != "") {
                                    $chkMap = false;

                                    $table_name = $wpdb->prefix . "wpefc_items";
                                    $itemsQt = $wpdb->get_results("SELECT * FROM $table_name WHERE formID=$formID AND useDistanceAsQt=1 ORDER BY id ASC");
                                    if (count($itemsQt) > 0) {
                                        $chkMap = true;
                                    }
                                    if (!$chkMap) {
                                        $itemsCalcul = $wpdb->get_results("SELECT * FROM $table_name WHERE useCalculation=1 AND formID=$formID ORDER BY id ASC");
                                        foreach ($itemsCalcul as $itemCalcul) {
                                            $lastPos = 0;
                                            while (($lastPos = strpos($itemCalcul->calculation, 'distance_', $lastPos)) !== false) {
                                                $chkMap = true;
                                                $lastPos += 9;
                                            }
                                        }
                                    }
                                    if (!$chkMap) {
                                        $itemsCalcul = $wpdb->get_results("SELECT * FROM $table_name WHERE useCalculationQt=1 AND formID=$formID ORDER BY id ASC");
                                        foreach ($itemsCalcul as $itemCalcul) {
                                            $lastPos = 0;
                                            while (($lastPos = strpos($itemCalcul->calculationQt, 'distance_', $lastPos)) !== false) {
                                                $chkMap = true;
                                                $lastPos += 9;
                                            }
                                        }
                                    }

                                    $libPlace = '';
                                    $itemsTxt = $wpdb->get_results("SELECT * FROM $table_name WHERE formID=$formID AND type='textfield' AND autocomplete=1 ORDER BY id ASC");
                                    if (count($itemsTxt) > 0) {
                                        $chkMap = true;
                                        $libPlace = '&libraries=places';
                                    }

                                    if ($chkMap) {
                                        wp_register_script($this->_token . '-gmap', '//maps.googleapis.com/maps/api/js?key=' . $form->gmap_key . $libPlace, array());
                                        wp_enqueue_script($this->_token . '-gmap');
                                    }
                                }


                                if ($form->analyticsID != "") {
                                    $this->analyticsID = $form->analyticsID;
                                    add_action('wp_footer', array($this, 'add_googleanalytics'));
                                }
                                if (is_plugin_active('gravityforms/gravityforms.php') && $form->gravityFormID > 0 && !$this->is_enqueued_script($this->_token . '-estimationpopup')) {
                                    gravity_form_enqueue_scripts($form->gravityFormID, true);
                                    if (is_plugin_active('gravityformssignature/signature.php')) {
                                        wp_register_script('gforms_signature', esc_url($this->assets_url) . '../../gravityformssignature/super_signature/ss.js', array("gform_gravityforms"), $this->_version);
                                        wp_enqueue_script('gforms_signature');
                                    }
                                }
                                if (!$form->colorA || $form->colorA == "") {
                                    $form->colorA = $settings->colorA;
                                }
                                if ($form->use_stripe) {
                                    wp_enqueue_script($this->_token . '-stripe', 'https://js.stripe.com/v2/', true, 3);
                                }


                                if ($form->datepickerLang != "" && is_file($this->assets_dir . '/js/datepickerLocale/bootstrap-datetimepicker.' . $form->datepickerLang . '.js')) {

                                    wp_register_script($this->_token . '-datetimepicker-locale-' . $form->datepickerLang, esc_url($this->assets_url) . 'js/datepickerLocale/bootstrap-datetimepicker.' . $form->datepickerLang . '.js', array(), $this->_version);
                                    wp_enqueue_script($this->_token . '-datetimepicker-locale-' . $form->datepickerLang);
                                }

                                $table_name = $wpdb->prefix . "wpefc_links";
                                $links = $wpdb->get_results("SELECT * FROM $table_name WHERE formID=" . $formID);

                                $table_name = $wpdb->prefix . "wpefc_redirConditions";
                                $redirections = $wpdb->get_results("SELECT * FROM $table_name WHERE formID=" . $formID);

                                if ($form->decimalsSeparator == "") {
                                    $form->decimalsSeparator = '.';
                                }
                                $usePdf = 0;
                                if ($form->sendPdfCustomer || $form->sendPdfAdmin) {
                                    $usePdf = 1;
                                }
                                $form->fixedToPay = $form->paypal_fixedToPay;
                                $form->payMode = $form->paypal_payMode;
                                if ($form->use_stripe) {
                                    $form->percentToPay = $form->stripe_percentToPay;
                                    $form->fixedToPay = $form->stripe_fixedToPay;
                                    $form->payMode = $form->stripe_payMode;
                                }
                                $js_data[] = array(
                                    'currentRef' => 0,
                                    'ajaxurl' => admin_url('admin-ajax.php'),
                                    'initialPrice' => $form->initial_price,
                                    'max_price' => $form->max_price,
                                    'percentToPay' => $form->percentToPay,
                                    'fixedToPay' => $form->fixedToPay,
                                    'payMode' => $form->payMode,
                                    'currency' => $form->currency,
                                    'currencyPosition' => $form->currencyPosition,
                                    'intro_enabled' => $form->intro_enabled,
                                    'save_to_cart' => $form->save_to_cart,
                                    'save_to_cart_edd' => $form->save_to_cart_edd,
                                    'colorA' => $form->colorA,
                                    'animationsSpeed' => $form->animationsSpeed,
                                    'email_toUser' => $form->email_toUser,
                                    'showSteps' => $form->showSteps,
                                    'formID' => $form->id,
                                    'gravityFormID' => $form->gravityFormID,
                                    'showInitialPrice' => $form->show_initialPrice,
                                    'disableTipMobile' => $form->disableTipMobile,
                                    'legalNoticeEnable' => $form->legalNoticeEnable,
                                    'links' => $links,
                                    'close_url' => $form->close_url,
                                    'redirections' => $redirections,
                                    'useRedirectionConditions' => $form->useRedirectionConditions,
                                    'usePdf' => $usePdf,
                                    'txt_yes' => __('Yes', 'lfb'),
                                    'txt_no' => __('No', 'lfb'),
                                    'txt_lastBtn' => $form->last_btn,
                                    'txt_btnStep' => $form->btn_step,
                                    'dateFormat' => stripslashes($this->dateFormatToDatePickerFormat(get_option('date_format'))),
                                    'datePickerLanguage' => $form->datepickerLang,
                                    'thousandsSeparator' => $form->thousandsSeparator,
                                    'decimalsSeparator' => $form->decimalsSeparator,
                                    'millionSeparator' => $form->millionSeparator,
                                    'billionsSeparator' => $form->billionsSeparator,
                                    'summary_hideQt' => $form->summary_hideQt,
                                    'summary_hideZero' => $form->summary_hideZero,
                                    'summary_hidePrices' => $form->summary_hidePrices,
                                    'groupAutoClick' => $form->groupAutoClick,
                                    'filesUpload_text' => $form->filesUpload_text,
                                    'filesUploadSize_text' => $form->filesUploadSize_text,
                                    'filesUploadType_text' => $form->filesUploadType_text,
                                    'filesUploadLimit_text' => $form->filesUploadLimit_text,
                                    'sendContactASAP' => $form->sendContactASAP,
                                    'showTotalBottom' => $form->showTotalBottom,
                                    'stripePubKey' => $form->stripe_publishKey,
                                    'scrollTopMargin' => $form->scrollTopMargin,
                                    'redirectionDelay' => $form->redirectionDelay,
                                    'gmap_key' => $form->gmap_key,
                                    'txtDistanceError' => $form->txtDistanceError,
                                    'captchaUrl' => esc_url(trailingslashit(plugins_url('/includes/captcha/', $this->file))) . 'get_captcha.php',
                                    'summary_noDecimals' => $form->summary_noDecimals,
                                    'scrollTopPage' => $form->scrollTopPage,
                                    'disableDropdowns' => $form->disableDropdowns,
                                    'imgIconStyle' => $form->imgIconStyle,
                                    'summary_hideFinalStep' => $form->summary_hideFinalStep,
                                    'timeModeAM' => $form->timeModeAM,
                                    'enableShineFxBtn' => $form->enableShineFxBtn,
                                    'summary_showAllPricesEmail' => $form->summary_showAllPricesEmail,
                                    'imgTitlesStyle' => $form->imgTitlesStyle,
                                    'lastS' => $form->lastSave,
                                    'emptyWooCart'=>$form->emptyWooCart
                                );
                            }
                        }
                    }
                }
                wp_localize_script($this->_token . '-estimationpopup', 'wpe_forms', $js_data);

                add_action('wp_head', array($this, 'options_custom_styles'));
            }
        }

        return $posts;
    }

    private function is_enqueued_script($script) {
        return isset($GLOBALS['wp_scripts']->registered[$script]);
    }

    public function dateFormatToDatePickerFormat($dateFormat) {
        $chars = array(
            'd' => 'dd', 'j' => 'd', 'l' => 'DD', 'D' => 'D',
            'm' => 'mm', 'n' => 'm', 'F' => 'MM', 'M' => 'M',
            'Y' => 'yyyy', 'y' => 'y',
        );
        if (strpos('F', $dateFormat) > -1) {
            $rep = 'YYYY-MM-DD';
        } else {
            $rep = strtr((string) $dateFormat, $chars);
        }
        return $rep;
    }

    public function timeFormatToDatePickerFormat($timeFormat) {
        $chars = array(
            'G' => 'H', 'g' => 'H', 'h' => 'hh', 'H' => 'hh',
            'a' => 'p', 'A' => 'P', 'i' => 'ii'
        );
        if ($timeFormat == '') {
            $rep = 'hh:mm';
        } else {
            $rep = strtr((string) $timeFormat, $chars);
        }
        return $rep;
    }

    public function add_googleanalytics() {
        if (!$this->checkAnalytics) {
            $this->checkAnalytics = true;
            echo "<script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
            ga('create', '" . $this->analyticsID . "', 'auto');
            ga('send', 'pageview');
          </script>";
        }
    }

    public function checkCaptcha() {
        $captcha = sanitize_text_field($_POST['captcha']);
        session_start();
        if ($captcha != "" && strtolower($captcha) == strtolower($_SESSION['lfb_random_number'])) {
            echo 1;
        }
        die();
    }

    private function generateItemHtml($dataItem, $form, $dataSlide, $isFinalStep) {
        global $wpdb;

        $response = '';
        $chkDisplay = true;
        $hiddenClass = '';
        $checked = '';
        $checkedCb = '';
        $prodID = 0;
        $wooVar = $dataItem->wooVariation;
        $eddVar = $dataItem->eddVariation;
        $itemRequired = '';
        $showInSummary = '';
        $useCalculation = '';
        $calculation = '';
        $useCalculationQt = '';
        $calculationQt = '';
        $useShowConditions = '';
        $showConditionsOperator = '';
        $showConditions = '';
        $hideQtSummary = '';
        $defaultValue = '';
        $activatePaypal = '';
        $cssWidth = '';

        $stepTitleTag = 'h2';
        if ($form->stepTitleTag != '') {
            $stepTitleTag = $form->stepTitleTag;
        }

        $conditionalWrapStart = '';
        $conditionalWrapEnd = '';
        if ($dataItem->icon != "") {
            $conditionalWrapStart = '<div class="input-group">';
            $conditionalWrapEnd = '</div>';
            if ($dataItem->iconPosition) {
                $conditionalWrapEnd = '<span class="input-group-addon" id="basic-addon1"><span class="fa ' . $dataItem->icon . '"></span></span>' . $conditionalWrapEnd;
            } else {
                $conditionalWrapStart = $conditionalWrapStart . '<span class="input-group-addon" id="basic-addon1"><span class="fa ' . $dataItem->icon . '"></span></span>';
            }
        }

        if ($dataItem->defaultValue != "") {
            $defaultValue = 'value="' . $dataItem->defaultValue . '"';
        }

        if ($dataItem->hideQtSummary) {
            $hideQtSummary = 'data-hideqtsum="true"';
        }

        if ($dataItem->useShowConditions) {
            $useShowConditions = 'data-useshowconditions="true"';
            $dataItem->showConditions = str_replace('"', "'", $dataItem->showConditions);
            $showConditions = 'data-showconditions="' . addslashes($dataItem->showConditions) . '"';
            $showConditionsOperator = 'data-showconditionsoperator="' . $dataItem->showConditionsOperator . '"';
        }

        if ($dataItem->useCalculation) {
            $useCalculation = 'data-usecalculation="true"';
            $calculation = 'data-calculation="' . addslashes($dataItem->calculation) . '"';
        }
        if ($dataItem->useCalculationQt) {
            $useCalculationQt = 'data-usecalculationqt="true"';
            $calculationQt = 'data-calculationqt="' . addslashes($dataItem->calculationQt) . '"';
        }

        if ($dataItem->isRequired) {
            $itemRequired = 'data-required="true"';
        }
        if ($dataItem->ischecked == 1) {
            $checked = 'prechecked';
            $checkedCb = 'checked';
        }
        if ($dataItem->isHidden == 1) {
            $hiddenClass = 'lfb-hidden';
        }

        if ($dataItem->showInSummary == 1) {
            $showInSummary = 'data-showinsummary="true"';
        }
        if (is_plugin_active('woocommerce/woocommerce.php')) {
            if ($dataItem->wooProductID > 0) {
                $prodID = $dataItem->wooProductID;
                $dataItem->dontAddToTotal = 0;

                try {
                    $product = new WC_Product($dataItem->wooProductID);

                    if (!$product->exists()) {
                        $chkDisplay = false;
                    } else {
                        if ($dataItem->wooVariation == 0) {
                            $dataItem->price = $product->get_price();
                            if ($dataItem->type == 'slider') {
                                if ($product->get_stock_quantity() && $product->get_stock_quantity() < $dataItem->maxSize) {
                                    $dataItem->maxSize = $product->get_stock_quantity();
                                }
                            } else {
                                if ($product->get_stock_quantity() && $product->get_stock_quantity() < $dataItem->quantity_max) {
                                    $dataItem->quantity_max = $product->get_stock_quantity();
                                }
                            }
                            if (!$product->is_in_stock()) {
                                $chkDisplay = false;
                            }
                        } else {
                            $variable_product = new WC_Product_Variation($dataItem->wooVariation);
                            $dataItem->price = $variable_product->get_price();
                            if ($dataItem->type == 'slider') {
                                if ($variable_product->get_stock_quantity() && $variable_product->get_stock_quantity() < $dataItem->maxSize) {
                                    $dataItem->maxSize = $product->get_stock_quantity();
                                }
                            } else {
                                if ($variable_product->get_stock_quantity() && $variable_product->get_stock_quantity() < $dataItem->quantity_max) {
                                    $dataItem->quantity_max = $variable_product->get_stock_quantity();
                                }
                            }
                            if (!$variable_product->is_in_stock()) {
                                $chkDisplay = false;
                            }
                        }
                    }
                } catch (Exception $ex) {
                    $chkDisplay = false;
                }
            } else if ($form->save_to_cart) {
                $dataItem->price = 0;
            }
        }
        if ($dataItem->eddProductID > 0 && is_plugin_active('easy-digital-downloads/easy-digital-downloads.php')) {
            $download = new EDD_Download($dataItem->eddProductID);
            $prodID = $dataItem->eddProductID;
            $dataItem->price = $download->price;
            $dataItem->dontAddToTotal = 0;
            if ($dataItem->eddVariation > 0) {
                if (count($download->prices) > 0) {
                    $dataItem->price = $download->prices[$dataItem->eddVariation]['amount'];
                }
            }
        } else if ($form->save_to_cart_edd) {
            $dataItem->price = 0;
        }
        $dataItem->title = str_replace('"', "''", $dataItem->title);
        $originalTitle = $dataItem->title;
        $dataShowPrice = "";
        if ($dataItem->showPrice) {
            $dataShowPrice = 'data-showprice="1"';
            if ($form->currencyPosition == 'right') {
                if ($dataItem->operation == "+") {
                    $dataItem->title = $dataItem->title . " : " . $this->getFormatedPrice($dataItem->price, $form) . $form->currency;
                }
                if ($dataItem->operation == "-") {
                    $dataItem->title = $dataItem->title . " : -" . $this->getFormatedPrice($dataItem->price, $form) . $form->currency;
                }
                if ($dataItem->operation == "x") {
                    $dataItem->title = $dataItem->title . " : +" . $this->getFormatedPrice($dataItem->price, $form) . '%';
                }
                if ($dataItem->operation == "/") {
                    $dataItem->title = $dataItem->title . " : -" . $this->getFormatedPrice($dataItem->price, $form) . '%';
                }
            } else {
                if ($dataItem->operation == "+") {
                    $dataItem->title = $dataItem->title . " : " . $form->currency . $this->getFormatedPrice($dataItem->price, $form);
                }
                if ($dataItem->operation == "-") {
                    $dataItem->title = $dataItem->title . " : -" . $form->currency . $this->getFormatedPrice($dataItem->price, $form);
                }
                if ($dataItem->operation == "x") {
                    $dataItem->title = $dataItem->title . " : +" . $this->getFormatedPrice($dataItem->price, $form) . '%';
                }
                if ($dataItem->operation == "/") {
                    $dataItem->title = $dataItem->title . " : -" . $this->getFormatedPrice($dataItem->price, $form) . '%';
                }
            }
        }
        $urlTag = "";
        if ($dataItem->urlTarget != "") {
            $urlTag .= 'data-urltarget="' . $dataItem->urlTarget . '" data-urltargetmode="' . $dataItem->urlTargetMode . '"';
        }
        $isSinglePrice = '';
        if ($form->isSubscription && $dataItem->isSinglePrice) {
            $isSinglePrice = 'data-singleprice="true"';
        }

        if ($chkDisplay) {

            $colClass = 'col-md-2' . ' ' . $hiddenClass . ' lfb_item';
            if ($dataItem->useRow /* || $dataItem->type == 'richtext' */) {
                $form->itemIndex = 0;
                $colClass = 'col-md-12' . ' ' . $hiddenClass . ' lfb_item';
            } else {

                if ($dataItem->isHidden == 0) {
                    $form->itemIndex++;
                }
                if ($dataSlide && $dataSlide->itemsPerRow > 0 && $form->itemIndex - 1 == $dataSlide->itemsPerRow) {
                    $form->itemIndex = 1;
                    $response .= '<br/>';
                }
            }
            $colClass .= ' lfb_itemContainer_' . $dataItem->id;
            $distanceQt = '';
            if ($dataItem->useDistanceAsQt && $dataItem->distanceQt != "") {
                $distanceQt = 'data-distanceqt="' . $dataItem->distanceQt . '"';
            }

            $activatePaypal = '';
            if ($dataItem->usePaypalIfChecked) {
                $activatePaypal = 'data-activatepaypal="true"';
            }
            $dontActivatePaypal = '';
            if ($dataItem->dontUsePaypalIfChecked) {
                $dontActivatePaypal = 'data-dontactivatepaypal="true"';
            }

            if ($dataItem->type == 'picture') {

                $response .= '<div class="itemBloc ' . $colClass . ' lfb_picRow">';
                $group = '';
                if ($dataItem->groupitems != "") {
                    $group = 'data-group="' . $dataItem->groupitems . '"';
                }
                $tooltipPosition = 'bottom';
                if ($form->qtType == 1) {
                    $tooltipPosition = 'top';
                }
                $svgClass = strtolower(substr($dataItem->image, -4));
                if (strtolower(substr($dataItem->image, -4)) == '.svg') {
                    $svgClass = 'lfb_imgSvg';
                }
                $tooltipAttr = 'data-toggle="tooltip"';

                if ($form->imgTitlesStyle == "static") {
                    $tooltipAttr = '';
                }
                $response .= '<div class="selectable  ' . $checked . '" ' . $itemRequired . ' ' . $useCalculationQt . ' ' . $calculationQt . ' ' . $useCalculation . ' ' . $hideQtSummary . ' ' . $calculation . ' ' . $distanceQt . ' ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . ' ' . $isSinglePrice . ' ' . $dataShowPrice . ' ' . $urlTag . ' ' . $showInSummary . ' data-woovar="' . $wooVar . '" data-eddvar="' . $eddVar . '" data-reduc="' . $dataItem->reduc_enabled . '" data-reducqt="' . $dataItem->reducsQt . '"  data-operation="' . $dataItem->operation . '" data-itemid="' . $dataItem->id . '"  ' . $group . '  data-prodid="' . $prodID . '" data-title="' . $dataItem->title . '" ' . $tooltipAttr . ' title="' . $dataItem->title . '" data-originaltitle="' . $originalTitle . '" data-placement="' . $tooltipPosition . '" data-price="' . $dataItem->price . '" '
                        . 'data-addtototal="' . $dataItem->dontAddToTotal . '" data-iconstyle="' . $form->imgIconStyle . '" data-flipfx="' . $form->enableFlipFX . '"'
                        . ' data-quantityenabled="' . $dataItem->quantity_enabled . '" ' . $activatePaypal . ' ' . $dontActivatePaypal . '>';
                $tint = 'false';
                if ($dataItem->imageTint) {
                    $tint = 'true';
                }
                $response .= '<img data-tint="' . $tint . '" src="' . $dataItem->image . '" alt="' . $dataItem->imageDes . '" class="img ' . $svgClass . '" />';

                $defaultSelectorClass = 'fui-cross';
                $selectorFxClass = '';
                if ($form->imgIconStyle == 'zoom') {
                    $defaultSelectorClass = 'fui-check';
                    $selectorFxClass = 'lfb_fxZoom';
                }
                $response .= '<span class="palette-clouds ' . $defaultSelectorClass . ' ' . $selectorFxClass . ' icon_select"></span>';
                if ($dataItem->quantity_enabled) {
                    if (!$dataItem->useDistanceAsQt && $form->qtType == 1) {
                        $qtMax = '';
                        if ($dataItem->quantity_max > 0) {
                            $qtMax = 'max="' . $dataItem->quantity_max . '"';
                        } else {
                            $qtMax = 'max="999999999"';
                        }
                        if ($dataItem->quantity_min > 0) {
                            $qtMin = $dataItem->quantity_min . '"';
                        } else {
                            $qtMin = '1';
                        }
                        $response .= '<div class="form-group wpe_itemQtField">';
                        $response .= ' <input class="wpe_qtfield form-control" min="' . $qtMin . '" ' . $qtMax . ' type="number" value="' . $qtMin . '" /> ';

                        $response .= '</div>';
                    } else if (!$dataItem->useDistanceAsQt && $form->qtType == 2) {

                        $valMin = 1;
                        if ($dataItem->quantity_min > 0) {
                            $valMin = $dataItem->quantity_min;
                        }
                        if ($dataItem->sliderStep > 1) {
                            $dataItem->quantity_min = $dataItem->sliderStep;
                            $valMin = $dataItem->quantity_min;
                        }
                        $response .= '<div class="quantityBtns wpe_sliderQtContainer" data-stepslider="' . $dataItem->sliderStep . '" data-max="' . $dataItem->quantity_max . '" data-min="' . $dataItem->quantity_min . '">
                                                     <div class="wpe_sliderQt"></div>
                                                 </div>';
                        $response .= '<span class="palette-turquoise icon_quantity wpe_hidden">' . $valMin . '</span>';
                    } else {
                        $response .= '<div class="quantityBtns" data-max="' . $dataItem->quantity_max . '" data-min="' . $dataItem->quantity_min . '">
                                                <a href="javascript:" data-btn="less">-</a>
                                                <a href="javascript:" data-btn="more">+</a>
                                                </div>';
                        $valMin = 1;
                        if ($dataItem->quantity_min > 0) {
                            $valMin = $dataItem->quantity_min;
                        }
                        $response .= '<span class="palette-turquoise icon_quantity">' . $valMin . '</span>';
                    }
                }
                $response .= '</div>';
                if ($form->imgTitlesStyle == "static") {
                    $cssWidth = '';
                    if ($dataItem->useRow) {
                        $cssWidth = 'max-width: 100%;';
                    }
                    $response .= '<p class="lfb_imgTitle" style="' . $cssWidth . '">' . $dataItem->title . '</p>';
                }
                if ($dataItem->description != "") {
                    $cssWidth = '';
                    if ($dataItem->useRow) {
                        $cssWidth = 'max-width: 100%;';
                    }
                    $response .= '<p class="itemDes" style="' . $cssWidth . '">' . $dataItem->description . '</p>';
                }
                $response .= '</div>';
            } else if ($dataItem->type == 'datepicker') {

                $daysWeek = '';
                $hoursDisabled = '';
                if ($dataItem->calendarID > 0) {
                    $table_name = $wpdb->prefix . "wpefc_calendars";
                    $calendarData = $wpdb->get_results("SELECT * FROM $table_name WHERE id=" . $dataItem->calendarID . " LIMIT 1");
                    if (count($calendarData) > 0) {
                        $calendarData = $calendarData[0];
                        $daysWeek = $calendarData->unavailableDays;
                        $hoursDisabled = $calendarData->unavailableHours;
                    }
                }


                $response .= '<div class="itemBloc ' . $colClass . '">';
                $response .= '<div class="form-group">';
                if ($dataItem->useAsDateRange) {
                    $dataItem->eventDuration = 0;
                }
                $response .= '<label>' . $dataItem->title . '</label>
                        ' . $conditionalWrapStart . '<input readonly="true" type="text"  placeholder="' . $dataItem->placeholder . '" data-itemid="' . $dataItem->id . '"  ' . $showInSummary . '  ' . $hideQtSummary . '   ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . '  class="form-control lfb_datepicker" ' . $itemRequired . ' data-title="' . $dataItem->title . '" data-originaltitle="' . $originalTitle . '"  ' . $urlTag .
                        ' data-allowpast="' . $dataItem->date_allowPast . '" data-showmonths="' . $dataItem->date_showMonths . '" data-showyears="' . $dataItem->date_showYears . '"'
                        . 'data-datetype="' . $dataItem->dateType . '" data-calendarid="' . $dataItem->calendarID . '" data-daysweek="' . $daysWeek . '" data-hoursdisabled="' . $hoursDisabled . '" '
                        . 'data-eventduration="' . $dataItem->eventDuration . '" data-eventdurationtype="' . $dataItem->eventDurationType . '" data-eventcategory="' . $dataItem->eventCategory . '" data-registerevent="' . $dataItem->registerEvent . '" data-eventbusy="' . $dataItem->eventBusy . '" '
                        . 'data-eventtitle="' . $dataItem->eventTitle . '" data-useasdaterange="' . $dataItem->useAsDateRange . '" data-enddaterangeid="' . $dataItem->endDaterangeID . '" data-disableminutes="' . $dataItem->disableMinutes . '" />' . $conditionalWrapEnd . '
                         ';

                $cssWidth = '';
                if ($dataItem->useRow) {
                    $cssWidth = 'max-width: 90%;';
                }
                if ($dataItem->description != "") {
                    $response .= '<p class="itemDes" style="margin: 0 auto; ' . $cssWidth . '">' . $dataItem->description . '</p>';
                }
                $response .= '</div>';
                $response .= '</div>';
            } else if ($dataItem->type == 'timepicker') {
                $response .= '<div class="itemBloc ' . $colClass . '">';
                $response .= '<div class="form-group">';
                $response .= '<label>' . $dataItem->title . '</label>
                ' . $conditionalWrapStart . '<input type="text" data-mintime="' . $dataItem->minTime . '"  placeholder="' . $dataItem->placeholder . '" data-maxtime="' . $dataItem->maxTime . '" data-itemid="' . $dataItem->id . '"  ' . $showInSummary . '  ' . $hideQtSummary . '   ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . '  class="form-control lfb_timepicker" ' . $itemRequired . ' data-title="' . $dataItem->title . '" data-originaltitle="' . $originalTitle . '"  ' . $urlTag . '/>' . $conditionalWrapEnd;

                if ($dataItem->useRow) {
                    $cssWidth = 'max-width: 90%;';
                }
                if ($dataItem->description != "") {
                    $response .= '<p class="itemDes" style="margin: 0 auto; ' . $cssWidth . '">' . $dataItem->description . '</p>';
                }
                $response .= '</div>';
                $response .= '</div>';
            } else if ($dataItem->type == 'filefield_') {
                $response .= '<div class="itemBloc ' . $colClass . '">';
                if ($dataItem->fileSize == 0) {
                    $dataItem->fileSize = 25;
                }
                $response .= '<div class="form-group">
                            <label>' . $dataItem->title . '</label>
                            ' . $conditionalWrapStart . '<input type="file" ' . $itemRequired . ' data-filesize="' . $dataItem->fileSize . '"  ' . $showInSummary . '  ' . $hideQtSummary . '   ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . ' class="lfb_filefield"  name="file_' . $dataItem->id . '" data-itemid="' . $dataItem->id . '" data-title="' . $dataItem->title . '" data-originaltitle="' . $originalTitle . '" ' . $urlTag . '  />' . $conditionalWrapEnd . '
                            </div>
                            ';
                if ($dataItem->useRow) {
                    $cssWidth = 'max-width: 90%;';
                }
                if ($dataItem->description != "") {
                    $response .= '<p class="itemDes" style="margin: 0 auto; ' . $cssWidth . '">' . $dataItem->description . '</p>';
                }
                $response .= '</div>';
            } else if ($dataItem->type == 'filefield') {
                if ($dataItem->fileSize == 0) {
                    $dataItem->fileSize = 25;
                }
                $response .= '<div class="itemBloc ' . $colClass . '" style="margin-top: 18px;">';
                $response .= '<label>' . $dataItem->title . '</label>';
                $response .= '<div class="lfb_dropzone dropzone" data-filesize="' . $dataItem->fileSize . '" ' . $itemRequired . '  ' . $hideQtSummary . '   ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . ' ' . $showInSummary . ' data-allowedfiles="' . $dataItem->allowedFiles . '" data-maxfiles="' . $dataItem->maxFiles . '" id="lfb_dropzone_' . $dataItem->id . '" data-itemid="' . $dataItem->id . '" data-title="' . $dataItem->title . '" data-originaltitle="' . $originalTitle . '" ></div>';

                if ($dataItem->useRow) {
                    $cssWidth = 'max-width: 90%;';
                }
                if ($dataItem->description != "") {
                    $response .= '<p class="itemDes" style="margin: 0 auto; ' . $cssWidth . '; margin-top: 6px !important;">' . $dataItem->description . '</p>';
                }
                $response .= '</div>';
            } else if ($dataItem->type == 'qtfield') {
                $response .= '<div class="itemBloc ' . $colClass . '">';
                $response .= '<div class="form-group">';
                $response .= '<label>' . $dataItem->title . '</label>';
                $qtMax = '';
                if ($qtMax > 0) {
                    $qtMax = 'max="' . $dataItem->quantity_max . '"';
                }
                $response .= ' <input  ' . $urlTag . '  ' . $showInSummary . '  ' . $hideQtSummary . '   ' . $useShowConditions . ' ' . $showConditions . '  ' . $isSinglePrice . '  class="wpe_qtfield form-control" min="0" ' . $qtMax . ' ' . $dataShowPrice . ' type="number" value="0" data-reduc="' . $dataItem->reduc_enabled . '" data-price="' . $dataItem->price . '"  data-addtototal="' . $dataItem->dontAddToTotal . '" data-reducqt="' . $dataItem->reducsQt . '" data-operation="' . $dataItem->operation . '" data-itemid="' . $dataItem->id . '" class="form-control" data-title="' . $dataItem->title . '" /> ';
                if ($dataItem->useRow) {
                    $cssWidth = 'max-width: 90%;';
                }
                if ($dataItem->description != "") {
                    $response .= '<p class="itemDes" style="margin: 0 auto; ' . $cssWidth . ';">' . $dataItem->description . '</p>';
                }
                $response .= '</div>';
                $response .= '</div>';
            } else if ($dataItem->type == 'textarea') {
                $response .= '<div class="itemBloc ' . $colClass . '">';
                $response .= '<div class="form-group">';
                $response .= '<label>' . $dataItem->title . '</label>
                 <textarea  placeholder="' . $dataItem->placeholder . '"  data-type="' . $dataItem->type . '" data-itemid="' . $dataItem->id . '"  ' . $useShowConditions . '  ' . $hideQtSummary . '  ' . $showConditions . ' ' . $showConditionsOperator . ' ' . $showInSummary . ' ' . $urlTag . ' class="form-control" ' . $itemRequired . ' data-title="' . $dataItem->title . '" data-originaltitle="' . $originalTitle . '">' . $dataItem->defaultValue . '</textarea>';
                if ($dataItem->useRow) {
                    $cssWidth = 'max-width: 90%;';
                }
                if ($dataItem->description != "") {
                    $response .= '<p class="itemDes" style="margin: 0 auto; ' . $cssWidth . ';">' . $dataItem->description . '</p>';
                }
                $response .= '</div>';
                $response .= '</div>';
            } else if ($dataItem->type == 'select') {
                $response .= '<div class="itemBloc ' . $colClass . '">';
                $dropClass = "lfb_selectpicker";
                if ($form->disableDropdowns) {
                    $dropClass = "";
                }
                $firstVDisabled = '';
                if ($dataItem->firstValueDisabled) {
                    $firstVDisabled = 'data-firstvaluedisabled="true"';
                }
                if ($form->disableDropdowns == 0) {
                    $conditionalWrapStart = '';
                    $conditionalWrapEnd = '';
                }
                $response .= '
                    <div class="form-group">
                    <label>' . $dataItem->title . '</label>
                   ' . $conditionalWrapStart . '<select  data-addtototal="' . $dataItem->dontAddToTotal . '" class="form-control ' . $dropClass . ' " ' . $itemRequired . ' ' . $firstVDisabled . ' ' . $useShowConditions . '  ' . $hideQtSummary . '  ' . $showConditions . ' ' . $showConditionsOperator . ' ' . $showInSummary . ' ' . $isSinglePrice . '  data-operation="' . $dataItem->operation . '"  data-originaltitle="' . $originalTitle . '"  ' . $urlTag . '  data-itemid="' . $dataItem->id . '"  data-title="' . $dataItem->title . '" >';
                $optionsArray = explode('|', $dataItem->optionsValues);
                foreach ($optionsArray as $option) {
                    if ($option != "") {
                        $value = $option;
                        $price = 0;
                        if (strpos($option, ";;") > 0) {
                            $optionArr = explode(";;", $option);
                            $value = $optionArr[0];
                            $price = $optionArr[1];
                        }
                        $response .= '<option value="' . $value . '" data-price="' . $price . '" >' . $value . '</option>';
                    }
                }
                $response .= '</select>'. $conditionalWrapEnd.'</div>' ;
                if ($dataItem->useRow) {
                    $cssWidth = 'max-width: 90%;';
                }
                if ($dataItem->description != "") {
                    $response .= '<p class="itemDes" style="margin: 0 auto; ' . $cssWidth . ';">' . $dataItem->description . '</p>';
                }
                $response .= '</div>';
            } else if ($dataItem->type == 'richtext') {
                $response .= '<div class="itemBloc lfb_richtext  ' . $colClass . '" data-itemid="' . $dataItem->id . '" data-title="' . $dataItem->title . '"  ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . '>' . do_shortcode($dataItem->richtext) . '</div>';
            } else if ($dataItem->type == 'shortcode') {
                $response .= '<div class="itemBloc lfb_richtext lfb_shortcode ' . $colClass . '" data-itemid="' . $dataItem->id . '" data-title="' . $dataItem->title . '"  ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . '>' . do_shortcode($dataItem->shortcode) . '</div>';
            } else if ($dataItem->type == 'separator') {
                $response .= '<div data-itemid="' . $dataItem->id . '" ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . '></div>';
            } else if ($dataItem->type == 'layeredImage') {
                $styles = '';
                if ($dataItem->maxWidth > 0) {
                    $styles = 'max-width:' . $dataItem->maxWidth . 'px;';
                }
                if ($dataItem->maxHeight > 0) {
                    $styles .= 'max-height:' . $dataItem->maxHeight . 'px;';
                }
                $response .= '<div class="clearfix"></div>';
                $response .= '<div style="' . $styles . '" class="lfb_item lfb_layeredImage" data-itemid="' . $dataItem->id . '" data-title="' . $dataItem->title . '" ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . '>';

                $response .= '<img src="' . $dataItem->image . '" style="' . $styles . '" class="lfb_baseLayer"  alt="' . $dataItem->title . '" />';
                global $wpdb;
                $table_name = $wpdb->prefix . "wpefc_layeredImages";
                $layers = $wpdb->get_results("SELECT * FROM $table_name WHERE formID=$form->id AND itemID=$dataItem->id ORDER BY ordersort ASC");
                $i = 0;
                foreach ($layers as $layer) {
                    $conditions = str_replace('"', "'", $layer->showConditions);
                    $response .= '<img src="' . $layer->image . '" alt="' . $dataItem->title . '" data-showconditions="' . $conditions . '" data-showconditionsoperator="' . $layer->showConditionsOperator . '" />';
                    $i++;
                }
                $response .= '</div>';
                $response .= '<div class="clearfix"></div>';
            } else if ($dataItem->type == 'checkbox') {
                $group = '';
                if ($dataItem->groupitems != "") {
                    $group = 'data-group="' . $dataItem->groupitems . '"';
                }
                $response .= '<div class="itemBloc ' . $colClass . '">';
                $response .= '<div>
                                                <label>' . $dataItem->title . '</label>';

                if (!$form->inlineLabels) {
                    $response .= '<br/>';
                }

                $response .= '<input type="checkbox"  ' . $hideQtSummary . '  ' . $group . ' ' . $useCalculationQt . ' ' . $calculationQt . ' ' . $useCalculation . ' ' . $activatePaypal . ' ' . $dontActivatePaypal . ' ' . $calculation . '  ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . ' ' . $showInSummary . ' ' . $isSinglePrice . '  class="' . $checked . '" ' . $urlTag . ' ' . $dataShowPrice . ' data-operation="' . $dataItem->operation . '" data-originaltitle="' . $originalTitle . '" data-itemid="' . $dataItem->id . '" data-prodid="' . $prodID . '"  data-woovar="' . $wooVar . '" data-eddvar="' . $eddVar . '" ' . $itemRequired . ' data-toggle="switch" ' . $checkedCb . ' data-price="' . $dataItem->price . '"  data-addtototal="' . $dataItem->dontAddToTotal . '" data-title="' . $dataItem->title . '" />
                                                </div>';
                if ($dataItem->useRow) {
                    $cssWidth = 'max-width: 90%;';
                }
                if ($dataItem->description != "") {
                    $response .= '<p class="itemDes" style="margin: 0 auto; ' . $cssWidth . ';">' . $dataItem->description . '</p>';
                }
                $response .= '</div>';
            } else if ($dataItem->type == 'button') {
                $response .= '<div class="itemBloc ' . $colClass . ' lfb_btnContainer">';
                $group = '';
                if ($dataItem->groupitems != "") {
                    $group = 'data-group="' . $dataItem->groupitems . '"';
                }
                $tooltipPosition = 'bottom';
                if ($form->qtType == 1) {
                    $tooltipPosition = 'top';
                }
                $svgClass = strtolower(substr($dataItem->image, -4));
                if (strtolower(substr($dataItem->image, -4)) == '.svg') {
                    $svgClass = 'lfb_imgSvg';
                }
                $color = '';
                if ($dataItem->color != '') {
                    $color = 'style="background-color: ' . $dataItem->color . ';"';
                }
                $callNextstep = '';
                if ($dataItem->callNextStep) {
                    $callNextstep = 'data-callnextstep="1"';
                }
                $response .= '<a  ' . $activatePaypal . ' ' . $dontActivatePaypal . ' class="lfb_button btn btn-primary btn-wide ' . $checked . '" ' . $callNextstep . ' ' . $color . ' ' . $itemRequired . ' ' . $useCalculationQt . ' ' . $calculationQt . ' ' . $useCalculation . ' ' . $hideQtSummary . ' ' . $calculation . ' ' . $distanceQt . ' ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . ' ' . $isSinglePrice . ' ' . $dataShowPrice . ' ' . $urlTag . ' ' . $showInSummary . ' data-woovar="' . $wooVar . '" data-eddvar="' . $eddVar . '" data-operation="' . $dataItem->operation . '" data-itemid="' . $dataItem->id . '"  ' . $group . '  data-prodid="' . $prodID . '" data-title="' . $dataItem->title . '"  title="' . $dataItem->title . '" data-originaltitle="' . $originalTitle . '"  data-price="' . $dataItem->price . '" data-addtototal="' . $dataItem->dontAddToTotal . '">';
                if ($dataItem->icon != "" && $dataItem->iconPosition == 0) {
                    $response .= '<span class="fa ' . $dataItem->icon . '"></span>';
                }
                $response .= $dataItem->title;
                if ($dataItem->icon != "" && $dataItem->iconPosition == 1) {
                    $response .= '<span class="fa ' . $dataItem->icon . ' lfb_iconRight"></span>';
                }
                $response .= '</a>';
                if ($dataItem->useRow) {
                    $cssWidth = 'max-width: 90%;';
                }
                if ($dataItem->description != "") {
                    $response .= '<p class="itemDes" style="margin: 0 auto; ' . $cssWidth . ';">' . $dataItem->description . '</p>';
                }
                $response .= '</div>';
            } else if ($dataItem->type == 'colorpicker') {
                $response .= '<div class="itemBloc ' . $colClass . '">';
                $response .= '<div style="background-color: ' . $form->colorA . ';"  ' . $useShowConditions . '  ' . $hideQtSummary . '  ' . $showConditions . ' ' . $showConditionsOperator . ' class="lfb_colorPreview checked" data-itemid="' . $dataItem->id . '"  ' . $urlTag . ' ' . $showInSummary . ' data-toggle="tooltip"  ' . $itemRequired . ' data-placement="bottom" data-title="' . $dataItem->title . '" data-originaltitle="' . $originalTitle . '" ></div>'
                        . '<input type="text" value="' . $form->colorA . '" class="lfb_colorpicker" />'
                        . '<label class="lfb-hidden">' . $dataItem->title . '</label>';

                if ($dataItem->useRow) {
                    $cssWidth = 'max-width: 90%;';
                }
                if ($dataItem->description != "") {
                    $response .= '<p class="itemDes" style="margin: 0 auto;' . $cssWidth . ';">' . $dataItem->description . '</p>';
                }
                $response .= '</div>';
            } else if ($dataItem->type == 'numberfield') {
                $response .= '<div class="itemBloc ' . $colClass . '">';
                $response .= '<div class="form-group">';
                $minLength = '';
                $maxLength = '';
                //  if ($dataItem->minSize > 0) {
                $minLength = 'min="' . $dataItem->minSize . '"';
                //  }
                if ($dataItem->maxSize > 0) {
                    $maxLength = 'max="' . $dataItem->maxSize . '"';
                }
                $response .= '<label>' . $dataItem->title . '</label>
                     ' . $conditionalWrapStart . '<input data-type="' . $dataItem->type . '" type="number" ' . $useCalculationQt . ' ' . $calculationQt . ' data-reduc="' . $dataItem->reduc_enabled . '" data-reducqt="' . $dataItem->reducsQt . '"  data-price="' . $dataItem->price . '" ' . $isSinglePrice . '  data-operation="' . $dataItem->operation . '" data-addtototal="' . $dataItem->dontAddToTotal . '" ' . $useCalculation . ' ' . $calculation . ' data-valueasqt="' . $dataItem->useValueAsQt . '" placeholder="' . $dataItem->placeholder . '" ' . $useShowConditions . ' ' . $showConditions . '  ' . $hideQtSummary . '  ' . $showConditionsOperator . ' data-itemid="' . $dataItem->id . '" ' . $minLength . ' ' . $maxLength . ' ' . $showInSummary . ' ' . $urlTag . ' ' . $defaultValue . ' class="form-control" ' . $itemRequired . ' data-title="' . $dataItem->title . '" data-originaltitle="' . $originalTitle . '" />' . $conditionalWrapEnd . '
                  ';

                if ($dataItem->useRow) {
                    $cssWidth = 'max-width: 90%;';
                }
                if ($dataItem->description != "") {
                    $response .= '<p class="itemDes" style="margin: 0 auto; ' . $cssWidth . ';">' . $dataItem->description . '</p>';
                }
                $response .= '</div>';
                $response .= '</div>';
            } else if ($dataItem->type == 'slider') {
                $dataShowPrice = '';
                if ($dataItem->showPrice) {
                    $dataShowPrice = 'data-showprice="1"';
                }
                $response .= '<div class="itemBloc ' . $colClass . '">';
                $minLength = 'data-min="0"';
                $maxLength = 'data-max="30"';
                if ($dataItem->maxSize < $dataItem->minSize) {
                    $dataItem->minSize = $dataItem->maxSize;
                }
                if ($dataItem->minSize > 0) {
                    $minLength = 'data-min="' . $dataItem->minSize . '"';
                }
                if ($dataItem->sliderStep > 1 && $dataItem->minSize < $dataItem->sliderStep) {
                    $dataItem->minSize = $dataItem->sliderStep;
                }
                if ($dataItem->maxSize > 0) {
                    $maxLength = 'data-max="' . $dataItem->maxSize . '"';
                }

                $response .= '<label>' . $dataItem->title . '</label>
                    <div data-type="slider"  data-stepslider="' . $dataItem->sliderStep . '" ' . $distanceQt . '  ' . $dataShowPrice . '  ' . $hideQtSummary . '  ' . $isSinglePrice . '  data-reducqt="' . $dataItem->reducsQt . '" data-operation="' . $dataItem->operation . '" data-reduc="' . $dataItem->reduc_enabled . '" data-price="' . $dataItem->price . '"  data-addtototal="' . $dataItem->dontAddToTotal . '"  ' . $useCalculationQt . ' ' . $calculationQt . ' ' . $useCalculation . ' ' . $calculation . '  ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . ' data-itemid="' . $dataItem->id . '" ' . $minLength . ' ' . $maxLength . ' ' . $showInSummary . ' class="" data-title="' . $dataItem->title . '" data-originaltitle="' . $originalTitle . '" data-prodid="' . $prodID . '"  data-eddvar="' . $eddVar . '" data-woovar="' . $wooVar . '"></div>
                    ';

                if ($dataItem->useRow) {
                    $cssWidth = 'max-width: 90%;';
                }
                if ($dataItem->description != "") {
                    $response .= '<p class="itemDes" style="margin: 0 auto; ' . $cssWidth . ';">' . $dataItem->description . '</p>';
                }
                $response .= '</div>';
            } else {
                $response .= '<div class="itemBloc ' . $colClass . '">';
                $response .= '<div class="form-group">';
                $minLength = '';
                $maxLength = '';
                $autocomp = '';
                if ($dataItem->minSize > 0) {
                    $minLength = 'minlength="' . $dataItem->minSize . '"';
                }
                if ($dataItem->maxSize > 0) {
                    $maxLength = 'maxlength="' . $dataItem->maxSize . '"';
                }
                if ($dataItem->fieldType == 'email') {
                    $autocomp = 'autocomplete="on" name="email" ';
                }
                $validation = '';
                if ($dataItem->validation != '') {
                    $validation = 'data-validation="' . $dataItem->validation . '"';
                    if ($dataItem->validation == 'custom') {
                        $validation .= ' data-validmin="' . $dataItem->validationMin . '"';
                        $validation .= ' data-validmax="' . $dataItem->validationMax . '"';
                        $validation .= ' data-validcar="' . $dataItem->validationCaracts . '"';
                    }
                }
                if (strlen($form->gmap_key) < 3) {
                    $dataItem->autocomplete = 0;
                }
                $response .= '<label>' . $dataItem->title . '</label>
                 ' . $conditionalWrapStart . '<input  data-type="' . $dataItem->type . '" type="text" ' . $validation . ' data-autocomplete="' . $dataItem->autocomplete . '" placeholder="' . $dataItem->placeholder . '" data-fieldtype="' . $dataItem->fieldType . '" ' . $defaultValue . '  ' . $hideQtSummary . '  ' . $autocomp . ' ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . ' data-itemid="' . $dataItem->id . '" ' . $minLength . ' ' . $maxLength . ' ' . $showInSummary . ' ' . $urlTag . ' class="form-control" ' . $itemRequired . ' data-title="' . $dataItem->title . '" data-originaltitle="' . $originalTitle . '" />' . $conditionalWrapEnd . '
                ';
                if ($dataItem->useRow) {
                    $cssWidth = 'max-width: 90%;';
                }
                if ($dataItem->description != "") {
                    $response .= '<p class="itemDes" style="margin: 0 auto; ' . $cssWidth . ';">' . $dataItem->description . '</p>';
                }
                $response .= '</div>';
                $response .= '</div>';
            }
        }
        return $response;
    }

    /*
     * Shortcode to integrate a form in a page
     */

    public function wpt_shortcode($attributes, $content = null) {
        global $wpdb;
        $response = "";
        $popup = false;
        $fullscreen = false;
        extract(shortcode_atts(array(
            'form' => 0,
            'height' => 1000,
            'popup' => false,
            'fullscreen' => false,
            'form_id' => 0), $attributes));
        if (is_numeric($height)) {
            $height .= 'px';
        }
        if ($form_id == 0) {
            $table_name = $wpdb->prefix . "wpefc_forms";
            $formReq = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id ASC LIMIT 1");
            $form = $formReq[0];
            $form_id = $form->id;
        }
        if ($form_id != "" && $form_id > 0 && !is_array($form_id)) {
            $table_name = $wpdb->prefix . "wpefc_forms";
            $forms = array();
            $formReq = $wpdb->get_results("SELECT * FROM $table_name WHERE id=" . $form_id . " LIMIT 1");
            if (count($formReq) > 0) {
                $form = $formReq[0];
                //$form=$formReq->form_page_id;
                $settings = $this->getSettings();
                $fields = $this->getFieldDatas($form->id);
                $steps = $this->getStepsData($form->id);
                $items = $this->getItemsData($form->id);

                if (!$form->save_to_cart) {
                    $form->save_to_cart = '0';
                }
                $popupCss = '';
                $fullscreenCss = '';
                if ($popup) {
                    $popupCss = 'wpe_popup';
                }
                if ($fullscreen) {
                    $fullscreenCss = 'wpe_fullscreen';
                }
                $formSession = uniqid();
                $priceSubs = '';
                $priceSubsClass = '';
                $dataSubs = '';
                $dataIsSubs = '';
                if ($form->isSubscription) {
                    $dataIsSubs = 'data-isSubs="true"';
                }
                if ($form->isSubscription && $form->showSteps == 0) {
                    $priceSubsClass = 'lfb_subsPrice';
                    $priceSubs = '<span>' . $form->subscription_text . '</span>';
                    $dataSubs = $form->subscription_text;
                }
                $priceSubBottom = '';
                if ($form->isSubscription) {
                    $priceSubBottom = '<span class="lfb_subTxtBottom">' . $form->subscription_text . '</span>';
                }
                $dispIntro = '';
                if (!$form->intro_enabled) {
                    $dispIntro = 'display:none !important;';
                }
                $progressBarHide = '';
                if ($form->showSteps == 2) {
                    $progressBarHide = 'style="display: none !important;"';
                }
                $dataInlineLabels = '';
                if ($form->inlineLabels) {
                    $dataInlineLabels = 'data-inlinelabels="true"';
                }
                $dataAlignLeft = '';
                if ($form->alignLeft) {
                    $dataAlignLeft = 'data-alignleft="true"';
                }
                $dataPreviousStepBtn = '';
                if ($form->previousStepBtn) {
                    $dataPreviousStepBtn = 'data-previousstepbtn="true"';
                }
                $dataTotalRange = '';
                if ($form->totalIsRange) {
                    $dataTotalRange = 'data-totalrange="' . $form->totalRange . '" data-rangelabelbetween="' . $form->labelRangeBetween . '" data-rangelabeland="' . $form->labelRangeAnd . '" data-rangemode="' . $form->totalRangeMode . '"';
                }
                $datashowsteps = '';
                if ($form->showSteps) {
                    $datashowsteps = 'data-showsteps="true"';
                }
                $finalIcon = '';
                if ($form->finalButtonIcon != "") {
                    $finalIcon = '<span class="fa ' . $form->finalButtonIcon . '"></span>';
                }
                $nextStepIcon = '';
                if ($form->nextStepButtonIcon != "") {
                    $nextStepIcon = '<span class="fa ' . $form->nextStepButtonIcon . '"></span>';
                }
                $previousIcon = '';
                if ($form->previousStepButtonIcon != "") {
                    $previousIcon = '<span class="fa ' . $form->previousStepButtonIcon . '"></span>';
                }
                $mainTitleTag = 'h1';
                if ($form->mainTitleTag != '') {
                    $mainTitleTag = $form->mainTitleTag;
                }
                $stepTitleTag = 'h2';
                if ($form->stepTitleTag != '') {
                    $stepTitleTag = $form->stepTitleTag;
                }


                $response .= '<div id="lfb_bootstraped" class="lfb_bootstraped"><div id="estimation_popup" data-qttype="' . $form->qtType . '" data-imgtitlesstyle="' . $form->imgTitlesStyle . '" data-emaillaststep="' . $form->sendEmailLastStep . '" ' . $datashowsteps . ' ' . $dataTotalRange . ' ' . $dataIsSubs . ' ' . $dataInlineLabels . ' ' . $dataAlignLeft . ' ' . $dataPreviousStepBtn . ' data-formtitle="' . $form->title . '" data-formsession="' . $formSession . '" data-autoclick="' . $form->groupAutoClick . '"  data-subs="' . $dataSubs . '" data-form="' . $form_id . '" class="wpe_bootstraped ' . $popupCss . ' ' . $fullscreenCss . '" data-stylefields="' . $form->fieldsPreset . '" data-animspeed="' . $form->animationsSpeed . '" >
                <div id="lfb_loader"><div class="lfb_spinner"><div class="double-bounce1"></div><div class="double-bounce2"></div></div></div>';

                if ($form->enableFloatingSummary || $form->enableSaveForLaterBtn) {
                    $response .= '<div class="lfb_floatingSummaryBtnCtWrapper">';
                    $response .= '<div class="lfb_floatingSummaryBtnCt">';

                    if ($form->enableSaveForLaterBtn) {
                        $margRight = '0px';
                        if ($form->enableFloatingSummary) {
                            $margRight = '8px';
                        }
                        $btnCircleClass = '';
                        if ($form->saveForLaterIcon != "" && $form->saveForLaterLabel == "") {
                            $btnCircleClass = 'btn-circle';
                        }
                        $response .= '<a href="javascript:" data-defaulttext="' . $form->saveForLaterLabel . '" data-deltext="' . $form->saveForLaterDelLabel . '" style="margin-right:' . $margRight . '" onclick="lfb_saveForLater(' . $form->id . ');" data-originalicon="' . $form->saveForLaterIcon . '" class="lfb_btnSaveForm btn btn-default ' . $btnCircleClass . '">';
                        if ($form->saveForLaterIcon != "") {
                            $response .= '<span class="fa ' . $form->saveForLaterIcon . '"></span>';
                        }
                        $response .= '<span>' . $form->saveForLaterLabel . '</span>';
                        $response .= '</a>';
                    }


                    if ($form->enableFloatingSummary) {
                        $btnCircleClass = '';
                        if ($form->floatSummary_icon != "" && $form->floatSummary_label == "") {
                            $btnCircleClass = 'btn-circle';
                        }
                        $response .= '<a href="javascript:" onclick="lfb_toggleFloatingSummary(' . $form->id . ');" class="lfb_btnFloatingSummary btn btn-default ' . $btnCircleClass . ' disabled">';
                        if ($form->floatSummary_icon != "") {
                            $response .= '<span class="fa ' . $form->floatSummary_icon . '"></span>';
                        }
                        $response .= $form->floatSummary_label;
                        $response .= '</a>';

                        $response .= '</div>';
                        $response .= '<div id="lfb_floatingSummary" data-numberstep="' . $form->floatSummary_numSteps . '" data-hideprices="' . $form->floatSummary_hidePrices . '"><div id="lfb_floatingSummaryInner"></div></div>';
                    } else {
                        $response .= '</div>';
                    }
                    $response .= '</div>';
                }

                $response .= '<a id="wpe_close_btn" href="javascript:"><span class="fui-cross"></span></a>
                <div id="wpe_panel">
                <div class="container-fluid">
                    <div class="row">
                        <div class="" >';
                if ($form->intro_enabled) {
                    $response .= '<div id="startInfos" style="' . $dispIntro . '">';
                    if ($form->intro_image != "") {
                        $response .= '<p style="text-align: center;"><img src="' . $form->intro_image . '" id="lfb_introImage" alt="' . $form->intro_title . '" /></p>';
                    }
                    $response .= '<' . $mainTitleTag . ' id="lfb_mainFormTitle">' . $form->intro_title . '</' . $mainTitleTag . '>
                        <p>' . $form->intro_text . '</p>
                            </div>';
                }

                $introIcon = '';
                if ($form->introButtonIcon != "") {
                    $introIcon = '<span class="fa ' . $form->introButtonIcon . '"></span>';
                }
                $response .= '<p class="lfb_startBtnContainer" style="' . $dispIntro . '">
                                <a href="javascript:" onclick="lfb_startFormIntro(' . $form->id . ');" class="btn btn-large btn-primary" id="btnStart">' . $introIcon . $form->intro_btn . '</a>
                            </p>

                            <div id="genPrice" class="genPrice" ' . $progressBarHide . '>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 0%;">
                                        <div class="progress-bar-price ' . $priceSubsClass . '">
                                            <span>0$</span>
                                            ' . $priceSubs . '
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <!-- /genPrice -->
                            <' . $stepTitleTag . ' id="finalText" class="stepTitle">' . $form->succeed_text . '</' . $stepTitleTag . '>
                        </div>
                        <!-- /col -->
                    </div>
                    <!-- /row -->
                <div id="mainPanel" class="palette-clouds" data-savecart="' . $form->save_to_cart . '">
                <input type="hidden" name="action" value="lfb_upload_form"/>
                <input type="hidden" id="lfb_formSession" name="formSession" value="' . $formSession . '"/>';
                $i = 0;

                foreach ($steps as $dataSlide) {
                    if ($dataSlide->formID == $form->id) {
                        $dataContent = json_decode($dataSlide->content);
                        if (!empty($dataContent)) {
                            $required = '';
                            if ($dataSlide->itemRequired > 0) {
                                $required = 'data-required="true"';
                            }
                            $useShowStepConditions = '';
                            $showStepConditionsOperator = '';
                            $showStepConditions = '';
                            if ($dataSlide->useShowConditions) {
                                $useShowStepConditions = 'data-useshowconditions="true"';
                                $dataSlide->showConditions = str_replace('"', "'", $dataSlide->showConditions);
                                $showStepConditions = 'data-showconditions="' . addslashes($dataSlide->showConditions) . '"';
                                $showStepConditionsOperator = 'data-showconditionsoperator="' . $dataSlide->showConditionsOperator . '"';
                            }

                            $response .= '<div class="genSlide" data-start="' . $dataContent->start . '" ' . $useShowStepConditions . ' ' . $showStepConditions . ' ' . $showStepConditionsOperator . ' data-showstepsum="' . $dataSlide->showInSummary . '" data-stepid="' . $dataSlide->id . '" data-title="' . $dataSlide->title . '" ' . $required . ' data-dependitem="' . $dataSlide->itemDepend . '">';
                            $response .= '	<' . $stepTitleTag . ' class="stepTitle">' . $dataSlide->title . '</' . $stepTitleTag . '>';
                            $contentNoDes = 'lfb_noDes';
                            if ($dataSlide->description != "") {
                                $response .= '	<p class="lfb_stepDescription">' . $dataSlide->description . '</p>';
                                $contentNoDes = '';
                            }
                            $response .= '	<div class="genContent container-fluid ' . $contentNoDes . '">';
                            $response .= '		<div class="row">';
                            $form->itemIndex = 0;
                            foreach ($items as $dataItem) {

                                if ($dataItem->stepID == $dataSlide->id) {
                                    $response .= $this->generateItemHtml($dataItem, $form, $dataSlide, false);
                                }
                            }

                            $response .= ' </div>';
                            $response .= ' </div>';
                            if ($form->showTotalBottom) {
                                $response .= '<div class="lfb_totalBottomContainer ' . $priceSubsClass . '"><hr/><h3 class="lfb_totalBottom">
                                <span>0$</span>' . $priceSubBottom . '</h3></div>';
                            }
                            $response .= '<div class="errorMsg alert alert-danger">' . $form->errorMessage . '</div>';
                            $response .= '<p style="margin-top: 22px; position: absolute; width: 100%;" class="text-center lfb_btnNextContainer">';
                            $hideNtxStepBtn = '';
                            if ($dataSlide->hideNextStepBtn) {
                                $hideNtxStepBtn = 'lfb-hidden lfb-btnNext-hidden';
                            }
                            $shineCanvas = '';
                            $response .= '<a href="javascript:" id="lfb_btnNext_' . $dataContent->id . '" class="btn btn-wide btn-primary btn-next ' . $hideNtxStepBtn . '">' . $nextStepIcon . $form->btn_step . '</a>';

                            if ($dataContent->start == 0) {
                                $response .= '<br/><a href="javascript:"  class="linkPrevious">' . $previousIcon . $form->previous_step . '</a>';
                            }
                            $response .= '</p>';

                            $response .= '</div>';
                            $i++;
                        }
                    }
                }

                $response .= '<div class="genSlide" id="finalSlide" data-stepid="final" data-title="' . $form->last_title . '">
                <' . $stepTitleTag . ' class="stepTitle">' . $form->last_title . '</' . $stepTitleTag . '>
                <div class="genContent">
                    <div class="genContentSlide active">
                        ';
                $dispFinalPrice = '';
                if ($form->hideFinalPrice == 1) {
                    $dispFinalPrice = "display:none;";
                }
                $response .= '<p id="lfb_finalLabel" style="' . $dispFinalPrice . '">' . $form->last_text . '</p>';
                $subTxt = '';
                if ($form->isSubscription == 1) {
                    $subTxt = '<span>' . $form->subscription_text . '</span>';
                }
                $response .= '<h3 id="finalPrice" style="' . $dispFinalPrice . '"><span></span>' . $subTxt . '</h3>';

                $response .= '<div id="lfb_subTxtValue" style="display: none;">' . $priceSubs . '</div>';

                if ($form->gravityFormID > 0) {
                    gravity_form($form->gravityFormID, $display_title = false, $display_description = true, $display_inactive = false, $field_values = null, $ajax = true);
                } else {
                    $fieldIndex = 0;
                    foreach ($fields as $field) {

                        $response .= $this->generateItemHtml($field, $form, null, true, $fieldIndex);
                        $fieldIndex++;
                    }
                }

                if ($form->useCoupons) {
                    $response .= '<div id="lfb_couponContainer" class="form-group">'
                            . '<input type="text" placeholder="' . $form->couponText . '" id="lfb_couponField" class="form-control"/>'
                            . '<a href="javascript:" id="lfb_couponBtn" onclick="lfb_applyCouponCode(' . $form->id . ');" class="btn btn-primary"><span class="glyphicon glyphicon-check"></span></a>'
                            . '</div>';
                }

                $cssSum = '';
                $cssQtCol = '';
                if (!$form->useSummary) {
                    $cssSum = 'lfb-hidden';
                }
                if ($form->summary_hideQt) {
                    $cssQtCol = 'lfb-hidden';
                }
                $subTxt = '';
                if ($form->isSubscription == 1) {
                    $subTxt = '<span class="lfb_subTxt">' . $form->subscription_text . '</span>';
                }
                $priceHiddenClass = '';
                if ($form->summary_hidePrices == 1) {
                    $priceHiddenClass = 'lfb-hidden lfb_hidePrice';
                }
                $totalHiddenClass = '';
                if ($form->summary_hideTotal == 1) {
                    $totalHiddenClass = 'lfb-hidden lfb_hidePrice';
                }
                $response .= '
                   <div id="lfb_summary" class="table-responsive ' . $cssSum . '">
                        <h4>' . $form->summary_title . '</h4>
                        <table class="table table-bordered">
                            <thead>
                                <th>' . $form->summary_description . '</th>
                                <th class="lfb_valueTh">' . $form->summary_value . '</th>
                                <th class="lfb_quantityTh ' . $cssQtCol . '">' . $form->summary_quantity . '</th>
                                <th class="lfb_priceTh ' . $priceHiddenClass . '">' . $form->summary_price . '</th>
                            </thead>
                            <tbody>
                                <tr id="lfb_summaryDiscountTr" class="lfb_static ' . $priceHiddenClass . '"><th colspan="3">' . $form->summary_discount . '</th><th id="lfb_summaryDiscount"><span></span></th></tr>
                                <tr id="sfb_summaryTotalTr" class="lfb_static ' . $totalHiddenClass . '"><th colspan="3">' . $form->summary_total . '</th><th id="lfb_summaryTotal"><span></span>' . $subTxt . '</th></tr>
                            </tbody>
                        </table>
                    </div>';


                if ($form->legalNoticeEnable) {
                    $response .= '
                    <div id="lfb_legalNoticeContent">' . nl2br($form->legalNoticeContent) . '</div>
                    <div class="form-group" style=" margin-top: 14px;">
                      <label for="lfb_legalCheckbox">' . $form->legalNoticeTitle . '</label>
                      <input type="checkbox" data-toggle="switch" id="lfb_legalCheckbox" class="form-control"/>
                    </div>';
                }



                if ($form->use_stripe && $form->paymentType != "email") {

                    $response .= '<form id="lfb_stripeForm" action="" data-title="' . $form->title . '" method="post">';
                    $response .= '<div class="lfb_stripeContainer">';


                    $response .= '
                    <div class="form-group">
                    <label>
                      <span>' . $form->stripe_label_creditCard . '</span>
                    </label>
                    ';
                    if (!$form->inlineLabels) {
                        $response .= '<br/>';
                    }
                    $response .= '<input type="text" size="20" data-stripe="number" class="form-control">
                  </div>
                  <span class="payment-errors"></span>
                  <div class="form-group">
                    <label>
                      <span>' . $form->stripe_label_expiration . ' (MM/YY)</span>
                    </label>
                    ';
                    if (!$form->inlineLabels) {
                        $response .= '<br/>';
                    }
                    $response .= '<input type="text" size="2" data-stripe="exp_month" class="form-control" style="display: inline-block;margin-right: 8px; width: 60px;">
                    <span style="font-size: 24px;"> / </span>
                    <input type="text" size="2" data-stripe="exp_year" class="form-control" style="display: inline-block;margin-left: 8px; width: 60px;">
                  </div>

                  <div class="form-group">
                    <label>
                      <span>' . $form->stripe_label_cvc . '</span>
                    </label>
                    ';
                    if (!$form->inlineLabels) {
                        $response .= '<br/>';
                    }
                    $response .= '<input type="text" size="4" data-stripe="cvc"  class="form-control" style="width: 110px;">
                  </div>';
                    if ($form->stripe_logoImg != "") {
                        $response .= '<img class="lfb_stripeLogoImg" alt="Stripe" src="' . $form->stripe_logoImg . '" />';
                    }
                    $response .= '
                  </div>';

                    if ($form->useCaptcha) {
                        $response .= '<div id="lfb_captcha-wrap">
                    <div id="lfb_captchaPanel" class="form-group">
                        <p>' . $form->captchaLabel . '</p>
                        <img src="' . esc_url(trailingslashit(plugins_url('/includes/captcha/', $this->file))) . 'get_captcha.php' . '" alt="Captcha" id="lfb_captcha" />
                        <a href="javascript:" id="lfb_captcha_refresh" onclick="lfb_changeCaptcha(' . $form->id . ');"><span class="glyphicon glyphicon-refresh"></span></a><br/>
                        <input type="text" class="form-control" data-required="true" id="lfb_captchaField" />
                    </div>
                </div>';
                    }
                    $response .= '<p style="margin-top: 38px; margin-bottom: -28px;" class="lfb_btnNextContainer"><input type="submit" value="' . $form->last_btn . '"  id="wpe_btnOrderStripe"  class="btn btn-wide btn-primary">';
                    if (count($steps) > 0) {

                        $response .= '<a href="javascript:" class="linkPrevious">' . $previousIcon . $form->previous_step . '</a>';
                    }
                    $response .= '</p>';
                    $response .= '</form>';
                } else if ($form->use_paypal && $form->paymentType != "email") {
                    $useIPN = '';
                    if ($form->paypal_useIpn == 1) {
                        $useIPN = 'data-useipn="1"';
                    }
                    if ($form->paypal_useSandbox == 1) {
                        $response .= '<form id="wtmt_paypalForm" action="https://www.sandbox.paypal.com/cgi-bin/webscr" ' . $useIPN . ' method="post">';
                    } else {
                        $response .= '<form id="wtmt_paypalForm" action="https://www.paypal.com/cgi-bin/webscr" ' . $useIPN . ' method="post">';
                    }
                    if ($form->useCaptcha) {
                        $response .= '<div id="lfb_captcha-wrap">
                    <div id="lfb_captchaPanel" class="form-group">
                        <p>' . $form->captchaLabel . '</p>
                        <img src="' . esc_url(trailingslashit(plugins_url('/includes/captcha/', $this->file))) . 'get_captcha.php' . '" alt="Captcha" id="lfb_captcha" />
                        <a href="javascript:" id="lfb_captcha_refresh" onclick="lfb_changeCaptcha(' . $form->id . ');"><span class="glyphicon glyphicon-refresh"></span></a><br/>
                        <input type="text" class="form-control" data-required="true" id="lfb_captchaField" />
                    </div>
                </div>';
                    }
                    $response .= '<p style="" class="text-center lfb_btnNextContainer">'
                            . '<a href="javascript:" id="btnOrderPaypal" class="btn btn-wide btn-primary">' . $finalIcon . $form->last_btn . '</a>';
                    if (count($steps) > 0) {
                        $response .= '<a href="javascript:" class="linkPrevious">' . $previousIcon . $form->previous_step . '</a>';
                    }
                    $response .= '</p>
                            <input type="submit" style="display: none;" name="submit"/>
                            <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">';
                    if ($form->isSubscription == 1) {
                        $response .= '<input type="hidden" name="cmd" value="_xclick-subscriptions">
                            <input type="hidden" name="no_note" value="1">
                            <input type="hidden" name="src" value="1">';

                        $response .= '<input type="hidden" name="a3" value="15.00">
                            <input type="hidden" name="p3" value="' . $form->paypal_subsFrequency . '">
                            <input type="hidden" name="t3" value="' . $form->paypal_subsFrequencyType . '">
                            <input type="hidden" name="bn" value="PP-SubscriptionsBF:btn_subscribeCC_LG.gif:NonHostedGuest">';
                    } else {
                        $response .= '<input type="hidden" name="cmd" value="_xclick">
                            <input type="hidden" name="amount" value="1">';
                    }
                    $lang = '';
                    if ($form->paypal_languagePayment != "") {
                        $lang = '<input type="hidden" name="lc" value="' . $form->paypal_languagePayment . '"><input type="hidden" name="country" value="' . $form->paypal_languagePayment . '">';
                    }
                    $response .= '<input type="hidden" name="business" value="' . $form->paypal_email . '">
                            <input type="hidden" name="business_cs_email" value="' . $form->paypal_email . '">
                            <input type="hidden" name="item_name" value="' . $form->title . '">
                            <input type="hidden" name="item_number" value="A00001">
                            <input type="hidden" name="charset" value="utf-8">
                            <input type="hidden" name="no_shipping" value="1">
                            <input type="hidden" name="cn" value="Message">
                            <input type="hidden" name="custom" value="Form content">
                            <input type="hidden" name="currency_code" value="' . $form->paypal_currency . '">
                            <input type="hidden" name="return" value="' . $form->close_url . '">
                                ' . $lang . '
                        </form>';
                } else if ($form->gravityFormID == 0) {
                    if ($form->useCaptcha) {
                        $response .= '<div id="lfb_captcha-wrap">
                    <div id="lfb_captchaPanel" class="form-group">
                        <p>' . $form->captchaLabel . '</p>
                        <img src="' . esc_url(trailingslashit(plugins_url('/includes/captcha/', $this->file))) . 'get_captcha.php' . '" alt="Captcha" id="lfb_captcha" />
                        <a href="javascript:" id="lfb_captcha_refresh" onclick="lfb_changeCaptcha(' . $form->id . ');"><span class="glyphicon glyphicon-refresh"></span></a><br/>
                        <input type="text" class="form-control" data-required="true" id="lfb_captchaField" />
                    </div>
                </div>';
                    }
                    $response .= '<p style="margin-top: 22px; position: absolute; width: 100%;" class="text-center lfb_btnNextContainer">'
                            . '<a href="javascript:" id="wpe_btnOrder" class="btn btn-wide btn-primary">' . $finalIcon . $form->last_btn . '</a>';
                    if (count($steps) > 0) {
                        $response .= '<a href="javascript:" class="linkPrevious">' . $previousIcon . $form->previous_step . '</a>';
                    }
                    $response .= '</p>';
                }
                /* if (count($steps) > 0) {
                  $response .= '<div><a href="javascript:" class="linkPrevious">' . $form->previous_step . '</a></div>';
                  } */
                $response .= '</p>';
            }
        }
        $response .= '</div>';
        $response .= '</div>';
        $response .= '</div>';
        $response .= '</div>';
        $response .= '</div>';


        $response .= '</div>';
        $response .= '</div>';
        $response .= '</div>';
        /* end */


        return $response;
    }

    private function getFormatedPrice($price, $form) {
        $formatedPrice = $price;
        $priceNoDecimals = $formatedPrice;
        $decimals = "";
        if (strpos($formatedPrice, '.') > 0) {
            $formatedPrice = number_format($formatedPrice, 2, ".", "");
            $priceNoDecimals = substr($formatedPrice, 0, strpos($formatedPrice, '.'));
            $decimals = substr($formatedPrice, strpos($formatedPrice, '.') + 1, 2);
            $formatedPrice = str_replace(".", $form->decimalsSeparator, $formatedPrice);
            if (strlen($decimals) == 1) {
                
            }
            if (strlen($priceNoDecimals) > 9) {
                $formatedPrice = substr($priceNoDecimals, 0, -9) . $form->billionsSeparator . substr($priceNoDecimals, 0, -6) . $form->millionSeparator . substr($priceNoDecimals, 0, -3) . $form->thousandsSeparator . substr($priceNoDecimals, -3) . $form->decimalsSeparator . $decimals;
            } else if (strlen($priceNoDecimals) > 6) {
                $formatedPrice = substr($priceNoDecimals, 0, -6) . $form->millionSeparator . substr($priceNoDecimals, -6, -3) . $form->thousandsSeparator . substr($priceNoDecimals, -3) . $form->decimalsSeparator . $decimals;
            } else if (strlen($priceNoDecimals) > 3) {
                $formatedPrice = substr($priceNoDecimals, 0, -3) . $form->thousandsSeparator . substr($priceNoDecimals, -3) . $form->decimalsSeparator . $decimals;
            }
        } else {
            if (strlen($priceNoDecimals) > 9) {
                $formatedPrice = substr($priceNoDecimals, 0, -9) . $form->billionsSeparator . substr($priceNoDecimals, -9, -6) . $form->millionSeparator . substr($priceNoDecimals, -6, -3) . $form->thousandsSeparator . substr($priceNoDecimals, -3);
            } else if (strlen($priceNoDecimals) > 6) {
                $formatedPrice = substr($priceNoDecimals, 0, -6) . $form->millionSeparator . substr($priceNoDecimals, -6, -3) . $form->thousandsSeparator . substr($priceNoDecimals, -3);
            } else if (strlen($priceNoDecimals) > 3) {
                $formatedPrice = substr($priceNoDecimals, 0, -3) . $form->thousandsSeparator . substr($priceNoDecimals, -3);
            }
        }


        return $formatedPrice;
    }

    /*
     * Styles integration
     */

    public function options_custom_styles() {

        $settings = $this->getSettings();
        $output = '';
        $outputJS = '';

        foreach ($this->currentForms as $currentForm) {
            if ($currentForm > 0 && !is_array($currentForm)) {
                $form = $this->getFormDatas($currentForm);
                if ($form) {
                    if (!$form->colorA || $form->colorA == "") {
                        $form->colorA = $settings->colorA;
                    }
                    if (!$form->colorB || $form->colorB == "") {
                        $form->colorB = $settings->colorB;
                    }
                    if (!$form->colorC || $form->colorC == "") {
                        $form->colorC = $settings->colorC;
                    }
                    if (!$form->item_pictures_size || $form->item_pictures_size == "") {
                        $form->item_pictures_size = 64;
                    }

                    if ($form->useGoogleFont && $form->googleFontName != "") {
                        $fontname = str_replace(' ', '+', $form->googleFontName);
                        $output .= '@import url(https://fonts.googleapis.com/css?family=' . $fontname . ':400,700);';

                        $output .= 'body:not(.wp-admin) #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"], html body .lfb_datepickerContainer{';
                        $output .= ' font-family:"' . $form->googleFontName . '"; ';
                        $output .= '}';
                    }

                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]  {';
                    $output .= ' background-color:' . $form->colorPageBg . '; ';
                    $output .= ' color:' . $form->colorB . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .datetimepicker {';
                    $output .= ' background-color:' . $form->colorB . '; ';
                    $output .= ' color:' . $form->colorSecondaryTxt . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .datetimepicker table tr td span.active:active,'
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .datetimepicker table tr td span.active:hover:active, '
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .datetimepicker table tr td span.active.disabled:active,'
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]  .datetimepicker table tr td span.active.disabled:hover:active, '
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .datetimepicker table tr td span.active.active, '
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .datetimepicker table tr td span.active:hover.active, #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .datetimepicker table tr td span.active.disabled.active,'
                            . ' #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .datetimepicker table tr td span.active.disabled:hover.active, '
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .datetimepicker table tr td.active:active,'
                            . ' #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .datetimepicker table tr td.active:hover, '
                            . ' #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .datetimepicker table tr td.active:hover:active, '
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .datetimepicker table tr td.active.disabled:active, '
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .datetimepicker table tr td.active.disabled:hover:active,'
                            . ' #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .datetimepicker table tr td.active.active, '
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .datetimepicker table tr td.active:hover.active,'
                            . ' #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .datetimepicker table tr td.active.disabled.active,'
                            . ' #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .datetimepicker table tr td.active.disabled:hover.active,'
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]  .datetimepicker table tr td.day:hover,'
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]  .datetimepicker table tr th.day:hover,'
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]  .datetimepicker table tr td span:hover,'
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]  .datetimepicker table tr th span:hover {';
                    $output .= ' background-color:' . $form->colorA . '; ';
                    $output .= '}';
                    $output .= "\n";

                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .datetimepicker thead tr:first-child th:hover {';
                    $output .= ' background-color:' . $form->colorA . ' !important; ';
                    $output .= '}';
                    $output .= "\n";


                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel {';
                    $output .= ' background-color:' . $form->colorBg . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #lfb_loader {';
                    $output .= ' background-color:' . $form->colorA . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .genSlide .lfb_imgTitle  {';
                    $output .= ' color:' . $form->colorA . '; ';
                    $output .= '}';
                    $output .= "\n";

                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .genSlide .lfb_totalBottomContainer hr  {';
                    $output .= ' border-color:' . $form->colorC . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel .genSlide .genContent div.selectable span.icon_select.lfb_fxZoom  {';
                    $output .= ' text-shadow: -2px 0px ' . $form->colorBg . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#lfb_bootstraped #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .lfb_stripeContainer {';
                    $output .= ' border-color: ' . $form->colorSecondary . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel #lfb_payFormFinalTxt {';
                    $output .= ' color: ' . $form->colorB . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#lfb_bootstraped #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #lfb_floatingSummary:before {';
                    $output .= '  border-color: transparent transparent ' . $form->colorA . ' transparent; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#lfb_bootstraped #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #lfb_floatingSummaryInner {';
                    $output .= '  border-color: ' . $form->colorA . ';';
                    $output .= '}';
                    $output .= "\n";


                    $fieldsColor = $form->colorC;
                    if (strtolower($fieldsColor) == '#ffffff') {
                        $fieldsColor = '#bdc3c7';
                    }
                    $output .= 'body #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .form-control,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel ,'
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] p,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #lfb_summary tbody td,'
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #lfb_summary tbody #sfb_summaryTotalTr th:not(#lfb_summaryTotal) {';
                    $output .= ' color:' . $fieldsColor . '; ';
                    $output .= '}';
                    $output .= "\n";


                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]  .tooltip .tooltip-inner,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   #mainPanel .genSlide .genContent div.selectable span.icon_quantity,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .dropdown-inverse {';
                    $output .= ' background-color:' . $form->colorB . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .tooltip.top .tooltip-arrow {';
                    $output .= ' border-top-color:' . $form->colorB . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .tooltip.bottom .tooltip-arrow {';
                    $output .= ' border-bottom-color:' . $form->colorB . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .btn-primary,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .gform_button,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .btn-primary:hover,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .btn-primary:active,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .genPrice .progress .progress-bar-price,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .progress-bar,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .quantityBtns a,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .btn-primary:active,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .btn-primary.active,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .open .dropdown-toggle.btn-primary,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .dropdown-inverse li.active > a,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .dropdown-inverse li.selected > a,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .btn-primary:active,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]
                    .btn-primary.active,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .open .dropdown-toggle.btn-primary,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .btn-primary:hover,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .btn-primary:focus,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .btn-primary:active,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .btn-primary.active,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .open .dropdown-toggle.btn-primary {';
                    $output .= ' background-color:' . $form->colorA . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .form-group.lfb_focus .form-control, #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .lfb_dropzone:focus,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .has-switch > div.switch-on label,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .form-group.focus .form-control,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]  .form-control:focus {';
                    $output .= ' border-color:' . $form->colorA . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] a:not(.btn),#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   a:not(.btn):hover,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   a:not(.btn):active,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   #mainPanel .genSlide .genContent div.selectable.checked span.icon_select,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   #mainPanel #finalPrice,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .ginput_product_price,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .checkbox.checked,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .radio.checked,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .checkbox.checked .second-icon,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .radio.checked .second-icon {';
                    $output .= ' color:' . $form->colorA . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   #mainPanel .genSlide .genContent div.selectable .img {';
                    $output .= ' max-width:' . $form->item_pictures_size . 'px; ';
                    $output .= ' max-height:' . $form->item_pictures_size . 'px; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   #mainPanel .genSlide .genContent div.selectable .img.lfb_imgSvg {';
                    $output .= ' min-width:' . $form->item_pictures_size . 'px; ';
                    $output .= '}';
                    $output .= "\n";

                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   #mainPanel,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .form-control {';
                    $output .= ' color:' . $form->colorC . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .form-control,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .lfb_dropzone  {';
                    $output .= ' color:' . $form->colorC . '; ';
                    $output .= ' border-color:' . $form->colorSecondary . '; ';
                    $output .= '}';
                    $output .= "\n";

                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]  .input-group-addon {';
                    $output .= ' background-color:' . $form->colorSecondary . '; ';
                    $output .= 'color:' . $form->colorSecondaryTxt . '; ';
                    $output .= ' border-color:' . $form->colorSecondary . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"][data-stylefields="light"]  .input-group-addon,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"][data-stylefields="light"] .form-control {';
                    $output .= ' background-color:transparent; ';
                    $output .= 'color:' . $form->colorA . '; ';
                    $output .= '}';
                    $output .= "\n";

                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]  .lfb_dropzone .dz-preview .dz-remove {';
                    $output .= ' color:' . $form->colorC . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .btn-default,'
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .has-switch span.switch-right,'
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .bootstrap-datetimepicker-widget .has-switch span.switch-right,'
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .dropdown-menu:not(.datetimepicker) {';
                    $output .= ' background-color:' . $form->colorSecondary . '; ';
                    $output .= ' color:' . $form->colorSecondaryTxt . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .lfb_bootstrap-select.btn-group .dropdown-menu li a{';
                    $output .= ' color:' . $form->colorSecondaryTxt . '; ';
                    $output .= '}';
                    $output .= "\n";

                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .lfb_bootstrap-select.btn-group .dropdown-menu li.selected> a,'
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .lfb_bootstrap-select.btn-group .dropdown-menu li.selected> a:hover{';
                    $output .= ' background-color:' . $form->colorA . '; ';
                    $output .= '}';
                    $output .= "\n";

                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .has-switch>div.switch-off label{';
                    $output .= ' border-color:' . $form->colorSecondary . '; ';
                    $output .= ' background-color:' . $form->colorCbCircle . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .has-switch>div.switch-on label{';
                    $output .= ' background-color:' . $form->colorCbCircleOn . '; ';
                    $output .= '}';
                    $output .= "\n";

                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .btn-default .bs-caret > .caret {';
                    $output .= '  border-bottom-color:' . $form->colorSecondaryTxt . '; ';
                    $output .= '  border-top-color:' . $form->colorSecondaryTxt . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .genPrice .progress .progress-bar-price  {';
                    $output .= ' font-size:' . $form->priceFontSize . 'px; ';
                    $output .= '}';
                    $output .= "\n";
                    $maxWidth = 240;
                    if ($form->item_pictures_size > $maxWidth) {
                        $maxWidth = $form->item_pictures_size;
                    }
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .itemDes  {';
                    $output .= ' max-width:' . ($maxWidth) . 'px; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel .genSlide .genContent div.selectable .wpe_itemQtField  {';
                    $output .= ' width:' . ($form->item_pictures_size) . 'px; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel .genSlide .genContent div.selectable .wpe_itemQtField .wpe_qtfield  {';
                    $output .= ' margin-left:' . (0 - (100 - ($form->item_pictures_size)) / 2) . 'px; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= 'body .lfb_datepickerContainer .ui-datepicker-title { ';
                    $output .= ' background-color:' . $form->colorA . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= 'body .lfb_datepickerContainer td a {';
                    $output .= ' color:' . $form->colorA . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= 'body .lfb_datepickerContainer  td.ui-datepicker-today a {';
                    $output .= ' color:' . $form->colorB . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .has-switch span.switch-left {';
                    $output .= ' background-color:' . $form->colorA . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel #lfb_summary table thead,'
                            . ' #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]  #lfb_floatingSummaryContent table thead{';
                    $output .= ' background-color:' . $form->colorA . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel #lfb_summary table th.sfb_summaryStep,'
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #lfb_floatingSummaryContent table th.sfb_summaryStep {';
                    $output .= ' background-color:' . $fieldsColor . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel #lfb_summary table #lfb_summaryTotal,'
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #lfb_floatingSummaryContent table #lfb_summaryTotal  {';
                    $output .= ' color:' . $form->colorA . '; ';
                    $output .= '}';
                    $output .= "\n";

                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]:not([data-stylefields="light"]) .form-group.lfb_focus .input-group-addon, #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .form-group.focus .input-group-addon,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .bootstrap-datetimepicker-widget .form-group.focus .input-group-addon,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]:not([data-stylefields="light"]) .input-group.focus .input-group-addon,.bootstrap-datetimepicker-widget .input-group.focus .input-group-addon {';
                    $output .= ' background-color:' . $form->colorA . '; ';
                    $output .= ' border-color:' . $form->colorA . '; ';
                    $output .= '}';
                    $output .= "\n";

                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"][data-stylefields="light"] .form-group.lfb_focus .input-group-addon,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"][data-stylefields="light"] .form-group .focus .input-group-addon {';
                    $output .= ' color:' . $form->colorA . '; ';
                    $output .= ' border-color:' . $form->colorA . '; ';
                    $output .= '}';
                    $output .= "\n";


                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel .wpe_sliderQt {';
                    $output .= ' background-color:' . $form->colorC . '; ';
                    $output .= '}';
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel [data-type="slider"] {';
                    $output .= ' background-color:' . $form->colorC . '; ';
                    $output .= '}';
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel .wpe_sliderQt .ui-slider-range,'
                            . ' #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel .wpe_sliderQt .ui-slider-handle, '
                            . ' #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel [data-type="slider"] .ui-slider-range,'
                            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel [data-type="slider"] .ui-slider-handle {';
                    $output .= ' background-color:' . $form->colorA . ' ; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel #finalPrice span:nth-child(2) {';
                    $output .= ' color:' . $form->colorC . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .lfb_colorPreview {';
                    $output .= ' border-color:' . $form->colorC . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#lfb_bootstraped.lfb_bootstraped[data-form="' . $form->id . '"] #estimation_popup[data-previousstepbtn="true"] .linkPrevious {';
                    $output .= ' background-color:' . $form->colorSecondary . '; ';
                    $output .= ' color:' . $form->colorSecondaryTxt . '; ';
                    $output .= '}';
                    $output .= "\n";


                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] > .bootstrap-timepicker-widget  {';
                    $output .= ' color:' . $form->colorSecondaryTxt . '; ';
                    $output .= ' background-color:' . $form->colorSecondary . '; ';
                    $output .= '}';
                    $output .= "\n";
                    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] [class*="datetimepicker-dropdown"]:before,'
                            . '#lfb_bootstraped.lfb_bootstraped [class*=" datetimepicker-dropdown"]:after {';
                    $output .= ' border-bottom-color:' . $form->colorB . '; ';
                    $output .= '}';
                    $output .= "\n";


                    if ($form->columnsWidth > 0) {
                        $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .genContent .col-md-2{';
                        $output .= ' width:' . $form->columnsWidth . 'px; ';
                        $output .= '}';
                        $output .= "\n";
                    }

                    if ($form->inverseGrayFx) {
                        $output .= 'body #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel .genSlide div.selectable:not(.checked) .img {
                            -webkit-filter: grayscale(100%);
                            -moz-filter: grayscale(100%);
                            -ms-filter: grayscale(100%);
                            -o-filter: grayscale(100%);
                            filter: grayscale(100%);
                            filter: gray;
                        }
                        body #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel .genSlide div.selectable.checked .img {
                                -webkit-filter: grayscale(0%);
                            -moz-filter: grayscale(0%);
                            -ms-filter: grayscale(0%);
                            -o-filter: grayscale(0%);
                            filter: grayscale(0%);
                            filter: none;
                        }';
                    }
                    if ($form->customCss != "") {
                        $output .= $form->customCss;
                        $output .= "\n";
                    }
                    if ($form->formStyles != '') {
                        $output .= $form->formStyles;
                        $output .= "\n";
                    }


                    if ($form->customJS != "" && !isset($_POST['action'])) {
                        $outputJS = "\n<script>\n" . $form->customJS . "</script>\n";
                    }
                }
            }
        }
        if ($output != '') {
            $output = "\n<style >\n" . $output . "</style>\n";
            echo $output;
        }
        if ($outputJS != '') {
            echo $outputJS;
        }
    }

    private function isUpdated() {
        $settings = $this->getSettings();
        if ($settings->updated) {
            return false;
        } else {
            return true;
        }
    }

    public function frontend_enqueue_scripts($hook = '') {
        $settings = $this->getSettings();

        wp_register_script($this->_token . '-frontend', esc_url($this->assets_url) . 'js/lfb_frontend.min.js', array('jquery'), $this->_version);
        wp_enqueue_script($this->_token . '-frontend');

        if (isset($_GET['lfb_action']) && $_GET['lfb_action'] == 'preview') {

            wp_register_script($this->_token . '-designerFrontend', esc_url($this->assets_url) . 'js/lfb_formDesigner_frontend.min.js', array('jquery'), $this->_version);
            wp_enqueue_script($this->_token . '-designerFrontend');
        }
        if ($this->modeManageData) {

            wp_register_script($this->_token . '-frontend-libs', esc_url($this->assets_url) . 'js/lfb_frontendPackedLibs.min.js', array("jquery-ui-core", "jquery-ui-tooltip", "jquery-ui-slider", "jquery-ui-position", "jquery-ui-datepicker"), $this->_version);
            wp_enqueue_script($this->_token . '-frontend-libs');

            wp_register_script($this->_token . '-manageDatas', esc_url($this->assets_url) . 'js/lfb_manageDatas.min.js', array('jquery'), $this->_version);
            wp_enqueue_script($this->_token . '-manageDatas');
            wp_localize_script($this->_token . '-manageDatas', 'lfb_dataMan', array(
                'homeUrl' => get_site_url(),
                'ajaxurl' => admin_url('admin-ajax.php'),
                'txtCustomersDataWarningText' => $settings->txtCustomersDataWarningText,
                'txtCustomersDataDownloadLink' => $settings->txtCustomersDataDownloadLink,
                'txtCustomersDataDeleteLink' => $settings->txtCustomersDataDeleteLink,
                'txtCustomersDataLeaveLink' => $settings->txtCustomersDataLeaveLink,
                'customersDataDeleteDelay' => $settings->customersDataDeleteDelay,
                'customersDataLabelEmail' => $settings->customersDataLabelEmail,
                'customersDataLabelPass' => $settings->customersDataLabelPass,
                'customersDataLabelModify' => $settings->customersDataLabelModify,
                'txtCustomersDataTitle' => $settings->txtCustomersDataTitle,
                'txtCustomersDataEditLink' => $settings->txtCustomersDataEditLink,
                'customersDataLabelEmail' => $settings->customersDataLabelEmail,
                'customersDataLabelPass' => $settings->customersDataLabelPass,
                'txtCustomersDataForgotPassLink' => $settings->txtCustomersDataForgotPassLink,
                'txtCustomersDataForgotPassSent' => $settings->txtCustomersDataForgotPassSent,
                'txtCustomersDataModifyValidConfirm' => $settings->txtCustomersDataModifyValidConfirm,
            ));
        }
        if ($this->formToPayKey != "") {

            global $wpdb;
            $table_name = $wpdb->prefix . "wpefc_forms";
            $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE id=$this->formToPayID LIMIT 1");
            if (count($rows) > 0) {
                $form = $rows[0];

                $table_name = $wpdb->prefix . "wpefc_logs";
                $logs = $wpdb->get_results("SELECT * FROM $table_name WHERE paymentKey='$this->formToPayKey' LIMIT 1");
                if (count($logs) > 0) {
                    $log = $logs[0];

                    if ($form->use_stripe) {
                        wp_enqueue_script($this->_token . '-stripe', 'https://js.stripe.com/v2/', true, 3);
                    }

                    wp_register_script($this->_token . '-payForm', esc_url($this->assets_url) . 'js/lfb_payForm.min.js', array('jquery'), $this->_version);
                    wp_enqueue_script($this->_token . '-payForm');
                    wp_localize_script($this->_token . '-payForm', 'lfb_dataPay', array(
                        'homeUrl' => get_site_url(),
                        'key' => $this->formToPayKey,
                        'ajaxurl' => admin_url('admin-ajax.php'), 'formID' => $this->formToPayID,
                        'stripePubKey' => $form->stripe_publishKey, 'finalText' => $form->txt_payFormFinalTxt,
                        'finalUrl' => $log->finalUrl,
                        'redirectionDelay' => $form->redirectionDelay,
                        'total' => $log->totalPrice,
                        'totalSub' => $log->totalSubscription,
                        'ref' => $log->ref,
                        'percentToPay' => $form->percentToPay));
                }
            }
        }
    }

    /* Ajax : get Current ref */

    public function get_currentRef() {
        $rep = false;
        $settings = $this->getSettings();
        if (isset($_POST['formID']) && !is_array($_POST['formID'])) {
            $formID = sanitize_text_field($_POST['formID']);

            global $wpdb;
            $table_name = $wpdb->prefix . "wpefc_forms";
            $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE id=$formID LIMIT 1");
            $form = $rows[0];
            $current_ref = $form->current_ref + 1;
            $wpdb->update($table_name, array('current_ref' => $current_ref), array('id' => $form->id));
            $rep = $form->ref_root . $current_ref;
        }
        echo $rep;
        die();
    }

    private function lfb_sanitizeFilename($filename) {
        $filename = preg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);
        $filename = preg_replace("([\.]{2,})", '', $filename);
        return $filename;
    }

    private function lfb_generatePdfAdmin($order, $form) {
        $settings = $this->getSettings();
        $contentPdf = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><style>body,*{font-family: "dejavu sans" !important; } hr{color: #ddd; border-color: #ddd;} table{width: 100% !important; line-height: 18px;} table td, table th{width: auto!important; border: 1px solid #ddd; line-height: 16px; overflow-wrap: break-word;}table td,table tbody th  {padding-top: 2px !important; padding-bottom: 6px !important} table thead th {padding: 8px;line-height: 18px;}</style></head><body>' . $this->stringDecode($order->pdfContent, $settings->encryptDB) . '</body></html>';
        $contentPdf = str_replace('border="1"', '', $contentPdf);
        $upDir = wp_upload_dir();
        $contentPdf = str_replace('src="' . get_site_url() . '/wp-content/uploads/', 'src="' . $upDir['basedir'] . '/', $contentPdf);

        if (!$order->paid) {
            $txt_orderType = $form->txt_quotation;
        }
        $contentPdf = str_replace("[order_type]", $txt_orderType, $contentPdf);

        require_once("dompdf/dompdf_config.inc.php");
        $dompdf = new DOMPDF();
        //  $contentPdf = mb_convert_encoding($contentPdf, 'HTML-ENTITIES', 'UTF-8');
        $dompdf->load_html($contentPdf, 'UTF-8');
        $dompdf->set_paper('a4', 'portrait');
        $dompdf->render();
        $fileName = $this->lfb_sanitizeFilename($form->title) . '-' . $order->ref . '-' . uniqid() . '.pdf';
        $output = $dompdf->output();
        file_put_contents($this->dir . '/uploads/' . $fileName, $output);
        return ($this->dir . '/uploads/' . $fileName);
    }

    private function lfb_generatePdfCustomer($order, $form) {
        $settings = $this->getSettings();
        $contentPdf = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><style>body,*{font-family: "dejavu sans" !important; } hr{color: #ddd; border-color: #ddd;} table{width: 100% !important; line-height: 18px;} table td, table th{width: auto!important; border: 1px solid #ddd; line-height: 16px;overflow-wrap: break-word;}table td,table tbody th  {padding-top: 2px !important; padding-bottom: 6px !important} table thead th {padding: 8px;line-height: 18px;}</style></head><body>' . $this->stringDecode($order->pdfContentUser, $settings->encryptDB) . '</body></html>';

        $contentPdf = str_replace('border="1"', '', $contentPdf);
        $upDir = wp_upload_dir();
        $contentPdf = str_replace('src="' . get_site_url() . '/wp-content/uploads/', 'src="' . $upDir['basedir'] . '/', $contentPdf);

        if (!$order->paid) {
            $txt_orderType = $form->txt_quotation;
        }
        $contentPdf = str_replace("[order_type]", $txt_orderType, $contentPdf);

        require_once("dompdf/dompdf_config.inc.php");
        $dompdf = new DOMPDF();
        $dompdf->load_html($contentPdf, 'UTF-8');
        $dompdf->set_paper('a4', 'portrait');
        $dompdf->render();
        $fileName = $this->lfb_sanitizeFilename($form->title) . '-' . $order->ref . '-' . uniqid() . '.pdf';
        $output = $dompdf->output();
        file_put_contents($this->dir . '/uploads/' . $fileName, $output);
        return ($this->dir . '/uploads/' . $fileName);
    }

    // Send email to admin & customer
    private function sendOrderEmail($orderRef, $formID) {
        global $wpdb;
        global $_currentFormID;

        $table_name = $wpdb->prefix . "wpefc_logs";
        $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE ref='$orderRef' AND formID='$formID' LIMIT 1");
        if (count($rows) > 0) {

            $settings = $this->getSettings();

            $order = $rows[0];
            $order->email = $this->stringDecode($order->email, $settings->encryptDB);
            $order->content = $this->stringDecode($order->content, $settings->encryptDB);
            $order->contentUser = $this->stringDecode($order->contentUser, $settings->encryptDB);
            $order->address = $this->stringDecode($order->address, $settings->encryptDB);
            $order->zip = $this->stringDecode($order->zip, $settings->encryptDB);
            $order->city = $this->stringDecode($order->city, $settings->encryptDB);
            $order->country = $this->stringDecode($order->country, $settings->encryptDB);

            $table_name = $wpdb->prefix . "wpefc_forms";
            $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE id=$order->formID LIMIT 1");
            $form = $rows[0];
            if (strlen($order->eventsData) > 2) {
                $eventsData = json_decode($order->eventsData);
                foreach ($eventsData as $eventData) {
                    $customerAddress = $order->address . ' ' . $order->zip . ' ' . $order->city . ' , ' . $order->country;
                    if (strlen(str_replace(' ', '', $customerAddress)) < 3) {
                        $customerAddress = '';
                    }
                    
                    if(is_null($eventData->fullDay)){
                        $eventData->fullDay = 0;
                    }

                    $table_nameEv = $wpdb->prefix . "wpefc_calendarEvents";
                    $wpdb->insert($table_nameEv, array(
                        'calendarID' => $eventData->calendarID,
                        'title' => $eventData->title,
                        'startDate' => $eventData->startDate,
                        'endDate' => $eventData->endDate,
                        'fullDay' => $eventData->fullDay,
                        'orderID' => $order->id,
                        'isBusy' => $eventData->isBusy,
                        'categoryID' => $eventData->categoryID,
                        'customerEmail' => $this->stringEncode($order->email, $settings->encryptDB),
                        'customerAddress' => $this->stringEncode($customerAddress, $settings->encryptDB)
                    ));
                    $eventID = $wpdb->insert_id;
                    $table_nameR = $wpdb->prefix . "wpefc_calendarReminders";
                    $remindersData = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_nameR WHERE eventID=0 AND calendarID=%s", $eventData->calendarID));
                    foreach ($remindersData as $reminder) {
                        $reminder->eventID = $eventID;
                        unset($reminder->id);
                        $wpdb->insert($table_nameR, (array) $reminder);
                    }
                }
            }


            $txt_orderType = $form->txt_invoice;
            if (!$order->paid) {
                $txt_orderType = $form->txt_quotation;
            }
            $order->content = str_replace("[order_type]", $txt_orderType, $order->content);
            $order->contentUser = str_replace("[order_type]", $txt_orderType, $order->contentUser);

            if ($form->enableCustomersData) {
                if (strpos($order->contentUser, '[gdpr_link]') !== false) {
                    $customersDataUrl = get_site_url() . '/?EPFormsBuilder=checkMyData&e=' . $order->email;
                    $order->contentUser = str_replace("[gdpr_link]", $customersDataUrl, $order->contentUser);
                } else {
                    $customersDataEmailLink = '<hr/><p style="color:#bdc3c7;font-style: italic; font-size: 11px;">' . $form->customersDataEmailLink . '</p>';
                    $customersDataUrl = get_site_url() . '/?EPFormsBuilder=checkMyData&e=' . $order->email;
                    $customersDataEmailLink = str_replace("[url]", '<a href="' . $customersDataUrl . '" style="color: #bdc3c7;">' . $customersDataUrl . '</a>', $customersDataEmailLink);
                    $order->contentUser .= '<div>' . $customersDataEmailLink . '</div>';
                }
            } else {
                $order->contentUser = str_replace("[gdpr_link]", '', $order->contentUser);
            }
            $order->content = str_replace("[gdpr_link]", '', $order->content);

            add_filter('wp_mail_content_type', create_function('', 'return "text/html"; '));
            $headers = "";
            if ($order->email != "") {
                $headers .= "Reply-to: " . $order->email . "\n";
            }
            if (strpos($form->email, ',') > 0) {
                $emailsArr = explode(',', $form->email);
                $form->email = $emailsArr;
            }


            if (!$order->paid && $form->paymentType == "email" && ($order->totalPrice > 0 || $order->totalSubscription > 0)) {
                $paymentLink = '';
                $paymentUrl = get_site_url() . '/?EPFormsBuilder=payOrder&h=' . $order->paymentKey;
                if ($form->emailPaymentType == 'checkbox') {
                    $paymentLink = '<p><a href="' . $paymentUrl . '">' . $form->enableEmailPaymentText . '<input type="checkbox" style="vertical-align:middle;"  /></a></p>';
                } else if ($form->emailPaymentType == 'button') {
                    $paymentLink = '<p><a href="' . $paymentUrl . '" style="padding: 14px;border-radius: 4px; background-color: ' . $form->colorA . ';color: #fff; text-decoration:none;">' . $form->enableEmailPaymentText . '</a></p>';
                } else if ($form->emailPaymentType == 'link') {
                    $paymentLink = '<p><a href="' . $paymentUrl . '">' . $form->enableEmailPaymentText . '</a></p>';
                }
                $paymentLink = '<div style="text-align:center;margin-bottom: 28px;">' . $paymentLink . '</div>';

                if (strpos($order->contentUser, '[payment_link]') !== false) {
                    $order->contentUser = str_replace("[payment_link]", $paymentLink, $order->contentUser);
                } else {
                    $order->contentUser .= $paymentLink;
                }
            } else {
                $order->contentUser = str_replace("[payment_link]", "", $order->contentUser);
            }

            $content = str_replace("[payment_link]", "", $order->content);

            //$order->content= chunk_split(base64_encode($order->content));
            //$headers .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $attachmentAdmin = array();
            if ($form->sendPdfAdmin) {

                try {
                    $attachmentAdmin[] = $this->lfb_generatePdfAdmin($order, $form);
                } catch (Exception $ex) {
                    
                }
            }
            if ($form->email_name != "") {
                $_currentFormID = $formID;
                add_filter('wp_mail_from_name', array($this, 'wpb_sender_name'));
            }

            $_currentFormID = $formID;
            add_filter('wp_mail_from', array($this, 'wpb_sender_email'));

            if (wp_mail($form->email, $form->email_subject . ' - ' . $order->ref, $order->content, $headers, $attachmentAdmin)) {
                if (count($attachmentAdmin) > 0) {
                    unlink($attachmentAdmin[0]);
                }
            }

            if ($order->sendToUser && $order->email != '') {
                $attachmentCustomer = array();
                if ($form->sendPdfCustomer) {
                    try {
                        $attachmentCustomer[] = $this->lfb_generatePdfCustomer($order, $form);
                    } catch (Exception $ex) {
                        
                    }
                }
                $headers = "";
                if ($form->email_name != "") {
                    global $_currentFormID;
                    $_currentFormID = $formID;
                    add_filter('wp_mail_from_name', array($this, 'wpb_sender_name'));
                }
                $_currentFormID = $formID;
                add_filter('wp_mail_from', array($this, 'wpb_sender_email'));

                add_filter('wp_mail_content_type', create_function('', 'return "text/html"; '));
                if (wp_mail($order->email, $form->email_userSubject, $order->contentUser, $headers, $attachmentCustomer)) {
                    if (count($attachmentCustomer) > 0) {
                        unlink($attachmentCustomer[0]);
                    }
                }
            }

            $table_name = $wpdb->prefix . "wpefc_logs";
            $wpdb->update($table_name, array('checked' => true), array('id' => $order->id));
        }
    }

    public function customDataToWooOrder($product_name, $values, $cart_item_key) {
        if (count($values['lfbRef']) > 0) {
            $return_string = $product_name . "</a><dl class='variation'>";
            $return_string .= "<table class='wdm_options_table' id='" . $values['product_id'] . "'>";
            $return_string .= "<tr><td>" . __('Form ref', 'lfb') . ' : ' . $values['lfbRef'] . "</td></tr>";
            $return_string .= "</table></dl>";
            return $return_string;
        } else {
            return $product_name;
        }
    }

    public function customDataToWooFinalOrder($item_id, $values) {
        global $woocommerce, $wpdb;
        $user_custom_values = $values['lfbRef'];
        if (!empty($user_custom_values)) {
            wc_add_order_item_meta($item_id, __('Form ref', 'lfb'), $user_custom_values);
        }
    }

    public function removeCustomDataWoo($cart_item_key) {
        global $woocommerce;
        // Get cart
        $cart = $woocommerce->cart->get_cart();
        // For each item in cart, if item is upsell of deleted product, delete it
        foreach ($cart as $key => $values) {
            if ($values['lfbRef'] == $cart_item_key)
                unset($woocommerce->cart->cart_contents[$key]);
        }
    }

    public function wpb_sender_name($name) {
        global $wpdb;
        global $_currentFormID;
        if ($_currentFormID > 0) {
            $table_name = $wpdb->prefix . "wpefc_forms";
            $rows = $wpdb->get_results("SELECT id,email_name FROM $table_name WHERE id=$_currentFormID LIMIT 1");
            $form = $rows[0];
            return $form->email_name;
        } else {
            return $name;
        }
    }

    public function wpb_sender_email($name) {
        global $wpdb;
        global $_currentFormID;
        if ($_currentFormID > 0) {
            $table_name = $wpdb->prefix . "wpefc_forms";
            $rows = $wpdb->get_results("SELECT id,email FROM $table_name WHERE id=$_currentFormID LIMIT 1");
            $form = $rows[0];
            return $form->email;
        } else {
            return $name;
        }
    }

    /*
     * Ajax : send email
     */

    public function send_email() {
        global $wpdb;
        $settings = $this->getSettings();
        $formID = sanitize_text_field($_POST['formID']);
        $formSession = sanitize_text_field(($_POST['formSession']));
        $phone = sanitize_text_field($_POST['phone']);
        $firstName = sanitize_text_field($_POST['firstName']);
        $lastName = sanitize_text_field($_POST['lastName']);
        $address = sanitize_text_field($_POST['address']);
        $city = sanitize_text_field($_POST['city']);
        $country = sanitize_text_field($_POST['country']);
        $state = sanitize_text_field($_POST['state']);
        $zip = sanitize_text_field($_POST['zip']);
        $email = sanitize_text_field($_POST['email']);
        $contentTxt = sanitize_text_field($_POST['contentTxt']);
        $contactSent = $_POST['contactSent'];
        $activatePaypal = $_POST['activatePaypal'];
        $finalUrl = sanitize_text_field($_POST['finalUrl']);

        $total = sanitize_text_field($_POST['total']);
        $totalSub = sanitize_text_field($_POST['totalSub']);
        $subFrequency = sanitize_text_field($_POST['subFrequency']);
        $formTitle = sanitize_text_field($_POST['formTitle']);
        $stripeToken = sanitize_text_field($_POST['stripeToken']);
        $stripeTokenB = sanitize_text_field($_POST['stripeTokenB']);
        $events = stripslashes($_POST['eventsData']);
        $itemsArray = $_POST['items'];

        $usePaypalIpn = false;
        if (isset($_POST['usePaypalIpn']) && $_POST['usePaypalIpn'] == '1') {
            $usePaypalIpn = true;
        }
        $sendUser = 0;
        $discountCode = sanitize_text_field($_POST['discountCode']);
        if ($discountCode != "") {
            $table_name = $wpdb->prefix . "wpefc_coupons";
            $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE formID=%s AND couponCode='%s' LIMIT 1", $formID, $discountCode));
            if (count($rows) > 0) {
                $coupon = $rows[0];
                $coupon->currentUses ++;
                if ($coupon->useMax > 0 && $coupon->currentUses >= $coupon->useMax) {
                    $wpdb->delete($table_name, array('id' => $coupon->id));
                } else {
                    $wpdb->update($table_name, array('currentUses' => $coupon->currentUses), array('id' => $coupon->id));
                }
            }
        }

        $table_name = $wpdb->prefix . "wpefc_forms";
        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE id=%s LIMIT 1", $formID));
        $form = $rows[0];
        session_start();
        if (!$form->useCaptcha || ($_SESSION['lfb_random_number'] != "" && strtolower($_SESSION['lfb_random_number']) == strtolower($_POST['captcha']))) {

            $summary = ($_POST['summary']);
            $summaryA = ($_POST['summary']);
            //$summaryPdf = $_POST['summaryPdf'];

            add_filter('safe_style_css', function( $styles ) {
                $styles[] = 'border-color';
                $styles[] = 'background-color';
                $styles[] = 'font-size';
                $styles[] = 'padding';
                $styles[] = 'color';
                $styles[] = 'text-align';
                $styles[] = 'line-height';
                $styles[] = 'margin';
                $styles[] = 'direction';
                return $styles;
            });
            $contentProject = $summary;
            $contentProject = wp_kses($contentProject, array(
                'br' => array(),
                'u' => array(),
                'p' => array(),
                'b' => array(),
                'span' => array('style' => true),
                'strong' => array('style' => true),
                'div' => array('style' => true),
                'td' => array('style' => true, 'align' => true, 'colspan' => true, 'bgcolor' => true, 'color' => true, 'width' => true),
                'thead' => array('style' => true, 'bgcolor' => true),
                'th' => array('style' => true, 'align' => true, 'colspan' => true, 'bgcolor' => true, 'color' => true, 'width' => true, 'id' => true),
                'table' => array('style' => true, 'cellspacing' => true, 'cellpadding' => true, 'border' => true, 'width' => true, 'bordercolor' => true, 'bgcolor' => true),
                'tbody' => array('style' => true),
                'tr' => array('style' => true, 'id' => true),
                'img' => array('style' => true)
            ));
            $informations = $_POST['informations'];
            $informations = wp_kses($informations, array(
                'br' => array(),
                'u' => array(),
                'p' => array(),
                'b' => array(),
                'strong' => array(),
            ));
            $contentUser = '';
            $contentUserPdf = '';
            $contentAdmin = '';
            $contentAdminPdf = '';

            $current_ref = $form->current_ref + 1;
            $wpdb->update($table_name, array('current_ref' => $current_ref), array('id' => $form->id));
            $rep = $form->ref_root . $current_ref;
            if (!isset($_POST['gravity']) || $_POST['gravity'] == 0) {

                if ($_POST['email_toUser'] == '1') {
                    $sendUser = 1;

                    $projectCustomer = stripslashes($contentProject);
                    $projectCustomer = str_replace('C:\\fakepath\\', "", $projectCustomer);
                    if ($form->emailCustomerLinks) {
                        $toReplaceDefault = array();
                        $toReplaceBy = array();
                        while (($lastPos = strpos($projectCustomer, 'class="lfb_file">', $lastPos)) !== false) {
                            $positions[] = $lastPos;
                            $lastPos = $lastPos + 17;
                            $fileStartPos = $lastPos;
                            $lastSpan = strpos($projectCustomer, '</span>', $fileStartPos);
                            $file = substr($projectCustomer, $fileStartPos, $lastSpan - $fileStartPos);
                            if (!in_array($file, $toReplaceDefault)) {
                                $toReplaceDefault[] = $file;
                                $filename = $file;
                                if (strlen($filename) > 48) {
                                    $filename = substr($filename, 0, 45) . '...';
                                }
                                $toReplaceBy[] = '<a href="' . $this->uploads_url . $formSession . '/' . $file . '">' . $filename . '</a>';
                            }
                        }
                        foreach ($toReplaceBy as $key => $value) {
                            $projectCustomer = str_replace($toReplaceDefault[$key], $toReplaceBy[$key], $projectCustomer);
                        }
                    }

                    // user email
                    $content = $form->email_userContent;
                    $content = str_replace("[customer_email]", sanitize_text_field($_POST['email']), $content);
                    $content = str_replace("[project_content]", $projectCustomer, $content);
                    $content = str_replace("[information_content]", stripslashes($informations), $content);
                    $content = str_replace("[total_price]", sanitize_text_field($_POST['totalTxt']), $content);
                    $content = str_replace("[ref]", $form->ref_root . $current_ref, $content);
                    $content = str_replace("[date]", date(get_option('date_format')), $content);

                    $content = str_replace("[ip]", $this->get_client_ip(), $content);

                    $content = $this->prepareEmailContent($content, $form, $formSession);

                    $contentUser = $content;

                    // user pdf
                    $content = $form->pdf_userContent;
                    $content = str_replace("[customer_email]", sanitize_text_field($_POST['email']), $content);
                    $content = str_replace("[project_content]", $projectCustomer, $content);
                    $content = str_replace("[information_content]", stripslashes($informations), $content);
                    $content = str_replace("[total_price]", sanitize_text_field($_POST['totalTxt']), $content);
                    $content = str_replace("[ref]", $form->ref_root . $current_ref, $content);
                    $content = str_replace("[date]", date(get_option('date_format')), $content);
                    $content = str_replace("[ip]", $this->get_client_ip(), $content);
                    $content = $this->prepareEmailContent($content, $form, $formSession);
                    $contentUserPdf = $content;
                }

                $projectAdmin = stripslashes($summaryA);
                $lastPos = 0;
                $positions = array();

                $projectAdmin = str_replace('C:\\fakepath\\', "", $projectAdmin);
                $toReplaceDefault = array();
                $toReplaceBy = array();
                while (($lastPos = strpos($projectAdmin, 'class="lfb_file">', $lastPos)) !== false) {
                    $positions[] = $lastPos;
                    $lastPos = $lastPos + 17;
                    $fileStartPos = $lastPos;
                    // $fileStartPos = strpos($projectAdmin, ':', $lastPos) + 2;
                    $lastSpan = strpos($projectAdmin, '</span>', $fileStartPos);
                    $file = substr($projectAdmin, $fileStartPos, $lastSpan - $fileStartPos);
                    if (!in_array($file, $toReplaceDefault)) {
                        $toReplaceDefault[] = $file;
                        $filename = $file;
                        if (strlen($filename) > 48) {
                            $filename = substr($filename, 0, 45) . '...';
                        }
                        $toReplaceBy[] = '<a href="' . $this->uploads_url . $formSession . '/' . $file . '">' . $filename . '</a>';
                    }
                }
                foreach ($toReplaceBy as $key => $value) {
                    $projectAdmin = str_replace($toReplaceDefault[$key], $toReplaceBy[$key], $projectAdmin);
                }
                add_filter('safe_style_css', function( $styles ) {
                    $styles[] = 'border-color';
                    $styles[] = 'background-color';
                    $styles[] = 'font-size';
                    $styles[] = 'padding';
                    $styles[] = 'color';
                    $styles[] = 'text-align';
                    $styles[] = 'line-height';
                    $styles[] = 'margin';
                    $styles[] = 'direction';
                    return $styles;
                });
                $projectAdmin = wp_kses($projectAdmin, array(
                    'br' => array(),
                    'u' => array(),
                    'p' => array(),
                    'b' => array(),
                    'a' => array('href' => true),
                    'span' => array('style' => true),
                    'strong' => array('style' => true),
                    'div' => array('style' => true),
                    'td' => array('style' => true, 'align' => true, 'colspan' => true, 'bgcolor' => true, 'color' => true, 'width' => true),
                    'thead' => array('style' => true, 'bgcolor' => true),
                    'th' => array('style' => true, 'align' => true, 'colspan' => true, 'bgcolor' => true, 'color' => true, 'width' => true, 'id' => true),
                    'table' => array('style' => true, 'cellspacing' => true, 'cellpadding' => true, 'border' => true, 'width' => true, 'bordercolor' => true, 'bgcolor' => true),
                    'tbody' => array('style' => true),
                    'tr' => array('style' => true, 'id' => true),
                    'img' => array('style' => true)
                ));

                // admin email
                $content = $form->email_adminContent;
                $content = str_replace("[customer_email]", $form->ref_root . $current_ref, $content);
                $content = str_replace("[project_content]", $projectAdmin, $content);
                $content = str_replace("[information_content]", stripslashes($informations), $content);
                $content = str_replace("[total_price]", sanitize_text_field($_POST['totalTxt']), $content);
                $content = str_replace("[ref]", $form->ref_root . $current_ref, $content);
                $content = str_replace("[date]", date(get_option('date_format')), $content);
                $content = str_replace("[ip]", $this->get_client_ip(), $content);

                $contentAdmin = $this->prepareEmailContent($content, $form, $formSession);

                // user pdf
                $content = $form->pdf_adminContent;
                $content = str_replace("[customer_email]", sanitize_text_field($_POST['email']), $content);
                $content = str_replace("[project_content]", $projectAdmin, $content);
                $content = str_replace("[information_content]", stripslashes($informations), $content);
                $content = str_replace("[total_price]", sanitize_text_field($_POST['totalTxt']), $content);
                $content = str_replace("[ref]", $form->ref_root . $current_ref, $content);
                $content = str_replace("[date]", date(get_option('date_format')), $content);
                $content = str_replace("[ip]", $this->get_client_ip(), $content);
                $txt_orderType = $form->txt_invoice;

                $content = $this->prepareEmailContent($content, $form, $formSession);
                $contentAdminPdf = $content;


                if (isset($_POST['email']) && $contactSent == 0) {
                    if ($form->useMailchimp && $form->mailchimpList != "") {
                        try {
                            $MailChimp = new Mailchimp($form->mailchimpKey);
                            $merge_vars = array('FNAME' => $firstName, 'LNAME' => $lastName, 'phone' => $phone,
                                'address1' => array('addr1' => $address, 'city' => $city, 'state' => $state, 'zip' => $zip, 'country' => $country));

                            $MailChimp->lists->subscribe($form->mailchimpList, array('email' => $email), $merge_vars, 'html', $form->mailchimpOptin);
                        } catch (Throwable $t) {
                            
                        } catch (Exception $e) {
                            
                        }
                    }
                    if ($form->useMailpoet) {
                        $MailPoet = new MailPoetListEP(date('his'));
                        $MailPoet->add_contact($email, $form->mailPoetList, $firstName, $lastName);
                    }
                    if ($form->useGetResponse) {
                        $GetResponse = new GetResponseEP($form->getResponseKey);
                        $merge_vars = array('phone' => $phone, 'city' => $city, 'state' => $state, 'postal_code' => $zip, 'country' => $country);
                        $GetResponse->addContact($form->getResponseList, $firstName . ' ' . $lastName, $email, 'standard', 0, $merge_vars);
                    }
                }
                $table_name = $wpdb->prefix . "wpefc_customers";
                $rows = $wpdb->get_results($wpdb->prepare("SELECT email,id,password FROM $table_name WHERE email=%s LIMIT 1", $this->stringEncode($email, $settings->encryptDB)));
                $customerID = 0;
                $pass = "a";
                if (count($rows) > 0) {
                    $customerID = $rows[0]->id;
                    $pass = $this->stringDecode($rows[0]->password, true);
                } else {
                    $pass = $this->generatePassword();
                    $wpdb->insert($table_name, array('email' => $this->stringEncode($email, $settings->encryptDB), 'password' => $this->stringEncode($pass, true)));
                    $customerID = $wpdb->insert_id;
                }

                $table_name = $wpdb->prefix . "wpefc_logs";
                $checked = false;
                if ($_POST['useRtl'] == 'true') {
                    $contentAdmin = '<div style="direction: rtl;">' . $contentAdmin . '</div>';
                    $contentUser = '<div style="direction: rtl;">' . $contentUser . '</div>';
                }


                $paymentKey = md5(uniqid());

                $wpdb->insert($table_name, array('ref' => $form->ref_root . $current_ref, 'email' => $this->stringEncode($email, $settings->encryptDB), 'phone' => $this->stringEncode($phone, $settings->encryptDB), 'firstName' => $this->stringEncode($firstName, $settings->encryptDB), 'lastName' => $this->stringEncode($lastName, $settings->encryptDB),
                    'address' => $this->stringEncode($address, $settings->encryptDB), 'city' => $this->stringEncode($city, $settings->encryptDB), 'country' => $this->stringEncode($country, $settings->encryptDB), 'state' => $this->stringEncode($state, $settings->encryptDB), 'zip' => $this->stringEncode($zip, $settings->encryptDB),
                    'formID' => $formID, 'dateLog' => date('Y-m-d'), 'content' => $this->stringEncode($contentAdmin, $settings->encryptDB), 'contentUser' => $this->stringEncode($contentUser, $settings->encryptDB),
                    'pdfContent' => $this->stringEncode($contentAdminPdf, $settings->encryptDB), 'pdfContentUser' => $this->stringEncode($contentUserPdf, $settings->encryptDB),
                    'sendToUser' => $sendUser,
                    'totalPrice' => $total, 'totalSubscription' => $totalSub, 'subscriptionFrequency' => $subFrequency, 'formTitle' => $formTitle, 'contentTxt' => $this->stringEncode($contentTxt, $settings->encryptDB),
                    'paymentKey' => $paymentKey, 'finalUrl' => $finalUrl, 'eventsData' => $events, 'customerID' => $customerID));
                $orderID = $wpdb->insert_id;
                $chkStripe = false;
                $useStripe = false;
                if ($stripeToken != "" && $form->use_stripe) {
                    $useStripe = true;
                    $chkStripe = $this->doStripePayment($orderID, $stripeToken, $stripeTokenB);
                }
                if ((!$usePaypalIpn || $activatePaypal == "false") && (!$useStripe || $chkStripe)) {
                    $this->sendOrderEmail($form->ref_root . $current_ref, $form->id);
                } else if ($useStripe && !$chkStripe) {
                    $rep = '';
                }
            }
            echo $rep;
        }
        die();
    }

    public function generatePassword($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);

        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        return $result;
    }

    private function prepareEmailContent($content, $form, $formSession) {
        // recover items values
        $lastPos = 0;
        while (($lastPos = strpos($content, '[item-', $lastPos)) !== false) {
            $itemID = substr($content, $lastPos + 6, (strpos($content, '_', $lastPos) - ($lastPos + 6)));
            $attribute = substr($content, strpos($content, '_', $lastPos) + 1, ((strpos($content, ']', $lastPos)) - strpos($content, '_', $lastPos)) - 1);
            if ($attribute == 'title') {
                $attribute = 'label';
            }
            $newContent = substr($content, 0, $lastPos);
            $newValue = '';
            $itemFound = false;
            if (substr($itemID, 0, 1) != 'f') {
                foreach ($_POST['items'] as $key => $value) {
                    if ($value['itemid'] == $itemID) {
                        if ($value[$attribute]) {
                            $newValue = stripslashes($value[$attribute]);
                            if ($value['isFile'] == 'true' && $attribute == 'value') {
                                $i_lastPos = 0;
                                while (($i_lastPos = strpos($newValue, 'class="lfb_file">', $i_lastPos)) !== false) {
                                    $positions[] = $i_lastPos;
                                    $i_lastPos = $i_lastPos + 17;
                                    $fileStartPos = $i_lastPos;
                                    $lastSpan = strpos($newValue, '</span>', $fileStartPos);
                                    $file = substr($newValue, $fileStartPos, $lastSpan - $fileStartPos);
                                    $newValue = str_replace($file, '<a href="' . $this->uploads_url . $formSession . '/' . $file . '">' . $file . '</a>', $newValue);
                                }
                            }
                            $itemFound = true;
                        }
                    }
                }
            } else {
                foreach ($_POST['fieldsLast'] as $key => $value) {
                    if ($value['fieldID'] == substr($itemID, 1)) {
                        $newValue = stripslashes($value['value']);
                        if ($value['isFile'] == 'true' && $attribute == 'value') {
                            $i_lastPos = 0;
                            while (($i_lastPos = strpos($newValue, 'class="lfb_file">', $i_lastPos)) !== false) {
                                $positions[] = $i_lastPos;
                                $i_lastPos = $i_lastPos + 17;
                                $fileStartPos = $i_lastPos;
                                $lastSpan = strpos($newValue, '</span>', $fileStartPos);
                                $file = substr($newValue, $fileStartPos, $lastSpan - $fileStartPos);
                                $newValue = str_replace($file, '<a href="' . $this->uploads_url . $formSession . '/' . $file . '">' . $file . '</a>', $newValue);
                            }
                        }
                        $itemFound = true;
                    }
                }
            }
            if ($attribute == 'price') {
                $newValue = $this->getFormatedPrice((float) $newValue, $form);
                if ($form->currencyPosition == 'right') {
                    $newValue = $newValue . $form->currency;
                } else {
                    $newValue = $form->currency . $newValue;
                }
            }

            $newContent .= stripslashes(nl2br($newValue));
            $newContent .= substr($content, strpos($content, ']', $lastPos) + 1);
            $content = $newContent;

            if ($itemFound) {
                $lastPos += 6;
            } else {
                $lastPos += 1;
            }
        }
        return $content;
    }

    private function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    /*
     * Stripe : new subscription
     */

    public function doStripePayment($orderID, $stripeToken, $stripeTokenB) {
        global $wpdb;
        $rep = false;
        $settings = $this->getSettings();
        $table_name = $wpdb->prefix . "wpefc_logs";
        $orders = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE id=%s LIMIT 1", $orderID));
        if (count($orders) > 0) {
            $order = $orders[0];
            $order->email = $this->stringDecode($order->email, $settings->encryptDB);
            $form = $this->getFormDatas($order->formID);
            if (!class_exists('\Stripe\Stripe') && !class_exists('\Stripe')) {
                require_once( 'stripe/Stripe.php');
                require_once( 'stripe/JsonSerializable.php');
                require_once( 'stripe/ApiRequestor.php');
                require_once( 'stripe/ApiResponse.php');
                require_once( 'stripe/Error/Base.php');
                require_once( 'stripe/Error/InvalidRequest.php');
                require_once( 'stripe/Error/Authentication.php');
                require_once( 'stripe/Error/ApiConnection.php');
                require_once( 'stripe/Error/Api.php');
                require_once( 'stripe/Error/Card.php');
                require_once( 'stripe/Error/InvalidRequest.php');
                require_once( 'stripe/Error/RateLimit.php');
                require_once( 'stripe/Util/Util.php');
                require_once( 'stripe/Util/Set.php');
                require_once( 'stripe/HttpClient/ClientInterface.php');
                require_once( 'stripe/HttpClient/CurlClient.php');
                require_once( 'stripe/Util/RequestOptions.php');
                require_once( 'stripe/StripeObject.php');
                require_once( 'stripe/AttachedObject.php');
                require_once( 'stripe/ApiResource.php');
                require_once( 'stripe/Plan.php');
                require_once( 'stripe/ExternalAccount.php');
                require_once( 'stripe/Card.php');
                require_once( 'stripe/Charge.php');
                require_once( 'stripe/Collection.php');
                require_once( 'stripe/Error/Card.php');
                require_once( 'stripe/Customer.php');
                require_once( 'stripe/Subscription.php');
            }

            if ($order->totalPrice > 0) {
                if ($form->stripe_payMode == "percent") {
                    if ($form->stripe_percentToPay != 100) {
                        $order->totalPrice = ($order->totalPrice * $form->stripe_percentToPay) / 100;
                    }
                } else if ($form->stripe_payMode == "fixed") {
                    $order->totalPrice = $form->stripe_fixedToPay;
                }

                if ($form->stripe_currency == "JPY") {
                    $price = number_format((int) $order->totalPrice, 0, '', '');
                } else {
                    $price = number_format((float) $order->totalPrice, 2, '', '');
                }
                try {
                    \Stripe\Stripe::setApiKey($form->stripe_secretKey);
                    \Stripe\Stripe::setApiVersion('2015-10-16');

                    $charge = \Stripe\Charge::create(array(
                                'amount' => $price,
                                "currency" => strtolower($form->stripe_currency),
                                'source' => $stripeToken,
                                'receipt_email' => $order->email,
                                'description' => $form->title . ' - ' . $order->ref,
                                "metadata" => array('email' => $order->email)
                    ));


                    $rep = true;

                    $table_name = $wpdb->prefix . "wpefc_logs";
                    $wpdb->update($table_name, array('paid' => 1), array('id' => $order->id));
                } catch (Throwable $t) {
                    echo 'stripeError:Invalid request';
                } catch (\Stripe\Error\ApiConnection $e) {
                    echo 'stripeError:Stripe service is not available currently, please try later';
                } catch (\Stripe\Error\InvalidRequest $e) {
                    echo 'stripeError:Invalid request';
                } catch (\Stripe\Error\Api $e) {
                    echo 'stripeError:Stripe service is not available currently, please try later';
                } catch (\Stripe\Error\Card $e) {
                    echo 'stripeError:The card was declined';
                } catch (Stripe_ApiConnectionError $e) {
                    echo "stripeError:TLS 1.2 is not supported. You will need to upgrade your integration.";
                }
            }
            if ($order->totalSubscription > 0) {
                $interval = $form->stripe_subsFrequencyType;
                $intervalFreq = $form->stripe_subsFrequency;
                $price = $order->totalSubscription;
                if ($form->stripe_currency == "JPY") {
                    $price = number_format((int) $price, 0, '', '');
                } else {
                    $price = number_format((float) $price, 2, '', '');
                }

                try {
                    $trialDays = 0;

                    if ($order->totalPrice > 0) {
                        $trialDays = 30 * $intervalFreq;
                        if ($interval == 'day') {
                            $trialDays = 1 * $intervalFreq;
                        }
                        if ($interval == 'week') {
                            $trialDays = 7 * $intervalFreq;
                        }
                        if ($interval == 'year') {
                            $trialDays = 365 * $intervalFreq;
                        }
                    }
                    if ($order->totalPrice > 0) {
                        $stripeToken = $stripeTokenB;
                    }
                    \Stripe\Stripe::setApiKey($form->stripe_secretKey);
                    \Stripe\Stripe::setApiVersion('2015-10-16');
                    \Stripe\Plan::create(array(
                        "amount" => $price,
                        "interval" => $interval,
                        "interval_count" => $intervalFreq,
                        "name" => $form->title . ' - ' . $order->ref,
                        "currency" => strtolower($form->stripe_currency),
                        "id" => $order->id,
                        "metadata" => array('email' => $order->email, 'date' => $order->dateLog),
                        "trial_period_days" => $trialDays)
                    );

                    $customer = \Stripe\Customer::create(array(
                                "source" => $stripeToken,
                                "plan" => $order->id,
                                "email" => $order->email
                    ));

                    $table_name = $wpdb->prefix . "wpefc_logs";
                    $wpdb->update($table_name, array('paid' => 1), array('id' => $order->id));

                    $rep = true;
                } catch (Throwable $t) {
                    echo 'stripeError:Invalid request';
                } catch (\Stripe\Error\ApiConnection $e) {
                    echo 'stripeError:Stripe service is not available currently, please try later';
                } catch (\Stripe\Error\InvalidRequest $e) {
                    echo 'stripeError:Invalid request';
                } catch (\Stripe\Error\Api $e) {
                    echo 'stripeError:Stripe service is not available currently, please try later';
                } catch (\Stripe\Error\Card $e) {
                    echo 'stripeError:The card was declined';
                }
            }
        }

        return $rep;
    }

    public function sendContact() {
        global $wpdb;
        $phone = sanitize_text_field($_POST['phone']);
        $firstName = sanitize_text_field($_POST['firstName']);
        $lastName = sanitize_text_field($_POST['lastName']);
        $address = sanitize_text_field($_POST['address']);
        $city = sanitize_text_field($_POST['city']);
        $country = sanitize_text_field($_POST['country']);
        $state = sanitize_text_field($_POST['state']);
        $zip = sanitize_text_field($_POST['zip']);
        $email = sanitize_text_field($_POST['email']);
        $formID = sanitize_text_field($_POST['formID']);

        $table_name = $wpdb->prefix . "wpefc_forms";
        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE id=%s LIMIT 1", $formID));
        if (count($rows) > 0) {
            $form = $rows[0];

            if (isset($_POST['email'])) {
                if ($form->useMailchimp && $form->mailchimpList != "") {
                    try {
                        $MailChimp = new Mailchimp($form->mailchimpKey);
                        $merge_vars = array('FNAME' => $firstName, 'LNAME' => $lastName, 'phone' => $phone,
                            'address1' => array('addr1' => $address, 'city' => $city, 'state' => $state, 'zip' => $zip, 'country' => $country));

                        $MailChimp->lists->subscribe($form->mailchimpList, array('email' => $email), $merge_vars, 'html', $form->mailchimpOptin);
                    } catch (Throwable $t) {
                        
                    } catch (Exception $e) {
                        
                    }
                }
                if ($form->useMailpoet) {
                    $MailPoet = new MailPoetListEP(date('his'));
                    $MailPoet->add_contact($email, $form->mailPoetList, $firstName, $lastName);
                }
                if ($form->useGetResponse) {
                    $GetResponse = new GetResponseEP($form->getResponseKey);
                    $merge_vars = array('firstName' => $firstName, 'lastName' => $lastName, 'phone' => $phone,
                        'city' => $city, 'state' => $state, 'zipCode' => $zip);
                    $GetResponse->addContact($form->getResponseList, $firstName . ' ' . $lastName, $email, 'standard', 0, $merge_vars);
                }
            }
        }
        die();
    }

    public function applyCouponCode() {
        global $wpdb;
        $rep = '';
        $table_name = $wpdb->prefix . "wpefc_coupons";
        $formID = sanitize_text_field($_POST['formID']);
        $code = sanitize_text_field($_POST['code']);
        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name  WHERE couponCode='%s' AND formID=%s LIMIT 1", $code, $formID));
        $chk = false;
        if (count($rows) > 0) {
            $coupon = $rows[0];
            if ($coupon->reductionType == 'percentage') {
                $rep = $coupon->reduction . '%';
            } else {
                $rep = $coupon->reduction;
            }
        }
        echo $rep;
        die();
    }

    function custom_wp_mail_from($email) {
        return sanitize_text_field($_POST['email']);
    }

    /**
     * Get  fields datas
     * @since   1.6.0
     * @return object
     */
    public function getFieldsData() {
        global $wpdb;
        $table_name = $wpdb->prefix . "wpefc_fields";
        $rows = $wpdb->get_results("SELECT * FROM $table_name  ORDER BY ordersort ASC");
        return $rows;
    }

    /**
     * Get  fields from specific form
     * @since   1.6.0
     * @return object
     */
    public function getFieldDatas($form_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . "wpefc_items";
        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE formID=%s AND stepID=0 ORDER BY ordersort ASC, id ASC", $form_id));
        /* $table_name = $wpdb->prefix . "wpefc_fields";
          $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE formID=%s ORDER BY ordersort ASC", $form_id)); */
        return $rows;
    }

    /**
     * Get  form by pageID
     * @since   1.6.0
     * @return object
     */
    public function getFormByPageID($pageID) {
        global $wpdb;
        $table_name = $wpdb->prefix . "wpefc_forms";
        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE form_page_id=%s LIMIT 1", $pageID));
        if ($rows) {
            return $rows[0];
        } else {
            return null;
        }
    }

    /**
     * Get Forms datas
     * @return Array
     */
    private function getFormsData() {
        global $wpdb;
        $table_name = $wpdb->prefix . "wpefc_forms";
        $rows = $wpdb->get_results("SELECT * FROM $table_name");
        return $rows;
    }

    /**
     * Get specific Form datas
     * @return object
     */
    public function getFormDatas($form_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . "wpefc_forms";
        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE id=%s LIMIT 1", $form_id));
        if (count($rows) > 0) {
            return $rows[0];
        } else {
            return null;
        }
    }

    /**
     * Recover uploaded files from the form
     * @access  public
     * @since   1.0.0
     * @return  object
     */
    public function uploadFormFiles() {
        global $wpdb;
        $formSession = sanitize_text_field($_POST['formSession']);
        $itemID = sanitize_text_field($_POST['itemID']);
        $table_name = $wpdb->prefix . "wpefc_items";
        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE id=%s LIMIT 1", $itemID));
        $maxSize = 25;
        if (count($rows) > 0) {
            $maxSize = $rows[0]->fileSize;
        }
        if ($maxSize == 0) {
            $maxSize = 25;
        }
        $maxSize = $maxSize * pow(1024, 2);

        foreach ($_FILES as $key => $value) {
            if ($value["error"] > 0) {
                echo "error";
            } else {
                if (strlen($value["name"]) > 4 &&
                        $value['size'] < $maxSize &&
                        strpos(strtolower($value["name"]), '.php') === false &&
                        strpos(strtolower($value["name"]), '.js') === false &&
                        strpos(strtolower($value["name"]), '.html') === false &&
                        strpos(strtolower($value["name"]), '.phtml') === false &&
                        strpos(strtolower($value["name"]), '.pl') === false &&
                        strpos(strtolower($value["name"]), '.py') === false &&
                        strpos(strtolower($value["name"]), '.jsp') === false &&
                        strpos(strtolower($value["name"]), '.asp') === false &&
                        strpos(strtolower($value["name"]), '.htm') === false &&
                        strpos(strtolower($value["name"]), '.shtml') === false &&
                        strpos(strtolower($value["name"]), '.sh') === false &&
                        strpos(strtolower($value["name"]), '.cgi') === false
                ) {
                    $fileName = str_replace(' ', '_', $value["name"]);

                    if (!is_dir($this->uploads_dir . $formSession)) {
                        mkdir($this->uploads_dir . $formSession);
                        chmod($this->uploads_dir . $formSession, 0747);
                    }
                    move_uploaded_file($value["tmp_name"], $this->uploads_dir . $formSession . '/' . $fileName);
                    chmod($this->uploads_dir . $formSession . '/' . $fileName, 0644);
                }
            }
        }
        die();
    }

    public function removeFile() {
        $formSession = sanitize_text_field($_POST['formSession']);
        $file = sanitize_text_field($_POST['file']);
        $fileName = $formSession . '_' . $file;
        if (file_exists($this->uploads_dir . $fileName)) {
            unlink($this->uploads_dir . $fileName);
        }
        die();
    }

    /**
     * Return steps data.
     * @access  public
     * @since   1.0.0
     * @return  object
     */
    public function getStepsData($form_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . "wpefc_steps";
        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE formID=%s ORDER BY ordersort", $form_id));
        return $rows;
    }

    /**
     * Return items data.
     * @access  public
     * @since   1.0.0
     * @return  object
     */
    public function getItemsData($form_id) {
        global $wpdb;
        $results = array();
        $table_name = $wpdb->prefix . "wpefc_steps";
        $steps = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE formID=%s ORDER BY ordersort", $form_id));
        foreach ($steps as $step) {
            $table_name = $wpdb->prefix . "wpefc_items";
            $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE stepID=%s ORDER BY ordersort", $step->id));
            foreach ($rows as $row) {
                $results[] = $row;
            }
        }
        return $results;
    }

    // End getItemsData()

    /**
     * Save form datas to cart (woocommerce only)
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function cart_save() {
        global $woocommerce;
        $products = $_POST['products'];
        
        if(isset($_POST['emptyWooCart']) && $_POST['emptyWooCart'] == '1'){
             $woocommerce->cart->empty_cart();
        }
        
        foreach ($products as $product) {
            //variation
            $productWoo = new WC_Product($product['product_id']);
            if ($product['variation'] != 0) {
                $productWoo = new WC_Product_Variation($product['variation']);
            }
            $existInCart = false;
            $productData = array();
            $productData['lfbRef'] = sanitize_text_field($_POST['ref']);
            if ($product['variation'] == '0') {
                $woocommerce->cart->add_to_cart($product['product_id'], $product['quantity'], null, null, $productData);
            } else {
                $variation = new WC_Product_Variation($product['variation']);
                $attributes = $productWoo->get_variation_attributes();
                $woocommerce->cart->add_to_cart($product['product_id'], $product['quantity'], $product['variation'], $attributes, $productData);
            }
        }

        die();
    }

    public function cartdd_save() {
        $products = $_POST['products'];
        foreach ($products as $product) {
            $download = new EDD_Download($product['product_id']);
            $arg = array();
            if ($product['variation'] > 0) {
                $arg['price_id'] = $product['variation'];
            }
            //variation
            for ($i = 0; $i < $product['quantity']; $i++) {
                $result = edd_add_to_cart($product['product_id'], $arg);
                echo $result;
            }
        }
        die();
    }

    /**
     * Main LFB_Core Instance
     *
     *
     * @since 1.0.0
     * @static
     * @see BSS_Core()
     * @return Main LFB_Core instance
     */
    public static function instance($file = '', $version = '1.0.0') {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }
        return self::$_instance;
    }

// End instance()

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone() {
        
    }

// End __clone()

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup() {
        //  _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

// End __wakeup()

    /**
     * Return settings.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function getSettings() {
        global $wpdb;
        $table_name = $wpdb->prefix . "wpefc_settings";
        $settings = $wpdb->get_results("SELECT * FROM $table_name WHERE id=1 LIMIT 1");
        $rep = false;
        if (count($settings) > 0) {
            $rep = $settings[0];
        }
        return $rep;
    }

    // End getSettings()

    /**
     * Log the plugin version number.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    private function _log_version_number() {
        update_option($this->_token . '_version', $this->_version);
    }

}
