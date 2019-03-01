/*
 * @package   Awesome Support: Private Credentials
 * @author    Robert W. Kramer III for Awesome Support <support@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016. Awesome Support
 *
 */



jQuery(document).ready(function ($) {

    /**
     * Load view
     */

     $(document).on('click', '.wpas-pc-load', function(e) {
        e.preventDefault();

        return loadView( $(this).data('post-id'), $(this).data('view'), $(this).data('key') );
    
     });

    /**
     * Close modal
     */
    $(document).on('click', '.as-pc-modal-close', function(e) {
        e.preventDefault();
        closeModal();
    });

    /**
     * Show modal window
     */
     function showModal() {

        var modal = $('.as-pc-modal');

        if ( ! modal.is(':visible') ) {

            $('body').addClass('wpas-overflow-hidden');    
            modal.fadeIn();
        }

     }

    /**
     * Close modal window and cleniup the content
     */
     function closeModal() {

        $('body').removeClass('wpas-overflow-hidden');
        $('.as-pc-modal').fadeOut();
        $('.as-pc-modal-content').html('');
     }


    /**
     * Function to load a view into container 
     */
     function loadView( post_id, view, key = false, show_loader = true ) {

        showModal();

        // show loader
        if ( show_loader ) {
            $('.as-pc-modal-content').html('<img src="' + txtVars.adminurl + 'images/loading.gif">');
        }
  
        $.post( txtVars.ajaxurl, {
            action : 'wpas_pc_load_view',
            nonce : txtVars.nonce,
            id: post_id,
            template: view,
            key: key
        }).done(function( data ) {

            $('.as-pc-modal-content').html( data );

        }).fail(function( data ){
            console.log(data.responseText);
        });

     }


    /**
     * Generate random encryption key
     *
     * @returns {string}
     */
    function guid() {

        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });

    }


    /**
     * Change crypt key
     */
    $(document).on('change', '#as-pc-crypt-key', function(e) {

        e.preventDefault();

        if ( $(this).is(':checked') ) {

            var $key = guid();

            $('input[name="crypt_key"]').val($key);

        }

    });

    /**
     * Run action
     */
    $(document).on('click', '.wpas-pc-action', function(e) {

        e.preventDefault();

        var post_id      = $(this).data('post-id');
        var action       = $(this).data('action');
        var key          = $(this).data('key') || false;
        var confirmation = $(this).data('confirm') || false;

        // check for confirmation
        if ( confirmation ) {

            if ( ! confirm( confirmation ) ) {
                return false;
            }

        }

        // show loader in status
        $('.as-pc-modal-status').html('<img src="' + txtVars.adminurl + 'images/loading.gif">');

        $.post( txtVars.ajaxurl, {
            action : 'wpas_pc_action',
            nonce: txtVars.nonce,
            id: post_id,
            trigger: action,
            key: key,
            dataType: 'json'
        }).done(function( data ) {

            // action OK?
            if ( data.status === 1 ) {

                loadView( post_id, data.view, key, false );

            } else {
                $('.as-pc-modal-status').html( data.message );
            }



        }).fail(function( data ){
            console.log(data.responseText);
        });

     });


     /**
      * Save data
      */
     $(document).on('submit', '#as-pc-save-data-form', function(e){

        e.preventDefault();

        var post_id = $('input[name="post_id"]', this).val();
        var fields  = $('input[type="text"]', this);
        var errors  = false;

        // check fields
        $.each( fields, function(i, v){

            if ( $(this)[0].hasAttribute('required') && $(this).val() == '' ) {

                errors = true;

                $(this).addClass('wpas-input-error');

                // add event to clear the error on keyup
                $(this).on('keyup', function(){
                    $(this).removeClass('wpas-input-error');
                });

            }

        });

        // check errors
        if ( ! errors ) {

            var btn = $('input[type="submit"]', this);

            btn.attr('disabled', true);

            var form_data = $(this).serialize();

            // show loader in status
            $('.as-pc-modal-status').html('<img src="' + txtVars.adminurl + 'images/loading.gif">');

            $.post( txtVars.ajaxurl, {
                action : 'wpas_pc_action',
                nonce: txtVars.nonce,
                trigger: 'save_data',
                data : form_data,
                dataType: 'json'
            }).done(function( data ) {

                // action OK?
                if ( data.status === 1 ) {

                    return loadView( post_id, data.view, false, false );

                } else {
                    $('.as-pc-modal-status').html( data.message );
                    btn.attr('disabled', false);
                }

            }).fail(function( data ){
                console.log(data.responseText);
            });

        }


     });


});
