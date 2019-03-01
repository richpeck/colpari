<?php

/*
 * Plugin Name: WP Cost Estimation & Payment Forms Builder 
 * Version: 9.634
 *
 * Plugin URI: http://codecanyon.net/item/wp-cost-estimation-payment-forms-builder/7818230
 * Description: This plugin allows you to create easily beautiful cost estimation & payment forms on your Wordpress website
 * Author: Biscay Charly (loopus)
 * Author URI: http://www.loopus-plugins.com/
 * Requires at least: 3.8
 * Tested up to: 4.9.7
 *
 * @package WordPress
 * @author Biscay Charly (loopus)
 * @since 1.0.0
 */

if (!defined('ABSPATH'))
    exit;

register_activation_hook(__FILE__, 'lfb_install');
register_uninstall_hook(__FILE__, 'lfb_uninstall');

global $jal_db_version;
$jal_db_version = "1.1";

if (!class_exists("Mailchimp", false)) {
    require_once('includes/Mailchimp.php');
}
if (!class_exists("MailPoetListEP", false)) {
    require_once('includes/MailPoetList.php');
}
if (!class_exists("GetResponseEP", false)) {
    require_once('includes/GetResponseAPI.class.php');
}
require_once('includes/lfb-core.php');
require_once('includes/lfb-admin.php');

function Estimation_Form() {
    update_option("lfb_themeMode", false);
    $version = 9.634;
    lfb_checkDBUpdates($version);
    $instance = LFB_Core::instance(__FILE__, $version);
    if (is_null($instance->menu)) {
        $instance->menu = LFB_admin::instance($instance);
    }

    return $instance;
}

/**
 * Installation. Runs on activation.
 * @access  public
 * @since   1.0.0
 * @return  void
 */
