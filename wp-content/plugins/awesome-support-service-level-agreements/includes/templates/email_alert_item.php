<?php

/* Exit if accessed directly */
if( !defined( 'ABSPATH' ) ) {
	exit;
}


$alert_recipients = '';
$alert_subject = '';
$alert_content = '';

$_alert_recipient_types = array();


if( isset( $alert ) ) {
	$alert_recipients = isset( $alert['recipients'] ) ? $alert['recipients'] : '';
	$alert_subject = isset( $alert['subject'] ) ? $alert['subject'] : '';
	$alert_content = isset( $alert['content'] ) ? $alert['content'] : '';
	$alert_time = isset( $alert['time'] ) ? $alert['time'] : '';
	
	$alert_recipient_types = isset( $alert['recipient_types'] ) && is_array( $alert['recipient_types'] ) ? $alert['recipient_types'] : array();
	
	$name_prefix = "sla_alerts[{$alert_id}]";
	$id_prefix = "sla_alerts__{$alert_id}__";
}
?>

<table class="wpas_sla_email_alert form-table" data-alert_id="<?php echo $alert_id; ?>">
		<tr valign="top">
			<td colspan="2" align="right">
				<button class="button button-primary button-small btn_wpas_sla_delete_email_alert"><?php _e( 'Delete', 'wpas_sla' ); ?></button>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row" class="first">
				<label for="sla_alerts__recipients"><?php _e( 'Time', 'wpas_sla' ); ?></label>
			</th>
			<td class="second tf-text">
				<input class="regular-text" name="<?php echo $name_prefix; ?>[time]" id="<?php echo $id_prefix; ?>time" type="text" value="<?php echo $alert_time; ?>" />
				<p class="description"><?php _e( 'Time in minutes', 'wpas_sla' ); ?></p>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row" class="first">
				<label for="sla_alerts__recipients"><?php _e( 'Recipient', 'wpas_sla' );?></label>
			</th>
			<td class="second tf-text">
				<input class="regular-text" name="<?php echo $name_prefix; ?>[recipients]" id="<?php echo $id_prefix; ?>recipients" type="text" value="<?php echo $alert_recipients; ?>" />
				<p class="description"><?php _e( 'Recipients (Comma separated list of emails)', 'wpas_sla' ); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" class="first">
				<label for="sla_alerts__subject"><?php _e( 'Subject', 'wpas_sla' ); ?></label>
			</th>
			<td class="second tf-text">
				<input class="regular-text" name="<?php echo $name_prefix; ?>[subject]" id="<?php echo $id_prefix; ?>subject" type="text" value="<?php echo $alert_subject; ?>" />
			</td>
		</tr>


		<tr valign="top">
				<th scope="row" class="first">
					<label for="wpas_new_reply_agent__content"><?php _e( 'Content', 'wpas_sla' ); ?></label>
				</th>
				<td class="second tf-editor">

					<?php
					wp_editor( $alert_content, "{$id_prefix}content", array(
						'textarea_name' => "{$name_prefix}[content]"
					) );
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
				$_alert_recipient_types = wpas_sla_alert_recipient_types();
				foreach ( $_alert_recipient_types as $type => $label ) {
					$checked = in_array( $type, $alert_recipient_types ) ? 'checked="checked" ' : '';
					printf( '<label><input id="%s" type="checkbox" name="%s" value="%s" %s/> %s</label><br>', "{$id_prefix}recipient_types__{$type}", "{$name_prefix}[recipient_types][]" , $type, $checked , $label );
				}
				?>
			</fieldset>
			</td>
		</tr>

	</table>