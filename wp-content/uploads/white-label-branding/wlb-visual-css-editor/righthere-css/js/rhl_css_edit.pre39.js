var get_css_value_callbacks = {};
var properties_with_units = [
		'top',
		'width',
		'height',
		'margin-top',
		'min-height',
		'font-size',
		'line-height',
		'border-top-left-radius',
		'border-top-right-radius',
		'border-bottom-left-radius',
		'border-bottom-right-radius',	
		'border-bottom-width',	
		'padding-top',
		'padding-right',
		'padding-bottom',
		'padding-left'
];

function rhl_css_edit_init(){
jQuery(document).ready(function($){
	if( rh_detect_selector && $(rh_detect_selector).length==0 ){
		$('.rh-css-edit, .rh-css-edit-form').css('display','none');
		return;
	}else{
		$('BODY').addClass('rhcss-editor-active');
	}

	//--fix-- on some themes the form does not gets placed on the root but inside other elements.
	if( ! $('.rh-css-edit-form').parent().is('BODY') ){
		$('.body-child').appendTo('BODY');
	}
	$('.rh-css-edit-form').show();
	
	//-- minicolors ----
	$('.input-minicolors').each(function(i,o){
		var opacity = $(o).hasClass('with-opacity')?true:false;
		$(o).miniColors({
			opacity: opacity,
			change: function(e){
				$(this).trigger('change');
			}
		});	
	});	
	//--- tooltip
	//not working.
	//$('.bootstrap-tooltip').tooltip({position:'fixed'});
	
	//clicks on the editor section should not pass to the site:
	$('.rh-css-edit-form').on('click',function(e){});
		
	//save button
	$('#btn-save').click(function(e){
		css_save(e);
	});
	
	//generic clear button
	$('.btn_clear_generic').click(function(e){
		$(this).parents('.input-field').find('.input-field-input').each(function(i,inp){	
			$(this).val('')
				.trigger('change')
				.trigger('fieldCleared')
			;
		});
	});
	
	//clear gradient button
	$('.btn_clear_gradient').click(function(e){
		$(this).parent()
			.find('input.sub_colorpicker_gradient')
				.val('')
				.css('background-color','#eee')
				.end()
			.find('input.colorpicker_gradient').val('').trigger('change').end()
		;
	});
	
	//text shadow clear button
	$('.btn_clear_text_shadow').click(function(e){
		$(this).parent().parent()
			.find('input.text-shadow-field').val('').end()
			.find('input.colorpicker_textshadow').val('').trigger('change').end()
		;
	});
	//text shadow none button
	$('.btn_none_text_shadow').click(function(e){
		$(this).parent().parent()
			.find('input.text-shadow-field').val('').end()
			.find('input.colorpicker_textshadow').val('none').trigger('change').end()
		;
	});
	
	//image url clear
	$('.btn_clear_image_url').click(function(e){
		var _value_to_set = $(this).data('value_to_set');
		_value_to_set = _value_to_set?_value_to_set:'';
		var _parent = $(this).parent().parent().parent();
		_parent.find('.rhl_image_uploader').val(_value_to_set).trigger('change');
		_parent.find('.rhl-image-upoader-helper').addClass('helper-closed');
	});
	//----------------
	
	//--
	fill_default_values();
	//$('#rh-modal-login').trigger('shown');
	
	$('#btn-reset-css').on('click',function(){
		restore_default_values();	
	});
	//----
	$('.pop_rangeinput').each(function(i,inp){
		var sel 	= $(inp).attr('data-css-selector');
		var arg 	= $(inp).attr('data-css-property');
		var unit	= $(inp).attr('data-input-unit');
		var id = $(inp).attr('id');
		
		$(inp).pop_rangeinput();
		if(sel)$('#'+id).attr('data-css-selector',sel);
		if(arg)$('#'+id).attr('data-css-property',arg);
		if(unit)$('#'+id).attr('data-input-unit',unit);	
	});
	$('.rhlogin-edit .handle').mousedown(function(e){
		$(this).parent().parent().find('.pop_rangeinput').focus();
	});
	
	$('.pop_rangeinput').bind('onSlide',function(e,value){
		var sel = $(this).attr('data-css-selector');
		var arg = $(this).attr('data-css-property');
		var unit = $(this).attr('data-input-unit');
		
		var val = normalize_css_value( arg, $(sel).css(arg) );
		//var new_val = normalize_input_value( this, arg, $(this).val() );
		var new_val = normalize_input_value( this, arg, value );
		new_val = unit?new_val + unit:new_val;
		
		$(sel).css(arg, new_val);
	});	
		
	//----
	
	$('.real-time').on('change',function(e){
		set_css_from_input_value(this);
		return true;
	});
	
	//-- gradient sub field change
	$('.sub_colorpicker_gradient').change(function(e){
		var colors = [];
		var last_val = '#ffffff';
		$(this).parents('.input-field').find('.sub_colorpicker_gradient').each(function(i,o){
			var val=$(o).val();
			if(val){
				//add opacity
				var opacity = $(o).miniColors('opacity');
				opacity = opacity>=0&&opacity<=1?opacity:1;
				if(opacity<1){
					var tiny = tinycolor(val);
					//var tiny = tinycolor(tiny.toRgbString());//convert internally to rgb.
					tiny.alpha = opacity;					
					if(tiny.ok){	
						val = tiny.toRgbString();				
					}
				}
			}else{
				val = last_val;
				$(o).val(val);
			}
			colors[colors.length]=val;
			last_val=val;
		});
		$(this).parents('.input-field').find('.input-field-input')
			.val( colors.join('|') )
			.trigger('change')
		;
	});
	
	//-- text-shadow sub field change
	$('.text-shadow-field').change(function(e){
		var p = $(this).parent().parent();
		//rgba(0, 0, 0, 0.247059) 0px -0.9090908765792847px 0px
		var arr = [];
		arr[0] = p.find('.text-shadow-h').val();
		arr[0] = arr[0]?arr[0]+'px':'0';  
		arr[1] = p.find('.text-shadow-v').val();
		arr[1] = arr[1]?arr[1]+'px':'0';
		arr[2] = p.find('.text-shadow-b').val();
		arr[2] = arr[2]?arr[2]+'px':'0';
		arr[3] = p.find('.text-shadow-color').val();
		if( arr[3]!='none' && arr[3]!='' && $(this).is('.with-opacity') ){
			var opacity = $(this).attr('data-opacity') ;
			opacity = opacity>=0&&opacity<=1?opacity:1;
			if(opacity<1){
				var tiny = tinycolor(arr[3]);
				tiny.alpha = opacity;					
				if(tiny.ok){	
					arr[3] = tiny.toRgbString();				
				}
			}			
		}		
		val = arr[3]=='none'?'none': (arr[3]==''?'':arr.join(' '));
		p.find('.colorpicker_textshadow')
			.val(val)
			.trigger('change')
		;
	});
	
	//-- image uploader helper
	$('.rhl-image-uploader-helper-trigger').on('click',function(e){
		$(this).parent().parent().parent().find('.rhl-image-upoader-helper').toggleClass('helper-closed');
	});
	
	//-- image uploader change
	$('.rhl_image_uploader').on('change',function(e){
		var val = $(this).val();
		var el = $(this).parent().find('.dropdown-content img');
		var status = $(this).parent().find('.dropdown-content .dropdown-status');
		var grad = $(this).parent().find('.dropdown-content .dropdown-gradient');
		var none = $(this).parent().find('.dropdown-content .dropdown-none');
		if( ''==val ){
			el.attr('src','').hide();
			status.show();
			grad.hide();
			none.hide();
		}else if( isURL(val) ){
			el.attr('src',val).show();			
			status.hide();
			grad.hide();
			none.hide();
		}else if( 'none'==val ){
			el.attr('src','').hide();
			status.hide();
			grad.hide();
			none.show();
		}else{
			el.attr('src','').hide();
			status.hide();
			grad.show();
			none.hide();
			
			var values = get_gradient_from_color(val,'background-image');
			if(false===values){
				var tiny = tinycolor(val);
				if(tiny.ok){
					//;
					var sel = $(this).attr('data-css-selector');
					var arg = $(this).attr('data-css-property');
					grad.css(arg, $(sel).css(arg) );
				}			
			}else{
	//			grad.css(values);
				$.each(values,function(i,v){
					grad.css(v);
				});
			}						
		}		
	});
	
	//-- background_image control
	$('.input-field-bakground_image').bind('UpdateChildControls',function(e){	
		var inp = this;
		var id = $(inp).attr('id');
		var val = $(inp).val();		
		var arr = val.split('|');
		if(arr.length==2){
			set_colorpicker_color( '#'+ id + '-start', arr[0]);
			set_colorpicker_color( '#'+ id + '-end', arr[1]);		
			$(inp).parents('.input-field').find('a.rhl-image-gradient')
				.tab('show')
			;
		}
	});

	//-- background_size control
	$('.input-field-input.background_size').bind('UpdateChildControls',function(e){
		var inp = this;
		var id = $(inp).attr('id');
		var val = $(inp).val();		
		var holder = $(this).parents('.input-field');
		if( holder.find('.bgsize_options option[value="' + val + '"]').length > 0 ){
			holder.find('.bgsize_options').val( val );
			holder.find('.bgsize_value').val('');
		}else{
			var arr = val.match(/([0-9.]*)(%|px)/gi);
			if(arr&&arr.length>0){
				$('.bgsize_value').val('');
				$.each(arr,function(i,val){
					if(i>1)return;
					var brr = val.match(/([0-9.]*)(%|px)/i);
					if(i==0){
						if( brr[2] && brr[2]=='%' ){
							holder.find('.bgsize_options').val( 'percentage' );
						}else if( brr[2] && brr[2].toLowerCase()=='px' ){
							holder.find('.bgsize_options').val( 'length' );
						}
						holder.find('.bgsize_h').val( Math.round(brr[1]*100)/100 );
					}else{
						holder.find('.bgsize_w').val( Math.round(brr[1]*100)/100 );
					}
				});
			}
		}
	
		switch( holder.find('.bgsize_options').val() ){
			case '':
			case 'auto':
			case 'cover':
			case 'contain':
				holder.find('.bgsize_value_holder').stop().fadeOut();
				break;
			default:
				holder.find('.bgsize_value_holder').stop().fadeIn();
		}

	});
	$('.bgsize_value').change(function(e){
		$(this).parents('.input-field').find('.bgsize_options').trigger('change');
	});
	$('.bgsize_options').change(function(e){	
		var holder = $(this).parents('.input-field');
		var val = $(this).val();
		var sel = $(this).data('target-selector');
		var h = holder.find('.bgsize_h').val();
		var w = holder.find('.bgsize_w').val();
		var str = '';
		var unit = '';
		switch(val){
			case '':
			case 'auto':
			case 'cover':
			case 'contain':
				holder.find('.bgsize_value_holder').stop().fadeOut();
				$(sel).val(val).trigger('change');
				break;
			case 'percentage':
			case 'length':
				unit = val=='percentage'?'%':'px';
				str = h==''? str : str + h + unit;
				str = w==''? str : str + ' ' + w + unit;
				$(sel).val(str).trigger('change');
				holder.find('.bgsize_value_holder').stop().fadeIn();
				break;	
			default:
				holder.find('.bgsize_value_holder').stop().fadeIn();
				$(sel).val('');
		}
		switch(val){
			case 'length':
				$('.bgsize-unit').html('px');
				break;
			case 'percentage':
				$('.bgsize-unit').html('%');
				break;
		}
	});
	//-- end bgsize control
	
	//-- hide loading
	$('.rhl_loading').stop().fadeOut();
	
	//-- color_or_something else select
	$('.alternate-color-values').on('change',function(e){
		var sel = $(this).attr('data-target-selector');
		var val = $(this).val();
		if('color'==val){
			var colorpicker_sel = sel + '-color';
			$(colorpicker_sel).trigger('change');
			//--
			$(this).parents('.input-field').find('.input-minicolors-hold').fadeIn();
		}else{
			$(sel).val(val)
				.trigger('change')
			;
			//--
			$(this).parents('.input-field').find('.input-minicolors-hold').fadeOut();
		}
	});
	
	$('.color_or_something_else').bind('UpdateChildControls',function(e){
		var value = $(this).val();
		if( $(this).parents('.input-field').find('.color-or-something-options option[value="' + value + '"]').length > 0 ){
			//its an alternate value ie transparent or none
			$(this).parents('.input-field').find('.alternate-color-values')
				.val(value)
				.trigger('change')
			;
		}else{
			var sel = '#' + $(this).attr('id') + '-color';		
			set_colorpicker_color(sel,value);
			$(this).parents('.input-field').find('.alternate-color-values')
				.val('color')
				.trigger('change')
			;
		}
	});
	
	$('.input-field-color_or_something_else .input-minicolors').change(function(e){
		var options_sel = $(this).attr('data-target-selector') + '-options';
		if( 'color'==$(options_sel).val() ){
			var sel = $(this).attr('data-target-selector');	
			$(sel).val( get_miniColors_color(this) )
				.trigger('change')
			;
		}
		return true;
	});
	//-- end color_or_something_else
	
	$('.sup-input-font-helper').click(function(e){
		var sel = $(this).data('input-parent');
		if( !$(this).hasClass('active') ){
			$(sel).val( $(sel).data('selected-value') )
				.trigger('change')
			;
		}else{
			$(sel).val( 'normal' )
				.trigger('change')
			;
		}
		return false;
	});
	
	$('.sup-input-font').change(function(e){
		var sel = '#'+$(this).attr('id')+'-helper';
		var val = $(this).val();
		val = val.replace(' ','');
		if( val==$(this).data('selected-value') ){
			$(sel).addClass('active');
		}else{
			$(sel).removeClass('active');
		}
	});
	
	$('.btn-collapse').click(function(e){
		var sel = '#rh-css-form';
		if( $(sel).is(':visible') ){
			var left = parseInt(($(sel).width()+20)) * -1;
			$(sel)
				.stop()
				.animate({left:left},'fast',function(){
					$(this).hide();
				})
			;
			$(this).button('loading');
			$('BODY').removeClass('rhcss-editor-active');
		}else{
			$(sel)
				.stop()
				.show()
				.animate({left:0},'fast')
			;
			$(this).button('reset');
			$('BODY').addClass('rhcss-editor-active');
		}
	});
	
	$('#btn-remove-css').click(function(e){
		css_remove();
	});

	load_saved_settings();
	
	$('#btn-add-backup').click(function(e){
		css_backup();
	});
	
	$('.rhl-backup-item a').on('click',function(e){
		css_restore(this);
	});
	
	$('#btn-restore-backup').on('click',function(e){
		css_restore();
	});
	
	$('#btn-scope,#btn-scope-done').on('click',function(e){
		css_scope();
	});
	
	$('#btn-scope-remove').on('click',function(e){
		css_remove_scope( $('#rhcss_section').val(), $('#rhcss_scope').val());
	});
	
});
}
rhl_css_edit_init();

