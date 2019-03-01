<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

/**
 * Class MinervaKB_Content
 * Manges KB content rendering
 */
class MinervaKB_Content {

	private $info;

	private $restrict;

	private $search_results_count = null;

	private $search_filter_running = false;

	/**
	 * Constructor
	 * @param $deps
	 */
	public function __construct($deps) {

		$this->setup_dependencies($deps);

		// content
		add_filter('the_content', array($this, 'content_filter'));
		add_action('minerva_single_article_content', array($this, 'render_article_content'));

		// body classes
		add_filter('body_class', array($this, 'body_class_filter'));

		// templates
		add_filter('single_template', array($this, 'single_template_filter'), 999);
		add_filter('archive_template', array($this, 'archive_template_filter'), 999);
		add_filter('taxonomy_template', array($this, 'archive_template_filter'), 999);
		add_filter('page_template', array($this, 'page_template_filter'), 999);
		add_filter('template_include', array($this, 'search_template_filter'));
		add_filter('template_include', array($this, 'page_template_filter'));
		add_filter('template_include', array($this, 'single_template_filter'), 999);

		// comments
		add_filter('comments_open', array($this, 'comments_filter'), 10, 2);

		// search results
		add_filter('pre_get_posts', array($this, 'search_filter'));

		if (MKB_Options::option('search_include_tag_matches')) {
			add_filter('posts_join', array( $this, 'search_tags_join_filter' ), 10, 2);
			add_filter('posts_where', array( $this, 'search_tags_where_filter' ), 10, 2);
			add_filter('posts_distinct', array( $this, 'search_tags_distinct_filter' ), 10, 2);
		}

		// topic loop
		add_filter('pre_get_posts', array($this, 'topic_filter'));

		// topic html filter
		if (MKB_Options::option('raw_topic_description_switch')) {
			add_filter('get_the_archive_description', array($this, 'raw_topic_description_filter'));
		}

		// tag loop
		add_filter('pre_get_posts', array($this, 'tag_filter'));
	}

	/**
	 * Tags search SQL filter for join part
	 * @param $join
	 * @param $query
	 *
	 * @return string
	 */
	public function search_tags_join_filter($join, $query) {
		if (!$this->is_kb_search_query($query)) {
			return $join;
		}

		if (!empty($query->query_vars['s'])) {
			$tag_sql = MKB_DbModel::get_search_tags_join_clauses($query->query_vars['s']);
			$join .= $tag_sql["join"];
		}

		return $join;
	}

	/**
	 * Tags search SQL filter for where part
	 * @param $where
	 * @param $query
	 *
	 * @return string
	 */
	public function search_tags_where_filter( $where, $query ) {
		if (!$this->is_kb_search_query($query)) {
			return $where;
		}

		if (!empty($query->query_vars['s'])) {
			$tag_sql = MKB_DbModel::get_search_tags_join_clauses($query->query_vars['s']);
			$where .= $tag_sql["where"];
		}

		return $where;
	}

	/**
	 * Tags search SQL distinct
	 * @param $distinct
	 * @param $query
	 *
	 * @return string
	 */
	public function search_tags_distinct_filter( $distinct, $query ) {
		if (!$this->is_kb_search_query($query)) {
			return $distinct;
		}

		return 'DISTINCT';
	}

	/**
	 * Checks if given query is KB search query
	 * @param $query
	 *
	 * @return bool
	 */
	public function is_kb_search_query($query) {
		// NOTE, cannot use info here, runs before query is set
		return $query->is_search &&
		       (
			       isset($_REQUEST['source']) && $_REQUEST['source'] == 'kb' && $query->is_main_query() ||
			       isset($_REQUEST['action']) && $_REQUEST['action'] == 'mkb_kb_search'
		       );
	}

	/**
	 * Sets up dependencies
	 * @param $deps
	 */
	private function setup_dependencies($deps) {
		if (isset($deps['info'])) {
			$this->info = $deps['info'];
		}

		if (isset($deps['restrict'])) {
			$this->restrict = $deps['restrict'];
		}
	}

