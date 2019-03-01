<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.

/**
 *
 * Plugin path finder
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if( ! function_exists( 'cs_plugin_get_path_locate' ) ) {
	function cs_plugin_get_path_locate() {
		$parent_dirname = realpath(dirname(__FILE__) . '/../..');
		// $dirname        = wp_normalize_path( dirname( __FILE__ ) );
		$dirname 		= wp_normalize_path($parent_dirname);

		$plugin_dir     = wp_normalize_path( WP_PLUGIN_DIR );
		$located_plugin = ( preg_match( '#'. $plugin_dir .'#', $dirname ) ) ? true : false;
		$directory      = ( $located_plugin ) ? $plugin_dir : get_template_directory();
		$directory_uri  = ( $located_plugin ) ? WP_PLUGIN_URL : get_template_directory_uri();
		$basename       = str_replace( wp_normalize_path( $directory ), '', $dirname );
		$dir            = $directory . $basename;
		$uri            = $directory_uri . $basename;

		return apply_filters( 'cs_plugin_get_path_locate', array(
			'basename' => wp_normalize_path( $basename ),
			'dir'      => wp_normalize_path( $dir ),
			'uri'      => $uri
		) );
	}
}


/**
 * Plugin is Network Activated
 * 
 * @since 1.0.0
 */
function cs_is_network_active() {
    // Makes sure the plugin is defined before trying to use it
    if (!function_exists( 'is_plugin_active_for_network' ) ) {
        require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
    }
    
    if (is_plugin_active_for_network( 'abbua_admin/abbua_admin.php' ) ) {
        // Plugin is activated
        return true;
    }

    return false;
}


/**
 * Functions to Add, Update and Get Settings
 *
 * @since 1.0.0
 */
function cs_add_option($variable, $default) {
    if (cs_is_network_active()) {
        add_site_option($variable, $default);
    } else {
        add_option($variable, $default);
    }
}
function cs_update_option($variable, $default) {
    if (cs_is_network_active()) {
        update_site_option($variable, $default);
    } else {
        update_option($variable, $default);
    }
}
function cs_get_option($variable, $default) {
    if (cs_is_network_active()) {
        return get_site_option($variable, $default);
    } else {
		return get_option($variable, $default);
    }
}


/**
 * Get Plugin Settings, from the Settings Panel with the unique field_id
 *
 * @since 1.0.0
 */
function cs_get_settings($variable) {
    if (cs_is_network_active()) {
        return get_site_option($variable);
    } else {
		$settings 	= get_option( 'cs_abbuaadmin_settings' );
		$output 	= cs_search_settings_array($settings,$variable);
		return $output;
    }
}


/**
 * Helper Function to search plugin settings right with the unique field_id
 *
 * @since 1.0.0
 */
function cs_search_settings_array( array $array, $search ){
    while( $array ) {
        if ( isset( $array[ $search ] ) ) { 
			return $array[ $search ]; 
		}
		$segment = array_shift( $array );
		if( is_array( $segment ) ) {
			if( $return = cs_search_settings_array( $segment, $search ) ) {
				return $return;
			}
        }
    }
    return false;
}









function cs_get_user_type() {
    $get_admin_menumng_page = cs_get_option("framework_option_to_know_when_loading_lkasdklajsdlkjdcmlakxalksdxa", "enable");

    $enablemenumng = true;
    if ((is_super_admin() || current_user_can('manage_options')) && $get_admin_menumng_page == "disable") {
        $enablemenumng = false;
    }
	// return $enablemenumng;
	return true;
}











/**
 * 
 * Helper Functions
 * 
 */
function cs_removeslashes($string) {
    $string = implode("", explode("\\", $string));
    return stripslashes(trim($string));
}

function cs_reformatstring($str) {
    $str = htmlspecialchars($str, ENT_QUOTES);
    $str = cs_removeslashes($str);
    return $str;
}

