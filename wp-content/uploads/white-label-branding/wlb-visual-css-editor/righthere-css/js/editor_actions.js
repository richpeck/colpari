
function css_save(e){
	jQuery(document).ready(function($){
		$('.btn-save-settings').twbutton('loading');
		var scope = $('#rhcss_scope').val();
		var data = [];
		var data_class = [];
		$('.input-field-input').each(function(i,inp){
			var sel = add_scope_to_selector( $(this).attr('data-css-selector') , scope );
			var arg = $(this).attr('data-css-property');
			var unit = $(this).attr('data-input-unit');
			var val = $(this).val();
 			var hook = $(this).attr('data-hook');
			var _type = $(inp).attr('data-rhcss_type');
			
			if( 'css' == _type ){
				var subset = get_array_of_css_blocks(sel,inp,arg,val);
				if(subset.length>0){
					data = data.concat(subset);
				}
			}
			
			if( 'class' == _type ){
				subset = {
					'id':jQuery(inp).attr('id'),
					'sel':sel,
					'pre':jQuery(inp).data('class_prefix'),
					'val':val,
					'hook':hook
				};
				data_class = data_class.concat(subset);
			}
		});
		
		var pop = [];
		$('.input-pop-option').each(function(i,inp){
			pop[pop.length] = {
				'name': $(inp).attr('name'),
				'value': $(inp).val()
			};
		});
		
		var arg = {
			action: 'rhcss_ajax_' + $('#rh-editor-id').val(),
			method: 'save',
			pop: pop,
			section: $('#rhcss_section').val(),
			scope: $('#rhcss_scope').val(),
			default_values: default_values,
			data: data,
			data_class: data_class
		};	

		$.post(rh_ajax_url,arg,function(data){

			$('.btn-save-settings').twbutton('reset');
			if(data.R=='OK'){
				var _msg = '<div class="alert alert-success"><a class="close" data-dismiss="alert" href="#">x</a>'+data.MSG+'</div>';
			}else if(data.R=='ERR'){
				var _msg = '<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">x</a>'+data.MSG+'</div>';
			}else{
				var _msg = '<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">x</a>'+_unexpected_error+'</div>';
			}
			$('.ajax-result-messages')
				.empty()
				.append(_msg)
			;			
		},'json');

	});
}

function css_remove(){
	jQuery(document).ready(function($){
		$('.rhl_loading').stop().fadeIn();
		
		var arg = {
			action: 'rhcss_ajax_' + $('#rh-editor-id').val(),
			method: 'remove'
		};
		$.post(rh_ajax_url,arg,function(data){
			$('.rhl_loading').stop().fadeOut('fast');
			if(data.R=='OK'){
				var _msg = '<div class="alert alert-success"><a class="close" data-dismiss="alert" href="#">x</a>'+data.MSG+'</div>';
				location.reload(true);
			}else if(data.R=='ERR'){
				var _msg = '<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">x</a>'+data.MSG+'</div>';
			}else{
				var _msg = '<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">x</a>'+_unexpected_error+'</div>';
			}
			$('.ajax-result-messages')
				.empty()
				.append(_msg)
			;			
		},'json');		
	});
}

function css_remove_scope(section,scope){
	jQuery(document).ready(function($){		
		if(scope==''){
			return;
		}
	
		$('.rhl_loading').stop().fadeIn();
		
		var arg = {
			action: 'rhcss_ajax_' + $('#rh-editor-id').val(),
			method: 'remove',
			section: section,
			scope: scope
		};
		$.post(rh_ajax_url,arg,function(data){
			$('.rhl_loading').stop().fadeOut('fast');
			if(data.R=='OK'){
				var _msg = '<div class="alert alert-success"><a class="close" data-dismiss="alert" href="#">x</a>'+data.MSG+'</div>';
				location.reload(true);
			}else if(data.R=='ERR'){
				var _msg = '<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">x</a>'+data.MSG+'</div>';
			}else{
				var _msg = '<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">x</a>'+_unexpected_error+'</div>';
			}
			$('.ajax-result-messages')
				.empty()
				.append(_msg)
			;			
		},'json');		
	});
}