function lfb_install() {
    global $wpdb;
    global $jal_db_version;
    require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

    add_option("jal_db_version", $jal_db_version);

    $db_table_name = $wpdb->prefix . "wpefc_forms";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		title VARCHAR(120) NOT NULL,
                errorMessage VARCHAR(240) NOT NULL,
                intro_enabled BOOL,
                emptyWooCart BOOL NOT NULL,
                save_to_cart BOOL NOT NULL,
                save_to_cart_edd BOOL NOT NULL,
                use_paypal BOOL NOT NULL,
                paypal_email VARCHAR(250) NULL,
                paypal_currency VARCHAR(3) NOT NULL DEFAULT 'USD',
                paypal_useIpn BOOL,
                paypal_useSandbox BOOL,
                paypal_subsFrequency SMALLINT(5) NOT NULL DEFAULT 1,
                paypal_subsFrequencyType VARCHAR(1) NOT NULL DEFAULT 'M',
                paypal_subsMaxPayments SMALLINT(5) NOT NULL DEFAULT 0,
                paypal_languagePayment VARCHAR(8) NOT NULL DEFAULT '',
                use_stripe BOOL,
                stripe_useSandbox BOOL,
                stripe_secretKey VARCHAR(250) NOT NULL,
                stripe_publishKey VARCHAR(250) NOT NULL,
                stripe_currency VARCHAR(6) NOT NULL,
                stripe_subsFrequency SMALLINT(5) NOT NULL DEFAULT 1,
                stripe_subsFrequencyType VARCHAR(16) NOT NULL DEFAULT 'month',  
                stripe_logoImg VARCHAR(250) NOT NULL DEFAULT '" . esc_url(trailingslashit(plugins_url('/assets/', __FILE__))) . "img/powered_by_stripe@2x.png',
                isSubscription BOOL,
                subscription_text VARCHAR(250) NOT NULL DEFAULT '/month',
                close_url VARCHAR(250) NOT NULL DEFAULT '#',
                btn_step VARCHAR(120) NOT NULL,
                previous_step VARCHAR(120) NOT NULL,
                intro_title VARCHAR(120) NOT NULL,
                intro_text TEXT NOT NULL,
                intro_btn VARCHAR(120) NOT NULL,
                intro_image VARCHAR(250) NOT NULL,
                last_title VARCHAR(120) NOT NULL,
                last_text TEXT NOT NULL,
                last_btn VARCHAR(120) NOT NULL,
                last_msg_label VARCHAR(240) NOT NULL,
                initial_price FLOAT NOT NULL,
                max_price FLOAT NOT NULL,
                succeed_text TEXT NOT NULL,
                email_name VARCHAR(250) NOT NULL,
                email VARCHAR(250) NOT NULL,
                email_adminContent LONGTEXT NOT NULL,
                email_subject VARCHAR(250) NOT NULL,
                email_toUser BOOL NOT NULL,
                email_userSubject VARCHAR(250) NOT NULL,
                email_userContent LONGTEXT NOT NULL,
                emailCustomerLinks BOOL NOT NULL DEFAULT 0,
                currency VARCHAR (32) NOT NULL,
                currencyPosition VARCHAR (32) NOT NULL,
                gravityFormID INT(9) NOT NULL,
                animationsSpeed FLOAT NOT NULL DEFAULT 0.5,
                showSteps SMALLINT(5) NOT NULL,
                qtType SMALLINT(9) NOT NULL,
                show_initialPrice BOOL NOT NULL,
                ref_root VARCHAR(32) NOT NULL DEFAULT 'A000',
                current_ref INT(9) NOT NULL DEFAULT 1,
                colorA VARCHAR(16) NOT NULL,
                colorB VARCHAR(16) NOT NULL,
                colorC VARCHAR(16) NOT NULL,
                colorBg VARCHAR(16) NOT NULL,
                colorSecondary VARCHAR(16) NOT NULL,
                colorSecondaryTxt VARCHAR(16) NOT NULL,
                colorCbCircle VARCHAR(16) NOT NULL,
                colorCbCircleOn VARCHAR(16) NOT NULL,
                colorPageBg VARCHAR(16) NOT NULL DEFAULT '#ffffff',
                item_pictures_size SMALLINT(9) NOT NULL,
                hideFinalPrice BOOL NOT NULL DEFAULT 0,
                priceFontSize SMALLINT NOT NULL DEFAULT 18,
                customCss TEXT NOT NULL,
                disableTipMobile BOOL NOT NULL,
                legalNoticeContent TEXT NOT NULL,
                legalNoticeTitle TEXT NOT NULL,
                legalNoticeEnable BOOL NOT NULL,
                datepickerLang VARCHAR(16)  NOT NULL,
         	percentToPay FLOAT DEFAULT 100,
                thousandsSeparator VARCHAR(4) NOT NULL,
                decimalsSeparator VARCHAR(4) NOT NULL,
                millionSeparator VARCHAR(4) NOT NULL,
                billionsSeparator VARCHAR(4) NOT NULL,
                useSummary BOOL NOT NULL,
                summary_title VARCHAR(240) NOT NULL,
                summary_description VARCHAR(240) NOT NULL,
                summary_quantity VARCHAR(240) NOT NULL,
                summary_price VARCHAR(240) NOT NULL,
                summary_total VARCHAR(240) NOT NULL,
                summary_value VARCHAR(240) NOT NULL,
                summary_discount VARCHAR(240) NOT NULL DEFAULT 'Discount :',
                summary_hideQt BOOL,
                summary_hideZero BOOL,
                summary_hidePrices BOOL,
                summary_hideTotal BOOL,
                summary_hideFinalStep BOOL NOT NULL DEFAULT 1,
                summary_showAllPricesEmail BOOL NOT NULL DEFAULT 0,
                enableFloatingSummary BOOL NOT NULL DEFAULT 0,
                floatSummary_icon VARCHAR(250) NOT NULL DEFAULT 'fa-shopping-cart',
                floatSummary_label VARCHAR(250) NOT NULL DEFAULT '',
                floatSummary_numSteps BOOL NOT NULL DEFAULT 0,
                floatSummary_hidePrices BOOL NOT NULL DEFAULT 0,
                groupAutoClick BOOL,
                useCoupons BOOL NOT NULL,
                inverseGrayFx BOOL NOT NULL,                
                couponText VARCHAR(250) NOT NULL DEFAULT 'Discount coupon code',
                useMailchimp BOOL NOT NULL,
                mailchimpKey VARCHAR(250) NOT NULL,
                mailchimpList VARCHAR(250) NOT NULL,
                mailchimpOptin BOOL NOT NULL,
                useMailpoet BOOL NOT NULL,
                mailPoetList VARCHAR(250) NOT NULL,
                useGetResponse BOOL NOT NULL,
                getResponseKey VARCHAR(250) NOT NULL,
                getResponseList VARCHAR(250) NOT NULL,
                loadAllPages BOOL NOT NULL,
                filesUpload_text VARCHAR(250) NOT NULL DEFAULT 'Drop files here to upload', 
                filesUploadSize_text VARCHAR(250) NOT NULL DEFAULT 'File is too big (max size: {{maxFilesize}}MB)', 
                filesUploadType_text VARCHAR(250) NOT NULL DEFAULT 'Invalid file type',          
                filesUploadLimit_text VARCHAR(250) NOT NULL DEFAULT 'You can not upload any more files',
                useGoogleFont BOOL NOT NULL DEFAULT 1,
                googleFontName VARCHAR(250) NOT NULL DEFAULT 'Lato',
                analyticsID VARCHAR(250) NOT NULL,
                sendPdfCustomer BOOL NOT NULL, 
                sendPdfAdmin BOOL NOT NULL, 
                sendContactASAP BOOL NOT NULL,
                showTotalBottom BOOL NOT NULL,
                stripe_label_creditCard VARCHAR(250) NOT NULL,
                stripe_label_cvc VARCHAR(250) NOT NULL,
                stripe_label_expiration VARCHAR(250) NOT NULL,  
                scrollTopMargin INT(9) NOT NULL,
                redirectionDelay INT(9) NOT NULL DEFAULT 5,
                useRedirectionConditions BOOL NOT NULL DEFAULT 0,
                gmap_key VARCHAR(250) NOT NULL,
                txtDistanceError TEXT NOT NULL,
                customJS TEXT NOT NULL,
                disableDropdowns BOOL NOT NULL DEFAULT 1,                
                usedCssFile VARCHAR(250) NOT NULL,
                formStyles LONGTEXT NOT NULL,
                columnsWidth SMALLINT(5) NOT NULL,
                inlineLabels BOOL NOT NULL DEFAULT 0,
                previousStepBtn BOOL NOT NULL DEFAULT 0,
                alignLeft BOOL NOT NULL DEFAULT 0,
                totalIsRange BOOL NOT NULL DEFAULT 0,
                totalRange SMALLINT(5) NOT NULL DEFAULT 100,
                totalRangeMode VARCHAR(16) NOT NULL DEFAULT '',
                labelRangeBetween VARCHAR(128) NOT NULL DEFAULT 'between',
                labelRangeAnd VARCHAR(128) NOT NULL DEFAULT 'and',                
                useCaptcha  BOOL NOT NULL DEFAULT 0,
                captchaLabel VARCHAR(250) NOT NULL DEFAULT 'Please rewrite the following text in the field',
                summary_noDecimals BOOL NOT NULL DEFAULT 0, 
                scrollTopPage BOOL NOT NULL DEFAULT 0,                 
         	stripe_percentToPay FLOAT DEFAULT 100,
                nextStepButtonIcon VARCHAR(250) NOT NULL DEFAULT 'fa-check',
                previousStepButtonIcon VARCHAR(250) NOT NULL DEFAULT 'fa-arrow-left',
                finalButtonIcon VARCHAR(250) NOT NULL DEFAULT 'fa-check',
                introButtonIcon VARCHAR(250) NOT NULL DEFAULT 'fa-check',
                imgIconStyle VARCHAR(64) NOT NULL DEFAULT 'circles',
                timeModeAM BOOL NOT NULL DEFAULT 1,
                fieldsPreset VARCHAR(64) NOT NULL,
                enableFlipFX BOOL NOT NULL DEFAULT 1,
                enableShineFxBtn BOOL NOT NULL DEFAULT 1,
                paymentType VARCHAR(64) NOT NULL DEFAULT 'form',
                enableEmailPaymentText TEXT NOT NULL,
                emailPaymentType VARCHAR(64) NOT NULL DEFAULT 'checkbox',
                txt_invoice VARCHAR(250) NOT NULL DEFAULT 'Invoice',
                txt_quotation VARCHAR(250) NOT NULL DEFAULT 'Quotation',
                txt_payFormFinalTxt VARCHAR(250) NOT NULL DEFAULT 'Thank you for your order, we will contact you soon',                                   
         	stripe_payMode VARCHAR(64) NOT NULL DEFAULT '',                         
         	stripe_fixedToPay FLOAT DEFAULT 100,      
         	paypal_payMode VARCHAR(64) NOT NULL DEFAULT '',                         
         	paypal_fixedToPay FLOAT DEFAULT 100,     
                disableLinksAnim BOOL NOT NULL DEFAULT 0,
                imgTitlesStyle VARCHAR(16) NOT NULL DEFAULT '',
                sendEmailLastStep BOOL NOT NULL DEFAULT 0,
                enableSaveForLaterBtn BOOL NOT NULL DEFAULT 0,
                saveForLaterLabel VARCHAR(250) NOT NULL DEFAULT '',
                saveForLaterDelLabel VARCHAR(250) NOT NULL DEFAULT 'Delete backup',                
                saveForLaterIcon VARCHAR(250) NOT NULL DEFAULT 'fa-floppy-o',
                lastSave VARCHAR(250) NOT NULL,
                pdf_userContent LONGTEXT NOT NULL,
                pdf_adminContent LONGTEXT NOT NULL,
                mainTitleTag VARCHAR(6) NOT NULL DEFAULT 'h1',
                stepTitleTag VARCHAR(6) NOT NULL DEFAULT 'h2',
                enableCustomersData BOOL NOT NULL DEFAULT 0,
                customersDataEmailLink LONGTEXT NOT NULL,                   
		UNIQUE KEY id (id)
		) $charset_collate;";
        dbDelta($sql);
    }

    $db_table_name = $wpdb->prefix . "wpefc_steps";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
    		formID mediumint (9) NOT NULL,
    		start BOOL  NOT NULL DEFAULT 0,
    		title VARCHAR(120) NOT NULL,
    		content TEXT NOT NULL,
    		ordersort mediumint(9) NOT NULL,
    		itemRequired BOOL  NOT NULL DEFAULT 0,
    		itemDepend SMALLINT(5) NOT NULL,
    		interactions TEXT NOT NULL,
    		description TEXT NOT NULL,
    		showInSummary BOOL  NOT NULL DEFAULT 1,
                itemsPerRow TINYINT(2) NOT NULL,
                useShowConditions BOOL NOT NULL,
                showConditions TEXT NOT NULL,
                showConditionsOperator VARCHAR(8) NOT NULL,
                hideNextStepBtn  BOOL NOT NULL,
    		UNIQUE KEY id (id)
    		) $charset_collate;";
        dbDelta($sql);
    }

    $db_table_name = $wpdb->prefix . "wpefc_logs";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
    		formID mediumint (9) NOT NULL,
    		customerID mediumint (9) NOT NULL,
    		ref VARCHAR(120) NOT NULL,
    		email VARCHAR(250) NOT NULL,
    		content MEDIUMTEXT NOT NULL,
                contentUser LONGTEXT NOT NULL,
    		pdfContent LONGTEXT NOT NULL,
                pdfContentUser LONGTEXT NOT NULL,
                contentTxt LONGTEXT NOT NULL,
                dateLog VARCHAR(64) NOT NULL,
                sendToUser BOOL,
                checked BOOL,
                phone VARCHAR(120) NOT NULL,
                firstName VARCHAR(250) NOT NULL,
                lastName VARCHAR(250) NOT NULL,
                address TEXT NOT NULL,
                city VARCHAR(250) NOT NULL,
                country VARCHAR(250) NOT NULL,
                state VARCHAR(250) NOT NULL,
                zip VARCHAR(128) NOT NULL,
                totalPrice FLOAT NOT NULL,
                totalSubscription FLOAT NOT NULL,
                subscriptionFrequency VARCHAR(64) NOT NULL,
                formTitle VARCHAR(250) NOT NULL,
                paid BOOL NOT NULL,
                paymentKey VARCHAR(250) NOT NULL DEFAULT '',
                finalUrl VARCHAR(250) NOT NULL DEFAULT '',
                eventsData LONGTEXT NOT NULL,         
    		UNIQUE KEY id (id)
    		) $charset_collate;";
        dbDelta($sql);
    }

    $db_table_name = $wpdb->prefix . "wpefc_items";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                title VARCHAR(120) NOT NULL,
                 description TEXT NOT NULL,
                ordersort mediumint(9) NOT NULL,
                image VARCHAR(250) NOT NULL,
                imageDes VARCHAR(250) NOT NULL,
                groupitems VARCHAR(120) NOT NULL,
                type VARCHAR(120) NOT NULL,
                stepID mediumint(9) NOT NULL,
                formID mediumint(9) NOT NULL,
                price FLOAT NOT NULL,
                operation VARCHAR(1) NOT NULL DEFAULT '+',
                ischecked BOOL,
                isRequired BOOL,
                quantity_enabled BOOL,
                quantity_max INT(11)  NOT NULL,
                quantity_min INT(11)  NOT NULL,
                reduc_enabled BOOL NOT NULL,
                reduc_qt SMALLINT(5) NOT NULL,
                reduc_value FLOAT NOT NULL,
                reducsQt LONGTEXT NOT NULL,
                isWooLinked BOOL,
                wooProductID INT(11)  NOT NULL,
                wooVariation INT(11)  NOT NULL,
                eddProductID INT(11)  NOT NULL,
                eddVariation INT(11)  NOT NULL,
                imageTint BOOL,
                showPrice BOOL,
                useRow BOOL NOT NULL,
                optionsValues TEXT NOT NULL,
                urlTarget VARCHAR(250) NOT NULL,
                showInSummary BOOL DEFAULT 1,
                richtext TEXT NOT NULL,
                isHidden BOOL NOT NULL,
                minSize INT(11) NOT NULL,
                maxSize INT(11) NOT NULL,
                isNumeric BOOL NOT NULL,
                isSinglePrice BOOL NOT NULL,
                maxFiles SMALLINT(9) NOT NULL,
                allowedFiles VARCHAR(250) NOT NULL DEFAULT '.png,.jpg,.jpeg,.gif,.zip,.rar',
                useCalculation BOOL NOT NULL,
                calculation LONGTEXT NOT NULL,
                fieldType VARCHAR(64) NOT NULL,
                useShowConditions BOOL NOT NULL,
                showConditions TEXT NOT NULL,
                showConditionsOperator VARCHAR(8) NOT NULL,
                usePaypalIfChecked BOOL NOT NULL,
                dontUsePaypalIfChecked BOOL NOT NULL,                
                useDistanceAsQt BOOL NOT NULL,
                distanceQt VARCHAR(250) NOT NULL,
                hideQtSummary BOOL NOT NULL,
                defaultValue TEXT NOT NULL,
                fileSize INT(9) NOT NULL DEFAULT 25,
                firstValueDisabled BOOL NOT NULL,
                sliderStep INT(11) NOT NULL DEFAULT 1,
                date_allowPast BOOL NOT NULL,
                date_showMonths BOOL NOT NULL,
                date_showYears BOOL NOT NULL,     
                shortcode LONGTEXT NOT NULL,
                minTime VARCHAR(16) NOT NULL,
                maxTime VARCHAR(16) NOT NULL,
                dontAddToTotal BOOL NOT NULL,
                useCalculationQt BOOL NOT NULL,
                calculationQt LONGTEXT NOT NULL,
                placeholder VARCHAR(250) NOT NULL,
                validation VARCHAR(64) NOT NULL,
                validationMin SMALLINT(5) NOT NULL,
                validationMax SMALLINT(5) NOT NULL,                
                validationCaracts VARCHAR(250) NOT NULL,    
                icon VARCHAR(128) NOT NULL,
                iconPosition BOOL NOT NULL,
                maxWidth SMALLINT(5) NOT NULL,
                maxHeight SMALLINT(5) NOT NULL,
                autocomplete BOOL NOT NULL,
                urlTargetMode VARCHAR(64) NOT NULL DEFAULT '_blank',
                color VARCHAR(64) NOT NULL DEFAULT '#1abc9c',
                callNextStep BOOL NOT NULL DEFAULT 0,
                useValueAsQt BOOL NOT NULL DEFAULT 0,
                dateType VARCHAR(32) NOT NULL DEFAULT 'date', 
                calendarID MEDIUMINT(9) NOT NULL DEFAULT 0,                
                eventDuration SMALLINT(5) NOT NULL DEFAULT 1,
                eventDurationType VARCHAR(25) NOT NULL DEFAULT 'hours', 
                eventCategory SMALLINT(5) NOT NULL DEFAULT 1, 
                eventTitle VARCHAR(250) NOT NULL DEFAULT 'New event',
                registerEvent BOOL NOT NULL DEFAULT 0,
                eventBusy BOOL NOT NULL DEFAULT 0,
                useAsDateRange BOOL NOT NULL DEFAULT 0,
                endDaterangeID MEDIUMINT(9) NOT NULL DEFAULT 0, 
                disableMinutes BOOL NOT NULL DEFAULT 0,
  		UNIQUE KEY id (id)
		) $charset_collate;";
        dbDelta($sql);
    }

    $db_table_name = $wpdb->prefix . "wpefc_links";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
    		formID mediumint (9) NOT NULL,
    		originID INT(9) NOT NULL,
    		destinationID INT(9) NOT NULL,
    		conditions TEXT NOT NULL,
                operator VARCHAR(8) NOT NULL,
    		UNIQUE KEY id (id)
    		) $charset_collate;";
        dbDelta($sql);
    }


    $db_table_name = $wpdb->prefix . "wpefc_fields";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
    		    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    formID SMALLINT(5) NOT NULL,
    		    label VARCHAR(120) NOT NULL,
    		    ordersort mediumint(9) NOT NULL,
    		    isRequired BOOL,
    		    typefield VARCHAR(32) NOT NULL,
    		    visibility VARCHAR(32) NOT NULL,
                    validation VARCHAR(64) NOT NULL,
                    fieldType VARCHAR(64) NOT NULL,
    		UNIQUE KEY id (id)
    		) $charset_collate;";
        dbDelta($sql);
    }

    $db_table_name = $wpdb->prefix . "wpefc_settings";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
  		id mediumint(9) NOT NULL AUTO_INCREMENT,
  		purchaseCode VARCHAR(250) NOT NULL,
  		previewHeight SMALLINT(5) NOT NULL DEFAULT 300,
                tdgn_enabled BOOL NOT NULL,
                firstStart BOOL NOT NULL DEFAULT 1,
                customerDataAdminEmail VARCHAR(250) NOT NULL DEFAULT 'your@email.here',
                txtCustomersDataWarningText LONGTEXT NOT NULL,
                txtCustomersDataDownloadLink VARCHAR(250) NOT NULL DEFAULT 'Download my data',
                txtCustomersDataDeleteLink VARCHAR(250)NOT NULL DEFAULT 'Delete all my data',
                txtCustomersDataLeaveLink VARCHAR(250) NOT NULL DEFAULT 'Sign out',
                txtCustomersDataEditLink VARCHAR(250) NOT NULL DEFAULT 'Modify my data',
                customersDataDeleteDelay SMALLINT(5) NOT NULL DEFAULT 3,  
                txtCustomersDataTitle VARCHAR(250) NOT NULL DEFAULT 'Manage my data',  
                customersDataLabelEmail VARCHAR(250) NOT NULL DEFAULT 'Your email', 
                customersDataLabelPass VARCHAR(250) NOT NULL DEFAULT 'Your password', 
                customersDataLabelModify VARCHAR(250) NOT NULL DEFAULT 'What data do you want to edit ?',    
                txtCustomersDataForgotPassLink VARCHAR(250) NOT NULL DEFAULT 'Send me my password', 
                txtCustomersDataForgotPassSent VARCHAR(250) NOT NULL DEFAULT 'Your password has been sent by email', 
                txtCustomersDataForgotMailSubject  VARCHAR(250) NOT NULL DEFAULT 'Your password to manage your data', 
                txtCustomersDataForgotPassMail LONGTEXT NOT NULL, 
                txtCustomersDataModifyValidConfirm VARCHAR(250) NOT NULL DEFAULT 'Your request has been sent and will be processed as soon as possible', 
                txtCustomersDataModifyMailSubject VARCHAR(250) NOT NULL DEFAULT 'Data modification request from a customer', 
                txtCustomersDataDeleteMailSubject VARCHAR(250) NOT NULL DEFAULT 'Data deletion request from a customer',                
                encryptDB BOOL NOT NULL DEFAULT 1,         
  		UNIQUE KEY id (id)
  		) $charset_collate;";
        dbDelta($sql);
        $rows_affected = $wpdb->insert($db_table_name, array('previewHeight' => 300,
            'customerDataAdminEmail' => 'your@email.here', 'txtCustomersDataWarningText' => 'I understand and agree that deleting all data about me may result in the inability to process your order properly.',
            'txtCustomersDataDownloadLink' => 'Download my data', 'txtCustomersDataDeleteLink' => 'Delete all my data', 'txtCustomersDataLeaveLink' => 'Sign out', 'customersDataDeleteDelay' => 3,
            'txtCustomersDataTitle' => 'Manage my data',
            'txtCustomersDataForgotPassLink' => 'Send me my password',
            'txtCustomersDataForgotPassSent' => 'Your password has been sent by email',
            'txtCustomersDataForgotMailSubject' => 'Your password to manage your data',
            'txtCustomersDataForgotPassMail' => "Hello,\nHere is your password to manage your data :\nPassword: [password]\nManage your data from : [url]",
            'txtCustomersDataModifyValidConfirm' => 'Your request has been sent and will be processed as soon as possible',
            'txtCustomersDataModifyMailSubject' => 'Data modification request from a customer',
            'txtCustomersDataDeleteMailSubject' => 'Data deletion request from a customer'));
    }

    $db_table_name = $wpdb->prefix . "wpefc_coupons";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
  		id mediumint(9) NOT NULL AUTO_INCREMENT,
                formID mediumint(9) NOT NULL,
  		couponCode VARCHAR(250) NOT NULL,
  		reduction FLOAT NOT NULL,
                reductionType VARCHAR(64) NOT NULL,
                useMax SMALLINT(5) NOT NULL DEFAULT 1,
                currentUses SMALLINT(5) NOT NULL,
  		UNIQUE KEY id (id)
  		) $charset_collate;";
        dbDelta($sql);
    }

    $db_table_name = $wpdb->prefix . "wpefc_redirConditions";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
    		formID mediumint (9) NOT NULL,    		
    		conditions TEXT NOT NULL,
                conditionsOperator VARCHAR(4) NOT NULL DEFAULT '+',
                url VARCHAR(250) NOT NULL,
    		UNIQUE KEY id (id)
    		) $charset_collate;";
        dbDelta($sql);
    }


    $db_table_name = $wpdb->prefix . "wpefc_layeredImages";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                formID SMALLINT(5) NOT NULL,
                itemID SMALLINT(5) NOT NULL,
                title VARCHAR(120) NOT NULL,
                ordersort mediumint(9) NOT NULL,
                image VARCHAR(250) NOT NULL,
                showConditions TEXT NOT NULL,
                showConditionsOperator VARCHAR(8) NOT NULL,           
    		UNIQUE KEY id (id)
    		) $charset_collate;";
        dbDelta($sql);
    }

    $db_table_name = $wpdb->prefix . "wpefc_calendars";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                title VARCHAR(250) NOT NULL,    	
                unavailableDays VARCHAR(32) NOT NULL DEFAULT '',
                unavailableHours VARCHAR(64) NOT NULL DEFAULT '',
    		UNIQUE KEY id (id)
    		) $charset_collate;";
        dbDelta($sql);
        $rows_affected = $wpdb->insert($db_table_name, array('title' => 'Default', 'unavailableDays' => '', 'unavailableHours' => ''));
    }

    $db_table_name = $wpdb->prefix . "wpefc_calendarEvents";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
                calendarID SMALLINT(5) NOT NULL,
    		title VARCHAR(250) NOT NULL,    	
                startDate DATETIME NOT NULL,	
                endDate DATETIME NOT NULL,
                fullDay BOOL NOT NULL DEFAULT 0,
                orderID MEDIUMINT(9) NOT NULL,
                isBusy BOOL NOT NULL DEFAULT 1,
                notes LONGTEXT NOT NULL,
                categoryID SMALLINT(5) NOT NULL DEFAULT 1,
                customerEmail VARCHAR(250) NOT NULL DEFAULT '',
                customerAddress TEXT NOT NULL,          
    		UNIQUE KEY id (id)
    		) $charset_collate;";
        dbDelta($sql);
    }

    $db_table_name = $wpdb->prefix . "wpefc_calendarCategories";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
                calendarID SMALLINT(5) NOT NULL DEFAULT 1,
    		title VARCHAR(250) NOT NULL,    	
                color VARCHAR(64) NOT NULL DEFAULT '#1abc9c',  
    		UNIQUE KEY id (id)
    		) $charset_collate;";
        dbDelta($sql);
        $rows_affected = $wpdb->insert($db_table_name, array('title' => 'Default', 'color' => '#1abc9c', 'calendarID' => 1));
    }



    $db_table_name = $wpdb->prefix . "wpefc_calendarReminders";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
                calendarID mediumint(9) NOT NULL,
                eventID mediumint(9) NOT NULL,
                title VARCHAR(250) NOT NULL,
                content LONGTEXT NOT NULL,
                isSent BOOL NOT NULL DEFAULT 0,
                method VARCHAR(64) NOT NULL DEFAULT 'email',
                delayType VARCHAR(16) NOT NULL DEFAULT 'day',	
                delayValue SMALLINT(5) NOT NULL DEFAULT 1,    
                email VARCHAR(250) NOT NULL DEFAULT '',    
    		UNIQUE KEY id (id)
    		) $charset_collate;";
        dbDelta($sql);
    }

    $db_table_name = $wpdb->prefix . "wpefc_customers";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
                email VARCHAR(250) NOT NULL,
                password VARCHAR(250) NOT NULL,                
    		UNIQUE KEY id (id)
    		) $charset_collate;";
        dbDelta($sql);
    }

    update_option('lfbK', md5(uniqid(rand(), true)));

    global $isInstalled;
    $isInstalled = true;
}

