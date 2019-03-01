( function( $ ) {
  jQuery( document ).ready( function( $ ) {
      //If timer manual add functionality for displaying timer and its fields logic
      if( $( "input[name='as_time_tracking_tickets_timer_type']" ).val() == "manual" ) {
        manualTimeEmptySetup();
      }

      /**
       * Sets the intial hidden divs and text for manual mode
       * @return void
       */
      function manualTimeEmptySetup() {
        $( ".as_time_tracking_ticket_reply_time .as_time_tracking_record_time_wrapper" ).hide();
        $( "#as_time_tracking_link_start_end" ).hide();
        $( "#as_time_tracking_manual_timer_text" ).text( js_ticket_object.manual_empty_timer );
      }

      /**
       * Updates the start end fields area
       * @return void
       */
      function updateStartEndArea() {
        var updatedTime = "";
        var currentHours = $( "#as_time_tracking_tickets_hours_field" ).val();
        currentHours = checkPrependNumerNotZero( currentHours );

        var currentMinutes = $( "#as_time_tracking_tickets_minutes_field" ).val();
        currentMinutes = checkPrependNumerNotZero( currentMinutes );

        var currentSeconds = $( "#as_time_tracking_tickets_seconds_field" ).val();
        currentSeconds = checkPrependNumerNotZero( currentSeconds );

        updatedTime = currentHours + ":" + currentMinutes + ":" + currentSeconds;

        $( "#as_time_tracking_manual_timer_text" ).text( js_ticket_object.time_recorded_text + updatedTime );
        //If to not show the link again if the date/time fields are already displayed
        if( $( ".as_time_tracking_record_time_wrapper:visible" ).length === 1 ) {
          $( "#as_time_tracking_link_start_end" ).show();
        }

        if( currentHours === "00" && currentMinutes === "00" && currentSeconds === "00" ) {
          manualTimeEmptySetup();
        }
      }

      $( "#as_time_tracking_manual_pause_start" ).click( function( e ) {
        var manualStatus = $( "#as_time_tracking_manual_pause_start" ).text().toLowerCase();
        if( manualStatus == 'start' ) {
          updateStartEndArea();
        }
      });

      $( "#as_time_tracking_tickets_hours_field, #as_time_tracking_tickets_minutes_field, #as_time_tracking_tickets_seconds_field" ).on('input', function() {
        updateStartEndArea();
      });

      $( "#as_time_tracking_link_start_end" ).click( function( e ) {
        $( ".as_time_tracking_ticket_reply_time .as_time_tracking_record_time_wrapper" ).fadeIn();
        $( "#as_time_tracking_link_start_end" ).hide();

        e.preventDefault();
      });

    /**
     * Prepends a zero to single digit numbers
     * @param   {integer} valToCheck    - The value check
     * @return  {void}
     */
    function checkPrependNumberZero( valToCheck ) {
      var returnVal;
      if( valToCheck.toString().length === 1 ) {
        returnVal = "0" + valToCheck;
      } else {
        returnVal = valToCheck;
      }

      return returnVal;
    }

    /**
     * Prepends a zero to single digit numbers
     * @param   {integer} valToCheck    - The value check
     * @return  {void}
     */
    function checkPrependNumerNotZero( valToCheck ) {
      var returnVal;

      if( valToCheck.length === 0 ) {
        returnVal = "0";
      } else {
        returnVal = valToCheck;
      }

      returnVal = checkPrependNumberZero( returnVal );

      return returnVal;
    }

    //Functionality for detailed information overlay
    /**
     * Show overlay when the view all time button is clicked on the metabox.
     */
    $( "#as_time_tracking_all_recorded_time_button" ).click( function( e ) {
     e.preventDefault();
       $( "body" ).addClass( "as_time_tracking_noscroll" );
       $( "#as_time_tracking_custom_overlay" ).fadeIn();
    });

    /**
     * Hide overlay when the opacity background around the content is clicked.
    */
    $( "#as_time_tracking_custom_overlay" ).click( function() {
      $( "#as_time_tracking_custom_overlay" ).fadeOut();
      $( "body" ).removeClass( "as_time_tracking_noscroll" );
    });

    /**
     * Hide overlay when the close button on the overlay content is clicked.
     */
    $( "#as_time_tracking_custom_overlay .cancel" ).click( function() {
      $( "#as_time_tracking_custom_overlay" ).fadeOut();
      $( "body" ).removeClass( "as_time_tracking_noscroll" );
    });

    //Functionality for saving on the ticket level

    /**
     * Changes the text of the ticket level sdaving button based on if the timer is running or not.
     * @return  {void}
     */
    function handleTicketLevelSaveText() {
      if( $( "#as_time_tracking_automatic_pause_start" ).length ) {
        var timerText = $( "#as_time_tracking_automatic_pause_start" ).text().toLowerCase();

        if( timerText === 'pause' ) {
          $( ".as_time_tracking_ticket_level_save_container a" ).text( js_ticket_object.create_entry_timer );
        } else {
          $( ".as_time_tracking_ticket_level_save_container a" ).text( js_ticket_object.create_entry );
        }
      } else {
        var timerText = $( "#as_time_tracking_manual_pause_start" ).text().toLowerCase();

        if( timerText === 'stop' ) {
          $( ".as_time_tracking_ticket_level_save_container a" ).text( js_ticket_object.create_entry_timer );
        } else {
          $( ".as_time_tracking_ticket_level_save_container a" ).text( js_ticket_object.create_entry );
        }
      }
    }

    //Runs the ticket level save function when the page is loaded
    handleTicketLevelSaveText();

    //Show/hide save ticket level button based on the checkbox
    if ( $( '.as_time_tracking_ticket_level_option input' ).is( ':checked' ) ) {
      $( '.as_time_tracking_ticket_level_save_container' ).show();
    } else {
      $( '.as_time_tracking_ticket_level_save_container' ).hide();
    }

    //Show/hide save ticket level button based on the checkbox, after the checkbox has been changed.
    $( ".as_time_tracking_ticket_level_option input" ).change( function() {
      if( this.checked ) {
        $( '.as_time_tracking_ticket_level_save_container' ).show();
      } else {
        $( '.as_time_tracking_ticket_level_save_container' ).hide();
      }
    });

    //Run ticket level button text change for the automatic timer
    $( "#as_time_tracking_automatic_pause_start" ).click( function( e ) {
      handleTicketLevelSaveText();
    });

    //Run ticket level button text change for the manual timer
    $( "#as_time_tracking_manual_pause_start" ).click( function( e ) {
      handleTicketLevelSaveText();
    });

    //Show the popup when the ticket level save button is clicked
    $( ".as_time_tracking_ticket_level_save_container a" ).click( function( e ) {
      e.preventDefault();
      $( "body" ).addClass( "as_time_tracking_noscroll" );
      $( "#as_time_tracking_custom_overlay_ticket_level" ).fadeIn();

      if( $( "#as_time_tracking_manual_pause_start" ).length > 0 ) {
        var timerText = $( "#as_time_tracking_manual_pause_start" ).text().toLowerCase();
      } else {
        var timerText = $( "#as_time_tracking_automatic_pause_start" ).text().toLowerCase();
      }

      if( timerText == 'stop' ) {
        $( "#as_time_tracking_manual_pause_start" ).trigger( "click" );
      } else if( timerText == 'pause' ) {
        $( "#as_time_tracking_automatic_pause_start" ).trigger( "click" );
      }

      //Set end date for automatic timer
      if( $( "#as_time_tracking_automatic_pause_start" ).length ) {
        var currentDateTime = new Date();
        var dateYear = currentDateTime.getFullYear();
        var dateMonth = currentDateTime.getMonth() + 1;
        ( dateMonth.toString().length == 1 ? dateMonth = "0" + dateMonth : '' );
        var dateDay = currentDateTime.getDate();
        ( dateDay.toString().length == 1 ? dateDay = "0" + dateDay : '' );
        var dateHour = currentDateTime.getHours();
        ( dateHour.toString().length == 1 ? dateHour = "0" + dateHour : '' );
        var dateMinutes = currentDateTime.getMinutes();
        ( dateMinutes.toString().length == 1 ? dateMinutes = "0" + dateMinutes : '' );
        var dateSeconds = currentDateTime.getSeconds();
        ( dateSeconds.toString().length == 1 ? dateSeconds = "0" + dateSeconds : '' );
        var endDateTimeFull = dateYear + "-" + dateMonth + "-" + dateDay + " " + dateHour + ":" + dateMinutes + ":" + dateSeconds;

        $( "#as_time_tracking_ticket_level_end_date_time" ).val( endDateTimeFull );
      }
    });

    /**
     * Hide ticket level overlay when the opacity background around the content is clicked.
    */
    $( "#as_time_tracking_custom_overlay_ticket_level" ).click( function( e ) {
      if( e.target.id === 'as_time_tracking_custom_overlay_ticket_level' ) {
        $( "#as_time_tracking_custom_overlay_ticket_level" ).fadeOut();
        $( "body" ).removeClass( "as_time_tracking_noscroll" );
      }
    });

    /**
     * Hide overlay when the close button on the ticket level overlay content is clicked.
     */
    $( "#as_time_tracking_custom_overlay_ticket_level .cancel, #as-time-tracking-ticket-level-close-btn" ).click( function() {
      $( "#as_time_tracking_custom_overlay_ticket_level" ).fadeOut();
      $( "body" ).removeClass( "as_time_tracking_noscroll" );
    });

    //Save the Time Entry on the ticket level when the overlay save button is clicked.
    $( "#as-time-tracking-ticket-level-description-save" ).click( function( e ) {
      e.preventDefault();
      var description = $( "#as_time_tracking_ticket_level_description" ).val();

      if( $( "#as_time_tracking_automatic_pause_start" ).length ) {
        var timerType = 'auto';
        var startDateTime = $( "#as_time_tracking_tickets_start_date_time" ).val();
        var endDateTime = $( "#as_time_tracking_ticket_level_end_date_time" ).val();
      } else {
        var timerType = 'manual';
        var startDate = $( "#as_time_tracking_tickets_start_date" ).val();
        var startHours = $( "#as_time_tracking_tickets_start_date_hours" ).val();
        var startMinutes = $( "#as_time_tracking_tickets_start_date_minutes" ).val();
        var startSeconds = $( "#as_time_tracking_tickets_start_date_seconds" ).val();
        var startDateTime = startDate + " " + startHours + ":" + startMinutes + ":" + startSeconds;

        var endDate = $( "#as_time_tracking_tickets_end_date" ).val();
        var endHours = $( "#as_time_tracking_tickets_end_date_hours" ).val();
        var endMinutes = $( "#as_time_tracking_tickets_end_date_minutes" ).val();
        var endSeconds = $( "#as_time_tracking_tickets_end_date_seconds" ).val();
        var endDateTime = endDate + " " + endHours + ":" + endMinutes + ":" + endSeconds;
      }

      var data = {
        'timer_type' : timerType,
        'action': 'ticket_level_description_save_action',
        'ticket_id': js_ticket_object.ticket_id,
        'start_date_time': startDateTime,
        'end_date_time': endDateTime,
        'description': description,
        'individual_hours': $( "#as_time_tracking_tickets_hours_field" ).val(),
        'individual_minutes': $( "#as_time_tracking_tickets_minutes_field" ).val(),
        'individual_seconds': $( "#as_time_tracking_tickets_seconds_field" ).val(),
        'security': js_ticket_object.ajax_nonce
      };

      jQuery.post( js_ticket_object.ajax_url, data, function( response ) {
        //If title returned update it
        if( response.length > 0 ) {
          $( "#as-time-tracking-ticket-level-overlay-result" ).append( "<p>" + js_ticket_object.success + "</p>" );
        } else {
          $( "#as-time-tracking-ticket-level-overlay-result" ).append( "<p>" + js_ticket_object.error + "</p>" );
        }
      });

    });

    //To fix the issue with productivity plugin and this one giving disabled visual editor
    //If we get here it means that the plugin has been activated and the capability for
    //full editor is true
    if( $( "input[name=as_time_tracking_productivity_active]" ).length ) {
      $( "input[name=as_time_tracking_productivity_active]" ).appendTo( ".as_time_tracking_ticket_reply_time" );

      //fix for WP versions under 4.9.6
      if ( typeof tinymce !== 'undefined' ) {
        tinymce.on( 'SetupEditor', function ( editor ) {
          if( editor.id === 'wpas_reply' || editor.id === 'content' ) {
          	//Timeout used otherwise sometimes the get method doesn't work
	        setTimeout( function() {
	        	tinyMCE.get( editor.id ).setMode( "design" );
	        }, 1000 );
          }
        });
      }

      //Fix for WP version 4.9.6
      window.onload = function() {
        if( $( '#content' ).length ) {
          setTimeout( function() {
            tinyMCE.get( 'content' ).setMode( 'design' );
          }, 300 );
        }

        if( $( '#wpas_reply' ).length ) {
          setTimeout( function() {
            tinyMCE.get( 'wpas_reply' ).setMode( 'design' );
          }, 300 );
        }
      }
    }

  });
})( jQuery );

