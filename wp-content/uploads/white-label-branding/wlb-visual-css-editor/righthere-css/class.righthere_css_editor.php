<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class righthere_css_editor {
	var $id = "";
	var $section = "";
	var $with_scope = true;
	var $url = "";
	var $path = "";
	var $options = array();
	var $options_callback = null;
	var $debug = false;
	var $file_queue_options_name = 'rhcss_file_queue';
	var $development = false;
	var $overlay = true;
	var $cb_get_option=false;
	var $resources_path='';
	var $push_body_on_editor=true;
	var $cb_init=false;
	var $detect_selector='';
	var $bootstrap_in_footer=false;
	var $in_admin=false;
	var $alternate_accordion=false;
	var $in_footer=false;
	function __construct($args=array()){
		foreach($args as $property => $value){
			$this->$property = $value;
		}
		//---		
		add_action('wp_head',array(&$this,'wp_head'));
		add_action('wp_footer',array(&$this,'wp_footer'));
		if($this->in_admin){
			add_action('admin_head',array(&$this,'wp_head'));
			add_action('admin_footer',array(&$this,'wp_footer'));		
		}
		if($this->in_login){
			add_action('login_head',array(&$this,'wp_head'));
			add_action('login_footer',array(&$this,'wp_footer'));		
			add_action('login_enqueue_scripts', array(&$this,'enqueue_styles_and_scripts'), 10);
		}
		
		//$this->enqueue_styles_and_scripts();		
		add_action('wp_enqueue_scripts', array(&$this,'enqueue_styles_and_scripts'), 10);
		add_action('admin_enqueue_scripts', array(&$this,'enqueue_styles_and_scripts'), 10);
	}
	
	function enqueue_styles_and_scripts(){
		rh_enqueue_script( 'bootstrap', $this->url.'bootstrap/js/bootstrap.js', array('jquery'),'3.0.0', $this->bootstrap_in_footer);
		rh_enqueue_script( 'bootstrap-renamed', $this->url.'bootstrap/js/custom.js', array(),'3.0.0.1', $this->bootstrap_in_footer);
		
		wp_enqueue_style( 'bootstrap', 	$this->url.'bootstrap/css/namespaced.bootstrap.css', array(), '2.3.2');
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'tinycolor', 			$this->url.'js/tinycolor.js', array(), '1.0.1', $this->in_footer );
		
		wp_enqueue_script( 'rh-css-scripts', 		$this->url.'js/scripts.js', array(), '1.0.1', $this->in_footer );
		wp_register_script( 'rh-css-normalizers', 	$this->url.'js/rhl_css_normalizers.js', array(), '1.0.2', $this->in_footer );
		wp_register_script( 'rh-css-actions',	 	$this->url.'js/editor_actions.js', array(), '1.0.2', $this->in_footer );

		wp_register_script( 'jquery-fontSelector',	$this->url.'js/jquery.fontSelector.js', array(), '1.0.0', $this->in_footer );
		wp_register_script( 'jquery-rule', 			$this->url.'js/jquery.rule-1.0.2-dev.js', array(), '1.0.2', $this->in_footer );

		if( get_bloginfo( 'version' ) < '3.9' ){
			wp_enqueue_style( 'rh-css-edit', 	$this->url.'css/style.prewp39.css', array(), '1.0.0.2' );
			wp_enqueue_style( 'minicolors', 	$this->url.'css/jquery.miniColors.css', array(), '1.0.1' );
			wp_enqueue_script( 'minicolors', 	$this->url.'js/jquery.miniColors.js', array(), '1.0.0', $this->in_footer );
			wp_enqueue_script( 'rh-css-edit', 	$this->url.'js/rhl_css_edit.pre39.js', array('tinycolor','minicolors','rh-css-normalizers','rh-css-actions','jquery-fontSelector','jquery-rule'), '1.0.1.1', $this->in_footer );
		}else{
			wp_enqueue_style( 'rh-css-edit', 	$this->url.'css/style.css', array(), '1.0.0.3' );
			wp_enqueue_style( 'minicolors', 	$this->url.'css/jquery.minicolors.2015.css', array(), '1.0.2' );
			wp_enqueue_script( 'minicolors', 	$this->url.'js/jquery.minicolors.2015.min.js', array(), '1.0.2', $this->in_footer );
			wp_enqueue_script( 'rh-css-edit', 	$this->url.'js/rhl_css_edit.js', array('tinycolor','minicolors','rh-css-normalizers','rh-css-actions','jquery-fontSelector','jquery-rule'), '1.0.1.8', $this->in_footer );		
		}
		

	}

	function less(){
		$uploaded_files = get_option($this->file_queue_options_name,array());
		$uploaded_files = is_array($uploaded_files)?$uploaded_files:array();
?>
<?php if($this->push_body_on_editor): ?>
<style>
BODY.rhcss-editor-active {
margin-left:300px !important;
padding-left:0 !important;
}
</style>
<?php endif; ?>
<script>try{var uploaded_files = <?php echo json_encode($uploaded_files)?>;
var rh_ajax_url = '<?php echo admin_url('/admin-ajax.php')?>';
var _unexpected_error='<?php _e('Unexpected error, reload and try again.','rhl')?>';}catch(e){}
var rh_detect_selector = '<?php echo $this->detect_selector?>';
</script>
<?php if(is_callable($this->cb_init)) call_user_func($this->cb_init); ?>
<?php	

	}
		
	function wp_head(){
		wp_print_scripts('plupload-all');
		$this->less();
		do_action( 'rh_css_editor_wp_head', $this );
	}
	
	function wp_footer(){
		$options = call_user_func( $this->options_callback, array() );
		if(count($options)==0)return;
	
		require_once $this->path.'class.rhcss_form_renderer.php';
		$this->input_renderer = new rhcss_form_renderer(array(
			'id'=>$this->id,
			'debug'=>$this->debug,
			'cb_get_option'=>$this->cb_get_option,
			'resources_path'=>$this->resources_path,
			'in_footer'=>$this->in_footer
		));
	
		$this->accordion_options($options);
		
		$this->modal_form();
		$this->alternate_accordion();
		
		do_action( 'rh_css_editor_wp_footer', $this );
	}
	
	function alternate_accordion(){
		if($this->alternate_accordion){
?>
<script>
jQuery(document).ready(function($){
	$('.rh-css-edit-form .accordion-toggle').on('click',function(e){	
		e.stopPropagation();
		$(this).parent().parent().find('.accordion-body').toggleClass('in');
		return false;
	});
});
</script>
<?php			
		}	
	}
	
	function accordion_options($options){
		$class = array();
		if( function_exists('is_admin_bar_showing') && is_admin_bar_showing() ){
			$class[]='admin-bar-showing';
		}
		if($this->overlay){
			$class[]='with-overlay';
		}
?>
<div style="display:none;" class="body-child rh-css-edit rh-css-edit-form <?PHP echo implode(' ',$class)?>">
	<form id="rh-css-form" class="rh-css-form" name="rh-css-form" method="post" action="">
	<input id="rh-editor-id" type="hidden" value="<?php echo $this->id?>" />
	<input id="rhcss_section" type="hidden" name="section" value="<?php echo $this->section?>" />
	<div class="rh-css-controls rh-css-edit rh-css-edit rhl-vertical">
		
		<div class="btn-group reset-control-group">
		  <a class="btn btn-danger dropdown-toggle" data-toggle="dropdown" href="#">
		    <?php _e('Action','rhcss') ?>
		    <span class="caret"></span>
		  </a>
		  <ul class="dropdown-menu">
		    <li><a id="btn-reset-css" href="javascript:void(0);" class=""><?php _e('Reset current settings','rhcss')?></a></li>
		    <li><a id="btn-remove-css" href="javascript:void(0);" class=""><?php _e('Remove all customization','rhcss')?></a></li>
			<?php if($this->with_scope): ?>
			<li><a id="btn-scope" href="javascript:void(0);" class=""><?php _e('Scope','rhcss')?></a></li>
			<?php endif;?>
			<?php do_action( 'rh_css_editor_actions', $this )?>
		  </ul>
		</div>		
		
		<div class="rhl_loading"><img src="<?php echo $this->url?>css/images/spinner_32x32.gif" /></div>
		
		<a id="btn-save" href="javascript:void(0);" class="btn btn-primary btn-save-settings" data-loading-text="<?php _e('Saving','rhcss')?>"><?php _e('Save','rhcss')?></a>

		<div class="rh-css-scope-controls">
			
			<div class="fld-scope">
				<label class="scope-label"><?php _e('Scope')?></label>
				<input id="rhcss_scope" type="text" name="scope" value="" />
				<div class="scope-controls">
					<a id="btn-scope-remove" href="javascript:void(0);" class="btn btn-danger" data-loading-text="<?php _e('Removing','rhcss')?>" tilte="<?php _e('Remove scope settings','rhcss')?>"><?php _e('Remove scope settings','rhcss')?></a>
					<a id="btn-scope-done" href="javascript:void(0);" class="btn"><?php _e('Hide','rhcss')?></a>
				</div>		
			</div>
		</div>
	</div>
	<div class="rh-css-slides rh-css-edit rhl-vertical">
		<div class="ajax-result-messages"></div>
		<?php $this->bootstrap_accordion_html($options);?>
	</div>
	<div class="rh-css-controls-bottom">
		
	</div>
	</form>
</div>
<div class="rh-css-edit body-child">
	<a href="javascript:void(0);" class="btn btn-primary btn-collapse" data-loading-text="<?php _e('Open','rhcss')?>" ><?php _e('Collapse','rhcss')?></a>		
</div>
<?php		
	}	
	
	function bootstrap_accordion_html($options){
?>
		<div id="accordion2" class="rhcss-accordion">
			<?php foreach($options as $i => $tab): ?>
			<div class="accordion-group">
				<div class="accordion-heading">
					<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#<?php echo $tab->id?>"><?php echo $tab->label?></a>
				</div>
				<div id="<?php echo $tab->id?>" class="accordion-body collapse">
					<div class="rhcss-accordion-inner">
						<?php $this->render_slide( $tab, $options, $i, 'accordion' ) ?>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
<?php	
	}
	
	function render_slide($tab,$options,$options_i,$type="tab"){
		if(count($tab->options)==0){
			_e('No options available on this section.','rhcss');
			return;
		}
		
		foreach($tab->options as $i => $option){
			if(@$option->input_type && 'callback'==$option->input_type && is_callable($option->callback)){
				call_user_func($option->callback,$option,$i,$options,$options_i);
			}else{
				$this->input_renderer->render($option,$i,$options,$options_i);
			}
		}
	}	
	
	function modal_form(){
?>
<div class="rh-css-edit">
	<div class="rh_editor_modal modal hide fade">
	  <div class="modal-header">
	    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	    <h3>Modal header</h3>
	  </div>
	  <div class="modal-body">
	    <p>One fine body…</p>
	  </div>
	  <div class="modal-footer">
	    <a href="#" class="btn">Close</a>
	    <a href="#" class="btn btn-primary">Save changes</a>
	  </div>
	</div>
</div>
<?php	
	}
}
?>