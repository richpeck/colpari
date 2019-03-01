<?php


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


class WPAS_CBOT_Messaging {

	protected $sender;
	protected $message;
	protected $entry;
	protected $text;
	protected $plain_text;
	protected $postback;
	protected $payload_type;
	protected $page_access_token;
	protected $plugin;
	protected $last_request;
	protected $reply_sent = false;

	public $fb_api_base = 'https://graph.facebook.com/v2.6';
	public $messages_api;
	public $user_api;

	function __construct( $entry, $plugin, $send_200 = true ) {
		$this->plugin            = $plugin;
		$this->page_access_token = $plugin->get_page_access_token();
		$this->entry             = $entry;

		if ( isset( $entry['sender'] ) ) {
			$this->sender = $entry['sender'];
		}
		if ( isset( $entry['message'] ) ) {
			$this->message = $entry['message'];
			if ( isset( $entry['message']['text'] ) ) {
				$this->text = $entry['message']['text'];
				
				$this->plain_text = str_replace( array( '?', '.', '!' ), '', $this->text );
				
			}
			if ( isset( $entry['message']['quick_reply'] ) ) {
				$this->postback     = $entry['message']['quick_reply']['payload'];
				$this->payload_type = 'quick_reply';
			}
		}

		if ( isset( $entry['postback'] ) ) {
			$this->postback     = $entry['postback']['payload'];
			$this->payload_type = 'postback';
		}

		$this->messages_api = $this->fb_api_base . '/me/messages?access_token=' . urlencode( $this->page_access_token );
		$this->user_api     = $this->fb_api_base . '/' . $this->sender['id'] . '?access_token=' . urlencode( $this->page_access_token );
	}

	/**
	 * Make protected properties gettable so they're read-only
	 *
	 * @param $name
	 *
	 * @return mixed
	 * @throws Exception
	 */
	function __get( $name ) {
		if ( property_exists( $this, $name ) ) {
			return $this->{$name};
		}

		throw new Exception( "Can not get property: {$name}", 1 );
	}

	/**
	 * Attempts to send a 200 response to the requester before continuing execution to
	 * ensure that Facebook doesn't retry the webhook while we're processing. It is
	 *
	 * TODO: Appears not to work with WordPress, but this would be really nice to have
	 */
	protected function send_200_continue() {
		ob_start();
		echo '0';
		http_response_code( 200 );
		header( 'Content-Encoding: none' );
		header( 'Connection: close' );
		header( 'Content-Length: ' . ob_get_length() );
		ob_end_flush();
		flush();
		session_write_close();
	}

	/**
	 * Send data to the specified API URL and handle the response
	 *
	 * @param string $method POST or GET
	 * @param string $url
	 * @param null   $data
	 *
	 * @return mixed|WP_Error
	 */
	function api_send( $method, $url, $data = null ) {
		
		
			
		if ( ! in_array( strtolower( $method ), array( 'get', 'post' ) ) ) {
			return new WP_Error( 'wpfbbk_type_error', '$method must be one of \'get\', \'post\'' );
		}

		/**
		 * Opportunity to hijack API Requests.
		 *
		 * Ideal for sending requests via a queue rather than blocking execution by sending immediately.
		 *
		 * @param bool   $request_handled Return `true` to skip sending immediately via Requests API
		 * @param string $method          Request method (get or post)
		 * @param string $url             Request URL
		 * @param array  $data            Request data
		 *
		 */
		$request_handled = apply_filters( 'wpfbbk_before_send_request', false, $method, $url, $data );
		if ( false !== $request_handled ) {
			error_log( print_r( [ $request_handled ], 1 ) );

			return $request_handled;
		}
		
		$return = "";
		
		try {
			
			$req                = Requests::{$method}( $url, array(), $data );
			
			$this->last_request = $req;
			
			$decoded_body  = json_decode( $req->body );
			$response_body = $decoded_body ? $decoded_body : $req->body;
			
			if ( $req->success ) {
				$return = $response_body;
				
				$this->reply_sent = true;
				
			} else {
				$return = new WP_Error( 'wpfbbk_fb_api_error', $response_body );
			}
		} catch (Exception $ex) {
			wpas_cbot_save_log( "Error Sending message to fb cbot : " . $ex->getMessage() . " | data : " . json_encode( $data ) );
		}
		
		
		return $return;
	}

	/**
	 * Send reply to current sender via Messages API.
	 *
	 * @param array $message       formatted message to send to current sender
	 * @param bool  $set_typing_on Should 'typing_on' action be sent after message to indicate further messages will be sent
	 *
	 * @return mixed|WP_Error
	 */
	function reply( $message, $set_typing_on = false ) {
		
		
		$return = $this->api_send( 'post', $this->messages_api, array( 'message' => $message,' recipient' => $this->sender ) );

		if ( $set_typing_on && true === $return ) {
			$this->set_typing_on();
		}

		return $return;
	}