// End install()

function lfb_setThemeMode() {
    update_option("lfb_themeMode", true);
}

/**
 * Update database
 * @access  public
 * @since   2.0
 * @return  void
 */
function lfb_checkDBUpdates($version) {
    global $wpdb;
    $installed_ver = get_option("wpecf_version");
    require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

    if (!$installed_ver || $installed_ver < 8.5) {
        $db_table_name = $wpdb->prefix . "lfb_items";
        if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") == $db_table_name) {
            $sql = "RENAME TABLE " . $db_table_name . " TO " . $wpdb->prefix . "wpefc_items;";
            $wpdb->query($sql);
        } else {
            $db_table_name = $wpdb->prefix . "wpefc_items";
            if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
                if (!empty($wpdb->charset))
                    $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
                if (!empty($wpdb->collate))
                    $charset_collate .= " COLLATE $wpdb->collate";

                $sql = "CREATE TABLE $db_table_name (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    title VARCHAR(120) NOT NULL,
                    description TEXT NOT NULL,
                    ordersort mediumint(9) NOT NULL,
                    image VARCHAR(250) NOT NULL,
                    groupitems VARCHAR(120) NOT NULL,
                    type VARCHAR(120) NOT NULL,
                    stepID mediumint(9) NOT NULL,
                    formID mediumint(9) NOT NULL,
                    price FLOAT NOT NULL,
                    operation VARCHAR(1) NOT NULL DEFAULT '+',
                    ischecked BOOL,
                    isRequired BOOL,
                    quantity_enabled BOOL,
                    quantity_max SMALLINT(5)  NOT NULL,
                    reduc_enabled BOOL NOT NULL,
                    reduc_qt SMALLINT(5) NOT NULL,
                    reduc_value FLOAT NOT NULL,
                    reducsQt TEXT NOT NULL,
                    isWooLinked BOOL,
                    wooProductID SMALLINT(5)  NOT NULL,
                    wooVariation SMALLINT(9)  NOT NULL,
                    imageTint BOOL,
                    showPrice BOOL NOT NULL,
                    useRow BOOL NOT NULL,
                    UNIQUE KEY id (id)
                    ) $charset_collate;";
                dbDelta($sql);
            }
        }
    }

    if (!$installed_ver || $installed_ver < 9.11) {
        $db_table_name = $wpdb->prefix . "wpefc_logs";
        if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
            if (!empty($wpdb->charset))
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if (!empty($wpdb->collate))
                $charset_collate .= " COLLATE $wpdb->collate";

            $sql = "CREATE TABLE $db_table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            formID mediumint (9) NOT NULL,
            ref VARCHAR(120) NOT NULL,
            email VARCHAR(120) NOT NULL,
            content TEXT NOT NULL,
            dateLog VARCHAR(64) NOT NULL,
            UNIQUE KEY id (id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }

    if (!$installed_ver || $installed_ver < 9.14) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD  hideFinalPrice BOOL DEFAULT 0;";
        $wpdb->query($sql);
    }

    if (!$installed_ver || $installed_ver < 9.15) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD  priceFontSize SMALLINT NOT NULL DEFAULT 18;";
        $wpdb->query($sql);
    }


    if (!$installed_ver || $installed_ver < 9.182) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD  customCss TEXT NOT NULL;";
        $wpdb->query($sql);
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD  optionsValues TEXT NOT NULL;";
        $wpdb->query($sql);
    }

    if (!$installed_ver || $installed_ver < 9.186) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD disableTipMobile BOOL NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.187) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD quantity_min SMALLINT(5)  NOT NULL;";
        $wpdb->query($sql);

        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN wooProductID mediumint(9) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN wooVariation mediumint(9) NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.193) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD legalNoticeContent TEXT NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD legalNoticeTitle TEXT NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD legalNoticeEnable BOOL NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.195) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD urlTarget VARCHAR(250)  NOT NULL;";
        $wpdb->query($sql);
    }

    if (!$installed_ver || $installed_ver < 9.21) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD datepickerLang VARCHAR(16)  NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.24) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD percentToPay FLOAT DEFAULT 100 ;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD  colorBg VARCHAR(16) NOT NULL;";
        $wpdb->query($sql);


        $forms = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        foreach ($forms as $form) {
            $wpdb->update($table_name, array('percentToPay' => 100), array('id' => $form->id));
        }
        mkdir('uploads');
        chmod("uploads", 0747);
    }
    if (!$installed_ver || $installed_ver < 9.34) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD thousandsSeparator VARCHAR(4) NOT NULL ;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD decimalsSeparator VARCHAR(4) NOT NULL ;";
        $wpdb->query($sql);
    }

    if (!$installed_ver || $installed_ver < 9.35) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD useSummary BOOL NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD summary_title VARCHAR(240) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD summary_description VARCHAR(240) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD summary_quantity VARCHAR(240) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD summary_price VARCHAR(240) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD summary_total VARCHAR(240) NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.370) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '" . $table_name . "' AND column_name = 'qtType'");

        if (empty($row)) {
            $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN qtType SMALLINT(5) NOT NULL;";
            $wpdb->query($sql);
        }
        $table_name = $wpdb->prefix . "wpefc_steps";
        $sql = "ALTER TABLE " . $table_name . " ADD description TEXT NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.382) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD summary_value VARCHAR(240) NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.385) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD showInSummary BOOL DEFAULT 1;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.386) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD paypal_useIpn BOOL DEFAULT 0;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD paypal_useSandbox BOOL DEFAULT 0;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD paypal_subsFrequency SMALLINT(5) NOT NULL DEFAULT 1;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD paypal_subsFrequencyType VARCHAR(1) NOT NULL DEFAULT 'M';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD paypal_subsMaxPayments SMALLINT(5) NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD isSubscription BOOL DEFAULT 0;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD subscription_text VARCHAR(250) NOT NULL DEFAULT '/month';";
        $wpdb->query($sql);

        $table_name = $wpdb->prefix . "wpefc_logs";
        $sql = "ALTER TABLE " . $table_name . " ADD sendToUser BOOL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD contentUser TEXT NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD checked BOOL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.394) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD summary_hideQt BOOL DEFAULT 0;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD summary_hideZero BOOL DEFAULT 0;";
        $wpdb->query($sql);
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD richtext TEXT NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.396) {
        $table_name = $wpdb->prefix . "wpefc_steps";
        $sql = "ALTER TABLE " . $table_name . " ADD showInSummary BOOL  NOT NULL DEFAULT 1;";
        $wpdb->query($sql);

        $steps = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        foreach ($steps as $step) {
            $wpdb->update($table_name, array('showInSummary' => true), array('id' => $step->id));
        }

        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD imageDes VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
    }

    if (!$installed_ver || $installed_ver < 9.407) {
        $db_table_name = $wpdb->prefix . "wpefc_coupons";
        if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
            if (!empty($wpdb->charset))
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if (!empty($wpdb->collate))
                $charset_collate .= " COLLATE $wpdb->collate";

            $sql = "CREATE TABLE $db_table_name (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    formID mediumint(9) NOT NULL,
                    couponCode VARCHAR(250) NOT NULL,
                    reduction FLOAT NOT NULL,
                    reductionType VARCHAR(64) NOT NULL,
                    useMax SMALLINT(5) NOT NULL DEFAULT 1,
                    currentUses SMALLINT(5) NOT NULL,
                    UNIQUE KEY id (id)
                    ) $charset_collate;";
            dbDelta($sql);
        }

        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD useCoupons BOOL NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD couponText VARCHAR(250) NOT NULL DEFAULT 'Discount coupon code';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD summary_discount VARCHAR(240) NOT NULL DEFAULT 'Discount :';";
        $wpdb->query($sql);

        $forms = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        foreach ($forms as $form) {
            $wpdb->update($table_name, array('summary_discount' => 'Discount :'), array('id' => $form->id));
        }
    }

    if (!$installed_ver || $installed_ver < 9.410) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD summary_hidePrices BOOL NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD groupAutoClick BOOL NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD inverseGrayFx BOOL NOT NULL;";
        $wpdb->query($sql);
        $forms = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        foreach ($forms as $form) {
            $wpdb->update($table_name, array('groupAutoClick' => 1), array('id' => $form->id));
        }
    }
    if (!$installed_ver || $installed_ver < 9.412) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD useMailchimp BOOL NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD mailchimpKey VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD mailchimpList VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD useMailpoet BOOL NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD mailPoetList VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD useGetResponse BOOL NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD getResponseKey VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD getResponseList VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD millionSeparator VARCHAR(4) NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.416) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD isHidden BOOL NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.417) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD minSize SMALLINT(9) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD maxSize SMALLINT(9) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD isNumeric BOOL NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.420) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD isSinglePrice BOOL NOT NULL;";
        $wpdb->query($sql);

        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD loadAllPages BOOL NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.424) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD filesUpload_text VARCHAR(250) NOT NULL DEFAULT 'Drop files here to upload';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD filesUploadSize_text VARCHAR(250) NOT NULL DEFAULT 'File is too big (max size: {{maxFilesize}}MB)';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD filesUploadType_text VARCHAR(250) NOT NULL DEFAULT 'Invalid file type';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD filesUploadLimit_text VARCHAR(250) NOT NULL DEFAULT 'You can not upload any more files';";
        $wpdb->query($sql);

        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD maxFiles SMALLINT(9) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD allowedFiles VARCHAR(250) NOT NULL DEFAULT '.png,.jpg,.jpeg,.gif,.zip,.rar';";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.426) {
        $table_name = $wpdb->prefix . "wpefc_links";
        $sql = "ALTER TABLE " . $table_name . " ADD operator VARCHAR(8) NOT NULL;";
        $wpdb->query($sql);
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD useCalculation BOOL NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD calculation TEXT NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.438) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD fieldType VARCHAR(64) NOT NULL;";
        $wpdb->query($sql);

        $table_name = $wpdb->prefix . "wpefc_logs";
        $sql = "ALTER TABLE " . $table_name . " ADD phone VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD lastName VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD firstName VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD address TEXT NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD city VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD country VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD state VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD zip VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);

        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD mailchimpOptin BOOL NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD useGoogleFont BOOL NOT NULL DEFAULT 1;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD googleFontName VARCHAR(250) NOT NULL DEFAULT 'Lato';";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.439) {
        $table_name = $wpdb->prefix . "wpefc_steps";
        $sql = "ALTER TABLE " . $table_name . " ADD itemsPerRow TINYINT(2) NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.440) {
        $table_name = $wpdb->prefix . "wpefc_logs";
        $sql = "ALTER TABLE " . $table_name . " ADD totalPrice FLOAT NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD totalSubscription FLOAT NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD subscriptionFrequency VARCHAR(64) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD formTitle VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD contentTxt TEXT NOT NULL;";
        $wpdb->query($sql);
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD analyticsID VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
    }

    if (!$installed_ver || $installed_ver < 9.445) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD useShowConditions BOOL NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD showConditions TEXT NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD showConditionsOperator VARCHAR(8) NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.451) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD sendPdfCustomer BOOL NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD sendPdfAdmin BOOL NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.458) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD usePaypalIfChecked BOOL NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.464) {
        $table_name = $wpdb->prefix . "wpefc_fields";
        $sql = "ALTER TABLE " . $table_name . " ADD fieldType VARCHAR(64) NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.465) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD sendContactASAP BOOL NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.472) {
        $table_name = $wpdb->prefix . "wpefc_steps";
        $sql = "ALTER TABLE " . $table_name . " ADD showConditions TEXT NOT NULL;";
        $wpdb->query($sql);
        $table_name = $wpdb->prefix . "wpefc_steps";
        $sql = "ALTER TABLE " . $table_name . " ADD showConditionsOperator VARCHAR(8) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD useShowConditions BOOL NOT NULL;";
        $wpdb->query($sql);
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN showSteps SMALLINT(5) NOT NULL;";
        $wpdb->query($sql);
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD showTotalBottom BOOL NOT NULL;";
        $wpdb->query($sql);
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN minSize INT(11) NOT NULL;";
        $wpdb->query($sql);
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN maxSize INT(11) NOT NULL;";
        $wpdb->query($sql);
    }

    if (!$installed_ver || $installed_ver < 9.474) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD use_stripe BOOL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD stripe_useSandbox BOOL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD stripe_secretKey VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD stripe_publishKey VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD stripe_label_creditCard VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD stripe_label_cvc VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD stripe_label_expiration VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD stripe_currency VARCHAR(6) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD stripe_subsFrequencyType VARCHAR(16) NOT NULL DEFAULT 'month';";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.475) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN quantity_max INT(11) NOT NULL;";
        $wpdb->query($sql);
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN quantity_min INT(11) NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.476) {
        $table_name = $wpdb->prefix . "wpefc_logs";
        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN content MEDIUMTEXT NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN contentUser MEDIUMTEXT NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN contentTxt MEDIUMTEXT NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.496) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD scrollTopMargin INT(9) NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.502) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD redirectionDelay INT(9) NOT NULL DEFAULT 5;";
        $wpdb->query($sql);
        $forms = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        foreach ($forms as $form) {
            $wpdb->update($table_name, array('redirectionDelay' => 5), array('id' => $form->id));
        }
    }
    if (!$installed_ver || $installed_ver < 9.505) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN reducsQt LONGTEXT NOT NULL;";
    }
    if (!$installed_ver || $installed_ver < 9.514) {
        $db_table_name = $wpdb->prefix . "wpefc_redirConditions";
        if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
            if (!empty($wpdb->charset))
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if (!empty($wpdb->collate))
                $charset_collate .= " COLLATE $wpdb->collate";

            $sql = "CREATE TABLE $db_table_name (
                        id mediumint(9) NOT NULL AUTO_INCREMENT,
                        formID mediumint (9) NOT NULL,    		
                        conditions TEXT NOT NULL,
                        conditionsOperator VARCHAR(4) NOT NULL DEFAULT '+',
                        url VARCHAR(250) NOT NULL,
                        UNIQUE KEY id (id)
                        ) $charset_collate;";
            dbDelta($sql);
        }

        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD useRedirectionConditions BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.515) {

        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD gmap_key VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD txtDistanceError TEXT NOT NULL;";
        $wpdb->query($sql);
        $forms = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        foreach ($forms as $form) {
            $wpdb->update($table_name, array('txtDistanceError' => 'Calculating the distance could not be performed, please verify the input addresses'), array('id' => $form->id));
        }
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD useDistanceAsQt BOOL NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD distanceQt VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.525) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD customJS TEXT NOT NULL;";
        $wpdb->query($sql);
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD hideQtSummary BOOL NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.526) {
        $table_name = $wpdb->prefix . "wpefc_steps";
        $sql = "ALTER TABLE " . $table_name . " ADD hideNextStepBtn BOOL NOT NULL;";
        $wpdb->query($sql);
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD defaultValue TEXT NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.532) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD summary_hideTotal BOOL NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD colorSecondary VARCHAR(16) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD colorSecondaryTxt VARCHAR(16) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD colorCbCircle VARCHAR(16) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD colorCbCircleOn VARCHAR(16) NOT NULL;";
        $wpdb->query($sql);

        $forms = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        foreach ($forms as $form) {
            $wpdb->update($table_name, array('colorSecondary' => '#bdc3c7', 'colorSecondaryTxt' => '#ffffff',
                'colorCbCircle' => '#7f8c9a', 'colorCbCircleOn' => '#bdc3c7'), array('id' => $form->id));
        }
    }
    if (!$installed_ver || $installed_ver < 9.535) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD fileSize INT(9) NOT NULL DEFAULT 25;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.537) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD disableDropdowns BOOL NOT NULL DEFAULT 1;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.544) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD usedCssFile VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD formStyles LONGTEXT NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD columnsWidth SMALLINT(5) NOT NULL;";
        $wpdb->query($sql);
        $table_name = $wpdb->prefix . "wpefc_settings";
        $sql = "ALTER TABLE " . $table_name . " ADD tdgn_enabled BOOL NOT NULL;";
        $wpdb->query($sql);
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD firstValueDisabled BOOL NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.547) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD paypal_languagePayment VARCHAR(8) NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.549) {
        $table_name = $wpdb->prefix . "wpefc_settings";
        $sql = "ALTER TABLE " . $table_name . " ADD firstStart BOOL NOT NULL DEFAULT 1;";
        $wpdb->query($sql);
        $wpdb->update($table_name, array('firstStart' => 0), array('id' => 1));
    }
    if (!$installed_ver || $installed_ver < 9.550) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD sliderStep SMALLINT(5) NOT NULL DEFAULT 1;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.551) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD inlineLabels BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD previousStepBtn BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD alignLeft BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.552) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD totalIsRange BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD totalRange SMALLINT(5) NOT NULL DEFAULT 100;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD labelRangeBetween VARCHAR(128) NOT NULL DEFAULT 'between';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD labelRangeAnd VARCHAR(128) NOT NULL DEFAULT 'and';";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.553) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD useCaptcha BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD captchaLabel VARCHAR(250) NOT NULL DEFAULT 'Please rewrite the following text in the field';";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.554) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $forms = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        foreach ($forms as $form) {
            if ($form->usedCssFile != "" && file_exists(plugin_dir_path(__FILE__) . 'export/' . $form->usedCssFile)) {
                $style = file_get_contents(plugin_dir_path(__FILE__) . 'export/' . $form->usedCssFile);
                $wpdb->update($table_name, array('formStyles' => $style), array('id' => $form->id));
            }
        }

        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD summary_noDecimals BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.555) {

        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD scrollTopPage BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.560) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN sliderStep INT(11) NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.562) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD email_name VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.563) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD date_allowPast BOOL NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD date_showMonths BOOL NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD date_showYears BOOL NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.564) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD stripe_percentToPay FLOAT DEFAULT 100;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.565) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD nextStepButtonIcon VARCHAR(250) NOT NULL DEFAULT 'fa-check';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD previousStepButtonIcon VARCHAR(250) NOT NULL DEFAULT 'fa-arrow-left';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD finalButtonIcon VARCHAR(250) NOT NULL DEFAULT 'fa-check';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD introButtonIcon VARCHAR(250) NOT NULL DEFAULT 'fa-check';";
        $wpdb->query($sql);
        $table_name = $wpdb->prefix . "wpefc_forms";
        $forms = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        foreach ($forms as $form) {
            $wpdb->update($table_name, array('nextStepButtonIcon' => '', 'previousStepButtonIcon' => '', 'finalButtonIcon' => '', 'introButtonIcon' => ''), array('id' => $form->id));
        }
    }
    if (!$installed_ver || $installed_ver < 9.566) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD imgIconStyle VARCHAR(64) NOT NULL DEFAULT 'circles';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD colorPageBg VARCHAR(16) NOT NULL DEFAULT '#ffffff';";
        $wpdb->query($sql);
        $table_name = $wpdb->prefix . "wpefc_forms";
        $forms = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        foreach ($forms as $form) {
            $wpdb->update($table_name, array('imgIconStyle' => 'circle', 'colorPageBg' => '#ffffff'), array('id' => $form->id));
        }
    }
    if (!$installed_ver || $installed_ver < 9.568) {
        $table_nameI = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_nameI . " ADD shortcode VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.569) {
        $table_nameF = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_nameF . " ADD summary_hideFinalStep BOOL NOT NULL DEFAULT 1;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.570) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD timeModeAM BOOL NOT NULL DEFAULT 1;";
        $wpdb->query($sql);

        $table_name = $wpdb->prefix . "wpefc_fields";
        $table_nameI = $wpdb->prefix . "wpefc_items";
        $table_nameF = $wpdb->prefix . "wpefc_forms";

        $fields = $wpdb->get_results("SELECT * FROM $table_name ORDER BY ordersort ASC,id ASC");
        foreach ($fields as $field) {
            $addToCss = '';
            $type = 'textfield';
            if ($field->typefield == 'textarea') {
                $type = 'textarea';
            }
            $useShowConditions = 0;
            $showConditions = '';
            if ($field->visibility == 'toggle') {
                $chechboxToggle = $wpdb->insert($table_nameI, array('formID' => $field->formID, 'stepID' => 0, 'title' => $field->label, 'type' => 'checkbox', 'ordersort' => $field->ordersort, 'showInSummary' => 0, 'useRow' => 1));
                $lastid = $wpdb->insert_id;
                $useShowConditions = 1;
                $showConditions = '[{"interaction":"0_' . $lastid . '","action":"clicked"}]';

                $form = $wpdb->get_results("SELECT * FROM $table_nameF WHERE id='" . $field->formID . "' LIMIT 1");
                if (count($form) > 0) {
                    $form = $form[0];
                }
            }
            $isRequired = 0;
            if ($field->validation != "") {
                $isRequired = 1;
            }
            if ($field->validation == 'email') {
                $field->fieldType = 'email';
            }
            $newItem = $wpdb->insert($table_nameI, array('formID' => $field->formID, 'stepID' => 0,
                'title' => $field->label,
                'type' => $type,
                'showConditions' => $showConditions,
                'useShowConditions' => $useShowConditions,
                'isRequired' => $isRequired,
                'fieldType' => $field->fieldType,
                'useRow' => 1,
                'ordersort' => $field->ordersort
            ));
            $newItemID = $wpdb->insert_id;
            if ($field->visibility == 'toggle') {
                $addToCss .= '#estimation_popup[data-form="' . $field->formID . '"] #mainPanel .lfb_item.lfb_itemContainer_' . $newItemID . ' textarea {margin-top:-22px}' . "\n";
                $addToCss .= '#estimation_popup[data-form="' . $field->formID . '"] #mainPanel .lfb_item.lfb_itemContainer_' . $newItemID . ' label {display:none !important;}' . "\n";

                $form = $wpdb->get_results("SELECT * FROM $table_nameF WHERE id='" . $field->formID . "' LIMIT 1");
                if (count($form) > 0) {
                    $form = $form[0];
                    $wpdb->update($table_nameF, array('customCss' => $form->customCss . "\n" . $addToCss), array('id' => $form->id));
                }
            }
        }
    }
    if (!$installed_ver || $installed_ver < 9.571) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $forms = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        foreach ($forms as $form) {
            $form->customCss = str_replace('label {display:none !important;}', ":not(.switch-animate)>label {display:none !important;}", $form->customCss);
            $wpdb->update($table_name, array('customCss' => $form->customCss), array('id' => $form->id));
        }
    }
    if (!$installed_ver || $installed_ver < 9.572) {
        $table_nameI = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_nameI . " ADD minTime VARCHAR(16) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_nameI . " ADD maxTime VARCHAR(16) NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.573) {
        $table_nameI = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_nameI . " ADD dontAddToTotal BOOL NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.576) {
        $table_nameI = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_nameI . " ADD useCalculationQt BOOL NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_nameI . " ADD calculationQt TEXT NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.578) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN wooProductID INT(11)  NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN wooVariation INT(11)  NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.579) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD  save_to_cart_edd BOOL NOT NULL;";
        $wpdb->query($sql);
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD  eddProductID INT(11)  NOT NULL;";
        $wpdb->query($sql);
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD eddVariation INT(11)  NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.581) {

        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD  placeholder VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD  validation VARCHAR(64)  NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD   validationMin SMALLINT(5) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD   validationMax SMALLINT(5) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD  validationCaracts VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);

        $items = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        foreach ($items as $item) {
            if ($item->fieldType == "email") {
                $wpdb->update($table_name, array('validation' => 'email'), array('id' => $item->id));
            }
            if ($item->fieldType == "phone") {
                $wpdb->update($table_name, array('validation' => 'phone'), array('id' => $item->id));
            }
        }
    }
    if (!$installed_ver || $installed_ver < 9.582) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD  icon VARCHAR(128) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD  iconPosition BOOL NOT NULL;";
        $wpdb->query($sql);

        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD  fieldsPreset VARCHAR(64) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD  enableFlipFX BOOL NOT NULL DEFAULT 1;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD  enableShineFxBtn BOOL NOT NULL DEFAULT 1;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.585) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD maxWidth SMALLINT(5) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD maxHeight SMALLINT(5) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD autocomplete BOOL NOT NULL;";
        $wpdb->query($sql);

        $db_table_name = $wpdb->prefix . "wpefc_layeredImages";
        if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
            if (!empty($wpdb->charset))
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if (!empty($wpdb->collate))
                $charset_collate .= " COLLATE $wpdb->collate";

            $sql = "CREATE TABLE $db_table_name (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    formID SMALLINT(5) NOT NULL,
                    itemID SMALLINT(5) NOT NULL,
                    title VARCHAR(120) NOT NULL,
                    ordersort mediumint(9) NOT NULL,
                    image VARCHAR(250) NOT NULL,
                    showConditions TEXT NOT NULL,
                    showConditionsOperator VARCHAR(8) NOT NULL,           
                    UNIQUE KEY id (id)
                    ) $charset_collate;";
            dbDelta($sql);
        }
    }
    if (!$installed_ver || $installed_ver < 9.587) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD intro_image VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.590) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD urlTargetMode VARCHAR(64) NOT NULL DEFAULT '_blank';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD color VARCHAR(64) NOT NULL DEFAULT '#1abc9c';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD callNextStep BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
    }

    if (!$installed_ver || $installed_ver < 9.593) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD paymentType VARCHAR(64) NOT NULL DEFAULT 'form';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD enableEmailPaymentText TEXT NOT NULL ;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD txt_invoice VARCHAR(250) NOT NULL DEFAULT 'Invoice';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD txt_quotation VARCHAR(250) NOT NULL DEFAULT 'Quotation';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD emailPaymentType VARCHAR(64) NOT NULL DEFAULT 'checkbox';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD txt_payFormFinalTxt VARCHAR(250) NOT NULL DEFAULT 'Thank you for your order, we will contact you soon';";
        $wpdb->query($sql);

        $table_name = $wpdb->prefix . "wpefc_forms";
        $forms = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        foreach ($forms as $form) {
            $wpdb->update($table_name, array('enableEmailPaymentText' => 'I validate this order and proceed to the payment',
                'paymentType' => 'form', 'emailPaymentType' => 'checkbox'), array('id' => $form->id));
        }

        $table_name = $wpdb->prefix . "wpefc_logs";
        $sql = "ALTER TABLE " . $table_name . " ADD paid BOOL NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD paymentKey VARCHAR(250) NOT NULL DEFAULT '';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD finalUrl VARCHAR(250) NOT NULL DEFAULT '';";
        $wpdb->query($sql);
    }


    if (!$installed_ver || $installed_ver < 9.596) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD stripe_payMode VARCHAR(64) NOT NULL DEFAULT '';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD stripe_fixedToPay FLOAT DEFAULT 100;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD paypal_payMode VARCHAR(64) NOT NULL DEFAULT '';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD paypal_fixedToPay FLOAT DEFAULT 100;";
        $wpdb->query($sql);

        $table_name = $wpdb->prefix . "wpefc_forms";
        $forms = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        foreach ($forms as $form) {
            if ($form->percentToPay != 100) {
                $wpdb->update($table_name, array('paypal_payMode' => 'percent'), array('id' => $form->id));
            }
            if ($form->stripe_percentToPay != 100) {
                $wpdb->update($table_name, array('stripe_payMode' => 'percent'), array('id' => $form->id));
            }
        }
    }

    if (!$installed_ver || $installed_ver < 9.598) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD summary_showAllPricesEmail BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
        $imgStripeUrl = esc_url(trailingslashit(plugins_url('/assets/', __FILE__))) . 'img/powered_by_stripe@2x.png';
        $sql = "ALTER TABLE " . $table_name . " ADD stripe_logoImg VARCHAR(250) NOT NULL DEFAULT '" . $imgStripeUrl . "';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD enableFloatingSummary BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD floatSummary_icon VARCHAR(250) NOT NULL DEFAULT 'fa-shopping-cart';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD floatSummary_label VARCHAR(250) NOT NULL DEFAULT '';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD floatSummary_numSteps BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.599) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD floatSummary_hidePrices BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.602) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD disableLinksAnim BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.603) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD imgTitlesStyle VARCHAR(16) NOT NULL DEFAULT '';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN email_adminContent LONGTEXT NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN email_userContent LONGTEXT NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.604) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD useValueAsQt BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.605) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD emailCustomerLinks BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.606) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD totalRangeMode VARCHAR(16) NOT NULL DEFAULT '';";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.608) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD sendEmailLastStep BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD enableSaveForLaterBtn BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD saveForLaterLabel VARCHAR(250) NOT NULL DEFAULT '';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD saveForLaterIcon VARCHAR(250) NOT NULL DEFAULT 'fa-floppy-o';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD saveForLaterDelLabel VARCHAR(250) NOT NULL DEFAULT 'Delete backup';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD lastSave VARCHAR(250) NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.609) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD billionsSeparator VARCHAR(4) NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.610) {

        $table_name = $wpdb->prefix . "wpefc_logs";
        $sql = "ALTER TABLE " . $table_name . " ADD pdfContent LONGTEXT NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD pdfContentUser LONGTEXT NOT NULL;";
        $wpdb->query($sql);

        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD pdf_userContent LONGTEXT NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD pdf_adminContent LONGTEXT NOT NULL;";
        $wpdb->query($sql);

        $forms = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        foreach ($forms as $form) {
            $wpdb->update($table_name, array('pdf_userContent' => $form->email_userContent, 'pdf_adminContent' => $form->email_adminContent), array('id' => $form->id));
        }
    }

    if (!$installed_ver || $installed_ver < 9.612) {

        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD dateType VARCHAR(32) NOT NULL DEFAULT 'date';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD calendarID MEDIUMINT(9) NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD eventDuration SMALLINT(5) NOT NULL DEFAULT 1;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD eventDurationType VARCHAR(25) NOT NULL DEFAULT 'hours';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD eventCategory SMALLINT(5) NOT NULL DEFAULT 1;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD registerEvent BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD eventBusy BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD eventTitle VARCHAR(250) NOT NULL DEFAULT 'New event';";
        $wpdb->query($sql);

        $table_name = $wpdb->prefix . "wpefc_logs";
        $sql = "ALTER TABLE " . $table_name . " ADD eventsData LONGTEXT NOT NULL;";
        $wpdb->query($sql);

        $db_table_name = $wpdb->prefix . "wpefc_calendars";
        if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
            if (!empty($wpdb->charset))
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if (!empty($wpdb->collate))
                $charset_collate .= " COLLATE $wpdb->collate";

            $sql = "CREATE TABLE $db_table_name (
    		    id mediumint(9) NOT NULL AUTO_INCREMENT,
    		    title VARCHAR(250) NOT NULL,    
                    unavailableDays VARCHAR(32) NOT NULL DEFAULT '',	
                    unavailableHours VARCHAR(64) NOT NULL DEFAULT '',	    
    		UNIQUE KEY id (id)
    		) $charset_collate;";
            dbDelta($sql);
            $rows_affected = $wpdb->insert($db_table_name, array('title' => 'Default', 'unavailableDays' => '', 'unavailableHours' => ''));
        }

        $db_table_name = $wpdb->prefix . "wpefc_calendarEvents";
        if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
            if (!empty($wpdb->charset))
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if (!empty($wpdb->collate))
                $charset_collate .= " COLLATE $wpdb->collate";

            $sql = "CREATE TABLE $db_table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
                calendarID SMALLINT(5) NOT NULL,
    		title VARCHAR(250) NOT NULL,    	
                startDate DATETIME NOT NULL,	
                endDate DATETIME NOT NULL,
                fullDay BOOL NOT NULL DEFAULT 0,
                orderID MEDIUMINT(9) NOT NULL,
                isBusy BOOL NOT NULL DEFAULT 1,
                notes LONGTEXT NOT NULL,
                categoryID SMALLINT(5) NOT NULL DEFAULT 1,
                customerEmail VARCHAR(250) NOT NULL DEFAULT '',
                customerAddress TEXT NOT NULL,
    		UNIQUE KEY id (id)
    		) $charset_collate;";
            dbDelta($sql);
        }

        $db_table_name = $wpdb->prefix . "wpefc_calendarCategories";
        if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {

            if (!empty($wpdb->charset))
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if (!empty($wpdb->collate))
                $charset_collate .= " COLLATE $wpdb->collate";

            $sql = "CREATE TABLE $db_table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
                calendarID SMALLINT(5) NOT NULL DEFAULT 1,
    		title VARCHAR(250) NOT NULL DEFAULT 'Default',    	
                color VARCHAR(64) NOT NULL DEFAULT '#1abc9c',
    		UNIQUE KEY id (id)
    		) $charset_collate;";
            dbDelta($sql);
            $rows_affected = $wpdb->insert($db_table_name, array('title' => 'Default', 'calendarID' => 1, 'color' => '#1abc9c'));
        }

        $db_table_name = $wpdb->prefix . "wpefc_calendarReminders";
        if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
            if (!empty($wpdb->charset))
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if (!empty($wpdb->collate))
                $charset_collate .= " COLLATE $wpdb->collate";

            $sql = "CREATE TABLE $db_table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
                calendarID mediumint(9) NOT NULL,
                eventID mediumint(9) NOT NULL,
                title VARCHAR(250) NOT NULL,
                content LONGTEXT NOT NULL,
                isSent BOOL NOT NULL DEFAULT 0,
                method VARCHAR(64) NOT NULL DEFAULT 'email',
                delayType VARCHAR(16) NOT NULL DEFAULT 'day',	
                delayValue SMALLINT(5) NOT NULL DEFAULT 1,   
                email VARCHAR(250) NOT NULL DEFAULT '',
                UNIQUE KEY id (id)
                ) $charset_collate;";
            dbDelta($sql);
        }

        $table_name = $wpdb->prefix . "wpefc_items";
        $items = $wpdb->get_results("SELECT * FROM $table_name WHERE type='datepicker' ORDER BY id DESC");
        foreach ($items as $item) {
            $wpdb->update($table_name, array('dateType' => 'date'), array('id' => $item->id));
        }
        $items = $wpdb->get_results("SELECT * FROM $table_name WHERE type='timepicker' ORDER BY id DESC");
        foreach ($items as $item) {
            $wpdb->update($table_name, array('type' => 'datepicker', 'dateType' => 'time'), array('id' => $item->id));
        }

        $items = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        foreach ($items as $item) {
            $wpdb->update($table_name, array('eventCategory' => 1, 'eventDuration' => 1, 'eventDurationType' => 'hours', 'eventTitle' => 'New event'), array('id' => $item->id));
        }
    }

    if (!$installed_ver || $installed_ver < 9.615) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD useAsDateRange BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD endDaterangeID MEDIUMINT(9) NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.616) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD disableMinutes BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.618) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD stripe_subsFrequency SMALLINT(5) NOT NULL DEFAULT 1;";
        $wpdb->query($sql);

        $forms = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        foreach ($forms as $form) {
            $wpdb->update($table_name, array('stripe_subsFrequency' => 1), array('id' => $form->id));
        }
    }
    if (!$installed_ver || $installed_ver < 9.619) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD mainTitleTag VARCHAR(6) NOT NULL DEFAULT 'h1';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD stepTitleTag VARCHAR(6) NOT NULL DEFAULT 'h2';";
        $wpdb->query($sql);

        $forms = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        foreach ($forms as $form) {
            $wpdb->update($table_name, array('mainTitleTag' => 'h1', 'stepTitleTag' => 'h2'), array('id' => $form->id));
        }
    }
    if (!$installed_ver || $installed_ver < 9.622) {
        update_option('lfbK', md5(uniqid(rand(), true)));

        $db_table_name = $wpdb->prefix . "wpefc_customers";
        if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
            if (!empty($wpdb->charset))
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if (!empty($wpdb->collate))
                $charset_collate .= " COLLATE $wpdb->collate";

            $sql = "CREATE TABLE $db_table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
                email VARCHAR(250) NOT NULL,
                password VARCHAR(250) NOT NULL,                
    		UNIQUE KEY id (id)
    		) $charset_collate;";
            dbDelta($sql);
        }

        $table_name = $wpdb->prefix . "wpefc_logs";
        $sql = "ALTER TABLE " . $table_name . " ADD customerID mediumint (9) NOT NULL;";
        $wpdb->query($sql);

        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD enableCustomersData BOOL NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD customersDataEmailLink LONGTEXT NOT NULL;";
        $wpdb->query($sql);


        $table_name = $wpdb->prefix . "wpefc_settings";
        $sql = "ALTER TABLE " . $table_name . " ADD txtCustomersDataForgotMailSubject VARCHAR(250) NOT NULL DEFAULT 'Your password to manage your data';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD customerDataAdminEmail VARCHAR(250) NOT NULL DEFAULT 'your@email.here';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD txtCustomersDataWarningText LONGTEXT NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD txtCustomersDataDownloadLink VARCHAR(250) NOT NULL DEFAULT 'Download my data';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD txtCustomersDataDeleteLink VARCHAR(250)NOT NULL DEFAULT 'Delete all my data';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD txtCustomersDataLeaveLink VARCHAR(250) NOT NULL DEFAULT 'Sign out';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD customersDataDeleteDelay SMALLINT(5) NOT NULL DEFAULT 3;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD txtCustomersDataTitle VARCHAR(250) NOT NULL DEFAULT 'Manage my data';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD txtCustomersDataEditLink VARCHAR(250) NOT NULL DEFAULT 'Modify my data';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD customersDataLabelEmail VARCHAR(250) NOT NULL DEFAULT 'Your email';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD customersDataLabelPass VARCHAR(250) NOT NULL DEFAULT 'Your password';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD customersDataLabelModify VARCHAR(250) NOT NULL DEFAULT 'What data do you want to edit ?';";
        $wpdb->query($sql);

        $sql = "ALTER TABLE " . $table_name . " ADD txtCustomersDataForgotPassLink VARCHAR(250) NOT NULL DEFAULT 'Send me my password';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD txtCustomersDataForgotPassSent VARCHAR(250) NOT NULL DEFAULT 'Your password has been sent by email';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD txtCustomersDataForgotPassMail LONGTEXT NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD txtCustomersDataModifyValidConfirm VARCHAR(250) NOT NULL DEFAULT 'Your request has been sent and will be processed as soon as possible';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD txtCustomersDataModifyMailSubject VARCHAR(250) NOT NULL DEFAULT 'Data modification request from a customer';";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD txtCustomersDataDeleteMailSubject VARCHAR(250) NOT NULL DEFAULT 'Data deletion request from a customer';";
        $wpdb->query($sql);


        $table_name = $wpdb->prefix . "wpefc_forms";
        $forms = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        foreach ($forms as $form) {
            $wpdb->update($table_name, array(
                'enableCustomersData' => 0, 'customersDataEmailLink' => 'According to the GDPR law, you can consult your data and delete them from this page: [url]',
                    ), array('id' => $form->id));
        }
        $table_name = $wpdb->prefix . "wpefc_logs";
        $logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        foreach ($logs as $log) {
            $wpdb->update($table_name, array(
                'email' => lfb_stringEncode($log->email),
                'phone' => lfb_stringEncode($log->phone),
                'firstName' => lfb_stringEncode($log->firstName),
                'lastName' => lfb_stringEncode($log->lastName),
                'address' => lfb_stringEncode($log->address),
                'city' => lfb_stringEncode($log->city),
                'country' => lfb_stringEncode($log->country),
                'state' => lfb_stringEncode($log->state),
                'zip' => lfb_stringEncode($log->zip),
                'pdfContent' => lfb_stringEncode($log->pdfContent),
                'pdfContentUser' => lfb_stringEncode($log->pdfContentUser),
                'contentTxt' => lfb_stringEncode($log->contentTxt),
                'content' => lfb_stringEncode($log->content),
                'contentUser' => lfb_stringEncode($log->contentUser)), array('id' => $log->id));
        }

        $table_name = $wpdb->prefix . "wpefc_settings";
        $wpdb->update($table_name, array(
            'customerDataAdminEmail' => 'your@email.here',
            'txtCustomersDataWarningText' => 'I understand and agree that deleting all data about me may result in the inability to process your order properly.',
            'txtCustomersDataDownloadLink' => 'Download my data', 'txtCustomersDataDeleteLink' => 'Delete all my data', 'txtCustomersDataLeaveLink' => 'Sign out', 'customersDataDeleteDelay' => 3,
            'txtCustomersDataTitle' => 'Manage my data', 'txtCustomersDataEditLink' => 'Modify my data',
            'customersDataLabelEmail' => 'Your email', 'customersDataLabelPass' => 'Your password', 'customersDataLabelModify' => 'What data do you want to edit ?',
            'txtCustomersDataForgotPassLink' => 'Send me my password',
            'txtCustomersDataForgotPassSent' => 'Your password has been sent by email',
            'txtCustomersDataForgotMailSubject' => 'Your password to manage your data',
            'txtCustomersDataForgotPassMail' => "Hello,\nHere is your password to manage your data :\nPassword: [password]\nManage your data from : [url]",
            'txtCustomersDataModifyValidConfirm' => 'Your request has been sent and will be processed as soon as possible',
            'txtCustomersDataModifyMailSubject' => 'Data modification request from a customer',
            'txtCustomersDataDeleteMailSubject' => 'Data deletion request from a customer'), array('id' => 1));

        $table_name = $wpdb->prefix . "wpefc_logs";
        $logs = $wpdb->get_results("SELECT * FROM $table_name  GROUP BY(email)");
        foreach ($logs as $log) {
            if ($log->customerID == 0) {
                $table_nameC = $wpdb->prefix . "wpefc_customers";
                $customerData = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_nameC WHERE email=%s LIMIT 1", $log->email));
                $customerID = 0;
                if (count($customerData) > 0) {
                    $customerID = $customerData[0]->id;
                } else {
                    $pass = lfb_generatePassword();
                    $wpdb->insert($table_nameC, array('email' => $log->email, 'password' => lfb_stringEncode($pass)));
                    $customerID = $wpdb->insert_id;
                }
                $wpdb->update($table_name, array('customerID' => $customerID), array('email' => $log->email));
            }
        }
    }
    if (!$installed_ver || $installed_ver < 9.629) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN calculation LONGTEXT NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN calculationQt LONGTEXT NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN shortcode LONGTEXT NOT NULL;";
        $wpdb->query($sql);

        $table_name = $wpdb->prefix . "wpefc_items";
        $items = $wpdb->get_results("SELECT * FROM $table_name WHERE type='richtext' ORDER BY id DESC");
        foreach ($items as $item) {
            $wpdb->update($table_name, array('useRow' => 1), array('id' => $item->id));
        }
        $items = $wpdb->get_results("SELECT * FROM $table_name WHERE type='shortcode' ORDER BY id DESC");
        foreach ($items as $item) {
            $wpdb->update($table_name, array('useRow' => 1), array('id' => $item->id));
        }
    }
    if (!$installed_ver || $installed_ver < 9.630) {
        $table_name = $wpdb->prefix . "wpefc_settings";
        $sql = "ALTER TABLE " . $table_name . " ADD encryptDB BOOL NOT NULL DEFAULT 1;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 9.631) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN ref_root VARCHAR(32) NOT NULL;";
        $wpdb->query($sql);
        
    }
    if (!$installed_ver || $installed_ver < 9.632) {
        $table_name = $wpdb->prefix . "wpefc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD dontUsePaypalIfChecked BOOL NOT NULL;";
        $wpdb->query($sql);        
    }
    if (!$installed_ver || $installed_ver < 9.633) {
        $table_name = $wpdb->prefix . "wpefc_forms";
        $sql = "ALTER TABLE " . $table_name . " ADD emptyWooCart BOOL NOT NULL;";
        $wpdb->query($sql);        
    }
    
    update_option("wpecf_version", $version);
}

