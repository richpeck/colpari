
(function ($) {
	"use strict";
        
	$(function () {
                
                // Open add info window
                
                  
                  
                $('body').delegate( '.wpas_win_link', 'click', function( e) {
                        e.preventDefault();
                          
                        var type = $(this).data('win_type') || 'inline';
                        var src = $(this).data('win_src') || $(this).attr('href');
                        
                        if( type && src ) {
                                
                                var settings = {
                                        items : { type : type, src : src },
                                        closeOnBgClick : false,
                                        callbacks: {
                                                parseAjax: function(mfpResponse) {
                                                  mfpResponse.data = $(mfpResponse.data).removeClass('mfp-hide')
                                                }
                                        }
                                };
                                
                                if( 'ajax' === type ) {
                                        
                                        settings.items.src = ajaxurl;
                                        
                                        var ajax_data = $(this).data('ajax_params');
                                        
                                        settings.ajax = {};
                                        settings.ajax.settings = {
                                                method : 'POST',
                                                data : ajax_data
                                        }
                                }
                                
                                $.magnificPopup.open( settings );
                        }
                  });
                  
                  // Fix select2 with magnificPopup
                  $.magnificPopup.instance._onFocusIn = function(e) {
                        // Do nothing if target element is select2 input
                        if( $(e.target).hasClass('select2-search__field') ) {
                          return true;
                        } 
                        // Else call parent method
                        $.magnificPopup.proto._onFocusIn.call(this,e);
                };
                
                
                $('body').delegate('.wpas_win_close_btn', 'click', function() {
                        $.magnificPopup.close();
                });


                // Send add/edit info request to server
                $('body').delegate('.wpas_cp_window_wrapper .submit input[type=button]', 'click', function() {
                        
                        var btn = $(this);
                        
                        var win = btn.closest('.wpas_cp_window_wrapper'); 
                        var data = btn.closest('form').serializeArray();
                        
                        
                        //var form = $(btn).closest('.wpas_cp_window_wrapper');
                        //var sec = form.data('section');

                        var loader = $('<div class="spinner"></div>');
                        
                        
                        loader.insertAfter( btn.closest('.wpas_win_footer').children('p').last() );
                        btn.prop('disabled', true);

                        
                        //var form_action = form.find('.wpas_cp_form_action').val();
                        //data.push({name:'action', value : form_action});
                        //data.push({name:'duid', value : $('#wpas_cp_ui_section_'+sec).data('duid')});

                        $.post(ajaxurl, data, function (response) {

                                if( true == response.success ) {
                                        
                                        //win.find('.wpas_cp_msg').removeClass('error').addClass('updated').show().html('<p>'+response.data.msg+'</p>');
                                        
                                        if( response.data.item ) {
                                                form.find('.wpas_cp_msg').removeClass('error').addClass('updated').html('<p>'+response.data.msg+'</p>');
                                                var item = $(response.data.item);
                                                var items_container = $('#wpas_cp_'+sec+'_items .wpas_cp_ui_items');
                                                
                                                items_container.append(item);
                                                it_clear_fields();
                                        } else if( response.data.update_item ) {
                                                var item = $(response.data.update_item);
                                                
                                                $( response.data.selector ).replaceWith(item);
                                                $( response.data.info_selector ).removeClass('error').addClass('updated').show().html('<p>'+response.data.msg+'</p>');

                                                //item.find('.wpas_cp_ui_item_msg .msg').html(response.data.msg);
                                                //item.find('.wpas_cp_ui_item_msg').addClass('updated').removeClass('error').show();
                                                //$('#wpas_cp_'+sec+'_items .wpas_cp_ui_items .wpas_cp_ui_item[data-item_id='+response.data.item_id+']').replaceWith(item);
                                                
                                                
                                                $.magnificPopup.close();
                                        } 
//                                        else if( response.data.location ) {
//                                                window.location = response.data.location;
//                                                return;
//                                        }
//
//                                        it_empty_section_handler(sec)
//
//                                        item.trigger( form_action + '_response', [sec, data, response] );

                                } else {
                                        win.find('.wpas_cp_msg').addClass('error').removeClass('updated').show().html('<p>'+response.data.msg+'</p>');
                                }

                                loader.remove();
                                btn.prop('disabled', false);

                        });

                });
                
                
                
                // Send add/edit info request to server
                
                $('#wpas_cp_add_company_form_wrapper .submit input[type=button]').click(function() {
                        
                        console.log('asd');
                        
                        var btn = $(this);
                        
                        var form = btn.closest('form'); 
                        var data = form.serializeArray();
                        
                        var loader = $('<div class="spinner"></div>');
                        
                        loader.insertAfter( btn );
                        btn.prop('disabled', true );

                        $.post(ajaxurl, data, function (response) {

                                if( true == response.success ) {
                                        if( response.data.location ) {
                                                window.location = response.data.location;
                                        }
                                } else {
                                        form.find('.wpas_cp_msg').addClass('error').removeClass('updated').show().html('<p>'+response.data.msg+'</p>');
                                }

                                loader.remove();
                                btn.prop('disabled', false);

                        });

                });
                
                


                // Send delete info request to server
                $('body').delegate('.wpas_cp_ui_item .wpas_cp_ui_item_action.wpas_cp_ui_item_action_delete', 'click', function(e) {
                        e.preventDefault();
                        //var section = $(this).closest('.wpas_cp_ui_wrapper').data('section');
                        var item = $(this).closest('.wpas_cp_ui_item');
                        var btn = $(this);
                        var msg = btn.data('confirm');

                        if(confirm(msg)) {
                                btn.hide();
                                var loader = $('<div class="spinner"></div>');
                                loader.css({visibility: 'visible'})
                                $(this).closest('li').append(loader);

                                //var data = new Array();
                                //var nonce = btn.closest('.wpas_cp_ui_items').find('.delete-nonce');
                                //var action = $(this).data('action');

                                //data.push({name:'action', value : action });
                                //data.push({name:'id', value : item_id});
                                //data.push({name:$(nonce).data('name'), value : $(nonce).val()});
                                //data.push({name:'duid', value : $(this).closest('.wpas_cp_ui_wrapper').data('duid')});

                                var data = btn.data('ajax_params');

                                $.post(ajaxurl, data, function (response) {

                                        if( true == response.success ) {
                                                $(response.data.info_selector).addClass('updated').removeClass('error').show().html('<p>'+response.data.msg+'</p>');
                                                item.remove();
                                        } else {
                                                $(response.data.info_selector).addClass('error').removeClass('updated').show().html('<p>'+response.data.msg+'</p>');
                                                loader.remove();
                                                btn.show();
                                        }
                                });
                        }

                });
                
                
                $('body').delegate('.wpas_cp_ui_item_action_toggle_support_users', 'click', function() {
                        $(this).closest('.wpas_cp_group_support_users__company').find('.wpas_cp_list_subtable_support_user').slideToggle();
                })
                
                
                
                        
                
                
	});
        
}(jQuery));