function css_backup(){
	jQuery(document).ready(function($){
		$('#btn-add-backup').twbutton('loading');
		
		var arg = {
			action: 'rhcss_ajax_' + $('#rh-editor-id').val(),
			method: 'backup',
			label: $('#rhl_backup_name').val()
		};
		$.post(rh_ajax_url,arg,function(data){
			$('#btn-add-backup').twbutton('reset');
			if(data.R=='OK'){
				var _msg = '<div class="alert alert-success"><a class="close" data-dismiss="alert" href="#">x</a>'+data.MSG+'</div>';
				load_saved_settings();
			}else if(data.R=='ERR'){
				var _msg = '<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">x</a>'+data.MSG+'</div>';
			}else{
				var _msg = '<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">x</a>'+_unexpected_error+'</div>';
			}
			$('#add_backup_msg')
				.empty()
				.append(_msg)
			;			
		},'json');		
	});	
}

function css_restore(){
	jQuery(document).ready(function($){
		$('#btn-restore-backup').twbutton('loading');
		var label = $('input[name=rhl_css_saved]:checked').val();
		label = label?label:'';
		
		var arg = {
			action: 'rhcss_ajax_' + $('#rh-editor-id').val(),
			method: 'restore',
			label: label
		};
		$.post(rh_ajax_url,arg,function(data){
			$('#btn-restore-backup').twbutton('reset');
			if(data.R=='OK'){
				var _msg = '<div class="alert alert-success"><a class="close" data-dismiss="alert" href="#">x</a>'+data.MSG+'</div>';
				location.reload(true);
			}else if(data.R=='ERR'){
				var _msg = '<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">x</a>'+data.MSG+'</div>';
			}else{
				var _msg = '<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">x</a>'+_unexpected_error+'</div>';
			}
			$('#add_backup_msg')
				.empty()
				.append(_msg)
			;			
		},'json');		
	});	
}

function load_saved_settings(){
	jQuery(document).ready(function($){
		var arg = {
			action: 'rhcss_ajax_' + $('#rh-editor-id').val(),
			method: 'list',
		};	
		$.post(rh_ajax_url,arg,function(data){
			if(data.R=='OK'){
				render_saved_settings(data.DATA);
			}
		},'json');
	});		
}

function render_saved_settings(data){
	jQuery(document).ready(function($){
		$('.saved_settings_list_cont')
			.empty()
		;
		
		if(data.length>0){
			$('.saved_settings_list_cont').show();
			$('.empty_saved_settings').hide();
			$('#btn-restore-backup').show();
			$.each(data,function(i,d){

				$('<label class="radio"><input type="radio" name="rhl_css_saved" value="' + d.name + '">' + d.name + '</label>')
					.appendTo( $('.saved_settings_list_cont') )
				;
			});
		}else{
			$('.saved_settings_list_cont').hide();
			$('.empty_saved_settings').show();
			$('#btn-restore-backup').hide();
		}
	});		
}

function css_scope(){
	jQuery(document).ready(function($){
		//console.log('scope',$('#rh-css-form .rh-css-slides').css('top'));
		var control = $('#rh-css-form .rh-css-scope-controls');
		if(control.is('.css-open')){
			control.removeClass('css-open');
			$('#rh-css-form .rh-css-controls').animate({height:$('#rh-css-form .rh-css-controls').data('original-h')});
			$('#rh-css-form .rh-css-slides').animate({top:$('#rh-css-form .rh-css-slides').data('original-t')});	
		}else{
			control.addClass('css-open');	
			var ch = control.height();
			var h1 = $('#rh-css-form .rh-css-controls').height();
			var top = parseInt($('#rh-css-form .rh-css-slides').css('top'));
			$('#rh-css-form .rh-css-controls').data('original-h',h1);
			$('#rh-css-form .rh-css-slides').data('original-t',top);
			$('#rh-css-form .rh-css-controls').animate({height:ch + h1});
			$('#rh-css-form .rh-css-slides').animate({top:ch + top});
		}
	});	
}

function add_scope_to_selector(sel,scope){
	if( scope!='' && sel ){
		var selectors = sel.split(',');
		if( selectors.length > 1 ){
			var arr = [];
			for(a=0;a<selectors.length;a++){
				arr[a] = scope + ' ' + selectors[a];
			}
			return arr.join(', ');		
		}else{
			return scope + ' ' + sel;
		}
	}
	return sel; 
}