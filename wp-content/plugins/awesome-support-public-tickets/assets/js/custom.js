jQuery(document).ready(function(){

	jQuery("#pbtk_ticket_filter li a").click(function() {
		var filter_view = jQuery(this).attr('class');
		
		if( filter_view =='ticket_accordion'){
			jQuery(".pbtk_ticket_list").hide();
			jQuery(".accordion_view").show();
		}else if(filter_view =='ticket_list'){

			jQuery(".accordion_view").hide();
			jQuery(".pbtk_ticket_list").show();			
			jQuery(".pbtk_ticket_list").removeClass('ticket_grid_view');
			jQuery(".pbtk_ticket_list").addClass(filter_view+'_view');
		}else{
			jQuery(".accordion_view").hide();
			jQuery(".pbtk_ticket_list").show();			
			jQuery(".pbtk_ticket_list").removeClass('ticket_list_view');
			jQuery(".pbtk_ticket_list").addClass(filter_view+'_view');
		}
	});

    jQuery(function() {
		jQuery( ".accordion_view" ).accordion({
			collapsible: true
		});
	});

	/**
	 * Mark as public/private
	 */
	jQuery(document.body).on('click', '.wpas-mark-private' ,function( event ){
		event.preventDefault();
		var btn = jQuery(this),
			repID = jQuery(this).attr('id'),
			replyID = jQuery(this).data('replyid'),
			replyStatus = jQuery(this).data('status'),
			data = {
				'action': 'wpas_mark_reply_private',
				'reply_id': replyID,
				'reply_status': replyStatus
			};

		jQuery.post(ajaxurl, data, function (response) {
			if(response == 'private')
			{
				jQuery('#'+repID).text( ascs.public_txt );
				jQuery('#'+repID).attr('data-status','public');
				jQuery('#'+repID).data('status','public');
			}else if(response == 'public'){

				jQuery('#'+repID).text( ascs.private_txt );
				jQuery('#'+repID).attr('data-status','private');
				jQuery('#'+repID).data('status','private');
			}
		});
	});
	
	/*!
	* Change display style
	*/
	
	jQuery('#ticket_filter a').click(function(){
		var cls = jQuery(this).attr('class');
		jQuery('#listtype').val(cls);
	});
	
	/*!
	* Reset Search form
	*/
	
	jQuery('.pbtk_reset').click(function(){
		jQuery('.pbtk-search-form .aspbtk-search-field').val("");		
		jQuery('.pbtk-search-form').submit();		
		
	});
});