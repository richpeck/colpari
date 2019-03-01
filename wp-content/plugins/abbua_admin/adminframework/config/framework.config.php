<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
// ===============================================================================================
// -----------------------------------------------------------------------------------------------
// FRAMEWORK SETTINGS
// -----------------------------------------------------------------------------------------------
// ===============================================================================================
$settings = array(
    'menu_title'            => __('ABBUA Admin Settings','abbua_admin'),
    'menu_type'             => 'menu', // menu, submenu, options, theme, etc.
    'menu_slug'             => 'cs-abbua-admin-settings',
    'ajax_save'             => false,
    'show_reset_all'        => false,
    'framework_title'       => __('ABBUA Admin Settings','abbua_admin'),
    'framework_subtitle'    => __('by CastorStudio','abbua_admin'),
);

// ===============================================================================================
// -----------------------------------------------------------------------------------------------
// FRAMEWORK OPTIONS
// -----------------------------------------------------------------------------------------------
// ===============================================================================================
$options        = array();

/* ===============================================================================================
    PICK THEME
   =============================================================================================== */
$options[]      = array(
    'name'        => 'theme',
    'title'       => __('Themes','abbua_admin'),
    'icon'        => 'fei fei-layout',
    
    // begin: fields
    'fields'      => array(
        array(
            'type'    => 'heading',
            'content' => __('Choose a Theme','abbua_admin'),
        ),
        array(
            'id'			=> 'theme',
            'type'			=> 'image_select',
            'title'			=> __('Theme','abbua_admin'),
            'radio'			=> true,
            'options'		=> Abbua_admin_Admin::cs_abbua_get_themes(),
            'default'   	=> 'default',
        ),
        array(
            'id'			=> 'theme_settings',
            'type'			=> 'fieldset',
            'fields'		=> Abbua_admin_Admin::cs_abbua_get_dynamic_settings(),
        ),
        
    ), // end: fields
);


/* ===============================================================================================
    LOGO SETTINGS
   =============================================================================================== */
