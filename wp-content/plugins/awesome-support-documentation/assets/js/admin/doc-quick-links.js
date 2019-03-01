jQuery(document).ready(function ($) {

	'use strict';

	/*
	References
	http://codeblow.com/questions/method-to-check-whether-tinymce-is-active-in-wordpress/
	https://wordpress.org/support/topic/tinymceactiveeditorgetcontentcontent-does-not-work-with-tinymce-advanced
	 */

	var linkToDOC,
		reply = $('#wpas_reply'),
		replyWrap = $('#wp-wpas_reply-wrap'),
		select = $('.asdoc_quick_reply_select');

	select.on('change', function (e) {
		e.preventDefault();

		/* Check if documentation item/response is empty */
		if (this.value) {

			linkToDOC = this.value;

			/* Check which editor is active TinyMCE or HTML */
			if (replyWrap.hasClass('tmce-active')) {

				/* Check if TinyMCE is loaded */
				if (typeof (tinyMCE) != 'undefined') {

					/* Check version of TinyMCE */
					if (tinymce.majorVersion < 4) {
						tinyMCE.execInstanceCommand('wpas_reply', 'mceInsertContent', false, linkToDOC);
					} else {
						tinyMCE.get('wpas_reply').insertContent(linkToDOC);
					}

				}

			} else {

				/* Append documentation item response to existing textarea value */
				reply.val(function (i, val) {
					return val + linkToDOC;
				});

			}

		} else {
			alert('This documentation item is empty.');
		}
	});

});