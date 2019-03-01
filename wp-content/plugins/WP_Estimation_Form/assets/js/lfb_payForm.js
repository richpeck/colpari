jQuery(window).on('load', function () {
    lfb_initPayForm();
});

function lfb_initPayForm() {
    jQuery('html,body').css({
       overflow: 'hidden' 
    });
    var content = jQuery('<div id="lfb_bootstraped" class="lfb_bootstraped lfb_payForm"></div>');
    content.append('<div id="estimation_popup" data-form="' + lfb_dataPay.formID + '" class="wpe_bootstraped  wpe_fullscreen"><div id="mainPanel" style="display: block !important;"><div id="lfb_summary"></div></div></div>');
    jQuery('body').html(content);

    if (lfb_dataPay.key != "") {
        jQuery.ajax({
            url: lfb_dataPay.ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_getFormToPay',
                key: lfb_dataPay.key
            },
            success: function (rep) {
                if (rep.length < 5) {
                    jQuery('#lfb_bootstraped.lfb_payForm').remove();
                    document.location.href = lfb_dataPay.homeUrl;
                } else {
                    content.find('#lfb_summary').html(rep);
                    content.find('#lfb_summary table').parent().css({
                       paddingLeft:0,
                       paddingRight: 0
                    });
                    content.find('#lfb_summary').find('[bgcolor]').each(function () {
                        jQuery(this).css({
                            backgroundColor: jQuery(this).attr('bgcolor')
                        });
                    });
                    content.find('#lfb_summary').find('[width]').each(function () {
                        jQuery(this).css({
                            width: jQuery(this).attr('width')
                        });
                    });
                    if (content.find('#lfb_summary table').attr('border') != "0") {
                        content.find('#lfb_summary table').find('th,td').css({
                            border: '1px solid ' + content.find('#lfb_summary table').attr('bordercolor')
                        });
                    }
                    lfb_initPayment();
                }
            }
        });
    }
}

function lfb_initPayment() {
    if (jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #lfb_stripeForm').length > 0) {
        Stripe.setPublishableKey(lfb_dataPay.stripePubKey);
    }

    jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #lfb_stripeForm').submit(function (event) {
        var error = false;
        jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #lfb_stripeForm [data-stripe="number"]').closest('.form-group').removeClass('has-error');
        jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #lfb_stripeForm [data-stripe="exp_month"]').closest('.form-group').removeClass('has-error');
        jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #lfb_stripeForm [data-stripe="exp_year"]').closest('.form-group').removeClass('has-error');
        jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #lfb_stripeForm [data-stripe="cvc"]').closest('.form-group').removeClass('has-error');

        if (jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #lfb_stripeForm [data-stripe="number"]').val().length < 5) {
            error = true;
            jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #lfb_stripeForm [data-stripe="number"]').closest('.form-group').addClass('has-error');
        }
        if (jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #lfb_stripeForm [data-stripe="exp_month"]').val().length < 2) {
            error = true;
            jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #lfb_stripeForm [data-stripe="exp_month"]').closest('.form-group').addClass('has-error');
        }
        if (jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #lfb_stripeForm [data-stripe="exp_year"]').val().length < 2) {
            error = true;
            jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #lfb_stripeForm [data-stripe="exp_year"]').closest('.form-group').addClass('has-error');
        }
        if (jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #lfb_stripeForm [data-stripe="cvc"]').val().length < 2) {
            error = true;
            jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #lfb_stripeForm [data-stripe="cvc"]').closest('.form-group').addClass('has-error');
        }
        if (!error) {
            jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #lfb_stripeForm #wpe_btnOrderStripe').prop('disabled', true);
            Stripe.card.createToken(jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #lfb_stripeForm'), function (status, response) {
                lfb_stripeResponsePay(status, response, lfb_dataPay.formID);
            });
        }
        return false;

    });

    lfb_initPaypalForm();
}