$options[]      = array(
    'name'        => 'logo',
    'title'       => __('Logo Settings','abbua_admin'),
    'icon'        => 'fei fei-image',
    
    // begin: fields
    'fields'      => array(
        array(
            'type'    => 'heading',
            'content' => __('Logo Settings','abbua_admin'),
        ),
        array(
            'id'            => 'logo_url',
            'type'          => 'text',
            'title'         => __('Logo URL','abbua_admin'),
            'desc'          => __('User will be redirected to this mentioned url when clicking the logo.','abbua_admin'),
            'after'         => __('<p class="cs-text-muted">If you need to redirect to the wordpress admin area, use: "admin_url" without quotes</p>','abbua_admin'),
            'default'       => 'admin_url',
        ),
        array(
            'id'            => 'logo_type',
            'type'          => 'image_select',
            'title'         => __('Admin Logo Type','abbua_admin'),
            'desc'          => __('Choose a logo style to use in the admin area','abbua_admin'),
            'options'       => array(
                'image'     => CS_PLUGIN_URI .'/adminframework/assets/images/logo-type-image.png',
                'text'      => CS_PLUGIN_URI .'/adminframework/assets/images/logo-type-text.png',
            ),
            'radio'         => true,
            'default'       => 'text'
        ),

        // Logo Image
        // -----------------------------------------------------------------
        array(
            'dependency'    => array('logo_type_image','==','true'),
            'id'            => 'logo_type_image_fs',
            'type'          => 'fieldset',
            'fields'        => array(
                array(
                    'id'            => 'logo_image',
                    'type'          => 'image',
                    'title'         => __('Logo Image','abbua_admin'),
                    'desc'          => __('Upload your own logo of 200px * 44px (width*height)','abbua_admin'),
                    'settings'      => array(
                        'button_title' => __('Choose Logo','abbua_admin'),
                        'frame_title'  => __('Choose an image','abbua_admin'),
                        'insert_title' => __('Use this logo','abbua_admin'),
                    ),
                ),
                array(
                    'id'            => 'logo_image_collapsed',
                    'type'          => 'image',
                    'title'         => __('Logo Image Collapsed Menu','abbua_admin'),
                    'desc'          => __('Upload your own logo of 44px * 44px (width*height)','abbua_admin'),
                    'settings'      => array(
                        'button_title' => __('Choose Logo','abbua_admin'),
                        'frame_title'  => __('Choose an image','abbua_admin'),
                        'insert_title' => __('Use this logo','abbua_admin'),
                    ),
                ),
            ),
        ),

        // Logo Text
        // -----------------------------------------------------------------
        array(
            'dependency'    => array('logo_type_text','==','true'),
            'id'            => 'logo_type_text_fs',
            'type'          => 'fieldset',
            'fields'        => array(
                array(
                    'id'            => 'logo_icon',
                    'type'          => 'icon',
                    'title'         => __('Logo Icon','abbua_admin'),
                    'desc'          => __('Choose an icon for the logo','abbua_admin'),
                    'default'       => 'fa fa-diamond',
                ),
                array(
                    'id'            => 'logo_text',
                    'type'          => 'text',
                    'title'         => __('Logo Text','abbua_admin'),
                    'desc'          => __('Enter the text to use in the logo','abbua_admin'),
                    'default'       => __('ABBUA ADMIN <small>White label WordPress Admin Theme</small>','abbua_admin'),
                ),
            ),
        ),


        // Login Screen Logo
        // -----------------------------------------------------------------
        array(
            'id'            => 'logo_image_login',
            'type'          => 'image',
            'title'         => __('Login Page Logo','abbua_admin'),
            'desc'          => __('Upload your own logo of 350px * 100px (width*height).','abbua_admin'),
            'settings'      => array(
                'button_title' => __('Choose Logo','abbua_admin'),
                'frame_title'  => __('Choose an image','abbua_admin'),
                'insert_title' => __('Use this logo','abbua_admin'),
            ),
        ),

        array(
            'id'            => 'logo_favicon_status',
            'type'          => 'switcher',
            'title'         => __('Favicon Logo','abbua_admin'),
            'label'         => __('Use custom favicon for admin area','abbua_admin'),
            'labels'        => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'dependency'    => array('logo_favicon_status','==','true'),
            'id'            => 'logo_devices_fs',
            'type'          => 'fieldset',
            'fields'        => array(
                array(
                    'type'    		=> 'info',
                    'title'         => __('Notice','abbua_admin'),
                    'content'		=> __('We\'ll automatically generate 3 different favicon sizes: 16x16px, 32x32px, 96x96px. <br> To get the best results, upload an image of at least 96x96 pixels.','abbua_admin'),
                ),
                array(
                    'id'            => 'logo_favicon',
                    'type'          => 'image',
                    'title'         => __('Favicon Logo Image','abbua_admin'),
                    'desc'          => __('Upload an image to use as a favicon','abbua_admin'),
                    'settings'       => array(
                        'button_title' => __('Choose Logo','abbua_admin'),
                        'frame_title'  => __('Choose an image to use as a Favicon','abbua_admin'),
                        'insert_title' => __('Use this logo','abbua_admin'),
                    ),
                ),
            ),
        ),

        array(
            'id'            => 'logo_apple_status',
            'type'          => 'switcher',
            'title'         => __('Apple Devices Logo','abbua_admin'),
            'label'         => __('Use custom logo for Apple devices','abbua_admin'),
            'labels'        => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'dependency'    => array('logo_apple_status','==','true'),
            'id'            => 'logo_devices_fs',
            'type'          => 'fieldset',
            'fields'        => array(
                array(
                    'type'    		=> 'info',
                    'title'         => __('Notice','abbua_admin'),
                    'content'		=> __('We\'ll automatically generate 9 different device icon sizes: 57x57px, 60x60px, 72x72px, 76x76px, 114x114px, 120x120px, 144x144px, 152x152px, 180x180px. <br> To get the best results, upload an image of at least 180x180 pixels.','abbua_admin'),
                ),
                array(
                    'id'            => 'logo_apple',
                    'type'          => 'image',
                    'title'         => __('Apple Devices Logo Image','abbua_admin'),
                    'desc'          => __('Upload an image to use as a logo for Apple devices','abbua_admin'),
                    'settings'       => array(
                        'button_title' => __('Choose Logo','abbua_admin'),
                        'frame_title'  => __('Choose an image to use as a Apple devices logo','abbua_admin'),
                        'insert_title' => __('Use this logo','abbua_admin'),
                    ),
                ),
            ),
        ),

        array(
            'id'            => 'logo_android_status',
            'type'          => 'switcher',
            'title'         => __('Android Devices Logo','abbua_admin'),
            'label'         => __('Use custom logo for Android devices','abbua_admin'),
            'labels'        => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'dependency'    => array('logo_android_status','==','true'),
            'id'            => 'logo_devices_fs',
            'type'          => 'fieldset',
            'fields'        => array(
                array(
                    'type'    		=> 'info',
                    'title'         => __('Notice','abbua_admin'),
                    'content'		=> __('We\'ll automatically generate 6 different device icon sizes: 36x36px, 48x48px, 72x72px, 96x96px, 144x144px, 192x192px. <br> To get the best results, upload an image of at least 192x192 pixels.','abbua_admin'),
                ),
                array(
                    'id'            => 'logo_android',
                    'type'          => 'image',
                    'title'         => __('Android Devices Logo Image','abbua_admin'),
                    'desc'          => __('Upload an image to use as a logo for Android devices','abbua_admin'),
                    'settings'       => array(
                        'button_title' => __('Choose Logo','abbua_admin'),
                        'frame_title'  => __('Choose an image to use as a Android devices logo','abbua_admin'),
                        'insert_title' => __('Use this logo','abbua_admin'),
                    ),
                ),
            ),
        ),
    ), // end: fields
);


/* ===============================================================================================
    LOGIN PAGE
   =============================================================================================== */
