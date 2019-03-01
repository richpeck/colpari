( function( $ ) {
  jQuery( document ).ready( function( $ ) {
    //Variables used for time to mimic when user stops editing an input
    var typing_timer;
    var done_typing_interval = 500;
    var ticket_id_timer;
    var ticket_typing_interval = 500;

    /**
     * Function which helps return GET parameters from a search string.
     * @return void
     */
    function getUrlParameter( name ) {
      return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search) || [null, ''])[1].replace(/\+/g, '%20')) || null;
    }

    /**
     * Function used for copy/duplicate functionality. Splits date object to separate date/time values.
     * @return Object
     */
    function getDateTimeSeparateValues( dateTime ) {
      if( typeof dateTime !== 'undefined' ) {
        var dateTime = dateTime.split( " " );
        var splitTime = dateTime[1].split( ":" );
        var returnObj = {
          date: dateTime,
          time: splitTime
        };

        //Remove time portion of date part of the object
        returnObj.date.splice( 1, 1 );
      } else {
        return undefined;
      }

      return returnObj;
    }

    /**
     * Function which has the effect where the user stops entering on a field after a delay.
     * Also runs when lookup clicks are done.
     * Handles the title to show based on ticket/reply #s, setting agent field and displaying
     * lookup button on ticket replies if ticket # was valid.
     * @return void
     */
    function done_typing_ticket_info( initialLoad ) {
      var ticket_id = $( "#as-time-tracking-ticket-id").val();
      var ticket_reply_id = $( "#as-time-tracking-ticket-reply" ).val();
      var ticket_level = $( "#as_time_tracking_ticket_level:checkbox:checked").length > 0;
      var data = {
        'action': 'ticket_and_reply_id_changed_action',
        'post_id': ajax_object.post_id,
        'ticket_id': ticket_id,
        'ticket_reply_id': ticket_reply_id,
        'ticket_level': ticket_level,
        'security': ajax_object.ajax_nonce
      };

      jQuery.post( ajax_object.ajax_url, data, function( response ) {
        //If title returned update it
        if( response.length > 0 ) {
          //$( "#title" ).val( response );
        } else {
          //$( "#title" ).val( "" );
        }
      });

      //Display the lookup ticket reply button if the ticket # was valid
      var data = {
        'action': 'determine_reply_lookup_display',
        'ticket_id': ticket_id,
        'security': ajax_object.ajax_nonce
      };

      jQuery.post( ajax_object.ajax_url, data, function( response ) {
        if( response === "exists" ) {
          $( "#as_time_tracking_ticket_reply_lookup_btn" ).show();
        } else {
          $( "#as_time_tracking_ticket_reply_lookup_btn" ).hide();
        }
      });

      //Check if on the ticket level and set agent if it is
      if( initialLoad !== true ) {
        set_ticket_level_agent( ticket_id );
      }

    }

    /**
     * Checks if ticket level is set then does an ajax request to select agent.
     * @return void
     */
    function set_ticket_level_agent( ticket_id ) {
      var isTicketLevel = $( "#as_time_tracking_ticket_level:checkbox:checked").length > 0;

      if( isTicketLevel ) {
        var data = {
          'action': 'ticket_level_get_agent_action',
          'ticket_id': ticket_id,
          'security': ajax_object.ajax_nonce
        };

        jQuery.post( ajax_object.ajax_url, data, function( response ) {
          if( response.length > 0 ) {

            $( "#as-time-tracking-agent > option" ).each( function() {
                if( response == this.value ) {
                  $( this ).attr( "selected","selected" );
                  $( "#as-time-tracking-agent-value" ).val( this.value );
                }
            });
          }
        });
      }
    }

    //Function to update the agent hidden field.
    $( "#as-time-tracking-agent" ).change( function() {
        $( "#as-time-tracking-agent-value" ).val( this.value );
    });

    /**
     * Function to update title when ticket reply was entered.
     * Used in the lookup.
     * @return void
     */
    function done_typing_ticket_reply_id() {
      var ticket_id = $( "#as-time-tracking-ticket-id" ).val();
      var ticket_reply_id = $( "#as-time-tracking-ticket-reply" ).val();
      $( "#title" ).val( ajax_object.ticket_id_title + ticket_id + ajax_object.ticket_reply_id_title + ticket_reply_id );

      //Setting the agent field if the ticket id was a correct value
      var data = {
        'action': 'determine_agent_action',
        'ticket_reply_id': ticket_reply_id,
        'security': ajax_object.ajax_nonce
      };

      jQuery.post( ajax_object.ajax_url, data, function( response ) {
        if( response.length > 0 ) {
          $( "#as-time-tracking-agent > option" ).each(function() {

              if( response == this.value ) {
                $( this ).attr( "selected","selected" );
                $( "#as-time-tracking-agent-value" ).val( this.value );
              }
          });
        } else {
          $( "#as-time-tracking-agent option:first" ).attr( "selected","selected" );
          $( "#as-time-tracking-agent-value" ).val( "" );
        }
      });
    }

    /**
     * Function to hide ticket reply lookup button if the input was empty.
     * @return void
     */
    function check_ticket_info() {
      if( $( "#as-time-tracking-ticket-id" ).val() == '' ) {
        $( "#as_time_tracking_ticket_reply_lookup_btn" ).hide();
      }
    }

    /**
     * Timer to mimic user finishing entering a ticket #, then runs a function.
     * Similar functionality is done on the js script but finally checks here through
     * ajax is the values belong to a valid ticket #. The function "done_typing_ticket_info"
     * handles both ticket and ticket reply #s.
     */
    $( "#as-time-tracking-ticket-id" ).keyup( function() {
      clearTimeout( typing_timer );
      typing_timer = setTimeout( done_typing_ticket_info(false), done_typing_interval );
    });

    /**
     * Timer to mimic user finishing entering a ticket reply #, then runs a function.
     * Similar functionality is done on the js script but finally checks here through
     * ajax is the values belong to a valid ticket reply #. The function
     * "done_typing_ticket_info" handles both ticket and ticket reply #s.
     */
    $( "#as-time-tracking-ticket-reply" ).keyup( function() {
      clearTimeout( typing_timer );
      typing_timer = setTimeout( done_typing_ticket_info(false), done_typing_interval );
      done_typing_ticket_reply_id();
    });

    /**
     * Update the title if the ticket level checkbox has changed.
     */
    $( "#as_time_tracking_ticket_level" ).change( function() {
      clearTimeout( typing_timer );
      typing_timer = setTimeout( done_typing_ticket_info(false), done_typing_interval );
    });

    /**
     * Functionality when the ticket lookup button is clicked. Uses data attributes
     * which contain the limit and offset used in the SQL query. These are used so a
     * large amount of records won't be queried at once. The results are then output.
     */
     $( "#as_time_tracking_ticket_lookup_btn" ).click( function( e ) {
       if( $( "#as_time_tracking_lookup_ticket_id_content" ).is( ":visible" ) ) {
         $( "#as_time_tracking_lookup_ticket_id_content" ).hide();
       } else {
         $( "#as_time_tracking_lookup_ticket_id_content" ).show();
       }

       e.preventDefault();
     });

     /**
      * Functionality when the ticket reply lookup button is clicked. If ajax returns
      * data then html is appended to the page with the response data.
      */
     $( "#as_time_tracking_ticket_reply_lookup_btn" ).click( function( e ) {
       var data = {
         'action': 'ticket_reply_id_lookup_action',
         'ticket_id': $( "#as-time-tracking-ticket-id" ).val(),
         'security': ajax_object.ajax_nonce
       };

       jQuery.post( ajax_object.ajax_url, data, function( response ) {
         if( Object.keys( response.data ).length > 0 ) {
           $( "#as_time_tracking_lookup_ticket_reply_id_content .table-result-data" ).empty();
           $( "#as_time_tracking_lookup_ticket_reply_id_content" ).show();

           var html = "<table>";
           html += "<tr><th class='as_time_tracking_ticket_reply_lookup_heading'>" + ajax_object.ticket_reply_id + "</th><th class='as_time_tracking_ticket_reply_lookup_heading'>" + ajax_object.ticket_reply_content + "</th><th class='as_time_tracking_ticket_reply_lookup_heading'>" + ajax_object.ticket_reply_meta_status + "</th></tr>";

           Object.keys( response.data ).forEach( function( key ) {
               html += "<tr>";
               html += "<td data-id='" + response.data[key].id + "'>" + response.data[key].id + "</td>";
               html += "<td data-id='" + response.data[key].id + "'>" + response.data[key].content + "</td>";
               html += "<td data-id='" + response.data[key].id + "'>" + response.data[key].tracked_time_status + "</td>";
               html += "</tr>";
           });

           html += "</table>";
           $( "#as_time_tracking_lookup_ticket_reply_id_content .table-result-data" ).append( html );
         } else {
           $( "#as_time_tracking_lookup_ticket_reply_id_content .table-result-data" ).empty();
         }
       });

       e.preventDefault();
     });

     //Set lookup select2 search inputs
     $( "#as_time_tracking_ticket_lookup_search" ).select2({
      ajax: {
        url: ajax_object.ajax_url,
        dataType: 'json',
        type: 'POST',
        delay: 250,
        data: function ( params ) {
          return {
            action: 'as_time_tracking_ticket_id_lookup_select',
            searchText: params.term,
            'security': ajax_object.ajax_nonce
          };
        },

        processResults: function ( data ) {
          return {
            results: data.data
          };
        }

      },

      templateResult: ticketTemplateResult,
      templateSelection: ticketTemplateSelection,
      escapeMarkup: function (markup) { return markup; }, 
      minimumInputLength: 3,
      placeholder: "Search for ticket ID",
      width: 'auto',
      dropdownAutoWidth : true


    });

    /**
     * For the templateResult option of select2. Adds custom HTML.
     * @return void
     */
     function ticketTemplateResult( data ) {
      if ( data.loading ) {
        return data.text;
      }

      if( $( ".as_time_tracking_ticket_lookup_heading" ).length === 0 ) {
        $( "#select2-as_time_tracking_ticket_lookup_search-results" ).prepend( 
          "<li><span class='as_time_tracking_ticket_lookup_heading'>" + ajax_object.ticket_id_title + "</span><span class='as_time_tracking_ticket_lookup_heading'>" + ajax_object.ticket_title + "</span><span class='as_time_tracking_ticket_lookup_heading'>" + ajax_object.status + "</span><span class='as_time_tracking_ticket_lookup_heading'>" + ajax_object.agent + "</span><span class='as_time_tracking_ticket_lookup_heading'>" + ajax_object.client_customer + "</span></li>"
        );
      }

      var markup = "<div class='as_time_tracking_ticket_lookup_val'>";
      markup = "<span data-id='" + data.id + "'>" + data.id + "</span>";
      markup += "<span data-id='" + data.id + "'>" + data.title + "</span>";
      markup += "<span data-id='" + data.id + "'>" + data.status + "</span>";
      markup += "<span data-id='" + data.id + "'>" + data.agent + "</span>";
      markup += "<span data-id='" + data.id + "'>" + data.client + "</span>";
      markup += "</div>";        

      return markup;
     }

    /**
     * For the templateSelection of select2. Gets all default text for our custom output.
     * @return void
     */
     function ticketTemplateSelection( data ) {
      
      if(data.id !== '') {
	      $( "#as-time-tracking-ticket-id" ).val( data.id );
	      $( "#as_time_tracking_lookup_ticket_id_content" ).hide();
	      $( "#as_time_tracking_ticket_reply_lookup_btn" ).show();
	      done_typing_ticket_info(false);
      }

      return data.id || data.text;
     }

    /**
     * Functionality to handle when to close the lookup overlays and add ticket/ticket
     * reply # values to their respective inputs if clicked.
     */
    $( document ).click( function( e ) {
      var target = $( e.target );

      //If area clicked isn't a ticket # or the select2 box, then close the overlay.
      if(
        target.is( ':not(#as_time_tracking_lookup_ticket_id_content table tr td)' ) &&
        target.is( ':not(#as_time_tracking_lookup_ticket_id_content span.select2-selection__placeholder)' ) &&
        target.is( ':not(input.select2-search__field)' ) &&
        target.is( ':not(li.select2-results__option.select2-results__message)' ) &&
        target.is( ':not(span.select2-search.select2-search--dropdown)' ) &&
        target.is( ':not(span.select2-selection__arrow)' ) &&
        target.is( ':not(span.select2-selection__arrow b)' ) &&
        target.is( ':not(span#select2-as_time_tracking_ticket_lookup_search-container.select2-selection__rendered)' ) &&
        target.is( ':not(ul#select2-as_time_tracking_ticket_lookup_search-results.select2-results__options)' ) &&
        target.is( ':not(#as_time_tracking_lookup_ticket_id_content)' ) &&
        target.is( ':not(#as_time_tracking_ticket_lookup_btn)' )
      ) {
        if( $( "#as_time_tracking_lookup_ticket_id_content" ).is( ":visible" ) ) {
          $( "#as_time_tracking_lookup_ticket_id_content" ).hide();
        }
      }

      //If area clicked isn't a ticket reply #, then close the overlay.
      if(
        target.is( ':not(#as_time_tracking_lookup_ticket_reply_id_content table tr td)' ) &&
        target.is( ':not(#as_time_tracking_lookup_ticket_id_content)' ) &&
        target.is( ':not(#as_time_tracking_ticket_reply_lookup_btn)' )
      ) {
        if( $( "#as_time_tracking_lookup_ticket_reply_id_content" ).is( ":visible" ) ) {
          $( "#as_time_tracking_lookup_ticket_reply_id_content" ).hide();
          $( "#as_time_tracking_lookup_ticket_reply_id_content .table-result-data" ).empty();
        }
      }

      /** If area clicked was the lookup span then fill the input value and close
       *  the overlay and show the reply button (for ticket reply) */
      if( target.is( "#as_time_tracking_lookup_ticket_reply_id_content table tr td" ) ) {
        var ticketReplyIdVal = $( target ).data( "id" );
        $( "#as-time-tracking-ticket-reply" ).val( ticketReplyIdVal );
        $( "#as_time_tracking_lookup_ticket_reply_id_content" ).hide();
        $( "#as_time_tracking_lookup_ticket_reply_id_content .table-result-data" ).empty();
        done_typing_ticket_reply_id();
      }
    });

    /**
     * When the ticket # field value has changed by a user, check if it was empty
     * and hide ticket reply lookup button.
     */
    $( "#as-time-tracking-ticket-id" ).keyup( function() {
      clearTimeout( ticket_id_timer );
      ticket_id_timer = setTimeout( check_ticket_info, ticket_typing_interval );
    });

    /**
     * Check if ticket # has value, if so then show the ticket reply lookup button
     * or else hide it.
     */
    if( $( "#as-time-tracking-ticket-id" ).val() != "" ) {
      $( "#as_time_tracking_ticket_reply_lookup_btn" ).show();
    } else {
      $( "#as_time_tracking_ticket_reply_lookup_btn" ).hide();
    }

    /**
     * Check if the multiple entries for one ticket reply is active and if the URL has the copy/duplicate
     * parameter. If so, send ajax request for data and update fields. 
     */
    if( ajax_object.allow_multiple_entries === 'yes' && getUrlParameter( "post_id_copy" ) !== null ) {
      var data = {
        'action': 'duplicate_ticket_reply_data_action',
        'post_id': getUrlParameter( "post_id_copy" ),
        'security': ajax_object.ajax_nonce
      };
      jQuery.post( ajax_object.ajax_url, data, function( response ) {
        if( response.data[0] !== undefined ) {
          $( "#title" ).val( response.data[0].post_title );
          $( "#as-time-tracking-ticket-id" ).val( response.data[0].ticket_id );
          $( "#as-time-tracking-ticket-reply" ).val( response.data[0].ticket_reply );

          var startDateTime = getDateTimeSeparateValues( response.data[0].start_date_time );

          if( typeof startDateTime !== 'undefined' ) {
            $( "#as-time-tracking-start-date" ).val( startDateTime.date[0] );
            $( "#as-time-tracking-start-time-hours" ).val( startDateTime.time[0] );
            $( "#as-time-tracking-start-time-minutes" ).val( startDateTime.time[1] );
            $( "#as-time-tracking-start-time-seconds" ).val( startDateTime.time[2] );
          }

          var endDateTime = getDateTimeSeparateValues( response.data[0].end_date_time );

          if( typeof endDateTime !== 'undefined' ) {
            $( "#as-time-tracking-end-date" ).val( endDateTime.date[0] );
            $( "#as-time-tracking-end-time-hours" ).val( endDateTime.time[0] );
            $( "#as-time-tracking-end-time-minutes" ).val( endDateTime.time[1] );
            $( "#as-time-tracking-end-time-seconds" ).val( endDateTime.time[2] );
          }

          var individualHours = Math.floor( response.data[0].individual_time / 60 );
          var individualMinutes = response.data[0].individual_time % 60;
          ( individualHours.toString().length < 2 ? individualHours = "0" + individualHours : '' );
          ( individualMinutes.toString().length < 2 ? individualMinutes = "0" + individualMinutes : '' );

          $( "#as-time-tracking-total-time-hours" ).val( individualHours );
          $( "#as-time-tracking-total-time-minutes" ).val( individualMinutes );

          $( "#as-time-tracking-total-time-adjustments" ).val( response.data[0].adjusted_time );

          setTimeout( function() {
            tinyMCE.get( 'astimetrackingnotes' ).setContent( response.data[0].notes );
          }, 1000 );
          
          $( "#as-time-tracking-agent" ).val( response.data[0].agent ).prop( 'selected', true );
          $( "#as-time-tracking-agent-value" ).val( response.data[0].agent );

          var entryDateTime = getDateTimeSeparateValues( response.data[0].entry_date_time );

          if( typeof entryDateTime !== 'undefined' ) {
            $( "#as-time-tracking-entry-date" ).val( entryDateTime.date[0] );
            $( "#as-time-tracking-entry-date-hours" ).val( entryDateTime.time[0] );
            $( "#as-time-tracking-entry-date-minutes" ).val( entryDateTime.time[1] );
            $( "#as-time-tracking-entry-date-seconds" ).val( entryDateTime.time[2] );
          }

        }
      });
    }

    /**
     * Check and update the title on page load, for successful validation values so "Auto Draft"
     * won't save if the ticket/ticket reply fields haven't changed on next post save.
    */
    clearTimeout( typing_timer );
    typing_timer = setTimeout( done_typing_ticket_info(true), done_typing_interval );

  });
})( jQuery );
