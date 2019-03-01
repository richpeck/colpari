( function( $ ) {
  jQuery( document ).ready( function( $ ) {
    var faviconUrl = $( 'link[rel="shortcut icon"]' ).prop( 'href' );
    var tabTitle = $( document ).find( 'title' ).text();
    var tabHours = 0;
    var tabMinutes = 0;
    var tabSeconds = 0;
    var tabText = js_object_timer.tab_text;

  	//Set up clock object for the timer
  	var Clock = {
  	  totalSeconds: 0,

  	  start: function () {
  	    var self = this;

  	    this.interval = setInterval(function () {
  	      self.totalSeconds += 1;

  	      $("#as_time_tracking_tickets_hours_field").val(Math.floor(self.totalSeconds / 3600)).trigger( 'change' );
  	      $("#as_time_tracking_tickets_minutes_field").val(Math.floor(self.totalSeconds / 60 % 60)).trigger( 'change' );
  	      $("#as_time_tracking_tickets_seconds_field").val(parseInt(self.totalSeconds % 60)).trigger( 'change' );

          //Update the browser tab title with the current time as the timer is running
          generateTimedTab(Math.floor( self.totalSeconds / 3600 ), Math.floor( self.totalSeconds / 60 % 60 ), parseInt( self.totalSeconds % 60 ) );
  	    }, 1000);
  	  },

  	  pause: function ( isTabHidden ) {
        if( isTabHidden === false ) {
          document.title = tabTitle;
          if( faviconUrl === undefined ) {
            $( 'link[rel="shortcut icon"]' ).attr( 'href', js_object_timer.as_favicon_url );
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

    //Variables for script
    var totalSeconds = new Date().getTime();
    var manualTotalSeconds = 0;
    var manualCountDownTimer;
    var totalInactiveTime = 0;
    var inactiveTime = new Date().getTime();
    var staticTime = new Date().getTime();
    var staticTimeSecondsTotal = 0;
    var startBlurTime;

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
     * Updates the tab title and favicon if the timer is running
     * @param   {string} hoursUpdated - The hours part of the time.
     * @param   {string} minutesUpdated - The minutes part of the time.
     * @param   {string} secondsUpdated - The seconds part of the time.
     * @return  void
     */
    function generateTimedTab( hoursUpdated, minutesUpdated, secondsUpdated ) {
      tabHours = hoursUpdated;
      tabMinutes = minutesUpdated;
      tabSeconds = secondsUpdated;
      tabHours = checkPrependNumberZero( tabHours );
      tabMinutes = checkPrependNumberZero( tabMinutes );
      tabSeconds = checkPrependNumberZero( tabSeconds );
      var tabTimerText = tabHours + ":" + tabMinutes + ":" + tabSeconds + " - " + tabTitle;
      document.title = tabTimerText;

      if( faviconUrl === undefined ) {
        $( 'head' ).append('<link href="' + js_object_timer.clock_favicon_url + '" rel="shortcut icon" type="image/x-icon" />');
      } else {
            var link = document.querySelector( "link[rel*='icon']" ) || document.createElement( 'link' );
            link.type = 'image/x-icon';
            link.rel = 'shortcut icon';
            link.href = js_object_timer.as_favicon_url;
            document.getElementsByTagName( 'head' )[0].appendChild( link );
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
    function checkAutomaticStartDateLength( fullStartDate, e ) {
      if( fullStartDate.length === 0 ) {
        /** global: js_object_timer */
        alert( js_object_timer.start_empty );
        e.preventDefault();
      }
    }

    /**
     * Checks if the start date is empty
     * @param   {string} fullStartDate - The date string to check
     * @return  {void}
     */
    function checkAutomaticValidDate( fullStartDate, e ) {
      if( isValidDate( fullStartDate ) === false ) {
        /** global: js_object_timer */
        alert( js_object_timer.start_format );
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

    /**
     * Pause and start functionality on manual timer when button is clicked.
     * setInterval is used for the timer.
     */
    $( "#as_time_tracking_manual_pause_start" ).click( function( e ) {
      var manualStatus = $( "#as_time_tracking_manual_pause_start" ).text().toLowerCase();
      if( manualStatus == 'start' ) {
		    var currentHoursField = $( "#as_time_tracking_tickets_hours_field" ).val();
		    var currentMinutesField = $( "#as_time_tracking_tickets_minutes_field" ).val();
		    var currentSecondsField = $( "#as_time_tracking_tickets_seconds_field" ).val();

      	Clock.manualResume(currentHoursField, currentMinutesField, currentSecondsField);

        setReadOnlyFields( "#as_time_tracking_tickets_hours_field" );
        setReadOnlyFields( "#as_time_tracking_tickets_minutes_field" );
        setReadOnlyFields( "#as_time_tracking_tickets_seconds_field" );

        /** global: js_object_timer */
        $( "#as_time_tracking_manual_pause_start" ).text( js_object_timer.stop );
      } else if( manualStatus == 'stop' ) {
        Clock.pause( false ); 
        /** global: js_object_timer */
        $( "#as_time_tracking_manual_pause_start" ).text( js_object_timer.start );

        unsetReadOnlyFields( "#as_time_tracking_tickets_hours_field" );
        unsetReadOnlyFields( "#as_time_tracking_tickets_minutes_field" );
        unsetReadOnlyFields( "#as_time_tracking_tickets_seconds_field" );

        setEndTimeValues();
      }

      e.preventDefault();
    });

    /**
     * Pause and start functionality on automatic timer when button is clicked.
     * setInterval is used for the timer.
     */
    $( "#as_time_tracking_automatic_pause_start" ).click( function( e ) {
      e.preventDefault();

      var status = $( "#as_time_tracking_automatic_pause_start" ).text().toLowerCase();

      if( status === 'pause' ) {
        Clock.pause( false ); 

        /** global: js_object_timer */
        $( "#as_time_tracking_automatic_pause_start" ).text( js_object_timer.resume );
      } else {
         Clock.resume(); 

        // /** global: js_object_timer */
        $( "#as_time_tracking_automatic_pause_start" ).text( js_object_timer.pause );
      }
    });

    /**
     * Stop the timer when a button/link is pressed which will save/edit the tickets screen.
     * As setInterval is used for the timer it is cleared here.
     */
    $( "#publish, button[type='submit']" ).click( function() {
      //clearInterval( countDownTimer );
      var currentDate = new Date();
      var month = currentDate.getMonth() + 1;
      month = checkPrependNumberZero( month );
      var day = currentDate.getDate();
      day = checkPrependNumberZero( day );
      var hours = currentDate.getHours();
      hours = checkPrependNumberZero( hours );
      var minutes = currentDate.getMinutes();
      minutes = checkPrependNumberZero( minutes );
      var seconds = currentDate.getSeconds();
      seconds = checkPrependNumberZero( seconds );
      var dateTime = currentDate.getFullYear() + '-' + month + '-' + day + " " + hours + ":" + minutes + ":" + seconds;

      $( "#as_time_tracking_tickets_end_date_time" ).val( dateTime );
    });

    /**
     * Validate the fields when a update/submit button/link is clicked.
     * Alert boxes are used if validation fails and event prevention is used.
     */
    $( "#publish, button[type='submit']" ).click( function( e ) {
      if( js_object_timer.current_page === 'ticket' ) {
        //e.preventDefault();
        //Time recorded fields
        var hoursfield = $( "#as_time_tracking_tickets_hours_field" ).val();
        hoursfield = checkPrependNumerNotZero( hoursfield );
        var minutesfield = $( "#as_time_tracking_tickets_minutes_field" ).val();
        minutesfield = checkPrependNumerNotZero( minutesfield );
        var secondsfield = $( "#as_time_tracking_tickets_seconds_field" ).val();
        secondsfield = checkPrependNumerNotZero( secondsfield );
        var timeEntered = false;

        //Initial numeric checks and check if a time was entered
        valueCheckNumeric( hoursfield, js_object_timer.hours_num, e );
        valueCheckNumeric( minutesfield, js_object_timer.minutes_num, e );
        valueCheckNumeric( secondsfield, js_object_timer.seconds_num, e );

        if( $.isNumeric( hoursfield ) && $.isNumeric( minutesfield ) && $.isNumeric( secondsfield ) ) {
          timeEntered = true;
        }

        //Check if automatic/manual timer then do logic for each respective timer type
        if( $( "#as_time_tracking_automatic_timer_field_wrapper" ).length ) {
          //Start date validation in yyyy-mm-dd format
          var startDate = $( "#as_time_tracking_tickets_start_date_time" ).val();

          //Fix for IE not accepting the val as a date
          var a = startDate.split(" ");
          var d = a[0].split("-");
          var t = a[1].split(":");

          var dateCollected = new Date( d[0],(d[1]-1),d[2],t[0],t[1],t[2] );
          var yearCollected = dateCollected.getFullYear();
          var monthCollected = dateCollected.getMonth() + 1;
          monthCollected = checkPrependNumberZero( monthCollected );
          var dayCollected = dateCollected.getDate();
          dayCollected = checkPrependNumberZero( dayCollected );
          var fullStartDate = yearCollected + "-" + monthCollected + "-" + dayCollected;

          checkAutomaticStartDateLength( fullStartDate, e );
          checkAutomaticValidDate( fullStartDate, e );
          //Start time validation
          var startDateCollected = new Date( d[0],(d[1]-1),d[2],t[0],t[1],t[2] );
          var timeHours = startDateCollected.getHours();
          var timeMinutes = startDateCollected.getMinutes();
          var timeSeconds = startDateCollected.getSeconds();

          /** global: js_object_timer */
          valueCheckNumeric( timeHours, js_object_timer.hours_num, e );
          valueCheckNumeric( timeMinutes, js_object_timer.minutes_num, e );
          valueCheckNumeric( timeSeconds, js_object_timer.seconds_num, e );
        } else {
          //If time entered then do date/time validation
          if( timeEntered === true ) {
            //Date fields
            var endDate = $( "#as_time_tracking_tickets_end_date" ).val();
            var startDate = $( "#as_time_tracking_tickets_start_date" ).val();

            checkValueLengthEmpty( startDate, js_object_timer.start_empty, e );
            checkValueLengthEmpty( endDate, js_object_timer.end_empty, e );
            checkValueValidDate( startDate, js_object_timer.start_format, e );
            checkValueValidDate( endDate, js_object_timer.end_format, e );

            //Time fields
            var startDateHours = $( "#as_time_tracking_tickets_start_date_hours option:selected" ).text();
            var startDateMinutes = $( "#as_time_tracking_tickets_start_date_minutes option:selected" ).text();
            var startDateSeconds = $( "#as_time_tracking_tickets_start_date_seconds option:selected" ).text();

            valueCheckNumeric( startDateHours, js_object_timer.start_hours_num, e );
            valueCheckNumeric( startDateMinutes, js_object_timer.start_minutes_num, e );
            valueCheckNumeric( startDateSeconds, js_object_timer.start_seconds_num, e );

            var endDateHours = $( "#as_time_tracking_tickets_end_date_hours option:selected" ).text();
            var endDateMinutes = $( "#as_time_tracking_tickets_end_date_minutes option:selected" ).text();
            var endDateSeconds = $( "#as_time_tracking_tickets_end_date_seconds option:selected" ).text();

            valueCheckNumeric( endDateHours, js_object_timer.end_hours_num, e );
            valueCheckNumeric( endDateMinutes, js_object_timer.end_minutes_num, e );
            valueCheckNumeric( endDateSeconds, js_object_timer.end_seconds_num, e );

            //Check if end date is before start date
            var formatFullStartDate = startDate + " " + startDateHours + ":" + startDateMinutes + ":" + startDateSeconds;
            var fullEndDate = endDate + " " + endDateHours + ":" + endDateMinutes + ":" + endDateSeconds;

            if( endDateCompare( formatFullStartDate, fullEndDate ) === true ) {
              /** global: js_object_timer */
              alert( js_object_timer.end_start );
              //e.preventDefault();
            }
          }
        }
      }
     });

    /**
     *  Check if the automatic timer field has been set.
     *  If so, make the field disabled and set up the timer and
     *  hide the main metabox as it doesn't have fields to display inside it.
     */
    if( $( "#as_time_tracking_automatic_timer_field_wrapper" ).length ) {
      $( ".as_time_tracking_ticket_reply_time" ).hide();
      setReadOnlyFields("#as_time_tracking_tickets_hours_field");
      setReadOnlyFields("#as_time_tracking_tickets_minutes_field");
      setReadOnlyFields("#as_time_tracking_tickets_seconds_field");
      Clock.start();
    } else {
      //Set datepickers for manual setting
      $("#as_time_tracking_tickets_start_date").datepicker({
        dateFormat: "yy-mm-dd",
      });

      $("#as_time_tracking_tickets_end_date").datepicker({
        dateFormat: "yy-mm-dd",
      });

      $("#as_time_tracking_tickets_start_date").datepicker({ dateFormat: "yy-mm-yy" }).datepicker("setDate", new Date());
      $("#as_time_tracking_tickets_end_date").datepicker({ dateFormat: "yy-mm-yy" }).datepicker("setDate", new Date());
    }

    /**
     * Calculates difference between two second values.
     * @param   {integer} timeOne    - The first value to compare
     * @param   {integer} timeTwo    - The second value to compare
     * @return  {integer}
     */
    function calculateTimeSecondDifference( timeOne, timeTwo ) {
    	var returnVal = Math.floor( parseInt( (timeOne - timeTwo) / (1000) ) );
    	return parseInt( returnVal );
    }

    /**
     * Calculates all time taken into seconds
     * @param   {integer} hoursField      - The hours field value
     * @param   {integer} minutesField    - The minutes field value
     * @param   {integer} secondsField    - The seconds field value
     * @return  {integer}
     */
    function calculateInputValuesSeconds( hoursField, minutesField, secondsField ) {
    	var totalSeconds = 0;
    	var hoursSeconds = hoursField * 3600;
    	var minutesSeconds = minutesField * 60;
    	totalSeconds = hoursSeconds + minutesSeconds + secondsField;

    	return secondsField;
    }

    /**
     * Sets the end date time fields to the current time
     * @return void
     */
    function setEndTimeValues() {
      var currentDate = new Date();
      var currentHours = currentDate.getHours();
      currentHours = checkPrependNumberZero( currentHours );

      var currentMinutes = currentDate.getMinutes();
      currentMinutes = checkPrependNumberZero( currentMinutes );

      var currentSeconds = currentDate.getSeconds();
      currentSeconds = checkPrependNumberZero( currentSeconds );

      $( "#as_time_tracking_tickets_end_date_hours" ).val( currentHours ).change();
      $( "#as_time_tracking_tickets_end_date_minutes" ).val( currentMinutes ).change();
      $( "#as_time_tracking_tickets_end_date_seconds" ).val( currentSeconds ).change();
    }

    /**
     * Sets the start date time fields to the current time
     * @return void
     */
    function setStartTimeValues() {
      var currentDate = new Date();
      var currentHours = currentDate.getHours();
      currentHours = checkPrependNumberZero( currentHours );

      var currentMinutes = currentDate.getMinutes();
      currentMinutes = checkPrependNumberZero( currentMinutes );

      var currentSeconds = currentDate.getSeconds();
      currentSeconds = checkPrependNumberZero( currentSeconds );

      $( "#as_time_tracking_tickets_start_date_hours" ).val( currentHours ).change();
      $( "#as_time_tracking_tickets_start_date_minutes" ).val( currentMinutes ).change();
      $( "#as_time_tracking_tickets_start_date_seconds" ).val( currentSeconds ).change();
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
    }
   });
})( jQuery );
