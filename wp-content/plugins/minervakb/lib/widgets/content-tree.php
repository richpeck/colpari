<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MKB_Content_Tree_Widget extends WP_Widget {
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'mkb_content_tree_widget',
			'description' => __('Displays all Knowledge Base content list', 'minerva-kb' ),
		);
		parent::__construct( 'kb_content_tree_widget', __('KB Content Tree', 'minerva-kb' ), $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];

		$show_count = ! empty( $instance['show_count'] ) ? (bool)$instance['show_count'] : false;

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$topics = get_terms( MKB_Options::option( 'article_cpt_category' ), array(
			'hide_empty' => true,
			'number' => 0
		) );

		uasort($topics, function($a, $b) {
			$orderA = (int)MKB_TemplateHelper::get_topic_option($a, 'topic_order');
			$orderB = (int)MKB_TemplateHelper::get_topic_option($b, 'topic_order');

			if ($orderA == $orderB) {
				return 0;
			}

			return ($orderA < $orderB) ? -1 : 1;
		});

		?>
		<div class="mkb-widget-content-tree__list">
			<ul class="mkb-widget-content-tree__topic-list">
				<?php

				foreach ($topics as $topic) :

					if ($topic->parent != '0') { // we'll handle children in content render
						continue;
					}

					$this->render_topic($topic, $topics, $show_count);
				endforeach;

				?>
			</ul>
		</div>
		<?php

		echo $args['after_widget'];
	}

	/**
	 * Renders one topic
	 *
	 * @param $topic
	 * @param $topics
	 */
	protected function render_topic($topic, $topics, $show_count) {
		?>
		<li class="mkb-widget-content-tree__topic" data-id="<?php echo esc_attr($topic->term_id); ?>">
			<span class="mkb-widget-content-tree__topic-name">
				<i class="mkb-widget-content-tree__list-icon fa <?php esc_attr_e(MKB_Options::option( 'content_tree_widget_icon' )); ?>"></i>
				<i class="mkb-widget-content-tree__list-icon-open fa <?php esc_attr_e(MKB_Options::option( 'content_tree_widget_icon_open' )); ?>"></i>
				<?php echo esc_html($topic->name); ?>
				<?php if ($show_count): ?>
					<span class="mkb-widget-content-tree__topic-count">
						<?php echo esc_attr($topic->count); ?>
					</span>
				<?php endif; ?>
			</span>

			<?php $this->render_topic_content($topic, $topics, $show_count); ?>
		</li>
	<?php
	}

	/**
	 * Renders topic contents
	 * @param $topic
	 * @param $topics
	 */
	protected function render_topic_content($topic, $topics, $show_count) {

		global $minerva_kb;

		uasort($topics, function($a, $b) {
			$orderA = (int)MKB_TemplateHelper::get_topic_option($a, 'topic_order');
			$orderB = (int)MKB_TemplateHelper::get_topic_option($b, 'topic_order');

			if ($orderA == $orderB) {
				return 0;
			}

			return ($orderA < $orderB) ? -1 : 1;
		});

		// first, render child topics, if any
		foreach ($topics as $child) :

			if ( ! $child->parent || $child->parent != $topic->term_id) {
				continue;
			}

			?>
			<ul class="mkb-widget-content-tree__topic-list">
				<?php

				$this->render_topic($child, $topics, $show_count);

				?>
			</ul>
		<?php

		endforeach;

		$current_id = null;

		if ($minerva_kb->info->is_single()) {
			$current_id = get_the_ID();
		}

		// render topic articles
		$query_args = array(
			'post_type' => MKB_Options::option( 'article_cpt' ),
			'ignore_sticky_posts' => 1,
			'posts_per_page' => -1,
			'post_status'      => 'publish',
			'suppress_filters' => true,
			'tax_query' => array(
				array(
					'taxonomy' => MKB_Options::option( 'article_cpt_category' ),
					'field' => 'slug',
					'terms' => $topic->slug,
					'include_children' => false
				),
			)
		);

		$topic_loop = new WP_Query( $query_args );

		if ( $topic_loop->have_posts() ): ?>
			<ul class="mkb-widget-content-tree__article-list">
				<?php
				while ( $topic_loop->have_posts() ) : $topic_loop->the_post();

					$is_active = (bool) ($current_id && $current_id === get_the_ID());

					?>
					<li class="mkb-widget-content-tree__article<?php if ($is_active) { esc_attr_e(' mkb-widget-content-tree__article--active'); }; ?>">
						<?php if (!$is_active): ?>
							<a href="<?php the_permalink(); ?>">
						<?php endif; ?>
							<span class="mkb-widget-content-tree__article-title">
								<?php if (MKB_Options::option('widget_icons_on')): ?>
									<i class="mkb-widget-articles__list-icon fa <?php echo esc_attr( MKB_Options::option( 'article_icon' ) ); ?>"></i>
								<?php endif; ?>
								<?php the_title(); ?>
							</span>
						<?php if (!$is_active): ?>
							</a>
						<?php endif; ?>
					</li>
				<?php
				endwhile;
				?>
			</ul>
		<?php
		endif;

		wp_reset_postdata();
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Knowledge Base', 'minerva-kb' );
		$show_count = isset($instance['show_count']) ? (bool) $instance['show_count'] : false;

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'minerva-kb' ); ?></label>
			<input class="widefat"
			       id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
			       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
			       type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_count'); ?>" name="<?php echo $this->get_field_name('show_count'); ?>"<?php checked( $show_count ); ?> />
			<label for="<?php echo $this->get_field_id('show_count'); ?>"><?php _e( 'Show articles count?', 'minerva-kb' ); ?></label><br />
		</p>
	<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['show_count'] = ( ! empty( $new_instance['show_count'] ) ) ? (bool)$new_instance['show_count'] : false;

		return $instance;
	}
}