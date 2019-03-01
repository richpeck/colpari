<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class WPAS_PF_Ticket_Split {
	
	protected static $instance = null;
	
	public function __construct() {
		
		add_action( 'wp_ajax_pf_split',		 array( $this, 'split' ),			11, 0 );
		//add_action( 'wp_ajax_pf_split_ticket',		 array( $this, 'split' ),			11, 0 );
		
		
		add_action( 'wp_ajax_pf_split_win',		array( $this, 'split_form' ),			11, 0 );
		
		add_filter( 'wpas_ticket_reply_controls',	 array( $this, 'add_reply_button' ),		11, 3 );
		
		add_action( 'wpas_backend_reply_content_after',  array( $this, 'split_to_note' ),		10, 1 );
		add_action( 'wpas_backend_ticket_content_after', array( $this, 'split_to_note' ),		11, 1 );
		
		add_action( 'wpas_backend_ticket_content_after', array( $this, 'split_from_note' ),		11, 1 );
		
		add_action( 'wpas_backend_ticket_content_after', array( $this, 'ticket_content_after' ),	11, 1 );
		
		add_action( 'admin_notices',			 array( $this, 'admin_notices' ),		11, 0 );
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
	 * Register admin notices on success or fail split action
	 */
	public function admin_notices() {

		if ( isset( $_GET['wpas-pf-message'] ) ) {

			switch ( $_GET['wpas-pf-message'] ) {

				case 'reply_splitted':
					?>
					<div class="updated">
						<p><?php printf( __( 'A new ticket has been created with the selected reply.', 'wpas_productivity' ), intval( $_GET['post'] ) ); ?></p>
					</div>
					<?php
					break;

				case 'ticket_splitted':
					?>
					<div class="updated">
						<p><?php printf( __( 'A new ticket has been created from this ticket.', 'wpas_productivity' ), intval( $_GET['post'] ) ); ?></p>
					</div>
					<?php
					break;

			}

		}
	}
	
	
	/**
	 * Add note to source ticket or reply
	 * 
	 * @param int $id
	 */
	public function split_to_note( $id ) {
		
		$split_to_ids = $this->split_to_id( $id );
		
		if( $split_to_ids ) {
			
			$links = array();
			
			$post = get_post( $id );
			$type = ( 'ticket_reply' === $post->post_type ) ? 'reply' : 'ticket';
			
			foreach( $split_to_ids as $split_to_id ) {
				
				$link = add_query_arg( array( 'post' => $split_to_id, 'action' => 'edit' ), admin_url( 'post.php' ) );
				
				$links[] = sprintf( __( '<a href="%s">#%s</a>' ), $link, $split_to_id );
			}
			
			
			
			echo '<div class="note">';
			
			if( 1 < count( $links ) ) {
				echo sprintf( __( 'Note : New tickets %s have been created from this %s.' ), implode( ', ', $links ), $type );
			} else {
				echo sprintf( __( 'Note : A new ticket %s has been created from this %s.' ), implode( ', ', $links ), $type );
			}
			echo "</div>";
		}
		
	}
	
	/**
	 * Add note to created ticket
	 * 
	 * @param int $id
	 */
	public function split_from_note( $id ) {
		
		$split_from_id = $this->split_from_id( $id );
		
		
		if( $split_from_id ) {
			
			$source_post = get_post( $split_from_id );
			
			$type = ( 'ticket_reply' === $source_post->post_type ) ? 'reply' : 'ticket';
			
			$ticket_id = $split_from_id;
			if( 'reply' === $type ) {
				$ticket_id = $source_post->post_parent;
			}
			
			$link = add_query_arg( array( 'post' => $ticket_id, 'action' => 'edit' ), admin_url( 'post.php' ) );
			
			if( 'reply' === $type ) {
				$link .= "#wpas-post-{$split_from_id}";
			}
			
			echo '<div class="note">';
			echo sprintf( __( 'Note : This ticket was been created from %s <a href="%s">#%s</a>.' ), $type, $link, $split_from_id );
			echo "</div>";
		}
		
	}
	
	/**
	 * Add split button in ticket post
	 * 
	 * @param int $ticket_id
	 */
	public function ticket_content_after( $ticket_id ) {
		
		echo '<div style="margin-top : 30px;">'.$this->button( $ticket_id, 'ticket', 'button button-primary button-large' ).'</div>';
		
	}
	
	/**
	 * Add button in reply post
	 * 
	 * @param array $buttons
	 * @param int $ticket_id
	 * @param object $reply
	 * 
	 * @return array
	 */
	public function add_reply_button( $buttons, $ticket_id, $reply ) {
		
		if( !isset( $buttons['split'] ) )  {
			$buttons['split'] = $this->button( $reply->ID );;
		}
		return $buttons;
	}
	
	/**
	 * Return split button code
	 * 
	 * @param int $id
	 * @param string $type
	 * @param string $classes
	 * 
	 * @return string
	 */
	public function button( $id, $type = 'reply', $classes = '' ) {
		
		$action = "pf_split_{$type}";
		$nonce = wp_create_nonce( $action );
		
		
		$link = add_query_arg( array( 
			'action' => 'pf_split_win', 
			'id' => $id,
			'width' => 600
			), 
			admin_url( 'admin-ajax.php' ) 
		);
		
		return '<a class="wpas_pf_tb_win_btn '.$classes.'" href="'.$link.'" data-action="'.$action.'" data-nonce="'.$nonce.'" data-id="'.$id.'" title="Split">'.__( 'Copy To New Ticket', 'wpas_productivity' ).'</a>';
		
	}
	
	
	/**
	 * Return created ticket id
	 * 
	 * @param int $id
	 * 
	 * @return boolean/int
	 */
	private function split_to_id( $id ) {
		
		$ticket_ids = maybe_unserialize( get_post_meta( $id, 'split_to', true ) );
		
		$ids = array();
		
		if( $ticket_ids ) {
			
			if( is_array( $ticket_ids ) ) {
				$ids = $ticket_ids;
			} else {
				$ids[] = $ticket_ids;
			}
		}
		
		return $ids;
	}
	
	/**
	 * Return source ticket or reply id
	 * 
	 * @param int $id
	 * 
	 * @return boolean/int
	 */
	private function split_from_id ( $id ) {
		
		$from_id = 0;
		$types = array( 'ticket', 'reply' );
		
		foreach ($types as $_type) {
			$_from_id = (int) get_post_meta( $id, "split_from_{$_type}", true );
			
			if( $_from_id ) {
				$from_id = $_from_id;
				break;
			}
		}
		
		if( $from_id ) {
			return $from_id;
		}
		
		return false;
	}
	
	
	public function split_form() {
		
		$id = filter_input( INPUT_GET, 'id' );
		
		$data = $this->split_default_data( $id );
		$title = isset($data['post_title']) ? $data['post_title'] : "";
		$content = isset($data['post_content']) ? $data['post_content'] : "";
		
		
		include WPAS_PF_PATH . 'includes/templates/split.php';
		
		die();
	}
	
	/**
	 * Return default split content and title
	 * 
	 * @param int $id
	 */
	public function split_default_data( $id ) {
		$post = get_post( $id );
		
		$data = array();
		
		if ( $post ) {
			
			$source_type = $post->post_type === 'ticket_reply' ? 'reply' : 'ticket';
			
			if( 'reply' === $source_type ) {
				$ticket_id = $post->post_parent;
				$ticket = get_post( $ticket_id );
			} else {
				$ticket_id = $id;
				$ticket = $post;
			}
			
			$post_name = sprintf( __( '%s : Split %s %s', 'wpas_productivity' ), $ticket->post_title , $source_type , "#$id" );
			
			$data = array(
				'post_author'	=> $ticket->post_author,
				'post_content'	=> $post->post_content,
				'post_name'	=> $post_name,
				'post_title'	=> $post_name,
			);
		}
		
		return $data;
	}
	
	/**
	 * Split ticket or post
	 */
	public function split() {
		
		$post_id = filter_input( INPUT_POST, 'id' );
		$nonce = filter_input( INPUT_POST, '_wpnonce_pf-split' );
		$action = filter_input( INPUT_POST, 'action' );
		
		
		$title = filter_input( INPUT_POST, 'split_title' );
		$content = filter_input( INPUT_POST, 'pf_split_content' );
		
		
		
		$success = false;
		$data = array();
		
		
		if( ! check_ajax_referer( 'pf-split', '_wpnonce_pf-split', false ) ) {
			$data['msg'] = __( 'Sorry, we can\'t perform this action, try again later.', 'wpas_productivity' );
		} elseif( "" == $title ) {
			$data['msg'] = __( 'Title is required.', 'wpas_productivity' );
		} else {
			
			$post = get_post( $post_id );
			if ( $post ) {
				
				$source_type = $post->post_type === 'ticket_reply' ? 'reply' : 'ticket';
				
				if( 'reply' === $source_type ) {
					$ticket_id = $post->post_parent;
					$ticket = get_post( $ticket_id );
				} else {
					$ticket_id = $post_id;
					$ticket = $post;
				}
				
				
				
				$args = $this->split_default_data( $post_id );
				$args['post_title'] = $title;
				$args['post_content'] = $content;
				
				$new_ticket_id = wpas_insert_ticket( $args );
				
				if( $new_ticket_id ) {
					
					$current_split_to_ids = $this->split_to_id( $post_id );
					$current_split_to_ids[] = $new_ticket_id;
					
					update_post_meta( $post_id, 'split_to', $current_split_to_ids );
					
					if( 'reply' === $source_type ) {
						update_post_meta( $new_ticket_id, 'split_from_reply', $post_id );
					} else {
						update_post_meta( $new_ticket_id, 'split_from_ticket', $post_id );
					}
					
					
					
					$notice_msg = 'reply' === $source_type ? 'reply_splitted' : 'ticket_splitted';
					$data['location'] = add_query_arg( array( 'post' => $ticket_id, 'action' => 'edit', 'wpas-pf-message' =>  $notice_msg ), admin_url( 'post.php' ) );
					
					$success = true;
				}
			} else {
				$data['msg'] = __( 'Sorry, we can\'t perform this action, try again later.', 'wpas_productivity' );
			}
			
		}
		
		
		if( $success ) {
			wp_send_json_success( $data );
		}
		
		wp_send_json_error( $data );
		exit;
	}
}