<?php
/**
 * Handles the Events-Calendar implementation.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      3.8.7
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Handles the Events-Calendar implementation.
 */
class Avada_EventsCalendar {

	/**
	 * Holds the HMTL of the title bar.
	 *
	 * @access private
	 * @since 5.6
	 * @var string
	 */
	private $title_bar_html;

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {
		add_action( 'tribe_events_before_the_title', array( $this, 'before_the_title' ) );
		add_action( 'tribe_events_after_the_title', array( $this, 'after_the_title' ) );

		add_filter( 'tribe_events_mobile_breakpoint', array( $this, 'set_mobile_breakpoint' ) );
		add_action( 'tribe_events_bar_after_template', array( $this, 'add_clearfix' ) );

		add_filter( 'tribe_events_get_the_excerpt', array( $this, 'get_the_excerpt' ), 10, 2 );

		add_action( 'tribe_events_pro_tribe_events_shortcode_prepare_photo', array( $this, 'add_packery_library_to_photo_view' ), 20 );

		add_action( 'customize_controls_print_styles', array( $this, 'ec_customizer_styles' ), 999 );

		add_action( 'tribe_customizer_register_single_event_settings', array( $this, 'add_single_event_notice' ), 10, 2 );
		add_action( 'tribe_customizer_register_photo_view_settings', array( $this, 'add_photo_view_notice' ), 10, 2 );
		add_action( 'tribe_customizer_register_month_week_view_settings', array( $this, 'add_month_week_view_notice' ), 10, 2 );

		add_filter( 'tribe_get_map_link_html', array( $this, 'change_map_link_html' ), 10 );

		add_filter( 'tribe_the_notices', array( $this, 'style_notices' ), 10, 2 );

		add_filter( 'tribe_get_template_part_content', array( $this, 'position_events_title_bar' ), 10, 5 );

		add_filter( 'tribe_get_template_part_content', array( $this, 'sidebar_headings' ), 10, 5 );
	}

	/**
	 * Open the wrapper before the title.
	 *
	 * @access public
	 */
	public function before_the_title() {
		echo '<div class="fusion-events-before-title">';
	}

	/**
	 * Close the wrapper after the title.
	 *
	 * @access public
	 */
	public function after_the_title() {
		echo '</div>';
	}

	/**
	 * Removes arrows from the "previous" link.
	 *
	 * @access public
	 * @param string $anchor The HTML.
	 * @return string
	 */
	public function remove_arrow_from_prev_link( $anchor ) {
		return tribe_get_prev_event_link( '%title%' );
	}

	/**
	 * Removes arrows from the "next" link.
	 *
	 * @access public
	 * @param string $anchor The HTML.
	 * @return string
	 */
	public function remove_arrow_from_next_link( $anchor ) {
		return tribe_get_next_event_link( '%title%' );
	}

	/**
	 * Returns the mobile breakpoint.
	 *
	 * @access public
	 * @return int
	 */
	public function set_mobile_breakpoint() {
		return intval( Avada()->settings->get( 'content_break_point' ) );
	}

	/**
	 * Renders the title for single events.
	 *
	 * @access public
	 */
	public static function render_single_event_title() {
		$event_id = get_the_ID();
		?>
		<div class="fusion-events-single-title-content">
			<?php the_title( '<h2 class="tribe-events-single-event-title summary entry-title">', '</h2>' ); ?>
			<div class="tribe-events-schedule updated published tribe-clearfix">
				<?php echo tribe_events_event_schedule_details( $event_id, '<h3>', '</h3>' ); // WPCS: XSS ok. ?>
				<?php if ( tribe_get_cost() ) : ?>
					<span class="tribe-events-divider">|</span>
					<span class="tribe-events-cost"><?php echo tribe_get_cost( null, true ); // WPCS: XSS ok. ?></span>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Adds clearfix.
	 *
	 * @access public
	 */
	public function add_clearfix() {
		echo '<div class="clearfix"></div>';
	}

	/**
	 * Renders to correct excerpts on archive pages.
	 *
	 * @since 5.1.6
	 * @access public
	 * @param string $excerpt The post excerpt.
	 * @param object $_post The post object.
	 * @return string The new excerpt.
	 */
	public function get_the_excerpt( $excerpt, $_post ) {
		global $post;

		if ( false !== strpos( get_post_type( $_post->ID ), 'tribe_' ) && is_archive() && ! empty( $post->ID ) && $post->ID === $_post->ID ) {
			return fusion_get_post_content( $post->ID, 'yes', apply_filters( 'excerpt_length', 55 ), true );
		}

		return $excerpt;
	}

	/**
	 * Add packery library to the Events Calendar Photo View shortcode.
	 *
	 * @since 5.3.1
	 * @access public
	 * @return void
	 */
	public function add_packery_library_to_photo_view() {
		wp_enqueue_script( 'tribe-events-pro-isotope-packery', FUSION_LIBRARY_URL . '/assets/min/js/library/packery.js', array( 'tribe-events-pro-isotope' ), '', true );
	}

	/**
	 * Add CSS to hide incompatible customizer controls.
	 *
	 * @since 5.5.0
	 * @access public
	 * @return void
	 */
	public function ec_customizer_styles() {
		?>
		<style>
			li#customize-control-tribe_customizer-month_week_view-highlight_color,
			li#customize-control-tribe_customizer-photo_view-bg_color,
			li#customize-control-tribe_customizer-single_event-post_title_color {
				opacity: 0.2;
				pointer-events: none;
				cursor: not-allowed;
			}
		</style>
		<?php
	}

