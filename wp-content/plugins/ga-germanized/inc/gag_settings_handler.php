<?php
/* Security-Check */
if ( !class_exists('WP') ) {
	die();
}

if( ! class_exists( 'gag_settings_handler' ) ):
	class gag_settings_handler
	{
		public static $namespace = 'ga-germanized';
		public static $version = 'v1';
		public static $prefix = 'gag_';
		public static $ep_save_settings = 'save-settings';

		public static function init()
		{
			add_action(
				'rest_api_init',
				array(
					__CLASS__,
					'rest_api'
				)
			);
		}

		public static function rest_api()
		{
			register_rest_route( self::$namespace.'/'.self::$version, '/'.self::$ep_save_settings, array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array(__CLASS__, 'save_settings'),
				'permission_callback' => array(__CLASS__, 'permission_callback')
			));
		}

		public static function permission_callback()
		{
			return current_user_can('manage_options');
		}

		public static function settings()
		{
			return array(
			    /**
			     * Main Settings
                 * *************************************
			     */
				'analytics-id' => array(
					'name'      => 'analytics-id',
					'default'   => '',
					'required'  => true,
					'min_l'     => 8,
					'max_l'     => 16
				),

				'analytics-mode' => array(
					'name'      => 'analytics-mode',
					'default'   => 'gst',
					'required'  => true,
					'min_l'     => 2,
					'max_l'     => 3,
					'values'    => array(
						'gst',
						'ua'
					)
				),

                /**
                 * Advanced Settings
                 * *************************************
                 */
				'disable-analytics-integration' => array(
					'name'      => 'disable-analytics-integration',
					'default'   => 0,
					'required'  => false,
					'min_l'     => 0,
					'max_l'     => 1,
					'values'    => array(
						0,
						1
					)
				),

				'anonymize_ip' => array(
					'name'      => 'anonymize_ip',
					'default'   => 1,
					'required'  => false,
					'min_l'     => 0,
					'max_l'     => 1,
					'values'    => array(
						0,
						1
					)
				),

				'displayfeatures' => array(
					'name'      => 'displayfeatures',
					'default'   => 0,
					'required'  => false,
					'min_l'     => 0,
					'max_l'     => 1,
					'values'    => array(
						0,
						1
					)
				),

				'link-tracking' => array(
					'name'      => 'link-tracking',
					'default'   => 0,
					'required'  => false,
					'min_l'     => 0,
					'max_l'     => 1,
					'values'    => array(
						0,
						1
					)
				),

				'linkid' => array(
					'name'      => 'linkid',
					'default'   => 0,
					'required'  => false,
					'min_l'     => 0,
					'max_l'     => 1,
					'values'    => array(
						0,
						1
					)
				),

				'domain' => array(
					'name'      => 'domain',
					'default'   => '',
					'required'  => false,
					'min_l'     => 0,
					'max_l'     => 255
				),

				'custom-code' => array(
					'name'      => 'custom-code',
					'default'   => '',
					'required'  => false,
					'min_l'     => 0,
					'max_l'     => 500
				),

                'ga-dnt' => array(
					'name'      => 'ga-dnt',
					'default'   => 0,
					'required'  => false,
					'min_l'     => 0,
					'max_l'     => 1,
					'values'    => array(
						0,
						1
					)
				),

                /**
                 * Cookie Consent
                 * *************************************
                 */

                'disable-cookie-notice' => array(
                    'name'      => 'disable-cookie-notice',
                    'default'   => 0,
                    'required'  => false,
                    'min_l'     => 0,
                    'max_l'     => 1,
                    'values'    => array(
                        0,
                        1
                    )
                ),

				'compliance-type' => array(
                    'name'      => 'compliance-type',
                    'default'   => 'note',
                    'required'  => true,
                    'min_l'     => 4,
                    'max_l'     => 8
                ),

                'cc-position' => array(
                    'name'      => 'cc-position',
                    'default'   => 'bottom',
                    'required'  => true,
                    'values'    => array(
                        'bottom',
                        'top',
                        'top-pushed',
                        'bottom-left',
                        'bottom-right'
                    )
                ),

                'cc-layout' => array(
                    'name'      => 'cc-layout',
                    'default'   => 'block',
                    'required'  => true,
                    'values'    => array(
                        'block',
                        'edgeless',
                        'classic'
                    )
                ),

                'cc-banner-text' => array(
                    'name'      => 'cc-banner-text',
                    'default'   => __('This website uses cookies to ensure you get the best experience on our website.', 'ga-germanized'),
                    'required'  => false,
                    'min_l'     => 0,
                    'max_l'     => 500
                ),

                'cc-button-text' => array(
                    'name'      => 'cc-button-text',
                    'default'   => __('Got it!', 'ga-germanized'),
                    'required'  => false,
                    'min_l'     => 0,
                    'max_l'     => 65
                ),

				'cc-accept-button-text' => array(
                    'name'      => 'cc-accept-button-text',
                    'default'   => __('Allow Cookies', 'ga-germanized'),
                    'required'  => false,
                    'min_l'     => 0,
                    'max_l'     => 65
                ),

				'cc-deny-button-text' => array(
                    'name'      => 'cc-deny-button-text',
                    'default'   => __('Refuse Cookies', 'ga-germanized'),
                    'required'  => false,
                    'min_l'     => 0,
                    'max_l'     => 65
                ),

                'enable-policy-link' => array(
                    'name'      => 'enable-policy-link',
                    'default'   => 0,
                    'required'  => false,
                    'min_l'     => 0,
                    'max_l'     => 1,
                    'values'    => array(
                        0,
                        1
                    )
                ),

                'cc-policy-link-text' => array(
                    'name'      => 'cc-policy-link-text',
                    'default'   => __('Learn more', 'ga-germanized'),
                    'required'  => false,
                    'min_l'     => 0,
                    'max_l'     => 100
                ),

                'cc-policy-link' => array(
                    'name'      => 'cc-policy-link',
                    'default'   => __('https://cookiesandyou.com/', 'ga-germanized'),
                    'required'  => false,
                    'min_l'     => 0,
                    'max_l'     => 500
                ),

                'cc-banner-background-color' => array(
                    'name'      => 'cc-banner-background-color',
                    'default'   => '#edeff5',
                    'required'  => false,
                    'min_l'     => 4,
                    'max_l'     => 7
                ),

                'cc-banner-text-color' => array(
                    'name'      => 'cc-banner-text-color',
                    'default'   => '#838391',
                    'required'  => false,
                    'min_l'     => 4,
                    'max_l'     => 7
                ),

                'cc-button-background-color' => array(
                    'name'      => 'cc-button-background-color',
                    'default'   => '#4b81e8',
                    'required'  => false,
                    'min_l'     => 4,
                    'max_l'     => 7
                ),

                'cc-button-text-color' => array(
                    'name'      => 'cc-button-text-color',
                    'default'   => '#ffffff',
                    'required'  => false,
                    'min_l'     => 4,
                    'max_l'     => 7
                ),

				/**
				 * Other Tracking Codes
				 * *************************************
				 */

				'custom-tracker-head' => array(
					'name'      => 'custom-tracker-head',
					'default'   => '',
					'required'  => false
				),

				'custom-tracker-footer' => array(
					'name'      => 'custom-tracker-footer',
					'default'   => '',
					'required'  => false
				),

				'other-tracking-compliance' => array(
					'name'      => 'other-tracking-compliance',
					'default'   => 0,
					'required'  => false,
					'min_l'     => 0,
					'max_l'     => 1,
					'values'    => array(
						0,
						1
					)
				),

				'custom-tracker-dnt' => array(
					'name'      => 'custom-tracker-dnt',
					'default'   => 0,
					'required'  => false,
					'min_l'     => 0,
					'max_l'     => 1,
					'values'    => array(
						0,
						1
					)
				),
			);
		}

		public static function save_settings( WP_REST_Request $request )
		{
			@error_reporting(0);

			$settings = self::settings();

			foreach ($settings as $setting) {
				$setting_name = $setting['name'];

				if( isset($_POST[ $setting_name ]) ) {
					$value = trim($_POST[ $setting_name ]);
				} else {
					$value = null;
				}

				$validate = self::validate( $value, $setting );

				if( is_wp_error($validate) ) {
					return array(
						'status' => false,
						'message' => sprintf(
							__('Field "%s": %s', 'ga-germanized'),
							$setting_name,
							$validate->get_error_message()
						)
					);
				} elseif( $validate ) {
					update_option( self::$prefix.$setting_name, $value, false );
				}
			}

			return array(
				'status' => true,
				'message' => esc_attr__('Settings saved!')
			);
		}

		public static function current_settings()
		{
			$settings = self::settings();

			$current_settings = array();

			foreach ($settings as $setting) {
				$setting_name = $setting['name'];

				$current_settings[ $setting_name ] = stripslashes(get_option(self::$prefix.$setting_name, $setting['default']));

				if( ! is_bool($current_settings[ $setting_name ]) && ! is_numeric($current_settings[ $setting_name ]) && empty($current_settings[ $setting_name ]) ) {
                    $current_settings[ $setting_name ] = $setting['default'];
                }
			}

			$current_settings = apply_filters('gag-settings', $current_settings);

			return $current_settings;
		}

		public static function validate($value, $validation_rule)
		{
			$value = trim($value);
			$length = mb_strlen($value);

			if( isset($validation_rule['required']) ) {
				if( $validation_rule['required'] == true && empty($value) ) {
					return new WP_Error( 'error', __('setting is required', 'ga-germanized') );
				}
			}

			if( isset($validation_rule['min_l']) ) {
				if( is_numeric($validation_rule['min_l']) && $length < $validation_rule['min_l'] && !empty($value) ) {
					return new WP_Error( 'error',
						sprintf(
							__('min length is %d characters, current length is %d', 'ga-germanized'),
							$validation_rule['min_l'],
							$length
						)
					);
				}
			}

			if( isset($validation_rule['max_l']) ) {
				if( is_numeric($validation_rule['max_l']) && $length > $validation_rule['max_l'] && !empty($value) ) {
					return new WP_Error( 'error',
						sprintf(
							__('max length is %d characters, current length is %d', 'ga-germanized'),
							$validation_rule['max_l'],
							$length
						)
					);
				}
			}

			if( isset($validation_rule['values']) ) {
				if( is_array($validation_rule['values']) && ! in_array($value, $validation_rule['values']) ) {
					return new WP_Error( 'error', __('unknown value', 'ga-germanized') );
				}
			}

			return true;
		}

		public static function delete_options()
		{
			$settings = self::settings();

			foreach ($settings as $setting) {
				$setting_name = $setting['name'];

				delete_option( self::$prefix.$setting_name );
			}
		}
	}
endif;