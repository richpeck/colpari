<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class google_web_fonts_admin {
	var $parent_slug = 'options-general.php';
	var $page_title ='';
	var $skip_google_demo = false;
	function __construct($args=array()){
		//---
		if(defined('GOOGLE_WEB_FONTS_ADMIN'))return;//many plugins can run this , but we only need one instance.
		define('GOOGLE_WEB_FONTS_ADMIN',__FILE__);
		//---
		$defaults = array(
			'sample_text'			=> __('Sample text','rhcss'),
			'parent_slug'			=> 'options-general.php',
			'page_title' 			=> __('Enable Google Web Fonts','rhcss'),
			'menu_title' 			=> __('Google Web Fonts','rhcss'),
			'capability'			=> 'manage_options',
			'menu_slug'				=> 'rhcss-google-fonts',
			'path'						=> plugin_dir_path(__FILE__),
			'url'						=> plugin_dir_url(__FILE__),
			'skip_google_demo'		=> true,
			'option_varname'		=> 'enabled_google_fonts',
			'in_admin'				=> true
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}
		//---	
		add_action('admin_menu', array(&$this,'admin_menu'), 11);
		add_action('init', array(&$this,'init'));
		
		add_action('wp_ajax_rhcss-fonts', array(&$this,'handle_ajax'));
		
		add_action('wp_ajax_rhcss_save_fonts', array(&$this,'handle_save_fonts'));
		
		//frontend hooks:
		add_action('wp_head',array(&$this,'wp_head'));
		add_action('login_head',array(&$this,'wp_head'));
		if($this->in_admin){
			add_action('admin_head',array(&$this,'wp_head'));
		}
		add_filter('rhcss-enabled-fonts',array(&$this,'rhcss_enabled_fonts'),10,1);
	}
	
	function rhcss_enabled_fonts($fonts){
		$enabled_fonts = get_option( $this->option_varname );
		if(is_array($enabled_fonts)&&count($enabled_fonts)>0){
			foreach($enabled_fonts as $f){
				if(isset($f['enabled'])&& $f['enabled']){
					$font = $f['family'];
					if(isset($f['alt'])&&!empty($f['alt'])){
						$font.=', '.$f['alt'];
					}
					$fonts[]=$font;			
				}
			}		
		}

		
		$web_safe_fonts = array(
			"Georgia, serif",
			"Palatino Linotype, Book Antiqua, Palatino, serif",
			"Times New Roman, Times, serif",
			"Arial, Helvetica, sans-serif",
			"Arial Black, Gadget, sans-serif",
			"Comic Sans MS, cursive, sans-serif",
			"Impact, Charcoal, sans-serif",
			"Lucida Sans Unicode, Lucida Grande, sans-serif",
			"Tahoma, Geneva, sans-serif",
			"Trebuchet MS, Helvetica, sans-serif",
			"Verdana, Geneva, sans-serif",
			"Courier New, Courier, monospace",
			"Lucida Console, Monaco, monospace"		
		);
		
		foreach($web_safe_fonts as $font){
			$fonts[]=$font;
		}

		return $fonts;
	}
	
	function wp_head(){
		$enabled_fonts = get_option( $this->option_varname );
		if(is_array($enabled_fonts)&&count($enabled_fonts)>0){
			$families=array();
			$subsets=array();
			foreach($enabled_fonts as $f){
				if(!is_array($f)&&!is_object($f))continue;
				$f = (object)$f;
				$variants = array();
				if(isset($f->set_variants) && is_array($f->set_variants) && count($f->set_variants)>0){
					foreach($f->set_variants as $v){
						$variants[]=urlencode($v);
					}
				}
				$family = urlencode($f->family);
				if(!empty($variants)){
					$family=$family.':'.implode(',',$variants);
				}
				$families[]=$family;
				
				if(isset($f->set_subsets) && is_array($f->set_subsets) && count($f->set_subsets)>0 ){
					foreach($f->set_subsets as $s){
						$s=urlencode($s);
						if(!in_array($s,$subsets)){
							$subsets[]=$s;
						}
					}
				}
			}
			
			if( is_ssl() ){
				$url = 'https://fonts.googleapis.com/css?family='.implode('|',$families);
			}else{
				$url = 'http://fonts.googleapis.com/css?family='.implode('|',$families);
			}
			
			if(!empty($subsets)){
				$url.='&subset='.implode(',',$subsets);
			}

			echo sprintf('<link media="all" type="text/css" rel="stylesheet" href="%s" />',
				$url
			);
		}
	}
	
	function handle_save_fonts(){		
		$enabled_fonts = isset($_REQUEST['enabled_fonts']) && is_array($_REQUEST['enabled_fonts']) && count($_REQUEST['enabled_fonts'])>0 ? $_REQUEST['enabled_fonts'] : array();
		if(!empty($enabled_fonts)){
			update_option( $this->option_varname, $enabled_fonts );
		}
	
		$response = (object)array(
			'R' 	=> 'OK',
			'MSG'	=> '',
			'DATA'	=> ''
		);
		die( json_encode($response) );
	}
	
	function handle_ajax(){
		header('HTTP/1.0 404 Not Found');
		die();
	}
	
	function init(){

	}
	
	function admin_menu(){
		$page_id = add_submenu_page( $this->parent_slug, $this->page_title, $this->menu_title, $this->capability, $this->menu_slug, array(&$this,'output') );
		add_action( 'admin_head-'. $page_id, array(&$this,'head') );
	}
	
	function get_google_fonts_list(){
		$google_fonts = '';
		include 'google_fonts_json.php';
		$google_fonts = trim($google_fonts);
		$google_json = json_decode($google_fonts);	
		if(is_object($google_json) && is_array($google_json->items)){
			//-- add enabled info
			$enabled_fonts = get_option( $this->option_varname );
			if(is_array($enabled_fonts)&&count($enabled_fonts)>0){
				foreach($google_json->items as $i => $item){
					foreach($enabled_fonts as $j => $e_item){
						$e_item = (object)$e_item;
						if($e_item->family==$item->family){
							$google_json->items[$i]=$e_item;
						}
					}
				}			
			}	
			//----
			return $google_json->items;
		}else{
			return array();
		}
	}
	
	function render_list_of_fonts($google_fonts){
		$unencoded_family = array();
		$family = array();
		$first_letters = array();
		ob_start();
		
		
?>
<div id="rhcss-fonts-holder" class="rhcss-isotope-holder">
		<?php foreach($google_fonts as $i => $font):
			//if($i>=100)break;
			$letter = substr( strtolower($font->family), 0, 1 );
			//if($letter!='c')break;
			$family[] = rawurlencode($font->family);
			$unencoded_family[]=$font->family;
			$letter_filter = 'letter-'.$letter;
			$first_letters[$letter] = $letter_filter;
			
			
			
			continue;
		?>
	<div class="rhcss-font rhcss-isotope-item <?php echo $letter_filter . ' family-' . str_replace(' ','-',strtolower($font->family))?>">

		<div class="btn-group font-control" data-toggle="buttons-radio">
		  <button type="button" class="btn btn-danger activate-font"><?php _e('On','rhcss')?></button>
		  <button type="button" data-toggle="button" class="btn btn-danger active deactivate-font"><?php _e('Off','rhcss')?></button>
		</div>		
		
		<p style="font-size:1.2em;font-family:<?php echo $font->family ?>, sans-serif, serif"><?php echo $this->sample_text?></p>&nbsp;&nbsp;
		
		<h4 class="font-title"><?php echo $font->family ?></h4>
		
		<div class="row-fluid">
			<?php if(!empty($font->variants)):?>
			<div class="span6 font-variant">
				<label for="variants"><?php _e('Variants','rhcss')?></label>
				<?php foreach($font->variants as $variant):?>
				<input type="checkbox" value="1" />&nbsp;<?php echo $variant?><br />
				<?php endforeach;?>
			</div>
			<?php endif;?>
			
			<?php if(!empty($font->subsets)):?>
			<div class="span6 font-subset">
				<label for="subsets"><?php _e('Subsets','rhcss')?></label>
				<?php foreach($font->subsets as $subset):?>
				<input type="checkbox" value="1" />&nbsp;<?php echo $subset?><br />
				<?php endforeach;?>
			</div>
			<?php endif;?>
		</div>				
	</div>
		<?php endforeach;?>
</div>

<?php		
		$content = ob_get_contents();
		ob_end_clean();
	
?>	
<div class="row-fluid">
	<div class="span6">
		<div class="input-append control-search">
		  <input class="" id="rhcss-search-input" type="text" data-provide="typeahead">
		  <button class="btn" type="button" id="rhcss-search-btn"><?php _e('Search','rhcss') ?></button>
		</div>	
	</div>
	<div class="span6">
		<div class="control-google-preview">
			<label><?php _e('Google Web Fonts preview','rhcss')?></label>
			<div class="btn-group preview-control" data-toggle="buttons-radio">
			  <button type="button" class="btn enable-preview"><?php _e('On','rhcss')?></button>
			  <button type="button" data-toggle="button" class="btn btn-danger active disable-preview"><?php _e('Off','rhcss')?></button>
			</div>	
			<input type="hidden" id="enable-google-preview" value="0" />
		</div>
	</div>
</div>
<script type="text/javascript">
jQuery(document).ready(function($){
	try{
	var families=<?php echo json_encode($unencoded_family)?>;
	
	$('#rhcss-search-input').typeahead({
		source: families,
		updater: function(item){
			if(item!=''){
				load_items( '#rhcss-fonts-holder', get_subset_by_family(google_fonts,item) );
			}
			return item;
		}
	});
	
	}catch(e){}
});
</script>
<div class="row-fluid"> 
	<div class="span12">
		<div class="pagination letter-filters">
			<ul>
				<li><a class="status-filter" href="javascript:void(0);" data-filter=".font-enabled"><?php _e('Enabled','rhcss')?></a></li>	
				<?php foreach($first_letters as $letter => $letter_filter): ?>
				<li><a class="link-letter-filter" href="javascript:void(0);" data-filter="<?php echo $letter?>"><?php echo $letter?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>	
	</div>
</div>
<?php	
		echo $content;
		
		if(!$this->skip_google_demo){
			$sources = array();
			$j=0;
			$k=0;
			$sources[$k]=array();
			foreach($family as $f){
				if($j++>30){
					$j=0;
					$k++;
					$sources[$k]=array();
				}
				$sources[$k][]=$f;
			}
			
			foreach($sources as $families){
				if( is_ssl() ){
					$src = 'https://fonts.googleapis.com/css?family=' . implode('|',$families);
				}else{
					$src = 'http://fonts.googleapis.com/css?family=' . implode('|',$families);
				}
				$src.= '&text=' . rawurlencode( $this->sample_text );
				echo sprintf('<link rel="stylesheet" type="text/css" href="%s">',$src);
				//echo $src."<br />";
			}		
		}
		
	}
	
	function head(){
		rh_register_script( 'bootstrap', $this->url.'bootstrap/js/bootstrap.min.js', array(),'2.3.1');
		rh_register_script( 'jquery-isotope', $this->url.'js/jquery.isotope.min.js', array(),'1.5.14');
		//rh_register_script( 'jquery-infinite-scroll', $this->url.'js/jquery.infinitescroll.min.js', array(),'2.0b2.110713');
		
		wp_register_style( 'rhcss-bootstrap', $this->url.'bootstrap/css/namespaced.bootstrap.min.css', array(), '2.2.2' );
		wp_register_style( 'rhcss-gfonts-admin', $this->url.'css/google_web_fonts_admin.css', array(), '1.0.0');
			
		wp_print_styles('rhcss-bootstrap');
		wp_print_styles('rhcss-gfonts-admin');
	
		wp_print_scripts('bootstrap');
		wp_print_scripts('jquery-isotope');
		//wp_print_scripts('jquery-infinite-scroll');
?>
<style>
<?php /*$this->animation_styles();*/?>
</style>
<script type="text/javascript">

var google_fonts = <?php echo json_encode($this->get_google_fonts_list())?>;
var demo_text = '<?php echo $this->sample_text?>';

function get_subset_by_family(google_fonts,family){
	var subset = [];
	for(a=0;a<google_fonts.length;a++){
		if( family.toLowerCase() == google_fonts[a].family.toLowerCase() ){
			subset[subset.length]=google_fonts[a];
			continue;
		}
		
		if( google_fonts[a].family.toLowerCase().indexOf( family.toLowerCase() )!=-1 ){
			subset[subset.length]=google_fonts[a];
			continue;
		}
	}
	return subset;
}

function get_subset_by_letter(google_fonts,letter){
	var subset = [];
	for(a=0;a<google_fonts.length;a++){
		if( letter == google_fonts[a].family.substring(0,1).toLowerCase() ){
			subset[subset.length]=google_fonts[a];
		}
	}
	return subset;
}

function get_subset_enabled(google_fonts){
	var subset = [];
	for(a=0;a<google_fonts.length;a++){
		if( 'undefined'!=typeof(google_fonts[a].enabled) && google_fonts[a].enabled ){
			subset[subset.length]=google_fonts[a];
		}
	}
	return subset;
}

function load_items(selector,sub_set){
	jQuery(document).ready(function($){
		var web_safe_fonts = [
			"Georgia, serif",
			"Palatino Linotype, Book Antiqua, Palatino, serif",
			"Times New Roman, Times, serif",
			"Arial, Helvetica, sans-serif",
			"Arial Black, Gadget, sans-serif",
			"Comic Sans MS, cursive, sans-serif",
			"Impact, Charcoal, sans-serif",
			"Lucida Sans Unicode, Lucida Grande, sans-serif",
			"Tahoma, Geneva, sans-serif",
			"Trebuchet MS, Helvetica, sans-serif",
			"Verdana, Geneva, sans-serif",
			"Courier New, Courier, monospace",
			"Lucida Console, Monaco, monospace"
		];
		//
		$('.rhcss-isotope-item').each(function(i,item){
			$(selector).isotope('remove', $(this) );
		});
	
		var families = [];
		$.each(sub_set, function(i,data){
			//if(i>30)return;
			//console.log( data );
			//rhcss-isotope-item
			
			families[families.length]=escape(data.family);
			
			if( 'undefined'!=typeof(data.enabled) && data.enabled ){
				var enable_control = $('<div class="btn-group font-control" data-toggle="buttons-radio"><button type="button" class="btn btn-success active activate-font"><?php _e('On','rhcss')?></button><button type="button" data-toggle="button" class="btn deactivate-font"><?php _e('Off','rhcss')?></button></div>');
				var status = 'font-enabled';
			}else{
				var enable_control = $('<div class="btn-group font-control" data-toggle="buttons-radio"><button type="button" class="btn activate-font"><?php _e('On','rhcss')?></button><button type="button" data-toggle="button" class="btn btn-danger active deactivate-font"><?php _e('Off','rhcss')?></button></div>');
				var status = 'font-disabled';
			}
			
			var sample_text = $('<p class="google-font-preview" style="font-size:1.5em;font-family:'+data.family+', sans-serif, serif">'+demo_text+'</p>');
			
			var letter = 'letter-' + data.family.substring(0,1).toLowerCase();
			var family = 'family-' + data.family.toLowerCase().replace(' ','-');
			var new_items = $('<div></div>')
				.data('family', data.family )
				.addClass('rhcss-isotope-item')
				.addClass(status)
				.addClass(letter)
				.addClass(family)
				.append(enable_control)
				.append(sample_text)
				.append('<h4 class="font-title">' + data.family + '</h4>')
				.append('<div id="extra-sets" class="row-fluid"></div>')
			;
			
			if( data.variants.length > 0 ){
				var variant_cont = $('<div class="span6 font-variant"></div>');
				variant_cont.append('<label><?php _e('Variants','rhcss')?></label>');
			
				$.each(data.variants,function(i,v){
					var checked = '';
					if( 'undefined'!=typeof(data.set_variants) ){
						for(a=0;a<data.set_variants.length;a++){
							if( v==data.set_variants[a] ){
								checked = 'checked="checked"';
								break;
							}
						}					
					}
					
					variant_cont.append('<input type="checkbox" '+checked+' class="input-font-variant" value="'+v+'" />&nbsp;'+v+'<br />');		
				});
				new_items.find('#extra-sets')
					.append(variant_cont);
			}
			
			if( data.subsets.length > 0 ){
				var subset_cont = $('<div class="span6 font-subset"></div>');
				subset_cont.append('<label><?php _e('Subsets','rhcss')?></label>');

				$.each(data.subsets,function(i,v){
					var checked = '';
					if( 'undefined'!=typeof(data.set_subsets) ){
						for(a=0;a<data.set_subsets.length;a++){
							if( v==data.set_subsets[a] ){
								checked = 'checked="checked"';
								break;
							}
						}					
					}				
					subset_cont.append('<input type="checkbox" '+checked+' class="input-font-subset" value="'+v+'" />&nbsp;'+v+'<br />');		
				});				
				new_items.find('#extra-sets')
					.append( subset_cont );
			}
			
			var web_safe_options = $('<select class="rhcss-alt-font"></select>');
			var selected = 'undefined'!=typeof( data.alt ) && data.alt!=''?data.alt:'';
			$.each(web_safe_fonts,function(i,f){
				web_safe_options.append( $('<option '+ (selected==f?'selected="selected"':'') +' value="' + f + '">' + f + '</option>') );
			});			

			var control_fonts = $('<div class="control-alt-font"></div>');
			control_fonts.append('<label>Alternate Web Safe font:</label>');
			control_fonts.append(web_safe_options);
			new_items.append(control_fonts);
			
			$(selector).isotope('insert', new_items);
		});
		load_google_fonts_preview(families,demo_text);
		init_loaded_items();
	});
}

function save_google_web_fonts(){
	var enabled_fonts = [];
	for(a=0;a<google_fonts.length;a++){
		if( google_fonts[a].enabled ){
			enabled_fonts.push( google_fonts[a] );
		}
	}
	jQuery(document).ready(function($){
		var args = {
			action: 'rhcss_save_fonts',
			enabled_fonts: enabled_fonts
		};
		
		$.post( '<?php echo admin_url('/admin-ajax.php')?>', args, function(data){
			if(data.R=='OK'){
				//console.log(data.DATA);
			}else if(data.R=='ERR'){
				alert(data.MSG);
			}else{
				alert('Error saving, reload page and try again.');
			}
		}, 'json');
	});
	//console.log('save font state', enabled_fonts);
}

var last_families = [];
function load_google_fonts_preview(_families,demo_text){
	if( '1'!=jQuery('#enable-google-preview').val() ){
		if(_families.length>0)
			last_families = _families;
		return true;
	}
	
	while( jQuery('link.google-web-font').length>10 ){
		jQuery('link.google-web-font').first().remove();
	}
	
	while( families = _families.splice(0, 30) ){
		if(families.length==0)break;
		var url = 'https://fonts.googleapis.com/css?family=' + families.join('|') + '&text=' + escape(demo_text);	
		
		jQuery('<link />', {
			rel: 'stylesheet',
			type: 'text/css',
			href: url,
			class: 'google-web-font'
		}).appendTo('HEAD');			
	}
	return true;
}

jQuery(document).ready(function($){
	$('#enable-google-preview').val('0');
	
	$('#rhcss-fonts-holder').isotope({
		itemSelector : '.rhcss-isotope-item',
  		layoutMode : 'fitRows'
		/*,filter : '.letter-a'*/
	});
	
	load_items( '#rhcss-fonts-holder', get_subset_by_letter(google_fonts,'a') );
	
	$('.status-filter').on('click',function(e){
		load_items( '#rhcss-fonts-holder', get_subset_enabled( google_fonts ) );
		return true;
	});
	
	$('.letter-filters .link-letter-filter').on('click',function(e){
		var letter = $(this).attr('data-filter');
		load_items( '#rhcss-fonts-holder', get_subset_by_letter(google_fonts,letter) );
		return true;
	});
	
	$('.enable-preview').on('click',function(e){
		$(this).parent().find('.btn.enable-preview')	
			.addClass('btn-success')
		;	
		$(this).parent().find('.btn.disable-preview')
			.removeClass('btn-danger')
		;	
		$('#enable-google-preview').val('1');
		load_google_fonts_preview(last_families,demo_text);
	});
	
	$('.disable-preview').on('click',function(e){
		$(this).parent().find('.btn.enable-preview')	
			.removeClass('btn-success')
		;	
		$(this).parent().find('.btn.disable-preview')
			.addClass('btn-danger')
		;	
		$('#enable-google-preview').val('0');	
		jQuery('link.google-web-font').remove();
	});
	
	$('#rhcss-search-btn').on('click',function(e){
		var val = $('#rhcss-search-input').val();
		if(val!=''){
			load_items( '#rhcss-fonts-holder', get_subset_by_family(google_fonts,val) );
		}
	});
});

function init_loaded_items(){
	jQuery(document).ready(function($){
		$('.activate-font').on('click',function(e){
			$(this).parent().find('.btn.activate-font')
				.addClass('btn-success')
			;	
			$(this).parent().find('.btn.deactivate-font')
				.removeClass('btn-danger')
			;
			
			$(this).parents('.rhcss-isotope-item')
				.removeClass('font-disabled')
				.addClass('font-enabled')
			;
			
			var family = $(this).parents('.rhcss-isotope-item').data('family');
			for(a=0;a<google_fonts.length;a++){
				if( family==google_fonts[a].family ){
					google_fonts[a].enabled = true;				
					google_fonts[a].set_variants = [];
					$(this).parents('.rhcss-isotope-item').find('.input-font-variant:checked').each(function(i,inp){
						google_fonts[a].set_variants.push( $(inp).val() );
					});
					
					google_fonts[a].set_subsets = [];
					$(this).parents('.rhcss-isotope-item').find('.input-font-subset:checked').each(function(i,inp){
						google_fonts[a].set_subsets.push( $(inp).val() );
					});
					
					google_fonts[a].alt = $(this).parents('.rhcss-isotope-item').find('.rhcss-alt-font').val();
					google_fonts[a].alt = google_fonts[a].alt==''?'serif':google_fonts[a].alt;
				}
			}
			
			save_google_web_fonts();
		});
		
		$('.deactivate-font').on('click',function(e){
			$(this).parent().find('.btn.activate-font')
				.removeClass('btn-success')
			;	
			$(this).parent().find('.btn.deactivate-font')
				.addClass('btn-danger')
			;
			
			$(this).parents('.rhcss-isotope-item')
				.removeClass('font-enabled')
				.addClass('font-disabled')
			;
	
			var family = $(this).parents('.rhcss-isotope-item').data('family');
			for(a=0;a<google_fonts.length;a++){
				if( family==google_fonts[a].family ){
					google_fonts[a].enabled = false;
					google_fonts[a].set_variants = [];
					google_fonts[a].set_subsets = [];
				}
			}		
			
			save_google_web_fonts();
		});	
		$('.input-font-subset,.input-font-variant').on('click',function(e){
			var container = $(this).parents('.rhcss-isotope-item');
			if( container.is('.font-enabled') ){
				container.find('.activate-font').trigger('click');
			}
		});
			
		$('.rhcss-alt-font').on('change',function(e){
			var container = $(this).parents('.rhcss-isotope-item');
			if( container.is('.font-enabled') ){
				container.find('.activate-font').trigger('click');
			}	
		});
	});
}
</script>
<?php
	}
	
	function animation_styles(){
?>
.isotope,
.isotope .isotope-item {
  /* change duration value to whatever you like */
  -webkit-transition-duration: 0.8s;
     -moz-transition-duration: 0.8s;
      -ms-transition-duration: 0.8s;
       -o-transition-duration: 0.8s;
          transition-duration: 0.8s;
}

.isotope {
  -webkit-transition-property: height, width;
     -moz-transition-property: height, width;
      -ms-transition-property: height, width;
       -o-transition-property: height, width;
          transition-property: height, width;
}

.isotope .isotope-item {
  -webkit-transition-property: -webkit-transform, opacity;
     -moz-transition-property:    -moz-transform, opacity;
      -ms-transition-property:     -ms-transform, opacity;
       -o-transition-property:      -o-transform, opacity;
          transition-property:         transform, opacity;
}
<?php	
	}
		
	function output(){
		$google_fonts = $this->get_google_fonts_list();
		
		//http://fonts.googleapis.com/css?family=Inconsolata&text=Hello
?>
<div class="wrap">
	<div id="icon-rh-google-fonts" class="icon32"><br></div>
	<h2><?php _e('Enable Google Web Fonts','rhcss')?></h2>
	<div class="rhcss rh-css-edit-form">
		<?php $this->render_list_of_fonts($google_fonts)?>
	</div>
</div>
<?php
	}
}
?>