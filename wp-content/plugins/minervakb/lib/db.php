<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

/**
 * MinervaKB custom DB tables schema and internal API
 * Class MKB_DbModel
 */
class MKB_DbModel {

	// tables plugin prefix
	const PLUGIN_PREFIX = 'mkb_';

	// table names
	const KEYWORDS_TABLE_NAME = 'keywords';
	const HITS_TABLE_NAME = 'hits';
	const HITS_META_TABLE_NAME = 'hits_meta';

	// hit types
	const HIT_TYPE_SEARCH = 0;
	const HIT_TYPE_LIKE = 1;
	const HIT_TYPE_DISLIKE = 2;
	const HIT_TYPE_FEEDBACK = 3;

	/**
	 * Tracks analytics event
	 * @param $hit_type
	 * @param $hit_data
	 */
	public static function register_hit($hit_type, $hit_data) {
		switch ($hit_type) {
			case self::HIT_TYPE_SEARCH:
				self::save_search_hit($hit_data);
				break;

			default:
				break;
		}
	}

	/**
	 * Saves search hit and all related metadata
	 * @param $data
	 */
	private static function save_search_hit($data) {

		$keyword = $data["keyword"];
		$results_count = $data["results_count"];
		$results_ids = $data["results_ids"];

		global $wpdb;

		$keyword_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM " . self::get_table_name_for( self::KEYWORDS_TABLE_NAME ) . "
                    WHERE keyword = %s LIMIT 1",
				$keyword
			)
		);

		if (!$keyword_id) { // does not exist, save to db
			// save keyword
			$wpdb->insert(
				self::get_table_name_for( self::KEYWORDS_TABLE_NAME ),
				array(
					'keyword' => strtolower($keyword)
				),
				array(
					'%s'
				)
			);

			$keyword_id = $wpdb->insert_id;
		}

		$creation_timestamp = current_time( 'mysql' );

		// save hits
		$wpdb->insert(
			self::get_table_name_for( self::HITS_TABLE_NAME ),
			array(
				'type' => self::HIT_TYPE_SEARCH,
				'keyword_id'  => $keyword_id,
				'registered_at'  => $creation_timestamp,
			),
			array(
				'%d',
				'%s',
				'%s'
			)
		);

		$hit_id = $wpdb->insert_id;

		// save hit result count
		$wpdb->insert(
			self::get_table_name_for( self::HITS_META_TABLE_NAME ),
			array(
				'hit_id'  => $hit_id,
				'meta_key'  => "results_count",
				'meta_value'  => $results_count,
			),
			array(
				'%d',
				'%s',
				'%d'
			)
		);

		if ($results_count) {
			// save hit result ids
			$wpdb->insert(
				self::get_table_name_for( self::HITS_META_TABLE_NAME ),
				array(
					'hit_id'  => $hit_id,
					'meta_key'  => "results_ids",
					'meta_value'  => json_encode($results_ids),
				),
				array(
					'%d',
					'%s',
					'%s'
				)
			);
		}
	}

	/**
	 * Gets search statistics ordered by most searched keyword
	 * @return array|null|object
	 */
	public static function get_top_keywords($order_options = array()) {
		global $wpdb;

		$keywords_table_name = self::get_table_name_for( self::KEYWORDS_TABLE_NAME );
		$hits_table_name = self::get_table_name_for( self::HITS_TABLE_NAME );
		$hits_meta_table_name = self::get_table_name_for( self::HITS_META_TABLE_NAME );
		$hit_type = self::HIT_TYPE_SEARCH;

		$order_by = 'hit_count';
		$order = 'DESC';

		if (isset($order_options) && !empty($order_options)) {
			$field = $order_options["field"];
			$order = $order_options["order"];

			switch ($field) {
				case 'keyword':
					$order_by = 'keyword';
					break;

				case 'hits':
					$order_by = 'hit_count';
					break;

				case 'results':
					$order_by = 'last_results';
					break;

				case 'date':
					$order_by = 'last_search';
					break;

				default:
					break;
			}
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					h.keyword_id AS id,
					h.id AS hit_id,
					k.keyword AS keyword,
					hm.meta_value AS last_results,
					MAX(registered_at) AS last_search,
					COUNT(h.id) AS hit_count
				FROM $hits_table_name AS h
					LEFT JOIN $keywords_table_name AS k ON k.id=h.keyword_id
					LEFT JOIN $hits_meta_table_name AS hm ON hm.hit_id=h.id AND hm.meta_key='results_count'
				WHERE h.type=%d
				GROUP BY keyword_id
				ORDER BY $order_by $order;",
				$hit_type
			)
		);

		return $results;
	}

	/**
	 * Gets results for specific search hit
	 * @param $hit_id
	 *
	 * @return array|mixed|object
	 */
	public static function get_search_hit_results($hit_id) {
		global $wpdb;

		$hits_table_name = self::get_table_name_for( self::HITS_TABLE_NAME );
		$hits_meta_table_name = self::get_table_name_for( self::HITS_META_TABLE_NAME );
		$hit_type = self::HIT_TYPE_SEARCH;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					hm.meta_value AS results
				FROM $hits_table_name AS h
					LEFT JOIN $hits_meta_table_name AS hm ON hm.hit_id=h.id AND hm.meta_key='results_ids'
				WHERE h.type=%d AND h.id=%d;",
				$hit_type,
				$hit_id
			)
		);

		return $results && isset($results[0]) ? json_decode($results[0]->results, true) : array();
	}

	/**
	 * Removes all search data
	 */
	public static function reset_search_data() {
		self::delete_schema();
		self::create_schema();
	}

	/**
	 * Helper to build table names
	 * @param $name
	 *
	 * @return string
	 */
	private static function get_table_name_for( $name ) {
		global $wpdb;

		return $wpdb->prefix . self::PLUGIN_PREFIX . $name;
	}

	/**
	 * For use in WP SQL filters
	 * @param $name
	 *
	 * @return string
	 */
	private static function get_wp_table_name_for( $name ) {
		global $wpdb;

		return $wpdb->prefix . $name;
	}

	/**
	 * Helper to get table charset and collate
	 * @return string
	 */
	private static function get_wp_charset_collate() {
		global $wpdb;
		$charset_collate = '';

		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = " DEFAULT CHARACTER SET $wpdb->charset";
		}

		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}

		return $charset_collate;
	}

	/**
	 * Gets SQL to create keywords table
	 * @return string
	 */
	private static function get_keywords_structure() {
		$table_name = self::get_table_name_for( self::KEYWORDS_TABLE_NAME );

		return "CREATE TABLE $table_name (
		      id int unsigned NOT NULL auto_increment,
		      keyword varchar(128) default '',
		      PRIMARY KEY id (id),
		      UNIQUE KEY keyword (keyword)
		    )";
	}

	/**
	 * Gets SQL to create hits table
	 * @return string
	 */
	private static function get_hits_structure() {
		$table_name = self::get_table_name_for( self::HITS_TABLE_NAME );

		return "CREATE TABLE $table_name (
		      id int unsigned NOT NULL auto_increment,
		      article_id int default NULL,
		      keyword_id int default NULL,
		      type int NOT NULL,
		      registered_at datetime NOT NULL,
		      PRIMARY KEY  (id)
		    )";
	}

	/**
	 * Gets SQL to create hits meta table
	 * @return string
	 */
	private static function get_hits_meta_structure() {
		$table_name = self::get_table_name_for( self::HITS_META_TABLE_NAME );

		return "CREATE TABLE $table_name (
		      id int unsigned NOT NULL auto_increment,
		      hit_id int unsigned NOT NULL,
		      meta_key varchar(255) NOT NULL,
		      meta_value longtext default '',
		      PRIMARY KEY  (id)
		    )";
	}

	public static function get_all_table_names() {
		return array(
			self::get_table_name_for( self::KEYWORDS_TABLE_NAME ),
			self::get_table_name_for( self::HITS_TABLE_NAME ),
			self::get_table_name_for( self::HITS_META_TABLE_NAME )
		);
	}

	/**
	 * Gets join and where clauses for tag search results
	 * @return array
	 */
	public static function get_search_tags_join_clauses($search) {
		$posts = self::get_wp_table_name_for('posts');

		$rel = self::get_wp_table_name_for('term_relationships');
		$rel_alias = self::PLUGIN_PREFIX . $rel;

		$tax = self::get_wp_table_name_for('term_taxonomy');
		$tax_alias = self::PLUGIN_PREFIX . $tax;

		$terms = self::get_wp_table_name_for('terms');
		$terms_alias = self::PLUGIN_PREFIX . $terms;

		return array(
			"join" => "LEFT JOIN $rel AS $rel_alias
				ON $posts.id = $rel_alias.object_id
				LEFT JOIN $tax AS $tax_alias
				ON $rel_alias.term_taxonomy_id = $tax_alias.term_id
				LEFT JOIN $terms AS $terms_alias
				ON $rel_alias.term_taxonomy_id = $terms_alias.term_id ",

			"where" => " OR ($tax_alias.taxonomy = '" . esc_sql(MKB_Options::option( 'article_cpt_tag' )) .
			           "' AND $terms_alias.name = '" . esc_sql($search) .
			           "' AND $posts.post_title NOT LIKE '%" . esc_sql($search) . "%'" .
			           " AND $posts.post_excerpt NOT LIKE '%" . esc_sql($search) . "%'" .
			           " AND $posts.post_content NOT LIKE '%" . esc_sql($search) . "%'" .
			           ") "
		);
	}

	/**
	 * Creates custom tables DB schema (to be called on plugin activation)
	 */
	public static function create_schema() {
		$wp_charset_collate = self::get_wp_charset_collate();
		$sql_postfix = $wp_charset_collate . ';';

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( self::get_keywords_structure() . $sql_postfix );
		dbDelta( self::get_hits_structure() . $sql_postfix );
		dbDelta( self::get_hits_meta_structure() . $sql_postfix );
	}

	/**
	 * Deletes all custom tables
	 */
	public static function delete_schema() {
		global $wpdb;

		if ( !current_user_can( 'administrator' ) ) {
			wp_die();
		}

		$wpdb->query( 'DROP TABLE IF EXISTS ' . self::get_table_name_for( self::KEYWORDS_TABLE_NAME ) );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . self::get_table_name_for( self::HITS_TABLE_NAME ) );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . self::get_table_name_for( self::HITS_META_TABLE_NAME ) );
	}
}

// delete the table whenever a blog is deleted
function mkb_on_delete_blog( $tables ) {
	$mkb_tables = MKB_DbModel::get_all_table_names();

	foreach($mkb_tables as $table) {
		$tables[] = $table;
	}

	return $tables;
}
add_filter( 'wpmu_drop_tables', 'mkb_on_delete_blog' );