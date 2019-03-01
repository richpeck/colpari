<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Theme: 	ebony
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
require_once CS_PLUGIN_PATH .'/admin/includes/Color.php';
use Mexitek\PHPColors\Color;

class ABBUA_Theme_ebony{
	public function __construct(){
        $this->theme_name   = 'ebony';
		$this->theme_prefix = 'abbua_theme-'.$this->theme_name;
	}

	public function get_settings(){
		$theme_id 		= $this->theme_prefix;
		$theme_prefix 	= $theme_id .'__';
		$styles_path 	= CS_PLUGIN_URI .'/themes/'.$this->theme_name;

		$settings 		= array(
			'dependency'	=> array('theme_'.$this->theme_name,'==','true'),
			'id'			=> $theme_id,
			'type'			=> 'fieldset',
			'fields'		=> array(
                array(
					'type'			=> 'subheading',
					'content'		=> __('Ebony Theme Settings'),
				),
				array(
					'id'        	=> $theme_prefix.'style',
					'type'      	=> 'image_select',
					'title'     	=> __('Theme Style'),
					'options'   	=> array(
						'style1' 	=> $styles_path .'/style1.png',
						'style2' 	=> $styles_path .'/style2.png',
						'style3' 	=> $styles_path .'/style3.png',
						'style4' 	=> $styles_path .'/style4.png',
						'style5' 	=> $styles_path .'/style5.png',
					),
					'radio'     	=> true,
					'default'   	=> 'style1',
				),
				array(
					'id'			=> $theme_prefix.'primary',
					'type'			=> 'color_variant',
					'title'			=> __('Color Primary'),
					'options'		=> array(
						'normal'	=> true,
						'light'		=> true,
						'palettes'	=> array(
							'#d64933',
							'#98c1d9',
							'#70587c',
							'#bde4a8',
							'#226ce0',
							'#ff9505',
							'#016fb9',
							'#0c7c59',
						),
					),
					'default'		=> array(
						'normal'	=> '#518c54',
						'light'		=> '#93af95',
					),
				),
				array(
					'id'			=> $theme_prefix.'accent',
					'type'			=> 'color_picker',
					'title'			=> __('Color Accent'),
					'default'		=> '#f2545b',
					'palettes'		=> array(
						'#d64933',
						'#98c1d9',
						'#70587c',
						'#bde4a8',
						'#226ce0',
						'#ff9505',
						'#016fb9',
						'#0c7c59',
					),
				),
			),
		);
		return $settings;
	}

