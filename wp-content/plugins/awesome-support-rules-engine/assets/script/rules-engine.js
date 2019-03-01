jQuery( document ).ready( function() {

	jQuery("#metabox-conditions .condition-operators").each(function(){
		if(jQuery(this).val() && 'default' != jQuery(this).val() ){
			jQuery(this).parent('.condition').addClass('condition_highlight');
		}
	});

	jQuery("#metabox-conditions").on('change', ".condition-operators", function(){
		if(jQuery(this).val() && 'default' != jQuery(this).val() ){
			if( ! jQuery(this).parent('.condition').hasClass('condition_highlight') ){
				jQuery(this).parent('.condition').addClass('condition_highlight');
			}
		}else{
			jQuery(this).parent('.condition').removeClass('condition_highlight');
		}
	} );

	jQuery( '.duplicate-ruleset' ).live( 'click', function( e ) {		
		e.preventDefault();
		
		var data = {
			action: 'rs_duplicate',
			original_id: jQuery(this).data('postid')
		};
		
		jQuery.post( ajaxurl, data, function( response ) {
			var location = window.location.href;
			if( location.split('?').length > 1 ) {
				location = location + '&rs-duplicated='+response;
			} else {
				location = location + '?rs-duplicated='+response;
			}
			
			window.location.href = location;
		});
		
	});

	if ( jQuery("#mytabs").length ) {
		jQuery("#mytabs .hidden").removeClass('hidden');
    	jQuery("#mytabs").tabs();
	}

	// for custom date field provide greater and less then option to make condition for date.
	jQuery('.condition_field_right').on( 'change', '.condition_custom_field', function(){
		var field_type = jQuery('option:selected', this).attr('data-field_type');
		if( 'date-field' === field_type ){
			jQuery(this).next('select').next('input').attr('type', 'date');
			// If it is date field, then hide regex, contain options
			jQuery(this).next('select').find('option').each(function( key, value ){
				var field_type = jQuery(value).val();
				if( 'starts' == field_type || 'contains' == field_type || 'regex' == field_type ){
					jQuery(this).attr( 'disabled', 'disabled' );
				} else{
					jQuery(this).removeAttr('disabled');
				}
			});
		}else{
			jQuery(this).next('select').next('input').attr('type', 'text');
			// If it is date field, then hide greater, less options
			jQuery(this).next('select').find('option').each(function( key, value ){
				var field_type = jQuery(value).val();
				if( '>' == field_type || '<' == field_type ){
					jQuery(this).attr( 'disabled', 'disabled' );
				} else{
					jQuery(this).removeAttr('disabled');
				}
			});
		}
	});

	jQuery("#send-email-tab .condition").each(function(){
		var test = jQuery(this).prev(".multiple-recipent-checkbox").length;
		if(jQuery(this).prev(".multiple-recipent-checkbox").length){
			var checkbox_html = jQuery(this).prev(".multiple-recipent-checkbox").html();
			jQuery(this).find('label').prepend( checkbox_html );
			jQuery(this).prev(".multiple-recipent-checkbox").remove();
		}
	});
});

function sendZap( btn ){
	var zapurl = jQuery('input[name="action_zapier_notification"]').val();
	var zap_data = jQuery('#rules-testzap-data').val(); 	
	jQuery(btn).attr('disabled','disabled');
	jQuery('#message-status').text('Sending...');

	if ( isValidURL(zapurl) ) {

		jQuery.ajax({
			url: zapurl,
			type: 'POST',
			dataType: 'json',
			data: zap_data,
			complete: function(xhr, textStatus) {
				jQuery(btn).removeAttr('disabled');
			},
			success: function(data, textStatus, xhr) {
				//called when successful
				if ( data.status == 'success' ) {
					jQuery('#message-status').html('<b>Success!</b> your zap is validated!');
				} else {
					jQuery('#message-status').html('<b>Error!</b> we cannot validate your zap.');
				}
			},
			error: function(xhr, textStatus, errorThrown) {
				//called when there is an error
				jQuery('#message-status').html(errorThrown);
			}
		});

	} else {

	    jQuery(btn).removeAttr('disabled');
	    jQuery('#message-status').html('<b>Error!</b> The URL you\'ve given is not valid.');
	}
	
}

function isValidURL(str) {
	var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
	return regexp.test(str);  
}
