<?php

class WPAS_SC_ChatBot {
	
	
	private $message;
	
	private $type;
	
	public function __construct( $message = '', $type = '' ) {
		$this->message = $message;
		
		$this->type = $type;
	}
	
	
	
	/**
	 *  Reply back if a message matched with a keyword or links
	 */
	public function get_reply() {
		
		//$this->plugin->save_log( 'Matching keywords.' );
		
		$reply_message = WPAS_CBOT_Keyword::getResponseText( $this->message, $this->type );
		
		
		
		
		if( empty( $reply_message ) )  {
			
			if( wpas_get_option( 'cbot_enable_api_ai', false ) ) {
				
				$ai_response = WPAS_CBOT_API_AI::Response( $this->message );
				

				if( isset( $ai_response['type'] ) && ( 'text' === $ai_response['type'] || 'buttons' === $ai_response['type'] ) ) {
					
					$response_text = $this->wpas_sc_buttons_reply_content( $ai_response );
					if( $response_text ) {
						
						$reply_message = $ai_response;
						$reply_message['origin'] = 'api_ai';
						$reply_message['text'] = $response_text;
					}
					
				}
			} else {
				
				
				if( wpas_cbot_get_option( 'cbot_enable_gnl' ) ) {
					$origin = 'gnl_post_links';
					
					$search_results = WPAS_CBOT_Google_Natural_Language::search_posts( $this->message, 1, 25 );
					
				} else {
					$origin = 'post_links';
					$search_results = wpas_cbot_search_posts( $this->message, 1, 25 );
				}
				
				if( !empty( $search_results ) ) {
					
					
					$response_text = $this->wpas_sc_buttons_reply_content( $search_results );
					if( $response_text ) {
						$reply_message = $search_results;
						$reply_message['origin'] = $origin;
						$reply_message['type'] = 'buttons';
						$reply_message['text'] = $response_text;
					}
				}
				
				
			}
			
		} 
		
		
		
		
		if( empty( $reply_message ) ) {
			$reply_message = array(
			    'type' => 'text',
			    'origin' => 'fallback',
			    'text' => wpas_sc_fallback_message( $this->type ),
			);
		}
		
		
		return $reply_message;
		
	}
	
	
	
	public function wpas_sc_buttons_reply_content( $reply ) {
		
		$reply_message = "";
		$links_text = "";
		$links = array();
		
		if( isset( $reply['buttons'] ) && isset( $reply['buttons']['links'] ) ) {
			
			$links = $reply['buttons']['links'];
			$links_text = isset( $reply['buttons']['links_text'] ) ? $reply['buttons']['links_text'] : "";
			
		} elseif( isset( $reply['links'] ) ) {
			$links = $reply['links'];
			$links_text = isset( $reply['links_text'] ) ? $reply['links_text'] : "";
		}
		
		
		foreach ( $links as $link ) {
			$reply_message .= sprintf( '<a href="%s" class="reply_link" target="_blank">%s</a><br />', $link['url'], $link['title'] );
		}
		
		
		if( !empty( $reply_message ) ) {
			
			if( !empty( $links_text ) ) {
				$reply_message = "<div>{$links_text}</div>" . $reply_message;
			}
		} elseif( isset ( $reply['text'] ) ) {
			$reply_message = $reply['text'];
		}
		
		
		return $reply_message;
	}
}