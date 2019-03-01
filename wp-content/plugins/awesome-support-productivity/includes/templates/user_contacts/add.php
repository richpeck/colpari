<?php

$submit_text = isset( $args['submit_text'] ) ? $args['submit_text'] : 'Save';
$type = isset( $args['type'] ) ? $args['type'] : 'add';

$email = isset( $args['data']['email'] ) ? $args['data']['email'] : '';


$contact_user = null;

if( isset( $args['data']['user_id'] ) && $args['data']['user_id'] ) {
	$contact_user = get_user_by( 'id', $args['data']['user_id'] );
}



$active = isset( $args['data']['active'] ) && $args['data']['active'] ? true : false;






if( 'edit' === $args['type'] ) {
	echo '<input type="hidden" data-name="id" data-default="" value="' . $args['data']['id'] . '" />';
}
?>


<table class="form-table">
	<tr class="form-field form-required">
		<th scope="row">
			<label><?php _e( 'Select user' ); ?> <span class="description"><?php _e('(required)'); ?></span></label>
		</th>
		<td>
			
			<?php
			
			$options = "";
			
			if( $contact_user ) {
				$options = "<option selected=\"selected\" value=\"{$contact_user->ID}\">".'#'.$contact_user->ID . ' - ' . $contact_user->display_name . ' - ' . $contact_user->user_email."</option>";
			}
			
			$staff_atts = array(
				'name'      => 'wpas_pf_contact',
				'id'        => 'wpas_pf_contact',
				'select2'   => true,
				'data_attr' => array( 'capability' => 'create_ticket', 'opt-type' => 'user-picker' )
			);
			
			

			echo wpas_dropdown( $staff_atts, $options );
			
			?>
			
		</td>
	</tr>
	<tr>
		<th scope="row"></th>
		<td>
			<label><input type="checkbox"<?php echo ( ( $active ) ? ' checked="checked"' : '' ); ?> data-name="active" value="1" /> <?php _e('Receive Notification Emails'); ?></label>
		</td>
		
	</tr>
</table>
<?php pf_tb_footer( $submit_text ); ?>