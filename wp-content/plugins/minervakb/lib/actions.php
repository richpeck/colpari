<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_ContentHooks {

	private $info;

	private $restrict;

	/**
	 * Constructor
	 * @param $deps
	 */
	public function __construct($deps) {

		$this->setup_dependencies( $deps );

		// single template actions
		if (MKB_Options::option('show_last_modified_date')) {
			add_action('minerva_single_title_after', array($this, 'single_modified'), 50);
		}

		if (MKB_Options::option('add_article_versions') && MKB_Options::option('show_article_versions')) {
			add_action('minerva_single_title_after', array($this, 'single_versions'), 50);
		}

		add_action('minerva_single_title_after', array($this, 'single_search'), 50);
		add_action('minerva_single_title_after', array($this, 'single_breadcrumbs'), 100);
		add_action('minerva_single_content_after', array($this, 'single_related_articles'), 500);

		// single entry actions

		add_action('minerva_single_entry_header_meta', array($this, 'single_reading_estimate'), 50);
		add_action('minerva_single_entry_header_meta', array($this, 'single_table_of_contents'), 100);

		if (MKB_Options::option('show_article_author')) {
			add_action('minerva_single_entry_footer_meta', array($this, 'single_author'), 100);
		}

		add_action('minerva_single_entry_footer_meta', array($this, 'single_extra_attachments'), 90);
		add_action('minerva_single_entry_footer_meta', array($this, 'single_tags'), 100);
		add_action('minerva_single_entry_footer_meta', array($this, 'single_extra_rating'), 200);
		add_action('minerva_single_entry_footer_meta', array($this, 'single_extra_pageviews'), 300);
		add_action('minerva_single_entry_footer_meta', array($this, 'single_extra_html'), 400);

		// topic template actions
		add_action('minerva_category_title_after', array($this, 'category_search'), 50);
		add_action('minerva_category_title_after', array($this, 'category_breadcrumbs'), 100);
		add_action('minerva_category_title_after', array($this, 'category_children'), 150);
		add_action('minerva_category_loop_after', array($this, 'category_pagination'), 100);

		// tag template actions
		add_action('minerva_tag_loop_after', array($this, 'tag_pagination'), 100);

		// search template actions
		add_action('minerva_search_title_after', array($this, 'search_results_search'), 50);
		add_action('minerva_search_title_after', array($this, 'search_results_breadcrumbs'), 100);
		add_action('minerva_search_loop_after', array($this, 'search_results_pagination'), 100);

		// no results
		add_action('minerva_no_content_inside', array($this, 'no_results_search'), 100);
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
	 * Article search
	 */
	public function single_search() {

		if (!$this->restrict->check_access() && !MKB_Options::option('restrict_show_article_search')) {
			return false;
		}

		if (MKB_Options::option('add_article_search')) {
			MKB_TemplateHelper::render_search(array(
				"search_title" => MKB_Options::option( 'article_search_title' ),
				"search_title_color" => MKB_Options::option( 'article_search_title_color' ),
				"search_title_size" => MKB_Options::option( 'article_search_title_size' ),
				"search_theme" => MKB_Options::option( 'article_search_theme' ),
				"search_border_color" => MKB_Options::option( 'article_search_border_color' ),
				"search_min_width" => MKB_Options::option( 'article_search_min_width' ),
				"search_container_padding_top" => MKB_Options::option( 'article_search_container_padding_top' ),
				"search_container_padding_bottom" => MKB_Options::option( 'article_search_container_padding_bottom' ),
				"search_placeholder" => MKB_Options::option( 'article_search_placeholder' ),
				"search_tip_color" => MKB_Options::option( 'article_search_tip_color' ),
				"add_pattern_overlay" => MKB_Options::option( 'article_add_pattern_overlay' ),
				"search_container_image_pattern" => MKB_Options::option( 'article_search_container_image_pattern' ),
				"add_gradient_overlay" => MKB_Options::option( 'article_add_gradient_overlay' ),
				"search_container_gradient_from" => MKB_Options::option( 'article_search_container_gradient_from' ),
				"search_container_gradient_to" => MKB_Options::option( 'article_search_container_gradient_to' ),
				"search_container_gradient_opacity" => MKB_Options::option( 'article_search_container_gradient_opacity' ),
				"show_search_tip" => MKB_Options::option( 'article_show_search_tip' ),
				"disable_autofocus" => MKB_Options::option( 'article_disable_autofocus' ),
				"search_tip" => MKB_Options::option( 'article_search_tip' ),
				"search_container_bg" => MKB_Options::option( 'article_search_container_bg' ),
				"search_container_image_bg" => MKB_Options::option( 'article_search_container_image_bg' ),
				"show_topic_in_results" => MKB_Options::option( 'article_show_topic_in_results' )
			));
		}
	}

	/**
	 * Article breadcrumbs
	 */
	public function single_breadcrumbs() {

		if (!$this->restrict->check_access() && !MKB_Options::option('restrict_show_article_breadcrumbs')) {
			return false;
		}

		if (MKB_Options::option('show_breadcrumbs_single')) {
			$terms = wp_get_post_terms( get_the_ID(), MKB_Options::option( 'article_cpt_category' ));
			$term = null;

			if ($terms && !empty($terms) && isset($terms[0])) {
				$term = $terms[0];
			}

			MKB_TemplateHelper::breadcrumbs( $term, MKB_Options::option( 'article_cpt_category' ), 'single' );
		}
	}

	/**
	 * Article reading estimate
	 */
	public function single_reading_estimate() {
		$words_per_minute = 275;
		$content = get_post_field( 'post_content', get_the_ID() );
		$word_count = self::count_unicode_words( strip_tags( $content ) );

		$est_reading_time_raw = round( $word_count / $words_per_minute );

		if ( $est_reading_time_raw < 1 ) {
			$est_reading_time = MKB_Options::option( 'estimated_time_less_than_min' );
		} else {
			$est_reading_time = $est_reading_time_raw . ' ' . MKB_Options::option( 'estimated_time_min' );
		}

		if ( MKB_Options::option( 'show_reading_estimate' ) ): ?>
			<div
				class="mkb-article-header__estimate">
				<i class="mkb-estimated-icon fa <?php echo esc_attr(MKB_Options::option( 'estimated_time_icon' )); ?>"></i>
				<span><?php echo esc_html( MKB_Options::option( 'estimated_time_text' ) ); ?></span> <span><?php echo esc_html( $est_reading_time ); ?></span>
			</div>
		<?php endif;
	}

	private function count_unicode_words( $unicode_string ){
		$unicode_string = preg_replace('/[[:punct:][:digit:]]/', '', $unicode_string);
		$unicode_string = preg_replace('/[[:space:]]/', ' ', $unicode_string);
		$words_array = preg_split( "/[\n\r\t ]+/", $unicode_string, 0, PREG_SPLIT_NO_EMPTY );

		return count($words_array);
	}

	/**
	 * Article table of contents
	 */
	public function single_table_of_contents() {

		if (MKB_Options::option('toc_in_content_disable') &&
	        (!MKB_Options::option('toc_sidebar_desktop_only') || $this->info->is_desktop())) {
			return;
		}

		MKB_TemplateHelper::table_of_contents();
	}

	/**
	 * Article versions
	 */
	public function single_versions() {
		?>
		<div class="mkb-article-versions">
			<?php esc_html_e(MKB_Options::option('article_versions_text')); ?><?php
			if (MKB_Options::option( 'enable_versions_links' ) && MKB_Options::option( 'enable_versions_archive' )):
				echo get_the_term_list(
					get_the_ID(),
					'mkb_version',
					' ',
					' '
				);
			else:
				$versions = wp_get_object_terms(get_the_ID(), 'mkb_version');

				if (sizeof($versions)):
					foreach($versions as $version):
						?><span class="mkb-article-version"><?php esc_html_e($version->name); ?></span><?php
					endforeach;
				endif;
			endif;
		?>
		</div><?php
	}

	/**
	 * Article modified date
	 */
	public function single_modified() {
		?>
		<div class="mkb-article-modified-date">
			<span class="mkb-meta-label">
				<?php esc_html_e(MKB_Options::option('last_modified_date_text')); ?>
			</span><?php

			the_modified_date();

			?>
		</div><?php
	}

	/**
	 * Article author
	 */
	public function single_author () {
		?>
		<div class="mkb-article-author">
			<?php esc_html_e(MKB_Options::option('article_author_text')); ?> <?php the_author(); ?>
		</div>
	<?php
	}

	/**
	 * Attachments here
	 */
	public function single_extra_attachments() {

		$files = MinervaKB_ArticleEdit::get_attachments_data();

		if (!sizeof($files)) {
			return;
		}

		$heading = MKB_Options::option('article_attach_label');
		$is_show_filename = MKB_Options::option('attach_archive_file_label') === 'filename';
		$is_show_icon = !MKB_Options::option('attach_icons_off');
		$is_show_size = MKB_Options::option('show_attach_size');

		?>
		<div class="mkb-attachments js-mkb-attachments">
			<?php if ($heading): ?>
				<h3><?php esc_html_e($heading); ?></h3>
			<?php endif; ?>
			<?php foreach($files as $file):

				$label = $file[$is_show_filename? 'filename' : 'title'];

				?>
				<div class="mkb-attachment-item">
					<a class="js-mkb-attachment-link"
					   data-id="<?php esc_attr_e($file['id']); ?>"
					   href="<?php esc_attr_e($file['url']); ?>"
					   target="_blank"
					   title="<?php esc_attr_e(__( 'Download', 'minerva-kb' )); ?> <?php esc_attr_e($label); ?>">
						<?php if ($is_show_icon): ?>
							<i class="mkb-attachment-icon fa <?php esc_attr_e($file['icon']); ?>" style="color:<?php esc_attr_e($file['color']); ?>"></i>
						<?php endif; ?>
						<span class="mkb-attachment-label"><?php esc_html_e($label); ?>
							<?php if ($is_show_size): ?>
								<span class="mkb-attachment-size">(<?php esc_html_e($file['filesizeHumanReadable']); ?>)</span>
							<?php endif; ?>
						</span>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
	<?php
	}

	/**
	 * Article tags
	 */
	public function single_tags() {
		if ( MKB_Options::option( 'show_article_tags' ) ):
			?><div class="mkb-article-extra__tags"><?php
			if (MKB_Options::option( 'show_article_tags_icon' )):
				?><i class="fa <?php echo esc_attr(MKB_Options::option( 'article_tags_icon' )); ?>"></i><?php
			endif;
				if (!MKB_Options::option( 'tags_disable' )):
					echo get_the_term_list(
						get_the_ID(),
						MKB_Options::option( 'article_cpt_tag' ),
						MKB_Options::option( 'article_tags_label' ) . ' ',
						' '
					);
				else:
					$tags = wp_get_object_terms(get_the_ID(), MKB_Options::option( 'article_cpt_tag' ));

					if (sizeof($tags)):
						foreach($tags as $tag):
							?><span class="mkb-tag-nolink"><?php echo esc_html($tag->name); ?></span><?php
						endforeach;
					endif;
				endif;
			?></div><?php
		endif;
	}

	/**
	 * Article rating
	 */
	public function single_extra_rating() {
		$id = get_the_ID();
		$likes = (int) get_post_meta( $id, '_mkb_likes', true );
		$dislikes = (int) get_post_meta( $id, '_mkb_dislikes', true );
		$total = $likes + $dislikes;

		?>
		<div class="mkb-article-extra__actions">
			<?php if ( MKB_Options::option( 'show_likes_button' ) || MKB_Options::option( 'show_dislikes_button' ) ): ?>
				<div class="mkb-article-extra__rating fn-article-rating">
					<div class="mkb-article-extra__rating-likes-block fn-rating-likes-block">
						<div
							class="mkb-article-extra__rating-title"><?php echo esc_html( MKB_Options::option( 'rating_block_label' ) ); ?></div>
						<?php if ( MKB_Options::option( 'show_likes_button' ) ): ?>
							<a href="#" class="mkb-article-extra__like"
							   data-article-id="<?php echo esc_attr( $id ); ?>"
							   data-article-title="<?php echo esc_attr( get_the_title() ); ?>"
							   title="<?php echo esc_attr( MKB_Options::option( 'like_label' ) ); ?>">
								<?php if ( MKB_Options::option( 'show_likes_icon' ) ): ?>
									<i class="mkb-like-icon fa <?php echo esc_attr( MKB_Options::option( 'like_icon' ) ); ?>"></i>
								<?php endif; ?>
								<?php echo esc_html( MKB_Options::option( 'like_label' ) ); ?>
								<?php if ( MKB_Options::option( 'show_likes_count' ) ): ?>
									<span class="mkb-article-extra__stats-likes">
									<?php echo esc_html( $likes ? $likes : 0 ); ?>
								</span>
								<?php endif; ?>
							</a>
						<?php endif; ?>
						<?php if ( MKB_Options::option( 'show_dislikes_button' ) ): ?>
							<a href="#" class="mkb-article-extra__dislike"
							   data-article-id="<?php echo esc_attr( $id ); ?>"
							   data-article-title="<?php echo esc_attr( get_the_title() ); ?>"
							   title="<?php echo esc_attr( MKB_Options::option( 'dislike_label' ) ); ?>">
								<?php if ( MKB_Options::option( 'show_dislikes_icon' ) ): ?>
									<i class="mkb-dislike-icon fa <?php echo esc_attr( MKB_Options::option( 'dislike_icon' ) ); ?>"></i>
								<?php endif; ?>
								<?php echo esc_html( MKB_Options::option( 'dislike_label' ) ); ?>
								<?php if ( MKB_Options::option( 'show_dislikes_count' ) ): ?>
									<span class="mkb-article-extra__stats-dislikes">
									<?php echo esc_html( $dislikes ? $dislikes : 0 ); ?>
								</span>
								<?php endif; ?>
							</a>
						<?php endif; ?>
						<?php if ( MKB_Options::option( 'show_rating_total' ) ): ?>
							<span class="mkb-article-extra__rating-total">
						<?php printf( esc_html( MKB_Options::option( 'rating_total_text' ) ), $likes, $total ); ?>
					</span>
						<?php endif; ?>
					</div>
					<div class="fn-article-feedback-container">
						<?php if ( MKB_Options::option( 'enable_feedback' ) && MKB_Options::option( 'feedback_mode' ) == 'always' ): ?>
							<div class="mkb-article-extra__feedback-form mkb-article-extra__feedback-form--no-content fn-feedback-form">
								<div class="mkb-article-extra__feedback-form-title">
									<?php echo esc_html( MKB_Options::option( 'feedback_label' ) ); ?>
								</div>
								<div class="mkb-article-extra__feedback-form-message">
									<textarea class="mkb-article-extra__feedback-form-message-area" rows="5"></textarea>
									<?php echo wp_kses_post(
										MKB_Options::option( 'feedback_info_text' ) ?
											'<div class="mkb-article-extra__feedback-info">' . MKB_Options::option( 'feedback_info_text' ) . '</div>' :
											'' );
									?>
								</div>
								<div class="mkb-article-extra__feedback-form-submit">
									<a href="#"><?php echo esc_html( MKB_Options::option( 'feedback_submit_label' ) ); ?></a>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	<?php
	}

	/**
	 * Article pageviews display
	 */
	public function single_extra_pageviews() {
		$id = get_the_ID();
		$views = get_post_meta( $id, '_mkb_views', true );
		?>
		<div class="mkb-article-extra__stats">
			<?php if ( MKB_Options::option( 'show_pageviews' ) ): ?>
				<div class="mkb-article-extra__stats-pageviews">
					<span><?php echo esc_html(MKB_Options::option( 'pageviews_label' )); ?></span> <span><?php echo esc_html( $views ? $views : 0 ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	<?php
	}

	/**
	 * Article bottom HTML
	 */
	public function single_extra_html() {
		if (MKB_Options::option( 'add_article_html' ) && MKB_Options::option( 'article_html' )): ?>
			<div class="mkb-article-extra__custom-html">
				<?php echo wp_kses_post(MKB_Options::option( 'article_html' )); ?>
			</div>
		<?php endif;
	}

	/**
	 * Article related
	 */
	public function single_related_articles() {

		if (!$this->restrict->check_access() && !MKB_Options::option('restrict_show_article_related')) {
			return false;
		}

		$related = get_post_meta(get_the_ID(), '_mkb_related_articles', true);

		if (MKB_Options::option( 'show_related_articles' ) && $related && is_array($related) && !empty($related)): ?>
			<div class="mkb-related-articles">
				<h3><?php echo esc_html(MKB_Options::option( 'related_articles_label' )); ?></h3>
				<ul class="mkb-related-articles__list">
					<?php foreach($related as $article_id): ?>
						<li class="mkb-related-article">
							<a href="<?php echo esc_url(get_permalink($article_id)); ?>"
							   title="<?php echo esc_attr(get_the_title($article_id)); ?>">
								<?php echo esc_html(get_the_title($article_id)); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php
		endif;
	}

	/**
	 * Topic search
	 */
	public function category_search() {
		$term = get_term_by( 'id', get_queried_object_id(), MKB_Options::option( 'article_cpt_category' ) );

		if (MKB_Options::option('add_topic_search') && !MinervaKB::topic_option($term, 'topic_no_search_switch')) {
			MKB_TemplateHelper::render_search(array(
				"search_title" => MKB_Options::option( 'topic_search_title' ),
				"search_title_color" => MKB_Options::option( 'topic_search_title_color' ),
				"search_title_size" => MKB_Options::option( 'topic_search_title_size' ),
				"search_theme" => MKB_Options::option( 'topic_search_theme' ),
				"search_border_color" => MKB_Options::option( 'topic_search_border_color' ),
				"search_min_width" => MKB_Options::option( 'topic_search_min_width' ),
				"search_container_padding_top" => MKB_Options::option( 'topic_search_container_padding_top' ),
				"search_container_padding_bottom" => MKB_Options::option( 'topic_search_container_padding_bottom' ),
				"search_placeholder" => MKB_Options::option( 'topic_search_placeholder' ),
				"search_tip_color" => MKB_Options::option( 'topic_search_tip_color' ),
				"add_pattern_overlay" => MKB_Options::option( 'topic_add_pattern_overlay' ),
				"search_container_image_pattern" => MKB_Options::option( 'topic_search_container_image_pattern' ),
				"add_gradient_overlay" => MKB_Options::option( 'topic_add_gradient_overlay' ),
				"search_container_gradient_from" => MKB_Options::option( 'topic_search_container_gradient_from' ),
				"search_container_gradient_to" => MKB_Options::option( 'topic_search_container_gradient_to' ),
				"search_container_gradient_opacity" => MKB_Options::option( 'topic_search_container_gradient_opacity' ),
				"show_search_tip" => MKB_Options::option( 'topic_show_search_tip' ),
				"disable_autofocus" => MKB_Options::option( 'topic_disable_autofocus' ),
				"search_tip" => MKB_Options::option( 'topic_search_tip' ),
				"search_container_bg" => MKB_Options::option( 'topic_search_container_bg' ),
				"search_container_image_bg" => MKB_Options::option( 'topic_search_container_image_bg' ),
				"show_topic_in_results" => MKB_Options::option( 'topic_show_topic_in_results' )
			));
		}
	}

	/**
	 * Topic breadcrumbs
	 */
	public function category_breadcrumbs() {
		$term = get_term_by( 'id', get_queried_object_id(), MKB_Options::option( 'article_cpt_category' ) );

		if (MKB_Options::option('show_breadcrumbs_category') && !MinervaKB::topic_option($term, 'topic_no_breadcrumbs_switch')) {
			MKB_TemplateHelper::breadcrumbs( $term, MKB_Options::option( 'article_cpt_category' ) );
		}
	}

	/**
	 * Topic children
	 */
	public function category_children() {
		$term = get_term_by( 'id', get_queried_object_id(), MKB_Options::option( 'article_cpt_category' ) );

		if (MinervaKB::topic_option($term, 'topic_page_switch') && MinervaKB::topic_option($term, 'topic_page')) {
			return;
		}

		global $minerva_kb;

		$children = $terms = get_terms( MKB_Options::option( 'article_cpt_category' ), array(
			'taxonomy'   => MKB_Options::option( 'article_cpt_category' ),
			'hide_empty' => true,
			'parent'     => $term->term_id
		) );

		$children_columns = MKB_TemplateHelper::get_topic_children_columns();
		$view_mode = MKB_Options::option('topic_children_view');
		$row_open = false;

		if ( ! empty( $children ) ):
			?>
			<div class="mkb-topic__children mkb-columns mkb-columns-<?php echo esc_attr($children_columns); ?>">
				<?php

				$i = 0;

				foreach ( $children as $topic ):

                    // skip all restricted topics
                    if (MKB_Options::option('restrict_on') &&
                        MKB_Options::option('restrict_remove_from_archives') &&
                        isset($topic->term_id) && !$minerva_kb->restrict->is_topic_allowed($topic)) {

                        continue;
                    }

					if ($i % $children_columns === 0):
						echo '<div class="mkb-row">';
						$row_open = true;
					endif;

					if ($view_mode === 'list'):
						MKB_TemplateHelper::render_as_list($topic);
					else:
						MKB_TemplateHelper::render_as_box($topic);
					endif;

					if ( ($i + 1) % $children_columns === 0 ):
						echo '</div >';
						$row_open = false;
					endif;

					++$i;
				endforeach;

				if ( $row_open ):
					echo '</div >';
					$row_open = false;
				endif;
				?>
			</div>
		<?php
		endif;
	}

	/**
	 * Search results search
	 */
	public function search_results_search() {
		if (MKB_Options::option('show_search_page_search')) {
			MKB_TemplateHelper::render_search(array(
				"search_title" => MKB_Options::option( 'topic_search_title' ),
				"search_title_color" => MKB_Options::option( 'topic_search_title_color' ),
				"search_title_size" => MKB_Options::option( 'topic_search_title_size' ),
				"search_theme" => MKB_Options::option( 'topic_search_theme' ),
				"search_border_color" => MKB_Options::option( 'topic_search_border_color' ),
				"search_min_width" => MKB_Options::option( 'topic_search_min_width' ),
				"search_container_padding_top" => MKB_Options::option( 'topic_search_container_padding_top' ),
				"search_container_padding_bottom" => MKB_Options::option( 'topic_search_container_padding_bottom' ),
				"search_placeholder" => MKB_Options::option( 'topic_search_placeholder' ),
				"search_tip_color" => MKB_Options::option( 'topic_search_tip_color' ),
				"add_pattern_overlay" => MKB_Options::option( 'topic_add_pattern_overlay' ),
				"search_container_image_pattern" => MKB_Options::option( 'topic_search_container_image_pattern' ),
				"add_gradient_overlay" => MKB_Options::option( 'topic_add_gradient_overlay' ),
				"search_container_gradient_from" => MKB_Options::option( 'topic_search_container_gradient_from' ),
				"search_container_gradient_to" => MKB_Options::option( 'topic_search_container_gradient_to' ),
				"search_container_gradient_opacity" => MKB_Options::option( 'topic_search_container_gradient_opacity' ),
				"show_search_tip" => MKB_Options::option( 'topic_show_search_tip' ),
				"disable_autofocus" => MKB_Options::option( 'topic_disable_autofocus' ),
				"search_tip" => MKB_Options::option( 'topic_search_tip' ),
				"search_container_bg" => MKB_Options::option( 'topic_search_container_bg' ),
				"search_container_image_bg" => MKB_Options::option( 'topic_search_container_image_bg' ),
				"show_topic_in_results" => MKB_Options::option( 'topic_show_topic_in_results' )
			));
		}
	}

	/**
	 * Search breadcrumbs
	 */
	public function search_results_breadcrumbs() {
		if (MKB_Options::option('show_breadcrumbs_search')) {
			MKB_TemplateHelper::search_breadcrumbs( $_REQUEST['s'] );
		}
	}

	/**
	 * Pagination for category page
	 */
	public function category_pagination () {
		$term = get_term_by( 'id', get_queried_object_id(), MKB_Options::option( 'article_cpt_category' ) );

		if (MinervaKB::topic_option($term, 'topic_page_switch') && MinervaKB::topic_option($term, 'topic_page')) {
			return;
		}

		MKB_TemplateHelper::pagination();
	}

	/**
	 * Pagination for tag page
	 */
	public function tag_pagination () {
		MKB_TemplateHelper::pagination();
	}

	/**
	 * Pagination for search results page
	 */
	public function search_results_pagination () {
		MKB_TemplateHelper::pagination();
	}

	/**
	 * Pagination for search results page
	 */
	public function no_results_search () {
		MKB_TemplateHelper::render_search(array(
			"search_title" => MKB_Options::option( 'topic_search_title' ),
			"search_title_color" => MKB_Options::option( 'topic_search_title_color' ),
			"search_title_size" => MKB_Options::option( 'topic_search_title_size' ),
			"search_theme" => MKB_Options::option( 'topic_search_theme' ),
			"search_border_color" => MKB_Options::option( 'topic_search_border_color' ),
			"search_min_width" => MKB_Options::option( 'topic_search_min_width' ),
			"search_container_padding_top" => MKB_Options::option( 'topic_search_container_padding_top' ),
			"search_container_padding_bottom" => MKB_Options::option( 'topic_search_container_padding_bottom' ),
			"search_placeholder" => MKB_Options::option( 'topic_search_placeholder' ),
			"search_tip_color" => MKB_Options::option( 'topic_search_tip_color' ),
			"add_pattern_overlay" => MKB_Options::option( 'topic_add_pattern_overlay' ),
			"search_container_image_pattern" => MKB_Options::option( 'topic_search_container_image_pattern' ),
			"add_gradient_overlay" => MKB_Options::option( 'topic_add_gradient_overlay' ),
			"search_container_gradient_from" => MKB_Options::option( 'topic_search_container_gradient_from' ),
			"search_container_gradient_to" => MKB_Options::option( 'topic_search_container_gradient_to' ),
			"search_container_gradient_opacity" => MKB_Options::option( 'topic_search_container_gradient_opacity' ),
			"show_search_tip" => MKB_Options::option( 'topic_show_search_tip' ),
			"disable_autofocus" => MKB_Options::option( 'topic_disable_autofocus' ),
			"search_tip" => MKB_Options::option( 'topic_search_tip' ),
			"search_container_bg" => MKB_Options::option( 'topic_search_container_bg' ),
			"search_container_image_bg" => MKB_Options::option( 'topic_search_container_image_bg' ),
			"show_topic_in_results" => MKB_Options::option( 'topic_show_topic_in_results' )
		));
	}
}

