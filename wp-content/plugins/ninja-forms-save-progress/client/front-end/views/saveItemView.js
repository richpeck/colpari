var SaveItemView = Marionette.ItemView.extend({
    tagName: 'tr',
    className: 'nf-saves-item',
    template: '#tmpl-nf-save-item',

    events: {
        'click .load': function( e ){

            jQuery( e.target ).closest( '.nf-saves-cont' ).find( '.active' ).removeClass( 'active' );
            jQuery( e.target ).closest( 'tr' ).addClass( 'active' );

            var formID = this.model.get( 'form_id' );
            Backbone.Radio.channel( 'forms' ).request( 'save:updateFieldsCollection', formID,
                this.model.get( 'fields' )
            );

            var formModel = Backbone.Radio.channel( 'app' ).request( 'get:form', formID );
            if( 'undefined' != typeof formModel ){
                formModel.set( 'save_id', this.model.get( 'save_id' ) );
            }
        },
        'click .cancel': function( e ){
            jQuery( e.target ).closest( 'tr' ).removeClass( 'active' );

            var formID = this.model.get( 'form_id' );
            var formModel = Backbone.Radio.channel( 'app' ).request( 'get:form', formID );
            var fieldsCollection = formModel.get( 'fields' );
            var defaults = formModel.get( 'loadedFields' );
            fieldsCollection.reset( defaults );
        }
    },

    templateHelpers: function(){
        var view = this;
        return {
            updated: this.model.get( 'updated' ),
            columns: function(){
                var formModel = nfRadio.channel( 'app' ).request( 'get:form', view.model.get( 'form_id' ) );
                var columns = formModel.get( 'save_progress_table_columns' );
                var $return = '';
                _.each( columns, function( column ){
                    var fieldModel = formModel.get( 'fields' ).find( function( field ){
                       return column.field ==  field.get( 'key' );
                    });
                    var savedField = view.model.get( 'fields' ).find( function( field ){
                        if( 'undefined' != typeof ( fieldModel ) ) {
                            return fieldModel.get( 'id' ) == field.id;
                        } else {
                            return '';
                        }
                    });
                    var fieldValue = ( 'undefined' != typeof savedField ) ? savedField.value : '';
                    $return += '<td>' + fieldValue + '</td>';
                })
                return $return;
            }
        }
    }
});