function get_miniColors_color(inp){
	var val = jQuery(inp).val();
	if(val=='#')return '';
				var opacity = jQuery(inp).miniColors('opacity');
				opacity = opacity>=0&&opacity<=1?opacity:1;
				if(opacity<1){
					var tiny = tinycolor(val);
					//var tiny = tinycolor(tiny.toRgbString());//convert internally to rgb.
					tiny.alpha = opacity;				
					if(tiny.ok){
						val = tiny.toRgbString();				
					}
				}
	return val;
}

if(!uploaded_files){
	var uploaded_files = [];//-- upload list
}

var default_values = [];
function fill_default_values(){
	var fill_default_values = default_values.length>0?false:true;
	jQuery(document).ready(function($){
		$('.default-value-from-css').each(function(i,inp){
			if( $(inp).attr('data-css-selector')!='' ){
				var sel = $(inp).attr('data-css-selector');
				var arg = $(inp).attr('data-css-property');
				var val = normalize_css_value( inp, arg, get_css_value(inp,sel,arg) );	
				if(fill_default_values){
					default_values[default_values.length] = {
						'sel':sel,
						'arg':arg,
						'val':val
					};
				}
				//-------colorpicker		
				if( $(inp).is('.colorpicker-input-field') ){
					$(inp)
						.val(val)
						.trigger('change')
					;							
					set_colorpicker_color( '#'+$(inp).attr('id') ,val);	
				}	
				//-------gradient color picker	
				if( val && $(inp).is('.colorpicker_gradient') ){
					var arr = val.split('|');
					if(arr.length==2){
						set_colorpicker_color( '#'+$(inp).attr('id') + '-start', arr[0]);
						set_colorpicker_color( '#'+$(inp).attr('id') + '-end', arr[1]);					
					}else{
						$('#'+$(inp).attr('id') + '-start').val('');
						$('#'+$(inp).attr('id') + '-end').val('');			
					}
				}
				//-------- set text-shadow sub-fields
				if( val && 'none'!=val && $(inp).is('.colorpicker_textshadow') ){
					var tiny = tinycolor(val);			
					if(tiny.ok){
						var id = $(inp).attr('id');
						str = val.replace(tiny.m,'');
						var sel1 = '#'+ id + '-color';
						var color = tiny.toHexString();
						set_colorpicker_color(sel1,color);
						str = $.trim(val.replace(tiny.m,''));				
						var arr = str.split(' ');	
						if(arr.length==3){
							var sel1 = '#'+ id + '-h';
							var sel2 = '#'+ id + '-v';
							var sel3 = '#'+ id + '-b';	
							var sel4 = '#'+ id + '-color';				
							$(sel1).val( (Math.ceil(parseFloat(arr[0])*10))/10 );
							$(sel2).val( (Math.ceil(parseFloat(arr[1])*10))/10 );
							$(sel3).val( (Math.ceil(parseFloat(arr[2])*10))/10 );
							$(sel4).val( color );
							$(sel1).trigger('change');
						}
					}	
				}
				if( $(inp).is('select') ){
					val = typeof val!='undefined'?val:'';
					$(inp).val(val)
						.trigger('change')
					;
				}		

				if( $(inp).is('.rhl_image_uploader') ){
					$(inp)
						.trigger('change')
					;
				}
				
				if( $(inp).val()=='' ){				
					val = typeof val=='undefined'?'':val;
					$(inp).val(val)
						.trigger('change')
					;
				}else if( $(inp).is('.pop_rangeinput') ){
					var api = $(inp).data('pop_rangeinput');
					api.setValue(val);
					$(inp).trigger('change');
				}

				$(inp).trigger('UpdateChildControls');
			}
		});	
	});
}

