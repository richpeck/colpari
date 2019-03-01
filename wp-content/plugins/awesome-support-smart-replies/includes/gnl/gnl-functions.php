<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Return all entity types
 * 
 * @return array
 */
function wpas_cbot_entity_types() {
	
	return array(
		'UNKNOWN'		=> __( 'Unknown',		'wpas_chatbot' ),
		'PERSON'		=> __( 'Person',		'wpas_chatbot' ),
		'LOCATION'		=> __( 'Location',		'wpas_chatbot' ),
		'ORGANIZATION'	=> __( 'Organization',	'wpas_chatbot' ),
		'EVENT'			=> __( 'Event',			'wpas_chatbot' ),
		'WORK_OF_ART'	=> __( 'Work of art',	'wpas_chatbot' ),
		'CONSUMER_GOOD'	=> __( 'Consumer goods', 'wpas_chatbot' ),
		'OTHER'			=> __( 'Other types',	'wpas_chatbot' )

		);
	
}

/**
 * Return all part of speech options
 * 
 * @return array
 */
function wpas_cbot_part_of_speech_options() {
	
	return array(
		
		'UNKNOWN'	=> __( 'Unknown',			'wpas_chatbot' ),
		'ADJ'		=> __( 'Adjective',			'wpas_chatbot' ),
		'ADP'		=> __( 'Adposition (preposition and postposition)', 'wpas_chatbot' ),
		'ADV'		=> __( 'Adverb',			'wpas_chatbot' ),
		'CONJ'		=> __( 'Conjunction',		'wpas_chatbot' ),
		'DET'		=> __( 'Determiner',		'wpas_chatbot' ),
		'NOUN'		=> __( 'Noun (common and proper)', 'wpas_chatbot' ),
		'NUM'		=> __( 'Cardinal number',	'wpas_chatbot' ),
		'PRON'		=> __( 'Pronoun',			'wpas_chatbot' ),
		'PRT'		=> __( 'Particle or other function word', 'wpas_chatbot' ),
		'PUNCT'		=> __( 'Punctuation',		'wpas_chatbot' ),
		'VERB'		=> __( 'Verb (all tenses and modes)', 'wpas_chatbot' ),
		'X'			=> __( 'Other: foreign words, typos, abbreviations', 'wpas_chatbot' ),
		'AFFIX'		=> __( 'Affix',				'wpas_chatbot' )
		);
	
}

/**
 * Return default settings
 * 
 * @return array
 */
function wpas_cbot_gnl_default_settings() {
	
	return array(
		'cbot_enable_gnl'			=> false,
		'cbot_gnl_salience_score'	=> '0.03',
		'cbot_gnl_entity_types'		=> array( 'OTHER' ),
		'cbot_gnl_part_of_speech'	=> array(),
		'cbot_gnl_keyword_results_limit' => 5
	);
}

/**
 * Return setting value
 * 
 * @param string $option
 * 
 * @return mixed
 */
function wpas_cbot_get_option( $option ) {
	
	$default_settings = wpas_cbot_gnl_default_settings();
	$default = isset( $default_settings[ $option ] ) ? $default_settings[ $option ] : '';
	
	return maybe_unserialize( wpas_get_option( $option, $default ) );
}

/**
 * Return entities and tags from google natural languange
 * 
 * @param int $ticket_id
 * 
 * @return array
 */
function wpas_cbot_gnl_calculate_tags( $ticket_id ) {
			
	$ticket = get_post( $ticket_id );

	$text = $ticket->post_title . '. ' . $ticket->post_content;
	$gnl = new WPAS_CBOT_Google_Natural_Language( $text );
	$entities = $gnl->entity_keywords();
	$tags = $gnl->pof_keywords();
	
	$data = array( 'entities' => $entities, 'tags' => $tags );

	update_post_meta( $ticket_id, 'gnl_keywords',  $data );
	
	return $data;
}


