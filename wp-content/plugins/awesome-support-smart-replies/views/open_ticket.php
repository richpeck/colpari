<?php
/**
 * This is a built-in template file. If you need to customize it, please,
 * DO NOT modify this file directly. Instead, copy it to your theme's directory
 * and then modify the code. If you modify this file directly, your changes
 * will be overwritten during next update of the plugin.
 */


$current_user = wp_get_current_user();


$email = "";
if( $current_user ) {
	$email = $current_user->user_email;
}


?>

<div class="wpas wpas-submit-ticket">

	
	<form class="wpas-form" role="form" method="post" action="" id="wpas-sc-new-ticket" enctype="multipart/form-data">

		
		<h3><?php _e( 'Open New Ticket', 'wpas_chatbot' ); ?></h3>
		
		<div class="notify"></div>
		
		<div class="wpas-form-group" id="wpas_email_wrapper">
			<label for="wpas_title">Email</label>
			<?php
			
			printf( '<input value="%s" id="wpas_email" class="wpas-form-control" name="wpas_email" required="" type="email" %s>', 
				$email, 
				($email ? 'disabled' : '')
				);
			
			?>
		</div>
		
		
		<div class="wpas-form-group" id="wpas_title_wrapper">
			<label for="wpas_title">Subject</label>
			<input value="" id="wpas_title" class="wpas-form-control" name="wpas_title" required="" type="text">
		</div>
		
		
		<div class="wpas-form-group" id="wpas_message_wrapper">
			<label for="wpas_message">Description</label>
			
			<textarea id="wpas_message" class="wpas-form-control" name="wpas_message" required="" rows="5" cols="10"></textarea>
		</div>
		
		
		<?php
		
		
		wp_nonce_field( 'sc_new_ticket', 'wpas_nonce', true, true );
		wpas_make_button( __( 'Submit ticket', 'awesome-support' ), array( 'name' => 'wpas-submit' ) );
		
		/**
		 * The wpas_submission_form_inside_before hook has to be placed
		 * right before the form closing tag.
		 *
		 * @since  3.0.0
		 */
		do_action( 'wpas_submission_form_inside_after' );
		wpas_do_field( 'sc_submit_new_ticket' );
		
		?>
	</form>
</div>