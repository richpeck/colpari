<?php
/* Security-Check */
if ( !class_exists('WP') ) {
	die();
}

if( ! class_exists('gag_settings') ):
	class gag_settings
	{
		public static function plugin_action_links($data)
		{
			if( ! current_user_can('manage_options') ) {
				return $data;
			}

			$data = array_merge(
				array(
					sprintf(
						'<a href="%s">%s</a>',
						add_query_arg(
							array(),
							admin_url('options-general.php?page=ga-germanized')
						),
						__('Settings', 'ga-germanized')
					)
				),
				$data
			);

			return $data;
		}

		public static function admin_enqueue_scripts()
		{
			wp_register_style(
				'ga-germanized-css',
				plugins_url(dirname(PBGAG_BASE)).'/assets/css/ga-germanized.css',
				false,
				PBGAG_VERSION
			);

			wp_enqueue_style( 'ga-germanized-css' );

			wp_register_script(
				'ga-germanized-js',
				plugins_url(dirname(PBGAG_BASE)).'/assets/js/ga-germanized.js',
				array('jquery'),
				PBGAG_VERSION,
				true
			);

			wp_localize_script(
				'ga-germanized-js',
				'gagApiSettings', array(
					'root' => esc_url_raw( rest_url('/')),
					'save_settings' => esc_url_raw( rest_url( gag_settings_handler::$namespace . '/' . gag_settings_handler::$version . '/' . gag_settings_handler::$ep_save_settings) ),
					'nonce' => wp_create_nonce( 'wp_rest' )
				)
			);

			wp_enqueue_script('ga-germanized-js');
		}

		public static function options_page_menu()
		{
			add_submenu_page(
				'options-general.php',
				__('Google Analytics Germanized', 'ga-germanized'),
				__('Google Analytics', 'ga-germanized'),
				'manage_options',
				'ga-germanized',
				array(__CLASS__, 'options_page')
			);
		}

		public static function options_page()
		{
			if (!current_user_can('manage_options')) {
				return;
			}

			$settings = gag_settings_handler::current_settings();

			require_once PBGAG_DIR.'/inc/tpl/options_page.php';
		}
	}
endif;