	/**
	 * Content handlers
	 */
	public function content_filter( $content ) {
		global $minerva_kb_content_filter_running_global;

		if (!is_main_query() || $minerva_kb_content_filter_running_global) {
			return $content;
		}

		// process content
		$content = $this->maybe_add_single_content( $content );
		$content = $this->maybe_add_home_page_content( $content );

		return $content;
	}

	/**
	 * Adds home page content if necessary
	 * @param $content
	 * @return string
	 */
	private function maybe_add_home_page_content( $content ) {
		global $minerva_kb_page_render;

		if (!get_post_type() === 'page' || MKB_Options::option( 'page_template' ) === 'plugin' || $minerva_kb_page_render) {
			return $content;
		}

		if ( $this->info->is_builder_home() ) {

			ob_start();
			if ($this->restrict->check_access()) {
				?><div class="mkb-root mkb-clearfix"><?php
				include( MINERVA_KB_PLUGIN_DIR . '/lib/templates/kb-builder-page.php' );
				?></div><?php
			}
			$kb_content = ob_get_clean();

			return $kb_content;
		} else if ( $this->info->is_settings_home() ) {

			ob_start();
			if ($this->restrict->check_access()) {
				?><div class="mkb-root mkb-clearfix"><?php
				include( MINERVA_KB_PLUGIN_DIR . '/lib/templates/kb-home.php' );
				?></div><?php
			}
			$kb_content = ob_get_clean();

			if ( MKB_Options::option( 'show_page_content' ) === 'before' ) {
				$kb_content = $content . $kb_content;
			} else if ( MKB_Options::option( 'show_page_content' ) === 'after' ) {
				$kb_content = $kb_content . $content;
			}

			return $kb_content;
		}

		return $content;
	}

	/**
	 * Adds single content if necessary
	 * @param $content
	 * @return string
	 */
	private function maybe_add_single_content( $content ) {
		if ( $this->info->is_single() && MKB_Options::option( 'single_template' ) === 'theme' && !MKB_Options::option('article_no_content_filter')) {
			$content = $this->add_single_content_elements($content);
		}

		return $content;
	}

	public function render_article_content() {
		$content = do_shortcode(get_the_content());

		echo $this->add_single_content_elements($content);
	}

	private function add_single_content_elements($content) {
		$before = '<div class="mkb-article-text">';
		$after = '</div>';

		if ($this->restrict->check_access()) {
			ob_start();

			?>
			<div class="mkb-single-theme-before">
				<?php do_action('minerva_single_title_after'); ?>
			</div>
			<?php

			$this->single_header_meta();
			$content = ob_get_clean() . $before . $content . $after;

			ob_start();
			$this->single_footer_meta();

			?>
			<div class="mkb-single-theme-after">
				<?php do_action('minerva_single_content_after'); ?>
			</div>
			<?php

			$content .= ob_get_clean();
		} else {
			$content = $before . $this->restrict->get_message() . $after;
		}

		return $content;
	}

	/**
	 * Single header meta
	 */
	private function single_header_meta() {
		?>
		<div class="mkb-article-header">
			<?php

			do_action('minerva_single_entry_header_meta');

			?>
		</div>
	<?php
	}

	/**
	 * Single footer meta
	 */
	private function single_footer_meta() {
		?>
		<div class="mkb-article-extra">
			<div class="mkb-article-extra__hidden">
				<span class="mkb-article-extra__tracking-data"
			        data-article-id="<?php echo esc_attr( get_the_ID() ); ?>"
			        data-article-title="<?php echo esc_attr( get_the_title() ); ?>"></span>
			</div>
			<?php

			do_action('minerva_single_entry_footer_meta');

			?>
		</div>
	<?php
	}

