jQuery(document).ready(function ($) {

    "use strict";

    // Special event used to bind notify handlers for .remove()
    (function ($) {
        $.event.special.destroyed = {
            remove: function (o) {
                if (o.handler) {
                    o.handler();
                }
            }
        }
    })(jQuery);

    // Allow more than one accordion item to be opened
    (function ($) {
        $(function () {
            $("#accordion > div").accordion({header: "h3", collapsible: true});
        })
    })(jQuery);

    var _initializing = true;

    /**
     *  Array of Gravity Forms
     */
    var _select_default = {};
    $.each(localized.GF_Forms, function (key, value) {
        _select_default[key] = value['title'];
    });

    /**
     * Array of currently mapped Gravity Forms
     */
    var _mapped_forms = {};

    // Mark GF table
    var $dd = $('select[name="wpas_gravity_form_list[0]"]');
    $($dd).attr('id', 'wpas_gravity_form_list[0]')
        .closest('table')
        .addClass('wpas-gf');

    $('.wpas-gf tr.tf-heading')
        .after('<tr><td colspan="2">' +
            '<div id="wpas-gf-stats-container" style="float: left; width: 300px;"></div>' +
            '<div id="accordion"></div>' +
            '<div id="dialog-confirm" class="ui-dialog" title="Deactivate Attributes?" style="display: none;">' +
            '<p><span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>These items will be permanently deleted and cannot be recovered. Are you sure?</p>' +
            '</div>' +
            //'<div id="popUp"><div>' +
            '</td></tr>')
        .insertAfter(".first-row");


    /***************************************
     * EVENTS
     ***************************************/

    /**
     * ADD Mapping
     */
    $("table.wpas-gf").on('change', 'select[name="wpas_gravity_form_list[0]"]', function () {

        var $html = '';

        var $gfid = $(this).val();
        if ($gfid > 0) {

            // Option text is also Gravity Form form title
            var $title = $('select[name="wpas_gravity_form_list[0]"] option:selected').text();

            //$('table.wpas-gf tbody tr td div#accordion div').last().after(function () {
            $('table.wpas-gf div#accordion').append(function () {

                $html += '<h3 class="gfid-' + $gfid + '" data-gfid="' + $gfid + '" style="clear: both; font-weight: 700; margin-top: 10px;">' + 'Gravity Form: #' + $gfid + ' - ' + $title + '</h3>';

                $html += '<div>';

                /**
                 * MAIN WRAPPER - ACCORDION ITEM
                 */
                $html += '<div class="contentwrapper" >';

                /**
                 * LEFT COLUMN
                 */
                $html += '<div class="leftcolumn clearfix ui-front ui-helper-clearfix gfid-' + $gfid + ' ui-corner-all" >';
                //$html += '<span class="delete_mapping dashicons-before dashicons-trash" data-gfid="' + $gfid + '"></span>';
                $html += getTabsMenuHTML($gfid);
                //$html += '<br class="ui-helper-clearfix"/>';
                $html += '</div>';      // leftcolumn

                /**
                 * CONTENT COLUMN
                 */
                $html += '<div class="contentcolumn clearfix ui-front ui-helper-clearfix gfid-' + $gfid + '" >';
                $html += getTabsMappingHTML($gfid);
                $html += '<span class="delete_mapping dashicons-before dashicons-trash" data-gfid="' + $gfid + '" title="' + localized.delete + '"></span>';
                //$html += '<br class="ui-helper-clearfix"/>';
                $html += '</div>';      // contencolumn

                //$html += '<div class="ui-helper-clearfix"></div>';

                $html += '</div>';      // contenwrapper

                $html += '</div>';

                return $html;
            });

            refreshAccordion();

            $('#tabs-form-menu-' + $gfid).tabs({
                heightStyle: "fill"
            }); //.addClass("ui-tabs-vertical ui-helper-clearfix");

            $('#tabs-' + $gfid).tabs({
                heightStyle: "fill"
            }); //.addClass("ui-tabs-vertical ui-helper-clearfix");


            var $test = $('#accordion #tabs-' + $gfid + '-common p')
            addInput($gfid, 'content', localized.content, true, false, $test);
            addInput($gfid, 'subject', localized.subject, false, false, $test);
            addInput($gfid, 'email', localized.email, false, false, $test);

            $test = $('#accordion #tabs-' + $gfid + '-custom p');
            addInput($gfid, 'assignee', localized.assignee, false, false, $test);
            addInput($gfid, 'product', localized.product, false, false, $test);
            addInput($gfid, 'department', localized.department, false, false, $test);
            //addInput($gfid, 'ticket-tag', localized.ticket-tag, false, false, $test);
            $.each(localized.custom_fields, function (key, value) {
                if (key !== 'department' && key !== 'status' && key !== 'assignee' && key !== 'ticket-tag' && key !== 'product') {
                    addInput($gfid, key, value['args'].title !== '' ? value['args'].title : value.name, false, true, $test);
                }
            });

            $test = $('#accordion #tabs-' + $gfid + '-advanced p');
            addInput($gfid, 'ticket_id', localized.ticket_id, false, false, $test);
            addInput($gfid, 'status', localized.status, false, false, $test);
            addInput($gfid, 'ticket_state', localized.ticket_state, false, false, $test);

            //$test = $('#accordion #tabs-' + $gfid + '-security p');
            //addInput($gfid, 'reference', localized.reference, false, false, $test);  // 'force'?

            $test = $('#accordion #tabs-' + $gfid + '-general p');
            htmlOption($gfid, 'allow_create_user', localized.allow_create_user, $test);
			htmlOption($gfid, 'new_user_send_wp_email_to_admin', localized.new_user_send_wp_email_to_admin, $test);			
			htmlOption($gfid, 'new_user_send_wp_email_to_user', localized.new_user_send_wp_email_to_user, $test);			
            htmlOption($gfid, 'attach_uploaded_files', localized.attach_uploaded_files, $test);
            htmlOption($gfid, 'include_unmapped_fields', localized.include_unmapped_fields, $test);
			htmlOption($gfid, 'hide_field_id_in_ticket_body', localized.hide_field_id_in_ticket_body, $test);
			htmlOption($gfid, 'hide_blanks', localized.hide_blanks, $test);
            htmlOption($gfid, 'include_wpas_gf_stats', localized.include_wpas_gf_stats, $test);

            getMappedForms();

            setFormsDropdown(0, '', 'wpas_gravity_form_list[0]', true);

            if (!_initializing) {
                $('select[name^="wpas_gf_mapping"]').selectmenu('refresh');
            }
        }

    });

    /**
     * DELETE Mapping
     */
    $("table.wpas-gf").on("click", 'span.delete_mapping', function () {

        if (confirm(localized.confirm_delete_this_mapping)) {

            var parent = $(this).closest('div').parent().parent();
            var head = parent.prev('h3');

            parent.bind('destroyed', function () {
                // Sync available forms dropdown once this form is removed
                setFormsDropdown(0, '', 'wpas_gravity_form_list[0]', true);
            });

            parent.add(head).fadeOut('fast', function () {
                $(this).remove();
            });
        }
        return false;

    });

    // Field: Change Field Mapping
    $("table.wpas-gf").on('selectmenuselect', 'select[name^="wpas_gf_mapping"]', function (event, ui) {

        if ($(this).val() === '' || $(this).val() === '-1') {
            $('#' + $(this).data('gfid') + '_' + $(this).data('id') + '_options_wrapper').css('display', 'none');
            $('#' + $(this).data('gfid') + '_' + $(this).data('id') + '_attributes').css('visibility', 'hidden');
        } else {
            $('#' + $(this).data('gfid') + '_' + $(this).data('id') + '_options_wrapper').css('display', 'block');
            $('#' + $(this).data('gfid') + '_' + $(this).data('id') + '_attributes').css('visibility', 'visible');
        }

        return event;

    });

    // Reference Field: Change Source
    $("table.wpas-gf").on('selectmenuselect', 'select[name$="[reference][source_form][id]"]', function () {

        var $field_id = $(this).data('id');
        var $form_id = $(this).val();

        var $options = get_form_fields($form_id, $field_id);

        var $dd = $('select[name="wpas_gf_mapping[' + $(this).data('gfid') + '][reference][source_field][id]"]');

        $dd.css('display', 'visible');
        $('select[name="wpas_gf_mapping[' + $(this).data('gfid') + '][reference][source_field][id]"] option').remove();
        $dd.append($options).val('-1').selectmenu('refresh');

    });

    // Field Attributes - Single attribute only
    $("table.wpas-gf").on('change', 'input[type="checkbox"].single_field_attribute_allowed', function (e) {

        if (this.checked && !_initializing) {

            e.preventDefault();

            var $ret = false;
            var $gfid = $(this).data('gfid');
            var $id = $(this).data('id');

            var $cnt = $('input[name^="wpas_gf_mapping[' + $gfid + '][' + $id + '][attributes]"]:checked').not(this).length;


            if (0 < $('#' + $gfid + '_' + $id + '_attributes input[type="checkbox"]:checked').not(this).length) {

                var $g = this;

                $("#dialog-confirm").dialog({
                    dialogClass: 'ui-dialog',
                    resizable: false,
                    height: "auto",
                    width: 400,
                    modal: true,
                    buttons: {
                        "Continue": function () {
                            $('#' + $gfid + '_' + $id + '_attributes input:checkbox').not($g).removeAttr('checked').checkboxradio("refresh");
                            $ret = true;
                            $(this).dialog("close");
                        },
                        Cancel: function () {
                            $($g).removeAttr('checked').checkboxradio("refresh");
                            $ret = false;
                            $(this).dialog("close");
                        }
                    }
                });

                customConfirm('Enabling this attribute will deactivate all others.<br/><br/>Are you sure you want to do this?')
                    .then(function () {
                        $('#' + $gfid + '_' + $id + '_attributes input:checkbox').not($g).removeAttr('checked').checkboxradio("refresh");
                        console.log("You Clicked Yes");
                    })
                    .fail(function () {
                        $($g).attr('checked', false);
                        console.log("You Clicked No");
                        return false;
                    });

            }
            return $ret;
        }

        return true;

    });


    function customConfirm(customMessage) {
        var d = new $.Deferred();
        $("#popUp").html(customMessage);
        $("#popUp").dialog({
            resizable: false,
            height: 200,
            modal: true,
            buttons: {
                "Yes": function () {
                    $(this).dialog("close");
                    d.resolve()
                },
                "No": function () {
                    $(this).dialog("close");
                    d.reject();
                }
            }
        });
        return d.promise();
    }

    function getTabsMappingHTML($gfid) {

        var $html = '';

        $html += '<div id="tabs-' + $gfid + '" class="tabslll" >';
        $html += '<ul>';
        $html += '<li><a href="#tabs-' + $gfid + '-common">' + localized.tab_menu_common_fields + '</a></li>';
        $html += '<li><a href="#tabs-' + $gfid + '-custom">' + localized.tab_menu_custom_fields + '</a></li>';
        //$html += '<li><a href="#tabs-' + $gfid + '-security">' + localized.tab_menu_security + '</a></li>';
        $html += '<li><a href="#tabs-' + $gfid + '-advanced">' + localized.tab_menu_advanced + '</a></li>';
        $html += '<li><a href="#tabs-' + $gfid + '-general">' + localized.tab_menu_general + '</a></li>';
        $html += '</ul>';
        $html += '<div id="tabs-' + $gfid + '-common" ><p></p></div>';
        $html += '<div id="tabs-' + $gfid + '-custom"><p></p></div>';
        //$html += '<div id="tabs-' + $gfid + '-security"><p></p></div>';
        $html += '<div id="tabs-' + $gfid + '-advanced"><p></p></div>';
        $html += '<div id="tabs-' + $gfid + '-general"><p></p></div>';
        $html += '</div>';      // tabs-

        return $html;

    }

    function getTabsMenuHTML($gfid) {

        var $html = '';

        $html += '<div id="tabs-form-menu-' + $gfid + '" class="tabslll" style="clear: both;">';
        $html += '<ul>';
        $html += '<li><a href="#tabs-form-menu-' + $gfid + '-status">' + localized.tabs_forms_menu_status + '</a></li>';
        $html += '<li><a href="#tabs-form-menu-' + $gfid + '-quickset">' + localized.tabs_forms_menu_quickset + '</a></li>';
        $html += '</ul>';


        $html += '<div id="tabs-form-menu-' + $gfid + '-status"><p>';
        $html += '<div class="leftcolumn-row">';
        $html += '<div style="float: left;">Form submissions: </div>';
        $html += '<div style="float: right; padding-right: 10px;">' + localized.GF_Forms[$gfid]['entries_count'] + '</div>';
        $html += '</div>';

        if (undefined !== localized.GF_Forms[$gfid]['last_entry'] && null !== localized.GF_Forms[$gfid]['last_entry']) {
            $html += '<div class="leftcolumn-row">';
            $html += '<div style="float: left;">Source URL: </div>';
            $html += '<div style="float: right; padding-right: 10px;"><a href="' + localized.GF_Forms[$gfid]['last_entry']['source_url'] + '" target="_new">' + 'View Form' + '</a></div>';
            $html += '</div>';

            $html += '<div class="leftcolumn-row">';
            $html += '<div style="float: left;">Status: </div>';
            $html += '<div style="float: right; padding-right: 10px;">' + localized.GF_Forms[$gfid]['last_entry']['status'] + '</div>';
            $html += '</div>';

            $html += '<div class="leftcolumn-row">';
            $html += '<div style="float: left;">User Agent: </div>';
            $html += '<div style="float: right; padding-right: 10px;">' + localized.GF_Forms[$gfid]['last_entry']['user_agent'] + '</div>';
            $html += '</div>';

            $html += '<div class="leftcolumn-row">';
            $html += '<div style="float: left;">Date Created: </div>';
            $html += '<div style="float: right; padding-right: 10px;">' + localized.GF_Forms[$gfid]['last_entry']['date_created'] + '</div>';
            $html += '</div>';

            $html += '<div class="leftcolumn-row">';
            $html += '<div style="float: left;">Created by: </div>';
            $html += '<div style="float: right; padding-right: 10px;">' + localized.GF_Forms[$gfid]['last_entry']['created_by'] + '</div>';
            $html += '</div>';

            $html += '<div class="leftcolumn-row">';
            $html += '<div style="float: left;">Is Read: </div>';
            $html += '<div style="float: right; padding-right: 10px;">' + localized.GF_Forms[$gfid]['last_entry']['is_read'] + '</div>';
            $html += '</div>';
        }

        $html += '</p></div>';

        $html += '<div id="tabs-form-menu-' + $gfid + '-quickset"><p>';

        $html += '<div>#1 Reply to Ticket</div>';
        $html += '<div>#2 Reply to Ticket (Authenticated)</div>';
        $html += '<div>#3 New Ticket</div>';
        $html += '<div>#4 New Ticket with Department</div>';
        $html += '<div>#5 New Ticket with Product</div>';
        $html += '<div>#6 New Ticket with Status & State</div>';
        $html += '<div>#7 Admin: Assign Ticket to Agent</div>';
        $html += '<div>#8 Ticket Update for Status & State Changes</div>';
        $html += '<div>#9 Ticket Update for Status & State Changes (Authenticated)</div>';
        $html += '<div>#10 Special: Reference Source</div>';
        $html += '<div>#11 Special: Reference Target</div>';
        $html += '<div>#12 TBD</div>';

        $html += '</p></div>';
        $html += '</div>';      // tabs-

        return $html;

    }

    /**
     * Get an array of mapped forms
     */
    function getMappedForms() {

        _mapped_forms = new Array();

        var $dd = $(".wpas-gf h3[class^='gfid-']");

        $.each($dd, function (key, value) {    //localized.GF_Mappings, function (key, value) {
            _mapped_forms.push(parseInt($(value).data('gfid'), 10));
            //_mapped_forms[$(value).data('gfid')] = $(value).data('gfid');
        });

        return _mapped_forms;
    }

    /**
     * Available Forms Dropdown
     */
    function setFormsDropdown($gfid, $id, $dropdown_name, $ignore_mapped) {

        getMappedForms();

        $ignore_mapped = undefined === $ignore_mapped ? !_initializing : $ignore_mapped;

        var $html = '';
        var $all_forms_mapped = (Object.keys(_select_default).length - 1) === _mapped_forms.length;

        if ($ignore_mapped && $all_forms_mapped) {
            $html = '<option value="-1" selected>' + localized.all_forms_mapped + '</option>';
        }
        else {
            $html = '<option value="-1" selected>' + localized.select_a_form + '</option>';
        }

        $dropdown_name = $dropdown_name || 'wpas_gravity_form_list[0]';
        //$dropdown_name += '[' + $gfid + ']';

        $.each(_select_default, function (key, value) {
            if (key != '-1') {
                if ($.inArray(parseInt(key, 10), _mapped_forms) === -1) {
                    $html += '<option value="' + key + '">' + value + '</option>';
                }
                else if ($.inArray(parseInt(key, 10), _mapped_forms) !== -1 && !$ignore_mapped) {
                    $html += '<option value="' + key + '">' + value + '</option>';
                }
            }
        });

        var $dd = $('select[name="' + $dropdown_name + '"]');
        $($dd).empty().append($html);

        if (!_initializing) {
            if ($ignore_mapped) {
                $($dd).prop("disabled", $all_forms_mapped);
            }
        }

        if ('wpas_gravity_form_list[0]' !== $dropdown_name) {
            if (_initializing) {
                //$dd.selectmenu();
            } else {
                //$dd.selectmenu("refresh");
            }
        }

    }

    function htmlOption($gfid, $optionName, $label, $container) {

        var $id = 'wpas_gf_mapping[' + $gfid + '][options][' + $optionName + ']';
        var $html = '';

        $($container).append(function () {

            $html += '<div style="border-bottom: 1px solid #dfdfdf; display: block; overflow: hidden; clear: both; margin: 0 0;">';
            $html += '<fieldset class="ui-front" style="padding: 5px 20px;">';
            $html += '<input type="checkbox" id="' + $id + '" name="' + $id + '" value="1" />';
            $html += '<label for="' + $id + '" >' + $label + '</label>';
            $html += '<span class="dashicons-before dashicons-editor-help" style="vertical-align: middle !important;" title="' + popup_content($optionName, '') + '" ></span>';
            $html += '</fieldset>';
            $html += '</div>';

            return $html;
        });

        $('input[name="' + $id + '" ').checkboxradio();
        $('input[name="' + $id + '" ').attr('value', htmlOptionChecked($gfid, $optionName) === '' ? '1' : '1');
        $('input[name="' + $id + '" ').prop('checked', htmlOptionChecked($gfid, $optionName) !== '');
        $('input[name="' + $id + '" ').checkboxradio("refresh");
    }

    function htmlOptionChecked($gfid, $id) {

        if (localized.GF_Mappings.hasOwnProperty($gfid)) {
            return localized.GF_Mappings[$gfid]['options'][$id] == '1' ? '1' : '';
        }
        else {
            return '';
        }

    }

    function htmlChecked($gfid, $id, $attribute) {

        if (localized.GF_Mappings.hasOwnProperty($gfid)) {
            //if ($id === 'reference') {
            //    return ''
            //}
            if ($attribute !== '') {
                // Field mapped?
                if (undefined !== localized.GF_Mappings[$gfid][$id] && null !== localized.GF_Mappings[$gfid][$id] && localized.GF_Mappings[$gfid][$id] !== '-1') {

                    // Have attribute settings?
                    if (undefined !== localized.GF_Mappings[$gfid][$id]['attributes']) {
                        return localized.GF_Mappings[$gfid][$id]['attributes'][$attribute] == '1' ? 'checked' : '';
                    }

                }

            } else {
                return localized.GF_Mappings[$gfid][$id]['id'] == '1' ? 'checked' : '';
            }
        }

        return '';

    }

    function htmlSetAvailableFormFields($gfid, $id) {
        /**
         * SELECT Dropdown for mappable fields
         */
        var $is_mapped = false;
        var $selected = '';

        var $html = '<option value="-1">' + localized.select_a_field + '</option>';
        $.each(localized.GF_Forms, function (key, value) {

            if (_initializing && !(key in localized.GF_Mappings)) {
                return true;
            }

            if (key == $gfid) {
                $.each(value['fields'], function (fieldkey, fieldvalue) {
                    if (_initializing) {
                        $selected = '';
                        if (localized.GF_Mappings[$gfid][$id]) {
                            if (localized.GF_Mappings[$gfid][$id]['id'] == fieldkey) {
                                $selected = 'selected';
                                $is_mapped = true;
                            }
                        }
                    }
                    $html += '<option value="' + fieldkey + '" ' + $selected + '>' + fieldvalue + '</option>';
                })
            }
        });

        var $dd = $('select[name="wpas_gf_mapping[' + $gfid + '][' + $id + '][id]"]');
        $dd.empty().append($html);
        $dd.selectmenu();

        return $is_mapped;

    }


    function addInput($gfid, $id, $label, $map_required, $custom_field, $container) {

        $custom_field = !$custom_field ? false : true;

        var $mapping_required = $map_required === true ? '* <em>(required)</em>' : '';
        var $html = '';

        var $main_wrapper = 'wrapper_row_wpas_gf_mapping[' + $gfid + '][' + $id + '][id]';

        $($container).append(function () {
            $html = '';

            $html += '<div id="' + $main_wrapper + '" style="border-bottom: 1px solid #dfdfdf; display: block; overflow: hidden; clear: both; margin: 0 0; padding: 5px 0;">';

            // Row
            $html += '<div id="row_wpas_gf_mapping[' + $gfid + '][' + $id + '][id]" style="clear: both; overflow: hidden">';

            // Mapping Dropdown
            $html += '<div class="mapping-dropdown-wrapper">';
            $html += '<fieldset class="ui-front">';
            $html += '<label for="wpas_gf_mapping[' + $gfid + '][' + $id + '][id]" style="width: 150px;">' + $label + $mapping_required + '</label>';
            $html += '<select id="wpas_gf_mapping[' + $gfid + '][' + $id + '][id]" data-id="' + $id + '" data-gfid="' + $gfid + '" name="wpas_gf_mapping[' + $gfid + '][' + $id + '][id]"></select>';
            $html += '<span class="dashicons-before dashicons-editor-help" style="vertical-align: middle !important;" title="' + popup_content($id, 'field') + '" ></span>';
            $html += '</fieldset>';
            $html += '</div>';

            $html += '<div id="' + $gfid + '_' + $id + '_attributes" class="mapping-attributes-wrapper"></div>';

            $html += '</div>';  // row_wpas_gf_mapping

            // Field options wrapper (used by Reference et al for special settings). Hidden by default.
            if (-1 < $.inArray($id, ['reference'])) {   //, 'status'])) {
                $html += '<div id="' + $gfid + '_' + $id + '_' + 'options_wrapper" style="clear: both; margin-top: 25px; display: none;"></div>';
            }

            $html += '</div>';  // main_wrapper

            return $html;
        });

        var $is_mapped = htmlSetAvailableFormFields($gfid, $id);

        /**
         *  Field Attributes
         */

        // Set visibility of attributes container depending on whether this field is mapped
        $('#' + $gfid + '_' + $id + '_attributes').css('visibility', $is_mapped === true ? 'visible' : 'hidden');

        // Default to no attributes
        var $attributes = [];

        // All custom fields have the same attributes
        if ($custom_field) {
            $attributes = localized.attributes['custom_field'];
        }
        // Else get attributes for this field
        else {
            $attributes = localized.attributes[$id];
        }

        var $class = '';
        if (-1 < $.inArray($id, ['reference'])) {
            $class = 'single_field_attribute_allowed';
        }

        var $html_id = '';

        // Create each attribute
        $.each($attributes, function ($index, $attribute) {

            $html_id = 'wpas_gf_mapping[' + $gfid + ']' + '[' + $id + '][attributes][' + $attribute + ']';
            $('table.wpas-gf #' + $gfid + '_' + $id + '_' + 'attributes').append(
                '<div id="' + $gfid + '_' + $id + '_' + $attribute + '" '
                + 'style="float: left; margin: 5px 2px 0; padding: 1px;" '
                + 'class="ui-front" '
                + '>'
                + '</div>'
            );

            // Checkbox
            $('table.wpas-gf #' + $gfid + '_' + $id + '_' + $attribute).append(
                '<label for="' + $html_id + '" title="' + popup_content($id, $attribute) + '" >' + localized['field_' + $attribute]

                + '<input type="checkbox" ' +
                'id="' + $html_id + '" name="' + $html_id + '" ' +
                'value="1" ' +
                'data-gfid="' + $gfid + '" ' +
                'data-id="' + $id + '" ' +
                'class="' + $class + '" />'

                + '</label>'
            );


            //$('.wpas-gf #' + $gfid + '_' + $id + '_' + $attribute).prop('tooltipText', popup_content($id, $attribute));

            //var $att = $('#' + $gfid + '_' + $id + '_' + $attribute).tooltip();
            //$att.prop('title', popup_content($id, $attribute));
            //$att.attr('title', popup_content($id, $attribute));
            //$att.data('title',popup_content($id, $attribute));
            //$att.removeAttr("title");

            $('#' + $gfid + '_' + $id + '_' + $attribute).tooltip({
                classes: {
                    "ui-tooltip": "highlight ui-corner-all"
                }
            });

            $('input[name="' + $html_id + '"]').checkboxradio();
            $('input[name="' + $html_id + '"]').prop('checked', htmlChecked($gfid, $id, $attribute) !== '');
            $('input[name="' + $html_id + '"]').checkboxradio("refresh");
        });

        //$('#accordion #tabs-' + $gfid + ' #' + $gfid + '_' + $id + '_attributes input[type="checkbox"]').checkboxradio();


        /**
         * Fields with Special Circumstance
         */

        if ($id === 'reference') {

            var $dropdown_name = '';

            // FIELD OPTIONS WRAPPER NAME
            var $wrapper = $('#' + $gfid + '_' + $id + '_' + 'options_wrapper');

            $html = '<div class="mapping-attributes-wrapper">';
            $html += '<h4>Actions</h4>';

            $html += '<fieldset class="ui-front">';

            // Source form ids
            var $source_form_dropdown_name = 'wpas_gf_mapping[' + $gfid + '][' + $id + '][source_form][id]';
            $html += '<div style="clear: both; float: left; min-width: 40%; padding: 5px 0px;">';
            $html += '<label for="' + $source_form_dropdown_name + '" style="width: 150px;">Source Form</label>';
            $html += '<select id="' + $source_form_dropdown_name + '" data-id="' + $id + '" data-gfid="' + $gfid + '" name="' + $source_form_dropdown_name + '"></select>';
            $html += '</div>';

            // Source field ids
            var $source_fields_dropdown_name = 'wpas_gf_mapping[' + $gfid + '][' + $id + '][source_field][id]';
            $html += '<div style="clear: both; float: left; padding: 5px 0px;">';
            $html += '<label for="' + $source_fields_dropdown_name + '" style="width: 150px;">Source Field</label>';
            $html += '<select id="' + $source_fields_dropdown_name + '" data-id="' + $id + '" data-gfid="' + $gfid + '" name="' + $source_fields_dropdown_name + '"></select>';
            $html += '</div>';

            $html += '</fieldset>';
            $html += '</div>';

            $wrapper.append($html);


            $('input[name="radio-1"]').checkboxradio();
            //$('input[name="radio-1"]').prop('checked', htmlChecked($gfid, $id, $attribute) !== '');
            $('input[name="radio-1"]').checkboxradio("refresh");


            setFormsDropdown($gfid, $id, $source_form_dropdown_name, false);
            $('select[name="' + $source_form_dropdown_name + '"]').selectmenu();
            $('select[name="' + $source_fields_dropdown_name + '"]').selectmenu();


            var $source_form_id = '-1';
            var $source_field_id = '-1';

            if (undefined !== localized.GF_Mappings[$gfid]
                && undefined !== localized.GF_Mappings[$gfid][$id]
                && '-1' != localized.GF_Mappings[$gfid][$id]['id']
                && undefined !== localized.GF_Mappings[$gfid][$id]['source_form']) {

                $source_form_id = localized.GF_Mappings[$gfid][$id]['source_form']['id'];

                if (undefined !== localized.GF_Mappings[$gfid][$id]['source_field']) {
                    $source_field_id = localized.GF_Mappings[$gfid][$id]['source_field']['id'];
                }
            }

            // Trigger attribute select to sync options wrapper display
            $('table.wpas-gf select[name="wpas_gf_mapping[' + $gfid + '][' + $id + '][source_form][id]"]')
                .val($source_form_id)
                .selectmenu('refresh')
                //.trigger('change');
                .trigger('selectmenuselect');

            // Update source form fields dropdown
            $('table.wpas-gf #wpas_gf_mapping[' + $gfid + '][' + $id + '][source_form][id]').trigger('change');

            $('table.wpas-gf select[name="wpas_gf_mapping[' + $gfid + '][' + $id + '][source_field][id]"]')
                .val($source_field_id)
                .selectmenu('refresh')
                .trigger('selectmenuselect');

            // Display wrapper contents
            $('table.wpas-gf input[type="checkbox"].single_field_attribute_allowed').trigger('change');

            var $selected = '';
            if ($is_mapped && _initializing) {
                $selected = '';
                if (localized.GF_Mappings[$gfid][$id]) {
                    if (undefined !== localized.GF_Mappings[$gfid][$id]['id']) {
                        $wrapper.css('display', undefined !== localized.GF_Mappings[$gfid][$id]['attributes']['validate']);
                        //[reference][source_field][id]"]').css('display', 'visible');
                    }
                }
            }


        }

        if ($id === 'statusz' && $attribute === 'force' || $id === 'ticket_statez' && $attribute === 'force') {
            $html += '<select id="wpas_gf_mapping[' + $gfid + '][' + $id + ']" '
                + 'name="wpas_gf_mapping[' + $gfid + '][' + $id + ']">'
                + '<option value="" selected="selected">' + localized.select_a_field + '</option>';

            $.each(localized.WPAS_Status, function (key, value) {

                $html += '<option value="' + key + '" ' + '>' + value + '</option>';
            });

            $html += '</select>';

        }

    }

    /**
     * Return available field options for passed Gravity Forms form ID.
     *
     * Returned data are options HTML for a <select> dropdown.
     *
     * @param $gfid
     * @param $selected_id
     * @returns {string}
     */
    function get_form_fields($gfid, $selected_id) {

        var $html = '';

        $.each(localized.GF_Forms, function (key, value) {

            if (_initializing && !(key in localized.GF_Mappings)) {
                return true;
            }

            if (key == $gfid) {
                $html = '<option value="-1" selected="selected">' + localized.select_a_field + '</option>';
                var $selected = '';
                $.each(value['fields'], function (fieldkey, fieldvalue) {
                    if (_initializing) {
                        $selected = '';
                        if (localized.GF_Mappings[$gfid][$selected_id]) {
                            if (localized.GF_Mappings[$gfid][$selected_id]['id'] == fieldkey) {
                                $selected = 'selected="selected"';
                                //$is_mapped = true;
                            }
                        }
                    }
                    $html += '<option value="' + fieldkey + '" ' + $selected + '>' + fieldvalue + '</option>';
                })
            }
        });

        return $html;
    }

    function popup_content($id, $help_type) {

        var $help_lines = '';
        if ('' === $help_type) {
            if (!localized.help[$id]) {
                return $id;
            }
            $help_lines = localized.help[$id];
        }
        else {
            if (!localized.help[$id] || !localized.help[$id][$help_type]) {
                return localized.help['custom_field'][$help_type]
                //return $id + '-' + $help_type;
            }
            $help_lines = localized.help[$id][$help_type];
        }
        var $title = '';

        $.each($help_lines, function ($index, $help_line) {
            $title += $help_line + '\n';
        });

        return $title;
    }

    function refreshAccordion() {

        var $accordion = $('table.wpas-gf div#accordion');

        if (!$accordion.hasClass('ui-accordion')) {
            $accordion.accordion({
                collapsible: true,
                heightStyle: "auto"
            });
        }
        else {          //if ($accordion.hasClass('ui-accordion')) {
            $accordion.accordion("refresh");
        }
    }

    /******************************************************
     *
     * All events registered - display GF settings
     *
     *****************************************************/

    // Set initial Gravity Forms choices - do not ignore mapped (yet)
    setFormsDropdown(0, '', 'wpas_gravity_form_list[0]', false);

    // Render current mapping configurations
    $.each(localized.GF_Mappings, function (key, value) {
        $('table select[name="wpas_gravity_form_list[0]"]')
            .val(key)
            .trigger('change');
    });

    // Open first accordion
    if (_mapped_forms.length > 0) {
        $("#accordion").accordion("option", "active", 0);
    }

    //$(document).tooltip({
    //    classes: {
    //        "ui-tooltip": "highlight ui-corner-all"
    //    }
    //});

    _initializing = false;

    // Set Gravity Forms choices - ignore mapped forms
    setFormsDropdown(0, '', 'wpas_gravity_form_list[0]', true);

});
