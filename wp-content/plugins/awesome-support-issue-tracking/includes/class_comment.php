<?php

/**
 * Issue Comment class
 */
class WPAS_IT_Comment extends WPAS_IT_Post {
	
	public  $comment_id,
			$comment;
	
	/**
	 * Comment class constructor
	 * @param int $id
	 */
	public function __construct( $id ) {
		
		parent::__construct( $id );
		
		$this->comment_id = $id;
		$this->comment = $this->Post;
	}
	
	/**
	 * Get comment issue id
	 * 
	 * @return int
	 */
	public function getIssueID() {
		return $this->comment->post_parent;
	}
	
	/**
	 * Get comment status term
	 * 
	 * @return Object
	 */
	public function getStatus() {
		
		return $this->getTerms( 'wpas_it_cmt_status' );
		
	}
	
	
	
	/**
	 * Get comment status term
	 * 
	 * @return Object
	 */
	public function getStatusColor() {
		
		$status = $this->getStatus();
		
		$color = '';
		if( $status ) {
			$color = get_term_meta( $status->term_id, 'color', true );
		}
		
		return $color;
	}
	
	
	/**
	 * Get comment status name
	 * 
	 * @return string
	 */
	public function getStatusName() {
		$status = $this->getStatus();
		
		$name = "";
		
		if( !empty( $status ) ) {
			$name = $status->name;
		}
		
		return $name;
	}
	
	/**
	 * Get comment type
	 * 
	 * @return string
	 */
	public function getType() {
		return $this->getMeta( 'comment_type' );
	}
	
	/**
	 * Get type label
	 * 
	 * @return string
	 */
	public function getTypeName() {
		
		$type = $this->getType();
		
		$all_types = wpas_it_comment_types();
		
		$name = "";
		
		if( $type && isset( $all_types[ $type ] ) ) {
			$name = $all_types[ $type ];
		}
		
		return $name;
	}
	
	/**
	 * Trash comment
	 * 
	 * return void
	 */
	public function trash() {
		
		wp_trash_post( $this->comment_id, false );
		
		// Recalculate issue comments
		$issue = new WPAS_IT_Issue( $this->getIssueID() ); 
		$issue->calculateCommentsCount();
	}
	
}