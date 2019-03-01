<?php

/*
 * @package   Awesome Support: Private Credentials
 * @author    Robert W. Kramer III for Awesome Support <support@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016. Awesome Support
 *
 */

class UnsafeCrypto {

	const METHOD = 'aes-256-ctr';


	/**
	 * Encrypts (but does not authenticate) a message
	 *
	 * @param string       $message - plaintext message
	 * @param string|false $key     - encryption key (raw binary expected)
	 * @param boolean      $encode  - set to TRUE to return a base64-encoded
	 *
	 * @return string (raw binary)
	 */
	public static function encrypt( $message, $key, $encode = true ) {
		$nonceSize = openssl_cipher_iv_length( self::METHOD );
		$nonce     = openssl_random_pseudo_bytes( $nonceSize );

		$ciphertext = openssl_encrypt(
			$message, self::METHOD, $key, OPENSSL_RAW_DATA, $nonce
		);

		// Now let's pack the IV and the ciphertext together
		// Naively, we can just concatenate
		if ( $encode ) {
			return base64_encode( $nonce . $ciphertext );
		}

		return $nonce . $ciphertext;

	}


	/**
	 * Decrypts (but does not verify) a message
	 *
	 * @param string       $message - ciphertext message
	 * @param string|false $key     - encryption key (raw binary expected)
	 * @param boolean      $encoded - are we expecting an encoded string?
	 *
	 * @return false|string
	 */
	public static function decrypt( $message, $key, $encoded = true ) {

		if ( $message == '' ) {
			return '';
		}

		if ( $encoded ) {
			$message = base64_decode( $message, true );
			if ( $message === false ) {
				return false;
			}
		}

		$nonceSize  = openssl_cipher_iv_length( self::METHOD );
		$nonce      = mb_substr( $message, 0, $nonceSize, '8bit' );
		$ciphertext = mb_substr( $message, $nonceSize, null, '8bit' );

		$plaintext = '';

		try {
			$plaintext = openssl_decrypt( $ciphertext, self::METHOD, $key, OPENSSL_RAW_DATA, $nonce );
		} catch ( Exception $e ) {
			return false;
		}

		return $plaintext;

	}

}
