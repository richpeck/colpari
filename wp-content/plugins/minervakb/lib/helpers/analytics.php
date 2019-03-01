<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

class MinervaKB_Analytics {

	/**
	 * From date
	 * @var
	 */
	private $from;

	/**
	 * To date
	 * @var
	 */
	private $to;

	/**
	 * Limit for top/recent articles queries
	 * @var int
	 */
	private $top_limit = 5;

	/**
	 * Articles cache
	 * @var array()
	 */
	private $articles = array();

	/**
	 * KB topics cache
	 * @var array
	 */
	private $topics = array();

	/**
	 * KB tags cache
	 * @var array
	 */
	private $tags = array();

	/**
	 * Articles meta cache
	 * @var array()
	 */
	private $meta = array();

	private $articles_count = 0;
	private $topics_count = 0;
	private $tags_count = 0;
	private $views_count = 0;
	private $likes_count = 0;
	private $dislikes_count = 0;

	public function __construct() {
		$this->set_default_interval();

		// NOTE: post types must be already registered
		add_action('init', array($this, 'load_articles'), 10);
	}

	private function set_default_interval() {
		$now = time();
		$begin_of_day = strtotime("midnight", $now);

		$this->to = $begin_of_day;
		$this->from = strtotime("-7 day", $now);
	}

	public function load_articles() {
		$post_type = MKB_Options::option( 'article_cpt' );

		$args = array(
			'posts_per_page'   => -1,
			'offset'           => 0,
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'date',
			'order'            => 'DESC',
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => $post_type,
			'post_mime_type'   => '',
			'post_parent'      => '',
			'author'	   => '',
			'author_name'	   => '',
			'post_status'      => 'publish',
			'suppress_filters' => false
		);

		$this->articles = get_posts( $args );

		if (!empty($this->articles)) {
			$this->articles_count = sizeof($this->articles);

			foreach($this->articles as $article):
				$this->views_count += (int) get_post_meta($article->ID, '_mkb_views', true);
				$this->likes_count += (int) get_post_meta($article->ID, '_mkb_likes', true);
				$this->dislikes_count += (int) get_post_meta($article->ID, '_mkb_dislikes', true);

				$views_meta = get_post_meta($article->ID, '_mkb_views_meta', true);
				$views_meta = $views_meta ? json_decode($views_meta, true) : array();

				$likes_meta = get_post_meta($article->ID, '_mkb_likes_meta', true);
				$likes_meta = $likes_meta ? json_decode($likes_meta, true) : array();

				$dislikes_meta = get_post_meta($article->ID, '_mkb_dislikes_meta', true);
				$dislikes_meta = $dislikes_meta ? json_decode($dislikes_meta, true) : array();

				$this->meta[$article->ID] = array(
					"views" => $views_meta,
					"likes" => $likes_meta,
					"dislikes" => $dislikes_meta
				);
			endforeach;
		}

		$this->topics = get_terms( MKB_Options::option( 'article_cpt_category' ), array(
			'hide_empty' => false,
		));

		if (!empty($this->topics)) {
			$this->topics_count = sizeof($this->topics);
		}

		$this->tags = get_terms( MKB_Options::option( 'article_cpt_tag' ), array(
			'hide_empty' => false,
		));

		if (!empty($this->tags)) {
			$this->tags_count = sizeof($this->tags);
		}
	}

	/**
	 * Override for old WP
	 * @param string $d
	 * @param null $post
	 *
	 * @return mixed|void
	 */
	function get_the_date( $d = '', $post = null ) {
		$post = get_post( $post );

		if ( ! $post ) {
			return false;
		}

		if ( '' == $d ) {
			$the_date = mysql2date( get_option( 'date_format' ), $post->post_date );
		} else {
			$the_date = mysql2date( $d, $post->post_date );
		}

		/**
		 * Filter the date a post was published.
		 *
		 * @since 3.0.0
		 *
		 * @param string      $the_date The formatted date.
		 * @param string      $d        PHP date format. Defaults to 'date_format' option
		 *                              if not specified.
		 * @param int|WP_Post $post     The post object or ID.
		 */
		return apply_filters( 'get_the_date', $the_date, $d, $post );
	}

