<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

global $pagenow, $post;




$address = get_post_meta( $post->ID, 'address', true );
$email   = get_post_meta( $post->ID, 'email',   true );
$phone   = get_post_meta( $post->ID, 'phone',   true );
$fax     = get_post_meta( $post->ID, 'fax',     true );


?>

<div class="wpas-cp-company-details">
		
	<?php wp_nonce_field( 'wpas_cp_company_profile', 'wpas-cp-add-company-profile-nonce', true, true ); ?>
	
	
	
	<div class="wpas-cp-mb-details-field">
		<label for="wpas-post-status"><strong><?php _e( 'Address', 'wpas_cp' ); ?></strong></label>
		<p>
				<textarea name="cp_address" id="cp_address"><?php echo $address; ?></textarea>
		</p>
	</div>
		
	<div class="wpas-cp-mb-details-field">
		<label for="wpas-post-status"><strong><?php _e( 'Email', 'wpas_cp' ); ?></strong></label>
		<p>
				<input type="text" name="cp_email" id="cp_email" value="<?php echo $email; ?>" />
		</p>
	</div>
		
	<div class="wpas-cp-mb-details-field">
		<label for="wpas-post-status"><strong><?php _e( 'Phone', 'wpas_cp' ); ?></strong></label>
		<p>
				<input type="text" name="cp_phone" id="cp_phone" value="<?php echo $phone; ?>" />
		</p>
	</div>
		
	<div class="wpas-cp-mb-details-field">
		<label for="wpas-post-status"><strong><?php _e( 'Fax', 'wpas_cp' ); ?></strong></label>
		<p>
				<input type="text" name="cp_fax" id="cp_fax" value="<?php echo $fax; ?>" />
		</p>
	</div>
</div>