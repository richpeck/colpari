jQuery(document).ready(function ($) {

	// Define variables
	var notification = $('<div class="as-esn as-esn-loading"><span class="as-esn-content">' + wpas_mails.checking_mails + '</span><span class="as-esn-close" aria-label="Close">&times;</span></div>').appendTo('#wpbody').hide(),
		notificationContent = notification.find('.as-esn-content'),
		notificationClose = notification.find('.as-esn-close'),
		ajaxdata = {
			'action': 'wpas_check_mails'
		};

	// Add notification (hidden by default) on every page
	notification.hide();

	// Close notification on click
	notification.on('click', notificationClose, function (e) {
		e.preventDefault();
		notification.hide();
	});

	// Hook into the heartbeat-send
	$(document).on('heartbeat-send', function (e, data) {
		data['wpas_check_mails_now'] = 'check';
	});

	// Listen for the custom event "heartbeat-tick" on $(document).
	$(document).on('heartbeat-tick', function (e, data) {

		if (data.wpas_check_mails_now === true) {

			wpasCheckMails();

		}

	});

	function wpasCheckMails() {
		// Show the "in progress" notification
		notification.removeClass('as-esn-success as-esn-error').addClass('as-esn-loading').show();
		notificationContent.html(wpas_mails.checking_mails);

		// AJAX request
		$.post(ajaxurl, ajaxdata, function (ajaxresponse) {
			ajaxresponse = $.parseJSON(ajaxresponse);

			// Add status class & message
			notification.removeClass('as-esn-loading').addClass('as-esn-' + ajaxresponse.status);
			notificationContent.html(ajaxresponse.content);

			// Automatically close success messages after 5s
			if (ajaxresponse.status == 'success') {
				setTimeout(function () {
					notification.fadeOut('slow');
				}, 8000);
			}
		});
	}

	$('#wp-admin-bar-ases_mail_fetch').on('click', function (event) {
		event.preventDefault();
		wpasCheckMails();
	});

	$('.wpas-mail-test-settings').on('click', function (event) {
		event.preventDefault();

		var button = $(this);
		var buttonDefault = button.data('old-state', button.html());
		var result = $('.wpas-mail-test-settings-result')
		var data = {
			action: 'ases_mailbox_test_connect'
		};

		// Change button label
		button.text(wpas_mails.testing).blur();

		// Remove previous test results
		result.hide();

		$.post(ajaxurl, data, function (data) {
			data = $.parseJSON(data);

			// Restore button to default
			button.html(button.data('old-state'));

			// Toggle class depending on status
			if (data.result === 0) {
				result.addClass('wpas-alert-danger').html(data.message).fadeIn(400);
			} else {
				result.addClass('wpas-alert-success').html(data.message).fadeIn(400);
			}
		});

	});
        
        if($('input[name=assign_action]').length === 1) {
                
                $('select[name=wpas_message_creator]').change(function() {
                        if($(this).val()) {
                                $('.assign_to_customer_btn').show();
                        } else {
                                $('.assign_to_customer_btn').hide();
                        }

                });
                
                $('select[name=wpas_parent_ticket]').change(function() {
                        if($(this).val()) {
                                $('.assign_to_ticket_btn').show();
                        } else {
                                $('.assign_to_ticket_btn').hide();
                        }

                });
                
                $('.assign_to_ticket_btn button, .assign_to_customer_btn button, .assign_btn').click(function() {
                        $('input[name=assign_action]').val($(this).data('assign_action'));
                        $(this).closest('form').submit();
                        
                });
                
        }
        
});