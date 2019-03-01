//lfb_dataMan

jQuery(window).on('load', function () {
    lfb_initManageDatas();
});

function lfb_initManageDatas() {
    jQuery('html,body').css({
        overflow: 'hidden'
    });
    var content = jQuery('<div id="lfb_bootstraped" class="lfb_bootstraped lfb_payForm"></div>');
    content.append('<div id="estimation_popup" data-form="0" class="wpe_bootstraped  wpe_fullscreen lfb_manageDataPanel"><div id="mainPanel" style="display: block !important;"></div></div>');
    jQuery('body').append(content);

    var mainPanel = jQuery('<div id="lfb_loginPanel"></div>');
    var loginPanel = jQuery('<div id="lfb_loginPanelCt"></div>');
    mainPanel.append('<h4><span class="glyphicon glyphicon-user"></span>' + lfb_dataMan.txtCustomersDataTitle + '</h4>');
    loginPanel.append('<div class="form-group"><div class="input-group"><div class="input-group-addon"><span class="glyphicon glyphicon-envelope"></span></div><input type="text" class="form-control" id="lfb_manEmail" placeholder="' + lfb_dataMan.customersDataLabelEmail + '"></div></div>');
    loginPanel.append('<div class="form-group"><div class="input-group"><div class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></div><input type="password" class="form-control" id="lfb_manPass" placeholder="' + lfb_dataMan.customersDataLabelPass + '"></div></div>');
    loginPanel.append('<a href="javascript:" onclick="lfb_loginManData();" class="btn btn-primary btn-circle"><span class="glyphicon glyphicon-check"></span></a>');
    loginPanel.append('<p  style="text-align: center;"><a href="javascript:" onclick="lfb_manDataPassLost();"  id="lfb_loginPassLink">' + lfb_dataMan.txtCustomersDataForgotPassLink + '</a></p>');
    mainPanel.append(loginPanel);
    jQuery('#mainPanel').append(mainPanel);

    var winPass = jQuery('<div class="modal fade" id="lfb_winPass" tabindex="-1" role="dialog" aria-hidden="true"></div>');
    winPass.append('<div class="modal-dialog"><div class="modal-content"><div class="modal-header"></div><div class="modal-body"></div></div></div>');
    winPass.find('.modal-header').append('<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title">' +lfb_dataMan.txtCustomersDataForgotPassLink+ '</h4>');
    winPass.find('.modal-body').append('<div class="form-group"><div class="input-group"><div class="input-group-addon"><span class="glyphicon glyphicon-envelope"></span></div><input type="text" class="form-control" id="lfb_manEmailPass" placeholder="' + lfb_dataMan.customersDataLabelEmail + '"></div></div>');
    winPass.find('.modal-body').append('<a href="javascript:" onclick="lfb_manDataPassLostSend();"  class="btn btn-primary btn-circle"><span class="glyphicon glyphicon-envelope"></span></a>');
    winPass.find('.modal-body').append('<p id="lfb_passLostConfirmation"  style="text-align: center;">' + lfb_dataMan.txtCustomersDataForgotPassSent + '</p>');
    jQuery('#mainPanel').append(winPass);
    jQuery('#lfb_winPass').find('#lfb_passLostConfirmation').hide();

    if (document.location.href.indexOf('&e=') > 0) {
        var email = document.location.href.substr(document.location.href.indexOf('&e=') + 3, document.location.href.length);
        jQuery('#lfb_manEmail').val(email);
        jQuery('#lfb_manEmailPass').val(email);
    }


}
function lfb_loginManData() {
    var email = jQuery('#lfb_manEmail').val();
    var pass = jQuery('#lfb_manPass').val();
    var error = false;
    jQuery('#lfb_manEmail').closest('.form-group').removeClass('has-error');
    jQuery('#lfb_manPass').closest('.form-group').removeClass('has-error');
    if (!lfb_mCheckEmail(email)) {
        jQuery('#lfb_manEmail').closest('.form-group').addClass('has-error');
        error = true;
    }
    if (pass.length < 3) {
        jQuery('#lfb_manPass').closest('.form-group').addClass('has-error');
        error = true;
    }
    if (!error) {
        jQuery.ajax({
            url: lfb_dataMan.ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_loginManD',
                email: email,
                pass: pass
            },
            success: function (rep) {
                rep = rep.trim();
                if (rep == '1') {
                    lfb_showManageDatasBtns();
                } else {
                    jQuery('#lfb_manEmail').closest('.form-group').addClass('has-error');
                    jQuery('#lfb_manPass').closest('.form-group').addClass('has-error');

                }
            }
        });
    }

}

function lfb_mCheckEmail(email) {
    if (email.indexOf("@") != "-1" && email.indexOf(".") != "-1" && email != "")
        return true;
    return false;
}

