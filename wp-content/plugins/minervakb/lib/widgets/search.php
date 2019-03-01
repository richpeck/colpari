<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MKB_Search_Widget extends WP_Widget {
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'mkb_search_widget',
			'description' => __('Displays KB search', 'minerva-kb' ),
		);
		parent::__construct( 'kb_search_widget', __('KB Search', 'minerva-kb' ), $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		/**
		 * Do not render search if user is globally restricted
		 */
		global $minerva_kb;

		if (MKB_Options::option('restrict_on') && MKB_Options::option('restrict_remove_search_for_restricted') && $minerva_kb->restrict->is_user_globally_restricted()) {
			return false;
		}

		echo $args['before_widget'];

		$theme = ! empty( $instance['theme'] ) ? $instance['theme'] : 'clean';
		$placeholder = ! empty( $instance['placeholder'] ) ? $instance['placeholder'] : esc_html__( 'Search KB', 'minerva-kb' );

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		MKB_TemplateHelper::render_search(array(
			"search_title" => "",
			"search_tip" => "",
			"search_border_color" => "rgba(0,0,0,0)",
			"search_container_padding_top" => "0px",
			"search_container_padding_bottom" => "0px",
			"search_min_width" => "100%",
			"search_topics" => "",
			"add_gradient_overlay" => false,
			"add_pattern_overlay" => false,
			"disable_autofocus" => true,
			"search_container_bg" => "rgba(0,0,0,0)",
			"search_container_image_bg" => "",
			"show_topic_in_results" => false,

			"search_placeholder" => $placeholder,
			"search_theme" => $theme
		));

		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {

		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Search', 'minerva-kb' );
		$placeholder = ! empty( $instance['placeholder'] ) ? $instance['placeholder'] : esc_html__( 'Search KB', 'minerva-kb' );
		$theme = ! empty( $instance['theme'] ) ? $instance['theme'] : 'clean';

		$theme_options = array(
			array(
				'key' => 'minerva',
				'label' => __('Minerva', 'minerva-kb' )
			),
			array(
				'key' => 'clean',
				'label' => __('Clean', 'minerva-kb' )
			),
			array(
				'key' => 'mini',
				'label' => __('Mini', 'minerva-kb' )
			),
			array(
				'key' => 'invisible',
				'label' => __('Invisible', 'minerva-kb' )
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
			<label for="<?php echo esc_attr( $this->get_field_id( 'placeholder' ) ); ?>"><?php esc_attr_e( 'Placeholder:', 'minerva-kb' ); ?></label>
			<input class="widefat"
			       id="<?php echo esc_attr( $this->get_field_id( 'placeholder' ) ); ?>"
			       name="<?php echo esc_attr( $this->get_field_name( 'placeholder' ) ); ?>"
			       type="text" value="<?php echo esc_attr( $placeholder ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'theme' ) ); ?>"><?php esc_attr_e( 'theme:', 'minerva-kb' ); ?></label>

			<select class="widefat"
			        id="<?php echo esc_attr( $this->get_field_id( 'theme' ) ); ?>"
			        name="<?php echo esc_attr( $this->get_field_name( 'theme' ) ); ?>">
				<?php
				foreach ( $theme_options as $option ):
					?>
					<option
						value="<?php echo esc_attr( $option["key"] ); ?>"<?php if ($option["key"] === $theme) { echo 'selected="selected"'; }?>>
						<?php echo esc_html( $option["label"] ); ?>
					</option>
				<?php
				endforeach;
				?>
			</select>
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
		$instance['placeholder'] = ( ! empty( $new_instance['placeholder'] ) ) ? strip_tags( $new_instance['placeholder'] ) : esc_html__( 'Search KB', 'minerva-kb' );
		$instance['theme'] = ( ! empty( $new_instance['theme'] ) ) ? strip_tags( $new_instance['theme'] ) : 'clean';

		return $instance;
	}
}