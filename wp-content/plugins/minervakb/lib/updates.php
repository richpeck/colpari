<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_EnvatoApi {

	static $api_token = 'jQkiFq8hqnJWxG1tAlrTquD5esNOgZNX';

	static function getPurchaseData( $code ) {
		$verify_url = 'https://api.envato.com/v1/market/private/user/verify-purchase:' . $code . '.json';

		$request = wp_remote_get( $verify_url, array(
			'headers' => array(
				'Authorization' => 'Bearer ' . self::$api_token
			)
		));

		if (is_wp_error($request)) {
			return false;
		}

		$body = wp_remote_retrieve_body($request);

		try {
			$decoded_response = json_decode($body);
		} catch (Exception $e) {
			return false;
		}

		return $decoded_response;
	}

	public static function verify( $code ) {
		$verify_obj = self::getPurchaseData($code);

		return false !== $verify_obj &&
		       is_object($verify_obj) &&
		       isset($verify_obj->{"verify-purchase"}) &&
		       isset($verify_obj->{"verify-purchase"}->item_name);
	}
}

class MinervaKB_AutoUpdate {

	private $info;

	private $update_checker = null;

	private static $VERIFY_TRANSIENT_TTL = MONTH_IN_SECONDS;

	private static $VERIFY_TRANSIENT_KEY = 'mkb_purchase_verification_result';

	/**
	 * Init
	 */
	public function __construct($deps) {
		$this->setup_dependencies($deps);

		if (self::verify_purchase()) {
			$this->setup_updates();
		}
	}

	/**
	 * Sets up dependencies
	 * @param $deps
	 */
	private function setup_dependencies($deps) {
		if (isset($deps['info'])) {
			$this->info = $deps['info'];
		}
	}

	public static function verify_purchase($force_request = false) {

		if (!MKB_Options::option('auto_updates_verification')) {
			return false;
		}

		$verification_result = get_transient( self::$VERIFY_TRANSIENT_KEY );

		if ($verification_result && !$force_request) {
			$verification_result = json_decode($verification_result, true);
		} else {
			$is_verified = false;

			try {
				$is_verified = MinervaKB_EnvatoApi::verify(MKB_Options::option('auto_updates_verification'));
			} catch (Exception $e) {
				$is_verified = false;
			}

			$verification_result = array(
				'is_verified' => $is_verified,
				'verification_date' => current_time( 'mysql' )
			);

			set_transient(self::$VERIFY_TRANSIENT_KEY, json_encode($verification_result), self::$VERIFY_TRANSIENT_TTL);
		}

		return $verification_result['is_verified'];
	}

	/**
	 * Activates auto updates
	 */
	private function setup_updates() {
		require_once(MINERVA_KB_PLUGIN_DIR . 'lib/vendor/plugin-update-checker/plugin-update-checker.php');

		$this->update_checker = Puc_v4_Factory::buildUpdateChecker(
			'https://support.konstruktstudio.com/wp-content/uploads/updates/minervakb/plugin.json',
			MINERVA_KB_PLUGIN_FILE,
			'minervakb'
		);
	}

	public static function registered_label() {
		$is_registered = self::verify_purchase();

		?>
		<span class="mkb-header-verification fn-mkb-header-verification mkb-header-verification--<?php
			esc_attr_e($is_registered ? 'registered' : 'not-registered'); ?>">
				<?php if ($is_registered): ?>
					<?php echo __( 'Registered', 'minerva-kb' ); ?>
				<?php else: ?>
					<?php echo __( 'Not registered', 'minerva-kb' ); ?>
					<span class="mkb-header-verification-tooltip js-mkb-tooltip"
					      data-tooltip="<?php esc_attr_e(__('Registration is optional, but you can provide your Envato Purchase Code in Settings - Registration / Updates to enable automatic updates.', 'minerva-kb')); ?>">?</span>
				<?php endif; ?>
			</span>
		<?php
	}
}