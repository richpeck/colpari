<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NF_FU_Helper
 *
 * The Static Helper Class
 *
 * Provides helper functionality for File Uploads
 */
final class NF_FU_Helper {

	public static function random_string( $length = 10 ) {
		$characters    = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$random_string = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$random_string .= $characters[ rand( 0, strlen( $characters ) - 1 ) ];
		}

		return $random_string;
	}

	/**
	 * Format a number to MBs
	 *
	 * @param int  $val
	 * @param bool $return_int
	 *
	 * @return int|string
	 */
	public static function format_mb( $val, $return_int = false ) {
		$val  = trim( $val );
		$last = strtolower( $val[ strlen( $val ) - 1 ] );
		switch ( $last ) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
				break;
			case 'k':
				$val /= 1024;
				break;
		}

		$val = rtrim( strtoupper( $val ), 'M' ) . 'M';

		if ( $return_int ) {
			$val = substr( $val, 0, -1 );
		}

		return $val;
	}

	/**
	 * Are we on the FU page?
	 *
	 * @param string $tab
	 * @param array  $args
	 *
	 * @return bool
	 */
	public static function is_page( $tab = '', $args = array() ) {
		global $pagenow;

		if ( 'admin.php' !== $pagenow ) {
			return false;
		}

		$defaults = array( 'page' => 'ninja-forms-uploads' );

		if ( $tab ) {
			$defaults['tab'] = $tab;
		}

		$args = array_merge( $args, $defaults );

		foreach ( $args as $key => $value ) {
			if ( ! isset( $_GET[ $key ] ) ) {
				return false;
			}

			if ( false !== $value && $value !== $_GET[ $key ] ) {
				return false;
			}
		}

		return true;
	}

} // End Class WPN_Helper
