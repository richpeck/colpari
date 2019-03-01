<?php

$del_action = $this->actionName( 'delete' );
$del_nonce = $this->nonce( $del_action );

$dup_action = $this->actionName( 'duplicate' );
$dup_nonce = $this->nonce( $dup_action );

$completed_action = $this->actionName( 'completed' );
$completed_nonce = $this->nonce( $completed_action );

?>


<div class="wpas_pf_ui_items">
	<?php if( $this->user_can_add_todo() ) : ?>
	<input type="hidden" class="delete-nonce" data-name="<?php echo $del_nonce['name']; ?>" value="<?php echo wp_create_nonce( $del_nonce['action'] ); ?>" />
	<input type="hidden" class="duplicate-nonce" data-name="<?php echo $dup_nonce['name']; ?>" value="<?php echo wp_create_nonce( $dup_nonce['action'] ); ?>" />
	<input type="hidden" class="completed-nonce" data-name="<?php echo $completed_nonce['name']; ?>" value="<?php echo wp_create_nonce( $completed_nonce['action'] ); ?>" />
	<?php
	
	endif;
	
	foreach( $items as $item_id => $item ) {
		include 'item.php';
	}
?>
</div>