add_action( 'wpas_backend_ticket_content_after', 'wpas_cbot_add_gnl_buttons_after_ticket_content', 100, 2 );

/**
 * Print keywords under ticket content
 * 
 * @param int $ticket_id
 * @param object $ticket
 */

/**
 * Print or keywords under ticket content or return keywords
 * 
 * @param int $ticket_id
 * @param object $ticket
 * @param boolean $force
 * @param boolean $echo
 * 
 * @return void|array
 */
function wpas_cbot_add_gnl_buttons_after_ticket_content( $ticket_id, $ticket, $force = false, $echo = true ) {
	
	if ( false === boolval( wpas_get_option( 'cbot_gnl_show_tags_below_ticket', true ) ) ) {
		return ;
	}
	
	$keywords = maybe_unserialize( get_post_meta( $ticket_id, 'gnl_keywords', true ) );
	
	
	if( !is_array( $keywords ) || $force ) {
		$keywords = wpas_cbot_gnl_calculate_tags( $ticket_id );
	}
	
	$entities = isset( $keywords['entities'] ) ? $keywords['entities'] : array();
	$tags     = isset( $keywords['tags'] ) ? $keywords['tags'] : array();
	
	$entity_buttons = $tag_buttons = array();
	
	
	foreach( $entities as $entity ) {
		$entity_buttons[] = sprintf( '<span class="wpas-label hint-bottom hint-anim" data-hint="%s">%s</span>',  "Type : {$entity['type']}, Salience : {$entity['salience']}" , $entity['text'] );
	}
	
	
	foreach( $tags as $tag ) {
		$tag_buttons[] = sprintf( '<span class="wpas-label hint-bottom hint-anim" data-hint="%s">%s</span>',  "{$tag['type']}" , $tag['text'] );
	}
	
	if( $echo ) {
		echo '<div class="entity_buttons">' . implode( ' ', $entity_buttons ) . '</div>';
		echo '<div class="pof_buttons">' . implode( ' ', $tag_buttons ) . '</div>';
		
	} else {
		return array(
			'entities'  => implode( ' ', $entity_buttons ),
			'tags'		=> implode( ' ', $tag_buttons )
		);
	}
	
}


add_filter( 'wpas_backend_ticket_content_after_buttons', 'wpas_cbot_add_recalculate_tags_button', 12, 2 );

/**
 * Add recalculate tags button on ticket edit page
 * 
 * @param array $buttons
 * @param int $ticket_id
 * 
 * @return array
 */
function wpas_cbot_add_recalculate_tags_button( $buttons , $ticket_id ) {
	
	$action = "wpas-gnl-recalculate-tags";
	$nonce = wp_create_nonce( $action );
		
	$buttons[] = sprintf('<div><a class="wpas_cbot_gnl_recalculate_tags_btn" href="#" data-nonce="%s">%s</a></div>', $nonce ,  __( 'Recalculate GNL tags' ) );
	
	
	return $buttons;
}


add_action( 'wp_ajax_wpas_cbot_gnl_recalculate_tags', 'wpas_cbot_ajax_gnl_recalculate_tags' );

/**
 * Recalculate google natural language tags via ajax
 * 
 * @return void
 */
function wpas_cbot_ajax_gnl_recalculate_tags() {
	
	wpas_sc_ajax_nonce_check( 'wpas-gnl-recalculate-tags' );
	
	$ticket_id = filter_input( INPUT_POST, 'ticket_id');
	
	
	
	if ( false === boolval( wpas_get_option( 'cbot_gnl_show_tags_below_ticket', true ) ) ) {
		return ;
	}
	
	if( $ticket_id ) {
		
		$ticket = get_post( $ticket_id );
		
		$keywords = wpas_cbot_add_gnl_buttons_after_ticket_content( $ticket_id, $ticket, true, false );
		
		
		wp_send_json_success( $keywords );
		die();
	}
	
	die();
}