/**
 * Auto selects the product on the submit ticket page, if a valid licence key is
 * entered.
 *
 * @global {jQuery} jQuery current version of WP jQuery
 * @global {Object} AS_EDD various WP related data set in includes/functions-edd-sl.php
 */
(function ($) {

    $(document).on('ready', function () {
        var $productLicenseOption = $("#wpas_edd_product_license option");
        $productLicenseOption.each(function () {

            var $productLicense = $(this);

            if ($productLicense.attr('value') != "") {

                var $orderID = $productLicense.attr('value').split("_|_")[2];
                var $productID = $productLicense.attr('value').split("_|_")[1];
                var $license = $productLicense.attr('value').split("_|_")[0];

                $productLicense.val($license);
                $productLicense.attr('data-product', $productID);
                $productLicense.attr('data-order', $orderID);
            }
        });

        var $orderField = $('#wpas_edd_order_num');
        var $ordersOption = $("#wpas_edd_order_num option");

        $ordersOption.each(function () {

            var $orderProducts = $(this);

            if ($orderProducts.attr('value') != "") {

                var $orderID = $orderProducts.attr('value').split("_|_")[0];
                var $productIDs = $orderProducts.attr('value').split("_|_")[1];

                $orderProducts.val($orderID);
                $orderProducts.attr('data-product', $productIDs);
            }
        });

        var $productField = $('#wpas_product');
        var $productOption = $("#wpas_product option");

        var $licenseField = $('#wpas_edd_product_license');
        var $licenseOption = $('#wpas_edd_product_license option');
        $licenseField.on('change', function () {

            if ($licenseField.val() != "") {

                addLoader("wpas_edd_order_num");
                addLoader("wpas_product");
                $orderField.attr('disabled', 'disabled');
                $productField.attr('disabled', 'disabled');

                var $selectedProduct = $('option:selected', this).attr('data-product');
                var $selectedOrder = $('option:selected', this).attr('data-order');

                $ordersOption.show();
                var selected = false;
                $ordersOption.each(function () {
                    if ($(this).attr('value') != $selectedOrder && $(this).attr('value') != "") {
                        $(this).hide();
                    } else {
                        if (selected === false && $(this).attr('value') != "") {
                            $(this).attr("selected", "selected");
                            selected = true;
                        }
                    }
                });
                emptyDropDown($orderField,selected);

                $orderField.removeAttr('disabled');

                selectProductIdFromLicense($licenseField.val());
            } else {
                resetFormFieldsAction();
            }

        });

        $orderField.on('change', function () {

            if ($orderField.val() != "") {

                addLoader("wpas_edd_product_license");
                $licenseField.attr('disabled', 'disabled');

                var $selectedOrder = $('option:selected', this).attr('value');

                $licenseOption.show();

                var selected = false;
                $licenseOption.each(function () {
                    if ($(this).attr('data-order') != $selectedOrder && $(this).attr('value') != "") {
                        $(this).hide();
                    } else {
                        if (selected === false && $(this).attr('value') != "") {
                            $(this).attr("selected", "selected");
                            selected = true;
                        }
                    }
                });
                emptyDropDown($licenseField, selected);

                $licenseField.removeAttr('disabled');

                removeLoader();
            } else {
                resetFormFieldsAction();
            }

        });

        $productField.on('change', function () {
            selectLicenseFromProductTermId($productField.val());
        });

        $productField.val('');
    });

    function resetFormFieldsAction() {
        $('#wpas_edd_product_license option').show();
        $('#wpas_edd_order_num option').show();
        $('#wpas_product option').show();

        $("#wpas_edd_product_license").val("");
        $("#wpas_edd_order_num").val("");
        $("#wpas_product").val("");
    }

    function selectLicenseFromProductTermId(termId) {
        if (termId != "") {

            addLoader("wpas_edd_order_num");
            addLoader("wpas_edd_product_license");
            $("#wpas_edd_order_num").attr('disabled', 'disabled');
            $("#wpas_edd_product_license").attr('disabled', 'disabled');

            var data = {
                action: AS_EDD.LicenceFromTermAction,
                termId: $.trim(termId)
            };
            $.get(AS_EDD.ajaxUrl, data, processProductResponse);

            function processProductResponse(response) {
                var data = $.parseJSON(response);

                if (data.productId !== false) {

                    $("#wpas_edd_product_license option").show();

                    var selected = false;
                    $("#wpas_edd_product_license option").each(function () {
                        if ($(this).attr('data-product') != data.productId && $(this).attr('value') != "") {
                            $(this).hide();
                        } else {
                            if (selected === false && $(this).attr('value') != "") {
                                $(this).attr("selected", "selected");
                                selected = true;
                            }
                        }
                    });
                    emptyDropDown($("#wpas_edd_product_license"), selected);

                    $("#wpas_edd_product_license").removeAttr('disabled');

                    $("#wpas_edd_order_num option").show();

                    var selected = false;
                    $("#wpas_edd_order_num option").each(function () {

                        var $data_product_str = $(this).attr('data-product');

                        if ($data_product_str != undefined) {
                            if ($data_product_str.indexOf(',') > -1) {
                                var $data_product = $data_product_str.split(',');
                            } else {
                                var $data_product = [$data_product_str];
                            }
                        } else {
                            var $data_product = [$data_product_str];
                        }


                        if ($data_product.indexOf(data.productId) == -1 && $(this).attr('value') != "") {
                            $(this).hide();
                        } else {
                            if (selected === false && $(this).attr('value') != "") {
                                $(this).attr("selected", "selected");
                                selected = true;
                            }
                        }
                    });
                    emptyDropDown($("#wpas_edd_order_num"), selected);

                    $("#wpas_edd_order_num").removeAttr('disabled');

                    removeLoader();
                }
            }
        } else {
            resetFormFieldsAction();
        }
    }

    /**
     * Hits an AJAX endpoint check if the entered key is valid and gets the
     * product ID if it is. Then, selects the appropriate product in the form.
     * @param {string} linceseKey the licence key to check
     * @returns {undefined}
     */
    function selectProductIdFromLicense(linceseKey) {
        var data = {
            action: AS_EDD.productFromLicenceAction,
            licenseKey: $.trim(linceseKey)
        };
        $.get(AS_EDD.ajaxUrl, data, processProductResponse);

        /**
         * Processes the response from the asedd_get_product_from_license endpoint.
         * @param {Object} response { productId: boolean|number (false if no match found) }
         */
        function processProductResponse(response) {
            var data = $.parseJSON(response);

            if (data.productId !== false) {
                selectProduct(data.productId);
            }
        }
    }

    /**
     * Select empty option from dropdown after validation and remove previous selected option from options
     * @param {Object} element
     * @param {Boolean} selected
     */
    function emptyDropDown(element, selected) {
        if (selected === false) {
            $(element).find('option').removeAttr('selected'); // Remove all old selected items
            $(element).find('option[value=""]').attr("selected", "selected"); // Select empty option
        }
    }

    /**
     * Selects the the product based on product ID.
     * @param {Number} productId
     * @returns {undefined}
     */
    function selectProduct(productId) {
        var selected = false;
        $("#wpas_product option").each(function () {
            if ($(this).attr('value') != productId && $(this).attr('value') != "") {
                $(this).hide();
            } else {
                if (selected === false && $(this).attr('value') != "") {
                    $(this).attr("selected", "selected");
                    selected = true;
                }
            }
        });

        $("#wpas_product").removeAttr('disabled');
        removeLoader();
    }

    function addLoader(id) {
        $('#' + id).after('<p class="wpas-loader"></p>');
    }

    function removeLoader() {
        $('.wpas-loader').remove();
    }
})(jQuery);