<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}



?>

<div class="wpas_cp_edit_company_win wpas_cp_window_wrapper">
		
		<h3> <?php _e( 'Company Info', 'wpas_cp' ); ?> </h3>
		<div class="wpas-cp-field">
			<label for="cp_mpec_name"><strong><?php _e( 'Company Name', 'wpas_cp' ); ?></strong></label>
			<p>
					<input type="text" name="cp_name" id="cp_mpec_name" value="">
			</p>
		</div>

		<div class="wpas-cp-field">
			<label for="cp_mpec_address"><strong><?php _e( 'Address', 'wpas_cp' ); ?></strong></label>
			<p>
					<textarea name="cp_address" id="cp_mpec_address"></textarea>
			</p>
		</div>

		<div class="wpas-cp-field">
			<label for="cp_mpec_email"><strong><?php _e( 'Email', 'wpas_cp' ); ?></strong></label>
			<p>
					<input type="text" name="cp_email" id="cp_mpec_email" value="" />
			</p>
		</div>

		<div class="wpas-cp-field">
			<label for="cp_mpec_phone"><strong><?php _e( 'Phone', 'wpas_cp' ); ?></strong></label>
			<p>
					<input type="text" name="cp_phone" id="cp_mpec_phone" value="" />
			</p>
		</div>

		<div class="wpas-cp-field">
			<label for="cp_mpec_fax"><strong><?php _e( 'Fax', 'wpas_cp' ); ?></strong></label>
			<p>
					<input type="text" name="cp_fax" id="cp_mpec_fax" value="" />
			</p>
		</div>

</div>

