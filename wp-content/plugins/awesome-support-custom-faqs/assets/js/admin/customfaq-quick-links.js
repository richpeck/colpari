jQuery(document).ready(function ($) {

	'use strict';

	/*
	References
	http://codeblow.com/questions/method-to-check-whether-tinymce-is-active-in-wordpress/
	https://wordpress.org/support/topic/tinymceactiveeditorgetcontentcontent-does-not-work-with-tinymce-advanced
	 */

	var linkToCUSTOMFAQ,
		reply = $('#wpas_reply'),
		replyWrap = $('#wp-wpas_reply-wrap'),
		select = $('.ascustomfaq_quick_reply_select');

	select.on('change', function (e) {
		e.preventDefault();

		/* Check if canned response is empty */
		if (this.value) {

			linkToCUSTOMFAQ = this.value;

			/* Check which editor is active TinyMCE or HTML */
			if (replyWrap.hasClass('tmce-active')) {

				/* Check if TinyMCE is loaded */
				if (typeof (tinyMCE) != 'undefined') {

					/* Check version of TinyMCE */
					if (tinymce.majorVersion < 4) {
						tinyMCE.execInstanceCommand('wpas_reply', 'mceInsertContent', false, linkToCUSTOMFAQ);
					} else {
						tinyMCE.get('wpas_reply').insertContent(linkToCUSTOMFAQ);
					}

				}

			} else {

				/* Append canned response to existing textarea value */
				reply.val(function (i, val) {
					return val + linkToCUSTOMFAQ;
				});

			}

		} else {
			alert('This CUSTOMFAQ is empty.');
		}
	});

});