( function( $ ) {
  jQuery( document ).ready( function( $ ) {
    /**
     * Gets the date values from the filter.
     * @param   {string} from - The from date value.
     * @param   {string} to   - The to date value.
     * @return  array
     */
    function calculateDateRange( from, to ) {
      var date = [];
      var dateFrom = $( from ).val();
      var dateTo = $( to ).val();

      /** If any date field is empty we just set the start date to the current date
       *  and the end date 30 days ahead. */
      if( dateFrom.length === 0 || dateTo.length === 0 ) {
        var currentDate = new Date();
        var year = currentDate.getFullYear();
        var month = currentDate.getMonth() + 1;
        month = as_time_tracking_filter_prepend_zero_single_digit( month );
        var day = currentDate.getDate();
        day = as_time_tracking_filter_prepend_zero_single_digit( day );
        var formattedDate = year + "-" + month + "-" + day;
        var futureDate = new Date();
        futureDate.setDate( futureDate.getDate() + 30 );
        var futureYear = futureDate.getFullYear();
        var futureMonth = futureDate.getMonth() + 1;
        futureMonth = as_time_tracking_filter_prepend_zero_single_digit( futureMonth );
        var futureDay = futureDate.getDate();
        futureDay = as_time_tracking_filter_prepend_zero_single_digit( futureDay );
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
     * Prepends a "0" to a value if it is single digit.
     * @param   {string} val_to_check - The value to check.
     * @return  array
     */
    function as_time_tracking_filter_prepend_zero_single_digit( val_to_check ) {
      var return_val = val_to_check;

      if( val_to_check.toString().length === 1 ) {
        return_val = "0" + return_val;
      } else {
        return_val = "";
      }

      return return_val;
    }
  });
})( jQuery );
