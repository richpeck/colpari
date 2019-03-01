var SaveModel = Backbone.Model.extend( {

    parse: function( response, options )
    {
        response.fields = JSON.parse( response.fields );
        return response;
    }
});
