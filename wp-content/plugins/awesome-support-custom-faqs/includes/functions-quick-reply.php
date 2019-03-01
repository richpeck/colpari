<?php
/**
 * @package   Awesome Support CUSTOMFAQ/Settings
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      http://getawesomesupport.com
 * @copyright 2014 Awesome Support
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_filter( 'wpas_admin_tabs_after_reply_wysiwyg', 'ascustomfaq_quick_reply_add_tab' , 12, 1 ); // Add custom faq tab
add_filter( 'wpas_admin_tabs_after_reply_wysiwyg_customfaq_quick_reply_content', 'ascustomfaq_quick_reply_content' , 11, 1 ); // Add content for custom faq

/**
 * Add custom faq tab
 * 
 * @param array $tabs
 * 
 * @return array
 */
function ascustomfaq_quick_reply_add_tab( $tabs ) {
	$tabs['customfaq_quick_reply'] = __( 'Quick CUSTOMFAQ Links', 'as-customfaq' );
	
	return $tabs;
}

/**
 * Return content for custom faq tab
 * 
 * @return string
 */
function ascustomfaq_quick_reply_content() {
	ob_start();
	ascustomfaq_quick_reply();
	
	// Get the post ID
	$post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
	
	ascustomfaq_reply_customfaq_button( $post_id );
	
	return ob_get_clean();
}


/**
 * Add CUSTOMFAQs quick reply under reply WYSIWYG
 *
 * @since 1.0
 * @return void
 */
function ascustomfaq_quick_reply() {

	// Get the post ID
	$post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );

	// Instantiate WPAS_Email_Notification, used to convert template tags
	$emails = new WPAS_Email_Notification( $post_id );

	// Set the output
	$output = sprintf( '<div class="ascustomfaq_quick_reply_wrapper"><h3>%s</h3>', esc_html__( 'Quick CUSTOMFAQ Links', 'as-customfaq' ) );

	// Get the CUSTOMFAQs
	$replies = ascustomfaq_get_customfaqs( apply_filters( 'ascustomfaq_quick_reply_query_args', array( 'posts_per_page' => 500 ) ) ); // Use an oddly high number as the limit

	if ( empty( $replies ) ) {
		echo '';
	}

	$output .= '<select class="wpas-select2 ascustomfaq_quick_reply_select">';
	$output .= sprintf( '<option value="&nbsp;">%s</option>', esc_html__( 'Select a CUSTOMFAQ', 'as-customfaq' ) );

	foreach ( $replies as $reply ) {

		$title   = apply_filters( 'the_title', $reply->post_title );
		$url     = esc_url( get_permalink( $reply->ID ) );
		$link    = sprintf( '<a href="%1$s" target="_blank">%1$s</a>', $url );
		$content = $emails->fetch( ascustomfaq_get_option( 'customfaq_quick_reply_template', '' ) );
		$content = str_replace( '{customfaq_link}', $link, $content ); // Convert {customfaq_link} template tag that's unique to this addon
		$content = htmlentities( wpautop( str_replace( '\'', '&apos;', $content ) ) );

		// Add the select option
		$output .= sprintf( '<option value="%s">%s</option>', $content, $title );

	}

	$output .= '</select></div>';

	echo $output;

}

add_filter( 'contextual_help', 'ascustomfaq_contextual_help', 10, 0 );
/**
 * Add contextual help.
 *
 * The contextual help shows all the available tags
 * and how to use them in custom FAQs
 *
 * @since  1.0
 * @return void
 */
function ascustomfaq_contextual_help() {

	global $post;

	if ( ! isset( $_GET['page'] ) || 'ascustomfaq-settings' !== $_GET['page'] || isset( $_GET['tab'] ) && 'general' !== $_GET['tab'] ) {
		return;
	}

	/**
	 * Gather the list of e-mail template tags and their description
	 */
	$emails    = new WPAS_Email_Notification( false );
	$list_tags = $emails->get_tags();

	$tags = '<table class="widefat"><thead><th class="row-title">' . __( 'Tag', 'wpas' ) . '</th><th>' . __( 'Description', 'wpas' ) . '</th></thead><tbody>';

	foreach ( $list_tags as $the_tag ) {
		$tags .= '<tr><td class="row-title"><strong>' . $the_tag['tag'] . '</strong></td><td>' . $the_tag['desc'] . '</td></tr>';
	}

	$tags .= sprintf( '<tr><td class="row-title"><strong>{customfaq_link}</strong></td><td>%s</td></tr>', esc_html__( 'Converts into the link to the CUSTOMFAQ' ) );
	$tags .= '</tbody></table>';

	$screen = get_current_screen();

	$screen->add_help_tab( array(
		'id'      => 'template-tags',
		'title'   => __( 'Template Tags', 'as-customfaq' ),
		'content' => sprintf( __( '<p>When setting up your quick reply, you can use a certain number of template tags allowing you to dynamically add ticket-related information at the moment the reply is sent. Here is the list of available tags:</p>%s', 'as-customfaq' ), $tags )
	) );
}