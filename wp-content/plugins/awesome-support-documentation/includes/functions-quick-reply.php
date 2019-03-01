<?php
/**
 * @package   Awesome Support Documentation
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2014-2017 Awesome Support
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


add_filter( 'wpas_admin_tabs_after_reply_wysiwyg', 'asdoc_quick_reply_add_tab' , 11, 1 ); // Add documentation links tab
add_filter( 'wpas_admin_tabs_after_reply_wysiwyg_doc_quick_reply_content', 'asdoc_quick_reply_content' , 11, 1 ); // Add content for documentations tab

/**
 * Add documentation links tab
 * 
 * @param array $tabs
 * 
 * @return array
 */
function asdoc_quick_reply_add_tab( $tabs ) {
	$tabs['doc_quick_reply'] = __( 'Quick Documentation Links', 'wpas-documentation' );
	
	return $tabs;
}

/**
 * Return content for documentation tab
 * 
 * @return string
 */
function asdoc_quick_reply_content() {
	ob_start();
	asdoc_quick_reply();
	
	// Get the post ID
	$post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
	
	asdoc_reply_doc_button( $post_id );
	return ob_get_clean();
}

/**
 * Add documentation quick reply under reply WYSIWYG
 *
 * @since 2.0.1
 * @return void
 */
function asdoc_quick_reply() {

	// Get the post ID
	$post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );

	// Instantiate WPAS_Email_Notification, used to convert template tags
	$emails = new WPAS_Email_Notification( $post_id );

	// Set the output
	$output = sprintf( '<div class="asdoc_quick_reply_wrapper"><h3>%s</h3>', esc_html__( 'Quick Documentation Links', 'wpas-documentation' ) );

	// Get the DOCUMENTATION items
	$replies = asdoc_get_docs( apply_filters( 'asdoc_quick_reply_query_args', array( 'posts_per_page' => 500 ) ) ); // Use an oddly high number as the limit

	if ( empty( $replies ) ) {
		echo '';
	}

	$output .= '<select class="wpas-select2 asdoc_quick_reply_select">';
	$output .= sprintf( '<option value="&nbsp;">%s</option>', esc_html__( 'Select a Document', 'wpas-documentation' ) );

	foreach ( $replies as $reply ) {

		$title   = apply_filters( 'the_title', $reply->post_title );
		$url     = esc_url( get_permalink( $reply->ID ) );
		$link    = sprintf( '<a href="%1$s" target="_blank">%1$s</a>', $url );
		$content = $emails->fetch( asdoc_get_option( 'asdoc-quick_reply_template', '' ) );
		$content = str_replace( '{doc_link}', $link, $content ); // Convert {doc_link} template tag that's unique to this addon
		$content = htmlentities( wpautop( str_replace( '\'', '&apos;', $content ) ) );

		// Add the select option
		$output .= sprintf( '<option value="%s">%s</option>', $content, $title );

	}

	$output .= '</select></div>';

	echo $output;

}

add_filter( 'contextual_help', 'asdoc_contextual_help', 10, 0 );
/**
 * Add contextual help.
 *
 * The contextual help shows all the available tags
 * and how to use them in documentatoin.
 *
 * @since  2.0.1
 * @return void
 */
function asdoc_contextual_help() {

	global $post;

	if ( ! isset( $_GET['page'] ) || 'asdoc-settings' !== $_GET['page'] || isset( $_GET['tab'] ) && 'general' !== $_GET['tab'] ) {
		return;
	}

	/**
	 * Gather the list of e-mail template tags and their description
	 */
	$emails    = new WPAS_Email_Notification( false );
	$list_tags = $emails->get_tags();

	$tags = '<table class="widefat"><thead><th class="row-title">' . __( 'Tag', 'wpas-documentation' ) . '</th><th>' . __( 'Description', 'wpas-documentation' ) . '</th></thead><tbody>';

	foreach ( $list_tags as $the_tag ) {
		$tags .= '<tr><td class="row-title"><strong>' . $the_tag['tag'] . '</strong></td><td>' . $the_tag['desc'] . '</td></tr>';
	}

	$tags .= sprintf( '<tr><td class="row-title"><strong>{doc_link}</strong></td><td>%s</td></tr>', esc_html__( 'Converts into the link to the Documentation Item' ) );
	$tags .= '</tbody></table>';

	$screen = get_current_screen();

	$screen->add_help_tab( array(
		'id'      => 'template-tags',
		'title'   => __( 'Template Tags', 'wpas-documentation' ),
		'content' => sprintf( __( '<p>When setting up your documentation, you can use a certain number of template tags allowing you to dynamically add ticket-related information at the moment the reply is sent. Here is the list of available tags:</p>%s', 'wpas-documentation' ), $tags )
	) );
}