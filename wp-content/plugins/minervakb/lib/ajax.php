<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

require_once( MINERVA_KB_PLUGIN_DIR . 'lib/helpers/settings-builder.php' );
require_once( MINERVA_KB_PLUGIN_DIR . 'lib/db.php' );

class MinervaKB_Ajax {

	private $analytics;

	private $restrict;

	private $info;

	const NONCE = 'minerva_kb_nonce';
	const NONCE_KEY = 'minerva_kb_ajax_nonce';

	public function __construct($deps) {

		$this->setup_dependencies( $deps );

		$this->register();
	}

	/**
	 * Sets up dependencies
	 * @param $deps
	 */
	private function setup_dependencies($deps) {
		if (isset($deps['analytics'])) {
			$this->analytics = $deps['analytics'];
		}

		if (isset($deps['restrict'])) {
			$this->restrict = $deps['restrict'];
		}

		if (isset($deps['info'])) {
			$this->info = $deps['info'];
		}
	}

	/**
	 * Registers actions handlers
	 */
	public function register() {

		// save settings
		add_action( 'wp_ajax_mkb_save_settings', array( $this, 'save_settings' ) );

		// reset settings
		add_action( 'wp_ajax_mkb_reset_settings', array( $this, 'reset_settings' ) );

		// import settings
		add_action( 'wp_ajax_mkb_import_settings', array( $this, 'import_settings' ) );

		// save sorting
		add_action( 'wp_ajax_mkb_save_sorting', array( $this, 'save_sorting' ) );

		// save terms sorting
		add_action( 'wp_ajax_mkb_save_terms_sorting', array( $this, 'save_terms_sorting' ) );

		// verify purchase
		add_action( 'wp_ajax_mkb_verify_purchase', array( $this, 'verify_purchase' ) );

		// uninstall settings
		add_action( 'wp_ajax_mkb_uninstall_plugin', array( $this, 'uninstall_plugin' ) );

		// import dummy data
		add_action( 'wp_ajax_mkb_demo_import', array( $this, 'demo_import' ) );

		// skip dummy data import
		add_action( 'wp_ajax_mkb_skip_demo_import', array( $this, 'skip_demo_import' ) );

		// remove selected import entities
		add_action( 'wp_ajax_mkb_remove_import_entities', array( $this, 'remove_import_entities' ) );

		// remove all import entities
		add_action( 'wp_ajax_mkb_remove_all_import_entities', array( $this, 'remove_all_import_entities' ) );

		// resets stats
		add_action( 'wp_ajax_mkb_reset_stats', array( $this, 'reset_stats' ) );

		// restriction rules flush
		add_action( 'wp_ajax_mkb_flush_restriction', array( $this, 'flush_restriction' ) );

		// restriction views log
		add_action( 'wp_ajax_mkb_view_restriction_log', array( $this, 'view_restriction_log' ) );

		// restriction views log clear
		add_action( 'wp_ajax_mkb_clear_restriction_log', array( $this, 'clear_restriction_log' ) );

		// home builder section html
		add_action( 'wp_ajax_mkb_get_section_html', array( $this, 'get_section_html' ) );

		// drag n drop articles reorder
		add_action( 'wp_ajax_mkb_reorder_articles', array( $this, 'reorder_articles' ) );

		// analytics
		add_action( 'wp_ajax_mkb_get_month_analytics', array( $this, 'get_month_analytics' ) );
		add_action( 'wp_ajax_mkb_get_hit_results', array( $this, 'get_hit_results' ) );
		add_action( 'wp_ajax_mkb_get_ordered_search_stats', array( $this, 'get_ordered_search_stats' ) );
		add_action( 'wp_ajax_mkb_get_search_stats_page', array( $this, 'get_search_stats_page' ) );
		add_action( 'wp_ajax_mkb_get_articles_list', array( $this, 'get_articles_list' ) );
		add_action( 'wp_ajax_mkb_get_week_analytics', array( $this, 'get_week_analytics' ) );

		// search
		add_action( 'wp_ajax_mkb_kb_search', array( $this, 'ajax_kb_search' ) );
		add_action( 'wp_ajax_nopriv_mkb_kb_search', array( $this, 'ajax_kb_search' ) );

		// pageview tracking
		add_action( 'wp_ajax_mkb_article_pageview', array( $this, 'article_pageview' ) );
		add_action( 'wp_ajax_nopriv_mkb_article_pageview', array( $this, 'article_pageview' ) );

		// article like
		add_action( 'wp_ajax_mkb_article_like', array( $this, 'article_like' ) );
		add_action( 'wp_ajax_nopriv_mkb_article_like', array( $this, 'article_like' ) );

		// article dislike
		add_action( 'wp_ajax_mkb_article_dislike', array( $this, 'article_dislike' ) );
		add_action( 'wp_ajax_nopriv_mkb_article_dislike', array( $this, 'article_dislike' ) );

		// article feedback
		add_action( 'wp_ajax_mkb_article_feedback', array( $this, 'article_feedback' ) );
		add_action( 'wp_ajax_nopriv_mkb_article_feedback', array( $this, 'article_feedback' ) );
		add_action( 'wp_ajax_mkb_remove_feedback', array( $this, 'remove_feedback' ) );

		// get shortcodes options HTML
		add_action( 'wp_ajax_mkb_get_shortcode_options', array( $this, 'get_shortcode_options' ) );

		// receive frontend submission
		add_action( 'wp_ajax_mkb_save_client_submission', array( $this, 'save_client_submission' ) );
		add_action( 'wp_ajax_nopriv_mkb_save_client_submission', array( $this, 'save_client_submission' ) );

		// track attachment downloads
		add_action( 'wp_ajax_mkb_track_attachment_download', array( $this, 'track_attachment_download' ) );
		add_action( 'wp_ajax_nopriv_mkb_track_attachment_download', array( $this, 'track_attachment_download' ) );

	}

