var nfUserManagement = Marionette.Object.extend( {
    initialize: function() {
        this.listenTo( nfRadio.channel( 'fields' ), 'change:field', this.removeError );
    },

    removeError: function( el, model ) {
        nfRadio.channel( 'fields' ).request('remove:error', model.get( 'id' ), 'user-management');
    }
});

jQuery( document ).ready( function( $ ) {
   new nfUserManagement();
});