function lfb_stringEncode($value) {
    if ($value != "") {
        $iv = openssl_random_pseudo_bytes(16);
        $text = openssl_encrypt($value, 'aes128', get_option('lfbK'), null, $iv);
        $text = lfb_safe_b64encode($text . '::' . $iv);
    } else {
        $text = "";
    }
    return $text;
}

function lfb_safe_b64encode($string) {
    $data = base64_encode($string);
    $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
    return $data;
}

function lfb_generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $count = mb_strlen($chars);
    for ($i = 0, $result = ''; $i < $length; $i++) {
        $index = rand(0, $count - 1);
        $result .= mb_substr($chars, $index, 1);
    }
    return $result;
}

/**
 * Uninstallation.
 * @access  public
 * @since   1.0.0
 * @return  void
 */
function lfb_uninstall() {
    global $wpdb;
    global $jal_db_version;
    $table_name = $wpdb->prefix . "wpefc_steps";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "wpefc_items";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "wpefc_links";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "wpefc_settings";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "wpefc_forms";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "wpefc_fields";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "wpefc_logs";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "wpefc_coupons";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "wpefc_redirConditions";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "wpefc_layeredImages";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "wpefc_calendars";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "wpefc_calendarEvents";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "wpefc_calendarReminders";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "wpefc_calendarCategories";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "wpefc_customers";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}

Estimation_Form();
