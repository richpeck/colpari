<?php
// If this file is called directly, abort.
if (!defined( 'WPINC' ) ) {die;}

/**
 * Define CS_PLUGIN_PATH for later use on Admin Framework and other files
 */
$path = cs_plugin_get_path_locate();
if (!defined('CS_PLUGIN_PATH')){
    define( 'CS_PLUGIN_PATH', $path['dir'] );
}
if (!defined('CS_PLUGIN_URI')){
    define( 'CS_PLUGIN_URI', $path['uri'] );
}

// Plugin URL
if(!defined('CS_PLUGIN_URL')){
    define('CS_PLUGIN_URL', 'http://www.castorstudio.com/abbua-admin-wordpress-white-label-admin-theme');
}

// Documentation URL
if(!defined('CS_DOCS_URL')){
    define('CS_DOCS_URL', CS_PLUGIN_URL . '/docs');
}

// Support URL
if(!defined('CS_SUPPORT_URL')){
    define('CS_SUPPORT_URL', CS_PLUGIN_URL . '/support');
}

// Themes Slug
if(!defined('CS_CSS_THEME_SLUG')){
    define('CS_CSS_THEME_SLUG', '--cs-abbua-theme');
}