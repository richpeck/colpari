<div id="wpas-unknown-message-details">
	<?php
	/**
	 * wpas_backend_mail_unknown_details_before hook
	 *
	 * @param  integer $post_id Current post ID
	 * @param  object  $post    Current post object
	 * @since  0.1.0
	 */
	do_action( 'wpas_backend_mail_unknown_details_before', $post->ID, $post );

	/* Get e-mail headers */
	$headers = ases_read_email_headers( $raw_headers = get_post_meta( $post->ID, '_wpas_email_headers', true ) );
	$unknown = get_post_meta( $post->ID, '_wpas_mail_unknown_author', true );

	if ( false === $headers ) {
		esc_html_e( 'Error reading the email headers.', 'as-email-support' );
		return;
	} ?>

	<div class="error">
		<p>
			<?php
			if ( 0 == $post->post_parent && ! empty( $unknown ) ) {
				_e( 'This message is unassigned because we are unable to identify the ticket it relates to nor the sender', 'wpas-mail' );
			} elseif ( 0 == $post->post_parent ) {
				_e( 'This message is unassigned because we are unable to identify the ticket it relates to', 'wpas-mail' );
			} elseif ( ! empty( $unknown ) ) {
				_e( 'This message is unassigned because we are unable to identify the sender', 'wpas-mail' );
			}
			?>
		</p>
	</div>

	<h3><?php _e( 'Highlights', 'wpas-mail' ); ?></h3>
	<table class="widefat wpas-mail-highlights" style="text-align: center;">
		<tbody>
			<tr>
				<td width="33%">
					<h2><?php esc_html_e( 'Sender', 'wpas-mail' ); ?></h2>
					<?php
					if ( user_can( $post->post_author, 'edit_ticket' ) ) {
						echo $headers->get( 'sender_email' );
					} else {

						$user         = get_user_by( 'id', $post->ID );
						$user_profile = add_query_arg( 'user_id', $user->ID, admin_url( 'user-edit.php' ) );

						echo "<a href='$user_profile'>{$user->data->display_name}</a>";

					}
					?>
				</td>
				<td width="33%">
					<h2><?php esc_html_e( 'Ticket', 'wpas-mail' ); ?></h2>
					<p>
						<?php
						if ( 0 != $post->post_parent ) {

							$link   = add_query_arg( array( 'post' => $post->post_parent, 'action' => 'edit' ), admin_url( 'post.php' ) );
							$ticket = get_post( $post->post_parent );

							if ( 'ticket' === $ticket->post_type ) {
								printf( '<a href="%s">%s (#%s)</a>', $link, $ticket->post_title, $post->post_parent );
							} else {
								printf( esc_html__( 'Invalid (%s)', 'wpas-mail' ), "<a href='$link'>#$post->post_parent</a>" );
							}
						
						} else {
							esc_html_e( 'Unknown', 'wpas-mail' );
						}
						?>
					</p>
				</td>
				<td width="33%">
					<h2><?php esc_html_e( 'Sent On', 'wpas-mail' ); ?></h2>
					<p><?php echo $headers->get_date(); ?></p>
				</td>
			</tr>
		</tbody>
	</table>

	<h3><?php esc_html_e( 'Details', 'wpas-mail' ); ?></h3>
	<textarea name="wpas_header_raw_dump" id="wpas_header_raw_dump" style="width:100%; height:200px;">
		<?php print_r( $raw_headers ); ?>
	</textarea>

	<?php
	/**
	 * wpas_backend_mail_unknown_details_after hook
	 *
	 * @param  integer $post_id Current post ID
	 * @param  object  $post    Current post object
	 * @since  0.1.0
	 */
	do_action( 'wpas_backend_mail_unknown_details_after', $post->ID, $post );
	?>
</div>