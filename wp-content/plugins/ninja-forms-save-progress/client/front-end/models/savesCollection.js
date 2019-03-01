var SavesCollection = Backbone.Collection.extend( {
    model: SaveModel,

    url: function() {
        var formID = this.formModel.get( 'id' );
        return nfSaveProgress.restApiEndpoint + 'saves/' + formID;
    },

    initialize: function( models, options ) {
        this.formModel = options.formModel;
    },

    parse: function( response ){
        if( 'undefined' == typeof response.saves ) return;
        return response.saves;
    },

    setAuthHeaders: function( xhr ) {
        // If we have a localized nonce, pass that along with each sync.
        if ( 'undefined' !== typeof wpApiSettings.nonce ) {
            xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
        }
    },

    // https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/
    sync: function( method, model, options ) {
        options = options || {};
        options.beforeSend = this.setAuthHeaders;

        // Continue by calling Bacckbone's sync.
        return Backbone.sync( method, model, options );
    }
});
