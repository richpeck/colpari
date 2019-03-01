EA.AppRouter = Backbone.Router.extend({
    current: null,
    routes: {
    	"custumize":"custumize",
        "staff/": "staff",
        "services/": "services",
        "connection/": "connections",
        "locations/": "location",
        "custumize/": "custumize",
        "tools/": "tools",
        "": 'location'
    },

    initialize: function () {
        var currentHash = window.location.hash;

        mainView.selectHash(currentHash);
    },

    clearState : function() {
        if(this.current != null) {
            this.current.destroy_view();
            
            // FIX
            mainView.addContainer();
        }
    },
    setState: function(newState) {
    	this.current = newState;
        // FIX back/forward navigation    
        var hash = window.location.hash;

        if(hash === '') {
            hash = '#locations/';
        }
    
        var tab = mainView.$el.find('[href="' + hash + '"]')[0];

        mainView.select({ target : tab});
    
    }
});

// Instantiate the router
var app_router = new EA.AppRouter;

// Services
app_router.on('route:services', function () {
    this.clearState();

    var services = new EA.ServicesView({
        el: '#tab-content'
    });

    this.setState(services);
});

// Locations
app_router.on('route:location', function () {
    this.clearState();

    var locations = new EA.LocationsView({
        el: '#tab-content'
    });

    this.setState(locations);
});

// Staff
app_router.on('route:staff', function () {
    this.clearState();

    var staff = new EA.StaffView({
        el: '#tab-content'
    });

    this.setState(staff);
});

// Connections
app_router.on('route:connections', function () {
    this.clearState();

    var connections = new EA.ConnectionsView({
        el: '#tab-content'
    });

    this.setState(connections);
});

// Customize
app_router.on('route:custumize', function () {
    this.clearState();

    var custumize = new EA.CustumizeView({
        el: '#tab-content'
    });

    this.setState(custumize);
});

app_router.on('route:tools', function () {
    this.clearState();

    var custumize = new EA.ToolsView({
        el: '#tab-content'
    });

    this.setState(custumize);
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();