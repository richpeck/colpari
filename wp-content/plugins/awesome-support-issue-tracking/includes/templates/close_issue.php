
<div class="wpas_it_tb_window_wrapper" style="margin:20px 0;">

	<input type="hidden" class="close_issue_id" value="<?php echo $post->ID; ?>" />
	
	
	<?php wp_nonce_field( 'it-close-issue', 'it_nonce_wpas_it_close_issue' ); ?>

	<div class="wpas_it_msg"></div>

	<div style="margin-bottom: 10px;">
			<a href="#" class="btn_close_issue_with_tickets"><?php _e( 'Close all tickets attached to this issue', 'wpas_it' ); ?></a>
	</div>
	<div>
			<a href="#" class="btn_close_just_issue"><?php _e( 'Just close this issue', 'wpas_it' ); ?></a>
	</div>
</div>