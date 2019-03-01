<?php
/* Security-Check */
if ( !class_exists('WP') ) {
	die();
}

if( ! class_exists('gag_analytics') ):
	class gag_analytics
	{
		public static $settings = false;

		public static $uaid = false;
		public static $mode = false;

		public static function init()
		{
			if( ! self::$settings || ! is_array(self::$settings) )
			{
				self::$settings = gag_settings_handler::current_settings();
			}

			self::$uaid = self::$settings['analytics-id'];
			self::$mode = self::$settings['analytics-mode'];
		}

		public static function is_dnt()
		{
			return (isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] == 1);
		}

		public static function is_analytics_allowed($check_disable_analytics_integration=true)
		{
			// Init Settings
			if( ! self::$settings ) {
				self::init();
			}

			// cookieconsent_status Cookie
			if( isset($_COOKIE['cookieconsent_status']) ) {
				$coookie_consent = $_COOKIE['cookieconsent_status'];
			} else {
				$coookie_consent = false;
			}

			if( self::$settings['disable-analytics-integration'] && $check_disable_analytics_integration ) {
				return false;
			}

			if( ! self::$settings['disable-cookie-notice'] && self::$settings['compliance-type'] == 'opt-in' && $coookie_consent !== 'allow' ) {
				return false;
			}

			if( ! self::$settings['disable-cookie-notice'] && self::$settings['compliance-type'] == 'opt-out' && $coookie_consent == 'deny' ) {
				return false;
			}

			if( self::$settings['ga-dnt'] && self::is_dnt() && self::$settings['compliance-type'] != 'opt-in' ) {
				return false;
			}


			return true;
		}

		public static function gst()
		{
			$config = array();

			if( self::$settings['anonymize_ip'] ) {
				$config['anonymize_ip'] = true;
			}

			if( self::$settings['displayfeatures'] ) {
				$config['allow_display_features'] = true;
			} else {
				$config['allow_display_features'] = false;
			}

			if( self::$settings['linkid'] ) {
				$config['link_attribution'] = true;
			} else {
				$config['link_attribution'] = false;
			}

			$code  = '<script async src="https://www.googletagmanager.com/gtag/js?id='.esc_attr(self::$uaid).'"></script>';
			$code .= '<script>';
			$code .= "
				window.dataLayer = window.dataLayer || [];
				function gtag(){dataLayer.push(arguments);}
				gtag('js', new Date());";
				
			$code .= "gtag('config', '".esc_attr(self::$uaid)."', ".json_encode($config).");";

			if( ! empty(self::$settings['custom-code']) ) {
				$code .= self::$settings['custom-code'];
			}

			$code .= '</script>';

			$code = apply_filters('gag-gst-code', $code, self::$settings);

			return $code;
		}

		public static function ua()
		{
			$domain = ((empty(self::$settings['domain']))?'auto':self::$settings['disable-analytics-integration']);

			$code  = '<script>';
			$code .= "
				(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');";

			$code .= sprintf(
				"ga('create', '%s', '%s');",

				esc_attr(self::$uaid),
				$domain
			);

			if( ! empty(self::$settings['custom-code']) ) {
				$code .= self::$settings['custom-code'];
			}

			if( self::$settings['linkid'] ) {
				$code .= "ga('require', 'linkid');";
			}

			if( self::$settings['displayfeatures'] ) {
				$code .= "ga('require', 'displayfeatures');";
			} else {
				$code .= "ga('set', 'displayFeaturesTask', null);";
			}

			if( self::$settings['anonymize_ip'] ) {
				$code .= "ga('set', 'anonymizeIp', true);";
			}

			$code .= "ga('send', 'pageview');";

			$code .= '</script>';

			$code = apply_filters('gag-ua-code', $code, self::$settings);

			return $code;
		}

		public static function wp_head_code()
		{

			self::init();

			if( ! empty( self::$uaid ) && self::is_analytics_allowed() ) {

				if( self::$mode === 'ua' ) {
					echo self::ua();
				} else {
					echo self::gst();
				}

			} else {
				echo '<!-- Missing Google Analytics ID or Analytics Integration disabled -->';
			}
		}

		public static function gag_tracker_js()
		{
			self::init();

			if( self::is_analytics_allowed() === false )
				return;

			$link_tracking = (bool) self::$settings['link-tracking'];

			if( ! $link_tracking )
			    return;

			wp_register_script(
				'gag-tracker',
				plugins_url(dirname(PBGAG_BASE)).'/assets/js/gag-tracker.js',
				array('jquery'),
				PBGAG_VERSION,
				true
			);

			wp_localize_script(
				'gag-tracker',
				'gagTracker', array(
					'url' => home_url(),
					'ua' => self::$settings['analytics-id'],
					'mode' => self::$settings['analytics-mode'],
					'link_tracking' => $link_tracking
				)
			);

			wp_enqueue_script('gag-tracker');
		}

		public static function custom_tracker_head()
		{
			// Init Settings
			if( ! self::$settings ) {
				self::init();
			}

			if( self::$settings['other-tracking-compliance'] && self::is_analytics_allowed(false) === false )
				return;

			if( self::$settings['custom-tracker-dnt'] && self::is_dnt() )
				return;

			echo self::$settings['custom-tracker-head'];
		}

		public static function custom_tracker_footer()
		{
			// Init Settings
			if( ! self::$settings ) {
				self::init();
			}

			if( self::$settings['other-tracking-compliance'] && self::is_analytics_allowed(false) === false )
				return;

			if( self::$settings['custom-tracker-dnt'] && self::is_dnt() )
				return;

			echo self::$settings['custom-tracker-footer'];
		}
	}
endif;