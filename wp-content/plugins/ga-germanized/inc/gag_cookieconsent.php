<?php
/* Security-Check */
if ( !class_exists('WP') ) {
    die();
}

if( ! class_exists('gag_cookieconsent') ):
    class gag_cookieconsent
    {
        public static $settings = false;
        public static $version = '3.0.3';

        public static function init()
        {
            if( ! self::$settings || ! is_array(self::$settings) )
            {
                self::$settings = gag_settings_handler::current_settings();
            }

            if( self::$settings['disable-cookie-notice'] ) {
                return;
            }

            add_action(
                'wp_enqueue_scripts',
                array(
                    __CLASS__,
                    'scripts_and_css'
                )
            );
        }

        public static function scripts_and_css()
        {
	        global $wp;

            /**
             * cookieconsent css
             */
            wp_enqueue_style(
                'cookieconsent',
                plugins_url(dirname(PBGAG_BASE)).'/assets/css/cookieconsent.css',
                false,
                self::$version
            );

            /**
             * cookieconsent js
             */
            wp_enqueue_script(
                'cookieconsent',
                plugins_url(dirname(PBGAG_BASE)).'/assets/js/cookieconsent.js',
                false,
                self::$version,
                true
            );

            if( self::$settings['cc-position'] == 'top-pushed' ) {
                self::$settings['cc-position'] = 'top';
                $static = true;
            } else {
                $static = false;
            }

            if( self::$settings['enable-policy-link'] ) {
	            $policy_link = esc_attr__(self::$settings['cc-policy-link-text'], 'ga-germanized');
            } else {
            	$policy_link = false;
            }

            if( self::$settings['compliance-type'] == 'opt-in' || self::$settings['compliance-type'] == 'opt-out' ) {
            	$compliance_type = self::$settings['compliance-type'];
            } else {
	            $compliance_type = false;
            }

            /**
             * cookieconsent setup & config
             */

            $cookieconsent_array = array (
	            'palette' => array (
		            'popup' =>
			            array (
				            'background' => self::$settings['cc-banner-background-color'],
				            'text' => self::$settings['cc-banner-text-color'],
			            ),
		            'button' =>
			            array (
				            'background' => self::$settings['cc-button-background-color'],
				            'text' => self::$settings['cc-button-text-color'],
			            ),
	            ),
	            'theme' => self::$settings['cc-layout'],
	            'position' => self::$settings['cc-position'],
	            'static' => $static,
	            'content' => array (
		            'message' => nl2br(__(self::$settings['cc-banner-text'], 'ga-germanized')),
		            'dismiss' => esc_attr__(self::$settings['cc-button-text'], 'ga-germanized'),
		            'allow' => esc_attr__(self::$settings['cc-accept-button-text'], 'ga-germanized'),
		            'deny' => esc_attr__(self::$settings['cc-deny-button-text'], 'ga-germanized'),
		            'link' => $policy_link,
		            'href' => self::$settings['cc-policy-link'],
	            ),
	            'type' => $compliance_type,
	            'onStatusChange' => '%onStatusChange%'
            );

            $cookieconsent_settings = sprintf(
	            'try { window.addEventListener("load", function(){window.cookieconsent.initialise(%1$s)}); } catch(err) { console.error(err.message); }',
	            json_encode( $cookieconsent_array )
            );

            if( self::$settings['compliance-type'] == 'opt-in' || self::$settings['compliance-type'] == 'opt-out' ) {
            	$onStatusChange = 'function(){ window.location.href = "'.esc_url( home_url( $wp->request ) .'?cookie-state-change=' ).'" + Date.now(); }';
            } else {
	            $onStatusChange = 'function(){}';
            }

	        $onStatusChange = apply_filters('gag-onstatuschange', $onStatusChange);

	        $cookieconsent_settings = str_replace('"%onStatusChange%"', $onStatusChange, $cookieconsent_settings);

	        $cookieconsent_settings = apply_filters('gag-cookieconsent-settings', $cookieconsent_settings, self::$settings);

            wp_add_inline_script(
                'cookieconsent',
	            $cookieconsent_settings,
                'after'
            );
        }
    }
endif;