	/**
	 * Add notice to Events Calendar single event customizer section.
	 *
	 * @since 5.5.0
	 * @access public
	 * @param  WP_Customize_Section $section The WordPress section instance.
	 * @param  WP_Customize_Manager $manager The WordPress Customizer manager.
	 * @return void
	 */
	public function add_single_event_notice( WP_Customize_Section $section, WP_Customize_Manager $manager ) {
		$customizer = Tribe__Customizer::instance();

		$manager->add_setting(
			$customizer->get_setting_name( 'avada_ec_notice_post_title_color', $section ),
			array(
				'type' => 'hidden',
			)
		);

		$manager->add_control(
			'avada_ec_notice_post_title_color',
			array(
				'label'       => __( 'NOTE', 'Avada' ),
				/* translators: EC Customizer notice. */
				'description' => sprintf( __( 'You can control the post title color from Avada theme options panel through the <a href="%1$s" target="_blank">Events Primary Color Overlay Text Color</a> setting. Avada has additional <a href="%2$s" target="_blank">Event Calendar settings</a> in theme options.', 'Avada' ), Avada()->settings->get_setting_link( 'primary_overlay_text_color' ), Avada()->settings->get_setting_link( 'primary_overlay_text_color' ) ),
				'section'     => $section->id,
				'settings'    => $customizer->get_setting_name( 'avada_ec_notice_post_title_color', $section ),
				'type'        => 'hidden',
			)
		);
	}

	/**
	 * Add notice to Events Calendar photo view customizer section.
	 *
	 * @since 5.5.0
	 * @access public
	 * @param  WP_Customize_Section $section The WordPress section instance.
	 * @param  WP_Customize_Manager $manager The WordPress Customizer manager.
	 * @return void
	 */
	public function add_photo_view_notice( WP_Customize_Section $section, WP_Customize_Manager $manager ) {
		$customizer = Tribe__Customizer::instance();

		$manager->add_setting(
			$customizer->get_setting_name( 'avada_ec_notice_photo_bg_color', $section ),
			array(
				'type' => 'hidden',
			)
		);

		$manager->add_control(
			'avada_ec_notice_photo_bg_color',
			array(
				'label'       => __( 'NOTE', 'Avada' ),
				/* translators: EC Customizer notice. */
				'description' => sprintf( __( 'You can control the photo background color from Avada theme options panel through the <a href="%1$s" target="_blank">Grid Box Color</a> setting. Avada has additional <a href="%2$s" target="_blank">Event Calendar settings</a> in theme options.', 'Avada' ), Avada()->settings->get_setting_link( 'timeline_bg_color' ), Avada()->settings->get_setting_link( 'primary_overlay_text_color' ) ),
				'section'     => $section->id,
				'settings'    => $customizer->get_setting_name( 'avada_ec_notice_photo_bg_color', $section ),
				'type'        => 'hidden',
			)
		);
	}

