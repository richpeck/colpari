<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once WPAS_CBOT_PATH.'vendor/autoload.php';

use Google\Cloud\Language\LanguageClient;


/**
 * Class to connect with google and fetch keywords based on provided message
 */
class WPAS_CBOT_Google_Natural_Language {
	
	/**
	 * Input message
	 * 
	 * @var string 
	 */
	private $message;

	/**
	 * Page number
	 * 
	 * @var int
	 */
	private  $page;
	
	/**
	 * Results limit
	 * 
	 * @var int
	 */
	private $limit;
	
	/**
	 * Json config file content
	 * 
	 * @var string
	 */
	private $json_file = null;
	
	/**
	 * Payload name used for post back actions
	 * 
	 * @var string
	 */
	private $payload_name = 'wpas_cbot_get_more_links';
	
	/**
	 * Setup object params
	 * 
	 * @param string $message
	 * @param int $page
	 * @param int $limit
	 * @param string $payload_name
	 */
	public function __construct( $message, $page = 1, $limit = 3, $payload_name = '' ) {
		
		$this->message = $message;
		
		$this->page = $page;
		
		$this->limit = $limit;
		
		if( $payload_name ) {
			$this->payload_name = $payload_name;
		}
		
		$this->json_file = json_decode( wpas_cbot_get_option( 'cbot_gnl_json_file' ) , true );
	}
	
	
	/**
	 * Return Entity keywords
	 * 
	 * @return array
	 */
	public function entity_keywords() {
		$score			= wpas_cbot_get_option( 'cbot_gnl_salience_score' );
		$entity_types	= wpas_cbot_get_option( 'cbot_gnl_entity_types' );
		
		
		$keywords = array();
		
		if( !empty( $entity_types ) ) {
			$entity_keywords = $this->analyzeEntities();
			
			foreach ( $entity_keywords as $entity_kw ) {
				if( in_array( $entity_kw['type'], $entity_types ) &&  $entity_kw['salience'] >= $score ) {
					$keywords[] = $entity_kw;
				}
			}
			
		}
		
		
		return $keywords;
	}
	
	
	/**
	 * Return keywords based on selected post of speech options
	 * 
	 * @return array
	 */
	public function pof_keywords() {
		
		$pof_types		= wpas_cbot_get_option( 'cbot_gnl_part_of_speech' );
		$keywords		= array();
		
		
		if( !empty( $pof_types ) ) {
			$pof_keywords = $this->analyzeSyntax();
			
			foreach ( $pof_keywords as $pof_kw => $tag ) {
				if( in_array( $tag, $pof_types ) ) {
					$keywords[] = array( 'text' => $pof_kw, 'type' => $tag );
				}
			}
			
		}
		
		return $keywords;
		
	}
	
	
	/**
	 * Return just keywords from both entity and part of speech options
	 * @return type
	 */
	public function keywords() {
		
		$temp_keywords = $this->entity_keywords();
		
		
		$temp_keywords = array_merge( $temp_keywords, $this->pof_keywords() );
		
		
		foreach ( $temp_keywords as $kw ) {
			$keywords[] = $kw['text'];
		}
			
		
		$keywords = array_unique( $keywords );
			
		return $keywords;
		
	}
	
	
	/**
	 * Fetch entities from google
	 * 
	 * @return array
	 */
	function analyzeEntities() {

		$keywords = array();
		
		
		$language = new LanguageClient([
				'keyFile' => $this->json_file
			]);
		
		try {
			
			$annotation = @$language->analyzeEntities( $this->message );
			
			$entities = $annotation->entities();

			foreach ( $entities as $entity ) {
				$keywords[] = array(
					'text' => $entity['name'],
					'type' => $entity['type'],
					'salience' => $entity['salience']
				);
			}
			
			
		} catch ( \Exception $error ) {
			wpas_cbot_save_log( "Error while getting entities from google: " . $error->getMessage() );
		}
		
		
		
		return $keywords;
	}
	
	
	/**
	 * Fetch part of speech tags from google
	 * 
	 * @return array
	 */
	function analyzeSyntax() {

		$language = new LanguageClient([
			'keyFile' => $this->json_file
		]);
		
		$keywords = array();
		
		try {
			$annotation = @$language->analyzeSyntax( $this->message );
			$tokens = $annotation->tokens();

			foreach ($tokens as $token) {
				$text = $token['text']['content'];
				$keywords[ $text ] = $token['partOfSpeech']['tag'];
			}
		} catch ( \Exception $error ) {
			wpas_cbot_save_log( "Error while getting part of speech tags from google: " . $error->getMessage() );
		}
		
		return $keywords;
	}
	
	
	/**
	 * Find keywords and Search posts based on those keywords
	 * 
	 * @return array
	 */
	public function get_reply() {
		
		
		$keywords = $this->keywords();
		
		
		$links = $reply_message = array();
		
		$limit_per_keyword = wpas_cbot_get_option( 'cbot_gnl_keyword_results_limit' );
		
		if( !empty( $keywords ) )  {
			
			foreach( $keywords as $keyword ) {
			
				$search_results =  wpas_cbot_search_posts( $keyword, 1, $limit_per_keyword, $this->payload_name );
				
				if( isset( $search_results['links'] ) ) {
					$links = array_merge( $links, $search_results['links']);
				}
				
			}
			
			
			if( !empty( $links ) ) {
			
				$total_links = count( $links );

				$total_pages = ( -1 === $this->limit ) ? 1 : ceil( $total_links / $this->limit );

				if( -1 !== $this->limit && $total_links > $this->limit ) {
					
					$offset = ( $this->page * $this->limit ) - $this->limit;
					$links = array_slice( $links,  $offset, $this->limit );
				}

				$next_page = $this->page < $total_pages ? $this->page + 1 : false;
			}
			
		} 
		
		
		
		if( !empty( $links ) ) {
			$reply_message = array(
			    'type' => 'buttons',
			    'links' => $links,
				'links_text' =>  ( 1 === $this->page ? wpas_cbot_links_text() : __( 'Here are more links', 'wpas_chatbot' ) )
			);
			
			if( $next_page ) {
				$reply_message['next_page_payload'] = "{$this->payload_name}:::{$next_page}:::{$this->message}";
			}
		}
		
		
		return $reply_message;
		
	}
	
	
	/**
	 * init GNL class object and search posts
	 * 
	 * @param string $message
	 * @param int $page
	 * @param int $limit
	 * @param string $payload_name
	 * 
	 * @return array
	 */
	public static function search_posts( $message, $page = 1, $limit = 3, $payload_name = '' ) {
		$gnl = new self( $message, $page, $limit, $payload_name );
		
		return $gnl->get_reply();
	}
}