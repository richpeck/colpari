// thickbox settings
var tb_position;




(function ($) {
	"use strict";
        
	$(function () {
                
                /* Reposition thickbox to default */
                tb_position = function() {
                        var isIE6 = typeof document.body.style.maxHeight === "undefined";
                        if( jQuery("#TB_window").length ) {
                                jQuery("#TB_window").css({marginLeft: '-' + parseInt((TB_WIDTH / 2),10) + 'px', width: TB_WIDTH + 'px'});
                                if ( ! isIE6 ) { // take away IE6
                                        jQuery("#TB_window").css({marginTop: '-' + parseInt((TB_HEIGHT / 2),10) + 'px'});
                                }
                        }
                };

                $(window).resize(function(){ tb_position(); });
                
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
               var selector = $('.wpas-select2.it-select2');
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
                                                                data.capability = $(el).data('capability');
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
                
                        if( $('#it_nonce_'+action).length > 0 ) {
                                return $('#it_nonce_'+action).val();
                        }

                        return '';
                }
                
                /* Prepare form data */
                function wpas_get_form_data( form, action ) {
                        
                        $('.wp-editor-wrap').each( function() {
                                var editor = tinymce.get( $(this).attr('id').split('-')[1] );
                                
                                if( editor != null ) {
                                        editor.save();
                                }
                        });
                        
                        
                        
                        var data = $( form ).find('select, textarea, input').serializeArray();
                        
                        if( !action ) {
                                action = $( form ).data( 'action' );
                        }
                        
                        return wpas_prepare_data( data, action );
                        
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
                
                /* Init color picker fields */
                $('.term-color-wrap #term-meta-color').wpColorPicker();
                
                /* Handle add comment request */
                $('.wpas_it_btn_comment').click( function() {
                        
                        var btn = $(this);
                        var data = wpas_get_form_data( '.wpas_it_comment_form', 'wpas_it_add_issue_comment' );
                        
                        var loader = $('<div class="spinner"></div>').css('visibility', 'visible');
                        
                        loader.insertAfter( btn );
                        btn.prop( 'disabled', true );
                        
                        $.post(ajaxurl, data, function (response) {

                                if( true == response.success ) {
                                        $('.wpas_it_message').addClass('updated').removeClass('error').html( response.data.msg ).show();
                                        window.location = response.data.location;
                                } else {
                                        $('.wpas_it_message').addClass('error').removeClass('updated').html( response.data.msg ).show();
                                }
                                
                                loader.remove();
                                btn.prop( 'disabled', false );
                         });
                        
                        
                        
                });
                
                
                // close thickbox on close button click
                $('body').delegate('.it_tb_close_btn input[type=button]', 'click', function() {
                        tb_remove();
                });
                
                
                // Clear add item form fields after an item is added, for example : personal notes, todos, signatures etc
                function it_clear_fields() {
                        
                        $('.wpas_it_tb_window_wrapper').find('input, select, textarea, checkbox, radio').each(function() {
                                var tagName = $(this).prop('tagName');
                                
                                if( $(this).data().hasOwnProperty('default') ) {
                                        var clear = $(this).data('default');
                                        if( 'INPUT' === tagName && $(this).attr('type') !== 'hidden' ) {
                                                $(this).val(clear);
                                        } else if( 'TEXTAREA' === tagName ) {
                                                $(this).val(clear);
                                        } else if( 'SELECT' === tagName ) {
                                                console.log('select');
                                                if( $(this).hasClass( 'wpas-select2' ) ) {
                                                        $(this).val('').trigger('change');
                                                } else {
                                                        $(this).val( $(this).find('option:first').val() );
                                                }
                                        } else if( 'CHECKBOX' === tagName ) {
                                                $(this).prop('checked', false);
                                        }
                                }

                        });
                }

                /**
                 * Checks if list is empty
                 */
                function it_empty_section_handler( section ) {
                        if( 0 === $('#wpas_it_'+section+'_items .wpas_it_ui_item').length ) {
                                $('#wpas_it_'+section+'_items .wpas_it_ui_items').hide();
                                $('#wpas_it_'+section+'_items .no_item_msg').show();

                        } else {
                                $('#wpas_it_'+section+'_items .wpas_it_ui_items').show();
                                $('#wpas_it_'+section+'_items .no_item_msg').hide();
                        }
                }

                /* Check if tabs data is empty and show empty message */
                $('.wpas_it_ui_wrapper').each(function() {
                        it_empty_section_handler($(this).data('section'));
                })


                // Open add info window
                $('.wpas_it_tb_button a').click(function(e) {
                        e.preventDefault();
                        var tb_wrapper = $(this).closest('.wpas_tb_window');
                        tb_wrapper.find('.wpas_it_msg').removeClass('error updated').html('');
                        tb_show($(this).attr('title'), $(this).attr('href'));
                        $('#TB_ajaxContent').css('height','auto');

                        it_clear_fields( tb_wrapper );

                        $('#TB_ajaxContent').trigger( 'wpas_tb_window_opened' );
                });

                // Send add/edit info request to server
                $('body').delegate('.wpas_it_tb_window_wrapper .submit input[type=button]', 'click', function() {

                        var data = new Array();
                        var btn = $(this);
                        var form = $(btn).closest('.wpas_it_tb_window_wrapper');
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

                        var form_action = form.find('.wpas_it_form_action').val();
                        data.push({name:'action', value : form_action});
                        data.push({name:'duid', value : $('#wpas_it_ui_section_'+sec).data('duid')});

                        $.post(ajaxurl, data, function (response) {


                                if( true == response.success ) {


                                        if( response.data.item ) {
                                                form.find('.wpas_it_msg').removeClass('error').addClass('updated').html('<p>'+response.data.msg+'</p>');
                                                var item = $(response.data.item);
                                                var items_container = $('#wpas_it_'+sec+'_items .wpas_it_ui_items');
                                                
                                                items_container.append(item);
                                                it_clear_fields();
                                        } else if( response.data.update_item ) {
                                                var item = $(response.data.update_item);

                                                item.find('.wpas_it_ui_item_msg .msg').html(response.data.msg);
                                                item.find('.wpas_it_ui_item_msg').addClass('updated').removeClass('error').show();
                                                $('#wpas_it_'+sec+'_items .wpas_it_ui_items .wpas_it_ui_item[data-item_id='+response.data.item_id+']').replaceWith(item);
                                                tb_remove();
                                        } else if( response.data.location ) {
                                                window.location = response.data.location;
                                                return;
                                        }

                                        it_empty_section_handler(sec)

                                        item.trigger( form_action + '_response', [sec, data, response] );

                                } else {
                                        form.find('.wpas_it_msg').addClass('error').removeClass('updated').html('<p>'+response.data.msg+'</p>');
                                }

                                loader.remove();
                                btn.prop('disabled', false);

                        });

                });
                
                // Open edit item form
                $('body').delegate('.wpas_it_ui_item_action_edit, .wpas_it_tb_win_btn', 'click', function(e) {
                        e.preventDefault();
                        tb_show($(this).attr('title'), $(this).attr('href'));

                        $('#TB_ajaxContent').trigger('wpas_it_window_opened');
                });


                // making inline notice dismissable
                $('body').delegate('.wpas_it_ui_item .notice-dismiss', 'click', function(e) {
                        $(this).closest('.wpas_it_ui_item_msg').slideUp();
                });

                // Send delete info request to server
                $('body').delegate('.wpas_it_ui_item .wpas_it_ui_item_action.wpas_it_ui_item_action_delete', 'click', function(e) {
                        e.preventDefault();
                        var section = $(this).closest('.wpas_it_ui_wrapper').data('section');
                        var item = $(this).closest('.wpas_it_ui_item');
                        var item_id = item.data('item_id');
                        var btn = $(this);
                        var msg = btn.data('confirm');

                        if(confirm(msg)) {
                                btn.hide();
                                var loader = $('<div class="spinner"></div>');
                                loader.css({visibility: 'visible'})
                                $(this).closest('li').append(loader);

                                var data = new Array();
                                var nonce = btn.closest('.wpas_it_ui_items').find('.delete-nonce');
                                var action = $(this).data('action');

                                data.push({name:'action', value : action });
                                data.push({name:'id', value : item_id});
                                data.push({name:$(nonce).data('name'), value : $(nonce).val()});
                                data.push({name:'duid', value : $(this).closest('.wpas_it_ui_wrapper').data('duid')});

                                $.post(ajaxurl, data, function (response) {

                                        var msg = item.find('.wpas_it_ui_item_msg');
                                        $(msg).find('.msg').html(response.data.msg);

                                        if( true == response.success ) {
                                                item.trigger( action + '_response', [section, data, response] );
                                                item.remove();
                                                it_empty_section_handler(section);
                                        } else {
                                                msg.removeClass('updated').addClass('error').show();
                                                loader.remove();
                                                btn.show();
                                        }
                                });
                        }

                });



                $('body').on('wpas_tb_window_opened', '#TB_ajaxContent', check_tb_window_loaded )
                $('body').on('wpas_tb_window_loaded', '#TB_ajaxContent', on_tb_window_loaded);

                /* Call once thickbox window loaded */
                function on_tb_window_loaded() {
                        
                        // Fix select2 dropdown in thickbox
                        
                        var select2_exists = false;
                        var user_picker_exists = false;
                        $('#TB_ajaxContent').find('.wpas-select2').each(function() {

                                if( $(this).data('opt-type') === 'user-picker' )  {
                                        user_picker_exists = true;
                                } else {
                                        select2_exists = true;
                                }
                        });


                        if( select2_exists ) {
                                selector = $('#TB_ajaxContent').find('.wpas-select2');
                                getSelect2Data( selector );
                        }

                        if( user_picker_exists ) {
                                selector = $('#TB_ajaxContent').find('.wpas-select2');

                                if( typeof window.getUserListSelect2 !== "undefined" ) {
                                        console.log('exists');
                                        window.getUserListSelect2( selector );
                                }
                        }


                        $('#TB_ajaxContent').css('height','auto');
                }
        
                /* check if thickbox window loaded */
                function check_tb_window_loaded() {

                        
                        if( 0 === $('#TB_ajaxContent').length ) {
                                return -1;
                        }

                        if( $('#TB_ajaxContent').closest('#TB_window').css('visibility') == 'hidden' ) {
                                setTimeout( check_tb_window_loaded , 500 );
                        } else {
                                $('#TB_ajaxContent').trigger('wpas_tb_window_loaded');
                        }
                }
                
                
                /* Handle Close issue */
                $('body').delegate( '.btn_close_issue_with_tickets, .btn_close_just_issue', 'click',  function(e) {
                
                        e.preventDefault();
                        
                        if( $(this).hasClass('disabled') ) 
                                return;
                        
                        
                        var btn = $(this);
                        
                        var loader = $('<div class="spinner"></div>').css('visibility', 'visible');
                        
                        loader.insertAfter( btn );
                        
                        $('.btn_close_issue_with_tickets, .btn_close_just_issue').addClass('disabled');
                        
                        
                        var close_tickets = '';
                        if( $( this ).hasClass('btn_close_issue_with_tickets') ) {
                                close_tickets = '1';
                        }
                        
                        var data = new Array();
                        
                        data.push({name : 'issue_id', value : $('.close_issue_id').val() } )
                        data.push({name : 'close_tickets', value : close_tickets } )
                        
                        data = wpas_prepare_data( data, 'wpas_it_close_issue' );
                        
                        $.post( ajaxurl, data, function (response) {
                                
                                
                                var msg = $('#tb_Window').find('.wpas_it_msg');
                                $(msg).html(response.data.msg);

                                
                                if( true == response.success ) {
                                        tb_remove();
                                        if( response.data.location ) {
                                                window.location = response.data.location;
                                        }
                                } else {
                                        msg.removeClass('updated').addClass('error').show();
                                        loader.remove();
                                        btn.show();
                                }
                                
                                
                                loader.remove();
                        });
                        
                        
                });
                
                
                /* Validate issue submit form */
                $('.wpas_it_issue_publish_btn').click( function() {
                        
                        var agent = $('select[name=wpas_it_primary_agent]').val();
                        
                        // Make sure support staff is selected
                        if( !agent ) {
                                $(this).closest('#major-publishing-actions').find('.wpas_it_msg').addClass('error').html( '<p>'+wpas_it.support_staff_required_msg+'</p>' );
                                return false;
                        }
                        
                        $(this).closest('#major-publishing-actions').find('.wpas_it_msg').removeClass('error').html('');
                        return true;
                        
                });
                
                        
                /* confirm deleting issue comment */
                $('.wpas-it-comment-controls .wpas-delete').click(function (e) {
			if ( confirm( $(this).data('confirm') ) ) {
				return true;
			} else {
				return false;
			}
		});
                
                
                
	});
        
}(jQuery));