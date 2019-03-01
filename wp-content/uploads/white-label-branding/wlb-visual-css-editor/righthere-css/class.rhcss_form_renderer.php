<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class rhcss_form_renderer {
	var $id;
	var $debug;
	var $cb_get_option;
	var $resources_path;
	var $wp_footer = '';
	function __construct($args=array()){
		$defaults = array(
			'id'						=> 'plugin-righthere-css',
			'debug'						=> false,
			'cb_get_option'				=> false,
			'resources_path'			=> '',
			'in_footer'					=> false
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}
		//---	
	}

	function get_option($name,$default='',$default_if_empty=true){
		if(is_callable($this->cb_get_option)){
			$str = call_user_func($this->cb_get_option,$name,$default,$default_if_empty);
			//----
			$upload_dir = wp_upload_dir();					
			$dcurl = $upload_dir['baseurl'].'/'.$this->resources_path.'/';
			$str = str_replace('{dcurl}',$dcurl,$str);			
			//----	
			return $str;
		}else if($default_if_empty){
			return $default;
		}	
		return '';
	}
	
	function render($option,$option_i=null,$options=null,$options_i=null){
		$method = 'render_'.$option->input_type;
		$method = method_exists($this,$method)?$method:'render_text';
		
		//-- render a unique element from wish we can read the computed style
		/* fills the fields with values, wich is currently undersired. if the customization is not done, we want it empty.
		if(property_exists($option,'id') && property_exists($option,'selector') && property_exists($option,'type') && $option->type=='css'){
			if(!empty($option->selector)){
				$helper_id = 'helper_'.$option->id;
				$option->selector = '#'.$helper_id.', '.$option->selector;
				echo sprintf('<div style="display:none;"><div id="%s"></div></div>',$helper_id);
			}
		}
		*/
		//--
		$this->$method($option, $option_i ,$options, $options_i );	
	}
	
	function render_grid_start($option,$option_i=null,$options=null,$options_i=null){
?>
<div class="row-fluid <?php $this->input_extra_class($option,$option_i,$options,$options_i)?>">
<?php
	}

	function render_toggle_button($option,$option_i=null,$options=null,$options_i=null){
		$name = property_exists($option,'name')?$option->name:$option->id;
?>
	<button id="<?php echo $option->id?>" type="button" data-toggle="button" class="btn input-field-input <?php $this->input_extra_class($option,$option_i,$options,$options_i)?>" <?php $this->input_extra_properties($option,$option_i,$options,$options_i)?> ><?php echo $option->label?></button>
<?php		
	}

	function render_font($option,$option_i=null,$options=null,$options_i=null){
		$name = property_exists($option,'name')?$option->name:$option->id;
		$unit = property_exists($option,'unit')?$option->unit:false;
		$bold_button = property_exists($option,'bold')? ($option->bold?true:false) : true ;
		
		$enabled_fonts = apply_filters('rhcss-enabled-fonts',array());
		$enabled_fonts = is_array($enabled_fonts)&&count($enabled_fonts)>0?$enabled_fonts:array("Times New Roman, Times, serif","Arial, Helvetica, sans-serif","Arial, Helvetica, sans-serif","Arial, Helvetica, sans-serif","Arial, Helvetica, sans-serif");		
		array_unshift($enabled_fonts,'inherit');
?>
<div id="<?php echo $option->id?>-holder" class="input-field <?php $this->holder_extra_class($option) ?>">
	<span class="field-label label-for-<?php echo $option->input_type?> label-for-<?php echo $option->id?>"><?php echo $option->label?></span>
	<input id="<?php echo $option->id?>-style" type="hidden" class="input-field-input real-time default-value-from-css sup-input-font" data-selected-value="italic" data-css-property="font-style" data-css-selector="<?php echo @$option->selector?>" data-rhcss_type="css" />
	<input id="<?php echo $option->id?>-weight" type="hidden" class="input-field-input real-time default-value-from-css sup-input-font" data-selected-value="bold" data-css-property="font-weight" data-css-selector="<?php echo @$option->selector?>" />
	
	<div class="rhcss-font-selector-cont">
		<div class="input-append">
			<input id="<?php echo $option->id?>" type="text" name="<?php echo $name?>" value="" class="input-field-input input-font-family <?php $this->input_extra_class($option,$option_i,$options,$options_i)?>" <?php $this->input_extra_properties($option,$option_i,$options,$options_i)?> />
			<button id="<?php echo $option->id?>-style-helper" data-input-parent="#<?php echo $option->id?>-style" type="button" class="btn sup-italic sup-input-font-helper" data-toggle="button"  >I</button>
			<?php if($bold_button): ?>
			<button id="<?php echo $option->id?>-weight-helper" data-input-parent="#<?php echo $option->id?>-weight" type="button" class="btn sup-bold sup-input-font-helper" data-toggle="button"  >B</button>			
			<?php endif; ?>
		</div>		
	</div>
	<div class="clear"></div>
	<div style="display:none;">
		<div id="helper_<?php echo $option->id?>-style"></div>
		<div id="helper_<?php echo $option->id?>-weight"></div>
	</div>
</div>
<?php	
		ob_start();
?>
<script type="text/javascript">jQuery(document).ready(function($){$('#<?php echo $option->id?>').fontSelector(<?php echo json_encode($enabled_fonts) ?>);});</script>
<?php
		$init_font_selector = ob_get_contents();
		ob_end_clean();
		
		if( $this->in_footer ){
			$this->wp_footer.= $init_font_selector;
			add_action( 'wp_footer', array( &$this, 'wp_footer' ), 99999 );
		}else{
			echo $init_font_selector;
		}
	}
	
	function wp_footer(){
		echo $this->wp_footer;
	}
	
	function render_grid_end($option,$option_i=null,$options=null,$options_i=null){
		echo "</div>";
	}
		
	function render_text($option,$option_i=null,$options=null,$options_i=null){
		$input_type = property_exists($option,'input_type')?$option->input_type:'text';
		$name = property_exists($option,'name')?$option->name:$option->id;
		$unit = property_exists($option,'unit')?$option->unit:false;
?>
<div id="<?php echo $option->id?>-holder" class="input-field <?php $this->holder_extra_class($option) ?>">
	<span class="field-label label-for-<?php echo $option->input_type?> label-for-<?php echo $option->id?>"><?php echo $option->label?></span>
	<div class="<?php echo false!==$unit?'input-append':'';?>">
		<input id="<?php echo $option->id?>" type="<?php echo $input_type?>" name="<?php echo $name?>" value="" class="input-field-input <?php $this->input_extra_class($option,$option_i,$options,$options_i)?>" <?php $this->input_extra_properties($option,$option_i,$options,$options_i)?> />
		<?php if(false!==$unit): ?>
			<span class="add-on"><?php echo $option->unit?></span>
		<?php endif; ?>
	</div>
</div>
<?php		
	}
	
	function render_number($option,$option_i=null,$options=null,$options_i=null){
		$option->input_type = 'number';
		return $this->render_text($option,$option_i,$options,$options_i);
	}
		
	function render_select($option,$option_i=null,$options=null,$options_i=null){
		$name = property_exists($option,'name')?$option->name:$option->id;
?>
<div id="<?php echo $option->id?>-holder" class="input-field <?php $this->holder_extra_class($option) ?>">
	<span class="field-label label-for-<?php echo $option->input_type?> label-for-<?php echo $option->id?>"><?php echo $option->label?></span>
	<div class="rhce-input-holder">
		<select id="<?php echo $option->id?>" type="text" name="<?php echo $name?>" class="input-field-input <?php $this->input_extra_class($option,$option_i,$options,$options_i)?>" <?php $this->input_extra_properties($option,$option_i,$options,$options_i)?>>
		<?php foreach($option->options as $value => $label):?>
			<option value="<?php echo $value?>"><?php echo $label?></option>
		<?php endforeach; ?>
		</select>	
	</div>
</div>
<?php	
	}
	
	function render_background_position($option,$option_i=null,$options=null,$options_i=null){
?>
<div id="<?php echo $option->id?>-holder" class="input-field <?php $this->holder_extra_class($option) ?>">
	<span class="field-label label-for-<?php echo $option->input_type?> label-for-<?php echo $option->id?>"><?php echo $option->label?></span>
	<select id="<?php echo $option->id?>" type="text" name="<?php echo $name?>" class="input-field-input <?php $this->input_extra_class($option,$option_i,$options,$options_i)?>" <?php $this->input_extra_properties($option,$option_i,$options,$options_i)?>>
	<?php foreach($option->options as $value => $label):?>
		<option value="<?php echo $value?>"><?php echo $label?></option>
	<?php endforeach; ?>
	</select>
	<span>
		<label>X</label>
		<input id="<?php echo $option->id?>-x" name="<?php echo $option->id?>-x" type="text" class="bg-position" value="" />
		<label>Y</label>
		<input id="<?php echo $option->id?>-y" name="<?php echo $option->id?>-y" type="text" class="bg-position" value="" />
	</span>
</div>
<?php
	}
	
	function render_subtitle($option,$option_i=null,$options=null,$options_i=null){
?>
<div class="input-field <?php $this->holder_extra_class($option) ?>">
	<span class="field-label <?php $this->input_extra_class($option,$option_i,$options,$options_i)?>"><?php echo $option->label?></span>
</div>
<?php
	}
	
	function render_slider($option,$option_i=null,$options=null,$options_i=null){
		$name = property_exists($option,'name')?$option->name:$option->id;
?>
<div id="<?php echo $option->id?>-holder" class="input-field input-append pt-option-range <?php $this->holder_extra_class($option) ?>">
	<span class="field-label label-for-<?php echo $option->input_type?> label-for-<?php echo $option->id?>"><?php echo $option->label?></span>
	<input rel="test" id="<?php echo $option->id?>" type="range" min="<?php echo $option->min?>" max="<?php echo $option->max?>" step="<?php echo $option->step?>" name="<?php echo $name?>" value="<?php echo $option->default?$option->default:0;?>" class="input-field-input <?php $this->input_extra_class($option,$option_i,$options,$options_i)?>" <?php $this->input_extra_properties($option,$option_i,$options,$options_i)?> />
	<div class="rhl-clear"></div>
</div>
<?php		
	}
	
	function render_element_size($option,$option_i=null,$options=null,$options_i=null){
		$name = property_exists($option,'name')?$option->name:$option->id;
?>
<div id="<?php echo $option->id?>-holder" class="input-field input-append pt-option-range <?php $this->holder_extra_class($option) ?>">
	<span class="field-label label-for-<?php echo $option->input_type?> label-for-<?php echo $option->id?>"><?php echo $option->label?></span>
	<input rel="test" id="<?php echo $option->id?>" type="range" min="<?php echo $option->min?>" max="<?php echo $option->max?>" step="<?php echo $option->step?>" name="<?php echo $name?>" value="<?php echo $option->default?$option->default:0;?>" class="input-field-input <?php $this->input_extra_class($option,$option_i,$options,$options_i)?>" <?php $this->input_extra_properties($option,$option_i,$options,$options_i)?> />
	<div class="rhl-clear"></div>
</div>
<?php	
	}
	
	function render_color_or_something_else($option,$option_i=null,$options=null,$options_i=null){
		$other_options = property_exists($option,'other_options') && $option->other_options && is_array($option->other_options)?$option->other_options:false;
		if(false===$other_options){
			return $this->render_colorpicker($option,$option_i,$options,$options_i);
		}
		$opacity = $option->opacity?true:false;
?>
<div id="<?php echo $option->id?>-holder" class="input-field input-field-color_or_something_else <?php $this->holder_extra_class($option) ?>">
	<span class="field-label label-for-<?php echo $option->input_type?> label-for-<?php echo $option->id?>"><?php echo $option->label?></span>
	<div class="colorpicker-input">
		<input id="<?php echo $option->id?>" value='' type="<?php echo $this->debug?'text':'hidden'?>" class="input-field-input color_or_something_else with-alternate-color-value <?php $this->input_extra_class($option,$option_i,$options,$options_i)?>" <?php $this->input_extra_properties($option,$option_i,$options,$options_i)?> />
		<select id="<?php echo $option->id?>-options" class="color-or-something-options alternate-color-values" data-target-selector="#<?php echo $option->id?>">
			<?php if($option->btn_clear):?>
			<option value="">&nbsp;</option>
			<?php endif; ?>
			<option value="color"><?php _e('Color','rhl')?></option>
			<?php foreach($option->other_options as $val => $label):?>
			<option value="<?php echo $val?>" class="alternate-color-value"><?php echo $label?></option>
			<?php endforeach; ?>
		</select>
		<div class='input-minicolors-hold'>
			<input id="<?php echo $option->id?>-color" data-target-selector="#<?php echo $option->id?>" value='' type="text" class="input-minicolors <?php echo $opacity?'with-opacity':'';?> input-field-input colorpicker-preview-content colorpicker-input-field sub_color_or_something_else" />
		</div>
		<?php if($option->btn_clear):?>
		<div class="image-url-button-clear-cont">
			<input type="button" class="btn btn_clear_generic" data-clear-level='2' value="Clear" />
		</div>
		<?php endif; ?>	
	</div>
	<div class="rhl-clear"></div>
</div>
<?php		
	}
	
	function render_colorpicker($option,$option_i=null,$options=null,$options_i=null){
		$opacity = $option->opacity?true:false;
		$opacity = false; //this is missing the javascript to geenrate the color code with opacity.
?>
<div id="<?php echo $option->id?>-holder" class="input-field <?php $this->holder_extra_class($option) ?>">
	<span class="field-label label-for-<?php echo $option->input_type?> label-for-<?php echo $option->id?>"><?php echo $option->label?></span>
	<div class="colorpicker-input">
		<div class="colorpicker-preview">
			<input id="<?php echo $option->id?>" value='' type="text" class="input-minicolors <?php echo $opacity?'with-opacity':'';?> input-field-input colorpicker-preview-content colorpicker-input-field <?php $this->input_extra_class($option,$option_i,$options,$options_i)?>" <?php $this->input_extra_properties($option,$option_i,$options,$options_i)?> />
			<?php if($option->btn_clear):?>
			<input type="button" class="btn btn_clear_generic" value="Clear" />
			<?php endif; ?>
		</div>
		
	</div>
	
	<div class="rhl-clear"></div>
</div>
<?php	
	}

	function render_color_gradient($option,$option_i=null,$options=null,$options_i=null){
		$opacity = $option->opacity?true:false;
?>
<div id="<?php echo $option->id?>-holder" class="input-field <?php $this->holder_extra_class($option) ?>">
	<span class="field-label label-for-<?php echo $option->input_type?> label-for-<?php echo $option->id?>"><?php echo $option->label?></span>
	
	<div class="colorpicker-input">
		<div class="colorpicker-preview colorpicker_gradient">
			<input id="<?php echo $option->id?>" value='' type="<?php echo $this->debug?'text':'hidden'?>" class="input-field-input colorpicker_gradient <?php $this->input_extra_class($option,$option_i,$options,$options_i)?>" <?php $this->input_extra_properties($option,$option_i,$options,$options_i)?> />
			
			<input id="<?php echo $option->id?>-start" value='' type="text" class="input-minicolors <?php echo $opacity?'with-opacity':'';?> colorpicker-preview-content colorpicker-input-field sub_colorpicker_gradient" />
			
			<input id="<?php echo $option->id?>-end" value='' type="text" class="input-minicolors <?php echo $opacity?'with-opacity':'';?> colorpicker-preview-content colorpicker-input-field sub_colorpicker_gradient right-colorpicker" />
			
			<?php if($option->btn_clear):?>
			<input type="button" class="btn btn_clear_gradient" value="Clear" />
			<?php endif; ?>
		</div>
		
	</div>
	<div class="rhl-clear"></div>
</div>
<?php	
	}

	function render_textshadow($option,$option_i=null,$options=null,$options_i=null){
		$opacity = $option->opacity?true:false;
?>
<div id="<?php echo $option->id?>-holder" class="input-field <?php $this->holder_extra_class($option) ?>">
	<span class="field-label label-for-<?php echo $option->input_type?> label-for-<?php echo $option->id?>"><?php echo $option->label?></span>
	
	<div class="colorpicker-input">
		<div class="colorpicker-preview">
			<input id="<?php echo $option->id?>" value='' type="<?php echo $this->debug?'text':'hidden'?>" class="input-field-input colorpicker_textshadow <?php $this->input_extra_class($option,$option_i,$options,$options_i)?>" <?php $this->input_extra_properties($option,$option_i,$options,$options_i)?> />
			
			<input id="<?php echo $option->id?>-color" value='' type="text" class="input-minicolors <?php echo $opacity?'with-opacity':'';?> colorpicker-preview-content colorpicker-input-field  text-shadow-field text-shadow-color"  />
			
			<?php if($option->btn_clear):?>
			<input type="button" class="btn btn_none_text_shadow" value="<?php _e('None','rhcss')?>" />
			<input type="button" class="btn btn_clear_text_shadow" value="<?php _e('Clear','rhcss')?>" />
			<?php endif; ?>
		</div>
		<div class="colorpicker-preview text-shadow-extra">
			<span class="bootstrap-tooltip" data-tooltip-position="fixed" rel="tooltip" title="<?php _e('Horizontal position of the shadow','pop') ?>"><?php _e('H:','pop')?></span>
			<input id="<?php echo $option->id?>-h" class="input-text-shadow-extra text-shadow-field text-shadow-h" type="number" value="" />
			<span class="bootstrap-tooltip" rel="tooltip" title="<?php _e('Vertical position of the shadow','pop') ?>"><?php _e('V:','pop')?></span>
			<input id="<?php echo $option->id?>-v" class="input-text-shadow-extra text-shadow-field text-shadow-v" type="number" value="" />			
			<span class="bootstrap-tooltip" rel="tooltip" title="<?php _e('Shadow Blur','pop') ?>"><?php _e('Blur:','pop')?></span>
			<input id="<?php echo $option->id?>-b" class="input-text-shadow-extra text-shadow-field text-shadow-b" type="number" value="" />
		</div>
	</div>
	
	<div class="rhl-clear"></div>
</div>
<?php	
	}
	
	function render_image_url($option,$option_i=null,$options=null,$options_i=null){
		$queue = property_exists($option,'queue') ? $option->queue : $option->id ; 
		$upload_list = $this->get_option($queue.'-upload-list','',true);
?>
<div id="<?php echo $option->id?>-holder" class="input-field rhl-image-uploader">
	<span class="field-label label-for-<?php echo $option->input_type?> label-for-<?php echo $option->id?>"><?php echo $option->label?></span>
	<input id="<?php echo $option->id?>" value='' type="<?php echo $this->debug?'text':'hidden'?>" class="input-field-input rhl_image_uploader <?php $this->input_extra_class($option,$option_i,$options,$options_i)?>" <?php $this->input_extra_properties($option,$option_i,$options,$options_i)?> />
	<div class="rhl-image-uploader-control">
		<div class="dropdown preview-thumbnail">
			<div class="dropdown-content">
				<img style="display:none;">
				<div class="dropdown-status" style="display: block; ">No Image</div>
			</div>
			<div class="dropdown-arrow rhl-image-uploader-helper-trigger"></div>
		</div>
		<?php if($option->btn_clear):?>
		<div class="image-url-button-clear-cont">
			<input type="button" class="btn btn_clear_image_url" value="Clear" />
		</div>
		<?php endif; ?>  			
		<div class="rhl-clear"></div>
	</div>
		
	<div class="rhl-clear"></div>
	
   <div id="<?php echo $option->id?>-msg" class="rhl-image-upload-msg"></div>
   
	<div class="rhl-image-upoader-helper helper-closed">
		<ul class="nav nav-tabs">
			<li class="active"><a class="rhl-upload-new" href="#<?php echo $option->id?>-upload" data-toggle="tab"><?php _e('Upload new','rhl')?></a></li>
			<li class="rhl-uploaded-images-tab"><a href="#<?php echo $option->id?>-uploaded" data-toggle="tab"><?php _e('Uploaded','rhl')?></a></li>
		</ul>
		<div class="tab-content">
			<div id="<?php echo $option->id?>-upload" class="tab-pane active">

				<div id="<?php echo $option->id?>-upload-ui" class="hide-if-no-js">
					<div id="<?php echo $option->id?>-drag-drop-area" class="drag-drop-area">
				    	<div class="drag-drop-inside">
				     		<p class="drag-drop-info">
							<?php _e('Drop a file here or'); ?>&nbsp;
				     		<a id="<?php echo $option->id?>-browse-button" href="#" class="upload" style="position: relative; z-index: 0; ">select a file</a>
							</p>	
				   		</div>
					</div>
				</div>
			
			</div>		
			<div id="<?php echo $option->id?>-uploaded" class="tab-pane rhl-uploaded-images-tab-pane">
				UPLOADED
			</div>		
			<textarea id="<?php echo $option->id?>-upload-list" class="<?php echo $queue?>-upload-list" data-upload_queue=".<?php echo $queue?>-upload-list" style="width:96%;<?php echo $this->debug?'':'display:none;'?>" rows='5' class="input-pop-option" name="<?php echo $option->id?>-upload-list"><?php echo $upload_list;?></textarea>
		</div>
	</div>
</div>

<?php
	
		$plupload_init = array(
			'runtimes'            => 'html5,silverlight,flash,html4',
			'browse_button'       => $option->id.'-browse-button',
			'container'           => $option->id.'-upload-ui',
			'drop_element'        => $option->id.'-drag-drop-area',
			'file_data_name'      => 'rh-async-upload',            
			'multiple_queues'     => true,
			'max_file_size'       => $this->wp_max_upload_size().'b',
			'url'                 => admin_url('admin-ajax.php'),
			'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
			'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
			'filters'             => array(array('title' => __('Allowed Files','rhcss'), 'extensions' => '*')),
			'multipart'           => true,
			'urlstream_upload'    => true,
			'multipart_params'    => array(
		    	'_ajax_nonce'	=> wp_create_nonce('rhcss-upload'),
		    	'action'      	=> 'rhcss_ajax_'.$this->id,            // the ajax action name
				'method'		=> 'upload',
				'id'			=> $option->id,
				'queue'			=> $option->queue?$option->queue:$option->id
		  	),
			'id'				=> $option->id,
			'queue'				=> $option->queue?$option->queue:$option->id
		);

?>
<script type="text/javascript">jQuery(document).ready(function($){init_image_uploader(<?php echo json_encode($plupload_init); ?>);});</script>
<?php		
		
	}
	
	function render_background_image($option,$option_i=null,$options=null,$options_i=null){
		$opacity = $option->opacity?true:false;
		$queue = property_exists($option,'queue') ? $option->queue : $option->id ; 
		$upload_list = $this->get_option($queue.'-upload-list','',true);
?>
<div id="<?php echo $option->id?>-holder" class="input-field rhl-image-uploader <?php $this->holder_extra_class($option) ?>">
	<span class="field-label label-for-<?php echo $option->input_type?> label-for-<?php echo $option->id?>"><?php echo $option->label?></span>
	<input id="<?php echo $option->id?>" value='' type="<?php echo $this->debug?'text':'hidden'?>" class="input-field-input rhl_image_uploader input-field-bakground_image <?php $this->input_extra_class($option,$option_i,$options,$options_i)?>" <?php $this->input_extra_properties($option,$option_i,$options,$options_i)?> />
	<div class="rhl-image-uploader-control">
		<div class="dropdown preview-thumbnail">
			<div class="dropdown-content">
				<img style="display:none;" />
				<div style="display:block;" class="dropdown-status"><?php _e('No Image','rhcss')?></div>
				<div style="display:none;" class="dropdown-none"><?php _e('None','rhcss')?></div>
				<div style="display:none;" class="dropdown-gradient">&nbsp;</div>
			</div>
			<div class="dropdown-arrow rhl-image-uploader-helper-trigger"></div>
		</div>
		<?php if($option->btn_clear):?>
		<div class="image-url-button-clear-cont">
			<input type="button" class="btn btn_clear_image_url" data-value_to_set='none' value="<?php _e('None','rhcss')?>" />
			<input type="button" class="btn btn_clear_image_url" data-value_to_set='' value="<?php _e('Clear','rhcss')?>" />
		</div>
		<?php endif; ?>  			
		<div class="rhl-clear"></div>
	</div>
		
	<div class="rhl-clear"></div>
	
   <div id="<?php echo $option->id?>-msg" class="rhl-image-upload-msg"></div>
   
	<div class="rhl-image-upoader-helper helper-closed">
		<ul class="nav nav-tabs">
			<li class=""><a class="rhl-image-gradient" href="#<?php echo $option->id?>-gradient" data-toggle="tab"><?php _e('Gradient','rhl')?></a></li>
			<li class="active"><a class="rhl-upload-new" href="#<?php echo $option->id?>-upload" data-toggle="tab"><?php _e('Upload','rhl')?></a></li>
			<li class="rhl-uploaded-images-tab"><a href="#<?php echo $option->id?>-uploaded" data-toggle="tab"><?php _e('Uploaded','rhl')?></a></li>
		</ul>
		<div class="tab-content">
			<div id="<?php echo $option->id?>-gradient" class="tab-pane">
				
				<input id="<?php echo $option->id?>-start" value='' type="text" class="input-minicolors <?php echo $opacity?'with-opacity':'';?> colorpicker-preview-content colorpicker-input-field sub_colorpicker_gradient" />
			
				<input id="<?php echo $option->id?>-end" value='' type="text" class="input-minicolors <?php echo $opacity?'with-opacity':'';?> colorpicker-preview-content colorpicker-input-field sub_colorpicker_gradient right-colorpicker" />
			
			</div>
			<div id="<?php echo $option->id?>-upload" class="tab-pane active">

				<div id="<?php echo $option->id?>-upload-ui" class="hide-if-no-js">
					<div id="<?php echo $option->id?>-drag-drop-area" class="drag-drop-area">
				    	<div class="drag-drop-inside">
				     		<p class="drag-drop-info">
							<?php _e('Drop a file here or'); ?>&nbsp;
				     		<a id="<?php echo $option->id?>-browse-button" href="#" class="upload" style="position: relative; z-index: 0; ">select a file</a>
							</p>	
				   		</div>
					</div>
				</div>
			
			</div>		
			<div id="<?php echo $option->id?>-uploaded" class="tab-pane rhl-uploaded-images-tab-pane">
				UPLOADED
			</div>		
			<textarea id="<?php echo $option->id?>-upload-list" class="<?php echo $queue?>-upload-list" data-upload_queue=".<?php echo $queue?>-upload-list" style="width:96%;<?php echo $this->debug?'':'display:none;'?>" rows='5' class="input-pop-option" name="<?php echo $option->id?>-upload-list"><?php echo $upload_list;?></textarea>
		</div>
	</div>
</div>

<?php
	
		$plupload_init = array(
			'runtimes'            => 'html5,silverlight,flash,html4',
			'browse_button'       => $option->id.'-browse-button',
			'container'           => $option->id.'-upload-ui',
			'drop_element'        => $option->id.'-drag-drop-area',
			'file_data_name'      => 'rh-async-upload',            
			'multiple_queues'     => true,
			'max_file_size'       => $this->wp_max_upload_size().'b',
			'url'                 => admin_url('admin-ajax.php'),
			'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
			'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
			'filters'             => array(array('title' => __('Allowed Files','rhl'), 'extensions' => '*')),
			'multipart'           => true,
			'urlstream_upload'    => true,
			'multipart_params'    => array(
		    	'_ajax_nonce'	=> wp_create_nonce('rhcss-upload'),
		    	'action'      	=> 'rhcss_ajax_'.$this->id,            // the ajax action name
				'method'		=> 'upload',
				'id'			=> $option->id,
				'queue'			=> $option->queue?$option->queue:$option->id
		  	),
			'id'				=> $option->id,
			'queue'				=> $option->queue?$option->queue:$option->id
		);

?>
<script type="text/javascript">jQuery(document).ready(function($){init_image_uploader(<?php echo json_encode($plupload_init); ?>);});</script>
<?php		
		
	}

	function render_backup_list($option,$option_i=null,$options=null,$options_i=null){
?>
<div id="<?php echo $option->id?>-holder" class="rhl-backup-cont">
	<div id="add_backup_msg"></div>
	<div class="rhl-add-backup-cont">
		<span class="field-label label-for-text"><?php _e('Template description','rhl')?></span>
		<input id="rhl_backup_name" type="text" value="" class="input-medium" /><a id="btn-add-backup" data-loading-text="<?php _e('Adding...','rhl')?>" class="btn btn-primary"><?php _e('Add','rhl')?></a>
	</div>
	<span class="field-label label-for-text"><?php _e('Saved and downloaded','rhl')?></span>
	<p><?php _e('Choose a template and click load.  Current settings will be overwritten.','rhl')?></p>
	<div id="<?php echo $option->id?>" class="saved_settings_list_cont"></div>
	<div class="empty_saved_settings" style="display:none;"><?php _e('No saved settings.','rhl')?></div>
	<a id="btn-restore-backup" class="btn btn-primary" data-loading-text="<?php _e('Loading','rhl')?>"><?php _e('Load','rhl')?></a>
</div>
<?php	
	}	
	
	function render_raw_html($option,$option_i=null,$options=null,$options_i=null){
		echo $option->html;
	}
	
	function render_background_size($option,$option_i=null,$options=null,$options_i=null){
		if( property_exists( $option, 'auto' ) && false===$option->auto ){
			$auto = false;
		}else{
			$auto = true;
		}
		
		$x_label = __('Height','rhl');
		if( property_exists( $option, 'x_label' ) ){
			$x_label = $option->x_label;
		}
		
		$y_label = __('Width','rhl');
		if( property_exists( $option, 'y_label' ) ){
			$y_label = $option->y_label;
		}
		
?>
<div id="<?php echo $option->id?>-holder" class="input-field input-field-background_size <?php $this->holder_extra_class($option) ?>">
	<span class="field-label label-for-<?php echo $option->input_type?> label-for-<?php echo $option->id?>"><?php echo $option->label?></span>
	<input id="<?php echo $option->id?>" value='' type="<?php echo $this->debug?'text':'hidden'?>" class="input-field-input background_size <?php $this->input_extra_class($option,$option_i,$options,$options_i)?>" <?php $this->input_extra_properties($option,$option_i,$options,$options_i)?> />
	
	<div class="row-fluid">
		<div class="span8">
			<select id="<?php echo $option->id?>-options" class="input-wide bgsize_options alternate-bgsize-values" data-target-selector="#<?php echo $option->id?>">
				<?php if( false!==$auto ): ?>
				<option value="auto">auto</option>
				<?php endif; ?>
				<?php foreach($option->other_options as $val => $label):?>
				<option value="<?php echo $val?>" class="alternate-bgsize-value"><?php echo $label?></option>
				<?php endforeach; ?>
			</select>
		</div>	
		<?php /*if($option->btn_clear):*/?>
		<div class="span4 bgsize_clear_btn">
			<input type="button" class="btn btn_clear_generic" data-clear-level='2' value="Clear" />
		</div>
		<?php /*endif;*/ ?>				
	</div>	
	<div class="row-fluid bgsize_value_holder">
		<div class="span6">
			<span class="field-label label-for-bgsize_percent_h"><?php echo $x_label?></span>
			<div class="input-append input-wide">
				<input id="<?php echo $option->id?>" type="text" name="bgsize_h" value="" class="bgsize_value bgsize_h" />
				<span class="add-on bgsize-unit">%</span>
			</div>
		</div>
		<div class="span6">
			<span class="field-label label-for-bgsize_percent_w"><?php echo $y_label ?></span>
			<div class="input-append input-wide">
				<input id="<?php echo $option->id?>" type="text" name="bgsize_w" value="" class="bgsize_value bgsize_w" />
				<span class="add-on bgsize-unit">%</span>
			</div>
		</div>
	</div>
	
	<div class="rhl-clear"></div>	
</div>
<?php
	}
	
	function wp_max_upload_size() {
		//from wp 3.5
		$u_bytes = $this->wp_convert_hr_to_bytes( ini_get( 'upload_max_filesize' ) );
		$p_bytes = $this->wp_convert_hr_to_bytes( ini_get( 'post_max_size' ) );
		$bytes   = apply_filters( 'upload_size_limit', min( $u_bytes, $p_bytes ), $u_bytes, $p_bytes );
		return $bytes;
	}
	
	function wp_convert_hr_to_bytes( $size ) {
		//from wp 3.5
		$size = strtolower($size);
		$bytes = (int) $size;
		if ( strpos($size, 'k') !== false )
			$bytes = intval($size) * 1024;
		elseif ( strpos($size, 'm') !== false )
			$bytes = intval($size) * 1024 * 1024;
		elseif ( strpos($size, 'g') !== false )
			$bytes = intval($size) * 1024 * 1024 * 1024;
		return $bytes;
	}
	
	function holder_extra_class($option,$option_i=null,$options=null,$options_i=null){
		if(@$option->holder_class){
			echo $option->holder_class." ";
		}	
	}
	
	function input_extra_class($option,$option_i=null,$options=null,$options_i=null){
		if(@$option->class){
			echo $option->class." ";
		}
		
		if(@$option->type=='css' && $option->selector){
			echo "default-value-from-css ";
			if($option->real_time){
				echo "real-time ";
			}
		}	
		
		if(@$option->type=='class' && $option->selector){
			echo "default-value-from-class ";
			if($option->real_time){
				echo "real-time-class ";
			}
		}	
	}
	
	function input_extra_properties($option,$option_i=null,$options=null,$options_i=null){
		if( property_exists($option, 'type') && '' != $option->type ){
			echo "data-rhcss_type=\"".$option->type."\" ";
		}else{
			echo "data-rhcss_type=\"undefined\" ";
		}			
		
		if(@$option->type=='class' && $option->selector){
			echo "data-css-selector=\"".$option->selector."\" ";
			echo "data-class_prefix=\"".$option->class_prefix."\" ";
			echo "data-hook=\"".@$option->hook."\" ";
		}	

		if(@$option->type=='css' && $option->selector){
			echo "data-css-selector=\"".$option->selector."\" data-css-property=\"".$option->property."\" ";
		}	
		
		if(@$option->unit){
			echo "data-input-unit=\"".$option->unit."\" ";
		}
		
		if(@$option->blank_value){
			echo "data-blank-value=\"".$option->blank_value."\" ";
		}
		
		if(@$option->children){
			echo "data-children=\"".rawurlencode(json_encode($option->children))."\" ";
		}
		
		if(@$option->derived){
			echo "data-derived=\"".rawurlencode(json_encode($option->derived))."\" ";
		}
		
		if(@$option->fallback_value){
			echo "data-fallback-value=\"".$option->fallback_value."\" ";
		}
		
		if(@$option->media){
			echo "data-media=\"".$option->media."\" ";
		}
		
		if(@$option->editor_helper){
			echo "data-editor_helper=\"".$option->editor_helper."\" ";
		}
		
		if(@$option->cb_get_css_value){
			echo "data-cb_get_css_value=\"".$option->cb_get_css_value."\" ";
		}
		
		if(@$option->queue){
			echo "data-uploader_queue=\"".$option->queue."\" ";
		}
		
		foreach(array('min','max','step') as $attr){
			if(@$option->$attr){
				echo sprintf('%s="%s"',$attr, $option->$attr);
			}
		}
		
	}
}
?>