	/**
	 * Sends the 'typing_on' action to the current sender to show that bot is working/typing.
	 * Typically indicates that more messages will be sent.
	 *
	 * @return mixed|WP_Error
	 */
	function set_typing_on() {
		return $this->reply( array(
			'sender_action' => 'typing_on',
		), false );
	}

	/**
	 * Sends a plain text reply to current sender.
	 *
	 * @param string $text          message to send
	 * @param bool   $set_typing_on Should 'typing_on' action be sent after message to indicate further messages will be sent
	 *
	 * @return mixed|WP_Error
	 */
	function reply_with_text( $text, $set_typing_on = false ) {
		return $this->reply( array( 'text' => $text ), $set_typing_on );
	}

	/**
	 * Sends an image to current sender. Image url must be HTTPS with valid cert.
	 * Todo: add is_reusable param and handle attachment_id response
	 *
	 * @param string $url           Valid HTTPS url of image (or,
	 * @param bool   $set_typing_on Should 'typing_on' action be sent after message to indicate further messages will be sent
	 *
	 * @return mixed|WP_Error
	 */
	function reply_with_image_url( $url, $set_typing_on = false ) {
		return $this->reply( array(
			'attachment' => array(
				'type'    => 'image',
				'payload' => array( 'url' => $url ),
			),
		), $set_typing_on );
	}

	/**
	 * Sends a button or multiple buttons along with text message to current sender
	 *
	 * @param string $text          Text message that will preceed buttons
	 * @param array  $buttons       Array of buttons as defined at
	 *                              https://developers.facebook.com/docs/messenger-platform/send-api-reference/buttons
	 * @param bool   $set_typing_on Should 'typing_on' action be sent after message to indicate further messages will
	 *                              be sent
	 *
	 * @return mixed|WP_Error
	 */
	function reply_with_buttons( $text = '', $buttons, $set_typing_on = false ) {
		return $this->reply( array(
			'attachment' => array(
				'type' => 'template',
				'payload' => array(
					'template_type' => 'button',
					'buttons'       => $buttons,
					'text'          => $text,
				),
			),
		), $set_typing_on );
	}

	/**
	 * Sends a "generic template" to current sender. Specifically a version of the generic template with a clickable
	 * image that leads to aa url An array of buttons can also be sent with additional links or postback actions
	 *
	 * @param string $title         Title of card that will be overlaid on top of image in large text
	 * @param string $subtitle      Subtitle, will be smaller text just below title
	 * @param string $image         Image url or reusable attachment_id of image for card
	 * @param string $url           Web url for card link
	 * @param null   $buttons       Array of buttons as defined at
	 *                              https://developers.facebook.com/docs/messenger-platform/send-api-reference/buttons
	 * @param bool   $set_typing_on Should 'typing_on' action be sent after message to indicate further messages will
	 *                              be sent
	 *
	 * @return mixed|WP_Error
	 */
	function reply_with_generic_template_link( $title, $subtitle, $image, $url, $buttons = null, $set_typing_on = false ) {
		$reply = array(
			'attachment' => array(
				'type'    => 'template',
				'payload' => array(
					'template_type' => 'generic',
					'elements'      => array(
						array(
							'title'          => $title,
							'subtitle'       => $subtitle,
							'image_url'      => $image,
							'default_action' => array(
								'type' => 'web_url',
								'url'  => $url,
							),
							'buttons'        => $buttons,
						),
					),
				),
			),
		);

		return $this->reply( $reply, $set_typing_on );
	}

	/**
	 * Sends a request to the Facebook User API to request more information about current sender.
	 * Fields are not guaranteed to be populated, so it is recommended to check that values
	 * are set and provide fallback text when utilizing user info in messages.
	 *
	 * @param string $fields
	 *
	 * @return stdClass|WP_Error
	 */
	function get_user_info( $fields = 'all' ) {
		if ( 'all' === $fields ) {
			$fields = 'first_name,last_name,profile_pic,locale,timezone,gender';
		}

		return $this->api_send( 'get', $this->user_api . '&fields=' . urlencode( $fields ) );
	}


