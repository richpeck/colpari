<?php


/**
 * Issue class
 */
class WPAS_IT_Issue extends WPAS_IT_Post {
	
	public  $issue_id,
			$issue,
			$Comments = null,
			$lastComment = null;
	
	
	/**
	 * Issue class constructor
	 * 
	 * @param int $id
	 */
	public function __construct( $id ) {
		
		parent::__construct( $id );
		
		$this->issue_id = $id;
		
		$this->issue = $this->Post;
	}
	
	/**
	 * Get issue status term
	 * 
	 * @return object
	 */
	public function getStatus() {
		
		return $this->getTerms( 'wpas_it_status' );
		
	}
	
	/**
	 * Get issue status name
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
	 * Get issue priority term
	 * 
	 * @return object
	 */
	public function getPriority() {
		
		return $this->getTerms( 'wpas_it_priority' );
		
	}
	
	/**
	 * Get issue priority name
	 * 
	 * @return string
	 */
	public function getPriorityName() {
		$priority = $this->getPriority();
		
		$name = "";
		
		if( !empty( $priority ) ) {
			$name = $priority->name;
		}
		
		return $name;
	}
	
	/**
	 * Get issue state		open or closed
	 * 
	 * @return string
	 */
	public function getState() {
		$state = $this->getMeta( '_wpas_it_state' );
		
		return ( 'closed' === $state ? 'closed' : 'open' );
	}
	
	/**
	 * Get all tickets linked to an issue
	 * 
	 * @global object $wpdb
	 * 
	 * @return array
	 */
	public function getTickets() {
		global $wpdb;

		$key = "wpas_ticket_issue_{$this->issue_id}";
		
		$q = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %d";

		$results = $wpdb->get_results( $wpdb->prepare( $q, $key, $this->issue_id ) );


		$tickets = array();
		foreach ( $results as $res ) {
			$ticket_id = $res->post_id;

			if( !array_key_exists( $ticket_id, $tickets ) ) {
				$ticket = get_post( $ticket_id );
				if( $ticket ) {
					$tickets[ $ticket_id ] = $ticket;
				}
			}
		}

		return $tickets;
	}
	
	
	/**
	 * Get all issue comments
	 * 
	 * @param array $args
	 * 
	 * @return array
	 */
	public function getComments( $args = array() ) {
		
		if( null === $this->Comments ) {
			
			$defaults = array(
				'posts_per_page' => -1,
				'orderby'        => 'post_date',
				'order'          => 'ASC' ,
				'post_type'      => 'wpas_it_comment',
				'post_parent'    => $this->issue_id,
				'post_status'    => array( 'publish', 'trash' )
			);
			
			$args  = wp_parse_args( $args, $defaults );
			
			$query = new WP_Query( $args );
			

			$comments = ! empty( $query->posts ) ? $query->posts : array();
			
			$this->Comments = $comments;
		}
		
		return $this->Comments;
		
	}
	
	/**
	 * Return last issue comment
	 * 
	 * @return object | array
	 */
	public function getLastComment() {
		
		if( null === $this->lastComment ) {
			
			$args = array(
				'posts_per_page' => 1,
				'orderby'        => 'post_date',
				'order'          => 'DESC' ,
				'post_type'      => 'wpas_it_comment',
				'post_parent'    => $this->issue_id,
				'post_status'    => array( 'publish' )
			);
			
			$query = new WP_Query( $args );
			
			$comment = ! empty( $query->posts ) ? $query->posts[0] : array();
			
			$this->lastComment = $comment;
		}
		
		return $this->lastComment;
	}
	
	/**
	 * Return content of last comment
	 * 
	 * @return string
	 */
	public function getLastCommentContent() {
		
		$comment = $this->getLastComment();
		
		$content = "";
		
		if( $comment ) {
			$content = $comment->post_content;
		}
		
		return $content;
	}
	
