( function( $ ) {
	"use strict";

	var	emailField       = $( '#wpas_gt_email' ),
		infoDiv          = $( '#wpas_gt_info_div' ),
		statusDiv        = $( '#wpas_gt_status_div' ),
		passwordBtnDiv   = $( '.wpas_gt_password_div' ),
		loginBtnDiv      = $( '.wpas_gt_login_div'),
		guestBtnDiv      = $( '.wpas_gt_guest_div'),
		privacyBtnDiv    = $( '.wpas_gt_privacy_div'),
		formAction       = $('input[name="form-action" ]'),
		submitted        = false;

	/**
	 * Check user email, if user is registered show login form fields
	 */
	emailField.on( 'input', function( ) {

		var email = $( this ).val();

		// show register fields if email is empty
		if ( email == '' ) {
			showRegisterFields();
		}
		
		if ( email.length > 6 ) {

			$.post( gtl10n.ajaxurl, {
				action : 'gt_check_user',
				email: email,
				dataType: 'json'
			}).done(function( data ) {

				if ( data.registered ) {
					showLoginFields();
				} else {
					showRegisterFields();
				}

			}).fail(function( data ){
				console.log(data.responseText);
			});

		}

	} );


	/**
	 * Show fields required for user login
	 */
	function showLoginFields() {

		passwordBtnDiv.show();
		loginBtnDiv.show();
		guestBtnDiv.hide();
		privacyBtnDiv.hide();
		formAction.val('login');

		infoDiv.html( gtl10n.existingUserMsg );
		
	}

	
	/**
	 * Show fields required for user registration
	 */
	function showRegisterFields() {

		passwordBtnDiv.hide();
		loginBtnDiv.hide();
		guestBtnDiv.show();
		privacyBtnDiv.show();
		formAction.val('register');

		infoDiv.html('');

	}


	/**
	 * Submit form if recaptcha is ok
	 */
	window.gt_recaptcha_callback = function( token ) {
		// reset captcha
		grecaptcha.reset();
		
		$( '#wpas_form_guest_login' ).submit();
	};

	/**
	 * Form action login/register user
	 */
	$( '#wpas_form_guest_login' ).on( 'submit', function( e ) {

		e.preventDefault();

		// bail out if user is already subbmited this form
		if ( submitted ) {
			return; 
		}

		// set flag
		submitted = true;

		// parameters to be sent
		var params = {
			dataType : 'json'
		};

		statusDiv.removeAttr('class').html('<img src="' + gtl10n.adminurl + 'images/loading.gif">');

		if ( formAction.val() == 'login' ) {

			params.action   = 'gt_auth_user';
			params.email    = $('input[name="wpas_gt_email"]').val();
			params.password = $('input[name="wpas_gt_pass"]').val();

		} else {

			params.action   = 'gt_register_user';
			params.email    = $('input[name="wpas_gt_email"]').val();
			params.privacy_notice = $('input[name="privacy_notice"]').is(':checked') ? 1 : 0;

		}

		$.post( gtl10n.ajaxurl, params).done(function( data ) {

			submitted = false;

			if ( data.status ) {
				location.replace( data.url );
			} else {
				statusDiv.addClass('wpas-alert wpas-alert-danger').html( data.message );
			}

		}).fail(function( data ){
			console.log(data.responseText);
		});
	
	} );


	// show privacy notice window
	$(document).on('click', '.wpas-gt-show-notice', function(e) {

		e.preventDefault();

		$('body').addClass('wpas-overflow-hidden');    
		$('.wpas-gt-notice-box').fadeIn();

	});

	// close privacy notice window
	$(document).on('click', '.wpas-gt-close', function(e) {

		e.preventDefault();

		$('body').removeClass('wpas-overflow-hidden');    
		$('.wpas-gt-notice-box').fadeOut();

		$('input[name="privacy_notice"]').attr('checked', false);

	});


	// accept the terms
	$(document).on('click', '.wpas-gt-accept', function(e) {

		e.preventDefault();

		$('body').removeClass('wpas-overflow-hidden');    
		$('.wpas-gt-notice-box').fadeOut();

		$('input[name="privacy_notice"]').attr('checked', true);


	});

} )( jQuery );
