<?php

/**
 * Dashboard page controller
 * Class MinervaKB_DashboardPage
 */

class MinervaKB_DashboardPage {

	private $analytics;

	private $SCREEN_BASE = null;

	public function __construct($deps) {

		$this->setup_dependencies( $deps );

		$this->SCREEN_BASE = MKB_Options::option('article_cpt') . '_page_minerva-kb-submenu-dashboard';

		add_action( 'admin_menu', array( $this, 'add_submenu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );
	}

	/**
	 * Sets up dependencies
	 * @param $deps
	 */
	private function setup_dependencies($deps) {
		if (isset($deps['analytics'])) {
			$this->analytics = $deps['analytics'];
		}
	}

	/**
	 * Adds dashboard submenu page
	 */
	public function add_submenu() {
		add_submenu_page(
			'edit.php?post_type=' . MKB_Options::option('article_cpt'),
			__( 'Dashboard', 'minerva-kb' ),
			__( 'Dashboard', 'minerva-kb' ),
			'manage_options',
			'minerva-kb-submenu-dashboard',
			array( $this, 'submenu_html' )
		);
	}

	/**
	 * Gets dashboard page html
	 */
	public function submenu_html() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'minerva-kb' ) );
		}

		?>
		<div class="mkb-admin-page-header">
			<span class="mkb-header-logo mkb-header-item" data-version="v<?php echo esc_attr(MINERVA_KB_VERSION); ?>">
				<img class="logo-img" src="<?php echo esc_attr( MINERVA_KB_IMG_URL . 'logo.png' ); ?>" title="logo"/>
			</span>
			<span class="mkb-header-title mkb-header-item"><?php _e( 'Dashboard', 'minerva-kb' ); ?></span>
			<?php MinervaKB_AutoUpdate::registered_label(); ?>
		</div>

		<div id="mkb-dashboard">
			<div class="mkb-dashboard__tabs">
				<ul class="mkb-dashboard__tabs-list">
					<li><a href="#dashboard_general"
					       class="fn-dashboard-tab-link mkb-dashboard__tabs-list-item mkb-dashboard__tabs-list-item--active"
					       title="<?php esc_attr_e('Overview', 'minerva-kb'); ?>">
							<?php _e( 'Overview', 'minerva-kb' ); ?>
						</a>
					</li>
					<li><a href="#dashboard_search" class="fn-dashboard-tab-link mkb-dashboard__tabs-list-item"
					       title="<?php esc_attr_e('Search', 'minerva-kb'); ?>">
							<?php _e( 'Search', 'minerva-kb' ); ?>
						</a>
					</li>
					<li><a href="#dashboard_feedback" class="fn-dashboard-tab-link mkb-dashboard__tabs-list-item"
					       title="<?php esc_attr_e('Feedback', 'minerva-kb'); ?>">
							<?php _e( 'Feedback', 'minerva-kb' ); ?>
						</a>
					</li>
					<li><a href="#dashboard_reset" class="fn-dashboard-tab-link mkb-dashboard__tabs-list-item"
					       title="<?php esc_attr_e('Reset', 'minerva-kb'); ?>">
							<?php _e( 'Reset', 'minerva-kb' ); ?>
						</a>
					</li>
				</ul>
			</div>
			<div class="mkb-dashboard__content">
				<div id="dashboard_general" class="mkb-dashboard-page mkb-dashboard-page--active">
					<?php self::render_counters(); ?>
					<?php self::render_graph(); ?>
					<?php self::render_lists(); ?>
				</div>
				<div id="dashboard_search" class="mkb-dashboard-page">
					<?php self::render_search(); ?>
				</div>
				<div id="dashboard_feedback" class="mkb-dashboard-page">
					<?php self::render_feedback(); ?>
				</div>
				<div id="dashboard_reset" class="mkb-dashboard-page">
					<?php self::render_reset(); ?>
				</div>
			</div>
		</div>
	<?php
	}

	/**
	 * Dashboard overview counters
	 */
	protected function render_counters() {
		?>
		<div class="mkb-dashboard__counters">
			<!-- Articles -->
			<div class="mkb-dashboard-widget">
				<div class="mkb-dashboard-widget__inner">
					<div class="mkb-dashboard-widget__header">
						<div class="mkb-dashboard-widget__title">
							<h3><?php _e( 'Articles', 'minerva-kb' ); ?></h3>
						</div>
					</div>
					<div class="mkb-dashboard-widget__content">
					<span id="mkb_total_articles_count"
					      class="mkb-value-epic fn-mkb-counter"
					      data-target="<?php echo esc_attr( $this->analytics->get_articles_count() ); ?>">0
					</span>
					</div>
				</div>
			</div>

			<!-- Topics -->
			<div class="mkb-dashboard-widget">
				<div class="mkb-dashboard-widget__inner">
					<div class="mkb-dashboard-widget__header">
						<div class="mkb-dashboard-widget__title">
							<h3><?php _e( 'Topics', 'minerva-kb' ); ?></h3>
						</div>
					</div>
					<div class="mkb-dashboard-widget__content">
						<span id="mkb_total_topics_count" class="mkb-value-epic fn-mkb-counter"
						      data-target="<?php echo esc_attr( $this->analytics->get_topics_count() ); ?>">
							0
						</span>
					</div>
				</div>
			</div>

			<!-- Tags -->
			<div class="mkb-dashboard-widget">
				<div class="mkb-dashboard-widget__inner">
					<div class="mkb-dashboard-widget__header">
						<div class="mkb-dashboard-widget__title">
							<h3><?php _e( 'Tags', 'minerva-kb' ); ?></h3>
						</div>
					</div>
					<div class="mkb-dashboard-widget__content">
						<span id="mkb_total_tags_count"
						      class="mkb-value-epic fn-mkb-counter"
						      data-target="<?php echo esc_attr( $this->analytics->get_tags_count() ); ?>">0
						</span>
					</div>
				</div>
			</div>

			<!-- Views -->
			<div class="mkb-dashboard-widget">
				<div class="mkb-dashboard-widget__inner">
					<div class="mkb-dashboard-widget__header">
						<div class="mkb-dashboard-widget__title">
							<h3><?php _e( 'Views', 'minerva-kb' ); ?></h3>
						</div>
					</div>
					<div class="mkb-dashboard-widget__content">
						<span id="mkb_total_views_count"
						      class="mkb-value-epic fn-mkb-counter"
						      data-target="<?php echo esc_attr( $this->analytics->get_views_count() ); ?>">0
						</span>
					</div>
				</div>
			</div>

			<!-- Likes -->
			<div class="mkb-dashboard-widget">
				<div class="mkb-dashboard-widget__inner">
					<div class="mkb-dashboard-widget__header">
						<div class="mkb-dashboard-widget__title">
							<h3><?php _e( 'Likes', 'minerva-kb' ); ?></h3>
						</div>
					</div>
					<div class="mkb-dashboard-widget__content">
						<span style="color: #4BB651;"
						      id="mkb_total_likes_count"
						      class="mkb-value-epic fn-mkb-counter"
						      data-target="<?php echo esc_attr( $this->analytics->get_likes_count() ); ?>">0
						</span>
					</div>
				</div>
			</div>

			<!-- Dislikes -->
			<div class="mkb-dashboard-widget">
				<div class="mkb-dashboard-widget__inner">
					<div class="mkb-dashboard-widget__header">
						<div class="mkb-dashboard-widget__title">
							<h3><?php _e( 'Dislikes', 'minerva-kb' ); ?></h3>
						</div>
					</div>
					<div class="mkb-dashboard-widget__content">
						<span style="color: #C85C5E;"
						      id="mkb_total_dislikes_count"
						      class="mkb-value-epic fn-mkb-counter"
						      data-target="<?php echo esc_attr( $this->analytics->get_dislikes_count() ); ?>">0
						</span>
					</div>
				</div>
			</div>
		</div>
	<?php
	}

	/**
	 * Dashboard overview graph
	 */
	private function render_graph() {
		?>
		<!-- Graph -->
		<div class="mkb-dashboard-widget mkb-dashboard-widget--fullwidth">
			<div class="mkb-dashboard-widget__inner">
				<div class="mkb-dashboard-widget__header">
					<div class="mkb-dashboard-widget__title">
						<h3><?php _e( 'Analytics', 'minerva-kb' ); ?></h3>
					</div>
					<div>
						<label for="mkb-analytics-period"><?php _e( 'Select period', 'minerva-kb' ); ?> </label>
						<select id="mkb-analytics-period">
							<option value="week"
							        selected="selected"><?php _e( 'Last 7 days', 'minerva-kb' ); ?></option>
							<option value="month"><?php _e( 'Last 30 days', 'minerva-kb' ); ?></option>
						</select>
						<br/>
						<br/>
					</div>
				</div>
				<div class="mkb-dashboard-widget__content">
					<div class="mkb-chart-holder">
						<canvas id="mkb-analytics-canvas"></canvas>
					</div>
				</div>
			</div>
		</div>
	<?php
	}

	/**
	 * Dashboard recent entries lists
	 */
	private function render_lists() {
		?>
		<div class="mkb-dashboard__lists">
			<!-- Recent -->
			<div class="mkb-dashboard-widget">
				<div class="mkb-dashboard-widget__inner">
					<div class="mkb-dashboard-widget__header">
						<div class="mkb-dashboard-widget__title">
							<h3><?php _e( 'Recently added articles', 'minerva-kb' ); ?></h3>
						</div>
					</div>
					<div class="mkb-dashboard-widget__content">
						<ul>
							<?php
							$recent = $this->analytics->get_recent_articles();

							if ( ! empty( $recent ) ):
								foreach ( $recent as $article ):
									?>
									<li>
										<a target="_blank" class="mkb-unstyled-link"
										   href="<?php echo esc_attr( $article["link"] ); ?>">
											<?php echo esc_html( $article["title"] ); ?>
										</a>
										<span class="mkb-value"><?php echo esc_html( $article["date"] ); ?></span>
									</li>
								<?php
								endforeach;
							else:
								?>
								<p class="mkb-no-entries"><?php _e( 'You have no articles yet', 'minerva-kb' ); ?></p>
							<?php
							endif;
							?>
						</ul>
					</div>
				</div>
			</div>

			<!-- Top views -->
			<div class="mkb-dashboard-widget">
				<div class="mkb-dashboard-widget__inner">
					<div class="mkb-dashboard-widget__header">
						<div class="mkb-dashboard-widget__title">
							<h3><?php _e( 'Most viewed articles', 'minerva-kb' ); ?></h3>
						</div>
					</div>
					<div class="mkb-dashboard-widget__content">
						<ul>
							<?php
							$top_viewed = $this->analytics->get_most_viewed_articles();

							if ( ! empty( $top_viewed ) ):
								foreach ( $top_viewed as $article ):
									?>
									<li>
										<a target="_blank" class="mkb-unstyled-link"
										   href="<?php echo esc_attr( $article["link"] ); ?>">
											<?php echo esc_html( $article["title"] ); ?>
										</a>
										<span class="mkb-value"><?php echo esc_html( $article["views"] ); ?></span>
									</li>
								<?php
								endforeach;
							else:
								?>
								<p class="mkb-no-entries"><?php _e( 'You have no views yet', 'minerva-kb' ); ?></p>
							<?php
							endif;
							?>
						</ul>
					</div>
				</div>
			</div>

			<!-- Top likes -->
			<div class="mkb-dashboard-widget">
				<div class="mkb-dashboard-widget__inner">
					<div class="mkb-dashboard-widget__header">
						<div class="mkb-dashboard-widget__title">
							<h3><?php _e( 'Most liked articles', 'minerva-kb' ); ?></h3>
						</div>
					</div>
					<div class="mkb-dashboard-widget__content">
						<ul>
							<?php
							$top_liked = $this->analytics->get_most_liked_articles();

							if ( ! empty( $top_liked ) ):
								foreach ( $top_liked as $article ):
									?>
									<li>
										<a target="_blank" class="mkb-unstyled-link"
										   href="<?php echo esc_attr( $article["link"] ); ?>">
											<?php echo esc_html( $article["title"] ); ?>
										</a>
										<span class="mkb-value"><?php echo esc_html( $article["likes"] ); ?></span>
									</li>
								<?php
								endforeach;
							else:
								?>
								<p class="mkb-no-entries"><?php _e( 'You have no likes yet', 'minerva-kb' ); ?></p>
							<?php
							endif;
							?>
						</ul>
					</div>
				</div>
			</div>

			<!-- Top dislikes -->
			<div class="mkb-dashboard-widget">
				<div class="mkb-dashboard-widget__inner">
					<div class="mkb-dashboard-widget__header">
						<div class="mkb-dashboard-widget__title">
							<h3><?php _e( 'Most disliked articles', 'minerva-kb' ); ?></h3>
						</div>
					</div>
					<div class="mkb-dashboard-widget__content">
						<ul>
							<?php
							$top_disliked = $this->analytics->get_most_disliked_articles();

							if ( ! empty( $top_disliked ) ):
								foreach ( $top_disliked as $article ):
									?>
									<li>
										<a target="_blank" class="mkb-unstyled-link"
										   href="<?php echo esc_attr( $article["link"] ); ?>">
											<?php echo esc_html( $article["title"] ); ?>
										</a>
										<span class="mkb-value"><?php echo esc_html( $article["dislikes"] ); ?></span>
									</li>
								<?php
								endforeach;
							else:
								?>
								<p class="mkb-no-entries"><?php _e( 'You have no dislikes yet', 'minerva-kb' ); ?></p>
							<?php
							endif;
							?>
						</ul>
					</div>
				</div>
			</div>
		</div>
	<?php
	}

	/**
	 * Renders submitted feedback
	 */
	private function render_feedback() {
		$feedback = $this->analytics->get_feedback();
		?>
		<div class="mkb-dashboard__feedback"><?php
			if ( sizeof( $feedback ) ): ?>
				<table class="mkb-dashboard__feedback-table" cellspacing="0" cellpadding="0">
					<tr>
						<th><i class="fa fa-close"></i></th>
						<th><?php _e( 'Message', 'minerva-kb' ); ?></th>
						<th><?php _e( 'For article', 'minerva-kb' ); ?></th>
						<th><?php _e( 'Date', 'minerva-kb' ); ?></th>
					</tr>
					<?php
					foreach ( $feedback as $item ):
						?>
						<tr class="mkb-dashboard__feedback-item-row">
							<td>
								<a href="#"
								   data-id="<?php echo esc_attr( $item["feedback_id"] ); ?>"
								   class="fn-remove-feedback mkb-dashboard__feedback-remove-item-link"
								   title="<?php esc_attr_e( 'Remove this entry?', 'minerva-kb' ); ?>">
									<i class="fa fa-close"></i>
								</a>
							</td>
							<td class="mkb-dashboard__feedback-item-content">
								<?php echo esc_html( $item["content"] ); ?>
							</td>
							<td>
								<a href="<?php echo esc_url(get_permalink($item["article_id"])); ?>">
									<?php echo get_the_title( $item["article_id"] ); ?>
								</a>
							</td>
							<td>
								<?php echo esc_html( $item["date"] ); ?>
							</td>
						</tr>
					<?php
					endforeach; ?>
				</table>
			<?php
			else:
				?><p class="mkb-no-entries">
				<?php _e( 'No feedback was submitted for your content yet', 'minerva-kb' ); ?>
				</p><?php
			endif;
			?></div>
	<?php
	}

	/**
	 * Renders reset
	 */
	private function render_reset() {
		$options = array(
			array(
				'id' => 'views',
				'type' => 'checkbox',
				'label' => __( 'Reset views?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'likes',
				'type' => 'checkbox',
				'label' => __( 'Reset likes?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'dislikes',
				'type' => 'checkbox',
				'label' => __( 'Reset dislikes?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'search',
				'type' => 'checkbox',
				'label' => __( 'Reset search?', 'minerva-kb' ),
				'default' => false
			),
		);
		$settings_helper = new MKB_SettingsBuilder(array("no_tabs" => true));

		?>
		<div class="mkb-dashboard__reset mkb-clearfix">
			<h2><?php _e( 'Select data to reset', 'minerva-kb' ); ?></h2>

			<div class="mkb-settings-content fn-mkb-dashboard-reset-form">
				<form action="" novalidate>
					<?php
					foreach ($options as $option):
						$settings_helper->render_option(
							$option["type"],
							$option["default"],
							$option
						);
					endforeach;
					?>
					<a href="#" class="mkb-action-button mkb-action-danger fn-mkb-reset-stats-btn"
					   title="<?php esc_attr_e('Reset data', 'minerva-kb'); ?>"><?php echo __('Reset data', 'minerva-kb'); ?></a>
				</form>
			</div>
		</div>
	<?php
	}

	/**
	 * Renders top keywords stats
	 */
	private function render_search() {
		$search = array();

		ob_start();
		try {
			$search = $this->analytics->get_keywords();
		} catch (Exception $e) { /* tables can be removed by uninstall */ }
		ob_clean();

		$items_per_page = 20;

		?>
		<div class="mkb-dashboard__search"><?php
			if ( sizeof( $search ) ): ?>
				<table class="mkb-dashboard__search-table" cellspacing="0" cellpadding="0">
					<tr>
						<th class="mkb-dashboard__search-keyword-head">
							<?php _e( 'Keyword', 'minerva-kb' ); ?>
							<a href="#"
							   class="fn-reorder-search-results mkb-unstyled-link reorder-search-results reorder-search-results--asc"
							   data-field="keyword"
							   data-order="DESC">
								<i class="fa fa-sort-asc"></i>
								<i class="fa fa-sort-desc"></i>
							</a>
						</th>
						<th class="mkb-dashboard__search-hit-count-head">
							<?php _e( 'Hits', 'minerva-kb' ); ?>
							<a href="#"
							   class="fn-reorder-search-results mkb-unstyled-link reorder-search-results reorder-search-results--active"
							   data-field="hits"
							   data-order="ASC">
								<i class="fa fa-sort-asc"></i>
								<i class="fa fa-sort-desc"></i>
							</a>
						</th>
						<th class="mkb-dashboard__search-results-head">
							<?php _e( 'Last search results', 'minerva-kb' ); ?>
							<a href="#"
							   class="fn-reorder-search-results mkb-unstyled-link reorder-search-results reorder-search-results--asc"
							   data-field="results"
							   data-order="DESC">
								<i class="fa fa-sort-asc"></i>
								<i class="fa fa-sort-desc"></i>
							</a>
						</th>
						<th class="mkb-dashboard__search-last-date-head">
							<?php _e( 'Last search date', 'minerva-kb' ); ?>
							<a href="#"
							   class="fn-reorder-search-results mkb-unstyled-link reorder-search-results reorder-search-results--asc"
							   data-field="date"
							   data-order="DESC">
								<i class="fa fa-sort-asc"></i>
								<i class="fa fa-sort-desc"></i>
							</a>
						</th>
					</tr>
					<?php
					foreach ( $search as $index => $item ):

						if ($index == $items_per_page) {
							break;
						}

						?>
						<tr class="mkb-dashboard__search-item-row">
							<td class="mkb-dashboard__search-keyword">
								<?php echo esc_html( $item->keyword ); ?>
							</td>
							<td class="mkb-dashboard__search-hit-count">
								<?php echo esc_html( $item->hit_count ); ?>
							</td>
							<td class="mkb-dashboard__search-results">
								<?php if ( $item->last_results != 0 ): ?>
									<span class="fn-search-results-container mkb-search-results-container">
										<a class="fn-show-search-results show-search-results mkb-unstyled-link"
										   href="#"
										   data-hit-id="<?php echo esc_attr( $item->hit_id ); ?>">
											<?php echo esc_html( $item->last_results ); ?>
											<i class="fa fa-eye"></i>
										</a>
										<span class="fn-search-results mkb-search-results"></span>
									</span>
								<?php else: ?>
									<?php echo esc_html( $item->last_results ); ?>
								<?php endif; ?>
							</td>
							<td class="mkb-dashboard__search-last-date">
								<?php echo esc_html( $item->last_search ); ?>
							</td>
						</tr>
					<?php
					endforeach; ?>
				</table>

				<?php

				$search_items_count = sizeof($search);

				if ($search_items_count > $items_per_page):
					$pages_num_full = floor($search_items_count / $items_per_page);
					$pages_num_partial = $search_items_count % $items_per_page > 0 ? 1 : 0;
					$pages_total = $pages_num_full + $pages_num_partial;
					?>
					<div class="mkb-search-pagination mkb-dashboard-pagination">
						<ul>
						<?php
						for ( $i=0; $i < $pages_total; $i++ ):
							?><li>
							<a class="fn-search-pagination-item mkb-pagination-item mkb-unstyled-link <?php if ($i == 0) {
								echo esc_attr('mkb-pagination-item--active');
							}?>"
							   data-page="<?php echo esc_attr($i); ?>"
							   href="#"><?php echo esc_html($i + 1); ?></a>
							</li><?php
						endfor;
						?>
						</ul>
					</div>
				<?php endif; ?>
			<?php
			else:
				?><p class="mkb-no-entries">
				<?php _e( 'No search keywords were tracked for your content yet', 'minerva-kb' ); ?>
				</p><?php
			endif;
			?>
		</div>
	<?php
	}

	/**
	 * Loads admin assets
	 */
	public function load_assets() {

		$screen = get_current_screen();

		if ( $screen->base !== $this->SCREEN_BASE ) {
			return;
		}

		// toastr
		wp_enqueue_style( 'minerva-kb/admin-toastr-css', MINERVA_KB_PLUGIN_URL . 'assets/css/vendor/toastr/toastr.min.css', false, '2.1.3' );

		wp_enqueue_script( 'minerva-kb/admin-toastr-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/toastr/toastr.min.js', array(), '2.1.3', true );

		wp_enqueue_script( 'minerva-kb/admin-dashboard-chart-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/chart.bundle.min.js', array(), null, true );
		wp_enqueue_script( 'minerva-kb/admin-dashboard-counter-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/count-up.min.js', array(), null, true );
		wp_enqueue_script( 'minerva-kb/admin-dashboard-js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-dashboard.js', array(
			'jquery',
			'minerva-kb/admin-ui-js',
			'minerva-kb/admin-toastr-js',
			'minerva-kb/admin-dashboard-chart-js'
		), null, true );

		wp_localize_script( 'minerva-kb/admin-dashboard-js', 'MinervaDashboard', array(
				'graphDates' => $this->analytics->get_recent_week_dates(),
				'graphViews' => $this->analytics->get_recent_week_views(),
				'graphLikes' => $this->analytics->get_recent_week_likes(),
				'graphDislikes' => $this->analytics->get_recent_week_dislikes(),
			)
		);
	}
}