	public function get_recent_articles() {
		$self = $this;

		return array_map(function($article) use ($self) {
			return array(
				'id' => $article->ID,
				'title' => $article->post_title,
				'link' => get_the_permalink($article->ID),
				'date' => $self->get_the_date( 'M j', $article->ID )
			);
		}, array_slice($this->articles, 0, $this->top_limit));
	}

	public function get_most_viewed_articles() {
		$result = array();

		$post_type = MKB_Options::option( 'article_cpt' );

		$args = array(
			'posts_per_page'   => $this->top_limit,
			'offset'           => 0,
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'meta_value_num',
			'order'            => 'DESC',
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => '_mkb_views',
			'meta_value'       => '',
			'post_type'        => $post_type,
			'post_mime_type'   => '',
			'post_parent'      => '',
			'author'	   => '',
			'author_name'	   => '',
			'post_status'      => 'publish',
			'suppress_filters' => false
		);

		$articles = get_posts( $args );

		if ( ! empty( $articles ) ):
			$result = array_map(function($article) {
				return array(
					'id' => $article->ID,
					'title' => $article->post_title,
					'link' => get_the_permalink($article->ID),
					'views' => (int) get_post_meta($article->ID, '_mkb_views', true),
				);
			}, $articles);
		endif;

		return $result;
	}

	public function get_most_liked_articles() {
		$result = array();

		$post_type = MKB_Options::option( 'article_cpt' );

		$args = array(
			'posts_per_page'   => $this->top_limit,
			'offset'           => 0,
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'meta_value_num',
			'order'            => 'DESC',
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => '_mkb_likes',
			'meta_value'       => '',
			'post_type'        => $post_type,
			'post_mime_type'   => '',
			'post_parent'      => '',
			'author'	   => '',
			'author_name'	   => '',
			'post_status'      => 'publish',
			'suppress_filters' => false
		);

		$articles = get_posts( $args );

		if ( ! empty( $articles ) ):
			$result = array_map(function($article) {
				return array(
					'id' => $article->ID,
					'title' => $article->post_title,
					'link' => get_the_permalink($article->ID),
					'likes' => (int) get_post_meta($article->ID, '_mkb_likes', true),
				);
			}, $articles);
		endif;

		return $result;
	}

	public function get_most_disliked_articles() {
		$result = array();

		$post_type = MKB_Options::option( 'article_cpt' );

		$args = array(
			'posts_per_page'   => $this->top_limit,
			'offset'           => 0,
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'meta_value_num',
			'order'            => 'DESC',
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => '_mkb_dislikes',
			'meta_value'       => '',
			'post_type'        => $post_type,
			'post_mime_type'   => '',
			'post_parent'      => '',
			'author'	   => '',
			'author_name'	   => '',
			'post_status'      => 'publish',
			'suppress_filters' => false
		);

		$articles = get_posts( $args );

		if ( ! empty( $articles ) ):
			$result = array_map(function($article) {
				return array(
					'id' => $article->ID,
					'title' => $article->post_title,
					'link' => get_the_permalink($article->ID),
					'dislikes' => (int) get_post_meta($article->ID, '_mkb_dislikes', true),
				);
			}, $articles);
		endif;

		return $result;
	}

