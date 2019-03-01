jQuery(document).ready(function($) {

	/*
	*
	* Get the settings for the checkbox/select/radio field
	*
	*/
	function getFieldTypeOptions( fieldType ) {
		var fieldTypeOptions = [];
		$( fieldType ).find('.wpas-cf-options-wrapper .wpas-cf-option').each(function(index, option) {
			var optionId = $( option ).find('.wpas-cf-option-id').val();
			var optionLabel = $( option ).find('.wpas-cf-option-label').val();
			fieldTypeOptions[optionId] = optionLabel;
		});
		return $.extend( {}, fieldTypeOptions );
	}

	/*
	*
	* Get the options for the textarea field
	*
	*/
	function getTextareaOptions( textarea ) {
		var textareaOptions = {}
		var rows = $( textarea ).find('.wpas-cf-textarea-options-wrapper .wpas-cf-textarea-rows').val();
		var cols = $( textarea ).find('.wpas-cf-textarea-options-wrapper .wpas-cf-textarea-cols').val();
		textareaOptions.rows = rows;
		textareaOptions.cols = cols;
		return textareaOptions;
	}

	/*
	*
	* Get the settings for WYSIWYG field
	*
	*/
	function getWysiwygSettings ( wysiwyg ) {
		var wysiwygOptions = [];
		$( wysiwyg ).find('.wpas-cf-wysiwyg-options-wrapper .wpas-cf-option').each(function(index, option) {
			var optionId = $( option ).find('.wpas-cf-option-id').val();
			var optionLabel = $( option ).find('.wpas-cf-option-label').val();
			wysiwygOptions[optionId] = optionLabel;
		});
		return $.extend( {}, wysiwygOptions );
	}

	/*
	*
	* Get the data for the field from the input/select elements
	*
	*/
	function getFieldsData() {
		var fieldsData = [];
		$('.wpas-cf-wrapper-object').each(function(index, element) {
			var fieldData = {};
			var field_type = $( element ).find('.wpas-cf-field-type-field select').val();
			var text_area_options = getTextareaOptions( element );

			fieldData.title = $( element ).find('.wpas-cf-title-field input').val();
			fieldData.name = $( element ).find('.wpas-cf-name-field input').val();
			fieldData.field_type = field_type;
			fieldData.order = $( element ).find('.wpas-cf-options-field-order .wpas-cf-field-order').val();
			fieldData.hide =( $( element ).find('.wpas-cf-options-hide input').prop('checked') ) ? 'yes': 'no';	
			fieldData.placeholder = $( element ).find('.wpas-cf-placeholder-field input').val();
			fieldData.default = $( element ).find('.wpas-cf-default-field input').val();
			fieldData.required = $( element ).find('.wpas-cf-required-field select').val();
			fieldData.log = $( element ).find('.wpas-cf-log-field select').val();
			fieldData.show_column = $( element ).find('.wpas-cf-show-column-field select').val();
			fieldData.sortable_column = $( element ).find('.wpas-cf-sortable-column-field select').val();
			fieldData.filterable = $( element ).find('.wpas-cf-filterable-field select').val();
			fieldData.capability = $( element ).find('.wpas-cf-capability-field input').val();
			fieldData.desc = $( element ).find('.wpas-cf-desc-field input').val();
			fieldData.html5_pattern = $( element ).find('.wpas-cf-html5-pattern-field input').val();
			fieldData.select2 = $( element ).find('.wpas-cf-select2-field select').val();
			fieldData.backend_only = $( element ).find('.wpas-cf-backendonly-field select').val();
			fieldData.readonly = $( element ).find('.wpas-cf-readonly-field select').val();
			fieldData.show_frontend_list = $( element ).find('.wpas-cf-show-frontend-list-field select').val();
			fieldData.show_frontend_detail = $( element ).find('.wpas-cf-show-frontend-detail-field select').val();			

			if( field_type == 'checkbox' || field_type == 'radio' || field_type == 'select' ){
				fieldData.options = getFieldTypeOptions( element );
			}

			if( field_type == 'textarea' ){
				fieldData.rows = text_area_options.rows;
				fieldData.cols = text_area_options.cols;
			}
			
			if( field_type == 'upload' ) {
				fieldData.multiple =  $( element ).find('.wpas-cf-textarea-upload-wrapper input').prop('checked');	
			}

			if( field_type == 'wysiwyg' ) {
				fieldData.settings = getWysiwygSettings( element );
			}

			if( field_type == 'taxonomy' ) {
				fieldData.taxo_std = $( element ).find('.wpas-cf-taxonomy-taxo-std').val();
				fieldData.label = $( element ).find('.wpas-cf-taxonomy-label').val();
				fieldData.label_plural = $( element ).find('.wpas-cf-taxonomy-label-plural').val();
				fieldData.taxo_hierarchical = $( element ).find('.wpas-cf-taxonomy-taxo-hierarchical').val();
			}
			fieldsData.push( fieldData );
		});
		return fieldsData;
	}

	/*
	*
	* Show notification for success on top of the page.
	*
	* @param message - the message to be displayed
	*/
	function notifySuccess( message ) {
		$('.wpas-cf-notification-content').text( message );		
		$('.wpas-cf-notification').css({
			width: '100%',
			'background-color': '#41a943',
			'border-color': '#d6e9c6',
	    	'border-radius': '5px',
		}).show();
		$('html body').animate( {scrollTop: 0}, 'slow' );
	}

	/*
	*
	* Show notification for failure on top of the page.
	*
	* @param message - the message to be displayed
	*/
	function notifyFailure( message ) {
		$('.wpas-cf-notification-content').text( message );		
		$('.wpas-cf-notification').css({
			width: '100%',
			'background-color': '#BD3333',
			'border-color': '#B90D0D',
	    	'border-radius': '5px',
		}).show();
		$('html body').animate( {scrollTop: 0}, 'slow' );
	}

	/*
	*
	* Get all fields via ajax and add them to .wpas-cf-table
	*
	*/
	function loadAllFields() {
		$.post( 
			ajaxurl, 
			{
				action: 'ascf_all_fields'
			}
		)
		.done( function( markup ) {
			$('.wpas-cf-wrapper-object').remove();
			$('.wpas-cf-table').append( markup );
		})
	}

	/*
	*
	* Save the fields
	*
	*/
	function saveCustomFields() {
		var fieldsData = getFieldsData();
		$.post(
			ajaxurl,
			{ 
				action : 'ascf_save_fields',
				fields : fieldsData,
			}
		)
		.done( function( response ) {
			if( response.success == true ) {
				loadAllFields();
				notifySuccess( response.data.message );
			} else {
				notifyFailure( response.data.message );
			}
		});
	}

	/*
	*
	* Save the fields when form#wpas-as-form is submitted 
	*
	*/
	$('#wpas-as-form').submit(function(event) {
		event.preventDefault();
		saveCustomFields();
	});

	/*
	*
	* Show the markup for adding new field
	*
	*/
	$("#wpas-cf-add-btn").click(function(){ 
		$.post(
			ajaxurl,
			{
				action : 'ascf_add_field'
			}
		)
		.done( function( data ) {
			$('.wpas-cf-table').append( data );
		});
	});

	/*
	*
	* Get the markup for option
	*
	*/
	function getOptionMarkup() {
		return  '<div class="wpas-cf-option">\
					<input class="wpas-cf-option-id" type="text" placeholder="' + option.option_id + '">\
					<input class="wpas-cf-option-label" type="text" placeholder="' + option.option_label + '">\
					<button id="wpas-cf-remove-option" class="button-secondary">' + option.remove_button + '</button>\
				</div>';
	}

	/*
	*
	* Append the markup for the options on checkbox/radio/select field
	*
	*/
	function appentOptionMarkup( option ) {
		var option_markup = getOptionMarkup();
		option.find('.wpas-cf-options').show();
		option.find('.wpas-cf-options .wpas-cf-options-wrapper').append( option_markup );
	}

	/*
	*
	* Append the options for the field type which is selected
	*
	*/
	$('table.wpas-cf-table').on( 'change', '.wpas-cf-field-type-field select', function() {
		var field_type = $(this).val();

		if( field_type == 'checkbox' || field_type == 'radio' || field_type == 'select' ) {
			var selector = $(this).closest('tbody');
			selector.find('.wpas-cf-options .wpas-cf-options-wrapper').html('');
			appentOptionMarkup( selector );
		} else {
			 $(this).closest('tbody').find('.wpas-cf-options').hide();
		}

		if( field_type == 'textarea' ) {
			$(this).closest('tbody').find('.wpas-cf-options-textarea').show();	
		} else {
			$(this).closest('tbody').find('.wpas-cf-options-textarea').hide();	
		}

		if( field_type == 'upload' ) {
			$(this).closest('tbody').find('.wpas-cf-options-upload').show();	
		} else {
			$(this).closest('tbody').find('.wpas-cf-options-upload').hide();	
		}

		if( field_type == 'wysiwyg' ) {
			$(this).closest('tbody').find('.wpas-cf-options-wysiwyg').hide();
		} else {
			$(this).closest('tbody').find('.wpas-cf-options-wysiwyg').hide();
		}

		if( field_type == 'taxonomy' ) {
			var selector = $(this).closest('tbody');
			selector.find('.wpas-cf-options-taxonomy').show();
		} else {
			$(this).closest('tbody').find('.wpas-cf-options-taxonomy').hide();
		}


	});

	$('table.wpas-cf-table').on( 'click', '#wpas-cf-add-option', function() {
		var selector = $(this).closest('tbody');
		appentOptionMarkup( selector );
	});

	$('table.wpas-cf-table').on( 'click', '#wpas-cf-remove-option', function () {
		$(this).closest('.wpas-cf-option').remove();
	});

	$('table.wpas-cf-table').on( 'click', '#wpas-cf-remove', function () {
		$(this).closest('tbody').remove();
	});

	$('table.wpas-cf-table').on( 'click', '#wpas-cf-add-wysiwyg-option', function() {
		var option_markup = getOptionMarkup();
		$(this).closest('tbody').find('.wpas-cf-options-wysiwyg .wpas-cf-wysiwyg-options-wrapper').append( option_markup );
	});

});