	public static function get_nonce() {
		return self::NONCE;
	}

	public static function get_nonce_key() {
		return self::NONCE_KEY;
	}

	protected function send_security_error() {
		echo json_encode( array(
			'status' => 1,
			'errors' => array(
				'global' => array(
					array(
						'code' => 4001,
						'error_message' => __( 'Security or timeout error. Sorry, you cannot currently perform this action. Try to refresh the page or login.', 'minerva-kb' )
					)
				)
			)
		) );

		wp_die();
	}

	/**
	 * Checks user and checks if he is admin
	 */
	protected function check_admin_user() {
		if ( ! current_user_can( 'administrator' ) ) {
			$this->send_security_error();
		}

		$this->check_user();
	}

	/**
	 * Checks if user is really user
	 */
	protected function check_user() {
		if ( ! check_ajax_referer( self::get_nonce(), 'nonce_value', false ) ) {
			$this->send_security_error();
		}
	}

	/**
	 * Live search handler
	 */
	public function ajax_kb_search() {
		global $post;

		$search = trim( $_REQUEST['search'] );
		$search = filter_var($search, FILTER_SANITIZE_STRING);
		$track_results = isset( $_REQUEST['trackResults'] );
		$search_mode = $_REQUEST['mode'];
		$search_results = array();
		$is_specific_topics = isset( $_REQUEST['topics'] ) && $_REQUEST['topics'] != '';
		$product_id = isset( $_REQUEST['kb_id'] ) && (int)$_REQUEST['kb_id'] > 0 ? (int)$_REQUEST['kb_id'] : null;
		$specific_topics_query = array();

		// search by content
		$query_args = array(
			'post_type' => MKB_Options::option( 'article_cpt' ),
			'post_status' => 'publish',
			'ignore_sticky_posts' => 1,
			's' => $search,
			'order_by' => 'relevance',
			'posts_per_page' => -1
		);

		if ( $is_specific_topics ) {
			$specific_topics_query = array(
				array(
					'taxonomy' => MKB_Options::option( 'article_cpt_category' ),
					'field' => 'term_id',
					'terms' => array_map( function ( $string_id ) {
						return (int) $string_id;
					}, explode( ',', $_REQUEST['topics'] ) ),
					'operator' => 'IN',
				),
			);

			$query_args['tax_query'] = $specific_topics_query;
		}

		$search_loop = new WP_Query( $query_args );

		if ( $search_loop->have_posts() ) :
			while ( $search_loop->have_posts() ) : $search_loop->the_post();
				$topics_list = wp_get_post_terms( $post->ID, MKB_Options::option( 'article_cpt_category' ), array( "fields" => "all" ) );
				$topics_info = array();

				if (!empty($topics_list)) {
					foreach($topics_list as $topic) {
						array_push($topics_info, array(
							'id' => $topic->term_id,
							'name' => $topic->name,
							'color' => MKB_TemplateHelper::get_topic_color_by_id($topic->term_id)
						));
					}
				}

				$excerpt = strip_tags(preg_replace('#\[[^\]]+\]#', '', $post->post_content));

				$article_product = $this->info->get_product_for_article(true);
				$article_product_name = null;

				if ($article_product) {
					$article_product_name = $article_product->name;
				}

				array_push( $search_results, array(
					"id" => $post->ID,
					"title" => get_the_title(),
					"link" => get_the_permalink(),
					"topics" => $topics_info,
					"product" => $article_product_name,
					"excerpt" => MKB_Options::option( 'live_search_show_excerpt' ) ?
						mb_substr($excerpt, 0, MKB_Options::option( 'live_search_excerpt_length' )) :
						''
				) );
			endwhile;
		endif;
		wp_reset_postdata();

		if ($search_mode === 'blocking' || $track_results) {
			ob_start();
			try {
				MKB_DbModel::register_hit( MKB_DbModel::HIT_TYPE_SEARCH, array(
					"keyword" => $search,
					"results_count" => sizeof( $search_results ),
					"results_ids" => sizeof( $search_results ) ?
						array_map( function ( $result ) {
							return $result["id"];
						}, $search_results ) :
						null
				) );
			} catch (Exception $e) {}
			ob_clean();
		}

		$status = 0;

		$res = array(
			'search' => $search,
			'result' => $search_results,
			'status' => $status
		);

		if ($product_id) {
			$product = get_term_by('id', $product_id, MKB_Options::option( 'article_cpt_category' ));

			ob_start();
			?>
			<div class="kb-search__results-info">
				<?php echo esc_html(MKB_Options::option('search_product_prefix')); ?>
				&nbsp;<strong><?php echo esc_html($product->name); ?></strong>
			</div>
			<?php
			$results_info_html = ob_get_clean();

			$res['results_info'] = $results_info_html;
		}

		echo json_encode( $res );

		wp_die();
	}

