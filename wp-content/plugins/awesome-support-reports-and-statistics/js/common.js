
jQuery(document).ready(function() {
	
	jQuery('.ticket_auth').select2();
	
	/* post custom field values from reports setting   */
	jQuery(".wpas_custom_field_type").on('change',function(){
			post_custom_fields();
	});

		
	if(jQuery( '.rns-left-filters' ).length > 0) {
		var screen_size = jQuery(window).width();
		if( screen_size < 800 ) {
			jQuery( '.rns-left-filters' ).css({'width':'0%','display':'none'});
		}
	}

	/* Adding date picker to the date filters. */
	jQuery( '.date' ).datepicker({
			dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
			yearRange: "1990:+0"
     });
	 
	 /* Removing postion for canvas */
	 if( jQuery( ".canvasjs-chart-canvas" ).length > 0 ){
		 jQuery( ".canvasjs-chart-canvas" ).css('postion','');
	 }
	 
	 // Code for updating date filter values by change in drop down for Day.
	 jQuery( '#filter_days' ).change( function () { 
	 	var filter_days_value = jQuery( this ).val();
		var start_date	= '';
		var end_date	= '';
		
		var today	= new Date();
		var dd		= today.getDate();
		var mm		= today.getMonth()+1; //January is 0!
		var yyyy	= today.getFullYear();
		
		if( filter_days_value == 'lmonth' ) { //if date filter value is selected last month 
			
			var date		= new Date();
			var firstDay	= new Date( date.getFullYear() , date.getMonth() - 1 , 1).toString( "yyyy-M-dd" );
			var lastDay		= new Date( date.getFullYear() , date.getMonth() , 0 ).toString( "yyyy-M-dd" );

			jQuery( "#sdate" ).val( firstDay );
			jQuery( "#edate" ).val( lastDay );
			
		} else if( filter_days_value == 'tmonth' ) { //if date filter value is selected this month 
			
			var date		= new Date();
			var firstDay	= new Date( date.getFullYear() , date.getMonth() , 1 ).toString( "yyyy-M-dd" );
			var lastDay		= new Date( date.getFullYear() , date.getMonth() + 1, 0).toString( "yyyy-M-dd" );

			jQuery( "#sdate" ).val( firstDay );
			jQuery( "#edate" ).val( lastDay );
			
		} else if( filter_days_value == 'lweek' ) { //if date filter value is selected last week 
			
			var beforeOneWeek	= new Date( new Date().getTime() - 60 * 60 * 24 * 7 * 1000 );
			day					= beforeOneWeek.getDay();
			diffToMonday		= beforeOneWeek.getDate() - day + ( day === 0 ? -6 : 1 );
			lastMonday			= new Date( beforeOneWeek.setDate( diffToMonday ) );
			lastSunday			= new Date( beforeOneWeek.setDate( diffToMonday + 6 ) );
			
			jQuery( "#sdate" ).val( lastMonday.toString( "yyyy-M-dd" ) );
			jQuery( "#edate" ).val( lastSunday.toString( "yyyy-M-dd" ) );
			
		} else if( filter_days_value == 'tweek' ) { //if date filter value is selected last week 
			
			var curr	= new Date; // get current date
			var first	= curr.getDate() - curr.getDay(); // First day is the day of the month - the day of the week
			var last	= first + 6; // last day is the first day + 6
			
			var firstday	= new Date( curr.setDate( first ) ).toString( "yyyy-M-dd" );
			var lastday		= new Date( curr.setDate( last ) ).toString( "yyyy-M-dd" );
			
			jQuery( "#sdate" ).val( firstday );
			jQuery( "#edate" ).val( lastday );
			
		} else if( filter_days_value == 'yesterday' ) { //if date filter value is selected yesterday
			
			var yesterday = new Date( new Date().getTime() - 60 * 60 * 24 * 1 * 1000 );
			dd		= yesterday.getDate();
			mm		= yesterday.getMonth() + 1; //January is 0!
			yyyy	= yesterday.getFullYear();
			jQuery( "#sdate" ).val( yyyy + '-' + mm + '-' + dd );
			jQuery( "#edate" ).val( yyyy + '-' + mm + '-' + dd );
			
		} else if( filter_days_value == 'today' ) { //if date filter value is today 
			
			var today	= new Date();
			var dd		= today.getDate();
			var mm		= today.getMonth()+1; //January is 0!
			var yyyy	= today.getFullYear();
			
			jQuery( "#sdate" ).val( yyyy + '-' + mm + '-' + dd );
			jQuery( "#edate" ).val( yyyy + '-' + mm + '-' + dd );
			
		} else {
			jQuery( "#sdate" ).val( start_date );
			jQuery( "#edate" ).val( start_date );
		}
		
		applyFilters();
		
	 } );
	 
	 
	 /* if the value of second dimenssion filter is none */
	 if ( jQuery(".second_dimension:checked").val() == 'none')	{

			 jQuery( "#type-of-chart option[value=area]" ).remove();
			 jQuery( "#type-of-chart option[value=splineArea]" ).remove();
			 jQuery( "#type-of-chart option[value=stackedColumn]" ).remove();
			 jQuery( "#type-of-chart option[value=stackedArea]" ).remove();
			 jQuery( "#type-of-chart option[value=stackedBar]" ).remove();
 
     }
	 
	 //Code for updating chart options as per the second dimension choosen.
	 jQuery(".second_dimension").click(function(){
		 if ( jQuery(this).val() == 'none'){
			 jQuery( "#type-of-chart option[value=area]" ).remove();
			 jQuery( "#type-of-chart option[value=splineArea]" ).remove();
			 jQuery( "#type-of-chart option[value=stackedColumn]" ).remove();
			 jQuery( "#type-of-chart option[value=stackedArea]" ).remove();
			 jQuery( "#type-of-chart option[value=stackedBar]" ).remove();
			 jQuery( "#type-of-chart option[value=bar]" ).attr('selected','selected');
			 
			 /* If the report is neither distribution analysis nor treand analysis    */
			 if(jQuery('#rns_action').val()!='distribution_report' &&  jQuery('#rns_action').val()!='trend_report' ) {
				 
			 	jQuery( "#type-of-chart" ).append('<option value="pie" >Pie Charts</option>');
				jQuery( "#type-of-chart" ).append('<option value="doughnut" >	Doughnut Charts </option>');
				
			  }
			 
		 }else{
			 jQuery( "#type-of-chart option[value=pie]" ).remove();
			 jQuery( "#type-of-chart option[value=doughnut]" ).remove();
			 jQuery( "#type-of-chart").append('<option value="area"> Area chart </option>');
			 jQuery( "#type-of-chart").append('<option value="splineArea" >	Area Spline Chart  </option>');
			 jQuery( "#type-of-chart").append('<option value="stackedColumn">	Stacked Column Chart </option>');
			 jQuery( "#type-of-chart").append('<option value="stackedArea" > Stacked Area Chart </option>');
			 jQuery( "#type-of-chart").append('<option value="stackedBar" >Stacked Bar Chart </option>');
		 }
	 });
	 
	 
	 /* on click  toggle fiter link show or hide the left sidebar filters */
	 jQuery( '.toggle_sidefilter' ).on( 'click', function() {
			jQuery('#rns_saveform_view').hide(); 
			
		   if ( jQuery( '.rns-left-filters' ).is( ':visible' ) ) { // if left sidebar filter visible
			   
			   jQuery( '.rns-left-filters' ).css({'width':'0%','display':'none'},100);
			   jQuery( '.rns-right-container' ).css({'width':'99.5%','display':'block'},100);
			   
		   } else {
			   jQuery( '.rns-help-right-filters' ).css({'width':'0%','display':'none'},100);
			   
			   if(screen_size > 500 && screen_size < 800){ // if browser window size between 500 and 800
				   
				   jQuery( '.rns-left-filters' ).css({'width':'35%','display':'block'},100);
				   jQuery( '.rns-right-container' ).css({'width':'64%','float':'right'},100);
				   
			   } else if(screen_size < 500){ // if browser window size less than 500
				   
				   jQuery( '.rns-left-filters' ).css({'width':'100%','display':'block'},100);
				   jQuery( '.rns-right-container' ).css({'width':'0%','display':'none'},100);
				   
			   }else{
				   
				   jQuery( '.rns-left-filters' ).css({'width':'19%','display':'block'},100);
				   jQuery( '.rns-right-container' ).css({'width':'79.5%','display':'block','float':'right'},100);
			   }
		   }
		   jQuery( '.canvasjs-chart-container' ).find('canvas').css({'width':'99.5%'},100);
			
	 });
	 
	 /* On click on help text button show help bar    */
	 jQuery( '.toggle_helptext' ).on( 'click', function() {
			jQuery('#rns_saveform_view').hide(); 
		   if ( jQuery( '.rns-help-right-filters' ).is( ':visible' ) ) { //if help text bar visible
			   
			   jQuery( '.rns-help-right-filters' ).css({'width':'0%','display':'none'},100);
			   jQuery( '.rns-right-container' ).css({'width':'99.5%','display':'block'},100);
			   
		   } else {
			   
			   jQuery( '.rns-left-filters' ).css({'width':'0%','display':'none'},100);
			   
			   if(screen_size > 500 && screen_size < 800){ // if browser window size between 500 and 800
				   
				   jQuery( '.rns-help-right-filters' ).css({'width':'35%','display':'block','float':'right'},100);
				   jQuery( '.rns-right-container' ).css({'width':'64%','float':'left'},100);
				   
			   } else if(screen_size < 500){ // if browser window size less than 500
				   
				   jQuery( '.rns-help-right-filters' ).css({'width':'100%','display':'block'},100);
				   jQuery( '.rns-right-container' ).css({'width':'0%','display':'none'},100);
				   
			   }else{
				   
				   jQuery( '.rns-help-right-filters' ).css({'width':'19%','display':'block','float':'right'},100);
				   jQuery( '.rns-right-container' ).css({'width':'79.5%','display':'block','float':'left'},100);
			   }
		   }
		   jQuery( '.canvasjs-chart-container' ).find('canvas').css({'width':'99.5%'},100);
			
	 });
	 
	/* show save report forn  */
	 jQuery('.rns_open_save').on('click',function(){

		 	jQuery('#rns_graph_view').hide();
			jQuery( '.rns-left-filters' ).hide();
			jQuery( '.rns-help-right-filters' ).hide();
			jQuery('#rns_saveform_view').show(); 
	 });
	 
	/* delete report script  */
	jQuery('.rns_delete_report').on('click',function(){
		
		var basePath    = jQuery('#plugin_url').val();
		var report_id   = jQuery('#rns_report_id').val(); 
		var report_link = jQuery('#report_link').val();  	
		
		
		
		if(confirm("Are you sure, you want to delete this report ? ")) { // confirmation for delete report 
			
				jQuery.get(basePath+'delete-report.php?report_id='+report_id,function(data) {
					
					if(data.success) {
						setTimeout(window.location.href=report_link,3000);	
					} else {
						
					}
					
				});	
		}
		 	
	 });
	 
	 /* cancel the view of save report form */
	 jQuery('.rns_close_report_view').on('click',function(){
			jQuery('#rns_saveform_view').hide();
			jQuery('#rns_graph_view').show();
			jQuery( '.rns-left-filters' ).show();
	 });
	 
	 
});