	public function parse_settings($settings){
		$option = function($option) use ($settings){
			$theme_prefix = $this->theme_prefix.'__';
			return $settings[$theme_prefix.$option];
		};


		// Parse Settings
		// ==========================================================================
		$color_primary_normal	= new Color($option('primary')['normal']);
		$color_primary_light	= $option('primary')['light'];
		$color_accent 			= $option('accent');
		$body_background 		= new Color('#d7d7d9');

		// Dynamic Primary color variants
		$color_primary_dark 	= '#'.$color_primary_normal->darken(5);
		$color_primary_darker 	= '#'.$color_primary_normal->darken(10);
		$body_background_light	= '#'.$body_background->lighten(10);

		$theme = array(
			// General Scaffold
			'white'				=> 'rgb(255,255,255)',		// white color
			'white_7'			=> 'rgba(255,255,255,0.7)',	// white color 70%
			'black'				=> 'rgb(0,0,0)',			// black color
			'primary'			=> $option('primary')['normal'],
			'primary_light'		=> $color_primary_light,
			'primary_dark'		=> $color_primary_dark,
			'primary_darker'	=> $color_primary_darker,

			// Background Colors Scheme
			'background1'	=> $body_background,		// body color
			'background2'	=> '#565e57',				// dark sidebar background
			'background3'	=> '#464e47',				// dark sidebar active background
			'background4'	=> '#d0dcd1',				// light sidebar background
			'background5'	=> '#c1d1c2',				// light sidebar active background
			'background6'	=> 'rgb(255,255,255)',		// light navbar background
			'background7'	=> $body_background_light,

			// Buttons
			'button1'		=> $color_primary_normal,
			'button2'		=> $color_primary_darker,

			// Border
			'border1'		=> '#464e47',		// Sidebar brand border
			'border2'		=> '#dddedd',		// aaaeab	// Navbar border

			// Text Colors Scheme
			'text1'			=> 'rgb(68, 68, 68)',		// body text
            'text2'			=> '#aaaeab',	// light navbar text color, light navbar toolbar text color, dark brand subtitle text color
            'text3'         => '#565e57',    // dark navbar text color, dark navbar toolbar text, light brand subtitle text color
			'text4'			=> '#9a9e9a',	// dark sidebar text
			'text5'			=> '#bbbebc',	// dark sidebar text hover
			'text6'			=> '#97a297',		// light sidebar text
			'text7'			=> '#818e81',		// light sidebar text hover
		);

		$style = $option('style');
		if ($style == 'style1'){
			$body_background			= $theme['background1'];

			$navbar_background 			= $theme['background6'];
			$navbar_border 				= $theme['border2'];
			$navbar_color 				= $theme['text2'];
			$navbar_toolbar_color 		= $theme['text2'];

			$brand_background 			= $theme['background2'];
			$brand_color				= $theme['background6'];
			$brand_subtitle_color		= $theme['text2'];
			$brand_border				= $theme['border1'];
			
			$sidebar_background 		= $theme['background2'];
			$sidebar_background_active	= $theme['background3'];
			$sidebar_text				= $theme['text4'];
			$sidebar_text_hover			= $theme['text5'];

			$card_border				= $theme['border2'];
			$card_background			= $theme['white'];
			$card_background_title		= $theme['white'];

			$dropdown_border_color		= $theme['border2'];
			$dropdown_background		= $theme['white'];
			$dropdown_background_hover	= $theme['background7'];
			$dropdown_color				= $theme['text2'];
			$dropdown_color_hover		= $theme['primary'];

			$input_border_color			= $theme['border2'];
			$input_border_color_focus	= $theme['primary'];
			$input_color				= $theme['text2'];

			$btn_primary_ini			= $theme['button1'];
			$btn_primary_end			= $theme['button2'];
		} else if ($style == 'style2'){
			$body_background			= $theme['background1'];

			$navbar_background 			= $theme['background2'];
			$navbar_border 				= $theme['border1'];
			$navbar_color 				= $theme['text2'];
			$navbar_toolbar_color 		= $theme['text2'];

			$brand_background 			= $theme['background2'];
			$brand_color				= $theme['background6'];
			$brand_subtitle_color		= $theme['text2'];
			$brand_border				= $theme['border1'];
			
			$sidebar_background 		= $theme['background2'];
			$sidebar_background_active	= $theme['background3'];
			$sidebar_text				= $theme['text4'];
			$sidebar_text_hover			= $theme['text5'];

			$card_border				= $theme['border2'];
			$card_background			= $theme['white'];
			$card_background_title		= $theme['white'];

			$dropdown_border_color		= $theme['border2'];
			$dropdown_background		= $theme['white'];
			$dropdown_background_hover	= $theme['background7'];
			$dropdown_color				= $theme['text2'];
			$dropdown_color_hover		= $theme['primary'];

			$input_border_color			= $theme['border2'];
			$input_border_color_focus	= $theme['primary'];
			$input_color				= $theme['text2'];

			$btn_primary_ini			= $theme['button1'];
			$btn_primary_end			= $theme['button2'];
		} else if ($style == 'style3'){
			$body_background			= $theme['background1'];

			$navbar_background 			= $theme['background2'];
			$navbar_border 				= $theme['border1'];
			$navbar_color 				= $theme['text2'];
			$navbar_toolbar_color 		= $theme['text2'];

			$brand_background 			= $theme['background2'];
			$brand_color				= $theme['background6'];
			$brand_subtitle_color		= $theme['text2'];
			$brand_border				= $theme['border1'];
			
			$sidebar_background 		= $theme['background4'];
			$sidebar_background_active	= $theme['background5'];
			$sidebar_text				= $theme['text6'];
			$sidebar_text_hover			= $theme['text7'];

			$card_border				= $theme['border2'];
			$card_background			= $theme['white'];
			$card_background_title		= $theme['white'];

			$dropdown_border_color		= $theme['border2'];
			$dropdown_background		= $theme['white'];
			$dropdown_background_hover	= $theme['background7'];
			$dropdown_color				= $theme['text2'];
			$dropdown_color_hover		= $theme['primary'];

			$input_border_color			= $theme['border2'];
			$input_border_color_focus	= $theme['primary'];
			$input_color				= $theme['text2'];

			$btn_primary_ini			= $theme['button1'];
			$btn_primary_end			= $theme['button2'];
		} else if ($style == 'style4'){
			$body_background			= $theme['background1'];

			$navbar_background 			= $theme['background6'];
			$navbar_border 				= $theme['border2'];
			$navbar_color 				= $theme['text2'];
			$navbar_toolbar_color 		= $theme['text2'];

			$brand_background 			= $theme['background6'];
			$brand_color				= $theme['text3'];
			$brand_subtitle_color		= $theme['text2'];
			$brand_border				= $theme['border2'];
			
			$sidebar_background 		= $theme['background2'];
			$sidebar_background_active	= $theme['background3'];
			$sidebar_text				= $theme['text4'];
			$sidebar_text_hover			= $theme['text5'];

			$card_border				= $theme['border2'];
			$card_background			= $theme['white'];
			$card_background_title		= $theme['white'];

			$dropdown_border_color		= $theme['border2'];
			$dropdown_background		= $theme['white'];
			$dropdown_background_hover	= $theme['background7'];
			$dropdown_color				= $theme['text2'];
			$dropdown_color_hover		= $theme['primary'];

			$input_border_color			= $theme['border2'];
			$input_border_color_focus	= $theme['primary'];
			$input_color				= $theme['text2'];

			$btn_primary_ini			= $theme['button1'];
			$btn_primary_end			= $theme['button2'];
		} else if ($style == 'style5'){
			$body_background			= $theme['background1'];

			$navbar_background 			= $theme['background6'];
			$navbar_border 				= $theme['border2'];
			$navbar_color 				= $theme['text2'];
			$navbar_toolbar_color 		= $theme['text2'];

			$brand_background 			= $theme['background6'];
			$brand_color				= $theme['text3'];
			$brand_subtitle_color		= $theme['text2'];
			$brand_border				= $theme['border2'];
			
			$sidebar_background 		= $theme['background4'];
			$sidebar_background_active	= $theme['background5'];
			$sidebar_text				= $theme['text6'];
			$sidebar_text_hover			= $theme['text7'];

			$card_border				= $theme['border2'];
			$card_background			= $theme['white'];
			$card_background_title		= $theme['white'];

			$dropdown_border_color		= $theme['border2'];
			$dropdown_background		= $theme['white'];
			$dropdown_background_hover	= $theme['background7'];
			$dropdown_color				= $theme['text2'];
			$dropdown_color_hover		= $theme['primary'];

			$input_border_color			= $theme['border2'];
			$input_border_color_focus	= $theme['primary'];
			$input_color				= $theme['text2'];

			$btn_primary_ini			= $theme['button1'];
			$btn_primary_end			= $theme['button2'];
		}

		$btn_normal_ini 	= new Color($btn_primary_ini);
		$btn_normal_end 	= new Color($btn_primary_end);
		$btn_normal_border 	= new Color('#'.$btn_normal_ini->darken(10));
		$btn_normal_color	= $theme['white_7'];
	
		$btn_hover_ini 		= '#'.$btn_normal_ini->darken(2);
		$btn_hover_end 		= '#'.$btn_normal_end->darken(2);
		$btn_hover_border 	= '#'.$btn_normal_border->darken(15);
		$btn_hover_color	= $theme['white'];
	
		$btn_active_ini 	= '#'.$btn_normal_ini->darken(5);
		$btn_active_end 	= '#'.$btn_normal_end->darken(5);
		$btn_active_border 	= '#'.$btn_normal_border->darken(20);
		$btn_active_color	= $theme['white_7'];

		// Output Theme CSS Vars
		// ==========================================================================
		$output = "
		:root{
			%s_color-primary:					$color_primary_normal;
			%s_color-primary-light:				$color_primary_light;
			%s_color-primary-dark:				$color_primary_dark;
			%s_color-primary-darker:			$color_primary_darker;
			%s_color-accent:					$color_accent;
			%s_body-background:					$body_background;
			%s_navbar-background: 				$navbar_background;
			%s_navbar-border-color: 			$navbar_border;
			%s_navbar-color: 					$navbar_color;
			%s_navbar-toolbar-color: 			$navbar_toolbar_color;
			%s_brand-background:				$brand_background;
			%s_brand-border:					$brand_border;
			%s_brand-color:						$brand_color;
			%s_brand-subtitle-color:			$brand_subtitle_color;
			%s_sidebar-background:				$sidebar_background;
			%s_sidebar-background-active:		$sidebar_background_active;
			%s_sidebar-text:					$sidebar_text;
			%s_sidebar-text-hover:				$sidebar_text_hover;
			%s_card-background:					$card_background;
			%s_card-background-title:			$card_background_title;
			%s_card-border-color:				$card_border;
			%s_dropdown-border-color:			$dropdown_border_color;
			%s_dropdown-background:				$dropdown_background;
			%s_dropdown-background-hover:		$dropdown_background_hover;
			%s_dropdown-color:					$dropdown_color;
			%s_dropdown-color-hover:			$dropdown_color_hover;
			%s_input-border-color:				$input_border_color;
			%s_input-border-color-focus:		$input_border_color_focus;
			%s_input-color:						$input_color;
			%s_button-primary-normal-ini:		$btn_normal_ini;
			%s_button-primary-normal-end:		$btn_normal_end;
			%s_button-primary-normal-border:	$btn_normal_border;
			%s_button-primary-normal-color:		$btn_normal_color;
			%s_button-primary-hover-ini:		$btn_hover_ini;
			%s_button-primary-hover-end:		$btn_hover_end;
			%s_button-primary-hover-border:		$btn_hover_border;
			%s_button-primary-hover-color:		$btn_hover_color;
			%s_button-primary-active-ini:		$btn_active_ini;
			%s_button-primary-active-end:		$btn_active_end;
			%s_button-primary-active-border:	$btn_active_border;
			%s_button-primary-active-color:		$btn_active_color;
		}
		";
		$prefix = CS_CSS_THEME_SLUG;
		$output = str_replace('%s',$prefix,$output);
		return $output;
	}
}