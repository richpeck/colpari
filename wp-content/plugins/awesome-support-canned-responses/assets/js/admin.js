jQuery(document).ready(function ($) {

	'use strict';

	/*
	References
	http://codeblow.com/questions/method-to-check-whether-tinymce-is-active-in-wordpress/
	https://wordpress.org/support/topic/tinymceactiveeditorgetcontentcontent-does-not-work-with-tinymce-advanced
	 */

	var cannedResponse, reply, replyWrap;

	reply = $('#wpas_reply');
	replyWrap = $('#wp-wpas_reply-wrap');

	$('.wpas-canned-response').click(function (e) {
		e.preventDefault();

		/* Check if canned response is empty */
		if ($(this).attr('data-message')) {

			cannedResponse = $(this).attr('data-message');

			/* Check which editor is active TinyMCE or HTML */
			if (replyWrap.hasClass('tmce-active')) {

				/* Check if TinyMCE is loaded */
				if (typeof (tinyMCE) != 'undefined') {

					/* Check version of TinyMCE */
					if (tinymce.majorVersion < 4) {
						tinyMCE.execInstanceCommand('wpas_reply', 'mceInsertContent', false, cannedResponse);
					} else {
						tinyMCE.get('wpas_reply').insertContent(cannedResponse);
					}

				}

			} else {

				/* Append canned response to existing textarea value */
				reply.val(function (i, val) {
					return val + cannedResponse;
				});

			}

		} else {
			alert('This canned response is empty.');
		}

	});

});