
(function ($) {
	"use strict";
        
	$(function () {
                
                
                // Smart Responses : Remove keyword setting
                $('body').delegate('.wpas_cbot_keyword_del_btn', 'click', function(e) {
                        e.preventDefault();
                        
                        $(this).closest('table').closest('tr').remove();
                });
                
                // Smart Responses : Add new keyword
                $('a.wpas_cbot_keyword_add_btn').click(function(e) {
                        e.preventDefault();
                        
                        
                        var new_option = $('table.cbot_option_add_keyword').closest('tr').clone();
                        
                        var new_option_index = 0;
                        
                        
                        if( 0 !== $('table.cbot_option_multi_keyword').length ) {
                                new_option_index = parseInt( $('table.cbot_option_multi_keyword:last').data('row_index') ) + 1
                        }
                        
                        
                        var id_prefix = 'wpas_cbot_search_keywords_' + new_option_index + '__';
                        var name_prefix = 'wpas_cbot_search_keywords['+new_option_index+']';
                        
                        
                        
                        new_option.find('.wpas_cbot_keyword_add_btn').remove();
                        new_option.find('.cbot_option_add_keyword')
                                .data( 'now_index', new_option_index )
                                .addClass('cbot_option_multi_keyword')
                                .removeClass('cbot_option_add_keyword');
                        
                        
                        
                        
                        new_option.find('.cbot_add_kw_sr_enabled_field')
                                .attr('id', id_prefix+'smart_replies_enabled')
                                .attr('name', name_prefix + '[smart_replies_enabled]');
                        
                        new_option.find('.cbot_add_kw_keyword_field')
                                .attr('id', id_prefix+'keyword')
                                .attr('name', name_prefix + '[keyword]');
                        
                        new_option.find('.cbot_add_kw_content_field')
                                .attr('id', id_prefix+'content')
                                .attr('name', name_prefix + '[content]');
                        
                        
                        new_option.find('.cbot_add_kw_type_field input[type=radio]').each( function() {
                                
                                $(this)
                                        .attr('id', id_prefix + $(this).data('id') )
                                        .attr('name', name_prefix + $(this).data('name'));
                                
                        });
                        
                        
                                
                        
                        if( 0 === new_option_index ) {
                                new_option.insertAfter($(this).closest('tr'));
                        } else {
                                new_option.insertAfter( $('table.cbot_option_multi_keyword:last').closest('tr') );
                        }
                        
                        // Auto scroll to new added item
                        $('html,body').animate({scrollTop: new_option.offset().top});
                        
                });
                
                // Add new multi text setting, used for smart chat include and exclude url settings
                $('.wpas_multi_text_field_add_new .field_add_new_btn').click( function(e) {
                        
                        e.preventDefault();
                        
                        var new_field_container = $(this).closest('.wpas_multi_text_field_add_new');
                        
                        var new_field = new_field_container.find('.wpas_sc_mtext_field_group').clone();
                        
                        
                        var index = parseInt( new_field_container.data('new_field_index') );
                        
                        
                        new_field = $(
                                $('<div />').append(new_field).html()
                                .replace( /{{{index}}}/gm, index )
                                .replace('data-name', 'name')
                                .replace('data-id', 'id'));
                        
                        
                        new_field.insertBefore( new_field_container );
                        
                        index++;
                        
                        new_field_container.data( 'new_field_index', index );
                        
                        
                });
                
                
                // Remove multi text setting
                $('body').delegate('.wpas_sc_mtext_field_group .wpas_sc_remove_mtext_field', 'click', function() {
                        
                        $(this).closest('.wpas_sc_mtext_field_group').remove();
                });
                
                // Manual smart reply
                $('.wpas_sc_manual_smart_reply_btn').click(function( e ) {
                        e.preventDefault();
                        
                        if( $(this).hasClass('disabled')) {
                                return;
                        }
                        
                        var btn = $(this);
                        
                        var data = new Array();
                        
                        data.push({name : 'action',    value : 'wpas_sc_manual_smart_reply' });
                        data.push({name : 'ticket_id', value : wpas.ticket_id });
                        data.push({name : 'security',  value : $(this).data('nonce') });
                        
                        var loader = $('<div class="spinner"></div>').css({visibility:'visible'});
                        loader.insertAfter( btn );
                        
                        btn.addClass('disabled');
                        
                        // Send request to get smart reply
                        $.ajax({
                                type : "post",
                                dataType : "json",
                                url : wpas.ajaxurl,
                                data : data,
                                success: function(response) {
                                        
                                        
                                        if( response.success ) {
                                                
                                                // Put smart reply in reply editor
                                                if( typeof tinyMCEPreInit !== "undefined" && typeof tinyMCEPreInit.mceInit.wpas_reply === "object" ) {
                        
                                                        var $wrap = tinymce.$( '#wp-wpas_reply-wrap' );

                                                        if ( $wrap.hasClass( 'tmce-active' ) ) {

                                                                var editor = tinyMCE.get('wpas_reply');
                                                                
                                                                var content =  response.data.content + ( editor.getContent() ).replace(/\n/g, "<br />");
                                                                editor.setContent( content );
                                                                $('html,body').animate({scrollTop: $('#wp-wpas_reply-wrap').offset().top});

                                                        } else {
                                                                var content = response.data.content + $('#wpas_reply').val();
                                                                $('#wpas_reply').val(content);
                                                                
                                                                $('html,body').animate({scrollTop: $('#wpas_reply').offset().top-80});
                                                        }
                                                }
                                                
                                        }
                                        
                                        loader.remove();
                                        btn.removeClass('disabled');
                                }
                      });
                        
                });
                
                
                
                // Recalculate google natural language tags
                $('.wpas_cbot_gnl_recalculate_tags_btn').click(function( e ) {
                        e.preventDefault();
                        
                        if( $(this).hasClass('disabled')) {
                                return;
                        }
                        
                        var btn = $(this);
                        
                        var data = new Array();
                        
                        data.push({name : 'action',    value : 'wpas_cbot_gnl_recalculate_tags' });
                        data.push({name : 'ticket_id', value : wpas.ticket_id });
                        data.push({name : 'security',  value : $(this).data('nonce') });
                        
                        var loader = $('<div class="spinner"></div>').css({visibility:'visible'});
                        loader.insertAfter( btn );
                        
                        btn.addClass('disabled');
                        
                        $.ajax({
                                type : "post",
                                dataType : "json",
                                url : wpas.ajaxurl,
                                data : data,
                                success: function(response) {
                                        
                                        if( response.success ) {
                                                $('.entity_buttons').html(response.data.entities).show();
                                                $('.pof_buttons').html(response.data.tags).show();
                                        }
                                        
                                        loader.remove();
                                        btn.removeClass('disabled');
                                }
                      });
                        
                });
                
                
                
                
                /* Smart Responses : Post types */
                $('.wpas_cbot_search_post_type  .post_type_checkbox').change(function() {
                        if( $(this).is(':checked') ) {
                                $(this).closest('.wpas_cbot_search_post_type').find('.wpas_cbot_exclude_post_fields').slideDown();
                        } else {
                                $(this).closest('.wpas_cbot_search_post_type').find('.wpas_cbot_exclude_post_fields').slideUp();
                        }
                });
                
                
	});
        
}(jQuery));