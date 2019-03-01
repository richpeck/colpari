<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<div class="wpas_it_comment_form">

	<h2>
		<?php
		
		echo apply_filters( 'wpas_it_write_comment_title_admin', sprintf( esc_html_x( 'Write a comment to %s', 'Title of the reply editor in the back-end', 'wpas_it' ), '&laquo;' . esc_attr( get_the_title( $post->ID ) ) . '&raquo;' ), $post ); ?>
	</h2>
		
		<table>
				<tr class="wpas_it_comment_field">
						<td><label class="wpas-it-field-label">Status : </label></td>
						<td><?php
			
						$ic_status_default_term = wpas_it_get_term_by( 'name', 'Standard', 'wpas_it_cmt_status' );
						
						wp_dropdown_categories( array( 
							'name'					=> 'it_comment_status',
							'option_none_value'		=> '',
							'show_option_none'		=> 'Select Status...',
							'taxonomy'				=> 'wpas_it_cmt_status', 
							'hide_empty'			=> false,
							'selected'				=> ( $ic_status_default_term ? $ic_status_default_term->term_id : '' )
							) );

						?></td>
				</tr>
				<tr class="wpas_it_comment_field">
						<td><label class="wpas-it-field-label">Type : </label></td>
						<td>
							<?php
							
							$types = wpas_it_comment_types();
							
							$options = '<option value="">' . __( 'Select Comment Type', 'wpas_it' ) . '</option>';
							
							foreach ( $types as $type_id => $type ) {
								$selected = 'regular' === $type_id ? ' selected="selected"' : '';
								$options .= "<option value=\"{$type_id}\"{$selected}>{$type}</option>";
							}
				
							
							echo wpas_dropdown( array(
								'name' => 'it_comment_type',
								
							), $options );
							
							?>
								
						</td>
				</tr>
				
		</table>
		
	
		
	
		
	<div>
	<?php
		// Load the WordPress WYSIWYG with minimal options
		wp_editor( apply_filters( 'wpas_admin_reply_form_reply_content', '' ), 'it_comment_content', apply_filters( 'wpas_admin_reply_form_args', array(
				'media_buttons' => false,
				'teeny'         => true,
				'quicktags'     => true,
			)
		) );
		?>
	</div>
	<?php
	
	
	
	wp_nonce_field( 'add-issue-comment', 'it_nonce_wpas_it_add_issue_comment' );
	?>

	<input type="hidden" name="it_comment_issue" value="<?php echo $post->ID; ?>" />
	<div class="wpas-it-comment-actions">
		<button type="button" class="button-primary wpas_it_btn_comment" value="comment"><?php _e( 'Comment', 'wpas_it' ); ?></button>
		
		<div class="wpas_it_message"><p></p></div>
	</div>
</div>