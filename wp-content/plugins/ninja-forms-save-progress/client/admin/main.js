jQuery( document ).ready( function(){

    var nfSaveProgressFilter = {
        addQueryArg: function( key, value, url){
            var parts = url.split( '?' );
            var sep = ( 1 == parts.length ) ? '?' : '&';
            return url + sep + key + '=' + value;
        }
    };

    jQuery( '.js--nf-saves-filter' ).on( 'change', function () {

        var url = nfSaveProgress.url
        jQuery( this ).parent( '.tablenav' ).find( '.js--nf-saves-filter' ).each( function(){
            var key = jQuery( this ).attr( 'name' );
            var value = jQuery( this ).val();
            url = nfSaveProgressFilter.addQueryArg( key, value, url );
        });

        window.location.href = url;
    });

    /**
     * Thickbox
     */

    jQuery( '.js--nf-saves-thickbox' ).hover( function() {
        var saveID = jQuery( this ).data( 'save-id' );
        var updated = jQuery( this ).data( 'updated' );
        var convertURL = jQuery( this ).data( 'convert-url' );
        var fields = JSON.parse( nfSaveProgress.progress[ saveID ] );
        var formFields = nfSaveProgress.fields || {};

        jQuery( '#nf-save-progress-modal' ).find( '.js--updated' ).html( updated );

        var table = jQuery( '#nf-save-progress-modal' ).find( 'dl' );
        table.html( '' ); /* Clear the contents */
        _.each( fields, function( field ){
            var id    = field.id || 0;
            var type = formFields[ id ].type || '';
            if( ! type || [ 'save', 'submit', 'html' ].includes( type ) ) return;
            var label = formFields[ id ].label || '';
            var value = field.value || '';
            table.append( '<dt><strong>' + label + '</strong></dt><dd>' + value + '</dd>');
        } );

        // Set convert button href.
        jQuery( '#nf-save-progress-modal-convert' ).attr( 'href', convertURL );
    });
});
