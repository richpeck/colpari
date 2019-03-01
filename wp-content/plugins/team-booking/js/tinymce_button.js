(function () {
    tinymce.PluginManager.add('teambooking_tinymce_button', function (editor, url) {
        editor.addButton('teambooking_tinymce_button', {
            title: tbk_mce_config.strings.title,
            icon : 'icon teambooking-tinymce-icon',
            type : 'menubutton',
            menu : [
                {
                    text   : tbk_mce_config.strings.tb_calendar,
                    value  : 'add calendar',
                    onclick: function () {
                        var body = [
                            {
                                type: 'checkbox',
                                name: 'read_only',
                                text: tbk_mce_config.strings.read_only
                            },
                            {
                                type: 'checkbox',
                                name: 'no_filter',
                                text: tbk_mce_config.strings.no_filter
                            },
                            {
                                type: 'checkbox',
                                name: 'no_timezone',
                                text: tbk_mce_config.strings.no_timezone
                            },
                            {
                                type: 'checkbox',
                                name: 'logged_only',
                                text: tbk_mce_config.strings.logged_only
                            }
                        ];

                        body.push(
                            {
                                type : 'label',
                                style: 'font-weight:700;height:40px;padding-top:10px;',
                                text : tbk_mce_config.strings.specific_services
                            }
                        );
                        for (var key in tbk_mce_config.services) {
                            body.push(
                                {
                                    type: 'checkbox',
                                    name: '[srv]' + key,
                                    text: tbk_mce_config.services[key]
                                }
                            );
                        }
                        body.push(
                            {
                                type : 'label',
                                style: 'font-weight:700;height:40px;padding-top:10px;',
                                text : tbk_mce_config.strings.specific_coworkers
                            }
                        );
                        for (var key in tbk_mce_config.coworkers) {
                            body.push(
                                {
                                    type: 'checkbox',
                                    name: '[cwk]' + key,
                                    text: tbk_mce_config.coworkers[key]
                                }
                            );
                        }
                        editor.windowManager.open({
                            title     : tbk_mce_config.strings.tb_calendar,
                            body      : body,
                            autoScroll: true,
                            classes   : 'teambooking-tinymce-panel',
                            onsubmit  : function (e) {
                                // Set var for sending back to editor
                                var attributes = '';
                                // Check first checkbox
                                if (e.data.read_only === true) {
                                    attributes += ' read_only="yes"';
                                }
                                if (e.data.no_filter === true) {
                                    attributes += ' nofilter="yes"';
                                }
                                if (e.data.no_timezone === true) {
                                    attributes += ' notimezone="yes"';
                                }
                                if (e.data.logged_only === true) {
                                    attributes += ' logged_only="yes"';
                                }
                                var coworkers = '';
                                var services = '';
                                for (var key in e.data) {
                                    if (e.data[key] === true) {
                                        if (key.substr(0, 5) === '[cwk]') {
                                            coworkers += key.substr(5) + ', ';
                                        }
                                        if (key.substr(0, 5) === '[srv]') {
                                            services += key.substr(5) + ', ';
                                        }
                                    }
                                }
                                coworkers = coworkers.slice(0, -2);
                                services = services.slice(0, -2);
                                if (services !== '') {
                                    attributes += ' booking="' + services + '"';
                                }
                                if (coworkers !== '') {
                                    attributes += ' coworker="' + coworkers + '"';
                                }
                                editor.insertContent('[tb-calendar' + attributes + ']');
                            }
                        });
                    }
                },
                {
                    text   : tbk_mce_config.strings.tb_reservations,
                    value  : 'add reservations list',
                    onclick: function () {
                        var body = [
                            {
                                type: 'checkbox',
                                name: 'read_only',
                                text: tbk_mce_config.strings.read_only
                            }
                        ];
                        editor.windowManager.open({
                            title   : tbk_mce_config.strings.tb_reservations,
                            body    : body,
                            onsubmit: function (e) {
                                // Set var for sending back to editor
                                var attributes = '';
                                // Check first checkbox
                                if (e.data.read_only === true) {
                                    attributes += ' read_only="yes"';
                                }
                                editor.insertContent('[tb-reservations' + attributes + ']');
                            }
                        });
                    }
                },
                {
                    text   : tbk_mce_config.strings.tb_upcoming,
                    value  : 'add upcoming list',
                    onclick: function () {
                        var body = [
                            {
                                type   : 'textbox',
                                name   : 'shown',
                                label  : tbk_mce_config.strings.how_many,
                                tooltip: tbk_mce_config.strings.how_many_tooltip,
                                value  : '4'
                            },
                            {
                                type   : 'textbox',
                                name   : 'limit',
                                label  : tbk_mce_config.strings.max_events,
                                tooltip: tbk_mce_config.strings.max_events_tooltip,
                                value  : ''
                            },
                            {
                                type  : 'listbox',
                                name  : 'slot_style',
                                label : tbk_mce_config.strings.slot_style,
                                values: [
                                    {text: tbk_mce_config.strings.style_basic, value: 0},
                                    {text: tbk_mce_config.strings.style_elegant, value: 1}
                                ],
                                value : 0
                            },
                            {
                                type: 'checkbox',
                                name: 'read_only',
                                text: tbk_mce_config.strings.read_only
                            },
                            {
                                type: 'checkbox',
                                name: 'logged_only',
                                text: tbk_mce_config.strings.logged_only
                            },
                            {
                                type: 'checkbox',
                                name: 'show_more',
                                text: tbk_mce_config.strings.show_more
                            },
                            {
                                type: 'checkbox',
                                name: 'hide_timezone',
                                text: tbk_mce_config.strings.no_timezone
                            },
                            {
                                type: 'checkbox',
                                name: 'show_descriptions',
                                text: tbk_mce_config.strings.show_descriptions
                            },
                            {
                                type   : 'checkbox',
                                name   : 'hide_same_days',
                                text   : tbk_mce_config.strings.hide_same_days,
                                checked: true
                            }
                        ];

                        body.push(
                            {
                                type : 'label',
                                style: 'font-weight:700;height:40px;padding-top:10px;',
                                text : tbk_mce_config.strings.specific_services
                            }
                        );
                        for (var key in tbk_mce_config.services) {
                            body.push(
                                {
                                    type: 'checkbox',
                                    name: '[srv]' + key,
                                    text: tbk_mce_config.services[key]
                                }
                            );
                        }
                        body.push(
                            {
                                type : 'label',
                                style: 'font-weight:700;height:40px;padding-top:10px;',
                                text : tbk_mce_config.strings.specific_coworkers
                            }
                        );
                        for (var key in tbk_mce_config.coworkers) {
                            body.push(
                                {
                                    type: 'checkbox',
                                    name: '[cwk]' + key,
                                    text: tbk_mce_config.coworkers[key]
                                }
                            );
                        }

                        editor.windowManager.open({
                            title   : tbk_mce_config.strings.tb_upcoming,
                            body    : body,
                            onsubmit: function (e) {
                                // Set var for sending back to editor
                                var attributes = '';
                                var _shown = parseInt(e.data.shown);
                                if (!isNaN(_shown)) {
                                    attributes += ' shown="' + _shown + '"';
                                }
                                var _limit = parseInt(e.data.limit);
                                if (!isNaN(_limit)) {
                                    attributes += ' limit="' + _limit + '"';
                                }
                                var _slot_style = parseInt(e.data.slot_style);
                                if (!isNaN(_slot_style) && _slot_style !== 0) {
                                    attributes += ' slot_style="' + _slot_style + '"';
                                }
                                // Check first checkbox
                                if (e.data.read_only === true) {
                                    attributes += ' read_only="yes"';
                                }
                                if (e.data.logged_only === true) {
                                    attributes += ' logged_only="yes"';
                                }
                                if (e.data.show_more === true) {
                                    attributes += ' more="yes"';
                                }
                                if (e.data.hide_timezone === true) {
                                    attributes += ' notimezone="yes"';
                                }
                                if (e.data.show_descriptions === true) {
                                    attributes += ' descriptions="yes"';
                                }
                                if (e.data.hide_same_days === false) {
                                    attributes += ' hide_same_days="no"';
                                }
                                var coworkers = '';
                                var services = '';
                                for (var key in e.data) {
                                    if (e.data[key] === true) {
                                        if (key.substr(0, 5) === '[cwk]') {
                                            coworkers += key.substr(5) + ', ';
                                        }
                                        if (key.substr(0, 5) === '[srv]') {
                                            services += key.substr(5) + ', ';
                                        }
                                    }
                                }
                                coworkers = coworkers.slice(0, -2);
                                services = services.slice(0, -2);
                                if (services !== '') {
                                    attributes += ' service="' + services + '"';
                                }
                                if (coworkers !== '') {
                                    attributes += ' coworker="' + coworkers + '"';
                                }
                                editor.insertContent('[tb-upcoming' + attributes + ']');
                            }
                        });
                    }
                }
            ]
        });
    });
})();

