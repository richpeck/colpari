/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */
(function($) {
    'use strict';

    var GLOBAL_DATA = window.MinervaKB;
    var ui = window.MinervaUI;

    var $pageSettingsContainer = $('.fn-mkb-page-settings'); //only page settings
    var $settingsContainers = $('.fn-mkb-settings-container'); // all settings containers, including switch

    function buildSectionEl(options) {
        return $(
            '<div class="mkb-layout-editor__section fn-layout-editor-section mkb-section-loading" data-type="' + options.type + '">' +
            '<div class="fn-section-inner"></div>' +
            '<div class="mkb-loader">' +
            '<span class="inner1"></span>' +
            '<span class="inner2"></span>' +
            '<span class="inner3"></span>' +
            '</div>' +
            '</div>'
        );
    }

    function loadSectionHTML(options, position) {
        return ui.fetch({
            action: 'mkb_get_section_html',
            section_type: options.type,
            position: position
        });
    }

    function addSection($container, options) {
        var $sections = $container.find('.mkb-layout-editor__section');
        var hasSections = Boolean($sections.length);
        var $section = buildSectionEl(options);

        if (hasSections) {
            $section.insertAfter($sections.last());
        } else {
            $container.prepend($section);
        }

        loadSectionHTML(options, $sections.length)
            .then(function (response) {
                var $html = $(response.html);

                $section.find('.fn-section-inner').append($html);
                $section.find('.mkb-loader').remove();
                $section.removeClass('mkb-section-loading');

                ui.setupColorPickers($section);
                ui.setupTopicsSelect($section);
                ui.setupTermsSelect($section);
                ui.setupMediaUpload($section);

                ui.setupDependencies($section);

                reorderInputs($container);
                updateAllStores();
            });
    }

    function reorderInputs($container) {
        $container.find('.fn-section-settings-store').each(function(index, el) {
            $(el).attr('name', 'mkb_page_section[' + index + ']');
        });
    }

    function updateSectionStore(e) {
        var $el = $(e.currentTarget);
        var $storeWrap = $el.parents('.fn-section-settings-container');
        var $store = $storeWrap.find('.fn-section-settings-store');

        if ($storeWrap.length) {
            $store.val(JSON.stringify({
                    type: $store.data('type'),
                    settings: ui.getFormData($storeWrap)
                }
            ));
        }
    }

    function updateAllStores($container) {
        $container = $pageSettingsContainer || $container;

        var $storeWrap = $container.find('.fn-section-settings-container');

        $storeWrap.each(function(index, el) {
            var $wrap = $(el);
            var $store = $wrap.find('.fn-section-settings-store');

            if ($wrap.length) {
                $store.val(JSON.stringify({
                        type: $store.data('type'),
                        settings: ui.getFormData($wrap)
                    }
                ));
            }
        });
    }

    function setupLayoutEditors() {
        var $editorContainers = $('.mkb-settings-layout-editor-container');

        $editorContainers.each(function(index, el) {
            var $container = $(el);
            var $sectionsContainer = $container.find('.mkb-layout-editor__sections');
            var $sections = $sectionsContainer.find('.fn-layout-editor-section');
            var $wrap = $container.parents('.mkb-control-wrap');
            var $input = $wrap.find('.mkb-layout-editor-hidden-input');
            var $addSectionBtn = $container.find('.fn-layout-editor-add');

            $addSectionBtn.on('click', function(e) {
                e.preventDefault();

                $(e.currentTarget).toggleClass('add-new--open');
            });

            $container.on('click', '.fn-layout-editor-add-section', function(e) {
                e.preventDefault();

                var link = e.currentTarget;

                addSection($sectionsContainer, {
                    type: link.dataset.type
                });
            });

            $sectionsContainer.sortable({
                items: '.fn-layout-editor-section',
                handle: '.fn-layout-editor-section-handle',
                update: function( event, ui ) {
                    reorderInputs($container);
                }
            });

            $container.on('click', '.fn-section-settings-toggle', function(e) {
                e.preventDefault();

                var $btn = $(e.currentTarget);
                var $section = $btn.parents('.fn-layout-editor-section');
                var $settings = $section.find('.fn-section-settings-container');

                $btn.toggleClass('mkb-pressed');

                $settings.toggleClass('mkb-hidden');

                var $handle = $section.find('.fn-layout-editor-section-handle');

                // fix for reorder handle repaint
                $handle.css('top', '2px');
                setTimeout(function() {
                    $handle.css('top', '3px');
                }, 50);
            });

            $container.on('click', '.fn-section-remove', function(e) {
                e.preventDefault();

                var $btn = $(e.currentTarget);
                var $section = $btn.parents('.fn-layout-editor-section');

                var remove = confirm("Are you sure you want to remove section?");

                if (remove) {
                    $section.remove();
                    reorderInputs($container);
                }
            });

            $sections.each(function(index, section) {
                ui.setupDependencies($(section));
            });

            $container.on('change', '.fn-control', updateSectionStore);
            $container.on('input', '.fn-control', updateSectionStore);

            updateAllStores();
        });
    }

    function init() {
        if (!$settingsContainers.length) {
            return;
        }

        setupLayoutEditors();
        ui.setupSettingsTabs($settingsContainers); // used for both switch and builder

        if (!$pageSettingsContainer.length) {
            return;
        }

        ui.setupColorPickers($pageSettingsContainer, {
            onChange: updateAllStores.bind(this, $pageSettingsContainer)
        });
        ui.setupImageSelect($pageSettingsContainer);
        ui.setupIconSelect($pageSettingsContainer);
        ui.setupCSSSize($pageSettingsContainer);
        ui.setupTopicsSelect($pageSettingsContainer);
        ui.setupTermsSelect($pageSettingsContainer);
        ui.setupMediaUpload($pageSettingsContainer);
    }

    $(document).ready(init);
})(jQuery);