<?php
class ABBUA_Theme{
	public function __construct() {
		$this->themes 	= $this->load_themes();
	}

	private function load_themes(){
        $themes_path 	= CS_PLUGIN_PATH . '/themes/';
        $themes_uri 	= CS_PLUGIN_URI . '/themes/';

		$themes = array();
		$themes_dir = new DirectoryIterator($themes_path);
		foreach ($themes_dir as $theme){
			if ($theme->isDir() && !$theme->isDot()) {
				$active_dir; $active_theme;
				$theme_path 	= $theme->getRealPath();
				$theme_name 	= $theme->getFilename();
				$theme_uri 		= $themes_uri .'/'. $theme_name;

				$active_theme 				= $theme_path . '/'.$theme_name.'.php';
				$active_theme_preview 		= $theme_uri . '/'.$theme_name.'.png';
				$active_theme_stylesheet	= $theme_path . '/'.$theme_name.'.css';

				if (file_exists($active_theme)){
                    require_once $active_theme;
                    
					$class = 'ABBUA_Theme_'.$theme_name;
					$newtheme = new $class;
					$newthemesettings = array(
                        'type'          => 'dynamic',
						'name'			=> $theme_name,
						'class'			=> $class,
						'preview'		=> $active_theme_preview,
						'stylesheet'	=> $active_theme_stylesheet,
						'settings'		=> $newtheme->get_settings(),
						'instance'		=> $newtheme,
					);
					$themes[$theme_name] = $newthemesettings;
				}
			}
        }
        
        /**
         * Core Theme
         * Based on user custom settings
         */
        require_once 'abbua_admin-theme-core.php';
        $class = 'ABBUA_Theme_core';
        $coretheme = new $class;
        $corethemesettings = array(
            'type'          => 'core',
            'name'			=> 'core',
            'class'			=> $class,
            'preview'		=> false,
            'stylesheet'	=> false,
            'settings'		=> $coretheme->get_settings(),
            'instance'		=> $coretheme,
        );
        $themes['core'] = $corethemesettings;

		return $themes;
	}

	public function get_themes(){
		return $this->themes;
	}

    public function parse_theme_settings($theme,$settings){
		if ($theme && $settings){
			$themes 			= $this->themes;
			$theme_instance 	= $themes[$theme]['instance'];
			$parsed_settings 	= $theme_instance->parse_settings($settings);

			return $parsed_settings;	
		}
	}

	public function parse_theme_stylesheet($theme_vars){
		$themes = $this->themes;
        $buffer = "";
        
        // Add Theme Vars
        $buffer .= $theme_vars;

		foreach($themes as $theme){
			$stylesheet = $theme['stylesheet'];
			if (file_exists($stylesheet)){
				$buffer .= file_get_contents($theme['stylesheet']);
			}
		}		

		// CSS MINIFY & COMPRESS
		// --------------------------------------------------------------------------

		// Remove comments
		$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
		// Remove space after colons
		$buffer = str_replace(': ', ':', $buffer);
		// Remove whitespace
		$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
		// Remove space near commas
		$buffer = str_replace(', ', ',', $buffer);
		$buffer = str_replace(' ,', ',', $buffer);
		// Remove space before brackets
		$buffer = str_replace('{ ', '{', $buffer);
		$buffer = str_replace('} ', '}', $buffer);
		$buffer = str_replace(' {', '{', $buffer);
		$buffer = str_replace(' }', '}', $buffer);
		// Remove last dot with comma
		$buffer = str_replace(';}', '}', $buffer);
		// Remove space before and after >
		$buffer = str_replace('> ','>', $buffer);
		$buffer = str_replace(' >','>', $buffer);

		// Enable GZip encoding.
		ob_start("ob_gzhandler");

		// Enable caching
		header('Cache-Control: public');

		// Expire in one day
		// header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');

		// Set the correct MIME type, because Apache won't set it for us
		header("Content-type: text/css");
		
		// Write everything out
		echo($buffer);

		exit;
	}
}