<?php
/**
 * @package   Awesome Support FAQ
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_filter( 'manage_faq_posts_columns', 'asfaq_post_type_columns', 10, 1 );
/**
 * Add the views count in the post type list table
 *
 * @since 1.0
 *
 * @param $columns
 *
 * @return array
 */
function asfaq_post_type_columns( $columns ) {

	$new = array();

	foreach ( $columns as $id => $column ) {

		if ( 'date' === $id ) {
			$new['views'] = esc_html_x( 'Views', 'FAQ views count', 'as-faq' );
		}

		$new[ $id ] = $column;

	}

	return $new;

}


add_action( 'manage_faq_posts_custom_column', 'asfaq_post_type_columns_content', 10, 2 );
/**
 * Set the custom columns content
 *
 * @since 1.0
 *
 * @param $column
 * @param $post_id
 */
function asfaq_post_type_columns_content( $column, $post_id ) {

	switch ( $column ) {

		case 'views' :

			$views = get_post_meta( $post_id, '_as_faq_count', true );
			$views = empty( $views ) ? 0 : (int) $views;

			echo number_format_i18n( $views, 0 );

			break;

	}
}

add_filter( 'manage_edit-faq_sortable_columns', 'asfaq_custom_columns_sortable', 10, 1 );
/**
 * Make custom columns sortable
 *
 * @since 1.0
 *
 * @param  array $columns Already sortable columns
 *
 * @return array          New sortable columns
 */
function asfaq_custom_columns_sortable( $columns ) {

	$columns['views'] = 'views';

	return $columns;

}

add_action( 'pre_get_posts', 'asfaq_custom_column_orderby' );
/**
 * Reorder custom columns based on custom values.
 *
 * FAQs with 0 views are hidden. Let's see if we keep it this way...
 *
 * @since 1.0
 *
 * @param  object $query Main query
 *
 * @return void
 */
function asfaq_custom_column_orderby( $query ) {

	if ( ! isset( $_GET['post_type'] ) || 'faq' !== $_GET['post_type'] ) {
		return;
	}

	if ( ! $query->is_main_query() ) {
		return;
	}

	$orderby = $query->get( 'orderby' );

	if ( ! empty( $orderby ) && 'views' === $orderby ) {
		$query->set( 'meta_key', '_as_faq_count' );
		$query->set( 'orderby', 'meta_value_num' );
	}

}

add_action( 'admin_head', 'asfaq_post_type_views_column' );
/**
 * Add style for the views custom column
 *
 * @since 1.0
 * @return void
 */
function asfaq_post_type_views_column() {

	if ( ! isset( $_GET['post_type'] ) || 'faq' !== $_GET['post_type'] ) {
		return;
	}

	echo '<style>.fixed .column-views {width: 10%;}</style>';

}