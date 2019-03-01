var NF_SaveProgress = Marionette.Application.extend({

    initialize: function( options ){

        // Load Controllers.
        new nfSaveProgressActiveController();
        new nfSaveProgressSaveButtonController();
        new nfSaveProgressPassiveController();

        Backbone.Radio.channel( 'forms' ).reply( 'save:fieldAttributes', this.getfieldAttributes, this );
        Backbone.Radio.channel( 'forms' ).reply( 'save:updateFieldsCollection', this.updateFieldsCollection, this );
    },

    getfieldAttributes: function( formID ) {
        var formModel = Backbone.Radio.channel( 'app' ).request( 'get:form', formID );

        var fields = formModel.get( 'fields' ).filter( function( fieldModel, key ){
            if ( 'html' == fieldModel.get( 'type' ) ) return false;
            // If we don't have a value...
            // AND our field is anything other than a table editor...
            if ( ! fieldModel.get( 'value' ) && 'table_editor' != fieldModel.get( 'type' ) ) return false;
            // If the model is clean...
            // AND it has a type of anything other than file upload or table editor...
            if ( fieldModel.get( 'clean' ) && 'file_upload' != fieldModel.get( 'type' ) && 'table_editor' != fieldModel.get( 'type' ) ) return false;
            return true;
        });

        var omit = [
            /* core */ 'admin_label', 'element_class', 'required', 'container_class', 'custom_mask','mask', 'manual_key', 'drawerDisabled', 'input_limit', 'input_limit_msg', 'input_limit_type', 'help_text', 'desc_text', 'created_at', 'editActive', 'disabled', 'afterField', 'beforeField', 'classes', 'confirm_field', 'element_templates', 'errors', 'formID', 'key', 'label', 'label_pos', 'mirror_field', 'objectDomain', 'objectType', 'old_classname', 'order', 'parentType', 'placeholder', 'reRender', 'type', 'wrap_template',
            /* List Fields */ 'options',
            /* Table Editor */ 'hot', 'tableConfig',
            /* File Uploads */ 'uploadNonce', 'upload_types',
            /* Layout & Styles */ 'cellcid'
        ];

        // Attributes to be saved.
        var atts = fields.map( function( fieldModel, key ) {
            return _.omit( fieldModel.attributes, function( value, key, object ){

                // Omit function properties.
                if( 'function' == typeof value ) return true;

                // Omit known "non-essential" core attributes.
                if( -1 !== omit.indexOf( key ) ) return true;

                // Omit Layout & Styles attributes.
                if( -1 !== key.indexOf( '_styles_' ) ) return true;
               return false;
            });
        });
        return atts;
    },

    updateFieldsCollection: function( formID, savedFields ){
        var formModel = Backbone.Radio.channel( 'app' ).request( 'get:form', formID );
        var fieldsCollection = formModel.get( 'fields' );

        var defaults = formModel.get( 'loadedFields' );
        fieldsCollection.reset( defaults );

        _.each( savedFields, function( savedField ){
            var fieldID = parseInt( savedField.id );
            var field   = fieldsCollection.get( fieldID );
            var atts    = _.omit( savedField, [
                /* Core */ 'id', 'required',
                /* File Uploads */ 'uploadNonce', 'upload_types'
            ] );

            // Force `visible` attribute to a String
            if( 'undefined' != typeof atts.visible ) {
                atts.visible = atts.visible.toString();
            }

            if( 'undefined' != typeof field ) {
                field.set(atts);
            }
        });

        Backbone.Radio.channel( 'fields' ).trigger( 'reset:collection', fieldsCollection );
    }
});
