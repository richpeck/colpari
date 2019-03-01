( function( $ ) {
  jQuery( document ).ready( function( $ ) {
    /**
     * Take filter fields, match tracked times, create CSV file and update tracked
     * times invoiced fields.
     * @param   {object} e - The event from the click.
     * @return  array
     */
    $( "#as_time_tracking_invoice_filter_submit" ).click( function( e ) {
      var dateRange = asTimeTrackingCalculateDateRange();
      var agentVals = asTimeTrackingCalculateAgentValues();
      var customerVals = asTimeTrackingCalculateCustomerValues();
      var allClosedVal = $( "#as_time_tracking_invoice_all_closed_tickets option:selected" ).text().toLowerCase();
      var ticketVals = asTimeTrackingCalculateTicketValues();
      var csvCreated = false;

      var data = {
        "action": 'invoice_csv_action',
        "from_date": dateRange[0],
        "to_date": dateRange[1],
        "all_agent_check": agentVals[0],
        "selected_agent_check": agentVals[1],
        "selected_agent_val": agentVals[2],
        "all_customer_check": customerVals[0],
        "selected_customer_check": customerVals[1],
        "selected_customer_val": customerVals[2],
        "all_closed_val": allClosedVal,
        "all_ticket_check": ticketVals[0],
        "selected_ticket_check": ticketVals[1],
        "selected_ticket_val": ticketVals[2],
        "security": ajax_object.ajax_nonce
      };

      /** global: ajax_object */
      $.ajax({
        type: 'POST',
        url: ajax_object.ajax_url,
        data: data,
        success: function( response ) {
          $( "#as_time_tracking_invoicing_container .as_time_tracking_message_container" ).empty();

          if( Boolean( response ) === true ) {
            asTimeTrackingDisplayCsvCreated( true );
            csvCreated = true;
          } else {
            $( "#as_time_tracking_invoicing_container .as_time_tracking_filter_list" ).fadeIn();
            $( "#as_time_tracking_invoicing_container .as_time_tracking_csv_created_container" ).hide();
            $( "#as_time_tracking_invoicing_container .as_time_tracking_message_container" ).append( "<p>" + ajax_object.csv_fail + "</p>" );
          }
        },
        dataType: '',
        async:false
      });

      //Display the preview after the CSV file has been created
      if( csvCreated === true ) {
        var data = {
          "action": 'csv_preview_action',
          "security": ajax_object.ajax_nonce
        };

        /** global: ajax_object */
        $.post( ajax_object.ajax_url, data, function( response ) {
          if( Object.keys( response.data ).length > 0 ) {
              $( ".as_time_tracking_csv_created_container .as_time_tracking_csv_preview" ).empty();
                var html = asTimeTrackingdrawCsvPreview( response.data );
              $( ".as_time_tracking_csv_created_container .as_time_tracking_csv_preview" ).append( html );
          }
        });
      }

      e.preventDefault();
    });

    /**
     * Prepares the html for the preview of the invoice csv and returns it.
     * @param   {array} data - The data to output.
     * @return  array
     */
    function asTimeTrackingdrawCsvPreview( data ) {
      html = "<h2 class='invoicing-preview-title'>CSV file preview:</h2>";
      html += "<table class='as-time-tracking-invoicing-preview'>";

      for( var i = 0; i < data.length; i++ ) {
        if( data[i] !== false ) {
          if( i === 0 ) {
            html += "<tr><th>" + data[i][0] + "</th>";
            html += "<th>" + data[i][1] + "</th>";
            html += "<th>" + data[i][2] + "</th>";
            html += "<th>" + data[i][3] + "</th>";
            html += "<th>" + data[i][4] + "</th>";
            html += "<th>" + data[i][5] + "</th>";
            html += "<th>" + data[i][6] + "</th>";
            html += "<th>" + data[i][7] + "</th></tr>";
          } else {
            html += "<tr><td>" + data[i][0] + "</td>";
            html += "<td>" + data[i][1] + "</td>";
            html += "<td>" + data[i][2] + "</td>";
            html += "<td>" + data[i][3] + "</td>";
            html += "<td>" + data[i][4] + "</td>";
            html += "<td>" + data[i][5] + "</td>";
            html += "<td>" + data[i][6] + "</td>";
            html += "<td>" + data[i][7] + "</td></tr>";
          }
        }
      }

      html += "</table>";

      return html;
    }

    //Allows a user to download the csv file if the button is clicked.
    $( "#as_time_tracking_download_csv_btn" ).click( function( e ) {
      //We need to do some checks in case the CSV file doesn't exist
      e.preventDefault();

      var url = $( this ).attr( "href" );
      var data = {
        "action": 'csv_file_exists',
        "security": ajax_object.ajax_nonce
      };

      /** global: ajax_object */
      $.post( ajax_object.ajax_url, data, function( response ) {
        //If download exists proceed to download url
        if( Boolean( response ) === true ) {
          window.location.href = url;
        }
      });
    });

    //Approves the invoice csv when the button is clicked.
    $( "#as_time_tracking_invoice_approve_btn" ).click( function( e ) {
      var file_exists = false;
      var data = {
        "action": 'csv_file_exists',
        "security": ajax_object.ajax_nonce
      };

      //Runs ajax as we don't want it to run asyncronously
      $.ajax({
        type: 'POST',
        url: ajax_object.ajax_url,
        data: data,
        success: function( response ) {
          //If no file exits cancel action or else allow approve functionality
          if( Boolean( response ) === true ) {
            file_exists = true;
          } else {
            e.preventDefault();
          }
        },
        dataType: '',
        async:false
      });

      if( file_exists === true ) {
        var data = {
          "action": 'csv_file_approve_action',
          "security": ajax_object.ajax_nonce
        };

        /** global: ajax_object */
        $.post( ajax_object.ajax_url, data, function( response ) {
          $( "#as_time_tracking_invoicing_container .as_time_tracking_message_container" ).empty();

          if( Boolean( response ) === true ) {
            $( "#as_time_tracking_invoicing_container .as_time_tracking_filter_list" ).fadeIn();
            $( "#as_time_tracking_invoicing_container .as_time_tracking_csv_created_container" ).hide();
            $( "#as_time_tracking_invoicing_container .as_time_tracking_message_container" ).append( "<p>" + ajax_object.csv_approve_success + "</p>" );
          } else {
            $( "#as_time_tracking_invoicing_container .as_time_tracking_message_container" ).append( "<p>" + ajax_object.csv_approve_fail + "</p>" );
          }
        });
      }

      e.preventDefault();
    });

    //Dissaproves the current invoice when the disapprove button is clicked.
    $( "#as_time_tracking_invoice_disapprove_btn" ).click( function( e ) {
      var file_exists = false;
      var data = {
        "action": 'csv_file_exists',
        "security": ajax_object.ajax_nonce
      };

      //Runs ajax as we don't want it to run asyncronously
      $.ajax({
        type: 'POST',
        url: ajax_object.ajax_url,
        data: data,
        success: function( response ) {
          //If no file exits cancel action or else allow approve functionality
          if( Boolean( response ) === true ) {
            file_exists = true;
          } else {
            e.preventDefault();
          }
        },
        dataType: '',
        async:false
      });

      if( file_exists === true ) {
        var data = {
          "action": 'csv_file_disapprove_action',
          "security": ajax_object.ajax_nonce
        };

        /** global: ajax_object */
        $.post( ajax_object.ajax_url, data, function( response ) {
          $( "#as_time_tracking_invoicing_container .as_time_tracking_message_container" ).empty();

          if( Boolean( response ) === true ) {
            $( "#as_time_tracking_invoicing_container .as_time_tracking_filter_list" ).fadeIn();
            $( "#as_time_tracking_invoicing_container .as_time_tracking_csv_created_container" ).hide();
            $( "#as_time_tracking_invoicing_container .as_time_tracking_message_container" ).append( "<p>" + ajax_object.csv_delete_success + "</p>" );
          } else {
            $( "#as_time_tracking_invoicing_container .as_time_tracking_message_container" ).append( "<p>" + ajax_object.csv_delete_fail + "</p>" );
          }
        });
      }

      e.preventDefault();
    });


    /**
     * Sets an element to read only.
     * @param   {boolean} justCreated - The indicator if the csv file was created.
     * @return  void
     */
    function asTimeTrackingDisplayCsvCreated( justCreated ) {
      var initialText = ajax_object.invoice_success;

      $( "#as_time_tracking_invoicing_container .as_time_tracking_filter_list" ).hide();
      $( "#as_time_tracking_invoicing_container .as_time_tracking_csv_created_container" ).fadeIn();
      $( "#as_time_tracking_invoicing_container .as_time_tracking_csv_created_container .initial_text" ).text( initialText );
    }

    /**
     * Gets the date values from the filter.
     * @return array
     */
    function asTimeTrackingCalculateDateRange() {
      var date = [];
      var dateFrom = $( "#as_time_tracking_invoice_date_from" ).val();
      var dateTo = $( "#as_time_tracking_invoice_date_to" ).val();

      /** If any date field is empty we just set the start date to the current date
       *  and the end date 30 days ahead. */
      if( dateFrom.length === 0 || dateTo.length === 0 ) {
        var currentDate = new Date();
        var year = currentDate.getFullYear();
        var month = currentDate.getMonth() + 1;
        month = as_time_tracking_invoicing_prepend_zero( month );
        var day = currentDate.getDate();
        day = as_time_tracking_invoicing_prepend_zero( day );
        var formattedDate = year + "-" + month + "-" + day;
        var futureDate = new Date();
        futureDate.setDate( futureDate.getDate() + 30 );
        var futureYear = futureDate.getFullYear();
        var futureMonth = futureDate.getMonth() + 1;
        futureMonth = as_time_tracking_invoicing_prepend_zero( futureMonth );
        var futureDay = futureDate.getDate();
        futureDay = as_time_tracking_invoicing_prepend_zero( futureDay );
        var formattedFutureDate = futureYear + "-" + futureMonth + "-" + futureDay;

        date[0] = formattedDate;
        date[1] = formattedFutureDate;
      } else {
        date[0] = dateFrom;
        date[1] = dateTo;
      }

      return date;
    }

    //Reset values for the ticket select when the ticket status field is changed
    $( "#as_time_tracking_invoice_all_closed_tickets" ).change( function() {
      var ticketStatus = $( this ).val();
      var data = {
            'action': 'as_time_tracking_invoice_filter_status_change',
            'status': ticketStatus,
            'security': ajax_object.ajax_nonce
          };

      /** global: ajax_object */
      $.post( ajax_object.ajax_url, data, function( response ) {
        $( "#as_time_tracking_invoice_selected_tickets" ).empty();
        $( "#as_time_tracking_invoice_selected_tickets" ).select2().val( null );

        if( Object.keys( response.data ).length > 0 ) {
          for( var key in response.data ) {
            $( "#as_time_tracking_invoice_selected_tickets" ).append( "<option value=" + response.data[key].ID + ">" + response.data[key].post_title + "</option>" );
          }

          //Reset the select2 width otherwise the input width goes to small when we reset the values
          $( "#as_time_tracking_invoice_selected_tickets" ).select2({
            dropdownAutoWidth : true,
            width: '200px',
          });
        }
      });

      if( ticketStatus === "closed" ) {
        $( "#as_time_tracking_invoice_all_tickets_label" ).text( ajax_object.all_closed_tickets );
        $( "#as_time_tracking_invoice_selected_tickets_label" ).text( ajax_object.selected_closed_tickets );
      } else {
        $( "#as_time_tracking_invoice_all_tickets_label" ).text( ajax_object.all_tickets );
        $( "#as_time_tracking_invoice_selected_tickets_label" ).text( ajax_object.selected_tickets );
      }
    });

    /**
     * Gets the agent values from the filter.
     * @return array
     */
    function asTimeTrackingCalculateAgentValues() {
      var agentValues = as_time_tracking_invoicing_calculate_filter_select_values( "#as_time_tracking_invoice_all_agents", "#as_time_tracking_invoice_selected_agents" );
      //If the user selected some values we need to convert the array to json string
      agentValues[2] = as_time_tracking_invoicing_string_array( agentValues[2] ); //Index 2 is the selected values set by the user in the select2 input

      return [ agentValues[0], agentValues[1], agentValues[2] ];
    }

    /**
     * Gets the customer values from the filter.
     * @return array
     */
    function asTimeTrackingCalculateCustomerValues() {
      var customerValues = as_time_tracking_invoicing_calculate_filter_select_values( "#as_time_tracking_invoice_all_customers", "#as_time_tracking_invoice_selected_customers" );
      //If the user selected some values we need to convert the array to json string
      customerValues[2] = as_time_tracking_invoicing_string_array( customerValues[2] ); //Index 2 is the selected values set by the user in the select2 input

      return [ customerValues[0], customerValues[1], customerValues[2] ];
    }

    /**
     * Gets the ticket values from the filter.
     * @return array
     */
    function asTimeTrackingCalculateTicketValues() {
      var ticketValues = as_time_tracking_invoicing_calculate_filter_select_values( "#as_time_tracking_invoice_all_tickets", "#as_time_tracking_invoice_selected_tickets" );
      //If the user selected some values we need to convert the array to json string
      ticketValues[2] = as_time_tracking_invoicing_string_array( ticketValues[2] ); //Index 2 is the selected values set by the user in the select2 input

      return [ ticketValues[0], ticketValues[1], ticketValues[2] ];
    }

    //Show/hide selected agents based on when the all agents checkbox changes
    $( "#as_time_tracking_invoice_all_agents" ).change( function() {
      as_time_tracking_invoicing_filter_change( "#as_time_tracking_invoice_selected_agents_wrapper", this );
    });

    //Show/hide selected customers based on when the all customers checkbox changes
    $( "#as_time_tracking_invoice_all_customers" ).change( function() {
      as_time_tracking_invoicing_filter_change( "#as_time_tracking_invoice_selected_customers_wrapper", this );
    });

    //Show/hide selected tickets based on when the all ticket checkbox changes
    $( "#as_time_tracking_invoice_all_tickets" ).change( function() {
      as_time_tracking_invoicing_filter_change( "#as_time_tracking_invoice_selected_tickets_wrapper", this );
    });

    /**
     * Used as a helper function. Hides/shows elements.
     * @param   {string} elem_wrapper_id  - The CSS ID of the elemnt.
     * @param   {object} this_val         - The "this" object of the element.
     * @return  void
     */
    function as_time_tracking_invoicing_filter_change( elem_wrapper_id, this_val ) {
      if( this_val.checked ) {
        $( elem_wrapper_id ).hide();
      } else {
        $( elem_wrapper_id ).show();
      }
    }

    /**
     * Used as a helper function. Gets JSON format if val is an array.
     * @param   {array} val   - The values to check
     * @return  string
     */
    function as_time_tracking_invoicing_string_array( val ) {
      var return_val = val;

      if( return_val instanceof Array ) {
        return_val = JSON.stringify( return_val );
      } else {
        return_val = "";
      }

      return return_val;
    }

    /**
     * Prepends a "0" to single digit values.
     * @param   {array} val   - The value to check
     * @return  string
     */
    function as_time_tracking_invoicing_prepend_zero( val ) {
      var return_val = val;

      if( return_val.toString().length === 1 ) {
        return_val = "0" + return_val;
      }

      return return_val;
    }

    /**
     * Get filter select input type values.
     * @param   {string} all_id   - The "all" CSS ID we want to get values from
     * @param   {string} all_id   - The "selected" CSS ID we want to get values from
     * @return  array
     */
    function as_time_tracking_invoicing_calculate_filter_select_values( all_id, selected_id ) {
      var returnArr = [];
      //Index 0 is if the all value checkbox was selected, index 1 was if it was unselected, index 2 is the selected values typed by the user
      returnArr[0] = "";
      returnArr[1] = "";
      returnArr[2] = null; //only set to null as empty vals returned from select2 will return null
      var allValues = $( all_id );

      if( $( allValues ).is( ":checked" ) ) {
        returnArr[0] = true;
        returnArr[1] = false;
      } else {
        returnArr[0] = false;
        returnArr[1] = true;
        returnArr[2] = $( selected_id ).val();
      }

      return returnArr;
    }

    //On load hide selected fields and set "all" checboxes on fields
    $( "#as_time_tracking_invoice_all_agents" ).prop( "checked", true);
    $( "#as_time_tracking_invoice_selected_agents_wrapper" ).hide();

    $( "#as_time_tracking_invoice_all_customers" ).prop( "checked", true);
    $( "#as_time_tracking_invoice_selected_customers_wrapper" ).hide();

    $( "#as_time_tracking_invoice_all_tickets" ).prop( "checked", true);
    $( "#as_time_tracking_invoice_selected_tickets_wrapper" ).hide();

    //Hide initial filter options which aren't needed
    $( "#as_time_tracking_invoice_selected_agents_wrapper" ).hide();
    $( "#as_time_tracking_invoice_selected_customers_wrapper" ).hide();
    $( "#as_time_tracking_invoice_selected_tickets_wrapper" ).hide();

    //When page loads determine which screen to show based on if CSV file has been created before
    var data = {
      "action": 'csv_file_exists',
      "security": ajax_object.ajax_nonce
    };

    /** global: ajax_object */
    $.post( ajax_object.ajax_url, data, function( response ) {
      response = Number( response ); //Used as the returned "0" was a string which casts to true on Boolean()

      if( Boolean( response ) === true ) {
        $( "#as_time_tracking_invoicing_container .as_time_tracking_csv_created_container .initial_text" ).text( ajax_object.invoice_default );
        $( "#as_time_tracking_invoicing_container .as_time_tracking_filter_list" ).hide();
        $( "#as_time_tracking_invoicing_container .as_time_tracking_csv_created_container" ).show();

        var data = {
          "action": 'csv_preview_action',
          'security': ajax_object.ajax_nonce
        };

        /** global: ajax_object */
        $.post( ajax_object.ajax_url, data, function( response ) {
          if( Object.keys( response.data ).length > 0 ) {
              $( ".as_time_tracking_csv_created_container .as_time_tracking_csv_preview" ).empty();
                var html = asTimeTrackingdrawCsvPreview( response.data );
              $( ".as_time_tracking_csv_created_container .as_time_tracking_csv_preview" ).append( html );
          }
        });
      } else {
        $( "#as_time_tracking_invoicing_container .as_time_tracking_filter_list" ).show();
        $( "#as_time_tracking_invoicing_container .as_time_tracking_csv_created_container" ).hide();
      }
    });
  });
})( jQuery );
