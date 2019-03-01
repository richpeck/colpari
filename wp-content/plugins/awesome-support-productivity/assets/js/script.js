/** global: wpas */
/** global: ajaxurl */
/** global: wpas_pf */
/** global: typenow */
/** global: pagenow */
/** global: adminpage */

(function ($) {
	"use strict";
        
        
        $.fn.hasAttr = function(name) {  
                return this.attr(name) !== undefined;
        };

	$(function () {
                
                $('#btn-merge').click(function() {
                        var merge_ticket = $('select[name="wpas_merge_ticket"]').val();
                        if(merge_ticket && !$(this).prop('disabled')) {
                                
                                var btn = $(this);
                                $('#wpas_ticket_merge_wrapper .spinner').css({visibility:'visible'});
                                btn.prop('disabled', true);
                                
                                var data = {
                                        action: 'wpas_merge_ticket',
					target_ticket: merge_ticket,
					ticket_id: wpas.ticket_id
                                };
                                
                                $.post(ajaxurl, data, function (response) {
                                        
                                        if( (true == response.success || false == response.success) && response.data.location ) {
                                                $('#wpas_ticket_merge_wrapper .spinner').css({visibility:'hidden'});
                                                btn.prop('disabled', false);
                                                window.location = response.data.location;
                                        }
                                });
                                
                        }
                });
                
                $('#wpas_ticket_lock_cb').change(function() {
                        
                        if( $(this).prop("checked") ) {
                                $('#wpas_ticket_lock').val('1');
                        } else {
                                $('#wpas_ticket_lock').val('0');
                        }
                });
                
                
                
                
                
                /**
	 * Condition to prevent JavaScript error. It checks if:
	 * 1) Selector exists
	 * 2) jQuery Select2 is loaded
	 * 3) WordPress AJAX is enabled
	 */
	var selector = $('.wpas-select2');
	var condition = selector.length && $.fn.select2 && typeof ajaxurl !== 'undefined';
	if (condition) getTicketsList();

	/**
	 * Get User List via AJAX
	 * https://select2.github.io/examples.html#data-ajax
	 */
	function getTicketsList() {
		selector.each(function (index, el) {
                        console.log($($(el).data('filter_req')).length);
                        
                        
			var type = $(el).attr('data-opt-type');
			if ('ticket-picker' === type || 'product-picker' === type) {
                                
                                var act;
                                if( 'ticket-picker' === type ) {
                                        act = 'wpas_get_tickets';
                                } else if( 'product-picker' === type ) {
                                        act = 'wpas_get_products';
                                }
                                
				$(el).select2({
					ajax: {
						url: ajaxurl,
						dataType: 'json',
						type: 'POST',
						delay: 250,
						data: function (params) {
                                                        
                                                        var data = {
								action: act,
								q: params.term,
                                                                ticket_id : (typeof variable !== 'undefined' ? wpas.ticket_id : '')
							};
                                                        
                                                        if( $(el).data('filter_req') ) {
                                                                $($(el).data('filter_req')).each(function() {
                                                                        data[$(this).attr('name')] = $(this).val()
                                                                })
                                                        }
                                                        
							return data
						},
						processResults: function (data, params) {
							return {
								results: $.map(data, function (obj) {
									return {
										id: obj.ticket_id,
										text: obj.text
									};
								})
							};
						}
					},
					minimumInputLength: 3
				});
			}
		});
	}
        
        // We need to remove name attribute for add new user fields as it might conflict with ticket fields
        $('#createuserform').find('input, select').each(function() {
                var name = $(this).attr('name');
                
                if(name) {
                        $(this).removeAttr('name').attr('id', name);
                }
        });
        
        // Open add user window
        $('.wpas_pf_add_user_button a').click(function(e) {
                e.preventDefault();
                $('.pf_add_user_msg').removeClass('error updated').html('');
                tb_show($(this).attr('title'), $(this).attr('href'));
                $('#TB_ajaxContent').css('height','auto');
                pf_au_clear_fields();
        });
        
        // Send add user request to server
        $('#createuserform .submit input[type=button]').click(function(){
                var data = new Array();
                var btn = $(this);
                var form = $(btn).closest('#createuserform');
                
                var loader = $('<div class="spinner"></div>');
                loader.insertAfter( $('#TB_window .tb_footer').children('p').last() );
                btn.prop('disabled', true);
                
                form.find('input, select').each(function() {
                        
                        var name = $(this).attr('id');
                        var value = $(this).val();
                        data.push({ name: name, value : value });
                });
                
                data.push({name:'action', value : 'wpas_pf_createuser'});
                
                $.post(ajaxurl, data, function (response) {
                                        
                        if( true == response.success ) {
                                form.find('.pf_add_user_msg').removeClass('error').addClass('updated').html('<p>'+response.data+'</p>');
                                pf_au_clear_fields();
                                
                        } else {
                                form.find('.pf_add_user_msg').addClass('error').removeClass('updated').html('<p>'+response.data+'</p>');
                        }
                        
                        loader.remove();
                        btn.prop('disabled', false);
                });
                
        });
        
        
        // Edit ticket page : Clear add user form fields after a user is added
        function pf_au_clear_fields() {
                
                $('#createuserform').find('#user_login, #email, #first_name, #last_name').each(function() {
                        $(this).val('');
                });
                                
                $('#role').val( $('#role option:first').val() );
                $('#send_user_notification').prop('checked', false);
        }
        
        // Clear add item form fields after an item is added, for example : personal notes, todos, signatures etc
        function pf_clear_fields() {
                
                $('.wpas_tb_window_wrapper').find('input, select, textarea, checkbox, radio').each(function() {
                        var tagName = $(this).prop('tagName');
                        
                        if( $(this).data().hasOwnProperty('default') ) {
                                var clear = $(this).data('default');
                                if( 'INPUT' === tagName && $(this).attr('type') !== 'hidden' ) {
                                        $(this).val(clear);
                                } else if( 'TEXTAREA' === tagName ) {
                                        $(this).val(clear);
                                } else if( 'SELECT' === tagName ) {
                                        $(this).val( $(this).find('option:first').val() );
                                } else if( 'CHECKBOX' === tagName ) {
                                        $(this).prop('checked', false);
                                }
                        }
                        
                });
        }
        
        /**
         * Checks if list is empty
         */
        function pf_empty_section_handler( section ) {
                if( 0 === $('#wpas_pf_'+section+'_items .wpas_pf_ui_item').length ) {
                        $('#wpas_pf_'+section+'_items .wpas_pf_ui_items').hide();
                        $('#wpas_pf_'+section+'_items .no_item_msg').show();
                        
                } else {
                        $('#wpas_pf_'+section+'_items .wpas_pf_ui_items').show();
                        $('#wpas_pf_'+section+'_items .no_item_msg').hide();
                }
        }
        
        $('.wpas_pf_ui_wrapper').each(function() {
                pf_empty_section_handler($(this).data('section'));
        })
        
        
        // Open add info window
        $('.wpas_pf_tb_button a').click(function(e) {
                e.preventDefault();
                var tb_wrapper = $(this).closest('.wpas_tb_window');
                tb_wrapper.find('.wpas_pf_msg').removeClass('error updated').html('');
                tb_show($(this).attr('title'), $(this).attr('href'));
                $('#TB_ajaxContent').css('height','auto');
                
                pf_clear_fields( tb_wrapper );
                
                $('#TB_ajaxContent').trigger('tb_window_open');
        });
        
        $('body').delegate('.wpas_pf_ui_item_action_edit, .wpas_pf_tb_win_btn', 'click', function(e) {
                e.preventDefault();
                tb_show($(this).attr('title'), $(this).attr('href'));
                
                $('#TB_ajaxContent').trigger('wpas_pf_window_opened');
        });
        
        function onPFWindowLoadedHandler() {
                
                $('#TB_ajaxContent').find('.wpas_pf_date_field').each(function() {
                        if( false === $(this).hasClass('hasDatepicker') )  {
                                $(this).datepicker({
                                        dateFormat : "mm/dd/yy"
                                });
                        }
                });
                
                
                var ticket_picker_exists = false;
                var user_picker_exists = false;
                $('#TB_ajaxContent').find('.wpas-select2').each(function() {
                        if( $(this).data('opt-type') === 'ticket-picker' )  {
                                ticket_picker_exists = true;
                        }
                        
                        if( $(this).data('opt-type') === 'user-picker' )  {
                                user_picker_exists = true;
                        }
                });
                
                
                if( ticket_picker_exists ) {
                        selector = $('#TB_ajaxContent').find('.wpas-select2');
                        getTicketsList();
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
        
        function checkPFWindowLoaded() {
                
                
                if( 0 === $('#TB_ajaxContent').length ) {
                        return -1;
                }
                
                if( $('#TB_ajaxContent').closest('#TB_window').css('visibility') == 'hidden' ) {
                        setTimeout( checkPFWindowLoaded , 500 );
                } else {
                        $('#TB_ajaxContent').trigger('wpas_pf_window_loaded');
                }
        }
        
        $('body').on('wpas_pf_window_opened', '#TB_ajaxContent', checkPFWindowLoaded )
        $('body').on('wpas_pf_window_loaded', '#TB_ajaxContent', onPFWindowLoadedHandler);
        
        // Send add/edit info request to server
        $('body').delegate('.wpas_tb_window_wrapper .submit input[type=button]', 'click', function() {

                var data = new Array();
                var btn = $(this);
                var form = $(btn).closest('.wpas_tb_window_wrapper');
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
                
                var form_action = form.find('.wpas_pf_form_action').val();
                data.push({name:'action', value : form_action});
                data.push({name:'duid', value : $('#wpas_pf_ui_section_'+sec).data('duid')});
                
                $.post(ajaxurl, data, function (response) {
                          
                        
                        if( true == response.success ) {
                                
                                
                                if( response.data.item ) {
                                        form.find('.wpas_pf_msg').removeClass('error').addClass('updated').html('<p>'+response.data.msg+'</p>');
                                        var item = $(response.data.item);
                                        $('#wpas_pf_'+sec+'_items .wpas_pf_ui_items').append(item);
                                        pf_clear_fields();
                                } else if( response.data.update_item ) {
                                        var item = $(response.data.update_item);
                                        
                                        item.find('.wpas_pf_ui_item_msg .msg').html(response.data.msg);
                                        item.find('.wpas_pf_ui_item_msg').addClass('updated').removeClass('error').show();
                                        $('#wpas_pf_'+sec+'_items .wpas_pf_ui_items .wpas_pf_ui_item[data-item_id='+response.data.item_id+']').replaceWith(item);
                                        tb_remove();
                                } else if( response.data.location ) {
                                        window.location = response.data.location;
                                        return;
                                }
                                
                                pf_empty_section_handler(sec)
                                
                                item.trigger( form_action + '_response', [sec, data, response] );
                                
                        } else {
                                form.find('.wpas_pf_msg').addClass('error').removeClass('updated').html('<p>'+response.data.msg+'</p>');
                        }
                        
                        loader.remove();
                        btn.prop('disabled', false);
                        
                });
                
        });
        
        
        // making inline notice dismissable
        $('body').delegate('.wpas_pf_ui_item .notice-dismiss', 'click', function(e) {
                $(this).closest('.wpas_pf_ui_item_msg').slideUp();
        });
        
        // Send delete info request to server
        $('body').delegate('.wpas_pf_ui_item .wpas_pf_ui_item_action.wpas_pf_ui_item_action_delete', 'click', function(e) {
                e.preventDefault();
                var section = $(this).closest('.wpas_pf_ui_wrapper').data('section');
                var item = $(this).closest('.wpas_pf_ui_item');
                var item_id = item.data('item_id');
                var btn = $(this);
                var msg = btn.data('confirm');
                
                if(confirm(msg)) {
                        btn.hide();
                        var loader = $('<div class="spinner"></div>');
                        loader.css({visibility: 'visible'})
                        $(this).closest('li').append(loader);
                        
                        var data = new Array();
                        var nonce = btn.closest('.wpas_pf_ui_items').find('.delete-nonce');
                        var action = $(this).data('action');
                        
                        data.push({name:'action', value : action });
                        data.push({name:'id', value : item_id});
                        data.push({name:$(nonce).data('name'), value : $(nonce).val()});
                        data.push({name:'duid', value : $(this).closest('.wpas_pf_ui_wrapper').data('duid')});
                        
                        $.post(ajaxurl, data, function (response) {
                                
                                var msg = item.find('.wpas_pf_ui_item_msg');
                                $(msg).find('.msg').html(response.data.msg);
                                
                                if( true == response.success ) {
                                        item.trigger( action + '_response', [section, data, response] );
                                        item.remove();
                                        pf_empty_section_handler(section);
                                } else {
                                        msg.removeClass('updated').addClass('error').show();
                                        loader.remove();
                                        btn.show();
                                }
                        });
                }
                
        });
        
        
        
        function wpas_pf_item_action_normal( e ) {
                
                e.preventDefault();
                
                var btn = $(this);
                
                var section = btn.closest('.wpas_pf_ui_wrapper').data('section');
                var item = btn.closest('.wpas_pf_ui_item');
                var item_id = item.data('item_id');
                var action_name = btn.data('action_name');
                
                
                $(btn).hide();
                var loader = $('<div class="spinner"></div>');
                loader.css({visibility: 'visible'})
                
                if( 'INPUT' === btn.prop('tagName') ) {
                        $(btn).parent().prepend(loader);
                } else {
                        $(this).closest('li').append(loader);
                }
                
                var data = new Array();
                var nonce = btn.closest('.wpas_pf_ui_items').find('.'+action_name+'-nonce');
                
                
                
                if( 'INPUT' === btn.prop('tagName') ) {
                        if( btn.attr('type') == 'checkbox' && btn.prop('checked') ) {
                                data.push({name: btn.data('name'), value : btn.val()});
                        }
                }
                
                data.push({name:'action', value : $(this).data('action')});
                data.push({name:'id', value : item_id});
                data.push({name:$(nonce).data('name'), value : $(nonce).val()});
                data.push({name:'duid', value : $(this).closest('.wpas_pf_ui_wrapper').data('duid')});
                
                $.post(ajaxurl, data, function (response) {
                        item.trigger( 'pf_'+action_name+'_response', [section, response, btn] );
                });
        }
        
        
        // Send duplicate info request to server
        $('body').delegate('.wpas_pf_ui_item .wpas_pf_ui_item_action.wpas_pf_ui_item_action_normal', 'click', wpas_pf_item_action_normal );
        
        
        
        
        $('body').delegate('.wpas_pf_ui_item', 'pf_duplicate_response', function(e , section, response, btn ) {
                
                var item = $(this);
                var msg = item.find('.wpas_pf_ui_item_msg');
                
                //$(msg).find('.msg').html(response.data.msg);
                if( true == response.success ) {
                        $('#wpas_pf_'+section+'_items .wpas_pf_ui_items').append(response.data.item);
                } else {
                        msg.removeClass('updated').addClass('error').show();
                }
                
                btn.closest('li').find('.spinner').remove();
                btn.show();
        });
        
        $('body').delegate('.wpas_pf_ui_item', 'pf_completed_response', function(e , section, response, btn ) {
                
                var item = $(this);
                
                if( true == response.success ) {
                        
                        var new_item = $(response.data.item);
                        new_item.find('.wpas_pf_ui_item_msg .msg').html(response.data.msg);
                        new_item.find('.wpas_pf_ui_item_msg').addClass('updated').removeClass('error').show();
                        item.replaceWith(new_item);
                } else {
                        var msg = item.find('.wpas_pf_ui_item_msg');
                        $(msg).find('.msg').html(response.data.msg);
                        msg.removeClass('updated').addClass('error').show();
                        btn.closest('li').find('.spinner').remove();
                        btn.show();
                }
                
        });
        
        $('.wpas_pf_date_field').datepicker({
                dateFormat : "mm/dd/yy"
        });
        
        
        
        $('.wpas_pf_profile_criteria_field').each(function() {
               
                $(this).find('.select_field_type input[type=radio]').change(function() {
                        
                        var f_container = $(this).closest('.wpas_pf_profile_criteria_field');
                        console.log(f_container);
                        if('selected' === $(this).val()) {
                                $(f_container).find('.options').slideDown();
                        } else {
                                $(f_container).find('.options').slideUp();
                        }
                });
        });
        
        
        $('.wpas_pf_profile_main_criteria_fields input').change(pn_main_criteria_change);
        
        if( $('.wpas_pf_profile_main_criteria_fields input[type=checkbox]').is(':checked') ) {
                $('.wpas_pf_profile_main_criteria_fields input[type=checkbox]:checked').trigger('change');
        }
        
        
        function pn_main_criteria_change() {
                var wrapper = $(this).closest('.wpas_pf_profile_criteria_fields');
                
                $('.wpas_pf_profile_main_criteria_fields .option').removeClass('active');
                
                if( $(this).is(':checked') ) {
                        wrapper.addClass('greyed').find('input').prop( 'disabled', true);
                        $(this).prop( 'disabled', false ).closest('.option').addClass('active');
                        
                } else {
                        wrapper.removeClass('greyed').find('input').prop('disabled', false);
                }
        }
        
        
        
        
        function metabox_tabs_setup() {
                
                if( $('.wpas_pf_mb_tab').length > 0 ) {

                        $('<div id="wpas_pf_meta_tabs_wrapper"><ul id="wpas_pf_meta_tabs"></ul></div>').insertBefore($('.wpas_pf_mb_tab').get(0));

                        var tab_order = 1;
                        $('.wpas_pf_mb_tab').each( function() {

                                var tab = $(this);
                                var id = tab.attr('id');
                                var tab_name = $(this).find('h2.hndle span').html();
                                var tab_btn = $('<li data-tab-order="'+ tab_order +'" rel="'+id+'" class="wpas_pf_meta_tab">' + tab_name  + '</li>');

                                $('#wpas_pf_meta_tabs').append(tab_btn);
                                tab.hide();
                                $(tab_btn).click(change_metabox_tab_handler);
                                tab_order++;
                        });

                        $('#wpas_pf_meta_tabs').append('<li class="moreTab">\
                                <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">More <span class="caret"></span></a>\
                                <ul class="dropdown-menu tabs_collapsed"></ul>\
                                </li><li class="clear clearfix"></li>');

                        $($('.wpas_pf_meta_tab').get(0)).trigger('click');
                }
                
        }
        
        
        function change_metabox_tab_handler() {
                $('.wpas_pf_mb_tab').each( function() {
                        $(this).hide();
                });

                $('#wpas_pf_meta_tabs .wpas_pf_meta_tab').removeClass('active');
                $(this).addClass('active');
                
                var id = $(this).attr('rel');
                $('#'+id).show();
        }


        metabox_tabs_setup();
        
             
        $('body').delegate('.pf_favorite_btn, .pf_global_favorite_btn', 'click', function(e) {
                e.preventDefault();
                
                
                var btn = $(this);
                
                if( btn.hasClass('disabled') ) {
                        return;
                }
                
                btn.addClass('disabled');
                
                
                var loader = $('<div class="spinner"></div>');
                
                loader.css({visibility: 'visible'}).insertAfter(btn);
                
                var data = new Array();
                var id = btn.data('ticket');
                var action = btn.data('action');
                var nonce = btn.data('nonce');
                
                data.push({name:'action', value : action});
                data.push({name:'id', value : id});
                data.push({name:'nonce', value : nonce});
                
                
                $.post(ajaxurl, data, function (response) {
                        
                        var msg = btn.closest('.wpas_pf_widget').find('.pf_msg');
                        $(msg).find('p').html(response.data.msg);
                        
                        if( response.success ) {
                                msg.addClass('updated').removeClass('error');
                                btn.replaceWith( $(response.data.button) );
                        } else {
                                msg.addClass('error').removeClass('updated');
                        }
                        
                        msg.slideDown();
                        loader.remove();
                        btn.removeClass('disabled');
                        
                });
        });
        
        
        $('.pf_msg .notice-dismiss').click(function() {
                $(this).closest('.pf_msg').slideUp();
        });
        
        
        // Listen for Add/Edit signature response event
        $('body').delegate('.wpas_pf_ui_item', 'pf_edit_signatures_response', signature_add_edit_handler);
        $('body').delegate('.wpas_pf_ui_item', 'pf_add_signatures_response', signature_add_edit_handler);
        
        
        // add/edit signature handler
        function signature_add_edit_handler( e , section, params , response ) {
                var is_default = false;
                $(params).each(function() {
                        if( this.name == 'default' && this.value ) {
                                is_default = true;
                        }
                });
                
                if( is_default ) {
                        
                        var current_sig = $('#wpas_pf_'+section+'_items').data('default_signature');
                        
                        if( !current_sig || current_sig != $(this).data('item_id')) {
                                reply_add_signature(response.data.signature);
                                $('#wpas_pf_'+section+'_items').data('default_signature', $(this).data('item_id') )
                        }
                        
                        $('#wpas_pf_'+section+'_items .wpas_pf_ui_item').each(function() {
                                $(this).find('.actions li a[data-action_name="default"]').show().closest('li').find('span').hide();
                        });

                        $(this).find('.actions li a[data-action_name="default"]').hide().closest('li').find('span').show();
                }
        }
        
        
        // handle response for default signature action
        $('body').delegate('.wpas_pf_ui_item', 'pf_default_response', function(e , section, response, btn ) {
                
                btn.closest('li').find('.spinner').remove();
                
                $('#wpas_pf_'+section+'_items .wpas_pf_ui_item').each(function() {
                        $(this).find('.actions li a[data-action_name="default"]').show().closest('li').find('span').hide();
                });
                
                btn.hide().closest('li').find('span').show();
                
                if( 'signatures' === section ) {
                        var item = $(this);
                        var msg = item.find('.wpas_pf_ui_item_msg');

                        $(msg).find('.msg').html(response.data.msg);
                        if( true == response.success ) {
                                msg.removeClass('error').addClass('updated').show();
                                reply_add_signature(response.data.signature)
                                $('#wpas_pf_'+section+'_items').data('default_signature', $(this).data('item_id') )
                        } else {
                                msg.removeClass('updated').addClass('error').show();
                        }
                }
        });
        
        
        // Set default disnature in reply editor
        function reply_add_signature( signature ) {
                
                if( typeof tinyMCEPreInit !== "undefined" && typeof tinyMCEPreInit.mceInit.wpas_reply === "object" ) {
                        
                        var $wrap = tinymce.$( '#wp-wpas_reply-wrap' );

                        if ( $wrap.hasClass( 'tmce-active' ) ) {
                                
                                var editor = tinyMCE.get('wpas_reply');
                                
                                if( editor === null ) {
                                        setTimeout(function() { reply_add_signature(signature) }, 1000 )
                                        return;
                                }
                                
                                
                                var content =  (editor.getContent() + "\n \n \n" + signature).replace(/\n/g, "<br />");
                                editor.setContent( content );
                                
                        } else {
                                var content = $('#wpas_reply').val() + '\n \n \n' + signature;
                                $('#wpas_reply').val(content);
                        }
                }
                
        }
        
        
        
        // reinit wp_editor for add signature window
        $('body').on('tb_window_open', '#TB_ajaxContent', function() {
                var win = $(this);
                var section = win.find('.wpas_tb_window_wrapper').data('section');
                
                if(section == 'signatures' && typeof tinymce !== 'undefined') {
                        tinyMCE.get('pf_signature_content_add').destroy();
                        tinymce.init( tinyMCEPreInit.mceInit.pf_signature_content_add )
                }
        });
        
        
        // Remove wp_editor instances after thickbox window closed
        var tb_editors = new Array();
       	$( 'body' ).on( 'thickbox:removed', function() {
                if( typeof tinyMCEPreInit !== 'undefined' ) {
                        $.each( tb_editors, function() {
                                var id = this;
                                
                                $(tinyMCEPreInit.qtInit).removeProp(id);
                                $(tinyMCEPreInit.mceInit).removeProp(id)
                                tinymce.remove( tinyMCE.get(id) );
                                
                                tb_editors = $.grep(tb_editors, function(value) {
                                        return value != id;
                                });
                        });
                }
        });
        
        // Fix wp_editor for ajax thickbox windows
        $('body').on('wpas_pf_window_loaded', '#TB_ajaxContent' , function() {
                
                var win = $(this);
                
                win.find('.wp-editor-area').each(function() {
                        var id = $(this).attr('id');
                        
                        
                        if( tinyMCEPreInit.mceInit.hasOwnProperty(id) ) {
                                var init = tinyMCEPreInit.mceInit[id];
                        } else {
                                var init = $.extend({}, tinyMCEPreInit.mceInit.pf_signature_content_add, { selector : "#" + id });
                                tinyMCEPreInit.mceInit[id] = init;
                        }
                        
			var $wrap = tinymce.$( '#wp-' + id + '-wrap' );

			if ( ( $wrap.hasClass( 'tmce-active' ) || ! tinyMCEPreInit.qtInit.hasOwnProperty( id ) ) && ! init.wp_skip_init ) {
				tinymce.init( init );

				if ( ! window.wpActiveEditor ) {
					window.wpActiveEditor = id;
				}
			}
                        
                        
                        
                        if ( typeof quicktags !== 'undefined' ) {
                                
                                tinyMCEPreInit.qtInit[id] = {id:id, buttons : tinyMCEPreInit.qtInit.pf_signature_content_add.buttons}
                                $(QTags.instances).removeProp('0');
				quicktags( tinyMCEPreInit.qtInit[id] );
				if ( ! window.wpActiveEditor ) {
					window.wpActiveEditor = id;
				}
			}
                        
                        if( tinyMCE.get(id).hidden === false && $( '#wp-' + id + '-wrap' ).hasClass('html-active')) {
                                $( '#wp-' + id + '-wrap' ).removeClass('html-active').addClass('tmce-active');
                        }
                        
                        tb_editors.push(id);
                        
                });
        });
        
        
        // Set default disnature in reply editor on page load
        if( typeof wpas_pf === "object" ) {
                if(wpas_pf.hasOwnProperty('default_signature')) {
                        reply_add_signature(wpas_pf.default_signature.signature);
                }
        }
        
        
        
        
        // making tabs smart responsive
        var processing_resize = false;
        var processing_resize_queue = false;
        
        function meta_tabs_responsive() {
                
                if( processing_resize ) {
                        processing_resize_queue = true;
                        return;
                }
                
                processing_resize = true;
                
                var tabs = $('#wpas_pf_meta_tabs');
                var wrapper_width = tabs.innerWidth() - 60;
                var children = tabs.children('li:not(:last-child, .moreTab)');
                
                var items_width = 0;
                var iw = 0;
                
                
                var limit_over = false;
                
                
                children.each(function() {
                        iw = $(this).innerWidth();
                        if($(this).hasClass('active')) {
                                iw += 2;
                        }
                        
                        if( !limit_over && wrapper_width > items_width + iw ) {
                                
                                items_width += iw ;
                                
                        } else {
                                limit_over = true;
                                
                                $(this).appendTo( '#wpas_pf_meta_tabs .tabs_collapsed' );
                                $(this).data('inner_width', iw );
                                
                        }
                        
                        
                });
                
                $(tabs.find('.tabs_collapsed li').toArray().sort(sort_items)).appendTo( $(tabs.find('.tabs_collapsed')) )
                limit_over = false;
                
                $('#wpas_pf_meta_tabs .tabs_collapsed li').each(function(){
                        iw = parseInt($(this).data('inner_width'));
                        
                        if( !limit_over && wrapper_width > items_width + iw ) {
                                var last_tab = $('#wpas_pf_meta_tabs > li:not(.moreTab , .clear):last');
                                
                                if( last_tab.length === 1 ) {
                                        $(this).insertAfter(  last_tab );
                                } else {
                                        $(this).prependTo($('#wpas_pf_meta_tabs'));
                                }
                                
                                items_width += iw ;
                        } else {
                              limit_over = true;
                        }
                });
                
                
                if( tabs.find('.tabs_collapsed li').length === 0 ) {
                        tabs.find('.moreTab').hide();
                } else {
                        tabs.find('.moreTab').show();
                }
                
                processing_resize = false;
                
                if( processing_resize_queue ) {
                        meta_tabs_responsive();
                        processing_resize_queue = false;
                }


        }
        
        function sort_items(a, b){
                return parseInt($(a).data('tab-order')) - parseInt($(b).data('tab-order'));
        }
        
  
  	meta_tabs_responsive();

	$(window).on('resize', meta_tabs_responsive);
        
        
        // Tools -> capabilities tab : Update role capabilities checkboxes
        function update_role_capabilities( caps ) {
            
            $('#wpas_pf_role_caps_options input[type="checkbox"]').each(function() {
                var val = $(this).val();
                if($.inArray(val, $(caps).toArray()) !== -1 ) {
                    $(this).prop('checked', true);
                } else {
                    $(this).prop('checked', false);
                }
            });
                                
            $('.wpas_pf_caps_row').show();
            $('.wpas_pf_quickset_btns_row').show();
            $('#pf_update_caps_btn').show();
            $('.pf_overlay').hide();
            
            
        }
        
        // Tools -> capabilities tab : Trigger once a role is select with dropdown
        $('#pf_settings_cap_role').change(function() {
                
                // Hide capabilities and buttons if a role is unselected
                if($(this).val() == "") {
                        $('.wpas_pf_caps_row').hide();
                        $('.wpas_pf_quickset_btns_row').hide();
                        $('#pf_update_caps_btn').hide();
                } else {
                        
                        var loader = $('<span class="spinner" style="float:none;"></span>');
                        loader.css({visibility: 'visible'}).insertAfter($(this));
                        
                        
                        $('.pf_overlay').show();
                        var data = {
                                action: 'wpas_pf_settings_caps',
				role: $(this).val()
                        };
                        
                        // Load role capabilities and update view
                        $.post(ajaxurl, data, function (response) {
                                update_role_capabilities( response.data.caps );
                                loader.remove();
                        });
                        
                }
                
        });

        // Tools -> capabilities tab : Update role capabilities
        $('#pf_update_caps_btn').click(function() {
                
                $('.pf_overlay').show();
                
                var data = jQuery('#wpas_pf_role_caps_options input[type="checkbox"]:checked').serializeArray();
                
                data.push({name: 'action', value : 'wpas_pf_settings_caps_update'});
                data.push({name: 'role', value : $('#pf_settings_cap_role').val()});
                
                
                $.post(ajaxurl, data, function (response) {

                        if(response.success) {
                                $('.updated.below-h2 p').html(response.data.msg);
                                $('.updated.below-h2').show();
                                $('html,body').animate({scrollTop: $('.updated.below-h2 p').offset().top-50});
                        }
                        
                        $('.pf_overlay').hide();
                        
                });
        });
        
        // Tools -> capabilities tab : Quickset capabilities
        $('.wpas_pf_quickset_btn').click( function(e) {
            e.preventDefault();
            if($(this).hasClass('disabled')) {
                return;
            }
            
            if( confirm("Are you sure you want to update capabilities with selected preset capabilities") ) {
                    
                    $('.wpas_pf_quickset_btn').addClass('disabled');
                    $('#pf_update_caps_btn').hide();
                    
                    var loader = $('<span class="spinner" style="float:none;"></span>');
                    loader.css({visibility: 'visible'}).insertAfter( $('#pf_settings_cap_role') );
                    
                    $('.pf_overlay').show();
                    var data = {
                        action: 'wpas_pf_settings_caps_update_preset',
			role: $('#pf_settings_cap_role').val(),
                        preset : $(this).data('preset')
                    };
                    
                    $.post(ajaxurl, data, function (response) {
                        update_role_capabilities( response.data.caps );
                        loader.remove();
                        $('.wpas_pf_quickset_btn').removeClass('disabled')
                    });
            }
            
            
        });
        
        // Trigger once a checkbox is checked|unchecked under ticket tabs
        $('body').delegate('.wpas_pf_ui_item .active-cb', 'change', wpas_pf_item_action_normal );
        
        
        
        // calls after notification email | user contact | additional emails turns active
        $('body').delegate('.wpas_pf_ui_item', 'pf_active_response', function(e , section, response, btn ) {
                
                
                var item = $(this);
                var msg = item.find('.wpas_pf_ui_item_msg');
                
                
                if( true == response.success ) {
                        
                        var new_item = $(response.data.update_item);
                        new_item.find('.wpas_pf_ui_item_msg .msg').html(response.data.msg);
                        new_item.find('.wpas_pf_ui_item_msg').addClass('updated').removeClass('error').show();
                        item.replaceWith(new_item);
                        
                } else {
                        var msg = item.find('.wpas_pf_ui_item_msg');
                        $(msg).find('.msg').html(response.data.msg);
                        msg.removeClass('updated').addClass('error').show();
                        btn.show();
                }
                
                btn.parent().find('.spinner').remove();
                btn.show();
                
        });
        
        
        // Open multi ticket merge window
        $('#doaction, #doaction2').click(function() {
                
                
                
                var data = $(this).closest('form').find('input[type=checkbox][name="post[]"]:checked').serialize();
                data = "?action=wpas_pf_multi_ticket_merge_view&"+data;
                
                var current_action = $(this).closest('.actions').find('select').val();
                
                
                if( 'wpas_pf_multi_ticket_merge' === current_action ) {
                        
                        tb_show( 'Merge Tickets', wpas.ajaxurl+data );
                        
                        $('#TB_ajaxContent').trigger('wpas_pf_window_opened');
                        
                        getTicketsList();
                        return false;
                }
                
                
                return true;
        });
        
        
        // Process multi ticket merge 
        $('body').delegate('.multi_ticket_merge_view .submit input[type=button]', 'click', function() {
                
                var btn = $(this);
                
                if( btn.prop('disabled') ) {
                        return;
                }
                
                var form = $(btn).closest('.multi_ticket_merge_view');
                
                
                var target_ticket_id = $('#wpas_merge_target_ticket_dd').val();
                var post_ids = $('#wpas_merge_source_tickets_dd option:selected');
                
                if( 0 === post_ids.length ) {
                        form.find('.wpas_pf_msg').addClass('error').removeClass('updated').html('<p>'+wpas_pf.multi_merge_msgs.post_error+'</p>');
                        return;
                } else if( !target_ticket_id ) {
                        form.find('.wpas_pf_msg').addClass('error').removeClass('updated').html('<p>'+wpas_pf.multi_merge_msgs.target_ticket_error+'</p>');
                        return;
                }
                
                
                
                var loader = $('<div class="spinner"></div>');
                loader.insertAfter( $('#TB_window .tb_footer').children('p').last() );
                btn.prop( 'disabled', true );
                
                var loc_obj = window.location;
                
                var loc = loc_obj.protocol + "//" +  loc_obj.host + loc_obj.pathname
                var url = loc + '?' + $('#posts-filter').find('input, select').not('[name="post[]"]').serialize();
                
                post_ids.each( function() {
                        url += '&post[]='+$(this).val();
                });
                
                url += '&target_id='+target_ticket_id;
                window.location = url;
                
        });
        
        
        // close thickbox on close button click
        $('body').delegate('.tb_close_btn input[type=button]', 'click', function() {
                tb_remove();
        });
        
        // Add signature in reply editor
        $('body').delegate('.wpas_pf_ui_item .wpas_pf_ui_item_action.wpas_pf_ui_item_action_use', 'click', function(e) {
                e.preventDefault();
                
                var sig = $(this).data('signature');
                
                reply_add_signature( sig )
                
        });
        
        
        // Handle ticket listing page
        if( 'ticket' === typenow && 'edit-ticket' === pagenow && 'edit-php' === adminpage ) { 
                
                /**
                 * Start : Bulk edit
                 */
                
                if( $('.inline-edit-status select[name="_status"]').length > 0 ) {
                        
                        // Removing statuses that are not related to Awesome Support (basically removing generic WP statuses)
                        $('.inline-edit-status select[name="_status"]')
                                .find('option[value=publish], option[value=private], option[value=draft], option[value=pending]').remove();
                        
                        
                        // Lets add custom statuses in bulk edit status field
                        if( typeof wpas_pf.statuses !== "undefined" ) {
                                $.each(wpas_pf.statuses, function(value, text ) {
                                        $('.inline-edit-status select[name="_status"]').append('<option value="'+value+'">'+text+'</option>')
                                });
                        }
                }
                
                
                var status_override_field = '<input type="hidden" name="post_status_override" value="" />';
                var action_field = '<input type="hidden" name="wpas_pf_action_inline_edit" value="1" />'; // adding custom action so we can handle it with custom code
                var agent_field = 
                '<div class="inline-edit-col inline-edit-col-agent-field">\
                        <div class="inline-edit-group wp-clearfix">\
                                <label class="inline-edit-status alignleft">\
                                        <span class="title">Support Staff</span>\
                                        <select name="wpas_assignee" class=" wpas-select2" id="wpas-assignee" data-capability="edit_ticket"></select>\
                                </label>\
                        </div>\
                </div>';

                $(agent_field).insertAfter($('.inline-edit-status select[name="_status"]').closest('.inline-edit-col')); // Adding agent feild in bulk edit form
                $(action_field).insertAfter($('.inline-edit-status select[name="_status"]').closest('.inline-edit-col'));
                $(status_override_field).insertAfter($('.inline-edit-status select[name="_status"]').closest('.inline-edit-col'));

                // Allow only one option per custom field
                function restrict_checklist() {
                        if( $(this).is(':checked') ) {
                                $(this).closest('.cat-checklist').find('input[type="checkbox"]').not(this).prop('checked', false );
                        }
                }

                // Remove custom fields which we are processing right now
                $(document).ready( function() {
                        $('#bulk-edit .inline-edit-col .cat-checklist').not('.product-checklist, .department-checklist, .ticket_priority-checklist, .ticket_channel-checklist').each( function() {
                                $(this).prevAll("input[type=hidden]:first, span.title:first").remove();
                                $(this).remove();
                        });
                });

                $('body').delegate('.cat-checklist.product-checklist input[type="checkbox"]', 'change', restrict_checklist );
                $('body').delegate('.cat-checklist.department-checklist input[type="checkbox"]', 'change', restrict_checklist );
                $('body').delegate('.cat-checklist.ticket_priority-checklist input[type="checkbox"]', 'change', restrict_checklist );
                $('body').delegate('.cat-checklist.ticket_channel-checklist input[type="checkbox"]', 'change', restrict_checklist );
                $('body').delegate('.inline-edit-status select[name="_status"]', 'change', function() {
                        
                        var status = '-1' === $(this).val() ? '' : $(this).val();
                        $('input[name=post_status_override]').val( status );
                });
                
                
                // Send ajax request to save bulk edit
                $('body').delegate('.submit.inline-edit-save input#bulk_edit[type=submit]' , 'click', function(e) {
                        e.preventDefault();
                        
                        var data = $(this).closest('form').find('[name!=action], [name!=action2]').serializeArray();
                        var btn = $(this);
                 
                 
                        var skip_data = new Array('action', 'action2');
                 
                        data = data.filter(function(elem){
                               return !( $.inArray(elem.name, skip_data) !== -1 )
                        });
                 
                 
                        btn.prop( 'disabled', true );
                        
                        var loader = $('<div class="spinner"></div>').css({visibility:'visible'});;
                        loader.insertAfter( btn );
                 
                        data.push({name : 'action', value : 'wpas_pf_save_inline_edit'});
                 
                 
                         $.post(ajaxurl, data, function (response) {

                                 if( true == response.success ) {
                                         if( response.data.location ) {
                                                 window.location = response.data.location;
                                         }
                                 } else {
                                         loader.remove();
                                         btn.prop( 'disabled', false );
                                 }
                         });
                });
                
                
                
                /**
                * Start : Save filters
                */

               // Trigger once a saved filter is deleted
               $('body').delegate('.wpas_pf_ui_item', 'pf_delete_ticket_filter_options_response', function(e , section, data, response ) {
                       $('select.pf_saved_filters_dropdown option[value='+response.data.item_id+']').remove();

                       if( 0 === $('select.pf_saved_filters_dropdown option[value!=""]').length ) {
                               // Hide delete filter and saved filters dropdown as there is no saved filter exist
                               handle_no_saved_filter_exists();
                       }
               });

               // hide dropdown and delete filter button if no filter exist
               function handle_no_saved_filter_exists() {
                       $('select.pf_saved_filters_dropdown').hide();
                       $('.wpas_pf_search_criteria_listing_win_btn').hide();
               }

               // show dropdown and delete filter button once a filter is added or on page load
               function handle_saved_filter_exists() {
                       $('select.pf_saved_filters_dropdown').show();
                       $('.wpas_pf_search_criteria_listing_win_btn').show();
               }


               if( 0 === $('select.pf_saved_filters_dropdown option[value!=""]').length ) {
                       // Hide delete filter and saved filters dropdown as there is no saved filter exist
                       handle_no_saved_filter_exists();
               } else {
                       // show delete filter and saved filters dropdown
                       handle_saved_filter_exists();
               }


               // Load saved filters for tickets listing
               $('select[data-name=pf_saved_filters]').change( function(e) {
                       if( $(this).val() ) {

                               var dd = $(this);

                               dd.prop( 'disabled', true );

                               var loader = $('<div class="spinner"></div>');
                               loader.css({visibility: 'visible', float: 'none'}).insertAfter(dd);

                               var data = new Array(
                                       {name : 'action', value : 'wpas_pf_load_saved_filter'},
                                       {name : 'id', value : $(this).val()}
                                       );


                               $.post(ajaxurl, data, function (response) {

                                       if( true == response.success ) {
                                               if( response.data.location ) {
                                                       window.location = response.data.location;
                                                       return;
                                               }
                                       } else {
                                               dd.prop( 'disabled', false );
                                               loader.remove();
                                       }
                               });
                       }
               });


               // Send ajax request to save filters
               $('body').delegate('.save_criteria_btn', 'click', function(e) {
                       e.preventDefault();

                       var data = new Array();
                       var btn = $(this);
                       var form = $(btn).closest('.wpas_tb_window_wrapper');
                       var sec = form.data('section');

                       var loader = $('<div class="spinner"></div>');
                       loader.insertAfter( $('#TB_window .tb_footer').children('p').last() );
                       btn.prop('disabled', true);



                       var temp_params = window.location.search.substring(1).split('&');

                       // prepare loaded criteria params to send with server request
                       $.each(temp_params, function() { 
                               var param = this.split('=');
                               if(2 === param.length) {
                                       data.push({name : 'criteria['+param[0]+']', value : param[1]})
                               }
                       });

                       // prepare any input field in save filters form to send with server request
                       form.find('input').each(function() { 
                               if( ($(this).hasAttr('id') || $(this).hasAttr('data-name')) && !$(this).hasClass('ed_button') ) {
                                       var name = $(this).hasAttr('data-name') ? $(this).data('name') : $(this).attr('id');
                                       var value = $(this).val();
                                       data.push({ name: name, value : value });
                               }
                       });


                       var form_action = form.find('.wpas_pf_form_action').val();
                       data.push({name:'action', value : form_action});
                       data.push({name:'duid', value : $('#wpas_pf_ui_section_'+sec).data('duid')});

                       $.post(ajaxurl, data, function (response) {

                               // Lets create an empty container to add success or failed messages
                               if( 0 === $('#wpbody .wpas_pf_main_notification_bar').length ) {
                                       $('<div class="wpas_pf_main_notification_bar"></div>').insertBefore( $('.subsubsub') );
                               }

                               var msg_ele = $('.wpas_pf_main_notification_bar');

                               if( true == response.success ) {
                                       msg_ele.removeClass('error').addClass('updated').html('<p>'+response.data.msg+'</p>');
                                       var item = $(response.data.item);
                                       $('select[data-name=pf_saved_filters]').append(item); // Adding dropdown element after filter is saved
                                       handle_saved_filter_exists(); // show delete filter and saved filters dropdown as a new item is added
                                       pf_clear_fields();
                                       tb_remove();

                               } else {
                                       // Display error message
                                       form.find('.wpas_pf_msg').addClass('error').removeClass('updated').html('<p>'+response.data.msg+'</p>');
                               }

                               loader.remove();
                               btn.prop('disabled', false);
                       });
               })
               
        }
        
	});
        
        
        
}(jQuery));