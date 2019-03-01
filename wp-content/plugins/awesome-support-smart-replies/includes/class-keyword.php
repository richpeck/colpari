<?php

/**
 * Match keywords in user message
 */

class WPAS_CBOT_Keyword {
	
	protected $text;
	
	protected $type;
	
	protected $fields;
	
	
	public function __construct( $text, $type = '' ) {
		$this->text = $text;
		
		$this->type = $type;
		
		$value = wpas_get_option( 'cbot_search_keywords' );
		
		$this->fields = $value && is_array( $value )? $value : array();
	}
	
	
	/**
	 * 
	 * @param type $text
	 * 
	 * @return type
	 */
	public static function getResponseText( $text, $type = '' ) {
		
		$keyword = new self( $text, $type );
		
		return $keyword->check();
	}
	
	
	/**
	 * Check all keywords against user message
	 * 
	 * @return string
	 */
	public function check() {
		
		
		$matches = array();
		
		$result = array();
		
		foreach( $this->fields as $field ) {
			
			if( 'smart_replies' !== $this->type || ( $this->type == 'smart_replies' && isset( $field['smart_replies_enabled'] ) && $field['smart_replies_enabled'] ) ) {
				
				if( $this->match_field( $field ) ) {
					$matches[] = $field['content'];
				}
			}
		}
		
		
		
		if( !empty( $matches ) ) {
			$text = wpas_cbot_get_single_message( $matches );
			
			$result = array(
			    'type' => 'text',
			    'origin' => 'keyword',
			    'text' => $text
			);
			
		}
		
		return $result;
	}
	
	
	/**
	 * Check if a keyword field matched in user message
	 * 
	 * @param array $field
	 * 
	 * @return boolean
	 */
	public function match_field( $field ) { 
		
		
		if( empty( trim( $field['keyword'] ) ) ) {
			return;
		}
			
		$keywords = array_map( 'trim', explode( ';', $field['keyword'] ) );
		
		$matched = false;
		
		$match_type = $this->get_match_type( $field );
		
		
		
		foreach ( $keywords as $keyword ) {
			
			if( !empty( $keyword ) ) {
			
				if( call_user_func( array( $this, "match_{$match_type}"), $keyword ) ) {
					$matched = true;
				}
				
				
			}
		}
		
		return $matched;
	}
	
	
	/**
	 * Get keyword match type ie contain, exact, similar or regex
	 * 
	 * @param array $field
	 * 
	 * @return string
	 */
	public function get_match_type( $field ) {
		
		$types = array_keys( wpas_cbot_keyword_match_types() );
		
		
		
		$type = isset( $field['keyword_match_type'] ) ? $field['keyword_match_type'] : '';
		
		return ( $type && in_array( $type, $types ) ? $type : 'contain' );
	}
	
	/**
	 * Check if text contains keyword
	 * 
	 * @param type $keyword
	 * @return boolean
	 */
	public function match_contain( $keyword ) {
		
		$matched = false;
		
		if ( false !== strpos( strtolower( $this->text ), strtolower( $keyword ) ) ) {
			
			$matched = true;
		}
		
		return $matched;
	}
	
	/**
	 * Check if text exact matched with a keyword
	 * 
	 * @param type $keyword
	 * @return boolean
	 */
	public function match_exact( $keyword ) {
		
		$matched = false;
		
		if( strtolower( $this->text ) == strtolower( $keyword ) ) {
			$matched = true;
		}
		
		return $matched;
	}
	
	/**
	 * Check if text similar to a keyword
	 * 
	 * @param type $keyword
	 * @return boolean
	 */
	public function match_similar( $keyword ) {
		
		$match_percent = wpas_cbot_similar_text_match_percent();
		$percent = 0;
		
		similar_text( strtolower( $this->text ), strtolower( $keyword ), $percent ); 
		
		$matched = false;
		
		if( $percent >= $match_percent ) {
			$matched = true;
		}
		
		return $matched;
		
	}
	
	/**
	 * Check if a regular expression is valid
	 * 
	 * @param string $pattern
	 * 
	 * @return boolean
	 * @throws Exception
	 */
	public function isPatternValid( $pattern ) {
		
		$valid = true;
		
		try {
			if( @preg_match( $pattern, null ) === false ) {
				throw new Exception( "Invalid Pattern" );
			}
			
		} catch ( Exception $e ) {
			$valid = false;
		}
		
		
		return $valid;
	}
		
	/**
	 * regex match text
	 * 
	 * @param string $pattern
	 * 
	 * return boolean
	 */
	public function match_regex( $pattern ) {
		
		$matched = false;
		
		if( substr( $pattern, 0, 1 ) != '/' ) {
			$pattern =  "/{$pattern}";
		}
		
		if( substr( $pattern, -1 ) != '/' ) {
			$pattern =  "{$pattern}/";
		}
		
		if( $this->isPatternValid( $pattern ) ) {
			
			if( preg_match( $pattern, $this->text ) ) {
				$matched = true;
			}
			
		}
		
		return $matched;
		
	}
	
}