/*  save report form validation */
jQuery("#rns_save_button").on('click',function() {
	
	var basePath = jQuery('#plugin_url').val();
	
	/* if short name is empty */
	if(jQuery('#sname').val()=="") {
		jQuery('#sname').parent().find('.rns-field_err').html("Please enter short name ");
	} else {
		jQuery('#sname').parent().find('.rns-field_err').html("");	
	}
	
	/* if long name is empty */
	if(jQuery('#lname').val()=="") {
		jQuery('#lname').parent().find('.rns-field_err').html("Please enter short name ");
	} else {
		jQuery('#lname').parent().find('.rns-field_err').html("");	
	}
	
	/* if description is empty */
	if(jQuery('#desc').val()=="") {
		jQuery('#desc').parent().find('.rns-field_err').html("Please enter description ");
	} else {
		jQuery('#desc').parent().find('.rns-field_err').html("");	
	}
	
	/* if report order value is empty */
	if(jQuery('#report_order').val()=="") {
		jQuery('#report_order').parent().find('.rns-field_err').html("Please enter report order ");
	} else {
		jQuery('#report_order').parent().find('.rns-field_err').html("");	
	}
	
	/* if no role selected  */
	if(jQuery('.rns_role_type:checked').length==0) {
		jQuery('#rns_role_type_err').html("Please select atleast one role.");
	} else {
		jQuery('#rns_role_type_err').html("");	
	}
	
	/* if no error displaying */
	if(jQuery('.rns-field_err:not(:empty)').length==0) {
	
		jQuery.post(basePath+'save-report.php',jQuery('#rns_save_form').serializeArray(),function(data) {
			
			if(data.success) { 
				jQuery('#rns_save_message').html(data.message);
				setTimeout("window.location.reload()",2500);	
			}
		});
	}

});




