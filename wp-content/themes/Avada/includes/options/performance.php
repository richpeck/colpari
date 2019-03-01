<?php
/**
 * Avada Options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.8
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Advanced settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_performance( $sections ) {
	$settings = get_option( Avada::get_option_name() );

	$all_filetypes = Avada()->pwa->get_filetypes();
	$filetypes     = array();
	foreach ( $all_filetypes as $key => $details ) {
		$filetypes[ $key ] = $details['label'];
	}

	$cache_first_defaults   = array( 'images', 'scripts', 'styles', 'fonts' );
	$network_first_defaults = array();

	$all_pages = get_pages();
	$pages     = array();
	foreach ( $all_pages as $page ) {
		$pages [ $page->ID ] = $page->post_title;
	}

	$pwa_enabled = ( function_exists( 'wp_register_service_worker_caching_route' ) && class_exists( 'WP_Service_Worker_Caching_Routes' ) );
	$is_https    = ( false !== strpos( home_url(), 'https' ) );

	// Is the JS compiler enabled?
	$is_http2 = Fusion_Dynamic_JS::is_http2();
	if ( $is_http2 ) {
		$js_compiler_enabled = ( isset( $settings['js_compiler'] ) && ( '1' === $settings['js_compiler'] || 1 === $settings['js_compiler'] || true === $settings['js_compiler'] ) );
	} else {
		$js_compiler_enabled = ( ! isset( $settings['js_compiler'] ) || ( '1' === $settings['js_compiler'] || 1 === $settings['js_compiler'] || true === $settings['js_compiler'] ) );
	}

	$sections['performance'] = array(
		'label'    => esc_html__( 'Performance', 'Avada' ),
		'id'       => 'heading_performance',
		'is_panel' => true,
		'priority' => 25,
		'icon'     => 'el-icon-time-alt',
		'fields'   => array(
			'lazy_load'                            => array(
				'label'       => esc_html__( 'Enable Lazy Loading', 'Avada' ),
				'description' => esc_html__( 'Enable lazy loading for your website\'s images to improve performance.', 'Avada' ),
				'id'          => 'lazy_load',
				'default'     => '0',
				'type'        => 'switch',
			),
			'font_face_display'                    => array(
				'label'       => esc_html__( 'Font Face Rendering', 'Avada' ),
				'description' => esc_html__( 'Choose "Swap" for faster rendering with possible flash of unstyled text (FOUT) or "Block" for clean rendering but longer wait time until first paint.', 'Avada' ),
				'id'          => 'font_face_display',
				'default'     => 'block',
				'type'        => 'radio-buttonset',
				'choices'     => array(
					'block' => esc_attr__( 'Block', 'Avada' ),
					'swap'  => esc_attr__( 'Swap', 'Avada' ),
				),
			),
			'emojis_disabled' => array(
				'label'       => esc_html__( 'Emojis script', 'Avada' ),
				'description' => esc_html__( 'If you don\'t use emojis you can improve performance by removing WordPress\' emojis script.', 'Avada' ),
				'id'          => 'emojis_disabled',
				'default'     => 'enabled',
				'type'        => 'radio-buttonset',
				'choices'     => array(
					'enabled'   => esc_attr__( 'Enable', 'Avada' ),
					'disabled'  => esc_attr__( 'Disable', 'Avada' ),
				),
			),
			'dynamic_compiler_section'             => array(
				'label' => esc_html__( 'Dynamic CSS & JS', 'Avada' ),
				'id'    => 'dynamic_compiler_section',
				'icon'  => true,
				'type'  => 'info',
			),
			'css_cache_method'                     => array(
				'label'       => esc_html__( 'CSS Compiling method', 'Avada' ),
				'description' => esc_html__( 'Select "File" mode to compile the dynamic CSS to files (a separate file will be created for each of your pages & posts inside of the uploads/fusion-styles folder), "Database" mode to cache the CSS in your database, or select "Disabled" to disable.', 'Avada' ),
				'id'          => 'css_cache_method',
				'default'     => 'file',
				'type'        => 'radio-buttonset',
				'choices'     => array(
					'file' => esc_attr__( 'File', 'Avada' ),
					'db'   => esc_attr__( 'Database', 'Avada' ),
					'off'  => esc_attr__( 'Disabled', 'Avada' ),
				),
			),
			'media_queries_async'                  => array(
				'label'       => esc_attr__( 'Load Media-Queries Files Asynchronously', 'Avada' ),
				'description' => esc_attr__( 'When enabled, the CSS media-queries will be enqueued separately and then loaded asynchronously, improving performance on mobile and desktop.', 'Avada' ),
				'id'          => 'media_queries_async',
				'default'     => '0',
				'type'        => 'switch',
			),
			'cache_server_ip'                      => array(
				'label'       => esc_html__( 'Cache Server IP', 'Avada' ),
				'description' => esc_html__( 'For unique cases where you are using cloud flare and a cache server, ex: varnish cache. Enter your cache server IP to clear the theme options dynamic CSS cache. Consult with your server admin for help.', 'Avada' ),
				'id'          => 'cache_server_ip',
				'default'     => '',
				'type'        => 'text',
			),
			'js_compiler_note'                     => ( apply_filters( 'fusion_compiler_js_file_is_readable', ( get_transient( 'fusion_dynamic_js_readable' ) || ! $js_compiler_enabled ) ) ) ? array() : array(
				'label'       => '',
				'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> JS Compiler is disabled. File does not exist or access is restricted.', 'Avada' ) . '</div>',
				'id'          => 'js_compiler_note',
				'type'        => 'custom',
			),
			'js_compiler'                          => array(
				'label'       => esc_html__( 'Enable JS Compiler', 'Avada' ),
				'description' => ( $is_http2 ) ? esc_html__( 'We have detected that your server supports HTTP/2. We recommend you leave the compiler disabled as that will improve performance of your site by allowing multiple JS files to be downloaded simultaneously.', 'Avada' ) : esc_html__( 'By default all the javascript files are combined. Disabling the JS compiler will load non-combined javascript files. This will have an impact on the performance of your site.', 'Avada' ),
				'id'          => 'js_compiler',
				'default'     => ( $is_http2 ) ? '0' : '1',
				'type'        => 'switch',
			),
			'reset_caches_button'                  => array(
				'label'       => esc_html__( 'Reset Fusion Caches', 'Avada' ),
				/* translators: %1$s: <code>uploads/fusion-styles</code>. %2$s: <code>uploads/fusion-scripts</code>. */
				'description' => ( is_multisite() && is_main_site() ) ? sprintf( esc_html__( 'Resets all Dynamic CSS & Dynamic JS, cleans-up the database and deletes the %1$s and %2$s folders. When resetting the caches on the main site of a multisite installation, caches for all sub-sites will be reset. IMPORTANT NOTE: On large multisite installations with a low PHP timeout setting, bulk-resetting the caches may timeout.', 'Avada' ), '<code>uploads/fusion-styles</code>', '<code>uploads/fusion-scripts</code>' ) : sprintf( esc_html__( 'Resets all Dynamic CSS & Dynamic JS, cleans-up the database and deletes the %1$s and %2$s folders.', 'Avada' ), '<code>uploads/fusion-styles</code>', '<code>uploads/fusion-scripts</code>' ),
				'id'          => 'reset_caches_button',
				'default'     => '',
				'type'        => 'raw',
				'content'     => '<a class="button button-secondary" href="#" onclick="fusionResetCaches(event);" target="_self" >' . esc_attr__( 'Reset Fusion Caches', 'Avada' ) . '</a><span class="spinner fusion-spinner"></span>',
				'full_width'  => false,
			),
			'pwa_section'                          => array(
				'label' => esc_html__( 'Progressive Web App', 'Avada' ),
				'id'    => 'pwa_section',
				'icon'  => true,
				'type'  => 'info',
			),
			'pwa_required_notice'                  => ! $pwa_enabled ? array(
				'label'       => '',
				'description' => sprintf(
					/* translators: URL for the plugins page. */
					'<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> To use the Avada PWA feature you need to install and activate the latest version of the PWA plugin. Please <a href="%s">visit the Avada Plugins page</a> to install and activate the plugin and then refresh Theme Options to edit the options.', 'Avada' ) . '</div>',
					admin_url( 'admin.php?page=avada-plugins' )
				),
				'id'          => 'pwa_required_notice',
				'type'        => 'custom',
			) : array(),
			'pwa_required_https_notice'            => $pwa_enabled && ! $is_https ? array(
				'label'       => '',
				'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> To use the Avada PWA feature your site must use SSL (HTTPS). For more information about the options in this section, please see our <a href="https://theme-fusion.com/documentation/avada/options/avada-progressive-web-app/" target="_blank">PWA documentation</a> page. To learn more about caching strategies and their use in general you can <a href="https://developers.google.com/web/tools/workbox/modules/workbox-strategies" target="_blank" rel="nofollow">read this article</a>.', 'Avada' ) . '</div>',
				'id'          => 'pwa_required_notice',
				'type'        => 'custom',
			) : array(),
			'pwa_enable'                           => $pwa_enabled ? array(
				'label'       => esc_html__( 'Enable Progressive Web App', 'Avada' ),
				'description' => esc_html__( 'Enable this option if you want to enable the Progressive Web App feature and options on your website.', 'Avada' ),
				'id'          => 'pwa_enable',
				'default'     => '0',
				'type'        => 'switch',
			) : array(),
			'pwa_filetypes_cache_first'            => $pwa_enabled ? array(
				'label'       => esc_html__( 'Cache-First strategy file types', 'Avada' ),
				'description' => esc_html__( 'File types added in this list will be cached in the browser. Subsequent page requests will use the cached assets. Use this for static assets that don\'t change like images and fonts.', 'Avada' ),
				'id'          => 'pwa_filetypes_cache_first',
				'default'     => $cache_first_defaults,
				'type'        => 'select',
				'multi'       => true,
				'choices'     => $filetypes,
				'required'    => array(
					array(
						'setting'  => 'pwa_enable',
						'operator' => '==',
						'value'    => '1',
					),
				),
			) : array(),
			'pwa_filetypes_network_first'          => $pwa_enabled ? array(
				'label'       => esc_html__( 'Network-First strategy file types', 'Avada' ),
				'description' => esc_html__( 'File types added in this list will be cached in the browser. Subsequent page requests will first try to get a more recent version of these files from the network, and fallback to the cached files in case the network is unreachable. If your site\'s content gets updated often we recommend you can use this for your content.', 'Avada' ),
				'id'          => 'pwa_filetypes_network_first',
				'default'     => $network_first_defaults,
				'type'        => 'select',
				'multi'       => true,
				'choices'     => $filetypes,
				'required'    => array(
					array(
						'setting'  => 'pwa_enable',
						'operator' => '==',
						'value'    => '1',
					),
				),
			) : array(),
			'pwa_filetypes_stale_while_revalidate' => $pwa_enabled ? array(
				'label'       => esc_html__( 'Stale-While-Revalidating strategy file types', 'Avada' ),
				'description' => esc_html__( 'Any file types added here will be served with a cache-first strategy, and after the page has been loaded the caches will be updated with more recent versions of the selected file types from the network. Use this for assets that may get updated but having their latest version is not critical.', 'Avada' ),
				'id'          => 'pwa_filetypes_stale_while_revalidate',
				'default'     => array(),
				'type'        => 'select',
				'multi'       => true,
				'choices'     => $filetypes,
				'required'    => array(
					array(
						'setting'  => 'pwa_enable',
						'operator' => '==',
						'value'    => '1',
					),
				),
			) : array(),
			'pwa_manifest_logo'                        => $pwa_enabled ? array(
				'label'       => esc_html__( 'App Splash Screen Logo', 'Avada' ),
				'description' => esc_html__( 'Logo displayed for your website at 512px x 512px when installing as an app. Logo image must be in PNG format.', 'Avada' ),
				'id'          => 'pwa_manifest_logo',
				'default'     => '',
				'type'        => 'media',
				'mode'        => false,
				'required'    => array(
					array(
						'setting'  => 'pwa_enable',
						'operator' => '==',
						'value'    => '1',
					),
				),
			) : array(),
			'pwa_manifest_display'                 => $pwa_enabled ? array(
				'label'       => esc_html__( 'App Display Mode', 'Avada' ),
				'description' => __( 'If the user installs your site as an app, select how the app will behave. For more information about these options please refer to <a href="https://developers.google.com/web/fundamentals/web-app-manifest/#display" target="_blank">this document.</a>', 'Avada' ),
				'id'          => 'pwa_manifest_display',
				'default'     => 'minimal-ui',
				'type'        => 'select',
				'choices'     => array(
					'fullscreen' => esc_html__( 'Fullscreen', 'Avada' ),
					'standalone' => esc_html__( 'Standalone', 'Avada' ),
					'minimal-ui' => esc_html__( 'Minimal UI', 'Avada' ),
					'browser'    => esc_html__( 'Browser', 'Avada' ),
				),
				'required'    => array(
					array(
						'setting'  => 'pwa_enable',
						'operator' => '==',
						'value'    => '1',
					),
				),
			) : array(),
			/**
			 * This is still a work in progress in the PWA plugin
			 *
			'pwa_precache_pages'                   => $pwa_enabled ? array(
				'label'       => esc_html__( 'Precache Pages', 'Avada' ),
				'description' => esc_html__( 'Pages added to this list will be precached and become available faster. You can use this option to precache your homepage or any other pages that are frequently visited. Use with caution and restraint.', 'Avada' ),
				'id'          => 'pwa_precache_pages',
				'default'     => array(),
				'type'        => 'select',
				'multi'       => true,
				'choices'     => $pages,
				'required'    => array(
					array(
						'setting'  => 'pwa_enable',
						'operator' => '==',
						'value'    => '1',
					),
				),
			) : array(),
			*/
			'pwa_theme_color'                      => $pwa_enabled ? array(
				'label'       => esc_html__( 'App Theme Color', 'Avada' ),
				'description' => __( 'Select a color that will be used for the header of your app, as well as the browser toolbar-color on mobile devices.', 'Avada' ),
				'id'          => 'pwa_theme_color',
				'default'     => 'minimal-ui',
				'type'        => 'color',
				'default'     => isset( $settings['mobile_header_bg_color'] ) ? $settings['mobile_header_bg_color'] : '#ffffff',
				'required'    => array(
					array(
						'setting'  => 'pwa_enable',
						'operator' => '==',
						'value'    => '1',
					),
				),
			) : array(),
		),
	);

	return $sections;

}