	/**
	 *
	 * @param $post_id
	 * @param $key
	 */
	protected function update_count_meta( $post_id, $key ) {
		$now = time();
		$begin_of_day = strtotime( "midnight", $now );

		$current_count_meta_raw = get_post_meta( $post_id, $key, true );
		$current_count_meta = array();

		if ( $current_count_meta_raw ) {
			$current_count_meta = json_decode( $current_count_meta_raw, true );
		}

		if ( ! array_key_exists( $begin_of_day, $current_count_meta ) ) {
			$current_count_meta[ $begin_of_day ] = 0;
		}

		$current_day_count                   = (int) $current_count_meta[ $begin_of_day ];
		$current_count_meta[ $begin_of_day ] = ++ $current_day_count;

		update_post_meta( $post_id, $key, json_encode( $current_count_meta ) );
	}

	/**
	 * Article pageview
	 */
	public function article_pageview() {
		$article_id = (int) $_POST['id'];
		$article    = get_post( $article_id );

		if ( $article === null ) {
			wp_die();
		}

		$current_views = (int) get_post_meta( $article_id, '_mkb_views', true );
		update_post_meta( $article_id, '_mkb_views', ++ $current_views );

		$this->update_count_meta( $article_id, '_mkb_views_meta' );

		$status = 0;

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Article like handler
	 */
	public function article_like() {
		$this->check_user();

		$article_id = (int) $_POST['id'];
		$article    = get_post( $article_id );

		if ( $article === null ) {
			wp_die();
		}

		$current_views = (int) get_post_meta( $article_id, '_mkb_likes', true );
		update_post_meta( $article_id, '_mkb_likes', ++ $current_views );

		$this->update_count_meta( $article_id, '_mkb_likes_meta' );

		$status = 0;

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Article dislike
	 */
	public function article_dislike() {
		$this->check_user();

		$article_id = (int) $_POST['id'];
		$article    = get_post( $article_id );

		if ( $article === null ) {
			wp_die();
		}

		$current_views = (int) get_post_meta( $article_id, '_mkb_dislikes', true );
		update_post_meta( $article_id, '_mkb_dislikes', ++ $current_views );

		$this->update_count_meta( $article_id, '_mkb_dislikes_meta' );

		$status = 0;

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Article feedback
	 */
	public function article_feedback() {
		$this->check_user();

		$article_id     = (int) $_POST['id'];
		$feedback_count = wp_count_posts( 'mkb_feedback' )->publish;

		$feedback_post = array(
			'post_title' => wp_strip_all_tags(
				__( 'Article feedback' .
				    ( $feedback_count > 0 ?
					    ' #' . ( $feedback_count + 1 ) :
					    '' ), 'minerva-kb' ) ),
			'post_content' => wp_strip_all_tags( $_POST['content'] ),
			'post_status' => 'publish',
			'post_type' => 'mkb_feedback'
		);
		$feedback_post_id = wp_insert_post( $feedback_post );

		// older WP versions
		add_post_meta($feedback_post_id, 'feedback_article_id', $article_id);

		$status = 0;

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Saves plugin settings
	 */
	public function save_settings() {
		$this->check_admin_user();

		$settings = $_POST['settings'];

		if ( ! $settings || empty( $settings ) ) {
			wp_die();
		}

		MKB_Options::save( $settings );

		$status = 0;

		echo json_encode( array(
			'status' => $status,
			'settings' => MKB_Options::get()
		) );

		wp_die();
	}

	/**
	 * Resets plugin settings
	 */
	public function reset_settings() {
		$this->check_admin_user();

		MKB_Options::reset();

		$status = 0;

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Import plugin settings
	 */
	public function import_settings() {
		$this->check_admin_user();

		$import_data = $_POST['importData'];

		if ( ! isset($import_data) ) {
			wp_die();
		}

		$status = MKB_Options::import( $import_data ) ? 0 : 1;

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * uninstall plugin data
	 */
	public function uninstall_plugin() {
		$this->check_admin_user();

		$status = 0;

		MKB_Options::remove_data();
		MinervaKB_Analytics::delete_all_feedback();
		MinervaKB_DemoImporter::remove_data();
		MKB_DbModel::delete_schema();

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Imports dummy data
	 */
	public function demo_import() {
		$this->check_admin_user();

		$status = 0;

		$set_home_page = $_POST['setHomePage'] === 'true';

		ob_start();
		$entries = MinervaKB_DemoImporter::run_import(array('set_home_page' => $set_home_page));
		$output = ob_get_clean();

		echo json_encode( array(
			'status' => $status,
			'output' => $output,
			'entities_html' => MinervaKB_DemoImporter::get_entities_html($entries)
		) );

		wp_die();
	}

	/**
	 * Skips dummy data import
	 */
	public function skip_demo_import() {
		$this->check_admin_user();

		$status = 0;

		MinervaKB_DemoImporter::skip_import();

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Removes selected imported entities
	 */
	public function remove_import_entities() {
		$this->check_admin_user();

		$status = 0;

		$ids = $_POST['ids'];
		$type = $_POST['type'];

		$status = MinervaKB_DemoImporter::remove_import_entities($type, $ids);

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Removes all imported entities
	 */
	public function remove_all_import_entities() {
		$this->check_admin_user();

		$status = 0;

		$status = MinervaKB_DemoImporter::remove_all_import_entities();

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Gets month analytics
	 */
	public function get_month_analytics() {
		$this->check_admin_user();

		$status = 0;

		echo json_encode( array(
			'status' => $status,
			'graphDates' => $this->analytics->get_recent_month_dates(),
			'graphViews' => $this->analytics->get_recent_month_views(),
			'graphLikes' => $this->analytics->get_recent_month_likes(),
			'graphDislikes' => $this->analytics->get_recent_month_dislikes(),
		) );

		wp_die();
	}

	/**
	 * Gets week analytics
	 */
	public function get_week_analytics() {
		$this->check_admin_user();

		$status = 0;

		echo json_encode( array(
			'status' => $status,
			'graphDates' => $this->analytics->get_recent_week_dates(),
			'graphViews' => $this->analytics->get_recent_week_views(),
			'graphLikes' => $this->analytics->get_recent_week_likes(),
			'graphDislikes' => $this->analytics->get_recent_week_dislikes(),
		) );

		wp_die();
	}

	/**
	 * Gets home page builder section html
	 */
	public function get_section_html() {
		$settings_builder = new MKB_SettingsBuilder();
		$layout_editor    = new MKB_LayoutEditor( $settings_builder );

		$this->check_admin_user();

		$status = 0;

		echo json_encode( array(
			'status' => $status,
			'html' => $layout_editor->get_section_html( $_POST['section_type'], $_POST['position'] )
		) );

		wp_die();
	}

	/**
	 * DnD articles reorder
	 */
	public function reorder_articles() {
		$this->check_admin_user();

		parse_str( $_POST['new_order'], $new_order );

		if ( ! is_array( $new_order ) ) {
			wp_die(); // wrong data
		}

		$new_order = isset( $new_order["post"] ) ? $new_order["post"] : array();

		if ( sizeof( $new_order ) < 2 ) {
			wp_die(); // nothing to sort
		}

		global $wpdb;

		foreach ( $new_order as $order => $id ): // settings custom articles order using menu_order field
		{
			$wpdb->update(
				$wpdb->prefix . 'posts',
				array(
					'menu_order' => $order
				),
			array( 'ID' => $id, 'post_type' => MKB_Options::option( 'article_cpt' ) ),
				array(
					'%d'
				),
				array(
					'%d',
					'%s'
				)
			);
		}

		endforeach;

		$status = 0;

		echo json_encode( array(
			'status' => $status,
			'order' => $new_order
		) );

		wp_die();
	}

	/**
	 * Gets article list
	 */
	public function get_articles_list() {
		$query_args = array(
			'post_type' => MKB_Options::option( 'article_cpt' ),
			'post__not_in' => array( (int) $_POST['currentId'] ),
			'posts_per_page' => - 1
		);

		$articles_loop = new WP_Query( $query_args );
		$articles_list = array();

		if ( $articles_loop->have_posts() ) :
			while ( $articles_loop->have_posts() ) : $articles_loop->the_post();
				array_push( $articles_list, array(
					"title" => get_the_title(),
					"id" => get_the_ID()
				) );
			endwhile;
		endif;
		wp_reset_postdata();

		$status = 0;

		echo json_encode( array(
			'articles' => $articles_list,
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Saves posts sorting
	 */
	public function save_sorting() {
		$this->check_admin_user();

		$sorting = $_POST['sorting'];
		$tax = $_POST['taxonomy'];

		if ( ! $sorting || empty( $sorting ) || !$tax ) {
			wp_die();
		}

		foreach($sorting as $term_id => $posts):
			foreach($posts as $index => $id):
				wp_update_post( array(
					'ID' => (int) $id,
					'menu_order' => (int)$index,
				));
			endforeach;
		endforeach;

		$status = 0;

		echo json_encode( array(
			'status' => $status,
			'sorting' => $sorting,
			'tax' => $tax
		) );

		wp_die();
	}


	/**
	 * Saves terms sorting
	 */
	public function save_terms_sorting() {
		$this->check_admin_user();

		$sorting = $_POST['sorting'];
		$tax = $_POST['taxonomy'];

		if ( ! $sorting || empty( $sorting ) || !$tax ) {
			wp_die();
		}

		foreach($sorting as $term_id => $order):
			MKB_TemplateHelper::set_topic_option($term_id, 'topic_order', $order);
		endforeach;

		$status = 0;

		echo json_encode( array(
			'status' => $status,
			'sorting' => $sorting,
			'tax' => $tax
		) );

		wp_die();
	}

	/**
	 * Removes feedback entry
	 */
	public function remove_feedback() {
		$this->check_admin_user();

		$status = 0;

		if ( isset( $_POST['feedback_id'] ) ) {
			wp_trash_post( (int) $_POST['feedback_id'] );
		}

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Return keyword article recent matches
	 */
	public function get_hit_results() {
		$this->check_admin_user();

		$status = 0;

		$results = MKB_DbModel::get_search_hit_results( $_POST["hit_id"] );

		if ($results && sizeof($results)) {
			$results = array_map(function($result_id) {
				return array(
					'id' => $result_id,
					'title' => get_the_title($result_id),
					'link' => get_the_permalink($result_id)
				);
			}, $results);
		}

		echo json_encode( array(
			'status' => $status,
			'articles' => $results
		) );

		wp_die();
	}

	/**
	 * Gets search stats
	 */
	public function get_search_stats_page() {
		$this->check_admin_user();

		$status = 0;

		$page = (int) $_POST['page'];
		$field = $_POST['field'];
		$order = $_POST['order'];
		$items_per_page = 20;

		$results = array_slice($this->analytics->get_keywords(array(
			"field" => $field,
			"order" => $order
		)), $page * $items_per_page, $items_per_page);

		echo json_encode( array(
			'status' => $status,
			'stats' => $results
		) );

		wp_die();
	}

	/**
	 * Gets search stats ordered
	 */
	public function get_ordered_search_stats() {
		$this->check_admin_user();

		$status = 0;

		$page = (int) $_POST['page'];
		$field = $_POST['field'];
		$order = $_POST['order'];

		$items_per_page = 20;

		$results = array_slice($this->analytics->get_keywords(array(
			"field" => $field,
			"order" => $order
		)), $page * $items_per_page, $items_per_page);

		echo json_encode( array(
			'status' => $status,
			'stats' => $results
		) );

		wp_die();
	}

	/**
	 * Resets stats on user request
	 */
	public function reset_stats() {
		$this->check_admin_user();

		$status = 0;

		$config = $_POST['resetConfig'];

		$articleId = isset($_POST['articleId']) ? $_POST['articleId'] : null;

		if ($articleId !== null) {
			$this->reset_article_stats((int)$articleId, $config);
		} else {
			$query_args = array(
				'post_type' => MKB_Options::option( 'article_cpt' ),
				'posts_per_page' => - 1
			);

			$articles_loop = new WP_Query( $query_args );

			if ( $articles_loop->have_posts() ) :
				while ( $articles_loop->have_posts() ) : $articles_loop->the_post();
					$id = get_the_ID();

					$this->reset_article_stats($id, $config);
				endwhile;
			endif;
			wp_reset_postdata();
		}

		if ($articleId === null && isset($config['search']) && $config['search'] === "true") {
			// reset search
			MKB_DbModel::reset_search_data();
		}

		echo json_encode( array(
			'status' => $status,
			'config' => $config
		) );

		wp_die();
	}

	/**
	 * Helper to reset single article stats
	 * @param $id
	 * @param $config
	 */
	private function reset_article_stats($id, $config) {
		if (isset($config['dislikes']) && $config['dislikes'] === "true") {
			// reset dislikes
			delete_post_meta($id, '_mkb_dislikes');
			delete_post_meta($id, '_mkb_dislikes_meta');
		}

		if (isset($config['likes']) && $config['likes'] === "true") {
			// reset likes
			delete_post_meta($id, '_mkb_likes');
			delete_post_meta($id, '_mkb_likes_meta');
		}

		if (isset($config['views']) && $config['views'] === "true") {
			// reset views
			delete_post_meta($id, '_mkb_views');
			delete_post_meta($id, '_mkb_views_meta');
		}
	}

	/**
	 * Flush restriction cache
	 */
	public function flush_restriction() {
		$this->check_admin_user();

		$status = 0;

		global $minerva_kb;
		$minerva_kb->restrict->invalidate_restriction_cache();

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * View restriction log
	 */
	public function view_restriction_log() {
		$this->check_admin_user();

		$status = 0;

		global $minerva_kb;
		$log = $minerva_kb->restrict->get_recent_visitors_log();

		echo json_encode( array(
			'status' => $status,
			'log' => $log
		) );

		wp_die();
	}

	/**
	 * Clear restriction log
	 */
	public function clear_restriction_log() {
		$this->check_admin_user();

		$status = 0;

		global $minerva_kb;
		$minerva_kb->restrict->clear_recent_visitors_log();

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Gets options html for shortcode
	 */
	public function get_shortcode_options() {
		$status = 0;

		$shortcode = isset($_POST['shortcode']) ? $_POST['shortcode'] : '';
		$values = isset($_POST['values']) ? $_POST['values'] : array();

		global $minerva_kb;

		$options = $minerva_kb->shortcodes->get_options_for($shortcode);

		ob_start();
		$settings_helper = new MKB_SettingsBuilder();
		?><div class="mkb-shortcode-options">
			<?php
			if (!empty($options)):
				foreach ( $options as $option ):
					$settings_helper->render_option(
						$option["type"],
						isset($values[$option['id']]) ? $values[$option['id']] : $option["default"],
						$option
					);
				endforeach;
			else:
				?><div class="mkb-shortcode-no-options"><?php
				_e( 'This shortcode has no options', 'minerva-kb' );
				?></div><?php
			endif;
		?></div>
		<?php
		$html = ob_get_clean();

		echo json_encode( array(
			'status' => $status,
			'count' => sizeof($options),
			'html' => $html
		) );

		wp_die();
	}

	/**
	 * Receives and saves client submission
	 */
	public function save_client_submission() {
		$status = 0;
		$error = '';

		$title = isset($_POST['title']) ? trim($_POST['title']) : '';
		$topic = isset($_POST['topic']) ? $_POST['topic'] : '';
		$content = isset($_POST['content']) ? trim($_POST['content']) : '';
		$antispam = isset($_POST['antispam']) ? trim($_POST['antispam']) : '';

		// filter to block submission by user parameters (IP, location, etc.)
		if (!apply_filters('minerva_guestpost_allow_post', true)) {
			$status = 1;
			$error = __( 'Submission disabled by server rules', 'minerva-kb' );
		}

		// content and title must not be empty
		if (!$title || !$content) {
			$status = 1;
			$error = __( 'Title and content are required', 'minerva-kb' );
		}

		// unique titles check
		if (MKB_Options::option('submit_unique_titles') && post_exists( $title )) {
			$status = 1;
			$error = MKB_Options::option('submit_unique_titles_error_message');
		}

		// antispam
		if (MKB_Options::option('antispam_quiz_enable') && $antispam != MKB_Options::option('antispam_quiz_answer')) {
			$status = 1;
			$error = MKB_Options::option('antispam_failed_message');
		}

		if ($status === 0) {
			$client_article = array(
				'post_title' => wp_strip_all_tags( apply_filters('minerva_guestpost_title', $title) ),
				'post_content' => wpautop( wp_kses_post( apply_filters('minerva_guestpost_content', $content) ) ),
				'post_status' => apply_filters('minerva_guestpost_post_status', 'draft'),
				'post_type' => MKB_Options::option('article_cpt')
			);

			$create_result = wp_insert_post( $client_article );

			if ($create_result == 0) {
				$status = 1;
				$error = __( 'Article not created, some unknown error happened', 'minerva-kb' );
			} else {
				if (MKB_Options::option('submit_allow_topics_select') && $topic) {
					wp_set_object_terms( $create_result, array( (int)$topic ), MKB_Options::option('article_cpt_category') );
				}
			}
		}

		echo json_encode( array(
			'status' => $status,
			'error' => $error
		) );

		wp_die();
	}

	/**
	 * Purchase verification via purchase code
	 */
	public function verify_purchase() {
		$status = 0;

		$purchase_code = isset($_REQUEST['code']) ? trim($_POST['code']) : '';
		$check_result = false;

		if (!$purchase_code) {
			$status = 1;
		} else {
			MKB_Options::save_option('auto_updates_verification', $purchase_code);

			try {
				$check_result = MinervaKB_AutoUpdate::verify_purchase(true);
			} catch (Exception $e) {
				$check_result = false;
			}
		}

		echo json_encode( array(
			'status' => $status,
			'check_result' => $check_result
		) );

		wp_die();
	}

	public function track_attachment_download() {
		$status = 0;

		$attachment_id = isset($_POST['id']) ? $_POST['id'] : '';

		if ($attachment_id) {
			MinervaKB_ArticleEdit::track_attachment_download($attachment_id);
		}

		echo json_encode( array(
			'status' => $status,
		) );

		wp_die();
	}
}
