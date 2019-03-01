<?php
namespace AsRulesEngine;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Class:RE_Action handle all rules action related work.
 */
if( ! class_exists( 'RE_Action' ) ) {
	
	class RE_Action {
		/**
		 * $agent_id Agent id
		 * @var int
		 */
		public $agent_id;
		/**
		 * $ticket_id Ticket id
		 * @var int
		 */
		private $ticket_id;
		/**
		 * $ruleset_id Ruleset id
		 * @var int
		 */
		private $ruleset_id;
		/**
		 * $reply_id reply id
		 * @var int
		 */
		private $reply_id;
		/**
		 * $status ticket status
		 * @var string
		 */
		private $status;
		/**
		 * $status ticket state
		 * @var string
		 */
		private $state;
		/**
		 * $status action close ticket check value.
		 * @var array
		 */
		private $close_ticket;
		/**
		 * $change_agent Action Change Agent checked value.
		 * @var array
		 */
		private $change_agent;
		/**
		 * $change_agent2 Action Change secondary Agent value.
		 * @var array
		 */
		private $change_agent2;
		/**
		 * $change_agent3 Action Change tertiary agent value.
		 * @var array
		 */
		private $change_agent3;
		/**
		 * $change_first_interested_party_email Action Change first interested party email address
		 * @var array
		 */
		private $change_first_interested_party_email;		
		/**
		 * $change_second_interested_party_email Action Change first interested party email address
		 * @var array
		 */
		private $change_second_interested_party_email;		
		/**
		 * $change_priority Action Change Priority value.
		 * @var array
		 */		 
		private $change_priority;	
		/**
		 * $change_dept Action Change Department value.
		 * @var array
		 */
		private $change_dept;
		/**
		 * $change_channel Action Change channel value.
		 * @var array
		 */
		private $change_channel;			
		/**
		 * $reply Action reply checked value.
		 * @var array
		 */
		private $reply;
		/**
		 * $change_trash_ticketagent Action trash ticket checked value.
		 * @var array
		 */
		private $trash_ticket;
		/**
		 * $add_note Action add note checked value.
		 * @var array
		 */
		private $add_note;
		/**
		 * $send_email Action send email checked value.
		 * @var array
		 */
		private $send_email;
		/**
		 * $webhook Action webhook checked value.
		 * @var array
		 */
		private $webhook;
		/**
		 * $http_action Action http action checked value.
		 * @var array
		 */
		private $http_action;
		/**
		 * $http_url Action http url checked value.
		 * @var array
		 */
		private $http_url;
		/**
		 * $zapier_data Action Zapier data checked value.
		 * @var array
		 */
		private $zapier_data;
		/**
		 * $assignee Action Assignee data checked value.
		 * @var array
		 */
		private $assignee;
		/**
		 * $customer Action Customer data checked value.
		 * @var array
		 */
		private $customer;
		/**
		 * $assignee_template Action selected Assignee email template checked value.
		 * @var array
		 */
		private $assignee_template;
		/**
		 * $customer_template Action selected customer email template checked value.
		 * @var array
		 */
		private $customer_template;
		/**
		 * $secondary_assignee Action selected secondary checked value.
		 * @var array
		 */
		private $secondary_assignee;
		/**
		 * $secondary_assignee Action selected secondary assignee email template checked value.
		 * @var array
		 */
		private $secondary_assignee_template;
		/**
		 * $tertiary_assignee Action selected tertiary assignee checked value.
		 * @var array
		 */
		private $tertiary_assignee;
		/**
		 * $tertiary_assignee Action selected tertiary assignee email template checked value.
		 * @var array
		 */
		private $tertiary_assignee_template;
		/**
		 * $first_interested_party Selected first interested party
		 * @var array
		 */
		private $first_interested_party;
		/**
		 * $first_interested_party Selected first interested party email template.
		 * @var array
		 */
		private $first_interested_party_template;
		/**
		 * $second_interested_party Selected Second interested party.
		 * @var array
		 */
		private $second_interested_party;
		/**
		 * $second_interested_party_template Selected Second interested party email template.
		 * @var array
		 */
		private $second_interested_party_template;

		/**
		 * RE_Action constructor		 
		 */
		public function __construct( $instances = "" ){
			if( ! empty ( $instances ) && ( is_array( $instances ) || is_object( $instances ) ) ){
				foreach( $instances as $key => $value ){
					$this->$key = $value;
				}
			}
			add_filter( 'wpas_email_notifications_template_tags', array( $this, 'wpas_email_notifications_template_tags_callback' ), 10, 1 ); // Display valid tag values in the emails tab
			add_action( 'wpas_email_notifications_tags_values', array($this, 'wpas_email_notifications_tags_values_callback' ), 10, 2);
		}
		
		/**
		 * Actual action takes place
		 * 
		 * @param  string $statement Action to take.
		 * @param  int $ticket_id  Ticket ID.
		 * @param  int $reply_id   Reply ID.
		 * @param  int $ruleset_id Ruleset ID.
		 * @param  string $exclude Exclude action element.
		 */
		public function do_action( $statement, $ticket_id, $reply_id, $ruleset_id, $exclude = "" ){
			$this->ticket_id = $ticket_id;
			$this->ruleset_id = $ruleset_id;
			$this->reply_id = $reply_id;
			$this->get_ruleset_value( $this->ruleset_id );
			if( $statement ){
				if( ! empty ( $this->status ) && $this->status['value'] !== "default" && "change_status" !== $exclude ){
					$this->change_status( $ticket_id, $this->status['value'] );
				}
				if( ! empty ( $this->state['value'] ) ){
					if( $this->state['value'] == "open" ){
						$this->ticket( $ticket_id, "open" );
					}elseif( $this->state['value'] == "close" ){
						$this->ticket( $ticket_id, "close" );
					}					
				}
				if( isset( $this->change_agent['value'] ) && !empty( $this->change_agent['value'] )  && ( 'default' !== $this->change_agent['value'] ) && $exclude !== "change_agent"  ){
					/**
					 * Get agent ID from Ruleset
					*/
					$this->change_agent( $ticket_id, $this->change_agent['value'] );
				}
				
				if( isset( $this->change_agent2['value'] ) && !empty( $this->change_agent2['value'] )  && ( 'default' !== $this->change_agent2['value'] ) && $exclude !== "change_agent2"  ){
					/**
					 * Get secondary agent/assignee ID from Ruleset
					*/
					$this->change_agent2( $ticket_id, $this->change_agent2['value'] );
				}
				
				if( isset( $this->change_agent3['value'] ) && !empty( $this->change_agent3['value'] )  && ( 'default' !== $this->change_agent3['value'] ) && $exclude !== "change_agent3"  ){
					/**
					 * Get tertiary agent/assignee ID from Ruleset
					*/
					$this->change_agent3( $ticket_id, $this->change_agent3['value'] );
				}								
				
				if( isset( $this->change_first_interested_party_email['value'] ) && !empty( $this->change_first_interested_party_email['value'] )  && ( 'default' !== $this->change_first_interested_party_email['value'] ) && $exclude !== "change_first_interested_party_email"  ){
					/**
					 * Get first interested party email from Ruleset
					*/
					$this->change_first_interested_party_email( $ticket_id, $this->change_first_interested_party_email['value'] );
				}
				
				if( isset( $this->change_second_interested_party_email['value'] ) && !empty( $this->change_second_interested_party_email['value'] )  && ( 'default' !== $this->change_second_interested_party_email['value'] ) && $exclude !== "change_second_interested_party_email"  ){
					/**
					 * Get second interested party email from Ruleset
					*/
					$this->change_second_interested_party_email( $ticket_id, $this->change_second_interested_party_email['value'] );
				}				
				
				if( isset( $this->change_priority['value'] ) && !empty( $this->change_priority['value'] )  && ( 'default' !== $this->change_priority['value'] ) && $exclude !== "change_priority"  ){
					/**
					 * Get Priority value from Ruleset
					*/
					$this->change_priority( $ticket_id, $this->change_priority['value'] );
				}
				
				if( isset( $this->change_dept['value'] ) && !empty( $this->change_dept['value'] )  && ( 'default' !== $this->change_dept['value'] ) && $exclude !== "change_dept"  ){
					/**
					 * Get Department value from Ruleset
					*/
					$this->change_dept( $ticket_id, $this->change_dept['value'] );
				}
				
				if( isset( $this->change_channel['value'] ) && !empty( $this->change_channel['value'] )  && ( 'default' !== $this->change_channel['value'] ) && $exclude !== "change_channel"  ){
					/**
					 * Get Channel value from Ruleset
					*/
					$this->change_channel( $ticket_id, $this->change_channel['value'] );
				}								

				if( ! empty ( $this->reply['value'] ) ){
					/**
					 * Get reply author
					*/
					$get_author = get_post_meta( $ruleset_id, 'action_edit_ticket_user', true );
					if( isset ( $get_author['value'] ) && ! empty ( $get_author['value'] ) ){
						$author = $get_author['value'];
					}else{
						$author = false;
					}
					$this->ticket( $ticket_id, "add_reply", $this->reply['value'], $author );
				}
				
				if( ! empty ( $this->trash_ticket['value'] ) ){
					/**
					 * Delete the ticket
					*/
					$this->ticket( $ticket_id, "trash" );
				}
				
				if( ! empty ( $this->add_note['value'] ) && $exclude !== "add_note"  ){
					/**
					 * Get the author/agent for the note
					*/
					$get_note_author = get_post_meta( $ruleset_id, 'action_note_ticket_user', true );
					if( isset ( $get_note_author['value'] ) && ! empty ( $get_note_author['value'] ) ){
						$note_author = $get_note_author['value'];
					}else{
						$note_author = false;
					}					
					$this->add_note( $ticket_id, $this->add_note['value'], $note_author );
				}
				
				if( ! empty ( $this->send_email['value'] ) && $exclude !== "send_email"  ){
					if( isset( $this->send_email['action_send_email_template'] ) && ! empty ( $this->send_email['action_send_email_template'] ) ){
						$this->send( "email", $this->send_email['value'], $this->send_email['action_send_email_template'] );
					}
				}
				
				if ( isset( $this->assignee['value'] ) && 'on' === $this->assignee['value']  && $exclude !== "send_email" ) {
					$assignee_id = get_post_meta( $ticket_id, '_wpas_assignee', true );
					$assignee_email = get_userdata( $assignee_id );
					if(isset($assignee_email->data->user_email) && !empty($assignee_email->data->user_email)){
						$this->send( "email", $assignee_email->data->user_email, $this->assignee_template['value'] );
					}
				}

				if ( isset( $this->customer['value'] ) && 'on' === $this->customer['value'] && $exclude !== "send_email" ) {
					$customer_ticket = get_post( $ticket_id );
					if( !empty( $customer_ticket ) ){
						$customer_id = $customer_ticket->post_author;
						$customer_email = get_userdata( $customer_id );
						if(isset($customer_email->data->user_email) && !empty($customer_email->data->user_email)){
							$this->send( "email", $customer_email->data->user_email, $this->customer_template['value'] );
						}
					}
				}


				if ( isset( $this->secondary_assignee['value'] )  && 'on'  === $this->secondary_assignee['value'] ) {
					$secondary_assignee_id = get_post_meta( $ticket_id, '_wpas_secondary_assignee', true );
					$secondary_assignee_email = get_userdata( $secondary_assignee_id );
					if(isset($secondary_assignee_email->data->user_email) && !empty($secondary_assignee_email->data->user_email)){
						$this->send( "email", $secondary_assignee_email->data->user_email, $this->secondary_assignee_template['value'] );
					}
				}

				if ( isset( $this->tertiary_assignee['value'] ) && 'on'  === $this->tertiary_assignee['value'] ) {
					$tertiary_assignee_id = get_post_meta( $ticket_id, '_wpas_tertiary_assignee', true );
					$tertiary_assignee_email = get_userdata( $tertiary_assignee_id );
					if(isset($tertiary_assignee_email->data->user_email) && !empty($tertiary_assignee_email->data->user_email)){
						$this->send( "email", $tertiary_assignee_email->data->user_email, $this->tertiary_assignee_template['value'] );
					}
				}

				if ( isset( $this->first_interested_party['value'] ) && 'on' === $this->first_interested_party['value'] ) {
					$first_interested_party_email = get_post_meta( $ticket_id, '_wpas_first_addl_interested_party_email', true );
					if ( filter_var( $first_interested_party_email, FILTER_VALIDATE_EMAIL ) ){
						$this->send( "email", $first_interested_party_email, $this->first_interested_party_template['value'] );
					}
				}

				if ( isset( $this->second_interested_party['value'] ) && 'on' === $this->second_interested_party['value'] ) {
					$second_interested_party_email = get_post_meta( $ticket_id, '_wpas_second_addl_interested_party_email', true );
					if ( filter_var( $second_interested_party_email, FILTER_VALIDATE_EMAIL ) ){
						$this->send( "email", $second_interested_party_email, $this->second_interested_party_template['value'] );
					}
				}

				if( isset( $this->webhook['value'] ) && !empty ( $this->webhook['value'] ) && isset( $this->http_action['value'] ) && !empty( $this->http_action['value'] ) && $exclude !== "webhook"  ){
					
					$this->reaw_zapier_notification( $this->webhook['value'], $exclude, $this->http_action['value'], 'webhook');
				}

				if( !empty($this->zapier_data) && $exclude !== "zapier" ){

					if ( filter_var( $this->zapier_data['value'], FILTER_VALIDATE_URL ) ) {
						$this->reaw_zapier_notification( $this->zapier_data['value'], $exclude, 'post', 'zapier');
					} 
				}
			}
		}

		/**
		 * Handle Zapier Notification related action.
		 * @param  String $url     Zap URL.
		 * @param  String $exclude rules trigger.
		 * @return String     Excerpt of ticket.
		 */
		function reaw_zapier_notification( $url, $exclude, $method, $type){
			if( ! empty ( $url ) ){
				$zapier_data = array();
				if( 'zapier' === $type ){
					$zapier_data = get_post_meta( $this->ruleset_id, 'zapier_notification_data', true );
				}
				if( 'webhook' == $type ){
					$zapier_data = get_post_meta( $this->ruleset_id, 'hooks_notification_data', true );
				}
				if( !empty($zapier_data)){
					$ticket_data = get_post( $this->ticket_id ); 
					$zapier = array();

					foreach ($zapier_data as $key => $value) {

						if('title' === $value){
							$title = $ticket_data->post_title;
							$zapier[$value] = $title;
						}

						if('description' === $value){
							if( isset( $ticket_data->post_excerpt ) ){
								$post_excerpt = $ticket_data->post_excerpt;
								if ( !empty( $post_excerpt ) ) {
									$zapier[$value] = $post_excerpt;	
								}
							}
						}

						if('creator' === $value){
							if( isset( $ticket_data->post_author ) && !empty( $ticket_data->post_author ) ){
								$creator_data = get_userdata($ticket_data->post_author);	
								
								if( isset( $creator_data->user_login ) && !empty( $creator_data->user_login ) ){
									$zapier[$value] = $creator_data->user_login;
								}
							}
						}

						if('support' === $value){
							if( isset( $this->change_agent['value'] ) && !empty( $this->change_agent['value'] ) && 'default' !== $this->change_agent['value'] ){
								$support_user = get_userdata($this->change_agent['value']);	
								if( isset( $support_user->user_login ) && !empty( $support_user->user_login ) ){
									$zapier[$value] = $support_user->user_login;
								}
							}else{
								$agent_id = get_post_meta( $this->ticket_id, '_wpas_assignee', true );
								$agent_arttributes = get_userdata( $agent_id );	
								if( isset( $agent_arttributes->user_login ) && !empty( $agent_arttributes->user_login ) ){
									$zapier[$value] = $agent_arttributes->user_login;
								}
							}
						}

						if('status' === $value){
							if( isset( $this->status['value'] ) && !empty( $this->status['value'] ) && 'default' !== $this->status['value'] ){
								switch ( $this->status['value'] ) {
									case 'queued':
										$status = 'New';
										break;
									case 'processing':
										$status = 'In Progress';
										break;
									case 'hold':
										$status = 'On Hold';
										break;
									default:
										$status = str_replace('-', ' ', $this->status['value']);
										$status = ucfirst( $status );
								}


								$zapier[$value] =  $status;
							}else{
								$ticket_status = get_post_status ( $ticket_data->ID );
								if( !empty($ticket_status) ){
									$zapier[$value] = $ticket_status;
								}
							}
						}

						if('trigger' === $value){
							$zapier[$value] = $exclude;
						}

						if('state' === $value){
							$state = wpas_get_ticket_status( $ticket_data->ID );
							$zapier[$value] = $state;
						}

						if('custom_fields' === $value){

							$product_custom_field = wp_get_post_terms( $ticket_data->ID, 'product' );
							$admin_only_custom_field = wp_get_post_terms( $ticket_data->ID, 'ticket_channel' );

							if ( ! is_wp_error( $product_custom_field ) ) {

								$zapier['product_custom_field'] = ( isset($product_custom_field[0]->name)) ? $product_custom_field[0]->name : '';
							}
							if ( ! is_wp_error( $product_custom_field ) ) {
								$zapier['admin_only_custom_field'] =  isset($admin_only_custom_field[0]->name ) ? $admin_only_custom_field[0]->name : '';
							}

							$custom_fields = $this->pull_all_custom_fields( $ticket_data->ID );
							if ( ! is_wp_error( $custom_fields ) && !empty( $custom_fields ) ) {
								$zapier = array_merge( $zapier, $custom_fields );	
							}
						}

						if('client_arttributes' === $value){
							$client_arttributes = get_userdata( $ticket_data->post_author );	
							unset( $client_arttributes->data->user_pass ); // remove password hash
							foreach ($client_arttributes->data as $client_key => $client_value) {
								if ( !empty( $client_value ) ) {
									$zapier['client_'.$client_key] = $client_value;	
								}
							}
						}

						if('agent_arttributes' === $value){
							$agent_id = get_post_meta( $this->ticket_id, '_wpas_assignee', true );
							$agent_arttributes = get_userdata( $agent_id );	
							unset( $agent_arttributes->data->user_pass ); // remove password hash
							foreach ($agent_arttributes->data as $agent_key => $agent_value) {
								if ( !empty( $agent_value ) ) {
									$zapier['agent_'.$agent_key] = $agent_value;	
								}
							}
						}

						if('ticket_contents' === $value){
							$zapier[$value] = $ticket_data->post_content;
						}

						if('ticket_excerpt' === $value){

							if ( empty( $ticket_data->post_excerpt ) ) { 
								$zapier[$value] = wp_trim_words( $ticket_data->post_content, 50, '...' );
							} else {
								$zapier[$value] = wp_trim_words( $ticket_data->post_excerpt, 50, '...' );
							}
						}

						if('reply_contents' === $value){
							$reply_contents = get_post_meta( $this->ruleset_id, 'action_reply_ticket', true );
							if( isset( $this->reply_id ) && !empty($this->reply_id )){
								$ticket_reply = get_post( $this->reply_id );
								if( isset( $ticket_reply->post_content ) && !empty( $ticket_reply->post_content )){
									$zapier[$value] = $ticket_reply->post_content;
								}
							}
						}

						if('note_contents' === $value){
							$action_note_ticket = get_post_meta( $this->ruleset_id, 'action_note_ticket', true );
							if ( !empty( $action_note_ticket['value'] ) ) {
								$zapier[$value] = $action_note_ticket['value'];	
							}
						}

						/**
						 * Update extra zapier data here.
						 */
						do_action('update_zapier_data', $value, $exclude, $type);

					}

					$data_content = array();
					if( !empty( $zapier ) ){

						$zapier_keys 	= array_keys( $zapier ); // get keys
						$zapier_values = array_values( $zapier ); // get values

						// strip all html tags
						array_walk( $zapier_values, array( $this, 'clean_html_tags' ) );

						// combine array keys and values
						$data_content = array_combine( $zapier_keys, $zapier_values );
						$data_content['rule_id'] = $this->ruleset_id;
						if( isset( $this->reply_id ) && !empty( $this->reply_id ) ){
							$data_content['reply_id'] = $this->reply_id;
						}
						if( isset( $ticket_data->ID ) && !empty( $ticket_data->ID ) ){
							$data_content['ticket_id'] = $ticket_data->ID;
						}
						$data_content['webhook_url'] = $url;
						/**
						 * FIlter to extend Web hook data format. 
						 * @var array $date_content 
						 * @var string $type  type of data zapier or web hook
						 */
						$data_content = apply_filters( 'rules_webhooks_data', $data_content, $type);
					}
					$response = array();
					if( 'post' === $method ){
						$response = wp_remote_post( $url, array(
							'method' => 'POST',
							'timeout' => 45,
							'redirection' => 5,
							'httpversion' => '1.0',
							'blocking' => true,
							'headers' => array(),
							'body' => $data_content,
							'cookies' => array()
						    )
						);
					}
					if( 'get' === $method ){
						$response = wp_remote_get( $url, array(
							'method' => 'GET',
							'timeout' => 45,
							'redirection' => 5,
							'httpversion' => '1.0',
							'blocking' => true,
							'headers' => array(),
							'body' => $data_content,
							'cookies' => array()
						    )
						);
					}

					if ( !empty( $response ) &&  is_wp_error( $response ) || ( isset( $response['response']['code'] ) && 200 !== $response['response']['code'] )){
						$error_message = ' ';
						if( is_wp_error( $response ) ){
							$error_message = $response->get_error_message();
						} elseif( isset( $response['response']['message'] ) && !empty( $response['response']['message'] )){
								$error_message = $response['response']['message'];
						}
						wpas_write_log('as-rules-engine', "Something went wrong, Message:$error_message" );
					}
				}
			}
		}

		/**
		 * Function to edit tags value.
		 * 
		 * @param string $new_content New Email content.
		 * @param int $post_id Post ID.
		 */
		function wpas_email_notifications_tags_values_callback( $new_content, $post_id ){
			if( isset( $this->ticket_id ) && !empty( $this->ticket_id )){

				if( !empty( $new_content ) && is_array( $new_content )){
					foreach ( $new_content as $key => $tags ) {
						if( isset( $tags['tag'] )){
							if( '{message}' === $tags['tag'] ){
								if( isset($this->reply_id) && !empty( $this->reply_id )){
									$recent_reply = get_post( $this->reply_id );
									if( isset( $recent_reply->post_content ) ){
										$new_content[ $key ][ 'value' ] = $recent_reply->post_content;
									}else{
										$new_content[ $key ][ 'value' ] = '';
									}
								}
							}
							if( '{ruleset_id}' === $tags['tag'] ){
								if( isset( $this->ruleset_id ) && !empty( $this->ruleset_id )){
									$new_content[ $key ][ 'value' ] = $this->ruleset_id;
								}else{
									$new_content[ $key ][ 'value' ] = '';
								}
							}
						}
					}
				}

			}
			return $new_content;
		}

		public function wpas_email_notifications_template_tags_callback( $tags ){
			$tags[] = array(
				'tag'  => '{ruleset_id}',
				'desc' => __( 'Rules ID', 'as-rules-engine' ),
			);
			return $tags;
		}

		/**
		* strip all html tags inside an array
		*/
		public function clean_html_tags( &$item ){
			
			if ( is_array( $item ) ) {
				return $item;
			}

			return strip_tags($item);
		}

		/**
		* Pull custom fields.
		* 
		* @param Integer ticket ID
		* @return Array
		*/
		public function pull_all_custom_fields( $ticket_id ){
			
			$fields = get_option( 'wpas_custom_fields' );
			$meta_data = array();

			if( ! empty( $fields ) ) {

				foreach ( $fields as $field ) {

					$field_name = $field['name'];
					$tagname = '_wpas_'.$field['name'];
					$post_meta = get_post_meta( $ticket_id, $tagname, true );

					if( !empty( $post_meta ) ) {
						$meta_data[$field_name] = $post_meta;							
					}

					if ( $field['field_type'] == 'taxonomy' ) {
						$custom_taxonomy = wp_get_post_terms( $ticket_id, $field_name );
						if ( !is_wp_error( $custom_taxonomy ) && isset( $custom_taxonomy[0] ) ) {
							$meta_data[$field_name] = $custom_taxonomy[0]->name;
						}
					}

				}
			}

			return $meta_data;
		}

		/**
		 * Get corresponding values from ruleset action
		 * 
		 * @param int $ruleset_id rule set ID.
		 */
		private function get_ruleset_value( $ruleset_id ){
			$prefix = AS_RE_ACTION_META_PREFIX;
			$this->status = get_post_meta( $ruleset_id, AS_RE_ACTION_META_PREFIX . 'change_status', true );
			$this->state = get_post_meta( $ruleset_id, AS_RE_ACTION_META_PREFIX . 'change_state', true );
			$this->change_agent = get_post_meta( $ruleset_id, AS_RE_ACTION_META_PREFIX . 'change_agent', true );
			$this->change_agent2 = get_post_meta( $ruleset_id, AS_RE_ACTION_META_PREFIX . 'change_agent2', true );
			$this->change_agent3 = get_post_meta( $ruleset_id, AS_RE_ACTION_META_PREFIX . 'change_agent3', true );
			$this->change_first_interested_party_email = get_post_meta( $ruleset_id, AS_RE_ACTION_META_PREFIX . 'change_first_interested_party_email', true );
			$this->change_second_interested_party_email = get_post_meta( $ruleset_id, AS_RE_ACTION_META_PREFIX . 'change_second_interested_party_email', true );						
			$this->change_priority = get_post_meta( $ruleset_id, AS_RE_ACTION_META_PREFIX . 'change_priority', true );
			$this->change_channel = get_post_meta( $ruleset_id, AS_RE_ACTION_META_PREFIX . 'change_channel', true );
			$this->change_dept = get_post_meta( $ruleset_id, AS_RE_ACTION_META_PREFIX . 'change_dept', true );
			$this->close_ticket = get_post_meta( $ruleset_id, AS_RE_ACTION_META_PREFIX . 'close_ticket', true );
			$this->reply = get_post_meta( $ruleset_id, AS_RE_ACTION_META_PREFIX . 'reply_ticket', true );
			$this->trash_ticket = get_post_meta( $ruleset_id, AS_RE_ACTION_META_PREFIX . 'trash_ticket', true );
			$this->add_note = get_post_meta( $ruleset_id, AS_RE_ACTION_META_PREFIX . 'note_ticket', true );
			$this->send_email = get_post_meta( $ruleset_id, AS_RE_ACTION_META_PREFIX . 'send_email', true );
			$this->webhook = get_post_meta( $ruleset_id, AS_RE_ACTION_META_PREFIX . 'call_webhook', true );
			$this->http_action = get_post_meta( $ruleset_id, AS_RE_ACTION_META_PREFIX . 'execute_http_action', true );
			$this->zapier_data = get_post_meta($ruleset_id, 'action_zapier_notification', true);
			$this->secondary_assignee = get_post_meta( $ruleset_id, $prefix . 'secondary_assignee', true );
			$this->secondary_assignee_template = get_post_meta( $ruleset_id, $prefix . 'secondary_assignee_template', true );
			$this->tertiary_assignee = get_post_meta( $ruleset_id, $prefix . 'tertiary_assignee', true );
			$this->tertiary_assignee_template = get_post_meta( $ruleset_id, $prefix . 'tertiary_assignee_template', true );
			$this->first_interested_party = get_post_meta( $ruleset_id, $prefix . 'first_interested_party', true );
			$this->first_interested_party_template = get_post_meta( $ruleset_id, $prefix . 'first_interested_party_template', true );
			$this->second_interested_party = get_post_meta( $ruleset_id, $prefix . 'second_interested_party', true );
			$this->second_interested_party_template = get_post_meta( $ruleset_id, $prefix . 'second_interested_party_template', true );
			$this->assignee = get_post_meta( $ruleset_id, $prefix . 'assignee', true );
			$this->customer = get_post_meta( $ruleset_id, $prefix . 'customer', true );
			$this->customer_template =  get_post_meta( $ruleset_id, $prefix . 'customer_template', true );
			$this->assignee_template = get_post_meta( $ruleset_id, $prefix . 'assignee_template', true );

		}
		
		/**
		 * Change ticket status.
		 * 
		 * @param int $ticket_id Ticket ID.
		 * @param string $status Ticket Status.
		 */
		public function change_status( $ticket_id, $status ){
			global $wpdb;
			$query = $wpdb->prepare( "UPDATE ".$wpdb->prefix."posts SET  `post_status` = %s  WHERE ID= %d", $status, $ticket_id );
			$wpdb->query($query);
		}
		
		/**
		 * Change ticket agent.
		 *
		 * @param int $ticket_id Ticket ID.
		 * @param int $agent_id Agent ID.
		 */
		public function change_agent( $ticket_id, $agent_id ){
			wpas_assign_ticket( $ticket_id, $agent_id, false );
		}
		
		/**
		 * Change secondary ticket agent.
		 *
		 * @param int $ticket_id Ticket ID.
		 * @param int $agent_id Agent ID.
		 */
		public function change_agent2( $ticket_id, $agent_id ){
			add_post_meta( $ticket_id, '_wpas_secondary_assignee', $agent_id );
		}
		
		/**
		 * Change tertiary ticket agent.
		 *
		 * @param int $ticket_id Ticket ID.
		 * @param int $agent_id Agent ID.
		 */
		public function change_agent3( $ticket_id, $agent_id ){
			add_post_meta( $ticket_id, '_wpas_tertiary_assignee', $agent_id );
		}
		
		/**
		 * Change first interested party email address on ticket
		 *
		 * @param int $ticket_id Ticket ID.
		 * @param $interested_party_email 
		 */
		public function change_first_interested_party_email( $ticket_id, $interested_party_email ){
			add_post_meta( $ticket_id, '_wpas_first_addl_interested_party_email', $interested_party_email );
		}
		
		/**
		 * Change second interested party email address on ticket
		 *
		 * @param int $ticket_id Ticket ID.
		 * @param $interested_party_email 
		 */
		public function change_second_interested_party_email( $ticket_id, $interested_party_email ){
			add_post_meta( $ticket_id, '_wpas_second_addl_interested_party_email', $interested_party_email );
		}		
		
		/**
		 * Change ticket priority.
		 *
		 * @param int $ticket_id Ticket ID.
		 * @param int $priority_id priority taxonomy id
		 */
		public function change_priority( $ticket_id, $priority_id ){
			wp_set_post_terms( $ticket_id, $priority_id, 'ticket_priority', false );
		}
		
		/**
		 * Change ticket channel
		 *
		 * @param int $ticket_id Ticket ID.
		 * @param int $channel_id channel taxonomy id
		 */
		public function change_channel( $ticket_id, $channel_id ){
			wp_set_post_terms( $ticket_id, $channel_id, 'ticket_channel', false );
		}
		
		/**
		 * Change ticket department.
		 *
		 * @param int $ticket_id Ticket ID.
		 * @param int $dept_id department taxonomy id
		 */
		public function change_dept( $ticket_id, $dept_id ){
			wp_set_post_terms( $ticket_id, $dept_id, 'department', false );
		}		
		
		/**
		 * Add note to ticket action.
		 *
		 * Private Notes addon does not have actions/filters
		 * We mimic it in this function.
		 * 
		 * @param int $ticket_id ticket ID.
		 * @param string $note Note content.
		 * @param int $note_author The author of the note
		 */
		public function add_note( $ticket_id, $note, $note_author = false ){
			if( class_exists( 'WPAS_Private_Note' ) ){
				/* figure out the user id under which this note will be logged */
				$the_user_id = 0 ;
				if ( !$note_author || 'default' == $note_author ) {

					/* user id not passed in, so use the current user - we really shouldn't be here though */
					global $current_user;
					$the_user_id = $current_user->ID;
					
					if ( $the_user_id <= 0 ) {

						// set the user id to 1 if the user id is invalid 
						$the_user_id = 1 ;
					}
					
				} else {
					/* use the user id that was passed in which should be an AGENT! */
					$the_user_id = $note_author;
				}

				$this_note    = wp_kses_post( $note );
				$args    = array(
					'post_content'   => $this_note,
					'post_type'      => 'ticket_note',
					'post_author'    => $the_user_id,
					'post_parent'    => $ticket_id,
					'post_title'     => sprintf( __( 'Note to ticket %s', 'as-private-notes' ), "#$ticket_id" ),
					'post_status'    => 'publish',
					'ping_status'    => 'closed',
					'comment_status' => 'closed'
				);
				
				$insert = wp_insert_post( $args, true );
			} else {
				wpas_log( $ticket_id, $note );
			}
		}
		
		/**
		 * Ticket action.
		 * 
		 * @param int $ticket_id ticket ID.
		 * @param string $type close, add_reply, trash, open
		 * @param string $reply_content reply content text.
		*/
		public function ticket( $ticket_id, $type = "close", $reply_data = null, $reply_author = false ){
			switch( $type ){
				case 'add_reply':
					if( ! empty ( $reply_data ) && ! empty ( $reply_author ) ){
						update_post_meta($ticket_id,'ticket_reply_action', true);
						wpas_add_reply( array( 'post_content' => $reply_data ), $ticket_id, $reply_author );
						update_post_meta($ticket_id,'ticket_reply_action', false);
					}					
				break;
				case 'trash':
					update_post_meta($ticket_id,'ticket_trash_action', true);
					wp_trash_post( $ticket_id );
					update_post_meta($ticket_id,'ticket_trash_action', false);
				break;
				case 'open':
					//wpas_reopen_ticket( $ticket_id );
					/**
					 * Bypass the re-open helper function as they need
					 * to check the current user. We will add log instead stating
					 * that the ticket was re-open through ruleset
					*/
					update_post_meta( intval( $ticket_id ), '_wpas_status', 'open' );
					
					/* Log the action */
					wpas_log( $ticket_id, __( 'The ticket was re-opened through ruleset.', 'as-rules-engine' ) );
				break;
				case 'close':				
				default:
					update_post_meta($ticket_id,'ticket_close_action', true);
					wpas_close_ticket( $ticket_id );
					update_post_meta($ticket_id,'ticket_close_action', false);
				break;
			}
		}
		
		/**
		 * Send action.
		 * 
		 * @param string $type email, zapier.
		 * @param string $receipient email recipient id.
		 * @param string $template_id email template id.
		 */
		public function send( $type="email", $receipient = "", $template_id ){
			switch( $type ){
				case 'email':
					// use post ID directly. don't use arguments
					// when pulling a single post.
					$get_template = get_post( $template_id );

					if( ! empty ( $get_template ) ){
						$sender_name  = wpas_get_option( 'sender_name', get_bloginfo( 'name' ) );
						$sender_email  =wpas_get_option( 'sender_email', get_bloginfo( 'admin_email' ) );
						/**
						 * Prepare e-mail headers
						 * 
						 * @var array
						 */
						$headers = array(
							"MIME-Version: 1.0",
							"Content-type: text/html; charset=utf-8",
							"From: $sender_name <$sender_email>",
							// "Subject: $subject",
							"X-Mailer: Awesome Support/" . WPAS_VERSION,
						);
						$subject = $get_template->post_title;
						$content = $get_template->post_content;
						if( ! empty ( $receipient ) )
						{
							// replace all tags from subject.
							$subject = $this->replace_all_tags( $subject, 'subject' );

							// relace all tags for content
							$new_content = $this->replace_all_tags( $content, 'content' );
							if ( !wp_mail( $receipient, $subject, $new_content, $headers ) ) {
								wpas_write_log( 'as-rules-engine', 'Email not sent' );
							}
						}
					}
					// always reset post data.
					wp_reset_postdata(); 
				break;
			}			
		}

		/**
		 * Replaces all tags
		 *
		 * @param string $content Email notification content.
		 */
		public function replace_all_tags( $content, $type ){

			/* Get the involved users' information */
			$agent_id = get_post_meta( $this->ticket_id, '_wpas_assignee', true );
			$ticket = get_post( $this->ticket_id );

			// Fallback to the default assignee if for some reason there is no agent assigned
			if ( empty( $agent_id ) ) { $agent_id = wpas_get_option( 'assignee_default', 1 ); }

			$agent  = get_user_by( 'id', (int) $agent_id  );
			$client = get_user_by( 'id', $ticket->post_author );

			/* Get the ticket links */
			$url_public  = get_permalink( $ticket->ID );
			$url_private = add_query_arg( array( 'post' => $this->ticket_id, 'action' => 'edit' ), admin_url( 'post.php' ) );

			$as_instance = new \WPAS_Email_Notification( $this->ticket_id );
			if( 'subject' === $type ){
				$return_data = $as_instance->fetch($content);					
			}else{
				$return_data = $as_instance->fetch($content);
				$return_data = $as_instance->get_formatted_email( $return_data );
			}
			return $return_data;
		}
	}
}
?>