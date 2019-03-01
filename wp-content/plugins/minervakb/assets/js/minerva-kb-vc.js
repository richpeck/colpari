/**
 * MinervaKB Visual Composer controls
 */
(function($) {
    var GLOBAL_DATA = window.MinervaKB;
    var ui = window.MinervaUI;
    var i18n = GLOBAL_DATA.i18n;

    var $vcContainer = $('#vc_ui-panel-edit-element.vc_active');

    ui.setupRelatedArticles($vcContainer);
    ui.setupTopicsSelect($vcContainer);
    ui.setupTermsSelect($vcContainer);
    ui.setupImageSelect($vcContainer);
    ui.setupCSSSize($vcContainer);
    ui.setupVCToggle($vcContainer);
}(jQuery))