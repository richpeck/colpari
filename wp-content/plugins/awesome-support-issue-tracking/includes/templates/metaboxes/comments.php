<?php
global $post, $pagenow;


$issue = new WPAS_IT_Issue( $post->ID );



$status = get_post_meta( $post->ID, '_wpas_status', true );
?>


<?php
		/* If the post hasn't been saved yet we do not display the metabox's content */
		if( '1' == $status ) { ?>

			<div class="updated below-h2" style="margin-top: 2em;">
				<h2 style="margin: 0.5em 0; padding: 0; line-height: 100%;"><?php _e( 'Create Ticket', 'wpas_it' ); ?></h2>
				<p><?php _e( 'Please save this ticket to reveal all options.', 'wpas_it' ); ?></p>
			</div>
		<?php } ?>
		
<table class="form-table wpas-table-it-comments">
	<tbody>

		<?php
		/* Now let's display the real content */
		if( '1' != $status ) {

			$comments = $issue->getComments();
			

			if ( ! empty( $comments ) ):

				foreach ( $comments as $row ) {
					include WPAS_IT_PATH . 'includes/templates/metaboxes/comment_item.php';
				}
			endif;
		} ?>
	</tbody>
</table>

<?php if( $issue->is_closed() ) { ?>
	
	
	<div class="updated below-h2" style="margin-top: 2em;">
		<p><?php _e( 'This issue has been closed.', 'wpas_it' ); ?></p>
	</div>

	

<?php } else {
		
		if( current_user_can( 'reply_ticket' ) ) {
			if( 'post-new.php' !== $pagenow  ) {
				require( WPAS_IT_PATH . 'includes/templates/metaboxes/comments-form.php' );
			}
		} else { ?>

			<p><?php _e( 'Sorry, you don\'t have sufficient permissions to add comment on this issue.', 'wpas_it' ); ?></p>
		
		<?php } ?>

	

<?php }
