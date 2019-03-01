// JavaScript Document
(function ($) {
	"use strict";
	$(function () {

		function disabletimeinput(){
			
			$( ".day" ) . each( function() {
						
				if( !$( this ) . is( ':checked' ) ){
					var cid	=	$(this).attr('id');
					$( "." + cid + "_daterange input" ).attr( 'disabled' , 'disabled' );
				}
				
			});
			
		}
		
		disabletimeinput();
		
		$( '.day' ) . on( 'change' , function() {	
		
			var cid	=	$( this ) . attr( 'id' );
			if( $( this ).is( ':checked' ) ) {	
			
				$( "." + cid + "_daterange input" ) . removeAttr( 'disabled' );		
				$( "." + cid + "_daterange input" ) . eq(0).val( "12:00am" );	
				$( "." + cid + "_daterange input" ) . eq(1).val( "11:59pm" );
					
			}else{
				
				$( "." + cid + "_daterange input" ) . val( '' );
				$( "." + cid + "_daterange input" ) . attr( 'disabled' , 'disabled' );
				
			}
			
		});
	
		$( '.daterange .time' ) . timepicker( {
			
			'showDuration'	:	true,
			'timeFormat'	:	'g:ia',
			'step'			:	15,
			
		} );
		
		$( '.time' ) . keyup( function() {
			
		} );
		$( '.daterange' ) . datepair();
		
	});
	
}( jQuery ) );