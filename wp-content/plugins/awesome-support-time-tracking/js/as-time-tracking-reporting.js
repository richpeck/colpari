( function( $ ) {
  jQuery( document ).ready( function( $ ) {
    //Set title text vars
    var agentTitle = ajax_object_reporting.agent_title;
    var clientTitle = ajax_object_reporting.client_title;
    var ticketTitle = ajax_object_reporting.ticket_title;
    var invoiceTitle = ajax_object_reporting.invoice_title;

    /**
     * Helper function for asTimeTrackingGenerateReport. Returns the report type and changes a hidden field value.
     * @param {string} reportType - The type of report to generate
     * @return array
     */
    function determineTimeTrackingReportTypeTitle( reportType ) {
      var actionTitle = [];

      switch( reportType ) {
        case "agent":
        actionTitle[0] = "agent_report_full_action";
        actionTitle[1] = agentTitle;
        actionTitle[2] = ajax_object_reporting.agent_heading;
        $( "#as_time_tracking_report_type_hidden" ).val( "agent" );
        break;

        case "client":
        actionTitle[0] = "client_report_full_action";
        actionTitle[1] = clientTitle;
        actionTitle[2] = ajax_object_reporting.client_ticket_heading;
        $( "#as_time_tracking_report_type_hidden" ).val( "client" );
        break;

        case "ticket":
        actionTitle[0] = "ticket_report_full_action";
        actionTitle[1] = ticketTitle;
        actionTitle[2] = ajax_object_reporting.client_ticket_heading;
        $( "#as_time_tracking_report_type_hidden" ).val( "ticket" );
        break;

        case "invoice":
        actionTitle[0] = "invoice_report_full_action";
        actionTitle[1] = invoiceTitle;
        actionTitle[2] = ajax_object_reporting.client_invoice_heading;
        $( "#as_time_tracking_report_type_hidden" ).val( "invoice" );
        break;

        default:
        actionTitle[0] = "agent_report_full_action";
        actionTitle[1] = agentTitle;
        $( "#as_time_tracking_report_type_hidden" ).val( "agent" );
      }

      return actionTitle;
    }

    /**
     * Generates information for creating a report. Uses a helper function to output
     * the report content if the filter is valid.
     * @param {string} reportType - The type of report to generate
     * @return void
     */
    function asTimeTrackingGenerateReport( reportType ) {
      //Get filter values
      var fromToDates = asTimeTrackingCalculateDateRange();
      var agentVals = asTimeTrackingCalculateAgentValues();
      var customerVals = asTimeTrackingCalculateCustomerValues();
      var allClosedVal = $( "#as_time_tracking_all_closed_tickets option:selected" ).text().toLowerCase();
      var ticketVals = asTimeTrackingCalculateTicketValues();

      //Update the report type and set the action so we know which report logic to run in ajax
      actionTitle = determineTimeTrackingReportTypeTitle( reportType );

      var data = {
        "action": actionTitle[0],
        "from_date": fromToDates[0],
        "to_date": fromToDates[1],
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
        "security": ajax_object_reporting.ajax_nonce
      };

      /** global: ajax_object_reporting */
      $.post( ajax_object_reporting.ajax_url, data, function( response ) {
        //If the ajax returns content, output the report or output an error message
        if( Object.keys( response.data[0] ).length > 0 ) {
          var reportType = $( "#as_time_tracking_report_type_hidden" ).val();
          var title = actionTitle[1];
          var heading = actionTitle[2];

          $( "#as_time_tracking_report_container .as_time_tracking_report_content" ).empty();

          asTimeTrackingDrawReport( reportType, response.data[0], title, heading, response.data[1] );
        } else {
          $( "#as_time_tracking_report_container .as_time_tracking_report_content" ).empty();
          var emptyText = ajax_object_reporting.no_times;
          if(response.data[1] === true) {
          	emptyText += ajax_object_reporting.no_times_missing_client;
          }
          $( "#as_time_tracking_report_container .as_time_tracking_report_content" ).append( "<p class='no_times_found'>" + emptyText + "</p>" );
        }
      });
    }

    /**
     * Helper function for asTimeTrackingDrawReport(). Returns the column number based on the report type.
     * @param {string} reportType  - The type of report to generate
     * @return integer
     */
    function determineTimeTableColLength( reportType ) {
      if( reportType === "agent" ) {
        return 6;
      } else {
        return 7;
      }
    }

    /**
     * Helper function for asTimeTrackingDrawReport(). Returns the html for agent, client and invoice headings
     * @param {integer} colLength     - The column length
     * @param {string}  tableHeading  - The heading for the report
     * @param {string}  reportType    - The type of report to generate
     * @param {string}  html          - The current html for the report
     * @return string
     */
    function asTimeTrackingDrawReportAgentClientInvoiceHeadings( colLength, tableHeading, reportType, html ) {
      html += "<tr><th colspan='" + colLength + "'>" + tableHeading + "</th></tr>";

      if( reportType == 'agent' ) {
        html += "<tr class='headings'><td><strong>" + ajax_object_reporting.entry_date + "</strong></td>";
      } else {
        html += "<tr class='headings'><td><strong>" + ajax_object_reporting.ticket_agent + "</strong></td>";
        html += "<td><strong>" + ajax_object_reporting.entry_date + "</strong></td>";
      }

      html += "<td><strong>" + ajax_object_reporting.start_date + "</strong></td>";
      html += "<td><strong>" + ajax_object_reporting.end_date + "</strong></td>";
      html += "<td><strong>" + ajax_object_reporting.ticket_id + "</strong></td>";
      html += "<td><strong>" + ajax_object_reporting.ticket_reply_id + "</strong></td>";
      html += "<td><strong>" + ajax_object_reporting.time_recorded + "</strong></td></tr>";

      return html;
    }

    /**
     * Helper function for asTimeTrackingDrawReport(). Returns the html for the ticket headings
     * @param {integer} colLength     - The column length
     * @param {string}  tableHeading  - The heading for the report
     * @param {string}  reportType    - The type of report to generate
     * @param {string}  html          - The current html for the report
     * @return string
     */
    function asTimeTrackingDrawReportTicketHeadings( colLength, tableHeading, reportType, html ) {
      html += "<tr><th colspan='7'>" + tableHeading + "</th></tr>";
      html += "<tr class='headings'><td><strong>" + ajax_object_reporting.ticket_id + "</strong></td>";
      html += "<td><strong>" + ajax_object_reporting.ticket_agent + "</strong></td>";
      html += "<td><strong>" + ajax_object_reporting.entry_date + "</strong></td>";
      html += "<td><strong>" + ajax_object_reporting.start_date + "</strong></td>";
      html += "<td><strong>" + ajax_object_reporting.end_date + "</strong></td>";
      html += "<td><strong>" + ajax_object_reporting.ticket_reply_id + "</strong></td>";
      html += "<td><strong>" + ajax_object_reporting.time_recorded + "</strong></td></tr>";
      return html;
    }

    /**
     * Helper function for asTimeTrackingGenerateReport(). Outputs the report content and title.
     * @param {string} reportType     - The type of report to generate
     * @param {Object} reportData     - The returned report content from ajax
     * @param {string} reportTitle    - The report title
     * @param {string} reportHeading  - The heading of the report
     * @return void
     */
    function asTimeTrackingDrawReport( reportType, reportData, reportTitle, reportHeading, missingClient ) {
      var tableHeading;
      var html = "<h2>" + reportTitle + "</h2>";
      html += "<table class='as-time-tracking-report-table " + reportType + "'>";
      html += "<tr class='top-tr'><td>&nbsp;</td></tr>";
      var totalTime = 0;

      for( var key in reportData ) {
        tableHeading = reportHeading + key;
        /** Agent and client reports have the same number of fields. The ajax
         *  data key will have the correct table heading to output */
        var colLength = determineTimeTableColLength( reportType );
        var htmlTotal;

        if( reportType !== "ticket" ) {
          html = asTimeTrackingDrawReportAgentClientInvoiceHeadings( colLength, tableHeading, reportType, html );
          htmlTotal = calculateAgentClientInvoiceDataTotals( reportType, reportData, key );
          html += htmlTotal[0];
          totalTime = htmlTotal[1];

          //Calculate total time and print at bottom of an entry
          var hours = Math.floor( totalTime / 60);
          var minutes = totalTime % 60;
          html += "<tr><td colspan='3' class='total-time'><strong>" + ajax_object_reporting.total_time + " " + hours + " " + ajax_object_reporting.hours + " " + minutes + " " + ajax_object_reporting.minutes + "</strong></td></tr>";
          html += "<tr class='under-total-time'><td>&nbsp;</td></tr>";
          totalTime = 0;
        } else  { //Is the ticket report
          //Outputs the ticket report information
          html = asTimeTrackingDrawReportTicketHeadings( colLength, tableHeading, reportType, html );
          htmlTotal = calculateTicketDataTotals( reportType, reportData, key );
          html += htmlTotal[0];
          totalTime = htmlTotal[1];

          //Calculate the total time and output it at the bottom of an entry
          var hours = Math.floor( totalTime / 60);
          var minutes = totalTime % 60;
          html += "<tr><td colspan='3' class='total-time'><strong>" + ajax_object_reporting.total_time + hours + " " + ajax_object_reporting.hours + " " + minutes + " " + ajax_object_reporting.minutes + "</strong></td></tr>";
          html += "<tr class='under-total-time'><td>&nbsp;</td></tr>";
          totalTime = 0;
        }
      }

      if( missingClient === true && (reportType === "client" || reportType === "invoice") ) {
      	html += "<tr><td colspan='7'><strong>" + ajax_object_reporting.missing_client + "</strong></td></tr>";
      }

      html += "</table>";
      $( "#as_time_tracking_report_container .as_time_tracking_report_content" ).append( html );
    }

    /**
     * Helper function for asTimeTrackingDrawReport(). Outputs the report content and title.
     * @param {string} reportType  - The type of report to generate
     * @param {Object} reportData  - The returned report content from ajax
     * @param {string} key         - The key for the report data
     * @return array
     */
    function calculateAgentClientInvoiceDataTotals( reportType, reportData, key ) {
      var htmlTotal = [];
      var timeTotal = 0;
      htmlTotal[0] = ""; //Removes undefined showing

      for( var i = 0; i < reportData[key].length; i++ ) {
        htmlTotal[0] += "<tr>";

        if( reportType == 'client' || reportType === 'invoice' ) {
          htmlTotal[0] += "<td>" + reportData[key][i].agent_name + "</td>";
        }

        htmlTotal[0] += "<td>" + reportData[key][i].entry_date + "</td>";
        htmlTotal[0] += "<td>" + reportData[key][i].start_date + "</td>";
        htmlTotal[0] += "<td>" + reportData[key][i].end_date + "</td>";
        htmlTotal[0] += "<td>" + reportData[key][i].ticket_id + "</td>";
        htmlTotal[0] += "<td>" + reportData[key][i].ticket_reply + "</td>";
        htmlTotal[0] += "<td>" + reportData[key][i].individual_time + "</td>";
        htmlTotal[0] += "</tr>";
        timeTotal += Number( reportData[key][i].individual_time );
      }

      htmlTotal[1] = timeTotal;

      return htmlTotal;
    }

    /**
     * Helper function for asTimeTrackingDrawReport(). Outputs the report content and title for the ticket report.
     * @param {string} reportType  - The type of report to generate
     * @param {Object} reportData  - The returned report content from ajax
     * @param {string} key         - The key for the report data
     * @return array
     */
    function calculateTicketDataTotals( reportType, reportData, key ) {
      var htmlTotal = [];
      var timeTotal = 0;
      htmlTotal[0] = ""; //Removes undefined showing

      for( var i = 0; i < reportData[key].length; i++ ) {
        htmlTotal[0] += "<tr>";
        htmlTotal[0] += "<td>" + reportData[key][i].ticket_id + "</td>";
        htmlTotal[0] += "<td>" + reportData[key][i].agent_name + "</td>";
        htmlTotal[0] += "<td>" + reportData[key][i].entry_date + "</td>";
        htmlTotal[0] += "<td>" + reportData[key][i].start_date + "</td>";
        htmlTotal[0] += "<td>" + reportData[key][i].end_date + "</td>";
        htmlTotal[0] += "<td>" + reportData[key][i].ticket_reply + "</td>";
        htmlTotal[0] += "<td>" + reportData[key][i].individual_time + "</td>";
        htmlTotal[0] += "</tr>";
        timeTotal += Number( reportData[key][i].individual_time );
      }

      htmlTotal[1] = timeTotal;

      return htmlTotal;
    }

    /**
     * Gets the customer values from the filter.
     * @return array
     */
    function asTimeTrackingCalculateCustomerValues() {
      var customerValues = as_time_tracking_reporting_calculate_filter_select_values( "#as_time_tracking_all_customers", "#as_time_tracking_selected_customers" );
      //If the user selected some values we need to convert the array to json string. Index 2 holds the selected customers.
      customerValues[2] = as_time_tracking_reporting_string_array( customerValues[2] ); //Index 2 is the selected values set by the user in the select2 input

      return [ customerValues[0], customerValues[1], customerValues[2] ];
    }

    /**
     * Gets the ticket values from the filter.
     * @return array
     */
    function asTimeTrackingCalculateTicketValues() {
      var ticketValues = as_time_tracking_reporting_calculate_filter_select_values( "#as_time_tracking_all_tickets", "#as_time_tracking_selected_tickets" );
      //If the user selected some values we need to convert the array to json string
      ticketValues[2] = as_time_tracking_reporting_string_array( ticketValues[2] ); //Index 2 is the selected values set by the user in the select2 input

      return [ ticketValues[0], ticketValues[1], ticketValues[2] ];
    }

    /**
     * Gets the agent values from the filter.
     * @return array
     */
    function asTimeTrackingCalculateAgentValues() {
      var agentValues = as_time_tracking_reporting_calculate_filter_select_values( "#as_time_tracking_all_agents", "#as_time_tracking_selected_agents" );
      //If the user selected some values we need to convert the array to json string
      agentValues[2] = as_time_tracking_reporting_string_array( agentValues[2] ); //Index 2 is the selected values set by the user in the select2 input

      return [ agentValues[0], agentValues[1], agentValues[2] ];
    }

    /**
     * Determines filter values and returns them.
     * @param   {string} all_id       - The CSS ID of the "all" element.
     * @param   {string} selected_id  - The CSS ID of the "selected" element.
     * @return array
     */
    function as_time_tracking_reporting_calculate_filter_select_values( all_id, selected_id ) {
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

    /**
     * Determines filter values and returns them.
     * @param   {array} val       - Converts array data to JSON string
     * @return  string
     */
    function as_time_tracking_reporting_string_array( val ) {
      var return_val = val;

      if( return_val instanceof Array ) {
        return_val = JSON.stringify( return_val );
      } else {
        return_val = "";
      }

      return return_val;
    }

    /**
     * Helper function for asTimeTrackingDrawReport(). Outputs the report content and title for the ticket report.
     * @param {string} valToCheck  - The value to check
     * @return string
     */
    function asTimeTrackingPrependZeroDateTime( valToCheck ) {
      var finalVal = "";
      if( valToCheck.toString().length === 1 ) {
        finalVal = "0" + valToCheck;
      } else {
        finalVal = valToCheck;
      }

      return finalVal;
    }

    /**
     * Gets the date values from the filter.
     * @return array
     */
    function asTimeTrackingCalculateDateRange() {
      var date = [];
      var dateFrom = $( "#as_time_tracking_report_date_from" ).val();
      var dateTo = $( "#as_time_tracking_report_date_to" ).val();

      /** If any date field is empty we just set the start date to the current date
       *  and the end date 30 days ahead. */
      if( dateFrom.length === 0 || dateTo.length === 0 ) {
        var currentDate = new Date();
        var year = currentDate.getFullYear();
        var month = currentDate.getMonth() + 1;
        month = asTimeTrackingPrependZeroDateTime( month );
        var day = currentDate.getDate();
        day = asTimeTrackingPrependZeroDateTime( day );
        var formattedDate = year + "-" + month + "-" + day;
        var futureDate = new Date();
        futureDate.setDate( futureDate.getDate() + 30 );
        var futureYear = futureDate.getFullYear();
        var futureMonth = futureDate.getMonth() + 1;
        futureMonth = asTimeTrackingPrependZeroDateTime( futureMonth );
        var futureDay = futureDate.getDate();
        futureDay = asTimeTrackingPrependZeroDateTime( futureDay );
        var formattedFutureDate = futureYear + "-" + futureMonth + "-" + futureDay;
        date[0] = formattedDate;
        date[1] = formattedFutureDate;
      } else {
        date[0] = dateFrom;
        date[1] = dateTo;
      }
      return date;
    }

    /**
     * Defaults the field values in the filter.
     * @return void
     */
    function asTimeTrackingDefaultReportFilter() {
      $( "#as_time_tracking_report_date_from" ).val( "" );
      $( "#as_time_tracking_report_date_to" ).val( "" );

      $( "#as_time_tracking_all_agents" ).prop( "checked", true);
      $( "#as_time_tracking_selected_agents_wrapper" ).hide();
      $( "#as_time_tracking_selected_tickets" ).select2().val( null );

      $( "#as_time_tracking_all_customers" ).prop( "checked", true);
      $( "#as_time_tracking_selected_customers_wrapper" ).hide();

      $( "#as_time_tracking_all_tickets" ).prop( "checked", true);
      $( "#as_time_tracking_selected_tickets_wrapper" ).hide();

      //Reset the selected tickets to the default values
      var data = {
            'action': 'as_time_tracking_report_filter_status_change',
            'status': 'open',
            'security': ajax_object_reporting.ajax_nonce
          };

      /** global: ajax_object_reporting */
      $.post( ajax_object_reporting.ajax_url, data, function( response ) {

        $( "#as_time_tracking_selected_tickets" ).empty();

        for( var key in response.data ) {
          $( "#as_time_tracking_selected_tickets" ).append( "<option value=" + response.data[key].ID + ">" + response.data[key].post_title + "</option>" );
        }
      });
    }

    /**
     * Logic when a tab is clicked.
     */
    $( "#as_time_tracking_reporting_tabs .nav-tab-wrapper a" ).click( function( e ) {
      //Set the tab clicked to active class
      var idName = this.id;
      var tabList = $( "#as_time_tracking_reporting_tabs .nav-tab-wrapper a" );

      $( tabList ).each( function() {
        ( this.id === idName ? $( this ).addClass( "nav-tab-active" ) : $( this ).removeClass( "nav-tab-active" ) );
      });

      //Change content based on the tab clicked
      switch( idName ) {
        case "as_time_tracking_agent_tab":
        asTimeTrackingGenerateReport( "agent" );
        break;

        case "as_time_tracking_client_tab":
        asTimeTrackingGenerateReport( "client" );
        break;

        case "as_time_tracking_ticket_tab":
        asTimeTrackingGenerateReport( "ticket" );
        break;

        case "as_time_tracking_invoice_tab":
        asTimeTrackingGenerateReport( "invoice" );
        break;

        default:
        break;
      }

      e.preventDefault();
    });

    /**
     * Filtering functionality. Show/hide selected agents based on when the all
     * agents checkbox changes.
     */
    $( "#as_time_tracking_all_agents" ).change( function() {
      as_time_tracking_reporting_filter_select_change( "#as_time_tracking_selected_agents_wrapper", this );
    });

    //Show/hide selected customers based on when the all agents checkbox changes
    $( "#as_time_tracking_all_customers" ).change( function() {
      as_time_tracking_reporting_filter_select_change( "#as_time_tracking_selected_customers_wrapper", this );
    });

    //Show/hide selected customers based on when the all agents checkbox changes
    $( "#as_time_tracking_all_tickets" ).change( function() {
      as_time_tracking_reporting_filter_select_change( "#as_time_tracking_selected_tickets_wrapper", this );
    });

    /**
     * Shows/hides elements for the select input fields for the filter.
     * @param   {string} element_id     - The CSS ID of the element.
     * @param   {object} this_element   - The "this" object of the parent function.
     * @return  void
     */
    function as_time_tracking_reporting_filter_select_change( element_id, this_element ) {
      if( this_element.checked ) {
        $( element_id ).hide();
      } else {
        $( element_id ).show();
      }
    }

    //Reset values for the ticket select when the ticket status field is changed
    $( "#as_time_tracking_all_closed_tickets" ).change( function() {
      var ticketStatus = $( this ).val();
      var data = {
            'action': 'as_time_tracking_report_filter_status_change',
            'status': ticketStatus,
            'security': ajax_object_reporting.ajax_nonce
          };

      /** global: ajax_object_reporting */
      $.post( ajax_object_reporting.ajax_url, data, function( response ) {
        if( Object.keys( response.data ).length > 0 ) {
          $( "#as_time_tracking_selected_tickets" ).empty();
          $( "#as_time_tracking_selected_tickets" ).select2().val( null );

          for( var key in response.data ) {
            $( "#as_time_tracking_selected_tickets" ).append( "<option value=" + response.data[key].ID + ">" + response.data[key].post_title + "</option>" );
          }

          //Reset the select2 width otherwise the input width goes to small when we reset the values
          $( "#as_time_tracking_selected_tickets" ).select2({
            dropdownAutoWidth : true,
            width: '200px',
          });
        }
      });

      if( ticketStatus === "closed" ) {
        $( "#as_time_tracking_all_tickets_label" ).text( ajax_object_reporting.all_closed_tickets );
        $( "#as_time_tracking_selected_tickets_label" ).text( ajax_object_reporting.selected_closed_tickets );
      } else {
        $( "#as_time_tracking_all_tickets_label" ).text( ajax_object_reporting.all_tickets );
        $( "#as_time_tracking_selected_tickets_label" ).text( ajax_object_reporting.selected_tickets );
      }
    });

    /**
     * Generate report when filter submit button is clicked
     */
    $( "#as_time_tracking_report_filter_submit" ).click( function( e ) {
      var reportType = $( "#as_time_tracking_report_type_hidden" ).val();
      asTimeTrackingGenerateReport( reportType );
      e.preventDefault();
    });

    //Hide initial filter options which aren't needed
    $( "#as_time_tracking_selected_agents_wrapper" ).hide();
    $( "#as_time_tracking_selected_customers_wrapper" ).hide();
    $( "#as_time_tracking_selected_tickets_wrapper" ).hide();

    //Generate default agent report when page is opened
    asTimeTrackingGenerateReport( "agent" );
  });
})( jQuery );
