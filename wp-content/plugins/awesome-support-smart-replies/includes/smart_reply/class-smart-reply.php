<?php

/**
 * Handle smart reply feature
 */
class WPAS_SC_Smart_Reply {
	
	
	private $ticket_id;
	private $ticket_content;
	
	
	public function __construct( $ticket_id, $content = '' ) {
		
		$this->ticket_id = $ticket_id;
		
		
		if( empty( $content ) ) {
			$ticket = get_post( $this->ticket_id );
			
			$content = $ticket->post_title . '. ' . $ticket->post_content;
		} 
		
		
		$this->ticket_content = $content;
		
	}
	
	/**
	 * Return list of smart replies
	 * 
	 * @return array
	 */
	public function get_smart_reply() {
		
		$smart_replies = array();

		
		if( wpas_cbot_get_option( 'cbot_enable_gnl' ) ) {
			
			$bot = new WPAS_SC_ChatBot( $this->ticket_content, 'smart_replies' );
			$reply = $bot->get_reply();

			
			if( 'fallback' !== $reply['origin'] && ( 'text' == $reply['type'] || 'buttons' == $reply['type'] ) && !empty( $reply['text'] ) ) {
				$smart_replies[] = sprintf( '<div class="smart_reply">%s</div>', $reply['text'] );
			}

		} else {
			// Splitting ticket content into sentences
			$sentences = preg_split('/(?<=[.?!])\s+(?=[a-z])/i', $this->ticket_content );


			if( !empty( $sentences ) ) {

				foreach( $sentences as $sentance ) {

					$bot = new WPAS_SC_ChatBot( $sentance, 'smart_replies' );
					$reply = $bot->get_reply();

					if( 'fallback' == $reply['origin'] ) {
						continue;
					}

					if( ( 'text' == $reply['type'] || 'buttons' == $reply['type'] ) && !empty( $reply['text'] ) ) {
						$smart_replies[] = sprintf( '<div class="smart_reply">%s</div>', $reply['text'] );
					}

				}
			}
		
		}
		
		return $smart_replies;
		
	}
	
	/**
	 * Send auto reply once new ticket is added
	 * 
	 * @param int $ticket_id
	 * @param string $content
	 * 
	 * @return boolean
	 */
	public static function send_auto_reply( $ticket_id, $content ) {
		
		$smart_reply = new self( $ticket_id, $content );
		
		return $smart_reply->maybe_add_smart_reply();
	}
	
	/**
	 * Return reply content
	 * 
	 * @param int $ticket_id
	 * @param string $content
	 * 
	 * @return string
	 */
	public static function get_reply( $ticket_id, $content = '' ) {
		
		$smart_reply = new self( $ticket_id, $content );
		
		return $smart_reply->get_reply_content();
	}
	
	/**
	 * Return reply content
	 * 
	 * @return string
	 */
	public function get_reply_content() {
		
		$smart_replies = $this->get_smart_reply();
		
		$smart_reply_content = '';
		$smart_reply_header = '';
		$smart_reply_footer = '';
		
		
		if( empty( $smart_replies ) ) {

			if ( true === boolval( wpas_get_option( 'sc_smart_replies_send_fallback_message', false ) ) ) {
			
				$smart_replies[] = sprintf( '<div class="fallback_smart_reply">%s</div>', wpas_sc_fallback_message( 'smart_replies' ) );
			}
			
		} else {

			$smart_reply_header = wpas_sc_get_option( 'sc_smart_replies_reply_header' );
			$smart_reply_footer = wpas_sc_get_option( 'sc_smart_replies_reply_footer' );

		}
		
		// At this point the $smart_replies array might have contents so just convert to string if its not empty...
		$smart_reply_content = '' ;
		if ( ! empty( $smart_replies ) ) {
			$smart_reply_content = $smart_reply_header . implode( '', $smart_replies ) . $smart_reply_footer;		
		}
		
		return $smart_reply_content;
		
	}
	
	/**
	 * Add smart reply if content found
	 * 
	 * @return boolean
	 */
	function maybe_add_smart_reply() {
		
		$content = $this->get_reply_content();

		if( ! empty( $content ) ) {
			
			$data = array(
				'post_content'   => $content,
				'post_status'    => 'unread',
				'post_type'      => 'ticket_reply',
				'ping_status'    => 'closed',
				'comment_status' => 'closed',
				'post_parent'    => $this->ticket_id
			);
			
			
			$agent = get_post_meta( $this->ticket_id, '_wpas_assignee', true );
			
			return wpas_add_reply( $data, $this->ticket_id, $agent );

		}
		
		return false;
	}
	
	
	
	/**
	 * Return content for manual smart reply button
	 * 
	 * @param int $ticket_id
	 * 
	 * @return string
	 */
	public static function manual_button( $ticket_id ) {
		
		$action = "wpas-sc-manual-smart-reply";
		$nonce = wp_create_nonce( $action );
		
		return sprintf('<div><a class="wpas_sc_manual_smart_reply_btn" href="#" data-nonce="%s">%s</a></div>', $nonce ,  __( 'Smart Reply' ) );
		
	}
}