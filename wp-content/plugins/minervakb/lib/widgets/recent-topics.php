<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MKB_Recent_Topics_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'mkb_recent_topics_widget',
			'description' => __('Displays recent Knowledge Base topics', 'minerva-kb' ),
		);
		parent::__construct( 'kb_recent_topics_widget', __('KB Topics', 'minerva-kb' ), $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		/**
		 * Do not render topics if user is globally restricted
		 */
		global $minerva_kb;

		if (MKB_Options::option('restrict_on') &&
		    MKB_Options::option('restrict_remove_from_archives') &&
		    $minerva_kb->restrict->is_user_globally_restricted()) {

			return false;
		}

		echo $args['before_widget'];

		$limit = ! empty( $instance['limit'] ) ? $instance['limit'] : 5;

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$topics_args = array(
			'hide_empty' => true,
			'number' => $limit
		);

		if (MKB_TemplateHelper::info('current_product')) {
			$topic_ids = MKB_TemplateHelper::info('product_topics');
			$topics = array();

			foreach ($topic_ids as $id) {
				if (MKB_TemplateHelper::info('current_product') == $id) {
					continue;
				}

				array_push($topics, get_term_by('id', $id, MKB_Options::option( 'article_cpt_category' )));
			}
		} else {
			$topics = get_terms( MKB_Options::option( 'article_cpt_category' ), $topics_args);
		}

		if ( sizeof( $topics ) ):
			?>
			<div class="mkb-widget-topics__list">
				<ul>
					<?php
					foreach ( $topics as $topic ):

						// skip all restricted topics
						if (MKB_Options::option('restrict_on') &&
						    MKB_Options::option('restrict_remove_from_archives') &&
						    isset($topic->term_id) && !$minerva_kb->restrict->is_topic_allowed($topic)) {

							continue;
						}

						$topic_link = get_term_link( $topic );
						?>
						<li class="mkb-widget-topics__list-item">
							<a href="<?php echo esc_attr( $topic_link ); ?>">
								<?php if (MKB_Options::option('widget_icons_on')): ?>
									<i class="mkb-widget-topics__list-icon fa <?php echo esc_attr( MKB_TemplateHelper::get_topic_icon( $topic ) ); ?>"></i>
								<?php endif; ?>
								<?php echo esc_html( $topic->name ); ?>
							</a>
						</li>
					<?php endforeach; // end of terms loop
					?>
				</ul>
			</div>
		<?php
		endif; // end of topics loop

		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Recent topics', 'minerva-kb' );
		$limit = ! empty( $instance['limit'] ) ? $instance['limit'] : 5;

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

		return $instance;
	}
}