function set_colorpicker_color(sel,color){	
	if( jQuery(sel).hasClass('input-minicolors') ){
		var opacity = 1;
		var tiny = tinycolor(color);
		if(tiny.ok){
			opacity = tiny.alpha;
			hexcolor = tiny.toHexString();
			jQuery(sel).miniColors('value',hexcolor);
			jQuery(sel).miniColors('opacity',opacity);
		}
	}else if( jQuery(sel).hasClass('farbtastic-holder') ){
		//farbtastic
		sel = sel + '-farbtastic';
		var picker = jQuery.farbtastic(sel);
		picker.setColor(color);		
	}
}

function restore_default_values(){
	jQuery(document).ready(function($){
		if(default_values.length>0){
			$.each(default_values,function(i,values){
				var sel = values.sel;
				var arg = values.arg;
				var val = values.val;
				var inp = $('[data-css-selector="'+sel+'"][data-css-property="'+arg+'"]')[0];			
				//-------		
				$(inp).val(val)
					.trigger('change')
				;			
			});
			fill_default_values();
		}			
	});
}

function set_css_from_input_value(inp){
	jQuery(document).ready(function($){
		function _remove_duplicate_media_cssrule_property(_media,property){
			jQuery.each( $.rule( $('#rule_style') )[0].sheet.cssRules, function(i,_cssRules ){
				if( !_cssRules.media  )return;
				if( _media==_cssRules.media.mediaText ) {
					if( 1==_cssRules.cssRules.length ){
						if( property==_cssRules.cssRules[0].style[0] ){
							$.rule( $('#rule_style') )[0].sheet.deleteRule( i );
							return false;
						}
					}
				}						
			});
			return true;								
		}	
		var sel = $(inp).attr('data-css-selector');
		var arg = $(inp).attr('data-css-property');
		var blocks = get_array_of_css_blocks(sel,inp,arg,$(inp).val());
		if(blocks.length>0){

			$.each(blocks,function(i,block){
				$.each(block.css,function(j,item){
					for( variable in item ){
						var value = item[variable];
						if( ''==item[variable] ){
							$.rule(block.sel).each(function(i,el){
								
								if('undefined'==typeof(el.style[0])){
									return;
								}
								
								if(variable==el.style[0]){								
									$.rule(el).remove();
									return;	
								}
								
								//this may cause undesired behaviour, like remove styles that shouldnt be removed							
								if( el.style[0].indexOf(variable) == 0){
									$.rule(el).remove();
								}
								
							});
							/* this section would set the empty value to the css it had at the start of the edit session.
							$.each(default_values,function(i,dv){
								if(dv.sel==block.sel && dv.arg==variable){
									value = dv.val;
								}
							});
							*/
							//----
							return;
						}
						var str = variable + ':' + value + ';';
					}
					
					if( $('#rule_style').length==0 ){
						$('head').append('<style id="rule_style"></style>');
					}
					
					//$.rule(block.sel).css(item);ff reports insecure operation.
					var rule = block.sel + '{' + str + '}';

					if(''!=block.media){
						try{
							var property = $.rule(rule)[0].style[0];						
							var _media = $.rule('@media ' + block.media + ' {}')[0].media.mediaText;
							var _max=0;
							while( !_remove_duplicate_media_cssrule_property(_media,property)||_max++<50){
							
							}
						}catch(e){
						
						}

						//---add new rule
						var _media = '@media ' + block.media + ' {' + rule + '}';
						$.rule( $('#rule_style') )[0].sheet.insertRule( _media,
							$.rule( $('#rule_style') )[0].sheet.cssRules.length
						 );						
						//---					
//console.log($.rule( $('#rule_style') )[0].sheet);
					}else{
						$.rule(rule).remove();
						$.rule(block.sel + '{}').remove();					
						$.rule(rule)
							.appendTo('#rule_style')
						;	
					}					
					
					if( block && block.sel && block.sel.indexOf(':hover')>-1 )return;//hover is not a valid pseudo selector for .css
		// original realt-time css setup, replaced with the rule library.			
		//			$( block.sel ).css( item );							
				});
			});
			
			//cleanup
			function _cleanup_empty_rules(){		
				if( $.rule( $('#rule_style') ).length>0 ){
					jQuery.each( $.rule( $('#rule_style') )[0].sheet.cssRules, function(i,_cssRules ){
						if( 'undefined'==typeof(_cssRules.style) 
						|| 'undefined'==typeof(_cssRules.style[0]) 
						|| ''==_cssRules.style[0] ){		
							$.rule( $('#rule_style') )[0].sheet.deleteRule( i );
							return false;
						}
					});				
				}

				return true;				
			}
			var c = 0;
			while( !_cleanup_empty_rules() || c++<500 ){
				//TODO, find out why it gets cluttered with empty rules in the first place.
			}
			
		}
	});
}

