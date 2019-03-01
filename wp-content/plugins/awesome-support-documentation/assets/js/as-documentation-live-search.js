jQuery(document).ready(function ($) {

	/**
	 * Define variables & Cache selectors
	 */
	var timeout;
	var container = $('<div class="asdoc-results"></div>');
	var selectors = asdoc.settings.selectors;

	/**
	 * Custom Helper Function
	 * http://stackoverflow.com/a/1909508
	 */
	var delay = (function () {
		var timer = 0;
		return function (callback, ms) {
			clearTimeout(timer);
			timer = setTimeout(callback, ms);
		};
	})();

	/**
	 * Live Search AJAX WordPress
	 * https://codex.wordpress.org/AJAX_in_Plugins
	 */
	$.each(selectors, function (index, val) {
		var selector = $(val.replace(/\\/g, ''));
		if (selector.length) {

			// Append search results container right after the input
			selector.after(container);

			selector.on('keyup', function (event) {

				event.preventDefault();

				// Define variables & Cache selectors
				var charsMin = asdoc.settings.chars_min;
				var el = this;
				var value = this.value;
				var strLength = value.length;

				// Hold on for some time between each keyup
				delay(function () {

					// Search query needs minimum XX characters
					if (value && strLength >= charsMin) {

						var data = {
							'action': 'asdoc_search_doc',
							'asdoc_term': value
						};

						$.post(asdoc.ajaxurl, data, function (response) {
							// Check if AJAX response is empty
							if (response.length) {

								var docs = $.parseJSON(response);
								var dynamicItems = "";

								// Check if the object is empty
								if (!$.isEmptyObject(docs)) {
									// Loop through the DOCS
									$.each(docs, function (i, val) {
										dynamicItems += '<a title="Click to view the documentation answer" target="' + asdoc.settings.link_target + '" href="' + val.link + '">' + val.title + '</a>';
									});
									// Append only once
									container.html(dynamicItems).show();
								}
							}
						});

					} else {
						// Clear the previous results
						container.html('').hide();
					}

				}, asdoc.settings.delay);

			});

		}
	});

});