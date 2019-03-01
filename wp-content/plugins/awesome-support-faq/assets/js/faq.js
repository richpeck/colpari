jQuery(document).ready(function ($) {

	/**
	 * Fancy FAQs with jQuery Sliders
	 * http://davidwalsh.name/jquery-sliders
	 */
	$('.asfaq-shortcode-wrapper h3').each(function () {
		var tis = $(this),
			state = false,
			answer = tis.next('div').slideUp();
		tis.on('click', function () {
			state = !state;

			// Update the UI
			answer.slideToggle(state);
			tis.toggleClass('active', state);

			// Count the view
			asfaq_count_view_ajax(tis);
		});
	});

	/**
	 * AJAX Count View
	 * Count the clicks when expanding a FAQ (only once)
	 */
	var alreadyClicked = [];

	function asfaq_count_view_ajax(tis) {
		var id = tis.attr('data-id');
		var data = {
			'action': 'asfaq_count_view',
			'faq_id': id
		};

		if ($.inArray(id, alreadyClicked) == -1) {
			alreadyClicked.push(id);
			$.post(asfaq.ajaxurl, data);
		}
	}

	/**
	 * Live Search
	 * http://jsfiddle.net/umaar/t82gZ/
	 */
	var searchInput = $('#asfaq_sc_search_input'),
		searchCount = $('#asfaq_sc_search_count'),
		searchClear = $('#asfaq_sc_search_clear'),
		searchItems = $('.asfaq_item');

	searchInput.keyup(function () {

		// Retrieve the input field text and reset the count to zero
		var filter = $(this).val(),
			count = 0;

		// Loop through the FAQ list
		searchItems.each(function () {

			// If the list item does not contain the text phrase fade it out
			if ($(this).text().search(new RegExp(filter, 'i')) < 0) {
				$(this).addClass('asfaq_item_has_result').fadeOut();
			}
			// Show the list item if the phrase matches and increase the count by 1
			else {
				$(this).removeClass('asfaq_item_has_result').show();
				count++;
			}

		});

		// Update the count
		var countText;
		count >= 2 ? countText = 'results found.' : countText = 'result found.';
		searchCount.text(count + ' ' + countText);
	});

	// Clear the search input
	searchClear.on('click', function (event) {
		event.preventDefault();
		searchInput.val('').trigger('keyup');
	});

});