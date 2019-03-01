
(function ($) {
	"use strict";
        
	$(function () {
                
                
                $('body').delegate('.wpas_cbot_keyword_del_btn', 'click', function(e) {
                        e.preventDefault();
                        
                        $(this).closest('table').closest('tr').remove();
                });
                
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
                        
                        $('html,body').animate({scrollTop: new_option.offset().top});
                        
                })
                
	});
        
}(jQuery));