	/**
	 * Return content of {full_issue} email template tag
	 * 
	 * @return string
	 */
	public function full_issue() {
		
		$comments = $this->getComments();
		
		
		$value = '<div style="background: #f9f9f9;padding: 20px;border: #efeeee solid 1px;">

		<div>
			<h3>' . $this->getTitle() . '</h3>
			<strong>'.wpas_it_user_display_name($this->getProp( 'post_author' ) ).'</strong> - '
				. date( get_option( 'date_format' ), strtotime( $this->getProp( 'post_date' ) ) ) . 
			'<div>'. $this->getContent().'</div>
		</div>';


		$value .= '<table style="width : 100%;margin-top: 20px;">';

		foreach( $comments as $comment ) {

			$value .=
				'<tr>
					<td style="border-top : #efeeee solid 1px; padding : 10px 0;">
						<strong>'.wpas_it_user_display_name( $comment->post_author ).'</strong> - ' . date( get_option( 'date_format' ), strtotime( $comment->post_date ) ) . 
						'<div>'.$comment->post_content.'</div>
					</td>
				</tr>';
		}

		$value .= "</table></div>";
		
		return $value;
		
	}
	
	/**
	 * Return issue tickets count
	 * 
	 * @return int
	 */
	public function getTicketsCount() {
		$count = $this->getMeta( 'tickets_count' );
		
		return $count ? $count : 0;
	}
	
	/**
	 * Return issue comments count
	 * 
	 * @return int
	 */
	public function getCommentsCount() {
		$count = $this->getMeta( 'comments_count' );
		
		return $count ? $count : 0;
	}
	
	/**
	 * Calculate and save issue tickets count
	 * 
	 * @global object $wpdb
	 */
	public function calculateTicketsCount() {
		
		global $wpdb;

		$key = "wpas_ticket_issue_{$this->issue_id}";
		$q   = "SELECT COUNT(posts.post_id) FROM ("
				. "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %d GROUP BY post_id"
				. ") as posts";
		
		$new_count  = $wpdb->get_var( $wpdb->prepare( $q, $key, $this->issue_id ) );
		
		update_post_meta( $this->issue_id, 'tickets_count', $new_count );
	}
	
	/**
	 * Calculate and save issue comments count
	 * 
	 * @global object $wpdb
	 */
	public function calculateCommentsCount() {
		global $wpdb;
		
		
		$query = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s AND post_parent = %d";
		
		$comments_count = $wpdb->get_var( $wpdb->prepare( $query, 'wpas_it_comment', 'publish', $this->issue_id ) );
		
		$new_comments_count = $comments_count ? $comments_count : 0;
		update_post_meta( $this->issue_id, 'comments_count', $new_comments_count );
	}
	
	/**
	 * Get all additional interested parties
	 * 
	 * @return array
	 */
	public function getAdditionalInterestedParties() {
		$parties = maybe_unserialize( $this->getMeta( 'ai_parties' ) );
		
		return ( is_array( $parties ) && !empty( $parties ) ? $parties : array() );
	}
	
	
	/**
	 * Get issue primary agent id
	 * 
	 * @return int
	 */
	public function getPrimaryAgentID() {
		
		return $this->getMeta( 'wpas_it_primary_agent' );
		
	}
	
	/**
	 * Get issue primary agent
	 * 
	 * @return null | object
	 */
	public function getPrimaryAgent() {
		$id = $this->getPrimaryAgentID();
		
		$agent = null;
		if( $id ) {
			$agent = get_user_by( 'ID', $id );
		}
		
		return $agent;
	}
	
	/**
	 * Get issue primary agent name
	 * 
	 * @param string $default
	 * 
	 * @return string
	 */
	public function getPrimaryAgentName( $default = "" ) {
		
		$name = $default;
		
		$agent = $this->getPrimaryAgent();
		
		if( $agent ) {
			$name = $agent->data->display_name;
		}
		
		return $name;
	}
	
	/**
	 * Get all additional agent ids
	 * 
	 * @return array
	 */
	public function getAdditionalAgentIDs() {
		
		
		$agents = maybe_unserialize( $this->getMeta( 'additional_agents' ) );

		$agents = is_array( $agents ) && $agents ? $agents : array();
		
		$additional_agents = array();
		
		foreach( $agents  as $agent ) {
			if( is_array( $agent ) && isset( $agent['user_id'] ) ) {
				$additional_agents[] = $agent['user_id'];
			}
		}
		
		return $additional_agents;
	}
	
