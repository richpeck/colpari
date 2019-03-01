jQuery(document).ready(function ($) {

	'use strict';

	// Convert size in bytes to human readable format
	function bytesToSize(bytes, precision) {
		var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
		var posttxt = 0;
		if (bytes === 0) return 'n/a';
		while (bytes >= 1024) {
			posttxt++;
			bytes = bytes / 1024;
		}
		return parseInt(bytes, 10).toFixed(precision) + " " + sizes[posttxt];
	}

	// Define variables
	var getApiKey = {
		'action': 'filepicker_settings'
	};
	var button = $('#wpas-filepicker-upload');
	var maxfilesAlert = button.attr('data-maxfiles');
	var input = $('#wpas-filepicker-data');
	var uploads = [];
	var uploadList = $('<ul class="wpas-attachments-list"></ul>').hide();
	button.after(uploadList);

	/*
	FilePicker File Attachements
	https://developers.inkfilepicker.com/docs/web/
	 */
	if (typeof filepicker !== 'undefined' && button.length) {

		// Check whether we're in Dashboard or not
		var endpoint;
		if (typeof ajaxurl !== 'undefined') {
			endpoint = ajaxurl;
		} else {
			endpoint = wpas.ajaxurl;
		}

		$.post(endpoint, getApiKey, function (response) {
			var o = $.parseJSON(response);
			filepicker.setKey(o.api_key);

			// Security Enabled
			if (typeof o.policy !== 'undefined' || typeof o.signature !== 'undefined') {

				button.click(function (event) {

					event.preventDefault();
					filepicker.pickMultiple({
							policy: o.policy,
							signature: o.signature,
							extensions: o.extensions,
							services: o.services,
							maxSize: o.max_size,
							maxFiles: o.max_files
						},
						function (InkBlob) {
							$.each(InkBlob, function (i, upload) {
								uploads.push(upload);
								var dataGetSecureUrl = {
									'action': 'filepicker_get_secured_url',
									'url': upload.url
								};
								$.post(endpoint, dataGetSecureUrl, function (secureUrl) {
									uploadList.append('<li><a href="' + secureUrl + '" target="_blank">' + upload.filename + '</a> <span class="wpas-upload-size">' + bytesToSize(upload.size) + '</span></li>');
								});
							});
							// Show the list of uploaded files
							uploadList.show();
							// Populate the hidden input
							input.val(encodeURIComponent(JSON.stringify(uploads)));
							// Hide the upload button if max_files is reached
							if (uploads.length >= o.max_files) {
								button.off('click').text(maxfilesAlert).css({
									color: 'red',
									textDecoration: 'none'
								});
							}
						}
					);
				});
			}

			// Security disabled
			else {
				button.click(function (event) {
					event.preventDefault();
					filepicker.pickMultiple({
							extensions: o.extensions,
							services: o.services,
							maxSize: o.max_size,
							maxFiles: o.max_files
						},
						function (InkBlob) {
							$.each(InkBlob, function (i, upload) {
								uploads.push(upload);
								uploadList.append('<li><a href="' + upload.url + '" target="_blank">' + upload.filename + '</a> <span class="wpas-upload-size">' + bytesToSize(upload.size) + '</span></li>');
							});
							// Show the list of uploaded files
							uploadList.show();
							// Populate the hidden input
							input.val(encodeURIComponent(JSON.stringify(uploads)));
							// Hide the upload button if max_files is reached
							if (uploads.length >= o.max_files) {
								button.off('click').text(maxfilesAlert).css({
									color: 'red',
									textDecoration: 'none'
								});
							}
						}
					);
				});
			}

		});
	}

});