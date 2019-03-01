// JavaScript Document
(function ($) {
	"use strict";

	$( function () {
		function getAlgoDescription() {
			
			jQuery.ajax( {
				
				url		: esma.ajaxurl,
				data	:{ action: 'post_algo' , algo : $( 'select' ) . val()},
				type	:'post',
				dataType: 'html',
				success	: function( response ) {
					$( '.description:first' ) . html( response ); // note the user of the pseudo-element FIRST so that just the first description item on the screen is updated with the description of the algo.
				}
				
			});
			
		}
		
		function updateRoleSelection() {
			
			var data	=	{ action: 'post_user_roles' , 'wpas_agent_role_type[]' : [] };
			
			$( ".wpas_agent_role_type" ) . each( function() {
				
				if( $( this ) . is( ":checked" ) )
				  data['wpas_agent_role_type[]'] . push( $( this ).val() );
				  
			});
			jQuery.ajax({
				
				url		:	esma.ajaxurl,
				data	:	data,
				type	:	'post',
				dataType:	'html',
				success	:	function( response ) {

				}
			});
		}
		
		getAlgoDescription();
		
		$( 'select' ) . on ( 'change' , function() {
			getAlgoDescription();
		});
		
		$( ".wpas_agent_role_type" ) . on ( 'change' , function() {
			updateRoleSelection();
		})
	});
	
}(jQuery));