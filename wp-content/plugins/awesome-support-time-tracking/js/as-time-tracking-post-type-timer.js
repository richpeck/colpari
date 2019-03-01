( function( $ ) {
  jQuery( document ).ready( function( $ ) {
    var totalSeconds = new Date().getTime();
    var manualTotalSeconds = 0;
    var manualCountDownTimer;
    var totalInactiveTime = 0;
    var inactiveTime = new Date().getTime();
    var staticTime = new Date().getTime();
    var staticTimeSecondsTotal = 0;
    var startBlurTime;

	//Set up clock object for the timer
	var Clock = {
	  totalSeconds: 0,

	  start: function () {
	    var self = this;

	    this.interval = setInterval(function () {
	      self.totalSeconds += 1;

	      $("#as_time_tracking_tickets_hours_field").val(Math.floor(self.totalSeconds / 3600));
	      $("#as_time_tracking_tickets_minutes_field").val(Math.floor(self.totalSeconds / 60 % 60));
	      $("#as_time_tracking_tickets_seconds_field").val(parseInt(self.totalSeconds % 60));

        //Update the browser tab title with the current time as the timer is running
        generateTimedTab(Math.floor( self.totalSeconds / 3600 ), Math.floor( self.totalSeconds / 60 % 60 ), parseInt( self.totalSeconds % 60 ) );
	    }, 1000);
	  },

	  pause: function ( isTabHidden ) {
      if( isTabHidden === false ) {
        document.title = tabTitle;
        if( faviconUrl === undefined ) {
          $( 'link[rel="shortcut icon"]' ).attr( 'href', js_object.as_favicon_url );
        } else {
          var link = document.querySelector( "link[rel*='icon']" ) || document.createElement( 'link' );
          link.type = 'image/x-icon';
          link.rel = 'shortcut icon';
          link.href = faviconUrl;
          document.getElementsByTagName( 'head' )[0].appendChild( link );
        }
      }
	    clearInterval(this.interval);
	    delete this.interval;
	  },

	  resume: function () {
	    if (!this.interval) {
        this.start();
        
      };
	  },

	  manualResume: function (currentHoursField, currentMinutesField, currentSecondsField) {
	    var self = this;
	    var hours = parseInt( currentHoursField );
	    var minutes = parseInt( currentMinutesField );
	    var seconds = parseInt( currentSecondsField );

	    if( isNaN( hours ) ) {
	    	hours = 0;
	    }

	    if( isNaN( minutes ) ) {
	    	minutes = 0;
	    }

	    if( isNaN( seconds ) ) {
	    	seconds = 0;
	    }

	    var updatedSeconds = 0;
	    updatedSeconds += Math.floor( hours * 3600 );
	    updatedSeconds += Math.floor( minutes * 60 );
	    updatedSeconds += seconds;

	    self.totalSeconds = updatedSeconds;

	    this.interval = setInterval(function () {
	      self.totalSeconds += 1;

	      $("#as_time_tracking_tickets_hours_field").val(Math.floor(self.totalSeconds / 3600));
	      $("#as_time_tracking_tickets_minutes_field").val(Math.floor(self.totalSeconds / 60 % 60));
	      $("#as_time_tracking_tickets_seconds_field").val(parseInt(self.totalSeconds % 60));

        //Update the browser tab title with the current time as the timer is running
        generateTimedTab(Math.floor( self.totalSeconds / 3600 ), Math.floor( self.totalSeconds / 60 % 60 ), parseInt( self.totalSeconds % 60 ) );

	    }, 1000);
	  },

	  blurOverride: function(timeElapsed) {
	  	var self = this;
	    self.totalSeconds += timeElapsed;
	  }
	};

  //Variables for the Page Visibility API
	var hidden, visibilityChange; 
	if (typeof document.hidden !== "undefined") { // Opera 12.10 and Firefox 18 and later support 
	  hidden = "hidden";
	  visibilityChange = "visibilitychange";
	} else if (typeof document.msHidden !== "undefined") {
	  hidden = "msHidden";
	  visibilityChange = "msvisibilitychange";
	} else if (typeof document.webkitHidden !== "undefined") {
	  hidden = "webkitHidden";
	  visibilityChange = "webkitvisibilitychange";
	}

	//Check for blur and focus events. Used for issue in safari on mac where the timer wouldn't update on blur.
	document.addEventListener(visibilityChange, handleVisibilityChange, false);

	/**
	 * Checks if the screen is visible or not and sets dates so we can calculate the extra time to add on
	 * the clock timer on focus. 
	 * @return {void}
	 */
	function handleVisibilityChange() {
	  if (document[hidden]) {
	  	if( $( "#as_time_tracking_automatic_timer_field_wrapper" ).length ) {
	  		var status = $( "#as_time_tracking_automatic_pause_start" ).text().toLowerCase();
	  	} else {
	  		var status = $( "#as_time_tracking_manual_pause_start" ).text().toLowerCase();
	  	}

	    if( status === "pause" || status === "stop" ) {
        document.title = tabText + " - " + tabTitle;
	      startBlurTime = new Date().getTime();
	    } 

	    Clock.pause( true ); 
	  } else {
	  	if( $( "#as_time_tracking_automatic_timer_field_wrapper" ).length ) {
	  		var status = $( "#as_time_tracking_automatic_pause_start" ).text().toLowerCase();
	  	} else {
	  		var status = $( "#as_time_tracking_manual_pause_start" ).text().toLowerCase();
	  	}
      
	    if( status === "pause" || status === "stop" ) {
	    	startFocusTime = new Date().getTime();
	    }

	    if (startBlurTime) {
	     if( status === "pause" || status === "stop" ) {
	      	  if( $( "#as_time_tracking_automatic_timer_field_wrapper" ).length ) {
	      	  $( "#as_time_tracking_tickets_hours_field" ).val("");
		      $( "#as_time_tracking_tickets_minutes_field" ).val("");
		      $( "#as_time_tracking_tickets_seconds_field" ).val("");
		      	Clock.resume(); 
		      } else {
		      	var currentHoursField = $( "#as_time_tracking_tickets_hours_field" ).val();
	    		var currentMinutesField = $( "#as_time_tracking_tickets_minutes_field" ).val();
	    		var currentSecondsField = $( "#as_time_tracking_tickets_seconds_field" ).val();
	    		$( "#as_time_tracking_tickets_hours_field" ).val("");
		      	$( "#as_time_tracking_tickets_minutes_field" ).val("");
		     	$( "#as_time_tracking_tickets_seconds_field" ).val("");
		      	Clock.manualResume(currentHoursField, currentMinutesField, currentSecondsField);
		      }

		      var timeElapsed = calculateTimeSecondDifference(startFocusTime, startBlurTime);
	      	  Clock.blurOverride(timeElapsed);
	      }
	    }
	  }
	}

  /**
   * Sets an element to read only.
   * @param   {string} cssID - The CSS ID of the elemnt to make readonly.
   * @return  void
   */
  function setReadOnlyFields( cssID ) {
    $( cssID ).prop( "readonly", true );
    $( cssID ).css( "background", "#cccccc" );
    $( cssID ).css( "background-color", "#cccccc" );
  }

  /**
   * Unsets an element from read only.
   * @param   {string} cssID - The CSS ID of the elemnt to remove as readonly.
   * @return  void
   */
  function unsetReadOnlyFields( cssID ) {
    $( cssID ).prop( "readonly", false );
    $( cssID ).css( "background", "#ffffff" );
    $( cssID ).css( "background-color", "#ffffff" );
  }

  /**
   * Calculates time values to hours, minutes and seconds and sets the values.
   * @param   {string} totalSeconds - The seconds to calculate time from
   * @return  void
   */
  function calculateTimeValues( totalSeconds ) {
    var seconds = totalSeconds;
    var minutes = Math.floor( seconds / 60 );
    seconds = seconds - ( minutes * 60 );
    var hours = Math.floor( minutes / 60 );
    minutes = minutes - ( hours * 60 );

    $( "#as_time_tracking_tickets_hours_field" ).val( hours );
    $( "#as_time_tracking_tickets_minutes_field" ).val( minutes );
    $( "#as_time_tracking_tickets_seconds_field" ).val( seconds );

    //Set end date/time values to prevent bug where end date/time comes before start date/time if reply is clicked while timer is running
    $( "#as_time_tracking_tickets_end_date_hours" ).val( $( "#as_time_tracking_tickets_start_date_hours" ).val() );
    $( "#as_time_tracking_tickets_end_date_minutes" ).val( $( "#as_time_tracking_tickets_start_date_minutes" ).val() );
    $( "#as_time_tracking_tickets_end_date_seconds" ).val( $( "#as_time_tracking_tickets_start_date_seconds" ).val() );
  }

  /**
   * Calculates manual time values to hours, minutes and seconds and sets the values.
   * @return {Number} converted time added in seconds to make a total
   * @return {void}
   */
  function calculateManualTimeValues() {
    var manualHours = $( "#as_time_tracking_tickets_hours_field" ).val();
    manualHours = calculateTimerVal( manualHours );

    var manualMinutes = $( "#as_time_tracking_tickets_minutes_field" ).val();
    manualMinutes = calculateTimerVal( manualMinutes );

    var manualSeconds = $( "#as_time_tracking_tickets_seconds_field" ).val();
    manualSeconds = calculateTimerVal( manualSeconds );

    //Seconds casted to integers as sometimes it concatenated the values like a string
    var inputtedHoursToSeconds = manualHours * 3600;
    var inputtedminutesToSeconds = manualMinutes * 60;
    var seconds = Number( inputtedHoursToSeconds ) + Number( inputtedminutesToSeconds ) + Number( manualSeconds );
    var minutes = Math.floor( seconds / 60 );
    seconds = seconds - ( minutes * 60 );
    var hours = Math.floor( minutes / 60 );
    minutes = minutes - ( hours * 60 );

    $("#as_time_tracking_tickets_hours_field").val( hours );
    $("#as_time_tracking_tickets_minutes_field").val( minutes );
    $("#as_time_tracking_tickets_seconds_field").val( seconds );

    //Casted as an integer as sometimes values were concatenated as a string
    return Number( inputtedHoursToSeconds ) + Number( inputtedminutesToSeconds ) + Number( manualSeconds );
  }

  /**
   * Checks that the end date can't be before the start date.
   * @param   {string} startDate - The start date to check
   * @param   {string} endDate - The end date to check
   * @return  {Boolean}
   */
  function endDateCompare( startDate, endDate ) {
      return new Date( endDate ) < new Date( startDate );
  }

  /**
   * Checks that the date entered is in the yyyy-mm-dd format
   * @param   {string} dateString - The date string to check
   * @return  {Boolean}
   */
  function isValidDate( dateString ) {
    var regEx = /^\d{4}-\d{2}-\d{2}$/;
    return dateString.match( regEx ) != null;
  }

  /**
   * Checks if the start date is empty
   * @param   {string} fullStartDate - The date string to check
   * @return  {void}
   */
  function checkAutomaticStartDateLength( fullStartDate ) {
    if( fullStartDate.length === 0 ) {
      /** global: js_object */
      alert( js_object.start_empty );
      e.preventDefault();
    }
  }

  /**
   * Checks if the start date is empty
   * @param   {string} fullStartDate - The date string to check
   * @return  {void}
   */
  function checkAutomaticValidDate( fullStartDate ) {
    if( isValidDate( fullStartDate ) === false ) {
      /** global: js_object */
      alert( js_object.start_format );
      e.preventDefault();
    }
  }

  /**
   * Checks if value is numeric
   * @param   {integer} valToCheck    - The value check
   * @param   {string}  errorMessage  - The error message to alert
   * @param   {object}  e             - The event of the submit button
   * @return  {void}
   */
  function valueCheckNumeric( valToCheck, errorMessage, e ) {
    if( $.isNumeric( valToCheck ) === false ) {
      alert( errorMessage );
      e.preventDefault();
    }
  }

  /**
   * Checks if value is numeric
   * @param   {integer} valToCheck    - The value check
   * @param   {string}  errorMessage  - The error message to alert
   * @param   {object}  e             - The event of the submit button
   * @return  {void}
   */
  function checkValueLengthEmpty( valToCheck, errorMessage, e ) {
    if( valToCheck.length === 0 ) {
      alert( errorMessage );
      e.preventDefault();
    }
  }

  /**
   * Checks if value is numeric
   * @param   {integer} valToCheck    - The value check
   * @param   {string}  errorMessage  - The error message to alert
   * @param   {object}  e             - The event of the submit button
   * @return  {void}
   */
  function checkValueValidDate( valToCheck, errorMessage, e ) {
    if( isValidDate( valToCheck ) === false ) {
      alert( errorMessage );
      e.preventDefault();
    }
  }

  /**
   * Prepends a zero to single digit numbers
   * @param   {integer} valToCheck    - The value check
   * @return  {integer}
   */
  function calculateTimerVal( valToCheck ) {
    var returnVal;

    if( valToCheck.length === 0 ) {
      returnVal = 0;
    } else {
      returnVal = valToCheck;
      if( !$.isNumeric( returnVal ) ) {
        returnVal = 0;
      }
        return returnVal;
    }

    returnVal = checkPrependNumberZero( returnVal );

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

   });
})( jQuery );