function get_array_of_css_blocks(sel,inp,arg,val){
	var ret=[];
	var blocks = [];
	var media = jQuery(inp).data('media');
	media = 'undefined'==typeof(media) ? '' : media ;
	
	blocks[blocks.length]={
		'id':jQuery(inp).attr('id'),
		'sel':sel,
		'arg':arg,
		'val':val,
		'css':get_css_array(inp,arg,val),
		'media': media
	};

	//--- generic derived styles
	try {
		var derived = jQuery(inp).data('derived');
		var arr = eval(unescape(derived));
		if(arr && arr.length>0){
			jQuery.each(arr,function(i,o){
				var value_set = false;
				var property_value = '';
				//filter value			
				if(o.type=='gradient_darken'){
					var grr = val.split('|');			
					var tiny = tinycolor(grr[1]);
					if(tiny.ok){			
						var alpha = tiny.alpha;			
						color = tinycolor.darken(tiny, o.val ).toRgbString();					
				//add parents opacity
						var tiny = tinycolor(color);
						tiny.alpha = alpha;
						
						color = tiny.toRgbString();
						//---
						property_value = color;									
						value_set =true;			
					}else{
	
					}
				}else if(o.type=='color_darken'){
					var tiny = tinycolor(val);				
					if(tiny.ok){			
						var alpha = tiny.alpha;			
						color = tinycolor.darken(tiny, o.val ).toRgbString();					
				//add parents opacity
						var tiny = tinycolor(color);
						tiny.alpha = alpha;
						
						color = tiny.toRgbString();
						//---
						property_value = color;									
						value_set =true;			
					}else{
						property_value = '';
						value_set = true;
					}
				}else if(o.type=='same'){		
					property_value = normalize_input_value(inp,arg,val);
					value_set = true;
				}else if(o.type=='same2'){
					var items = get_css_array(inp,arg,val);
					if(items.length>0){
						blocks[blocks.length]={
							'id':jQuery(inp).attr('id'),
							'sel':o.sel,
							'arg':arg,
							'val':val,						
							'css':items,
							'media': ('undefined'==typeof(o.media) ? media : o.media)
						};	
					}
					value_set = false;			
				}
				
				if(value_set){
					var _property = o.arg;
					items=[];					
					for(a=0;a<_property.length;a++){				
						var p = {};
						tpl = _property[a].tpl?_property[a].tpl:'__value__';
						tpl = ''==property_value?'':tpl.replace('__value__',property_value);
						p[ _property[a].name ] = tpl;			
						items[items.length]=p;			
					}								
					blocks[blocks.length]={
						'id':jQuery(inp).attr('id'),
						'sel':o.sel,
						'arg':arg,
						'val':val,						
						'css':items,
						'media': ('undefined'==typeof(o.media) ? media : o.media)
					};										
				}
			});		
		}
	}catch(e){
	
	}
	
	return blocks;
}

