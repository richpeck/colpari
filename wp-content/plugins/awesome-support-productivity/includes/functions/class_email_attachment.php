<?php


/**
 * Add email attachments feature
 */
class WPAS_PF_Email_Attachments {
	
	protected static $instance = null;
	
	public function __construct() {
		
		add_filter( 'wpas_email_notifications_template_tags', array( $this, 'add_attachments_tag'   ), 11, 1 ); // Register {attachments} tag
		add_filter( 'wpas_email_notifications_tags_values',   array( $this, 'attachments_tag_value' ), 10, 2 ); // Set default tag value
		add_filter( 'wpas_email_notification_attachments',    array( $this, 'set_email_attachments' ), 10, 4 );
		
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
	 * Set email attachments
	 * 
	 * @param array $attachments
	 * @param string $case
	 * @param int $ticket_id
	 * @param int $post_id
	 * @return array
	 */
	public function set_email_attachments( $attachments, $case, $ticket_id, $post_id ) {
		
		$posts =  $this->get_attachments( $post_id ) ;
		
		
		foreach( $posts as $att ) {
			$att_file = get_attached_file( $att->ID );
			if( !in_array( $att_file, $attachments ) ) {
				$attachments[] = $att_file;
			}
		}
		
		return $attachments;
	}
	
	/**
	 * Set default value as empty string as its not a tag to replace content
	 * 
	 * @param array $new
	 * @param int $post_id
	 * @return array
	 */
	public function attachments_tag_value( $new, $post_id ) {
		
		foreach($new as $k => $tag) {
			if ( '{attachments}' === $tag['tag'] ) {
				$new[ $k ]['value'] = '';
			}
		}
		
		return $new;
	}
	
	/**
	 * Get ticket or reply attachments
	 * 
	 * @param int $post_id
	 * @return array
	 */
	public function get_attachments( $post_id ) {
		
		$post = get_post( $post_id );
		
		if ( is_null( $post ) ) {
			return array();
		}

		$args = array(
			'post_parent'            => $post_id,
			'post_type'              => 'attachment',
			'post_status'            => 'inherit',
			'posts_per_page'         => - 1,
			'no_found_rows'          => true,
			'cache_results'          => false,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,

		);

		$attachments = new WP_Query( $args );
		
		if ( empty( $attachments->posts ) ) {
			return array();
		}
		
		return $attachments->posts;
	}
	
	/**
	 * Add {attachments} tag
	 * 
	 * @param array $tags
	 * @return array
	 */
	public function add_attachments_tag( $tags = array() ) {
		
		$tags[] = array(
			'tag' 	=> '{attachments}',
			'desc' 	=> __( 'Add attachments', 'wpas_productivity' )
		);
		
		return $tags;
	}
	
}

