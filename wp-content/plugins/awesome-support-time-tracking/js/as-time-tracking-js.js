( function( $ ) {
  jQuery( document ).ready( function( $ ) {
    //Variables for typing timer to mimic when user stops editing a input
    var timerTextId = "";
    var timerType = "";
    var timer;

    //Variables for setting default dates
    var date = new Date();
    var hours = date.getHours();

    /**
     * Checks if there was a previous total time then updates the manual timer values.
     * @return void
     */
    function as_time_tracking_default_manual_timer_values() {
      if( $( "#as_time_tracking_manual_pause_start" ).length ) {
        var currentTotalHours = $( "#as-time-tracking-total-time-hours" ).val();
        currentTotalHours = Number( currentTotalHours );
        var currentTotalMinutes = $( "#as-time-tracking-total-time-minutes" ).val();
        currentTotalMinutes = Number( currentTotalMinutes );

        $( "#as_time_tracking_tickets_hours_field" ).val( currentTotalHours );
        $( "#as_time_tracking_tickets_minutes_field" ).val( currentTotalMinutes );
        $( "#as_time_tracking_tickets_seconds_field" ).val( 0 );
      }
    }

    /**
     * Sets the start/end dates. Will be done when the page loads and if the button is clicked.
     * @return void
     */
    function as_time_tracking_set_default_dates( e ) {
      var date = new Date();
      var hours = date.getHours();
      ( hours.toString().length == 1 ? hours = '0' + hours : '' );
      var minutes = date.getMinutes();
      ( minutes.toString().length == 1 ? minutes = '0' + minutes : '' );
      var seconds = date.getSeconds();
      ( seconds.toString().length == 1 ? seconds = '0' + seconds : '' );

      $( "#as-time-tracking-start-date" ).datepicker({ dateFormat: "yy-mm-yy" }).datepicker( "setDate", new Date() );
      $( "#as-time-tracking-end-date" ).datepicker({ dateFormat: "yy-mm-yy" }).datepicker( "setDate", new Date() );

      $( "#as-time-tracking-start-time-hours").val( hours ).change();
      $( "#as-time-tracking-start-time-minutes" ).val( minutes ).change();
      $( "#as-time-tracking-start-time-seconds" ).val( seconds ).change();

      $( "#as-time-tracking-end-time-hours" ).val( hours ).change();
      $( "#as-time-tracking-end-time-minutes" ).val( minutes ).change();
      $( "#as-time-tracking-end-time-seconds" ).val( seconds ).change();

      if( e !== null ) {
        e.preventDefault();
      }
    } 

    /**
     * Checks the invoiced status and sets everything to readonly if approved.
     * @return void
     */
    function set_approved_invoice_readonly() {
      var approved_status = $( "#as_time_tracking_invoice_hidden_status" ).val();

      if( approved_status == "approved" ) {
        //Make each field readonly and set consistent styling
        $( "#as-time-tracking-ticket-id" ).prop( "readonly", true );
        $( "#as-time-tracking-ticket-id" ).css( "color", "rgba(51,51,51,.5)" );
        $( "#as-time-tracking-ticket-id" ).css( "cursor", "default" );

        $( "#as-time-tracking-ticket-reply" ).prop( "readonly", true );
        $( "#as-time-tracking-ticket-reply" ).css( "color", "rgba(51,51,51,.5)" );
        $( "#as-time-tracking-ticket-reply" ).css( "cursor", "default" );

        $( "#as-time-tracking-start-date" ).datepicker( "option", "disabled", true );
        $( "#as-time-tracking-start-date" ).css( "background", "#eee" );
        $( "#as-time-tracking-start-date" ).css( "background-color", "#eee" );

        $( "#as-time-tracking-start-time-hours" ).prop( "disabled", true );
        $( "#as-time-tracking-start-time-hours" ).css( "background", "#eee" );
        $( "#as-time-tracking-start-time-hours" ).css( "background-color", "#eee" );

        $( "#as-time-tracking-start-time-minutes" ).prop( "disabled", true );
        $( "#as-time-tracking-start-time-minutes" ).css( "background", "#eee" );
        $( "#as-time-tracking-start-time-minutes" ).css( "background-color", "#eee" );

        $( "#as-time-tracking-start-time-seconds" ).prop( "disabled", true );
        $( "#as-time-tracking-start-time-seconds" ).css( "background", "#eee" );
        $( "#as-time-tracking-start-time-seconds" ).css( "background-color", "#eee" );

        $( "#as-time-tracking-end-date" ).datepicker( "option", "disabled", true );
        $( "#as-time-tracking-end-date" ).css( "background", "#eee" );
        $( "#as-time-tracking-end-date" ).css( "background-color", "#eee" );

        $( "#as-time-tracking-end-time-hours" ).prop( "disabled", true );
        $( "#as-time-tracking-end-time-hours" ).css( "background", "#eee" );
        $( "#as-time-tracking-end-time-hours" ).css( "background-color", "#eee" );

        $( "#as-time-tracking-end-time-minutes" ).prop( "disabled", true );
        $( "#as-time-tracking-end-time-minutes" ).css( "background", "#eee" );
        $( "#as-time-tracking-end-time-minutes" ).css( "background-color", "#eee" );

        $( "#as-time-tracking-end-time-seconds" ).prop( "disabled", true );
        $( "#as-time-tracking-end-time-seconds" ).css( "background", "#eee" );
        $( "#as-time-tracking-end-time-seconds" ).css( "background-color", "#eee" );

        $( "#as-time-tracking-total-time-hours" ).prop( "readonly", true );
        $( "#as-time-tracking-total-time-hours" ).css( "color", "rgba(51,51,51,.5)" );
        $( "#as-time-tracking-ticket-reply" ).css( "cursor", "default" );

        $( "#as-time-tracking-total-time-minutes" ).prop( "disabled", true );
        $( "#as-time-tracking-total-time-minutes" ).css( "background", "#eee" );
        $( "#as-time-tracking-total-time-minutes" ).css( "background-color", "#eee" );

        $( "#as-time-tracking-total-time-adjustments" ).prop( "readonly", true );
        $( "#as-time-tracking-total-time-adjustments" ).css( "color", "rgba(51,51,51,.5)" );
        $( "#as-time-tracking-total-time-adjustments" ).css( "cursor", "default" );

        $( "#astimetrackingnotes" ).css( "color", "rgba(51,51,51,.5)" );
        $( "#astimetrackingnotes" ).css( "cursor", "default" );

        $( "#as-time-tracking-agent" ).prop( "disabled", true );
        $( "#as-time-tracking-agent" ).css( "background", "#eee" );
        $( "#as-time-tracking-agent" ).css( "background-color", "#eee" );

        $( "#as-time-tracking-entry-date" ).datepicker( "option", "disabled", true );
        $( "#as-time-tracking-entry-date" ).css( "background", "#eee" );
        $( "#as-time-tracking-entry-date" ).css( "background-color", "#eee" );

        $( "#as-time-tracking-entry-date-hours" ).prop( "disabled", true );
        $( "#as-time-tracking-entry-date-hours" ).css( "background", "#eee" );
        $( "#as-time-tracking-entry-date-hours" ).css( "background-color", "#eee" );

        $( "#as-time-tracking-entry-date-minutes" ).prop( "disabled", true );
        $( "#as-time-tracking-entry-date-minutes" ).css( "background", "#eee" );
        $( "#as-time-tracking-entry-date-minutes" ).css( "background-color", "#eee" );

        $( "#as-time-tracking-entry-date-seconds" ).prop( "disabled", true );
        $( "#as-time-tracking-entry-date-seconds" ).css( "background", "#eee" );
        $( "#as-time-tracking-entry-date-seconds" ).css( "background-color", "#eee" );

        $( "#as-time-tracking-invoiced-field" ).prop( "disabled", true );
        $( "#as-time-tracking-invoiced-field" ).css( "background", "#eee" );
        $( "#as-time-tracking-invoiced-field" ).css( "background-color", "#eee" );

        $( "#as-time-tracking-invoice-number" ).css( "color", "rgba(51,51,51,.5)" );
        $( "#as-time-tracking-invoice-number" ).css( "cursor", "default" );
      }
    }

    /**
     * Calculates and updates the total time fields if the start/end date/times are
     * changed.
     * @return void
     */
    function asTimeTrackingCalculateTimeFieldsOnChange() {
      var start_hours = $( "#as-time-tracking-start-time-hours option:selected" ).text();
      var start_minutes = $( "#as-time-tracking-start-time-minutes option:selected" ).text();
      var start_seconds = $( "#as-time-tracking-start-time-seconds option:selected" ).text();
      var end_hours = $( "#as-time-tracking-end-time-hours option:selected" ).text();
      var end_minutes = $( "#as-time-tracking-end-time-minutes option:selected" ).text();
      var end_seconds = $( "#as-time-tracking-end-time-seconds option:selected" ).text();
      var start_date = $( "#as-time-tracking-start-date" ).val();
      var end_date = $( "#as-time-tracking-end-date" ).val();

      //If the dates are not empty and all times not equal to "00" then calculate time
      if( start_date.length !== 0 && end_date.length !== 0 ) {
        if(
          Number( start_hours ) === 0 && Number( start_minutes ) === 0 && Number( start_seconds ) === 0 &&
          Number( end_hours ) === 0 && Number( end_minutes ) === 0 && Number( end_seconds ) === 0
          ) {
        } else {
          var startDateTime = start_date + " " + start_hours + ":" + start_minutes + ":" + start_seconds;
          var fullStartDateTime = new Date( startDateTime ).getTime();
          var endDateTime = end_date + " " + end_hours + ":" + end_minutes + ":" + end_seconds;
          var fullEndDateTime = new Date( endDateTime ).getTime();

          if( new Date( startDateTime ) < new Date( endDateTime ) ) {
            var hourDiff = fullEndDateTime - fullStartDateTime;
            var secDiff = hourDiff / 1000;
            var minDiff = hourDiff / 60 / 1000;
            var hDiff = hourDiff / 3600 / 1000;
            var humanReadable = {};
            humanReadable.hours = Math.floor( hDiff );
            humanReadable.minutes = Math.round( minDiff - 60 * humanReadable.hours );
            ( humanReadable.minutes.toString().length === 1 ? humanReadable.minutes = "0" + humanReadable.minutes : "" );

            $( "#as-time-tracking-total-time-hours" ).val( humanReadable.hours );
            $( "#as-time-tracking-total-time-minutes" ).val( humanReadable.minutes ).change();
          } else {
            $( "#as-time-tracking-total-time-hours" ).val( "0" );
            $( "#as-time-tracking-total-time-minutes" ).val( "00" ).change();
          }
        }
      }
    }

    /**
     * Check if start/end date/times are changed.
     */
    $('#as-time-tracking-start-time-hours, #as-time-tracking-start-time-minutes, #as-time-tracking-start-time-seconds, #as-time-tracking-end-time-hours, #as-time-tracking-end-time-minutes, #as-time-tracking-end-time-seconds').change( function() {
      asTimeTrackingCalculateTimeFieldsOnChange();
    });

    /**
     * Set up date picker for entry date which accepts a yyyy-mm-dd format.
     */
    $( "#as-time-tracking-entry-date" ).datepicker({
      dateFormat: "yy-mm-dd"
    });

    /**
     * If total time recorded fields change, change value on timer.
     */
    $( "#as-time-tracking-total-time-hours" ).keyup( function() {
      as_time_tracking_default_manual_timer_values();
    });

    $( "#as-time-tracking-total-time-minutes" ).change( function() {
      as_time_tracking_default_manual_timer_values();
    });

    /**
     * Set up date picker for start date which accepts a yyyy-mm-dd format. When
     * selected it will calculate and change the total time fields.
     */
    $( "#as-time-tracking-start-date" ).datepicker({
      dateFormat: "yy-mm-dd",
      onSelect: function() {
        asTimeTrackingCalculateTimeFieldsOnChange();
      }
    });

    /**
     * Set up date picker for end date which accepts a yyyy-mm-dd format. When
     * selected it will calculate and change the total time fields.
     */
    $( "#as-time-tracking-end-date" ).datepicker({
      dateFormat: "yy-mm-dd",
      onSelect: function(){
        asTimeTrackingCalculateTimeFieldsOnChange();
      }
    });

    /**
     * Set date times when set default button is clicked. The date and times
     * will be based on the current time when the button was clicked.
     */
    $( "#as-time-tracking-default-entry-end-dates" ).click( function( e ) {
      as_time_tracking_set_default_dates( e );
    });

    //Hide empty metabox to make space
    $( "#normal-sortables" ).hide();

    //Remove placeholder text on the title input
    $( "#title-prompt-text" ).hide();

    //Make the custom post type title read only and add styles
    $( "#title" ).prop( "readonly", true );
    $( "#title" ).css( "cursor", "default" );
    $( "#title" ).css( "background", "#cccccc" );
    $( "#title" ).css( "background-color", "#cccccc" );

    //Add help message under title to tell the user that the field is readonly
    $( "#titlewrap" ).append( js_object.title_text );

    //Functionality for default start/end date button - sets the fields to current date/time
    ( hours.toString().length == 1 ? hours = '0' + hours : '' );

    /** Checks if the entry date field is empty, if it is then it means that the user is making
     *  a new entry, so default the date and time to the current date/time. */
    var entryDateLength = $( "#as-time-tracking-entry-date" ).val().length;

    if( entryDateLength == 0 ) {
      var date = new Date();
      var hours = date.getHours();
      ( hours.toString().length == 1 ? hours = '0' + hours : '' );
      var minutes = date.getMinutes();
      ( minutes.toString().length == 1 ? minutes = '0' + minutes : '' );
      var seconds = date.getSeconds();
      ( seconds.toString().length == 1 ? seconds = '0' + seconds : '' );

      $( "#as-time-tracking-entry-date" ).datepicker({ dateFormat: "yy-mm-yy" }).datepicker( "setDate", new Date() );
      $( "#as-time-tracking-entry-date-hours ").val( hours ).change();
      $( "#as-time-tracking-entry-date-minutes" ).val( minutes ).change();
      $( "#as-time-tracking-entry-date-seconds" ).val( seconds ).change();
    }

    //Check if validation title div exists and then set the title if it does
    if( $("#as_time_tracking_title_validation" ).length ) {
      var titleText = $( "#as_time_tracking_title_validation" ).text();
      $( "#title" ).val( titleText );
    }

    //Set invoice approved records to readonly
    set_approved_invoice_readonly();

    //Timer logic
    /**
     * Determines the timer type and sets the CSS ids to variables
     * changed.
     * @return void
     */
    function determineTimerType() {
    	var post_status = $("#as_time_tracking_new_or_saved_post").val();

    	if( $( "#as_time_tracking_manual_pause_start" ).length ) {
    		timerTextId = "#as_time_tracking_manual_pause_start";
    		timerType = "manual";
    		updateTotalTimeFields();
    	} else {
    		timerTextId = "#as_time_tracking_automatic_pause_start";
    		timerType = "automatic";

    		if( post_status === 'saved' ) {
    			$( "#as_time_tracking_automatic_pause_start" ).trigger( "click" );
    			$( "#as_time_tracking_automatic_pause_start" ).text( js_object.start );
    		}

    	}

    }

    /**
     * Checks the timer type and updates the total timer fields using a timer which runs just below a second
     * @return void
     */
    function updateTotalTimeFields() {
    	var statusText = $( timerTextId ).text().toLowerCase();

    	if( timerType === 'automatic' ) {
    		if( statusText === 'pause' ) {
    			timer = setInterval( function() {
    				var hoursVal = $( "#as_time_tracking_tickets_hours_field" ).val();
    				var minutesVal = $( "#as_time_tracking_tickets_minutes_field" ).val();
    				( hoursVal.toString().length < 2 ? hoursVal = "0" + hoursVal : '' );
    				( minutesVal.toString().length < 2 ? minutesVal = "0" + minutesVal : '' );
    				$( "#as-time-tracking-total-time-hours" ).val( hoursVal );
    				$( "#as-time-tracking-total-time-minutes" ).val( minutesVal );
    			}, 800);
    		} else {
    			clearInterval( timer );
    		}
    	} else {
    		if( statusText === 'stop' ) {
    			timer = setInterval( function() {
    				var hoursVal = $( "#as_time_tracking_tickets_hours_field" ).val();
    				var minutesVal = $( "#as_time_tracking_tickets_minutes_field" ).val();
    				( hoursVal.toString().length < 2 ? hoursVal = "0" + hoursVal : '' );
    				( minutesVal.toString().length < 2 ? minutesVal = "0" + minutesVal : '' );
    				$( "#as-time-tracking-total-time-hours" ).val( hoursVal );
    				$( "#as-time-tracking-total-time-minutes" ).val( minutesVal );
    			}, 800);
    		} else {
    			clearInterval( timer );
    		}
    	}
    }

    //Updates total time based on if timer is running or not
    $( "#as_time_tracking_manual_pause_start, #as_time_tracking_automatic_pause_start" ).click( function() {
    	updateTotalTimeFields();
    });

    //Updates manual time total time if the user types a value
    $( '#as_time_tracking_tickets_hours_field, #as_time_tracking_tickets_minutes_field, #as_time_tracking_tickets_seconds_field' ).on( 'keyup', function() {
      if( $( "#as_time_tracking_manual_pause_start" ).length ) {
        var hoursVal = $( "#as_time_tracking_tickets_hours_field" ).val();
        var minutesVal = $( "#as_time_tracking_tickets_minutes_field" ).val();
        ( minutesVal.toString().length < 2 ? minutesVal = "0" + minutesVal : '' );
        $( "#as-time-tracking-total-time-hours" ).val( hoursVal );
        $( "#as-time-tracking-total-time-minutes" ).val( minutesVal );
      }
    });

    //If on the automatic timer the minutes or hours change then update the total time fields.
    $( "#as_time_tracking_tickets_minutes_field, #as_time_tracking_tickets_hours_field" ).on( "change", function() {
      if( $( "#as_time_tracking_manual_pause_start" ).length === 0 ) {
        var hours = $( "#as_time_tracking_tickets_hours_field" ).val();
        ( hours.length === 1 ? hours = "0" + hours : '' );

        var minutes = $( "#as_time_tracking_tickets_minutes_field" ).val();
        ( minutes.length === 1 ? minutes = "0" + minutes : '' );

        $( "#as-time-tracking-total-time-hours" ).val( hours );
        $( "#as-time-tracking-total-time-minutes" ).val( minutes ).prop( 'selected', true );
      }
    });

    //Launches initial timer and total timer fields based on timer type.
    determineTimerType();

    //Sets the automatic timer successful validation values if the button is clickd and pauses the automatic timer
    $( "#as-time-tracking-automatic-timer-validation" ).click( function( e ) {
    	$( '#as-time-tracking-total-time-hours' ).val( $( '#as-time-tracking-automatic-timer-validation-hours' ).val() );
    	$( '#as-time-tracking-total-time-minutes' ).val( $( '#as-time-tracking-automatic-timer-validation-minutes' ).val() );
    	$( '#as-time-tracking-total-time-minutes' ).prop( 'selected', true );
    	$( "#as_time_tracking_automatic_pause_start" ).trigger( "click" );
    	$( "#as-time-tracking-automatic-timer-validation" ).hide();
    	e.preventDefault();
    });

    //Set default start/end dates straight away
    if(
      $( "#as-time-tracking-start-date").val().length === 0 &&
      $( "#as-time-tracking-end-date").val().length === 0
      ) {
        as_time_tracking_set_default_dates( null );
    }

    //On manual timer if validation error get the total time recorded and re-input the values in the timer
    as_time_tracking_default_manual_timer_values();

  });
})( jQuery );
