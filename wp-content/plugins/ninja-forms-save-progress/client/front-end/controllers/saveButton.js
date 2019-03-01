/**
 * Save Progress Save Button Controller
 */
var nfSaveProgressSaveButtonController = Marionette.Object.extend({

    endpoint: '',

    initialize: function( options ) {
        this.endpoint = nfSaveProgress.restApiEndpoint || '';

        Backbone.Radio.channel( 'forms' ).reply( 'maybe:validate', this.maybeValidate );

        this.listenTo( nfRadio.channel( 'save' ), 'init:model', this.registerHandlers );
        this.listenTo( nfRadio.channel( 'save' ), 'render:view', this.maybeHide );
        this.listenTo( nfRadio.channel( 'save' ), 'click:field', this.click );
    },

    maybeValidate: function( formModel ) {
        if( formModel.getExtra( 'saveProgress' ) ) return false;
    },

    registerHandlers: function( fieldModel ) {
         var formChannel = Backbone.Radio.channel( 'form-' + fieldModel.get( 'formID' ) );
        fieldModel.listenTo( formChannel, 'before:submit', this.beforeSubmit, fieldModel );
        fieldModel.listenTo( formChannel, 'submit:cancel',   this.reset.bind( fieldModel ) );
        fieldModel.listenTo( formChannel, 'submit:response', this.reset.bind( fieldModel ) );
        fieldModel.listenTo( formChannel, 'submit:response', this.response.bind( this ) );

         var formID = fieldModel.get( 'formID' );
         fieldModel.listenTo( formChannel, 'submit:failed', function(){
             Backbone.Radio.channel( 'form-' + formID ).request( 'remove:extra', 'saveProgress' );
         } );
         fieldModel.listenTo( formChannel, 'submit:response', function(){
             Backbone.Radio.channel( 'form-' + formID ).request( 'remove:extra', 'saveProgress' );
         } );

        // Add progress to Save field submission data.
        Backbone.Radio.channel( 'save' ).reply( 'get:submitData', function( fieldData ){
            formID = fieldModel.get( 'formID' );
            var formModel = Backbone.Radio.channel( 'app' ).request( 'get:form', formID );

            if( 'undefined' != typeof formModel ){
                fieldData.save_id = formModel.get( 'save_id' );
            }

            return fieldData;
        } );
        this.listenTo( nfRadio.channel( 'submit' ), 'init:model', this.registerSubmitHandlers );
    },

    registerSubmitHandlers: function( fieldModel ) {
        fieldModel.listenTo( nfRadio.channel( 'form-' + fieldModel.get( 'formID' ) ), 'before:submit', function( formModel ){

            // If this isn't a save, then bail.
            if( ! Backbone.Radio.channel( 'form-' + formModel.get( 'id' ) ).request( 'get:extra', 'saveProgress' ) ) return;

            // Reset the submit button's label, because this is a save, not a submit.
            if ( 'undefined' != typeof this.get( 'oldLabel' ) ) {
                this.set( 'label', this.get( 'oldLabel' ) );
            }
            this.set( 'disabled', true );
            this.trigger( 'reRender' );
        }, fieldModel );
    },

    beforeSubmit: function() {
        this.set( 'disabled', true );
        this.trigger( 'reRender' );
    },

    maybeHide: function( fieldView ) {
        if( nfSaveProgress.currentUserID ) return;
        setTimeout(function(){
            fieldView.remove();
        }, 500);
    },

    click: function( e, fieldModel ) {

        fieldModel.set( 'disabled', true );
        fieldModel.set( 'oldLabel', fieldModel.get( 'label' ) );
        fieldModel.set( 'label', fieldModel.get( 'processing_label' ) );
        fieldModel.trigger( 'reRender' );

        var formID    = fieldModel.get( 'formID' );
        var formModel = Backbone.Radio.channel( 'app' ).request( 'get:form', formID );

        // Flag the submission as a Save.
        var saveData = Backbone.Radio.channel( 'forms' ).request( 'save:fieldAttributes', formID );
        Backbone.Radio.channel( 'form-' + formID ).request( 'add:extra', 'saveProgress', saveData );

        // Submit the form.
        Backbone.Radio.channel( 'form-' + formID ).request( 'submit', formModel );
    },

    response: function( response, textStatus, jqXHR, formID ) {

        /* If we are hiding the form, then also hide the saves table. */
        if( 1 == response.data.settings.hide_complete ){
            jQuery( '#formSave' + response.data.form_id ).hide();
        }
    },

    reset: function(){
        console.log( this );
        this.set( 'disabled', false );
        if( 'undefined' != typeof this.get( 'oldLabel' ) ) {
            this.set('label', this.get('oldLabel'));
        }
        this.trigger( 'reRender' );
    }

});
