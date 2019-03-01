<?php
/**
 * Widget Class to display author details.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Widget class.
 */
class Fusion_Widget_Author extends WP_Widget {

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {

		$widget_ops  = array(
			'classname'   => 'fusion-author-widget',
			'description' => __( 'Display author details.', 'Avada' ),
		);
		$control_ops = array(
			'id_base' => 'fusion_author-widget',
		);

		parent::__construct( 'fusion_author-widget', __( 'Avada: Author', 'Avada' ), $widget_ops, $control_ops );

	}

	/**
	 * Echoes the widget content.
	 *
	 * @access public
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {
		extract( $args );

		// Initialize needed variables.
		$custom_author          = isset( $instance['custom_author'] ) ? trim( $instance['custom_author'] ) : '';
		$display_avatar         = isset( $instance['display_avatar'] ) ? (int) $instance['display_avatar'] : 54;
		$display_biography      = isset( $instance['display_biography'] ) ? (int) $instance['display_biography'] : 0;
		$display_custom_message = isset( $instance['display_custom_message'] ) ? (int) $instance['display_custom_message'] : 0;
		$display_sep            = isset( $instance['display_sep'] ) ? (int) $instance['display_sep'] : 1;
		$separator_class        = ( $display_sep ) ? 'fusion-author-widget-separator' : '';
		$display_name           = isset( $instance['display_name'] ) ? $instance['display_name'] : 'name_with_label';
		$display_post_date      = isset( $instance['display_post_date'] ) ? $instance['display_post_date'] : 'date_only';
		$display_social_links   = isset( $instance['display_social_links'] ) ? (int) $instance['display_social_links'] : 0;
		$link_author_page       = isset( $instance['link_author_page'] ) ? (int) $instance['link_author_page'] : 1;
		$queried_object         = get_queried_object();
		$author_id              = 0;

		if ( '' !== $custom_author ) {
			$author_id = (int) $custom_author;

			if ( ! is_numeric( $custom_author ) ) {
				$user = get_user_by( 'slug', $custom_author );

				if ( ! empty( $user ) ) {
					$author_id = $user->ID;
				}
			}
		} elseif ( is_author() ) {
			$author_id = $queried_object->data->ID;
		} elseif ( is_single() ) {
			$author_id = $queried_object->post_author;
		}

		// Early exit if no author could be found.
		if ( ! $author_id ) {
			return;
		}

		$author_name       = get_the_author_meta( 'display_name', $author_id );
		$author_link_open  = '';
		$author_link_close = '';
		if ( $link_author_page ) {
			$author_link_open  = '<a class"fusion-author-widget-link" href="' . esc_url( get_author_posts_url( $author_id ) ) . '" title="' . esc_attr( $author_name ) . '" rel="author">';
			$author_link_close = '</a>';
		}

		$custom_message = '';
		if ( $display_custom_message ) {
			$custom_message = get_the_author_meta( 'author_custom', $author_id );
		}

		$widget_title = '';
		if ( 'name_as_title' === $display_name ) {
			$widget_title = $author_name;
		} elseif ( isset( $instance['title'] ) ) {
			$widget_title = $instance['title'];
		}

		$widget_title = apply_filters( 'widget_title', $widget_title );

		echo $before_widget; // WPCS: XSS ok.

		if ( $widget_title ) {
			echo $before_title . $widget_title . $after_title; // WPCS: XSS ok.
		}
		?>
		<div class="fusion-author-widget-content <?php echo esc_attr( $separator_class ); ?>">
			<?php if ( $display_avatar ) : ?>
				<div class="fusion-author-widget-avatar">
					<?php echo $author_link_open . get_avatar( get_the_author_meta( 'email', $author_id ), $display_avatar ) . $author_link_close; // WPCS: XSS ok. ?>
				</div>
			<?php endif; ?>

			<?php if ( 'name_only' === $display_name || 'name_with_label' === $display_name ) : ?>
				<div class="fusion-author-widget-name">
					<?php if ( 'name_with_label' === $display_name ) : ?>
						<div class="fusion-author-widget-name-label"><?php echo esc_html( apply_filters( 'avada_author_widget_posted_by', __( 'Posted by', 'Avada' ) ) ); ?></div>
					<?php endif; ?>
					<?php echo $author_link_open . ' <span class="fusion-author-widget-name">' . $author_name . '</span>' . $author_link_close; // WPCS: XSS ok. ?>
				</div>
			<?php endif; ?>

			<?php if ( $custom_message ) : ?>
				<div class="fusion-author-widget-sep"></div>
				<div class="fusion-author-widget-tagline">
					<?php echo get_the_author_meta( 'author_custom', $author_id ); // WPCS: XSS ok. ?>
				</div>
			<?php endif; ?>

			<?php if ( $display_biography ) : ?>
				<?php
				$author_biography = get_the_author_meta( 'description', $author_id );

				// If no description was added by user, add some default text and stats.
				if ( empty( $author_biography ) ) {
					$author_biography = esc_html__( 'This author has not yet filled in any details.', 'Avada' );
					/* translators: %s: Number. */
					$author_biography .= '<br />' . sprintf( esc_html__( 'So far the author has created %s blog entries.', 'Avada' ), count_user_posts( $author_id ) );
				}
				?>
				<div class="fusion-author-widget-sep"></div>
				<div class="fusion-author-widget-biography">
					<?php echo $author_biography; // WPCS: XSS ok. ?>
				</div>
			<?php endif; ?>

			<?php if ( $display_social_links ) : ?>
				<?php
				// Get the social icons for the author set on his profile page.
				$author_social_icon_options = array(
					'authorpage'        => 'yes',
					'author_id'         => $author_id,
					'position'          => 'author',
					'color_type'        => Avada()->settings->get( 'social_links_color_type' ),
					'icon_colors'       => Avada()->settings->get( 'social_links_icon_color' ),
					'box_colors'        => Avada()->settings->get( 'social_links_box_color' ),
					'icon_boxed'        => Avada()->settings->get( 'social_links_boxed' ),
					'icon_boxed_radius' => Fusion_Sanitize::size( Avada()->settings->get( 'social_links_boxed_radius' ) ),
					'tooltip_placement' => Avada()->settings->get( 'social_links_tooltip_placement' ),
					'linktarget'        => Avada()->settings->get( 'social_icons_new' ),
				);
				?>
				<div class="fusion-author-widget-sep"></div>
				<div class="fusion-author-widget-social">
					<?php echo Avada()->social_sharing->render_social_icons( $author_social_icon_options ); // WPCS: XSS ok. ?>
				</div>
			<?php endif; ?>

			<?php if ( 'no' !== $display_post_date && is_single() ) : ?>
				<div class="fusion-author-widget-sep"></div>
				<div class="fusion-author-widget-date">
					<?php if ( 'date_with_label' === $display_post_date ) : ?>
						<div class="fusion-author-widget-date-label"><?php esc_html_e( 'Post Date', 'Avada' ); ?></div>
					<?php endif; ?>
					<?php echo get_the_time( Avada()->settings->get( 'date_format' ), get_queried_object_id() ); // WPCS: XSS ok. ?>
				</div>
			<?php endif; ?>
		</div>

		<?php

		echo $after_widget; // WPCS: XSS ok.

	}

	/**
	 * Updates a particular instance of a widget.
	 *
	 * This function should check that `$new_instance` is set correctly. The newly-calculated
	 * value of `$instance` should be returned. If false is returned, the instance won't be
	 * saved/updated.
	 *
	 * @access public
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title']                  = isset( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['custom_author']          = isset( $new_instance['custom_author'] ) ? strip_tags( $new_instance['custom_author'] ) : '';
		$instance['display_avatar']         = isset( $new_instance['display_avatar'] ) ? strip_tags( $new_instance['display_avatar'] ) : '54';
		$instance['display_biography']      = isset( $new_instance['display_biography'] ) ? (int) $new_instance['display_biography'] : 0;
		$instance['display_custom_message'] = isset( $new_instance['display_custom_message'] ) ? (int) $new_instance['display_custom_message'] : 0;
		$instance['display_sep']            = isset( $new_instance['display_sep'] ) ? (int) $new_instance['display_sep'] : 0;
		$instance['display_name']           = isset( $new_instance['display_name'] ) ? strip_tags( $new_instance['display_name'] ) : 'name_with_label';
		$instance['display_post_date']      = isset( $new_instance['display_post_date'] ) ? strip_tags( $new_instance['display_post_date'] ) : 'date_only';
		$instance['display_social_links']   = isset( $new_instance['display_social_links'] ) ? (int) $new_instance['display_social_links'] : 0;
		$instance['link_author_page']       = isset( $new_instance['link_author_page'] ) ? (int) $new_instance['link_author_page'] : 0;

		return $instance;

	}

	/**
	 * Outputs the settings update form.
	 *
	 * @access public
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {

		$defaults = array(
			'title'                  => __( 'Author', 'Avada' ),
			'custom_author'          => '',
			'display_avatar'         => '54',
			'display_biography'      => 0,
			'display_custom_message' => 0,
			'display_name'           => 'name_with_label',
			'display_post_date'      => 'date_only',
			'display_sep'            => 1,
			'display_social_links'   => 0,
			'link_author_page'       => 1,
		);

		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<h4 style="line-height: 1.6em;"><?php esc_attr_e( 'IMPORTANT: When used on single posts, the author will be auto pulled. If you want to display a specific author please use the "Custom Author" field. Post date will on be displayed on single posts.', 'Avada' ); ?></h4>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'Avada' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'custom_author' ) ); ?>"><?php esc_attr_e( 'Custom Author (username or ID):', 'Avada' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'custom_author' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'custom_author' ) ); ?>" value="<?php echo esc_attr( $instance['custom_author'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'display_avatar' ) ); ?>"><?php esc_html_e( 'Avatar:', 'Avada' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'display_avatar' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_avatar' ) ); ?>" class="widefat" style="width:100%;">
				<option value="32" <?php echo ( '32' === $instance['display_avatar'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Small', 'Avada' ); ?></option>
				<option value="54" <?php echo ( '54' === $instance['display_avatar'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Medium', 'Avada' ); ?></option>
				<option value="72" <?php echo ( '72' === $instance['display_avatar'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Large', 'Avada' ); ?></option>
				<option value="96" <?php echo ( '96' === $instance['display_avatar'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'XLarge', 'Avada' ); ?></option>
				<option value="0" <?php echo ( '0' === $instance['display_avatar'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'None', 'Avada' ); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'display_name' ) ); ?>"><?php esc_html_e( 'Display Name:', 'Avada' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'display_name' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_name' ) ); ?>" class="widefat" style="width:100%;">
				<option value="name_with_label" <?php echo ( 'name_with_label' === $instance['display_name'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Name with label', 'Avada' ); ?></option>
				<option value="name_only" <?php echo ( 'name_only' === $instance['display_name'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Name only', 'Avada' ); ?></option>
				<option value="name_as_title" <?php echo ( 'name_as_title' === $instance['display_name'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Name as widget title', 'Avada' ); ?></option>
				<option value="no" <?php echo ( 'no' === $instance['display_name'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'No', 'Avada' ); ?></option>
			</select>
		</p>

		<p>
			<input class="checkbox" type="checkbox"  value="1" <?php checked( $instance['link_author_page'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'link_author_page' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'link_author_page' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'link_author_page' ) ); ?>"><?php esc_html_e( 'Link to author page', 'Avada' ); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox"  value="1" <?php checked( $instance['display_custom_message'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'display_custom_message' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_custom_message' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'display_custom_message' ) ); ?>"><?php esc_html_e( 'Display custom author message', 'Avada' ); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox"  value="1" <?php checked( $instance['display_biography'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'display_biography' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_biography' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'display_biography' ) ); ?>"><?php esc_html_e( 'Display author biography', 'Avada' ); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox"  value="1" <?php checked( $instance['display_social_links'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'display_social_links' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_social_links' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'display_social_links' ) ); ?>"><?php esc_html_e( 'Display author social icons', 'Avada' ); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox"  value="1" <?php checked( $instance['display_sep'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'display_sep' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_sep' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'display_sep' ) ); ?>"><?php esc_html_e( 'Display separator', 'Avada' ); ?></label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'display_post_date' ) ); ?>"><?php esc_html_e( 'Display Post Date:', 'Avada' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'display_post_date' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_post_date' ) ); ?>" class="widefat" style="width:100%;">
				<option value="date_with_label" <?php echo ( 'date_with_label' === $instance['display_post_date'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Date with label', 'Avada' ); ?></option>
				<option value="date_only" <?php echo ( 'date_only' === $instance['display_post_date'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Date only', 'Avada' ); ?></option>
				<option value="no" <?php echo ( 'no' === $instance['display_post_date'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'No', 'Avada' ); ?></option>
			</select>
		</p>

		<?php
	}
}
