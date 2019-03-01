/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */
(function($) {
    'use strict';

    var GLOBAL_DATA = window.MinervaKB;
    var ui = window.MinervaUI;

    var $termSettingsContainer = $('.mkb-term-settings');

    function init() {
        if (!$termSettingsContainer.length) {
            return;
        }

        ui.setupColorPickers($termSettingsContainer);
        ui.setupIconSelect($termSettingsContainer);
        ui.setupImageSelect($termSettingsContainer);
        ui.setupPageSelect($termSettingsContainer);
        ui.setupRolesSelector($termSettingsContainer);
        ui.setupMediaUpload($termSettingsContainer);
    }

    $(document).ready(init);
})(jQuery);