// thickbox settings
var tb_position;




(function ($) {
	"use strict";
        
	$(function () {
                
                
                /* Check if element has Attr */
                $.fn.hasAttr = function(name) {  
                        return this.attr(name) !== undefined;
                };
                
                 /**
                * Condition to prevent JavaScript error. It checks if:
                * 1) Selector exists
                * 2) jQuery Select2 is loaded
                * 3) WordPress AJAX is enabled
                */
               var selector = $('.wpas-select2.cp-select2');
               var condition = selector.length && $.fn.select2 && typeof ajaxurl !== 'undefined';
               if (condition) getSelect2Data( selector );

                /**
                 * Get select2 data via AJAX
                 * https://select2.github.io/examples.html#data-ajax
                 */
                function getSelect2Data( selector ) {
                        
                        
                        
                        selector.each(function (index, el) {
                                
                                
                                var act;
                                var type = $(el).attr('data-opt-type');
                               
                                act = $(el).data('action') ? $(el).data('action') : 'wpas_get_select2_' + type;
                                
                                $(el).select2({
                                        ajax: {
                                                url: ajaxurl,
                                                dataType: 'json',
                                                type: 'POST',
                                                delay: 250,
                                                data: function (params) {

                                                        var data = {
                                                                q: params.term,
                                                        };
                                                        
                                                        if( $(el).data('capability') ) {
                                                                data.cap = $(el).data('capability');
                                                        }

                                                        if( $(el).data('filter_req') ) {
                                                                $($(el).data('filter_req')).each(function() {
                                                                        data[$(this).attr('name')] = $(this).val()
                                                                })
                                                        }
                                                        
                                                        


                                                        return wpas_prepare_data( data, act );
                                                },
                                                processResults: function (data, params) {
                                                        return {
                                                                results: $.map(data, function (obj) {
                                                                        var id   = $(el).data('result_id') ? obj[ $(el).data('result_id') ] : obj.id;
                                                                        var text = $(el).data('result_text') ? obj[ $(el).data('result_text') ] : obj.text;
                                                                        return {
                                                                                id: id,
                                                                                text: text
                                                                        };
                                                                })
                                                        };
                                                }
                                        },
                                        minimumInputLength: 3
                                });
                               
                       });
                }
                
                
                /* Get nonce from action */
                function wpas_get_nonce( action ) {
                
                        if( $('#cp_nonce_'+action).length > 0 ) {
                                return $('#cp_nonce_'+action).val();
                        }

                        return '';
                }
                
                

                /* Prepare data for ajax request */
                function wpas_prepare_data( data, action ) {

                        if( Array.isArray( data ) ) {
                                data.push( { name : 'action', value : action } );
                        } else {
                                data.action = action;
                        }

                        var nonce = wpas_get_nonce( action );
                        if( nonce ) {
                                if( Array.isArray( data ) ) {
                                        data.push( { name : 'security', value : nonce } );
                                } else {
                                        data.security = nonce;
                                }
                        }

                        return data;
                }
                
                
                
                // Clear add item form fields after an item is added, for example : personal notes, todos, signatures etc
                function cp_clear_fields() {
                        
                        $('.wpas_cp_mfp_window_wrapper').find('input, select, textarea, checkbox, radio').each(function() {
                                var tagName = $(this).prop('tagName');
                                
                                if( $(this).data().hasOwnProperty('default') ) {
                                        var clear = $(this).data('default');
                                        if( 'INPUT' === tagName && $(this).attr('type') !== 'hidden' ) {
                                                
                                                if( 'checkbox' === $(this).attr('type') ) {
                                                        $(this).prop('checked', false);
                                                } else {
                                                        $(this).val(clear);
                                                }
                                        } else if( 'TEXTAREA' === tagName ) {
                                                $(this).val(clear);
                                        } else if( 'SELECT' === tagName ) {
                                                if( $(this).hasClass( 'wpas-select2' ) ) {
                                                        $(this).val('').trigger('change');
                                                } else {
                                                        $(this).val( $(this).find('option:first').val() );
                                                }
                                        }
                                }

                        });
                }

                /**
                 * Checks if list is empty
                 */
                function cp_empty_section_handler( section ) {
                        if( 0 === $('#wpas_cp_'+section+'_items .wpas_cp_ui_item').length ) {
                                $('#wpas_cp_'+section+'_items .wpas_cp_ui_items').hide();
                                $('#wpas_cp_'+section+'_items .no_item_msg').show();

                        } else {
                                $('#wpas_cp_'+section+'_items .wpas_cp_ui_items').show();
                                $('#wpas_cp_'+section+'_items .no_item_msg').hide();
                        }
                }

                /* Check if data is empty and show empty message */
                $('.wpas_cp_ui_wrapper').each(function() {
                        cp_empty_section_handler($(this).data('section'));
                })


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
                                                },
                                                
                                                      
                                                      ajaxContentAdded: function() {
                                                              
                                                              console.log( $( this.content ).find('.cp-select2').length );
                                                              
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
                                
                                $('.mfp-content .wpas_cp_mfp_window_wrapper .wpas_cp_msg').hide();
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
                $('body').delegate('.wpas_cp_mfp_window_wrapper .submit input[type=button]', 'click', function() {

                        var data = new Array();
                        var btn = $(this);
                        var form = $(btn).closest('.wpas_cp_mfp_window_wrapper');
                        var sec = form.data('section');

                        var loader = $('<div class="spinner"></div>');
                        loader.insertAfter( $('#TB_window .tb_footer').children('p').last() );
                        btn.prop('disabled', true);

                        if( typeof tinymce !== 'undefined' ) {
                                tinymce.triggerSave();
                        }
                        form.find('input, select, textarea').each(function() {

                                if( ($(this).hasAttr('id') || $(this).hasAttr('data-name')) && !$(this).hasClass('ed_button') ) {

                                        if( ('checkbox' == $(this).attr('type') && $(this).prop('checked')) || $(this).attr('type') != 'checkbox' ) {
                                                var name = $(this).hasAttr('data-name') ? $(this).data('name') : $(this).attr('id');
                                                var value = $(this).val();
                                                data.push({ name: name, value : value });
                                        }
                                }
                        });

                        var form_action = form.find('.wpas_cp_form_action').val();
                        data.push({name:'action', value : form_action});
                        data.push({name:'duid', value : $('#wpas_cp_ui_section_'+sec).data('duid')});

                        $.post(ajaxurl, data, function (response) {


                                if( true == response.success ) {
                                        
                                        if( 'support_users' === sec ) {
                                                
                                                if( 1 === $( response.data.item || response.data.update_item ).find('.primary').length ) {
                                                        items_container = $('#wpas_cp_'+sec+'_items .wpas_cp_ui_item .primary').removeClass('primary')
                                                }
                                        }

                                        if( response.data.item ) {
                                                
                                                form.find('.wpas_cp_msg').removeClass('error').addClass('updated').html('<p>'+response.data.msg+'</p>').show();
                                                var item = $(response.data.item);
                                                var items_container = $('#wpas_cp_'+sec+'_items .wpas_cp_ui_items');
                                                
                                                if( 'table' === items_container.data('type') ) {
                                                        items_container = items_container.find('table:first tbody');
                                                }
                                                
                                                items_container.append(item);
                                                cp_clear_fields();
                                        } else if( response.data.update_item ) {
                                                var item = $(response.data.update_item);
                                                
                                                $(response.data.msg_item)
                                                        .html(response.data.msg)
                                                        .addClass('updated')
                                                        .removeClass('error').show();
                                                
                                                $('#wpas_cp_'+sec+'_items .wpas_cp_ui_items .wpas_cp_ui_item[data-item_id='+response.data.item_id+']').replaceWith(item);
                                                $.magnificPopup.close();
                                                
                                        } else if( response.data.location ) {
                                                window.location = response.data.location;
                                                return;
                                        }

                                        cp_empty_section_handler(sec)

                                        item.trigger( form_action + '_response', [sec, data, response] );

                                } else {
                                        form.find('.wpas_cp_msg').addClass('error').removeClass('updated').html('<p>'+response.data.msg+'</p>').show();
                                }

                                loader.remove();
                                btn.prop('disabled', false);

                        });

                });
                
                
                // Send delete info request to server
                $('body').delegate('.wpas_cp_ui_item .wpas_cp_ui_item_action.wpas_cp_ui_item_action_delete', 'click', function(e) {
                        e.preventDefault();
                        var section = $(this).closest('.wpas_cp_ui_wrapper').data('section');
                        var item = $(this).closest('.wpas_cp_ui_item');
                        var item_id = item.data('item_id');
                        var btn = $(this);
                        var msg = btn.data('confirm');

                        if(confirm(msg)) {
                                btn.hide();
                                var loader = $('<div class="spinner"></div>');
                                loader.css({visibility: 'visible'})
                                $(this).closest('li').append(loader);

                                var data = new Array();
                                var nonce = btn.closest('.wpas_cp_ui_items').find('.delete-nonce');
                                var action = $(this).data('action');

                                data.push({name:'action', value : action });
                                data.push({name:'id', value : item_id});
                                data.push({name:$(nonce).data('name'), value : $(nonce).val()});
                                data.push({name:'duid', value : $(this).closest('.wpas_cp_ui_wrapper').data('duid')});

                                $.post(ajaxurl, data, function (response) {

                                        var msg = item.find('.wpas_cp_ui_item_msg');
                                        $(msg).find('.msg').html(response.data.msg);

                                        if( true == response.success ) {
                                                item.trigger( action + '_response', [section, data, response] );
                                                item.remove();
                                                cp_empty_section_handler(section);
                                        } else {
                                                msg.removeClass('updated').addClass('error').show();
                                                loader.remove();
                                                btn.show();
                                        }
                                });
                        }

                });
                
                /* Change publish button text to save */
                if( 'wpas_company_profile' === pagenow && 'wpas_company_profile' === typenow  ) {
                        $('#submitdiv #publishing-action input[type=submit]').val( wpas_cp.save_btn_label ).show();
                        
                        $('#postdivrich').appendTo( $('#postbox-container-2 #normal-sortables') );
                }

	});
        
}(jQuery));