<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class righthere_css_ajax {
	var $id = 'notdefined';
	var $capability = 'manage_options';
	var $options_varname = 'rhcss_demo';
	var $valid_methods;
	var $file_queue_options_name = 'rhcss_file_queue';
	var $upload_limit_per_index = 10;
	var $cb_get_option = false;
	var $resources_path = 'undefined';
	function __construct($args=array()){
		if(!empty($args)){
			foreach($args as $p => $v){
				$this->$p = $v;
			}		
		}
		//--
		$this->valid_methods = array(
			'save',
			'remove',
			'list',
			'backup',
			'restore',
			'upload'
		);
		
		add_action('wp_ajax_rhcss_ajax_'.$this->id, array(&$this,'handle_ajax'));
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
	
	function send_response($ret){		
		die(json_encode($ret));	
	}
	
	function send_error($msg){		
		$this->send_response(array(
			'R'=>'ERR',
			'MSG'=> $msg
		));
	}	
	
	function handle_admin_access(){
		if(!current_user_can( $this->capability )){
			$this->send_error( __('No access.  Open a diferent browser tab and login, then come back to this screen and try again.','rhcss') );
		}
	}	

	function handle_ajax(){		
		$this->handle_admin_access();
		
		$method = isset($_REQUEST['method'])?$_REQUEST['method']:'';
		if(!in_array($method,$this->valid_methods)){
			$this->send_error( __('Unknown ajax method', 'rhcss') );
		}else{
			$method = '_'.$method;
			$this->$method();
		}
	}	
	
	function _save(){
		$data = isset($_REQUEST['data'])&&is_array($_REQUEST['data'])?$_REQUEST['data']:array();
		$default = isset($_REQUEST['default_values'])&&is_array($_REQUEST['default_values'])?$_REQUEST['default_values']:array();
		foreach(array('section','scope') as $field){
			$$field = isset( $_REQUEST[$field] ) && ''!=trim( $_REQUEST[$field]) ? trim($_REQUEST[$field]) : '' ;
		}
//file_put_contents(ABSPATH.'css.log',print_r($_REQUEST['data'],true));
		$output = '';
		if(!empty($data)){
			foreach($data as $fieldset){

				$selector = @stripslashes($fieldset['sel']); 
				$items = array();
				if( !isset($fieldset['css']) ){
					//error_log( print_r($fieldset,true)."\n", 3, ABSPATH.'css.log' );				
				}
				if( isset($fieldset['css']) && is_array($fieldset['css'])&&count($fieldset['css'])>0){
					foreach($fieldset['css'] as $values){
						if(!empty($values)){
							foreach($values as $property => $value){
								if(trim($value)=='')continue;
								if( $property=='content' ){
									$value = "'".stripslashes($value)."'";
								}
								$items[]=sprintf("\t%s:%s;\n",$property,$value);						
							}
						}
					}				
				}

				if(!empty($items)){
					$media = isset($fieldset['media']) ? trim($fieldset['media']) : false;
					if($media){
						$output.=sprintf("@media %s {\n\t%s {\n\t\t%s\n\t}\n}\n",$media,$selector,implode('',$items));
					}else{
						$output.=sprintf("%s {\n%s}\n",$selector,implode('',$items));
					}
				}
			}
		}

		$data_class = isset($_REQUEST['data_class'])&&is_array($_REQUEST['data_class'])?$_REQUEST['data_class']:array();
		if( count($data_class) > 0 ){
			foreach($data_class as $i => $d){
				$data_class[$i]['scope']	= $scope;
			}
		}
//-----
		$options = get_option($this->options_varname);
		$options = is_array($options)?$options:array();	
	
		$options['css_output_sections'][$section][$scope] = $output;
		if(isset($options['css_output']))unset($options['css_output']);
		
		$options['css_class'][$section] =$data_class;
		
		$pop = isset($_REQUEST['pop'])&&is_array($_REQUEST['pop'])?$_REQUEST['pop']:array();
		if(is_array($pop)&&count($pop)>0){
			foreach($pop as $p){
				if( isset($p['name']) ){
					$options[ $p['name'] ] = $p['value'];
				}
			}
		}		
		if($this->id=='ace' && isset($options['css_output_sections']['admin_menu'])){
			unset($options['css_output_sections']['admin_menu']);
		}	
		if($this->id=='ace' && isset($options['css_output_sections']['menu'])){
			unset($options['css_output_sections']['menu']);
		}
		update_option($this->options_varname,$options);

		do_action('saved-rh-css',$this->options_varname,$options);
		
		$ret = array(
			'R'=>'OK',
			'MSG'=>__('Custom settings saved','rhcss')
		);	
		$this->send_response($ret);
	}	

	function _remove(){
		foreach(array('section','scope') as $var ){
			$$var = isset($_REQUEST[$var]) && ''!=trim($_REQUEST[$var]) ? $_REQUEST[$var] : false ;
		}
		$options = get_option($this->options_varname);
		$options = is_array($options)?$options:array();	
		if($section && $scope){
			if( isset($options['css_output_sections'][$section][$scope]) ) unset($options['css_output_sections'][$section][$scope]);
		}else{
			$options['css_output_sections']	= array();
		}
		
		update_option($this->options_varname,$options);		
		
		do_action('removed-rh-css',$this->options_varname,$options);
		
		$ret = array(
			'R'=>'OK',
			'MSG'=>__('Customization removed','rhcss')
		);	
		$this->send_response($ret);		
	}	
	
	function _backup(){
		if(!isset($_REQUEST['label']) || ''==trim($_REQUEST['label'])){
			$ret = array(
				'R'=>'ERR',
				'MSG'=>__('Please specify a short name for the backup.','rhcss')
			);	
			$this->send_response($ret);		
		}
		
		$options = get_option($this->options_varname);
		$options = is_array($options)?$options:array();	

		$css_options = array();
		$css_options['css_output_sections'] 	=	$options['css_output_sections'];

		//-- append uploaded-list--------
		foreach($options as $field => $value){
			if(false!==strpos($field,'-upload-list')){
				$css_options[$field] = $value;
			}
		}
		//-------------------------------			
		$saved_options = get_option($this->options_varname.'_saved');
		$saved_options = is_array($saved_options)?$saved_options:array();	
		
		$label = $_REQUEST['label'];
		
		$done=false;
		if(count($saved_options)>0){
			foreach($saved_options as $i => $saved_option){
				if($label == $saved_option->name){
					$done=true;
					$saved_options[$i]=(object)array(
						'id'		=> md5($_REQUEST['label']),
						'name' 		=> $_REQUEST['label'],
						'groups' 	=> array('css'),
						'date' 		=> date('Y-m-d H:i:s'),
						'options' 	=> $css_options
					);		
					update_option($this->options_varname.'_saved',$saved_options);		
					
					$ret = array(
						'R'=>'OK',
						'MSG'=>__('Current settings saved, item replaced.','rhcss')
					);	
					$this->send_response($ret);								
					break;
				}	
			}
		}
		
		if(!$done){
			$saved_options[]=(object)array(
				'id'		=> md5($_REQUEST['label']),
				'name' 		=> $_REQUEST['label'],
				'groups' 	=> array(),
				'date' 		=> date('Y-m-d H:i:s'),
				'options' 	=> $css_options
			);		
			update_option($this->options_varname.'_saved',$saved_options);	
		}
		
		$ret = array(
			'R'=>'OK',
			'MSG'=>__('Current settings saved.','rhcss')
		);	
		$this->send_response($ret);		
	}	

	function _restore(){
		$label = $_REQUEST['label'];
		
		$saved_options = get_option($this->options_varname.'_saved');
		$saved_options = is_array($saved_options)?$saved_options:array();	
		if( count($saved_options)>0 ){
			foreach($saved_options as $i => $saved_option){
				if($saved_option->name==$label){
					$options = get_option($this->options_varname);
					$options = is_array($options)?$options:array();
					foreach($saved_option->options as $field => $value){
						if($field=='css_output_sections' && is_array($value)){							
							foreach($value as $section => $scopes){
								$options[$field][$section]=$scopes;
							}
						}else{
							$options[$field]=$value;
						}
					}
					//---
					if( isset($saved_option->options['enabled_google_fonts']) && is_array($saved_option->options['enabled_google_fonts']) && count($saved_option->options['enabled_google_fonts'])>0){
						$enabled_google_fonts = get_option('enabled_google_fonts');
						$enabled_google_fonts = is_array($enabled_google_fonts)?$enabled_google_fonts:array();
												
						foreach($saved_option->options['enabled_google_fonts'] as $arr){
							if(!is_array($arr))continue;
							//--check duplicate
							$duplicate = false;
							foreach($enabled_google_fonts as $brr){
								if(!is_array($brr))continue;
								if($brr['family']==$arr['family']){
									$duplicate = true;
								}
							}
							
							if(!$duplicate){
								$enabled_google_fonts[]=$arr;
							}
						}
						update_option('enabled_google_fonts',$enabled_google_fonts);
						unset($options['enabled_google_fonts']);
					}
					//---
					update_option($this->options_varname,$options);
					
					do_action('restored-rh-css',$this->options_varname,$options);
					
					$ret = array(
						'R'=>'OK',
						'MSG'=>__('Settings restored, reloading','rhcss')
					);	
					$this->send_response($ret);				
				}
			}
		}
				
		$ret = array(
			'R'=>'ERR',
			'MSG'=>__('Setting not found','rhcss')
		);	
		$this->send_response($ret);		
	}
	
	function _list(){
		$skip_groups = isset($_REQUEST['skip_groups'])&&is_array($_REQUEST['skip_groups'])?$_REQUEST['skip_groups']:array();
		
		$varname = $this->options_varname.'_saved';
		$saved_options = get_option($varname);
		$saved_options = is_array($saved_options)?$saved_options:array();
//$saved_options=array();		
		//todo skip email templates
		if(count($saved_options)>0){
			$new_saved_options = array();
			foreach($saved_options as $i => $s){
				if(!is_array($s->options) || count($s->options)==0 ){
					continue;
				}
				if($s->groups && is_array($s->groups) && !empty($skip_groups) && array_intersect( $skip_groups, $s->groups) /*&& in_array('rhl_email_template_list',$s->groups)*/ ){
					continue;
				}
				$new_saved_options[]=$s;
			}
			$saved_options = $new_saved_options;
		}
		
		$ret = array(
			'R'=>'OK',
			'MSG'=>__('Loaded','rhcss'),
			'DATA'=> array_reverse($saved_options)
		);	
		$this->send_response($ret);		
	}	

	function _upload(){
		//--
		$id = isset($_REQUEST['id'])?$_REQUEST['id']:'';
		check_ajax_referer('rhcss-upload');
		$status = wp_handle_upload($_FILES['rh-async-upload'], array('test_form'=>true, 'action' => 'rhcss_ajax_'.$this->id ));
		if(isset($status['url'])){
			//-- handle queue
			$saved_options = get_option($this->options_varname);
			$saved_options = is_array($saved_options)?$saved_options:array();			
			$queue = isset($_REQUEST['queue'])?$_REQUEST['queue']:$id;
			$field = $queue.'-upload-list';
			$saved_options[$field] = isset($saved_options[$field])&&is_string($saved_options[$field])?$saved_options[$field]:'';
			$arr = explode("\n",$saved_options[$field]);
			$arr = is_array($arr)?$arr:array();
			array_unshift($arr,$status['url']);
			$arr = array_slice($arr, 0, $this->upload_limit_per_index );
			$saved_options[$field] = implode("\n",$arr);
			update_option($this->options_varname,$saved_options);
			//--			
			$ret = array(
				'R'		=> 'OK',
				'MSG'	=> '',
				'ID'	=> $id,
				'URL'	=> $status['url'],
				'FILES'	=> @$uploaded_files[$index],
				'UPLOADED'	=> $saved_options[$field]
			);			
		}else if(isset($status['error'])){
			$ret = array(
				'R'		=> 'ERR',
				'ID'	=> $id,
				'MSG'	=> $status['error']
			);			
		}else{
			$ret = array(
				'R'		=> 'ERR',
				'ID'	=> $id,
				'MSG'	=> __('Unknown error, reload and try again.','rhcss')
			);		
		}
		//--			
		$this->send_response($ret);
	}	
	
}
?>