	/**
	 * Controls comments display for articles
	 * @param $open
	 * @param $post_id
	 * @return bool|null
	 */
	public function comments_filter( $open, $post_id ) {
		// if not open, or not KB article, just return
		if ( ! $open || ! $this->info->is_single()) {
			return $open;
		}

		// if article is restricted, return false
		if (!$this->restrict->check_access()) {
			return false;
		}

		return MKB_Options::option( 'enable_comments' );
	}

	/**
	 * Single template
	 * @param $single_template
	 *
	 * @return string
	 */
	public function single_template_filter($single_template) {

		if ($this->info->is_single() && MKB_Options::option( 'single_template' ) === 'plugin') {
			return $this->locate_template('single');
		}

		return $single_template;
	}

	/**
	 * Archives template
	 * @param $archive_template
	 *
	 * @return string
	 */
	public function archive_template_filter( $template ) {

		/**
		 * KB Tags
		 */
		if ($this->info->is_tag() && MKB_Options::option('tag_template') === 'plugin') {
			return $this->locate_template('tag');
		}

		/**
		 * KB Version tag
		 */
		if ($this->info->is_version_tag()) {
			return $this->locate_template('tag');
		}

		/**
		 * KB Category
		 */
		if ($this->info->is_topic() && MKB_Options::option('topic_template') === 'plugin') {
			return $this->locate_template('category');
		}

		/**
		 * KB Archive
		 */
		if ( $this->info->is_article_archive() && MKB_Options::option('archive_template') === 'plugin' ) {
			return $this->locate_template('archive');
		}

		/**
		 * Default template
		 */
		return $template;
	}

	/**
	 * Page template
	 * @param $page_template
	 *
	 * @return string
	 */
	public function page_template_filter($page_template) {

		if ($this->info->is_home() && MKB_Options::option( 'page_template' ) === 'plugin') {
			return $this->locate_template('page');
		}

		return $page_template;
	}

	/**
	 * Gets template path looking for theme override
	 * @param $template
	 * @return string
	 */
	private function locate_template($template) {
		$theme_override = MINERVA_THEME_DIR . '/minerva-kb/' . $template . '.php';

		if (file_exists($theme_override)) {
			return $theme_override;
		}

		return MINERVA_KB_PLUGIN_DIR . 'lib/templates/' . $template . '.php';
	}

	/**
	 * Search template filter
	 * @param $template
	 * @return string
	 */
	public function search_template_filter($template){
		if (!$this->info->is_search() || !isset($_REQUEST['source']) || $_REQUEST['source'] !== 'kb') {
			return $template;
		}

		return $this->locate_template('search');
	}

	/**
	 * Search results filter
	 * @param $query
	 * @return mixed
	 */
	public function search_filter($query) {

		if (!$this->is_kb_search_query($query)) {
			return $query;
		}

		if (!$this->info->is_ajax()) { // ajax request set these params manually
			$query->set('post_type', array( MKB_Options::option( 'article_cpt' ) ));
			$query->set('posts_per_page', (int) MKB_Options::option('search_results_per_page'));
			$query->set('order_by', 'relevance');
		}

		if (isset($_REQUEST['kb_id']) && (int)$_REQUEST['kb_id'] > 0) {
			$tax_query = array(
				'taxonomy' => MKB_Options::option( 'article_cpt_category' ),
				'field' => 'id',
				'terms' => array((int)$_REQUEST['kb_id']),
				'include_children' => true
			);

			$query->tax_query->queries[] = $tax_query;
			$query->query_vars['tax_query'] = $query->tax_query->queries;

		} else if (isset($_REQUEST['topics']) && !empty($_REQUEST['topics'])) {
			$tax_query = array(
				'taxonomy' => MKB_Options::option( 'article_cpt_category' ),
				'field' => 'id',
				'terms' => explode(',', $_REQUEST['topics']),
				'include_children' => true
			);

			$query->tax_query->queries[] = $tax_query;
			$query->query_vars['tax_query'] = $query->tax_query->queries;
		}

		if ( trim( $query->query_vars['s'] ) == '' ) {
			$query->set('post__in', array(-1));

			return $query;
		}

		/**
		 * Remove restricted articles from query, if required
		 */
		if (MKB_Options::option('restrict_on') && MKB_Options::option('restrict_remove_from_search')) {
			$posts_in = $this->restrict->get_allowed_article_ids_query();

			if ($posts_in) {
				$query->set('post__in', $posts_in);
			}
		}

		return $query;
	}

