<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MKB_Recent_Articles_Widget extends WP_Widget {
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'mkb_recent_articles_widget',
			'description' => __('Displays recent Knowledge Base articles', 'minerva-kb' ),
		);
		parent::__construct( 'kb_recent_articles_widget', __('KB Articles', 'minerva-kb' ), $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		/**
		 * Do not render articles if user is globally restricted
		 */
		global $minerva_kb;

		if (MKB_Options::option('restrict_on') && MKB_Options::option('restrict_remove_from_archives') && $minerva_kb->restrict->is_user_globally_restricted()) {
			return false;
		}

		echo $args['before_widget'];

		$limit = ! empty( $instance['limit'] ) ? $instance['limit'] : 5;
		$order = ! empty( $instance['order'] ) ? $instance['order'] : 'recent';
		$show_from_current_topic = ! empty( $instance['show_from_current_topic'] ) ? $instance['show_from_current_topic'] : false;

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		if ($order === 'likes') {
			$query_args = array(
				'posts_per_page'   => $limit,
				'offset'           => 0,
				'category'         => '',
				'category_name'    => '',
				'orderby'          => 'meta_value_num',
				'order'            => 'DESC',
				'include'          => '',
				'exclude'          => '',
				'meta_key'         => '_mkb_likes',
				'meta_value'       => '',
				'post_type'        => MKB_Options::option( 'article_cpt' ),
				'post_mime_type'   => '',
				'post_parent'      => '',
				'author'	   => '',
				'author_name'	   => '',
				'post_status'      => 'publish'
			);
		} else if ($order === 'views') {
			$query_args = array(
				'posts_per_page'   => $limit,
				'offset'           => 0,
				'category'         => '',
				'category_name'    => '',
				'orderby'          => 'meta_value_num',
				'order'            => 'DESC',
				'include'          => '',
				'exclude'          => '',
				'meta_key'         => '_mkb_views',
				'meta_value'       => '',
				'post_type'        => MKB_Options::option( 'article_cpt' ),
				'post_mime_type'   => '',
				'post_parent'      => '',
				'author'	   => '',
				'author_name'	   => '',
				'post_status'      => 'publish'
			);
		} else {
			$query_args = array(
				'post_type'           => MKB_Options::option( 'article_cpt' ),
				'ignore_sticky_posts' => 1,
				'posts_per_page' => $limit,
				'post_status'      => 'publish'
			);

			if ($order === 'updated') {
				$query_args['orderby'] = 'modified';
				$query_args['order'] = 'DESC';
			}
		}

		if (MKB_TemplateHelper::info('current_product')) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => MKB_Options::option( 'article_cpt_category' ),
					'field' => 'id',
					'terms' => MKB_TemplateHelper::info('product_topics'),
				),
			);
		} else if ($show_from_current_topic) {
			$topic_id = $this->get_current_topic_id();

			if ($topic_id) {
				$query_args['tax_query'] = array(
					array(
						'taxonomy' => MKB_Options::option( 'article_cpt_category' ),
						'field' => 'id',
						'terms' => $topic_id,
					),
				);
			}
		}

		/**
		 * Remove restricted articles from query, if required
		 */
		if (MKB_Options::option('restrict_on') && MKB_Options::option('restrict_remove_from_archives')) {
			global $minerva_kb;

			$query_args['post__in'] = $minerva_kb->restrict->get_allowed_article_ids_query();
		}

		$articles_loop = new WP_Query( $query_args );
		?>
		<div class="mkb-widget-articles__list">
			<ul>
				<?php

				if ( $articles_loop->have_posts() ):
					while ( $articles_loop->have_posts() ) : $articles_loop->the_post();
						?>
						<li class="mkb-widget-articles__list-item">
							<a href="<?php the_permalink(); ?>">
								<?php if (MKB_Options::option('widget_icons_on')): ?>
									<i class="mkb-widget-articles__list-icon fa <?php echo esc_attr( MKB_Options::option( 'article_icon' ) ); ?>"></i>
								<?php endif; ?>
								<?php the_title(); ?>
							</a>
						</li>
					<?php
					endwhile;
				endif;
				?>
			</ul>
		</div>
		<?php

		wp_reset_postdata();

		echo $args['after_widget'];
	}

	/**
	 * Gets current page topic (for single or topic pages)
	 */
	private function get_current_topic_id() {
		global $minerva_kb;

		if ($minerva_kb->info->is_single()) {
			$terms = wp_get_post_terms( get_the_ID(), MKB_Options::option( 'article_cpt_category' ));
			$term = null;

			if ($terms && !empty($terms) && isset($terms[0])) {
				$term = $terms[0];

				return $term->term_id;
			}
		} else if ($minerva_kb->info->is_topic()) {
			$term = get_term_by( 'id', get_queried_object_id(), MKB_Options::option( 'article_cpt_category' ) );

			return $term->term_id;
		}

		return null;
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Recent articles', 'minerva-kb' );
		$limit = ! empty( $instance['limit'] ) ? $instance['limit'] : 5;
		$order = ! empty( $instance['order'] ) ? $instance['order'] : 'recent';
		$show_from_current_topic = ! empty( $instance['show_from_current_topic'] ) ? $instance['show_from_current_topic'] : false;

		$order_options = array(
			array(
				'key' => 'recent',
				'label' => __('Recent', 'minerva-kb' )
			),
			array(
				'key' => 'updated',
				'label' => __('Recently updated', 'minerva-kb' )
			),
			array(
				'key' => 'likes',
				'label' => __('Most liked', 'minerva-kb' )
			),
			array(
				'key' => 'views',
				'label' => __('Most viewed', 'minerva-kb' )
			)
		);

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'minerva-kb' ); ?></label>
			<input class="widefat"
			       id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
			       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
			       type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php esc_attr_e( 'Limit:', 'minerva-kb' ); ?></label>
			<input class="widefat"
			       id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"
			       name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>"
			       type="text" value="<?php echo esc_attr( $limit ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>"><?php esc_attr_e( 'Order:', 'minerva-kb' ); ?></label>

			<select class="widefat"
			        id="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>"
			        name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>">
				<?php
				foreach ( $order_options as $option ):
					?>
					<option
						value="<?php echo esc_attr( $option["key"] ); ?>"<?php if ($option["key"] === $order) { echo 'selected="selected"'; }?>>
						<?php echo esc_html( $option["label"] ); ?>
					</option>
				<?php
				endforeach;
				?>
			</select>
		</p>

		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_from_current_topic'); ?>" name="<?php echo $this->get_field_name('show_from_current_topic'); ?>"<?php checked( $show_from_current_topic ); ?> />
			<label for="<?php echo $this->get_field_id('show_from_current_topic'); ?>"><?php _e( 'Show articles from current topic (only on topic/article page)?', 'minerva-kb' ); ?></label><br />
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
		$instance['limit'] = ( ! empty( $new_instance['limit'] ) ) ? strip_tags( $new_instance['limit'] ) : 5;
		$instance['show_from_current_topic'] = ( ! empty( $new_instance['show_from_current_topic'] ) ) ? (bool)$new_instance['show_from_current_topic'] : false;
		$instance['order'] = ( ! empty( $new_instance['order'] ) ) ? strip_tags( $new_instance['order'] ) : 'recent';

		return $instance;
	}
}