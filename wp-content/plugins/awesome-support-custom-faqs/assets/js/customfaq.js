jQuery(document).ready(function ($) {

	var	shortcodeWrapper = $( '.ascustomfaq-shortcode-wrapper h3' ),
		archiveWrapper   = $( '.ascustomfaq-category-wrapper h3' ),
		kbWrapper        = $( '.ascustomfaq-kb-main span' );

	function accordionizeContent() {
		var tis = $(this),
			state = false,
			answer = tis.next('div').slideUp();
		tis.on('click', function () {
			state = !state;

			// Update the UI
			answer.slideToggle(state);
			tis.toggleClass('active', state);

			// Count the view
			ascustomfaq_count_view_ajax(tis);
		});
	}

	shortcodeWrapper.each( accordionizeContent );
	archiveWrapper.each( accordionizeContent );
	kbWrapper.each( accordionizeContent );

	/**
	 * AJAX Count View
	 * Count the clicks when expanding a CUSTOMFAQ (only once)
	 */
	var alreadyClicked = [];

	function ascustomfaq_count_view_ajax(tis) {
		var id = tis.attr('data-id');
		var data = {
			'action': 'ascustomfaq_count_view',
			'customfaq_id': id
		};

		if ($.inArray(id, alreadyClicked) == -1) {
			alreadyClicked.push(id);
			$.post(ascustomfaq.ajaxurl, data);
		}
	}

	/**
	 * Live Search
	 * http://jsfiddle.net/umaar/t82gZ/
	 */
	var searchInput = $('#ascustomfaq_sc_search_input'),
		searchCount = $('#ascustomfaq_sc_search_count'),
		searchClear = $('#ascustomfaq_sc_search_clear'),
		searchItems = $('.ascustomfaq_item');

	searchInput.keyup(function () {

		// Retrieve the input field text and reset the count to zero
		var filter = $(this).val(),
			count = 0;

		// Loop through the CUSTOMFAQ list
		searchItems.each(function () {

			// If the list item does not contain the text phrase fade it out
			if ($(this).text().search(new RegExp(filter, 'i')) < 0) {
				$(this).addClass('ascustomfaq_item_has_result').fadeOut();
			}
			// Show the list item if the phrase matches and increase the count by 1
			else {
				$(this).removeClass('ascustomfaq_item_has_result').show();
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

	var kbCategory = $( '.ascustomfaq-kb-category' );

	kbCategory.on( 'click', function( event ) {
		event.preventDefault();

		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				slug: $( this ).data( 'slug' ),
				action: 'get_category_customfaqs'
				// nonce: $( 'ascustomfaq.nonce,
			},
			dataType: "json",
			success: function( response ) {
				if ( response.success ) {
					var kbMain = $( '#ascustomfaq-kb-main' );

					kbMain.each( accordionizeContent );
					kbMain.html( response.data.output );

				}
				kbWrapper.each( accordionizeContent );

			}
		}).fail(function (response) {
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		});

	})
});
