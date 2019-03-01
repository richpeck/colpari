

(function($) {
        
        
        $( function() {
                
                /**
                 * Categories accordion control
                 */
                $('.accordion-item .accordion-header').click( function() {
                        var parent = $(this).closest('.accordion');

                        var item = $(this).closest('.accordion-item');
                        
                        parent.find( '.accordion-item' ).not(item).removeClass('active').find('.accordion-body').slideUp();
                        
                        item.toggleClass('active');
                        item.find('.accordion-body').slideToggle();
                });
                
                
                
                // Show stage 2 if stage 1 is disabled and stage 2 is enabled
                if( '0' == wpas_ss.stage_1_enabled && '1' == wpas_ss.stage_2_enabled ) {
                        $('#stage-2 #stage_2_step_1').show();
                } 
                // Show ticket submission form if both stages are disabled
                else if( '0' == wpas_ss.stage_1_enabled && '0' == wpas_ss.stage_2_enabled ) {
                        $('#wpas-new-ticket').show();
                }
                
                
                
                $('#stage-1 select[name=cat]').change( on_category_change );
                $('#stage-1 input[type=radio][name=cat]').change( on_category_change );
                $('#stage-1 .categoty_view_links li a').click( on_category_change );
                 
                /**
                 * Category change handler, triggers once a category is selected
                 * 
                 * @param object e
                 * 
                 * @returns void
                 */
                function on_category_change( e ) {
                        e.preventDefault();
                         
                         
                        var category = $(this).data('value') ? $(this).data('value') : $(this).val();
                         
                        if( !category ) {
                                return;
                        }
                        
                        var data = {
                                action : 'wpas_ss_get_topics',
                                cat : category
                        };
                        
                        
                        $('#stage-1 #stage_1_step_2').hide();
                        $('#stage-1 .answer').hide();
                        $('#stage-2 #stage_2_step_1').hide();
                        $('#stage-2 .answer .answer_content').hide();
                        $('#stage-2 .answer .buttons').hide();
                        $('#wpas-new-ticket').hide();
                        
                        
                        $('#wpas-new-ticket input[type=hidden][name=ss_category]').val(category);
                        $('#wpas-new-ticket input[type=hidden][name=ss_category_topic]').val('');
                        $('#wpas-new-ticket input[type=hidden][name=ss_search_term]').val('');
                        
                        
                        
                        var _ele = $(this);
                        
                        var loader = $('<div class="spinner"></div>');
                        loader.insertAfter( $(this).parent().find(':last-child') );
                        
                        $.post( wp.ajax.settings.url, data, function (response) {
                                
                                $('#stage-1 select[name=topic] option:gt(0)').remove();
                                
                                
                                if(  response.success ) {
                                        
                                        $.each( response.data, function() {
                                                $('#stage-1 select[name=topic]').append('<option value="'+this.id+'">'+this.title+'</option>')
                                        });
                                        
                                        
                                        $('#stage-1 #stage_1_step_2').show();
                                }
                                
                                _ele.parent().find('.spinner').remove();
                                
                        });
                }
                 

                
                /**
                 * Trigger once category topic is selected from accordion
                 */
                $('.accordion-item .category_topics a').click( function(e) {
                        e.preventDefault();
                        on_topic_selected( this, $(this).data('id') );
                });
                
                /**
                 * Trigger once category topic is selected from dropdown
                 */
                $('#stage-1 select[name=topic]').change( function(e) {
                        on_topic_selected( this, $(this).val() );
                });
                
                
                
                /**
                 * Topic change handler, triggers once a category topic is selected
                 * 
                 * @param object _ele
                 * @param int topic
                 * 
                 * @returns void
                 */
                function on_topic_selected( _ele, topic ) {
                        
                        var data = {
                                action : 'wpas_ss_get_topic_anwser',
                                topic  : topic
                        };
                        
                        $('#stage-1 .answer').hide();
                        $('#stage-2 #stage_2_step_1').hide();
                        $('#stage-2 .answer .answer_content').hide();
                        $('#stage-2 .answer .buttons').hide();
                        $('#wpas-new-ticket').hide();
                        
                        if( !topic ) {
                                return;
                        }
                        
                        
                        
                        $('#wpas-new-ticket input[type=hidden][name=ss_category_topic]').val( topic );
                        $('#wpas-new-ticket input[type=hidden][name=ss_search_term]').val('');
                        
                        var loader = $('<div class="spinner"></div>');
                        loader.insertAfter( $(_ele) );
                        
                        $.post( wp.ajax.settings.url, data, function (response) {
                                
                                $('#stage-1 .answer').show();
                                
                                if(  response.success ) {
                                        $( '#stage-1 .answer .answer_content' ).html( response.data.answer );
                                }
                                
                                loader.remove();
                        });
                }
                
                
                /**
                 * Show stage 2 if user didn't get answer from 1st stage and stage 2 is enabled, otherwise show ticket submission form
                 */
                $('#stage-1 .btn_no').click( function(e) {
                        e.preventDefault();
                        
                        if( '0' == wpas_ss.stage_2_enabled ) {
                                $('#wpas-new-ticket').show();
                        } else {
                                $('#wpas-new-ticket').hide();
                                $('#stage-2 #stage_2_step_1').show();
                        }
                });
                
                /**
                 * Detect enter key pressed in search field
                 */
                $('#stage-2 #search_field').keypress( function(e) {
                        if( e.keyCode == 13 ) {
                                $('#stage-2 .btn_search').trigger('click');
                        }
                });
                
                
                /**
                 * Trigger once user press search button in stage 2
                 */
                $('#stage-2 .btn_search').click( function() {
                        
                        if( $(this).prop('disabled')) {
                                return;
                        }
                        
                        var q = $('#stage-2 #search_field').val();
                        
                        $('#wpas-new-ticket').hide();
                        $('#stage-2 .answer .answer_content').hide();
                        $('#stage-2 .answer .buttons').hide();
                        var data = {
                                action : 'wpas_ss_search_anwser',
                                q : q
                        };
                        
                        var _btn = $(this);
                        
                        $('#stage_2_step_1 .ss_msg').hide();
                        var loader = $( '<div class="spinner"></div>' );
                        loader.insertAfter( _btn );
                        
                        
                        _btn.prop( 'disabled', true );
                        
                        $.post( wp.ajax.settings.url, data, function (response) {
                                
                                
                                
                                if(  response.success ) {
                                        $( '#stage-2 .answer .answer_content' ).html( response.data.answer ).show();
                                        $('#wpas-new-ticket input[type=hidden][name=ss_search_term]').val(q);
                                } else {
                                        $('#stage_2_step_1 .ss_msg').addClass('error').removeClass('updated').html('<p>'+response.data.msg+'</p>').show();
                                }
                                
                                $('#stage-2 .answer .buttons').show();
                                
                                loader.remove();
                                _btn.prop('disabled', false);
                        });
                });
                
                $('.open_ticket_btn_2').click( function(e) {
                        e.preventDefault();
                        $('#wpas-new-ticket').show();
                });
                
                
                
        });
        
        
        
        
})(jQuery);


