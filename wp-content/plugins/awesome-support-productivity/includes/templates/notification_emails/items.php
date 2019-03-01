<?php

$del_action = $this->actionName( 'delete' );
$del_nonce = $this->nonce( $del_action );


$active_action = $this->actionName( 'active' );
$active_nonce = $this->nonce( $active_action );

?>


<div class="wpas_pf_ui_items">
	<?php if( $this->user_can_add() ) : ?>
	<input type="hidden" class="delete-nonce" data-name="<?php echo $del_nonce['name']; ?>" value="<?php echo wp_create_nonce( $del_nonce['action'] ); ?>" />
	<input type="hidden" class="active-nonce" data-name="<?php echo $active_nonce['name']; ?>" value="<?php echo wp_create_nonce( $active_nonce['action'] ); ?>" />
	<?php
	
	endif;
	
	foreach( $items as $item_id => $item ) {
		include 'item.php';
	}
?>
</div>