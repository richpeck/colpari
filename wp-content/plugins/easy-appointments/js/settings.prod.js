(function($) {

    var EA = {};

    ;

    /**
     *
     * @param time string value like 23:00
     * @returns {string}
     */
    function formatTime(time) {
        var timeFormat = ea_settings.time_format;

        if (typeof timeFormat === 'undefined') {
            return time;
        }

        var m = moment(time, ['HH:mm']);

        if (!m.isValid()) {
            return '--:--';
        }

        if (timeFormat === 'am-pm') {
            return m.format('h:mm A');
        }

        return m.format('HH:mm');
    }

    /**
     *
     * @param date
     */
    function formatDate(date) {
        var dateFormat = ea_settings.date_format;

        if (typeof dateFormat === 'undefined') {
            return date;
        }

        var m = moment(date, ['YYYY-MM-DD']);

        if (!m.isValid()) {
            return '-';
        }

        return m.format(dateFormat);
    }

    function formatDateTime(datetime) {

        if (typeof datetime === 'undefined' || datetime.length < 10) {
            return datetime;
        }

        var parts = datetime.split(' ');

        if (parts.length !== 2) {
            return datetime;
        }

        return formatDate(parts[0]) + ' ' + formatTime(parts[1]);
    }

    _.mixin({
        formatTime:formatTime,
        formatDate:formatDate,
        formatDateTime:formatDateTime
    });    Backbone.ajax = function() {
        var args = Array.prototype.slice.call(arguments, 0)[0];
        var change = {};

        if(args.type === 'PUT' || args.type === 'DELETE') {
            change.type = 'POST';
            change.url = args.url + '&_method=' + args.type;
        }

        var newArgs = _.extend(args, change);
        return Backbone.$.ajax.apply(Backbone.$, [newArgs]);
    };    /**
     * Single location
     */
    EA.Location = Backbone.Model.extend({
        defaults : {
            name:"",
            address: "",
            location: "",
            cord: null
        },

        url: function() { return ajaxurl+'?action=ea_location&id=' + encodeURIComponent(this.id) },

        toJSON : function() {
            var attrs = _.clone( this.attributes );
            return attrs;
        }
    });    /**
     * Service model
     */
    EA.Service = Backbone.Model.extend({
        defaults : {
            name:"",
            duration: 60,
            slot_step: 60,
            price: 10
        },
        url : function() {
            return ajaxurl+'?action=ea_service&id=' + this.id;
        },
        toJSON : function() {
            var attrs = _.clone( this.attributes );
            return attrs;
        }
    });    /**
     * Service model
     */
    EA.Worker = Backbone.Model.extend({
        defaults : {
            name:"",
            description : "",
            email: "",
            phone: ""
        },
        url : function() {
            return ajaxurl+'?action=ea_worker&id=' + this.id;
        },
        toJSON : function() {
            var attrs = _.clone( this.attributes );
            return attrs;
        }
    });    /**
     * Single connection
     */
    EA.Connection = Backbone.Model.extend({
        defaults : {
            group_id : null,
            location : null,
            service : null,
            worker : null,
            day_of_week : [],
            time_from : null,
            time_to : null,
            day_from : '2018-01-01',
            day_to : '2020-01-01',
            is_working : 0
        },

        url: function() { return ajaxurl+'?action=ea_connection&id=' + encodeURIComponent(this.id) },

        toJSON: function() {
            var attrs = _.clone( this.attributes );

            return attrs;
        },

        parse: function(data, options) {

            if(typeof data.day_of_week !== "undefined" && data.day_of_week != null) {
                data.day_of_week = data.day_of_week.split(',');
            } else {
                // console.log(this.get('day_of_week').split(','));
                this.set('day_of_week', this.get('day_of_week'));
            }

            if(typeof data.time_from !== "undefined" && typeof data.time_to !== "undefined") {

                if(data.time_from.length === 8) {
                    data.time_from = data.time_from.substring(0, 5);
                }

                if(data.time_to.length === 8) {
                        data.time_to = data.time_to.substring(0, 5);
                }
            }

            return data;
        },

        save: function(attrs, options) {
            options || (options = {});
            attrs || (attrs = _.clone(this.attributes));

            attrs.day_of_week = attrs.day_of_week.join(',');

            return Backbone.Model.prototype.save.call(this, attrs, options);
        }
    });    /**
     * Single Appointment
     */
    EA.Appointment = Backbone.Model.extend({
        defaults : {
            location    : null,
            service     : null,
            worker      : null,
            // name        : '',
            // email       : '',
            // phone       : '',
            date        : null,
            start       : null,
            end         : null,
            end_date    : null,
            description : null,
            status      : null,
            user        : null,
            price       : 0
        },

        url: function() { return ajaxurl+'?action=ea_appointment&id=' + encodeURIComponent(this.id) },

        toJSON : function() {
            var attrs = _.clone( this.attributes );
            return attrs;
        },

        parse: function(data, options) {

            if(typeof data.start !== "undefined" && data.start != null && data.start.length === 8) {
                data.start = data.start.substring(0, 5);
            }

            if(typeof data.created !== "undefined" && data.created.length === 19) {
                data.created = data.created.substring(0, 16);
            }

            return data;
        }
    });    /**
     * Locations collection
     */
    EA.Locations = Backbone.Collection.extend({
        url : ajaxurl+'?action=ea_locations',
        model: EA.Location,
        cacheData: function() {
            if(typeof eaData !== 'undefined') {
                eaData.Locations = this.toJSON();
            }
        }
    });    /**
     * Services collection
     */
    EA.Services = Backbone.Collection.extend({
        url : ajaxurl+'?action=ea_services',
        model: EA.Service,
        parse: function(response) {
        	// console.log(response);
        	return response;
      	},
      	cacheData: function() {
            if(typeof eaData !== 'undefined') {
                eaData.Services = this.toJSON();
            }
        }
    });    /**
     * Workers collection
     */
    EA.Workers = Backbone.Collection.extend({
        url : ajaxurl+'?action=ea_workers',
        model: EA.Worker,
        cacheData: function() {
            if(typeof eaData !== 'undefined') {
                eaData.Workers = this.toJSON();
            }
        }
    });    /**
     * Connections collection
     */
    EA.Connections = Backbone.Collection.extend({
        url : ajaxurl+'?action=ea_connections',
        model: EA.Connection
    });    /**
     * Appointments collection
     */
    EA.Appointments = Backbone.Collection.extend({
        url : ajaxurl+'?action=ea_appointments',
        model: EA.Appointment
    });    /**
     * Main Admin View
     * Renders Admin tab panel
     *
     **/
    EA.MainView = Backbone.View.extend({
        el: jQuery('#wpbody-content'),

        template: _.template(jQuery("#ea-appointments-main").html()),

        events: {
            "change .filter-part input": "filterChange",
            "change .filter-part select": "filterChange",
            "click .refresh-list": "refreshList",
            "click .add-new": "addNew",

            "change #ea-filter-locations": "filterLocationChanged",
            "change #ea-filter-services" : "filterServiceChanged"
        },

        initialize: function () {
            jQuery.datepicker.setDefaults(jQuery.datepicker.regional[ea_settings.datepicker]);

            // Empty array of connections
            this.collection = new EA.Appointments();

            if (typeof eaData !== 'undefined') {
                // In page cache
                this.locations = new EA.Locations(eaData.Locations);
                this.services = new EA.Services(eaData.Services);
                this.workers = new EA.Workers(eaData.Workers);
            } else {
                // Get from server
                this.locations = new EA.Locations();
                this.services = new EA.Services();
                this.workers = new EA.Workers();

                this.locations.fetch();
                this.services.fetch();
                this.workers.fetch();
            }

            this.render();

            this.setDefaults();

            // Bind the reset event
            this.collection.bind("reset", this.showRows, this);

            // Get data from server
            // this.collection.fetch( {reset:true} );
            this.filterChange();
        },

        /**
         * Set defaults if there are one
         */
        setDefaults: function () {
            if (this.locations.length === 1) {
                this.$el.find('#ea-filter-locations').val(this.locations.at(0).get('id'));
            }

            if (this.services.length === 1) {
                this.$el.find('#ea-filter-services').val(this.services.at(0).get('id'));
            }

            if (this.workers.length === 1) {
                this.$el.find('#ea-filter-workers').val(this.workers.at(0).get('id'));
            }
        },

        render: function () {
            this.$el.empty();

            this.$el.html(this.template({cache: eaData}));

            // From datepicker
            this.$el.find('#ea-filter-from').datepicker({
                dateFormat: jQuery.datepicker.regional[ea_settings.datepicker].dateFormat
            });

            this.$el.find('#ea-filter-from').datepicker('setDate', this.getMonday(new Date()));

            // To datepicker
            this.$el.find('#ea-filter-to').datepicker({
                dateFormat: jQuery.datepicker.regional[ea_settings.datepicker].dateFormat
            });

            this.$el.find('#ea-filter-to').datepicker('setDate', this.getSunday(new Date()));

            this.showRows();

            return this;
        },

        showRows: function () {
            var self = this; // so you can use this inside the each function

            var row_container = self.$el.find("#ea-appointments");

            row_container.empty();

            this.collection.each(function (appointment) { // iterate through the collection
                var appointmentView = new EA.AppointmentView({
                    model: appointment
                });

                appointmentView.setData(
                    self.locations,
                    self.services,
                    self.workers
                );

                appointmentView.render();

                row_container.append(appointmentView.$el);
            });

            this.showMessage('');
        },

        // get current Filter
        getFilter: function () {
            var filters = this.$el.find('input, select');

            var filter = {};

            jQuery.each(filters, function (index, elem) {
                var value = jQuery(elem).val();
                var col = jQuery(elem).data('c');

                if (value !== '') {

                    if (col === 'from') {
                        value = moment(jQuery(elem).datepicker('getDate')).format('YYYY-MM-DD');
                    } else if (col === 'to') {
                        value = moment(jQuery(elem).datepicker('getDate')).format('YYYY-MM-DD');
                    }

                    filter[col] = value;
                }
            });

            return filter;
        },

        // Filter has changed
        filterChange: function (e) {
            if (typeof e !== 'undefined' && jQuery(e.currentTarget).is('#ea-period')) {
                switch (jQuery(e.currentTarget).val()) {
                    case 'week':
                        this.setThisWeekPeriod();
                        break;
                    case 'month':
                        this.setThisMonthPeriod();
                        break;
                    case 'today':
                        this.setThisDayPeriod();
                        break;
                    default:
                        return;
                }
            }

            var filter = this.getFilter();
            var that = this;

            this.showMessage('Loading table...', true);

            this.collection.fetch({data: jQuery.param(filter), reset: true}, {
                error: function (response) {
                    that.showMessage('');
                    alert('Error, try refresh again.');
                }
            });
        },

        /**
         *
         */
        filterLocationChanged: function() {
            var location = this.$el.find('#ea-filter-locations').val();

            // enabled all the fields
            this.$el.find('#ea-filter-services').children().prop("disabled", false).show();
            this.$el.find('#ea-filter-workers').children().prop("disabled", false).show();

            if (location === '') {
                return;
            }

            var services = [];
            var workers = [];

            jQuery.each(ea_connections, function(index, connection) {
                if (connection.location === location) {
                    if (_.indexOf(services, connection.service) === -1 ) {
                        services.push(connection.service);
                    }

                    if (_.indexOf(workers, connection.worker) === -1 ) {
                        workers.push(connection.worker);
                    }
                }
            });

            this.$el.find('#ea-filter-services').children().each(function(index, element) {
                var value = jQuery(element).attr('value');

                if (value === '') {
                    return;
                }

                if (_.indexOf(services, value) === -1 ) {
                    jQuery(element).prop('disabled', true).hide();
                }
            });

            this.$el.find('#ea-filter-workers').children().each(function(index, element) {
                var value = jQuery(element).attr('value');

                if (value === '') {
                    return;
                }

                if (_.indexOf(workers, value) === -1 ) {
                    jQuery(element).prop('disabled', true).hide();
                }
            });
        },

        filterServiceChanged: function() {
            var location = this.$el.find('#ea-filter-locations').val();
            var service = this.$el.find('#ea-filter-services').val();

            // enabled all the fields
            this.$el.find('#ea-filter-workers').children().prop("disabled", false).show();

            var workers = [];

            jQuery.each(ea_connections, function(index, connection) {
                if (connection.location === location && connection.service === service) {
                    if (_.indexOf(workers, connection.worker) === -1 ) {
                        workers.push(connection.worker);
                    }
                }
            });

            this.$el.find('#ea-filter-workers').children().each(function(index, element) {
                var value = jQuery(element).attr('value');

                if (value === '') {
                    return;
                }

                if (_.indexOf(workers, value) === -1 ) {
                    jQuery(element).prop('disabled', true).hide();
                }
            });
        },

        addNew: function (e) {
            e.preventDefault();

            var appointment = new EA.Appointment();

            var location = this.$el.find('#ea-filter-locations').val();
            var service = this.$el.find('#ea-filter-services').val();
            var worker = this.$el.find('#ea-filter-workers').val();

            if (location !== '') {
                appointment.set('location', location);
            }

            if (service !== '') {
                appointment.set('service', service);
            }

            if (worker !== '') {
                appointment.set('worker', worker);
            }

            this.collection.add(appointment, {at: 0});

            var appointmentView = new EA.AppointmentView({
                model: appointment
            });

            appointmentView.setData(
                this.locations,
                this.services,
                this.workers
            );

            this.$el.find("#ea-appointments").prepend(appointmentView.$el);

            appointmentView.edit();
        },

        /**
         * Refresh list
         */
        refreshList: function (e) {
            e.preventDefault();

            this.filterChange();
        },

        getMonday: function (d) {
            d = new Date(d);
            var day = d.getDay();
            var diff = d.getDate() - day + (day == 0 ? -6 : 1); // adjust when day is sunday
            return new Date(d.setDate(diff));
        },

        getSunday: function (d) {
            d = new Date(d);
            var day = d.getDay();
            var diff = d.getDate() + (day == 0 ? 0 : (7 - day)); // adjust when day is sunday
            return new Date(d.setDate(diff));
        },

        showMessage: function (text, hold) {
            var onHold = hold || false;

            if (onHold) {
                this.$el.find('#status-msg').text(text).show();
            } else {
                this.$el.find('#status-msg').text(text).show().delay(2000).fadeOut();
            }
        },

        setThisMonthPeriod: function () {
            var date = new Date(), y = date.getFullYear(), m = date.getMonth();
            var firstDay = new Date(y, m, 1);
            var lastDay = new Date(y, m + 1, 0);

            this.$el.find('#ea-filter-from').datepicker('setDate', firstDay);
            this.$el.find('#ea-filter-to').datepicker('setDate', lastDay);
        },

        setThisWeekPeriod: function () {
            this.$el.find('#ea-filter-from').datepicker('setDate', this.getMonday(new Date()));
            this.$el.find('#ea-filter-to').datepicker('setDate', this.getSunday(new Date()));
        },

        setThisDayPeriod: function () {
            var tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            this.$el.find('#ea-filter-from').datepicker('setDate', new Date());
            this.$el.find('#ea-filter-to').datepicker('setDate', tomorrow);
        }
    });    /**
     *
     */
    EA.AppointmentView = Backbone.View.extend({

        tagName: "tr",

        // show template
        template_show : _.template( jQuery("#ea-tpl-appointment-row").html() ),

        // edit template
        template_edit : _.template( jQuery("#ea-tpl-appointment-row-edit").html() ),

        // select times template
        template_times : _.template( jQuery("#ea-tpl-appointment-times").html() ),

        template : null,

        edit_mode : false,

        events: {
            "click .btn-edit"   : "edit",
            "click .btn-clone"  : "clone",
            "dblclick"          : "edit",
            "click .btn-del"    : "removeItem",
            "click .btn-save"   : "save",
            "click .btn-cancel" : "cancel",
            "keydown input"     : "keydownEvent",
            "keydown select"    : "keydownEvent",
            "change .app-fields": "changeApp",
            "change .time-start": "setEndTimeApp",
            "change .ea-service": "serviceChange",

            "change #ea-input-locations": "locationChanged",
            "change #ea-input-services" : "serviceChanged"
        },

        initialize: function () {
            this.template = this.template_show;
        },

        render: function () {
            var self = this;

            var renderedContent = this.template( {
                row       : this.model.toJSON(),
                cache     : eaData
            } );

            jQuery(this.el).html( renderedContent );

            this.setDefaults();

            this.$el.addClass('ea-row');

            this.locationChanged();
            this.serviceChanged();

            return this;
        },

        /**
         * Set defaults if there are one
         */
        setDefaults: function() {
            if (eaData.Locations.length === 1) {
                this.$el.find('#ea-input-locations').val(eaData.Locations[0].id);
            }

            if (eaData.Services.length === 1) {
                this.$el.find('#ea-input-services').val(eaData.Services[0].id);
                this.serviceChange();
            }

            if (eaData.Workers.length === 1) {
                this.$el.find('#ea-input-workers').val(eaData.Workers[0].id);
            }

        },

        /**
         *
         */
        locationChanged: function() {
            var location = this.$el.find('#ea-input-locations').val();

            // enabled all the fields
            this.$el.find('#ea-input-services').children().prop("disabled", false).show();
            this.$el.find('#ea-input-workers').children().prop("disabled", false).show();

            if (location === '') {
                return;
            }

            var services = [];
            var workers = [];

            jQuery.each(ea_connections, function(index, connection) {
                if (connection.location === location) {
                    if (_.indexOf(services, connection.service) === -1 ) {
                        services.push(connection.service);
                    }

                    if (_.indexOf(workers, connection.worker) === -1 ) {
                        workers.push(connection.worker);
                    }
                }
            });

            this.$el.find('#ea-input-services').children().each(function(index, element) {
                var value = jQuery(element).attr('value');

                if (value === '') {
                    return;
                }

                if (_.indexOf(services, value) === -1 ) {
                    jQuery(element).prop('disabled', true).hide();
                }
            });

            this.$el.find('#ea-input-workers').children().each(function(index, element) {
                var value = jQuery(element).attr('value');

                if (value === '') {
                    return;
                }

                if (_.indexOf(workers, value) === -1 ) {
                    jQuery(element).prop('disabled', true).hide();
                }
            });
        },

        serviceChanged: function() {
            var location = this.$el.find('#ea-input-locations').val();
            var service = this.$el.find('#ea-input-services').val();

            // enabled all the fields
            this.$el.find('#ea-input-workers').children().prop("disabled", false).show();

            var workers = [];

            jQuery.each(ea_connections, function(index, connection) {
                if (connection.location === location && connection.service === service) {
                    if (_.indexOf(workers, connection.worker) === -1 ) {
                        workers.push(connection.worker);
                    }
                }
            });

            this.$el.find('#ea-input-workers').children().each(function(index, element) {
                var value = jQuery(element).attr('value');

                if (value === '') {
                    return;
                }

                if (_.indexOf(workers, value) === -1 ) {
                    jQuery(element).prop('disabled', true).hide();
                }
            });
        },

        /**
         *
         */
        edit: function() {
            var self = this;

            if(this.edit_mode) {
                return;
            }

            if(this.$el.hasClass('ea-editing')) {
                return;
            }

            // Edit class
            this.$el.addClass('ea-editing');

            this.template = this.template_edit;
            this.render();

            this.$el.find('select, input').first().focus();

            // this.$el.find('[data-prop="start"]').timepicker();
            var datepickerElement = this.$el.find('[data-prop="date"]');

            datepickerElement.datepicker({
                dateFormat: jQuery.datepicker.regional[ea_settings.datepicker].dateFormat,
                minDate: 0
            });

            datepickerElement.datepicker("setDate", moment(this.model.get('date'), "YYYY-MM-DD").toDate());

            this.changeApp();

            this.edit_mode = true;
        },

        /**
         * Clone object
         * @param e
         */
        clone: function(e) {
            e.preventDefault();

            var collection = this.model.collection;
            var appointment = this.model.clone();

            appointment.unset('_id');
            appointment.unset('id');
            appointment.unset('start');
            appointment.unset('end');

            collection.add(appointment, {at: 0});

            var appointmentView = new EA.AppointmentView({
                model: appointment
            });

            appointmentView.setData(
                this.locations,
                this.services,
                this.workers
            );

            this.$el.closest("#ea-appointments").prepend(appointmentView.$el);

            appointmentView.edit();
        },

        save: function() {
            var appointment = this.model;
            var view = this;
            var customParams = {};

            this.$el.find('.time-start').change();

            jQuery.each(this.$el.find('input, select, textarea'), function(index, elem){
                var $elem = jQuery(elem);


                if ($elem.data('prop') === 'date') {
                    appointment.set($elem.data('prop'), moment(jQuery(elem).datepicker('getDate')).format('YYYY-MM-DD'));
                    appointment.set('end_date', moment(jQuery(elem).datepicker('getDate')).format('YYYY-MM-DD'));
                } else {
                    appointment.set($elem.data('prop'), $elem.val());
                }

                if($elem.attr('name') === 'send-mail' && $elem.is(':checked')) {
                    customParams._mail = $elem.val();
                }
            });

            // Saves appointment
            appointment.save(customParams, {
                success: function(model, response) {
                    view.render();
                }
            });

            this.$el.removeClass('ea-editing');

            // show row
            this.template = this.template_show;

            this.render();

            this.edit_mode = false;
        },

        cancel: function() {
            // If is new remove model/view
            if(this.model.isNew()) {

                this.model.destroy();
                this.remove();

            } else {

                this.$el.removeClass('ea-editing');

                this.template = this.template_show;
                this.render();
            }

            this.edit_mode = false;
        },

        // Delets model and view
        removeItem: function() {
            var view = this;

            this.model.destroy({
                success: function(model, response) {
                    view.remove();
                }
            });
        },

        setData: function(locations, services, workers) {
            this.locations = locations;
            this.services  = services;
            this.workers   = workers;
        },

        //
        keydownEvent: function(e) {
            switch (e.which) {
                // esc
                case 27 :
                    this.cancel();
                break;
            }
        },

        /**
         * Change of App params
         */
        changeApp: function() {

            var fields = this.$el.find(".app-fields");
            var timeField = this.$el.find('[data-prop="start"]');

            // remove current times
            timeField.empty();

            var isComplete = true;

            var filter = {};

            jQuery.each(fields, function(index, element){
                var value = jQuery(element).val();

                filter[jQuery(element).data('prop')] = value;

                // format date field
                if (jQuery(element).data('prop') === 'date') {
                    filter['date'] = moment(jQuery(element).datepicker('getDate')).format('YYYY-MM-DD');
                }

                if(value === '') {
                    isComplete = false;
                }
            });

            if(isComplete) {
                filter.action = 'ea_open_times';
                filter.app_id = this.model.get('id');

                var that = this;

                jQuery.get(
                    ajaxurl,
                    filter,
                    function(response) {
                        if(response.length > 0) {
                            // console.log(response);
                            var options = that.template_times({
                                app : that.model.toJSON(),
                                times: response
                            });

                            timeField.html(options);
                            timeField.prop('disabled', false);
                        }
                }, "json");
            } else {
                timeField.prop('disabled', true);
            }
        },

        setEndTimeApp: function() {
            var start = this.$el.find('.time-start').val();
            var date = this.$el.find('.date-start').val();

            // service duration
            var service = this.$el.find('[name="ea-input-services"]');
            var duration = parseInt(service.children(':selected').data('duration'));

            var startTime = new Date(date + "T" + start);

            var newDateObj = new Date(startTime.getTime() + duration * 60000);

            var minutes = newDateObj.getMinutes();
            var hours = newDateObj.getHours();

            if(minutes.length === 1) {
                minutes = '0' + minutes;
            }

            if(hours.length === 1) {
                hours = '0' + hours;
            }
            // FIX there is time end issue here
            // this.model.set('end', hours + ":" + minutes);
            this.model.set('end', null);
        },

        serviceChange: function() {
            if(!this.model.isNew()) {
                return;
            }

            var option = this.$el.find('.ea-service').children(':selected');

            this.$el.find('.ea-price').val(option.data('price'));
        }
    });
    var mainView = new EA.MainView();

}(jQuery));