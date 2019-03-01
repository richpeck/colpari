<?php
/* Security-Check */
if ( !class_exists('WP') ) {
	die();
}

if( ! class_exists('gag_shortcodes') ):
	class gag_shortcodes
	{
		public static function init()
		{
			add_action(
				'wp_enqueue_scripts',
				array(
					__CLASS__,
					'ga_optout_scripts'
				)
			);

			add_shortcode(
				'ga-optout',
				array(
					__CLASS__,
					'ga_optout'
				)
			);
		}

		public static function ga_optout_scripts()
		{
			$disable_ga_optout_scripts = apply_filters('gag-disable-ga-optout-scripts', false);

			if( $disable_ga_optout_scripts )
				return;

			$settings = gag_settings_handler::current_settings();

			wp_register_script(
				'google-analytics-germanized-gaoptout',
				plugins_url(dirname(PBGAG_BASE)).'/assets/js/gaoptout.js',
				array('jquery'),
				PBGAG_VERSION,
				true
			);

			wp_localize_script(
				'google-analytics-germanized-gaoptout',
				'gaoptoutSettings', array(
					'ua' => $settings['analytics-id'],
					'disabled' => esc_attr__('Google Analytics Opt-out Cookie was set!', 'ga-germanized')
				)
			);

			wp_enqueue_script('google-analytics-germanized-gaoptout');
		}

		public static function ga_optout( $atts )
		{
			$a = shortcode_atts( array(
				'text' => esc_html__('Disable Google Analytics', 'ga-germanized'),
			), $atts );

			$settings = gag_settings_handler::current_settings();

			return sprintf(
				__('<a href="#" data-ua="%s" class="gaoptout">%s</a>', 'ga-germanized'),

				$settings['analytics-id'],
				$a['text']
			);
		}
	}
endif;