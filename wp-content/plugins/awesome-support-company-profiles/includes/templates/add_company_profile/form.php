<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>


<div id="wpas_cp_add_company_form_wrapper">
	
	<form class="wpas-form" role="form" method="post" action="" id="wpas-cp-add-company">

		<div class="wpas_cp_msg"></div>
		<input type="hidden" name="action" value="wpas_cp_fe_add_company_profile" />
		<?php
		
		wp_nonce_field( 'wpas_cp_add_company_profile', 'wpas-cp-add-cp' );
		
		include 'company.php';

		include 'support_user.php';

		?>
			
		
		<p class="submit">
			<input type="button" class="button button-primary" value="<?php _e( 'Add Company Profile', 'wpas_cp' ); ?>">
		</p>	
	</form>
</div>