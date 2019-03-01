<?php

/* Exit if accessed directly */
if( !defined( 'ABSPATH' ) ) {
	exit;
}

global $post_type, $post_id, $current_user;



if( 'ticket' !== $post_type ) {
	return;
}

wpas_sla_ticket_form_add_nonce_field(); 

if( ! $post_id ) {
	return;
}


$due_date = get_post_meta( $post_id, '_wpas_due_date', true );
$due_date_locked = get_post_meta( $post_id, '_wpas_due_date_lock', true );
$due_date_read_only = $due_date_locked || !$current_user->has_cap( 'ticket_edit_due_date' ) ? true : false;

$sla_id = wpas_sla_get_sla_id( $post_id );

// Don't print sla id field if there is no sla id and user don't have capability to update sla id
if( $sla_id || current_user_can( 'ticket_sla_admin' ) ) {
?>

<div class="sla_id_picker">
	<p>
		<label> <strong><?php _e( 'SLA ID', 'wpas_sla' ) ?></strong> </label>
		<div>
		<?php 
		if( current_user_can( 'ticket_sla_admin') ) {
			wpas_sla_sla_id_field( $post_id ); 
		} elseif( $sla_post = get_post( $sla_id ) ) {
			printf( '<input type="text" value="%s" readonly="readonly" />', esc_html( $sla_post->post_title ) );
		}
		?>
		</div>
	</p>
</div>

<?php 
}
?>

<div class="sla_ticket_due_date">
	<p>
		<label><strong><?php _e( 'Due Date', 'wpas_sla' ) ?></strong></label>
		<input type="text" name="sla_due_date" value="<?php echo $due_date; ?>"<?php echo ( $due_date_read_only ? ' readonly="readonly"' : '' ); ?> />
	</p>
</div>

<?php 

// Don't print lock due date field if user don't have capability to update lock due date
if( current_user_can( 'ticket_edit_due_date' ) ) { 
	
?>

<div class="sla_ticket_lock_due_date">
	<p>
		<label>
			<input type="checkbox" value="1" name="wpas_due_date_lock"<?php echo ( $due_date_locked ? ' checked="checked"' : '' ); ?> />
			<strong><?php _e( 'Lock Due Date', 'wpas_sla' ) ?></strong>
		</label>
	</p>
</div>

<?php } ?>

<?php 


wpas_sla_add_sla_dropdown_nonce_field();

?>