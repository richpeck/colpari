var lfb_isLinking = false;
var lfb_links = new Array();
var lfb_linkCurrentIndex = -1;
var lfb_canvasTimer;
var lfb_mouseX, lfb_mouseY;
var lfb_linkGradientIndex = 1;
var lfb_itemWinTimer;
var lfb_currentDomElement = false;
var lfb_currentStep = false;
var lfb_currentStepID = 0;
var lfb_lock = false;
var lfb_defaultStep = false;
var lfb_steps = false;
var lfb_params;
var lfb_currentLinkIndex = 0;
var lfb_settings;
var lfb_formfield;
var lfb_currentFormID = 0;
var lfb_actTimer;
var lfb_currentForm = false;
var lfb_currentItemID = 0;
var lfb_canSaveLink = true;
var lfb_canDuplicate = true;
var lfb_openChartsAuto = false;
var lfb_currentCharts = false, lfb_currentChartsOptions, lfb_currentChartsData;
var lfb_currentRedirEdit = 0;
var lfb_distanceModeQt = false;
var lfb_editorCustomJS;
var lfb_editorCustomCSS;
var lfb_editorLog;
var lfb_formToDelete = 0;
var lfb_calculationModeQt = false;
var lfb_currentLogID = 0;
var lfb_orderModified = false;
var lfb_logEditorStepThStyle = "";
var lfb_logEditorTdStyle = "";
var lfb_logEditorSummaryTable = false;
var lfb_currentLayerTr;
var lfb_currentLogCurrency = '$';
var lfb_currentLogCurrencyPosition = 'left';
var lfb_currentLogDecSep = '.';
var lfb_currentLogThousSep = ',';
var lfb_currentLogMilSep = '';
var lfb_currentLogSubTxt = '';
var lfb_currentLogSubTotal = 0;
var lfb_currentLogTotal = 0;
var lfb_currentLogIsPaid = false;
var lfb_currentLogUseSub = false;
var lfb_currentLogCanPay = false;
var lfb_logsTable;
var lfb_disableLinksAnim = false;
var lfb_currentCalendarID = 1;
var lfb_currentCalendarEventID = 0;
var lfb_currentCalendarEvents = new Array();
var lfb_currentCalendarDefaultReminders = new Array();
var lfb_currentCalendarCats = new Array();
var lfb_currentCalendarDaysWeek = new Array();
var lfb_currentCalendarDisabledHours = new Array();

lfb_data = lfb_data[0];

jQuery(document).ready(function () {
    jQuery('#lfb_loader').remove();
    jQuery('#wpcontent').append('<div id="lfb_loader"><div class="lfb_spinner"><div class="double-bounce1"></div><div class="double-bounce2"></div></div></div>');
    jQuery('#lfb_loader .lfb_spinner').css({
        top: jQuery(window).height() / 2 - jQuery('#wpadminbar').height() / 2
    });
    jQuery(window).resize(function () {
        jQuery('#lfb_loader .lfb_spinner').css({
            top: jQuery(window).height() / 2 - jQuery('#wpadminbar').height() / 2
        });
        jQuery('#lfb_bootstraped,#estimation_popup').css({
            minHeight: jQuery(window).height() - jQuery('#wpadminbar').height()
        });
        jQuery('#lfb_emailTemplateAdmin').css({
            minHeight: jQuery('#lfb_emailTemplateCustomer').outerHeight()
        });

        lfb_updatelLeftPanels();
    });
    jQuery('#lfb_bootstraped,#estimation_popup').css({
        minHeight: jQuery(window).height() - jQuery('#wpadminbar').height()
    });
    lfb_updatelLeftPanels();
    jQuery('.lfb_btnWinClose').parent().click(function () {
        lfb_closeWin(jQuery(this).closest('.lfb_window'));
    });
    jQuery('#lfb_stepsContainer').droppable({
        drop: function (event, ui) {
            var $object = jQuery(ui.draggable[0]);
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'lfb_saveStepPosition',
                    stepID: $object.attr('data-stepid'),
                    posX: parseInt($object.css('left')),
                    posY: parseInt($object.css('top'))
                }
            });
            var currentStep = lfb_getStepByID(parseInt($object.attr('data-stepid')));
            if (currentStep != null && currentStep.content != null) {
                currentStep.content.previewPosX = parseInt($object.css('left'));
                currentStep.content.previewPosY = parseInt($object.css('top'));
            }
            lfb_updateStepCanvas();
        }
    });
    jQuery('body').css({
        overflow: 'initial'
    });
    jQuery('#lfb_editorLog').summernote({
        height: 500,
        minHeight: null,
        maxHeight: null,
    });

    window.old_tb_remove = window.tb_remove;
    window.tb_remove = function () {
        window.old_tb_remove();
        lfb_formfield = null;
    };
    window.original_send_to_editor = window.send_to_editor;
    window.send_to_editor = function (html) {
        if (lfb_formfield) {
            var alt = jQuery('img', html).attr('alt');
            fileurl = jQuery('img', html).attr('src');
            if (jQuery('img', html).length == 0) {
                fileurl = jQuery(html).attr('src');
                alt = jQuery(html).attr('alt');
            }
            lfb_formfield.val(fileurl);
            lfb_formfield.trigger('keyup');
            if (lfb_formfield.closest('.picOnly').length > 0) {
                jQuery('#lfb_itemTabGeneral [name="imageDes"]').val(alt);
            }
            tb_remove();
        } else {
            window.original_send_to_editor(html);
        }
    };
    jQuery('#wpwrap').css({
        height: jQuery('#lfb_bootstraped').height() + 48
    });

    if (jQuery('textarea[name="customJS"]').length > 0) {
        lfb_editorCustomJS = CodeMirror.fromTextArea(jQuery('textarea[name="customJS"]').get(0), {
            mode: "javascript",
            lineNumbers: true
        });
        lfb_editorCustomCSS = CodeMirror.fromTextArea(jQuery('textarea[name="customCss"]').get(0), {
            mode: "css",
            lineNumbers: true
        });
    }
    setInterval(function () {
        if (jQuery('#lfb_winStep').css('display') == 'block') {
            jQuery('#wpwrap').css({
                height: jQuery('#lfb_winStep').height() + 48
            });

        } else {
            jQuery('#wpwrap').css({
                height: jQuery('#lfb_bootstraped').height() + 48
            });

        }
    }, 1000);

    jQuery(document).mousemove(function (e) {
        if (lfb_isLinking) {
            lfb_mouseX = e.pageX - jQuery('#lfb_stepsContainer').offset().left;
            lfb_mouseY = e.pageY - jQuery('#lfb_stepsContainer').offset().top;
        }
    });
    jQuery(window).resize(lfb_updateStepsDesign);
    lfb_itemWinTimer = setInterval(lfb_updateWinItemPosition, 30);
    jQuery('#lfb_actionSelect').change(function () {
        lfb_changeActionBubble(jQuery('#lfb_actionSelect').val());
    });
    jQuery('#lfb_interactionSelect').change(function () {
        lfb_changeInteractionBubble(jQuery('#lfb_interactionSelect').val());
    });

    jQuery('#lfb_interactionBubble,#lfb_actionBubble,#lfb_linkBubble,#lfb_fieldBubble,#lfb_calculationValueBubble,#lfb_emailValueBubble,#lfb_distanceValueBubble,#lfb_calculationDatesDiffBubble').hover(function (e) {
        jQuery(this).addClass('lfb_hover');
    }, function (e) {
        jQuery(this).removeClass('lfb_hover');
    });
    jQuery('#lfb_interactionBubble,#lfb_actionBubble,#lfb_linkBubble,#lfb_fieldBubble,#lfb_calculationValueBubble,#lfb_emailValueBubble,#lfb_distanceValueBubble,#lfb_calculationDatesDiffBubble').find('select').focus(function () {
        jQuery(this).addClass('lfb_hover');
    }).blur(function () {
        jQuery(this).removeClass('lfb_hover');
    });
    jQuery('body').click(function () {
        if (!jQuery('#lfb_interactionBubble').is('.lfb_hover')) {
            jQuery('#lfb_interactionBubble').fadeOut();
        }
        if (!jQuery('#lfb_actionBubble').is('.lfb_hover') && !jQuery('#lfb_websiteFrame').is('.lfb_hover') && !jQuery('.lfb_selectElementPanel').is('.lfb_hover')) {
            jQuery('#lfb_actionBubble').fadeOut();
        }
        if (!jQuery('#lfb_linkBubble').is('.lfb_hover')) {
            jQuery('#lfb_linkBubble').fadeOut();
        }
        if (!jQuery('#lfb_calculationValueBubble').is('.lfb_hover') && jQuery('#lfb_calculationValueBubble').find('.lfb_hover').length == 0) {
            jQuery('#lfb_calculationValueBubble').fadeOut();
        }
        if (!jQuery('#lfb_emailValueBubble').is('.lfb_hover') && jQuery('#lfb_emailValueBubble').find('.lfb_hover').length == 0) {
            jQuery('#lfb_emailValueBubble').fadeOut();
        }

        if (!jQuery('#lfb_fieldBubble').is('.lfb_hover') && jQuery('#lfb_fieldBubble').find('.lfb_hover').length == 0) {
            jQuery('#lfb_fieldBubble').fadeOut();
        }
        if (!jQuery('#lfb_distanceValueBubble').is('.lfb_hover') && jQuery('#lfb_distanceValueBubble').find('.lfb_hover').length == 0) {
            jQuery('#lfb_distanceValueBubble').fadeOut();
        }
        if (!jQuery('#lfb_calculationDatesDiffBubble').is('.lfb_hover') && jQuery('#lfb_calculationDatesDiffBubble').find('.lfb_hover').length == 0) {
            jQuery('#lfb_calculationDatesDiffBubble').fadeOut();
        }

    });
    jQuery('#lfb_bootstraped .modal-dialog').hover(function () {
        jQuery(this).addClass('lfb_hover');
    }, function () {
        jQuery(this).removeClass('lfb_hover');
    });
    jQuery('#lfb_bootstraped .modal').on('hide.bs.modal', function (e) {
        if (!jQuery(this).find('.modal-dialog').is('.lfb_hover')) {
            e.preventDefault();
        }
    });
    jQuery('#lfb_closeWinActivationBtn').click(function () {
        if (!lfb_lock) {
            jQuery('#lfb_winActivation').modal('hide');
            jQuery('#lfb_winActivation').delay(200).fadeOut();
        }
    });
    if (jQuery('#lfb_winActivation').is('[data-show="true"]') && document.referrer.indexOf('admin.php?page=lfb_menu') < 0) {
        lfb_lock = true;

        jQuery('#lfb_closeWinActivationBtn .lfb_text').data('num', 10).html('Wait 10 seconds');
        lfb_actTimer = setInterval(function () {
            var num = jQuery('#lfb_closeWinActivationBtn .lfb_text').data('num');
            num--;
            if (num > 0) {
                jQuery('#lfb_closeWinActivationBtn .lfb_text').data('num', num).html('Wait ' + num + ' seconds');
            } else {
                jQuery('#lfb_closeWinActivationBtn').removeClass('disabled');
                lfb_lock = false;
                jQuery('#lfb_closeWinActivationBtn .lfb_text').data('num', '').html('Close');
            }
        }, 1000);
    } else {
        jQuery('#lfb_winActivation').attr('data-show', 'false');
    }
    jQuery('#lfb_winActivation').on('hide.bs.modal', function (e) {
        if (lfb_lock && !jQuery('#lfb_winActivation .modal-dialog').is('.lfb_hover')) {
            e.preventDefault();
        }
    });
    jQuery(document).mousedown(function (e) {
        if (e.button == 2 && lfb_isLinking) {
            lfb_isLinking = false;
        }
    });

    jQuery('input[type="number"][min]').focusout(function () {
        if (jQuery(this).val().indexOf('-') > -1 && (!jQuery(this).is('[min]') || jQuery(this).attr('min').indexOf('-') < 0)) {
            jQuery(this).val(parseInt(jQuery(this).attr('min')));
        }
        if (parseFloat(jQuery(this).val()) < parseFloat(jQuery(this).attr('min'))) {
            jQuery(this).val(jQuery(this).attr('min'));
        }
        if (parseFloat(jQuery(this).val()) > parseFloat(jQuery(this).attr('max'))) {
            jQuery(this).val(jQuery(this).attr('max'));
        }
    });
    jQuery('.form-group').each(function () {
        var self = this;
        if (jQuery(self).find('small').length > 0 && jQuery(self).find('.form-control').length > 0) {
            jQuery(this).find('.form-control').b_tooltip({
                title: jQuery(self).find('small').html()
            });
        }
    });
    jQuery('#lfb_winSendOrberByEmail').find('[data-switch="switch"]').wrap('<div class="switch" data-on-label="' + lfb_data.texts['Yes'] + '" data-off-label="' + lfb_data.texts['No'] + '" />').parent().bootstrapSwitch({onLabel: lfb_data.texts['Yes'], offLabel: lfb_data.texts['No']});
    jQuery("#lfb_bootstraped.lfb_bootstraped [data-toggle='switch']").wrap('<div class="switch" data-on-label="' + lfb_data.texts['Yes'] + '" data-off-label="' + lfb_data.texts['No'] + '" />').parent().bootstrapSwitch({onLabel: lfb_data.texts['Yes'], offLabel: lfb_data.texts['No']});
    jQuery("#lfb_bootstraped.lfb_bootstraped #lfb_winItem [data-switch='switch']").wrap('<div class="switch" data-on-label="' + lfb_data.texts['Yes'] + '" data-off-label="' + lfb_data.texts['No'] + '" />').parent().bootstrapSwitch({onLabel: lfb_data.texts['Yes'], offLabel: lfb_data.texts['No']});
    jQuery("#lfb_bootstraped.lfb_bootstraped #lfb_winStep [data-switch='switch']").wrap('<div class="switch" data-on-label="' + lfb_data.texts['Yes'] + '" data-off-label="' + lfb_data.texts['No'] + '" />').parent().bootstrapSwitch({onLabel: lfb_data.texts['Yes'], offLabel: lfb_data.texts['No']});
    jQuery("#lfb_bootstraped.lfb_bootstraped #lfb_formFields [data-switch='switch']").wrap('<div class="switch" data-on-label="' + lfb_data.texts['Yes'] + '" data-off-label="' + lfb_data.texts['No'] + '" />').parent().bootstrapSwitch({onLabel: lfb_data.texts['Yes'], offLabel: lfb_data.texts['No']});
    jQuery("#lfb_bootstraped.lfb_bootstraped #lfb_winDeleteOrder [data-switch='switch']").wrap('<div class="switch" data-on-label="' + lfb_data.texts['Yes'] + '" data-off-label="' + lfb_data.texts['No'] + '" />').parent().bootstrapSwitch({onLabel: lfb_data.texts['Yes'], offLabel: lfb_data.texts['No']});

    var dateFormat = lfb_data.dateFormat;
    dateFormat = dateFormat.replace(/\\\//g, "/");
    if (dateFormat.indexOf('h') == -1) {
        dateFormat += ' hh:ii';
    }
    jQuery('.lfb_datetimepicker').datetimepicker({
        timeZone: '',
        showMeridian: (lfb_data.dateMeridian == '1'),
        format: dateFormat,
        container: jQuery('#estimation_popup.wpe_bootstraped')
    }).on('show', function () {
        jQuery('.datetimepicker .table-condensed .prev').show();
        jQuery('.datetimepicker .table-condensed .switch').show();
        jQuery('.datetimepicker .table-condensed .next').show();
    });
    jQuery('#lfb_imageLayersTable tbody').sortable({
        helper: function (e, tr) {
            var $originals = tr.children();
            var $helper = tr.clone();
            $helper.children().each(function (index)
            {
                jQuery(this).width($originals.eq(index).width());
            });
            return $helper;
        },
        stop: function (event, ui) {
            var layers = '';
            jQuery('#lfb_imageLayersTable tbody tr[data-layerid]').each(function (i) {
                layers += jQuery(this).attr('data-layerid') + ',';
            });
            if (layers.length > 0) {
                layers = layers.substr(0, layers.length - 1);
            }
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'lfb_changeLayersOrder',
                    layers: layers
                }
            });
        }
    });

    jQuery('#lfb_tabLastStep table tbody').sortable({
        helper: function (e, tr) {
            var $originals = tr.children();
            var $helper = tr.clone();
            $helper.children().each(function (index)
            {
                jQuery(this).width($originals.eq(index).width());
            });
            return $helper;
        },
        stop: function (event, ui) {
            var fields = '';
            jQuery('#lfb_tabLastStep table tbody tr[data-fieldid]').each(function (i) {
                fields += jQuery(this).attr('data-fieldid') + ',';
            });
            if (fields.length > 0) {
                fields = fields.substr(0, fields.length - 1);
            }
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'lfb_changeLastFieldsOrders',
                    fields: fields
                }
            });
        }
    });

    var todayDate = new Date();
    jQuery('#lfb_calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay,listWeek'
        },
        defaultDate: new Date(),
        editable: true,
        locale: lfb_data.locale,
        navLinks: true,
        eventLimit: true,
        events: function (start, end, timezone, callback) {
            if (jQuery('#lfb_winCalendars').css('display') != 'none') {
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: {
                        action: 'lfb_getCalendarEvents',
                        formID: lfb_currentFormID,
                        calendarID: lfb_currentCalendarID,
                        start: start.toDate(),
                        end: end.toDate()
                    },
                    success: function (doc) {
                        doc = jQuery.parseJSON(doc, true);
                        lfb_currentCalendarEvents = doc.events;
                        lfb_currentCalendarDefaultReminders = doc.reminders;
                        lfb_currentCalendarCats = doc.categories;
                        lfb_currentCalendarDaysWeek = doc.daysWeek;
                        lfb_currentCalendarDisabledHours = doc.disabledHours;
                        lfb_generateCalendarCatsSelect();
                        lfb_generateCalendarCatsTable();
                        lfb_updateCalendarDaysWeekTable();
                        lfb_updateCalendarHoursEnabledTable();

                        jQuery.each(lfb_currentCalendarDaysWeek, function (i) {
                            lfb_currentCalendarDaysWeek[i] = parseInt(this);
                        });

                        jQuery('#lfb_calendar').fullCalendar('option', 'hiddenDays', lfb_currentCalendarDaysWeek);
                        jQuery.each(lfb_currentCalendarEvents, function () {
                            if (this.allDay == 1) {
                                this.allDay = true;
                            } else {
                                this.allDay = false;
                            }
                        });
                        jQuery('#lfb_calendarLeftMenu [name="orderID"] option[value!="0"]').remove();
                        jQuery.each(doc.orders, function () {
                            jQuery('#lfb_calendarLeftMenu [name="orderID"]').append('<option value="' + this.id + '">' + this.title + '</option>');
                        });
                        if (lfb_currentCalendarEventID > 0) {
                            lfb_editCalendarEvent(lfb_currentCalendarEventID);
                        }
                        callback(lfb_currentCalendarEvents);
                        jQuery('#lfb_loader').fadeOut();
                    }
                });
            }
        },
        eventDrop: function (event, delta, revertFunc) {
            var end = event.start.toDate().toISOString().slice(0, 19).replace('T', ' ');
            if (event.end != null) {
                end = event.end.toDate().toISOString().slice(0, 19).replace('T', ' ');
            }
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'lfb_updateCalendarEvent',
                    formID: lfb_currentFormID,
                    eventID: event.id,
                    start: event.start.toDate().toISOString().slice(0, 19).replace('T', ' '),
                    end: end
                },
                success: function (rep) {
                }
            });
        },
        eventResize: function (event, jsEvent, ui, view) {
            var end = event.start.toDate().toISOString().slice(0, 19).replace('T', ' ');
            if (event.end != null) {
                end = event.end.toDate().toISOString().slice(0, 19).replace('T', ' ');
            }
            var eventData = lfb_getCalendarEvent(event.id);
            eventData.end = end;
            if (lfb_currentCalendarEventID == event.id) {
                jQuery('#lfb_calendarLeftMenu [name="end"]').datetimepicker('setDate', moment(end, 'YYYY-MM-DD HH:mm').toDate());
            }

            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'lfb_updateCalendarEvent',
                    formID: lfb_currentFormID,
                    eventID: event.id,
                    start: event.start.toDate().toISOString().slice(0, 19).replace('T', ' '),
                    end: end
                },
                success: function (rep) {
                }
            });
        },
        eventClick: function (calEvent, jsEvent, view) {
            lfb_editCalendarEvent(calEvent.id);
        },
        dayRender: function (date, cell) {
            var link = jQuery('<a href="javascript:" class="lfb_calendarLinkAddEventDay"><span class="glyphicon glyphicon-plus"></span></a>');
            link.on('click', function () {
                lfb_addCalendarEvent(date, cell);
            });
            jQuery(cell).append(link);
        },
        dayClick: function (date, jsEvent, view) {
            if (view.name == 'agendaWeek' || view.name == 'agendaDay' || view.name == 'month') {
                lfb_addCalendarEvent(date);
            }
        }
    });
    jQuery('#lfb_calendarLeftMenu [name="allDay"]').on('change', lfb_calEventFullDayChange);
    jQuery('#lfb_calendarLeftMenu [name="start"]').on('change', lfb_calEventStartDateChange);
    jQuery('#lfb_selectCalendar').on('change', lfb_selectCalendarChange);
    jQuery('#lfb_calendarLeftMenu [name="orderID"]').on('change', lfb_calEventOrderIDChange);
    jQuery('#lfb_calendarLeftMenu [name="categoryID"]').on('change', lfb_calEventCategoryIDChange);

    jQuery('#calEventContent').summernote({
        height: 200,
        minHeight: null,
        maxHeight: null,
    });

    jQuery('.lfb_iconslist li a').click(function () {
        jQuery(this).closest('.form-group').find('.btn.dropdown-toggle>span.lfb_name').html(jQuery(this).attr('data-icon'));
        jQuery(this).closest('.form-group').find('input.lfb_iconField').val(jQuery(this).attr('data-icon'));
        jQuery(this).closest('ul').find('li.lfb_active').removeClass('lfb_active');
        jQuery(this).closest('li').addClass('lfb_active');
    });
    jQuery('input.lfb_iconField').on('change', function () {
        if (jQuery(this).closest('.form-group').find('.btn.dropdown-toggle>span.lfb_name').html() != jQuery(this).val()) {
            jQuery(this).closest('.form-group').find('.btn.dropdown-toggle>span.lfb_name').html(jQuery(this).val());
        }
    });

    jQuery('#lfb_winEditCalendarCat input[data-switch="switch"]').wrap('<div class="switch" data-on-label="' + lfb_data.texts['Yes'] + '" data-off-label="' + lfb_data.texts['No'] + '" />').parent().bootstrapSwitch();
    jQuery('#lfb_winEditCalendarCat .colorpick').each(function () {
        var $this = jQuery(this);
        if (jQuery(this).prev('.lfb_colorPreview').length == 0) {
            jQuery(this).before('<div class="lfb_colorPreview" style="background-color:#' + $this.val().substr(1, 7) + '"></div>');
        }
        jQuery(this).prev('.lfb_colorPreview').click(function () {
            jQuery(this).next('.colorpick').trigger('click');
        });
        jQuery(this).colpick({
            color: $this.val().substr(1, 7),
            onChange: function (hsb, hex, rgb, el, bySetColor) {
                jQuery(el).val('#' + hex);
                jQuery(el).prev('.lfb_colorPreview').css({
                    backgroundColor: '#' + hex
                });
            }
        });
    });

    jQuery('.imageBtn').click(function () {
        lfb_formfield = jQuery(this).prev('input');
        tb_show('', 'media-upload.php?TB_iframe=true');
        return false;
    });

    jQuery('#lfb_winExport input[data-switch="switch"]').wrap('<div class="switch" data-on-label="' + lfb_data.texts['Yes'] + '" data-off-label="' + lfb_data.texts['No'] + '" />').parent().bootstrapSwitch();
    jQuery('#lfb_winExport input[data-switch="switch"]').on('change', lfb_exportForms);
    lfb_initCharts();
    jQuery('#lfb_winActivation').modal();
    jQuery('[data-toggle="tooltip"]').b_tooltip();

    lfb_initWeeksDaysText();
    lfb_loadSettings();
    lfb_initFormsBackend();
    if (lfb_data.designForm != 0) {
        lfb_loadForm(lfb_data.designForm);
    }
    jQuery('html,body').scrollTop(0);
});
function lfb_updateCalendarLeftMenuHeight() {
    var calendarMenuHeight = jQuery('#lfb_calendar').outerHeight();
    if (jQuery('#estimation_popup').height() > calendarMenuHeight) {
        calendarMenuHeight = jQuery('#estimation_popup').outerHeight();
    }
    jQuery('#lfb_calendarLeftMenu').css({
        height: calendarMenuHeight
    });
}
function lfb_updatelLeftPanels() {
    jQuery('.lfb_lPanel.lfb_lPanelLeft').each(function () {
        var newHeight = jQuery(this).next('.lfb_lPanel.lfb_lPanelMain').outerHeight();
        if (jQuery('#estimation_popup').height() > newHeight) {
            newHeight = jQuery('#estimation_popup').outerHeight();
        }
        jQuery(this).css({
            height: newHeight
        });
    });
}

function lfb_openWinLicense() {
    if (lfb_data.lscV == 1) {
        jQuery('#lfb_lscUnverified').hide();
        jQuery('#lfb_winActivation .alert').hide();
    } else {
        jQuery('#lfb_lscUnverified').show();
    }
    lfb_lock = false;
    jQuery('#lfb_winActivation').modal('show');
    jQuery('#lfb_winActivation').fadeIn();
    jQuery('#lfb_closeWinActivationBtn').removeAttr('disabled');
    jQuery('#lfb_closeWinActivationBtn').removeClass('disabled');
}

function lfb_initFormsBackend() {
    jQuery('#lfb_formFields [name="use_paypal"]').on('change', lfb_formPaypalChange);
    jQuery('#lfb_formFields [name="use_stripe"]').on('change', lfb_formStripeChange);
    jQuery('#lfb_formFields [name="isSubscription"]').on('change', lfb_formIsSubscriptionChange);
    jQuery('#lfb_formFields [name="gravityFormID"]').on('change', lfb_formGravityChange);
    jQuery('#lfb_formFields [name="save_to_cart"]').on('change', lfb_formWooChange);
    jQuery('#lfb_formFields [name="save_to_cart_edd"]').on('change', lfb_formEDDChange);
    jQuery('#lfb_formFields [name="email_toUser"]').on('change', lfb_formEmailUserChange);
    jQuery('#lfb_formFields [name="legalNoticeEnable"]').on('change', lfb_formLegalNoticeChange);
    jQuery('#lfb_formFields [name="useSummary"]').on('change', lfb_formUseSummaryChange);
    jQuery('#lfb_formFields [name="intro_enabled"]').on('change', lfb_formUseIntroChange);
    jQuery('#lfb_formFields [name="paypal_useIpn"]').on('change', lfb_formIpnChange);
    jQuery('#lfb_formFields [name="useCoupons"]').on('change', lfb_formUseCouponsChange);
    jQuery('#lfb_formFields [name="useRedirectionConditions"]').on('change', lfb_changeUseRedirs);
    jQuery('#lfb_formFields [name="useMailchimp"]').on('change', lfb_changeMailchimp);
    jQuery('#lfb_formFields [name="useMailpoet"]').on('change', lfb_changeMailpoet);
    jQuery('#lfb_formFields [name="useGetResponse"]').on('change', lfb_changeGetResponse);
    jQuery('#lfb_formFields [name="useGoogleFont"]').on('change', lfb_useGoogleFontChange);
    jQuery('#lfb_formFields [name="scrollTopPage"]').on('change', lfb_scrollTopPageChange);
    jQuery('#lfb_formFields [name="previousStepBtn"]').on('change', lfb_previousStepBtnChange);
    jQuery('#lfb_formFields [name="paymentType"]').on('change', lfb_updateEmailPaymentType);
    jQuery('#lfb_formFields [name="totalIsRange"]').on('change', lfb_totalIsRangeChange);
    jQuery('#lfb_formFields [name="totalRangeMode"]').on('change', lfb_totalRangeModeChange);
    jQuery('#lfb_formFields [name="getResponseKey"]').focusout(lfb_changeGetResponseList);
    jQuery('#lfb_formFields [name="mailchimpKey"]').focusout(lfb_changeMailchimpList);
    jQuery('#lfb_formFields [name="stripe_payMode"]').on('change', lfb_changeStripePayMode);
    jQuery('#lfb_formFields [name="paypal_payMode"]').on('change', lfb_changePaypalPayMode);
    jQuery('#lfb_formFields [name="summary_hidePrices"]').on('change', lfb_changeSummaryPriceShow);
    jQuery('#lfb_formFields [name="summary_hideTotal"]').on('change', lfb_changeSummaryPriceShow);
    jQuery('#lfb_formFields [name="enableFloatingSummary"]').on('change', lfb_changeEnableFloatingSummary);
    jQuery('#lfb_formFields [name="summary_hidePrices"]').on('change', lfb_changeSummaryHidePrices);
    jQuery('#lfb_formFields [name="useCaptcha"]').on('change', lfb_changeSendEmailLastStep);
    jQuery('#lfb_formFields [name="enableSaveForLaterBtn"]').on('change', lfb_changeSaveForLater);
    jQuery('#lfb_formFields [name="sendPdfAdmin"]').on('change', lfb_changeSendPdfAdmin);
    jQuery('#lfb_formFields [name="sendPdfCustomer"]').on('change', lfb_changeSendPdfUser);
    jQuery('#lfb_formFields [name="enableCustomersData"]').on('change', lfb_changeEnableCustomerData);

    jQuery('#lfb_chartsTypeSelect').on('change', lfb_chartsTypeChange);
    jQuery('#lfb_chartsMonth').on('change', lfb_chartsMonthChange);
    jQuery('#lfb_chartsYear').on('change', lfb_chartsYearChange);

    lfb_formGravityChange();
    lfb_formEDDChange();
    lfb_formLegalNoticeChange();
    lfb_formEmailUserChange();
    lfb_formUseSummaryChange();
    lfb_formPaypalChange();
    lfb_formStripeChange();
    lfb_formUseIntroChange();
    lfb_formUseCouponsChange();
    lfb_changeUseRedirs();
    lfb_changeMailchimp();
    lfb_changeMailpoet();
    lfb_changeGetResponse();
    lfb_changeGetResponseList();
    lfb_changeMailchimpList();
    lfb_useGoogleFontChange();
    lfb_chartsTypeChange();
    lfb_totalIsRangeChange();
    lfb_totalRangeModeChange();
    lfb_scrollTopPageChange();
    lfb_previousStepBtnChange();
    lfb_updateEmailPaymentType();
    lfb_changeStripePayMode();
    lfb_changePaypalPayMode();
    lfb_changeSummaryPriceShow();
    lfb_changeSummaryHidePrices();
    lfb_changeEnableFloatingSummary();
    lfb_changeSendEmailLastStep();
    lfb_changeSaveForLater();
    lfb_changeSendPdfAdmin();
    lfb_changeSendPdfUser();
    lfb_changeEnableCustomerData();
    lfb_formWooChange();

    jQuery('#lfb_calculationValueBubble select[name="itemID"]').on('change', lfb_updateCalculationsValueElements);
    jQuery('#lfb_emailValueBubble select[name="itemID"]').on('change', lfb_updateEmailValueElements);

}
function lfb_changeEnableCustomerData() {
    if (jQuery('#lfb_formFields [name="enableCustomersData"]').is(':checked')) {
        jQuery('#lfb_formFields [name="customersDataEmailLink"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="customersDataWarningText"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="customersDataDownloadLink"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="customersDataDeleteLink"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="customersDataLeaveLink"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="customersDataDeleteDelay"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="customersDataTitle"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="email_toUser"]').parent().bootstrapSwitch('setState', true);
        jQuery('#lfb_formFields [name="email_toUser"]').parent().parent().addClass('deactivate')
        jQuery('#alertCustomerData').slideDown();
        jQuery('#lfb_formFields [name="email_toUser"]').closest('.switch.has-switch').attr('title', lfb_data.texts['userEmailTipDisabled']).b_tooltip('fixTitle');

    } else {
        jQuery('#lfb_gdprSettings').slideUp();
        jQuery('#lfb_formFields [name="customersDataEmailLink"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="customersDataWarningText"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="customersDataDownloadLink"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="customersDataDeleteLink"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="customersDataLeaveLink"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="customersDataDeleteDelay"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="customersDataTitle"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="email_toUser"]').parent().parent().removeClass('deactivate');
        jQuery('#lfb_formFields [name="email_toUser"]').closest('.switch.has-switch').attr('title', lfb_data.texts['userEmailTip']).b_tooltip('fixTitle');
        jQuery('#alertCustomerData').slideUp();
    }

}
function lfb_changeSendPdfUser() {
    if (jQuery('#lfb_formFields [name="sendPdfCustomer"]').is(':checked')) {
        jQuery('#lfb_pdfTemplateUserContainer').slideDown();
    } else {
        jQuery('#lfb_pdfTemplateUserContainer').slideUp();
    }
}
function lfb_changeSendPdfAdmin() {
    if (jQuery('#lfb_formFields [name="sendPdfAdmin"]').is(':checked')) {
        jQuery('#lfb_pdfTemplateAdminContainer').slideDown();
    } else {
        jQuery('#lfb_pdfTemplateAdminContainer').slideUp();
    }
}
function lfb_changeSaveForLater() {
    if (jQuery('#lfb_formFields [name="enableSaveForLaterBtn"]').is(':checked')) {
        jQuery('#lfb_formFields [name="saveForLaterLabel"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="saveForLaterDelLabel"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="saveForLaterIcon"]').closest('.form-group').slideDown();
    } else {
        jQuery('#lfb_formFields [name="saveForLaterLabel"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="saveForLaterDelLabel"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="saveForLaterIcon"]').closest('.form-group').slideUp();
    }
}
function lfb_changeSendEmailLastStep() {
    var chkPossible = true;
    if (jQuery('#lfb_formFields [name="use_paypal"]').is(':checked') || jQuery('#lfb_formFields [name="use_stripe"]').is(':checked')
            || jQuery('#lfb_formFields [name="useCaptcha"]').is(':checked') || jQuery('#lfb_formFields [name="legalNoticeEnable"]').is(':checked')
            || jQuery('#lfb_formFields [name="useCoupons"]').is(':checked')) {
        chkPossible = false;
    }
    if (chkPossible) {
        jQuery.each(lfb_currentForm.fields, function () {
            var item = this;
            if ((item.type != 'richtext' && item.type != 'shortcode' && item.type != 'separator')) {
                if (item.isHidden == '0' || item.isRequired == '1' || item.validation != '') {
                    chkPossible = false;
                }
            }
        });
    }
    if (chkPossible) {
        jQuery('#lfb_formFields [name="sendEmailLastStep"]').closest('.switch.has-switch').removeClass('deactivate');
    } else {
        jQuery('#lfb_formFields [name="sendEmailLastStep"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_formFields [name="sendEmailLastStep"]').closest('.switch.has-switch').addClass('deactivate');
    }
}
function lfb_changeSummaryHidePrices() {
    if (jQuery('#lfb_formFields [name="summary_hidePrices"]').is(':checked')) {
        jQuery('#lfb_formFields [name="floatSummary_hidePrices"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_formFields [name="floatSummary_hidePrices"]').closest('.form-group').slideUp();
    } else {
        jQuery('#lfb_formFields [name="floatSummary_hidePrices"]').closest('.form-group').slideDown();
    }

}
function lfb_changeEnableFloatingSummary() {
    if (jQuery('#lfb_formFields [name="enableFloatingSummary"]').is(':checked')) {
        jQuery('#lfb_formFields [name="floatSummary_label"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="floatSummary_icon"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="floatSummary_numSteps"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="floatSummary_hidePrices"]').closest('.form-group').slideDown();
    } else {
        jQuery('#lfb_formFields [name="floatSummary_label"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="floatSummary_icon"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="floatSummary_numSteps"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="floatSummary_hidePrices"]').closest('.form-group').slideUp();
    }
}
function lfb_changeSummaryPriceShow() {
    if (jQuery('#lfb_formFields [name="summary_hidePrices"]').is(':checked') || jQuery('#lfb_formFields [name="summary_hideTotal"]').is(':checked')) {
        jQuery('#lfb_formFields [name="summary_showAllPricesEmail"]').closest('.form-group').slideDown();
    } else {
        jQuery('#lfb_formFields [name="summary_showAllPricesEmail"]').closest('.form-group').slideUp();
    }
}
function lfb_changePaypalPayMode() {
    if (jQuery('#lfb_formFields [name="use_paypal"]').is(':checked') && !jQuery('#lfb_formFields [name="isSubscription"]').is(':checked')) {

        jQuery('#lfb_formFields [name="paypal_payMode"]').closest('.form-group').slideDown();
        if (jQuery('#lfb_formFields [name="paypal_payMode"]').val() == "") {
            jQuery('#lfb_formFields [name="paypal_fixedToPay"]').closest('.form-group').slideUp();
            jQuery('#lfb_formFields [name="percentToPay"]').closest('.form-group').slideUp();
        } else if (jQuery('#lfb_formFields [name="paypal_payMode"]').val() == "fixed") {
            jQuery('#lfb_formFields [name="paypal_fixedToPay"]').closest('.form-group').slideDown();
            jQuery('#lfb_formFields [name="percentToPay"]').closest('.form-group').slideUp();
        } else if (jQuery('#lfb_formFields [name="paypal_payMode"]').val() == "percent") {
            jQuery('#lfb_formFields [name="paypal_fixedToPay"]').closest('.form-group').slideUp();
            jQuery('#lfb_formFields [name="percentToPay"]').closest('.form-group').slideDown();
        }
    } else {
        jQuery('#lfb_formFields [name="paypal_payMode"]').val('');
        jQuery('#lfb_formFields [name="paypal_payMode"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="paypal_fixedToPay"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="percentToPay"]').closest('.form-group').slideUp();
    }
}
function lfb_changeStripePayMode() {
    if (jQuery('#lfb_formFields [name="use_stripe"]').is(':checked') && !jQuery('#lfb_formFields [name="isSubscription"]').is(':checked')) {
        jQuery('#lfb_formFields [name="stripe_payMode"]').closest('.form-group').slideDown();
        if (jQuery('#lfb_formFields [name="stripe_payMode"]').val() == "") {
            jQuery('#lfb_formFields [name="stripe_fixedToPay"]').closest('.form-group').slideUp();
            jQuery('#lfb_formFields [name="stripe_percentToPay"]').closest('.form-group').slideUp();

        } else if (jQuery('#lfb_formFields [name="stripe_payMode"]').val() == "fixed") {
            jQuery('#lfb_formFields [name="stripe_fixedToPay"]').closest('.form-group').slideDown();
            jQuery('#lfb_formFields [name="stripe_percentToPay"]').closest('.form-group').slideUp();
        } else if (jQuery('#lfb_formFields [name="stripe_payMode"]').val() == "percent") {
            jQuery('#lfb_formFields [name="stripe_fixedToPay"]').closest('.form-group').slideUp();
            jQuery('#lfb_formFields [name="stripe_percentToPay"]').closest('.form-group').slideDown();
        }
    } else {
        jQuery('#lfb_formFields [name="stripe_payMode"]').val('');
        jQuery('#lfb_formFields [name="stripe_payMode"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="stripe_fixedToPay"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="stripe_percentToPay"]').closest('.form-group').slideUp();
    }
}
function lfb_previousStepBtnChange() {
    if (jQuery('#lfb_formFields [name="previousStepBtn"]').is(':checked')) {
        jQuery('#lfb_formFields [name="previousStepButtonIcon"]').closest('.form-group').slideDown();
    } else {
        jQuery('#lfb_formFields [name="previousStepButtonIcon"]').closest('.form-group').slideUp();
    }
}
function lfb_formDistanceAsQtChange() {
    if (jQuery('#lfb_winItem [name="useDistanceAsQt"]').is(':checked')) {
        jQuery('#lfb_winItem #lfb_distanceQtContainer').slideDown();
    } else {
        jQuery('#lfb_winItem #lfb_distanceQtContainer').slideUp();
    }

}
function lfb_changeUseRedirs() {
    if (jQuery('#lfb_formFields [name="useRedirectionConditions"]').is(':checked')) {
        jQuery('#lfb_formFields #lfb_redirConditionsContainer').slideDown();
    } else {
        jQuery('#lfb_formFields #lfb_redirConditionsContainer').slideUp();
    }
}
function lfb_scrollTopPageChange() {
    if (jQuery('#lfb_formFields [name="scrollTopPage"]').is(':checked')) {
        jQuery('#lfb_formFields [name="scrollTopMargin"]').closest('.form-group').slideUp();
    } else {
        jQuery('#lfb_formFields [name="scrollTopMargin"]').closest('.form-group').slideDown();
    }
}
function lfb_useGoogleFontChange() {
    if (jQuery('#lfb_formFields [name="useGoogleFont"]').is(':checked')) {
        jQuery('#lfb_formFields [name="googleFontName"]').closest('.form-group').slideDown();
    } else {
        jQuery('#lfb_formFields [name="googleFontName"]').closest('.form-group').slideUp();
    }
}
function lfb_changeMailchimp() {
    if (jQuery('#lfb_formFields [name="useMailchimp"]').is(':checked')) {
        jQuery('#lfb_formFields [name="mailchimpKey"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="mailchimpList"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="mailchimpOptin"]').closest('.form-group').slideDown();

        lfb_changeMailchimpList();
    } else {
        jQuery('#lfb_formFields [name="mailchimpKey"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="mailchimpList"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="mailchimpOptin"]').closest('.form-group').slideUp();
    }
}
function lfb_changeMailpoet() {
    if (jQuery('#lfb_formFields [name="useMailpoet"]').is(':checked')) {
        jQuery('#lfb_formFields [name="mailPoetList"]').closest('.form-group').slideDown();
        lfb_changeMailpoetList();
    } else {
        jQuery('#lfb_formFields [name="mailPoetList"]').closest('.form-group').slideUp();
    }
}
function lfb_changeGetResponse() {
    if (jQuery('#lfb_formFields [name="useGetResponse"]').is(':checked')) {
        jQuery('#lfb_formFields [name="getResponseKey"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="getResponseList"]').closest('.form-group').slideDown();
        lfb_changeGetResponseList();
    } else {
        jQuery('#lfb_formFields [name="getResponseKey"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="getResponseList"]').closest('.form-group').slideUp();
    }
}
function lfb_changeMailchimpList() {
    jQuery('#lfb_formFields [name="mailchimpList"] option').remove();
    var apiKey = jQuery('#lfb_formFields [name="mailchimpKey"]').val();
    if (apiKey != "") {
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_getMailchimpLists',
                apiKey: apiKey
            },
            success: function (rep) {
                jQuery('#lfb_formFields [name="mailchimpList"]').html(rep);
                if (jQuery('#lfb_formFields [name="mailchimpList"] option[value="' + jQuery('#lfb_tabSettings [name="mailchimpList"]').attr('data-initial') + '"]').length > 0) {
                    jQuery('#lfb_formFields [name="mailchimpList"]').val(jQuery('#lfb_tabSettings [name="mailchimpList"]').attr('data-initial'));
                }
                if (lfb_currentForm != false) {
                    jQuery('#lfb_formFields [name="mailchimpList"]').val(lfb_currentForm.form.mailchimpList);
                }
            }
        });
    }
}
function lfb_changeMailpoetList() {
    jQuery('#lfb_formFields [name="mailPoetList"] option').remove();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_getMailpoetLists'
        },
        success: function (rep) {
            jQuery('#lfb_formFields [name="mailPoetList"]').html(rep);
            if (jQuery('#lfb_formFields [name="mailPoetList"] option[value="' + jQuery('#lfb_tabSettings [name="mailPoetList"]').attr('data-initial') + '"]').length > 0) {
                jQuery('#lfb_formFields [name="mailPoetList"]').val(jQuery('#lfb_tabSettings [name="mailPoetList"]').attr('data-initial'));
            }
            if (lfb_currentForm != false) {
                jQuery('#lfb_formFields [name="mailPoetList"]').val(lfb_currentForm.form.mailPoetList);
            }
        }
    });
}
function lfb_changeGetResponseList() {
    var apiKey = jQuery('#lfb_formFields [name="getResponseKey"]').val();
    jQuery('#lfb_tabSettings [name="getResponseList"] option').remove();
    if (apiKey != "") {
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_getGetResponseLists',
                apiKey: apiKey
            },
            success: function (rep) {
                jQuery('#lfb_formFields [name="getResponseList"]').html(rep);
                if (jQuery('#lfb_formFields [name="getResponseList"] option[value="' + jQuery('#lfb_tabSettings [name="getResponseList"]').attr('data-initial') + '"]').length > 0) {
                    jQuery('#lfb_formFields [name="getResponseList"]').val(jQuery('#lfb_tabSettings [name="getResponseList"]').attr('data-initial'));
                }
                if (lfb_currentForm != false) {
                    jQuery('#lfb_formFields [name="getResponseList"]').val(lfb_currentForm.form.getResponseList);
                }
            }
        });
    }
}
function lfb_formUseCouponsChange() {
    if (jQuery('#lfb_formFields [name="useCoupons"]').is(':checked')) {
        jQuery('#lfb_formFields .lfb_couponsContainer').slideDown();
    } else {
        jQuery('#lfb_formFields .lfb_couponsContainer').slideUp();
    }
    lfb_changeSendEmailLastStep();
}
function lfb_formUseIntroChange() {
    if (jQuery('#lfb_formFields [name="intro_enabled"]').is(':checked')) {
        jQuery('#lfb_formFields [name="intro_title"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="intro_text"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="intro_btn"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="intro_image"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="introButtonIcon"]').closest('.form-group').slideDown();


    } else {
        jQuery('#lfb_formFields [name="intro_title"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="intro_text"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="intro_btn"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="intro_image"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="introButtonIcon"]').closest('.form-group').slideUp();

    }
}
function lfb_formIsSubscriptionChange() {
    if (jQuery('#lfb_formFields [name="isSubscription"]').is(':checked')) {
        jQuery('#lfb_formFields [name="subscription_text"]').parent().slideDown();
        jQuery('#lfb_formFields [name="totalIsRange"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_formFields [name="paypal_payMode"]').parent().slideUp();
        jQuery('#lfb_formFields [name="paypal_payMode"]').val('');
        jQuery('#lfb_formFields [name="stripe_payMode"]').parent().slideUp();
        jQuery('#lfb_formFields [name="stripe_payMode"]').val('');
        if (jQuery('#lfb_formFields [name="use_paypal"]').is(':checked')) {
            jQuery('#lfb_formFields [name="paypal_subsFrequency"]').parent().slideDown();
            jQuery('#lfb_formFields [name="paypal_subsMaxPayments"]').parent().slideDown();
            jQuery('#lfb_formFields [name="percentToPay"]').parent().slideUp();
        }
        if (jQuery('#lfb_formFields [name="use_stripe"]').is(':checked')) {
            jQuery('#lfb_formFields [name="stripe_subsFrequencyType"]').parent().slideDown();
        }
        jQuery('#lfb_winItem').find('[name="isSinglePrice"]').closest('.form-group').slideDown();
    } else {
        jQuery('#lfb_formFields [name="subscription_text"]').parent().slideUp();
        jQuery('#lfb_formFields [name="paypal_subsFrequency"]').parent().slideUp();
        jQuery('#lfb_formFields [name="paypal_subsMaxPayments"]').parent().slideUp();
        jQuery('#lfb_formFields [name="stripe_subsFrequencyType"]').parent().slideUp();
        if (jQuery('#lfb_formFields [name="use_paypal"]').is(':checked')) {
        }
        jQuery('#lfb_winItem').find('[name="isSinglePrice"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem').find('[name="isSinglePrice"]').closest('.form-group').slideUp();
    }
    lfb_changePaypalPayMode();
    lfb_changeStripePayMode();
}
function lfb_formUseSummaryChange() {
    if (jQuery('#lfb_formFields [name="useSummary"]').is(':checked')) {
        jQuery('#lfb_formFields [name="summary_title"]').parent().slideDown();
    } else {
        jQuery('#lfb_formFields [name="summary_title"]').parent().slideUp();
    }
}

function lfb_formLegalNoticeChange() {
    if (jQuery('#lfb_formFields [name="legalNoticeEnable"]').is(':checked')) {
        jQuery('#lfb_formFields [name="legalNoticeTitle"]').parent().slideDown();
        jQuery('#lfb_formFields #lfb_legalNoticeContent').closest('.form-group').slideDown();
    } else {
        jQuery('#lfb_formFields [name="legalNoticeTitle"]').parent().slideUp();
        jQuery('#lfb_formFields #lfb_legalNoticeContent').closest('.form-group').slideUp();
    }
    lfb_changeSendEmailLastStep();
}
function lfb_totalIsRangeChange() {
    if (jQuery('#lfb_formFields [name="totalIsRange"]').is(':checked')) {
        jQuery('#lfb_formFields [name="use_paypal"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_formFields [name="use_stripe"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_formFields [name="isSubscription"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_formFields [name="save_to_cart"]').parent().bootstrapSwitch('setState', false);
        if (jQuery('#lfb_formFields select[name="gravityFormID"]').val() > 0) {
            jQuery('#lfb_formFields select[name="gravityFormID"]').val('0');
        }
        jQuery('#lfb_formFields [name="totalRangeMode"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="totalRange"]').closest('.form-group').slideDown();
    } else {
        jQuery('#lfb_formFields [name="totalRange"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="totalRangeMode"]').closest('.form-group').slideUp();
    }

}
function lfb_totalRangeModeChange() {
    if (jQuery('#lfb_formFields [name="totalRangeMode"]').val() == '') {
        jQuery('#lfb_totalRangeLabelFixed').show();
        jQuery('#lfb_totalRangeLabelPercent').hide();
    } else {
        jQuery('#lfb_totalRangeLabelFixed').hide();
        jQuery('#lfb_totalRangeLabelPercent').show();
    }
}
function lfb_formPaypalChange() {
    if (jQuery('#lfb_formFields [name="use_paypal"]').is(':checked')) {
        jQuery('#lfb_formPaypal').slideDown();
        jQuery('#lfb_formFields [name="totalIsRange"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_formFields [name="use_stripe"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_formFields [name="save_to_cart"]').parent().bootstrapSwitch('setState', false);
        if (jQuery('#lfb_formFields select[name="gravityFormID"]').val() > 0) {
            jQuery('#lfb_formFields select[name="gravityFormID"]').val('0');
        }
        jQuery('#lfb_formFields .lfb_wooOption').slideUp();
        lfb_formIpnChange();
        lfb_changePaypalPayMode();
    } else {
        jQuery('#lfb_formPaypal').slideUp();
        if (!jQuery('#lfb_formFields [name="use_stripe"]').is(':checked')) {
            jQuery('#lfb_formFields .lfb_wooOption').slideDown();
        }

    }
    lfb_formIsSubscriptionChange();
    lfb_updatePaymentType();
}
function lfb_updatePaymentType() {
    if ((jQuery('#lfb_formFields [name="use_paypal"]').is(':checked') && jQuery('#lfb_formFields [name="paypal_useIpn"]').is(':checked')) ||
            jQuery('#lfb_formFields [name="use_stripe"]').is(':checked')) {
        jQuery('#lfb_formFields [name="paymentType"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="txt_payFormFinalTxt"]').closest('.form-group').slideDown();

    } else {
        jQuery('#lfb_formFields [name="paymentType"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="txt_payFormFinalTxt"]').closest('.form-group').slideUp();
        jQuery('[name="paymentType"]').val('form');

    }
    lfb_updateEmailPaymentType();
    lfb_changeSendEmailLastStep();
}
function lfb_updateEmailPaymentType() {
    if ((jQuery('#lfb_formFields [name="use_paypal"]').is(':checked') && jQuery('#lfb_formFields [name="paypal_useIpn"]').is(':checked')) ||
            jQuery('#lfb_formFields [name="use_stripe"]').is(':checked')) {
        if (jQuery('#lfb_formFields [name="paymentType"]').val() == 'email') {
            jQuery('[name="emailPaymentType"]').closest('.form-group').slideDown();
        } else {
            jQuery('[name="emailPaymentType"]').closest('.form-group').slideUp();
            jQuery('[name="emailPaymentType"]').val('checkbox');
        }
    } else {
        jQuery('[name="emailPaymentType"]').closest('.form-group').slideUp();
        jQuery('[name="emailPaymentType"]').val('checkbox');

    }

}
function lfb_formStripeChange() {
    if (jQuery('#lfb_formFields [name="use_stripe"]').is(':checked')) {
        jQuery('#lfb_formFields [name="use_paypal"]').parent().bootstrapSwitch('setState', false);
        jQuery('.lfb_stripeField:not([name="stripe_percentToPay"])').slideDown();
        jQuery('#lfb_formFields [name="totalIsRange"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_formFields [name="save_to_cart"]').parent().bootstrapSwitch('setState', false);
        if (jQuery('#lfb_formFields select[name="gravityFormID"]').val() > 0) {
            jQuery('#lfb_formFields select[name="gravityFormID"]').val('0');
        }

        jQuery('#lfb_formFields .lfb_wooOption').slideUp();
        lfb_changeStripePayMode();
    } else {

        jQuery('.lfb_stripeField').slideUp();
        if (!jQuery('#lfb_formFields [name="use_paypal"]').is(':checked')) {
            jQuery('#lfb_formFields .lfb_wooOption').slideDown();
        }
    }
    lfb_formIsSubscriptionChange();
    lfb_updatePaymentType();
}
function lfb_chartsTypeChange() {
    if (jQuery('#lfb_chartsTypeSelect').val() == 'month') {
        jQuery('#lfb_panelCharts #lfb_chartsMonth').slideDown();
        jQuery('#lfb_panelCharts #lfb_chartsYear').slideUp();
    } else if (jQuery('#lfb_chartsTypeSelect').val() == 'year') {
        jQuery('#lfb_panelCharts #lfb_chartsMonth').slideUp();
        jQuery('#lfb_panelCharts #lfb_chartsYear').slideDown();
    } else {
        jQuery('#lfb_panelCharts #lfb_chartsMonth').slideUp();
        jQuery('#lfb_panelCharts #lfb_chartsYear').slideUp();
    }
    if (jQuery('#lfb_panelCharts').css('display') == 'block') {
        lfb_loadCharts(jQuery('#lfb_panelCharts').attr('data-formid'));
    }
}
function lfb_chartsYearChange() {
    lfb_loadCharts(jQuery('#lfb_panelCharts').attr('data-formid'));
}
function lfb_chartsMonthChange() {
    lfb_loadCharts(jQuery('#lfb_panelCharts').attr('data-formid'));
}
function lfb_showShortcodeWin(formID) {
    if (!formID) {
        formID = lfb_currentFormID;
    }
    jQuery('#lfb_shortcode_1').val('[estimation_form form_id="' + formID + '"]');
    jQuery('#lfb_shortcode_2').val('[estimation_form form_id="' + formID + '" fullscreen="true"]');
    jQuery('#lfb_shortcode_3').val('[estimation_form form_id="' + formID + '" popup="true"]');
    jQuery('#lfb_shortcode_4').val('<a href="#" class="open-estimation-form form-' + formID + '">Open Form</a>');
    jQuery('#lfb_winShortcode').find('span[data-displayid]').html(formID);
    jQuery('#lfb_winShortcode').modal('show');
}
function lfb_formGravityChange() {
    if (jQuery('#lfb_formFields select[name="gravityFormID"]').val() > 0) {
        jQuery('#lfb_formFields .nav-tabs > li:eq(2)').slideUp();

        jQuery('#lfb_finalStepFields').slideUp();
        jQuery('#lfb_formFields [name="use_paypal"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_formFields [name="use_stripe"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_formFields [name="isSubscription"]').parent().bootstrapSwitch('setState', false);

        jQuery('#lfb_formFields [name="close_url"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="useRedirectionConditions"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="redirectionDelay"]').closest('.form-group').slideUp();
        jQuery('#lfb_formFields [name="useCaptcha"]').closest('.form-group').slideUp();

    } else {
        jQuery('#lfb_finalStepFields').slideDown();
        jQuery('#lfb_formFields .nav-tabs > li:eq(2)').slideDown();
        jQuery('#lfb_formFields [name="close_url"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="useRedirectionConditions"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="redirectionDelay"]').closest('.form-group').slideDown();
        jQuery('#lfb_formFields [name="useCaptcha"]').closest('.form-group').slideDown();

    }
}
function lfb_formEmailUserChange() {
    if (jQuery('#lfb_formFields [name="email_toUser"]').is(':checked')) {
        jQuery('#lfb_formEmailUser').slideDown();
    } else {
        jQuery('#lfb_formEmailUser').slideUp();
        jQuery('#lfb_formFields [name="enableCustomersData"]').parent().bootstrapSwitch('setState', false);
    }
}
function lfb_formWooChange() {
    if (jQuery('#lfb_formFields [name="save_to_cart"]').is(':checked')) {
        jQuery('#lfb_formFields .lfb_paymentOption').slideUp();        
        jQuery('#lfb_formFields [name="emptyWooCart"]').closest('.form-group').slideDown();
        
        jQuery('#lfb_formFields [name="save_to_cart_edd"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_formFields [name="use_paypal"]').parent().bootstrapSwitch('setState', false);

    } else if (!jQuery('#lfb_formFields [name="save_to_cart_edd"]').is(':checked')) {
        jQuery('#lfb_formFields .lfb_paymentOption').slideDown();
    }    
    if (!jQuery('#lfb_formFields [name="save_to_cart"]').is(':checked')) {    
        jQuery('#lfb_formFields [name="emptyWooCart"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_formFields [name="emptyWooCart"]').closest('.form-group').slideUp();
    }
}

function lfb_formEDDChange() {
    if (jQuery('#lfb_formFields [name="save_to_cart_edd"]').is(':checked')) {
        jQuery('#lfb_formFields .lfb_paymentOption').slideUp();
        jQuery('#lfb_formFields [name="save_to_cart"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_formFields [name="use_paypal"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_formFields [name="isSubscription"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_formFields [name="save_to_cart"]').parent().bootstrapSwitch('setState', false);
    } else if (!jQuery('#lfb_formFields [name="save_to_cart"]').is(':checked')) {
        jQuery('#lfb_formFields .lfb_paymentOption').slideDown();
    }
}
function lfb_formIpnChange() {
    if (jQuery('#lfb_formFields [name="paypal_useIpn"]').is(':checked')) {
        jQuery('#lfb_infoIpn').slideDown();
    } else {
        jQuery('#lfb_infoIpn').slideUp();
    }
    lfb_updatePaymentType();
}
function lfb_getStepByID(stepID) {
    var rep = false;
    jQuery.each(lfb_steps, function (i) {
        if (this.id == stepID) {
            rep = this;
        }
    });
    return rep;
}
function lfb_showLoader() {
    jQuery('html,body').animate({scrollTop: 0}, 250);
    jQuery('#lfb_loader').fadeIn();
}
function lfb_addStep(step) {
    var title = '';
    var startStep = 0;
    if (!step.content) {
        title = step;
    } else {
        title = step.title;

    }

    if (step.id) {
        var newStep = jQuery('<div class="lfb_stepBloc palette palette-clouds"><div class="lfb_stepBlocWrapper"><h4>' + title + '</h4></div>' +
                '<a href="javascript:" class="lfb_btnEdit" title="' + lfb_data.texts['tip_editStep'] + '"><span class="glyphicon glyphicon-pencil"></span></a>' +
                '<a href="javascript:" class="lfb_btnSup" title="' + lfb_data.texts['tip_delStep'] + '"><span class="glyphicon glyphicon-trash"></span></a>' +
                '<a href="javascript:" class="lfb_btnDup" title="' + lfb_data.texts['tip_duplicateStep'] + '"><span class="glyphicon glyphicon-duplicate"></span></a>' +
                '<a href="javascript:" class="lfb_btnLink" title="' + lfb_data.texts['tip_linkStep'] + '"><span class="glyphicon glyphicon-link"></span></a>' +
                '<a href="javascript:" class="lfb_btnStart" title="' + lfb_data.texts['tip_flagStep'] + '"><span class="glyphicon glyphicon-flag"></span></a></div>');
        if (step.content && step.content.start == 1) {
            newStep.find('.lfb_btnStart').addClass('lfb_selected');
            newStep.addClass('lfb_selected');
        }
        if (step.elementID) {
            newStep.attr('id', step.elementID);

        } else {
            newStep.uniqueId();
        }

        newStep.children('a.lfb_btnEdit').click(function () {
            lfb_openWinStep(jQuery(this).parent().attr('data-stepid'));
        });
        newStep.children('a.lfb_btnLink').click(function () {
            lfb_startLink(jQuery(this).parent().attr('id'));
        });
        newStep.children('a.lfb_btnSup').click(function () {
            lfb_askDeleteStep(jQuery(this).parent().attr('data-stepid'));
        });
        newStep.children('a.lfb_btnDup').click(function () {
            lfb_duplicateStep(jQuery(this).parent().attr('data-stepid'));
        });
        newStep.children('a.lfb_btnStart').click(function () {
            lfb_showLoader();
            jQuery('.lfb_stepBloc[data-stepid]').find('.lfb_btnStart').removeClass('lfb_selected');
            jQuery('.lfb_stepBloc[data-stepid]').find('.lfb_btnStart').closest('.lfb_stepBloc').removeClass('lfb_selected');
            jQuery.each(lfb_steps, function () {
                var step = this;
                if (step.id != jQuery(this).parent().attr('data-stepid') && step.content.start == 1) {
                    step.content.start = 0;
                    jQuery.ajax({
                        url: ajaxurl,
                        type: 'post',
                        data: {
                            action: 'lfb_saveStep',
                            id: step.id,
                            start: 0,
                            formID: lfb_currentFormID,
                            content: JSON.stringify(step.content)
                        }
                    });
                }
            });

            jQuery(this).addClass('lfb_selected');
            jQuery(this).closest('.lfb_stepBloc').addClass('lfb_selected');
            var currentStep = lfb_getStepByID(parseInt(jQuery(this).parent().attr('data-stepid')));
            currentStep.content.start = 1;
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'lfb_saveStep',
                    id: step.id,
                    start: 1,
                    formID: lfb_currentFormID,
                    content: JSON.stringify(currentStep.content)
                },
                success: function () {
                    lfb_loadForm(lfb_currentFormID);
                }
            });
        });


        newStep.draggable({
            containment: "parent",
            handle: ".lfb_stepBlocWrapper",
            drag: function () {
                lfb_updateStepCanvas();
            }
        });
        newStep.children('.lfb_stepBlocWrapper').click(function () {
            if (lfb_isLinking) {
                lfb_stopLink(newStep);
            }
        });
        var posX = 10, posY = 10;
        if (step.content && step.content.previewPosX) {
            posX = step.content.previewPosX;
            posY = step.content.previewPosY;
        } else {
            posX = jQuery('#lfb_stepsOverflow').scrollLeft() + jQuery('#lfb_stepsOverflow').width() / 2 - 64;
            posY = jQuery('#lfb_stepsOverflow').scrollTop() + jQuery('#lfb_stepsOverflow').height() / 2 - 64;
        }
        newStep.hide();
        jQuery('#lfb_stepsContainer').append(newStep);
        newStep.css({
            left: (posX) + 'px',
            top: posY + 'px'
        });

        newStep.fadeIn();
        setTimeout(lfb_updateStepsDesign, 250);
        if (jQuery('#lfb_stepsContainer .lfb_stepBloc').length == 0) {
            startStep = 1;
        }

        newStep.attr('data-stepid', step.id);
    } else {

        var newStep = jQuery('<div></div>');
        newStep.uniqueId();
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_addStep',
                elementID: newStep.attr('id'),
                formID: lfb_currentFormID,
                start: startStep,
                previewPosX: jQuery('#lfb_stepsOverflow').scrollLeft() + jQuery('#lfb_stepsOverflow').width() / 2 - 64,
                previewPosY: jQuery('#lfb_stepsOverflow').scrollTop() + jQuery('#lfb_stepsOverflow').height() / 2 - 64
            },
            success: function (step) {
                step = jQuery.parseJSON(step);
                if (jQuery.inArray(step.id, lfb_steps) == -1) {
                    lfb_showLoader();
                    lfb_loadForm(lfb_currentFormID);
                }
            }
        });
    }
}

function lfb_removeStep(stepID) {
    var i = 0;

    jQuery('.lfb_stepBloc[data-stepid="' + stepID + '"]').remove();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_removeStep',
            stepID: stepID
        },
        success: function () {
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'lfb_loadForm',
                    formID: lfb_currentFormID
                },
                success: function (rep) {
                    rep = JSON.parse(rep);
                    lfb_currentForm = rep;
                    lfb_params = rep.params;
                    lfb_steps = rep.steps;
                    jQuery.each(rep.links, function (index) {
                        var link = this;
                        link.originID = jQuery('.lfb_stepBloc[data-stepid="' + link.originID + '"]').attr('id');
                        link.destinationID = jQuery('.lfb_stepBloc[data-stepid="' + link.destinationID + '"]').attr('id');
                        link.conditions = JSON.parse(link.conditions);
                        lfb_links[index] = link;
                    });
                }
            });
        }
    });
}
function lfb_updateStepsDesign() {
    jQuery('#wpwrap').css({
        height: jQuery('#lfb_bootstraped').height() + 48
    });
    jQuery('#lfb_stepsCanvas').attr('width', jQuery('#lfb_stepsContainer').outerWidth());
    jQuery('#lfb_stepsCanvas').attr('height', jQuery('#lfb_stepsContainer').outerHeight());
    jQuery('#lfb_stepsCanvas').css({
        width: jQuery('#lfb_stepsContainer').outerWidth(),
        height: jQuery('#lfb_stepsContainer').outerHeight()
    });
    jQuery('.lfb_stepBloc > .lfb_stepBlocWrapper > h4').each(function () {
        jQuery(this).css('margin-top', 0 - jQuery(this).height() / 2);
    });
}

function lfb_repositionLinkPoints(linkIndexes) {
    var checkedLinks = new Array();
    jQuery.each(linkIndexes, function () {
        var linkIndex = this;
        var link = lfb_links[linkIndex];
        checkedLinks.push(link);

        var originLeft = (jQuery('#' + link.originID).offset().left - jQuery('#lfb_stepsContainer').offset().left) + jQuery('#' + link.originID).width() / 2;
        var originTop = (jQuery('#' + link.originID).offset().top - jQuery('#lfb_stepsContainer').offset().top) + jQuery('#' + link.originID).height() / 2;
        var destinationLeft = (jQuery('#' + link.destinationID).offset().left - jQuery('#lfb_stepsContainer').offset().left) + jQuery('#' + link.destinationID).width() / 2;
        var destinationTop = (jQuery('#' + link.destinationID).offset().top - jQuery('#lfb_stepsContainer').offset().top) + jQuery('#' + link.destinationID).height() / 2;
        var posX = originLeft + (destinationLeft - originLeft) / 2;
        var posY = originTop + (destinationTop - originTop) / 2;

        jQuery.each(checkedLinks, function (i) {
            if (this.originID == link.destinationID && this.destinationID == link.originID && i < linkIndex) {
                posX += 15;
                posY += 15;
            }
        });

        jQuery('.lfb_linkPoint[data-linkindex="' + linkIndex + '"]').css({
            left: posX + 'px',
            top: posY + 'px'
        });
    });
}
function lfb_repositionLinkPoint(linkIndex) {
    var link = lfb_links[linkIndex];
    var originLeft = (jQuery('#' + link.originID).offset().left - jQuery('#lfb_stepsContainer').offset().left) + jQuery('#' + link.originID).width() / 2;
    var originTop = (jQuery('#' + link.originID).offset().top - jQuery('#lfb_stepsContainer').offset().top) + jQuery('#' + link.originID).height() / 2;
    var destinationLeft = (jQuery('#' + link.destinationID).offset().left - jQuery('#lfb_stepsContainer').offset().left) + jQuery('#' + link.destinationID).width() / 2;
    var destinationTop = (jQuery('#' + link.destinationID).offset().top - jQuery('#lfb_stepsContainer').offset().top) + jQuery('#' + link.destinationID).height() / 2;
    var posX = originLeft + (destinationLeft - originLeft) / 2;
    var posY = originTop + (destinationTop - originTop) / 2;

    jQuery.each(lfb_links, function (i) {
        if (this.originID == link.destinationID && this.destinationID == link.originID && i < linkIndex) {

            posX += 15;
            posY += 15;
        }
    });
    jQuery('.lfb_linkPoint[data-linkindex="' + linkIndex + '"]').css({
        left: posX + 'px',
        top: posY + 'px'
    });
}
function lfb_loadSettings() {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_loadSettings'
        },
        success: function (rep) {
            rep = jQuery.parseJSON(rep);
            lfb_settings = rep;
            jQuery('#lfb_gdprSettings').find('input,select,textarea').each(function () {
                if (jQuery(this).is('[data-switch="switch"]')) {
                    var value = false;
                    eval('if(rep.' + jQuery(this).attr('name') + ' == 1){ jQuery(this).parent().bootstrapSwitch("setState",true); } else {jQuery(this).parent().bootstrapSwitch("setState",false);}');
                } else {
                    eval('jQuery(this).val(rep.' + jQuery(this).attr('name') + ');');
                }

            });
            if (lfb_settings.encryptDB == 1) {
                jQuery('#lfb_formFields [name="encryptDB"]').parent().bootstrapSwitch("setState", true);
            } else {
                jQuery('#lfb_formFields [name="encryptDB"]').parent().bootstrapSwitch("setState", false);
            }
            if (lfb_data.designForm == 0) {
                jQuery('#lfb_loader').fadeOut();
            }
        }
    });
}

function lfb_closeSettings() {
    lfb_showLoader();
    document.location.href = document.location.href;
}

function lfb_duplicateStep(stepID) {
    if (lfb_canDuplicate) {
        lfb_showLoader();
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_duplicateStep',
                stepID: stepID
            },
            success: function (newStepID) {
                lfb_canDuplicate = true;
                lfb_loadForm(lfb_currentFormID);
            }
        });
    }
}

function lfb_updateStepCanvas() {
    if (jQuery('#lfb_stepsCanvas').length > 0
            && jQuery('.lfb_window[style="display: block;"]').length == 0
            && jQuery('#tld_tdgnPanel[style="display: block;"]').length == 0
            && jQuery('#lfb_panelLogs[style="display: block;"]').length == 0
            && jQuery('#lfb_panelCharts[style="display: block;"]').length == 0
            && jQuery('#lfb_panelLogs[style="display: block;"]').length == 0
            ) {
        lfb_linkGradientIndex++;
        if (lfb_linkGradientIndex >= 30) {
            lfb_linkGradientIndex = 1;
        }
        var ctx = jQuery('#lfb_stepsCanvas').get(0).getContext('2d');
        ctx.clearRect(0, 0, jQuery('#lfb_stepsCanvas').attr('width'), jQuery('#lfb_stepsCanvas').attr('height'));

        var linksPointsToReposition = new Array();
        jQuery.each(lfb_links, function (index) {
            var link = this;

            if (link.destinationID && jQuery('#' + link.originID).length > 0 && jQuery('#' + link.destinationID).length > 0) {

                var posX = parseInt(jQuery('#' + link.originID).css('left')) + jQuery('#' + link.originID).outerWidth() / 2 + 22;
                var posY = parseInt(jQuery('#' + link.originID).css('top')) + jQuery('#' + link.originID).outerHeight() / 2 + 22;
                var posX2 = parseInt(jQuery('#' + link.destinationID).css('left')) + jQuery('#' + link.destinationID).outerWidth() / 2 + 22;
                var posY2 = parseInt(jQuery('#' + link.destinationID).css('top')) + jQuery('#' + link.destinationID).outerHeight() / 2 + 22;

                var chkVisible = true;
                if (posY < jQuery('#lfb_stepsOverflow').scrollTop()) {
                    if (posY2 < jQuery('#lfb_stepsOverflow').scrollTop()) {
                        chkVisible = false;
                    } else {
                        if (posX < jQuery('#lfb_stepsOverflow').scrollLeft() && posX2 < jQuery('#lfb_stepsOverflow').scrollLeft()) {
                            chkVisible = false;
                        } else if (posX > jQuery('#lfb_stepsOverflow').scrollLeft() + jQuery('#lfb_stepsOverflow').width() && posX2 > jQuery('#lfb_stepsOverflow').scrollLeft() + jQuery('#lfb_stepsOverflow').width()) {
                            chkVisible = false;
                        }
                    }
                }
                if (posY > (jQuery('#lfb_stepsOverflow').scrollTop() + jQuery('#lfb_stepsOverflow').height())) {
                    if (posY2 > jQuery('#lfb_stepsOverflow').scrollTop() + jQuery('#lfb_stepsOverflow').height()) {
                        chkVisible = false;
                    } else {
                        if (posX < jQuery('#lfb_stepsOverflow').scrollLeft() && posX2 < jQuery('#lfb_stepsOverflow').scrollLeft()) {
                            chkVisible = false;
                        } else if (posX > jQuery('#lfb_stepsOverflow').scrollLeft() + jQuery('#lfb_stepsOverflow').width() && posX2 > jQuery('#lfb_stepsOverflow').scrollLeft() + jQuery('#lfb_stepsOverflow').width()) {
                            chkVisible = false;
                        }
                    }
                }
                if (posX < jQuery('#lfb_stepsOverflow').scrollLeft()) {
                    if (posX2 < jQuery('#lfb_stepsOverflow').scrollLeft()) {
                        chkVisible = false;
                    }
                }
                if (posX > (jQuery('#lfb_stepsOverflow').scrollLeft() + jQuery('#lfb_stepsOverflow').width())) {
                    if (posX2 > (jQuery('#lfb_stepsOverflow').scrollLeft() + jQuery('#lfb_stepsOverflow').width())) {
                        chkVisible = false;
                    }
                }

                if (chkVisible) {
                    var grd = ctx.createLinearGradient(posX, posY, posX2, posY2);

                    var chkBack = false;
                    var lfb_linkGradientIndexA = lfb_linkGradientIndex / 30;
                    var gradPos1 = lfb_linkGradientIndexA;
                    var gradPos2 = lfb_linkGradientIndexA + 0.1;
                    var gradPos3 = lfb_linkGradientIndexA + 0.2;
                    ctx.lineWidth = 4;
                    if (gradPos2 > 1) {
                        gradPos2 = 0;
                        gradPos3 = 0.2;
                    }
                    if (gradPos3 > 1) {
                        gradPos3 = 0;
                    }
                    grd.addColorStop(gradPos1, "#bdc3c7");
                    grd.addColorStop(gradPos2, "#1ABC9C");
                    grd.addColorStop(gradPos3, "#bdc3c7");
                    ctx.strokeStyle = grd;
                    ctx.beginPath();
                    ctx.moveTo(posX, posY);
                    ctx.lineTo(posX2, posY2);
                    ctx.stroke();

                    if (jQuery('.lfb_linkPoint[data-linkindex="' + index + '"]').length == 0) {
                        var $point = jQuery('<a href="javascript:" data-linkindex="' + index + '" class="lfb_linkPoint"><span class="glyphicon glyphicon-pencil"></span></a>');
                        jQuery('#lfb_stepsContainer').append($point);
                        $point.click(function () {
                            lfb_openWinLink(jQuery(this));
                        });
                    }
                    linksPointsToReposition.push(index);

                }
            } else {
                jQuery('.lfb_linkPoint[data-linkindex="' + index + '"]').remove();
            }
        });
        lfb_repositionLinkPoints(linksPointsToReposition);
        if (lfb_isLinking) {
            var step = jQuery('#' + lfb_links[lfb_linkCurrentIndex].originID);
            var posX = step.position().left + jQuery('#lfb_stepsOverflow').scrollLeft() + step.outerWidth() / 2;
            var posY = step.position().top + jQuery('#lfb_stepsOverflow').scrollTop() + step.outerHeight() / 2;
            ctx.strokeStyle = "#bdc3c7";
            ctx.lineWidth = 4;
            ctx.beginPath();
            ctx.moveTo(posX, posY);
            ctx.lineTo(lfb_mouseX, lfb_mouseY);
            ctx.stroke();
        }

    }
}
function lfb_removeItem(itemID) {
    // lfb_showLoader();
    jQuery('#lfb_itemsTable tr[data-itemid="' + itemID + '"]').remove();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_removeItem',
            itemID: itemID,
            stepID: lfb_currentStepID,
            formID: lfb_currentFormID
        },
        success: function () {
            //lfb_loadForm(lfb_currentFormID);
            lfb_loadFields();
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'lfb_loadForm',
                    formID: lfb_currentFormID
                },
                success: function (rep) {

                    rep = JSON.parse(rep);
                    lfb_currentForm = rep;
                    lfb_params = rep.params;
                    lfb_steps = rep.steps;
                    lfb_openWinStep(lfb_currentStepID);
                }
            });
        }
    });

}
function lfb_editItem(itemID) {

    lfb_currentItemID = itemID;
    jQuery('#lfb_winItem').find('input,textarea').val('');
    jQuery('#lfb_winItem').find('select option').removeAttr('selected');
    jQuery('#lfb_winItem').find('select option:eq(0)').attr('selected', 'selected');
    jQuery('#lfb_winItem').find('#lfb_itemPricesGrid tbody tr').not('.static').remove();
    jQuery('#lfb_winItem').find('#lfb_itemOptionsValues tbody tr').not('.static').remove();
    jQuery('#lfb_winItem').find('.has-error').removeClass('has-error');

    if (itemID > 0) {
        var itemsList = new Array();
        if (lfb_currentStepID > 0) {
            itemsList = lfb_currentStep.items;
        } else {
            itemsList = lfb_currentForm.fields;
        }

        jQuery.each(itemsList, function () {
            var item = this;
            if (item.id == itemID) {
                if (item.useRow == '') {
                    item.useRow = 0;
                }
                jQuery('#lfb_winItem').find('input,select,textarea').each(function () {
                    if (jQuery(this).is('[data-switch="switch"]')) {
                        var value = false;
                        eval('if(item.' + jQuery(this).attr('name') + ' == 1){jQuery(this).attr(\'checked\',\'checked\');} else {jQuery(this).attr(\'checked\',false);}');
                        eval('if(item.' + jQuery(this).attr('name') + ' == 1){ jQuery(this).parent().bootstrapSwitch("setState",true); } else {jQuery(this).parent().bootstrapSwitch("setState",false);}');

                    } else {
                        eval('jQuery(this).val(item.' + jQuery(this).attr('name') + ');');
                    }

                });
                jQuery('#lfb_winItem #lfb_itemRichText').code(this.richtext);
                var reducs = item.reducsQt.split('*');
                jQuery.each(reducs, function () {
                    var reduc = this.split('|');
                    if (reduc[0] && reduc[0] > 0) {
                        jQuery('#lfb_itemPricesGrid tbody').prepend('<tr><td>' + reduc[0] + '</td><td>' + parseFloat(reduc[1]).toFixed(2) + '</td><td><a href="javascript:" onclick="lfb_del_reduc(this);" class="btn btn-danger  btn-circle "><span class="glyphicon glyphicon-trash"></span></a></td></tr>');
                    }
                });
                var optionsV = item.optionsValues.split('|');
                jQuery.each(optionsV, function () {
                    var value = this;
                    var price = 0;
                    if (this.indexOf(';;') > 0) {
                        value = this.substr(0, this.indexOf(';;'));
                        price = this.substr(this.indexOf(';;') + 2, this.length);
                    }
                    if (this != "") {
                        jQuery('#lfb_itemOptionsValues #option_new_value').closest('tr').before('<tr><td>' + value + '</td><td>' + price + '</td><td><a href="javascript:" onclick="lfb_edit_option(this);" class="btn btn-default  btn-circle "><span class="glyphicon glyphicon-pencil"></span></a><a href="javascript:" onclick="lfb_del_option(this);" class="btn btn-danger  btn-circle "><span class="glyphicon glyphicon-trash"></span></a></td></tr>');
                    }
                });
                jQuery('#lfb_itemOptionsValues tbody').sortable({
                    items: "tr:not(.static)",
                    helper: function (e, tr) {
                        var $originals = tr.children();
                        var $helper = tr.clone();
                        $helper.children().each(function (index)
                        {
                            jQuery(this).width($originals.eq(index).width());
                        });
                        return $helper;
                    }
                });

                var color = item.color;
                if (color == '') {
                    color = '#FFFFFF;'
                }
                jQuery('#lfb_winItem').find('[name="color"]').prev('.lfb_colorPreview').css({
                    backgroundColor: color
                });

                jQuery('#lfb_winItem').find('[name="eddProductID"]').val(item.eddProductID);
                jQuery('#lfb_winItem').find('[name="wooProductID"]').val(item.wooProductID);
                if (item.wooProductID > 0 && item.wooVariation > 0) {
                    jQuery('#lfb_winItem').find('[name="wooProductID"]').find('option[value="' + item.wooProductID + '"]').each(function () {
                        if (jQuery(this).attr('data-woovariation') == item.wooVariation) {
                            jQuery(this).attr('selected', 'selected');
                        }
                    });
                }
                if (item.eddProductID > 0 && item.eddVariation > 0) {
                    jQuery('#lfb_winItem').find('[name="eddProductID"]').find('option[value="' + item.eddProductID + '"]').each(function () {
                        if (jQuery(this).attr('data-eddvariation') == item.eddVariation) {
                            jQuery(this).attr('selected', 'selected');
                        }
                    });
                }
                jQuery('#lfb_imageLayersTable tbody').html('');
                var layers = new Array();
                jQuery.each(lfb_currentForm.layers, function () {
                    if (this.itemID == item.id) {
                        layers.push(this);
                    }
                });
                if (layers.length > 0) {
                    lfb_showLayersTable(layers);
                }
                jQuery('#lfb_winItem').find('[name="endDaterangeID"]').find('option[value!="0"]').remove();
                jQuery.each(lfb_steps, function () {
                    var step = this;
                    jQuery.each(step.items, function () {
                        var item = this;
                        if (item.type == 'datepicker' && item.id != itemID) {
                            jQuery('#lfb_winItem').find('[name="endDaterangeID"]').append('<option value="' + item.id + '">' + step.title + ' : " ' + item.title + ' "</option>');
                        }
                    });
                });
                jQuery.each(lfb_currentForm.fields, function () {
                    var item = this;
                    if (item.type == 'datepicker' && item.id != itemID) {
                        jQuery('#lfb_winItem').find('[name="endDaterangeID"]').append('<option value="' + item.id + '">' + lfb_data.texts['lastStep'] + ' : " ' + item.title + ' "</option>');
                    }

                });

            }
        });
    } else {
        jQuery('#lfb_imageLayersTable tbody').html('');
        jQuery('#lfb_winItem').find('input[name="operation"]').val('+');
        jQuery('#lfb_winItem').find('input[name="ordersort"]').val(0);
        jQuery('#lfb_winItem').find('input[name="quantity_max"]').val(5);
        jQuery('#lfb_winItem').find('[name="reduc_enabled"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem').find('[name="quantity_enabled"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem').find('[name="ischecked"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem').find('[name="isHidden"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem').find('[name="isRequired"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem').find('[name="dontAddToTotal"]').parent().bootstrapSwitch('setState', false);

        jQuery('#lfb_winItem').find('select[name="type"]').val('picture');
        jQuery('#lfb_winItem').find('[name="showInSummary"]').parent().bootstrapSwitch('setState', true);
        jQuery('#lfb_winItem').find('[name="useCalculation"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem').find('[name="useValueAsQt"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem').find('[name="hideQtSummary"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem').find('[name="useCalculationQt"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem').find('[name="showPrice"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem').find('[name="useShowConditions"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem').find('[name="allowedFiles"]').val('.png,.jpg,.jpeg,.gif,.zip,.rar');
        jQuery('#lfb_winItem').find('[name="maxFiles"]').val('4');
        jQuery('#lfb_winItem').find('[name="useRow"]').val('0');
        jQuery('#lfb_winItem').find('[name="color"]').val(jQuery('#lfb_tabDesign [name="colorA"]').val());
        jQuery('#lfb_winItem').find('[name="color"]').prev('.lfb_colorPreview').css({
            backgroundColor: jQuery('#lfb_tabDesign [name="colorA"]').val()
        });
        jQuery('#lfb_winItem').find('[name="eventDurationType"]').val('hours');
        jQuery('#lfb_winItem').find('[name="eventDuration"]').val('1');
        jQuery('#lfb_winItem').find('[name="eventTitle"]').val(lfb_data.texts['newEvent']);

        jQuery('#lfb_imageLayersTableContainer').slideUp();

    }
    jQuery('#lfb_winItem').find('input[type="checkbox"]').each(function () {
        if (jQuery(this).is('[data-switch="switch"]')) {
            if (jQuery(this).closest('.form-group').find('small').length > 0) {
                jQuery(this).closest('.has-switch').b_tooltip({
                    title: jQuery(this).closest('.form-group').find('small').html()
                });
            }
        }
    });
    if (jQuery('#lfb_formFields [name="gmap_key"]').val().length < 3) {
        jQuery('#lfb_winItem #lfb_addDistanceBtn').attr('disabled', 'disabled');
        jQuery('#lfb_winItem [name="useDistanceAsQt"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem [name="useDistanceAsQt"]').closest('.switch.has-switch').addClass('deactivate');
    } else {
        jQuery('#lfb_winItem #lfb_addDistanceBtn').removeAttr('disabled');
        jQuery('#lfb_winItem [name="useDistanceAsQt"]').closest('.switch.has-switch').removeClass('deactivate');
    }
    jQuery('#lfb_winItem').find('[name="quantity_enabled"]').on('change', lfb_changeQuantityEnabled);
    lfb_changeQuantityEnabled();
    jQuery('#lfb_winItem').find('[name="reduc_enabled"]').on('change', lfb_changeReducEnabled);
    lfb_changeReducEnabled();
    jQuery('#lfb_winItem').find('[name="quantityUpdated"]').change(lfb_changeQuantity);
    lfb_changeQuantity();
    jQuery('#lfb_winItem').find('[name="wooProductID"]').change(lfb_changeWoo);
    lfb_changeWoo();
    jQuery('#lfb_winItem').find('[name="eddProductID"]').change(lfb_changeEDD);
    lfb_changeEDD();
    jQuery('#lfb_winItem').find('[name="fieldType"]').change(lfb_changeFieldType);
    jQuery('#lfb_winItem').find('[name="type"]').change(lfb_changeFieldType);
    lfb_changeFieldType();
    jQuery('#lfb_winItem').find('[name="autocomplete"]').change(lfb_changeAutocomplete);
    lfb_changeAutocomplete();
    jQuery('#lfb_winItem').find('[name="operation"]').change(lpf_changeOperation);
    lpf_changeOperation();
    jQuery('#lfb_winItem').find('[name="useCalculation"]').change(lfb_changeUseCalculation);
    lfb_changeUseCalculation();
    jQuery('#lfb_winItem').find('[name="useCalculationQt"]').change(lfb_changeUseCalculationQt);
    lfb_changeUseCalculationQt();
    jQuery('#lfb_winItem').find('[name="validation"]').change(lfb_changeValidation);
    lfb_changeValidation();
    jQuery('#lfb_winItem').find('[name="type"]').change(lfb_changeItemType);
    lfb_changeItemType();
    jQuery('#lfb_winItem').find('[name="useShowConditions"]').change(lfb_changeUseShowConditions);
    lfb_changeUseShowConditions();
    jQuery('#lfb_winItem').find('[name="showInSummary"]').on('change', lfb_showSummaryItemChange);
    lfb_showSummaryItemChange();
    jQuery('#lfb_winItem').find('[name="useDistanceAsQt"]').on('change', lfb_formDistanceAsQtChange);
    lfb_formDistanceAsQtChange();
    jQuery('#lfb_winItem').find('[name="isRequired"]').on('change', lfb_changeItemIsRequired);
    lfb_changeItemIsRequired();
    jQuery('#lfb_winItem').find('[name="useValueAsQt"]').on('change', lfb_changeUseValueAsQt);
    lfb_changeUseValueAsQt();
    jQuery('#lfb_winItem').find('[name="calendarID"]').on('change', lfb_changeItemCalendarID);
    lfb_changeItemCalendarID();
    jQuery('#lfb_winItem').find('[name="dateType"]').on('change', changeItemDateType);
    changeItemDateType();
    jQuery('#lfb_winItem').find('[name="registerEvent"]').on('change', lfb_changeRegisterEvent);
    lfb_changeRegisterEvent();
    jQuery('#lfb_winItem').find('[name="useAsDateRange"]').on('change', lfb_changeUseAsDateRange);
    lfb_changeUseAsDateRange();
    
    jQuery('#lfb_winItem').find('[name="usePaypalIfChecked"]').on('change', lfb_changeUsePaypalIfChecked);
    lfb_changeUsePaypalIfChecked();
    jQuery('#lfb_winItem').find('[name="dontUsePaypalIfChecked"]').on('change', lfb_changeDontUsePaypalIfChecked);
    lfb_changeDontUsePaypalIfChecked();

    jQuery('#lfb_winItem').find('input.lfb_iconField').trigger('change');
    jQuery('#lfb_winItem').fadeIn();
    jQuery('html,body').scrollTop(0);
    setTimeout(function () {
        jQuery('html,body').scrollTop(0);
    }, 400);


}
function lfb_changeUsePaypalIfChecked(){
    if (jQuery("#lfb_winItem").find('[name="usePaypalIfChecked"]').is(":checked")) {
       jQuery('#lfb_winItem').find('[name="dontUsePaypalIfChecked"]').parent().bootstrapSwitch('setState', false);        
    }
}
function lfb_changeDontUsePaypalIfChecked(){
    
    if (jQuery("#lfb_winItem").find('[name="dontUsePaypalIfChecked"]').is(":checked")) {
       jQuery('#lfb_winItem').find('[name="usePaypalIfChecked"]').parent().bootstrapSwitch('setState', false);      
    }    
}
function lfb_showSummaryItemChange() {
    if (jQuery("#lfb_winItem").find('[name="showInSummary"]').is(":checked")) {
        jQuery("#lfb_winItem").find('[name="hideQtSummary"]').closest(".form-group").slideDown();
    } else {
        jQuery("#lfb_winItem").find('[name="hideQtSummary"]').closest(".form-group").slideUp();
    }
}
var lfb_isWoo = false;
function lfb_changeWoo() {
    if (jQuery('#lfb_winItem').find('[name="wooProductID"]').val() != '0') {
        jQuery('#lfb_winItem').find('[name="eddProductID"]').val(0);
        if (!lfb_isWoo) {
            jQuery('#lfb_winItem').find('[name="quantity_enabled"]').prop('checked', true);
            jQuery('.quantity_max_tr').show();
        }
        lfb_isWoo = true;
        jQuery('.wooMasked').fadeOut(250);
        if (jQuery('#lfb_winItem').find('[name="title"]').val() == "") {
            jQuery('#lfb_winItem').find('[name="title"]').val(jQuery('#lfb_winItem').find('[name="wooProductID"] option:selected').data('title'));
        }
        if (jQuery('#lfb_winItem').find('[name="wooProductID"] option:selected').data('max')) {
            jQuery('#lfb_winItem').find('[name="quantity_max"]').val(jQuery('#lfb_winItem').find('[name="wooProductID"] option:selected').data('max'));
        }
        if (jQuery('#lfb_winItem').find('[name="wooProductID"] option:selected').data('img') && jQuery('#lfb_winItem').find('[name="image"]').val() == '') {
            jQuery('#lfb_winItem').find('[name="image"]').val(jQuery('#lfb_winItem').find('[name="wooProductID"] option:selected').data('img'));
        }

        jQuery('#lfb_winItem').find('[name="useCalculation"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem').find('[name="useCalculation"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="price"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="operation"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="dontAddToTotal"]').closest('.form-group').slideUp();

        if (jQuery('#lfb_winItem').find('[name="wooProductID"] option:selected').is('[data-type="subscription"]') || jQuery('#lfb_winItem').find('[name="wooProductID"] option:selected').is('[data-type="variable-subscription"]')) {
            jQuery('#lfb_winItem').find('[name="isSinglePrice"]').parent().bootstrapSwitch('setState', false);
        } else {
            jQuery('#lfb_winItem').find('[name="isSinglePrice"]').parent().bootstrapSwitch('setState', true);
        }
    } else {
        lfb_isWoo = false;
        if (parseInt(jQuery('#lfb_winItem').find('[name="eddProductID"]').val()) == 0) {
            jQuery('#lfb_winItem').find('[name="operation"]').closest('.form-group').slideDown();
            // jQuery('.wooMasked').fadeIn(250);
            lfb_changeUseCalculation();
        }
    }
}
function lfb_changeAutocomplete() {
    if (jQuery('#lfb_winItem').find('[name="autocomplete"]').is(':checked')) {
        jQuery('#lfb_winItem').find('[name="autocomplete"]').closest('.form-group').find('.alert').slideDown();
    } else {
        jQuery('#lfb_winItem').find('[name="autocomplete"]').closest('.form-group').find('.alert').slideUp();
    }
}
function lfb_changeFieldType() {
    if (jQuery('#lfb_formFields [name="gmap_key"]').val().length > 3 && jQuery('#lfb_winItem').find('[name="type"]').val() == "textfield"
            && (jQuery('#lfb_winItem').find('[name="fieldType"]').val() == 'address' || jQuery('#lfb_winItem').find('[name="fieldType"]').val() == 'city'
                    || jQuery('#lfb_winItem').find('[name="fieldType"]').val() == 'country' || jQuery('#lfb_winItem').find('[name="fieldType"]').val() == 'zip')) {
        jQuery('#lfb_winItem').find('[name="autocomplete"]').closest('.form-group').slideDown();
    } else {
        jQuery('#lfb_winItem').find('[name="autocomplete"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem').find('[name="autocomplete"]').closest('.form-group').slideUp();
    }
}
function lfb_changeEDD() {
    if (parseInt(jQuery('#lfb_winItem').find('[name="eddProductID"]').val()) > 0) {
        jQuery('#lfb_winItem').find('[name="wooProductID"]').val(0);
        if (jQuery('#lfb_winItem').find('[name="title"]').val() == "") {
            jQuery('#lfb_winItem').find('[name="title"]').val(jQuery('#lfb_winItem').find('[name="eddProductID"] option:selected').data('title'));
        }
        if (jQuery('#lfb_winItem').find('[name="eddProductID"] option:selected').data('img') && jQuery('#lfb_winItem').find('[name="image"]').val() == '') {
            jQuery('#lfb_winItem').find('[name="image"]').val(jQuery('#lfb_winItem').find('[name="eddProductID"] option:selected').data('img'));
        }

        jQuery('#lfb_winItem').find('[name="useCalculation"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem').find('[name="useCalculation"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="price"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="operation"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="dontAddToTotal"]').closest('.form-group').slideUp();

    } else {
        if (parseInt(jQuery('#lfb_winItem').find('[name="wooProductID"]').val()) == 0) {
            jQuery('#lfb_winItem').find('[name="operation"]').closest('.form-group').slideDown();
            //  jQuery('.wooMasked').fadeIn(250);
            lfb_changeUseCalculation();
        }
    }
}
function lpf_changeOperation() {
    if (jQuery('#lfb_winItem').find('[name="operation"]').val() == 'x' || jQuery('#lfb_winItem').find('[name="operation"]').val() == '/') {
        jQuery('#lfb_winItem').find('[name="price"]').parent().find('label:eq(1)').slideDown();
        jQuery('#lfb_winItem').find('[name="price"]').parent().find('label:eq(0)').slideUp();
    } else {
        jQuery('#lfb_winItem').find('[name="price"]').parent().find('label:eq(1)').slideUp();
        jQuery('#lfb_winItem').find('[name="price"]').parent().find('label:eq(0)').slideDown();
    }
    if (jQuery('#lfb_winItem').find('[name="operation"]').val() != '+') {
        jQuery('#lfb_winItem').find('[name="reduc_enabled"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="reduc_enabled"]').prop('checked', false);
        jQuery('#lfb_itemPricesGrid').slideUp();
        jQuery('#lfb_winItem').find('#lfb_itemPricesGrid tbody tr').not('.static').remove();
    } else if (jQuery('#lfb_winItem').find('[name="quantity_enabled"]').is(':checked')) {
        jQuery('#lfb_winItem').find('[name="reduc_enabled"]').closest('.form-group').slideDown();
    }
}

function lfb_changeUseShowConditions() {
    if (jQuery("#lfb_winItem").find('[name="useShowConditions"]').is(":checked")) {
        jQuery("#lfb_winItem").find('[name="showConditions"]').closest(".form-group").slideDown();
    } else {
        jQuery("#lfb_winItem").find('[name="showConditions"]').closest(".form-group").slideUp();
    }
}
function lfb_changeUseCalculation() {
    if ((jQuery('#lfb_winItem').find('[name="type"]').val() == 'numberfield' && jQuery("#lfb_winItem").find('[name="useValueAsQt"]').is(":checked")) || jQuery('#lfb_winItem').find('[name="type"]').val() == 'picture' || jQuery('#lfb_winItem').find('[name="type"]').val() == 'checkbox' || jQuery('#lfb_winItem').find('[name="type"]').val() == 'slider' || jQuery('#lfb_winItem').find('[name="type"]').val() == 'button') {
        if (jQuery('#lfb_winItem').find('[name="wooProductID"]').val() == '0' && parseInt(jQuery('#lfb_winItem').find('[name="eddProductID"]').val()) == 0) {
            jQuery('#lfb_winItem').find('[name="useCalculation"]').closest('.form-group').slideDown();
            if (jQuery('#lfb_winItem').find('[name="useCalculation"]').is(':checked')) {
                if (jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').css('display') != 'block') {
                    jQuery('#lfb_winItem').find('[name="price"]').closest('.form-group').slideUp();
                    jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').slideDown();

                }
            } else {
                if (jQuery('#lfb_winItem').find('[name="wooProductID"]').val() == '0') {
                    jQuery('#lfb_winItem').find('[name="price"]').closest('.form-group').slideDown();
                }
                if (parseInt(jQuery('#lfb_winItem').find('[name="eddProductID"]').val()) == 0) {
                    jQuery('#lfb_winItem').find('[name="price"]').closest('.form-group').slideDown();
                }
                jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').slideUp();
            }
        }
    }
}
function lfb_changeUseCalculationQt() {
    if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'picture' || jQuery('#lfb_winItem').find('[name="type"]').val() == 'checkbox' || jQuery('#lfb_winItem').find('[name="type"]').val() == 'slider' || jQuery('#lfb_winItem').find('[name="type"]').val() == 'button' || jQuery('#lfb_winItem').find('[name="type"]').val() == 'numberfield') {

        if (jQuery('#lfb_winItem').find('[name="useCalculationQt"]').is(':checked')) {
            jQuery('#lfb_winItem').find('[name="calculationQt"]').closest('.form-group').slideDown();
        } else {
            jQuery('#lfb_winItem').find('[name="calculationQt"]').closest('.form-group').slideUp();
        }
    }
}

function lfb_changeUseValueAsQt() {
    if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'numberfield') {
        if (jQuery("#lfb_winItem").find('[name="useValueAsQt"]').is(":checked")) {
            jQuery('#lfb_winItem').find('[name="quantity_enabled"]').parent().bootstrapSwitch('setState', true);
            jQuery("#lfb_winItem").find('[name="useCalculation"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="dontAddToTotal"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="operation"]').closest('.form-group').slideDown();
            if (jQuery('#lfb_formFields [name="isSubscription"]').is(':checked')) {
                jQuery('#lfb_winItem').find('[name="isSinglePrice"]').closest('.form-group').slideDown();
            }
            jQuery('#lfb_winItem').find('[name="reduc_enabled"]').closest('.form-group').slideDown();
            jQuery("#lfb_winItem").find('[name="useCalculationQt"]').closest('.form-group').slideDown();
            jQuery("#lfb_winItem").find('[name="useDistanceAsQt"]').closest('.form-group').slideDown();

        } else {
            jQuery('#lfb_winItem').find('[name="price"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').slideUp();
            jQuery("#lfb_winItem").find('[name="useCalculationQt"]').parent().bootstrapSwitch('setState', false);
            jQuery("#lfb_winItem").find('[name="useCalculationQt"]').closest('.form-group').slideUp();
            jQuery("#lfb_winItem").find('[name="useCalculation"]').parent().bootstrapSwitch('setState', false);
            jQuery("#lfb_winItem").find('[name="useCalculation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="dontAddToTotal"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="operation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="isSinglePrice"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="isSinglePrice"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="reduc_enabled"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="reduc_enabled"]').prop('checked', false);
            jQuery("#lfb_winItem").find('[name="useDistanceAsQt"]').closest('.form-group').slideUp();
        }
        lfb_changeUseCalculation();
    }

}

function lfb_changeItemCalendarID() {
    if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'datepicker' && jQuery('#lfb_winItem').find('[name="calendarID"]').val() > 0) {
        jQuery('#lfb_winItem').find('[name="registerEvent"]').closest('.form-group').slideDown();
        jQuery('#lfb_winItem').find('[name="calendarID"]').animate({width: 234}, 200);
        setTimeout(function () {
            jQuery('#lfb_winItem').find('[name="calendarID"]').closest('.form-group').find('a.btn-circle').fadeIn(200);
        }, 250);
    } else {
        jQuery('#lfb_winItem [name="registerEvent"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem').find('[name="registerEvent"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="calendarID"]').closest('.form-group').find('a.btn-circle').fadeOut(200);
        setTimeout(function () {
            jQuery('#lfb_winItem').find('[name="calendarID"]').animate({width: 280}, 200);
        }, 250);
    }
}
function changeItemDateType() {
    if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'datepicker' && jQuery('#lfb_winItem').find('[name="dateType"]').val() != 'time') {
        jQuery('#lfb_winItem').find('[name="calendarID"]').closest('.form-group').slideDown();
    } else {
        jQuery('#lfb_winItem').find('[name="calendarID"]').val('0');
        jQuery('#lfb_winItem').find('[name="calendarID"]').closest('.form-group').slideUp();
    }
    if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'datepicker' && jQuery('#lfb_winItem').find('[name="dateType"]').val() != 'date') {
        jQuery('#lfb_winItem').find('[name="disableMinutes"]').closest('.form-group').slideDown();
    } else {
        jQuery('#lfb_winItem [name="disableMinutes"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem').find('[name="disableMinutes"]').closest('.form-group').slideUp();
    }
    lfb_changeItemCalendarID();
}
function lfb_updateItemCalCategories() {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_getCalendarCategories',
            calendarID: jQuery('#lfb_winItem').find('[name="calendarID"]').val()
        },
        success: function (rep) {
            rep = jQuery.parseJSON(rep.trim());
            jQuery('#lfb_winItem [name="eventCategory"]').html('');
            jQuery.each(rep, function () {
                jQuery('#lfb_winItem [name="eventCategory"]').append('<option value="' + this.id + '">' + this.title + '</option>');
            });
        }
    });
}
function lfb_changeRegisterEvent() {
    if (jQuery('#lfb_winItem').find('[name="registerEvent"]').is(':checked')) {
        lfb_updateItemCalCategories();
        jQuery('#lfb_winItem').find('[name="eventCategory"]').closest('.form-group').slideDown();
        jQuery('#lfb_winItem').find('[name="eventBusy"]').closest('.form-group').slideDown();
        jQuery('#lfb_winItem').find('[name="eventTitle"]').closest('.form-group').slideDown();
        jQuery('#lfb_winItem').find('[name="useAsDateRange"]').closest('.form-group').slideDown();

    } else {
        jQuery('#lfb_winItem').find('[name="eventCategory"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="eventBusy"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="eventDuration"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="eventTitle"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="useAsDateRange"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem').find('[name="useAsDateRange"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="endDaterangeID"]').val('');
        if (jQuery('#lfb_winItem').find('[name="endDaterangeID"] option').length > 0) {
            jQuery('#lfb_winItem').find('[name="endDaterangeID"]').val(jQuery('#lfb_winItem').find('[name="endDaterangeID"] option').first().attr('value'));
        }
        jQuery('#lfb_winItem').find('[name="endDaterangeID"]').closest('.form-group').slideUp();
    }
    lfb_changeUseAsDateRange();
}
function lfb_changeUseAsDateRange() {
    if (jQuery('#lfb_winItem').find('[name="registerEvent"]').is(':checked')) {
        if (jQuery('#lfb_winItem').find('[name="useAsDateRange"]').is(':checked')) {
            jQuery('#lfb_winItem').find('[name="endDaterangeID"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="eventDuration"]').closest('.form-group').slideUp();

        } else {
            jQuery('#lfb_winItem').find('[name="endDaterangeID"]').val('');
            if (jQuery('#lfb_winItem').find('[name="endDaterangeID"] option').length > 0) {
                jQuery('#lfb_winItem').find('[name="endDaterangeID"]').val(jQuery('#lfb_winItem').find('[name="endDaterangeID"] option').first().attr('value'));
            }
            jQuery('#lfb_winItem').find('[name="endDaterangeID"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="eventDuration"]').closest('.form-group').slideDown();

        }
    } else {
        jQuery('#lfb_winItem').find('[name="endDaterangeID"]').closest('.form-group').slideUp();
    }
}


function lfb_changeItemIsRequired() {
    if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'select' && jQuery('#lfb_winItem [name="isRequired"]').is(':checked')) {
        jQuery('#lfb_winItem').find('[name="firstValueDisabled"]').closest('.form-group').slideDown();
    } else {
        jQuery('#lfb_winItem [name="firstValueDisabled"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem').find('[name="firstValueDisabled"]').closest('.form-group').slideUp();
    }
}
function lfb_changeItemType() {

    if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'datepicker') {
        jQuery('#lfb_winItem').find('[name="calendarID"]').closest('.form-group').slideDown();
    } else {
        jQuery('#lfb_winItem').find('[name="calendarID"]').val(0);
        jQuery('#lfb_winItem').find('[name="calendarID"]').closest('.form-group').slideUp();
    }
    if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'picture' || jQuery('#lfb_winItem').find('[name="type"]').val() == 'qtfield' || jQuery('#lfb_winItem').find('[name="type"]').val() == 'button') {

        jQuery('#lfb_winItem').find('[name="maxWidth"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="maxHeight"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="useValueAsQt"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="sliderStep"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="dateType"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem [name="disableMinutes"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_winItem').find('[name="disableMinutes"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('#lfb_calEventRemindersTableItem').closest(".form-group").slideUp();
        jQuery('#lfb_itemPricesGrid').slideUp();
        jQuery('#lfb_imageLayersTableContainer').slideUp();
        if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'button') {

            jQuery('#lfb_winItem').find('[name="useCalculationQt"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="icon"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="iconPosition"]').closest('.form-group').slideDown();
            //jQuery('#lfb_winItem').find('[name="showPrice"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="showPrice"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem .picOnly').slideUp();
            jQuery('#lfb_winItem').find('[name="quantity_enabled"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="quantity_enabled"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="quantity_max"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="quantity_min"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="color"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="callNextStep"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="usePaypalIfChecked"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="dontUsePaypalIfChecked"]').closest('.form-group').slideDown();

        } else {
            jQuery('#lfb_winItem').find('[name="color"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="callNextStep"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="icon"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="iconPosition"]').closest('.form-group').slideUp();
            jQuery('.picOnly').slideDown();
            jQuery('#lfb_winItem').find('[name="showPrice"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="quantity_enabled"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="quantity_max"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="quantity_min"]').closest('.form-group').slideDown();
           // jQuery('#lfb_winItem').find('[name="usePaypalIfChecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="usePaypalIfChecked"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="dontUsePaypalIfChecked"]').closest('.form-group').slideDown();
        }
        jQuery('#lfb_itemRichTextContainer').slideUp();
        jQuery('#lfb_winItem').find('.lfb_textOnly').slideUp();
        jQuery('#lfb_winItem').find('[name="useRow"]').closest('.form-group').slideDown();
        jQuery('#lfb_winItem').find('[name="urlTarget"]').closest('.form-group').slideDown();
        jQuery('#lfb_winItem').find('[name="urlTargetMode"]').closest('.form-group').slideDown();
        jQuery('#lfb_winItem').find('[name="showInSummary"]').closest('.form-group').slideDown();

        jQuery('#lfb_winItem').find('[name="isHidden"]').closest('.form-group').slideDown();
        jQuery('#lfb_winItem').find('[name="description"]').closest('.form-group').slideDown();
        jQuery('#lfb_winItem').find('[name="maxFiles"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="allowedFiles"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="minSize"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="maxSize"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="fileSize"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="defaultValue"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="minTime"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="shortcode"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="maxTime"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('.lfb_onlyDatefield').slideUp();

        jQuery('#lfb_winItem').find('[name="validation"]').val('');
        jQuery('#lfb_winItem').find('[name="validation"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="placeholder"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="placeholder"]').parent().bootstrapSwitch('setState', false);


        if (!jQuery('#lfb_winItem').find('[name="useCalculation"]').is(':checked')) {
            jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').slideUp();
            if (jQuery('#lfb_winItem').find('[name="wooProductID"]').val() == '0' && parseInt(jQuery('#lfb_winItem').find('[name="eddProductID"]').val()) == 0) {
                jQuery('#lfb_winItem').find('[name="price"]').closest('.form-group').slideDown();
            }
        } else {
            jQuery('#lfb_winItem').find('[name="price"]').closest('.form-group').hide();
            jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').show();
        }
        if (jQuery('#lfb_winItem').find('[name="wooProductID"]').val() == '0' && parseInt(jQuery('#lfb_winItem').find('[name="eddProductID"]').val()) == 0) {
            jQuery('#lfb_winItem').find('[name="useCalculation"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="operation"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="dontAddToTotal"]').closest('.form-group').slideDown();
        } else {

            jQuery('#lfb_winItem').find('[name="dontAddToTotal"]').closest('.form-group').slideUp();
        }
        jQuery('#lfb_winItem').find('#lfb_itemOptionsValuesPanel').slideUp();
        jQuery('#lfb_winItem').find('[name="groupitems"]').closest('.form-group').slideDown();
        jQuery('#lfb_winItem').find('[name="quantity_max"]').closest('.form-group').slideDown();
        jQuery('#lfb_winItem').find('[name="reduc_enabled"]').closest('.form-group').slideDown();
        if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'qtfield') {
            jQuery('#lfb_winItem [name="useDistanceAsQt"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="isRequired"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="isSelected"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="groupitems"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="imageTint"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="quantity_enabled"]').prop('checked', true);
            jQuery('#lfb_winItem').find('[name="quantity_enabled"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="image"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="ischecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="urlTarget"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="urlTargetMode"]').closest('.form-group').slideUp();

        } else {
            jQuery('#lfb_winItem').find('[name="isRequired"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="ischecked"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('#lfb_itemOptionsValuesPanel').slideUp();
            jQuery('#lfb_winItem').find('[name="wooProductID"]').closest('.form-group').slideDown();
            if (!jQuery('#lfb_formFields [name="save_to_cart"]').is(':checked')) {
                jQuery('#lfb_winItem').find('[name="eddProductID"]').closest('.form-group').slideDown();
            } else {
                jQuery('#lfb_winItem').find('[name="eddProductID"]').closest('.form-group').slideUp();
            }

            jQuery('#lfb_winItem').find('[name="urlTarget"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="urlTargetMode"]').closest('.form-group').slideDown();

            lfb_changeReducEnabled();
            // lfb_changeUseCalculation();
        }
    } else {
        jQuery('#lfb_winItem').find('[name="color"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="callNextStep"]').closest('.form-group').slideUp();
        // jQuery('#lfb_winItem').find('[name="urlTarget"]').closest('.form-group').slideUp();
        //  jQuery('#lfb_winItem').find('[name="urlTargetMode"]').closest('.form-group').slideUp();


        if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'layeredImage') {
            jQuery('.picOnly:not(.lfb_imageField)').slideUp();
        } else {
            jQuery('.picOnly').slideUp();
        }
        jQuery('#lfb_winItem').find('[name="quantity_max"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="quantity_min"]').closest('.form-group').slideUp();
        if (jQuery('#lfb_winItem').find('[name="type"]').val() != 'slider' && jQuery('#lfb_winItem').find('[name="type"]').val() != 'numberfield') {
            jQuery('#lfb_winItem [name="quantity_enabled"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_itemPricesGrid').slideUp();
            jQuery('#lfb_winItem [name="reduc_enabled"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="reduc_enabled"]').closest('.form-group').slideUp();
        }
        if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'textfield') {
            jQuery('#lfb_winItem').find('.lfb_textOnly').slideDown();
        } else {
            jQuery('#lfb_winItem').find('.lfb_textOnly').slideUp();
        }
        if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'textfield' || jQuery('#lfb_winItem').find('[name="type"]').val() == 'numberfield' || jQuery('#lfb_winItem').find('[name="type"]').val() == 'datepicker'
                || jQuery('#lfb_winItem').find('[name="type"]').val() == 'textarea' || jQuery('#lfb_winItem').find('[name="type"]').val() == 'timepicker') {


            if (jQuery('#lfb_winItem').find('[name="type"]').val() != 'numberfield' || !jQuery('#lfb_winItem').find('[name="useValueAsQt"]').is(':checked')) {
                jQuery('#lfb_winItem [name="useDistanceAsQt"]').parent().bootstrapSwitch('setState', false);
                jQuery('#lfb_winItem').find('[name="useCalculationQt"]').parent().bootstrapSwitch('setState', false);
            }

            jQuery('#lfb_winItem').find('[name="showInSummary"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="shortcode"]').closest('.form-group').slideUp();
            if (jQuery('#lfb_winItem').find('[name="type"]').val() != 'textarea') {
                jQuery('#lfb_winItem').find('[name="icon"]').closest('.form-group').slideDown();
                jQuery('#lfb_winItem').find('[name="iconPosition"]').closest('.form-group').slideDown();
            }
            jQuery('#lfb_winItem [name="showPrice"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="showPrice"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('#lfb_itemOptionsValuesPanel').slideUp();
            jQuery('#lfb_winItem').find('[name="groupitems"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="wooProductID"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="eddProductID"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="isHidden"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="useRow"]').closest('.form-group').slideDown();
            if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'numberfield') {
                jQuery('#lfb_winItem').find('[name="minSize"]').closest('.form-group').slideDown();
                jQuery('#lfb_winItem').find('[name="maxSize"]').closest('.form-group').slideDown();
                jQuery('#lfb_winItem').find('[name="useValueAsQt"]').closest('.form-group').slideDown();
                if (jQuery("#lfb_winItem").find('[name="useValueAsQt"]').is(":checked")) {
                } else {
                    jQuery('#lfb_winItem').find('[name="price"]').closest('.form-group').slideUp();
                    jQuery('#lfb_winItem').find('[name="dontAddToTotal"]').closest('.form-group').slideUp();
                    jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').slideUp();
                    jQuery('#lfb_winItem').find('[name="useCalculation"]').parent().bootstrapSwitch('setState', false);
                    jQuery('#lfb_winItem').find('[name="useCalculation"]').closest('.form-group').slideUp();
                    jQuery('#lfb_winItem').find('[name="isSinglePrice"]').parent().bootstrapSwitch('setState', false);
                    jQuery('#lfb_winItem').find('[name="isSinglePrice"]').closest('.form-group').slideUp();
                    jQuery('#lfb_winItem').find('[name="operation"]').closest('.form-group').slideUp();
                }
            } else {
                jQuery('#lfb_winItem').find('[name="price"]').closest('.form-group').slideUp();
                jQuery('#lfb_winItem').find('[name="minSize"]').closest('.form-group').slideUp();
                jQuery('#lfb_winItem').find('[name="maxSize"]').closest('.form-group').slideUp();
                jQuery('#lfb_winItem').find('[name="useValueAsQt"]').closest('.form-group').slideUp();
                jQuery('#lfb_winItem').find('[name="dontAddToTotal"]').closest('.form-group').slideUp();
                jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').slideUp();
                jQuery('#lfb_winItem').find('[name="useCalculation"]').parent().bootstrapSwitch('setState', false);
                jQuery('#lfb_winItem').find('[name="useCalculation"]').closest('.form-group').slideUp();
                jQuery('#lfb_winItem').find('[name="isSinglePrice"]').parent().bootstrapSwitch('setState', false);
                jQuery('#lfb_winItem').find('[name="isSinglePrice"]').closest('.form-group').slideUp();
                jQuery('#lfb_winItem').find('[name="operation"]').closest('.form-group').slideUp();
            }
            jQuery('#lfb_winItem').find('[name="fileSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="placeholder"]').closest('.form-group').slideDown();

            if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'textfield') {
                jQuery('#lfb_winItem').find('[name="validation"]').closest('.form-group').slideDown();
            } else {
                jQuery('#lfb_winItem').find('[name="validation"]').val('');
                jQuery('#lfb_winItem').find('[name="validation"]').closest('.form-group').slideUp();
                jQuery('#lfb_winItem').find('[name="validationCaracts"]').closest('.form-group').slideUp();
                jQuery('#lfb_winItem').find('[name="validationMin"]').closest('.form-group').slideUp();
                jQuery('#lfb_winItem').find('[name="validationMax"]').closest('.form-group').slideUp();
            }
            if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'textarea') {
                jQuery('#lfb_winItem').find('[name="icon"]').closest('.form-group').slideUp();
                jQuery('#lfb_winItem').find('[name="iconPosition"]').closest('.form-group').slideUp();
            }
            if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'timepicker') {
                jQuery('#lfb_winItem').find('[name="minTime"]').closest('.form-group').slideDown();
                jQuery('#lfb_winItem').find('[name="maxTime"]').closest('.form-group').slideDown();
            } else {
                jQuery('#lfb_winItem').find('[name="minTime"]').closest('.form-group').slideUp();
                jQuery('#lfb_winItem').find('[name="maxTime"]').closest('.form-group').slideUp();
            }
            if (jQuery('#lfb_winItem').find('[name="type"]').val() != 'datepicker') {
                jQuery('#lfb_winItem').find('[name="dateType"]').closest('.form-group').slideUp();
                jQuery('#lfb_winItem [name="disableMinutes"]').parent().bootstrapSwitch('setState', false);
                jQuery('#lfb_winItem').find('[name="disableMinutes"]').closest('.form-group').slideUp();
                jQuery('#lfb_winItem').find('#lfb_calEventRemindersTableItem').closest(".form-group").slideUp();
                if (jQuery('#lfb_winItem').find('[name="type"]').val() != 'timepicker') {
                    jQuery('#lfb_winItem').find('[name="defaultValue"]').closest('.form-group').slideDown();
                } else {
                    jQuery('#lfb_winItem').find('[name="defaultValue"]').closest('.form-group').slideUp();
                }
                jQuery('#lfb_winItem').find('.lfb_onlyDatefield').slideUp();
            } else {

                jQuery('#lfb_winItem').find('[name="dateType"]').closest('.form-group').slideDown();
                if (jQuery('#lfb_winItem').find('[name="calendarID"]').val() != '' && jQuery('#lfb_winItem').find('[name="calendarID"]').val() > 0) {
                    jQuery('#lfb_winItem').find('#lfb_calEventRemindersTableItem').closest(".form-group").slideDown();
                } else {
                    jQuery('#lfb_winItem').find('#lfb_calEventRemindersTableItem').closest(".form-group").slideUp();
                }
                jQuery('#lfb_winItem').find('[name="defaultValue"]').closest('.form-group').slideUp();
                jQuery('#lfb_winItem').find('.lfb_onlyDatefield').slideDown();
            }
            jQuery('#lfb_winItem').find('[name="maxWidth"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxHeight"]').closest('.form-group').slideUp();
            jQuery('#lfb_imageLayersTableContainer').slideUp();
            jQuery('#lfb_winItem').find('[name="wooProductID"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="eddProductID"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="ischecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="ischecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="quantity_enabled"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('input[name="showPrice"]').val(0);
            jQuery('#lfb_itemRichTextContainer').slideUp();
            jQuery('#lfb_winItem').find('[name="isRequired"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="maxFiles"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="allowedFiles"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="description"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="usePaypalIfChecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="dontUsePaypalIfChecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="sliderStep"]').closest('.form-group').slideUp();
        } else if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'slider') {
            jQuery('#lfb_imageLayersTableContainer').slideUp();
            jQuery('#lfb_winItem').find('[name="dateType"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem [name="disableMinutes"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="disableMinutes"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('#lfb_calEventRemindersTableItem').closest(".form-group").slideUp();
            jQuery('#lfb_winItem').find('[name="useValueAsQt"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxWidth"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxHeight"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="icon"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="iconPosition"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="minTime"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxTime"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="showInSummary"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="shortcode"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="isHidden"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="reduc_enabled"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="validation"]').val('');
            jQuery('#lfb_winItem').find('[name="validation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="placeholder"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="placeholder"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('.lfb_onlyDatefield').slideUp();
            jQuery('#lfb_winItem').find('[name="wooProductID"]').parent().slideDown();
            if (!jQuery('#lfb_formFields [name="save_to_cart"]').is(':checked')) {
                jQuery('#lfb_winItem').find('[name="eddProductID"]').closest('.form-group').slideDown();
            } else {
                jQuery('#lfb_winItem').find('[name="eddProductID"]').closest('.form-group').slideUp();
            }
            jQuery('#lfb_winItem').find('[name="sliderStep"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="defaultValue"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="fileSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="useRow"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="quantity_enabled"]').parent().bootstrapSwitch('setState', true);
            jQuery('#lfb_winItem').find('[name="quantity_enabled"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem [name="useDistanceAsQt"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="useDistanceAsQt"]').closest('.form-group').slideUp();

            if (!jQuery('#lfb_winItem').find('[name="useCalculation"]').is(':checked')) {
                if (jQuery('#lfb_winItem').find('[name="wooProductID"]').val() == '0' && parseInt(jQuery('#lfb_winItem').find('[name="eddProductID"]').val()) == 0) {
                    jQuery('#lfb_winItem').find('[name="price"]').closest('.form-group').slideDown();
                }
                jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').slideUp();
            } else {
                jQuery('#lfb_winItem').find('[name="price"]').closest('.form-group').hide();
                jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').show();
            }
            if (jQuery('#lfb_winItem').find('[name="wooProductID"]').val() == '0' && parseInt(jQuery('#lfb_winItem').find('[name="eddProductID"]').val()) == 0) {
                jQuery('#lfb_winItem').find('[name="useCalculation"]').closest('.form-group').slideDown();
                jQuery('#lfb_winItem').find('[name="operation"]').closest('.form-group').slideDown();
                jQuery('#lfb_winItem').find('[name="dontAddToTotal"]').closest('.form-group').slideDown();
            } else {
                jQuery('#lfb_winItem').find('[name="dontAddToTotal"]').closest('.form-group').slideUp();
            }
            jQuery('#lfb_winItem').find('[name="minSize"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="maxSize"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="showPrice"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('#lfb_itemOptionsValuesPanel').slideUp();
            jQuery('#lfb_winItem').find('[name="groupitems"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="ischecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="ischecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="quantity_enabled"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('input[name="showPrice"]').val(0);
            jQuery('#lfb_itemRichTextContainer').slideUp();
            jQuery('#lfb_winItem').find('[name="isRequired"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxFiles"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="allowedFiles"]').closest('.form-group').slideUp();
            if (jQuery('#lfb_formFields [name="isSubscription"]').is(':checked')) {
                jQuery('#lfb_winItem').find('[name="isSinglePrice"]').closest('.form-group').slideDown();
            } else {
                jQuery('#lfb_winItem').find('[name="isSinglePrice"]').parent().bootstrapSwitch('setState', false);
                jQuery('#lfb_winItem').find('[name="isSinglePrice"]').closest('.form-group').slideUp();
            }
            jQuery('#lfb_winItem').find('[name="description"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="usePaypalIfChecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="dontUsePaypalIfChecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="urlTarget"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="urlTargetMode"]').closest('.form-group').slideUp();

        } else if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'select') {
            jQuery('#lfb_imageLayersTableContainer').slideUp();
            jQuery('#lfb_winItem').find('[name="useCalculationQt"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="dateType"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem [name="disableMinutes"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="disableMinutes"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('#lfb_calEventRemindersTableItem').closest(".form-group").slideUp();
            jQuery('#lfb_winItem').find('[name="useValueAsQt"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxWidth"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxHeight"]').closest('.form-group').slideUp();
            if (jQuery('#lfb_formFields [name="disableDropdowns"]').is(':checked')) {
                jQuery('#lfb_winItem').find('[name="icon"]').closest('.form-group').slideDown();
                jQuery('#lfb_winItem').find('[name="iconPosition"]').closest('.form-group').slideDown();
            } else {
                jQuery('#lfb_winItem').find('[name="icon"]').closest('.form-group').slideUp();
                jQuery('#lfb_winItem').find('[name="iconPosition"]').closest('.form-group').slideUp();
            }
            jQuery('#lfb_winItem').find('[name="minTime"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxTime"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="shortcode"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="showInSummary"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('.lfb_onlyDatefield').slideUp();
            jQuery('#lfb_winItem').find('[name="isHidden"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('input[name="showPrice"]').val(0);
            jQuery('#lfb_winItem').find('[name="operation"]').parent().slideDown();
            jQuery('#lfb_winItem').find('[name="sliderStep"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="validation"]').val('');
            jQuery('#lfb_winItem').find('[name="validation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="placeholder"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="placeholder"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="fileSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="defaultValue"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="price"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="dontAddToTotal"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="useCalculation"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="useCalculation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem [name="showPrice"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="showPrice"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="useRow"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="minSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="groupitems"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="wooProductID"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="eddProductID"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="ischecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="ischecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="quantity_enabled"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('#lfb_itemOptionsValuesPanel').slideDown();
            jQuery('#lfb_winItem').find('[name="urlTarget"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="urlTargetMode"]').closest('.form-group').slideUp();
            jQuery('#lfb_itemRichTextContainer').slideUp();
            jQuery('#lfb_winItem').find('[name="isRequired"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="maxFiles"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="allowedFiles"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('.lfb_textOnly').slideUp();
            if (jQuery('#lfb_formFields [name="isSubscription"]').is(':checked')) {
                jQuery('#lfb_winItem').find('[name="isSinglePrice"]').closest('.form-group').slideDown();
            } else {
                jQuery('#lfb_winItem').find('[name="isSinglePrice"]').parent().bootstrapSwitch('setState', false);
                jQuery('#lfb_winItem').find('[name="isSinglePrice"]').closest('.form-group').slideUp();
            }
            jQuery('#lfb_winItem').find('[name="description"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="usePaypalIfChecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="dontUsePaypalIfChecked"]').closest('.form-group').slideUp();

        } else if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'filefield') {
            jQuery('#lfb_imageLayersTableContainer').slideUp();
            jQuery('#lfb_winItem').find('[name="useCalculationQt"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="dateType"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem [name="disableMinutes"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="disableMinutes"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('#lfb_calEventRemindersTableItem').closest(".form-group").slideUp();
            jQuery('#lfb_winItem').find('[name="useValueAsQt"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxWidth"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxHeight"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="icon"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="iconPosition"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="minTime"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxTime"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('.lfb_onlyDatefield').slideUp();
            jQuery('#lfb_winItem').find('[name="isHidden"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="operation"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="sliderStep"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="showInSummary"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="price"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="dontAddToTotal"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="defaultValue"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="validation"]').val('');
            jQuery('#lfb_winItem').find('[name="validation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="placeholder"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="placeholder"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="fileSize"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="minSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="useCalculation"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="useCalculation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('#lfb_itemOptionsValuesPanel').slideUp();
            jQuery('#lfb_winItem [name="showPrice"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="useRow"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="showPrice"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="ischecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="groupitems"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="wooProductID"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="eddProductID"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="urlTarget"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="urlTargetMode"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="ischecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="quantity_enabled"]').closest('.form-group').slideUp();
            jQuery('#lfb_itemRichTextContainer').slideUp();
            jQuery('#lfb_winItem').find('[name="isRequired"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="maxFiles"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="allowedFiles"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="isSinglePrice"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="isSinglePrice"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="description"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('.lfb_textOnly').slideUp();
            jQuery('#lfb_winItem').find('[name="usePaypalIfChecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="dontUsePaypalIfChecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="shortcode"]').closest('.form-group').slideUp();
        } else if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'richtext') {
            jQuery('#lfb_imageLayersTableContainer').slideUp();
            jQuery('#lfb_winItem').find('[name="useCalculationQt"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="dateType"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem [name="disableMinutes"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="disableMinutes"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('#lfb_calEventRemindersTableItem').closest(".form-group").slideUp();
            jQuery('#lfb_winItem').find('[name="useValueAsQt"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxWidth"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxHeight"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="icon"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="iconPosition"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="minTime"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxTime"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="shortcode"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('.lfb_onlyDatefield').slideUp();
            jQuery('#lfb_winItem').find('[name="isHidden"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="operation"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="sliderStep"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="price"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="validation"]').val('');
            jQuery('#lfb_winItem').find('[name="validation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="placeholder"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="placeholder"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="dontAddToTotal"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="fileSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="defaultValue"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="minSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="useCalculation"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="useCalculation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('#lfb_itemOptionsValuesPanel').slideUp();
            jQuery('#lfb_winItem [name="showPrice"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="showPrice"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="ischecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="groupitems"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="wooProductID"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="eddProductID"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="ischecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="quantity_enabled"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="isRequired"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="showInSummary"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="urlTarget"]').val('');
            jQuery('#lfb_winItem').find('[name="urlTarget"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="urlTargetMode"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="useRow"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="description"]').closest('.form-group').slideUp();
            jQuery('#lfb_itemRichTextContainer').slideDown();
            jQuery('#lfb_winItem').find('[name="isRequired"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxFiles"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="allowedFiles"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="isSinglePrice"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="isSinglePrice"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('.lfb_textOnly').slideUp();
            jQuery('#lfb_winItem').find('[name="usePaypalIfChecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="dontUsePaypalIfChecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="hideQtSummary"]').closest('.form-group').slideUp();

        } else if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'shortcode') {
            jQuery('#lfb_imageLayersTableContainer').slideUp();
            jQuery('#lfb_winItem').find('[name="useCalculationQt"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="dateType"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem [name="disableMinutes"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="disableMinutes"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('#lfb_calEventRemindersTableItem').closest(".form-group").slideUp();
            jQuery('#lfb_winItem').find('[name="useValueAsQt"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxWidth"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxHeight"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="icon"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="iconPosition"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="minTime"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxTime"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('.lfb_onlyDatefield').slideUp();
            jQuery('#lfb_winItem').find('[name="isHidden"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="operation"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="sliderStep"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="validation"]').val('');
            jQuery('#lfb_winItem').find('[name="validation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="placeholder"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="placeholder"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="price"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="dontAddToTotal"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="fileSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="defaultValue"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="minSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="useCalculation"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="useCalculation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('#lfb_itemOptionsValuesPanel').slideUp();
            jQuery('#lfb_winItem [name="showPrice"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="showPrice"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="ischecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="groupitems"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="wooProductID"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="eddProductID"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="ischecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="quantity_enabled"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="isRequired"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="showInSummary"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="urlTarget"]').val('');
            jQuery('#lfb_winItem').find('[name="urlTarget"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="urlTargetMode"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="useRow"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="description"]').closest('.form-group').slideUp();
            jQuery('#lfb_itemRichTextContainer').slideUp();
            jQuery('#lfb_winItem').find('[name="isRequired"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxFiles"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="allowedFiles"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="isSinglePrice"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="isSinglePrice"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('.lfb_textOnly').slideUp();
            jQuery('#lfb_winItem').find('[name="usePaypalIfChecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="dontUsePaypalIfChecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="shortcode"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="hideQtSummary"]').closest('.form-group').slideUp();

        } else if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'separator') {
            jQuery('#lfb_imageLayersTableContainer').slideUp();
            jQuery('#lfb_winItem').find('[name="useCalculationQt"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="dateType"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem [name="disableMinutes"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="disableMinutes"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('#lfb_calEventRemindersTableItem').closest(".form-group").slideUp();
            jQuery('#lfb_winItem').find('[name="useValueAsQt"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxWidth"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxHeight"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="icon"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="iconPosition"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="minTime"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxTime"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('.lfb_onlyDatefield').slideUp();
            jQuery('#lfb_winItem').find('[name="operation"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="sliderStep"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="price"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="dontAddToTotal"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="fileSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="validation"]').val('');
            jQuery('#lfb_winItem').find('[name="validation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="placeholder"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="placeholder"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="wooProductID"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="eddProductID"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="defaultValue"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="minSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="useCalculation"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="useCalculation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('#lfb_itemOptionsValuesPanel').slideUp();
            jQuery('#lfb_winItem [name="showPrice"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="showPrice"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="ischecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="groupitems"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="ischecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="quantity_enabled"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="isRequired"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="showInSummary"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="urlTarget"]').val('');
            jQuery('#lfb_winItem').find('[name="urlTarget"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="urlTargetMode"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="useRow"]').val('row');
            jQuery('#lfb_winItem').find('[name="useRow"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="description"]').closest('.form-group').slideUp();
            jQuery('#lfb_itemRichTextContainer').slideUp();
            jQuery('#lfb_winItem').find('[name="isRequired"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxFiles"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="allowedFiles"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="isSinglePrice"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="isSinglePrice"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('.lfb_textOnly').slideUp();
            jQuery('#lfb_winItem').find('[name="usePaypalIfChecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="dontUsePaypalIfChecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="shortcode"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="isHidden"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="hideQtSummary"]').closest('.form-group').slideUp();

        } else if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'colorpicker') {
            jQuery('#lfb_imageLayersTableContainer').slideUp();
            jQuery('#lfb_winItem').find('[name="useCalculationQt"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="dateType"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem [name="disableMinutes"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="disableMinutes"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('#lfb_calEventRemindersTableItem').closest(".form-group").slideUp();
            jQuery('#lfb_winItem').find('[name="useValueAsQt"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxWidth"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxHeight"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="icon"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="iconPosition"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="minTime"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxTime"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="shortcode"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="hideQtSummary"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="operation"]').parent().slideUp();
            jQuery('#lfb_winItem').find('.lfb_onlyDatefield').slideUp();
            jQuery('#lfb_winItem').find('[name="price"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="validation"]').val('');
            jQuery('#lfb_winItem').find('[name="validation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="placeholder"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="placeholder"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="dontAddToTotal"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="showInSummary"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="wooProductID"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="eddProductID"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="sliderStep"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="defaultValue"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="useCalculation"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="useCalculation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('#lfb_itemOptionsValuesPanel').slideUp();
            jQuery('#lfb_winItem').find('[name="fileSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="minSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="isHidden"]').closest('.form-group').slideDown();

            jQuery('#lfb_winItem [name="showPrice"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="showPrice"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="groupitems"]').parent().slideUp();
            jQuery('#lfb_winItem').find('#lfb_itemOptionsValuesPanel').slideUp();
            jQuery('#lfb_winItem').find('[name="wooProductID"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="eddProductID"]').parent().slideUp();

            jQuery('#lfb_winItem').find('[name="useRow"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="ischecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="quantity_enabled"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="quantity_max"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="reduc_enabled"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="urlTarget"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="urlTargetMode"]').closest('.form-group').slideUp();
            jQuery('#lfb_itemRichTextContainer').slideUp();
            jQuery('#lfb_winItem').find('.lfb_textOnly').slideUp();
            jQuery('#lfb_winItem').find('[name="isRequired"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="description"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="maxFiles"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="allowedFiles"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="isSinglePrice"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="isSinglePrice"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="usePaypalIfChecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="dontUsePaypalIfChecked"]').closest('.form-group').slideUp();
        } else if (jQuery('#lfb_winItem').find('[name="type"]').val() == 'layeredImage') {
            jQuery('#lfb_imageLayersTableContainer').slideDown();
            jQuery('#lfb_winItem').find('[name="useCalculationQt"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="dateType"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem [name="disableMinutes"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="disableMinutes"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('#lfb_calEventRemindersTableItem').closest(".form-group").slideUp();
            jQuery('#lfb_winItem').find('[name="useValueAsQt"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="image"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="icon"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="iconPosition"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="minTime"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxTime"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('.lfb_onlyDatefield').slideUp();
            jQuery('#lfb_winItem').find('[name="isHidden"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="operation"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="sliderStep"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="validation"]').val('');
            jQuery('#lfb_winItem').find('[name="validation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="placeholder"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="placeholder"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="price"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="dontAddToTotal"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="fileSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="defaultValue"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="minSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="useCalculation"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="useCalculation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('#lfb_itemOptionsValuesPanel').slideUp();
            jQuery('#lfb_winItem [name="showPrice"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="showPrice"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="ischecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="groupitems"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="wooProductID"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="eddProductID"]').parent().slideUp();
            jQuery('#lfb_winItem').find('[name="ischecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="quantity_enabled"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="isRequired"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="showInSummary"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="urlTarget"]').val('');
            jQuery('#lfb_winItem').find('[name="urlTarget"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="urlTargetMode"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="useRow"]').val('1');
            jQuery('#lfb_winItem').find('[name="useRow"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="description"]').closest('.form-group').slideUp();
            jQuery('#lfb_itemRichTextContainer').slideUp();
            jQuery('#lfb_winItem').find('[name="isRequired"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxFiles"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="allowedFiles"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="isSinglePrice"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="isSinglePrice"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('.lfb_textOnly').slideUp();
            jQuery('#lfb_winItem').find('[name="usePaypalIfChecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="dontUsePaypalIfChecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="shortcode"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="hideQtSummary"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxWidth"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="maxHeight"]').closest('.form-group').slideDown();


        } else {
            jQuery('#lfb_imageLayersTableContainer').slideUp();
            jQuery('#lfb_winItem').find('[name="useCalculationQt"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="dateType"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem [name="disableMinutes"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="disableMinutes"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('#lfb_calEventRemindersTableItem').closest(".form-group").slideUp();
            jQuery('#lfb_winItem').find('[name="useValueAsQt"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxWidth"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxHeight"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="minTime"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="icon"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="iconPosition"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxTime"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="hideQtSummary"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="shortcode"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="validation"]').val('');
            jQuery('#lfb_winItem').find('[name="validation"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="placeholder"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="placeholder"]').parent().bootstrapSwitch('setState', false);
            jQuery('#lfb_winItem').find('[name="isHidden"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('.lfb_onlyDatefield').slideUp();
            if (!jQuery('#lfb_winItem').find('[name="useCalculation"]').is(':checked')) {
                jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').slideUp();
                if (jQuery('#lfb_winItem').find('[name="wooProductID"]').val() == '0' && parseInt(jQuery('#lfb_winItem').find('[name="eddProductID"]').val()) == 0) {
                    jQuery('#lfb_winItem').find('[name="price"]').closest('.form-group').slideDown();
                }
            } else {
                jQuery('#lfb_winItem').find('[name="price"]').closest('.form-group').hide();
                jQuery('#lfb_winItem').find('[name="calculation"]').closest('.form-group').show();
            }
            if (jQuery('#lfb_winItem').find('[name="wooProductID"]').val() == '0' && parseInt(jQuery('#lfb_winItem').find('[name="eddProductID"]').val()) == 0) {
                jQuery('#lfb_winItem').find('[name="useCalculation"]').closest('.form-group').slideDown();
                jQuery('#lfb_winItem').find('[name="dontAddToTotal"]').closest('.form-group').slideDown();
                jQuery('#lfb_winItem').find('[name="operation"]').parent().slideDown();
            } else {

                jQuery('#lfb_winItem').find('[name="dontAddToTotal"]').closest('.form-group').slideUp();
            }
            jQuery('#lfb_winItem').find('[name="showPrice"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="wooProductID"]').parent().slideDown();
            if (!jQuery('#lfb_formFields [name="save_to_cart"]').is(':checked')) {
                jQuery('#lfb_winItem').find('[name="eddProductID"]').closest('.form-group').slideDown();
            } else {
                jQuery('#lfb_winItem').find('[name="eddProductID"]').closest('.form-group').slideUp();
            }
            jQuery('#lfb_winItem').find('[name="groupitems"]').parent().slideDown();
            jQuery('#lfb_winItem').find('[name="sliderStep"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="defaultValue"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="minSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="fileSize"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="showInSummary"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('#lfb_itemOptionsValuesPanel').slideUp();
            jQuery('#lfb_winItem').find('[name="ischecked"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="quantity_enabled"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="useRow"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="quantity_max"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="reduc_enabled"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="description"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="urlTarget"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="urlTargetMode"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="maxFiles"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="allowedFiles"]').closest('.form-group').slideUp();

            if (jQuery('#lfb_formFields [name="isSubscription"]').is(':checked')) {
                jQuery('#lfb_winItem').find('[name="isSinglePrice"]').closest('.form-group').slideDown();
            } else {
                jQuery('#lfb_winItem').find('[name="isSinglePrice"]').parent().bootstrapSwitch('setState', false);
                jQuery('#lfb_winItem').find('[name="isSinglePrice"]').closest('.form-group').slideUp();
            }
            if (jQuery('#lfb_formFields [name="use_paypal"]').is(':checked') || jQuery('#lfb_formFields [name="use_stripe"]').is(':checked')) {
                jQuery('#lfb_winItem').find('[name="usePaypalIfChecked"]').closest('.form-group').slideDown();
            jQuery('#lfb_winItem').find('[name="dontUsePaypalIfChecked"]').closest('.form-group').slideDown();
            } else {
                jQuery('#lfb_winItem').find('[name="usePaypalIfChecked"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="dontUsePaypalIfChecked"]').closest('.form-group').slideUp();
            }
            jQuery('#lfb_itemRichTextContainer').slideUp();
            jQuery('#lfb_winItem').find('[name="isRequired"]').closest('.form-group').slideDown();
        }
    }
    lfb_changeUseValueAsQt();
    lfb_changeItemIsRequired();
    lfb_changeValidation();
    lfb_changeUseCalculation();
    setTimeout(lfb_changeUseCalculation, 100);
}
function lfb_changeValidation() {
    if (jQuery('#lfb_winItem').find('[name="validation"]').val() == 'custom') {
        jQuery('#lfb_winItem').find('[name="validationMin"]').closest('.form-group').slideDown();
        jQuery('#lfb_winItem').find('[name="validationMax"]').closest('.form-group').slideDown();
        jQuery('#lfb_winItem').find('[name="validationCaracts"]').closest('.form-group').slideDown();

    } else {
        jQuery('#lfb_winItem').find('[name="validationMin"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="validationMax"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="validationCaracts"]').closest('.form-group').slideUp();

    }
}
function lfb_changeQuantityEnabled() {
    if (jQuery('#lfb_winItem').find('[name="quantity_enabled"]').is(':checked')) {
        jQuery('#efp_itemQuantity').slideDown();

        if ((jQuery('#lfb_winItem').find('[name="type"]').val() == 'picture' && jQuery('#lfb_formFields [name="qtType"]').val() == 2) ||
                jQuery('#lfb_winItem').find('[name="type"]').val() == 'slider') {
            jQuery('#lfb_winItem').find('[name="sliderStep"]').closest('.form-group').slideDown();
        } else {
            jQuery('#lfb_winItem').find('[name="sliderStep"]').closest('.form-group').slideUp();
            jQuery('#lfb_winItem').find('[name="sliderStep"]').val(1);
        }


    } else {
        jQuery('#lfb_winItem').find('[name="reduc_enabled"]').prop('checked', false);
        jQuery('#efp_itemQuantity').slideUp();
        jQuery('#lfb_winItem').find('[name="useDistanceAsQt"]').closest('.form-group').slideDown();
        jQuery('#lfb_winItem').find('[name="sliderStep"]').closest('.form-group').slideUp();
        jQuery('#lfb_winItem').find('[name="sliderStep"]').val(1);
    }
}
function lfb_changeReducEnabled() {
    if (jQuery('#lfb_winItem').find('[name="reduc_enabled"]').is(':checked')) {
        jQuery('#lfb_itemPricesGrid').slideDown(250);
    } else {
        jQuery('#lfb_itemPricesGrid').slideUp(250);
    }
}
function lfb_changeQuantity() {
    if (jQuery('#lfb_winItem').find('input[name="quantityUpdated"]').val() < 1) {
        jQuery('#lfb_winItem').find('input[name="quantityUpdated"]').val('3');
    }
}
function lfb_getReducs() {
    var reducsTab = new Array();
    jQuery('#lfb_itemPricesGrid tbody tr').not('.static').each(function () {
        var qt = jQuery(this).find('td:eq(0)').html();
        var price = jQuery(this).find('td:eq(1)').html();
        reducsTab.push(new Array(qt, price));
    });
    reducsTab.sort(function (a, b) {
        return a[0] - b[0];
    });
    return reducsTab;
}
function lfb_getOptions() {
    var optionsTab = new Array();
    jQuery('#lfb_itemOptionsValues tbody tr').not('.static').each(function () {
        if (jQuery(this).find('td:eq(0) input').length > 0) {
            optionsTab.push(jQuery(this).find('td:eq(0) input').val() + ';;' + jQuery(this).find('td:eq(1) input').val());
        } else {
            optionsTab.push(jQuery(this).find('td:eq(0)').html() + ';;' + jQuery(this).find('td:eq(1)').html());
        }
    });
    return optionsTab;
}
function lfb_add_option() {
    var newValue = jQuery('#lfb_itemOptionsValues #option_new_value').val();
    var newPrice = parseFloat(jQuery('#lfb_itemOptionsValues #option_new_price').val());
    if (isNaN(newPrice)) {
        newPrice = 0;
    }
    if (newValue != "") {
        jQuery('#lfb_itemOptionsValues #option_new_value').closest('tr').before('<tr><td>' + newValue + '</td><td>' + newPrice + '</td><td><a href="javascript:" onclick="lfb_edit_option(this);" class="btn btn-default  btn-circle "><span class="glyphicon glyphicon-pencil"></span></a><a href="javascript:" onclick="lfb_del_option(this);" class="btn btn-danger btn-circle "><span class="glyphicon glyphicon-trash"></span></a></td></tr>');
        jQuery('#lfb_itemOptionsValues #option_new_value').val('');
    }
    jQuery('#lfb_itemOptionsValues #option_new_price').val('');
    jQuery('#lfb_itemOptionsValues tbody').sortable({
        helper: function (e, tr) {
            var $originals = tr.children();
            var $helper = tr.clone();
            $helper.children().each(function (index)
            {
                jQuery(this).width($originals.eq(index).width());
            });
            return $helper;
        }
    });
}
function lfb_del_option(btn) {
    jQuery(btn).parent().parent().remove();
}

function lfb_add_reduc() {
    var qt = parseInt(jQuery('#reduc_new_qt').val());
    var price = parseFloat(jQuery('#reduc_new_price').val());

    if (!isNaN(qt) && qt > 0 && !isNaN(price)) {

        var reducsTab = lfb_getReducs();
        reducsTab.push(new Array(qt, price));
        reducsTab.sort(function (a, b) {
            return b[0] - a[0];
        });
        jQuery('#lfb_itemPricesGrid tbody tr').not('.static').remove();
        jQuery.each(reducsTab, function () {
            jQuery('#lfb_itemPricesGrid tbody').prepend('<tr><td>' + this[0] + '</td><td>' + parseFloat(this[1]).toFixed(2) + '</td><td><a href="javascript:" onclick="lfb_del_reduc(this);" class="btn btn-danger btn-circle "><span class="glyphicon glyphicon-trash"></span></a></td></tr>');
        });
        jQuery('#reduc_new_qt').val('');
        jQuery('#reduc_new_price').val('');
    }
}
function lfb_del_reduc(btn) {
    jQuery(btn).parent().parent().remove();
}
function lfb_saveItem() {
    var reducs = '';
    var optionsValues = '';
    var wooVariation = 0;
    var eddVariation = 0;
    var error = false;

    jQuery("body,html").animate({
        scrollTop: 0
    }, 200);
    jQuery('#lfb_winItem').find('.has-error').removeClass('has-error');

    jQuery('#lfb_winItem').find('[name="calculation"]').val(jQuery('#lfb_winItem').find('[name="calculation"]').val().replace(/"/g, "'"));
    jQuery('#lfb_winItem').find('[name="calculationQt"]').val(jQuery('#lfb_winItem').find('[name="calculationQt"]').val().replace(/"/g, "'"));

    if (jQuery('#lfb_winItem').find('input[name="title"]').val() < 1) {
        error = true;
        jQuery('#lfb_winItem').find('input[name="title"]').parent().addClass('has-error');
    }
    if (jQuery('#lfb_winItem').find('select[name="type"]').val() == 'picture' && jQuery('#lfb_winItem').find('input[name="image"]').val().length < 4) {
        error = true;
        jQuery('#lfb_winItem').find('input[name="image"]').parent().addClass('has-error');
    }
    if (jQuery('#lfb_winItem').find('select[name="type"]').val() == 'layeredImage' && jQuery('#lfb_winItem').find('input[name="image"]').val().length < 4) {
        error = true;
        jQuery('#lfb_winItem').find('input[name="image"]').parent().addClass('has-error');
    }
    if (jQuery('#lfb_winItem').find('[name="quantity_enabled"]').val() == '1' && jQuery('#lfb_winItem').find('input[name="quantity_max"]').val() == "") {
        error = true;
        jQuery('#lfb_winItem').find('input[name="quantity_max"]').parent().addClass('has-error');
    }
    if (jQuery('#lfb_winItem').find('select[name="type"]').val() == 'shortcode' && jQuery('#lfb_winItem').find('input[name="shortcode"]').val().length < 1) {
        error = true;
        jQuery('#lfb_winItem').find('input[name="shortcode"]').parent().addClass('has-error');
    }
    var optionStab = lfb_getOptions();
    jQuery.each(optionStab, function () {
        optionsValues += this + '|';
    });

    if (jQuery('#lfb_winItem').find('[name="reduc_enabled"]').is(':checked')) {
        var reducsTab = lfb_getReducs();
        jQuery.each(reducsTab, function () {
            reducs += this[0] + '|' + parseFloat(this[1]).toFixed(2) + '*';
        });
        reducs = reducs.substr(0, reducs.length - 1);
    }
    if (jQuery('#lfb_winItem').find('[name="wooProductID"] option:selected').data('woovariation') && jQuery('#lfb_winItem').find('[name="wooProductID"] option:selected').data('woovariation') > 0) {
        wooVariation = jQuery('#lfb_winItem').find('[name="wooProductID"] option:selected').data('woovariation');
    }
    if (jQuery('#lfb_winItem').find('[name="eddProductID"] option:selected').data('eddvariation') && jQuery('#lfb_winItem').find('[name="eddProductID"] option:selected').data('eddvariation') > 0) {
        eddVariation = jQuery('#lfb_winItem').find('[name="eddProductID"] option:selected').data('eddvariation');
    }


    var itemData = {};
    itemData.layers = new Array();
    if (jQuery('#lfb_winItem').find('select[name="type"]').val() == 'layeredImage') {

        jQuery('#lfb_imageLayersTable tr[data-layerid]').each(function () {
            itemData.layers.push({
                id: jQuery(this).attr('data-layerid'),
                title: jQuery(this).find('td').first().find('a').text(),
                image: jQuery(this).find('input[name="image"]').val(),
                showConditions: jQuery(this).find('textarea[name="showConditions"]').val(),
                showConditionsOperator: jQuery(this).find('input[name="showConditionsOperator"]').val()
            });
        });
    }
    jQuery('#lfb_winItem').find('input,select,textarea').each(function () {
        if (jQuery(this).closest('#lfb_itemPricesGrid').length == 0 && jQuery(this).closest('#lfb_itemOptionsValues').length == 0 &&
                jQuery(this).closest('#lfb_calculationValueBubble').length == 0 && jQuery(this).closest('#lfb_imageLayersTable').length == 0) {
            if (!jQuery(this).is('[data-switch="switch"]')) {
                eval('itemData.' + jQuery(this).attr('name') + ' = jQuery(this).val();');
            } else {
                var value = 0;
                if (jQuery(this).is(':checked')) {
                    value = 1;
                }
                eval('itemData.' + jQuery(this).attr('name') + ' = value;');
            }
        }
    });
    itemData.action = 'lfb_saveItem';
    itemData.formID = lfb_currentFormID;
    itemData.stepID = lfb_currentStepID;
    itemData.id = lfb_currentItemID;
    itemData.wooVariation = wooVariation;
    itemData.eddVariation = eddVariation;
    itemData.reducsQt = reducs;
    itemData.optionsValues = optionsValues;


    if (jQuery('#lfb_itemRichText').next('.note-editor').find('.note-toolbar .note-view [data-name="codeview"]').is('.active')) {
        jQuery('#lfb_itemRichText').next('.note-editor').find('.note-toolbar .note-view [data-name="codeview"]').trigger('click');
    }


    itemData.richtext = jQuery('#lfb_itemRichText').code();
    if (!error) {

        // lfb_showLoader();
        jQuery('#lfb_winItem').fadeOut();
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: itemData,
            success: function (itemID) {
                lfb_loadFields();
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: {
                        action: 'lfb_loadForm',
                        formID: lfb_currentFormID
                    },
                    success: function (rep) {

                        rep = JSON.parse(rep);
                        lfb_currentForm = rep;
                        lfb_params = rep.params;
                        lfb_steps = rep.steps;
                        lfb_openWinStep(lfb_currentStepID);
                    }
                });
            }
        });
    } else {
        jQuery("body,html").animate({
            scrollTop: 0
        }, 200);
    }
}

function lfb_checkLicense() {
    var error = false;
    var $field = jQuery('#lfb_winActivation input[name="purchaseCode"]');
    if ($field.val().length < 9) {
        $field.parent().addClass('has-error');
    } else {
        lfb_showLoader();
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {action: 'lfb_checkLicense', code: $field.val()},
            success: function (rep) {
                jQuery('#lfb_loader').fadeOut();
                if (rep == '1') {
                    $field.parent().addClass('has-error');
                } else {
                    lfb_lock = false;
                    lfb_data.lscV = 1;
                    jQuery('#lfb_winActivation').modal('hide');
                    jQuery('#lfb_winActivation').fadeOut();
                    jQuery('#lfb_winTldAddon').find('input[name="purchaseCode"]').val($field.val());
                }
            }
        });
    }
}

function lfb_duplicateForm(formID) {
    lfb_showLoader();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {action: 'lfb_duplicateForm', formID: formID},
        success: function (rep) {
            document.location.href = document.location.href;
        }
    });
}
function lfb_duplicateItem(itemID) {
    // lfb_showLoader();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {action: 'lfb_duplicateItem', itemID: itemID},
        success: function (rep) {
            lfb_openWinStep(lfb_currentStepID);
            lfb_reloadLayers();
        }
    });
}
function lfb_duplicateItemLastStep(itemID) {
    //  lfb_showLoader();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {action: 'lfb_duplicateItem', itemID: itemID},
        success: function (rep) {
            lfb_loadFields();
            lfb_reloadLayers();
        }
    });
}
function lfb_reloadLayers() {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {action: 'lfb_loadLayers', formID: lfb_currentFormID},
        success: function (rep) {
            rep = jQuery.parseJSON(rep);
            lfb_currentForm.layers = rep;

        }
    });
}
function lfb_startPreview() {

}
function lfb_openWinStep(stepID) {
    lfb_currentStepID = stepID;
    // lfb_showLoader();

    /* jQuery('#lfb_winStep').find('.switch [data-switch="switch"]').bootstrapSwitch('destroy');
     jQuery('#lfb_winStep').find('.switch > div > :not([data-switch="switch"])').remove();
     jQuery('#lfb_winStep').find('.switch [data-switch="switch"]').unwrap().unwrap();*/

    if (lfb_currentStepID == 0) {
        jQuery('#lfb_itemsList').hide();
    } else {
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_loadStep',
                stepID: stepID
            },
            success: function (rep) {
                rep = jQuery.parseJSON(rep);
                step = rep.step;
                lfb_currentStep = rep;

                jQuery('#lfb_stepTabGeneral').find('input,select,textarea').each(function () {
                    if (jQuery(this).is('[data-switch="switch"]')) {
                        var value = false;
                        eval('if(step.' + jQuery(this).attr('name') + ' == 1){jQuery(this).attr(\'checked\',\'checked\');} else {jQuery(this).attr(\'checked\',false);}');
                        eval('if(step.' + jQuery(this).attr('name') + ' == 1){ jQuery(this).parent().bootstrapSwitch("setState",true); } else {jQuery(this).parent().bootstrapSwitch("setState",false);}');

                        // jQuery(this).wrap('<div class="switch" data-on-label="' + lfb_data.texts['Yes'] + '" data-off-label="' + lfb_data.texts['No'] + '" />').parent().bootstrapSwitch();

                    } else {
                        eval('jQuery(this).val(step.' + jQuery(this).attr('name') + ');');
                    }
                });

                jQuery('#lfb_itemsTable tbody').html('');
                jQuery.each(rep.items, function () {
                    var item = this;
                    var $tr = jQuery('<tr data-itemid="' + item.id + '"></tr>');
                    var typeName = jQuery('#lfb_winItem').find('[name="type"] option[value="' + item.type + '"]').text();
                    $tr.append('<td><a href="javascript:"  onclick="lfb_editItem(' + item.id + ');">' + item.title + '</a></td>');
                    $tr.append('<td>' + typeName + '</td>');
                    $tr.append('<td>' + item.groupitems + '</td>');
                    $tr.append('<td><a href="javascript:"  data-toggle="tooltip" data-placement="bottom" title="' + lfb_data.texts['edit'] + '" onclick="lfb_editItem(' + item.id + ');" class="btn btn-primary btn-circle"><span class="glyphicon glyphicon-pencil"></span></a>' +
                            '<a href="javascript:"  data-toggle="tooltip" data-placement="bottom" title="' + lfb_data.texts['duplicate'] + '" onclick="lfb_duplicateItem(' + item.id + ');" class="btn btn-default btn-circle"><span class="glyphicon glyphicon-duplicate"></span></a>' +
                            '<a href="javascript:"  data-toggle="tooltip" data-placement="bottom" title="' + lfb_data.texts['remove'] + '" onclick="lfb_removeItem(' + item.id + ');" class="btn btn-danger btn-circle"><span class="glyphicon glyphicon-trash"></span></a></td>');

                    $tr.find('[data-toggle="tooltip"]').b_tooltip();
                    jQuery('#lfb_itemsTable tbody').append($tr);

                });
                jQuery('#lfb_itemsTable tbody').sortable({
                    helper: function (e, tr) {
                        var $originals = tr.children();
                        var $helper = tr.clone();
                        $helper.children().each(function (index)
                        {
                            jQuery(this).width($originals.eq(index).width());
                        });
                        return $helper;
                    },
                    scroll: true,
                    scrollSensitivity: 80,
                    scrollSpeed: 3,
                    stop: function (event, ui) {
                        var items = '';
                        jQuery('#lfb_itemsTable tbody tr[data-itemid]').each(function (i) {
                            items += jQuery(this).attr('data-itemid') + ',';
                        });
                        if (items.length > 0) {
                            items = items.substr(0, items.length - 1);
                        }
                        jQuery.ajax({
                            url: ajaxurl,
                            type: 'post',
                            data: {
                                action: 'lfb_changeItemsOrders',
                                items: items
                            }
                        });
                    }
                });
                jQuery('#lfb_itemsList').show();

                jQuery('#lfb_btns').html('');
                jQuery('#lfb_winStep').show();
                jQuery('#lfb_stepsContainer').slideUp();
                jQuery('#lfb_loader').fadeOut();
                jQuery('#lfb_winStep').find('[name="useShowConditions"]').change(lfb_changeUseShowStepConditions);
                lfb_changeUseShowStepConditions();

                jQuery('#wpwrap').css({
                    height: jQuery('#lfb_winStep').height() + 48
                });
                jQuery('#lfb_winStep').find('input[type="checkbox"]').each(function () {
                    if (jQuery(this).is('[data-switch="switch"]')) {
                        if (jQuery(this).closest('.form-group').find('small').length > 0) {
                            jQuery(this).closest('.has-switch').b_tooltip({
                                title: jQuery(this).closest('.form-group').find('small').html()
                            });
                        }
                    }
                });

            }
        });
    }

}

function lfb_changeUseShowStepConditions() {
    if (jQuery("#lfb_winStep").find('[name="useShowConditions"]').is(":checked")) {
        jQuery("#showConditionsStepBtn").slideDown();
    } else {
        jQuery("#showConditionsStepBtn").slideUp();
    }
}

function lfb_saveStep() {
    lfb_showLoader();
    jQuery("body,html").animate({
        scrollTop: 0
    }, 200);
    var stepData = {};
    jQuery('#lfb_stepTabGeneral').find('input,select,textarea').each(function () {
        if (!jQuery(this).is('[data-switch="switch"]')) {
            eval('stepData.' + jQuery(this).attr('name') + ' = jQuery(this).val();');
        } else {
            var value = 0;
            if (jQuery(this).is(':checked')) {
                value = 1;
            }
            eval('stepData.' + jQuery(this).attr('name') + ' = value;');
        }
    });
    stepData.action = 'lfb_saveStep';
    stepData.formID = lfb_currentFormID;
    stepData.id = lfb_currentStepID;
    jQuery('.lfb_stepBloc[data-stepid="' + lfb_currentStepID + '"] h4').html(stepData.title);
    lfb_updateStepsDesign();

    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: stepData,
        success: function (stepID) {
            lfb_openWinStep(stepID);
        }
    });
}
function lfb_closeItemWin() {
    lfb_closeWin(jQuery('#lfb_winItem'));
}
function lfb_closeWin(win) {
    win.fadeOut();
    if (win.is('#lfb_winStep')) {
        lfb_currentStepID = 0;
    }
    jQuery('#lfb_stepsContainer').slideDown();
    setTimeout(function () {
        lfb_updateStepsDesign();
        if (lfb_disableLinksAnim) {
            clearInterval(lfb_canvasTimer);
            lfb_updateStepCanvas();
        }
    }, 250);
}

function lfb_startLink(stepID) {
    lfb_isLinking = true;
    lfb_linkCurrentIndex = lfb_links.length;
    lfb_links.push({
        originID: stepID,
        destinationID: null
    });

}

function lfb_stopLink(newStep) {
    lfb_isLinking = false;
    var chkLink = false;
    jQuery.each(lfb_links, function () {
        if (this.originID == lfb_links[lfb_linkCurrentIndex].originID && this.destinationID == newStep.attr('id')) {
            chkLink = this;
        }
    });
    if (!chkLink) {
        //  lfb_showLoader();
        lfb_links[lfb_linkCurrentIndex].destinationID = newStep.attr('id');
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_newLink',
                formID: lfb_currentFormID,
                originStepID: jQuery('#' + lfb_links[lfb_linkCurrentIndex].originID).attr('data-stepid'),
                destinationStepID: jQuery('#' + lfb_links[lfb_linkCurrentIndex].destinationID).attr('data-stepid')
            },
            success: function (linkID) {
                lfb_links[lfb_linkCurrentIndex].id = linkID;
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: {
                        action: 'lfb_loadForm',
                        formID: lfb_currentFormID
                    },
                    success: function (rep) {
                        rep = JSON.parse(rep);
                        lfb_currentForm = rep;
                        lfb_params = rep.params;
                        lfb_steps = rep.steps;
                        jQuery.each(rep.links, function (index) {
                            var link = this;
                            link.originID = jQuery('.lfb_stepBloc[data-stepid="' + link.originID + '"]').attr('id');
                            link.destinationID = jQuery('.lfb_stepBloc[data-stepid="' + link.destinationID + '"]').attr('id');
                            link.conditions = JSON.parse(link.conditions);
                            lfb_links[index] = link;
                        });
                    }
                });
                //lfb_loadForm(lfb_currentFormID);
            }
        });
    } else {
        jQuery.grep(lfb_links, function (value) {
            return value != chkLink;
        });
    }
}

function lfb_itemsCheckRows(item) {
    var clear = jQuery(item).parent().children('.clearfix');
    clear.detach();
    jQuery(item).parent().append(clear);
}


function lfb_getUniqueTime() {
    var time = new Date().getTime();
    while (time == new Date().getTime())
        ;
    return new Date().getTime();
}

function lfb_changeInteractionBubble(action) {
    jQuery('#lfb_interactionBubble').data('type', action);
    jQuery('#lfb_interactionBubble #lfb_interactionContent > div').slideUp();
    if (action != "") {
        jQuery('#lfb_interactionBubble #lfb_interactionContent > [data-type="' + action + '"]').slideDown();
    }
    if (action == 'select') {
        var nbSel = jQuery('#lfb_interactionContent > [data-type="' + action + '"]').find('.form-group:not(.default)').length;

        if (nbSel == 0 || jQuery('#lfb_interactionContent > [data-type="' + action + '"]').find('.form-group:not(.default):last-child').find('input').val() == '') {
            lfb_interactionAddSelect(action);
        }
    }
}

function lfb_interactionAddSelect(action) {
    var nbSel = jQuery('#lfb_interactionContent > [data-type="' + action + '"]').find('.form-group').length;
    var $field = jQuery('<div class="form-group"><label>' + lfb_data.txt_option + '</label><input type="text" placeholder="' + lfb_data.txt_option + '" class="form-control" name="s_' + nbSel + '_value"></div>');
    $field.find('input').keyup(function () {
        if (jQuery(this).val() == '') {
            if (jQuery(this).closest('.form-group:not(.default)').index() > 0) {
                jQuery(this).closest('.form-group:not(.default)').remove();
            }
        } else {
            if (jQuery(this).closest('.form-group:not(.default)').next('.form-group:not(.default)').length == 0) {
                lfb_interactionAddSelect(action)
            }
        }
    });
    jQuery('#lfb_interactionContent > [data-type="' + action + '"]').append($field);
    return $field;
}

function lfb_openWinLink($item) {
    lfb_currentLinkIndex = $item.attr('data-linkindex');
    jQuery('#lfb_winLink').attr('data-linkindex', $item.attr('data-linkindex'));
    jQuery('.lfb_conditionItem').remove();
    var stepID = jQuery('#' + lfb_links[$item.attr('data-linkindex')].originID).attr('data-stepid');
    var step = lfb_getStepByID(stepID);
    var destID = jQuery('#' + lfb_links[$item.attr('data-linkindex')].destinationID).attr('data-stepid');
    var destination = lfb_getStepByID(destID);

    jQuery('#lfb_linkInteractions').show();
    jQuery('#lfb_linkOriginTitle').html(step.title);
    jQuery('#lfb_linkDestinationTitle').html(destination.title);

    jQuery.each(lfb_links[lfb_currentLinkIndex].conditions, function () {
        lfb_addLinkInteraction(this);
    });
    jQuery('#lfb_linkOperator').val(lfb_links[lfb_currentLinkIndex].operator);
    jQuery('#lfb_winLink').fadeIn(250);

    setTimeout(lfb_updateStepsDesign, 255);
    setTimeout(function () {
        jQuery('#wpwrap').css({
            height: jQuery('#lfb_bootstraped').height() + 48
        });
    }, 300);

}

function lfb_addShowLayerInteraction(data) {
    var $item = jQuery('<tr class="lfb_conditionItem"></tr>');
    var $select = jQuery('<select class="lfb_conditionSelect form-control"></select>');
    jQuery.each(lfb_steps, function () {
        var step = this;
        jQuery.each(step.items, function () {
            var item = this;
            if (item.type != 'richtext' && item.type != 'colorpicker' && item.type != 'shortcode' && item.type != 'separator' && item.type != 'layeredImage') {
                var itemID = step.id + '_' + item.id;
                $select.append('<option value="' + itemID + '" data-type="' + item.type + '" data-datetype="' + item.dateType + '">' + step.title + ' : " ' + item.title + ' "</option>');
            }
        });
    });
    var finalStepTxt = lfb_data.texts['lastStep'];
    jQuery.each(lfb_currentForm.fields, function () {
        var item = this;
        if (item.type != 'richtext' && item.type != 'colorpicker' && item.type != 'shortcode' && item.type != 'separator' && item.type != 'layeredImage') {
            var itemID = '0_' + item.id;
            $select.append('<option value="' + itemID + '" data-type="' + item.type + '" data-datetype="' + item.dateType + '">' + finalStepTxt + ' : " ' + item.title + ' "</option>');
        }
    });

    $select.append('<option value="_total" data-static="1" data-type="totalPrice" data-variable="pricefield">' + lfb_data.texts['totalPrice'] + '</option>');
    $select.append('<option value="_total_qt" data-static="1" data-type="totalQt" data-variable="numberfield">' + lfb_data.texts['totalQuantity'] + '</option>');

    var $operator = jQuery('<select class="lfb_conditionoperatorSelect form-control"></select>');
    $select.change(function () {
        var stepID = $select.val().substr(0, $select.val().indexOf('_'));
        var itemID = $select.val().substr($select.val().indexOf('_') + 1, $select.val().length);
        var item = false;
        if (stepID > 0) {
            jQuery.each(lfb_steps, function () {
                var step = this;
                if (step.id == stepID) {
                    jQuery.each(step.items, function () {
                        if (this.id == itemID) {
                            item = this;
                        }
                    });
                }
            });
        } else {
            jQuery.each(lfb_currentForm.fields, function () {
                if (this.id == itemID) {
                    item = this;
                }
            });
        }
        var operator = jQuery(this).parent().parent().find('.lfb_conditionoperatorSelect');
        operator.find('option').remove();
        if ($select.find('option:selected').is('[data-static]')) {
            var options = lfb_conditionGetOperators({
                type: $select.find('option:selected').attr('data-type')
            }, $select);
        } else {
            var options = lfb_conditionGetOperators(item, $select);
        }
        jQuery.each(options, function () {
            operator.append('<option value="' + this.value + '"  data-variable="' + this.hasVariable + '">' + this.text + '</option>');
        });
        $operator.change();
        setTimeout(function () {
            $operator.change();
        }, 300);
    });
    if (data) {
        $select.val(data.interaction);
    }
    if ($select.find('option:selected').is('[data-static]')) {
        var options = lfb_conditionGetOperators({
            type: $select.find('option:selected').attr('data-type')
        }, $select);
    } else {
        var stepID = $select.val().substr(0, $select.val().indexOf('_'));
        var itemID = $select.val().substr($select.val().indexOf('_') + 1, $select.val().length);
        var item = false;
        if (stepID > 0) {
            jQuery.each(lfb_steps, function () {
                var step = this;
                if (step.id == stepID) {
                    jQuery.each(step.items, function () {
                        if (this.id == itemID) {
                            item = this;
                        }
                    });
                }
            });
        } else {
            jQuery.each(lfb_currentForm.fields, function () {
                if (this.id == itemID) {
                    item = this;
                }
            });
        }
        var options = lfb_conditionGetOperators(item, $select);
    }
    jQuery.each(options, function () {
        $operator.append('<option value="' + this.value + '" data-variable="' + this.hasVariable + '">' + this.text + '</option>');
    });

    $operator.change(function () {
        lfb_linksUpdateFields(jQuery(this));
    });
    setTimeout(function () {
        $select.change();
    }, 250);
    var $col1 = jQuery('<td></td>');
    $col1.append($select);
    $item.append($col1);
    var $col2 = jQuery('<td></td>');
    $col2.append($operator);
    $item.append($col2);
    $item.append('<td></td><td><a href="javascript:" class="lfb_conditionDelBtn" onclick="lfb_conditionRemove(this);"><span class="glyphicon glyphicon-remove"></span></a> </td>');
    if (data) {
        $operator.val(data.action);
        $operator.change();
        if (data.value) {
            lfb_linksUpdateFields($operator, data);
        }
        setTimeout(function () {
            $operator.val(data.action);
            $operator.change();
            if (data.value) {
                $operator.closest('.lfb_conditionItem').find('.lfb_conditionValue').val(data.value);
                setTimeout(function () {
                    $operator.closest('.lfb_conditionItem').find('.lfb_conditionValue').val(data.value);
                }, 200);
                lfb_linksUpdateFields($operator, data);
            }
        }, 300);

    }
    jQuery('#lfb_showLayerConditionsTable tbody').append($item);
}

function lfb_addShowStepInteraction(data) {
    var $item = jQuery('<tr class="lfb_conditionItem"></tr>');
    var $select = jQuery('<select class="lfb_conditionSelect form-control"></select>');
    jQuery.each(lfb_steps, function () {
        var step = this;
        jQuery.each(step.items, function () {
            var item = this;
            if (item.type != 'richtext' && item.type != 'colorpicker' && item.type != 'shortcode' && item.type != 'separator' && item.type != 'layeredImage') {
                var itemID = step.id + '_' + item.id;
                $select.append('<option value="' + itemID + '" data-type="' + item.type + '" data-datetype="' + item.dateType + '">' + step.title + ' : " ' + item.title + ' "</option>');
            }
        });
    });

    $select.append('<option value="_total" data-static="1" data-type="totalPrice" data-variable="pricefield">' + lfb_data.texts['totalPrice'] + '</option>');
    $select.append('<option value="_total_qt" data-static="1" data-type="totalQt" data-variable="numberfield">' + lfb_data.texts['totalQuantity'] + '</option>');

    var $operator = jQuery('<select class="lfb_conditionoperatorSelect form-control"></select>');
    $select.change(function () {
        var stepID = $select.val().substr(0, $select.val().indexOf('_'));
        var itemID = $select.val().substr($select.val().indexOf('_') + 1, $select.val().length);
        var item = false;
        jQuery.each(lfb_steps, function () {
            var step = this;
            if (step.id == stepID) {
                jQuery.each(step.items, function () {
                    if (this.id == itemID) {
                        item = this;
                    }
                });
            }
        });
        var operator = jQuery(this).parent().parent().find('.lfb_conditionoperatorSelect');
        operator.find('option').remove();
        if ($select.find('option:selected').is('[data-static]')) {
            var options = lfb_conditionGetOperators({
                type: $select.find('option:selected').attr('data-type')
            }, $select);
        } else {
            var options = lfb_conditionGetOperators(item, $select);
        }
        jQuery.each(options, function () {
            operator.append('<option value="' + this.value + '"  data-variable="' + this.hasVariable + '">' + this.text + '</option>');
        });
        $operator.change();
        setTimeout(function () {
            $operator.change();
        }, 300);
    });
    if (data) {
        $select.val(data.interaction);
    }
    if ($select.find('option:selected').is('[data-static]')) {
        var options = lfb_conditionGetOperators({
            type: $select.find('option:selected').attr('data-type')
        }, $select);
    } else {
        var stepID = $select.val().substr(0, $select.val().indexOf('_'));
        var itemID = $select.val().substr($select.val().indexOf('_') + 1, $select.val().length);
        var item = false;
        jQuery.each(lfb_steps, function () {
            var step = this;
            if (step.id == stepID) {
                jQuery.each(step.items, function () {
                    if (this.id == itemID) {
                        item = this;
                    }
                });
            }
        });
        var options = lfb_conditionGetOperators(item, $select);
    }
    jQuery.each(options, function () {
        $operator.append('<option value="' + this.value + '" data-variable="' + this.hasVariable + '">' + this.text + '</option>');
    });

    $operator.change(function () {
        lfb_linksUpdateFields(jQuery(this));
    });
    setTimeout(function () {
        $select.change();
    }, 250);
    var $col1 = jQuery('<td></td>');
    $col1.append($select);
    $item.append($col1);
    var $col2 = jQuery('<td></td>');
    $col2.append($operator);
    $item.append($col2);
    $item.append('<td></td><td><a href="javascript:" class="lfb_conditionDelBtn" onclick="lfb_conditionRemove(this);"><span class="glyphicon glyphicon-remove"></span></a> </td>');
    if (data) {
        $operator.val(data.action);
        $operator.change();
        if (data.value) {
            lfb_linksUpdateFields($operator, data);
        }
        setTimeout(function () {
            $operator.val(data.action);
            $operator.change();
            if (data.value) {
                $operator.closest('.lfb_conditionItem').find('.lfb_conditionValue').val(data.value);
                setTimeout(function () {
                    $operator.closest('.lfb_conditionItem').find('.lfb_conditionValue').val(data.value);
                }, 200);
                lfb_linksUpdateFields($operator, data);
            }
        }, 300);

    }
    jQuery('#lfb_showStepConditionsTable tbody').append($item);
}

function lfb_addRedirInteraction(data) {
    var $item = jQuery('<tr class="lfb_conditionItem"></tr>');
    var $select = jQuery('<select class="lfb_conditionSelect form-control"></select>');
    jQuery.each(lfb_steps, function () {
        var step = this;
        jQuery.each(step.items, function () {
            var item = this;
            if (item.type != 'richtext' && item.type != 'colorpicker' && item.type != 'shortcode' && item.type != 'separator' && item.type != 'layeredImage') {
                var itemID = step.id + '_' + item.id;
                $select.append('<option value="' + itemID + '" data-type="' + item.type + '" data-datetype="' + item.dateType + '">' + step.title + ' : " ' + item.title + ' "</option>');
            }
        });
    });
    var finalStepTxt = lfb_data.texts['lastStep'];
    jQuery.each(lfb_currentForm.fields, function () {
        var item = this;
        if (item.type != 'richtext' && item.type != 'colorpicker' && item.type != 'shortcode' && item.type != 'separator' && item.type != 'layeredImage') {
            var itemID = '0_' + item.id;
            $select.append('<option value="' + itemID + '" data-type="' + item.type + '" data-datetype="' + item.dateType + '">' + finalStepTxt + ' : " ' + item.title + ' "</option>');
        }
    });

    $select.append('<option value="_total" data-static="1" data-type="totalPrice" data-variable="pricefield">' + lfb_data.texts['totalPrice'] + '</option>');
    $select.append('<option value="_total_qt" data-static="1" data-type="totalQt" data-variable="numberfield">' + lfb_data.texts['totalQuantity'] + '</option>');

    var $operator = jQuery('<select class="lfb_conditionoperatorSelect form-control"></select>');
    $select.change(function () {
        var stepID = $select.val().substr(0, $select.val().indexOf('_'));
        var itemID = $select.val().substr($select.val().indexOf('_') + 1, $select.val().length);
        var item = false;
        if (stepID > 0) {
            jQuery.each(lfb_steps, function () {
                var step = this;
                if (step.id == stepID) {
                    jQuery.each(step.items, function () {
                        if (this.id == itemID) {
                            item = this;
                        }
                    });
                }
            });
        } else {
            jQuery.each(lfb_currentForm.fields, function () {
                if (this.id == itemID) {
                    item = this;
                }
            });
        }
        var operator = jQuery(this).parent().parent().find('.lfb_conditionoperatorSelect');
        operator.find('option').remove();
        if ($select.find('option:selected').is('[data-static]')) {
            var options = lfb_conditionGetOperators({
                type: $select.find('option:selected').attr('data-type')
            }, $select);
        } else {
            var options = lfb_conditionGetOperators(item, $select);
        }
        jQuery.each(options, function () {
            operator.append('<option value="' + this.value + '"  data-variable="' + this.hasVariable + '">' + this.text + '</option>');
        });
        $operator.change();
    });
    if (data) {
        $select.val(data.interaction);
    }
    if ($select.find('option:selected').is('[data-static]')) {
        var options = lfb_conditionGetOperators({
            type: $select.find('option:selected').attr('data-type')
        }, $select);
    } else {
        var stepID = $select.val().substr(0, $select.val().indexOf('_'));
        var itemID = $select.val().substr($select.val().indexOf('_') + 1, $select.val().length);
        var item = false;
        if (stepID > 0) {
            jQuery.each(lfb_steps, function () {
                var step = this;
                if (step.id == stepID) {
                    jQuery.each(step.items, function () {
                        if (this.id == itemID) {
                            item = this;
                        }
                    });
                }
            });
        } else {
            jQuery.each(lfb_currentForm.fields, function () {
                if (this.id == itemID) {
                    item = this;
                }
            });
        }
        var options = lfb_conditionGetOperators(item, $select);
    }
    jQuery.each(options, function () {
        $operator.append('<option value="' + this.value + '" data-variable="' + this.hasVariable + '">' + this.text + '</option>');
    });

    $operator.change(function () {
        lfb_linksUpdateFields(jQuery(this));
    });
    setTimeout(function () {
        $select.change();
    }, 250);
    var $col1 = jQuery('<td></td>');
    $col1.append($select);
    $item.append($col1);
    var $col2 = jQuery('<td></td>');
    $col2.append($operator);
    $item.append($col2);
    $item.append('<td></td><td><a href="javascript:" class="lfb_conditionDelBtn" onclick="lfb_conditionRemove(this);"><span class="glyphicon glyphicon-remove"></span></a> </td>');

    if (data) {
        $operator.val(data.action);
        $operator.change();
        if (data.value) {
            $operator.closest('.lfb_conditionItem').find('.lfb_conditionValue').val(data.value);
        }
        lfb_linksUpdateFields($operator, data);

        setTimeout(function () {
            $operator.val(data.action);
            $operator.change();
            $operator.closest('.lfb_conditionItem').find('.lfb_conditionValue').val(data.value);
        }, 400);
    }
    jQuery('#lfb_redirConditionsTable tbody').append($item);
}


function lfb_addShowInteraction(data) {
    var $item = jQuery('<tr class="lfb_conditionItem"></tr>');
    var $select = jQuery('<select class="lfb_conditionSelect form-control"></select>');
    jQuery.each(lfb_steps, function () {
        var step = this;
        jQuery.each(step.items, function () {
            var item = this;
            if (item.type != 'richtext' && item.type != 'colorpicker' && item.type != 'shortcode' && item.type != 'separator' && item.type != 'layeredImage') {
                var itemID = step.id + '_' + item.id;
                $select.append('<option value="' + itemID + '" data-type="' + item.type + '" data-datetype="' + item.dateType + '">' + step.title + ' : " ' + item.title + ' "</option>');
            }
        });
    });
    var finalStepTxt = lfb_data.texts['lastStep'];
    jQuery.each(lfb_currentForm.fields, function () {
        var item = this;
        if (item.type != 'richtext' && item.type != 'colorpicker' && item.type != 'shortcode' && item.type != 'separator' && item.type != 'layeredImage') {
            var itemID = '0_' + item.id;
            $select.append('<option value="' + itemID + '" data-type="' + item.type + '" data-datetype="' + item.dateType + '">' + finalStepTxt + ' : " ' + item.title + ' "</option>');
        }
    });

    $select.append('<option value="_total" data-static="1" data-type="totalPrice" data-variable="pricefield">' + lfb_data.texts['totalPrice'] + '</option>');
    $select.append('<option value="_total_qt" data-static="1" data-type="totalQt" data-variable="numberfield">' + lfb_data.texts['totalQuantity'] + '</option>');

    var $operator = jQuery('<select class="lfb_conditionoperatorSelect form-control"></select>');
    $select.change(function () {
        var stepID = $select.val().substr(0, $select.val().indexOf('_'));
        var itemID = $select.val().substr($select.val().indexOf('_') + 1, $select.val().length);
        var item = false;
        if (stepID > 0) {
            jQuery.each(lfb_steps, function () {
                var step = this;
                if (step.id == stepID) {
                    jQuery.each(step.items, function () {
                        if (this.id == itemID) {
                            item = this;
                        }
                    });
                }
            });
        } else {
            jQuery.each(lfb_currentForm.fields, function () {
                if (this.id == itemID) {
                    item = this;
                }
            });
        }
        var operator = jQuery(this).parent().parent().find('.lfb_conditionoperatorSelect');
        operator.find('option').remove();
        if ($select.find('option:selected').is('[data-static]')) {
            var options = lfb_conditionGetOperators({
                type: $select.find('option:selected').attr('data-type')
            }, $select);
        } else {
            var options = lfb_conditionGetOperators(item, $select);
        }
        jQuery.each(options, function () {
            operator.append('<option value="' + this.value + '"  data-variable="' + this.hasVariable + '">' + this.text + '</option>');
        });
        $operator.change();
    });
    if (data) {
        $select.val(data.interaction);
    }
    if ($select.find('option:selected').is('[data-static]')) {
        var options = lfb_conditionGetOperators({
            type: $select.find('option:selected').attr('data-type')
        }, $select);
    } else {
        var stepID = $select.val().substr(0, $select.val().indexOf('_'));
        var itemID = $select.val().substr($select.val().indexOf('_') + 1, $select.val().length);
        var item = false;
        if (stepID > 0) {
            jQuery.each(lfb_steps, function () {
                var step = this;
                if (step.id == stepID) {
                    jQuery.each(step.items, function () {
                        if (this.id == itemID) {
                            item = this;
                        }
                    });
                }
            });
        } else {
            jQuery.each(lfb_currentForm.fields, function () {
                if (this.id == itemID) {
                    item = this;
                }
            });
        }
        var options = lfb_conditionGetOperators(item, $select);
    }
    jQuery.each(options, function () {
        $operator.append('<option value="' + this.value + '" data-variable="' + this.hasVariable + '">' + this.text + '</option>');
    });

    $operator.change(function () {
        lfb_linksUpdateFields(jQuery(this));
    });
    var $col1 = jQuery('<td></td>');
    $col1.append($select);
    $item.append($col1);
    var $col2 = jQuery('<td></td>');
    $col2.append($operator);
    $item.append($col2);
    $item.append('<td></td><td><a href="javascript:" class="lfb_conditionDelBtn" onclick="lfb_conditionRemove(this);"><span class="glyphicon glyphicon-remove"></span></a> </td>');
    if (data) {

        $operator.val(data.action);
        $operator.change();
        if (data.value) {
            $operator.closest('.lfb_conditionItem').find('.lfb_conditionValue').val(data.value);
        }
        lfb_linksUpdateFields($operator, data);
    }
    jQuery('#lfb_showConditionsTable tbody').append($item);
}

function lfb_addCalcInteraction(data) {
    var $item = jQuery('<tr class="lfb_conditionItem"></tr>');
    var $select = jQuery('<select class="lfb_conditionSelect form-control"></select>');
    jQuery.each(lfb_steps, function () {
        var step = this;
        jQuery.each(step.items, function () {
            var item = this;
            if (item.type != 'richtext' && item.type != 'colorpicker' && item.type != 'shortcode' && item.type != 'separator' && item.type != 'layeredImage') {
                var itemID = step.id + '_' + item.id;
                $select.append('<option value="' + itemID + '" data-type="' + item.type + '" data-datetype="' + item.dateType + '">' + step.title + ' : " ' + item.title + ' "</option>');
            }
        });
    });
    var finalStepTxt = lfb_data.texts['lastStep'];
    jQuery.each(lfb_currentForm.fields, function () {
        var item = this;
        if (item.type != 'richtext' && item.type != 'colorpicker' && item.type != 'shortcode' && item.type != 'separator' && item.type != 'layeredImage') {
            var itemID = '0_' + item.id;
            $select.append('<option value="' + itemID + '" data-type="' + item.type + '" data-datetype="' + item.dateType + '">' + finalStepTxt + ' : " ' + item.title + ' "</option>');
        }
    });



    $select.append('<option value="_total" data-static="1" data-type="totalPrice" data-variable="pricefield">' + lfb_data.texts['totalPrice'] + '</option>');
    $select.append('<option value="_total_qt" data-static="1" data-type="totalQt" data-variable="numberfield">' + lfb_data.texts['totalQuantity'] + '</option>');

    var $operator = jQuery('<select class="lfb_conditionoperatorSelect form-control"></select>');
    $select.change(function () {
        var stepID = $select.val().substr(0, $select.val().indexOf('_'));
        var itemID = $select.val().substr($select.val().indexOf('_') + 1, $select.val().length);
        var item = false;
        if (stepID > 0) {
            jQuery.each(lfb_steps, function () {
                var step = this;
                if (step.id == stepID) {
                    jQuery.each(step.items, function () {
                        if (this.id == itemID) {
                            item = this;
                        }
                    });
                }
            });
        } else {
            jQuery.each(lfb_currentForm.fields, function () {
                if (this.id == itemID) {
                    item = this;
                }
            });
        }
        var operator = jQuery(this).parent().parent().find('.lfb_conditionoperatorSelect');
        operator.find('option').remove();
        if ($select.find('option:selected').is('[data-static]')) {
            var options = lfb_conditionGetOperators({
                type: $select.find('option:selected').attr('data-type')
            }, $select);
        } else {
            var options = lfb_conditionGetOperators(item, $select);
        }
        jQuery.each(options, function () {
            operator.append('<option value="' + this.value + '"  data-variable="' + this.hasVariable + '">' + this.text + '</option>');
        });
        $operator.change();
    });
    if (data) {
        $select.val(data.interaction);
    }
    if ($select.find('option:selected').is('[data-static]')) {
        var options = lfb_conditionGetOperators({
            type: $select.find('option:selected').attr('data-type')
        }, $select);
    } else {
        var stepID = $select.val().substr(0, $select.val().indexOf('_'));
        var itemID = $select.val().substr($select.val().indexOf('_') + 1, $select.val().length);
        var item = false;
        jQuery.each(lfb_steps, function () {
            var step = this;
            if (step.id == stepID) {
                jQuery.each(step.items, function () {
                    if (this.id == itemID) {
                        item = this;
                    }
                });
            }
        });
        var options = lfb_conditionGetOperators(item, $select);
    }
    jQuery.each(options, function () {
        $operator.append('<option value="' + this.value + '" data-variable="' + this.hasVariable + '">' + this.text + '</option>');
    });

    $operator.change(function () {
        lfb_linksUpdateFields(jQuery(this));
    });
    var $col1 = jQuery('<td></td>');
    $col1.append($select);
    $item.append($col1);
    var $col2 = jQuery('<td></td>');
    $col2.append($operator);
    $item.append($col2);
    $item.append('<td></td><td><a href="javascript:" class="lfb_conditionDelBtn" onclick="lfb_conditionRemove(this);"><span class="glyphicon glyphicon-remove"></span></a> </td>');
    if (data) {
        $operator.val(data.action);
        $operator.change();
        if (data.value) {
            lfb_linksUpdateFields($operator, data);
        }
    }
    jQuery('#lfb_calcConditionsTable tbody').append($item);
}
function lfb_addLinkInteraction(data) {
    var $item = jQuery('<tr class="lfb_conditionItem"></tr>');
    var $select = jQuery('<select class="lfb_conditionSelect form-control"></select>');
    jQuery.each(lfb_steps, function () {
        var step = this;
        jQuery.each(step.items, function () {
            var item = this;
            if (item.type != 'richtext' && item.type != 'colorpicker' && item.type != 'shortcode' && item.type != 'separator' && item.type != 'layeredImage') {
                var itemID = step.id + '_' + item.id;
                $select.append('<option value="' + itemID + '" data-type="' + item.type + '" data-datetype="' + item.dateType + '">' + step.title + ' : " ' + item.title + ' "</option>');
            }
        });
    });
    $select.append('<option value="_total" data-static="1" data-type="totalPrice" data-variable="pricefield">' + lfb_data.texts['totalPrice'] + '</option>');
    $select.append('<option value="_total_qt" data-static="1" data-type="totalQt" data-variable="numberfield">' + lfb_data.texts['totalQuantity'] + '</option>');
    var $operator = jQuery('<select class="lfb_conditionoperatorSelect form-control"></select>');
    $select.change(function () {
        var stepID = $select.val().substr(0, $select.val().indexOf('_'));
        var itemID = $select.val().substr($select.val().indexOf('_') + 1, $select.val().length);
        var item = false;
        jQuery.each(lfb_steps, function () {
            var step = this;
            if (step.id == stepID) {
                jQuery.each(step.items, function () {
                    if (this.id == itemID) {
                        item = this;
                    }
                });
            }
        });
        var operator = jQuery(this).parent().parent().find('.lfb_conditionoperatorSelect');
        operator.find('option').remove();
        if ($select.find('option:selected').is('[data-static]')) {
            var options = lfb_conditionGetOperators({
                type: $select.find('option:selected').attr('data-type')
            }, $select);
        } else {
            var options = lfb_conditionGetOperators(item, $select);
        }
        jQuery.each(options, function () {
            operator.append('<option value="' + this.value + '"  data-variable="' + this.hasVariable + '">' + this.text + '</option>');
        });
        $operator.change();
    });
    if (data) {
        $select.val(data.interaction);
    }
    if ($select.find('option:selected').is('[data-static]')) {
        var options = lfb_conditionGetOperators({
            type: $select.find('option:selected').attr('data-type')
        }, $select);
    } else {
        var stepID = $select.val().substr(0, $select.val().indexOf('_'));
        var itemID = $select.val().substr($select.val().indexOf('_') + 1, $select.val().length);
        var item = false;
        jQuery.each(lfb_steps, function () {
            var step = this;
            if (step.id == stepID) {
                jQuery.each(step.items, function () {
                    if (this.id == itemID) {
                        item = this;
                    }
                });
            }
        });
        var options = lfb_conditionGetOperators(item, $select);
    }
    jQuery.each(options, function () {
        $operator.append('<option value="' + this.value + '" data-variable="' + this.hasVariable + '">' + this.text + '</option>');
    });

    $operator.change(function () {
        lfb_linksUpdateFields(jQuery(this));
    });
    var $col1 = jQuery('<td></td>');
    $col1.append($select);
    $item.append($col1);
    var $col2 = jQuery('<td></td>');
    $col2.append($operator);
    $item.append($col2);
    $item.append('<td></td><td><a href="javascript:" class="lfb_conditionDelBtn" onclick="lfb_conditionRemove(this);"><span class="glyphicon glyphicon-remove"></span></a> </td>');
    if (data) {
        $operator.val(data.action);
        $operator.change();
        if (data.value) {
            $operator.closest('.lfb_conditionItem').find('.lfb_conditionValue').val(data.value);
        }

        if (data.value) {
            lfb_linksUpdateFields($operator, data);
        }
    }
    jQuery('#lfb_conditionsTable tbody').append($item);
}

function lfb_linksUpdateFields($operatorSelect, data) {

    $operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValue').parent().remove();
    if ($operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionoperatorSelect option:selected').attr('data-variable') == "textfield") {
        if ($operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValue').length == 0) {
            $operatorSelect.closest('.lfb_conditionItem').children('td:eq(2)').html('<div><input type="text" placeholder="" class="lfb_conditionValue form-control" /> </div>');
        }
    }

    if ($operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionoperatorSelect option:selected').attr('data-variable') == "numberfield") {
        if ($operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValue').length == 0) {
            $operatorSelect.closest('.lfb_conditionItem').children('td:eq(2)').html('<div><input type="number" class="lfb_conditionValue form-control" /> </div>');
        }
    }
    if ($operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionoperatorSelect option:selected').attr('data-variable') == "pricefield") {
        if ($operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValue').length == 0) {
            $operatorSelect.closest('.lfb_conditionItem').children('td:eq(2)').html('<div><input type="number" step="any" class="lfb_conditionValue form-control" /> </div>');
        }
    }

    if ($operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionoperatorSelect option:selected').attr('data-variable') == "datefield") {
        if ($operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValue').length == 0) {
            $operatorSelect.closest('.lfb_conditionItem').children('td:eq(2)').html('<div><input type="text" step="any" class="lfb_conditionValue form-control"/> </div>');
            $operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValue').datetimepicker({
                format: lfb_data.dateFormat,
                showMeridian: (lfb_data.dateMeridian == '1'),
                container: '#estimation_popup.wpe_bootstraped',
                minView: 2
            }).on('show', function () {
                jQuery('.datetimepicker .table-condensed .prev').show();
                jQuery('.datetimepicker .table-condensed .switch').show();
                jQuery('.datetimepicker .table-condensed .next').show();
            });
        }
    }
    if ($operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionoperatorSelect option:selected').attr('data-variable') == "timefield") {
        if ($operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValue').length == 0) {
            $operatorSelect.closest('.lfb_conditionItem').children('td:eq(2)').html('<div><input type="text lfb_timepicker"  class="lfb_conditionValue form-control"/> </div>');

            $operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValue').datetimepicker({
                showMeridian: (lfb_data.dateMeridian == '1'),
                container: '#estimation_popup.wpe_bootstraped',
                format: 'hh:ii',
                startView: 1
            }).on('show', function () {
                jQuery('.datetimepicker .table-condensed .prev').hide();
                jQuery('.datetimepicker .table-condensed .switch').hide();
                jQuery('.datetimepicker .table-condensed .next').hide();
            });
            $operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValue').click(function () {
                jQuery(this).datetimepicker('show');
            });
        }
    }
    if ($operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionoperatorSelect option:selected').attr('data-variable') == "datetimefield") {
        if ($operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValue').length == 0) {
            $operatorSelect.closest('.lfb_conditionItem').children('td:eq(2)').html('<div><input type="text lfb_timepicker"  class="lfb_conditionValue form-control"/> </div>');

            $operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValue').datetimepicker({
                showMeridian: jQuery('#lfb_formFields [name="timeModeAM"]').is(':checked'),
                container: '#estimation_popup.wpe_bootstraped',
                format: 'yyyy-mm-dd hh:ii'
            }).on('show', function () {
                jQuery('.datetimepicker .table-condensed .prev').show();
                jQuery('.datetimepicker .table-condensed .switch').show();
                jQuery('.datetimepicker .table-condensed .next').show();
            });
            $operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValue').click(function () {
                jQuery(this).datetimepicker('show');
            });
        }
    }


    if ($operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionoperatorSelect option:selected').attr('data-variable') == "select") {
        var optionsSelect = '';
        var $select = $operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionSelect');
        var stepID = $select.val().substr(0, $select.val().indexOf('_'));
        var itemID = $select.val().substr($select.val().indexOf('_') + 1, $select.val().length);

        var optionsString = '';
        jQuery.each(lfb_currentForm.steps, function () {
            if (this.id == stepID) {
                jQuery.each(this.items, function () {
                    if (this.id == itemID) {
                        optionsString = this.optionsValues;
                    }
                });
            }
        });

        jQuery.each(lfb_currentForm.fields, function () {
            if (this.id == itemID) {
                optionsString = this.optionsValues;
            }
        });
        var optionsArray = optionsString.split('|');
        jQuery.each(optionsArray, function () {
            var value = this;
            if (value.indexOf(';;') > 0) {
                var valueArray = value.split(';;');
                value = valueArray[0];
            }
            if (value.length > 0) {
                optionsString += '<option value="' + value + '">' + value + '</option>';
            }
        });

        if ($operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValue').length == 0) {
            $operatorSelect.closest('.lfb_conditionItem').children('td:eq(2)').html('<div><select class="lfb_conditionValue form-control">' + optionsString + '</select> </div>');
        }
    }
    if (jQuery('#lfb_winCalculationConditions').css('display') == 'none' && jQuery('#lfb_winRedirection').css('display') == 'none') {
        if ($operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValue').length > 0) {
            if ($operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValueBtn').length == 0) {
                var $btn = jQuery('<a href="javascript:" onclick="lfb_conditionValueBtnClick(this);" class="lfb_conditionValueBtn"><span class="glyphicon glyphicon-list-alt"></span></a>');

                $operatorSelect.closest('.lfb_conditionItem').children('td:eq(3)').prepend($btn);
            }
            if ($operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValueMenu').length == 0) {
                var $menu = jQuery('<div class="lfb_conditionValueMenu"></div>');
                var $menuItem = $operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionSelect').clone();
                $menuItem.on('change', function () {
                    lfb_updateConditionValueElements(this);
                });
                $menuItem.css({
                    width: '52%',
                    display: 'inline-block',
                    marginRight: 5
                });
                $menuItem.removeClass('lfb_conditionSelect').addClass('lfb_conditionValueItemSelect');
                $menu.append($menuItem);

                var $menuElement = jQuery('<select class="form-control lfb_conditionAttributeMenu" style="width:45%;display:inline-block; float:right;"></select>');
                $menuElement.append('<option value="">' + lfb_data.texts['price'] + '</value>');
                $menuElement.append('<option value="quantity">' + lfb_data.texts['quantity'] + '</value>');
                $menuElement.append('<option value="value">' + lfb_data.texts['value'] + '</value>');
                $menu.append($menuElement);

                $operatorSelect.closest('.lfb_conditionItem').children('td:eq(2)').append($menu);
            }
        } else {
            $operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValueBtn').remove();
        }
    }
    setTimeout(function () {
        if (data && data.value) {
            if (data.value.indexOf('_') > -1) {
                var itemID = data.value.substr(0, data.value.indexOf('-'));
                var attribute = data.value.substr(data.value.indexOf('-') + 1, data.value.length);
                $operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValueItemSelect').val(itemID);
                $operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValueItemSelect').trigger('change');
                $operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionAttributeMenu').val(attribute);
                $operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValue').parent().hide();
                $operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValueMenu').show();

            } else {
                $operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValueMenu').hide();
                $operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValue').parent().show();
                $operatorSelect.closest('.lfb_conditionItem').find('.lfb_conditionValue').val(data.value);
            }
        } else {
        }
    }, 500);
}

function lfb_updateConditionValueElements(select) {
    var $selectItem = jQuery(select);
    var $selectElement = jQuery(select).next('.lfb_conditionAttributeMenu');
    $selectElement.val('');
    $selectElement.find('option[value="quantity"]').hide();
    $selectElement.find('option[value=""]').show();
    if ($selectItem.val() != "") {
        var selectedItemID = $selectItem.val();
        if (selectedItemID.indexOf('_') > 0) {
            selectedItemID = selectedItemID.substr(selectedItemID.indexOf('_') + 1, selectedItemID.length);
        }
        jQuery.each(lfb_currentForm.steps, function () {
            jQuery.each(this.items, function () {
                if (this.id == selectedItemID) {
                    if (this.quantity_enabled == 1 || this.type == 'slider') {
                        $selectElement.find('option[value="quantity"]').show();
                    } else {
                        $selectElement.find('option[value="quantity"]').hide();
                    }
                    if (this.type == 'numberfield') {
                        $selectElement.find('option[value="value"]').show();
                        $selectElement.find('option[value=""]').hide();
                        $selectElement.val('value');
                    } else if (this.type == 'textfield' || this.type == 'select' || this.type == 'textarea' || this.type == 'datepicker' || this.type == 'timepicker') {
                        $selectElement.find('option[value="value"]').show();
                        $selectElement.find('option[value=""]').hide();
                        $selectElement.val('value');
                    } else {
                        $selectElement.find('option[value="value"]').hide();
                        $selectElement.find('option[value=""]').show();
                    }
                }
            });
        });
        jQuery.each(lfb_currentForm.fields, function () {
            if (this.id == selectedItemID) {
                if (this.quantity_enabled == 1 || this.type == 'slider') {
                    $selectElement.find('option[value="quantity"]').show();
                } else {
                    $selectElement.find('option[value="quantity"]').hide();
                }
                if (this.type == 'numberfield') {
                    $selectElement.find('option[value="value"]').show();
                    $selectElement.find('option[value=""]').hide();
                    $selectElement.val('value');
                } else {
                    $selectElement.find('option[value="value"]').hide();
                    $selectElement.find('option[value=""]').show();
                }
            }
        });

        if ($selectItem.val() == "_total") {
            $selectElement.find('option[value="quantity"]').hide();
            $selectElement.find('option[value=""]').show();
            $selectElement.val('');

        }
        if ($selectItem.val() == "_total_qt") {
            $selectElement.find('option[value="quantity"]').show();
            $selectElement.find('option[value=""]').hide();
            $selectElement.val('quantity');

        }
    }
}

function lfb_conditionValueBtnClick(btn) {
    var $btn = jQuery(btn);
    $btn.closest('.lfb_conditionItem').find('.lfb_conditionValueItemSelect').trigger('change');
    if ($btn.closest('.lfb_conditionItem').children('td:eq(2)').find('div:not(.lfb_conditionValueMenu)').css('display') != 'none') {
        $btn.closest('.lfb_conditionItem').children('td:eq(2)').find('div:not(.lfb_conditionValueMenu)').hide();
        $btn.closest('.lfb_conditionItem').children('td:eq(2)').find('div.lfb_conditionValueMenu').show();
    } else {
        $btn.closest('.lfb_conditionItem').children('td:eq(2)').find('div:not(.lfb_conditionValueMenu)').show();
        $btn.closest('.lfb_conditionItem').children('td:eq(2)').find('div.lfb_conditionValueMenu').hide();
    }
}

function lfb_conditionRemove(btn) {
    var $btn = jQuery(btn);
    $btn.closest('.lfb_conditionItem').remove();
}
function lfb_getConditionValue($field) {
    var rep = $field.val();
    if ($field.parent().css('display') == 'none') {
        rep = $field.closest('td').find('.lfb_conditionValueItemSelect').val() + '-' + $field.closest('td').find('.lfb_conditionAttributeMenu').val();
    }
    return rep;
}

function lfb_linkSave() {
    if (lfb_canSaveLink) {
        lfb_canSaveLink = false;
        // lfb_showLoader();
        lfb_links[lfb_currentLinkIndex].conditions = new Array();
        jQuery('.lfb_conditionItem').each(function () {
            lfb_links[lfb_currentLinkIndex].conditions.push({
                interaction: jQuery(this).find('.lfb_conditionSelect').val(),
                action: jQuery(this).find('.lfb_conditionoperatorSelect').val(),
                value: lfb_getConditionValue(jQuery(this).find('.lfb_conditionValue'))
            });
        });
        lfb_links[lfb_currentLinkIndex].operator = jQuery('#lfb_linkOperator').val();

        var cloneLinks = lfb_links.slice();
        jQuery.each(cloneLinks, function () {
            this.originID = jQuery('#' + this.originID).attr('data-stepid');
            this.destinationID = jQuery('#' + this.destinationID).attr('data-stepid');
        });
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_saveLinks',
                formID: lfb_currentFormID,
                links: JSON.stringify(cloneLinks)
            },
            success: function () {
                lfb_closeWin(jQuery('#lfb_winLink'));
                //   lfb_loadForm(lfb_currentFormID);

                lfb_loadFields();
                //lfb_loadForm(lfb_currentFormID);
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: {
                        action: 'lfb_loadForm',
                        formID: lfb_currentFormID
                    },
                    success: function (rep) {
                        rep = JSON.parse(rep);
                        lfb_currentForm = rep;
                        lfb_params = rep.params;
                        lfb_steps = rep.steps;
                        jQuery.each(rep.links, function (index) {
                            var link = this;
                            link.originID = jQuery('.lfb_stepBloc[data-stepid="' + link.originID + '"]').attr('id');
                            link.destinationID = jQuery('.lfb_stepBloc[data-stepid="' + link.destinationID + '"]').attr('id');
                            link.conditions = JSON.parse(link.conditions);
                            lfb_links[index] = link;
                        });
                    }
                });

                lfb_canSaveLink = true;
            }
        });
    }

}

function lfb_linkDel() {
    if (lfb_canSaveLink) {
        jQuery('.lfb_linkPoint[data-linkindex=' + lfb_currentLinkIndex + ']').remove();
        lfb_canSaveLink = false;
        setTimeout(function () {
            lfb_canSaveLink = true;
        }, 1500);
        lfb_links.splice(jQuery.inArray(lfb_links[lfb_currentLinkIndex], lfb_links), 1);
        var cloneLinks = lfb_links.slice();
        jQuery.each(cloneLinks, function () {
            this.originID = jQuery('#' + this.originID).attr('data-stepid');
            this.destinationID = jQuery('#' + this.destinationID).attr('data-stepid');
        });
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_saveLinks',
                formID: lfb_currentFormID,
                links: JSON.stringify(cloneLinks)
            },
            success: function () {
                lfb_closeWin(jQuery('#lfb_winLink'));
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: {
                        action: 'lfb_loadForm',
                        formID: lfb_currentFormID
                    },
                    success: function (rep) {
                        rep = JSON.parse(rep);
                        lfb_currentForm = rep;
                        lfb_params = rep.params;
                        lfb_steps = rep.steps;
                        jQuery.each(rep.links, function (index) {
                            var link = this;
                            link.originID = jQuery('.lfb_stepBloc[data-stepid="' + link.originID + '"]').attr('id');
                            link.destinationID = jQuery('.lfb_stepBloc[data-stepid="' + link.destinationID + '"]').attr('id');
                            link.conditions = JSON.parse(link.conditions);
                            lfb_links[index] = link;
                        });
                    }
                });

                //   lfb_loadForm(lfb_currentFormID);
            }
        });
    }
}

function lfb_conditionGetOperators(item, $select) {
    var options = new Array();
    switch (item.type) {
        case "step":
            options.push({
                value: 'superior',
                text: lfb_data.texts['isSuperior'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'inferior',
                text: lfb_data.texts['isInferior'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'equal',
                text: lfb_data.texts['isEqual'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'different',
                text: lfb_data.texts['isntEqual'],
                hasVariable: 'numberfield'
            });
            break;
        case "totalPrice":
            options.push({
                value: 'superior',
                text: lfb_data.texts['isSuperior'],
                hasVariable: 'pricefield'
            });
            options.push({
                value: 'inferior',
                text: lfb_data.texts['isInferior'],
                hasVariable: 'pricefield'
            });
            options.push({
                value: 'equal',
                text: lfb_data.texts['isEqual'],
                hasVariable: 'pricefield'
            });
            options.push({
                value: 'different',
                text: lfb_data.texts['isntEqual'],
                hasVariable: 'pricefield'
            });
            break;
        case "totalQt":
            options.push({
                value: 'superior',
                text: lfb_data.texts['isSuperior'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'inferior',
                text: lfb_data.texts['isInferior'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'equal',
                text: lfb_data.texts['isEqual'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'different',
                text: lfb_data.texts['isntEqual'],
                hasVariable: 'numberfield'
            });
            break;

        case "picture":
            options.push({
                value: 'clicked',
                text: lfb_data.texts['isSelected']
            });
            options.push({
                value: 'unclicked',
                text: lfb_data.texts['isUnselected']
            });
            options.push({
                value: 'PriceSuperior',
                text: lfb_data.texts['isPriceSuperior'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'PriceInferior',
                text: lfb_data.texts['isPriceInferior'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'PriceEqual',
                text: lfb_data.texts['isPriceEqual'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'PriceDifferent',
                text: lfb_data.texts['isntPriceEqual'],
                hasVariable: 'numberfield'
            });
            if (item.quantity_enabled == "1") {
                options.push({
                    value: 'QtSuperior',
                    text: lfb_data.texts['isQuantitySuperior'],
                    hasVariable: 'numberfield'
                });
                options.push({
                    value: 'QtInferior',
                    text: lfb_data.texts['isQuantityInferior'],
                    hasVariable: 'numberfield'
                });
                options.push({
                    value: 'QtEqual',
                    text: lfb_data.texts['isQuantityEqual'],
                    hasVariable: 'numberfield'
                });
                options.push({
                    value: 'QtDifferent',
                    text: lfb_data.texts['isntQuantityEqual'],
                    hasVariable: 'numberfield'
                });
            }
            break;
        case "button":
            options.push({
                value: 'clicked',
                text: lfb_data.texts['isSelected']
            });
            options.push({
                value: 'unclicked',
                text: lfb_data.texts['isUnselected']
            });
            options.push({
                value: 'PriceSuperior',
                text: lfb_data.texts['isPriceSuperior'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'PriceInferior',
                text: lfb_data.texts['isPriceInferior'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'PriceEqual',
                text: lfb_data.texts['isPriceEqual'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'PriceDifferent',
                text: lfb_data.texts['isntPriceEqual'],
                hasVariable: 'numberfield'
            });
            break;
        case "slider":
            options.push({
                value: 'PriceSuperior',
                text: lfb_data.texts['isPriceSuperior'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'PriceInferior',
                text: lfb_data.texts['isPriceInferior'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'PriceEqual',
                text: lfb_data.texts['isPriceEqual'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'PriceDifferent',
                text: lfb_data.texts['isntPriceEqual'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'QtSuperior',
                text: lfb_data.texts['isQuantitySuperior'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'QtInferior',
                text: lfb_data.texts['isQuantityInferior'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'QtEqual',
                text: lfb_data.texts['isQuantityEqual'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'QtDifferent',
                text: lfb_data.texts['isntQuantityEqual'],
                hasVariable: 'numberfield'
            });
            break;

        case "textfield":
            options.push({
                value: 'filled',
                text: lfb_data.texts['isFilled']
            });
            options.push({
                value: 'equal',
                text: lfb_data.texts['isEqual'],
                hasVariable: 'textfield'
            });
            options.push({
                value: 'different',
                text: lfb_data.texts['isntEqual'],
                hasVariable: 'textfield'
            });
            break;
        case "numberfield":
            options.push({
                value: 'filled',
                text: lfb_data.texts['isFilled']
            });
            options.push({
                value: 'superior',
                text: lfb_data.texts['isSuperior'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'inferior',
                text: lfb_data.texts['isInferior'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'equal',
                text: lfb_data.texts['isEqual'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'different',
                text: lfb_data.texts['isntEqual'],
                hasVariable: 'numberfield'
            });
            break;
        case "textarea":
            options.push({
                value: 'filled',
                text: lfb_data.texts['isFilled']
            });
            break;
        case "datepicker":
            options.push({
                value: 'filled',
                text: lfb_data.texts['isFilled']
            });
            if ($select.find('option:selected').is('[data-datetype="date"]')) {
                options.push({
                    value: 'superior',
                    text: lfb_data.texts['isSuperior'],
                    hasVariable: 'datefield'
                });
                options.push({
                    value: 'inferior',
                    text: lfb_data.texts['isInferior'],
                    hasVariable: 'datefield'
                });
                options.push({
                    value: 'equal',
                    text: lfb_data.texts['isEqual'],
                    hasVariable: 'datefield'
                });
                options.push({
                    value: 'different',
                    text: lfb_data.texts['isntEqual'],
                    hasVariable: 'datefield'
                });
            } else if ($select.find('option:selected').is('[data-datetype="time"]')) {
                options.push({
                    value: 'superior',
                    text: lfb_data.texts['isSuperior'],
                    hasVariable: 'timefield'
                });
                options.push({
                    value: 'inferior',
                    text: lfb_data.texts['isInferior'],
                    hasVariable: 'timefield'
                });
                options.push({
                    value: 'equal',
                    text: lfb_data.texts['isEqual'],
                    hasVariable: 'timefield'
                });
                options.push({
                    value: 'different',
                    text: lfb_data.texts['isntEqual'],
                    hasVariable: 'timefield'
                });
            } else if ($select.find('option:selected').is('[data-datetype="dateTime"]')) {
                options.push({
                    value: 'superior',
                    text: lfb_data.texts['isSuperior'],
                    hasVariable: 'datetimefield'
                });
                options.push({
                    value: 'inferior',
                    text: lfb_data.texts['isInferior'],
                    hasVariable: 'datetimefield'
                });
                options.push({
                    value: 'equal',
                    text: lfb_data.texts['isEqual'],
                    hasVariable: 'datetimefield'
                });
                options.push({
                    value: 'different',
                    text: lfb_data.texts['isntEqual'],
                    hasVariable: 'datetimefield'
                });
            }

            break;
        case "timepicker":
            options.push({
                value: 'filled',
                text: lfb_data.texts['isFilled']
            });
            options.push({
                value: 'superior',
                text: lfb_data.texts['isSuperior'],
                hasVariable: 'timefield'
            });
            options.push({
                value: 'inferior',
                text: lfb_data.texts['isInferior'],
                hasVariable: 'timefield'
            });
            options.push({
                value: 'equal',
                text: lfb_data.texts['isEqual'],
                hasVariable: 'timefield'
            });
            options.push({
                value: 'different',
                text: lfb_data.texts['isntEqual'],
                hasVariable: 'timefield'
            });
            break;
        case "select":
            options.push({
                value: 'equal',
                text: lfb_data.texts['isEqual'],
                hasVariable: 'select'
            });
            options.push({
                value: 'different',
                text: lfb_data.texts['isntEqual'],
                hasVariable: 'select'
            });
            break;
        case "filefield":
            options.push({
                value: 'filled',
                text: lfb_data.texts['isFilled']
            });
            break;
        case "checkbox":
            options.push({
                value: 'clicked',
                text: lfb_data.texts['isSelected']
            });
            options.push({
                value: 'unclicked',
                text: lfb_data.texts['isUnselected']
            });
            options.push({
                value: 'PriceSuperior',
                text: lfb_data.texts['isPriceSuperior'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'PriceInferior',
                text: lfb_data.texts['isPriceInferior'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'PriceEqual',
                text: lfb_data.texts['isPriceEqual'],
                hasVariable: 'numberfield'
            });
            options.push({
                value: 'PriceDifferent',
                text: lfb_data.texts['isntPriceEqual'],
                hasVariable: 'numberfield'
            });
            break;
        case "datefield":
            options.push({
                value: 'filled',
                text: lfb_data.txt_filled
            });
            options.push({
                value: 'superior',
                text: lfb_data.txt_superiorTo
            });
            options.push({
                value: 'inferior',
                text: lfb_data.txt_inferiorTo
            });
            options.push({
                value: 'equal',
                text: lfb_data.txt_equalTo
            });
            options.push({
                value: 'different',
                text: lfb_data.texts['isntEqual']
            });
            break;
        case "date":
            options.push({
                value: 'superior',
                text: lfb_data.txt_superiorTo
            });
            options.push({
                value: 'inferior',
                text: lfb_data.txt_inferiorTo
            });
            options.push({
                value: 'equal',
                text: lfb_data.txt_equalTo
            });
            options.push({
                value: 'different',
                text: lfb_data.texts['isntEqual']
            });
            break;
    }
    return options;
}


function lfb_updateWinItemPosition() {
    if (jQuery('#lfb_winStep').css('display') != 'none') {
        var $item = jQuery('#' + jQuery('#lfb_itemWindow').attr('data-item'));
        if ($item.length > 0) {
            jQuery('#lfb_itemWindow').css({
                top: $item.offset().top - jQuery('#lfb_bootstraped.lfb_bootstraped').offset().top + $item.outerHeight() + 12,
                left: $item.offset().left - jQuery('#lfb_bootstraped.lfb_bootstraped').offset().left
            });
        } else {
            jQuery('#lfb_itemWindow').fadeOut();
        }
    } else {
        jQuery('#lfb_itemWindow').fadeOut();
    }
}

function lfb_checkEmail(emailToTest) {
    if (emailToTest.indexOf("@") != "-1" && emailToTest.indexOf(".") != "-1" && emailToTest != "")
        return true;
    return false;
}


function lfb_existInDefaultStep(itemID) {
    var rep = false;
    jQuery.each(lfb_defaultStep.interactions, function () {
        var interaction = this;
        if (interaction.itemID == itemID) {
            rep = true;
        }
    });
    return rep;
}

function lfb_removeAllSteps() {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_removeAllSteps',
            formID: lfb_currentFormID
        },
        success: function () {
            lfb_loadForm(lfb_currentFormID);
        }
    });
}

function lfb_addForm() {
    lfb_showLoader();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_addForm'
        },
        success: function (formID) {
            lfb_loadForm(formID);
        }
    });
}
function lfb_removeForm(formID) {
    lfb_showLoader();

    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_removeForm',
            formID: formID
        },
        success: function () {
            lfb_closeSettings();
        }
    });

}
function lfb_saveForm() {
    lfb_showLoader();
    var formData = {};
    var globalData = {};
    jQuery('#lfb_formFields').find('input,select,textarea').each(function () {
        if (jQuery(this).closest('#lfb_gdprSettings').length == 0 && jQuery(this).closest('#lfb_fieldBubble').length == 0 && jQuery(this).closest('#lfb_couponsTable').length == 0 && jQuery(this).closest('#lfb_distanceValueBubble').length == 0 && jQuery(this).closest('#lfb_calculationDatesDiffBubble').length == 0) {
            if (!jQuery(this).is('[data-switch="switch"]')) {
                if (jQuery(this).is('[name="percentToPay"]')) {
                    /* if (jQuery(this).val() == 0 || jQuery(this).val() > 100) {
                     jQuery(this).val('100');
                     }*/
                }
                if (jQuery(this).is('[name="stripe_percentToPay"]')) {
                    /* if (jQuery(this).val() == 0 || jQuery(this).val() > 100) {
                     jQuery(this).val('100');
                     }*/
                }
                eval('formData.' + jQuery(this).attr('name') + ' = jQuery(this).val();');
            } else {
                var value = 0;
                if (jQuery(this).is(':checked')) {
                    value = 1;
                }
                eval('formData.' + jQuery(this).attr('name') + ' = value;');
            }
        }
    });
    jQuery('#lfb_gdprSettings').find('input,select,textarea').each(function () {
        if (!jQuery(this).is('[data-switch="switch"]')) {
            eval('globalData.' + jQuery(this).attr('name') + ' = jQuery(this).val();');
        } else {
            var value = 0;
            if (jQuery(this).is(':checked')) {
                value = 1;
            }
            eval('globalData.' + jQuery(this).attr('name') + ' = value;');
        }
    });
    if (jQuery('#lfb_formFields [name="encryptDB"]').is(':checked')) {
        globalData.encryptDB = 1;
    } else {
        globalData.encryptDB = 0;
    }
    formData.pdf_adminContent = jQuery('#pdf_adminContent').code();
    formData.pdf_userContent = jQuery('#pdf_userContent').code();
    formData.email_adminContent = jQuery('#email_adminContent').code();
    formData.email_userContent = jQuery('#email_userContent').code();
    formData.legalNoticeContent = jQuery('#lfb_legalNoticeContent').code();
    formData.customCss = lfb_editorCustomCSS.getValue();
    formData.customJS = lfb_editorCustomJS.getValue();
    formData.lastSave = Date.now();

    formData.action = 'lfb_saveForm';
    formData.formID = lfb_currentFormID;
    formData.globalData = JSON.stringify(globalData);

    lfb_disableLinksAnim = false;
    if (formData.disableLinksAnim == 1) {
        lfb_disableLinksAnim = true;
    }
    clearInterval(lfb_canvasTimer);
    if (!lfb_disableLinksAnim) {
        lfb_canvasTimer = setInterval(lfb_updateStepCanvas, 30);
    }
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: formData,
        success: function () {
            jQuery('#lfb_loader').fadeOut();
        }
    });
}
function lfb_editField(fieldID) {
    jQuery('#lfb_fieldBubble').find('input,textarea').val('');
    jQuery('#lfb_fieldBubble').find('select option').removeAttr('selected');
    jQuery('#lfb_fieldBubble').find('select option:eq(0)').attr('selected', 'selected');
    if (fieldID > 0) {
        jQuery.each(lfb_currentForm.fields, function () {
            var field = this;
            if (field.id == fieldID) {
                jQuery('#lfb_fieldBubble').find('input,select,textarea').each(function () {
                    eval('jQuery(this).val(field.' + jQuery(this).attr('name') + ');');
                });
            }
        });
        jQuery('#lfb_fieldBubble').css({
            left: jQuery('#lfb_finalStepFields tr[data-fieldid="' + fieldID + '"] td:eq(0) a').offset().left,
            top: jQuery('#lfb_finalStepFields tr[data-fieldid="' + fieldID + '"] td:eq(0) a').offset().top
        });
    } else {
        jQuery('#lfb_fieldBubble').find('input[name="id"]').val(0);
        jQuery('#lfb_fieldBubble').css({
            left: jQuery('#lfb_addFieldBtn').offset().left,
            top: jQuery('#lfb_addFieldBtn').offset().top + 18
        });
    }
    jQuery('#lfb_fieldBubble').fadeIn();
    jQuery('#lfb_fieldBubble').addClass('lfb_hover');
    setTimeout(function () {
        jQuery('#lfb_fieldBubble').removeClass('lfb_hover');
    }, 50);

}
function lfb_saveField() {
    lfb_showLoader();
    jQuery('#lfb_fieldBubble').fadeOut();
    var fieldData = {};
    jQuery('#lfb_fieldBubble').find('input,select,textarea').each(function () {
        eval('fieldData.' + jQuery(this).attr('name') + ' = jQuery(this).val();');
    });
    fieldData.action = 'lfb_saveField';
    fieldData.formID = lfb_currentFormID;
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: fieldData,
        success: function () {
            lfb_loadFields();
        }
    });
}
function lfb_loadFields() {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_loadFields',
            formID: lfb_currentFormID
        },
        success: function (fields) {
            jQuery('#lfb_finalStepFields table tbody').html('');
            if (fields != "[]") {
                fields = JSON.parse(fields);
                lfb_currentForm.fields = fields;
                jQuery.each(fields, function () {
                    var field = this;
                    var $tr = jQuery('<tr data-fieldid="' + field.id + '"></tr>');
                    $tr.append('<td><a href="javascript:" onclick="lfb_editItem(' + field.id + ');">' + field.title + '</a></td>');
                    $tr.append('<td>' + field.type + '</td>');
                    $tr.append('<td>' + field.groupitems + '</td>');
                    $tr.append('<td>' +
                            '<a href="javascript:"  data-toggle="tooltip" data-placement="bottom" title="' + lfb_data.texts['edit'] + '" onclick="lfb_editItem(' + field.id + ');" class="btn btn-primary btn-circle"><span class="glyphicon glyphicon-pencil"></span></a>' +
                            '<a href="javascript:"  data-toggle="tooltip" data-placement="bottom" title="' + lfb_data.texts['duplicate'] + '" onclick="lfb_duplicateItemLastStep(' + field.id + ');" class="btn btn-default btn-circle"><span class="glyphicon glyphicon-duplicate"></span></a>' +
                            '<a href="javascript:"  data-toggle="tooltip" data-placement="bottom" title="' + lfb_data.texts['remove'] + '" onclick="lfb_removeItem(' + field.id + ');" class="btn btn-danger btn-circle"><span class="glyphicon glyphicon-trash"></span></a>' +
                            '</td>');
                    $tr.find('[data-toggle="tooltip"]').b_tooltip();
                    jQuery('#lfb_finalStepFields table tbody').append($tr);
                    if (lfb_data.designForm == 0) {
                        jQuery('#lfb_loader').fadeOut();
                    }

                });
            }
        }
    });
}
function lfb_removeField(fieldID) {
    jQuery('#lfb_finalStepFields table tr[data-fieldid="' + fieldID + '"]').slideUp();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_removeField',
            fieldID: fieldID
        }
    });
}
function lfb_loadForm(formID) {
    lfb_currentFormID = formID;

    jQuery('#lfb_btnLogsForm').attr('data-formid', formID);
    lfb_showLoader();
    jQuery('#lfb_stepsContainer .lfb_stepBloc,.lfb_loadSteps,.lfb_linkPoint').remove();
    /*jQuery('#lfb_formFields').find('.switch [data-switch="switch"]').bootstrapSwitch('destroy');
     jQuery('#lfb_formFields').find('.switch > div > :not([data-switch="switch"])').remove();
     jQuery('#lfb_formFields').find('.switch [data-switch="switch"]').unwrap().unwrap();*/
    jQuery('#lfb_formFields').find('#lfb_itemPricesGrid tbody tr').not('.static').remove();
    lfb_loadFields();
    jQuery('#lfb_logsBtn').attr('data-formid', formID);
    jQuery('#lfb_chartsBtn').attr('data-formid', formID);

    jQuery('#lfb_btnPreview').attr('href', lfb_data.websiteUrl + '?lfb_action=preview&form=' + formID);
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_loadForm',
            formID: formID
        },
        success: function (rep) {

            rep = JSON.parse(rep);
            lfb_currentForm = rep;
            lfb_params = rep.params;
            lfb_steps = rep.steps;

            lfb_disableLinksAnim = false;
            if (rep.form.disableLinksAnim == '1') {
                lfb_disableLinksAnim = true;
            }

            if (!lfb_disableLinksAnim) {
                clearInterval(lfb_canvasTimer);
                lfb_canvasTimer = setInterval(lfb_updateStepCanvas, 30);
            } else {
                jQuery('#lfb_stepsOverflow').bind('scroll', lfb_updateStepCanvas);
            }
            jQuery('#lfb_formFields').find('input,select,textarea').each(function () {
                if (!jQuery(this).is('[data-name="encryptDB"]') && jQuery(this).closest('#lfb_gdprSettings').length == 0 && jQuery(this).closest('#lfb_calculationDatesDiffBubble').length == 0 && jQuery(this).closest('#lfb_calculationValueBubble').length == 0) {
                    if (jQuery(this).is('[data-switch="switch"]')) {
                        var value = false;
                        eval('if(rep.form.' + jQuery(this).attr('name') + ' == 1){jQuery(this).attr(\'checked\',\'checked\');} else {jQuery(this).attr(\'checked\',false);}');
                        eval('if(rep.form.' + jQuery(this).attr('name') + ' == 1){ jQuery(this).parent().bootstrapSwitch("setState",true); } else {jQuery(this).parent().bootstrapSwitch("setState",false);}');

                        // jQuery(this).wrap('<div class="switch" data-on-label="' + lfb_data.texts['Yes'] + '" data-off-label="' + lfb_data.texts['No'] + '" />').parent().bootstrapSwitch({onLabel: lfb_data.texts['Yes'], offLabel: lfb_data.texts['No']});
                        var self = this;
                        if (jQuery(self).closest('.form-group').find('small').length > 0) {
                            jQuery(self).closest('.has-switch').b_tooltip({
                                title: jQuery(self).closest('.form-group').find('small').html()
                            });
                        }
                    } else if (jQuery(this).is('pre')) {
                        eval('jQuery(this).html(rep.form.' + jQuery(this).attr('name') + ');');
                    } else {
                        eval('jQuery(this).val(rep.form.' + jQuery(this).attr('name') + ');');
                    }
                }
            });
           

            lfb_initFormsBackend();

            jQuery('#lfb_itemRichText').summernote({
                height: 300,
                minHeight: null,
                maxHeight: null,
            });
            jQuery('#lfb_tabEmail').show();
            jQuery('#email_adminContent').summernote({
                height: 300,
                minHeight: null,
                maxHeight: null,
            });
            jQuery('#email_adminContent').code(rep.form.email_adminContent);
            jQuery('#lfb_formEmailUser').show();
            jQuery('#email_userContent').summernote({
                height: 300,
                minHeight: null,
                maxHeight: null,
            });
            jQuery('#email_userContent').code(rep.form.email_userContent);

            jQuery('#pdf_adminContent').summernote({
                height: 300,
                minHeight: null,
                maxHeight: null,
            });
            jQuery('#pdf_adminContent').code(rep.form.pdf_adminContent);

            jQuery('#pdf_userContent').summernote({
                height: 300,
                minHeight: null,
                maxHeight: null,
            });
            jQuery('#pdf_userContent').code(rep.form.pdf_userContent);

            jQuery('#lfb_legalNoticeContent').summernote({
                height: 300,
                minHeight: null,
                maxHeight: null,
            });
            jQuery('#lfb_legalNoticeContent').code(rep.form.legalNoticeContent);
            if (rep.form.customJS) {
                lfb_editorCustomJS.setValue(rep.form.customJS);
            }
            lfb_editorCustomCSS.setValue(rep.form.customCss);
            setTimeout(function () {
                lfb_editorCustomJS.refresh();
            }, 100);
            jQuery('.imageBtn').click(function () {
                lfb_formfield = jQuery(this).prev('input');
                tb_show('', 'media-upload.php?TB_iframe=true');
                return false;
            });

            if (!jQuery('#lfb_formFields [name="email_toUser"]').is(':checked')) {
                jQuery('#lfb_formEmailUser').hide();
            }
            jQuery('#lfb_tabEmail').attr('style', '');
            jQuery('#lfb_tabEmail').prop('style', '');

            jQuery('.colorpick').each(function () {
                var $this = jQuery(this);
                if (jQuery(this).prev('.lfb_colorPreview').length == 0) {
                    jQuery(this).before('<div class="lfb_colorPreview" style="background-color:#' + $this.val().substr(1, 7) + '"></div>');
                }
                jQuery(this).prev('.lfb_colorPreview').click(function () {
                    jQuery(this).next('.colorpick').trigger('click');
                });
                jQuery(this).colpick({
                    color: $this.val().substr(1, 7),
                    onChange: function (hsb, hex, rgb, el, bySetColor) {
                        jQuery(el).val('#' + hex);
                        jQuery(el).prev('.lfb_colorPreview').css({
                            backgroundColor: '#' + hex
                        });
                    }
                });
            });

            jQuery.each(rep.steps, function (index) {
                var step = this;
                if (step.content != "") {
                    step.content = step.content.replace('}"', '}');
                    step.content = step.content.replace('"{', '{');
                    try {
                        step.content = JSON.parse(step.content);
                        lfb_addStep(step);
                    } catch (e) {
                    }
                }
            });
            jQuery.each(rep.links, function (index) {
                var link = this;
                link.originID = jQuery('.lfb_stepBloc[data-stepid="' + link.originID + '"]').attr('id');
                link.destinationID = jQuery('.lfb_stepBloc[data-stepid="' + link.destinationID + '"]').attr('id');
                link.conditions = JSON.parse(link.conditions);
                lfb_links[index] = link;
            });

            jQuery.each(rep.redirections, function (index) {
                var tr = jQuery('<tr data-id="' + this.id + '"></tr>');
                tr.append('<td>' + this.url + '</td>');
                tr.append('<td><a href="javascript:"  data-toggle="tooltip" data-placement="bottom" title="' + lfb_data.texts['edit'] + '" onclick="lfb_editRedirection(' + this.id + ');" class="btn btn-primary btn-circle"><span class="glyphicon glyphicon-pencil"></span></a><a href="javascript:" onclick="lfb_removeRedirection(' + this.id + ');" class="btn btn-danger btn-circle"><span class="glyphicon glyphicon-trash"></span></a></td>');

                jQuery('#lfb_redirsTable tbody').append(tr);
            });

            jQuery('#lfb_formFields').find('input.lfb_iconField').trigger('change');
            jQuery('#lfb_panelPreview').show();
            jQuery('#lfb_panelFormsList').hide();
            jQuery('#lfb_panelLogs').hide();
            jQuery('#lfb_panelSettings').hide();

            jQuery('#lfb_couponsTable tbody').html('');
            jQuery.each(rep.coupons, function () {
                var coupon = this;

                if (coupon.reductionType == 'percentage') {
                    coupon.reduction = '-' + coupon.reduction + '%';
                } else {
                    coupon.reduction = '-' + parseFloat(coupon.reduction).toFixed(2);
                }

                var tdAction = '<td style="text-align:right;">' +
                        '<a href="javascript:"  data-toggle="tooltip" data-placement="bottom" title="' + lfb_data.texts['edit'] + '" onclick="lfb_editCoupon(' + coupon.id + ');" class="btn btn-primary btn-circle"><span class="glyphicon glyphicon-pencil"></span></a>' +
                        '<a href="javascript:"  data-toggle="tooltip" data-placement="bottom" title="' + lfb_data.texts['remove'] + '" onclick="lfb_removeCoupon(' + coupon.id + ');" class="btn btn-danger btn-circle"><span class="glyphicon glyphicon-trash"></span></a>' +
                        '</td>';
                jQuery('#lfb_couponsTable tbody').append('<tr data-couponid="' + coupon.id + '"><td>' + coupon.couponCode + '</td><td>' + coupon.useMax + '</td><td>' + coupon.currentUses + '</td><td>' + coupon.reduction + '</td>' + tdAction + '</tr>');
            });

            jQuery('input.lfb_timepicker').each(function () {
                jQuery(this).datetimepicker({
                    showMeridian: jQuery('#lfb_formFields [name="timeModeAM"]').is(':checked'),
                    container: '#estimation_popup.wpe_bootstraped',
                    format: 'HH:mm'
                });
                jQuery(this).click(function () {
                    jQuery(this).datetimepicker('show');
                });
            });

            jQuery('#lfb_finalStepFields table tbody tr').each(function () {
                var itemType = jQuery(this).find('td:eq(1)').text();
                if (jQuery('#lfb_winItem').find('[name="type"] option[value="' + itemType + '"]').length > 0) {
                    var typeName = jQuery('#lfb_winItem').find('[name="type"] option[value="' + itemType + '"]').text();
                    jQuery(this).find('td:eq(1)').html(typeName);
                }
            });

            lfb_updateStepsDesign();

            if (lfb_openChartsAuto) {
                lfb_openChartsAuto = false;
                lfb_loadCharts(formID);
            } else {

                if (lfb_data.designForm == 0) {
                    jQuery('#lfb_loader').delay(1000).fadeOut();
                }
            }
             if (lfb_settings.encryptDB == 1) {
                jQuery('#lfb_formFields [name="encryptDB"]').parent().bootstrapSwitch("setState", true);
            } else {
                jQuery('#lfb_formFields [name="encryptDB"]').parent().bootstrapSwitch("setState", false);
            }

            jQuery('#lfb_emailTemplateAdmin').css({
                minHeight: jQuery('#lfb_emailTemplateCustomer').outerHeight()
            });
            setTimeout(function () {
                jQuery('a[href="#collapse-lfb_tabEmail"]').click(lfb_openEmailTab);
                lfb_updateStepsDesign();
                lfb_updateStepCanvas();
            }, 250);

            if (lfb_data.designForm != 0) {

                lfb_data.designForm = 0;
                window.history.pushState('lfb', document.title, 'admin.php?page=lfb_menu');
                lfb_openFormDesigner();
            }
        }
    });
}
function lfb_openEmailTab() {
    setTimeout(function () {
        jQuery('#lfb_emailTemplateAdmin').css({minHeight: jQuery('#lfb_emailTemplateCustomer').outerHeight()});
    }, 100);
}
function lfb_initCharts() {
    google.charts.load('current', {'packages': ['corechart']});
}
function lfb_openCharts(formID) {
    lfb_openChartsAuto = true;
    lfb_loadForm(formID);
}
function lfb_closeCharts() {
    lfb_showLoader();
    jQuery('#lfb_panelPreview').show();
    jQuery('#lfb_panelCharts').hide();
    lfb_loadForm(jQuery('#lfb_panelCharts').attr('data-formid'));
}
function lfb_loadCharts(formID) {
    jQuery('#lfb_panelCharts').attr('data-formid', formID);
    jQuery('#lfb_panelPreview').hide();
    jQuery('#lfb_panelFormsList').hide();
    jQuery('#lfb_panelLogs').hide();

    var mode = jQuery('#lfb_chartsTypeSelect').val();
    var year = jQuery('#lfb_chartsYear').val();
    var month = jQuery('#lfb_chartsMonth').val();

    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_loadCharts',
            formID: formID,
            mode: mode,
            year: year,
            yearMonth: month
        },
        success: function (rep) {
            jQuery('#lfb_panelCharts').show();
            var rowsPrice = [];
            rep = rep.split('|');
            jQuery.each(rep, function () {
                if (this.indexOf(';') > -1) {
                    var row = this.split(';');
                    if (row[2] > 0) {
                        chkSubs = true;
                    }
                    rowsPrice.push([row[0].toString(), parseFloat(row[1]), parseFloat(row[2])]);
                }
            });

            google.charts.setOnLoadCallback(function () {
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'X');
                data.addColumn('number', lfb_data.texts['oneTimePayment']);
                data.addColumn('number', lfb_data.texts['subscriptions']);

                var prefixCurrency = '';
                var suffixCurrency = '';
                if (jQuery('#lfb_formFields [name="currencyPosition"]').val() == 'right') {
                    suffixCurrency = jQuery('#lfb_formFields [name="currency"]').val();
                } else {
                    prefixCurrency = jQuery('#lfb_formFields [name="currency"]').val();
                }
                var decimalSymbol = jQuery('#lfb_formFields [name="decimalsSeparator"]').val();
                var thousandsSeparator = jQuery('#lfb_formFields [name="thousandsSeparator"]').val();
                var millionSeparator = jQuery('#lfb_formFields [name="millionSeparator"]').val();
                if (thousandsSeparator == '.') {
                    thousandsSeparator = ' ';
                }
                var columnFormat = prefixCurrency + '###' + millionSeparator + '###' + thousandsSeparator + '###' + decimalSymbol + '00' + suffixCurrency;

                var formatter = new google.visualization.NumberFormat({
                    prefix: prefixCurrency,
                    suffix: suffixCurrency,
                });

                var options = {
                    hAxis: {
                        title: lfb_data.texts['months'],
                    },
                    vAxis: {
                        title: lfb_data.texts['amountOrders'],
                        format: columnFormat,
                        viewWindow: {
                            min: 0
                        }
                    },
                    legend: {position: 'bottom'},
                    backgroundColor: '#FFFFFF',
                    colors: ['#16a085', '#e67e22', '#95a5a6', '#34495e'],
                    width: jQuery('#lfb_charts').parent().width(),
                    height: 550
                };
                data.addRows(rowsPrice);

                formatter.format(data, 1);
                formatter.format(data, 2);
                lfb_currentChartsOptions = options;
                lfb_currentChartsData = data;

                var chart = new google.visualization.LineChart(document.getElementById('lfb_charts'));
                lfb_currentCharts = chart;
                chart.draw(data, options);

                jQuery(window).resize(function () {
                    var data = lfb_currentChartsData;
                    var options = lfb_currentChartsOptions;
                    options.width = jQuery('#lfb_charts').parent().width();
                    lfb_currentCharts.draw(data, options);
                });
                jQuery('#lfb_loader').fadeOut();

            });
        }
    });

}
function lfb_loadLogs(formID) {
    lfb_showLoader();
    jQuery('#lfb_panelLogs').attr('data-formid', formID);
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_loadLogs',
            formID: formID
        },
        success: function (rep) {
            if (jQuery('#lfb_logsTable').closest('.dataTables_wrapper').length > 0) {
                lfb_logsTable.destroy();
            }
            jQuery('#lfb_logsTable tbody').html(rep);
            lfb_logsTable = jQuery('#lfb_logsTable').DataTable({
                'ordering': false,
                'language': {
                    'search': lfb_data.texts['search'],
                    'infoFiltered': lfb_data.texts['filteredFrom'],
                    'zeroRecords': lfb_data.texts['noRecords'],
                    'infoEmpty': '',
                    'info': lfb_data.texts['showingPage'],
                    'lengthMenu': lfb_data.texts['display'] + ' _MENU_',
                    'paginate': {
                        'first': '<span class="glyphicon glyphicon-fast-backward"></span>',
                        'previous': '<span class="glyphicon glyphicon-step-backward"></span>',
                        'next': '<span class="glyphicon glyphicon-step-forward"></span>',
                        'last': '<span class="glyphicon glyphicon-fast-forward"></span>'
                    }
                }
            });
            jQuery('#lfb_logsTable').wrap('<div class="table-responsive"></div>');
            jQuery('#lfb_logsTable [name="tableSelector"]').attr('checked', 'checked');
            jQuery('#lfb_logsTable [name="tableSelector"]').on('change', function () {
                if (jQuery('#lfb_logsTable [name="tableSelector"]:checked').length == 0) {
                    jQuery('#lfb_btnExportOrdersSelection,#lfb_btnDeleteOrdersSelection').attr('disabled', 'disabled');
                } else {
                    jQuery('#lfb_btnExportOrdersSelection,#lfb_btnDeleteOrdersSelection').removeAttr('disabled');
                }
            });
            if (jQuery('#lfb_logsTable [name="tableSelector"]:checked').length == 0) {
                jQuery('#lfb_btnExportOrdersSelection,#lfb_btnDeleteOrdersSelection').attr('disabled', 'disabled');
            } else {
                jQuery('#lfb_btnExportOrdersSelection,#lfb_btnDeleteOrdersSelection').removeAttr('disabled');
            }
            jQuery('#lfb_logsTable thead th:last').css({
                width: 238
            });
            jQuery('#lfb_panelPreview').hide();
            jQuery('#lfb_panelFormsList').hide();
            jQuery('#lfb_panelCharts').hide();
            jQuery('#lfb_panelLogs').show();
            jQuery('#lfb_logsTable tbody [data-toggle="tooltip"]').b_tooltip();
            jQuery('#lfb_loader').fadeOut();
        }
    });
}
function lfb_loadLog(logID, modeEdit) {
    lfb_currentLogID = logID;
    lfb_orderModified = false;
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_loadLog',
            logID: logID
        },
        success: function (rep) {
            jQuery('#lfb_winLog').find('.lfb_logContainer').html(rep);


            jQuery('#lfb_winNewTotal [name="lfb_modifyTotalField"]').val(jQuery('#lfb_winLog').find('.lfb_logContainer #lfb_logTotal').html());
            jQuery('#lfb_winLog').find('.lfb_logContainer #lfb_logTotal').remove();
            jQuery('#lfb_winNewTotal [name="lfb_modifySubTotalField"]').val(jQuery('#lfb_winLog').find('.lfb_logContainer #lfb_logSubTotal').html());
            jQuery('#lfb_winLog').find('.lfb_logContainer #lfb_logSubTotal').remove();
            lfb_currentLogCurrency = jQuery('#lfb_winLog').find('.lfb_logContainer #lfb_logCurrency').html();
            lfb_currentLogCurrencyPosition = jQuery('#lfb_winLog').find('.lfb_logContainer #lfb_logCurrencyPosition').html();

            lfb_currentLogDecSep = jQuery('#lfb_winLog').find('.lfb_logContainer #lfb_logDecSep').html();
            lfb_currentLogThousSep = jQuery('#lfb_winLog').find('.lfb_logContainer #lfb_logThousSep').html();
            lfb_currentLogMilSep = jQuery('#lfb_winLog').find('.lfb_logContainer #lfb_logMilSep').html();
            lfb_currentLogSubTxt = jQuery('#lfb_winLog').find('.lfb_logContainer #lfb_logSubTxt').html();
            lfb_currentLogUseSub = jQuery('#lfb_winLog').find('.lfb_logContainer #lfb_currentLogUseSub').html();
            lfb_currentLogIsPaid = jQuery('#lfb_winLog').find('.lfb_logContainer #lfb_currentLogIsPaid').html();
            lfb_currentLogCanPay = jQuery('#lfb_winLog').find('.lfb_logContainer #lfb_logCanPay').html();
            jQuery('#lfb_winLog').find('#lfb_logDecSep').remove();
            jQuery('#lfb_winLog').find('#lfb_logThousSep').remove();
            jQuery('#lfb_winLog').find('#lfb_logMilSep').remove();
            jQuery('#lfb_winLog').find('#lfb_logSubTxt').remove();
            jQuery('#lfb_winLog').find('#lfb_currentLogIsPaid').remove();
            jQuery('#lfb_winLog').find('#lfb_currentLogUseSub').remove();
            jQuery('#lfb_winLog').find('#lfb_logCurrency').remove();
            jQuery('#lfb_winLog').find('#lfb_logCurrencyPosition').remove();
            jQuery('#lfb_winLog').find('#lfb_logCanPay').remove();

            jQuery('#lfb_editorLog').code(jQuery('#lfb_winLog').find('.lfb_logContainer').html());
            jQuery('#lfb_winLog .lfb_logEditorContainer .panel-body *[bgcolor]').each(function () {
                jQuery(this).css({
                    backgroundColor: jQuery(this).attr('bgcolor')
                });
            });

            if (lfb_currentLogCanPay == 1) {
                jQuery('#lfb_winSendOrberByEmail [name="addPaymentLink"]').closest('.form-group').slideDown();
            } else {
                jQuery('#lfb_winSendOrberByEmail [name="addPaymentLink"]').closest('.form-group').slideUp();
                jQuery('#lfb_winSendOrberByEmail [name="addPaymentLink"]').parent().bootstrapSwitch('setState', false);
            }

            jQuery('#lfb_winLog .lfb_logEditorContainer .panel-body').click(function () {

            });
            jQuery('#lfb_winLog').css({
                backgroundColor: jQuery('body').css('background-color')
            });
            jQuery('#lfb_winLog .lfb_logEditorContainer .panel-body *[color]').each(function () {
                jQuery(this).css({
                    color: jQuery(this).attr('bgcolor')
                })
            });
            jQuery('.lfb_logEditorContainer .note-editable').keyup(function () {
                jQuery('.lfb_logEditorContainer .note-editable table tbody tr').find('th,td').each(function () {
                    if (jQuery(this).children('span').length == 0) {
                        var $span = jQuery('<span></span>');
                        jQuery(this).append($span);

                        $span.bind('mousedown.ui-disableSelection selectstart.ui-disableSelection', function (e) {
                            e.stopImmediatePropagation();
                        });
                    }
                    if (jQuery(this).children().eq(0).is('br')) {
                        jQuery(this).children().eq(0).remove();
                    }
                });
            });

            jQuery('#lfb_winLog').find('.lfb_logContainer [bgcolor]').each(function () {
                jQuery(this).css({
                    backgroundColor: jQuery(this).attr('bgcolor')
                });
            });
            var userEmail = jQuery('#lfb_logsTable tr[data-logid="' + logID + '"]').attr('data-useremail');
            jQuery('#lfb_winSendOrberByEmail [name="recipients"]').val(userEmail);
            jQuery('#lfb_winLog .lfb_logContainer').show();
            jQuery('#lfb_winLog .lfb_logEditorContainer').hide();
            jQuery('#lfb_winLog').fadeIn();
            if (modeEdit) {
                lfb_editLog();
            }
        }
    });
}
function lfb_openWinSendOrderEmail() {
    if (lfb_orderModified) {
        jQuery('#lfb_winSaveBeforeSendOrder').modal('show');
    } else {
        jQuery('#lfb_winLog .lfb_logContainer').show();
        jQuery('#lfb_winLog .lfb_logEditorContainer').hide();
        jQuery('#lfb_winSendOrberByEmail').modal('show');
        jQuery('#lfb_winSendOrberByEmail [name="generatePdf"]').parent().bootstrapSwitch('setState', false);
    }
}
function lfb_editLog() {
    if (!jQuery('.lfb_logEditorContainer .note-editable table tbody').is('ui-sortable')) {
        jQuery('.lfb_logEditorContainer .note-editable table tbody').sortable({
            helper: function (e, tr) {
                var $originals = tr.children();
                var $helper = tr.clone();
                $helper.children().each(function (index)
                {
                    jQuery(this).width($originals.eq(index).width());
                });
                return $helper;
            },
            stop: function (event, ui) {

            }
        });
        jQuery('.lfb_logEditorContainer .note-editable table tbody tr').find('td>span,th>span>strong').bind('mousedown.ui-disableSelection selectstart.ui-disableSelection', function (e) {
            e.stopImmediatePropagation();
        });
    }


    jQuery('.lfb_logEditorContainer .note-editable table').each(function () {
        if (jQuery(this).find('th[width="103"]').length > 0) {
            lfb_logEditorSummaryTable = jQuery(this);
            jQuery(this).find('tr>th[colspan]').each(function () {
                if (jQuery(this).closest('tr').children().length == 1) {
                    lfb_logEditorStepThStyle = jQuery(this).closest('tr').children('th').children('span').find('strong').attr('style');
                }
            });
            jQuery(this).find('tr>td:not([colspan])').each(function () {
                lfb_logEditorTdStyle = jQuery(this).closest('tr').children('th').children('span').attr('style');
            });
        }
    });
    if (!lfb_logEditorSummaryTable) {
        lfb_logEditorSummaryTable = jQuery(jQuery('.lfb_logEditorContainer .note-editable table').get(0));
    }

    jQuery('#lfb_winLog .lfb_logContainer').hide();
    jQuery('#lfb_winLog .lfb_logEditorContainer').show();
    lfb_orderModified = true;

}
function lfb_orderAddRow() {
    var $trModel = false;

    lfb_logEditorSummaryTable.find('tr').each(function () {
        if (jQuery(this).children('td').length > 0 && !jQuery(this).children('td').first().is('[colspan]')) {
            $trModel = jQuery(this);
        }
    });
    var $tr = $trModel.clone();
    $tr.find('td>span').html('');
    $tr.find('td>span').bind('mousedown.ui-disableSelection selectstart.ui-disableSelection', function (e) {
        e.stopImmediatePropagation();
    });
    $tr.attr('style', lfb_logEditorTdStyle);
    lfb_logEditorSummaryTable.find('tbody').append($tr);
}
function lfb_orderAddStepRow() {
    var $trModel = false;

    lfb_logEditorSummaryTable.find('tr').each(function () {
        if (jQuery(this).children('th').length == 1 && jQuery(this).children().length == 1) {
            $trModel = jQuery(this);
        }
    });
    var $tr = $trModel.clone();
    $tr.find('th>span>strong').html('');
    $tr.find('th>span>strong').bind('mousedown.ui-disableSelection selectstart.ui-disableSelection', function (e) {
        e.stopImmediatePropagation();
    });
    lfb_logEditorSummaryTable.find('tbody').append($tr);
}
function lfb_downloadOrder() {
    lfb_showLoader();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_downloadLog',
            logID: lfb_currentLogID
        },
        success: function (rep) {
            jQuery('#lfb_loader').fadeOut();
            if (rep.indexOf('.pdf') > 0) {
                jQuery('#lfb_downloadOrderLink').attr('href', lfb_data.assetsUrl + '../uploads/' + rep);
                jQuery('#lfb_winDownloadOrder').modal('show');
            } else {
                alert(lfb_data.texts['errorExport']);
            }
        }
    });
}
function lfb_sendOrderByEmail() {
    jQuery('#lfb_winSendOrberByEmail [name="recipients"]').closest('.form-group').removeClass('has-error');
    jQuery('#lfb_winSendOrberByEmail [name="subject"]').closest('.form-group').removeClass('has-error');
    var recipients = jQuery('#lfb_winSendOrberByEmail [name="recipients"]').val();
    var subject = jQuery('#lfb_winSendOrberByEmail [name="subject"]').val();
    var error = false;
    if (recipients.length == 0) {
        error = true;
        jQuery('#lfb_winSendOrberByEmail [name="recipients"]').closest('.form-group').addClass('has-error');
    } else {
        var allRecipients = recipients.split(',');
        jQuery.each(allRecipients, function () {
            if (!lfb_checkEmail(this)) {
                error = true;
                jQuery('#lfb_winSendOrberByEmail [name="recipients"]').closest('.form-group').addClass('has-error');
            }
        });
    }
    if (subject.length == 0) {
        error = true;
        jQuery('#lfb_winSendOrberByEmail [name="subject"]').closest('.form-group').addClass('has-error');

    }
    var generatePDF = 0;
    if (jQuery('#lfb_winSendOrberByEmail [name="generatePdf"]').is(':checked')) {
        generatePDF = 1;
    }
    var addPayLink = 0;
    if (jQuery('#lfb_winSendOrberByEmail [name="addPaymentLink"]').is(':checked')) {
        addPayLink = 1;
    }
    if (!error) {
        lfb_showLoader();
        jQuery('#lfb_winSendOrberByEmail').modal('hide');
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_sendOrderByEmail',
                logID: lfb_currentLogID,
                recipients: recipients,
                subject: subject,
                generatePDF: generatePDF,
                addPayLink: addPayLink
            },
            success: function () {
                jQuery('#lfb_loader').fadeOut();

            }
        });
    }

}
function lfb_saveLog(mustOpenWinSend) {
    lfb_orderModified = false;
    lfb_showLoader();
    jQuery('.lfb_logEditorContainer .note-editable table tbody tr').find('td>span,th>span').each(function () {
        jQuery(this).css('display', 'inline-block');
    });
    jQuery('.lfb_logEditorContainer .note-editable *[class]').removeAttr('class');
    jQuery('.lfb_logEditorContainer .note-editable table').each(function () {
        if (!jQuery(this).is('[width]')) {
            jQuery(this).attr('width', '668');
            jQuery(this).css('width', '100%');
        }
    });
    jQuery('.lfb_logEditorContainer .note-editable table').find('td,th').each(function () {
        var width = parseInt((jQuery(this).width() * 100) / jQuery(this).closest('table').width());
        jQuery(this).css('width', parseInt(width) + '%');
        width = parseInt((width * 668) / 100);
        jQuery(this).attr('width', width);
    });

    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_saveLog',
            logID: lfb_currentLogID,
            formID: lfb_currentFormID,
            content: jQuery('#lfb_editorLog').code(),
            total: lfb_currentLogTotal,
            totalSub: lfb_currentLogSubTotal
        },
        success: function () {
            jQuery('#lfb_winLog').find('.lfb_logContainer').html(jQuery('#lfb_editorLog').code());
            jQuery('#lfb_winLog .lfb_logContainer').show();
            jQuery('#lfb_winLog .lfb_logEditorContainer').hide();
            jQuery('#lfb_loader').fadeOut();
            if (mustOpenWinSend) {
                lfb_openWinSendOrderEmail();
            }
        }
    });
}
function lfb_closeLog() {
    jQuery('#lfb_winLog').fadeOut();

}
function lfb_removeLog(logID, formID) {
    jQuery('#lfb_winDeleteOrder').attr('data-logid', logID);
    jQuery('#lfb_winDeleteOrder').attr('data-formid', formID);
    jQuery('#lfb_winDeleteOrder').modal('show');

}
function lfb_confirmRemoveLog() {
    var logID = jQuery('#lfb_winDeleteOrder').attr('data-logid');
    var formID = jQuery('#lfb_winDeleteOrder').attr('data-formid');
    var allOrders = 0;
    if (jQuery('#lfb_winDeleteOrder [name="allOrders"]').is(':checked')) {
        allOrders = 1;
    }
    jQuery('#lfb_winDeleteOrder').modal('hide');
    lfb_showLoader();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_removeLog',
            logID: logID,
            allOrders: allOrders
        },
        success: function () {
            lfb_loadLogs(formID);
        }
    });
}
function lfb_exportForms() {
    var withLogs = 0;
    var withCoupons = 0;
    if (jQuery('[name="exportLogs"]').is(':checked')) {
        withLogs = 1;
    }
    if (jQuery('[name="exportCoupons"]').is(':checked')) {
        withCoupons = 1;
    }
    lfb_showLoader();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_exportForms',
            withLogs: withLogs,
            withCoupons: withCoupons
        },
        success: function (rep) {
            jQuery('#lfb_loader').fadeOut();
            if (rep == '1') {
                jQuery('#lfb_winExport').modal('show');
            } else {
                alert(lfb_data.texts['errorExport']);
            }
        }
    });

}
function lfb_importForms() {
    lfb_showLoader();
    jQuery('#lfb_winImport').modal('hide');
    var formData = new FormData(jQuery('#lfb_winImportForm')[0]);

    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        xhr: function () {
            var myXhr = jQuery.ajaxSettings.xhr();
            return myXhr;
        },
        success: function (rep) {
            if (rep != '1') {
                jQuery('#lfb_loader').fadeOut();
                alert(lfb_data.texts['errorImport']);
            } else {
                document.location.href = document.location.href;
            }
        },
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    });
}

function lfb_editCoupon(couponID) {
    var couponCode = '';
    var useMax = 1;
    var reduction = 0;
    if (couponID > 0) {
        couponCode = jQuery('#lfb_couponsTable tbody tr[data-couponid="' + couponID + '"] td:eq(0)').html();
        useMax = jQuery('#lfb_couponsTable tbody tr[data-couponid="' + couponID + '"] td:eq(1)').html();
        reduction = jQuery('#lfb_couponsTable tbody tr[data-couponid="' + couponID + '"] td:eq(3)').html();
        reduction = reduction.substr(1, reduction.length);
        if (reduction.substr(reduction.length - 1, 1) == '%') {
            reduction = reduction.substr(0, reduction.length - 1);
        }
    }

    jQuery('#lfb_winEditCoupon .form-group').removeClass('has-error');
    jQuery('#lfb_winEditCoupon').attr('data-couponid', couponID);
    jQuery('#lfb_winEditCoupon [name="couponCode"]').val(couponCode);
    jQuery('#lfb_winEditCoupon [name="useMax"]').val(useMax);
    jQuery('#lfb_winEditCoupon [name="reduction"]').val(reduction);
    jQuery('#lfb_winEditCoupon').modal('show');
}

function lfb_removeCoupon(couponID) {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_removeCoupon',
            formID: lfb_currentFormID,
            couponID: couponID
        },
        success: function () {
            jQuery('#lfb_couponsTable tbody tr[data-couponid="' + couponID + '"]').slideUp();
            setTimeout(function () {
                jQuery('#lfb_couponsTable tbody tr[data-couponid="' + couponID + '"]').remove();
            }, 300);
        }
    });
}
function lfb_removeAllCoupons() {
    jQuery('#lfb_couponsTable tbody').html('');
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_removeAllCoupons',
            formID: lfb_currentFormID
        },
        success: function () {

        }
    });
}

function lfb_saveCoupon() {
    var couponID = jQuery('#lfb_winEditCoupon').attr('data-couponid');
    jQuery('#lfb_winEditCoupon .form-group').removeClass('has-error');

    var error = false;
    if (jQuery('#lfb_winEditCoupon [name="couponCode"]').val().length < 3) {
        error = true;
        jQuery('#lfb_winEditCoupon [name="couponCode"]').closest('.form-group').addClass('has-error');
    }
    if (!error) {
        var couponCode = jQuery('#lfb_winEditCoupon [name="couponCode"]').val();
        var useMax = jQuery('#lfb_winEditCoupon [name="useMax"]').val();
        var reduction = jQuery('#lfb_winEditCoupon [name="reduction"]').val();
        var reductionType = jQuery('#lfb_winEditCoupon [name="reductionType"]').val();
        if (reduction == "" || isNaN(reduction)) {
            reduction = 0;
        }
        if (reduction < 0) {
            reduction *= -1;
        }

        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_saveCoupon',
                formID: lfb_currentFormID,
                couponID: couponID,
                couponCode: jQuery('#lfb_winEditCoupon [name="couponCode"]').val(),
                useMax: jQuery('#lfb_winEditCoupon [name="useMax"]').val(),
                reduction: jQuery('#lfb_winEditCoupon [name="reduction"]').val(),
                reductionType: jQuery('#lfb_winEditCoupon [name="reductionType"]').val()
            },
            success: function (rep) {
                jQuery('#lfb_winEditCoupon').modal('hide');

                if (reductionType == 'percentage') {
                    reduction = '-' + reduction + '%';
                } else {
                    reduction = '-' + parseFloat(reduction).toFixed(2);
                }

                if (couponID == 0) {
                    var tdAction = '<td style="text-align:right;">' +
                            '<a href="javascript:" onclick="lfb_editCoupon(' + rep + ');" class="btn btn-primary btn-circle"><span class="glyphicon glyphicon-pencil"></span></a>' +
                            '<a href="javascript:" onclick="lfb_removeCoupon(' + rep + ');" class="btn btn-danger btn-circle"><span class="glyphicon glyphicon-trash"></span></a>' +
                            '</td>';
                    jQuery('#lfb_couponsTable tbody').append('<tr data-couponid="' + rep + '"><td>' + couponCode + '</td><td>' + useMax + '</td><td>0</td><td>' + reduction + '</td>' + tdAction + '</tr>');

                } else {
                    jQuery('#lfb_couponsTable tbody tr[data-couponid="' + couponID + '"] td:eq(0)').html(couponCode);
                    jQuery('#lfb_couponsTable tbody tr[data-couponid="' + couponID + '"] td:eq(1)').html(useMax);
                    jQuery('#lfb_couponsTable tbody tr[data-couponid="' + couponID + '"] td:eq(3)').html(reduction);
                }

            }
        });
    }
}
function lfb_addDateDiffValue(btn) {
    jQuery('#lfb_calculationDatesDiffBubble').find('select').val('currentDate');
    jQuery('#lfb_calculationDatesDiffBubble').css({
        left: jQuery(btn).offset().left,
        top: jQuery(btn).offset().top + 10
    });
    jQuery('#lfb_calculationValueBubble').fadeOut();
    jQuery('#lfb_calculationDatesDiffBubble').fadeIn();
    jQuery('#lfb_calculationDatesDiffBubble').addClass('lfb_hover');
    lfb_updateCalculationsDates();
}
function lfb_updateCalculationsDates() {
    jQuery('#lfb_calculationDatesDiffBubble select option:not([data-static])').remove();
    jQuery.each(lfb_steps, function () {
        var step = this;
        jQuery.each(step.items, function () {
            var item = this;
            if (item.type == 'datepicker') {
                var itemID = item.id;
                jQuery('#lfb_calculationDatesDiffBubble select').append('<option value="' + itemID + '" data-type="' + item.type + '">' + step.title + ' : " ' + item.title + ' "</option>');
            }
        });
    });

    jQuery.each(lfb_currentForm.fields, function () {
        var item = this;
        if (item.type == 'datepicker') {
            var itemID = item.id;
            jQuery('#lfb_calculationDatesDiffBubble select').append('<option value="' + itemID + '" data-type="' + item.type + '">' + lfb_data.texts['lastStep'] + ' : " ' + item.title + ' "</option>');
        }
    });

}
function lfb_addCalculationValue(btn) {
    jQuery('#lfb_calculationValueBubble').find('select,textarea,input').val('');
    lfb_updateCalculationsValueItems();
    jQuery('#lfb_calculationValueBubble').css({
        left: jQuery(btn).offset().left,
        top: jQuery(btn).offset().top + 10
    });
    jQuery('#lfb_calculationDatesDiffBubble').fadeOut();
    jQuery('#lfb_calculationValueBubble').fadeIn();
    jQuery('#lfb_calculationValueBubble').addClass('lfb_hover');
    lfb_updateCalculationsValueElements();
}
function lfb_updateCalculationsValueItems() {
    var $selectItem = jQuery('#lfb_calculationValueBubble select[name="itemID"]');
    $selectItem.html('');
    jQuery.each(lfb_steps, function () {
        var step = this;
        $selectItem.append('<option value="step-' + step.id + '" >' + step.title + ' :  ' + lfb_data.texts['totalQuantity'] + ' </option>');

        jQuery.each(step.items, function () {
            var item = this;

            if (item.type == 'picture' || item.type == 'checkbox' || item.type == 'numberfield' || item.type == 'select' || item.type == 'slider' || item.type == 'button'
                    || this.type == 'numberfield' || this.type == 'textfield' || this.type == 'datepicker' || this.type == 'select' || this.type == 'textarea') {
                var itemID = item.id;
                $selectItem.append('<option value="' + itemID + '" data-type="' + item.type + '" data-datetype="' + item.dateType + '">' + step.title + ' : " ' + item.title + ' "</option>');
            }

        });
    });
    jQuery.each(lfb_currentForm.fields, function () {
        var item = this;

        if (item.type == 'picture' || item.type == 'checkbox' || item.type == 'numberfield' || item.type == 'select' || item.type == 'slider' || item.type == 'button'
                || this.type == 'numberfield' || this.type == 'textfield' || this.type == 'datepicker' || this.type == 'select' || this.type == 'textarea') {
            var itemID = item.id;
            $selectItem.append('<option value="' + itemID + '" data-type="' + item.type + '" data-datetype="' + item.dateType + '">' + lfb_data.texts['lastStep'] + ' : " ' + item.title + ' "</option>');
        }
    });

    $selectItem.append('<option value="_total" data-type="totalPrice">' + lfb_data.texts['totalPrice'] + '</option>');
    $selectItem.append('<option value="_total_qt" data-type="totalQt">' + lfb_data.texts['totalQuantity'] + '</option>');
}
function lfb_updateCalculationsValueElements() {
    var $selectItem = jQuery('#lfb_calculationValueBubble select[name="itemID"]');
    var $selectElement = jQuery('#lfb_calculationValueBubble select[name="element"]');
    $selectElement.val('');
    $selectElement.find('option[value="quantity"]').hide();
    $selectElement.find('option[value=""]').show();
    if ($selectItem.val().indexOf('step-') == 0) {
        $selectElement.find('option[value="quantity"]').show();
        $selectElement.find('option[value=""]').hide();
        $selectElement.find('option[value="value"]').hide();
        $selectElement.val('quantity');
    } else {
        if ($selectItem.val() != "") {
            var selectedItemID = $selectItem.val();
            jQuery.each(lfb_currentForm.steps, function () {
                jQuery.each(this.items, function () {
                    if (this.id == selectedItemID) {
                        if (this.quantity_enabled == 1 || this.type == 'slider') {
                            $selectElement.find('option[value="quantity"]').show();
                        } else {
                            $selectElement.find('option[value="quantity"]').hide();
                        }
                        if (this.type == 'numberfield' || this.type == 'textfield' || this.type == 'datepicker' || this.type == 'select' || this.type == 'textarea') {
                            $selectElement.find('option[value="value"]').show();
                            $selectElement.find('option[value=""]').hide();
                            $selectElement.val('value');
                        } else {
                            $selectElement.find('option[value="value"]').hide();
                            $selectElement.find('option[value=""]').show();
                        }
                    }
                });
            });

            jQuery.each(lfb_currentForm.fields, function () {
                if (this.id == selectedItemID) {
                    if (this.quantity_enabled == 1 || this.type == 'slider') {
                        $selectElement.find('option[value="quantity"]').show();
                    } else {
                        $selectElement.find('option[value="quantity"]').hide();
                    }
                    if (this.type == 'numberfield' || this.type == 'textfield' || this.type == 'datepicker' || this.type == 'select' || this.type == 'textarea') {
                        $selectElement.find('option[value="value"]').show();
                        $selectElement.find('option[value=""]').hide();
                        $selectElement.val('value');
                    } else {
                        $selectElement.find('option[value="value"]').hide();
                        $selectElement.find('option[value=""]').show();
                    }

                }
            });
            if ($selectItem.val() == "_total_qt") {
                $selectElement.find('option[value="quantity"]').show();
                $selectElement.find('option[value=""]').hide();
                $selectElement.find('option[value="value"]').hide();
                $selectElement.val('quantity');
            }
        }
    }
}

function lfb_addDistanceCondition() {

    jQuery('#lfb_winDistances').fadeIn();
}

function lfb_saveCalculationValue() {
    var targetfieldName = "calculation";
    if (lfb_calculationModeQt) {
        targetfieldName = "calculationQt";
    }
    var $selectItem = jQuery('#lfb_calculationValueBubble select[name="itemID"]');
    var $selectElement = jQuery('#lfb_calculationValueBubble select[name="element"]');
    var attribute = 'price';
    if ($selectElement.val() != "") {
        attribute = $selectElement.val();
    }
    var itemTag = '[item-' + $selectItem.val() + '_' + attribute + ']';
    if ($selectItem.val() == '_total') {
        itemTag = '[total]';
    }
    if ($selectItem.val() == '_total_qt') {
        itemTag = '[total_quantity]';
    }
    if ($selectItem.val().indexOf('step-') == 0) {
        var stepID = $selectItem.val().substr(5);
        itemTag = '[step-' + stepID + '_' + attribute + ']';
    }
    var posCar = jQuery('#lfb_winItem').find('[name="' + targetfieldName + '"]').prop("selectionStart");
    var value = jQuery('#lfb_winItem').find('[name="' + targetfieldName + '"]').val();
    if (isNaN(posCar)) {
        posCar = value.length;
    }
    var newValue = value.substr(0, posCar) + ' ' + itemTag + ' ' + value.substr(posCar, value.length);

    jQuery('#lfb_winItem').find('[name="' + targetfieldName + '"]').val(newValue);
    jQuery('#lfb_calculationValueBubble').fadeOut();
}

function lfb_saveCalculationDatesDiff() {
    var targetfieldName = "calculation";
    if (lfb_calculationModeQt) {
        targetfieldName = "calculationQt";
    }
    var $startDate = jQuery('#lfb_calculationDatesDiffBubble select[name="startDate"]');
    var $endDate = jQuery('#lfb_calculationDatesDiffBubble select[name="endDate"]');
    var itemTag = '[dateDifference-' + $startDate.val() + '_' + $endDate.val() + ']';
    var posCar = jQuery('#lfb_winItem').find('[name="' + targetfieldName + '"]').prop("selectionStart");
    var value = jQuery('#lfb_winItem').find('[name="' + targetfieldName + '"]').val();
    if (isNaN(posCar)) {
        posCar = value.length;
    }
    var newValue = value.substr(0, posCar) + ' ' + itemTag + ' ' + value.substr(posCar, value.length);
    jQuery('#lfb_winItem').find('[name="' + targetfieldName + '"]').val(newValue);
    jQuery('#lfb_calculationDatesDiffBubble').fadeOut();

}

function lfb_addCalculationCondition() {
    jQuery('#lfb_winCalculationConditions #lfb_calcConditionsTable tbody').html('');
    jQuery("body,html").animate({
        scrollTop: 0
    }, 200);
    jQuery('#lfb_winCalculationConditions').fadeIn();
}
function lfb_calcConditionSave() {
    var targetfieldName = "calculation";
    if (lfb_calculationModeQt) {
        targetfieldName = "calculationQt";
    }

    var conditionString = 'if(';
    if (jQuery('#lfb_winItem').find('[name="' + targetfieldName + '"]').val().length > 0) {
        conditionString = "\n" + conditionString;
    }
    var operator = '&&';
    if (jQuery('#lfb_calcOperator').val() == 'OR') {
        operator = '||';
    }
    jQuery('#lfb_winCalculationConditions #lfb_calcConditionsTable tbody tr.lfb_conditionItem').each(function () {
        var tr = this;
        var itemID = jQuery(tr).find('.lfb_conditionSelect').val();
        if (itemID != '_total' && itemID != '_total_qt') {
            itemID = 'item-' + itemID.substr(itemID.indexOf('_') + 1, itemID.length);
        }
        if (jQuery(tr).find('.lfb_conditionoperatorSelect ').val().substr(0, 2) == 'Qt') {
            conditionString += '([' + itemID + '_quantity]';
            if (jQuery(tr).find('.lfb_conditionoperatorSelect ').val() == 'QtSuperior') {
                conditionString += ' >';
            } else if (jQuery(tr).find('.lfb_conditionoperatorSelect ').val() == 'QtInferior') {
                conditionString += ' <';
            } else if (jQuery(tr).find('.lfb_conditionoperatorSelect ').val() == 'QtDifferent') {
                conditionString += ' !=';
            } else {
                conditionString += ' ==';
            }
            conditionString += jQuery(tr).find('.lfb_conditionValue').val();
        } else if (jQuery(tr).find('.lfb_conditionoperatorSelect ').val().substr(0, 5) == 'Price') {
            conditionString += '([' + itemID + '_price]';
            if (jQuery(tr).find('.lfb_conditionoperatorSelect ').val() == 'PriceSuperior') {
                conditionString += ' >';
            } else if (jQuery(tr).find('.lfb_conditionoperatorSelect ').val() == 'PriceInferior') {
                conditionString += ' <';
            } else if (jQuery(tr).find('.lfb_conditionoperatorSelect ').val() == 'PriceDifferent') {
                conditionString += ' !=';
            } else {
                conditionString += ' ==';
            }
            conditionString += jQuery(tr).find('.lfb_conditionValue').val();
        } else if (jQuery(tr).find('.lfb_conditionoperatorSelect ').val() == 'clicked') {
            conditionString += '([' + itemID + '_isChecked]';
        } else if (jQuery(tr).find('.lfb_conditionoperatorSelect ').val() == 'unclicked') {
            conditionString += '([' + itemID + '_isUnchecked]';
        } else if (jQuery(tr).find('.lfb_conditionoperatorSelect ').val() == 'superior') {
            if (itemID == '_total') {
                conditionString += '([total]';
                conditionString += ' >';
                conditionString += jQuery(tr).find('.lfb_conditionValue').val();
            } else if (itemID == '_total_qt') {
                conditionString += '([total_quantity]';
                conditionString += ' >';
                conditionString += jQuery(tr).find('.lfb_conditionValue').val();
            } else if (jQuery(tr).find('.lfb_conditionSelect option[value="' + jQuery(tr).find('.lfb_conditionSelect').val() + '"]').attr('data-type') == 'select') {
                conditionString += '([' + itemID + '_value]';
                conditionString += ' >';
                conditionString += '\'' + jQuery(tr).find('.lfb_conditionValue').val() + '\'';
            } else if (jQuery(tr).find('.lfb_conditionSelect option[value="' + jQuery(tr).find('.lfb_conditionSelect').val() + '"]').attr('data-type') == 'numberfield') {
                conditionString += '([' + itemID + '_value]';
                conditionString += ' >';
                conditionString += jQuery(tr).find('.lfb_conditionValue').val();
            } else {
                conditionString += '([' + itemID + '_date]';
                conditionString += ' >';
                conditionString += "'" + jQuery(tr).find('.lfb_conditionValue').val() + "'";
            }
        } else if (jQuery(tr).find('.lfb_conditionoperatorSelect').val() == 'inferior') {
            if (itemID == '_total') {
                conditionString += '([total]';
                conditionString += ' <';
                conditionString += jQuery(tr).find('.lfb_conditionValue').val();
            } else if (itemID == '_total_qt') {
                conditionString += '([total_quantity]';
                conditionString += ' <';
                conditionString += jQuery(tr).find('.lfb_conditionValue').val();
            } else if (jQuery(tr).find('.lfb_conditionSelect option[value="' + jQuery(tr).find('.lfb_conditionSelect').val() + '"]').attr('data-type') == 'select') {
                conditionString += '([' + itemID + '_value]';
                conditionString += ' <';
                conditionString += '\'' + jQuery(tr).find('.lfb_conditionValue').val() + '\'';
            } else if (jQuery(tr).find('.lfb_conditionSelect option[value="' + jQuery(tr).find('.lfb_conditionSelect').val() + '"]').attr('data-type') == 'numberfield') {
                conditionString += '([' + itemID + '_value]';
                conditionString += ' <';
                conditionString += jQuery(tr).find('.lfb_conditionValue').val();
            } else {
                conditionString += '([' + itemID + '_date]';
                conditionString += ' <';
                conditionString += '"' + jQuery(tr).find('.lfb_conditionValue').val() + '"';
            }
        } else if (jQuery(tr).find('.lfb_conditionoperatorSelect').val() == 'equal') {
            if (itemID == '_total') {
                conditionString += '([total]';
                conditionString += ' ==';
                conditionString += jQuery(tr).find('.lfb_conditionValue').val();
            } else if (itemID == '_total_qt') {
                conditionString += '([total_quantity]';
                conditionString += ' ==';
                conditionString += jQuery(tr).find('.lfb_conditionValue').val();
            } else if (jQuery(tr).find('.lfb_conditionSelect option[value="' + jQuery(tr).find('.lfb_conditionSelect').val() + '"]').attr('data-type') == 'select') {
                conditionString += '([' + itemID + '_value]';
                conditionString += ' ==';
                conditionString += '\'' + jQuery(tr).find('.lfb_conditionValue').val() + '\'';
            } else if (jQuery(tr).find('.lfb_conditionSelect option[value="' + jQuery(tr).find('.lfb_conditionSelect').val() + '"]').attr('data-type') == 'numberfield') {
                conditionString += '([' + itemID + '_value]';
                conditionString += ' ==';
                conditionString += jQuery(tr).find('.lfb_conditionValue').val();
            } else {
                conditionString += '([' + itemID + '_date]';
                conditionString += ' ==';
                conditionString += '\'' + jQuery(tr).find('.lfb_conditionValue').val() + '\'';
            }
        } else if (jQuery(tr).find('.lfb_conditionoperatorSelect').val() == 'different') {
            if (itemID == '_total') {
                conditionString += '([total]';
                conditionString += ' !=';
                conditionString += jQuery(tr).find('.lfb_conditionValue').val();
            } else if (itemID == '_total_qt') {
                conditionString += '([total_quantity]';
                conditionString += ' !=';
                conditionString += jQuery(tr).find('.lfb_conditionValue').val();
            } else if (jQuery(tr).find('.lfb_conditionSelect option[value="' + jQuery(tr).find('.lfb_conditionSelect').val() + '"]').attr('data-type') == 'select') {
                conditionString += '([' + itemID + '_value]';
                conditionString += ' !=';
                conditionString += '\'' + jQuery(tr).find('.lfb_conditionValue').val() + '\'';
            } else if (jQuery(tr).find('.lfb_conditionSelect option[value="' + jQuery(tr).find('.lfb_conditionSelect').val() + '"]').attr('data-type') == 'numberfield') {
                conditionString += '([' + itemID + '_value]';
                conditionString += ' !=';
                conditionString += jQuery(tr).find('.lfb_conditionValue').val();
            } else {
                conditionString += '([' + itemID + '_date]';
                conditionString += ' !=';
                conditionString += '\'' + jQuery(tr).find('.lfb_conditionValue').val() + '\'';
            }
        } else if (jQuery(tr).find('.lfb_conditionoperatorSelect').val() == 'filled') {
            conditionString += '([' + itemID + '_isFilled]';
        }
        conditionString += ')' + operator;
    });
    conditionString = conditionString.substr(0, conditionString.length - 2);
    conditionString += ') {' + "\n" + "\n" + '}';
    var posCar = jQuery('#lfb_winItem').find('[name="' + targetfieldName + '"]').prop("selectionStart");
    var value = jQuery('#lfb_winItem').find('[name="' + targetfieldName + '"]').val();
    if (isNaN(posCar)) {
        posCar == value.length;
    }
    var newValue = value.substr(0, posCar) + ' ' + conditionString + ' ' + value.substr(posCar, value.length);

    jQuery('#lfb_winItem').find('[name="' + targetfieldName + '"]').val(newValue);
    jQuery('#lfb_winCalculationConditions').fadeOut();
}

function lfb_addEmailValue(mode) {
    jQuery('#lfb_emailValueBubble').find('select,textarea,input').val('');
    lfb_updateEmailValueItems();
    var target = '#lfb_btnAddEmailValue';
    jQuery('#lfb_emailValueBubble').attr('data-customermode', mode);
    if (mode == 1) {
        target = '#lfb_btnAddEmailValueCustomer';
    } else if (mode == 2) {
        target = '#lfb_btnAddRichtextValue';
    } else if (mode == 3) {
        target = '#lfb_btnAddPdfValue';
    } else if (mode == 4) {
        target = '#lfb_btnAddPdfValueCustomer';
    }
    jQuery('#lfb_emailValueBubble').css({
        left: jQuery(target).offset().left - 80,
        top: jQuery(target).offset().top + 28
    });
    jQuery('#lfb_emailValueBubble').fadeIn();
    jQuery('#lfb_emailValueBubble').addClass('lfb_hover');
    lfb_updateEmailValueElements();
}
function lfb_updateEmailValueElements() {
    var $selectItem = jQuery('#lfb_emailValueBubble select[name="itemID"]');
    var $selectElement = jQuery('#lfb_emailValueBubble select[name="element"]');
    $selectElement.val('');
    $selectElement.find('option[value="quantity"]').hide();
    $selectElement.find('option[value=""]').show();
    if ($selectItem.val() != "") {
        var selectedItemID = $selectItem.val();
        jQuery.each(lfb_currentForm.steps, function () {
            jQuery.each(this.items, function () {
                if (this.id == selectedItemID) {
                    if (this.quantity_enabled == 1 || this.type == 'slider') {
                        $selectElement.find('option[value="quantity"]').show();
                    } else {
                        $selectElement.find('option[value="quantity"]').hide();
                    }

                    if (this.type == 'textfield' || this.type == 'numberfield' || this.type == 'textarea' || this.type == 'select' || this.type == 'colorpicker' || this.type == 'datepicker' || this.type == 'timepicker' || this.type == 'filefield') {
                        $selectElement.find('option[value="value"]').show();
                        $selectElement.find('option[value=""]').hide();
                        $selectElement.val('value');
                    } else {
                        $selectElement.find('option[value="value"]').hide();
                        $selectElement.find('option[value=""]').show();
                    }
                }
            });
        });

        jQuery.each(lfb_currentForm.fields, function () {
            if (this.id == selectedItemID) {
                if (this.quantity_enabled == 1 || this.type == 'slider') {
                    $selectElement.find('option[value="quantity"]').show();
                } else {
                    $selectElement.find('option[value="quantity"]').hide();
                }
                if (this.type == 'textfield' || this.type == 'textarea' || this.type == 'select' || this.type == 'colorpicker' || this.type == 'datepicker' || this.type == 'timepicker' || this.type == 'filefield') {
                    $selectElement.find('option[value="value"]').show();
                    $selectElement.find('option[value=""]').hide();
                    $selectElement.val('value');
                } else {
                    $selectElement.find('option[value="value"]').hide();
                    $selectElement.find('option[value=""]').show();
                }

            }
        });
        if ($selectItem.val() == "_total_qt") {
            $selectElement.find('option[value="quantity"]').show();
            $selectElement.find('option[value=""]').hide();
            $selectElement.find('option[value="title"]').hide();
            $selectElement.val('quantity');
        } else if ($selectItem.val() == "_total") {
            $selectElement.find('option[value="quantity"]').hide();
            $selectElement.find('option[value=""]').show();
            $selectElement.find('option[value="title"]').hide();
            $selectElement.val('');
        } else {
            $selectElement.find('option[value="title"]').show();
        }
    }
}
function lfb_updateEmailValueItems() {
    var $selectItem = jQuery('#lfb_emailValueBubble select[name="itemID"]');
    $selectItem.html('');
    jQuery.each(lfb_steps, function () {
        var step = this;
        jQuery.each(step.items, function () {
            var item = this;
            if (this.type == 'picture' || this.type == 'checkbox' || this.type == 'numberfield' || this.type == 'select' || this.type == 'slider' || this.type == 'button' || this.type == 'filefield'
                    || this.type == 'textfield' || this.type == 'textarea' || this.type == 'select' || this.type == 'colorpicker' || this.type == 'datepicker' || this.type == 'timepicker') {
                var itemID = item.id;
                $selectItem.append('<option value="' + itemID + '" data-type="' + item.type + '" data-datetype="' + item.dateType + '">' + step.title + ' : " ' + item.title + ' "</option>');
            }
        });
    });
    jQuery.each(lfb_currentForm.fields, function () {
        var item = this;
        if (this.type == 'picture' || this.type == 'checkbox' || this.type == 'numberfield' || this.type == 'select' || this.type == 'slider' || this.type == 'button' || this.type == 'filefield'
                || this.type == 'textfield' || this.type == 'textarea' || this.type == 'select' || this.type == 'colorpicker' || this.type == 'datepicker' || this.type == 'timepicker') {
            var itemID = item.id;
            $selectItem.append('<option value="' + itemID + '" data-type="' + item.type + '" data-datetype="' + item.dateType + '">' + lfb_data.texts['lastStep'] + ' : " ' + item.title + ' "</option>');
        }
    });

    $selectItem.append('<option value="_total" data-static="1" data-type="totalPrice" data-variable="pricefield">' + lfb_data.texts['totalPrice'] + '</option>');
    $selectItem.append('<option value="_total_qt" data-static="1" data-type="totalQt" data-variable="numberfield">' + lfb_data.texts['totalQuantity'] + '</option>');
}

function lfb_saveEmailValue() {
    var $selectItem = jQuery('#lfb_emailValueBubble select[name="itemID"]');
    var $selectElement = jQuery('#lfb_emailValueBubble select[name="element"]');
    var target = '#email_adminContent';
    if (jQuery('#lfb_emailValueBubble').attr('data-customermode') == '1') {
        target = '#email_userContent';
    } else if (jQuery('#lfb_emailValueBubble').attr('data-customermode') == '2') {
        target = '#lfb_itemRichText';
    } else if (jQuery('#lfb_emailValueBubble').attr('data-customermode') == '3') {
        target = '#pdf_adminContent';
    } else if (jQuery('#lfb_emailValueBubble').attr('data-customermode') == '4') {
        target = '#pdf_userContent';
    }
    var attribute = jQuery('#lfb_emailValueBubble select[name="element"]').val();
    if (attribute == '') {
        attribute = 'price';
    }
    if ($selectElement.val() != "") {
        attribute = $selectElement.val();
    }
    var itemTag = '[item-' + $selectItem.val() + '_' + attribute + ']';
    if ($selectItem.val() == '_total') {
        itemTag = '[total]';
    }
    if ($selectItem.val() == '_total_qt') {
        itemTag = '[total_quantity]';
    }
    jQuery(target).summernote('editor.focus');
    jQuery(target).summernote('editor.insertText', itemTag);

    jQuery('#lfb_emailValueBubble').fadeOut();
}

function lfb_exportLogs() {
    var formID = jQuery('#lfb_panelLogs').attr('data-formid');
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_exportLogs',
            formID: formID
        },
        success: function (rep) {
            if (rep != 'error') {
                window.open(
                        rep,
                        '_blank'
                        );
            }
        }
    });
}

function lfb_showLayerConditionSave() {
    var conditions = new Array();
    jQuery("#lfb_showLayerConditionsTable .lfb_conditionItem").each(function () {
        var condValue = lfb_getConditionValue(jQuery(this).find(".lfb_conditionValue"));

        if (condValue) {
            condValue = condValue.replace(/\'/g, '`');
        }
        conditions.push({
            interaction: jQuery(this).find(".lfb_conditionSelect").val(),
            action: jQuery(this).find(".lfb_conditionoperatorSelect").val(),
            value: condValue
        });
    });
    lfb_currentLayerTr.find('[name="showConditions"]').val(JSON.stringify(conditions));
    lfb_currentLayerTr.find('[name="showConditionsOperator"]').val(jQuery("#lfb_showLayerOperator").val());
    jQuery("#lfb_winLayerShowConditions").fadeOut();
}

function lfb_editLayerConditions(btn) {
    lfb_currentLayerTr = jQuery(btn).closest('tr');
    jQuery("#lfb_winLayerShowConditions #lfb_showStepOperator").val(jQuery("#lfb_winStep").find('[name="showConditionsOperator"]').val());
    jQuery("#lfb_winLayerShowConditions #lfb_showLayerConditionsTable tbody").html("");

    if (lfb_currentLayerTr.find('[name="showConditions"]').val() != '') {
        var conditions = JSON.parse(lfb_currentLayerTr.find('[name="showConditions"]').val());
        jQuery.each(conditions, function () {
            lfb_addShowLayerInteraction(this);
        });
    }

    jQuery("#lfb_winLayerShowConditions").fadeIn();
    setTimeout(function () {
        jQuery("#wpwrap").css({
            height: jQuery("#lfb_bootstraped").height() + 48
        });
    }, 300);
    jQuery("body,html").animate({
        scrollTop: 0
    }, 200);
}
function lfb_editShowStepConditions() {
    jQuery("#lfb_winShowStepConditions #lfb_showLayerOperator").val(jQuery("#lfb_winStep").find('[name="showConditionsOperator"]').val());
    jQuery("#lfb_winShowStepConditions #lfb_showStepConditionsTable tbody").html("");
    if (jQuery("#lfb_winStep").find('[name="showConditions"]').val() != '') {
        try {
            var conditions = JSON.parse(jQuery("#lfb_winStep").find('[name="showConditions"]').val());
            jQuery.each(conditions, function () {
                lfb_addShowStepInteraction(this);
            });
        } catch (e) {
        }
    }
    jQuery("#lfb_winShowStepConditions").fadeIn();
    setTimeout(function () {
        jQuery("#wpwrap").css({
            height: jQuery("#lfb_bootstraped").height() + 48
        });
    }, 300);
    jQuery("body,html").animate({
        scrollTop: 0
    }, 200);
}

function lfb_editShowConditions() {
    jQuery("#lfb_winShowConditions #lfb_showOperator").val(jQuery("#lfb_winItem").find('[name="showConditionsOperator"]').val());
    jQuery("#lfb_winShowConditions #lfb_showConditionsTable tbody").html("");
    if (jQuery("#lfb_winItem").find('[name="showConditions"]').val() != '') {
        try {
            var conditions = JSON.parse(jQuery("#lfb_winItem").find('[name="showConditions"]').val());
            jQuery.each(conditions, function () {
                lfb_addShowInteraction(this);
            });
        } catch (e) {
        }
    }
    jQuery("#lfb_winShowConditions").fadeIn();
    setTimeout(function () {
        jQuery("#wpwrap").css({
            height: jQuery("#lfb_bootstraped").height() + 48
        });
    }, 300);
    jQuery("body,html").animate({
        scrollTop: 0
    }, 200);
}

function lfb_showConditionSave() {
    var conditions = new Array();
    jQuery("#lfb_showConditionsTable .lfb_conditionItem").each(function () {
        var condValue = lfb_getConditionValue(jQuery(this).find(".lfb_conditionValue"));

        if (condValue) {
            condValue = condValue.replace(/\'/g, '`');
        }
        conditions.push({
            interaction: jQuery(this).find(".lfb_conditionSelect").val(),
            action: jQuery(this).find(".lfb_conditionoperatorSelect").val(),
            value: condValue
        });
    });
    jQuery("#lfb_winItem").find('[name="showConditionsOperator"]').val(jQuery("#lfb_showOperator").val());
    jQuery("#lfb_winItem").find('[name="showConditions"]').val(JSON.stringify(conditions));
    jQuery("#lfb_winShowConditions").fadeOut();
}
function lfb_showStepConditionSave() {
    var conditions = new Array();
    jQuery("#lfb_showStepConditionsTable .lfb_conditionItem").each(function () {
        var condValue = lfb_getConditionValue(jQuery(this).find(".lfb_conditionValue"));
        if (condValue) {
            condValue = condValue.replace(/\'/g, '`');
        }
        conditions.push({
            interaction: jQuery(this).find(".lfb_conditionSelect").val(),
            action: jQuery(this).find(".lfb_conditionoperatorSelect").val(),
            value: condValue
        });
    });
    jQuery("#lfb_winStep").find('[name="showConditionsOperator"]').val(jQuery("#lfb_showStepOperator").val());
    jQuery("#lfb_winStep").find('[name="showConditions"]').val(JSON.stringify(conditions));
    jQuery("#lfb_winShowStepConditions").fadeOut();
}
function lfb_selectPre(input) {
    jQuery(input).select();
}
function lfb_removeRedirection(id) {
    jQuery('#lfb_redirsTable tr[data-id="' + id + '"]').remove();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_removeRedirection',
            id: id,
            formID: lfb_currentFormID
        }
    });
}


function lfb_editRedirection(id, mode) {
    lfb_currentRedirEdit = id;
    jQuery("#lfb_winRedirection #lfb_redirOperator").val(jQuery("#lfb_winItem").find('[name="showConditionsOperator"]').val());
    jQuery("#lfb_winRedirection #lfb_redirConditionsTable tbody").html("");
    jQuery("#lfb_winRedirection #lfb_redirUrl").val("");

    if (id > 0) {
        jQuery.each(lfb_currentForm.redirections, function () {
            if (this.id == id) {
                jQuery("#lfb_winRedirection #lfb_redirUrl").val(this.url);
                var conditions = this.conditions.replace(/\\"/g, '"');
                conditions = JSON.parse(conditions);
                jQuery.each(conditions, function () {
                    lfb_addRedirInteraction(this);
                });
            }
        });
    }

    jQuery("#lfb_winRedirection").fadeIn();
    setTimeout(function () {
        jQuery("#wpwrap").css({
            height: jQuery("#lfb_bootstraped").height() + 48
        });
    }, 300);
    jQuery("body,html").animate({
        scrollTop: 0
    }, 200);
}

function lfb_redirSave() {

    var conditions = new Array();
    jQuery('#lfb_winRedirection #lfb_redirUrl').parent().removeClass('has-error');
    jQuery("#lfb_winRedirection .lfb_conditionItem").each(function () {
        var condValue = jQuery(this).find(".lfb_conditionValue").val();
        if (condValue) {
            condValue = condValue.replace(/\'/g, '`');
        }
        conditions.push({
            interaction: jQuery(this).find(".lfb_conditionSelect").val(),
            action: jQuery(this).find(".lfb_conditionoperatorSelect").val(),
            value: condValue
        });
    });
    var url = jQuery('#lfb_winRedirection #lfb_redirUrl').val();
    if (url.length < 1) {
        jQuery('#lfb_winRedirection #lfb_redirUrl').parent().addClass('has-error');
    } else {
        var data = {
            action: 'lfb_saveRedirection',
            id: lfb_currentRedirEdit,
            url: url,
            formID: lfb_currentFormID,
            conditions: JSON.stringify(conditions),
            operator: jQuery("#lfb_redirOperator").val()
        };
        lfb_showLoader();
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: data,
            success: function (rep) {
                if (lfb_currentRedirEdit == 0) {
                    data.id = rep;
                    lfb_currentForm.redirections.push(data);
                    var tr = jQuery('<tr data-id="' + this.id + '"></tr>');
                    tr.append('<td>' + data.url + '</td>');
                    tr.append('<td style="text-align:right;"><a href="javascript:" onclick="lfb_editRedirection(' + data.id + ');" class="btn btn-primary btn-circle"><span class="glyphicon glyphicon-pencil"></span></a><a href="javascript:" onclick="lfb_removeRedirection(' + data.id + ');" class="btn btn-danger btn-circle"><span class="glyphicon glyphicon-trash"></span></a></td>');

                    jQuery('#lfb_redirsTable tbody').append(tr);
                } else {
                    jQuery.each(lfb_currentForm.redirections, function () {
                        if (this.id == lfb_currentRedirEdit) {
                            this.url = data.url;
                            this.conditions = data.conditions;
                            this.conditionsOperator = data.operator;
                        }
                    });
                }
                jQuery('#lfb_loader').fadeOut();
            }
        });
    }
    jQuery("#lfb_winRedirection").fadeOut();
}

function lfb_editDistanceValue(modeQt) {
    lfb_distanceModeQt = modeQt;
    var departAdress = -1;
    var departCity = -1;
    var departZip = -1;
    var departCountry = -1;
    var arrivalAdress = -1;
    var arrivalCity = -1;
    var arrivalZip = -1;
    var arrivalCountry = -1;
    var distanceType = 'km';

    if (modeQt) {
        var distCode = jQuery('#lfb_winItem [name="distanceQt"]').val();
        if (distCode.indexOf('distance_') > -1) {
            var i = -1;
            while ((i = distCode.indexOf('distance_', i + 1)) != -1) {

                var departAdPosEnd = distCode.indexOf('-', i + 9) + 1;
                departAdress = distCode.substr(i + 9, distCode.indexOf('-', i) - (i + 9));

                var departCityPosEnd = distCode.indexOf('-', departAdPosEnd) + 1;
                departCity = distCode.substr(departAdPosEnd, distCode.indexOf('-', departAdPosEnd) - (departAdPosEnd));

                var departZipPosEnd = distCode.indexOf('-', departCityPosEnd) + 1;
                departZip = distCode.substr(departCityPosEnd, distCode.indexOf('-', departCityPosEnd) - (departCityPosEnd));

                var departCountryPosEnd = distCode.indexOf('_', departZipPosEnd) + 1;
                departCountry = distCode.substr(departZipPosEnd, distCode.indexOf('_', departZipPosEnd) - (departZipPosEnd));

                var arrivalAdPosEnd = distCode.indexOf('-', departCountryPosEnd) + 1;
                arrivalAdress = distCode.substr(departCountryPosEnd, distCode.indexOf('-', departCountryPosEnd) - (departCountryPosEnd));

                var arrivalCityPosEnd = distCode.indexOf('-', arrivalAdPosEnd) + 1;
                arrivalCity = distCode.substr(arrivalAdPosEnd, distCode.indexOf('-', arrivalAdPosEnd) - (arrivalAdPosEnd));

                var arrivalZipPosEnd = distCode.indexOf('-', arrivalCityPosEnd) + 1;
                arrivalZip = distCode.substr(arrivalCityPosEnd, distCode.indexOf('-', arrivalCityPosEnd) - (arrivalCityPosEnd));

                var arrivalCountryPosEnd = distCode.indexOf('-', arrivalZipPosEnd) + 1;
                arrivalCountry = distCode.substr(arrivalZipPosEnd, distCode.indexOf('_', arrivalZipPosEnd) - (arrivalZipPosEnd));

                distanceType = distCode.substr(arrivalCountryPosEnd, distCode.indexOf(']', arrivalCountryPosEnd) - (arrivalCountryPosEnd));

            }
        }

    }

    var $selectDepart = jQuery('#lfb_departAdressItem');
    var $selectArrival = jQuery('#lfb_arrivalAdressItem');
    var $selectDepartCity = jQuery('#lfb_departCityItem');
    var $selectArrivalCity = jQuery('#lfb_arrivalCityItem');
    var $selectDepartZip = jQuery('#lfb_departZipItem');
    var $selectArrivalZip = jQuery('#lfb_arrivalZipItem');
    var $selectDepartCountry = jQuery('#lfb_departCountryItem');
    var $selectArrivalCountry = jQuery('#lfb_arrivalCountryItem');
    jQuery('#lfb_distanceType').val(distanceType);

    $selectDepart.find('option').remove();
    $selectArrival.find('option').remove();
    $selectDepartCity.find('option').remove();
    $selectArrivalCity.find('option').remove();
    $selectDepartZip.find('option').remove();
    $selectArrivalZip.find('option').remove();
    $selectDepartCountry.find('option').remove();
    $selectArrivalCountry.find('option').remove();
    $selectDepart.append('<option value="" data-type="">' + lfb_data.texts['Nothing'] + '</option>');
    $selectArrival.append('<option value="" data-type="">' + lfb_data.texts['Nothing'] + '</option>');
    $selectDepartCity.append('<option value="" data-type="">' + lfb_data.texts['Nothing'] + '</option>');
    $selectArrivalCity.append('<option value="" data-type="">' + lfb_data.texts['Nothing'] + '</option>');
    $selectDepartZip.append('<option value="" data-type="">' + lfb_data.texts['Nothing'] + '</option>');
    $selectArrivalZip.append('<option value="" data-type="">' + lfb_data.texts['Nothing'] + '</option>');
    $selectDepartCountry.append('<option value="" data-type="">' + lfb_data.texts['Nothing'] + '</option>');
    $selectArrivalCountry.append('<option value="" data-type="">' + lfb_data.texts['Nothing'] + '</option>');

    jQuery.each(lfb_currentForm.steps, function () {
        var step = this;
        jQuery.each(this.items, function () {
            var item = this;
            if (item.type == 'textfield' || item.type == 'select') {
                var itemID = item.id;
                var selDepAd = '';
                var selDepCity = '';
                var selDepZip = '';
                var selDepCountry = '';
                var selArrAd = '';
                var selArrCity = '';
                var selArrZip = '';
                var selArrCountry = '';

                if (item.id == departAdress) {
                    selDepAd = 'selected';
                }
                if (item.id == departCity) {
                    selDepCity = 'selected';
                }
                if (item.id == departZip) {
                    selDepZip = 'selected';
                }
                if (item.id == departCountry) {
                    selDepCountry = 'selected';
                }
                if (item.id == arrivalAdress) {
                    selArrAd = 'selected';
                }
                if (item.id == arrivalCity) {
                    selArrCity = 'selected';
                }
                if (item.id == arrivalZip) {
                    selArrZip = 'selected';
                }
                if (item.id == arrivalCountry) {
                    selArrCountry = 'selected';
                }

                $selectDepart.append('<option ' + selDepAd + ' value="' + itemID + '" data-type="' + item.type + '">' + step.title + ' : " ' + item.title + ' "</option>');
                $selectArrival.append('<option ' + selArrAd + ' value="' + itemID + '" data-type="' + item.type + '">' + step.title + ' : " ' + item.title + ' "</option>');
                $selectDepartCity.append('<option ' + selDepCity + ' value="' + itemID + '" data-type="' + item.type + '">' + step.title + ' : " ' + item.title + ' "</option>');
                $selectArrivalCity.append('<option ' + selArrCity + ' value="' + itemID + '" data-type="' + item.type + '">' + step.title + ' : " ' + item.title + ' "</option>');
                $selectDepartZip.append('<option ' + selDepZip + ' value="' + itemID + '" data-type="' + item.type + '">' + step.title + ' : " ' + item.title + ' "</option>');
                $selectArrivalZip.append('<option ' + selArrZip + ' value="' + itemID + '" data-type="' + item.type + '">' + step.title + ' : " ' + item.title + ' "</option>');
                $selectDepartCountry.append('<option ' + selDepCountry + ' value="' + itemID + '" data-type="' + item.type + '">' + step.title + ' : " ' + item.title + ' "</option>');
                $selectArrivalCountry.append('<option ' + selArrCountry + ' value="' + itemID + '" data-type="' + item.type + '">' + step.title + ' : " ' + item.title + ' "</option>');
            }
        });
    });


    jQuery("body,html").animate({
        scrollTop: 0
    }, 200);
    jQuery('#lfb_winDistance').fadeIn();
}
function lfb_addonTdgn() {
    lfb_showLoader();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_addonTdgn',
            code: jQuery('#lfb_winTldAddon').find('input[name="purchaseCode"]').val()
        },
        success: function (rep) {
            rep.trim();
            if (rep == '101') {
                document.location.href = document.location.href + '&lfb_formDesign=' + lfb_currentFormID;
            } else {
                jQuery('#lfb_loader').fadeOut();
                jQuery('#lfb_winTldAddon').modal('show');
                jQuery('#lfb_winTldAddon').find('input[name="purchaseCode"]').closest('.form-group').addClass('has-error');
            }
        }
    });
}

function lfb_settings_checkLicense() {
    var error = false;
    var $field = jQuery('#lfb_settings_licenseContainer input[name="purchaseCode"]');
    if ($field.val().length < 9) {
        $field.parent().addClass('has-error');
    } else {
        lfb_showLoader();
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {action: 'lfb_checkLicense', code: $field.val()},
            success: function (rep) {
                jQuery('#lfb_loader').fadeOut();
                if (rep == '1') {
                    $field.parent().addClass('has-error');
                    setTimeout(function () {
                        document.location.href = document.location.href;
                    }, 1000);
                } else {
                    document.location.href = document.location.href;
                }
            }
        });
    }
}
function lfb_saveDistanceValue() {
    var targetfieldName = "calculation";
    if (lfb_calculationModeQt) {
        targetfieldName = "calculationQt";
    }

    var depAd = '';
    if (jQuery('#lfb_departAdressItem').val() != "") {
        depAd = jQuery('#lfb_departAdressItem').val();
    }
    var depCity = '';
    if (jQuery('#lfb_departCityItem').val() != "") {
        depCity = jQuery('#lfb_departCityItem').val();
    }
    var depCountry = '';
    if (jQuery('#lfb_departCountryItem').val() != "") {
        depCountry = jQuery('#lfb_departCountryItem').val();
    }
    var depZip = '';
    if (jQuery('#lfb_departZipItem').val() != "") {
        depZip = jQuery('#lfb_departZipItem').val();
    }


    var arrivalAd = '';
    if (jQuery('#lfb_arrivalAdressItem').val() != "") {
        arrivalAd = jQuery('#lfb_arrivalAdressItem').val();
    }
    var arrivalCity = '';
    if (jQuery('#lfb_arrivalCityItem').val() != "") {
        arrivalCity = jQuery('#lfb_arrivalCityItem').val();
    }
    var arrivalCountry = '';
    if (jQuery('#lfb_arrivalCountryItem').val() != "") {
        arrivalCountry = jQuery('#lfb_arrivalCountryItem').val();
    }
    var arrivalZip = '';
    if (jQuery('#lfb_arrivalZipItem').val() != "") {
        arrivalZip = jQuery('#lfb_arrivalZipItem').val();
    }
    var distanceType = jQuery('#lfb_distanceType').val();

    var code = '[distance_';
    code += depAd + '-' + depCity + '-' + depZip + '-' + depCountry + '_' + arrivalAd + '-' + arrivalCity + '-' + arrivalZip + '-' + arrivalCountry + '_' + distanceType;
    code += ']';

    if (depAd == '' && depCity == '' && depCountry == '' && arrivalAd == '' && arrivalCity == '' && arrivalCountry == '' && depZip == '' && arrivalZip == '') {
        code = '';
    }



    if (!lfb_distanceModeQt) {
        var posCar = jQuery('#lfb_winItem').find('[name="' + targetfieldName + '"]').prop("selectionStart");
        var value = jQuery('#lfb_winItem').find('[name="' + targetfieldName + '"]').val();
        if (isNaN(posCar)) {
            posCar == value.length;
        }
        var newValue = value.substr(0, posCar) + ' ' + code + ' ' + value.substr(posCar, value.length);
        jQuery('#lfb_winItem').find('[name="' + targetfieldName + '"]').val(newValue);
    } else {
        jQuery('#lfb_winItem').find('[name="distanceQt"]').val(code);
    }

    jQuery('#lfb_winDistance').fadeOut();
}
function lfb_openFormDesigner() {
    lfb_showLoader();
    jQuery('#lfb_loader').css({
        position: 'fixed'
    });
    jQuery('body').css({
        overflow: 'hidden'
    });
    setTimeout(function () {
        jQuery('#tld_tdgnPanel').fadeIn();
        jQuery('.tld_tdgnBootstrap').fadeIn();
    }, 500);
    tld_onOpen();
}
function lfb_closeFormDesigner() {
    setTimeout(function () {
        jQuery('#tld_tdgnFrame').attr('src', 'about:blank');
    }, 400);
    jQuery('#lfb_loader').css({
        position: 'absolute'
    });
    jQuery('.tld_tdgnBootstrap').fadeOut();
    jQuery('#tld_tdgnPanel').fadeOut();
    jQuery('body').css({
        overflow: 'initial'
    });
}
function lfb_edit_option(btn) {
    var $tr = jQuery(btn).closest('tr');
    var name = $tr.children('td:eq(0)').html();
    var price = $tr.children('td:eq(1)').html();
    $tr.children('td:eq(0)').html('<input type="text" id="option_edit_value" class="form-control" value="' + name + '" placeholder="Option value">');
    $tr.children('td:eq(1)').html('<input type="number" id="option_new_price" step="any" class="form-control" value="' + price + '" placeholder="Option price">');
    jQuery(btn).hide();
    jQuery(btn).after('<a href="javascript:" onclick="lfb_edit_saveOption(this);" class="btn btn-primary btn-circle "><span class="glyphicon glyphicon-ok"></span></a>');
}
function lfb_edit_saveOption(btn) {
    var $tr = jQuery(btn).closest('tr');
    var name = $tr.children('td:eq(0)').find('input').val();
    var price = $tr.children('td:eq(1)').find('input').val();
    $tr.children('td:eq(0)').html(name);
    $tr.children('td:eq(1)').html(price);
    jQuery(btn).prev('a').show();
    jQuery(btn).remove();
}

function lfb_askDeleteStep(stepID) {
    jQuery('#lfb_winDeleteStep').attr('data-stepid', stepID);
    jQuery('#lfb_winDeleteStep').modal('show');
}
function lfb_confirmDeleteStep() {
    jQuery('#lfb_winDeleteStep').modal('hide');
    lfb_removeStep(jQuery('#lfb_winDeleteStep').attr('data-stepid'));
}

function lfb_askDeleteForm(formID) {
    lfb_formToDelete = formID;
    var formTitle = jQuery('#lfb_panelFormsList table tbody > tr[data-formid="' + formID + '"] a.lfb_formListTitle').text();
    jQuery('#lfb_winDeleteForm #lfb_deleteFormTitle').html(formTitle);
    jQuery('#lfb_winDeleteForm').modal('show');
}
function lfb_confirmDeleteForm() {
    jQuery('#lfb_winDeleteForm').modal('hide');
    lfb_removeForm(lfb_formToDelete);
}
function lfb_removeLayerImg(btn) {
    var $tr = jQuery(btn).closest('tr');
    $tr.slideUp();
    setTimeout(function () {
        $tr.remove();
    }, 380);
}
function lfb_showLayersTable(layers) {
    jQuery('#lfb_imageLayersTable tbody').html('');
    jQuery.each(layers, function () {
        var img = '<input type="hidden" name="image" value="' + this.image + '" onkeyup="lfb_onLayerImgChange(this);" />';
        if (this.image != "") {
            img += '<a href="javascript:" class="imageBtn" data-toggle="tooltip" data-placement="bottom" title="' + lfb_data.texts['edit'] + '" ><img src="' + this.image + '" class="lfb_layerImgPreview" /></a>';
        } else {
            img += '<a href="javascript:" class="btn btn-circle btn-primary imageBtn"  data-toggle="tooltip" data-placement="bottom" title="' + lfb_data.texts['edit'] + '"><span class="glyphicon glyphicon-plus"></span></a>';

        }
        var $tr = jQuery('<tr data-layerid="' + this.id + '"></tr>');
        $tr.append('<td><a href="javascript:" onclick="lfb_editLayerTitle(this);">' + this.title + '</a></td>');
        $tr.append('<td>' + img + '</td>');
        $tr.append('<td style="text-align: right;"><a href="javascript:" data-toggle="tooltip" data-placement="bottom"  title="' + lfb_data.texts['editConditions'] + '" class="btn btn-circle btn-primary" onclick="lfb_editLayerConditions(this);"><span class="fa fa-eye"></span></a><textarea style="display: none;" name="showConditions">' + this.showConditions + '</textarea><input type="hidden" name="showConditionsOperator" value="' + this.showConditionsOperator + '"/><a href="javascript:" onclick="lfb_duplicateLayer(this);" data-toggle="tooltip" data-placement="bottom"  title="' + lfb_data.texts['duplicate'] + '"  class="btn btn-default btn-circle"><span class="glyphicon glyphicon-duplicate"></span></a><a href="javascript:" data-toggle="tooltip" data-placement="bottom"  title="' + lfb_data.texts['remove'] + '" class="btn btn-circle btn-danger" onclick="lfb_removeLayerImg(this);"><span class="glyphicon glyphicon-trash"></span></a></td>');
        jQuery('#lfb_imageLayersTable tbody').append($tr);

        $tr.find('[data-toggle="tooltip"]').b_tooltip();
        $tr.find('.imageBtn').click(function () {
            lfb_formfield = jQuery(this).prev('input');
            tb_show('', 'media-upload.php?TB_iframe=true');
            return false;
        });
    });
}
function lfb_duplicateLayer(btn) {
    jQuery('#lfb_imageLayersTable .lfb_layerEditField').remove();
    var $tr = jQuery(btn).closest('tr');
    var newTr = $tr.clone();
    $tr.after(newTr);
    newTr.find('.imageBtn').click(function () {
        lfb_formfield = jQuery(this).prev('input');
        tb_show('', 'media-upload.php?TB_iframe=true');
        return false;
    });
}
function lfb_newLayerImg() {
    var $tr = jQuery('<tr data-layerid="0"></tr>');
    $tr.append('<td><a href="javascript:" onclick="lfb_editLayerTitle(this);">' + lfb_data.texts['myNewLayer'] + '</a></td>');
    $tr.append('<td><input type="hidden" name="image" value="" onkeyup="lfb_onLayerImgChange(this);" /><a href="javascript:"  data-placement="bottom" data-toggle="tooltip" title="' + lfb_data.texts['edit'] + '" class="btn btn-circle btn-primary imageBtn"><span class="glyphicon glyphicon-plus"></span></a></td>');
    $tr.append('<td style="text-align: right;"><a href="javascript:" data-toggle="tooltip"  data-placement="bottom" title="' + lfb_data.texts['editConditions'] + '" class="btn btn-circle btn-primary" onclick="lfb_editLayerConditions(this);"><span class="fa fa-eye"></span></a><textarea style="display: none;" name="showConditions"></textarea><input type="hidden" name="showConditionsOperator" value=""/><a href="javascript:"  data-placement="bottom" data-toggle="tooltip" title="' + lfb_data.texts['duplicate'] + '" onclick="lfb_duplicateLayer(this);" class="btn btn-default btn-circle"><span class="glyphicon glyphicon-duplicate"></span></a><a href="javascript:"  data-placement="bottom" data-toggle="tooltip" title="' + lfb_data.texts['remove'] + '" class="btn btn-circle btn-danger" onclick="lfb_removeLayerImg(this);"><span class="glyphicon glyphicon-trash"></span></a></td>');

    $tr.find('[data-toggle="tooltip"]').b_tooltip();
    jQuery('#lfb_imageLayersTable tbody').append($tr);
    $tr.find('.imageBtn').click(function () {
        lfb_formfield = jQuery(this).prev('input');
        tb_show('', 'media-upload.php?TB_iframe=true');
        return false;
    });

}
function lfb_editLayerTitle(btn) {
    if (jQuery('#lfb_imageLayersTable .lfb_layerEditField').length > 0) {
        jQuery('#lfb_imageLayersTable .lfb_layerEditField').prev().html(jQuery('#lfb_imageLayersTable .lfb_layerEditField input').val());
        jQuery('#lfb_imageLayersTable .lfb_layerEditField').prev().show();
        jQuery('#lfb_imageLayersTable .lfb_layerEditField').remove();
    }
    jQuery(btn).closest('tr').children('td').first().find('a').hide();
    jQuery(btn).closest('tr').children('td').first().find('a').after('<div class="lfb_layerEditField form-group"><input type="text" class="form-control" value="' + jQuery(btn).closest('tr').children('td').first().find('a').text() + '"/></div>');
    jQuery('#lfb_imageLayersTable .lfb_layerEditField').focusout(function () {
        jQuery('#lfb_imageLayersTable .lfb_layerEditField').prev().html(jQuery('#lfb_imageLayersTable .lfb_layerEditField input').val());
        jQuery('#lfb_imageLayersTable .lfb_layerEditField').prev().show();
        jQuery('#lfb_imageLayersTable .lfb_layerEditField').remove();

    });
}
function lfb_onLayerImgChange(field) {
    var rep = '';
    if (jQuery(field).val() == '') {
        jQuery(field).closest('td').find('a').addClass('btn');
        jQuery(field).closest('td').find('a').addClass('btn-primary');
        jQuery(field).closest('td').find('a').addClass('btn-circle');
        jQuery(field).closest('td').find('a').html('<span class="glyphicon glyphicon-plus"></span>');
    } else {
        jQuery(field).closest('td').find('a').removeClass('btn');
        jQuery(field).closest('td').find('a').removeClass('btn-primary');
        jQuery(field).closest('td').find('a').removeClass('btn-circle');
        jQuery(field).closest('td').find('a').html('<img src="' + jQuery(field).val() + '" class="lfb_layerImgPreview" />');
    }
}
function lfb_openWinModifyTotal() {
    if (lfb_currentLogUseSub == 1) {
        jQuery('#lfb_winNewTotal [name="lfb_modifySubTotalField"]').closest('.form-group').slideDown();
    } else {
        jQuery('#lfb_winNewTotal [name="lfb_modifySubTotalField"]').val(0);
        jQuery('#lfb_winNewTotal [name="lfb_modifySubTotalField"]').closest('.form-group').slideUp();
    }
    jQuery('#lfb_winNewTotal').modal('show');
}
function lfb_confirmModifyTotal() {
    jQuery('#lfb_winNewTotal').modal('hide');
    var total = parseFloat(jQuery('#lfb_winNewTotal [name="lfb_modifyTotalField"]').val());
    var totalSub = parseFloat(jQuery('#lfb_winNewTotal [name="lfb_modifySubTotalField"]').val());

    lfb_currentLogTotal = total;
    lfb_currentLogSubTotal = totalSub;
    var summaryPrice = lfb_currentLogCurrency + '' + wpe_formatPrice(totalSub.toFixed(2), lfb_currentFormID);
    var summaryPriceSingle = lfb_currentLogCurrency + '' + wpe_formatPrice(total.toFixed(2), lfb_currentFormID);
    if (lfb_currentLogCurrencyPosition != 'left') {
        summaryPrice = wpe_formatPrice(totalSub.toFixed(2), lfb_currentFormID) + '' + lfb_currentLogCurrency;
        summaryPriceSingle = wpe_formatPrice(total.toFixed(2), lfb_currentFormID) + '' + lfb_currentLogCurrency;
    }
    if (total > 0 && totalSub > 0) {
        jQuery('.lfb_logEditorContainer #lfb_summaryTotal').html('<span style="color: inherit !important;">' + summaryPrice + '</span>' + lfb_currentLogSubTxt + ' <br/>+' + summaryPriceSingle);
    } else if (totalSub > 0) {
        jQuery('.lfb_logEditorContainer #lfb_summaryTotal').html('<span style="color: inherit !important;">' + summaryPrice + '</span>' + lfb_currentLogSubTxt);
    } else if (total > 0) {
        jQuery('.lfb_logEditorContainer #lfb_summaryTotal').html('<span style="color: inherit !important;">' + summaryPriceSingle + '</span>');
    }
    jQuery('.lfb_logEditorContainer #lfb_summaryTotal').css('color', jQuery('.lfb_logEditorContainer #lfb_summaryTotal').attr('color'));
}


function wpe_formatPrice(price, formID) {
    if (!price) {
        price = 0;
    }
    var formatedPrice = price.toString();
    if (formatedPrice.indexOf('.') > -1) {
        formatedPrice = parseFloat(price).toFixed(2).toString();
    }
    var form = lfb_currentForm;
    if (form.summary_noDecimals == '1') {
        formatedPrice = Math.round(formatedPrice).toString();
    }
    var decSep = lfb_currentLogDecSep;
    var thousSep = lfb_currentLogThousSep;
    var priceNoDecimals = formatedPrice;
    var millionSep = lfb_currentLogMilSep;
    var decimals = "";
    if (formatedPrice.indexOf('.') > -1) {
        priceNoDecimals = formatedPrice.substr(0, formatedPrice.indexOf('.'));
        decimals = formatedPrice.substr(formatedPrice.indexOf('.') + 1, 2);
        formatedPrice = formatedPrice.replace('.', decSep);
        if (decimals.toString().length == 1) {
            decimals = decimals.toString() + '0';
        }
        if (priceNoDecimals.length > 6) {
            formatedPrice = priceNoDecimals.substr(0, priceNoDecimals.length - 6) + millionSep + priceNoDecimals.substr(priceNoDecimals.length - 6, 3) + thousSep + priceNoDecimals.substr(priceNoDecimals.length - 3, priceNoDecimals.length) + decSep + decimals;
        } else if (priceNoDecimals.length > 3) {
            formatedPrice = priceNoDecimals.substr(0, priceNoDecimals.length - 3) + thousSep + priceNoDecimals.substr(priceNoDecimals.length - 3, priceNoDecimals.length) + decSep + decimals;
        }
    } else {
        if (priceNoDecimals.length > 6) {
            formatedPrice = priceNoDecimals.substr(0, priceNoDecimals.length - 6) + millionSep + priceNoDecimals.substr(priceNoDecimals.length - 6, 3) + thousSep + priceNoDecimals.substr(priceNoDecimals.length - 3, priceNoDecimals.length);
        } else if (priceNoDecimals.length > 3) {
            formatedPrice = priceNoDecimals.substr(0, priceNoDecimals.length - 3) + thousSep + priceNoDecimals.substr(priceNoDecimals.length - 3, priceNoDecimals.length);
        }
    }
    return formatedPrice;

}
function lfb_resetReference() {

    jQuery('#lfb_btnResetRef').addClass('disabled');
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_resetReference',
            formID: lfb_currentFormID
        }
    });
}
function lfb_openCalendarPanelFromItem() {
    lfb_openCalendarsPanel(jQuery('#lfb_winItem [name="calendarID"]').val());
}
function lfb_openCalendarsPanel(calendarID) {
    if (calendarID == null) {
        calendarID = 1;
    }
    lfb_showLoader();
    jQuery('#lfb_panelFormsList .tooltip').remove();
    jQuery('.lfb_winHeader .tooltip').remove();

    jQuery('#lfb_winCalendars').fadeIn();
    lfb_currentCalendarEventID = 0;
    jQuery('#lfb_selectCalendar').val(calendarID);
    jQuery('#lfb_selectCalendar').trigger('change');
}
function lfb_openCalendarLeftMenu() {
    lfb_openLeftPanel('lfb_calendarLeftMenu');
}
function lfb_closeCalendarLeftMenu() {
    jQuery('#lfb_calendarLeftMenu .lfb_lPanelBody').fadeOut();
    jQuery('#lfb_calendarLeftMenu .lfb_lPanelHeader').fadeOut();
    setTimeout(function () {
        jQuery('#lfb_calendar').removeClass('lfb_open');
        jQuery('#lfb_calendarLeftMenu').removeClass('lfb_open');
    }, 300);
}
function lfb_editCalendarEvent(eventID) {
    var chkEvent = false;
    if (eventID > 0) {
        jQuery('#lfb_calEventRemindersTable').closest('.form-group').show();
        var eventData = lfb_getCalendarEvent(eventID);
        if (eventData != false) {
            chkEvent = true;
            lfb_currentCalendarEventID = eventID;
            jQuery('#lfb_calendarLeftMenu [name="title"]').val(eventData.title);
            jQuery('#lfb_calendarLeftMenu [name="start"]').datetimepicker('setDate', moment(eventData.start, 'YYYY-MM-DD HH:mm').toDate());
            if (eventData.allDay == 1) {
                jQuery('#lfb_calendarLeftMenu [name="end"]').closest('.form-group').slideUp();
                jQuery('#lfb_calendarLeftMenu [name="end"]').val('');
                jQuery('#lfb_calendarLeftMenu [name="allDay"]').parent().bootstrapSwitch('setState', true);

            } else {
                jQuery('#lfb_calendarLeftMenu [name="end"]').datetimepicker('setDate', moment(eventData.end, 'YYYY-MM-DD  HH:mm').toDate());
                jQuery('#lfb_calendarLeftMenu [name="end"]').closest('.form-group').slideDown();
                jQuery('#lfb_calendarLeftMenu [name="allDay"]').parent().bootstrapSwitch('setState', false);
            }
            var isBusy = false;
            if (eventData.isBusy == 1) {
                isBusy = true;
            }
            jQuery('#lfb_calendarLeftMenu [name="orderID"]').val(eventData.orderID);
            jQuery('#lfb_calendarLeftMenu [name="categoryID"]').val(eventData.categoryID);
            jQuery('#lfb_calendarLeftMenu [name="notes"]').val(eventData.notes);
            jQuery('#lfb_calendarLeftMenu [name="customerAddress"]').val(eventData.customerAddress);
            jQuery('#lfb_calendarLeftMenu [name="customerEmail"]').val(eventData.customerEmail);
            jQuery('#lfb_calendarLeftMenu [name="isBusy"]').parent().bootstrapSwitch('setState', isBusy);
            lfb_generateRemindersTable(eventData.reminders);
        }
    } else {
        jQuery('#lfb_calEventRemindersTable').closest('.form-group').hide();
    }
    if (!chkEvent) {
        lfb_currentCalendarEventID = 0;
        jQuery('#lfb_calendarLeftMenu [name="title"]').val('');
        jQuery('#lfb_calendarLeftMenu [name="start"]').val('');
        jQuery('#lfb_calendarLeftMenu [name="end"]').val('');
        jQuery('#lfb_calendarLeftMenu [name="allDay"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_calendarLeftMenu [name="orderID"]').val('');
        jQuery('#lfb_calendarLeftMenu [name="notes"]').val('');
        jQuery('#lfb_calendarLeftMenu [name="categoryID"]').val(jQuery('#lfb_calendarLeftMenu [name="categoryID"] option').first().attr('value'));
        jQuery('#lfb_calendarLeftMenu [name="customerAddress"]').val('');
        jQuery('#lfb_calendarLeftMenu [name="customerEmail"]').val('');
        jQuery('#lfb_calendarLeftMenu [name="isBusy"]').parent().bootstrapSwitch('setState', false);
        jQuery('#lfb_calendarLeftMenu #lfb_calEventRemindersTable tbody').html('');
    }
    jQuery('html,body').animate({scrollTop: 0}, 250);
    lfb_updatelLeftPanels();
    lfb_openCalendarLeftMenu();
}

function lfb_generateRemindersTable(reminders) {
    var target = '#lfb_calendarLeftMenu #lfb_calEventRemindersTable';
    if (lfb_currentCalendarEventID == 0) {
        target = '#lfb_calEventRemindersTableDefault';
    }
    jQuery(target).find('tbody').html('');
    jQuery.each(reminders, function () {
        var delayText = lfb_data.texts['days'];
        if (this.delayType == 'hours') {
            delayText = lfb_data.texts['hours'];
        } else if (this.delayType == 'weeks') {
            delayText = lfb_data.texts['weeks'];
        } else if (this.delayType == 'months') {
            delayText = lfb_data.texts['months'];
        }
        var tr = jQuery('<tr data-id="' + this.id + '"><td>' + this.delayValue + ' ' + this.delayType + '</td><td class="lfb_calReminderActionTd"><a href="javascript:" onclick="lfb_editCalendarReminder(' + this.id + ');" class="btn btn-primary  btn-circle "><span class="glyphicon glyphicon-pencil"></span></a><a href="javascript:" onclick="lfb_deleteCalendarReminder(' + this.id + ');" class="btn btn-danger btn-circle "><span class="glyphicon glyphicon-trash"></span></a></td></tr>');
        jQuery(target).find('tbody').append(tr);
    });
    if (reminders.length == 0) {
        jQuery(target).find('tbody').html('<tr><td colspan="2">' + lfb_data.texts['noReminders'] + '</td></tr>');
    }
}

function lfb_calEventFullDayChange() {
    if (jQuery('#lfb_calendarLeftMenu [name="allDay"]').is(':checked')) {
        jQuery('#lfb_calendarLeftMenu [name="end"]').closest('.form-group').slideUp();
    } else {
        jQuery('#lfb_calendarLeftMenu [name="end"]').closest('.form-group').slideDown();
    }
}
function lfb_calEventStartDateChange() {
    if (!jQuery('#lfb_calendarLeftMenu [name="allDay"]').is(':checked')) {
        if (jQuery('#lfb_calendarLeftMenu [name="end"]').val() == '' || moment(jQuery('#lfb_calendarLeftMenu [name="end"]').datetimepicker('getDate')) < moment(jQuery('#lfb_calendarLeftMenu [name="start"]').datetimepicker('getDate'))) {
            jQuery('#lfb_calendarLeftMenu [name="end"]').datetimepicker('setDate', moment(moment(jQuery('#lfb_calendarLeftMenu [name="start"]').datetimepicker('getDate')).add(1, 'hours'), 'YYYY-MM-DD  HH:mm').toDate());
        }
    }
}
function lfb_calEventCategoryIDChange() {
    var category = lfb_getCalendarCat(jQuery('#lfb_calendarLeftMenu [name="categoryID"]').val());
    if (category.isBusy == 1) {
        jQuery('#lfb_calendarLeftMenu [name="isBusy"]').parent().bootstrapSwitch('setState', true);
    } else {
        jQuery('#lfb_calendarLeftMenu [name="isBusy"]').parent().bootstrapSwitch('setState', false);
    }
}
function lfb_selectCalendarChange() {
    if (jQuery('#lfb_selectCalendar').val() > 1) {
        jQuery('#lfb_btnDeleteCalendar').removeAttr('disabled');
    } else {
        jQuery('#lfb_btnDeleteCalendar').attr('disabled', 'disabled');
    }
    lfb_closeAllLeftPanels();
    lfb_currentCalendarEventID = 0;
    lfb_currentCalendarID = jQuery('#lfb_selectCalendar').val();
    jQuery('#lfb_calendar').fullCalendar('refetchEvents');
}
function lfb_saveCalendarEvent() {
    var title = jQuery('#lfb_calendarLeftMenu [name="title"]').val();
    var start = moment(jQuery('#lfb_calendarLeftMenu [name="start"]').datetimepicker('getDate')).format('YYYY-MM-DD HH:mm');
    var end = moment(jQuery('#lfb_calendarLeftMenu [name="end"]').datetimepicker('getDate')).format('YYYY-MM-DD HH:mm');
    var customerEmail = jQuery('#lfb_calendarLeftMenu [name="customerEmail"]').val();
    var customerAddress = jQuery('#lfb_calendarLeftMenu [name="customerAddress"]').val();
    var notes = jQuery('#lfb_calendarLeftMenu [name="notes"]').val();
    var categoryID = jQuery('#lfb_calendarLeftMenu [name="categoryID"]').val();
    var allDay = 0;
    var isBusy = 0;
    var error = false;


    jQuery('#lfb_calendarLeftMenu [name="title"]').closest('.form-group').removeClass('has-error');
    jQuery('#lfb_calendarLeftMenu [name="end"]').closest('.form-group').removeClass('has-error');

    if (title.length == 0) {
        error = true;
        jQuery('#lfb_calendarLeftMenu [name="title"]').closest('.form-group').addClass('has-error');
    }
    if (jQuery('#lfb_calendarLeftMenu [name="allDay"]').is(':checked')) {
        allDay = 1;
    }
    if (jQuery('#lfb_calendarLeftMenu [name="isBusy"]').is(':checked')) {
        isBusy = 1;
    }
    if (!allDay && jQuery('#lfb_calendarLeftMenu [name="end"]').val() == '') {
        error = true;
        jQuery('#lfb_calendarLeftMenu [name="end"]').closest('.form-group').addClass('has-error');

    }

    if (!error) {

        jQuery("body,html").animate({
            scrollTop: 0
        }, 200);
        //lfb_showLoader();
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_saveCalendarEvent',
                calendarID: lfb_currentCalendarID,
                eventID: lfb_currentCalendarEventID,
                title: title,
                allDay: allDay,
                orderID: jQuery('#lfb_calendarLeftMenu [name="orderID"]').val(),
                start: start,
                end: end,
                customerEmail: customerEmail,
                customerAddress: customerAddress,
                categoryID: categoryID,
                notes: notes,
                isBusy: isBusy
            },
            success: function (eventID) {

                var newData = {
                    calendarID: lfb_currentCalendarID,
                    eventID: eventID,
                    title: title,
                    allDay: allDay,
                    orderID: jQuery('#lfb_calendarLeftMenu [name="orderID"]').val(),
                    start: start,
                    end: end,
                    customerEmail: customerEmail,
                    customerAddress: customerAddress,
                    notes: notes,
                    isBusy: isBusy,
                    categoryID: categoryID,
                    reminders: new Array()
                };

                if (lfb_currentCalendarEventID == 0) {
                    lfb_currentCalendarEvents.push(newData);
                    lfb_currentCalendarEventID = eventID;
                } else {
                    var eventData = lfb_getCalendarEvent(eventID);
                    eventData = newData;
                }
                jQuery('#lfb_calendar').fullCalendar('refetchEvents');
                jQuery('#lfb_loader').fadeOut();
                jQuery('#lfb_calEventRemindersTable').closest('.form-group').slideDown();
            }
        });
    }

}
function lfb_addCalendarEvent(date, cell) {
    lfb_editCalendarEvent(0);
    jQuery('#lfb_calendarLeftMenu [name="start"]').datetimepicker('setDate', new Date(moment.utc(date).format('YYYY-MM-DD HH:mm')));
    var endDate = date.toDate();
    endDate.setTime(endDate.getTime() + 60 * 60 * 1000);
    jQuery('#lfb_calendarLeftMenu [name="end"]').datetimepicker('setDate', new Date(moment.utc(endDate).format('YYYY-MM-DD HH:mm')));
}
function lfb_confirmDeleteCalendarEvent() {

    jQuery("body,html").animate({
        scrollTop: 0
    }, 200);
    jQuery('#lfb_winDeleteCalendarEvent').modal('hide');
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_deleteCalendarEvent',
            eventID: lfb_currentCalendarEventID
        },
        success: function () {
            jQuery('#lfb_calendar').fullCalendar('refetchEvents');
            lfb_currentCalendarEventID = 0;
            lfb_closeAllLeftPanels();
        }
    });
}

function lfb_deleteCalendarEvent() {
    jQuery('#lfb_winDeleteCalendarEvent').modal('show');
}
function lfb_addNewCalendar() {
    jQuery('#lfb_winEditCalendar').attr('data-calendarid', 0);
    jQuery('#lfb_winEditCalendar').modal('show');
}
function lfb_addEditCalendar() {
    jQuery('#lfb_winEditCalendar').attr('data-calendarid', lfb_currentCalendarID);
    jQuery('#lfb_winEditCalendar').modal('show');
}
function lfb_saveCalendar() {
    var calendarID = jQuery('#lfb_winEditCalendar').attr('data-calendarid');
    var title = jQuery('#lfb_winEditCalendar [name="title"]').val();
    jQuery('#lfb_winEditCalendar [name="title"]').closest('.form-group').removeClass('has-error');

    if (title.length == 0) {
        jQuery('#lfb_winEditCalendar [name="title"]').closest('.form-group').addClass('has-error');

    } else {

        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_saveCalendar',
                calendarID: calendarID,
                title: title
            },
            success: function (newCalendarID) {
                if (calendarID > 0) {
                    jQuery('#lfb_selectCalendar option[value="' + newCalendarID + '"]').html(rep);
                } else {
                    jQuery('#lfb_selectCalendar').append('<option value="' + newCalendarID + '">' + title + '</option>');
                    jQuery('#lfb_winItem').find('[name="calendarID"]').append('<option value="' + newCalendarID + '">' + title + '</option>');
                    jQuery('#lfb_selectCalendar').val(newCalendarID);
                    jQuery('#lfb_selectCalendar').trigger('change');
                }

            }
        });
        jQuery('#lfb_winEditCalendar').modal('hide');
    }
}
function lfb_askDeleteCalendar() {
    jQuery('#lfb_winDeleteCalendar').modal('show');
}
function lfb_deleteCalendar() {
    jQuery('#lfb_winDeleteCalendar').modal('hide');
    if (lfb_currentCalendarID > 1) {
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_deleteCalendar',
                calendarID: lfb_currentCalendarID
            },
            success: function (calendarID) {
                calendarID = calendarID.trim();

                jQuery('#lfb_winItem').find('[name="calendarID"] option[value="' + lfb_currentCalendarID + '"]').remove();
                jQuery('#lfb_selectCalendar option[value="' + lfb_currentCalendarID + '"]').remove();
                lfb_currentCalendarID = 1;
                jQuery('#lfb_selectCalendar').val(1);
                jQuery('#lfb_selectCalendar').trigger('change');
                jQuery('#lfb_calendar').fullCalendar('refetchEvents');
            }
        });
    }
}
function lfb_getCalendarEvent(eventID) {
    var rep = false;
    jQuery.each(lfb_currentCalendarEvents, function () {
        if (this.id == eventID) {
            rep = this;
        }
    });
    return rep;
}
function lfb_btnCalEventViewOrderClick() {
    var orderID = parseInt(jQuery('#lfb_calendarLeftMenu [name="orderID"]').val());
    if (orderID > 0) {
        lfb_showLoader();
        lfb_loadLog(orderID, false);
        jQuery('#lfb_loader').fadeOut();
    }
}
function lfb_calEventOrderIDChange() {
    var orderID = parseInt(jQuery('#lfb_calendarLeftMenu [name="orderID"]').val());
    if (orderID == 0) {
        jQuery('#lfb_calendarLeftMenu [name="orderID"]').parent().find('.btn-circle').attr('disabled', 'disabled');
    } else {
        jQuery('#lfb_calendarLeftMenu [name="orderID"]').parent().find('.btn-circle').removeAttr('disabled');
    }
}
function lfb_getCalendarReminder(reminderID, eventID) {
    var reminderData = false;
    if (eventID > 0) {
        jQuery.each(lfb_getCalendarEvent(eventID).reminders, function () {
            if (this.id == reminderID) {
                reminderData = this;
            }
        });
    } else {
        jQuery.each(lfb_currentCalendarDefaultReminders, function () {
            if (this.id == reminderID) {
                reminderData = this;
            }
        });
    }
    return reminderData;
}
function lfb_editCalendarReminder(reminderID) {
    var reminderData = lfb_getCalendarReminder(reminderID, lfb_currentCalendarEventID);
    jQuery('#lfb_winEditReminder').attr('data-remininderid', reminderID);
    if (reminderID > 0) {
        jQuery('#lfb_winEditReminder [name="delayValue"]').val(reminderData.delayValue);
        jQuery('#lfb_winEditReminder [name="delayType"]').val(reminderData.delayType);
        jQuery('#lfb_winEditReminder [name="title"]').val(reminderData.title);
        jQuery('#lfb_winEditReminder [name="email"]').val(reminderData.email);
        jQuery('#calEventContent').code(reminderData.content);
    } else {
        jQuery('#lfb_winEditReminder [name="email"]').val(jQuery('#lfb_formFields [name="email"]').val());
        jQuery('#lfb_winEditReminder [name="delayValue"]').val(2);
        jQuery('#lfb_winEditReminder [name="delayType"]').val('hours');
        jQuery('#lfb_winEditReminder [name="title"]').val(lfb_data.texts['newEventSubject']);
        jQuery('#calEventContent').code(lfb_data.texts['newEventContent'].replace('[date]', '<strong>[date]</strong>'));
    }
    jQuery('#lfb_winEditReminder').attr('data-reminderid', reminderID);
    jQuery('#lfb_winEditReminder').modal('show');

}
function lfb_saveCalendarReminder() {
    var delayType = jQuery('#lfb_winEditReminder [name="delayType"]').val();
    var delayValue = jQuery('#lfb_winEditReminder [name="delayValue"]').val();
    var title = jQuery('#lfb_winEditReminder [name="title"]').val();
    var email = jQuery('#lfb_winEditReminder [name="email"]').val();
    var content = jQuery('#calEventContent').code();
    var reminderID = jQuery('#lfb_winEditReminder').attr('data-remininderid');

    jQuery('#lfb_winEditReminder [name="delayValue"]').closest('.form-group').removeClass('has-error');
    jQuery('#lfb_winEditReminder [name="title"]').closest('.form-group').removeClass('has-error');

    var error = false;
    if (delayValue == '') {
        error = true;
        jQuery('#lfb_winEditReminder [name="delayValue"]').closest('.form-group').addClass('has-error');
    }
    if (title == '') {
        error = true;
        jQuery('#lfb_winEditReminder [name="title"]').closest('.form-group').addClass('has-error');
    }
    if (!lfb_checkEmail(email)) {
        error = true;
        jQuery('#lfb_winEditReminder [name="email"]').closest('.form-group').addClass('has-error');
    }

    if (!error) {
        //  lfb_showLoader();
        jQuery('#lfb_winEditReminder').modal('hide');

        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_saveCalendarReminder',
                calendarID: lfb_currentCalendarID,
                eventID: lfb_currentCalendarEventID,
                reminderID: reminderID,
                delayValue: delayValue,
                delayType: delayType,
                email: email,
                title: title,
                content: content
            },
            success: function (newReminderID) {
                newReminderID = newReminderID.trim();
                var reminderData = {};
                if (reminderID > 0) {
                    reminderData = lfb_getCalendarReminder(reminderID, lfb_currentCalendarEventID);
                }
                reminderData.delayType = delayType;
                reminderData.delayValue = delayValue;
                reminderData.title = title;
                reminderData.content = content;

                if (reminderID == 0) {
                    reminderData.id = newReminderID;
                    if (lfb_currentCalendarEventID > 0) {
                        var currentEvent = lfb_getCalendarEvent(lfb_currentCalendarEventID);
                        currentEvent.reminders.push(reminderData);
                        lfb_editCalendarEvent(lfb_currentCalendarEventID);
                    } else {
                        lfb_currentCalendarDefaultReminders.push(reminderData);
                        lfb_openDefaultReminders();
                    }
                }

                jQuery('#lfb_loader').fadeOut();
            }
        });
    }

}
function lfb_deleteCalendarReminder(reminderID) {
    if (lfb_currentCalendarEventID > 0) {
        var eventData = lfb_getCalendarEvent(lfb_currentCalendarEventID);
        eventData.reminders = jQuery.grep(eventData.reminders, function (reminder) {
            return reminder.id != reminderID;
        });
    } else {
        lfb_currentCalendarDefaultReminders = jQuery.grep(lfb_currentCalendarDefaultReminders, function (reminder) {
            return reminder.id != reminderID;
        });
    }
    var target = '#lfb_calendarLeftMenu #lfb_calEventRemindersTable';
    if (lfb_currentCalendarEventID == 0) {
        target = '#lfb_calEventRemindersTableDefault';
    }

    if (jQuery(target).find('tbody').children().length == 0) {
        jQuery(target).find('tbody').html('<tr><td colspan="2">' + lfb_data.texts['noReminders'] + '</td></tr>');
    }
    jQuery(target).find('tr[data-id="' + reminderID + '"]').slideUp();
    setTimeout(function () {
        jQuery(target).find('tr[data-id="' + reminderID + '"]').remove();
    }, 300);
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_deleteCalendarReminder',
            reminderID: reminderID
        },
        success: function (rep) {
        }
    });
}
function lfb_calendarEventViewGmap() {
    var address = jQuery('#lfb_calendarLeftMenu [name="customerAddress"]').val();
    if (address.length > 0) {
        var url = 'https://www.google.com/maps/place/' + encodeURIComponent(address);
        var win = window.open(url, '_blank');
        win.focus();
    }
}
function lfb_openLeftPanel(panelID) {
    if (panelID != 'lfb_calendarLeftMenu') {
        lfb_currentCalendarEventID = 0;
    }
    jQuery('#' + panelID).parent().find('.lfb_lPanelMain').addClass('lfb_open');
    jQuery('#' + panelID).addClass('lfb_open');
    jQuery('#' + panelID).parent().find('.lfb_lPanelLeft').not('#' + panelID).find('.lfb_lPanelBody,.lfb_lPanelHeader').fadeOut();
    setTimeout(function () {
        jQuery('#' + panelID).parent().find('.lfb_lPanelLeft').not('#' + panelID).removeClass('lfb_open');
        jQuery('#' + panelID).find('.lfb_lPanelBody,.lfb_lPanelHeader').fadeIn();
    }, 300);
}
function lfb_closeLeftPanel(panelID) {
    lfb_currentCalendarEventID = 0;
    jQuery('#' + panelID).find('.lfb_lPanelBody,.lfb_lPanelHeader').fadeOut();
    setTimeout(function () {
        jQuery('#' + panelID).parent().find('.lfb_lPanelMain').removeClass('lfb_open');
        jQuery('#' + panelID).removeClass('lfb_open');
    }, 300);
}
function lfb_closeAllLeftPanels() {
    jQuery('.lfb_lPanelLeft.lfb_open').each(function () {
        lfb_closeLeftPanel(jQuery(this).attr('id'));
    });

}
function lfb_openDefaultReminders() {
    lfb_currentCalendarEventID = 0;
    lfb_generateRemindersTable(lfb_currentCalendarDefaultReminders);
    lfb_openLeftPanel('lfb_calendarDefaultReminders');
}
function lfb_closeDefaultReminders() {
    jQuery('#lfb_calendarDefaultReminders .lfb_lPanelBody').fadeOut();
    jQuery('#lfb_calendarDefaultReminders .lfb_lPanelHeader').fadeOut();
    setTimeout(function () {
        jQuery('#lfb_calendar').removeClass('lfb_open');
        jQuery('#lfb_calendarDefaultReminders').removeClass('lfb_open');
    }, 300);
}
function lfb_openEventsCategories() {
    lfb_openLeftPanel('lfb_calendarEventsCategories');
}

function lfb_closeEventsCategories() {
    jQuery('#lfb_calendarEventsCategories .lfb_lPanelBody').fadeOut();
    jQuery('#lfb_calendarEventsCategories .lfb_lPanelHeader').fadeOut();
    setTimeout(function () {
        jQuery('#lfb_calendar').removeClass('lfb_open');
        jQuery('#lfb_calendarEventsCategories').removeClass('lfb_open');
    }, 300);
}
function lfb_editCalendarCat(catID) {
    if (catID > 0) {
        var catData = lfb_getCalendarCat(catID);
        if (catData != false) {
            jQuery('#lfb_winEditCalendarCat').find('[name="title"]').val(catData.title);
            jQuery('#lfb_winEditCalendarCat').find('[name="color"]').val(catData.color);
            if (catData.isBusy) {
                jQuery('#lfb_calendarLeftMenu [name="isBusy"]').parent().bootstrapSwitch('setState', true);
            } else {
                jQuery('#lfb_calendarLeftMenu [name="isBusy"]').parent().bootstrapSwitch('setState', false);
            }
        }
    } else {
        jQuery('#lfb_winEditCalendarCat').find('[name="title"]').val('');
        jQuery('#lfb_winEditCalendarCat').find('[name="color"]').val('#f39c12');
    }
    jQuery('#lfb_winEditCalendarCat').find('.lfb_colorPreview').css('backgroundColor', jQuery('#lfb_winEditCalendarCat').find('[name="color"]').val());
    jQuery('#lfb_winEditCalendarCat').attr('data-id', catID);
    jQuery('#lfb_winEditCalendarCat').modal('show');
}
function lfb_saveCalendarCat() {
    //  lfb_showLoader();
    var catID = jQuery('#lfb_winEditCalendarCat').attr('data-id');
    var title = jQuery('#lfb_winEditCalendarCat').find('[name="title"]').val();
    var color = jQuery('#lfb_winEditCalendarCat').find('[name="color"]').val();

    var error = false;
    if (title == '') {
        error = true;
        jQuery('#lfb_winEditCalendarCat [name="title"]').closest('.form-group').addClass('has-error');
    }
    if (!error) {
        jQuery('#lfb_winEditCalendarCat').modal('hide');
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_saveCalendarCat',
                calendarID: lfb_currentCalendarID,
                catID: catID,
                title: title,
                color: color
            },
            success: function () {
                jQuery('#lfb_calendar').fullCalendar('refetchEvents');
                jQuery('#lfb_loader').fadeOut();
            }
        });
    }
}
function lfb_generateCalendarCatsSelect() {
    jQuery('#lfb_calendarLeftMenu').find('[name="categoryID"]').html('');
    jQuery.each(lfb_currentCalendarCats, function () {
        jQuery('#lfb_calendarLeftMenu').find('[name="categoryID"]').append('<option value="' + this.id + '">' + this.title + '</option>');
    });
}

function lfb_generateCalendarCatsTable() {
    jQuery('#lfb_calendarEventsCatsTable').find('tbody').html('');
    jQuery.each(lfb_currentCalendarCats, function () {
        var btnDeleteStyle = '';
        if (this.id == 1) {
            btnDeleteStyle = 'display:none;';
        }
        var tr = jQuery('<tr data-id="' + this.id + '"><td>' + this.title + '</td><td><div class="lfb_calendarCatColor" style="background-color: ' + this.color + ';"></div></td><td class="lfb_calReminderActionTd"><a href="javascript:" onclick="lfb_editCalendarCat(' + this.id + ');" class="btn btn-primary  btn-circle "><span class="glyphicon glyphicon-pencil"></span></a><a href="javascript:" style="' + btnDeleteStyle + '" onclick="lfb_deleteCalendarCat(' + this.id + ');" class="btn btn-danger btn-circle "><span class="glyphicon glyphicon-trash"></span></a></td></tr>');
        jQuery('#lfb_calendarEventsCatsTable').find('tbody').append(tr);
    });
    if (lfb_currentCalendarCats.length == 0) {
        jQuery('#lfb_calendarEventsCatsTable').find('tbody').html('<tr><td colspan="3">' + lfb_data.texts['noCategories'] + '</td></tr>');
    }
}

function lfb_confirmDeleteCalendarCat() {
    lfb_showLoader();
    var catID = jQuery('#lfb_winDeleteCalendarCat').attr('data-id');
    jQuery('#lfb_winDeleteCalendarCat').modal('hide');
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_deleteCalendarCat',
            catID: catID
        },
        success: function () {
            jQuery('#lfb_calendar').fullCalendar('refetchEvents');
        }
    });
}

function lfb_deleteCalendarCat(catID) {
    jQuery('#lfb_winDeleteCalendarCat').attr('data-id', catID);
    jQuery('#lfb_winDeleteCalendarCat').modal('show');
}

function lfb_getCalendarCat(catID) {
    var catData = false;
    jQuery.each(lfb_currentCalendarCats, function () {
        if (this.id == catID) {
            catData = this;
        }
    });
    return catData;
}
function lfb_updateCalendarDaysWeekTable() {
    jQuery('#lfb_calendarDaysWeekTable tr[data-day]').find('input[type="checkbox"]').parent().bootstrapSwitch('setState', true);
    jQuery.each(lfb_currentCalendarDaysWeek, function (i) {
        jQuery('#lfb_calendarDaysWeekTable tr[data-day="' + this + '"]').find('input[type="checkbox"]').parent().bootstrapSwitch('setState', false);
    });
}
function lfb_updateCalendarHoursEnabledTable() {
    jQuery('#lfb_calendarHoursEnabledTable tr[data-hour]').find('input[type="checkbox"]').parent().bootstrapSwitch('setState', true);
    jQuery.each(lfb_currentCalendarDisabledHours, function (i) {
        jQuery('#lfb_calendarHoursEnabledTable tr[data-hour="' + this + '"]').find('input[type="checkbox"]').parent().bootstrapSwitch('setState', false);
    });
}
function lfb_saveCalendarHoursDisabled() {
    //  lfb_showLoader();
    var hoursData = '';
    for (var i = 0; i < 24; i++) {
        if (!jQuery('#lfb_calendarHoursEnabledTable tr[data-hour="' + i + '"]').find('input[type="checkbox"]').is(':checked')) {
            hoursData += i + ',';
        }
    }

    if (hoursData.length > 0) {
        hoursData = hoursData.substr(0, hoursData.length - 1);
    }
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_saveCalendarHoursDisabled',
            calendarID: lfb_currentCalendarID,
            hours: hoursData
        },
        success: function () {
            jQuery('#lfb_calendar').fullCalendar('refetchEvents');
            jQuery('#lfb_loader').fadeOut();
        }
    });
}
function lfb_saveCalendarDaysWeek() {
    //  lfb_showLoader();
    var daysData = '';
    for (var i = 0; i < 7; i++) {
        if (!jQuery('#lfb_calendarDaysWeekTable tr[data-day="' + i + '"]').find('input[type="checkbox"]').is(':checked')) {
            daysData += i + ',';
        }
    }

    if (daysData.length > 0) {
        daysData = daysData.substr(0, daysData.length - 1);
    }
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'lfb_saveCalendarDaysWeek',
            calendarID: lfb_currentCalendarID,
            days: daysData
        },
        success: function () {
            jQuery('#lfb_calendar').fullCalendar('refetchEvents');
            jQuery('#lfb_loader').fadeOut();
        }
    });
}
function lfb_initWeeksDaysText() {
    jQuery('#lfb_calendarDaysWeekTable tr[data-day]').each(function () {
        if (jQuery.fn.datetimepicker.dates[lfb_data.locale] != undefined) {
            jQuery(this).find('td').first().html(jQuery.fn.datetimepicker.dates[lfb_data.locale].days[parseInt(jQuery(this).attr('data-day'))]);
        }
    });
    if (jQuery.fn.datetimepicker.dates[lfb_data.locale] != undefined) {
        if (jQuery.fn.datetimepicker.dates[lfb_data.locale].weekStart == 1) {
            jQuery('#lfb_calendarDaysWeekTable tr[data-day="0"]').detach().appendTo('#lfb_calendarDaysWeekTable tbody');
        }
    }
}
function lfb_deleteOrdersSelection() {
    var logsIDs = '';
    jQuery('#lfb_logsTable tr[data-logid] [name="tableSelector"]:checked').each(function () {
        logsIDs += jQuery(this).closest('tr').attr('data-logid') + ',';
    });
    if (logsIDs.length > 0) {
        lfb_showLoader();
        logsIDs = logsIDs.substr(0, logsIDs.length - 1);
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_removeLogs',
                logsIDs: logsIDs
            },
            success: function () {
                lfb_loadLogs(jQuery('#lfb_panelLogs').attr('data-formid'));
            }
        });
    }
}
function lfb_exportOrdersSelection() {
    var logsIDs = '';
    jQuery('#lfb_logsTable tr[data-logid] [name="tableSelector"]:checked').each(function () {
        logsIDs += jQuery(this).closest('tr').attr('data-logid') + ',';
    });
    if (logsIDs.length > 0) {
        var logID = jQuery('#lfb_panelLogs').attr('data-formid');
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_exportLogs',
                formID: logID,
                logsIDs: logsIDs
            },
            success: function (rep) {
                if (rep != 'error') {
                    window.open(
                            rep,
                            '_blank'
                            );
                }
            }
        });
    }
}
function lfb_editCustomerDataSettings() {
    jQuery('#lfb_gdprSettings').slideDown();
}
function lfb_saveCustomerDataSettings() {
    var error = false;

    if (jQuery('#lfb_winCustDataSettings').find('[name="txtCustomersDataWarningText"]').val().length == 0) {
        error = true;
        jQuery('#lfb_winCustDataSettings').find('[name="txtCustomersDataWarningText"]').closest('.form-group').addClass('has-error');
    }
    if (jQuery('#lfb_winCustDataSettings').find('[name="txtCustomersDataDownloadLink"]').val().length == 0) {
        error = true;
        jQuery('#lfb_winCustDataSettings').find('[name="txtCustomersDataDownloadLink"]').closest('.form-group').addClass('has-error');
    }
    if (jQuery('#lfb_winCustDataSettings').find('[name="txtCustomersDataDeleteLink"]').val().length == 0) {
        error = true;
        jQuery('#lfb_winCustDataSettings').find('[name="txtCustomersDataDeleteLink"]').closest('.form-group').addClass('has-error');
    }
    if (jQuery('#lfb_winCustDataSettings').find('[name="txtCustomersDataLeaveLink"]').val().length == 0) {
        error = true;
        jQuery('#lfb_winCustDataSettings').find('[name="txtCustomersDataLeaveLink"]').closest('.form-group').addClass('has-error');
    }
    if (jQuery('#lfb_winCustDataSettings').find('[name="customersDataDeleteDelay"]').val().length == 0) {
        error = true;
        jQuery('#lfb_winCustDataSettings').find('[name="customersDataDeleteDelay"]').closest('.form-group').addClass('has-error');
    }
    if (jQuery('#lfb_winCustDataSettings').find('[name="txtCustomersDataTitle"]').val().length == 0) {
        error = true;
        jQuery('#lfb_winCustDataSettings').find('[name="txtCustomersDataTitle"]').closest('.form-group').addClass('has-error');
    }
    if (jQuery('#lfb_winCustDataSettings').find('[name="customersDataLabelEmail"]').val().length == 0) {
        error = true;
        jQuery('#lfb_winCustDataSettings').find('[name="customersDataLabelEmail"]').closest('.form-group').addClass('has-error');
    }
    if (jQuery('#lfb_winCustDataSettings').find('[name="customersDataLabelPass"]').val().length == 0) {
        error = true;
        jQuery('#lfb_winCustDataSettings').find('[name="customersDataLabelPass"]').closest('.form-group').addClass('has-error');
    }
    if (jQuery('#lfb_winCustDataSettings').find('[name="customersDataLabelModify"]').val().length == 0) {
        error = true;
        jQuery('#lfb_winCustDataSettings').find('[name="customersDataLabelModify"]').closest('.form-group').addClass('has-error');
    }
    if (jQuery('#lfb_winCustDataSettings').find('[name="txtCustomersDataEditLink"]').val().length == 0) {
        error = true;
        jQuery('#lfb_winCustDataSettings').find('[name="txtCustomersDataEditLink"]').closest('.form-group').addClass('has-error');
    }

    if (!error) {

        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'lfb_saveCustomerDataSettings',
                txtCustomersDataWarningText: jQuery('#lfb_winCustDataSettings').find('[name="txtCustomersDataWarningText"]').val(),
                txtCustomersDataDownloadLink: jQuery('#lfb_winCustDataSettings').find('[name="txtCustomersDataDownloadLink"]').val(),
                txtCustomersDataDeleteLink: jQuery('#lfb_winCustDataSettings').find('[name="txtCustomersDataDeleteLink"]').val(),
                txtCustomersDataLeaveLink: jQuery('#lfb_winCustDataSettings').find('[name="txtCustomersDataLeaveLink"]').val(),
                customersDataDeleteDelay: jQuery('#lfb_winCustDataSettings').find('[name="customersDataDeleteDelay"]').val(),
                txtCustomersDataTitle: jQuery('#lfb_winCustDataSettings').find('[name="txtCustomersDataTitle"]').val(),
                customersDataLabelEmail: jQuery('#lfb_winCustDataSettings').find('[name="customersDataLabelEmail"]').val(),
                customersDataLabelPass: jQuery('#lfb_winCustDataSettings').find('[name="customersDataLabelPass"]').val(),
                customersDataLabelModify: jQuery('#lfb_winCustDataSettings').find('[name="customersDataLabelModify"]').val(),
                txtCustomersDataEditLink: jQuery('#lfb_winCustDataSettings').find('[name="txtCustomersDataEditLink"]').val()
            }
        });
        jQuery('#lfb_winCustDataSettings').modal('hide');
    }
}