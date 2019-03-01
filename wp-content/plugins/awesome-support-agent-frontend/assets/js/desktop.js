jQuery(document).ready(function ($) {

    var table_translations = {        
        'lengthMenu': ASFA.lengthMenu,
        'zeroRecords': ASFA.zeroRecords,
        'info': ASFA.info,
        'infoEmpty': ASFA.infoEmpty,
        "infoFiltered": ASFA.infoFiltered,
        "search":  ASFA.search,
        "paginate": {
            "first": ASFA.first,
            "last": ASFA.last,
            "next": ASFA.next,
            "previous": ASFA.previous
        }
    };

    // dataTables
    var open_tickets_table = $('#fa-open-tickets').DataTable({  
        columnDefs: [ {
            className: 'control',
            orderable: false,
            targets:   0
        } ],
        'destroy': true,
        order: [ 1, 'asc' ],
        'initComplete': function () {
            $(this).fadeIn();
        },
        'language': table_translations
    });

    var closed_tickets_table = false;

    // show settings menu
    $('.wpas-fa-setting-menu-tab a').on('click', function(e){
        e.preventDefault();
        $('.wpas-fa-settings-menu').toggle();
    });

    // show custom fields sidebar
    $(document).on('click', '.wpas-fa-arrow-down', function(e){
        e.preventDefault();

        $(this).toggleClass('wpas-open');
        $('#wpas-fa-sidebar-content-table-' + $(this).data('id') ).toggle();

    });


    // show editor
    function show_editor( id ) {

        tinymce.remove('#wpas-fa-reply-editor-' + id);

        // load reply editor
        tinymce.init( {
            mode : 'exact',
            elements : 'wpas-fa-reply-editor-' + id,
            plugins: ['link lists hr'],
            menubar: false,
            toolbar: 'bold italic underline strikethrough hr bullist numlist link unlink',
            branding: false
        } );

    }
    
    // tabs
    $(document).on('click', '[data-page-id]', function (e) {

        e.preventDefault();

        $('[data-page-id]').removeClass('active');
        $('[data-page]').removeClass('active');

        $('[data-page="' + $(this).data('page-id') + '"]').addClass('active');
        $(this).addClass('active');

    });

    // add tag to current table search input
    $(document).on('click', '[data-fa-search]', function (e) {
        e.preventDefault();

        var table_id = $(this).parents('table').attr('id');
        $('#' + table_id + '_filter input').val($(this).data('fa-search')).keyup();
    });


    // load closed tickets 
    $(document).on('click', '[data-page-id="closed-tickets"]', function (e) {

        e.preventDefault();

        // check if closed tickets are loaded
        if ( ! closed_tickets_table) {

            // show loader
            $('[data-page="closed-tickets"]').html('<img src="' + ASFA.admin_url + 'images/loading.gif">');

            // get closed tickets
            $.post(ASFA.ajax_url, { 
                action: 'view_closed_tickets', 
                nonce: ASFA.nonce 
            }).done(function( data ) {

                $('[data-page="closed-tickets"]').html(data);

                //  initialize datatables
                closed_tickets_table = $('#fa-closed-tickets').DataTable({
                    columnDefs: [ {
                        className: 'control',
                        orderable: false,
                        targets:   0
                    } ],
                    'destroy': true,
                    order: [ 1, 'asc' ],
                    'initComplete': function () {
                        $(this).fadeIn();
                    },
                    'language': table_translations
                });

            }).fail(function( data ){
                console.log(data.responseText);
            });

        }


    });
    

    // view ticket in new tab
    $(document).on('click', '.wpas-fa-view-ticket', function (e) {

        e.preventDefault();

        var ticket_id = $(this).data('ticket-id');
        var ticket_tab = $('[data-page-id="ticket-page-' + ticket_id + '"]');
 
        if (ticket_tab.length) {
            ticket_tab.click();
            return false;
        }

        // tab
        var tab = ' <li class="wpas-fa-dynamic-tab">';
            tab += '<a class="wpas-fa-close-tab" data-tab-id="' + ticket_id + '" href="#">×</a> ';
            tab += '<a href="#" data-page-id="ticket-page-' + ticket_id + '">';
            tab +=  $(this).data('ticket-title');
            tab += '</a></li> ';
        // tab content
        var content = '<div class="wpas-fa-tab-page" data-page="ticket-page-' + ticket_id + '" id="view-ticket-' + ticket_id + '">';
            content += '<img src="' + ASFA.admin_url + 'images/loading.gif">';
            content += '</div>';

        // append html
        $('#wpas-fa-tabs-menu').append(tab);
        $('#wpas-fa-tab-content').append(content);

        // click on tab, 
        $('[data-page-id="ticket-page-' + ticket_id + '"]').click();

        // get ticket data
        $.post(ASFA.ajax_url, { 
            action: 'view_ticket', 
            id: ticket_id, 
            nonce: ASFA.nonce 
        }).done(function( data ) {

            $('#view-ticket-' + ticket_id).html(data);

            // show editor
            show_editor( ticket_id );

        }).fail(function( data ){
            console.log(data.responseText);
        });

    });


    // close ticket tab
    $(document).on('click', '.wpas-fa-close-tab', function (e) {

        e.preventDefault();

        var tab_id = $(this).data('tab-id');
        var current = $('[data-page-id="ticket-page-' + tab_id + '"]').hasClass('active');

        // remove tab
        $(this).parent().remove();
        // remove tab content
        $('#view-ticket-' + tab_id).remove();
        
        if (current) {
            // show main tab
            $('[data-page-id="open-tickets"]').click();
        }

    });

    // recalc the table width
    $(document).on('click', '#wpas-fa-tabs-menu li a', function () {
        $($.fn.dataTable.tables(true)).DataTable().responsive.recalc();
    });


    // show date on hover
    $(document).on({
        mouseenter: function () {

            var el = $('.wpas-fa-replay-date-display', this);

            el.text( el.data('hover') );
        },
        mouseleave: function () {

            var el = $('.wpas-fa-replay-date-display', this);

            el.text( el.data('default') );

        }
    }, '.wpas-fa-reply-table' );


    // ticket action (open - close)
    $(document).on('click', '.wpas-fa-ticket-action', function (e) {

        e.preventDefault();

        var ticket_id = $(this).data('id');
        var ticket_action = $(this).data('action');

        // show loader
        $('#view-ticket-' + ticket_id).html('<img src="' + ASFA.admin_url + 'images/loading.gif">');

        // get ticket data
        $.post(ASFA.ajax_url, { 
            action: ticket_action, 
            id: ticket_id, 
            nonce: ASFA.nonce,
            dataType : 'json' 
        }).done(function( data ) {

            
            // update ticket label
            $('.wpas-row-ticket-status-' + ticket_id).html(data.label);

            // load view ticket
            $('#view-ticket-' + ticket_id).html(data.html);

            var ticket = $('.wpas-ticket-table-row-' + ticket_id);
            var row = ticket.clone();
  
            // remove style because elements are hidden and not shown when inserted in another table
            $.each(row.find('td'), function(i, v){
                $(this).removeAttr('style');
            });


            // move rows between tables
            switch ( ticket_action ) {

                case 'open_ticket':

                    if ( closed_tickets_table !== false ) {
                        closed_tickets_table.row(ticket).remove().draw();
                        open_tickets_table.row.add(row).draw();
                    } else {
                        // to-do: reload open tickets table via ajax
                        // closed tickets are not loaded so we cannot move row from one table to another
                        window.location.reload();
                    }

                    show_editor( ticket_id );

                    break;

                case 'close_ticket':

                    open_tickets_table.row(ticket).remove().draw();

                    if ( closed_tickets_table !== false ) {
                        closed_tickets_table.row.add(row).draw();
                    }

                    break;
            }


        }).fail(function( data ){
            
            console.log(data.responseText);

        });

    });


    // change ticket status
    $(document).on('change', '.wpas-fa-change-status', function(e){

        e.preventDefault();

        var ticket_id = $(this).data('id');
        var ticket_status = $(this).val();

        // show loader
        $('#view-ticket-' + ticket_id).html('<img src="' + ASFA.admin_url + 'images/loading.gif">');

        // get ticket data
        $.post(ASFA.ajax_url, { 
            action: 'update_ticket_status', 
            id: ticket_id, 
            status: ticket_status,
            nonce: ASFA.nonce,
            dataType: 'json'
        }).done(function( data ) {

            $('#view-ticket-' + ticket_id).html(data.html);
            $('.wpas-row-ticket-status-' + ticket_id).html(data.label);

            show_editor( ticket_id );

        }).fail(function( data ){
            
            console.log(data.responseText);

        });

    });


    // ticket reply
    $(document).on('click', '.wpas-fa-ticket-reply', function (e) {

        e.preventDefault();

        var ticket_id = $(this).data('id');
        var ticket_action = $(this).data('action');
        var ticket_reply = tinymce.get('wpas-fa-reply-editor-' + ticket_id).getContent();

        // check if reply is empty
        if ( ticket_reply == '' ) {

            alert( ASFA.reply_empty );

            tinymce.get('wpas-fa-reply-editor-' + ticket_id).focus();

            return false;
        }

        // show loader
        $('#view-ticket-' + ticket_id).append('<img id="wpas-fa-loader" src="' + ASFA.admin_url + 'images/loading.gif">');

        // remove editor
        $('.wpas-fa-reply-form-' + ticket_id).hide();

        var form      = $(this).parent('.wpas-fa-reply-form')[0];
        var form_data = new FormData(form);

        form_data.append('id', ticket_id);
        form_data.append('action', ticket_action);
        form_data.append('reply', ticket_reply);
        form_data.append('nonce', ASFA.nonce);
     
        // get ticket data
        $.ajax({ 
            method: 'POST',
            url: ASFA.ajax_url,
            data : form_data,
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false
        }).done(function( data ) {

            $('#wpas-fa-loader').remove();

            // check for errors
            if ( data.status === 0 ) {

                // show editor
                $('.wpas-fa-reply-form-' + ticket_id).show();
                alert(data.message);

            } else {

                $('#view-ticket-' + ticket_id).html(data.html);
                $('.wpas-row-ticket-status-' + ticket_id).html(data.label);
    
                if ( ticket_action == 'ticket_reply_close' ) {
    
                    var ticket = $('.wpas-ticket-table-row-' + ticket_id);
                    var row = ticket.clone();
        
                    $.each(row.find('td'), function(i, v){
                        $(this).removeAttr('style');
                    });
        
                    open_tickets_table.row(ticket).remove().draw();

                    if ( closed_tickets_table !== false ) {
                        closed_tickets_table.row.add(row).draw();
                    }
                   
    
                } else {

                    $('.wpas-fa-reply-form-' + ticket_id).show();
                    show_editor( ticket_id );
                }

            }




        }).fail(function( data ){
            
            console.log(data.responseText);

        });
        

    });
    

});