	/**
	 * Topic items filter
	 * @param $query
	 * @return mixed
	 */
	public function topic_filter($query) {

		// NOTE, cannot use info here, runs before query is set
		if ( !$query->is_main_query() || !$query->is_tax || !is_tax(MKB_Options::option('article_cpt_category')) ) {
			return $query;
		}

		// control child topics articles display, included by default in wordpress
		if (!MKB_Options::option('topic_children_include_articles')) {
			$tax_obj = $query->get_queried_object();

			$tax_query = array(
				'taxonomy' => $tax_obj->taxonomy,
				'field' => 'slug',
				'terms' => $tax_obj->slug,
				'include_children' => false
			);
			$query->tax_query->queries[] = $tax_query;
			$query->query_vars['tax_query'] = $query->tax_query->queries;
		}

		/**
		 * Remove restricted articles from query, if required
		 */
		if (MKB_Options::option('restrict_on') && MKB_Options::option('restrict_remove_from_archives')) {
			$posts_in = $this->restrict->get_allowed_article_ids_query();

			if ($posts_in) {
				$query->set('post__in', $posts_in);
			}
		}

		$query->set('posts_per_page', (int) MKB_Options::option('topic_articles_per_page'));

		return $query;
	}

	/**
	 * Topic items filter
	 * @param $query
	 * @return mixed
	 */
	public function tag_filter($query) {

		if ( !$query->is_main_query() || !$query->is_tax || !is_tax(MKB_Options::option('article_cpt_tag')) ) {
			return $query;
		}

		/**
		 * Remove restricted articles from query, if required
		 */
		if (MKB_Options::option('restrict_on') && MKB_Options::option('restrict_remove_from_archives')) {
			$posts_in = $this->restrict->get_allowed_article_ids_query();

			if ($posts_in) {
				$query->set('post__in', $posts_in);
			}
		}

		$query->set('posts_per_page', (int) MKB_Options::option('tag_articles_per_page'));

		return $query;
	}

	/**
	 * Replaces term description with raw value
	 * @param $description
	 *
	 * @return mixed
	 */
	public function raw_topic_description_filter($description) {
		if (is_tax(MKB_Options::option('article_cpt_category'))) {

			$term = get_queried_object();

			if ($term && isset($term->description)) {
				return $term->description;
			}
		}

		return $description;
	}

	/**
	 * Body extra classes
	 * @param $classes
	 * @return array
	 */
	public function body_class_filter($classes) {

		// device classes
		if ( $this->info->is_tablet() ) {
			$classes[] = 'mkb-tablet';
		} else if ( $this->info->is_mobile() ) {
			$classes[] = 'mkb-mobile';
		} else {
			$classes[] = 'mkb-desktop';
		}

		if ($this->info->is_rtl()) {
			$classes[] = 'mkb-rtl';
		}

		// KB template classes
		if ($this->info->is_home()) {
			$classes[] = 'mkb-home-page';
		}

		if ( $this->info->is_builder_home() ) {
			$classes[] = 'mkb-builder-home-page';
		} else if ( $this->info->is_settings_home() ) {
			$classes[] = 'mkb-settings-home-page';
		} else if ( $this->info->is_archive() ) {
			$classes[] = 'mkb-archive';
		} else if ( $this->info->is_single() ) {
			$classes[] = 'mkb-single';
		} else if ( $this->info->is_search() ) {
			$classes[] = 'mkb-search';
		}

		$classes[] = 'mkb-version-' . str_replace('.', '-', strtolower(MINERVA_KB_VERSION));

		return $classes;
	}
}
