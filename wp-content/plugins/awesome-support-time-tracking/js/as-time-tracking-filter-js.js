( function( $ ) {
  jQuery( document ).ready( function( $ ) {
    //Set up select2 for filter fields
    $( "#as_time_tracking_filter_customers" ).select2({
      ajax: {
        url: ajax_object.ajax_url,
        dataType: 'json',
        type: 'POST',
        delay: 250,
        data: function ( params ) {
          return {
            action: 'wpas_get_users',
            cap: 'create_ticket',
            q: params.term
          };
        },
        processResults: function ( data, params ) {
          return {
            results: $.map( data, function ( obj ) {
              return {
                id: obj.user_id,
                text: obj.user_name
              };
            })
          };
        }
      },
      minimumInputLength: 3,
      placeholder: "All Clients",
      width: '300px',
      dropdownAutoWidth : true
    });

    $( "#as_time_tracking_filter_agents" ).select2({
      ajax: {
        url: ajax_object.ajax_url,
        dataType: 'json',
        type: 'POST',
        delay: 250,
        data: function ( params ) {
          return {
            action: 'wpas_get_users',
            cap: 'edit_ticket',
            q: params.term
          };
        },
        processResults: function ( data, params ) {
          return {
            results: $.map( data, function ( obj ) {
              return {
                id: obj.user_id,
                text: obj.user_name
              };
            })
          };
        }
      },
      minimumInputLength: 3,
      placeholder: "All Agents",
      width: '300px',
      dropdownAutoWidth : true
    });

    $( ".select2.select2-container" ).css( "margin-right", "5px" );
  });
})( jQuery );
