<?php


if( class_exists( 'WPAS_SC_ChatBot' ) ) {

	class WPAS_SS_ChatBot extends WPAS_SC_ChatBot {
		
		/**
		 * Return search posts links from array to string
		 * 
		 * @param array $reply
		 * 
		 * @return string
		 */
		public function wpas_sc_buttons_reply_content($reply) {
			
			
			$reply_message = "";
			$links_text = "";
			$links = array();

			if( isset( $reply['buttons'] ) && isset( $reply['buttons']['links'] ) ) {
				$links = $reply['buttons']['links'];

			} elseif( isset( $reply['links'] ) ) {
				$links = $reply['links'];
			}


			foreach ( $links as $link ) {
				$reply_message .= sprintf( '<a href="%s" class="reply_link" target="_blank">%s</a><br />', $link['url'], $link['title'] );
			}


			if( !empty( $reply_message ) ) {
				
				$links_text = wpas_ss_get_option( 'ss_smart_submit_text_before_links' );

				if( !empty( $links_text ) ) {
					$reply_message = "<div>{$links_text}</div>" . $reply_message;
				}
			} elseif( isset ( $reply['text'] ) ) {
				$reply_message = $reply['text'];
			}


			return $reply_message;
			
		}
	}

}