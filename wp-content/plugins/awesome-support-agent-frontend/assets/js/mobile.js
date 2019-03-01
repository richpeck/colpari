(function($){

    var header_menu = $('.as-fa-mobile-header');

    function moveMenuOnTop() {
        
        //landscape
        if(window.innerWidth > window.innerHeight){

            header_menu.css({top:42});

        } else {

            // portrait
            var scroll = $(window).scrollTop();

            if (scroll == 0) {
                header_menu.css({top:42});
            } else if ( scroll > 0 && scroll < 42 ) {
                header_menu.css({top: 42 - scroll});
            } else {
                header_menu.css({top:0});
            }

        }

    }

    /**
     * Toggle options menu
     */
    function toggleOptionsMenu() {
        $('.as-fa-mobile-menu-options').slideToggle(200);

    }

    if ( $('#wpadminbar').length ) {
        moveMenuOnTop();
        $(window).scroll(moveMenuOnTop);
        $('#as-fa-tab-mobile-content').css({'padding-top': 90});
    } else {
        header_menu.css({top:0});
    }

    /**
     * Toggle mobile menu options
     */
    $(document).on('click', '.as-fa-toggle-mobile-menu', function(e){

        e.preventDefault();
        toggleOptionsMenu();

    });

    // windows
    $(document).on('click', '[data-view]', function (e) {

        e.preventDefault();

        var id      = $(this).data('id');
        var view    = $(this).data('view');
        var title   = $(this).data('title');
        var content = $('[data-window-content="' + id + '"]');

        toggleOptionsMenu();

        // set title
        $('#as-fa-mobile-menu-center').text( title );
        // hide others
        $('.as-fa-mobile-content-page').removeClass('active');
        // show menu icon
        $('.as-fa-toggle-mobile-menu').show();
        // hide back icon
        $('.as-fa-mobile-back').attr('data-back', id).hide();
            
        // if content is already loaded
        if ( content.length > 0 ) {

            content.addClass('active');

        } else {
    
            var html = '<div class="as-fa-mobile-content-page active" data-window-content="' + id + '"><img class="as-fa-loader-icon" src="' + ASFA.plugin_url + 'assets/images/loader.svg"></div>';
    
            // append content with the loader first
            $('#as-fa-tab-mobile-content').append( html );
    
            // get closed tickets
            $.post(ASFA.ajax_url, { 
                action: 'load_view', 
                view: view, 
                nonce: ASFA.nonce 
            }).done(function( data ) {
    
                // load content
                $('[data-window-content="' + id + '"]').html( data );
    
            }).fail(function( data ){
                console.log(data.responseText);
            });

        }
  


    });
        


    /**
     * Touch, Long press on ticket
     */

    var touchStart, touchMoved, timer = 0;

    $(document).on('touchstart', '.as-fa-mobile-ticket-card', function(e){

        e.cancelBubble = true;

        /**
         * Show ticket options on long press
         */

        var that = this;

        touchMoved = false;
        touchStart = e.timeStamp;

        timer = setTimeout(function(){

            var id = $(that).data('id');
  
            $(that).toggleClass('as-fa-selected');

            $('.as-fa-mobile-ticket-card').not('[data-id="' + id + '"]').removeClass('as-fa-selected');
            $('.as-fa-mobile-ticket-custom-fields').not('[data-fields-id="' + id + '"]').slideUp();

            $('[data-fields-id="' + id + '"]').slideToggle(); 


        }, 1000);

    }).on('touchmove', '.as-fa-mobile-ticket-card', function(e){

        e.cancelBubble = true;

        touchMoved = true;

    }).on('touchend', '.as-fa-mobile-ticket-card', function(e){

        e.cancelBubble = true;

        clearTimeout( timer );

        /**
         * View ticket on short press
         */
        if ( ! touchMoved && ( e.timeStamp - touchStart ) < 1000 ) {

            var id      = $(this).data('id');
            var title   = $(this).data('title');
            var content = $('[data-window-content="' + id + '"]');

            // hide menu
            $('.as-fa-mobile-menu-options').slideUp(200);
            // hide menu icons
            $('.as-fa-toggle-mobile-menu').hide();
            // show back icon
            $('.as-fa-mobile-back').show();
            // show options icon
            $('.as-fa-mobile-ticket-options').attr('data-id', id).show();
            // hide others
            $('.as-fa-mobile-content-page').removeClass('active');
            // set title
            $('#as-fa-mobile-menu-center').text( '#' + id );

            // if content is already loaded
            if ( content.length > 0 ) {

                content.addClass('active');
    
            } else {

                // html
                var html = '<div class="as-fa-mobile-content-page active" data-window-content="' + id + '"><img class="as-fa-loader-icon" src="' + ASFA.plugin_url + 'assets/images/loader.svg"></div>';
        
                // show loader
                $('#as-fa-tab-mobile-content').append( html );

        
                // get closed tickets
                $.post(ASFA.ajax_url, { 
                    action: 'view_ticket_mobile', 
                    id: id,
                    nonce: ASFA.nonce,
                    dataType: 'json'
                }).done(function( data) {
            
                    $('[data-window-content="' + id + '"]').html(data.content);
        
                }).fail(function( data ){
                    console.log(data.responseText);
                });


            }

        }


    });


    /**
     * Back button
     */
    $(document).on('click', '.as-fa-mobile-back', function(e){

        e.preventDefault();
        e.stopPropagation();

        var back = $(this).attr('data-back');
        var default_tab = $('[data-id="' + back + '"]');
        var title = default_tab.data('title');

        // show menu icons
        $('.as-fa-toggle-mobile-menu').show();
        // hide back icon
        $(this).hide();
        // hide options icon
        $('.as-fa-mobile-ticket-options').hide();
        // set title
        $('#as-fa-mobile-menu-center').text( title );
        // hide content
        $('.as-fa-mobile-content-page').removeClass('active');
        // show default content
        $('[data-window-content="' + back + '"]').addClass('active');

    });

    /**
     * Show Ticket options
     */
     $(document).on('click', '.as-fa-mobile-ticket-options', function(e){

        e.preventDefault();

        var id = $(this).attr('data-id');

         // hide wp admin bar
        $('#wpadminbar').addClass('as-fa-mobile-hide');
        // hide tickets header
        $('.as-fa-mobile-header').addClass('as-fa-mobile-hide');
        // show ticket options
        $('.as-fa-mobile-ticket-options-content[data-options-id="' + id + '"]').show();
     });


    /**
     * Ticket options close 
     */
     $(document).on('click', '.as-fa-mobile-close-options', function(e){

        e.preventDefault();
        
        // empty reply content
        $('#as-fa-tab-mobile-reply-content').html('').hide();
        // show wp admin bar
        $('#wpadminbar').removeClass('as-fa-mobile-hide');
        // show tickets header
        $('.as-fa-mobile-header').removeClass('as-fa-mobile-hide');

        // hide options
        $(this).parent('.as-fa-mobile-ticket-options-content').hide();

     });

     /**
      * Change ticket status 
      */
    $(document).on('change', '.as-fa-mobile-change-status', function(e){

        e.preventDefault();

        var id = $(this).data('id');
        var status = $(this).val();
        var container =  $('.as-fa-mobile-ticket-options-content[data-options-id="' + id + '"]');

        // show loader
        container.append('<img class="as-fa-loader-icon" src="' + ASFA.plugin_url + 'assets/images/loader.svg">');

        // get ticket data
        $.post(ASFA.ajax_url, { 
            action: 'update_ticket_status_mobile', 
            id: id, 
            status: status,
            nonce: ASFA.nonce,
            dataType: 'json'
        }).done(function( data ) {

            $('[data-window-content="' + id + '"]').html(data.content);
            $('.as-fa-mobile-ticket-status[data-id="' + id + '"]').html( data.label );

            $('.as-fa-loader-icon').remove();

            // show wp admin bar
            $('#wpadminbar').removeClass('as-fa-mobile-hide');
            // show tickets header
            $('.as-fa-mobile-header').removeClass('as-fa-mobile-hide');

            $(this).parent('.as-fa-mobile-ticket-options-content').hide();

        }).fail(function( data ){
            
            console.log(data.responseText);

        });

    });

    
    /**
      * Re-open / Close ticket
      */
     $(document).on('click', '.ticket-action-mobile', function(e){

        e.preventDefault();
        e.stopPropagation();

        var id         = $(this).data('id');
        var action     = $(this).data('action'); 
        var container  = $('.as-fa-mobile-ticket-options-content[data-options-id="' + id + '"]');

        // show loader
        container.append('<img class="as-fa-loader-icon" src="' + ASFA.plugin_url + 'assets/images/loader.svg">');

        // get ticket data
        $.post(ASFA.ajax_url, { 
            action: action + '_mobile', 
            id: id, 
            nonce: ASFA.nonce,
            dataType: 'json'
        }).done(function( data ) {

            var list = ( action == 'open_ticket') ? 'open_tickets' : 'closed_tickets';

            // move ticket accros the lists
            if ( data.status == 1 ) {

                $('.as-fa-mobile-ticket-card[data-id="'+ id +'"]').remove();

                var content = $('[data-window-content="' + list + '"]');

                if ( content.children().length > 0 ) {
                    content.prepend( data.item );
                } else {
                    content.html( data.item );
                }

            }
         
            $('[data-window-content="' + id + '"]').html(data.content);

            $('.as-fa-loader-icon').remove();

            // show wp admin bar
            $('#wpadminbar').removeClass('as-fa-mobile-hide');
            // show tickets header
            $('.as-fa-mobile-header').removeClass('as-fa-mobile-hide');

            $(this).parent('.as-fa-mobile-ticket-options-content').hide();

        }).fail(function( data ){
            
            console.log(data.responseText);

        });

    });


    /**
     * Ticket Quick Actions Close/Re-open ticket
     */
    $(document).on('touchstart', '.as-fa-ticket-quick-action-mobile', function(e){

        e.preventDefault();
        e.stopPropagation();

        var id     = $(this).data('id');
        var action = $(this).data('action');

        $(this).parents('.as-fa-mobile-content-page').append('<img class="as-fa-loader-icon" src="' + ASFA.plugin_url + 'assets/images/loader.svg">');

        // run action
        $.post(ASFA.ajax_url, { 
            action: action + '_mobile', 
            id: id, 
            nonce: ASFA.nonce,
            dataType: 'json'
        }).done(function( data ) {

            
            $('.as-fa-loader-icon').remove();

            var list = ( action == 'open_ticket') ? 'open_tickets' : 'closed_tickets';

            // move ticket accros the list
            if ( data.status == 1 ) {

                $('.as-fa-mobile-ticket-card[data-id="'+ id +'"]').remove();

                var content = $('[data-window-content="' + list + '"]');

                if ( content.children().length > 0 ) {
                    content.prepend( data.item );
                } else {
                    content.html( data.item );
                }

            }


        }).fail(function( data ){
            
            console.log(data.responseText);

        });
        


    });

        
    /**
      * Write a reply
      */
     $(document).on('touchstart', '.as-fa-mobile-write-reply', function(e){

        e.preventDefault();
        e.stopPropagation();

        var id = $(this).data('id');

        var loader = '<img class="as-fa-loader-icon" src="' + ASFA.plugin_url + 'assets/images/loader.svg">';
    
        if ( $(this).data('options') == 1 ) {
            $('.as-fa-mobile-ticket-options-content[data-options-id="' + id + '"]').append( loader );
        } else {
            $(this).parents('.as-fa-mobile-content-page').append( loader );
        }


        // run action
        $.post(ASFA.ajax_url, { 
            action: 'load_view', 
            id: id,
            view: 'mobile/ticket_reply',  
            nonce: ASFA.nonce,
            dataType: 'json'
        }).done(function( data ) {

            
            $('.as-fa-loader-icon').remove();
            // hide wp admin bar
            $('#wpadminbar').addClass('as-fa-mobile-hide');
            // hide tickets header
            $('.as-fa-mobile-header').addClass('as-fa-mobile-hide');
            // hide options menu if opened
            $('.as-fa-mobile-ticket-options-content').hide();
            // show reply window
            $('#as-fa-tab-mobile-reply-content').html(data).show();



        }).fail(function( data ){
            
            console.log(data.responseText);

        });
  

    });


    /**
     * Reply actions
     */
    $(document).on('click', '.as-fa-mobile-reply-action', function(e){

        e.preventDefault();
        e.stopPropagation();

        var id        = $(this).data('id');
        var action    = $(this).data('action');
        var form      = $(this).parent('.wpas-fa-reply-form-mobile')[0];
        var form_data = new FormData(form);
        var reply     = $('.as-fa-reply-editor-mobile-' + id).val(); 

        form_data.append('id', id);
        form_data.append('action', action + '_mobile');
        form_data.append('reply', reply);
        form_data.append('nonce', ASFA.nonce);

        if ( reply == '' ) {

            alert( ASFA.reply_empty );

            $('.as-fa-reply-editor-mobile-' + id).focus();

            return false;
        }
     
        $('.as-fa-mobile-ticket-reply-content').append('<img class="as-fa-loader-icon" src="' + ASFA.plugin_url + 'assets/images/loader.svg">');

        // get ticket data
        $.ajax({ 
            method: 'POST',
            url: ASFA.ajax_url,
            data : form_data,
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false
        }).done(function( data ) {

            $('.as-fa-loader-icon').remove();

            if ( data.status == 1 ) {

                // insert ticket
                $('[data-window-content="' + id + '"]').html( data.content );

                // remove ticket from open ticket list
                if( action == 'ticket_reply_close' ) {

                    // remove item from list
                    $('.as-fa-mobile-ticket-card[data-id="'+ id +'"]').remove();

                    var content = $('[data-window-content="closed_tickets"]');
    
                    if ( content.children().length > 0 ) {
                        content.prepend( data.item );
                    } else {
                        content.html( data.item );
                    }

                } else {

                    $('.as-fa-mobile-ticket-card[data-id="'+ id +'"]').replaceWith( data.item );

                }

                
                // show wp admin bar
                $('#wpadminbar').removeClass('as-fa-mobile-hide');
                // show tickets header
                $('.as-fa-mobile-header').removeClass('as-fa-mobile-hide');

                $('#as-fa-tab-mobile-reply-content').html('').hide();



            } else {

                alert( data.message );

            }


        }).fail(function( data ){
            
            console.log(data.responseText);

        });


    });

    

})( jQuery );
