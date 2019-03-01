/**
 * Save Progress Passive Controller
 */
var nfSaveProgressPassiveController = Marionette.Object.extend({

    initialize: function( options ) {

        this.storage = window.localStorage;

        this.listenTo( nfRadio.channel( 'form' ), 'render:view', this.onFormRendered );
    },

    onFormRendered: function( formView ) {
        var formModel = formView.model;

        if( ! formModel.get( 'save_progress_passive_mode' ) ) return;

        var formData = this.storage.getItem( 'nfForm-' + formModel.get( 'id' ) );

        Backbone.Radio.channel( 'forms' ).request( 'save:updateFieldsCollection',
            formModel.get( 'id' ),
            JSON.parse( formData )
        );

        // if( ! formData ) return;
        //
        // _.each( formData, function( fieldValue, fieldID ){
        //     var fieldModel = formModel.get( 'fields' ).get( fieldID );
        //
        //     if( 'undefined' == typeof fieldModel ) return;
        //
        //     fieldModel.set( 'value', fieldValue );
        //     fieldModel.trigger( 'reRender' );
        // });

        // console.log( this.cookie.get( 'nfForm-' + formModel.get( 'id' ) ) );

        this.listenTo( nfRadio.channel( 'fields' ), 'change:modelValue', this.onChangeModelValue );
        this.listenTo( nfRadio.channel( 'form-' + formModel.get( 'id' ) ), 'submit:response', function(){
            this.storage.removeItem( 'nfForm-' + formModel.get( 'id' ) );
        } );
    },

    onChangeModelValue: function( fieldModel ) {

        var name = 'nfForm-' + fieldModel.get( 'formID' );

        var formID    = fieldModel.get( 'formID' );
        var formModel = Backbone.Radio.channel( 'app' ).request( 'get:form', formID );

        if( 'undefined' == typeof formModel ) return;
        if( ! formModel.get( 'save_progress_passive_mode' ) ) return;

        // var formData = this.cookie.get( name ) || {};
        // formData[ fieldModel.get( 'id' ) ] = fieldModel.get( 'value' );

        var fieldData = Backbone.Radio.channel( 'forms' ).request( 'save:fieldAttributes', formID );

        this.storage.setItem( name, JSON.stringify( fieldData ) );
    },

});
