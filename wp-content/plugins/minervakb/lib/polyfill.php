<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

/**
 * Some polyfills for legacy WordPress versions (<4.1)
 */
if (!function_exists('the_archive_title')) {
	function the_archive_title( $before = '', $after = '' ) {
		$title = get_the_archive_title();

		if ( ! empty( $title ) ) {
			echo $before . $title . $after;
		}
	}
}

if (!function_exists('get_the_archive_title')) {
	function get_the_archive_title() {
		if ( is_category() ) {
			$title = sprintf( __( 'Category: %s' ), single_cat_title( '', false ) );
		} elseif ( is_tag() ) {
			$title = sprintf( __( 'Tag: %s' ), single_tag_title( '', false ) );
		} elseif ( is_author() ) {
			$title = sprintf( __( 'Author: %s' ), '<span class="vcard">' . get_the_author() . '</span>' );
		} elseif ( is_year() ) {
			$title = sprintf( __( 'Year: %s' ), get_the_date( _x( 'Y', 'yearly archives date format' ) ) );
		} elseif ( is_month() ) {
			$title = sprintf( __( 'Month: %s' ), get_the_date( _x( 'F Y', 'monthly archives date format' ) ) );
		} elseif ( is_day() ) {
			$title = sprintf( __( 'Day: %s' ), get_the_date( _x( 'F j, Y', 'daily archives date format' ) ) );
		} elseif ( is_tax( 'post_format' ) ) {
			if ( is_tax( 'post_format', 'post-format-aside' ) ) {
				$title = _x( 'Asides', 'post format archive title' );
			} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
				$title = _x( 'Galleries', 'post format archive title' );
			} elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
				$title = _x( 'Images', 'post format archive title' );
			} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
				$title = _x( 'Videos', 'post format archive title' );
			} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
				$title = _x( 'Quotes', 'post format archive title' );
			} elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
				$title = _x( 'Links', 'post format archive title' );
			} elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
				$title = _x( 'Statuses', 'post format archive title' );
			} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
				$title = _x( 'Audio', 'post format archive title' );
			} elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
				$title = _x( 'Chats', 'post format archive title' );
			}
		} elseif ( is_post_type_archive() ) {
			$title = sprintf( __( 'Archives: %s' ), post_type_archive_title( '', false ) );
		} elseif ( is_tax() ) {
			$tax = get_taxonomy( get_queried_object()->taxonomy );
			/* translators: 1: Taxonomy singular name, 2: Current taxonomy term */
			$title = sprintf( __( '%1$s: %2$s' ), $tax->labels->singular_name, single_term_title( '', false ) );
		} else {
			$title = __( 'Archives' );
		}

		/**
		 * Filter the archive title.
		 *
		 * @since 4.1.0
		 *
		 * @param string $title Archive title to be displayed.
		 */
		return apply_filters( 'get_the_archive_title', $title );
	}
}

if (!function_exists('the_archive_description')) {
	function the_archive_description( $before = '', $after = '' ) {
		$description = get_the_archive_description();
		if ( $description ) {
			echo $before . $description . $after;
		}
	}
}

if (!function_exists('get_the_archive_description')) {
	function get_the_archive_description() {
		/**
		 * Filter the archive description.
		 *
		 * @since 4.1.0
		 *
		 * @see term_description()
		 *
		 * @param string $description Archive description to be displayed.
		 */
		return apply_filters( 'get_the_archive_description', term_description() );
	}
}

if (!function_exists('get_the_posts_pagination')) {
	function get_the_posts_pagination( $args = array() ) {
		$navigation = '';

		// Don't print empty markup if there's only one page.
		if ( $GLOBALS['wp_query']->max_num_pages > 1 ) {
			$args = wp_parse_args( $args, array(
				'mid_size'           => 1,
				'prev_text'          => _x( 'Previous', 'previous post' ),
				'next_text'          => _x( 'Next', 'next post' ),
				'screen_reader_text' => __( 'Posts navigation' ),
			) );

			// Make sure we get a string back. Plain is the next best thing.
			if ( isset( $args['type'] ) && 'array' == $args['type'] ) {
				$args['type'] = 'plain';
			}

			// Set up paginated links.
			$links = paginate_links( $args );

			if ( $links ) {
				$navigation = _navigation_markup( $links, 'pagination', $args['screen_reader_text'] );
			}
		}

		return $navigation;
	}
}

if (!function_exists('the_posts_pagination')) {
	function the_posts_pagination( $args = array() ) {
		echo get_the_posts_pagination( $args );
	}
}

if (!function_exists('_navigation_markup')) {
	function _navigation_markup( $links, $class = 'posts-navigation', $screen_reader_text = '' ) {
		if ( empty( $screen_reader_text ) ) {
			$screen_reader_text = __( 'Posts navigation' );
		}

		$template = '
	<nav class="navigation %1$s" role="navigation">
		<h2 class="screen-reader-text">%2$s</h2>
		<div class="nav-links">%3$s</div>
	</nav>';

		/**
		 * Filter the navigation markup template.
		 *
		 * Note: The filtered template HTML must contain specifiers for the navigation
		 * class (%1$s), the screen-reader-text value (%2$s), and placement of the
		 * navigation links (%3$s):
		 *
		 *     <nav class="navigation %1$s" role="navigation">
		 *         <h2 class="screen-reader-text">%2$s</h2>
		 *         <div class="nav-links">%3$s</div>
		 *     </nav>
		 *
		 * @since 4.4.0
		 *
		 * @param string $template The default template.
		 * @param string $class    The class passed by the calling function.
		 * @return string Navigation template.
		 */
		$template = apply_filters( 'navigation_markup_template', $template, $class );

		return sprintf( $template, sanitize_html_class( $class ), esc_html( $screen_reader_text ), $links );
	}
}

if (!function_exists('get_the_permalink')) {
	function get_the_permalink( $post = 0, $leavename = false ) {
		return get_permalink( $post, $leavename );
	}
}
