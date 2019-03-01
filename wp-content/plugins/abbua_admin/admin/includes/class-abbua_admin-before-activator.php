<?php
class Abbua_admin_Before_Activator {
    
    /**
    * Autoload plugin settings
    *
    * @since    1.0.0
    */
    public static function activate() {
        $default_settings = array (
            'theme' => 'abbua',
            'theme_settings' => 
            array (
                'abbua_theme-abbua' => 
                array (
                    'abbua_theme-abbua__style' => 'style1',
                    'abbua_theme-abbua__primary' => 
                    array (
                        'normal' => '#749da5',
                        'light' => '#8daeb5',
                    ),
                    'abbua_theme-abbua__accent' => '#749da5',
                ),
                'abbua_theme-default' => 
                array (
                    'abbua_theme-default__style' => 'style1',
                    'abbua_theme-default__primary' => 
                    array (
                        'normal' => 'rgb(0, 168, 255)',
                        'light' => 'rgb(179, 229, 252)',
                    ),
                    'abbua_theme-default__accent' => 'rgb(255, 120, 0)',
                ),
                'abbua_theme-ebony' => 
                array (
                    'abbua_theme-ebony__style' => 'style1',
                    'abbua_theme-ebony__primary' => 
                    array (
                        'normal' => '#518c54',
                        'light' => '#93af95',
                    ),
                    'abbua_theme-ebony__accent' => '#f2545b',
                ),
                'abbua_theme-forgedsteel' => 
                array (
                    'abbua_theme-forgedsteel__style' => 'style1',
                    'abbua_theme-forgedsteel__primary' => 
                    array (
                        'normal' => '#d64933',
                        'light' => '#d95945',
                    ),
                    'abbua_theme-forgedsteel__accent' => '#e8c547',
                ),
                'abbua_theme-oasis' => 
                array (
                    'abbua_theme-oasis__style' => 'style1',
                    'abbua_theme-oasis__primary' => 
                    array (
                        'normal' => '#78a1bb',
                        'light' => '#a9c3d3',
                    ),
                    'abbua_theme-oasis__accent' => '#dda161',
                ),
                'abbua_theme-onyx' => 
                array (
                    'abbua_theme-onyx__style' => 'style1',
                    'abbua_theme-onyx__primary' => 
                    array (
                        'normal' => '#b48eae',
                        'light' => '#d6c1d2',
                    ),
                    'abbua_theme-onyx__accent' => 'rgb(235, 130, 88)',
                ),
            ),
            'logo_url' => 'admin_url',
            'logo_type' => 'text',
            'logo_type_image_fs' => 
            array (
                'logo_image' => '',
                'logo_image_collapsed' => '',
            ),
            'logo_type_text_fs' => 
            array (
                'logo_icon' => 'fa fa-diamond',
                'logo_text' => 'ABBUA ADMIN <small>White label WordPress Admin Theme</small>',
            ),
            'logo_image_login' => '',
            'logo_devices_fs' => 
            array (
                'logo_favicon' => '',
                'logo_apple' => '',
                'logo_android' => '',
            ),
            'login_page_status' => true,
            'login_page_background_image' => 'background-0',
            'login_page_background' => 
            array (
                'color' => '#ffbc00',
            ),
            'login_page_loginbox_style' => 'boxed',
            'login_page_loginbox_background_style' => 'frozen-glass',
            'login_page_loginbox_background' => 'rgba(0,0,0,0.5)',
            'login_page_title_status' => true,
            'login_page_title' => 'ABBUA Admin - Whitelabel WordPress Admin Theme',
            'login_logo_url' => '',
            'login_logo_url_title_status' => true,
            'login_logo_url_title' => 'ABBUA Admin for WordPress',
            'login_page_login_message_fs' => 
            array (
                'login_page_login_message_color' => 'rgba(255,255,255,0.8)',
                'login_page_login_message' => 'Welcome back to ABBUA Admin. Please login using the user credentials below:
                <strong>Username:</strong> demo <strong>Password:</strong> demo',
            ),
            'login_page_logout_message' => 'Now you\'re out',
            'login_page_rememberme' => 'Keep session active',
            'login_page_loginbutton_fs' => 
            array (
                'login_page_loginbutton' => 'Test ABBUA Admin now!',
                'login_page_loginbutton_background' => 
                array (
                    'regular' => '#8E24AA',
                    'hover' => '#9C27B0',
                    'active' => '#7B1FA2',
                ),
                'login_page_loginbutton_color' => 
                array (
                    'regular' => 'rgba(255,255,255,0.7)',
                    'hover' => 'rgba(255,255,255,0.9)',
                    'active' => 'rgba(255,255,255,0.7)',
                ),
            ),
            'login_page_link_back_fs' => 
            array (
                'login_page_link_back' => 'Go to Homepage',
            ),
            'login_page_link_lostpassword_fs' => 
            array (
                'login_page_link_lostpassword' => 'Lost your password? Click here',
            ),
            'login_page_invalid_username' => '<strong>ERROR</strong>: Invalid username.',
            'login_page_invalid_password' => '<strong>ERROR</strong>: The password you entered is incorrect.',
            'page_loader_status' => true,
            'page_loader_custom_colors_fs' => 
            array (
                'page_loader_color_primary' => '#9C27B0',
                'page_loader_color_secondary' => '#E1BEE7',
            ),
            'page_loader_theme' => 'theme-2',
            'navbar_link_site' => true,
            'navbar_link_updates' => true,
            'navbar_link_comments' => true,
            'navbar_link_addnew' => true,
            'navbar_link_profile' => true,
            'navbar_position' => 'fixed',
            'sidebar_accordion' => true,
            'sidebar_scrollbar' => true,
            'sidebar_brand_position' => 'fixed',
            'sidebar_position' => 'fixed',
            'footer_text_status' => true,
            'footer_text_fs' => 
            array (
                'footer_text' => 'ABBUA Admin Powered by <a href="http://www.castorstudio.com" target="_blank" rel="noopener">CastorStudio</a>',
            ),
            'footer_version_status' => true,
            'footer_version_fs' => 
            array (
                'footer_version' => '<a href="http://www.castorstudio.com/abbua-admin-wordpress-white-label-admin-theme" target="_blank" rel="noopener">v1.0.0</a>',
            ),
            'customcss' => '',
            'import' => '',
            'logo_favicon_status' => false,
            'logo_apple_status' => false,
            'logo_android_status' => false,
            'login_logo_url_status' => false,
            'login_page_login_message_status' => false,
            'login_page_logout_message_status' => false,
            'login_page_rememberme_status' => false,
            'login_page_loginbutton_status' => false,
            'login_page_link_back_status' => false,
            'login_page_link_lostpassword_status' => false,
            'login_page_error_shake' => false,
            'login_page_invalid_username_status' => false,
            'login_page_invalid_password_status' => false,
            'page_loader_custom_colors_status' => false,
            'user_profile_status' => false,
            'user_profile_options' => false,
            'sidebar_status' => false,
            'customcss_status' => false,
            'resetsettings_status' => false,
            'rightnowwidget_status' => false,
        );
        
        if (!get_option('cs_abbuaadmin_settings')){
            add_option('cs_abbuaadmin_settings',$default_settings);
        }
    }
    
}