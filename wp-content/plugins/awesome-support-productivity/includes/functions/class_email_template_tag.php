<?php

/**
 * Add custom field email tags
 */
class WPAS_PF_Email_Template_Tag {
	
	protected static $instance = null;
	
	public function __construct() {
		
		add_filter( 'wpas_email_notifications_template_tags', array( $this, 'add_template_tags'   ),     10, 1 ); // Register email template tags
		add_filter( 'wpas_email_notifications_tags_values',   array( $this, 'custom_field_tags_value' ), 10, 2 ); // Add tag values
		
	}
	
	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	/**
	 * Register new email template tags
	 * 
	 * @param array $tags
	 * 
	 * @return array
	 */
	public function add_template_tags( $tags ) {
		
		// Add custom field tags
		$tags = $this->add_custom_field_tags( $tags );
		
		$tags[] = array(
		    'tag' 	=> '{fullticket}',
		    'desc' 	=> __( 'Full Ticket', 'wpas_productivity' )
		);
	
		return $tags;
	}
	
	
	/**
	 * Register custom field tags
	 * 
	 * @param array $tags
	 * 
	 * @return array
	 */
	public function add_custom_field_tags( $tags = array() ) {
		
		$fields = WPAS()->custom_fields->get_custom_fields();
		
		$exclude_tags = array( 'id', 'author', 'wpas-activity' );
		
		$tags[] = array(
		    'tag' 	=> '{ticket_status}',
		    'desc' 	=> __( 'Ticket Status', 'wpas_productivity' )
		);
		
		foreach( $fields as $field ) {
			
			if( !in_array( $field['name'], $exclude_tags ) ) {
				
				$id = '{customfield:' . $field['name'] . '}';
				$title = !empty( $field['args']['title'] ) ? $field['args']['title'] : $field['name'];
				$tags[] = array(
					'tag' 	=> $id,
					'desc' 	=> __( 'Custom field : '.$title, 'wpas_productivity' )
				);
			}
		}
		
		
		
		return $tags;
	}
	
	/**
	 * Set custom field tag values
	 * 
	 * @param array $new
	 * @param int $post_id
	 * 
	 * @return array
	 */
	public function custom_field_tags_value( $new, $post_id ) {
		
		$ticket = get_post( $post_id );
		
		if( $ticket ) {
			if ( 'ticket_reply' === $ticket->post_type ) {
				$ticket = get_post( $ticket->post_parent );
			}
			
			$custom_fields = WPAS()->custom_fields->get_custom_fields();
			
			
			$user_id_custom_fields = array( 'secondary_assignee', 'tertiary_assignee', 'author' );
			
			$custom_field_value_cb = array(
			    'id'	    => 'get_cf_id_value',
			    'author'	    => 'get_cf_author_value'
			);
			
			foreach( $new as $k => $tag ) {
				if( preg_match( '{customfield:([a-zA-Z0-9-_]+)}', $tag['tag'], $matches ) ) {
					$custom_field = $matches[1];
					
					if( array_key_exists( $custom_field, $custom_fields ) ) {
						$value = "";
						
						if ( array_key_exists( $custom_field, $custom_field_value_cb ) ) {
							$cb = $custom_field_value_cb[ $custom_field ];
							if( method_exists( $this, $cb ) ) {
								$value = call_user_func_array( array( $this, $cb ), array( $ticket ) );
							}
						} else {
							$value = $this->get_custom_field_value( $custom_fields[ $custom_field ], $ticket );
						}
						if( $value && in_array( $custom_field, $user_id_custom_fields ) ) {
							$value = get_the_author_meta( 'display_name', $value );
						}
						
						$new[ $k ]['value'] = $value;
					}
				} else {
					// As its not a customfield tag we need to process each tag separately
					switch( $tag['tag'] ) {
						
						case '{ticket_status}':
							$new[ $k ]['value'] = $this->get_cf_ticket_status_value( $ticket );
							break;
						
						case '{fullticket}';
							$new[ $k ]['value'] = $this->get_fullticket_value( $ticket );
							break;
					}
				}
			}
			
		}
		
		return $new;
	}
	
	
	/**
	 * Return {fullticket} tag value
	 * 
	 * @param object $ticket
	 * 
	 * @return string
	 */
	private function get_fullticket_value( $ticket ) {
	
	
		$replies = wpas_get_replies( $ticket->ID );
		

		$value = '<div style="background: #f9f9f9;padding: 20px;border: #efeeee solid 1px;">

		<div>
			<h3>' . $ticket->post_title . '</h3>
			<strong>'.wpas_pf_user_display_name( $ticket->post_author ).'</strong> - '
				. date( get_option( 'date_format' ), strtotime( $ticket->post_date ) ) . 
			'<div>'.$ticket->post_content.'</div>
		</div>';


		$value .= '<table style="width : 100%;margin-top: 20px;">';

		foreach( $replies as $reply ) {

			$value .=
				'<tr>
					<td style="border-top : #efeeee solid 1px; padding : 10px 0;">
						<strong>'.wpas_pf_user_display_name( $reply->post_author ).'</strong> - ' . date( get_option( 'date_format' ), strtotime( $reply->post_date ) ) . 
						'<div>'.$reply->post_content.'</div>
					</td>
				</tr>';
		}

		$value .= "</table></div>";
		
		return $value;
	}
	
	/**
	 * Return value of custom field tag
	 * 
	 * @param array $field_args
	 * @param object $ticket
	 * 
	 * @return string
	 */
	private function get_custom_field_value( $field_args, $ticket ) {
		
		$field = new WPAS_Custom_Field( $field_args['name'], $field_args );
						
		$value = $field->get_field_value( '', $ticket->ID );

		if( 'taxonomy' === $field->field_type ) {			
			$term = get_term_by( 'slug', $value, $field->field_id );
			if( false !== $term ) {
				$value = $term->name;
			}
		} elseif( 'checkbox' === $field->field_type && isset( $field->field_args['options'] ) && !empty( $field->field_args['options'] ) && is_array( $value ) ) {
			$new_value = array();
			foreach( $value  as $val ) {
				$new_value[] = $field->field_args['options'][ $val ];
			}
			
			$value = implode(', ', $new_value );
		}
		
		return $value;
	}
	
	/**
	 * Return ticket is as tag value
	 * 
	 * @param object $ticket
	 * 
	 * @return int
	 */
	private function get_cf_id_value( $ticket ) {
		
		return $ticket->ID;
	}
	
	/**
	 * Return customer id as tag value
	 * 
	 * @param object $ticket
	 * 
	 * @return string
	 */
	private function get_cf_author_value( $ticket ) {
		return $ticket->post_author;
	}
	
	/**
	 * Return ticket status as tag value
	 * 
	 * @param object $ticket
	 * 
	 * @return string
	 */
	private function get_cf_ticket_status_value( $ticket ) {
		
		$stauses = wpas_get_post_status();
		
		$status = $ticket->post_status;
		
		
		if( array_key_exists( $status, $stauses ) && !empty( $stauses[ $status ] ) ) {
			$status = $stauses[ $status ];
		}
		
		return $status;
	}

}