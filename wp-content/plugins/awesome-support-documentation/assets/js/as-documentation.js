jQuery.noConflict();

jQuery(document).ready(function ($) {

	var docCollapse = $('#as-collapse'),
		docMain = $('.doc-main');

	if (docCollapse.length) {

		/*
		jQuery Collapsible
		http://github.com/danielstocks/jQuery-Collapse/
		 */
		docCollapse.collapse({
			accordion: true,
			open: function () {
				this.slideDown(150);
			},
			close: function () {
				this.slideUp(150);
			}
		});


		/*
		AJAX to load content
		http://codex.wordpress.org/AJAX_in_Plugins
		http://html5doctor.com/history-api/
		 */
		docCollapse.on('click', 'a', function (event) {
			event.preventDefault();

			// Prepare AJAX variables
			var data = {
				'action': 'wpas_get_doc',
				'id': $(this).attr('id')
			};

			// Update the UI
			$(this).addClass('as-collapse-active').siblings().removeClass('as-collapse-active');
			docMain.addClass('as-doc-loading');

			$.post(wpas.ajaxurl, data, function (response) {
				// JSON to Object
				response = $.parseJSON(response);

				// Populate the content
				docMain.removeClass('as-doc-loading').html(response.html);

				// Update the URL
				history.pushState(null, response.title, response.permalink);
			});
		});

	}

});