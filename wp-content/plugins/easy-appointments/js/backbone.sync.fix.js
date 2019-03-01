Backbone.ajax = function() {
    var args = Array.prototype.slice.call(arguments, 0)[0];
    var change = {};

    if(args.type === 'PUT' || args.type === 'DELETE') {
        change.type = 'POST';
        change.url = args.url + '&_method=' + args.type;
    }

    var newArgs = _.extend(args, change);
    return Backbone.$.ajax.apply(Backbone.$, [newArgs]);
};