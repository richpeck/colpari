<?php
/*
Plugin Name: Google Analytics Germanized
Plugin URI: https://wordpress.org/plugins/ga-germanized/
Description: Google Analytics preconfigured to respect EU law (GDPR / DSGVO) and with lots of advanced analytics settings for extensive tracking possibilities.
Version: 1.4.0
Author: Pascal Bajorat
Author URI: https://www.pascal-bajorat.com
Text Domain: ga-germanized
Domain Path: /lang
License: GNU General Public License v.3

Copyright (c) 2018 by Pascal-Bajorat.com.
*/

/* Security-Check */
if ( !class_exists('WP') ) {
	die();
}

/* Constants */
define('PBGAG_FILE', __FILE__);
define('PBGAG_DIR', dirname(__FILE__));
define('PBGAG_BASE', plugin_basename(__FILE__));
define('PBGAG_VERSION', '1.4.0');

if( ! class_exists('gaGermanized') ):

	class gaGermanized
	{
		public static function init()
		{
			/*
             * Language file
             */
			load_plugin_textdomain(
				'ga-germanized',
				false,
				dirname( PBGAG_BASE ) . '/lang/'
			);

			/*
			 * Admin functions
			 */
			add_action(
				'init',
				array(
					'gag_settings_handler',
					'init'
				)
			);

			if( is_admin() ) {

				add_action(
					'admin_enqueue_scripts',
					array(
						'gag_settings',
						'admin_enqueue_scripts'
					)
				);

				add_action(
					'admin_menu',
					array(
						'gag_settings',
						'options_page_menu'
					)
				);

				add_filter(
					'plugin_action_links_'.PBGAG_BASE,
					array(
						'gag_settings',
						'plugin_action_links'
					)
				);

			} else { // if( is_admin() )

				/*
				 * Frontend functions
				 */

				add_action(
					'wp_head',
					array(
						'gag_analytics',
						'wp_head_code'
					)
				);

				add_action(
					'wp_enqueue_scripts',
					array(
						'gag_analytics',
						'gag_tracker_js'
					)
				);

                add_action(
                    'init',
                    array(
                        'gag_cookieconsent',
                        'init'
                    )
                );

				add_action(
					'wp_head',
					array(
						'gag_analytics',
						'custom_tracker_head'
					),
					99
				);

				add_action(
					'wp_footer',
					array(
						'gag_analytics',
						'custom_tracker_footer'
					),
					99
				);

			} // end else if( is_admin() )

			add_action(
				'init',
				array(
					'gag_shortcodes',
					'init'
				)
			);
		}

		public static function detect_plugin_activation( $plugin, $network_activation )
		{
			if( $plugin !== PBGAG_BASE || $network_activation ) {
				return;
			}

			$settings_page_url = admin_url('options-general.php?page=ga-germanized');

			if ( wp_redirect( $settings_page_url ) ) {
				exit;
			}
		}

		public static function activation_hook()
		{
			// no setup required
		}

		public static function uninstall_hook()
		{
			gag_settings_handler::delete_options();
		}
	}

endif;

add_action(
	'plugins_loaded',
	array(
		'gaGermanized',
		'init'
	)
);

add_action(
	'activated_plugin',
	array(
		'gaGermanized',
		'detect_plugin_activation'
	),
	10,
	2
);

register_activation_hook(
	__FILE__,
	array(
		'gaGermanized',
		'activation_hook'
	)
);

register_uninstall_hook(
	__FILE__,
	array(
		'gaGermanized',
		'uninstall_hook'
	)
);

/* Autoload Init */
spl_autoload_register('pbgag_autoload');

/* Autoload Function */
function pbgag_autoload($class)
{
	$allowed_classes = array(
		'gag_settings',
		'gag_settings_handler',
		'gag_analytics',
		'gag_cookieconsent',
		'gag_shortcodes'
	);

	if( in_array($class, $allowed_classes) ) {

		$require_once = sprintf(
			'%s/inc/%s.php',

			PBGAG_DIR,
			strtolower($class)
		);

		require_once( $require_once );
	}
}