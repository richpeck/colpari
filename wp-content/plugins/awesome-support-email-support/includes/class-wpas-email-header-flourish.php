<?php

/**
 * @package   as-email-support
 * @author    Julien Liabeuf <julien@liabeuf.fr>
 * @license   GPL-2.0+
 * @link      https://julienliabeuf.com
 * @copyright 2017 Julien Liabeuf
 */
class WPAS_Email_Header_Flourish extends WPAS_Email_Header {

	public function get( $value, $default = 'unknown' ) {

		// Set output to default value in case the $value is unknown.
		$output = $default;

		switch ( $value ) {

			case 'sender_email':
				$output = $this->get_user_email();
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
	 * @since  0.5.0
	 * @return string User e-mail
	 */
	public function get_user_email() {

		$sender = '';

		if ( isset( $this->headers['sender'] ) ) {
			if ( isset( $this->headers['from'] ) ) {
				echo "{$this->headers['from']['mailbox']}@{$this->headers['from']['host']}";
			} else {
				$sender = "{$this->headers['sender']['mailbox']}@{$this->headers['sender']['host']}";
			}
		} else {
			$sender = "{$this->headers['from']['mailbox']}@{$this->headers['from']['host']}";
		}

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

		if ( isset( $this->headers['date'] ) ) {
			$timestamp = strtotime( $this->headers['date'] );
			$date      = date( 'Y-m-d H:i:s', $timestamp );
		}

		return $date;

	}

}