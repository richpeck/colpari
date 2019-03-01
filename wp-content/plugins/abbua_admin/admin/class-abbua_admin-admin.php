<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.castorstudio.com
 * @since      1.0.0
 *
 * @package    Abbua_admin
 * @subpackage Abbua_admin/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Abbua_admin
 * @subpackage Abbua_admin/admin
 * @author     Castorstudio <support@castorstudio.com>
 */
class Abbua_admin_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	static $themes;
	static $themes_instance;


	/**
	 * Load Available Themes
	 *
	 * @since    1.0.0
	 */
	public function cs_abbua_load_themes(){
		require_once 'class-abbua_admin-theme.php';
		self::$themes_instance 	= new ABBUA_Theme();
		self::$themes 			= self::$themes_instance->get_themes();
	}

	/**
	 * Get Available themes for the right settings section
	 *
	 * @since    1.0.0
	 */
	public static function cs_abbua_get_themes($preview = true){
		$active_themes = self::$themes;

		$themes = [];
		foreach ($active_themes as $theme){
			if ($theme['type'] == 'dynamic'){
				$name = str_replace('_',' ',$theme['name']);
				$themes[$theme['name']] = ($preview) ? $theme['preview'] : ucwords($name);
			}
		}
		return $themes;
	}

	/**
	 * Get Available themes settings for admin options
	 *
	 * @since    1.0.0
	 */
	public static function cs_abbua_get_dynamic_settings(){
		$active_themes = self::$themes;
		$settings = [];

		foreach ($active_themes as $theme){
			if ($theme['type'] == 'dynamic'){
				$settings[] = $theme['settings'];
			}
		}
		return $settings;
	}


	/**
	 * Generate dynamic css stylesheet with the available loaded themes for use on the Frontend
	 * Gets called from an 'add_action()' function on class-ultimatelogoshowcase.php file
	 *
	 * @since    	1.0.0
	 * @param 		string 		String of css vars to apply to the parsed theme stylesheet
	 */
	public function cs_abbua_dynamic_themes_callback() {
		$active_theme 			= cs_get_settings('theme');
		$active_theme_settings 	= cs_get_settings('abbua_theme-'.$active_theme);

		$vars = self::$themes_instance->parse_theme_settings($active_theme,$active_theme_settings);
		$vars .= self::$themes_instance->parse_theme_settings('core',$active_theme_settings);
		$vars = $this->sanitize($vars);
		// $vars = implode('', $vars);
		
		// $showcase_style_vars			= $showcase->get_style_vars(true);

		self::$themes_instance->parse_theme_stylesheet($vars);
	}


	/**
	 * Get general theme style vars
	 *
	 * @description Returns the full list of settings styles variables, to apply and use into the admin themes.
	 *
	 * @since 	1.0.0
	 * @param 	boolean 	$asString 	Return the list as a string instead of array
	 * @return 	string|array
	 */
	public function get_style_vars($asString){
		$vars = $this->style_vars;

		if ($asString){
			$vars = implode('', $vars);
		}
		return $vars;
	}
	private function sanitize($string){
		return filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/abbua_admin-admin.css', array('cs-framework'), $this->version, 'all' );

		// AJAX CALL: All Available Themes (for all customizable sections)
		wp_enqueue_style( $this->plugin_name . '_dynamic-themes',admin_url('admin-ajax.php').'?action=abbua_dynamic_themes', array($this->plugin_name), $this->version, 'all');

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name . '_select2', plugin_dir_url( __FILE__ ) . 'js/select2.full.min.js', array( 'jquery' ), $this->version, false);
		
		if(cs_get_settings('page_loader_status')){
			wp_enqueue_script( $this->plugin_name . '_pace', plugin_dir_url( __FILE__ ) . 'js/pace.js', array( ), $this->version, false );
		}
		if (cs_get_settings('sidebar_scrollbar')){
			wp_enqueue_script( $this->plugin_name . '_scrollbars', plugin_dir_url( __FILE__ ) . 'js/jquery.overlayScrollbars.js', array( 'jquery' ), $this->version, false );
		}
		
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/abbua_admin-admin.js', array( 'jquery' ), $this->version, false );

		wp_localize_script( $this->plugin_name, 'abbua_admin', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'cs-abbua-admin-nonce' )  ) );
	}


	/**
	 * Admin Head
	 *
	 * @since    1.0.0
	 */
	function cs_abbua_admin_init(){

	}
	function cs_abbua_admin_head(){
		// Favicons & Device Icons
		$devices_icons = $this->cs_abbua_favicon_get_html();
		if ($devices_icons) {
			echo $devices_icons;
		}
	}
	function cs_abbua_admin_body_class($classes){
		// Page Loader
		// --------------------------------------------------------
		if (cs_get_settings('page_loader_status')){
			$theme = cs_get_settings('page_loader_theme');
			$classes .= ' cs-abbua-page-loader__'.$theme;
		}


		// User Profile Settings
		// --------------------------------------------------------
		if (cs_get_settings('user_profile_status')){
			$classes .= ' cs-abbua-userprofile_hidden';
		}
		$sections = cs_get_settings('user_profile_options');
		if ($sections){
			$editor = in_array('editor', $sections);
			if ($editor){
				$classes .= ' cs-abbua-userprofile_hidden-editor';
			}
			$syntaxis = in_array('syntaxis', $sections);
			if ($syntaxis){
				$classes .= ' cs-abbua-userprofile_hidden-syntaxis';
			}
			$colors = in_array('colors', $sections);
			if ($colors){
				$classes .= ' cs-abbua-userprofile_hidden-colors';
			}
			$shortcuts = in_array('shortcuts', $sections);
			if ($shortcuts){
				$classes .= ' cs-abbua-userprofile_hidden-shortcuts';
			}
			$adminbar = in_array('adminbar', $sections);
			if ($adminbar){
				$classes .= ' cs-abbua-userprofile_hidden-adminbar';
			}
			$language = in_array('language', $sections);
			if ($language){
				$classes .= ' cs-abbua-userprofile_hidden-language';
			}
		}


		// Top Navbar Fixed Style
		// --------------------------------------------------------
		$navbar_title = cs_get_settings('navbar_position');
		if ($navbar_title == 'fixed'){
			$classes .= ' cs-abbua-fixed-title';
		}

		
		// Sidebar Fixed Style
		// --------------------------------------------------------
		$sidebar_accordion		= cs_get_settings('sidebar_accordion');
		$sidebar_scrollbar		= cs_get_settings('sidebar_scrollbar');
		$sidebar_brand_position = cs_get_settings('sidebar_brand_position');
		$sidebar_position		= cs_get_settings('sidebar_position');

		if ($sidebar_accordion){
			$classes .= ' cs-abbua-sidebar-accordion';
		}
		if ($sidebar_scrollbar){
			$classes .= ' cs-abbua-sidebar-scrollbar';
		}
		if ($sidebar_brand_position == 'fixed'){
			$classes .= ' cs-abbua-sidebar-brand-fixed';
		}
		if ($sidebar_position == 'fixed'){
			$classes .= ' cs-abbua-sidebar-fixed';
		}

		return $classes;
	}

	function cs_abbua_admin_dashboard_widget_right_now($content){
		if (!cs_get_settings('rightnowwidget_status')){
			$version = $this->version;
			$content .= printf( __('<br> Your WordPress is using <a href="%s" title="ABBUA Admin Website" target="_blank">ABBUA Admin %s</a>','abbua_admin'), CS_PLUGIN_URL, $version);
		}
		return $content;
	}


	/**
	 * Plugin row action links
	 */
	function cs_abbua_plugin_row_action_links($actions,$file){
		$abbua_basename = 'abbua_admin/abbua_admin.php';
		if ($abbua_basename != $file){ return $actions; }

		$settings = array('settings' => '<a href="admin.php?page=cs-abbua-admin-settings">' . __('Settings','abbua_admin') . '</a>');
		$site_link = array('support' => '<a href="' . CS_PLUGIN_URL . '/support/" target="_blank">'. __('Support','abbua_admin') .'</a>');

		$actions = array_merge($settings, $actions);
		$actions = array_merge($site_link, $actions);

		return $actions;
	}

	/**
	 * Plugin row meta links
	 */
	function cs_abbua_plugin_row_meta_links( $input, $file ) {
		$abbua_basename = 'abbua_admin/abbua_admin.php';
		if ($abbua_basename != $file){ return $input; }

		$links = array(
			'<a href="' . admin_url( 'admin.php?page=cs-abbua-admin-home' ) . '">' . __( 'Getting Started','abbua_admin' ) . '</a>',
			'<a href="' . CS_PLUGIN_URL . '/docs/" target="_blank">' . __( 'Documentation','abbua_admin' ) . '</a>',
		);

		$output = array_merge( $input, $links );

		return $output;
	}


	/**
	 * On Plugin Settings Save Hook
	 *
	 * @param [array] $options
	 * @return array
	 * @since    1.0.0
	 */
	function cs_abbua_save_plugin_settings($options){
		$this->cs_abbua_favicon_generate($options);

	  	return $options;
	}


	/**
	 * Create Favicons - Apple Devices Icon - Android Devices Icon
	 * 
	 * The icons are generated by resizing the specified uploaded image
	 *
	 * @since    1.0.0
	 */
	function cs_abbua_favicon_generate($options){
		$favicon_status 	= $options['logo_favicon_status'];
		$apple_status 		= $options['logo_apple_status'];
		$android_status 	= $options['logo_android_status'];
		$devices			= $options['logo_devices_fs'];

		if ($favicon_status || $apple_status || $android_status){
			require_once CS_PLUGIN_PATH . '/admin/includes/ImageResize.php';
	
			$favicon_path 	= CS_PLUGIN_PATH .'/favicons';
			if (!file_exists($favicon_path)) {
				mkdir($favicon_path, 0777, true);
			}
	
			// FAVICON
			if ($favicon_status){
				$favicon_id = $devices['logo_favicon'];

				if (cs_get_settings('logo_favicon') != $favicon_id) {
					$favicon 	= get_attached_file($favicon_id);
					$sizes		= array('16', '32', '96');
					
					if ($favicon){
						foreach ($sizes as $size){
							$image = new \Gumlet\ImageResize($favicon);
							$image
								->resizeToBestFit($size, $size)
								->save($favicon_path."/favicon-{$size}x{$size}.png");
						}
					} else {
						foreach ($sizes as $size){
							$file = $favicon_path."/favicon-{$size}x{$size}.png";
							unlink($file);
						}
					}
				}
			}
	
			// APPLE
			if ($apple_status){
				$apple_id = $devices['logo_apple'];

				if (cs_get_settings('logo_apple') != $apple_id) {
					$apple 	= get_attached_file($apple_id);
					$sizes 	= array('57', '60', '72', '76', '114', '120', '144', '152', '180');
		
					if ($apple){
						foreach ($sizes as $size){
							$image = new \Gumlet\ImageResize($apple);
							$image
								->resizeToBestFit($size, $size)
								->save($favicon_path."/apple-touch-icon-{$size}x{$size}.png");
						}
					} else {
						foreach ($sizes as $size){
							$file = $favicon_path."/apple-touch-icon-{$size}x{$size}.png";
							unlink($file);
						}
					}
				}
			}
	
			// ANDROID
			if ($android_status){
				$android_id = $devices['logo_android'];

				if (cs_get_settings('logo_android') != $android_id) {
					$android 	= get_attached_file($android_id);
					$sizes 		= array('36', '48', '72', '96', '144', '192');
		
					if ($android){
						foreach ($sizes as $size){
							$image = new \Gumlet\ImageResize($android);
							$image
								->resizeToBestFit($size, $size)
								->save($favicon_path."/android-chrome-{$size}x{$size}.png");
						}
					} else {
						foreach ($sizes as $size){
							$file = $favicon_path."/android-chrome-{$size}x{$size}.png";
							unlink($file);
						}
					}
				}
			}
		}
	}


	/**
	 * Generate Favicon/Apple/Android icons HTML code to be displayed on the admin area
	 *
	 * @since    1.0.0
	 */
	function cs_abbua_favicon_get_html(){
		$favicon_path 	= CS_PLUGIN_PATH .'/favicons';
		$favicon_uri	= CS_PLUGIN_URI .'/favicons';
		$html = '';

		// FAVICON
		if (cs_get_settings('logo_favicon_status')){
			foreach (array('16', '32', '96') as $size) {
				$size = "{$size}x{$size}";
				if (file_exists("{$favicon_path}/favicon-{$size}.png")) {
					$html .= '<link rel="icon" type="image/png" href="'.$favicon_uri.'/favicon-'.$size.'.png" sizes="'.$size.'">';
					$html .= "\n";
				}
			}
		}

		// APPLE
		if (cs_get_settings('logo_favicon_status')){
			foreach (array('57', '60', '72', '76', '114', '120', '144', '152', '180') as $size){
				$size = "{$size}x{$size}";
				if (file_exists("{$favicon_path}/apple-touch-icon-{$size}.png")) {
					$html .= '<link rel="apple-touch-icon" sizes="'.$size.'" href="'.$favicon_uri.'/apple-touch-icon-'.$size.'.png">';
					$html .= "\n";
				}
			}
		}

		// ANDROID
		if (cs_get_settings('logo_android_status')){
			foreach (array('36', '48', '72', '96', '144', '192') as $size){
				$size = "{$size}x{$size}";
				if (file_exists("{$favicon_path}/android-chrome-{$size}.png")) {
					$html .= '<link rel="icon" type="image/png" href="'.$favicon_uri.'/android-chrome-'.$size.'.png" sizes="'.$size.'">';
					$html .= "\n";
				}
			}
		}

		return strlen($html) > 0 ? $html : false;
	}


	/**
	 * SET Admin Settings for the Admin Area [Hook: admin_footer]
	 *
	 * @since    1.0.0
	 */
	function cs_abbua_admin_getset_settings(){
		// General Settings
		// --------------------------------------------------------
		$logo_url = cs_get_settings('logo_url');
		if ($logo_url == 'admin_url') { $logo_url = admin_url(); }
		$output = array(
			'logo'		=> array(
				'status'	=> cs_get_settings('logo_status'),
				'url'		=> $logo_url,
				'type'		=> cs_get_settings('logo_type'),
				'image'		=> wp_get_attachment_url(cs_get_settings('logo_image')),
				'collapsed'	=> wp_get_attachment_url(cs_get_settings('logo_image_collapsed')),
				'icon'		=> cs_get_settings('logo_icon'),
				'text'		=> cs_get_settings('logo_text'),
				'login'		=> wp_get_attachment_url(cs_get_settings('logo_image_login')),
			),
			'navbar' 	=> array(
				'site' 		=> cs_get_settings('navbar_link_site'),
				'updates'	=> cs_get_settings('navbar_link_updates'),
				'comments'	=> cs_get_settings('navbar_link_comments'),
				'addnew'	=> cs_get_settings('navbar_link_addnew'),
				'profile'	=> cs_get_settings('navbar_link_profile'),
				'position'	=> cs_get_settings('navbar_position'),
			),
			'adminmenu'	=> array(
				'status'			=> cs_get_settings('sidebar_status'),
				'accordion'			=> cs_get_settings('sidebar_accordion'),
				'scrollbar'			=> cs_get_settings('sidebar_scrollbar'),
				'brand_position'	=> cs_get_settings('sidebar_brand_position'),
				'position'			=> cs_get_settings('sidebar_position'),
			),
		);

		$output = json_encode($output);

		echo '<script type="text/javascript">$csj = jQuery.noConflict();ABBUA_SETTINGS.settings = $csj.extend(true,ABBUA_SETTINGS.settings, '.$output.');</script>';

	}


	/**
	 * Login Area: Enqueue & Register stylesheets and scripts for login area
	 *
	 * @since    1.0.0
	 */
	function cs_abbua_enqueue_login_style(){
		wp_enqueue_style( $this->plugin_name.'feather-icons', CS_URI .'/assets/css/feather-icons.css', array(), '1.0', 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/abbua_admin-admin.css', array(), $this->version, 'all' );
		
		// AJAX CALL: All Available Themes (for all customizable sections)
		wp_enqueue_style( $this->plugin_name . '_dynamic-themes',admin_url('admin-ajax.php').'?action=abbua_dynamic_themes', array($this->plugin_name), $this->version, 'all');

		// Scripts
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/abbua_admin-login.js', array( 'jquery' ), $this->version, false );
	}


	/**
	 * Login Head
	 * 
	 * Para agregar o quitar lo que sea necesario solo en la pantalla de login:
	 * - Remover el efecto shake
	 *
	 * @since    1.0.0
	 */
	function cs_abbua_login_head(){
		if (cs_get_settings('login_page_error_shake')){
			remove_action('login_head', 'wp_shake_js', 12);
		}

		$this->cs_abbua_admin_head();
	}
	function cs_abbua_login_class($classes){
		$bg 		= cs_get_settings('login_page_background_image');
		$style 		= cs_get_settings('login_page_loginbox_style');
		$loginbox 	= cs_get_settings('login_page_loginbox_background_style');

		if ($bg){
			$classes[] = 'cs-abbua-login-theme__'.$bg;
		}
		if ($style){
			$classes[] = 'cs-abbua-login-theme__'.$style;
		}
		if ($loginbox){
			$classes[] = 'cs-abbua-login-theme__'.$loginbox;
		}
		return $classes;
	}
	function cs_abbua_login_label_change( $translated_text, $original_text, $domain ) {
		// Reemplazar solo en la pagina de login o registro
		if (in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) )) {
			switch ( $original_text ) {
				case 'Username or Email Address':
					$translated_text = '';
				case 'Password':
					$translated_text = '';
					break;
				case 'Remember Me':
					if (cs_get_settings('login_page_rememberme_status')) {
						$translated_text = cs_get_settings('login_page_rememberme');
					}
					break;
				case 'Log In':
					if (cs_get_settings('login_page_loginbutton_status')) {
						$translated_text = cs_get_settings('login_page_loginbutton');
					}
					break;
				case 'Lost your password?':
					if (cs_get_settings('login_page_link_lostpassword_status')) {
						if (!cs_get_settings('login_page_link_lostpassword_visibility')){
							$translated_text = cs_get_settings('login_page_link_lostpassword');
						} else {
							$translated_text = '';
						}
					}
					break;
				case '&larr; Back to %s':
					if (cs_get_settings('login_page_link_back_status')) {
						if (!cs_get_settings('login_page_link_back_visibility')){
							$translated_text = cs_get_settings('login_page_link_back');
						} else {
							$translated_text = '';
						}
					}
					break;
			}
		}
		return $translated_text;
	}


	/**
	 * Replace Login Area Settings
	 * 
	 * @since	1.0.0
	 */
	function cs_abbua_login_title($default){
		if (cs_get_settings('login_page_title_status')){
			return cs_get_settings('login_page_title');
		} else { return $default; }
	}
	function cs_abbua_login_logo_url($default) {
		if (cs_get_settings('login_logo_url_status')){
			return cs_get_settings('login_logo_url');
		} else {
			return get_bloginfo( 'url' );
			// return $default;
		}
	}
	function cs_abbua_login_logo_url_title() {
		if (cs_get_settings('login_logo_url_title_status')){
			return cs_get_settings('login_logo_url_title');
		} else { return $default; }
	}

	// Login Messages
	function cs_abbua_login_message_override($message) {
		if (empty($message)){
			if (cs_get_settings('login_page_login_message_status')){
				if (cs_get_settings('login_page_login_message_style')){
					return '<div class="cs-glass-message"><div class="cs-glass-message-inner">'.cs_get_settings('login_page_login_message').'</div></div>';
				} else {
					return '<div class="cs-normal-message">'.cs_get_settings('login_page_login_message').'</div>';
				}
			}
		} else {
			return $message;
		}
	}
	function cs_abbua_login_messages_override($messages){
		global $errors;
		$err_codes = $errors->get_error_codes();

		if ( in_array( 'loggedout', $err_codes ) ) {
			if (cs_get_settings('login_page_logout_message_status')){
				$message = cs_get_settings('login_page_logout_message');
			}
		}
		return $message;
	}
	function cs_abbua_login_errors_override($error) {
		global $errors;
		$err_codes = $errors->get_error_codes();
	
		// Invalid username.
		// Default: '<strong>ERROR</strong>: Invalid username. <a href="%s">Lost your password</a>?'
		if ( in_array( 'invalid_username', $err_codes ) ) {
			if (cs_get_settings('login_page_invalid_username_status')){
				$error = cs_get_settings('login_page_invalid_username');
			}
		}
	
		// Incorrect password.
		// Default: '<strong>ERROR</strong>: The password you entered for the username <strong>%1$s</strong> is incorrect. <a href="%2$s">Lost your password</a>?'
		if ( in_array( 'incorrect_password', $err_codes ) ) {
			if (cs_get_settings('login_page_invalid_password_status')){
				$error = cs_get_settings('login_page_invalid_password');
			}
		}
	
		return $error;
	}


	/**
	 * Replace Footer Text & Footer Version
	 * 
	 * @since 	1.0.0
	 */
	function cs_abbua_remove_footer_text($default){
		$status = cs_get_settings('footer_text_status');
		if ($status){
			$hidden = cs_get_settings('footer_text_visibility');
			$text 	= cs_get_settings('footer_text');

			echo ($hidden) ? '' : $text;
		} else {
			echo $default;
		}
	}
	function cs_abbua_remove_footer_version($default){
		$status = cs_get_settings('footer_version_status');
		if ($status){
			$hidden = cs_get_settings('footer_version_visibility');
			$text 	= cs_get_settings('footer_version');
			
			echo ($hidden) ? '' : $text;
		} else {
			echo $default;
		}
	}


	/**
	 * Register the 'Admin Menu Manager Page' as a submenu page
	 * 
	 * @since 	1.0.0
	 */
	function cs_register_admin_menu_settings(){
		add_submenu_page('cs-abbua-admin-settings', 'Admin Menu', __('Admin Menu Manager','abbua_admin'), 'manage_options', 'cs-abbua-menu-settings', 'cs_menumng_settings_page');
		add_submenu_page('cs-abbua-admin-settings', 'ABBUA Admin Home', __( 'About the Plugin','abbua_admin'), 'manage_options', 'cs-abbua-admin-home', 'cs_abbua_admin_welcome_page', 11 );
	}


	/**
	 * Admin Menu Manager - Reordenar Admin Menu
	 *
	 * @since 	1.0.0
	 */
	function cs_adminmenu_rearrange() {
		$menu_manager_state = cs_get_user_type();
	
		global $menu;
		global $submenu;
	
		if ($menu_manager_state) {
			$renamemenu = cs_rename_menu();
			$menu = $renamemenu;
			$neworder = cs_adminmenu_neworder();
			$ret = cs_adminmenu_newmenu($neworder, $menu);
			$menu = $ret;
	
			$GLOBALS['cs_abbua_menu'] = $menu;
	
			$menu = cs_adminmenu_disable($menu);
		}
		return $menu;
	}
	
	/**
	 * Admin Menu Manager - Reordenar Admin Menu Submenu
	 *
	 * @since 	1.0.0
	 */
	function cs_admin_submenu_rearrange() {
		global $cs_abbua_menu;
		global $submenu;
	
		$menu_manager_state = cs_get_user_type();
		if ($menu_manager_state) {
			$renamesubmenu = cs_rename_submenu();
			$submenu = $renamesubmenu;

			$newsuborder = cs_adminmenu_neworder();

			$ret = cs_adminmenu_newsubmenu($newsuborder, $submenu, $cs_abbua_menu);
			$submenu = $ret;
			$GLOBALS['cs_abbua_submenu'] = $submenu;
			$submenu = cs_adminsubmenu_disable($submenu);
		}
		return $submenu;
	}




	/**
	 * AJAX CALLS CALLBACKS
	 *
	 * @since 	1.0.0
	 */
	function cs_abbua_menu_save_callback() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cs-abbua-admin-nonce')) {
			die('Permissions check failed. Please login or refresh (if already logged in) the page, then try Again.');
		}
	
		$neworder 			= $_POST['neworder'];
		$newsuborder 		= $_POST['newsuborder'];
		$menurename 		= $_POST['menurename'];
		$submenurename 		= $_POST['submenurename'];
		$menudisable 		= $_POST['menudisable'];
		$submenudisable 	= $_POST['submenudisable'];
	
		cs_update_option("cs_abbuaadmin_menuorder", $neworder);
		cs_update_option("cs_abbuaadmin_submenuorder", $newsuborder);
		cs_update_option("cs_abbuaadmin_menurename", $menurename);
		cs_update_option("cs_abbuaadmin_submenurename", $submenurename);
		cs_update_option("cs_abbuaadmin_menudisable", $menudisable);
		cs_update_option("cs_abbuaadmin_submenudisable", $submenudisable);
	
		/* Output Response
		   ========================================================================== */
		$response = array(
			'status'    => 'OK',
			'message'   => 'Settings saved'
		);
		echo json_encode($response);
		die();
	}
	function cs_abbua_menu_reset_callback() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cs-abbua-admin-nonce')) {
			die('Permissions check failed. Please login or refresh (if already logged in) the page, then try Again.');
		}
	
		$neworder 		= "";
		$newsuborder 	= "";
		$menurename 	= "";
		$submenurename 	= "";
		$menudisable 	= "";
		$submenudisable = "";
	
		cs_update_option("cs_abbuaadmin_menuorder", $neworder);
		cs_update_option("cs_abbuaadmin_submenuorder", $newsuborder);
		cs_update_option("cs_abbuaadmin_menurename", $menurename);
		cs_update_option("cs_abbuaadmin_submenurename", $submenurename);
		cs_update_option("cs_abbuaadmin_menudisable", $menudisable);
		cs_update_option("cs_abbuaadmin_submenudisable", $submenudisable);

		/* Output Response
		   ========================================================================== */
		   $response = array(
			'status'    => 'OK',
			'message'   => 'Settings updated'
		);
		echo json_encode($response);
		die();
	}


	/**
	 * Custom Login and Logout URLs
	 * @since 1.1.0
	 */
	private $wp_login_php;
	private $wp_logout_php;


	/**
	 * Custom Login Page, Login URL, Custom Login Redirect
	 *
	 * @wp-hook login_redirect
	 * @since 1.1.0
	 */
	function cs_abbua_login_redirect( $redirect_to, $request, $user){
		$login_redirect 		= cs_get_settings('login_security_custom_login_redirect_status');
		$login_redirect_roles 	= cs_get_settings('login_security_custom_login_redirect_roles');

		if ($login_redirect){
			$user_role = $user->roles[0];

			$url 	= $login_redirect_roles[$user_role];
			$status = $login_redirect_roles[$user_role .'_status'];

			if (isset($url) && isset($status)){
				$redirect_to = home_url($url);
			} else {
				$redirect_to = $redirect_to;
			}
		}
		return $redirect_to;
	}

	/**
	 * Custom Logout Page, Logout URL, Custom Logout Redirect
	 *
	 * @wp-hook logout_redirect
	 * @wp-hook logout_url
	 * @wp-hook wp_loaded
	 * 
	 * @since 1.1.0
	 */

	private function cs_abbua_logout_slug(){
		if (cs_get_settings('login_security_custom_logout_url_status')){
			$slug = cs_get_settings('login_security_custom_logout_slug');
	
			if (!$slug){
				$slug = 'abbua-admin-logout';
			}
			return $slug;
		} else {
			return '';
		}
	}
	private function cs_abbua_logout_slug_url(){
		$slug 	= $this->cs_abbua_logout_slug();
		$url 	= home_url($slug);

		return $url;
	}
	private function cs_abbua_logout_redirect_url(){
		$user = wp_get_current_user();
		$logout_redirect 		= cs_get_settings('login_security_custom_logout_redirect_status');
		$logout_redirect_roles 	= cs_get_settings('login_security_custom_logout_redirect_roles');

		if ($logout_redirect){
			$user_role = $user->roles[0];

			$url 	= $logout_redirect_roles[$user_role];
			$status = $logout_redirect_roles[$user_role .'_status'];

			if (isset($url) && isset($status)){
				$redirect_to = home_url($url);
			} else {
				$url = $this->cs_abbua_logout_slug_url();
				// $redirect_to = ($url) ? $url : 'wp-login.php';
				$redirect_to = 'wp-login.php';
				
				echo $url;
			}
			// die();
		}
		return $redirect_to;
	}
	
	// Logout Redirect URL
	function cs_abbua_logout_redirect( $redirect_to, $request, $user){
		$logout_redirect 		= cs_get_settings('login_security_custom_logout_redirect_status');
		$logout_redirect_roles 	= cs_get_settings('login_security_custom_logout_redirect_roles');

		if ($logout_redirect){
			$user_role = $user->roles[0];

			$url 	= $logout_redirect_roles[$user_role];
			$status = $logout_redirect_roles[$user_role .'_status'];

			if (isset($url) && isset($status)){
				$redirect_to = home_url($url);
			} else {
				$redirect_to = $redirect_to;
			}
		}
		return $redirect_to;
	}

	// Custom Logout URL
	function cs_abbua_logout_url( $logout_url, $redirect ) {
		$logout_url = $this->cs_abbua_logout_slug_url();
		$url 		= add_query_arg( 'action', 'logout', $logout_url );
		return $url;
	}
	function cs_abbua_logout_action(){
		if (!isset($_GET['action'])){
			return;
		}
		$request = parse_url( $_SERVER['REQUEST_URI'] );

		if (
			untrailingslashit( $request['path'] ) === home_url( $this->cs_abbua_logout_slug(), 'relative' ) || (
				! get_option( 'permalink_structure' ) &&
				isset( $_GET[$this->cs_abbua_logout_slug()] ) &&
				empty( $_GET[$this->cs_abbua_logout_slug()] )
				)
			) 
		{
			if ($this->wp_logout_php){
				wp_logout();
				wp_safe_redirect($this->cs_abbua_logout_redirect_url());
				die();
			} else {
				wp_logout();
				$logout_url = $this->cs_abbua_logout_slug_url();
				$url 		= add_query_arg( 'loggedout', 'true', $logout_url );
				wp_safe_redirect( $url );
				exit;

			}
		}
	}


	/**
	 * Filter WP Login
	 *
	 * @since 1.1.0
	 */

	// Helper Functions
	// ---------------------------------------------------------
	function cs_abbua_site_url( $url, $path, $scheme, $blog_id ) {
		return $this->filter_wp_login_php( $url, $scheme );
	}

	function cs_abbua_network_site_url( $url, $path, $scheme ) {
		return $this->filter_wp_login_php( $url, $scheme );
	}

	function cs_abbua_wp_redirect( $location, $status ) {
		return $this->filter_wp_login_php( $location );
	}

	private function use_trailing_slashes() {
		// return '/' === substr( get_option( 'permalink_structure' ), -1, 1 );
	}

	private function user_trailingslashit( $string ) {
		return $this->use_trailing_slashes() ? trailingslashit( $string ) : untrailingslashit( $string );
	}



	// General
	// ---------------------------------------------------------
	function filter_wp_login_php( $url, $scheme = null ) {
		if ( strpos( $url, 'wp-login.php' ) !== false ) {
			if ( is_ssl() ) {
				$scheme = 'https';
			}

			$args = explode( '?', $url );

			if ( isset( $args[1] ) ) {
				parse_str( $args[1], $args );
				if ( isset( $args['login'] ) ) {
					$args['login'] = rawurlencode( $args['login'] );
				}
				$url = add_query_arg( $args, $this->new_login_url( $scheme ) );
			} else {
				$url = $this->new_login_url( $scheme );
			}
		}
		return $url;
	}

	private function new_login_slug() {
		$slug = cs_get_settings('login_security_custom_login_slug');
		if (!$slug) {
			$slug = 'abbua-admin-login';
		}
		return $slug;
	}

	private function new_login_url( $scheme = null ) {
		if ( get_option( 'permalink_structure' ) ) {
			return $this->user_trailingslashit( home_url( '/', $scheme ) . $this->new_login_slug() );
		} else {
			return home_url( '/', $scheme ) . '?' . $this->new_login_slug();
		}
	}







	public function cs_abbua_plugins_loaded() {
		global $pagenow;

		if (
			! is_multisite() && (
				strpos( $_SERVER['REQUEST_URI'], 'wp-signup' ) !== false ||
				strpos( $_SERVER['REQUEST_URI'], 'wp-activate' ) !== false
			)
		) {
			wp_die( __( 'This feature is not enabled.', 'rename-wp-login' ) );
		}

		$request = parse_url( $_SERVER['REQUEST_URI'] );

		if ( (
				strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) !== false ||
				untrailingslashit( $request['path'] ) === site_url( 'wp-login', 'relative' )
			) &&
			! is_admin()
		) {
			$this->wp_login_php = true;
			$_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/' . str_repeat( '-/', 10 ) );
			$pagenow = 'index.php';
		} elseif (
			untrailingslashit( $request['path'] ) === home_url( $this->new_login_slug(), 'relative' ) || (
				! get_option( 'permalink_structure' ) &&
				isset( $_GET[$this->new_login_slug()] ) &&
				empty( $_GET[$this->new_login_slug()] )
		) ) {
			$pagenow = 'wp-login.php';
		}  elseif ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-register.php' ) !== false
			|| untrailingslashit( $request['path'] ) === site_url( 'wp-register', 'relative' ) )
			&& ! is_admin() ) {

				$this->wp_login_php = true;

				$_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/' . str_repeat( '-/', 10 ) );

				$pagenow = 'index.php';
			}

		
		// LOGOUT CUSTOM URL PAGE 
		if (
			untrailingslashit( $request['path'] ) === home_url( $this->cs_abbua_logout_slug(), 'relative' ) || (
				! get_option( 'permalink_structure' ) &&
				isset( $_GET[$this->cs_abbua_logout_slug()] ) &&
				empty( $_GET[$this->cs_abbua_logout_slug()] )
		)) {
			$logout_redirect = cs_get_settings('login_security_custom_logout_redirect_status');
			if (!$logout_redirect){
				$pagenow = 'wp-login.php';
				$this->wp_logout_php = false;
			} else {
				$this->wp_logout_php = true;
			}
		}
	}

	public function cs_abbua_login_action() {
		global $pagenow;

		if ( is_admin() && ! is_user_logged_in() && ! defined( 'DOING_AJAX' ) && $pagenow !== 'admin-post.php' && ( isset( $_GET ) && empty( $_GET['adminhash'] ) && $request['path'] !== '/wp-admin/options.php' ) ) {
			wp_safe_redirect( 
				home_url( $this->user_trailingslashit( $this->new_login_slug() ) . '?redirect_to='.admin_url().'&reauth=1')
			);
			die();
		}

		$request = parse_url( $_SERVER['REQUEST_URI'] );

		if (
			$pagenow === 'wp-login.php' &&
			$request['path'] !== $this->user_trailingslashit( $request['path'] ) &&
			get_option( 'permalink_structure' )
		) {
			wp_safe_redirect( $this->user_trailingslashit( $this->new_login_url() ) . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );
			die;
		} elseif ( $this->wp_login_php ) {
			if (
				( $referer = wp_get_referer() ) &&
				strpos( $referer, 'wp-activate.php' ) !== false &&
				( $referer = parse_url( $referer ) ) &&
				! empty( $referer['query'] )
			) {
				parse_str( $referer['query'], $referer );

				if (
					! empty( $referer['key'] ) &&
					( $result = wpmu_activate_signup( $referer['key'] ) ) &&
					is_wp_error( $result ) && (
						$result->get_error_code() === 'already_active' ||
						$result->get_error_code() === 'blog_taken'
				) ) {
					wp_safe_redirect( $this->new_login_url() . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );
					die;
				}
			}

			$this->wp_template_loader();
		} elseif ( $pagenow === 'wp-login.php' ) {
			global $errors, $error, $interim_login, $action, $user_login;

			if ( is_user_logged_in() && ! isset( $_REQUEST['action'] ) ) {
				wp_safe_redirect( admin_url() );
				die();
			}

			@require_once ABSPATH . 'wp-login.php';

			die;

		}
	}
	private function wp_template_loader() {
		global $pagenow;

		$pagenow = 'index.php';

		if ( ! defined( 'WP_USE_THEMES' ) ) {
			define( 'WP_USE_THEMES', true );
		}

		wp();

		if ( $_SERVER['REQUEST_URI'] === $this->user_trailingslashit( str_repeat( '-/', 10 ) ) ) {
			$_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/wp-login-php/' );
		}

		require_once( ABSPATH . WPINC . '/template-loader.php' );

		die;
	}



	/**
	 * Update Welcome Email with the new login url
	 *
	 * @since 1.1.0
	 */
	public function cs_abbua_login_welcome_email($value){
		return $value = str_replace( 'wp-login.php', trailingslashit( get_site_option( 'whl_page', 'login' ) ), $value );
	}


	/**
	 * Update redirect for Woocommerce email notification
	 * @since 1.1.0
	 */
	public function cs_abbua_hide_login_redirect_page_email_notif_woocommerce() {
		if (!class_exists('WC_Form_Handler')){
			return false;
		}
		if (!empty( $_GET ) && isset( $_GET['action'] ) && 'rp' === $_GET['action'] && isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {
			wp_redirect( $this->new_login_url() );
			exit();
		}
	}


	/**
	 *
	 * Update url redirect : wp-admin/options.php
	 *
	 * @param $login_url
	 * @param $redirect
	 * @param $force_reauth
	 *
	 * @return string
	 * @since 1.1.0
	 */
	public function cs_abbua_login_url( $login_url, $redirect, $force_reauth ) {
		if ( $force_reauth === false ) {
			return $login_url;
		}
		if ( empty( $redirect ) ) {
			return $login_url;
		}
		$redirect = explode( '?', $redirect );

		if ( $redirect[0] === admin_url( 'options.php' ) ) {
			$login_url = admin_url();
		}
		return $login_url;
	}
}