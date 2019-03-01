/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */
(function($) {
    'use strict';

    var GLOBAL_DATA = window.MinervaKB;
    var ajaxUrl = GLOBAL_DATA.ajaxUrl;
    var OPTION_PREFIX = GLOBAL_DATA.optionPrefix;
    var i18n = GLOBAL_DATA.i18n;

    // polyfills
    // https://tc39.github.io/ecma262/#sec-array.prototype.includes
    if (!Array.prototype.includes) {
        Object.defineProperty(Array.prototype, 'includes', {
            value: function(searchElement, fromIndex) {

                if (this == null) {
                    throw new TypeError('"this" is null or not defined');
                }

                // 1. Let O be ? ToObject(this value).
                var o = Object(this);

                // 2. Let len be ? ToLength(? Get(O, "length")).
                var len = o.length >>> 0;

                // 3. If len is 0, return false.
                if (len === 0) {
                    return false;
                }

                // 4. Let n be ? ToInteger(fromIndex).
                //    (If fromIndex is undefined, this step produces the value 0.)
                var n = fromIndex | 0;

                // 5. If n ≥ 0, then
                //  a. Let k be n.
                // 6. Else n < 0,
                //  a. Let k be len + n.
                //  b. If k < 0, let k be 0.
                var k = Math.max(n >= 0 ? n : len - Math.abs(n), 0);

                function sameValueZero(x, y) {
                    return x === y || (typeof x === 'number' && typeof y === 'number' && isNaN(x) && isNaN(y));
                }

                // 7. Repeat, while k < len
                while (k < len) {
                    // a. Let elementK be the result of ? Get(O, ! ToString(k)).
                    // b. If SameValueZero(searchElement, elementK) is true, return true.
                    if (sameValueZero(o[k], searchElement)) {
                        return true;
                    }
                    // c. Increase k by 1.
                    k++;
                }

                // 8. Return false
                return false;
            }
        });
    }

    /**
     * Micro Pub/Sub
     * @type {*|HTMLElement}
     */
    var $_eventsBus = $({});

    var listenTo = function() {
        $_eventsBus.on.apply($_eventsBus, arguments);
    };

    var stopListeningTo = function() {
        $_eventsBus.off.apply($_eventsBus, arguments);
    };

    var trigger = function() {
        $_eventsBus.trigger.apply($_eventsBus, arguments);
    };

    /**
     * Really basic popup
     * @returns {{render: _render, setEvents: _bindEvents, close: _close}}
     * @constructor
     */
    function Popup() {

        var isInDOM = false;

        var $rootEl;
        var $overlayEl;
        var $popupEl;
        var $body = $('body');

        var events = {};

        function _defaults(base, options) {
            for (var key in options) {
                if (base.hasOwnProperty(key)) { continue; }
                base[key] = options[key];
            }

            return base;
        }

        function _create() {
            $rootEl = $('<div class="mkb-popup-wrap"></div>'); // should not content styles

            $overlayEl = null;
            $popupEl = null;

            $('body').append($rootEl);
            isInDOM = true;
        }

        function _render(options) {
            var options = _defaults(options, Popup.defaults);

            _renderOverlay();
            _renderPopup(options);
        }

        function _renderOverlay() {
            $overlayEl = $rootEl.find('.mkb-popup-overlay');

            if ($overlayEl.length) {
                return;
            }

            $overlayEl = $('<div class="mkb-popup-overlay"></div>');
            $rootEl.append($overlayEl);

            $body.css('overflow', 'hidden');
        }

        function _renderPopup(options) {
            var $existingPopupEl = $rootEl.find('.mkb-popup');

            if ($existingPopupEl.length) {
                $existingPopupEl.remove();
            }

            renderEmptyPopup();

            $popupEl.find('.mkb-popup__header-title').html(options.title);
            $popupEl.find('.mkb-popup__body').html(options.content);
            $popupEl.find('.mkb-popup__header-controls--left').html(options.headerControlsLeft.join(''));
            $popupEl.find('.mkb-popup__header-controls--right').html(options.headerControlsRight.join(''));
            $popupEl.find('.mkb-popup__footer-controls--left').html(options.footerControlsLeft.join(''));
            $popupEl.find('.mkb-popup__footer-controls--center').html(options.footerControlsCenter.join(''));
            $popupEl.find('.mkb-popup__footer-controls--right').html(options.footerControlsRight.join(''));

            $rootEl.append($popupEl);
        }

        function renderEmptyPopup() {
            $popupEl = $(
                '<div class="mkb-popup">' +
                '<div class="mkb-popup__header mkb-clearfix">' +
                '<div class="mkb-popup__header-controls--left"></div>' +
                '<div class="mkb-popup__header-title"></div>' +
                '<div class="mkb-popup__header-controls--right"></div>' +
                '</div>' +
                '<div class="mkb-popup__body"></div>' +
                '<div class="mkb-popup__footer mkb-clearfix">' +
                '<div class="mkb-popup__footer-controls--left"></div>' +
                '<div class="mkb-popup__footer-controls--center"></div>' +
                '<div class="mkb-popup__footer-controls--right"></div>' +
                '</div>' +
                '</div>'
            )
        }

        function _close() {
            $rootEl.html('');

            $body.css('overflow', '');
        }

        function _destroy() {
            $rootEl.remove();
            $rootEl = null;
            $body.css('overflow', '');

            _unbindEvents();
            isInDOM = false;
        }

        function _bindEvents(eventsMap) {
            _unbindEvents();

            for (var event in eventsMap) {
                if (!eventsMap.hasOwnProperty(event)) { continue; }

                var splitEvent = event.split(' ');

                if (splitEvent.length !== 2) { continue; }

                $rootEl.on(splitEvent[0], splitEvent[1], eventsMap[event]);
            }
        }

        function _unbindEvents() {
            $rootEl.off();
        }

        // create root and attach it to DOM
        _create();

        return {
            $el: $rootEl,
            render: _render,
            bindEvents: _bindEvents,
            close: _close,
            destroy: _destroy
        };
    }

    Popup.defaults = {
        title: 'Dialog',
        content: '',
        headerControlsLeft: [],
        headerControlsRight: [
            '<a href="#" class="fn-mkb-popup-close mkb-popup-close"><i class="fa fa-lg fa-times-circle"></i></a>'
        ],
        footerControlsLeft: [],
        footerControlsCenter: [],
        footerControlsRight: []
    };

    /**
     * Server
     * @param data
     * @returns {*}
     */
    function addAjaxNonce (data) {
        data['nonce_key'] = GLOBAL_DATA.nonce.nonceKey;
        data['nonce_value'] = GLOBAL_DATA.nonce.nonce;

        return data;
    }

    /**
     * Debounces function execution
     * @param func
     * @param wait
     * @param immediate
     * @returns {Function}
     */
    function debounce(func, wait, immediate) {
        var timeout;
        return function () {
            var context = this, args = arguments;
            var later = function () {
                timeout = null;
                if (!immediate) {
                    func.apply(context, args);
                }
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) {
                func.apply(context, args);
            }
        };
    }

    /**
     * Throttles function execution. Based on Ben Alman implementation
     * @param delay
     * @param noTrailing
     * @param callback
     * @param atBegin
     * @returns {wrapper}
     */
    function throttle(delay, noTrailing, callback, atBegin) {
        var timeoutId;
        var lastExec = 0;

        if (typeof noTrailing !== 'boolean') {
            atBegin = callback;
            callback = noTrailing;
            noTrailing = undefined;
        }

        function wrapper() {
            var elapsed = +new Date() - lastExec;
            var args = arguments;

            var exec = function _exec() {
                lastExec = +new Date();
                callback.apply(this, args );
            }.bind(this);

            function clear() {
                timeoutId = undefined;
            }

            if (atBegin && !timeoutId) {
                exec();
            }

            timeoutId && clearTimeout(timeoutId);

            if (atBegin === undefined && elapsed > delay) {
                exec();
            } else if (noTrailing !== true) {
                timeoutId = setTimeout(
                    atBegin ?
                        clear :
                        exec,
                    atBegin === undefined ?
                    delay - elapsed :
                        delay
                );
            }
        }

        return wrapper;
    }

    /**
     * Handles errors in response
     * @param response
     */
    function handleErrors(response) {
        if (response.errors && response.errors.global) {
            response.errors.global.forEach(function(error) {
                toastr.error('<strong>Error ' + error.code + '</strong>: ' + error.error_message);
            });
        } else {
            // unknown error
            toastr.error('Some unknown error happened');
        }
    }

    /**
     * Simple WP ajax wrapper
     * @param data
     * @param options
     * @returns {*}
     * @private
     */
    function _fetch (data, options) {
        var _defaults = {
            method: 'POST',
            url: ajaxUrl,
            dataType: 'json',
            data: {}
        };
        var _options = options ? _.extend({}, _defaults, options) : _defaults;

        return jQuery.ajax(_.extend({}, _options, {
            data: addAjaxNonce(data)
        }), options);
    }

    /**
     * Form data
     * @param data
     * @param el
     * @returns {*}
     */
    function formControlReducer(data, el) {
        var type = el.dataset.type;
        var $el = $(el);
        var $control;
        var value = null;
        var name = null;

        switch(type) {
            case 'checkbox':
            case 'toggle':
                $control = $el.find('input[type="checkbox"]');
                name = $control.attr('name');
                value = Boolean($control.attr('checked'));
                break;

            case 'input':
            case 'input_text':
            case 'envato_verify':
            case 'color':
                $control = $el.find('input[type="text"]');
                name = $control.attr('name');
                value = $control.val();
                break;

            case 'textarea':
            case 'textarea_text':
                $control = $el.find('textarea');
                name = $control.attr('name');
                value = $control.val();
                break;

            case 'icon_select':
            case 'image_select':
            case 'layout_select':
            case 'term_select':
            case 'roles_select':
                $control = $el.find('input[type="hidden"]');
                name = $control.attr('name');
                value = $control.val();
                break;

            case 'select':
            case 'page_select':
            case 'font':
                $control = $el.find('select');
                name = $control.attr('name');
                value = $control.val();
                break;

            case 'google_font_weights':
            case 'google_font_languages':
                $control = $el.find('select');
                name = $control.attr('name');
                value = $control.val() || "";
                break;

            case 'css_size':
                var $size = $el.find('.fn-css-size-value');
                var $unit = $el.find('.fn-css-size-unit-value');
                name = $size.attr('name');

                value = {
                    'unit': $unit.val(),
                    'size': $size.val()
                };
                break;

            case 'media':
                $control = $el.find('.fn-media-store');
                name = $control.attr('name');
                value = $control.val();
                break;

            case 'articles_list':
                var $items = $el.find('.fn-related-article-item-select');
                name = $el.attr('data-name');
                value = Array.prototype.map.call($items, function(item) { return $(item).val(); }).join(',');
                break;

            default:
                return data;
                break;
        }

        data[name.replace('mkb_option_', '')] = value;

        return data;
    }

    function formControlShortcodeReducer(data, el) {
        var type = el.dataset.type;
        var $el = $(el);
        var $control;
        var value = null;
        var name = null;

        switch(type) {
            case 'css_size':
                var $size = $el.find('.fn-css-size-value');
                var $unit = $el.find('.fn-css-size-unit-value');
                name = $size.attr('name');

                value = '' + $size.val() + $unit.val();
                break;

            case 'media':
                $control = $el.find('.fn-media-store');
                name = $control.attr('name');
                value = '';
                try {
                    value = JSON.parse($control.val()).img || '';
                } catch (e) {}

                console.log(value);
                break;

            default:
                return formControlReducer(data, el);
                break;
        }

        data[name.replace('mkb_option_', '')] = value;

        return data;
    }

    function getFormData($form) {
        return Array.prototype.reduce.apply($form.find('.fn-control-wrap'), [formControlReducer, {}]);
    }

    function getFormDataForShortcode($form) {
        return Array.prototype.reduce.apply($form.find('.fn-control-wrap'), [formControlShortcodeReducer, {}]);
    }

    /**
     * Icon Select
     * @param $container
     */
    function setupIconSelect($container) {
        $container.on('click', '.mkb-icon-button__link', function(e) {
            e.preventDefault();

            var $btn = $(e.currentTarget);
            var $wrap = $btn.parents('.mkb-control-wrap');
            var $iconsBox = $wrap.find('.mkb-icon-select');
            var $iconsFilter = $wrap.find('.mkb-icon-select-filter');
            var $iconsFilterInput = $iconsFilter.find('input');

            $iconsBox.toggleClass('mkb-hidden');
            $iconsFilter.toggleClass('mkb-hidden');
            $btn.toggleClass('mkb-pressed');

            $iconsFilterInput.focus();
        });

        $container.on('click', '.mkb-icon-select__item', function(e) {
            e.preventDefault();

            var $icon = $(e.currentTarget);
            var icon = $icon.data('mkb-icon');
            var $wrap = $icon.parents('.mkb-control-wrap');
            var $btn = $wrap.find('.mkb-icon-button__link');
            var $btnText = $btn.find('.mkb-icon-button__text');
            var $btnIcon = $btn.find('.mkb-icon-button__icon');
            var $iconsBox = $wrap.find('.mkb-icon-select');
            var $iconsFilter = $wrap.find('.mkb-icon-select-filter');
            var $input = $wrap.find('.mkb-icon-hidden-input');
            var $allIcons = $wrap.find('.mkb-icon-select__item');

            $iconsBox.addClass('mkb-hidden');
            $iconsFilter.addClass('mkb-hidden');
            $btn.removeClass('mkb-pressed');
            $btnText.text(icon);
            $btnIcon.attr('class', 'mkb-icon-button__icon fa fa-lg ' + icon);
            $input.val(icon).trigger('change');
            $allIcons.removeClass('mkb-icon-selected');
            $icon.addClass('mkb-icon-selected');
        });

        $container.find('.mkb-control-wrap[data-type="icon_select"]').each(function(index, item) {
            var $wrap = $(item);
            var $allIcons = $wrap.find('.mkb-icon-select__item');

            $wrap.on('input', '.mkb-icon-select-filter input', function(e) {
                var needle = e.currentTarget.value.trim();

                if (needle.length < 2) {
                    clearFilter();
                    return;
                }

                filterIcons(needle);
            });

            function filterIcons(needle) {
                $allIcons.addClass('mkb-hidden');
                $allIcons.filter('[data-mkb-icon*=' + needle + ']').removeClass('mkb-hidden');
            }

            function clearFilter() {
                $allIcons.removeClass('mkb-hidden');
            }
        });
    }

    /**
     * Super simple url utils
     * @type {{getSearchParams: Function, getSearchParamByName: Function, setSearchParam: Function}}
     */
    var urlUtils = {
        getSearchParams: function () {
            return window.location.search.replace(/^\?/, '').split('&').map(function (paramString) {
                var kv = paramString.split('=');

                return {
                    name: decodeURIComponent(kv[0]),
                    value: decodeURIComponent(kv[1])
                }
            });
        },

        getSearchParamByName: function (name) {
            var search = this.getSearchParams();
            var value;

            search.some(function(param) {
                if (param.name === name) {
                    value = param.value;
                    return true;
                }
            });

            return value;
        },

        setSearchParam: function (name, value) {
            var search = this.getSearchParams();

            var parameterExists = search.some(function (param) {
                if (param.name === name) {
                    param.value = value;
                    return true;
                }
            });

            if (!parameterExists) {
                search.push({
                    name: name,
                    value: value
                });
            }

            if (history.pushState) {
                var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?' + search.map(function (param) {
                        return encodeURIComponent(param.name) + '=' + encodeURIComponent(param.value);
                    }).join('&');

                window.history.pushState({ path: newurl }, '', newurl);
            }
        }
    };

    /**
     * Settings tabs
     * @param $container
     */
    function setupSettingsTabs($container) {
        $container.each(function(index, container) {
            var $container = $(container);

            $container.on('click', '.mkb-settings-tab a', function(e) {
                var $link = $(e.currentTarget);
                var $links = $container.find('.mkb-settings-tab a');
                var href = $link.attr('href');
                var $tabContainers = $container.find('.mkb-settings-tab__container');
                var tabID = href.replace("#", '');
                var tabIDClean = tabID.replace(/^mkb_tab\-/, '');
                var $tabContainer = $tabContainers.filter('[id="' + tabID + '"]');

                $links.removeClass('active');
                $link.addClass('active');

                $tabContainers.removeClass('active');
                $tabContainer.addClass('active');

                e.preventDefault();

                urlUtils.setSearchParam('mkb_options_tab', tabIDClean);
            });

            var activeTabFromURL = urlUtils.getSearchParamByName('mkb_options_tab');

            if (activeTabFromURL) {
                $container.find('.mkb-settings-tab a[href=#mkb_tab-' + activeTabFromURL + ']').eq(0).click();
            } else {
                $container.find('.mkb-settings-tab a').eq(0).click();
            }

            $container.find('form, .mkb-form').removeClass('mkb-loading');
        });
    }

    /**
     * ColorPickers
     * @param $container
     * @param options
     */
    function setupColorPickers($container, options) {
        $container.find('.mkb-color-picker').wpColorPicker({
            /**
             * @param {Event} event - standard jQuery event, produced by whichever
             * control was changed.
             * @param {Object} ui - standard jQuery UI object, with a color member
             * containing a Color.js object.
             */
            change: function (event, ui) {
                var element = event.target;
                var color = ui.color.toString();

                if (options && options.onChange) {
                    setTimeout(options.onChange, 300);
                }
            },

            /**
             * @param {Event} event - standard jQuery event, produced by "Clear"
             * button.
             */
            clear: function (event) {
                var element = jQuery(event.target).siblings('.wp-color-picker')[0];
                var color = '';

                if (element) {
                    if (options && options.onChange) {
                        setTimeout(options.onChange, 300);
                    }
                }
            }
        });
    }

    /**
     * Image select
     * @param e
     */
    function onImageSelectClick(e) {
        var $image = $(e.currentTarget);
        var value = $image.data('value');
        var $wrap = $image.parents('.mkb-control-wrap');
        var $images = $wrap.find('.mkb-image-select__item');
        var $input = $wrap.find('.mkb-image-hidden-input');

        $images.removeClass('mkb-image-selected');
        $image.addClass('mkb-image-selected');

        $input.val(value).trigger('change');
    }

    function setupImageSelect($container) {
        $container.on('click', '.mkb-image-select__item', onImageSelectClick);
    }

    /**
     * Topics select
     * @param $container
     */
    function setupTopicsSelect($container) {
        var $layoutSelectWrap = $container.find('.mkb-layout-select');

        $layoutSelectWrap.each(function(index, item) {
            var $layout = $(item);

            $layout.find('.mkb-layout-select__available, .mkb-layout-select__selected').sortable({
                connectWith: ".mkb-layout-select__container",
                receive: function( event, ui ) {
                    var $available = $layout.find('.mkb-layout-select__available .mkb-layout-select__item');
                    var $selected = $layout.find('.mkb-layout-select__selected .mkb-layout-select__item');
                    var $selectedContainer = $layout.find('.mkb-layout-select__selected');
                    var $wrap = $selectedContainer.parents('.mkb-control-wrap');
                    var $input = $wrap.find('.mkb-layout-hidden-input');
                    var selected = Array.prototype.reduce.apply($selected, [function(list, item) {
                        list.push(item.dataset.value);
                        return list;
                    }, []]);

                    $input.val(selected.join(',')).trigger('change');
                },
                stop: function () {
                    var $available = $layout.find('.mkb-layout-select__available .mkb-layout-select__item');
                    var $selected = $layout.find('.mkb-layout-select__selected .mkb-layout-select__item');
                    var $selectedContainer = $layout.find('.mkb-layout-select__selected');
                    var $wrap = $selectedContainer.parents('.mkb-control-wrap');
                    var $input = $wrap.find('.mkb-layout-hidden-input');
                    var selected = Array.prototype.reduce.apply($selected, [function(list, item) {
                        list.push(item.dataset.value);
                        return list;
                    }, []]);

                    $input.val(selected.join(',')).trigger('change');
                }
            });


        });
    }

    function setupTermsSelect($container) {
        var $termsSelectStores = $container.find('.fn-terms-select-store');

        $termsSelectStores.each(function(index, item) {
            var $store = $(item);
            var $wrap = $store.parents('.mkb-control-wrap');
            var $tree = $wrap.find('.fn-terms-tree');
            var $selected = $wrap.find('.fn-terms-selected');
            var $selectedList = $selected.find('ul');

            $selectedList.find('li').append('<a href="#" class="mkb-term-delete"><i class="fa fa-times-circle"></i></a>');

            function updateStore() {
                var $selectedItems = $selectedList.find('li');

                if (!$selectedItems.length) {
                    $store.val('');
                    return;
                }

                var termIds = [].map.call($selectedItems, function(item) {
                    return item.dataset.id;
                });

                $store.val(termIds.join(','));

                $store.trigger('change');
            }

            function deselectTerm(id) {
                var $text = $tree.find('span[data-id="' + id + '"]');
                var $checkbox = $text.find('input');
                var count = parseInt($text[0].dataset.count);

                $checkbox.attr('checked', false);

                $text.removeClass('mkb-term-selected');
            }

            $selected.sortable({
                items: 'li',
                axis: 'y',
                stop: updateStore
            });

            $tree.on('click', 'span', function(e) {
                var text = e.currentTarget;
                var $text = $(text);
                var id = text.dataset.id;
                var count = parseInt(text.dataset.count);
                var path = text.dataset.path;

                var $checkbox = $text.find('input');
                var state = Boolean($checkbox.attr('checked'));

                e.preventDefault();
                e.stopImmediatePropagation();

                $checkbox.attr('checked', !state);
                $text.toggleClass('mkb-term-selected', !state);

                if (!state) {
                    $selectedList.append($('<li data-id="' + id + '">' +
                    '<span>' + path + '</span>' +
                    text.innerText +
                    '<a href="#" class="mkb-term-delete"><i class="fa fa-times-circle"></i></a>' +
                    '</li>'));
                } else {
                    $selectedList.find('li[data-id="' + id + '"]').remove();
                }

                updateStore();
            });

            $selected.on('click', '.mkb-term-delete', function(e) {
                var $delete = $(e.currentTarget);
                var $item = $delete.parent();
                var id = $item[0].dataset.id;

                e.preventDefault();

                deselectTerm(id);
                $item.remove();
                updateStore();
            });

            /**
             * Box/List view custom dependency
             */
            var $closestWrap = $wrap.parents('.fn-layout-editor-section');

            if (!$closestWrap.length) {
                $closestWrap = $wrap.parents('.mkb-settings-tab__container');
            }

            if (!$closestWrap.length) {
                $closestWrap = $wrap.parents('.fn-mkb-shortcode-options');
            }

            var $homeViewSelect = $closestWrap.length ?
                $closestWrap.find('[data-name="mkb_option_home_view"]') :
                $wrap.find('[data-name="mkb_option_home_view"]');

            if (!$homeViewSelect.length) {
                $homeViewSelect = $closestWrap.find('[data-name="mkb_option_view"]');
            }

            if ($homeViewSelect.length) {
                var $input = $homeViewSelect.find('input[type="hidden"]');

                $input.on('change', function(e) {
                    $wrap.toggleClass('mkb-layout-select--box-view', e.currentTarget.value === 'box');
                });

                $input.trigger('change');
            } else if ($container.hasClass('vc_active')) {
                // Visual Composer form
                var $vcInput = $container.find('.wpb_vc_param_value[name="view"]');

                $vcInput.on('change', function(e) {
                    $wrap.toggleClass('mkb-layout-select--box-view', e.currentTarget.value === 'box');
                });

                $vcInput.trigger('change');
            }
        });
    }

    /**
     * Handles dependencies
     * @param $container
     */
    function setupDependencies($container) {
        var data = getFormData($container);
        var dependencies = [];
        var $deps = $container.find('.mkb-control-wrap[data-dependency]');

        function onDependencyTargetChange() {
            var data = getFormData($container);

            dependencies.forEach(function(dep) {
                var targetValue = data[dep.config.target];

                switch (dep.config.type) {
                    case 'EQ':
                        if (targetValue == dep.config.value) {
                            dep.$el.slideDown();
                        } else {
                            dep.$el.hide();
                        }
                        break;

                    case 'NEQ':
                        if (targetValue != dep.config.value) {
                            dep.$el.slideDown();
                        } else {
                            dep.$el.hide();
                        }
                        break;

                    default:
                        break;
                }
            });
        }

        $deps.each(function(index, el) {
            var $el = $(el);
            var name = $(el).data('name');
            var dependencyConfig;

            try {
                dependencyConfig = JSON.parse(
                    el.dataset.dependency
                        .replace(/^"/, '')
                        .replace(/"$/, '')
                );
            } catch (e) {
                console.log('DEV_INFO: Could not parse dependency config');
            }

            if (dependencyConfig) {
                $container
                    .find('.mkb-control-wrap[data-name="' + OPTION_PREFIX + dependencyConfig['target'] + '"]')
                    .addClass('fn-dependency-target');

                dependencies.push({
                    _id: name.replace(OPTION_PREFIX, ''),
                    $el: $el,
                    config: dependencyConfig
                });
            }
        });

        $container.on('change input', '.fn-dependency-target', onDependencyTargetChange);
        onDependencyTargetChange();
    }

    /**
     * CSS size control
     * @param $container
     */
    function setupCSSSize($container) {
        var $cssSizes = $container.find('.mkb-css-size');

        $cssSizes.each(function(index, wrap) {
            var $wrap = $(wrap);
            var $store = $wrap.find('.fn-css-size-store');
            var $size = $wrap.find('.fn-css-size-value');
            var $unit = $wrap.find('.fn-css-size-unit-value');
            var $unitSelectItems = $wrap.find('.fn-css-unit');

            function updateStore(e) {
                $store.val($size.val() + $unit.val());
            }

            $wrap.on('click', '.fn-css-unit', function(e) {
                e.preventDefault();

                var $el = $(e.currentTarget);

                $unitSelectItems.removeClass('mkb-css-unit--selected');
                $el.addClass('mkb-css-unit--selected');
                $unit.val($el.data('unit'));

                updateStore();
            });

            $size.on('input', updateStore);
        });
    }

    /**
     * Home page selector
     * @param $container
     */
    function setupPageSelect($container) {

        function updateLinkButton($btn, linkValue) {
            var link = linkValue !== '' ? linkValue : '#';

            $btn.attr('href', link);
            $btn.attr('target', link === '#' ? '_self' : '_blank');
            $btn.toggleClass('mkb-disabled', link === '#');
        }

        function preventJump(e) {
            if ($(e.currentTarget).attr('href') === '#') {
                e.preventDefault();
            }
        }

        $container.on('change', '.fn-page-select-wrap .fn-control', function(e) {
            var $select = $(e.currentTarget);
            var $selected = $select.find('option:selected');
            var link = $selected.data('link');
            var editLink = $selected.data('edit-link');
            var $previewLink = $select.parents('.fn-page-select-wrap').find('.fn-page-select-link');
            var $editLink = $select.parents('.fn-page-select-wrap').find('.fn-page-select-edit-link');

            updateLinkButton($previewLink, link);

            if ($editLink.length) {
                updateLinkButton($editLink, editLink);
            }
        });

        $container.find('.fn-page-select-wrap .fn-control').trigger('change');
        $container.on('click', '.fn-page-select-link, .fn-page-select-edit-link', preventJump);
    }

    /**
     * Related articles control
     */
    function setupRelatedArticles($container) {
        var $addBtn = $container.find('.fn-related-article-add');
        var $relatedContainer = $container.find('.fn-related-articles');
        var $storage = $container.find('.fn-related-articles-value');

        function updateStorage() {
            var $items = $container.find('.fn-related-article-item-select');
            $storage.val(Array.prototype.map.call($items, function(item) { return $(item).val(); }).join(','));
        }
        updateStorage();
        $container.on('change', '.fn-related-article-item-select', updateStorage);

        $addBtn.on('click', function(e) {
            e.preventDefault();

            var btnText = $addBtn.text();

            $addBtn.text(i18n['loading']).attr('disabled', 'disabled');

            _fetch({
                action: 'mkb_get_articles_list',
                currentId: $addBtn.data('id')
            }).then(function(response) {
                var $related = $('<div class="mkb-related-articles__item fn-related-article-item"></div>');
                var $select = $('<select class="mkb-related-articles__select fn-related-article-item-select" name="mkb_related_articles[]"></select>');
                var articlesList = response.articles || [];

                articlesList.forEach(function(article) {
                    $select.append(
                        $('<option value="' + article.id + '">' + article.title + '</option>')
                    );
                });

                var $noRelatedMessage = $container.find('.fn-no-related-message');

                $noRelatedMessage.length && $noRelatedMessage.remove();

                $related.append($select);
                $related.append(
                    $('<a class="mkb-related-articles__item-remove fn-related-remove mkb-unstyled-link" href="#">' +
                    '<i class="fa fa-close"></i>' +
                    '</a>')
                );

                $container.find('.fn-related-articles').append($related);

                $addBtn.text(btnText).attr('disabled', false);
                updateStorage();
            });
        });

        $relatedContainer.sortable({
            'items': '.mkb-related-articles__item',
            'axis': 'y',
            'update': updateStorage
        });

        $relatedContainer.on('click', '.fn-related-remove', function(e) {
            e.preventDefault();

            var $link = $(e.currentTarget);

            $link.parents('.mkb-related-articles__item').remove();

            if ($relatedContainer.find('.mkb-related-articles__item').length === 0) {
                $relatedContainer.append(
                    $('<div class="fn-no-related-message mkb-no-related-message">' +
                        '<p>' + i18n['no-related'] + '</p>' +
                        '</div>'
                    ));
            }

            updateStorage();
        });
    }

    /**
     * Roles selector control
     */
    function setupRolesSelector($container) {
        var $roleSelectors = $container.find('.fn-mkb-roles-selector-wrap');

        $roleSelectors.each(function(index, el) {
            var $wrap = $(el);
            var $control = $wrap.parents('.fn-control-wrap');
            var $store = $wrap.find('.fn-control');
            var $all = $wrap.find('.fn-mkb-role-select');
            var $toggle = $wrap.find('.fn-roles-toggle-all');
            var $guest = $wrap.find('.fn-mkb-role-select[data-role="guest"]');

            function updateStorage(allowed) {
                $store.val(JSON.stringify(allowed));
            }

            function getAllowedRoles() {
                return [].map.call($wrap.find('.fn-mkb-role-select:checked'), function(item) {
                    return item.dataset.role;
                });
            }

            $wrap.on('change', '.fn-mkb-role-select', function() {
                var allowed = getAllowedRoles();

                if (allowed.indexOf('guest') !== -1) {
                    allowed = [].map.call($all, function(item) {
                        return item.dataset.role;
                    });

                    $all.filter('[data-role!="guest"]').attr('disabled', 'disabled');
                    $all.attr('checked', 'checked');
                } else {
                    $all.attr('disabled', false);
                }

                updateStorage(allowed);
            });

            if ($guest.attr('checked')) {
                $all.filter('[data-role!="guest"]').attr('disabled', 'disabled');
            }

            /**
             * Toggle all link
             */
            $toggle.on('click', function(e) {
                e.preventDefault();

                var allowed = getAllowedRoles();

                if (allowed.length === $all.length) {
                    // remove all
                    $all.attr('checked', false).attr('disabled', false);
                    updateStorage([]);
                } else {
                    // enable all
                    $all.attr('checked', 'checked');
                    $all.filter('[data-role!="guest"]').attr('disabled', 'disabled');
                    updateStorage(["guest"]);
                }
            });

            /**
             * Restriction flush
             */
            $control.on('click', '.fn-roles-selector-flush', function(e) {
                e.preventDefault();

                var el = e.currentTarget;
                var $btn = $(el);

                if ($btn.hasClass('mkb-disabled')) {
                    return;
                }

                $btn.addClass('mkb-disabled');

                _fetch({
                    action: 'mkb_flush_restriction'
                }).always(function(response) {

                    if (response.status == 1) {
                        // error

                        $btn.removeClass('mkb-disabled').addClass('mkb-button-danger');
                        toastr.error('Restriction rules not cache flushed');

                        handleErrors(response);

                    } else {
                        // success

                        $btn.removeClass('mkb-disabled').addClass('mkb-button-success');
                        toastr.success('Restriction rules cache flushed');
                    }

                    setTimeout(function() {
                        $btn.removeClass('mkb-button-success mkb-button-danger');
                    }, 700);
                }).fail(function() {
                    toastr.error('Some error happened, try to refresh page');
                });
            });

            /**
             * Restriction roles log
             */
            $control.on('click', '.fn-roles-log-view', function(e) {
                e.preventDefault();

                var el = e.currentTarget;
                var $btn = $(el);

                if ($btn.hasClass('mkb-disabled')) {
                    return;
                }

                $btn.addClass('mkb-disabled');

                _fetch({
                    action: 'mkb_view_restriction_log'
                }).always(function(response) {

                    if (response.status == 1) {
                        // error

                        $btn.removeClass('mkb-disabled').addClass('mkb-button-danger');
                        toastr.error('Couldn\'t read log.');

                        handleErrors(response);

                    } else {
                        // success

                        $btn.removeClass('mkb-disabled').addClass('mkb-button-success');
                        $control.find('.fn-mkb-restriction-log-results').html(
                            response.log.length ?
                            '<ul>' +
                                response.log.map(function(line) {
                                    return '<li>' + line + '</li>';
                                }).join('') +
                            '</ul>' : '<p>No log yet</p>'
                        ).removeClass('mkb-hidden');
                    }

                    setTimeout(function() {
                        $btn.removeClass('mkb-button-success mkb-button-danger');
                    }, 700);
                }).fail(function() {
                    toastr.error('Some error happened, try to refresh the page');
                });
            });


            /**
             * Restriction roles clear
             */
            $control.on('click', '.fn-roles-log-clear', function(e) {
                e.preventDefault();

                var el = e.currentTarget;
                var $btn = $(el);

                if ($btn.hasClass('mkb-disabled')) {
                    return;
                }

                if (!confirm('Are you sure?')) {
                    return;
                }

                $btn.addClass('mkb-disabled');

                _fetch({
                    action: 'mkb_clear_restriction_log'
                }).always(function(response) {

                    if (response.status == 1) {
                        // error

                        $btn.removeClass('mkb-disabled').addClass('mkb-button-danger');
                        toastr.error('Couldn\'t clear log.');

                        handleErrors(response);

                    } else {
                        // success

                        $btn.removeClass('mkb-disabled').addClass('mkb-button-success');
                        $control.find('.fn-mkb-restriction-log-results').html('').addClass('mkb-hidden');

                        toastr.success('Restriction visitors log cleared');
                    }

                    setTimeout(function() {
                        $btn.removeClass('mkb-button-success mkb-button-danger');
                    }, 700);
                }).fail(function() {
                    toastr.error('Some error happened, try to refresh the page');
                });
            });
        });
    }

    /**
     * VC toggle uses on/off
     * @param $container
     */
    function setupVCToggle ($container) {
        $container.on('change', '.fn-control.mkb-toggle', function(e) {
            var $el = $(e.currentTarget);
            $el.attr('value', $el.attr('checked') ? 'on': 'off');
        });
    }

    /**
     * Media uploader
     */
    function setupMediaUpload($container) {
        var $mediaUploads = $container.find('.fn-control-wrap[data-type="media"]');

        $mediaUploads.each(function(index, media) {
            var frame;
            var $upload = $(media);
            var $store = $upload.find('.fn-media-store');
            var $addImgLink = $upload.find('.fn-mkb-add-media-img');
            var $delImgLink = $upload.find( '.fn-mkb-remove-media-img');
            var $delUrlImgLink = $upload.find( '.fn-mkb-remove-url-img');
            var $imgContainer = $upload.find( '.fn-mkb-media-preview');
            var $imgUrlContainer = $upload.find( '.fn-mkb-url-preview');
            var $imgIdInput = $upload.find( '.fn-media-upload-store' );
            var $mediaValueType = $upload.find('.fn-media-value-type');
            var $urlStore = $upload.find('.fn-mkb-url-store');
            var $mediaBlock = $upload.find('.fn-mkb-media-upload');
            var $urlBlock = $upload.find('.fn-mkb-url-upload');

            $addImgLink.on('click', function( e ) {
                e.preventDefault();

                if (frame) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: 'Select or Upload Media',
                    button: {
                        text: 'Use this media'
                    },
                    multiple: false
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();

                    $imgContainer.html('');
                    $imgContainer.append( '<img src="' + attachment.url + '" alt="" />' );
                    $imgContainer.removeClass('hidden');
                    $imgIdInput.val( attachment.id );
                    $addImgLink.addClass( 'hidden' );
                    $delImgLink.removeClass( 'hidden' );
                    updateStore();
                });

                frame.open();
            });

            $delImgLink.on( 'click', function( e ){
                e.preventDefault();

                $imgContainer.html('');
                $imgContainer.addClass('hidden');
                $addImgLink.removeClass('hidden');
                $delImgLink.addClass( 'hidden' );
                $imgIdInput.val('');
                updateStore();
            });

            $mediaValueType.on('change', function(e) {
                var state = Boolean($(e.currentTarget).attr('checked'));

                $urlBlock.toggleClass('hidden', !state);
                $mediaBlock.toggleClass('hidden', state);
                updateStore();
            });

            function clearUrlPreview() {
                $imgUrlContainer.html('');
                $imgUrlContainer.addClass('hidden');
                $delUrlImgLink.addClass('hidden')
            }

            $delUrlImgLink.on( 'click', function( e ){
                e.preventDefault();
                clearUrlPreview();
                $urlStore.val('');
                updateStore();
            });

            function updateStore() {
                var isUrl = Boolean($mediaValueType.attr('checked'));

                var state = {
                    isUrl: isUrl,
                    img: isUrl ? $urlStore.val() : $imgIdInput.val()
                };

                $store.val(JSON.stringify(state));
                $store.trigger('change');
            }

            $urlStore.on('input', function(e) {
                var value = e.currentTarget.value;

                if (!value.trim) {
                    clearUrlPreview();
                    return;
                }

                var newImg = new Image;

                newImg.onload = function () {
                    $imgUrlContainer.html('');
                    $imgUrlContainer.append( '<img src="' + this.src + '" alt="" />' );
                    $imgUrlContainer.removeClass('hidden');
                    $delUrlImgLink.removeClass('hidden')
                };

                newImg.onerror = clearUrlPreview;
                newImg.src = value;
                updateStore();
            });
        });
    }

    /**
     * Settings exporter
     */
    function setupSettingsExport($container) {
        $container.find('.fn-mkb-settings-export-container').each(function(index, exportContainer) {
            var $exportContainer = $(exportContainer);
            var $exportTextarea = $exportContainer.find('.fn-mkb-export-json-control');
            var $exportDownloadBtn = $exportContainer.find('.fn-mkb-settings-export-download');

            listenTo('settings:saved', function(e, settings) {
                var formattedExportJson = JSON.stringify(settings, null, 2);

                $exportTextarea.val(formattedExportJson);
                $exportDownloadBtn.attr('href', 'data:application/json;charset=utf-8,' + encodeURIComponent(formattedExportJson))
            });
        });
    }

    /**
     * Settings importer
     */
    function setupSettingsImport($container) {

        $container.find('.fn-mkb-settings-import-container').each(function(index, importContainer) {
            var $importContainer = $(importContainer);
            var $importTextarea = $importContainer.find('.fn-mkb-settings-import-control');
            var $importApplyBtn = $importContainer.find('.fn-mkb-settings-import-upload-btn');

            function onImportFileUploadChange(event) {
                var reader = new FileReader();
                reader.onload = onReaderLoad;
                reader.readAsText(event.target.files[0]);
            }

            function onReaderLoad(event){
                try {
                    var importData = JSON.parse(event.target.result);
                    $importTextarea.val(JSON.stringify(importData, null, 2)).trigger('input');
                    toastr.success('JSON file parsed successfully');
                } catch (e) {
                    $importTextarea.val('').trigger('input');
                    toastr.error('Wrong file format');
                }
            }

            function onImportControlChange(e) {
                $importApplyBtn.toggleClass('mkb-disabled', Boolean(e.currentTarget.value.trim().length === 0))
            }

            $importContainer.on('change', '.fn-mkb-settings-import-upload', onImportFileUploadChange);

            $importTextarea.on('change, input', onImportControlChange);

            $importApplyBtn.on('click', function(e) {

                e.preventDefault();

                if ($importApplyBtn.hasClass('mkb-disabled')) {
                    return;
                }

                $importApplyBtn.addClass('mkb-disabled');

                _fetch({
                    action: 'mkb_import_settings',
                    importData: JSON.stringify(JSON.parse($importTextarea.val()))
                }).done(function(response) {

                    if (response.status == 1) {
                        // error

                        $importApplyBtn.removeClass('mkb-disabled').addClass('mkb-button-danger');
                        toastr.error('Parse error: Couldn\'t import settings.');

                    } else {
                        // success

                        $importApplyBtn.removeClass('mkb-disabled').addClass('mkb-button-success');
                        toastr.success('Settings imported!');

                        window.location.reload();
                    }

                    setTimeout(function() {
                        $importApplyBtn.removeClass('mkb-button-success mkb-button-danger');
                    }, 700);
                }).fail(function() {
                    toastr.error('Network or server error happened, could not apply import');
                    $importApplyBtn.removeClass('mkb-disabled');
                });
            });
        });

    }

    /**
     * Purchase verification
     * @param $container
     */
    function setupEnvatoVerify ($container) {
        $container.find('.fn-mkb-envato-verify-container').each(function(index, verifyContainer) {
            var $verifyContainer = $(verifyContainer);
            var $code = $verifyContainer.find('.fn-mkb-envato-verify-control');
            var $submitBtn = $verifyContainer.find('.fn-mkb-envato-verify-submit');
            var $headerVerify = $('.fn-mkb-header-verification');

            $submitBtn.on('click', function(e) {
                e.preventDefault();

                var code = $code.val().trim().toLowerCase();

                if (!code) {
                    return toastr.error('Please, enter your purchase code');
                }

                if ($submitBtn.hasClass('mkb-disabled')) {
                    return;
                }

                $submitBtn.addClass('mkb-disabled');

                _fetch({
                    action: 'mkb_verify_purchase',
                    code: code
                }).done(function(response) {

                    if (response.status == 1) {
                        // error

                        $submitBtn.removeClass('mkb-disabled').addClass('mkb-button-danger');
                        toastr.error('Server error, could not verify purchase');

                    } else {
                        // success

                        if (response.check_result) {
                            toastr.success('Purchase verified, thank you!');

                            $headerVerify
                                .text('Registered')
                                .removeClass('mkb-header-verification--not-registered')
                                .addClass('mkb-header-verification--registered');
                        } else {
                            toastr.error('Purchase not verified, wrong purchase code');

                            $headerVerify
                                .text('Not registered')
                                .removeClass('mkb-header-verification--registered')
                                .addClass('mkb-header-verification--not-registered');
                        }

                        $submitBtn.removeClass('mkb-disabled').addClass('mkb-button-success');
                    }

                    setTimeout(function() {
                        $submitBtn.removeClass('mkb-button-success mkb-button-danger');
                    }, 700);
                }).fail(function() {
                    toastr.error('Network or server error happened, could not verify purchase');
                    $submitBtn.removeClass('mkb-disabled');
                });
            });
        });
    }

    function setupTooltips($container) {
        var $body = $('body');

        $container.find('.js-mkb-tooltip').each(function(index, tooltip) {
            var $tooltip = $(tooltip);
            var $tooltipPopup = $('<div class="mkb-tooltip-popup mkb-hidden">' + tooltip.dataset.tooltip + '</div>');

            $body.append($tooltipPopup);

            $tooltip.hover(function() {
                var tooltipRect = tooltip.getBoundingClientRect();

                $tooltipPopup.css({
                    left: tooltipRect.right + 10 + 'px',
                    top: tooltipRect.bottom + 10 + 'px'
                });
                $tooltipPopup.removeClass('mkb-hidden');
            }, function() {
                $tooltipPopup.addClass('mkb-hidden');
            });
        });
    }

    /**
     * Generic tabs
     * @param $container
     */
    function setupTabs($container) {
        $container.find('.js-mkb-tabs-container').each(function(index, tabsWrap) {
            var $tabsWrap = $(tabsWrap);
            var $tabs = $tabsWrap.find('.js-mkb-tabs li');
            var $tabItems = $tabsWrap.find('.js-mkb-tab-item');

            $tabsWrap.on('click', '.js-mkb-tabs li', function(e) {
                var $tab = $(e.currentTarget);
                var tabId = $tab.data('tab');

                $tabs.removeClass('mkb-active');
                $tab.addClass('mkb-active');

                $tabItems.addClass('mkb-hidden');
                $tabItems.filter('[data-tab="' + tabId + '"]').removeClass('mkb-hidden');
            });
        });
    }

    /**
     * Wrapper for localStorage
     * @type {{set: Function, get: Function, remove: Function}}
     */
    var storage = {
        set: function(key, value) {
            localStorage.setItem(key, value);
        },

        get: function(key) {
            return localStorage.getItem(key);
        },

        remove: function (key) {
            localStorage.removeItem(key);
        }
    };

    /**
     * Exports
     */
    window.MinervaUI = {
        addAjaxNonce: addAjaxNonce,
        fetch: _fetch,
        handleErrors: handleErrors,
        debounce: debounce,
        throttle: throttle,
        getFormData: getFormData,
        getFormDataForShortcode: getFormDataForShortcode,
        setupIconSelect: setupIconSelect,
        setupTabs: setupTabs,
        setupSettingsTabs: setupSettingsTabs,
        setupColorPickers: setupColorPickers,
        setupCSSSize: setupCSSSize,
        setupVCToggle: setupVCToggle,
        setupPageSelect: setupPageSelect,
        setupRelatedArticles: setupRelatedArticles,
        setupImageSelect: setupImageSelect,
        setupTopicsSelect: setupTopicsSelect,
        setupTermsSelect: setupTermsSelect,
        setupRolesSelector: setupRolesSelector,
        setupMediaUpload: setupMediaUpload,
        setupSettingsImport: setupSettingsImport,
        setupSettingsExport: setupSettingsExport,
        setupEnvatoVerify: setupEnvatoVerify,
        setupTooltips: setupTooltips,
        setupDependencies: setupDependencies,
        storage: storage,
        listenTo: listenTo,
        stopListeningTo: stopListeningTo,
        trigger: trigger,
        Popup: Popup
    };

    // init globals
    jQuery(document).ready(function() {
        setupTooltips($('#wpbody-content'));
    });
})(jQuery);