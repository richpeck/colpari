<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_Restrict {

	private static $ARTICLE = 'ARTICLE';
	private static $ARCHIVE = 'ARCHIVE';
	private static $HOME = 'HOME';
	private static $UNKNOWN = 'UNKNOWN';

	private static $ARTICLES_CACHE_KEY = 'mkb_allowed_articles_for_roles';
	private static $TOPICS_CACHE_KEY = 'mkb_allowed_topics_for_roles';
	private static $ROLES_LOG_KEY = 'mkb_user_roles_log';
	private static $CACHE_TTL = MONTH_IN_SECONDS;

	private $user_allowed_article_ids;
	private $user_allowed_topic_ids;
	private $current_entity_allowed;
	private $current_user_roles;

	private $is_debug = false;

	private $info;

	public function __construct($deps) {
		$this->setup_dependencies($deps);
	}

	/**
	 * Public check access method for current rendered entity
	 * used for KB home pages, builder pages, single articles
	 * cached
	 * @return bool
	 */
	public function check_access() {

		if (isset($this->current_entity_allowed)) {
			return apply_filters('minerva_restrict_access_allowed', $this->current_entity_allowed);
		}

		$this->current_entity_allowed = MKB_Options::option('restrict_on') ?
			(bool) $this->check_entity_access($this->get_current_entity()) :
			true; // always allowed if restriction is off

		return apply_filters('minerva_restrict_access_allowed', $this->current_entity_allowed);
	}

	/**
	 * Checks if given topic is allowed by restriction rules for current user
	 * @param $topic
	 *
	 * @return bool
	 */
	public function is_topic_allowed($topic) {
		return in_array($topic->term_id, $this->get_allowed_topic_ids_for_user()) || apply_filters('minerva_restrict_access_allowed', false);
	}

	/**
	 * Check if current user passes global restriction setting
	 * @return bool
	 */
	public function is_user_globally_restricted($roles = null) {

		if (!$roles && $this->is_current_user_admin() || apply_filters('minerva_restrict_access_allowed', false)) {
			return false;
		}

		$user_roles = $roles ? $roles : $this->get_current_roles();

		$global_restriction_passed = false;

		$global_allowed_roles = MKB_SettingsBuilder::get_role(MKB_Options::option('restrict_article_role'));

		if (in_array('guest', $global_allowed_roles)) {
			$global_restriction_passed = true;
		} else {
			foreach($global_allowed_roles as $allowed_role) {
				if (in_array($allowed_role, $user_roles)) {
					$global_restriction_passed = true;
					break;
				}
			}
		}

		return !$global_restriction_passed;
	}

	private function is_current_user_admin() {
		return in_array('administrator', $this->get_current_roles());
	}

	/**
	 * Gets allowed articles query param to use in WP_Query
	 * @return array
	 */
	public function get_allowed_article_ids_query() {
		if ($this->is_current_user_admin() || apply_filters('minerva_restrict_access_allowed', false)) {
			return null; // admins can watch all content
		}

		$allowed_ids = $this->get_allowed_article_ids_for_user();

		return !empty($allowed_ids) ? $allowed_ids : array(-1);
	}

	/**
	 * Removes transients for restriction rules
	 * TODO: add non-static wrapper for rebuild
	 */
	public static function invalidate_restriction_cache($rebuild = true) {
		delete_transient(self::$ARTICLES_CACHE_KEY);
		delete_transient(self::$TOPICS_CACHE_KEY);
	}

	/**
	 * Reads current visitors log
	 * @return array|mixed|object|void
	 */
	public function get_recent_visitors_log() {
		$current_log = get_option(self::$ROLES_LOG_KEY);
		$current_log = $current_log ? json_decode($current_log, true) : array();

		if (!empty($current_log)) {
			global $wp_roles;
			$roles = $wp_roles->get_names();
			$roles['guest'] = __('Guest', 'minerva-kb');

			$current_log = array_map(function($entry) use ($roles) {
				$entry_roles = array_map(function($role) use ($roles) {
					return isset($roles[$role]) ? $roles[$role] : $role; // replace keys with names, if available
				}, explode('%', $entry));

				return implode('/', $entry_roles);
			}, $current_log);
		}

		return $current_log;
	}

	/**
	 * Clears recent visitors log
	 */
	public function clear_recent_visitors_log() {
		delete_option(self::$ROLES_LOG_KEY);
	}

	/**
	 * Displays a message about restricted content
	 */
	public function get_message() {

		$entity = $this->get_current_entity();

		switch ($entity) {
			case self::$ARTICLE:
				return $this->maybe_get_restricted_excerpt() .
				       $this->maybe_get_restricted_message() .
				       $this->maybe_get_before_html() .
				       $this->maybe_get_login_form() .
				       $this->maybe_get_after_html();

			case self::$ARCHIVE:
				return MKB_Options::option('restrict_topic_message');

			case self::$HOME:
				return MKB_Options::option('restrict_page_message');

			default:
				break;
		}

		return '';
	}

	/**
	 * Checks current user again role select option value
	 * @param $option_name
	 *
	 * @return bool
	 */
	public function check_current_user_against_option($option_name) {
		$option_roles = MKB_SettingsBuilder::get_role(MKB_Options::option($option_name));
		$user_roles = $this->get_current_roles();

		return $this->check_if_user_in_allowed_roles ($user_roles, $option_roles);
	}

	/**
	 * COMMON
	 */

	/**
	 * Sets up dependencies
	 * @param $deps
	 */
	private function setup_dependencies($deps) {
		if (isset($deps['info'])) {
			$this->info = $deps['info'];
		}
	}

	/**
	 * Checks for user capabilities, highest to lowest, cached
	 * @return string
	 */
	private function get_current_roles() {
		if (isset($this->current_user_roles)) {
			return $this->current_user_roles;
		}

		$user_info = wp_get_current_user();

		$this->current_user_roles = !empty($user_info->roles) ?
			$user_info->roles :
			array('guest');

		return $this->current_user_roles;
	}

	/**
	 * MAIN RESTRICTION CACHED INFO
	 */

	private function rebuild_cache_for_typical_roles() {
		$common_roles = array(
			array('guest'),
			array('subscriber'),
		);

		foreach($common_roles as $roles) {
			$this->get_allowed_article_ids_for_user($roles);
			$this->get_allowed_topic_ids_for_user($roles);
		}
	}

	/**
	 * Gets restriction allowed items list using transients cache
	 * @param $type
	 * @param $transient_key
	 * @param $local_cache_key
	 * @param $callback
	 * @param null $roles
	 *
	 * NOTE: it is critical to pass roles arg at all steps for correct cache invalidation
	 *
	 * @return array|mixed|null
	 */
	private function get_cached_allowed_entries_ids_list($type, $transient_key, &$local_cache_key, $callback, $roles = null) {
		if (isset($local_cache_key) && !$roles) {
			// object cache found, return it
			return $local_cache_key;
		}

		$this->is_debug = isset($_REQUEST['minerva_restrict_transients_debug_on']) && !defined('DOING_AJAX');

		$allowed_entries = null;

		if ($this->is_debug) {
			$time_start = microtime(true);
		}

		$user_roles = $roles ? $roles : $this->get_current_roles();
		$user_profile_key = implode('%', $user_roles);

		if (!$roles) {
			// save to recent roles log
			$this->log_user_profile($user_profile_key);
		}

		$cached_allowed_entries_ids_profiles = get_transient( $transient_key );

		if ($cached_allowed_entries_ids_profiles !== false) {
			$cached_allowed_entries_ids_profiles = json_decode($cached_allowed_entries_ids_profiles, true);
		} else {
			$cached_allowed_entries_ids_profiles = array();
		}

		if ($this->is_debug) {
			echo '<br/>';
		}

		if ( !isset($cached_allowed_entries_ids_profiles[$user_profile_key]) ) {

			if ($this->is_debug) {
				echo 'no ' . $type . ' cache for ' . $user_profile_key . ', updating<br/>';
				echo 'User profile: ';
				print_r($user_profile_key);
				echo '<br/>';
			}

			$allowed_entries = array();

			if (!$this->is_user_globally_restricted($roles)) {
				if ($this->is_debug) {
					echo 'global restriction <span style="color:limegreen;">passed</span><br/>';
				}

				$allowed_entries = call_user_func($callback, $user_roles);
			} else {
				if ($this->is_debug) {
					echo 'global restriction <span style="color:red;">failed</span><br/>';
				}
			}

			$cached_allowed_entries_ids_profiles[$user_profile_key] = $allowed_entries;
			set_transient($transient_key, json_encode($cached_allowed_entries_ids_profiles), self::$CACHE_TTL);
		} else {
			if ($this->is_debug) {
				echo 'got ' . $type . ' cache for ' . $user_profile_key . '<br/>';
			}
			// got cached profile, returning
			$allowed_entries = $cached_allowed_entries_ids_profiles[$user_profile_key];
		}

		if ($this->is_debug) {
			echo '<pre>';
			print_r($allowed_entries);
			echo '</pre>';
			$time_end = microtime(true);
			$time = $time_end - $time_start;
			echo 'total time for ' . $type . ': ' . (int) ( $time * 1000 ) . ' ms</br>';
		}

		// save to object cache
		$local_cache_key = $allowed_entries;

		return $allowed_entries;
	}

	/**
	 * Saves user profiles visiting site for admin restriction
	 * @param $user_profile_key
	 */
	private function log_user_profile($user_profile_key) {
		$current_log = get_option(self::$ROLES_LOG_KEY);
		$current_log = $current_log ? json_decode($current_log, true) : array();
		$current_log[] = $user_profile_key;
		update_option(self::$ROLES_LOG_KEY, json_encode(array_unique($current_log)));
	}

	/**
	 * Gets allowed article ids for current user or guest
	 * @return array
	 */
	private function get_allowed_article_ids_for_user($roles = null) {
		return $this->get_cached_allowed_entries_ids_list(
			'article',
			self::$ARTICLES_CACHE_KEY,
			$this->user_allowed_article_ids,
			array($this, 'get_allowed_article_ids'),
			$roles
		);
	}

	/**
	 * Gets allowed topic ids for current user or guest
	 * @return array
	 */
	private function get_allowed_topic_ids_for_user($roles = null) {
		return $this->get_cached_allowed_entries_ids_list(
			'topic',
			self::$TOPICS_CACHE_KEY,
			$this->user_allowed_topic_ids,
			array($this, 'get_allowed_topic_ids'),
			$roles
		);
	}

	/**
	 * Callback to get allowed article ids for current user
	 * @return array
	 */
	public function get_allowed_article_ids($roles = null) {
		$allowed_articles = array();
		$user_roles = $roles ? $roles : $this->get_current_roles();

		$query_args = array(
			'post_type' => MKB_Options::option( 'article_cpt' ),
			'ignore_sticky_posts' => 1,
			'suppress_filters' => 1,
			'posts_per_page' => -1
		);

		$articles_loop = new WP_Query( $query_args );

		if ($articles_loop->have_posts()):
			while ( $articles_loop->have_posts() ) : $articles_loop->the_post();
				$article_id = get_the_ID();

				// topic restriction check
				if (!$this->check_if_article_allowed_by_topics($user_roles)) {
					if ($this->is_debug) {
						echo $article_id . ' - <strong>' . get_the_title() . '</strong>: topic restriction <span style="color:red;">failed</span></br>';
					}

					continue;
				}

				// article restriction check
				if (!$this->check_if_user_in_allowed_roles($user_roles, $this->get_article_allowed_roles($article_id))) {
					if ($this->is_debug) {
						echo $article_id . ' - <strong>' . get_the_title() . '</strong>: article restriction <span style="color:red;">failed</span></br>';
					}

					continue;
				}

				array_push($allowed_articles, $article_id);

			endwhile;
		endif;

		wp_reset_postdata();

		return $allowed_articles;
	}

	/**
	 * Callback to get allowed topic ids for current user
	 * note: public to be used as callback
	 * @return array
	 */
	public function get_allowed_topic_ids($roles = null) {
		$allowed_topics = array();

		// get all topics
		$topics = get_terms( array(
			'taxonomy' => MKB_Options::option( 'article_cpt_category' ),
			'hide_empty' => false,
		));

		if ($topics && !empty($topics)) {

			foreach($topics as $topic) {
				if (!$this->check_if_topic_allowed($topic, $roles)) {
					continue;
				}

				array_push($allowed_topics, $topic->term_id);
			}
		}

		return $allowed_topics;
	}

	/**
	 * GENERAL ACCESS CHECKS
	 */

	/**
	 * Given user role and entity type returns auth setting
	 * @param $entity
	 * @return bool
	 */
	private function check_entity_access($entity, $roles = null) {

		$user_roles = $roles ? $roles : $this->get_current_roles();

		if (in_array('administrator', $user_roles) || in_array('super', $user_roles) || $entity === self::$UNKNOWN) {
			return true;
		}

		switch ($entity) {
			case self::$ARTICLE:
				return in_array(get_the_ID(), $this->get_allowed_article_ids_for_user());

			case self::$ARCHIVE:
				return true;

			case self::$HOME:
				return true;

			default:
				break;
		}

		return true;
	}

	/**
	 * Gets current rendered entity, cached
	 * @return null|string
	 */
	private function get_current_entity() {
		global $mkb_current_rendered_entity;

		if (isset($mkb_current_rendered_entity)) {
			return $mkb_current_rendered_entity;
		}

		$mkb_current_rendered_entity = $this->get_current_entity_request();

		return $mkb_current_rendered_entity;
	}

	/**
	 * Actual rendered entity request
	 * @return null|string
	 */
	private function get_current_entity_request() {
		if ($this->info->is_single()) {
			return self::$ARTICLE;
		} else if ($this->info->is_archive()) {
			return self::$ARCHIVE;
		} else if ($this->info->is_home()) {
			return self::$HOME;
		}

		return self::$UNKNOWN;
	}

	/**
	 * TOPICS
	 */

	/**
	 * @param $user_roles
	 *
	 * @return array
	 */
	private function check_if_article_allowed_by_topics($roles = null) {

		$topics = wp_get_post_terms( get_the_ID(), MKB_Options::option( 'article_cpt_category' ));

		$access_allowed = true;

		if (sizeof($topics)) {
			foreach($topics as $topic) {
				if (!$this->check_if_topic_allowed($topic, $roles)) {
					$access_allowed = false;
					break;
				}
			}
		}

		return $access_allowed;
	}

	/**
	 * Checks current user access to topic and it's parents branch
	 * @param $topics
	 *
	 * @return bool
	 */
	private function check_if_topic_allowed($topic, $roles = null) {

		$topic_branch_ids = array($topic->term_id);
		$user_roles = $roles ? $roles : $this->get_current_roles();

		$ancestors = get_ancestors( $topic->term_id, MKB_Options::option( 'article_cpt_category' ), 'taxonomy' );

		if (!empty($ancestors)) {
			$topic_branch_ids = array_unique(array_merge($topic_branch_ids, $ancestors));
		}

		$access_allowed = true;

		if (sizeof($topic_branch_ids)) {
			foreach($topic_branch_ids as $topic_id) {
				$topic_roles = $this->get_topic_allowed_roles($topic_id);

				if (!$this->check_if_user_in_allowed_roles($user_roles, $topic_roles)) {
					$access_allowed = false;
					break;
				}
			}
		}

		return $access_allowed;
	}

	/**
	 * Get allowed roles for topic
	 * @param $term_id
	 */
	private function get_topic_allowed_roles($term_id) {
		$term_meta = get_option('taxonomy_' . MKB_Options::option( 'article_cpt_category' ) . '_' . $term_id);

		if ($term_meta && isset($term_meta['topic_restrict_role']) && $term_meta['topic_restrict_role'] != "") {
			return MKB_SettingsBuilder::get_role($term_meta['topic_restrict_role']);
		}

		return array('guest');
	}

	/**
	 * Gets article allowed roles
	 * @param $article_id
	 *
	 * @return array
	 */
	private function get_article_allowed_roles($article_id) {
		$post_restriction_meta = stripslashes(get_post_meta($article_id, '_mkb_restrict_role', true));

		return $post_restriction_meta ? MKB_SettingsBuilder::get_role($post_restriction_meta) : array('guest');
	}

	/**
	 * Checks if user roles array intersects with allowed roles array
	 * @param $user_roles
	 * @param $allowed_roles
	 *
	 * @return bool
	 */
	private function check_if_user_in_allowed_roles ($user_roles, $allowed_roles) {
		if (empty($allowed_roles) ||
		    in_array('guest', $allowed_roles) ||
		    in_array('administrator', $user_roles) ||
		    in_array('super', $user_roles) ||
			sizeof(array_intersect($user_roles, $allowed_roles)) > 0) {

			return true;
		}

		return false;
	}

	/**
	 * TEMPLATES
	 */

	/**
	 * @return string
	 */
	private function maybe_get_restricted_message() {

		if (!MKB_Options::option('restrict_article_message')) {
			return '';
		}

		return '<div class="mkb-restricted-message"><i class="fa ' .
		       esc_attr(MKB_Options::option('restrict_message_icon')) . ' mkb-restricted-message__icon"></i>' .
		       '<div class="mkb-restricted-message__content">' .
		       MKB_Options::option('restrict_article_message') . '</div>' . '</div>';
	}

	/**
	 *
	 * @param $ID
	 * @return string
	 */
	private function maybe_get_restricted_excerpt() {

		if (!MKB_Options::option('restrict_show_article_excerpt')) {
			return '';
		}

		global $post;

		$extra_class = MKB_Options::option('restrict_show_excerpt_gradient') ? ' mkb-article-restricted-excerpt--overlayed' : '';

		return '<br/>' . '<div class="mkb-article-restricted-excerpt' . $extra_class .'">' . $post->post_excerpt . '</div>';
	}

	/**
	 * Adds login form to message text
	 * @param $ID
	 * @return string
	 */
	private function maybe_get_login_form() {

		if (!MKB_Options::option('restrict_show_login_form')) {
			return '';
		}

		$login_container_class = MKB_Options::option('restrict_disable_form_styles') ?
			'mkb-restricted-login--default' :
			'mkb-restricted-login--custom';

		return '<br/>' .
		       '<div class="mkb-restricted-login ' . $login_container_class . '">' .
		       (
		       MKB_Options::option('restrict_disable_form_styles') ?
			       wp_login_form( array('echo' => false) ) :
			       $this->get_custom_login_form()
		       ) . '</div>';
	}

	/**
	 * Gets Minerva custom login form
	 * @return string
	 */
	private function get_custom_login_form() {
		ob_start();

		?>
		<form name="loginform" action="<?php echo site_url('wp-login.php'); ?>" method="post">
			<p class="login-username">
				<label for="mkb_user_login">
					<?php echo esc_html(MKB_Options::option('restrict_login_username_label_text')); ?></label>
				<input type="text" name="log" id="mkb_user_login" class="input" value="" />
			</p>
			<p class="login-password">
				<label for="mkb_user_pass">
					<?php echo esc_html(MKB_Options::option('restrict_login_password_label_text')); ?></label>
				<input type="password" name="pwd" id="mkb_user_pass" class="input" value="" />
			</p>
			<p class="login-remember">
				<label><input name="rememberme" type="checkbox" id="rememberme" value="forever"> <?php
					echo esc_html(MKB_Options::option('restrict_login_remember_label_text')); ?></label>
			</p>
			<p class="login-submit">
				<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary"
				       value="<?php echo esc_attr(MKB_Options::option('restrict_login_text')); ?>" />
				<input type="hidden" name="redirect_to" value="<?php
				echo esc_attr(( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" />
			<?php if (MKB_Options::option('restrict_show_register_link')): ?>
				<?php if (MKB_Options::option('restrict_show_or')):?>
					<div class="mkb-login-or"><?php
					echo esc_html(MKB_Options::option('restrict_or_text')); ?></div>
				<?php endif; ?>
					<div class="mkb-register-link">
						<a href="<?php echo esc_url(wp_registration_url()); ?>">
							<?php echo esc_html(MKB_Options::option('restrict_register_text')); ?>
						</a>
					</div>
			<?php endif; ?>
			</p>
		</form>
		<?php

		return ob_get_clean();
	}

	/**
	 * Before form HTML
	 * @return string
	 */
	private function maybe_get_before_html() {

		if (!MKB_Options::option('restrict_message_before_html')) {
			return '';
		}

		return '<div class="mkb-restricted-extra-html mkb-restricted-extra-html--before">' . MKB_Options::option('restrict_message_before_html') . '</div>';
	}

	/**
	 * After form HTML
	 */
	private function maybe_get_after_html() {

		if (!MKB_Options::option('restrict_message_after_html')) {
			return '';
		}

		return '<div class="mkb-restricted-extra-html mkb-restricted-extra-html--after">' . MKB_Options::option('restrict_message_after_html') . '</div>';
	}
}