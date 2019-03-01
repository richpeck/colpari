<?php

$submit_text = isset( $args['submit_text'] ) ? $args['submit_text'] : 'Save';
$type = 'add';




wp_nonce_field( 'wpas-get-issues', 'it_nonce_wpas_get_select2_issues' );
?>


<table class="form-table">
	<tr class="form-field form-required">
		<th scope="row">
			<label><?php _e( 'Select issue' ); ?> <span class="description"><?php _e('(required)'); ?></span></label>
		</th>
		<td>
			
			<?php
			
			$select2 = wpas_it_get_option( 'issue_dropdown_select2', false );

			if( $select2 ) {
			
				echo wpas_dropdown( array(
					'name'      => 'wpas_ticket_issue',
					'id'        => 'wpas_ticket_issue',
					'select2'   => true,
					'class'		=> 'it-select2',
					'data_attr' => array( 'opt-type' => 'issues', 'default' => '' ) 
					), "" );
			} else {
				
				wp_dropdown_pages(array(
					'post_type'			=> 'wpas_issue_tracking', 
					'selected'			=> "", 
					'name'				=> 'wpas_ticket_issue', 
					'show_option_none' 	=> __( 'Select an issue', 'wpas_it' ), 
				));

			}
			?>
			
		</td>
	</tr>
	
</table>
<div class="tb_footer">
	<p class="submit">
		<input type="button" class="button button-primary" value="<?php _e( $submit_text, 'wpas_it' ); ?>">
	</p>
	<p class="it_tb_close_btn">
		<input type="button" class="button button-primary" value="<?php _e( 'Close', 'wpas_it' ); ?>">
	</p>
	<div class="clear clearfix"></div>
</div>