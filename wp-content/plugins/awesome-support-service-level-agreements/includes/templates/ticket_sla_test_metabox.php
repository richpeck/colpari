<?php

/* Exit if accessed directly */
if( !defined( 'ABSPATH' ) ) {
	exit;
}

global $post_type, $post_id, $current_user;

?>


<div>
	<p>
		<label><strong><?php _e( 'Test Ticket Receipt Date', 'wpas_sla' ) ?> : </strong></label>
		<input type="text" class="test_ticket_receipt_date" />
	</p>
</div>

<div class="calculated_test_due_date_msg"><p></p></div>

<div class="calculated_test_due_date">
	<p>
		<label><strong><?php _e( 'Test Due Date', 'wpas_sla' ) ?> : </strong> <span class="date"></span></label>
		
	</p>
</div>

<div>
	<p>
		<input type="button" class="test_due_date_calculate_button button button-primary" value="Calculate Test Due Date" />
	</p>
</div>
<?php 
wp_nonce_field( 'wpas-sla-get-test-due-date', 'sla_nonce_wpas_sla_get_test_due_date' ); 
?>