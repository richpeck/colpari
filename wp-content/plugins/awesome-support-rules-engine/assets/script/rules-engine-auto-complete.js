jQuery( document ).ready( function() {
	
	var client_attrs = AS_Rules_Engine.client_attrs;
	if( client_attrs.length !== 0 ){
		jQuery( "div.condition_client_attrs .condition_field_right" ).append( client_attrs );
	}

	var agent_attrs = AS_Rules_Engine.agent_attrs;
	if( agent_attrs.length !== 0 ){
		jQuery( "div.condition_agent_attrs .condition_field_right" ).append( agent_attrs );
	}

	var custom_fields = AS_Rules_Engine.custom_fields;
	if( custom_fields !== null  ){
		jQuery( "div.condition_custom_field .condition_field_right" ).append( custom_fields );
	}

	jQuery( ".action_send_email" ).autocomplete({
		source: ajaxurl+'?action=get_as_agents_email',
		select: function( event, ui ) {
		    jQuery(".action_send_email").val(ui.item.value);
		    return false;
		}
	});


	jQuery(document).on('change', "select.condition_client_attrs", function(){
		var input = jQuery(this).nextAll("input.condition_client_attrs_value");
		jQuery.ajax({
			url: ajaxurl+'?action=get_client_attr_value',
			data: { attr_field : this.value},
			success: function( data ){
			    input.val( data );
			}
		});
	})

	jQuery(document).on('change', "select.condition_agent_attrs", function(){
		var input = jQuery(this).nextAll("input.condition_agent_attrs_value");
		jQuery.ajax({
			url: ajaxurl+'?action=get_agent_attr_value',
			data: { attr_field : this.value},
			success: function( data ){
			    input.val( data );
			}
		});
	})

	jQuery("select.condition_client_caps_fields").on('change', function(){
		var input = jQuery(this).nextAll("input.condition_client_caps_value");
		jQuery.ajax({
			url: ajaxurl+'?action=get_client_attr_value',
			data: { attr_field : this.value},
			success: function( data ){
				input.val( data );
			}
		});
	})


	jQuery(document).on('click', "button.add_extra_client_attr", function(){
		jQuery(this).parent().append( AS_Rules_Engine.client_attrs_default );
	});

	jQuery(document).on('click', 'button.remove_extra_client_attr', function(){
			
		$this_element = jQuery(this);
		var post_id = $this_element.attr('post-id');
		var metaid = $this_element.attr('id');
		$this_element.html('<img src="images/wpspin_light.gif" width="10" />');
		if(post_id !== '' && metaid !== '#'){
			jQuery.ajax({
				url: AS_Rules_Engine.ajax_url,
				type : 'post',
				data: {
					'post_id': post_id,
					'metaid' : metaid,
					'action' : 'remove_client_attr_extra',
				},
				success: function( data ){
					if(data == 'success'){
						$this_element.parent().remove();
					}
				}
			});
		}else{
			$this_element.parent().remove();
		}
	});

	jQuery(document).on('click', "button.add_extra_agent_attr", function(){
		jQuery(this).parent().append( AS_Rules_Engine.agent_attrs_default );
	});

	jQuery(document).on('click', 'button.remove_extra_agent_attr', function(){
			
		$this_element = jQuery(this);
		var post_id = $this_element.attr('post-id');
		var metaid = $this_element.attr('id');
		$this_element.html('<img src="images/wpspin_light.gif" width="10" />');
		if(post_id !== '' && metaid !== '#'){
			jQuery.ajax({
				url: AS_Rules_Engine.ajax_url,
				type : 'post',
				data: {
					'post_id': post_id,
					'metaid' : metaid,
					'action' : 'remove_client_attr_extra',
				},
				success: function( data ){
					if(data == 'success'){
						$this_element.parent().remove();
					}
				}
			});
		}else{
			$this_element.parent().remove();
		}
	});



	jQuery(document).on('click', "button.add_extra_custom_field", function(){
		jQuery(this).parent().append( AS_Rules_Engine.custom_field_default );
	});

	jQuery(document).on('click', 'button.remove_extra_custom_field', function(){
		
		$this_element = jQuery(this);
		var post_id = $this_element.attr('post-id');
		var metaid = $this_element.attr('id');
		$this_element.html('<img src="images/wpspin_light.gif" width="10" />');
		if(post_id !== '' && metaid !== '#'){
			jQuery.ajax({
				url: AS_Rules_Engine.ajax_url,
				type : 'post',
				data: {
					'post_id': jQuery(this).attr('post-id'),
					'metaid' : jQuery(this).attr('id'),
					'action' : 'remove_custom_field_extra',
				},
				success: function( data ){
					if(data == 'success'){
					    $this_element.parent().remove();
					}
				}
			});
		}else{
			$this_element.parent().remove();
		}
	});

});


