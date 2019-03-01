var SaveEmptyView = Marionette.ItemView.extend({
    tagName: 'nf-save',
    template: '#tmpl-nf-save-empty',

    initialize: function() {
        this.on( 'render', this.afterRender );
    },

    afterRender: function(){
        var that = this;
        setTimeout(function(){
            jQuery( that.$el ).slideUp( 'slow', function(){
                that.remove();
            });
        }, 2000);
    }
});