	/**
	 *  Reply back if a message matched with a keyword or links
	 */
	public function maybe_send_reply() {
		
		$this->plugin->save_log( 'Matching keywords.' );
		
		$matched_keyword_text = WPAS_CBOT_Keyword::getResponseText( $this->plain_text );
		
		if( $matched_keyword_text ) {
			$this->plugin->save_log( 'Keyword matched and a reply is sending with keyword content.' );
			$this->reply_with_text( $matched_keyword_text );
			$this->plugin->save_log( 'Keyword content sent to fb bot.' );
		} else {
			
			$this->plugin->save_log( 'No Keyword matched.' );
			
			if( wpas_get_option( 'cbot_enable_api_ai', false ) ) {
				$this->plugin->save_log( 'API.AI is enabled and we are sending request to API.AI for reply content.' );
				try {
					$this->send_API_AI_reply( $this->plain_text );
				} catch (Exception $ex) {
					$this->plugin->save_log( 'send_API_AI_reply Failed.' );
				}
				
			} else {
				
				if( wpas_cbot_get_option( 'cbot_enable_gnl' ) ) {
					
					$this->plugin->save_log( 'sending search links with gnl keywords.' );
					$links_data = WPAS_CBOT_Google_Natural_Language::search_posts( $this->plain_text );
					
				} else {
					$links_data = wpas_cbot_search_posts( $this->plain_text );
				}
				
				if( !empty( $links_data ) ) {
					$this->send_links_reply( $links_data );
				}
				
			}

		}
		
		if( !$this->reply_sent ) {
			$this->send_fallback_reply();
		}
	}
	
	/**
	 * Get message from api.ai and send text reply
	 * 
	 * @param type $text
	 */
	public function send_API_AI_reply( $text, $params = array() ) {
		
		

		$api_ai = new WPAS_CBOT_API_AI( $text );

		$ai_response = $api_ai->getResponse( $params );

		if( isset( $ai_response['type'] ) ) {
			if( 'text' === $ai_response['type'] ) {
				$this->reply_with_text( $ai_response['text'] );
			} elseif( 'buttons' === $ai_response['type'] ) {

				$this->send_search_page_button( $text );
			}
		}
		
	}
	
	/**
	 * Send a reply with links
	 * 
	 * @param string $text
	 * @param int $page
	 */
	function send_links_reply( $data ) {
		
		if( !empty( $data['links'] ) ) {
			$this->plugin->save_log( 'sending search links : ' . json_encode( $data ) );
			
			$links_text =  isset( $data['links_text'] ) ? $data['links_text'] : wpas_cbot_links_text();
			
			$res = $this->reply_with_buttons( $links_text, $data['links'] );
			
			
			if( is_wp_error( $res ) ) {
				$this->plugin->save_log( 'search links sending failed' );
			} else {
				$this->plugin->save_log( 'search links sent' );
				
				// Send more links button if we found next page
				if( isset( $data['next_page_payload'] ) && $data['next_page_payload'] ) {
					$this->send_more_links_button( $data['next_page_payload'] );
				}
			}
			
		}
	}
	
	/**
	 * Send a more... button after links
	 * 
	 * @param string $text
	 * @param int $page
	 */
	public function send_more_links_button( $payload ) {
		
		$button = array ( array( 
			"type" => "postback",
			"title" => __( "More...", 'wpas_chatbot' ),
			"payload" => $payload
			)
		);
		
		
		$res = $this->reply_with_buttons( __( 'Do you want to see more links ?', 'wpas_chatbot' ), $button );
		
		if( is_wp_error($res) ) {
			$this->plugin->save_log( 'postback button sending failed : ' . $payload );
		} else {
			$this->plugin->save_log( 'postback button sent : ' . $payload );
		}
	}
	
	/**
	 * Send a more... button after links
	 * 
	 * @param string $text
	 * @param int $page
	 */
	public function send_search_page_button( $text ) {
		
		
		$url = add_query_arg( array( 'question' => $text ), get_permalink( wpas_cbot_get_links_search_page() ) );
		
		$button = array(
		    array(
			    'type' => "web_url",
			    'url' => $url,
			    'title' => wpas_cbot_apiai_search_links_page_anchor_text()
			    )
		);
		
		
		$res = $this->reply_with_buttons( wpas_cbot_apiai_search_links_page_info_text(), $button );
		
	}
	
	/**
	 * Handle request once a user click postback button
	 */
	public function handle_post_back() {
		
		$this->plugin->save_log( 'postback payload : ' . $this->postback );
		if( 'wpas_cbot_get_more_links:::' === substr( $this->postback, 0, 27 ) ) {
			
			$parts = explode( ':::', $this->postback );
			$page = isset( $parts[1] ) ? $parts[1] : '';
			$text = $parts[2];
			
			if( wpas_cbot_get_option( 'cbot_enable_gnl' ) ) {
				$this->plugin->save_log( 'sending search links with gnl keywords.' );
				$links_data = WPAS_CBOT_Google_Natural_Language::search_posts( $text, $page );
			} else {
				$links_data = wpas_cbot_search_posts( $text, $page );
			}
			
			if( !empty( $links_data ) ) {
				$this->send_links_reply( $links_data );
			}
			
		}
		
	}
	
	/*
	 * Send a fallback message if no post or keyword matched
	 */
	public function send_fallback_reply() {
		$text = wpas_sc_fallback_message();
		
		if( $text ) {
			
			try{
				$this->reply_with_text( $text );
				
				$this->plugin->save_log( 'Fallback message sent.' );
			} catch (Exception $ex) {
				$this->plugin->save_log( 'Fallback message sending failed.' );
			}
		}
	}
}