function get_css_array(inp,arg,val){
	var ret = [];
	//--- special conditions -- todo: put this in some generic procedure.
	if( 'rhl-footer-bg-color'==jQuery(inp).attr('id') ){
		var tiny = tinycolor(val);
		if(tiny.ok){			
			color = tinycolor.lighten(tiny,10).toRgbString();
			//add parents opacity
			var tiny = tinycolor(color);
			if(jQuery(inp).data('opacity')){
				tiny.alpha = jQuery(inp).data('alpha');
			}
			color = tiny.toRgbString();
			//---
			var box_shadow = [
				'-webkit-box-shadow',
				'-moz-box-shadow',
				'box-shadow'
			];
			for(a=0;a<box_shadow.length;a++){
				var o = {};
				o[ box_shadow[a] ] = 'inset 0 1px 0 ' + color;
				ret[ret.length]=o;
			}
		}		
	}

	//---
	if(arg=='background-color'){
		//-- TODO if color with opacity, add solid colors and use the element opacity
	}
	
	//---
	if(arg=='background-image'){
		if( val!='none' && !isURL(val) ){
			var ret = get_gradient_from_color(val,'background-image');
			if( false!==ret ){
				return ret;
			}
			
		}
	}
	//---
	val = normalize_input_value(inp,arg,val);
	var o = {};
	o[arg]=val;
	ret[ret.length]=o;
	return ret;
}

