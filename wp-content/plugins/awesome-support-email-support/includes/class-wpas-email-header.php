<?php
/**
 * @package   Awesome Support Email Addon/Email Header
 * @author    Julien Liabeuf <julien@liabeuf.fr>
 * @license   GPL-2.0+
 * @link      https://julienliabeuf.com
 * @copyright 2017 Julien Liabeuf
 */

/**
 * Class WPAS_Email_Header
 *
 * Abstracting the way email headers is being read is somewhat helpful with backward compatibility. A helper function
 * will decide which class to use, but we abstract the core logic of the header class.
 *
 * @since 0.5.0
 */
abstract class WPAS_Email_Header {

	protected $headers;

	public function __construct( $headers ) {
		$this->headers = $headers;
	}

	abstract public function get( $value, $default = 'unknown' );

	/**
	 * Get the e-mail date
	 *
	 * Instead of using the current date as the reply date, try to get the date
	 * at which the e-mail was sent.
	 *
	 * @since 0.2.1
	 * @return string
	 */
	public function get_date() {
		return get_date_from_gmt( $this->get( 'date' ) );
	}

}