/** global: tinyMCE */
/** global: wp */

jQuery(document).ready(function( $ ) {
    
    // we need to move resources to head tag
    $('table#warning-msg-table-1').find('link').detach().appendTo('head');
    $('.field_delete.delete_warningmessage').click(function() {
        var header_row = $(this).closest('tr');
        var row_num = header_row.data('header_num_row');
        $('table#warning-msg-table-'+row_num).closest('tr').remove();
        header_row.remove();
    });
    
    
    
    
    
    
    
    // handle add new message
    $('.btn_save_new_warning_msg').click(function() {
        
        var _btn = $(this);
        if(_btn.prop('disabled')) {
            return;
        }
        
        
        _btn.closest('.submit').find('.loading').show()
        
        _btn.prop('disabled', true);
        
        var data = $(this).closest('form').serializeArray();
        
        if ($('#wp-wpas_msg-wrap').hasClass("tmce-active")) {
            data.push({name : 'wpas_msg', value : tinyMCE.activeEditor.getContent()})
        }

        $.ajax({
            url : wp.ajax.settings.url,
            method : 'POST',
            data : data,
            success : function(res) {
                
                _btn.closest('.submit').find('.loading').hide();
                if(res.success) {
                    $('.new_wanring_msg_error').html("").hide();
                    $('.asac_save_success_msg').html(res.data.msg).removeClass('error').addClass('updated').show();
                    window.location = window.location.href;
                } else {
                    $('.new_wanring_msg_error').html(res.data.error).show();
                    _btn.prop('disabled', false);
                }
            }
        });
    });
    
    
    
    $('body').delegate( '.btn_ac_clear_inprocess_values', 'click', function(e) {
            
        e.preventDefault();
        var btn = $(this);
            
        if( btn.hasClass('disabled') ) {
                return;
        }

        var loader = $('<span class="spinner" style="float:none;"></span>');
        loader.css({visibility: 'visible'}).insertAfter( btn );

        btn.addClass('disabled');

        $.post( ajaxurl, {action: 'wpas_ac_clear_inprocess_values'}, function (response) {

            var msg = $('.asac_save_success_msg');
            msg.html(response.data.msg);

            if( response.success ) {
                msg.removeClass('error').addClass('updated').show();
            } else {
                msg.removeClass('updated').addClass('error').show();
            }

            $('html,body').animate({scrollTop: $('.asac_save_success_msg').offset().top-50});

            loader.remove();
            btn.removeClass('disabled');

        });

    });
    
    
    
	
});