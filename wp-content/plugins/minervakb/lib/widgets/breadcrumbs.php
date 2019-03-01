<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MKB_Breadcrumbs_Widget extends WP_Widget {
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'mkb_breadcrumbs_widget',
			'description' => __('Displays Knowledge Base breadcrumbs', 'minerva-kb' ),
		);
		parent::__construct( 'kb_breadcrumbs_widget', __('KB Breadcrumbs', 'minerva-kb' ), $widget_ops );
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

		if (!$minerva_kb->info->is_single() && !$minerva_kb->info->is_topic() && !$minerva_kb->info->is_search()) {
			return false;
		}

		echo $args['before_widget'];

		$label = ! empty( $instance['label'] ) ? $instance['label'] : '';

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		if (!$minerva_kb->restrict->check_access() && !MKB_Options::option('restrict_show_article_breadcrumbs')) {
			return false;
		}

		if ($minerva_kb->info->is_single()) {

			// article

			$terms = wp_get_post_terms( get_the_ID(), MKB_Options::option( 'article_cpt_category' ));
			$term = null;

			if ($terms && !empty($terms) && isset($terms[0])) {
				$term = $terms[0];
			}

			MKB_TemplateHelper::breadcrumbs( $term, MKB_Options::option( 'article_cpt_category' ), 'single', $label );
		} else if ($minerva_kb->info->is_topic()) {

			// topic

			$term = get_term_by( 'id', get_queried_object_id(), MKB_Options::option( 'article_cpt_category' ) );
			MKB_TemplateHelper::breadcrumbs( $term, MKB_Options::option( 'article_cpt_category', 'category', $label ) );
		} else if ($minerva_kb->info->is_search()) {

			// search

			MKB_TemplateHelper::search_breadcrumbs( $_REQUEST['s'], $label );
		}

		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : esc_html__( 'Knowledge Base', 'minerva-kb' );
		$label = isset( $instance['label'] ) ? $instance['label'] : esc_html__( 'You are here:', 'minerva-kb' );

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'minerva-kb' ); ?></label>
			<input class="widefat"
			       id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
			       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
			       type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'label' ) ); ?>"><?php esc_attr_e( 'Label:', 'minerva-kb' ); ?></label>
			<input class="widefat"
			       id="<?php echo esc_attr( $this->get_field_id( 'label' ) ); ?>"
			       name="<?php echo esc_attr( $this->get_field_name( 'label' ) ); ?>"
			       type="text" value="<?php echo esc_attr( $label ); ?>" />
		</p>
		<p><?php _e("You can leave title or label empty to remove them.", 'minerva-kb' ) ?></p>
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
		$instance['label'] = ( ! empty( $new_instance['label'] ) ) ? strip_tags( $new_instance['label'] ) : '';

		return $instance;
	}
}