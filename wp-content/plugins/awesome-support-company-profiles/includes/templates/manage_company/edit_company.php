<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


$name    = get_the_title( $company_id );
$address = get_post_meta( $company_id, 'address', true );
$email   = get_post_meta( $company_id, 'email',   true );
$phone   = get_post_meta( $company_id, 'phone',   true );
$fax     = get_post_meta( $company_id, 'fax',     true );



?>

<div class="wpas_cp_edit_company_win white-popup wpas_cp_window_wrapper">
		
	<div class="wpas_cp_msg"></div>
	<form class="wpas-form" role="form" method="post" action="" id="wpas-cp-edit-company">
		
		<?php wp_nonce_field( 'wpas_cp_mc_company_profile', 'wpas-cp-edit-company-profile', true, true ); ?>


		<input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />
		<input type="hidden" name="action" value="wpas_cp_manage_company_edit" />

		<div class="wpas-cp-field">
			<label for="cp_mpec_name"><strong><?php _e( 'Company Name', 'wpas_cp' ); ?></strong></label>
			<p>
					<input type="text" name="cp_name" id="cp_mpec_name" value="<?php echo $name; ?>">
			</p>
		</div>

		<div class="wpas-cp-field">
			<label for="cp_mpec_address"><strong><?php _e( 'Address', 'wpas_cp' ); ?></strong></label>
			<p>
					<textarea name="cp_address" id="cp_mpec_address"><?php echo $address; ?></textarea>
			</p>
		</div>

		<div class="wpas-cp-field">
			<label for="cp_mpec_email"><strong><?php _e( 'Email', 'wpas_cp' ); ?></strong></label>
			<p>
					<input type="text" name="cp_email" id="cp_mpec_email" value="<?php echo $email; ?>" />
			</p>
		</div>

		<div class="wpas-cp-field">
			<label for="cp_mpec_phone"><strong><?php _e( 'Phone', 'wpas_cp' ); ?></strong></label>
			<p>
					<input type="text" name="cp_phone" id="cp_mpec_phone" value="<?php echo $phone; ?>" />
			</p>
		</div>

		<div class="wpas-cp-field">
			<label for="cp_mpec_fax"><strong><?php _e( 'Fax', 'wpas_cp' ); ?></strong></label>
			<p>
					<input type="text" name="cp_fax" id="cp_mpec_fax" value="<?php echo $fax; ?>" />
			</p>
		</div>


		<div class="wpas_win_footer">
			<p class="submit">
				<input type="button" class="button button-primary" value="<?php _e( 'Update', 'wpas_cp' ); ?>">
			</p>
			<p class="wpas_win_close_btn">
				<input type="button" class="button button-primary" value="<?php _e( 'Close', 'wpas_cp' ); ?>">
			</p>
			<div class="clear clearfix"></div>
		</div>
	</form>
		
</div>