function lfb_showManageDatasBtns() {
    jQuery('#lfb_loginPanelCt').hide();
    jQuery('#lfb_loginPanel').append('<div id="lfb_manBtnsCt"></div>');
    jQuery('#lfb_loginPanel').find('#lfb_manBtnsCt').append('<a href="javascript:" onclick="lfb_manDownloadData();" class="btn btn-default"><span class="glyphicon glyphicon-cloud-download"></span>' + lfb_dataMan.txtCustomersDataDownloadLink + '</a>');
    jQuery('#lfb_loginPanel').find('#lfb_manBtnsCt').append('<a href="javascript:"  onclick="lfb_manEditData();" class="btn btn-warning"><span class="glyphicon glyphicon-pencil"></span>' + lfb_dataMan.txtCustomersDataEditLink + '</a>');
    jQuery('#lfb_loginPanel').find('#lfb_manBtnsCt').append('<a href="javascript:" onclick="lfb_manDelData();" class="btn btn-danger"><span class="glyphicon glyphicon-trash"></span>' + lfb_dataMan.txtCustomersDataDeleteLink + '</a>');
    jQuery('#lfb_loginPanel').find('#lfb_manBtnsCt').append('<a href="javascript:" onclick="lfb_manSignOut();" class="btn btn-default"><span class="glyphicon glyphicon-log-out"></span>' + lfb_dataMan.txtCustomersDataLeaveLink + '</a>');

    jQuery('#lfb_loginPanel').append('<div id="lfb_editDataForm"></div>');
    jQuery('#lfb_loginPanel').find('#lfb_editDataForm').append('<div class="form-group"><label>' + lfb_dataMan.customersDataLabelModify + '</label><br/><textarea class="form-control" id="lfb_editDataField"></textarea></div>');
    jQuery('#lfb_loginPanel').find('#lfb_editDataForm').append('<a href="javascript:" onclick="lfb_confirmEditData();" class="btn btn-primary"><span class="glyphicon glyphicon-send"></span>' + lfb_dataMan.txtCustomersDataEditLink + '</a>');
    jQuery('#lfb_loginPanel').find('#lfb_editDataForm').hide();

    jQuery('#lfb_loginPanel').append('<div id="lfb_delDataForm"></div>');
    jQuery('#lfb_loginPanel').find('#lfb_delDataForm').append('<div class="alert alert-warning">' + lfb_dataMan.txtCustomersDataWarningText + '</div>');
    jQuery('#lfb_loginPanel').find('#lfb_delDataForm').append('<a href="javascript:" onclick="lfb_confirmDelData();" class="btn btn-danger"><span class="glyphicon glyphicon-send"></span>' + lfb_dataMan.txtCustomersDataDeleteLink + '</a>');
    jQuery('#lfb_loginPanel').find('#lfb_delDataForm').hide();

}
function lfb_manSignOut(){
    jQuery.ajax({
        url: lfb_dataMan.ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_manSignOut'
        },
        success: function (rep) {
            document.location.href = document.location.href;
        }
    });
}
function lfb_confirmDelData() {

    jQuery.ajax({
        url: lfb_dataMan.ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_confirmDeleteData'
        },
        success: function (rep) {
            jQuery('#lfb_loginPanel').find('#lfb_delDataForm a.btn').remove();
            jQuery('#lfb_loginPanel').find('#lfb_delDataForm .alert').after('<p style="text-align: center;">' + lfb_dataMan.txtCustomersDataModifyValidConfirm + '</p>');
        }
    });


}
function lfb_confirmEditData() {
    if (jQuery('#lfb_editDataField').val().length < 4) {
        jQuery('#lfb_editDataField').closest('.form-group').addClass('has-error');
    } else {
        jQuery('#lfb_editDataField').closest('.form-group').removeClass('has-error');
        jQuery.ajax({
            url: lfb_dataMan.ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_confirmModifyData',
                details: jQuery('#lfb_editDataField').val()
            },
            success: function (rep) {
                jQuery('#lfb_loginPanel').find('#lfb_editDataForm').html('<p  style="text-align: center;">' + lfb_dataMan.txtCustomersDataModifyValidConfirm + '</p>');
            }
        });

    }
}
function lfb_manEditData() {
    jQuery('#lfb_loginPanel').find('#lfb_delDataForm').slideUp();
    jQuery('#lfb_loginPanel').find('#lfb_editDataForm').slideDown();
}
function lfb_manDelData() {
    jQuery('#lfb_loginPanel').find('#lfb_editDataForm').slideUp();
    jQuery('#lfb_loginPanel').find('#lfb_delDataForm').slideDown();
}
function lfb_manDataPassLost() {
    jQuery('#lfb_winPass').modal('show');
}

function lfb_manDataPassLostSend() {
    var email = jQuery('#lfb_manEmailPass').val();
    var error = false;
    jQuery('#lfb_manEmailPass').closest('.form-group').removeClass('has-error');
    if (!lfb_mCheckEmail(email)) {
        jQuery('#lfb_manEmailPass').closest('.form-group').addClass('has-error');
        error = true;
    }
    if (!error) {
        jQuery.ajax({
            url: lfb_dataMan.ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_forgotPassManD',
                email: email
            },
            success: function (rep) {
                rep = rep.trim();
                if (rep == '1') {
                    jQuery('#lfb_winPass').find('.form-group,a.btn').hide();
                    jQuery('#lfb_winPass').find('#lfb_passLostConfirmation').show();
                    setTimeout(function () {
                        jQuery('#lfb_winPass').modal('hide');
                        jQuery('#lfb_winPass').find('#lfb_passLostConfirmation').hide();
                        jQuery('#lfb_winPass').find('.form-group,a.btn').show();
                    }, 3000);
                } else {
                    jQuery('#lfb_manEmailPass').closest('.form-group').addClass('has-error');
                }
            }
        });
    }
}

function lfb_manDownloadData() {
    jQuery.ajax({
        url: lfb_dataMan.ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_downloadDataMan'
        },
        success: function (rep) {
            rep = rep.trim();
            var w = window.open("");
            w.document.write(rep);
            w.focus();
        }
    });
}