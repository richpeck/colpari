<?php


class WPAS_IT_Post {
	
	public  $post_id,
			$Post = null;
	
	public function __construct( $id ) {
		
		$this->post_id = $id;
		
		$this->Post = get_post( $this->post_id );
	}
	
	/**
	 * Return post property
	 * 
	 * @param string $prop
	 * 
	 * @return string
	 */
	public function getProp( $prop ) {
		
		if( $this->Post ) {
			return $this->Post->{$prop};
		}
		
		return '';
	}
	
	/**
	 * Return post title
	 * 
	 * @return string
	 */
	public function getTitle() {
		
		$title = $this->getProp( 'post_title' );
		if ( empty( $title ) ) {
			$title = __( '(no title)' );
		}
		return esc_html( $title );
	}
	
	/**
	 * Return post id
	 * 
	 * @return int
	 */
	public function getID() {
		return $this->post_id;
	}
	
	/**
	 * Return post content
	 * 
	 * @return string
	 */
	public function getContent() {
		return $this->getProp( 'post_content' );
	}
	
	/**
	 * Get post taxonomy terms
	 * 
	 * @param taxonomy $tax
	 * @param boolean $single
	 * 
	 * @return array|object
	 */
	public function getTerms( $tax, $single = true ) {
		
		$terms = wp_get_post_terms( $this->post_id, $tax );
		
		$terms = is_wp_error( $terms ) ? array() : $terms;
		
		if( !empty( $terms ) && $single ) {
			$terms = $terms[0];
		}
		
		return $terms;
	}
	
	/**
	 * Return post meta
	 * 
	 * @param string $key
	 * 
	 * @return string
	 */
	public function getMeta( $key ) {
		
		return get_post_meta( $this->post_id, $key, true );
		
	}
	
	
}