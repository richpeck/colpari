/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */
(function($) {

    var GLOBAL_DATA = window.MinervaKB;
    var ui = window.MinervaUI;

    var $uninstallBtn = $('.fn-mkb-uninstall-btn');

    function handleUninstall (e) {
        e.preventDefault();

        if ($uninstallBtn.hasClass('mkb-disabled')) {
            return;
        }

        $uninstallBtn.addClass('mkb-disabled');

        ui.fetch({
            action: 'mkb_uninstall_plugin'
        }).always(function(response) {
            var text = $uninstallBtn.text();

            if (response.status == 1) {
                // error

                $uninstallBtn.text('Error');
                $uninstallBtn.removeClass('mkb-disabled');

            } else {
                // success

                $uninstallBtn.text('Success!');
                $uninstallBtn.removeClass('mkb-disabled').addClass('mkb-success');

                window.location.reload();
            }

            setTimeout(function() {
                $uninstallBtn.text(text);
                $uninstallBtn.removeClass('mkb-success');
            }, 700);
        }).fail(function() {
            toastr.error('Some error happened, try to refresh page');
        });
    }

    /**
     * Init
     */
    function init() {

        $uninstallBtn.on('click', handleUninstall);

        toastr.options.positionClass = "toast-top-right";
        toastr.options.timeOut = 10000;
        toastr.options.showDuration = 200;
    }

    $(document).ready(init);
})(jQuery);