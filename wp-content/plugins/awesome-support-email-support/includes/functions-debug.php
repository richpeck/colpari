<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'wpas_system_tools_table_after', 'ases_reset_check_interval_tool' );
/**
 * Add a new cleanup tool to reset the e-mail check interval
 *
 * @since 0.1.4
 * @return void
 */
function ases_reset_check_interval_tool() { ?>

	<tr>
		<td class="row-title"><label for="tablecell"><?php _e( 'Reset Mail Check Interval', 'as-email-support' ); ?></label></td>
		<td>
			<a href="<?php echo wpas_tool_link( 'email_interval_reset' ); ?>" class="button-secondary"><?php _e( 'Reset', 'as-email-support' ); ?></a>
			<span class="wpas-system-tools-desc"><?php _e( 'Reset the e-mail checking interval.', 'as-email-support' ); ?></span>
		</td>
	</tr>

<?php }

add_action( 'plugins_loaded', 'ases_reset_check_interval', 25 );
/**
 * Remove the option that controls the interval at which e-mails are checked
 *
 * @since 0.1.4
 * @return void
 */
function ases_reset_check_interval() {

	if ( ! isset( $_GET['tool'] ) || ! isset( $_GET['_nonce'] ) ) {
		return;
	}

	if ( 'email_interval_reset' !== $_GET['tool'] ) {
		return;
	}

	if ( ! wp_verify_nonce( $_GET['_nonce'], 'system_tool' ) ) {
		return;
	}

	delete_option( 'wpas_check_mails' );

	/* Redirect in "read-only" mode */
	$url = add_query_arg( array(
		'post_type' => 'ticket',
		'page'      => 'wpas-status',
		'tab'       => 'tools',
		'done'      => sanitize_text_field( $_GET['tool'] )
	), admin_url( 'edit.php' )
	);

	wp_redirect( wp_sanitize_redirect( $url ) );
	exit;

}

/**
 * Try to establish a connection to the mailbox via Ajax
 *
 * @since 0.1.4
 * @return void
 */
function ases_test_settings_ajax() {
	echo json_encode( (array) ases_mailbox_test_connect() );
	die();
}