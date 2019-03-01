jQuery(document).ready(function () {
    jQuery('.open-estimation-form').click(wpe_popup_estimation);
});

var wpe_initial_overflowBody = "auto";
var wpe_initial_overflowHtml = "auto";
function wpe_popup_estimation() {
    var form_id = 0;
    var cssClass = jQuery(this).attr('class');
    cssClass = cssClass.split(' ');
    jQuery.each(cssClass, function (c) {
        c = cssClass[c];
        if (c.indexOf('form-') > -1) {
            form_id = c.substr(c.indexOf('-') + 1, c.length);
        }
    });
    wpe_initial_overflowBody = jQuery('body').css('overflow-y');
    wpe_initial_overflowHtml = jQuery('html').css('overflow-y');
    jQuery('body,html').css('overflow-y','hidden');
    jQuery('#estimation_popup[data-form="' + form_id + '"]').show().animate({
        left: 0,
        top: 0,
        width: '100%',
        height: '100%',
        opacity: 1
    }, 500, function () {
        jQuery('#estimation_popup[data-form="' + form_id + '"] #wpe_close_btn').delay(500).fadeIn(500);
        jQuery('#estimation_popup[data-form="' + form_id + '"] #wpe_close_btn').click(function () {
            wpe_close_popup_estimation(form_id);
        });
        setTimeout(lfb_onResize,250);
    });
    
    
}

function wpe_close_popup_estimation(form_id) {
    jQuery('#estimation_popup[data-form="' + form_id + '"]').animate({
        top: '50%',
        left: '50%',
        width: '0px',
        height: '0px',
        opacity: 0
    }, 500, function () {
        jQuery('body').css('overflow-y',wpe_initial_overflowBody);
        jQuery('html').css('overflow-y',wpe_initial_overflowHtml);
        location.reload();
    });

}
