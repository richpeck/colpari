<?php


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}



require_once WPAS_CBOT_PATH.'vendor/autoload.php';

use ApiAi\Client;



class WPAS_CBOT_API_AI {
	
	protected $text;
	
	
	public function __construct( $text ) {
		$this->text = $text;
	}
	
	
	
	/**
	 * 
	 * @param type $text
	 * 
	 * @return type
	 */
	public static function Response( $text ) {
		
		$ai = new self( $text );
		
		return $ai->getResponse();
	}
	
	/**
	 * Send user message to api.ai and receive a reply
	 * 
	 * @return string
	 */
	public function getResponse() {
		
		$client_access_token = wpas_get_option( 'cbot_api_ai_client_access_token' );
		
		if ( empty( $client_access_token ) ) {
			return '';
		}
		
		$responseData = array();
		
		try {
			$client = new Client( $client_access_token );
			
			$query = $client->get( 'query', [
			    'query'     => $this->text,
			    'sessionId' => time()
			] );
			
			
			$response = json_decode( (string)$query->getBody(), true );
			
			if( $this->is_response_success( $response ) ) {
				$responseData = $this->prepareResponse( $response );
			}
			
		} catch ( \Exception $error ) {
			wpas_cbot_save_log( "Error Getting API.AI Response : " . $error->getMessage() );
		}
		
		return $responseData;
		
	}
	
	
	/**
	 * Get a text message from api.ai response
	 * 
	 * @param array $response
	 * 
	 * @return string
	 */
	public function prepareResponse( $response ) {
		
		$responseText = "";
		
		$possibleReplies = array();
		
		
		$responseData = array();
		
		if( $this->is_response_success( $response ) ) {
			
			$fulfillment = $response['result']['fulfillment'];
			
			$data = isset( $fulfillment['data'] ) ? $fulfillment['data'] : array();
			
			if( isset( $data['facebook'] ) && isset( $data['facebook']['links'] ) ) {
				$responseData['type'] = 'buttons';
				$responseData['buttons'] = $data['facebook'];
			} else {
				
				$messages = isset( $fulfillment['messages'] ) ? $fulfillment['messages'] : array();


				if( isset( $fulfillment['speech'] ) ) {
					$possibleReplies[] = $fulfillment['speech'];
				}

				foreach( $messages as $msg ) {

					if( isset( $msg['speech'] ) && $msg['speech'] && !in_array( $msg['speech'], $possibleReplies ) ) {
						$possibleReplies[] = $msg['speech'];
					}
				}

				if( !empty( $possibleReplies ) ) {
					$responseData['type'] = 'text';
					$responseData['text'] = wpas_cbot_get_single_message( $possibleReplies );
				}
				
			}
		}
		
		wpas_cbot_save_log( 'API.AI prepared response for facebook chatbot : ' . json_encode( $responseData ) );
		
		return $responseData;
	}
	
	
	
	/**
	 * Check if api.ai returned a success response
	 * 
	 * @param type $response
	 * 
	 * @return boolean
	 */
	public function is_response_success( $response ) {
		
		if( isset( $response['status'] ) ) {
			if( 200 === $response['status']['code'] && 'success' === $response['status']['errorType'] ) {
				return true;
			}
		}
		
		return false;
	}
	
	
}