	/**
	 * Get all issue agent ids
	 * 
	 * @return array
	 */
	public function getAgents() {
		
		$agent_ids = array();
		$primery_agent_id = $this->getPrimaryAgentID();
		
		if( $primery_agent_id ) {
			$agent_ids[] = $primery_agent_id;
		}
		
		$additional_agents = $this->getAdditionalAgentIDs();
		
		$agent_ids =  array_merge( $agent_ids, $additional_agents );
		
		return array_unique( $agent_ids );
	}
	
	
	/**
	 * Close issue
	 * 
	 * @global object $current_user
	 * 
	 * @return boolean
	 */
	function close() {
		global $current_user;
		
		$user_id = $current_user->ID;
		
		if ( ! current_user_can( 'close_ticket' ) ) {
			wp_die( __( 'You do not have the capacity to close this issue', 'wpas_it' ), __( 'Can’t close issue', 'wpas_it' ), array( 'back_link' => true ) );
		}
	
		$update = update_post_meta( $this->issue_id, '_wpas_it_state', 'closed' );

		update_post_meta( $this->issue_id, '_issue_closed_on', current_time( 'mysql' ) );
		update_post_meta( $this->issue_id, '_issue_closed_on_gmt', current_time( 'mysql', 1 ) );
		
		do_action( 'wpas_after_close_issue', $this->issue_id );


		return $update;

	}
	
	/**
	 * Return issue close date
	 * 
	 * @param boolean $gmt
	 * 
	 * @return string
	 */
	public function close_date( $gmt = false ) {
		
		$key = '_issue_closed_on';
		if( $gmt ) {
			$key .= '_gmt';
		}
		
		return $this->getMeta( $key );
	}
	
	
	/**
	 * Check if issue is closed
	 * 
	 * @return boolean
	 */
	public function is_closed() {
		
		$state = $this->getState();
		return ( 'closed' === $state ? true : false );
	}
	
	
	/**
	 * Display status label
	 * 
	 * @return string
	 */
	public function display_status() {

		$state = $this->getState();
		
		$label = 'Open';
		$color = '#169baa';
		
		if( 'closed' === $state ) {
			$label = 'Closed';
			$color = '#dd3333';
		} else {
			$status = $this->getStatus();
			
			
			if( !empty( $status ) ) {
				$label = $status->name;
				$_color = get_term_meta( $status->term_id, 'color', true );
				$color = $_color ? $_color : $color;
			} 
			
			
		}
		
		return sprintf( '<span class="wpas-label" style="background-color:%s;">%s</span>', $color, $label );
	}
	
	/**
	 * Display priority label
	 * 
	 * @return string
	 */
	public function display_priority() {

		$label = 'medium';
		$color = '#169baa';
		
		$priority = $this->getPriority();
		
		if( !empty( $priority ) ) {
			$label = $this->getPriorityName();
			$_color = get_term_meta( $priority->term_id, 'color', true );
			$color = $_color ? $_color : $color;
		} 
		
		return sprintf( '<span class="wpas-label" style="background-color:%s;">%s</span>', $color, $label );
	}	
	
	/**
	 * return all ticket issus
	 * 
	 * @param int $ticket_id
	 * 
	 * @return array
	 */
	public static function get_ticket_issues( $ticket_id ) {
		
		$ids = self::get_ticket_issue_ids( $ticket_id );
		
		$issues = array();
		foreach ( $ids as $issue_id ) {
			$issues[] = new self( $issue_id );
		}
		
		return $issues;
	}
	
	/**
	 * get ticket issue ids
	 * 
	 * @global object $wpdb
	 * 
	 * @param int $ticket_id
	 * 
	 * @return array
	 */
	public static function get_ticket_issue_ids( $ticket_id ) {
		global $wpdb;

		$q = "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key LIKE %s";
		$results = $wpdb->get_results( $wpdb->prepare( $q , $ticket_id, 'wpas\_ticket\_issue\_%' ) );
		
		$ids = array();
		
		foreach ( $results as $res ) {
			$ids[] = $res->meta_value;
		}
		return $ids;
	}
	
}