function get_css_value( inp, sel, arg ){
	var cb = jQuery(inp).data('cb_get_css_value');
	if (get_css_value_callbacks[cb] && typeof(get_css_value_callbacks[cb]) === "function") {
		return get_css_value_callbacks[cb](inp, sel, arg);
	}

	var value = '';
	var found = false;		

	if(!found){
		jQuery.rule(sel).each(function(i,o){
			var tmp = o.style.getPropertyValue(arg);	
			if( !found && (tmp!='') && (tmp||0==tmp) ){
				value = tmp;
				found=true;
			}else if( !found && arg==o.style[0] && o.style[arg] ){
				value = o.style[arg];
				found=true;				
			}
		});	
	}

	var map = {
		'border-color': 'borderColor'
	}
	if(!found && 'undefined'!=typeof map[arg]){
		var index = map[arg];
		jQuery.rule(sel).each(function(i,o){
			if( !found && 'undefined'!=typeof o.style[index] && ''!=o.style[index] ){
				value = o.style[index];
				found=true;			
			}
		});	
	}

	return typeof value=='undefined'?'':value;//diferent browsers may set NaN
}

function _get_css_value( inp, sel, arg ){
	var value = jQuery(sel).css(arg);
	if( jQuery(sel).length>1 ){
		//bug fix, .css only gets the value of first element
		var found = false;
		jQuery.rule(sel).each(function(i,o){
			if( !found && arg==o.style[0] && o.style[arg] ){
				value = o.style[arg];
				found=true;
			}
		});
	}
	
	if( 'undefined'==typeof(value) ){
		if( sel.indexOf(':hover')>-1 ){

		}	
	}
	return typeof value=='undefined'?'':value;
}

