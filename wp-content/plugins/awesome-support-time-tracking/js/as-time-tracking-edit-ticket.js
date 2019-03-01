( function( $ ) {
  jQuery( document ).ready( function( $ ) {
    //Hide admin notice when we couldn't delete certain tickets. This is because the counter is incorrect.
    if( $( "#as_time_tracking_admin_notice_ticket" ).length > 0 && $( "#message" ).length > 0 ) {
      $( "#message" ).hide();
    }
  });
})( jQuery );