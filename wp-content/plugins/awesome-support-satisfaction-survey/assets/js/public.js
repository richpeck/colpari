/*
 *
 * @package   Awesome Support: Satisfaction Survey
 * @author    Robert W. Kramer III for Awesome Support <support@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016. Awesome Support
 *
 */

jQuery(document).ready(function ($) {
    $("input[name='wpasss_rating']").trigger('change');
});


jQuery('form#wpas_submit_survey').on('submit', function (e) {

    if (jQuery('#wpasss_rating:checked').length == 0) {
        alert("Please rate our performance on this ticket before submitting this survey.");
        return false;
    }

    // If unsatisfied reasons dropdown is displayed force user to choose
    if (jQuery('.wpas_unsatisfied_reasons').css('display') != 'none') {
        if (jQuery('#wpas_unsatisfied_reasons').val() == '') {
            alert("Please select the main reason you are unsatisfied.");
            return false;
        }
    }

    return true;

});


/* Hide/show Unsatisfied Reasons dropdown based on rating threshold. */
jQuery("input[name='wpasss_rating']").change(function () {

    var $threshold = jQuery('input[name="wpasss_rating"]').data('threshold');
    var $value = jQuery('input[name="wpasss_rating"]:checked').val();

    if ($value <= $threshold) {
        jQuery('.wpas_unsatisfied_reasons').css('display', 'block');
    }
    else {
        jQuery('#wpas_unsatisfied_reasons').val('').prop('selected', true);
        jQuery('.wpas_unsatisfied_reasons').css('display', 'none');
    }

});