$options[]      = array(
    'name'        => 'login_page',
    'title'       => __('Login Page','abbua_admin'),
    'icon'        => 'fei fei-log-in',
    
    // begin: fields
    'fields'      => array(
        array(
            'type'    => 'heading',
            'content' => __('Login Page','abbua_admin'),
        ),

        array(
            'id'        => 'login_page_status',
            'type'      => 'switcher',
            'title'     => __('Login Page','abbua_admin'),
            'label'     => __('Use custom login page theme','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
            'default'   => true
        ),
        array(
            'id'        => 'login_page_background_image',
            'type'      => 'image_select',
            'title'     => __('Background Image','abbua_admin'),
            'options'   => array(
                'background-custom' => CS_PLUGIN_URI .'/adminframework/assets/images/background-login-custom.png',
                'background-0'      => CS_PLUGIN_URI .'/adminframework/assets/images/background-login-0.png',
                'background-1'      => CS_PLUGIN_URI .'/adminframework/assets/images/background-login-1.png',
                'background-2'      => CS_PLUGIN_URI .'/adminframework/assets/images/background-login-2.png',
                'background-3'      => CS_PLUGIN_URI .'/adminframework/assets/images/background-login-3.png',
                'background-4'      => CS_PLUGIN_URI .'/adminframework/assets/images/background-login-4.png',
                'background-5'      => CS_PLUGIN_URI .'/adminframework/assets/images/background-login-5.png',
                'background-6'      => CS_PLUGIN_URI .'/adminframework/assets/images/background-login-6.png',
                'background-7'      => CS_PLUGIN_URI .'/adminframework/assets/images/background-login-7.png',
                'background-8'      => CS_PLUGIN_URI .'/adminframework/assets/images/background-login-8.png',
                'background-9'      => CS_PLUGIN_URI .'/adminframework/assets/images/background-login-9.png',
                'background-10'     => CS_PLUGIN_URI .'/adminframework/assets/images/background-login-10.png',
            ),
            'radio'     => true,
            'default'   => 'background-0',
        ),
        array(
            'dependency'    => array('login_page_background_image_background-custom','==','true'),
            'id'            => 'login_page_background',
            'type'          => 'background',
            'title'         => __('Custom Background Image','abbua_admin'),
            'desc'          => __('Background image, color and settings etc. for login page (Eg: http://www.yourdomain.com/wp-login.php)','abbua_admin'),
            'settings'       => array(
                'button_title' => __('Choose Background','abbua_admin'),
                'frame_title'  => __('Choose an image to use as a login background','abbua_admin'),
                'insert_title' => __('Use this background image','abbua_admin'),
            ),
            'options'       => array(
                'repeat'        => false,
                'position'      => false,
                'attachment'    => false,
                'size'          => false,
            ),
            'default'       => array(
                'image'      => 'wp-content/uploads/your-background-image.jpeg',
                'repeat'     => 'repeat-x',
                'position'   => 'center center',
                'attachment' => 'fixed',
                'color'      => '#ffbc00',
            ),
            'wrap_class'	=> 'cs-field-subfield',
        ),
        array(
            'id'        => 'login_page_loginbox_style',
            'type'      => 'image_select',
            'title'     => __('Loginbox Style','abbua_admin'),
            'options'   => array(
                'fullheight'    => CS_PLUGIN_URI .'/adminframework/assets/images/login-box-fullheight.png',
                'boxed'         => CS_PLUGIN_URI .'/adminframework/assets/images/login-box-boxed.png',
            ),
            'radio'     => true,
            'default'   => 'boxed',
        ),
        array(
            'id'        => 'login_page_loginbox_background_style',
            'type'      => 'image_select',
            'title'     => __('Loginbox Background Style','abbua_admin'),
            'options'   => array(
                'frozen-glass'  => CS_PLUGIN_URI .'/adminframework/assets/images/login-box-style-frozenglass.png',
                'normal'        => CS_PLUGIN_URI .'/adminframework/assets/images/login-box-style-normal.png',
            ),
            'radio'     => true,
            'default'   => 'frozen-glass',
        ),
        array(
            'id'        => 'login_page_loginbox_background',
            'type'      => 'color_picker',
            'title'     => __('Loginbox Background Color','abbua_admin'),
            'default'   => 'rgba(0,0,0,0.5)',
        ),
        array(
            'id'            => 'login_page_title_status',
            'type'          => 'switcher',
            'title'         => __('Page Title','abbua_admin'),
            'label'         => __('Use custom login page title','abbua_admin'),
            'labels'        => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
            'default'       => true
        ),
        array(
            'dependency'    => array('login_page_title_status','==','true'),
            'id'            => 'login_page_title',
            'type'          => 'text',
            'title'         => __('Page Title Text','abbua_admin'),
            'desc'          => __('This is the "title" meta tag.','abbua_admin'),
            'default'       => __('ABBUA Admin - Whitelabel WordPress Admin Theme','abbua_admin'),
            'wrap_class'	=> 'cs-field-subfield',
        ),

        array(
            'id'            => 'login_logo_url_status',
            'type'          => 'switcher',
            'title'         => __('Logo URL','abbua_admin'),
            'label'         => __('Use custom login logo url','abbua_admin'),
            'labels'        => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'dependency'    => array('login_logo_url_status','==','true'),
            'id'            => 'login_logo_url',
            'type'          => 'text',
            'title'         => __('Logo URL','abbua_admin'),
            'desc'          => __('This is the URL to which the logo points','abbua_admin'),
            'after'         => __('<p class="cs-text-muted">By default this url is your bloginfo url.</p>','abbua_admin'),
            'wrap_class'	=> 'cs-field-subfield',
        ),

        array(
            'id'            => 'login_logo_url_title_status',
            'type'          => 'switcher',
            'title'         => __('Logo Title','abbua_admin'),
            'label'         => __('Use custom logo title','abbua_admin'),
            'labels'        => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'dependency'    => array('login_logo_url_title_status','==','true'),
            'id'            => 'login_logo_url_title',
            'type'          => 'text',
            'title'         => __('Logo Title Text','abbua_admin'),
            'desc'          => __('This is simply ALT text for the logo.','abbua_admin'),
            'wrap_class'	=> 'cs-field-subfield',
        ),

        array(
            'id'            => 'login_page_login_message_status',
            'type'          => 'switcher',
            'title'         => __('Login Message','abbua_admin'),
            'label'         => __('Use custom login message','abbua_admin'),
            'labels'        => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'dependency'    => array('login_page_login_message_status','==','true'),
            'id'            => 'login_page_login_message_fs',
            'type'          => 'fieldset',
            'fields'        => array(
                array(
                    'id'            => 'login_page_login_message_style',
                    'type'          => 'switcher',
                    'title'         => __('Message Box Style','abbua_admin'),
                    'label'         => __('Apply frozen glass style to the message box','abbua_admin'),
                    'labels'        => array(
                        'on'    => __('Yes','abbua_admin'),
                        'off'   => __('No','abbua_admin'),
                    ),
                ),
                array(
                    'id'            => 'login_page_login_message_color',
                    'type'          => 'color_picker',
                    'title'         => __('Login Message Text Color','abbua_admin'),
                    'default'       => 'rgba(255,255,255,0.8)',
                ),
                array(
                    'id'            => 'login_page_login_message',
                    'type'          => 'wysiwyg',
                    'title'         => __('Login Message Text','abbua_admin'),
                    'desc'          => __('Enter a custom text to show on the login screen. HTML markup can be used.','abbua_admin'),
                    'default'       => __('Welcome back to ABBUA Admin. Please login using the user credentials below:<br><strong>Username:</strong> demo <strong>Password:</strong> demo','abbua_admin'),
                    'settings'      => array(
                        'textarea_rows' => 5,
                        'tinymce'       => true,
                        'media_buttons' => false,
                        'quicktags'     => false,
                        'teeny'         => true,
                    ),
                ),
            ),
        ),

        array(
            'id'            => 'login_page_logout_message_status',
            'type'          => 'switcher',
            'title'         => __('Logout Message','abbua_admin'),
            'label'         => __('Use custom logout message','abbua_admin'),
            'labels'        => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'dependency'    => array('login_page_logout_message_status','==','true'),
            'id'            => 'login_page_loginbutton_fs',
            'type'          => 'fieldset',
            'fields'        => array(
                array(
                    'id'            => 'login_page_logout_message_visibility',
                    'type'          => 'switcher',
                    'title'         => __('Message Visibility','abbua_admin'),
                    'label'         => __('Hide loggedout message','abbua_admin'),
                    'labels'        => array(
                        'on'    => __('Yes','abbua_admin'),
                        'off'   => __('No','abbua_admin'),
                    ),
                ),
                array(
                    'id'            => 'login_page_logout_message',
                    'type'          => 'wysiwyg',
                    'title'         => __('Logout Message Text','abbua_admin'),
                    'desc'          => __('Enter a text to show on the logout screen. HTML markup can be used.','abbua_admin'),
                    'default'       => __('Now you\'re out','abbua_admin'),
                    'settings'      => array(
                        'textarea_rows' => 5,
                        'tinymce'       => true,
                        'media_buttons' => false,
                        'quicktags'     => false,
                        'teeny'         => true,
                    ),
                ),
            ),
        ),

        array(
            'id'            => 'login_page_rememberme_status',
            'type'          => 'switcher',
            'title'         => __('Remember Me','abbua_admin'),
            'label'         => __('Use custom "Remember Me" text','abbua_admin'),
            'labels'        => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'dependency'    => array('login_page_rememberme_status','==','true'),
            'id'            => 'login_page_rememberme',
            'type'          => 'text',
            'title'         => __('Remember Me Text','abbua_admin'),
            'default'       => __('Keep session active','abbua_admin'),
            'wrap_class'	=> 'cs-field-subfield',
        ),

        array(
            'id'            => 'login_page_loginbutton_status',
            'type'          => 'switcher',
            'title'         => __('Login Button','abbua_admin'),
            'label'         => __('Use custom login button style','abbua_admin'),
            'labels'        => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'dependency'    => array('login_page_loginbutton_status','==','true'),
            'id'            => 'login_page_loginbutton_fs',
            'type'          => 'fieldset',
            'fields'        => array(
                array(
                    'id'            => 'login_page_loginbutton',
                    'type'          => 'text',
                    'title'         => __('Button Text','abbua_admin'),
                    'default'       => __('Test ABBUA Admin now!','abbua_admin'),
                ),
                array(
                    'id'            => 'login_page_loginbutton_background_status',
                    'type'          => 'switcher',
                    'title'         => __('Custom Button Colors','abbua_admin'),
                    'label'         => __('Use custom button colors','abbua_admin'),
                    'labels'        => array(
                        'on'    => __('Yes','abbua_admin'),
                        'off'   => __('No','abbua_admin'),
                    ),
                ),
                array(
                    'dependency'    => array('login_page_loginbutton_background_status','==','true'),
                    'id'            => 'login_page_loginbutton_background',
                    'type'          => 'color_link',
                    'title'         => __('Button Background Color','abbua_admin'),
                    'options'       => array(
                        'regular'   => true,
                        'hover'     => true,
                        'active'    => true,
                    ),
                    'default'       => array(
                        'regular'   => '#8E24AA',
                        'hover'     => '#9C27B0',
                        'active'    => '#7B1FA2',
                    )
                ),
                array(
                    'dependency'    => array('login_page_loginbutton_background_status','==','true'),
                    'id'            => 'login_page_loginbutton_color',
                    'type'          => 'color_link',
                    'title'         => __('Button Text Color','abbua_admin'),
                    'options'       => array(
                        'regular'   => true,
                        'hover'     => true,
                        'active'    => true,
                    ),
                    'default'       => array(
                        'regular'   => 'rgba(255,255,255,0.7)',
                        'hover'     => 'rgba(255,255,255,0.9)',
                        'active'    => 'rgba(255,255,255,0.7)',
                    )
                ),
            ),
        ),

        array(
            'id'            => 'login_page_link_back_status',
            'type'          => 'switcher',
            'title'         => __('Back to main site link','abbua_admin'),
            'label'         => __('Use custom link options','abbua_admin'),
            'labels'        => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'dependency'    => array('login_page_link_back_status','==','true'),
            'id'            => 'login_page_link_back_fs',
            'type'          => 'fieldset',
            'fields'        => array(
                array(
                    'id'            => 'login_page_link_back_visibility',
                    'type'          => 'switcher',
                    'title'         => __('Link Visibility','abbua_admin'),
                    'label'         => __('Hide "Back to main site" link','abbua_admin'),
                    'labels'        => array(
                        'on'    => __('Yes','abbua_admin'),
                        'off'   => __('No','abbua_admin'),
                    ),
                ),
                array(
                    'id'            => 'login_page_link_back',
                    'type'          => 'text',
                    'title'         => __('Link Text','abbua_admin'),
                    'after'         => __('<p class="cs-text-muted">Use %s as a site name wildcard</p>','abbua_admin'),
                    'default'       => __('Go to Homepage','abbua_admin'),
                ),
            )
        ),
        
        array(
            'id'            => 'login_page_link_lostpassword_status',
            'type'          => 'switcher',
            'title'         => __('Lost your password? link','abbua_admin'),
            'label'         => __('Use custom link options','abbua_admin'),
            'labels'        => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'dependency'    => array('login_page_link_lostpassword_status','==','true'),
            'id'            => 'login_page_link_lostpassword_fs',
            'type'          => 'fieldset',
            'fields'        => array(
                array(
                    'id'            => 'login_page_link_lostpassword_visibility',
                    'type'          => 'switcher',
                    'title'         => __('Link Visibility','abbua_admin'),
                    'label'         => __('Hide "Lost your password?" link','abbua_admin'),
                    'labels'        => array(
                        'on'    => __('Yes','abbua_admin'),
                        'off'   => __('No','abbua_admin'),
                    ),
                ),
                array(
                    'id'            => 'login_page_link_lostpassword',
                    'type'          => 'text',
                    'title'         => __('Link Text','abbua_admin'),
                    'default'       => __('Lost your password? Click here','abbua_admin'),
                ),
            )
        ),

        array(
            'id'        => 'login_page_error_shake',
            'type'      => 'switcher',
            'title'     => __('Login Error Shake','abbua_admin'),
            'desc'      => __('When you enter an incorrect username or password, the login form shakes to alert the user they need to try again.','abbua_admin'),
            'label'     => __('Remove the error shake effect','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),

        array(
            'id'            => 'login_page_invalid_username_status',
            'type'          => 'switcher',
            'title'         => __('Invalid Username Message','abbua_admin'),
            'label'         => __('Use custom invalid username message','abbua_admin'),
            'labels'        => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'dependency'    => array('login_page_invalid_username_status','==','true'),
            'id'            => 'login_page_invalid_username',
            'type'          => 'wysiwyg',
            'title'         => __('Invalid Username Message Text','abbua_admin'),
            'desc'          => __('Enter a text to show when entering an incorrect username. HTML markup can be used.','abbua_admin'),
            'default'       => __('<strong>ERROR</strong>: Invalid username.','abbua_admin'),
            'settings'      => array(
                'textarea_rows' => 5,
                'tinymce'       => true,
                'media_buttons' => false,
                'quicktags'     => false,
                'teeny'         => true,
            ),
            'wrap_class'	=> 'cs-field-subfield',
        ),

        array(
            'id'            => 'login_page_invalid_password_status',
            'type'          => 'switcher',
            'title'         => __('Invalid Password Message','abbua_admin'),
            'label'         => __('Use custom invalid password message','abbua_admin'),
            'labels'        => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'dependency'    => array('login_page_invalid_password_status','==','true'),
            'id'            => 'login_page_invalid_password',
            'type'          => 'wysiwyg',
            'title'         => __('Invalid Password Message Text','abbua_admin'),
            'desc'          => __('Enter a text to show when entering an incorrect password. HTML markup can be used.','abbua_admin'),
            'default'       => __('<strong>ERROR</strong>: The password you entered is incorrect.','abbua_admin'),
            'settings'      => array(
                'textarea_rows' => 5,
                'tinymce'       => true,
                'media_buttons' => false,
                'quicktags'     => false,
                'teeny'         => true,
            ),
            'wrap_class'	=> 'cs-field-subfield',
        ),
        
    ), // end: fields
);


/* ===============================================================================================
    LOGIN PAGE SECURITY
   =============================================================================================== */
$options[]      = array(
    'name'        => 'login_page_security',
    'title'       => __('Login Page Security','abbua_admin'),
    'icon'        => 'fei fei-shield',
    
    // begin: fields
    'fields'      => array(
        array(
            'type'    => 'heading',
            'content' => __('Login Page Security','abbua_admin'),
        ),

        array(
            'type'          => 'info',
            'title'         => __('A help for your WordPress', 'abbua_admin'),
            'content'       => __('Through this page you can increase the security of your WordPress by changing the default login and logout urls by other customized according to different user roles.','abbua_admin'),
        ),

        array(
            'id'        => 'login_security_custom_login_url_status',
            'type'      => 'switcher',
            'title'     => __('Custom Login URL','abbua_admin'),
            'label'     => __('Use custom login URL','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
            'default'   => true
        ),
        array(
            'dependency'    => array('login_security_custom_login_url_status','==','true'),
            'id'            => 'login_security_custom_login_url_fs',
            'type'          => 'fieldset',
            'fields'        => array(
                array(
                    'id'            => 'login_security_custom_login_slug',
                    'type'          => 'text',
                    'title'         => __('Login URL slug','abbua_admin'),
                    'default'       => __('abbua-admin-login','abbua_admin'),
                    'after'         => __('<p class="cs-text-muted">Important: Your new login url will be in this format: http://www.yoursite.com/your-new-login-url-slug/</p>','abbua_admin'),
                ),
            ),
        ),

        array(
            'id'        => 'login_security_custom_login_redirect_status',
            'type'      => 'switcher',
            'title'     => __('Custom Login Redirect','abbua_admin'),
            'label'     => __('Redirect users to a custom page after login','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'dependency'    => array('login_security_custom_login_redirect_status','==','true'),
            'id'            => 'login_security_custom_login_redirect_fs',
            'type'          => 'fieldset',
            'fields'        => array(
                array(
                    'id'            => 'login_security_custom_login_redirect_roles',
                    'type'          => 'custom_userrole',
                    'title'         => __('Redirect by User Role','abbua_admin'),
                ),
            ),
        ),

        array(
            'id'        => 'login_security_custom_logout_url_status',
            'type'      => 'switcher',
            'title'     => __('Custom Logout URL','abbua_admin'),
            'label'     => __('Use custom logout URL','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
            'default'   => true
        ),
        array(
            'dependency'    => array('login_security_custom_logout_url_status','==','true'),
            'id'            => 'login_security_custom_logout_url_fs',
            'type'          => 'fieldset',
            'fields'        => array(
                array(
                    'id'            => 'login_security_custom_logout_slug',
                    'type'          => 'text',
                    'title'         => __('Logout URL slug','abbua_admin'),
                    'default'       => __('abbua-admin-logout','abbua_admin'),
                    'after'         => __('<p class="cs-text-muted">Important: Your new logout url will be in this format: http://www.yoursite.com/your-new-logout-url-slug/</p>','abbua_admin'),
                ),
            ),
        ),

        array(
            'id'        => 'login_security_custom_logout_redirect_status',
            'type'      => 'switcher',
            'title'     => __('Custom Logout Redirect','abbua_admin'),
            'label'     => __('Redirect users to a custom page after logout','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'dependency'    => array('login_security_custom_logout_redirect_status','==','true'),
            'id'            => 'login_security_custom_logout_redirect_fs',
            'type'          => 'fieldset',
            'fields'        => array(
                array(
                    'id'            => 'login_security_custom_logout_redirect_roles',
                    'type'          => 'custom_userrole',
                    'title'         => __('Redirect by User Role','abbua_admin'),
                ),
            ),
        ),
    ),
);


/* ===============================================================================================
    PAGE LOADER
   =============================================================================================== */
$options[]      = array(
    'name'        => 'page_loader',
    'title'       => __('Page Loader','abbua_admin'),
    'icon'        => 'fei fei-loader',
    
    // begin: fields
    'fields'      => array(
        array(
            'type'    => 'heading',
            'content' => __('Page Loader','abbua_admin'),
        ),
        array(
            'id'        => 'page_loader_status',
            'type'      => 'switcher',
            'title'     => __('Page Loader','abbua_admin'),
            'label'     => __('Use custom page load progress indicator','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),

        array(
            'id'        => 'page_loader_custom_colors_status',
            'type'      => 'switcher',
            'title'     => __('Custom Colors','abbua_admin'),
            'label'     => __('Use custom progress loader colors','abbua_admin'),
            'after'     => __('<p class="cs-text-muted">Important: By default, the progress bar uses the theme primary and primary light colors.</p>','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'dependency'    => array('page_loader_custom_colors_status','==','true'),
            'id'            => 'page_loader_custom_colors_fs',
            'type'          => 'fieldset',
            'fields'        => array(
                array(
                    'id'        => 'page_loader_color_primary',
                    'type'      => 'color_picker',
                    'title'     => __('Bar Primary Color','abbua_admin'),
                    'default'   => '#9C27B0',
                ),
                array(
                    'id'        => 'page_loader_color_secondary',
                    'type'      => 'color_picker',
                    'title'     => __('Bar Secondary Color','abbua_admin'),
                    'default'   => '#E1BEE7',
                ),
            ),
        ),
        array(
            'id'        => 'page_loader_theme',
            'type'      => 'image_select',
            'title'     => __('Choose a Theme','abbua_admin'),
            'options'   => array(
                'theme-1'       => CS_PLUGIN_URI .'/adminframework/assets/images/theme-pace-1.png',
                'theme-2'       => CS_PLUGIN_URI .'/adminframework/assets/images/theme-pace-2.png',
                'theme-3'       => CS_PLUGIN_URI .'/adminframework/assets/images/theme-pace-3.png',
                'theme-4'       => CS_PLUGIN_URI .'/adminframework/assets/images/theme-pace-4.png',
                'theme-5'       => CS_PLUGIN_URI .'/adminframework/assets/images/theme-pace-5.png',
                'theme-6'       => CS_PLUGIN_URI .'/adminframework/assets/images/theme-pace-6.png',
                'theme-7'       => CS_PLUGIN_URI .'/adminframework/assets/images/theme-pace-7.png',
                'theme-8'       => CS_PLUGIN_URI .'/adminframework/assets/images/theme-pace-8.png',
                'theme-9'       => CS_PLUGIN_URI .'/adminframework/assets/images/theme-pace-9.png',
                'theme-10'      => CS_PLUGIN_URI .'/adminframework/assets/images/theme-pace-10.png',
                'theme-11'      => CS_PLUGIN_URI .'/adminframework/assets/images/theme-pace-11.png',
                'theme-12'      => CS_PLUGIN_URI .'/adminframework/assets/images/theme-pace-12.png',
                'theme-13'      => CS_PLUGIN_URI .'/adminframework/assets/images/theme-pace-13.png',
                'theme-14'      => CS_PLUGIN_URI .'/adminframework/assets/images/theme-pace-14.png',
            ),
            'radio'     => true,
            'default'   => 'theme-2',
        ),
        
    ), // end: fields
);


/* ===============================================================================================
    USER PROFILE
   =============================================================================================== */
$options[]      = array(
    'name'        => 'user_profile',
    'title'       => __('User Profile','abbua_admin'),
    'icon'        => 'fei fei-users',
    
    // begin: fields
    'fields'      => array(
        array(
            'type'    => 'heading',
            'content' => __('User Profile','abbua_admin'),
        ),
        array(
            'id'        => 'user_profile_status',
            'type'      => 'switcher',
            'title'     => __('Personal Settings','abbua_admin'),
            'label'     => __('Hide all user profile Personal Settings section','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'id'        => 'user_profile_options',
            'type'      => 'checkbox',
            'title'     => __('User Profile Personal Settings','abbua_admin'),
            'desc'      => __('Check to hide the section','abbua_admin'),
            'options'   => array(
                'editor'    => __('Hide Visual editor','abbua_admin'),
                'syntaxis'  => __('Hide Syntaxis Highlighting','abbua_admin'),
                'colors'    => __('Hide Admin colors schemes','abbua_admin'),
                'shortcuts' => __('Hide Keyboard shortcuts','abbua_admin'),
                'adminbar'  => __('Hide Admin Bar','abbua_admin'),
                'language'  => __('Hide Language selector','abbua_admin'),
            ),
        ),
        
    ), // end: fields
);


/* ===============================================================================================
    ADMIN TOP NAVBAR
   =============================================================================================== */
$options[]      = array(
    'name'        => 'top_navbar',
    'title'       => __('Top Navbar','abbua_admin'),
    'icon'        => 'fei fei-header',
    
    // begin: fields
    'fields'      => array(
        array(
            'type'    => 'heading',
            'content' => __('Top Navbar','abbua_admin'),
        ),

        array(
            'id'        => 'navbar_link_site',
            'type'      => 'switcher',
            'title'     => __('View Site Link','abbua_admin'),
            'label'     => __('Show "view site" link','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'id'        => 'navbar_link_updates',
            'type'      => 'switcher',
            'title'     => __('Updates Link','abbua_admin'),
            'label'     => __('Show updates link','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'id'        => 'navbar_link_comments',
            'type'      => 'switcher',
            'title'     => __('Comments Link','abbua_admin'),
            'label'     => __('Show comments link','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'id'        => 'navbar_link_addnew',
            'type'      => 'switcher',
            'title'     => __('Add New link','abbua_admin'),
            'label'     => __('Show "Add New" link','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'id'        => 'navbar_link_profile',
            'type'      => 'switcher',
            'title'     => __('User Profile','abbua_admin'),
            'label'     => __('Show user profile avatar link','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'id'        => 'navbar_position',
            'type'      => 'select',
            'title'     => __('Navbar Position','abbua_admin'),
            'options'   => array(
                'scroll'    => __('Follow Scroll','abbua_admin'),
                'fixed'     => __('Fixed to Top','abbua_admin'),
            ),
        ),
        
    ), // end: fields
);


/* ===============================================================================================
    SIDEBAR
   =============================================================================================== */
$options[]      = array(
    'name'        => 'sidebar',
    'title'       => __('Admin Menu','abbua_admin'),
    'icon'        => 'fei fei-sidebar',
    
    // begin: fields
    'fields'      => array(
        array(
            'type'    => 'heading',
            'content' => __('Admin Menu','abbua_admin'),
        ),
        array(
            'id'        => 'sidebar_status',
            'type'      => 'switcher',
            'title'     => __('Custom Admin Menu','abbua_admin'),
            'label'     => __('Use custom admin menu','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'id'        => 'sidebar_accordion',
            'type'      => 'switcher',
            'title'     => __('Submenu Accordion','abbua_admin'),
            'label'     => __('Collapse submenu as an accordion menu','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'id'        => 'sidebar_scrollbar',
            'type'      => 'switcher',
            'title'     => __('Custom Scrollbar','abbua_admin'),
            'label'     => __('Use custom scrollbar when fixed-sidebar gets overflowed','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'id'        => 'sidebar_brand_position',
            'type'      => 'select',
            'title'     => __('Sidebar Brand Position','abbua_admin'),
            'options'   => array(
                'scroll'    => __('Follow Scroll','abbua_admin'),
                'fixed'     => __('Fixed','abbua_admin'),
            ),
        ),
        array(
            'id'        => 'sidebar_position',
            'type'      => 'select',
            'title'     => __('Sidebar Position','abbua_admin'),
            'options'   => array(
                'scroll'    => __('Follow Scroll','abbua_admin'),
                'fixed'     => __('Fixed','abbua_admin'),
            ),
        ),
    ), // end: fields
);


/* ===============================================================================================
    FOOTER
   =============================================================================================== */
$options[]      = array(
    'name'        => 'footer',
    'title'       => __('Footer','abbua_admin'),
    'icon'        => 'fei fei-footer',
    
    // begin: fields
    'fields'      => array(
        array(
            'type'    => 'heading',
            'content' => __('Footer','abbua_admin'),
        ),
        array(
            'id'        => 'footer_text_status',
            'type'      => 'switcher',
            'title'     => __('Footer','abbua_admin'),
            'label'     => __('Use custom footer text','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
            'default'   => true,
        ),
        array(
            'dependency'    => array('footer_text_status','==','true'),
            'id'            => 'footer_text_fs',
            'type'          => 'fieldset',
            'fields'        => array(
                array(
                    'id'        => 'footer_text_visibility',
                    'type'      => 'switcher',
                    'title'     => __('Footer Text Visibility','abbua_admin'),
                    'label'     => __('Hide footer text','abbua_admin'),
                    'labels'    => array(
                        'on'    => __('Yes','abbua_admin'),
                        'off'   => __('No','abbua_admin'),
                    ),
                ),
                array(
                    'id'        => 'footer_text',
                    'type'      => 'wysiwyg',
                    'title'     => __('Custom Footer Text','abbua_admin'),
                    'desc'      => __('Enter the text that displays in the footer bar. HTML markup can be used.','abbua_admin'),
                    'default'   => __('ABBUA Admin Powered by <a href="http://www.castorstudio.com" target="_blank">CastorStudio</a>','abbua_admin'),
                    'settings'  => array(
                        'textarea_rows' => 5,
                        'tinymce'       => true,
                        'media_buttons' => false,
                        'quicktags'     => false,
                        'teeny'         => true,
                    ),
                ),
            ),
        ),
        array(
            'id'            => 'footer_version_status',
            'type'          => 'switcher',
            'title'         => __('Footer Version','abbua_admin'),
            'label'         => __('Use custom footer version text','abbua_admin'),
            'labels'        => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
            'default'       => true,
        ),
        array(
            'dependency'    => array('footer_version_status','==','true'),
            'id'            => 'footer_version_fs',
            'type'          => 'fieldset',
            'fields'        => array(
                array(
                    'id'        => 'footer_version_visibility',
                    'type'      => 'switcher',
                    'title'     => __('Footer Version Text Visibility','abbua_admin'),
                    'label'     => __('Hide footer version text','abbua_admin'),
                    'labels'    => array(
                        'on'    => __('Yes','abbua_admin'),
                        'off'   => __('No','abbua_admin'),
                    ),
                ),
                array(
                    'id'        => 'footer_version',
                    'type'      => 'wysiwyg',
                    'title'     => __('Custom Version Text','abbua_admin'),
                    'desc'      => __('Enter the text that displays in the footer version bar. HTML markup can be used.','abbua_admin'),
                    'default'   => sprintf(__('<a href="http://www.castorstudio.com/abbua-admin-wordpress-white-label-admin-theme" target="_blank">%s</a>','abbua_admin'),PLUGIN_NAME_VERSION),
                    'settings'  => array(
                        'textarea_rows' => 5,
                        'tinymce'       => true,
                        'media_buttons' => false,
                        'quicktags'     => false,
                        'teeny'         => true,
                    ),
                ),
            ),
        ),
    ), // end: fields
);


/* ===============================================================================================
    CUSTOM CSS
   =============================================================================================== */
$options[]      = array(
    'name'        => 'customcss',
    'title'       => __('Custom CSS','abbua_admin'),
    'icon'        => 'fei fei-code',
    
    // begin: fields
    'fields'      => array(
        array(
            'type'    => 'heading',
            'content' => __('Custom CSS','abbua_admin'),
        ),
        array(
            'id'        => 'customcss_status',
            'type'      => 'switcher',
            'title'     => __('Custom CSS','abbua_admin'),
            'label'     => __('Use custom CSS code','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'id'        => 'customcss',
            'type'      => 'code_editor',
            'title'     => __('Custom CSS','abbua_admin'),
            'desc'      => __('The code you paste here will be applied in all your admin and login area.','abbua_admin'),
            'after'     => __('<p class="cs-text-muted">Information: If you need to overwrite any CSS setting, you can add !important at the end of CSS property. eg: margin: 10px !important;</p>','abbua_admin'),
            'attributes'  => array(
                'data-theme'    => 'monokai',  // the theme for ACE Editor
                'data-mode'     => 'css',     // the language for ACE Editor
            ),
        )
    ),
);


/* ===============================================================================================
    GENERAL SETTINGS
   =============================================================================================== */
   $options[]      = array(
    'name'        => 'generalsettings',
    'title'       => __('General Settings','abbua_admin'),
    'icon'        => 'fei fei-settings',
    
    // begin: fields
    'fields'      => array(
        array(
            'type'    => 'heading',
            'content' => __('General Settings','abbua_admin'),
        ),
        array(
            'id'        => 'resetsettings_status',
            'type'      => 'switcher',
            'title'     => __('Reset Admin Settings','abbua_admin'),
            'desc'      => __('When you deactivate the plugin all your preferences will be deleted or reset to their default value.','abbua_admin'),
            'label'     => __('Reset admin settings on plugin deactivation','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
        ),
        array(
            'id'        => 'rightnowwidget_status',
            'type'      => 'switcher',
            'title'     => __('Admin Name on Right Now Dashboard Widget','abbua_admin'),
            'label'     => __('Hide the ABBUA Admin version on Dashboard','abbua_admin'),
            'labels'    => array(
                'on'    => __('Yes','abbua_admin'),
                'off'   => __('No','abbua_admin'),
            ),
            'default'   => false,
        ),
    ),
);


/* ===============================================================================================
    BACKUP
   =============================================================================================== */
$options[]   = array(
    'name'     => 'backup_section',
    'title'    => 'Backup',
    'icon'     => 'fei fei-shield',
    'fields'   => array(
        array(
            'type'    => 'heading',
            'content' => __('Backup','abbua_admin'),
        ),
        array(
            'type'    => 'notice',
            'class'   => 'warning',
            'content' => __('You can save your current options. Download a Backup and Import.','abbua_admin'),
        ),
        array(
            'type'    => 'backup',
        ),
    ),
);

CSFramework::instance( $settings, $options );