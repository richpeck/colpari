<?php

/**
 * Template Name: WP Estimation & Payment Forms Preview
 *
 * @package WordPress
 * @subpackage WP Estimation & Payment Forms
 */
$lfb = LFB_Core::instance(__FILE__, '1.0');
$formID = $_GET['form'];
$form = $lfb->getFormDatas($formID);
wp_register_style($lfb->_token . '-frontend-libs', esc_url($lfb->assets_url) . 'css/lfb_frontendPackedLibs.min.css', array(), $lfb->_version);
wp_enqueue_style($lfb->_token . '-frontend-libs');
wp_register_style($lfb->_token . '-estimationpopup', esc_url($lfb->assets_url) . 'css/lfb_forms.min.css', array($lfb->_token .'-frontend-libs'), $lfb->_version);
wp_enqueue_style($lfb->_token . '-estimationpopup');

// scripts
wp_register_script($lfb->_token . '-frontend-libs', esc_url($lfb->assets_url) . 'js/lfb_frontendPackedLibs.min.js', array("jquery-ui-core", "jquery-ui-tooltip", "jquery-ui-slider", "jquery-ui-position", "jquery-ui-datepicker"), $lfb->_version);
wp_enqueue_script($lfb->_token . '-frontend-libs');
wp_register_script($lfb->_token . '-estimationpopup', esc_url($lfb->assets_url) . 'js/lfb_form.min.js', array($lfb->_token . '-frontend-libs'), $lfb->_version);
wp_enqueue_script($lfb->_token . '-estimationpopup');

$lfb->currentForms[] = $formID;
add_action('wp_head', array($lfb, 'options_custom_styles'));
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
$js_data = array();

if ($form) {
    global $wpdb;
    // check gmap
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
            wp_register_script($lfb->_token . '-gmap', '//maps.googleapis.com/maps/api/js?key=' . $form->gmap_key . $libPlace, array());
            wp_enqueue_script($lfb->_token . '-gmap');
        }
    }

    if ($form->usedCssFile != '' && file_exists(trailingslashit($lfb->dir) . 'export/' . $form->usedCssFile)) {
        wp_register_style($lfb->_token . '-usedStyles-' . $form->id, esc_url($lfb->tmp_url) . $form->usedCssFile, array(), date('Mdhis'));
        wp_enqueue_style($lfb->_token . '-usedStyles-' . $form->id);
    }

    if (is_plugin_active('gravityforms/gravityforms.php') && $form->gravityFormID > 0) {
        gravity_form_enqueue_scripts($form->gravityFormID, true);
        if (is_plugin_active('gravityformssignature/signature.php')) {
            wp_register_script('gforms_signature', esc_url($lfb->assets_url) . '../../gravityformssignature/super_signature/ss.js', array("gform_gravityforms"), $lfb->_version);
            wp_enqueue_script('gforms_signature');
        }
    }
    if (!$form->colorA || $form->colorA == "") {
        $form->colorA = $settings->colorA;
    }
    
    
    if($form->datepickerLang != "" && is_file($lfb->assets_dir .'/js/datepickerLocale/bootstrap-datetimepicker.'.$form->datepickerLang.'.js')){
       wp_register_script($lfb->_token . '-datetimepicker-locale-'.$form->datepickerLang, esc_url($lfb->assets_url) . 'js/datepickerLocale/bootstrap-datetimepicker.'.$form->datepickerLang.'.js', array(), $lfb->_version);
       wp_enqueue_script($lfb->_token . '-datetimepicker-locale-'.$form->datepickerLang);                                    
    }

    global $wpdb;
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

    $formStyleSrc = '';
    if (isset($_GET['lfb_action']) && $_GET['lfb_action'] == 'preview') {
        $formStyleSrc = $form->formStyles;
    }

    if ($form->use_stripe) {
        $form->percentToPay = $form->stripe_percentToPay;
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
        'currency' => $form->currency,
        'percentToPay' => $form->percentToPay,
        'fixedToPay' => $form->fixedToPay,
        'payMode' => $form->payMode,
        'currencyPosition' => $form->currencyPosition,
        'intro_enabled' => $form->intro_enabled,
        'save_to_cart' => $form->save_to_cart,
        'save_to_cart_edd' => $form->save_to_cart_edd,
        'colorA' => $form->colorA,
        'close_url' => $form->close_url,
        'animationsSpeed' => $form->animationsSpeed,
        'email_toUser' => $form->email_toUser,
        'showSteps' => $form->showSteps,
        'formID' => $form->id,
        'gravityFormID' => $form->gravityFormID,
        'showInitialPrice' => $form->show_initialPrice,
        'disableTipMobile' => $form->disableTipMobile,
        'legalNoticeEnable' => $form->legalNoticeEnable,
        'links' => $links,
        'redirections' => $redirections,
        'useRedirectionConditions' => $form->useRedirectionConditions,
        'usePdf' => $usePdf,
        'txt_yes' => __('Yes', 'lfb'),
        'txt_no' => __('No', 'lfb'),
        'txt_lastBtn' => $form->last_btn,
        'txt_btnStep' => $form->btn_step,
        'dateFormat' => stripslashes($lfb->dateFormatToDatePickerFormat(get_option('date_format'))),
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
        'captchaUrl' => esc_url(trailingslashit(plugins_url('/includes/captcha/', $lfb->file))) . 'get_captcha.php',
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
wp_localize_script($lfb->_token . '-estimationpopup', 'wpe_forms', $js_data);
add_action('wp_head', array($lfb, 'options_custom_styles'));

get_header();

function lfb_content($content) {
    $content = '[estimation_form form_id="' . $_GET['form'] . '" fullscreen="true"]';
    return do_shortcode($content);
}

add_filter('the_content', 'lfb_content', 20);
echo '<div id="lfb_preview">';
the_content();
echo '</div>';
wp_footer();
?>
