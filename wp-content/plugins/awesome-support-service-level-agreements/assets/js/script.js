
(function ($) {
	"use strict";
        
	$(function () {
                
                /* Turn input fields into datepicker */
                $('input[name=holiday_date]').datepicker();
                
                $('input.test_ticket_receipt_date').datetimepicker();

                /**
                 * Calculate test due date
                 */
                $('.test_due_date_calculate_button').click(function() {
                        
                        var btn = $(this);
                        
                        if( btn.prop( 'disabled' ) ) {
                                return;
                        }
                        
                        var data = new Array(
                                { name: 'ticket_receipt_date', value : $('input.test_ticket_receipt_date').val() },
                                { name: 'ticket_id', value : $('input#post_ID').val() }
                                );
                        
                        
                        data = wpas_sla_prepare_data( data, 'wpas_sla_get_test_due_date' );
                        
                        var loader = $('<div class="spinner"></div>').css('visibility', 'visible');
                        
                        loader.insertAfter( btn );
                        btn.prop( 'disabled', true );
                        
                        $.post(ajaxurl, data, function (response) {

                                if( response.success ) {
                                        $('.calculated_test_due_date_msg').hide();
                                        $('.calculated_test_due_date').show().find('.date').html(response.data.due_date).show();
                                } else {
                                        $('.calculated_test_due_date').hide().find('.date').html('');
                                        $('.calculated_test_due_date_msg').addClass('error').show().find('p').html( response.data.msg );
                                }
                                
                                loader.remove();
                                btn.prop( 'disabled', false );
                         });
                });
                
                /**
                 * Include nonce field in ajax requests
                 * 
                 * @param string action
                 * 
                 * @returns string
                 */
                function wpas_sla_get_nonce( action ) {
                        
                        if( $('#sla_nonce_'+action).length > 0 ) {
                                return $('#sla_nonce_'+action).val();
                        }

                        return '';
                }
                
                /**
                 * Prepare data to submit with ajax
                 * 
                 * @param array data
                 * @param string action
                 * 
                 * @returns array
                 */
                function wpas_sla_prepare_data( data, action ) {
                
                        if( Array.isArray( data ) ) {
                                data.push( { name : 'action', value : action } );
                        } else {
                                data.action = action;
                        }
                        

                        var nonce = wpas_sla_get_nonce( action );
                        if( nonce ) {
                                if( Array.isArray( data ) ) {
                                        data.push( { name : 'security', value : nonce } );
                                } else {
                                        data.security = nonce;
                                }
                        }

                        return data;
                }
                
                 /**
                 * Condition to prevent JavaScript error. It checks if:
                 * 1) Selector exists
                 * 2) jQuery Select2 is loaded
                 * 3) WordPress AJAX is enabled
                 */
                var selector = $('.wpas-select2');
                var condition = selector.length && $.fn.select2 && typeof ajaxurl !== 'undefined';
                if (condition) getSlaList();

                /**
                 * Get Sla post List via AJAX
                 * https://select2.github.io/examples.html#data-ajax
                 */
                function getSlaList() {
                        selector.each(function (index, el) {
                                
                                var type = $(el).attr('data-opt-type');
                                
                                
                                
                                if ( 'sla_id_picker' === type ) {

                                        var act = 'wpas_get_sla';
                                        
                                        $(el).select2({
                                                ajax: {
                                                        url: ajaxurl,
                                                        dataType: 'json',
                                                        type: 'POST',
                                                        delay: 250,
                                                        data: function (params) {

                                                                var data = {
                                                                        q: params.term,
                                                                        ticket_id : (typeof wpas.ticket_id !== 'undefined' ? wpas.ticket_id : '')
                                                                };

                                                                if( $(el).data('filter_req') ) {
                                                                        $($(el).data('filter_req')).each(function() {
                                                                                data[$(this).attr('name')] = $(this).val()
                                                                        })
                                                                }
                                                                
                                                                return wpas_sla_prepare_data( data, act );
                                                        },
                                                        processResults: function (data, params) {
                                                                return {
                                                                        results: $.map(data, function (obj) {
                                                                                return {
                                                                                        id: obj.id,
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
                
                
                /* Add new email alert from edit sla post */
                $('#wpas_sla_add_email_alert_btn').click( function(e) {
                        e.preventDefault();
                        
                        
                        
                        
                        var id = $('#wpas_sla_email_alerts .wpas_sla_email_alert').length > 0 ? $('#wpas_sla_email_alerts .wpas_sla_email_alert').last().data('alert_id') : '0';
                        
                        var new_item_id = parseInt( id ) + 1;
                        var name_prefix = 'sla_alerts[' + new_item_id + ']';
                        var id_prefix = 'sla_alerts__' + new_item_id + '__';
                        var editor_name = id_prefix + 'content';
                        
                        var template = wp.template( 'wpas-sla-add-email-alert' );
                        
                        
                        var el = template( { 
                                editor_id: editor_name, 
                                id_prefix : id_prefix,  
                                name_prefix: name_prefix,
                                alert_id : new_item_id
                        } );
                        
                        $(el).appendTo( $('#wpas_sla_email_alerts') );
                        
                        $('html,body').animate({
                                scrollTop: $('table[data-alert_id=' + new_item_id + ']').offset().top - 50
                        }, 500);
                        
                        
                        tinyMCEPreInit.mceInit[ editor_name ] =  $.extend({}, tinyMCEPreInit.mceInit.content );
                        tinyMCEPreInit.mceInit[ editor_name ].selector = '#' + editor_name;
                        tinyMCEPreInit.mceInit[ editor_name ].body_class = id_prefix + "content post-type-wpas_sla post-status-publish locale-en-us";
                        
                        
                        new QTags( editor_name );
			QTags._buttonsInit();
			switchEditors.go( editor_name, 'html' );
                        
                        
                });
                
                /* Delete email alert */
                $('body').delegate( '.btn_wpas_sla_delete_email_alert', 'click', function(e) {
                        e.preventDefault();
                        $(this).closest('.wpas_sla_email_alert').remove();
                });
                
                /* Making due date fields readonly once lock date checkbox checked */
                $('.sla_ticket_lock_due_date input[type=checkbox]').change( function() {
                        
                        if( $(this).prop('checked') ) {
                                $('.sla_ticket_due_date input').prop( 'readonly', true );
                        } else {
                                $('.sla_ticket_due_date input').prop( 'readonly', false );
                        }
                        
                });
                
                /* Report page turn fields into datepicker */
                $('.wpas_sla_report_start_date, .wpas_sla_report_end_date').datepicker({
                        dateFormat : 'yy-mm-dd'
                });
                
                
                /* Time convert into minutes helper */
                function calculate_time_into_minutes() {
                        
                        var time = get_time_into_minutes();
                        $('.wpas_sla_time_convert_answer').html( time + ' Minutes' );
                        
                        if( 0 < time ) {
                                $( '.wpas_sla_set_target_time_btn' ).show();
                        } else {
                                $( '.wpas_sla_set_target_time_btn' ).hide();
                        }
                }
                
                /**
                 * Convert time onto minutes 
                 * 
                 * @returns {Number}
                 */
                function get_time_into_minutes() {
                        var time = parseInt( $('.wpas_sla_time_convert_time').val() );
                        var option = parseInt( $('.wpas_sla_time_convert_option').val() );
                        
                        if( time && option ) {
                                return time * option;
                        }
                        
                        return 0;
                }
                
                $('.wpas_sla_time_convert_option').change( function() {
                        calculate_time_into_minutes();
                });
                
                $('.wpas_sla_time_convert_time').keyup( function() {
                        calculate_time_into_minutes();
                });
                
                $('.wpas_sla_time_convert_time').click( function() {
                        calculate_time_into_minutes();
                });
                
                /* Set calculated time in target time field */
                $('.wpas_sla_set_target_time_btn').click( function( e) {
                        e.preventDefault();
                        
                        var time = get_time_into_minutes();
                        $('input[name=time_frame]').val( time );
                });
                
                /* Recalculate due date for all tickets linked to a sla id */
                $('.wpas_sla_recalculate_due_dates').click( function() {
                        
                        var sla_id = $('#post_ID').val();
                        
                        var btn = $(this);
                        
                        var data = new Array({ name: 'sla_id', value : sla_id });
                        data = wpas_sla_prepare_data( data, 'wpas_sla_recalculate_due_dates' );
                        
                        var loader = $('<div class="spinner"></div>').css('visibility', 'visible');
                        
                        loader.insertAfter( btn );
                        btn.prop( 'disabled', true );
                        
                        $.post(ajaxurl, data, function (response) {
                                var msg = $('.wpas_sla_recalculate_due_dates_msg');
                                msg.find('p').html( response.data.msg );
                                
                                if( response.success ) {
                                        msg.addClass('updated').removeClass('error').show();
                                } else {
                                        msg.removeClass('updated').addClass('error').show();
                                }
                                
                                loader.remove();
                                btn.prop( 'disabled', false );
                         });
                        
                });
                
                // On sla post page move default content editor to content metabox
                if( 1 === $('body.wp-admin.post-type-wpas_sla #postdivrich').length && 'wpas_sla' === pagenow && 'wpas_sla' === typenow) {
                        $('.wp-admin.post-type-wpas_sla #postdivrich').appendTo( $('.wpas_sla_post_content') );
                }
                
                /* Toggle no cutoff time checkbox field when 'all day is work day' setting changed*/
                $( '#wpassla_workday_mon___active_full_day,\
                    #wpassla_workday_tue___active_full_day,\
                    #wpassla_workday_wed___active_full_day,\
                    #wpassla_workday_thu___active_full_day,\
                    #wpassla_workday_fri___active_full_day,\
                    #wpassla_workday_sat___active_full_day,\
                    #wpassla_workday_sun___active_full_day').on( 'change', function() {
                        var name = $(this).attr('name').split('___')[0] + '___active_no_cutoff_time';
                        console.log( $( 'input[name='+name+']') );
                        if( 1 === $( 'input[name='+name+']').length ) {
                                var field_row = $( 'input[name='+name+']').closest('tr');
                                
                                if( $(this).prop('checked') ) {
                                        field_row.show();
                                } else {
                                        field_row.hide()
                                }
                        }
                        
                });
                
                
	});
        
}(jQuery));