function cs_dashiconscsv() {
    $str = "menu:f333,
	admin-site:f319,
	dashboard:f226,
	admin-media:f104,
	admin-page:f105,
	admin-comments:f101,
	admin-appearance:f100,
	admin-plugins:f106,
	admin-users:f110,
	admin-tools:f107,
	admin-settings:f108,
	admin-network:f112,
	admin-generic:f111,
	admin-home:f102,
	admin-collapse:f148,
	format-links:f103,
	format-standard:f109,
	format-image:f128,
	format-gallery:f161,
	format-audio:f127,
	format-video:f126,
	format-chat:f125,
	format-status:f130,
	format-aside:f123,
	format-quote:f122,
	welcome-edit-page:f119,
	welcome-add-page:f133,
	welcome-view-site:f115,
	welcome-widgets-menus:f116,
	welcome-comments:f117,
	welcome-learn-more:f118,
	image-crop:f165,
	image-rotate-left:f166,
	image-rotate-right:f167,
	image-flip-vertical:f168,
	image-flip-horizontal:f169,
	undo:f171,
	redo:f172,
	editor-bold:f200,
	editor-italic:f201,
	editor-ul:f203,
	editor-ol:f204,
	editor-quote:f205,
	editor-alignleft:f206,
	editor-aligncenter:f207,
	editor-alignright:f208,
	editor-insertmore:f209,
	editor-spellcheck:f210,
	editor-expand:f211,
	editor-contract:f506,
	editor-kitchensink:f212,
	editor-underline:f213,
	editor-justify:f214,
	editor-textcolor:f215,
	editor-paste-word:f216,
	editor-paste-text:f217,
	editor-removeformatting:f218,
	editor-video:f219,
	editor-customchar:f220,
	editor-outdent:f221,
	editor-indent:f222,
	editor-help:f223,
	editor-strikethrough:f224,
	editor-unlink:f225,
	editor-rtl:f320,
	editor-break:f474,
	editor-code:f475,
	editor-paragraph:f476,
	align-left:f135,
	align-right:f136,
	align-center:f134,
	align-none:f138,
	lock:f160,
	calendar:f145,
	calendar-alt:f508,
	visibility:f177,
	post-status:f173,
	edit:f464,
	trash:f182,
	external:f504,
	arrow-up:f142,
	arrow-down:f140,
	arrow-left:f141,
	arrow-right:f139,
	arrow-up-alt:f342,
	arrow-down-alt:f346,
	arrow-left-alt:f340,
	arrow-right-alt:f344,
	arrow-up-alt2:f343,
	arrow-down-alt2:f347,
	arrow-left-alt2:f341,
	arrow-right-alt2:f345,
	leftright:f229,
	sort:f156,
	randomize:f503,
	list-view:f163,
	exerpt-view:f164,
	grid-view:f509,
	hammer:f308,
	art:f309,
	migrate:f310,
	performance:f311,
	universal-access:f483,
	universal-access-alt:f507,
	tickets:f486,
	nametag:f484,
	clipboard:f481,
	heart:f487,
	megaphone:f488,
	schedule:f489,
	wordpress:f120,
	wordpress-alt:f324,
	pressthis:f157,
	update:f463,
	screenoptions:f180,
	info:f348,
	cart:f174,
	feedback:f175,
	cloud:f176,
	translation:f326,
	tag:f323,
	category:f318,
	archive:f480,
	tagcloud:f479,
	text:f478,
	media-archive:f501,
	media-audio:f500,
	media-code:f499,
	media-default:f498,
	media-document:f497,
	media-interactive:f496,
	media-spreadsheet:f495,
	media-text:f491,
	media-video:f490,
	playlist-audio:f492,
	playlist-video:f493,
	yes:f147,
	no:f158,
	no-alt:f335,
	plus:f132,
	plus-alt:f502,
	minus:f460,
	dismiss:f153,
	marker:f159,
	star-filled:f155,
	star-half:f459,
	star-empty:f154,
	flag:f227,
	share:f237,
	share1:f237,
	share-alt:f240,
	share-alt2:f242,
	twitter:f301,
	rss:f303,
	email:f465,
	email-alt:f466,
	facebook:f304,
	facebook-alt:f305,
	networking:f325,
	googleplus:f462,
	location:f230,
	location-alt:f231,
	camera:f306,
	images-alt:f232,
	images-alt2:f233,
	video-alt:f234,
	video-alt2:f235,
	video-alt3:f236,
	vault:f178,
	shield:f332,
	shield-alt:f334,
	sos:f468,
	search:f179,
	slides:f181,
	analytics:f183,
	chart-pie:f184,
	chart-bar:f185,
	chart-line:f238,
	chart-area:f239,
	groups:f307,
	businessman:f338,
	id:f336,
	id-alt:f337,
	products:f312,
	awards:f313,
	forms:f314,
	testimonial:f473,
	portfolio:f322,
	book:f330,
	book-alt:f331,
	download:f316,
	upload:f317,
	backup:f321,
	clock:f469,
	lightbulb:f339,
	microphone:f482,
	desktop:f472,
	tablet:f471,
	smartphone:f470,
	smiley:f328,
	index-card:f510,
	carrot:f511";

    return $str;
}