function lfb_stripeResponsePay(status, response, formID) {
    var $form = jQuery('#estimation_popup.wpe_bootstraped[data-form="' + formID + '"] #lfb_stripeForm');
    if (response.error) {
        $form.find('.payment-errors').text(response.error.message);
        $form.find('.btn').prop('disabled', false);
    } else {
        var token = response.id;
        if ($form.find('[name="stripeToken"]').length == 0) {
            $form.append(jQuery('<input type="hidden" name="stripeToken">').val(token));
            Stripe.card.createToken(jQuery('#estimation_popup.wpe_bootstraped[data-form="' + formID + '"] #lfb_stripeForm'), function (statusB, responseB) {
                lfb_stripeResponsePay(statusB, responseB, formID);
            });
        } else if ($form.find('[name="stripeTokenB"]').length == 0) {
            $form.append(jQuery('<input type="hidden" name="stripeTokenB">').val(token));
            lfb_validOrder(lfb_dataPay.formID);
        }
    }
}

function lfb_initPaypalForm(){
    
    if (jQuery('#wtmt_paypalForm').length > 0) {
        
        jQuery('#btnOrderPaypal').click(function(){
            jQuery('#wtmt_paypalForm [name="submit"]').trigger('click');
        });
        
        var payPrice = lfb_dataPay.total;
        payPrice = parseFloat(payPrice) * (parseFloat(lfb_dataPay.percentToPay) / 100);
        payPrice = parseFloat(payPrice).toFixed(2);
        if (lfb_dataPay.priceSingle > 0) {
            var payPriceSingle = parseFloat(lfb_dataPay.total) * (parseFloat(lfb_dataPay.percentToPay) / 100);
            payPriceSingle = parseFloat(payPriceSingle).toFixed(2);
            if (jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=a1]').length == 0) {
                jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm').append('<input type="hidden" name="a1" value="0"/>');
                jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm').append('<input type="hidden" name="p1" value="1"/>');
                jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm').append('<input type="hidden" name="t1" value="M"/>');
            }
            jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=a1]').val(lfb_dataPay.totalSub);
            jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=p1]').val(jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=p3]').val());

            if (payPrice <= 0) {
                jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=cmd]').val('_xclick');
                jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=a3]').remove();
                jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=t3]').remove();
                jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=p3]').remove();
                jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=bn]').remove();
                jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=no_note]').remove();
                jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=src]').remove();
                jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=a1]').remove();
                jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=t1]').remove();
                jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=p1]').remove();
                jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm').append('<input type="hidden" name="amount" value="' + lfb_dataPay.total + '"/>');
            }
        } else {
            jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=a1]').remove();
            jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=t1]').remove();
            jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=p1]').remove();
            jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=amount]').val(lfb_dataPay.total);
        }


        jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=return]').val(lfb_dataPay.finalUrl);
        jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=a3]').val(lfb_dataPay.total);
        jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=custom]').val(lfb_dataPay.ref);
        jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] #wtmt_paypalForm [name=item_number]').val(lfb_dataPay.ref);
        jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.ref + '"] #wtmt_paypalForm [name=item_name]').val(jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.ref + '"] #wtmt_paypalForm [name=item_name]').val() + ' - ' + lfb_dataPay.ref);
        jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.ref + '"] #wtmt_paypalForm [type="submit"]').trigger('click');
    } 
}

function lfb_validOrder(formID) {
    var stripeToken = '';
    if (jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] [name="stripeToken"]').length > 0) {
        stripeToken = jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] [name="stripeToken"]').val();
    }
    var stripeTokenB = '';
    if (jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] [name="stripeTokenB"]').length > 0) {
        stripeTokenB = jQuery('#estimation_popup.wpe_bootstraped[data-form="' + lfb_dataPay.formID + '"] [name="stripeTokenB"]').val();
    }


    jQuery.ajax({
        url: lfb_dataPay.ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_validPayForm',
            formID: lfb_dataPay.formID,
            orderKey: lfb_dataPay.key,
            stripeToken: stripeToken,
            stripeTokenB: stripeTokenB
        },
        success: function (rep) {
            jQuery('#estimation_popup #mainPanel').prepend('<h2 style="display:none;" id="lfb_payFormFinalTxt">' + lfb_dataPay.finalText + '</h2>');
            jQuery('#estimation_popup #mainPanel #lfb_summary').fadeOut();
            setTimeout(function () {
                jQuery('#estimation_popup #mainPanel #lfb_payFormFinalTxt').fadeIn();
                setTimeout(function () {
                    if (lfb_dataPay.finalUrl != "" && lfb_dataPay.finalUrl != "#" && lfb_dataPay.finalUrl != " ") {
                        document.location.href = lfb_dataPay.finalUrl;
                    }
                }, lfb_dataPay.redirectionDelay * 1000);
            }, 300);
        }
    });

}