// uncheck the state value
function OnChangeStatus(str) {
	
    if( str == 'closed' ) {
		jQuery ( '.status-filter-check' ).prop('checked',false);
	}
}

/* Function used to make query string for search filters */
function applyFilters() {
	var queryArray = {};
	jQuery( '.filter-checkbox' ).each(function(){
		var eleName = jQuery(this).attr( 'name' );
		var tmpName = eleName.split('[');
		if(jQuery(this).is( ":checked" )){
			queryArray[tmpName[0]] = queryArray[tmpName[0]] || [];
			queryArray[tmpName[0]].push(jQuery(this).val()) ;
		}
	});
	
	
	var select1 = jQuery(".select2").parent(jQuery(this)).find("select"); // it's <select> element
	var ticket_author = '';
	// if ticket author slected from dropdown 
	if(select1.val()!=null) {
		 ticket_author = select1.val(); 
	}

	var queryString = '?post_type=ticket&page=' + jQuery('#rns_page').val() + '&action=' + jQuery('#rns_action').val() + '&search_filter=' +
	jQuery('#search-filter').val() + '&days=' + jQuery('#filter_days').val() + '&sDate=' + jQuery('#sdate').val() + '&eDate=' + jQuery('#edate').val()+        			    '&type_of_chart=' + jQuery('#type-of-chart').val() + '&second=' + jQuery("input[name=second]:checked").val()+ '&ticket_author=' + ticket_author;
	for(key in queryArray){
		queryString += '&' + key + '=' + queryArray[key].join(',');
	}
	

	jQuery( '.rns-filter-textbox' ).each(function(){
		if(jQuery(this).val() != '' ) {
			var eleName = jQuery(this).attr( 'name' );	
			queryString += '&' + eleName + '=' + jQuery(this).val();
		}
		
	});
	
	if( jQuery( '#report_id' ).val() != "" ) {	
		queryString += '&report_id=' + jQuery('#report_id').val();	
	}
	
	if( jQuery('#rns_drop_zero_rows').is(':checked')) {	
		queryString += '&drop_zero_rows=' + jQuery('#rns_drop_zero_rows').val();	
	}
	
	if( jQuery('#rns_drop_zero_columns').is(':checked')) {	
		queryString += '&drop_zero_columns=' + jQuery('#rns_drop_zero_columns').val();	
	}
	
	
	
	window.location.href = queryString;
					
}

/* function to post custom field value  */
function post_custom_fields(){

	var data = { action: 'post_custom_fields','wpas_custom_field_type[]' : []};
	jQuery(".wpas_custom_field_type").each(function() {
		
		if(jQuery(this).is(":checked"))
		  data['wpas_custom_field_type[]'].push(jQuery(this).val());
	});
	jQuery.ajax({
		url: rsa.ajaxurl,
		 data:data,
		 type:'post',
		 dataType: 'html',
		 success: function( response ) {
			//$('.description').html(response);
			// console.log(response);  
		 }
	});
}

/* function to remove the selected client value  */
function rns_set_client_empty() {
	var select_c = jQuery('#ticket_author');
    jQuery('option:selected',select_c).removeAttr('selected');	
	jQuery('.select2-selection__choice').remove();
}