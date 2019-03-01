<?php
/**
 * @package   Awesome Support E-Mail Support/Mailbox
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016 Awesome Support
 */

use Zend\Mail\Storage\Pop3;
use Zend\Mail\Storage\Imap;

/**
 * Class AS_Mailbox
 *
 * This class is a wrapper for the Zend Framework email class. AS Email addon relies on Zend Mail to retrieve emails
 * from an email server through either POP or IMAP.
 *
 * @since 0.5.0
 */
class ASES_Mailbox {

	/**
	 * Holds the mailbox instance.
	 *
	 * @since 0.5.0
	 * @var null|Zend\Mail\Storage\Pop3|Zend\Mail\Storage\Imap|WP_Error
	 */
	public $mailbox;

	/**
	 * Remote email server's address.
	 *
	 * @since 0.5.0
	 * @var string
	 */
	private $server;

	/**
	 * Protocol to use for opening the mailbox.
	 *
	 * @since 0.5.0
	 * @var string
	 */
	private $protocol;

	/**
	 * Mailbox username.
	 *
	 * @since 0.5.0
	 * @var string
	 */
	private $username;

	/**
	 * Mailbox password.
	 *
	 * @since 0.5.0
	 * @var string
	 */
	private $password;

	/**
	 * Server port in use for the protocol specified.
	 *
	 * @since 0.5.0
	 * @var mixed
	 */
	private $port;

	/**
	 * Type of secure connection to use (ssl, tls, none)
	 *
	 * @since 0.5.0
	 * @var bool
	 */
	private $secure;

	/**
	 * Constructor
	 *
	 * @param string $inserver address or name of mail server
	 * @param string $insprotocol inbox protocol ("imap" or "pop3")
	 * @param string $inusername mailbox login user name
	 * @param string $inpassword mailbox password
	 * @param string $inport port to connect on
	 * @param string $insecure secure port protocol
	 * @param array  $inother for future use in case we need additional prameters
	 *
	 */
	public function __construct($inserver, $inprotocol, $inusername, $inpassword, $inport, $insecure, $inother = array()) {

		// Get email server address.
		$this->server = $inserver;
		if ( empty( $this->server ) ) {
			$this->server = wpas_get_option( 'email_server', '' );
		} 

		// Get protocol.
		$this->protocol = $inprotocol;
		if ( empty( $this->protocol ) ) {
			$this->protocol = wpas_get_option( 'email_protocol', '' );
		}

		// Get mailbox username.
		$this->username = $inusername;
		if ( empty ( $this->username ) ) {
			$this->username = wpas_get_option( 'email_username', '' );
		}

		// Get mailbox password.
		$this->password = $inpassword;
		if ( empty( $this->password ) ) {
			$this->password = wpas_get_option( 'email_password', '' );
		}

		// Get mailbox port.
		$this->port = $inport ;
		if ( empty( $this->port ) ) {
			$this->port = wpas_get_option( 'email_port', null );
		}

		// Get mailbox security.
		$this->secure = $insecure;
		if ( true === is_null( $this->secure ) ) {
			// If no value was passed in then get the SECURE option value from the TICKETS->SETTINGS->Email Piping settings instead...	
			$this->secure =  wpas_get_option( 'email_secure', 'ssl' );
		}

		// Get the mailbox.
		$this->mailbox();
		
	}

	/**
	 * Connect to the mailbox.
	 *
	 * Use the Zend Framework email class to fetch emails from the client's mailbox.
	 *
	 * @since 0.5.0
	 * @link  https://github.com/zendframework/zend-mail
	 * @return Zend\Mail\Storage\Pop3|Zend\Mail\Storage\Imap|WP_Error
	 */
	protected function mailbox() {

		// Prepare the mailbox arguments.
		// Arguments are the same for both POP and IMAP so let's prepare them beforehand and pass them to the appropriate class.		
		$sslvalue = false;
		switch ( $this-> secure ) {
			case null :
				$sslvalue = false ;
				break ;
			case false :
				$sslvalue = false ;
				break ;
			case 'ssl' :
				$sslvalue = 'ssl' ;
				break ;			
			case 'tls' :
				$sslvalue = 'tls' ;
				break ;
			case 'none' :
				$sslvalue = false ;
				break ;
			case true :
				$sslvalue = 'ssl' ;
				break ;				
			default :
				$sslvalue = false ;
		}

		$args = array(
			'host'     => $this->server,
			'user'     => $this->username,
			'password' => $this->password,
			'port'     => $this->port,
			'ssl'      => $sslvalue,
		);

		switch ( $this->protocol ) {
			case 'imap':
				try {
					$this->mailbox = new Imap( $args );
				} catch ( Exception $e ) {
					$this->mailbox = new WP_Error( 'connection_error', $e->getMessage() );
				}
				break;

			case 'pop3':
				try {
					$this->mailbox = new Pop3( $args );
				} catch ( Exception $e ) {
					$this->mailbox = new WP_Error( 'connection_error', $e->getMessage() );
				}
				break;

			default:
				return new WP_Error( 'unknown_protocol', esc_attr__( 'You are trying to use an unsupported protocol for retrieving emails', 'as-email-support' ) );
		}

		return $this->mailbox;

	}

	/**
	 * Get the mailbox instance.
	 *
	 * @since 0.5.0
	 * @return null|WP_Error|Imap|Pop3
	 */
	public function get_mailbox() {
		return is_null( $this->mailbox ) ? $this->mailbox() : $this->mailbox;
	}

	/**
	 * Get the count of emails retrieved from the server.
	 *
	 * @since 0.5.0
	 * @return int
	 */
	public function get_emails_count() {

		if ( is_wp_error( $this->get_mailbox()) ) {
			return 0;
		}

		return $this->get_mailbox()->countMessages();

	}

	/**
	 * Get emails from the remote server.
	 *
	 * @since 0.5.0
	 * @return array
	 */
	public function get_emails() {

		// Array of emails that we're going to retrieve from the mail server.
		$mails = array();

		// If there is no email, well, here is your empty array.
		if ( 0 === $this->get_emails_count() ) {
			return $mails;
		}

		// Fetch all available emails.
		foreach ( $this->get_mailbox() as $email ) {
			$mails[] = $email;
		}

		return $mails;

	}

	/**
	 * Delete a message from the server.
	 *
	 * @since 0.5.0
	 *
	 * @param int $id Message ID
	 *
	 * @return void|WP_Error
	 */
	public function delete_message( $id ) {
		if ( ! defined( 'ASES_DELETE_MAIL_AFTER_FETCH' ) || defined( 'ASES_DELETE_MAIL_AFTER_FETCH' ) && true === ASES_DELETE_MAIL_AFTER_FETCH ) {
			try {
				$this->mailbox->removeMessage( $id );
			} catch ( Exception $e ) {
				wpas_write_log( 'emails', $e->getMessage() );
			}
		}
	}

}