	/**
	 * Add notice to Events Calendar month/week view customizer section.
	 *
	 * @since 5.5.0
	 * @access public
	 * @param  WP_Customize_Section $section The WordPress section instance.
	 * @param  WP_Customize_Manager $manager The WordPress Customizer manager.
	 * @return void
	 */
	public function add_month_week_view_notice( WP_Customize_Section $section, WP_Customize_Manager $manager ) {
		$customizer = Tribe__Customizer::instance();

		$manager->add_setting(
			$customizer->get_setting_name( 'avada_ec_notice_highlight_color', $section ),
			array(
				'type' => 'hidden',
			)
		);

		$manager->add_control(
			'avada_ec_notice_highlight_color',
			array(
				'label'       => __( 'NOTE', 'Avada' ),
				/* translators: EC Customizer notice. */
				'description' => sprintf( __( 'You can control the calendar highlight color from Avada theme options panel through the <a href="%1$s" target="_blank">Primary Color</a> setting. Avada has additional <a href="%2$s" target="_blank">Event Calendar settings</a> in theme options.', 'Avada' ), Avada()->settings->get_setting_link( 'primary_color' ), Avada()->settings->get_setting_link( 'primary_overlay_text_color' ) ),
				'section'     => $section->id,
				'settings'    => $customizer->get_setting_name( 'avada_ec_notice_highlight_color', $section ),
				'type'        => 'hidden',
			)
		);
	}

	/**
	 * Change the map link text.
	 *
	 * @since 5.5.0
	 * @access public
	 * @param string $link The link markup.
	 * @return string The adapted link markup.
	 */
	public function change_map_link_html( $link ) {
		$link = str_replace( 'target="_blank">+', 'target="_blank">', $link );

		return $link;
	}

	/**
	 * Style Event Notices.
	 *
	 * @since 5.5.0
	 * @access public
	 * @param string $html The notice markup.
	 * @param string $notices The actual notices.
	 * @return string The newly styled notice markup.
	 */
	public function style_notices( $html, $notices ) {

		if ( ! empty( $notices ) && shortcode_exists( 'fusion_alert' ) ) {
			$html = do_shortcode( '[fusion_alert class="tribe-events-notices" type="general"]<span>' . implode( '</span><br />', $notices ) . '</span>[/fusion_alert]' );
		}

		return $html;
	}

	/**
	 * Positions or disables the events page title.
	 *
	 * @access public
	 * @since 5.6
	 * @param string $html The template markup.
	 * @param string $template The template.
	 * @param string $file The template file.
	 * @param string $slug The template slug.
	 * @param string $name The template name.
	 * @return string Empty string.
	 */
	public function position_events_title_bar( $html, $template, $file, $slug, $name ) {
		if ( $slug && false !== strpos( $slug, 'title-bar' ) ) {
			if ( 'disable' === Avada()->settings->get( 'ec_display_page_title' ) ) {
				return '';
			} elseif ( 'below' === Avada()->settings->get( 'ec_display_page_title' ) ) {
				$this->title_bar_html = str_replace( array( '<h1', '</h1>' ), array( '<h2', '</h2>' ), $html );

				$action = 'tribe_events_bar_after_template';
				if ( class_exists( 'Tribe__Events__Filterbar__View' ) && 'horizontal' === tribe_get_option( 'events_filters_layout' ) ) {
					$action = 'tribe_events_filter_view_after_template';
				}

				add_action( $action, array( $this, 'the_events_title_bar' ), 20 );

				add_action( 'tribe_events_pro_tribe_events_shortcode_title_bar', array( $this, 'the_events_title_bar' ), 20 );

				return '';
			} else {

				// Extend "Upcoming Events" borders on versions above 4.6.18.
				if ( version_compare( Tribe__Events__Main::VERSION, '4.6.19', '>=' ) ) {
					$html = str_replace( 'tribe-events-page-title', 'tribe-events-page-title fusion-events-title-above', $html );
				}
			}
		}

		return $html;
	}

	/**
	 * Echo the events page title bar.
	 *
	 * @access public
	 * @since 5.6
	 * @param object $class_object A EC Pro shortcode object.
	 * @return void
	 */
	public function the_events_title_bar( $class_object = false ) {
		if ( is_object( $class_object ) ) {
			if ( ! $class_object->is_attribute_truthy( 'tribe-bar' ) ) {
				echo $this->title_bar_html; // WPCS: XSS ok.
			}
		} else {
			echo $this->title_bar_html; // WPCS: XSS ok.
		}
	}

	/**
	 * Change headings from h2 to h3.
	 *
	 * @access public
	 * @since 5.6
	 * @param string $html The template markup.
	 * @param string $template The template.
	 * @param string $file The template file.
	 * @param string $slug The template slug.
	 * @param string $name The template name.
	 * @return string The altered sidebar headings.
	 */
	public function sidebar_headings( $html, $template, $file, $slug, $name ) {
		if ( $slug && false !== strpos( $slug, 'modules/meta/' ) ) {
			return str_replace( array( '<h2', '</h2>' ), array( '<h4', '</h4>' ), $html );
		}
		return $html;
	}
}
