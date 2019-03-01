jQuery(document).ready(function($){
    "use strict";

    /**
     * Process Compliance Type
     *
     * @param $val
     */
    var process_compliance_type = function( $val ) {
        if( $val == 'opt-in' ) {
            $('#accept-button-wrapper').show();
            $('#deny-button-wrapper').hide();
        } else if( $val == 'opt-out' ) {
            $('#accept-button-wrapper').hide();
            $('#deny-button-wrapper').show();
        } else {
            $('#accept-button-wrapper').hide();
            $('#deny-button-wrapper').hide();
        }
    };

    process_compliance_type( $('#compliance-type').val() );

    var process_disable_analytics = function() {

        var $ids = '#tab2 .gag-settings-item:not(#disable-analytics-wrapper), .gag-analytics-mode';

        if( $('#disable-analytics-integration').is(':checked') ) {
            $($ids).fadeTo(500, .4);
        } else {
            $($ids).fadeTo(500, 1);
        }
    };

    process_disable_analytics();

    var process_disable_cookienotice = function() {

        var $ids = '#tab3 .gag-settings-item:not(#disable-cookie-notice-wrapper), #other-tracking-compliance-wrapper';

        if( $('#disable-cookie-notice').is(':checked') ) {
            $($ids).fadeTo(500, .4);
        } else {
            $($ids).fadeTo(500, 1);
        }
    };

    process_disable_cookienotice();

    /* **************************************************************************************************************** */

    $('#google-analytics-germanized-form').on('submit', function(e){
        e.preventDefault();

        var $formAction = $(this).attr('action');
        var $formData = $(this).serialize();

        $('#google-analytics-germanized-form button').prop('disabled', true).css('opacity', .5);

        $.ajax( {
            url: gagApiSettings.save_settings,

            method: 'POST',

            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', gagApiSettings.nonce );
            },

            data: $formData

        } ).done( function ( $response ) {

            if( $response.status ) {

                $('#google-analytics-germanized-form button').addClass('sucess');

                window.setTimeout(function(){
                    $('#google-analytics-germanized-form button').removeClass('sucess');
                }, 5000);

            } else {
                if( typeof $response.message !== 'undefined' ) {
                    alert( $response.message );
                } else {
                    alert( $response );
                }
            }

        } ).fail(function ($data) {

            console.log( $data );

            if( $data.responseJSON ) {
                alert($data.responseJSON.code+': '+$data.responseJSON.message);
            } else {
                alert('Save changes failed. REST API not available!');
            }

        }).always(function (){
            $('#google-analytics-germanized-form button').prop('disabled', false).css('opacity', 1);
        });
    });

    $('.gag-settings-buttons a').on('click', function(e){
        e.preventDefault();

        var $href = $(this).attr('href');

        $('.gag-settings-buttons a').removeClass('active');
        $(this).addClass('active');

        $('.gag-settings-wrapper-outer .tab-item').not($href).slideUp('fast', function(){
            $($href).slideDown('fast');
        });
    });

    $('#disable-analytics-integration').on('change', function(e){
        process_disable_analytics();
    });

    $('#disable-cookie-notice').on('change', function(e){
        process_disable_cookienotice();
    });

    $('#compliance-type').on('change', function(e){
        var $val = $(this).val();
        process_compliance_type( $val );
    });

    $('.ga-cookie-notice-layout').on('click', function(e){
        $('.ga-cookie-notice-layout').removeClass('active'),
            $(this).addClass('active');

        var $banner_background = $(this).attr('data-banner-background');
        var $banner_text = $(this).attr('data-banner-text');
        var $button_background = $(this).attr('data-button-background');
        var $button_text = $(this).attr('data-button-text');

        $('#cc-banner-background-color').val( $banner_background );
        $('#cc-banner-text-color').val( $banner_text );
        $('#cc-button-background-color').val( $button_background );
        $('#cc-button-text-color').val( $button_text );

    });

    $('#enable-policy-link').on('click', function(e){
        $('#policy-link-area').toggle();
    });
});