/**
* Font selector plugin
* Based on the work by: James Carmichael www.siteclick.co.uk
*/
if(jQuery) (function($) {
	$.fn.fontSelector = function(fonts) {  
		return this.each(function(){
	    	var sel = this;
			var id = $(this).attr('id');
			var ul = $('<ul class="fontselector"></ul>');
	    	$(sel).parent().parent().append(ul);
	    	$(ul).hide();

			$(document).keyup(function(e) {
			  if (e.keyCode == 27) { $(ul).hide(); }
			});		
				
			$('html').click(function() {
				$(ul).hide();//hide on click
			});
			
	      	$(sel).click(function(e) {
				e.preventDefault();
	        	
	        	if( $(ul).is(':visible') ){
					$(ul).hide();
				}else{
					$(ul).show();
				}
				//$(this).blur();
				e.stopPropagation();
	        	return false;
	      	});		
			 
			jQuery.each(fonts, function(i, item) {
	 	     	$(ul).append('<li><a href="#" class="font_' + i + '" style="font-family: ' + item + '">' + item.split(',')[0] + '</a></li>');
	 	 
		      	$(ul).find('a').click(function() {
		        	var font = fonts[$(this).attr('class').split('_')[1]];
		        	$(sel).val(font).trigger('change');
		        	$(ul).hide();
		        	return false;
		      	});
		    });
		});
	}
})(jQuery);