function normalize_input_value(inp,arg,val){
	if(arg=='font-family'){
		val = val.replace(/'/g,"");//'
	}
	
	if(arg=='background-image'||arg=='background-image'){
		if(isURL(val)){
			if(val==''||val=='none'){
				return 'none';
			}else{	
				return 'url(' + val + ')';
			}		
		}else{
			if(val=='none')return 'none';
			return '';
		}
	}
	
	switch(arg){
		case 'top':
		case 'width':
		case 'height':
		case 'margin-top':
		case 'min-height':
		case 'line-height':
		case 'font-size':
		case 'border-top-left-radius':
		case 'border-top-right-radius':
		case 'border-bottom-left-radius':
		case 'border-bottom-right-radius':		
		case 'border-bottom-width':		
		case 'border-width':
		case 'padding-top':
		case 'padding-right':
		case 'padding-bottom':
		case 'padding-left':	
		case 'margin-top':
		case 'margin-right':
		case 'margin-bottom':
		case 'margin-left':	
			val = get_value_with_unit(val,inp);
	}

		
	var blank_value = jQuery(inp).attr('data-blank-value');
	if(blank_value && ''==val){
		return blank_value;
	}

	if( jQuery(inp).hasClass('miniColors') ){
		var opacity = jQuery(inp).miniColors('opacity');
		if( opacity && opacity<1 ){
			var tiny = tinycolor(val);
			if(tiny.ok){
				tiny.alpha = opacity;
				val = tiny.toRgbString();
			}
		}
	}
	
	return val;
}

function normalize_css_value(inp,arg,val){
	switch(arg){

		case 'top':
		case 'width':
		case 'height':
		case 'margin-top':
		case 'min-height':
		case 'font-size':
		//case 'line-height':
		case 'border-top-left-radius':
		case 'border-top-right-radius':
		case 'border-bottom-left-radius':
		case 'border-bottom-right-radius':	
		case 'border-bottom-width':	
		case 'border-width':
		case 'padding-top':
		case 'padding-right':
		case 'padding-bottom':
		case 'padding-left':
		case 'margin-top':
		case 'margin-right':
		case 'margin-bottom':
		case 'margin-left':
			var r = parseInt(val); 
			r = isNaN(r)?'':r;
			return r;//firefox gives NaN if val is empty.
			
		case 'text-shadow':	
			return val;
		case 'background-image':
			return get_normalized_background_image(val);
		case 'color':
		case 'border-color':
		case 'border-top-color':
		case 'border-bottom-color':
		case 'border-left-color':
		case 'border-right-color':
			return get_normalized_sring_color(val,inp);
		case 'background-color':
			return get_normalized_background_color(val,inp);
		case 'background-position':
			return get_normalized_background_position(val,inp);
		case 'font-family':
			return get_normalized_font_family(val,inp);
	}
	
	
	return val;
}

function get_value_with_unit(str,inp){
	var unit = jQuery(inp).attr('data-input-unit');
	if(unit && ''!=str){
		str=str+unit;
	}
	return str;
}

function render_uploaded_files(id){
	jQuery(document).ready(function($){
		//----
		var sel = '#'+id+'-upload-list';
		list = $(sel).val().split("\n"); 
		if( list.length>0 ){
			var tmp=[];
			$.each(list,function(i,item){
				if( item.replace(' ','')=='' )return;
				tmp[tmp.length]=item;
			});
			list=tmp;
		}
		//----		
		var sel = '#'+id+'-uploaded';
		$(sel).empty();
		var cont = '#'+id+'-holder';
		if( list.length==0 ){
			$(cont).find('.rhl-uploaded-images-tab').hide();
			$(cont).find('.rhl-upload-new').tab('show');
		}else{
			$(cont).find('.rhl-uploaded-images-tab').show();
			//---
			$.each(list,function(i,item){
				var img = $('<img />')
					.attr('src',item)
				;
				
				$('<a></a>')
					.on('click',function(e){
						var sel = $(this).data('click_target');
						$(sel).val( $(this).data('click_source') ).trigger('change');					
					})
					.addClass('uploaded_image_url_thumb')
					.data('click_source',item)
					.data('click_target','#'+id)
					.append(img)
					.appendTo(sel)
				;	
			});
		}
	});
}	
	
function init_image_uploader(settings){  
	if( !uploaded_files[settings.queue] ){
		uploaded_files[settings.queue] = [];
	}
	
	render_uploaded_files(settings.id, uploaded_files[settings.queue]);
	
    jQuery(document).ready(function($){
      var uploader = new plupload.Uploader(settings);
      uploader.bind('Init', function(up){
        var uploaddiv = $('#'+settings.container);

        if(up.features.dragdrop){
          uploaddiv.addClass('drag-drop');
            $('#'+settings.drop_element)
              .bind('dragover.wp-uploader', function(){ uploaddiv.addClass('drag-over'); })
              .bind('dragleave.wp-uploader, drop.wp-uploader', function(){ uploaddiv.removeClass('drag-over'); });
        }else{
          uploaddiv.removeClass('drag-drop');
          $('#'+settings.drop_element).unbind('.wp-uploader');
        }
      });
      uploader.init();

      // a file was added in the queue
      uploader.bind('FilesAdded', function(up, files){
        var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);

        plupload.each(files, function(file){
          if (max > hundredmb && file.size > hundredmb && up.runtime != 'html5'){
            // file size error?

          }else{

          }
        });

        up.refresh();
        up.start();
      });

      	uploader.bind('UploadFile', function(up,file){
			$('.rhl_loading').stop().fadeIn();	
		});
		uploader.bind('FileUploaded', function(up, file, response) {
			if(response.status==200){
				try {
					data = eval("(" + response.response + ')');
					if(data.R=='OK'){
						$('#'+data.ID).val( data.URL ).trigger('change');
						$('#'+data.ID+'-upload-ui').parent().parent().parent().addClass('helper-closed');
						//--
						var sel = '#' + data.ID + '-upload-list';
						var queue = $(sel).data('upload_queue');
						$(queue).each(function(i,o){
							var _id = $(o).attr('id');
							$('#' + _id).val( data.UPLOADED );
							_id = _id.replace('-upload-list','');
							render_uploaded_files( _id );
						});
					}else if(data.R=='ERR'){
						var sel = '#'+data.ID + '-msg';
						var _msg = '<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">x</a>'+data.MSG+'</div>';
						$(sel).empty().append(_msg);
					}else{
						var sel = '#'+data.ID + '-msg';
						var _msg = '<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">x</a>'+_unexpected_error+'</div>';
						$(sel).empty().append(_msg);
					}	
					$('.rhl_loading').stop().fadeOut();
					return true;			
				}catch(e){
				
				}
			}
			$('.rhl_loading').stop().fadeOut();
			alert('Unknown server response while uploading image');
		});
    });   
}