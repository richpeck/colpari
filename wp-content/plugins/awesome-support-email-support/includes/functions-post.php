<?php
/**
 * Post Functions.
 *
 * @package   Awesome Support E-Mail Support/Admin
 * @author    Julien Liabeuf <julien@liabeuf.fr>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 */

function wpas_mail_get_messages( $limit = -1 ) {

	$args = array(
		'post_type'              => 'wpas_unassigned_mail',
		'post_status'            => 'publish',
		'order'                  => 'DESC',
		'orderby'                => 'date',
		'posts_per_page'         => $limit,
		'no_found_rows'          => false,
		'cache_results'          => true,
		'update_post_term_cache' => true,
		'update_post_meta_cache' => true,
	);
	
	$query = new WP_Query( $args );
	
	if ( empty( $query->posts ) ) {
		return array();
	}

	return $query->posts();

}

function wpas_mail_count_messages() {

	$args = array(
		'post_type'              => 'wpas_unassigned_mail',
		'post_status'            => 'publish',
		'order'                  => 'DESC',
		'orderby'                => 'date',
		'posts_per_page'         => -1,
		'no_found_rows'          => true,
		'cache_results'          => false,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
	);
	
	$query = new WP_Query( $args );
	
	return $query->post_count;

}

function wpas_mail_add_message( $args ) {

	$defaults = array(
		'post_content'   => '',
		'post_title'     => '',
		'post_status'    => 'publish',
		'post_type'      => 'wpas_unassigned_mail',
		'post_author'    => '',
		'ping_status'    => 'closed',
		'post_parent'    => 0,
		'comment_status' => 'closed',
	);

	$args = apply_filters( 'wpas_mail_insert_post_args', wp_parse_args( $args, $defaults ) );

	if ( empty( $args['post_content'] ) ) {
		return false;
	}

	if ( empty( $args['post_author'] ) || false === get_user_by( 'id', $args['post_author'] ) ) {
		return false;
	}

	return wp_insert_post( $args, false );

}