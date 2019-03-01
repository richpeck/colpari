var nfSaveProgressBuilderController = Marionette.Object.extend({
    initialize: function(){
        Backbone.Radio.channel( 'actions' ).reply( 'get:mainContentTemplate', this.getMainContentTemplate, this );
        Backbone.Radio.channel( 'actions' ).reply( 'get:actionItemTemplate',  this.getActionItemTemplate, this );
    },

    getMainContentTemplate: function() {
        if( ! this.hasSaveButton() ) return;
        return '#tmpl-nf-action-table--saves';
    },

    getActionItemTemplate: function() {
        if( ! this.hasSaveButton() ) return;
        return '#tmpl-nf-action-item--saves';
    },

    hasSaveButton: function() {
        var formModel = Backbone.Radio.channel('app').request('get:formModel')
        return ( 'undefined' != typeof formModel.get('fields').findWhere({type: 'save'}) );
    }
});

jQuery( document ).ready( function( $ ) {
    new nfSaveProgressBuilderController();
});
