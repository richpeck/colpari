<?php

/**
 * @package   as-email-support
 * @author    Julien Liabeuf <julien@liabeuf.fr>
 * @license   GPL-2.0+
 * @link      https://julienliabeuf.com
 * @copyright 2017 Julien Liabeuf
 */
class WPAS_Email_Header_Zend extends WPAS_Email_Header {

	public function get( $value, $default = 'unknown' ) {

		// Set output to default value in case the $value is unknown.
		$output = $default;

		switch ( $value ) {

			case 'sender_email':
				$output = $this->get_user_email();
				break;

			case 'sender_name':

				break;

			case 'date':
				$output = $this->get_date_gmt();
				break;

		}

		return $output;

	}

	/**
	 * Get the sender email address by looking into the email headers.
	 *
	 * The Zend framework has a number of classes to work with email headers. See the documentation for more
	 * information about the classes and methods available.
	 *
	 * @since  0.1.0
	 * @link   https://framework.zend.com/apidoc/2.0/namespaces/Zend.Mail.html
	 * @return string User e-mail
	 */
	public function get_user_email() {

		// Try to get the email address from the standard location in the headers (pre-processed by Zend Mail).
		$sender = $this->headers->get( 'from', 'object' )->getAddressList()->current()->getEmail();

		// Just in case, we validate the email address and fall back on the return path if necessary.
		if ( false === filter_var( $sender, FILTER_VALIDATE_EMAIL ) ) {
			$sender = trim( $this->headers->get( 'Return-Path', 'string' ), '<>' );
		}

		return $sender;

	}
	
	/**
	 * Get the sender NAME by looking into the email headers.
	 *
	 * The Zend framework has a number of classes to work with email headers. See the documentation for more
	 * information about the classes and methods available.
	 *
	 * @since  5.0.1
	 * @link   https://framework.zend.com/apidoc/2.0/namespaces/Zend.Mail.html
	 * @return string senders name
	 */
	public function get_from_name() {

		// Try to get the from address from the standard location in the headers (pre-processed by Zend Mail).
		$sender = $this->headers->get( 'from', 'object' )->getAddressList()->current()->getName();

		return $sender;

	}	

	/**
	 * Get the e-mail date in GMT format
	 *
	 * Instead of using the current date as the reply date, try to get the date
	 * at which the e-mail was sent.
	 *
	 * @since 0.2.0
	 * @return string
	 */
	public function get_date_gmt() {

		$date = date( 'Y-m-d H:i:s' );

		if ( isset( $this->email->date ) ) {
			$timestamp = strtotime( $this->email->date );
			$date      = date( 'Y-m-d H:i:s', $timestamp );
		}

		return $date;

	}

}