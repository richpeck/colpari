/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */
(function($) {

    var GLOBAL_DATA = window.MinervaKB;
    var ui = window.MinervaUI;
    var i18n = GLOBAL_DATA.i18n;

    var OPTION_PREFIX = GLOBAL_DATA.optionPrefix;

    var $form = $('#mkb-plugin-settings');
    var $saveBtn = $('#mkb-plugin-settings-save');
    var $resetBtn = $('#mkb-plugin-settings-reset');
    var $demoImportBtn = $('.fn-mkb-demo-import');
    var $skipDemoImportBtn = $('.fn-mkb-skip-demo-import');
    var $header = $('.mkb-admin-page-header');

    /**
     * TODO make deps globally available
     * @type {Array}
     */

    var dependencies = [];

    function onDependencyTargetChange() {
        var data = ui.getFormData($form);

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

    function initDependencies() {
        var data = ui.getFormData($form);

        var $deps = $form.find('.mkb-control-wrap[data-dependency]');

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
                $form
                    .find('.mkb-control-wrap[data-name="' + OPTION_PREFIX + dependencyConfig['target'] + '"]')
                    .addClass('fn-dependency-target');

                dependencies.push({
                    _id: name.replace(OPTION_PREFIX, ''),
                    $el: $el,
                    config: dependencyConfig
                });
            }
        });

        $form.on('change', '.fn-dependency-target', onDependencyTargetChange);

        onDependencyTargetChange();
    }

    /**
     * Settings save
     * @param e
     */
    function onSaveSettings(e) {
        e.preventDefault();

        if ($saveBtn.hasClass('mkb-disabled')) {
            return;
        }

        $saveBtn.addClass('mkb-disabled');

        ui.fetch({
            action: 'mkb_save_settings',
            settings: ui.getFormData($form)
        }).always(function(response) {
            var text = $saveBtn.text();

            if (response.status == 1) {
                // error

                $saveBtn.text('Error');
                $saveBtn.removeClass('mkb-disabled').addClass('mkb-action-danger');

                ui.handleErrors(response);

            } else {
                // success

                ui.trigger('settings:saved', response.settings);

                $saveBtn.text('Success!');
                $saveBtn.removeClass('mkb-disabled').addClass('mkb-success');
            }

            setTimeout(function() {
                $saveBtn.text(text);
                $saveBtn.removeClass('mkb-success mkb-action-danger');
            }, 700);
        }).fail(function() {
            toastr.error('Some error happened, try to refresh page');
        });
    }

    /**
     * Settings reset
     * @param e
     */
    function onResetSettings(e) {
        e.preventDefault();

        if (!confirm(i18n['reset-confirm'])) {
            return;
        }

        if ($resetBtn.hasClass('mkb-disabled')) {
            return;
        }

        $resetBtn.addClass('mkb-disabled');

        ui.fetch({
            action: 'mkb_reset_settings'
        }).always(function(response) {
            var text = $resetBtn.text();

            if (response.status == 1) {
                // error

                $resetBtn.text('Error');
                $resetBtn.removeClass('mkb-disabled').addClass('mkb-action-danger');

                ui.handleErrors(response);

            } else {
                // success

                $resetBtn.text('Success!');
                $resetBtn.removeClass('mkb-disabled').addClass('mkb-success');
            }

            setTimeout(function() {
                $resetBtn.text(text);
                $resetBtn.removeClass('mkb-success mkb-action-danger');

                window.location.reload();
            }, 700);
        });
    }

    /**
     * Sticky header for settings
     */
    function setupStickyHeader() {
        var STICKY_OFFSET = 150;
        var sticky = false;
        var $header = $('.mkb-admin-page-header');
        var $body = $('body');
        var win = window;
        var doc = document.documentElement;

        $(win).on('scroll', ui.throttle(300, function() {
            var top = win.pageYOffset || doc.scrollTop;

            if (sticky && top >= STICKY_OFFSET || !sticky && top < STICKY_OFFSET) {
                return;
            }

            sticky = !sticky;
            $header.toggleClass('mkb-fixed', sticky);
            $body.toggleClass('mkb-header-fixed', sticky);
        }));
    }

    /**
     * Imports demo data
     * @param e
     */
    function onDemoImport(e) {
        var $btn = $(e.currentTarget);
        var $wrap = $btn.parents('.fn-control-wrap');
        var $outputWrap = $wrap.find('.fn-import-output');
        var $entitiesWrap = $wrap.find('.fn-import-entities');
        var $output = $wrap.find('.fn-import-output-content');
        var setHomePage = Boolean($wrap.find('.fn-import-set-home-page').attr('checked'));

        e.preventDefault();

        if ($btn.hasClass('mkb-disabled')) {
            return;
        }

        $btn.addClass('mkb-disabled');
        var original = $btn.html();
        $btn.text('Please wait, importing...');

        ui.fetch({
            action: 'mkb_demo_import',
            setHomePage: setHomePage
        }).done(function(response) {
            $btn.text('Import completed');
            $btn.removeClass('mkb-action-featured').addClass('mkb-success');
            $outputWrap.removeClass('mkb-hidden');
            $output.html(response.output);
            $entitiesWrap.html(response.entities_html);
            $entitiesWrap.removeClass('mkb-hidden');
            $('.fn-demo-import-remove-all').removeClass('mkb-hidden mkb-disabled').show();

            setTimeout(function() {
                $btn.removeClass('mkb-success mkb-disabled').addClass('mkb-action-featured');
                $btn.html(original);
            }, 1000);
        });
    }

    /**
     * Skips demo data import
     * @param e
     */
    function onSkipDemoImport(e) {
        var $btn = $(e.currentTarget);

        e.preventDefault();

        $btn.addClass('mkb-disabled');

        ui.fetch({
            action: 'mkb_skip_demo_import'
        }).done(function() {
            $btn.removeClass('mkb-disabled');

            $('.mkb-settings-tab:first-child a').click();
            $(document).css({
                scrollTop: 0
            });
        });
    }

    /**
     * Entities select and remove functionality
     */
    function setDemoImportControls() {
        var $container = $('.fn-import-entities');

        // Select entities
        $container.on('change', '.mkb-entities-table__row-item input[type="checkbox"]', function(e) {
            var $input = $(e.target);
            var $group = $input.parents('.fn-import-entities-group');
            var $groupInputs = $group.find('.mkb-entities-table__row-item input[type="checkbox"]');
            var $selected = $groupInputs.filter(':checked');
            var selectedIds = Array.prototype.map.call($selected, function(item) {
                return parseInt(item.dataset.id, 10);
            });
            var groupType = $group.data('entity-type');
            var $removeBtn = $group.find('.fn-import-entities-remove');

            $removeBtn
                .toggleClass('mkb-disabled', !Boolean($selected.length))
                .attr('data-ids', selectedIds);
        });

        // Select all
        $container.on('change', '.mkb-entities-table__header-item input[type="checkbox"]', function(e) {
            var $input = $(e.target);
            var $group = $input.parents('.fn-import-entities-group');
            var $groupInputs = $group.find('.mkb-entities-table__row-item input[type="checkbox"]');

            $groupInputs.attr('checked', Boolean($input.attr('checked')));
            $groupInputs.trigger('change');
        });

        // Remove entities
        $container.on('click', '.fn-import-entities-remove', function(e) {
            e.preventDefault();

            var $btn = $(e.target);
            var selected = $btn.attr('data-ids');
            var $group = $btn.parents('.fn-import-entities-group');
            var $groupCount = $group.find('.fn-mkb-entities-count');
            var groupType = $group.data('entity-type');

            if ($btn.hasClass('mkb-disabled') || !selected) {
                return;
            }

            selected = selected && selected.split && typeof selected.split === 'function' ? // string value
                selected.split(',') : // single int value
                [selected];

            selected = selected.map(function(id) { return parseInt(id, 10); });

            var selectedRows = selected.map(function(id) {
                return $group.find('input[data-id="' + id + '"]').parents('.mkb-entities-table__row');
            });

            if (confirm('Remove selected ' + groupType + '?')) {
                $btn.addClass('mkb-disabled');

                ui.fetch({
                    action: 'mkb_remove_import_entities',
                    ids: selected,
                    type: groupType
                }).done(function(response) {
                    if (response.status === 0) {
                        selectedRows.forEach(function($row) { $row.remove(); });
                        $groupCount.text($group.find('.mkb-entities-table__row').length);
                        $group.find('input[type="checkbox"]').attr('checked', false);
                    }

                    $btn.attr('data-ids', '');
                });
            }
        });

        // Remove all entities
        $('.fn-demo-import-remove-all').on('click', function(e) {
            e.preventDefault();

            var $btn = $(e.target);
            var $wrap = $btn.parents('.fn-control-wrap');
            var $entitiesWrap = $wrap.find('.fn-import-entities');

            if ($btn.hasClass('mkb-disabled')) {
                return;
            }

            if (confirm('Remove all imported data?')) {
                $btn.addClass('mkb-disabled');

                ui.fetch({
                    action: 'mkb_remove_all_import_entities'
                }).done(function(response) {
                    if (response.status === 0) {
                        $btn.addClass('mkb-hidden').hide();
                        $('.fn-import-output').addClass('mkb-hidden');
                        $entitiesWrap.html('');
                    }
                });
            }
        });
    }

    /**
     * Displays a form
     */
    function formReady() {
        $form.removeClass('mkb-loading');
    }

    function setActiveTab() {
        if (!GLOBAL_DATA.info.isDemoImported && !GLOBAL_DATA.info.isDemoSkipped) {
            $('a[href="#mkb_tab-demo_import_tab"]').click();
            $(document).css({
                scrollTop: 0
            });
        }
    }

    /**
     * Init
     */
    function init() {
        $saveBtn.on('click', onSaveSettings);
        $resetBtn.on('click', onResetSettings);
        $demoImportBtn.on('click', onDemoImport);
        $skipDemoImportBtn.on('click', onSkipDemoImport);

        ui.setupColorPickers($form);
        ui.setupIconSelect($form);
        ui.setupImageSelect($form);
        ui.setupTopicsSelect($form);
        ui.setupTermsSelect($form);
        ui.setupCSSSize($form);
        ui.setupPageSelect($form);
        ui.setupRolesSelector($form);
        ui.setupMediaUpload($form);
        ui.setupSettingsExport($form);
        ui.setupSettingsImport($form);
        ui.setupEnvatoVerify($form);
        ui.setupSettingsTabs($form);

        setDemoImportControls();
        initDependencies();
        setActiveTab();
        formReady();

        toastr.options.positionClass = "toast-top-right";
        toastr.options.timeOut = 10000;
        toastr.options.showDuration = 200;

        setupStickyHeader();
    }

    $(document).ready(init);
})(jQuery);