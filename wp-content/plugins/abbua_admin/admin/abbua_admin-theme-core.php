<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Theme: 	core
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class ABBUA_Theme_core{
	public function __construct(){
        $this->theme_name   = 'default';
		$this->theme_prefix = 'abbua_theme-'.$this->theme_name;
	}

	public function get_settings(){
		$theme_id 		= $this->theme_prefix;
		$theme_prefix 	= $theme_id .'__';

		$settings 		= array();
		return $settings;
	}

	public function parse_settings($settings){
		$option = function($option) use ($settings){
			$theme_prefix = $this->theme_prefix.'__';
			return $settings[$theme_prefix.$option];
		};

		// Parse Settings
		// ==========================================================================
		$login_button_background 	= '';
		$page_loader_primary		= '';
		$page_loader_secondary		= '';
		$login_background_custom	= '';

		// Logo Settings
		$logo_brand 					= wp_get_attachment_url(cs_get_settings('logo_image'));
		$logo_brand_collapsed 			= wp_get_attachment_url(cs_get_settings('logo_image_collapsed'));
		$logo_login 					= wp_get_attachment_url(cs_get_settings('logo_image_login'));

		// Login Page
		$login_background 				= cs_get_settings('login_page_background_image');
		$loginbox_background 			= cs_get_settings('login_page_loginbox_background');
		$login_message_color			= cs_get_settings('login_page_login_message_color');
		$login_alert_message_display	= cs_get_settings('login_page_logout_message_visibility');
		$login_alert_message_display 	= ($login_alert_message_display) ? 'none' : 'block';
		$login_button_status			= cs_get_settings('login_page_loginbutton_status');
		$login_button_bg_status 		= cs_get_settings('login_page_loginbutton_background_status');

		// Login Background Custom
		if ($login_background == 'background-custom'){
			$bg = cs_get_settings('login_page_background');
			
			$image 		= wp_get_attachment_url($bg['image']);
			$color 		= $bg['color'];
			
			$login_background_custom = "
				body.cs-abbua-login-theme__background-custom{
					background-image: url({$image});
					background-color: $color;
				}
			";
		}

		// Login Button
		if ($login_button_status && $login_button_bg_status){
			$lb_bg						= cs_get_settings('login_page_loginbutton_background');
			$lb_color					= cs_get_settings('login_page_loginbutton_color');
			$login_button_bg_normal		= $lb_bg['regular'];
			$login_button_bg_hover		= $lb_bg['hover'];
			$login_button_bg_active		= $lb_bg['active'];
			$login_button_color_normal	= $lb_color['regular'];
			$login_button_color_hover	= $lb_color['hover'];
			$login_button_color_active	= $lb_color['active'];

			$login_button_background = "
				%s_login-button-bg-normal:		$login_button_bg_normal;
				%s_login-button-bg-hover:		$login_button_bg_hover;
				%s_login-button-bg-active:		$login_button_bg_active;
				%s_login-button-color-normal:	$login_button_color_normal;
				%s_login-button-color-hover:	$login_button_color_hover;
				%s_login-button-color-active:	$login_button_color_active;
			";
		}

		// Page Loader
		$page_loader_custom_colors_status 	= cs_get_settings('page_loader_custom_colors_status');
		if ($page_loader_custom_colors_status){
			$page_loader_primary 	= cs_get_settings('page_loader_color_primary');
			$page_loader_secondary 	= cs_get_settings('page_loader_color_secondary');
		}

		// Custom CSS
		$custom_css_status = cs_get_settings('customcss_status');
		$custom_css = '';
		if ($custom_css_status){
			$custom_css = cs_get_settings('customcss');
		}

		// Output Theme CSS Vars
		// ==========================================================================
		$output = "
			:root{
				$login_button_background

				%s_login-logo-image: 			url({$logo_login});
				%s_login-message-color:			$login_message_color;
				%s_login-alert-message-display:	$login_alert_message_display;
				%s_loginbox-background: 		$loginbox_background;
				
				%s_page-loader-primary:			$page_loader_primary;
				%s_page-loader-secondary:		$page_loader_secondary;

				%s_brand-logo-normal:			url({$logo_brand});
				%s_brand-logo-collapsed:		url({$logo_brand_collapsed});
			}
			$login_background_custom
			$custom_css
		";
		$prefix = CS_CSS_THEME_SLUG;
		$output = str_replace('%s',$prefix,$output);
		return $output;
	}
}