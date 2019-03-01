<?php

$del_action = $this->actionName( 'delete' );
$del_nonce = $this->nonce( $del_action );

?>


<div class="wpas_it_ui_items">
	<?php if( $this->user_can_add() ) : ?>
	<input type="hidden" class="delete-nonce" data-name="<?php echo $del_nonce['name']; ?>" value="<?php echo wp_create_nonce( $del_nonce['action'] ); ?>" />
	
	<?php
	
	endif;
	
	foreach( $items as $item_id => $item ) {
		include 'item.php';
	}
?>
</div>


