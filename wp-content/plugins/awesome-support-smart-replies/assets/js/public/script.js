
var sc_actions = [];

(function($) {
        
        $(function() {
        
                if( $('#wpas_smart_chat').length ) {
        
                        // Listen for Enter key pressed in smart chat input field
                        $('#wpas_smart_chat .sc_footer .message_input').keyup( function(e) {

                                var code = e.which;

                                if( code == 13 ) {
                                        // Trigger message send button click
                                        $('.sc_send_btn').trigger('click');
                                }
                        });


                        // Send message button click handler
                        $('.sc_send_btn').click(function() {
                                var text = $('.sc_textbox input').val();

                                // stop further processing if user message is empty
                                if( !text ) {
                                        return;
                                }

                                // Add customer message in messages list
                                sc_add_message( text, 'customer' );

                                var data = {
                                        action : 'wpas_sc_get_reply',
                                        message : text,
                                        security : $('.sc_textbox .wpas_sc_nonce').val(),
                                };

                                // Send request to server to get answer of customer's question
                                $.post( wp.ajax.settings.url, data, function (response) {

                                        if(  response.success ) {

                                                if( response.data.text ) {
                                                        // Add bot message in messages list
                                                        sc_add_message( response.data.text, 'bot' );
                                                } else if( response.data.action ) {
                                                        // Perform any action suggested in server response
                                                        if( typeof sc_actions[ response.data.action ] === 'function') {
                                                                sc_actions[ response.data.action ]( response.data );
                                                        }

                                                }
                                        }
                                });
                        });


                        // Show smart chat window once bubble is clicked
                        $('#wpas_smart_chat .wpas_smart_chat_button').click(function(e) {
                                e.preventDefault();
                                $(this).hide();
                                $(this).closest('#wpas_smart_chat').find('.smart_chat_window').show();
                                sc_resize_smart_chat();
                        });

                        // Hide smart chat window once 'X' is clicked
                        $('#wpas_smart_chat .ti-close').click(function(e) {
                                e.preventDefault();

                                $(this).closest('#wpas_smart_chat').find('.wpas_smart_chat_button').show();
                                $(this).closest('#wpas_smart_chat').find('.smart_chat_window').hide();
                        });

                        // Add welcome message in smart chat window
                        sc_add_message( wpas_sc.chat_welcome_message, 'bot' );

                        // Submit new ticket handler from smart chat
                        $('body').delegate('#wpas-sc-new-ticket', 'submit', function(e) {
                                e.preventDefault();


                                var form = $(this);
                                var data = form.serializeArray();
                                var btn = form.find('[name=wpas-submit]');

                                var loader = $('<div class="spinner"></div>');
                                loader.insertAfter( btn );
                                btn.prop('disabled', true);


                                if( wpas_sc.open_ticket_include_chat_content ) {
                                        data.push( {name : 'chat_content', value : $('#wpas_smart_chat .smart_chat_window ul.chat').html() } )
                                }

                                $.post( wp.ajax.settings.url, data, function ( response ) {

                                        if( true == response.success ) {
                                                // Add success message in chat window and remove open ticket window
                                                sc_add_message( response.data.message, 'bot' );
                                                tb_remove();
                                        } else {
                                                form.find('.notify').addClass('wpas-alert wpas-alert-danger').html( response.data.message ).show();
                                        }

                                        loader.remove();
                                        btn.prop('disabled', false);
                                });

                                return false;
                        });



                        $(window).resize( function() {
                                sc_resize_smart_chat();
                        });
                }
        
        });
        
        
        // Resize smart chat window based on screen size
        function sc_resize_smart_chat() {
                var win_width = $(this).width();
                        
                if( win_width < 520 ) {
                        $('.smart_chat_window').css({width: ($(this).width() - 30) +'px'});
                } else {
                        $('.smart_chat_window').css({width : '380px'});
                }
        }
        
        // Load open ticket window
        function init_open_ticket( data ) {
                tb_show( 'Open Ticket' , data.open_ticket_url );
        }
        
        
        sc_actions = {
                init_open_ticket : init_open_ticket
                
        };
        
        
        // Add message in smart chat window
        function sc_add_message( msg, sender_type ) {
                
                if( msg ) {
                        
                        var from = 'customer' === sender_type ? 'You' : wpas_sc.chat_box_title;
                        
                        var template = wp.template( 'wpas-sc-message-item' );
                        var style = 'customer' === sender_type ? wpas_sc.user_font_and_size : wpas_sc.answer_font_and_size;
                        
                        var el = template( { 
                                message: msg, 
                                sender_type : sender_type,  
                                from: from, 
                                time : new Date().toLocaleTimeString()
                        } );
                        
                        $('.sc_messages>ul').append( $(el).css(style) );
                        
                        $('.sc_messages').scrollTop($('.sc_messages')[0].scrollHeight);
                        
                        $('#wpas_smart_chat .sc_footer .message_input').val('').focus();

                }
        }
        
        
})(jQuery);


