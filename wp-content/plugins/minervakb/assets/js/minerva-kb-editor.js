/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */
(function($) {
    'use strict';

    var GLOBAL_DATA = window.MinervaKB;
    var ui = window.MinervaUI;
    var i18n = GLOBAL_DATA.i18n;
    var settings = GLOBAL_DATA.settings;

    // global editor reference
    var editor = null;

    // global editor state
    var store = {
        shortcode: null,
        shortcodesCache: {},
        selectedText: ''
    };

    // available shortcodes
    var shortcodes = {
        tip: {
            title: i18n['tip'],
            hasContent: true,
            icon: 'fa-lightbulb-o'
        },
        info: {
            title: i18n['info'],
            hasContent: true,
            icon: 'fa-info-circle'
        },
        warning: {
            title: i18n['warning'],
            hasContent: true,
            icon: 'fa-exclamation-triangle'
        },
        topic: {
            title: i18n['topic'],
            hasContent: false,
            icon: 'fa-file-text-o'
        },
        topics: {
            title: i18n['topics'],
            hasContent: false,
            icon: 'fa-th-large'
        },
        search: {
            title: i18n['search'],
            hasContent: false,
            icon: 'fa-search'
        },
        anchor: {
            title: i18n['anchor'],
            hasContent: true,
            icon: 'fa-list-ol'
        },
        related: {
            title: i18n['related'],
            hasContent: false,
            icon: 'fa-sitemap'
        },
        submission: {
            title: i18n['submission'],
            hasContent: false,
            icon: 'fa-paper-plane-o'
        }
    };

    if (!settings['disable_faq']) {
        shortcodes['faq'] = {
            title: i18n['faq'],
            hasContent: false,
            icon: 'fa-question'
        }
    }

    // create popup instance
    var popup = new ui.Popup();

    popup.bindEvents({
        'click .fn-mkb-popup-close': popup.close.bind(popup),
        'click .fn-mkb-shortcode-back': renderShortcodeList,
        'click .fn-mkb-shortcode-select': handleShortcodeSelect,
        'click .fn-mkb-shortcode-insert': handleShortcodeInsert
    });

    /**
     * Renders all he available shortcodes
     */
    function renderShortcodeList() {
        popup.render({
            title: i18n['select-shortcode'],
            content: '<div>' + Object.keys(shortcodes).reduce(function(html, id) {
                var item = shortcodes[id];
                return html + (
                '<a href="#" class="fn-mkb-shortcode-select mkb-shortcode-select" data-shortcode="' + id + '">' +
                    '<i class="fa ' + item.icon + '"></i>' +
                    item.title +
                '</a>'
                );
            }, '') +
            '</div>'
        });
    }

    /**
     * Loads and renders selected shortcode options
     */
    function renderShortcodeOptions(shortcode, values) {

        // in case we have cached options already
        if (store.shortcodesCache[shortcode] && !values) {
            return onShortcodeOptionsReceived(store.shortcodesCache[shortcode].html);
        }

        showOptionsFormPreloader();

        ui.fetch({
            action: 'mkb_get_shortcode_options',
            shortcode: shortcode,
            values: values
        }).done(function(response) {

            if (!response || response.status == 1) {
                ui.handleErrors(response);
                return;
            }

            store.shortcodesCache[shortcode] = {
                html: response.html,
                count: response.count
            };

            onShortcodeOptionsReceived(response.html);
        });
    }

    /**
     * Renders preloader
     */
    function showOptionsFormPreloader() {
        popup.render({
            title: i18n['loading-options'],
            content: '<div>' +
            '<div class="mkb-loader">' +
                '<span class="inner1"></span>' +
                '<span class="inner2"></span>' +
                '<span class="inner3"></span>' +
            '</div>' +
            '</div>'
        });
    }

    /**
     * Renders shortcode options received from BE
     * @param html
     */
    function onShortcodeOptionsReceived(html) {
        popup.render({
            title: i18n['configure-shortcode'],
            content: '<div class="fn-mkb-shortcode-options">' + html + '</div>',
            headerControlsLeft: store.parsedShortcode ?
                [] :
                [
                    '<a href="#" class="fn-mkb-shortcode-back"><i class="fa fa-lg fa-arrow-circle-left"></i></a>'
                ],
            footerControlsRight: [
                '<a href="#" class="fn-mkb-shortcode-insert mkb-action-button mkb-action-default">' +
                (store.parsedShortcode ? i18n['update'] : i18n['insert']) +
                '</a>'
            ]
        });

        var $form = popup.$el.find('.fn-mkb-shortcode-options');

        ui.setupColorPickers($form);
        ui.setupIconSelect($form);
        ui.setupImageSelect($form);
        ui.setupTopicsSelect($form);
        ui.setupTermsSelect($form);
        ui.setupCSSSize($form);
        ui.setupPageSelect($form);
        ui.setupRelatedArticles($form);
        ui.setupMediaUpload($form);
        ui.setupDependencies($form);
    }

    /**
     * Tries to parse shortcode from selected text
     * @param selected
     * @returns {boolean}
     */
    function tryToParseShortcodeOptions(selected) {

        store.parsedShortcode = null;

        if (!window.wp || !window.wp.shortcode) {
            return false;
        }

        var found = Object.keys(shortcodes).reduce(function (found, id) {
                var parsed = window.wp.shortcode.next('mkb-' + id, selected);
                parsed && found.push(parsed);
                return found;
            }, []);

        if (!found.length) {
            return false;
        }

        if (found.length > 1) {
            // more than 1 shortcode selected TODO: info box
            alert(i18n['more-than-one-shortcode']);

            return true; // parsed, but not rendered
        }

        var shortcode = found[0].shortcode;
        var id = shortcode.tag.replace('mkb-', '');

        store.shortcode = id;
        store.parsedShortcode = shortcode;
        renderShortcodeOptions(id, shortcode.attrs.named);

        return true;
    }

    /**
     * Handles shortcode select click
     */
    function handleShortcodeSelect(e) {
        var $link = $(e.currentTarget);
        var shortcode = $link.attr('data-shortcode');

        e.preventDefault();

        store.shortcode = shortcode;
        renderShortcodeOptions(shortcode);
    }

    /**
     * Handles shortcode insert click
     */
    function handleShortcodeInsert(e) {
        var $form = popup.$el.find('.fn-mkb-shortcode-options');
        var shortcodeInfo = shortcodes[store.shortcode];
        var result = '';
        var options = {};

        e.preventDefault();

        if ((store.shortcode === 'tip' || store.shortcode === 'warning' || store.shortcode === 'info') &&
            !store.selectedText) {
            store.selectedText = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.';
        }

        if (store.parsedShortcode && !shortcodeInfo.hasContent) {
            store.parsedShortcode.type = 'single'; // remove closed tag for shortcodes with no content
        }

        var before = store.parsedShortcode ? '' : '[mkb-' + store.shortcode + ']';
        var after = store.parsedShortcode ? '' : '[/mkb-' + store.shortcode + ']';

        if (store.shortcodesCache[store.shortcode].count) { // has options
            options = ui.getFormDataForShortcode($form);

            if (store.parsedShortcode) {
                for (var key in options) {
                    if (!options.hasOwnProperty(key)) { continue; }
                    store.parsedShortcode.set(key, options[key]);
                }
                result = store.parsedShortcode.string();
            } else { // TODO: rewrite to use wp.shortcode as well
                before = before.replace(']', ' ' + getOptionsString(options) + ']');
                result = before + (shortcodeInfo.hasContent ? store.selectedText : '') +
                (shortcodeInfo.hasContent ? after : '');
            }

            editor.execCommand('mceInsertContent', 0, result);
            popup.close();
        } else {
            if (store.parsedShortcode) {
                result = store.parsedShortcode.string();
            } else {
                result = before + (shortcodeInfo.hasContent ? store.selectedText : '') +
                (shortcodeInfo.hasContent ? after : '');
            }

            editor.execCommand('mceInsertContent', 0, result);
            popup.close();
        }
    }

    /**
     * Transforms options object into string
     */
    function getOptionsString(options) {
        var result = [];

        for (var id in options) {
            if (!options.hasOwnProperty(id)) { continue; }
            result.push(id + '="' + options[id] + '"');
        }

        return result.join(' ');
    }

    /**
     * Register MCE integration
     */
    tinymce.create('tinymce.plugins.MinervaKB', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url) {

            if (ed.id !== 'content') {
                return;
            }

            editor = ed;

            ed.addButton('minervakb', {
                title : i18n['minervakb-shortcodes'],
                cmd : 'minervakb',
                image : url + '/../img/minerva-mce.png'
            });

            ed.addCommand('minervakb', function() {
                store.selectedText = ed.selection.getContent();

                if (tryToParseShortcodeOptions(store.selectedText)) {
                    return;
                }

                renderShortcodeList();
            });
        },

        /**
         * Creates control instances based on the incoming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use in order to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl : function(n, cm) {
            return null;
        },

        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
                longname : 'MinervaKB Buttons',
                author : 'KonstruktStudio',
                authorurl : 'https://www.minerva-kb.com',
                infourl : 'https://www.minerva-kb.com',
                version : "1.0"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add( 'minervakb', tinymce.plugins.MinervaKB );
})(jQuery);