	/**
	 * Gets feedback data
	 * @return array
	 */
	public function get_feedback() {
		$feedback_args = array(
			'posts_per_page'   => - 1,
			'offset'           => 0,
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'DATE',
			'order'            => 'DESC',
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => 'mkb_feedback',
			'post_mime_type'   => '',
			'post_parent'      => '',
			'author'           => '',
			'author_name'      => '',
			'post_status'      => 'publish',
			'suppress_filters' => false
		);

		$feedback = get_posts( $feedback_args );

		$feedback_results = array();

		if (sizeof($feedback)):
			foreach($feedback as $item):
				$article_id = (int) get_post_meta($item->ID, 'feedback_article_id', true);

				array_push($feedback_results, array(
					'feedback_id' => $item->ID,
					'article_id' => $article_id,
					'content' => $item->post_content,
					'date' => $item->post_date
				));
			endforeach;
		endif;

		return $feedback_results;
	}

	/**
	 * Deletes all the feedback
	 */
	public static function delete_all_feedback() {
		$feedback_args = array(
			'posts_per_page'   => - 1,
			'offset'           => 0,
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'DATE',
			'order'            => 'DESC',
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => 'mkb_feedback',
			'post_mime_type'   => '',
			'post_parent'      => '',
			'author'           => '',
			'author_name'      => '',
			'post_status'      => 'publish',
			'suppress_filters' => false
		);

		$feedback = get_posts( $feedback_args );

		if (sizeof($feedback)):
			foreach($feedback as $item):
				wp_delete_post($item->ID);
			endforeach;
		endif;
	}

	/**
	 * Gets keywords data
	 * @return array
	 */
	public function get_keywords($order_options = array()) {

		$keywords = MKB_DbModel::get_top_keywords($order_options);

		return $keywords;
	}

	public function get_articles_count() { return $this->articles_count; }
	public function get_topics_count() { return $this->topics_count; }
	public function get_tags_count() { return $this->tags_count; }
	public function get_views_count() { return $this->views_count; }
	public function get_likes_count() { return $this->likes_count; }
	public function get_dislikes_count() { return $this->dislikes_count; }

	private static function get_recent_period_dates($days) {
		$dates = array();

		$now = time();
		$begin_of_day = strtotime("midnight", $now);

		for ($i = $days - 1; $i > 0; $i--) {
			array_push($dates, strtotime("-" . $i . " day", $begin_of_day));
		}

		array_push($dates, $begin_of_day);

		return array_map(function($day) {
			return array(
				"label" => date('M j', $day),
				"stamp" => $day
			);
		}, $dates);
	}

	public static function get_recent_week_dates() {
		return self::get_recent_period_dates(7);
	}

	public static function get_recent_month_dates() {
		return self::get_recent_period_dates(30);
	}

	public function get_recent_week_views() {
		return $this->get_recent_period_counts("views", 7);
	}

	public function get_recent_week_likes() {
		return $this->get_recent_period_counts("likes", 7);
	}

	public function get_recent_week_dislikes() {
		return $this->get_recent_period_counts("dislikes", 7);
	}
	
	public function get_recent_month_views() {
		return $this->get_recent_period_counts("views", 30);
	}

	public function get_recent_month_likes() {
		return $this->get_recent_period_counts("likes", 30);
	}

	public function get_recent_month_dislikes() {
		return $this->get_recent_period_counts("dislikes", 30);
	}

	public function get_recent_period_counts($key, $period) {
		// dates starting from the most recent
		$dates = array_reverse(
			array_map( function ( $entry ) use ($key) {
				$entry[$key] = 0;

				return $entry;
			}, $this->get_recent_period_dates($period) )
		);

		foreach ($this->meta as $id => $meta):
			if (!array_key_exists($key, $meta) || empty($meta[$key])) {
				continue;
			}

			$views = $meta[$key];

			foreach ($views as $stamp => $count):
				foreach ($dates as $date_index => $date):
					if ((int)$stamp >= (int)$date["stamp"]):
						$dates[$date_index][$key] += (int) $count;
						break;
					endif;
				endforeach;
			endforeach;

		endforeach;

		return array_reverse(
			array_map(function($entry) use ($key) {
				return $entry[$key];
			}, $dates)
		);
	}
}