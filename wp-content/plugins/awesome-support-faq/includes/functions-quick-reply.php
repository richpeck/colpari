<?php
/**
 * @package   Awesome Support FAQ/Settings
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


add_filter( 'wpas_admin_tabs_after_reply_wysiwyg', 'asfaq_quick_reply_add_tab' , 12, 1 ); // Add faq tab
add_filter( 'wpas_admin_tabs_after_reply_wysiwyg_faq_quick_reply_content', 'asfaq_quick_reply_content' , 11, 1 ); // Add content for faq

/**
 * Add faq tab
 * 
 * @param array $tabs
 * 
 * @return array
 */
function asfaq_quick_reply_add_tab( $tabs ) {
	$tabs['faq_quick_reply'] = __( 'Quick FAQ Links', 'as-faq' );
	
	return $tabs;
}

/**
 * Return content for faq tab
 * 
 * @return string
 */
function asfaq_quick_reply_content( $content ) {
	ob_start();
	asfaq_quick_reply();
	
	// Get the post ID
	$post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
	
	asfaq_reply_faq_button( $post_id );
	
	return ob_get_clean();
}


/**
 * Add FAQs quick reply under reply WYSIWYG
 *
 * @since 1.0
 * @return void
 */
function asfaq_quick_reply() {

	// Get the post ID
	$post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );

	// Instantiate WPAS_Email_Notification, used to convert template tags
	$emails = new WPAS_Email_Notification( $post_id );

	// Set the output
	$output = sprintf( '<div class="asfaq_quick_reply_wrapper"><h3>%s</h3>', esc_html__( 'Quick FAQ Links', 'as-faq' ) );

	// Get the FAQs
	$replies = asfaq_get_faqs( apply_filters( 'asfaq_quick_reply_query_args', array( 'posts_per_page' => 500 ) ) ); // Use an oddly high number as the limit

	if ( empty( $replies ) ) {
		echo '';
	}

	$output .= '<select class="wpas-select2 asfaq_quick_reply_select">';
	$output .= sprintf( '<option value="&nbsp;">%s</option>', esc_html__( 'Select a FAQ', 'as-faq' ) );

	foreach ( $replies as $reply ) {

		$title   = apply_filters( 'the_title', $reply->post_title );
		$url     = esc_url( get_permalink( $reply->ID ) );
		$link    = sprintf( '<a href="%1$s" target="_blank">%1$s</a>', $url );
		$content = $emails->fetch( asfaq_get_option( 'quick_reply_template', '' ) );
		$content = str_replace( '{faq_link}', $link, $content ); // Convert {faq_link} template tag that's unique to this addon
		$content = htmlentities( wpautop( str_replace( '\'', '&apos;', $content ) ) );

		// Add the select option
		$output .= sprintf( '<option value="%s">%s</option>', $content, $title );

	}

	$output .= '</select></div>';

	echo $output;

}

add_filter( 'contextual_help', 'asfas_contextual_help', 10, 0 );
/**
 * Add contextual help.
 *
 * The contextual help shows all the available tags
 * and how to use them in canned responses.
 *
 * @since  1.0
 * @return void
 */
function asfas_contextual_help() {

	global $post;

	if ( ! isset( $_GET['page'] ) || 'asfaq-settings' !== $_GET['page'] || isset( $_GET['tab'] ) && 'general' !== $_GET['tab'] ) {
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

	$tags .= sprintf( '<tr><td class="row-title"><strong>{faq_link}</strong></td><td>%s</td></tr>', esc_html__( 'Converts into the link to the FAQ' ) );
	$tags .= '</tbody></table>';

	$screen = get_current_screen();

	$screen->add_help_tab( array(
		'id'      => 'template-tags',
		'title'   => __( 'Template Tags', 'as-faq' ),
		'content' => sprintf( __( '<p>When setting up your canned responses, you can use a certain number of template tags allowing you to dynamically add ticket-related information at the moment the reply is sent. Here is the list of available tags:</p>%s', 'as-faq' ), $tags )
	) );
}