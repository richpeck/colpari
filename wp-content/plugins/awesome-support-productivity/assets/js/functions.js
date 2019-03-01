
var grecaptcha_validated = false;
var grecaptcha_active    = false;
var grecaptcha_loaded    = false;



(function($) {
        
        // This will run once registration form submitted and captcha verified
        function onRegSubmit(code) {
	
                grecaptcha_validated = true;	
                jQuery('#wpas_form_registration .wpas-btn[type=submit]').prop('disabled', false ).trigger('click');

        }

        // Check if captcha is loaded
        function check_recaptcha_loaded() {
                if( 0 === $('.g-recaptcha #g-recaptcha-response').length ) {
                        setTimeout( check_recaptcha_loaded , 500 );
                } else {
                        $('.g-recaptcha iframe').get(0).onload = function() {
                                grecaptcha_loaded = true;
                                $('.g-recaptcha').trigger('loaded');
                        }
                }
        }

        // Fix some css once captcha loaded with error message
        function onCaptchaLoaded() {
                if($('.g-recaptcha .grecaptcha-user-facing-error').length > 0) {
                        $('.g-recaptcha .grecaptcha-user-facing-error').remove();
                        $('.g-recaptcha .grecaptcha-badge').css('width', '300px');
                        $('.g-recaptcha iframe').css('width', '306px');
                }
        }

        // Init captcha verification once registration form submitted
        function onRegFormSubmit(e) {
                if( !grecaptcha_validated ) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        grecaptcha.execute();
                        return false;
                }

                return true;
        }
        
        
        $(function() {
                // Check if captcha is active
                grecaptcha_active = $('.g-recaptcha').length > 0 ? true : false
                
                // check if captcha is loaded
                if( grecaptcha_active ) {
                        check_recaptcha_loaded()
                }
                
                $('.g-recaptcha').on('loaded', onCaptchaLoaded );
                $('#wpas_form_registration').on('submit', onRegFormSubmit );
        });
	
	
}(jQuery));