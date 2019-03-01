<?php
/**
 * @package   Awesome Support FAQ/Post Type
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'init', 'asfaq_register_post_type' );
/**
 * Register the FAQ post type
 *
 * @since 0.1.0
 * @return void
 */
function asfaq_register_post_type() {

	$slug = asfaq_get_option( 'slug', 'question' );

	$labels = array(
		'name'               => _x( 'Frequently Asked Questions', 'post type general name', 'as-faq' ),
		'singular_name'      => _x( 'FAQ', 'post type singular name', 'as-faq' ),
		'menu_name'          => _x( 'FAQs', 'admin menu', 'as-faq' ),
		'name_admin_bar'     => _x( 'FAQ', 'add new on admin bar', 'as-faq' ),
		'add_new'            => _x( 'Add FAQ', 'faq', 'as-faq' ),
		'add_new_item'       => __( 'Add New FAQ', 'as-faq' ),
		'new_item'           => __( 'New FAQ', 'as-faq' ),
		'edit_item'          => __( 'Edit FAQ', 'as-faq' ),
		'view_item'          => __( 'View FAQ', 'as-faq' ),
		'all_items'          => __( 'All FAQs', 'as-faq' ),
		'search_items'       => __( 'Search FAQs', 'as-faq' ),
		'parent_item_colon'  => __( 'Parent FAQ:', 'as-faq' ),
		'not_found'          => __( 'No FAQs found.', 'as-faq' ),
		'not_found_in_trash' => __( 'No FAQs found in Trash.', 'as-faq' )
	);

	$args = apply_filters( 'asfaq_post_type_args', array(
		'labels'             => $labels,
		'description'        => __( 'FAQs that can be generated from your tickets directly', 'as-faq' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => $slug ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'menu_icon'          => 'dashicons-editor-help',
		'supports'           => array( 'title', 'editor' )
	) );

	register_post_type( 'faq', $args );

}

add_filter( 'post_updated_messages', 'asfaq_updated_messages' );
/**
 * Edit the updated messages
 *
 * See /wp-admin/edit-form-advanced.php
 *
 * @since 0.1.0
 *
 * @param array $messages Existing post update messages.
 *
 * @return array Amended post update messages with new CPT update messages.
 */
function asfaq_updated_messages( $messages ) {

	$post             = get_post();
	$post_type_object = get_post_type_object( 'faq' );

	$messages['faq'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => __( 'FAQ updated.', 'as-faq' ),
		2  => __( 'Custom field updated.', 'as-faq' ),
		3  => __( 'Custom field deleted.', 'as-faq' ),
		4  => __( 'FAQ updated.', 'as-faq' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'FAQ restored to revision from %s', 'as-faq' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => __( 'FAQ published.', 'as-faq' ),
		7  => __( 'FAQ saved.', 'as-faq' ),
		8  => __( 'FAQ submitted.', 'as-faq' ),
		9  => sprintf(
			__( 'Book scheduled for: <strong>%1$s</strong>.', 'as-faq' ),
			// translators: Publish box date format, see http://php.net/date
			date_i18n( __( 'M j, Y @ G:i', 'as-faq' ), strtotime( $post->post_date ) )
		),
		10 => __( 'FAQ draft updated.', 'as-faq' )
	);

	$permalink = get_permalink( $post->ID );

	$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View FAQ', 'as-faq' ) );
	$messages['faq'][1] .= $view_link;
	$messages['faq'][6] .= $view_link;
	$messages['faq'][9] .= $view_link;

	$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
	$preview_link      = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview FAQ', 'as-faq' ) );
	$messages['faq'][8] .= $preview_link;
	$messages['faq'][10] .= $preview_link;

	return $messages;

}

add_action( 'init', 'asfaq_taxonomy', 0 );
/**
 * Register the FAQs taxonomy
 *
 * @since 1.0
 * @return void
 */
function asfaq_taxonomy() {

	$labels = array(
		'name'              => _x( 'Categories', 'taxonomy general name' ),
		'singular_name'     => _x( 'Category', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Categories' ),
		'all_items'         => __( 'All Categories' ),
		'parent_item'       => __( 'Parent Category' ),
		'parent_item_colon' => __( 'Parent Category:' ),
		'edit_item'         => __( 'Edit Category' ),
		'update_item'       => __( 'Update Category' ),
		'add_new_item'      => __( 'Add New Category' ),
		'new_item_name'     => __( 'New Category Name' ),
		'menu_name'         => __( 'Categories' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'faqs' ),
	);

	register_taxonomy( 'as-faq-category', array( 'faq' ), $args );

}