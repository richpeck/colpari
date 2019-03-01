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
	function __construct($args){
		$defaults = array(
			'sample_text'			=> __('Sample text','rhcss'),
			'parent_slug'			=> 'options-general.php',
			'page_title' 			=> __('Google fonts','rhcss'),
			'menu_title' 			=> __('Google fonts','rhcss'),
			'capability'			=> 'manage_options',
			'menu_slug'				=> 'rhcss-google-fonts',
			'path'						=> plugin_dir_path(__FILE__),
			'url'						=> plugin_dir_url(__FILE__),
			'skip_google_demo'		=> false			
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}
		//---	
		add_action('admin_menu', array(&$this,'admin_menu'));
		add_action('init', array(&$this,'init'));
		
		add_action('wp_ajax_rhcss-fonts', array(&$this,'handle_ajax'));
	}
	
	function handle_ajax(){
		header('HTTP/1.0 404 Not Found');
		die();
	}
	
	function init(){
		rh_register_script( 'bootstrap', $this->url.'bootstrap/js/bootstrap.min.js', array(),'2.2.2');
		rh_register_script( 'jquery-isotope', $this->url.'js/jquery.isotope.min.js', array(),'1.5.14');
		rh_register_script( 'jquery-infinite-scroll', $this->url.'js/jquery.infinitescroll.min.js', array(),'2.0b2.110713');
		
		wp_register_style( 'rhcss-bootstrap', $this->url.'bootstrap/css/namespaced.bootstrap.min.css', array(), '2.2.2' );
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

<div class="input-append">
  <input class="span2" id="rhcss-search-input" type="text" data-provide="typeahead">
  <button class="btn" type="button" id="rhcss-search-btn">Search</button>
</div>
<script type="text/javascript">
jQuery(document).ready(function($){
	try{
	var families=<?php echo json_encode($unencoded_family)?>;
	
	$('#rhcss-search-input').typeahead({
		source: families,
		updater: function(item){
			var sel = item.toLowerCase();
			sel = '.family-' + sel.replace(' ','-');
			$('#rhcss-fonts-holder').isotope({ filter: sel });			
			return item;
		}
	});
	
	}catch(e){}
});
</script>

<div class="pagination letter-filters">
	<ul>
		<li><a class="link-letter-filter" href="javascript:void(0);" data-filter=".font-enabled"><?php _e('Enabled','rhcss')?></a></li>	
		<?php foreach($first_letters as $letter => $letter_filter): ?>
		<li><a class="link-letter-filter" href="javascript:void(0);" data-filter=".<?php echo $letter_filter?>"><?php echo $letter?></a></li>
		<?php endforeach; ?>
	</ul>
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
				$src = 'http://fonts.googleapis.com/css?family=' . implode('|',$families);
				$src.= '&text=' . rawurlencode( $this->sample_text );
				echo sprintf('<link rel="stylesheet" type="text/css" href="%s">',$src);
				//echo $src."<br />";
			}		
		}

/*								
echo "<pre>";
echo $src;
print_r($font);
echo "</pre>";
*/
		
	}
	
	function head(){
		wp_print_styles('rhcss-bootstrap');
	
		wp_print_scripts('bootstrap');
		wp_print_scripts('jquery-isotope');
		wp_print_scripts('jquery-infinite-scroll');
?>
<style>
.letter-filters {
margin-bottom:10px;
}
.rhcss-isotope-item {
	background: white;
	padding: 15px;
	margin-right: 15px;
	margin-bottom: 15px;
	border: 1px solid #E3E3E3;
	-webkit-border-radius: 3px;
	 -khtml-border-radius: 11px;
	   -moz-border-radius: 3px;
		    border-radius: 3px;	   
	min-width:200px;
	min-height: 100px;
	max-width:250px;
	-webkit-box-shadow: inset 0 1px 0 white, inset 0 0 20px rgba(0, 0, 0, 0.05), 0 1px 2px rgba( 0, 0, 0, 0.1 );
	   -moz-box-shadow: inset 0 1px 0 #fff, inset 0 0 20px rgba(0,0,0,0.05), 0 1px 2px rgba( 0,0,0,0.1 );
	        box-shadow: inset 0 1px 0 white, inset 0 0 20px rgba(0, 0, 0, 0.05), 0 1px 2px rgba( 0, 0, 0, 0.1 );	
}

.font-control {
float:right;
}

.font-title {
clear: both;
}

.rhcss input[type="text"] {
height:30px;
}
<?php /*$this->animation_styles();*/?>
</style>
<script type="text/javascript">
jQuery(document).ready(function($){
	$('#rhcss-fonts-holder').isotope({
		itemSelector : '.rhcss-isotope-item',
  		layoutMode : 'fitRows',
		filter : '.letter-a'
	});
	
	$('.letter-filters .link-letter-filter').live('click',function(e){
		var selector = $(this).attr('data-filter');
		$('#rhcss-fonts-holder').isotope({ filter: selector });
		return true;
	});
		
	$('.activate-font').click(function(e){
		$(this).parent().find('.btn')
			.removeClass('btn-danger')
			.addClass('btn-success')
		;
		
		$(this).parents('.rhcss-isotope-item')
			.removeClass('font-disabled')
			.addClass('font-enabled')
		;
	});
	
	$('.deactivate-font').click(function(e){
		$(this).parent().find('.btn')
			.removeClass('btn-success')
			.addClass('btn-danger')
		;
		
		$(this).parents('.rhcss-isotope-item')
			.removeClass('font-enabled')
			.addClass('font-disabled')
		;
	});
	
	$('#rhcss-search-btn').live('click',function(e){
		var val = $('#rhcss-search-input').val();
		if(val!=''){
			var sel = val.toLowerCase();
			sel = '.family-' + sel.replace(' ','-');
			$('#rhcss-fonts-holder').isotope({ filter: sel });
		}
	});
});
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
<div class="wrap rhcss">
	<div id="icon-rh-google-fonts" class="icon32"><br></div>
	<h2><?php _e('Enable google fonts','rhcss')?></h2>
	<?php $this->render_list_of_fonts($google_fonts)?>	
</div>
<?php
	}
}
?>