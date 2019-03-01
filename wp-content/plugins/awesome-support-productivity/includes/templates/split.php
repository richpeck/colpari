<?php

$action = 'pf_split';
$window_id = "wpas_{$action}_wrapper";

?>




<div class="wpas_tb_window">
	<div id="<?php echo $window_id; ?>">

		<div class="wpas_tb_window_wrapper" data-section="">
			<?php wp_nonce_field( 'pf-split', '_wpnonce_pf-split'  ); ?>
			<input type="hidden" data-name="id" data-default="" value="<?php echo $id; ?>" />
			<input type="hidden" class="wpas_pf_form_action" value="<?php echo $action; ?>" />

			<div class="wpas_pf_msg"></div>

			
			<table class="form-table">

				<tr class="form-field form-required">
					<th scope="row">
						<label><?php _e('Title'); ?> <span class="description"><?php _e('(required)'); ?></span></label>
					</th>
					<td>
						<input type="text" data-name="split_title" data-default="" value="<?php echo $title; ?>" />
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row">
						<label><?php _e('Content'); ?> <span class="description"><?php _e('(required)'); ?></span></label>
					</th>
					<td>
						<?php 

						wp_editor( $content, "pf_split_content", array(
							'media_buttons' => false,
							'teeny'         => true,
							'quicktags'     => true,
							'editor_height' => '200',
							'editor_width'  => '400'
						    
						) );

						?>

					</td>
				</tr>
			</table>
			
			<?php pf_tb_footer( 'Copy To New Ticket' ); ?>
			
		</div>
	</div>
</div>