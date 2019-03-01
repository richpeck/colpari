
function get_normalized_background_image(str){
	switch(str){
		case 'none':
			return 'none';
			//return str;
	}
	//unwrap url.
	arr = str.match(/url\(\"?([^)"]*)\"?\)/i);
	if(arr && arr.length==2 && isURL(arr[1])){
		str = arr[1];
	}
	if(isURL(str)){
		return str;
	}else{
		var colors = [];
		var tiny = tinycolor(str);			
		if(tiny.ok){
			if(tiny.alpha==1){
				colors[colors.length]=tiny.toHexString();
			}else{
				colors[colors.length]=tiny.toRgbString();
			}
			str = str.replace(tiny.m,'');			
			var tiny = tinycolor(str);	
			if(tiny.ok){
				if(tiny.alpha==1){
					colors[colors.length]=tiny.toHexString();
				}else{
					colors[colors.length]=tiny.toRgbString();
				}			
				return colors.join('|');
			}			
		}
	}
	
	return '';
}

function get_normalized_sring_color(str,inp){
	var tiny = tinycolor(str);
	if(tiny.ok){	
		if(tiny.alpha==1){
			return tiny.toHexString();
		}else{
			return tiny.toRgbString();
		}
	}

	return '';
}

function get_normalized_background_color(str,inp){
	if(jQuery(inp).is('.with-alternate-color-value')){
		//value is in dropdown of alternate values.
		if( jQuery(inp).parents('.input-field').find('.alternate-color-value[value="'+str+'"]').length>0 ){
			return str;
		}
	}
	
	var tiny = tinycolor(str);
	if(tiny.ok){	
		if(tiny.alpha==0){
			return 'transparent';
		}else if(tiny.alpha==1){
			return tiny.toHexString();
		}else{
			return tiny.toRgbString();
		}
	}

	return '';	
}

function get_normalized_background_repeat(str,inp){
	switch(str){
		case 'repeat repeat':
			return 'repeat';
		case 'repeat no-repeat':
			return 'repeat-x';
		case 'no-repeat repeat':
			return 'repeat-y';
		case 'no-repeat no-repeat':
			return 'no-repeat';
	}
	return str;
}

function get_normalized_background_position(str,inp){
	switch(str){
		case '0% 0%':
			return 'left top';
		case '0% 50%':
			return 'left center';
		case '0% 100%':
			return 'left bottom';
		case '100% 0%':
			return 'right top';
		case '100% 50%':
			return 'right center';
		case '100% 100%':
			return 'right bottom';
		case '50% 0%':
			return 'center top';
		case '50% 50%':
			return 'center center';
		case '50% 100%':
			return 'center bottom';
	}
	return str;
}

/* */
function get_hex_color_with_opacity(color){
	var tiny = tinycolor(color);
	if(tiny.ok){
		return tiny.toHexOpacityString();
	}
	return null;
}

function get_normalized_font_family(str,inp){
	str = str.replace(new RegExp("\\\\", "g"), "");
	str = str.replace(new RegExp('"', "g"),'');
	return unescape(str);
}

function get_gradient_from_color(val,arg){
	var ret = [];
	if(null==val)return false;
	var arr = val.split('|');
	if(arr.length==2){
		var background_image = [
			'-webkit-gradient(linear, left top, left bottom, color-stop(0, _c1), color-stop(1, _c2))',/* Webkit (Safari/Chrome 10) */ 
			'-webkit-linear-gradient(top, _c1 0%, _c2 100%)',/* Webkit (Chrome 11+) */ 
			'-ms-linear-gradient(top, _c1 0%, _c2 100%)',/* IE10 Consumer Preview */ 
			'-o-linear-gradient(top, _c1 0%, _c2 100%)',/* Opera */ 
			'linear-gradient(to bottom, _c1 0%, _c2 100%)',/* W3C Markup, IE10 Release Preview */ 
			'-moz-linear-gradient(top, _c1 0%, _c2 100%)'/* Mozilla Firefox */ 
		];
		
		for(a=0;a<background_image.length;a++){
			var o = {};	
			var val = background_image[a];
			val = val.replace("_c1",arr[0]);
			val = val.replace("_c2",arr[1]);		
			o[arg]=val;					
			ret[ret.length]=o;
		}
		/*ie9 todo transparency support or pre9?*/
		
		
		var iecolor1 = get_hex_color_with_opacity(arr[0]);
		var iecolor2 = get_hex_color_with_opacity(arr[1]);
		ret[ret.length]={
			'filter':'progid:DXImageTransform.Microsoft.gradient(startColorstr='+iecolor1+', endColorstr='+iecolor2+')'
		}
		/*
		ret[ret.length]={
			'-ms-filter':'progid:DXImageTransform.Microsoft.Alpha(Opacity=50)'
		}
		*/
		
		/* this interferes with double background effects.
		ret[ret.length]={
			'background-color':arr[1]
		}	
		*/		
		return ret;
	}
	return false;
}