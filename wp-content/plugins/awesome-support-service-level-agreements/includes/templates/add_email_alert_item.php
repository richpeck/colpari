<?php
/* Exit if accessed directly */
if( !defined( 'ABSPATH' ) ) {
	exit;
}

?>

<script type="text/html" id="tmpl-wpas-sla-add-email-alert" src="">

<table class="wpas_sla_email_alert form-table" data-alert_id="{{{data.alert_id}}}">
		<tr valign="top">
			<td colspan="2" align="right">
				<button class="button button-primary button-small btn_wpas_sla_delete_email_alert">Delete</button>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row" class="first">
				<label for="sla_alerts__recipients"><?php _e( 'Time', 'wpas_sla' ); ?></label>
			</th>
			<td class="second tf-text">
				<input class="regular-text sla_add_email_alert_time" name="{{{data.name_prefix}}}[time]" id="{{{data.id_prefix}}}time" type="text" value="" />
				<p class="description"><?php _e( 'Time in minutes', 'wpas_sla' ); ?></p>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row" class="first">
				<label for="sla_alerts__recipients"><?php _e( 'Recipient', 'wpas_sla' ); ?></label>
			</th>
			<td class="second tf-text">
				<input class="regular-text sla_add_email_alert_recipients" type="text" value="" name="{{{data.name_prefix}}}[recipients]" id="{{{data.id_prefix}}}recipients" />
				<p class="description"><?php _e( 'Recipients (Comma separated list of emails)', 'wpas_sla' ); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" class="first">
				<label for="sla_alerts__subject"><?php _e( 'Subject', 'wpas_sla' ); ?></label>
			</th>
			<td class="second tf-text">
				<input class="regular-text sla_add_email_alert_subject" type="text" value="" name="{{{data.name_prefix}}}[subject]" id="{{{data.id_prefix}}}subject" />
			</td>
		</tr>


		<tr valign="top">
				<th scope="row" class="first">
					<label for="sla_add_email_alert_content"><?php _e( 'Content', 'wpas_sla' ); ?></label>
				</th>
				<td>
						
						<?php
						ob_start();
						wp_editor( '', "sla_add_alert_editor_id", array(
						'textarea_name' => "sla_add_alert_editor_textarea_name"
						) );
						
						$editor_content = ob_get_clean();
						
						$editor_content = str_replace( 'sla_add_alert_editor_id', '{{{data.editor_id}}}', $editor_content );
						$editor_content = str_replace( 'sla_add_alert_editor_textarea_name', '{{{data.name_prefix}}}[content]', $editor_content );
						
						echo $editor_content;
						?>

					
					
					<p class="description"><?php _e( 'Email Content', 'wpas_sla' ); ?></p>
			</td>
		</tr>


		<tr valign="top" class="row-18 even">
			<th scope="row" class="first">
				<label><?php _e( 'Who should receive notifications', 'wpas_sla' ); ?></label>
			</th>
			<td class="second tf-multicheck">
			<fieldset>

				<?php
				$alert_recipient_types = wpas_sla_alert_recipient_types();
				foreach ( $alert_recipient_types as $type => $label ) {
					printf( '<label><input type="checkbox" class="sla_add_email_alert_recipient_types" name="{{{data.name_prefix}}}[recipient_types][]" value="%s"> %s</label><br>', $type, $label );
				}
				?>
			</fieldset>
			</td>
		</tr>

	</table>
</script>