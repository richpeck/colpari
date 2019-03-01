    jQuery(document).ready(function( $ ){

        /**
         * Check if is mobile device
         */
        function isMobile() {
            return window.matchMedia('only screen and (max-width: 760px)').matches;
        }

        /**
         * Load interface
         */
        function loadInterface() {

            var view, js_file;
            
            if ( isMobile() ) {

                view    = 'mobile/interface';
                js_file = 'mobile';

                // show loader on mobile
                $('#as-fa-container').html('<img class="as-fa-loader-icon" src="' + ASFA.plugin_url + 'assets/images/loader.svg">');

            } else {
                view    = 'interface';
                js_file = 'desktop';
            }

            $.post(ASFA.ajax_url, { 
                action: 'load_view', 
                view: view, 
                nonce: ASFA.nonce 
            }).done(function( data ) {

                $('.as-fa-loader-icon').remove();

                // insert interface
                $('#as-fa-container').html(data);

                // load script
                $.getScript( ASFA.plugin_url + 'assets/js/' + js_file + '.js' )
                .fail(function( jqxhr, settings, exception ) {
                    console.log( jqxhr );
                });



            }).fail(function( data ){
                console.log(data.responseText);
            });



        }

        if ( $('#as-fa-container').length > 0 ) {

            var width = $(window).width();

            // Add on resize event
            $( window ).resize(function() {

                // Check if width is changed
                if( $(this).width() == width ) {
                    return false;
                }

                clearTimeout(window.resizeingFinished);
                
                $('#as-fa-container').append('<img class="as-fa-loader-icon" src="' + ASFA.plugin_url + 'assets/images/loader.svg">');

                window.resizeingFinished = setTimeout(function(){

                    $('.as-fa-loader-icon').remove();

                    var url = window.location.href.replace('#', '');

                    if ( isMobile() ) {

                        if ( url.indexOf( 'fa-mobile=1' ) === -1 ) {

                            var prefix = ( url.indexOf( '?' )  > 0 ) ? '&' : '?';

                            window.location.replace( url + prefix + 'fa-mobile=1' );

                        } 

                    } else {

                        // remove parameter
                        url = url.replace('&fa-mobile=1', '');
                        url = url.replace('?fa-mobile=1', '');

                        window.location.replace( url );

                    }

                }, 250);

            });


        }

        /**
         * Init
         */

         if( ! $('#wpas-login-form').length ) {
            if ( $('#as-fa-container').length > 0 ) {
                loadInterface();
            }
         }


        // Login form
        $('#wpas-login-form').on('submit', function(e) {

            e.preventDefault();

            var that = this;
            var login_status = $('#wpas-login-status');
            var login_submit = $('#wpas-login-submit');

            login_submit.attr('disabled', true);

            $('input:not([type="submit"])', that).removeClass('error');

            login_status.show().html('<img src="' + ASFA.admin_url + 'images/loading.gif">');

            $.post( ASFA.ajax_url, {
                action : 'wpas_fa_ajax_login',
                data: $(this).serialize(),
                nonce: ASFA.nonce,
                dataType: 'json',
            }).done(function( data ) {

                login_submit.attr('disabled', false);

            
                if ( data.status === 1 ) {

                    login_status.html( '<span class="wpas_success_msg">' + data.message + '</span>' );

                    // scrol page to top
                    $(window).scrollTop(0);

                    // if user is logged in, reload the page
                    window.location.reload();
      

                } else {

                    $('input:not([type="submit"])', that).addClass('error');
                    login_status.html( '<span class="wpas_error_msg">' + data.message + '</span>' );
                }

            }).fail(function( data ){
                console.log(data.responseText);
            });


        });

        // Logout action
        $(document).on('click', '#wpas-fa-logout', function(e){

            e.preventDefault();

            if ( $(this).data('mobile') ) {
                $('#as-fa-tab-mobile-content').html('<img class="as-fa-loader-icon" src="' + ASFA.plugin_url + 'assets/images/loader.svg">');
                $('.as-fa-mobile-menu-options').slideUp(100);
            }

            $.post( ASFA.ajax_url, {
                action : 'wpas_fa_ajax_logout',
                nonce: ASFA.nonce,
            }).done(function( data ) {
                window.location.reload();
            }).fail(function( data ){
                console.log(data.responseText);
            });

        });

        
        // remove error class from login form input field
        $('#wpas-login-form input:not([type="submit"])').on('keyup', function(e){

            $(this).removeClass('error');

        });

    });
