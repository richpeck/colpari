( function( $ ) {
  jQuery( document ).ready( function( $ ) {
    /**
     * Set up date picker for the filters from date. When selected and if the to
     * date is empty it will change the to date 30 days ahead.
     */
    $( "#as_time_tracking_report_date_from" ).datepicker({
      dateFormat: "yy-mm-dd",
      changeMonth: true,
      changeYear: true,
      onSelect: function( dateChosen ) {
        $( "#as_time_tracking_report_date_to" ).datepicker( "option", "minDate", dateChosen );
        var toDateVal = $( "#as_time_tracking_report_date_to" ).val();

        if( toDateVal.length === 0 ) {
          var futureDate = new Date( dateChosen );
          futureDate.setDate( futureDate.getDate() + 30 );
          $( "#as_time_tracking_report_date_to" ).datepicker( "setDate", futureDate );
        }
      }
    });

    /**
     * Set up date picker for the filters to date. When selected and if the from
     * date is empty it will change the from date 30 days behind.
     */
    $( "#as_time_tracking_report_date_to" ).datepicker({
      dateFormat: "yy-mm-dd",
      changeMonth: true,
      changeYear: true,
      onSelect: function( dateChosen ) {
        var fromDateVal = $( "#as_time_tracking_report_date_from" ).val();

        if( fromDateVal.length === 0 ) {
          var pastDate = new Date( dateChosen );
          pastDate.setDate( pastDate.getDate() - 30 );
          $( "#as_time_tracking_report_date_from" ).datepicker( "setDate", pastDate );
        }
      }
    });

    /** Set the select2 inputs first. A width problem occurs if we do this after
     *  the hide() calls. */
    $( "#as_time_tracking_selected_agents" ).select2({
      dropdownAutoWidth : true,
      width: '200px',
    });

    $( "#as_time_tracking_selected_tickets" ).select2({
      dropdownAutoWidth : true,
      width: '200px',
    });

    /** Use core plugin's logic to get this functionality. Couldn't call the core
     *  plugin's function to do this as it didn't allow for multiple="multiple" on
     *  the select box which select2 needs for a multiselect */
    $( "#as_time_tracking_selected_customers" ).select2({
      ajax: {
        url: ajax_object_reporting.ajax_url,
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
      width: '200px ',
      dropdownAutoWidth : true
    });
  });
})( jQuery );
