<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class wlb_visual_css_editor_multisite {
	function __construct( $args=array() ){		
		//------------
		$defaults = array(
			'id'				=> 'acewlbms'
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}

		//-----------
		add_action('rh_css_editor_actions', array( &$this, 'rh_css_editor_actions'), 10, 1 );
		add_action('rh_css_editor_wp_head', array( &$this, 'rh_css_editor_wp_head'), 10, 1 );
		
		add_action('saved-rh-css', array( &$this, 'handle_saved_rh_css' ), 10, 2 );
		add_action('removed-rh-css', array( &$this, 'handle_saved_rh_css' ), 10, 2 );
		add_action('restored-rh-css', array( &$this, 'handle_saved_rh_css' ), 10, 2 );
		
		add_filter( 'option_enabled_google_fonts', array( &$this, 'option_enabled_google_fonts'), 10, 1 );
	}
	
	function option_enabled_google_fonts( $value ){
		global $wlb_plugin;
		
		$value = is_array( $value) ? $value : array() ;
		
		$blog_branding_type = $wlb_plugin->get_site_option('blog_branding_type',0);	
		if( '1'==$blog_branding_type ){
			$value = get_site_option( 'enabled_google_fonts', array() );
		}

		if(1==$blog_branding_type){//Option 2
			$value = get_site_option( 'enabled_google_fonts', array() );
		}else if(2==$blog_branding_type){//Option 3
			//do nothing
		}else if(3==$blog_branding_type){//Option 4
			// fallback is not supported, only default used. or fully fallback to default
			if( empty($value) ){
				$value = get_site_option( 'enabled_google_fonts', array() );
			}		
		}else{//Option 1
			if( empty($value) ){
				$value = get_site_option( 'enabled_google_fonts', array() );
			}
		}		
		
		return $value;
	}
	
	function handle_saved_rh_css( $options_varname, $options ){
		global $wlb_plugin;
		if( !$wlb_plugin->is_wlb_network_admin() ) return ;
		$blog_branding_type = $wlb_plugin->get_site_option('blog_branding_type',0);		
		//Note: if blog branding type os Option 2 (value=1), always push to site options.
		if( '1'==$blog_branding_type || isset($_REQUEST['push_site_options']) ){
			update_site_option( $options_varname, $options );	
			//--- update google fonts
			$google_fonts = get_option( 'enabled_google_fonts', array() );
			$google_fonts = is_array($google_fonts)? $google_fonts : array();
			update_site_option( 'enabled_google_fonts', $google_fonts );
		}
	}
	
	function rh_css_editor_actions( $ed ){
		global $wlb_plugin;
		if( $ed->id != $this->id ) return ;
		if( !$wlb_plugin->is_wlb_network_admin() ) return ;
		echo sprintf('<li><a id="btn-wlbms-save-global-default" href="javascript:void(0);" class="">%s</a></li>',
			__('Save as Global Default','rhcss')
		);
	}
	
	function rh_css_editor_wp_head( $ed ){
		if( $ed->id != $this->id ) return ;
?>
<script>
jQuery(document).ready(function($){
	$('#btn-wlbms-save-global-default').unbind('click').bind('click', function(e){
		wlbms_ace_css_save(e);
		return true;
	});
});
function wlbms_ace_css_save(e){
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
			push_site_options: true,
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
</script>
<?php 	
	}
	
	function get_option( $options_varname='' ){
		global $wlb_plugin;
		$blog_branding_type = $wlb_plugin->get_site_option('blog_branding_type',0);
		$options = array();
		if(1==$blog_branding_type){//Option 2
			$options = get_site_option( $options_varname );	
				
		}else if(2==$blog_branding_type){//Option 3
			$options = get_option( $options_varname );	
			
		}else if(3==$blog_branding_type){//Option 4
			// fallback is not supported, only default used. or fully fallback to default
			$options = get_option( $options_varname );
			$options = is_array( $options )? $options : array() ;		
			//or Default Branding
			if( !isset($options['css_output_sections']) || 0 == count( $options['css_output_sections'] ) ){
				$options = get_site_option( $options_varname );			
			}			
		}else{//Option 1
			$options = get_option( $options_varname );
			$options = is_array( $options )? $options : array() ;		
			//or Default Branding
			if( !isset($options['css_output_sections']) || 0 == count( $options['css_output_sections'] ) ){
				$options = get_site_option( $options_varname );			
			}			
		}

		return is_array( $options )? $options : array() ;
	}
}
?>